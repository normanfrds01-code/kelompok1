<?php if(!isset($pageTitle)) $pageTitle=siteName().' — Top Up Game'; $flash=getFlash(); $inAdmin=strpos($_SERVER['SCRIPT_FILENAME']??'','/admin/')!==false; ?>
<!DOCTYPE html>
<?php $ftaraLang=htmlspecialchars($_COOKIE['ftara_lang']??'ID'); ?>
<html lang="id" data-lang="<?=$ftaraLang?>">
<head>
<meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1"/>
<title><?=htmlspecialchars($pageTitle)?></title>
<link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 40 46'%3E%3Cdefs%3E%3ClinearGradient id='g' x1='20%25' y1='0%25' x2='80%25' y2='100%25'%3E%3Cstop offset='0%25' stop-color='%23ffd84d'/%3E%3Cstop offset='100%25' stop-color='%23b86200'/%3E%3C/linearGradient%3E%3C/defs%3E%3Cpolygon points='20,2 36,11 36,29 20,38 4,29 4,11' fill='%230d1322'/%3E%3Ctext x='20' y='26' font-family='sans-serif' font-size='17' font-weight='700' fill='url(%23g)' text-anchor='middle' dominant-baseline='auto'%3EF%3C/text%3E%3C/svg%3E"/>
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;600;700;800;900&family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet"/>
<link rel="stylesheet" href="<?=asset('assets/css/style.css')?>"/>
<link rel="stylesheet" href="<?=asset('assets/css/mobile.css')?>"/>
<style>
/* ══════════════════════════════════════════════
   THEME VARIABLES
   ══════════════════════════════════════════════ */
:root {
  /* ═══ FTARASTORE — Dunia Games Color Palette (Exact) ═══ */

  /* Red accent */
  --red:       #e31837;
  --red2:      #b8132d;
  --redf:      #ff3d57;
  --red-lo:    rgba(227,24,55,.10);
  --red-md:    rgba(227,24,55,.20);
  --red-hi:    rgba(227,24,55,.38);

  /* Info/status only */
  --cyan:      #e31837;
  --cyan2:     #c41230;
  --cyanf:     #ff3d57;
  --cyan-lo:   rgba(227,24,55,.1);
  --cyan-md:   rgba(56,189,248,.22);

  /* Prices — DG orange */
  --gold:      #f5a623;
  --gold2:     #e09020;
  --goldf:     #ffc107;
  --gold-lo:   rgba(245,166,35,.10);
  --gold-md:   rgba(245,166,35,.22);
  --gold-hi:   rgba(245,166,35,.40);

  /* Backgrounds — DG exact */
  --bg:          #0a0c15;
  --bg-primary:  #0a0c15;
  --bg-secondary:#0e1120;
  --bg-nav:      #07080f;
  --bg-header:   #07080f;
  --bg-footer:   #07080f;
  --bg-input:    #141928;
  --surface:     #0e1120;

  /* Cards — DG dark navy-purple */
  --card:    #141928;
  --card2:   #0e1120;
  --card3:   #1c2238;
  --bg-card:  #141928;
  --bg-card2: #0e1120;
  --input:    #141928;

  /* Text */
  --t1: #e8eaf0;
  --t2: #8892a4;
  --t3: #5a6478;
  --t4: #374155;
  --text-primary:   #e8eaf0;
  --text-secondary: #8892a4;
  --text-muted:     #5a6478;
  --white:          #e8eaf0;
  --white90: rgba(232,234,240,.9);
  --white70: rgba(232,234,240,.7);

  /* Borders */
  --b0: rgba(255,255,255,.03);
  --b1: rgba(255,255,255,.07);
  --b2: rgba(255,255,255,.11);
  --b3: rgba(255,255,255,.05);
  --border:   rgba(255,255,255,.07);
  --border-2: rgba(255,255,255,.04);

  /* Shadows */
  --shadow:   0 4px 24px rgba(0,0,0,.6);
  --shadowlg: 0 12px 48px rgba(0,0,0,.7);
  --shadowxl: 0 24px 72px rgba(0,0,0,.75);

  /* Fonts */
  --f-display: 'Orbitron', sans-serif;
  --f-body:    'Inter', sans-serif;
  --f-title:   'Orbitron', sans-serif;
  --r: 10px;

  /* Violet */
  --violet2:   #6d28d9;
  --violetf:   #c4b5fd;
  --violet-lo: rgba(124,58,237,.10);
  --violet-md: rgba(124,58,237,.22);

  --nav-active: var(--red);
}  /* ← close :root */

/* Light mode removed — always dark */

/* ── Apply variables ke elemen utama ── */
*, *::before, *::after { box-sizing: border-box; }

body {
  background: var(--bg) !important;
  color: var(--text-primary) !important;
  transition: background .3s ease, color .3s ease;
}

/* Header */
.hdr {
  background: var(--bg-header) !important;
  border-bottom-color: var(--border) !important;
  transition: background .3s ease;
}
.hdr-nav {
  background: var(--bg-nav) !important;
  border-top-color: var(--border-2) !important;
}

/* Cards */
.admin-card, .ck-card, .pop-card,
.game-card, .mini-card,
[class*="-card"]:not(.pop-card-v2) {
  background: var(--bg-card) !important;
  border-color: var(--border) !important;
}

/* Input & select */
.finput, input, select, textarea {
  background: var(--bg-input) !important;
  color: var(--text-primary) !important;
  border-color: var(--border) !important;
}
.finput::placeholder, input::placeholder { color: var(--text-muted) !important; }

/* Search bar */
.search-bar {
  background: var(--bg-card) !important;
  border-color: var(--border) !important;
}
.search-bar input { background: transparent !important; color: var(--text-primary) !important; }

/* Nav links */
.nav-link { color: var(--text-secondary) !important; }
.nav-link.on { color: var(--text-primary) !important; }

/* Teks umum */
h1,h2,h3,h4,h5,h6 { color: var(--text-primary) !important; }
p, span, label, td, th, li, div { color: inherit; }
.flabel, .sec-sub, .fhint { color: var(--text-secondary) !important; }

/* Cat tabs */
.cat-tab {
  background: var(--bg-card) !important;
  color: var(--text-secondary) !important;
  border-color: var(--border) !important;
}
.cat-tab.on {
  background: var(--violet) !important;
  color: #fff !important;
}

/* Sections */
.sec { background: transparent !important; }
.container { background: transparent !important; }

/* Placeholder game card */
.game-card-placeholder {
  background: linear-gradient(145deg, var(--card2), var(--card)) !important;
  display: flex !important;
  flex-direction: column !important;
  align-items: center !important;
  justify-content: center !important;
  min-height: 160px !important;
  width: 100% !important;
  height: 100% !important;
  position: relative !important;
}

/* Placeholder icon */
.game-card-placeholder .ph-icon {
  font-size: 2.2rem !important;
  opacity: .45 !important;
  margin-bottom: 6px !important;
}

/* ph-name hidden — sudah ada .game-card-name di luar placeholder */
.game-card-placeholder .ph-name {
  display: none !important;
}

/* Card tanpa gambar: gradient overlay tetap ada */
.game-card:not(:has(.game-card-img)) .game-card-hover,
.game-card:has(.game-card-placeholder) .game-card-hover {
  background: linear-gradient(
    to top,
    rgba(5,10,20,.90) 0%,
    rgba(5,10,20,.40) 50%,
    transparent       100%
  ) !important;
  top: 40% !important;
}

/* Nama di card placeholder — pakai warna kontras */
.game-card:has(.game-card-placeholder) .game-card-name {
  color: #ffffff !important;
  text-shadow: 0 1px 8px rgba(0,0,0,.9), 0 2px 16px rgba(0,0,0,.7) !important;
}

/* ── Admin dark mode — comprehensive fix ── */
.admin-wrap   { background: var(--bg-primary) !important; }
.admin-main   { background: var(--bg-primary) !important; color: var(--text-primary) !important; }
.admin-sidebar { background: var(--bg-secondary) !important; border-color: var(--border) !important; }

/* Admin card head */
.admin-card-head {
  background: var(--bg-card2) !important;
  border-bottom-color: var(--border) !important;
  color: var(--text-primary) !important;
}
.admin-card-head h3 { color: var(--text-primary) !important; }

/* Tabel admin */
table { background: var(--bg-card) !important; color: var(--text-primary) !important; }
thead, thead tr { background: var(--bg-card2) !important; }
th {
  background: var(--bg-card2) !important;
  color: var(--text-secondary) !important;
  border-color: var(--border) !important;
}
td { border-color: var(--border-2) !important; color: var(--text-primary) !important; }
tr:hover td { background: var(--bg-card2) !important; }

/* Sidebar nav items */
.sidebar-nav a, .sidebar-link {
  color: var(--text-secondary) !important;
}
.sidebar-nav a:hover, .sidebar-link:hover,
.sidebar-nav a.active, .sidebar-link.active {
  background: var(--bg-card2) !important;
  color: var(--text-primary) !important;
}
.sidebar-group-label { color: var(--text-muted) !important; }
.sidebar-user-name   { color: var(--text-primary) !important; }
.sidebar-user-role   { color: var(--text-muted) !important; }

/* Admin title & section headers */
.admin-title { color: var(--text-primary) !important; }
.admin-section-title { color: var(--text-secondary) !important; }

/* Stat cards di dashboard */
.stat-card, .kpi-card, .dash-stat {
  background: var(--bg-card) !important;
  border-color: var(--border) !important;
  color: var(--text-primary) !important;
}
.stat-label, .kpi-label { color: var(--text-secondary) !important; }
.stat-value, .kpi-value { color: var(--text-primary) !important; }

/* Tombol & badge admin */
.admin-badge, .badge {
  background: var(--bg-card2) !important;
  border-color: var(--border) !important;
  color: var(--text-secondary) !important;
}

/* Modal / overlay */
.modal-content, .modal-body {
  background: var(--bg-card) !important;
  border-color: var(--border) !important;
  color: var(--text-primary) !important;
}

/* Alert / notif box */
.alert, .notif-box {
  border-color: var(--border) !important;
}

