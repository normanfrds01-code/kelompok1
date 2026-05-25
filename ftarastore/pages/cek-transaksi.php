<?php
require_once __DIR__.'/../includes/functions.php';
$pageTitle='Cek Transaksi — '.siteName();
$code=trim($_GET['code']??$_POST['code']??'');
$order=$code?getOrderByCode($code):null;
$stMap=['pending'=>['Menunggu Bayar','pending',''],'paid'=>['Dibayar','process',''],'processing'=>['Diproses','process',''],'success'=>['Berhasil','success',''],'failed'=>['Gagal','failed',''],'refunded'=>['Direfund','pending','']];

// Riwayat transaksi user (jika login)
$myOrders = [];
if(isLoggedIn() && !isAdmin()){
    $email = currentUser()['email'] ?? '';
    if($email){
        $hist = db()->prepare("SELECT o.order_code,o.product_name,o.amount,o.status,o.created_at,o.game_user_id,g.name AS game_name,g.image_url AS game_img FROM orders o LEFT JOIN products p ON p.id=o.product_id LEFT JOIN games g ON g.id=p.game_id WHERE o.buyer_email=? ORDER BY o.created_at DESC LIMIT 20");
        $hist->execute([$email]);
        $myOrders = $hist->fetchAll();
    }
}

