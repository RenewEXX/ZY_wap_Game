<?php
$p = __DIR__ . '/includes/game.php';
$t = file_get_contents($p);
$old = "    if (\$toGround) {\n        \$eid = (int) db()->lastInsertId();\n        \$uo = user_by_id((int) \$uid);\n        ground_place_equip((string) (\$uo['loc'] ?? 'town_sq'), \$eid);\n        return '';\n    }\n    return \$name;";
if (strpos($t, $old) === false) { echo "MISS\n"; exit(1); }
$new = "    if (\$toGround) {\n        \$eid = (int) db()->lastInsertId();\n        \$uo = user_by_id((int) \$uid);\n        ground_place_equip((string) (\$uo['loc'] ?? 'town_sq'), \$eid);\n        return '';\n    }\n    \$as = auto_sell_check((int) \$uid, (int) \$q);\n    if (\$as !== '') { return equip_shortname(\$name) . \$as; }\n    return \$name;";
$t = str_replace($old, $new, $t);
file_put_contents($p, $t);
echo "OK\n";
