<?php
/* ═══════════════════════════════════════════════════
   FTARASTORE — Notification System
   includes/notifications.php

   Usage:
   require_once __DIR__.'/notifications.php';
   Notif::send($userId, 'tx_success', 'Top Up Berhasil!', 'Diamond 100 berhasil dikreditkan.', $url);
   Notif::getUnread($userId);
   ═══════════════════════════════════════════════════ */

class Notif {

    // Kirim notifikasi ke user
    public static function send(
        int    $userId,
        string $type,
        string $title,
        string $message = '',
        string $url     = ''
    ): void {
        try {
            db()->prepare("
                INSERT INTO notifications (user_id, type, title, message, url, is_read, created_at)
                VALUES (?, ?, ?, ?, ?, 0, NOW())
            ")->execute([$userId, $type, $title, $message, $url ?: null]);
        } catch (\Exception $e) {
            error_log('[Notif] send error: ' . $e->getMessage());
        }
    }

    // Kirim ke semua user (broadcast — misal promo baru)
    public static function broadcast(
        string $type,
        string $title,
        string $message = '',
        string $url     = ''
    ): void {
        try {
            $users = db()->query("SELECT id FROM users WHERE role='user' AND is_active=1")->fetchAll();
            $stmt  = db()->prepare("INSERT INTO notifications (user_id,type,title,message,url,is_read,created_at) VALUES (?,?,?,?,?,0,NOW())");
            foreach ($users as $u) {
                $stmt->execute([$u['id'], $type, $title, $message, $url ?: null]);
            }
        } catch (\Exception $e) {
            error_log('[Notif] broadcast error: ' . $e->getMessage());
        }
    }

    // Ambil notifikasi user (unread dulu)
    public static function get(int $userId, int $limit = 15): array {
        try {
            $stmt = db()->prepare("
                SELECT * FROM notifications
                WHERE user_id = ?
                ORDER BY is_read ASC, created_at DESC
                LIMIT ?
            ");
            $stmt->execute([$userId, $limit]);
            return $stmt->fetchAll();
        } catch (\Exception $e) {
            return [];
        }
    }

    // Hitung unread
    public static function countUnread(int $userId): int {
        try {
            $stmt = db()->prepare("SELECT COUNT(*) FROM notifications WHERE user_id=? AND is_read=0");
            $stmt->execute([$userId]);
            return (int)$stmt->fetchColumn();
        } catch (\Exception $e) {
            return 0;
        }
    }

    // Tandai semua sudah dibaca
    public static function markAllRead(int $userId): void {
        try {
            db()->prepare("UPDATE notifications SET is_read=1 WHERE user_id=?")->execute([$userId]);
        } catch (\Exception $e) {}
    }

    // Tandai satu sudah dibaca
    public static function markRead(int $notifId, int $userId): void {
        try {
            db()->prepare("UPDATE notifications SET is_read=1 WHERE id=? AND user_id=?")->execute([$notifId, $userId]);
        } catch (\Exception $e) {}
    }

    // Icon per type
    public static function icon(string $type): string {
        return match($type) {
            'tx_success' => '✅',
            'tx_pending' => '⏳',
            'tx_failed'  => '❌',
            'promo'      => '🎁',
            'system'     => '🔔',
            default      => '📢',
        };
    }

    // Label per type
    public static function label(string $type): string {
        return match($type) {
            'tx_success' => 'Transaksi Berhasil',
            'tx_pending' => 'Menunggu Pembayaran',
            'tx_failed'  => 'Transaksi Gagal',
            'promo'      => 'Promo Baru',
            'system'     => 'Sistem',
            default      => 'Notifikasi',
        };
    }

    // Kirim notif transaksi sukses + tambah poin
    public static function onTransactionSuccess(int $userId, array $order): void {
        $title   = '✅ Top Up Berhasil!';
        $message = $order['product_name'] . ' berhasil dikreditkan ke akun kamu.';
        $url     = '/pages/cek-transaksi.php?code=' . ($order['order_code'] ?? '');
        self::send($userId, 'tx_success', $title, $message, $url);

        // Tambah poin: 1 poin per Rp 1.000
        $points = (int)floor(($order['amount'] ?? 0) / 1000);
        if ($points > 0) {
            self::addPoints($userId, $points, 'Top Up: ' . ($order['product_name'] ?? ''), $order['id'] ?? null);
        }
    }

    // Kirim notif pending
    public static function onTransactionPending(int $userId, array $order): void {
        $title   = '⏳ Menunggu Pembayaran';
        $message = 'Order ' . ($order['order_code'] ?? '') . ' menunggu konfirmasi pembayaran.';
        $url     = '/pages/cek-transaksi.php?code=' . ($order['order_code'] ?? '');
        self::send($userId, 'tx_pending', $title, $message, $url);
    }

    // Kirim notif gagal
    public static function onTransactionFailed(int $userId, array $order): void {
        $title   = '❌ Transaksi Gagal';
        $message = 'Order ' . ($order['order_code'] ?? '') . ' gagal diproses. Hubungi CS untuk bantuan.';
        $url     = '/pages/cek-transaksi.php?code=' . ($order['order_code'] ?? '');
        self::send($userId, 'tx_failed', $title, $message, $url);
    }

    // Tambah poin ke user
    public static function addPoints(int $userId, int $points, string $desc = '', ?int $orderId = null): void {
        try {
            $db = db();
            // Insert point transaction
            $db->prepare("INSERT INTO point_transactions (user_id,order_id,type,points,description,created_at) VALUES (?,?,'earn',?,?,NOW())")
               ->execute([$userId, $orderId, $points, $desc]);

            // Update user_points
            $db->prepare("INSERT INTO user_points (user_id,points,level,total_spent,updated_at)
                          VALUES (?,?,
                            CASE WHEN ? >= 5000000 THEN 'platinum'
                                 WHEN ? >= 2000000 THEN 'gold'
                                 WHEN ? >= 500000  THEN 'silver'
                                 ELSE 'bronze' END,
                            ?,NOW())
                          ON DUPLICATE KEY UPDATE
                            points      = points + ?,
                            total_spent = total_spent + ?,
                            level       = CASE WHEN total_spent+? >= 5000000 THEN 'platinum'
                                               WHEN total_spent+? >= 2000000 THEN 'gold'
                                               WHEN total_spent+? >= 500000  THEN 'silver'
                                               ELSE 'bronze' END,
                            updated_at  = NOW()
            ")->execute([
                $userId, $points,
                0, 0, 0, 0, // for INSERT values (spent = 0 initially)
                $points, 0, // for UPDATE points + spent
                0, 0, 0,    // for UPDATE level calculation
            ]);
        } catch (\Exception $e) {
            error_log('[Notif] addPoints error: ' . $e->getMessage());
        }
    }
}