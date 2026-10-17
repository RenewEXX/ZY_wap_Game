<?php
$p = __DIR__ . '/includes/game.php';
$lines = file($p);
// 书：book_p6行后
$bi = null;
foreach ($lines as $i => $l) {
    if (strpos($l, "'book_p6' =>") !== false) { $bi = $i; break; }
}
if ($bi === null) { echo "MISS book\n"; exit(1); }
$books = [
    "        'book_w7' => ['skill' => 'w7', 'name' => 'XueNuMiJi'],\n",
    "        'book_w8' => ['skill' => 'w8', 'name' => 'BiYuMiJi'],\n",
    "        'book_m7' => ['skill' => 'm7', 'name' => 'LeiBaoMiJi'],\n",
    "        'book_m8' => ['skill' => 'm8', 'name' => 'FengHuoMiJi'],\n",
    "        'book_h7' => ['skill' => 'h7', 'name' => 'ChuanYunMiJi'],\n",
    "        'book_h8' => ['skill' => 'h8', 'name' => 'YeMuMiJi'],\n",
    "        'book_p7' => ['skill' => 'p7', 'name' => 'ShiHunMiJi'],\n",
    "        'book_p8' => ['skill' => 'p8', 'name' => 'ShenPanMiJi'],\n",
];
array_splice($lines, $bi + 1, 0, $books);
// 技能组
foreach ($lines as $i => $l) {
    if (strpos($l, 'function job_skillset(') !== false) {
        for ($k = $i; $k < $i + 4; $k++) {
            if (strpos($lines[$k], "'warrior' =>") !== false) {
                $lines[$k] = "    return ['warrior' => ['w1', 'w2', 'w3', 'w4', 'w5', 'w6', 'w7', 'w8'], 'mage' => ['m1', 'm2', 'm3', 'm4', 'm5', 'm6', 'm7', 'm8'], 'hunter' => ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'h7', 'h8'], 'priest' => ['p1', 'p2', 'p3', 'p4', 'p5', 'p6', 'p7', 'p8']][\$job] ?? ['w1', 'w2', 'w3', 'w4', 'w5', 'w6', 'w7', 'w8'];\n";
                break;
            }
        }
        break;
    }
}
file_put_contents($p, implode('', $lines));
echo "OK\n";
