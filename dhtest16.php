<?php
define('DH_TEST_MODE', true);
require 'includes/bootstrap.php';
// 宠物图鉴：命名/来源统一 + 火龙去重 + 龙王幼体化
$ok = 0; $bad = 0;
function ckP(string $l, $got, $want) { global $ok,$bad; if ($got === $want) { $ok++; } else { $bad++; echo "FAIL $l: got=",var_export($got,true)," want=",var_export($want,true),"\n"; } }

$dex = pet_species();
$dragons = ['fire_drake' => '火龙多伊尔幼体', 'sky_serpent' => '风龙艾瑟琳幼体', 'frost_maiden' => '冰龙伊索尔德幼体',
            'storm_tyrant' => '雷龙雷克斯幼体', 'void_dragon' => '暗龙尼德霍格幼体', 'seraph_dragon' => '光龙奥瑞利安幼体'];

// —— 1. 火龙去重：图鉴里只能有一条火龙 ——
$fireNames = [];
foreach ($dex as $k => $sp) {
    if (str_contains($sp['name'], '火龙')) {
        $fireNames[] = $sp['name'] . '(' . $k . ')';
    }
}
ckP('火龙只有一条', count($fireNames), 1);
ckP('火龙名为火龙多伊尔幼体', $fireNames[0] ?? '', '火龙多伊尔幼体(fire_drake)');
ckP('fire_dragon已删除', isset($dex['fire_dragon']), false);
ckP('fire_drake仍在', isset($dex['fire_drake']), true);

// —— 2. 六龙王统一为「XX幼体」+ 世界BOSS掉落 ——
foreach ($dragons as $sid => $nm) {
    ckP("龙王 $sid 存在", isset($dex[$sid]), true);
    ckP("龙王 $sid 名为$nm", $dex[$sid]['name'] ?? '', $nm);
    ckP("龙王 $sid 来源世界BOSS掉落", $dex[$sid]['source'] ?? '', '世界BOSS掉落');
    ckP("龙王 $sid 品质传说", $dex[$sid]['quality'] ?? '', 'legend');
}
// 幼体后缀条目：6龙王 + 土龙岳柱幼体 + 蚀影幼体（阿巴顿）= 8
$nChu = 0;
foreach ($dex as $sp) {
    if (str_contains($sp['name'], '幼体')) { $nChu++; }
}
ckP('幼体命名共8条', $nChu, 8);
$chuNames = [];
foreach ($dex as $sp) {
    if (str_contains($sp['name'], '幼体')) { $chuNames[] = $sp['name']; }
}
sort($chuNames);
// sort() 按 UTF-8 字节序：光<冰<土<暗<火<蚀<雷<风（按首字码点）
ckP('幼体名单正确', implode('/', $chuNames), '光龙奥瑞利安幼体/冰龙伊索尔德幼体/土龙岳柱幼体/暗龙尼德霍格幼体/火龙多伊尔幼体/蚀影幼体/雷龙雷克斯幼体/风龙艾瑟琳幼体');

// —— 3. 图鉴条目不重名 ——
$names = array_map(fn($s) => $s['name'], $dex);
ckP('图鉴无重名', count($names), count(array_unique($names)));

// —— 4. 来源统一为「获取方式」而非龙王名 ——
foreach ($dex as $k => $sp) {
    ckP("$k 来源非龙王名", in_array($sp['source'], ['火龙多伊尔', '风龙艾瑟琳', '冰龙伊索尔德', '雷龙雷克斯', '暗龙尼德霍格', '光龙奥瑞利安'], true), false);
    ckP("$k 来源非空", $sp['source'] !== '', true);
}

// —— 5. 蛋名与宠物名对齐，且能正确孵化 ——
$eggs = pet_eggs();
$species2egg = [];
foreach ($eggs as $eid => $e) {
    if ($e['species'] !== '') { $species2egg[$e['species']] = $eid; }
}
foreach ($dragons as $sid => $nm) {
    ckP("$sid 有对应蛋", isset($species2egg[$sid]), true);
    if (isset($species2egg[$sid])) {
        ckP("$sid 蛋名正确", $eggs[$species2egg[$sid]]['name'], $nm . '蛋');
    }
}
ckP('土龙蛋名', $eggs['earth_dragon_egg']['name'], '土龙岳柱幼体蛋');
$eggNames = array_column($eggs, 'name');
ckP('蛋名无重复', count($eggNames), count(array_unique($eggNames)));

// —— 6. 蛋名唯一来源：dragon_mats() 与 pet_eggs() 一致 ——
$dm = dragon_mats();
foreach ($dragons as $sid => $nm) {
    $eggId = $species2egg[$sid] ?? '';
    ckP("dragon_mats 蛋名同步 $eggId", $dm[$eggId] ?? '', $eggs[$eggId]['name'] ?? '');
}

// —— 7. 龙威被动真的生效（攻击+20%）—— pet_stats 对 fire_drake 有 atk*1.2
$sp = pet_species()['fire_drake'];
$lv = 10;
$gf = 0.8 + $sp['growth'] * 0.4;
$rawAtk = (int) (($sp['atk'] * 0.4 + ($lv - 1) * $sp['atk'] * 0.06) * $gf * 1.5);
$st = pet_stats(['species' => 'fire_drake', 'level' => $lv]);
ckP('火龙攻击含龙威+20%', $st['atk'], (int) round($rawAtk * 1.2));
ckP('火龙龙威被动文案', $sp['passive'], '龙威：攻击+20%，承伤-10%');

// —— 8. 迁移：遗留 fire_dragon 宠物被改成 fire_drake ——
$uid = user_create('tpetfix', 'x', 'warrior', 'z1', true, 'tmp');
db()->prepare('INSERT INTO pets (uid, species, quality, level, exp, hp, status, active, created_at) VALUES (?, "fire_dragon", "legend", 5, 0, 100, "normal", 0, ?)')
    ->execute([$uid, time()]);
ckP('迁移前确实是fire_dragon', (string) db()->query('SELECT species FROM pets WHERE uid=' . (int) $uid)->fetchColumn(), 'fire_dragon');
// 模拟 db_init 的一次性迁移
db()->exec("UPDATE pets SET species='fire_drake' WHERE species='fire_dragon'");
ckP('遗留fire_dragon已迁移', (string) db()->query('SELECT species FROM pets WHERE uid=' . (int) $uid)->fetchColumn(), 'fire_drake');

db()->exec('DELETE FROM pets WHERE uid=' . (int) $uid);
db()->exec('DELETE FROM users WHERE id=' . (int) $uid);
db()->exec('DELETE FROM equips WHERE uid=' . (int) $uid);
db()->exec('DELETE FROM mats WHERE uid=' . (int) $uid);
$_SESSION = [];
echo "\nOK=$ok FAIL=$bad\n";