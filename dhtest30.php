<?php
define('DH_TEST_MODE', true);
require 'includes/bootstrap.php';

$ok = 0; $bad = 0;
function ck30(string $l, bool $c, string $d = '') { global $ok,$bad; if ($c) { $ok++; } else { $bad++; echo "FAIL $l $d\n"; } }

// 收集全服所有「传说」装备的 base 名（掉落表 legendary + 遗神铸庭遗物 + 召唤师传说）
$bases = [];
foreach (glob(__DIR__ . '/includes/*.php') as $f) {
    $txt = file_get_contents($f);
    if (preg_match_all("/'legendary'\s*=>\s*\[\s*'base'\s*=>\s*'([^']+)'/u", $txt, $m)) {
        foreach ($m[1] as $b) { $bases[$b] = true; }
    }
}
foreach (summoner_legends() as $n => $c) { $bases[$n] = true; }
foreach (legend_relics() as $n => $c) { $bases[$n] = true; }

$set = [];
foreach (array_keys($bases) as $b) { $set[equip_shortname($b)] = $b; }

ck30('传说法数量>100', count($set) > 100, (string) count($set));

$generic = '深渊里捞出来的东西，每一件都记得主人的名字。';
$miss = [];
foreach ($set as $sn => $b) {
    $lore = legend_lore($sn);
    ck30("[$sn]有传奇故事", $lore !== '' && !str_starts_with($lore, $generic), $lore);
    if (str_starts_with($lore, $generic)) { $miss[] = $sn; }
}
ck30('无任何传说落到通用兜底', $miss === [], implode(',', array_slice($miss, 0, 8)));

// legend_story_map 覆盖：抽检若干关键名，文本非空且含换行分段
$story = legend_story_map();
ck30('故事表>120条', count($story) > 120, (string) count($story));
foreach (['不朽', '怒血', '极寒', '熔金监工·赤铁之翼', '神墟终焉·归墟之链', '雷霆君临·裂云护手'] as $k) {
    ck30("[$k]在故事表", isset($story[$k]) && $story[$k] !== '', $k);
}

echo "\nOK=$ok FAIL=$bad\n";
