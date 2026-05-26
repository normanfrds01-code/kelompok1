<?php
require_once __DIR__."/../includes/functions.php";
$slug = $_GET["slug"]??"";
$game = getGameBySlug($slug);
if(!$game){ header("Location: ".asset("index.php")); exit; }
$products = getProductsByGame($game["id"]);
$pageTitle = "Top Up ".$game["name"]." — ".siteName();

// Group produk by category/prefix
$prodGroups = [];
foreach($products as $p){
    $grp = $p["category"]??$p["group_name"]??"Semua";
    $prodGroups[$grp][] = $p;
}
if(count($prodGroups)===1) $prodGroups = ["Semua"=>array_values($products)];

// Game lainnya
$otherGames = [];
try{
    $og=db()->prepare("SELECT id,name,slug,image_url FROM games WHERE is_active=1 AND id!=? AND category_id=? LIMIT 6");
    $og->execute([$game["id"],$game["category_id"]]); $otherGames=$og->fetchAll();
}catch(\Exception $e){}

// Voucher aktif
$activeVouchers=[];
try{
    $vs=db()->query("SELECT code,type,value,description FROM vouchers WHERE is_active=1 AND (expires_at IS NULL OR expires_at>NOW()) AND used_count<quota ORDER BY value DESC LIMIT 8");
    $activeVouchers=$vs->fetchAll();
}catch(\Exception $e){}

include __DIR__."/../includes/header.php";
?>
<style>
/* ═══ GAME PAGE — Dunia Games inspired ═══ */
.gp-wrap{max-width:1100px;margin:0 auto;padding:0 20px 60px}

