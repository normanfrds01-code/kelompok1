<?php
/* ═══════════════════════════════════════════════════
   FTARASTORE — Language System
   includes/lang.php

   Usage:
   require_once __DIR__.'/lang.php';
   echo __('nav_topup');           // "Top Up" / "Top Up"
   echo __('hero_title');          // "Top Up Game..."
   ═══════════════════════════════════════════════════ */

// Detect language from cookie → session → default ID
function getLang(): string {
    if (!empty($_SESSION['lang'])) return $_SESSION['lang'];
    if (!empty($_COOKIE['ftara_lang'])) {
        $l = strtolower($_COOKIE['ftara_lang']);
        return in_array($l, ['id','en']) ? $l : 'id';
    }
    return 'id';
}

// Set language
function setLang(string $lang): void {
    $lang = strtolower($lang);
    if (!in_array($lang, ['id','en'])) $lang = 'id';
    $_SESSION['lang'] = $lang;
    setcookie('ftara_lang', strtoupper($lang), time() + 31536000, '/', '', false, false);
}

// Load translations (cached)
function loadTranslations(): array {
    static $trans = null;
    if ($trans !== null) return $trans;

    $lang = getLang();
    $file = __DIR__ . '/../lang/' . $lang . '.php';

    if (!file_exists($file)) {
        $file = __DIR__ . '/../lang/id.php'; // fallback
    }

    $trans = require $file;
    return $trans;
}

// Translate key
function __(string $key, array $replace = []): string {
    $trans = loadTranslations();
    $text  = $trans[$key] ?? $key; // fallback to key if not found

    // Support :variable replacement e.g. __('hello', ['name' => 'John'])
    foreach ($replace as $k => $v) {
        $text = str_replace(':' . $k, $v, $text);
    }

    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

// Raw translate (no htmlspecialchars) — for use inside HTML attrs
function __r(string $key, array $replace = []): string {
    $trans = loadTranslations();
    $text  = $trans[$key] ?? $key;
    foreach ($replace as $k => $v) {
        $text = str_replace(':' . $k, $v, $text);
    }
    return $text;
}

// Current lang code: 'id' or 'en'
function currentLang(): string {
    return getLang();
}

// Is current lang
function isLang(string $lang): bool {
    return getLang() === strtolower($lang);
}

// Handle lang switch via GET ?lang=en
if (!empty($_GET['lang'])) {
    setLang($_GET['lang']);
    // Redirect back without ?lang param
    $redirect = strtok($_SERVER['REQUEST_URI'], '?');
    $params   = $_GET;
    unset($params['lang']);
    if ($params) $redirect .= '?' . http_build_query($params);
    header('Location: ' . $redirect);
    exit;
}