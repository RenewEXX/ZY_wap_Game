<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');
if (empty($_SESSION['uid'])) {
    session_write_close();
    echo json_encode(['ok' => 0]);
    exit;
}
// 轻量探活：不跑任何tick，只查人偶状态，不碰session锁
$uid = (int) $_SESSION['uid'];
session_write_close();
try {
    $st = db()->prepare('SELECT mat, num FROM mats WHERE uid=? AND mat IN ("dummy_on","dummy_time")');
    $st->execute([$uid]);
    $m = [];
    while ($row = $st->fetch()) {
        $m[$row['mat']] = (int) $row['num'];
    }
    $note = '';
    if (!empty($m['dummy_on']) && (int) ($m['dummy_time'] ?? 0) <= 0) {
        $note = '陪练人偶没电了，去商城续费。';
    }
    echo json_encode(['ok' => 1, 'note' => $note]);
} catch (Throwable $e) {
    echo json_encode(['ok' => 0]);
}
