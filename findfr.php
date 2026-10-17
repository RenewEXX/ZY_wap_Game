<?php
$t = file_get_contents(__DIR__ . '/includes/game.php');
$p = 0;
$n = 0;
while (($p = strpos($t, "['frost']", $p)) !== false && $n < 8) {
    echo substr($t, max(0, $p - 180), 400), "\n=====\n";
    $p += 9;
    $n++;
}
