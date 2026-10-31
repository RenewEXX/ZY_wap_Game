<?php
// dhtest62：自动售卖品质档位（低劣/普通/稀有/史诗及以下）+ 安全护栏
define('DH_TEST_MODE', true);
require 'includes/bootstrap.php';
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
$ok = 0; $bad = 0;
function ck62(string $l, bool $c, string $d = '') { global $ok, $bad; if ($c) { $ok++; } else { $bad++; echo "FAIL $l $d\n"; } }

$uid = (int) user_create('selltest' . mt_rand(1000, 9999), 'x', 'warrior', 'z1', true, 'tmp');
mat_set($uid, 'autosell_on', 1);
mat_set($uid, 'autosell_time', 100000);
mat_set($uid, 'autosell_q', 3);

function mk_eq(int $uid, int $q, string $pos = '', int $enh = 0): int {
    db()->prepare('INSERT INTO equips (uid, slot, name, quality, affixes, pos, item_level, enhance_level) VALUES (?, "weapon", ?, ?, "[]", ?, 80, ?)')
        ->execute([$uid, '测试剑', $q, $pos, $enh]);
    return (int) db()->lastInsertId();
}
function eq_exists(int $id): bool {
    $st = db()->prepare('SELECT 1 FROM equips WHERE id=?');
    $st->execute([$id]);
    return (bool) $st->fetchColumn();
}

// 史诗及以下 = 3：稀有(2)与史诗(3)都应卖，传说(4)不卖
$e2 = mk_eq($uid, 2);
$g0 = (int) user_by_id($uid)['gold'];
$r2 = auto_sell_check($uid, 2);
ck62('稀有装备会被自动卖', !eq_exists($e2), $r2);
ck62('卖稀有加钱', (int) user_by_id($uid)['gold'] > $g0, '');

$e3 = mk_eq($uid, 3);
$g1 = (int) user_by_id($uid)['gold'];
$r3 = auto_sell_check($uid, 3);
ck62('史诗装备会被自动卖(史诗及以下档)', !eq_exists($e3), $r3);
ck62('卖史诗加钱(+75)', (int) user_by_id($uid)['gold'] - $g1 === 75, (string) ((int) user_by_id($uid)['gold'] - $g1));

$e4 = mk_eq($uid, 4);
$r4 = auto_sell_check($uid, 4);
ck62('传说不会被自动卖(总封顶史诗)', eq_exists($e4), $r4);

$ew = mk_eq($uid, 3, 'wear');
$rw = auto_sell_check($uid, 3);
ck62('身上穿着的史诗不会被卖', eq_exists($ew), $rw);

$ee = mk_eq($uid, 3, '', 5);
$re = auto_sell_check($uid, 3);
ck62('已强化的史诗不会被卖', eq_exists($ee), $re);

// 低档位仍严格：q=3 时 q=4 不卖，且 autosell_q=2 时史诗不卖
mat_set($uid, 'autosell_q', 2);
$e3b = mk_eq($uid, 3);
$rb = auto_sell_check($uid, 3);
ck62('档位=稀有及以下时史诗不卖', eq_exists($e3b), $rb);

// 清理
foreach ([$e2, $e3, $e4, $ew, $ee, $e3b] as $eid) { db()->prepare('DELETE FROM equips WHERE id=?')->execute([$eid]); }
db()->prepare('DELETE FROM users WHERE id=?')->execute([$uid]);
db()->prepare('DELETE FROM mats WHERE uid=?')->execute([$uid]);
db()->prepare('DELETE FROM equips WHERE uid=?')->execute([$uid]);

echo "\nOK=$ok FAIL=$bad\n";
