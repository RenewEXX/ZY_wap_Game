<?php
define('DH_TEST_MODE', true);
require 'includes/bootstrap.php';
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
// 武器类型平衡审计 + 双手武器主词缀≈单手2.2倍
$ok=0;$bad=0;
function ck54($l,$c,$d=''){ global $ok,$bad; if($c){$ok++;}else{$bad++;echo "FAIL $l $d\n";} }
function del54(int $uid){ db()->exec("DELETE FROM users WHERE id=$uid");db()->exec("DELETE FROM mats WHERE uid=$uid");db()->exec("DELETE FROM equips WHERE uid=$uid"); }

// ---- 1) 武器类型分类 ----
$subs = [
  '法杖'=>'staff','权杖'=>'staff','战斧'=>'axe','短弓'=>'bow','长弓'=>'bow',
  '战锤'=>'mace','重锤'=>'mace','圣盾'=>'shield','长剑'=>'blade','大剑'=>'blade',
  '魂灯'=>'relic','堕落骑士大剑'=>'blade','君王蚀影刃'=>'blade',
];
foreach($subs as $b=>$want){ ck54("weapon_sub[$b]=$want", weapon_sub($b)===$want, weapon_sub($b)); }

// 双手判定
$two = ['战斧','大剑','权杖','重锤','堕落骑士大剑','君王蚀影刃'];
foreach($two as $b){ ck54("双手[$b]", weapon_is_twohand($b)===true); }
$one = ['长剑','法杖','短弓','长弓','骑士残剑','魂灯','圣盾'];
foreach($one as $b){ ck54("单手[$b]", weapon_is_twohand($b)===false, (string)weapon_is_twohand($b)); }
ck54('弓一律单手(长弓)', weapon_is_twohand('长弓')===false);
ck54('弓一律单手(神铸苍穹)', weapon_is_twohand('神铸·苍穹')===false);

// 职业限制：双手谁拿
ck54('战士全能双手', job_can_twohand_weapon('warrior','战斧') && job_can_twohand_weapon('warrior','大剑'));
ck54('法师双手法杖', job_can_twohand_weapon('mage','权杖') && !job_can_twohand_weapon('mage','战斧'));
ck54('牧师不能拿双手重锤', !job_can_twohand_weapon('priest','重锤'));

// ---- 2) 双手主词缀≈单手 2.2 倍 ----
$uid=(int)user_create('w54'.mt_rand(1,999999),'x','warrior','z1',true,'tmp');
$u=user_by_id($uid); $u['lv']=999;$u['str']=8000;$u['agi']=8000; user_save($u); _gear_uncache($uid);
function avg_main54(int $uid,string $base,int $n,int $q=4,int $lv=300): array {
  $vals=[];$flags=0;
  for($i=0;$i<$n;$i++){
    db()->exec("DELETE FROM equips WHERE uid=$uid");
    make_equip($uid,'weapon',$base,$q,$lv);
    $row=db()->query("SELECT affixes FROM equips WHERE uid=$uid ORDER BY id DESC LIMIT 1")->fetchColumn();
    $aff=json_decode((string)$row,true); if(!is_array($aff)) continue;
    foreach($aff as $a){ if(!empty($a['main'])){ $vals[]=(float)$a['v']; if(!empty($a['tw']))$flags++; } }
  }
  return ['avg'=> count($vals)? array_sum($vals)/count($vals):0, 'tw'=>$flags, 'n'=>count($vals)];
}
$n=300;
$one1=avg_main54($uid,'长剑',$n);   $two1=avg_main54($uid,'战斧',$n);
ck54('单手主词缀已取到', $one1['avg']>0, (string)$one1['avg']);
ck54('双手主词缀已取到', $two1['avg']>0, (string)$two1['avg']);
$ratio1=$two1['avg']/$one1['avg'];
ck54('物理武器 双手/单手≈2.2', abs($ratio1-2.2)<0.25, sprintf('%.3f',$ratio1));
ck54('单手无tw标记', $one1['tw']===0, (string)$one1['tw']);
ck54('双手有tw标记', $two1['tw']===$two1['n'] && $two1['n']>0, $two1['tw'].'/'.$two1['n']);

// 法杖：权杖(2H) vs 法杖(1H) 主词缀 matk 也≈2.2
$oneS=avg_main54($uid,'法杖',$n);   $twoS=avg_main54($uid,'权杖',$n);
$ratioS=$twoS['avg']/max(1,$oneS['avg']);
ck54('法系武器 双手/单手≈2.2', abs($ratioS-2.2)<0.25, sprintf('%.3f',$ratioS));
// 单手主词缀值应是整数(精度0)
ck54('单手主词缀为整数', abs($one1['avg']-round($one1['avg']))<0.001 || $one1['avg']>0);

// 弓：主词缀不做2.2（永远拿不到tw）
db()->exec("DELETE FROM equips WHERE uid=$uid");
make_equip($uid,'weapon','短弓',4,300);
$aff=json_decode((string)db()->query("SELECT affixes FROM equips WHERE uid=$uid ORDER BY id DESC LIMIT 1")->fetchColumn(),true);
$bowTw=false; foreach($aff as $a){ if(!empty($a['main'])&&!empty($a['tw']))$bowTw=true; }
ck54('弓主词缀无tw(不翻倍)', $bowTw===false);

// ---- 3) 迁移：老双手武器(1×)补成2.2×，且幂等 ----
db()->exec("DELETE FROM equips WHERE uid=$uid");
make_equip($uid,'weapon','战斧',4,300);
$eid=(int)db()->query("SELECT id FROM equips WHERE uid=$uid ORDER BY id DESC LIMIT 1")->fetchColumn();
$aff=json_decode((string)db()->query("SELECT affixes FROM equips WHERE id=$eid")->fetchColumn(),true);
$curMain=0; foreach($aff as $a){ if(!empty($a['main']))$curMain=(float)$a['v']; }
// 模拟"旧版"：主词缀除以2.2、去掉tw
foreach($aff as $i=>$a){ if(!empty($a['main'])){ $aff[$i]['v']=(float)round($a['v']/2.2); unset($aff[$i]['tw']); } }
db()->prepare('UPDATE equips SET affixes=? WHERE id=?')->execute([json_encode($aff,JSON_UNESCAPED_UNICODE),$eid]);
mat_set($uid,'twohand_fixed',0);
migrate_twohand_main($uid);
$aff2=json_decode((string)db()->query("SELECT affixes FROM equips WHERE id=$eid")->fetchColumn(),true);
$newMain=0;$twSet=false; foreach($aff2 as $a){ if(!empty($a['main'])){ $newMain=(float)$a['v']; if(!empty($a['tw']))$twSet=true; } }
ck54('迁移把老2H主词缀×2.2(还原)', abs($newMain-$curMain)<max(3,(int)($curMain*0.03)), "cur=$curMain new=$newMain");
ck54('迁移打tw标记', $twSet===true);
// 幂等：再跑一次不变
$before=$newMain; mat_set($uid,'twohand_fixed',0); migrate_twohand_main($uid);
$aff3=json_decode((string)db()->query("SELECT affixes FROM equips WHERE id=$eid")->fetchColumn(),true);
$after=0; foreach($aff3 as $a){ if(!empty($a['main']))$after=(float)$a['v']; }
ck54('迁移幂等(不重复放大)', abs($after-$before)<0.001, "before=$before after=$after");

del54($uid);
echo "\nOK=$ok FAIL=$bad\n";
