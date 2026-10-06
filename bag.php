<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

$u = require_login();
$a = (string) ($_GET['a'] ?? '');
$tab = (string) ($_GET['tab'] ?? 'equip');
if (!in_array($tab, ['equip', 'mat', 'quest', 'other'], true)) {
    $tab = 'equip';
}

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
    header('Location: bag.php?tab=other');
    exit;
}
if ($a === 'wear') {
    flash_set(wear_equip((int) $u['id'], (int) ($_GET['id'] ?? 0)));
    header('Location: bag.php?tab=equip');
    exit;
}
if ($a === 'off') {
    flash_set(take_off_equip((int) $u['id'], (int) ($_GET['id'] ?? 0)));
    header('Location: bag.php?tab=equip');
    exit;
}
if ($a === 'sell') {
    $st = db()->prepare('SELECT * FROM equips WHERE id=? AND uid=? AND pos=""');
    $st->execute([(int) ($_GET['id'] ?? 0), (int) $u['id']]);
    $eq = $st->fetch();
    if (!$eq) {
        flash_set('穿着的不能卖，先脱下来。');
    } else {
        $p = equip_sell_price((int) $eq['quality']);
        db()->exec('DELETE FROM equips WHERE id=' . (int) $eq['id']);
        $u['gold'] = (int) $u['gold'] + $p;
        user_save($u);
        flash_set('卖了【' . equip_shortname($eq['name']) . '】，+' . fmt_money($p) . '。');
    }
    header('Location: bag.php?tab=equip');
    exit;
}

wap_start('背包');
echo '<span class="gold">' . h(fmt_money((int) $u['gold'])) . '</span><br>';
echo '<a href="bag.php?tab=equip">装备</a> . ';
echo '<a href="bag.php?tab=mat">材料</a> . ';
echo '<a href="bag.php?tab=quest">任务</a> . ';
echo '<a href="bag.php?tab=other">其他</a>';
echo '<div class="hr">--------</div>';
$flash = flash_get();
if ($flash !== '') {
    echo '<div class="warn">' . h($flash) . '</div>';
}
$qcolor = ['#999', '#fff', '#6cf', '#c6f', '#fc3'];

if ($tab === 'equip') {
    $eqs = my_equips((int) $u['id']);
    echo '身上：<br>';
    $slots = equip_slots();
    $wornBy = [];
    $rings = [];
    foreach ($eqs as $e) {
        if ($e['pos'] !== 'wear') {
            continue;
        }
        if ($e['slot'] === 'ring') {
            $rings[] = $e;
        } else {
            $wornBy[$e['slot']] = $e;
        }
    }
    foreach ($slots as $sid => $sname) {
        if ($sid === 'ring') {
            for ($i = 0; $i < 2; $i++) {
                $r = $rings[$i] ?? null;
                echo $sname . ($i + 1) . '：' . ($r ? '<a href="equip.php?id=' . $r['id'] . '"><span style="color:' . $qcolor[(int) $r['quality']] . '">' . h(equip_shortname($r['name'])) . '</span></a>' : '空') . '<br>';
            }
            continue;
        }
        $e = $wornBy[$sid] ?? null;
        echo $sname . '：' . ($e ? '<a href="equip.php?id=' . $e['id'] . '"><span style="color:' . $qcolor[(int) $e['quality']] . '">' . h(equip_shortname($e['name'])) . '</span></a>' : '空') . '<br>';
    }
    echo '<div class="hr">--------</div>';
    echo '库存：<br>';
    $has = false;
    foreach ($eqs as $e) {
        if ($e['pos'] === 'wear') {
            continue;
        }
        $has = true;
        echo '<a href="equip.php?id=' . $e['id'] . '"><span style="color:' . $qcolor[(int) $e['quality']] . '">' . h(equip_shortname($e['name'])) . '</span></a>[' . h($slots[$e['slot']] ?? '') . ']';
        echo ' +' . (int) ($e['enhance_level'] ?? 0) . ((int) ($e['broken'] ?? 0) === 1 ? '（碎裂）' : '');
        echo ' <a href="bag.php?a=wear&id=' . $e['id'] . '">穿</a> <a href="bag.php?a=sell&id=' . $e['id'] . '">卖' . h(fmt_money(equip_sell_price((int) $e['quality']))) . '</a><br>';
    }
    if (!$has) {
        echo '<span class="muted">空。去刷怪掉装备吧。</span>';
    }
} elseif ($tab === 'mat') {
    $mats = mats_of((int) $u['id']);
    $qmats = quest_mats();
    $any = false;
    foreach ($mats as $mid => $num) {
        if (isset($qmats[$mid])) {
            continue;
        }
        $any = true;
        echo '·' . h(mat_name($mid)) . 'x' . $num . '<br>';
    }
    if (!$any) {
        echo '<span class="muted">空。刷怪会掉材料，掉率45%。</span>';
    }
} elseif ($tab === 'quest') {
    $qs = quest_state($u);
    echo '当前：' . h($qs['name']) . '<br><span class="muted">' . h($qs['todo']) . '</span><br>';
    $prog = quest_progress_text((int) $u['id'], $qs);
    if ($prog !== '') {
        echo h($prog) . '<br>';
    }
    echo '<div class="hr">--------</div>';
    $mats = mats_of((int) $u['id']);
    $qmats = quest_mats();
    $any = false;
    foreach ($qmats as $mid => $mname) {
        if (!empty($mats[$mid])) {
            $any = true;
            echo '·' . h($mname) . 'x' . $mats[$mid] . '（无法丢弃）<br>';
        }
    }
    if (!$any) {
        echo '<span class="muted">暂无任务物品。</span>';
    }
} else {
    echo '回血药：' . (int) $u['potion'] . '<br>';
    if ((int) $u['potion'] > 0) {
        echo '<a href="bag.php?a=drink">喝一瓶药</a><br>';
    }
    echo '<a href="shop.php">去黑市</a>';
}
nav_line();
wap_end(false);
