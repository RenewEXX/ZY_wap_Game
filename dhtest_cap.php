<?php
define('DH_TEST_MODE', true);
require 'includes/bootstrap.php';

$ok = 0; $bad = 0;
function ck(string $l, bool $c, string $d = '') { global $ok,$bad; if ($c) { $ok++; } else { $bad++; echo "FAIL $l $d\n"; } }

$name = '__captest_' . mt_rand(1000, 9999);
$uid = user_create($name, 'x');

// 场景1：未觉醒、未转生，quest=32，lv=298，一次性巨额经验 → 只能到299
db()->prepare('UPDATE users SET lv=298, exp=0, quest=32, chapter_flags=? WHERE id=?')->execute(['{}', $uid]);
$u = user_by_id($uid);
$msg = gain_exp($u, 1000000000);
ck('未觉醒卡299', (int) $u['lv'] === 299, 'lv=' . $u['lv']);
ck('卡级后经验清零', (int) $u['exp'] === 0, 'exp=' . $u['exp']);
ck('卡级提示299', strpos($msg, '299') !== false, $msg);
user_save($u);
$dblv = (int) db()->query('SELECT lv FROM users WHERE id=' . (int) $uid)->fetchColumn();
ck('封顶结算落库', $dblv === 299, 'db-lv=' . $dblv);

// 场景1b：再给经验仍在299不再涨
$u = user_by_id($uid);
gain_exp($u, 1000000000);
ck('持续给经验仍299', (int) $u['lv'] === 299, 'lv=' . $u['lv']);

// 场景2：1觉（rb1_job）但未2觉，lv=598，巨额经验 → 只能到599
cflag_set($uid, 'rb1_job', 'warrior');
db()->prepare('UPDATE users SET lv=598, exp=0 WHERE id=?')->execute([$uid]);
$u = user_by_id($uid);
$msg = gain_exp($u, 100000000000000);
ck('1觉未2觉卡599', (int) $u['lv'] === 599, 'lv=' . $u['lv']);
ck('2觉瓶颈提示', strpos($msg, '599') !== false, $msg);
ck('2觉卡级经验清零', (int) $u['exp'] === 0, 'exp=' . $u['exp']);

// 场景2b：再给经验仍599不再涨
$u = user_by_id($uid);
gain_exp($u, 100000000000000);
ck('持续给经验仍599', (int) $u['lv'] === 599, 'lv=' . $u['lv']);

// 场景3：已2觉，lv=598，巨额经验 → 可越过599（到999封顶）
cflag_set($uid, 'rb2_job', 'berserker');
db()->prepare('UPDATE users SET lv=598, exp=0 WHERE id=?')->execute([$uid]);
$u = user_by_id($uid);
gain_exp($u, 100000000000000);
ck('2觉可越599', (int) $u['lv'] > 599, 'lv=' . $u['lv']);
ck('2觉封顶999', (int) $u['lv'] === 999, 'lv=' . $u['lv']);

// 清理
db()->prepare('DELETE FROM users WHERE id=?')->execute([$uid]);
db()->prepare('DELETE FROM equips WHERE uid=?')->execute([$uid]);
db()->prepare('DELETE FROM mats WHERE uid=?')->execute([$uid]);

echo "\nOK=$ok FAIL=$bad\n";
