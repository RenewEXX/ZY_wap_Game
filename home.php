<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

$u = require_login();
$flash = flash_get();
$here = loc((string) $u['loc']);

wap_start('营地');
echo '你是 <span class="gold">' . h($u['username']) . '</span><br>';
echo '等级 ' . (int) $u['lv'] . '　';
echo '<span class="hp">生命 ' . (int) $u['hp'] . '/' . (int) $u['maxhp'] . '</span><br>';
echo '<span class="gold">金 ' . (int) $u['gold'] . '</span>　经验 ' . (int) $u['exp'] . '/' . exp_need((int) $u['lv']) . '<br>';
echo '所在：' . h($here['name']);
if ($flash !== '') {
    echo '<div class="warn">' . h($flash) . '</div>';
}
echo '<div class="hr">--------</div>';
echo '<div class="muted">' . h($here['desc']) . '</div>';
echo '<div class="hr">--------</div>';
echo '<a href="map.php">四处走走</a><br>';
if ($u['potion'] > 0) {
    echo '<a href="bag.php?a=drink">喝药 (' . (int) $u['potion'] . ')</a><br>';
}
echo '<a href="status.php">查看自身</a><br>';
echo '<a href="bag.php">打开行囊</a>';
nav_line();
wap_end(false);
