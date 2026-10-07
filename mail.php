<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

$u = require_login();
$uid = (int) $u['id'];
$a = (string) ($_GET['a'] ?? '');

db()->exec('DELETE FROM mails WHERE expires_at>0 AND expires_at<' . time());

if ($a === 'claim') {
    flash_set(claim_mail($uid, (int) ($_GET['id'] ?? 0)));
    header('Location: mail.php?a=view&id=' . (int) ($_GET['id'] ?? 0));
    exit;
}
if ($a === 'claimall') {
    flash_set(claim_all_mail($uid));
    header('Location: mail.php');
    exit;
}
if ($a === 'delread') {
    flash_set('删除了' . del_read_mail($uid) . '封已读邮件。');
    header('Location: mail.php');
    exit;
}
if ($a === 'del') {
    $st = db()->prepare('SELECT * FROM mails WHERE id=? AND uid=?');
    $st->execute([(int) ($_GET['id'] ?? 0), $uid]);
    $m = $st->fetch();
    if (!$m) {
        flash_set('没有这封邮件。');
    } elseif ((int) $m['pinned'] === 1) {
        flash_set('这封邮件不可删除。');
    } else {
        $att = json_decode((string) $m['attachments'], true);
        if (is_array($att) && $att !== [] && (int) $m['claimed'] === 0) {
            flash_set('附件还没领，不能删。');
            header('Location: mail.php?a=view&id=' . (int) $m['id']);
            exit;
        }
        db()->prepare('DELETE FROM mails WHERE id=?')->execute([(int) $m['id']]);
        flash_set('邮件已删除。');
    }
    header('Location: mail.php');
    exit;
}
if ($a === 'send' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $toName = trim((string) ($_POST['to'] ?? ''));
    $title = trim((string) ($_POST['title'] ?? ''));
    $body = trim((string) ($_POST['body'] ?? ''));
    $gold = max(0, (int) ($_POST['gold'] ?? 0));
    if ((int) $u['lv'] < 10) {
        flash_set('10级才能写信。');
    } elseif ($toName === '' || $title === '') {
        flash_set('收件人和标题不能为空。');
    } elseif ((int) $u['gold'] < $gold + 10) {
        flash_set('钱不够（邮费10铜+货款）。');
    } else {
        $wantsAtt = $gold > 0;
        if (!$wantsAtt) {
            foreach ($_POST as $k => $v) {
                if (str_starts_with($k, 'mat_') && (int) $v > 0) {
                    $wantsAtt = true;
                    break;
                }
            }
        }
        $target = user_by_name($toName);
        if (!$target) {
            flash_set('没有这个玩家。');
        } elseif ((int) $target['id'] === $uid) {
            flash_set('不能给自己写信。');
        } elseif ($wantsAtt) {
            flash_set('玩家之间只能写信，不能寄钱寄道具（防骗防刷）。');
        } else {
            $att = [];
            $cnt = (int) db()->query('SELECT COUNT(*) FROM mails WHERE uid=' . (int) $target['id'])->fetchColumn();
            if ($cnt >= 50) {
                flash_set('对方邮箱满了，发不出去。');
                header('Location: mail.php?a=write');
                exit;
            }
            $u['gold'] = (int) $u['gold'] - $gold - 10;
            foreach ($att as $x) {
                if ($x['t'] === 'mat') {
                    add_mat($uid, $x['id'], -$x['n']);
                }
            }
            user_save($u);
            send_mail((int) $target['id'], $u['username'], 'player', $title, $body, $att);
            flash_set('信寄出去了，邮费10铜。');
            header('Location: mail.php');
            exit;
        }
    }
    header('Location: mail.php?a=write');
    exit;
}

