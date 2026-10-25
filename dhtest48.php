<?php
define('DH_TEST_MODE', true);
require 'includes/bootstrap.php';
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
// 1) 所有副本小怪掉装备率≈1%（不再随等级暴涨）
// 2) 「可装备」文案统一：单职业限定一律「仅X」，召唤师武器/契匣都一致。
$ok=0;$bad=0;
function ck48($l,$c,$d=''){global $ok,$bad;if($c){$ok++;}else{$bad++;echo "FAIL $l $d\n";}}

// ---- 1) 掉率 ----
$uid=user_create('dr'.mt_rand(1,999999),'x','warrior','z1',true,'tmp'); $_SESSION['uid']=$uid;
$u=user_by_id($uid); $u['lv']=980; user_save($u); _gear_uncache($uid);
$samples=['rd_chrono_echo','rd_divine_wraith','em_slag','pz_puller','ry_crawler','dark_slime','puppet_guard'];
foreach($samples as $mid){
    if(!isset(monsters()[$mid])){ continue; }
    db()->exec("DELETE FROM equips WHERE uid=$uid");
    $N=3000; $got=0;
    for($i=0;$i<$N;$i++){ $b=(int)db()->query("SELECT COUNT(*) FROM equips WHERE uid=$uid")->fetchColumn(); roll_drop($uid,$mid,0); $a=(int)db()->query("SELECT COUNT(*) FROM equips WHERE uid=$uid")->fetchColumn(); if($a>$b)$got++; db()->exec("DELETE FROM equips WHERE uid=$uid"); }
    $pct=$got/$N*100;
    ck48("[$mid]掉率在0.5~2.0%", $pct>=0.5 && $pct<=2.0, round($pct,2).'%');
}
db()->exec("DELETE FROM users WHERE id=$uid");db()->exec("DELETE FROM mats WHERE uid=$uid");db()->exec("DELETE FROM equips WHERE uid=$uid");

// ---- 2) 可装备文案一致性 ----
$jobsMap=['warrior'=>'战士','mage'=>'法师','hunter'=>'猎手','priest'=>'牧师','summoner'=>'召唤师'];
ck48('召唤师武器→仅召唤师', equip_jobs_text(['slot'=>'weapon','name'=>'深渊引魂灯'])==='仅召唤师', equip_jobs_text(['slot'=>'weapon','name'=>'深渊引魂灯']));
ck48('召唤师契匣→仅召唤师', equip_jobs_text(['slot'=>'offhand','name'=>'深渊契匣'])==='仅召唤师', equip_jobs_text(['slot'=>'offhand','name'=>'深渊契匣']));
ck48('战士武器→仅战士', equip_jobs_text(['slot'=>'weapon','name'=>'苍穹之斧'])==='仅战士', equip_jobs_text(['slot'=>'weapon','name'=>'苍穹之斧']));
ck48('法杖→法师/牧师', equip_jobs_text(['slot'=>'weapon','name'=>'某法杖'])==='法师/牧师', equip_jobs_text(['slot'=>'weapon','name'=>'某法杖']));
ck48('盾→战士/牧师', equip_jobs_text(['slot'=>'offhand','name'=>'铁盾'])==='战士/牧师', equip_jobs_text(['slot'=>'offhand','name'=>'铁盾']));
ck48('普通甲→全职业', str_starts_with(equip_jobs_text(['slot'=>'body','name'=>'普通甲']),'全职业'), equip_jobs_text(['slot'=>'body','name'=>'普通甲']));
// 旧函数与新函数一致
ck48('equip_job_text 与 equip_jobs_text 一致', equip_job_text(['slot'=>'offhand','name'=>'深渊契匣'])===equip_jobs_text(['slot'=>'offhand','name'=>'深渊契匣']));
// 单职业限定统一以「仅」开头
foreach([['slot'=>'weapon','name'=>'深渊引魂灯'],['slot'=>'weapon','name'=>'苍穹之斧'],['slot'=>'offhand','name'=>'深渊契匣']] as $e){
    ck48('单职业限定以「仅」开头:'.$e['name'], str_starts_with(equip_jobs_text($e),'仅'), equip_jobs_text($e));
}
echo "\nOK=$ok FAIL=$bad\n";
