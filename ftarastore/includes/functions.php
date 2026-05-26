<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/digiflazz.php';
require_once __DIR__ . '/../config/midtrans.php';
require_once __DIR__ . '/../config/security.php';
require_once __DIR__ . '/Security.php';
require_once __DIR__ . '/notifications.php';

Security::boot();

function getSetting(string $key, string $default = ''): string {
    $row = db()->prepare("SELECT value FROM settings WHERE `key` = ?");
    $row->execute([$key]);
    $result = $row->fetchColumn();
    return $result !== false ? $result : $default;
}

function siteName(): string { return getSetting('site_name', 'ftarastore'); }

function baseUrl(): string {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host     = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
    $root = rtrim($scriptDir, '/');
    foreach (['/pages', '/admin', '/api', '/auth', '/user'] as $sub) {
        if (str_ends_with($root, $sub)) { $root = substr($root, 0, -strlen($sub)); break; }
    }
    return $protocol . '://' . $host . $root;
}

function asset(string $path): string { return baseUrl() . '/' . ltrim($path, '/'); }

// ── Auth helpers (RBAC) ───────────────────────────────────────
function isLoggedIn(): bool     { return !empty($_SESSION['user_id']); }
function isAdmin(): bool        { return in_array($_SESSION['user_role']??'', ['admin','super_admin']); }
function isSuperAdmin(): bool   { return ($_SESSION['user_role']??'') === 'super_admin'; }
function currentUser(): array   { return $_SESSION['user'] ?? []; }
function currentRole(): string  { return $_SESSION['user_role'] ?? 'guest'; }
function roleLabel(): string    { return ROLES[currentRole()]['label'] ?? 'Guest'; }

function loginUser(array $user): void {
    session_regenerate_id(true);
    $_SESSION['user_id']        = $user['id'];
    $_SESSION['user_role']      = $user['role'];
    $_SESSION['user']           = $user;
    $_SESSION['_fingerprint']   = md5(
        ($_SERVER['HTTP_USER_AGENT']??'') .
        substr($_SERVER['REMOTE_ADDR']??'0.0.0', 0, strrpos($_SERVER['REMOTE_ADDR']??'0.0.0','.'))
    );
    $_SESSION['_last_activity'] = time();
    $_SESSION['_regenerated_at']= time();
    // Update last_login
    db()->prepare("UPDATE users SET last_login_at=NOW(), last_login_ip=? WHERE id=?")
       ->execute([Security::getIP(), $user['id']]);
    Security::audit('LOGIN_SUCCESS', 'User login berhasil');
}

function logoutUser(): void {
    Security::audit('LOGOUT', 'User logout');
    session_destroy();
}

function requireAuth(): void   { Security::requireAuth(); }
function requireAdmin(): void  { Security::requireRole(['admin','super_admin']); }
function requireSuperAdmin(): void { Security::requireRole(['super_admin']); }

