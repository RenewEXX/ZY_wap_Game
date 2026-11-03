<?php
// dhtest79：战斗日志里“吸血”等自身标签位置正确，且绝不与怪物动作粘成一句
define('DH_TEST_MODE', true);
require 'includes/bootstrap.php';
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
$ok = 0; $bad = 0;
function ck79(string $l, bool $c, string $d = ''): void { global $ok, $bad; if ($c) { $ok++; } else { $bad++; echo "FAIL $l $d\n"; } }

$uid = (int) user_create('bld' . mt_rand(100000, 999999), 'x', 'warrior', 'z1', true, 'tmp');
$u = user_by_id($uid);
$u['lv'] = 300; $u['maxhp'] = 5000000; $u['hp'] = 5000000; $u['atk'] = 20000; $u['def'] = 5000;
$u['str'] = 8000; $u['last_seen'] = time();
user_save($u);
_gear_uncache($uid);
// 穿一件带吸血6%的装备，保证(lifesteal>0)触发“(吸血+N)”
db()->prepare('INSERT INTO equips (uid, slot, name, quality, affixes, pos, req_lv) VALUES (?, ?, ?, 3, ?, "wear", 1)')
    ->execute([$uid, 'ring', '测试吸血指环', json_encode([['id' => 'lifesteal', 'v' => 6]], JSON_UNESCAPED_UNICODE)]);
_gear_uncache($uid);
$gs = gear_stats($uid);
ck79('测试角色确实有吸血属性', (float) ($gs['lifesteal'] ?? 0) > 0, (string) ($gs['lifesteal'] ?? 0));

$allLog = '';
for ($i = 0; $i < 300; $i++) {
    $uu = user_by_id($uid);
    $uu['hp'] = $uu['maxhp']; // 每轮满血，避免被打死提前结束
    $b = [
        'id' => 'rd_sunk_hierophant', 'name' => '咸海诵经者·溺声', 'lv' => 160,
        'hp' => 100000000, 'maxhp' => 100000000, 'atk' => 4000, 'def' => 0, 'mdef' => 0,
        'left' => 1, 'num' => 1, 'elite' => 0, 'gold' => 1, 'exp' => 1, 'drops' => [],
    ];
    $res = battle_round($uu, $b, 'hit');
    $allLog .= (string) ($res['log'] ?? '');
}
ck79('产生了吸血标签', substr_count($allLog, '(吸血+') > 0, 'count=' . substr_count($allLog, '(吸血+'));
// 关键1：把整段按句号/叹号切句，“(吸血+N)”必须和玩家自己的伤害出现在同一句里（不再独自漂到召唤/怪物那行）
$parts = preg_split('/(?<=[。！])/u', $allLog, -1, PREG_SPLIT_NO_EMPTY);
$drainOk = true; $drainBad = '';
foreach ($parts as $p) {
    if (str_contains($p, '(吸血+')) {
        if (!(str_contains($p, '你劈出') || str_contains($p, '你放出') || str_contains($p, '你打出') || str_contains($p, '你挥出') || str_contains($p, '造成'))) {
            $drainOk = false; $drainBad = $p; break;
        }
    }
}
ck79('吸血标签和自身伤害在同一句', $drainOk, $drainBad);
// 关键2：任何一句都不能同时含“怪物技能(使出【)”和“吸血/召唤/契约”等自身标签
$glued = [];
foreach ($parts as $p) {
    if (str_contains($p, '使出【') && (str_contains($p, '吸血') || str_contains($p, '鲜血契约') || str_contains($p, '召唤已满') || str_contains($p, '献祭'))) {
        $glued[] = $p;
    }
}
ck79('怪物使出技能不会和吸血/召唤标签粘在一句', $glued === [], implode(' || ', array_slice($glued, 0, 3)));
// 关键3：怪物动作前一定是句号/叹号边界（不会紧跟一个右括号标签）
$badBoundary = preg_match('/\)咸海诵经者·溺声/u', $allLog);
ck79('怪物动作前有标点边界', $badBoundary === 0, 'matched=' . (int) $badBoundary);

