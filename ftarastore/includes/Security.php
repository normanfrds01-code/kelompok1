<?php
// ============================================================
// includes/Security.php — Core Security Engine ftarastore
// Inspired by Laravel middleware architecture
// ============================================================

class Security {

    // ── Boot: jalankan semua middleware sekaligus ─────────────
    public static function boot(array $middlewares = []): void {
        self::setSecureSession();
        self::setSecurityHeaders();
        self::checkIPBlacklist();
        self::validateSessionIntegrity();
        self::checkSessionTimeout();

        foreach ($middlewares as $m) {
            match($m) {
                'auth'        => self::requireAuth(),
                'admin'       => self::requireRole(['admin','super_admin']),
                'super_admin' => self::requireRole(['super_admin']),
                'guest'       => self::requireGuest(),
                'throttle'    => self::throttle(),
                default       => null,
            };
        }
    }

    // ── Secure Session Setup ──────────────────────────────────
    public static function setSecureSession(): void {
        if (session_status() !== PHP_SESSION_NONE) return;
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_samesite', SESSION_SAMESITE);
        ini_set('session.use_strict_mode', 1);
        ini_set('session.gc_maxlifetime', SESSION_LIFETIME);
        if (SESSION_SECURE) ini_set('session.cookie_secure', 1);
        session_name('FTARA_SESS');
        session_start();
    }

    // ── Security Headers ──────────────────────────────────────
    public static function setSecurityHeaders(): void {
        if (!SECURITY_HEADERS || headers_sent()) return;
        header("X-Frame-Options: SAMEORIGIN");
        header("X-Content-Type-Options: nosniff");
        header("X-XSS-Protection: 1; mode=block");
        header("Referrer-Policy: strict-origin-when-cross-origin");
        header("Permissions-Policy: camera=(), microphone=(), geolocation=()");
header("Content-Security-Policy: "
    . "default-src 'self' https://fonts.googleapis.com https://fonts.gstatic.com; "
    . "script-src 'self' 'unsafe-inline' https://app.sandbox.midtrans.com https://app.midtrans.com https://snap-static.midtrans.com; "
    . "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://fonts.gstatic.com; "
    . "img-src 'self' data: https:; "
    . "frame-src https://app.sandbox.midtrans.com https://app.midtrans.com; "
    . "connect-src 'self' https://app.sandbox.midtrans.com https://api.sandbox.midtrans.com https://app.midtrans.com https://api.midtrans.com; "
    . "font-src 'self' https://fonts.gstatic.com;"
);    }

    // ── Session Integrity (fingerprinting) ────────────────────
    public static function validateSessionIntegrity(): void {
        if (!isset($_SESSION['user_id'])) return;
        $fp = md5(
            ($_SERVER['HTTP_USER_AGENT'] ?? '') .
            substr($_SERVER['REMOTE_ADDR'] ?? '', 0, strrpos($_SERVER['REMOTE_ADDR'] ?? '0.0.0', '.'))
        );
        if (!isset($_SESSION['_fingerprint'])) {
            $_SESSION['_fingerprint'] = $fp;
        } elseif ($_SESSION['_fingerprint'] !== $fp) {
            self::forceLogout('Session fingerprint mismatch');
        }
    }

    // ── Session Timeout & Regeneration ───────────────────────
    public static function checkSessionTimeout(): void {
        $now = time();
        // Timeout check
        if (isset($_SESSION['_last_activity'])) {
            if ($now - $_SESSION['_last_activity'] > SESSION_LIFETIME) {
                self::forceLogout('Session expired');
            }
        }
        $_SESSION['_last_activity'] = $now;
        // Regenerate ID periodically
        if (!isset($_SESSION['_regenerated_at'])) {
            $_SESSION['_regenerated_at'] = $now;
        } elseif ($now - $_SESSION['_regenerated_at'] > SESSION_REGENERATE) {
            session_regenerate_id(true);
            $_SESSION['_regenerated_at'] = $now;
        }
    }

