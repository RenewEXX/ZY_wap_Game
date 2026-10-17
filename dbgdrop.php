<?php
$t = file_get_contents('includes/game.php');
$p = strpos($t, "add_mat(\$uid, 'shadow_cat_egg', 1);");
echo substr($t, max(0, $p - 500), 650), "\n";
