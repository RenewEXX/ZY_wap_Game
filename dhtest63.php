<?php
// dhtest63：竞技场奖励改版（名次奖励每日/无属性石；段位奖励每2周）
define('DH_TEST_MODE', true);
require 'includes/bootstrap.php';
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
$ok = 0; $bad = 0;
function ck63(string $l, bool $c, string $d = '') { global $ok, $bad; if ($c) { $ok++; } else { $bad++; echo "FAIL $l $d\n"; } }

// 1) 名次奖励：返回 2 元素、无属性石；第1名 1金/200币；单调下降
$r1 = arena_rewards(1);
ck63('名次奖励只返回[金,币]两项', count($r1) === 2, json_encode($r1));
ck63('第1名 1金/200竞技场币', $r1 === [10000, 200], json_encode($r1));
ck63('第1名不是属性石', !isset($r1[2]), '');
$prev = null; $mono = true;
foreach ([1, 2, 3, 10, 20, 50, 100, 300, 700, 1000, 1001, 5000] as $rk) {
    [$g, $c] = arena_rewards($rk);
    if ($prev !== null && ($g > $prev[0] || $c > $prev[1])) { $mono = false; }
    $prev = [$g, $c];
}
ck63('名次奖励依次下降', $mono, '');
ck63('第1001名以后最低档', arena_rewards(9999) === [50, 5], json_encode(arena_rewards(9999)));

// 2) 段位奖励：王者=40金(400000铜)/50币；黑铁=0
$king = arena_tier_bonus('king');
ck63('王者段位=40金/50币', $king === [400000, 50], json_encode($king));
ck63('王者是40金(fmt)', fmt_money($king[0]) === '40金', fmt_money($king[0]));
ck63('黑铁段位=0', arena_tier_bonus('iron') === [0, 0], json_encode(arena_tier_bonus('iron')));
ck63('段位奖励无属性石(2项)', count($king) === 2, '');

// 3) 双周周期：14天一个周期
$p = arena_tier_period();
ck63('段位周期为整数', is_int($p) && $p > 0, (string) $p);

// 4) 段位结算：到点给真人发段位奖励邮件
$uid = (int) user_create('tiersettle' . mt_rand(1000, 9999), 'x', 'warrior', 'z1', true, 'tmp');
$zone = arena_zone();
$season = arena_season();
db()->prepare("DELETE FROM arena_rank WHERE uid=? AND zone=?")->execute([$uid, $zone]);
db()->prepare('INSERT INTO arena_rank (uid, zone, rank, name, job, lv, power, is_dummy, season) VALUES (?, ?, 1, ?, "warrior", 200, 99999, 0, ?)')
    ->execute([$uid, $zone, '段位测试', $season]);
$key = 'arena_tier_period_' . $zone;
$period = arena_tier_period();
db()->prepare('INSERT OR REPLACE INTO sys_kv (k, num) VALUES (?, ?)')->execute([$key, $period - 1]);
$before = (int) db()->query('SELECT COUNT(*) FROM mails WHERE uid=' . $uid)->fetchColumn();
arena_tier_settle($zone);
$after = (int) db()->query('SELECT COUNT(*) FROM mails WHERE uid=' . $uid)->fetchColumn();
ck63('双周段位结算发了一封邮件', $after > $before, "before=$before after=$after");
$rec = (int) db()->query('SELECT num FROM sys_kv WHERE k=' . db()->quote($key))->fetchColumn();
ck63('结算后周期已更新', $rec === $period, "rec=$rec period=$period");
// 同周期内再跑一次不重复发
$mid = (int) db()->query('SELECT COUNT(*) FROM mails WHERE uid=' . $uid)->fetchColumn();
arena_tier_settle($zone);
$mid2 = (int) db()->query('SELECT COUNT(*) FROM mails WHERE uid=' . $uid)->fetchColumn();
ck63('同周期不重复发段位奖励', $mid2 === $mid, "mid=$mid mid2=$mid2");

// 清理
db()->prepare('DELETE FROM arena_rank WHERE uid=? AND zone=?')->execute([$uid, $zone]);
db()->prepare('DELETE FROM users WHERE id=?')->execute([$uid]);
db()->prepare('DELETE FROM mats WHERE uid=?')->execute([$uid]);
db()->prepare('DELETE FROM mails WHERE uid=?')->execute([$uid]);
db()->prepare('DELETE FROM sys_kv WHERE k=?')->execute([$key]);

echo "\nOK=$ok FAIL=$bad\n";
