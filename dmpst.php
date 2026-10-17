<?php
// status被动名 + 掉书扩展
$p = __DIR__ . '/status.php';
$lines = file($p);
foreach ($lines as $i => $l) {
    if (strpos($l, 'rebirth2_name((string)') !== false && strpos($l, '$rb2n =') !== false) {
        echo ($i + 1), ': ', trim($l), "\n";
        echo ($i + 2), ': ', trim($lines[$i + 1]), "\n";
    }
}
