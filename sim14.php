<?php
require __DIR__ . '/includes/bootstrap.php';
// 完整冒烟
echo '1 tank: ', (strpos(file_get_contents(__DIR__ . '/includes/game.php'), "'hp' ? 15 : 30") !== false ? 'OK' : 'FAIL'), "\n";
echo '2 sync js: ', (strpos(file_get_contents(__DIR__ . '/fight.php'), '同一tick') !== false ? 'OK' : 'FAIL'), "\n";
echo '3 dummy: ', (strpos(file_get_contents(__DIR__ . '/includes/game.php'), 'dummy_num') !== false ? 'OK' : 'FAIL'), "\n";
echo '4 autorev: ', (strpos(file_get_contents(__DIR__ . '/includes/game.php'), 'pet_autorev') !== false ? 'OK' : 'FAIL'), "\n";
echo '5 hide: ', (strpos(file_get_contents(__DIR__ . '/skills.php'), 'unlearned') === false ? 'OK' : 'FAIL'), "\n";
echo '6 nodrink: ', (strpos(file_get_contents(__DIR__ . '/fight.php'), 'drink&m=potion') === false ? 'OK' : 'FAIL'), "\n";
echo '7 belt: slots=', (equip_slots()['belt'] ?? 'MISS'), ' bagcap=', affix_catalog()['bagcap']['name'], "\n";
echo '8 listable title: ', (auction_listable_equip(['pos' => '', 'slot' => 'title', 'quality' => 4, 'name' => 'x']) ? 'OK' : 'FAIL'), "\n";
echo '8 rare gone: ', (strpos(file_get_contents(__DIR__ . '/auction.php'), "2 => '稀有'") === false ? 'OK' : 'FAIL'), "\n";
echo '9 ground: ', (strpos(file_get_contents(__DIR__ . '/bag.php'), '不能拍卖') !== false ? 'OK' : 'FAIL'), "\n";
echo '10 take warn fixed: ', (strpos(file_get_contents(__DIR__ . '/includes/game.php'), '+ $mDelta') === false ? 'OK' : 'FAIL'), "\n";
echo '11 req_lv: ', (db()->query("SELECT req_lv FROM equips WHERE slot='title' LIMIT 1")->fetchColumn() ?: 'NONE'), "\n";
echo '14 tag: ', (strpos(file_get_contents(__DIR__ . '/includes/game.php'), '属性伤害') !== false ? 'OK' : 'FAIL'), "\n";
echo "SMOKE DONE\n";
