<?php
define('DH_TEST_MODE', true);
require 'includes/bootstrap.php';
$ok = 0; $bad = 0;
function chk5(string $l, $got, $want) { global $ok,$bad; if ($got === $want) { $ok++; } else { $bad++; echo "FAIL $l: got=",var_export($got,true)," want=",var_export($want,true),"\n"; } }

$phys = ['斧','刀','剑','弓','弩','锤','杵','槌'];
$magic = ['杖','法器','魔杖','圣杖','提灯','法典','法球','魂灯','契杖','骨杖','灵幡','魂契'];

// 各类基底都应判对伤害路数
chk5('战斧=物理', equip_damage_type('weapon','锈蚀战斧'), 'phys');
chk5('猎弓=物理', equip_damage_type('weapon','猎弓'), 'phys');
chk5('法杖=法系', equip_damage_type('weapon','雾纹法杖'), 'magic');
chk5('魂灯=法系', equip_damage_type('weapon','残魂灯'), 'magic');
chk5('契杖=法系', equip_damage_type('weapon','烬焰·契杖'), 'magic');
chk5('骨杖=法系', equip_damage_type('weapon','骸骨之杖'), 'magic');
chk5('盾牌副手=物理', equip_damage_type('offhand','守护者之盾','shield'), 'phys');
chk5('法器副手=法系', equip_damage_type('offhand','雾纹法器','magic'), 'magic');
chk5('上衣=通用', equip_damage_type('body','雪原皮甲'), 'any');

// 法系副池不得含物攻类
$poolM = equip_sub_pool('weapon','雾纹法杖','matk');
chk5('法杖池无物攻', in_array('atk',$poolM), false);
chk5('法杖池无物攻%', in_array('atk_pct',$poolM), false);
chk5('法杖池有法攻%', in_array('matk_pct',$poolM), true);
chk5('魂灯池无物攻', in_array('atk', equip_sub_pool('weapon','残魂灯','matk')), false);

// 物理副池不得含法攻类
$poolP = equip_sub_pool('weapon','锈蚀战斧','atk');
chk5('战斧池无法攻', in_array('matk',$poolP), false);
chk5('战斧池无法攻%', in_array('matk_pct',$poolP), false);
chk5('战斧池有物攻%', in_array('atk_pct',$poolP), true);

// 副手同规则
chk5('盾牌池无法攻', in_array('matk', equip_sub_pool('offhand','守护者之盾','def','','shield')), false);
chk5('法器池无物攻', in_array('atk', equip_sub_pool('offhand','雾纹法器','element','','magic')), false);

// 防具/首饰通用，atk 与 matk 都应存在
chk5('手套有物攻', in_array('atk', equip_sub_pool('gloves','冒险手套','crit')), true);
chk5('手套有法攻', in_array('matk', equip_sub_pool('gloves','冒险手套','crit')), true);

// 主词条不得混进副池
chk5('副池不含主词条', in_array('matk', equip_sub_pool('weapon','雾纹法杖','matk')), false);
chk5('传奇词条被剔除', in_array('crit', equip_sub_pool('weapon','雾纹法杖','matk','crit')), false);

// matk_pct 已注册
$cat = affix_catalog();
chk5('matk_pct已注册', isset($cat['matk_pct']), true);
chk5('matk_pct按百分比显示', affix_fmt('matk_pct', 10.0, 'T3'), 'T3 法攻%+10%');
chk5('gear_stats认matk_pct', array_key_exists('matk_pct', gear_stats(1)), true);
chk5('matk_pct有词缀组', $cat['matk_pct']['data']['group'], 'offense_pct');

echo "OK=$ok FAIL=$bad\n";