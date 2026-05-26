<?php
// config/digiflazz.php
// Konstanta hardcode sebagai fallback

define('DIGI_USERNAME',       'username_digiflazz_anda');
define('DIGI_API_KEY_DEV',    'dev_api_key_anda');
define('DIGI_API_KEY_PROD',   'prod_api_key_anda');
define('DIGI_ENV',            'dev');
define('DIGI_BASE_URL',       'https://api.digiflazz.com/v1');
define('DIGI_WEBHOOK_SECRET', 'webhook_secret_anda');

// Baca dari settings DB jika tersedia (diisi via admin panel)
function _digiGet(string $key, string $fallback): string {
    static $cache = [];
    if (isset($cache[$key])) return $cache[$key];
    try {
        $r = db()->prepare("SELECT value FROM settings WHERE `key`=? LIMIT 1");
        $r->execute([$key]);
        $v = $r->fetchColumn();
        $cache[$key] = ($v !== false && $v !== '') ? $v : $fallback;
    } catch (\Throwable $e) {
        $cache[$key] = $fallback;
    }
    return $cache[$key];
}

function digiUsername(): string    { return _digiGet('digi_username', DIGI_USERNAME); }
function digiEnv(): string         { return _digiGet('digi_env', DIGI_ENV); }
function digiWebhookSecret(): string { return _digiGet('digi_webhook_secret', DIGI_WEBHOOK_SECRET); }
function digiApiKey(): string {
    return digiEnv() === 'prod'
        ? _digiGet('digi_api_key_prod', DIGI_API_KEY_PROD)
        : _digiGet('digi_api_key_dev',  DIGI_API_KEY_DEV);
}
function digiSign(string $ref_id): string {
    return md5(digiUsername() . digiApiKey() . $ref_id);
}