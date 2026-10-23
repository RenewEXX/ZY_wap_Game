<?php
define('DH_TEST_MODE', true);
require 'includes/bootstrap.php';
// 全部 BOSS 专属掉落传说 → 行为型传说特效 的覆盖审计
$ok = 0; $bad = 0;
function ck38(string $l, bool $c, string $d = '') { global $ok,$bad; if ($c) { $ok++; } else { $bad++; echo "FAIL $l $d\n"; } }

// 收集所有唯一 base => slot
$bases = [];
foreach (monsters() as $mid => $_) {
    $d = monster_drops((string)$mid);
    if (is_array($d) && isset($d['legendary']['base'])) { $bases[(string)$d['legendary']['base']] = (string)$d['slot']; }
}
foreach (world_expansion_drops() as $d) {
    if (isset($d['legendary']['base'])) { $bases[(string)$d['legendary']['base']] = (string)$d['slot']; }
}
ck38('唯一传说件数=76', count($bases) === 76, (string)count($bases));

$reg = boss_legendary_effects();
ck38('注册表件数=76', count($reg) === 76, (string)count($reg));

// 每个 eff 字段都必须是“已接入钩子”的合法字段
$allowed = ['dr','counter','kill_heal','oncrit_heal','regen','lowhp_reduce','lowhp_dodge','lowhp_dmg','lowhp_crit','single','perlost_crit','stun','execute','el','dot','summon_power','summon_hp','summon_cap','lifesteal','dodge','crit','critdmg','skill_damage','atk_pct','def_pct','allres','truedmg','element','hp','atk','matk','def','mdef'];
$seenBase = [];
foreach ($reg as $key => $cfg) {
    ck38("key以lg_bl_开头 $key", str_starts_with((string)$key, 'lg_bl_'));
    $base = (string)($cfg['base'] ?? '');
    ck38("entry有base $key", $base !== '');
    ck38("intro非空 $key", trim((string)($cfg['intro'] ?? '')) !== '');
    ck38("base唯一 $base", !isset($seenBase[$base]));
    $seenBase[$base] = true;
    foreach (array_keys((array)($cfg['eff'] ?? [])) as $f) {
        ck38("eff字段合法 $key:$f", in_array($f, $allowed, true));
    }
}

// 每个掉落 base 都能映射到特效 key，且有独特故事
$DEFAULT = '深渊里捞出来的东西，每一件都记得主人的名字。<br>它现在记得你的了。';
$noKey = []; $noStory = [];
foreach ($bases as $base => $slot) {
    if (boss_legendary_key_by_base((string)$base) === '') { $noKey[] = $base; }
    if (trim(legend_lore((string)$base)) === $DEFAULT) { $noStory[] = $base; }
}
ck38('所有传说base都映射到特效', $noKey === [], implode(',', $noKey));
ck38('所有传说base都有独特故事', $noStory === [], implode(',', $noStory));

// 唯一性：76 件特效的“字段组合”两两不同，且不与任何遗物特效撞车
$kf = function ($e) { $k = array_keys((array)$e); sort($k); return implode('+', $k); };
$relicSets = [];
foreach (legend_relics() as $c) {
    if (isset($c['eff']) && is_array($c['eff']) && $c['eff'] !== []) { $relicSets[$kf($c['eff'])] = true; }
}
$seenSet = []; $dupMsg = [];
foreach ($reg as $k => $c) {
    $s = $kf($c['eff'] ?? []);
    if (isset($relicSets[$s])) { $dupMsg[] = "与遗物重复:$k($s)"; }
    if (isset($seenSet[$s])) { $dupMsg[] = "互相重复:$k={$seenSet[$s]}($s)"; }
    $seenSet[$s] = $k;
}
ck38('76件特效两两不同、且不与遗物重复', $dupMsg === [], implode(' | ', $dupMsg));

// 实造：用旧的泛用 key 造装，make_equip 应自动替换成 lg_bl_* 传说词条
$uid = (int) user_create('zz38' . mt_rand(10000, 99999), 'x', 'warrior', 'z1', true, 'tmp');
$u = user_by_id($uid); $u['lv'] = 900; $u['str'] = 9999; $u['agi'] = 9999; user_save($u);
$badMake = [];
foreach ($bases as $base => $slot) {
    db()->exec('DELETE FROM equips WHERE uid=' . $uid);
    $sn = make_equip($uid, (string)$slot, (string)$base, 4, 900, ['base' => (string)$base, 'extra' => ['k' => 'damage_reduction', 'v' => 5]]);
    if ($sn === '') { $badMake[] = "$base(造不出)"; continue; }
    $aff = json_decode((string) db()->query('SELECT affixes FROM equips WHERE uid=' . $uid . ' ORDER BY id DESC LIMIT 1')->fetchColumn(), true);
    $hasLg = false;
    foreach ((array)$aff as $a) {
        if (!empty($a['legend']) && str_starts_with((string)($a['id'] ?? ''), 'lg_bl_')) { $hasLg = true; break; }
    }
    if (!$hasLg) { $badMake[] = $base; }
}
ck38('每件传说都能造出且带 lg_bl_* 传说词条', $badMake === [], implode(',', array_slice($badMake, 0, 8)));

// 抽验战斗生效：贯穿多种机制
db()->exec('DELETE FROM equips WHERE uid=' . $uid);
make_equip($uid, 'ring', '天外审判者·裁决之戒', 4, 900, ['base' => '天外审判者·裁决之戒', 'extra' => ['k'=>'critdmg','v'=>3]]);
db()->exec('UPDATE equips SET pos="wear" WHERE uid=' . $uid);
_gear_uncache($uid);
$u = user_by_id($uid);
ck38('裁决之戒 被识别', isset(legend_relic_worn($uid)['lg_bl_judge']));
$bLow = ['id'=>'rat','name'=>'鼠','hp'=>10,'maxhp'=>1000]; $lg='';
$bFull = ['id'=>'rat','name'=>'鼠','hp'=>1000,'maxhp'=>1000]; $lg2='';
$u = user_by_id($uid);
[$pdLow] = legend_relic_outgoing($u, $bLow, 1000, false, $lg);
[$pdFull] = legend_relic_outgoing($u, $bFull, 1000, false, $lg2);
ck38('裁决 斩杀：残血目标伤害更高', $pdLow > $pdFull, "$pdLow/$pdFull");

db()->exec('DELETE FROM equips WHERE uid=' . (int) $uid);
db()->exec('DELETE FROM mats WHERE uid=' . (int) $uid);
db()->exec('DELETE FROM users WHERE id=' . (int) $uid);
echo "\nOK=$ok FAIL=$bad\n";
