<?php
define('DH_TEST_MODE', true);
require 'includes/bootstrap.php';
// 回归：罪值1%速度 / 雷恩二阶段等级显示 / 技能图鉴描述 / 出生点确认
$ok = 0; $bad = 0;
function ckT(string $l, bool $cond, string $detail = '') { global $ok,$bad; if ($cond) { $ok++; } else { $bad++; echo "FAIL $l $detail\n"; } }

// —— 1. 罪值下降到原来的 1% ——
$uid = user_create('dhtsin' . mt_rand(1000, 9999), 'x', 'warrior', 'z1', true, 'tmp');
$u = user_by_id($uid);
$before = (float) (cflags((int) $uid)['sin_f'] ?? 0);
sin_gain_typed($u, 'normal');
$after = (float) (cflags((int) $uid)['sin_f'] ?? 0);
$delta = $after - $before;
// 原来每只 0.2（含衰减后净~0.198）；现在应为 1% => 约 0.00198
ckT('普通怪罪值增量约为原来的1%', abs($delta - 0.00198) < 0.0002, "delta=$delta");
ckT('罪值增量非旧值0.198', abs($delta - 0.198) > 0.1, "delta=$delta");
// boss(5) 与 elite(1) 也同比缩小
$u = user_by_id($uid);
$b2 = (float) (cflags((int) $uid)['sin_f'] ?? 0);
sin_gain_typed($u, 'boss');
$d2 = (float) (cflags((int) $uid)['sin_f'] ?? 0) - $b2;
ckT('boss罪值约为原来的1%', abs($d2 - 0.04998) < 0.002, "d2=$d2"); // 5*0.01 - 0.002*0.01

// —— 2. 雷恩二阶段等级显示 ——
ckT('雷恩一阶段(quest<=14)显示14', monster_shown_lv('echo_rayne', ['quest' => 14]) === 14, '');
ckT('雷恩二阶段(quest>14)不再显示14', monster_shown_lv('echo_rayne', ['quest' => 15]) === 30, (string) monster_shown_lv('echo_rayne', ['quest' => 15]));
ckT('普通怪等级不受影响', monster_shown_lv('knight', ['quest' => 99]) === monster_lv('knight'), '');
ckT('无u参数时退回monster_lv', monster_shown_lv('echo_rayne') === monster_lv('echo_rayne'), '');

// —— 3. 技能图鉴：每个技能都有描述，按职业各9个 ——
$cat = skill_catalog();
$jobs = ['warrior', 'mage', 'hunter', 'priest', 'summoner'];
$total = 0; $emptyDesc = 0; $defaultDesc = 0;
foreach ($jobs as $jid) {
    $setS = job_skillset($jid);
    ckT("职业[$jid]有9个技能", count($setS) === 9, (string) count($setS));
    foreach ($setS as $sid) {
        $c = $cat[$sid] ?? null;
        if ($c === null) { ckT("技能[$sid]存在于图鉴", false); continue; }
        $total++;
        $d = skill_effect_desc($c);
        if ($d === '') { $emptyDesc++; }
        if ($d === '特殊效果') { $defaultDesc++; }
        ckT("技能[$sid]有10阶/20级文案", isset($c['t10']) && isset($c['lv20']), json_encode($c));
    }
}
ckT('图鉴覆盖45个技能', $total === 45, (string) $total);
ckT('无空描述', $emptyDesc === 0, (string) $emptyDesc);
ckT('无未知类型(全部命中已知kind)', $defaultDesc === 0, (string) $defaultDesc);
ckT('召唤技能描述含召唤', str_contains(skill_effect_desc($cat['s1']), '召唤'), skill_effect_desc($cat['s1']));

// —— 4. 出生点确认（smithy = 灰雾村铁匠铺，是设计好的开场）——
ckT('新号出生点在smithy铁匠铺', (string) ($u['loc'] ?? '') === 'smithy', (string) ($u['loc'] ?? ''));

// 清理
db()->exec('DELETE FROM pets WHERE uid=' . (int) $uid);
db()->exec('DELETE FROM users WHERE id=' . (int) $uid);
db()->exec('DELETE FROM equips WHERE uid=' . (int) $uid);
db()->exec('DELETE FROM mats WHERE uid=' . (int) $uid);
db()->exec('DELETE FROM skills WHERE uid=' . (int) $uid);

echo "\nOK=$ok FAIL=$bad\n";
