<?php
define('DH_TEST_MODE', true);
require 'includes/bootstrap.php';
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
// 全装备类型：穿戴 + 强化数值变化 审计
$ok=0;$bad=0;
function ck49($l,$c,$d=''){ global $ok,$bad; if($c){$ok++;}else{$bad++;echo "FAIL $l $d\n";} }
function u49(string $job,int $lv=300): int {
  $uid=(int)user_create('e'.mt_rand(1,999999),'x',$job,'z1',true,'tmp'); $_SESSION['uid']=$uid;
  $u=user_by_id($uid); $u['lv']=$lv; $u['str']=9999;$u['agi']=9999;$u['int']=9999;$u['vit']=9999; user_save($u); _gear_uncache($uid);
  db()->exec("DELETE FROM equips WHERE uid=$uid"); db()->exec("DELETE FROM mats WHERE uid=$uid");
  return $uid;
}
function main49(int $eid): string { $aff=json_decode(db()->query("SELECT affixes FROM equips WHERE id=$eid")->fetchColumn(),true); foreach($aff as $a){ if(!empty($a['main'])) return (string)$a['id']; } return ''; }
function del49(int $uid){ db()->exec("DELETE FROM users WHERE id=$uid");db()->exec("DELETE FROM mats WHERE uid=$uid");db()->exec("DELETE FROM equips WHERE uid=$uid"); }

$cases = [
  ['weapon','测试战斧','warrior',''], ['weapon','测试法杖','mage',''], ['weapon','测试长弓','hunter',''],
  ['weapon','测试战锤','priest',''], ['weapon','测试长剑','warrior',''], ['weapon','测试魂灯','summoner',''],
  ['offhand','测试箭袋','hunter',''], ['offhand','测试盾牌','warrior','shield'], ['offhand','测试法器','mage',''],
  ['offhand','测试契匣','summoner',''],
  ['body','测试重铠','warrior',''], ['head','测试头盔','warrior',''], ['legs','测试胫甲','warrior',''],
  ['belt','测试腰带','warrior',''], ['gloves','测试护手','warrior',''], ['shoes','测试战靴','warrior',''],
  ['back','测试斗篷','warrior',''], ['ring','测试指环','warrior',''], ['necklace','测试项链','warrior',''],
];
foreach($cases as $c){
  [$slot,$base,$job,$offKind]=$c;
  $uid=u49($job);
  make_equip($uid,$slot,$base,3,300,null,$offKind);
  $eid=(int)db()->query("SELECT id FROM equips WHERE uid=$uid AND slot='$slot' ORDER BY id DESC LIMIT 1")->fetchColumn();
  ck49("$slot/$base 造得出", $eid>0);
  if(!$eid){ del49($uid); continue; }
  $res=wear_equip($uid,$eid); ck49("$slot/$base 可穿戴", str_contains($res,'穿上了'), $res);
  $mid=main49($eid);
  if($mid!==''){
    db()->prepare("UPDATE equips SET enhance_level=0 WHERE id=?")->execute([$eid]); _gear_uncache($uid); $v0=(float)gear_stats($uid)[$mid];
    db()->prepare("UPDATE equips SET enhance_level=10 WHERE id=?")->execute([$eid]); _gear_uncache($uid); $v10=(float)gear_stats($uid)[$mid];
    if($mid==='bagcap'){ ck49("$slot/$base 背包容量不随强化变化(设计)", abs($v10-$v0)<0.001, "$v0→$v10"); }
    else { ck49("$slot/$base 主属性($mid)随强化上升", $v10>$v0, "$v0→$v10"); }
  }
  del49($uid);
}

// 神魂：合成→穿戴→升级，真伤上升
$u=u49('warrior'); add_mat($u,'soul_shard',200);
soul_craft($u);
$sid=(int)db()->query("SELECT id FROM equips WHERE uid=$u AND slot='soul' ORDER BY id DESC LIMIT 1")->fetchColumn();
ck49('神魂造得出', $sid>0);
ck49('神魂可穿戴', str_contains(wear_equip($u,$sid),'穿上了'));
$s0=soul_truedmg($u); soul_upgrade($u,$sid); $s1=soul_truedmg($u);
ck49('神魂升级真伤上升', $s1>$s0, "$s0→$s1");
del49($u);

// 称号：发放→穿戴，属性上升；强化其atk/hp随等级上升
$u=u49('warrior');
$tids=title_ids(); $tid=(string)array_key_first($tids);
$b0=gear_stats($u)['atk']+gear_stats($u)['hp'];
give_title($u,$tid); $wt=wear_title($u,$tid); _gear_uncache($u);
$b1=gear_stats($u)['atk']+gear_stats($u)['hp'];
ck49('称号可穿戴', str_contains($wt,'穿上了'), $wt);
ck49('称号穿戴后属性上升', $b1>$b0, "$b0→$b1");
$teid=(int)db()->query("SELECT id FROM equips WHERE uid=$u AND slot='title' LIMIT 1")->fetchColumn();
$a0=gear_stats($u)['atk']; db()->prepare("UPDATE equips SET enhance_level=10 WHERE id=?")->execute([$teid]); _gear_uncache($u); $a10=gear_stats($u)['atk'];
ck49('称号强化后加成数值上升', $a10>$a0, "$a0→$a10");
del49($u);

// 真实强化路径抽样：对弓/箭袋各实际强化升级
$u=u49('hunter'); foreach(['enhance_t1'=>9999,'enhance_t2'=>9999,'enhance_t3'=>9999] as $m=>$n) add_mat($u,$m,$n);
make_equip($u,'weapon','测试长弓',3,300); $bid=(int)db()->query("SELECT id FROM equips WHERE uid=$u ORDER BY id DESC LIMIT 1")->fetchColumn(); wear_equip($u,$bid);
$up=false; for($i=0;$i<40;$i++){ enhance_equip($u,$bid); if((int)db()->query("SELECT enhance_level FROM equips WHERE id=$bid")->fetchColumn()>0){$up=true;break;} }
ck49('真实强化路径可对武器升级', $up);
del49($u);

echo "\nOK=$ok FAIL=$bad\n";
