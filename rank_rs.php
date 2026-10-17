<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';
$u = require_login();
$a = (string) ($_GET['a'] ?? '');

// 学源罪技能
if ($a === 'learn') {
    flash_set(h(src_sin_learn((int) $u['id'], (string) ($_GET['s'] ?? ''))));
    header('Location: rank.php?a=rs');
    exit;
}

// 周结算（每周首次访问触发）
$settle = rs_weekly_settle();

rs_weekly_settle();
$myRs = rs_count((int) $u['id']);
$rank = rs_rank();

wap_start('转生榜');
echo '你的转生：' . ($myRs > 0 ? $myRs . '转' : '未转生') . '　源罪点数：' . src_sin_point((int) $u['id']) . '<br>';
echo '<div class="hr">--------</div>转生条件：999级 + 当日世界BOSS + 神魂X级。当前：' . h(rs_require_text((int) $u['id'])) . '<br>';

// 转生加成
if ($myRs > 0) {
    echo '基础属性+' . ($myRs * 10) . '%、每级成长×2、经验+50%、掉落+20%、金币+30%<br>';
}
echo '<div class="hr">--------</div>全服转生排行（按转生次数，其次等级）：<br>';
if ($rank === []) {
    echo '<span class="muted">还没有人转生。</span><br>';
}
foreach (array_slice($rank, 0, 20) as $i => $r) {
    $pos = $i + 1;
    $t = rs_rank_title($pos);
    $me = ((int) $r['id'] === (int) $u['id']) ? ' <span class="gold">(我)</span>' : '';
    echo $pos . '. ' . h($r['name']) . '　' . $r['rs'] . '转' . $r['lv'] . '级' . $me;
    if ($pos <= 10) {
        echo '　<span class="muted">周奖：' . $t['name'] . ' + ' . $t['diamonds'] . '魔钻</span>';
    }
    echo '<br>';
}

// 源罪技能树
echo '<div class="hr">--------</div>【源罪技能树】<br>';
if (!src_sin_unlock((int) $u['id'])) {
    echo '<span class="muted">转生后解锁源罪技能树（每次转生+1源罪点数）。</span><br>';
} else {
    $own = src_sin_learned((int) $u['id']);
    $cur = 1;
    foreach (src_sin_skills() as $sid => $sk) {
        if ($sk['tier'] !== $cur) {
            $cur = $sk['tier'];
            echo '<br>—— 第' . $cur . "层 ——<br>";
        }
        $has = isset($own[$sid]);
        echo '·' . h($sk['name']) . '：' . h($sk['desc']) . '（需' . src_sin_cost($sid) . '点）　';
        echo $has ? '<span class="gold">已学</span>' : '<a href="rank.php?a=learn&s=' . h($sid) . '">学习</a>';
        echo '<br>';
    }
}
wap_end(false);