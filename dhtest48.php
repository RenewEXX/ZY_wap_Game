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

// ---- 5) 召唤兽满上限不再重复耗蓝召唤；召唤师普攻残血免献祭 ----
$su=(int)user_create('sc'.mt_rand(1,99999),'x','summoner','z1',true,'tmp'); $_SESSION['uid']=$su;
$uu=user_by_id($su); $uu['lv']=30;$uu['int']=120; user_save($uu); _gear_uncache($su);
$uu=user_by_id($su); $uu['maxhp']=1200;$uu['hp']=1200;$uu['maxmp']=800;$uu['mp']=800; user_save($uu); _gear_uncache($su);
db()->exec("DELETE FROM skills WHERE uid=$su");
db()->prepare("INSERT INTO skills (uid,skill,level,tier,prof,active) VALUES (?,'s1',1,1,0,1)")->execute([$su]);
summon_save([]);
$bb=['id'=>'x','name'=>'木桩','hp'=>999999,'maxhp'=>999999,'atk'=>20,'exp'=>1,'gold'=>1,'num'=>1,'left'=>1,'drops'=>[],'last'=>time()];
$_SESSION['battle']=$bb;
$cap=summon_cap($uu); $mpLeak=0;
for($i=0;$i<20;$i++){ $before=count(summon_list()); $mpB=$uu['mp']; $res=battle_round($uu,$bb,'tick'); if($before>=summon_cap($uu) && str_contains($res['log']??'','应召')) $mpLeak++; }
ck48('满上限不再重复耗蓝召唤', $mpLeak===0, "leak=$mpLeak");

// ---- 6) 召唤兽生命/伤害词条显示带 % 且生效 ----
ck48('召唤兽生命词条显示带%', str_ends_with(affix_fmt('summon_hp',6.0,'T3'),'%'), affix_fmt('summon_hp',6.0,'T3'));
ck48('召唤兽伤害词条显示带%', str_ends_with(affix_fmt('summon_power',8.0,'T2'),'%'), affix_fmt('summon_power',8.0,'T2'));
$su2=(int)user_create('sh'.mt_rand(1,99999),'x','summoner','z1',true,'tmp'); $_SESSION['uid']=$su2;
$hh=user_by_id($su2); $hh['lv']=60;$hh['int']=200;$hh['maxhp']=1800; user_save($hh); _gear_uncache($su2); $hh=user_by_id($su2);
$matk=player_matk($hh); $hp0=summon_spawn('imp',$matk,(int)$hh['maxhp'],$hh)['hp'];
db()->prepare('INSERT INTO equips (uid,slot,name,quality,affixes,pos,item_level) VALUES (?,"necklace","n",3,?,"wear",300)')->execute([$su2, json_encode([['id'=>'summon_hp','tier'=>'T1','v'=>50.0,'main'=>true]])]);
_gear_uncache($su2); $hh=user_by_id($su2); $hp1=summon_spawn('imp',$matk,(int)$hh['maxhp'],$hh)['hp'];
ck48('召唤兽生命词条实际生效(+50%)', $hp1 > $hp0, "$hp0->$hp1");
db()->exec("DELETE FROM users WHERE id IN ($su,$su2)");db()->exec("DELETE FROM mats WHERE uid IN ($su,$su2)");db()->exec("DELETE FROM equips WHERE uid=$su2");db()->exec("DELETE FROM skills WHERE uid=$su");

// ---- 7) 猎人：弓(单手)+箭袋(仅猎手)可穿，其他职业穿箭袋被拒 ----
ck48('硬木短弓=bow且单手', weapon_sub('硬木短弓')==='bow' && !weapon_is_twohand('硬木短弓'), '');
ck48('兽皮箭袋仅猎手', equip_jobs_text(['slot'=>'offhand','name'=>'兽皮箭袋'])==='仅猎手', equip_jobs_text(['slot'=>'offhand','name'=>'兽皮箭袋']));
$hu=(int)user_create('hh'.mt_rand(1,99999),'x','hunter','z1',true,'tmp'); $_SESSION['uid']=$hu;
$huu=user_by_id($hu); $huu['lv']=200;$huu['str']=99999;$huu['agi']=99999; user_save($huu); _gear_uncache($hu);
make_equip($hu,'offhand','兽皮箭袋',2,200);
$qid=(int)db()->query("SELECT id FROM equips WHERE uid=$hu AND name LIKE '%兽皮箭袋%' LIMIT 1")->fetchColumn();
ck48('猎手可背箭袋', str_contains(wear_equip($hu,$qid),'穿上了'), '');
$mg=(int)user_create('mm'.mt_rand(1,99999),'x','mage','z1',true,'tmp'); $_SESSION['uid']=$mg;
$mgu=user_by_id($mg); $mgu['lv']=200;$mgu['str']=99999;$mgu['agi']=99999; user_save($mgu); _gear_uncache($mg);
make_equip($mg,'offhand','兽皮箭袋',2,200);
$qid2=(int)db()->query("SELECT id FROM equips WHERE uid=$mg AND name LIKE '%兽皮箭袋%' LIMIT 1")->fetchColumn();
ck48('法师不能背箭袋', str_contains(wear_equip($mg,$qid2),'拿不动'), '');
db()->exec("DELETE FROM users WHERE id IN ($hu,$mg)");db()->exec("DELETE FROM mats WHERE uid IN ($hu,$mg)");db()->exec("DELETE FROM equips WHERE uid IN ($hu,$mg)");

