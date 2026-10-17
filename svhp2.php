<?php
$t = file_get_contents(__DIR__ . '/bag.php');
$p = 0;
while (($p = strpos($t, 'maxhp', $p)) !== false) {
    echo substr($t, max(0, $p - 200), 360), "\n=====\n";
    $p += 5;
}
