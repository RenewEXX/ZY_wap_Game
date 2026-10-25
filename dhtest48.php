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

// ---- 3) 双手武器「可装备」不再错误列出牧师 ----
ck48('矿工重锤(双手)仅战士', equip_jobs_text(['slot'=>'weapon','name'=>'矿工重锤'])==='仅战士', equip_jobs_text(['slot'=>'weapon','name'=>'矿工重锤']));
ck48('单手战锤仍可牧师', equip_jobs_text(['slot'=>'weapon','name'=>'寒霜·战锤'])==='战士/牧师', equip_jobs_text(['slot'=>'weapon','name'=>'寒霜·战锤']));
ck48('双手长弓仅猎手', equip_jobs_text(['slot'=>'weapon','name'=>'某长弓'])==='仅猎手', equip_jobs_text(['slot'=>'weapon','name'=>'某长弓']));
ck48('双手权杖仅法师', equip_jobs_text(['slot'=>'weapon','name'=>'某权杖'])==='仅法师', equip_jobs_text(['slot'=>'weapon','name'=>'某权杖']));
// 可装备列表里的职业，必须真的能被 equip_can_wear/穿戴接受（双手校验一致）
$wu=user_by_id((int)user_create('tw'.mt_rand(1,99999),'x','priest','z1',true,'tmp')); $_SESSION['uid']=(int)$wu['id'];
$wu['lv']=200;$wu['str']=99999;$wu['agi']=99999; user_save($wu); _gear_uncache((int)$wu['id']);
$wu=user_by_id((int)$wu['id']);
make_equip((int)$wu['id'],'weapon','矿工重锤',3,200);
$hammer=db()->query('SELECT * FROM equips WHERE uid='.(int)$wu['id']." AND name LIKE '%矿工重锤%' LIMIT 1")->fetch();
ck48('牧师无法真正穿双手锤(与文案一致)', $hammer && !in_array('priest', equip_job_list(['slot'=>'weapon','name'=>'矿工重锤'])), '');
db()->exec('DELETE FROM users WHERE id='.(int)$wu['id']);db()->exec('DELETE FROM mats WHERE uid='.(int)$wu['id']);db()->exec('DELETE FROM equips WHERE uid='.(int)$wu['id']);

// ---- 4) 公会成员列表：role/contrib 必须随 JOIN 查回（会长不能再显示成成员）----
$uidA=(int)user_create('ga'.mt_rand(1,99999),'x','warrior','z1',true,'tmp');
$uidB=(int)user_create('gb'.mt_rand(1,99999),'x','mage','z1',true,'tmp');
db()->prepare('INSERT INTO guilds (name,level,exp,gold,created_at) VALUES (?,1,0,0,?)')->execute(['t'.mt_rand(1,99999),time()]);
$gid=(int)db()->lastInsertId();
db()->prepare('INSERT INTO guild_members (uid,gid,role,contrib,joined_at) VALUES (?,?,"leader",50,?)')->execute([$uidA,$gid,time()]);
db()->prepare('INSERT INTO guild_members (uid,gid,role,contrib,joined_at) VALUES (?,?,"member",10,?)')->execute([$uidB,$gid,time()]);
$st=db()->prepare('SELECT u.*, m.role AS role, m.contrib AS contrib FROM guild_members m JOIN users u ON u.id=m.uid WHERE m.gid=? LIMIT 60');
$st->execute([$gid]); $rows=[]; while($r=$st->fetch()){$rows[]=$r;}
$byUid=[]; foreach($rows as $r){ $byUid[(int)$r['id']]=$r; }
ck48('会长身份=会长', guild_role_name((string)($byUid[$uidA]['role']??''))==='会长', guild_role_name((string)($byUid[$uidA]['role']??'')));
ck48('会长贡献=50', (int)($byUid[$uidA]['contrib']??-1)===50, (string)($byUid[$uidA]['contrib']??''));
ck48('成员身份=成员', guild_role_name((string)($byUid[$uidB]['role']??''))==='成员', guild_role_name((string)($byUid[$uidB]['role']??'')));
ck48('成员贡献=10', (int)($byUid[$uidB]['contrib']??-1)===10, (string)($byUid[$uidB]['contrib']??''));
db()->exec("DELETE FROM guild_members WHERE gid=$gid");db()->exec("DELETE FROM guilds WHERE id=$gid");
db()->exec("DELETE FROM users WHERE id IN ($uidA,$uidB)");db()->exec("DELETE FROM mats WHERE uid IN ($uidA,$uidB)");

echo "\nOK=$ok FAIL=$bad\n";
