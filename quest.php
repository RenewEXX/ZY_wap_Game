<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

$u = require_login();
$tab = (string) ($_GET['tab'] ?? 'doing');
if (!in_array($tab, ['done', 'doing', 'todo', 'activity'], true)) {
    $tab = 'doing';
}
$a = (string) ($_GET['a'] ?? '');
if ($a === 'claim_arena') {
    flash_set(arena_guide_claim((int) $u['id']));
    header('Location: quest.php?tab=activity');
    exit;
}
if ($a === 'claim_peak') {
    flash_set(activity_task_claim((int) $u['id'], 'peak'));
    header('Location: quest.php?tab=activity');
    exit;
}
if ($a === 'claim_gw') {
    flash_set(activity_task_claim((int) $u['id'], 'guildwar'));
    header('Location: quest.php?tab=activity');
    exit;
}
if ($a === 'claim_halloween') {
    flash_set(festival_task_claim((int) $u['id'], 'halloween'));
    header('Location: quest.php?tab=activity');
    exit;
}
if ($a === 'claim_fest') {
    $fid = (string) ($_GET['fid'] ?? '');
    flash_set(festival_task_claim((int) $u['id'], $fid));
    header('Location: quest.php?tab=activity');
    exit;
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
$ch5 = ch4_quests();
$ch6 = ch5_quests();
$ch7 = rebirth2_quests();

wap_start('任务');
echo '<a href="quest.php?tab=done">已完成</a> . ';
echo '<a href="quest.php?tab=doing">进行中</a> . ';
echo '<a href="quest.php?tab=todo">待接受</a> . ';
echo '<a href="quest.php?tab=activity">活动任务</a>';
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
} elseif ($q >= 38 && $q < 50) {
    foreach (array_keys($ch4) as $id) {
        if ($id > $q || ($q === 38 && $id >= 40)) {
            $laterQuests[] = $id;
        }
    }
} elseif ($q >= 50 && $q < 62) {
    foreach (array_keys($ch5) as $id) {
        if ($id > $q) {
            $laterQuests[] = $id;
        }
    }
} elseif ($q >= 62 && $q < 77) {
    foreach (array_keys($ch6) as $id) {
        if ($id > $q) {
            $laterQuests[] = $id;
        }
    }
} elseif ($q >= 77) {
    foreach (array_keys($ch7) as $id) {
        if ($id > $q) {
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
    foreach (array_merge(array_keys($ch2), array_keys($ch3), array_keys($ch4), array_keys($ch5), array_keys($ch6), array_keys($ch7)) as $id) {
        if ($id < $q && !in_array($id, $doneIds, true)) {
            $doneIds[] = $id;
        }
    }
    sort($doneIds);
}

$nameOf = function ($id) use ($newSteps, $ch1, $ch2, $ch3, $ch4, $ch5, $ch6, $ch7) {
    if (isset($newSteps[$id])) {
        return $newSteps[$id]['t'];
    }
    return $ch1[$id]['name'] ?? $ch2[$id]['name'] ?? $ch3[$id]['name'] ?? $ch4[$id]['name'] ?? $ch5[$id]['name'] ?? $ch6[$id]['name'] ?? ('任务' . $id);
};
$descOf = function ($id) use ($newSteps, $ch1, $ch2, $ch3, $ch4, $ch5, $ch6, $ch7) {
    if (isset($newSteps[$id])) {
        return $newSteps[$id]['d'];
    }
    return $ch1[$id]['todo'] ?? $ch2[$id]['todo'] ?? $ch3[$id]['todo'] ?? $ch4[$id]['todo'] ?? $ch5[$id]['todo'] ?? $ch6[$id]['todo'] ?? '';
};

if ($tab === 'doing') {
    if ($doingId === null && $q < 16) {
        echo '<span class="muted">第一章已通关，自由探索。听说罪字碑动了……</span>';
    } elseif ($q >= 76) {
        echo '<span class="muted">五章全通关！世界BOSS每天刷新，熔心/冰渊副本、神魂升级等你。</span>';
    } elseif ($q >= 38 && $q < 50) {
        echo '<span class="muted">三章全通关！农场、母巢、公会战、巅峰之战等你（影子伙伴：全属性+5%）。</span>';
    } else {
        $qs = quest_state($u);
        $dName = $q >= 16 ? (string) ($qs['name'] ?? '') : $nameOf($doingId ?? $q);
        $dDesc = $q >= 16 ? (string) ($qs['todo'] ?? '') : $descOf($doingId ?? $q);
        echo '<div class="warn">【' . h(($qs['step'] ?? '') . '·' . $dName) . '】<br>' . h($dDesc) . '</div>';
        $prog = (($qs['ch'] ?? 0) === 2) ? quest_progress2_text((int) $u['id'], $qs) : (in_array(($qs['ch'] ?? 0), [3, 5, 6], true) ? quest_progress3_text((int) $u['id'], $qs) : quest_progress_text((int) $u['id'], $qs));
        if ($prog !== '') {
            echo h($prog) . '<br>';
        }
        echo '<br><a href="map.php?go=' . h($qs['loc']) . '">【立即前往·' . h($qs['locname']) . '】</a>';
    }
} elseif ($tab === 'activity') {
    echo '<b>活动任务（可与主线同时进行）</b><br>';
    $af = cflags((int) $u['id']);
    // 0) 节日限时活动（按 festivals() 配置动态渲染）
    foreach (festivals() as $fid => $f) {
        if (!festival_active($fid)) {
            continue;
        }
        $mark = (string) ($f['mark'] ?? ('fest_done_' . $fid));
        $fDone = (int) (cflags((int) $u['id'])[$mark] ?? 0) === (int) date('Y');
        $fMat = (int) (mats_of((int) $u['id'])[$f['mat']] ?? 0);
        $fNeed = (int) $f['mat_need'];
        echo '·<b><span style="color:#f84">【' . h($f['name']) . '】</span></b> ';
        if ($fDone) {
            echo '<span style="color:#6f6">[已完成]</span> 今年的称号与奖励已领取。<br>';
        } elseif ($fMat >= $fNeed) {
            echo '<span class="warn">[可领取]</span> ' . h($f['mat_name']) . '已集满！领取：随机宠物蛋×2 + 称号【' . h(title_ids()[$f['title']] ?? $f['title']) . '】+ ' . (int) $f['diamonds'] . '魔钻。<br>';
            echo '<a href="quest.php?a=claim_fest&fid=' . h($fid) . '">领取' . h($f['name']) . '奖励</a><br>';
        } else {
            echo '<span class="muted">[进行中]</span> 活动期间刷怪掉【' . h($f['mat_name']) . '】（约8%）。收集 ' . $fNeed . ' 个即可领取：随机宠物蛋×2 + 称号【' . h(title_ids()[$f['title']] ?? $f['title']) . '】+ ' . (int) $f['diamonds'] . '魔钻。<br>';
            echo h($f['mat_name']) . '：<b>' . $fMat . '</b>/' . $fNeed . '<br>';
        }
    }
    // 1) 竞技场征召
    $done = !empty($af['arena_guide_done']);
    echo '·<b>【竞技场征召】</b> ';
    if ($done) {
        echo '<span style="color:#6f6">[已完成]</span> 雷横的20银已领取。<br>';
    } elseif ((int) ($u['lv'] ?? 1) < 80) {
        echo '<span class="muted">[未解锁]</span> 需80级。参加竞技场的引导任务，奖励20银。<br>';
    } else {
        echo '<span class="warn">[可领取]</span> 80级了！参加竞技场，奖励20银。<br>';
        echo '<a href="quest.php?a=claim_arena">领取20银奖励</a><br>';
    }
    // 2) 巅峰之战
    $peakJoined = !empty($af['act_peak_joined']);
    $peakClaimed = !empty($af['act_peak_claimed']);
    echo '·<b>【巅峰之战】</b> ';
    if ($peakClaimed) {
        echo '<span style="color:#6f6">[已完成]</span> 奖励已领取。<br>';
    } elseif (!$peakJoined) {
        echo '<span class="muted">[进行中]</span> 参加一次巅峰之战（每晚22:30~23:00，底部【活动】进场），完成后奖励随机宠物蛋×2。<br>';
    } else {
        echo '<span class="warn">[可领取]</span> 已参加巅峰之战，领取奖励。<br>';
        echo '<a href="quest.php?a=claim_peak">领取随机宠物蛋×2</a><br>';
    }
    // 3) 公会战
    $gwJoined = !empty($af['act_gw_joined']);
    $gwClaimed = !empty($af['act_gw_claimed']);
    echo '·<b>【公会战】</b> ';
    if ($gwClaimed) {
        echo '<span style="color:#6f6">[已完成]</span> 奖励已领取。<br>';
    } elseif (!$gwJoined) {
        echo '<span class="muted">[进行中]</span> 公会战期间（宣战后24h）刷怪涨战功，参与一次。完成后奖励随机宠物蛋×2。<br>';
    } else {
        echo '<span class="warn">[可领取]</span> 已参加公会战，领取奖励。<br>';
        echo '<a href="quest.php?a=claim_gw">领取随机宠物蛋×2</a><br>';
    }
    // 预留：后续活动任务可在此追加
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
