<?php
require_once __DIR__.'/../includes/functions.php';
Security::boot(['super_admin']);
$pageTitle = 'Kelola Admin — Admin';

if($_SERVER['REQUEST_METHOD']==='POST'){
    verifyCsrf(); $act=$_POST['action']??'';

    if($act==='add'){
        $name  = Security::cleanInput($_POST['name']??'');
        $email = strtolower(trim($_POST['email']??''));
        $pass  = $_POST['password']??'';
        $role  = $_POST['role']??'admin';

        if($role !== 'admin') $role='admin';

        $dup = db()->prepare("SELECT id FROM users WHERE email=?"); $dup->execute([$email]);
        if($dup->fetch()){ setFlash('error','Email sudah terdaftar.'); header('Location: '.asset('admin/admins.php')); exit; }

        if(strlen($pass)<8){ setFlash('error','Password minimal 8 karakter.'); header('Location: '.asset('admin/admins.php')); exit; }

        db()->prepare("INSERT INTO users (name,email,password,role,is_active,is_verified,email_verified_at) VALUES (?,?,?,?,1,1,NOW())")
           ->execute([$name,$email,password_hash($pass,PASSWORD_BCRYPT,['cost'=>12]),$role]);
        Security::audit('ADMIN_CREATED',"Admin baru: $name ($email) role: $role");
        setFlash('success',"Admin <strong>$name</strong> berhasil ditambahkan.");
    }

    elseif($act==='edit'){
        $id   = (int)$_POST['id'];
        $name = Security::cleanInput($_POST['name']??'');
        $role = $_POST['role']??'admin';
        if($role !== 'admin') $role='admin';
        // Jangan bisa ubah role diri sendiri
        if($id === (int)$_SESSION['user_id']){ setFlash('error','Tidak bisa mengubah role akun sendiri.'); header('Location: '.asset('admin/admins.php')); exit; }
        db()->prepare("UPDATE users SET name=?,role=? WHERE id=?")->execute([$name,$role,$id]);
        // Reset password jika diisi
        if(!empty($_POST['new_password'])){
            if(strlen($_POST['new_password'])<8){ setFlash('error','Password baru minimal 8 karakter.'); header('Location: '.asset('admin/admins.php')); exit; }
            db()->prepare("UPDATE users SET password=?,password_changed_at=NOW() WHERE id=?")
               ->execute([password_hash($_POST['new_password'],PASSWORD_BCRYPT,['cost'=>12]),$id]);
        }
        Security::audit('ADMIN_UPDATED',"Admin #$id diperbarui");
        setFlash('success','Data admin berhasil diperbarui.');
    }

    elseif($act==='toggle'){
        $id=(int)$_POST['id']; $curr=(int)$_POST['current'];
        if($id===(int)$_SESSION['user_id']){ setFlash('error','Tidak bisa menonaktifkan akun sendiri.'); header('Location: '.asset('admin/admins.php')); exit; }
        db()->prepare("UPDATE users SET is_active=? WHERE id=?")->execute([$curr?0:1,$id]);
        setFlash('success','Status admin diperbarui.');
    }

    elseif($act==='delete'){
        $id=(int)$_POST['id'];
        if($id===(int)$_SESSION['user_id']){ setFlash('error','Tidak bisa menghapus akun sendiri.'); header('Location: '.asset('admin/admins.php')); exit; }
        db()->prepare("UPDATE users SET is_active=0,role='user' WHERE id=?")->execute([$id]);
        Security::audit('ADMIN_DELETED',"Admin #$id dihapus");
        setFlash('success','Admin berhasil dihapus.');
    }

    header('Location: '.asset('admin/admins.php')); exit;
}

