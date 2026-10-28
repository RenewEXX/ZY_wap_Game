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
echo '当前：' . avatar_html($u, 48) . '<br>';
echo '<div class="muted">默认头像随「职业 → 一觉 → 二觉」自动升级；也可在已解锁头像里自选。二次元头像在商城付费解锁。</div>';
echo '<div class="hr">--------</div>';

$cur = avatar_of($u);
$unlocked = avatar_unlocked_ids($u);
$job = job_id_of($u);
$cat = avatar_catalog();

$render_row = function (string $id, array $a) use ($cur, $unlocked) {
    echo avatar_render($id, 36) . ' ' . h((string) $a['name']);
    if (in_array($id, $unlocked, true)) {
        if ($id === $cur) {
            echo ' <span style="color:#6f6">（使用中）</span>';
        } else {
            echo ' <a href="avatar.php?a=set&id=' . h($id) . '">设为头像</a>';
        }
    } else {
        echo ' <span class="muted">·未解锁</span>';
    }
    echo '<br>';
};

// ===== 职业 / 一觉 / 二觉 =====
echo '<b>职业 / 一转 / 二转头像</b><br>';
foreach (['job:' . $job, 'rb1:' . $job] as $id) {
    if (isset($cat[$id])) {
        $render_row($id, $cat[$id]);
    }
}
foreach ($cat as $id => $a) {
    if (str_starts_with($id, 'rb2:') && ($a['job'] ?? '') === $job) {
        $render_row($id, $a);
    }
}

// ===== 二次元头像（商城）=====
echo '<div class="hr">--------</div>';
echo '<b>二次元头像（商城付费解锁 · 跨职业可用）</b><br>';
$anime = function_exists('avatar_anime_goods') ? avatar_anime_goods() : [];
if ($anime !== []) {
    foreach ($anime as $gk => $gv) {
        $id = 'anime:' . $gv['job'];
        if (!isset($cat[$id])) {
            continue;
        }
        $owned = function_exists('avatar_anime_owned') ? avatar_anime_owned((int) $u['id'], (string) $gv['job']) : false;
        if ($owned) {
            $render_row($id, $cat[$id]);
        } else {
            echo '<span style="opacity:.4;filter:grayscale(1);display:inline-block;vertical-align:middle">' . avatar_render($id, 30) . '</span>';
            echo ' <span class="muted">' . h((string) $cat[$id]['name']) . '</span> · <a href="mall.php?m=' . h($gk) . '">去商城购买</a><br>';
        }
    }
}

echo '<div class="hr">--------</div>';
echo '<a href="avatar.php?a=set&id=">恢复默认（跟随职业/一觉/二觉）</a><br>';
echo '<a href="status.php">回状态</a> · <a href="home.php">回行动</a>';
nav_line();
wap_end(false);
