<?php
require_once __DIR__.'/../includes/functions.php';
Security::boot();

$slug = Security::cleanInput($_GET['slug'] ?? '');
if (!$slug) { header('Location: '.asset('index.php')); exit; }

$db  = db();
$stmt = $db->prepare("
    SELECT g.*, c.name AS cat_name, c.slug AS cat_slug
    FROM games g
    JOIN categories c ON c.id = g.category_id
    WHERE g.slug = ? AND g.is_active = 1
");
$stmt->execute([$slug]);
$game = $stmt->fetch();
if (!$game) { header('Location: '.asset('index.php')); exit; }

// Ambil produk aktif
$prodStmt = $db->prepare("SELECT * FROM products WHERE game_id=? AND is_active=1 ORDER BY sort_order ASC, price_sell ASC");
$prodStmt->execute([$game['id']]);
$products = $prodStmt->fetchAll();

// Deteksi tipe kategori
$catSlug = strtolower($game['cat_slug'] ?? '');
$catName = $game['cat_name'] ?? '';

$isGame        = str_contains($catSlug, 'game')        || str_contains($catName, 'Game');
$isPulsa       = str_contains($catSlug, 'pulsa')       || str_contains($catName, 'Pulsa');
$isTagihan     = str_contains($catSlug, 'tagihan')     || str_contains($catName, 'Tagihan')
              || str_contains($catSlug, 'ppob')        || str_contains($catName, 'PPOB')
              || str_contains($catSlug, 'pln')         || str_contains($catName, 'PLN');
$isVoucher     = str_contains($catSlug, 'voucher')     || str_contains($catName, 'Voucher');
$isEntertain   = str_contains($catSlug, 'entertain')   || str_contains($catName, 'Entertainment')
              || str_contains($catSlug, 'streaming')   || str_contains($catName, 'Streaming');

// Default ke game jika tidak cocok
if (!$isPulsa && !$isTagihan && !$isVoucher && !$isEntertain) $isGame = true;

// Identifikasi operator pulsa
$gameName = strtolower($game['name']);
$isIndosat    = str_contains($gameName, 'indosat') || str_contains($gameName, 'im3') || str_contains($gameName, 'ooredoo');
$isXL         = str_contains($gameName, 'xl') || str_contains($gameName, 'axis');
$isTelkomsel  = str_contains($gameName, 'telkomsel') || str_contains($gameName, 'simpati') || str_contains($gameName, 'kartu as');
$isByU        = str_contains($gameName, 'byu') || str_contains($gameName, 'by.u');
$isTri        = str_contains($gameName, 'tri') || str_contains($gameName, '3');
$isSmartfren  = str_contains($gameName, 'smartfren');
$isBiznet     = str_contains($gameName, 'biznet');
$isPLN        = str_contains($gameName, 'pln') || str_contains($gameName, 'listrik') || str_contains($gameName, 'token');
$isBPJS       = str_contains($gameName, 'bpjs');
$isPDAM       = str_contains($gameName, 'pdam');
$isSpotify    = str_contains($gameName, 'spotify');
$isNetflix    = str_contains($gameName, 'netflix');
$isVidio      = str_contains($gameName, 'vidio');
$isYoutube    = str_contains($gameName, 'youtube');

$pageTitle = 'Top Up '.$game['name'].' — '.siteName();
include __DIR__.'/../includes/header.php';
?>
<style>
.gp-wrap{max-width:1100px;margin:0 auto;padding:20px 24px 60px;}
.gp-hero{background:linear-gradient(135deg,#0a0c1a,#0e1220,#080a14);border-bottom:1px solid var(--b1);padding:24px 0 20px;margin-bottom:0;position:relative;overflow:hidden;}
.gp-hero-inner{max-width:1100px;margin:0 auto;padding:0 24px;}
.gp-body{display:grid;grid-template-columns:1fr 360px;gap:20px;margin-top:20px;}
.gp-left{display:flex;flex-direction:column;gap:16px;}
.gp-right{position:sticky;top:80px;align-self:flex-start;}
.step-box{background:var(--card);border:1px solid var(--b1);border-radius:14px;overflow:hidden;}
.step-head{padding:14px 18px;border-bottom:1px solid var(--b1);display:flex;align-items:center;gap:10px;}
.step-num{width:26px;height:26px;border-radius:50%;background:var(--red);color:#fff;font-size:.75rem;font-weight:800;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
.step-title{font-size:.9rem;font-weight:700;color:var(--t1);}
.step-body{padding:18px;}
.p-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;}
.p-card{background:var(--card2);border:2px solid var(--b1);border-radius:10px;padding:12px 10px;cursor:pointer;transition:all .18s;text-align:center;}
.p-card:hover{border-color:var(--red);background:rgba(227,24,55,.04);}
.p-card.sel{border-color:var(--red);background:rgba(227,24,55,.07);box-shadow:0 0 0 1px rgba(227,24,55,.3);}
.p-name{font-size:.78rem;font-weight:600;color:var(--t1);margin-bottom:5px;line-height:1.3;}
.p-price{font-size:.95rem;font-weight:800;color:var(--red);}
.p-orig{font-size:.66rem;color:var(--t3);text-decoration:line-through;}
.finput{width:100%;background:var(--input);border:1.5px solid var(--b2);border-radius:9px;padding:11px 14px;color:var(--t1);font-size:.88rem;font-family:var(--f-body);outline:none;transition:border-color .15s;}
.finput:focus{border-color:var(--red);box-shadow:0 0 0 3px rgba(227,24,55,.08);}
.order-panel{background:var(--card);border:1.5px solid var(--b1);border-radius:14px;padding:18px;}
.drow{display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid var(--b0);font-size:.82rem;}
.drow:last-child{border-bottom:none;}
.drow-label{color:var(--t3);}
.drow-val{color:var(--t1);font-weight:600;text-align:right;}
.total-row{background:var(--card2);border-radius:9px;padding:12px 14px;display:flex;justify-content:space-between;align-items:center;margin-top:12px;}
.total-label{font-size:.82rem;font-weight:700;color:var(--t2);}
.total-amount{font-size:1.2rem;font-weight:800;color:var(--red);}
.btn-pay{width:100%;padding:13px;background:linear-gradient(135deg,var(--red),var(--red2));color:#fff;font-size:.95rem;font-weight:800;border:none;border-radius:10px;cursor:pointer;margin-top:12px;transition:all .2s;font-family:var(--f-body);}
.btn-pay:hover:not(:disabled){filter:brightness(1.1);transform:translateY(-1px);}
.btn-pay:disabled{opacity:.45;cursor:not-allowed;transform:none;}
@media(max-width:768px){
  .gp-wrap { padding: 0 12px 80px !important; }
  .gp-hero-inner { padding: 14px 12px !important; }
  .gp-body {
    display: flex !important;
    flex-direction: column !important;
    gap: 12px !important;
    margin-top: 12px !important;
    padding: 0 !important;
  }
  .gp-left { order: 1 !important; }
  .gp-right {
    order: 2 !important;
    position: static !important;
    bottom: auto !important;
    border-radius: 14px !important;
    padding: 0 !important;
    z-index: auto !important;
    box-shadow: none !important;
    background: transparent !important;
    border: none !important;
  }
  .order-panel {
    background: var(--card) !important;
    border: 1px solid var(--b1) !important;
    border-radius: 14px !important;
    padding: 16px !important;
  }
  .order-details { display: flex !important; flex-direction: column !important; }
  .p-grid { grid-template-columns: repeat(2, 1fr) !important; gap: 8px !important; }
  .step-body { padding: 14px !important; }
  .step-head { padding: 12px 14px !important; }
  .total-row { padding: 10px 12px !important; }
  .total-amount { font-size: 1.1rem !important; }
  .btn-pay { padding: 12px !important; font-size: .9rem !important; }
}
.info-box{background:rgba(59,130,246,.06);border:1px solid rgba(59,130,246,.15);border-radius:8px;padding:10px 14px;font-size:.76rem;color:#60a5fa;display:flex;gap:8px;align-items:flex-start;}
.warn-box{background:rgba(245,158,11,.06);border:1px solid rgba(245,158,11,.15);border-radius:8px;padding:10px 14px;font-size:.76rem;color:#fbbf24;display:flex;gap:8px;align-items:flex-start;}
.operator-prefix{display:flex;flex-wrap:wrap;gap:5px;margin-top:8px;}
.op-badge{font-size:.66rem;padding:2px 8px;border-radius:20px;background:var(--card2);border:1px solid var(--b1);color:var(--t2);}
.bc a,.bc span{font-size:.75rem;color:var(--t3);text-decoration:none;}
.bc a:hover{color:var(--red);}
.bc svg{color:var(--t3);}
@media(max-width:768px){
  .gp-body{grid-template-columns:1fr;}
  .gp-right{position:fixed;bottom:0;left:0;right:0;background:var(--bg-nav);border-top:1px solid var(--b1);border-radius:14px 14px 0 0;padding:14px 16px;z-index:600;box-shadow:0 -4px 24px rgba(0,0,0,.4);}
  .gp-wrap{padding:0 0 120px;}
  .gp-hero-inner,.step-body,.step-head{padding-left:14px;padding-right:14px;}
  .p-grid{grid-template-columns:repeat(2,1fr);}
  .order-panel{background:transparent;border:none;padding:0;}
  .order-details{display:none;}
}
</style>

<!-- Hero -->
<div class="gp-hero" id="gp-hero-wrap">
  <?php if($game['image_url']): ?>
  <div style="position:absolute;inset:0;background-image:url('<?=htmlspecialchars($game['image_url'])?>');background-size:cover;background-position:center top;opacity:.15;filter:blur(3px);z-index:0;"></div>
  <?php endif; ?>
  <div style="position:absolute;inset:0;background:linear-gradient(to right,rgba(10,12,25,.95) 0%,rgba(10,12,25,.7) 60%,rgba(10,12,25,.4) 100%);z-index:0;"></div>
  <div class="gp-hero-inner" style="position:relative;z-index:1;">
    <!-- Breadcrumb -->
    <div class="bc" style="display:flex;align-items:center;gap:6px;margin-bottom:14px;">
      <a href="<?=asset('index.php')?>">Beranda</a>
      <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="m9 18 6-6-6-6"/></svg>
      <a href="<?=asset('index.php')?>">Top Up</a>
      <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="m9 18 6-6-6-6"/></svg>
      <span>Top Up <?=htmlspecialchars($game['name'])?></span>
    </div>
    <!-- Game info -->
    <div style="display:flex;align-items:center;gap:16px;">
      <?php if($game['image_url']): ?>
      <img src="<?=htmlspecialchars($game['image_url'])?>" style="width:72px;height:72px;border-radius:14px;object-fit:cover;border:1.5px solid var(--b1);flex-shrink:0;" onerror="this.style.display='none'"/>
      <?php endif; ?>
      <div>
        <h1 style="font-family:var(--f-display);font-size:1.6rem;font-weight:800;margin:0 0 4px;"><?=htmlspecialchars($game['name'])?></h1>
        <?php if($game['publisher']): ?>
        <div style="font-size:.78rem;color:var(--t3);"><?=htmlspecialchars($game['publisher'])?></div>
        <?php endif; ?>
        <div style="display:flex;gap:8px;margin-top:8px;flex-wrap:wrap;">
          <span style="display:inline-flex;align-items:center;gap:4px;font-size:.72rem;font-weight:600;padding:3px 10px;border-radius:20px;background:rgba(227,24,55,.1);color:var(--red);border:1px solid rgba(227,24,55,.2);">⚡ Proses Instan</span>
          <span style="display:inline-flex;align-items:center;gap:4px;font-size:.72rem;font-weight:600;padding:3px 10px;border-radius:20px;background:rgba(16,185,129,.08);color:#34d399;border:1px solid rgba(16,185,129,.2);">🔒 Terjamin Aman</span>
          <span style="display:inline-flex;align-items:center;gap:4px;font-size:.72rem;font-weight:600;padding:3px 10px;border-radius:20px;background:rgba(255,255,255,.05);color:var(--t2);border:1px solid var(--b2);">💬 24/7 Support</span>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="gp-wrap">
  <?php if(isAdmin()): ?>
  <div style="background:rgba(227,24,55,.06);border:1px solid rgba(227,24,55,.2);border-radius:8px;padding:10px 16px;font-size:.8rem;color:var(--red);text-align:center;margin-bottom:16px;font-weight:600;">
    Mode Admin — Tidak dapat melakukan order
  </div>
  <?php endif; ?>

  <div class="gp-body">
    <div class="gp-left">

      <!-- ════ STEP 1: Input ID — berbeda per kategori ════ -->
      <div class="step-box">
        <div class="step-head">
          <div class="step-num">1</div>
          <div class="step-title">
            <?php if($isPulsa): ?>Masukkan Nomor HP
            <?php elseif($isTagihan && $isPLN): ?>Masukkan Nomor Meter / ID Pelanggan PLN
            <?php elseif($isTagihan && $isBPJS): ?>Masukkan Nomor VA BPJS
            <?php elseif($isTagihan && $isPDAM): ?>Masukkan Nomor Pelanggan PDAM
            <?php elseif($isTagihan): ?>Masukkan Nomor Pelanggan
            <?php elseif($isVoucher): ?>Masukkan Email / ID Akun
            <?php elseif($isEntertain && $isSpotify): ?>Masukkan Email Akun Spotify
            <?php elseif($isEntertain): ?>Masukkan Email Akun
            <?php else: ?>Masukkan ID Akun Game
            <?php endif; ?>
          </div>
        </div>
        <div class="step-body">

          <?php if($isPulsa): ?>
          <!-- PULSA & DATA -->
          <div class="info-box" style="margin-bottom:14px;">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;margin-top:1px;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            Pastikan nomor yang diisi adalah nomor aktif
            <?php if($isIndosat): ?>(Indosat / IM3 / Ooredoo)<?php endif; ?>
            <?php if($isXL): ?>(XL / AXIS)<?php endif; ?>
            <?php if($isTelkomsel): ?>(Telkomsel / Simpati / Kartu AS / Loop)<?php endif; ?>
            <?php if($isByU): ?>(by.U)<?php endif; ?>
            <?php if($isTri): ?>(Tri / 3)<?php endif; ?>
            <?php if($isSmartfren): ?>(Smartfren)<?php endif; ?>
          </div>
          <div>
            <label style="font-size:.78rem;font-weight:600;color:var(--t2);margin-bottom:6px;display:block;">Nomor HP</label>
            <input type="tel" id="inp-phone" class="finput" placeholder="Contoh: 0812xxxxxxxx" maxlength="14"
                   oninput="onPhoneInput(this)" inputmode="numeric"/>
            <div class="operator-prefix" id="op-badge-wrap">
              <?php if($isIndosat): ?>
              <span class="op-badge">0814</span><span class="op-badge">0815</span><span class="op-badge">0816</span><span class="op-badge">0855</span><span class="op-badge">0856</span><span class="op-badge">0857</span><span class="op-badge">0858</span>
              <?php elseif($isXL): ?>
              <span class="op-badge">0817</span><span class="op-badge">0818</span><span class="op-badge">0819</span><span class="op-badge">0859</span><span class="op-badge">0877</span><span class="op-badge">0878</span><span class="op-badge">0831</span><span class="op-badge">0832</span><span class="op-badge">0833</span><span class="op-badge">0838</span>
              <?php elseif($isTelkomsel): ?>
              <span class="op-badge">0811</span><span class="op-badge">0812</span><span class="op-badge">0813</span><span class="op-badge">0821</span><span class="op-badge">0822</span><span class="op-badge">0823</span><span class="op-badge">0852</span><span class="op-badge">0853</span>
              <?php elseif($isTri): ?>
              <span class="op-badge">0895</span><span class="op-badge">0896</span><span class="op-badge">0897</span><span class="op-badge">0898</span><span class="op-badge">0899</span>
              <?php elseif($isSmartfren): ?>
              <span class="op-badge">0881</span><span class="op-badge">0882</span><span class="op-badge">0883</span><span class="op-badge">0884</span><span class="op-badge">0885</span><span class="op-badge">0886</span><span class="op-badge">0887</span><span class="op-badge">0888</span><span class="op-badge">0889</span>
              <?php endif; ?>
            </div>
          </div>

          <?php elseif($isTagihan): ?>
          <!-- TAGIHAN / PPOB -->
          <?php if($isPLN): ?>
          <div class="info-box" style="margin-bottom:14px;">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;margin-top:1px;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            Token Listrik Prabayar — Masukkan nomor meter 11-13 digit atau ID pelanggan PLN
          </div>
          <?php elseif($isBPJS): ?>
          <div class="info-box" style="margin-bottom:14px;">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;margin-top:1px;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            BPJS Kesehatan — Masukkan nomor VA atau nomor peserta BPJS (13 digit)
          </div>
          <?php elseif($isPDAM): ?>
          <div class="info-box" style="margin-bottom:14px;">⚠️ Masukkan nomor pelanggan PDAM sesuai yang tertera di tagihan.</div>
          <?php endif; ?>
          <div>
            <label style="font-size:.78rem;font-weight:600;color:var(--t2);margin-bottom:6px;display:block;">
              <?php if($isPLN): ?>Nomor Meter / ID Pelanggan
              <?php elseif($isBPJS): ?>Nomor VA BPJS (13 digit)
              <?php elseif($isPDAM): ?>Nomor Pelanggan PDAM
              <?php else: ?>Nomor Pelanggan
              <?php endif; ?>
            </label>
            <input type="text" id="inp-phone" class="finput"
                   placeholder="<?php if($isPLN) echo 'Contoh: 51234567890'; elseif($isBPJS) echo 'Contoh: 0001234567890'; else echo 'Nomor pelanggan...'; ?>"
                   maxlength="20" oninput="onPhoneInput(this)" inputmode="numeric"/>
          </div>

          <?php elseif($isEntertain): ?>
          <!-- ENTERTAINMENT / STREAMING -->
          <div class="info-box" style="margin-bottom:14px;">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;margin-top:1px;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <?php if($isSpotify): ?>Langganan Spotify Premium. Masukkan email akun Spotify aktif.
            <?php elseif($isNetflix): ?>Masukkan email akun Netflix yang akan diperpanjang.
            <?php elseif($isVidio): ?>Masukkan email akun Vidio atau kamu akan mendapat kode redeem.
            <?php elseif($isYoutube): ?>Masukkan email Google/Gmail yang terdaftar di YouTube.
            <?php else: ?>Masukkan email akun yang akan diisi/diperpanjang.
            <?php endif; ?>
          </div>
          <div>
            <label style="font-size:.78rem;font-weight:600;color:var(--t2);margin-bottom:6px;display:block;">Email Akun</label>
            <input type="email" id="inp-phone" class="finput"
                   placeholder="email@example.com" oninput="onPhoneInput(this)"/>
          </div>

          <?php elseif($isVoucher): ?>
          <!-- VOUCHER GAME -->
          <div class="info-box" style="margin-bottom:14px;">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;margin-top:1px;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            Kode voucher akan dikirim ke email kamu setelah pembayaran berhasil.
          </div>
          <div>
            <label style="font-size:.78rem;font-weight:600;color:var(--t2);margin-bottom:6px;display:block;">Email Penerima Voucher</label>
            <input type="email" id="inp-phone" class="finput"
                   placeholder="email@example.com" oninput="onPhoneInput(this)"/>
          </div>

          <?php else: ?>
          <!-- GAME (default) -->
          <?php if($game['has_server_id']): ?>
          <div class="info-box" style="margin-bottom:14px;">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;margin-top:1px;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            Game ini membutuhkan User ID + Server ID
          </div>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
            <div>
              <label style="font-size:.78rem;font-weight:600;color:var(--t2);margin-bottom:5px;display:flex;align-items:center;gap:6px;">
                User ID
                <a href="#" onclick="return false;" style="font-size:.66rem;color:var(--red);">❓ Cara cek</a>
              </label>
              <input type="text" id="inp-phone" class="finput" placeholder="Masukkan User ID" oninput="onPhoneInput(this)"/>
            </div>
            <div>
              <label style="font-size:.78rem;font-weight:600;color:var(--t2);margin-bottom:5px;display:block;">Server ID</label>
              <input type="text" id="inp-server" class="finput" placeholder="Server ID" oninput="onServerInput(this)"/>
            </div>
          </div>
          <?php else: ?>
          <div>
            <label style="font-size:.78rem;font-weight:600;color:var(--t2);margin-bottom:5px;display:flex;align-items:center;gap:6px;">
              User ID
              <a href="#" onclick="return false;" style="font-size:.66rem;color:var(--red);">❓ Cara cek</a>
            </label>
            <input type="text" id="inp-phone" class="finput" placeholder="Masukkan User ID" oninput="onPhoneInput(this)"/>
          </div>
          <?php endif; ?>
          <?php endif; ?>

        </div>
      </div><!-- end step 1 -->

      <!-- ════ STEP 2: Pilih Nominal ════ -->
      <div class="step-box">
        <div class="step-head">
          <div class="step-num">2</div>
          <div class="step-title">
            <?php if($isPulsa): ?>Pilih Nominal Pulsa / Paket Data
            <?php elseif($isTagihan): ?>Pilih Nominal
            <?php elseif($isVoucher): ?>Pilih Voucher
            <?php elseif($isEntertain): ?>Pilih Paket Berlangganan
            <?php else: ?>Pilih Nominal
            <?php endif; ?>
          </div>
        </div>
        <div class="step-body">
          <?php if(empty($products)): ?>
          <div style="text-align:center;padding:32px;color:var(--t3);">
            <div style="font-size:2rem;margin-bottom:8px;">📦</div>
            <div style="font-size:.84rem;">Produk belum tersedia</div>
            <div style="font-size:.72rem;margin-top:4px;">Silakan cek kembali.</div>
          </div>
          <?php else: ?>
          <div class="p-grid" id="product-grid">
            <?php foreach($products as $p): ?>
            <div class="p-card" data-id="<?=$p['id']?>" data-name="<?=htmlspecialchars($p['name'],ENT_QUOTES)?>" data-price="<?=(int)$p['price_sell']?>"
                 onclick="selectProduct(this)">
              <?php if($p['image_url'] ?? null): ?>
              <img src="<?=htmlspecialchars($p['image_url'])?>" style="width:32px;height:32px;object-fit:cover;border-radius:6px;margin:0 auto 6px;" onerror="this.style.display='none'"/>
              <?php endif; ?>
              <div class="p-name"><?=htmlspecialchars($p['name'])?></div>
              <?php if($p['price_cost'] ?? 0): ?>
              <div class="p-orig"><?=formatRupiah($p['price_cost'])?></div>
              <?php endif; ?>
              <div class="p-price"><?=formatRupiah($p['price_sell'])?></div>
            </div>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>
        </div>
      </div><!-- end step 2 -->

      <!-- ════ STEP 3: Kontak (untuk semua) ════ -->
      <div class="step-box">
        <div class="step-head">
          <div class="step-num">3</div>
          <div class="step-title">Detail Kontak</div>
        </div>
        <div class="step-body">
          <div class="fg" style="margin-bottom:12px;">
            <label style="font-size:.78rem;font-weight:600;color:var(--t2);margin-bottom:5px;display:block;">Email (opsional) — untuk notifikasi order</label>
            <input type="email" id="inp-email" class="finput" placeholder="email@example.com" oninput="updatePanel()"/>
          </div>
          <?php if(!empty(getSetting('whatsapp_number',''))): ?>
          <div class="warn-box" style="margin-top:4px;">
            <span style="font-size:.9rem;">💬</span>
            <span>Ada masalah? <a href="https://wa.me/<?=getSetting('whatsapp_number','')?>" target="_blank" style="color:var(--gold);font-weight:700;">Chat CS via WhatsApp</a></span>
          </div>
          <?php endif; ?>
        </div>
      </div><!-- end step 3 -->

    </div><!-- end gp-left -->

    <!-- ════ ORDER PANEL ════ -->
    <div class="gp-right">
      <div class="order-panel">
        <div style="font-size:.82rem;font-weight:700;color:var(--t2);margin-bottom:14px;">Detail Pembelian</div>
        <!-- Game info -->
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:14px;padding-bottom:14px;border-bottom:1px solid var(--b1);">
          <?php if($game['image_url']): ?>
          <img src="<?=htmlspecialchars($game['image_url'])?>" style="width:40px;height:40px;border-radius:9px;object-fit:cover;flex-shrink:0;" onerror="this.style.display='none'"/>
          <?php endif; ?>
          <div>
            <div style="font-size:.84rem;font-weight:700;"><?=htmlspecialchars($game['name'])?></div>
            <div style="font-size:.7rem;color:var(--t3);"><?=htmlspecialchars($game['publisher']??$catName)?></div>
          </div>
        </div>
        <div class="order-details">
          <div class="drow"><span class="drow-label">Produk</span><span class="drow-val" id="dp-product" style="color:var(--t3);">Belum dipilih</span></div>
          <div class="drow"><span class="drow-label" id="dp-id-label">
            <?php if($isPulsa||$isTagihan): ?>Nomor<?php elseif($isEntertain||$isVoucher): ?>Email<?php else: ?>User ID<?php endif; ?>
          </span><span class="drow-val" id="dp-userid">—</span></div>
          <?php if($isGame && $game['has_server_id']): ?>
          <div class="drow"><span class="drow-label">Server</span><span class="drow-val" id="dp-server">—</span></div>
          <?php endif; ?>
        </div>
        <div class="total-row">
          <span class="total-label">Total</span>
          <span class="total-amount" id="dp-total">Rp 0</span>
        </div>
        <?php if(!isAdmin()): ?>
        <?php if(isLoggedIn()): ?>
        <form id="order-form" method="POST" action="<?=asset('pages/checkout.php')?>">
          <input type="hidden" name="_token" value="<?=csrfToken()?>">
          <input type="hidden" name="product_id" id="hid-product">
          <input type="hidden" name="game_user_id" id="hid-userid">
          <input type="hidden" name="server_id" id="hid-server">
          <input type="hidden" name="email" id="hid-email">
          <button type="submit" class="btn-pay" id="btn-pay" disabled>
            💳 Bayar Sekarang
          </button>
        </form>
        <div id="pay-hint" style="text-align:center;font-size:.72rem;color:var(--t3);margin-top:8px;">Pilih nominal & isi <?php if($isPulsa||$isTagihan): ?>nomor<?php elseif($isEntertain||$isVoucher): ?>email<?php else: ?>User ID<?php endif; ?> terlebih dahulu</div>
        <?php else: ?>
        <a href="<?=asset('auth/login.php')?>?redirect=<?=urlencode($_SERVER['REQUEST_URI'])?>" class="btn-pay" style="display:block;text-align:center;text-decoration:none;padding:13px;">
          🔑 Masuk untuk Top Up
        </a>
        <?php endif; ?>
        <?php else: ?>
        <button class="btn-pay" disabled style="opacity:.4;">Mode Admin</button>
        <?php endif; ?>
        <!-- Trust badges -->
        <div style="display:flex;justify-content:center;gap:12px;margin-top:14px;">
          <span style="font-size:.66rem;color:var(--t3);display:flex;align-items:center;gap:3px;">🔒 SSL Aman</span>
          <span style="font-size:.66rem;color:var(--t3);display:flex;align-items:center;gap:3px;">⚡ Instan</span>
          <span style="font-size:.66rem;color:var(--t3);display:flex;align-items:center;gap:3px;">💬 24/7 CS</span>
        </div>
      </div>
    </div><!-- end gp-right -->
  </div><!-- end gp-body -->
</div>

<script>
var selProduct = null;
var inputVal   = '';
var serverVal  = '';

function formatRp(n) {
  return 'Rp ' + parseInt(n).toLocaleString('id-ID');
}

function selectProduct(el) {
  document.querySelectorAll('.p-card').forEach(c => c.classList.remove('sel'));
  el.classList.add('sel');
  selProduct = { id: el.dataset.id, name: el.dataset.name, price: parseInt(el.dataset.price) };
  document.getElementById('dp-product').textContent = selProduct.name;
  document.getElementById('dp-product').style.color = 'var(--t1)';
  updatePanel();
}

function onPhoneInput(el) { inputVal = el.value.trim(); updatePanel(); }
function onServerInput(el) { serverVal = el.value.trim(); updatePanel(); }

function updatePanel() {
  var email = (document.getElementById('inp-email') || {}).value || '';
  document.getElementById('dp-userid').textContent = inputVal || '—';
  var serverEl = document.getElementById('dp-server');
  if(serverEl) serverEl.textContent = serverVal || '—';
  document.getElementById('dp-total').textContent = selProduct ? formatRp(selProduct.price) : 'Rp 0';

  var needServer = <?=$isGame && $game['has_server_id'] ? 'true' : 'false'?>;
  var canPay = selProduct && inputVal.length >= 3 && (!needServer || serverVal.length >= 1);

  var btn = document.getElementById('btn-pay');
  var hint = document.getElementById('pay-hint');
  if(btn) btn.disabled = !canPay;
  if(hint) hint.style.display = canPay ? 'none' : 'block';

  // Fill hidden inputs
  var hidProd = document.getElementById('hid-product');
  var hidUser = document.getElementById('hid-userid');
  var hidSrv  = document.getElementById('hid-server');
  var hidEml  = document.getElementById('hid-email');
  if(hidProd) hidProd.value = selProduct ? selProduct.id : '';
  if(hidUser) hidUser.value = inputVal;
  if(hidSrv)  hidSrv.value  = serverVal;
  if(hidEml)  hidEml.value  = email;
}

// Prevent form submit jika belum lengkap
var orderForm = document.getElementById('order-form');
if(orderForm) orderForm.addEventListener('submit', function(e){
  if(!selProduct || !inputVal) { e.preventDefault(); return; }
});
</script>

<?php include __DIR__.'/../includes/footer.php'; ?>
