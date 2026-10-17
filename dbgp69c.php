<?php
$t = file_get_contents(__DIR__ . '/includes/game.php');
$p = strpos($t, 'function loc(');
$p2 = strpos($t, "\nfunction ", $p + 10);
$body = substr($t, $p, $p2 - $p);
foreach (explode("\n", $body) as $i => $l) {
    if (preg_match("/'(\w+)' => \[/", $l, $m)) {
        // 下3行找monsters
        $seg = implode('', array_slice(explode("\n", $body), $i, 8));
        if (preg_match("/'monsters' => \[([^\]]*)\]/", $seg, $mm)) {
            echo $m[1], ' => ', trim($mm[1]), "\n";
        }
    }
}
