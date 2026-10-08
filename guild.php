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
        flash_set('会长不能直接退，先转让会长再退。');
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
    } elseif ($n < 10000) {
        flash_set('最少捐1金（10000铜）。');
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
if ($a === 'levelup') {
    $g = my_guild($uid);
    if (!$g || ($g['role'] ?? '') !== 'leader') {
        flash_set('只有会长能升级公会。');
    } else {
        flash_set(guild_level_up((int) $g['id']));
    }
    header('Location: guild.php');
    exit;
}
if ($a === 'role') {
    $g = my_guild($uid);
    $tu = (int) ($_GET['uid'] ?? 0);
    $nr = (string) ($_GET['r'] ?? '');
    if (!$g || ($g['role'] ?? '') !== 'leader') {
        flash_set('只有会长能任免。');
    } elseif (!in_array($nr, ['vice', 'officer', 'member'], true)) {
        flash_set('没有这个职位。');
    } elseif ($tu === $uid) {
        flash_set('不能给自己设。转让会长用下面的转让。');
    } else {
        $st = db()->prepare('SELECT role FROM guild_members WHERE uid=? AND gid=?');
        $st->execute([$tu, (int) $g['id']]);
        if (!$st->fetch()) {
            flash_set('他不是本公会成员。');
        } else {
            if ($nr === 'vice') {
                $c = (int) (db()->query('SELECT COUNT(*) FROM guild_members WHERE gid=' . (int) $g['id'] . ' AND role="vice"')->fetchColumn() ?: 0);
                if ($c >= 1) {
                    flash_set('副会长只能有1个，先撤掉现任。');
                    header('Location: guild.php');
                    exit;
                }
            }
            if ($nr === 'officer') {
                $c = (int) (db()->query('SELECT COUNT(*) FROM guild_members WHERE gid=' . (int) $g['id'] . ' AND role="officer"')->fetchColumn() ?: 0);
                if ($c >= 2) {
                    flash_set('管理员只能有2个，先撤掉一个。');
                    header('Location: guild.php');
                    exit;
                }
            }
            db()->prepare('UPDATE guild_members SET role=? WHERE uid=?')->execute([$nr, $tu]);
            flash_set('任命成功。');
        }
    }
    header('Location: guild.php');
    exit;
}
if ($a === 'transfer') {
    $g = my_guild($uid);
    $tu = (int) ($_GET['uid'] ?? 0);
    if (!$g || ($g['role'] ?? '') !== 'leader') {
        flash_set('只有会长能转让。');
    } else {
        $st = db()->prepare('SELECT role FROM guild_members WHERE uid=? AND gid=?');
        $st->execute([$tu, (int) $g['id']]);
        if (!$st->fetch()) {
            flash_set('他不是本公会成员。');
        } else {
            db()->prepare('UPDATE guild_members SET role="member" WHERE uid=?')->execute([$uid]);
            db()->prepare('UPDATE guild_members SET role="leader" WHERE uid=?')->execute([$tu]);
            db()->prepare('UPDATE guilds SET leader_uid=? WHERE id=?')->execute([$tu, (int) $g['id']]);
            flash_set('会长已转让。');
        }
    }
    header('Location: guild.php');
    exit;
}
if ($a === 'kick') {
    $g = my_guild($uid);
    $tu = (int) ($_GET['uid'] ?? 0);
    if (!$g || !in_array($g['role'] ?? '', ['leader', 'vice', 'officer'], true)) {
        flash_set('管理以上才能踢人。');
    } else {
        $st = db()->prepare('SELECT role FROM guild_members WHERE uid=? AND gid=?');
        $st->execute([$tu, (int) $g['id']]);
        $tr = $st->fetchColumn();
        if (!$tr) {
            flash_set('他不是本公会成员。');
        } elseif ($tr === 'leader' || ($tr === 'vice' && ($g['role'] ?? '') !== 'leader')) {
            flash_set('权限不够踢他。');
        } else {
            db()->prepare('DELETE FROM guild_members WHERE uid=?')->execute([$tu]);
            flash_set('已踢出。');
        }
    }
    header('Location: guild.php');
    exit;
}
if ($a === 'wput') {
    $g = my_guild($uid);
    if (!$g) {
        flash_set('你没有公会。');
    } else {
        $cap = guild_warehouse_cap((int) $g['level']);
        $cnt = guild_warehouse_count((int) $g['id']);
        if ($cnt >= $cap) {
            flash_set('仓库满了（' . $cap . '格），升级公会扩容。');
        } else {
            $kind = (string) ($_GET['kind'] ?? '');
            if ($kind === 'mat') {
                $mid = (string) ($_GET['mid'] ?? '');
                $n = max(1, (int) ($_GET['n'] ?? 1));
                $mats = mats_of($uid);
                if (!isset($mats[$mid]) || (int) $mats[$mid] < $n) {
                    flash_set('你没有这么多【' . mat_name($mid) . '】。');
                } elseif (!is_tradable_mat($mid)) {
                    flash_set('【' . mat_name($mid) . '】不可交易，不能存仓库。');
                } else {
                    add_mat($uid, $mid, -$n);
                    db()->prepare('INSERT INTO guild_warehouse (gid, kind, mat_id, qty, donor_uid, donor_name, created_at) VALUES (?, "mat", ?, ?, ?, ?, ?)')->execute([(int) $g['id'], $mid, $n, $uid, (string) $u['username'], time()]);
                    flash_set('存入【' . mat_name($mid) . '】x' . $n . '。');
                }
            } elseif ($kind === 'equip') {
                $eid = (int) ($_GET['eid'] ?? 0);
                $st = db()->prepare('SELECT * FROM equips WHERE id=? AND uid=? AND pos=""');
                $st->execute([$eid, $uid]);
                $eq = $st->fetch();
                if (!$eq) {
                    flash_set('这件装备不在背包（穿着的先脱下）。');
                } else {
                    db()->prepare('UPDATE equips SET uid=0, pos="gwarehouse" WHERE id=?')->execute([$eid]);
                    db()->prepare('INSERT INTO guild_warehouse (gid, kind, equip_id, qty, donor_uid, donor_name, created_at) VALUES (?, "equip", ?, 1, ?, ?, ?)')->execute([(int) $g['id'], $eid, $uid, (string) $u['username'], time()]);
                    _gear_uncache($uid);
                    flash_set('存入【' . equip_shortname((string) $eq['name']) . '】。');
                }
            } else {
                flash_set('存什么？');
            }
        }
    }
    header('Location: guild.php?tab=wh');
    exit;
}
if ($a === 'wtake') {
    $g = my_guild($uid);
    $wid = (int) ($_GET['id'] ?? 0);
    if (!$g) {
        flash_set('你没有公会。');
    } elseif (!guild_can_take_warehouse($g)) {
        flash_set('副会长以上才能取出。');
    } else {
        $st = db()->prepare('SELECT * FROM guild_warehouse WHERE id=? AND gid=?');
        $st->execute([$wid, (int) $g['id']]);
        $w = $st->fetch();
        if (!$w) {
            flash_set('仓库没这件。');
        } else {
            if (($w['kind'] ?? '') === 'mat') {
                if (bag_full($uid)) {
                    flash_set('背包满了，先清理。');
                    header('Location: guild.php?tab=wh');
                    exit;
                }
                add_mat($uid, (string) $w['mat_id'], (int) $w['qty']);
                db()->prepare('DELETE FROM guild_warehouse WHERE id=?')->execute([$wid]);
                flash_set('取出【' . mat_name((string) $w['mat_id']) . '】x' . (int) $w['qty'] . '。');
            } else {
                db()->prepare('UPDATE equips SET uid=?, pos="" WHERE id=? AND pos="gwarehouse"')->execute([$uid, (int) $w['equip_id']]);
                db()->prepare('DELETE FROM guild_warehouse WHERE id=?')->execute([$wid]);
                _gear_uncache($uid);
                flash_set('取出装备。');
            }
        }
    }
    header('Location: guild.php?tab=wh');
    exit;
}
if ($a === 'invite') {
    $g = my_guild($uid);
    $tu = (int) ($_GET['uid'] ?? 0);
    if (!$g) {
        flash_set('你没有公会。');
    } elseif (!guild_can_invite($g)) {
        flash_set('管理以上才能邀请。');
    } else {
        $t = user_by_id($tu);
        if (!$t) {
            flash_set('没有这个人。');
        } elseif (my_guild($tu)) {
            flash_set('他已经有公会了。');
        } else {
            db()->prepare('INSERT INTO guild_invites (gid, inviter_uid, target_uid, status, created_at) VALUES (?, ?, ?, "open", ?)')->execute([(int) $g['id'], $uid, $tu, time()]);
            send_mail($tu, (string) $u['username'], 'guild_invite', '公会邀请：【' . $g['name'] . '】', (string) $u['username'] . '邀请你加入【' . $g['name'] . '】(' . (int) $g['level'] . '级)，去公会页接受。', []);
            flash_set('邀请邮件已发出。');
        }
    }
    header('Location: look.php?id=' . $tu);
    exit;
}
if ($a === 'accept_invite') {
    $iid = (int) ($_GET['id'] ?? 0);
    $st = db()->prepare('SELECT * FROM guild_invites WHERE id=? AND target_uid=? AND status="open"');
    $st->execute([$iid, $uid]);
    $inv = $st->fetch();
    if (!$inv) {
        flash_set('邀请失效了。');
    } elseif (my_guild($uid)) {
        flash_set('你已经有公会了。');
        db()->prepare('UPDATE guild_invites SET status="done" WHERE id=?')->execute([$iid]);
    } else {
        db()->prepare('INSERT INTO guild_members (uid, gid, role, contrib, joined_at) VALUES (?, ?, "member", 0, ?)')->execute([$uid, (int) $inv['gid'], time()]);
        db()->prepare('UPDATE guild_invites SET status="done" WHERE id=?')->execute([$iid]);
        flash_set('加入成功！');
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

$tab = (string) ($_GET['tab'] ?? '');
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
    $st = db()->prepare('SELECT i.*, g.name AS gname FROM guild_invites i JOIN guilds g ON g.id=i.gid WHERE i.target_uid=? AND i.status="open" ORDER BY i.id DESC LIMIT 5');
    $st->execute([$uid]);
    $hasInv = false;
    while ($inv = $st->fetch()) {
        if (!$hasInv) {
            echo '【我的邀请】<br>';
            $hasInv = true;
        }
        echo '·【' . h($inv['gname']) . '】 <a href="guild.php?a=accept_invite&id=' . $inv['id'] . '">接受</a><br>';
    }
    if ($hasInv) {
        echo '<div class="hr">--------</div>';
    }
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
    echo '<a href="guild.php">总览</a> <a href="guild.php?tab=wh">仓库(' . guild_warehouse_count((int) $g['id']) . '/' . guild_warehouse_cap((int) $g['level']) . ')</a><br>';
    echo '【我的公会】' . h($g['name']) . ' ' . (int) $g['level'] . '级（经验' . (int) $g['exp'] . '/' . guild_level_need((int) $g['level']) . '）<br>';
    echo '身份：' . h(guild_role_name((string) ($g['role'] ?? 'member'))) . '　贡献：' . (int) $g['contrib'] . '<br>';
    echo '公告：' . h($g['notice'] === '' ? '暂无' : $g['notice']) . '<br>';
    echo '加成：全员打怪经验+' . guild_exp_bonus((int) $g['level']) . '%　仓库' . guild_warehouse_cap((int) $g['level']) . '格<br>';
    $w = open_war((int) $g['id']);
    if ($w) {
        $mine = ((int) $w['a_gid'] === (int) $g['id']) ? (int) $w['a_score'] : (int) $w['b_score'];
        $foe = ((int) $w['a_gid'] === (int) $g['id']) ? (int) $w['b_score'] : (int) $w['a_score'];
        echo '<div class="warn">交战中！我方战功' . $mine . '：敌方' . $foe . '，剩' . h(dummy_fmt(max(0, (int) $w['ends_at'] - time()))) . '</div>';
    }
    if ($tab === 'wh') {
        echo '<div class="hr">--------</div>【公会仓库】' . guild_warehouse_count((int) $g['id']) . '/' . guild_warehouse_cap((int) $g['level']) . '格（成员可存可交易物，副会长以上可取）<br>';
        $st = db()->prepare('SELECT * FROM guild_warehouse WHERE gid=? ORDER BY id DESC LIMIT 50');
        $st->execute([(int) $g['id']]);
        $has = false;
        while ($wr = $st->fetch()) {
            $has = true;
            if (($wr['kind'] ?? '') === 'mat') {
                echo '·【' . h(mat_name((string) $wr['mat_id'])) . '】x' . (int) $wr['qty'] . '(存:' . h($wr['donor_name']) . ')';
            } else {
                $eq = db()->query('SELECT name FROM equips WHERE id=' . (int) $wr['equip_id'])->fetch();
                echo '·【' . h(equip_shortname((string) ($eq['name'] ?? '装备'))) . '】(存:' . h($wr['donor_name']) . ')';
            }
            if (guild_can_take_warehouse($g)) {
                echo ' <a href="guild.php?a=wtake&id=' . $wr['id'] . '">取出</a>';
            }
            echo '<br>';
        }
        if (!$has) {
            echo '<span class="muted">空。去背包把可交易材料/装备存进来。</span><br>';
        }
        echo '存入：<a href="bag.php?tab=mat">去材料背包</a> <a href="bag.php?tab=equip">去装备背包</a><br>';
    } else {
        $st = db()->prepare('SELECT u.id, u.username, u.lv, m.role, m.contrib FROM guild_members m JOIN users u ON u.id=m.uid WHERE m.gid=? ORDER BY m.contrib DESC LIMIT 30');
        $st->execute([(int) $g['id']]);
        echo '成员：<br>';
        while ($m = $st->fetch()) {
            echo '·' . h($m['username']) . (int) $m['lv'] . '级(' . h(guild_role_name((string) $m['role'])) . ',贡献' . (int) $m['contrib'] . ')';
            if (($g['role'] ?? '') === 'leader' && (int) $m['id'] !== $uid) {
                echo ' <a href="guild.php?a=role&uid=' . $m['id'] . '&r=vice">设副会长</a> <a href="guild.php?a=role&uid=' . $m['id'] . '&r=officer">设管理</a> <a href="guild.php?a=role&uid=' . $m['id'] . '&r=member">撤职</a> <a href="guild.php?a=transfer&uid=' . $m['id'] . '">转让会长</a>';
            }
            if (in_array($g['role'] ?? '', ['leader', 'vice', 'officer'], true) && (int) $m['id'] !== $uid) {
                echo ' <a href="guild.php?a=kick&uid=' . $m['id'] . '">踢</a>';
            }
            echo '<br>';
        }
        echo '<form method="post" action="guild.php?a=donate">';
        echo '捐铜<input name="gold" size="6" value="10000"><input type="submit" value="捐献(最少1金)">';
        echo '</form>';
        if (($g['role'] ?? '') === 'leader') {
            echo '<a href="guild.php?a=levelup">升级公会(消耗全员贡献)</a><br>';
            echo '<form method="post" action="guild.php?a=notice">';
            echo '<input name="notice" maxlength="60" placeholder="改公告"><input type="submit" value="发布">';
            echo '</form>';
        }
    }
    echo '<a href="guild.php?a=leave">退出公会</a><br>';
}
nav_line();
wap_end(false);
