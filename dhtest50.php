<?php
define('DH_TEST_MODE', true);
require 'includes/bootstrap.php';
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
// 大区域副本（新副本）全链路审计：地图/怪/BOSS/掉落/传说/套装加成
$ok=0;$bad=0;
function ck50($l,$c,$d=''){ global $ok,$bad; if($c){$ok++;}else{$bad++;echo "FAIL $l $d\n";} }
function u50(string $job,int $lv=300): int { $uid=(int)user_create('d'.mt_rand(1,999999),'x',$job,'z1',true,'tmp'); $_SESSION['uid']=$uid; $u=user_by_id($uid);$u['lv']=$lv;$u['str']=9999;$u['agi']=9999;$u['int']=9999;$u['vit']=9999;user_save($u);_gear_uncache($uid); db()->exec("DELETE FROM equips WHERE uid=$uid");db()->exec("DELETE FROM mats WHERE uid=$uid"); return $uid; }
function del50(int $uid){ db()->exec("DELETE FROM users WHERE id=$uid");db()->exec("DELETE FROM mats WHERE uid=$uid");db()->exec("DELETE FROM equips WHERE uid=$uid"); }
function near($a,$b,$t=0.51){ return abs((float)$a-(float)$b)<=$t; }

$B = region_dungeon_build();
$L = $B['loc'];
$M = $B['mon'];

// ---- 1) 每个副本：地图串联 + 门→BOSS房可达 ----
foreach($B['maps'] as $rk => $list){
  $start=$list[0]; $bossRoom=$list[count($list)-1];
  ck50("$rk 入口层存在", isset($L[$start]));
  // 双向逐层连通
  for($i=0;$i<count($list)-1;$i++){ ck50("$rk 连通 {$list[$i]}→{$list[$i+1]}", isset($L[$list[$i]]['exits'][$list[$i+1]]) && isset($L[$list[$i+1]]['exits'][$list[$i]])); }
  // BFS 可达
  $seen=[$start=>true]; $q=[$start];
  while($q){ $x=array_shift($q); foreach(($L[$x]['exits']??[]) as $y=>$_){ if(!isset($seen[$y])){$seen[$y]=true;$q[]=$y;} } }
  ck50("$rk 从入口可达BOSS房", isset($seen[$bossRoom]));
  // BOSS 房映射到 BOSS 且 BOSS 存在
  $bmid=$B['bossMap'][$bossRoom]??'';
  ck50("$rk BOSS房映射BOSS", $bmid!=='' && in_array($bmid,$B['bosses'],true));
  ck50("$rk 门有最低等级", region_dungeon_min_lv($start)>0);
  ck50("$rk 处于副本内(region_dungeon_of)", region_dungeon_of($start)===$start);
}

// ---- 2) 副本怪物/BOSS 数值合法 + 已并入 monsters()/掉落 ----
foreach($M as $mid=>$m){
  ck50("怪 $mid 数值合法", ($m['hp']??0)>0 && ($m['atk']??0)>0 && isset($m['name']));
}
foreach($B['bosses'] as $bmid){
  ck50("BOSS $bmid 并入monsters()", isset(monsters()[$bmid]));
  $d = monster_drops($bmid);
  $lgBase = (string)($d['legendary']['base'] ?? '');
  ck50("BOSS $bmid 掉传说", $lgBase!=='' && boss_legendary_key_by_base($lgBase)!=='', $lgBase);
  $dflt='深渊里捞出来的东西，每一件都记得主人的名字。<br>它现在记得你的了。';
  ck50("BOSS传说 $lgBase 有独特故事", legend_lore($lgBase)!==$dflt);
  ck50("BOSS $bmid 掉套装件", region_dungeon_boss_set_piece($bmid)!==null);
}

// ---- 3) 副本套装结构：11件(5武器+6防具)、命名/职业/前缀正确 ----
foreach(region_dungeon_set_prefixes() as $prefix){
  $info=region_dungeon_set_info($prefix); $lay=$info['layout'];
  ck50("套装 $prefix 共11件", count($info['pieces'])===11);
  ck50("套装 $prefix 武器5/防具6", count($lay['weapons'])===5 && count($lay['armor'])===6);
  ck50("套装 $prefix 说明非空", region_dungeon_set_bonus_text($prefix)!=='');
  $jobSub=['warrior'=>'axe','mage'=>'staff','priest'=>'mace','hunter'=>'bow','summoner'=>'relic'];
  foreach($lay['weapons'] as $job=>$wn){ ck50("套装 $prefix 武器 $job($wn)", weapon_sub($wn)===$jobSub[$job]); }
  foreach($info['pieces'] as $pn){ ck50("套装 $prefix 前缀识别 $pn", region_dungeon_prefix_of($pn)===$prefix); }
}

