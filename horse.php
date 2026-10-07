<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

$u = require_login();
$uid = (int) $u['id'];

if ((string) ($_GET['a'] ?? '') === 'bet') {
    flash_set(horse_bet($uid, (int) ($_GET['h'] ?? -1), (int) ($_GET['n'] ?? 0)));
    header('Location: horse.php');
    exit;
}

wap_start('赌马');
$flash = flash_get();
if ($flash !== '') {
    echo '<div class="warn">' . h($flash) . '</div>';
}
$pid = horse_period();
$race = horse_race($pid);
$left = max(0, (int) $race['ends_at'] - time());
echo '第' . $pid . '场，剩' . gmdate('H:i:s', $left) . '开赛（每2小时一场）<br>';
echo '奖池：<b>' . h(fmt_diamond(horse_pool($pid))) . '</b>（含系统保底50魔钻）<br>';
echo '规则：冠军分60%，亚军25%，季军15%，同名次按押注比例分。每人每场押一匹，1~10魔钻。<br>';
echo '<div class="hr">--------</div>';
$st = db()->prepare('SELECT horse, amount FROM horse_bets WHERE race_id=? AND uid=?');
$st->execute([$pid, $uid]);
$mine = $st->fetch();
if ($mine) {
    echo '你押了【' . h(horse_names()[(int) $mine['horse']]) . '】' . (int) $mine['amount'] . '魔钻，等开赛。<br>';
} else {
    echo '余额' . h(fmt_diamond((int) ($u['diamonds'] ?? 0))) . '，选马下注：<br>';
    foreach (horse_names() as $i => $hn) {
        echo '·【' . h($hn) . '】 ';
        foreach ([1, 5, 10] as $n) {
            echo '<a href="horse.php?a=bet&h=' . $i . '&n=' . $n . '">押' . $n . '</a> ';
        }
        echo '<br>';
    }
}
echo '<div class="hr">--------</div>【上一场结果】<br>';
$st = db()->prepare('SELECT * FROM horse_races WHERE id<? AND status="done" ORDER BY id DESC LIMIT 1');
$st->execute([$pid]);
$last = $st->fetch();
if ($last && ($last['result'] ?? '') !== '') {
    $top = explode(',', (string) $last['result']);
    $names = horse_names();
    echo '冠：' . h($names[(int) ($top[0] ?? 0)]) . ' 亚：' . h($names[(int) ($top[1] ?? 0)]) . ' 季：' . h($names[(int) ($top[2] ?? 0)]) . '<br>';
} else {
    echo '<span class="muted">暂无。</span><br>';
}
nav_line();
wap_end(false);
