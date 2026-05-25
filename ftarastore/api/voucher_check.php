<?php
require_once __DIR__.'/../includes/functions.php';
header('Content-Type: application/json');

if($_SERVER['REQUEST_METHOD']!=='POST'){ echo json_encode(['valid'=>false,'message'=>'Invalid request']); exit; }

$input  = json_decode(file_get_contents('php://input'), true);
$code   = strtoupper(trim($input['code']??''));
$amount = (float)($input['amount']??0);

if(!$code){ echo json_encode(['valid'=>false,'message'=>'Kode voucher kosong.']); exit; }

// Rate limiting — max 10 request per menit per IP
try {
    Security::throttle('voucher:'.Security::getIP(), 10, 60);
} catch(\Exception $e) {
    echo json_encode(['valid'=>false,'message'=>'Terlalu banyak percobaan. Coba lagi nanti.']); exit;
}

try {
    $stmt = db()->prepare("SELECT * FROM vouchers WHERE code=? AND is_active=1 AND (expires_at IS NULL OR expires_at>NOW()) AND used_count<quota");
    $stmt->execute([$code]);
    $v = $stmt->fetch();

    if(!$v){ echo json_encode(['valid'=>false,'message'=>'Voucher tidak valid, sudah kadaluarsa, atau habis.']); exit; }

    if($v['min_purchase']>0 && $amount < $v['min_purchase']){
        echo json_encode(['valid'=>false,'message'=>'Min. pembelian '.formatRupiah($v['min_purchase']).' untuk voucher ini.']); exit;
    }

    $discount = 0;
    if($v['type']==='percent'){
        $discount = $amount * ($v['value']/100);
        if($v['max_discount']>0) $discount = min($discount, $v['max_discount']);
        $discount = round($discount);
    } else {
        $discount = min($v['value'], $amount);
    }

    echo json_encode([
        'valid'    => true,
        'discount' => $discount,
        'type'     => $v['type'],
        'value'    => $v['value'],
        'message'  => 'Voucher valid!'
    ]);

} catch(\Exception $e) {
    echo json_encode(['valid'=>false,'message'=>'Terjadi kesalahan sistem.']);
}