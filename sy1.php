<?php
$p = 'includes/game.php';
$t = file_get_contents($p);
function rep(&$t, $a, $b, $label) {
    if (strpos($t, $a) === false) { echo "MISS $label\n"; return; }
    $n = 0; $t = str_replace($a, $b, $t, $n);
    echo "$label=$n\n";
}

// 1) 怪物
$a = "        'gf_seraphim' => ['name' => '堕天炽天使', 'hp' => 4200000, 'atk' => 8500, 'exp' => 7000000, 'gold' => 3400000],";
if (strpos($t, "'sy_sin' => [") === false) {
    rep($t, $a, $a . "\n        'sy_sin' => ['name' => '渊中残罪', 'hp' => 2600000, 'atk' => 7200, 'exp' => 4800000, 'gold' => 2300000],\n        'sy_echo' => ['name' => '渊回之影', 'hp' => 3200000, 'atk' => 8000, 'exp' => 5800000, 'gold' => 2800000],\n        'sy_boss' => ['name' => '源罪·寂灭君主', 'hp' => 90000000, 'atk' => 12000, 'exp' => 40000000, 'gold' => 20000000],", 'mon');
} else { echo "mon=have\n"; }

// 2) 等级
$a = "'gf_fallen' => 830,";
if (strpos($t, "'sy_sin' => 760") === false) {
    rep($t, $a, $a . "\n        'sy_sin' => 760, 'sy_echo' => 820, 'sy_boss' => 999,", 'lv');
} else { echo "lv=have\n"; }

// 3) BOSS绑定
if (strpos($t, "'sy_pit' => 'sy_boss'") === false) {
    rep($t, "'gf_core' => 'god_emperor'];", "'gf_core' => 'god_emperor', 'sy_pit' => 'sy_boss'];", 'boss');
} else { echo "boss=have\n"; }

// 4) 地图函数
if (!function_exists('x')) {
    $a = "function sy_locations(): array";
    if (strpos($t, $a) === false) {
        rep($t, "    return ['gf_gate', 'gf_stair', 'gf_void', 'gf_wing', 'gf_core'];", "    return ['gf_gate', 'gf_stair', 'gf_void', 'gf_wing', 'gf_core'];\n}\n\n// 源罪渊·月度活动副本（700级+，每月一次，单BOSS）\nfunction sy_locations(): array\n{\n    \$m = [];\n    \$m['sy_gate'] = ['name' => '源罪渊·渊口', 'desc' => '一道往下的裂缝，缝里没有光，只有一句话在反复念：把名字留下，把等级留下。', 'exits' => ['sy_pit' => '寂灭之底'], 'monsters' => ['sy_sin']];\n    \$m['sy_pit'] = ['name' => '源罪渊·寂灭之底', 'desc' => '渊底什么都不剩，连回声都被吃干净了。君主坐在那里，等一个还带着名字的人。', 'exits' => ['sy_gate' => '渊口'], 'monsters' => ['sy_echo', 'sy_boss']];\n    return \$m;\n}\n\nfunction sy_maps(): array\n{\n    return ['sy_gate', 'sy_pit'];", 'maps');
    } else { echo "maps=have\n"; }
}

// 5) dungeon_maps
if (strpos($t, "gf_maps(), sy_maps()") === false) {
    rep($t, "ry_maps(), vx_maps(), gy_maps(), gf_maps());", "ry_maps(), vx_maps(), gy_maps(), gf_maps(), sy_maps());", 'dungeon');
} else { echo "dungeon=have\n"; }

file_put_contents($p, $t);
echo "DONE\n";