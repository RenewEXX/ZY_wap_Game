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
        if ($to === 'dsw_throne' && dsw_stage((int) $u['id']) < 5) {
            flash_set('王座大门纹丝不动。集齐四样信物（阶段5/5）才能推开。');
            header('Location: home.php');
            exit;
        }
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
echo '<span class="hp">生命 ' . (int) $u['hp'] . '/' . (int) $u['maxhp'] . '</span> <span style="color:#6cf">魔力 ' . (int) ($u['mp'] ?? 0) . '/' . (int) ($u['maxmp'] ?? 0) . '</span>　';
echo '<span class="gold">' . h(fmt_money((int) $u['gold'])) . '</span><br>';
echo '所在：' . h($here['name']);
if ($flash !== '') {
    echo '<div class="warn">' . h($flash) . '</div>';
}
echo '<div class="hr">--------</div>';
echo '<div class="muted">' . h($here['desc']) . '</div>';
$npcLinks = [
    'smith' => [['barton', '老铁匠·巴顿']], 'supply' => [['martha', '杂货商·玛莎']],
    'market' => [['aileen', '药剂师·艾琳']], 'wall' => [['carl', '守卫队长·卡尔']], 'tavern' => [['jack', '酒馆老板·老杰克']],
    'gate' => [['lily', '神秘少女·莉莉']], 'mansion' => [['augustus', '镇长·奥古斯都']], 'church' => [['thomas', '牧师·托马斯']],
    'town_sq' => [['alice', '修女·爱丽丝']],
];
foreach ($npcLinks[$cur] ?? [] as [$npcId, $npcName]) {
    echo 'NPC【' . h($npcName) . '】：<a href="npc.php?who=' . h($npcId) . '">对话</a><br>';
}
$smiths = blacksmiths();
if (isset($smiths[$cur])) {
    echo '铁匠【' . h($smiths[$cur]) . '】在炉子边。<a href="smith.php">找他强化装备</a><br>';
}
echo '<div class="hr">--------</div>';
if (in_array($cur, dsw_maps(), true)) {
    $left = 1800 - (time() - (int) (mats_of((int) $u['id'])['dsw_enter'] ?? time()));
    $stg = dsw_stage((int) $u['id']);
    $need = [1 => '幽暗黏液×10（黑暗史莱姆）', 2 => '沼泽之核×10（沼泽史莱姆）', 3 => '腐泥之心×8（黑暗史莱姆）', 4 => '王座徽记×5（沼泽史莱姆）', 5 => '去王座大门，推开王座之间，杀霍克'][min(5, $stg)];
    echo '<div class="warn">黑暗沼泽：剩' . gmdate('i:s', max(0, $left)) . '　阶段' . $stg . '/5：' . h($need) . '</div>';
}
$mailN = mail_unread((int) $u['id']);
if ($mailN > 0) {
    echo '<div class="warn">【邮件】你有' . $mailN . '封未读邮件！<a href="mail.php">查看邮件</a></div>';
}
quest_banner($u, 'home.php');
echo '<div class="hr">--------</div>';
echo '附近的人：<br>';
$nears = pres_close((int) $u['id']);
if ($nears === []) {
    echo '<span class="muted">只有风声。</span><br>';
}
foreach ($nears as $nu) {
    echo '·<a href="look.php?id=' . (int) $nu['id'] . '">' . h($nu['username']) . '</a>(' . (int) $nu['lv'] . '级' . h(job_of(['job' => (string) $nu['job']])['name']) . ')<br>';
}

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
