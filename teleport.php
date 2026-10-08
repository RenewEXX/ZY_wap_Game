<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

$u = require_login();
$all = locations();
$cur = (string) $u['loc'];

function teleport_cost(string $from, string $to): ?int
{
    if ($from === $to) {
        return 0;
    }
    $path = find_route($from, $to);
    if ($path === []) {
        return null;
    }
    return (count($path) - 1) * 15;
}

$go = (string) ($_GET['go'] ?? '');
$backRegion = (string) ($_GET['region'] ?? '');
$backSuffix = $backRegion !== '' ? '?region=' . urlencode($backRegion) : '';
if ($go !== '' && isset($all[$go])) {
    $cost = teleport_cost($cur, $go);
    if (in_array($go, dsw_maps(), true) || in_array($go, abx_maps(), true) || in_array($go, mx_maps(), true)) {
        flash_set('副本只能找NPC进，不能传送。');
        header('Location: teleport.php' . $backSuffix);
        exit;
    }
    if ($go === 'warfield') {
        $werr = warfield_can_enter($u);
        if ($werr !== '') {
            flash_set($werr);
            header('Location: teleport.php');
            exit;
        }
    }
    $needLv = teleport_min_lv($go);
    $maxLv = teleport_max_lv($go);
    if ((int) $u['lv'] < $needLv) {
        flash_set('【' . $all[$go]['name'] . '】太危险，需要' . $needLv . '级，你现在' . (int) $u['lv'] . '级。');
        header('Location: teleport.php');
        exit;
    }
    if ((int) $u['lv'] > $maxLv) {
        flash_set('【' . $all[$go]['name'] . '】只接待' . $maxLv . '级以下，你超了。');
        header('Location: teleport.php');
        exit;
    }
    if ($cost === null) {
        flash_set('走不过去，传不了。');
    } elseif ($cost === 0) {
        flash_set('已经在这里了。');
    } elseif ((int) $u['gold'] < $cost) {
        flash_set('传送要' . fmt_money($cost) . '，钱不够。');
    } else {
        $u['gold'] = (int) $u['gold'] - $cost;
        $u['loc'] = $go;
        user_save($u);
        flash_set('传送到' . $all[$go]['name'] . '，花费' . fmt_money($cost) . '。');
        if ($go === 'shop') {
            header('Location: shop.php');
            exit;
        }
        if ($go === 'camp') {
            header('Location: rest.php');
            exit;
        }
        header('Location: home.php');
        exit;
    }
    header('Location: teleport.php' . $backSuffix);
    exit;
}

wap_start('传送');
echo '你在哪：' . h($all[$cur]['name'] ?? $cur) . '<br>';
echo '<span class="gold">' . h(fmt_money((int) $u['gold'])) . '</span><br>';
echo '<span class="muted">按步数收费，每步15铜。任务【立即前往】免费，走路免费。</span>';
echo '<div class="hr">--------</div>';
$flash = flash_get();
if ($flash !== '') {
    echo '<div class="warn">' . h($flash) . '</div>';
}
$openRegion = $backRegion;
foreach (map_regions() as $region => $ids) {
    $cnt = 0;
    foreach ($ids as $id) {
        if (isset($all[$id]) && !in_array($id, dsw_maps(), true) && !in_array($id, abx_maps(), true) && !in_array($id, mx_maps(), true)) {
            $cnt++;
        }
    }
    if ($cnt <= 0) {
        continue;
    }
    if ($openRegion !== $region) {
        echo '【<a href="teleport.php?region=' . h($region) . '">' . h($region) . '×' . $cnt . '</a>】<br>';
        continue;
    }
    echo '【<a href="teleport.php">' . h($region) . '×' . $cnt . '</a>】(收起)<br>';
    foreach ($ids as $id) {
        if (!isset($all[$id])) {
            continue;
        }
        if ($id === $cur) {
            echo '·' . h($all[$id]['name']) . '(你在)<br>';
            continue;
        }
        $cost = teleport_cost($cur, $id);
        if ($cost === null) {
            echo '·' . h($all[$id]['name']) . '(到不了)<br>';
            continue;
        }
        if (in_array($id, dsw_maps(), true) || in_array($id, abx_maps(), true) || in_array($id, mx_maps(), true)) {
            continue;
        }
        $lvTag = teleport_min_lv($id) > 1 ? '需' . teleport_min_lv($id) . '级' : '';
        echo '·<a href="teleport.php?region=' . h($region) . '&go=' . h($id) . '">' . h($all[$id]['name']) . '</a>(' . h(fmt_money($cost)) . ')' . h($lvTag) . '<br>';
    }
}
nav_line();
wap_end(false);
