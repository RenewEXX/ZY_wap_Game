<?php
// 1.tt收尾+2.经验卡+3.新主城，一次写完
$p = 'includes/game.php';
$t = file_get_contents($p);
// --- A.门票quest_mats加tt_ticket
$old = "'pz_ticket' => '傀儡回廊入场券'";
if (strpos($t, $old) === false) { echo "MISS ticket\n"; exit(1); }
$t = str_replace($old, "'pz_ticket' => '傀儡回廊入场券', 'tt_ticket' => '岩狱副本入场券'", $t);
// --- B.土龙蛋：tt_titan 3%掉（roll_drop怪物掉蛋逻辑在哪？看shadow_cat_egg掉落）
file_put_contents($p, $t);
echo "OK ticket\n";
