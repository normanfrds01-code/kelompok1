<?php
require_once __DIR__.'/../includes/functions.php';
Security::boot(['admin']);
$pageTitle = 'Kelola Kategori — Admin';

if($_SERVER['REQUEST_METHOD']==='POST'){
    verifyCsrf(); $act=$_POST['action']??'';

    if($act==='add'){
        $name = trim($_POST['name']??'');
        $slug = strtolower(preg_replace('/[^a-z0-9]+/','-',trim($_POST['slug']??$name)));
        $slug = trim($slug,'-');
        $sort = (int)($_POST['sort_order']??0);
        $icon = trim($_POST['icon']??'');
        if(!$name){ setFlash('error','Nama kategori wajib diisi.'); }
        else {
            $check = db()->prepare("SELECT id FROM categories WHERE slug=?"); $check->execute([$slug]);
            if($check->fetch()) $slug .= '-'.substr(uniqid(),-4);
            db()->prepare("INSERT INTO categories (name,slug,sort_order,icon,is_active) VALUES (?,?,?,?,1)")
               ->execute([$name,$slug,$sort,$icon?:null]);
            setFlash('success','Kategori berhasil ditambahkan.');
        }
    }
    elseif($act==='edit'){
        $id     = (int)$_POST['id'];
        $name   = trim($_POST['name']??'');
        $slug   = strtolower(preg_replace('/[^a-z0-9]+/','-',trim($_POST['slug']??$name)));
        $slug   = trim($slug,'-');
        $sort   = (int)($_POST['sort_order']??0);
        $icon   = trim($_POST['icon']??'');
        $active = (int)($_POST['is_active']??1);
        if(!$name){ setFlash('error','Nama kategori wajib diisi.'); }
        else {
            $check = db()->prepare("SELECT id FROM categories WHERE slug=? AND id!=?"); $check->execute([$slug,$id]);
            if($check->fetch()) $slug .= '-'.substr(uniqid(),-4);
            db()->prepare("UPDATE categories SET name=?,slug=?,sort_order=?,icon=?,is_active=? WHERE id=?")
               ->execute([$name,$slug,$sort,$icon?:null,$active,$id]);
            setFlash('success','Kategori berhasil diperbarui.');
        }
    }
    elseif($act==='delete'){
        $id   = (int)$_POST['id'];
        $stmt = db()->prepare("SELECT COUNT(*) FROM games WHERE category_id=?");
        $stmt->execute([$id]); $gameCount = (int)$stmt->fetchColumn();
        if($gameCount > 0){
            setFlash('error',"Tidak bisa hapus: ada $gameCount game di kategori ini.");
        } else {
            db()->prepare("DELETE FROM categories WHERE id=?")->execute([$id]);
            setFlash('success','Kategori dihapus.');
        }
    }
    header('Location: '.asset('admin/categories.php')); exit;
}

$cats = db()->query("SELECT c.*, (SELECT COUNT(*) FROM games WHERE category_id=c.id AND is_active=1) AS game_count FROM categories c ORDER BY c.sort_order ASC, c.name ASC")->fetchAll();
include __DIR__.'/../includes/header.php';
?>
<div class="admin-wrap">
<?php include __DIR__.'/sidebar.php'; ?>
<div class="admin-main">

  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:22px;">
    <div class="admin-title" style="margin:0;">
      <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
      Kelola Kategori
    </div>
    <button class="btn-gold" data-modal-open="modal-add" style="padding:9px 20px;font-size:.88rem;">+ Tambah Kategori</button>
  </div>

  <div class="admin-card">
    <div class="admin-card-head">
      <h3>Daftar Kategori <span style="font-size:.78rem;color:var(--t3);font-weight:400;">(<?=count($cats)?> kategori)</span></h3>
    </div>
    <div class="table-wrap">
      <table class="dtable">
        <thead><tr><th>Nama</th><th>Slug</th><th>Icon</th><th>Jumlah Game</th><th style="text-align:center">Urutan</th><th>Status</th><th>Aksi</th></tr></thead>
        <tbody>
        <?php if(empty($cats)): ?>
        <tr><td colspan="7" style="text-align:center;padding:48px;color:var(--t3);">Belum ada kategori.</td></tr>
        <?php else: foreach($cats as $c): ?>
        <tr>
          <td><div style="font-weight:600;"><?=htmlspecialchars($c['name'])?></div></td>
          <td style="font-family:monospace;font-size:.8rem;color:var(--t3);"><?=htmlspecialchars($c['slug'])?></td>
          <td style="font-size:1.2rem;"><?=$c['icon']?htmlspecialchars($c['icon']):'—'?></td>
          <td>
            <span class="badge badge-process" style="font-size:.72rem;"><?=$c['game_count']?> game</span>
            <?php if($c['game_count']>0): ?><a href="<?=asset('admin/games.php')?>" style="font-size:.72rem;color:var(--cyan);margin-left:4px;">Lihat →</a><?php endif; ?>
          </td>
          <td style="text-align:center;color:var(--t2);"><?=$c['sort_order']?></td>
          <td><?=$c['is_active']?'<span class="badge badge-success" style="font-size:.69rem;">Aktif</span>':'<span class="badge badge-failed" style="font-size:.69rem;">Nonaktif</span>'?></td>
          <td>
            <div style="display:flex;gap:5px;">
              <!-- Edit: data attributes, no PHP in onclick -->
              <button type="button" class="btn-sm btn-sm-edit"
                data-id="<?=(int)$c['id']?>"
                data-name="<?=htmlspecialchars($c['name'], ENT_QUOTES)?>"
                data-slug="<?=htmlspecialchars($c['slug'], ENT_QUOTES)?>"
                data-icon="<?=htmlspecialchars($c['icon']??'', ENT_QUOTES)?>"
                data-sort="<?=(int)$c['sort_order']?>"
                data-active="<?=(int)$c['is_active']?>"
                onclick="openEditCatEl(this)">✏️ Edit</button>
              <!-- Hapus: gunakan shared form di luar tabel agar tidak ada form-nesting issue -->
              <button type="button" class="btn-sm btn-sm-danger"
                onclick="deleteCat(<?=(int)$c['id']?>)">🗑️</button>
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

