<?php
define('DH_TEST_MODE', true);
require 'includes/bootstrap.php';
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
// Debuff 补全审计：玩家侧控制(眩晕/沉默/减速) + 抗性 + eye_weak 接线
$ok=0;$bad=0;
function ck55($l,$c,$d=''){ global $ok,$bad; if($c){$ok++;}else{$bad++;echo "FAIL $l $d\n";} }

$uid=(int)user_create('d55'.mt_rand(1,999999),'x','warrior','z1',true,'tmp');
$u=user_by_id($uid); $u['lv']=999;$u['str']=8000;$u['agi']=8000;$u['mp']=500;$u['maxmp']=500; user_save($u); _gear_uncache($uid);

// ---- 1) 基础函数：施加/读取/递减/清除（新号控制抗性=0，不受免疫干扰） ----
$b=[];
$m1=player_debuff_apply($b,'silence',2,0,$u);
ck55('施加沉默', str_contains($m1,'沉默') && (int)($b['p_silence']??0)===2, $m1);
$m2=player_debuff_apply($b,'slow',2,20,$u);
ck55('施加减速', (int)($b['p_slow']??0)===2 && (int)($b['p_slow_pct']??0)===20, $m2);
$m3=player_debuff_apply($b,'stun',1,0,$u);
ck55('施加眩晕', (int)($b['p_stun']??0)===1, $m3);
$st=player_debuff_begin($b);
ck55('begin: stun', $st['stun']===true);
ck55('begin: silence', $st['silence']===true);
ck55('begin: slow_pct=20', abs($st['slow_pct']-20)<0.01, (string)$st['slow_pct']);
ck55('递减: stun→0', (int)($b['p_stun']??0)===0);
ck55('递减: silence 2→1', (int)($b['p_silence']??0)===1);
ck55('递减: slow 2→1', (int)($b['p_slow']??0)===1);
player_debuff_clear($b);
ck55('清除后无减益', empty($b['p_stun'])&&empty($b['p_silence'])&&empty($b['p_slow'])&&!isset($b['p_slow_pct']));

// ---- 2) 数据接线：指定 BOSS 带上新 debuff 字段 ----
$ms=monster_skills();
$has=function($mid,$name,$field) use($ms){ foreach($ms[$mid]['skills']??[] as $sk){ if(($sk['name']??'')===$name) return !empty($sk[$field]); } return false; };
ck55('使徒低语·沉默', $has('apostle','使徒低语','psilence'));
ck55('提线木偶戏·沉默', $has('string_puller','提线木偶戏','psilence'));
ck55('万丝缠身·沉默', $has('silk_mother','万丝缠身','psilence'));
ck55('回响侵蚀·沉默', $has('echo_rayne','回响侵蚀','psilence'));
ck55('赦令神罚·沉默', $has('brass_bishop','赦令神罚','psilence'));
ck55('巨像践踏·眩晕', $has('guardian','巨像践踏','pstun'));
ck55('凛冬审判·眩晕', $has('winter_king','凛冬审判','pstun'));
ck55('熔心爆裂·眩晕', $has('em_heart','熔心爆裂','pstun'));
ck55('堕天断罪·眩晕', $has('god_emperor','堕天断罪','pstun'));
ck55('维度裂隙·眩晕', $has('vx_tyrant','维度裂隙','pstun'));
ck55('暴风雪幕·减速', $has('blizzard_shaman','暴风雪幕','pslow'));
ck55('冰川崩塌·减速', $has('frost_giant','冰川崩塌','pslow'));
ck55('风雪远行·减速', $has('tundra_wanderer','风雪远行','pslow'));
ck55('雪崩倾泻·减速', $has('avalanche_titan','雪崩倾泻','pslow'));
ck55('烬风吐息·减速', $has('ash_wyrm','烬风吐息','pslow'));
ck55('万眼开阖·weak(eye_weak)', $has('abyss_eye','万眼开阖','weak'));

// ---- 3) 集成：monster_skill_turn 能给玩家挂上控制（多轮必触发） ----
$bt=['id'=>'apostle','name'=>'使徒','hp'=>100000,'maxhp'=>100000,'atk'=>10];
$gotSil=false;
for($i=0;$i<500 && !$gotSil;$i++){ monster_skill_turn($u,$bt,5); if((int)($bt['p_silence']??0)>0)$gotSil=true; }
ck55('使徒能沉默玩家(集成)', $gotSil);

$bst=['id'=>'guardian','name'=>'守卫','hp'=>100000,'maxhp'=>100000,'atk'=>10];
$gotStun=false;
for($i=0;$i<500 && !$gotStun;$i++){ monster_skill_turn($u,$bst,5); if((int)($bst['p_stun']??0)>0)$gotStun=true; }
ck55('守卫能眩晕玩家(集成)', $gotStun);

$bsl=['id'=>'blizzard_shaman','name'=>'萨满','hp'=>100000,'maxhp'=>100000,'atk'=>10];
$gotSlow=false;
for($i=0;$i<800 && !$gotSlow;$i++){ monster_skill_turn($u,$bsl,5); if((int)($bsl['p_slow']??0)>0)$gotSlow=true; }
ck55('萨满能减速玩家(集成)', $gotSlow);

// ---- 4) 集成：battle_round 尊重眩晕/沉默 ----
function mkb55(array $extra): array {
    return array_merge(['id'=>'goblin','name'=>'哥布林','hp'=>99999999,'maxhp'=>99999999,'atk'=>0,
        'exp'=>1,'gold'=>1,'log'=>'','num'=>1,'left'=>1,'wexp'=>0,'wgold'=>0,'drops'=>[],'elite'=>0,'live'=>true,'last'=>time()], $extra);
}
// 眩晕：整轮落空
$bStun=mkb55(['p_stun'=>1]);
$_SESSION['battle']=$bStun; $uS=user_by_id($uid);
$rS=battle_round($uS,$bStun,'skill'); user_save($uS);
ck55('眩晕·出手被打断', str_contains((string)$rS['log'],'出手被打断'), (string)$rS['log']);
// 沉默：日志出现"沉默"
$bSil=mkb55(['p_silence'=>1]);
$_SESSION['battle']=$bSil; $uQ=user_by_id($uid); $mpBefore=(int)$uQ['mp'];
$rQ=battle_round($uQ,$bSil,'skill'); user_save($uQ);
ck55('沉默回合日志', str_contains((string)$rQ['log'],'沉默'), (string)$rQ['log']);
ck55('沉默不耗蓝', (int)$uQ['mp']===$mpBefore, $mpBefore.'→'.(int)$uQ['mp']);

// ---- 5) eye_weak：battle_round 里会递减（接线生效） ----
$bEye=mkb55(['eye_weak'=>2]);
$_SESSION['battle']=$bEye; $uE=user_by_id($uid);
battle_round($uE,$bEye,'hit'); user_save($uE);
ck55('eye_weak 递减 2→1', (int)($bEye['eye_weak']??0)===1, (string)($bEye['eye_weak']??'unset'));

unset($_SESSION['battle']);
db()->exec("DELETE FROM users WHERE id=$uid");db()->exec("DELETE FROM equips WHERE uid=$uid");db()->exec("DELETE FROM mats WHERE uid=$uid");
echo "\nOK=$ok FAIL=$bad\n";
