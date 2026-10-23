<?php
define('DH_TEST_MODE', true);
require 'includes/bootstrap.php';
// 升级成长回归：
// 通用：每级潜力 +3；五个可分配属性（力量/坚毅/体质/智慧/灵力）升级时都【不许自动涨】
// 战士：物防法防+1 魔力+2 ；猎手：物攻+2 魔力+2 ；法师：魔攻+2 魔力+2
// 牧师：生命+5 ；召唤师：生命+2 魔力+5
$ok = 0; $bad = 0;
function ck32(string $l, bool $c, string $d = '') { global $ok,$bad; if ($c) { $ok++; } else { $bad++; echo "FAIL $l $d\n"; } }
$ids = [];

function setupJob32(string $job): array {
    global $ids;
    $uid = user_create('zz32' . mt_rand(10000, 99999), 'x', $job, 'z1', true, 'tmp');
    $ids[] = (int) $uid;
    $u = user_by_id((int) $uid);
    $u['lv'] = 1; $u['exp'] = 0;
    $u['str'] = 0; $u['agi'] = 0; $u['vit'] = 0; $u['int'] = 0; $u['spi'] = 0;
    $u['grow_def'] = 0; $u['grow_matk'] = 0;
    $u['maxhp'] = 100; $u['maxmp'] = 50; $u['hp'] = 100; $u['mp'] = 50;
    $u['s_pts'] = 0; $u['atk'] = 10; $u['def'] = 5;
    user_save($u);
    // 罪值>=10 关闭"罪<10 时 +20% 生命"，好精确校验
    db()->prepare('UPDATE users SET chapter_flags=? WHERE id=?')->execute(['{"sin_f":50,"sin":50,"sin_hp":0}', (int) $uid]);
    return user_by_id((int) $uid);
}
function levelOnce32(array $u): array {
    gain_exp($u, exp_need(1));
    user_save($u);
    return user_by_id((int) $u['id']);
}
// 五个可分配属性升级都不能自动涨
function attrsUnchanged32(string $tag, array $b, array $a): void {
    foreach (['str' => '力量', 'agi' => '坚毅', 'vit' => '体质', 'int' => '智慧', 'spi' => '灵力'] as $k => $nm) {
        ck32("$tag 升级不自动加$nm($k)", (int) $a[$k] === (int) $b[$k], $b[$k] . '->' . $a[$k]);
    }
}

// —— 战士：物防法防+1，魔力+2，属性不动 ——
$b = setupJob32('warrior'); $a = levelOnce32($b);
ck32('战士升级', (int) $a['lv'] === 2);
attrsUnchanged32('战士', $b, $a);
ck32('战士 物防+1', player_pdef($a) === player_pdef($b) + 1, player_pdef($b) . '->' . player_pdef($a));
ck32('战士 法防+1', player_mdef($a) === player_mdef($b) + 1, player_mdef($b) . '->' . player_mdef($a));
ck32('战士 魔力+2', (int) $a['maxmp'] === (int) $b['maxmp'] + 2, $b['maxmp'] . '->' . $a['maxmp']);
ck32('战士 生命未变', (int) $a['maxhp'] === (int) $b['maxhp'], $b['maxhp'] . '->' . $a['maxhp']);
ck32('战士 潜力+3', (int) $a['s_pts'] === (int) $b['s_pts'] + 3);

// —— 猎手：物攻+2，魔力+2，属性不动 ——
$b = setupJob32('hunter'); $a = levelOnce32($b);
attrsUnchanged32('猎手', $b, $a);
ck32('猎手 物攻+2', player_patk($a) === player_patk($b) + 2, player_patk($b) . '->' . player_patk($a));
ck32('猎手 魔力+2', (int) $a['maxmp'] === (int) $b['maxmp'] + 2, $b['maxmp'] . '->' . $a['maxmp']);
ck32('猎手 生命未变', (int) $a['maxhp'] === (int) $b['maxhp'], $b['maxhp'] . '->' . $a['maxhp']);
ck32('猎手 潜力+3', (int) $a['s_pts'] === (int) $b['s_pts'] + 3);

// —— 法师：魔攻+2，魔力+2，属性不动 ——
$b = setupJob32('mage'); $a = levelOnce32($b);
attrsUnchanged32('法师', $b, $a);
ck32('法师 魔攻+2', player_matk($a) === player_matk($b) + 2, player_matk($b) . '->' . player_matk($a));
ck32('法师 魔力+2', (int) $a['maxmp'] === (int) $b['maxmp'] + 2, $b['maxmp'] . '->' . $a['maxmp']);
ck32('法师 生命未变', (int) $a['maxhp'] === (int) $b['maxhp'], $b['maxhp'] . '->' . $a['maxhp']);
ck32('法师 潜力+3', (int) $a['s_pts'] === (int) $b['s_pts'] + 3);

// —— 牧师：生命+5，属性不动 ——
$b = setupJob32('priest'); $a = levelOnce32($b);
attrsUnchanged32('牧师', $b, $a);
ck32('牧师 生命+5', (int) $a['maxhp'] === (int) $b['maxhp'] + 5, $b['maxhp'] . '->' . $a['maxhp']);
ck32('牧师 魔力未变', (int) $a['maxmp'] === (int) $b['maxmp'], $b['maxmp'] . '->' . $a['maxmp']);
ck32('牧师 潜力+3', (int) $a['s_pts'] === (int) $b['s_pts'] + 3);

// —— 召唤师：生命+2，魔力+5，属性不动 ——
$b = setupJob32('summoner'); $a = levelOnce32($b);
attrsUnchanged32('召唤师', $b, $a);
ck32('召唤师 生命+2', (int) $a['maxhp'] === (int) $b['maxhp'] + 2, $b['maxhp'] . '->' . $a['maxhp']);
ck32('召唤师 魔力+5', (int) $a['maxmp'] === (int) $b['maxmp'] + 5, $b['maxmp'] . '->' . $a['maxmp']);
ck32('召唤师 潜力+3', (int) $a['s_pts'] === (int) $b['s_pts'] + 3);

// 清理
foreach ($ids as $id) {
    foreach (['skills', 'equips', 'mats', 'pets'] as $t) { db()->exec("DELETE FROM {$t} WHERE uid=" . (int) $id); }
    db()->exec('DELETE FROM users WHERE id=' . (int) $id);
}
echo "\nOK=$ok FAIL=$bad\n";
