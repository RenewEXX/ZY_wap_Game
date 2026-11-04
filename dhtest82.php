<?php
// dhtest82：离线/陪练 召唤师“打满回合僵持”不再拖垮整段离线
//  - 旧行为：idle_fight_once 打满回合返回 'fight'，offline/dummy 直接 break → 1 场 0 胜 0 金
//  - 新行为：idle_resolve_fight 血线稳 → 判持久战拖赢并结算（有胜场/金币）；血线不稳 → skip 继续下一场
define('DH_TEST_MODE', true);
require 'includes/bootstrap.php';
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
$ok = 0; $bad = 0;
function ck82(string $l, bool $c, string $d = '') { global $ok, $bad; if ($c) { $ok++; } else { $bad++; echo "FAIL $l $d\n"; } }

function mk82(string $job, string $skill, int $lv, int $maxmp, int $php, int $intv, int $atk): int
{
    $uid = (int) user_create('rb82' . mt_rand(100000, 999999), 'x', $job, 'z1', true, 'tmp');
    $u = user_by_id($uid);
    $u['lv'] = $lv; $u['maxhp'] = $php; $u['hp'] = $php; $u['maxmp'] = $maxmp; $u['mp'] = $maxmp;
    $u['int'] = $intv; $u['atk'] = $atk;
    user_save($u);
    cflag_set($uid, 'rb1_job', $job);
    cflag_set($uid, 'sin_f', 10); cflag_set($uid, 'sin', 10);
    db()->exec('DELETE FROM skills WHERE uid=' . $uid);
    db()->prepare('INSERT INTO skills (uid,skill,level,prof,tier,active) VALUES (?,?,20,20,10,1)')->execute([$uid, $skill]);
    return $uid;
}
function mon82(int $hp, int $atk, int $num, int $gold = 500): array
{
    return ['id' => 'sim', 'name' => '怪', 'hp' => $hp, 'maxhp' => $hp, 'atk' => $atk, 'def' => 0, 'mdef' => 0,
        'exp' => 100, 'gold' => $gold, 'log' => '', 'num' => $num, 'left' => $num, 'wexp' => 0, 'wgold' => 0, 'drops' => [], 'last' => time()];
}
function rm82(int $uid): void
{
    db()->prepare('DELETE FROM users WHERE id=?')->execute([$uid]);
    db()->prepare('DELETE FROM skills WHERE uid=?')->execute([$uid]);
    db()->prepare('DELETE FROM mats WHERE uid=?')->execute([$uid]);
}

// ---- 1) 召唤师持久战：单体召唤兽清不掉整组怪，打满回合仍活着 -> 拖赢并结算 ----
$uid = mk82('summoner', 's1', 220, 50 + 5 * 220, 200000, 50, 30);
$_SESSION = ['uid' => $uid];
$u = user_by_id($uid);
$b = mon82(500000, 0, 6);
$r0 = idle_fight_once($u, $b, 60);
ck82('先复现：打满回合仍是僵持', $r0['status'] === 'fight', 'status=' . $r0['status']);
$gold0 = (int) $u['gold'];
$u = user_by_id($uid);
$b = mon82(500000, 0, 6);
$r = idle_resolve_fight($u, $b, 60);
ck82('持久战判定为胜利', $r['status'] === 'victory', 'status=' . $r['status']);
ck82('持久战有金币入账', (int) $u['gold'] > $gold0, 'gold ' . $gold0 . '->' . (int) $u['gold']);
rm82($uid);

// ---- 2) 血线不稳（开打时就残血）-> skip，不白送胜利，也不拖垮整段 ----
$uid = mk82('warrior', 'w1', 220, 50 + 2 * 220, 1000, 0, 1000);
$u = user_by_id($uid);
$u['hp'] = 400; user_save($u); // 400/1000 = 40% < 50%
$_SESSION = ['uid' => $uid];
$u = user_by_id($uid);
$b = mon82(2000000, 0, 1); // 怪血极厚打不死、也不打人；僵持后血线<50% -> 只能算没打过
$r = idle_resolve_fight($u, $b, 60);
ck82('血线不稳返回 skip', $r['status'] === 'skip', 'status=' . $r['status'] . ' hp=' . (int) $u['hp'] . '/' . (int) $u['maxhp']);
rm82($uid);

// ---- 3) 普通能打赢的战斗仍正常胜利（不误判） ----
$uid = mk82('summoner', 's7', 220, 50 + 5 * 220, 5000, 800, 30);
$_SESSION = ['uid' => $uid];
$u = user_by_id($uid);
$b = mon82(3000, 0, 1);
$r = idle_resolve_fight($u, $b, 60);
ck82('普通战斗正常胜利', $r['status'] === 'victory', 'status=' . $r['status']);
rm82($uid);

echo "\nOK=$ok FAIL=$bad\n";
