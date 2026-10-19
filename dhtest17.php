<?php
define('DH_TEST_MODE', true);
require 'includes/bootstrap.php';
// 召唤师新手流程：技能书缺失 + 副手契匣缺失（并确认其余四职业无此问题）
$ok = 0; $bad = 0;
function ckN(string $l, $got, $want) { global $ok,$bad; if ($got === $want) { $ok++; } else { $bad++; echo "FAIL $l: got=",var_export($got,true)," want=",var_export($want,true),"\n"; } }

function wipeChar(int $uid): void
{
    db()->exec('DELETE FROM pets WHERE uid=' . (int) $uid);
    db()->exec('DELETE FROM skills WHERE uid=' . (int) $uid);
    db()->exec('DELETE FROM users WHERE id=' . (int) $uid);
    db()->exec('DELETE FROM equips WHERE uid=' . (int) $uid);
    db()->exec('DELETE FROM mats WHERE uid=' . (int) $uid);
}

// —— 1. 全职业技能书完整：每个职业技能序列都有对应的书 ——
$books = skill_books();
foreach (['warrior', 'mage', 'hunter', 'priest', 'summoner'] as $job) {
    foreach (job_skillset($job) as $sid) {
        ckP_book($job . ' 有 ' . $sid . ' 的书', isset($books['book_' . $sid]), true);
    }
}
function ckP_book(string $l, $got, $want) { global $ok,$bad; if ($got === $want) { $ok++; } else { $bad++; echo "FAIL $l: got=",var_export($got,true)," want=",var_export($want,true),"\n"; } }

// —— 2. 召唤师 s1~s8 的书都在表里，且名字不是空 ——
foreach (['s1', 's2', 's3', 's4', 's5', 's6', 's7', 's8', 's9'] as $sid) {
    ckN('book_' . $sid . '存在', isset($books['book_' . $sid]), true);
    ckN('book_' . $sid . '有名字', ($books['book_' . $sid]['name'] ?? '') !== '', true);
    ckN('book_' . $sid . '指向正确技能', (string) ($books['book_' . $sid]['skill'] ?? ''), $sid);
}

// —— 3. 书能显示名字（mat_name 走 skill_books 分支） ——
foreach (['s1', 's2', 's3', 's4'] as $sid) {
    ckN('book_' . $sid . '显示名字', mat_name('book_' . $sid), (string) $books['book_' . $sid]['name']);
}

// —— 4. 召唤师能真正学会 s1（走完 use_skill_book） ——
$uid = user_create('tnwf1', 'x', 'summoner', 'z1', true, 'tmp');
$u = user_by_id($uid);
$u['lv'] = 5; $u['quest'] = 3; $u['hp'] = 100000; $u['maxhp'] = 100000; user_save($u);
add_mat((int) $uid, 'book_s1', 1);
$res = use_skill_book((int) $uid, 'book_s1');
ckN('学会s1', str_contains($res, '学会'), true);
$sk = db()->query('SELECT skill FROM skills WHERE uid=' . (int) $uid)->fetchAll();
$skNames = array_column($sk, 'skill');
ckN('技能表里有s1', in_array('s1', $skNames, true), true);
ckN('s1设为默认', (string) (db()->query('SELECT skill FROM skills WHERE uid=' . (int) $uid . ' AND active=1')->fetchColumn() ?: ''), 's1');
wipeChar((int) $uid);

// —— 5. 召唤师新手期必须拿到可用的副手契匣（带开场自动召唤） ——
$uid = user_create('tnwf2', 'x', 'summoner', 'z1', true, 'tmp');
$u = user_by_id($uid);
$u['lv'] = 5; $u['quest'] = 3; $u['hp'] = 100000; $u['maxhp'] = 100000; user_save($u);
// 模拟咕噜关：发主手引魂灯 + 副手契匣并穿上
make_equip((int) $uid, 'weapon', '深渊引魂灯', 1, 5);
make_equip((int) $uid, 'offhand', '残魂灯匣', 0, 5);
summoner_wear_box((int) $uid);
$worn = summoner_offhand_worn((int) $uid);
ckN('召唤师有穿戴中的契匣', $worn !== [], true);
ckN('契匣是残魂灯匣', (string) ($worn['name'] ?? ''), '残魂灯匣');
// 契匣带开场自动召唤
$uw = user_by_id($uid);
$openMsg = summoner_open_message($uw);
ckN('开场自动召唤生效', str_contains($openMsg, '匣门自开'), true);
ckN('开场召唤出了小鬼', str_contains($openMsg, '深渊小鬼'), true);
wipeChar((int) $uid);

// —— 6. 非召唤职业不应拿到契匣（副手仍归其它装备） ——
$uid = user_create('tnwf3', 'x', 'warrior', 'z1', true, 'tmp');
make_equip((int) $uid, 'weapon', '生锈战斧', 0, 1);
ckN('战士没有契匣', summoner_offhand_worn((int) $uid), []);
wipeChar((int) $uid);

// —— 7. 2觉群攻秘籍池含召唤师（其它职业本来就有，召唤师之前被漏） ——
$src = file_get_contents(__DIR__ . '/includes/game.php');
ckN('ash_titan池含s5', str_contains($src, "'book_s5', 'book_s6'"), true);
ckN('vx_tyrant池含s7', str_contains($src, "'book_s7', 'book_s8'"), true);

// —— 8. 2觉秘籍学习门槛：s5~s8 没2觉就学不了 ——
$uid = user_create('tnwf4', 'x', 'summoner', 'z1', true, 'tmp');
$u = user_by_id($uid);
$u['lv'] = 700; user_save($u);
foreach (['s5' => 'necro', 's6' => 'plague', 's7' => 'necro', 's8' => 'plague'] as $sid => $rb2) {
    add_mat((int) $uid, 'book_' . $sid, 1);
    $res = use_skill_book((int) $uid, 'book_' . $sid);
    ckN($sid . '未2觉学不了', str_contains($res, '2觉'), true);
    // 2觉选对路线后就能学
    cflag_set((int) $uid, 'rb2_job', $rb2);
    $res2 = use_skill_book((int) $uid, 'book_' . $sid);
    ckN($sid . '2觉后能学', str_contains($res2, '学会'), true);
}
wipeChar((int) $uid);

echo "\nOK=$ok FAIL=$bad\n";