<!-- Form hapus — satu form shared di luar tabel, dikirim via JS -->
<form id="form-delete-cat" method="POST" style="display:none;">
  <input type="hidden" name="_token" value="<?=csrfToken()?>">
  <input type="hidden" name="action" value="delete">
  <input type="hidden" name="id" id="delete-cat-id" value="">
</form>

<!-- Modal Tambah -->
<div class="modal" id="modal-add">
  <div class="modal-box">
    <div class="modal-head"><h3>+ Tambah Kategori</h3><button class="modal-close" data-modal-close="modal-add">✕</button></div>
    <form method="POST">
      <input type="hidden" name="_token" value="<?=csrfToken()?>">
      <input type="hidden" name="action" value="add">
      <div class="fg"><label class="flabel">Nama Kategori <span class="req">*</span></label><input type="text" name="name" class="finput" placeholder="Contoh: Top Up Games" required/></div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
        <div class="fg"><label class="flabel">Slug</label><input type="text" name="slug" class="finput" placeholder="auto-generate"/></div>
        <div class="fg"><label class="flabel">Icon (emoji)</label><input type="text" name="icon" class="finput" placeholder="🎮"/></div>
      </div>
      <div class="fg"><label class="flabel">Sort Order</label><input type="number" name="sort_order" class="finput" value="0"/></div>
      <div class="modal-footer">
        <button type="submit" class="btn-submit" style="flex:1;">Simpan</button>
        <button type="button" class="btn-ghost" data-modal-close="modal-add" style="flex:1;border-radius:10px;padding:13px;">Batal</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Edit -->
<div class="modal" id="modal-edit">
  <div class="modal-box">
    <div class="modal-head"><h3>✏️ Edit Kategori</h3><button class="modal-close" data-modal-close="modal-edit">✕</button></div>
    <form method="POST">
      <input type="hidden" name="_token" value="<?=csrfToken()?>">
      <input type="hidden" name="action" value="edit">
      <input type="hidden" name="id" id="ec-id">
      <div class="fg"><label class="flabel">Nama Kategori <span class="req">*</span></label><input type="text" name="name" id="ec-name" class="finput" required/></div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
        <div class="fg"><label class="flabel">Slug</label><input type="text" name="slug" id="ec-slug" class="finput"/></div>
        <div class="fg"><label class="flabel">Icon (emoji)</label><input type="text" name="icon" id="ec-icon" class="finput"/></div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
        <div class="fg"><label class="flabel">Sort Order</label><input type="number" name="sort_order" id="ec-sort" class="finput"/></div>
        <div class="fg" style="display:flex;align-items:flex-end;padding-bottom:2px;">
          <label style="display:flex;align-items:center;gap:8px;font-size:.88rem;color:var(--t2);cursor:pointer;">
            <input type="checkbox" name="is_active" id="ec-active" value="1"> Aktif
          </label>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn-submit" style="flex:1;">Simpan Perubahan</button>
        <button type="button" class="btn-ghost" data-modal-close="modal-edit" style="flex:1;border-radius:10px;padding:13px;">Batal</button>
      </div>
    </form>
  </div>
</div>

<script>
function deleteCat(id){
  if(!confirm('Hapus kategori ini?')) return;
  document.getElementById('delete-cat-id').value = id;
  document.getElementById('form-delete-cat').submit();
}

function openEditCatEl(btn){
  document.getElementById('ec-id').value       = btn.dataset.id;
  document.getElementById('ec-name').value     = btn.dataset.name;
  document.getElementById('ec-slug').value     = btn.dataset.slug;
  document.getElementById('ec-icon').value     = btn.dataset.icon;
  document.getElementById('ec-sort').value     = btn.dataset.sort;
  document.getElementById('ec-active').checked = btn.dataset.active === '1';
  document.getElementById('modal-edit').classList.add('show');
}
</script>
<?php include __DIR__.'/../includes/footer.php'; ?>