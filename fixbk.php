<?php
$p = __DIR__ . '/bag.php';
$t = file_get_contents($p);
// quest里的技能书块起止
$s0 = strpos($t, "    echo '技能书：<br>';");
$s1 = strpos($t, "} elseif (\$tab === 'func') {");
echo 'book block: ', substr_count(substr($t, 0, $s0), "\n") + 1, ' to ', substr_count(substr($t, 0, $s1), "\n") + 1, "\n";
$block = substr($t, $s0, $s1 - $s0);
$t = substr($t, 0, $s0) . substr($t, $s1);
// book tab：在mat块结束处插入（找"} elseif ($tab === 'quest') {"）
$anchor = "} elseif (\$tab === 'quest') {";
$pos = strpos($t, $anchor);
$newTab = "} elseif (\$tab === 'book') {\n    \$mats = mats_of((int) \$u['id']);\n" . $block . "\n" . $anchor;
$t = str_replace($anchor, $newTab, $t);
file_put_contents($p, $t);
echo "OK\n";
