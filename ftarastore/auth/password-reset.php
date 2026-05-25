<?php
require_once __DIR__.'/../includes/functions.php';
Security::boot(['guest']);
$pageTitle = 'Reset Password — '.siteName();

$step    = 'request'; // request | reset | done
$error   = '';
$success = '';
$token   = trim($_GET['token'] ?? '');

// Step 2: Ada token di URL → form reset password baru
if($token){
    $resetData = validatePasswordReset($token);
    if(!$resetData){
        $error = 'Link reset password tidak valid atau sudah kadaluarsa.';
    } else {
        $step = 'reset';
    }
}

if($_SERVER['REQUEST_METHOD']==='POST'){
    verifyCsrf();

    // Step 1: Request reset
    if(isset($_POST['email'])){
        $email = trim($_POST['email']??'');
        if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
            $error = 'Format email tidak valid.';
        } else {
            $resetToken = createPasswordReset($email);
            // Selalu tampilkan pesan sukses (cegah user enumeration)
            $step    = 'done';
            $success = 'Jika email terdaftar, link reset akan dikirim ke email kamu dalam beberapa menit.';

            // TODO: Kirim email dengan link reset
            // Untuk sementara, log token untuk testing
            if($resetToken){
                $resetLink = asset('auth/password-reset.php').'?token='.$resetToken;
                error_log('[Password Reset] Link untuk '.$email.': '.$resetLink);
                // Uncomment & isi SMTP jika sudah setup email:
                // sendResetEmail($email, $resetLink);
            }
        }
    }

    // Step 2: Set password baru
    elseif(isset($_POST['token'], $_POST['password'])){
        $tok  = trim($_POST['token']??'');
        $pass = $_POST['password']??'';
        $pass2= $_POST['password2']??'';
        $resetData = validatePasswordReset($tok);

        if(!$resetData){
            $error = 'Link tidak valid atau sudah kadaluarsa.';
        } elseif(strlen($pass) < 8){
            $error = 'Password minimal 8 karakter.';
            $step = 'reset';
        } elseif($pass !== $pass2){
            $error = 'Konfirmasi password tidak cocok.';
            $step = 'reset';
        } else {
            db()->prepare("UPDATE users SET password=?, password_changed_at=NOW() WHERE id=?")
               ->execute([Security::hashPassword($pass), $resetData['uid']]);
            consumePasswordReset($tok);
            Security::audit('PASSWORD_RESET', 'Password berhasil direset via email: '.$resetData['uemail']);
            $step    = 'done';
            $success = 'Password berhasil direset! Silakan masuk dengan password baru.';
        }
    }
}

include __DIR__.'/../includes/header.php';
?>
<style>
.auth-logo-text{font-family:'Orbitron',sans-serif;font-size:16px;font-weight:500;letter-spacing:1.5px;color:var(--text-primary,#0d2137);transition:color .3s}
.auth-logo-text .logo-a,.auth-logo-text .logo-store{color:#0ea5e9!important}
.auth-logo-text .logo-a{font-weight:700}.auth-logo-text .logo-store{font-weight:300}
.auth-logo-tag{font-size:8px;letter-spacing:2px;color:var(--text-muted,#7aabb8);text-transform:uppercase;margin-top:3px}
</style>

<div class="auth-page">
  <div class="auth-card">

    <div style="text-align:center;margin-bottom:20px;">
      <div class="auth-logo-text">ftar<span class="logo-a">a</span><span class="logo-store">store</span></div>
      <div class="auth-logo-tag">Top Up Game Center</div>
    </div>

    <?php if($error): ?>
    <div class="auth-err">⚠️ <?=htmlspecialchars($error)?></div>
    <?php endif; ?>

    <?php if($step==='request'): ?>
    <!-- STEP 1: Minta email -->
    <div style="text-align:center;margin-bottom:20px;">
      <div style="font-size:2rem;margin-bottom:8px;">🔐</div>
      <h1 style="font-size:1.3rem;">Lupa Password?</h1>
      <p class="sub">Masukkan email kamu dan kami akan kirim link reset password.</p>
    </div>
    <form method="POST">
      <input type="hidden" name="_token" value="<?=csrfToken()?>">
      <div class="fg">
        <label class="flabel">Email</label>
        <input type="email" name="email" class="finput" placeholder="email@kamu.com"
               required autocomplete="email"/>
      </div>
      <button type="submit" class="btn-submit">Kirim Link Reset</button>
    </form>

    <?php elseif($step==='reset'): ?>
    <!-- STEP 2: Isi password baru -->
    <div style="text-align:center;margin-bottom:20px;">
      <div style="font-size:2rem;margin-bottom:8px;">🔑</div>
      <h1 style="font-size:1.3rem;">Buat Password Baru</h1>
      <p class="sub">Masukkan password baru untuk akun kamu.</p>
    </div>
    <form method="POST">
      <input type="hidden" name="_token" value="<?=csrfToken()?>">
      <input type="hidden" name="token" value="<?=htmlspecialchars($token)?>">
      <div class="fg">
        <label class="flabel">Password Baru</label>
        <input type="password" name="password" class="finput" placeholder="Minimal 8 karakter"
               required minlength="8"/>
      </div>
      <div class="fg">
        <label class="flabel">Konfirmasi Password</label>
        <input type="password" name="password2" class="finput" placeholder="Ulangi password baru"
               required minlength="8"/>
      </div>
      <button type="submit" class="btn-submit">Reset Password</button>
    </form>

    <?php elseif($step==='done'): ?>
    <!-- STEP 3: Selesai -->
    <div style="text-align:center;padding:16px 0;">
      <div style="font-size:2.5rem;margin-bottom:12px;"><?=str_contains($success,'berhasil direset')?'✅':'📧'?></div>
      <p style="color:var(--t2);font-size:.9rem;line-height:1.7;"><?=htmlspecialchars($success)?></p>
      <?php if(str_contains($success,'dikirim')): ?>
      <p style="color:var(--t3);font-size:.78rem;margin-top:12px;">Cek juga folder Spam/Junk jika tidak muncul di inbox.</p>
      <?php endif; ?>
    </div>
    <?php endif; ?>

    <div class="divider">atau</div>
    <p class="auth-foot">
      <a href="<?=asset('auth/login.php')?>">← Kembali ke Login</a>
    </p>
  </div>
</div>
<?php include __DIR__.'/../includes/footer.php'; ?>