<?php
$t = file_get_contents('includes/game.php');
$p = strpos($t, 'function equip_set_owned_count');
echo substr($t, $p, 460), "\n";