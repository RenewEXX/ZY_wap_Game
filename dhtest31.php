<?php
define('DH_TEST_MODE', true);
require 'includes/bootstrap.php';

$ok = 0; $bad = 0;
function ck31(string $l, bool $c, string $d = '') { global $ok,$bad; if ($c) { $ok++; } else { $bad++; echo "FAIL $l $d\n"; } }

// 1) 之前泄漏到「旧物杂物」的系统标记，现在必须是 hidden
foreach (['legend_fixed', 'rs_summ_hp', 'rs_point', 'exp_card_mult', 'gy_month', 'yz_month', 'twohand_fixed', 'avatar_anime_summoner', 'avatar_anime_mage'] as $m) {
    ck31("[$m] 被隐藏", mat_hidden($m) === true, '');
}

// 1.5) 有用途的道具不能当杂物（清罪丹/改名卡/龙鳞宝箱等）
foreach (['qingzui_dan', 'rename_card', 'dragon_box', 'dragon_pill'] as $m) {
    ck31("[$m] 判定为可用道具", is_usable_item($m) === true, '');
}
ck31('矿工铭牌已系统隐藏且不可用', mat_hidden('mine_badge') === true && is_usable_item('mine_badge') === false, '');
ck31('失控矿工不再掉矿工铭牌', monster_material('runaway_miner') === [], json_encode(monster_material('runaway_miner')));

// 2) 真实锻造材料/进度标记仍按规则处理，不能被误隐藏或误判
ck31('enhance_t1 不被隐藏', mat_hidden('enhance_t1') === false, '');
ck31('enhance_t1 是锻造材料', is_craft_mat('enhance_t1') === true, '');
ck31('副本进度标记隐藏', mat_hidden('vx_stage') === true && mat_hidden('dsw_enter') === true, '');
ck31('普通材料不隐藏', mat_hidden('slime_jelly') === false, '');

// 3) 模拟背包「旧物杂物」过滤：系统标记不得进 junk 列表
$mats = ['legend_fixed' => 1, 'rs_summ_hp' => 1, 'enhance_t1' => 5, 'slime_jelly' => 3];
$junk = [];
foreach ($mats as $mid => $num) {
    if (mat_hidden($mid) || is_usable_item($mid)) { continue; }
    if (!is_craft_mat($mid)) { $junk[$mid] = $num; continue; }
}
ck31('legend_fixed 不进杂物', !isset($junk['legend_fixed']), json_encode($junk));
ck31('rs_summ_hp 不进杂物', !isset($junk['rs_summ_hp']), json_encode($junk));

// 4) 背包 drop 处理器不再允许丢系统标记（静态检查）
$bag = file_get_contents(__DIR__ . '/bag.php');
ck31('drop 拦系统标记', str_contains($bag, "mat_hidden(\$did)"), '');

echo "\nOK=$ok FAIL=$bad\n";
