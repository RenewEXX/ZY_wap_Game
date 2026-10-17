<?php
$t = file_get_contents('includes/game.php');
$p = strpos($t, 'function roll_pet_egg(');
$p2 = strpos($t, "\nfunction ", $p + 10);
echo substr($t, $p, $p2 - $p), "\n";
