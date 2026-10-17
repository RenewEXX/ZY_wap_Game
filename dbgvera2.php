<?php
// vera只开影蚀深渊——用户说的"vera开傀儡回廊"不存在。找谁在低级区开高级本：
// spider@theater开300-460：theater是王都（多少级？）
$t = file_get_contents('includes/game.php');
foreach (['teleport_min_lv', 'teleport_max_lv'] as $k) {
    $p = strpos($t, "function $k(");
    $p2 = strpos($t, "\nfunction ", $p + 10);
    echo substr($t, $p, min(800, $p2 - $p)), "\n=====\n";
}
