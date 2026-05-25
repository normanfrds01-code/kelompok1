<?php
require_once __DIR__.'/../includes/functions.php';
$pageTitle = 'Pencarian — '.siteName();

$q       = Security::cleanInput($_GET['q'] ?? '');
$results = [];

if(strlen($q) >= 2){
    $like = '%'.$q.'%';
    $stmt = db()->prepare("
        SELECT g.*, c.name AS category_name,
               (SELECT COUNT(*) FROM products WHERE game_id=g.id AND is_active=1) AS product_count
        FROM games g
        JOIN categories c ON c.id = g.category_id
        WHERE g.is_active = 1 AND (g.name LIKE ? OR g.publisher LIKE ? OR c.name LIKE ?)
        ORDER BY g.is_popular DESC, g.sort_order ASC
        LIMIT 24
    ");
    $stmt->execute([$like, $like, $like]);
    $results = $stmt->fetchAll();
}

$allGames = strlen($q) < 2 ? getAllGames() : [];

include __DIR__.'/../includes/header.php';
?>
<div class="container" style="padding-top:28px;padding-bottom:64px;max-width:1200px;">

  <!-- Breadcrumb -->
  <div class="bc" style="margin-bottom:20px;">
    <a href="<?=asset('index.php')?>">Beranda</a>
    <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="m9 18 6-6-6-6"/></svg>
    <span>Pencarian</span>
  </div>

  <!-- Search bar -->
  <div style="max-width:640px;margin-bottom:32px;">
    <form method="GET" action="<?=asset('pages/search.php')?>">
      <div style="display:flex;gap:10px;">
        <div style="flex:1;display:flex;align-items:center;gap:10px;background:var(--input);border:1.5px solid <?=$q?'var(--violet)':'var(--b2)'?>;border-radius:var(--r);padding:0 16px;box-shadow:<?=$q?'0 0 0 3px var(--violet-lo)':'none'?>;">
          <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color:var(--t3);flex-shrink:0;"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
          <input type="text" name="q" id="search-q" value="<?=htmlspecialchars($q)?>"
                 placeholder="Cari nama game, voucher, publisher..."
                 autofocus autocomplete="off"
                 style="flex:1;background:none;border:none;outline:none;color:var(--t1);font-size:.92rem;padding:13px 0;font-family:var(--f-body);"/>
          <?php if($q): ?>
          <a href="<?=asset('pages/search.php')?>" style="display:flex;align-items:center;justify-content:center;width:22px;height:22px;border-radius:50%;background:var(--card2);color:var(--t3);flex-shrink:0;font-size:.75rem;transition:all .15s;" onmouseover="this.style.background='var(--b2)';this.style.color='var(--t1)'" onmouseout="this.style.background='var(--card2)';this.style.color='var(--t3)'">✕</a>
          <?php endif; ?>
        </div>
        <button type="submit" class="btn-gold" style="padding:0 28px;border-radius:var(--r);font-size:.9rem;font-weight:700;white-space:nowrap;">Cari</button>
      </div>
    </form>
  </div>

  <?php if(strlen($q) >= 2): ?>

    <!-- Ada query -->
    <?php if(empty($results)): ?>

    <!-- Tidak ada hasil -->
    <div style="background:var(--card);border:1px solid var(--b1);border-radius:var(--rl);padding:60px 40px;text-align:center;max-width:520px;margin:0 auto;">
      <div style="width:64px;height:64px;border-radius:50%;background:var(--card2);border:1px solid var(--b1);display:flex;align-items:center;justify-content:center;margin:0 auto 18px;">
        <svg width="26" height="26" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="color:var(--t3);"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
      </div>
      <h2 style="font-family:var(--f-display);font-size:1.15rem;font-weight:800;margin-bottom:8px;">Game tidak ditemukan</h2>
      <p style="color:var(--t3);font-size:.83rem;line-height:1.75;margin-bottom:22px;">Tidak ada game untuk kata kunci <strong style="color:var(--t1);">"<?=htmlspecialchars($q)?>"</strong>. Coba kata kunci yang lebih umum.</p>
      <div style="display:flex;flex-wrap:wrap;gap:7px;justify-content:center;">
        <?php foreach(['Mobile Legends','Free Fire','PUBG Mobile','Genshin Impact','Valorant'] as $s): ?>
        <a href="?q=<?=urlencode($s)?>" style="padding:6px 14px;background:var(--card2);border:1px solid var(--b2);border-radius:20px;font-size:.77rem;color:var(--t2);transition:all .15s;" onmouseover="this.style.borderColor='var(--violet)';this.style.color='var(--t1)'" onmouseout="this.style.borderColor='var(--b2)';this.style.color='var(--t2)'"><?=$s?></a>
        <?php endforeach; ?>
      </div>
    </div>

    <?php else: ?>

    <!-- Ada hasil -->
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;">
      <div>
        <span style="font-family:var(--f-display);font-size:1.05rem;font-weight:700;">Hasil untuk </span>
        <span style="color:var(--gold);font-family:var(--f-display);font-weight:800;">"<?=htmlspecialchars($q)?>"</span>
        <span style="font-size:.8rem;color:var(--t3);margin-left:8px;"><?=count($results)?> game ditemukan</span>
      </div>
    </div>

    <!-- Grid hasil — pakai layout horizontal card, bukan portrait -->
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:12px;">
      <?php foreach($results as $g): ?>
      <a href="<?=asset('pages/game.php')?>?slug=<?=urlencode($g['slug'])?>"
         style="display:flex;align-items:center;gap:14px;background:var(--card);border:1px solid var(--b1);border-radius:var(--rl);padding:14px 16px;text-decoration:none;transition:all .2s var(--ease);position:relative;overflow:hidden;"
         onmouseover="this.style.borderColor='rgba(245,166,35,.4)';this.style.transform='translateY(-2px)';this.style.boxShadow='0 8px 24px rgba(0,0,0,.4)'"
         onmouseout="this.style.borderColor='var(--b1)';this.style.transform='';this.style.boxShadow=''">

        <!-- Thumbnail -->
        <div style="width:56px;height:56px;border-radius:10px;overflow:hidden;flex-shrink:0;background:var(--card2);border:1px solid var(--b1);">
          <?php if($g['image_url']): ?>
          <img src="<?=htmlspecialchars($g['image_url'])?>" style="width:100%;height:100%;object-fit:cover;" onerror="this.style.display='none';this.parentElement.innerHTML='<div style=\'width:100%;height:100%;display:flex;align-items:center;justify-content:center;\'><svg width=\'20\' height=\'20\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'1.5\' viewBox=\'0 0 24 24\' style=\'opacity:.3\'><rect x=\'2\' y=\'6\' width=\'20\' height=\'12\' rx=\'3\'/><path d=\'M6 12h4m-2-2v4M15 12h.01M18 12h.01\'/></svg></div>'"/>
          <?php else: ?>
          <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="opacity:.3;"><rect x="2" y="6" width="20" height="12" rx="3"/><path d="M6 12h4m-2-2v4M15 12h.01M18 12h.01"/></svg>
          </div>
          <?php endif; ?>
        </div>

        <!-- Info -->
        <div style="flex:1;min-width:0;">
          <div style="font-family:var(--f-display);font-weight:700;font-size:.92rem;color:var(--t1);margin-bottom:3px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?=htmlspecialchars($g['name'])?></div>
          <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
            <span style="font-size:.71rem;color:var(--t3);"><?=htmlspecialchars($g['category_name'])?></span>
            <?php if($g['publisher']): ?>
            <span style="font-size:.68rem;color:var(--t3);">·</span>
            <span style="font-size:.71rem;color:var(--t3);"><?=htmlspecialchars($g['publisher'])?></span>
            <?php endif; ?>
          </div>
          <div style="margin-top:6px;display:flex;align-items:center;gap:6px;">
            <?php if($g['product_count'] > 0): ?>
            <span style="font-size:.7rem;color:var(--cyan);background:rgba(6,214,160,.08);border:1px solid rgba(6,214,160,.15);border-radius:4px;padding:2px 8px;"><?=$g['product_count']?> produk</span>
            <?php endif; ?>
            <?php if($g['is_popular']): ?>
            <span style="font-size:.7rem;color:var(--gold);background:var(--gold-lo);border:1px solid var(--gold-md);border-radius:4px;padding:2px 8px;">Populer</span>
            <?php endif; ?>
          </div>
        </div>

        <!-- Arrow -->
        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color:var(--t3);flex-shrink:0;"><path d="m9 18 6-6-6-6"/></svg>
      </a>
      <?php endforeach; ?>
    </div>

    <?php endif; ?>

  <?php else: ?>

  <!-- Belum ada query — tampilkan semua game -->
  <div>
    <div style="display:flex;align-items:center;gap:10px;margin-bottom:18px;">
      <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color:var(--violet);"><rect x="2" y="6" width="20" height="12" rx="3"/><path d="M6 12h4m-2-2v4M15 12h.01M18 12h.01"/></svg>
      <span style="font-family:var(--f-display);font-size:1rem;font-weight:700;color:var(--t1);">Semua Game</span>
      <span style="font-size:.78rem;color:var(--t3);"><?=count($allGames)?> tersedia</span>
    </div>

    <?php if(empty($allGames)): ?>
    <div style="text-align:center;padding:60px;color:var(--t3);">Belum ada game tersedia.</div>
    <?php else: ?>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:12px;">
      <?php foreach($allGames as $g):
        $pc = db()->prepare("SELECT COUNT(*) FROM products WHERE game_id=? AND is_active=1"); $pc->execute([$g['id']]); $pc=(int)$pc->fetchColumn();
      ?>
      <a href="<?=asset('pages/game.php')?>?slug=<?=urlencode($g['slug'])?>"
         style="display:flex;align-items:center;gap:14px;background:var(--card);border:1px solid var(--b1);border-radius:var(--rl);padding:14px 16px;text-decoration:none;transition:all .2s var(--ease);"
         onmouseover="this.style.borderColor='rgba(245,166,35,.4)';this.style.transform='translateY(-2px)';this.style.boxShadow='0 8px 24px rgba(0,0,0,.4)'"
         onmouseout="this.style.borderColor='var(--b1)';this.style.transform='';this.style.boxShadow=''">
        <div style="width:56px;height:56px;border-radius:10px;overflow:hidden;flex-shrink:0;background:var(--card2);border:1px solid var(--b1);">
          <?php if($g['image_url']): ?>
          <img src="<?=htmlspecialchars($g['image_url'])?>" style="width:100%;height:100%;object-fit:cover;"/>
          <?php else: ?>
          <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="opacity:.3;"><rect x="2" y="6" width="20" height="12" rx="3"/><path d="M6 12h4m-2-2v4M15 12h.01M18 12h.01"/></svg>
          </div>
          <?php endif; ?>
        </div>
        <div style="flex:1;min-width:0;">
          <div style="font-family:var(--f-display);font-weight:700;font-size:.92rem;margin-bottom:3px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?=htmlspecialchars($g['name'])?></div>
          <div style="font-size:.71rem;color:var(--t3);"><?=htmlspecialchars($g['category_name'])?><?=$g['publisher']?' · '.htmlspecialchars($g['publisher']):''?></div>
          <div style="margin-top:5px;display:flex;gap:5px;">
            <?php if($pc>0): ?><span style="font-size:.68rem;color:var(--cyan);background:rgba(6,214,160,.08);border:1px solid rgba(6,214,160,.15);border-radius:4px;padding:1px 7px;"><?=$pc?> produk</span><?php endif; ?>
            <?php if($g['is_popular']): ?><span style="font-size:.68rem;color:var(--gold);background:var(--gold-lo);border:1px solid var(--gold-md);border-radius:4px;padding:1px 7px;">Populer</span><?php endif; ?>
          </div>
        </div>
        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color:var(--t3);flex-shrink:0;"><path d="m9 18 6-6-6-6"/></svg>
      </a>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>

  <?php endif; ?>

</div>
<?php include __DIR__.'/../includes/footer.php'; ?>