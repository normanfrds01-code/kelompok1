<?php
require_once __DIR__.'/../includes/functions.php';
$code  = trim($_GET['code'] ?? '');
$order = $code ? getOrderByCode($code) : null;

if(!$order){ header('Location: '.asset('pages/cek-transaksi.php')); exit; }

// Ambil detail game dari produk
$prod = db()->prepare("SELECT p.*, g.name AS game_name, g.image_url AS game_img FROM products p JOIN games g ON g.id=p.game_id WHERE p.id=?");
$prod->execute([$order['product_id'] ?? 0]);
$product = $prod->fetch() ?: [];

$siteName    = siteName();
$waNumber    = getSetting('whatsapp_number','62');
$invoiceNo   = 'INV-'.strtoupper(substr(md5($order['order_code']),0,8));
$statusLabel = match($order['status']){
    'success'    => ['text'=>'LUNAS', 'color'=>'#059669'],
    'paid'       => ['text'=>'DIBAYAR', 'color'=>'#aa0000'],
    'processing' => ['text'=>'DIPROSES', 'color'=>'#e07800'],
    'pending'    => ['text'=>'PENDING', 'color'=>'#e07800'],
    'failed'     => ['text'=>'GAGAL', 'color'=>'#dc2626'],
    'refunded'   => ['text'=>'REFUND', 'color'=>'#7c3aed'],
    default      => ['text'=>'UNKNOWN', 'color'=>'#6b7280'],
};
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1"/>
<title>Invoice <?=htmlspecialchars($order['order_code'])?> — <?=$siteName?></title>
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@300;400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
<style>
  *{margin:0;padding:0;box-sizing:border-box}
  body{font-family:'Inter',sans-serif;background:#f0f7ff;color:#0d2137;min-height:100vh;padding:24px 20px}

  .page-actions{max-width:680px;margin:0 auto 18px;display:flex;gap:10px;flex-wrap:wrap}
  .btn{display:inline-flex;align-items:center;gap:7px;padding:9px 20px;border-radius:8px;font-size:.84rem;font-weight:600;cursor:pointer;transition:all .2s;text-decoration:none;border:none;font-family:'Inter',sans-serif}
  .btn-back{background:white;color:#3a6080;border:1.5px solid #c5dff0}
  .btn-back:hover{border-color:#aa0000;color:#aa0000}
  .btn-print{background:linear-gradient(135deg,#d40000,#aa0000);color:white;box-shadow:0 3px 12px rgba(212,0,0,.35)}
  .btn-print:hover{filter:brightness(1.08);transform:translateY(-1px)}
  .btn-wa{background:linear-gradient(135deg,#22c55e,#16a34a);color:white;box-shadow:0 3px 12px rgba(34,197,94,.3)}
  .btn-wa:hover{filter:brightness(1.08);transform:translateY(-1px)}

  /* INVOICE CARD */
  .invoice{max-width:680px;margin:0 auto;background:white;border-radius:16px;overflow:hidden;box-shadow:0 4px 32px rgba(14,100,180,.12)}

  /* HEADER */
  .inv-head{
    background:linear-gradient(135deg,#0c1a2e,#0f2a4a);
    padding:32px 36px;
    display:flex;justify-content:space-between;align-items:flex-start;
    gap:20px;
  }
  .inv-brand{color:white}
  .inv-brand .name{font-family:'Orbitron',sans-serif;font-size:18px;font-weight:500;letter-spacing:2px}
  .inv-brand .name span{color:#d40000;font-weight:700}
  .inv-brand .tagline{font-size:.75rem;color:rgba(255,255,255,.45);letter-spacing:1px;margin-top:3px;text-transform:uppercase}
  .inv-title-box{text-align:right}
  .inv-title-box .title{font-family:'Orbitron',sans-serif;font-size:17px;font-weight:700;color:white;letter-spacing:2px}
  .inv-title-box .inv-no{font-size:.8rem;color:#ff4d63;margin-top:4px;font-family:'Orbitron',sans-serif;letter-spacing:1px}
  .inv-title-box .inv-date{font-size:.75rem;color:rgba(255,255,255,.5);margin-top:2px}

  /* STATUS BANNER */
  .inv-status{
    background:linear-gradient(135deg,#eaf3ff,#f0f9ff);
    border-bottom:1.5px solid #c5dff0;
    padding:14px 36px;
    display:flex;justify-content:space-between;align-items:center;
  }
  .status-pill{
    display:inline-flex;align-items:center;gap:7px;
    padding:6px 18px;border-radius:20px;font-size:.8rem;font-weight:800;letter-spacing:1px;
  }
  .status-dot{width:8px;height:8px;border-radius:50%;animation:pulse 2s infinite}
  @keyframes pulse{0%,100%{opacity:1;transform:scale(1)}50%{opacity:.6;transform:scale(1.3)}}
  .inv-code{font-family:'Orbitron',sans-serif;font-size:.88rem;color:#3a6080;font-weight:600}
  .inv-code span{color:#aa0000;font-weight:700}

  /* BODY */
  .inv-body{padding:28px 36px}

  /* GAME INFO */
  .game-row{display:flex;align-items:center;gap:16px;padding:18px;background:#f0f7ff;border-radius:12px;margin-bottom:24px;border:1.5px solid #c5dff0}
  .game-img{width:60px;height:60px;border-radius:10px;object-fit:cover;border:1.5px solid #c5dff0;flex-shrink:0}
  .game-img-ph{width:60px;height:60px;border-radius:10px;background:linear-gradient(135deg,#bfdbf7,#fca5a5);display:flex;align-items:center;justify-content:center;flex-shrink:0}
  .game-info .gname{font-family:'Orbitron',sans-serif;font-size:1.05rem;font-weight:800;color:#0d2137}
  .game-info .gprod{font-size:.82rem;color:#3a6080;margin-top:3px}
  .game-info .gcode{font-size:.73rem;color:#7aabb8;margin-top:2px;font-family:'Orbitron',sans-serif}

  /* DETAIL ROWS */
  .section-title{font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:1.5px;color:#7aabb8;margin-bottom:12px;margin-top:22px}
  .drow{display:flex;justify-content:space-between;align-items:flex-start;padding:10px 0;border-bottom:1px solid #eaf3ff;font-size:.86rem;gap:12px}
  .drow:last-child{border-bottom:none}
  .drow .label{color:#7aabb8}
  .drow .val{font-weight:600;color:#0d2137;text-align:right}

  /* TOTAL */
  .total-box{
    margin:18px 0;padding:18px 20px;
    background:linear-gradient(135deg,#0c1a2e,#0f2a4a);
    border-radius:12px;
    display:flex;justify-content:space-between;align-items:center;
  }
  .total-box .tl{font-size:.9rem;color:rgba(255,255,255,.7);font-weight:600}
  .total-box .ta{font-family:'Orbitron',sans-serif;font-size:1.7rem;font-weight:800;color:#d40000}

  /* FOOTER */
  .inv-footer{
    padding:20px 36px;
    border-top:1.5px solid #eaf3ff;
    display:flex;justify-content:space-between;align-items:center;
    flex-wrap:wrap;gap:10px;
    background:#f8fbff;
  }
  .inv-footer .note{font-size:.75rem;color:#7aabb8;max-width:400px;line-height:1.6}
  .inv-footer .note strong{color:#aa0000}
  .inv-qr{text-align:right;font-size:.72rem;color:#7aabb8}
  .inv-qr .qr-code{font-family:'Orbitron',sans-serif;font-size:.9rem;font-weight:700;color:#0d2137;letter-spacing:1px}

  /* PRINT */
  @media print{
    body{background:white;padding:0}
    .page-actions{display:none}
    .invoice{box-shadow:none;border-radius:0;max-width:100%}
  }
  @media(max-width:540px){
    .inv-head{flex-direction:column;gap:14px;padding:24px 22px}
    .inv-title-box{text-align:left}
    .inv-body{padding:20px 22px}
    .inv-footer{flex-direction:column}
    .total-box{flex-direction:column;gap:6px;text-align:center}
  }
</style>
</head>
<body>

<!-- Tombol aksi -->
<div class="page-actions">
  <a href="<?=asset('pages/order-success.php')?>?code=<?=urlencode($code)?>" class="btn btn-back">
    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="m15 18-6-6 6-6"/></svg>
    Kembali
  </a>
  <button onclick="window.print()" class="btn btn-print">
    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 9V2h12v7"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
    Cetak / Save PDF
  </button>
  <?php if($waNumber): ?>
  <a href="https://wa.me/<?=$waNumber?>?text=Halo+CS,+saya+minta+konfirmasi+invoice+<?=urlencode($order['order_code'])?>" target="_blank" class="btn btn-wa">
    <svg width="14" height="14" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413z"/></svg>
    Hubungi CS
  </a>
  <?php endif; ?>
</div>

<!-- INVOICE -->
<div class="invoice">

  <!-- Header -->
  <div class="inv-head">
    <div class="inv-brand">
      <div class="name">fta<span>ra</span>store</div>
      <div class="tagline">Top Up Game Center</div>
    </div>
    <div class="inv-title-box">
      <div class="title">INVOICE</div>
      <div class="inv-no"><?=$invoiceNo?></div>
      <div class="inv-date"><?=date('d F Y, H:i', strtotime($order['created_at']))?>WIB</div>
    </div>
  </div>

  <!-- Status -->
  <div class="inv-status">
    <div>
      <span class="status-pill" style="background:<?=$statusLabel['color']?>22;color:<?=$statusLabel['color']?>;border:1.5px solid <?=$statusLabel['color']?>44">
        <span class="status-dot" style="background:<?=$statusLabel['color']?>"></span>
        <?=$statusLabel['text']?>
      </span>
    </div>
    <div class="inv-code">Kode Order: <span><?=htmlspecialchars($order['order_code'])?></span></div>
  </div>

  <!-- Body -->
  <div class="inv-body">

    <!-- Game info -->
    <div class="game-row">
      <?php if(!empty($product['game_img'])): ?>
        <img src="<?=htmlspecialchars($product['game_img'])?>" class="game-img" alt="game" onerror="this.style.display='none'"/>
      <?php else: ?>
        <div class="game-img-ph">
          <svg width="24" height="24" fill="none" stroke="#3a6080" stroke-width="1.5" viewBox="0 0 24 24"><rect x="2" y="6" width="20" height="12" rx="3"/><path d="M6 12h4m-2-2v4M15 12h.01M18 12h.01"/></svg>
        </div>
      <?php endif; ?>
      <div class="game-info">
        <div class="gname"><?=htmlspecialchars($product['game_name'] ?? $order['product_name'] ?? 'Game')?></div>
        <div class="gprod"><?=htmlspecialchars($order['product_name'])?></div>
        <?php if(!empty($product['digi_code'])): ?>
        <div class="gcode">SKU: <?=htmlspecialchars($product['digi_code'])?></div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Detail transaksi -->
    <div class="section-title">Detail Transaksi</div>

    <div class="drow">
      <span class="label">No. Invoice</span>
      <span class="val" style="font-family:'Orbitron',sans-serif;color:#aa0000"><?=$invoiceNo?></span>
    </div>
    <div class="drow">
      <span class="label">Kode Order</span>
      <span class="val"><?=htmlspecialchars($order['order_code'])?></span>
    </div>
    <div class="drow">
      <span class="label">Tanggal</span>
      <span class="val"><?=date('d M Y H:i', strtotime($order['created_at']))?></span>
    </div>
    <?php if($order['pay_status']): ?>
    <div class="drow">
      <span class="label">Metode Bayar</span>
      <span class="val"><?=htmlspecialchars(strtoupper($order['payment_method'] ?? 'Online'))?></span>
    </div>
    <?php endif; ?>

    <!-- Detail pembeli -->
    <div class="section-title">Informasi Pembeli</div>

    <div class="drow">
      <span class="label">Email</span>
      <span class="val"><?=htmlspecialchars($order['buyer_email'])?></span>
    </div>
    <?php if($order['buyer_phone']): ?>
    <div class="drow">
      <span class="label">No. HP</span>
      <span class="val"><?=htmlspecialchars($order['buyer_phone'])?></span>
    </div>
    <?php endif; ?>
    <div class="drow">
      <span class="label">ID Akun Game</span>
      <span class="val"><?=htmlspecialchars($order['game_user_id'])?><?=$order['server_id']?' / '.$order['server_id']:''?></span>
    </div>

    <!-- Detail harga -->
    <div class="section-title">Rincian Harga</div>

    <div class="drow">
      <span class="label"><?=htmlspecialchars($order['product_name'])?></span>
      <span class="val"><?=formatRupiah($order['amount'])?></span>
    </div>
    <div class="drow">
      <span class="label">Biaya Layanan</span>
      <span class="val">Rp 0</span>
    </div>

    <!-- Total -->
    <div class="total-box">
      <span class="tl">TOTAL PEMBAYARAN</span>
      <span class="ta"><?=formatRupiah($order['amount'])?></span>
    </div>

    <!-- Top-up info -->
    <?php if($order['topup_status']): ?>
    <div class="section-title">Status Top-Up</div>
    <div class="drow">
      <span class="label">Status</span>
      <span class="val"><?=ucfirst($order['topup_status'])?></span>
    </div>
    <?php if($order['topup_msg']): ?>
    <div class="drow">
      <span class="label">Keterangan</span>
      <span class="val"><?=htmlspecialchars($order['topup_msg'])?></span>
    </div>
    <?php endif; ?>
    <?php endif; ?>

  </div>

  <!-- Footer invoice -->
  <div class="inv-footer">
    <div class="note">
      Terima kasih telah berbelanja di <strong>ftarastore</strong>.<br>
      Simpan invoice ini sebagai bukti pembayaran yang sah.<br>
      Pertanyaan? Hubungi CS kami di WhatsApp.
    </div>
    <div class="inv-qr">
      <div style="font-size:.7rem;color:#7aabb8;margin-bottom:3px;">Kode Verifikasi</div>
      <div class="qr-code"><?=strtoupper(substr(md5($order['order_code'].date('Y')), 0, 12))?></div>
      <div style="font-size:.68rem;color:#7aabb8;margin-top:2px;">ftarastore.com</div>
    </div>
  </div>

</div>

<div style="text-align:center;margin-top:18px;font-size:.75rem;color:#7aabb8;">
  Untuk mencetak: tekan tombol "Cetak / Save PDF" atau <kbd style="background:white;border:1px solid #c5dff0;border-radius:4px;padding:1px 6px;">Ctrl+P</kbd>
</div>

</body>
</html>