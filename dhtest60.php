<?php
define('DH_TEST_MODE', true);
require 'includes/bootstrap.php';
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
// 技能学习规则：有书就能学（去前置链），但按技能书掉落地图等级卡角色等级；五职业同阶同门槛
$ok = 0; $bad = 0;
function ck60(string $l, bool $c, string $d = '') { global $ok, $bad; if ($c) { $ok++; } else { $bad++; echo "FAIL $l $d\n"; } }
function mk60(string $job, int $lv): int {
    $uid = (int) user_create('t60' . mt_rand(100000, 999999), 'x', $job, 'z1', true, 'tmp');
    $u = user_by_id($uid); $u['lv'] = $lv; $u['last_seen'] = time(); user_save($u);
    return $uid;
}
function hasSkill60(int $uid, string $sid): bool {
    $st = db()->prepare('SELECT 1 FROM skills WHERE uid=? AND skill=?');
    $st->execute([$uid, $sid]);
    return (bool) $st->fetch();
}
function clean60(int $uid): void {
    db()->prepare('DELETE FROM users WHERE id=?')->execute([$uid]);
    db()->prepare('DELETE FROM skills WHERE uid=?')->execute([$uid]);
    db()->prepare('DELETE FROM mats WHERE uid=?')->execute([$uid]);
    db()->prepare('DELETE FROM equips WHERE uid=?')->execute([$uid]);
}

// 1) 各阶最低等级（五职业同阶同门槛）
$expect = ['1' => 1, '2' => 10, '3' => 50, '4' => 150, '5' => 580, '6' => 580, '7' => 600, '8' => 600, '9' => 700];
foreach ($expect as $n => $lv) {
    foreach (['w' => 'warrior', 'm' => 'mage', 'h' => 'hunter', 'p' => 'priest', 's' => 'summoner'] as $pre => $job) {
        $book = 'book_' . $pre . $n;
        ck60("min_lv {$book}={$lv}", skill_book_min_lv($book) === $lv, (string) skill_book_min_lv($book));
    }
}

// 2) 有书就能学：召唤师 400 级，只有 book_s3（没有 s1/s2），应能直接学会 s3
$sum = mk60('summoner', 400);
add_mat($sum, 'book_s3', 1);
$r = use_skill_book($sum, 'book_s3');
ck60('召唤师400级仅凭书直接学血肉筑城(无前置)', hasSkill60($sum, 's3'), $r);
ck60('书被消耗', (int) (mats_of($sum)['book_s3'] ?? 0) === 0);
clean60($sum);

// 3) 等级不足被拦：召唤师 49 级拿 book_s3（需50）不能学
$sum2 = mk60('summoner', 49);
add_mat($sum2, 'book_s3', 1);
$r = use_skill_book($sum2, 'book_s3');
ck60('49级学3阶被拦', !hasSkill60($sum2, 's3') && (int) (mats_of($sum2)['book_s3'] ?? 0) === 1, $r);
ck60('拦截提示含等级', str_contains($r, '50'), $r);
clean60($sum2);

// 4) 战士 50 级学 w3；49 级被拦
$war = mk60('warrior', 50);
add_mat($war, 'book_w3', 1);
$r = use_skill_book($war, 'book_w3');
ck60('战士50级学裂地(w3)', hasSkill60($war, 'w3'), $r);
clean60($war);
$war2 = mk60('warrior', 49);
add_mat($war2, 'book_w3', 1);
use_skill_book($war2, 'book_w3');
ck60('战士49级学w3被拦', !hasSkill60($war2, 'w3'));
clean60($war2);

// 5) 2觉群攻书仍要2觉：战士 600 级未2觉学 w5 被拦；2觉后可学（且无需前置 w1..w4）
$war3 = mk60('warrior', 600);
add_mat($war3, 'book_w5', 1);
$r = use_skill_book($war3, 'book_w5');
ck60('未2觉学w5被拦', !hasSkill60($war3, 'w5'), $r);
cflag_set($war3, 'rb2_job', 'berserker');
$r = use_skill_book($war3, 'book_w5');
ck60('2觉后仅凭书直接学w5(无前置)', hasSkill60($war3, 'w5'), $r);
clean60($war3);

// 6) 职业不符仍拦
$mag = mk60('mage', 700);
add_mat($mag, 'book_w3', 1);
$r = use_skill_book($mag, 'book_w3');
ck60('法师不能学战士书', !hasSkill60($mag, 'w3'), $r);
clean60($mag);

// 7) 升级路径：已有技能未满20级时用同书升级
$war4 = mk60('warrior', 60);
add_mat($war4, 'book_w3', 1);
use_skill_book($war4, 'book_w3');
add_mat($war4, 'book_w3', 1);
$r = use_skill_book($war4, 'book_w3');
$st = db()->prepare('SELECT level FROM skills WHERE uid=? AND skill=?'); $st->execute([$war4, 'w3']);
ck60('重复使用升级技能', (int) $st->fetchColumn() === 2, $r);
clean60($war4);

echo "OK=$ok FAIL=$bad\n";
