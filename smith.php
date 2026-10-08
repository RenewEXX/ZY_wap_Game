<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

$u = require_login();
$smiths = blacksmiths();
$cur = (string) $u['loc'];
if (!isset($smiths[$cur])) {
    flash_set('这里没有铁匠。去灰雾村铁匠铺或白石镇铁匠找人强化。');
    header('Location: home.php');
    exit;
}
$name = $smiths[$cur];

$a = (string) ($_GET['a'] ?? '');
if ($a === 'enhance') {
    flash_set(enhance_equip((int) $u['id'], (int) ($_GET['id'] ?? 0)));
    header('Location: smith.php');
    exit;
}

wap_start('铁匠铺');
echo '铁匠【' . h($name) . '】擦着锤子看你：“强化找我，野外可没炉子。”<br>';
echo '<span class="gold">' . h(fmt_money((int) $u['gold'])) . '</span><br>';
$mats = mats_of((int) $u['id']);
echo '<span class="muted">下级石x' . ($mats['enhance_t1'] ?? 0) . ' 中级石x' . ($mats['enhance_t2'] ?? 0) . ' 上级石x' . ($mats['enhance_t3'] ?? 0) . '</span>';
echo '<div class="hr">--------</div>';
$flash = flash_get();
if ($flash !== '') {
    echo '<div class="warn">' . h($flash) . '</div>';
}
$qcolor = ['#999', '#fff', '#6cf', '#c6f', '#fc3'];
$eqs = my_equips((int) $u['id']);
if ($eqs === []) {
    echo '<span class="muted">你一件装备都没有，先去刷吧。</span>';
}
foreach ($eqs as $e) {
    $lv = (int) ($e['enhance_level'] ?? 0);
    echo enhance_tag_html($lv) . '<span style="color:' . $qcolor[(int) $e['quality']] . '">' . h(equip_shortname($e['name'])) . '</span><br>';
    $rule = enhance_table()[$lv + 1] ?? null;
    if (!$rule) {
        echo '<span class="muted">已满+15。</span><br>';
        continue;
    }
    $have = $mats[$rule['mat']] ?? 0;
    echo '<span class="muted">+' . ($lv + 1) . ' 成功率' . $rule['rate'] . '% ' . h(enhance_material_name($rule['mat'])) . 'x' . $rule['cost'] . '(有' . $have . ')主属性×' . rtrim(rtrim(number_format(enhance_rate(['enhance_level' => $lv + 1]), 2, '.', ''), '0'), '.') . h(enhance_bonus_text($lv + 1)) . '</span><br>';
    echo '<a href="smith.php?a=enhance&id=' . $e['id'] . '">强化</a><br>';
}
nav_line();
wap_end(false);
