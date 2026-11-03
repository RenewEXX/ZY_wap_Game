<?php
// dhtest81：召唤师技能重做 ——
// 1) s1~s9 每个技能都必须召唤出对应召唤物（s7/s9 以前不召唤）；
// 2) 献祭爆炸伤害远高于召唤物单次普攻（修“爆炸还不如普攻”的垃圾手感）；
// 3) 深渊主宰每 3 回合自动献祭一只并 AoE 爆炸。
define('DH_TEST_MODE', true);
require 'includes/bootstrap.php';
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
$ok = 0; $bad = 0;
function ck81(string $l, bool $c, string $d = ''): void { global $ok, $bad; if ($c) { $ok++; } else { $bad++; echo "FAIL $l $d\n"; } }

function mk81(string $rb2 = ''): int
{
    $uid = (int) user_create('s81' . mt_rand(100000, 999999), 'x', 'summoner', 'z1', true, 'tmp');
    $_SESSION['uid'] = $uid;
    $u = user_by_id($uid);
    $u['lv'] = 300; $u['int'] = 20000; $u['grow_matk'] = 20000;
    $u['maxhp'] = 5000000; $u['hp'] = 5000000; $u['maxmp'] = 999999; $u['mp'] = 999999;
    user_save($u);
    cflag_set($uid, 'rb1_job', 'summoner');
    if ($rb2 !== '') { cflag_set($uid, 'rb2_job', $rb2); }
    cflag_set($uid, 'sin', 200);
    return $uid;
}
function clean81(int $uid): void
{
    db()->exec('DELETE FROM skills WHERE uid=' . $uid);
    db()->exec('DELETE FROM equips WHERE uid=' . $uid);
    db()->exec('DELETE FROM mats WHERE uid=' . $uid);
    db()->exec('DELETE FROM users WHERE id=' . $uid);
    $_SESSION = [];
}

// ===== 1) 每个技能都召唤 =====
$rb2map = ['s5' => 'necro', 's6' => 'plague', 's7' => 'necro', 's8' => 'plague'];
$summonWords = ['深渊小鬼', '骸骨守卫', '憎恶聚合体', '深渊恶鬼', '无面梦魇', '应召而来', '召唤'];
foreach (['s1', 's2', 's3', 's4', 's5', 's6', 's7', 's8', 's9'] as $sid) {
    $uid = mk81($rb2map[$sid] ?? '');
    db()->exec('DELETE FROM skills WHERE uid=' . $uid);
    db()->prepare('INSERT INTO skills (uid,skill,level,prof,tier,active) VALUES (?,?,20,20,10,1)')->execute([$uid, $sid]);
    _gear_uncache($uid);
    $u = user_by_id($uid); $u['hp'] = (int) ($u['maxhp'] * 0.6); user_save($u); $u = user_by_id($uid);
    $b = ['id' => 'rat', 'name' => '木桩', 'hp' => 90000000, 'maxhp' => 90000000, 'atk' => 200, 'def' => 1, 'mdef' => 1,
        'agi' => 0, 'dodge' => 0, 'exp' => 0, 'gold' => 0, 'log' => '', 'num' => 1, 'left' => 1, 'wexp' => 0, 'wgold' => 0, 'drops' => [], 'last' => time()];
    $_SESSION['battle'] = $b;
    $res = battle_round($u, $b, 'skill');
    $log = ($res['log'] ?? '') . ' ' . ($res['flash'] ?? '');
    $hasSum = false;
    foreach ($summonWords as $w) { if (str_contains($log, $w)) { $hasSum = true; break; } }
    ck81("$sid 召唤生效", $hasSum, $log);
    clean81($uid);
}

// ===== 2) 爆炸伤害远高于召唤物单次普攻 =====
$uid = mk81();
$u = user_by_id($uid);
$atk = 1000;
$b = ['id' => 'rat', 'name' => '木桩', 'hp' => 100000000, 'maxhp' => 100000000, 'left' => 1, 'elite' => 0, 'gold' => 1];
$_SESSION['battle'] = $b;
summon_save([
    ['kind' => 'imp', 'name' => '深渊小鬼', 'hp' => 5000, 'maxhp' => 5000, 'atk' => $atk, 'bomb' => false],
    ['kind' => 'imp', 'name' => '深渊小鬼', 'hp' => 5000, 'maxhp' => 5000, 'atk' => $atk, 'bomb' => false],
]);
[$tot, $lg, $hl] = summoner_explode_all($u, $b, (int) $u['id'], 3.5, 0.0);
ck81('2只爆炸总伤远超单只普攻', $tot > $atk * 5, "tot=$tot atk=$atk");
ck81('爆炸后清空召唤列表', count(summon_list()) === 0, json_encode(summon_list()));
clean81($uid);

// ===== 3) 深渊主宰·每3回合献祭 =====
$uid = mk81();
$u = user_by_id($uid);
$b = ['id' => 'rat', 'name' => '木桩', 'hp' => 100000000, 'maxhp' => 100000000, 'left' => 1, 'elite' => 0, 'gold' => 1, 'lord_period' => 3];
$_SESSION['battle'] = $b;
summon_save([['kind' => 'mare', 'name' => '无面梦魇', 'hp' => 9999, 'maxhp' => 9999, 'atk' => 1000, 'bomb' => false]]);
$r1 = summoner_lord_periodic($u, $b, (int) $u['id']);
$r2 = summoner_lord_periodic($u, $b, (int) $u['id']);
$r3 = summoner_lord_periodic($u, $b, (int) $u['id']);
ck81('第1回合不献祭', $r1 === '', $r1);
ck81('第2回合不献祭', $r2 === '', $r2);
ck81('第3回合献祭并AoE爆炸', str_contains($r3, '献祭') && str_contains($r3, 'AoE'), $r3);
ck81('周期献祭清掉那只召唤兽', count(summon_list()) === 0, json_encode(summon_list()));
ck81('周期献祭造成掉血', (int) $b['hp'] < 100000000, (string) $b['hp']);
ck81('献祭后重置计数=3', (int) ($b['lord_period'] ?? 0) === 3, (string) ($b['lord_period'] ?? 'null'));
clean81($uid);

echo "OK=$ok FAIL=$bad\n";
