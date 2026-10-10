<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

$u = require_login();
$uid = (int) $u['id'];
$a = (string) ($_GET['a'] ?? '');
arena_settle_daily();
arena_reset_weekly();

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
if (!$me) {
    echo '【竞 技 场】<br>';
    echo '想证明自己吗？80级可进。异步PVP，自动战斗，赢了排名上升，输了下降。每天凌晨5点结算，每周重置。<br>';
    echo '今日免费挑战5次，额外10银/次，也可1魔钻买5次。<br>';
    echo '<a href="arena.php?a=join">进入竞技场</a><br>';
    echo '<a href="npc.php?who=arena_m">返回</a>';
    nav_line();
    wap_end(false);
    exit;
}
$me = arena_my($uid);
$ch = arena_chances($uid);
$myPower = power_score($u);
$coin = (int) (mats_of($uid)['arena_coin'] ?? 0);

if ($a === 'buy_chance') {
    if ((int) ($u['diamonds'] ?? 0) < 10) {
        flash_set('魔钻不够，1魔钻换5次。');
    } else {
        $u['diamonds'] = (int) ($u['diamonds'] ?? 0) - 10;
        user_save($u);
        spend_diamonds($uid, 10);
        $day = date('Y-m-d');
        $mats = mats_of($uid);
        mat_set($uid, 'arena_buy_' . $day, (int) ($mats['arena_buy_' . $day] ?? 0) + 5);
        flash_set('买下5次挑战！');
    }
    header('Location: arena.php');
    exit;
}
if ($a === 'fight') {
    $r = arena_fight($uid, (int) ($_GET['rank'] ?? 0));
    if (isset($r['err'])) {
        flash_set($r['err']);
        header('Location: arena.php');
        exit;
    }
    echo '【战 斗】<br>' . h($r['log']) . '<br>';
    echo '<div class="hr">--------</div>';
    echo '<a href="arena.php">继续挑战</a> <a href="arena.php">返回</a>';
    nav_line();
    wap_end(false);
    exit;
}
if ($a === 'rank') {
    $zone = (string) $me['zone'];
    echo '【排 名】本大区前50<br>';
    $st = db()->prepare('SELECT * FROM arena_ranks WHERE zone=? ORDER BY rank ASC LIMIT 50');
    $st->execute([$zone]);
    foreach ($st->fetchAll() as $row) {
        echo '第' . (int) $row['rank'] . '名：' . h($row['name']) . ' 战力' . (int) $row['power'] . ((int) $row['uid'] === $uid ? '（你）' : '') . '<br>';
    }
    echo '<a href="arena.php">返回</a>';
    nav_line();
    wap_end(false);
    exit;
}
if ($a === 'prize') {
    echo '【奖 励】每日凌晨5点结算（邮件），每周重置（回到2001名重争，上周奖励双倍竞技场币）。<br>';
    foreach ([1 => '第1名', 2 => '第2名', 3 => '第3名', 10 => '第4-10名', 50 => '第11-50名', 200 => '第51-200名', 1000000 => '第201名以后'] as $top => $label) {
        $p = arena_prize_for($top);
        echo $label . '：' . h(fmt_money($p['gold'])) . '/竞技场币' . $p['arena_coin'] . ($p['gift'] > 0 ? '/低级附魔礼包' . $p['gift'] : '') . '<br>';
    }
    echo '<a href="arena.php">返回</a>';
    nav_line();
    wap_end(false);
    exit;
}
if ($a === 'def') {
    echo '【防 守 记 录】<br>';
    $st = db()->prepare('SELECT * FROM arena_logs WHERE uid=? AND kind="def" ORDER BY id DESC LIMIT 10');
    $st->execute([$uid]);
    $rows = $st->fetchAll();
    if ($rows === []) {
        echo '<span class="muted">还没有人挑战过你。</span><br>';
    }
    foreach ($rows as $row) {
        echo date('m-d H:i', (int) $row['created_at']) . ' 被' . h($row['foe']) . '挑战，你' . ((int) $row['win'] === 1 ? '胜利，排名不变' : '失败，排名降至第' . (int) $row['new_rank'] . '名') . '。<br>';
    }
    echo '<a href="arena.php">返回</a>';
    nav_line();
    wap_end(false);
    exit;
}
if ($a === 'shop') {
    echo '【竞 技 场 商 店】竞技场币：' . $coin . '<br>';
    $day = date('Y-m-d');
    $mon = date('Y-m');
    $mats = mats_of($uid);
    foreach (arena_shop() as $mid => $g) {
        $limit = '';
        $left = -1;
        if ($g['day'] > 0) {
            $used = (int) ($mats['arena_shop_' . $mid . '_' . $day] ?? 0);
            $left = $g['day'] - $used;
            $limit = '每日限购' . $g['day'] . '次（剩' . max(0, $left) . '）';
        } elseif ($g['month'] > 0) {
            $used = (int) ($mats['arena_shop_' . $mid . '_' . $mon] ?? 0);
            $left = $g['month'] - $used;
            $limit = '每月限购' . $g['month'] . '次（剩' . max(0, $left) . '）';
        }
        echo '·' . h($g['name']) . ' ' . $g['price'] . '币 ' . $limit . ' <a href="arena.php?a=shopbuy&m=' . h($mid) . '">购买</a><br>';
    }
    echo '<a href="arena.php">返回</a>';
    nav_line();
    wap_end(false);
    exit;
}
if ($a === 'shopbuy') {
    $mid = (string) ($_GET['m'] ?? '');
    $shop = arena_shop();
    if (!isset($shop[$mid])) {
        flash_set('没有这件商品。');
    } else {
        $g = $shop[$mid];
        $day = date('Y-m-d');
        $mon = date('Y-m');
        $mats = mats_of($uid);
        $key = $g['day'] > 0 ? 'arena_shop_' . $mid . '_' . $day : 'arena_shop_' . $mid . '_' . $mon;
        $lim = $g['day'] > 0 ? $g['day'] : $g['month'];
        if ((int) ($mats[$key] ?? 0) >= $lim) {
            flash_set('限购次数用完了。');
        } elseif ($coin < $g['price']) {
            flash_set('竞技场币不够。');
        } else {
            add_mat($uid, 'arena_coin', -$g['price']);
            add_mat($uid, $mid, 1);
            mat_set($uid, $key, (int) ($mats[$key] ?? 0) + 1);
            flash_set('买下' . $g['name'] . '！');
        }
    }
    header('Location: arena.php?a=shop');
    exit;
}

