<?php
define("DH_TEST_MODE", true);
require "includes/bootstrap.php";
$ok=0;$bad=0;
function ck($l,$c,$d=''){global $ok,$bad; if($c)$ok++; else{$bad++; echo "FAIL $l $d\n";}}
$uid=(int)user_create("actask".mt_rand(100000,999999),"x","warrior","z1",true,"tmp");
// peak claim without join -> error
$m=activity_task_claim($uid,'peak');
ck('未参加巅峰不能领', str_contains($m,'还没参加'), $m);
// join then claim
cflag_set($uid,'act_peak_joined',1);
$m=activity_task_claim($uid,'peak');
ck('参加后领取成功', str_contains($m,'随机宠物蛋×2'), $m);
$eggs=mats_of($uid);
$tot=0; foreach(pet_eggs() as $mid=>$e){ $tot+=(int)($eggs[$mid]??0); }
ck('发放了2个蛋', $tot>=2, "tot=$tot");
ck('标记已领', !empty(cflags($uid)['act_peak_claimed']));
// idempotent
$m2=activity_task_claim($uid,'peak');
ck('重复领取为空', $m2==='', $m2);
// guildwar
$m=activity_task_claim($uid,'guildwar');
ck('未参加公会战不能领', str_contains($m,'还没参加'), $m);
cflag_set($uid,'act_gw_joined',1);
$m=activity_task_claim($uid,'guildwar');
ck('公会战领取成功', str_contains($m,'随机宠物蛋×2'), $m);
// egg count now >=4
$eggs=mats_of($uid); $tot=0; foreach(pet_eggs() as $mid=>$e){ $tot+=(int)($eggs[$mid]??0); }
ck('累计>=4个蛋', $tot>=4, "tot=$tot");
echo "OK=$ok FAIL=$bad\n";
db()->prepare("DELETE FROM users WHERE id=?")->execute([$uid]);
db()->prepare("DELETE FROM mats WHERE uid=?")->execute([$uid]);
