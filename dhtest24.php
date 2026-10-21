<?php
define('DH_TEST_MODE', true);
require 'includes/bootstrap.php';

$ok = 0; $bad = 0;
function ck24(string $l, bool $c, string $d = '') { global $ok,$bad; if ($c) { $ok++; } else { $bad++; echo "FAIL $l $d\n"; } }

// 主要装备部件（除 称号 title / 神魂 soul）
$mainSlots = ['weapon','offhand','body','head','legs','belt','gloves','shoes','back','ring','necklace'];

$drops = world_expansion_drops();
$bosses = world_expansion_bosses();
// 本测试只覆盖第一批（天空岛/沉没圣所）的12只小怪，第二批由 dhtest25 覆盖
$orig12 = ['cloud_hawk','thunder_sprite','sky_sentinel','storm_caller','wind_golem','sky_priest','void_wisp','tide_hunter','drowned_one','coral_guard','siren_singer','deep_crab'];
$normals = [];
foreach ($orig12 as $mid) {
    if (isset($drops[$mid])) { $normals[$mid] = $drops[$mid]; }
}
ck24('第一批普通怪数量=12', count($normals) === 12, (string) count($normals));

// 1) 普通怪掉落覆盖所有主要部件
$slots = [];
foreach ($normals as $mid => $d) {
    $slots[$d['slot']] = true;
    ck24("普通怪[$mid]部件合法", isset(equip_slots()[$d['slot']]), $d['slot']);
    ck24("普通怪[$mid]不带传说字段", !isset($d['legendary']), $mid);
}
foreach ($mainSlots as $s) {
    ck24("主要部件[$s]有普通怪掉落", isset($slots[$s]), json_encode(array_keys($slots)));
}

// 2) 实测普通怪掉落：品质封顶史诗(3)、等级=怪物等级、能产出
$uid = user_create('q24' . mt_rand(1000, 9999), 'x', 'warrior', 'z1', true, 'tmp');
db()->exec('DELETE FROM equips WHERE uid=' . (int) $uid); // 清掉出生装备，确保只统计本次掉落
$seenQ = []; $maxQ = -1; $anyDrop = false;
foreach ($normals as $mid => $d) {
    $mlv = monster_lv($mid);
    $got = 0;
    for ($i = 0; $i < 160; $i++) {
        $res = roll_drop((int) $uid, $mid, 0);
        // 取最新插入的装备（若有）
        $row = db()->query('SELECT quality, item_level FROM equips WHERE uid=' . (int) $uid . ' ORDER BY id DESC LIMIT 1')->fetch();
        db()->exec('DELETE FROM equips WHERE uid=' . (int) $uid); // 防止背包满导致落到地面
        if (!$row) { continue; }
        $got++;
        $q = (int) $row['quality'];
        $seenQ[$q] = ($seenQ[$q] ?? 0) + 1;
        if ($q > $maxQ) { $maxQ = $q; }
        ck24("普通怪[$mid]品质<=3", $q <= 3, "q=$q");
        ck24("普通怪[$mid]装备等级=怪物等级", (int) $row['item_level'] === $mlv, "iLv={$row['item_level']} mlv=$mlv");
    }
    ck24("普通怪[$mid]确实会掉装备", $got > 0, "got=$got");
    if ($got > 0) { $anyDrop = true; }
}
ck24('普通怪最高品质不超过史诗', $maxQ <= 3, "maxQ=$maxQ");
ck24('普通怪能掉落(有产出)', $anyDrop, '');
ck24('掉落品质覆盖低劣(0)', isset($seenQ[0]), json_encode($seenQ));

// 3) BOSS 仍然只可能出传说(4)（对照，确保没被误封顶）
$row = null;
$bossLeg = false;
for ($i = 0; $i < 1500; $i++) {
    $r = roll_drop((int) $uid, 'sky_sovereign', 0);
    $row = db()->query('SELECT quality FROM equips WHERE uid=' . (int) $uid . ' ORDER BY id DESC LIMIT 1')->fetch();
    db()->exec('DELETE FROM equips WHERE uid=' . (int) $uid);
    if ($row && (int) $row['quality'] === 4) { $bossLeg = true; break; }
}
ck24('BOSS仍能出传说(4)', $bossLeg, '');

// 清理
db()->exec('DELETE FROM users WHERE id=' . (int) $uid);
db()->exec('DELETE FROM equips WHERE uid=' . (int) $uid);
db()->exec('DELETE FROM mats WHERE uid=' . (int) $uid);

echo "\nOK=$ok FAIL=$bad\n";
