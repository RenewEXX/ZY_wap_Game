<?php
// dhtest85：觉醒流程引导
//  - 「进行中」不再被"已通关"一句盖掉，1觉/2觉每一步都要可见；
//  - 召唤师额外显示「深渊试炼」，做完四试炼但仍缺深渊时要明确提示补做；
//  - 1觉完成后应指向第四章（北风），而不是重复叫你去开启1觉；
//  - 非召唤师总数是 /4 且不显示深渊项；2觉窗口显示虚空试炼。
define('DH_TEST_MODE', true);
require 'includes/bootstrap.php';
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
$ok = 0; $bad = 0;
function ck85(string $l, bool $c, string $d = '') { global $ok, $bad; if ($c) { $ok++; } else { $bad++; echo "FAIL $l $d\n"; } }

$uid = (int) user_create('rb85s' . mt_rand(100000, 999999), 'x', 'summoner', 'z1', true, 'tmp');
$_SESSION['uid'] = $uid;
$u = user_by_id($uid); $u['lv'] = 320; $u['quest'] = 41; user_save($u);
foreach (['rb1_start', 'rb1_war', 'rb1_mag'] as $k) { cflag_set($uid, $k, 1); }
$u = user_by_id($uid);
$g = awaken_guide($u);
ck85('召唤师1觉显示 2/5', str_contains($g, '（2/5）'), $g);
ck85('召唤师含深渊试炼项', str_contains($g, '深渊试炼'));
ck85('含四试炼去处', str_contains($g, '战士试炼') && str_contains($g, '牧师试炼'));

// 四试炼做完、缺深渊 → 4/5 + 明确补做提示
cflag_set($uid, 'rb1_hun', 1); cflag_set($uid, 'rb1_pri', 1);
$u = user_by_id($uid); $u['quest'] = 45; user_save($u); $u = user_by_id($uid);
$g = awaken_guide($u);
ck85('缺深渊显示 4/5', str_contains($g, '（4/5）'), $g);
ck85('缺深渊给出补做提示', str_contains($g, '补做深渊试炼'), $g);

// 补做深渊 → 5/5 完成
cflag_set($uid, 'rb1_sum', 1);
$u = user_by_id($uid); $g = awaken_guide($u);
ck85('补做后 5/5', str_contains($g, '（5/5）'), $g);
ck85('完成提示选路', str_contains($g, '试炼全部完成'), $g);

// 选定路线后隐藏引导，并指向第四章
cflag_set($uid, 'rb1_job', 'summoner');
$u = user_by_id($uid); $u['quest'] = 46; user_save($u); $u = user_by_id($uid);
ck85('1觉完成后不再显示1觉引导', awaken_guide($u) === '', awaken_guide($u));
$qs = quest_state($u);
ck85('1觉后引导去第四章·北风', str_contains((string) $qs['name'], '凛冬') && ($qs['loc'] ?? '') === 'capital_gate', json_encode($qs, JSON_UNESCAPED_UNICODE));

// 非召唤师：/4 且无深渊
$uid2 = (int) user_create('rb85w' . mt_rand(100000, 999999), 'x', 'warrior', 'z1', true, 'tmp');
$u2 = user_by_id($uid2); $u2['lv'] = 320; $u2['quest'] = 41; user_save($u2);
cflag_set($uid2, 'rb1_start', 1);
$u2 = user_by_id($uid2); $g2 = awaken_guide($u2);
ck85('战士1觉显示 0/4', str_contains($g2, '（0/4）'), $g2);
ck85('非召唤师无深渊项', !str_contains($g2, '深渊试炼'), $g2);

// 2觉引导
$u3 = user_by_id($uid2); $u3['lv'] = 600; $u3['quest'] = 77; user_save($u3);
cflag_set($uid2, 'rb2_start', 1); $u3 = user_by_id($uid2);
$g3 = awaken_guide($u3);
ck85('2觉引导出现', str_contains($g3, '2觉·群攻之路') && str_contains($g3, '虚空试炼'), $g3);

foreach ([$uid, $uid2] as $x) {
    db()->prepare('DELETE FROM users WHERE id=?')->execute([$x]);
    db()->prepare('DELETE FROM skills WHERE uid=?')->execute([$x]);
    db()->prepare('DELETE FROM mats WHERE uid=?')->execute([$x]);
}
echo "\nOK=$ok FAIL=$bad\n";
