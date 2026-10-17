<?php
$t = file_get_contents(__DIR__ . '/includes/game.php');
$p = strpos($t, 'function enhance_equip(');
echo substr($t, $p, 700), "\n";
