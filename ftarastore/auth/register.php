<?php
require_once __DIR__.'/../includes/functions.php';
Security::boot(['guest']);
if(isLoggedIn()){header('Location: '.asset('index.php'));exit;}
$pageTitle='Daftar — '.siteName();
$errors=[];
if($_SERVER['REQUEST_METHOD']==='POST'){
  verifyCsrf();
  $name=trim($_POST['name']??'');$email=trim($_POST['email']??'');
  $pass=$_POST['password']??'';$pass2=$_POST['password2']??'';$phone=trim($_POST['phone']??'');
  if(strlen($name)<2) $errors[]='Nama minimal 2 karakter.';
  if(!filter_var($email,FILTER_VALIDATE_EMAIL)) $errors[]='Format email tidak valid.';
  if(strlen($pass)<6) $errors[]='Password minimal 6 karakter.';
  if($pass!==$pass2) $errors[]='Konfirmasi password tidak cocok.';
  if(!$errors){
    $c=db()->prepare("SELECT id FROM users WHERE email=?");$c->execute([$email]);
    if($c->fetch()) $errors[]='Email sudah terdaftar.';
  }
  if(!$errors){
    db()->prepare("INSERT INTO users (name,email,phone,password) VALUES (?,?,?,?)")->execute([$name,$email,$phone?:null,password_hash($pass,PASSWORD_BCRYPT)]);
    setFlash('success','Akun berhasil dibuat! Silakan masuk.');
    header('Location: '.asset('auth/login.php'));exit;
  }
}
include __DIR__.'/../includes/header.php';
?>
<div class="auth-page">
  <div class="auth-card">
    
    <div style="text-align:center;margin-bottom:20px;">
      <div class="auth-logo-text">ftar<span class="logo-a">a</span><span class="logo-store">store</span></div>
      <div class="auth-logo-tag">Top Up Game Center</div>
    </div>
    <style>
    .auth-logo-text{font-family:'Orbitron',sans-serif;font-size:16px;font-weight:500;letter-spacing:1.5px;color:var(--text-primary,#0d2137);transition:color .3s}
    .auth-logo-text .logo-a,.auth-logo-text .logo-store{color:#e90e0e!important}
    .auth-logo-text .logo-a{font-weight:700}.auth-logo-text .logo-store{font-weight:300}
    .auth-logo-tag{font-size:8px;letter-spacing:2px;color:var(--text-muted,#7aabb8);text-transform:uppercase;margin-top:3px}
    </style>
    <h1>Buat Akun</h1>
    <p class="sub">Daftar gratis dan mulai top up game favoritmu</p>
    <?php if($errors): ?><div class="auth-err">⚠️ <?=implode('<br>',array_map('htmlspecialchars',$errors))?></div><?php endif; ?>
    <form method="POST">
      <input type="hidden" name="_token" value="<?=csrfToken()?>">
      <div class="fg"><label class="flabel">Nama Lengkap</label><input type="text" name="name" class="finput" placeholder="Nama kamu" required value="<?=htmlspecialchars($_POST['name']??'')?>"/></div>
      <div class="fg"><label class="flabel">Email</label><input type="email" name="email" class="finput" placeholder="email@kamu.com" required value="<?=htmlspecialchars($_POST['email']??'')?>"/></div>
      <div class="fg"><label class="flabel">No. WhatsApp <span style="color:var(--t3);font-weight:400">(opsional)</span></label><input type="tel" name="phone" class="finput" placeholder="08xxxxxxxxxx" value="<?=htmlspecialchars($_POST['phone']??'')?>"/></div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
        <div class="fg"><label class="flabel">Password</label><input type="password" name="password" class="finput" placeholder="Min. 6 karakter" required/></div>
        <div class="fg"><label class="flabel">Konfirmasi</label><input type="password" name="password2" class="finput" placeholder="Ulangi password" required/></div>
      </div>
      <button type="submit" class="btn-submit">Buat Akun Sekarang</button>
    </form>
    <p class="auth-foot" style="margin-top:16px">Sudah punya akun? <a href="<?=asset('auth/login.php')?>">Masuk di sini →</a></p>
  </div>
</div>
<?php include __DIR__.'/../includes/footer.php'; ?>