<?php
define('DH_TEST_MODE', true);
require 'includes/bootstrap.php';

$ok = 0; $bad = 0;
function ck28(string $l, bool $c, string $d = '') { global $ok,$bad; if ($c) { $ok++; } else { $bad++; echo "FAIL $l $d\n"; } }

function mk28(int $mhp = 1000000000000, int $matk = 1): array {
    return ['id'=>'crypt_lord','name'=>'靶子','hp'=>$mhp,'maxhp'=>$mhp,'atk'=>$matk,'exp'=>1,'gold'=>1,'num'=>1,'left'=>1,'elite'=>0,'wexp'=>0,'wgold'=>0,'drops'=>[],'log'=>'','history'=>[],'summons'=>[]];
}
function learn28(int $uid, array $sids): void {
    foreach ($sids as $sid) {
        db()->prepare('INSERT OR REPLACE INTO skills (uid,skill,level,prof,tier,active) VALUES (?,?,?,?,?,?)')->execute([$uid,$sid,20,600,10,0]);
    }
}
// 逐轮推进（正确按引用把 $_SESSION['battle'] 传给 battle_round，召唤兽存到 session）
function run28(int $uid, int $rounds): string {
    $err = '';
    try {
        for ($i=0;$i<$rounds;$i++){
            $uu=user_by_id($uid); if(!$uu){break;}
            $uu['hp']=9000000; $uu['mp']=9999999; user_save($uu);
            $r = battle_round($uu, $_SESSION['battle'], 'skill');
            $st = (string)($r['status'] ?? '');
            if (!in_array($st, ['fight','victory','defeat'], true)) { return "bad status: $st"; }
            if ($st !== 'fight') { break; }
        }
    } catch (\Throwable $e) { $err = $e->getMessage(); }
    return $err;
}

$uid = user_create('tsimsum' . mt_rand(1000,9999), 'x', 'summoner', 'z1', true, 'tmp');
$_SESSION['uid'] = (int) $uid;
$u = user_by_id($uid);
$u['lv']=999; $u['str']=20000; $u['agi']=20000; $u['int']=200000; $u['atk']=200000; $u['def']=80000;
$u['hp']=9000000; $u['maxhp']=9000000; $u['mp']=9999999; $u['maxmp']=9999999;
user_save($u);
learn28((int)$uid, ['s1','s2','s3','s4','s5','s6','s7','s8']);

// ============ 1) 基础召唤：s1 反复施放能召唤，战斗不报错 ============
set_active_skill((int)$uid, 's1');
$_SESSION['battle'] = mk28();
$err = run28((int)$uid, 8);
ck28('基础召唤战斗无异常', $err === '', $err);
ck28('s1 召唤出召唤兽', summon_list() !== [], (string) count(summon_list()));

// ============ 2) 死灵之拥 necro：召唤上限提升到 4 ============
cflag_set((int)$uid, 'rb2_job', 'necro');
$_SESSION['battle'] = mk28();
$err = run28((int)$uid, 8);
$uu = user_by_id($uid);
ck28('necro 召唤上限=4', summon_cap($uu) === 4, (string) summon_cap($uu));
ck28('necro 场上召唤物<=4', count(summon_list()) <= 4, (string) count(summon_list()));
ck28('necro 战斗无异常', $err === '', $err);

// ============ 3) 痛苦盛宴 plague：s6 噬魂夜宴能召唤 ============
cflag_set((int)$uid, 'rb2_job', 'plague');
set_active_skill((int)$uid, 's6');
$_SESSION['battle'] = mk28();
$err = run28((int)$uid, 4);
ck28('plague 战斗无异常(s6)', $err === '', $err);
ck28('plague s6 能召唤', count(summon_list()) > 0, (string) count(summon_list()));

