<?php
// auth/2fa-cancel.php — Batalkan proses 2FA dan kembali ke login
require_once __DIR__.'/../includes/functions.php';
unset($_SESSION['_2fa_pending_user']);
unset($_SESSION['_redirect_after_login']);
header('Location: '.asset('auth/login.php'));
exit;