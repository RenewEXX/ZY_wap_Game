<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

$u = require_login();
$uid = (int) $u['id'];
$a = (string) ($_GET['a'] ?? '');

if ($a === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    flash_set(guild_create($uid, (string) ($_POST['name'] ?? '')));
    header('Location: guild.php');
    exit;
}
if ($a === 'join') {
    $gid = (int) ($_GET['gid'] ?? 0);
    if (my_guild($uid)) {
        flash_set('你已经有公会了。');
    } else {
        $st = db()->prepare('SELECT id FROM guilds WHERE id=?');
        $st->execute([$gid]);
        if (!$st->fetch()) {
            flash_set('没有这个公会。');
        } else {
            db()->prepare('INSERT INTO guild_members (uid, gid, role, contrib, joined_at) VALUES (?, ?, "member", 0, ?)')->execute([$uid, $gid, time()]);
            flash_set('加入成功！');
        }
    }
    header('Location: guild.php');
    exit;
}
if ($a === 'leave') {
    $g = my_guild($uid);
    if (!$g) {
        flash_set('你没有公会。');
    } elseif (($g['role'] ?? '') === 'leader') {
        flash_set('会长不能直接退，先解散或转让（转让找客服）。');
    } else {
        db()->prepare('DELETE FROM guild_members WHERE uid=?')->execute([$uid]);
        flash_set('退出了公会。');
    }
    header('Location: guild.php');
    exit;
}
if ($a === 'donate') {
    $g = my_guild($uid);
    $n = max(0, (int) ($_POST['gold'] ?? 0));
    if (!$g) {
        flash_set('你没有公会。');
    } elseif ($n < 100) {
        flash_set('最少捐100铜。');
    } elseif ((int) $u['gold'] < $n) {
        flash_set('钱不够。');
    } else {
        $u['gold'] = (int) $u['gold'] - $n;
        user_save($u);
        db()->prepare('UPDATE guild_members SET contrib=contrib+? WHERE uid=?')->execute([$n, $uid]);
        $msg = guild_add_exp((int) $g['id'], (int) ($n / 100));
        flash_set('捐献' . fmt_money($n) . '，个人贡献+' . $n . '。' . $msg);
    }
    header('Location: guild.php');
    exit;
}
if ($a === 'notice' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $g = my_guild($uid);
    if (!$g || ($g['role'] ?? '') !== 'leader') {
        flash_set('只有会长能改公告。');
    } else {
        db()->prepare('UPDATE guilds SET notice=? WHERE id=?')->execute([mb_substr((string) ($_POST['notice'] ?? ''), 0, 60), (int) $g['id']]);
        flash_set('公告已更新。');
    }
    header('Location: guild.php');
    exit;
}
if ($a === 'war') {
    flash_set(declare_war($uid, (int) ($_GET['gid'] ?? 0)));
    header('Location: guild.php');
    exit;
}

wap_start('公会');
$flash = flash_get();
if ($flash !== '') {
    echo '<div class="warn">' . h($flash) . '</div>';
}
$g = my_guild($uid);
if ($g === null) {
    echo '【创建公会】10级、1金。<br>';
    echo '<form method="post" action="guild.php?a=create">';
    echo '<input name="name" maxlength="8" placeholder="公会名(8字内)">';
    echo '<input type="submit" value="创建">';
    echo '</form><div class="hr">--------</div>';
}
echo '【公会排行】<br>';
foreach (guild_rank() as $r) {
    echo '·' . (int) $r['level'] . '级【' . h($r['name']) . '】' . (int) $r['num'] . '人';
    if ($g === null) {
        echo ' <a href="guild.php?a=join&gid=' . $r['id'] . '">加入</a>';
    }
    if ($g && ($g['role'] ?? '') === 'leader' && (int) $r['id'] !== (int) $g['id']) {
        echo ' <a href="guild.php?a=war&gid=' . $r['id'] . '">宣战(50银)</a>';
    }
    echo '<br>';
}
if ($g) {
    echo '<div class="hr">--------</div>';
    echo '【我的公会】' . h($g['name']) . ' ' . (int) $g['level'] . '级（经验' . (int) $g['exp'] . '/' . guild_level_need((int) $g['level']) . '）<br>';
    echo '身份：' . (($g['role'] ?? '') === 'leader' ? '会长' : '成员') . '　贡献：' . (int) $g['contrib'] . '<br>';
    echo '公告：' . h($g['notice'] === '' ? '暂无' : $g['notice']) . '<br>';
    echo '加成：全员打怪经验+' . (int) $g['level'] . '%<br>';
    $w = open_war((int) $g['id']);
    if ($w) {
        $mine = ((int) $w['a_gid'] === (int) $g['id']) ? (int) $w['a_score'] : (int) $w['b_score'];
        $foe = ((int) $w['a_gid'] === (int) $g['id']) ? (int) $w['b_score'] : (int) $w['a_score'];
        echo '<div class="warn">交战中！我方战功' . $mine . '：敌方' . $foe . '，剩' . h(dummy_fmt(max(0, (int) $w['ends_at'] - time()))) . '</div>';
    }
    $st = db()->prepare('SELECT u.username, u.lv, m.role, m.contrib FROM guild_members m JOIN users u ON u.id=m.uid WHERE m.gid=? ORDER BY m.role DESC, m.contrib DESC LIMIT 30');
    $st->execute([(int) $g['id']]);
    echo '成员：<br>';
    while ($m = $st->fetch()) {
        echo '·' . h($m['username']) . (int) $m['lv'] . '级' . ($m['role'] === 'leader' ? '(会长)' : '') . '<br>';
    }
    echo '<form method="post" action="guild.php?a=donate">';
    echo '捐铜<input name="gold" size="6" value="1000"><input type="submit" value="捐献(100铜=1公会经验)">';
    echo '</form>';
    if (($g['role'] ?? '') === 'leader') {
        echo '<form method="post" action="guild.php?a=notice">';
        echo '<input name="notice" maxlength="60" placeholder="改公告"><input type="submit" value="发布">';
        echo '</form>';
    }
    echo '<a href="guild.php?a=leave">退出公会</a><br>';
}
nav_line();
wap_end(false);
