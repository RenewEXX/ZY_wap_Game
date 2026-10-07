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

if ($buy === 'pet_revive_potion') {
    if ((int) $u['gold'] < 5000) {
        flash_set('钱不够（50银）。');
    } else {
        $u['gold'] = (int) $u['gold'] - 5000;
        user_save($u);
        add_mat((int) $u['id'], 'pet_revive_potion', 1);
        flash_set('买下宠物复活药。');
    }
    header('Location: shop.php');
    exit;
}
if ($buy !== '' && isset($all[$buy])) {
    $it = $all[$buy];
    if ((int) $u['gold'] < (int) $it['gold']) {
        flash_set('钱不够。');
    } elseif ($it['slot'] === 'potion') {
        $u['gold'] = (int) $u['gold'] - (int) $it['gold'];
        $u['potion'] = (int) $u['potion'] + 1;
        user_save($u);
        flash_set('买下回血药。');
    } else {
        // 商店旧货直接换成新装备系统的正式装备（普通随机2词缀）
        $u['gold'] = (int) $u['gold'] - (int) $it['gold'];
        $nm = make_equip((int) $u['id'], $it['slot'] === 'armor' ? 'body' : 'weapon', $it['name'], 1);
        user_save($u);
        flash_set('买下【' . $nm . '】，去背包穿上。');
    }
    header('Location: shop.php');
    exit;
}

wap_start('黑市');
echo '<div class="muted">' . h(loc('shop')['desc']) . '</div>';
echo '<span class="gold">' . h(fmt_money((int) $u['gold'])) . '</span>';
$flash = flash_get();
if ($flash !== '') {
    echo '<div class="warn">' . h($flash) . '</div>';
}
echo '<div class="hr">--------</div>';
echo '· <a href="shop.php?buy=pet_revive_potion">宠物复活药</a> <span class="gold">50银</span>（虚弱宠物满血复活）<br>';
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
    echo ' <span class="gold">' . h(fmt_money((int) $it['gold'])) . '</span>' . h($extra) . '<br>';
}
echo '<div class="hr">--------</div>';
echo '<a href="map.php?to=gate">离开黑市</a>';
nav_line();
wap_end(false);
