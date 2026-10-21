<?php
define('DH_TEST_MODE', true);
require 'includes/bootstrap.php';
// 装备完整性守卫：非法部位不入库 + 历史垃圾行被迁移清掉 + 掉落永不产生畸形装
$ok = 0; $bad = 0;
function ckE(string $l, bool $cond, string $detail = '') { global $ok,$bad; if ($cond) { $ok++; } else { $bad++; echo "FAIL $l $detail\n"; } }

$uid = user_create('dhte' . mt_rand(1000, 9999), 'x', 'warrior', 'z1', true, 'tmp');

// 1. make_equip 拒绝非法部位
$before = (int) db()->query('SELECT COUNT(*) FROM equips WHERE uid=' . (int)$uid)->fetchColumn();
$r1 = make_equip((int)$uid, 'NULL', '生锈战斧', 0, 1);
ckE('非法slot(NULL)返回空', $r1 === '', var_export($r1, true));
$r2 = make_equip((int)$uid, 'equip', '生锈战斧', 0, 1);
ckE('非法slot(equip)返回空', $r2 === '', var_export($r2, true));
$r3 = make_equip((int)$uid, '', '生锈战斧', 0, 1);
ckE('空slot返回空', $r3 === '', var_export($r3, true));
$r4 = make_equip((int)$uid, "'weapon'", '生锈战斧', 0, 1);
ckE('带引号slot返回空', $r4 === '', var_export($r4, true));
$after = (int) db()->query('SELECT COUNT(*) FROM equips WHERE uid=' . (int)$uid)->fetchColumn();
ckE('非法调用没写入任何行', $after === $before, "before=$before after=$after");

// 2. 合法部位正常入库
$r5 = make_equip((int)$uid, 'weapon', '生锈战斧', 0, 1);
ckE('合法weapon入库', $r5 !== '', var_export($r5, true));
$row = db()->query('SELECT * FROM equips WHERE uid=' . (int)$uid . " AND slot='weapon' ORDER BY id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
ckE('入库装备部位合法', isset(equip_slots()[$row['slot'] ?? '']), var_export($row['slot'] ?? null, true));
ckE('入库装备名字正常', strpos((string)$row['name'], "'") !== 0, var_export($row['name'], true));
ckE('入库装备词缀合法', is_array(json_decode((string)$row['affixes'], true)), (string)$row['affixes']);

// 3. 全库无非法部位装备（迁移已清）
$valid = array_keys(equip_slots());
$ph = implode(',', array_fill(0, count($valid), '?'));
$st = db()->prepare("SELECT COUNT(*) FROM equips WHERE slot IS NULL OR slot NOT IN ($ph)");
$st->execute($valid);
ckE('全库无非法部位残留', (int) $st->fetchColumn() === 0);

// 4. 每件现有装备渲染都有部位
$noPart = 0;
foreach (db()->query('SELECT slot FROM equips')->fetchAll(PDO::FETCH_COLUMN) as $s) {
    if ((equip_slots()[$s] ?? '') === '') { $noPart++; }
}
ckE('所有装备部位非空', $noPart === 0);

// 5. 迁移标记已置位（幂等，不会重复删）
ckE('迁移标记已置位', (int) db()->query("SELECT num FROM sys_kv WHERE k='mig_equip_junk'")->fetchColumn() === 1);

// 清理测试号
db()->exec('DELETE FROM pets WHERE uid=' . (int)$uid);
db()->exec('DELETE FROM users WHERE id=' . (int)$uid);
db()->exec('DELETE FROM equips WHERE uid=' . (int)$uid);
db()->exec('DELETE FROM mats WHERE uid=' . (int)$uid);
db()->exec('DELETE FROM skills WHERE uid=' . (int)$uid);

echo "\nOK=$ok FAIL=$bad\n";
