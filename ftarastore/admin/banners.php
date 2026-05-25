<?php
require_once __DIR__.'/../includes/functions.php';
Security::boot(['admin']);
$pageTitle = 'Kelola Banner — Admin';

// Pastikan folder upload ada
$uploadDir = __DIR__.'/../assets/images/banners/';
if(!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

if($_SERVER['REQUEST_METHOD']==='POST'){
    verifyCsrf();
    $act = $_POST['action']??'';

    //  TAMBAH BANNER 
    if($act==='add'){
        $title     = Security::cleanInput($_POST['title']??'');
        $link_url  = Security::cleanInput($_POST['link_url']??'');
        $sort      = (int)($_POST['sort_order']??0);
        $image_url = '';

        // Upload file
        if(!empty($_FILES['image']['name'])){
            $errs = Security::validateUpload($_FILES['image']);
            if($errs){ setFlash('error', implode(' ', $errs)); header('Location: '.asset('admin/banners.php')); exit; }

            $ext      = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $filename = 'banner_'.time().'_'.uniqid().'.'.$ext;
            $dest     = $uploadDir.$filename;

            if(move_uploaded_file($_FILES['image']['tmp_name'], $dest)){
                $image_url = asset('assets/images/banners/'.$filename);
            } else {
                setFlash('error','Gagal upload gambar. Cek permission folder.');
                header('Location: '.asset('admin/banners.php')); exit;
            }
        } elseif(!empty($_POST['image_url'])){
            $image_url = Security::cleanInput($_POST['image_url']);
        } else {
            setFlash('error','Gambar wajib diisi (upload file atau masukkan URL).');
            header('Location: '.asset('admin/banners.php')); exit;
        }

        db()->prepare("INSERT INTO banners (title,image_url,link_url,sort_order,is_active) VALUES (?,?,?,?,1)")
           ->execute([$title,$image_url,$link_url,$sort]);
        audit('BANNER_CREATED', "Banner baru: $title");
        setFlash('success','Banner berhasil ditambahkan.');
    }

    //  EDIT BANNER 
    elseif($act==='edit'){
        $id        = (int)$_POST['id'];
        $title     = Security::cleanInput($_POST['title']??'');
        $link_url  = Security::cleanInput($_POST['link_url']??'');
        $sort      = (int)($_POST['sort_order']??0);
        $is_active = (int)($_POST['is_active']??1);

        // Get existing
        $ex = db()->prepare("SELECT image_url FROM banners WHERE id=?"); $ex->execute([$id]); $existing=$ex->fetch();
        $image_url = $existing['image_url']??'';

        // Upload baru jika ada
        if(!empty($_FILES['image']['name'])){
            $errs = Security::validateUpload($_FILES['image']);
            if($errs){ setFlash('error',implode(' ',$errs)); header('Location: '.asset('admin/banners.php')); exit; }
            $ext      = strtolower(pathinfo($_FILES['image']['name'],PATHINFO_EXTENSION));
            $filename = 'banner_'.time().'_'.uniqid().'.'.$ext;
            if(move_uploaded_file($_FILES['image']['tmp_name'],$uploadDir.$filename)){
                // Hapus gambar lama jika file lokal
                if($existing && strpos($existing['image_url'],'banners/')!==false){
                    $oldFile = $uploadDir.basename($existing['image_url']);
                    if(file_exists($oldFile)) unlink($oldFile);
                }
                $image_url = asset('assets/images/banners/'.$filename);
            }
        } elseif(!empty($_POST['image_url'])){
            $image_url = Security::cleanInput($_POST['image_url']);
        }

        db()->prepare("UPDATE banners SET title=?,image_url=?,link_url=?,sort_order=?,is_active=? WHERE id=?")
           ->execute([$title,$image_url,$link_url,$sort,$is_active,$id]);
        audit('BANNER_UPDATED',"Banner #$id diperbarui");
        setFlash('success','Banner berhasil diperbarui.');
    }

    //  TOGGLE AKTIF 
    elseif($act==='toggle'){
        $id=(int)$_POST['id']; $curr=(int)$_POST['current'];
        db()->prepare("UPDATE banners SET is_active=? WHERE id=?")->execute([$curr?0:1,$id]);
        setFlash('success','Status banner diperbarui.');
    }

    //  HAPUS 
    elseif($act==='delete'){
        $id=(int)$_POST['id'];
        $ex=db()->prepare("SELECT image_url FROM banners WHERE id=?");$ex->execute([$id]);$b=$ex->fetch();
        if($b && strpos($b['image_url'],'banners/')!==false){
            $f=$uploadDir.basename($b['image_url']);
            if(file_exists($f)) unlink($f);
        }
        db()->prepare("DELETE FROM banners WHERE id=?")->execute([$id]);
        audit('BANNER_DELETED',"Banner #$id dihapus");
        setFlash('success','Banner dihapus.');
    }

    header('Location: '.asset('admin/banners.php')); exit;
}

$banners = db()->query("SELECT * FROM banners ORDER BY sort_order ASC, id ASC")->fetchAll();
include __DIR__.'/../includes/header.php';
?>
<div class="admin-wrap">
<?php include __DIR__.'/sidebar.php'; ?>
<div class="admin-main">

  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:22px;">
    <div class="admin-title" style="margin:0;display:flex;align-items:center;gap:10px;">
      <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="m2 10 20 0"/></svg>
      Kelola Banner
    </div>
    <button class="btn-gold" data-modal-open="modal-add" style="padding:9px 20px;font-size:.88rem;display:flex;align-items:center;gap:7px;">
      <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      Tambah Banner
    </button>
  </div>

  <!-- Info box -->
  <div style="background:rgba(124,58,237,.06);border:1px solid rgba(124,58,237,.15);border-radius:var(--r);padding:13px 16px;margin-bottom:20px;font-size:.82rem;color:#c4b5fd;display:flex;align-items:flex-start;gap:10px;">
    <span></span>
    <div>
      Upload gambar banner atau masukkan URL gambar online.<br>
      <strong>Ukuran ideal: 1200×380px</strong> · Format: JPG, PNG, WebP · Maks 2MB<br>
      Banner aktif akan tampil sebagai slider di halaman utama.
    </div>
  </div>

  <?php if(empty($banners)): ?>
  <div style="background:var(--card);border:1px solid var(--b1);border-radius:var(--rl);padding:60px;text-align:center;color:var(--t3);">
    <div style="font-size:3rem;margin-bottom:12px;"></div>
    <p style="margin-bottom:16px;">Belum ada banner. Tambah banner pertama!</p>
    <button class="btn-gold" data-modal-open="modal-add" style="padding:10px 28px;">+ Tambah Banner</button>
  </div>
  <?php else: ?>

  <!-- Banner cards grid -->
  <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(340px,1fr));gap:16px;">
    <?php foreach($banners as $b): ?>
    <div style="background:var(--card);border:1px solid <?=$b['is_active']?'rgba(124,58,237,.25)':'var(--b1)'?>;border-radius:var(--rl);overflow:hidden;transition:border-color .2s;">

      <!-- Preview gambar -->
      <div style="position:relative;aspect-ratio:3/1;background:var(--card2);overflow:hidden;">
        <?php if($b['image_url']): ?>
          <img src="<?=htmlspecialchars($b['image_url'])?>" alt="<?=htmlspecialchars($b['title']??'')?>"
               style="width:100%;height:100%;object-fit:cover;"
               onerror="this.style.display='none';this.nextElementSibling.style.display='flex'"/>
          <div style="display:none;position:absolute;inset:0;align-items:center;justify-content:center;flex-direction:column;gap:6px;color:var(--t3);">
            <span style="font-size:1.5rem;"></span>
            <span style="font-size:.75rem;">Gambar tidak ditemukan</span>
          </div>
        <?php else: ?>
          <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:var(--t3);flex-direction:column;gap:6px;">
            <span style="font-size:1.5rem;"></span>
            <span style="font-size:.75rem;">Belum ada gambar</span>
          </div>
        <?php endif; ?>

        <!-- Status badge -->
        <div style="position:absolute;top:10px;right:10px;">
          <span style="padding:3px 10px;border-radius:20px;font-size:.7rem;font-weight:700;backdrop-filter:blur(8px);
            <?=$b['is_active']?'background:rgba(16,185,129,.8);color:#fff;':'background:rgba(239,68,68,.8);color:#fff;'?>">
            <?=$b['is_active']?' Aktif':' Nonaktif'?>
          </span>
        </div>

        <!-- Sort order -->
        <div style="position:absolute;top:10px;left:10px;">
          <span style="padding:3px 10px;border-radius:20px;font-size:.7rem;font-weight:700;background:rgba(0,0,0,.6);color:var(--gold);backdrop-filter:blur(8px);">
            #<?=$b['sort_order']?>
          </span>
        </div>
      </div>

      <!-- Info -->
      <div style="padding:14px 16px;">
        <div style="font-weight:700;font-size:.9rem;font-family:var(--f-display);margin-bottom:3px;">
          <?=htmlspecialchars($b['title']??'(Tanpa Judul)')?>
        </div>
        <?php if($b['link_url']): ?>
        <div style="font-size:.75rem;color:var(--t3);margin-bottom:8px;display:flex;align-items:center;gap:4px;">
          <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
          <?=htmlspecialchars($b['link_url'])?>
        </div>
        <?php endif; ?>

        <!-- Actions -->
        <div style="display:flex;gap:7px;flex-wrap:wrap;">
          <button class="btn-sm btn-sm-edit"
            data-id="<?=(int)$b['id']?>"
            data-title="<?=htmlspecialchars($b['title']??'')?>"
            data-img="<?=htmlspecialchars($b['image_url']??'')?>"
            data-link="<?=htmlspecialchars($b['link_url']??'')?>"
            data-sort="<?=(int)$b['sort_order']?>"
            data-active="<?=(int)$b['is_active']?>"
            onclick="openEditBannerEl(this)"
            style="padding:6px 14px;display:inline-flex;align-items:center;gap:5px;">
            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            Edit
          </button>

          <form method="POST" style="display:inline;">
            <input type="hidden" name="_token" value="<?=csrfToken()?>">
            <input type="hidden" name="action" value="toggle">
            <input type="hidden" name="id" value="<?=$b['id']?>">
            <input type="hidden" name="current" value="<?=$b['is_active']?>">
            <button class="btn-sm <?=$b['is_active']?'btn-sm-danger':'btn-sm-edit'?>" style="padding:6px 14px;display:inline-flex;align-items:center;gap:5px;">
              <?php if($b['is_active']): ?>
              <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="6" y="4" width="4" height="16"/><rect x="14" y="4" width="4" height="16"/></svg>
              Nonaktifkan
              <?php else: ?>
              <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polygon points="5 3 19 12 5 21 5 3"/></svg>
              Aktifkan
              <?php endif; ?>
            </button>
          </form>

          <form method="POST" style="display:inline;">
            <input type="hidden" name="_token" value="<?=csrfToken()?>">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?=$b['id']?>">
            <button type="submit" class="btn-sm btn-sm-danger btn-delete-confirm" style="padding:6px 14px;display:inline-flex;align-items:center;gap:5px;">
              <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
              Hapus
            </button>
          </form>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

