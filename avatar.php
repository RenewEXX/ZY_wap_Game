<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

$u = require_login();

if ((string) ($_GET['a'] ?? '') === 'set') {
    flash_set(avatar_set((int) $u['id'], (string) ($_GET['id'] ?? '')));
    header('Location: avatar.php');
    exit;
}

$u = user_by_id((int) $u['id']);
wap_start('头像');

$flash = flash_get();
if ($flash !== '') {
    echo '<div class="warn">' . h($flash) . '</div>';
}

echo '【选择头像】<br>';
echo '当前：' . avatar_html($u, 44) . '<br>';
echo '<div class="muted">默认头像会随你的职业/转职自动变化；也可以在本职业已解锁的头像里自选。</div>';
echo '<div class="hr">--------</div>';

$cur = avatar_of($u);
$unlocked = avatar_unlocked_ids($u);
$rb2 = (string) (cflags((int) $u['id'])['rb2_job'] ?? '');

echo '<b>本职业头像</b><br>';
$lockedList = [];
foreach (avatar_catalog() as $id => $a) {
    if (($a['job'] ?? '') !== job_id_of($u)) {
        continue;
    }
    if (in_array($id, $unlocked, true)) {
        echo avatar_render($id, 36) . ' ' . h($a['name']);
        if ($id === $cur) {
            echo ' <span style="color:#6f6">（使用中）</span>';
        } else {
            echo ' <a href="avatar.php?a=set&id=' . h($id) . '">设为头像</a>';
        }
        echo '<br>';
    } else {
        $lockedList[$id] = $a;
    }
}

if ($lockedList !== []) {
    echo '<div class="hr">--------</div>';
    echo '<b>未解锁（选择对应2觉转职后获得）</b><br>';
    foreach ($lockedList as $lid => $a) {
        echo '<span style="opacity:.35;filter:grayscale(1);display:inline-block;vertical-align:middle">' . avatar_render($lid, 30) . '</span>';
        echo ' <span class="muted">' . h($a['name']) . '</span><br>';
    }
}

echo '<div class="hr">--------</div>';
echo '<a href="avatar.php?a=set&id=">恢复默认（跟随职业/转职）</a><br>';
echo '<a href="status.php">回状态</a> · <a href="home.php">回行动</a>';
nav_line();
wap_end(false);
