<?php
// 10.技能书分栏：bag book tab
$t = file_get_contents(__DIR__ . '/bag.php');
$p = strpos($t, "} elseif (\$tab === 'book') {");
echo $p === false ? "MISS\n" : substr($t, $p, 700), "\n";
