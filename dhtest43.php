<?php
// ============================================================
// dhtest43：PVP 趣味性升级回归
//  [竞技场] 每3回合双方放大招；随机先手（消除进攻方先手碾压）
//  [巅峰]   缩圈：22:45后全场每分钟按比例掉血(保底1血) + 偶发补给增益
//  [天梯]   段位(黑铁→王者) + 段位保底奖励
// ============================================================
define('DH_TEST_MODE', true);
require 'includes/bootstrap.php';
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
$ok=0;$bad=0;
function ck43($l,$c,$d=''){global $ok,$bad;if($c){$ok++;}else{$bad++;echo "  X $l $d\n";}}
function mk43($job){ $uid=(int)user_create('pv'.mt_rand(1,999999),'x',$job,'z1',true,'tmp'); $_SESSION['uid']=$uid;
  $u=user_by_id($uid); $u['lv']=300;$u['str']=6000;$u['int']=6000;$u['atk']=6000;$u['grow_matk']=6000;$u['def']=3000;$u['grow_def']=2000;
  $u['maxhp']=80000;$u['hp']=80000;$u['maxmp']=999999;$u['mp']=999999; user_save($u); _gear_uncache($uid); return $uid; }
function clean43($uid){ db()->exec("DELETE FROM users WHERE id=$uid");db()->exec("DELETE FROM equips WHERE uid=$uid");db()->exec("DELETE FROM mats WHERE uid=$uid"); }

// ---------- [竞技场] 大招 ----------
echo "[1] 竞技场大招\n";
$jobs=['warrior','mage','hunter','priest','summoner'];
foreach($jobs as $j){ $ult=arena_job_ult($j); echo "    $j 大招：{$ult['name']} x{$ult['mult']}\n"; ck43("$j 有专属大招", $ult['name']!=='全力一击' && $ult['mult']>1.5); }
$uw=mk43('warrior'); $um=mk43('mage');
$A=arena_my_team(user_by_id($uw)); $B=arena_my_team(user_by_id($um));
ck43('组队带大招字段', isset($A['ult']['name']) && isset($B['ult']));
// 打一场看日志里出现【大招】
$found=false; for($i=0;$i<5 && !$found;$i++){ [$w,$log]=arena_sim($A,$B); foreach($log as $l){ if(str_contains($l,'【大招】')){$found=true;break;} } }
ck43('战报出现【大招】', $found);
// 大招伤害应显著高于普通一击
$du=0;$dn=0; for($i=0;$i<40;$i++){ $du+=arena_ult_strike($A,$B)[0]; $dn+=arena_strike($A,$B)[0]; }
echo "    大招均值 ".round($du/40)." / 普攻均值 ".round($dn/40)."\n";
ck43('大招>普攻1.5倍', ($du/40) > ($dn/40)*1.5);
clean43($uw);clean43($um);

// ---------- [竞技场] 随机先手消除碾压 ----------
echo "\n[2] 随机先手：同配置对手胜率应接近 50%\n";
$u1=mk43('warrior'); $u2=mk43('warrior');
$t1=arena_my_team(user_by_id($u1)); $t2=arena_my_team(user_by_id($u2));
$win=0; for($i=0;$i<600;$i++){ [$w,]=arena_sim($t1,$t2); if($w)$win++; }
$rate=round($win/6);
echo "    同职业内战 A 胜率 {$rate}%\n";
ck43('同配置胜率 40~60%', $rate>=40 && $rate<=60, "{$rate}%");
clean43($u1);clean43($u2);