echo '【竞 技 场】<br>';
echo '我的排名：第' . (int) $me['rank'] . '名<br>';
echo '我的战力：' . $myPower . '<br>';
echo '今日免费挑战：' . $ch['free'] . '/5（购买剩' . $ch['buy'] . '次）<br>';
echo '[<a href="arena.php?a=targets">挑战对手</a>] [<a href="arena.php?a=rank">查看排名</a>] [<a href="arena.php?a=prize">查看奖励</a>] [<a href="arena.php?a=def">防守记录</a>] [<a href="arena.php?a=shop">竞技场商店</a>]<br>';
if ($a === 'targets') {
    echo '<div class="hr">--------</div>【选 择 对 手】可挑战范围：第' . max(1, (int) $me['rank'] - 34) . '名到第' . ((int) $me['rank'] - 1) . '名<br>';
    foreach (arena_targets($uid) as $t) {
        echo '第' . (int) $t['rank'] . '名：' . h($t['name']) . ' 战力' . (int) $t['power'] . ' <a href="arena.php?a=fight&rank=' . (int) $t['rank'] . '">挑战</a><br>';
    }
    echo '额外挑战10银/次，<a href="arena.php?a=buy_chance">1魔钻买5次</a><br>';
}
nav_line();
wap_end(false);
