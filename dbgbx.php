<?php
$t = file_get_contents(__DIR__ . '/includes/game.php');
$p = strpos($t, 'function open_dragon_box(');
echo substr($t, $p, 1100), "\n";
$p2 = strpos($t, 'function grant_chapter_titles(');
echo substr($t, $p2, 800), "\n";
