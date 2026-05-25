<?php
require_once __DIR__.'/../includes/functions.php';
Security::boot(['admin']);
$pageTitle='Dashboard Admin — '.siteName();
$db=db();
$stats=[
  'today'   =>$db->query("SELECT COUNT(*) FROM orders WHERE DATE(created_at)=CURDATE()")->fetchColumn(),
  'month'   =>$db->query("SELECT COUNT(*) FROM orders WHERE MONTH(created_at)=MONTH(NOW()) AND YEAR(created_at)=YEAR(NOW())")->fetchColumn(),
  'revenue' =>$db->query("SELECT COALESCE(SUM(amount),0) FROM orders WHERE status='success' AND MONTH(created_at)=MONTH(NOW())")->fetchColumn(),
  'games'   =>$db->query("SELECT COUNT(*) FROM games WHERE is_active=1")->fetchColumn(),
];
$recent=$db->query("SELECT o.*,py.payment_method FROM orders o LEFT JOIN payments py ON py.order_id=o.id ORDER BY o.created_at DESC LIMIT 12")->fetchAll();
$stMap=['pending'=>['pending',''],'paid'=>['process',''],'processing'=>['process',''],'success'=>['success',''],'failed'=>['failed',''],'refunded'=>['pending',''],'refund'=>['pending','']];
include __DIR__.'/../includes/header.php';
?>
<div class="admin-wrap">
<?php include __DIR__.'/sidebar.php'; ?>
<div class="admin-main">
  <div class="admin-title">Dashboard</div>

  <!-- Sistem Normal — hanya di dashboard admin -->
  <?php
  $sysStatus = getSetting('system_status','normal');
  $sCfg = match($sysStatus){
    'maintenance'=>['label'=>'Maintenance','icon'=>'','color'=>'#ef4444','bg'=>'rgba(239,68,68,.08)','border'=>'rgba(239,68,68,.2)','msg'=>'Website sedang dalam maintenance. User tidak bisa transaksi.'],
    'degraded'   =>['label'=>'Ada Gangguan','icon'=>'','color'=>'#f59e0b','bg'=>'rgba(245,158,11,.08)','border'=>'rgba(245,158,11,.2)','msg'=>'Layanan mengalami gangguan sebagian.'],
    default      =>['label'=>'Sistem Normal','icon'=>'','color'=>'#10b981','bg'=>'rgba(16,185,129,.06)','border'=>'rgba(16,185,129,.18)','msg'=>'Semua layanan berjalan normal.'],
  };
  ?>
  <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 18px;border-radius:12px;margin-bottom:18px;background:<?=$sCfg['bg']?>;border:1px solid <?=$sCfg['border']?>;">
    <div style="display:flex;align-items:center;gap:10px;">
      <span style="font-size:1.1rem;"><?=$sCfg['icon']?></span>
      <div>
        <div style="font-weight:700;font-size:.88rem;color:<?=$sCfg['color']?>"><?=$sCfg['label']?></div>
        <div style="font-size:.75rem;color:var(--t3);margin-top:1px;"><?=$sCfg['msg']?></div>
      </div>
    </div>
    <a href="<?=asset('admin/settings.php')?>" style="font-size:.75rem;color:var(--cyan);font-weight:600;">Ubah Status →</a>
  </div>

  <div class="stats-grid">
    <div class="stat-card"><div class="stat-label">Order Hari Ini</div><div class="stat-val"><?=number_format($stats['today'])?></div><div class="stat-change">Transaksi masuk</div></div>
    <div class="stat-card"><div class="stat-label">Order Bulan Ini</div><div class="stat-val"><?=number_format($stats['month'])?></div><div class="stat-change">Total bulan ini</div></div>
    <div class="stat-card"><div class="stat-label">Pendapatan Bulan Ini</div><div class="stat-val" style="font-size:1.3rem"><?=formatRupiah($stats['revenue'])?></div><div class="stat-change">Order sukses</div></div>
    <div class="stat-card"><div class="stat-label">Game Aktif</div><div class="stat-val"><?=$stats['games']?></div><div class="stat-change">Produk tersedia</div></div>
  </div>

  <div class="admin-card">
    <div class="admin-card-head">
      <h3>Order Terbaru</h3>
      <a href="<?=asset('admin/transactions.php')?>" class="btn-sm btn-sm-edit">Lihat Semua →</a>
    </div>
    <div class="table-wrap">
    <table class="dtable">
      <thead><tr><th>Kode Order</th><th>Produk</th><th>ID Game</th><th>Email</th><th>Total</th><th>Status</th><th>Waktu</th></tr></thead>
      <tbody>
      <?php if(empty($recent)): ?>
        <tr><td colspan="7" style="text-align:center;padding:40px;color:var(--t3)">Belum ada transaksi</td></tr>
      <?php else: ?>
      <?php foreach($recent as $o): $sl=$stMap[$o['status']]??['pending','']; ?>
      <tr>
        <td class="code"><?=htmlspecialchars($o['order_code'])?></td>
        <td style="max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?=htmlspecialchars($o['product_name'])?></td>
        <td style="color:var(--t2)"><?=htmlspecialchars($o['game_user_id'])?></td>
        <td style="font-size:.8rem;color:var(--t3)"><?=htmlspecialchars($o['buyer_email'])?></td>
        <td style="color:var(--gold);font-family:var(--f-display);font-weight:700"><?=formatRupiah($o['amount'])?></td>
        <td><span class="badge badge-<?=$sl[0]?>"><?=$sl[1]?><?php if($sl[1]) echo ' '; ?><?=ucfirst($o['status'])?></span></td>
        <td style="font-size:.78rem;color:var(--t3)"><?=date('d/m H:i',strtotime($o['created_at']))?></td>
      </tr>
      <?php endforeach; endif; ?>
      </tbody>
    </table>
    </div>
  </div>
</div>
</div>
<?php include __DIR__.'/../includes/footer.php'; ?>