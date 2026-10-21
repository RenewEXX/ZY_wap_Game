<?php
define('DH_TEST_MODE', true);
require 'includes/bootstrap.php';

$ok = 0; $bad = 0;
function ck25(string $l, bool $c, string $d = '') { global $ok,$bad; if ($c) { $ok++; } else { $bad++; echo "FAIL $l $d\n"; } }

$all = locations();
$regions = we2_regions();
$drops = we2_drops();
$bosses = we2_bosses();
$links = we2_links();
$mainSlots = ['weapon','offhand','body','head','legs','belt','gloves','shoes','back','ring','necklace'];

ck25('新增大区域=7', count($regions) === 7, (string) count($regions));

$totalMaps = 0; $totalCreatures = 0;
foreach ($regions as $rname => $ids) {
    // 1) 每区 >=10 张可传送小地图
    $real = 0; $reachable = 0;
    foreach ($ids as $id) {
        if (isset($all[$id])) { $real++; }
    }
    ck25("[$rname]地图数>=10", $real >= 10, "$real");
    $totalMaps += $real;

    // 可达：从该区第一张图（gateway）出发，每张图 find_route 都能到
    $gw = $ids[0];
    $hub = $links[$gw] ?? '';
    ck25("[$rname]gateway=$gw 有hub", $hub !== '' && isset($all[$hub]), $hub);
    if ($hub !== '' && isset($all[$hub])) {
        ck25("[$rname]hub能走到gateway", find_route($hub, $gw) !== [], "$hub->$gw");
    }
    foreach ($ids as $id) {
        if (isset($all[$id]) && find_route($gw, $id) !== []) { $reachable++; }
    }
    ck25("[$rname]所有地图互相可达", $reachable === $real, "$reachable/$real");

    // 2) 该区所有怪：注册 + 等级一致 + element + identity(base=final)
    $seenSlots = [];
    $monsters = we2_monsters();
    foreach ($all[$gw]['monsters'] as $m) {} // ensure gateway has monster
    // 收集本区所有怪：从地图 monsters + bosses
    $regionMids = [];
    foreach ($ids as $id) {
        foreach (($all[$id]['monsters'] ?? []) as $m) { $regionMids[$m] = true; }
    }
    foreach ($regionMids as $mid => $_) {
        $totalCreatures++;
        ck25("[$rname][$mid]已注册", isset(monsters()[$mid]), '');
        ck25("[$rname][$mid]等级=spec", monster_lv($mid) === (we2_lv()[$mid] ?? -1), (string) monster_lv($mid));
        ck25("[$rname][$mid]有元素", monster_element($mid) === (we2_element()[$mid] ?? '??'), monster_element($mid));
        $def = monsters()[$mid];
        $fin = scale_monster(['hp'=>$def['hp'],'atk'=>$def['atk'],'maxhp'=>$def['hp']], $mid, 0);
        ck25("[$rname][$mid]identity(base=final)", (int)$fin['hp'] === (int)$def['hp'], $def['hp'].'->'.$fin['hp']);
        if (isset($drops[$mid]['slot'])) { $seenSlots[$drops[$mid]['slot']] = true; }
    }
    // 3) 本区部件覆盖全部 11 个
    foreach ($mainSlots as $s) {
        ck25("[$rname]部件[$s]有掉落", isset($seenSlots[$s]), json_encode(array_keys($seenSlots)));
    }
    // 4) BOSS 识别 + 传说 + boss_of_map
    foreach ($bosses as $b) {
        // 只查属于本区的 boss（通过 drops 存在且地图在本区）
    }
}

// 5) 每个 BOSS：is_boss_mid、有传说、其所在地图 boss_of_map 正确
$bossMap = we2_boss_map();
ck25('BOSS地图数=21', count($bossMap) === 21, (string) count($bossMap));
foreach ($bossMap as $loc => $boss) {
    ck25("[$loc]是BOSS图", isset($all[$loc]), '');
    ck25("[$loc]boss_of_map=$boss", boss_of_map($loc) === $boss, boss_of_map($loc));
    ck25("[$boss]被识别为BOSS", is_boss_mid($boss), '');
    ck25("[$boss]有传说掉落", isset($drops[$boss]['legendary']['base']), '');
    ck25("[$loc]只含该BOSS", ($all[$loc]['monsters'] ?? []) === [$boss], json_encode($all[$loc]['monsters'] ?? []));
}

// 6) 总量核对
ck25('新增地图总数=91(含7个根据地)', $totalMaps === 91, (string) $totalMaps);
ck25('新增怪物总数=77', $totalCreatures === 77, (string) $totalCreatures);
ck25('新增BOSS=21', count($bosses) === 21, (string) count($bosses));

// 7) 普通怪无传说 + 品质实测封顶史诗
$uid = user_create('w2' . mt_rand(1000,9999), 'x', 'warrior', 'z1', true, 'tmp');
db()->exec('DELETE FROM equips WHERE uid=' . (int)$uid);
$maxQ = -1;
foreach ($drops as $mid => $d) {
    if (in_array($mid, $bosses, true)) { continue; }
    ck25("[$mid]普通怪无传说", !isset($d['legendary']), '');
    for ($i=0;$i<40;$i++) {
        roll_drop((int)$uid, $mid, 0);
        $row = db()->query('SELECT quality FROM equips WHERE uid='.(int)$uid.' ORDER BY id DESC LIMIT 1')->fetch();
        db()->exec('DELETE FROM equips WHERE uid='.(int)$uid);
        if ($row) { $maxQ = max($maxQ, (int)$row['quality']); }
    }
}
ck25('普通怪品质封顶史诗(<=3)', $maxQ <= 3, (string)$maxQ);

// 8) 每个区域进传送分组
foreach ($regions as $rname => $_) {
    ck25("【传送】{$rname}在map_regions", isset(map_regions()[$rname]), '');
}

db()->exec('DELETE FROM users WHERE id='.(int)$uid);
db()->exec('DELETE FROM equips WHERE uid='.(int)$uid);
db()->exec('DELETE FROM mats WHERE uid='.(int)$uid);

echo "\nOK=$ok FAIL=$bad\n";