/* Pagination */
.pagination a, .page-link {
  background: var(--bg-card) !important;
  border-color: var(--border) !important;
  color: var(--text-secondary) !important;
}
.pagination .active a, .page-link.active {
  background: var(--violet) !important;
  color: #fff !important;
}

/* Filter / search bar di admin */
.admin-filter, .filter-bar {
  background: var(--bg-card2) !important;
  border-color: var(--border) !important;
}

/* Tambahkan ke transition list */
table, thead, th, td, .stat-card, .kpi-card,
.admin-card-head, .sidebar-nav a {
  transition: background .3s ease, border-color .3s ease, color .2s ease !important;
}

/* Demo bar */
.demo-bar { background: var(--bg-card) !important; border-color: var(--border) !important; }

/* Checkout */
.ck-head { background: var(--bg-card2) !important; border-color: var(--border) !important; }
.drow { border-color: var(--border-2) !important; }
.total-row { background: var(--bg-card2) !important; }

/* Pay badges */
.pay-badge {
  background: var(--bg-card2) !important;
  border-color: var(--border) !important;
  color: var(--text-secondary) !important;
}

/* Smooth transition semua elemen */
body, .hdr, .hdr-nav, .admin-card, .ck-card,
.finput, input, select, .search-bar, .cat-tab,
.admin-sidebar, .admin-main, .game-card-placeholder {
  transition: background .3s ease, border-color .3s ease, color .2s ease !important;
}

/* ════════════════════════════════════════════
   DARK MODE — override semua hardcoded white
   dari style.css yang tidak pakai CSS variable
   ════════════════════════════════════════════ */

