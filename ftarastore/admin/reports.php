<?php
require_once __DIR__.'/../includes/functions.php';
Security::boot(['super_admin']);
$pageTitle = 'Laporan Revenue — Admin';

$period = $_GET['period'] ?? 'month';
$from   = $_GET['from']   ?? date('Y-m-01');
$to     = $_GET['to']     ?? date('Y-m-d');

switch($period){
    case 'today': $from = date('Y-m-d'); $to = date('Y-m-d'); break;
    case 'week':  $from = date('Y-m-d', strtotime('monday this week')); $to = date('Y-m-d'); break;
    case 'month': $from = date('Y-m-01'); $to = date('Y-m-d'); break;
    case 'year':  $from = date('Y-01-01'); $to = date('Y-m-d'); break;
}
$fromDt = $from.' 00:00:00';
$toDt   = $to.' 23:59:59';

// Previous period untuk perbandingan
$days  = (strtotime($to) - strtotime($from)) / 86400 + 1;
$prevFrom = date('Y-m-d', strtotime($from) - $days * 86400).' 00:00:00';
$prevTo   = date('Y-m-d', strtotime($from) - 86400).' 23:59:59';

$q = function($sql, $p=[]) { $s=db()->prepare($sql); $s->execute($p); return $s; };

// Stats utama
$totalRevenue   = (float)$q("SELECT COALESCE(SUM(amount),0) FROM orders WHERE status IN('success','paid') AND created_at BETWEEN ? AND ?",[$fromDt,$toDt])->fetchColumn();
$prevRevenue    = (float)$q("SELECT COALESCE(SUM(amount),0) FROM orders WHERE status IN('success','paid') AND created_at BETWEEN ? AND ?",[$prevFrom,$prevTo])->fetchColumn();
$totalModal     = (float)$q("SELECT COALESCE(SUM(p.price_modal),0) FROM orders o JOIN products p ON p.id=o.product_id WHERE o.status IN('success','paid') AND o.created_at BETWEEN ? AND ?",[$fromDt,$toDt])->fetchColumn();
$totalOrders    = (int)$q("SELECT COUNT(*) FROM orders WHERE created_at BETWEEN ? AND ?",[$fromDt,$toDt])->fetchColumn();
$prevOrders     = (int)$q("SELECT COUNT(*) FROM orders WHERE created_at BETWEEN ? AND ?",[$prevFrom,$prevTo])->fetchColumn();
$successOrders  = (int)$q("SELECT COUNT(*) FROM orders WHERE status IN('success','paid') AND created_at BETWEEN ? AND ?",[$fromDt,$toDt])->fetchColumn();
$failedOrders   = (int)$q("SELECT COUNT(*) FROM orders WHERE status='failed' AND created_at BETWEEN ? AND ?",[$fromDt,$toDt])->fetchColumn();
$pendingOrders  = (int)$q("SELECT COUNT(*) FROM orders WHERE status='pending' AND created_at BETWEEN ? AND ?",[$fromDt,$toDt])->fetchColumn();
$totalUsers     = (int)$q("SELECT COUNT(DISTINCT buyer_email) FROM orders WHERE created_at BETWEEN ? AND ?",[$fromDt,$toDt])->fetchColumn();
$profit         = $totalRevenue - $totalModal;
$margin         = $totalRevenue > 0 ? round($profit/$totalRevenue*100,1) : 0;
$successRate    = $totalOrders > 0 ? round($successOrders/$totalOrders*100,1) : 0;
$avgOrder       = $successOrders > 0 ? round($totalRevenue/$successOrders) : 0;

// Growth
$revenueGrowth  = $prevRevenue > 0 ? round(($totalRevenue-$prevRevenue)/$prevRevenue*100,1) : 0;
$orderGrowth    = $prevOrders > 0 ? round(($totalOrders-$prevOrders)/$prevOrders*100,1) : 0;

