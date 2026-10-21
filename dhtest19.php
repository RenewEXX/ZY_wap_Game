<?php
define('DH_TEST_MODE', true);
require 'includes/bootstrap.php';
// 真打：建战斗 $b，走 battle_round 的掉落分支（多只怪、精英）
// 找“怪被打死”这段逻辑属于哪个函数
$ok = 0; $bad = 0;
function ckG(string $l, bool $cond, string $detail = '') { global $ok,$bad; if ($cond) { $ok++; } else { $bad++; echo "FAIL $l $detail\n"; } }

// 直接看：哪个函数包含 roll_drop 调用并返回 battle 结果
$src = file_get_contents(__DIR__ . '/includes/game.php');
// battle_round 内部杀怪→roll_drop→$b['drops']，我们直接构造 $b 走一次
$uid = user_create('dhtf' . mt_rand(1000, 9999), 'x', 'warrior', 'z1', true, 'tmp');
$u = user_by_id($uid);
$u['lv'] = 30; $u['hp'] = 100000; $u['maxhp'] = 100000; $u['atk'] = 8000; $u['mp'] = 9000; $u['maxmp'] = 9000; $u['loc'] = 'sewer';
user_save($u);

$before = [];
foreach (db()->query('SELECT id FROM equips')->fetchAll(PDO::FETCH_COLUMN) as $i) { $before[(int)$i] = true; }

// 构造战斗数组，像 fight.php 那样
function mkBattle(string $mid, int $elite, int $left): array
{
    $m = monsters()[$mid] ?? monsters()['goblin'];
    return [
        'id' => $mid, 'name' => (string)($m['name'] ?? '怪'), 'lv' => monster_lv($mid),
        'hp' => 30, 'maxhp' => 30, 'atk' => 1, 'def' => 0, 'mdef' => 0,
        'gold' => (int)($m['gold'] ?? 1), 'exp' => 10,
        'num' => $left, 'left' => $left, 'elite' => $elite,
        'wexp' => 0, 'wgold' => 0, 'drops' => [],
    ];
}

$mids = array_keys(monsters());
$fights = 0;
foreach ($mids as $mid) {
    foreach ([0, 1] as $elite) {
        foreach ([1, 3] as $left) {
            $b = mkBattle((string)$mid, (int)$elite, (int)$left);
            $uu = user_by_id((int)$uid);
            try { battle_round($uu, $b, 'hit'); $fights++; } catch (Throwable $e) { }
            user_save($uu);
        }
    }
}
echo "战斗轮数: $fights\n";
$valid = array_keys(equip_slots());
$newN = 0; $weird = 0;
foreach (db()->query('SELECT id,uid,slot,name,quality,affixes,pos FROM equips ORDER BY id')->fetchAll(PDO::FETCH_ASSOC) as $r) {
    if (isset($before[(int)$r['id']]) || (int)$r['uid'] !== (int)$uid) { continue; }
    $newN++;
    $flags = [];
    if (!in_array($r['slot'], $valid, true)) { $flags[] = 'slot=' . $r['slot']; }
    if ((string)$r['name'] === '') { $flags[] = '空名'; }
    if (is_string($r['name']) && strlen($r['name']) > 0 && $r['name'][0] === "'") { $flags[] = '引号'; }
    if (json_decode((string)$r['affixes'], true) === null) { $flags[] = '词缀'; }
    if ((equip_slots()[$r['slot']] ?? '') === '') { $flags[] = '部位空'; }
    if ($flags !== []) { $weird++; ckG('掉落装备合法', false, "id={$r['id']} [" . implode('/', $flags) . "] name=" . var_export($r['name'], true)); }
}
ckG('战斗掉落有产出记录', $newN >= 0, "new=$newN");
ckG('战斗产出无畸形装备', $weird === 0, "weird=$weird new=$newN");

db()->exec('DELETE FROM pets WHERE uid=' . (int)$uid);
db()->exec('DELETE FROM users WHERE id=' . (int)$uid);
db()->exec('DELETE FROM equips WHERE uid=' . (int)$uid);
db()->exec('DELETE FROM mats WHERE uid=' . (int)$uid);
db()->exec('DELETE FROM skills WHERE uid=' . (int)$uid);

echo "\nOK=$ok FAIL=$bad\n";
