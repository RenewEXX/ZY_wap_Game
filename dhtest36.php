<?php
define('DH_TEST_MODE', true);
require 'includes/bootstrap.php';
// 传说装备“独特小故事”全覆盖审计：
// 所有来源的传说，legend_lore() 都不能落到通用兜底文案。
$ok = 0; $bad = 0;
function ck36(string $l, bool $c, string $d = '') { global $ok,$bad; if ($c) { $ok++; } else { $bad++; echo "FAIL $l $d\n"; } }
$DEFAULT = '深渊里捞出来的东西，每一件都记得主人的名字。<br>它现在记得你的了。';
$hasStory = fn(string $n): bool => trim(legend_lore($n)) !== $DEFAULT;

// 收集所有传说短名（含来源分类，便于报错定位）
$groups = [];

// 1) BOSS 专属掉落传说
$boss = [];
foreach (monsters() as $mid => $_) {
    $d = monster_drops((string) $mid);
    if (is_array($d) && isset($d['legendary']['base'])) { $boss[] = (string) $d['legendary']['base']; }
}
foreach (world_expansion_drops() as $d) { if (isset($d['legendary']['base'])) { $boss[] = (string) $d['legendary']['base']; } }
foreach (we2_drops() as $d) { if (isset($d['legendary']['base'])) { $boss[] = (string) $d['legendary']['base']; } }
$groups['BOSS掉落传说'] = array_values(array_unique($boss));

// 2) 遗神铸庭 relic
$groups['遗神铸庭传说'] = array_keys(legend_relics());

// 3) 召唤师死灵传说
$groups['召唤师死灵传说'] = array_keys(summoner_legends());

// 4) 龙·六圣柱套装
$dragon = [];
foreach (dragons() as $dd) {
    $set = (string) ($dd['set'] ?? '');
    if ($set === '') { continue; }
    foreach (dragon_set_pieces($set) as $pc) { $dragon[] = $pc; }
}
$groups['龙·圣柱套装'] = array_values(array_unique($dragon));

// 5) 命名的特殊传说
$groups['命名特殊传说'] = ['君王蚀影刃', '堕落骑士大剑', '食人魔战裙', '雷恩回响戒'];

$total = 0; $miss = [];
foreach ($groups as $g => $names) {
    $gmiss = [];
    foreach ($names as $n) {
        $total++;
        if (!$hasStory((string) $n)) { $gmiss[] = $n; }
    }
    ck36("[$g] 全部有独特故事 (".count($names)."件)", $gmiss === [], implode(',', $gmiss));
    foreach ($gmiss as $m) { $miss[] = "$g:$m"; }
}

echo "共审计传说 $total 件，缺独特故事 ".count($miss)." 件\n";
foreach ($miss as $m) echo "   - $m\n";
ck36('全局：所有传说都有独特故事', $miss === []);
// 顺手确认每件至少能取到非空文案
$empty = 0;
foreach ($groups as $names) { foreach ($names as $n) { if (trim(legend_lore((string)$n)) === '') { $empty++; } } }
ck36('没有空故事', $empty === 0, (string) $empty);

echo "\nOK=$ok FAIL=$bad\n";