</div>
</div>

<!--  MODAL TAMBAH  -->
<div class="modal" id="modal-add">
  <div class="modal-box">
    <div class="modal-head">
      <h3>Tambah Banner Baru</h3>
      <button class="modal-close" data-modal-close="modal-add">✕</button>
    </div>
    <form method="POST" enctype="multipart/form-data">
      <input type="hidden" name="_token" value="<?=csrfToken()?>">
      <input type="hidden" name="action" value="add">

      <div class="fg">
        <label class="flabel">Judul Banner <span style="color:var(--t3);font-weight:400">(opsional)</span></label>
        <input type="text" name="title" class="finput" placeholder="Contoh: Promo Mobile Legends"/>
      </div>

      <!-- Upload gambar -->
      <div class="fg">
        <label class="flabel">Upload Gambar <span style="font-size:.75rem;color:var(--t3)">(JPG/PNG/WebP, maks 2MB, 1200×380px)</span></label>
        <div style="border:2px dashed var(--b2);border-radius:10px;padding:20px;text-align:center;cursor:pointer;transition:border-color .2s;position:relative;"
             id="dropzone-add"
             ondragover="event.preventDefault();this.style.borderColor='var(--gold)'"
             ondragleave="this.style.borderColor='var(--b2)'"
             ondrop="handleDrop(event,'file-add','preview-add')">
          <input type="file" name="image" id="file-add" accept="image/*" style="position:absolute;inset:0;opacity:0;cursor:pointer;"
                 onchange="previewImage(this,'preview-add')"/>
          <img id="preview-add" src="" alt="" style="max-width:100%;max-height:120px;border-radius:8px;display:none;margin:0 auto 10px;"/>
          <div id="droptext-add">
            <div style="font-size:1.8rem;margin-bottom:6px;"></div>
            <div style="font-size:.85rem;color:var(--t2);font-weight:500;">Klik atau drag & drop gambar di sini</div>
            <div style="font-size:.75rem;color:var(--t3);margin-top:3px;">JPG, PNG, WebP · Maks 2MB</div>
          </div>
        </div>
      </div>

      <!-- Atau URL -->
      <div style="text-align:center;font-size:.8rem;color:var(--t3);margin:-6px 0 12px;">— atau masukkan URL gambar —</div>
      <div class="fg">
        <label class="flabel">URL Gambar Online</label>
        <input type="url" name="image_url" class="finput" placeholder="https://example.com/banner.jpg"/>
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
        <div class="fg">
          <label class="flabel">Link Tujuan <span style="color:var(--t3);font-weight:400">(opsional)</span></label>
          <input type="text" name="link_url" class="finput" placeholder="/game/mobile-legends"/>
        </div>
        <div class="fg">
          <label class="flabel">Urutan Tampil</label>
          <input type="number" name="sort_order" class="finput" value="1" min="0"/>
        </div>
      </div>

      <div class="modal-footer">
        <button type="submit" class="btn-submit" style="flex:1;">Simpan Banner</button>
        <button type="button" class="btn-ghost" data-modal-close="modal-add" style="flex:1;border-radius:var(--r);padding:13px;">Batal</button>
      </div>
    </form>
  </div>
