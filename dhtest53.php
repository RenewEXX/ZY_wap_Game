<?php
define('DH_TEST_MODE', true);
require 'includes/bootstrap.php';
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
// DOT传说(20件: 10防具+5弓+5箭袋, 300~999) + 蚀心DOT套装(11件毕业, 堕天阶) 审计
$ok=0;$bad=0;
function ck53($l,$c,$d=''){ global $ok,$bad; if($c){$ok++;}else{$bad++;echo "FAIL $l $d\n";} }
function del53(int $uid){ db()->exec("DELETE FROM users WHERE id=$uid");db()->exec("DELETE FROM mats WHERE uid=$uid");db()->exec("DELETE FROM equips WHERE uid=$uid"); }
$DFLT='深渊里捞出来的东西，每一件都记得主人的名字。<br>它现在记得你的了。';

// ============ Part 1：20 件 DOT 传说 ============
$relics = legend_relics();
$dotRelics = array_filter($relics, fn($c)=>str_starts_with((string)($c['key']??''),'lg_dot'));
ck53('20件DOT传说', count($dotRelics)===20, (string)count($dotRelics));
$bowN=0;$qvN=0; foreach($dotRelics as $c){ if($c['slot']==='weapon')$bowN++; if($c['slot']==='offhand' && ($c['key']??'')!=='lg_dot3')$qvN++; }
ck53('5把传说弓', $bowN===5, (string)$bowN);
ck53('5个传说箭袋(新增)', $qvN===5, (string)$qvN);
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
// 戴 lg_dot1 (dot6) → 1.06
make_equip($uid,'ring','蛇吻之戒',4,300,['extra'=>['k'=>'lg_dot1','v'=>1]],'');
$rid=(int)db()->query("SELECT id FROM equips WHERE uid=$uid ORDER BY id DESC LIMIT 1")->fetchColumn();
wear_equip($uid,$rid); _gear_uncache($uid);
ck53('戴蛇吻之戒 dot_mult=1.06', abs(legend_relic_dot_mult($uid)-1.06)<0.001, (string)legend_relic_dot_mult($uid));

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

// ============ Part 2：蚀心 DOT 套装（11件毕业） ============
ck53('set_id=shixing', equip_set_id('蚀心·猎弓')==='shixing');
$pcs=equip_set_pieces('shixing');
ck53('11件套', count($pcs)===11, (string)count($pcs));
$layout=[['weapon','蚀心·猎弓'],['offhand','蚀心·箭袋'],['body','蚀心·噬心袍'],['head','蚀心·瞑目兜帽'],['legs','蚀心·缚足胫'],['gloves','蚀心·引弦手'],['shoes','蚀心·疾影靴'],['belt','蚀心·束魂带'],['back','蚀心·蚀空披'],['ring','蚀心·蚀骨戒'],['necklace','蚀心·噬魂链']];
foreach($layout as [$slot,$base]){
  if($slot==='weapon') ck53("弓 $base→bow", weapon_sub($base)==='bow', weapon_sub($base));
  if($slot==='offhand') ck53("箭袋 $base", is_quiver($base) && equip_jobs_text(['slot'=>'offhand','name'=>$base])==='仅猎手');
}
// 裸装 dot_mult=1；造+穿满11件
db()->exec("DELETE FROM equips WHERE uid=$uid"); _gear_uncache($uid);
$m0=dot_set_dot_pct($uid); $hm0=hunter_dot_mult(user_by_id($uid));
ck53('无套装 dot_pct=0', $m0==0.0, (string)$m0);
foreach($layout as [$slot,$base]){ make_equip($uid,$slot,$base,4,900); }
$eids=db()->query("SELECT id FROM equips WHERE uid=$uid")->fetchAll(PDO::FETCH_COLUMN);
foreach($eids as $eid){ wear_equip($uid,(int)$eid); }
_gear_uncache($uid);
ck53('穿满11件 owned=11', equip_set_owned_count($uid,'shixing')===11, (string)equip_set_owned_count($uid,'shixing'));
ck53('11件 dot_pct=250', dot_set_dot_pct($uid)==250.0, (string)dot_set_dot_pct($uid));
$fullSetMult=hunter_dot_mult(user_by_id($uid));
ck53('11件 hunter_dot_mult≈3.50', abs($fullSetMult-3.50)<0.01, (string)$fullSetMult);
ck53('套装说明非空', trim(equip_set_bonus_text('shixing',11))!=='');
// gear_stats 面板应含套装加成(至少物攻%上升)
$gs=gear_stats($uid); ck53('gear_stats有atk_pct', isset($gs['atk_pct']));

