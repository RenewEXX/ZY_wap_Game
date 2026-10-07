<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

$u = require_login();
$t = user_by_id((int) ($_GET['id'] ?? 0));
if (!$t) {
    flash_set('没有这个人。');
    header('Location: home.php');
    exit;
}
if ((string) $t['loc'] !== (string) $u['loc']) {
    flash_set('他不在附近了。');
    header('Location: home.php');
    exit;
}
if ((int) $t['id'] === (int) $u['id']) {
    header('Location: status.php');
    exit;
}

wap_start('查看玩家');
echo '【' . h($t['username']) . '】<br>';
echo '等级：' . (int) $t['lv'] . '<br>';
echo '职业：' . h(job_of($t)['name']) . '（' . h(job_of($t)['skill']) . '）<br>';
echo '生命：' . (int) $t['hp'] . '/' . (int) $t['maxhp'] . '<br>';
echo '魔力：' . (int) ($t['mp'] ?? 0) . '/' . (int) ($t['maxmp'] ?? 0) . '<br>';
echo '位置：' . h(loc((string) $t['loc'])['name']) . '<br>';
echo '<div class="hr">--------</div>';
echo '<a href="mail.php?a=write&to=' . h($t['username']) . '">写信</a><br>';
echo '<a href="home.php">回行动</a>';
nav_line();
wap_end(false);
