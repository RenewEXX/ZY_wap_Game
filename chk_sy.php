<?php
$t = file_get_contents('includes/game.php');
$p = strpos($t, 'function sy_drop_roll');
$q = strpos($t, "\nfunction dusk_kill_drop", $p);
echo substr($t, $p - 100, $q - $p + 10);