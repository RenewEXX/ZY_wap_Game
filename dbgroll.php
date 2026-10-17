<?php
// 泰坦掉蛋：roll_drop里abaddon_egg怎么掉的
$t = file_get_contents('includes/game.php');
$p = strpos($t, 'abaddon_egg');
echo substr($t, max(0, $p - 300), 420), "\n";
