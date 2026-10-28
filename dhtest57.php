<?php
define('DH_TEST_MODE', true);
require 'includes/bootstrap.php';
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
// 副本门票系统审计：区域副本门票 + 每区最终BOSS 0.5% 掉落 + NPC 门禁 + 旧副本转门票
$ok=0;$bad=0;
function ck57($l,$c,$d=''){ global $ok,$bad; if($c){$ok++;}else{$bad++;echo "FAIL $l $d\n";} }
function del57(int $uid){ db()->exec("DELETE FROM users WHERE id=$uid");db()->exec("DELETE FROM equips WHERE uid=$uid");db()->exec("DELETE FROM mats WHERE uid=$uid"); }

$uid=(int)user_create('a57'.mt_rand(1,999999),'x','warrior','z1',true,'tmp');
$u=user_by_id($uid);

// ---- 1) 门票表完整性：9 个区域副本都有门票、最终BOSS、门票名 ----
$map=region_dungeon_ticket_map();
ck57('区域副本门票=9', count($map)===9, (string)count($map));
$specs=region_dungeon_specs();
$mats=quest_mats();
foreach($specs as $rk=>$R){
    ck57("[$rk] 有门票记录", isset($map[$rk]));
    $t=$map[$rk]??null;
    if(!$t){continue;}
    ck57("[$rk] 门票材料key", ($t['mat']??'')==='rd_ticket_'.$rk, (string)($t['mat']??''));
    ck57("[$rk] 门票名=副本名+入场券", ($t['name']??'')===$R['name'].'入场券', (string)($t['name']??''));
    ck57("[$rk] 门票已登记背包名", isset($mats['rd_ticket_'.$rk]));
    ck57("[$rk] 最终BOSS存在", isset(monsters()[$t['boss']]), (string)($t['boss']??''));
    ck57("[$rk] 最终BOSS是boss", is_boss_mid((string)$t['boss']), (string)$t['boss']);
}

// ---- 2) 掉落表：每区最终BOSS + 旧副本门票猎场BOSS 都进 0.5% 池 ----
$drops=region_final_ticket_drops();
foreach($map as $rk=>$t){ ck57("掉落含[$rk]最终BOSS", isset($drops[$t['boss']])); }
$legacySources=['string_puller'=>'pz_ticket','mist_seer'=>'sen_ticket','ember_lord'=>'as_ticket','void_herald'=>'vx_ticket','stone_warden'=>'tt_ticket'];
foreach($legacySources as $b=>$tk){
    $ms=array_map(fn($p)=>$p[0], $drops[$b]??[]);
    ck57("旧票掉源含 $b -> $tk", in_array($tk,$ms,true), json_encode($ms));
}
ck57('as/vx门票已登记背包名', isset($mats['as_ticket'])&&isset($mats['vx_ticket']));

// ---- 3) 0.5% 概率（统计 4000 次，期望~20） ----
db()->exec("DELETE FROM mats WHERE uid=$uid");
$hits=0;
for($i=0;$i<4000;$i++){ if(roll_region_ticket($uid,'sky_sovereign')!=='')$hits++; }
ck57('0.5%掉率数量合理', $hits>=2 && $hits<=60, "hits=$hits");
ck57('掉落实际入包', (int)(mats_of($uid)['rd_ticket_sky']??0)=== $hits, (string)(mats_of($uid)['rd_ticket_sky']??0));
ck57('非最终BOSS不掉', roll_region_ticket($uid,'goblin')==='' );

// ---- 4) 入口门反查 ----
foreach($specs as $rk=>$R){ ck57("[$rk] gate反查rk", region_dungeon_key_of_gate('rd_'.$rk.'_gate')===$rk); }
ck57('非入口反查为空', region_dungeon_key_of_gate('sky_gale_shrine')==='');

// ---- 5) 页面接线（文件级）：NPC / 门禁 / 旧副本转门票 ----
$npc=file_get_contents(__DIR__.'/npc.php');
foreach(array_keys($specs) as $rk){ ck57("npc.php含引路人 rd_$rk", strpos($npc,"'rd_".$rk."'")!==false); }
ck57('npc.php含进入处理', strpos($npc,"choice === 'enter'")!==false);
foreach(['pz_ticket','sen_ticket','as_ticket','vx_ticket','tt_ticket'] as $tk){ ck57("旧副本改为消耗门票 $tk", strpos($npc,"'".$tk."', -1")!==false); }
$home=file_get_contents(__DIR__.'/home.php');
ck57('home.php含门禁提示', strpos($home,'引路人对话')!==false);
foreach(array_keys($specs) as $rk){ ck57("home.php列出引路人 rd_$rk", strpos($home,"'rd_".$rk."'")!==false); }

// ---- 6) 旧副本门票猎场：额外延展的新地图 + 专属BOSS ----
foreach(ticket_hunt_specs() as $k=>$s){
    ck57("[$k] 猎场地图存在", isset(locations()[$s['loc']]));
    ck57("[$k] 猎场BOSS存在", isset(monsters()[$s['boss']]));
    ck57("[$k] BOSS等级>0", monster_lv($s['boss'])>0);
    ck57("[$k] BOSS元素非空", monster_element($s['boss'])!=='');
    ck57("[$k] 猎场可从{$s['from']}走到", isset(locations()[$s['from']]['exits'][$s['loc']]));
    ck57("[$k] 门票名已登记", isset($mats[$s['ticket']]));
}
ck57('傀儡回廊沿用牵线者（不新建地图）', isset(region_final_ticket_drops()['string_puller']));

del57($uid);
echo "\nOK=$ok FAIL=$bad\n";
