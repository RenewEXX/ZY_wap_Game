<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');
if (empty($_SESSION['uid'])) {
    echo json_encode(['ok' => 0]);
    exit;
}
$u = require_login();
$f = flash_get();
if ($f !== '') {
    flash_set($f);
}
$note = '';
$mats = mats_of((int) $u['id']);
if (!empty($mats['dummy_on'])) {
    $here = loc((string) ($u['loc'] ?? ''));
    if (empty($here['monsters'])) {
        $note = '陪练人偶开着，但这里没怪，去有怪的地图。';
    } elseif ((int) ($mats['dummy_time'] ?? 0) <= 0) {
        $note = '陪练人偶没电了，去商城续费。';
    }
}
echo json_encode(['ok' => 1, 'flash' => $f, 'note' => $note]);
