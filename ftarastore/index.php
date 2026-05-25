<?php
require_once __DIR__.'/includes/functions.php';
$pageTitle = siteName().' — Top Up Game Murah, Cepat & Terpercaya';
$banners   = getBanners();
$popular   = getPopularGames();
$cats      = getCategories();
$games     = getAllGames();

// Semua glow & badge seragam biru
$gameAccents = [
  'mobile-legends' => ['glow'=>'rgba(227,24,55,.22)', 'badge'=>'#d40000', 'icon'=>'⚔️'],
  'free-fire'      => ['glow'=>'rgba(227,24,55,.22)', 'badge'=>'#d40000', 'icon'=>'🔥'],
  'pubg-mobile'    => ['glow'=>'rgba(227,24,55,.22)', 'badge'=>'#d40000', 'icon'=>'🎯'],
  'genshin-impact' => ['glow'=>'rgba(227,24,55,.22)', 'badge'=>'#d40000', 'icon'=>'🌙'],
  'valorant'       => ['glow'=>'rgba(227,24,55,.22)', 'badge'=>'#d40000', 'icon'=>'🎮'],
  'call-of-duty'   => ['glow'=>'rgba(227,24,55,.22)', 'badge'=>'#d40000', 'icon'=>'💣'],
  'roblox'         => ['glow'=>'rgba(227,24,55,.22)', 'badge'=>'#d40000', 'icon'=>'🧱'],
  'magic-chess'    => ['glow'=>'rgba(227,24,55,.22)', 'badge'=>'#d40000', 'icon'=>'♟️'],
  'honkai-sr'      => ['glow'=>'rgba(227,24,55,.22)', 'badge'=>'#d40000', 'icon'=>'🌟'],
  'clash-of-clans' => ['glow'=>'rgba(227,24,55,.22)', 'badge'=>'#d40000', 'icon'=>'🏰'],
  'aov'            => ['glow'=>'rgba(227,24,55,.22)', 'badge'=>'#d40000', 'icon'=>'🛡️'],
  'blood-strike'   => ['glow'=>'rgba(227,24,55,.22)', 'badge'=>'#d40000', 'icon'=>''],
];
$defaultAccent = ['glow'=>'rgba(212,0,0,.2)', 'badge'=>'#d40000', 'icon'=>'🎮'];

$demoGames = [
  ['name'=>'Mobile Legends','slug'=>'mobile-legends'],
  ['name'=>'Free Fire',     'slug'=>'free-fire'],
  ['name'=>'PUBG Mobile',   'slug'=>'pubg-mobile'],
  ['name'=>'Genshin Impact','slug'=>'genshin-impact'],
  ['name'=>'Valorant',      'slug'=>'valorant'],
  ['name'=>'COD Mobile',    'slug'=>'call-of-duty'],
  ['name'=>'Roblox',        'slug'=>'roblox'],
  ['name'=>'Magic Chess',   'slug'=>'magic-chess'],
  ['name'=>'Honkai: SR',    'slug'=>'honkai-sr'],
  ['name'=>'Clash of Clans','slug'=>'clash-of-clans'],
  ['name'=>'Arena of Valor','slug'=>'aov'],
  ['name'=>'Blood Strike',  'slug'=>'blood-strike'],
];
include 'includes/header.php';
?>

