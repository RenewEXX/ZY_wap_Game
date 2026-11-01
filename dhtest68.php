<?php
// dhtest68：个人仓库（存物/存钱 + 扩容）
define('DH_TEST_MODE', true);
require 'includes/bootstrap.php';
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
$ok = 0; $bad = 0;
function ck(string $l, bool $c, string $d = '') { global $ok, $bad; if ($c) { $ok++; } else { $bad++; echo "FAIL $l $d\n"; } }
function clean(int $uid): void {
    db()->prepare('DELETE FROM users WHERE id=?')->execute([$uid]);
    db()->prepare('DELETE FROM equips WHERE uid=?')->execute([$uid]);
    db()->prepare('DELETE FROM mats WHERE uid=?')->execute([$uid]);
    db()->prepare('DELETE FROM warehouse WHERE uid=?')->execute([$uid]);
    db()->prepare('DELETE FROM warehouse_gold WHERE uid=?')->execute([$uid]);
}

$uid = (int) user_create('wh' . mt_rand(100000, 999999), 'x', 'warrior', 'z1', true, 'tmp');
$u = user_by_id($uid); $u['gold'] = 1000000; user_save($u);

// 1) 默认容量 20
ck('默认容量=20', warehouse_capacity($uid) === 20, 'cap=' . warehouse_capacity($uid));

// 2) 存材料：占 1 格，背包清空，仓库有量
add_mat($uid, 'enhance_t1', 5);
warehouse_store_mat($uid, 'enhance_t1');
ck('存材料后背包清零', (int) (mats_of($uid)['enhance_t1'] ?? 0) === 0);
ck('存材料占1格', warehouse_slots_used($uid) === 1, 'used=' . warehouse_slots_used($uid));
$row = db()->prepare('SELECT qty FROM warehouse WHERE uid=? AND kind="mat" AND mat_id="enhance_t1"');
$row->execute([$uid]);
ck('仓库材料量=5', (int) ($row->fetchColumn() ?: 0) === 5);

// 3) 再存同种材料：合并，不额外占格
add_mat($uid, 'enhance_t1', 3);
warehouse_store_mat($uid, 'enhance_t1');
ck('合并后仍1格', warehouse_slots_used($uid) === 1);
$row->execute([$uid]);
ck('合并后仓库量=8', (int) ($row->fetchColumn() ?: 0) === 8);

// 4) 取出材料：回背包、清格
$wid = (int) db()->query('SELECT id FROM warehouse WHERE uid=' . $uid . ' AND mat_id="enhance_t1"')->fetchColumn();
warehouse_take_mat($uid, $wid);
ck('取出后背包8', (int) (mats_of($uid)['enhance_t1'] ?? 0) === 8);
ck('取出后0格', warehouse_slots_used($uid) === 0);

// 5) 金币存取
warehouse_store_gold($uid, 600000);
ck('存钱后仓库600000', warehouse_gold_of($uid) === 600000, 'wg=' . warehouse_gold_of($uid));
ck('存钱后身上40万', (int) user_by_id($uid)['gold'] === 400000, 'gold=' . (int) user_by_id($uid)['gold']);
warehouse_take_gold($uid, 250000);
ck('取钱后仓库35万', warehouse_gold_of($uid) === 350000, 'wg=' . warehouse_gold_of($uid));
ck('取钱后身上65万', (int) user_by_id($uid)['gold'] === 650000, 'gold=' . (int) user_by_id($uid)['gold']);
warehouse_take_gold($uid, 999999999); // 取超过库存 → 只取到剩的
ck('超额取钱=取空', warehouse_gold_of($uid) === 0);

// 6) 存/取装备
make_equip($uid, 'head', '铁盔', 1, 10);
$eid = (int) db()->query('SELECT id FROM equips WHERE uid=' . $uid . ' AND slot="head" ORDER BY id DESC LIMIT 1')->fetchColumn();
ck('造出一件头装', $eid > 0);
warehouse_store_equip($uid, $eid);
$e = db()->prepare('SELECT uid, pos FROM equips WHERE id=?'); $e->execute([$eid]); $er = $e->fetch();
ck('存装备→uid0 pos=warehouse', (int) $er['uid'] === 0 && $er['pos'] === 'warehouse', json_encode($er));
ck('存装备占1格', warehouse_slots_used($uid) === 1);
$wid = (int) db()->query('SELECT id FROM warehouse WHERE uid=' . $uid . ' AND kind="equip"')->fetchColumn();
warehouse_take_equip($uid, $wid);
$e->execute([$eid]); $er = $e->fetch();
ck('取装备→uid回来 pos空', (int) $er['uid'] === $uid && $er['pos'] === '', json_encode($er));
ck('取装备后0格', warehouse_slots_used($uid) === 0);
db()->prepare('DELETE FROM equips WHERE id=?')->execute([$eid]);

// 7) 容量上限：造 20 件存满，第 21 件被拒
for ($i = 0; $i < 21; $i++) {
    make_equip($uid, 'legs', '布裤', 0, 1);
}
$ids = [];
$q = db()->prepare('SELECT id FROM equips WHERE uid=? AND slot="legs" ORDER BY id');
$q->execute([$uid]);
foreach ($q->fetchAll() as $x) { $ids[] = (int) $x['id']; }
$stored = 0; $blocked = 0;
foreach ($ids as $id) {
    $r = warehouse_store_equip($uid, $id);
    if (str_contains($r, '满了')) { $blocked++; } else { $stored++; }
}
ck('存满20件', warehouse_slots_used($uid) === 20, 'used=' . warehouse_slots_used($uid));
ck('第21件被拒', $blocked >= 1, 'stored=' . $stored . ' blocked=' . $blocked);

// 8) 扩容书 +5
add_mat($uid, 'wh_ext5', 1);
warehouse_use_ext($uid);
ck('扩容后容量25', warehouse_capacity($uid) === 25, 'cap=' . warehouse_capacity($uid));
ck('扩容书消耗', (int) (mats_of($uid)['wh_ext5'] ?? 0) === 0);

// 9) 可入仓判定
ck('锻造材料可存', warehouse_mat_allowed('enhance_t1'));
ck('系统标记不可存', !warehouse_mat_allowed('wh_cap'));
ck('dummy标记不可存', !warehouse_mat_allowed('dummy_on'));

clean($uid);
echo "\nOK=$ok FAIL=$bad\n";
