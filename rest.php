<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

$u = require_login();
$a = (string) ($_GET['a'] ?? '');

if ($u['loc'] !== 'camp') {
    $u['loc'] = 'camp';
    user_save($u);
}

if ($a === 'sleep') {
    $u['hp'] = (int) $u['maxhp'];
    $u['mp'] = (int) ($u['maxmp'] ?? 0);
    user_save($u);
    flash_set('火堆旁一觉。伤好了，梦不好。');
    header('Location: rest.php');
    exit;
}

wap_start('残火营地');
echo '<div class="muted">' . h(loc('camp')['desc']) . '</div>';
echo '<span class="hp">生命 ' . (int) $u['hp'] . '/' . (int) $u['maxhp'] . '</span> <span style="color:#6cf">魔力 ' . (int) ($u['mp'] ?? 0) . '/' . (int) ($u['maxmp'] ?? 0) . '</span>';
$flash = flash_get();
if ($flash !== '') {
    echo '<div class="warn">' . h($flash) . '</div>';
}
echo '<div class="hr">--------</div>';
echo '<a href="rest.php?a=sleep">靠着火堆睡</a><br>';
echo '<a href="map.php?to=gate">离开营地</a>';
nav_line();
wap_end(false);
