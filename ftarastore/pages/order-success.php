<?php
require_once __DIR__.'/../includes/functions.php';
$code=trim($_GET['code']??'');
$order=$code?getOrderByCode($code):null;
$pageTitle='Status Order — '.siteName();
include __DIR__.'/../includes/header.php';
$isSuccess=$order&&$order['status']==='success';
$isFailed=$order&&in_array($order['status'],['failed','refunded']);
$isPending=$order&&!$isSuccess&&!$isFailed;
?>
<div class="success-page">
  <?php if(!$order): ?>
  <div class="success-card"><div class="success-head failed"><div class="success-icon">❓</div><div class="success-title">Order Tidak Ditemukan</div></div>
    <div class="success-body" style="text-align:center;padding:32px">
      <a href="<?=asset('index.php')?>" class="btn-submit" style="display:inline-block;width:auto;padding:11px 40px;text-decoration:none">Kembali ke Beranda</a>
    </div>
  </div>
  <?php else: ?>
  <div class="success-card">
    <div class="success-head <?=$isSuccess?'':($isFailed?'failed':'pending')?>">
      <div class="success-icon"><?=$isSuccess?'✅':($isFailed?'❌':'⏳')?></div>
      <div class="success-title"><?=$isSuccess?'Top Up Berhasil!':($isFailed?'Top Up Gagal':'Pembayaran Diterima')?></div>
      <div style="font-size:.82rem;margin-top:4px;opacity:.7"><?=$isSuccess?'Item berhasil masuk ke akun kamu':($isFailed?'Refund akan diproses otomatis':'Top up sedang diproses...')?></div>
      <div class="success-code" style="margin-top:8px">Kode Order: <strong><?=htmlspecialchars($order['order_code'])?></strong></div>
    </div>
    <div class="success-body">
      <div class="drow"><span class="drow-label">Produk</span><span class="drow-val"><strong><?=htmlspecialchars($order['product_name'])?></strong></span></div>
      <div class="drow"><span class="drow-label">ID Akun</span><span class="drow-val"><?=htmlspecialchars($order['game_user_id'])?></span></div>
      <div class="drow"><span class="drow-label">Total</span><span class="drow-val" style="color:var(--gold);font-family:var(--f-title);font-weight:700;font-size:1.05rem"><?=formatRupiah($order['amount'])?></span></div>
      <div class="drow" style="border:none"><span class="drow-label">Tanggal</span><span class="drow-val"><?=date('d M Y H:i',strtotime($order['created_at']))?></span></div>
      <div style="display:flex;gap:10px;margin-top:20px;flex-wrap:wrap">
        <a href="<?=asset('index.php')?>" class="btn-submit" style="flex:1;display:flex;align-items:center;justify-content:center;gap:6px;text-decoration:none">🏠 Kembali ke Beranda</a>
        <a href="<?=asset('pages/invoice.php')?>?code=<?=urlencode($order['order_code'])?>" class="btn-ghost" style="flex:1;display:flex;align-items:center;justify-content:center;gap:6px;border-radius:10px;padding:13px;text-decoration:none;">🧾 Lihat Invoice</a>
        <a href="<?=asset('pages/cek-transaksi.php')?>?code=<?=urlencode($order['order_code'])?>" class="btn-ghost" style="flex:1;display:flex;align-items:center;justify-content:center;gap:6px;border-radius:10px;padding:13px">🔍 Cek Status</a>
      </div>
      <?php if($isFailed): ?>
      <div style="margin-top:16px;padding:12px 14px;background:rgba(239,68,68,.07);border:1px solid rgba(239,68,68,.15);border-radius:8px;font-size:.82rem;color:#fca5a5;text-align:center">
        Butuh bantuan? <a href="https://wa.me/<?=getSetting('whatsapp_number','62')?>" style="color:var(--gold);font-weight:600">Hubungi CS →</a>
      </div>
      <?php endif; ?>
    </div>
  </div>
  <?php endif; ?>
</div>
<?php include __DIR__.'/../includes/footer.php'; ?>
