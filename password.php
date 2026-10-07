<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

$name = (string) ($_SESSION['account'] ?? '');
if ($name === '') {
    header('Location: index.php');
    exit;
}
$err = '';
$ok = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old = (string) ($_POST['old'] ?? '');
    $new = (string) ($_POST['new'] ?? '');
    $rows = account_rows($name);
    $passOk = false;
    foreach ($rows as $row) {
        if (password_verify($old, $row['pass'])) {
            $passOk = true;
            break;
        }
    }
    if (!$passOk) {
        $err = '旧密码不对。';
    } elseif (strlen($new) < 4) {
        $err = '新密码至少4位。';
    } else {
        $h = password_hash($new, PASSWORD_DEFAULT);
        $st = db()->prepare('UPDATE users SET pass=? WHERE username=?');
        $st->execute([$h, $name]);
        $ok = '全大区密码已更新。';
    }
}

wap_start('密码管理');
if ($err !== '') {
    echo '<div class="warn">' . h($err) . '</div>';
}
if ($ok !== '') {
    echo '<div class="warn">' . h($ok) . '</div>';
}
echo '账号：' . h($name) . '（改一次，全大区生效）<br>';
echo '<form method="post">';
echo '旧密码<br><input name="old" type="password" maxlength="32" required><br>';
echo '新密码<br><input name="new" type="password" maxlength="32" required><br><br>';
echo '<input type="submit" value="修改密码">';
echo '</form>';
echo '<a href="account.php">回账号管理</a>';
wap_end(false);
