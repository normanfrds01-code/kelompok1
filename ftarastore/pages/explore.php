<?php
require_once __DIR__.'/../includes/functions.php';
Security::boot();
$pageTitle = 'Explore — '.siteName();
$db = db();

try {
    $articles    = $db->query("SELECT * FROM explore_articles WHERE is_active=1 ORDER BY sort_order ASC, created_at DESC LIMIT 12")->fetchAll();
    $tournaments = $db->query("SELECT * FROM explore_tournaments WHERE is_active=1 ORDER BY sort_order ASC LIMIT 6")->fetchAll();
} catch (\Exception $e) {
    $articles = []; $tournaments = [];
}

$cats = [
    'artikel'   =>['label'=>'Artikel',      'emoji'=>'📰','color'=>'rgba(227,24,55,.6)','bg'=>'rgba(227,24,55,.1)','badge'=>'var(--red)','tag'=>'Baru','desc'=>'Berita & review game terbaru'],
    'tips'      =>['label'=>'Tips & Trik',  'emoji'=>'💡','color'=>'rgba(245,166,35,.6)','bg'=>'rgba(245,166,35,.1)','badge'=>'var(--gold)','tag'=>'Hot','desc'=>'Panduan & strategi bermain'],
    'turnamen'  =>['label'=>'Turnamen',     'emoji'=>'🏆','color'=>'rgba(56,189,248,.6)','bg'=>'rgba(56,189,248,.1)','badge'=>'#38bdf8','tag'=>'Live','desc'=>'Jadwal & hasil kompetisi'],
    'komunitas' =>['label'=>'Komunitas',    'emoji'=>'👥','color'=>'rgba(45,212,191,.6)','bg'=>'rgba(45,212,191,.1)','badge'=>'#2dd4bf','tag'=>'Join','desc'=>'Forum & diskusi player'],
    'livescore' =>['label'=>'Livescore',    'emoji'=>'📊','color'=>'rgba(167,139,250,.6)','bg'=>'rgba(167,139,250,.1)','badge'=>'#a78bfa','tag'=>'Segera','desc'=>'Skor pertandingan real-time'],
    'review'    =>['label'=>'Review Game',  'emoji'=>'⭐','color'=>'rgba(251,146,60,.6)','bg'=>'rgba(251,146,60,.1)','badge'=>'#fb923c','tag'=>'Pilihan','desc'=>'Ulasan game terpopuler'],
    'update'    =>['label'=>'Update Game',  'emoji'=>'🔔','color'=>'rgba(34,211,160,.6)','bg'=>'rgba(34,211,160,.1)','badge'=>'#22d3a0','tag'=>'Update','desc'=>'Patch notes & update terbaru'],
    'promo'     =>['label'=>'Promo & Event','emoji'=>'🎁','color'=>'rgba(227,24,55,.6)','bg'=>'rgba(227,24,55,.1)','badge'=>'var(--red)','tag'=>'Gratis','desc'=>'Event & giveaway eksklusif'],
];
$statusMap=['live'=>['#22d3a0','🔴 Live'],'upcoming'=>['#38bdf8','Segera'],'ended'=>['#64748b','Selesai']];

include __DIR__.'/../includes/header.php';
?>
<style>
.explore-hero{background:linear-gradient(135deg,#0e1120 0%,#1c1030 50%,#0a0c15 100%);border-bottom:1px solid var(--b1);padding:32px 0 28px;}
.explore-hero-inner,.explore-wrap{max-width:1200px;margin:0 auto;padding:0 24px;}
.explore-wrap{padding-top:28px;padding-bottom:28px;}
.explore-hero h1{font-family:var(--f-display);font-size:1.6rem !important;font-weight:800;margin-bottom:6px;display:flex;align-items:center;gap:10px;}
.explore-hero p{color:var(--t3);font-size:.88rem;}
.expl-sec{font-family:var(--f-display);font-size:.78rem;font-weight:700;color:var(--t3);text-transform:uppercase;letter-spacing:1.2px;margin-bottom:14px;display:flex;align-items:center;gap:8px;}
.expl-sec::after{content:'';flex:1;height:1px;background:var(--b1);}
.explore-cats{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:32px;}
.ec{background:var(--card);border:1.5px solid var(--b1);border-radius:14px;padding:20px 16px;display:flex;flex-direction:column;align-items:center;gap:10px;text-decoration:none;transition:all .25s cubic-bezier(.34,1.56,.64,1);}
.ec:hover{transform:translateY(-4px);border-color:var(--ec-accent);box-shadow:0 8px 28px rgba(0,0,0,.4),0 0 0 1px var(--ec-accent);}
.ec-icon{width:54px;height:54px;border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:1.5rem;}
.ec-name{font-family:var(--f-display);font-size:.86rem;font-weight:700;color:var(--t1);text-align:center;}
.ec-desc{font-size:.7rem;color:var(--t3);text-align:center;line-height:1.5;}
.ec-badge{font-size:.62rem;font-weight:700;padding:2px 8px;border-radius:20px;}
.articles-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:32px;}
.ac{background:var(--card);border:1.5px solid var(--b1);border-radius:12px;overflow:hidden;text-decoration:none;transition:all .2s;display:flex;flex-direction:column;}
.ac:hover{border-color:rgba(227,24,55,.4);transform:translateY(-3px);box-shadow:0 8px 24px rgba(0,0,0,.4);}
.ac-img{width:100%;height:130px;background:linear-gradient(135deg,var(--card2),var(--card3));display:flex;align-items:center;justify-content:center;font-size:2.2rem;}
.ac-body{padding:13px 15px;flex:1;display:flex;flex-direction:column;gap:5px;}
.ac-tag{font-size:.63rem;font-weight:700;color:var(--red);text-transform:uppercase;letter-spacing:.5px;}
.ac-title{font-size:.86rem;font-weight:700;color:var(--t1);line-height:1.4;}
.ac-meta{font-size:.69rem;color:var(--t3);margin-top:auto;}
.tourn-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:32px;}
@media(max-width:768px){.explore-cats,.articles-grid,.tourn-grid{grid-template-columns:repeat(2,1fr);}}
@media(max-width:480px){.explore-cats,.articles-grid,.tourn-grid{grid-template-columns:1fr;}}
</style>

