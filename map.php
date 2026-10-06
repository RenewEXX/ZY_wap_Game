<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

$u = require_login();
$cur = (string) $u['loc'];
$go = (string) ($_GET['go'] ?? '');
if ($go !== '' && isset(locations()[$go])) {
    // 任务快速传送：直接到目标
    $u['loc'] = $go;
    $cur = $go;
    user_save($u);
    header('Location: map.php');
    exit;
}
$to = (string) ($_GET['to'] ?? '');
$all = locations();

if ($to !== '' && isset($all[$to])) {
    $here = loc($cur);
    if (isset($here['exits'][$to]) || $to === $cur) {
        $u['loc'] = $to;
        $cur = $to;
        user_save($u);
        if ($to === 'shop') {
            header('Location: shop.php');
            exit;
        }
        if ($to === 'camp') {
            header('Location: rest.php');
            exit;
        }
        header('Location: map.php');
        exit;
    }
}

$here = loc($cur);
$flash = flash_get();
// 新手毕业到白石镇：quest=3 的人一进广场/道口就转第一章
if ((int) ($u['quest'] ?? 0) === 3 && ($cur === 'town_sq' || $cur === 'gate')) {
    $u['quest'] = 10;
    user_save($u);
    if ($flash !== '') {
        $flash .= ' ';
    }
    $flash .= '布隆拍你肩膀：“去冒险者公会吧，坠星者。”第一章开始！';
}
wap_start('地图');
if ($flash !== '') {
    echo '<div class="warn">' . h($flash) . '</div>';
    echo '<div class="hr">--------</div>';
}
quest_banner($u);

// 想去哪：路线查询
$routeTo = (string) ($_GET['route'] ?? '');
if ($routeTo === '') {
    $qs = quest_state($u);
    $routeTo = (string) ($qs['loc'] ?? '');
}
if ($routeTo !== '' && isset($all[$routeTo])) {
    $path = find_route($cur, $routeTo);
    echo '<div>你在哪：' . h($here['name']) . ' → 想去：' . h($all[$routeTo]['name']) . '<br>';
    if ($path === []) {
        echo '<span class="muted">走不过去，点【立即前往】飞吧。</span>';
    } else {
        $names = [];
        foreach ($path as $pid) {
            $names[] = $all[$pid]['name'] . ($pid === $cur ? '(你)' : '');
        }
        echo h(implode(' → ', $names));
        if (count($path) > 1) {
            $next = $path[1];
            echo '<br><a href="map.php?to=' . h($next) . '">下一步：' . h($all[$next]['name']) . '</a>';
        } else {
            echo '<br>已经到了！';
        }
    }
    echo '</div>';
    echo '<div class="hr">--------</div>';
}

// 3格方向盘：以当前位置为中心，最远显示3格
echo '所在：' . h($here['name']) . '<br><span class="muted">' . h($here['desc']) . '</span><br>';
$dirs = exit_dirs($cur);
$ray = function ($d) use ($cur) {
    $cells = [];
    $at = $cur ?? '';
    for ($i = 0; $i < 3; $i++) {
        $ds = exit_dirs($at);
        if (!isset($ds[$d])) {
            break;
        }
        $at = $ds[$d];
        $cells[] = $at;
    }
    return $cells;
};
$north = array_reverse($ray('北'));
$south = $ray('南');
$west = array_reverse($ray('西'));
$east = $ray('东');
$box = function ($id) use ($all, $cur) {
    if ($id === null) {
        return '．';
    }
    $t = $id === $cur ? $all[$id]['name'] . '(你)' : $all[$id]['name'];
    // 相邻格直接走，远格点一下看路线
    $here_dirs = exit_dirs($cur);
    $isNear = in_array($id, array_values($here_dirs), true);
    $url = $isNear ? 'map.php?to=' . $id : 'map.php?route=' . $id;
    return '<a href="' . h($url) . '">' . h($t) . '</a>';
};
echo '<table border="1" cellpadding="4" cellspacing="0" style="border-collapse:collapse;text-align:center">';
foreach ($north as $nid) {
    echo '<tr><td></td><td>' . $box($nid) . '</td><td></td></tr>';
}
echo '<tr><td>' . ($west ? $box($west[count($west) - 1] ?? null) : '．') . '</td><td>' . $box($cur) . '</td><td>' . ($east ? $box($east[0] ?? null) : '．') . '</td></tr>';
// 西/东多格展开
if (count($west) > 1 || count($east) > 1) {
    $w2 = $west[count($west) - 2] ?? null;
    $e2 = $east[1] ?? null;
    if ($w2 !== null || $e2 !== null) {
        echo '<tr><td>' . $box($w2) . '</td><td></td><td>' . $box($e2) . '</td></tr>';
    }
    $w3 = $west[count($west) - 3] ?? null;
    $e3 = $east[2] ?? null;
    if ($w3 !== null || $e3 !== null) {
        echo '<tr><td>' . $box($w3) . '</td><td></td><td>' . $box($e3) . '</td></tr>';
    }
}
foreach ($south as $sid) {
    echo '<tr><td></td><td>' . $box($sid) . '</td><td></td></tr>';
}
echo '</table>';
if (isset($dirs['上']) || isset($dirs['下'])) {
    $up = isset($dirs['上']) ? '<a href="map.php?to=' . h($dirs['上']) . '">上：' . h($all[$dirs['上']]['name']) . '</a>' : '·';
    $dn = isset($dirs['下']) ? '<a href="map.php?to=' . h($dirs['下']) . '">下：' . h($all[$dirs['下']]['name']) . '</a>' : '·';
    echo $up . ' | ' . $dn . '<br>';
}
echo '<div class="hr">--------</div>';
echo '所在：' . h($here['name']) . '（要动手回行动画面）<br><a href="home.php">回行动</a>';
nav_line();
wap_end(false);
