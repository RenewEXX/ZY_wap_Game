<?php
$t = file_get_contents('includes/game.php');
// tt三怪掉落+岩狱套装现状
foreach (["'tt_magma' => ['slot'", "'tt_warden' => ['slot'", "'tt_titan' => ['slot'", "'岩狱·泰坦'"] as $k) {
    $p = strpos($t, $k);
    echo substr($t, $p, 190), "\n";
}
// 套装pieces rockjail
$p = strpos($t, 'function equip_set_pieces(');
$p2 = strpos($t, "\nfunction ", $p + 10);
$body = substr($t, $p, $p2 - $p);
$q = strpos($body, 'rockjail');
echo substr($body, max(0, $q - 50), 300), "\n";
