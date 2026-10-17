<?php
// auto_sell_check被插错位置了！在equip_shortname中间。修
$t = file_get_contents(__DIR__ . '/includes/game.php');
$p = strpos($t, 'function equip_shortname(');
$p2 = strpos($t, "\nfunction ", $p + 10);
$body = substr($t, $p, $p2 - $p);
echo $body, "\n";
