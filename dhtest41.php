<?php
// ============================================================
// dhtest41：源罪技能树「功能实测」（不是查函数存在，而是真建号、真学技能、真打木桩）
// 曾修复：盾不涨 maxhp / 速无战斗作用 / 怒守逃空 flag / 神之罪主加伤是死字段。
// 本测试用均值跑多场，规避暴击/完美一击噪声；法系判定避开战士荆棘反伤稀释。
// ============================================================
define('DH_TEST_MODE', true);
require 'includes/bootstrap.php';
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
$ok=0;$bad=0;
function ck41($l,$c,$d=''){global $ok,$bad;if($c){$ok++;}else{$bad++;echo "  X $l $d\n";}}

function mku($job='warrior'){
    $uid=(int)user_create('src'.mt_rand(100000,999999),'x',$job,'z1',true,'tmp');
    $_SESSION['uid']=$uid;
    $u=user_by_id($uid);
    $u['lv']=300;$u['str']=8000;$u['agi']=0;$u['int']=8000;$u['spi']=8000;$u['vit']=4000;
    $u['atk']=8000;$u['def']=2000;$u['maxhp']=100000;$u['hp']=100000;$u['maxmp']=999999;$u['mp']=999999;
    user_save($u); cflag_set($uid,'rs',1); mat_set($uid,'rs_point',20); _gear_uncache($uid);
    return $uid;
}
// 平均普攻输出（打高血木桩，跑N次取均值，避免暴击/完美一击噪声）
function avgOut($uid,$hpPct=100,$n=200,$mAtk=1){
    $sum=0;
    for($i=0;$i<$n;$i++){
        $u=user_by_id($uid); $u['mp']=999999;$u['hp']=(int)($u['maxhp']*$hpPct/100);user_save($u);
        $mhp=50000000;
        $b=['id'=>'rat','name'=>'木桩','hp'=>$mhp,'maxhp'=>$mhp,'atk'=>$mAtk,'def'=>1,'mdef'=>1,'agi'=>0,'dodge'=>0,
            'exp'=>0,'gold'=>0,'log'=>'','num'=>1,'left'=>1,'wexp'=>0,'wgold'=>0,'drops'=>[],'last'=>time()];
        $_SESSION['battle']=$b; battle_round($u,$b,'hit');
        $sum += $mhp-(int)($_SESSION['battle']['hp']??$mhp); $_SESSION=[];
    }
    return (int)round($sum/$n);
}
// 平均受到伤害（怪高攻，跑N次）
function avgTaken($uid,$hpPct=100,$n=40){
    $sum=0;
    for($i=0;$i<$n;$i++){
        $u=user_by_id($uid); $u['hp']=(int)($u['maxhp']*$hpPct/100);user_save($u);
        $u=user_by_id($uid); $before=(int)$u['hp'];
        $mhp=99999999;
        $b=['id'=>'rat','name'=>'木桩','hp'=>$mhp,'maxhp'=>$mhp,'atk'=>14000,'def'=>1,'mdef'=>1,'agi'=>0,'dodge'=>0,
            'exp'=>0,'gold'=>0,'log'=>'','num'=>1,'left'=>1,'wexp'=>0,'wgold'=>0,'drops'=>[],'last'=>time()];
        $_SESSION['battle']=$b; battle_round($u,$b,'hit');
        $after=(int)user_by_id($uid)['hp']; $sum+=max(0,$before-$after); $_SESSION=[];
    }
    return (int)round($sum/$n);
}
function cleanup($uid){ db()->exec("DELETE FROM users WHERE id=$uid"); db()->exec("DELETE FROM mats WHERE uid=$uid"); }

echo "=========== 源罪技能树 · 修复后功能验证（均值） ===========\n\n";

// [1] 之力 攻+5%
$uid=mku();_gear_uncache($uid);$a0=player_patk(user_by_id($uid));$d0=avgOut($uid);
src_sin_learn($uid,'power');_gear_uncache($uid);$a1=player_patk(user_by_id($uid));$d1=avgOut($uid);
$g=round(($a1-$a0)/max(1,$a0)*100,2);
echo "[1] 源罪之力  物攻 {$a0}->{$a1} (+{$g}%)  普攻 {$d0}->{$d1}\n";
ck41('之力 攻+5%',abs($g-5)<0.6);ck41('之力 实战涨', $d1>$d0*1.01);
cleanup($uid);