/* Hero */
.gp-hero{position:relative;overflow:hidden;background:#07080f;height:280px}
.gp-hero-bg{position:absolute;inset:0;background-size:cover;background-position:center 20%;filter:blur(0px) brightness(.55) saturate(.8);transform:scale(1.02);transition:filter .3s}
.gp-hero-vignette{position:absolute;inset:0;background:linear-gradient(90deg,rgba(6,13,28,.95) 30%,rgba(6,13,28,.3) 70%,rgba(6,13,28,.1) 100%)}
.gp-hero-bottom{position:absolute;bottom:0;left:0;right:0;height:80px;background:linear-gradient(to top,var(--bg),transparent)}
.gp-hero-inner{position:relative;z-index:2;max-width:1100px;margin:0 auto;padding:0 20px;height:100%;display:flex;flex-direction:column;justify-content:flex-end;padding-bottom:24px}
.gp-bc{display:flex;align-items:center;gap:5px;font-size:.74rem;color:rgba(255,255,255,.4);margin-bottom:20px;padding-top:18px}
.gp-bc a{color:rgba(255,255,255,.4);text-decoration:none}
.gp-bc a:hover{color:rgba(255,255,255,.7)}
.gp-bc-sep{color:rgba(255,255,255,.2)}
.gp-hero-content{display:flex;align-items:center;gap:18px}
.gp-hero-logo{width:80px;height:80px;border-radius:16px;object-fit:cover;border:2px solid rgba(255,255,255,.2);box-shadow:0 8px 32px rgba(0,0,0,.7);flex-shrink:0}
.gp-hero-name{font-family:var(--f-display);font-size:2rem;font-weight:800;color:#fff;letter-spacing:-.5px;line-height:1;text-shadow:0 2px 20px rgba(0,0,0,.8)}
.gp-hero-pub{font-size:.8rem;color:rgba(255,255,255,.45);margin-top:5px}
.gp-hero-badges{display:flex;gap:8px;margin-top:10px;flex-wrap:wrap}
.gp-badge{display:inline-flex;align-items:center;gap:5px;font-size:.7rem;font-weight:600;padding:3px 9px;border-radius:20px;background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.1);color:rgba(255,255,255,.55)}
.gp-badge.b-cyan{color:#d40000;border-color:rgba(212,0,0,.3);background:rgba(212,0,0,.08)}
.gp-badge.b-green{color:#34d399;border-color:rgba(52,211,153,.3);background:rgba(52,211,153,.07)}

/* Layout */
.gp-layout{display:grid;grid-template-columns:1fr 310px;gap:20px;align-items:start;padding-top:24px}

/* Product Section */
.gp-section{margin-bottom:6px}
.gp-section-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:10px}
.gp-section-title{font-weight:700;font-size:.9rem;color:var(--t1);display:flex;align-items:center;gap:8px}
.gp-count{display:inline-flex;align-items:center;justify-content:center;background:var(--card2);border:1px solid var(--b2);color:var(--t3);font-size:.7rem;font-weight:700;padding:2px 8px;border-radius:12px;min-width:22px}

/* Product Tabs */
.prod-tabs{display:flex;gap:6px;flex-wrap:wrap;margin-bottom:18px}
.prod-tab{padding:7px 16px;border-radius:8px;font-size:.8rem;font-weight:600;border:1.5px solid var(--b2);color:var(--t2);background:var(--card);cursor:pointer;transition:all .2s;white-space:nowrap}
.prod-tab:hover{border-color:rgba(212,0,0,.4);color:var(--t1)}
.prod-tab.on{background:rgba(212,0,0,.1);border-color:#d40000;color:#ff5566}

/* Product Grid */
.prod-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:10px;margin-bottom:20px}
.prod-card{background:var(--card2);border:1.5px solid var(--b1);border-radius:12px;padding:14px;cursor:pointer;transition:all .2s;position:relative;min-height:90px;display:flex;flex-direction:column;gap:4px}
.prod-card:hover{border-color:rgba(227,24,55,.45);background:rgba(227,24,55,.04);transform:translateY(-1px)}
.prod-card.sel{border-color:#aa0000;background:rgba(227,24,55,.08);box-shadow:0 0 0 3px rgba(212,0,0,.15)}
.prod-card.sel::after{content:"✓";position:absolute;top:8px;right:9px;width:20px;height:20px;background:#aa0000;color:white;border-radius:50%;font-size:.72rem;font-weight:800;display:flex;align-items:center;justify-content:center;line-height:1}
.pc-ico{width:28px;height:28px;border-radius:6px;object-fit:cover;margin-bottom:4px;opacity:.7}
.pc-name{font-weight:700;font-size:.82rem;color:var(--t1);line-height:1.3;padding-right:22px}
.pc-desc{font-size:.67rem;color:var(--t3);line-height:1.3}
.pc-price{font-weight:800;font-size:.95rem;color:#ff8c00;margin-top:auto;padding-top:6px}
.prod-card.sel .pc-price{color:#ffaa33}
.pc-badge-wrap{position:absolute;top:0;left:0;right:0;display:flex;justify-content:flex-end}
.pc-flash{background:linear-gradient(135deg,#ef4444,#dc2626);color:white;font-size:.6rem;font-weight:700;padding:2px 7px;border-radius:0 10px 0 8px;letter-spacing:.3px}
.pc-limited{background:linear-gradient(135deg,#7c3aed,#6d28d9);color:white;font-size:.6rem;font-weight:700;padding:2px 7px;border-radius:0 10px 0 8px;letter-spacing:.3px}

/* User ID + Detail input */
.gp-card{background:var(--card);border:1.5px solid var(--b1);border-radius:14px;overflow:hidden;margin-bottom:14px}
.gp-card-head{display:flex;align-items:center;gap:10px;padding:12px 18px;background:var(--card2);border-bottom:1px solid var(--b1)}
.gp-step-num{width:24px;height:24px;border-radius:6px;background:linear-gradient(135deg,#aa0000,#b8132d);color:white;font-weight:800;font-size:.78rem;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.gp-card-title{font-weight:700;font-size:.88rem;color:var(--t1)}
.gp-card-body{padding:16px 18px}
.gp-label{display:block;font-size:.77rem;font-weight:600;color:var(--t2);margin-bottom:5px}

/* Right panel */
.sum-panel{background:var(--card);border:1.5px solid var(--b1);border-radius:14px;overflow:hidden;position:sticky;top:76px}
.sum-ph{padding:14px 18px;background:var(--card2);border-bottom:1px solid var(--b1);font-weight:700;font-size:.88rem;color:var(--t1)}
.sum-pb{padding:16px 18px}
.sum-gamebar{display:flex;gap:10px;align-items:center;padding-bottom:12px;border-bottom:1px solid var(--b0);margin-bottom:10px}
.sum-gamebar img{width:38px;height:38px;border-radius:8px;object-fit:cover}
.sum-row{display:flex;justify-content:space-between;align-items:center;padding:7px 0;border-bottom:1px solid var(--b0);font-size:.83rem}
.sum-row:last-of-type{border-bottom:none}
.sum-lbl{color:var(--t3)}
.sum-val{color:var(--t1);font-weight:600}
.sum-total-row{display:flex;justify-content:space-between;align-items:center;padding:12px 0 0;border-top:1.5px solid var(--b2);margin-top:4px}
.sum-total-lbl{font-weight:700;color:var(--t1);font-size:.88rem}
.sum-total-val{font-size:1.15rem;font-weight:800;color:#aa0000;font-family:var(--f-display)}
.btn-bayar{display:flex;align-items:center;justify-content:center;gap:8px;width:100%;padding:13px;margin-top:12px;background:linear-gradient(135deg,#aa0000,#b8132d);color:white;font-weight:800;font-size:.92rem;border:none;border-radius:10px;cursor:pointer;transition:all .2s;box-shadow:0 4px 18px rgba(212,0,0,.3);font-family:var(--f-display)}
.btn-bayar:hover:not(:disabled){background:linear-gradient(135deg,#d40000,#aa0000);box-shadow:0 6px 24px rgba(212,0,0,.4);transform:translateY(-1px)}
.btn-bayar:disabled{opacity:.4;cursor:not-allowed;transform:none;background:var(--card2);color:var(--t3);box-shadow:none;border:1px solid var(--b2)}
.payment-not-sel{font-size:.76rem;color:var(--t3);font-style:italic;text-align:center;margin-top:8px}
.trust-mini{display:flex;justify-content:center;gap:16px;margin-top:12px;padding-top:12px;border-top:1px solid var(--b0)}
.trust-mini-item{display:flex;align-items:center;gap:4px;font-size:.68rem;color:var(--t3)}

/* Voucher section */
.voucher-section{margin-bottom:14px}
.voucher-btn{display:flex;align-items:center;gap:10px;width:100%;background:var(--card);border:1.5px solid var(--b1);border-radius:12px;padding:12px 16px;cursor:pointer;text-align:left;transition:border-color .2s;color:var(--t1)}
.voucher-btn:hover{border-color:rgba(212,0,0,.3)}
.voucher-btn-ico{width:36px;height:36px;border-radius:8px;background:linear-gradient(135deg,rgba(212,0,0,.15),rgba(212,0,0,.1));border:1px solid rgba(212,0,0,.2);display:flex;align-items:center;justify-content:center;flex-shrink:0}
.voucher-drawer{border:1.5px solid var(--b1);border-top:none;border-radius:0 0 12px 12px;background:var(--card);padding:14px 16px;display:none}
.v-chip{display:inline-flex;align-items:center;gap:6px;background:rgba(34,211,160,.07);border:1px solid rgba(34,211,160,.2);color:#34d399;border-radius:20px;padding:4px 12px;font-size:.75rem;font-weight:600;cursor:pointer;margin:3px}
.v-chip:hover{background:rgba(34,211,160,.15)}

/* Other games */
.other-games-grid{display:grid;grid-template-columns:repeat(6,1fr);gap:10px}
.og-card{text-align:center;text-decoration:none;transition:transform .2s}
.og-card:hover{transform:translateY(-3px)}
.og-card img{width:54px;height:54px;border-radius:12px;object-fit:cover;display:block;margin:0 auto 6px;border:1px solid var(--b1)}
.og-card span{font-size:.72rem;color:var(--t2);font-weight:500;display:block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}

/* FAQ */
.faq-item-dg{border:1px solid var(--b1);border-radius:10px;margin-bottom:8px;overflow:hidden}
.faq-q{display:flex;justify-content:space-between;align-items:center;padding:12px 16px;cursor:pointer;font-size:.84rem;font-weight:600;color:var(--t1);background:var(--card);transition:background .15s}
.faq-q:hover{background:var(--card2)}
.faq-a{display:none;padding:0 16px 14px;font-size:.8rem;color:var(--t2);line-height:1.7;background:var(--card)}

@media(max-width:900px){
  .gp-layout{grid-template-columns:1fr}
  .prod-grid{grid-template-columns:repeat(3,1fr)}
  .sum-panel{position:static}
  .other-games-grid{grid-template-columns:repeat(4,1fr)}
}
@media(max-width:600px){
  .prod-grid{grid-template-columns:repeat(2,1fr)}
  .gp-hero-name{font-size:1.5rem}
  .other-games-grid{grid-template-columns:repeat(3,1fr)}
}
</style>

<!-- HERO -->
<div class="gp-hero">
  <?php if($game["image_url"]): ?>
  <div class="gp-hero-bg" style="background-image:url(<?=htmlspecialchars($game["image_url"])?>)"></div>
  <?php endif; ?>
  <div class="gp-hero-vignette"></div>
  <div class="gp-hero-bottom"></div>
  <div class="gp-hero-inner">
    <!-- Breadcrumb -->
    <nav class="gp-bc">
      <a href="<?=asset("index.php")?>">Beranda</a>
      <span class="gp-bc-sep">›</span>
      <a href="<?=asset("index.php")?>">Top Up</a>
      <span class="gp-bc-sep">›</span>
      <span style="color:rgba(255,255,255,.65);">Top Up <?=htmlspecialchars($game["name"])?></span>
    </nav>
    <div class="gp-hero-content">
      <?php if($game["image_url"]): ?>
      <img src="<?=htmlspecialchars($game["image_url"])?>" class="gp-hero-logo" alt="" onerror="this.style.display='none'"/>
      <?php endif; ?>
      <div>
        <div class="gp-hero-name">Top Up <?=htmlspecialchars($game["name"])?></div>
        <?php if($game["publisher"]): ?><div class="gp-hero-pub"><?=htmlspecialchars($game["publisher"])?></div><?php endif; ?>
        <div class="gp-hero-badges">
          <span class="gp-badge b-cyan">⚡ Proses Instan</span>
          <span class="gp-badge b-green">🔒 Terjamin Aman</span>
          <span class="gp-badge">🕐 24/7 Support</span>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="gp-wrap">
  <?php if(isAdmin()): ?>
  <div style="background:rgba(239,68,68,.07);border:1px solid rgba(239,68,68,.2);color:#fca5a5;padding:10px 14px;font-size:.82rem;border-radius:9px;margin-top:16px;text-align:center;">
    Mode Admin — Tidak dapat melakukan order
  </div>
  <?php endif; ?>

  <div class="gp-layout">
    <!-- ══ LEFT ══ -->
    <div>
      <!-- Step 1: User ID -->
      <div class="gp-card">
        <div class="gp-card-head">
          <div class="gp-step-num">1</div>
          <div class="gp-card-title">Masukkan ID Akun</div>
        </div>
        <div class="gp-card-body">
          <?php if($game["has_server_id"]): ?>
          <div style="background:rgba(45,212,191,.05);border:1px solid rgba(45,212,191,.15);border-radius:8px;padding:9px 13px;margin-bottom:12px;font-size:.77rem;color:#2dd4bf;display:flex;gap:7px;">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;margin-top:1px"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            Game ini membutuhkan User ID + Server ID
          </div>
          <?php endif; ?>
          <div style="display:grid;grid-template-columns:<?=$game["has_server_id"]?"1fr 1fr":"1fr"?>;gap:12px;">
            <div>
              <label class="gp-label">User ID
                <a style="font-size:.69rem;color:#aa0000;font-weight:400;cursor:pointer;margin-left:6px;"
                   onclick="document.getElementById('id-help-modal').style.display='flex'">ⓘ Cara cek</a>
              </label>
              <input type="text" id="inp-userid" placeholder="Masukkan User ID kamu" class="finput" oninput="updateSummary()"/>
            </div>
            <?php if($game["has_server_id"]): ?>
            <div>
              <label class="gp-label">Server ID</label>
              <input type="text" id="inp-serverid" placeholder="Server ID" class="finput" oninput="updateSummary()"/>
            </div>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- Step 2: Pilih Produk -->
      <div class="gp-card">
        <div class="gp-card-head">
          <div class="gp-step-num">2</div>
          <div class="gp-card-title">Pilih Nominal</div>
        </div>
        <div class="gp-card-body">
          <?php
          $tabs = array_keys($prodGroups);
          $hasMulti = count($tabs) > 1;
          ?>
          <?php if($hasMulti): ?>
          <div class="prod-tabs" id="prod-tabs">
            <?php foreach($tabs as $i=>$tab): ?>
            <button class="prod-tab<?=$i===0?' on':''?>" onclick="switchTab(this,'tab-<?=$i?>')"><?=htmlspecialchars($tab)?> <span class="gp-count"><?=count($prodGroups[$tab])?></span></button>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>

          <?php if(empty($products)): ?>
          <div style="text-align:center;padding:40px;color:var(--t3);"><p style="font-weight:600;margin-bottom:6px;">Produk belum tersedia</p><p style="font-size:.82rem;">Silakan cek kembali.</p></div>
          <?php else: ?>
          <?php foreach($tabs as $i=>$tab): ?>
          <div id="tab-<?=$i?>" class="prod-tab-content" style="<?=$i>0?'display:none':''?>">
            <div class="prod-grid">
              <?php foreach($prodGroups[$tab] as $p): ?>
              <div class="prod-card"
                   data-id="<?=$p["id"]?>"
                   data-price="<?=$p["price_sell"]?>"
                   data-name="<?=htmlspecialchars($p["name"],ENT_QUOTES)?>"
                   onclick="selectProduct(this)">
                <?php if($game["image_url"]): ?>
                <img src="<?=htmlspecialchars($game["image_url"])?>" class="pc-ico" onerror="this.style.display='none'"/>
                <?php endif; ?>
                <div class="pc-name"><?=htmlspecialchars($p["name"])?></div>
                <?php if($p["description"]): ?><div class="pc-desc"><?=htmlspecialchars($p["description"])?></div><?php endif; ?>
                <div class="pc-price"><?=formatRupiah($p["price_sell"])?></div>
              </div>
              <?php endforeach; ?>
            </div>
          </div>
          <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>

      <!-- Step 3: Kontak -->
      <div class="gp-card">
        <div class="gp-card-head">
          <div class="gp-step-num">3</div>
          <div class="gp-card-title">Detail Kontak</div>
        </div>
        <div class="gp-card-body">
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
            <div><label class="gp-label">Email <span style="color:#ef4444;">*</span></label>
            <input type="email" id="inp-email" placeholder="email@kamu.com" class="finput" oninput="enablePay()"/></div>
            <div><label class="gp-label">No. HP (Opsional)</label>
            <input type="tel" id="inp-phone" placeholder="08xxxxxxxxxx" class="finput"/></div>
          </div>
          <div style="font-size:.72rem;color:var(--t3);margin-top:8px;display:flex;align-items:center;gap:5px;">
            <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            Bukti top up dikirim ke email ini
          </div>
        </div>
      </div>

      <!-- Voucher Section (DG style) -->
      <div class="voucher-section">
        <button class="voucher-btn" onclick="toggleVoucher()" id="voucher-toggle-btn">
          <div class="voucher-btn-ico">
            <svg width="16" height="16" fill="none" stroke="#d40000" stroke-width="2" viewBox="0 0 24 24"><path d="M20 12v10H4V12"/><path d="M22 7H2v5h20V7z"/></svg>
          </div>
          <div style="flex:1;">
            <div style="font-weight:600;font-size:.85rem;">Voucher &amp; Promo</div>
            <div style="font-size:.72rem;color:var(--t3);margin-top:1px;">Masukkan kode promo atau pilih dari daftar</div>
          </div>
          <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" id="voucher-caret" style="transition:transform .2s"><path d="m6 9 6 6 6-6"/></svg>
        </button>
        <div class="voucher-drawer" id="voucher-drawer">
          <div style="display:flex;gap:8px;margin-bottom:10px;">
            <input type="text" id="inp-voucher" placeholder="Ketik kode promo" class="finput" style="flex:1;text-transform:uppercase;" oninput="this.value=this.value.toUpperCase()" maxlength="32"/>
            <button onclick="applyVoucher()" class="btn-ghost" style="padding:9px 14px;font-size:.83rem;white-space:nowrap;">Gunakan</button>
          </div>
          <div id="voucher-msg" style="font-size:.76rem;margin-bottom:8px;display:none;"></div>
          <?php if(!empty($activeVouchers)): ?>
          <div style="font-size:.72rem;color:var(--t3);margin-bottom:6px;font-weight:600;">PROMO TERSEDIA</div>
          <div style="display:flex;flex-wrap:wrap;gap:4px;">
            <?php foreach($activeVouchers as $v): ?>
            <div class="v-chip" data-code="<?=htmlspecialchars($v["code"],ENT_QUOTES)?>" onclick="useVoucherEl(this)">
              <?=htmlspecialchars($v["code"])?>
              <span style="opacity:.4">·</span>
              <span><?=$v["type"]==="percent"?$v["value"]."%":"Rp ".number_format($v["value"],0,",",".")?> off</span>
            </div>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Other Games -->
      <?php if(!empty($otherGames)): ?>
      <div style="margin-top:28px;">
        <div style="font-weight:700;font-size:.88rem;color:var(--t1);margin-bottom:12px;display:flex;align-items:center;gap:8px;">
          <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="2" y="6" width="20" height="12" rx="3"/><path d="M6 12h4m-2-2v4M15 12h.01M18 12h.01"/></svg>
          Game Lainnya
        </div>
        <div class="other-games-grid">
          <?php foreach($otherGames as $og): ?>
          <a href="<?=asset("pages/game.php")?>?slug=<?=urlencode($og["slug"])?>" class="og-card">
            <?php if($og["image_url"]): ?>
            <img src="<?=htmlspecialchars($og["image_url"])?>" alt="<?=htmlspecialchars($og["name"])?>" onerror="this.src=''"/>
            <?php else: ?>
            <div style="width:54px;height:54px;border-radius:12px;background:var(--card2);margin:0 auto 6px;border:1px solid var(--b1);"></div>
            <?php endif; ?>
            <span><?=htmlspecialchars($og["name"])?></span>
          </a>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

      <!-- Info & FAQ -->
      <div style="margin-top:28px;">
        <div style="font-weight:700;font-size:.88rem;color:var(--t1);margin-bottom:12px;">Info &amp; FAQ</div>
        <div class="faq-item-dg">
          <div class="faq-q" onclick="toggleFaq(this)">
            <span>Bagaimana cara top up <?=htmlspecialchars($game["name"])?>?</span>
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="m6 9 6 6 6-6"/></svg>
          </div>
          <div class="faq-a">Masukkan User ID game kamu, pilih nominal yang diinginkan, lalu klik Bayar Sekarang. Top up akan diproses secara otomatis dan langsung masuk ke akun game kamu.</div>
        </div>
        <div class="faq-item-dg">
          <div class="faq-q" onclick="toggleFaq(this)">
            <span>Berapa lama proses top up?</span>
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="m6 9 6 6 6-6"/></svg>
          </div>
          <div class="faq-a">Proses top up bersifat instan, biasanya selesai dalam hitungan detik hingga maksimal 5 menit setelah pembayaran berhasil.</div>
        </div>
        <div class="faq-item-dg">
          <div class="faq-q" onclick="toggleFaq(this)">
            <span>Top up tidak masuk, apa yang harus dilakukan?</span>
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="m6 9 6 6 6-6"/></svg>
          </div>
          <div class="faq-a">Cek halaman Cek Transaksi dengan kode order kamu. Jika status masih pending lebih dari 30 menit, hubungi CS kami via WhatsApp atau Telegram yang tersedia di bagian bawah halaman.</div>
        </div>
      </div>

    </div>

    <!-- ══ RIGHT: Detail Pembelian ══ -->
    <div>
      <div class="sum-panel">
        <div class="sum-ph">Detail Pembelian</div>
        <div class="sum-pb">
          <div class="sum-gamebar">
            <?php if($game["image_url"]): ?>
            <img src="<?=htmlspecialchars($game["image_url"])?>" onerror="this.style.display='none'"/>
            <?php endif; ?>
            <div>
              <div style="font-weight:700;font-size:.84rem;color:var(--t1);"><?=htmlspecialchars($game["name"])?></div>
              <?php if($game["publisher"]): ?><div style="font-size:.7rem;color:var(--t3);"><?=htmlspecialchars($game["publisher"])?></div><?php endif; ?>
            </div>
          </div>

          <div class="sum-row"><span class="sum-lbl" id="sum-prod-lbl">Produk</span><span class="sum-val" id="sum-nominal" style="color:var(--t3);font-style:italic;font-weight:400;font-size:.78rem;">Belum dipilih</span></div>
          <div class="sum-row"><span class="sum-lbl">User ID</span><span class="sum-val" id="sum-userid" style="color:var(--t3);font-style:italic;font-weight:400;font-size:.78rem;">—</span></div>
          <?php if($game["has_server_id"]): ?>
          <div class="sum-row"><span class="sum-lbl">Server</span><span class="sum-val" id="sum-serverid" style="color:var(--t3);font-size:.78rem;font-weight:400;">—</span></div>
          <?php endif; ?>
          <div class="sum-row" id="sum-disc-row" style="display:none;">
            <span class="sum-lbl" style="color:#22d3a0;">Diskon</span>
            <span class="sum-val" id="sum-disc" style="color:#22d3a0;"></span>
          </div>

          <div class="sum-total-row">
            <span class="sum-total-lbl">Total</span>
            <span class="sum-total-val" id="sum-total">Rp 0</span>
          </div>

          <button class="btn-bayar" id="btn-pay" onclick="submitOrder()" disabled>
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
            Bayar Sekarang
          </button>
          <div class="payment-not-sel" id="pay-hint">Pilih nominal &amp; isi User ID terlebih dahulu</div>

          <div class="trust-mini">
            <div class="trust-mini-item"><svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>SSL Aman</div>
            <div class="trust-mini-item"><svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>Instan</div>
            <div class="trust-mini-item"><svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.6 1.3h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8a16 16 0 0 0 6 6l.92-.92a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 21.73 16"/></svg>24/7 CS</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- "Cara cek ID" modal -->
<div id="id-help-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:9999;align-items:center;justify-content:center;padding:20px;" onclick="this.style.display='none'">
  <div style="background:var(--card);border-radius:14px;padding:24px;max-width:340px;width:100%;" onclick="event.stopPropagation()">
    <div style="font-weight:700;font-size:.95rem;margin-bottom:14px;color:var(--t1);">Cara Cek User ID</div>
    <ol style="font-size:.82rem;color:var(--t2);line-height:2;padding-left:18px;">
      <li>Buka game <?=htmlspecialchars($game["name"])?></li>
      <li>Tap foto profil / ikon akun di pojok kiri atas</li>
      <li>User ID terlihat di bawah nama karakter</li>
      <?php if($game["has_server_id"]): ?><li>Server ID ada di sebelah User ID (contoh: (2101))</li><?php endif; ?>
    </ol>
    <button onclick="document.getElementById('id-help-modal').style.display='none'" style="width:100%;padding:10px;background:var(--card2);border:1px solid var(--b1);border-radius:8px;color:var(--t2);cursor:pointer;margin-top:14px;font-size:.84rem;">Mengerti</button>
  </div>
</div>

<!-- Hidden form -->
<form id="order-form" method="POST" action="<?=asset("pages/checkout.php")?>" style="display:none;">
  <input type="hidden" name="_token" value="<?=csrfToken()?>">
  <input type="hidden" name="product_id" id="f-product">
  <input type="hidden" name="game_user_id" id="f-userid">
  <input type="hidden" name="server_id" id="f-serverid">
  <input type="hidden" name="buyer_email" id="f-email">
  <input type="hidden" name="buyer_phone" id="f-phone">
  <input type="hidden" name="voucher_code" id="f-voucher">
  <input type="hidden" name="game_slug" value="<?=htmlspecialchars($slug)?>">
</form>

<script>
var selProduct=null,discType="",discVal=0;

function switchTab(btn, tabId){
  document.querySelectorAll(".prod-tab").forEach(t=>t.classList.remove("on"));
  document.querySelectorAll(".prod-tab-content").forEach(t=>t.style.display="none");
  btn.classList.add("on");
  document.getElementById(tabId).style.display="";
}

function selectProduct(el){
  document.querySelectorAll(".prod-card").forEach(c=>c.classList.remove("sel"));
  el.classList.add("sel");
  selProduct={id:el.dataset.id,price:parseFloat(el.dataset.price),name:el.dataset.name};
  updateSummary();
}

function updateSummary(){
  if(selProduct){
    var n=document.getElementById("sum-nominal");
    if(n){n.textContent=selProduct.name;n.style.cssText="font-weight:700;color:var(--t1);font-style:normal;font-size:.84rem;";}
    var pl=document.getElementById("sum-prod-lbl");
    if(pl)pl.textContent="Produk";
  }
  var uid=document.getElementById("inp-userid")?.value.trim()||"—";
  var uel=document.getElementById("sum-userid");
  if(uel){uel.textContent=uid;uel.style.cssText=uid!=="—"?"font-weight:600;color:var(--t1);font-style:normal;font-size:.83rem;":"color:var(--t3);font-style:italic;font-weight:400;font-size:.78rem;";}
  <?php if($game["has_server_id"]): ?>
  var sid=document.getElementById("inp-serverid")?.value.trim()||"—";
  var sel2=document.getElementById("sum-serverid");
  if(sel2){sel2.textContent=sid;sel2.style.cssText=sid!=="—"?"font-weight:600;color:var(--t1);font-size:.83rem;":"color:var(--t3);font-size:.78rem;font-weight:400;";}
  <?php endif; ?>
  calcTotal();enablePay();
}

function calcTotal(){
  if(!selProduct){document.getElementById("sum-total").textContent="Rp 0";return;}
  var base=selProduct.price,disc=0;
  if(discType==="percent")disc=Math.round(base*discVal/100);
  else if(discType==="fixed")disc=Math.min(discVal,base);
  var total=base-disc;
  document.getElementById("sum-total").textContent="Rp "+total.toLocaleString("id-ID");
  var dr=document.getElementById("sum-disc-row"),de=document.getElementById("sum-disc");
  if(disc>0&&dr&&de){dr.style.display="flex";de.textContent="- Rp "+disc.toLocaleString("id-ID");}
  else if(dr)dr.style.display="none";
}

function enablePay(){
  var uid=document.getElementById("inp-userid")?.value.trim();
  var email=document.getElementById("inp-email")?.value.trim();
  var ok=selProduct&&uid&&email&&email.includes("@");
  var btn=document.getElementById("btn-pay"),hint=document.getElementById("pay-hint");
  if(btn)btn.disabled=!ok;
  if(hint)hint.style.display=ok?"none":"block";
}

function toggleVoucher(){
  var d=document.getElementById("voucher-drawer"),c=document.getElementById("voucher-caret");
  var open=d.style.display!=="block";
  d.style.display=open?"block":"none";
  if(c)c.style.transform=open?"rotate(180deg)":"";
}

function applyVoucher(){
  var code=document.getElementById("inp-voucher")?.value.trim().toUpperCase();
  var msg=document.getElementById("voucher-msg");
  if(!code){if(msg){msg.style.display="block";msg.style.color="#f87171";msg.textContent="Masukkan kode voucher.";}return;}
  fetch("<?=asset('api/voucher_check.php')?>?code="+encodeURIComponent(code))
    .then(r=>r.json()).then(d=>{
      if(d.valid){discType=d.type;discVal=d.value;if(msg){msg.style.display="block";msg.style.color="#34d399";msg.textContent="Voucher "+code+" berhasil diterapkan!";}calcTotal();}
      else{discType="";discVal=0;if(msg){msg.style.display="block";msg.style.color="#f87171";msg.textContent=d.message||"Voucher tidak valid.";}calcTotal();}
    }).catch(()=>{if(msg){msg.style.display="block";msg.style.color="#f87171";msg.textContent="Gagal cek voucher.";}});
}
function useVoucherEl(el){var inp=document.getElementById("inp-voucher");if(inp)inp.value=el.dataset.code;applyVoucher();}

function toggleFaq(el){
  var a=el.nextElementSibling;
  var open=a.style.display!=="block";
  a.style.display=open?"block":"none";
  var svg=el.querySelector("svg");
  if(svg)svg.style.transform=open?"rotate(180deg)":"";
}

function submitOrder(){
  var uid=document.getElementById("inp-userid")?.value.trim();
  var email=document.getElementById("inp-email")?.value.trim();
  if(!selProduct){alert("Pilih nominal terlebih dahulu.");return;}
  if(!uid){alert("Masukkan User ID game kamu.");return;}
  if(!email||!email.includes("@")){alert("Masukkan email yang valid.");return;}
  document.getElementById("f-product").value=selProduct.id;
  document.getElementById("f-userid").value=uid;
  document.getElementById("f-serverid").value=document.getElementById("inp-serverid")?.value.trim()||"";
  document.getElementById("f-email").value=email;
  document.getElementById("f-phone").value=document.getElementById("inp-phone")?.value.trim()||"";
  document.getElementById("f-voucher").value=document.getElementById("inp-voucher")?.value.trim().toUpperCase()||"";
  var btn=document.getElementById("btn-pay");
  btn.disabled=true;btn.innerHTML="Memproses...";
  document.getElementById("order-form").submit();
}
document.addEventListener("input",enablePay);
</script>
<?php include __DIR__."/../includes/footer.php"; ?>