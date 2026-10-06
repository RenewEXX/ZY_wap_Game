<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

$u = require_login();

// 任务传送：留在行动画面
$go = (string) ($_GET['go'] ?? '');
if ($go !== '' && isset(locations()[$go])) {
    $u['loc'] = $go;
    user_save($u);
    header('Location: home.php');
    exit;
}
// 走一步：留在行动画面
$to = (string) ($_GET['to'] ?? '');
if ($to !== '' && isset(locations()[$to])) {
    $here0 = loc((string) $u['loc']);
    if (isset($here0['exits'][$to])) {
        $u['loc'] = $to;
        user_save($u);
        if ($to === 'shop') {
            header('Location: shop.php');
            exit;
        }
        if ($to === 'camp') {
            header('Location: rest.php');
            exit;
        }
        header('Location: home.php');
        exit;
    }
}

$flash = flash_get();
$here = loc((string) $u['loc']);
$cur = (string) $u['loc'];
$all = locations();

wap_start('行动');
echo '你是 <span class="gold">' . h($u['username']) . '</span>';
$jobs = jobs();
$jid = job_id_of($u);
if (isset($jobs[$jid])) {
    echo '·' . h($jobs[$jid]['name']);
}
echo '<br>';
echo '等级 ' . (int) $u['lv'] . '　';
echo '<span class="hp">生命 ' . (int) $u['hp'] . '/' . (int) $u['maxhp'] . '</span>　';
echo '<span class="gold">' . h(fmt_money((int) $u['gold'])) . '</span><br>';
echo '所在：' . h($here['name']);
if ($flash !== '') {
    echo '<div class="warn">' . h($flash) . '</div>';
}
echo '<div class="hr">--------</div>';
echo '<div class="muted">' . h($here['desc']) . '</div>';
$smiths = blacksmiths();
if (isset($smiths[$cur])) {
    echo '铁匠【' . h($smiths[$cur]) . '】在炉子边。<a href="smith.php">找他强化装备</a><br>';
}
echo '<div class="hr">--------</div>';
quest_banner($u, 'home.php');

// 打怪：在行动画面直接开打
if ($here['monsters'] !== []) {
    echo '动手：<br>';
    foreach ($here['monsters'] as $mid) {
        $m = monsters()[$mid];
        $diff = monster_lv($mid) - (int) ($u['lv'] ?? 1);
        $tag = $diff <= -4 ? '经验衰减' : ($diff > 0 ? '越级+' . (int) min(100, $diff * 15) . '%' : '');
        echo '· ' . h($m['name']) . '[Lv' . monster_lv($mid) . ($tag !== '' ? '·' . $tag : '') . '] <a href="fight.php?a=start&m=' . h($mid) . '">打1只</a> / <a href="fight.php?a=start&m=' . h($mid) . '&n=6">群殴6只</a><br>';
    }
    echo '<div class="hr">--------</div>';
}

// 走动：上下左右下一步
$dirs = exit_dirs($cur);
$step = function ($d) use ($dirs, $all) {
    if (!isset($dirs[$d])) {
        return '·';
    }
    $id = $dirs[$d];
    return '<a href="home.php?to=' . h($id) . '">' . h($d . $all[$id]['name']) . '</a>';
};
echo '走动：北' . $step('北') . ' 南' . $step('南') . ' 东' . $step('东') . ' 西' . $step('西');
if (isset($dirs['上']) || isset($dirs['下'])) {
    echo ' 上' . $step('上') . ' 下' . $step('下');
}
echo '<br><a href="map.php">看大地图</a>';
echo '<div class="hr">--------</div>';
if ((int) $u['potion'] > 0) {
    echo '<a href="bag.php?a=drink">喝药 (' . (int) $u['potion'] . ')</a><br>';
}
echo '<a href="status.php">查看自身</a><br>';
echo '<a href="bag.php">打开背包</a>';
nav_line();
wap_end(false);
