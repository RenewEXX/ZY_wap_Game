<?php
define('DH_TEST_MODE', true);
require 'includes/bootstrap.php';
// 召唤师四项改造回归：副手介绍 / 召唤物血量显示 / 无回合限制 / 战报写清打谁
$ok = 0; $bad = 0;
function ckS(string $l, bool $cond, string $detail = '') { global $ok,$bad; if ($cond) { $ok++; } else { $bad++; echo "FAIL $l $detail\n"; } }

$uid = user_create('dhtsm' . mt_rand(1000, 9999), 'x', 'summoner', 'z1', true, 'tmp');
$u = user_by_id($uid);
$u['lv'] = 60; $u['hp'] = 6000; $u['maxhp'] = 6000; $u['mp'] = 4000; $u['maxmp'] = 4000; $u['atk'] = 60; $u['matk'] = 60;
user_save($u);
db()->prepare('INSERT OR REPLACE INTO skills (uid,skill,level,prof,tier,active) VALUES (?,?,?,?,?,?)')->execute([(int)$uid, 's1', 20, 0, 10, 1]);

// —— 1. 副手(契匣)装备详情页有召唤物介绍 ——
foreach (summoner_offhands() as $key => $cfg) {
    $html = summoner_offhand_intro_html($key);
    ckS("契匣[$key]有介绍", $html !== '', '空');
    $unitName = summon_units()[$cfg['summon']]['name'] ?? '';
    ckS("契匣[$key]介绍里有召唤物名", $unitName !== '' && str_contains($html, $unitName), $html);
    ckS("契匣[$key]介绍说明了不会自动消散", str_contains($html, '打死') || str_contains($html, '消散'), $html);
}
ckS('非契匣不显示召唤物介绍', summoner_offhand_intro_html('生锈战斧') === '');

// —— 2. 召唤物血量状态（像宠物那样） ——
$_SESSION['uid'] = (int) $uid;
$_SESSION['battle'] = ['id' => 'goblin', 'name' => '哥布林', 'hp' => 99999, 'maxhp' => 99999, 'atk' => 50, 'def' => 0, 'mdef' => 0, 'gold' => 1, 'exp' => 1, 'num' => 1, 'left' => 1, 'elite' => 0, 'wexp' => 0, 'wgold' => 0, 'drops' => [], 'summons' => []];
$uu = user_by_id((int) $uid);
$msg = summon_add('imp', $uu, 0);
ckS('召唤成功', summon_list() !== [], $msg);
ckS('召唤提示带生命值', str_contains($msg, '生命'), $msg);
$st = summon_status_list();
ckS('状态列表有1只', count($st) === 1, (string) count($st));
ckS('状态含名字', ($st[0]['name'] ?? '') !== '', json_encode($st));
ckS('状态含血量', ($st[0]['hp'] ?? 0) > 0 && ($st[0]['max'] ?? 0) > 0, json_encode($st));
ckS('hp不超过max', ($st[0]['hp'] ?? 0) <= ($st[0]['max'] ?? 0), json_encode($st));

// —— 3. 召唤物没有回合上限：打 20 轮还在 ——
$sp = summon_spawn('imp', 100, 1000, $u);
ckS('召唤物数据里没有turns字段', !array_key_exists('turns', $sp), json_encode(array_keys($sp)));
$bb = $_SESSION['battle'];
for ($i = 0; $i < 20; $i++) { summon_turn($bb); }
ckS('20轮后召唤物仍在', count(summon_list()) === 1, '剩' . count(summon_list()));
ckS('召唤物血量未被回合消耗', (int) (summon_list()[0]['hp'] ?? 0) === (int) (summon_list()[0]['maxhp'] ?? 0), json_encode(summon_list()));

// —— 4. 战报写清怪物打的是谁 ——
$logs = '';
for ($i = 0; $i < 4; $i++) {
    $uu = user_by_id((int) $uid);
    $bb = $_SESSION['battle'];
    $r = battle_round($uu, $bb, 'skill');
    $logs .= $r['log'];
    if ($r['status'] !== 'fight') { break; }
}
ckS('战报里出现"怪物扑向"（写明打的是召唤物）', str_contains($logs, '怪物扑向'), $logs);
ckS('战报里写明承伤对象和伤害', str_contains($logs, '它挨了') || str_contains($logs, '打在你身上'), $logs);
ckS('战报里给出召唤物剩余血量', str_contains($logs, '它挨了') && str_contains($logs, '/'), $logs);
ckS('全挡下时明确说明', str_contains($logs, '挡下了全部伤害') || str_contains($logs, '打在你身上'), $logs);

// 无召唤物时也要写明打在你身上
$_SESSION['battle']['summons'] = [];
$uu = user_by_id((int) $uid);
$bb = $_SESSION['battle'];
$r2 = battle_round($uu, $bb, 'hit');
ckS('没有召唤物时战报写明打在你身上', str_contains($r2['log'], '打在你身上'), $r2['log']);

// —— 5. 真实开局（battle 无 summons 键）跑一轮后，召唤物必须持久化到 session ——
// 否则「实时战斗画面」永远拿不到召唤物，看不到剩余血量（本轮修复的回归点）
$_SESSION['battle'] = ['id' => 'goblin', 'name' => '哥布林', 'hp' => 999999, 'maxhp' => 999999, 'atk' => 10, 'def' => 0, 'mdef' => 0, 'gold' => 1, 'exp' => 1, 'num' => 1, 'left' => 1, 'elite' => 0, 'wexp' => 0, 'wgold' => 0, 'drops' => []];
unset($_SESSION['battle']['summons']);
$uu = user_by_id((int) $uid);
$bb = $_SESSION['battle'];
$r5 = battle_round($uu, $bb, 'skill');
$live = summon_status_list();
ckS('开局无summons跑一轮后召唤物落库', $live !== [], json_encode($live));
ckS('落库召唤物带血量显示字段', isset($live[0]['hp'], $live[0]['max']) && $live[0]['hp'] > 0 && $live[0]['max'] > 0, json_encode($live));
ckS('session持久化数量一致', count($_SESSION['battle']['summons'] ?? []) === count($live), '');

// 清理
unset($_SESSION['battle'], $_SESSION['uid']);
db()->exec('DELETE FROM pets WHERE uid=' . (int) $uid);
db()->exec('DELETE FROM users WHERE id=' . (int) $uid);
db()->exec('DELETE FROM equips WHERE uid=' . (int) $uid);
db()->exec('DELETE FROM mats WHERE uid=' . (int) $uid);
db()->exec('DELETE FROM skills WHERE uid=' . (int) $uid);

echo "\nOK=$ok FAIL=$bad\n";
