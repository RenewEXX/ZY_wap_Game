<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

$u = require_login();
$eid = (int) ($_GET['id'] ?? 0);
$st = db()->prepare('SELECT * FROM equips WHERE id=? AND uid=?');
$st->execute([$eid, (int) $u['id']]);
$e = $st->fetch();
if ((string) ($_GET['a'] ?? '') === 'enhance') {
    flash_set('强化要去铁匠铺找铁匠，野外点不了。');
    header('Location: equip.php?id=' . $eid);
    exit;
}
if ((string) ($_GET['a'] ?? '') === 'enchant') {
    flash_set(enchant_equip((int) $u['id'], $eid, (string) ($_GET['stone'] ?? '')));
    header('Location: equip.php?id=' . $eid);
    exit;
}
if (!$e) {
    flash_set('没有这件装备。');
    header('Location: bag.php');
    exit;
}

wap_start('装备');
$qcolor = ['#999', '#fff', '#6cf', '#c6f', '#fc3'];
$qname = equip_qualities()[(int) $e['quality']] ?? '';
echo '<span style="color:' . $qcolor[(int) $e['quality']] . '">' . h(equip_shortname($e['name'])) . '</span><br>';
echo '部位：' . h(equip_slots()[$e['slot']] ?? '') . '<br>';
echo '品级：' . h($qname) . '<br>';
echo '物品等级：' . (int) ($e['item_level'] ?? 1) . '<br>';
echo '状态：' . ((int) ($e['broken'] ?? 0) === 1 ? '已碎裂' : ($e['pos'] === 'wear' ? '已穿戴' : '背包')) . '<br>';
echo '强化：+' . (int) ($e['enhance_level'] ?? 0) . ((int) ($e['broken'] ?? 0) === 1 ? '（碎裂）' : '') . '<br>';
echo '<div class="hr">--------</div>';
$aff = json_decode((string) $e['affixes'], true);
if (is_array($aff)) {
    echo equip_affix_html($aff);
}
$isWep = (($e['slot'] ?? '') === 'weapon');
if (!empty($e['enchant_el'])) {
    echo '·附魔：' . h(element_name((string) $e['enchant_el']) . ($isWep ? '伤害+' : '抗性+') . (int) ($e['enchant_val'] ?? 0) . ($isWep ? '' : '%')) . '<br>';
}
echo '<div class="hr">--------</div>';
$myStones = [];
foreach (mats_of((int) $u['id']) as $mid => $num) {
    if ($num > 0 && enchant_mat_el($mid) !== '' && enchant_mat_tier($mid) !== '') {
        $myStones[$mid] = $num;
    }
}
if ($myStones !== []) {
    echo '附魔（武器加属性伤害，防具加属性抗性，戒指项链不可附魔；同石重附只在区间内波动）：<br>';
    foreach ($myStones as $mid => $num) {
        echo '·' . h(enchant_mat_name($mid)) . 'x' . $num . ' <a href="equip.php?a=enchant&id=' . $eid . '&stone=' . h($mid) . '">附魔</a><br>';
    }
}
if ((int) ($e['broken'] ?? 0) === 0) {
    $next = (int) ($e['enhance_level'] ?? 0) + 1;
    $rule = enhance_table()[$next] ?? null;
    if ($rule) {
        echo '下一阶：+' . $next . '　成功率' . $rule['rate'] . '%　消耗' . h(enhance_material_name($rule['mat'])) . 'x' . $rule['cost'] . '<br>';
        echo '<span class="muted">强化要找铁匠：灰雾村找布隆，白石镇找丹恩·铜须。</span><br>';
    }
}
if ($e['pos'] === 'wear') {
    echo '<a href="bag.php?a=off&id=' . $e['id'] . '">脱下</a><br>';
} else {
    echo '<a href="bag.php?a=wear&id=' . $e['id'] . '">穿上</a> ';
    echo '<a href="bag.php?a=sell&id=' . $e['id'] . '">卖' . h(fmt_money(equip_sell_price((int) $e['quality']))) . '</a><br>';
}
echo '<a href="bag.php">回背包</a>';
nav_line();
wap_end(false);
