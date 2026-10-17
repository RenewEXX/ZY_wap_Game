<?php
$t = file_get_contents(__DIR__ . '/includes/game.php');
foreach (['function loc(', 'function zones(', 'function maps('] as $k) {
    $p = strpos($t, $k);
    echo $k, ': ', $p === false ? 'MISS' : 'found', "\n";
}
$p = strpos($t, "'monsters' =>");
echo substr($t, max(0, $p - 300), 400), "\n";
