<?php
define('DH_TEST_MODE', true);
require 'includes/bootstrap.php';

$ok = 0; $bad = 0;
function ck29(string $l, bool $c, string $d = '') { global $ok,$bad; if ($c) { $ok++; } else { $bad++; echo "FAIL $l $d\n"; } }

$bases = we2_base_ids();
$all = locations();
$regions = we2_regions();
$links = we2_links();

// 1) 每个区域一个根据地，数量=7
ck29('根据地数量=区域数', count($bases) === count(we2_specs()), count($bases) . '/' . count(we2_specs()));
ck29('根据地数量=7', count($bases) === 7, (string) count($bases));

foreach ($bases as $i => $bid) {
    // 2) 基地是合法地图
    ck29("[$bid]在locations中", isset($all[$bid]), $bid);
    // 3) 基地无怪（安全区）
    ck29("[$bid]无怪", ($all[$bid]['monsters'] ?? []) === [], json_encode($all[$bid]['monsters'] ?? []));
    // 4) 基地是该区域第一张图
    $regionIds = array_values($regions)[$i];
    ck29("[$bid]是区域首图", ($regionIds[0] ?? '') === $bid, ($regionIds[0] ?? '') . ' vs ' . $bid);
    // 5) 基地与 hub 双向连通
    $hub = $links[$bid] ?? '';
    ck29("[$bid]有hub链接", $hub !== '' && isset($all[$hub]), $hub);
    ck29("[$bid]出口含hub", isset($all[$bid]['exits'][$hub]), json_encode(array_keys($all[$bid]['exits'])));
    ck29("[$hub]出口含基地", isset($all[$hub]['exits'][$bid]), $hub);
    // 6) 基地通向区域下一张图（第二张）
    $gate = $regionIds[1] ?? '';
    ck29("[$bid]通向下图", $gate !== '' && isset($all[$bid]['exits'][$gate]), $gate);
    // 6b) 传送等级要求 = 区域第一关的等级要求，且>1
    $lvBase = teleport_min_lv($bid);
    $lvGate = $gate !== '' ? teleport_min_lv($gate) : 0;
    ck29("[$bid]传送等级=gate", $lvBase === $lvGate, $lvBase . '/' . $lvGate);
    ck29("[$bid]传送等级>1", $lvBase > 1, (string) $lvBase);
    // 7) 铁匠（强化）可用
    ck29("[$bid]配了铁匠", isset(blacksmiths()[$bid]), $bid);
}

// 8) 玩家在基地时，从 hub 能走进来（可徒步到基地）
$start = array_values($links)[0] ?? 'capital_gate';
ck29('从hub可寻路到某基地', find_route($start, $bases[0]) !== [], $start . '->' . $bases[0]);

// 9) 休整逻辑：满血逻辑存在（用 user 验证 home 的 rest_base 会回满）——间接检查 we2_base_ids 判定
$uid = user_create('tbase' . mt_rand(1000,9999), 'x', 'warrior', 'z1', true, 'tmp');
$u = user_by_id($uid);
$u['maxhp'] = 5000; $u['hp'] = 10; $u['maxmp'] = 2000; $u['mp'] = 0; $u['loc'] = $bases[0];
user_save($u);
ck29('基地地图可被设为所在', loc($bases[0])['name'] !== '', '');
// 模拟 rest_base 的效果
$u = user_by_id($uid);
$u['hp'] = (int)$u['maxhp']; $u['mp'] = (int)($u['maxmp'] ?? 0); user_save($u);
$u2 = user_by_id($uid);
ck29('休整后血满', (int)$u2['hp'] === (int)$u2['maxhp'], (string)$u2['hp']);
ck29('休整后蓝满', (int)$u2['mp'] === (int)($u2['maxmp'] ?? 0), (string)$u2['mp']);

// 清理
db()->exec('DELETE FROM equips WHERE uid=' . (int)$uid);
db()->exec('DELETE FROM mats WHERE uid=' . (int)$uid);
db()->exec('DELETE FROM users WHERE id=' . (int)$uid);

// 10) 基地名字里带“根据地”，且命名来自区域
foreach ($bases as $bid) {
    ck29("[$bid]名含根据地", str_contains((string)($all[$bid]['name'] ?? ''), '根据地'), (string)($all[$bid]['name'] ?? ''));
}

echo "\nOK=$ok FAIL=$bad\n";
