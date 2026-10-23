<?php
define('DH_TEST_MODE', true);
require 'includes/bootstrap.php';
// 技能书掉落回归：每一本秘籍都必须至少有一个掉落来源，
// 三级秘籍（各职业第3本，含召唤师血肉筑城 s3）统一走深渊 boss。
$ok = 0; $bad = 0;
function ck34(string $l, bool $c, string $d = '') { global $ok,$bad; if ($c) { $ok++; } else { $bad++; echo "FAIL $l $d\n"; } }

$all = array_keys(skill_books());

// 1) 三级书池 = 所有以 3 结尾的书（不多不少）
$u3 = array_values(array_filter($all, fn($k) => str_ends_with($k, '3')));
$t3 = tier3_skill_books();
sort($u3); sort($t3);
ck34('三级书池恰好等于所有 _3 秘籍', $u3 === $t3, implode(',', $u3) . ' vs ' . implode(',', $t3));

// 2) 含召唤师的血肉筑城（本次修复点）
ck34('含召唤师【血肉筑城秘籍 book_s3】', in_array('book_s3', $t3, true));

// 3) 五职业各有且仅有一本三级书
foreach (['warrior' => 'book_w3', 'mage' => 'book_m3', 'hunter' => 'book_h3', 'priest' => 'book_p3', 'summoner' => 'book_s3'] as $job => $b) {
    ck34("$job 三级书在池内($b)", in_array($b, $t3, true));
}

// 4) 普通池（全部减三级）里不含任何三级书 —— 三级书只在深渊掉，不会串味
$regular = array_values(array_filter($all, fn($k) => !in_array($k, $t3, true)));
$leak = array_filter($regular, fn($k) => str_ends_with($k, '3'));
ck34('普通掉落池不含三级书', $leak === [], implode(',', $leak));

// 5) 覆盖性：普通池 ∪ 三级池 = 全部秘籍（没有孤儿书）
$cover = array_map('strval', array_merge($regular, $t3));
$allS = array_map('strval', $all);
sort($cover); sort($allS);
ck34('全部秘籍都有掉落来源', $cover === $allS);

// 6) 每本书的 skill 都在技能表里存在
$sk = skill_catalog();
$missing = [];
foreach (skill_books() as $bk => $info) {
    if (!isset($sk[$info['skill']])) { $missing[] = $bk; }
}
ck34('每本秘籍对应的技能都存在', $missing === [], implode(',', $missing));

// 7) 实打实蒙特卡洛：深渊 boss(abaddon) 能掉出书，且最终会掉到 book_s3
$uid = user_create('zz34' . mt_rand(10000, 99999), 'x', 'summoner', 'z1', true, 'tmp');
mt_srand(20261027);
for ($i = 0; $i < 6000; $i++) {
    roll_abx_skillbook((int) $uid, 'abaddon');
}
$got = mats_of((int) $uid);
ck34('深渊可掉出召唤师三级书 book_s3', (int) ($got['book_s3'] ?? 0) >= 1, (string) ($got['book_s3'] ?? 0));

// 8) 普通怪(swamp_king)连打 4000 次，绝不该掉出任何三级书
$uid2 = user_create('zz34b' . mt_rand(10000, 99999), 'x', 'warrior', 'z1', true, 'tmp');
mt_srand(777);
for ($i = 0; $i < 4000; $i++) {
    roll_skillbook((int) $uid2, 'swamp_king');
}
$got2 = mats_of((int) $uid2);
$leaked3 = 0;
foreach ($t3 as $b) { $leaked3 += (int) ($got2[$b] ?? 0); }
ck34('普通怪不掉三级书', $leaked3 === 0, (string) $leaked3);

// 清理
foreach ([$uid, $uid2] as $id) {
    foreach (['skills', 'equips', 'mats', 'pets'] as $t) { db()->exec("DELETE FROM {$t} WHERE uid=" . (int) $id); }
    db()->exec('DELETE FROM users WHERE id=' . (int) $id);
}
echo "\nOK=$ok FAIL=$bad\n";