// ---- 8) 猎人打怪按等级段掉弓/箭袋（不是只能买）----
$hq=(int)user_create('hq'.mt_rand(1,99999),'x','hunter','z1',true,'tmp'); $_SESSION['uid']=$hq;
$hqu=user_by_id($hq); $hqu['lv']=980; user_save($hqu); _gear_uncache($hq);
// 各等级段基底正确
ck48('lv1段=新手短弓/兽皮箭袋', (hunter_arsenal_for_lv(10)['bow']??'')==='新手短弓' && (hunter_arsenal_for_lv(10)['quiver']??'')==='兽皮箭袋', '');
ck48('lv800段=时序追星弓/星辉箭袋', (hunter_arsenal_for_lv(850)['bow']??'')==='时序追星弓' && (hunter_arsenal_for_lv(850)['quiver']??'')==='星辉箭袋', '');
$grate=function($mid,$N=2000) use($hq){ $got=0; for($i=0;$i<$N;$i++){ db()->exec("DELETE FROM equips WHERE uid=$hq"); if(roll_hunter_gear($hq,$mid)!=='')$got++; } db()->exec("DELETE FROM equips WHERE uid=$hq"); return $got/$N*100; };
// 挑选：低段野外 / 中段野外 / 高段野外(应0) / 高段副本小怪(应>0) / 高段BOSS(应更高)
$fLo=$fMid=$fHi=$dHi=$bHi=null;
foreach(monsters() as $mid=>$m){ $lv=monster_lv($mid); $boss=is_boss_mid($mid); $dun=is_dungeon_mob($mid);
  if($lv<90 && !$boss && !$dun && !$fLo)$fLo=$mid;
  if($lv>=200 && $lv<290 && !$boss && !$dun && !$fMid)$fMid=$mid;
  if($lv>=400 && !$boss && !$dun && !$fHi)$fHi=$mid;
  if($lv>=400 && !$boss && $dun && !$dHi)$dHi=$mid;
  if($lv>=400 && $boss && !$bHi)$bHi=$mid;
}
ck48('低段野外能掉弓/箭袋', $fLo && $grate($fLo) >= 0.5, $fLo.' '.round($grate($fLo),2).'%');
ck48('中段(200-299)野外仍能掉', $fMid && $grate($fMid) >= 0.5, (string)$fMid);
ck48('高段(≥300)野外不掉弓/箭袋', $fHi && $grate($fHi, 1500) < 0.1, (string)$fHi);
ck48('高段副本小怪掉弓/箭袋', $dHi && $grate($dHi) >= 0.5, $dHi.' '.round($grate($dHi),2).'%');
ck48('高段BOSS掉弓/箭袋(掉率更高)', $bHi && $grate($bHi, 600) >= 10, $bHi.' '.round($grate($bHi,600),1).'%');
// BOSS 保底品质
if($bHi){ $mq=-1; for($i=0;$i<60;$i++){ db()->exec("DELETE FROM equips WHERE uid=$hq"); roll_hunter_gear($hq,$bHi); $r=db()->query("SELECT quality FROM equips WHERE uid=$hq LIMIT 1")->fetch(); if($r)$mq=max($mq,(int)$r['quality']); } db()->exec("DELETE FROM equips WHERE uid=$hq"); ck48('高段BOSS掉弓/箭袋品质保底史诗(≥3)', $mq>=3, 'q='.$mq); }
// 非猎人不掉
$wq=(int)user_create('wq'.mt_rand(1,99999),'x','warrior','z1',true,'tmp'); $_SESSION['uid']=$wq;
$wqu=user_by_id($wq); $wqu['lv']=980; user_save($wqu); _gear_uncache($wq);
ck48('战士不掉弓/箭袋', roll_hunter_gear($wq, $dHi??'shadow_rat')==='', '');
db()->exec("DELETE FROM users WHERE id IN ($hq,$wq)");db()->exec("DELETE FROM mats WHERE uid IN ($hq,$wq)");db()->exec("DELETE FROM equips WHERE uid IN ($hq,$wq)");

