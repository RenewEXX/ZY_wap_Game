<?php
define('DH_TEST_MODE', true);
require 'includes/bootstrap.php';
$ok=0;$bad=0;
function ck($l,$c,$d=''){global $ok,$bad; if($c)$ok++; else{$bad++; echo "FAIL $l $d\n";}}
$uid=(int)user_create('hallow'.mt_rand(100000,999999),'x','warrior','z1',true,'tmp');
// active check
ck('活动当天判定为true', halloween_active());
// no pumpkins -> error
$m=halloween_task_claim($uid);
ck('南瓜不够不能领', str_contains($m,'南瓜不够'), $m);
// add pumpkins
add_mat($uid,'pumpkin',20);
$m=halloween_task_claim($uid);
ck('领取成功', str_contains($m,'万圣节任务完成'), $m);
ck('称号已发', (int)db()->query('SELECT COUNT(*) FROM equips WHERE uid='.$uid.' AND slot="title" AND name="称号·万圣惊魂"')->fetchColumn()===1);
$eggs=mats_of($uid); $tot=0; foreach(pet_eggs() as $mid=>$e){ $tot+=(int)($eggs[$mid]??0); }
ck('发放2个宠物蛋', $tot>=2, "tot=$tot");
ck('魔钻+10', (int)user_by_id($uid)['diamonds']===10, 'd='.(int)user_by_id($uid)['diamonds']);
ck('南瓜消耗20', (int)(mats_of($uid)['pumpkin']??0)===0, 'p='.(int)(mats_of($uid)['pumpkin']??0));
ck('标记今年已完成', (int)(cflags($uid)['halloween_done']??0)===(int)date('Y'));
// idempotent
$m2=halloween_task_claim($uid);
ck('重复领取为空', $m2==='', $m2);
// roll_halloween_pumpkin drops sometimes (smoke just ensure no crash)
$pk=roll_halloween_pumpkin($uid,'goblin');
ck('roll不崩溃', is_string($pk));
echo "OK=$ok FAIL=$bad\n";
db()->prepare('DELETE FROM users WHERE id=?')->execute([$uid]);
db()->prepare('DELETE FROM equips WHERE uid=?')->execute([$uid]);
db()->prepare('DELETE FROM mats WHERE uid=?')->execute([$uid]);
