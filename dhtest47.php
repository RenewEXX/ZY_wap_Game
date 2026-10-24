<?php
define('DH_TEST_MODE', true);
require 'includes/bootstrap.php';
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
// 召唤师召唤物伤害平衡回归：
// 病根：旧 atk_mult 过低（imp 0.30），召唤物每击远低于召唤师本体普攻（matk×0.85），
// 违背"强度全在召唤兽"的定位。修复后：召唤物每击应≈甚至超过本体普攻，且高阶兽明显更强。
$ok=0;$bad=0;
function ck47($l,$c,$d=''){global $ok,$bad;if($c){$ok++;}else{$bad++;echo "FAIL $l $d\n";}}

// 1) 数值表：各兽 atk_mult 是否已处于合理区间（≥本体普攻0.85 的量级附近，高阶更高）
$U=summon_units();
ck47('小鬼 atk_mult≥0.6', $U['imp']['atk_mult']>=0.60, (string)$U['imp']['atk_mult']);
ck47('恶鬼 atk_mult≥1.0', $U['wraith']['atk_mult']>=1.0, (string)$U['wraith']['atk_mult']);
ck47('守卫 atk_mult≥0.8', $U['bone']['atk_mult']>=0.8, (string)$U['bone']['atk_mult']);
ck47('憎恶 atk_mult≥1.5', $U['abom']['atk_mult']>=1.5, (string)$U['abom']['atk_mult']);
ck47('梦魇 atk_mult≥2.0', $U['mare']['atk_mult']>=2.0, (string)$U['mare']['atk_mult']);
ck47('品阶单调递增', $U['imp']['atk_mult']<$U['wraith']['atk_mult'] && $U['wraith']['atk_mult']<$U['abom']['atk_mult'] && $U['abom']['atk_mult']<$U['mare']['atk_mult']);

// 2) 实测：造一个中等级召唤师，召唤兽每击 ≥ 本体普攻的 0.7（旧版只有 ~0.35）
$uid=(int)user_create('smd'.mt_rand(1,999999),'x','summoner','z1',true,'tmp'); $_SESSION['uid']=$uid;
$u=user_by_id($uid); $u['lv']=60;$u['int']=177; user_save($u); _gear_uncache($uid);
$u=user_by_id($uid);
$matk=player_matk($u);
$ownBA=(int)($matk*0.85); // 本体平A（献祭换伤害前的系数）
$imp=summon_spawn('imp',$matk,(int)$u['maxhp'],$u);
$mare=summon_spawn('mare',$matk,(int)$u['maxhp'],$u);
ck47('小鬼每击≥本体普攻70%', $imp['atk'] >= (int)($ownBA*0.7), "imp={$imp['atk']} own={$ownBA}");
ck47('梦魇每击≥本体普攻150%', $mare['atk'] >= (int)($ownBA*1.5), "mare={$mare['atk']} own={$ownBA}");
ck47('召唤兽atk随matk缩放', $imp['atk']>0 && $mare['atk']>$imp['atk']);

// 3) 战报里召唤物造成的单次伤害 > 1（旧低等级会卡在1）
$_SESSION['battle']=['id'=>'rat','name'=>'鼠','hp'=>999999,'maxhp'=>999999,'atk'=>1,'def'=>0];
summon_save([['kind'=>'imp','name'=>'深渊小鬼','hp'=>9999,'maxhp'=>9999,'atk'=>$imp['atk'],'bomb'=>false]]);
$st=summon_turn($_SESSION['battle']);
ck47('召唤兽单次伤害明显>1', ($st['dmg'] ?? 0) > 1, (string)($st['dmg'] ?? 0));

db()->exec("DELETE FROM users WHERE id=$uid");db()->exec("DELETE FROM mats WHERE uid=$uid");db()->exec("DELETE FROM equips WHERE uid=$uid");db()->exec("DELETE FROM pets WHERE uid=$uid");
echo "\nOK=$ok FAIL=$bad\n";
