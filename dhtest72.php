<?php
define('DH_TEST_MODE', true);
require 'includes/bootstrap.php';
$ok=0;$bad=0;
function ck($l,$c,$d=''){global $ok,$bad; if($c)$ok++; else{$bad++; echo "FAIL $l $d\n";}}
$sp=pet_species();
ck('南瓜灯魔为传说', ($sp['pumpkin_jack']['quality']??'')==='legend', $sp['pumpkin_jack']['quality']??'');
$eg=pet_eggs();
ck('南瓜蛋存在且指向南瓜灯魔', ($eg['pumpkin_jack_egg']['species']??'')==='pumpkin_jack');
// pool active (today 11-01 within 10-24~11-07)
unset($GLOBALS['__halloween_pool']);
ck('今天在万圣节活动季', halloween_pool_active());
$pool=shop_egg_pool();
ck('活动季池含南瓜蛋', isset($pool['pumpkin_jack_egg']), 'keys='.implode(',',array_keys($pool)));
// force off
$GLOBALS['__halloween_pool']=false;
$pool2=shop_egg_pool();
ck('关闭时池不含南瓜蛋', !isset($pool2['pumpkin_jack_egg']));
unset($GLOBALS['__halloween_pool']);
// stats stronger than slime
$pj=pet_stats(['species'=>'pumpkin_jack','level'=>1,'star'=>0,'quality'=>'legend']);
$sl=pet_stats(['species'=>'slime','level'=>1,'star'=>0,'quality'=>'common']);
ck('南瓜灯魔攻击远高于史莱姆', (int)$pj['atk'] > (int)$sl['atk'], 'pj='.(int)$pj['atk'].' sl='.(int)$sl['atk']);
ck('南瓜灯魔可用技能', ($sp['pumpkin_jack']['active']??'')==='南瓜爆裂');
echo "OK=$ok FAIL=$bad\n";
