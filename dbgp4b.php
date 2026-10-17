<?php
// 全部tiers现状
$t = file_get_contents(__DIR__ . '/includes/game.php');
$p = strpos($t, 'function affix_catalog(');
$p2 = strpos($t, "\nfunction ", $p + 10);
$body = substr($t, $p, $p2 - $p);
foreach (explode("\n", $body) as $l) {
    if (strpos($l, "'name'") !== false && strpos($l, '$r([') !== false) {
        echo trim($l), "\n";
    }
}
