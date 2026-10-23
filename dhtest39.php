<?php
define('DH_TEST_MODE', true);
require 'includes/bootstrap.php';
// ============================================================
// 各职业全技能回归：45 个技能逐个真实进战斗，确保都“放得出、有实效”。
// 曾修复：法师群攻 m5~m8 的 mult=0 导致恒 1 伤害；m8 溅射 splash 未定义告警。
// ============================================================
$ok=0;$bad=0;
function ck39(string $l,bool $c,string $d=''){global $ok,$bad; if($c){$ok++;}else{$bad++;echo "FAIL $l $d\n";}}
function has_any(string $h,array $ns):bool{foreach($ns as $n){if(str_contains($h,$n))return true;}return false;}

$RB2=['w5'=>'berserker','w6'=>'aegis','w7'=>'berserker','w8'=>'aegis',
      'm5'=>'wintermage','m6'=>'wildfire','m7'=>'wintermage','m8'=>'wildfire',
      'h5'=>'ranger','h6'=>'assassin','h7'=>'ranger','h8'=>'assassin',
      'p5'=>'shadowbishop','p6'=>'holybishop','p7'=>'shadowbishop','p8'=>'holybishop',
      's5'=>'necro','s6'=>'plague','s7'=>'necro','s8'=>'plague'];
$healSids=['p1','p2','p3','p4','p9'];
$summonSids=['s1','s2','s3','s4','s5','s6','s8'];
$mageAoe=['m5','m6','m7','m8'];

function mk39(string $job,bool $sin,string $rb2):int{
    $uid=(int)user_create('t39'.$job.mt_rand(10000,99999),'x',$job,'z1',true,'tmp');
    $_SESSION['uid']=$uid;
    $u=user_by_id($uid);
    $u['lv']=300;$u['str']=8000;$u['agi']=8000;$u['int']=8000;$u['spi']=8000;$u['vit']=8000;
    $u['atk']=8000;$u['def']=3000;$u['maxhp']=500000;$u['hp']=300000;$u['maxmp']=999999;$u['mp']=999999;
    user_save($u);
    cflag_set($uid,'rb1_job',$job);
    if($rb2!==''){cflag_set($uid,'rb2_job',$rb2);}
    if($sin){cflag_set($uid,'sin',100);}
    return $uid;
}

foreach(['warrior','mage','hunter','priest','summoner'] as $job){
    foreach(job_skillset($job) as $sid){
        $cat=skill_catalog()[$sid]??null;
        ck39("$job/$sid 图鉴存在",$cat!==null);
        if($cat===null){continue;}
        $name=$cat['name'];$kind=$cat['kind'];
        $uid=mk39($job,$kind==='sin',$RB2[$sid]??'');
        db()->exec('DELETE FROM skills WHERE uid='.$uid);
        db()->prepare('INSERT INTO skills (uid,skill,level,prof,tier,active) VALUES (?,?,20,20,10,1)')->execute([$uid,$sid]);
        _gear_uncache($uid);

        $N=4;$castN=0;$dmg=0;$heal=0;$sumN=0;$err='';
        for($i=0;$i<$N;$i++){
            $u=user_by_id($uid);$u['hp']=(int)($u['maxhp']*0.3);$u['mp']=999999;user_save($u);
            $u=user_by_id($uid);
            $b=['id'=>'rat','name'=>'木桩','hp'=>50000000,'maxhp'=>50000000,'atk'=>200,'def'=>1,'mdef'=>1,'agi'=>0,'dodge'=>0,
                'exp'=>0,'gold'=>0,'log'=>'','num'=>1,'left'=>1,'wexp'=>0,'wgold'=>0,'drops'=>[],'last'=>time()];
            $_SESSION['battle']=$b;
            $mp0=(int)$u['mp'];$hp0=(int)$u['hp'];$m0=(int)$_SESSION['battle']['hp'];
            try{$res=battle_round($u,$b,'skill');}catch(\Throwable $e){$err=$e->getMessage();$res=['log'=>'','flash'=>''];
            }
            $log=($res['log']??'').' '.($res['flash']??'');
            if((int)$u['mp']<$mp0){$castN++;}
            $dmg+=max(0,$m0-(int)($_SESSION['battle']['hp']??$m0));
            $heal+=max(0,(int)$u['hp']-$hp0);
            if(has_any($log,['召唤','应召','契约','小鬼','守卫','憎恶','恶鬼','梦魇'])){$sumN++;}
        }
        $avgDmg=(int)round($dmg/$N);$avgHeal=(int)round($heal/$N);
        ck39("$job/$sid 无异常",$err==='',$err);
        ck39("$job/$sid 放出(name=$name)",$castN>=(int)ceil($N*0.6),"cast=$castN/$N");
        if(in_array($sid,$summonSids,true)){
            ck39("$job/$sid 召唤生效",$sumN>=1,"sum=$sumN/$N");
        } elseif(in_array($sid,$healSids,true)){
            ck39("$job/$sid 有治疗",$avgHeal>=10,"heal=$avgHeal");
        } else {
            ck39("$job/$sid 有伤害",$avgDmg>=100,"dmg=$avgDmg");
        }
        if(in_array($sid,$mageAoe,true)){
            ck39("$job/$sid 法师群攻不再恒1(>=1000)",$avgDmg>=1000,"dmg=$avgDmg");
        }
        db()->exec('DELETE FROM skills WHERE uid='.$uid);
        db()->exec('DELETE FROM users WHERE id='.$uid);
        $_SESSION=[];
    }
}
echo "\nOK=$ok FAIL=$bad\n";
