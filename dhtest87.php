<?php
define('DH_TEST_MODE', true);
require 'includes/bootstrap.php';
// 回归：技能「逐级提升」——原来只在 20 级触发的额外效果，改为 1~20 级线性生效。
// 覆盖：比例函数、显示文本、召唤兽生命/爆炸、猎人 DOT，以及真实战斗回合里的生效。
$ok = 0; $bad = 0;
function ck87(string $l, bool $c, string $d = '') { global $ok, $bad; if ($c) { $ok++; } else { $bad++; echo "FAIL $l $d\n"; } }

// 1) 比例：1级=0，20级=1，2级=1/19
ck87('ratio(1)=0', abs(skill_lv_ratio(1) - 0.0) < 1e-9);
ck87('ratio(20)=1', abs(skill_lv_ratio(20) - 1.0) < 1e-9);
ck87('ratio(2)=1/19', abs(skill_lv_ratio(2) - 1 / 19) < 1e-9);

// 2) 显示文本随等级缩放（用户例子：s3 2级 生命+1% 爆炸+2%；20级 15%/30%）
$t2 = skill_lv_bonus_text('s3', 2);
$t20 = skill_lv_bonus_text('s3', 20);
ck87('s3 2级文案含 生命+1%', str_contains($t2, '生命+1%'), $t2);
ck87('s3 2级文案含 爆炸+2%', str_contains($t2, '爆炸+2%'), $t2);
ck87('s3 20级文案含 生命+15%', str_contains($t20, '生命+15%'), $t20);
ck87('s3 20级文案含 爆炸+30%', str_contains($t20, '爆炸+30%'), $t20);

// 3) 召唤兽生成：生命与爆炸倍率随比例提升
$spawn = function (float $hpPct, float $bombPct) {
    $u = ['id' => 0, 'maxhp' => 1000, '_skill_summon_hp_pct' => $hpPct, '_skill_bomb_pct' => $bombPct];
    return summon_spawn('abom', 100, 1000, $u);
};
$s1 = $spawn(0.0, 0.0);
$s20 = $spawn(15.0, 30.0);
ck87('聚合体 20级生命 > 1级', (int) $s20['maxhp'] > (int) $s1['maxhp'], $s1['maxhp'] . ' vs ' . $s20['maxhp']);
ck87('聚合体 20级爆炸倍率 > 1级', (float) $s20['bomb_mult'] > (float) $s1['bomb_mult'], $s1['bomb_mult'] . ' vs ' . $s20['bomb_mult']);
ck87('聚合体 20级爆炸倍率≈1.95', abs((float) $s20['bomb_mult'] - 1.95) < 1e-6, (string) $s20['bomb_mult']);

// 4) 猎人 DOT 每层伤害随比例提升（20级 ×1.4）
$d0 = hunter_dot_per_stack('bleed', 100, 0.0);
$d20 = hunter_dot_per_stack('bleed', 100, 1.0);
ck87('DOT 20级 > 低等级', $d20 > $d0, $d0 . ' vs ' . $d20);
ck87('DOT 20级=基础×1.4', $d20 === (int) ($d0 * 1.4), $d0 . ' vs ' . $d20);

// 5) 真实战斗回合：同一技能，2级 vs 20级，召唤出的聚合体生命更高（证明战斗中生效）
function mk_sum87(int $level): array {
    $uid = (int) user_create('zz87' . mt_rand(100000, 999999), 'x', 'summoner', 'z1', true, 'tmp');
    db()->exec('DELETE FROM skills WHERE uid=' . $uid);
    db()->prepare('INSERT INTO skills (uid,skill,level,prof,tier,active) VALUES (?,?,?,?,?,?)')
        ->execute([$uid, 's3', $level, 0, 10, 1]);
    $u = user_by_id($uid);
    $u['mp'] = 5000; $u['maxmp'] = 5000; $u['hp'] = $u['maxhp'];
    user_save($u);
    $_SESSION['battle'] = [
        'id' => 'goblin', 'name' => '哥布林', 'hp' => 99999999, 'maxhp' => 99999999, 'atk' => 0,
        'exp' => 1, 'gold' => 1, 'log' => '开打', 'num' => 1, 'left' => 1, 'wexp' => 0, 'wgold' => 0,
        'drops' => [], 'elite' => 0, 'live' => true, 'last' => time(),
    ];
    return [$uid, $u];
}
function cast_s3_87(int $uid): array {
    $u = user_by_id($uid);
    $b = $_SESSION['battle'];
    battle_round($u, $b, 'skill');
    user_save($u);
    $list = $_SESSION['battle']['summons'] ?? [];
    return $list ? end($list) : [];
}
[$uidA] = mk_sum87(2);
$abom2 = cast_s3_87($uidA);
ck87('战斗：2级确实召出聚合体', !empty($abom2['maxhp']));
foreach (['skills', 'equips', 'mats', 'pets'] as $t) { db()->exec("DELETE FROM {$t} WHERE uid=" . (int) $uidA); }
db()->exec('DELETE FROM users WHERE id=' . (int) $uidA);
unset($_SESSION['battle']);

[$uidB] = mk_sum87(20);
$abom20 = cast_s3_87($uidB);
ck87('战斗：20级确实召出聚合体', !empty($abom20['maxhp']));
if (!empty($abom2['maxhp']) && !empty($abom20['maxhp'])) {
    ck87('战斗：20级聚合体生命 > 2级', (int) $abom20['maxhp'] > (int) $abom2['maxhp'], $abom2['maxhp'] . ' vs ' . $abom20['maxhp']);
    ck87('战斗：20级聚合体爆炸倍率 > 2级', (float) $abom20['bomb_mult'] > (float) $abom2['bomb_mult'], $abom2['bomb_mult'] . ' vs ' . $abom20['bomb_mult']);
}
foreach (['skills', 'equips', 'mats', 'pets'] as $t) { db()->exec("DELETE FROM {$t} WHERE uid=" . (int) $uidB); }
db()->exec('DELETE FROM users WHERE id=' . (int) $uidB);
unset($_SESSION['battle']);

echo "\nOK=$ok FAIL=$bad\n";
