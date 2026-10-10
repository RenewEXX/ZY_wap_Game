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
$ch2 = ch2_quests();
$ch3 = ch3_quests();
$ch4 = rebirth1_quests();

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
// 第二章(16~27)/第三章(28~37+)：进行中直接读quest_state，待接受=本章后续任务
$laterQuests = [];
if ($q >= 16 && $q < 28) {
    foreach (array_keys($ch2) as $id) {
        if ($id > $q || ($q === 16 && $id >= 20)) {
            $laterQuests[] = $id;
        }
    }
} elseif ($q >= 28 && $q < 38) {
    foreach (array_keys($ch3) as $id) {
        if ($id > $q || ($q === 28 && $id >= 30)) {
            $laterQuests[] = $id;
        }
    }
} elseif ($q >= 38) {
    foreach (array_keys($ch4) as $id) {
        if ($id > $q || ($q === 38 && $id >= 40)) {
            $laterQuests[] = $id;
        }
    }
}
if ($q >= 16) {
    $doingId = null;
    $todoIds = $laterQuests;
    foreach (array_merge([0, 1, 2], array_keys($ch1)) as $id) {
        if (!in_array($id, $doneIds, true)) {
            $doneIds[] = $id;
        }
    }
    foreach (array_merge(array_keys($ch2), array_keys($ch3), array_keys($ch4)) as $id) {
        if ($id < $q && !in_array($id, $doneIds, true)) {
            $doneIds[] = $id;
        }
    }
    sort($doneIds);
}

$nameOf = function ($id) use ($newSteps, $ch1, $ch2, $ch3, $ch4) {
    if (isset($newSteps[$id])) {
        return $newSteps[$id]['t'];
    }
    return $ch1[$id]['name'] ?? $ch2[$id]['name'] ?? $ch3[$id]['name'] ?? $ch4[$id]['name'] ?? ('任务' . $id);
};
$descOf = function ($id) use ($newSteps, $ch1, $ch2, $ch3, $ch4) {
    if (isset($newSteps[$id])) {
        return $newSteps[$id]['d'];
    }
    return $ch1[$id]['todo'] ?? $ch2[$id]['todo'] ?? $ch3[$id]['todo'] ?? $ch4[$id]['todo'] ?? '';
};

if ($tab === 'doing') {
    if ($doingId === null && $q < 16) {
        echo '<span class="muted">第一章已通关，自由探索。听说罪字碑动了……</span>';
    } elseif ($q >= 38) {
        echo '<span class="muted">三章全通关！农场、母巢、公会战、巅峰之战等你（影子伙伴：全属性+5%）。</span>';
    } else {
        $qs = quest_state($u);
        $dName = $q >= 16 ? (string) ($qs['name'] ?? '') : $nameOf($doingId ?? $q);
        $dDesc = $q >= 16 ? (string) ($qs['todo'] ?? '') : $descOf($doingId ?? $q);
        echo '<div class="warn">【' . h(($qs['step'] ?? '') . '·' . $dName) . '】<br>' . h($dDesc) . '</div>';
        $prog = (($qs['ch'] ?? 0) === 2) ? quest_progress2_text((int) $u['id'], $qs) : ((($qs['ch'] ?? 0) === 3) ? quest_progress3_text((int) $u['id'], $qs) : quest_progress_text((int) $u['id'], $qs));
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