function formatRupiah(float $amount): string {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

function generateOrderCode(): string {
    return 'FTS-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
}

function createPasswordReset(string $email): ?string {
    $user = db()->prepare("SELECT id FROM users WHERE email=? AND is_active=1");
    $user->execute([$email]);
    $row = $user->fetch();
    if (!$row) return null;

    $token = bin2hex(random_bytes(32));
    $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

    db()->prepare("DELETE FROM password_resets WHERE email=?")->execute([$email]);
    db()->prepare("INSERT INTO password_resets (user_id, email, token, expires_at) VALUES (?,?,?,?)")
       ->execute([$row['id'], $email, $token, $expires]);

    return $token;
}

function validatePasswordReset(string $token): ?array {
    $stmt = db()->prepare("
        SELECT pr.*, u.id AS uid, u.email AS uemail
        FROM password_resets pr
        JOIN users u ON u.id = pr.user_id
        WHERE pr.token=? AND pr.expires_at > NOW() AND pr.used_at IS NULL
    ");
    $stmt->execute([$token]);
    return $stmt->fetch() ?: null;
}

function consumePasswordReset(string $token): void {
    db()->prepare("UPDATE password_resets SET used_at=NOW() WHERE token=?")
       ->execute([$token]);
}

// ── Games & Products ──────────────────────────────────────────
function getPopularGames(): array {
    $stmt = db()->query("
        SELECT g.*, c.name AS category_name
        FROM games g
        JOIN categories c ON c.id = g.category_id
        WHERE g.is_popular = 1 AND g.is_active = 1
        ORDER BY g.sort_order ASC
        LIMIT 9
    ");
    return $stmt->fetchAll();
}

function getAllGames(int $categoryId = 0): array {
    if ($categoryId > 0) {
        $stmt = db()->prepare("
            SELECT g.*, c.name AS category_name FROM games g
            JOIN categories c ON c.id = g.category_id
            WHERE g.category_id = ? AND g.is_active = 1
            ORDER BY g.sort_order ASC
        ");
        $stmt->execute([$categoryId]);
    } else {
        $stmt = db()->query("
            SELECT g.*, c.name AS category_name FROM games g
            JOIN categories c ON c.id = g.category_id
            WHERE g.is_active = 1 ORDER BY g.sort_order ASC
        ");
    }
    return $stmt->fetchAll();
}

function getGameBySlug(string $slug): ?array {
    $stmt = db()->prepare("SELECT * FROM games WHERE slug = ? AND is_active = 1");
    $stmt->execute([$slug]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function getProductsByGame(int $gameId): array {
    $stmt = db()->prepare("
        SELECT * FROM products
        WHERE game_id = ? AND is_active = 1
        ORDER BY sort_order ASC, price_sell ASC
    ");
    $stmt->execute([$gameId]);
    return $stmt->fetchAll();
}

function getCategories(): array {
    return db()->query("SELECT * FROM categories WHERE is_active=1 ORDER BY sort_order ASC")->fetchAll();
}

function getBanners(): array {
    return db()->query("SELECT * FROM banners WHERE is_active=1 ORDER BY sort_order ASC")->fetchAll();
}

// ── Order ──────────────────────────────────────────────────────
function getOrderByCode(string $code): ?array {
    $stmt = db()->prepare("
        SELECT o.*, o.product_name AS pname, py.status AS pay_status,
               py.midtrans_id, py.payment_method, t.status AS topup_status, t.message AS topup_msg
        FROM orders o
        LEFT JOIN payments py ON py.order_id = o.id
        LEFT JOIN transactions t ON t.order_id = o.id
        WHERE o.order_code = ?
    ");
    $stmt->execute([$code]);
    $row = $stmt->fetch();
    return $row ?: null;
}

// ── Digiflazz API ─────────────────────────────────────────────
function digiRequest(string $endpoint, array $body): array {
    $url = DIGI_BASE_URL . $endpoint;
    $payload = json_encode($body);
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_TIMEOUT        => 30,
    ]);
    $resp = curl_exec($ch);
    unset($ch);
    return json_decode($resp, true) ?? [];
}

function digiTopUp(string $buyerSkuCode, string $customerNo, string $refId): array {
    return digiRequest('/transaction', [
        'username'       => digiUsername(),
        'buyer_sku_code' => $buyerSkuCode,
        'customer_no'    => $customerNo,
        'ref_id'         => $refId,
        'sign'           => digiSign($refId),
        'testing'        => digiEnv() === 'dev',
    ]);
}

function digiCheckBalance(): array {
    $user = digiUsername();
    $sign = md5($user . digiApiKey() . 'depo');
    return digiRequest('/cek-saldo', [
        'username' => $user,
        'sign'     => $sign,
    ]);
}

// ── Midtrans Snap ─────────────────────────────────────────────
function midtransCreateSnap(array $order, array $product): array {
    $orderCode   = $order['order_code'];
    $grossAmount = (int) $order['amount'];

    $params = [
        'transaction_details' => [
            'order_id'     => $orderCode,
            'gross_amount' => $grossAmount,
        ],
        'customer_details' => [
            'first_name' => $order['buyer_name'] ?? 'Pelanggan',
            'email'      => $order['buyer_email'],
            'phone'      => $order['buyer_phone'] ?? '',
        ],
        'item_details' => [[
            'id'       => (string)($product['id'] ?? '1'),
            'price'    => $grossAmount,
            'quantity' => 1,
            'name'     => mb_substr($product['name'] ?? 'Produk', 0, 50),
        ]],
        'callbacks' => [
            // ✅ Fix: semua callback ke halaman yang menerima GET
            'finish'   => asset('pages/order-success.php') . '?code=' . $orderCode,
            'unfinish' => asset('pages/cek-transaksi.php') . '?code=' . $orderCode,
            'error'    => asset('pages/cek-transaksi.php') . '?code=' . $orderCode,
        ],
    ];

    $snapApiUrl = MIDTRANS_ENV === 'production'
        ? 'https://app.midtrans.com/snap/v1/transactions'
        : 'https://app.sandbox.midtrans.com/snap/v1/transactions';

    $ch = curl_init($snapApiUrl);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($params),
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Basic ' . base64_encode(midtransServerKey() . ':'),
        ],
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_SSL_VERIFYPEER => MIDTRANS_ENV === 'production', // ✅ false hanya di sandbox
        CURLOPT_SSL_VERIFYHOST => MIDTRANS_ENV === 'production' ? 2 : 0,
    ]);

    $resp     = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr  = curl_error($ch);
    unset($ch);

    // Logging detail untuk debug
    if ($curlErr) {
        error_log('[Midtrans] cURL error: ' . $curlErr);
        throw new Exception('Koneksi ke Midtrans gagal: ' . $curlErr);
    }

    $result = json_decode($resp, true) ?? [];

    if ($httpCode !== 201) {
        $errMsg = $result['error_messages'][0] ?? $result['message'] ?? 'Unknown error';
        error_log('[Midtrans] HTTP ' . $httpCode . ' — ' . $resp);
        throw new Exception('Midtrans error (' . $httpCode . '): ' . $errMsg);
    }

    return $result;
}

// ── CSRF — delegated to Security class ───────────────────────
// csrfToken() dan verifyCsrf() sudah didefinisikan di Security.php
// Fungsi di bawah sebagai alias backward-compatible
if (!function_exists('csrfToken')) {
    function csrfToken(): string { return Security::csrfToken(); }
}
if (!function_exists('verifyCsrf')) {
    function verifyCsrf(): void { Security::verifyCsrf(); }
}

// ── Flash messages ────────────────────────────────────────────
function setFlash(string $type, string $msg): void {
    $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
}
function getFlash(): ?array {
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}
// ── Icon image upload & display helpers ───────────────────────
/**
 * Handle upload gambar ikon. Return path gambar (asset url).
 * Jika tidak ada file diupload, kembalikan $existing.
 * Jika file lama lokal & ada gambar baru, file lama dihapus.
 */
function uploadIconImage(string $fileKey, string $subdir, string $prefix, string $existing = ''): string {
    if (empty($_FILES[$fileKey]['name'])) return $existing;
    $errs = Security::validateUpload($_FILES[$fileKey]);
    if ($errs) { setFlash('error', implode(' ', $errs)); return $existing; }
    $dir = __DIR__ . '/../assets/images/' . $subdir . '/';
    if (!is_dir($dir)) @mkdir($dir, 0755, true);
    $ext      = strtolower(pathinfo($_FILES[$fileKey]['name'], PATHINFO_EXTENSION));
    $filename = $prefix . '_' . time() . '_' . uniqid() . '.' . $ext;
    if (move_uploaded_file($_FILES[$fileKey]['tmp_name'], $dir . $filename)) {
        if ($existing && strpos($existing, '/' . $subdir . '/') !== false) {
            $old = $dir . basename($existing);
            if (is_file($old)) @unlink($old);
        }
        return asset('assets/images/' . $subdir . '/' . $filename);
    }
    setFlash('error', 'Gagal upload gambar. Cek permission folder.');
    return $existing;
}

/**
 * Tampilkan ikon: <img> kalau ada gambar, jika tidak fallback ke emoji.
 */
function iconImg(?string $image, string $emoji = '', int $size = 28, int $radius = 8): string {
    if (!empty($image)) {
        return '<img src="' . htmlspecialchars($image) . '" alt="" style="width:' . $size . 'px;height:' . $size
             . 'px;object-fit:cover;border-radius:' . $radius . 'px;display:inline-block;vertical-align:middle;">';
    }
    return '<span style="font-size:' . round($size * 0.82) . 'px;line-height:1;">' . htmlspecialchars($emoji) . '</span>';
}