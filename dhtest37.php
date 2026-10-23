<?php
define('DH_TEST_MODE', true);
require 'includes/bootstrap.php';
// BOSS 专属掉落传说·行为型特效（第一批：天空岛/沉没圣所 5 件）
$ok = 0; $bad = 0;
function ck37(string $l, bool $c, string $d = '') { global $ok,$bad; if ($c) { $ok++; } else { $bad++; echo "FAIL $l $d\n"; } }

$uid = (int) user_create('zz37' . mt_rand(10000, 99999), 'x', 'mage', 'z1', true, 'tmp');
$u = user_by_id($uid);
$u['lv'] = 900; $u['str'] = 5000; $u['agi'] = 5000; $u['hp'] = 50000; $u['maxhp'] = 50000; $u['mp'] = 5000; $u['maxmp'] = 5000;
user_save($u);

function wear_legend(int $uid, string $slot, string $base, string $key): void {
    db()->exec('DELETE FROM equips WHERE uid=' . $uid);
    make_equip($uid, $slot, $base, 4, 900, ['base' => $base, 'extra' => ['k' => $key, 'v' => 1]]);
    db()->exec('UPDATE equips SET pos="wear" WHERE uid=' . $uid);
    _gear_uncache($uid);
}

// 每件都有 intro（详情页显示独特说明）
foreach (boss_legendary_effects() as $key => $cfg) {
    ck37("$key 有独特 intro", trim((string)($cfg['intro'] ?? '')) !== '' && trim(legend_relic_intro_by_key($key)) !== '');
}

// —— 雷霆君临·裂云护手：雷伤+25% + 暴击回血 ——
wear_legend($uid, 'gloves', '雷霆君临·裂云护手', 'lg_bl_thunder');
ck37('雷霆护手被识别', isset(legend_relic_worn($uid)['lg_bl_thunder']));
ck37('雷霆 雷伤eff>=25', legend_relic_eff_el($uid, 'thunder') >= 25, (string)legend_relic_eff_el($uid,'thunder'));
ck37('雷霆 暴击回血eff>0', legend_relic_eff_sum($uid, 'oncrit_heal') > 0);

// —— 风之母·追云之靴：闪避+10，残血再+20 ——
$base = gear_stats($uid)['dodge'];
wear_legend($uid, 'shoes', '风之母·追云之靴', 'lg_bl_wind');
$withBoots = gear_stats($uid)['dodge'];
ck37('风靴 闪避提升>=10', $withBoots - $base >= 10, "$base -> $withBoots");
$u = user_by_id($uid); $u['hp'] = (int)($u['maxhp'] * 0.3); user_save($u);
$u = user_by_id($uid);
$lowDodge = legend_relic_bonus_dodge($u);
ck37('风靴 残血额外闪避>=20', $lowDodge >= 20, (string)$lowDodge);

// —— 深渊鲸主·吞海之戒：吸血+4，击杀回血10% ——
wear_legend($uid, 'ring', '深渊鲸主·吞海之戒', 'lg_bl_whale');
ck37('吞海 吸血eff>=4', legend_relic_eff_sum($uid, 'lifesteal') >= 4);
ck37('吞海 击杀回血eff>=10', legend_relic_eff_sum($uid, 'kill_heal') >= 10);
// 击杀回血实际生效
$u = user_by_id($uid); $u['hp'] = 1000; $u['maxhp'] = 50000; user_save($u);
$u = user_by_id($uid); $b = ['id'=>'rat','name'=>'鼠','hp'=>1,'maxhp'=>1];
legend_relic_on_kill($u, $b);
ck37('吞海 击杀回血生效(>=5000)', (int)$u['hp'] - 1000 >= 4500, (string)((int)$u['hp']-1000));

// —— 苍穹之主·天穹之刃：对残血目标 +40% ——
wear_legend($uid, 'weapon', '苍穹之主·天穹之刃', 'lg_bl_sky');
$u = user_by_id($uid);
$bLow = ['id'=>'rat','name'=>'鼠','hp'=>10,'maxhp'=>1000]; // 1% 血
$bFull = ['id'=>'rat','name'=>'鼠','hp'=>1000,'maxhp'=>1000];
$lg = '';
[$pdLow, , , , ] = legend_relic_outgoing($u, $bLow, 1000, false, $lg);
$lg2 = '';
[$pdFull, , , , ] = legend_relic_outgoing($u, $bFull, 1000, false, $lg2);
ck37('苍穹 残血增伤(低>满)', $pdLow > $pdFull, "$pdLow / $pdFull");
ck37('苍穹 增伤约40%', $pdLow >= (int)($pdFull * 1.35), "$pdLow vs $pdFull");

// —— 海妖女王·溺歌之冠：挨打30%概率麻痹 ——
wear_legend($uid, 'head', '海妖女王·溺歌之冠', 'lg_bl_siren');
$u = user_by_id($uid);
$stunned = 0;
for ($i = 0; $i < 200; $i++) {
    $b = ['id'=>'rat','name'=>'鼠','hp'=>999,'maxhp'=>999];
    legend_relic_incoming($u, $b, 100);
    if (!empty($b['stun'])) { $stunned++; }
}
ck37('海妖 挨打会麻痹敌人', $stunned > 0, (string)$stunned);
ck37('海妖 概率约30%', $stunned >= 30 && $stunned <= 90, (string)$stunned);

db()->exec('DELETE FROM equips WHERE uid=' . (int) $uid);
db()->exec('DELETE FROM mats WHERE uid=' . (int) $uid);
db()->exec('DELETE FROM users WHERE id=' . (int) $uid);
echo "\nOK=$ok FAIL=$bad\n";
