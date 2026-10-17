<?php
$t = file_get_contents('includes/game.php');
foreach (['function gr_locations(', 'function ry_locations(', 'function vx_locations(', 'function gr_maps(', 'function ry_maps(', 'function vx_maps('] as $fn) {
    $p = strpos($t, $fn);
    $p2 = strpos($t, "\n}\n", $p) + 3;
    echo "===== $fn\n", substr($t, $p, $p2 - $p), "\n";
}
