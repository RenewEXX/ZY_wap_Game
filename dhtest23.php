<?php
define('DH_TEST_MODE', true);
require 'includes/bootstrap.php';

$ok = 0; $bad = 0;
function ckW(string $l, bool $cond, string $d = '') { global $ok,$bad; if ($cond) { $ok++; } else { $bad++; echo "FAIL $l $d\n"; } }

$all = locations();
$ms = monsters();

// —— 1. 新区域地图存在且连通 ——
$skyMaps = ['sky_ferry','sky_cloud_path','sky_thunder_nest','sky_storm_core','sky_wind_temple','sky_gale_shrine','sky_void_stair','sky_throne'];
$sunkMaps = ['sunk_shore','sunk_gallery','sunk_altar','sunk_queen','sunk_trench','sunk_lair'];
foreach (array_merge($skyMaps, $sunkMaps) as $mid) {
    ckW("地图[$mid]存在", isset($all[$mid]), '');
}
// 连通：从王都能走到天空王座，从白银能走到巨鲸巢
ckW('王都→天空王座可达', find_route('avenue', 'sky_throne') !== [], json_encode(find_route('avenue','sky_throne')));
ckW('白银→巨鲸巢可达', find_route('silver_sq', 'sunk_lair') !== [], '');
// 网关双向
ckW('王都有出口到浮空渡口', isset($all['avenue']['exits']['sky_ferry']), '');
ckW('浮空渡口能回王都', isset($all['sky_ferry']['exits']['avenue']), '');

// —— 2. 新怪存在、等级/属性/BOSS正确 ——
foreach (world_expansion_monsters() as $mid => $def) {
    ckW("怪物[$mid]已注册", isset($ms[$mid]), '');
    ckW("怪物[$mid]有等级", monster_lv($mid) === (world_expansion_lv()[$mid] ?? monster_lv($mid)), (string) monster_lv($mid));
    ckW("怪物[$mid]有元素", isset(world_expansion_element()[$mid]) && monster_element($mid) === world_expansion_element()[$mid], monster_element($mid));
    ckW("怪物[$mid]base=final(identity)", (int) (scale_monster(['hp'=>$def['hp'],'atk'=>$def['atk'],'maxhp'=>$def['hp']], $mid, 0)['hp']) === (int) $def['hp'], '');
}
foreach (world_expansion_bosses() as $b) {
    ckW("BOSS[$b]被is_boss_mid识别", is_boss_mid($b), '');
    $dp = monster_drops($b);
    ckW("BOSS[$b]有传说掉落", isset($dp['legendary']['base']), json_encode($dp));
}

// —— 3. BOSS地图 boss_of_map 正确 ——
foreach (world_expansion_boss_map() as $loc => $boss) {
    ckW("地图[$loc]的BOSS是$boss", boss_of_map($loc) === $boss, boss_of_map($loc));
    ckW("地图[$loc]只有BOSS一种怪", ($all[$loc]['monsters'] ?? []) === [$boss], json_encode($all[$loc]['monsters'] ?? []));
}

// —— 4. 传说只在 BOSS 表里，普通怪没有 ——
foreach (world_expansion_monsters() as $mid => $def) {
    $dp = monster_drops($mid);
    if (is_boss_mid($mid)) {
        ckW("BOSS[$mid]传说存在", isset($dp['legendary']), '');
    } else {
        ckW("普通怪[$mid]无传说", !isset($dp['legendary']), json_encode($dp));
    }
}

// —— 5. 传送：新区域进 map_regions 且 min_lv 合理 ——
$reg = map_regions();
ckW('天空岛进传送分组', isset($reg['天空岛·浮空列岛']), '');
ckW('沉没圣所进传送分组', isset($reg['沉没圣所·海渊']), '');
ckW('天空岛入口最低等级≈115', abs(teleport_min_lv('sky_ferry') - 115) <= 2, (string) teleport_min_lv('sky_ferry'));

// —— 6. BOSS专属传说能真的通过 make_equip 生成 ——
$uid = user_create('wntest' . mt_rand(1000, 9999), 'x', 'warrior', 'z1', true, 'tmp');
foreach (world_expansion_bosses() as $b) {
    $dp = monster_drops($b);
    if (!isset($dp['legendary'])) { continue; }
    $name = make_equip((int) $uid, $dp['slot'], $dp['legendary']['base'], 4, monster_lv($b), $dp['legendary']);
    ckW("BOSS[$b]传说能生成($name)", $name !== '', $name);
    // 库里查到质量4
    $q = db()->query('SELECT quality FROM equips WHERE uid=' . (int)$uid . ' ORDER BY id DESC LIMIT 1')->fetchColumn();
    ckW("BOSS[$b]传说质量为4", (int)$q === 4, (string)$q);
}

// 清理
db()->exec('DELETE FROM users WHERE id=' . (int) $uid);
db()->exec('DELETE FROM equips WHERE uid=' . (int) $uid);
db()->exec('DELETE FROM mats WHERE uid=' . (int) $uid);

// —— 7. 等级覆盖：新怪确实落在 90~195 区间 ——
$lvs = array_values(world_expansion_lv());
ckW('新怪覆盖90~195', min($lvs) >= 90 && max($lvs) <= 195, json_encode([min($lvs), max($lvs)]));

echo "\nOK=$ok FAIL=$bad\n";
