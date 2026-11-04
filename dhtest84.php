<?php
// dhtest84：召唤师能正常走完1觉，且不再被「四重试炼先做完就锁死深渊试炼」卡住
//  - 召唤师必须额外做深渊试炼(rb1_sum)才能选召唤师之路；
//  - 但若先做完战/法/猎/牧四试炼，任务会跳到 quest=45，旧代码此时不再提供深渊试炼 → 永久卡死；
//  - 修复后 rb_veil 在 quest 40/41/45 都可做，补做完即可继续；
//  - 同时校验 1觉/2觉 入口 handler 结构正确（之前漏了闭合括号导致大片 handler 被吞）。
define('DH_TEST_MODE', true);
require 'includes/bootstrap.php';
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
$ok = 0; $bad = 0;
function ck84(string $l, bool $c, string $d = '') { global $ok, $bad; if ($c) { $ok++; } else { $bad++; echo "FAIL $l $d\n"; } }

// ---- 1) 逻辑：召唤师缺 rb1_sum 被拦；补做后可在 quest 45 完成1觉 ----
$uid = (int) user_create('rb84' . mt_rand(100000, 999999), 'x', 'summoner', 'z1', true, 'tmp');
$_SESSION['uid'] = $uid;
$u = user_by_id($uid);
$u['lv'] = 300; $u['quest'] = 45; user_save($u);
foreach (['rb1_war', 'rb1_mag', 'rb1_hun', 'rb1_pri'] as $k) { cflag_set($uid, $k, 1); }

$r = rb1_choose_job($uid, 'summoner');
ck84('四试炼全过但缺深渊试炼 → 被拦', str_contains($r, '还没完') && empty(cflags($uid)['rb1_job']), $r);

cflag_set($uid, 'rb1_sum', 1); // 模拟在 quest 45 补做深渊试炼
$r = rb1_choose_job($uid, 'summoner');
$u = user_by_id($uid);
ck84('补做深渊试炼后可完成1觉', (cflags($uid)['rb1_job'] ?? '') === 'summoner' && (int) $u['quest'] === 46, $r . ' q=' . (int) $u['quest']);

db()->prepare('DELETE FROM users WHERE id=?')->execute([$uid]);
db()->prepare('DELETE FROM skills WHERE uid=?')->execute([$uid]);
db()->prepare('DELETE FROM mats WHERE uid=?')->execute([$uid]);

// ---- 2) 源码守卫：深渊试炼窗口含 quest 45（动作 + 入口渲染）----
$src = file_get_contents(__DIR__ . '/npc.php');
ck84('深渊试炼动作接受 quest 40/41/45', str_contains($src, "rb_veil' && \$choice === 'try' && in_array((int) \$u['quest'], [40, 41, 45], true)"));
ck84('深渊试炼入口在 quest 45 也显示', str_contains($src, "rb_veil' && in_array((int) \$u['quest'], [40, 41, 45], true)"));

// ---- 3) 源码守卫：1觉 start 动作块已正确闭合（exit; } 后才是 start2）----
ck84('1觉 start 动作块已闭合', str_contains($src, "    exit;\n}\nif (\$who === 'rb_waldon' && \$choice === 'start2'"));

// ---- 4) 源码守卫：1觉/2觉入口渲染不再嵌套（38 块闭合后才判 76）----
ck84('1觉/2觉入口不再嵌套', str_contains($src, "}\nif (\$who === 'rb_waldon' && (int) \$u['quest'] === 76"));

echo "\nOK=$ok FAIL=$bad\n";
