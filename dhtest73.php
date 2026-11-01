<?php
define('DH_TEST_MODE', true);
require 'includes/bootstrap.php';
$ok=0;$bad=0;
function ck($l,$c,$d=''){global $ok,$bad; if($c)$ok++; else{$bad++; echo "FAIL $l $d\n";}}
$uid=(int)user_create('festnew'.mt_rand(100000,999999),'x','warrior','z1',true,'tmp');
$fid='national_day';
$days=festival_days($fid);
$need=20*$days; $eggs=2*$days; $diamonds=10+($days>3?10:0)+($fid==='spring'?50:0);
// claim without enough mat -> error
$m=festival_task_claim($uid,$fid);
ck('红旗不够不能领', str_contains($m,'不够'), $m);
// add mats (need-1) -> still not enough
add_mat($uid,'flag',$need-1);
$m=festival_task_claim($uid,$fid);
ck('材料不足仍不能领', str_contains($m,'不够'), $m);
// add 1 more -> enough
add_mat($uid,'flag',1);
$m=festival_task_claim($uid,$fid);
ck('国庆领取成功', str_contains($m,'任务完成'), $m);
ck('称号已发', (int)db()->query('SELECT COUNT(*) FROM equips WHERE uid='.$uid.' AND slot="title" AND name="称号·金龙献瑞"')->fetchColumn()===1);
$eqLim = db()->query('SELECT expires_at FROM equips WHERE uid='.$uid.' AND slot="title" AND name="称号·金龙献瑞"')->fetch();
ck('节日称号限时10天', (int)($eqLim['expires_at']??0) > time() && (int)($eqLim['expires_at']??0) <= time()+10*86400+5);
$ms=mats_of($uid); $tot=0; foreach(pet_eggs() as $mid=>$e){ $tot+=(int)($ms[$mid]??0); }
ck("发放{$eggs}个宠物蛋", $tot>=$eggs, "tot=$tot");
ck("魔钻+$diamonds", (int)user_by_id($uid)['diamonds']===$diamonds, 'd='.(int)user_by_id($uid)['diamonds']);
ck("红旗消耗$need", (int)(mats_of($uid)['flag']??0)===0);
ck('标记今年已完成', (int)(cflags($uid)['fest_done_national_day']??0)===(int)date('Y'));
// idempotent
$m2=festival_task_claim($uid,$fid);
ck('重复领取为空', $m2==='', $m2);
// render activity tab
$_SESSION['uid']=$uid;
$_GET=['tab'=>'activity'];
ob_start(); include 'quest.php'; $html=ob_get_clean();
ck('活动栏渲染', strlen($html)>100 && str_contains($html,'国庆'));
echo "OK=$ok FAIL=$bad\n";
db()->prepare('DELETE FROM users WHERE id=?')->execute([$uid]);
db()->prepare('DELETE FROM equips WHERE uid=?')->execute([$uid]);
db()->prepare('DELETE FROM mats WHERE uid=?')->execute([$uid]);
