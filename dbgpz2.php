<?php
$t = file_get_contents('includes/game.php');
// pz怪数值+boss_of_map/enter_dungeon/dungeon_tick通用函数
foreach (["'pz_puller' =>", "'pz_master' =>"] as $k) {
    $p = strpos($t, $k);
    echo substr($t, $p, 200), "\n";
}
foreach (['function enter_dungeon(', 'function boss_of_map(', 'function dungeon_maps('] as $k) {
    echo $k, strpos($t, $k) === false ? ' MISS' : ' EXISTS', "\n";
}
// ticket/券定义
$p = strpos($t, "'pz_ticket'");
echo substr($t, max(0, $p - 100), 200), "\n";
