<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

$u = require_login();
$uid = (int) $u['id'];
$a = (string) ($_GET['a'] ?? '');

wap_start('竞技场');
$flash = flash_get();
if ($flash !== '') {
    echo '<div class="warn">' . h($flash) . '</div>';
}

$me = arena_my($uid);
if ($a === 'join') {
    flash_set(arena_join($uid));
    header('Location: arena.php');
    exit;
}
// 查看类页面未加入也能看（不然按钮点进去是空的）
if (in_array($a, ['rank', 'prize', 'def', 'shop', 'shopbuy'], true)) {
    $zone = (string) ($me['zone'] ?? $u['zone'] ?? 'z1');
    $season = (string) ($me['season'] ?? arena_season());
    $coin = (int) (mats_of($uid)['arena_coin'] ?? 0);
    if ($a === 'rank') {
        echo '【排 名】本大区前50<br>';
        $st = db()->prepare('SELECT * FROM arena_rank WHERE zone=? AND season=? ORDER BY rank ASC LIMIT 50');
        $st->execute([$zone, $season]);
        foreach ($st->fetchAll() as $row) {
            $tier = arena_tier((int) $row['rank']);
            echo '<span style="color:' . $tier[2] . '">[' . $tier[1] . ']</span>第' . (int) $row['rank'] . '名：' . h($row['name']) . ' Lv' . (int) $row['lv'] . ' 战力' . (int) $row['power'] . ((int) $row['uid'] === $uid ? '（你）' : '') . '<br>';
        }
        echo '<a href="arena.php">返回</a>';
        nav_line();
        wap_end(false);
        exit;
    }
    if ($a === 'prize') {
        echo '【奖 励】每日凌晨5点结算（邮件），每周一赛季重置（上周奖励再发一份）。<br>';
        echo '—— 段位（按名次）——<br>';
        $prevMax = 0;
        foreach (arena_tier_bounds() as $max => $tier) {
            [$bg, $bc] = arena_tier_bonus($tier[0]);
            if ($max >= PHP_INT_MAX) {
                $range = '第' . ($prevMax + 1) . '名以后';
            } elseif ($prevMax + 1 >= $max) {
                $range = '第' . $max . '名';
            } else {
                $range = '第' . ($prevMax + 1) . '~' . $max . '名';
            }
            echo '<span style="color:' . $tier[2] . '">[' . $tier[1] . ']</span> ' . $range . '：段位保底(金' . $bg . '/币' . $bc . ')<br>';
            if ($max < PHP_INT_MAX) {
                $prevMax = $max;
            }
        }
        echo '—— 名次奖励档位 ——<br>';
        foreach ([1, 2, 3, 10, 50, 100, 300, 1000, 999999] as $top) {
            [$gold, $coinP, $stones] = arena_rewards($top);
            $label = $top === 999999 ? '第1001名以后' : '第' . $top . '名档';
            $txt = $label . '：' . h(fmt_money($gold)) . '/竞技场币' . $coinP;
            foreach ($stones as $sid => $n) {
                $txt .= '/' . h(mat_name($sid)) . 'x' . $n;
            }
            echo $txt . '<br>';
        }
        echo '<a href="arena.php">返回</a>';
        nav_line();
        wap_end(false);
        exit;
    }
    if ($a === 'def') {
        echo '【防 守 记 录】<br>';
        $rows = arena_defense_log($uid);
        if ($rows === []) {
            echo '<span class="muted">还没有人挑战过你。</span><br>';
        }
        foreach ($rows as $row) {
            echo date('m-d H:i', (int) $row['created_at']) . ' 被' . h($row['attacker_name']) . '挑战，你' . ((int) $row['win'] === 1 ? '胜利，排名不变' : '失败，排名降至第' . (int) $row['new_rank'] . '名') . '。<br>';
        }
        echo '<a href="arena.php">返回</a>';
        nav_line();
        wap_end(false);
        exit;
    }
    if ($a === 'shop') {
        echo '【竞 技 场 商 店】竞技场币：' . $coin . '<br>';
        foreach (arena_shop() as $mid => $g) {
            echo '·' . h($g['name']) . ' ' . $g['price'] . '币（' . ($g['period'] === 'day' ? '每日' : '每月') . '限购' . $g['limit'] . '） <a href="arena.php?a=shopbuy&m=' . h($mid) . '">购买</a><br>';
        }
        echo '<a href="arena.php">返回</a>';
        nav_line();
        wap_end(false);
        exit;
    }
    if ($a === 'shopbuy') {
        flash_set(arena_shop_buy($uid, (string) ($_GET['m'] ?? '')));
        header('Location: arena.php?a=shop');
        exit;
    }
}
if (!$me) {
    echo '【竞 技 场】<br>';
    echo '想证明自己吗？80级可进。异步PVP，自动战斗，赢了排名上升，输了下降。每日凌晨5点结算，每周一重置赛季。<br>';
    echo '今日免费挑战5次，额外10金/次，也可1魔钻买5次。<br>';
    echo '<a href="arena.php?a=join">进入竞技场</a><br>';
    echo '<a href="npc.php?who=arena_m">返回</a>';
    nav_line();
    wap_end(false);
    exit;
}
arena_refresh_power($uid);
$me = arena_my($uid);
$ch = arena_chances($uid);
$myPower = power_score($u);
$coin = (int) (mats_of($uid)['arena_coin'] ?? 0);

if ($a === 'buy_gold') {
    flash_set(arena_buy_chances($uid, 'gold'));
    header('Location: arena.php?a=targets');
    exit;
}
if ($a === 'buy_diamond') {
    flash_set(arena_buy_chances($uid, 'diamond'));
    header('Location: arena.php?a=targets');
    exit;
}
if ($a === 'fight') {
    [$win, $log, $foeRank, $myOld, $myNew] = arena_fight($uid, (int) ($_GET['rank'] ?? 0));
    echo '【战 斗】<br>' . h(implode('<br>', $log)) . '<br>';
    echo '<div class="hr">--------</div>';
    echo '<a href="arena.php?a=targets">继续挑战</a> <a href="arena.php">返回</a>';
    nav_line();
    wap_end(false);
    exit;
}
echo '今日挑战：免费剩' . $ch['free'] . '/5，可用' . $ch['left'] . '次<br>';
$myTier = arena_tier((int) $me['rank']);
echo '当前：第' . (int) $me['rank'] . '名 <span style="color:' . $myTier[2] . '">[' . $myTier[1] . '段位]</span><br>';
echo '[<a href="arena.php?a=targets">挑战对手</a>] [<a href="arena.php?a=rank">查看排名</a>] [<a href="arena.php?a=prize">查看奖励</a>] [<a href="arena.php?a=def">防守记录</a>] [<a href="arena.php?a=shop">竞技场商店</a>]<br>';
if ($a === 'targets') {
    echo '<div class="hr">--------</div>【选 择 对 手】可挑战范围：第' . max(1, (int) $me['rank'] - 34) . '名到第' . ((int) $me['rank'] - 1) . '名<br>';
    foreach (arena_targets($me) as $t) {
        echo '第' . (int) $t['rank'] . '名：' . h($t['name']) . ' Lv' . (int) $t['lv'] . ' 战力' . (int) $t['power'] . ' <a href="arena.php?a=fight&rank=' . (int) $t['rank'] . '">挑战</a><br>';
    }
    echo '额外：<a href="arena.php?a=buy_gold">10金买1次</a> <a href="arena.php?a=buy_diamond">1魔钻买5次</a><br>';
}
nav_line();
wap_end(false);
