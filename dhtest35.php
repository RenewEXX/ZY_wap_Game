<?php
define('DH_TEST_MODE', true);
require 'includes/bootstrap.php';
// 回归：召唤师没蓝打「拳击」时，绝不能白嫖召唤（此前每轮兜底 else 白招一只小鬼）。
// 只有蓝够、真正放出召唤技能时才召唤；契匣开场只额外给 1 只。
$ok = 0; $bad = 0;
function ck35(string $l, bool $c, string $d = '') { global $ok,$bad; if ($c) { $ok++; } else { $bad++; echo "FAIL $l $d\n"; } }

function mk_summoner35(int $maxmp): array {
    $uid = (int) user_create('zz35' . mt_rand(10000, 99999), 'x', 'summoner', 'z1', true, 'tmp');
    make_equip($uid, 'offhand', '残魂灯匣', 0, 1);
    summoner_wear_box($uid);
    db()->prepare('INSERT OR REPLACE INTO skills (uid,skill,level,prof,tier,active) VALUES (?,?,?,?,?,?)')->execute([$uid, 's1', 1, 0, 1, 1]);
    $u = user_by_id($uid);
    $u['mp'] = $maxmp; $u['maxmp'] = $maxmp; $u['hp'] = $u['maxhp'];
    user_save($u);
    return [$uid, $u];
}
function big_battle35(): void {
    // 怪物攻击0、血量极大，避免把召唤兽打死、也拖很久
    $_SESSION['battle'] = [
        'id' => 'goblin', 'name' => '哥布林', 'hp' => 99999999, 'maxhp' => 99999999, 'atk' => 0,
        'exp' => 1, 'gold' => 1, 'log' => '开打', 'num' => 1, 'left' => 1, 'wexp' => 0, 'wgold' => 0,
        'drops' => [], 'elite' => 0, 'live' => true, 'last' => time(),
    ];
}

// 1) 没蓝（maxmp=0）：打 8 轮技能键，召唤数始终 <= 1（只允许契匣开场那 1 只）
[$uid, $u] = mk_summoner35(0);
big_battle35();
$maxN = 0; $openSeen = false;
for ($i = 0; $i < 8; $i++) {
    $u = user_by_id($uid);
    $b = $_SESSION['battle'];
    battle_round($u, $b, 'skill');
    user_save($u);
    $n = count($_SESSION['battle']['summons'] ?? []);
    if ($n >= 1) { $openSeen = true; }
    $maxN = max($maxN, $n);
}
ck35('没蓝时不白嫖召唤（最多只有开场1只）', $maxN <= 1, 'maxN=' . $maxN);
ck35('契匣开场确实招了1只', $openSeen);
foreach (['skills', 'equips', 'mats', 'pets'] as $t) { db()->exec("DELETE FROM {$t} WHERE uid=" . (int) $uid); }
db()->exec('DELETE FROM users WHERE id=' . (int) $uid);
unset($_SESSION['battle']);

// 2) 蓝充足：真正放技能时会召唤，能到 2 只（开场1 + 技能1）
[$uid2, $u2] = mk_summoner35(200);
big_battle35();
$maxN2 = 0; $castSeen = false;
for ($i = 0; $i < 4; $i++) {
    $u2 = user_by_id($uid2);
    $b = $_SESSION['battle'];
    $r = battle_round($u2, $b, 'skill');
    user_save($u2);
    if (strpos((string) $r['log'], '深渊契约') !== false || strpos((string) $r['log'], '应召而来') !== false) { $castSeen = true; }
    $maxN2 = max($maxN2, count($_SESSION['battle']['summons'] ?? []));
}
ck35('蓝够时技能能召唤（达到2只）', $maxN2 >= 2, 'maxN2=' . $maxN2);
foreach (['skills', 'equips', 'mats', 'pets'] as $t) { db()->exec("DELETE FROM {$t} WHERE uid=" . (int) $uid2); }
db()->exec('DELETE FROM users WHERE id=' . (int) $uid2);
unset($_SESSION['battle']);

// 3) 普攻键（hit）绝不召唤，只有开场那1只
[$uid3, $u3] = mk_summoner35(200);
big_battle35();
$maxN3 = 0;
for ($i = 0; $i < 6; $i++) {
    $u3 = user_by_id($uid3);
    $b = $_SESSION['battle'];
    battle_round($u3, $b, 'hit');
    user_save($u3);
    $maxN3 = max($maxN3, count($_SESSION['battle']['summons'] ?? []));
}
ck35('普攻不召唤（hit 模式最多开场1只）', $maxN3 <= 1, 'maxN3=' . $maxN3);
foreach (['skills', 'equips', 'mats', 'pets'] as $t) { db()->exec("DELETE FROM {$t} WHERE uid=" . (int) $uid3); }
db()->exec('DELETE FROM users WHERE id=' . (int) $uid3);

echo "\nOK=$ok FAIL=$bad\n";
