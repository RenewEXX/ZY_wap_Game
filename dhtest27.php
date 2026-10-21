<?php
define('DH_TEST_MODE', true);
require 'includes/bootstrap.php';

$ok = 0; $bad = 0;
function ck27(string $l, bool $c, string $d = '') { global $ok,$bad; if ($c) { $ok++; } else { $bad++; echo "FAIL $l $d\n"; } }

$relics = legend_relics();
ck27('传说relic数量=62', count($relics) === 62, (string) count($relics));

// 1) 部位合法、key前缀、说明非空、职业合法
$jobs = array_keys(jobs());
foreach ($relics as $name => $c) {
    ck27("[$name]部位合法", isset(equip_slots()[$c['slot']]), $c['slot']);
    ck27("[$name]非称号/神魂", !in_array($c['slot'], ['title', 'soul'], true), $c['slot']);
    ck27("[$name]key=lg_前缀", str_starts_with($c['key'], 'lg_'), $c['key']);
    ck27("[$name]有说明", (string) $c['intro'] !== '', $name);
    ck27("[$name]等级>0", (int) $c['lv'] > 0, (string) $c['lv']);
    ck27("[$name]职业合法", $c['job'] === '' || in_array($c['job'], $jobs, true), (string) $c['job']);
    ck27("[$name]intro=by_key可取", legend_relic_intro_by_key($c['key']) === $c['intro'], $c['key']);
}

// 2) 掉落表：boss存在且是BOSS、relic名存在、boss等级 >= relic需求等级(合理)
$all = locations();
$bothDrops = [];
foreach ([legend_relic_drops(), legend_relic_drops2()] as $map) {
    foreach ($map as $mid => $names) {
        foreach ($names as $n) {
            $bothDrops[$n] = true;
        }
    }
}
foreach ([legend_relic_drops(), legend_relic_drops2()] as $map) {
    foreach ($map as $mid => $names) {
        ck27("掉落BOSS[$mid]存在", isset(monsters()[$mid]), $mid);
        ck27("掉落BOSS[$mid]是BOSS", is_boss_mid($mid), $mid);
        foreach ($names as $n) {
            ck27("掉落relic[$n]在表中", isset($relics[$n]), $n);
            ck27("[$n]需求等级<=掉落BOSS等级", (int) $relics[$n]['lv'] <= monster_lv($mid), $relics[$n]['lv'] . '>' . monster_lv($mid));
        }
    }
}
// 每件relic都能从某个boss掉落（可获得）
foreach ($relics as $name => $c) {
    ck27("relic[$name]有掉落来源", isset($bothDrops[$name]), '');
}
// 第二接口 40 件都带 eff 且能被索引到
$idx = legend_relic_eff_index();
$effCount = 0;
foreach ($relics as $name => $c) {
    if (isset($c['eff'])) {
        $effCount++;
        ck27("[$name]eff被索引", isset($idx[$c['key']]), $c['key']);
    }
}
ck27('第二接口eff件数=40', $effCount === 40, (string) $effCount);

// 3) affix 显示（专属传说效果文字）
$uid = user_create('w27' . mt_rand(1000, 9999), 'x', 'warrior', 'z1', true, 'tmp');
$dbg = affix_fmt('lg_deathmatch', 50);
ck27('affix_fmt显示效果说明', $dbg === legend_relics()['死斗者']['intro'], $dbg);

// 4) 制造一件带lg词条的传说，gear_stats 命中效果（死斗者 暴伤+50）
$GLOBALS['_gear_bust'] = $uid;
$before = gear_stats((int) $uid)['critdmg'];
make_equip((int) $uid, 'head', '死斗者', 4, 975, ['extra' => ['k' => 'lg_deathmatch', 'v' => 50.0]]);
db()->prepare('UPDATE equips SET pos="wear" WHERE uid=?')->execute([(int) $uid]);
$GLOBALS['_gear_bust'] = $uid;
$GLOBALS['_legend_gen'] = (int) ($GLOBALS['_legend_gen'] ?? 0) + 1;
$after = gear_stats((int) $uid)['critdmg'];
ck27('死斗者·暴伤+50生效', $after >= $before + 50, "$before->" . $after);
$w = legend_relic_worn((int) $uid);
ck27('legend_relic_worn读到lg_deathmatch', isset($w['lg_deathmatch']), json_encode(array_keys($w)));

