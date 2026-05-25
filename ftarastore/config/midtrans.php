<?php
// config/midtrans.php

define('MIDTRANS_ENV',                   'sandbox');
define('MIDTRANS_SERVER_KEY_SANDBOX',    'SB-Mid-server-xxxx');
define('MIDTRANS_CLIENT_KEY_SANDBOX',    'SB-Mid-client-xxxx');
define('MIDTRANS_SERVER_KEY_PRODUCTION', 'Mid-server-xxxx');
define('MIDTRANS_CLIENT_KEY_PRODUCTION', 'Mid-client-xxxx');

function _midGet(string $key, string $fallback): string {
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

function midtransEnv(): string {
    return _midGet('midtrans_env', MIDTRANS_ENV);
}
function midtransServerKey(): string {
    return midtransEnv() === 'production'
        ? _midGet('midtrans_server_key_production', MIDTRANS_SERVER_KEY_PRODUCTION)
        : _midGet('midtrans_server_key_sandbox',    MIDTRANS_SERVER_KEY_SANDBOX);
}
function midtransClientKey(): string {
    return midtransEnv() === 'production'
        ? _midGet('midtrans_client_key_production', MIDTRANS_CLIENT_KEY_PRODUCTION)
        : _midGet('midtrans_client_key_sandbox',    MIDTRANS_CLIENT_KEY_SANDBOX);
}
function midtransSnapUrl(): string {
    return midtransEnv() === 'production'
        ? 'https://app.midtrans.com/snap/snap.js'
        : 'https://app.sandbox.midtrans.com/snap/snap.js';
}