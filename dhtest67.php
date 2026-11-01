<?php
// dhtest67：召唤师挂机续航修复
//  1) 种 session 后，召唤兽第 1 回合就能存住（旧 bug：挂机里首回合召唤当场丢失）
//  2) idle_fight_once：胜利回血，脆皮召唤师连打多场不死
define('DH_TEST_MODE', true);
require 'includes/bootstrap.php';
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
$ok = 0; $bad = 0;
function ck67(string $l, bool $c, string $d = '') { global $ok, $bad; if ($c) { $ok++; } else { $bad++; echo "FAIL $l $d\n"; } }

function mk67(int $maxhp, int $intv): int
{
    $uid = (int) user_create('rb67' . mt_rand(100000, 999999), 'x', 'summoner', 'z1', true, 'tmp');
    $u = user_by_id($uid);
    $u['lv'] = 220; $u['maxhp'] = $maxhp; $u['hp'] = $maxhp; $u['maxmp'] = 999999; $u['mp'] = 999999;
    $u['int'] = $intv; $u['atk'] = 30;
    user_save($u);
    cflag_set($uid, 'rb1_job', 'summoner');
    db()->exec('DELETE FROM skills WHERE uid=' . $uid);
    db()->prepare('INSERT INTO skills (uid,skill,level,prof,tier,active) VALUES (?,?,20,20,10,1)')->execute([$uid, 's1']);
    return $uid;
}
function monster67(int $hp, int $atk): array
{
    return ['id' => 'sim', 'name' => '怪', 'hp' => $hp, 'maxhp' => $hp, 'atk' => $atk, 'def' => 0, 'mdef' => 0,
        'exp' => 0, 'gold' => 0, 'log' => '', 'num' => 1, 'left' => 1, 'wexp' => 0, 'wgold' => 0, 'drops' => [], 'last' => time()];
}
function rm67(int $uid): void
{
    db()->prepare('DELETE FROM users WHERE id=?')->execute([$uid]);
    db()->prepare('DELETE FROM skills WHERE uid=?')->execute([$uid]);
    db()->prepare('DELETE FROM mats WHERE uid=?')->execute([$uid]);
    db()->prepare('DELETE FROM mails WHERE uid=?')->execute([$uid]);
}

// ---- 1) 种 session：首回合召唤兽存得住 ----
$uid = mk67(3000, 800);
$_SESSION = ['uid' => $uid];
$b = monster67(9999999, 0);          // 打不死的木桩，只看召唤是否存进 session
$_SESSION['battle'] = $b;
$u = user_by_id($uid);
battle_round($u, $b, 'tick');
ck67('种session后首回合即有召唤兽', count(summon_status_list()) > 0, 'summons=' . count(summon_status_list()));
rm67($uid);

// ---- 2) 不种 session（旧 bug 复现）：首回合召唤丢失 ----
$uid = mk67(3000, 800);
$_SESSION = ['uid' => $uid];         // 故意不设 $_SESSION['battle']
$b = monster67(9999999, 0);
$u = user_by_id($uid);
battle_round($u, $b, 'tick');
ck67('旧bug：不种session首回合召唤丢失(复现)', count(summon_status_list()) === 0, 'summons=' . count(summon_status_list()));
rm67($uid);

// ---- 3) idle_fight_once：胜利回血 ----
$uid = mk67(3000, 800);
$_SESSION = ['uid' => $uid];
$u = user_by_id($uid);
$u['hp'] = 300;                       // 残血 10%
user_save($u);
$u = user_by_id($uid);
$b = monster67(500, 0);   // 一击必杀、不打人
$r = idle_fight_once($u, $b);
ck67('idle_fight_once 胜利', $r['status'] === 'victory', $r['status']);
ck67('胜利后回血(>300)', (int) $u['hp'] > 300, 'hp=' . (int) $u['hp']);
ck67('回血但不超过上限', (int) $u['hp'] <= (int) $u['maxhp'], 'hp=' . (int) $u['hp'] . '/' . (int) $u['maxhp']);
rm67($uid);

// ---- 4) 脆皮召唤师连打 25 场不死 ----
$uid = mk67(3000, 800);
$_SESSION = ['uid' => $uid];
$u = user_by_id($uid);
$deaths = 0; $fought = 0;
for ($f = 0; $f < 25; $f++) {
    if ((int) $u['hp'] <= 0) { break; }
    $b = monster67(6000, 700);  // 会反打的怪
    $r = idle_fight_once($u, $b);
    user_save($u);              // 模拟挂机循环里 $u 跨场持久（真实代码是在同一 $u 引用上累积）
    $fought++;
    if ($r['status'] === 'dead') { $deaths++; break; }
}
ck67('召唤师挂机25场不死', $deaths === 0, 'fought=' . $fought . ' deaths=' . $deaths);
ck67('确实打完了多场', $fought >= 20, 'fought=' . $fought);
rm67($uid);

// ---- 5) 战士对照也稳（回血不影响正常职业）----
$uid2 = (int) user_create('rb67w' . mt_rand(100000, 999999), 'x', 'warrior', 'z1', true, 'tmp');
$u2 = user_by_id($uid2);
$u2['lv'] = 220; $u2['maxhp'] = 8000; $u2['hp'] = 8000; $u2['maxmp'] = 99999; $u2['mp'] = 99999; $u2['atk'] = 1200;
user_save($u2);
cflag_set($uid2, 'rb1_job', 'warrior');
db()->exec('DELETE FROM skills WHERE uid=' . $uid2);
db()->prepare('INSERT INTO skills (uid,skill,level,prof,tier,active) VALUES (?,?,20,20,10,1)')->execute([$uid2, 'w2']);
$_SESSION = ['uid' => $uid2];
$u = user_by_id($uid2);
$wdeath = 0;
for ($f = 0; $f < 25; $f++) {
    if ((int) $u['hp'] <= 0) { break; }
    $b = monster67(6000, 700);
    $r = idle_fight_once($u, $b);
    user_save($u);
    if ($r['status'] === 'dead') { $wdeath++; break; }
}
ck67('战士挂机25场不死', $wdeath === 0, 'deaths=' . $wdeath);
rm67($uid2);

echo "\nOK=$ok FAIL=$bad\n";
