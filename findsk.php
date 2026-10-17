<?php
$t = file_get_contents(__DIR__ . '/includes/game.php');
foreach (['function active_skill(', 'function learn_skill(', 'function skill_tier(', 'function skill_pow('] as $k) {
    $p = strpos($t, $k);
    echo "== $k\n", $p === false ? "NOT FOUND\n" : substr($t, $p, 800), "\n";
}
