<?php
$files = ['mall.php', 'bag.php'];
foreach ($files as $f) {
    $t = file_get_contents(__DIR__ . '/' . $f);
    foreach (explode("\n", $t) as $i => $l) {
        if (strpos($l, '15%') !== false || (strpos($l, '10%') !== false && strpos($l, '自动') !== false)) {
            echo $f, ' ', $i + 1, ': ', trim(mb_substr($l, 0, 110)), "\n";
        }
    }
}
