<?php
require_once __DIR__.'/../includes/functions.php';
// 2FA page: user sudah login tapi belum verifikasi TOTP
if(isLoggedIn()) { header('Location: '.asset('index.php')); exit; }
if(empty($_SESSION['_2fa_pending_user'])) { header('Location: '.asset('auth/login.php')); exit; }

$pageTitle = 'Verifikasi 2FA — '.siteName();
$error = '';
$pendingUser = $_SESSION['_2fa_pending_user'];

if($_SERVER['REQUEST_METHOD']==='POST'){
  Security::verifyCsrf();
  $code = preg_replace('/\s+/','',$_POST['code']??'');
  if(Security::verify2FA($pendingUser['two_fa_secret'], $code)){
    unset($_SESSION['_2fa_pending_user']);
    loginUser($pendingUser);
    $redirect = $_SESSION['_redirect_after_login'] ?? asset('index.php');
    unset($_SESSION['_redirect_after_login']);
    if(in_array($pendingUser['role'],['admin','super_admin'])){
      header('Location: '.asset('admin/index.php'));
    } else {
      header('Location: '.$redirect);
    }
    exit;
  } else {
    Security::audit('2FA_FAILED','Kode 2FA salah untuk user #'.$pendingUser['id']);
    $error = 'Kode 2FA tidak valid. Coba lagi.';
  }
}
include __DIR__.'/../includes/header.php';
?>
<div class="auth-page">
  <div class="auth-card">
    <div style="text-align:center;margin-bottom:20px">
      <div style="font-size:2.5rem;margin-bottom:8px">🔐</div>
      <h1 style="font-size:1.3rem">Verifikasi 2FA</h1>
      <p class="sub" style="margin-bottom:0">Masukkan kode dari aplikasi authenticator kamu (Google Authenticator, Authy, dll).</p>
    </div>
    <?php if($error): ?><div class="auth-err">⚠️ <?=htmlspecialchars($error)?></div><?php endif; ?>
    <form method="POST">
      <input type="hidden" name="_token" value="<?=Security::csrfToken()?>">
      <div class="fg">
        <label class="flabel">Kode 6 Digit</label>
        <input type="text" name="code" class="finput" placeholder="000000"
               maxlength="6" pattern="\d{6}" autocomplete="one-time-code"
               required autofocus
               style="text-align:center;font-size:1.8rem;letter-spacing:8px;font-family:monospace"/>
      </div>
      <button type="submit" class="btn-submit">Verifikasi</button>
    </form>
    <p style="text-align:center;margin-top:20px;font-size:.82rem;color:var(--t3)">
      Bukan kamu? <a href="<?= asset('auth/2fa-cancel.php')?>" style="color:var(--cyan)">Kembali ke Login</a>
    </p>
  </div>
</div>
<?php include __DIR__.'/../includes/footer.php'; ?>