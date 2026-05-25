<?php
require_once __DIR__.'/../includes/functions.php';
Security::boot();
$pageTitle = 'Leaderboard — '.siteName();
$db = db();

$period = $_GET['period'] ?? 'month'; // month | alltime
$gameFilter = (int)($_GET['game'] ?? 0);

// Query top spenders
try {
    $where = "WHERE o.status = 'success'";
    $params = [];

    if ($period === 'month') {
        $where .= " AND MONTH(o.created_at)=MONTH(NOW()) AND YEAR(o.created_at)=YEAR(NOW())";
    }
    if ($gameFilter > 0) {
        $where .= " AND p.game_id = ?";
        $params[] = $gameFilter;
    }

    $topSpenders = $db->prepare("
        SELECT
            u.id,
            u.name,
            u.avatar,
            COUNT(o.id)        AS tx_count,
            SUM(o.amount)      AS total_spent,
            MAX(o.created_at)  AS last_tx,
            COALESCE(up.level, 'bronze') AS level
        FROM orders o
        JOIN users u ON u.id = o.user_id
        LEFT JOIN products p ON p.id = o.product_id
        LEFT JOIN user_points up ON up.user_id = u.id
        $where
          AND u.role = 'user'
        GROUP BY u.id, u.name, u.avatar, up.level
        ORDER BY total_spent DESC
        LIMIT 20
    ");
    $topSpenders->execute($params);
    $leaders = $topSpenders->fetchAll();

    // My rank (if logged in)
    $myRank = null;
    if (isLoggedIn()) {
        $uid = (int)$_SESSION['user_id'];
        $rankWhere = $where . " AND u.id != $uid";
        $rankStmt = $db->prepare("
            SELECT COUNT(DISTINCT u2.id)+1 AS rank
            FROM orders o2
            JOIN users u2 ON u2.id = o2.user_id
            $rankWhere
            AND (SELECT SUM(o3.amount) FROM orders o3 WHERE o3.user_id=u2.id AND o3.status='success'
                 " . ($period==='month' ? "AND MONTH(o3.created_at)=MONTH(NOW()) AND YEAR(o3.created_at)=YEAR(NOW())" : "") . ")
               > (SELECT COALESCE(SUM(o4.amount),0) FROM orders o4 WHERE o4.user_id=$uid AND o4.status='success'
                  " . ($period==='month' ? "AND MONTH(o4.created_at)=MONTH(NOW()) AND YEAR(o4.created_at)=YEAR(NOW())" : "") . ")
        ");
        $rankStmt->execute($params);
        $myRank = (int)$rankStmt->fetchColumn();

        // My total
        $myTotalStmt = $db->prepare("
            SELECT COALESCE(SUM(amount),0) FROM orders
            WHERE user_id=? AND status='success'
            " . ($period==='month' ? "AND MONTH(created_at)=MONTH(NOW()) AND YEAR(created_at)=YEAR(NOW())" : "")
        );
        $myTotalStmt->execute([$uid]);
        $myTotal = (int)$myTotalStmt->fetchColumn();
    }

    // Games for filter
    $games = $db->query("SELECT id,name FROM games WHERE is_active=1 ORDER BY name")->fetchAll();

} catch (\Exception $e) {
    $leaders = [];
    $games   = [];
    $myRank  = null;
    $myTotal = 0;
}

$levelConfig = [
    'bronze'   => ['icon'=>'🥉','color'=>'#cd7f32'],
    'silver'   => ['icon'=>'🥈','color'=>'#8892a4'],
    'gold'     => ['icon'=>'🥇','color'=>'#f5a623'],
    'platinum' => ['icon'=>'💎','color'=>'#60a5fa'],
];

include __DIR__.'/../includes/header.php';
?>
<style>
.lb-wrap { max-width: 860px; margin: 0 auto; padding: 28px 24px; }
.lb-hero { background: linear-gradient(135deg, #0e1120, #0a1428, #0a0c15); border-bottom: 1px solid var(--b1); padding: 34px 0 26px; }
.lb-hero-inner { max-width: 860px; margin: 0 auto; padding: 0 24px; }
.lb-podium { display: grid; grid-template-columns: 1fr 1.2fr 1fr; gap: 12px; margin-bottom: 24px; align-items: flex-end; }
.lb-pod-item { background: var(--card); border: 1.5px solid var(--b1); border-radius: 14px; padding: 18px 14px; text-align: center; position: relative; overflow: hidden; }
.lb-pod-item.first { border-color: rgba(245,166,35,.4); background: linear-gradient(180deg, rgba(245,166,35,.06), var(--card)); }
.lb-pod-item.second { border-color: rgba(137,143,164,.3); }
.lb-pod-item.third { border-color: rgba(205,127,50,.3); }
.lb-pod-rank { font-size: 1.6rem; margin-bottom: 8px; }
.lb-pod-avatar {
  width: 52px; height: 52px; border-radius: 50%; margin: 0 auto 8px;
  background: linear-gradient(135deg, var(--red), var(--red2));
  display: flex; align-items: center; justify-content: center;
  font-size: 1.1rem; font-weight: 800; color: #fff; font-family: var(--f-display);
  border: 2px solid rgba(255,255,255,.1);
}
.lb-pod-name { font-size: .82rem; font-weight: 700; color: var(--t1); margin-bottom: 4px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.lb-pod-amount { font-size: .9rem; font-weight: 800; color: var(--gold); font-family: var(--f-display); }
.lb-pod-tx { font-size: .66rem; color: var(--t3); margin-top: 2px; }
.lb-table { background: var(--card); border: 1px solid var(--b1); border-radius: 12px; overflow: hidden; }
.lb-row { display: flex; align-items: center; gap: 14px; padding: 12px 16px; border-bottom: 1px solid var(--b0); transition: background .12s; }
.lb-row:last-child { border-bottom: none; }
.lb-row:hover { background: rgba(227,24,55,.03); }
.lb-row.me { background: rgba(227,24,55,.05); border-left: 2px solid var(--red); }
.lb-rank-num { width: 28px; font-size: .82rem; font-weight: 700; color: var(--t3); text-align: center; flex-shrink: 0; }
.lb-avatar { width: 36px; height: 36px; border-radius: 50%; background: linear-gradient(135deg, #3b82f6, #2dd4bf); display: flex; align-items: center; justify-content: center; font-size: .82rem; font-weight: 800; color: #fff; flex-shrink: 0; }
.lb-name { flex: 1; font-size: .84rem; font-weight: 600; color: var(--t1); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.lb-amount { font-size: .84rem; font-weight: 700; color: var(--gold); white-space: nowrap; }
.lb-badge { font-size: .62rem; font-weight: 700; padding: 2px 7px; border-radius: 20px; }
.sec-label { font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .8px; color: var(--t3); margin-bottom: 14px; display: flex; align-items: center; gap: 8px; }
.sec-label::after { content: ''; flex: 1; height: 1px; background: var(--b1); }
@media(max-width: 480px) { .lb-podium { grid-template-columns: 1fr 1fr 1fr; gap: 8px; } .lb-pod-item { padding: 12px 8px; } }
</style>

<!-- Hero -->
<div class="lb-hero">
  <div class="lb-hero-inner">
    <div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:16px;">
      <div>
        <div style="font-family:var(--f-display);font-size:1.5rem;font-weight:800;color:var(--t1);display:flex;align-items:center;gap:10px;">
          🏆 Leaderboard
        </div>
        <div style="font-size:.82rem;color:var(--t3);margin-top:4px;">Top spender platform ftarastore</div>
      </div>
      <!-- Period & Filter -->
      <div style="display:flex;gap:8px;flex-wrap:wrap;">
        <a href="?period=month<?=$gameFilter?'&game='.$gameFilter:''?>" style="padding:7px 16px;border-radius:20px;font-size:.78rem;font-weight:600;text-decoration:none;<?=$period==='month'?'background:var(--red);color:#fff;':'background:var(--card);color:var(--t2);border:1px solid var(--b2);'?>">Bulan Ini</a>
        <a href="?period=alltime<?=$gameFilter?'&game='.$gameFilter:''?>" style="padding:7px 16px;border-radius:20px;font-size:.78rem;font-weight:600;text-decoration:none;<?=$period==='alltime'?'background:var(--red);color:#fff;':'background:var(--card);color:var(--t2);border:1px solid var(--b2);'?>">Sepanjang Masa</a>
      </div>
    </div>
  </div>
</div>

<div class="lb-wrap">

  <!-- My rank banner (if logged in) -->
  <?php if (isLoggedIn() && $myRank): ?>
  <div style="background:rgba(227,24,55,.06);border:1.5px solid rgba(227,24,55,.2);border-radius:10px;padding:12px 16px;margin-bottom:20px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;">
    <div style="display:flex;align-items:center;gap:10px;">
      <div style="font-size:1.3rem;">📍</div>
      <div>
        <div style="font-size:.82rem;font-weight:700;color:var(--t1);">Rank kamu: <span style="color:var(--red);">#<?=number_format($myRank)?></span></div>
        <div style="font-size:.72rem;color:var(--t3);">Total belanja: <?=formatRupiah($myTotal ?? 0)?> <?=$period==='month'?'bulan ini':'sepanjang masa'?></div>
      </div>
    </div>
    <a href="<?=asset('index.php')?>" style="font-size:.76rem;font-weight:700;color:var(--red);text-decoration:none;">Top Up lagi →</a>
  </div>
  <?php endif; ?>

  <!-- Podium Top 3 -->
  <?php if (count($leaders) >= 3): ?>
  <div class="sec-label">🥇 Top 3 Spender</div>
  <div class="lb-podium">
    <!-- 2nd -->
    <div class="lb-pod-item second" style="padding-top:14px;">
      <div class="lb-pod-rank">🥈</div>
      <div class="lb-pod-avatar" style="width:44px;height:44px;font-size:.95rem;">
        <?=strtoupper(substr($leaders[1]['name'],0,1))?>
      </div>
      <div class="lb-pod-name"><?=htmlspecialchars(explode(' ',$leaders[1]['name'])[0])?></div>
      <div class="lb-pod-amount" style="font-size:.8rem;"><?=formatRupiah($leaders[1]['total_spent'])?></div>
      <div class="lb-pod-tx"><?=number_format($leaders[1]['tx_count'])?> transaksi</div>
    </div>
    <!-- 1st -->
    <div class="lb-pod-item first">
      <div style="position:absolute;top:-1px;left:50%;transform:translateX(-50%);background:var(--gold);color:#0a0c15;font-size:.6rem;font-weight:800;padding:2px 10px;border-radius:0 0 8px 8px;letter-spacing:.5px;">CHAMPION</div>
      <div class="lb-pod-rank" style="font-size:2rem;">👑</div>
      <div class="lb-pod-avatar" style="width:60px;height:60px;font-size:1.2rem;border:2px solid var(--gold);">
        <?=strtoupper(substr($leaders[0]['name'],0,1))?>
      </div>
      <div class="lb-pod-name" style="font-size:.9rem;"><?=htmlspecialchars(explode(' ',$leaders[0]['name'])[0])?></div>
      <div class="lb-pod-amount"><?=formatRupiah($leaders[0]['total_spent'])?></div>
      <div class="lb-pod-tx"><?=number_format($leaders[0]['tx_count'])?> transaksi</div>
    </div>
    <!-- 3rd -->
    <div class="lb-pod-item third" style="padding-top:22px;">
      <div class="lb-pod-rank">🥉</div>
      <div class="lb-pod-avatar" style="width:40px;height:40px;font-size:.85rem;">
        <?=strtoupper(substr($leaders[2]['name'],0,1))?>
      </div>
      <div class="lb-pod-name" style="font-size:.78rem;"><?=htmlspecialchars(explode(' ',$leaders[2]['name'])[0])?></div>
      <div class="lb-pod-amount" style="font-size:.78rem;"><?=formatRupiah($leaders[2]['total_spent'])?></div>
      <div class="lb-pod-tx"><?=number_format($leaders[2]['tx_count'])?> transaksi</div>
    </div>
  </div>
  <?php endif; ?>

  <!-- Full Ranking -->
  <div class="sec-label">Ranking Lengkap</div>

  <?php if (empty($leaders)): ?>
  <div style="background:var(--card);border:1px solid var(--b1);border-radius:12px;padding:48px;text-align:center;color:var(--t3);">
    <div style="font-size:2.5rem;margin-bottom:12px;">🏆</div>
    <div style="font-size:.88rem;">Belum ada data leaderboard <?=$period==='month'?'bulan ini.':'.'?></div>
    <div style="font-size:.76rem;margin-top:6px;">Jadilah yang pertama masuk leaderboard!</div>
  </div>
  <?php else: ?>
  <div class="lb-table">
    <?php foreach ($leaders as $i => $l):
      $rank = $i + 1;
      $isMe = isLoggedIn() && $l['id'] == ($_SESSION['user_id'] ?? 0);
      $lc   = $levelConfig[$l['level']] ?? $levelConfig['bronze'];
      $rankIcon = match($rank) { 1=>'👑', 2=>'🥈', 3=>'🥉', default=>$rank };
    ?>
    <div class="lb-row <?=$isMe?'me':''?>">
      <div class="lb-rank-num">
        <?php if ($rank <= 3): ?>
          <span style="font-size:1.1rem;"><?=$rankIcon?></span>
        <?php else: ?>
          <span style="font-size:.82rem;color:var(--t3);"><?=$rank?></span>
        <?php endif; ?>
      </div>
      <div class="lb-avatar" style="<?=$rank===1?'border:1.5px solid var(--gold);background:linear-gradient(135deg,#d40000,#aa0000);':''?>">
        <?php if (!empty($l['avatar'])): ?>
          <img src="<?=htmlspecialchars($l['avatar'])?>" style="width:100%;height:100%;border-radius:50%;object-fit:cover;"/>
        <?php else: ?>
          <?=strtoupper(substr($l['name'],0,1))?>
        <?php endif; ?>
      </div>
      <div style="flex:1;min-width:0;">
        <div class="lb-name">
          <?=htmlspecialchars($l['name'])?>
          <?=$isMe?'<span style="font-size:.65rem;color:var(--red);font-weight:700;margin-left:4px;">(Kamu)</span>':''?>
        </div>
        <div style="font-size:.68rem;color:var(--t3);"><?=number_format($l['tx_count'])?> transaksi</div>
      </div>
      <span class="lb-badge" style="background:<?=$lc['color']?>22;color:<?=$lc['color']?>;border:1px solid <?=$lc['color']?>44;">
        <?=$lc['icon']?> <?=ucfirst($l['level'])?>
      </span>
      <div class="lb-amount"><?=formatRupiah($l['total_spent'])?></div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <!-- Info -->
  <div style="margin-top:20px;background:var(--card2);border:1px solid var(--b1);border-radius:10px;padding:14px 16px;font-size:.74rem;color:var(--t3);line-height:1.8;">
    📊 <strong style="color:var(--t2);">Cara masuk leaderboard:</strong> Lakukan top up game apapun, semakin banyak transaksi sukses, semakin tinggi ranking kamu.
    <br>🏆 Ranking diupdate otomatis setiap transaksi selesai.
    <br>🎁 Top 3 bulan ini mendapat badge eksklusif dan cashback bonus!
  </div>

</div>
<?php include __DIR__.'/../includes/footer.php'; ?>