<?php
declare(strict_types=1);

// ============================================================
// 世界扩展：天空岛 + 沉没圣所
// 目的：填补 90~200 级中段地图/怪物荒漠，并新增两个大型主题区域、
//       若干只只掉传说装备的BOSS（传说装备只在BOSS掉落表里出现）。
// 约定：
//   - 这些区域是"野外"区域（不进 dungeon_maps，不吃30/45分钟清场），
//     通过传送 / 走过 gateway 进入。
//   - 怪物走 identity 缩放（base 即为最终数值），像三章傀儡/母巢那样直接写数值。
//   - 与主世界共用 spawn / 掉落 / 经验 / 战报系统。
// ============================================================

function sky_locations(): array
{
    $m = [];
    $m['sky_ferry'] = ['name' => '天空岛·浮空渡口', 'desc' => '一块倒悬的岩石停在半空，锚链锈成了青绿。往上，云在脚下流。', 'exits' => ['sky_cloud_path' => '云海栈道'], 'monsters' => ['cloud_hawk', 'thunder_sprite']];
    $m['sky_cloud_path'] = ['name' => '云海栈道', 'desc' => '木板悬在万仞之上，一步一响。鹰妖在云缝里盘点过路的人。', 'exits' => ['sky_ferry' => '浮空渡口', 'sky_thunder_nest' => '雷暴云巢', 'sky_wind_temple' => '风蚀神殿'], 'monsters' => ['sky_sentinel', 'cloud_hawk']];
    $m['sky_thunder_nest'] = ['name' => '雷暴云巢', 'desc' => '黑云翻涌，闪电在巢里筑窝。唤雷祭司把雷声编成经。', 'exits' => ['sky_cloud_path' => '云海栈道', 'sky_storm_core' => '雷暴之核'], 'monsters' => ['storm_caller', 'thunder_sprite']];
    $m['sky_storm_core'] = ['name' => '雷暴之核', 'desc' => '雷的心脏悬在半空，一跳一闪。雷霆君临就坐在它下面。', 'exits' => ['sky_thunder_nest' => '雷暴云巢'], 'monsters' => ['thunder_regent']];
    $m['sky_wind_temple'] = ['name' => '风蚀神殿', 'desc' => '风把石柱啃成了骨头。石像会动，动起来比风还快。', 'exits' => ['sky_cloud_path' => '云海栈道', 'sky_gale_shrine' => '疾风神龛', 'sky_void_stair' => '虚空回廊'], 'monsters' => ['wind_golem', 'sky_priest']];
    $m['sky_gale_shrine'] = ['name' => '疾风神龛', 'desc' => '没有墙，只有风围成的一圈。风之母站在正中，衣角从不落下。', 'exits' => ['sky_wind_temple' => '风蚀神殿'], 'monsters' => ['wind_matriarch']];
    $m['sky_void_stair'] = ['name' => '虚空回廊', 'desc' => '台阶一级级通向没有地面的地方。游魂贴着台阶背面往上爬。', 'exits' => ['sky_wind_temple' => '风蚀神殿', 'sky_throne' => '苍穹王座'], 'monsters' => ['void_wisp', 'sky_sentinel']];
    $m['sky_throne'] = ['name' => '苍穹王座', 'desc' => '云的王座悬在最顶。苍穹之主·瓦洛俯视众生——包括你。', 'exits' => ['sky_void_stair' => '虚空回廊'], 'monsters' => ['sky_sovereign']];
    return $m;
}

function sunken_locations(): array
{
    $m = [];
    $m['sunk_shore'] = ['name' => '沉没圣所·海岸', 'desc' => '海水退成一条黑线，露出半沉的石阶。潮声里混着人的低语。', 'exits' => ['sunk_gallery' => '溺亡回廊'], 'monsters' => ['tide_hunter', 'drowned_one']];
    $m['sunk_gallery'] = ['name' => '溺亡回廊', 'desc' => '廊柱一半泡在水里，倒影里的人比本体多。', 'exits' => ['sunk_shore' => '沉没海岸', 'sunk_altar' => '珊瑚祭坛'], 'monsters' => ['drowned_one', 'coral_guard']];
    $m['sunk_altar'] = ['name' => '珊瑚祭坛', 'desc' => '珊瑚长成的祭坛，供台上摆着一排没有脸的雕像。歌声从雕像里出来。', 'exits' => ['sunk_gallery' => '溺亡回廊', 'sunk_queen' => '海妖王座', 'sunk_trench' => '深海沟壑'], 'monsters' => ['coral_guard', 'siren_singer']];
    $m['sunk_queen'] = ['name' => '海妖王座', 'desc' => '王座是鲸骨搭的。海妖女王坐在上面，用溺死者的名字唱歌。', 'exits' => ['sunk_altar' => '珊瑚祭坛'], 'monsters' => ['siren_queen']];
    $m['sunk_trench'] = ['name' => '深海沟壑', 'desc' => '光到不了的地方。巨蟹在沟底横行，壳上长着别的壳。', 'exits' => ['sunk_altar' => '珊瑚祭坛', 'sunk_lair' => '巨鲸巢'], 'monsters' => ['deep_crab', 'tide_hunter']];
    $m['sunk_lair'] = ['name' => '巨鲸巢', 'desc' => '一具比城堡还大的鲸骨盘在海底。骨缝里，深渊鲸主还在呼吸。', 'exits' => ['sunk_trench' => '深海沟壑'], 'monsters' => ['abyss_whale']];
    return $m;
}

