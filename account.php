<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

$name = (string) ($_SESSION['account'] ?? '');
if ($name === '') {
    header('Location: index.php');
    exit;
}
$a = (string) ($_GET['a'] ?? '');
if ($a === 'enter') {
    $z = (string) ($_GET['zone'] ?? '');
    foreach (account_rows($name) as $row) {
        if ((string) ($row['zone'] ?? 'z1') === $z) {
            $_SESSION['uid'] = (int) $row['id'];
            header('Location: home.php');
            exit;
        }
    }
    header('Location: create.php?zone=' . urlencode($z));
    exit;
}

$rows = account_rows($name);
$byZone = [];
foreach ($rows as $row) {
    $byZone[(string) ($row['zone'] ?? 'z1')] = $row;
}
$uidNum = $rows !== [] ? (int) $rows[0]['id'] : 0;

if ($a === 'deldo' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $z = (string) ($_POST['zone'] ?? '');
    $target = null;
    foreach (account_rows($name) as $row) {
        if ((string) ($row['zone'] ?? 'z1') === $z) {
            $target = (int) $row['id'];
            break;
        }
    }
    if ($target === null) {
        flash_set('这个大区没有角色。');
    } else {
        $open = (int) db()->query('SELECT COUNT(*) FROM auctions WHERE seller_uid=' . $target . " AND status='open'")->fetchColumn();
        if ($open > 0) {
            flash_set('有进行中的拍卖，先去下架再删角。');
            header('Location: account.php');
            exit;
        }
        foreach (['equips' => 'uid', 'mats' => 'uid', 'mails' => 'uid', 'skills' => 'uid', 'bids' => 'bidder', 'auction_logs' => 'uid'] as $t => $col) {
            db()->prepare('DELETE FROM ' . $t . ' WHERE ' . $col . '=?')->execute([$target]);
        }
        db()->prepare('DELETE FROM users WHERE id=?')->execute([$target]);
        if ((int) ($_SESSION['uid'] ?? 0) === $target) {
            unset($_SESSION['uid']);
        }
        flash_set('角色已删除，装备邮件材料一并清空，不可恢复。');
    }
    header('Location: account.php');
    exit;
}

wap_start('账号管理');
$accFlash = flash_get();
if ($accFlash !== '') {
    echo '<div class="warn">' . h($accFlash) . '</div>';
}
echo '<div class="t">罪渊·账号管理</div>';
echo '保存本页书签以后自动登录<br>';
echo '用户名：' . h($name) . '<br>';
echo '数字uid：' . $uidNum . '<br>';
echo '<a href="account.php?a=enter&zone=z1" style="background:#a00;color:#fff;padding:2px 8px;">[进 入 游 戏]</a><br>';
echo '<div class="hr">--------</div>';
foreach (zones() as $zid => $z) {
    echo '罪渊之门<a href="account.php?a=enter&zone=' . h($zid) . '">[' . h($z['name']) . ']</a>(' . h($z['tag']) . ')';
    if (isset($byZone[$zid])) {
        $c = $byZone[$zid];
        echo ' ' . (int) $c['lv'] . '级' . h(job_of($c)['name']);
    } else {
        echo ' 未创建';
    }
    echo '<br>';
}
echo '<div class="hr">--------</div>';
if ($a === 'del') {
    $z = (string) ($_GET['zone'] ?? '');
    $target = null;
    foreach ($rows as $row) {
        if ((string) ($row['zone'] ?? 'z1') === $z) {
            $target = $row;
            break;
        }
    }
    if ($target === null) {
        echo '<div class="warn">这个大区没有角色。</div>';
    } else {
        $openN = (int) db()->query('SELECT COUNT(*) FROM auctions WHERE seller_uid=' . (int) $target['id'] . " AND status='open'")->fetchColumn();
        if ($openN > 0) {
            echo '<div style="color:#f00">有' . $openN . '件拍卖进行中，先去<a href="auction.php?a=mine">拍卖行下架</a>再回来删角。</div>';
        }
        echo '<div class="warn">确认删除【' . h(zones()[$z]['name']) . '】的 ' . (int) $target['lv'] . '级' . h(job_of($target)['name']) . ' 吗？装备、材料、邮件、技能全部清空，不可恢复！</div>';
        echo '<form method="post" action="account.php?a=deldo">';
        echo '<input type="hidden" name="zone" value="' . h($z) . '">';
        echo '<input type="submit" value="确认删除">';
        echo '</form>';
    }
    echo '<a href="account.php">我再想想</a><br>';
    wap_end(false);
    exit;
}
echo '牢记密码，谨防被盗<br>';
echo '<a href="password.php">密码管理</a><br>';
echo '<a href="logout.php">重新登录</a><br>';
foreach (zones() as $zid => $z) {
    if (isset($byZone[$zid])) {
        echo '<a href="account.php?a=del&zone=' . h($zid) . '">删除[' . h($z['name']) . ']角色</a><br>';
    }
}
echo '<a href="logout.php">退出</a><br>';
echo date('m-d H:i') . '<br>';
echo '<a href="account.php">刷新</a><br>';
wap_end(false);
