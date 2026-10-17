<?php
$t = file_get_contents(__DIR__ . '/includes/game.php');
$p = strpos($t, "_stats(\$uid)['hp'];\n    \$delta");
echo substr($t, max(0, $p - 900), 1100), "\n";
