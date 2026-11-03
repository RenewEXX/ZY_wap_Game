<?php
// dhtest77：竞技场月度赛季结算整体重构回归
//  1) 赛季=自然月(Y-m)，每月1号05:00切换
//  2) 月末奖励：按上月最终名次发邮件，第1名=王者档，奖励随名次单调不减
//  3) 从旧「按周/存 Y-m-d」升级只记基线、按战力重排修复名次，但不群发邮件
//  4) 月度推进：给上月末真人发结算奖励 + 按战力重排全体(真人+假人)
//     —— 关键：多个真人不会被一股脑塞到同一段名次，名次互不相同且反映战力
define('DH_TEST_MODE', true);
require 'includes/bootstrap.php';
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
$ok = 0; $bad = 0;
function ck77(string $l, bool $c, string $d = '') { global $ok, $bad; if ($c) { $ok++; } else { $bad++; echo "FAIL $l $d\n"; } }
$zone = 'arena';

// 清理该区
db()->prepare('DELETE FROM arena_rank WHERE zone=?')->execute([$zone]);
db()->prepare('DELETE FROM sys_kv WHERE k=?')->execute(['arena_season_roll_' . $zone]);
db()->prepare('DELETE FROM sys_kv WHERE k=?')->execute(['arena_bot_tick_' . $zone]);

// 1) 赛季=自然月
$s = arena_season();
ck77('赛季为 Y-m 格式', (bool) preg_match('/^\d{4}-\d{2}$/', $s), $s);

// 2) 月末奖励：王者最大、随名次单调不减
$prev = null; $mono = true;
foreach ([1, 2, 3, 10, 50, 100, 300, 700, 1000, 1001, 99999] as $rk) {
    [$g, $c] = arena_season_reward($rk);
    if ($prev !== null && ($g > $prev[0] || $c > $prev[1])) { $mono = false; }
    $prev = [$g, $c];
}
ck77('月末奖励随名次单调不减', $mono, '');
$king = arena_season_reward(1);
ck77('第1名(王者)奖励最大', $king[0] > arena_season_reward(2)[0] && $king[1] > arena_season_reward(2)[1], json_encode($king));
ck77('第1名=5金/500竞技场币', $king === [50000, 500], json_encode($king));

// 播种 1000 假人
arena_ensure($zone);

// 造 100 个真人，战力各不相同（index 0 战力最高）
$fakeUids = [];
for ($i = 0; $i < 100; $i++) {
    $fakeUids[] = 950000 + $i;
}
foreach ($fakeUids as $i => $uid) {
    db()->prepare('DELETE FROM arena_rank WHERE uid=?')->execute([$uid]);
    db()->prepare('INSERT INTO arena_rank (uid, zone, rank, name, job, lv, power, is_dummy, season) VALUES (?, ?, ?, ?, ?, ?, ?, 0, ?)')
        ->execute([$uid, $zone, arena_start_rank() + $i, '模拟玩家' . $i, '战士', 200, 30000 - $i * 100, $s]);
}
// 造一个旧的按周格式基线(YYYYMMDD)，触发升级迁移：只记基线、按战力重排，但不群发邮件
db()->prepare('INSERT OR REPLACE INTO sys_kv (k,num) VALUES (?,?)')->execute(['arena_season_roll_' . $zone, 20260101]);
$mailBefore = (int) db()->query('SELECT COUNT(*) FROM mails WHERE uid BETWEEN 950000 AND 950099')->fetchColumn();
$rankBefore = array_map('intval', array_column(db()->query('SELECT uid, rank FROM arena_rank WHERE zone=' . db()->quote($zone) . ' AND is_dummy=0 ORDER BY uid')->fetchAll(), 'rank'));
arena_season_settle($zone);
$mailAfter = (int) db()->query('SELECT COUNT(*) FROM mails WHERE uid BETWEEN 950000 AND 950099')->fetchColumn();
ck77('旧按周格式升级不群发邮件', $mailAfter === $mailBefore, "before=$mailBefore after=$mailAfter");
$rec = (int) db()->query('SELECT num FROM sys_kv WHERE k=' . db()->quote('arena_season_roll_' . $zone))->fetchColumn();
ck77('升级后基线改为当前月(6位)', $rec === (int) str_replace('-', '', $s), "rec=$rec");
$rankAfter = array_map('intval', array_column(db()->query('SELECT uid, rank FROM arena_rank WHERE zone=' . db()->quote($zone) . ' AND is_dummy=0 ORDER BY uid')->fetchAll(), 'rank'));
ck77('升级阶段按战力重排修复旧乱序名次', $rankAfter !== $rankBefore, '');
ck77('升级重排后名次互不相同', count(array_unique($rankAfter)) === count($rankAfter), implode(',', array_slice($rankAfter, 0, 6)));
ck77('升级重排后不再全员同一名次段', $rankAfter !== range(arena_start_rank(), arena_start_rank() + count($rankAfter) - 1), '');

