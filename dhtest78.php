<?php
// dhtest78：副本怪物战斗日志不展示内部英文动作标记
define('DH_TEST_MODE', true);
require 'includes/bootstrap.php';
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
$ok = 0; $bad = 0;
function ck78(string $label, bool $condition, string $detail = ''): void
{
    global $ok, $bad;
    if ($condition) { $ok++; } else { $bad++; echo "FAIL $label $detail\n"; }
}
$skills = region_dungeon_skills();
$badNames = [];
foreach ($skills as $mid => $cfg) {
    $atk = (string) ($cfg['atk'] ?? '');
    if ($atk !== '' && preg_match('/[a-z]/i', $atk)) { $badNames[$mid] = $atk; }
    foreach (($cfg['skills'] ?? []) as $skill) {
        $name = (string) ($skill['name'] ?? '');
        if ($name !== '' && preg_match('/[a-z]/i', $name)) { $badNames[$mid . ':skill'] = $name; }
    }
}
ck78('区域副本动作名/技能名没有内部英文标记', $badNames === [], json_encode($badNames, JSON_UNESCAPED_UNICODE));
ck78('hiero映射为中文招牌动作', region_atk_name('hiero') === '诵经噬魂', region_atk_name('hiero'));
ck78('未知标记安全回退为中文动作', region_atk_name('unknown_token') === '攻击', region_atk_name('unknown_token'));

$bossId = 'rd_sunk_hierophant';
$cfg = $skills[$bossId] ?? [];
$b = ['id' => $bossId, 'name' => '咸海诵经者·溺声'];
$u = ['id' => 0, 'hp' => 100000, 'maxhp' => 100000];
$found = false;
for ($i = 0; $i < 100 && !$found; $i++) {
    $turn = monster_skill_turn($u, $b, 1);
    $found = str_contains((string) ($turn['log'] ?? ''), '诵经噬魂') || str_contains((string) ($turn['log'] ?? ''), '咸海诵经者·溺声·');
}
ck78('咸海诵经者战斗普攻显示中文', $found, (string) ($turn['log'] ?? ''));

echo "OK=$ok FAIL=$bad\n";
