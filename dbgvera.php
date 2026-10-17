<?php
// 1.tt收尾：门票+镇狱人NPC+进入handler+teleport region+土龙蛋掉落
// 2.经验卡：exp_card10x/1000x（1钻/天，无等级限制）
// 3.NPC审计：vera开傀儡回廊？看vera展示
$n = file_get_contents('npc.php');
$p = strpos($n, "who === 'vera'");
while ($p !== false) {
    echo substr($n, max(0, $p - 80), 400), "\n=====\n";
    $p = strpos($n, "who === 'vera'", $p + 10);
    if (substr_count(substr($n, 0, $p), "who === 'vera'") > 6) break;
}
