<?php
// 3) 怪物单体：pet段改随机二选一
$p = __DIR__ . '/includes/game.php';
$t = file_get_contents($p);
$old = "    \$tankHp = tank_auto(\$u, 'hp');\n    \$log .= \$tankHp;\n    \$petHurt = pet_hurt((int) (\$u['id'] ?? 0), \$md);\n    if (\$petHurt !== '') {\n        \$log .= \$petHurt;\n    }";
if (strpos($t, $old) === false) { echo "MISS\n"; exit(1); }
$new = "    \$tankHp = tank_auto(\$u, 'hp');\n    \$log .= \$tankHp;\n    if (active_pet((int) (\$u['id'] ?? 0)) && mt_rand(1, 100) <= 50) {\n        \$petHurt = pet_hurt((int) (\$u['id'] ?? 0), \$md);\n        if (\$petHurt !== '') { \$log .= \$petHurt; }\n        \$u['hp'] = (int) \$u['hp'] + \$md;\n        \$log .= '(宠物挡下了这一击)';\n    }";
$t = str_replace($old, $new, $t);
file_put_contents($p, $t);
echo "step2 OK\n";
