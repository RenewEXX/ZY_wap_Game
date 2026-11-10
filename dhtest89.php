<?php
define('DH_TEST_MODE', true);
require 'includes/bootstrap.php';
// 回归：竞技场额外挑战次数改为「2魔钻买1次」（魔钻以 0.1 为单位存储，即扣 20）。
$ok = 0; $bad = 0;
function ck89(string $l, bool $c, string $d = '') { global $ok, $bad; if ($c) { $ok++; } else { $bad++; echo "FAIL $l $d\n"; } }

function mk89(int $diamonds): int {
    $uid = (int) user_create('zz89' . mt_rand(100000, 999999), 'x', 'warrior', 'z1', true, 'tmp');
    $u = user_by_id($uid);
    $u['diamonds'] = $diamonds;
    user_save($u);
    return $uid;
}

// 1) 恰好 2 魔钻（20 单位）：成功买 1 次
$uidA = mk89(20);
$msgA = arena_buy_chances($uidA, 'diamond');
$uA = user_by_id($uidA);
$mA = mats_of($uidA);
ck89('2魔钻购买成功', str_contains($msgA, '2魔钻买下1次'), $msgA);
ck89('扣光2魔钻（剩0）', (int) $uA['diamonds'] === 0, 'd=' . (int) $uA['diamonds']);
ck89('只增加1次挑战', (int) ($mA['arena_bought'] ?? 0) === 1, 'bought=' . (int) ($mA['arena_bought'] ?? 0));
db()->exec('DELETE FROM mats WHERE uid=' . $uidA);
db()->exec('DELETE FROM users WHERE id=' . $uidA);

// 2) 只有 1.9 魔钻（19 单位）：不足，购买失败且不扣钻
$uidB = mk89(19);
$msgB = arena_buy_chances($uidB, 'diamond');
$uB = user_by_id($uidB);
ck89('不足2魔钻购买失败', str_contains($msgB, '魔钻不够'), $msgB);
ck89('失败不扣钻', (int) $uB['diamonds'] === 19, 'd=' . (int) $uB['diamonds']);
db()->exec('DELETE FROM mats WHERE uid=' . $uidB);
db()->exec('DELETE FROM users WHERE id=' . $uidB);

echo "\nOK=$ok FAIL=$bad\n";
