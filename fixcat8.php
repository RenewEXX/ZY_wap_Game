<?php
// 8新技能：面板+书+技能组+门控
$p = __DIR__ . '/includes/game.php';
$lines = file($p);
// 找到h6/p6行附近插入（ASCII锚：'h6' =>行）
$idx = null;
foreach ($lines as $i => $l) {
    if (strpos($l, "'h6' =>") !== false) { $idx = $i; break; }
}
if ($idx === null) { echo "MISS h6\n"; exit(1); }
// 找到p6行
$idx2 = null;
foreach ($lines as $i => $l) {
    if (strpos($l, "'p6' =>") !== false) { $idx2 = $i; break; }
}
$new = [
    "        'w7' => ['name' => 'YanXuKuangZhan', 'job' => 'warrior', 'mp' => 55, 'kind' => 'aoe', 'mult' => 2.6, 'el' => 'dark', 't10' => 'P_T10', 'lv20' => 'P_LV20'],\n",
    "        'w8' => ['name' => 'BiLeiBiYu', 'job' => 'warrior', 'mp' => 55, 'kind' => 'aoe', 'mult' => 2.0, 'el' => 'thunder', 't10' => 'Q_T10', 'lv20' => 'Q_LV20'],\n",
    "        'm7' => ['name' => 'LeiBaoShu', 'job' => 'mage', 'mp' => 60, 'kind' => 'aoe', 'mult' => 0, 'el' => 'thunder', 't10' => 'R_T10', 'lv20' => 'R_LV20'],\n",
    "        'm8' => ['name' => 'FengHuoLiaoYuan', 'job' => 'mage', 'mp' => 60, 'kind' => 'aoe', 'mult' => 0, 'el' => 'wind', 't10' => 'S_T10', 'lv20' => 'S_LV20'],\n",
    "        'h7' => ['name' => 'ChuanYunJianYu', 'job' => 'hunter', 'mp' => 55, 'kind' => 'aoe', 'mult' => 2.6, 'el' => 'wind', 't10' => 'T_T10', 'lv20' => 'T_LV20'],\n",
    "        'h8' => ['name' => 'YeMuYingSha', 'job' => 'hunter', 'mp' => 55, 'kind' => 'aoe', 'mult' => 2.8, 'el' => 'dark', 't10' => 'U_T10', 'lv20' => 'U_LV20'],\n",
    "        'p7' => ['name' => 'ShiHunXinXing', 'job' => 'priest', 'mp' => 60, 'kind' => 'aoe', 'mult' => 2.0, 'el' => 'dark', 't10' => 'V_T10', 'lv20' => 'V_LV20'],\n",
    "        'p8' => ['name' => 'ShengGuangShenPan', 'job' => 'priest', 'mp' => 60, 'kind' => 'aoe', 'mult' => 1.0, 'heal' => 150, 'el' => 'light', 't10' => 'W_T10', 'lv20' => 'W_LV20'],\n",
];
array_splice($lines, $idx2 + 1, 0, $new);
file_put_contents($p, implode('', $lines));
echo "OK cat\n";
