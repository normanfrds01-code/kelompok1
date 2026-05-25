<?php
require_once __DIR__.'/../includes/functions.php';
Security::boot(['super_admin']);
$pageTitle = 'Kelola Akun Pengunjung — Admin';

if($_SERVER['REQUEST_METHOD']==='POST'){
    verifyCsrf(); $act=$_POST['action']??'';

    if($act==='toggle_active'){
        $uid=(int)$_POST['id']; $curr=(int)$_POST['current'];
        db()->prepare("UPDATE users SET is_active=? WHERE id=? AND role='user'")->execute([$curr?0:1,$uid]);
        Security::audit('USER_TOGGLE_ACTIVE',"User #$uid is_active → ".($curr?0:1));
        setFlash('success','Status user diperbarui.');
    }
    elseif($act==='reset_password' && isSuperAdmin()){
        $uid=(int)$_POST['id']; $pass=$_POST['new_password']??'';
        if(strlen($pass)<8){ setFlash('error','Password minimal 8 karakter.'); }
        else{
            db()->prepare("UPDATE users SET password=?,password_changed_at=NOW() WHERE id=?")->execute([password_hash($pass,PASSWORD_BCRYPT,['cost'=>12]),$uid]);
            Security::audit('USER_PASSWORD_RESET',"Password user #$uid direset");
            setFlash('success','Password berhasil direset.');
        }
    }
    elseif($act==='delete' && isSuperAdmin()){
        $uid=(int)$_POST['id'];
        db()->prepare("UPDATE users SET is_active=0,email=CONCAT(email,'_deleted_',UNIX_TIMESTAMP()) WHERE id=? AND role='user'")->execute([$uid]);
        Security::audit('USER_DELETED',"User #$uid dihapus");
        setFlash('success','User dihapus.');
    }
    header('Location: '.asset('admin/users.php')); exit;
}

$page   = max(1,(int)($_GET['page']??1));
$limit  = 20; $offset = ($page-1)*$limit;
$q      = trim($_GET['q']??'');

// Hanya tampilkan role user (bukan admin/super_admin)
$where  = "WHERE u.role='user'";
$params = [];
if($q){
    $where  .= " AND (u.name LIKE ? OR u.email LIKE ?)";
    $params  = ["%$q%","%$q%"];
}

$totalStmt = db()->prepare("SELECT COUNT(*) FROM users u $where");
$totalStmt->execute($params);
$total = (int)$totalStmt->fetchColumn();
$pages = max(1, ceil($total/$limit));

