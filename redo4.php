<?php
$p = __DIR__ . '/includes/game.php';
$t = file_get_contents($p);
// monsters：guardian行后插入
$old = "        'guardian' => ['name' => '巨型傀儡·守护者', 'hp' => 4000, 'atk' => 140, 'exp' => 5000, 'gold' => 2500],";
if (strpos($t, $old) === false) { echo "MISS m\n"; exit(1); }
$new = $old . "\n        // ---- 70~100级·影蚀荒原 ----\n        'waste_stalker' => ['name' => '荒原影行者', 'hp' => 1600, 'atk' => 100, 'exp' => 2800, 'gold' => 1100],\n        'waste_hound' => ['name' => '蚀影荒犬', 'hp' => 1900, 'atk' => 108, 'exp' => 3400, 'gold' => 1300],\n        'waste_colossus' => ['name' => '荒原蚀像', 'hp' => 2600, 'atk' => 125, 'exp' => 4600, 'gold' => 1800],";
$t = str_replace($old, $new, $t);
// locations：sforest块——exits加waste
$old2 = "            'exits' => ['bmine2' => '矿道二层', 'manor' => '废弃庄园'],";
if (strpos($t, $old2) === false) { echo "MISS loc\n"; exit(1); }
$t = str_replace($old2, "            'exits' => ['bmine2' => '矿道二层', 'manor' => '废弃庄园', 'waste' => '影蚀荒原'],", $t);
// manor块插入waste（manor怪物行后）
$old3 = "            'monsters' => ['puppet', 'valentin'],";
if (strpos($t, $old3) === false) { echo "MISS manor\n"; exit(1); }
$t = str_replace($old3, $old3 . "\n        'waste' => [\n            'name' => '影蚀荒原',\n            'desc' => '影蚀和荒野的交界，70到100级的试炼场。',\n            'exits' => ['sforest' => '影蚀森林', 'manor' => '废弃庄园'],\n            'monsters' => ['waste_stalker', 'waste_hound', 'waste_colossus'],\n        ],", $t);
file_put_contents($p, $t);
echo "OK\n";
