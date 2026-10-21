<?php
define('DH_TEST_MODE', true);
require 'includes/bootstrap.php';
$ok = 0; $bad = 0;
function ck11(string $l, $got, $want) { global $ok,$bad; if ($got === $want) { $ok++; } else { $bad++; echo "FAIL $l: got=",var_export($got,true)," want=",var_export($want,true),"\n"; } }
function gt11(string $l, $a, $b) { global $ok,$bad; if ($a > $b) { $ok++; } else { $bad++; echo "FAIL $l: $a <= $b\n"; } }

$cat = skill_catalog();

echo "=== 战士：两方向标签 ===\n";
// 防战：吃双防 + 触发反击
$tank = ['w1','w3','w6','w8'];
// 狂战：吃本场损血数值
$rage = ['w2','w4','w5','w7','w9'];
foreach ($tank as $s) { ck11("$s 有 tank 标签", !empty($cat[$s]['tank']), true); ck11("$s 无 rage", empty($cat[$s]['rage']), true); }
foreach ($rage as $s) { ck11("$s 有 rage 标签", !empty($cat[$s]['rage']), true); ck11("$s 无 tank", empty($cat[$s]['tank']), true); }
ck11('战士9技能都归入两方向', count($tank) + count($rage), 9);

echo "\n=== 狂战：按损失生命「数值」而非百分比 ===\n";
$b = [];
ck11('无掉血时系数=1.0', warr_rage_mult($b, 0), 1.0);
// 关键：同样的掉血数，在血量不同(百分比不同)的两个人身上，加成必须相同
$b1 = ['warr_hp_lost' => 5000];   // 掉了5000血
ck11('掉5000血 → +50%', warr_rage_mult($b1, 0), 1.5);
$b2 = ['warr_hp_lost' => 10000];
ck11('掉10000血 → +100%', warr_rage_mult($b2, 0), 2.0);
$b3 = ['warr_hp_lost' => 100000];
ck11('封顶 +200%', warr_rage_mult($b3, 0), 3.0);
$b4 = ['warr_hp_lost' => 500];
ck11('掉500血 → +5%', warr_rage_mult($b4, 0), 1.05);
// 核心断言：纯数值驱动，与当前血量百分比无关
$bA = ['warr_hp_lost' => 3000];
$bB = ['warr_hp_lost' => 3000, 'maxhp' => 999999, 'hp' => 1];  // 掉光了
ck11('同一掉血数→同一系数(与百分比无关)', warr_rage_mult($bA,0), warr_rage_mult($bB,0));
ck11('累加', warr_add_hp_lost($bA, 2000), 5000);

echo "\n=== 防战：按防御等比例反击 ===\n";
$uid = user_create('tw11b', 'x', 'warrior', 'z1', true, 'tmp');
$_SESSION['uid'] = $uid;
$u = user_by_id($uid);
$u['def'] = 2000; user_save($u);
$u = user_by_id($uid);
$tw = warr_thorns($u, 1.0);
gt11('有防御就有反击', $tw, 0);
// 防御翻倍 → 反击翻倍（等比例）
$u2 = $u; $u2['def'] = 4000;
$tw2 = warr_thorns($u2, 1.0);
ck11('防御翻倍→反击翻倍', $tw2, $tw * 2);
// 2觉铁卫 +35%
cflag_set($uid, 'rb2_job', 'aegis');
$twA = warr_thorns(user_by_id($uid), 1.0);
gt11('铁卫反击>普通', $twA, $tw);
cflag_set($uid, 'rb2_job', '');
cflag_set($uid, 'rb2_job', 'berserker');
ck11('狂战士狂战上限+25%', warr_rage_cap_for($uid), 225.0);
cflag_set($uid, 'rb2_job', '');

