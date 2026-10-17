<?php
$t = file_get_contents('includes/game.php');
$p = strpos($t, 'function monster_drops(');
$p2 = strpos($t, "\n}\n", $p) + 3;
$body = substr($t, $p, $p2 - $p);
// 找最后几条
$lines = explode("\n", $body);
echo "LINES=", count($lines), "\n";
echo implode("\n", array_slice($lines, -12)), "\n";
