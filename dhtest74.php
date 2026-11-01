<?php
define('DH_TEST_MODE', true);
require 'includes/bootstrap.php';
$ok=0;$bad=0;
function ck($l,$c,$d=''){global $ok,$bad; if($c)$ok++; else{$bad++; echo "FAIL $l $d\n";}}
$cases=[
  ['new_year','firework','title_newyear','称号·新年快乐'],
  ['spring','redpacket','title_spring','称号·年兽驱邪'],
  ['christmas','bell','title_christmas','称号·圣夜祝福'],
  ['labor','laborstar','title_labor','称号·勤劳之魂'],
];
foreach($cases as $c){
  [$fid,$mat,$titleid,$titlename]=$c;
  $uid=(int)user_create('festx'.mt_rand(100000,999999),'x','warrior','z1',true,'tmp');
  $days=festival_days($fid);
  $need=20*$days; $eggs=2*$days; $diamonds=10+($days>3?10:0)+($fid==='spring'?50:0);
  // insufficient
  add_mat($uid,$mat,$need-1);
  $m=festival_task_claim($uid,$fid);
  ck("$fid 材料不足不能领", str_contains($m,'不够'), $m);
  // enough
  add_mat($uid,$mat,1);
  $m=festival_task_claim($uid,$fid);
  ck("$fid 领取成功", str_contains($m,'任务完成'), $m);
  ck("$fid 称号", (int)db()->query('SELECT COUNT(*) FROM equips WHERE uid='.$uid.' AND slot="title" AND name="'.addslashes($titlename).'"')->fetchColumn()===1, $titlename);
  $ms=mats_of($uid); $tot=0; foreach(pet_eggs() as $mid=>$e){ $tot+=(int)($ms[$mid]??0); }
  ck("$fid 发放{$eggs}个宠物蛋", $tot>=$eggs, "tot=$tot");
  ck("$fid 魔钻+$diamonds", (int)user_by_id($uid)['diamonds']===$diamonds, 'd='.(int)user_by_id($uid)['diamonds']);
  ck("$fid 材料消耗", (int)(mats_of($uid)[$mat]??0)===0);
  $mark='fest_done_'.$fid;
  if($fid==='halloween') $mark='halloween_done';
  // use config mark
  $f=festivals()[$fid]; $mark=(string)($f['mark']??('fest_done_'.$fid));
  ck("$fid 标记今年已完成", (int)(cflags($uid)[$mark]??0)===(int)date('Y'));
  // idempotent
  $m2=festival_task_claim($uid,$fid);
  ck("$fid 重复领取为空", $m2==='', $m2);
  db()->prepare('DELETE FROM users WHERE id=?')->execute([$uid]);
  db()->prepare('DELETE FROM equips WHERE uid=?')->execute([$uid]);
  db()->prepare('DELETE FROM mats WHERE uid=?')->execute([$uid]);
}
echo "OK=$ok FAIL=$bad\n";
