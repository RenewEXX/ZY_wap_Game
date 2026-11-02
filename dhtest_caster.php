<?php
define('DH_TEST_MODE', true);
require 'includes/bootstrap.php';
require_once 'includes/legend_relics.php';

$ok = 0; $bad = 0;
function ckc(string $l, bool $c, string $d = '') { global $ok,$bad; if ($c) { $ok++; } else { $bad++; echo "FAIL $l $d\n"; } }

// 1) 法系/召唤系主手不设力量/坚毅门槛，且主词条正确
$name = '__caster_' . mt_rand(1000, 9999);
$uid = user_create($name, 'x');

// 召唤师主手：魂契器(relic) → 主词条召唤兽伤害，req 全 0
make_equip($uid, 'weapon', '深渊引魂灯', 4, 700);
$w1 = db()->query('SELECT * FROM equips WHERE uid=' . $uid . ' AND slot="weapon" ORDER BY id DESC LIMIT 1')->fetch();
$aff1 = json_decode($w1['affixes'], true) ?: [];
$main1 = ''; foreach ($aff1 as $a){ if(!empty($a['main'])) $main1=(string)$a['id']; }
ckc('relic→无力量要求', (int)$w1['req_str']===0 && (int)$w1['req_agi']===0, "str={$w1['req_str']} agi={$w1['req_agi']}");
ckc('relic→主词条召唤兽伤害', $main1==='summon_power', $main1);
ckc('relic→仅召唤师', equip_jobs_text($w1)==='仅召唤师', equip_jobs_text($w1));

// 法系主手：法杖 → 主词条法攻，req 全 0
make_equip($uid, 'weapon', '桦木法杖', 4, 700);
$w2 = db()->query('SELECT * FROM equips WHERE uid=' . $uid . ' AND slot="weapon" ORDER BY id DESC LIMIT 1')->fetch();
$aff2 = json_decode($w2['affixes'], true) ?: [];
$main2 = ''; foreach ($aff2 as $a){ if(!empty($a['main'])) $main2=(string)$a['id']; }
ckc('staff→无力量要求', (int)$w2['req_str']===0 && (int)$w2['req_agi']===0, "str={$w2['req_str']}");
ckc('staff→主词条法攻', $main2==='matk', $main2);

// 2) 传说 relic 主手「噬魂」：职业=召唤师、主词条=召唤兽伤害、无力量要求、传说词条=噬魂
make_equip($uid, 'weapon', '噬魂', 4, 700, ['extra' => ['k' => 'lg_souleat', 'v' => 1]]);
$w3 = db()->query('SELECT * FROM equips WHERE uid=' . $uid . ' AND name LIKE "%噬魂" ORDER BY id DESC LIMIT 1')->fetch();
$aff3 = json_decode($w3['affixes'], true) ?: [];
$main3=''; $leg3='';
foreach ($aff3 as $a){ if(!empty($a['main'])) $main3=(string)$a['id']; if(!empty($a['legend'])) $leg3=(string)$a['id']; }
ckc('噬魂→仅召唤师', equip_jobs_text($w3)==='仅召唤师', equip_jobs_text($w3));
ckc('噬魂→无力量要求', (int)$w3['req_str']===0 && (int)$w3['req_agi']===0, "str={$w3['req_str']}");
ckc('噬魂→主词条召唤兽伤害', $main3==='summon_power', $main3);
ckc('噬魂→传说词条lg_souleat', $leg3==='lg_souleat', $leg3);
ckc('噬魂→传说词条有噬魂主题说明', str_contains(legend_relic_intro_by_key('lg_souleat'), '噬魂'), legend_relic_intro_by_key('lg_souleat'));

// 3) 召唤师专属契匣只看等级，无力量/坚毅要求
make_equip($uid, 'offhand', '渊狱·噬魂契匣', 4, 725);
$box = db()->query('SELECT * FROM equips WHERE uid=' . $uid . ' AND slot="offhand" ORDER BY id DESC LIMIT 1')->fetch();
ckc('契匣→仅召唤师', equip_jobs_text($box)==='仅召唤师', equip_jobs_text($box));
ckc('契匣→无属性要求', (int)$box['req_str']===0 && (int)$box['req_agi']===0, "str={$box['req_str']} agi={$box['req_agi']}");
$sum = ['id'=>$uid,'lv'=>725,'str'=>0,'agi'=>0,'job'=>'summoner'];
ckc('召唤师725级可穿契匣', equip_can_wear($sum,$box)==='', equip_can_wear($sum,$box));
$war = ['id'=>$uid,'lv'=>999,'str'=>999,'agi'=>999,'job'=>'warrior'];
ckc('战士不可穿契匣', equip_can_wear($war,$box)!=='', equip_can_wear($war,$box));
ckc('正式契匣传说底名', summoner_is_box('渊狱·噬魂契匣'), 'not recognized');
ckc('700级契匣掉落底名', summoner_offhand_for_lv(725,true)==='渊狱·噬魂契匣', (string)summoner_offhand_for_lv(725,true));

// 清理
db()->prepare('DELETE FROM equips WHERE uid=?')->execute([$uid]);
db()->prepare('DELETE FROM users WHERE id=?')->execute([$uid]);
db()->prepare('DELETE FROM mats WHERE uid=?')->execute([$uid]);

echo "\nOK=$ok FAIL=$bad\n";
