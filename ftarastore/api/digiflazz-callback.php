<?php
/* ═══════════════════════════════════════════════════
   FTARASTORE — DigiFlazz Callback Handler
   api/digiflazz-callback.php

   DigiFlazz mengirim POST request ke URL ini
   setiap kali status transaksi berubah.

   Setting di dashboard DigiFlazz:
   Callback URL: https://yourdomain.com/api/digiflazz-callback.php
   ═══════════════════════════════════════════════════ */

require_once __DIR__.'/../includes/functions.php';
require_once __DIR__.'/../includes/notifications.php';

header('Content-Type: application/json');

// Hanya terima POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Baca raw POST body
$rawBody = file_get_contents('php://input');
$data    = json_decode($rawBody, true);

// Log untuk debugging
error_log('[DigiCallback] Raw: ' . $rawBody);

if (!$data || !isset($data['data'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid payload']);
    exit;
}

$d = $data['data'];

// Verifikasi signature DigiFlazz
$sign = md5(DIGI_USERNAME . digiApiKey() . ($d['ref_id'] ?? ''));
if (isset($d['sign']) && $d['sign'] !== $sign) {
    error_log('[DigiCallback] Invalid signature');
    http_response_code(403);
    echo json_encode(['error' => 'Invalid signature']);
    exit;
}

$refId  = $d['ref_id']  ?? null;  // = order_code kita
$status = strtolower($d['status'] ?? '');
$sn     = $d['sn']      ?? null;  // Serial Number (untuk voucher/dll)
$msg    = $d['message'] ?? '';

if (!$refId) {
    http_response_code(400);
    echo json_encode(['error' => 'No ref_id']);
    exit;
}

$db = db();

// Cari order berdasarkan order_code
$orderStmt = $db->prepare("SELECT o.*, p.name AS pname FROM orders o LEFT JOIN products p ON p.id=o.product_id WHERE o.order_code=?");
$orderStmt->execute([$refId]);
$order = $orderStmt->fetch();

if (!$order) {
    error_log('[DigiCallback] Order not found: ' . $refId);
    http_response_code(404);
    echo json_encode(['error' => 'Order not found']);
    exit;
}

// Map status DigiFlazz → status order kita
$newStatus = match($status) {
    'sukses', 'success' => 'success',
    'gagal', 'failed'   => 'failed',
    'pending'           => 'processing',
    default             => 'processing',
};

// Jangan update jika sudah final
if (in_array($order['status'], ['success', 'failed'])) {
    echo json_encode(['ok' => true, 'msg' => 'Already final status: ' . $order['status']]);
    exit;
}

// Update status order
$db->prepare("UPDATE orders SET status=?, updated_at=NOW() WHERE order_code=?")
   ->execute([$newStatus, $refId]);

// Update transaction record
$db->prepare("UPDATE transactions SET status=?, message=?, sn=?, updated_at=NOW() WHERE order_id=?")
   ->execute([$newStatus, $msg, $sn, $order['id']]);

// Notifikasi ke user
$userId = (int)$order['user_id'];
if ($userId > 0) {
    $orderArr = [
        'id'           => $order['id'],
        'order_code'   => $order['order_code'],
        'product_name' => $order['product_name'] ?? $order['pname'] ?? 'Produk',
        'amount'       => $order['amount'],
    ];

    match($newStatus) {
        'success' => Notif::onTransactionSuccess($userId, $orderArr),
        'failed'  => Notif::onTransactionFailed($userId, $orderArr),
        default   => null,
    };
}

// Audit log
Security::audit('DIGI_CALLBACK', "Order $refId → $newStatus. SN: $sn. Msg: $msg");

error_log("[DigiCallback] Order $refId updated to $newStatus");
echo json_encode(['ok' => true, 'status' => $newStatus]);