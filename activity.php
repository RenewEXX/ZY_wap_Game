<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

$u = require_login();
$uid = (int) $u['id'];
$a = (string) ($_GET['a'] ?? '');

if ($a === 'peak') {
    $arena = (string) ($_GET['arena'] ?? '');
    flash_set(peak_enter($uid, $arena));
    $nu = user_by_id($uid);
    if ($nu && peak_arena_of((string) $nu['loc']) !== '') {
        header('Location: map.php');
    } else {
        header('Location: activity.php');
    }
    exit;
}

$u = user_by_id($uid);
// 竞技场引导：80级+、主线 quest=28、未完成 → 奖励20银（原 arena_m NPC 的引导任务，NPC 下沉后迁移到这里）
if ((int) ($u['lv'] ?? 1) >= 80 && empty(cflags($uid)['arena_guide_done']) && (int) ($u['quest'] ?? 0) === 28) {
    cflag_set($uid, 'arena_guide_done', 1);
    $u['gold'] = (int) ($u['gold'] ?? 0) + 2000;
    user_save($u);
    $u = user_by_id($uid);
    flash_set('雷横拍了拍你的肩：好，80级了，有胆！竞技场欢迎你。任务完成，奖励20银！');
}

wap_start('活动中心');
$flash = flash_get();
if ($flash !== '') {
    echo '<div class="warn">' . h($flash) . '</div>';
}
echo '<span class="muted">竞技、赌马、公会战、巅峰，都在这。</span>';
echo '<div class="hr">--------</div>';

// ===== 竞技场 =====
echo '<b><span style="color:#6cf">【竞技场·雷横】</span></b><br>';
echo '<span class="muted">队伍/宠物/装备自动战斗，赢了排名上升，每日结算发奖。</span><br>';
echo '<a href="arena.php">进入竞技场</a> <a href="arena.php?a=rank">排名</a> <a href="arena.php?a=prize">奖励</a> <a href="arena.php?a=def">防守记录</a><br>';
echo '<div class="hr">--------</div>';

// ===== 赛马 =====
echo '<b><span style="color:#fc3">【赌马·老霍】</span></b><br>';
echo '<span class="muted">10 匹马，2 小时一场，1~10 魔钻下注，冠军分奖池六成，全服同池。</span><br>';
echo '<a href="horse.php">去赌马</a><br>';
echo '<div class="hr">--------</div>';

// ===== 公会战 =====
echo '<b><span style="color:#9f9">【公会战】</span></b><br>';
echo '<span class="muted">会长宣战、24 小时刷怪比战功，战功换奖励。</span><br>';
echo '<a href="guild.php">进入公会（宣战/战功）</a><br>';
echo '<div class="hr">--------</div>';

// ===== 巅峰之战 =====
echo '<b><span style="color:#f6a">【巅峰之战·阿战】</span></b><br>';
$lv = (int) $u['lv'];
echo '<span class="muted">每晚 22:30~23:00，三个战场按等级分流（100~300 / 301~600 / 601~999），活到最后拿真龙丹（+3 自由属性点）。</span><br>';
if (!peak_open()) {
    $left = strtotime(date('Y-m-d') . ' 22:30:00') - time();
    if ($left < 0) {
        $left = strtotime('tomorrow 22:30:00') - time();
    }
    echo '<span class="warn">现在没开，距离开场约 ' . dummy_fmt(max(0, $left)) . '。</span><br>';
} else {
    $myArena = peak_arena_for_level($lv);
    if ($myArena === '') {
        echo '<span class="warn">巅峰战场只收 100~999 级，你 ' . $lv . ' 级还没资格。</span><br>';
    } else {
        $cfg = peak_arenas()[$myArena];
        echo '<span style="color:#6f6">战场进行中！你的段位：' . $cfg['min'] . '~' . $cfg['max'] . '级。</span><br>';
        echo '<a href="activity.php?a=peak&arena=' . h($myArena) . '">进场！（伤势回满，出来就没资格了）</a><br>';
    }
}
echo '<div class="hr">--------</div>';
nav_line();
wap_end();
