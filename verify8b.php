<?php
$lines = file(__DIR__ . '/includes/game.php');
for ($k = 2856; $k < 2862; $k++) {
    echo ($k + 1), ': ', $lines[$k];
}
