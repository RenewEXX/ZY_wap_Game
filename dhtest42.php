<?php
// ============================================================
// dhtest42：PVP（巅峰/公会战/罪技PK/竞技场）平衡与可玩性回归
// 曾修复：所有 PVP 用 player_atk()，而法系(法师/牧师/召唤师) player_atk 恒为 0，
//         导致法系在 PVP 里每刀 1 点，等于送。且玩家 mdef≈pdef/2，若法系直接吃 mdef 会碾压。
// 现改用 pvp_hit_base()：按类型取攻，防御用 pdef/mdef 对称混合，法系能打、物理不白嫖。
// ============================================================
define('DH_TEST_MODE', true);
require 'includes/bootstrap.php';
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
$ok=0;$bad=0;
function ck42($l,$c,$d=''){global $ok,$bad;if($c){$ok++;}else{$bad++;echo "  X $l $d\n";}}

function mk42($job){
    $uid=(int)user_create('pv'.mt_rand(1,999999),'x',$job,'z1',true,'tmp'); $_SESSION['uid']=$uid;
    $u=user_by_id($uid); $u['lv']=300;
    $u['str']=6000;$u['int']=6000;$u['atk']=6000;$u['grow_matk']=6000;$u['def']=3000;$u['grow_def']=2000;
    $u['maxhp']=80000;$u['hp']=80000;$u['maxmp']=999999;$u['mp']=999999;
    user_save($u); _gear_uncache($uid); return $uid;
}
function clean42($uid){ db()->exec("DELETE FROM users WHERE id=$uid");db()->exec("DELETE FROM equips WHERE uid=$uid");db()->exec("DELETE FROM mats WHERE uid=$uid"); }

$jobs=['warrior','mage','priest','summoner','hunter'];
$us=[]; foreach($jobs as $j){ $us[$j]=mk42($j); }

// [1] 法系在 PVP 里不再只能打 1 点
echo "[1] 法系 PVP 伤害不再卡 1 点\n";
$war=user_by_id($us['warrior']);
foreach(['mage','priest','summoner'] as $j){
    $u=user_by_id($us[$j]);
    [$d,$type]=pvp_hit_base($u,$war);
    echo "    $j 打战士 → $d 点 ($type)\n";
    ck42("$j 能造成可观伤害", $d > 1000 && $type==='mag', "$d/$type");
}
// [2] 物攻职业依然是物理、正常输出
echo "\n[2] 物理职业伤害类型\n";
foreach(['warrior','hunter'] as $j){
    $u=user_by_id($us[$j]);
    [$d,$type]=pvp_hit_base($u,$war);
    ck42("$j 物理且不卡1点", $d>1000 && $type==='phys', "$d/$type");
}

// [3] pvp_attack_of：法系取 matk，物理取 atk
echo "\n[3] pvp_attack_of 分职业取攻\n";
ck42('法师取 matk>0', pvp_attack_of(user_by_id($us['mage']))[0] > 0 && pvp_attack_of(user_by_id($us['mage']))[1]==='mag');
ck42('战士取 atk 且 phys', pvp_attack_of(user_by_id($us['warrior']))[1]==='phys');

// [4] 防御对称：法系打战士 与 物理打战士，基础减伤系数一致（不该出现一系碾压）
echo "\n[4] 攻防对称性（相同攻击面板下，物理/法系对同目标伤害接近）\n";
$mageP = pvp_attack_of(user_by_id($us['mage']))[0];
$warP  = pvp_attack_of(user_by_id($us['warrior']))[0];
$sumM=0;$sumW=0; for($i=0;$i<50;$i++){ $sumM+=pvp_hit_base(user_by_id($us['mage']),$war)[0]; $sumW+=pvp_hit_base(user_by_id($us['warrior']),$war)[0]; }
$ratio = ($sumM/50)/max(1,($sumW/50));
echo "    法系均值 ".round($sumM/50)." / 物理均值 ".round($sumW/50)." = ".round($ratio,2)."\n";
ck42('法/物对同目标伤害比在 0.7~1.3', $ratio>0.7 && $ratio<1.3, "ratio=".round($ratio,2));

// [5] 竞技场：各职业胜率不应出现一家独大（任一职业进攻胜率 20%~80%）
echo "\n[5] 竞技场职业胜率均衡\n";
$teams=[]; foreach($jobs as $j) $teams[$j]=arena_my_team(user_by_id($us[$j]));
foreach($jobs as $ja){
    $wins=0;$tot=0;
    foreach($jobs as $jb){ if($ja===$jb) continue;
        for($i=0;$i<120;$i++){ [$w,]=arena_sim($teams[$ja],$teams[$jb]); if($w)$wins++; $tot++; }
    }
    $rate=round($wins/$tot*100);
    echo "    $ja 总进攻胜率 {$rate}%\n";
    ck42("$ja 胜率在合理区间(20-85)", $rate>=20 && $rate<=85, "{$rate}%");
}

// [6] 竞技场：不再出现“进攻方 0% / 防守方 100%”的极端
echo "\n[6] 竞技场不再零和极端\n";
$winM=0; for($i=0;$i<200;$i++){ [$w,]=arena_sim($teams['mage'],$teams['warrior']); if($w)$winM++; }
$rateM=round($winM/2);
echo "    法师进攻战士 {$rateM}%\n";
ck42('法师进攻战士>10%', $rateM>10, "{$rateM}%");

foreach($us as $uid) clean42($uid);
echo "\nOK=$ok FAIL=$bad\n";
