<?php
define('DH_TEST_MODE', true);
require 'includes/bootstrap.php';
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
$ok=0;$bad=0;
function ck45($l,$c,$d=''){global $ok,$bad;if($c){$ok++;}else{$bad++;echo "FAIL $l $d\n";}}

// 1) 目录：5职业初始 + 10转职分支 = 15
$cat = avatar_catalog();
ck45('头像总数=15', count($cat)===15, (string)count($cat));
$byJob = [];
foreach ($cat as $id=>$a){ $byJob[$a['job']][] = $id; }
foreach (['warrior','mage','hunter','priest','summoner'] as $j){
    ck45("职业$j有job头像", isset($byJob[$j]) && in_array("job:$j",$byJob[$j],true));
}
// 每个职业 = 1初始 + 2转职 = 3
foreach ($byJob as $j=>$ids){ ck45("职业$j共3个头像", count($ids)===3, (string)count($ids)); }
// 10个转职分支头像齐全
$rb2all = ['berserker','aegis','wintermage','wildfire','ranger','assassin','shadowbishop','holybishop','necro','plague'];
foreach ($rb2all as $r){ ck45("有转职头像rb2:$r", isset($cat['rb2:'.$r])); }

// 2) 建号：默认头像跟随职业
$uid=(int)user_create('av'.mt_rand(1,999999),'x','warrior','z1',true,'tmp'); $_SESSION['uid']=$uid;
$u=user_by_id($uid);
ck45('战士默认头像=job:warrior', avatar_of($u)==='job:warrior', avatar_of($u));
ck45('默认只有本职业解锁', avatar_unlocked_ids($u)===['job:warrior'], json_encode(avatar_unlocked_ids($u)));

// 3) 选一个未解锁头像 → 失败
$msg = avatar_set($uid,'rb2:aegis');
ck45('未解锁不能选', str_contains($msg,'解锁'), $msg);
ck45('非法头像被拒', str_contains(avatar_set($uid,'nope'),'没有'), '');

// 4) 2觉后解锁转职头像，默认头像自动跟随转职
cflag_set($uid,'rb2_job','aegis');
$u=user_by_id($uid);
ck45('2觉铁卫后默认头像跟随转职', avatar_of($u)==='rb2:aegis', avatar_of($u));
$unl = avatar_unlocked_ids($u);
ck45('解锁=本职业初始+所选转职', $unl===['job:warrior','rb2:aegis'], json_encode($unl));

// 5) 手动切换到职业头像
$msg = avatar_set($uid,'job:warrior');
ck45('可切换到已解锁头像', avatar_of(user_by_id($uid))==='job:warrior', $msg);
$msg = avatar_set($uid,'rb2:aegis');
ck45('可切到转职头像', avatar_of(user_by_id($uid))==='rb2:aegis', $msg);
// 清空恢复默认
$msg = avatar_set($uid,'');
ck45('清空恢复默认(跟随转职)', avatar_of(user_by_id($uid))==='rb2:aegis', $msg.avatar_of(user_by_id($uid)));

// 6) 渲染 html 非空且含 glyph
$html = avatar_html(user_by_id($uid),44);
ck45('头像HTML非空', $html!=='' && str_contains($html,'display:inline-flex'));
ck45('头像HTML含字形', str_contains($html, avatar_catalog()['rb2:aegis']['glyph']));

// 7) 跨职业不能选
$uid2=(int)user_create('av2'.mt_rand(1,999999),'x','mage','z1',true,'tmp');
$m2=avatar_set($uid2,'rb2:aegis');
ck45('法师不能选战士转职头像', str_contains($m2,'解锁'), $m2);

// 8) DB 列存在
$cols = db()->query('PRAGMA table_info(users)')->fetchAll();
$has=false; foreach($cols as $c){ if(($c['name']??'')==='avatar') $has=true; }
ck45('users表有avatar列', $has);

db()->exec("DELETE FROM users WHERE id IN ($uid,$uid2)");db()->exec("DELETE FROM mats WHERE uid IN ($uid,$uid2)");db()->exec("DELETE FROM equips WHERE uid IN ($uid,$uid2)");
echo "\nOK=$ok FAIL=$bad\n";
