<?php
define('DH_TEST_MODE', true);
require 'includes/bootstrap.php';
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
// 六龙王套重构：每套 5 武器 + 10 防具 = 15 件；满档 11（1职业武器 + 10 防具）；5 职业都能凑满 11。
$ok=0;$bad=0;
function ck46($l,$c,$d=''){global $ok,$bad;if($c){$ok++;}else{$bad++;echo "FAIL $l $d\n";}}
$sets=['firedragon'=>'烈炎','azure'=>'苍岚','glacier'=>'永冻','thunder'=>'天谴','ruin'=>'终焉','judgment'=>'圣裁'];
$jobs=['warrior','mage','priest','hunter','summoner'];
$armorSlots=['offhand','body','head','legs','gloves','shoes','belt','back','ring','necklace'];
$allsn=[];
foreach($sets as $sid=>$pfx){
    $layout=dragon_set_layout($sid);
    ck46("[$sid] 15件", count($layout)===15, (string)count($layout));
    $slots=array_map(fn($p)=>$p[0],$layout);
    $wep=array_filter($slots,fn($s)=>$s==='weapon'); $arm=array_filter($slots,fn($s)=>$s!=='weapon');
    ck46("[$sid] 5武器", count($wep)===5, (string)count($wep));
    ck46("[$sid] 10防具且槽唯一", count($arm)===10 && count(array_unique($arm))===10);
    ck46("[$sid] 防具槽全覆盖", array_values(array_unique($arm))===$armorSlots);
    // 武器覆盖5职业 + 无双手 + relic名含魂灯
    $cov=[]; $tw=false;
    foreach($layout as [$slot,$base]){
        if($slot!=='weapon') continue;
        foreach(weapon_jobs(weapon_sub($base)) as $j) $cov[$j]=true;
        if(weapon_is_twohand($base)) $tw=true;
    }
    ck46("[$sid] 武器覆盖5职业", count($cov)===5, json_encode(array_keys($cov)));
    ck46("[$sid] 无双手武器(否则副手空)", !$tw);
    // 副手无限制字
    $oh=''; foreach($layout as [$s,$b]){ if($s==='offhand') $oh=$b; }
    $badOh = preg_match('/盾|法器|契匣|法杖|提灯/u', $oh);
    ck46("[$sid] 副手无职业限制", $badOh===0, $oh);
    foreach($layout as [$s,$b]){ $allsn[]=$b; }
}
$dup=array_filter(array_count_values($allsn),fn($c)=>$c>1);
ck46('全局无重名', count($dup)===0, json_encode($dup,JSON_UNESCAPED_UNICODE));

// 5职业×4套 实穿满11件 + 11档加成
$uidRows=[];
foreach(['azure','ruin','judgment','firedragon'] as $sid){
    foreach($jobs as $job){
        $uid=(int)user_create('d'.$job.mt_rand(1,99999),'x',$job,'z1',true,'tmp'); $_SESSION['uid']=$uid;
        $u=user_by_id($uid); $u['lv']=950;$u['str']=99999;$u['agi']=99999;$u['int']=99999;$u['vit']=99999; user_save($u); _gear_uncache($uid);
        $layout=dragon_set_layout($sid); $chosenW=null;
        foreach($layout as [$s,$b]){ if($s==='weapon' && in_array($job, weapon_jobs(weapon_sub($b)), true)){ $chosenW=$b; break; } }
        ck46("[$sid/$job] 有可用武器", $chosenW!==null);
        foreach($layout as [$s,$b]){
            if($s==='weapon' && $b!==$chosenW) continue;
            make_equip($uid,$s,$b,4,900);
        }
        $st=db()->prepare("SELECT id FROM equips WHERE uid=? AND pos='' "); $st->execute([$uid]);
        $ids=[]; while($r=$st->fetch()){ $ids[]=(int)$r['id']; }
        foreach($ids as $id){ wear_equip($uid,$id); }
        _gear_uncache($uid);
        $cnt=equip_set_owned_count($uid,$sid);
        ck46("[$sid/$job] 穿满11件", $cnt===11, "cnt=$cnt");
        $stats=gear_stats($uid);
        // 11档：各套挑一个11档独有的可量化加成作证据
        $sig=false;
        if($sid==='azure') $sig = (($stats['crit']??0)>=18);
        if($sid==='ruin')  $sig = (($stats['lifesteal']??0)>=20);
        if($sid==='judgment') $sig = (($stats['skill_damage']??0)>=50);
        if($sid==='firedragon') $sig = (($stats['damage_reduction']??0)>=8);
        ck46("[$sid/$job] 触发11档加成", $sig);
        db()->exec("DELETE FROM users WHERE id=$uid");db()->exec("DELETE FROM mats WHERE uid=$uid");db()->exec("DELETE FROM equips WHERE uid=$uid");
    }
}
echo "\nOK=$ok FAIL=$bad\n";
