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
    $job = (string) ($_POST['job'] ?? 'warrior');
    if (!isset(jobs()[$job])) {
        $job = 'warrior';
    }
    if ($name === '' || preg_match('/^[\x{4e00}-\x{9fff}A-Za-z0-9_]{2,12}$/u', $name) !== 1) {
        $err = '名号用2-12字，汉字、字母、数字或下划线。';
    } elseif (strlen($pass) < 4) {
        $err = '口令至少4位。';
    } elseif (user_by_name($name)) {
        $err = '这名号已经被别人拿走了。';
    } else {
        $_SESSION['uid'] = user_create($name, $pass, $job);
        header('Location: home.php');
        exit;
    }
}

wap_start('登记');
echo '<div class="muted">写下你的名号，再选你以前是干啥的。布隆问：小子，以前是干啥的？</div>';
if ($err !== '') {
    echo '<div class="warn">' . h($err) . '</div>';
}
echo '<form method="post">';
echo '名号<br><input name="u" maxlength="12" required><br>';
echo '口令<br><input name="p" type="password" maxlength="32" required><br><br>';
echo '出身职业<br><select name="job">';
foreach (jobs() as $id => $j) {
    echo '<option value="' . h($id) . '">' . h($j['name']) . '：' . h($j['desc']) . '</option>';
}
echo '</select><br><br>';
echo '<input type="submit" value="登记">';
echo '</form>';
echo '<div class="hr">--------</div>';
echo '<a href="index.php">已有名号</a>';
wap_end(false);
