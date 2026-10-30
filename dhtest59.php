<?php
define('DH_TEST_MODE', true);
require 'includes/bootstrap.php';
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
// 组队系统完善：大区域经验加成 / 组队开本全队门票 / 离线退队解散 / 附近队伍同图
$ok = 0; $bad = 0;
function ck59($l, $c, $d = '') { global $ok, $bad; if ($c) { $ok++; } else { $bad++; echo "FAIL $l $d\n"; } }
function mk59(string $loc = 'field'): int {
    $uid = (int) user_create('p59' . mt_rand(100000, 999999), 'x', 'warrior', 'z1', true, 'tmp');
    $u = user_by_id($uid); $u['loc'] = $loc; $u['last_seen'] = time(); user_save($u);
    return $uid;
}
function part59(int $leader, array $members): int {
    db()->prepare('INSERT INTO parties (leader_uid, zone, created_at) VALUES (?,?,?)')->execute([$leader, 'z1', time()]);
    $pid = (int) db()->lastInsertId();
    db()->prepare('INSERT INTO party_members (uid,pid,joined_at) VALUES (?,?,?)')->execute([$leader, $pid, time()]);
    $t = time();
    foreach ($members as $m) { $t++; db()->prepare('INSERT INTO party_members (uid,pid,joined_at) VALUES (?,?,?)')->execute([$m, $pid, $t]); }
    return $pid;
}
$cleanup = [];
function regU(int $uid) { global $cleanup; $cleanup[] = $uid; }

// ---- 1) 大区域判定 ----
ck59('field/well 同区域', loc_big_region('field') === loc_big_region('well'), loc_big_region('field') . ' vs ' . loc_big_region('well'));
ck59('field 与 town_sq 不同区域', loc_big_region('field') !== loc_big_region('town_sq'));
$dsw0 = dsw_maps()[0]; $dsw1 = dsw_maps()[1] ?? $dsw0;
ck59('同副本不同图同区域', loc_big_region($dsw0) === loc_big_region($dsw1));
ck59('不同副本不同区域', loc_big_region($dsw0) !== loc_big_region(pz_maps()[0]));

// ---- 2) 队伍经验加成：同大区域生效 ----
$a = mk59('field'); regU($a);
$b = mk59('well'); regU($b);
$pa = part59($a, [$b]);
ck59('同大区域(不同图)经验1.1', abs(party_bonus_mult($a) - 1.1) < 1e-9, (string) party_bonus_mult($a));
$ub = user_by_id($b); $ub['loc'] = 'town_sq'; user_save($ub);
ck59('跨大区域经验1.0', abs(party_bonus_mult($a) - 1.0) < 1e-9, (string) party_bonus_mult($a));
$ub = user_by_id($b); $ub['loc'] = 'well'; user_save($ub);

// ---- 3) 组队开本：全队门票校验 / 各扣1张 / 整队传送 ----
add_mat($a, 'pz_ticket', 1); add_mat($b, 'pz_ticket', 1);
$ret = party_open_dungeon($a, 'pz_ticket', '傀儡回廊入场券', function (int $mu) {
    mat_set($mu, 'pz_stage', 1);
    $v = user_by_id($mu); $v['loc'] = 'pz_gate'; user_save($v);
});
ck59('全队有票 => ok', $ret['ok'] === true, json_encode($ret));
ck59('A 门票已扣', (int) (mats_of($a)['pz_ticket'] ?? 0) === 0);
ck59('B 门票已扣', (int) (mats_of($b)['pz_ticket'] ?? 0) === 0);
ck59('A 已传送', user_by_id($a)['loc'] === 'pz_gate');
ck59('B 已传送', user_by_id($b)['loc'] === 'pz_gate');

// 缺票阻止
$ua = user_by_id($a); $ua['loc'] = 'field'; user_save($ua);
$ubb = user_by_id($b); $ubb['loc'] = 'well'; user_save($ubb);
add_mat($a, 'pz_ticket', 1); // 只有 A 有
$ret2 = party_open_dungeon($a, 'pz_ticket', '傀儡回廊入场券', function (int $mu) {
    $v = user_by_id($mu); $v['loc'] = 'pz_gate'; user_save($v);
});
ck59('缺票 => 不 ok', $ret2['ok'] === false, json_encode($ret2));
ck59('缺票提示含B名', str_contains($ret2['msg'], (string) user_by_id($b)['username']), $ret2['msg']);
ck59('缺票时A票未扣', (int) (mats_of($a)['pz_ticket'] ?? 0) === 1);
ck59('缺票时无人传送', user_by_id($a)['loc'] !== 'pz_gate' && user_by_id($b)['loc'] !== 'pz_gate');

// ---- 4) 离线：队员退队 / 队长解散 ----
party_on_offline($b);
ck59('队员离线已退队', my_party($b) === null);
ck59('队长仍在队伍', my_party($a) !== null);
party_on_offline($a);
ck59('队长离线队伍解散', my_party($a) === null && my_party($b) === null);

// ---- 5) 离线扫描(last_seen) ----
$l = mk59('field'); regU($l);
$m = mk59('field'); regU($m);
part59($l, [$m]);
$um = user_by_id($m); $um['last_seen'] = time() - 999; user_save($um);
$ul = user_by_id($l); $ul['last_seen'] = time(); user_save($ul);
party_sweep_offline(120);
ck59('扫描：离线队员退队', my_party($m) === null);
ck59('扫描：在线队长留存', my_party($l) !== null);
$ul = user_by_id($l); $ul['last_seen'] = time() - 999; user_save($ul);
party_sweep_offline(120);
ck59('扫描：离线队长解散', my_party($l) === null);

// ---- 6) 附近队伍只列本图 ----
$p1 = mk59('field'); regU($p1);
$p2 = mk59('field'); regU($p2);
$q1 = mk59('town_sq'); regU($q1);
$part1 = part59($p1, [$p2]);
$part2 = part59($q1, []);
$st = db()->prepare('SELECT p.id FROM parties p JOIN users u ON u.id=p.leader_uid WHERE p.zone=? AND EXISTS (SELECT 1 FROM party_members m2 JOIN users u2 ON u2.id=m2.uid WHERE m2.pid=p.id AND u2.loc=?)');
$st->execute(['z1', 'field']);
$ids = array_map('intval', $st->fetchAll(PDO::FETCH_COLUMN));
ck59('附近队伍含field队伍', in_array($part1, $ids, true), json_encode($ids));
ck59('附近队伍不含town_sq队伍', !in_array($part2, $ids, true), json_encode($ids));

// 清理
foreach (array_unique($cleanup) as $uid) {
    db()->prepare('DELETE FROM party_members WHERE uid=?')->execute([$uid]);
    db()->prepare('DELETE FROM parties WHERE leader_uid=?')->execute([$uid]);
    db()->prepare('DELETE FROM users WHERE id=?')->execute([$uid]);
    db()->prepare('DELETE FROM equips WHERE uid=?')->execute([$uid]);
    db()->prepare('DELETE FROM mats WHERE uid=?')->execute([$uid]);
}
echo "OK=$ok FAIL=$bad\n";
