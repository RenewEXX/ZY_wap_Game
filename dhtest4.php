<?php
define('DH_TEST_MODE', true);
require 'includes/bootstrap.php';
$ok = 0; $bad = 0;
function chk4(string $label, $got, $want) { global $ok,$bad; if ($got === $want) { $ok++; } else { $bad++; echo "FAIL $label: got=", var_export($got,true), " want=", var_export($want,true), "\n"; } }

// —— 猎人DOT ——
$b = ['id' => 'goblin', 'name' => '测试怪', 'hp' => 10000, 'maxhp' => 10000];
chk4('出血挂层', str_contains(hunter_dot_add($b, 'bleed', 1, 1000, false), '出血1层'), true);
chk4('出血叠加', str_contains(hunter_dot_add($b, 'bleed', 2, 1000, false), '出血3层'), true);
chk4('出血封顶', str_contains(hunter_dot_add($b, 'bleed', 5, 1000, false), '出血3层'), true);
$dt = hunter_dot_tick($b);
chk4('出血结算>', $dt['dmg'] >= 3 * 180 * 0.9, true);
chk4('出血层数保持', (int) ($b['dot_bleed'] ?? 0), 3);
chk4('出血轮数衰减', (int) ($b['dot_bleed_turns'] ?? 0), 3);
$b2 = ['id' => 'goblin', 'name' => '毒怪', 'hp' => 20000, 'maxhp' => 20000];
hunter_dot_add($b2, 'poison', 2, 0, false);
chk4('剧毒百分比', hunter_dot_tick($b2)['dmg'], (int) (20000 * 0.01 * 2));
// 剧毒硬上限：超血目标 + 满层剧毒，单次结算不应超过传入的硬上限（防止被最大生命比例带飞）
$mkHuge = function () {
    return ['id' => 'goblin', 'name' => '超血', 'hp' => 9000000000, 'maxhp' => 9000000000,
            'dot_lv20' => true, 'dot_poison' => 5, 'dot_poison_turns' => 4];
};
$rawHuge = $mkHuge(); $capHuge = $mkHuge();
$dmgNoCap = hunter_dot_tick($rawHuge, 1.0, 0)['dmg'];
$dmgCapped = hunter_dot_tick($capHuge, 1.0, 50000)['dmg'];
chk4('剧毒无上限时按比例很大', $dmgNoCap > 1000000, true);
chk4('剧毒硬上限生效', $dmgCapped <= 50000, true);
chk4('剧毒硬上限确实压低', $dmgCapped < $dmgNoCap, true);
$b3 = ['id' => 'goblin', 'name' => '引燃怪', 'hp' => 30000, 'maxhp' => 30000];
hunter_dot_add($b3, 'bleed', 3, 2000, true);
hunter_dot_add($b3, 'burn', 2, 2000, true);
$det = hunter_dot_detonate($b3, 1.0, true);
chk4('引爆>0', $det['dmg'] > 500, true);
chk4('引爆清空', hunter_dot_stacks($b3), []);

// —— 召唤师契匣 ——
chk4('契匣表非空', count(summoner_offhands()) >= 10, true);
chk4('等级推荐基底', summoner_offhand_for_lv(150), '引魂旧匣');
chk4('低等级无传说', summoner_offhand_for_lv(150, true), null);
chk4('高等级传说', summoner_offhand_for_lv(800, true), '幽冥·招魂匣');
chk4('传说基底标记', summoner_offhands()['幽冥·招魂匣']['legend'], true);
chk4('普通基底标记', summoner_offhands()['裹魂契匣']['legend'], false);
chk4('契匣表无q字段', isset(summoner_offhands()['寒霜·契匣']['q']), false);
chk4('识别契匣', summoner_is_box('寒霜·契匣'), true);
chk4('识别普通', summoner_is_box('大骨棒'), false);
// 主手专属：魂契·深渊引魂灯是主手武器，不该出现在契匣(副手)表里，也不能触发开场召唤
chk4('契匣表不含主手引魂灯', isset(summoner_offhands()['魂契·深渊引魂灯']), false);
chk4('引魂灯不是契匣', summoner_is_box('魂契·深渊引魂灯'), false);
// 伪造穿戴测试
$u1 = user_by_id(1);
if ($u1) {
    db()->prepare('UPDATE equips SET pos="" WHERE uid=? AND slot="offhand" AND pos="wear"')->execute([(int)$u1['id']]);
    $eid = db()->query('SELECT MAX(id) AS m FROM equips')->fetchColumn();
    db()->prepare('INSERT INTO equips (uid, pos, slot, name, quality, affixes, item_level) VALUES (?, "wear", "offhand", "寒霜·契匣", 3, "[]", 460)')->execute([(int)$u1['id']]);
    $worn = summoner_offhand_worn((int)$u1['id']);
    chk4('穿戴识别', $worn['name'] ?? '', '寒霜·契匣');
    chk4('开场单位', $worn['cfg']['summon'] ?? '', 'bone');
    chk4('上限不降低', summon_cap($u1) >= 2, true);
    db()->exec('DELETE FROM equips WHERE uid=' . (int)$u1['id'] . ' AND slot="offhand" AND name="寒霜·契匣" AND id>' . (int)$eid);
}

// —— 转生龙魂 ——
chk4('龙魂1转', rs_soul_cost(1), 3);
chk4('龙魂2转', rs_soul_cost(2), 6);
chk4('龙魂3转', rs_soul_cost(3), 9);
$rq = rs_requirements(1);
chk4('转生要龙魂', isset($rq['dsoul']) && isset($rq['soul_need']), true);

echo "OK=$ok FAIL=$bad\n";
