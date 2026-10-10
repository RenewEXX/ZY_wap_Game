<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

$u = require_login();
$tab = (string) ($_GET['tab'] ?? 'power');
if ($tab === 'recharge') {
    $tab = 'spend';
}
if (!in_array($tab, ['power', 'active', 'spend', 'pet', 'horse'], true)) {
    $tab = 'power';
}
$job = (string) ($_GET['job'] ?? '');
if (!isset(jobs()[$job])) {
    $job = '';
}

wap_start('排行榜');
echo '<a href="rank.php?tab=power">战力</a> <a href="rank.php?tab=active">活跃</a> <a href="rank.php?tab=spend">消费</a> <a href="rank.php?tab=pet">宠物</a> <a href="rank.php?tab=horse">赛马</a><br>';
if ($tab === 'power') {
    echo '职业：<a href="rank.php?tab=power">全部</a> ';
    foreach (jobs() as $jid => $j) {
        echo '<a href="rank.php?tab=power&job=' . $jid . '">' . h($j['name']) . '</a> ';
    }
    echo '<br>';
}
echo '<div class="hr">--------</div>';

$rows = [];
if ($tab === 'power') {
    echo '【' . ($job !== '' ? h(jobs()[$job]['name']) : '全职业') . '战力榜·' . h(zones()[$u['zone'] ?? 'z1']['name'] ?? '') . '】（攻击×2+防御×1.5+生命/10+魔力/5+等级×5，本大区）<br>';
    $sql = 'SELECT * FROM users WHERE zone=:zone' . ($job !== '' ? ' AND job=:job' : '') . ' ORDER BY lv DESC LIMIT 200';
    $st = db()->prepare($sql);
    if ($job !== '') {
        $st->execute(['zone' => (string) ($u['zone'] ?? 'z1'), 'job' => $job]);
    } else {
        $st->execute(['zone' => (string) ($u['zone'] ?? 'z1')]);
    }
    $all = $st->fetchAll();
    foreach ($all as $r) {
        $rows[] = ['name' => $r['username'], 'job' => job_name_of($r), 'lv' => $r['lv'], 'v' => power_score($r)];
    }
    usort($rows, fn($a, $b) => $b['v'] <=> $a['v']);
    $rows = array_slice($rows, 0, 20);
    foreach ($rows as $i => $r) {
        echo ($i + 1) . '. ' . h($r['name']) . '【' . h($r['job']) . '】Lv' . $r['lv'] . ' 战力' . $r['v'] . '<br>';
    }
} elseif ($tab === 'active') {
    echo '【活跃榜·' . h(zones()[$u['zone'] ?? 'z1']['name'] ?? '') . '】（累计在线时长，本大区）<br>';
    $st = db()->prepare('SELECT username, job, lv, active_secs FROM users WHERE zone=? AND active_secs>0 ORDER BY active_secs DESC LIMIT 20');
    $st->execute([(string) ($u['zone'] ?? 'z1')]);
    $i = 0;
    while ($r = $st->fetch()) {
        $i++;
        echo $i . '. ' . h($r['username']) . '【' . h(jobs()[$r['job']]['name'] ?? '战士') . '】Lv' . $r['lv'] . ' ' . h(fmt_playtime((int) $r['active_secs'])) . '<br>';
    }
} elseif ($tab === 'spend') {
    echo '【消费榜·' . h(zones()[$u['zone'] ?? 'z1']['name'] ?? '') . '】（累计消费魔钻：商城/拍卖成交/赛马，本大区）<br>';
    $st = db()->prepare('SELECT username, job, lv, diamonds_spent FROM users WHERE zone=? AND diamonds_spent>0 ORDER BY diamonds_spent DESC LIMIT 20');
    $st->execute([(string) ($u['zone'] ?? 'z1')]);
    $i = 0;
    while ($r = $st->fetch()) {
        $i++;
        echo $i . '. ' . h($r['username']) . '【' . h(jobs()[$r['job']]['name'] ?? '战士') . '】Lv' . $r['lv'] . ' 累计消费' . h(fmt_diamond((int) $r['diamonds_spent'])) . '<br>';
    }
} elseif ($tab === 'pet') {
    echo '【宠物榜·' . h(zones()[$u['zone'] ?? 'z1']['name'] ?? '') . '】（宠物战力=攻击×3+防御×2+生命/5+速度×2+等级×5，本大区）<br>';
    $st = db()->prepare('SELECT p.*, u.username FROM pets p JOIN users u ON u.id=p.uid WHERE u.zone=? ORDER BY p.level DESC LIMIT 100');
    $st->execute([(string) ($u['zone'] ?? 'z1')]);
    $all = $st->fetchAll();
    $rows = [];
    foreach ($all as $r) {
        $sp = pet_species()[$r['species']] ?? null;
        $rows[] = ['user' => $r['username'], 'name' => $sp['name'] ?? $r['species'], 'lv' => $r['level'], 'q' => pet_quality_name($r['quality']), 'v' => pet_power($r)];
    }
    usort($rows, fn($a, $b) => $b['v'] <=> $a['v']);
    $rows = array_slice($rows, 0, 20);
    foreach ($rows as $i => $r) {
        echo ($i + 1) . '. ' . h($r['user']) . '的' . h($r['name']) . $r['lv'] . '级(' . h($r['q']) . ')战力' . $r['v'] . '<br>';
    }
    if ($rows === []) {
        echo '<span class="muted">还没有人拥有宠物。</span><br>';
    }
} else {
    echo '【赛马榜·' . h(zones()[$u['zone'] ?? 'z1']['name'] ?? '') . '】（累计赌马盈利，本大区）<br>';
    $st = db()->prepare('SELECT username, job, lv, horse_won FROM users WHERE zone=? AND horse_won>0 ORDER BY horse_won DESC LIMIT 20');
    $st->execute([(string) ($u['zone'] ?? 'z1')]);
    $i = 0;
    while ($r = $st->fetch()) {
        $i++;
        echo $i . '. ' . h($r['username']) . '【' . h(jobs()[$r['job']]['name'] ?? '战士') . '】Lv' . $r['lv'] . ' +' . h(fmt_diamond((int) $r['horse_won'])) . '<br>';
    }
}
nav_line();
wap_end(false);

