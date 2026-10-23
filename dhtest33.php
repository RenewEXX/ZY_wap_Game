<?php
define('DH_TEST_MODE', true);
require 'includes/bootstrap.php';
// 传送费用回归：严格按距离收费，越远越贵
$ok = 0; $bad = 0;
function ck33(string $l, bool $c, string $d = '') { global $ok,$bad; if ($c) { $ok++; } else { $bad++; echo "FAIL $l $d\n"; } }

$all = locations();
$ids = array_keys($all);
$from = 'square';

// 1) 原地传送 = 0
ck33('原地费用为0', teleport_cost($from, $from) === 0);

// 2) 费用与步数严格对应：cost = 20 * steps * (steps+1)
$checked = 0;
foreach ($ids as $to) {
    if ($to === $from || !isset($all[$to])) {
        continue;
    }
    $path = find_route($from, $to);
    if ($path === []) {
        continue;
    }
    $steps = count($path) - 1;
    $want = 20 * $steps * ($steps + 1);
    if ((int) teleport_cost($from, $to) !== (int) $want) {
        ck33("费用公式 $to", false, 'steps=' . $steps . ' got=' . teleport_cost($from, $to) . ' want=' . $want);
        $checked++;
        continue;
    }
    $checked++;
}
ck33('全部可达目标费用=20×步数×(步数+1)', $checked > 100, 'checked=' . $checked);

// 3) 越远越贵（单调）：把可达目标按步数排序，费用必须单调不减
$bySteps = [];
foreach ($ids as $to) {
    if ($to === $from) continue;
    $p = find_route($from, $to);
    if ($p === []) continue;
    $steps = count($p) - 1;
    $bySteps[$steps] = teleport_cost($from, $to);
}
ksort($bySteps);
$prevSteps = -1; $prevCost = -1; $mono = true;
foreach ($bySteps as $steps => $cost) {
    if ($cost < $prevCost) { $mono = false; }
    $prevSteps = $steps; $prevCost = $cost;
}
ck33('费用随距离单调递增', $mono);

// 4) 短途便宜、长途昂贵
reset($bySteps);
$minSteps = (int) key($bySteps);
$nearCost = (int) $bySteps[$minSteps];
ck33('短途为小额(<2银)', $nearCost < 200 && $minSteps >= 1, 'steps=' . $minSteps . ' cost=' . $nearCost);
$maxCost = 0; $maxTo = '';
foreach ($bySteps as $steps => $cost) { if ($cost > $maxCost) { $maxCost = $cost; $maxTo = $steps . '步'; } }
ck33('长途费用进入金币级(>1金)', $maxCost >= 10000, $maxTo . ' = ' . fmt_money($maxCost));

// 5) 不可达目标返回 null
$unreach = null;
foreach ($ids as $to) {
    if ($to === $from) continue;
    if (find_route($from, $to) === []) { $unreach = $to; break; }
}
if ($unreach !== null) {
    ck33('不可达返回null', teleport_cost($from, $unreach) === null, $unreach);
} else {
    ck33('不可达返回null(本世界全连通,跳过)', true);
}

echo "\nOK=$ok FAIL=$bad\n";
