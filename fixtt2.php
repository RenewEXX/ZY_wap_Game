<?php
// tt收尾：土龙蛋+经验卡+镇狱人NPC+进入+teleport+主城，一次写完
$p = 'includes/game.php';
$t = file_get_contents($p);
// 土龙蛋3%
$old = "    if (\$mid === 'string_puller') {\n        add_mat(\$uid, 'puppet_egg', 1);\n        return '傀儡人偶蛋';\n    }";
if (strpos($t, $old) === false) { echo "MISS egg\n"; exit(1); }
$t = str_replace($old, $old . "\n    if (\$mid === 'tt_titan' && mt_rand(1, 100) <= 3) {\n        add_mat(\$uid, 'earth_dragon_egg', 1);\n        return '土龙岳柱幼体蛋（3%）';\n    }", $t);
// earth_dragon_egg进pet_eggs
$old2 = "'abyss_witch' =>";
if (strpos($t, $old2) === false) { echo "MISS eggs\n"; exit(1); }
// pet_eggs函数里的earth_dragon？
echo 'has egg def: ', strpos($t, "'earth_dragon_egg'") !== false ? 'YES' : 'NO', "\n";
file_put_contents($p, $t);
echo "OK\n";
