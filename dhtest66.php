<?php
// dhtest66：转职流程（1觉选职业 / 2觉选分支 / 2觉加冕）——条件、状态、奖励、邮件
define('DH_TEST_MODE', true);
require 'includes/bootstrap.php';
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
$ok = 0; $bad = 0;
function ck66(string $l, bool $c, string $d = '') { global $ok, $bad; if ($c) { $ok++; } else { $bad++; echo "FAIL $l $d\n"; } }

$uid = (int) user_create('rb66' . mt_rand(10000, 99999), 'x', 'warrior', 'z1', true, 'tmp');

// ---- 1觉：四重试炼没做完时被拦 ----
cflag_set($uid, 'rb1_war', 1); // 只做了一关
$r = rb1_choose_job($uid, 'warrior');
ck66('试炼未完成被拦', str_contains($r, '试炼还没完'), $r);
ck66('未完成 rb1_job 为空', empty(cflags($uid)['rb1_job']), json_encode(cflags($uid)));

// ---- 1觉：四重试炼全做完 → 选战士 ----
foreach (['rb1_mag', 'rb1_hun', 'rb1_pri'] as $k) { cflag_set($uid, $k, 1); }
$u = user_by_id($uid);
$u['quest'] = 45; $u['gold'] = 0; user_save($u);
$g0 = (int) user_by_id($uid)['gold'];
$r = rb1_choose_job($uid, 'warrior');
$u = user_by_id($uid);
ck66('1觉完成提示', str_contains($r, '1觉完成'), $r);
ck66('1觉 rb1_job=warrior', (cflags($uid)['rb1_job'] ?? '') === 'warrior', json_encode(cflags($uid)));
ck66('1觉 quest=46', (int) $u['quest'] === 46, (string) $u['quest']);
ck66('1觉 金币+20000', (int) $u['gold'] - $g0 === 20000, ((int) $u['gold'] - $g0) . '');
ck66('1觉 发群攻秘籍(book_w3)', (int) (mats_of($uid)['book_w3'] ?? 0) === 1, (string) (mats_of($uid)['book_w3'] ?? 0));
$m = (int) db()->query('SELECT COUNT(*) FROM mails WHERE uid=' . $uid . " AND title='1觉'")->fetchColumn();
ck66('1觉 邮件已发', $m === 1, "m=$m");

// ---- 1觉：重复选择被拦 ----
$r2 = rb1_choose_job($uid, 'mage');
ck66('1觉 重复选择被拦', str_contains($r2, '路已经选了'), $r2);

// ---- 2觉：选分支，职业不符被拦 ----
cflag_set($uid, 'rb2_job', '');
$r = rb2_choose_branch($uid, 'wintermage'); // 法师分支，但该角色是战士
ck66('2觉 错职业被拦', str_contains($r, '这不是你的路'), $r);
ck66('2觉 错职业不写 rb2_job', empty(cflags($uid)['rb2_job']), json_encode(cflags($uid)));

// ---- 2觉：选对职业分支 berserker ----
$u = user_by_id($uid); $u['quest'] = 79; user_save($u);
$r = rb2_choose_branch($uid, 'berserker');
$u = user_by_id($uid);
ck66('2觉 选分支成功', str_contains($r, '你选择了'), $r);
ck66('2觉 rb2_job=berserker', (cflags($uid)['rb2_job'] ?? '') === 'berserker', json_encode(cflags($uid)));
ck66('2觉 quest=80', (int) $u['quest'] === 80, (string) $u['quest']);
ck66('2觉 发群攻秘籍(book_w5)', (int) (mats_of($uid)['book_w5'] ?? 0) === 1, (string) (mats_of($uid)['book_w5'] ?? 0));

// ---- 2觉：重复选分支被拦 ----
$r = rb2_choose_branch($uid, 'aegis');
ck66('2觉 重复选分支被拦', str_contains($r, '路已经选了'), $r);

// ---- 2觉：加冕 ----
$u = user_by_id($uid); $u['gold'] = 0; user_save($u);
$g0 = (int) user_by_id($uid)['gold'];
$r = rb2_crown($uid);
$u = user_by_id($uid);
$mt = mats_of($uid);
ck66('2觉 加冕提示', str_contains($r, '加冕完成'), $r);
ck66('2觉 rb2_done=1', !empty(cflags($uid)['rb2_done']), json_encode(cflags($uid)));
ck66('2觉 quest=81', (int) $u['quest'] === 81, (string) $u['quest']);
ck66('2觉 金币+1000000(100金)', (int) $u['gold'] - $g0 === 1000000, ((int) $u['gold'] - $g0) . '');
ck66('2觉 洗点药×1', (int) ($mt['reset_potion'] ?? 0) === 1, (string) ($mt['reset_potion'] ?? 0));
ck66('2觉 神魂碎片≥20', (int) ($mt['soul_shard'] ?? 0) >= 20, (string) ($mt['soul_shard'] ?? 0));
ck66('2觉 2觉证明×1', (int) ($mt['rb2_proof'] ?? 0) === 1, (string) ($mt['rb2_proof'] ?? 0));
$m = (int) db()->query('SELECT COUNT(*) FROM mails WHERE uid=' . $uid . " AND title='2觉'")->fetchColumn();
ck66('2觉 邮件已发', $m === 1, "m=$m");

// ---- 2觉：重复加冕被拦 ----
$r = rb2_crown($uid);
ck66('2觉 重复加冕被拦', str_contains($r, '已经加冕'), $r);

// ---- 清理 ----
db()->prepare('DELETE FROM users WHERE id=?')->execute([$uid]);
db()->prepare('DELETE FROM mails WHERE uid=?')->execute([$uid]);
db()->prepare('DELETE FROM mats WHERE uid=?')->execute([$uid]);
db()->prepare('DELETE FROM equips WHERE uid=?')->execute([$uid]);

echo "\nOK=$ok FAIL=$bad\n";
