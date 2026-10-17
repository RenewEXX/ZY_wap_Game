<?php
// 300+主城现状：gr/as/vx/frostburg/greenheart/wild45地图
$t = file_get_contents('includes/game.php');
foreach (['function gr_locations(', 'function as_locations(', 'function frostburg_locations(', 'function greenheart_locations(', 'function wild45_locations('] as $k) {
    $p = strpos($t, $k);
    if ($p === false) { echo $k, " MISS\n"; continue; }
    $p2 = strpos($t, "\nfunction ", $p + 10);
    $body = substr($t, $p, $p2 - $p);
    preg_match_all("/\\\$m\['([a-z0-9_]+)'\] = \['name' => '([^']*)'/u", $body, $m);
    echo $k, "\n";
    foreach (($m[1] ?? []) as $i => $id) {
        echo '  ', $id, ' ', mb_substr($m[2][$i] ?? '', 0, 14), "\n";
    }
}
