<?php
define('DH_TEST_MODE', true);
require 'includes/bootstrap.php';
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
// 竞技场赛季自愈：arena_rank 主键 (uid,zone)+假人负数uid，跨赛季若没滚动会造成「插不进→竞技场空无一人」
$ok = 0; $bad = 0;
function ck61(string $l, bool $c, string $d = '') { global $ok, $bad; if ($c) { $ok++; } else { $bad++; echo "FAIL $l $d\n"; } }
$season = arena_season();
$zone = 'arena';

function cleanArena61(string $zone, array $uids): void {
    foreach ($uids as $uid) {
        db()->prepare('DELETE FROM users WHERE id=?')->execute([$uid]);
        db()->prepare('DELETE FROM equips WHERE uid=?')->execute([$uid]);
        db()->prepare('DELETE FROM mats WHERE uid=?')->execute([$uid]);
        db()->prepare('DELETE FROM skills WHERE uid=?')->execute([$uid]);
        db()->prepare('DELETE FROM arena_rank WHERE uid=?')->execute([$uid]);
        db()->prepare('DELETE FROM mails WHERE uid=?')->execute([$uid]);
    }
    db()->prepare('DELETE FROM arena_rank WHERE zone=?')->execute([$zone]);
    db()->prepare('DELETE FROM sys_kv WHERE k=?')->execute(['arena_bot_tick_' . $zone]);
    db()->prepare('DELETE FROM sys_kv WHERE k=?')->execute(['arena_season_roll_' . $zone]);
}
cleanArena61($zone, []);

// 1) 首次 ensure：播满 200 个当前赛季假人
arena_ensure($zone);
$st = db()->prepare('SELECT COUNT(*) FROM arena_rank WHERE zone=? AND season=? AND is_dummy=1');
$st->execute([$zone, $season]);
ck61('首次播种1000假人', (int) $st->fetchColumn() === arena_bot_count());

// 2) 名次 1..200 连续
$st = db()->prepare('SELECT rank FROM arena_rank WHERE zone=? AND season=? AND is_dummy=1 ORDER BY rank ASC');
$st->execute([$zone, $season]);
$rr = array_map('intval', array_column($st->fetchAll(), 'rank'));
ck61('假人名次1..1000连续', $rr === range(1, arena_bot_count()));

// 3) 赛季卡住：整区置为旧赛季，ensure 应把 200 假人迁到当前赛季（而非插不进去）
db()->prepare('UPDATE arena_rank SET season=? WHERE zone=?')->execute(['2000-01-01', $zone]);
$cntSt = db()->prepare('SELECT COUNT(*) FROM arena_rank WHERE zone=? AND season=? AND is_dummy=1');
$cntSt->execute([$zone, $season]);
ck61('迁移前当前赛季为0', (int) $cntSt->fetchColumn() === 0);
arena_ensure($zone);
$cntSt->execute([$zone, $season]);
$cnt = (int) $cntSt->fetchColumn();
ck61('ensure自愈后当前赛季=1000', $cnt === arena_bot_count(), (string) $cnt);

// 4) 真人跨赛季不消失
$uid = (int) user_create('ta61' . mt_rand(100000, 999999), 'x', 'warrior', 'z1', true, 'tmp');
db()->prepare('INSERT INTO arena_rank (uid,zone,rank,name,job,lv,power,is_dummy,season) VALUES (?,?,?,?,?,?,?,0,?)')
    ->execute([$uid, $zone, 999, '真人测试', '战士', 123, 4567, '1999-01-01']);
arena_ensure($zone);
$st = db()->prepare('SELECT season FROM arena_rank WHERE uid=? AND zone=?');
$st->execute([$uid, $zone]);
ck61('真人跨赛季被保留并迁入当前赛季', (string) $st->fetchColumn() === $season);

// 5) 目标准备：把真人放到 150 名，前面有假人，targets 应非空
db()->prepare('UPDATE arena_rank SET rank=? WHERE uid=? AND zone=?')->execute([150, $uid, $zone]);
$st = db()->prepare('SELECT * FROM arena_rank WHERE uid=? AND zone=?');
$st->execute([$uid, $zone]);
$me = $st->fetch();
$tg = arena_targets($me);
ck61('可挑战对手非空', count($tg) > 0, (string) count($tg));

