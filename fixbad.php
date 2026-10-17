<?php
// 自动售卖接入点找错了return $name。正确位置是make_equip的return
$t = file_get_contents(__DIR__ . '/includes/game.php');
// 先把shortname里的两行删掉
$old = "    \$as = auto_sell_check((int) \$uid, (int) \$q);\n    if (\$as !== '') { return equip_shortname(\$name) . \$as; }\n    return \$name;";
if (strpos($t, $old) === false) { echo "MISS\n"; exit(1); }
$t = str_replace($old, "    return \$name;", $t);
// make_equip的INSERT行后找return
$p = strpos($t, 'function make_equip(');
$p2 = strpos($t, "\nfunction ", $p + 10);
$body = substr($t, $p, $p2 - $p);
echo substr($body, -500), "\n";
file_put_contents(__DIR__ . '/includes/game.php', $t);
