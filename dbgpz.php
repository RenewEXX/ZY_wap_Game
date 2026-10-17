<?php
$t = file_get_contents('includes/game.php');
// pz副本全貌（最新最小副本，照抄结构）
$p = strpos($t, 'function pz_locations(');
$p2 = strpos($t, "\nfunction ", $p + 10);
echo substr($t, $p, $p2 - $p), "\n";