// ---- 4) 套装集齐加成 3/5/7 档（零化单件词条，只看套装加成）----
$prefix=region_dungeon_set_prefixes()[0];
$lay=region_dungeon_set_info($prefix)['layout'];
$armor=$lay['armor']; $warWeapon=$lay['weapons']['warrior'];
$lv=300;
// 3 件
$u=u50('warrior',$lv); $slots=array_keys($armor); $ids=[];
foreach(array_slice($slots,0,3) as $sl){ make_equip($u,$sl,$armor[$sl],3,$lv); $ids[]=(int)db()->query("SELECT id FROM equips WHERE uid=$u ORDER BY id DESC LIMIT 1")->fetchColumn(); }
db()->exec("UPDATE equips SET affixes='[]' WHERE uid=$u"); foreach($ids as $id) wear_equip($u,$id); _gear_uncache($u);
$s=gear_stats($u);
ck50('套装3件: 全伤害+6', near($s['skill_damage'],6.0), (string)$s['skill_damage']);
ck50('套装3件: 真伤+等级×4', near($s['truedmg'],$lv*4,2), (string)$s['truedmg']);
del50($u);
// 5 件
$u=u50('warrior',$lv); $ids=[];
foreach(array_slice($slots,0,5) as $sl){ make_equip($u,$sl,$armor[$sl],3,$lv); $ids[]=(int)db()->query("SELECT id FROM equips WHERE uid=$u ORDER BY id DESC LIMIT 1")->fetchColumn(); }
db()->exec("UPDATE equips SET affixes='[]' WHERE uid=$u"); foreach($ids as $id) wear_equip($u,$id); _gear_uncache($u);
$s=gear_stats($u);
ck50('套装5件: 减伤+6', near($s['damage_reduction'],6.0), (string)$s['damage_reduction']);
ck50('套装5件: 吸血+2', near($s['lifesteal'],2.0), (string)$s['lifesteal']);
ck50('套装5件: 全属性+3', near($s['all_attr'],3.0), (string)$s['all_attr']);
del50($u);
// 7 件（1武器+6防具）
$u=u50('warrior',$lv); $ids=[];
make_equip($u,'weapon',$warWeapon,3,$lv); $ids[]=(int)db()->query("SELECT id FROM equips WHERE uid=$u ORDER BY id DESC LIMIT 1")->fetchColumn();
foreach($slots as $sl){ make_equip($u,$sl,$armor[$sl],3,$lv); $ids[]=(int)db()->query("SELECT id FROM equips WHERE uid=$u ORDER BY id DESC LIMIT 1")->fetchColumn(); }
db()->exec("UPDATE equips SET affixes='[]' WHERE uid=$u"); foreach($ids as $id) wear_equip($u,$id); _gear_uncache($u);
$s=gear_stats($u);
ck50("套装7件: 物攻%+8", near($s['atk_pct'],8.0), (string)$s['atk_pct']);
ck50("套装7件: 法攻%+8", near($s['matk_pct'],8.0), (string)$s['matk_pct']);
ck50("套装7件: 全属性+8", near($s['all_attr'],8.0), (string)$s['all_attr']);
ck50("套装7件: 全伤害+16", near($s['skill_damage'],16.0), (string)$s['skill_damage']);
ck50("套装7件: 减伤+14", near($s['damage_reduction'],14.0), (string)$s['damage_reduction']);
del50($u);

// ---- 5) 真实造物：BOSS传说件带特效；套装件可识别前缀；猎手BOSS掉传说箭袋 ----
$u=u50('warrior',400);
$b0=$B['bosses'][0]; $lg=(string)(monster_drops($b0)['legendary']['base'] ?? '');
$slot=(string)(monster_drops($b0)['legendary']['slot'] ?? 'weapon');
make_equip($u,$slot,$lg,4,400);
$aff=json_decode(db()->query("SELECT affixes FROM equips WHERE uid=$u ORDER BY id DESC LIMIT 1")->fetchColumn(),true);
$hasLg=false; foreach($aff as $a){ if(str_starts_with((string)($a['id']??''),'lg_')) $hasLg=true; }
ck50("BOSS传说件 $lg 带专属特效", $hasLg);
// 套装件
make_equip($u,'body',$armor[array_key_first($armor)],3,400);
$sp=(string)db()->query("SELECT name FROM equips WHERE uid=$u ORDER BY id DESC LIMIT 1")->fetchColumn();
ck50('套装件造出且识别前缀', region_dungeon_prefix_of(equip_shortname($sp))===$prefix, $sp);
del50($u);

// 猎手打区域BOSS：传说箭袋（用现有 dhtest48 已覆盖掉率，这里验制造链）
$u=u50('hunter',500); db()->exec("DELETE FROM equips WHERE uid=$u");
make_equip($u,'offhand','裂空猎魂箭袋',4,500);
$qaff=json_decode(db()->query("SELECT affixes FROM equips WHERE uid=$u ORDER BY id DESC LIMIT 1")->fetchColumn(),true);
$qlg=false; foreach($qaff as $a){ if(str_starts_with((string)($a['id']??''),'lg_')) $qlg=true; }
ck50('副本BOSS传说箭袋带特效', $qlg);
del50($u);

// ---- 6) 进入计时 + 超时弹回（dungeon_tick 流程）----
foreach($B['maps'] as $rk=>$list){
  $gate=$list[0];
  ck50("$rk 弹回点有效", isset(locations()[region_dungeon_eject($gate)]));
}
$rk=array_key_first($B['maps']); $gate=$B['maps'][$rk][0];
$u=u50('warrior',999);
$uu=user_by_id($u); $uu['loc']=$gate; user_save($uu);
$uArr=user_by_id($u); dungeon_tick($uArr);
$mats=mats_of($u);
ck50('首次进入自动开始计时', isset($mats['rd_enter_'.$gate]));
ck50('进入未超时不弹回', (user_by_id($u)['loc']??'')===$gate);
// 模拟超时45分钟
mat_set($u,'rd_enter_'.$gate, time()-2760);
$uu=user_by_id($u); $uu['loc']=$gate; user_save($uu);
$uArr=user_by_id($u); dungeon_tick($uArr);
$nowLoc=(string)(user_by_id($u)['loc']??'');
ck50('超时后被弹回根据地', $nowLoc===region_dungeon_eject($gate), $nowLoc);
ck50('弹回点不在副本内', region_dungeon_of($nowLoc)==='');
del50($u);

echo "\nOK=$ok FAIL=$bad\n";
