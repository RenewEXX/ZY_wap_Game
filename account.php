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

wap_start('账号管理');
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
echo '牢记密码，谨防被盗<br>';
echo '<a href="password.php">密码管理</a><br>';
echo '<a href="logout.php">重新登录</a><br>';
echo '<a href="logout.php">退出</a><br>';
echo date('m-d H:i') . '<br>';
echo '<a href="account.php">刷新</a><br>';
wap_end(false);