// 所有新增区域地图（供 locations() 合并）
function world_expansion_locations(): array
{
    return sky_locations() + sunken_locations() + we2_locations();
}

// 新区域在地图/传送里的归组
function world_expansion_regions(): array
{
    return [
        '天空岛·浮空列岛' => ['sky_ferry', 'sky_cloud_path', 'sky_thunder_nest', 'sky_storm_core', 'sky_wind_temple', 'sky_gale_shrine', 'sky_void_stair', 'sky_throne'],
        '沉没圣所·海渊' => ['sunk_shore', 'sunk_gallery', 'sunk_altar', 'sunk_queen', 'sunk_trench', 'sunk_lair'],
    ] + we2_regions();
}

// 把新区域接进主世界地图图（双向连线，保证 find_route / 走路 / 传送都能到）
function world_link_regions(array $all): array
{
    // 王都中央大道 ↔ 天空岛·浮空渡口
    if (isset($all['avenue'], $all['sky_ferry'])) {
        $all['avenue']['exits']['sky_ferry'] = '天空岛·浮空渡口';
        $all['sky_ferry']['exits']['avenue'] = '王都中央大道';
    }
    // 白银广场 ↔ 沉没圣所·海岸
    if (isset($all['silver_sq'], $all['sunk_shore'])) {
        $all['silver_sq']['exits']['sunk_shore'] = '沉没圣所·海岸';
        $all['sunk_shore']['exits']['silver_sq'] = '白银广场';
    }
    // 第二弹 7 区域：各自 gateway ↔ hub 双向连线
    foreach (we2_links() as $gw => $hub) {
        if (isset($all[$gw], $all[$hub])) {
            $all[$hub]['exits'][$gw] = (string) ($all[$gw]['name'] ?? $gw);
            $all[$gw]['exits'][$hub] = (string) ($all[$hub]['name'] ?? $hub);
        }
    }
    // 六圣柱：与主世界双向连通（可徒步进入，但传送页屏蔽）；挂在出口较少的枢纽上
    $dragonGates = [
        ['capital_gate', 'sky_ramp'],
        ['ice_village', 'frost_deep'],
        ['fo_gate', 'storm_wall'],
        ['altar', 'abyss_deep'],
    ];
    foreach ($dragonGates as [$hub, $gate]) {
        if (isset($all[$hub], $all[$gate])) {
            $all[$hub]['exits'][$gate] = (string) ($all[$gate]['name'] ?? $gate);
            $all[$gate]['exits'][$hub] = (string) ($all[$hub]['name'] ?? $hub);
        }
    }
    // 六圣柱内部：原本多条单向路，补成双向，保证从任意入口能走到深处
    $dm = dragon_maps();
    foreach ($dm as $a) {
        if (!isset($all[$a])) {
            continue;
        }
        foreach (array_keys($all[$a]['exits']) as $b) {
            if (in_array($b, $dm, true) && isset($all[$b])) {
                $all[$b]['exits'][$a] = (string) ($all[$a]['name'] ?? $a);
            }
        }
    }
    // 大区域副本：每个大区从指定野外点引一扇"副本入口"双向门
    if (function_exists('region_dungeon_specs') && function_exists('region_dungeon_locations')) {
        $rdLoc = region_dungeon_locations();
        foreach (region_dungeon_specs() as $rk => $R) {
            $gate = 'rd_' . $rk . '_gate';
            $from = (string) $R['gate'];
            if (isset($all[$gate], $all[$from])) {
                $all[$from]['exits'][$gate] = (string) ($rdLoc[$gate]['name'] ?? $gate);
                $all[$gate]['exits'][$from] = (string) ($all[$from]['name'] ?? $from);
            }
        }
    }
    return $all;
}

