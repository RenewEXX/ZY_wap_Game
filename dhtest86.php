<?php
// dhtest86：修复「本职业试炼完成、但 quest 停在 40/41（老档），点瓦尔顿没反应」
//  - 瓦尔顿觉醒入口不再写死 quest===45，放宽到 40/41/45；
//  - 这里直接走真实 npc.php 处理：quest=41 + 深渊试炼已做 + 点「觉醒召唤师」→ 必须成功。
define('DH_TEST_MODE', true);
require 'includes/bootstrap.php';
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
$uid = (int) user_create('rb86' . mt_rand(100000, 999999), 'x', 'summoner', 'z1', true, 'tmp');
$_SESSION['uid'] = $uid;
$u = user_by_id($uid);
$u['lv'] = 320; $u['quest'] = 41; $u['loc'] = 'avenue'; $u['maxhp'] = 10000; $u['hp'] = 10000; user_save($u);
cflag_set($uid, 'rb1_start', 1);
cflag_set($uid, 'rb1_sum', 1); // 试炼做完了，但 quest 仍停在 41（旧版本没自动推进）

ob_start();
register_shutdown_function(function () use ($uid) {
    ob_get_clean();
    $f = cflags($uid);
    $u = user_by_id($uid);
    $ok = 0; $bad = 0;
    $ck = function (string $l, bool $c) use (&$ok, &$bad) { if ($c) { $ok++; } else { $bad++; echo "FAIL $l\n"; } };
    $ck('quest41 点瓦尔顿也能觉醒', ($f['rb1_job'] ?? '') === 'summoner');
    $ck('觉醒后 quest=46', (int) $u['quest'] === 46);
    db()->prepare('DELETE FROM users WHERE id=?')->execute([$uid]);
    db()->prepare('DELETE FROM skills WHERE uid=?')->execute([$uid]);
    db()->prepare('DELETE FROM mats WHERE uid=?')->execute([$uid]);
    db()->prepare('DELETE FROM mails WHERE uid=?')->execute([$uid]);
    echo "\nOK=$ok FAIL=$bad\n";
});

$_GET = ['who' => 'rb_waldon', 'choice' => 'summoner'];
include 'npc.php';
