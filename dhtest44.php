<?php
define('DH_TEST_MODE', true);
require 'includes/bootstrap.php';
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
// 大区域专属副本：副本存在性 / 连通 / 计时 / BOSS传说 / 套装掉落与生效
$ok=0;$bad=0;
function ck44($l,$c,$d=''){global $ok,$bad;if($c){$ok++;}else{$bad++;echo "FAIL $l $d\n";}}

$locs = locations();
$specs = region_dungeon_specs();
ck44('副本大区数=9', count($specs)===9, (string)count($specs));

// ---------- [1] 每个大区至少一个副本且连通/有BOSS/有等级门槛 ----------
foreach ($specs as $rk => $R) {
    $gate = 'rd_'.$rk.'_gate';
    ck44("[$rk] 入口存在", isset($locs[$gate]), $gate);
    ck44("[$rk] 入口与野外点双向连通", isset($locs[$R['gate']]['exits'][$gate]) && isset($locs[$gate]['exits'][$R['gate']]));
    $ids = region_dungeon_build()['maps'][$rk];
    $okChain = true;
    foreach ($ids as $id) { if (!isset($locs[$id])) { $okChain=false; break; } }
    ck44("[$rk] 内部地图完整", $okChain, count($ids).'张');
    $bmap=''; foreach ($ids as $id){ if (isset(region_dungeon_boss_map()[$id])) { $bmap=$id; break; } }
    ck44("[$rk] 有BOSS房", $bmap !== '', $bmap);
    ck44("[$rk] 入口有等级限制", region_dungeon_min_lv($gate) === (int)$R['min_lv'], 'min='.$R['min_lv']);
    // 入口→内部→BOSS房 单链可达
    $reach = true; for ($i=0;$i<count($ids)-1;$i++){ if(!isset($locs[$ids[$i]]['exits'][$ids[$i+1]])){ $reach=false; break; } }
    ck44("[$rk] 内部单向链通", $reach);
}

// ---------- [2] 怪全在系统里 ----------
$mon = monsters();
$miss=false;
foreach (region_dungeon_monsters() as $mid=>$m) { if(!isset($mon[$mid])) $miss=true; if(monster_lv($mid)<1) $miss=true; if(monster_element($mid)==='')  {} }
ck44('所有副本怪都在monsters()且有等级', !$miss);

// ---------- [3] BOSS掉传说 + 传说名唯一 + 都有独特故事与特效 ----------
$legendNames=[]; $badBoss=[];
foreach (region_dungeon_bosses() as $bmid) {
    $d = monster_drops($bmid);
    if (!isset($d['legendary']['base'])) { $badBoss[]=$bmid; continue; }
    if (!is_boss_mid($bmid)) { $badBoss[]=$bmid.'(非BOSS)'; }
    $legendNames[] = (string)$d['legendary']['base'];
    // 传说必须有 lg_bl_* 特效 key
    if (boss_legendary_key_by_base((string)$d['legendary']['base'])==='') { $badBoss[]=$d['legendary']['base'].'(无特效)'; }
}
ck44('所有副本BOSS掉专属传说且判定为BOSS+有特效', $badBoss===[], implode(',',$badBoss));
ck44('副本传说名互不重复', count($legendNames)===count(array_unique($legendNames)));
$DEFAULT='深渊里捞出来的东西，每一件都记得主人的名字。<br>它现在记得你的了。';
$noStory=[]; foreach($legendNames as $n){ if(trim(legend_lore($n))===$DEFAULT) $noStory[]=$n; }
ck44('副本传说都有独特故事', $noStory===[], implode(',',$noStory));

// ---------- [4] 套装：件数/识别/集齐生效 ----------
$setOk=true;
foreach ($specs as $rk=>$R) {
    $prefix=$R['set']['prefix'];
    $sid='rd_set:'.$prefix;
    ck44("[$rk] 套装可识别", region_dungeon_is_set_prefix($prefix), $prefix);
    ck44("[$rk] 套装件数=3", count(equip_set_pieces($sid))===3, (string)count(equip_set_pieces($sid)));
    foreach (equip_set_pieces($sid) as $pc) { if (region_dungeon_prefix_of($pc)!==$prefix) $setOk=false; }
}
ck44('套装件都被识别为对应副本套装', $setOk);

