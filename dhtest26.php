<?php
define('DH_TEST_MODE', true);
require 'includes/bootstrap.php';

$ok = 0; $bad = 0;
function ck26(string $l, bool $c, string $d = '') { global $ok,$bad; if ($c) { $ok++; } else { $bad++; echo "FAIL $l $d\n"; } }

$all = locations();

// —— 1. 六圣柱连通（可徒步到达）——
$dragon = dragon_maps();
foreach ($dragon as $id) {
    ck26("六圣柱[$id]存在", isset($all[$id]), '');
    ck26("六圣柱[$id]从主城可达", find_route('town_sq', $id) !== [], json_encode(find_route('town_sq', $id)));
}
ck26('中央圣阶可达', find_route('town_sq', 'holy_stair') !== [], '');
ck26('天空之城遗迹可达', find_route('town_sq', 'sky_city_ruin') !== [], '');
// 各入口双向
ck26('王都→起风处', in_array('sky_ramp', array_keys($all['capital_gate']['exits']), true), json_encode(array_keys($all['capital_gate']['exits'])));
ck26('冰原村→冰原深处', in_array('frost_deep', array_keys($all['ice_village']['exits']), true), '');
ck26('渡口→雷暴禁区', in_array('storm_wall', array_keys($all['fo_gate']['exits']), true), '');
ck26('深渊祭坛→世界深渊', in_array('abyss_deep', array_keys($all['altar']['exits']), true), '');

// —— 2. 传送屏蔽六圣柱 ——
$regSix = map_regions()['六圣柱'] ?? [];
ck26('六圣柱图组非空', $regSix !== [], '');
foreach ($regSix as $id) {
    ck26("六圣柱[$id]被dragon_maps屏蔽传送", in_array($id, $dragon, true), $id);
}

// —— 3. 传说装备必带专属效果 ——
$uid = user_create('w26' . mt_rand(1000,9999), 'x', 'warrior', 'z1', true, 'tmp');
function latest_affixes(int $uid): array {
    $row = db()->query('SELECT affixes FROM equips WHERE uid=' . (int)$uid . ' ORDER BY id DESC LIMIT 1')->fetch();
    return $row ? (json_decode((string)$row['affixes'], true) ?: []) : [];
}
function has_legend(array $affixes): bool { foreach ($affixes as $a) { if (!empty($a['legend'])) return true; } return false; }

$slots = ['weapon','offhand','body','head','legs','belt','gloves','shoes','back','ring','necklace'];
foreach ($slots as $s) {
    make_equip((int)$uid, $s, '测试基底' . $s, 4, 100); // 无 legend，无 '·'
    ck26("传说[$s]自动补专属效果", has_legend(latest_affixes($uid)), '');
}
// 套装/特殊（含·）不强制补，避免破坏套装语义
make_equip((int)$uid, 'body', '烬焰·守卫铠', 4, 700);
ck26('套装(含·)不被强补专属', !has_legend(latest_affixes($uid)), '');
// 非传说不补
make_equip((int)$uid, 'weapon', '测试基底3', 3, 100);
ck26('史诗(3)不补专属', !has_legend(latest_affixes($uid)), '');

// —— 4. 偷袭NPC传说全部带extra且能出专属效果 ——
foreach (ambush_npcs() as $npc => $cfg) {
    $lg = $cfg['legend'];
    ck26("偷袭[$npc]传说有extra", isset($lg['extra']['k'], $lg['extra']['v']), json_encode($lg));
    $sn = make_equip((int)$uid, (string)$lg['slot'], (string)$lg['base'], 4, (int)$cfg['lv'], $lg);
    $af = latest_affixes($uid);
    ck26("偷袭[$npc]传说出专属效果", $sn !== '' && has_legend($af), $sn);
}

// —— 5. 迁移：老的无效果传说被补上 ——
$uid2 = user_create('w26b' . mt_rand(1000,9999), 'x', 'warrior', 'z1', true, 'tmp');
$st = db()->prepare('INSERT INTO equips (uid,slot,name,quality,affixes,pos,item_level,req_lv,req_str,req_agi) VALUES (?,?,?,?,?,?,?,?,?,?)');
$st->execute([(int)$uid2, 'necklace', '传说·圣骸 rosary', 4, json_encode([['id'=>'hp','tier'=>'S','v'=>1000]]), '', 30, 1, 0, 0]);
$eid = (int) db()->lastInsertId();
migrate_plain_legendaries((int)$uid2);
$af = db()->query('SELECT affixes FROM equips WHERE id=' . $eid)->fetch();
$afd = json_decode((string)$af['affixes'], true) ?: [];
ck26('迁移给老传说补了专属效果', has_legend($afd), json_encode($afd));

// —— 6. 技能基础效果描述存在 ——
$c = skill_catalog()['h2'] ?? null;
ck26('爆裂箭在技能表', $c !== null && $c['name'] === '爆裂箭', '');
ck26('爆裂箭有基础效果描述', $c !== null && str_contains(skill_effect_desc($c), '威力'), $c ? skill_effect_desc($c) : '');

// 清理
foreach ([$uid, $uid2] as $x) {
    db()->exec('DELETE FROM users WHERE id=' . (int)$x);
    db()->exec('DELETE FROM equips WHERE uid=' . (int)$x);
    db()->exec('DELETE FROM mats WHERE uid=' . (int)$x);
}

echo "\nOK=$ok FAIL=$bad\n";
