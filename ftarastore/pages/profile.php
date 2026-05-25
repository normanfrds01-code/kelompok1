<?php
require_once __DIR__.'/../includes/functions.php';
if(!isLoggedIn()){ header('Location: '.asset('auth/login.php')); exit; }
if(isAdmin()){ header('Location: '.asset('admin/index.php')); exit; }

$user = currentUser();
$pageTitle = 'Profil — '.siteName();

// Riwayat transaksi user
$orders = [];
try {
    $stmt = db()->prepare("SELECT o.order_code,o.product_name,o.amount,o.status,o.created_at,o.game_user_id,g.name AS game_name,g.image_url AS game_img FROM orders o LEFT JOIN products p ON p.id=o.product_id LEFT JOIN games g ON g.id=p.game_id WHERE o.buyer_email=? ORDER BY o.created_at DESC LIMIT 30");
    $stmt->execute([$user['email']]);
    $orders = $stmt->fetchAll();
} catch(\Exception $e){}

$totalSpend = array_sum(array_column(array_filter($orders, fn($o)=>$o['status']==='success'), 'amount'));
$successCount = count(array_filter($orders, fn($o)=>$o['status']==='success'));

include __DIR__.'/../includes/header.php';
?>
<style>
.prof-wrap { max-width: 960px; margin: 0 auto; padding: 28px 20px 60px; }
.prof-hero {
  background: linear-gradient(135deg, var(--card) 0%, rgba(227,24,55,.06) 100%);
  border: 1.5px solid var(--b1); border-radius: 16px;
  padding: 28px; margin-bottom: 20px;
  display: flex; align-items: center; gap: 20px; flex-wrap: wrap;
}
.prof-avatar {
  width: 72px; height: 72px; border-radius: 50%; flex-shrink: 0;
  background: linear-gradient(135deg, var(--red), var(--red2));
  color: #fff; font-weight: 800; font-size: 1.8rem; font-family: var(--f-display);
  display: flex; align-items: center; justify-content: center;
  border: 3px solid rgba(227,24,55,.3);
  box-shadow: 0 0 0 4px rgba(227,24,55,.1);
}
.prof-info { flex: 1; }
.prof-name { font-family: var(--f-display); font-weight: 800; font-size: 1.3rem; color: var(--t1); margin-bottom: 4px; }
.prof-email { font-size: .82rem; color: var(--t3); margin-bottom: 10px; }
.prof-badges { display: flex; gap: 8px; flex-wrap: wrap; }
.prof-badge {
  display: inline-flex; align-items: center; gap: 6px;
  background: var(--card2); border: 1px solid var(--b1);
  border-radius: 20px; padding: 5px 12px;
  font-size: .74rem; font-weight: 600; color: var(--t2);
}
.prof-badge.gold { color: var(--gold); border-color: rgba(245,166,35,.3); background: rgba(245,166,35,.06); }
.prof-stats {
  display: grid; grid-template-columns: repeat(3,1fr); gap: 12px; margin-bottom: 20px;
}
.prof-stat {
  background: var(--card); border: 1.5px solid var(--b1); border-radius: 12px;
  padding: 16px; text-align: center;
}
.prof-stat-val { font-family: var(--f-display); font-size: 1.4rem; font-weight: 800; color: var(--red); margin-bottom: 4px; }
.prof-stat-lbl { font-size: .74rem; color: var(--t3); font-weight: 500; }
.prof-tabs { display: flex; gap: 2px; background: var(--card2); border-radius: 10px; padding: 4px; margin-bottom: 18px; }
.prof-tab {
  flex: 1; padding: 9px; border-radius: 7px; text-align: center;
  font-size: .82rem; font-weight: 600; color: var(--t3); cursor: pointer; border: none;
  background: transparent; transition: all .2s;
}
.prof-tab.on { background: var(--red); color: #fff; box-shadow: 0 2px 8px rgba(227,24,55,.3); }
.prof-tab:hover:not(.on) { color: var(--t1); background: var(--b1); }

/* Tab content */
.prof-tab-pane { display: none; }
.prof-tab-pane.on { display: block; }

/* Transaction cards */
.tx-card {
  display: flex; align-items: center; gap: 12px;
  background: var(--card); border: 1.5px solid var(--b1);
  border-radius: 12px; padding: 12px 14px; margin-bottom: 8px;
  text-decoration: none; transition: all .2s;
}
.tx-card:hover { border-color: rgba(227,24,55,.4); transform: translateX(3px); }
.tx-img { width: 44px; height: 44px; border-radius: 8px; object-fit: cover; flex-shrink: 0; }
.tx-info { flex: 1; min-width: 0; }
.tx-prod { font-weight: 700; font-size: .85rem; color: var(--t1); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.tx-sub { font-size: .72rem; color: var(--t3); margin-top: 2px; }
.tx-right { text-align: right; flex-shrink: 0; }
.tx-amount { font-weight: 800; font-size: .9rem; color: var(--gold); font-family: var(--f-display); }
.tx-date { font-size: .68rem; color: var(--t3); margin-top: 3px; }

/* Settings */
.set-card { background: var(--card); border: 1.5px solid var(--b1); border-radius: 12px; overflow: hidden; margin-bottom: 12px; }
.set-row {
  display: flex; align-items: center; justify-content: space-between;
  padding: 14px 18px; border-bottom: 1px solid var(--b0); font-size: .86rem;
}
.set-row:last-child { border-bottom: none; }
.set-row-label { display: flex; align-items: center; gap: 10px; color: var(--t1); font-weight: 500; }
.set-row-val { color: var(--t3); font-size: .82rem; }

@media(max-width:600px){
  .prof-stats { grid-template-columns: repeat(2,1fr); }
  .prof-hero { flex-direction: column; text-align: center; }
}
</style>

<div class="prof-wrap">
  <!-- Hero -->
  <div class="prof-hero">
    <div class="prof-avatar"><?=strtoupper(substr($user['name']??'U',0,1))?></div>
    <div class="prof-info">
      <div class="prof-name"><?=htmlspecialchars($user['name']??'User')?></div>
      <div class="prof-email"><?=htmlspecialchars($user['email'])?></div>
      <div class="prof-badges">
        <span class="prof-badge gold">
          <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
          Member
        </span>
        <span class="prof-badge">
          <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
          Terverifikasi
        </span>
      </div>
    </div>
    <a href="<?=asset('auth/logout.php')?>" style="display:inline-flex;align-items:center;gap:7px;padding:9px 16px;background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);border-radius:8px;color:#fca5a5;font-size:.82rem;font-weight:600;text-decoration:none;transition:all .2s;"
       onmouseover="this.style.background='rgba(239,68,68,.2)'" onmouseout="this.style.background='rgba(239,68,68,.1)'">
      <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
      Keluar
    </a>
  </div>

  <!-- Stats -->
  <div class="prof-stats">
    <div class="prof-stat">
      <div class="prof-stat-val"><?=$successCount?></div>
      <div class="prof-stat-lbl">Top Up Sukses</div>
    </div>
    <div class="prof-stat">
      <div class="prof-stat-val" style="font-size:1rem;"><?=count($orders)?></div>
      <div class="prof-stat-lbl">Total Order</div>
    </div>
    <div class="prof-stat">
      <div class="prof-stat-val" style="font-size:.9rem;color:var(--gold);"><?=formatRupiah($totalSpend)?></div>
      <div class="prof-stat-lbl">Total Belanja</div>
    </div>
  </div>

  <!-- Tabs -->
  <div class="prof-tabs">
    <button class="prof-tab on" onclick="showTab('transaksi',this)">📋 Transaksi Saya</button>
    <button class="prof-tab" onclick="showTab('pengaturan',this)">⚙️ Pengaturan</button>
  </div>

  <!-- Tab: Transaksi -->
  <div class="prof-tab-pane on" id="tab-transaksi">
    <?php if(empty($orders)): ?>
    <div style="text-align:center;padding:60px 20px;color:var(--t3);">
      <svg width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="display:block;margin:0 auto 14px;opacity:.3"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
      <p style="font-weight:600;margin-bottom:6px;">Belum ada transaksi</p>
      <p style="font-size:.82rem;">Top up pertamamu akan muncul di sini</p>
      <a href="<?=asset('index.php')?>" class="btn-submit" style="display:inline-block;margin-top:16px;padding:10px 24px;font-size:.86rem;">Mulai Top Up</a>
    </div>
    <?php else: ?>
    <?php foreach($orders as $o):
      $stMap=['pending'=>['Menunggu','badge-pending'],'paid'=>['Dibayar','badge-process'],'processing'=>['Diproses','badge-process'],'success'=>['Berhasil','badge-success'],'failed'=>['Gagal','badge-failed']];
      $stInfo=$stMap[$o['status']]??['—','badge-pending'];
    ?>
    <a href="<?=asset('pages/cek-transaksi.php')?>?code=<?=urlencode($o['order_code'])?>" class="tx-card">
      <?php if($o['game_img']): ?>
      <img src="<?=htmlspecialchars($o['game_img'])?>" class="tx-img" onerror="this.style.display='none'"/>
      <?php else: ?>
      <div class="tx-img" style="background:var(--card2);display:flex;align-items:center;justify-content:center;"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="opacity:.3"><rect x="2" y="6" width="20" height="12" rx="3"/></svg></div>
      <?php endif; ?>
      <div class="tx-info">
        <div class="tx-prod"><?=htmlspecialchars($o['product_name'])?></div>
        <div class="tx-sub"><?=htmlspecialchars($o['game_name']??'')?> · <?=htmlspecialchars($o['game_user_id']??'')?></div>
        <div class="tx-sub" style="font-family:monospace;font-size:.68rem;color:var(--t3);"><?=htmlspecialchars($o['order_code'])?></div>
      </div>
      <div class="tx-right">
        <div class="tx-amount"><?=formatRupiah($o['amount'])?></div>
        <div style="margin-top:4px;"><span class="badge <?=$stInfo[1]?>" style="font-size:.65rem;"><?=$stInfo[0]?></span></div>
        <div class="tx-date"><?=date('d M Y, H:i',strtotime($o['created_at']))?></div>
      </div>
    </a>
    <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <!-- Tab: Pengaturan -->
  <div class="prof-tab-pane" id="tab-pengaturan">
    <div class="set-card">
      <div class="set-row">
        <div class="set-row-label">
          <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
          Nama
        </div>
        <span class="set-row-val"><?=htmlspecialchars($user['name']??'-')?></span>
      </div>
      <div class="set-row">
        <div class="set-row-label">
          <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
          Email
        </div>
        <span class="set-row-val"><?=htmlspecialchars($user['email'])?></span>
      </div>
      <div class="set-row">
        <div class="set-row-label">
          <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
          Role
        </div>
        <span class="set-row-val">Member</span>
      </div>
      <div class="set-row">
        <div class="set-row-label">
          <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
          Bergabung
        </div>
        <span class="set-row-val"><?=date('d M Y',strtotime($user['created_at']??'now'))?></span>
      </div>
    </div>
    <div class="set-card">
      <div class="set-row" style="cursor:pointer;" onclick="window.location='<?=asset('auth/logout.php')?>'">
        <div class="set-row-label" style="color:#fca5a5;">
          <svg width="15" height="15" fill="none" stroke="#fca5a5" stroke-width="2" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
          Keluar dari Akun
        </div>
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="m9 18 6-6-6-6"/></svg>
      </div>
    </div>
  </div>
</div>

<script>
function showTab(id, btn){
  document.querySelectorAll('.prof-tab-pane').forEach(function(p){ p.classList.remove('on'); });
  document.querySelectorAll('.prof-tab').forEach(function(b){ b.classList.remove('on'); });
  document.getElementById('tab-'+id).classList.add('on');
  btn.classList.add('on');
}
</script>
<?php include __DIR__.'/../includes/footer.php'; ?>