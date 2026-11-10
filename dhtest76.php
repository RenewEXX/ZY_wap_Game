<?php
define('DH_TEST_MODE', true);
require 'includes/bootstrap.php';
$ok=0;$bad=0;
function ck($l,$c,$d=''){global $ok,$bad; if($c)$ok++; else{$bad++; echo "FAIL $l $d\n";}}
$uid=(int)user_create('rebirth'.mt_rand(100000,999999),'x','warrior','z1',true,'tmp');
$u=user_by_id($uid);
// level 999
$u['lv']=999; $u['exp']=12345; $u['hp']=1000;$u['maxhp']=1000;$u['mp']=100;$u['maxmp']=100;
$u['atk']=100;$u['def']=50;$u['str']=10;$u['agi']=5;$u['vit']=5;$u['int']=5;
user_save($u);
// world boss killed
drg_kv_set('wb_done_doyle', 1);
// soul equip + soul_lv 10
db()->prepare('INSERT INTO equips (uid, slot, name, quality, affixes, pos, req_lv, expires_at) VALUES (?, "soul", ?, 4, "[]", "", 1, 0)')->execute([$uid, '测试神魂']);
$soulEid = (int)db()->lastInsertId();
mat_set($uid, 'soul_lv_' . $soulEid, 10);
// jade + soul + core mats (first rebirth: 2 jade, 3 soul, 1 core)
add_mat($uid, 'jade_fire', 2);
add_mat($uid, 'soul_fire', 3);
add_mat($uid, 'dragon_core', 1);
// rs_can_rebirth should be true
ck('转生条件满足', rs_can_rebirth($uid), rs_require_text($uid));
$beforeJade = (int)(mats_of($uid)['jade_fire'] ?? 0);
$beforeSoul = (int)(mats_of($uid)['soul_fire'] ?? 0);
$beforeCore = (int)(mats_of($uid)['dragon_core'] ?? 0);
$beforeRs = rs_count($uid);
$beforePoint = (int)(mats_of($uid)['rs_point'] ?? 0);
// perform
$out = rs_perform($uid);
ck('转生执行成功', str_contains($out, '转生'), $out);
$u2 = user_by_id($uid);
ck('转生次数+1', rs_count($uid) === $beforeRs + 1, 'rs=' . rs_count($uid));
ck('等级重置为1', (int)$u2['lv'] === 1, 'lv=' . (int)$u2['lv']);
ck('经验清零', (int)$u2['exp'] === 0);
ck('源罪点数+1', (int)(mats_of($uid)['rs_point'] ?? 0) === $beforePoint + 1);
ck('龙玉扣除2', (int)(mats_of($uid)['jade_fire'] ?? 0) === $beforeJade - 2, 'have=' . (int)(mats_of($uid)['jade_fire'] ?? 0));
ck('龙魂扣除3', (int)(mats_of($uid)['soul_fire'] ?? 0) === $beforeSoul - 3, 'have=' . (int)(mats_of($uid)['soul_fire'] ?? 0));
ck('龙王精华扣除1', (int)(mats_of($uid)['dragon_core'] ?? 0) === $beforeCore - 1, 'have=' . (int)(mats_of($uid)['dragon_core'] ?? 0));
ck('属性重置atk', (int)$u2['atk'] === (int)(jobs()['warrior']['atk'] ?? 0), 'atk=' . (int)$u2['atk']);
ck('转生加成倍率 1.1', abs(rs_stat_mult($uid) - 1.1) < 0.001);
// 第二转：再满足条件
$u2['lv']=999; user_save($u2);
add_mat($uid, 'jade_fire', 4);
add_mat($uid, 'soul_fire', 6);
add_mat($uid, 'dragon_core', 2);
ck('第二转条件满足', rs_can_rebirth($uid), rs_require_text($uid));
$out2 = rs_perform($uid);
ck('第二转成功', str_contains($out2, '2转'), $out2);
ck('转生次数=2', rs_count($uid) === 2);
ck('源罪点数=2', (int)(mats_of($uid)['rs_point'] ?? 0) === 2);
// 第三转
$u3 = user_by_id($uid); $u3['lv']=999; user_save($u3);
add_mat($uid, 'jade_fire', 6);
add_mat($uid, 'soul_fire', 9);
add_mat($uid, 'dragon_core', 3);
ck('第三转条件满足', rs_can_rebirth($uid));
$out3 = rs_perform($uid);
ck('第三转成功', str_contains($out3, '3转'), $out3);
ck('转生次数=3 (max)', rs_count($uid) === 3);
ck('已达最高转生', !rs_can_rebirth($uid));
echo "OK=$ok FAIL=$bad\n";
// cleanup
db()->prepare('DELETE FROM users WHERE id=?')->execute([$uid]);
db()->prepare('DELETE FROM equips WHERE uid=?')->execute([$uid]);
db()->prepare('DELETE FROM mats WHERE uid=?')->execute([$uid]);
db()->prepare("DELETE FROM sys_kv WHERE k='wb_done_doyle'")->execute();