// 新怪物（base 即最终数值，走 identity 缩放）
function world_expansion_monsters(): array
{
    return [
        // —— 天空岛（120~195）——
        'cloud_hawk' => ['name' => '云海鹰妖', 'hp' => 14000, 'atk' => 300, 'exp' => 30000, 'gold' => 7000],
        'thunder_sprite' => ['name' => '雷云精', 'hp' => 12000, 'atk' => 320, 'exp' => 30000, 'gold' => 7000],
        'sky_sentinel' => ['name' => '浮岛哨兵', 'hp' => 20000, 'atk' => 340, 'exp' => 42000, 'gold' => 8500],
        'storm_caller' => ['name' => '唤雷祭司', 'hp' => 24000, 'atk' => 370, 'exp' => 52000, 'gold' => 9500],
        'wind_golem' => ['name' => '风蚀石像', 'hp' => 32000, 'atk' => 410, 'exp' => 62000, 'gold' => 10500],
        'sky_priest' => ['name' => '苍穹祭司', 'hp' => 30000, 'atk' => 400, 'exp' => 62000, 'gold' => 10500],
        'void_wisp' => ['name' => '虚空游魂', 'hp' => 40000, 'atk' => 450, 'exp' => 72000, 'gold' => 12000],
        'thunder_regent' => ['name' => '雷霆君临', 'hp' => 95000, 'atk' => 720, 'exp' => 200000, 'gold' => 40000],
        'wind_matriarch' => ['name' => '风之母', 'hp' => 130000, 'atk' => 820, 'exp' => 260000, 'gold' => 50000],
        'sky_sovereign' => ['name' => '苍穹之主·瓦洛', 'hp' => 190000, 'atk' => 950, 'exp' => 400000, 'gold' => 80000],
        // —— 沉没圣所（90~160）——
        'tide_hunter' => ['name' => '潮汐猎手', 'hp' => 9000, 'atk' => 230, 'exp' => 18000, 'gold' => 5000],
        'drowned_one' => ['name' => '溺亡者', 'hp' => 11000, 'atk' => 210, 'exp' => 18000, 'gold' => 5000],
        'coral_guard' => ['name' => '珊瑚守卫', 'hp' => 15000, 'atk' => 260, 'exp' => 26000, 'gold' => 6200],
        'siren_singer' => ['name' => '海妖歌者', 'hp' => 16000, 'atk' => 300, 'exp' => 30000, 'gold' => 7000],
        'deep_crab' => ['name' => '深海巨蟹', 'hp' => 25000, 'atk' => 340, 'exp' => 40000, 'gold' => 8500],
        'siren_queen' => ['name' => '海妖女王', 'hp' => 55000, 'atk' => 520, 'exp' => 110000, 'gold' => 24000],
        'abyss_whale' => ['name' => '深渊鲸主', 'hp' => 112000, 'atk' => 730, 'exp' => 230000, 'gold' => 46000],
    ] + we2_monsters();
}

// 怪物等级（身份缩放，直接对应 band）
function world_expansion_lv(): array
{
    return [
        'cloud_hawk' => 120, 'thunder_sprite' => 120, 'sky_sentinel' => 135,
        'storm_caller' => 150, 'wind_golem' => 165, 'sky_priest' => 165, 'void_wisp' => 180,
        'thunder_regent' => 155, 'wind_matriarch' => 170, 'sky_sovereign' => 195,
        'tide_hunter' => 90, 'drowned_one' => 90, 'coral_guard' => 105,
        'siren_singer' => 120, 'deep_crab' => 140, 'siren_queen' => 125, 'abyss_whale' => 160,
    ] + we2_lv();
}

function world_expansion_element(): array
{
    return [
        'cloud_hawk' => 'wind', 'thunder_sprite' => 'thunder', 'sky_sentinel' => 'wind',
        'storm_caller' => 'thunder', 'wind_golem' => 'wind', 'sky_priest' => 'light', 'void_wisp' => 'dark',
        'thunder_regent' => 'thunder', 'wind_matriarch' => 'wind', 'sky_sovereign' => 'light',
        'tide_hunter' => 'ice', 'drowned_one' => 'dark', 'coral_guard' => 'ice',
        'siren_singer' => 'dark', 'deep_crab' => 'ice', 'siren_queen' => 'dark', 'abyss_whale' => 'ice',
    ] + we2_element();
}

// 哪些地图是 BOSS 地图（含这只BOSS），供 boss_of_map / spawn 上限 / 传送用
function world_expansion_boss_map(): array
{
    return [
        'sky_storm_core' => 'thunder_regent',
        'sky_gale_shrine' => 'wind_matriarch',
        'sky_throne' => 'sky_sovereign',
        'sunk_queen' => 'siren_queen',
        'sunk_lair' => 'abyss_whale',
    ] + we2_boss_map();
}

