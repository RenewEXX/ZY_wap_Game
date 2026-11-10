<?php
define('DH_TEST_MODE', true);
require 'includes/bootstrap.php';
// 回归：dev 运行时覆盖层——写入覆盖后核心函数即时生效，清除后完全还原默认。
$ok = 0; $bad = 0;
function ck95($l, $c, $d = '') { global $ok, $bad; if ($c) { $ok++; } else { $bad++; echo "FAIL $l $d\n"; } }

// 干净起点
dev_ov_delete('goblin');
dev_ov_delete('wolf');

$baseName = monsters_base()['goblin']['name'];
$baseHp   = (int) monsters_base()['goblin']['hp'];
$baseLv   = monster_lv_base('goblin');
$baseEl   = monster_element_base('goblin');
$baseMat  = monster_material_base('goblin');
$baseDrop = monster_drops_base('goblin');

// 无覆盖时透传
ck95('无覆盖 name 透传', (monsters()['goblin']['name'] ?? '') === $baseName);
ck95('无覆盖 掉落实体透传', monster_drops('goblin') === $baseDrop);

// 统计覆盖
dev_ov_set('goblin', ['name' => '测试哥布林', 'hp' => 1234]);
ck95('覆盖 name', (monsters()['goblin']['name'] ?? '') === '测试哥布林', monsters()['goblin']['name'] ?? '');
ck95('覆盖 hp', (int) (monsters()['goblin']['hp'] ?? 0) === 1234, (string) (monsters()['goblin']['hp'] ?? ''));

// 等级 / 元素覆盖
dev_ov_set('wolf', ['lv' => 77, 'el' => 'ice']);
ck95('覆盖 lv', monster_lv('wolf') === 77, (string) monster_lv('wolf'));
ck95('覆盖 el', monster_element('wolf') === 'ice', monster_element('wolf'));
ck95('未覆盖的既有字段仍返回 base lv', monster_lv('goblin') === $baseLv);

// 材料 / 掉落 / 技能 覆盖
dev_ov_set('goblin', ['material' => ['soul_fire', '龙魂·火']]);
ck95('覆盖 material', monster_material('goblin') === ['soul_fire', '龙魂·火'], json_encode(monster_material('goblin')));

dev_ov_set('goblin', ['drops' => ['slot' => 'weapon', 'base' => '测试剑', 'rate' => 1.0, 'q' => [0, 0, 0, 100, 0]]]);
ck95('覆盖 drops', (monster_drops('goblin')['base'] ?? '') === '测试剑', (string) (monster_drops('goblin')['base'] ?? ''));

$sk = ['atk' => '测试普攻', 'skills' => [['name' => '测试大招', 'rate' => 50, 'mult' => 2.0]]];
dev_ov_set('goblin', ['skills' => $sk]);
ck95('覆盖 skills', (monster_skills()['goblin'] ?? null) == $sk, json_encode(monster_skills()['goblin'] ?? null));

// 清除覆盖 → 还原
dev_ov_delete('goblin');
dev_ov_delete('wolf');
ck95('清除后 name 还原', (monsters()['goblin']['name'] ?? '') === $baseName, monsters()['goblin']['name'] ?? '');
ck95('清除后 hp 还原', (int) (monsters()['goblin']['hp'] ?? 0) === $baseHp);
ck95('清除后 lv 还原', monster_lv('wolf') === monster_lv_base('wolf'));
ck95('清除后 el 还原', monster_element('wolf') === monster_element_base('wolf'));
ck95('清除后 material 还原', monster_material('goblin') === $baseMat);
ck95('清除后 drops 还原', monster_drops('goblin') === $baseDrop);
ck95('清除后 skills 还原', (monster_skills()['goblin'] ?? null) === (monster_skills_base()['goblin'] ?? null));

// 收尾清理
dev_ov_delete('goblin');
dev_ov_delete('wolf');

echo "OK=$ok FAIL=$bad\n";
