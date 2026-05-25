<?php
require_once __DIR__.'/../includes/functions.php';
if(isAdmin()){ setFlash('error','Admin tidak dapat melakukan order.'); header('Location: '.asset('admin/index.php')); exit; }
if($_SERVER['REQUEST_METHOD']!=='POST'){header('Location: '.asset('index.php'));exit;}
verifyCsrf();
$productId  = (int)($_POST['product_id']??0);
$gameUserId = trim($_POST['game_user_id']??'');
$serverId   = trim($_POST['server_id']??'');
$buyerEmail = trim($_POST['buyer_email']??'');
$buyerPhone = trim($_POST['buyer_phone']??'');
$gameSlug    = $_POST['game_slug']??'';
$voucherCode = strtoupper(trim($_POST['voucher_code']??''));

// Validasi & hitung diskon voucher
$voucherDiscount = 0;
$voucherData     = null;
$voucherMsg      = '';
if($voucherCode){
    $vStmt = db()->prepare("SELECT * FROM vouchers WHERE code=? AND is_active=1 AND (expires_at IS NULL OR expires_at > NOW()) AND used_count < quota");
    $vStmt->execute([$voucherCode]);
    $voucherData = $vStmt->fetch();
    if(!$voucherData){
        $voucherMsg = 'Voucher tidak valid atau sudah habis.';
        $voucherCode = '';
    }
}
$errs=[];
if(!$productId) $errs[]='Pilih nominal terlebih dahulu.';
if(!$gameUserId) $errs[]='ID akun game wajib diisi.';
if(!filter_var($buyerEmail,FILTER_VALIDATE_EMAIL)) $errs[]='Email tidak valid.';
if($errs){setFlash('error',implode(' ',$errs));header('Location: '.asset('pages/game.php').'?slug='.$gameSlug);exit;}
$s=$s2=null;
$stmt=db()->prepare("SELECT p.*,g.name AS gname,g.slug AS gslug FROM products p JOIN games g ON g.id=p.game_id WHERE p.id=? AND p.is_active=1");
$stmt->execute([$productId]);
$product=$stmt->fetch();
if(!$product){setFlash('error','Produk tidak ditemukan.');header('Location: '.asset('index.php'));exit;}

// Hitung diskon
$baseAmount = $product['price_sell'];
if($voucherData){
    if($voucherData['min_purchase'] > 0 && $baseAmount < $voucherData['min_purchase']){
        $voucherMsg  = 'Min. pembelian '.formatRupiah($voucherData['min_purchase']).' untuk voucher ini.';
        $voucherData = null;
        $voucherCode = '';
    } else {
        if($voucherData['type']==='percent'){
            $disc = $baseAmount * ($voucherData['value']/100);
            if($voucherData['max_discount'] > 0) $disc = min($disc, $voucherData['max_discount']);
            $voucherDiscount = round($disc);
        } else {
            $voucherDiscount = min($voucherData['value'], $baseAmount);
        }
    }
}
$finalAmount = (int) max(1, $baseAmount - $voucherDiscount);
$orderCode=generateOrderCode();
$db=db();$db->beginTransaction();

// ── Buat order + ambil Snap token ─────────────────────────────
try {
    $db->prepare("INSERT INTO orders (order_code,user_id,product_id,game_user_id,server_id,buyer_email,buyer_phone,product_name,amount) VALUES (?,?,?,?,?,?,?,?,?)")
       ->execute([$orderCode,isLoggedIn()?currentUser()['id']:null,$productId,$gameUserId,$serverId?:null,$buyerEmail,$buyerPhone?:null,$product['name'],$finalAmount]);
    $orderId = $db->lastInsertId();

    // Update voucher used_count
    if($voucherData){
        $db->prepare("UPDATE vouchers SET used_count=used_count+1 WHERE id=?")->execute([$voucherData['id']]);
    }

    $order = ['order_code'=>$orderCode,'amount'=>$finalAmount,'buyer_email'=>$buyerEmail,'buyer_phone'=>$buyerPhone];
    $snap      = midtransCreateSnap($order, $product);
    $snapToken = $snap['token'] ?? null;

    if (!$snapToken) {
        throw new Exception('Token kosong dari Midtrans.');
    }

    $db->prepare("INSERT INTO payments (order_id,snap_token,gross_amount) VALUES (?,?,?)")
       ->execute([$orderId, $snapToken, $finalAmount]);
    $db->commit();

} catch (Exception $e) {
    $db->rollBack();
    error_log('[Checkout] ' . $e->getMessage());
    setFlash('error', 'Gagal membuat pembayaran: ' . $e->getMessage());
    header('Location: ' . asset('pages/game.php') . '?slug=' . $gameSlug);
    exit;
}