function world_expansion_bosses(): array
{
    return array_merge(['thunder_regent', 'wind_matriarch', 'sky_sovereign', 'siren_queen', 'abyss_whale'], we2_bosses());
}

// identity 缩放：这些怪的 base 就是最终数值（同三章傀儡/母巢）
function world_identity_mid(string $mid): bool
{
    static $set = null;
    if ($set === null) {
        $set = array_fill_keys(array_keys(world_expansion_monsters()), true);
        foreach (array_keys(region_dungeon_monsters()) as $__rk) {
            $set[$__rk] = true;
        }
    }
    return isset($set[$mid]);
}

// BOSS 专属掉落（传说只在 BOSS 掉落表里出现）
function world_expansion_drops(): array
{
    return [
        // 普通怪：白/绿/蓝主装备
        'cloud_hawk' => ['slot' => 'back', 'base' => '鹰羽斗篷', 'rate' => 0.10, 'q' => [0, 20, 50, 30, 0]],
        'thunder_sprite' => ['slot' => 'ring', 'base' => '雷云之戒', 'rate' => 0.10, 'q' => [0, 20, 50, 30, 0]],
        'sky_sentinel' => ['slot' => 'head', 'base' => '哨兵面甲', 'rate' => 0.10, 'q' => [0, 20, 50, 30, 0]],
        'storm_caller' => ['slot' => 'offhand', 'base' => '唤雷法器', 'rate' => 0.10, 'q' => [0, 20, 50, 30, 0]],
        'wind_golem' => ['slot' => 'gloves', 'base' => '风蚀护手', 'rate' => 0.10, 'q' => [0, 20, 50, 30, 0]],
        'sky_priest' => ['slot' => 'necklace', 'base' => '苍穹圣链', 'rate' => 0.10, 'q' => [0, 20, 50, 30, 0]],
        'void_wisp' => ['slot' => 'shoes', 'base' => '虚空游步', 'rate' => 0.10, 'q' => [0, 20, 50, 30, 0]],
        'tide_hunter' => ['slot' => 'weapon', 'base' => '潮汐之矛', 'rate' => 0.10, 'q' => [0, 20, 50, 30, 0]],
        'drowned_one' => ['slot' => 'body', 'base' => '溺亡者之衣', 'rate' => 0.10, 'q' => [0, 20, 50, 30, 0]],
        'coral_guard' => ['slot' => 'belt', 'base' => '珊瑚束腰', 'rate' => 0.10, 'q' => [0, 20, 50, 30, 0]],
        'siren_singer' => ['slot' => 'legs', 'base' => '海妖歌裙', 'rate' => 0.10, 'q' => [0, 20, 50, 30, 0]],
        'deep_crab' => ['slot' => 'legs', 'base' => '深海蟹甲', 'rate' => 0.10, 'q' => [0, 20, 50, 30, 0]],
        // BOSS：只掉传说（q=4 → legendary）
        'thunder_regent' => ['slot' => 'gloves', 'base' => '雷核护手', 'rate' => 0.35, 'q' => [0, 0, 15, 45, 40], 'legendary' => ['base' => '雷霆君临·裂云护手', 'extra' => ['k' => 'lg_bl_thunder', 'v' => 1]]],
        'wind_matriarch' => ['slot' => 'shoes', 'base' => '疾风之靴', 'rate' => 0.35, 'q' => [0, 0, 15, 45, 40], 'legendary' => ['base' => '风之母·追云之靴', 'extra' => ['k' => 'lg_bl_wind', 'v' => 1]]],
        'sky_sovereign' => ['slot' => 'weapon', 'base' => '苍穹权杖', 'rate' => 0.40, 'q' => [0, 0, 10, 40, 50], 'legendary' => ['base' => '苍穹之主·天穹之刃', 'extra' => ['k' => 'lg_bl_sky', 'v' => 1]]],
        'siren_queen' => ['slot' => 'head', 'base' => '海妖冠冕', 'rate' => 0.35, 'q' => [0, 0, 15, 45, 40], 'legendary' => ['base' => '海妖女王·溺歌之冠', 'extra' => ['k' => 'lg_bl_siren', 'v' => 1]]],
        'abyss_whale' => ['slot' => 'ring', 'base' => '鲸骨指环', 'rate' => 0.40, 'q' => [0, 0, 10, 40, 50], 'legendary' => ['base' => '深渊鲸主·吞海之戒', 'extra' => ['k' => 'lg_bl_whale', 'v' => 1]]],
    ] + we2_drops();
}