    // ── IP Blacklist ──────────────────────────────────────────
    public static function checkIPBlacklist(): void {
        $ip = self::getIP();
        try {
            $stmt = db()->prepare("SELECT id FROM ip_blacklist WHERE ip = ? AND (expires_at IS NULL OR expires_at > NOW()) LIMIT 1");
            $stmt->execute([$ip]);
            if ($stmt->fetch()) {
                http_response_code(403);
                die('<h1>403 Forbidden</h1><p>IP kamu telah diblokir. Hubungi admin.</p>');
            }
        } catch (\Exception $e) {}
    }

    // ── Auth Middleware ───────────────────────────────────────
    public static function requireAuth(): void {
        if (empty($_SESSION['user_id'])) {
            $_SESSION['_redirect_after_login'] = $_SERVER['REQUEST_URI'];
            header('Location: ' . asset('auth/login.php')); exit;
        }
    }

    // ── Guest Middleware ──────────────────────────────────────
    public static function requireGuest(): void {
        if (!empty($_SESSION['user_id'])) {
            header('Location: ' . asset('index.php')); exit;
        }
    }

    // ── Role Middleware ───────────────────────────────────────
    public static function requireRole(array $roles): void {
        self::requireAuth();
        $userRole = $_SESSION['user_role'] ?? '';
        if (!in_array($userRole, $roles)) {
            http_response_code(403);
            include __DIR__ . '/403.php'; exit;
        }
    }

    // ── Permission Check ─────────────────────────────────────
    public static function can(string $permission): bool {
        $role = $_SESSION['user_role'] ?? 'user';
        $perms = ROLES[$role]['permissions'] ?? [];
        return in_array('*', $perms) || in_array($permission, $perms);
    }

    // ── Rate Limiting ─────────────────────────────────────────
    public static function throttle(string $key = '', int $max = 60, int $decaySeconds = 60): void {
        if (!$key) $key = 'global:' . self::getIP();
        try {
            $db = db();
            // Cleanup expired
            $db->prepare("DELETE FROM rate_limits WHERE expires_at < NOW()")->execute();
            $stmt = $db->prepare("SELECT attempts, expires_at FROM rate_limits WHERE `key` = ?");
            $stmt->execute([$key]);
            $row = $stmt->fetch();
            if ($row) {
                if ($row['attempts'] >= $max) {
                    $retry = strtotime($row['expires_at']) - time();
                    header("Retry-After: $retry");
                    http_response_code(429);
                    die(json_encode(['error' => 'Too Many Requests', 'retry_after' => $retry]));
                }
                $db->prepare("UPDATE rate_limits SET attempts = attempts + 1 WHERE `key` = ?")->execute([$key]);
            } else {
                $db->prepare("INSERT INTO rate_limits (`key`, attempts, expires_at) VALUES (?, 1, DATE_ADD(NOW(), INTERVAL ? SECOND))")
                   ->execute([$key, $decaySeconds]);
            }
        } catch (\Exception $e) {}
    }

    // ── Login Rate Limiting ───────────────────────────────────
    public static function checkLoginThrottle(string $email): void {
        $key = 'login:' . md5($email . ':' . self::getIP());
        try {
            $db = db();
            $db->prepare("DELETE FROM rate_limits WHERE `key` = ? AND expires_at < NOW()")->execute([$key]);
            $stmt = $db->prepare("SELECT attempts FROM rate_limits WHERE `key` = ?");
            $stmt->execute([$key]);
            $row = $stmt->fetch();
            if ($row && $row['attempts'] >= LOGIN_MAX_ATTEMPTS) {
                $secs = LOGIN_LOCKOUT_MIN * 60;
                throw new \RuntimeException("Terlalu banyak percobaan login. Coba lagi dalam " . LOGIN_LOCKOUT_MIN . " menit.");
            }
        } catch (\RuntimeException $e) {
            throw $e;
        } catch (\Exception $e) {}
    }

    public static function recordLoginFail(string $email): void {
        $key = 'login:' . md5($email . ':' . self::getIP());
        try {
            $db = db();
            $stmt = $db->prepare("SELECT id FROM rate_limits WHERE `key` = ?");
            $stmt->execute([$key]);
            if ($stmt->fetch()) {
                $db->prepare("UPDATE rate_limits SET attempts = attempts + 1 WHERE `key` = ?")->execute([$key]);
            } else {
                $secs = LOGIN_LOCKOUT_MIN * 60;
                $db->prepare("INSERT INTO rate_limits (`key`, attempts, expires_at) VALUES (?, 1, DATE_ADD(NOW(), INTERVAL ? SECOND))")
                   ->execute([$key, $secs]);
            }
        } catch (\Exception $e) {}
    }

