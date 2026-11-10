<?php
define('DH_TEST_MODE', true);
require 'includes/bootstrap.php';
// 回归：转生材料与龙族产出对齐——龙魂(小怪)+龙王精华(区域首领)+龙玉(斩龙) 缺一不可，
// 且龙王精华此前是死材料，现已纳入转生消耗。
$ok = 0; $bad = 0;
function ck92($l, $c, $d = '') { global $ok, $bad; if ($c) { $ok++; } else { $bad++; echo "FAIL $l $d\n"; } }

function mk92(): int {
    $uid = (int) user_create('zz92' . mt_rand(100000, 999999), 'x', 'warrior', 'z1', true, 'tmp');
    $u = user_by_id($uid); $u['lv'] = 999; $u['hp'] = 1000; $u['maxhp'] = 1000; $u['mp'] = 100; $u['maxmp'] = 100;
    user_save($u);
    drg_kv_set('wb_done_doyle', 1);
    db()->prepare('INSERT INTO equips (uid, slot, name, quality, affixes, pos, req_lv, expires_at) VALUES (?, "soul", ?, 4, "[]", "", 1, 0)')->execute([$uid, '测试神魂']);
    $eid = (int) db()->lastInsertId();
    mat_set($uid, 'soul_lv_' . $eid, 10);
    return $uid;
}

$uid = mk92();
// 只有龙玉+龙魂，没有龙王精华 → 不能转生、且提示缺精华
add_mat($uid, 'jade_fire', 2);
add_mat($uid, 'soul_fire', 3);
ck92('缺龙王精华不能转生', !rs_can_rebirth($uid), rs_require_text($uid));
ck92('提示需龙王精华', str_contains(rs_require_text($uid), '龙王精华'), rs_require_text($uid));

// 补齐龙王精华 → 可以转生，且执行后精华被扣
add_mat($uid, 'dragon_core', 1);
ck92('补齐精华后可转生', rs_can_rebirth($uid), rs_require_text($uid));
$out = rs_perform($uid);
ck92('转生成功', str_contains($out, '转生'), $out);
ck92('精华已消耗', (int) (mats_of($uid)['dragon_core'] ?? 0) === 0, 'core=' . (int) (mats_of($uid)['dragon_core'] ?? 0));

// 三档成本：龙玉2/4/6、龙魂3/6/9、精华1/2/3
ck92('成本 1转 2/3/1', [rs_jade_cost(1), rs_soul_cost(1), rs_core_cost(1)] === [2, 3, 1]);
ck92('成本 2转 4/6/2', [rs_jade_cost(2), rs_soul_cost(2), rs_core_cost(2)] === [4, 6, 2]);
ck92('成本 3转 6/9/3', [rs_jade_cost(3), rs_soul_cost(3), rs_core_cost(3)] === [6, 9, 3]);

db()->prepare('DELETE FROM users WHERE id=?')->execute([$uid]);
db()->prepare('DELETE FROM equips WHERE uid=?')->execute([$uid]);
db()->prepare('DELETE FROM mats WHERE uid=?')->execute([$uid]);
db()->prepare("DELETE FROM sys_kv WHERE k='wb_done_doyle'")->execute();

echo "OK=$ok FAIL=$bad\n";
