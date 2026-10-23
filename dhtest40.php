<?php
define('DH_TEST_MODE', true);
require 'includes/bootstrap.php';
// ============================================================
// 第一章任务「打怪目标」与地图怪物一致性：
// 每个 ch1 任务的 need 怪物，必须能在该任务的 loc 地图刷到（或是该图 BOSS）。
// 曾修复：任务12「调查队的营火」指向矿坑第一层，但失控矿工只刷在第二层，导致“一层没有矿工”。
// ============================================================
$ok = 0; $bad = 0;
function ck40(string $l, bool $c, string $d = '') { global $ok, $bad; if ($c) { $ok++; } else { $bad++; echo "FAIL $l $d\n"; } }

foreach (ch1_quests() as $id => $qs) {
    if (empty($qs['need'])) { continue; }
    $loc = (string) ($qs['loc'] ?? '');
    $L = loc($loc);
    ck40("任务$id loc地图存在($loc)", isset($L['name']), $loc);
    $mons = $L['monsters'] ?? [];
    foreach ($qs['need'] as $mid => $n) {
        $inLoc = in_array($mid, $mons, true);
        $isBoss = boss_of_map($loc) === (string) $mid;
        ck40("任务$id 目标怪 '$mid' 可在 $loc 打到", $inLoc || $isBoss, "loc=$loc mons=" . implode(',', $mons));
    }
}

// 专项：矿坑第一层必须有失控矿工（任务12 的立即前往目标）
ck40('矿坑第一层含失控矿工', in_array('runaway_miner', loc('mine1')['monsters'] ?? [], true), implode(',', loc('mine1')['monsters'] ?? []));

echo "\nOK=$ok FAIL=$bad\n";
