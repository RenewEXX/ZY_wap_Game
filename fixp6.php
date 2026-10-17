<?php
// 6.70-100新图：影蚀荒原（sforest后）+怪3只+装备掉落
$p = __DIR__ . '/includes/game.php';
$lines = file($p);
// monsters插入点：gargoyle行后
foreach ($lines as $i => $l) {
    if (strpos($l, "'gargoyle' =>") !== false) { $gi = $i; break; }
}
$newM = [
    "        // ---- 70~100级·影蚀荒原 ----\n",
    "        'waste_stalker' => ['name' => '荒原影行者', 'hp' => 1600, 'atk' => 100, 'exp' => 2800, 'gold' => 1100],\n",
    "        'waste_hound' => ['name' => '蚀影荒犬', 'hp' => 1900, 'atk' => 108, 'exp' => 3400, 'gold' => 1300],\n",
    "        'waste_colossus' => ['name' => '荒原蚀像', 'hp' => 2600, 'atk' => 125, 'exp' => 4600, 'gold' => 1800],\n",
];
array_splice($lines, $gi + 1, 0, $newM);
// locations：sforest行后加新图
foreach ($lines as $i => $l) {
    if (strpos($l, "'sforest' =>") !== false) { $si = $i; break; }
}
// 找到sforest块结束（'],'行）
for ($k = $si; $k < $si + 10; $k++) {
    if (trim($lines[$k]) === '],') { $se = $k; break; }
}
$newL = [
    "        'waste' => [\n",
    "            'name' => '影蚀荒原',\n",
    "            'desc' => '影蚀和荒野的交界，70到100级的试炼场。',\n",
    "            'exits' => ['sforest' => '影蚀森林', 'manor' => '废弃庄园'],\n",
    "            'monsters' => ['waste_stalker', 'waste_hound', 'waste_colossus'],\n",
    "        ],\n",
];
array_splice($lines, $se + 1, 0, $newL);
file_put_contents($p, implode('', $lines));
echo "OK\n";
