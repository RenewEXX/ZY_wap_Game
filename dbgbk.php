<?php
$p = __DIR__ . '/bag.php';
$t = file_get_contents($p);
// 技能书块从quest搬到book tab（book tab现在是空的？）
$lines = explode("\n", $t);
$bi = null;
foreach ($lines as $i => $l) {
    if (strpos($l, "tab === 'book'") !== false && strpos($l, 'elseif') === false && strpos($l, 'in_array') === false) { echo 'booktab @', $i + 1, ': ', trim($l), "\n"; }
}
// 找book tab内容
foreach ($lines as $i => $l) {
    if (strpos($l, "'book'") !== false) { echo ($i + 1), ': ', trim(mb_substr($l, 0, 110)), "\n"; }
}
