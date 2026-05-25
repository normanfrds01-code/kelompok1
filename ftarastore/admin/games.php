<?php
require_once __DIR__.'/../includes/functions.php';
Security::boot(['admin']);
$pageTitle = 'Kelola Produk — Admin';

if($_SERVER['REQUEST_METHOD']==='POST'){
  verifyCsrf(); $act=$_POST['action']??'';

  if($act==='add'){
    $slug = trim($_POST['slug']??'') ?: strtolower(preg_replace('/[^a-z0-9]+/','-',trim($_POST['name'])));
    $slug = trim($slug,'-');
    $check = db()->prepare("SELECT id FROM games WHERE slug=?"); $check->execute([$slug]);
    if($check->fetch()) $slug = $slug.'-'.substr(uniqid(),-4);
    $imgUrl = substr(trim($_POST['image_url']??''), 0, 1000); // prevent truncation error
    db()->prepare("INSERT INTO games (category_id,name,slug,publisher,image_url,has_server_id,is_popular,sort_order) VALUES (?,?,?,?,?,?,?,?)")
      ->execute([$_POST['category_id'],$_POST['name'],$slug,$_POST['publisher']??null,$imgUrl?:null,(int)($_POST['has_server_id']??0),(int)($_POST['is_popular']??0),(int)($_POST['sort_order']??0)]);
    setFlash('success','Game berhasil ditambahkan.');
  }

  elseif($act==='edit'){
    $id   = (int)$_POST['id'];
    $slug = trim($_POST['slug']??'') ?: strtolower(preg_replace('/[^a-z0-9]+/','-',trim($_POST['name'])));
    $slug = trim($slug,'-');
    $check = db()->prepare("SELECT id FROM games WHERE slug=? AND id!=?"); $check->execute([$slug,$id]);
    if($check->fetch()) $slug = $slug.'-'.substr(uniqid(),-4);
    $imgUrl = substr(trim($_POST['image_url']??''), 0, 1000); // prevent truncation error
    db()->prepare("UPDATE games SET category_id=?,name=?,slug=?,publisher=?,image_url=?,has_server_id=?,is_popular=?,sort_order=? WHERE id=?")
      ->execute([$_POST['category_id'],$_POST['name'],$slug,$_POST['publisher']??null,$imgUrl?:null,(int)($_POST['has_server_id']??0),(int)($_POST['is_popular']??0),(int)($_POST['sort_order']??0),$id]);
    setFlash('success','Game berhasil diperbarui.');
  }

  elseif($act==='delete'){
    $id = (int)$_POST['id'];
    // Cek apakah ada produk yang punya order
    $hasOrders = db()->prepare("
        SELECT COUNT(*) FROM orders o
        JOIN products p ON p.id = o.product_id
        WHERE p.game_id = ?
    ");
    $hasOrders->execute([$id]);
    $orderCount = (int)$hasOrders->fetchColumn();

    if($orderCount > 0){
        // Ada order — soft delete saja (jaga riwayat transaksi)
        db()->prepare("UPDATE products SET is_active=0 WHERE game_id=?")->execute([$id]);
        db()->prepare("UPDATE games SET is_active=0 WHERE id=?")->execute([$id]);
        Security::audit('GAME_SOFT_DELETED',"Game #$id dinonaktifkan ($orderCount order terkait)");
        setFlash('info',"Game dinonaktifkan (tidak dihapus permanen karena ada <strong>$orderCount order</strong> terkait).");
    } else {
        // Tidak ada order — aman hard delete
        db()->prepare("DELETE FROM products WHERE game_id=?")->execute([$id]);
        db()->prepare("DELETE FROM games WHERE id=?")->execute([$id]);
        Security::audit('GAME_DELETED',"Game #$id dihapus permanen");
        setFlash('success','Game dan semua produknya berhasil dihapus permanen.');
    }
  }

  header('Location: '.asset('admin/games.php')); exit;
}

$filterCat = (int)($_GET['cat']??0);
if($filterCat > 0){
    $gstmt = db()->prepare("SELECT g.*,c.name AS cname FROM games g JOIN categories c ON c.id=g.category_id WHERE g.is_active=1 AND g.category_id=? ORDER BY g.sort_order,g.name");
    $gstmt->execute([$filterCat]);
    $games = $gstmt->fetchAll();
} else {
    $games = db()->query("SELECT g.*,c.name AS cname FROM games g JOIN categories c ON c.id=g.category_id WHERE g.is_active=1 ORDER BY g.sort_order,g.name")->fetchAll();
}
$cats  = getCategories();
include __DIR__.'/../includes/header.php';
?>
<div class="admin-wrap">
<?php include __DIR__.'/sidebar.php'; ?>
<div class="admin-main">

  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;flex-wrap:wrap;gap:12px;">
    <div class="admin-title" style="margin:0;">Kelola Produk</div>
    <button class="btn-submit" data-modal-open="modal-add" style="padding:9px 20px;font-size:.88rem;">+ Tambah Produk</button>
  </div>
  <!-- Filter Kategori Produk -->
  <?php
  $filterCat = (int)($_GET['cat']??0);
  $catFilters = [
    0 => ['label'=>'Semua', 'icon'=>'⊞'],
    1 => ['label'=>'Top Up Game', 'icon'=>'🎮'],
    2 => ['label'=>'Pulsa & Data', 'icon'=>'📱'],
    3 => ['label'=>'Voucher', 'icon'=>'🎁'],
    4 => ['label'=>'Tagihan', 'icon'=>'📋'],
    5 => ['label'=>'Entertainment', 'icon'=>'🎬'],
  ];
  // Get actual categories from DB
  $dbCats = db()->query("SELECT id,name FROM categories ORDER BY id")->fetchAll();
  ?>
  <div style="display:flex;gap:6px;margin-bottom:20px;flex-wrap:wrap;">
    <a href="?cat=0" style="padding:7px 16px;border-radius:8px;font-size:.8rem;font-weight:600;text-decoration:none;border:1px solid;<?=$filterCat===0?'background:var(--cyan2);color:white;border-color:var(--cyan2);':'background:var(--card);color:var(--t2);border-color:var(--b2);'?>">Semua</a>
    <?php foreach($dbCats as $dc): ?>
    <a href="?cat=<?=$dc['id']?>" style="padding:7px 16px;border-radius:8px;font-size:.8rem;font-weight:600;text-decoration:none;border:1px solid;<?=$filterCat===$dc['id']?'background:var(--cyan2);color:white;border-color:var(--cyan2);':'background:var(--card);color:var(--t2);border-color:var(--b2);'?>">
      <?=htmlspecialchars($dc['name'])?>
    </a>
    <?php endforeach; ?>
  </div>

  <div class="admin-card">
    <div class="table-wrap">
    <table class="dtable">
      <thead><tr><th>Game</th><th>Kategori</th><th>Publisher</th><th style="text-align:center">Server ID</th><th style="text-align:center">Populer</th><th>Aksi</th></tr></thead>
      <tbody>
      <?php if(empty($games)): ?>
      <tr><td colspan="6" style="text-align:center;padding:48px;color:var(--t3);">Belum ada game.</td></tr>
      <?php else: foreach($games as $g): ?>
      <tr>
        <td>
          <div style="display:flex;align-items:center;gap:10px;">
            <?php if($g['image_url']): ?>
              <img src="<?=htmlspecialchars($g['image_url'])?>" style="width:40px;height:40px;border-radius:8px;object-fit:cover;flex-shrink:0;border:1px solid var(--b1);" onerror="this.style.display='none';this.nextElementSibling.style.display='flex'"/>
              <div style="display:none;width:40px;height:40px;border-radius:8px;background:var(--card2);align-items:center;justify-content:center;flex-shrink:0;border:1px solid var(--b1);">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="opacity:.35"><rect x="2" y="6" width="20" height="12" rx="3"/><path d="M6 12h4m-2-2v4M15 12h.01M18 12h.01"/></svg>
              </div>
            <?php else: ?>
              <div style="width:40px;height:40px;border-radius:8px;background:var(--card2);display:flex;align-items:center;justify-content:center;flex-shrink:0;border:1px solid var(--b1);">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="opacity:.35"><rect x="2" y="6" width="20" height="12" rx="3"/><path d="M6 12h4m-2-2v4M15 12h.01M18 12h.01"/></svg>
              </div>
            <?php endif; ?>
            <div>
              <div style="font-weight:600;font-size:.88rem;"><?=htmlspecialchars($g['name'])?></div>
              <div style="font-size:.72rem;color:var(--t3);"><?=htmlspecialchars($g['slug'])?></div>
            </div>
          </div>
        </td>
        <td><span class="badge badge-process" style="font-size:.72rem;"><?=htmlspecialchars($g['cname'])?></span></td>
        <td style="color:var(--t2);font-size:.84rem;"><?=htmlspecialchars($g['publisher']??'—')?></td>
        <td style="text-align:center;"><?=$g['has_server_id']?'<span class="badge badge-success" style="font-size:.7rem;">Ya</span>':'<span style="color:var(--t3);">—</span>'?></td>
        <td style="text-align:center;"><?=$g['is_popular']?'<span class="badge badge-pending" style="font-size:.7rem;">Ya</span>':'<span style="color:var(--t3);">—</span>'?></td>
        <td>
          <div style="display:flex;gap:6px;">
            <button type="button" class="btn-sm btn-sm-edit"
              data-id="<?=(int)$g['id']?>"
              data-name="<?=htmlspecialchars($g['name'],ENT_QUOTES)?>"
              data-slug="<?=htmlspecialchars($g['slug'],ENT_QUOTES)?>"
              data-cat="<?=(int)$g['category_id']?>"
              data-pub="<?=htmlspecialchars($g['publisher']??'')?>"
              data-img="<?=htmlspecialchars($g['image_url']??'')?>"
              data-sid="<?=(int)$g['has_server_id']?>"
              data-pop="<?=(int)$g['is_popular']?>"
              data-sort="<?=(int)$g['sort_order']?>"
              onclick="openEditEl(this)">Edit</button>
            <a href="<?=asset('admin/products.php')?>?game_id=<?=$g['id']?>" class="btn-sm btn-sm-edit">Produk</a>
            <form method="POST" style="display:inline;">
              <input type="hidden" name="_token" value="<?=csrfToken()?>">
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="id" value="<?=$g['id']?>">
              <button type="submit" class="btn-sm btn-sm-danger" onclick="return confirm('Hapus game ini beserta SEMUA produknya secara permanen?')">Hapus</button>
            </form>
          </div>
        </td>
      </tr>
      <?php endforeach; endif; ?>
      </tbody>
    </table>
    </div>
  </div>
</div>
</div>

<!-- Modal Tambah -->
<div class="modal" id="modal-add">
  <div class="modal-box">
    <div class="modal-head"><h3>+ Tambah Produk Baru</h3><button class="modal-close" data-modal-close="modal-add">✕</button></div>
    <form method="POST">
      <input type="hidden" name="_token" value="<?=csrfToken()?>">
      <input type="hidden" name="action" value="add">
      <div class="fg"><label class="flabel">Kategori <span class="req">*</span></label>
        <select name="category_id" class="finput" required><?php foreach($cats as $c): ?><option value="<?=$c['id']?>"><?=htmlspecialchars($c['name'])?></option><?php endforeach; ?></select>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
        <div class="fg"><label class="flabel">Nama Game <span class="req">*</span></label><input type="text" name="name" class="finput" required/></div>
        <div class="fg"><label class="flabel">Slug URL</label><input type="text" name="slug" class="finput" placeholder="auto-generate"/></div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
        <div class="fg"><label class="flabel">Publisher</label><input type="text" name="publisher" class="finput"/></div>
        <div class="fg"><label class="flabel">Sort Order</label><input type="number" name="sort_order" class="finput" value="0"/></div>
      </div>
      <div class="fg">
        <label class="flabel">URL Gambar</label>
        <input type="url" name="image_url" class="finput" placeholder="https://..." oninput="previewImg(this.value,'add-prev')" style="font-size:.76rem;"/>
        <div id="add-url-display" style="display:none;font-size:.66rem;color:var(--t3);word-break:break-all;margin-top:3px;padding:4px 8px;background:var(--card2);border-radius:4px;"></div>
        <img id="add-prev" src="" style="display:none;margin-top:8px;max-height:120px;width:100%;object-fit:contain;border-radius:8px;border:1px solid var(--b1);background:var(--card2);"/>
        <div class="fhint">Tempel URL gambar dari internet. Ukuran ideal: 300×400px (portrait).</div>
      </div>
      <div style="display:flex;gap:20px;margin-bottom:18px;">
        <label style="display:flex;align-items:center;gap:8px;font-size:.88rem;color:var(--t2);cursor:pointer;"><input type="checkbox" name="has_server_id" value="1">Butuh Server ID</label>
        <label style="display:flex;align-items:center;gap:8px;font-size:.88rem;color:var(--t2);cursor:pointer;"><input type="checkbox" name="is_popular" value="1">Tampilkan di Populer</label>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn-submit" style="flex:1;">Simpan Game</button>
        <button type="button" class="btn-ghost" data-modal-close="modal-add" style="flex:1;border-radius:10px;padding:13px;">Batal</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Edit -->
<div class="modal" id="modal-edit">
  <div class="modal-box">
    <div class="modal-head"><h3>Edit Produk</h3><button class="modal-close" data-modal-close="modal-edit">✕</button></div>
    <form method="POST">
      <input type="hidden" name="_token" value="<?=csrfToken()?>">
      <input type="hidden" name="action" value="edit">
      <input type="hidden" name="id" id="edit-id">
      <div class="fg"><label class="flabel">Kategori <span class="req">*</span></label>
        <select name="category_id" id="edit-cat" class="finput" required><?php foreach($cats as $c): ?><option value="<?=$c['id']?>"><?=htmlspecialchars($c['name'])?></option><?php endforeach; ?></select>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
        <div class="fg"><label class="flabel">Nama Game <span class="req">*</span></label><input type="text" name="name" id="edit-name" class="finput" required/></div>
        <div class="fg"><label class="flabel">Slug URL</label><input type="text" name="slug" id="edit-slug" class="finput"/></div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
        <div class="fg"><label class="flabel">Publisher</label><input type="text" name="publisher" id="edit-pub" class="finput"/></div>
        <div class="fg"><label class="flabel">Sort Order</label><input type="number" name="sort_order" id="edit-sort" class="finput"/></div>
      </div>
      <div class="fg">
        <label class="flabel">URL Gambar</label>
        <input type="url" name="image_url" id="edit-img" class="finput" placeholder="https://..." oninput="previewImg(this.value,'edit-prev')" style="font-size:.76rem;"/>
        <div id="edit-url-display" style="display:none;font-size:.66rem;color:var(--t3);word-break:break-all;margin-top:3px;padding:4px 8px;background:var(--card2);border-radius:4px;"></div>
        <img id="edit-prev" src="" style="display:none;margin-top:8px;max-height:120px;width:100%;object-fit:contain;border-radius:8px;border:1px solid var(--b1);background:var(--card2);"/>
        <div class="fhint">Tempel URL gambar dari internet. Ukuran ideal: 300×400px (portrait).</div>
      </div>
      <div style="display:flex;gap:20px;margin-bottom:18px;">
        <label style="display:flex;align-items:center;gap:8px;font-size:.88rem;color:var(--t2);cursor:pointer;"><input type="checkbox" name="has_server_id" id="edit-sid" value="1">Butuh Server ID</label>
        <label style="display:flex;align-items:center;gap:8px;font-size:.88rem;color:var(--t2);cursor:pointer;"><input type="checkbox" name="is_popular" id="edit-pop" value="1">Tampilkan di Populer</label>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn-submit" style="flex:1;">Simpan Perubahan</button>
        <button type="button" class="btn-ghost" data-modal-close="modal-edit" style="flex:1;border-radius:10px;padding:13px;">Batal</button>
      </div>
    </form>
  </div>
</div>

<script>
function openEditEl(btn){
  var id=btn.dataset.id,name=btn.dataset.name,slug=btn.dataset.slug,catId=btn.dataset.cat,pub=btn.dataset.pub,imgUrl=btn.dataset.img,sid=btn.dataset.sid,pop=btn.dataset.pop,sort=btn.dataset.sort;
  document.getElementById('edit-id').value   = id;
  document.getElementById('edit-name').value = name;
  document.getElementById('edit-slug').value = slug;
  document.getElementById('edit-cat').value  = catId;
  document.getElementById('edit-pub').value  = pub;
  document.getElementById('edit-img').value  = imgUrl;
  document.getElementById('edit-sort').value = sort;
  document.getElementById('edit-sid').checked = sid==1;
  document.getElementById('edit-pop').checked = pop==1;
  previewImg(imgUrl,'edit-prev');
  document.getElementById('modal-edit').classList.add('show');
}
function previewImg(url, id){
  const img = document.getElementById(id);
  const displayId = id.replace('-prev', '-url-display');
  const disp = document.getElementById(displayId);
  if(url && url.startsWith('http')){
    img.src = url;
    img.style.display = 'block';
    img.onerror = () => { img.style.display = 'none'; };
    if(disp){ disp.textContent = url; disp.style.display = 'block'; }
  } else {
    img.style.display = 'none';
    if(disp) disp.style.display = 'none';
  }
}
</script>
<?php include __DIR__.'/../includes/footer.php'; ?>