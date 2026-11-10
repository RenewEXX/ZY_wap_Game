<?php
define('DH_TEST_MODE', true);
require 'includes/bootstrap.php';
// 回归：风龙艾瑟琳幼体·无羁苍岚（风味重做）
//   30% 化风遁走（完全闪避并积蓄「风迹」）→ 遁走后下一击必暴增伤；
//   攻击 40% 追加「风之残响」（55% 伤害）。
$ok = 0; $bad = 0;
function ck90(string $l, bool $c, string $d = '') { global $ok, $bad; if ($c) { $ok++; } else { $bad++; echo "FAIL $l $d\n"; } }

// 1) 被动文案
$sp = pet_species()['sky_serpent'];
ck90('被动含 无羁苍岚', str_contains($sp['passive'], '无羁苍岚'), $sp['passive']);
ck90('被动含 化风遁走', str_contains($sp['passive'], '化风遁走'), $sp['passive']);
ck90('被动含 风之残响', str_contains($sp['passive'], '风之残响'), $sp['passive']);

// 建一只出战的风龙（大血量，避免测试中被打死）
$uid = (int) user_create('zz90' . mt_rand(100000, 999999), 'x', 'warrior', 'z1', true, 'tmp');
$bigHp = 10000000;
db()->prepare('INSERT INTO pets (uid, species, quality, level, exp, hp, status, active, created_at) VALUES (?,?,?,?,0,?,"normal",1,?)')
    ->execute([$uid, 'sky_serpent', 'legend', 5, $bigHp, time()]);
$pid90 = (int) db()->lastInsertId();
mat_set($uid, 'pet_sat_' . $pid90, time() + 999999); // 测试期间不增长疲劳

// 2) 化风遁走：约 30% 概率，且遁走后积蓄风迹
$dodges = 0; $hits = 0; $N = 400;
for ($i = 0; $i < $N; $i++) {
    $before = (int) db()->query("SELECT hp FROM pets WHERE uid=$uid")->fetchColumn();
    $msg = pet_hurt($uid, 2000);
    $after = (int) db()->query("SELECT hp FROM pets WHERE uid=$uid")->fetchColumn();
    if (str_contains($msg, '苍岚')) {
        $dodges++;
        if ($after !== $before) { ck90('遁走时不应掉血', false, $before . '->' . $after); }
    } elseif ($after < $before) {
        $hits++;
    }
}
ck90('出现化风遁走', $dodges > 0, 'dodges=' . $dodges);
ck90('也存在正常受击', $hits > 0, 'hits=' . $hits);
ck90('遁走率大致合理(10%~55%)', $dodges >= $N * 0.10 && $dodges <= $N * 0.55, 'dodges=' . $dodges . '/' . $N);
ck90('遁走后积蓄风迹', (int) (mats_of($uid)['pet_wind_mark'] ?? 0) === 1);

// 3) 风迹：下一击必暴增伤，且消耗风迹
mat_set($uid, 'pet_wind_mark', 1);
$b = ['id' => 'dummy', 'name' => '木桩', 'hp' => 1000000000, 'maxhp' => 1000000000];
$u = user_by_id($uid);
$log = pet_attack($u, $b);
ck90('风迹使下一击必暴增伤', str_contains($log, '风迹·来去无痕'), $log);
ck90('风迹出手后被消耗', (int) (mats_of($uid)['pet_wind_mark'] ?? 0) === 0);

// 4) 风之残响：攻击有概率追加一击
$echo = 0; $M = 120;
for ($i = 0; $i < $M; $i++) {
    $u = user_by_id($uid);
    $b = ['id' => 'dummy', 'name' => '木桩', 'hp' => 1000000000, 'maxhp' => 1000000000];
    $log = pet_attack($u, $b);
    if (str_contains($log, '风之残响')) { $echo++; }
}
ck90('出现风之残响', $echo > 0, 'echo=' . $echo);
ck90('风之残响率大致合理(15%~70%)', $echo >= $M * 0.15 && $echo <= $M * 0.70, 'echo=' . $echo . '/' . $M);

db()->exec('DELETE FROM pets WHERE uid=' . $uid);
db()->exec('DELETE FROM mats WHERE uid=' . $uid);
db()->exec('DELETE FROM users WHERE id=' . $uid);

echo "\nOK=$ok FAIL=$bad\n";
