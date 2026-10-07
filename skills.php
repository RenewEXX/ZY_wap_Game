<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

$u = require_login();
if ((string) ($_GET['a'] ?? '') === 'set') {
    flash_set(set_active_skill((int) $u['id'], (string) ($_GET['id'] ?? '')));
    header('Location: skills.php');
    exit;
}
$job = job_id_of($u);
$set = job_skillset($job);
$mine = [];
foreach (my_skills((int) $u['id']) as $s) {
    $mine[$s['skill']] = $s;
}

wap_start('技能');
echo h(job_of($u)['name']) . '技能　<span style="color:#6cf">魔力 ' . (int) ($u['mp'] ?? 0) . '/' . (int) ($u['maxmp'] ?? 0) . '</span><br>';
echo '<span class="muted">用一次涨1熟练度，同职业技能书可升级（最高20级）。</span>';
echo '<div class="hr">--------</div>';
foreach ($set as $sid) {
    $c = skill_catalog()[$sid];
    echo '<b>' . h($c['name']) . '</b>（耗蓝' . $c['mp'] . '）<br>';
    if (!isset($mine[$sid])) {
        echo '<span class="muted">未学会（' . ($sid === $set[0] ? '新手毕业赠送' : '第一章完成赠送') . '）</span><br>';
        continue;
    }
    $s = $mine[$sid];
    $active = active_skill((int) $u['id'], $job);
    $isActive = $active && $active['skill'] === $sid;
    echo ($isActive ? '◀默认 ' : '<a href="skills.php?a=set&id=' . h($sid) . '">设为默认</a> ');
    echo $s['level'] . '级·' . $s['tier'] . '阶·熟练' . $s['prof'] . '/' . tier_prof_need(min(10, (int) $s['tier'] + 1)) . '<br>';
    echo '<span class="muted">10阶：' . h($c['t10']) . '　20级：' . h($c['lv20']) . '</span><br>';
}
echo '<div class="hr">--------</div>';
echo '技能书：<br>';
$mats = mats_of((int) $u['id']);
$any = false;
foreach (skill_books() as $bid => $b) {
    if (empty($mats[$bid])) {
        continue;
    }
    $any = true;
    $bjob = skill_catalog()[$b['skill']]['job'] ?? '';
    echo '·' . h($b['name']) . 'x' . $mats[$bid];
    if ($bjob === $job) {
        echo ' <a href="bag.php?a=usebook&id=' . h($bid) . '">使用</a>';
    } else {
        echo '（' . h(job_of(['job' => $bjob])['name']) . '用）';
    }
    echo '<br>';
}
if (!$any) {
    echo '<span class="muted">空。食人魔/狱卒/莫尔/雷恩会掉技能书。</span>';
}
nav_line();
wap_end(false);
