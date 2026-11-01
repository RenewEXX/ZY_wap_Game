<?php
// dhtest65：世界BOSS战模拟——必须打空才判死、死后才发奖、邮件正确、全服公告、防重复发奖
define('DH_TEST_MODE', true);
require 'includes/bootstrap.php';
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
$ok = 0; $bad = 0;
function ck65(string $l, bool $c, string $d = '') { global $ok, $bad; if ($c) { $ok++; } else { $bad++; echo "FAIL $l $d\n"; } }

drg_tick();
$mid = 'doyle';
$d = dragon($mid);
ck65('测试龙存在', $d !== [], $mid);

$uid1 = (int) user_create('wbtestA' . mt_rand(10000, 99999), 'x', 'warrior', 'z1', true, 'tmp');
$uid2 = (int) user_create('wbtestB' . mt_rand(10000, 99999), 'x', 'warrior', 'z1', true, 'tmp');

// 重置这条龙 + 清空本场排行/邮件
drg_kv_set('wb_hp_' . $mid, 1000);
drg_kv_set('wb_done_' . $mid, 0);
drg_kv_set('wb_next_' . $mid, 0);
drg_kv_set('wb_rewarded_' . $mid, 0);
db()->prepare('DELETE FROM wb_dmg WHERE mid=?')->execute([$mid]);
db()->prepare('DELETE FROM mails WHERE uid IN (?, ?)')->execute([$uid1, $uid2]);

$mailCnt = fn() => (int) db()->query('SELECT COUNT(*) FROM mails WHERE uid IN (' . $uid1 . ',' . $uid2 . ')')->fetchColumn();
$annCnt = fn() => (int) db()->query("SELECT COUNT(*) FROM chat_msgs WHERE username='六圣柱'")->fetchColumn();

$m0 = $mailCnt();
$a0 = $annCnt();

// 1) 非致命一击：400/1000 → 剩 600，不该死、不发奖、不公告
$k1 = drg_apply_damage($uid1, 'A', $mid, 400);
ck65('未打空不判死', $k1 === false, var_export($k1, true));
ck65('未死 done=0', drg_done($mid) === 0, (string) drg_done($mid));
ck65('未死 hp=600', drg_hp_now($mid) === 600, (string) drg_hp_now($mid));
ck65('未死 不发邮件', $mailCnt() === $m0, 'm=' . $mailCnt());
ck65('未死 不公告', $annCnt() === $a0, 'a=' . $annCnt());

// 2) 第二人 300 → 剩 300，仍不死
$k2 = drg_apply_damage($uid2, 'B', $mid, 300);
ck65('第二击仍不死', $k2 === false && drg_done($mid) === 0 && drg_hp_now($mid) === 300, drg_hp_now($mid) . '');

// 3) 第一人再打 300，打空 → 判死、发奖、全服公告
$k3 = drg_apply_damage($uid1, 'A', $mid, 300);
ck65('打空才判死', $k3 === true && drg_done($mid) === 1 && drg_hp_now($mid) === 0, 'done=' . drg_done($mid) . ' hp=' . drg_hp_now($mid));
ck65('死亡全服公告+1', $annCnt() === $a0 + 1, 'a=' . $annCnt());

// 排行：A 共 700、B 共 300
$rank = drg_rank($mid);
ck65('排行第一是A(700)', (int) ($rank[0]['uid'] ?? 0) === $uid1 && (int) ($rank[0]['dmg'] ?? 0) === 700, json_encode($rank));
ck65('排行第二是B(300)', (int) ($rank[1]['uid'] ?? 0) === $uid2 && (int) ($rank[1]['dmg'] ?? 0) === 300, json_encode($rank));

// 邮件：两人都应收到（第1名 A / 第2名 B），且各只一封
$mA = (int) db()->query('SELECT COUNT(*) FROM mails WHERE uid=' . $uid1)->fetchColumn();
$mB = (int) db()->query('SELECT COUNT(*) FROM mails WHERE uid=' . $uid2)->fetchColumn();
ck65('第一名收到邮件', $mA === 1, "mA=$mA");
ck65('第二名收到邮件', $mB === 1, "mB=$mB");
$titleA = (string) db()->query('SELECT title FROM mails WHERE uid=' . $uid1 . ' ORDER BY id DESC LIMIT 1')->fetchColumn();
$bodyA = (string) db()->query('SELECT body FROM mails WHERE uid=' . $uid1 . ' ORDER BY id DESC LIMIT 1')->fetchColumn();
ck65('第一名邮件标题=斩龙首功', str_contains($titleA, '斩龙首功'), $titleA);
ck65('第一名邮件含奖励(5金/100魔钻)', str_contains($bodyA, '5金') && str_contains($bodyA, '100魔钻'), $bodyA);
$titleB = (string) db()->query('SELECT title FROM mails WHERE uid=' . $uid2 . ' ORDER BY id DESC LIMIT 1')->fetchColumn();
ck65('第二名邮件标题=斩龙前三', str_contains($titleB, '斩龙前三'), $titleB);

// 4) 幂等：再次调用 drg_rewards 不应重复发奖
$mA2 = (int) db()->query('SELECT COUNT(*) FROM mails WHERE uid=' . $uid1)->fetchColumn();
drg_rewards($mid);
$mA3 = (int) db()->query('SELECT COUNT(*) FROM mails WHERE uid=' . $uid1)->fetchColumn();
ck65('重复发奖被拦截', $mA3 === $mA2, "before=$mA2 after=$mA3");

// 5) 已死后继续攻击：不再判死/记伤害/发奖
$dmgBefore = drg_my_dmg($uid2, $mid);
$k4 = drg_apply_damage($uid2, 'B', $mid, 999);
ck65('死后攻击无效', $k4 === false && drg_my_dmg($uid2, $mid) === $dmgBefore, var_export($k4, true));

// 清理（还原龙与测试数据）
db()->prepare('DELETE FROM wb_dmg WHERE mid=?')->execute([$mid]);
db()->prepare('DELETE FROM users WHERE id IN (?, ?)')->execute([$uid1, $uid2]);
db()->prepare('DELETE FROM mails WHERE uid IN (?, ?)')->execute([$uid1, $uid2]);
db()->prepare('DELETE FROM mats WHERE uid IN (?, ?)')->execute([$uid1, $uid2]);
db()->prepare('DELETE FROM equips WHERE uid IN (?, ?)')->execute([$uid1, $uid2]);
drg_kv_set('wb_hp_' . $mid, drg_max_hp($mid));
drg_kv_set('wb_done_' . $mid, 0);
drg_kv_set('wb_next_' . $mid, 0);
drg_kv_set('wb_rewarded_' . $mid, 0);

echo "\nOK=$ok FAIL=$bad\n";
