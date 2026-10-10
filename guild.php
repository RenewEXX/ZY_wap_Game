<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

$u = require_login();
$uid = (int) $u['id'];
$a = (string) ($_GET['a'] ?? '');

// 重大操作二次确认：链接不带yes=1时先弹确认页，防止误触
function guild_confirm(string $title, string $desc): void
{
    if ((string) ($_GET['yes'] ?? '') === '1') {
        return;
    }
    $qs = $_GET;
    $qs['yes'] = '1';
    $yesUrl = 'guild.php?' . http_build_query($qs);
    wap_start('确认操作');
    echo '<div class="warn">你确定吗？</div>';
    echo '【' . h($title) . '】<br>' . h($desc) . '<br>';
    echo '<div class="hr">--------</div>';
    echo '<a href="' . h($yesUrl) . '">确定</a>　<a href="guild.php">取消</a><br>';
    nav_line();
    wap_end(false);
    exit;
}

// 取仓库金是POST表单，走两步确认：第一步回显金额，第二步执行
function guild_confirm_take_gold(int $n): void
{
    wap_start('确认操作');
    echo '<div class="warn">你确定吗？</div>';
    echo '【取出仓库金】<br>取出' . h(fmt_money($n)) . '，个人贡献-' . intdiv($n, 10000) . '金，不可撤销！<br>';
    echo '<div class="hr">--------</div>';
    echo '<form method="post" action="guild.php?a=wwithgold">';
    echo '<input type="hidden" name="gold" value="' . $n . '">';
    echo '<input type="hidden" name="yes" value="1">';
    echo '<input type="submit" value="确定取出">';
    echo '</form><a href="guild.php?tab=wh">取消</a><br>';
    nav_line();
    wap_end(false);
    exit;
}

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
        $st = db()->prepare('SELECT zone FROM guilds WHERE id=?');
        $st->execute([$gid]);
        $grow = $st->fetch();
        if (!$grow) {
            flash_set('没有这个公会。');
        } elseif (($grow['zone'] ?? 'z1') !== (string) ($u['zone'] ?? 'z1')) {
            flash_set('跨大区不能加入别人的公会。');
        } else {
            db()->prepare('INSERT INTO guild_members (uid, gid, role, contrib, joined_at) VALUES (?, ?, "member", 0, ?)')->execute([$uid, $gid, time()]);
            flash_set('加入成功！');
        }
    }
    header('Location: guild.php');
    exit;
}
if ($a === 'leave') {
    guild_confirm('退出公会', '退出后贡献清零，想回要重新申请。');
    $g = my_guild($uid);
    if (!$g) {
        flash_set('你没有公会。');
    } elseif (($g['role'] ?? '') === 'leader') {
        flash_set('会长不能直接退：有人就转让会长，只剩你就解散公会。');
    } else {
        db()->prepare('DELETE FROM guild_members WHERE uid=?')->execute([$uid]);
        flash_set('退出了公会。');
    }
    header('Location: guild.php');
    exit;
}
if ($a === 'wdepgold') {
    $g = my_guild($uid);
    $n = max(0, (int) ($_POST['gold'] ?? 0));
    if (!$g) {
        flash_set('你没有公会。');
    } elseif ($n < 1) {
        flash_set('存多少？');
    } elseif ((int) $u['gold'] < $n) {
        flash_set('钱不够。');
    } else {
        $u['gold'] = (int) $u['gold'] - $n;
        user_save($u);
        db()->prepare('UPDATE guilds SET gold=gold+? WHERE id=?')->execute([$n, (int) $g['id']]);
        $cg = intdiv($n, 10000);
        if ($cg > 0) {
            db()->prepare('UPDATE guild_members SET contrib=contrib+? WHERE uid=?')->execute([$cg, $uid]);
        }
        guild_wh_log((int) $g['id'], $uid, (string) $u['username'], '存金', fmt_money($n));
        flash_set('存入仓库' . fmt_money($n) . '，个人贡献+' . $cg . '金' . ($cg <= 0 ? '（不足1金不计贡献）' : '') . '。');
    }
    header('Location: guild.php?tab=wh');
    exit;
}
if ($a === 'wwithgold') {
    $g = my_guild($uid);
    $n = max(0, (int) ($_POST['gold'] ?? 0));
    if (!$g) {
        flash_set('你没有公会。');
    } elseif (!guild_can_take_warehouse($g)) {
        flash_set('副会长以上才能取出。');
    } elseif ($n < 1) {
        flash_set('取多少？');
    } elseif ((int) ($g['gold'] ?? 0) < $n) {
        flash_set('仓库没这么多金。');
    } else {
        if ((string) ($_POST['yes'] ?? '') !== '1') {
            $_GET['a'] = 'wwithgold';
            $_GET['yes'] = '0';
            guild_confirm_take_gold($n);
        }
        db()->prepare('UPDATE guilds SET gold=gold-? WHERE id=?')->execute([$n, (int) $g['id']]);
        $u['gold'] = (int) $u['gold'] + $n;
        user_save($u);
        // 取金扣取金者自己的贡献，防止存取反复刷
        $cg = intdiv($n, 10000);
        if ($cg > 0) {
            db()->prepare('UPDATE guild_members SET contrib=CASE WHEN contrib>? THEN contrib-? ELSE 0 END WHERE uid=?')->execute([$cg, $cg, $uid]);
        }
        guild_wh_log((int) $g['id'], $uid, (string) $u['username'], '取金', fmt_money($n));
        flash_set('取出' . fmt_money($n) . '，个人贡献-' . $cg . '金。');
    }
    header('Location: guild.php?tab=wh');
    exit;
}
if ($a === 'disband') {
    guild_confirm('解散公会', '公会将彻底消失，仓库金退回给你，不可恢复！');
    $g = my_guild($uid);
    if (!$g || ($g['role'] ?? '') !== 'leader') {
        flash_set('只有会长能解散。');
    } else {
        $cnt = (int) (db()->query('SELECT COUNT(*) FROM guild_members WHERE gid=' . (int) $g['id'])->fetchColumn() ?: 0);
        if ($cnt > 1) {
            flash_set('还有其他成员，先转让会长或请他们离开。');
        } elseif (guild_warehouse_count((int) $g['id']) > 0) {
            flash_set('仓库还有物品，先取出来再解散。');
        } else {
            $back = (int) ($g['gold'] ?? 0);
            if ($back > 0) {
                $u['gold'] = (int) $u['gold'] + $back;
                user_save($u);
            }
            db()->prepare('DELETE FROM guild_members WHERE gid=?')->execute([(int) $g['id']]);
            db()->prepare('DELETE FROM guild_warehouse WHERE gid=?')->execute([(int) $g['id']]);
            db()->prepare('UPDATE guild_invites SET status="done" WHERE gid=? AND status="open"')->execute([(int) $g['id']]);
            db()->prepare('DELETE FROM guilds WHERE id=?')->execute([(int) $g['id']]);
            flash_set('公会已解散' . ($back > 0 ? '，仓库' . fmt_money($back) . '已退回给你' : '') . '。');
        }
    }
    header('Location: guild.php');
    exit;
}
if ($a === 'levelup') {
    guild_confirm('升级公会', '将消耗仓库金升级，不可退回。');
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
    guild_confirm('转让会长', '你将变成普通成员，对方成为会长！');
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
    guild_confirm('踢出成员', '对方将离开公会，贡献清零。');
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
                    guild_wh_log((int) $g['id'], $uid, (string) $u['username'], '存物', mat_name($mid) . 'x' . $n);
                    flash_set('存入【' . mat_name($mid) . '】x' . $n . '。');
                }
            } elseif ($kind === 'equip') {
                $eid = (int) ($_GET['eid'] ?? 0);
                $st = db()->prepare('SELECT * FROM equips WHERE id=? AND uid=? AND pos=""');
                $st->execute([$eid, $uid]);
                $eq = $st->fetch();
                if (!$eq) {
                    flash_set('这件装备不在背包（穿着的先脱下）。');
                } elseif (!is_tradable_equip($eq)) {
                    flash_set('仓库只收史诗及以上装备。');
                } else {
                    db()->prepare('UPDATE equips SET uid=0, pos="gwarehouse" WHERE id=?')->execute([$eid]);
                    db()->prepare('INSERT INTO guild_warehouse (gid, kind, equip_id, qty, donor_uid, donor_name, created_at) VALUES (?, "equip", ?, 1, ?, ?, ?)')->execute([(int) $g['id'], $eid, $uid, (string) $u['username'], time()]);
                    guild_wh_log((int) $g['id'], $uid, (string) $u['username'], '存物', equip_shortname((string) $eq['name']));
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
                guild_wh_log((int) $g['id'], $uid, (string) $u['username'], '取物', mat_name((string) $w['mat_id']) . 'x' . (int) $w['qty']);
                flash_set('取出【' . mat_name((string) $w['mat_id']) . '】x' . (int) $w['qty'] . '。');
            } else {
                $eqn = (string) (db()->query('SELECT name FROM equips WHERE id=' . (int) $w['equip_id'])->fetchColumn() ?: '装备');
                db()->prepare('UPDATE equips SET uid=?, pos="" WHERE id=? AND pos="gwarehouse"')->execute([$uid, (int) $w['equip_id']]);
                db()->prepare('DELETE FROM guild_warehouse WHERE id=?')->execute([$wid]);
                guild_wh_log((int) $g['id'], $uid, (string) $u['username'], '取物', equip_shortname($eqn));
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
echo '【公会排行·本大区】<br>';
foreach (guild_rank((string) ($u['zone'] ?? 'z1')) as $r) {
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
    echo '身份：' . h(guild_role_name((string) ($g['role'] ?? 'member'))) . '　贡献：' . (int) $g['contrib'] . '金　仓库金：' . fmt_money((int) ($g['gold'] ?? 0)) . '<br>';
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
        echo '仓库金：' . fmt_money((int) ($g['gold'] ?? 0)) . '<br>';
        echo '<form method="post" action="guild.php?a=wdepgold">';
        echo '存金<input name="gold" size="8" value="10000"><input type="submit" value="存入（满1金+1贡献）">';
        echo '</form>';
        if (guild_can_take_warehouse($g)) {
            echo '<form method="post" action="guild.php?a=wwithgold">';
            echo '取金<input name="gold" size="8" value="10000"><input type="submit" value="取出（扣自己贡献）">';
            echo '</form>';
        }
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
        echo '<div class="hr">--------</div>【出入库记录】<br>';
        $lst = db()->prepare('SELECT * FROM guild_warehouse_log WHERE gid=? ORDER BY id DESC LIMIT 20');
        $lst->execute([(int) $g['id']]);
        $hasLog = false;
        while ($lr = $lst->fetch()) {
            $hasLog = true;
            echo date('m-d H:i', (int) $lr['created_at']) . ' ' . h($lr['username']) . h($lr['action']) . '【' . h($lr['detail']) . '】<br>';
        }
        if (!$hasLog) {
            echo '<span class="muted">暂无记录。</span><br>';
        }
    } else {
        $st = db()->prepare('SELECT u.* FROM guild_members m JOIN users u ON u.id=m.uid WHERE m.gid=? LIMIT 60');
        $st->execute([(int) $g['id']]);
        $members = [];
        while ($m = $st->fetch()) {
            $m['_power'] = power_score($m);
            $members[] = $m;
        }
        usort($members, fn($a, $b) => (int) $b['_power'] <=> (int) $a['_power']);
        echo '成员（按战力排行）：<br>';
        foreach ($members as $m) {
            echo '·Lv' . (int) $m['lv'] . ' ' . h($m['username']) . ' | 战力' . (int) $m['_power'] . ' | 贡献' . (int) $m['contrib'] . '金 | ' . h(guild_role_name((string) $m['role']));
            if (($g['role'] ?? '') === 'leader' && (int) $m['id'] !== $uid) {
                echo '<br>　<a href="guild.php?a=role&uid=' . $m['id'] . '&r=vice">设副会长</a> <a href="guild.php?a=role&uid=' . $m['id'] . '&r=officer">设管理</a> <a href="guild.php?a=role&uid=' . $m['id'] . '&r=member">撤职</a> <a href="guild.php?a=transfer&uid=' . $m['id'] . '">转让会长</a>';
            }
            if (in_array($g['role'] ?? '', ['leader', 'vice', 'officer'], true) && (int) $m['id'] !== $uid) {
                echo ' <a href="guild.php?a=kick&uid=' . $m['id'] . '">踢</a>';
            }
            echo '<br>';
        }
        if (($g['role'] ?? '') === 'leader') {
            $needNext = (int) $g['level'] >= 10 ? '' : '（下级要仓库' . fmt_money(guild_level_need((int) $g['level'])) . '）';
            echo '<a href="guild.php?a=levelup">升级公会（消耗仓库金）</a>' . $needNext . '<br>';
            echo '<form method="post" action="guild.php?a=notice">';
            echo '<input name="notice" maxlength="60" placeholder="改公告"><input type="submit" value="发布">';
            echo '</form>';
        }
    }
    if (($g['role'] ?? '') === 'leader') {
        $cnt = (int) (db()->query('SELECT COUNT(*) FROM guild_members WHERE gid=' . (int) $g['id'])->fetchColumn() ?: 0);
        if ($cnt <= 1) {
            echo '<a href="guild.php?a=disband">解散公会（仅你一人）</a><br>';
        }
    } else {
        echo '<a href="guild.php?a=leave">退出公会</a><br>';
    }
}
nav_line();
wap_end(false);