// Chart data
$dailyData   = $q("SELECT DATE(created_at) tgl, COUNT(*) cnt, COALESCE(SUM(amount),0) rev FROM orders WHERE status IN('success','paid') AND created_at>=DATE_SUB(NOW(),INTERVAL 30 DAY) GROUP BY DATE(created_at) ORDER BY tgl ASC")->fetchAll();
$topGameRev  = $q("SELECT g.name, COUNT(o.id) cnt, COALESCE(SUM(o.amount),0) rev FROM orders o JOIN products p ON p.id=o.product_id JOIN games g ON g.id=p.game_id WHERE o.status IN('success','paid') AND o.created_at BETWEEN ? AND ? GROUP BY g.id, g.name ORDER BY rev DESC LIMIT 8",[$fromDt,$toDt])->fetchAll();
$topGameOrd  = $q("SELECT g.name, g.image_url, COUNT(o.id) cnt FROM orders o JOIN products p ON p.id=o.product_id JOIN games g ON g.id=p.game_id WHERE o.status IN('success','paid') AND o.created_at BETWEEN ? AND ? GROUP BY g.id, g.name, g.image_url ORDER BY cnt DESC LIMIT 6",[$fromDt,$toDt])->fetchAll();
$topProducts = $q("SELECT o.product_name, g.name game_name, COUNT(o.id) cnt, COALESCE(SUM(o.amount),0) rev FROM orders o JOIN products p ON p.id=o.product_id JOIN games g ON g.id=p.game_id WHERE o.status IN('success','paid') AND o.created_at BETWEEN ? AND ? GROUP BY o.product_name, g.name ORDER BY cnt DESC LIMIT 5",[$fromDt,$toDt])->fetchAll();
$hourData    = $q("SELECT HOUR(created_at) h, COUNT(*) cnt FROM orders WHERE status IN('success','paid') AND created_at BETWEEN ? AND ? GROUP BY HOUR(created_at)",[$fromDt,$toDt])->fetchAll(PDO::FETCH_KEY_PAIR);
$recent      = $q("SELECT o.order_code,o.amount,o.status,o.created_at,o.product_name,g.name game_name,o.buyer_email FROM orders o JOIN products p ON p.id=o.product_id JOIN games g ON g.id=p.game_id WHERE o.created_at BETWEEN ? AND ? ORDER BY o.created_at DESC LIMIT 10",[$fromDt,$toDt])->fetchAll();

// Export CSV
if(isset($_GET['export']) && $_GET['export']==='csv'){
    $allOrders = $q("SELECT o.order_code,o.buyer_email,o.product_name,g.name game_name,o.amount,o.status,o.created_at FROM orders o JOIN products p ON p.id=o.product_id JOIN games g ON g.id=p.game_id WHERE o.created_at BETWEEN ? AND ? ORDER BY o.created_at DESC",[$fromDt,$toDt])->fetchAll();
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="laporan-'.date('Ymd').'.csv"');
    $out = fopen('php://output','w');
    fputcsv($out,['Kode Order','Email','Produk','Game','Total','Status','Waktu']);
    foreach($allOrders as $r){
        fputcsv($out,[$r['order_code'],$r['buyer_email'],$r['product_name'],$r['game_name'],'Rp '.number_format($r['amount'],0,',','.'),$r['status'],date('d/m/Y H:i',strtotime($r['created_at']))]);
    }
    fclose($out); exit;
}

$statusData  = ['Sukses'=>$successOrders,'Pending'=>$pendingOrders,'Gagal'=>$failedOrders,'Lainnya'=>max(0,$totalOrders-$successOrders-$pendingOrders-$failedOrders)];
$dailyLabels = json_encode(array_map(fn($d)=>date('d/m',strtotime($d['tgl'])),$dailyData));
$dailyRevs   = json_encode(array_map(fn($d)=>(float)$d['rev'],$dailyData));
$dailyCnts   = json_encode(array_map(fn($d)=>(int)$d['cnt'],$dailyData));
$gameNames   = json_encode(array_map(fn($g)=>$g['name'],$topGameRev));
$gameRevs    = json_encode(array_map(fn($g)=>(float)$g['rev'],$topGameRev));
$gameOrdN    = json_encode(array_map(fn($g)=>$g['name'],$topGameOrd));
$gameOrdC    = json_encode(array_map(fn($g)=>(int)$g['cnt'],$topGameOrd));
$pieLabels   = json_encode(array_keys($statusData));
$pieData     = json_encode(array_values($statusData));

