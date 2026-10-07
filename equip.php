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
    foreach ($aff as $x) {
        $mainMark = !empty($x['main']) ? '主·' : (!empty($x['legend']) ? '传·' : '');
        echo '·' . $mainMark . h(affix_fmt((string) ($x['id'] ?? $x['k'] ?? ''), (float) $x['v'], $x['tier'] ?? null)) . '<br>';
    }
}
echo '<div class="hr">--------</div>';
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
