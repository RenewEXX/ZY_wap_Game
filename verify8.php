<?php
$lines = file(__DIR__ . '/includes/game.php');
foreach ($lines as $i => $l) {
    if (strpos($l, 'function job_skillset(') !== false) {
        echo ($i + 2), ': ', trim($lines[$i + 1]), "\n";
    }
    if (strpos($l, "'book_w7'") !== false) {
        echo ($i + 1), ': ', trim($l), "\n";
    }
}
