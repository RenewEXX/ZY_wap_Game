<?php
// 6.70-100级断层：看各图怪等级
$t = file_get_contents(__DIR__ . '/includes/game.php');
$p = strpos($t, 'function monsters(');
$p2 = strpos($t, "\nfunction ", $p + 10);
$body = substr($t, $p, $p2 - $p);
foreach (explode("\n", $body) as $l) {
    if (strpos($l, "'lv'") !== false || (strpos($l, '=> [') !== false && strpos($l, "'hp'") !== false)) {
        echo trim(mb_substr($l, 0, 130)), "\n";
    }
}
