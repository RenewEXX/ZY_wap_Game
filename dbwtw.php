<?php
$t = file_get_contents(__DIR__ . '/includes/game.php');
$p = strpos($t, 'function wear_title(');
echo substr($t, $p, 900), "\n";
