<?php
// 6/9.断层+武器：locs怪物分布
$t = file_get_contents(__DIR__ . '/includes/game.php');
$p = strpos($t, 'function locs(');
$p2 = strpos($t, "\nfunction ", $p + 10);
$body = substr($t, $p, $p2 - $p);
foreach (explode("\n", $body) as $l) {
    if (strpos($l, 'monsters') !== false || strpos($l, "'name'") !== false) {
        echo trim(mb_substr($l, 0, 140)), "\n";
    }
}