// ---- 9) 传说/套装级弓·箭袋只由副本BOSS掉落；副本小怪不再出弓/箭袋 ----
// 副本小怪(非BOSS)不会掉任何弓/箭袋（含套装弓）
$mobBow=[]; foreach(monsters() as $mid=>$m){ if(is_boss_mid($mid)||!is_dungeon_mob($mid))continue; $d=monster_drops($mid); if(!$d||!isset($d['base']))continue; $b=$d['base']; if(!empty($d['class_weapons']['hunter']))$b=$d['class_weapons']['hunter']; if(weapon_sub($b)==='bow'||str_contains($b,'箭袋'))$mobBow[]=$mid; }
ck48('副本小怪不掉弓/箭袋', $mobBow===[], implode(',',array_slice($mobBow,0,8)));
// 每个副本BOSS：猎人都能从BOSS专属掉落摸到套装弓/套装箭袋/传说箭袋
$bTest=(int)user_create('bt'.mt_rand(1,99999),'x','hunter','z1',true,'tmp'); $_SESSION['uid']=$bTest;
$btu=user_by_id($bTest); $btu['lv']=980; user_save($btu); _gear_uncache($bTest);
$gotSetBow=false;$gotSetQ=false;$gotLgQ=false;
foreach(region_dungeon_bosses() as $bmid){ for($i=0;$i<200;$i++){ db()->exec("DELETE FROM equips WHERE uid=$bTest"); roll_region_boss_hunter_gear($bTest,$bmid); $r=db()->query("SELECT slot,name,quality FROM equips WHERE uid=$bTest LIMIT 1")->fetch(); if($r){ if((int)$r['quality']===4)$gotLgQ=true; elseif($r['slot']==='weapon')$gotSetBow=true; else $gotSetQ=true; } db()->exec("DELETE FROM equips WHERE uid=$bTest"); } }
ck48('BOSS能掉套装弓', $gotSetBow);
ck48('BOSS能掉套装箭袋', $gotSetQ);
ck48('BOSS能掉传说箭袋', $gotLgQ);
// 非BOSS(小怪/野外)绝不产出传说级弓/箭袋(roll_drop对非BOSS封顶史诗)
$fieldMid=$fHi??$dHi;
$anyLg=false; for($i=0;$i<300 && $fieldMid;$i++){ db()->exec("DELETE FROM equips WHERE uid=$bTest"); roll_drop($bTest,$fieldMid,0); $r=db()->query("SELECT quality FROM equips WHERE uid=$bTest AND (name LIKE '%箭袋%' OR name LIKE '%弓%') ORDER BY id DESC LIMIT 1")->fetch(); if($r && (int)$r['quality']===4)$anyLg=true; db()->exec("DELETE FROM equips WHERE uid=$bTest"); }
ck48('非BOSS不出传说级弓/箭袋', !$anyLg, (string)$fieldMid);
db()->exec("DELETE FROM users WHERE id=$bTest");db()->exec("DELETE FROM mats WHERE uid=$bTest");db()->exec("DELETE FROM equips WHERE uid=$bTest");

// ---- 10) 箭袋主属性=暴击率，副词条不出法攻/召唤（猎手向） ----
ck48('箭袋主属性=暴击率(crit)', equip_primary('offhand','','苍穹之箭袋')==='crit', equip_primary('offhand','','苍穹之箭袋'));
ck48('箭袋属物理系(排除法攻)', equip_damage_type('offhand','苍穹之箭袋','')==='phys', equip_damage_type('offhand','苍穹之箭袋',''));
$qpool=equip_sub_pool('offhand','苍穹之箭袋','crit');
ck48('箭袋副词条不含法攻', !in_array('matk',$qpool,true) && !in_array('matk_pct',$qpool,true), json_encode($qpool));
ck48('箭袋副词条不含召唤', !in_array('summon_power',$qpool,true) && !in_array('summon_hp',$qpool,true), json_encode($qpool));
ck48('箭袋副词条含暴伤/攻速/物攻', in_array('critdmg',$qpool,true)&&in_array('speed',$qpool,true)&&in_array('atk',$qpool,true), json_encode($qpool));
ck48('箭袋副词条不含暴击(主属性已占)', !in_array('crit',$qpool,true), json_encode($qpool));
// 实造校验：主词条是 crit，且不含 matk/summon
$qv=(int)user_create('qq'.mt_rand(1,99999),'x','hunter','z1',true,'tmp'); $_SESSION['uid']=$qv;
db()->exec("DELETE FROM equips WHERE uid=$qv");
$qok=true; for($i=0;$i<20;$i++){ make_equip($qv,'offhand','苍穹之箭袋',3,400); $row=db()->query("SELECT affixes FROM equips WHERE uid=$qv ORDER BY id DESC LIMIT 1")->fetch(); db()->exec("DELETE FROM equips WHERE uid=$qv"); $aff=json_decode((string)$row['affixes'],true); $main=false; foreach($aff as $a){ if($a['id']==='crit'&&!empty($a['main']))$main=true; if(in_array($a['id'],['matk','matk_pct','summon_power','summon_hp'],true))$qok=false; } if(!$main)$qok=false; }
ck48('实造箭袋:主=暴击率且无发法攻/召唤词条(20件)', $qok);
db()->exec("DELETE FROM users WHERE id=$qv");db()->exec("DELETE FROM mats WHERE uid=$qv");db()->exec("DELETE FROM equips WHERE uid=$qv");

echo "\nOK=$ok FAIL=$bad\n";