    public static function clearLoginThrottle(string $email): void {
        $key = 'login:' . md5($email . ':' . self::getIP());
        try { db()->prepare("DELETE FROM rate_limits WHERE `key` = ?")->execute([$key]); }
        catch (\Exception $e) {}
    }

    // ── Audit Log ─────────────────────────────────────────────
    public static function audit(string $action, string $description = '', ?int $userId = null, array $extra = []): void {
        try {
            $userId = $userId ?? ($_SESSION['user_id'] ?? null);
            db()->prepare("
                INSERT INTO audit_logs (user_id, action, description, ip_address, user_agent, extra)
                VALUES (?, ?, ?, ?, ?, ?)
            ")->execute([
                $userId,
                $action,
                $description,
                self::getIP(),
                substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
                $extra ? json_encode($extra) : null,
            ]);
        } catch (\Exception $e) {}
    }

    // ── CSRF ─────────────────────────────────────────────────
    public static function csrfToken(): string {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function verifyCsrf(): void {
        $token = $_POST['_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            self::audit('CSRF_VIOLATION', 'CSRF token mismatch');
            http_response_code(419);
            die(json_encode(['error' => 'CSRF token mismatch']));
        }
    }

    // ── Password Validation ───────────────────────────────────
    public static function validatePassword(string $password): array {
        $errors = [];
        if (strlen($password) < PASSWORD_MIN_LENGTH) {
            $errors[] = 'Password minimal ' . PASSWORD_MIN_LENGTH . ' karakter.';
        }
        if (PASSWORD_REQUIRE_NUM && !preg_match('/[0-9]/', $password)) {
            $errors[] = 'Password harus mengandung minimal 1 angka.';
        }
        if (PASSWORD_REQUIRE_SYM && !preg_match('/[^a-zA-Z0-9]/', $password)) {
            $errors[] = 'Password harus mengandung minimal 1 simbol.';
        }
        return $errors;
    }

    public static function hashPassword(string $plain): string {
        return password_hash($plain, PASSWORD_BCRYPT, ['cost' => BCRYPT_COST]);
    }

    public static function verifyPassword(string $plain, string $hash): bool {
        return password_verify($plain, $hash);
    }

    // ── XSS Sanitize ─────────────────────────────────────────
    public static function sanitize(mixed $input): mixed {
        if (is_array($input)) return array_map([self::class, 'sanitize'], $input);
        return htmlspecialchars(trim((string)$input), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    public static function cleanInput(string $input): string {
        return strip_tags(trim($input));
    }

    // ── IP Helper ────────────────────────────────────────────
    public static function getIP(): string {
        foreach (['HTTP_CF_CONNECTING_IP','HTTP_X_FORWARDED_FOR','HTTP_X_REAL_IP','REMOTE_ADDR'] as $k) {
            if (!empty($_SERVER[$k])) {
                $ip = trim(explode(',', $_SERVER[$k])[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP)) return $ip;
            }
        }
        return '0.0.0.0';
    }

    // ── Force Logout ─────────────────────────────────────────
    public static function forceLogout(string $reason = ''): void {
        if (!empty($_SESSION['user_id'])) {
            self::audit('FORCE_LOGOUT', $reason);
        }
        session_destroy();
        session_start();
        session_regenerate_id(true);
        header('Location: ' . asset('auth/login.php') . '?msg=session_expired'); exit;
    }

    // ── 2FA: Generate Secret ──────────────────────────────────
    public static function generate2FASecret(): string {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $secret = '';
        for ($i = 0; $i < 32; $i++) {
            $secret .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $secret;
    }

    // ── 2FA: Verify TOTP ─────────────────────────────────────
    public static function verify2FA(string $secret, string $code): bool {
        $code = preg_replace('/\s+/', '', $code);
        if (!preg_match('/^\d{6}$/', $code)) return false;
        $time = floor(time() / TOTP_PERIOD);
        for ($i = -TOTP_WINDOW; $i <= TOTP_WINDOW; $i++) {
            if (self::generateTOTP($secret, $time + $i) === $code) return true;
        }
        return false;
    }

    private static function generateTOTP(string $secret, int $time): string {
        $base32 = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $secret = strtoupper($secret);
        $binaryKey = '';
        $buffer = 0; $bufLen = 0;
        foreach (str_split($secret) as $c) {
            $pos = strpos($base32, $c);
            if ($pos === false) continue;
            $buffer = ($buffer << 5) | $pos;
            $bufLen += 5;
            if ($bufLen >= 8) { $binaryKey .= chr(($buffer >> ($bufLen - 8)) & 0xFF); $bufLen -= 8; }
        }
        $msg = pack('N*', 0) . pack('N*', $time);
        $hash = hash_hmac('sha1', $msg, $binaryKey, true);
        $offset = ord($hash[19]) & 0x0F;
        $otp = ((ord($hash[$offset]) & 0x7F) << 24 | (ord($hash[$offset+1]) & 0xFF) << 16 | (ord($hash[$offset+2]) & 0xFF) << 8 | (ord($hash[$offset+3]) & 0xFF)) % pow(10, TOTP_DIGITS);
        return str_pad((string)$otp, TOTP_DIGITS, '0', STR_PAD_LEFT);
    }

    // ── 2FA QR URI ────────────────────────────────────────────
    public static function get2FAUri(string $email, string $secret): string {
        return 'otpauth://totp/' . rawurlencode(TOTP_ISSUER . ':' . $email) .
               '?secret=' . $secret . '&issuer=' . rawurlencode(TOTP_ISSUER) .
               '&digits=' . TOTP_DIGITS . '&period=' . TOTP_PERIOD;
    }

    // ── Block IP ─────────────────────────────────────────────
    public static function blockIP(string $ip, string $reason = '', ?int $hours = null): void {
        try {
            $expires = $hours ? "DATE_ADD(NOW(), INTERVAL $hours HOUR)" : 'NULL';
            db()->prepare("INSERT INTO ip_blacklist (ip, reason, expires_at) VALUES (?, ?, $expires) ON DUPLICATE KEY UPDATE reason=?, expires_at=$expires")
               ->execute([$ip, $reason, $reason]);
            self::audit('IP_BLOCKED', "IP $ip diblokir: $reason");
        } catch (\Exception $e) {}
    }

    // ── Unblock IP ────────────────────────────────────────────
    public static function unblockIP(string $ip): void {
        try {
            db()->prepare("DELETE FROM ip_blacklist WHERE ip = ?")->execute([$ip]);
            self::audit('IP_UNBLOCKED', "IP $ip dibuka");
        } catch (\Exception $e) {}
    }

    // ── File Upload Validation ───────────────────────────────
    public static function validateUpload(array $file): array {
        $errors = [];
        if ($file['error'] !== UPLOAD_ERR_OK) { $errors[] = 'Upload gagal.'; return $errors; }
        if ($file['size'] > UPLOAD_MAX_SIZE) { $errors[] = 'File terlalu besar (max 2MB).'; }
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, UPLOAD_ALLOWED_EXT)) { $errors[] = 'Tipe file tidak diizinkan.'; }
        // Check real MIME
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime  = $finfo->file($file['tmp_name']);
        $allowedMimes = ['image/jpeg','image/png','image/webp','image/gif'];
        if (!in_array($mime, $allowedMimes)) { $errors[] = 'File bukan gambar yang valid.'; }
        return $errors;
    }
}

// ── Global helper shortcuts ───────────────────────────────────
function can(string $permission): bool { return Security::can($permission); }
function audit(string $action, string $desc = '', array $extra = []): void { Security::audit($action, $desc, null, $extra); }
function getIP(): string { return Security::getIP(); }
function csrfToken(): string { return Security::csrfToken(); }
function verifyCsrf(): void { Security::verifyCsrf(); }
function sanitize(mixed $v): mixed { return Security::sanitize($v); }