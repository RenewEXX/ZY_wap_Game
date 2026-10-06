<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

$u = require_login();
$buy = (string) ($_GET['buy'] ?? '');
$all = items();

if ($u['loc'] !== 'shop') {
    $u['loc'] = 'shop';
    user_save($u);
}

if ($buy !== '' && isset($all[$buy])) {
    $it = $all[$buy];
    if ((int) $u['gold'] < (int) $it['gold']) {
        flash_set('金不够。');
    } elseif ($it['slot'] === 'potion') {
        $u['gold'] = (int) $u['gold'] - (int) $it['gold'];
        $u['potion'] = (int) $u['potion'] + 1;
        user_save($u);
        flash_set('买下回血药。');
    } else {
        $u['gold'] = (int) $u['gold'] - (int) $it['gold'];
        $slot = $it['slot'];
        $u[$slot] = $buy;
        user_save($u);
        flash_set('换上了' . $it['name'] . '。旧的被掌柜收走当废铁。');
    }
    header('Location: shop.php');
    exit;
}

wap_start('黑市');
echo '<div class="muted">' . h(loc('shop')['desc']) . '</div>';
echo '<span class="gold">金 ' . (int) $u['gold'] . '</span>';
$flash = flash_get();
if ($flash !== '') {
    echo '<div class="warn">' . h($flash) . '</div>';
}
echo '<div class="hr">--------</div>';
foreach ($all as $id => $it) {
    $extra = '';
    if ((int) $it['atk'] > 0) {
        $extra = ' 攻+' . $it['atk'];
    }
    if ((int) $it['def'] > 0) {
        $extra = ' 防+' . $it['def'];
    }
    if ($id === 'potion') {
        $extra = ' 生命+20';
    }
    echo '· <a href="shop.php?buy=' . h($id) . '">' . h($it['name']) . '</a>';
    echo ' <span class="gold">' . (int) $it['gold'] . '金</span>' . h($extra) . '<br>';
}
echo '<div class="hr">--------</div>';
echo '<a href="map.php?to=gate">离开黑市</a>';
nav_line();
wap_end(false);
