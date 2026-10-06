<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

$u = require_login();
$a = (string) ($_GET['a'] ?? '');

if ($a === 'drink') {
    if ((int) $u['potion'] <= 0) {
        flash_set('药空了。');
    } elseif ((int) $u['hp'] >= (int) $u['maxhp']) {
        flash_set('你没有伤。');
    } else {
        $u['potion'] = (int) $u['potion'] - 1;
        $heal = min(20, (int) $u['maxhp'] - (int) $u['hp']);
        $u['hp'] = (int) $u['hp'] + $heal;
        user_save($u);
        flash_set('回复' . $heal . '点生命。');
    }
    header('Location: bag.php');
    exit;
}

wap_start('行囊');
echo '武器：' . h(item_name((string) $u['weapon'])) . '<br>';
echo '护甲：' . h(item_name((string) $u['armor'])) . '<br>';
echo '回血药：' . (int) $u['potion'] . '<br>';
echo '<span class="gold">金 ' . (int) $u['gold'] . '</span>';
$flash = flash_get();
if ($flash !== '') {
    echo '<div class="warn">' . h($flash) . '</div>';
}
echo '<div class="hr">--------</div>';
if ((int) $u['potion'] > 0) {
    echo '<a href="bag.php?a=drink">喝一瓶药</a><br>';
}
echo '<a href="shop.php">去黑市</a>';
nav_line();
wap_end(false);
