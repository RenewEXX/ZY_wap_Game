<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

$u = require_login();
$uid = (int) $u['id'];
if ((int) ($u['quest'] ?? 0) < 38) {
    flash_set('先通关第三章，农场才会开放。');
    header('Location: home.php');
    exit;
}
$a = (string) ($_GET['a'] ?? '');
if ($a === 'plant') {
    flash_set(farm_plant($uid, (int) ($_GET['p'] ?? 0), (string) ($_GET['c'] ?? '')));
    header('Location: farm.php');
    exit;
}
if ($a === 'harvest') {
    flash_set(farm_harvest($uid, (int) ($_GET['p'] ?? 0)));
    header('Location: farm.php');
    exit;
}

wap_start('王都农场');
$flash = flash_get();
if ($flash !== '') {
    echo '<div class="warn">' . h($flash) . '</div>';
}
$mats = mats_of($uid);
echo '农场币：' . (int) ($mats['farm_coin'] ?? 0) . '<br>';
echo '<div class="hr">--------</div>';
$plots = farm_plots($uid);
for ($i = 1; $i <= 4; $i++) {
    echo $i . '号地：';
    if (empty($plots[$i])) {
        echo '空。<br>种：';
        foreach (farm_crops() as $cid => $c) {
            echo '<a href="farm.php?a=plant&p=' . $i . '&c=' . $cid . '">' . h($c['name']) . '(' . h(fmt_money($c['cost'])) . '/' . h(dummy_fmt($c['grow'])) . '/+' . $c['coin'] . '币)</a> ';
        }
        echo '<br>';
    } else {
        $c = farm_crops()[$plots[$i]['crop']];
        $left = $c['grow'] - (time() - (int) $plots[$i]['at']);
        if ($left <= 0) {
            echo h($c['name']) . '熟了！<a href="farm.php?a=harvest&p=' . $i . '">收获(+' . $c['coin'] . '币)</a><br>';
        } else {
            echo h($c['name']) . '生长中，剩' . h(dummy_fmt($left)) . '<br>';
        }
    }
}
echo '<div class="hr">--------</div>';
echo 'NPC【菜商·豆豆】：<a href="npc.php?who=grocer">对话（农场币换粮食/升级卡/洗点药，每周限量）</a><br>';
nav_line();
wap_end(false);