// 4) 月度推进：把基线设为上一个月(6位) → 触发真正的结算
$prevMonth = (int) str_replace('-', '', $s) - 1;
db()->prepare('INSERT OR REPLACE INTO sys_kv (k,num) VALUES (?,?)')->execute(['arena_season_roll_' . $zone, $prevMonth]);
arena_season_settle($zone);

// 4a) 每个真人都收到了月末结算奖励邮件
$groups = db()->query('SELECT uid, COUNT(*) c FROM mails WHERE uid BETWEEN 950000 AND 950099 GROUP BY uid')->fetchAll(PDO::FETCH_ASSOC);
$groupCounts = array_map('intval', array_column($groups, 'c'));
$minMails = $groupCounts === [] ? 0 : min($groupCounts);
ck77('100名真人每人至少1封结算邮件', count($groupCounts) === 100 && $minMails >= 1, 'grouped=' . count($groupCounts) . " min=$minMails");

// 4b) 真人名次互不相同（不全挤在起始名次）
$rows = db()->query('SELECT uid, power, rank FROM arena_rank WHERE zone=' . db()->quote($zone) . ' AND is_dummy=0 ORDER BY power DESC')->fetchAll();
$ranks = array_map('intval', array_column($rows, 'rank'));
ck77('100名真人名次互不相同', count(array_unique($ranks)) === count($ranks), 'n=' . count($ranks));
ck77('真人不再全部坠到起始名次', $ranks !== range(arena_start_rank(), arena_start_rank() + count($ranks) - 1), implode(',', array_slice($ranks, 0, 5)));

// 4c) 按战力排序：战力越高名次越靠前（单调）
$ordered = true;
$prevR = 0;
foreach ($rows as $r) {
    $rk = (int) $r['rank'];
    if ($rk <= $prevR) { $ordered = false; break; }
    $prevR = $rk;
}
ck77('战力越高名次越靠前', $ordered, '');

// 4d) 全体(真人+假人)名次连续 1..N 无空洞
$all = array_map('intval', array_column(db()->query('SELECT rank FROM arena_rank WHERE zone=' . db()->quote($zone) . ' ORDER BY rank')->fetchAll(), 'rank'));
ck77('榜单连续无空洞', $all === range(1, count($all)), 'n=' . count($all));

// 4e) 幂等：再结算一次不重排、不重复发邮件
$mailsMid = (int) db()->query('SELECT COUNT(*) FROM mails WHERE uid BETWEEN 950000 AND 950099')->fetchColumn();
$ranksMid = array_map('intval', array_column(db()->query('SELECT uid, rank FROM arena_rank WHERE zone=' . db()->quote($zone) . ' AND is_dummy=0 ORDER BY uid')->fetchAll(), 'rank'));
arena_season_settle($zone);
$mailsMid2 = (int) db()->query('SELECT COUNT(*) FROM mails WHERE uid BETWEEN 950000 AND 950099')->fetchColumn();
$ranksMid2 = array_map('intval', array_column(db()->query('SELECT uid, rank FROM arena_rank WHERE zone=' . db()->quote($zone) . ' AND is_dummy=0 ORDER BY uid')->fetchAll(), 'rank'));
ck77('重复结算不重复发邮件', $mailsMid2 === $mailsMid, "m=$mailsMid m2=$mailsMid2");
ck77('重复结算不重排名次', $ranksMid2 === $ranksMid, '');

// 清理
foreach ($fakeUids as $uid) {
    db()->prepare('DELETE FROM arena_rank WHERE uid=?')->execute([$uid]);
    db()->prepare('DELETE FROM mails WHERE uid=?')->execute([$uid]);
}
db()->prepare('DELETE FROM arena_rank WHERE zone=?')->execute([$zone]);
db()->prepare('DELETE FROM sys_kv WHERE k=?')->execute(['arena_season_roll_' . $zone]);
db()->prepare('DELETE FROM sys_kv WHERE k=?')->execute(['arena_bot_tick_' . $zone]);
echo "\nOK=$ok FAIL=$bad\n";
