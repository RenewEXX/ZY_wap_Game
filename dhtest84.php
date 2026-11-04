<?php
// dhtest84：召唤师1觉（各职业只做本职业试炼 → 召唤师只需「深渊试炼」）
//  - 召唤师只需 rb1_sum 即可觉醒，不再需要战/法/猎/牧；
//  - 缺 rb1_sum 被拦；补做后（quest 40/41/45 都能做）即可完成；
//  - 同时守卫 npc.php 的关键结构（1觉 start 闭合、1/2觉入口不嵌套）。
define('DH_TEST_MODE', true);
require 'includes/bootstrap.php';
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
$ok = 0; $bad = 0;
function ck84(string $l, bool $c, string $d = '') { global $ok, $bad; if ($c) { $ok++; } else { $bad++; echo "FAIL $l $d\n"; } }

$uid = (int) user_create('rb84' . mt_rand(100000, 999999), 'x', 'summoner', 'z1', true, 'tmp');
$_SESSION['uid'] = $uid;
$u = user_by_id($uid); $u['lv'] = 300; $u['quest'] = 45; user_save($u);
$r = rb1_choose_job($uid, 'summoner');
ck84('召唤师缺深渊试炼被拦', str_contains($r, '还没完成') && empty(cflags($uid)['rb1_job']), $r);

cflag_set($uid, 'rb1_sum', 1); // 只做本职业（深渊）试炼
$r = rb1_choose_job($uid, 'summoner');
$u = user_by_id($uid);
ck84('只需深渊试炼即可完成1觉', (cflags($uid)['rb1_job'] ?? '') === 'summoner' && (int) $u['quest'] === 46, $r . ' q=' . (int) $u['quest']);

db()->prepare('DELETE FROM users WHERE id=?')->execute([$uid]);
db()->prepare('DELETE FROM skills WHERE uid=?')->execute([$uid]);
db()->prepare('DELETE FROM mats WHERE uid=?')->execute([$uid]);
db()->prepare('DELETE FROM mails WHERE uid=?')->execute([$uid]);

$src = file_get_contents(__DIR__ . '/npc.php');
ck84('深渊试炼动作接受 quest 40/41/45', str_contains($src, "rb_veil' && \$choice === 'try' && job_id_of(\$u) === 'summoner' && in_array((int) \$u['quest'], [40, 41, 45], true)"));
ck84('深渊试炼入口在 quest 45 也显示', str_contains($src, "rb_veil' && job_id_of(\$u) === 'summoner' && in_array((int) \$u['quest'], [40, 41, 45], true)"));
ck84('1觉 start 动作块已闭合', str_contains($src, "    exit;\n}\nif (\$who === 'rb_waldon' && \$choice === 'start2'"));
ck84('1觉/2觉入口不再嵌套', str_contains($src, "}\nif (\$who === 'rb_waldon' && (int) \$u['quest'] === 76"));

echo "\nOK=$ok FAIL=$bad\n";
