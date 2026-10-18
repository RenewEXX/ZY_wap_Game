<?php
require 'includes/bootstrap.php';
$ok = 0; $bad = 0;
function chk(string $label, $got, $want) { global $ok,$bad; if ($got === $want) { $ok++; } else { $bad++; echo "FAIL $label: got=", var_export($got,true), " want=", var_export($want,true), "\n"; } }

chk('拖拽-怪物合并', isset(monsters()['da_nightmare']), true);
chk('怪物元素', monster_element('li_gold_herald'), 'light');
chk('怪物等级', monster_lv('th_chain_dragon'), 800);
chk('地图合并', isset(locations()['wb_light']), true);
chk('龙巢不进传送区', in_array('wb_light', map_regions()['六圣柱'], true), false);
chk('套装识别', equip_set_id('苍岚·游云戒'), 'azure');
chk('套装识别2', equip_set_id('圣裁·裁决杖'), 'judgment');
chk('套装件', count(equip_set_pieces('ruin')), 7);
chk('套装文案', str_contains(equip_set_bonus_text('thunder', 7), '天谴套装'), true);
chk('宠物', pet_species()['storm_tyrant']['quality'], 'legend');
chk('宠物蛋', pet_eggs()['ice_dragon_egg']['species'], 'frost_maiden');
chk('材料名', mat_name('soul_wind'), '龙魂·风');
chk('材料名2', mat_name('dragon_core'), '龙王精华');
chk('免疫同属性', drg_damage_vs('fire','fire',9999), 0);
chk('不免疫异属性', drg_damage_vs('fire','ice',9999), 9999);
chk('无元素', drg_damage_vs('fire','',500), 500);
chk('进场等级', dragon_min_lv('aurelian'), 800);
chk('龙巢反查', dragon_of_lair('wb_ice'), 'ysolde');
chk('普通怪不掉掉落表', monster_drops('az_gale_hound'), []);
chk('小怪材料', monster_material('da_rot_priest')[0], 'soul_dark');
chk('小怪不掉套装', monster_drops('da_nightmare'), []);
chk('小怪不掉套装2', monster_drops('li_cloud_sentinel'), []);
chk('转生龙玉1转', rs_jade_cost(1), 2);
chk('转生龙玉2转', rs_jade_cost(2), 4);
chk('转生龙玉3转', rs_jade_cost(3), 6);
chk('复生间隔30天', drg_respawn_secs(), 2592000);
chk('部位', dragon_piece_slots()['苍岚·飘羽靴'], 'shoes');
chk('状态血量', drg_status('doyle')['max'], 30000000);
chk('免疫元素表', in_array(monster_element('gl_ice_choir'), ['ice'], true), true);
chk('排行表已升级', strpos((string) db()->query("SELECT sql FROM sqlite_master WHERE name='wb_dmg'")->fetchColumn(), 'mid') !== false, true);
chk('排行写入', drg_add_dmg(1, 't', 'ysolde', 1000) === null, true);
chk('排行读回', drg_my_dmg(1, 'ysolde') >= 1000, true);
chk('排行分龙隔离', drg_my_dmg(1, 'rex'), 0);
chk('龙巢互不串门', dragon_of_lair('wb_light'), 'aurelian');
chk('转生读龙王', is_bool(rs_requirements(1)['wb']), true);
$cols = db()->query('PRAGMA table_info(wb_dmg)')->fetchAll();
chk('排行表结构', in_array('mid', array_column($cols, 'name'), true), true);
chk('首领掉龙王精华', in_array(monster_material('da_nightmare')[0], ['dragon_core', 'soul_dark'], true), true);
// 龙玉+龙魂扣除（先清零，做确定性的断言）
foreach (dragon_elements() as $el) {
    mat_set(1, 'jade_' . $el, 0);
    mat_set(1, 'soul_' . $el, 0);
}
add_mat(1, 'jade_fire', 3);
add_mat(1, 'jade_dark', 5);
add_mat(1, 'soul_fire', 4);
chk('龙玉总量', rs_mat_total(1, 'jade_'), 8);
chk('龙魂总量', rs_mat_total(1, 'soul_'), 4);
chk('龙玉扣除', rs_mat_pay(1, 'jade_', 2), '');
chk('龙魂扣除', rs_mat_pay(1, 'soul_', 3), '');
chk('扣完龙玉', rs_mat_total(1, 'jade_'), 6);
chk('扣完龙魂', rs_mat_total(1, 'soul_'), 1);
chk('不够时报错', rs_mat_pay(1, 'soul_', 99) !== '', true);
mat_set(1, 'jade_fire', 0);
mat_set(1, 'jade_dark', 0);
mat_set(1, 'soul_fire', 0);
// 斩龙 → 一个月锁 + 公屏播报
$before = (int) db()->query("SELECT COUNT(*) FROM chat_msgs WHERE username='六圣柱'")->fetchColumn();
drg_kill(1, 'aetherine');
chk('斩龙标记', drg_done('aetherine'), 1);
chk('血量归零', drg_hp_now('aetherine'), 0);
chk('锁定一个月', drg_respawn_left('aetherine') > 2592000 - 5, true);
chk('状态为已讨伐', drg_status('aetherine')['done'], 1);
$after = (int) db()->query("SELECT COUNT(*) FROM chat_msgs WHERE username='六圣柱'")->fetchColumn();
chk('公屏播报斩龙', $after - $before, 1);
$last = db()->query("SELECT text FROM chat_msgs WHERE username='六圣柱' ORDER BY id DESC LIMIT 1")->fetchColumn();
chk('播报内容含龙名', str_contains((string) $last, '艾瑟琳'), true);
drg_kv_set('wb_next_aetherine', time() - 1);
drg_tick();
chk('满月复生', drg_status('aetherine')['done'], 0);
chk('复生回满血', drg_hp_now('aetherine'), 42000000);
echo "OK=$ok FAIL=$bad\n";
