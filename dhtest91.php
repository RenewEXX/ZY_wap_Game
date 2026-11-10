<?php
define('DH_TEST_MODE', true);
require 'includes/bootstrap.php';
// 回归：六龙王 + 土龙 宠物被动「风味重做」，机制互不重复。
//   火=点燃DoT / 风=遁走+风迹(见dhtest90) / 冰=减伤+哀恸 / 雷=麻痹+对被麻痹增伤 /
//   暗=吸血+斩杀残血 / 光=审判高血+回血 / 土=减伤+反震
$ok = 0; $bad = 0;
function ck91(string $l, bool $c, string $d = '') { global $ok, $bad; if ($c) { $ok++; } else { $bad++; echo "FAIL $l $d\n"; } }

function mkPet91(string $sp): int {
    $uid = (int) user_create('zz91' . mt_rand(100000, 999999), 'x', 'warrior', 'z1', true, 'tmp');
    db()->prepare('INSERT INTO pets (uid, species, quality, level, exp, hp, status, active, created_at) VALUES (?,?,?,?,0,?,"normal",1,?)')
        ->execute([$uid, $sp, 'legend', 8, 10000000, time()]);
    $pid = (int) db()->lastInsertId();
    mat_set($uid, 'pet_sat_' . $pid, time() + 999999); // 测试期间不增长疲劳
    $u = user_by_id($uid); $u['hp'] = (int) ((int) $u['maxhp'] / 2); user_save($u);
    return $uid;
}
function dummyB91(): array { return ['id' => 'dummy', 'name' => '木桩', 'hp' => 1000000000, 'maxhp' => 1000000000]; }
function oneAtk91(int $uid, array &$b): string { $u = user_by_id($uid); return pet_attack($u, $b); }

// 0) 七个被动文案互不相同
$sps = ['fire_drake', 'sky_serpent', 'frost_maiden', 'storm_tyrant', 'void_dragon', 'seraph_dragon', 'earth_dragon'];
$texts = [];
foreach ($sps as $s) { $texts[] = pet_species()[$s]['passive']; }
ck91('七个被动文案互不相同', count(array_unique($texts)) === count($texts), implode('|', $texts));

// 1) 火：命中点燃烬火（DoT）
$uid = mkPet91('fire_drake'); $fire = 0;
for ($i = 0; $i < 60; $i++) { $b = dummyB91(); if (str_contains(oneAtk91($uid, $b), '烬火')) { $fire++; } }
ck91('火·点燃烬火出现', $fire > 0, 'fire=' . $fire);
// 直接验证点燃后敌人带 poison（沿用同一个目标，积累状态）
$b = dummyB91();
for ($i = 0; $i < 200 && empty($b['poison']); $i++) { oneAtk91($uid, $b); }
ck91('火·点燃后敌人带灼烧状态', !empty($b['poison']), 'poison=' . ($b['poison'] ?? 0));

// 2) 冰：减伤(承伤-20%) 确定性 + 哀恸debuff
$uid = mkPet91('frost_maiden');
$pet = active_pet($uid); $st = pet_stats($pet);
$md = 4000; $take = max(0, max(1, (int) ($md / 2 * 0.8)) - (int) ($st['def'] / 2));
$before = (int) $pet['hp'];
pet_hurt($uid, $md);
$after = (int) db()->query("SELECT hp FROM pets WHERE id=" . (int) $pet['id'])->fetchColumn();
ck91('冰·承伤-20%精确生效', ($before - $after) === $take, ($before - $after) . ' vs ' . $take);
$fear = 0; for ($i = 0; $i < 60; $i++) { $b = dummyB91(); if (str_contains(oneAtk91($uid, $b), '永冻哀歌')) { $fear++; } }
ck91('冰·哀恸debuff出现', $fear > 0, 'fear=' . $fear);

// 3) 雷：对被麻痹目标增伤(确定性) + 麻痹命中
$uid = mkPet91('storm_tyrant');
$b = dummyB91(); $b['stun'] = 1;
$log = oneAtk91($uid, $b);
ck91('雷·对被麻痹目标增伤', str_contains($log, '天谴·雷罚'), $log);
$para = 0; for ($i = 0; $i < 60; $i++) { $b = dummyB91(); if (str_contains(oneAtk91($uid, $b), '天谴麻痹')) { $para++; } }
ck91('雷·麻痹命中出现', $para > 0, 'para=' . $para);

// 4) 暗：吸血(玩家回血) + 斩杀残血增伤
$uid = mkPet91('void_dragon');
$b = ['id' => 'dummy', 'name' => '木桩', 'hp' => 10, 'maxhp' => 100];
$u = user_by_id($uid); $hpBefore = (int) $u['hp'];
$log = pet_attack($u, $b);
ck91('暗·对残血增伤', str_contains($log, '噬界·吞噬残渣'), $log);
ck91('暗·吸血使玩家回血', (int) $u['hp'] > $hpBefore, $hpBefore . '->' . (int) $u['hp']);

// 5) 光：对高血增伤 + 命中回血
$uid = mkPet91('seraph_dragon');
$b = dummyB91();
$u = user_by_id($uid); $hpBefore = (int) $u['hp'];
$log = pet_attack($u, $b);
ck91('光·对高血目标审判增伤', str_contains($log, '圣裁·审判强者'), $log);
ck91('光·命中回复生命', (int) $u['hp'] > $hpBefore, $hpBefore . '->' . (int) $u['hp']);

// 6) 土：减伤 + 反震
$uid = mkPet91('earth_dragon');
$pet = active_pet($uid); $st = pet_stats($pet);
$md = 4000; $take = max(0, max(1, (int) ($md / 2 * 0.7)) - (int) ($st['def'] / 2));
$before = (int) $pet['hp'];
$b = ['id' => 'dummy', 'name' => '木桩', 'hp' => 1000000, 'maxhp' => 1000000]; $bhp = (int) $b['hp'];
$msg = pet_hurt($uid, $md, $b);
$after = (int) db()->query("SELECT hp FROM pets WHERE id=" . (int) $pet['id'])->fetchColumn();
ck91('土·承伤-30%精确生效', ($before - $after) === $take, ($before - $after) . ' vs ' . $take);
ck91('土·反震使敌人掉血', (int) $b['hp'] < $bhp, $bhp . '->' . (int) $b['hp']);
ck91('土·反震日志', str_contains($msg, '磐岩反震'), $msg);

// 清理
foreach ([$uid] as $x) { db()->exec('DELETE FROM pets WHERE uid=' . $x); db()->exec('DELETE FROM mats WHERE uid=' . $x); db()->exec('DELETE FROM users WHERE id=' . $x); }
// 其余测试用户也清掉（按前缀）
$rows = db()->query("SELECT id FROM users WHERE username LIKE 'zz91%'")->fetchAll();
foreach ($rows as $r) { db()->exec('DELETE FROM pets WHERE uid=' . (int) $r['id']); db()->exec('DELETE FROM mats WHERE uid=' . (int) $r['id']); db()->exec('DELETE FROM users WHERE id=' . (int) $r['id']); }

echo "\nOK=$ok FAIL=$bad\n";
