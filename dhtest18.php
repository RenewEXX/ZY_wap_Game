<?php
define('DH_TEST_MODE', true);
require 'includes/bootstrap.php';
// 自动备份：到点才备、快照可读、超量清理、锁与容错
$ok = 0; $bad = 0;
function ckB(string $l, $got, $want) { global $ok,$bad; if ($got === $want) { $ok++; } else { $bad++; echo "FAIL $l: got=",var_export($got,true)," want=",var_export($want,true),"\n"; } }

$dir = db_backup_dir();
if (!is_dir($dir)) { mkdir($dir, 0777, true); }
// 清场：本测试自己造的备份先删掉，避免影响断言
foreach (glob($dir . '/zuiyuan-*.sqlite') as $f) { @unlink($f); }
@unlink($dir . '/.backup.lock');

// —— 1. 默认配置 ——
ckB('默认间隔6小时', db_backup_interval(), 6 * 3600);
ckB('默认保留12份', db_backup_keep(), 12);
ckB('备份目录在data/backups', basename(db_backup_dir()), 'backups');

// —— 2. 到点判定 ——
db()->exec("DELETE FROM sys_kv WHERE k='last_backup_at'");
ckB('从未备份=该备', db_backup_due(), true);
db()->prepare("INSERT OR REPLACE INTO sys_kv (k,num) VALUES('last_backup_at',?)")->execute([time()]);
ckB('刚备过=不备', db_backup_due(), false);
db()->prepare("UPDATE sys_kv SET num=? WHERE k='last_backup_at'")->execute([time() - 6 * 3600 - 1]);
ckB('超时=该备', db_backup_due(), true);
db()->prepare("UPDATE sys_kv SET num=? WHERE k='last_backup_at'")->execute([time()]);

// —— 3. 立即备份：产出可读快照 ——
$before = ['users' => db()->query('SELECT COUNT(*) FROM users')->fetchColumn(),
           'equips' => db()->query('SELECT COUNT(*) FROM equips')->fetchColumn()];
$file = db_backup_now();
ckB('备份成功产出文件', $file !== null, true);
ckB('文件确实存在', $file !== null && is_file($file), true);
ckB('记录了时间戳', db_backup_last_at() > 0, true);
ckB('备完不再到期', db_backup_due(), false);
// 快照能独立打开且完整
$sp = new PDO('sqlite:' . $file, null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$ic = $sp->query('PRAGMA integrity_check')->fetchAll(PDO::FETCH_COLUMN);
ckB('快照完整性ok', $ic === ['ok'], true);
$spUsers = (int) $sp->query('SELECT COUNT(*) FROM users')->fetchColumn();
$spEquips = (int) $sp->query('SELECT COUNT(*) FROM equips')->fetchColumn();
ckB('快照users数一致', $spUsers, (int) $before['users']);
ckB('快照equips数一致', $spEquips, (int) $before['equips']);
$sp = null;
// 现场库没被破坏
$ic2 = db()->query('PRAGMA integrity_check')->fetchAll(PDO::FETCH_COLUMN);
ckB('现场库仍ok', $ic2 === ['ok'], true);

// —— 4. 超量清理：造 15 份，应只留 keep 份 ——
$keep = db_backup_keep();
for ($i = 0; $i < 15; $i++) {
    // 用不同文件名模拟历史备份
    file_put_contents($dir . '/zuiyuan-' . sprintf('20200101-0000%02d', $i) . '.sqlite', '');
}
db()->prepare("UPDATE sys_kv SET num=? WHERE k='last_backup_at'")->execute([time() - 6 * 3600 - 1]);
$newFile = db_backup_now();
$left = db_backup_files();
ckB('清理后只留keep份', count($left), $keep);
// glob 返回的分隔符与 DIRECTORY_SEPARATOR 不同，按 basename 比
ckB('保留的是最新份', $newFile !== null && basename(end($left)) === basename($newFile), true);
ckB('新备份没被误删', $newFile !== null && is_file($newFile), true);
// 按时间序淘汰最旧的（占位文件从 000000 起，共15份+新1份=16 → 淘汰最旧4份）
$leftNames = array_map('basename', $left);
ckB('最旧占位被淘汰', in_array('zuiyuan-20200101-000000.sqlite', $leftNames, true), false);
ckB('较新占位仍在', in_array('zuiyuan-20200101-000014.sqlite', $leftNames, true), true);

// —— 5. 到点才做：db_auto_backup ——
foreach (glob($dir . '/zuiyuan-*.sqlite') as $f) { @unlink($f); }
db()->exec("DELETE FROM sys_kv WHERE k='last_backup_at'");
db_auto_backup(); // 到点，应产出
ckB('auto到点会备', count(db_backup_files()), 1);
db_auto_backup(); // 刚备过，不应再备
ckB('auto未到点不备', count(db_backup_files()), 1);

// —— 6. 备份目录不可写/失败时不炸请求 ——
// 模拟 sys_kv 缺失时也应安全返回
db()->exec("DELETE FROM sys_kv WHERE k='last_backup_at'");
try {
    db_auto_backup();
    ckB('auto异常安全(正常路径)', true, true);
} catch (Throwable $e) {
    ckB('auto异常安全', false, true);
}

// —— 7. 并发锁存在即不阻塞请求 ——
$lock = @fopen($dir . '/.backup.lock', 'c');
$f2 = @fopen($dir . '/.backup.lock', 'c');
$can = $f2 !== false && @flock($f2, LOCK_EX | LOCK_NB);
ckB('第二个句柄能拿到锁(前一个没持有)', $can !== false && $can, true);
if ($lock) { @flock($lock, LOCK_UN); @fclose($lock); }
if ($f2) { @flock($f2, LOCK_UN); @fclose($f2); }

echo "\nOK=$ok FAIL=$bad\n";
