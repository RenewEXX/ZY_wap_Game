<?php
$p = 'bag.php';
$t = file_get_contents($p);
$a = "商城人偶栏";
if (strpos($t, $a) === false) { echo "MISS\n"; exit(1); }
$t = str_replace($a, "商城功能栏", $t);
file_put_contents($p, $t);
echo "OK\n";
