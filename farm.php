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
if ($a === 'fert') {
    flash_set(farm_fertilize($uid, (int) ($_GET['p'] ?? 0)));
    header('Location: farm.php');
    exit;
}
if ($a === 'buyslot') {
    flash_set(farm_buy_slot($uid));
    header('Location: farm.php');
    exit;
}
if ($a === 'steal') {
    flash_set(farm_steal($uid, (int) ($_GET['v'] ?? 0), (int) ($_GET['p'] ?? 0)));
    header('Location: farm.php?view=' . (int) ($_GET['v'] ?? 0));
    exit;
}

wap_start('王都农场');
$flash = flash_get();
if ($flash !== '') {
    echo '<div class="warn">' . h($flash) . '</div>';
}
$view = (int) ($_GET['view'] ?? 0);
if ($view > 0 && $view !== $uid) {
    $vu = user_by_id($view);
    if (!$vu || (int) ($vu['quest'] ?? 0) < 38) {
        echo '这家农场还没开张。<br>';
    } else {
        echo '【' . h($vu['username']) . '的农场】 <a href="farm.php">回我的农场</a><br>';
        echo '<div class="hr">--------</div>';
        $plots = farm_plots($view);
        $any = false;
        for ($i = 1; $i <= farm_slots($view); $i++) {
            if (empty($plots[$i])) {
                continue;
            }
            $c = farm_crops()[$plots[$i]['crop']];
            $left = $c['grow'] - (time() - (int) $plots[$i]['at']);
            echo $i . '号地：' . h($c['name']);
            if ($left <= 0 && empty($plots[$i]['stolen'])) {
                $any = true;
                echo '熟了！<a href="farm.php?a=steal&v=' . $view . '&p=' . $i . '">偷（+' . max(1, (int) ($c['coin'] * 0.4)) . '币）</a>';
            } elseif ($left <= 0) {
                echo '（已被偷过）';
            } else {
                echo '（生长中）';
            }
            echo '<br>';
        }
        if (!$any) {
            echo '<span class="muted">没啥可偷的，换一家吧。</span><br>';
        }
    }
    nav_line();
    wap_end(false);
    exit;
}

$mats = mats_of($uid);
echo '农场币：' . (int) ($mats['farm_coin'] ?? 0) . '<br>';
echo '<div class="hr">--------</div>【我的农场】（' . farm_slots($uid) . '/6块地） ';
if (farm_slots($uid) < 6) {
    echo '<a href="farm.php?a=buyslot">扩地（100/300币）</a>';
}
echo '<br>';
$plots = farm_plots($uid);
for ($i = 1; $i <= farm_slots($uid); $i++) {
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
            echo h($c['name']) . '熟了！<a href="farm.php?a=harvest&p=' . $i . '">收获(+' . max(1, $c['coin'] - (int) ($plots[$i]['stolen'] ?? 0)) . '币)</a>';
            if (!empty($plots[$i]['stolen'])) {
                echo '（被偷过）';
            }
            echo '<br>';
        } else {
            echo h($c['name']) . '生长中，剩' . h(dummy_fmt($left));
            if (empty($plots[$i]['fert'])) {
                echo ' <a href="farm.php?a=fert&p=' . $i . '">施肥(10银/-30%)</a>';
            }
            echo '<br>';
        }
    }
}
echo '<div class="hr">--------</div>【串门偷菜】<br>';
$st = db()->query('SELECT id, username, quest, chapter_flags FROM users WHERE id!=' . $uid . ' AND quest>=38 ORDER BY RANDOM() LIMIT 10');
$found = false;
while ($r = $st->fetch()) {
    $d = json_decode((string) ($r['chapter_flags'] ?? ''), true);
    $fp = (is_array($d) && is_array($d['farm'] ?? null)) ? $d['farm'] : [];
    $ripe = 0;
    foreach ($fp as $pl) {
        if (empty($pl) || !empty($pl['stolen'])) {
            continue;
        }
        $cc = farm_crops()[$pl['crop']] ?? null;
        if ($cc && time() - (int) $pl['at'] >= $cc['grow']) {
            $ripe++;
        }
    }
    if ($ripe > 0) {
        $found = true;
        echo '·<a href="farm.php?view=' . $r['id'] . '">' . h($r['username']) . '的农场（' . $ripe . '块熟了）</a><br>';
    }
}
if (!$found) {
    echo '<span class="muted">大家地里都没熟的，等等再来。</span><br>';
}
echo '<div class="hr">--------</div>';
echo 'NPC【菜商·豆豆】：<a href="npc.php?who=grocer">对话（农场币换粮食/升级卡/洗点药，每周限量）</a><br>';
nav_line();
wap_end(false);
