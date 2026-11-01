<?php
// dhtest67：召唤师挂机续航修复
//  1) 种 session 后，召唤兽第 1 回合就能存住（旧 bug：挂机里首回合召唤当场丢失）
//  2) 召唤师挂机胜利后回 5% 最大生命；其他职业不回血
//  3) 召唤师连打多场（温和怪物）不死
define('DH_TEST_MODE', true);
require 'includes/bootstrap.php';
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
$ok = 0; $bad = 0;
function ck67(string $l, bool $c, string $d = '') { global $ok, $bad; if ($c) { $ok++; } else { $bad++; echo "FAIL $l $d\n"; } }

function mk67(string $job, int $maxhp, int $intv, int $atk, string $skill): int
{
    $uid = (int) user_create('rb67' . mt_rand(100000, 999999), 'x', $job, 'z1', true, 'tmp');
    $u = user_by_id($uid);
    $u['lv'] = 220; $u['maxhp'] = $maxhp; $u['hp'] = $maxhp; $u['maxmp'] = 999999; $u['mp'] = 999999;
    $u['int'] = $intv; $u['atk'] = $atk;
    user_save($u);
    cflag_set($uid, 'rb1_job', job_id_of(user_by_id($uid)));
    cflag_set($uid, 'sin_f', 10); // 罪值≥10，关掉“罪<10 时 +20% 生命”以隔离测试基线
    cflag_set($uid, 'sin', 10);
    db()->exec('DELETE FROM skills WHERE uid=' . $uid);
    db()->prepare('INSERT INTO skills (uid,skill,level,prof,tier,active) VALUES (?,?,20,20,10,1)')->execute([$uid, $skill]);
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
$uid = mk67('summoner', 3000, 800, 30, 's1');
$_SESSION = ['uid' => $uid];
$b = monster67(9999999, 0);
$_SESSION['battle'] = $b;
$u = user_by_id($uid);
battle_round($u, $b, 'tick');
ck67('种session后首回合即有召唤兽', count(summon_status_list()) > 0, 'summons=' . count(summon_status_list()));
rm67($uid);

// ---- 2) 不种 session（旧 bug 复现）：首回合召唤丢失 ----
$uid = mk67('summoner', 3000, 800, 30, 's1');
$_SESSION = ['uid' => $uid];
$b = monster67(9999999, 0);
$u = user_by_id($uid);
battle_round($u, $b, 'tick');
ck67('旧bug：不种session首回合召唤丢失(复现)', count(summon_status_list()) === 0, 'summons=' . count(summon_status_list()));
rm67($uid);

// ---- 3) 召唤师胜利后回 5% 最大生命 ----
$uid = mk67('summoner', 3000, 800, 30, 's1');
$_SESSION = ['uid' => $uid];
$u = user_by_id($uid);
$u['hp'] = 1000; user_save($u);
$u = user_by_id($uid);
$b = monster67(500, 0);   // 一击必杀、不打人
$r = idle_fight_once($u, $b);
ck67('召唤师挂机胜利', $r['status'] === 'victory', $r['status']);
ck67('召唤师回血=5%(3000×5%=150 => 1150)', (int) $u['hp'] === 1150, 'hp=' . (int) $u['hp']);
rm67($uid);

// ---- 4) 其他职业胜利后不回血 ----
$uid2 = mk67('warrior', 8000, 0, 1200, 'w2');
$_SESSION = ['uid' => $uid2];
$u = user_by_id($uid2);
$u['hp'] = 1000; user_save($u);
$u = user_by_id($uid2);
$b = monster67(500, 0);
$r = idle_fight_once($u, $b);
ck67('战士挂机胜利', $r['status'] === 'victory', $r['status']);
ck67('战士不回血(hp保持1000)', (int) $u['hp'] === 1000, 'hp=' . (int) $u['hp']);
rm67($uid2);

// ---- 5) 召唤师连打 25 场（温和怪物）不死 ----
$uid = mk67('summoner', 3000, 800, 30, 's1');
$_SESSION = ['uid' => $uid];
$u = user_by_id($uid);
$deaths = 0; $fought = 0;
for ($f = 0; $f < 25; $f++) {
    if ((int) $u['hp'] <= 0) { break; }
    $b = monster67(4000, 120);   // 温和怪：召唤兽能扛住
    $r = idle_fight_once($u, $b);
    user_save($u);
    $fought++;
    if ($r['status'] === 'dead') { $deaths++; break; }
}
ck67('召唤师挂机25场(温和怪)不死', $deaths === 0, 'fought=' . $fought . ' deaths=' . $deaths);
ck67('确实打完多场', $fought >= 20, 'fought=' . $fought);
rm67($uid);

// ---- 6) 回血封顶不超过上限 ----
$uid = mk67('summoner', 3000, 800, 30, 's1');
$_SESSION = ['uid' => $uid];
$u = user_by_id($uid);
$u['hp'] = 2980; user_save($u);
$u = user_by_id($uid);
$b = monster67(500, 0);
$r = idle_fight_once($u, $b);
ck67('召唤师回血不超上限', (int) $u['hp'] === 3000, 'hp=' . (int) $u['hp'] . '/3000');
rm67($uid);

echo "\nOK=$ok FAIL=$bad\n";
