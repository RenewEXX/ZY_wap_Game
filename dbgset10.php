<?php
// tt套装10件是否齐？搜岩狱·
$t = file_get_contents('includes/game.php');
preg_match_all("/'岩狱·([^']+)'/", $t, $m);
echo implode(',', array_unique($m[1])), "\ncount=", count(array_unique($m[1])), "\n";
// tt怪掉土龙蛋？
echo 'egg drop: ', strpos($t, 'earth_dragon') !== false ? 'defined' : 'MISS', "\n";
$p = 0;
while (($p = strpos($t, 'earth_dragon', $p)) !== false) {
    echo trim(preg_replace('/[^\x20-\x7e]/', '?', substr($t, max(0, $p - 100), 200))), "\n---\n";
    $p += 12;
}
