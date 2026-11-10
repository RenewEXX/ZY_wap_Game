<?php
define('DH_TEST_MODE', true);
require 'includes/bootstrap.php';
// 回归：10阶难度大幅提高——高阶熟练度门槛成倍拉高，且升阶逻辑按新门槛生效。
$ok = 0; $bad = 0;
function ck88(string $l, bool $c, string $d = '') { global $ok, $bad; if ($c) { $ok++; } else { $bad++; echo "FAIL $l $d\n"; } }

// 1) 门槛单调递增
$mono = true;
for ($t = 2; $t <= 10; $t++) { if (tier_prof_need($t) <= tier_prof_need($t - 1)) { $mono = false; } }
ck88('熟练度门槛逐阶递增', $mono);

// 2) 10阶明显更难：累计门槛 >= 13000（旧值为 4500）
ck88('10阶累计门槛>=13000', tier_prof_need(10) >= 13000, (string) tier_prof_need(10));
ck88('10阶至少是旧值4500的3倍', tier_prof_need(10) >= 4500 * 3, (string) tier_prof_need(10));
// 最后一冲（9->10）跨度远大于旧值（旧 3500->4500，仅1000）
ck88('9->10阶跨度>=5000', tier_prof_need(10) - tier_prof_need(9) >= 5000, (string) (tier_prof_need(10) - tier_prof_need(9)));

// 3) 升阶逻辑按新门槛生效：9阶、熟练=10阶门槛-2，点一次仍在9阶；再点一次到10阶
function mk_sk88(int $prof, int $tier): array {
    $uid = (int) user_create('zz88' . mt_rand(100000, 999999), 'x', 'warrior', 'z1', true, 'tmp');
    db()->exec('DELETE FROM skills WHERE uid=' . $uid);
    db()->prepare('INSERT INTO skills (uid,skill,level,prof,tier,active) VALUES (?,?,?,?,?,?)')
        ->execute([$uid, 'w1', 1, $prof, $tier, 1]);
    return [$uid];
}
$need10 = tier_prof_need(10);
[$uidA] = mk_sk88($need10 - 2, 9);
add_skill_prof($uidA, 'w1');
$rowA = db()->query("SELECT prof,tier FROM skills WHERE uid=$uidA AND skill='w1'")->fetch();
ck88('未达门槛仍为9阶', (int) $rowA['tier'] === 9, 'tier=' . $rowA['tier'] . ' prof=' . $rowA['prof']);
add_skill_prof($uidA, 'w1');
$rowA2 = db()->query("SELECT prof,tier FROM skills WHERE uid=$uidA AND skill='w1'")->fetch();
ck88('达到新门槛后升到10阶', (int) $rowA2['tier'] === 10, 'tier=' . $rowA2['tier'] . ' prof=' . $rowA2['prof']);
db()->exec('DELETE FROM skills WHERE uid=' . (int) $uidA);
db()->exec('DELETE FROM users WHERE id=' . (int) $uidA);

// 4) 旧门槛（4500）已不再能直接冲上10阶
[$uidB] = mk_sk88(4500, 9);
add_skill_prof($uidB, 'w1');
$rowB = db()->query("SELECT tier FROM skills WHERE uid=$uidB AND skill='w1'")->fetch();
ck88('熟练4500不足以升10阶', (int) $rowB['tier'] === 9, 'tier=' . $rowB['tier']);
db()->exec('DELETE FROM skills WHERE uid=' . (int) $uidB);
db()->exec('DELETE FROM users WHERE id=' . (int) $uidB);

echo "\nOK=$ok FAIL=$bad\n";
