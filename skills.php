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
        continue;
    }
    $s = $mine[$sid];
    $active = active_skill((int) $u['id'], $job);
    $isActive = $active && $active['skill'] === $sid;
    echo ($isActive ? '◀默认 ' : '<a href="skills.php?a=set&id=' . h($sid) . '">设为默认</a> ');
    echo $s['level'] . '级·' . $s['tier'] . '阶·熟练' . $s['prof'] . '/' . tier_prof_need(min(10, (int) $s['tier'] + 1)) . '<br>';
    $pw = skill_power($s);
    $pw1 = 1 + max(0, (int) ($s['tier'] ?? 1) - 1) * 0.05 + max(0, (int) ($s['level'] ?? 1)) * 0.04;
    $pwt = 1 + max(0, min(10, (int) ($s['tier'] ?? 1))) * 0.05 + max(0, (int) ($s['level'] ?? 1) - 1) * 0.04;
    echo '<span class="muted">现威力×' . number_format($pw, 2) . '（升1级+' . number_format($pw1 - $pw, 2) . '，升1阶+' . number_format($pwt - $pw, 2) . '，每级+4%每阶+5%）</span><br>';
    echo '<span class="muted">10阶：' . h($c['t10']) . '　20级：' . h($c['lv20']) . '</span><br>';
}
echo '<div class="hr">--------</div>';
$rb2j = (string) (cflags((int) $u['id'])['rb2_job'] ?? '');
if ($rb2j !== '') {
    $pc = rb2_passive_catalog()[$rb2j] ?? null;
    if ($pc) {
        echo '被动技能：<b>' . h($pc['name']) . '</b>（' . h(rebirth2_name($rb2j)) . '专属，常驻生效）<br>';
        echo '<span class="muted">' . h($pc['desc']) . '</span><br>';
    }
} else {
    echo '<span class="muted">二转后自动领悟被动技能（599级找瓦尔顿）。</span><br>';
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
