<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

$u = require_login();
$tab = (string) ($_GET['tab'] ?? 'power');
if (!in_array($tab, ['active', 'recharge', 'power', 'pet', 'horse'], true)) {
    $tab = 'power';
}

wap_start('排行榜');
echo '<a href="rank.php?tab=power">战力</a> <a href="rank.php?tab=active">活跃</a> <a href="rank.php?tab=recharge">充值</a> <a href="rank.php?tab=pet">宠物</a> <a href="rank.php?tab=horse">赛马</a><br>';
echo '<div class="hr">--------</div>';

$rows = [];
if ($tab === 'power') {
    echo '【战力榜】（攻击×2+防御×1.5+生命/10+魔力/5+等级×5）<br>';
    $st = db()->query('SELECT * FROM users ORDER BY lv DESC LIMIT 100');
    $all = $st->fetchAll();
    foreach ($all as $r) {
        $rows[] = ['name' => $r['username'], 'lv' => $r['lv'], 'v' => power_score($r)];
    }
    usort($rows, fn($a, $b) => $b['v'] <=> $a['v']);
    $rows = array_slice($rows, 0, 20);
    foreach ($rows as $i => $r) {
        echo ($i + 1) . '. ' . h($r['name']) . ' Lv' . $r['lv'] . ' 战力' . $r['v'] . '<br>';
    }
} elseif ($tab === 'active') {
    echo '【活跃榜】（累计在线时长）<br>';
    $st = db()->query('SELECT username, lv, active_secs FROM users ORDER BY active_secs DESC LIMIT 20');
    $i = 0;
    while ($r = $st->fetch()) {
        $i++;
        echo $i . '. ' . h($r['username']) . ' Lv' . $r['lv'] . ' ' . h(fmt_playtime((int) $r['active_secs'])) . '<br>';
    }
} elseif ($tab === 'recharge') {
    echo '【充值榜】（累计兑换魔钻）<br>';
    $st = db()->query('SELECT username, lv, diamonds_bought FROM users ORDER BY diamonds_bought DESC LIMIT 20');
    $i = 0;
    while ($r = $st->fetch()) {
        $i++;
        echo $i . '. ' . h($r['username']) . ' Lv' . $r['lv'] . ' ' . h(fmt_diamond((int) $r['diamonds_bought'])) . '<br>';
    }
} elseif ($tab === 'pet') {
    echo '【宠物榜】（最高等级宠物）<br>';
    $st = db()->query('SELECT p.uid, p.species, p.level, p.quality, u.username FROM pets p JOIN users u ON u.id=p.uid ORDER BY p.level DESC LIMIT 20');
    $i = 0;
    while ($r = $st->fetch()) {
        $i++;
        $sp = pet_species()[$r['species']] ?? null;
        echo $i . '. ' . h($r['username']) . '的' . h($sp['name'] ?? $r['species']) . (int) $r['level'] . '级(' . h(pet_quality_name($r['quality'])) . ')<br>';
    }
    if ($i === 0) {
        echo '<span class="muted">还没有人拥有宠物。</span><br>';
    }
} else {
    echo '【赛马榜】（累计赌马盈利）<br>';
    $st = db()->query('SELECT username, lv, horse_won FROM users ORDER BY horse_won DESC LIMIT 20');
    $i = 0;
    while ($r = $st->fetch()) {
        $i++;
        echo $i . '. ' . h($r['username']) . ' Lv' . $r['lv'] . ' +' . h(fmt_diamond((int) $r['horse_won'])) . '<br>';
    }
}
nav_line();
wap_end(false);