$admins = db()->query("SELECT id,name,email,role,is_active,last_login_at,created_at FROM users WHERE role='admin' ORDER BY name ASC")->fetchAll();
include __DIR__.'/../includes/header.php';
?>
<div class="admin-wrap">
<?php include __DIR__.'/sidebar.php'; ?>
<div class="admin-main">

  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:22px;">
    <div class="admin-title" style="margin:0;display:flex;align-items:center;gap:10px;">
      <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
      Kelola Admin
    </div>
    <button class="btn-gold" data-modal-open="modal-add" style="padding:9px 20px;font-size:.88rem;display:flex;align-items:center;gap:7px;">
      <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      Tambah Admin
    </button>
  </div>

  <!-- Info -->
  <div style="background:rgba(124,58,237,.06);border:1px solid rgba(124,58,237,.15);border-radius:var(--r);padding:12px 16px;margin-bottom:20px;font-size:.8rem;color:#c4b5fd;display:flex;gap:10px;">
    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;margin-top:1px;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    <span>Kelola akun <strong>Admin Operasional</strong> di sini. Akun Super Admin tidak ditampilkan dan tidak dapat diubah dari halaman ini.</span>
  </div>

  <div class="admin-card">
    <div class="table-wrap">
      <table class="dtable">
        <thead><tr><th>Admin</th><th>Role</th><th>Status</th><th>Login Terakhir</th><th>Bergabung</th><th>Aksi</th></tr></thead>
        <tbody>
        <?php if(empty($admins)): ?>
        <tr><td colspan="6" style="text-align:center;padding:40px;color:var(--t3);">Belum ada admin.</td></tr>
        <?php else: foreach($admins as $a):
          $isSelf = $a['id']==$_SESSION['user_id'];
        ?>
        <tr>
          <td>
            <div style="display:flex;align-items:center;gap:10px;">
              <div style="width:34px;height:34px;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-weight:800;font-size:.85rem;
                <?=$a['role']==='super_admin'?'background:linear-gradient(135deg,#f59e0b,#d97706);color:#0a0817;':'background:linear-gradient(135deg,var(--violet),var(--violet2));color:#fff;'?>">
                <?=strtoupper(substr($a['name'],0,1))?>
              </div>
              <div>
                <div style="font-weight:600;font-size:.88rem;"><?=htmlspecialchars($a['name'])?> <?=$isSelf?'<span style="font-size:.67rem;color:var(--gold);font-weight:700;background:var(--gold-lo);border:1px solid var(--gold-md);border-radius:4px;padding:1px 6px;">Kamu</span>':''?></div>
                <div style="font-size:.72rem;color:var(--t3);"><?=htmlspecialchars($a['email'])?></div>
              </div>
            </div>
          </td>
          <td>
            <span class="badge <?=$a['role']==='super_admin'?'badge-pending':'badge-process'?>" style="font-size:.7rem;">
              <?=$a['role']==='super_admin'?'Super Admin':'Admin'?>
            </span>
          </td>
          <td>
            <?php if($a['is_active']): ?>
            <span class="badge badge-success" style="font-size:.7rem;">Aktif</span>
            <?php else: ?>
            <span class="badge badge-failed" style="font-size:.7rem;">Nonaktif</span>
            <?php endif; ?>
          </td>
          <td style="font-size:.78rem;color:var(--t3);">
            <?=$a['last_login_at']?date('d M Y H:i',strtotime($a['last_login_at'])):'—'?>
          </td>
          <td style="font-size:.78rem;color:var(--t3);"><?=date('d M Y',strtotime($a['created_at']))?></td>
          <td>
            <div style="display:flex;gap:6px;">
              <?php if(!$isSelf): ?>
              <button class="btn-sm btn-sm-edit" style="display:inline-flex;align-items:center;gap:4px;"
                data-id="<?=(int)$a['id']?>"
                data-name="<?=htmlspecialchars($a['name'],ENT_QUOTES)?>"
                data-role="<?=htmlspecialchars($a['role'],ENT_QUOTES)?>"
                onclick="openEditAdminEl(this)">
                <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                Edit
              </button>
              <form method="POST" style="display:inline;">
                <input type="hidden" name="_token" value="<?=csrfToken()?>">
                <input type="hidden" name="action" value="toggle">
                <input type="hidden" name="id" value="<?=$a['id']?>">
                <input type="hidden" name="current" value="<?=$a['is_active']?>">
                <button class="btn-sm <?=$a['is_active']?'btn-sm-danger':'btn-sm-edit'?>" style="display:inline-flex;align-items:center;gap:4px;">
                  <?php if($a['is_active']): ?>
                  <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="6" y="4" width="4" height="16"/><rect x="14" y="4" width="4" height="16"/></svg> Nonaktif
                  <?php else: ?>
                  <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polygon points="5 3 19 12 5 21 5 3"/></svg> Aktifkan
                  <?php endif; ?>
                </button>
              </form>
              <form method="POST" style="display:inline;">
                <input type="hidden" name="_token" value="<?=csrfToken()?>">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?=$a['id']?>">
                <button type="submit" class="btn-sm btn-sm-danger btn-delete-confirm" style="display:inline-flex;align-items:center;gap:4px;">
                  <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg> Hapus
                </button>
              </form>
              <?php else: ?>
              <span style="font-size:.72rem;color:var(--t3);padding:5px 8px;">—</span>
              <?php endif; ?>
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
    <div class="modal-head">
      <h3>Tambah Admin Baru</h3>
      <button class="modal-close" data-modal-close="modal-add">✕</button>
    </div>
    <form method="POST">
      <input type="hidden" name="_token" value="<?=csrfToken()?>">
      <input type="hidden" name="action" value="add">
      <div class="fg"><label class="flabel">Nama Lengkap <span class="req">*</span></label><input type="text" name="name" class="finput" required/></div>
      <div class="fg"><label class="flabel">Email <span class="req">*</span></label><input type="email" name="email" class="finput" required/></div>
      <div class="fg"><label class="flabel">Password <span class="req">*</span></label>
        <input type="password" name="password" class="finput" required minlength="8"/>
        <div class="fhint">Minimal 8 karakter</div>
      </div>
      <div class="fg"><label class="flabel">Role <span class="req">*</span></label>
        <select name="role" class="finput">
          <option value="admin">Admin — Operasional</option>
        </select>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn-submit" style="flex:1;">Simpan Admin</button>
        <button type="button" class="btn-ghost" data-modal-close="modal-add" style="flex:1;border-radius:10px;padding:13px;">Batal</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Edit -->
