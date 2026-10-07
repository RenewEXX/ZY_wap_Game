<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

$name = (string) ($_SESSION['account'] ?? '');
if ($name === '') {
    header('Location: index.php');
    exit;
}
$zone = (string) ($_GET['zone'] ?? $_POST['zone'] ?? 'z1');
$zones = zones();
if (!isset($zones[$zone])) {
    $zone = 'z1';
}
foreach (account_rows($name) as $row) {
    if ((string) ($row['zone'] ?? 'z1') === $zone) {
        $_SESSION['uid'] = (int) $row['id'];
        header('Location: home.php');
        exit;
    }
}
$hash = null;
foreach (account_rows($name) as $row) {
    $hash = (string) $row['pass'];
    break;
}
if ($hash === null) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $job = (string) ($_POST['job'] ?? 'warrior');
    if (!isset(jobs()[$job])) {
        $job = 'warrior';
    }
    $id = user_create($name, $hash, $job, $zone, true);
    send_mail($id, '系统', 'system', '欢迎来到白石镇', '欢迎来到白石镇，年轻的冒险者！这是为你准备的新手补给，请查收。', [['t' => 'mat', 'id' => 'enhance_t1', 'n' => 10], ['t' => 'potion', 'n' => 5], ['t' => 'gold', 'n' => 500]], 1);
    $_SESSION['uid'] = $id;
    header('Location: home.php');
    exit;
}

wap_start('创建角色');
echo '在大区【' . h($zones[$zone]['name']) . '】创建新角色，名号沿用：' . h($name) . '<br>';
echo '<form method="post">';
echo '<input type="hidden" name="zone" value="' . h($zone) . '">';
foreach (jobs() as $jid => $j) {
    echo '<input type="radio" name="job" value="' . h($jid) . '"' . ($jid === 'warrior' ? ' checked' : '') . '>' . h($j['name']) . '(HP' . $j['hp'] . ' ATK' . $j['atk'] . ')<br>';
}
echo '<input type="submit" value="创建并进入">';
echo '</form>';
echo '<a href="account.php">回账号管理</a>';
wap_end(false);
