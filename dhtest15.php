<?php
define('DH_TEST_MODE', true);
require 'includes/bootstrap.php';
// 新号出生满血 + 罪值生命上限变动时血量跟随
$ok = 0; $bad = 0;
function ckN(string $l, $got, $want) { global $ok,$bad; if ($got === $want) { $ok++; } else { $bad++; echo "FAIL $l: got=",var_export($got,true)," want=",var_export($want,true),"\n"; } }

function newChar(string $name, string $job = 'warrior'): int
{
    $uid = user_create($name, 'x', $job, 'z1', true, 'tmp');
    return (int) $uid;
}

function wipeChar(int $uid): void
{
    db()->exec('DELETE FROM users WHERE id=' . (int) $uid);
    db()->exec('DELETE FROM equips WHERE uid=' . (int) $uid);
    db()->exec('DELETE FROM mats WHERE uid=' . (int) $uid);
}

// —— 1. 新号创建后满血，且 home.php 的 sin_sync_hp 之后仍然满血 ——
foreach (['warrior', 'mage', 'hunter', 'priest', 'summoner'] as $job) {
    $uid = newChar('tnew' . $job, $job);
    $u = user_by_id($uid);
    ckN($job . ' 创建即满血', (int) $u['hp'] >= (int) $u['maxhp'], true);
    $maxBefore = (int) $u['maxhp'];
    sin_sync_hp($uid); // home.php/status.php/fight.php 每次进入都会调
    $u = user_by_id($uid);
    ckN($job . ' sin_sync_hp后仍满血', (int) $u['hp'], (int) $u['maxhp']);
    ckN($job . ' 上限含罪值加成', (int) $u['maxhp'] > $maxBefore, true);
    // 反复调用不应继续改变（幂等）
    sin_sync_hp($uid);
    $u2 = user_by_id($uid);
    ckN($job . ' 重复调用幂等', (int) $u2['maxhp'], (int) $u['maxhp']);
    ckN($job . ' 重复调用后仍满血', (int) $u2['hp'], (int) $u2['maxhp']);
    wipeChar($uid);
}

// —— 2. 罪值>=10：加成取消，上限下降，血量按新上限截断（不越界） ——
$uid = newChar('tsinlose');
$u = user_by_id($uid);
sin_sync_hp($uid);
$u = user_by_id($uid);
$maxFull = (int) $u['maxhp'];
// 制造罪值（>=10 时+20%加成应取消）
$f = cflags($uid);
$f['sin'] = 50; $f['sin_f'] = 50.0;
db()->prepare('UPDATE users SET chapter_flags=? WHERE id=?')->execute([json_encode($f), $uid]);
sin_sync_hp($uid);
$u = user_by_id($uid);
ckN('罪值满血取消加成后仍满血', (int) $u['hp'], (int) $u['maxhp']);
ckN('罪值满血上限下降', (int) $u['maxhp'] < $maxFull, true);

// —— 3. 受伤状态下上限变动，不应把血加满（保持原有伤势） ——
$uid2 = newChar('tsinhurt');
$u = user_by_id($uid2);
sin_sync_hp($uid2);
$u = user_by_id($uid2);
$max2 = (int) $u['maxhp'];
$u['hp'] = 10; // 受伤
user_save($u);
// 触发上限再次变动：给一件带生命的装备
make_equip($uid2, 'ring', '生命戒指', 3, 100);
_gear_uncache($uid2);
sin_sync_hp($uid2);
$u = user_by_id($uid2);
ckN('受伤时上限增加不补满血', (int) $u['hp'] <= $max2, true);
ckN('受伤时血量不为负', (int) $u['hp'] >= 1, true);
ckN('受伤时血量不超上限', (int) $u['hp'] <= (int) $u['maxhp'], true);

wipeChar($uid);
wipeChar($uid2);
echo "\nOK=$ok FAIL=$bad\n";