</div>

<!--  MODAL EDIT  -->
<div class="modal" id="modal-edit">
  <div class="modal-box">
    <div class="modal-head">
      <h3>Edit Banner</h3>
      <button class="modal-close" data-modal-close="modal-edit">✕</button>
    </div>
    <form method="POST" enctype="multipart/form-data">
      <input type="hidden" name="_token" value="<?=csrfToken()?>">
      <input type="hidden" name="action" value="edit">
      <input type="hidden" name="id" id="edit-id">

      <div class="fg">
        <label class="flabel">Judul Banner</label>
        <input type="text" name="title" id="edit-title" class="finput"/>
      </div>

      <!-- Preview gambar saat ini -->
      <div class="fg">
        <label class="flabel">Gambar Saat Ini</label>
        <div style="background:var(--input);border:1px solid var(--b1);border-radius:8px;padding:10px;margin-bottom:10px;">
          <img id="edit-preview" src="" alt="" style="max-width:100%;max-height:100px;border-radius:6px;display:block;"/>
        </div>
        <label class="flabel">Ganti Gambar (opsional)</label>
        <input type="file" name="image" id="file-edit" accept="image/*" class="finput" style="padding:8px;"
               onchange="previewImage(this,'edit-preview')"/>
      </div>

      <div class="fg">
        <label class="flabel">Atau URL Gambar Baru</label>
        <input type="url" name="image_url" id="edit-imgurl" class="finput" placeholder="https://..."/>
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
        <div class="fg">
          <label class="flabel">Link Tujuan</label>
          <input type="text" name="link_url" id="edit-link" class="finput"/>
        </div>
        <div class="fg">
          <label class="flabel">Urutan</label>
          <input type="number" name="sort_order" id="edit-sort" class="finput" min="0"/>
        </div>
      </div>

      <div class="fg">
        <label class="flabel">Status</label>
        <select name="is_active" id="edit-active" class="finput">
          <option value="1">Aktif</option>
          <option value="0">Nonaktif</option>
        </select>
      </div>

      <div class="modal-footer">
        <button type="submit" class="btn-submit" style="flex:1;">Simpan Perubahan</button>
        <button type="button" class="btn-ghost" data-modal-close="modal-edit" style="flex:1;border-radius:var(--r);padding:13px;">Batal</button>
      </div>
    </form>
  </div>
