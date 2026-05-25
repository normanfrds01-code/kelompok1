<?php
require_once __DIR__.'/../includes/functions.php';
Security::boot(['super_admin']);
$pageTitle = 'Pusat Keamanan — Admin';

if($_SERVER['REQUEST_METHOD']==='POST'){
    verifyCsrf(); $act=$_POST['action']??'';

    if($act==='block_ip'){
        $ip=trim($_POST['ip']??''); $reason=trim($_POST['reason']??''); $hrs=(int)($_POST['hours']??0);
        if(filter_var($ip,FILTER_VALIDATE_IP)){
            Security::blockIP($ip,$reason,$hrs?:null);
            setFlash('success',"IP $ip diblokir.");
        } else setFlash('error','IP tidak valid.');
    }
    elseif($act==='unblock_ip'){
        Security::unblockIP(trim($_POST['ip']??''));
        setFlash('success','IP berhasil dibuka.');
    }
    elseif($act==='clear_rate_limits'){
        db()->query("DELETE FROM rate_limits WHERE expires_at < NOW()");
        Security::audit('RATE_LIMITS_CLEARED','Rate limits expired dibersihkan');
        setFlash('success','Rate limits expired dibersihkan.');
    }
    //  2FA untuk akun sendiri 
    elseif($act==='enable_2fa'){
        $secret = Security::generate2FASecret();
        db()->prepare("UPDATE users SET two_fa_secret=?, two_fa_enabled=0 WHERE id=?")->execute([$secret, currentUser()['id']]);
        header('Location: '.asset('admin/security.php').'?tab=2fa&setup=1'); exit;
    }
    elseif($act==='confirm_2fa'){
        $code = trim($_POST['code']??'');
        $u = db()->prepare("SELECT two_fa_secret FROM users WHERE id=?"); $u->execute([currentUser()['id']]); $u=$u->fetch();
        if(Security::verify2FA($u['two_fa_secret']??'', $code)){
            db()->prepare("UPDATE users SET two_fa_enabled=1 WHERE id=?")->execute([currentUser()['id']]);
            $_SESSION['user']['two_fa_enabled'] = 1;
            Security::audit('2FA_ENABLED','Admin #'.currentUser()['id'].' aktifkan 2FA');
            setFlash('success','2FA berhasil diaktifkan!');
        } else {
            setFlash('error','Kode 2FA tidak valid. Coba lagi.');
        }
        header('Location: '.asset('admin/security.php').'?tab=2fa'); exit;
    }
    elseif($act==='disable_2fa'){
        db()->prepare("UPDATE users SET two_fa_enabled=0, two_fa_secret=NULL WHERE id=?")->execute([currentUser()['id']]);
        Security::audit('2FA_DISABLED','Admin #'.currentUser()['id'].' nonaktifkan 2FA');
        setFlash('success','2FA dinonaktifkan.');
        header('Location: '.asset('admin/security.php').'?tab=2fa'); exit;
    }
    header('Location: '.asset('admin/security.php').'?tab='.($_GET['tab']??'audit')); exit;
}

//  Data per tab 
$tab = trim($_GET['tab']??'audit');
$auditLogs=[]; $ipBlacklist=[]; $rateLimits=[];
$pages = 0; $page = 1; $q = ''; // init agar tidak undefined di template

if($tab==='audit'){
    $page=max(1,(int)($_GET['page']??1)); $limit=30; $offset=($page-1)*$limit;
    $q=trim($_GET['q']??'');
    $where=$q?"WHERE al.action LIKE ? OR al.description LIKE ? OR al.ip_address LIKE ?":'';
    $params=$q?["%$q%","%$q%","%$q%"]:[];
    $totalStmt=db()->prepare("SELECT COUNT(*) FROM audit_logs al $where"); $totalStmt->execute($params); $total=(int)$totalStmt->fetchColumn(); $pages=ceil($total/$limit);
    $stmt=db()->prepare("SELECT al.*,u.name AS uname,u.email AS uemail FROM audit_logs al LEFT JOIN users u ON u.id=al.user_id $where ORDER BY al.created_at DESC LIMIT $limit OFFSET $offset");
    $stmt->execute($params); $auditLogs=$stmt->fetchAll();
}
elseif($tab==='blacklist'){
    $ipBlacklist=db()->query("SELECT ib.*,u.name AS blocked_by_name FROM ip_blacklist ib LEFT JOIN users u ON u.id=ib.blocked_by ORDER BY ib.created_at DESC LIMIT 100")->fetchAll();
}
elseif($tab==='rate'){
    $rateLimits=db()->query("SELECT * FROM rate_limits WHERE expires_at >NOW() ORDER BY attempts DESC LIMIT 50")->fetchAll();
}