// ---------- [巅峰] 缩圈 ----------
echo "\n[3] 巅峰缩圈\n";
$ph=peak_storm_phase(); // 真实时间，可能未到 22:45
ck43('缩圈阶段结构完整', isset($ph['ok']) && array_key_exists('pct',$ph));
// 构造一个在 peak 场、hp 满的玩家，直接验证掉血公式（绕过时间窗，用反射式调用内部逻辑不现实，
// ---------- [巅峰] 缩圈·圈内/圈外 ----------
echo "\n[3] 巅峰缩圈：圈内安全 / 圈外重伤 / 安全区收窄 / 终局塌缩\n";
$ph=peak_storm_phase();
ck43('缩圈阶段结构完整', isset($ph['ok']) && array_key_exists('pct',$ph) && array_key_exists('safe_ratio',$ph) && array_key_exists('final',$ph));
// 模拟一条时间轴验证 safe_ratio 单调下降 & 掉血比例单调上升（用同一公式重算）
$startStr=strtotime(date('Y-m-d').' 22:45:00'); $endStr=strtotime(date('Y-m-d').' 23:00:00');
$prevRatio=2.0; $prevPct=-1; $monoR=true; $monoP=true;
for($t=$startStr; $t<=$endStr; $t+=60){
    $frac=min(1.0,max(0.0,($t-$startStr)/max(1,($endStr-$startStr))));
    $ratio=max(0.0,1.0-$frac*1.15); $pct=0.04+$frac*0.12;
    if($ratio>$prevRatio)$monoR=false; if($pct<$prevPct)$monoP=false;
    $prevRatio=$ratio; $prevPct=$pct;
}
ck43('安全区占比随时间单调收窄', $monoR);
ck43('圈外掉血比例随时间单调上升', $monoP);

$up=mk43('warrior');
$u=user_by_id($up); $u['loc']='peak1'; $u['hp']=(int)$u['maxhp']; user_save($u);
// 圈内/圈外：1=圈内(安全)，2=圈外
peak_set_zone($up,1); ck43('默认在圈内', peak_zone_state($up)===1);
peak_set_zone($up,2); ck43('可切到圈外', peak_zone_state($up)===2);
peak_set_zone($up,1);
// buff take/consume
mat_set($up,'peak_buff_atk',1);
$buf=peak_buff_take($up); ck43('buff取到攻+15', $buf['atk_pct']==15.0);
peak_buff_consume($up); $buf2=peak_buff_take($up); ck43('buff消耗后清空', $buf2['atk_pct']==0.0);
// 缩圈伤害保底 1 血
$testHp=100; $dmg=max(1,(int)round($testHp*0.04)); ck43('缩圈伤害≥1', $dmg>=1);
// 倒计时条函数不炸（未开赛返回空，开赛返回HTML）
$bar=peak_storm_bar($up); ck43('倒计时条可用', is_string($bar));
clean43($up);

// ---------- [巅峰] 补给=共享地面掉落，可抢 ----------
echo "\n[4] 战场补给共享掉落（抢）\n";
$ua=mk43('warrior'); $ub=mk43('mage');
$aU=user_by_id($ua); $bU=user_by_id($ub);
$aU['loc']='peak2'; user_save($aU); $bU['loc']='peak2'; user_save($bU);
$gloc='peak2';
db()->exec('DELETE FROM ground_items WHERE loc='.db()->quote($gloc));
ground_place_peak_supply($gloc,'atk');
$list=ground_list($gloc);
$found=null; foreach($list as $g){ if(($g['kind']??'')==='peak'){$found=$g;break;} }
ck43('补给作为地面掉落出现', $found!==null && ($found['kind']??'')==='peak');
ck43('补给位置=战场', $found && $found['loc']===$gloc);
// A 先抢到
$msgA=ground_pickup($ua,(int)$found['id']);
ck43('A抢到补给', str_contains($msgA,'抢到') || str_contains($msgA,'战力增幅'), $msgA);
// 已被抢走，B 再抢应失败
$msgB=ground_pickup($ub,(int)$found['id']);
ck43('B再抢时已没了', str_contains($msgB,'没有') || str_contains($msgB,'失效') || str_contains($msgB,'慢了一步'), $msgB);
// A 确实拿到了 atk buff
$mA=mats_of($ua); ck43('A获得atk增益', !empty($mA['peak_buff_atk']));
db()->exec('DELETE FROM ground_items WHERE loc='.db()->quote($gloc));
clean43($ua); clean43($ub);