include __DIR__.'/../includes/header.php';
?>
<style>
.ck-wrap { max-width: 960px; margin: 0 auto; padding: 32px 20px 60px; }
.ck-grid { display: grid; grid-template-columns: 340px 1fr; gap: 24px; align-items: start; }
.ck-left { position: sticky; top: 80px; }
.ck-search-card {
  background: var(--card); border: 1.5px solid var(--b1); border-radius: 16px; padding: 24px;
}
.ck-search-card h2 { font-size: 1.1rem; font-weight: 700; color: var(--t1); margin-bottom:4px; }
.ck-search-card p { font-size: .8rem; color: var(--t3); margin-bottom: 18px; }
.ck-history { }
.ck-history-title {
  display: flex; align-items: center; gap: 8px;
  font-weight: 700; font-size: .95rem; color: var(--t1);
  margin-bottom: 14px;
}
.ck-tx-card {
  display: flex; align-items: center; gap: 12px;
  background: var(--card); border: 1.5px solid var(--b1);
  border-radius: 12px; padding: 12px 14px; text-decoration: none;
  transition: border-color .2s, transform .15s; margin-bottom: 8px;
  cursor: pointer;
}
.ck-tx-card:hover { border-color: rgba(227,24,55,.4); transform: translateX(2px); }
.ck-tx-card.active-tx { border-color: #e31837; background: rgba(227,24,55,.05); }
.ck-tx-img { width: 44px; height: 44px; border-radius: 9px; object-fit: cover; flex-shrink: 0; border: 1px solid var(--b1); }
.ck-tx-info { flex: 1; min-width: 0; }
.ck-tx-prod { font-weight: 700; font-size: .84rem; color: var(--t1); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.ck-tx-sub { font-size: .72rem; color: var(--t3); margin-top: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.ck-tx-right { text-align: right; flex-shrink: 0; }
.ck-tx-price { font-weight: 800; font-size: .88rem; color: var(--gold); font-family: var(--f-display); }
.ck-tx-status { margin-top: 4px; }
.ck-tx-date { font-size: .67rem; color: var(--t3); margin-top: 3px; }
.ck-detail { background: var(--card); border: 1.5px solid var(--b1); border-radius: 14px; overflow: hidden; }
.ck-empty { text-align: center; padding: 60px 20px; color: var(--t3); }
.ck-empty-ico { font-size: 2.5rem; margin-bottom: 12px; }
@media(max-width: 768px) {
  .ck-grid { grid-template-columns: 1fr; }
  .ck-left { position: static; }
}
</style>

<div class="ck-wrap">
  <?php if(isLoggedIn() && !isAdmin()): ?>
  <!-- ══ LAYOUT: Search kiri + Riwayat kanan ══ -->
  <div class="ck-grid">
    <!-- Search card (sticky) -->
    <div class="ck-left">
      <div class="ck-search-card">
        <h2>Cek Transaksi</h2>
        <p>Masukkan kode order atau pilih dari riwayat kamu</p>
        <form method="GET">
          <div class="fg" style="margin-bottom:12px;">
            <label class="flabel">Kode Order</label>
            <input type="text" name="code" class="finput" placeholder="FTS-20240101-XXXXXX"
                   value="<?=htmlspecialchars($code)?>" maxlength="30"
                   style="text-transform:uppercase;letter-spacing:.5px;"/>
            <div class="fhint">Kode order dikirim ke email setelah pembelian.</div>
          </div>
          <button type="submit" class="btn-submit" style="width:100%;">Cek Status</button>
        </form>
        <!-- CS Help -->
        <a href="https://wa.me/<?=getSetting('whatsapp_number','62')?>" target="_blank"
           style="display:flex;align-items:center;justify-content:center;gap:8px;margin-top:12px;padding:10px;background:var(--card2);border:1px solid var(--b1);border-radius:8px;font-size:.8rem;color:var(--t3);text-decoration:none;">
          Butuh bantuan?
          <span style="color:#25d366;font-weight:600;">Hubungi CS via WhatsApp →</span>
        </a>
      </div>
    </div>

    <!-- Right: riwayat + detail -->
    <div>
      <!-- Detail order (kalau ada kode) -->
      <?php if($code && !$order): ?>
      <div style="background:rgba(239,68,68,.07);border:1px solid rgba(239,68,68,.2);border-radius:12px;padding:14px 16px;color:#fca5a5;font-size:.86rem;margin-bottom:16px;display:flex;gap:10px;align-items:center;">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        Kode order <strong><?=htmlspecialchars($code)?></strong> tidak ditemukan.
      </div>
      <?php elseif($order): ?>
      <?php $st=$order['status']??'pending';$stl=$stMap[$st]??['Tidak Diketahui','pending',''];?>
      <div class="ck-detail" style="margin-bottom:20px;">
        <div style="padding:16px 20px;background:var(--card2);border-bottom:1px solid var(--b1);display:flex;justify-content:space-between;align-items:center;">
          <div>
            <div style="font-size:.7rem;color:var(--t3);margin-bottom:3px;">Kode Order</div>
            <div style="font-family:var(--f-display);font-weight:700;color:#e31837;font-size:.95rem;letter-spacing:.5px;"><?=htmlspecialchars($order['order_code'])?></div>
          </div>
          <span class="badge badge-<?=$stl[1]?>"><?=$stl[0]?></span>
        </div>
        <div style="padding:16px 20px;">
          <div style="display:flex;flex-direction:column;gap:12px;">
            <div style="display:flex;justify-content:space-between;font-size:.86rem;"><span style="color:var(--t3);">Produk</span><span style="font-weight:600;color:var(--t1);"><?=htmlspecialchars($order['product_name'])?></span></div>
            <div style="display:flex;justify-content:space-between;font-size:.86rem;"><span style="color:var(--t3);">ID Akun</span><span style="font-weight:600;color:var(--t1);"><?=htmlspecialchars($order['game_user_id'])?><?=$order['server_id']?' / '.htmlspecialchars($order['server_id']):''?></span></div>
            <div style="display:flex;justify-content:space-between;font-size:.86rem;"><span style="color:var(--t3);">Email</span><span style="color:var(--t2);"><?=htmlspecialchars($order['buyer_email'])?></span></div>
            <div style="display:flex;justify-content:space-between;font-size:.86rem;"><span style="color:var(--t3);">Total</span><span style="font-weight:800;color:var(--gold);font-family:var(--f-display);font-size:1rem;"><?=formatRupiah($order['amount'])?></span></div>
            <?php if($order['payment_method']): ?>
            <div style="display:flex;justify-content:space-between;font-size:.86rem;"><span style="color:var(--t3);">Metode Bayar</span><span style="font-weight:600;color:var(--t1);"><?=strtoupper(htmlspecialchars($order['payment_method']))?></span></div>
            <?php endif; ?>
            <div style="display:flex;justify-content:space-between;font-size:.86rem;"><span style="color:var(--t3);">Tanggal</span><span style="color:var(--t2);"><?=date('d M Y, H:i',strtotime($order['created_at']))?></span></div>
            <?php if($order['topup_status']??''): ?>
            <div style="display:flex;justify-content:space-between;font-size:.86rem;align-items:center;"><span style="color:var(--t3);">Status Top-Up</span><span class="badge badge-<?=$order['topup_status']==='sukses'?'success':($order['topup_status']==='gagal'?'failed':'process')?>"><?=ucfirst($order['topup_status'])?></span></div>
            <?php endif; ?>
          </div>
          <!-- Lanjutkan Pembayaran -->
          <?php if($order['status']==='pending'): ?>
          <?php
            $payRow=null;
            try{ $pst=db()->prepare("SELECT snap_token FROM payments WHERE order_id=? ORDER BY id DESC LIMIT 1"); $pst->execute([$order['id']]); $payRow=$pst->fetch(); }catch(\Exception $e){}
            $snapToken=$payRow['snap_token']??'';
          ?>
          <div style="margin-top:16px;padding-top:14px;border-top:1px solid var(--b1);">
            <?php if($snapToken): ?>
            <script src="https://app.midtrans.com/snap/snap.js" data-client-key="<?=defined('MIDTRANS_CLIENT_KEY')?MIDTRANS_CLIENT_KEY:''?>"></script>
            <button onclick="snap.pay('<?=htmlspecialchars($snapToken)?>', {
              onSuccess:function(){window.location.href='<?=asset('pages/order-success.php')?>?code=<?=urlencode($order['order_code'])?>';},
              onPending:function(){window.location.reload();},
              onError:function(){window.location.reload();}
            })" class="btn-submit" style="width:100%;">Bayar Sekarang →</button>
            <?php else: ?>
            <a href="<?=asset('index.php')?>" class="btn-submit" style="display:block;text-align:center;">Buat Order Baru</a>
            <div style="font-size:.72rem;color:var(--t3);text-align:center;margin-top:6px;">Token expired. Buat order baru.</div>
            <?php endif; ?>
          </div>
          <?php endif; ?>
        </div>
      </div>
      <?php endif; ?>

      <!-- Riwayat Transaksi -->
      <?php if(!empty($myOrders)): ?>
      <div class="ck-history">
        <div class="ck-history-title">
          <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
          Riwayat Transaksi
          <span style="font-size:.72rem;color:var(--t3);font-weight:400;">(<?=count($myOrders)?>)</span>
        </div>
        <?php foreach($myOrders as $o):
          $stI=['pending'=>['Menunggu','badge-pending'],'paid'=>['Dibayar','badge-process'],'processing'=>['Diproses','badge-process'],'success'=>['Berhasil','badge-success'],'failed'=>['Gagal','badge-failed'],'refunded'=>['Direfund','badge-pending']][$o['status']]??['—','badge-pending'];
        ?>
        <a href="?code=<?=urlencode($o['order_code'])?>" class="ck-tx-card<?=$code===$o['order_code']?' active-tx':''?>">
          <?php if($o['game_img']): ?>
          <img src="<?=htmlspecialchars($o['game_img'])?>" class="ck-tx-img" onerror="this.style.display='none'"/>
          <?php else: ?>
          <div class="ck-tx-img" style="background:var(--card2);display:flex;align-items:center;justify-content:center;">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="opacity:.3"><rect x="2" y="6" width="20" height="12" rx="3"/></svg>
          </div>
          <?php endif; ?>
          <div class="ck-tx-info">
            <div class="ck-tx-prod"><?=htmlspecialchars($o['product_name'])?></div>
            <div class="ck-tx-sub"><?=htmlspecialchars($o['game_name']??'').' · '.htmlspecialchars($o['game_user_id']??'')?></div>
          </div>
          <div class="ck-tx-right">
            <div class="ck-tx-price"><?=formatRupiah($o['amount'])?></div>
            <div class="ck-tx-status"><span class="badge <?=$stI[1]?>" style="font-size:.63rem;"><?=$stI[0]?></span></div>
            <div class="ck-tx-date"><?=date('d/m/y H:i',strtotime($o['created_at']))?></div>
          </div>
        </a>
        <?php endforeach; ?>
      </div>
      <?php else: ?>
      <div class="ck-empty">
        <div class="ck-empty-ico">📋</div>
        <p style="font-weight:600;margin-bottom:6px;">Belum ada transaksi</p>
        <p style="font-size:.82rem;">Transaksimu akan muncul di sini setelah top up pertama.</p>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <?php else: ?>
  <!-- Guest: tampilan search terpusat -->
  <div class="cek-page">
  <div class="cek-card">
    <div class="cek-icon" style="font-size:2rem;margin-bottom:12px;text-align:center;">🔍</div>
    <h1 style="font-size:1.6rem;font-weight:800;margin-bottom:6px;color:var(--t1);text-align:center;">Cek Transaksi</h1>
    <p style="font-size:.86rem;color:var(--t3);margin-bottom:22px;text-align:center;">Masukkan kode order untuk melihat status top up kamu.</p>
    <form method="GET">
      <div class="fg" style="text-align:left">
        <label class="flabel">Kode Order</label>
        <input type="text" name="code" class="finput" placeholder="FTS-20240101-XXXXXX"
               value="<?=htmlspecialchars($code)?>" maxlength="30"
               style="text-transform:uppercase;letter-spacing:.5px"/>
        <div class="fhint">Kode order dikirim ke email setelah pembelian.</div>
      </div>
      <button type="submit" class="btn-submit">Cek Status</button>
    </form>

    <?php if($code&&!$order): ?>
    <div style="margin-top:24px;padding:16px;background:rgba(239,68,68,.07);border:1px solid rgba(239,68,68,.2);border-radius:10px;color:#fca5a5;font-size:.88rem;display:flex;align-items:center;gap:10px">
      ❌ <span>Kode order <strong><?=htmlspecialchars($code)?></strong> tidak ditemukan. Periksa kembali kode yang kamu masukkan.</span>
    </div>
    <?php elseif($order): ?>
    <?php $st=$order['status']??'pending';$stl=$stMap[$st]??['Tidak Diketahui','pending','❓']; ?>
    <div class="result-box" style="margin-top:24px">
      <div class="result-head">
        <div>
          <div style="font-size:.72rem;color:var(--t3);margin-bottom:3px">Kode Order</div>
          <div class="result-code"><?=htmlspecialchars($order['order_code'])?></div>
        </div>
        <span class="badge badge-<?=$stl[1]?>"><?=$stl[2]?> <?=$stl[0]?></span>
      </div>
      <div class="result-body">
        <div class="drow"><span class="drow-label">Produk</span><span class="drow-val"><strong><?=htmlspecialchars($order['product_name'])?></strong></span></div>
        <div class="drow"><span class="drow-label">ID Akun</span><span class="drow-val"><?=htmlspecialchars($order['game_user_id'])?><?=$order['server_id']?' / '.htmlspecialchars($order['server_id']):''?></span></div>
        <div class="drow"><span class="drow-label">Email</span><span class="drow-val"><?=htmlspecialchars($order['buyer_email'])?></span></div>
        <div class="drow"><span class="drow-label">Total</span><span class="drow-val" style="color:var(--gold);font-family:var(--f-display);font-size:1.05rem;font-weight:700"><?=formatRupiah($order['amount'])?></span></div>
        <?php if($order['payment_method']): ?><div class="drow"><span class="drow-label">Metode Bayar</span><span class="drow-val"><?=strtoupper(htmlspecialchars($order['payment_method']))?></span></div><?php endif; ?>
        <div class="drow"><span class="drow-label">Tanggal</span><span class="drow-val"><?=date('d M Y, H:i',strtotime($order['created_at']))?></span></div>
        <?php if($order['topup_status']): ?>
        <div class="drow" style="border:none"><span class="drow-label">Status Top-Up</span>
          <span class="badge badge-<?=$order['topup_status']==='sukses'?'success':($order['topup_status']==='gagal'?'failed':'process')?>"><?=ucfirst($order['topup_status'])?></span>
        </div>
        <?php endif; ?>
      </div>
      <?php if(in_array($order['status'],['pending'])): ?>
      <?php
        // Ambil snap token yang sudah ada
        $payRow = null;
        try {
            $pst = db()->prepare("SELECT snap_token FROM payments WHERE order_id=? ORDER BY id DESC LIMIT 1");
            $pst->execute([$order['id']]);
            $payRow = $pst->fetch();
        } catch(\Exception $e){}
        $snapToken = $payRow['snap_token'] ?? '';
      ?>
      <div style="padding:14px 20px;border-top:1px solid var(--b1);text-align:center;">
        <?php if($snapToken): ?>
        <!-- Buka Midtrans Snap langsung dengan token yang ada -->
        <script src="https://app.midtrans.com/snap/snap.js"
                data-client-key="<?=defined('MIDTRANS_CLIENT_KEY')?MIDTRANS_CLIENT_KEY:''?>"></script>
        <button onclick="snap.pay('<?=htmlspecialchars($snapToken)?>', {
          onSuccess: function(){ window.location.href='<?=asset('pages/order-success.php')?>?code=<?=urlencode($order['order_code'])?>'; },
          onPending: function(){ window.location.reload(); },
          onError:   function(){ window.location.reload(); },
          onClose:   function(){ }
        })" class="btn-submit" style="padding:11px 28px;font-size:.9rem;border-radius:8px;cursor:pointer;">
          Lanjutkan Pembayaran →
        </button>
        <?php else: ?>
        <!-- Tidak ada token — buat order baru dari game page -->
        <a href="<?=asset('index.php')?>" class="btn-submit" style="display:inline-block;padding:11px 28px;font-size:.9rem;border-radius:8px;text-align:center;">
          Top Up Lagi →
        </a>
        <div style="font-size:.75rem;color:var(--t3);margin-top:8px;">Token pembayaran tidak ditemukan. Silakan buat order baru.</div>
        <?php endif; ?>
      </div>
      <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- ── Riwayat Transaksi Saya ── -->
    <?php if(!empty($myOrders)): ?>
    <div style="margin-top:32px;border-top:1px solid var(--b1);padding-top:24px;">
      <div style="font-weight:700;font-size:.95rem;margin-bottom:14px;display:flex;align-items:center;gap:8px;color:var(--t1);">
        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
        Riwayat Transaksi Saya
        <span style="font-size:.72rem;color:var(--t3);font-weight:400;">(<?=count($myOrders)?> transaksi)</span>
      </div>
      <div style="display:flex;flex-direction:column;gap:8px;">
        <?php foreach($myOrders as $o):
          $st=$o['status'];
          $stInfo=['pending'=>['Menunggu','badge-pending'],'paid'=>['Dibayar','badge-process'],'processing'=>['Diproses','badge-process'],'success'=>['Berhasil','badge-success'],'failed'=>['Gagal','badge-failed'],'refunded'=>['Direfund','badge-pending']][$st]??['—','badge-pending'];
        ?>
        <a href="?code=<?=urlencode($o['order_code'])?>" style="display:flex;align-items:center;gap:12px;background:var(--card2);border:1.5px solid var(--b1);border-radius:12px;padding:12px 14px;text-decoration:none;transition:border-color .2s;" onmouseover="this.style.borderColor='rgba(227,24,55,.35)'" onmouseout="this.style.borderColor='var(--b1)'">
          <?php if($o['game_img']): ?>
          <img src="<?=htmlspecialchars($o['game_img'])?>" style="width:40px;height:40px;border-radius:8px;object-fit:cover;flex-shrink:0;" onerror="this.style.display='none'"/>
          <?php else: ?>
          <div style="width:40px;height:40px;border-radius:8px;background:var(--card);border:1px solid var(--b1);flex-shrink:0;"></div>
          <?php endif; ?>
          <div style="flex:1;min-width:0;">
            <div style="font-weight:700;font-size:.84rem;color:var(--t1);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?=htmlspecialchars($o['product_name'])?></div>
            <div style="font-size:.72rem;color:var(--t3);margin-top:2px;"><?=htmlspecialchars($o['game_name']??'')?> · <?=htmlspecialchars($o['game_user_id']??'')?></div>
          </div>
          <div style="text-align:right;flex-shrink:0;">
            <div style="font-weight:800;font-size:.88rem;color:#c41230;font-family:var(--f-display);"><?=formatRupiah($o['amount'])?></div>
            <div style="margin-top:4px;"><span class="badge <?=$stInfo[1]?>" style="font-size:.65rem;"><?=$stInfo[0]?></span></div>
            <div style="font-size:.68rem;color:var(--t3);margin-top:3px;"><?=date('d/m/y H:i',strtotime($o['created_at']))?></div>
          </div>
        </a>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <div class="help-strip" style="margin-top:20px;">
      Butuh bantuan? <a href="https://wa.me/<?=getSetting('whatsapp_number','62')?>" target="_blank" style="color:#25d366;">Hubungi CS via WhatsApp →</a>
    </div>
  </div>
  <?php endif; // end logged-in vs guest ?>
</div><!-- /ck-wrap -->
<?php include __DIR__.'/../includes/footer.php'; ?>