db()->prepare('DELETE FROM equips WHERE uid=?')->execute([$uid]);
db()->prepare('DELETE FROM users WHERE id=?')->execute([$uid]);
db()->prepare('DELETE FROM mats WHERE uid=?')->execute([$uid]);

// 追加：召唤师平A满员场景（之前“憎恶聚合体扑上去”那行前面会粘 (吸血+N)(召唤已满·普攻)）
$sid = (int) user_create('blz' . mt_rand(100000, 999999), 'x', 'summoner', 'z1', true, 'tmp');
$su = user_by_id($sid);
$su['lv'] = 300; $su['maxhp'] = 5000000; $su['hp'] = 5000000; $su['int'] = 20000; $su['grow_matk'] = 20000; $su['def'] = 5000; $su['last_seen'] = time();
user_save($su); _gear_uncache($sid);
db()->prepare('INSERT INTO equips (uid, slot, name, quality, affixes, pos, req_lv) VALUES (?, ?, ?, 3, ?, "wear", 1)')
    ->execute([$sid, 'ring', '测试吸血指环', json_encode([['id' => 'lifesteal', 'v' => 6]], JSON_UNESCAPED_UNICODE)]);
_gear_uncache($sid);
db()->prepare('INSERT OR REPLACE INTO skills (uid, skill, level, active, prof) VALUES (?, ?, 1, 1, 0)')->execute([$sid, 's1']);
$_SESSION['battle'] = ['summons' => []];
summon_add('abom', user_by_id($sid), 0);
summon_add('abom', user_by_id($sid), 0);
$sall = '';
for ($i = 0; $i < 120; $i++) {
    $uu = user_by_id($sid);
    $uu['hp'] = $uu['maxhp'];
    $b = ['id' => 'rd_sunk_hierophant', 'name' => '咸海诵经者·溺声', 'lv' => 160,
        'hp' => 100000000, 'maxhp' => 100000000, 'atk' => 4000, 'def' => 0, 'mdef' => 0,
        'left' => 1, 'num' => 1, 'elite' => 0, 'gold' => 1, 'exp' => 1, 'drops' => []];
    $res = battle_round($uu, $b, 'tick');
    $sall .= (string) ($res['log'] ?? '');
}
ck79('召唤师触发召唤已满·普攻', str_contains($sall, '(召唤已满·普攻)'), '');
$sparts = preg_split('/(?<=[。！])/u', $sall, -1, PREG_SPLIT_NO_EMPTY);
$sBad = [];
foreach ($sparts as $p) {
    // 召唤兽扑击那一句，前面不能粘 (吸血+N)/(召唤已满·普攻)/召唤ADD
    if (str_contains($p, '扑上去') && (str_contains($p, '(吸血+') || str_contains($p, '召唤已满·普攻'))) {
        $sBad[] = $p;
    }
    if (str_contains($p, '使出【') && (str_contains($p, '(吸血+') || str_contains($p, '召唤已满·普攻'))) {
        $sBad[] = $p;
    }
}
ck79('召唤兽攻击/怪物技能句不粘自身标签', $sBad === [], implode(' || ', array_slice($sBad, 0, 2)));
// 召唤已满标签必须和玩家自身伤害(放出/劈出/造成)在同一句
$sDrainOk = true; $sDrainBad = '';
foreach ($sparts as $p) {
    if (str_contains($p, '(召唤已满·普攻)') || str_contains($p, '(吸血+')) {
        if (!(str_contains($p, '你放出') || str_contains($p, '你劈出') || str_contains($p, '造成') || str_contains($p, '你挥出'))) {
            $sDrainOk = false; $sDrainBad = $p; break;
        }
    }
}
ck79('召唤已满/吸血标签贴在自身伤害句内', $sDrainOk, $sDrainBad);

db()->prepare('DELETE FROM skills WHERE uid=?')->execute([$sid]);
db()->prepare('DELETE FROM equips WHERE uid=?')->execute([$sid]);
db()->prepare('DELETE FROM users WHERE id=?')->execute([$sid]);
db()->prepare('DELETE FROM mats WHERE uid=?')->execute([$sid]);
echo "OK=$ok FAIL=$bad\n";