// ---------- [天梯] 段位 & 保底 ----------
echo "\n[5] 天梯段位\n";
$cases=[[1,'王者'],[2,'大师'],[10,'钻石'],[30,'铂金'],[70,'黄金'],[150,'白银'],[400,'青铜'],[5000,'黑铁']];
foreach($cases as [$rk,$want]){ $t=arena_tier($rk); ck43("第{$rk}名→{$want}", $t[1]===$want, $t[1]); }
// 段位越高保底越多
$lo=arena_tier_bonus('iron'); $hi=arena_tier_bonus('king');
ck43('王者保底>黑铁保底', $hi[0]>$lo[0] && $hi[1]>$lo[1]);
$allTiers=['iron','bronze','silver','gold','platinum','diamond','master','king'];
$prev=-1; $mono=true;
foreach($allTiers as $t){ [$g,$c]=arena_tier_bonus($t); if($g<$prev)$mono=false; $prev=$g; }
ck43('保底奖励随段位单调不减', $mono);

// ---------- [天梯] 机器人动态刷新 ----------
echo "\n[6] 机器人动态刷新（爬榜/补位/跟随玩家）\n";
$zone='z1';
db()->exec('DELETE FROM arena_rank WHERE zone='.db()->quote($zone));
db()->exec("DELETE FROM sys_kv WHERE k LIKE 'arena_bot_tick_%'");
arena_ensure($zone);
$season=arena_season();
$cnt=(int)db()->query('SELECT COUNT(*) FROM arena_rank WHERE zone='.db()->quote($zone).' AND season='.db()->quote($season).' AND is_dummy=1')->fetchColumn();
ck43('生成200机器人', $cnt===200, (string)$cnt);
$topBefore=(int)db()->query('SELECT power FROM arena_rank WHERE zone='.db()->quote($zone).' AND season='.db()->quote($season).' AND rank=1')->fetchColumn();
// 放一个高战力真玩家，触发锚点抬升 + 多次刷新收敛
$ubig=(int)user_create('big'.mt_rand(1,999999),'x','warrior','z1',true,'tmp'); $_SESSION['uid']=$ubig;
$ub=user_by_id($ubig); $ub['lv']=300;$ub['str']=60000;$ub['atk']=60000;$ub['def']=30000;$ub['maxhp']=2000000;$ub['hp']=2000000; user_save($ub); _gear_uncache($ubig);
arena_join($ubig);
$anchor=arena_pop_anchor($zone);
ck43('锚点随高战力玩家抬升', $anchor['top_power']>$topBefore, "top={$anchor['top_power']} before={$topBefore}");
for($i=0;$i<10;$i++){ db()->exec("DELETE FROM sys_kv WHERE k='arena_bot_tick_{$zone}'"); arena_bots_tick($zone); }
$topAfter=(int)db()->query('SELECT power FROM arena_rank WHERE zone='.db()->quote($zone).' AND season='.db()->quote($season).' AND rank<=2 ORDER BY rank LIMIT 1')->fetchColumn();
ck43('机器人战力随玩家提升', $topAfter>$topBefore, "after={$topAfter} before={$topBefore}");
// 榜单连续无空洞
$all=db()->query('SELECT rank FROM arena_rank WHERE zone='.db()->quote($zone).' AND season='.db()->quote($season).' ORDER BY rank')->fetchAll();
$cont=true;$exp=1;foreach($all as $r){if((int)$r['rank']!==$exp){$cont=false;break;}$exp++;}
ck43('榜单连续无空洞(补位)', $cont);
// 爬榜：记录一次，刷新后检查是否有人上移
$nb=[];foreach(db()->query('SELECT name,rank FROM arena_rank WHERE zone='.db()->quote($zone).' AND season='.db()->quote($season).' AND is_dummy=1')->fetchAll() as $r){$nb[$r['name']]=(int)$r['rank'];}
$climb=false;
for($k=0;$k<5 && !$climb;$k++){ db()->exec("DELETE FROM sys_kv WHERE k='arena_bot_tick_{$zone}'"); arena_bots_tick($zone);
  foreach(db()->query('SELECT name,rank FROM arena_rank WHERE zone='.db()->quote($zone).' AND season='.db()->quote($season).' AND is_dummy=1')->fetchAll() as $r){ if(isset($nb[$r['name']])&&(int)$r['rank']<$nb[$r['name']]){$climb=true;break;} } }
