<?php
define('DH_TEST_MODE', true);
require 'includes/bootstrap.php';
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
// 猎人分等级段传说弓/箭袋 审计
$ok=0;$bad=0;
function ck52($l,$c,$d=''){ global $ok,$bad; if($c){$ok++;}else{$bad++;echo "FAIL $l $d\n";} }
function del52(int $uid){ db()->exec("DELETE FROM users WHERE id=$uid");db()->exec("DELETE FROM mats WHERE uid=$uid");db()->exec("DELETE FROM equips WHERE uid=$uid"); }
$DFLT='深渊里捞出来的东西，每一件都记得主人的名字。<br>它现在记得你的了。';

$legs = hunter_arsenal_legendaries();
ck52('共16件传说', count($legs)===16, (string)count($legs));

// ---- 结构：8段×(弓+箭袋)、关键字、键唯一、eff/intro/lore ----
$keys=[]; $bands=[];
foreach($legs as $c){
  $base=(string)$c['base']; $key=(string)$c['key'];
  ck52("键唯一 $key", !isset($keys[$key])); $keys[$key]=1;
  ck52("键前缀 $key", str_starts_with($key,'lg_hla_'));
  ck52("job=hunter $key", ($c['job']??'')==='hunter');
  if($c['slot']==='weapon'){ ck52("弓 $base→bow", weapon_sub($base)==='bow', weapon_sub($base)); }
  else { ck52("箭袋 $base is_quiver", is_quiver($base)); ck52("箭袋 $base 仅猎手", equip_jobs_text(['slot'=>'offhand','name'=>$base])==='仅猎手'); }
  ck52("eff非空 $key", isset($c['eff']) && is_array($c['eff']) && $c['eff']!==[]);
  ck52("intro非空 $key", trim((string)$c['intro'])!=='');
  ck52("lore独特 $base", legend_lore($base)!==$DFLT);
  $bands[$c['min'].'-'.$c['max']][]=$c;
}
ck52('8个等级段', count($bands)===8, (string)count($bands));
foreach($bands as $k=>$list){ $b=0;$q=0; foreach($list as $c){ $c['slot']==='weapon'?$b++:$q++; } ck52("段 $k 弓1箭袋1", $b===1&&$q===1, "b=$b q=$q"); }

// ---- 等级段匹配 ----
ck52('lv150→白翎套', array_column(hunter_arsenal_legendary_for_lv(150),'base')===['白翎短弓','白翎箭袋']);
ck52('lv900→猎神套', array_column(hunter_arsenal_legendary_for_lv(900),'base')===['猎神之弓','猎神箭袋']);
ck52('lv50无段', hunter_arsenal_legendary_for_lv(50)===[]);

// ---- eff索引 + intro 查询 ----
$effIdx = legend_relic_eff_index();
foreach($legs as $c){
  ck52("eff索引 {$c['key']}", isset($effIdx[$c['key']]));
  ck52("intro可查 {$c['key']}", trim(legend_relic_intro_by_key($c['key']))!=='');
}

// ---- 逐件：创建(q4+lg键)→穿戴→eff聚合生效 ----
$uid=(int)user_create('h'.mt_rand(1,999999),'x','hunter','z1',true,'tmp'); $_SESSION['uid']=$uid;
$u=user_by_id($uid); $u['lv']=999; $u['str']=8000;$u['agi']=8000;$u['int']=8000;$u['vit']=8000; user_save($u); _gear_uncache($uid);
db()->exec("DELETE FROM equips WHERE uid=$uid"); db()->exec("DELETE FROM mats WHERE uid=$uid");
foreach($legs as $c){
  db()->exec("DELETE FROM equips WHERE uid=$uid");
  $r = make_equip($uid,$c['slot'],$c['base'],4,900,['extra'=>['k'=>$c['key'],'v'=>(float)$c['param']]],'');
  $eid=(int)db()->query("SELECT id FROM equips WHERE uid=$uid ORDER BY id DESC LIMIT 1")->fetchColumn();
  ck52("造出 {$c['base']}", $eid>0, $r);
  $q=(int)db()->query("SELECT quality FROM equips WHERE id=$eid")->fetchColumn();
  ck52("{$c['base']} 品质=传说", $q===4, "q=$q");
  $aff=json_decode(db()->query("SELECT affixes FROM equips WHERE id=$eid")->fetchColumn(),true);
  $has=false; foreach($aff as $a){ if(($a['id']??'')===$c['key'] && !empty($a['legend'])) $has=true; }
  ck52("{$c['base']} 带lg词条", $has);
  $w=wear_equip($uid,$eid); ck52("{$c['base']} 可穿戴", str_contains($w,'穿上了'), $w);
  _gear_uncache($uid);
  ck52("{$c['base']} 穿戴中", isset(legend_relic_worn($uid)[$c['key']]));
  // 取eff中一个标量字段，验证聚合==该值（单件穿戴）
  $field=null;$val=null;
  foreach($c['eff'] as $fk=>$fv){ if(is_scalar($fv)){ $field=$fk; $val=(float)$fv; break; } }
  if($field!==null){ ck52("{$c['base']} eff生效($field)", abs(legend_relic_eff_sum($uid,$field)-$val)<0.001, (string)legend_relic_eff_sum($uid,$field)."≠$val"); }
}

// ---- 非猎手不能背箭袋 ----
$wuid=(int)user_create('w'.mt_rand(1,999999),'x','warrior','z1',true,'tmp'); $_SESSION['uid']=$wuid;
$wu=user_by_id($wuid); $wu['lv']=999; $wu['str']=8000;$wu['agi']=8000; user_save($wu); _gear_uncache($wuid);
db()->exec("DELETE FROM equips WHERE uid=$wuid");
make_equip($wuid,'offhand','猎神箭袋',4,900,['extra'=>['k'=>'lg_hla_q8','v'=>1]],'');
$qid=(int)db()->query("SELECT id FROM equips WHERE uid=$wuid ORDER BY id DESC LIMIT 1")->fetchColumn();
$res=wear_equip($wuid,$qid);
ck52('战士背猎神箭袋被拒', !str_contains($res,'穿上了'), $res);

// ---- 掉落函数：非猎手/非BOSS不掉；猎手打对应段BOSS可掉传说且落在该段 ----
$field=''; $boss='';
foreach(monsters() as $mid=>$m){ $lv=monster_lv($mid); if($lv>=100 && $lv<=199){ if(is_boss_mid($mid)&&$boss==='')$boss=$mid; if(!is_boss_mid($mid)&&$field==='')$field=$mid; } }
ck52('有100~199段BOSS', $boss!=='');
ck52('非猎手传说不掉', roll_hunter_legendary_arsenal($wuid, $boss)==='');
if($field!==''){ ck52('非BOSS传说不掉', roll_hunter_legendary_arsenal($uid, $field)===''); }
del52($wuid);
$bandBases = array_column(hunter_arsenal_legendary_for_lv(monster_lv($boss)),'base');
$got='';
for($i=0;$i<1500 && $got==='';$i++){ $got = roll_hunter_legendary_arsenal($uid, $boss); }
ck52('猎手能掉本段传说', $got!=='');
if($got!==''){
  $sn=equip_shortname($got);
  ck52('掉的落在本段', in_array($sn,$bandBases,true), $sn.' ∉ '.implode('/',$bandBases));
}
del52($uid);

echo "\nOK=$ok FAIL=$bad\n";
