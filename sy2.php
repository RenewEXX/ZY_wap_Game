<?php
$p = 'includes/game.php';
$t = file_get_contents($p);
$a = "god_locations() + gf_locations();";
if (strpos($t, $a) === false) { echo "MISS\n"; exit(1); }
$t = str_replace($a, "god_locations() + gf_locations() + sy_locations();", $t);
file_put_contents($p, $t);
echo "OK\n";