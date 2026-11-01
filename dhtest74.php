<?php
define('DH_TEST_MODE', true);
require 'includes/bootstrap.php';
$ok=0;$bad=0;
function ck($l,$c,$d=''){global $ok,$bad; if($c)$ok++; else{$bad++; echo "FAIL $l $d\n";}}
$cases=[
  ['new_year','firework',15,'title_newyear','称号·新年快乐'],
  ['spring','redpacket',20,'title_spring','称号·年兽驱邪'],
  ['christmas','bell',15,'title_christmas','称号·圣夜祝福'],
  ['labor','laborstar',10,'title_labor','称号·勤劳之魂'],
];
foreach($cases as $c){
  [$fid,$mat,$need,$titleid,$titlename]=$c;
  $uid=(int)user_create('festx'.mt_rand(100000,999999),'x','warrior','z1',true,'tmp');
  add_mat($uid,$mat,$need);
  $m=festival_task_claim($uid,$fid);
  ck("$fid 领取成功", str_contains($m,'任务完成'), $m);
  ck("$fid 称号", (int)db()->query('SELECT COUNT(*) FROM equips WHERE uid='.$uid.' AND slot="title" AND name="'.addslashes($titlename).'"')->fetchColumn()===1, $titlename);
  ck("$fid 魔钻+10", (int)user_by_id($uid)['diamonds']===10);
  ck("$fid 材料消耗", (int)(mats_of($uid)[$mat]??0)===0);
  db()->prepare('DELETE FROM users WHERE id=?')->execute([$uid]);
  db()->prepare('DELETE FROM equips WHERE uid=?')->execute([$uid]);
  db()->prepare('DELETE FROM mats WHERE uid=?')->execute([$uid]);
}
echo "OK=$ok FAIL=$bad\n";