// ==== Part 3：平衡——满套毕业必须严格强于「满散件」和「混搭」 ====
// 满散件（11件 lg_dot 最优组合，dot 和=142）
db()->exec("DELETE FROM equips WHERE uid=$uid"); _gear_uncache($uid);
$standalone=[
  ['weapon','蚀天弓','lg_dot_bow5'],['offhand','蚀天箭袋','lg_dot_q5'],
  ['body','血蟒皮甲','lg_dot5'],['head','蛇瞳兜帽','lg_dot6'],['legs','蛇行护腿','lg_dot7'],
  ['gloves','毒牙手甲','lg_dot4'],['shoes','蹑影之靴','lg_dot8'],['belt','鸩壶束带','lg_dot9'],
  ['back','冥蚀披风','lg_dot10'],['ring','蛇吻之戒','lg_dot1'],['necklace','饮鸩之链','lg_dot2'],
];
foreach($standalone as [$slot,$base,$k]){ make_equip($uid,$slot,$base,4,900,['extra'=>['k'=>$k,'v'=>1]],''); }
$eids=db()->query("SELECT id FROM equips WHERE uid=$uid")->fetchAll(PDO::FETCH_COLUMN);
foreach($eids as $eid){ wear_equip($uid,(int)$eid); }
_gear_uncache($uid);
$ssMult=hunter_dot_mult(user_by_id($uid));
ck53('满散件 dot和=142→mult≈2.42', abs($ssMult-2.42)<0.01, (string)$ssMult);
ck53('满套(3.50) > 满散件(2.42)', $fullSetMult > $ssMult+0.05, "$fullSetMult vs $ssMult");

// 混搭：7件套（放弱槽）+ 4件最强散件（weapon/offhand/belt/back）→ 应仍 < 满套
db()->exec("DELETE FROM equips WHERE uid=$uid"); _gear_uncache($uid);
$mixSet=[['ring','蚀心·蚀骨戒'],['necklace','蚀心·噬魂链'],['gloves','蚀心·引弦手'],['body','蚀心·噬心袍'],['head','蚀心·瞑目兜帽'],['legs','蚀心·缚足胫'],['shoes','蚀心·疾影靴']];
$mixStandalone=[['weapon','蚀天弓','lg_dot_bow5'],['offhand','蚀天箭袋','lg_dot_q5'],['belt','鸩壶束带','lg_dot9'],['back','冥蚀披风','lg_dot10']];
foreach($mixSet as [$slot,$base]){ make_equip($uid,$slot,$base,4,900); }
foreach($mixStandalone as [$slot,$base,$k]){ make_equip($uid,$slot,$base,4,900,['extra'=>['k'=>$k,'v'=>1]],''); }
$eids=db()->query("SELECT id FROM equips WHERE uid=$uid")->fetchAll(PDO::FETCH_COLUMN);
foreach($eids as $eid){ wear_equip($uid,(int)$eid); }
_gear_uncache($uid);
$mixMult=hunter_dot_mult(user_by_id($uid));
ck53('混搭7套+4散 mult≈3.18', abs($mixMult-3.18)<0.02, (string)$mixMult);
ck53('满套(3.50) > 混搭(3.18)', $fullSetMult > $mixMult+0.05, "$fullSetMult vs $mixMult");

// 掉落：非堕天阶/非猎手不掉；堕天阶BOSS能掉
$midOther='goblin';
ck53('非堕天阶不掉蚀心', gf_dot_set_drop($uid,$midOther)==='');
ck53('非猎手不掉蚀心', gf_dot_set_drop($wuid,'god_emperor')==='');
$gotSet='';
for($i=0;$i<80 && $gotSet==='';$i++){ $gotSet=gf_dot_set_drop($uid,'god_emperor'); }
ck53('堕天阶BOSS掉蚀心', $gotSet!=='' && str_contains($gotSet,'蚀心·'), $gotSet);
del53($uid); del53($wuid);

echo "\nOK=$ok FAIL=$bad\n";
