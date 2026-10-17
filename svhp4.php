<?php
$t = file_get_contents(__DIR__ . '/includes/game.php');
$p = 0;
$n = 0;
while (($p = strpos($t, "['hp']", $p)) !== false && $n < 25) {
    $seg = substr($t, max(0, $p - 120), 200);
    if (strpos($seg, 'maxhp') !== false || strpos($seg, 'gear_stats') !== false) {
        echo $seg, "\n---\n";
    }
    $p += 6;
    $n++;
}
