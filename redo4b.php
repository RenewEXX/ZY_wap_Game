<?php
$p = __DIR__ . '/includes/game.php';
$t = file_get_contents($p);
$old = "        'guardian' => ['name' => '巨型傀儡·守护者', 'hp' => 4000, 'atk' => 140, 'exp' => 5000, 'gold' => 2500],\n";
$new = $old . "        // ---- 70~100级·影蚀荒原 ----\n        'waste_stalker' => ['name' => '荒原影行者', 'hp' => 1600, 'atk' => 100, 'exp' => 2800, 'gold' => 1100],\n        'waste_hound' => ['name' => '蚀影荒犬', 'hp' => 1900, 'atk' => 108, 'exp' => 3400, 'gold' => 1300],\n        'waste_colossus' => ['name' => '荒原蚀像', 'hp' => 2600, 'atk' => 125, 'exp' => 4600, 'gold' => 1800],\n";
$t = str_replace($old, $new, $t);
file_put_contents($p, $t);
echo strpos($t, 'waste_stalker') === false ? "FAIL\n" : "mobs OK\n";
