<?php
// ============================================================
// config/security.php — Konfigurasi keamanan ftarastore
// ============================================================

// ── Session ───────────────────────────────────────────────────
define('SESSION_LIFETIME',   7200);        // 2 jam (detik)
define('SESSION_REGENERATE', 300);         // Regenerate setiap 5 menit
define('SESSION_SECURE',     false);       // true jika sudah HTTPS
define('SESSION_SAMESITE',   'Strict');

// ── Rate Limiting ─────────────────────────────────────────────
define('LOGIN_MAX_ATTEMPTS',  5);          // max gagal login
define('LOGIN_LOCKOUT_MIN',   15);         // lockout menit
define('API_MAX_REQ_PER_MIN', 60);         // max request per menit
define('CHECKOUT_MAX_PER_HR', 20);         // max checkout per jam

// ── Password Policy ───────────────────────────────────────────
define('PASSWORD_MIN_LENGTH',  8);
define('PASSWORD_REQUIRE_NUM', true);
define('PASSWORD_REQUIRE_SYM', false);
define('BCRYPT_COST',          12);

// ── 2FA ───────────────────────────────────────────────────────
define('TOTP_ISSUER',     'ftarastore');
define('TOTP_DIGITS',     6);
define('TOTP_PERIOD',     30);
define('TOTP_WINDOW',     1);              // ±1 periode toleransi

// ── File Upload ───────────────────────────────────────────────
define('UPLOAD_MAX_SIZE',   2 * 1024 * 1024); // 2MB
define('UPLOAD_ALLOWED_EXT',['jpg','jpeg','png','webp']);

// ── Security Headers ─────────────────────────────────────────
define('SECURITY_HEADERS', true);

// ── Roles & Permissions ──────────────────────────────────────
define('ROLES', [
    'super_admin' => [
        'label'       => 'Super Admin',
        'permissions' => ['*'],                    // semua
    ],
    'admin' => [
        'label'       => 'Admin',
        'permissions' => [
            'games.view','games.create','games.edit','games.delete',
            'products.view','products.create','products.edit','products.delete',
            'orders.view','orders.edit',
            'transactions.view',
            'banners.view','banners.create','banners.edit','banners.delete',
            'settings.view','settings.edit',
            'users.view',
            'reports.view',
        ],
    ],
    'user' => [
        'label'       => 'User',
        'permissions' => [
            'topup.buy',
            'orders.view_own',
            'profile.edit_own',
        ],
    ],
]);
