<?php
// api/midtrans-callback.php
// Midtrans akan POST ke URL ini setiap ada update status pembayaran
require_once __DIR__ . '/../includes/functions.php';

$raw    = file_get_contents('php://input');
$data   = json_decode($raw, true);

if (!$data) { http_response_code(400); exit; }

$orderId   = $data['order_id'] ?? '';
$grossAmt  = $data['gross_amount'] ?? 0;
$statusCode= $data['status_code'] ?? '';
$txStatus  = $data['transaction_status'] ?? '';
$payType   = $data['payment_type'] ?? '';
$txId      = $data['transaction_id'] ?? '';
$fraudStatus = $data['fraud_status'] ?? '';

// Verify signature
$serverKey  = midtransServerKey();
$signKey    = hash('sha512', $orderId . $statusCode . $grossAmt . $serverKey);
if ($signKey !== ($data['signature_key'] ?? '')) {
    http_response_code(403); exit('Invalid signature');
}

// Find order
$stmt = db()->prepare("SELECT * FROM orders WHERE order_code = ?");
$stmt->execute([$orderId]);
$order = $stmt->fetch();
if (!$order) { http_response_code(404); exit; }

// Map Midtrans status → our status
$newStatus = null;
if ($txStatus === 'capture') {
    $newStatus = ($fraudStatus === 'challenge') ? 'pending' : 'paid';
} elseif ($txStatus === 'settlement') {
    $newStatus = 'paid';
} elseif (in_array($txStatus, ['cancel','deny','expire'])) {
    $newStatus = 'failed';
} elseif ($txStatus === 'refund') {
    $newStatus = 'refunded';
}

if (!$newStatus) { http_response_code(200); exit('OK'); }

$db = db();
$db->beginTransaction();
try {
    // Update payment
    $db->prepare("
        UPDATE payments SET
          midtrans_id = ?, payment_method = ?, status = ?,
          midtrans_status = ?, paid_at = ?, raw_response = ?,
          updated_at = NOW()
        WHERE order_id = ?
    ")->execute([
        $txId, $payType, $newStatus === 'paid' ? 'settlement' : $txStatus,
        $txStatus,
        $newStatus === 'paid' ? date('Y-m-d H:i:s') : null,
        $raw,
        $order['id'],
    ]);

    // Update order status
    $db->prepare("UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?")
       ->execute([$newStatus, $order['id']]);

    // If paid → trigger Digiflazz
    if ($newStatus === 'paid' && $order['status'] !== 'paid') {
        $stmt2 = db()->prepare("SELECT * FROM products WHERE id = ?");
        $stmt2->execute([$order['product_id']]);
        $product = $stmt2->fetch();

        if ($product) {
            // Update order to processing
            $db->prepare("UPDATE orders SET status='processing', updated_at=NOW() WHERE id=?")->execute([$order['id']]);

            $digiResp = digiTopUp(
                $product['digi_code'],
                $order['game_user_id'] . ($order['server_id'] ? '|' . $order['server_id'] : ''),
                $order['order_code']
            );

            $digiData   = $digiResp['data'] ?? [];
            $digiStatus = strtolower($digiData['status'] ?? 'gagal');
            $finalStatus = match($digiStatus) {
                'sukses' => 'success',
                'process', 'pending' => 'processing',
                default  => 'failed',
            };

            // Save transaction
            $txStmt = db()->prepare("SELECT id FROM transactions WHERE order_id = ?");
            $txStmt->execute([$order['id']]);
            if ($txStmt->fetch()) {
                $db->prepare("UPDATE transactions SET digi_ref_id=?,digi_buyer_sku=?,status=?,message=?,sn=?,price=?,raw_response=?,updated_at=NOW() WHERE order_id=?")
                   ->execute([
                       $digiData['ref_id'] ?? null, $digiData['buyer_sku_code'] ?? null,
                       $digiStatus, $digiData['message'] ?? null, $digiData['sn'] ?? null,
                       $digiData['price'] ?? null, json_encode($digiResp), $order['id'],
                   ]);
            } else {
                $db->prepare("INSERT INTO transactions (order_id,digi_ref_id,digi_buyer_sku,status,message,sn,price,raw_response) VALUES (?,?,?,?,?,?,?,?)")
                   ->execute([
                       $order['id'], $digiData['ref_id'] ?? null, $digiData['buyer_sku_code'] ?? null,
                       $digiStatus, $digiData['message'] ?? null, $digiData['sn'] ?? null,
                       $digiData['price'] ?? null, json_encode($digiResp),
                   ]);
            }

            // Update order final status
            if ($finalStatus !== 'processing') {
                $db->prepare("UPDATE orders SET status=?, updated_at=NOW() WHERE id=?")->execute([$finalStatus, $order['id']]);
            }

            // If failed → schedule refund (implementasi manual/otomatis)
            if ($finalStatus === 'failed') {
                $db->prepare("UPDATE orders SET status='refunded', updated_at=NOW() WHERE id=?")->execute([$order['id']]);
                // TODO: trigger Midtrans refund API di sini
            }
        }
    }

    $db->commit();
    http_response_code(200);
    echo 'OK';
} catch (Exception $e) {
    $db->rollBack();
    error_log('[ftarastore callback] ' . $e->getMessage());
    http_response_code(500);
}
