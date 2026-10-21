<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

$u = require_login();

$jobNames = ['warrior' => '战士', 'mage' => '法师', 'hunter' => '猎手', 'priest' => '牧师', 'summoner' => '召唤师'];
$myJob = job_id_of($u);
$job = (string) ($_GET['job'] ?? $myJob);
if (!isset($jobNames[$job])) {
    $job = $myJob;
}

$cat = skill_catalog();
$mine = [];
foreach (my_skills((int) $u['id']) as $s) {
    $mine[$s['skill']] = $s;
}

wap_start('技能图鉴');
echo '<span class="muted">点职业按钮，只看该职业的技能。</span>';
echo '<div class="nav" style="grid-template-columns:repeat(5,1fr)">';
foreach ($jobNames as $jid => $jname) {
    $on = $jid === $job ? ' class="on"' : '';
    $star = $jid === $myJob ? '★' : '';
    echo '<a' . $on . ' href="skill_codex.php?job=' . h($jid) . '">' . h($jname) . $star . '</a>';
}
echo '</div>';
echo '<div class="hr">--------</div>';

$jd = jobs()[$job] ?? null;
if ($jd !== null && (string) ($jd['desc'] ?? '') !== '') {
    echo '<div class="muted">' . h((string) $jd['desc']) . '</div>';
    echo '<div class="hr">--------</div>';
}

foreach (job_skillset($job) as $sid) {
    $c = $cat[$sid] ?? null;
    if ($c === null) {
        continue;
    }
    $isMine = isset($mine[$sid]);
    $lvTxt = $isMine ? '<span style="color:#8f8">已学' . (int) $mine[$sid]['level'] . '级</span>' : '<span class="muted">未学</span>';
    echo '<div style="margin:10px 0">';
    echo '<b style="color:#9f9">' . h((string) $c['name']) . '</b> <span class="muted">耗蓝' . (int) $c['mp'] . '</span> ' . $lvTxt . '<br>';
    echo '<span style="color:#cfc">' . h(skill_effect_desc($c)) . '</span><br>';
    echo '<span class="muted">10阶：' . h((string) ($c['t10'] ?? '')) . '<br>20级：' . h((string) ($c['lv20'] ?? '')) . '</span>';
    echo '</div>';
}

// 2 觉被动（如果有）
$rb2j = (string) (cflags((int) $u['id'])['rb2_job'] ?? '');
if ($rb2j !== '' && ($rb2pc = rb2_passive_catalog()[$rb2j] ?? null)) {
    echo '<div class="hr">--------</div>';
    echo '<b>' . h((string) $rb2pc['name']) . '</b><span class="muted">（' . h(rebirth2_name($rb2j)) . '专属被动，常驻）</span><br>';
    echo '<span style="color:#cfc">' . h((string) $rb2pc['desc']) . '</span><br>';
}

echo '<div class="hr">--------</div>';
echo '<a href="skills.php">← 返回技能</a><br>';
nav_line();
wap_end(false);
