<?php
// 4.T1/T2下限拉高：低阶下限×2.5（高阶不动）
$p = __DIR__ . '/includes/game.php';
$t = file_get_contents($p);
$rep = [
    "'hp' => ['name' => '生命值', 'slots' => ['body', 'head', 'legs', 'shoes', 'ring', 'necklace', 'offhand', 'back'], 'data' => \$r([[8, 15], [15, 30], [30, 55], [55, 100], [90, 150]], 100)]" => "'hp' => ['name' => '生命值', 'slots' => ['body', 'head', 'legs', 'shoes', 'ring', 'necklace', 'offhand', 'back'], 'data' => \$r([[20, 30], [35, 55], [30, 55], [55, 100], [90, 150]], 100)]",
    "'def' => ['name' => '护甲', 'slots' => ['body', 'head', 'legs', 'gloves', 'shoes', 'offhand', 'back'], 'data' => \$r([[4, 10], [10, 20], [20, 35], [35, 60], [6" => "'def' => ['name' => '护甲', 'slots' => ['body', 'head', 'legs', 'gloves', 'shoes', 'offhand', 'back'], 'data' => \$r([[10, 18], [22, 35], [20, 35], [35, 60], [6",
    "'mdef' => ['name' => '法术抗性', 'slots' => ['body', 'head', 'offhand', 'back', 'necklace'], 'data' => \$r([[3, 8], [8, 16], [16, 28], [28, 48], [48, 80]], 90)]" => "'mdef' => ['name' => '法术抗性', 'slots' => ['body', 'head', 'offhand', 'back', 'necklace'], 'data' => \$r([[8, 14], [18, 28], [16, 28], [28, 48], [48, 80]], 90)]",
];
$n = 0;
foreach ($rep as $o => $nw) {
    if (strpos($t, $o) !== false) { $t = str_replace($o, $nw, $t); $n++; }
}
file_put_contents($p, $t);
echo 'replaced: ', $n, "\n";