</div>

<script>
function previewImage(input, previewId) {
  const preview = document.getElementById(previewId);
  if(input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = e => {
      preview.src = e.target.result;
      preview.style.display = 'block';
      const dt = document.getElementById('droptext-add');
      if(dt) dt.style.display = 'none';
    };
    reader.readAsDataURL(input.files[0]);
  }
}

function handleDrop(e, inputId, previewId) {
  e.preventDefault();
  document.getElementById('dropzone-add').style.borderColor = 'var(--b2)';
  const file = e.dataTransfer.files[0];
  if(!file) return;
  const input = document.getElementById(inputId);
  const dt = new DataTransfer();
  dt.items.add(file);
  input.files = dt.files;
  previewImage(input, previewId);
}

function openEditBannerEl(btn){
  document.getElementById('edit-id').value      = btn.dataset.id;
  document.getElementById('edit-title').value   = btn.dataset.title;
  document.getElementById('edit-img').value     = btn.dataset.img;
  document.getElementById('edit-link').value    = btn.dataset.link;
  document.getElementById('edit-sort').value    = btn.dataset.sort;
  document.getElementById('edit-active').value = btn.dataset.active;
  var prev = document.getElementById('edit-preview');
  if(prev && btn.dataset.img){ prev.src=btn.dataset.img; prev.style.display='block'; }
  else if(prev){ prev.style.display='none'; }
  var imgUrl = document.getElementById('edit-imgurl');
  if(imgUrl) imgUrl.value = btn.dataset.img || '';
  document.getElementById('modal-edit').classList.add('show');
}
</script>

<?php include __DIR__.'/../includes/footer.php'; ?>