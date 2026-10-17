<?php
$t = file_get_contents(__DIR__ . '/includes/game.php');
foreach (["'burn'", 'aegiswall', 'function my_skills('] as $k) {
    $p = strpos($t, $k);
    echo "== $k: ", $p === false ? 'NONE' : substr($t, max(0, $p - 200), 450), "\n";
}
echo "== skills.php head:\n", substr(file_get_contents(__DIR__ . '/skills.php'), 0, 1200), "\n";
