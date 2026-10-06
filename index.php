<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

if (!empty($_SESSION['uid'])) {
    header('Location: home.php');
    exit;
}

$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim((string) ($_POST['u'] ?? ''));
    $pass = (string) ($_POST['p'] ?? '');
    $u = user_by_name($name);
    if ($u && password_verify($pass, $u['pass'])) {
        $_SESSION['uid'] = (int) $u['id'];
        header('Location: home.php');
        exit;
    }
    $err = '名号或口令不对。';
}

wap_start('罪渊');
echo '<div class="muted">雾里没有路牌。你只能报上名号，才能被放进去。</div>';
if ($err !== '') {
    echo '<div class="warn">' . h($err) . '</div>';
}
echo '<form method="post">';
echo '名号<br><input name="u" maxlength="12" required><br>';
echo '口令<br><input name="p" type="password" maxlength="32" required><br><br>';
echo '<input type="submit" value="进入">';
echo '</form>';
echo '<div class="hr">--------</div>';
echo '<a href="register.php">登记新名号</a>';
wap_end(false);
