<?php
// 怪打人段：$md产生处
$t = file_get_contents(__DIR__ . '/includes/game.php');
$p = 0;
while (($p = strpos($t, 'pet_hurt(', $p)) !== false) {
    echo substr($t, max(0, $p - 600), 700), "\n=====\n";
    $p += 9;
}
