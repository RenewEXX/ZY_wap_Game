<?php
// dhtest66：转职流程——覆盖全部5职业的1觉选职业、全部10个2觉分支、2觉加冕、以及各类拦截
define('DH_TEST_MODE', true);
require 'includes/bootstrap.php';
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
$ok = 0; $bad = 0;
function ck66(string $l, bool $c, string $d = '') { global $ok, $bad; if ($c) { $ok++; } else { $bad++; echo "FAIL $l $d\n"; } }

function mk66(string $job): int
{
    $uid = (int) user_create('rb66' . mt_rand(100000, 999999), 'x', $job, 'z1', true, 'tmp');
    return $uid;
}
function rm66(int $uid): void
{
    db()->prepare('DELETE FROM users WHERE id=?')->execute([$uid]);
    db()->prepare('DELETE FROM mails WHERE uid=?')->execute([$uid]);
    db()->prepare('DELETE FROM mats WHERE uid=?')->execute([$uid]);
    db()->prepare('DELETE FROM equips WHERE uid=?')->execute([$uid]);
}
function mailCnt66(int $uid, string $title): int
{
    $st = db()->prepare('SELECT COUNT(*) FROM mails WHERE uid=? AND title=?');
    $st->execute([$uid, $title]);
    return (int) $st->fetchColumn();
}

// ============ 1觉：各职业只做本职业试炼，并觉醒为本职业路线 ============
$jobs1 = ['warrior' => 'w3', 'mage' => 'm3', 'hunter' => 'h3', 'priest' => 'p3', 'summoner' => 's3'];
$ownFlag1 = ['warrior' => 'rb1_war', 'mage' => 'rb1_mag', 'hunter' => 'rb1_hun', 'priest' => 'rb1_pri', 'summoner' => 'rb1_sum'];
foreach ($jobs1 as $job => $sk) {
    $uid = mk66($job);
    cflag_set($uid, $ownFlag1[$job], 1); // 只做本职业试炼
    $u = user_by_id($uid);
    $u['quest'] = 45; $u['gold'] = 0; user_save($u);
    $r = rb1_choose_job($uid, $job);
    $u = user_by_id($uid);
    ck66("1觉[$job]完成提示", str_contains($r, '1觉完成'), $r);
    ck66("1觉[$job] rb1_job=$job", (cflags($uid)['rb1_job'] ?? '') === $job, json_encode(cflags($uid)));
    ck66("1觉[$job] quest=46", (int) $u['quest'] === 46, (string) $u['quest']);
    ck66("1觉[$job] 金币+20000", (int) $u['gold'] === 20000, (string) $u['gold']);
    ck66("1觉[$job] 秘籍book_$sk", (int) (mats_of($uid)['book_' . $sk] ?? 0) === 1, (string) (mats_of($uid)['book_' . $sk] ?? 0));
    ck66("1觉[$job] 邮件", mailCnt66($uid, '1觉') === 1, (string) mailCnt66($uid, '1觉'));
    rm66($uid);
}

// ============ 1觉：不跨职业 ============
// 法师只做本职业试炼即可（不需要战/猎/牧）
$uid = mk66('mage');
cflag_set($uid, 'rb1_mag', 1);
$u = user_by_id($uid); $u['quest'] = 45; user_save($u);
$r = rb1_choose_job($uid, 'mage');
ck66('法师只做本职业试炼即可', str_contains($r, '1觉完成'), $r);
rm66($uid);

// 召唤师不填 rb1_war/mag/hun/pri 也能觉醒（只需 rb1_sum）
$uid = mk66('summoner');
cflag_set($uid, 'rb1_sum', 1);
$u = user_by_id($uid); $u['quest'] = 45; user_save($u);
$r = rb1_choose_job($uid, 'summoner');
ck66('召唤师只需深渊试炼即可', str_contains($r, '1觉完成'), $r);
rm66($uid);

// 缺本职业试炼 → 被拦
$uid = mk66('summoner');
$u = user_by_id($uid); $u['quest'] = 45; user_save($u);
$r = rb1_choose_job($uid, 'summoner');
ck66('召唤师缺深渊试炼被拦', str_contains($r, '还没完成') && empty(cflags($uid)['rb1_job']), $r);
rm66($uid);

