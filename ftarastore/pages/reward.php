<?php
require_once __DIR__.'/../includes/functions.php';
Security::boot();
$pageTitle = 'Reward & Poin — '.siteName();
$db = db();

$userPoints  = 0;
$userLevel   = 'bronze';
$totalSpent  = 0;
$pointHistory = [];

if (isLoggedIn()) {
    $uid = (int)$_SESSION['user_id'];
    try {
        $row = $db->prepare("SELECT * FROM user_points WHERE user_id=?");
        $row->execute([$uid]);
        $up = $row->fetch();
        if ($up) {
            $userPoints  = (int)$up['points'];
            $userLevel   = $up['level'];
            $totalSpent  = (int)$up['total_spent'];
        } else {
            // Init record
            $db->prepare("INSERT IGNORE INTO user_points (user_id,points,level,total_spent) VALUES (?,0,'bronze',0)")->execute([$uid]);
        }
        $ph = $db->prepare("SELECT * FROM point_transactions WHERE user_id=? ORDER BY created_at DESC LIMIT 10");
        $ph->execute([$uid]);
        $pointHistory = $ph->fetchAll();
    } catch (\Exception $e) {}
}

$levels = [
    'bronze'   => ['label'=>'Bronze',   'icon'=>'🥉','color'=>'#cd7f32','min'=>0,       'max'=>500000,  'cashback'=>0],
    'silver'   => ['label'=>'Silver',   'icon'=>'🥈','color'=>'#8892a4','min'=>500000,  'max'=>2000000, 'cashback'=>1],
    'gold'     => ['label'=>'Gold',     'icon'=>'🥇','color'=>'#f5a623','min'=>2000000, 'max'=>5000000, 'cashback'=>2],
    'platinum' => ['label'=>'Platinum', 'icon'=>'💎','color'=>'#60a5fa','min'=>5000000, 'max'=>null,    'cashback'=>3],
];

$curLevel = $levels[$userLevel] ?? $levels['bronze'];
$nextLevels = array_slice($levels, array_search($userLevel, array_keys($levels)) + 1, 1);
$nextLevel  = $nextLevels ? array_values($nextLevels)[0] : null;
$progress   = 0;
if ($nextLevel) {
    $range    = $nextLevel['min'] - $curLevel['min'];
    $progress = $range > 0 ? min(100, round((($totalSpent - $curLevel['min']) / $range) * 100)) : 100;
}

include __DIR__.'/../includes/header.php';
?>
<style>
.reward-wrap { max-width: 960px; margin: 0 auto; padding: 28px 24px; }
.reward-hero { background: linear-gradient(135deg, #0e1120, #1a0e2e, #0a0c15); border-bottom: 1px solid var(--b1); padding: 36px 0 30px; }
.reward-hero-inner { max-width: 960px; margin: 0 auto; padding: 0 24px; }
.level-card {
  background: var(--card); border: 1.5px solid var(--b1); border-radius: 16px;
  padding: 22px; margin-bottom: 20px; position: relative; overflow: hidden;
}
.level-card::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px; background: v; }
.points-big { font-size: 3rem; font-weight: 900; color: var(--gold); font-family: var(--f-display); line-height: 1; }
.level-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-bottom: 24px; }
@media(max-width: 600px) { .level-grid { grid-template-columns: repeat(2, 1fr); } }
.level-item {
  background: var(--card); border: 1.5px solid var(--b1); border-radius: 10px;
  padding: 14px; text-align: center; transition: all .2s;
}
.level-item.current { border-width: 2px; }
.sec-label { font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .8px; color: var(--t3); margin-bottom: 14px; display: flex; align-items: center; gap: 8px; }
.sec-label::after { content: ''; flex: 1; height: 1px; background: var(--b1); }
.how-item { display: flex; align-items: flex-start; gap: 12px; padding: 12px 0; border-bottom: 1px solid var(--b0); }
.how-item:last-child { border-bottom: none; }
.how-icon { width: 36px; height: 36px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 1rem; flex-shrink: 0; }
</style>

<div class="reward-hero">
  <div class="reward-hero-inner">
    <div style="display:flex;align-items:center;gap:12px;">
      <div style="font-size:2rem;">🎁</div>
      <div>
        <div style="font-family:var(--f-display);font-size:1.5rem;font-weight:800;color:var(--t1);">Reward & Poin</div>
        <div style="font-size:.82rem;color:var(--t3);margin-top:2px;">Kumpulkan poin dari setiap transaksi, tukar dengan hadiah menarik.</div>
      </div>
    </div>
  </div>
