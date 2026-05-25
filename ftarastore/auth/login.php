<?php
require_once __DIR__.'/../includes/functions.php';
Security::boot(['guest']);
$pageTitle='Masuk — '.siteName();
$error='';
$msg=trim($_GET['msg']??'');

if($_SERVER['REQUEST_METHOD']==='POST'){
  Security::verifyCsrf();
  $email=Security::cleanInput($_POST['email']??'');
  $pass=$_POST['password']??'';
  try {
    Security::checkLoginThrottle($email);
    $s=db()->prepare("SELECT * FROM users WHERE email=? AND is_active=1");
    $s->execute([$email]);$user=$s->fetch();
    if($user && Security::verifyPassword($pass,$user['password'])){
      if($user['two_fa_enabled']){
        $_SESSION['_2fa_pending_user'] = $user;
        header('Location: '.asset('auth/2fa.php'));exit;
      }
      Security::clearLoginThrottle($email);
      loginUser($user);
      $redirect = $_SESSION['_redirect_after_login'] ?? asset('index.php');
      unset($_SESSION['_redirect_after_login']);
      if($user['role']==='super_admin'||$user['role']==='admin'){
        header('Location: '.asset('admin/index.php'));
      } else {
        header('Location: '.$redirect);
      }
      exit;
    } else {
      Security::recordLoginFail($email);
      Security::audit('LOGIN_FAILED',"Login gagal untuk email: $email");
      $error='Email atau password salah.';
    }
  } catch(\RuntimeException $e) {
    Security::audit('LOGIN_LOCKOUT',"Login terkunci: $email");
    $error=$e->getMessage();
  }
}
include __DIR__.'/../includes/header.php';
?>

<style>
/* Logo di dalam auth card — adaptif light/dark */
.auth-logo-text {
  font-family: 'Orbitron', sans-serif;
  font-size: 24px;
  font-weight: 300;
  letter-spacing: 3.5px;
  /* Ikut var(--text-primary) dari tema aktif */
  color: var(--text-primary, #0d2137);
  transition: color .3s ease;
}
.auth-logo-text .logo-a,
.auth-logo-text .logo-store {
  color: #e90e0e !important;
}
.auth-logo-text .logo-a    { font-weight: 700; }
.auth-logo-text .logo-store{ font-weight: 300; }

.auth-logo-tag {
  font-size: 8px;
  letter-spacing: 2.5px;
  color: var(--text-muted, #7aabb8);
  text-transform: uppercase;
  margin-top: 3px;
  transition: color .3s ease;
}
</style>

<div class="auth-page">
  <div class="auth-card">
    <div style="text-align:center;margin-bottom:22px;">
      <div class="auth-logo-text">ftar<span class="logo-a">a</span><span class="logo-store">store</span></div>
      <div class="auth-logo-tag">Top Up Game Center</div>
    </div>
    <h1>Masuk</h1>
    <p class="sub">Selamat datang kembali!</p>
    <?php if($msg==='session_expired'): ?>
    <div class="auth-err">⏱️ Sesi kamu sudah berakhir, silakan login kembali.</div>
    <?php endif; ?>
    <?php if($error): ?>
    <div class="auth-err">⚠️ <?=htmlspecialchars($error)?></div>
    <?php endif; ?>
    <form method="POST">
      <input type="hidden" name="_token" value="<?=Security::csrfToken()?>">
      <div class="fg">
        <label class="flabel">Email</label>
        <input type="email" name="email" class="finput" placeholder="email@kamu.com"
               required autocomplete="email" value="<?=htmlspecialchars($_POST['email']??'')?>"/>
      </div>
      <div class="fg">
        <label class="flabel">Password</label>
        <input type="password" name="password" class="finput" placeholder="••••••••"
               required autocomplete="current-password"/>
      </div>
      <button type="submit" class="btn-submit" style="margin-top:4px">Masuk ke Akun</button>
    </form>
    <div class="divider">atau</div>
    <p class="auth-foot">Belum punya akun? <a href="<?=asset('auth/register.php')?>">Daftar gratis →</a></p>
  </div>
</div>
<?php include __DIR__.'/../includes/footer.php'; ?>