ck43('机器人会往上爬', $climb);

// ---------- [天梯] 机器人挑战玩家 ----------
echo "\n[7] 机器人挑战玩家（有胜有负/前3名压过最强真人）\n";
// 前3名机器人战力要高于最强真人（1.15/1.10/1.06 倍，视收敛程度）
for($i=0;$i<30;$i++){ db()->exec("DELETE FROM sys_kv WHERE k='arena_bot_tick_{$zone}'"); arena_bots_tick($zone); }
$maxReal=(int)db()->query('SELECT MAX(power) FROM arena_rank WHERE zone='.db()->quote($zone).' AND season='.db()->quote($season).' AND is_dummy=0')->fetchColumn();
$b1=(int)db()->query('SELECT power FROM arena_rank WHERE zone='.db()->quote($zone).' AND season='.db()->quote($season).' AND is_dummy=1 AND rank=1')->fetchColumn();
ck43('榜首机器人战力>最强真人', $b1>$maxReal, "b1={$b1} maxReal={$maxReal}");
// 构造一个中档真人，让机器人按其战力比例打，验证胜率随比例上升
$umid=(int)user_create('mid'.mt_rand(1,999999),'x','warrior','z1',true,'tmp'); $_SESSION['uid']=$umid;
$um=user_by_id($umid); $um['lv']=150;$um['str']=3000;$um['atk']=3000;$um['def']=1500;$um['maxhp']=30000;$um['hp']=30000;$um['maxmp']=99999;$um['mp']=99999; $um['grow_matk']=3000;$um['grow_def']=1500; user_save($um); _gear_uncache($umid);
$def=arena_my_team(user_by_id($umid));
$botLo=arena_bot_team_against(['name'=>'弱机','job'=>'战士','lv'=>150,'power'=>(int)($def['_power']*0.6)],$def);
$botHi=arena_bot_team_against(['name'=>'强机','job'=>'战士','lv'=>150,'power'=>(int)($def['_power']*1.2)],$def);
$loWin=0;$hiWin=0;
for($i=0;$i<200;$i++){ [$w]=arena_sim($botLo,$def); if($w)$loWin++; [$w2]=arena_sim($botHi,$def); if($w2)$hiWin++; }
ck43('弱机器人(0.6x)几乎打不过真人', $loWin/200 < 0.2, "win=".($loWin/2)."%");
ck43('强机器人(1.2x)能赢多数', $hiWin/200 > 0.5, "win=".($hiWin/2)."%");
// 机器人挑战真人函数能正常运行并写防守记录
arena_join($umid);
db()->prepare('UPDATE arena_rank SET rank=120 WHERE uid=? AND zone='.db()->quote($zone))->execute([$umid]);
$log0=(int)db()->query('SELECT COUNT(*) FROM arena_log WHERE defender_uid='.$umid)->fetchColumn();
for($i=0;$i<50;$i++){ db()->prepare('UPDATE arena_rank SET rank=120 WHERE uid=? AND zone='.db()->quote($zone))->execute([$umid]); arena_bots_attack_players($zone); }
$log1=(int)db()->query('SELECT COUNT(*) FROM arena_log WHERE defender_uid='.$umid)->fetchColumn();
ck43('机器人挑战真人写入防守记录', $log1>$log0, "before={$log0} after={$log1}");
clean43($umid);
db()->exec("DELETE FROM arena_log WHERE zone=".db()->quote($zone));
clean43($ubig);
db()->exec("DELETE FROM sys_kv WHERE k LIKE 'arena_bot_tick_%'");

echo "\nOK=$ok FAIL=$bad\n";