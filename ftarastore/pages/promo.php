<?php
require_once __DIR__.'/../includes/functions.php';
Security::boot();
$pageTitle = 'Promo & Diskon — '.siteName();
$db = db();

// Ambil voucher aktif sebagai promo
try {
    $promos = $db->query("
        SELECT v.*, 
               CASE WHEN v.expires_at IS NULL THEN NULL 
                    ELSE DATEDIFF(v.expires_at, NOW()) END AS days_left
        FROM vouchers v
        WHERE v.is_active = 1
          AND (v.expires_at IS NULL OR v.expires_at > NOW())
          AND (v.max_uses IS NULL OR v.used_count < v.max_uses)
        ORDER BY v.created_at DESC
    ")->fetchAll();
} catch (\Exception $e) {
    $promos = [];
}

include __DIR__.'/../includes/header.php';
?>
<style>
.promo-hero {
  background: linear-gradient(135deg, #0e1120 0%, #1a0e2e 50%, #0a0c15 100%);
  border-bottom: 1px solid var(--b1);
  padding: 36px 0 30px;
}
.promo-hero-inner { max-width: 1200px; margin: 0 auto; padding: 0 24px; }
.promo-wrap { max-width: 1200px; margin: 0 auto; padding: 28px 24px; }
.promo-tabs { display: flex; gap: 8px; margin-bottom: 24px; flex-wrap: wrap; }
.promo-tab {
  padding: 8px 20px; border-radius: 20px; font-size: .82rem; font-weight: 600;
  border: 1.5px solid var(--b2); color: var(--t2); cursor: pointer;
  background: var(--card); transition: all .18s; text-decoration: none;
}
.promo-tab.on, .promo-tab:hover {
  background: var(--red); color: #fff; border-color: var(--red);
  box-shadow: 0 3px 12px rgba(227,24,55,.3);
}
.promo-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 32px; }
@media(max-width: 768px) { .promo-grid { grid-template-columns: repeat(2, 1fr); } }
@media(max-width: 480px) { .promo-grid { grid-template-columns: 1fr; } }

.promo-card {
  background: var(--card);
  border: 1.5px solid var(--b1);
  border-radius: 14px;
  overflow: hidden;
  transition: all .2s;
  position: relative;
}
.promo-card:hover {
  border-color: rgba(227,24,55,.35);
  transform: translateY(-3px);
  box-shadow: 0 8px 28px rgba(0,0,0,.4);
}
.promo-card-badge {
  position: absolute; top: 12px; right: 12px;
  font-size: .62rem; font-weight: 700; padding: 3px 9px; border-radius: 20px;
}
.promo-card-body { padding: 18px; }
.promo-card-type { font-size: .63rem; font-weight: 700; text-transform: uppercase; letter-spacing: .6px; color: var(--red); margin-bottom: 6px; }
.promo-card-title { font-size: 1rem; font-weight: 800; color: var(--t1); margin-bottom: 6px; line-height: 1.3; }
.promo-card-desc { font-size: .77rem; color: var(--t3); line-height: 1.6; margin-bottom: 14px; }
.promo-code-wrap {
  display: flex; align-items: center; justify-content: space-between;
  background: var(--card2); border: 1.5px dashed var(--b2);
  border-radius: 8px; padding: 8px 12px; margin-bottom: 12px;
}
.promo-code { font-family: 'Courier New', monospace; font-size: .9rem; font-weight: 800; color: var(--red); letter-spacing: 1px; }
.promo-copy-btn {
  font-size: .7rem; font-weight: 700; color: var(--t2); cursor: pointer;
  background: none; border: none; padding: 4px 8px; border-radius: 4px;
  transition: all .15s;
}
.promo-copy-btn:hover { background: var(--b1); color: var(--t1); }
.promo-meta { display: flex; align-items: center; justify-content: space-between; font-size: .7rem; color: var(--t3); }

/* Event cards */
.event-card {
  background: var(--card);
  border: 1.5px solid var(--b1);
  border-radius: 14px;
  padding: 20px;
  display: flex;
  gap: 16px;
  align-items: flex-start;
  transition: all .2s;
}
.event-card:hover { border-color: rgba(227,24,55,.3); }
.event-icon { width: 52px; height: 52px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; flex-shrink: 0; }
.sec-label { font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .8px; color: var(--t3); margin-bottom: 14px; display: flex; align-items: center; gap: 8px; }
.sec-label::after { content: ''; flex: 1; height: 1px; background: var(--b1); }
</style>

<!-- Hero -->
<div class="promo-hero">
  <div class="promo-hero-inner">
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:8px;">
      <div style="width:40px;height:40px;border-radius:10px;background:rgba(227,24,55,.1);display:flex;align-items:center;justify-content:center;font-size:1.2rem;">🎁</div>
      <div>
        <div style="font-family:var(--f-display);font-size:1.5rem;font-weight:800;color:var(--t1);">Promo & Diskon</div>
        <div style="font-size:.82rem;color:var(--t3);margin-top:2px;">Dapatkan penawaran terbaik untuk top up game favoritmu.</div>
      </div>
    </div>
    <!-- Countdown promo terdekat -->
    <div style="display:inline-flex;align-items:center;gap:8px;background:rgba(227,24,55,.08);border:1px solid rgba(227,24,55,.2);border-radius:8px;padding:8px 14px;margin-top:12px;font-size:.78rem;">
      <svg width="13" height="13" fill="none" stroke="#e31837" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
      <span style="color:var(--red);font-weight:700;">Flash Sale</span>
      <span style="color:var(--t3);">Diskon up to 15% — Setiap Sabtu & Minggu</span>
    </div>
  </div>
</div>

<div class="promo-wrap">

  <!-- Tabs filter -->
  <div class="promo-tabs">
    <a href="#semua" class="promo-tab on" onclick="filterPromo('all',this)">🎯 Semua</a>
    <a href="#diskon" class="promo-tab" onclick="filterPromo('diskon',this)">💸 Diskon</a>
    <a href="#cashback" class="promo-tab" onclick="filterPromo('cashback',this)">💰 Cashback</a>
    <a href="#event" class="promo-tab" onclick="filterPromo('event',this)">🎮 Event Game</a>
  </div>

  <!-- Voucher aktif dari DB -->
  <?php if (!empty($promos)): ?>
  <div class="sec-label">Voucher Aktif</div>
  <div class="promo-grid" id="promo-grid">
    <?php foreach ($promos as $v):
      $typeLabel = $v['discount_type'] === 'percent' ? 'DISKON' : 'POTONGAN';
      $typeVal   = $v['discount_type'] === 'percent'
        ? $v['discount_value'] . '%'
        : 'Rp ' . number_format($v['discount_value'], 0, ',', '.');
      $category  = $v['discount_type'] === 'percent' ? 'diskon' : 'cashback';
    ?>
    <div class="promo-card" data-cat="<?=$category?>">
      <div class="promo-card-badge" style="background:rgba(227,24,55,.1);color:var(--red);border:1px solid rgba(227,24,55,.2);">
        <?=$typeLabel?> <?=$typeVal?>
      </div>
      <div style="background:linear-gradient(135deg,rgba(227,24,55,.08),rgba(227,24,55,.02));padding:20px 18px 14px;border-bottom:1px solid var(--b1);">
        <div style="font-size:2rem;font-weight:900;color:var(--red);font-family:var(--f-display);"><?=$typeVal?></div>
        <div style="font-size:.72rem;color:var(--t3);margin-top:2px;"><?=$typeLabel?> untuk semua transaksi</div>
      </div>
      <div class="promo-card-body">
        <div class="promo-card-type"><?=$typeLabel?></div>
        <div class="promo-card-title"><?=htmlspecialchars($v['code'])?></div>
        <?php if ($v['min_purchase'] > 0): ?>
        <div class="promo-card-desc">Min. transaksi <?=formatRupiah($v['min_purchase'])?>
          <?=$v['max_discount'] ? ' · Maks. diskon ' . formatRupiah($v['max_discount']) : ''?>
        </div>
        <?php endif; ?>
        <div class="promo-code-wrap">
          <span class="promo-code"><?=htmlspecialchars($v['code'])?></span>
          <button class="promo-copy-btn" onclick="copyCode('<?=htmlspecialchars($v['code'])?>',this)">Salin</button>
        </div>
        <div class="promo-meta">
          <?php if ($v['days_left'] !== null): ?>
          <span>⏰ <?=$v['days_left'] > 0 ? $v['days_left'] . ' hari lagi' : 'Berakhir hari ini'?></span>
          <?php else: ?>
          <span>♾️ Tidak ada batas waktu</span>
          <?php endif; ?>
          <?php if ($v['max_uses']): ?>
          <span><?=number_format($v['max_uses'] - $v['used_count'])?> tersisa</span>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <!-- Event Game section -->
  <div class="sec-label" id="event">Event Game Aktif</div>
  <div style="display:flex;flex-direction:column;gap:10px;margin-bottom:32px;" data-cat="event">
    <?php
    $events = [
      ['icon'=>'⚔️','color'=>'rgba(56,189,248,.1)','game'=>'Mobile Legends','title'=>'Double Diamond Event','desc'=>'Beli diamond ML selama weekend, dapat bonus 10% extra diamond!','period'=>'Setiap Sabtu-Minggu','status'=>'live'],
      ['icon'=>'🔥','color'=>'rgba(251,146,60,.1)','game'=>'Free Fire','title'=>'Top Up Hari Kemerdekaan','desc'=>'Spesial HUT RI, top up FF dapat bonus diamond dan skin eksklusif.','period'=>'17–31 Agustus','status'=>'upcoming'],
      ['icon'=>'💎','color'=>'rgba(167,139,250,.1)','game'=>'Genshin Impact','title'=>'Blessing of the Welkin Moon','desc'=>'Dapatkan harga terbaik untuk top up Genesis Crystal.','period'=>'Berlaku terus','status'=>'live'],
    ];
    foreach ($events as $e):
    ?>
    <div class="event-card">
      <div class="event-icon" style="background:<?=$e['color']?>;"><?=$e['icon']?></div>
      <div style="flex:1;">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:4px;flex-wrap:wrap;">
          <span style="font-size:.92rem;font-weight:700;color:var(--t1);"><?=$e['title']?></span>
          <span style="font-size:.64rem;font-weight:700;padding:2px 8px;border-radius:20px;<?=$e['status']==='live'?'background:rgba(16,185,129,.1);color:#34d399;border:1px solid rgba(16,185,129,.2);':'background:rgba(59,130,246,.1);color:#60a5fa;border:1px solid rgba(59,130,246,.2);'?>">
            <?=$e['status']==='live'?'● Live':'⏳ Segera'?>
          </span>
        </div>
        <div style="font-size:.72rem;color:var(--red);font-weight:600;margin-bottom:4px;"><?=$e['game']?></div>
        <div style="font-size:.78rem;color:var(--t3);line-height:1.5;"><?=$e['desc']?></div>
        <div style="font-size:.69rem;color:var(--t3);margin-top:6px;">📅 <?=$e['period']?></div>
      </div>
      <a href="<?=asset('index.php')?>" style="white-space:nowrap;background:var(--red);color:#fff;font-size:.75rem;font-weight:700;padding:8px 14px;border-radius:7px;text-decoration:none;display:flex;align-items:center;gap:5px;flex-shrink:0;">
        Top Up →
      </a>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- Cashback Info -->
  <div class="sec-label">Cashback & Reward</div>
  <div style="background:linear-gradient(135deg,rgba(245,166,35,.08),rgba(245,166,35,.02));border:1px solid rgba(245,166,35,.2);border-radius:14px;padding:24px;margin-bottom:32px;">
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;flex-wrap:wrap;">
      <?php
      $cashbacks = [
        ['level'=>'Bronze','icon'=>'🥉','min'=>0,'max'=>500000,'cashback'=>'0%','color'=>'#cd7f32'],
        ['level'=>'Silver','icon'=>'🥈','min'=>500000,'max'=>2000000,'cashback'=>'1%','color'=>'#8892a4'],
        ['level'=>'Gold','icon'=>'🥇','min'=>2000000,'max'=>5000000,'cashback'=>'2%','color'=>'#f5a623'],
        ['level'=>'Platinum','icon'=>'💎','min'=>5000000,'max'=>null,'cashback'=>'3%','color'=>'#60a5fa'],
      ];
      foreach (array_slice($cashbacks, 0, 4) as $cb):
      ?>
      <div style="background:var(--card);border:1px solid var(--b1);border-radius:10px;padding:14px;text-align:center;border-top:2px solid <?=$cb['color']?>;">
        <div style="font-size:1.5rem;margin-bottom:6px;"><?=$cb['icon']?></div>
        <div style="font-size:.82rem;font-weight:700;color:<?=$cb['color']?>;"><?=$cb['level']?></div>
        <div style="font-size:1.3rem;font-weight:800;color:var(--t1);margin:4px 0;"><?=$cb['cashback']?></div>
        <div style="font-size:.68rem;color:var(--t3);">
          Min. <?=formatRupiah($cb['min'])?>
          <?=$cb['max'] ? ' s/d ' . formatRupiah($cb['max']) : '+'?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <div style="margin-top:14px;font-size:.75rem;color:var(--t3);text-align:center;">
      💡 Cashback otomatis ditambah ke poin setiap transaksi sukses. Poin bisa ditukar di halaman <a href="<?=asset('pages/reward.php')?>" style="color:var(--gold);">Reward</a>.
    </div>
  </div>

  <!-- CTA -->
  <div style="background:linear-gradient(135deg,rgba(227,24,55,.1),rgba(227,24,55,.04));border:1px solid rgba(227,24,55,.18);border-radius:12px;padding:20px 24px;display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;">
    <div>
      <div style="font-family:var(--f-display);font-size:1rem;font-weight:800;color:var(--t1);margin-bottom:3px;">⚡ Mulai Top Up Sekarang</div>
      <div style="font-size:.78rem;color:var(--t3);">Proses instan 1–3 detik. Harga terbaik dijamin.</div>
    </div>
    <a href="<?=asset('index.php')?>" class="btn-submit" style="padding:10px 22px;font-size:.86rem;text-decoration:none;display:inline-flex;align-items:center;gap:7px;">
      <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
      Top Up Sekarang
    </a>
  </div>

</div>

<script>
function copyCode(code, btn) {
  navigator.clipboard.writeText(code).then(() => {
    var orig = btn.textContent;
    btn.textContent = '✅ Disalin!';
    btn.style.color = '#34d399';
    setTimeout(() => { btn.textContent = orig; btn.style.color = ''; }, 2000);
  });
}

function filterPromo(cat, tabEl) {
  // Update tabs
  document.querySelectorAll('.promo-tab').forEach(t => t.classList.remove('on'));
  tabEl.classList.add('on');

  // Filter cards
  document.querySelectorAll('.promo-card').forEach(card => {
    if (cat === 'all' || card.dataset.cat === cat) {
      card.style.display = '';
    } else {
      card.style.display = 'none';
    }
  });
  return false;
}
</script>

<?php include __DIR__.'/../includes/footer.php'; ?>