include __DIR__.'/../includes/header.php';
?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<style>
.kpi-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:18px}
.kpi{background:var(--card);border-radius:14px;padding:18px 20px;border:1.5px solid var(--b1);box-shadow:0 2px 12px rgba(14,100,180,.07);position:relative;overflow:hidden}
.kpi::before{content:'';position:absolute;top:0;left:0;right:0;height:3px}
.kpi.blue::before{background:linear-gradient(90deg,#d40000,#aa0000)}
.kpi.green::before{background:linear-gradient(90deg,#22d3a0,#16a34a)}
.kpi.amber::before{background:linear-gradient(90deg,#ffaa33,#e07800)}
.kpi.purple::before{background:linear-gradient(90deg,#a78bfa,#7c3aed)}
.kpi.red::before{background:linear-gradient(90deg,#f87171,#dc2626)}
.kpi.teal::before{background:linear-gradient(90deg,#2dd4bf,#0d9488)}
.kpi.indigo::before{background:linear-gradient(90deg,#818cf8,#4f46e5)}
.kpi.orange::before{background:linear-gradient(90deg,#fb923c,#ea580c)}
.kpi-label{font-size:.72rem;font-weight:700;letter-spacing:.8px;text-transform:uppercase;color:var(--t3);margin-bottom:6px}
.kpi-val{font-family:var(--f-display);font-size:1.6rem;font-weight:800;color:var(--t1);line-height:1.1}
.kpi-sub{font-size:.72rem;color:var(--t3);margin-top:4px;display:flex;align-items:center;gap:4px}
.kpi-growth{display:inline-flex;align-items:center;gap:3px;font-size:.72rem;font-weight:700;padding:2px 7px;border-radius:10px}
.kpi-growth.up{background:rgba(34,197,94,.1);color:#16a34a}
.kpi-growth.down{background:rgba(239,68,68,.1);color:#dc2626}
.kpi-growth.flat{background:rgba(107,114,128,.1);color:#6b7280}
.chart-card{background:var(--card);border-radius:14px;border:1.5px solid var(--b1);box-shadow:0 2px 12px rgba(14,100,180,.07);overflow:hidden}
.chart-head{padding:14px 18px;border-bottom:1px solid var(--b0);display:flex;justify-content:space-between;align-items:center}
.chart-head h3{font-size:.88rem;font-weight:700;color:var(--t1);display:flex;align-items:center;gap:7px;margin:0}
.chart-body{padding:18px 20px}
.rank-item{display:flex;align-items:center;gap:12px;padding:10px 0;border-bottom:1px solid var(--b0)}
.rank-item:last-child{border-bottom:none}
.rank-num{width:24px;height:24px;border-radius:6px;display:flex;align-items:center;justify-content:center;font-family:var(--f-display);font-weight:800;font-size:.75rem;flex-shrink:0}
</style>

<div class="admin-wrap">
<?php include __DIR__.'/sidebar.php'; ?>
<div class="admin-main">

 <!-- Header + Filter -->
 <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:12px;">
 <div class="admin-title" style="margin:0;display:flex;align-items:center;gap:10px;">
 <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
      Laporan Revenue
 </div>
 <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
 <!-- Export CSV -->
 <a href="?period=<?=$period?>&from=<?=$from?>&to=<?=$to?>&export=csv"
         style="display:flex;align-items:center;gap:6px;padding:8px 14px;background:rgba(34,197,94,.08);border:1.5px solid rgba(34,197,94,.2);border-radius:8px;font-size:.78rem;font-weight:600;color:#16a34a;text-decoration:none;">
 <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
        Export CSV
 </a>
 <!-- Period filter -->
 <form method="GET" style="display:flex;gap:6px;align-items:center;flex-wrap:wrap;">
 <?php foreach(['today'=>'Hari Ini','week'=>'Minggu Ini','month'=>'Bulan Ini','year'=>'Tahun Ini'] as $k=>$v): ?>
 <button type="submit" name="period" value="<?=$k?>" style="padding:6px 13px;border-radius:6px;font-size:.77rem;font-weight:600;cursor:pointer;border:1px solid;<?=$period===$k?'background:var(--red);color:#03111f;border-color:var(--cyan);':'background:var(--card);border-color:var(--b2);color:var(--t2);'?>"><?=$v?></button>
 <?php endforeach; ?>
 <input type="date" name="from" value="<?=$from?>" class="finput" style="padding:6px 10px;font-size:.77rem;width:128px;">
 <span style="color:var(--t3)">—</span>
 <input type="date" name="to" value="<?=$to?>" class="finput" style="padding:6px 10px;font-size:.77rem;width:128px;">
 <button type="submit" name="period" value="custom" class="btn-gold" style="padding:7px 14px;font-size:.77rem;">Terapkan</button>
 </form>
 </div>
 </div>

 <!-- KPI ROW 1: Revenue & Orders -->
 <div class="kpi-grid">
 <div class="kpi blue">
 <div class="kpi-label">Total Revenue</div>
 <div class="kpi-val"><?=formatRupiah($totalRevenue)?></div>
 <div class="kpi-sub">
 <?php $g=$revenueGrowth; $cls=$g>0?'up':($g<0?'down':'flat'); $arrow=$g>0?'↑':($g<0?'↓':'→'); ?>
 <span class="kpi-growth <?=$cls?>"><?=$arrow?> <?=abs($g)?>% vs periode lalu</span>
 </div>
 </div>
 <div class="kpi green">
 <div class="kpi-label">Estimasi Profit</div>
 <div class="kpi-val"><?=formatRupiah($profit)?></div>
 <div class="kpi-sub"><span style="color:var(--t3)">Margin <?=$margin?>% dari revenue</span></div>
 </div>
 <div class="kpi amber">
 <div class="kpi-label">Total Order</div>
 <div class="kpi-val"><?=number_format($totalOrders)?></div>
 <div class="kpi-sub">
 <?php $g=$orderGrowth; $cls=$g>0?'up':($g<0?'down':'flat'); $arrow=$g>0?'↑':($g<0?'↓':'→'); ?>
 <span class="kpi-growth <?=$cls?>"><?=$arrow?> <?=abs($g)?>% vs periode lalu</span>
 </div>
 </div>
 <div class="kpi purple">
 <div class="kpi-label">Success Rate</div>
 <div class="kpi-val" style="color:<?=$successRate>=80?'#16a34a':($successRate>=50?'#e07800':'#dc2626')?>"><?=$successRate?>%</div>
 <div class="kpi-sub"><span style="color:var(--t3)"><?=$successOrders?> sukses · <?=$failedOrders?> gagal</span></div>
 </div>
 </div>

 <!-- KPI ROW 2 -->
 <div class="kpi-grid" style="margin-bottom:18px;">
 <div class="kpi teal">
 <div class="kpi-label">Avg. Order Value</div>
 <div class="kpi-val" style="font-size:1.2rem;"><?=formatRupiah($avgOrder)?></div>
 <div class="kpi-sub"><span style="color:var(--t3)">Per transaksi sukses</span></div>
 </div>
 <div class="kpi indigo">
 <div class="kpi-label">Pembeli Unik</div>
 <div class="kpi-val"><?=number_format($totalUsers)?></div>
 <div class="kpi-sub"><span style="color:var(--t3)">Email berbeda</span></div>
 </div>
 <div class="kpi orange">
 <div class="kpi-label">Order Pending</div>
 <div class="kpi-val" style="color:#e07800;"><?=number_format($pendingOrders)?></div>
 <div class="kpi-sub"><span style="color:var(--t3)">Menunggu pembayaran</span></div>
 </div>
 <div class="kpi red">
 <div class="kpi-label">Order Gagal</div>
 <div class="kpi-val" style="color:#dc2626;"><?=number_format($failedOrders)?></div>
 <div class="kpi-sub"><span style="color:var(--t3)">Dibatalkan / expired</span></div>
 </div>
 </div>

 <!-- ROW 1: Line Chart + Pie -->
 <div style="display:grid;grid-template-columns:1fr 300px;gap:16px;margin-bottom:16px;">
 <div class="chart-card">
 <div class="chart-head">
 <h3>Tren Revenue 30 Hari</h3>
 <span style="font-size:.71rem;color:var(--t3);">Biru = revenue · Teal = jumlah order</span>
 </div>
 <div class="chart-body">
 <?php if(empty($dailyData)): ?>
 <div style="text-align:center;padding:48px;color:var(--t3);">Belum ada data</div>
 <?php else: ?>
 <canvas id="chartLine" height="130"></canvas>
 <?php endif; ?>
 </div>
 </div>
 <div class="chart-card">
 <div class="chart-head"><h3>Status Order</h3></div>
 <div class="chart-body" style="display:flex;flex-direction:column;align-items:center;">
 <canvas id="chartPie" width="180" height="180"></canvas>
 <div style="margin-top:14px;width:100%;">
 <?php $pieColors=['#22d3a0','#ffaa33','#f87171','#94a3b8']; $i=0;
          foreach($statusData as $label=>$val): if($val<=0){$i++;continue;} ?>
 <div style="display:flex;justify-content:space-between;padding:5px 0;font-size:.78rem;">
 <div style="display:flex;align-items:center;gap:7px;">
 <span style="width:10px;height:10px;border-radius:50%;background:<?=$pieColors[$i]?>;display:block;flex-shrink:0;"></span><?=$label?>
 </div>
 <span style="font-weight:700;"><?=$val?></span>
 </div>
 <?php $i++; endforeach; ?>
 </div>
 </div>
 </div>
 </div>

 <!-- ROW 2: Bar Game Revenue + Horizontal Bar -->
 <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
 <div class="chart-card">
 <div class="chart-head"><h3>Top Game — Revenue</h3></div>
 <div class="chart-body">
 <?php if(empty($topGameRev)): ?>
 <div style="text-align:center;padding:48px;color:var(--t3);">Belum ada data</div>
 <?php else: ?>
 <canvas id="chartGameRev" height="200"></canvas>
 <?php endif; ?>
 </div>
 </div>
 <div class="chart-card">
 <div class="chart-head"><h3>Top Game — Jumlah Order</h3></div>
 <div class="chart-body">
 <?php if(empty($topGameOrd)): ?>
 <div style="text-align:center;padding:48px;color:var(--t3);">Belum ada data</div>
 <?php else: ?>
 <canvas id="chartGameOrd" height="200"></canvas>
 <?php endif; ?>
 </div>
 </div>
 </div>

 <!-- ROW 3: Heatmap + Top Produk + Top Game Cards -->
 <div style="display:grid;grid-template-columns:1fr 340px;gap:16px;margin-bottom:16px;">
 <div class="chart-card">
 <div class="chart-head">
 <h3>Jam Tersibuk</h3>
 <span style="font-size:.71rem;color:var(--t3);">Warna gelap = lebih ramai</span>
 </div>
 <div class="chart-body">
 <div style="display:grid;grid-template-columns:repeat(12,1fr);gap:5px;margin-bottom:8px;">
 <?php $maxH=max(array_values($hourData)?:[1])?:1;
          for($h=0;$h<24;$h++):
            $cnt=$hourData[$h]??0;
            $op=$cnt>0?max(0.12,$cnt/$maxH):0.04;
          ?>
 <div title="<?=str_pad($h,2,'0',STR_PAD_LEFT)?>:00 — <?=$cnt?> order"
               style="aspect-ratio:1;border-radius:5px;background:rgba(227,24,55,<?=number_format($op,2)?>);display:flex;align-items:center;justify-content:center;font-size:.58rem;color:<?=$cnt>0?'var(--t1)':'var(--t3)'?>;cursor:default;transition:transform .15s;"
               onmouseover="this.style.transform='scale(1.2)'" onmouseout="this.style.transform=''">
 <?=$h?>
 </div>
 <?php endfor; ?>
 </div>
 <div style="display:flex;align-items:center;gap:8px;font-size:.72rem;color:var(--t3);">
          Sepi
 <?php foreach([0.04,0.2,0.4,0.6,0.8,1] as $op): ?>
 <div style="width:14px;height:14px;border-radius:3px;background:rgba(227,24,55,<?=$op?>);flex-shrink:0;"></div>
 <?php endforeach; ?>
          Ramai
 </div>
 </div>
 </div>

 <div class="chart-card">
 <div class="chart-head"><h3>Produk Terlaris</h3></div>
 <div class="chart-body">
 <?php if(empty($topProducts)): ?>
 <div style="text-align:center;padding:28px;color:var(--t3);">Belum ada data</div>
 <?php else:
        $maxP=max(array_column($topProducts,'cnt'))?:1;
        $medals=['#1','#2','#3','4⃣','5⃣'];
        foreach($topProducts as $i=>$p): $pct=round($p['cnt']/$maxP*100); ?>
 <div style="margin-bottom:14px;">
 <div style="display:flex;justify-content:space-between;margin-bottom:4px;gap:8px;">
 <span style="font-size:.78rem;font-weight:600;"><?=$medals[$i]?> <?=htmlspecialchars($p['product_name'])?></span>
 <span style="font-size:.72rem;color:var(--cyan);font-weight:700;flex-shrink:0;"><?=$p['cnt']?>x</span>
 </div>
 <div style="height:6px;background:var(--b1);border-radius:3px;overflow:hidden;">
 <div style="height:100%;width:<?=$pct?>%;background:linear-gradient(90deg,#aa0000,#2dd4bf);border-radius:3px;transition:width .6s;"></div>
 </div>
 <div style="font-size:.68rem;color:var(--t3);margin-top:2px;"><?=htmlspecialchars($p['game_name'])?> · <?=formatRupiah($p['rev'])?></div>
 </div>
 <?php endforeach; endif; ?>
 </div>
 </div>
 </div>

 <!-- Top Game Cards Visual -->
 <?php if(!empty($topGameOrd)): ?>
 <div class="chart-card" style="margin-bottom:16px;">
 <div class="chart-head"><h3>Game Populer — Visual Ranking</h3></div>
 <div class="chart-body">
 <div style="display:grid;grid-template-columns:repeat(6,1fr);gap:12px;">
 <?php foreach($topGameOrd as $i=>$g):
          $colors=['linear-gradient(135deg,#ffaa33,#ff8c00)','linear-gradient(135deg,#94a3b8,#64748b)','linear-gradient(135deg,#fb923c,#ea580c)','linear-gradient(135deg,#d40000,#aa0000)','linear-gradient(135deg,#a78bfa,#7c3aed)','linear-gradient(135deg,#34d399,#059669)'];
        ?>
 <div style="text-align:center;padding:14px 10px;background:var(--card2);border-radius:12px;border:1.5px solid var(--b1);position:relative;">
 <div style="position:absolute;top:-8px;left:50%;transform:translateX(-50%);width:22px;height:22px;border-radius:6px;background:<?=$colors[$i]?>;display:flex;align-items:center;justify-content:center;font-family:var(--f-display);font-weight:800;font-size:.72rem;color:white;"><?=$i+1?></div>
 <?php if($g['image_url']): ?>
 <img src="<?=htmlspecialchars($g['image_url'])?>" style="width:48px;height:48px;border-radius:10px;object-fit:cover;margin:8px auto 8px;display:block;"/>
 <?php else: ?>
 <div style="width:48px;height:48px;border-radius:10px;background:var(--b1);margin:8px auto;display:flex;align-items:center;justify-content:center;font-size:1.4rem;"></div>
 <?php endif; ?>
 <div style="font-size:.75rem;font-weight:700;color:var(--t1);margin-bottom:3px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?=htmlspecialchars($g['name'])?></div>
 <div style="font-size:.7rem;font-weight:700;color:var(--cyan);"><?=number_format($g['cnt'])?> order</div>
 </div>
 <?php endforeach; ?>
 </div>
 </div>
 </div>
 <?php endif; ?>

  <!-- ROW 4: Revenue vs Modal Bar + Pie Distribution -->
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
    <div class="chart-card">
      <div class="chart-head">
        <h3>Diagram Batang — Revenue vs Est. Modal</h3>
        <span style="font-size:.71rem;color:var(--t3);">Biru = revenue · Ungu = estimasi modal</span>
      </div>
      <div class="chart-body">
        <?php if(empty($topGameRev)): ?>
        <div style="text-align:center;padding:48px;color:var(--t3);">Belum ada data</div>
        <?php else: ?>
        <canvas id="chartRevenueModal" height="200"></canvas>
        <?php endif; ?>
      </div>
    </div>
    <div class="chart-card">
      <div class="chart-head">
        <h3>Diagram Lingkaran — Distribusi Revenue</h3>
      </div>
      <div class="chart-body" style="display:flex;gap:16px;align-items:center;">
        <?php if(empty($topGameRev)): ?>
        <div style="text-align:center;padding:48px;color:var(--t3);width:100%;">Belum ada data</div>
        <?php else: ?>
        <canvas id="chartRevPie" width="170" height="170" style="flex-shrink:0;"></canvas>
        <div style="flex:1;min-width:0;">
          <?php
          $gamePieColors=['#d40000','#22d3a0','#a78bfa','#f87171','#fb923c','#34d399','#818cf8','#94a3b8'];
          foreach($topGameRev as $i=>$g): ?>
          <div style="display:flex;align-items:center;justify-content:space-between;padding:5px 0;font-size:.74rem;border-bottom:1px solid var(--b0);">
            <div style="display:flex;align-items:center;gap:6px;">
              <span style="width:8px;height:8px;border-radius:50%;background:<?=$gamePieColors[$i%8]?>;flex-shrink:0;display:block;"></span>
              <span style="max-width:90px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?=htmlspecialchars($g['name'])?></span>
            </div>
            <span style="font-weight:700;color:var(--cyan);font-size:.7rem;"><?=formatRupiah($g['rev'])?></span>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Statistik Ringkasan -->
  <div class="chart-card" style="margin-bottom:16px;">
    <div class="chart-head"><h3>Statistik Ringkasan</h3></div>
    <div class="chart-body">
      <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:16px;">
        <div style="text-align:center;padding:14px;background:var(--card2);border-radius:12px;border:1px solid var(--b1);">
          <div style="font-size:.68rem;color:var(--t3);text-transform:uppercase;letter-spacing:.08em;margin-bottom:6px;">Konversi</div>
          <div style="font-size:1.8rem;font-weight:800;font-family:var(--f-display);color:<?=$successRate>=80?'#22d3a0':($successRate>=50?'#ffaa33':'#f87171')?>"><?=$successRate?>%</div>
          <div style="font-size:.68rem;color:var(--t3);margin-top:3px;">Order sukses / total</div>
        </div>
        <div style="text-align:center;padding:14px;background:var(--card2);border-radius:12px;border:1px solid var(--b1);">
          <div style="font-size:.68rem;color:var(--t3);text-transform:uppercase;letter-spacing:.08em;margin-bottom:6px;">Margin Profit</div>
          <div style="font-size:1.8rem;font-weight:800;font-family:var(--f-display);color:#d40000"><?=$margin?>%</div>
          <div style="font-size:.68rem;color:var(--t3);margin-top:3px;"><?=formatRupiah($profit)?> profit</div>
        </div>
        <div style="text-align:center;padding:14px;background:var(--card2);border-radius:12px;border:1px solid var(--b1);">
          <div style="font-size:.68rem;color:var(--t3);text-transform:uppercase;letter-spacing:.08em;margin-bottom:6px;">Avg. Order</div>
          <div style="font-size:1.4rem;font-weight:800;font-family:var(--f-display);color:#a78bfa"><?=formatRupiah($avgOrder)?></div>
          <div style="font-size:.68rem;color:var(--t3);margin-top:3px;">Per transaksi sukses</div>
        </div>
        <div style="text-align:center;padding:14px;background:var(--card2);border-radius:12px;border:1px solid var(--b1);">
          <div style="font-size:.68rem;color:var(--t3);text-transform:uppercase;letter-spacing:.08em;margin-bottom:6px;">Growth</div>
          <div style="font-size:1.8rem;font-weight:800;font-family:var(--f-display);color:<?=$revenueGrowth>=0?'#22d3a0':'#f87171'?>"><?=$revenueGrowth>=0?'+':''?><?=$revenueGrowth?>%</div>
          <div style="font-size:.68rem;color:var(--t3);margin-top:3px;">vs periode lalu</div>
        </div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
        <div>
          <div style="display:flex;justify-content:space-between;font-size:.75rem;margin-bottom:5px;">
            <span style="color:var(--t3);">Order Sukses</span><span style="color:#22d3a0;font-weight:700;"><?=$successOrders?> / <?=$totalOrders?></span>
          </div>
          <div style="height:7px;background:var(--b1);border-radius:4px;overflow:hidden;">
            <div style="height:100%;width:<?=$totalOrders>0?round($successOrders/$totalOrders*100):0?>%;background:linear-gradient(90deg,#22d3a0,#0d9488);border-radius:4px;"></div>
          </div>
        </div>
        <div>
          <div style="display:flex;justify-content:space-between;font-size:.75rem;margin-bottom:5px;">
            <span style="color:var(--t3);">Pembeli Unik</span><span style="color:#d40000;font-weight:700;"><?=number_format($totalUsers)?> user</span>
          </div>
          <div style="height:7px;background:var(--b1);border-radius:4px;overflow:hidden;">
            <div style="height:100%;width:<?=min(100,$totalUsers>0?min(100,round($totalUsers/max(1,$totalOrders)*200)):0)?>%;background:linear-gradient(90deg,#d40000,#aa0000);border-radius:4px;"></div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Tabel Terbaru -->
  <div class="admin-card">
 <div class="admin-card-head">
 <h3>Transaksi Terbaru</h3>
 <a href="<?=asset('admin/transactions.php')?>" style="font-size:.76rem;color:var(--cyan);">Lihat semua →</a>
 </div>
 <div class="table-wrap">
 <table class="dtable">
 <thead><tr><th>Kode Order</th><th>Game · Produk</th><th>Email</th><th>Total</th><th>Status</th><th>Waktu</th></tr></thead>
 <tbody>
 <?php if(empty($recent)): ?>
 <tr><td colspan="6" style="text-align:center;padding:36px;color:var(--t3);">Tidak ada transaksi</td></tr>
 <?php else: foreach($recent as $r):
          $bc=match($r['status']){'success','paid'=>'badge-success','pending'=>'badge-pending','processing'=>'badge-process',default=>'badge-failed'};
        ?>
 <tr>
 <td><span style="font-family:var(--f-display);font-weight:700;color:var(--cyan);font-size:.82rem;"><?=htmlspecialchars($r['order_code'])?></span></td>
 <td>
 <div style="font-weight:600;font-size:.83rem;"><?=htmlspecialchars($r['game_name'])?></div>
 <div style="font-size:.71rem;color:var(--t3);"><?=htmlspecialchars($r['product_name'])?></div>
 </td>
 <td style="font-size:.78rem;color:var(--t2);"><?=htmlspecialchars($r['buyer_email'])?></td>
 <td style="font-weight:700;color:var(--cyan);font-family:var(--f-display);"><?=formatRupiah($r['amount'])?></td>
 <td><span class="badge <?=$bc?>" style="font-size:.68rem;"><?=ucfirst($r['status'])?></span></td>
 <td style="font-size:.75rem;color:var(--t3);"><?=date('d/m/y H:i',strtotime($r['created_at']))?></td>
 </tr>
 <?php endforeach; endif; ?>
 </tbody>
 </table>
 </div>
 </div>

</div>
</div>

<script>
Chart.defaults.color='#8ab4d8';
Chart.defaults.borderColor='rgba(100,180,255,0.07)';
Chart.defaults.font.family="'Inter',sans-serif";
Chart.defaults.color='#94a3b8';

<?php if(!empty($dailyData)): ?>
new Chart(document.getElementById('chartLine'),{type:'bar',data:{labels:<?=$dailyLabels?>,datasets:[{type:'line',label:'Revenue',data:<?=$dailyRevs?>,borderColor:'#d40000',backgroundColor:'rgba(227,24,55,0.06)',borderWidth:2.5,pointRadius:3,pointBackgroundColor:'#d40000',tension:0.4,fill:true,yAxisID:'y'},{type:'bar',label:'Order',data:<?=$dailyCnts?>,backgroundColor:'rgba(45,212,191,0.2)',borderColor:'rgba(45,212,191,0.4)',borderWidth:1,borderRadius:5,yAxisID:'y1'}]},options:{responsive:true,interaction:{mode:'index',intersect:false},plugins:{legend:{position:'top',labels:{boxWidth:12,padding:14}}},scales:{y:{position:'left',grid:{color:'rgba(100,180,255,0.05)'},ticks:{callback:v=>'Rp '+v.toLocaleString('id')}},y1:{position:'right',grid:{drawOnChartArea:false},ticks:{stepSize:1}}}}});
<?php endif; ?>

new Chart(document.getElementById('chartPie'),{type:'doughnut',data:{labels:<?=$pieLabels?>,datasets:[{data:<?=$pieData?>,backgroundColor:['#22d3a0','#d40000','#f87171','#94a3b8'],borderColor:'rgba(15,30,56,0.5)',borderWidth:2,hoverOffset:10}]},options:{responsive:false,cutout:'68%',plugins:{legend:{display:false}}}});

<?php if(!empty($topGameRev)): ?>
new Chart(document.getElementById('chartGameRev'),{type:'bar',data:{labels:<?=$gameNames?>,datasets:[{label:'Revenue',data:<?=$gameRevs?>,backgroundColor:['rgba(227,24,55,.75)','rgba(45,212,191,.75)','rgba(34,211,160,.75)','rgba(96,165,250,.75)','rgba(167,139,250,.75)','rgba(248,113,113,.75)','rgba(167,139,250,.75)','rgba(52,211,153,.75)'],borderRadius:7,borderWidth:0}]},options:{responsive:true,plugins:{legend:{display:false}},scales:{x:{grid:{display:false},ticks:{maxRotation:30,font:{size:11}}},y:{grid:{color:'rgba(100,180,255,0.05)'},ticks:{callback:v=>'Rp '+v.toLocaleString('id')}}}}});
<?php endif; ?>

<?php if(!empty($topGameOrd)): ?>
new Chart(document.getElementById('chartGameOrd'),{type:'bar',data:{labels:<?=$gameOrdN?>,datasets:[{label:'Order',data:<?=$gameOrdC?>,backgroundColor:'rgba(227,24,55,0.12)',borderColor:'#d40000',borderWidth:2,borderRadius:6}]},options:{indexAxis:'y',responsive:true,plugins:{legend:{display:false}},scales:{x:{grid:{color:'rgba(100,180,255,0.05)'},ticks:{stepSize:1}},y:{grid:{display:false}}}}});
<?php endif; ?>
</script>


<?php if(!empty($topGameRev)): ?>
// Diagram Batang: Revenue vs Modal per Game
<?php
$gameModalData = db()->prepare("SELECT g.name, COALESCE(SUM(p.price_modal*o.id/o.id),0) AS avg_modal, COUNT(o.id) AS cnt FROM orders o JOIN products p ON p.id=o.product_id JOIN games g ON g.id=p.game_id WHERE o.status IN('success','paid') AND o.created_at BETWEEN ? AND ? GROUP BY g.id,g.name ORDER BY COUNT(o.id) DESC LIMIT 8");
$gameModalData->execute([$fromDt,$toDt]);
$gameModalArr = $gameModalData->fetchAll();
$modalNames = json_encode(array_map(fn($g)=>$g['name'],$gameModalArr));
$modalRevArr = [];
$modalCostArr = [];
foreach($topGameRev as $g){
    $modalRevArr[] = (float)$g['rev'];
    $modalCostArr[] = (float)$g['rev'] * (1 - $margin/100);
}
$modalRevJson = json_encode($modalRevArr);
$modalCostJson = json_encode($modalCostArr);
$revPieData = json_encode(array_map(fn($g)=>(float)$g['rev'],$topGameRev));
$revPieNames = json_encode(array_map(fn($g)=>$g['name'],$topGameRev));
?>
new Chart(document.getElementById('chartRevenueModal'),{type:'bar',data:{labels:<?=$gameNames?>,datasets:[{label:'Revenue',data:<?=$modalRevJson?>,backgroundColor:'rgba(212,0,0,.7)',borderRadius:6,borderWidth:0},{label:'Est. Modal',data:<?=$modalCostJson?>,backgroundColor:'rgba(167,139,250,.5)',borderRadius:6,borderWidth:0}]},options:{responsive:true,plugins:{legend:{position:'top',labels:{boxWidth:10,padding:12}}},scales:{x:{grid:{display:false},ticks:{maxRotation:25,font:{size:11}}},y:{grid:{color:'rgba(100,180,255,0.05)'},ticks:{callback:v=>'Rp '+v.toLocaleString('id')}}}}});

new Chart(document.getElementById('chartRevPie'),{type:'pie',data:{labels:<?=$revPieNames?>,datasets:[{data:<?=$revPieData?>,backgroundColor:['#d40000','#22d3a0','#a78bfa','#f87171','#fb923c','#34d399','#818cf8','#94a3b8'],borderColor:'rgba(15,30,56,0.4)',borderWidth:2,hoverOffset:8}]},options:{responsive:false,plugins:{legend:{display:false},tooltip:{callbacks:{label:function(ctx){return ctx.label+': Rp '+ctx.parsed.toLocaleString('id');}}}}}});
<?php endif; ?>

<?php include __DIR__.'/../includes/footer.php'; ?>