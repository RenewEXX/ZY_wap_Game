<?php
$t = file_get_contents('includes/game.php');
$p = strpos($t, 'function dungeon_tick(');
$p2 = strpos($t, "\n}\n", $p) + 3;
echo substr($t, $p, $p2 - $p), "\n";
echo "=== fight gf? ===\n";
$f = file_get_contents('fight.php');
echo strpos($f, 'gf_maps') !== false ? "fight has gf\n" : "fight NO gf\n";
echo "=== dungeon_maps fn ===\n";
$p = strpos($t, 'function dungeon_maps(');
echo substr($t, $p, 260), "\n";