echo "\n=== 猎手：游侠群攻DOT / 刺客单体爆发 ===\n";
ck11('h5 群攻', $cat['h5']['kind'], 'aoe');
ck11('h7 群攻', $cat['h7']['kind'], 'aoe');
ck11('h6 改单体 step', $cat['h6']['kind'], 'step');
ck11('h8 改单体 step', $cat['h8']['kind'], 'step');
gt11('h6倍率>h5(刺客单体更高)', $cat['h6']['mult'], $cat['h5']['mult']);
gt11('h8倍率>h6', $cat['h8']['mult'], $cat['h6']['mult']);
gt11('h8倍率>h7', $cat['h8']['mult'], $cat['h7']['mult']);
ck11('h6 assassin标签', !empty($cat['h6']['assassin']), true);
ck11('h8 assassin标签', !empty($cat['h8']['assassin']), true);
ck11('h5 ranger标签', !empty($cat['h5']['ranger']), true);
ck11('h7 ranger标签', !empty($cat['h7']['ranger']), true);
// 游侠DOT加成
cflag_set($uid, 'rb2_job', 'ranger');
gt11('游侠DOT加成>1', hunter_dot_mult(user_by_id($uid)), 1.0);
ck11('游侠铺层更快', hunter_ranger_stacks('h5', 10, true), 4);
cflag_set($uid, 'rb2_job', 'assassin');
ck11('刺客无DOT加成', hunter_dot_mult(user_by_id($uid)), 1.0);
gt11('刺客单体加成>1', hunter_assassin_bonus(user_by_id($uid)), 1.0);
ck11('刺客闪避+18', hunter_assassin_dodge($uid), 18);
cflag_set($uid, 'rb2_job', '');
ck11('无分支无闪避', hunter_assassin_dodge($uid), 0);
// DOT乘数确实生效（先切回游侠）
cflag_set($uid, 'rb2_job', 'ranger');
$bT = ['hp'=>1000,'maxhp'=>1000,'dot_bleed'=>3,'dot_bleed_turns'=>3,'dot_bleed_per'=>10];
$base = hunter_dot_tick($bT, 1.0)['dmg'];
$bT2 = ['hp'=>1000,'maxhp'=>1000,'dot_bleed'=>3,'dot_bleed_turns'=>3,'dot_bleed_per'=>10];
$ranger = hunter_dot_tick($bT2, hunter_dot_mult(user_by_id($uid)))['dmg'];
ck11('未觉醒无额外', $base, 30);
gt11('游侠DOT更高', $ranger, $base);
cflag_set($uid, 'rb2_job', '');

echo "\n=== 职业背景文案 ===\n";
foreach (['warrior','mage','hunter','priest','summoner'] as $j) {
    $f = job_flavor($j);
    ck11("$j 有背景", $f['bg'] !== '', true);
    ck11("$j 有特色", $f['feat'] !== '', true);
}
ck11('未知职业不报错', job_flavor('nope')['bg'], '');

echo "\n=== 竞技场假人名 ===\n";
$pool = arena_name_pool();
ck11('名字池≥150', count($pool) >= 150, true);
ck11('名字池无重复', count($pool), count(array_unique($pool)));
ck11('都在长度限制内', count(array_filter($pool, fn($n)=>mb_strlen($n) <= 12)) === count($pool), true);
ck11('无旧武侠味残留', count(array_filter($pool, fn($n)=>in_array($n,['剑影','孤狼','断魂'],true))) , 0);
ck11('含网感结构', (bool) count(array_filter($pool, fn($n)=>str_contains($n,'与'))), true);
ck11('含handle符号', (bool) count(array_filter($pool, fn($n)=>str_contains($n,'.'))), true);
echo "样例: " . implode(' / ', array_slice($pool, 0, 14)) . "\n";

db()->exec('DELETE FROM equips WHERE uid=' . (int) $uid);
db()->exec('DELETE FROM users WHERE id=' . (int) $uid);
$_SESSION = [];
echo "\nOK=$ok FAIL=$bad\n";
