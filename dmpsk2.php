<?php
$lines = file(__DIR__ . '/skills.php');
for ($k = 34; $k < 52; $k++) {
    echo ($k + 1), ': ', substr($lines[$k], 0, 120), "\n";
}
