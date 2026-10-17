<?php
// 7.称号+0去掉：enhance_tag_html(0)输出？equip_shortname?
$t = file_get_contents(__DIR__ . '/includes/game.php');
$p = strpos($t, 'function enhance_tag_html(');
echo substr($t, $p, 300), "\n";
$p2 = strpos($t, 'function equip_shortname(');
echo substr($t, $p2, 300), "\n";
