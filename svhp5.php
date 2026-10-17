<?php
$t = file_get_contents(__DIR__ . '/includes/game.php');
$p = 0;
$n = 0;
while (($p = strpos($t, "['maxhp'] =", $p)) !== false && $n < 30) {
    echo substr($t, max(0, $p - 110), 200), "\n---\n";
    $p += 10;
    $n++;
}