$stmt = db()->prepare("
    SELECT u.*,
           (SELECT COUNT(*) FROM orders WHERE user_id=u.id) AS total_orders
    FROM users u $where
    ORDER BY u.created_at DESC
    LIMIT $limit OFFSET $offset
");
$stmt->execute($params);
$users = $stmt->fetchAll();

// Stats pengunjung
$totalActive   = (int)db()->query("SELECT COUNT(*) FROM users WHERE role='user' AND is_active=1")->fetchColumn();
$totalInactive = (int)db()->query("SELECT COUNT(*) FROM users WHERE role='user' AND is_active=0")->fetchColumn();
$totalAll      = $totalActive + $totalInactive;

include __DIR__.'/../includes/header.php';
?>
<div class="admin-wrap">
<?php include __DIR__.'/sidebar.php'; ?>
<div class="admin-main">

  <div class="admin-title" style="display:flex;align-items:center;gap:10px;margin-bottom:20px;">
    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
    Kelola Akun Pengunjung
  </div>

  <!-- Stats -->
  <div class="stats-grid" style="grid-template-columns:repeat(3,1fr);margin-bottom:20px;">
    <div class="stat-card">
      <div class="stat-label">Total Pengunjung</div>
      <div class="stat-val"><?=number_format($totalAll)?></div>
      <div class="stat-change">Semua akun</div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Akun Aktif</div>
      <div class="stat-val" style="color:var(--green);"><?=number_format($totalActive)?></div>
      <div class="stat-change">Bisa login</div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Akun Nonaktif</div>
      <div class="stat-val" style="color:var(--red);"><?=number_format($totalInactive)?></div>
      <div class="stat-change">Diblokir / dihapus</div>
    </div>
  </div>

  <!-- Filter -->
  <form method="GET" style="display:flex;gap:10px;margin-bottom:18px;flex-wrap:wrap;">
    <input type="text" name="q" value="<?=htmlspecialchars($q)?>" placeholder="Cari nama/email..." class="finput" style="flex:1;min-width:200px;max-width:340px;"/>
    <button type="submit" class="btn-gold" style="padding:10px 22px;font-size:.88rem;">Filter</button>
    <?php if($q): ?>
    <a href="<?=asset('admin/users.php')?>" class="btn-ghost" style="padding:10px 18px;display:flex;align-items:center;font-size:.84rem;">Reset</a>
    <?php endif; ?>
  </form>

  <div class="admin-card">
    <div class="admin-card-head">
      <h3>Daftar Pengunjung <span style="font-size:.78rem;color:var(--t3);font-weight:400;">(<?=number_format($total)?> total)</span></h3>
    </div>
    <div class="table-wrap">
      <table class="dtable">
        <thead>
          <tr><th>Pengunjung</th><th>Order</th><th>2FA</th><th>Last Login</th><th>Bergabung</th><th>Status</th><th>Aksi</th></tr>
        </thead>
        <tbody>
        <?php if(empty($users)): ?>
        <tr><td colspan="7" style="text-align:center;padding:44px;color:var(--t3);">Tidak ada pengunjung ditemukan.</td></tr>
        <?php else: foreach($users as $u): ?>
        <tr>
          <td>
            <div style="display:flex;align-items:center;gap:10px;">
              <?php if(!empty($u['avatar'])): ?>
              <img src="<?=htmlspecialchars($u['avatar'])?>" style="width:32px;height:32px;border-radius:50%;object-fit:cover;border:1px solid var(--b2);flex-shrink:0;"/>
              <?php else: ?>
              <div style="width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,#0ea5e9,#2dd4bf);display:flex;align-items:center;justify-content:center;font-family:var(--f-display);font-size:.82rem;font-weight:800;color:white;flex-shrink:0;">
                <?=strtoupper(substr($u['name'],0,1))?>
              </div>
              <?php endif; ?>
              <div>
                <div style="font-weight:600;font-size:.88rem;"><?=htmlspecialchars($u['name'])?></div>
                <div style="font-size:.73rem;color:var(--t3);"><?=htmlspecialchars($u['email'])?></div>
                <?php if($u['phone']): ?>
                <div style="font-size:.7rem;color:var(--t3);"><?=htmlspecialchars($u['phone'])?></div>
                <?php endif; ?>
              </div>
            </div>
          </td>
          <td style="font-weight:600;color:var(--t1);"><?=number_format($u['total_orders'])?></td>
          <td style="text-align:center;">
            <?=$u['two_fa_enabled']??0?'<span class="badge badge-success" style="font-size:.68rem;">ON</span>':'<span style="color:var(--t3);font-size:.8rem;">—</span>'?>
          </td>
          <td style="font-size:.76rem;color:var(--t3);">
            <?=$u['last_login_at']?date('d/m/y H:i',strtotime($u['last_login_at'])):'—'?>
          </td>
          <td style="font-size:.76rem;color:var(--t3);"><?=date('d M Y',strtotime($u['created_at']))?></td>
          <td>
            <?php if($u['is_active']): ?>
            <span class="badge badge-success" style="font-size:.68rem;">Aktif</span>
            <?php else: ?>
            <span class="badge badge-failed" style="font-size:.68rem;">Nonaktif</span>
            <?php endif; ?>
          </td>
          <td>
            <div style="display:flex;gap:5px;flex-wrap:wrap;">
              <!-- Toggle aktif/nonaktif -->
              <form method="POST" style="display:inline;">
                <input type="hidden" name="_token" value="<?=csrfToken()?>">
                <input type="hidden" name="action" value="toggle_active">
                <input type="hidden" name="id" value="<?=$u['id']?>">
                <input type="hidden" name="current" value="<?=$u['is_active']?>">
                <button class="btn-sm <?=$u['is_active']?'btn-sm-danger':'btn-sm-edit'?>" style="font-size:.73rem;">
                  <?=$u['is_active']?'Nonaktifkan':'Aktifkan'?>
                </button>
              </form>
              <!-- Reset password -->
              <button class="btn-sm btn-sm-edit" style="font-size:.73rem;"
                data-uid="<?=(int)$u['id']?>"
                data-uname="<?=htmlspecialchars($u['name'],ENT_QUOTES)?>"
                onclick="openResetPwEl(this)">
                Reset PW
              </button>
              <!-- Hapus -->
              <form method="POST" style="display:inline;">
                <input type="hidden" name="_token" value="<?=csrfToken()?>">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?=$u['id']?>">
                <button type="submit" class="btn-sm btn-sm-danger" style="font-size:.73rem;"
                  data-uname="<?=htmlspecialchars($u['name'],ENT_QUOTES)?>"
                  onclick="return confirm('Hapus akun '+this.dataset.uname+' secara permanen?')">
                  Hapus
                </button>
              </form>
            </div>
          </td>
        </tr>
        <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>

    <?php if($pages>1): ?>
    <div style="display:flex;gap:5px;padding:16px 20px;flex-wrap:wrap;">
      <?php for($i=1;$i<=$pages;$i++): ?>
      <a href="?page=<?=$i?>&q=<?=urlencode($q)?>" style="padding:6px 12px;border-radius:6px;font-size:.82rem;<?=$i===$page?'background:var(--cyan);color:#03111f;font-weight:700;':'background:var(--card2);color:var(--t2);border:1px solid var(--b1);'?>"><?=$i?></a>
      <?php endfor; ?>
    </div>
    <?php endif; ?>
  </div>

</div>
</div>

<!-- Modal Reset Password -->
<div class="modal" id="modal-reset-pw">
  <div class="modal-box">
    <div class="modal-head">
      <h3>Reset Password Pengunjung</h3>
      <button class="modal-close" data-modal-close="modal-reset-pw">✕</button>
    </div>
    <p style="font-size:.84rem;color:var(--t2);margin-bottom:18px;">Reset password untuk: <strong id="reset-name" style="color:var(--t1);"></strong></p>
    <form method="POST">
      <input type="hidden" name="_token" value="<?=csrfToken()?>">
      <input type="hidden" name="action" value="reset_password">
      <input type="hidden" name="id" id="reset-id">
      <div class="fg">
        <label class="flabel">Password Baru <span class="req">*</span></label>
        <input type="password" name="new_password" class="finput" placeholder="Minimal 8 karakter" required minlength="8"/>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn-submit" style="flex:1;">Reset Password</button>
        <button type="button" class="btn-ghost" data-modal-close="modal-reset-pw" style="flex:1;border-radius:10px;padding:13px;">Batal</button>
      </div>
    </form>
  </div>
</div>

<script>
function openResetPwEl(btn){
  var id   = btn.dataset.uid;
  var name = btn.dataset.uname;
  document.getElementById('reset-id').value        = id;
  document.getElementById('reset-name').textContent = name;
  document.getElementById('modal-reset-pw').classList.add('show');
}
</script>

<?php include __DIR__.'/../includes/footer.php'; ?>