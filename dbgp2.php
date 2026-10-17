<?php
$t = file_get_contents(__DIR__ . '/includes/game.php');
$p = strpos($t, 'function equip_can_wear(');
echo substr($t, $p, 1400), "\n";
