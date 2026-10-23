<?php
define('DH_TEST_MODE', true);
require 'includes/bootstrap.php';
// 升级自动加点回归：召唤师/法师/牧师都不再自动加智慧/灵力/物攻（走潜力手动分配），战士仍长物攻
$ok = 0; $bad = 0;
function ck32(string $l, bool $c, string $d = '') { global $ok,$bad; if ($c) { $ok++; } else { $bad++; echo "FAIL $l $d\n"; } }

function mk32(string $job): array {
    $uid = user_create('zz32' . mt_rand(10000, 99999), 'x', $job, 'z1', true, 'tmp');
    return user_by_id($uid);
}
function cleanup32(array $ids): void {
    foreach ($ids as $id) {
        foreach (['skills', 'equips', 'mats', 'pets'] as $t) { db()->exec("DELETE FROM {$t} WHERE uid=" . (int) $id); }
        db()->exec('DELETE FROM users WHERE id=' . (int) $id);
    }
}

$ids = [];

// —— 召唤师：升级不自动加智慧/灵力/物攻 ——
$u = mk32('summoner');
$ids[] = (int) $u['id'];
$u['lv'] = 1; $u['exp'] = 0; $u['int'] = 0; $u['spi'] = 0; $u['atk'] = 5; $u['s_pts'] = 0; $u['maxhp'] = 40; $u['maxmp'] = 50;
user_save($u);
// 罪值调高（>=10）以关闭"罪<10 时+20%生命"的干扰，专注验证升级本身的血量成长
db()->prepare('UPDATE users SET chapter_flags=? WHERE id=?')->execute(['{"sin_f":50,"sin":50,"sin_hp":0}', (int) $u['id']]);
$before = user_by_id((int) $u['id']);
gain_exp($u, exp_need(1));
user_save($u);
$after = user_by_id((int) $u['id']);
ck32('召唤师升级到2级', (int) $after['lv'] === 2, (string) $after['lv']);
ck32('召唤师不自动加智慧', (int) $after['int'] === (int) $before['int'], $before['int'] . '->' . $after['int']);
ck32('召唤师不自动加灵力', (int) $after['spi'] === (int) $before['spi'], $before['spi'] . '->' . $after['spi']);
ck32('召唤师不自动加物攻', (int) $after['atk'] === (int) $before['atk'], $before['atk'] . '->' . $after['atk']);
ck32('召唤师潜力点+3', (int) $after['s_pts'] === (int) $before['s_pts'] + 3, $before['s_pts'] . '->' . $after['s_pts']);
ck32('召唤师生命+6', (int) $after['maxhp'] === (int) $before['maxhp'] + 6, $before['maxhp'] . '->' . $after['maxhp']);
ck32('召唤师魔力+10', (int) $after['maxmp'] === (int) $before['maxmp'] + 10, $before['maxmp'] . '->' . $after['maxmp']);

// —— 法师：也不自动加智慧/灵力/物攻，属性点走潜力手动分配 ——
$m = mk32('mage');
$ids[] = (int) $m['id'];
$m['lv'] = 1; $m['exp'] = 0; $m['int'] = 0; $m['spi'] = 0; $m['atk'] = 11; $m['maxhp'] = 42; $m['maxmp'] = 55; $m['s_pts'] = 0;
user_save($m);
$bm = user_by_id((int) $m['id']);
gain_exp($m, exp_need(1));
user_save($m);
$am = user_by_id((int) $m['id']);
ck32('法师不自动加智慧', (int) $am['int'] === (int) $bm['int'], $bm['int'] . '->' . $am['int']);
ck32('法师不自动加灵力', (int) $am['spi'] === (int) $bm['spi'], $bm['spi'] . '->' . $am['spi']);
ck32('法师不加物攻', (int) $am['atk'] === (int) $bm['atk'], $bm['atk'] . '->' . $am['atk']);
ck32('法师潜力点+3', (int) $am['s_pts'] === (int) $bm['s_pts'] + 3, $bm['s_pts'] . '->' . $am['s_pts']);

// —— 战士：长物攻 ——
$w = mk32('warrior');
$ids[] = (int) $w['id'];
$w['lv'] = 1; $w['exp'] = 0; $w['atk'] = 7; $w['maxhp'] = 62; $w['maxmp'] = 20;
user_save($w);
$bw = user_by_id((int) $w['id']);
gain_exp($w, exp_need(1));
user_save($w);
$aw = user_by_id((int) $w['id']);
ck32('战士物攻+1', (int) $aw['atk'] === (int) $bw['atk'] + 1, $bw['atk'] . '->' . $aw['atk']);

cleanup32($ids);
echo "\nOK=$ok FAIL=$bad\n";
