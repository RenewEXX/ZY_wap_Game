<?php
// tt_副本已存在大半！查缺：门票/进入NPC/掉落/主城
$t = file_get_contents('includes/game.php');
$n = file_get_contents('npc.php');
foreach (['tt_gate', 'tt_enter', '岩婆', 'tt_trail', 'earth_dragon'] as $k) {
    echo $k, ' game:', substr_count($t, $k), ' npc:', substr_count($n, $k), "\n";
}
// tt怪掉落
foreach (["'tt_magma'", "'tt_warden'", "'tt_titan'"] as $k) {
    echo $k, ' drops:', (strpos($t, $k . ' => [\'slot\'') !== false ? 'YES' : 'NO'), "\n";
}
// 岩狱套装件
foreach (['岩狱·泰坦'] as $k) {
    echo $k, ': ', substr_count($t, $k), "\n";
}
