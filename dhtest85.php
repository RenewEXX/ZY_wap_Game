<?php
// dhtest85：觉醒流程引导（各职业只做本职业试炼）
//  - 1觉只显示自己的那一项：战士→战士试炼、召唤师→深渊试炼，不再列出其它职业；
//  - 完成后显示 1/1 并提示回瓦尔顿觉醒；
//  - 1觉完成后引导去第四章（北风）；2觉窗口显示虚空试炼。
define('DH_TEST_MODE', true);
require 'includes/bootstrap.php';
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
$ok = 0; $bad = 0;
function ck85(string $l, bool $c, string $d = '') { global $ok, $bad; if ($c) { $ok++; } else { $bad++; echo "FAIL $l $d\n"; } }

// 召唤师：只显示自己的深渊试炼
$uid = (int) user_create('rb85s' . mt_rand(100000, 999999), 'x', 'summoner', 'z1', true, 'tmp');
$_SESSION['uid'] = $uid;
$u = user_by_id($uid); $u['lv'] = 320; $u['quest'] = 41; user_save($u);
cflag_set($uid, 'rb1_start', 1);
$u = user_by_id($uid);
$g = awaken_guide($u);
ck85('召唤师显示 0/1', str_contains($g, '（0/1）'), $g);
ck85('召唤师只显示深渊试炼', str_contains($g, '深渊试炼') && !str_contains($g, '战士试炼'), $g);
ck85('召唤师不再出现其它职业试炼', !str_contains($g, '牧师试炼') && !str_contains($g, '法师试炼'), $g);

cflag_set($uid, 'rb1_sum', 1);
$u = user_by_id($uid); $g = awaken_guide($u);
ck85('召唤师做完深渊 1/1', str_contains($g, '（1/1）') && str_contains($g, '本职业试炼完成'), $g);

// 选定路线后隐藏，并指向第四章
cflag_set($uid, 'rb1_job', 'summoner');
$u = user_by_id($uid); $u['quest'] = 46; user_save($u); $u = user_by_id($uid);
ck85('1觉完成后不再显示引导', awaken_guide($u) === '', awaken_guide($u));
$qs = quest_state($u);
ck85('1觉后引导去第四章·北风', str_contains((string) $qs['name'], '凛冬') && ($qs['loc'] ?? '') === 'capital_gate', json_encode($qs, JSON_UNESCAPED_UNICODE));
db()->prepare('DELETE FROM users WHERE id=?')->execute([$uid]);
db()->prepare('DELETE FROM skills WHERE uid=?')->execute([$uid]);
db()->prepare('DELETE FROM mats WHERE uid=?')->execute([$uid]);

// 战士：只显示自己的战士试炼
$uid2 = (int) user_create('rb85w' . mt_rand(100000, 999999), 'x', 'warrior', 'z1', true, 'tmp');
$u2 = user_by_id($uid2); $u2['lv'] = 320; $u2['quest'] = 41; user_save($u2);
cflag_set($uid2, 'rb1_start', 1);
$u2 = user_by_id($uid2); $g2 = awaken_guide($u2);
ck85('战士显示 0/1', str_contains($g2, '（0/1）'), $g2);
ck85('战士只显示战士试炼', str_contains($g2, '战士试炼') && !str_contains($g2, '深渊试炼') && !str_contains($g2, '法师试炼'), $g2);

cflag_set($uid2, 'rb1_war', 1);
$u2 = user_by_id($uid2); $g2 = awaken_guide($u2);
ck85('战士做完 1/1', str_contains($g2, '（1/1）') && str_contains($g2, '本职业试炼完成'), $g2);

// 2觉引导
$u3 = user_by_id($uid2); $u3['lv'] = 600; $u3['quest'] = 77; user_save($u3);
cflag_set($uid2, 'rb2_start', 1); $u3 = user_by_id($uid2);
$g3 = awaken_guide($u3);
ck85('2觉引导出现', str_contains($g3, '2觉·群攻之路') && str_contains($g3, '虚空试炼'), $g3);
db()->prepare('DELETE FROM users WHERE id=?')->execute([$uid2]);
db()->prepare('DELETE FROM skills WHERE uid=?')->execute([$uid2]);
db()->prepare('DELETE FROM mats WHERE uid=?')->execute([$uid2]);

// 完成本职业试炼后 quest 41→45 立即推进（不必再打一架）
$uid4 = (int) user_create('rb85f' . mt_rand(100000, 999999), 'x', 'hunter', 'z1', true, 'tmp');
$u4 = user_by_id($uid4); $u4['quest'] = 41; user_save($u4);
cflag_set($uid4, 'rb1_hun', 1);
rb1_trial_cleared($uid4);
$u4 = user_by_id($uid4);
ck85('完成本职业试炼自动推进到45', (int) $u4['quest'] === 45, (string) $u4['quest']);

// 只做了别的职业的试炼 → 不推进
$uid5 = (int) user_create('rb85x' . mt_rand(100000, 999999), 'x', 'mage', 'z1', true, 'tmp');
$u5 = user_by_id($uid5); $u5['quest'] = 41; user_save($u5);
cflag_set($uid5, 'rb1_war', 1);
rb1_trial_cleared($uid5);
$u5 = user_by_id($uid5);
ck85('做了别的职业试炼不推进', (int) $u5['quest'] === 41, (string) $u5['quest']);

foreach ([$uid4, $uid5] as $x) {
    db()->prepare('DELETE FROM users WHERE id=?')->execute([$x]);
    db()->prepare('DELETE FROM skills WHERE uid=?')->execute([$x]);
    db()->prepare('DELETE FROM mats WHERE uid=?')->execute([$x]);
}

echo "\nOK=$ok FAIL=$bad\n";