$pageTitle='Pembayaran '.$orderCode.' — '.siteName();
include __DIR__.'/../includes/header.php';
?>
<div class="checkout-wrap">
  <nav class="bc"><a href="<?=asset('index.php')?>">Beranda</a><span class="bc-sep">›</span><a href="<?=asset('pages/game.php')?>?slug=<?=htmlspecialchars($product['gslug'])?>">Top Up <?=htmlspecialchars($product['gname'])?></a><span class="bc-sep">›</span><span>Pembayaran</span></nav>

  <div class="ck-card" style="margin-top:16px">
    <div class="ck-head">
      <h2>Konfirmasi Pembayaran</h2>
      <div class="order-ref">Kode Order: <strong><?=$orderCode?></strong></div>
    </div>
    <div class="ck-body">
      <div class="drow"><span class="drow-label">Produk</span><span class="drow-val"><strong><?=htmlspecialchars($product['name'])?></strong></span></div>
      <div class="drow"><span class="drow-label">Game</span><span class="drow-val"><?=htmlspecialchars($product['gname'])?></span></div>
      <div class="drow"><span class="drow-label">ID Akun</span><span class="drow-val"><?=htmlspecialchars($gameUserId)?><?=$serverId?' / '.htmlspecialchars($serverId):''?></span></div>
      <div class="drow"><span class="drow-label">Email Struk</span><span class="drow-val"><?=htmlspecialchars($buyerEmail)?></span></div>
      <!-- Voucher info (read-only) -->
      <div style="margin:14px 0;padding:14px;background:var(--card2);border-radius:10px;border:1.5px solid var(--b2);">
        <div style="font-size:.78rem;font-weight:700;color:var(--t2);margin-bottom:8px;display:flex;align-items:center;gap:6px;">
          <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 12v10H4V12"/><path d="M22 7H2v5h20V7z"/><path d="M12 22V7"/><path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"/><path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"/></svg>
          Kode Voucher
        </div>
        <?php if($voucherCode && $voucherData): ?>
        <div style="display:flex;align-items:center;gap:10px;padding:10px 14px;background:rgba(34,211,160,.06);border:1.5px solid rgba(34,211,160,.2);border-radius:8px;">
          <svg width="14" height="14" fill="none" stroke="#22d3a0" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
          <div>
            <div style="font-family:var(--f-display);font-weight:800;color:#22d3a0;letter-spacing:1px;font-size:.88rem;"><?=htmlspecialchars($voucherCode)?></div>
            <div style="font-size:.72rem;color:var(--t3);margin-top:1px;">
              Hemat <?=$voucherData['type']==='percent'?$voucherData['value'].'%':formatRupiah($voucherData['value'])?>
              <?php if($voucherData['description']): ?> · <?=htmlspecialchars($voucherData['description'])?><?php endif; ?>
            </div>
          </div>
        </div>
        <?php else: ?>
        <div style="font-size:.8rem;color:var(--t3);display:flex;align-items:center;justify-content:space-between;">
          <span>Tidak ada voucher digunakan.</span>
          <a href="<?=asset('pages/game.php')?>?slug=<?=htmlspecialchars($product['gslug'])?>" style="font-size:.76rem;color:#0ea5e9;">← Kembali untuk pakai voucher</a>
        </div>
        <?php endif; ?>
      </div>

      <!-- Rincian harga -->
      <?php if($voucherDiscount > 0): ?>
      <div class="drow" style="color:var(--t3);">
        <span class="drow-label">Harga Normal</span>
        <span class="drow-val"><?=formatRupiah($baseAmount)?></span>
      </div>
      <div class="drow">
        <span class="drow-label" style="color:#22d3a0;">Diskon Voucher</span>
        <span class="drow-val" style="color:#22d3a0;font-weight:700;">- <?=formatRupiah($voucherDiscount)?></span>
      </div>
      <?php endif; ?>

      <div class="total-row">
        <span class="total-label">Total Pembayaran</span>
        <span class="total-amount"><?=formatRupiah($finalAmount)?></span>
      </div>
      <div class="pay-methods">
        <div class="pay-methods-title">Metode Pembayaran Tersedia</div>
        <div class="pay-badges">
          <span class="pay-badge">QRIS</span><span class="pay-badge">GoPay</span><span class="pay-badge">OVO</span>
          <span class="pay-badge">Dana</span><span class="pay-badge">BCA VA</span><span class="pay-badge">BRI VA</span>
          <span class="pay-badge">Mandiri</span><span class="pay-badge">Indomaret</span><span class="pay-badge">Alfamart</span>
        </div>
      </div>
      <?php if($snapToken): ?>
      <button id="pay-btn" class="btn-pay">
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
        Bayar Sekarang — <?=formatRupiah($finalAmount)?>
      </button>
      <script src="<?=midtransSnapUrl()?>" data-client-key="<?=midtransClientKey()?>"></script>
      <script>
      document.getElementById('pay-btn').addEventListener('click',function(){
        this.textContent='Membuka payment...';
        snap.pay('<?=$snapToken?>',{
          onSuccess:()=>location.href='<?=asset('pages/order-success.php')?>?code=<?=$orderCode?>',
          onPending:()=>location.href='<?=asset('pages/order-success.php')?>?code=<?=$orderCode?>',
          onError:()=>{document.getElementById('pay-btn').textContent='⚠️ Gagal — Coba Lagi';document.getElementById('pay-btn').style.background='var(--red)'},
          onClose:()=>{document.getElementById('pay-btn').textContent='Bayar Sekarang — <?=formatRupiah($finalAmount)?>'}
        });
      });
      </script>
      <?php else: ?>
      <div style="background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.2);border-radius:8px;padding:14px;text-align:center;color:#fca5a5;font-size:.88rem">
        ⚠️ Gagal terhubung ke payment gateway. <a href="https://wa.me/<?=getSetting('whatsapp_number','62')?>" style="color:var(--gold);font-weight:600">Hubungi CS →</a>
      </div>
      <?php endif; ?>
      <div style="text-align:center;margin-top:16px">
        <a href="<?=asset('pages/cek-transaksi.php')?>?code=<?=$orderCode?>" style="font-size:.82rem;color:var(--t3)">🔍 Cek status transaksi</a>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__.'/../includes/footer.php'; ?>