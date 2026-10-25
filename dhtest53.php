<?php
define('DH_TEST_MODE', true);
require 'includes/bootstrap.php';
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
// DOT传说(10件 300~999) + 蚀心DOT套装(堕天阶) 审计
$ok=0;$bad=0;
function ck53($l,$c,$d=''){ global $ok,$bad; if($c){$ok++;}else{$bad++;echo "FAIL $l $d\n";} }
function del53(int $uid){ db()->exec("DELETE FROM users WHERE id=$uid");db()->exec("DELETE FROM mats WHERE uid=$uid");db()->exec("DELETE FROM equips WHERE uid=$uid"); }
$DFLT='深渊里捞出来的东西，每一件都记得主人的名字。<br>它现在记得你的了。';

// ============ Part 1：10 件 DOT 传说 ============
$relics = legend_relics();
$dotRelics = array_filter($relics, fn($c)=>str_starts_with((string)($c['key']??''),'lg_dot'));
ck53('10件DOT传说', count($dotRelics)===10, (string)count($dotRelics));
$lvs = array_map(fn($c)=>(int)$c['lv'], array_values($dotRelics));
ck53('覆盖300~999: 最低>=300', min($lvs)>=300, (string)min($lvs));
ck53('覆盖300~999: 最高>=900', max($lvs)>=900, (string)max($lvs));
$effIdx = legend_relic_eff_index();
$slots=[];
foreach($dotRelics as $name=>$c){
  $slots[$c['slot']]=1;
  ck53("$name dot>0", isset($c['eff']['dot']) && $c['eff']['dot']>0);
  ck53("$name job=hunter", ($c['job']??'')==='hunter');
  ck53("$name intro", trim((string)$c['intro'])!=='');
  ck53("$name eff入索引", isset($effIdx[$c['key']]));
  ck53("$name intro可查", trim(legend_relic_intro_by_key($c['key']))!=='');
  ck53("$name lore独特", legend_lore($name)!==$DFLT);
}
ck53('槽位多样(>=6种)', count($slots)>=6, (string)count($slots));

// 创建+穿戴→legend_relic_dot_mult 生效
$uid=(int)user_create('h'.mt_rand(1,999999),'x','hunter','z1',true,'tmp'); $_SESSION['uid']=$uid;
$u=user_by_id($uid); $u['lv']=999; $u['str']=8000;$u['agi']=8000;$u['int']=8000;$u['vit']=8000; user_save($u); _gear_uncache($uid);
db()->exec("DELETE FROM equips WHERE uid=$uid"); db()->exec("DELETE FROM mats WHERE uid=$uid");
// 无装时 dot_mult=1
ck53('裸装dot_mult=1', abs(legend_relic_dot_mult($uid)-1.0)<0.001, (string)legend_relic_dot_mult($uid));
// 戴 lg_dot1 (dot12) → 1.12
make_equip($uid,'ring','蚀骨之戒',4,300,['extra'=>['k'=>'lg_dot1','v'=>1]],'');
$rid=(int)db()->query("SELECT id FROM equips WHERE uid=$uid ORDER BY id DESC LIMIT 1")->fetchColumn();
wear_equip($uid,$rid); _gear_uncache($uid);
ck53('戴蚀骨之戒 dot_mult=1.12', abs(legend_relic_dot_mult($uid)-1.12)<0.001, (string)legend_relic_dot_mult($uid));