/* ── Hardcoded white backgrounds dari style.css ── */
.btn-ghost                { background: var(--card) !important; color: var(--t1) !important; border-color: var(--b2) !important; }
.btn-ghost:hover          { background: var(--card2) !important; color: #ff4d63 !important; }
.search-bar               { background: var(--card) !important; border-color: var(--b2) !important; }
.search-bar:focus-within  { background: var(--card2) !important; border-color: var(--red) !important; }
.pop-card                 { background: var(--card) !important; }
.game-card-name           { background: var(--card) !important; color: var(--t1) !important; border-color: var(--b1) !important; }
.game-card                { background: var(--card) !important; border-color: var(--b1) !important; }
.p-card                   { background: var(--card) !important; border-color: var(--b1) !important; }
.p-card:hover             { background: var(--card2) !important; }
.p-card.sel               { background: var(--red-lo) !important; }
.order-panel              { background: var(--card) !important; border-color: var(--b1) !important; }
.ck-card                  { background: var(--card) !important; border-color: var(--b1) !important; }
.cek-card                 { background: var(--card) !important; border-color: var(--b1) !important; }
.success-card             { background: var(--card) !important; border-color: var(--b1) !important; }
.auth-card                { background: var(--card) !important; border-color: var(--b1) !important; }
.lb-table-wrap            { background: var(--card) !important; border-color: var(--b1) !important; }
.modal-box                { background: var(--card) !important; border-color: var(--b2) !important; }
.kalk-card                { background: var(--card) !important; }
.btn-load-more            { background: var(--card) !important; border-color: var(--b2) !important; color: var(--t1) !important; }

/* ── Admin sidebar & main ── */
.admin-sb                 { background: var(--card) !important; border-color: var(--b1) !important; }
.admin-layout .admin-sb   { display: none !important; } /* hidden - using dg-sidebar */
.admin-sb a               { color: var(--t2) !important; }
.admin-sb a:hover,
.admin-sb a.on            { background: rgba(212,0,0,.07) !important; color: #ff4d63 !important; }
.admin-main               { background: var(--bg) !important; }
.admin-layout .admin-main { margin-left: 0 !important; }
.admin-card               { background: var(--card) !important; border-color: var(--b1) !important; }
.admin-card-head          { background: var(--card2) !important; border-color: var(--b1) !important; }

/* ── Stat cards ── */
.stat-card                { background: var(--card) !important; border-color: var(--b1) !important; }

/* ── Table ── */
.dtable th                { background: var(--card2) !important; color: var(--t2) !important; border-color: var(--b1) !important; }
.dtable td                { color: var(--t1) !important; border-color: var(--b0) !important; }
.dtable tr:hover td       { background: rgba(212,0,0,.06) !important; }

/* ── Cat tabs ── */
.cat-tab                  { background: var(--card) !important; border-color: var(--b1) !important; color: var(--t2) !important; }
.cat-tab:hover            { background: var(--card2) !important; color: #ff4d63 !important; }
.cat-tab.on               { background: linear-gradient(135deg,#d40000,#aa0000) !important; color: #03111f !important; }

/* ── Leaderboard & kalkulator items ── */
.kalk-wrap *,
.lb-wrap * { color: var(--t1); }

/* ── Footer social icons ── */
.footer-social a          { background: var(--card2) !important; border-color: var(--b1) !important; }

/* ── Input, select, textarea focus ── */
.finput:focus             { background: var(--card2) !important; }

/* ══════════════════════════════════════
   GAME CARD — Clean profesional
   Nama selalu terlihat, tidak ada tombol
   ══════════════════════════════════════ */

/* ══ TOPBAR — SEAGM style ══ */
.hdr-topbar {
  background: var(--bg-nav);
  border-bottom: 1px solid var(--b1);
  position: relative;
  z-index: 101;
}
.hdr-topbar-inner {
  max-width: 1280px;
  margin: 0 auto;
  padding: 0 24px;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.hdr-topbar-left,
.hdr-topbar-right {
  display: flex;
  align-items: center;
  gap: 20px;
}
.topbar-link {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  font-size: .72rem;
  font-weight: 500;
  color: var(--t3);
  text-decoration: none;
  transition: color .15s;
  letter-spacing: .01em;
}
.topbar-link:hover { color: var(--red) !important; }
.topbar-logout:hover { color: var(--red) !important; }
.topbar-user {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  font-size: .72rem;
  font-weight: 600;
  color: var(--red);
}
.topbar-status {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: .69rem;
  font-weight: 600;
  padding: 3px 10px;
  border-radius: 20px;
  border: 1px solid;
  white-space: nowrap;
}
.topbar-status .status-dot {
  width: 5px; height: 5px;
  border-radius: 50%;
  display: block;
  animation: sPulse 2s infinite;
}
@keyframes sPulse{0%,100%{transform:scale(1);opacity:.5}50%{transform:scale(2.4);opacity:0}}

.topbar-theme {
  width: 26px !important;
  height: 26px !important;
  padding: 0 !important;
}
.topbar-btn-register {
  display: inline-flex;
  align-items: center;
  font-size: .71rem;
  font-weight: 700;
  padding: 4px 12px;
  background: var(--red);
  color: white;
  border-radius: 6px;
  text-decoration: none;
  transition: all .2s;
}
.topbar-btn-register:hover { filter: brightness(1.1); }

.topbar-brand-tag {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  font-size: .69rem;
  color: var(--red);
  font-weight: 600;
  letter-spacing: .02em;
}
.topbar-sep {
  color: var(--b3);
  font-size: .7rem;
  margin: 0 2px;
}
.nav-auth-btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
}

/* Main navbar — cleaner without tagline */
.logo-tag { display: none !important; }
.logo-name { font-size: 20px !important; letter-spacing: 1.5px !important; }

@media(max-width:768px){
  .hdr-topbar { display: none; }
}

/* ══ GAME CARD — Unipin style glow hover ══ */
.game-card {
  position: relative !important; overflow: hidden !important;
  border-radius: 14px !important; display: block !important;
  border: 1.5px solid var(--b1) !important;
  transition: border-color .3s, box-shadow .3s, transform .3s !important;
}
.game-card:hover {
  border-color: rgba(212,0,0,.5) !important;
  box-shadow: 0 0 0 1px rgba(212,0,0,.3),0 0 20px rgba(212,0,0,.25),0 0 40px rgba(212,0,0,.12),0 8px 32px rgba(0,0,0,.4) !important;
  transform: translateY(-3px) !important;
}
.game-card .game-card-img {
  display: block !important; width: 100% !important; height: 100% !important;
  object-fit: cover !important; object-position: center top !important; position: relative !important; z-index: 1 !important;
  transition: transform .4s !important;
}
.game-card:hover .game-card-img { transform: scale(1.05) !important; }
.game-card .game-card-placeholder { position: relative !important; z-index: 1 !important; }
.game-card-hover {
  position: absolute !important; inset: unset !important;
  bottom: 0 !important; left: 0 !important; right: 0 !important; top: 55% !important;
  background: linear-gradient(to top,rgba(5,8,20,.94) 0%,rgba(5,8,20,.5) 45%,transparent 100%) !important;
  z-index: 2 !important; pointer-events: none !important; display: block !important;
  transition: top .3s, background .3s !important;
}
.game-card:hover .game-card-hover {
  top: 0 !important;
  background: linear-gradient(to top,rgba(5,8,20,.95) 0%,rgba(5,8,20,.7) 40%,rgba(5,8,20,.15) 75%,transparent 100%) !important;
}
.game-card-btn {
  position: absolute !important; bottom: 38px !important; left: 50% !important;
  transform: translateX(-50%) translateY(8px) !important; z-index: 12 !important;
  display: inline-flex !important; align-items: center !important; justify-content: center !important;
  padding: 7px 22px !important; background: transparent !important;
  border: 1.5px solid rgba(212,0,0,.7) !important; border-radius: 6px !important;
  color: #d40000 !important; font-size: .74rem !important; font-weight: 700 !important;
  letter-spacing: .6px !important; text-transform: uppercase !important;
  white-space: nowrap !important; opacity: 0 !important; visibility: hidden !important;
  transition: opacity .25s, transform .25s, background .2s !important;
  pointer-events: none !important;
}
.game-card:hover .game-card-btn {
  opacity: 1 !important; visibility: visible !important;
  transform: translateX(-50%) translateY(0) !important;
  background: rgba(212,0,0,.1) !important;
}
.game-card-name {
  position: absolute !important; bottom: 0 !important; left: 0 !important; right: 0 !important;
  z-index: 10 !important; padding: 8px 12px 10px !important;
  font-family: 'Inter',sans-serif !important; font-size: .82rem !important; font-weight: 600 !important;
  color: #fff !important; text-shadow: 0 1px 6px rgba(0,0,0,.8) !important;
  background: transparent !important; border: none !important;
  white-space: nowrap !important; overflow: hidden !important; text-overflow: ellipsis !important;
  pointer-events: none !important; display: block !important; visibility: visible !important; opacity: 1 !important;
  transition: bottom .3s !important;
}
.game-card:hover .game-card-name { bottom: 48px !important; }
.game-card:hover .game-card-name,
.game-card:focus .game-card-name { display:block !important; visibility:visible !important; opacity:1 !important; color:#fff !important; }


/* ── Pay methods box ── */
.pay-methods              { background: var(--card2) !important; border-color: var(--b2) !important; }
.pay-badge                { background: var(--card3) !important; border-color: var(--b1) !important; color: var(--t2) !important; }

/* Header buttons — Super Admin, Keluar, Masuk, Daftar */
.btn-ghost {
  background: var(--bg-card) !important;
  border-color: var(--border) !important;
  color: var(--text-primary) !important;
}
.btn-ghost:hover {
  background: var(--bg-card2) !important;
}
.btn-gold {
  background: linear-gradient(135deg,#ff8c00,#e07800) !important;
  color: #fff !important;
}

/* Semua elemen dengan background putih eksplisit */
[style*="background:#fff"],
[style*="background: #fff"],
[style*="background:white"],
[style*="background:#ffffff"],
[style*="background: #ffffff"],
[style*="background-color:#fff"],
[style*="background-color: #fff"],
[style*="background-color:#ffffff"],
[style*="background-color: #ffffff"] {
  background: var(--bg-card) !important;
  background-color: var(--bg-card) !important;
}

/* Card container general */
.card,
.box,
.panel,
.section-box,
.content-box,
.wrap-card {
  background: var(--bg-card) !important;
  border-color: var(--border) !important;
  color: var(--text-primary) !important;
}

/* Tab buttons (Audit Log, IP Blacklist, Rate Limits, dll) */
.tab-btn,
.tab-item,
[role="tab"],
.btn-tab,
.filter-tab {
  background: var(--bg-card) !important;
  border-color: var(--border) !important;
  color: var(--text-secondary) !important;
}
.tab-btn.active,
.tab-item.active,
[role="tab"][aria-selected="true"] {
  background: var(--violet) !important;
  color: #fff !important;
  border-color: var(--violet) !important;
}

/* Leaderboard & kalkulator card items */
.lb-card,
.leaderboard-card,
.calc-item,
.kalk-item,
.menu-item,
.list-item {
  background: var(--bg-card) !important;
  border-color: var(--border) !important;
  color: var(--text-primary) !important;
}

/* KPI/stat cards di laporan revenue */
.kpi-box,
.report-card,
.stat-box,
[class*="kpi"],
[class*="stat-"] {
  background: var(--bg-card) !important;
  border-color: var(--border) !important;
}

/* Banner card di admin banners.php */
.banner-card,
.banner-item,
.media-card {
  background: var(--bg-card) !important;
  border-color: var(--border) !important;
}
.banner-card p,
.banner-card span {
  color: var(--text-secondary) !important;
}

/* Form group & label */
.fg { color: var(--text-primary) !important; }
.form-group { background: transparent !important; }

/* Dropdown / select option */
select option {
  background: var(--bg-card2) !important;
  color: var(--text-primary) !important;
}

/* Scrollbar */
::-webkit-scrollbar { background: var(--bg-secondary); width: 6px; }
::-webkit-scrollbar-thumb { background: var(--border); border-radius: 99px; }

/* Teks di dalam div dengan warna inline gelap */
[style*="color:#0"],
[style*="color: #0"],
[style*="color:#1"],
[style*="color: #1"],
[style*="color:#2"],
[style*="color: #2"],
[style*="color:#3"],
[style*="color: #3"],
[style*="color:#4"],
[style*="color: #4"] {
  /* Jangan override semua — terlalu agresif. Hanya override text-primary */
  color: inherit;
}

/* Override spesifik: teks hitam di dalam card dark */
.admin-card *,
.ck-card *,
table * {
  color: inherit;
}

/* Empty state / no-data box */
.empty-state,
.no-data,
.empty-box {
  background: var(--bg-card) !important;
  color: var(--text-muted) !important;
}

/* Voucher & promo badge count */
.badge-count,
.count-badge {
  background: var(--violet) !important;
  color: #fff !important;
}

/* Danger/warning/success teks tetap warnanya */
.text-danger, [style*="color:#ef4444"], [style*="color:#dc2626"],
[style*="color:red"] { color: #f87171 !important; }
.text-success, [style*="color:#10b981"], [style*="color:#22c55e"],
[style*="color:green"] { color: #34d399 !important; }
.text-warning, [style*="color:#ff8c00"], [style*="color:#eab308"] { color: #ffaa33 !important; }

/* ── Nav animations (tetap sama) ── */
.hdr-nav-inner .nav-link {
  position: relative;
  transition: color .22s ease, transform .18s ease;
}
.hdr-nav-inner .nav-link::after {
  content: '';
  position: absolute;
  bottom: -3px; left: 50%;
  width: 0; height: 2px;
  border-radius: 99px;
  background: linear-gradient(90deg, #d40000, #ff8c00);
  transform: translateX(-50%);
  transition: width .28s cubic-bezier(.4,0,.2,1);
  pointer-events: none;
}
.hdr-nav-inner .nav-link:hover::after { width: 100%; animation: navShimmer 1.8s linear infinite; }
.hdr-nav-inner .nav-link.on::after    { width: 100%; background: linear-gradient(90deg, #d40000, #ff5566) !important; }
@keyframes navShimmer { 0%{background-position:0%}100%{background-position:200%} }
.hdr-nav-inner .nav-link svg { display:inline-block;flex-shrink:0;transition:transform .3s cubic-bezier(.34,1.56,.64,1),color .22s ease; }
.hdr-nav-inner .nav-link:hover svg { transform:translateY(-3px) scale(1.2);color:#d40000; }
.hdr-nav-inner .nav-link.on    svg { color:#ff8c00; }
.hdr-nav-inner .nav-link:hover { transform:translateY(-1px); }
.hdr-nav-inner .nav-link:active { transform:translateY(0) scale(.97); }
.hdr-nav-inner .nav-link.admin:hover     { color:#a78bfa; }
.hdr-nav-inner .nav-link.admin:hover svg { color:#a78bfa; }
.hdr-nav-inner .nav-link.admin::after    { background:linear-gradient(90deg,#a78bfa,#f472b6); }

/* ── Logo header — adaptif per tema ── */

 .logo-name { color: #e2e8f0; }
.logo-a     { color: #d40000 !important; }
.logo-store { color: #d40000 !important; }

 .logo-tag { color: #475569; }

/* ── Logo di dalam login/register card — adaptif per tema ── */
/* Beberapa halaman render logo di dalam card dengan class .auth-logo / .login-logo */
.auth-logo,
.login-logo,
.auth-logo-name,
.card-logo-name { color: #e2e8f0 !important; }

/* Pop card arrow — adaptif */

 .pop-arrow-v2 { color: #e2e8f0; }

/* Pop card rank — light mode gelap */





/* ── Theme Toggle Button ── */
.theme-toggle {
  width: 36px; height: 36px;
  border-radius: 50%;
  border: 1px solid var(--border);
  background: var(--bg-card);
  cursor: pointer;
  display: flex; align-items: center; justify-content: center;
  transition: background .25s ease, border-color .25s ease, transform .2s cubic-bezier(.34,1.56,.64,1);
  flex-shrink: 0;
  position: relative;
  overflow: hidden;
}
.theme-toggle:hover {
  transform: scale(1.1) rotate(15deg);
  border-color: var(--gold);
}
.theme-toggle:active { transform: scale(.95); }

/* Sun & Moon icons di dalam toggle */
.toggle-sun, .toggle-moon {
  position: absolute;
  width: 18px; height: 18px;
  transition: opacity .3s ease, transform .4s cubic-bezier(.34,1.56,.64,1);
}


 .toggle-sun  { opacity: 0;  transform: rotate(-90deg) scale(.5); }
 .toggle-moon { opacity: 1;  transform: rotate(0deg) scale(1); }

/* ══ AVATAR DROPDOWN ══ */
.nav-avatar-wrap { position: relative; }
.nav-avatar-btn {
  display: inline-flex; align-items: center; gap: 7px;
  background: none; border: 1px solid var(--b2); border-radius: 50px;
  padding: 4px 10px 4px 4px; cursor: pointer; color: var(--t2);
  transition: all .2s; font-size: .73rem;
}
.nav-avatar-btn:hover { border-color: var(--red); color: var(--text-primary); background: var(--bg-card2); }
.nav-avatar-circle {
  width: 24px; height: 24px; border-radius: 50%;
  background: linear-gradient(135deg,#d40000,#aa0000);
  color: #03111f; font-weight: 800; font-size: .7rem;
  display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.nav-avatar-name { font-weight: 600; max-width: 90px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.nav-avatar-caret { transition: transform .2s; opacity: .6; }
.nav-avatar-btn.open .nav-avatar-caret { transform: rotate(180deg); }
.nav-avatar-dropdown {
  position: absolute; top: calc(100% + 8px); right: 0; min-width: 200px;
  background: var(--bg-card); border: 1px solid var(--b2); border-radius: 12px;
  box-shadow: 0 8px 32px rgba(0,0,0,.4); z-index: 9999; overflow: hidden;
  opacity: 0; visibility: hidden; transform: translateY(-6px);
  transition: opacity .2s, transform .2s, visibility .2s; pointer-events: none;
}
.nav-avatar-dropdown.open { opacity: 1; visibility: visible; transform: translateY(0); pointer-events: auto; }
.nav-dd-header {
  display: flex; align-items: center; gap: 10px; padding: 14px 16px;
  background: var(--bg-card2);
}
.nav-dd-avatar {
  width: 36px; height: 36px; border-radius: 50%;
  background: linear-gradient(135deg,#d40000,#aa0000);
  color: #03111f; font-weight: 800; font-size: .88rem;
  display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.nav-dd-name { font-size: .82rem; font-weight: 700; color: var(--t1); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.nav-dd-role { font-size: .68rem; color: var(--red); font-weight: 600; text-transform: uppercase; letter-spacing: .05em; margin-top: 2px; }
.nav-dd-divider { height: 1px; background: var(--b1); margin: 4px 0; }
.nav-dd-item {
  display: flex; align-items: center; gap: 10px;
  padding: 10px 16px; font-size: .82rem; color: var(--t2); text-decoration: none;
  transition: background .15s, color .15s;
}
.nav-dd-item:hover { background: var(--bg-card2); color: var(--t1); }
.nav-dd-logout { color: var(--t3); }
.nav-dd-logout:hover { background: rgba(239,68,68,.08) !important; color: #fca5a5 !important; }
.topbar-brand-tag svg { color: var(--red); }

/* ══ SINGLE ROW NAVBAR — Ourastore style ══ */
.hdr-top {
  display: flex !important;
  align-items: center !important;
  gap: 0 !important;
  padding: 0 28px !important;
  height: 58px !important;
  max-width: 1440px !important;
  margin: 0 auto !important;
}

.hdr-left-group { display: none !important; } /* unused now */

.logo {
  flex-shrink: 0 !important;
  margin-right: 24px !important;
}

/* Search bar: flex-grow fills middle */
.search-bar {
  flex: 1 !important;
  max-width: 100% !important;
  margin: 0 20px !important;
}

/* ── Row 2: nav bar ── */
.hdr-nav2 {
  display: flex !important;
  align-items: center !important;
  justify-content: space-between !important;
  padding: 0 28px !important;
  height: 44px !important;
  max-width: 1440px !important;
  margin: 0 auto !important;
  border-top: 1px solid var(--b1) !important;
}
.hdr-nav2-left {
  display: flex !important;
  align-items: center !important;
  gap: 2px !important;
}
.hdr-nav2-right {
  display: flex !important;
  align-items: center !important;
  gap: 8px !important;
}

/* Inline nav links */
.hdr-inline-nav {
  display: flex !important;
  align-items: center !important;
  gap: 2px !important;
  flex-shrink: 0 !important;
}
.hdr-inline-nav .nav-link {
  display: inline-flex !important;
  align-items: center !important;
  gap: 6px !important;
  padding: 7px 12px !important;
  border-radius: 8px !important;
  font-size: .83rem !important;
  font-weight: 500 !important;
  color: var(--t2) !important;
  text-decoration: none !important;
  white-space: nowrap !important;
  transition: color .15s, background .15s !important;
}
.hdr-inline-nav .nav-link:hover {
  color: var(--t1) !important;
  background: var(--card2) !important;
}
.hdr-inline-nav .nav-link.on {
  color: var(--red) !important;
  font-weight: 700 !important;
}
.hdr-inline-nav .nav-link.admin {
  color: var(--t2) !important;
}
.hdr-inline-nav .nav-link.admin.on {
  color: var(--gold) !important;
  font-weight: 700 !important;
}
.hdr-inline-nav .nav-link.admin.on::after {
  width: 100% !important;
  background: linear-gradient(90deg, #ff8c00, #ffd84d) !important;
}

/* Hide old hdr-nav (second bar) */
.hdr-nav { display: none !important; }

/* IDR Selector */
.idr-selector {
  display: inline-flex !important;
  align-items: center !important;
  gap: 6px !important;
  padding: 6px 12px !important;
  border: 1px solid var(--b2) !important;
  border-radius: 8px !important;
  font-size: .78rem !important;
  font-weight: 600 !important;
  color: var(--t2) !important;
  cursor: pointer !important;
  transition: all .2s !important;
  white-space: nowrap !important;
}
.idr-selector:hover {
  border-color: var(--red) !important;
  color: var(--t1) !important;
}
.idr-flag { font-size: 1rem !important; line-height: 1 !important; }
.idr-text { font-size: .75rem !important; font-weight: 600 !important; }

/* Auth buttons consistent */
.nav-auth-btn {
  display: inline-flex !important;
  align-items: center !important;
  gap: 6px !important;
  font-size: .82rem !important;
  font-weight: 600 !important;
  padding: 8px 16px !important;
  border-radius: 8px !important;
  text-decoration: none !important;
  white-space: nowrap !important;
}

/* ══ BANNER — Ourastore proportions ══ */
.banner-slider,
.banner-wrap {
  height: 420px !important;
  max-height: 420px !important;
  border-radius: 12px !important;
  overflow: hidden !important;
}
.banner-slide {
  height: 380px !important;
}
.banner-slide img,
.banner-slide .banner-img {
  width: 100% !important;
  height: 100% !important;
  object-fit: cover !important;
  object-position: center !important;
}
@media(max-width:768px){
  .banner-slider,.banner-wrap { height: 200px !important; }
  .banner-slide { height: 200px !important; }
  .hdr-inline-nav { display: none !important; }
}


/* ══ FORCE DARK — no white in dark mode ══ */
.hdr-top,
.hdr-topbar,
.hdr-nav,
header { background: var(--bg-header) !important; }




/* Remove all white backgrounds in dark mode */
*:not(img):not(svg):not(.game-card-btn):not(.status-dot) {
  --white-override: var(--card);
}
.faq-item,
.faq-search,
.tos-card,
.ck-card,
.auth-card,
.success-card,
.cek-card,
.result-box,
.admin-card,
.stat-card,
.kpi,
.chart-card,
table {
  background: var(--card) !important;
}
thead,
thead tr,
thead th {
  background: var(--card2) !important;
  color: var(--t2) !important;
}


/* ══ DARK MODE: nuke semua sisa white ══ */
*:not(img):not(svg):not(canvas) {
  --force-bg-override: transparent;
}
.auth-card      { background: var(--bg-card) !important; }
.ck-card        { background: var(--bg-card) !important; }
.result-box     { background: var(--bg-card) !important; border-color: var(--b1) !important; }
.cek-card       { background: var(--bg-card) !important; }
.success-card   { background: var(--bg-card) !important; }
.kpi            { background: var(--card) !important; }
.chart-card     { background: var(--card) !important; }
.tos-card       { background: var(--card) !important; }
.faq-item       { background: var(--card) !important; border-color: var(--b1) !important; }
.faq-search     { background: var(--card) !important; border-color: var(--b1) !important; }
.step-box       { background: var(--card) !important; }
.side-card      { background: var(--card) !important; }
.p-card2        { background: var(--card) !important; }
.help-strip     { background: var(--card2) !important; }
.pay-methods    { background: var(--card2) !important; border-color: var(--b1) !important; }
.total-row      { background: var(--card2) !important; }
.checkout-wrap  { background: transparent !important; }
.lb-table-wrap  { background: var(--card) !important; }
select.finput   { background: var(--input) !important; color: var(--t1) !important; }
option          { background: var(--bg-secondary) !important; color: var(--t1) !important; }

/* Light mode explicit — putih hanya di light */






/* Nav2 auth links — Ourastore style */
.nav2-auth-link {
  display: inline-flex; align-items: center; gap: 6px;
  font-size: .84rem; font-weight: 500; color: var(--t2);
  text-decoration: none; padding: 6px 14px;
  border-radius: 8px; transition: all .15s;
}
.nav2-auth-link:hover { color: var(--t1); background: var(--card2); }
.nav2-register {
  background: transparent;
  border: 1px solid var(--b2);
}
.nav2-register:hover { border-color: var(--red); color: var(--red) !important; }

/* ══ GOLD ACCENT UPGRADE — CTA, harga, highlight ══ */

/* btn-submit → Cyan (professional) */
.btn-submit,
button[type="submit"].btn-submit,
.btn-pay,
#pay-btn {
  background: linear-gradient(135deg, #d40000 0%, #aa0000 60%, #880000 100%) !important;
  color: #ffffff !important;
  font-weight: 700 !important;
  box-shadow: 0 4px 20px rgba(212,0,0,.35), 0 1px 4px rgba(0,0,0,.3) !important;
  border: none !important;
  transition: all .25s cubic-bezier(.4,0,.2,1) !important;
}
.btn-submit:hover,
.btn-pay:hover,
#pay-btn:hover {
  background: linear-gradient(135deg, #ff3333 0%, #d40000 60%, #aa0000 100%) !important;
  box-shadow: 0 6px 28px rgba(212,0,0,.4), 0 2px 8px rgba(0,0,0,.3) !important;
  transform: translateY(-1px) !important;
  color: #07060f !important;
}
.btn-submit:active, .btn-pay:active { transform: translateY(0) !important; }

/* btn-gold juga gold (sudah ada, tapi perkuat) */
.btn-gold,
.topbar-btn-register {
  background: linear-gradient(135deg, #ff8c00, #e07800) !important;
  color: #07060f !important;
  font-weight: 700 !important;
  box-shadow: 0 3px 14px rgba(255,140,0,.3) !important;
}
.btn-gold:hover { 
  filter: brightness(1.1) !important;
  box-shadow: 0 5px 20px rgba(255,140,0,.45) !important;
  transform: translateY(-1px) !important;
}

/* Harga produk — gold lebih bold */
.total-amount,
.stat-val,
.drow-val[style*="gold"],
span[style*="var(--gold)"] {
  font-weight: 800 !important;
  letter-spacing: -.02em !important;
}

/* Produk card selected — gold border */
.p-card.sel,
.p-card2.sel {
  border-color: #ff8c00 !important;
  box-shadow: 0 0 0 2px rgba(255,140,0,.25), inset 0 0 0 1px rgba(255,140,0,.15) !important;
}

/* Tombol "Top Up" pada game card hover — cyan */
.game-card-btn {
  border-color: rgba(212,0,0,.7) !important;
  color: #d40000 !important;
}
.game-card:hover .game-card-btn {
  background: rgba(212,0,0,.08) !important;
}

/* Nav active underline — tetap gold (sudah bagus) */
/* Badge dan accent kecil */
.badge-process { 
  background: rgba(255,140,0,.12) !important; 
  color: #ff8c00 !important; 
  border: 1px solid rgba(255,140,0,.25) !important;
}

/* Daftar button di nav2 — gold solid */
.nav2-register {
  background: linear-gradient(135deg,#aa0000,#b8132d) !important;
  color: #ffffff !important;
  border: none !important;
  font-weight: 700 !important;
  box-shadow: 0 2px 10px rgba(255,140,0,.3) !important;
}
.nav2-register:hover {
  filter: brightness(1.1) !important;
  color: #ffffff !important;
  background: linear-gradient(135deg,#d40000,#aa0000) !important;
}

/* IDR selector — subtle gold */
.idr-selector {
  border-color: rgba(212,0,0,.3) !important;
}
.idr-selector:hover {
  border-color: rgba(212,0,0,.5) !important;
  color: var(--redf) !important;
}

/* Topbar brand tag — gold star */
.topbar-brand-tag {
  color: #ff8c00 !important;
}

/* Focus visible — gold ring for accessibility */
*:focus-visible {
  outline-color: #d40000 !important;
}


/* lang selector removed */

/* ══ LEFT SIDEBAR — Dunia Games style ══ */
.dg-sidebar {
  position: fixed;
  top: 102px;
  left: 0;
  width: 80px;
  height: calc(100vh - 102px);
  background: var(--bg-nav);
  border-right: 1px solid var(--b1);
  z-index: 90;
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 8px 0;
  overflow-y: auto;
  overflow-x: hidden;
  scrollbar-width: none;
}
.dg-sidebar::-webkit-scrollbar { display: none; }
/* Admin: wider sidebar */
.admin-layout .dg-sidebar { width: 200px; align-items: flex-start; }
.admin-layout .dg-sb-item { flex-direction: row !important; gap: 12px !important; padding: 11px 18px !important; width: 100%; justify-content: flex-start !important; }
.admin-layout .dg-sb-item.on::before { top: 6px; bottom: 6px; }
.admin-layout .dg-sb-label { font-size: .82rem !important; display: block !important; }
/* Push admin content more */
.admin-layout main.wrap { margin-left: 200px !important; }
.admin-layout .admin-wrap { margin-left: 0 !important; gap: 0 !important; }
.dg-sb-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 5px;
  width: 100%;
  padding: 14px 0;
  text-decoration: none;
  color: var(--t3);
  font-size: .62rem;
  font-weight: 600;
  letter-spacing: .02em;
  transition: all .2s;
  position: relative;
  cursor: pointer;
}
.dg-sb-item:hover { color: var(--t1); background: rgba(255,255,255,.03); }
.dg-sb-item.on {
  color: var(--red);
  background: rgba(227,24,55,.06);
}
.dg-sb-item.on::before {
  content: '';
  position: absolute;
  left: 0; top: 10px; bottom: 10px;
  width: 3px;
  background: var(--red);
  border-radius: 0 3px 3px 0;
}
.dg-sb-item svg { transition: transform .2s; }
.dg-sb-item:hover svg { transform: scale(1.15); }
.dg-sb-item.on svg { color: var(--red); }

/* Push content right when sidebar visible */
@media(min-width: 900px){
  main.wrap, .wrap { margin-left: 80px !important; }
  .gp-wrap, .ck-wrap, .admin-wrap { margin-left: 80px !important; }
}
@media(max-width: 899px){
  .dg-sidebar { display: none; }
}


/* ══════════════════════════════════════════
   ADMIN LAYOUT FIX — Gap & Spacing
   ══════════════════════════════════════════ */

/* Sembunyikan admin-sb lama TOTAL */
.admin-layout .admin-sb,
.admin-wrap .admin-sb,
aside.admin-sb {
  display: none !important;
  width: 0 !important; max-width: 0 !important; min-width: 0 !important;
  flex: 0 0 0 !important; flex-basis: 0 !important;
  padding: 0 !important; margin: 0 !important;
  border: none !important; overflow: hidden !important;
}

/* Admin wrap - flex tanpa gap */
.admin-layout .admin-wrap {
  display: flex !important;
  margin-left: 0 !important;
  gap: 0 !important;
  padding: 0 !important;
  width: 100% !important;
  max-width: 100% !important;
}

/* Admin main - isi semua ruang */
.admin-layout .admin-main {
  flex: 1 1 auto !important;
  min-width: 0 !important;
  width: 100% !important;
  max-width: 100% !important;
  margin: 0 !important;
  padding: 16px 20px 24px !important;
  overflow-y: auto !important;
  box-sizing: border-box !important;
}

/* DG Sidebar admin mode - 200px tepat */
.admin-layout .dg-sidebar {
  width: 200px !important;
  min-width: 200px !important;
  max-width: 200px !important;
  flex-shrink: 0 !important;
}

/* stat-val & kpi-val - pakai Inter bukan Orbitron */
.stat-val, .kpi-val, .stat-card .stat-val {
  font-family: var(--f-body) !important;
  font-variant-numeric: tabular-nums !important;
  letter-spacing: -.01em !important;
}

/* Colored top border per stat card */
.stats-grid .stat-card:nth-child(1) { border-top: 2px solid #e31837 !important; }
.stats-grid .stat-card:nth-child(2) { border-top: 2px solid #3b82f6 !important; }
.stats-grid .stat-card:nth-child(3) { border-top: 2px solid #10b981 !important; }
.stats-grid .stat-card:nth-child(4) { border-top: 2px solid #8b5cf6 !important; }

/* Badge colors yang benar */
.badge-success { background: rgba(16,185,129,.1) !important; color: #34d399 !important; border: 1px solid rgba(16,185,129,.2) !important; }
.badge-pending  { background: rgba(245,158,11,.1) !important; color: #fbbf24 !important; border: 1px solid rgba(245,158,11,.2) !important; }
.badge-process  { background: rgba(59,130,246,.1) !important; color: #60a5fa !important; border: 1px solid rgba(59,130,246,.2) !important; }
.badge-failed   { background: rgba(239,68,68,.1)  !important; color: #f87171 !important; border: 1px solid rgba(239,68,68,.2)  !important; }

/* Modal - dark background */
.modal {
  position: fixed !important;
  inset: 0 !important;
  background: rgba(0,0,0,.72) !important;
  backdrop-filter: blur(6px) !important;
  z-index: 9999 !important;
  display: none !important;
  align-items: center !important;
  justify-content: center !important;
  padding: 20px !important;
}
.modal.show {
  display: flex !important;
}
.modal-box {
  background: #0e1120 !important;
  border: 1px solid rgba(255,255,255,.1) !important;
  border-radius: 14px !important;
  padding: 22px !important;
  width: 100% !important;
  max-width: 520px !important;
  max-height: 90vh !important;
  overflow-y: auto !important;
  box-shadow: 0 24px 64px rgba(0,0,0,.7) !important;
}
.modal-head {
  display: flex !important;
  align-items: center !important;
  justify-content: space-between !important;
  margin-bottom: 16px !important;
  padding-bottom: 12px !important;
  border-bottom: 1px solid rgba(255,255,255,.07) !important;
}
.modal-head h3 { font-size: .92rem !important; font-weight: 700 !important; color: #e8eaf0 !important; }
.modal-close {
  width: 28px !important; height: 28px !important;
  border-radius: 7px !important;
  background: rgba(255,255,255,.05) !important;
  border: 1px solid rgba(255,255,255,.08) !important;
  color: #8892a4 !important;
  font-size: .85rem !important;
  cursor: pointer !important;
  display: flex !important; align-items: center !important; justify-content: center !important;
  transition: all .15s !important;
}
.modal-close:hover { background: rgba(239,68,68,.12) !important; color: #f87171 !important; border-color: rgba(239,68,68,.3) !important; }
.modal-footer {
  display: flex !important; gap: 8px !important;
  margin-top: 16px !important; padding-top: 14px !important;
  border-top: 1px solid rgba(255,255,255,.06) !important;
}

/* Form inputs */
.finput {
  background: #06080f !important;
  border: 1px solid rgba(255,255,255,.1) !important;
  border-radius: 7px !important;
  color: #e8eaf0 !important;
  padding: 8px 12px !important;
  font-size: .83rem !important;
  width: 100% !important;
  transition: border-color .15s, box-shadow .15s !important;
}
.finput:focus {
  outline: none !important;
  border-color: #e31837 !important;
  box-shadow: 0 0 0 3px rgba(227,24,55,.1) !important;
}

/* Table improvements */
.dtable th {
  font-size: .61rem !important;
  text-transform: uppercase !important;
  letter-spacing: .7px !important;
  color: #374155 !important;
  padding: 9px 14px !important;
  font-weight: 700 !important;
}
.dtable td { padding: 9px 14px !important; }
.dtable tbody tr:hover td { background: rgba(227,24,55,.03) !important; }

/* btn-gold admin */
.admin-layout .btn-gold,
.admin-main .btn-gold {
  background: linear-gradient(135deg, #e31837, #b8132d) !important;
  color: #fff !important;
  font-weight: 700 !important;
  box-shadow: 0 2px 10px rgba(227,24,55,.3) !important;
  border: none !important;
  padding: 8px 18px !important;
  border-radius: 7px !important;
  font-size: .82rem !important;
  cursor: pointer !important;
  transition: all .2s !important;
  display: inline-flex !important;
  align-items: center !important;
  gap: 6px !important;
}
.admin-layout .btn-gold:hover,
.admin-main .btn-gold:hover {
  filter: brightness(1.1) !important;
  transform: translateY(-1px) !important;
  box-shadow: 0 4px 16px rgba(227,24,55,.4) !important;
}

</style>

<!-- Init lang dari cookie/localStorage sebelum render -->
<script>
(function(){
  var htmlEl = document.documentElement;
  var savedLang = htmlEl.getAttribute('data-lang') || localStorage.getItem('ftara_lang') || 'ID';
  if(savedLang !== 'ID') htmlEl.setAttribute('data-lang', savedLang);
  localStorage.setItem('ftara_lang', savedLang);
})();
</script>
<script>
// ── Simple ID/EN Language Toggle ────────────────────────────────
var _currentLang = localStorage.getItem('ftara_lang') || 'ID';

function toggleLangSimple(){
  _currentLang = _currentLang === 'ID' ? 'EN' : 'ID';
  localStorage.setItem('ftara_lang', _currentLang);
  document.cookie = 'ftara_lang='+_currentLang+';path=/;max-age=31536000;SameSite=Lax';
  var cur = document.getElementById('lang-current');
  var oth = document.getElementById('lang-other');
  if(cur) cur.textContent = _currentLang;
  if(oth) oth.textContent = _currentLang === 'ID' ? 'EN' : 'ID';
  applyTranslation(_currentLang);
}

var i18n = {
  ID: {
    search:'Cari Game atau Voucher...',
    topup:'Topup', cektx:'Cek Transaksi', lb:'Leaderboard', kalc:'Kalkulator',
    masuk:'Masuk', daftar:'Daftar', admin:'Admin', keluar:'Keluar',
    popular:'POPULER SEKARANG', allgame:'SEMUA PRODUK', catall:'Semua',
    hero_instant:'Proses Instan', hero_safe:'Terjamin Aman', hero_support:'24/7 Support',
    btn_topup:'Top Up', btn_cek:'Cek Status', btn_bayar:'Bayar Sekarang',
    promo:'Promo', explore:'Explore', reward:'Reward', profile:'Profile',
    // Sidebar
    dashboard:'Dashboard', produk:'Produk', transaksi:'Transaksi',
    laporan:'Laporan', pengaturan:'Pengaturan', bantuan:'Bantuan',
    // Game page
    step1:'Masukkan ID Akun Game', step2:'Pilih Nominal', step3:'Detail Kontak',
    user_id:'User ID', server_id:'Server ID', email:'Email',
    ringkasan:'Ringkasan Order', total:'Total', nominal:'Nominal',
    // Cek transaksi
    cek_title:'Cek Transaksi', cek_sub:'Masukkan kode order untuk melihat status top up kamu.',
    kode_order:'Kode Order', cek_btn:'Cek Status', riwayat:'Riwayat Transaksi',
    // Status
    menunggu:'Menunggu Bayar', berhasil:'Berhasil', gagal:'Gagal', diproses:'Diproses',
    // Footer
    platform:'Platform top up game yang aman, murah dan terpercaya.',
    peta_situs:'Peta Situs', dukungan:'Dukungan', legalitas:'Legalitas',
    kebijakan:'Kebijakan Privasi', syarat:'Syarat & Ketentuan',
    beranda:'Beranda', tentang:'Tentang Kami', kontak:'Kontak',
    // Profile
    profil_saya:'Profil Saya', transaksi_saya:'Transaksi Saya', pengaturan_akun:'Pengaturan Akun',
    keluar_akun:'Keluar dari Akun'
  },
  EN: {
    search:'Search Game or Voucher...',
    topup:'Top Up', cektx:'Check Transaction', lb:'Leaderboard', kalc:'Calculator',
    masuk:'Sign In', daftar:'Sign Up', admin:'Admin', keluar:'Sign Out',
    popular:'POPULAR NOW', allgame:'ALL PRODUCTS', catall:'All',
    hero_instant:'Instant Process', hero_safe:'Trusted & Safe', hero_support:'24/7 Support',
    btn_topup:'Top Up', btn_cek:'Check Status', btn_bayar:'Pay Now',
    promo:'Promo', explore:'Explore', reward:'Reward', profile:'Profile',
    // Sidebar
    dashboard:'Dashboard', produk:'Products', transaksi:'Transactions',
    laporan:'Reports', pengaturan:'Settings', bantuan:'Help',
    // Game page
    step1:'Enter Game Account ID', step2:'Select Nominal', step3:'Contact Details',
    user_id:'User ID', server_id:'Server ID', email:'Email',
    ringkasan:'Order Summary', total:'Total', nominal:'Nominal',
    // Cek transaksi
    cek_title:'Check Transaction', cek_sub:'Enter your order code to check top up status.',
    kode_order:'Order Code', cek_btn:'Check Status', riwayat:'Transaction History',
    // Status
    menunggu:'Pending Payment', berhasil:'Successful', gagal:'Failed', diproses:'Processing',
    // Footer
    platform:'Fast, safe and affordable game top up platform.',
    peta_situs:'Sitemap', dukungan:'Support', legalitas:'Legal',
    kebijakan:'Privacy Policy', syarat:'Terms & Conditions',
    beranda:'Home', tentang:'About Us', kontak:'Contact',
    // Profile
    profil_saya:'My Profile', transaksi_saya:'My Transactions', pengaturan_akun:'Account Settings',
    keluar_akun:'Sign Out'
  }
};

function applyTranslation(code){
  var t = i18n[code] || i18n['ID'];

  // Helper: translate last text node of element
  function setLastText(el, val){
    var last = el.lastChild;
    if(last && last.nodeType === 3) last.nodeValue = val;
    else el.appendChild(document.createTextNode(val));
  }

  // Search placeholders
  document.querySelectorAll('.search-bar input, input[placeholder*="Voucher"], input[placeholder*="Game"]').forEach(function(el){
    el.placeholder = t.search;
  });

  // Nav links by href
  // Translate only data-i18n spans (no duplication)
  document.querySelectorAll('[data-i18n]').forEach(function(el){
    var key = el.getAttribute('data-i18n');
    if(t[key]) el.textContent = t[key];
  });

  // Auth buttons
  document.querySelectorAll('.nav2-auth-link').forEach(function(el){
    var href = el.getAttribute('href')||'';
    var span = el.querySelector('[data-i18n]');
    if(span) return; // already handled by data-i18n
    var last = el.lastChild;
    if(!last||last.nodeType!==3) return;
    if(href.match(/login/))    last.nodeValue=' '+t.masuk;
    else if(href.match(/reg/)) last.nodeValue=' '+t.daftar;
  });

  // Dropdown keluar
  document.querySelectorAll('.nav-dd-logout').forEach(function(el){
    var span = el.querySelector('[data-i18n]');
    if(span) return;
    var last = el.lastChild;
    if(last&&last.nodeType===3) last.nodeValue=' '+t.keluar;
  });
}

// Apply on load
document.addEventListener('DOMContentLoaded', function(){
  var saved = localStorage.getItem('ftara_lang') || 'ID';
  _currentLang = saved;
  var cur=document.getElementById('lang-current');
  var oth=document.getElementById('lang-other');
  if(cur) cur.textContent = saved;
  if(oth) oth.textContent = saved==='ID'?'EN':'ID';
  if(saved!=='ID') applyTranslation(saved);

  // Also apply sidebar label translations
  document.querySelectorAll('[data-i18n-sb]').forEach(function(el){
    var key=el.getAttribute('data-i18n-sb');
    if(i18n[saved]&&i18n[saved][key]) el.textContent=i18n[saved][key];
  });
});

// ── Notification Bell ───────────────────────────────────────────
window.toggleNotif = function(e){
  e.stopPropagation();
  var dd = document.getElementById('notif-dropdown');
  if(dd) dd.style.display = dd.style.display==='none'?'block':'none';
};
document.addEventListener('click', function(){
  var dd = document.getElementById('notif-dropdown');
  if(dd) dd.style.display='none';
});

// ── Avatar Dropdown ─────────────────────────────────────────────
(function(){
  var btn = document.getElementById('nav-avatar-btn');
  var dd  = document.getElementById('nav-avatar-dropdown');
  if(!btn || !dd) return;
  btn.addEventListener('click', function(e){
    e.stopPropagation();
    var isOpen = dd.classList.toggle('open');
    btn.classList.toggle('open', isOpen);
  });
  document.addEventListener('click', function(){
    if(dd) dd.classList.remove('open');
    if(btn) btn.classList.remove('open');
  });
  if(dd) dd.addEventListener('click', function(e){ e.stopPropagation(); });
  document.addEventListener('keydown', function(e){
    if(e.key==='Escape'){
      if(dd) dd.classList.remove('open');
      if(btn) btn.classList.remove('open');
    }
  });
})();

</script>
</head>
<?php if($inAdmin??false): ?><body class="admin-layout"><?php else: ?><body><?php endif; ?>
<?php if($flash): ?>
<div class="flash flash--<?=$flash['type']?>" id="flash-msg">
  <span><?=htmlspecialchars($flash['msg'])?></span>
  <button onclick="this.parentElement.remove()">✕</button>
</div>
<?php endif; ?>

<header class="hdr">
  <!-- ── Top bar (SEAGM-style) ── -->
  <!-- ── Main navbar ── -->
  <div class="hdr-top">
    <a href="<?=asset('index.php')?>" class="logo" id="site-logo">
      <div class="logo-words">
        <div class="logo-name">ftar<span class="logo-a">a</span><span class="logo-store">store</span></div>
      </div>
    </a>

    <!-- Search bar — full width middle -->
    <form action="<?=asset('pages/search.php')?>" method="GET" class="search-bar">
      <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
      <input type="text" name="q" placeholder="Cari Game atau Voucher..." autocomplete="off" value="<?=htmlspecialchars($_GET['q']??'')?>"/>
    </form>
    <button class="mob-search-btn" id="mob-search-btn" aria-label="Cari">
      <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
    </button>
    <div class="mob-search-overlay" id="mob-search-overlay">
      <form action="<?=asset('pages/search.php')?>" method="GET" class="search-bar">
        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
        <input type="text" name="q" placeholder="Cari Game atau Voucher..." autocomplete="off" autofocus value="<?=htmlspecialchars($_GET['q']??'')?>"/>
      </form>
      <button class="mob-search-cancel" id="mob-search-cancel">Batal</button>
    </div>

    <div class="hdr-actions">
      <!-- Simple ID/EN toggle -->
      <button id="lang-toggle-btn" onclick="toggleLangSimple()" style="
        display:inline-flex;align-items:center;gap:6px;
        background:var(--card);border:1.5px solid var(--b2);border-radius:8px;
        padding:6px 12px;cursor:pointer;font-size:.8rem;font-weight:700;
        color:var(--t2);transition:all .2s;white-space:nowrap;
      " onmouseover="this.style.borderColor='var(--red)';this.style.color='var(--t1)'"
         onmouseout="this.style.borderColor='var(--b2)';this.style.color='var(--t2)'">
        <span id="lang-current">ID</span>
        <span style="opacity:.4;font-weight:400;">|</span>
        <span id="lang-other" style="opacity:.5;">EN</span>
      </button>

      <?php /* avatar in nav2 row */ ?>
      <!-- Hamburger mobile -->
      <button class="mob-menu-btn" id="mob-menu-btn" aria-label="Menu">
        <span></span><span></span><span></span>
      </button>
    </div>
  </div>
  <!-- ── Row 2: Nav links + Auth (Ourastore style) ── -->
  <div style="background:var(--bg-nav);border-top:1px solid var(--b1);">
    <div class="hdr-nav2">
      <div class="hdr-nav2-left">
        <?php $cp = basename($_SERVER['PHP_SELF']); ?>
        <?php if(!$inAdmin): ?>
        <?php if(!$inAdmin): ?>
        <a href="<?=asset('pages/cek-transaksi.php')?>" class="nav-link<?=$cp==='cek-transaksi.php'?' on':''?>">
          <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
          <span data-i18n="cektx">Cek Transaksi</span>
        </a>
        <?php endif; ?>
        <a href="<?=asset('pages/leaderboard.php')?>" class="nav-link<?=$cp==='leaderboard.php'?' on':''?>">
          <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
          <span data-i18n="lb">Leaderboard</span>
        </a>
        <a href="<?=asset('pages/kalkulator.php')?>" class="nav-link<?=$cp==='kalkulator.php'?' on':''?>">
          <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="4" y="2" width="16" height="20" rx="2"/><line x1="8" y1="6" x2="16" y2="6"/><line x1="8" y1="12" x2="10" y2="12"/><line x1="14" y1="12" x2="16" y2="12"/></svg>
          <span data-i18n="kalc">Kalkulator</span>
        </a>
        <?php endif; // end !inAdmin — semua nav link disembunyikan saat di admin ?>
        <?php if(isAdmin()): ?>
        <span style="font-size:.78rem;color:var(--t3);padding:0 8px;font-weight:600;letter-spacing:.05em;">ADMIN PANEL</span>
        <?php endif; ?>
      </div>
      <div class="hdr-nav2-right">
        <?php if(isLoggedIn()): ?>
        <!-- User logged in: show avatar dropdown in nav2 -->
        <!-- Notification Bell -->
        <div style="position:relative;" id="notif-wrap">
          <button id="notif-btn" onclick="toggleNotif(event)" style="width:36px;height:36px;border-radius:50%;background:var(--card);border:1px solid var(--b2);display:flex;align-items:center;justify-content:center;cursor:pointer;color:var(--t2);transition:all .2s;position:relative;" title="Notifikasi">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
          </button>
          <!-- Notification dropdown -->
          <div id="notif-dropdown" style="display:none;position:absolute;top:calc(100%+8px);right:0;width:320px;background:var(--card);border:1.5px solid var(--b2);border-radius:14px;box-shadow:0 12px 40px rgba(0,0,0,.5);z-index:9999;overflow:hidden;">
            <div style="padding:14px 18px;background:var(--card2);border-bottom:1px solid var(--b1);display:flex;justify-content:space-between;align-items:center;">
              <span style="font-weight:700;font-size:.9rem;color:var(--t1);">Notifikasi</span>
            </div>
            <div style="padding:40px 20px;text-align:center;color:var(--t3);">
              <svg width="36" height="36" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="display:block;margin:0 auto 12px;opacity:.3;"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
              <p style="font-size:.82rem;">Belum ada notifikasi</p>
            </div>
          </div>
        </div>
        <div class="nav-avatar-wrap" id="nav-avatar-wrap">
          <button class="nav-avatar-btn" id="nav-avatar-btn" aria-label="Menu akun">
            <div class="nav-avatar-circle"><?=strtoupper(substr(currentUser()['name']??'U',0,1))?></div>
            <span class="nav-avatar-name"><?=htmlspecialchars(explode(' ', currentUser()['name']??'Akun')[0])?></span>
            <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" class="nav-avatar-caret"><path d="m6 9 6 6 6-6"/></svg>
          </button>
          <div class="nav-avatar-dropdown" id="nav-avatar-dropdown">
            <div class="nav-dd-header">
              <div class="nav-dd-avatar"><?=strtoupper(substr(currentUser()['name']??'U',0,1))?></div>
              <div class="nav-dd-info">
                <div class="nav-dd-name"><?=htmlspecialchars(currentUser()['name']??'User')?></div>
                <div class="nav-dd-role"><?=isAdmin()?'Administrator':'Member'?></div>
              </div>
            </div>
            <div class="nav-dd-divider"></div>
            <?php if(!$inAdmin): ?>
            <a href="<?=asset('pages/cek-transaksi.php')?>" class="nav-dd-item">
              <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
              Riwayat Transaksi
            </a>
            <a href="<?=asset('pages/profile.php')?>" class="nav-dd-item">
              <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M20 21a8 8 0 1 0-16 0"/></svg>
              Profil Saya
            </a>
            <?php else: ?>
            <a href="<?=asset('admin/index.php')?>" class="nav-dd-item">
              <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
              Dashboard Admin
            </a>
            <a href="<?=asset('admin/settings.php')?>" class="nav-dd-item">
              <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
              Pengaturan
            </a>
            <?php endif; ?>
            <a href="<?=asset('pages/faq.php')?>" class="nav-dd-item">
              <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
              Bantuan
            </a>
            <div class="nav-dd-divider"></div>
            <a href="<?=asset('auth/logout.php')?>" class="nav-dd-item nav-dd-logout">
              <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
              <span data-i18n="keluar">Keluar</span>
            </a>
          </div>
        </div>
        <?php else: ?>
        <a href="<?=asset('auth/login.php')?>" class="nav2-auth-link">
          <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
          Masuk
        </a>
        <a href="<?=asset('auth/register.php')?>" class="nav2-auth-link nav2-register">
          <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
          Daftar
        </a>
        <?php endif; ?>
      </div>
    </div>
  </div>

</header>

<!-- ══ LEFT SIDEBAR — DG Style ══ -->
<nav class="dg-sidebar" id="dg-sidebar">
  <?php $cp = basename($_SERVER['PHP_SELF']); $inAdmin = strpos($_SERVER['SCRIPT_FILENAME']??'','/admin/')!==false; ?>

  <a href="<?=asset('index.php')?>" class="dg-sb-item <?=(!$inAdmin&&in_array($cp,['index.php','game.php']))?'on':''?>">
    <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><rect x="2" y="6" width="20" height="12" rx="3"/><path d="M6 12h4m-2-2v4M15 12h.01M18 12h.01"/></svg>
    <span class="dg-sb-label" data-i18n-sb="topup">Top Up</span>
  </a>

  <?php if(!$inAdmin): ?>
  <a href="<?=asset('pages/promo.php')?>" class="dg-sb-item <?=(!$inAdmin&&$cp==='promo.php')?'on':''?>">
    <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M20 12v10H4V12"/><path d="M22 7H2v5h20V7z"/><path d="M12 22V7m-3-4h6"/></svg>
    <span class="dg-sb-label" data-i18n-sb="promo">Promo</span>
  </a>
  <a href="<?=asset('pages/explore.php')?>" class="dg-sb-item <?=(!$inAdmin&&$cp==='explore.php')?'on':''?>">
    <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
    <span class="dg-sb-label" data-i18n-sb="explore">Explore</span>
  </a>
  <a href="<?=asset('pages/reward.php')?>" class="dg-sb-item <?=(!$inAdmin&&$cp==='reward.php')?'on':''?>">
    <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><polyline points="20 12 20 22 4 22 4 12"/><rect x="2" y="7" width="20" height="5"/><line x1="12" y1="22" x2="12" y2="7"/><path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"/><path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"/></svg>
    <span class="dg-sb-label" data-i18n-sb="reward">Reward</span>
  </a>
  <?php endif; ?>

  <?php if($inAdmin): ?>
  <!-- Admin menu - expanded width -->
  <a href="<?=asset('admin/index.php')?>" class="dg-sb-item <?=$inAdmin&&$cp==='index.php'?'on':''?>">
    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
    <span class="dg-sb-label">Dashboard</span>
  </a>
  <a href="<?=asset('admin/transactions.php')?>" class="dg-sb-item <?=$inAdmin&&$cp==='transactions.php'?'on':''?>">
    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
    <span class="dg-sb-label">Transaksi</span>
  </a>
  <div style="width:calc(100%-16px);height:1px;background:var(--b1);margin:4px 8px;flex-shrink:0;"></div>
  <a href="<?=asset('admin/games.php')?>" class="dg-sb-item <?=$inAdmin&&$cp==='games.php'?'on':''?>">
    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><rect x="2" y="6" width="20" height="12" rx="3"/><path d="M6 12h4m-2-2v4M15 12h.01M18 12h.01"/></svg>
    <span class="dg-sb-label">Produk</span>
  </a>
  <a href="<?=asset('admin/sync-products.php')?>" class="dg-sb-item <?=$inAdmin&&$cp==='sync-products.php'?'on':''?>">
    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M21 2v6h-6"/><path d="M3 12a9 9 0 0 1 15-6.7L21 8"/><path d="M3 22v-6h6"/><path d="M21 12a9 9 0 0 1-15 6.7L3 16"/></svg>
    <span class="dg-sb-label">Sync Produk Digi</span>
  </a>
  <a href="<?=asset('admin/categories.php')?>" class="dg-sb-item <?=$inAdmin&&$cp==='categories.php'?'on':''?>">
    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
    <span class="dg-sb-label">Kategori</span>
  </a>
  <a href="<?=asset('admin/banners.php')?>" class="dg-sb-item <?=$inAdmin&&$cp==='banners.php'?'on':''?>">
    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>
    <span class="dg-sb-label">Banner</span>
  </a>
  <a href="<?=asset('admin/explore.php')?>" class="dg-sb-item <?=$inAdmin&&$cp==='explore.php'?'on':''?>">
    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
    <span class="dg-sb-label">Kelola Explore</span>
  </a>
  <a href="<?=asset('admin/promo.php')?>" class="dg-sb-item <?=$inAdmin&&$cp==='promo.php'?'on':''?>">
    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M20 12v10H4V12"/><path d="M2 7h20v5H2z"/><path d="M12 22V7"/><path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"/><path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"/></svg>
    <span class="dg-sb-label">Kelola Promo &amp; Event</span>
  </a>
  <a href="<?=asset('admin/rewards.php')?>" class="dg-sb-item <?=$inAdmin&&$cp==='rewards.php'?'on':''?>">
    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><circle cx="12" cy="8" r="6"/><path d="M15.477 12.89 17 22l-5-3-5 3 1.523-9.11"/></svg>
    <span class="dg-sb-label">Kelola Reward</span>
  </a>
  <div style="width:calc(100%-16px);height:1px;background:var(--b1);margin:4px 8px;flex-shrink:0;"></div>
  <a href="<?=asset('admin/reports.php')?>" class="dg-sb-item <?=$inAdmin&&$cp==='reports.php'?'on':''?>">
    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
    <span class="dg-sb-label">Laporan</span>
  </a>
  <?php if(currentRole()==='super_admin'): ?>
  <a href="<?=asset('admin/admins.php')?>" class="dg-sb-item <?=$inAdmin&&$cp==='admins.php'?'on':''?>">
    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
    <span class="dg-sb-label">Admin</span>
  </a>
  <a href="<?=asset('admin/users.php')?>" class="dg-sb-item <?=$inAdmin&&$cp==='users.php'?'on':''?>">
    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
    <span class="dg-sb-label">Users</span>
  </a>
  <a href="<?=asset('admin/security.php')?>" class="dg-sb-item <?=$inAdmin&&$cp==='security.php'?'on':''?>">
    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
    <span class="dg-sb-label">Keamanan</span>
  </a>
  <?php endif; ?>
  <a href="<?=asset('admin/settings.php')?>" class="dg-sb-item <?=$inAdmin&&$cp==='settings.php'?'on':''?>">
    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
    <span class="dg-sb-label">Pengaturan</span>
  </a>
  <?php endif; ?>
<?php if(!$inAdmin && isAdmin()): ?>
<div style="width:100%;height:1px;background:var(--b1);margin:4px 0;flex-shrink:0;"></div>
<a href="<?=asset('admin/index.php')?>" class="dg-sb-item">
  <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
  <span class="dg-sb-label" style="color:var(--gold);">Admin</span>
</a>
<?php endif; ?>
  <div style="flex:1;min-height:16px;"></div>

  <!-- Profile bottom -->
  <?php if(isLoggedIn()): ?>
  <a href="<?=isAdmin()?asset('admin/index.php'):asset('pages/profile.php')?>" class="dg-sb-item <?=(!$inAdmin&&$cp==='profile.php')?'on':''?>" style="padding-bottom:20px;">
    <div style="width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,var(--red),var(--red2));color:#fff;font-weight:800;font-size:.85rem;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
      <?=strtoupper(substr(currentUser()['name']??'U',0,1))?>
    </div>
    <span class="dg-sb-label">Profile</span>
  </a>
  <?php else: ?>
  <a href="<?=asset('auth/login.php')?>" class="dg-sb-item" style="padding-bottom:20px;">
    <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
    <span class="dg-sb-label">Profile</span>
  </a>
  <?php endif; ?>
</nav>

<!-- ── Mobile Nav Drawer ── -->
<div class="mob-nav-drawer" id="mob-nav-drawer">
  <div class="mob-nav-overlay" id="mob-nav-overlay"></div>
  <div class="mob-nav-panel">
    <div class="mob-nav-header">
      <div class="mob-nav-logo">ftar<span class="la">a</span><span class="ls">store</span></div>
      <button class="mob-nav-close" id="mob-nav-close">✕</button>
    </div>
    <div class="mob-nav-links">
      <?php $cp = basename($_SERVER["PHP_SELF"]); ?>
      <?php if(!$inAdmin): ?>
      <span class="mob-nav-label">Menu</span>
      <a href="<?=asset("index.php")?>" class="<?=in_array($cp,["index.php","game.php"])?"on":""?>">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="2" y="6" width="20" height="12" rx="3"/><path d="M6 12h4m-2-2v4M15 12h.01M18 12h.01"/></svg>
        Topup
      </a>
      <a href="<?=asset("pages/cek-transaksi.php")?>" class="<?=$cp==="cek-transaksi.php"?"on":""?>">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
        Cek Transaksi
      </a>
      <?php endif; ?>
      <a href="<?=asset("pages/leaderboard.php")?>" class="<?=$cp==="leaderboard.php"?"on":""?>">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
        Leaderboard
      </a>
      <a href="<?=asset("pages/kalkulator.php")?>" class="<?=$cp==="kalkulator.php"?"on":""?>">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="4" y="2" width="16" height="20" rx="2"/><line x1="8" y1="6" x2="16" y2="6"/><line x1="8" y1="12" x2="10" y2="12"/><line x1="8" y1="16" x2="10" y2="16"/><line x1="14" y1="12" x2="16" y2="12"/><line x1="14" y1="16" x2="16" y2="16"/></svg>
        Kalkulator
      </a>
      <?php if(isAdmin()): ?>
      <div class="mob-nav-divider"></div>
      <span class="mob-nav-label">Admin</span>
      <a href="<?=asset("admin/index.php")?>">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
        Dashboard Admin
      </a>
      <?php endif; ?>
    </div>
    <div class="mob-nav-actions">
      <?php if(isLoggedIn()): ?>
        <a href="<?=asset("auth/logout.php")?>" class="mob-btn-ghost">
          <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
          Keluar
        </a>
      <?php else: ?>
        <a href="<?=asset("auth/login.php")?>" class="mob-btn-ghost">Masuk</a>
        <a href="<?=asset("auth/register.php")?>" class="mob-btn-primary">Daftar</a>
      <?php endif; ?>
    </div>
  </div>
</div>


<!-- ══ BOTTOM NAV — Mobile DG Style ══ -->
<?php if(!$inAdmin): ?>
<nav class="mob-bottom-nav" id="mob-bottom-nav">
  <?php $cp = basename($_SERVER['PHP_SELF']); ?>
  <a href="<?=asset('index.php')?>" class="mob-bn-item <?=in_array($cp,['index.php','game.php'])?'on':''?>">
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="2" y="6" width="20" height="12" rx="3"/><path d="M6 12h4m-2-2v4M15 12h.01M18 12h.01"/></svg>
    Top Up
  </a>
  <a href="<?=asset('pages/promo.php')?>" class="mob-bn-item <?=$cp==='promo.php'?'on':''?>">
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M20 12v10H4V12"/><path d="M22 7H2v5h20V7z"/><path d="M12 22V7"/></svg>
    Promo
  </a>
  <a href="<?=asset('pages/explore.php')?>" class="mob-bn-item <?=$cp==='explore.php'?'on':''?>">
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
    Explore
  </a>
  <a href="<?=asset('pages/reward.php')?>" class="mob-bn-item <?=$cp==='reward.php'?'on':''?>">
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><polyline points="20 12 20 22 4 22 4 12"/><rect x="2" y="7" width="20" height="5"/><line x1="12" y1="22" x2="12" y2="7"/><path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"/><path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"/></svg>
    Reward
  </a>
  <a href="<?=isLoggedIn()?asset('pages/profile.php'):asset('auth/login.php')?>" class="mob-bn-item <?=$cp==='profile.php'?'on':''?>">
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
    Profile
  </a>
</nav>
<?php endif; ?>

<main class="wrap">

<script>
(function(){
  /* ── Logo animation ── */
  var logo = document.getElementById('site-logo');
  if(logo){
    var mid = logo.querySelector('.hex-mid');
    logo.addEventListener('mouseenter', function(){ if(mid) mid.style.opacity='1'; });
    logo.addEventListener('mouseleave', function(){ if(mid) mid.style.opacity='0.3'; });
  }



  /* ── Mobile hamburger menu ── */
  var menuBtn  = document.getElementById('mob-menu-btn');
  var drawer   = document.getElementById('mob-nav-drawer');
  var overlay  = document.getElementById('mob-nav-overlay');
  var closeBtn = document.getElementById('mob-nav-close');

  function openDrawer(){
    if(!drawer) return;
    drawer.classList.add('open');
    if(menuBtn) menuBtn.classList.add('open');
    document.body.style.overflow = 'hidden';
  }
  function closeDrawer(){
    if(!drawer) return;
    drawer.classList.remove('open');
    if(menuBtn) menuBtn.classList.remove('open');
    document.body.style.overflow = '';
  }
  if(menuBtn)  menuBtn.addEventListener('click', openDrawer);
  if(closeBtn) closeBtn.addEventListener('click', closeDrawer);
  if(overlay)  overlay.addEventListener('click', closeDrawer);
  document.addEventListener('keydown', function(e){ if(e.key==='Escape') closeDrawer(); });

  /* ── Mobile search overlay ── */
  var searchBtn     = document.getElementById('mob-search-btn');
  var searchOverlay = document.getElementById('mob-search-overlay');
  var searchCancel  = document.getElementById('mob-search-cancel');

  if(searchBtn) searchBtn.addEventListener('click', function(){
    if(!searchOverlay) return;
    searchOverlay.classList.add('open');
    var inp = searchOverlay.querySelector('input');
    if(inp) setTimeout(function(){ inp.focus(); }, 60);
  });
  if(searchCancel) searchCancel.addEventListener('click', function(){
    if(searchOverlay) searchOverlay.classList.remove('open');
  });
})();
</script>