// 实造全套穿戴 → 集齐加成生效
$uid=(int)user_create('rd'.mt_rand(1,999999),'x','warrior','z1',true,'tmp'); $_SESSION['uid']=$uid;
$u=user_by_id($uid); $u['lv']=300;$u['str']=8000;$u['atk']=8000;$u['def']=4000;$u['maxhp']=200000;$u['hp']=200000; user_save($u); _gear_uncache($uid);
$firstKey=array_key_first($specs); $setDef=$specs[$firstKey]['set']; $pref=$setDef['prefix'];
foreach ($setDef['pieces'] as $p) { make_equip($uid, $p[0], $p[1], 3, 300); }
$st=db()->prepare("SELECT id FROM equips WHERE uid=? AND pos='' "); $st->execute([$uid]);
$ids=[]; while($r=$st->fetch()){ $ids[]=(int)$r['id']; }
foreach($ids as $id){ wear_equip($uid,$id); }
_gear_uncache($uid);
$cnt=equip_set_owned_count($uid,'rd_set:'.$pref);
ck44('穿戴后套装计数=3', $cnt===3, "cnt=$cnt pref=$pref");
$stats=gear_stats($uid);
ck44('套装集齐有减伤加成', (float)($stats['damage_reduction']??0)>=6.0, (string)($stats['damage_reduction']??0));
ck44('套装说明非空', equip_set_bonus_text('rd_set:'.$pref,$cnt)!=='');

// 造一件副本传说，确认带 lg_bl_ 词条
$bm=region_dungeon_boss_map(); $bossMid=reset($bm); $dd=monster_drops($bossMid);
db()->exec('DELETE FROM equips WHERE uid='.$uid);
$nm=make_equip($uid, $dd['slot'], $dd['legendary']['base'], 4, monster_lv($bossMid), $dd['legendary']);
ck44('副本BOSS传说可生成', $nm!=='', $nm);
$aff=json_decode((string)db()->query('SELECT affixes FROM equips WHERE uid='.$uid.' ORDER BY id DESC LIMIT 1')->fetchColumn(),true);
$hasLg=false; foreach((array)$aff as $a){ if(!empty($a['legend']) && str_starts_with((string)($a['id']??''),'lg_bl_')) { $hasLg=true; break; } }
ck44('副本传说带 lg_bl_* 特效词条', $hasLg);

db()->exec("DELETE FROM users WHERE id=$uid");db()->exec("DELETE FROM equips WHERE uid=$uid");db()->exec("DELETE FROM mats WHERE uid=$uid");

// ---------- [5] 端到端：进副本自动计时 + 超时弹回根据地 ----------
$uid2=(int)user_create('e2e'.mt_rand(1,999999),'x','warrior','z1',true,'tmp'); $_SESSION['uid']=$uid2;
$u2=user_by_id($uid2); $u2['lv']=300; user_save($u2);
$gate='rd_sky_gate';
$u2['loc']=$gate; user_save($u2);
$u3=user_by_id($uid2); dungeon_tick($u3);
$mats=mats_of($uid2);
ck44('进副本自动开始计时', isset($mats['rd_enter_'.$gate]) && (int)$mats['rd_enter_'.$gate]>0);
$inner=region_dungeon_build()['maps']['sky'][1];
mat_set($uid2,'rd_enter_'.$gate, time()-3700);
$u4=user_by_id($uid2); $u4['loc']=$inner; user_save($u4);
$u5=user_by_id($uid2); dungeon_tick($u5);
ck44('超时被弹回根据地', (string)user_by_id($uid2)['loc']==='sky_ferry', (string)user_by_id($uid2)['loc']);
db()->exec("DELETE FROM users WHERE id=$uid2");db()->exec("DELETE FROM mats WHERE uid=$uid2");db()->exec("DELETE FROM equips WHERE uid=$uid2");

echo "\nOK=$ok FAIL=$bad\n";
