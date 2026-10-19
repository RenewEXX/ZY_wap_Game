<?php
define('DH_TEST_MODE', true);
require 'includes/bootstrap.php';
$ok = 0; $bad = 0;
function chk6(string $l, $got, $want) { global $ok,$bad; if ($got === $want) { $ok++; } else { $bad++; echo "FAIL $l: got=",var_export($got,true)," want=",var_export($want,true),"\n"; } }

// —— 缺失BOSS已补 ——
$sk = monster_skills();
foreach (['swamp_king', 'vx_tyrant', 'ry_titan'] as $m) {
    chk6("$m 有技能表", isset($sk[$m]) && !empty($sk[$m]['skills']), true);
}
// 24 个地图BOSS全部有技能
$n = 0;
foreach (locations() as $lid => $l) {
    $b = boss_of_map($lid);
    if ($b !== '' && isset(monsters()[$b])) {
        $n++;
        if (empty($sk[$b]['skills'] ?? [])) { $bad++; echo "FAIL boss无技能: $b\n"; } else { $ok++; }
    }
}
echo "地图BOSS检查数: $n\n";

// —— 龙王独有招式 ——
foreach (['doyle', 'aetherine', 'ysolde', 'rex', 'nidhogg', 'aurelian'] as $m) {
    $seen = [];
    for ($i = 0; $i < 30; $i++) { $mv = drg_boss_move($m); $seen[$mv['name']] = true; }
    $hasSig = false;
    foreach (array_keys($seen) as $nm) { if (str_contains($nm, '柱·') || str_contains($nm, '柱')) { $hasSig = true; } }
    chk6("$m 有柱式大招", $hasSig, true);
    chk6("$m 招式数>=3", count($seen) >= 3, true);
    $el0 = dragon($m)['el'] ?? '';
    foreach (range(1, 10) as $_) { chk6("$m 元素恒为$el0", drg_boss_move($m)['el'], $el0); }
}
// 旧式传元素仍兼容
chk6('旧传元素fire', drg_boss_move('fire')['el'], 'fire');

// —— 排版函数 ——
chk6('空串', battle_log_html(''), '');
$h1 = battle_log_html('你放出重斩，造成12点。影蚀君王阿巴顿使出【堕天十字斩】（暗）！你掉2点生命。');
chk6('我方绿色', str_contains($h1, 'color:#8f8'), true);
chk6('敌方红色', str_contains($h1, 'color:#f88'), true);
chk6('伤害高亮', str_contains($h1, 'ffd24a'), true);
chk6('三句三行', substr_count($h1, '<div'), 3);
$h2 = battle_log_html('你的攻击被闪避了。');
chk6('闪避黄色', str_contains($h2, 'color:#fa6'), true);
$h3 = battle_log_html('喝下【苔语药膏】，回复8（20%）。');
chk6('喝药绿色', str_contains($h3, 'color:#8f8'), true);
$h4 = battle_log_html('<script>alert(1)</script>你掉5点生命。');
chk6('XSS转义', str_contains($h4, '<script>'), false);
chk6('转义后仍分行', str_contains($h4, 'color:#f88'), true);

echo "OK=$ok FAIL=$bad\n";