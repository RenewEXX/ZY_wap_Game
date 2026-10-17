<?php
$p = 'includes/game.php';
$t = file_get_contents($p);
function rep(&$t, $a, $b, $label) {
    if (strpos($t, $a) === false) { echo "MISS $label\n"; return; }
    $n = 0; $t = str_replace($a, $b, $t, $n);
    echo "$label=$n\n";
}

// 逃生出口
rep($t, "'exits' => ['sy_pit' => '寂灭之底'], 'monsters' => ['sy_sin']]", "'exits' => ['sy_pit' => '寂灭之底', 'god_hall' => '圣都（退出）'], 'monsters' => ['sy_sin']]", 'exit');

// ── 套装：寂灭(ann) / 噬渊(dev) ──
// set_id
$a = "    if (str_starts_with(\$shortName, '暮影·')) {\n        return 'dusk';\n    }";
rep($t, $a, $a . "\n    if (str_starts_with(\$shortName, '寂灭·')) {\n        return 'ann';\n    }\n    if (str_starts_with(\$shortName, '噬渊·')) {\n        return 'dev';\n    }", 'setid');

// pieces
$a = "    if (\$set === 'dusk') {\n        return ['暮影·暮刃', '暮影·暮誓手套', '暮影·暮链'];\n    }";
rep($t, $a, $a . "\n    if (\$set === 'ann') {\n        return ['寂灭·终焉之刃', '寂灭·终焉之斧', '寂灭·终焉之弓', '寂灭·终焉之锤', '寂灭·终焉之杖', '寂灭·终焉之盾', '寂灭·无光冠', '寂灭·空寂甲', '寂灭·灭音链', '寂灭·断律手套', '寂灭·静默戒'];\n    }\n    if (\$set === 'dev') {\n        return ['噬渊·吞星刃', '噬渊·裂渊斧', '噬渊·噬渊弓', '噬渊·万骸锤', '噬渊·渊喉杖', '噬渊·无底盾', '噬渊·无底冠', '噬渊·渊壳甲', '噬渊·万骸戒', '噬渊·啖罪手套', '噬渊·沉渊链'];\n    }", 'pieces');

// owned LIKE
rep($t, "'sin' => 11, 'god' => 11, 'dusk' => 3][\$set];", "'sin' => 11, 'god' => 11, 'dusk' => 3, 'ann' => 11, 'dev' => 11][\$set];", 'cap');
rep($t, "'rockjail', 'sin', 'god', 'dusk'], true)", "'rockjail', 'sin', 'god', 'dusk', 'ann', 'dev'], true)", 'ownedlist');
rep($t, "'god' => '%神铸·%', 'dusk' => '%暮影·%'][\$set];", "'god' => '%神铸·%', 'dusk' => '%暮影·%', 'ann' => '%寂灭·%', 'dev' => '%噬渊·%'][\$set];", 'mark');

// bonus_text
$a = "    if (\$set === 'dusk') {";
rep($t, $a, "    if (\$set === 'ann') {\n        return '【寂灭套装】已穿' . \$owned . '/11——2件：暴击+10%、技伤+15%；3件：全属性+等级×8；5件：暴伤+50%、真伤+等级×30、减伤+15%！渊底不产饶恕，只产终结。';\n    }\n    if (\$set === 'dev') {\n        return '【噬渊套装】已穿' . \$owned . '/11——2件：吸血+12%、生命+等级×60；3件：全属性+等级×8、减伤+12%；5件：真伤+等级×35、攻+20%、吸血再+8%！渊不需要呼吸，它只需要你。';\n    }\n" . $a, 'btxt');

// gear_stats
$a = "    \$dusk = 0;";
rep($t, $a, "    \$ann = 0; \$dev = 0;\n    \$ulvR = (int) (db()->query('SELECT lv FROM users WHERE id=' . (int) \$uid)->fetchColumn() ?: 1);\n    foreach (\$worn as \$wn) {\n        if (str_starts_with(\$wn, '寂灭·')) { \$ann++; } elseif (str_starts_with(\$wn, '噬渊·')) { \$dev++; }\n    }\n    if (\$ann >= 2) { \$s['crit'] += 10; \$s['skill_damage'] += 15; }\n    if (\$ann >= 3) { \$s['all_attr'] += \$ulvR * 8; }\n    if (\$ann >= 5) { \$s['critdmg'] += 50; \$s['truedmg'] += \$ulvR * 30; \$s['damage_reduction'] += 15; }\n    if (\$dev >= 2) { \$s['lifesteal'] += 12; \$s['hp'] += \$ulvR * 60; }\n    if (\$dev >= 3) { \$s['all_attr'] += \$ulvR * 8; \$s['damage_reduction'] += 12; }\n    if (\$dev >= 5) { \$s['truedmg'] += \$ulvR * 35; \$s['atk_pct'] += 20; \$s['lifesteal'] += 8; }\n" . $a, 'gear');

file_put_contents($p, $t);
echo "DONE\n";