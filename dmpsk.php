<?php
$lines = file(__DIR__ . '/skills.php');
echo count($lines), " lines\n";
for ($k = 18; $k < min(60, count($lines)); $k++) {
    echo ($k + 1), ': ', substr($lines[$k], 0, 220), "\n";
}
