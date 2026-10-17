<?php
$t = file_get_contents(__DIR__ . '/includes/game.php');
$p = strpos($t, '$before = gear_stats($uid)');
echo substr($t, $p, 1500), "\n";
