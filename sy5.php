<?php
$p = 'includes/game.php';
$t = file_get_contents($p);

// 定位 equip_set_bonus_text 内的 dusk 分支（第二个 $set === 'dusk'）
$p1 = strpos($t, 'function equip_set_bonus_text');
$p2 = strpos($t, "if (\$set === 'dusk') {", $p1);
if ($p2 === false) { echo "MISS\n"; exit(1); }
$ins = "    if (\$set === 'ann') {\n        return '【寂灭套装】已穿' . \$owned . '/11（6武器+5防具）——2件：暴击+10%、技伤+15%；3件：全属性+等级×8；5件：暴伤+50%、真伤+等级×30、减伤+15%！渊底不产饶恕，只产终结。';\n    }\n    if (\$set === 'dev') {\n        return '【噬渊套装】已穿' . \$owned . '/11（6武器+5防具）——2件：吸血+12%、生命+等级×60；3件：全属性+等级×8、减伤+12%；5件：真伤+等级×35、攻+20%、吸血再+8%！渊不需要呼吸，它只需要你。';\n    }\n";
$t = substr($t, 0, $p2) . $ins . substr($t, $p2);
file_put_contents($p, $t);
echo "OK\n";