<?php
define('DH_TEST_MODE', true);
require 'includes/bootstrap.php';
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
// 冰渊 / 灰烬深渊 专属套装补全审计
$ok = 0; $bad = 0;
function ck58($l, $c, $d = '') { global $ok, $bad; if ($c) { $ok++; } else { $bad++; echo "FAIL $l $d\n"; } }
function del58(int $uid) { db()->exec("DELETE FROM users WHERE id=$uid"); db()->exec("DELETE FROM equips WHERE uid=$uid"); db()->exec("DELETE FROM mats WHERE uid=$uid"); }

// ---- 1) 套装注册 ----
ck58('冰渊前缀=套装', equip_set_id('冰渊·寒锋') === 'binyuan');
ck58('灰烬前缀=套装', equip_set_id('灰烬·泰坦烬握') === 'huijin');
$bp = equip_set_pieces('binyuan'); $hp = equip_set_pieces('huijin');
ck58('冰渊5件', count($bp) === 5, json_encode($bp));
ck58('灰烬5件', count($hp) === 5, json_encode($hp));
foreach ($bp as $x) { ck58("冰渊件以冰渊·开头 $x", str_starts_with($x, '冰渊·')); }
foreach ($hp as $x) { ck58("灰烬件以灰烬·开头 $x", str_starts_with($x, '灰烬·')); }
ck58('冰渊件识别为套装', equip_is_set('冰渊·凝霜手套'));
ck58('灰烬件识别为套装', equip_is_set('灰烬·焦痕手套'));
ck58('冰渊说明非空', strlen(equip_set_bonus_text('binyuan', 5)) > 10);
ck58('灰烬说明非空', strlen(equip_set_bonus_text('huijin', 5)) > 10);

// ---- 2) 副本掉落接入了套装 ----
$frMap = ['fr_crawler', 'fr_stalker', 'fr_warden', 'fr_horror', 'fr_leviathan'];
foreach ($frMap as $mid) {
    $d = monster_drops($mid);
    ck58("冰渊副本怪 $mid 掉冰渊套装", str_starts_with((string) ($d['base'] ?? ''), '冰渊·'), json_encode($d));
}
$asMap = ['cinder_crawler', 'cinder_stalker', 'cinder_warden', 'cinder_horror', 'ash_titan'];
foreach ($asMap as $mid) {
    $d = monster_drops($mid);
    ck58("灰烬深渊怪 $mid 掉灰烬套装", str_starts_with((string) ($d['base'] ?? ''), '灰烬·'), json_encode($d));
}

// ---- 3) 冰渊副本 / 灰烬深渊副本 整张图里至少各有 5 个套装掉源 ----
$countSetSource = function (array $maps, string $prefix): int {
    $n = 0;
    foreach ($maps as $loc) {
        foreach ((locations()[$loc]['monsters'] ?? []) as $mid) {
            $d = monster_drops((string) $mid);
            if (str_starts_with((string) ($d['base'] ?? ''), $prefix)) { $n++; }
        }
    }
    return $n;
};
ck58('冰渊副本套装掉源>=5', $countSetSource(fr_maps(), '冰渊·') >= 5, (string) $countSetSource(fr_maps(), '冰渊·'));
ck58('灰烬深渊套装掉源>=5', $countSetSource(as_maps(), '灰烬·') >= 5, (string) $countSetSource(as_maps(), '灰烬·'));

// ---- 4) 实穿 5 件：套装加成进 gear_stats ----
$uid = (int) user_create('a58' . mt_rand(1, 999999), 'x', 'warrior', 'z1', true, 'tmp');
foreach (['gloves' => '冰渊·凝霜手套', 'weapon' => '冰渊·寒锋', 'body' => '冰渊·守望冰甲', 'back' => '冰渊·恐惧冰披', 'necklace' => '冰渊·利维坦之牙'] as $slot => $base) {
    make_equip($uid, $slot, $base, 2, 100);
}
db()->exec("UPDATE equips SET pos='wear' WHERE uid=$uid");
if (function_exists('_gear_uncache')) { _gear_uncache($uid); }
$gs = gear_stats($uid);
ck58('冰渊5件 元素伤害+100', $gs['element'] >= 100, (string) $gs['element']);
ck58('冰渊5件 真伤>0', $gs['truedmg'] > 0, (string) $gs['truedmg']);
ck58('冰渊5件 减伤>=8', $gs['damage_reduction'] >= 8, (string) $gs['damage_reduction']);
ck58('冰渊5件 法防>=150', $gs['mdef'] >= 150, (string) $gs['mdef']);
del58($uid);

$uid2 = (int) user_create('b58' . mt_rand(1, 999999), 'x', 'warrior', 'z1', true, 'tmp');
foreach (['gloves' => '灰烬·焦痕手套', 'shoes' => '灰烬·踏烬战靴', 'body' => '灰烬·守望烬甲', 'head' => '灰烬·惧焰头冠', 'weapon' => '灰烬·泰坦烬握'] as $slot => $base) {
    make_equip($uid2, $slot, $base, 2, 100);
}
db()->exec("UPDATE equips SET pos='wear' WHERE uid=$uid2");
if (function_exists('_gear_uncache')) { _gear_uncache($uid2); }
$gs2 = gear_stats($uid2);
ck58('灰烬5件 物攻%+10', $gs2['atk_pct'] >= 10, (string) $gs2['atk_pct']);
ck58('灰烬5件 火抗+40', $gs2['res_fire'] >= 40, (string) $gs2['res_fire']);
ck58('灰烬5件 真伤>0', $gs2['truedmg'] > 0, (string) $gs2['truedmg']);
ck58('灰烬5件 暴击>=3', $gs2['crit'] >= 3, (string) $gs2['crit']);
del58($uid2);

echo "OK=$ok FAIL=$bad\n";
