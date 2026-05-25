<?php
/* ═══════════════════════════════════════════
   FTARASTORE — Notification API Endpoint
   api/notif.php

   GET  ?action=get        → list notifikasi
   GET  ?action=count      → jumlah unread
   POST ?action=read_all   → mark all read
   POST ?action=read&id=X  → mark one read
   ═══════════════════════════════════════════ */

require_once __DIR__.'/../includes/functions.php';
require_once __DIR__.'/../includes/notifications.php';

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

if (!isLoggedIn()) {
    echo json_encode(['ok' => false, 'error' => 'Unauthorized']);
    exit;
}

$uid    = (int)$_SESSION['user_id'];
$action = $_GET['action'] ?? $_POST['action'] ?? 'get';

switch ($action) {

    case 'count':
        echo json_encode([
            'ok'     => true,
            'unread' => Notif::countUnread($uid),
        ]);
        break;

    case 'get':
        $notifs = Notif::get($uid, 15);
        $items  = [];
        foreach ($notifs as $n) {
            $items[] = [
                'id'      => (int)$n['id'],
                'type'    => $n['type'],
                'icon'    => Notif::icon($n['type']),
                'label'   => Notif::label($n['type']),
                'title'   => $n['title'],
                'message' => $n['message'],
                'url'     => $n['url'],
                'is_read' => (bool)$n['is_read'],
                'time'    => date('d M, H:i', strtotime($n['created_at'])),
                'time_ago'=> timeAgo($n['created_at']),
            ];
        }
        echo json_encode([
            'ok'     => true,
            'items'  => $items,
            'unread' => Notif::countUnread($uid),
        ]);
        break;

    case 'read_all':
        Notif::markAllRead($uid);
        echo json_encode(['ok' => true]);
        break;

    case 'read':
        $id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
        if ($id > 0) Notif::markRead($id, $uid);
        echo json_encode(['ok' => true]);
        break;

    default:
        echo json_encode(['ok' => false, 'error' => 'Unknown action']);
}

function timeAgo(string $datetime): string {
    $diff = time() - strtotime($datetime);
    if ($diff < 60)     return 'Baru saja';
    if ($diff < 3600)   return floor($diff/60) . ' menit lalu';
    if ($diff < 86400)  return floor($diff/3600) . ' jam lalu';
    if ($diff < 604800) return floor($diff/86400) . ' hari lalu';
    return date('d M Y', strtotime($datetime));
}