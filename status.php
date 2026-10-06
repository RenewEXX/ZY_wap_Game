<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

$u = require_login();
$here = loc((string) $u['loc']);

wap_start('状态');
echo h($u['username']) . '　' . (int) $u['lv'] . '级<br>';
echo '<span class="hp">生命 ' . (int) $u['hp'] . '/' . (int) $u['maxhp'] . '</span><br>';
echo '攻击 ' . player_atk($u) . '　防御 ' . player_def($u) . '<br>';
echo '<span class="gold">金 ' . (int) $u['gold'] . '</span><br>';
echo '经验 ' . (int) $u['exp'] . '/' . exp_need((int) $u['lv']) . '<br>';
echo '武器：' . h(item_name((string) $u['weapon'])) . '<br>';
echo '护甲：' . h(item_name((string) $u['armor'])) . '<br>';
echo '回血药：' . (int) $u['potion'] . '<br>';
echo '所在：' . h($here['name']);
nav_line();
wap_end(false);
