<?php
define('DH_TEST_MODE', true);
require 'includes/bootstrap.php';
$ok=0;$bad=0;
function ck($l,$c,$d=''){global $ok,$bad; if($c)$ok++; else{$bad++; echo "FAIL $l $d\n";}}
$uid=(int)user_create('festnew'.mt_rand(100000,999999),'x','warrior','z1',true,'tmp');
// claim without enough mat -> error
$m=festival_task_claim($uid,'national_day');
ck('红旗不够不能领', str_contains($m,'不够'), $m);
// add mats
add_mat($uid,'flag',20);
$m=festival_task_claim($uid,'national_day');
ck('国庆领取成功', str_contains($m,'国庆'), $m);
ck('称号已发', (int)db()->query('SELECT COUNT(*) FROM equips WHERE uid='.$uid.' AND slot="title" AND name="称号·金龙献瑞"')->fetchColumn()===1);
$eggs=mats_of($uid); $tot=0; foreach(pet_eggs() as $mid=>$e){ $tot+=(int)($eggs[$mid]??0); }
ck('发放2个宠物蛋', $tot>=2, "tot=$tot");
ck('魔钻+10', (int)user_by_id($uid)['diamonds']===10);
ck('红旗消耗20', (int)(mats_of($uid)['flag']??0)===0);
ck('标记今年已完成', (int)(cflags($uid)['fest_done_national_day']??0)===(int)date('Y'));
// idempotent
$m2=festival_task_claim($uid,'national_day');
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
