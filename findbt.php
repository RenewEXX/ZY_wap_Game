<?php
$t = file_get_contents(__DIR__ . '/fight.php');
$p = 0;
$n = 0;
while (($p = strpos($t, "_SESSION['battle']", $p)) !== false && $n < 10) {
    echo substr($t, max(0, $p - 150), 380), "\n=====\n";
    $p += 18;
    $n++;
}