// 6) arena_join 遇到已有(旧赛季)行：不崩，返回“已在竞技场”
$uid2 = (int) user_create('tb61' . mt_rand(100000, 999999), 'x', 'warrior', $zone, true, 'tmp');
$u2 = user_by_id($uid2); $u2['lv'] = 300; $u2['last_seen'] = time(); user_save($u2);
db()->prepare('INSERT INTO arena_rank (uid,zone,rank,name,job,lv,power,is_dummy,season) VALUES (?,?,?,?,?,?,?,0,?)')
    ->execute([$uid2, $zone, 500, '旧玩家', '战士', 300, 9999, '1998-01-01']);
$msg = arena_join($uid2);
ck61('跨赛季老玩家 join 不崩且判为已在场', str_contains($msg, '已经'), $msg);

// 7) 全新玩家 join 正常进入（机器人占1..1000，真人从1001起）
$uid3 = (int) user_create('tc61' . mt_rand(100000, 999999), 'x', 'warrior', $zone, true, 'tmp');
$u3 = user_by_id($uid3); $u3['lv'] = 300; $u3['last_seen'] = time(); user_save($u3);
$msg = arena_join($uid3);
$st = db()->prepare('SELECT COUNT(*) FROM arena_rank WHERE uid=? AND zone=? AND season=?');
$st->execute([$uid3, $zone, $season]);
ck61('新玩家join成功入场', (int) $st->fetchColumn() === 1 && str_contains($msg, '欢迎'), $msg);

// 8) 月末赛季结算兜底（不依赖每月1号）：kv 落后时，ensure 立刻结算——真人按战力重排（不再全塞到第N名）、kv推进当前月。
$kvKey = 'arena_season_roll_' . $zone;
db()->prepare('INSERT OR REPLACE INTO sys_kv (k,num) VALUES (?,?)')->execute([$kvKey, 202001]); // 旧月份(6位int)，触发结算
$mailBefore = (int) db()->query('SELECT COUNT(*) FROM mails')->fetchColumn();
arena_ensure($zone);
$st = db()->prepare('SELECT rank FROM arena_rank WHERE zone=? AND season=? AND is_dummy=0 ORDER BY rank ASC');
$st->execute([$zone, $season]);
$realRanks = array_map('intval', array_column($st->fetchAll(), 'rank'));
ck61('赛季结算:真人数=3', count($realRanks) === 3, (string) count($realRanks));
// 真人名次互不相同（按战力排开），且不再是被一股脑塞到同一段名次
ck61('赛季结算:三名真人名次互不相同', count(array_unique($realRanks)) === count($realRanks), implode(',', $realRanks));
ck61('赛季结算:真人不再全部坠到起始名次', $realRanks !== range(arena_start_rank(), arena_start_rank() + count($realRanks) - 1), implode(',', $realRanks));
$st = db()->prepare('SELECT num FROM sys_kv WHERE k=?');
$st->execute([$kvKey]);
$curKey = (int) str_replace('-', '', $season);
ck61('赛季结算:kv推进到当前月', (int) $st->fetchColumn() === $curKey, (string) $st->fetchColumn());
// 全体（真人+机器人）名次连续 1..N 无空洞
$st = db()->prepare('SELECT rank FROM arena_rank WHERE zone=? AND season=? ORDER BY rank ASC');
$st->execute([$zone, $season]);
$allR = array_map('intval', array_column($st->fetchAll(), 'rank'));
ck61('赛季结算:榜单连续无空洞', $allR === range(1, count($allR)), 'n=' . count($allR));
// 结算给真人发了月末结算奖励邮件
$mailAfter = (int) db()->query('SELECT COUNT(*) FROM mails')->fetchColumn();
ck61('赛季结算:发放了月末奖励邮件', $mailAfter > $mailBefore, "before=$mailBefore after=$mailAfter");
// 再调一次不应重复结算（kv 已是当前月，真人名次保持不变）
$mailsMid = (int) db()->query('SELECT COUNT(*) FROM mails')->fetchColumn();
arena_ensure($zone);
$st = db()->prepare('SELECT rank FROM arena_rank WHERE zone=? AND season=? AND is_dummy=0 ORDER BY rank ASC');
$st->execute([$zone, $season]);
$again = array_map('intval', array_column($st->fetchAll(), 'rank'));
ck61('重复访问不重复结算', $again === $realRanks, implode(',', $again));
$mailsMid2 = (int) db()->query('SELECT COUNT(*) FROM mails')->fetchColumn();
ck61('重复访问不重复发邮件', $mailsMid2 === $mailsMid, "mid=$mailsMid mid2=$mailsMid2");

cleanArena61($zone, [$uid, $uid2, $uid3]);
echo "OK=$ok FAIL=$bad\n";