<div class="explore-hero">
  <div class="explore-hero-inner">
    <h1>
      <svg width="26" height="26" fill="none" stroke="var(--red)" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
      Explore
    </h1>
    <p>Temukan artikel, tips, turnamen, dan komunitas gaming terbaru</p>
  </div>
</div>

<div class="explore-wrap">
  <div class="expl-sec">Kategori</div>
  <div class="explore-cats">
    <?php foreach ($cats as $key => $c): ?>
    <a href="#<?=$key?>" class="ec" style="--ec-accent:<?=$c['color']?>;">
      <div class="ec-icon" style="background:<?=$c['bg']?>;"><?=$c['emoji']?></div>
      <div class="ec-name"><?=$c['label']?></div>
      <div class="ec-desc"><?=$c['desc']?></div>
      <span class="ec-badge" style="background:<?=$c['bg']?>;color:<?=$c['badge']?>;"><?=$c['tag']?></span>
    </a>
    <?php endforeach; ?>
  </div>

  <div class="expl-sec" id="artikel">Artikel Terbaru</div>
  <?php if (!empty($articles)): ?>
  <div class="articles-grid">
    <?php foreach ($articles as $art):
      $ci = $cats[$art['category']] ?? ['label'=>$art['category'],'emoji'=>'📄'];
      $link = !empty($art['url']) ? htmlspecialchars($art['url']) : '#';
      $target = !empty($art['url']) ? ' target="_blank" rel="noopener"' : '';
    ?>
    <a href="<?=$link?>"<?=$target?> class="ac">
      <div class="ac-img"><?=htmlspecialchars($art['emoji'])?></div>
      <div class="ac-body">
        <span class="ac-tag"><?=htmlspecialchars($art['game_name'] ?: $ci['label'])?></span>
        <div class="ac-title"><?=htmlspecialchars($art['title'])?></div>
        <?php if ($art['summary']): ?>
        <div style="font-size:.73rem;color:var(--t3);line-height:1.5;overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;"><?=htmlspecialchars($art['summary'])?></div>
        <?php endif; ?>
        <div class="ac-meta">
          <?=$art['published_at'] ? date('d M Y',strtotime($art['published_at'])) : ''?>
          <?=$art['read_time'] ? ' · '.$art['read_time'].' mnt baca' : ''?>
        </div>
      </div>
    </a>
    <?php endforeach; ?>
  </div>
  <?php else: ?>
  <div style="background:var(--card);border:1px solid var(--b1);border-radius:12px;padding:40px;text-align:center;color:var(--t3);margin-bottom:32px;font-size:.84rem;">
    📰 Belum ada artikel. Tambahkan melalui panel admin.
  </div>
  <?php endif; ?>

  <?php if (!empty($tournaments)): ?>
  <div class="expl-sec" id="turnamen">Turnamen</div>
  <div class="tourn-grid">
    <?php foreach ($tournaments as $t):
      $sc = $statusMap[$t['status']] ?? ['#64748b','—'];
    ?>
    <div style="background:var(--card);border:1.5px solid var(--b1);border-radius:12px;padding:16px;display:flex;flex-direction:column;gap:10px;">
      <div style="display:flex;align-items:center;justify-content:space-between;">
        <span style="font-size:1.3rem;"><?=htmlspecialchars($t['emoji'])?></span>
        <span style="font-size:.64rem;font-weight:700;padding:2px 8px;border-radius:20px;background:<?=$sc[0]?>22;color:<?=$sc[0]?>;"><?=$sc[1]?></span>
      </div>
      <div>
        <div style="font-weight:700;font-size:.88rem;color:var(--t1);"><?=htmlspecialchars($t['name'])?></div>
        <div style="font-size:.71rem;color:var(--t3);margin-top:2px;"><?=htmlspecialchars($t['game'])?></div>
      </div>
      <div style="display:flex;justify-content:space-between;padding-top:8px;border-top:1px solid var(--b0);">
        <span style="font-size:.71rem;color:var(--t3);">📅 <?=htmlspecialchars($t['date_range']??'—')?></span>
        <?php if($t['prize']): ?><span style="font-size:.74rem;font-weight:700;color:var(--gold);">🏆 <?=htmlspecialchars($t['prize'])?></span><?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <div style="background:linear-gradient(135deg,rgba(227,24,55,.12),rgba(184,19,45,.06));border:1px solid rgba(227,24,55,.2);border-radius:14px;padding:24px 28px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px;">
    <div>
      <div style="font-family:var(--f-display);font-size:1.05rem;font-weight:800;color:var(--t1);margin-bottom:4px;">⚡ Top Up Kilat</div>
      <div style="font-size:.82rem;color:var(--t3);">Proses otomatis 1–3 detik. Harga terbaik, aman & terpercaya.</div>
    </div>
    <a href="<?=asset('index.php')?>" style="background:linear-gradient(135deg,#d40000,#aa0000);color:#fff;font-weight:700;font-size:.88rem;padding:10px 24px;border-radius:8px;text-decoration:none;display:inline-flex;align-items:center;gap:7px;white-space:nowrap;box-shadow:0 4px 14px rgba(212,0,0,.35);">
      <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
      Top Up Sekarang
    </a>
  </div>
</div>

<?php include __DIR__.'/../includes/footer.php'; ?>