// [2] 盾 生命+5%（真实 maxhp）
$uid=mku();$h0=(int)user_by_id($uid)['maxhp'];
src_sin_learn($uid,'shield');$h1=(int)user_by_id($uid)['maxhp'];
echo "\n[2] 源罪之盾  最大生命 {$h0}->{$h1} ( +".($h1-$h0)." )\n";
ck41('盾 maxhp +5%', $h1>$h0 && abs(($h1-$h0)/$h0*100-5)<0.6, "{$h0}->{$h1}");
cleanup($uid);

// [3] 速 速度->实战暴击
$uid=mku();_gear_uncache($uid);$sp0=(float)gear_stats($uid)['speed'];
src_sin_learn($uid,'swift');_gear_uncache($uid);$sp1=(float)gear_stats($uid)['speed'];
$src=file_get_contents(__DIR__.'/includes/game.php');
echo "\n[3] 源罪之速  speed {$sp0}->{$sp1} → 实战暴击率 +".round($sp1*0.4,2)."%\n";
ck41('速 speed 增', $sp1>$sp0);
ck41('速 被暴击管线消费', str_contains($src,"(float) \$gsV['speed'] * 0.4") && str_contains($src,"(float) \$gs['speed'] * 0.4"));
cleanup($uid);

// [4] 怒 残血攻击翻倍（用法系避战士反伤稀释）
$uid=mku('mage');
$fullNo=avgOut($uid,100); $lowNo=avgOut($uid,20);
src_sin_learn($uid,'rage');
$fullYes=avgOut($uid,100); $lowYes=avgOut($uid,20);
echo "\n[4] 源罪之怒  满血 {$fullNo}->{$fullYes}   残血20% {$lowNo}->{$lowYes}\n";
ck41('怒 残血约2x', $lowYes > $lowNo*1.7, "{$lowNo}->{$lowYes}");
ck41('怒 满血不变', abs($fullYes-$fullNo) <= max(3,(int)($fullNo*0.08)), "{$fullNo}->{$fullYes}");
cleanup($uid);

// [5] 守 残血减伤30%
$uid=mku();
$tLowNo=avgTaken($uid,20);
src_sin_learn($uid,'guard');
$tLowYes=avgTaken($uid,20);
echo "\n[5] 源罪之守  残血承伤 {$tLowNo}->{$tLowYes}\n";
ck41('守 残血↑承伤↓≈30%', $tLowYes < $tLowNo*0.82 && $tLowYes > $tLowNo*0.55, "{$tLowNo}->{$tLowYes}");
cleanup($uid);

// [6] 逃 残血闪避+30（统计命中率）
$uid=mku();
// 无逃
$miss0=0;$u=user_by_id($uid);$u['hp']=(int)($u['maxhp']*0.2);user_save($u);$u=user_by_id($uid);
for($i=0;$i<500;$i++){[$d,]=dmg_to_player($u,['id'=>'rat','atk'=>9000,'maxhp'=>9999999,'hp'=>9999999]);if($d===0)$miss0++;}
src_sin_learn($uid,'flee');
$u=user_by_id($uid);$u['hp']=(int)($u['maxhp']*0.2);user_save($u);$u=user_by_id($uid);
$miss1=0;
for($i=0;$i<500;$i++){[$d,]=dmg_to_player($u,['id'=>'rat','atk'=>9000,'maxhp'=>9999999,'hp'=>9999999]);if($d===0)$miss1++;}
echo "\n[6] 源罪之逃  残血500次挨打 闪避 {$miss0}->{$miss1} (期望明显提升)\n";
ck41('逃 残血闪避↑', $miss1 > $miss0, "{$miss0}->{$miss1}");
cleanup($uid);

// [7] 神之罪主 罪值减半 + 每点+0.5%全伤（用法系避反伤稀释）
$uidBase=mku('mage'); cflag_set($uidBase,'sin',200); _gear_uncache($uidBase); $dBase=avgOut($uidBase);
$uidLord=mku('mage'); src_sin_learn($uidLord,'lord'); cflag_set($uidLord,'sin',200); _gear_uncache($uidLord); $dLord=avgOut($uidLord);
echo "\n[7] 神之罪主  罪值200: 无主 {$dBase} -> 有主 {$dLord} (应≈2x)\n";
ck41('神之罪主 按罪值加伤≈2x', $dLord > $dBase*1.7, "{$dBase}->{$dLord}");cleanup($uidBase); cleanup($uidLord);

echo "\n=========== OK=$ok FAIL=$bad ===========\n";