// 试图觉醒别的职业 → 被拦
$uid = mk66('warrior');
cflag_set($uid, 'rb1_war', 1);
cflag_set($uid, 'rb1_mag', 1);
$u = user_by_id($uid); $u['quest'] = 45; user_save($u);
$r = rb1_choose_job($uid, 'mage');
ck66('跨职业觉醒被拦', str_contains($r, '别走岔了') && empty(cflags($uid)['rb1_job']), $r);
rm66($uid);

// 重复选择 → 被拦
$uid = mk66('priest');
cflag_set($uid, 'rb1_pri', 1);
$u = user_by_id($uid); $u['quest'] = 45; user_save($u);
rb1_choose_job($uid, 'priest');
$r = rb1_choose_job($uid, 'priest');
ck66('1觉重复选择被拦', str_contains($r, '路已经选了'), $r);
rm66($uid);

// ============ 2觉：全部10个分支各选一次 ============
$branches = [
    'warrior' => ['berserker' => 'w5', 'aegis' => 'w6'],
    'mage' => ['wintermage' => 'm5', 'wildfire' => 'm6'],
    'hunter' => ['ranger' => 'h5', 'assassin' => 'h6'],
    'priest' => ['shadowbishop' => 'p5', 'holybishop' => 'p6'],
    'summoner' => ['necro' => 's5', 'plague' => 's6'],
];
foreach ($branches as $base => $list) {
    foreach ($list as $branch => $sk) {
        $uid = mk66($base);
        $u = user_by_id($uid);
        $u['quest'] = 79; user_save($u);
        $r = rb2_choose_branch($uid, $branch);
        $u = user_by_id($uid);
        ck66("2觉[$branch]选分支成功", str_contains($r, '你选择了'), $r);
        ck66("2觉[$branch] rb2_job=$branch", (cflags($uid)['rb2_job'] ?? '') === $branch, json_encode(cflags($uid)));
        ck66("2觉[$branch] quest=80", (int) $u['quest'] === 80, (string) $u['quest']);
        ck66("2觉[$branch] 秘籍book_$sk", (int) (mats_of($uid)['book_' . $sk] ?? 0) === 1, (string) (mats_of($uid)['book_' . $sk] ?? 0));
        rm66($uid);
    }
}

// ============ 2觉：错职业分支被拦 ============
$uid = mk66('warrior');
$u = user_by_id($uid); $u['quest'] = 79; user_save($u);
$r = rb2_choose_branch($uid, 'wintermage');
ck66('2觉错职业被拦', str_contains($r, '这不是你的路') && empty(cflags($uid)['rb2_job']), $r);
rm66($uid);

// 重复选分支被拦
$uid = mk66('summoner');
$u = user_by_id($uid); $u['quest'] = 79; user_save($u);
rb2_choose_branch($uid, 'necro');
$r = rb2_choose_branch($uid, 'plague');
ck66('2觉重复选分支被拦', str_contains($r, '路已经选了'), $r);
rm66($uid);

// ============ 2觉加冕（用死灵分支验证，奖励与职业无关） ============
$uid = mk66('summoner');
$u = user_by_id($uid); $u['quest'] = 79; user_save($u);
rb2_choose_branch($uid, 'necro');
$u = user_by_id($uid); $u['quest'] = 80; $u['gold'] = 0; user_save($u);
$r = rb2_crown($uid);
$u = user_by_id($uid); $mt = mats_of($uid);
ck66('2觉加冕提示', str_contains($r, '加冕完成'), $r);
ck66('2觉 rb2_done=1', !empty(cflags($uid)['rb2_done']), json_encode(cflags($uid)));
ck66('2觉 quest=81', (int) $u['quest'] === 81, (string) $u['quest']);
ck66('2觉 金币+100金', (int) $u['gold'] === 1000000, (string) $u['gold']);
ck66('2觉 洗点药×1', (int) ($mt['reset_potion'] ?? 0) === 1, (string) ($mt['reset_potion'] ?? 0));
ck66('2觉 神魂碎片≥20', (int) ($mt['soul_shard'] ?? 0) >= 20, (string) ($mt['soul_shard'] ?? 0));
ck66('2觉 2觉证明×1', (int) ($mt['rb2_proof'] ?? 0) === 1, (string) ($mt['rb2_proof'] ?? 0));
ck66('2觉 邮件', mailCnt66($uid, '2觉') === 1, (string) mailCnt66($uid, '2觉'));
$r = rb2_crown($uid);
ck66('2觉重复加冕被拦', str_contains($r, '已经加冕'), $r);
rm66($uid);

echo "\nOK=$ok FAIL=$bad\n";
