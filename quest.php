<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

$u = require_login();
$tab = (string) ($_GET['tab'] ?? 'doing');
if (!in_array($tab, ['done', 'doing', 'todo'], true)) {
    $tab = 'doing';
}
$q = (int) ($u['quest'] ?? 0);
$newSteps = [
    0 => ['t' => '麦田驱逐', 'd' => '雾谷麦田打哥布林。'],
    1 => ['t' => '老井哭声', 'd' => '老井打女妖，靠技能。'],
    2 => ['t' => '断桥巨影', 'd' => '断桥打食人魔，送大骨棒+黑币。'],
];
$ch1 = ch1_quests();

wap_start('任务');
echo '<a href="quest.php?tab=done">已完成</a> . ';
echo '<a href="quest.php?tab=doing">进行中</a> . ';
echo '<a href="quest.php?tab=todo">待接受</a>';
echo '<div class="hr">--------</div>';

$order = array_merge([0, 1, 2], array_keys($ch1));
$doneIds = [];
$todoIds = [];
$doingId = null;
foreach ($order as $id) {
    if ($id < $q || $q >= 16) {
        if ($q >= 16 || $id < $q) {
            $doneIds[] = $id;
        }
    } elseif ($id === $q) {
        $doingId = $id;
    } else {
        $todoIds[] = $id;
    }
}
if ($q >= 16) {
    $doingId = null;
    $todoIds = [];
}

$nameOf = function ($id) use ($newSteps, $ch1) {
    if (isset($newSteps[$id])) {
        return $newSteps[$id]['t'];
    }
    return $ch1[$id]['name'] ?? ('任务' . $id);
};
$descOf = function ($id) use ($newSteps, $ch1) {
    if (isset($newSteps[$id])) {
        return $newSteps[$id]['d'];
    }
    return $ch1[$id]['todo'] ?? '';
};

if ($tab === 'doing') {
    if ($doingId === null) {
        echo '<span class="muted">第一章已通关，自由探索。听说罪字碑动了……</span>';
    } else {
        $qs = quest_state($u);
        echo '<div class="warn">【' . h($nameOf($doingId)) . '】<br>' . h($descOf($doingId)) . '</div>';
        $prog = quest_progress_text((int) $u['id'], $qs);
        if ($prog !== '') {
            echo h($prog) . '<br>';
        }
        echo '<br><a href="map.php?go=' . h($qs['loc']) . '">【立即前往·' . h($qs['locname']) . '】</a>';
    }
} elseif ($tab === 'done') {
    if (!$doneIds) {
        echo '<span class="muted">暂无已完成任务。</span>';
    }
    foreach ($doneIds as $id) {
        echo h($nameOf($id)) . ' [已完成]<br>';
    }
} else {
    if (!$todoIds) {
        echo '<span class="muted">没有待接受任务了。</span>';
    }
    foreach ($todoIds as $id) {
        echo h($nameOf($id)) . '<br><span class="muted">' . h($descOf($id)) . '</span><br>';
    }
}
nav_line();
wap_end(false);