<div class="modal" id="modal-edit">
  <div class="modal-box">
    <div class="modal-head">
      <h3>Edit Admin</h3>
      <button class="modal-close" data-modal-close="modal-edit">✕</button>
    </div>
    <form method="POST">
      <input type="hidden" name="_token" value="<?=csrfToken()?>">
      <input type="hidden" name="action" value="edit">
      <input type="hidden" name="id" id="edit-id">
      <div class="fg"><label class="flabel">Nama Lengkap</label><input type="text" name="name" id="edit-name" class="finput" required/></div>
      <div class="fg"><label class="flabel">Role</label>
        <select name="role" id="edit-role" class="finput">
          <option value="admin">Admin — Operasional</option>
        </select>
      </div>
      <div class="fg"><label class="flabel">Password Baru <span style="color:var(--t3);font-weight:400">(kosongkan jika tidak diubah)</span></label>
        <input type="password" name="new_password" class="finput" minlength="8" placeholder="Min. 8 karakter"/>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn-submit" style="flex:1;">Simpan Perubahan</button>
        <button type="button" class="btn-ghost" data-modal-close="modal-edit" style="flex:1;border-radius:10px;padding:13px;">Batal</button>
      </div>
    </form>
  </div>
</div>

<script>
function openEditAdminEl(btn){
  document.getElementById('edit-id').value   = btn.dataset.id;
  document.getElementById('edit-name').value = btn.dataset.name;
  document.getElementById('edit-role').value = btn.dataset.role;
  document.getElementById('modal-edit').classList.add('show');
}
</script>
<?php include __DIR__.'/../includes/footer.php'; ?>