</div>

<div class="reward-wrap">

  <?php if (!isLoggedIn()): ?>
  <!-- Not logged in -->
  <div style="background:var(--card);border:1.5px solid var(--b1);border-radius:14px;padding:48px;text-align:center;">
    <div style="font-size:3rem;margin-bottom:14px;">🔐</div>
    <div style="font-size:1rem;font-weight:700;color:var(--t1);margin-bottom:6px;">Masuk untuk lihat poin kamu</div>
    <div style="font-size:.82rem;color:var(--t3);margin-bottom:20px;">Login untuk mengakses reward, level, dan riwayat poin.</div>
    <a href="<?=asset('auth/login.php')?>" class="btn-submit" style="display:inline-flex;align-items:center;gap:7px;padding:10px 24px;text-decoration:none;">
      Masuk Sekarang
    </a>
  </div>

  <?php else: ?>

  <!-- Poin & Level Card -->
  <div class="level-card" style="border-color:<?=$curLevel['color']?>44;">
    <div style="position:absolute;top:0;left:0;right:0;height:3px;background:<?=$curLevel['color']?>;"></div>
    <div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:16px;">
      <div>
        <div style="font-size:.7rem;text-transform:uppercase;letter-spacing:.7px;color:var(--t3);margin-bottom:6px;">Total Poin Kamu</div>
        <div class="points-big"><?=number_format($userPoints)?></div>
        <div style="font-size:.75rem;color:var(--t3);margin-top:4px;">= Rp <?=number_format($userPoints * 100, 0, ',', '.')?> nilai tukar</div>
      </div>
      <div style="text-align:right;">
        <div style="font-size:.7rem;text-transform:uppercase;letter-spacing:.7px;color:var(--t3);margin-bottom:6px;">Level Kamu</div>
        <div style="display:flex;align-items:center;gap:8px;justify-content:flex-end;">
          <span style="font-size:1.8rem;"><?=$curLevel['icon']?></span>
          <div>
            <div style="font-size:1.1rem;font-weight:800;color:<?=$curLevel['color']?>;"><?=$curLevel['label']?></div>
            <div style="font-size:.72rem;color:var(--t3);">Cashback <?=$curLevel['cashback']?>%</div>
          </div>
        </div>
      </div>
    </div>

    <?php if ($nextLevel): ?>
    <!-- Progress ke level berikutnya -->
    <div style="margin-top:18px;">
      <div style="display:flex;justify-content:space-between;font-size:.72rem;color:var(--t3);margin-bottom:6px;">
        <span>Progress ke <?=$nextLevel['label']?> <?=$nextLevel['icon']?></span>
        <span><?=formatRupiah($totalSpent)?> / <?=formatRupiah($nextLevel['min'])?></span>
      </div>
      <div style="height:6px;background:var(--b1);border-radius:99px;overflow:hidden;">
        <div style="height:100%;width:<?=$progress?>%;background:linear-gradient(90deg,<?=$curLevel['color']?>,<?=$nextLevel['color']?>);border-radius:99px;transition:width .5s;"></div>
      </div>
      <div style="font-size:.69rem;color:var(--t3);margin-top:4px;">
        Butuh <?=formatRupiah(max(0, $nextLevel['min'] - $totalSpent))?> lagi untuk naik level
      </div>
    </div>
    <?php else: ?>
    <div style="margin-top:14px;font-size:.78rem;color:var(--gold);font-weight:600;">🏆 Kamu sudah di level tertinggi!</div>
    <?php endif; ?>
  </div>

  <!-- Level tiers -->
  <div class="sec-label">Level Member</div>
  <div class="level-grid">
    <?php foreach ($levels as $key => $lv): $isCurrent = $key === $userLevel; ?>
    <div class="level-item" style="border-color:<?=$isCurrent?$lv['color'].'99':'var(--b1)'?>;<?=$isCurrent?'background:' . $lv['color'] . '11;':''?>">
      <div style="font-size:1.8rem;margin-bottom:6px;"><?=$lv['icon']?></div>
      <div style="font-size:.82rem;font-weight:700;color:<?=$lv['color']?>;"><?=$lv['label']?></div>
      <div style="font-size:1rem;font-weight:800;color:var(--t1);margin:3px 0;"><?=$lv['cashback']?>%</div>
      <div style="font-size:.65rem;color:var(--t3);">cashback</div>
      <div style="font-size:.66rem;color:var(--t3);margin-top:4px;">Min. <?=formatRupiah($lv['min'])?></div>
      <?php if ($isCurrent): ?>
      <div style="font-size:.62rem;color:<?=$lv['color']?>;font-weight:700;margin-top:6px;">● Level Kamu</div>
      <?php endif; ?>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- Cara dapat poin -->
  <div class="sec-label">Cara Mendapatkan Poin</div>
  <div style="background:var(--card);border:1px solid var(--b1);border-radius:12px;padding:16px 20px;margin-bottom:24px;">
    <?php
    $hows = [
      ['icon'=>'⚡','bg'=>'rgba(227,24,55,.1)','title'=>'Top Up Game','desc'=>'Setiap Rp 1.000 transaksi = 1 poin'],
      ['icon'=>'🎯','bg'=>'rgba(59,130,246,.1)','title'=>'Transaksi Pertama','desc'=>'Bonus 50 poin untuk transaksi pertama'],
      ['icon'=>'👥','bg'=>'rgba(16,185,129,.1)','title'=>'Referral Teman','desc'=>'Bonus 100 poin saat teman mendaftar dengan kode referal kamu'],
      ['icon'=>'🎂','bg'=>'rgba(245,158,11,.1)','title'=>'Bonus Ulang Tahun','desc'=>'Bonus 200 poin di hari ulang tahunmu'],
    ];
    foreach ($hows as $h):
    ?>
    <div class="how-item">
      <div class="how-icon" style="background:<?=$h['bg']?>;"><?=$h['icon']?></div>
      <div>
        <div style="font-size:.86rem;font-weight:600;color:var(--t1);"><?=$h['title']?></div>
        <div style="font-size:.74rem;color:var(--t3);margin-top:2px;"><?=$h['desc']?></div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- Riwayat poin -->
  <?php if (!empty($pointHistory)): ?>
  <div class="sec-label">Riwayat Poin</div>
  <div style="background:var(--card);border:1px solid var(--b1);border-radius:12px;overflow:hidden;">
    <?php foreach ($pointHistory as $ph): ?>
    <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 16px;border-bottom:1px solid var(--b0);">
      <div>
        <div style="font-size:.82rem;font-weight:500;color:var(--t1);"><?=htmlspecialchars($ph['description'] ?? 'Transaksi')?></div>
        <div style="font-size:.7rem;color:var(--t3);margin-top:2px;"><?=date('d M Y H:i', strtotime($ph['created_at']))?></div>
      </div>
      <div style="font-size:.92rem;font-weight:700;color:<?=$ph['points'] >= 0 ? '#34d399' : '#f87171'?>;">
        <?=$ph['points'] >= 0 ? '+' : ''?><?=number_format($ph['points'])?> poin
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php else: ?>
  <div class="sec-label">Riwayat Poin</div>
  <div style="background:var(--card);border:1px solid var(--b1);border-radius:12px;padding:36px;text-align:center;color:var(--t3);">
    <div style="font-size:2rem;margin-bottom:8px;">📊</div>
    Belum ada riwayat poin. Mulai top up untuk kumpulkan poin!
  </div>
  <?php endif; ?>
  <?php endif; ?>

  <!-- CTA -->
  <div style="margin-top:24px;background:linear-gradient(135deg,rgba(245,166,35,.08),rgba(245,166,35,.02));border:1px solid rgba(245,166,35,.2);border-radius:12px;padding:20px 24px;display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;">
    <div>
      <div style="font-family:var(--f-display);font-size:.95rem;font-weight:800;color:var(--t1);">⚡ Mulai Kumpulkan Poin</div>
      <div style="font-size:.76rem;color:var(--t3);margin-top:2px;">Setiap transaksi = poin. Poin = cashback & hadiah.</div>
    </div>
    <a href="<?=asset('index.php')?>" class="btn-submit" style="padding:9px 20px;font-size:.84rem;text-decoration:none;display:inline-flex;align-items:center;gap:6px;">
      Top Up Sekarang →
    </a>
  </div>

</div>
<?php include __DIR__.'/../includes/footer.php'; ?>