<?php
// dhtest80：召唤物伤害统一为暗属性，并吃「元素伤害(flat)」+「暗伤%」+ 怪物暗抗/克制
define('DH_TEST_MODE', true);
require 'includes/bootstrap.php';
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
$ok = 0; $bad = 0;
function ck80(string $l, bool $c, string $d = ''): void { global $ok, $bad; if ($c) { $ok++; } else { $bad++; echo "FAIL $l $d\n"; } }

// 1) 基线：无角色(uid=0)、中立怪、无加成 → 伤害不变、无后缀
[$h0, $s0] = summoner_dark_damage(0, ['id' => 'rat'], 1000);
ck80('基线(无加成/中立怪)=原值', $h0 === 1000, (string) $h0);
ck80('中立怪无属性后缀', $s0 === '', $s0);

// 2) 暗属性克制关系：暗 打 光 = 1.3 克制
ck80('golden_slime 是光属性', monster_element('golden_slime') === 'light', monster_element('golden_slime'));
ck80('暗打光=克制×1.3', abs(element_chart('dark', 'light') - 1.3) < 1e-9, (string) element_chart('dark', 'light'));

$uid = (int) user_create('sdg' . mt_rand(1, 999999), 'x', 'summoner', 'z1', true, 'tmp');
$u = user_by_id($uid);
$u['lv'] = 300; $u['int'] = 20000; $u['grow_matk'] = 20000; $u['maxhp'] = 1000000; $u['hp'] = 1000000;
user_save($u); _gear_uncache($uid);

// 3) 元素伤害(flat) 参与召唤物伤害：加 500 元素伤害，伤害应随之抬高约 500
db()->prepare('INSERT INTO equips (uid,slot,name,quality,affixes,pos,req_lv) VALUES (?,?,?,3,?,"wear",1)')
    ->execute([$uid, 'ring', '测试元素戒', json_encode([['id' => 'element', 'v' => 500]], JSON_UNESCAPED_UNICODE)]);
_gear_uncache($uid);
[$hE, $sE] = summoner_dark_damage($uid, ['id' => 'rat'], 1000);
ck80('元素伤害(flat)提高召唤物伤害', $hE >= 1500, "hE=$hE");

// 4) 暗伤%(传说)：戴 lg_darkvow(暗属性伤害+20%) → 倍率 1.2 且伤害更高
db()->prepare('INSERT INTO equips (uid,slot,name,quality,affixes,pos,req_lv) VALUES (?,?,?,4,?,"wear",1)')
    ->execute([$uid, 'necklace', '暗誓', json_encode([['id' => 'lg_darkvow', 'v' => 1]], JSON_UNESCAPED_UNICODE)]);
_gear_uncache($uid);
$GLOBALS['_legend_gen'] = (int) ($GLOBALS['_legend_gen'] ?? 0) + 1;
$m = function_exists('legend_relic_el_mult') ? legend_relic_el_mult($uid, 'dark') : 1.0;
ck80('暗伤%倍率=1.2', abs($m - 1.2) < 0.01, (string) $m);
[$hD, $sD] = summoner_dark_damage($uid, ['id' => 'rat'], 1000);
ck80('暗伤%提高召唤物伤害', $hD > $hE, "hD=$hD hE=$hE");

// 5) 光怪(克制)伤害 > 暗怪(同属性)伤害
[$hLight, $sLight] = summoner_dark_damage(0, ['id' => 'golden_slime'], 1000); // 光
[$hDark, $sDark] = summoner_dark_damage(0, ['id' => 'mimic_chest'], 1000);     // 暗
ck80('暗打光带克制后缀', str_contains($sLight, '克制'), $sLight);
ck80('暗打光比暗打暗更疼', $hLight > $hDark, "light=$hLight dark=$hDark");

// 6) 集成：summon_turn 战报里的召唤物伤害为暗属性且走元素管线
$_SESSION['uid'] = $uid;
$_SESSION['battle'] = ['id' => 'golden_slime', 'name' => '黄金史莱姆', 'hp' => 9999999, 'maxhp' => 9999999, 'atk' => 1, 'def' => 0, 'mdef' => 0];
$imp = summon_spawn('imp', player_matk(user_by_id($uid)), (int) user_by_id($uid)['maxhp'], user_by_id($uid));
summon_save([['kind' => 'imp', 'name' => '深渊小鬼', 'hp' => 9999, 'maxhp' => 9999, 'atk' => $imp['atk'], 'bomb' => true]]);
$st = summon_turn($_SESSION['battle']);
ck80('summon_turn 造成伤害', ($st['dmg'] ?? 0) > 0, (string) ($st['dmg'] ?? 0));
ck80('战报为暗影伤害', str_contains((string) $st['log'], '暗影伤害'), (string) $st['log']);
ck80('暗打光战报体现克制', str_contains((string) $st['log'], '克制'), (string) $st['log']);

db()->prepare('DELETE FROM skills WHERE uid=?')->execute([$uid]);
db()->prepare('DELETE FROM equips WHERE uid=?')->execute([$uid]);
db()->prepare('DELETE FROM users WHERE id=?')->execute([$uid]);
db()->prepare('DELETE FROM mats WHERE uid=?')->execute([$uid]);
echo "OK=$ok FAIL=$bad\n";
