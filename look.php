<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

$u = require_login();
$t = user_by_id((int) ($_GET['id'] ?? 0));
if (!$t) {
    flash_set('没有这个人。');
    header('Location: home.php');
    exit;
}
if ((string) $t['loc'] !== (string) $u['loc']) {
    flash_set('他不在附近了。');
    header('Location: home.php');
    exit;
}
if ((int) $t['id'] === (int) $u['id']) {
    header('Location: status.php');
    exit;
}

wap_start('查看玩家');
echo '【' . h($t['username']) . '】<br>';
echo '等级：' . (int) $t['lv'] . '<br>';
echo '职业：' . h(job_of($t)['name']) . '（' . h(job_of($t)['skill']) . '）<br>';
echo '生命：' . (int) $t['hp'] . '/' . (int) $t['maxhp'] . '<br>';
echo '魔力：' . (int) ($t['mp'] ?? 0) . '/' . (int) ($t['maxmp'] ?? 0) . '<br>';
echo '位置：' . h(loc((string) $t['loc'])['name']) . '<br>';
echo '战力：' . power_score($t) . '<br>';
echo '<div class="hr">--------</div>';
echo '他的装备：<br>';
$wst = db()->prepare('SELECT * FROM equips WHERE uid=? AND pos="wear"');
$wst->execute([(int) $t['id']]);
$hasGear = false;
foreach (equip_sort_by_slot($wst->fetchAll()) as $we) {
    $hasGear = true;
    echo enhance_tag_html((int) ($we['enhance_level'] ?? 0)) . '<span style="color:' . equip_color($we) . '">' . h(equip_shortname((string) $we['name'])) . '</span><br>';
}
if (!$hasGear) {
    echo '<span class="muted">空身。</span><br>';
}
echo '<div class="hr">--------</div>';
echo '<a href="mail.php?a=write&to=' . h($t['username']) . '">写信</a><br>';
$mg = my_guild((int) $u['id']);
if ($mg && guild_can_invite($mg) && !my_guild((int) $t['id'])) {
    echo '<a href="guild.php?a=invite&uid=' . (int) $t['id'] . '">公会邀请（' . h($mg['name']) . '）</a><br>';
}
echo '<a href="home.php">回行动</a>';
nav_line();
wap_end(false);
