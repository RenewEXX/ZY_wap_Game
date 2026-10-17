<?php
$p = __DIR__ . '/includes/game.php';
$t = file_get_contents($p);
$old = "    \$tankHp = tank_auto(\$u, 'hp');\n    \$log .= \$tankHp;\n    \$petHurt = pet_hurt((int) (\$u['id'] ?? 0), \$md);\n    if (\$petHurt !== '') {\n        \$log .= \$petHurt;\n    }";
if (strpos($t, $old) === false) { echo "MISS\n"; exit(1); }
// 随机二选一：50%宠物全吃（人不掉），否则人全吃（宠物不掉）
$new = "    \$tankHp = tank_auto(\$u, 'hp');\n    \$log .= \$tankHp;\n    if (active_pet((int) (\$u['id'] ?? 0)) && mt_rand(1, 100) <= 50) {\n        \$petHurt = pet_hurt((int) (\$u['id'] ?? 0), \$md);\n        if (\$petHurt !== '') { \$log .= \$petHurt; }\n        \$u['hp'] = (int) \$u['hp'] + \$md;\n        \$log .= '(宠物挡下了这一击)';\n    }";
$t = str_replace($old, $new, $t);
file_put_contents($p, $t);
echo "OK\n";