// 4b) 第二接口 eff 通用引擎：面板减伤 + 元素加成
$uidG = user_create('w27g' . mt_rand(1000, 9999), 'x', 'warrior', 'z1', true, 'tmp');
make_equip((int) $uidG, 'body', '壁垒', 4, 600, ['extra' => ['k' => 'lg_bulwark', 'v' => 1]]);
make_equip((int) $uidG, 'weapon', '极寒', 4, 600, ['extra' => ['k' => 'lg_extreme', 'v' => 1]]);
db()->prepare('UPDATE equips SET pos="wear" WHERE uid=?')->execute([(int) $uidG]);
$GLOBALS['_gear_bust'] = $uidG;
$GLOBALS['_legend_gen'] = (int) ($GLOBALS['_legend_gen'] ?? 0) + 1;
$gsG = gear_stats((int) $uidG);
ck27('eff:壁垒减伤8生效', (float) $gsG['damage_reduction'] >= 8, (string) $gsG['damage_reduction']);
ck27('eff:极寒冰伤+25生效', abs(legend_relic_el_mult((int) $uidG, 'ice') - 1.25) < 0.001, (string) legend_relic_el_mult((int) $uidG, 'ice'));
ck27('eff:legend_relic_effs非空', count(legend_relic_effs((int) $uidG)) === 2, (string) count(legend_relic_effs((int) $uidG)));

// 5) 掉落：能真的出带lg词条的装备
db()->exec('DELETE FROM equips WHERE uid=' . (int) $uid);
$got = 0;
for ($i = 0; $i < 400; $i++) {
    $m = legend_relic_kill_drop((int) $uid, 'divine_ruin');
    if ($m !== '') { $got++; }
    db()->exec('DELETE FROM equips WHERE uid=' . (int) $uid);
}
ck27('divine_ruin能掉传说relic', $got > 0, "got=$got");

// 6) 战斗冒烟：战士穿几件，跑几轮不出错
db()->exec('DELETE FROM equips WHERE uid=' . (int) $uid);
foreach ([['weapon', '不朽', 'lg_undying', 10.0], ['necklace', '荆棘之心', 'lg_bramble', 30.0], ['belt', '誓言', 'lg_vow', 200.0]] as [$sl, $nm, $k, $v]) {
    make_equip((int) $uid, $sl, $nm, 4, 500, ['extra' => ['k' => $k, 'v' => $v]]);
}
db()->query('UPDATE equips SET pos="wear" WHERE uid=' . (int) $uid);
$u = user_by_id($uid);
$u['lv'] = 600; $u['hp'] = 9000000; $u['maxhp'] = 9000000; $u['mp'] = 9000000; $u['maxmp'] = 9000000; $u['atk'] = 200000; $u['def'] = 80000; $u['str'] = 5000; $u['agi'] = 5000;
user_save($u);
db()->prepare('INSERT OR REPLACE INTO skills (uid,skill,level,prof,tier,active) VALUES (?,?,?,?,?,?)')->execute([(int) $uid, 'w2', 20, 500, 10, 1]);
$_SESSION['uid'] = (int) $uid;
$m = scale_monster(['hp' => monsters()['crypt_lord']['hp'], 'atk' => monsters()['crypt_lord']['atk']], 'crypt_lord', 0);
$_SESSION['battle'] = ['id' => 'crypt_lord', 'name' => '霜墓领主', 'hp' => $m['hp'], 'maxhp' => $m['hp'], 'atk' => $m['atk'], 'exp' => 1, 'gold' => 1, 'num' => 1, 'left' => 1, 'elite' => 0, 'wexp' => 0, 'wgold' => 0, 'drops' => [], 'log' => '', 'history' => [], 'summons' => []];
$okBattle = true;
try {
    for ($i = 0; $i < 6; $i++) {
        $uu = user_by_id($uid);
        $bb = $_SESSION['battle'];
        $r = battle_round($uu, $bb, 'skill');
        if ($r['status'] !== 'fight') { break; }
    }
} catch (\Throwable $e) {
    $okBattle = false;
    echo "BATTLE ERROR: " . $e->getMessage() . "\n";
}
ck27('穿relic跑战斗不出错', $okBattle, '');

// 清理
foreach ([$uid, $uidG] as $x) {
    db()->exec('DELETE FROM users WHERE id=' . (int) $x);
    db()->exec('DELETE FROM equips WHERE uid=' . (int) $x);
    db()->exec('DELETE FROM mats WHERE uid=' . (int) $x);
    db()->exec('DELETE FROM skills WHERE uid=' . (int) $x);
}
unset($_SESSION['battle']);

echo "\nOK=$ok FAIL=$bad\n";