// ============ 4) 转职专属传说·召唤向：上限/伤害/生命 ============
cflag_set((int)$uid, 'rb2_job', 'necro');
db()->exec('DELETE FROM equips WHERE uid=' . (int)$uid);
foreach ([['weapon','尸潮','lg_corpsetide'],['ring','亡语','lg_deathword'],['necklace','冥约','lg_netherpact'],['head','骨爆','lg_boneburst']] as [$sl,$nm,$k]) {
    make_equip((int)$uid, $sl, $nm, 4, 700, ['extra'=>['k'=>$k,'v'=>1]]);
}
db()->prepare('UPDATE equips SET pos="wear" WHERE uid=?')->execute([(int)$uid]);
_gear_uncache((int)$uid);
$GLOBALS['_legend_gen'] = (int)($GLOBALS['_legend_gen'] ?? 0) + 1;
$uu = user_by_id($uid);
ck28('尸潮 召唤上限+1(necro4→5)', summon_cap($uu) === 5, (string) summon_cap($uu));
ck28('亡语+骨爆 召唤伤害+50', legend_relic_summon_power((int)$uid) >= 50, (string) legend_relic_summon_power((int)$uid));
ck28('冥约+骨爆 召唤生命+60', legend_relic_summon_hp((int)$uid) >= 60, (string) legend_relic_summon_hp((int)$uid));
$_SESSION['battle'] = mk28(); summon_save([]);
$s = summon_spawn('imp', player_matk($uu), (int)$uu['maxhp'], $uu);
ck28('召唤物受遗物加成(攻击>0)', (int)$s['atk'] > 0, (string)$s['atk']);
$_SESSION['battle'] = mk28();
$err = run28((int)$uid, 8);
ck28('穿召唤遗物战斗无异常', $err === '', $err);

// ============ 5) 彼岸（召唤物×5，死亡连坐） ============
db()->exec('DELETE FROM equips WHERE uid=' . (int)$uid);
make_equip((int)$uid, 'belt', '彼岸', 4, 700, ['extra'=>['k'=>'lg_othershore','v'=>5]]);
db()->prepare('UPDATE equips SET pos="wear" WHERE uid=?')->execute([(int)$uid]);
_gear_uncache((int)$uid);
$GLOBALS['_legend_gen'] = (int)($GLOBALS['_legend_gen'] ?? 0) + 1;
$uu = user_by_id($uid);
$_SESSION['battle'] = mk28(); summon_save([]);
$sOsh = summon_spawn('imp', player_matk($uu), (int)$uu['maxhp'], $uu);
ck28('彼岸 召唤物伤害×5', (float)summon_power_bonus($uu) >= 400.0, (string)summon_power_bonus($uu));
ck28('彼岸 召唤物生命×5', (float)summon_hp_bonus($uu) >= 400.0, (string)summon_hp_bonus($uu));
// 召唤物阵亡 → 触发玩家连坐死亡标记
summon_save([['kind'=>'imp','name'=>'深渊小鬼','hp'=>1,'maxhp'=>1000,'atk'=>10,'bomb'=>false]]);
unset($GLOBALS['_lg_osh_die']);
$b = mk28();
[$rest, $log] = summon_absorb(999999, $b);
ck28('彼岸 召唤物阵亡→连坐标记', !empty($GLOBALS['_lg_osh_die']), $log);
unset($GLOBALS['_lg_osh_die']);

// ============ 6) 压力：多 build × 多技能，穿满遗物，多轮不出错、状态合法 ============
$builds = ['', 'necro', 'plague'];
$sids = ['s1','s2','s3','s4','s5','s6'];
$allOk = true; $err2 = '';
foreach ($builds as $bj) {
    foreach ($sids as $sid) {
        db()->exec('DELETE FROM equips WHERE uid=' . (int)$uid);
        foreach ([['weapon','噬魂','lg_souleat'],['ring','亡语','lg_deathword'],['necklace','冥约','lg_netherpact'],['head','骨爆','lg_boneburst'],['belt','永夜','lg_endlessnight']] as [$sl,$nm,$k]) {
            make_equip((int)$uid, $sl, $nm, 4, 720, ['extra'=>['k'=>$k,'v'=>1]]);
        }
        db()->prepare('UPDATE equips SET pos="wear" WHERE uid=?')->execute([(int)$uid]);
        _gear_uncache((int)$uid);
        $GLOBALS['_legend_gen'] = (int)($GLOBALS['_legend_gen'] ?? 0) + 1;
        cflag_set((int)$uid, 'rb2_job', $bj);
        set_active_skill((int)$uid, $sid);
        $_SESSION['battle'] = mk28();
        $e = run28((int)$uid, 10);
        if ($e !== '') { $allOk = false; $err2 = "build=$bj sid=$sid: $e"; break 2; }
    }
}
ck28('多build×多技能满遗物无异常', $allOk, $err2);

// 清理
db()->exec('DELETE FROM equips WHERE uid=' . (int)$uid);
db()->exec('DELETE FROM mats WHERE uid=' . (int)$uid);
db()->exec('DELETE FROM skills WHERE uid=' . (int)$uid);
db()->exec('DELETE FROM users WHERE id=' . (int)$uid);
unset($_SESSION['battle']);

echo "\nOK=$ok FAIL=$bad\n";