wap_start('邮件箱');
if ($a === 'write') {
    $re = (string) ($_GET['to'] ?? '');
    echo '【写信】10级可寄，邮费10铜，只能写信不能寄钱寄道具。<br>';
    echo '<form method="post" action="mail.php?a=send">';
    echo '收件人 <input name="to" value="' . h($re) . '" maxlength="16"><br>';
    echo '标题 <input name="title" maxlength="24"><br>';
    echo '内容<br><textarea name="body" rows="4" cols="22" maxlength="500"></textarea><br>';
    echo '<input type="submit" value="发送邮件">';
    echo '</form>';
    echo '<a href="mail.php">返回列表</a>';
    nav_line();
    wap_end(false);
    exit;
}
if ($a === 'view') {
    $st = db()->prepare('SELECT * FROM mails WHERE id=? AND uid=?');
    $st->execute([(int) ($_GET['id'] ?? 0), $uid]);
    $m = $st->fetch();
    if (!$m) {
        flash_set('没有这封邮件。');
        header('Location: mail.php');
        exit;
    }
    db()->prepare('UPDATE mails SET is_read=1 WHERE id=?')->execute([(int) $m['id']]);
    $flash = flash_get();
    if ($flash !== '') {
        echo '<div class="warn">' . h($flash) . '</div>';
    }
    echo '【邮件详情】<br>';
    echo '发件人：' . h($m['sender']) . '<br>';
    echo '标题：' . h($m['title']) . '<br>';
    echo '时间：' . date('Y-m-d H:i', (int) $m['created_at']) . '<br>';
    echo '<div class="hr">--------</div>';
    echo nl2br(h($m['body'])) . '<br>';
    echo '<div class="hr">--------</div>';
    $att = json_decode((string) $m['attachments'], true);
    if (is_array($att) && $att !== []) {
        echo '附件：' . h(mail_att_text($att)) . '<br>';
        echo '状态：' . ((int) $m['claimed'] === 1 ? '已领取' : '未领取') . '<br>';
        if ((int) $m['claimed'] === 0) {
            echo '<a href="mail.php?a=claim&id=' . $m['id'] . '">领取附件</a><br>';
        }
    } else {
        echo '附件：无<br>';
    }
    if ($m['type'] === 'player') {
        echo '<a href="mail.php?a=write&to=' . h($m['sender']) . '">回复邮件</a><br>';
    }
    if ((int) $m['pinned'] === 0) {
        echo '<a href="mail.php?a=del&id=' . $m['id'] . '">删除邮件</a><br>';
    }
    echo '<a href="mail.php">返回列表</a>';
    nav_line();
    wap_end(false);
    exit;
}

$flash = flash_get();
if ($flash !== '') {
    echo '<div class="warn">' . h($flash) . '</div>';
}
$unread = mail_unread($uid);
$total = (int) db()->query('SELECT COUNT(*) FROM mails WHERE uid=' . $uid)->fetchColumn();
echo '未读：' . $unread . '封 | 总容量：' . $total . '/50<br>';
echo '<div class="hr">--------</div>';
$st = db()->prepare('SELECT * FROM mails WHERE uid=? ORDER BY pinned DESC, is_read ASC, created_at DESC LIMIT 50');
$st->execute([$uid]);
$rows = $st->fetchAll();
if ($rows === []) {
    echo '<span class="muted">空邮箱，去冒险吧。</span><br>';
}
$tcolor = ['system' => '#fff', 'auction' => '#fc3', 'player' => '#6cf'];
$tname = ['system' => '系统', 'auction' => '拍卖行', 'player' => '玩家'];
foreach ($rows as $m) {
    $mark = (int) $m['is_read'] === 0 ? '●' : '○';
    $c = $tcolor[$m['type']] ?? '#fff';
    echo $mark . ' <span style="color:' . $c . '">【' . h($tname[$m['type']] ?? $m['type']) . '】' . h($m['title']) . '</span>';
    if ((int) $m['pinned'] === 1) {
        echo '[顶]';
    }
    $att = json_decode((string) $m['attachments'], true);
    if (is_array($att) && $att !== [] && (int) $m['claimed'] === 0) {
        echo '[件]';
    }
    echo ' <a href="mail.php?a=view&id=' . $m['id'] . '">查看</a><br>';
}
echo '<div class="hr">--------</div>';
echo '<a href="mail.php?a=claimall">一键领取附件</a> <a href="mail.php?a=delread">一键删除已读</a><br>';
echo '<a href="mail.php?a=write">写信</a>';
nav_line();
wap_end(false);
