<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

$u = require_login();
$uid = (int) $u['id'];
$a = (string) ($_GET['a'] ?? '');

if ($a === 'create') {
    if (my_party($uid)) {
        flash_set('你已经在队伍里了。');
    } else {
        db()->prepare('INSERT INTO parties (leader_uid, created_at) VALUES (?, ?)')->execute([$uid, time()]);
        $pid = (int) db()->lastInsertId();
        db()->prepare('INSERT INTO party_members (uid, pid, joined_at) VALUES (?, ?, ?)')->execute([$uid, $pid, time()]);
        flash_set('建队成功，你是队长。叫人来同张地图，经验+10%。');
    }
    header('Location: party.php');
    exit;
}
if ($a === 'join') {
    $pid = (int) ($_GET['pid'] ?? 0);
    if (my_party($uid)) {
        flash_set('先退出现队伍。');
    } else {
        $st = db()->prepare('SELECT id FROM parties WHERE id=?');
        $st->execute([$pid]);
        if (!$st->fetch()) {
            flash_set('没有这支队伍。');
        } elseif ((int) db()->query('SELECT COUNT(*) FROM party_members WHERE pid=' . $pid)->fetchColumn() >= 5) {
            flash_set('队伍满了（5人）。');
        } else {
            db()->prepare('INSERT INTO party_members (uid, pid, joined_at) VALUES (?, ?, ?)')->execute([$uid, $pid, time()]);
            flash_set('入队成功！');
        }
    }
    header('Location: party.php');
    exit;
}
if ($a === 'leave') {
    $p = my_party($uid);
    if ($p) {
        db()->prepare('DELETE FROM party_members WHERE uid=?')->execute([$uid]);
        if ((int) db()->query('SELECT COUNT(*) FROM party_members WHERE pid=' . (int) $p['id'])->fetchColumn() === 0) {
            db()->prepare('DELETE FROM parties WHERE id=?')->execute([(int) $p['id']]);
        } elseif ((int) $p['leader_uid'] === $uid) {
            db()->exec('UPDATE parties SET leader_uid=(SELECT uid FROM party_members WHERE pid=' . (int) $p['id'] . ' ORDER BY joined_at LIMIT 1) WHERE id=' . (int) $p['id']);
        }
        flash_set('离队了。');
    }
    header('Location: party.php');
    exit;
}
if ($a === 'kick') {
    $p = my_party($uid);
    $kid = (int) ($_GET['uid'] ?? 0);
    if (!$p || (int) $p['leader_uid'] !== $uid) {
        flash_set('只有队长能踢人。');
    } elseif ($kid === $uid) {
        flash_set('不能踢自己。');
    } else {
        db()->prepare('DELETE FROM party_members WHERE uid=? AND pid=?')->execute([$kid, (int) $p['id']]);
        flash_set('踢掉了。');
    }
    header('Location: party.php');
    exit;
}

wap_start('组队');
$flash = flash_get();
if ($flash !== '') {
    echo '<div class="warn">' . h($flash) . '</div>';
}
$p = my_party($uid);
if ($p) {
    echo '【我的队伍】队长：' . h((user_by_id((int) $p['leader_uid'])['username'] ?? '?')) . '<br>';
    echo '队员（同图经验+10%）：<br>';
    foreach (party_mates((int) $p['id'], $uid) as $m) {
        echo '·' . h($m['username']) . (int) $m['lv'] . '级@' . h(loc($m['loc'])['name'] ?? $m['loc']);
        if ((int) $p['leader_uid'] === $uid) {
            echo ' <a href="party.php?a=kick&uid=' . $m['id'] . '">踢</a>';
        }
        echo '<br>';
    }
    echo '队伍频道：<a href="chat.php?ch=party">去喊话</a><br>';
    echo '<a href="party.php?a=leave">离队</a><br>';
} else {
    echo '<a href="party.php?a=create">创建队伍</a><br>';
    echo '<div class="hr">--------</div>【附近队伍】<br>';
    $st = db()->query('SELECT p.id, u.username AS lname, (SELECT COUNT(*) FROM party_members m WHERE m.pid=p.id) AS num FROM parties p JOIN users u ON u.id=p.leader_uid ORDER BY p.id DESC LIMIT 20');
    $n = 0;
    while ($r = $st->fetch()) {
        $n++;
        echo '·' . h($r['lname']) . '的队伍(' . $r['num'] . '/5) <a href="party.php?a=join&pid=' . $r['id'] . '">加入</a><br>';
    }
    if ($n === 0) {
        echo '<span class="muted">没人组队，自己开一个。</span><br>';
    }
}
nav_line();
wap_end(false);