<style>
/* ── Popular card — uniform dark, accent hanya di badge & glow ── */
/* ── POPULAR CARDS — UniPin style ── */
.pop-grid {
  display: grid !important;
  grid-template-columns: repeat(auto-fill, minmax(155px, 1fr)) !important;
  gap: 14px !important;
}
.pop-card-v2 {
  position: relative;
  display: block;
  border-radius: 14px;
  overflow: hidden;
  text-decoration: none;
  aspect-ratio: 3/4;
  background: var(--card2);
  border: 1.5px solid var(--b1);
  transition: transform .3s ease, box-shadow .3s ease, border-color .3s ease;
}
.pop-card-v2:hover {
  transform: translateY(-4px);
  border-color: rgba(212,0,0,.45);
  box-shadow: 0 0 0 1px rgba(212,0,0,.2), 0 8px 28px rgba(0,0,0,.4), 0 0 20px rgba(212,0,0,.15);
}
/* Full image */
.pop-icon-wrap {
  position: absolute;
  inset: 0;
  width: 100%; height: 100%;
}
.pop-icon-wrap img {
  width: 100%; height: 100%;
  object-fit: cover;
  display: block;
  transition: transform .4s ease;
}
.pop-card-v2:hover .pop-icon-wrap img { transform: scale(1.06); }
.pop-icon-wrap span {
  font-size: 2.5rem;
  display: flex; align-items: center; justify-content: center;
  width: 100%; height: 100%;
  background: var(--card2);
}
/* Gradient overlay bottom */
.pop-overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(to top, rgba(5,8,20,.95) 0%, rgba(5,8,20,.6) 35%, transparent 65%);
  z-index: 2;
  transition: opacity .3s;
}
/* Name */
.pop-meta {
  position: absolute;
  bottom: 0; left: 0; right: 0;
  z-index: 3;
  padding: 10px 12px 44px;
}
.pop-name-v2 {
  font-weight: 700;
  font-size: .82rem;
  color: #ffffff;
  text-shadow: 0 1px 4px rgba(0,0,0,.8);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  line-height: 1.2;
}
.pop-badge {
  display: inline-block;
  margin-top: 4px;
  font-size: .65rem;
  font-weight: 500;
  padding: 2px 7px;
  border-radius: 99px;
  background: rgba(255,255,255,.12);
  color: rgba(255,255,255,.7);
  border: 1px solid rgba(255,255,255,.12);
  backdrop-filter: blur(4px);
}
/* Top Up button */
.pop-topup-btn {
  position: absolute;
  bottom: 10px; left: 10px; right: 10px;
  z-index: 4;
  background: rgba(212,0,0,.15);
  border: 1.5px solid rgba(212,0,0,.5);
  border-radius: 8px;
  padding: 7px 0;
  text-align: center;
  font-size: .75rem;
  font-weight: 700;
  color: #d40000;
  letter-spacing: .4px;
  backdrop-filter: blur(4px);
  transition: background .2s, border-color .2s;
}
.pop-card-v2:hover .pop-topup-btn {
  background: rgba(212,0,0,.25);
  border-color: #d40000;
  color: #fff;
}
/* Arrow removed — replaced by card layout */
.pop-arrow-v2 { display: none; }
/* Rank badge */
.pop-rank {
  position: absolute;
  top: 8px; left: 8px;
  width: 22px; height: 22px;
  border-radius: 6px;
  background: rgba(0,0,0,.6);
  border: 1px solid rgba(255,255,255,.15);
  font-size: .65rem;
  font-weight: 800;
  color: rgba(255,255,255,.5);
  display: flex; align-items: center; justify-content: center;
  z-index: 5;
  backdrop-filter: blur(4px);
}
.pop-rank.top1 { background: rgba(255,140,0,.25); border-color: #ff8c00; color: #ff8c00; }
.pop-rank.top2 { background: rgba(148,163,184,.15); border-color: #8892a4; color: #8892a4; }
.pop-rank.top3 { background: rgba(180,100,50,.2); border-color: #cd7f32; color: #cd7f32; }

/* Hidden game cards collapse in grid */
.game-card[style*="display: none"],
.game-card[style*="display:none"] {
  display: none !important;
}
/* Active category tab */
.cat-tab.on {
  background: var(--red) !important;
  border-color: var(--red) !important;
  color: #fff !important;
  pointer-events: auto !important;
}
.cat-tab {
  pointer-events: auto !important;
  cursor: pointer !important;
}
</style>

<!-- ── HERO ── -->
<style>
.banner-slider { position: relative; overflow: hidden; }
/* Cahaya biru bergerak di atas banner */
.banner-slider::before {
  content: '';
  position: absolute;
  inset: 0;
  background:
    radial-gradient(ellipse 60% 40% at 20% 50%, rgba(227,24,55,.18) 0%, transparent 70%),
    radial-gradient(ellipse 40% 60% at 80% 30%, rgba(99,102,241,.12) 0%, transparent 65%);
  pointer-events: none;
  z-index: 2;
  animation: bannerGlow 6s ease-in-out infinite alternate;
}
/* Garis biru bawah banner */
.banner-slider::after {
  content: '';
  position: absolute;
  bottom: 0; left: 0; right: 0;
  height: 3px;
  background: linear-gradient(90deg, transparent 0%, rgba(227,24,55,.7) 30%, rgba(99,102,241,.9) 50%, rgba(227,24,55,.7) 70%, transparent 100%);
  background-size: 200% 100%;
  z-index: 3;
  animation: bannerLine 3s linear infinite;
}
@keyframes bannerGlow { 0%{opacity:.7;transform:scale(1)} 100%{opacity:1;transform:scale(1.02)} }
@keyframes bannerLine { 0%{background-position:200% 0} 100%{background-position:-200% 0} }
/* Lens flare biru pojok */
.banner-lens {
  position: absolute;
  bottom: 24px; left: 48px;
  width: 110px; height: 110px;
  border-radius: 50%;
  background: radial-gradient(circle, rgba(227,24,55,.28) 0%, transparent 70%);
  pointer-events: none;
  z-index: 2;
  animation: lensFloat 4s ease-in-out infinite;
}
.banner-lens-tr {
  position: absolute;
  top: 20px; right: 60px;
  width: 80px; height: 80px;
  border-radius: 50%;
  background: radial-gradient(circle, rgba(99,102,241,.2) 0%, transparent 70%);
  pointer-events: none;
  z-index: 2;
  animation: lensFloat 5s ease-in-out infinite reverse;
}
@keyframes lensFloat {
  0%,100% { transform: translateY(0) scale(1);     opacity: .6; }
  50%      { transform: translateY(-12px) scale(1.1); opacity: 1; }
}

/* ══ PROMO POPUP ══ */
.promo-popup-overlay {
  position: fixed; inset: 0; z-index: 99999;
  background: rgba(0,0,0,.75);
  display: flex; align-items: center; justify-content: center;
  padding: 20px;
  animation: popupFadeIn .3s ease;
  backdrop-filter: blur(4px);
}
@keyframes popupFadeIn {
  from { opacity: 0; }
  to   { opacity: 1; }
}
.promo-popup-box {
  position: relative;
  max-width: 520px; width: 100%;
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 24px 72px rgba(0,0,0,.8), 0 0 0 1px rgba(227,24,55,.3);
  animation: popupSlideUp .35s cubic-bezier(.34,1.56,.64,1);
}
@keyframes popupSlideUp {
  from { transform: translateY(40px) scale(.95); opacity: 0; }
  to   { transform: translateY(0) scale(1); opacity: 1; }
}
.promo-popup-close {
  position: absolute; top: 12px; right: 12px; z-index: 2;
  width: 36px; height: 36px; border-radius: 50%;
  background: rgba(0,0,0,.6); border: 1px solid rgba(255,255,255,.2);
  color: #fff; cursor: pointer; display: flex; align-items: center; justify-content: center;
  transition: background .2s, transform .2s;
}
.promo-popup-close:hover { background: var(--red); transform: rotate(90deg); }
.promo-popup-img {
  display: block; width: 100%; height: auto;
  max-height: 70vh; object-fit: cover;
}
.promo-popup-footer {
  background: #07080f;
  padding: 12px 16px;
  border-top: 1px solid rgba(255,255,255,.07);
}
.promo-popup-dontshow {
  display: flex; align-items: center; gap: 8px;
  font-size: .8rem; color: var(--t3); cursor: pointer;
}
.promo-popup-dontshow input { accent-color: var(--red); cursor: pointer; }
@media(max-width:480px){ .promo-popup-box { border-radius: 12px; } }

</style>

<!-- ══ PROMO POPUP ══ -->
<?php $popupBanner=null; foreach($banners as $b){ if(!empty($b['image_url'])){ $popupBanner=$b; break; } } ?>
<?php if($popupBanner): ?>
<div id="promo-popup" class="promo-popup-overlay" style="display:none;">
  <div class="promo-popup-box">
    <button class="promo-popup-close" onclick="closePopup()">
      <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M18 6 6 18M6 6l12 12"/></svg>
    </button>
    <?php if(!empty($popupBanner['link_url'])): ?><a href="<?=htmlspecialchars($popupBanner['link_url'])?>" target="_blank" onclick="closePopup()"><?php endif; ?>
    <img src="<?=htmlspecialchars($popupBanner['image_url'])?>" class="promo-popup-img" loading="eager"/>
    <?php if(!empty($popupBanner['link_url'])): ?></a><?php endif; ?>
    <div class="promo-popup-footer">
      <label class="promo-popup-dontshow">
        <input type="checkbox" id="popup-dontshow" onchange="dontShowAgain(this)"/>
        <span>Jangan tampilkan lagi hari ini</span>
      </label>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- ══ FLASH SALE ══ -->
<?php
$flashVouchers=[];
try{
  $fsq=db()->query("SELECT v.*,g.name AS game_name,g.image_url AS game_img,g.slug AS game_slug FROM vouchers v LEFT JOIN games g ON g.id=v.game_id WHERE v.is_active=1 AND v.used_count<v.quota AND (v.expires_at IS NULL OR v.expires_at>NOW()) ORDER BY v.created_at DESC LIMIT 8");
  $flashVouchers=$fsq->fetchAll();
}catch(\Exception $e){}
?>
<?php if(!empty($flashVouchers)): ?>
<section style="padding:0 0 28px;">
  <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;">
    <div style="display:flex;align-items:center;gap:10px;">
      <span style="font-size:1.4rem;">⚡</span>
      <div>
        <div style="font-size:1rem;font-weight:800;font-family:var(--f-display);color:var(--t1);">Flash Sale</div>
        <div style="font-size:.72rem;color:var(--t3);">Promo terbatas waktu!</div>
      </div>
    </div>
  </div>
  <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:12px;">
    <?php foreach($flashVouchers as $fv): ?>
    <a href="<?=asset('pages/game.php')?>?slug=<?=urlencode($fv['game_slug']??'')?>"
       style="background:var(--card);border:1.5px solid rgba(227,24,55,.2);border-radius:12px;overflow:hidden;text-decoration:none;display:block;transition:all .2s;"
       onmouseover="this.style.borderColor='rgba(227,24,55,.6)';this.style.transform='translateY(-3px)';this.style.boxShadow='0 8px 24px rgba(227,24,55,.2)'"
       onmouseout="this.style.borderColor='rgba(227,24,55,.2)';this.style.transform='';this.style.boxShadow=''">
      <div style="background:linear-gradient(135deg,#e31837,#9b0f24);padding:5px 10px;display:flex;justify-content:space-between;align-items:center;">
        <span style="font-size:.62rem;font-weight:800;color:#fff;letter-spacing:.5px;">⚡ FLASH SALE</span>
        <?php if($fv['expires_at']): ?>
        <span class="flash-timer" data-end="<?=strtotime($fv['expires_at'])?>" style="font-size:.62rem;font-weight:700;color:rgba(255,255,255,.85);font-family:monospace;">--:--:--</span>
        <?php endif; ?>
      </div>
      <?php if($fv['game_img']): ?>
      <div style="height:76px;overflow:hidden;position:relative;">
        <img src="<?=htmlspecialchars($fv['game_img'])?>" style="width:100%;height:100%;object-fit:cover;filter:brightness(.65);" onerror="this.style.display='none'"/>
        <div style="position:absolute;inset:0;background:linear-gradient(to top,rgba(0,0,0,.85),transparent 60%);"></div>
        <div style="position:absolute;bottom:5px;left:10px;font-size:.7rem;color:#fff;font-weight:600;"><?=htmlspecialchars($fv['game_name']??'')?></div>
      </div>
      <?php endif; ?>
      <div style="padding:10px 12px;">
        <div style="font-size:.8rem;font-weight:700;color:var(--t1);margin-bottom:4px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?=htmlspecialchars($fv['description']??$fv['code'])?></div>
        <div style="font-size:.92rem;font-weight:800;color:var(--gold);margin-bottom:6px;"><?=$fv['type']==='percent'?$fv['value'].'% OFF':'Rp '.number_format($fv['value'],0,',','.').' OFF'?></div>
        <div style="background:rgba(227,24,55,.1);border:1px solid rgba(227,24,55,.3);border-radius:6px;padding:3px;text-align:center;font-size:.67rem;font-weight:700;color:var(--redf);">⚡ Kuota Terbatas</div>
      </div>
    </a>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>
<div class="hero-wrap">
<?php if(!empty($banners)): ?>
  <div class="banner-slider">
    <div class="banner-lens" aria-hidden="true"></div>
    <div class="banner-lens-tr" aria-hidden="true"></div>
    <?php foreach($banners as $i=>$b): ?>
    <div class="banner-slide <?=$i===0?'active':''?>">
      <img src="<?=htmlspecialchars($b['image_url'])?>" style="width:100%;height:380px;object-fit:cover" alt="Banner"/>
    </div>
    <?php endforeach; ?>
    <button class="banner-arrow banner-arrow--prev">&#8249;</button>
    <button class="banner-arrow banner-arrow--next">&#8250;</button>
    <div class="banner-dots"><?php for($i=0;$i<count($banners);$i++): ?><button class="banner-dot <?=$i===0?'on':''?>"></button><?php endfor; ?></div>
  </div>
<?php else: ?>
  <div class="hero-default">
    <div class="hero-grid">
      <div class="hero-content">
        <div class="hero-eyebrow">
          <svg width="10" height="10" viewBox="0 0 10 10" fill="currentColor"><circle cx="5" cy="5" r="5"/></svg>
          Platform Top Up #1 Terpercaya
        </div>
        <h1 class="hero-title">Top Up Game<br><span>Murah & Instan</span></h1>
        <p class="hero-sub">Isi ulang diamond, UC, VP dan mata uang game favoritmu. Proses otomatis — bayar via QRIS, GoPay, OVO & 20+ metode lainnya.</p>
        <div class="hero-cta-row">
          <a href="#games" class="hero-cta">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polygon points="5 3 19 12 5 21 5 3"/></svg>
            Mulai Top Up
          </a>
          <a href="<?=asset('pages/cek-transaksi.php')?>" class="hero-cta2">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
            Cek Transaksi
          </a>
        </div>
        <div class="hero-stats">
          <div class="hero-stat"><div class="hero-stat-num">50K+</div><div class="hero-stat-label">Transaksi sukses</div></div>
          <div class="hero-stat"><div class="hero-stat-num">200+</div><div class="hero-stat-label">Produk tersedia</div></div>
          <div class="hero-stat"><div class="hero-stat-num">24/7</div><div class="hero-stat-label">Layanan aktif</div></div>
        </div>
      </div>
      <div class="hero-games">
        <?php foreach([['⚔️','Mobile Legends','MOBA'],['🔥','Free Fire','Battle Royale'],['🎯','PUBG Mobile','Battle Royale'],['🌙','Genshin Impact','RPG'],['🎮','Valorant','FPS'],['🧱','Roblox','Sandbox']] as [$e,$n,$c]): ?>
        <div class="mini-card">
          <div class="mini-card-icon"><?=$e?></div>
          <div><div class="mini-card-name"><?=$n?></div><div class="mini-card-cat"><?=$c?></div></div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
<?php endif; ?>
</div>

<!-- ── POPULER SEKARANG ── -->

<!-- ══ FLASH SALE SECTION (jika ada voucher/promo aktif) ══ -->
<?php
$flashVouchers = [];
try {
    $fsq = db()->query("SELECT v.*, g.name AS game_name, g.image_url AS game_img, g.slug AS game_slug
        FROM vouchers v
        LEFT JOIN games g ON g.id = v.game_id
        WHERE v.is_active=1 AND v.used_count < v.quota
        AND (v.expires_at IS NULL OR v.expires_at > NOW())
        ORDER BY v.created_at DESC LIMIT 8");
    $flashVouchers = $fsq->fetchAll();
} catch(\Exception $e){}
?>
<?php if(!empty($flashVouchers)): ?>
<section class="sec" style="margin-bottom:32px;">
  <div class="sec-head" style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
    <div style="display:flex;align-items:center;gap:10px;">
      <span style="font-size:1.3rem;">⚡</span>
      <div>
        <div class="sec-title" style="font-size:1.1rem;font-weight:800;font-family:var(--f-display);color:var(--t1);">Flash Sale</div>
        <div style="font-size:.75rem;color:var(--t3);">Promo terbatas — segera manfaatkan!</div>
      </div>
    </div>
    <a href="<?=asset('pages/cek-transaksi.php')?>" style="font-size:.8rem;color:var(--red);font-weight:600;text-decoration:none;">Semua Promo →</a>
  </div>
  <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:12px;">
    <?php foreach($flashVouchers as $fv): ?>
    <a href="<?=asset('pages/game.php')?>?slug=<?=urlencode($fv['game_slug']??'')?>"
       style="background:var(--card);border:1.5px solid rgba(227,24,55,.25);border-radius:12px;overflow:hidden;text-decoration:none;display:block;transition:all .2s;"
       onmouseover="this.style.borderColor='rgba(227,24,55,.6)';this.style.transform='translateY(-2px)'"
       onmouseout="this.style.borderColor='rgba(227,24,55,.25)';this.style.transform=''">
      <!-- Flash badge -->
      <div style="background:linear-gradient(135deg,#e31837,#9b0f24);padding:4px 10px;display:flex;justify-content:space-between;align-items:center;">
        <span style="font-size:.65rem;font-weight:700;color:white;letter-spacing:.3px;">⚡ FLASH</span>
        <?php if($fv['expires_at']): ?>
        <span class="flash-timer" data-end="<?=strtotime($fv['expires_at'])?>"
              style="font-size:.65rem;font-weight:700;color:rgba(255,255,255,.8);font-family:monospace;">--:--:--</span>
        <?php endif; ?>
      </div>
      <?php if($fv['game_img']): ?>
      <div style="position:relative;height:90px;overflow:hidden;">
        <img src="<?=htmlspecialchars($fv['game_img'])?>" style="width:100%;height:100%;object-fit:cover;filter:brightness(.7);" onerror="this.style.display='none'"/>
        <div style="position:absolute;inset:0;background:linear-gradient(to top,rgba(0,0,0,.8) 0%,transparent 60%);"></div>
      </div>
      <?php endif; ?>
      <div style="padding:10px 12px;">
        <div style="font-weight:700;font-size:.84rem;color:var(--t1);margin-bottom:3px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
          <?=htmlspecialchars($fv['description']??$fv['code'])?>
        </div>
        <div style="font-size:.72rem;color:var(--t3);margin-bottom:6px;"><?=htmlspecialchars($fv['game_name']??'Game')?></div>
        <div style="display:flex;align-items:center;gap:6px;">
          <span style="font-weight:800;font-size:.9rem;color:var(--gold);">
            <?=$fv['type']==='percent' ? $fv['value'].'% OFF' : 'Rp '.number_format($fv['value'],0,',','.').' OFF'?>
          </span>
        </div>
        <div style="margin-top:6px;background:rgba(227,24,55,.1);border:1px solid rgba(227,24,55,.3);border-radius:6px;padding:4px 0;text-align:center;font-size:.72rem;font-weight:700;color:var(--redf);">
          ⚡ Kuota Terbatas
        </div>
      </div>
    </a>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<?php if(!empty($popular)): ?>
<section class="sec container">
  <div class="sec-hd">
    <div class="sec-title">
      <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="color:#f97316;flex-shrink:0"><path d="M12 2c0 6-6 8-6 14a6 6 0 0 0 12 0c0-6-6-8-6-14z"/><path d="M12 12c0 3-2 4-2 7a2 2 0 0 0 4 0c0-3-2-4-2-7z"/></svg>
      <div><h2>POPULER SEKARANG</h2><div class="sec-sub">Paling banyak dibeli hari ini</div></div>
    </div>
  </div>
  <div class="pop-grid">
    <?php foreach($popular as $i=>$g):
      $slug    = $g['slug'] ?? '';
      $acc     = $gameAccents[$slug] ?? $defaultAccent;
      $rankCls = $i===0?'top1':($i===1?'top2':($i===2?'top3':''));
    ?>
    <a href="<?=asset('pages/game.php')?>?slug=<?=urlencode($slug)?>"
       class="pop-card-v2">
      <?php if($rankCls): ?>
      <div class="pop-rank <?=$rankCls?>"><?=$i+1?></div>
      <?php endif; ?>
      <div class="pop-icon-wrap">
        <?php if(!empty($g['image_url'])): ?>
          <img src="<?=htmlspecialchars($g['image_url'])?>" alt="<?=htmlspecialchars($g['name'])?>" loading="lazy" onerror="this.style.display='none';this.nextElementSibling.style.display='flex'"/>
          <span style="display:none"><?=$acc['icon']?></span>
        <?php else: ?>
          <span><?=$acc['icon']?></span>
        <?php endif; ?>
      </div>
      <div class="pop-overlay"></div>
      <div class="pop-meta">
        <div class="pop-name-v2"><?=htmlspecialchars($g['name'])?></div>
        <span class="pop-badge"><?=htmlspecialchars($g['publisher']??$g['category_name']??'Game')?></span>
      </div>
      <div class="pop-topup-btn">Top Up</div>
    </a>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<!-- ── ALL GAMES ── -->
<section class="sec container" id="games">
  <div class="sec-hd">
    <div class="sec-title">
      <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color:var(--violet);flex-shrink:0"><rect x="2" y="6" width="20" height="12" rx="3"/><path d="M6 12h4m-2-2v4M15 12h.01M18 12h.01"/></svg>
      <div><h2>SEMUA GAME</h2><div class="sec-sub">Pilih game dan top up sekarang</div></div>
    </div>
  </div>

  <?php
  $catIcons = [
    'topup-games'   => '<svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="2" y="6" width="20" height="12" rx="3"/><path d="M6 12h4m-2-2v4M15 12h.01M18 12h.01"/></svg>',
    'pulsa-data'    => '<svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="5" y="2" width="14" height="20" rx="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>',
    'voucher'       => '<svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 12v10H4V12"/><path d="M22 7H2v5h20V7z"/></svg>',
    'entertainment' => '<svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polygon points="23 7 16 12 23 17 23 7"/><rect x="1" y="5" width="15" height="14" rx="2"/></svg>',
    'tagihan'       => '<svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>',
  ];
  $defaultCatIcon = '<svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/></svg>';
  ?>

  <?php if(!empty($cats)): ?>
  <div class="cat-tabs">
    <button class="cat-tab on" data-cat="0">
      <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
      Semua
    </button>
    <?php foreach($cats as $c): ?>
    <button class="cat-tab" data-cat="<?=$c['id']?>">
      <?=$catIcons[$c['slug']] ?? $defaultCatIcon?>
      <?=htmlspecialchars($c['name'])?>
    </button>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <?php if(!empty($games)): ?>
  <div class="games-wrap clamped" id="gw">
    <div class="games-grid">
      <?php foreach($games as $g): ?>
      <a href="<?=asset('pages/game.php')?>?slug=<?=urlencode($g['slug'])?>" class="game-card" data-cat="<?=$g['category_id']?>">
        <?php if($g['image_url']): ?>
          <img src="<?=htmlspecialchars($g['image_url'])?>" class="game-card-img" alt="<?=htmlspecialchars($g['name'])?>" loading="lazy"/>
        <?php else:
          $acc2 = $gameAccents[$g['slug']] ?? $defaultAccent;
        ?>
          <div class="game-card-placeholder">
            <div style="font-size:2rem"><?=$acc2['icon']?></div>
            <div class="ph-name"><?=htmlspecialchars($g['name'])?></div>
          </div>
        <?php endif; ?>
        <div class="game-card-hover"></div>
        <div class="game-card-btn">Top Up</div>
        <div class="game-card-name"><?=htmlspecialchars($g['name'])?></div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>

  <?php else: ?>
  <div class="demo-bar">
    <span>ℹ️</span>
    <span>Tampilan demo — database belum disetup. <a href="<?=asset('admin/games.php')?>">Tambah game via Admin →</a></span>
  </div>
  <div class="games-wrap clamped" id="gw">
    <div class="games-grid">
      <?php foreach($demoGames as $g):
        $acc3 = $gameAccents[$g['slug']] ?? $defaultAccent;
      ?>
      <div class="game-card" style="cursor:default">
        <div class="game-card-placeholder">
          <div style="font-size:2rem"><?=$acc3['icon']?></div>
          <div class="ph-name"><?=htmlspecialchars($g['name'])?></div>
        </div>
        <div class="game-card-name"><?=htmlspecialchars($g['name'])?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

  <div class="load-more-box">
    <button class="btn-load-more" onclick="document.getElementById('gw').classList.remove('clamped');this.closest('.load-more-box').style.display='none'">
      Tampilkan Semua Game
      <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="m6 9 6 6 6-6"/></svg>
    </button>
  </div>
</section>

<script>
(function(){
  var popup=document.getElementById('promo-popup');
  if(!popup) return;
  if(localStorage.getItem('ftara_popup_dismissed')===new Date().toDateString()){ return; }
  popup.style.display='flex';
  popup.addEventListener('click',function(e){ if(e.target===popup) closePopup(); });
  document.addEventListener('keydown',function(e){ if(e.key==='Escape') closePopup(); });
})();
function closePopup(){
  var p=document.getElementById('promo-popup'); if(!p) return;
  p.style.opacity='0'; p.style.transition='opacity .25s';
  setTimeout(function(){ p.style.display='none'; p.style.opacity=''; },250);
}
function dontShowAgain(cb){
  if(cb.checked) localStorage.setItem('ftara_popup_dismissed',new Date().toDateString());
  else localStorage.removeItem('ftara_popup_dismissed');
}
(function(){
  var timers=document.querySelectorAll('.flash-timer');
  if(!timers.length) return;
  function tick(){
    var now=Math.floor(Date.now()/1000);
    timers.forEach(function(el){
      var diff=parseInt(el.dataset.end)-now;
      if(diff<=0){ el.textContent='BERAKHIR'; el.style.color='#f87171'; return; }
      var h=Math.floor(diff/3600),m=Math.floor((diff%3600)/60),s=diff%60;
      el.textContent=String(h).padStart(2,'0')+':'+String(m).padStart(2,'0')+':'+String(s).padStart(2,'0');
    });
  }
  tick(); setInterval(tick,1000);
})();
</script>
<?php include 'includes/footer.php'; ?>