// 掉落门槛
// 找 BOSS(>=300) / BOSS(<300) / 非BOSS
$hiBoss=$loBoss=$field='';
foreach(monsters() as $mid=>$m){
  $lv=monster_lv($mid);
  if(is_boss_mid($mid)){ if($lv>=300 && $hiBoss==='')$hiBoss=$mid; if($lv<300 && $loBoss==='')$loBoss=$mid; }
  else if($lv>=1 && $field==='')$field=$mid;
}
ck53('有>=300 BOSS', $hiBoss!=='');
ck53('非BOSS不掉DOT传说', roll_dot_legendary_gear($uid,$field)==='');
db()->exec("DELETE FROM equips WHERE uid=$uid"); _gear_uncache($uid);
// 非猎手不掉
$wuid=(int)user_create('w'.mt_rand(1,999999),'x','warrior','z1',true,'tmp');
$wu=user_by_id($wuid); $wu['lv']=999; $wu['str']=8000; user_save($wu); _gear_uncache($wuid);
ck53('非猎手不掉DOT传说', roll_dot_legendary_gear($wuid,$hiBoss)==='');
if($loBoss!==''){ ck53('低级BOSS(<300)不掉', roll_dot_legendary_gear($uid,$loBoss)===''); }
// 猎手打高BOSS能掉
$got='';
for($i=0;$i<600 && $got==='';$i++){ $got=roll_dot_legendary_gear($uid,$hiBoss); }
ck53('猎手能掉DOT传说', $got!=='');
if($got!==''){
  $aff=json_decode(db()->query("SELECT affixes FROM equips WHERE uid=$uid AND quality=4 ORDER BY id DESC LIMIT 1")->fetchColumn(),true);
  $lgk=''; foreach($aff as $a){ if(!empty($a['legend']) && str_starts_with($a['id'],'lg_dot')) $lgk=$a['id']; }
  ck53('掉的是lg_dot词条', $lgk!=='', $lgk);
}

// ============ Part 2：蚀心 DOT 套装 ============
ck53('set_id=shixing', equip_set_id('蚀心·猎弓')==='shixing');
$pcs=equip_set_pieces('shixing');
ck53('7件套', count($pcs)===7, (string)count($pcs));
$layout=[['weapon','蚀心·猎弓'],['offhand','蚀心·箭袋'],['body','蚀心·血衣'],['head','蚀心·覆面'],['legs','蚀心·绑腿'],['gloves','蚀心·裹手'],['shoes','蚀心·轻履']];
foreach($layout as [$slot,$base]){
  if($slot==='weapon') ck53("弓 $base→bow", weapon_sub($base)==='bow', weapon_sub($base));
  if($slot==='offhand') ck53("箭袋 $base", is_quiver($base) && equip_jobs_text(['slot'=>'offhand','name'=>$base])==='仅猎手');
}
// 裸装 dot_mult=1；造+穿满7件
db()->exec("DELETE FROM equips WHERE uid=$uid"); _gear_uncache($uid);
$m0=dot_set_dot_pct($uid); $hm0=hunter_dot_mult(user_by_id($uid));
ck53('无套装 dot_pct=0', $m0==0.0, (string)$m0);
foreach($layout as [$slot,$base]){ make_equip($uid,$slot,$base,4,900); }
$eids=db()->query("SELECT id FROM equips WHERE uid=$uid")->fetchAll(PDO::FETCH_COLUMN);
foreach($eids as $eid){ wear_equip($uid,(int)$eid); }
_gear_uncache($uid);
ck53('穿满7件 owned=7', equip_set_owned_count($uid,'shixing')===7, (string)equip_set_owned_count($uid,'shixing'));
ck53('7件 dot_pct=30', dot_set_dot_pct($uid)==30.0, (string)dot_set_dot_pct($uid));
$hm1=hunter_dot_mult(user_by_id($uid));
ck53('7件 hunter_dot_mult≈1.30', abs($hm1-1.30)<0.01, (string)$hm1);
ck53('套装说明非空', trim(equip_set_bonus_text('shixing',7))!=='');
// gear_stats 面板应含套装加成(至少物攻%上升)
$gs=gear_stats($uid); ck53('gear_stats有atk_pct', isset($gs['atk_pct']));

// 掉落：非堕天阶/非猎手不掉；堕天阶BOSS能掉
$midOther='goblin';
ck53('非堕天阶不掉蚀心', gf_dot_set_drop($uid,$midOther)==='');
ck53('非猎手不掉蚀心', gf_dot_set_drop($wuid,'god_emperor')==='');
$gotSet='';
for($i=0;$i<80 && $gotSet==='';$i++){ $gotSet=gf_dot_set_drop($uid,'god_emperor'); }
ck53('堕天阶BOSS掉蚀心', $gotSet!=='' && str_contains($gotSet,'蚀心·'), $gotSet);
del53($uid); del53($wuid);

echo "\nOK=$ok FAIL=$bad\n";
