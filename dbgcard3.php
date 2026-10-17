<?php
$t = file_get_contents('includes/game.php');
// 双倍在哪乘
$p = 0;
while (($p = strpos($t, 'exp_card_until', $p)) !== false) {
    $seg = substr($t, max(0, $p - 160), 260);
    if (strpos($seg, '* 2') !== false || strpos($seg, 'cardLeft') !== false || strpos($seg, 'exp_mult') !== false || strpos($seg, 'time()') !== false) {
        echo trim(preg_replace('/[^\x20-\x7e]/', '?', $seg)), "\n=====\n";
    }
    $p += 14;
}