// Stats
$auditCount24h = (int)db()->query("SELECT COUNT(*) FROM audit_logs WHERE created_at >DATE_SUB(NOW(),INTERVAL 24 HOUR)")->fetchColumn();
$blockedIPs    = (int)db()->query("SELECT COUNT(*) FROM ip_blacklist WHERE expires_at IS NULL OR expires_at >NOW()")->fetchColumn();
$activeRL      = (int)db()->query("SELECT COUNT(*) FROM rate_limits WHERE expires_at >NOW()")->fetchColumn();
$loginFails    = (int)db()->query("SELECT COUNT(*) FROM audit_logs WHERE action='LOGIN_FAILED' AND created_at >DATE_SUB(NOW(),INTERVAL 24 HOUR)")->fetchColumn();

// Current user 2FA status
$me = db()->prepare("SELECT two_fa_secret,two_fa_enabled FROM users WHERE id=?"); $me->execute([currentUser()['id']]); $me=$me->fetch();
$setup2FA = isset($_GET['setup']);

include __DIR__.'/../includes/header.php';
?>
<div class="admin-wrap">
<?php include __DIR__.'/sidebar.php'; ?>
<div class="admin-main">

  <div class="admin-title">Pusat Keamanan</div>

  <!-- Stats -->
  <div class="stats-grid" style="margin-bottom:20px">
    <div class="stat-card"><div class="stat-label">Audit Events (24h)</div><div class="stat-val" style="color:var(--blue)"><?=number_format($auditCount24h)?></div></div>
    <div class="stat-card"><div class="stat-label">IP Diblokir</div><div class="stat-val" style="color:var(--red)"><?=number_format($blockedIPs)?></div></div>
    <div class="stat-card"><div class="stat-label">Rate Limits Aktif</div><div class="stat-val" style="color:var(--amber)"><?=number_format($activeRL)?></div></div>
    <div class="stat-card"><div class="stat-label">Login Gagal (24h)</div><div class="stat-val" style="color:var(--red)"><?=number_format($loginFails)?></div></div>
  </div>

  <!-- Tabs -->
  <div style="display:flex;gap:6px;margin-bottom:20px;flex-wrap:wrap;">
    <?php foreach(['audit'=>'Audit Log','blacklist'=>'IP Blacklist','rate'=>'Rate Limits','2fa'=>'2FA Akun Saya'] as $t=>$l): ?>
    <a href="?tab=<?=$t?>" style="padding:8px 16px;border-radius:8px;font-size:.84rem;font-weight:500;text-decoration:none;<?=$tab===$t?'background:var(--gold);color:#07060f;font-weight:700;':'background:var(--card);border:1px solid var(--b1);color:var(--t2);'?>"><?=$l?></a>
    <?php endforeach; ?>
  </div>

  <?php if($tab==='audit'): ?>
  <!--  AUDIT LOG  -->
  <form method="GET" style="display:flex;gap:10px;margin-bottom:16px;">
    <input type="hidden" name="tab" value="audit">
    <input type="text" name="q" value="<?=htmlspecialchars($q??'')?>" placeholder="Cari action, IP, deskripsi..." class="finput" style="flex:1;max-width:360px;"/>
    <button type="submit" class="btn-gold" style="padding:11px 18px;font-size:.88rem;">Cari</button>
  </form>
  <div class="admin-card"><div class="table-wrap">
    <table class="dtable">
      <thead><tr><th>Waktu</th><th>User</th><th>Action</th><th>Deskripsi</th><th>IP</th></tr></thead>
      <tbody>
      <?php if(empty($auditLogs)): ?>
        <tr><td colspan="5" style="text-align:center;padding:32px;color:var(--t3);">Belum ada audit log.</td></tr>
      <?php else: foreach($auditLogs as $l):
        $isWarn=in_array($l['action'],['LOGIN_FAILED','CSRF_VIOLATION','FORCE_LOGOUT','IP_BLOCKED','LOGIN_LOCKOUT']);
        $isOk=in_array($l['action'],['LOGIN_SUCCESS','LOGOUT']);
      ?>
      <tr>
        <td style="font-size:.74rem;color:var(--t3);white-space:nowrap;"><?=date('d/m/y H:i:s',strtotime($l['created_at']))?></td>
        <td style="font-size:.78rem;"><?=$l['uname']?htmlspecialchars($l['uname']).'<br><span style="font-size:.69rem;color:var(--t3)">'.htmlspecialchars($l['uemail']).'</span>':'<span style="color:var(--t3)">Guest</span>'?></td>
        <td><span style="font-family:monospace;font-size:.74rem;padding:2px 8px;border-radius:5px;<?=$isWarn?'background:rgba(239,68,68,.1);color:#fca5a5':($isOk?'background:rgba(16,185,129,.1);color:#34d399':'background:var(--card2);color:var(--t2)')?>"><?=htmlspecialchars($l['action'])?></span></td>
        <td style="font-size:.8rem;color:var(--t2);max-width:260px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?=htmlspecialchars($l['description']??'')?></td>
        <td style="font-family:monospace;font-size:.74rem;color:var(--t3);"><?=htmlspecialchars($l['ip_address']??'')?></td>
      </tr>
      <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
  <?php if(($pages??0)>1): ?>
  <div style="display:flex;gap:5px;padding:14px 20px;flex-wrap:wrap;">
    <?php for($i=1;$i<=$pages;$i++): ?>
    <a href="?tab=audit&page=<?=$i?>&q=<?=urlencode($q??'')?>" style="padding:5px 11px;border-radius:6px;font-size:.8rem;<?=$i===$page?'background:var(--gold);color:#07060f;font-weight:700;':'background:var(--card2);color:var(--t2);border:1px solid var(--b1);'?>"><?=$i?></a>
    <?php endfor; ?>
  </div>
  <?php endif; ?>
  </div>

  <?php elseif($tab==='blacklist'): ?>
  <!--  IP BLACKLIST  -->
  <div class="admin-card" style="padding:20px;margin-bottom:16px;">
    <div style="font-size:.9rem;font-weight:700;margin-bottom:14px;color:var(--t1);">Blokir IP Baru</div>
    <form method="POST" style="display:grid;grid-template-columns:1fr 1fr 1fr auto;gap:12px;align-items:end;">
      <input type="hidden" name="_token" value="<?=csrfToken()?>">
      <input type="hidden" name="action" value="block_ip">
      <div class="fg" style="margin:0;"><label class="flabel">IP Address</label><input type="text" name="ip" class="finput" placeholder="192.168.1.1" required/></div>
      <div class="fg" style="margin:0;"><label class="flabel">Alasan</label><input type="text" name="reason" class="finput" placeholder="Brute force, spam..."/></div>
      <div class="fg" style="margin:0;"><label class="flabel">Durasi (jam, kosong=permanen)</label><input type="number" name="hours" class="finput" placeholder="24"/></div>
      <button type="submit" class="btn-submit" style="height:42px;">Blokir</button>
    </form>
  </div>
  <div class="admin-card"><div class="table-wrap">
    <table class="dtable">
      <thead><tr><th>IP Address</th><th>Alasan</th><th>Diblokir Oleh</th><th>Expires</th><th>Waktu</th><th>Aksi</th></tr></thead>
      <tbody>
      <?php if(empty($ipBlacklist)): ?>
        <tr><td colspan="6" style="text-align:center;padding:32px;color:var(--t3);">Tidak ada IP yang diblokir.</td></tr>
      <?php else: foreach($ipBlacklist as $ip): ?>
      <tr>
        <td style="font-family:monospace;color:var(--red);font-size:.88rem;"><?=htmlspecialchars($ip['ip'])?></td>
        <td style="font-size:.82rem;color:var(--t2);"><?=htmlspecialchars($ip['reason']??'—')?></td>
        <td style="font-size:.8rem;color:var(--t3);"><?=htmlspecialchars($ip['blocked_by_name']??'System')?></td>
        <td style="font-size:.78rem;"><?=$ip['expires_at']?date('d/m/y H:i',strtotime($ip['expires_at'])):'<span style="color:var(--red)">Permanen</span>'?></td>
        <td style="font-size:.74rem;color:var(--t3);"><?=date('d/m/y H:i',strtotime($ip['created_at']))?></td>
        <td>
          <form method="POST" style="display:inline;">
            <input type="hidden" name="_token" value="<?=csrfToken()?>">
            <input type="hidden" name="action" value="unblock_ip">
            <input type="hidden" name="ip" value="<?=htmlspecialchars($ip['ip'])?>">
            <button class="btn-sm btn-sm-edit" onclick="return confirm('Buka blokir IP ini?')">Buka Blokir</button>
          </form>
        </td>
      </tr>
      <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div></div>

  <?php elseif($tab==='rate'): ?>
  <!--  RATE LIMITS  -->
  <div style="display:flex;justify-content:flex-end;margin-bottom:12px;">
    <form method="POST">
      <input type="hidden" name="_token" value="<?=csrfToken()?>">
      <input type="hidden" name="action" value="clear_rate_limits">
      <button type="submit" class="btn-sm btn-sm-danger" onclick="return confirm('Bersihkan rate limits yang sudah expired?')" style="padding:8px 16px;font-size:.85rem;">Bersihkan Expired</button>
    </form>
  </div>
  <div class="admin-card"><div class="table-wrap">
    <table class="dtable">
      <thead><tr><th>Key</th><th style="text-align:center">Attempts</th><th>Expires</th></tr></thead>
      <tbody>
      <?php if(empty($rateLimits)): ?>
        <tr><td colspan="3" style="text-align:center;padding:32px;color:var(--t3);">Tidak ada rate limit aktif.</td></tr>
      <?php else: foreach($rateLimits as $r): $hi=$r['attempts']>=5; ?>
      <tr>
        <td style="font-family:monospace;font-size:.78rem;color:var(--t2);"><?=htmlspecialchars($r['key'])?></td>
        <td style="text-align:center;"><span style="<?=$hi?'color:var(--red);font-weight:700;':'color:var(--t2);'?>"><?=$r['attempts']?></span></td>
        <td style="font-size:.78rem;color:var(--t3);"><?=date('d/m/y H:i:s',strtotime($r['expires_at']))?></td>
      </tr>
      <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div></div>

  <?php elseif($tab==='2fa'): ?>
  <!--  2FA AKUN SAYA  -->
  <div class="admin-card" style="max-width:520px;">
    <div class="admin-card-head"><h3>Autentikasi Dua Faktor (2FA)</h3></div>
    <div style="padding:24px;">

      <!-- Status bar -->
      <div style="display:flex;align-items:center;justify-content:space-between;padding:16px;border-radius:12px;margin-bottom:24px;<?=($me['two_fa_enabled']??0)?'background:rgba(16,185,129,.06);border:1px solid rgba(16,185,129,.2);':'background:var(--card2);border:1px solid var(--b1);'?>">
        <div>
          <div style="font-weight:700;font-size:.95rem;"><?=($me['two_fa_enabled']??0)?'2FA Aktif':'2FA Tidak Aktif'?></div>
          <div style="font-size:.78rem;color:var(--t3);margin-top:3px;"><?=($me['two_fa_enabled']??0)?'Akun kamu terlindungi dengan verifikasi 2 langkah':'Aktifkan untuk keamanan lebih tinggi'?></div>
        </div>
        <span class="badge <?=($me['two_fa_enabled']??0)?'badge-success':'badge-pending'?>" style="font-size:.75rem;"><?=($me['two_fa_enabled']??0)?'ON':'OFF'?></span>
      </div>

      <?php if($setup2FA && !($me['two_fa_enabled']??0) && !empty($me['two_fa_secret'])): ?>
      <!-- Setup: scan QR -->
      <p style="font-size:.84rem;color:var(--t2);margin-bottom:16px;line-height:1.6;">Scan QR code di bawah dengan <strong>Google Authenticator</strong>, <strong>Authy</strong>, atau aplikasi TOTP lainnya:</p>
      <?php
        $uri = Security::get2FAUri(currentUser()['email'], $me['two_fa_secret']);
        $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=190x190&data='.urlencode($uri);
      ?>
      <div style="text-align:center;margin-bottom:20px;">
        <img src="<?=$qrUrl?>" alt="QR Code 2FA" style="border:4px solid white;border-radius:12px;display:inline-block;"/>
        <div style="margin-top:12px;font-family:monospace;font-size:.8rem;background:var(--card2);border:1px solid var(--b1);border-radius:8px;padding:10px 14px;word-break:break-all;color:var(--cyan);"><?=htmlspecialchars($me['two_fa_secret'])?></div>
        <div style="font-size:.72rem;color:var(--t3);margin-top:6px;">Kode manual jika tidak bisa scan QR</div>
      </div>
      <form method="POST">
        <input type="hidden" name="_token" value="<?=csrfToken()?>">
        <input type="hidden" name="action" value="confirm_2fa">
        <div class="fg">
          <label class="flabel">Masukkan Kode 6 Digit dari App</label>
          <input type="text" name="code" class="finput" placeholder="000000" maxlength="6" pattern="\d{6}" required autofocus autocomplete="one-time-code"
                 style="text-align:center;font-size:1.6rem;letter-spacing:8px;font-family:monospace;"/>
        </div>
        <button type="submit" class="btn-submit">Verifikasi &amp; Aktifkan</button>
      </form>
      <div style="margin-top:12px;text-align:center;">
        <a href="?tab=2fa" style="font-size:.78rem;color:var(--t3);">Batal setup</a>
      </div>

      <?php else: ?>
      <!-- Toggle 2FA -->
      <?php if(!($me['two_fa_enabled']??0)): ?>
      <div style="background:rgba(56,189,248,.05);border:1px solid rgba(56,189,248,.15);border-radius:10px;padding:14px 16px;margin-bottom:20px;font-size:.83rem;color:var(--cyan);line-height:1.7;">
        Dengan 2FA aktif, login admin memerlukan kode 6 digit dari aplikasi authenticator selain password. Ini mencegah akses tidak sah meski password kamu bocor.
      </div>
      <form method="POST">
        <input type="hidden" name="_token" value="<?=csrfToken()?>">
        <input type="hidden" name="action" value="enable_2fa">
        <button type="submit" class="btn-submit">Aktifkan 2FA Sekarang</button>
      </form>
      <?php else: ?>
      <div style="background:rgba(239,68,68,.05);border:1px solid rgba(239,68,68,.15);border-radius:10px;padding:14px 16px;margin-bottom:20px;font-size:.83rem;color:#fca5a5;line-height:1.7;">
        Menonaktifkan 2FA akan mengurangi keamanan akun kamu. Pastikan kamu tahu apa yang kamu lakukan.
      </div>
      <form method="POST" onsubmit="return confirm('Yakin nonaktifkan 2FA? Akun akan kurang aman.')">
        <input type="hidden" name="_token" value="<?=csrfToken()?>">
        <input type="hidden" name="action" value="disable_2fa">
        <button type="submit" class="btn-submit" style="background:linear-gradient(135deg,#ef4444,#dc2626);">Nonaktifkan 2FA</button>
      </form>
      <?php endif; ?>
      <?php endif; ?>

    </div>
  </div>
  <?php endif; ?>

</div>
</div>
<?php include __DIR__.'/../includes/footer.php'; ?>