// 怪物专属技能（并入 monster_skills()）：小怪各1招，BOSS 2~3招
function world_expansion_skills(): array
{
    return [
        // 天空岛小怪
        'cloud_hawk' => ['atk' => '俯冲', 'skills' => [['name' => '云海俯冲', 'rate' => 22, 'mult' => 1.6]]],
        'thunder_sprite' => ['atk' => '放电', 'skills' => [['name' => '静电麻痹', 'rate' => 22, 'mult' => 1.4, 'el' => 'thunder']]],
        'sky_sentinel' => ['atk' => '标枪', 'skills' => [['name' => '天空标枪', 'rate' => 24, 'mult' => 1.7]]],
        'storm_caller' => ['atk' => '引雷', 'skills' => [['name' => '落雷', 'rate' => 24, 'mult' => 1.8, 'el' => 'thunder']]],
        'wind_golem' => ['atk' => '挥拳', 'skills' => [['name' => '风刃横扫', 'rate' => 22, 'mult' => 1.7, 'el' => 'wind']]],
        'sky_priest' => ['atk' => '圣光', 'skills' => [['name' => '苍穹审判', 'rate' => 22, 'mult' => 1.7, 'el' => 'light']]],
        'void_wisp' => ['atk' => '噬咬', 'skills' => [['name' => '虚空侵蚀', 'rate' => 24, 'mult' => 1.5, 'dot' => [2, 3]]]],
        // 沉没圣所小怪
        'tide_hunter' => ['atk' => '刺击', 'skills' => [['name' => '潮汐突刺', 'rate' => 22, 'mult' => 1.6, 'el' => 'ice']]],
        'drowned_one' => ['atk' => '抓搡', 'skills' => [['name' => '溺水拖拽', 'rate' => 22, 'mult' => 1.5, 'dot' => [2, 2]]]],
        'coral_guard' => ['atk' => '盾击', 'skills' => [['name' => '珊瑚壁垒', 'rate' => 20, 'mult' => 0.6, 'guard' => 2]]],
        'siren_singer' => ['atk' => '咏唱', 'skills' => [['name' => '海妖悲歌', 'rate' => 24, 'mult' => 1.6, 'el' => 'dark']]],
        'deep_crab' => ['atk' => '巨钳', 'skills' => [['name' => '断骨钳击', 'rate' => 24, 'mult' => 1.8, 'el' => 'ice']]],
        // BOSS
        'thunder_regent' => ['atk' => '雷击', 'skills' => [
            ['name' => '万雷齐落', 'rate' => 30, 'mult' => 2.6, 'el' => 'thunder'],
            ['name' => '雷狱禁锢', 'rate' => 16, 'mult' => 1.6, 'dot' => [3, 3]],
            ['name' => '雷霆冲刺', 'rate' => 14, 'mult' => 1.8, 'rush' => true],
        ]],
        'wind_matriarch' => ['atk' => '风刃', 'skills' => [
            ['name' => '无形风刃', 'rate' => 30, 'mult' => 2.4, 'el' => 'wind'],
            ['name' => '风暴壁障', 'rate' => 14, 'mult' => 0.5, 'guard' => 3],
            ['name' => '追云突袭', 'rate' => 14, 'mult' => 1.7, 'rush' => true],
        ]],
        'sky_sovereign' => ['atk' => '天罚', 'skills' => [
            ['name' => '苍穹天罚', 'rate' => 30, 'mult' => 2.8, 'el' => 'light'],
            ['name' => '浮空放逐', 'rate' => 16, 'mult' => 1.8, 'dot' => [3, 4]],
            ['name' => '万象归穹', 'rate' => 12, 'mult' => 1.6, 'adds' => 1],
        ]],
        'siren_queen' => ['atk' => '魅音', 'skills' => [
            ['name' => '溺亡咏叹', 'rate' => 30, 'mult' => 2.4, 'el' => 'dark'],
            ['name' => '海妖之拥', 'rate' => 16, 'mult' => 1.6, 'drain' => 60],
            ['name' => '亡者合唱', 'rate' => 12, 'mult' => 1.4, 'adds' => 1],
        ]],
        'abyss_whale' => ['atk' => '鲸吞', 'skills' => [
            ['name' => '深渊鲸吞', 'rate' => 30, 'mult' => 2.8, 'el' => 'ice'],
            ['name' => '吞海冲击', 'rate' => 16, 'mult' => 1.8, 'dot' => [3, 3]],
            ['name' => '鲸骨壁垒', 'rate' => 12, 'mult' => 0.5, 'guard' => 3],
        ]],
    ] + we2_skills();
}

