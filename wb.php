<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

$u = require_login();
drg_tick();
$uid = (int) $u['id'];
$day = wb_day();
$mid = (string) ($_GET['d'] ?? '');
$d = $mid !== '' ? dragon($mid) : [];
if ($d === []) {
    $mid = '';
}
$a = (string) ($_GET['a'] ?? '');
$mode = (string) ($_GET['mode'] ?? 'skill');
$back = 'wb.php';

// —— 离巢：回到该龙的门（安全区）——
if ($a === 'leave') {
    if ($d !== []) {
        $u['loc'] = (string) $d['gate'];
        unset($_SESSION['wb_burn']);
        user_save($u);
        header('Location: home.php');
        exit;
    }
    header('Location: wb.php');
    exit;
}

// —— 入巢：扣龙鳞引（同一条龙每天一次）——
if ($a === 'enter') {
    if ($d === []) {
        flash_set('没有这条龙。');
        header('Location: wb.php');
        exit;
    }
    $err = drg_enter($u, $mid);
    if ($err !== '') {
        flash_set($err);
        header('Location: wb.php');
        exit;
    }
    $u['loc'] = (string) $d['lair'];
    user_save($u);
    header('Location: wb.php?d=' . urlencode($mid));
    exit;
}

// —— 挑战：每点一次算一次出手 ——
if ($a === 'hit') {
    if ($d === []) {
        header('Location: wb.php');
        exit;
    }
    $st = drg_status($mid);
    if ($st['done'] === 1) {
        flash_set($d['name'] . '已经倒下，距离它重新醒来还有' . drg_respawn_text($mid) . '。奖励已按伤害排行发邮件。');
        header('Location: wb.php?d=' . urlencode($mid));
        exit;
    }
    if ((int) $u['hp'] <= 0) {
        $pen = death_penalty($u);
        $u['hp'] = 1;
        $u['loc'] = (string) $d['gate'];
        user_save($u);
        flash_set('你倒在了' . $d['lair_name'] . '，被抬了出来。' . $pen);
        header('Location: home.php');
        exit;
    }

    $boss = [
        'id' => $mid,
        'name' => (string) $d['name'],
        'atk' => (int) $d['atk'],
        'maxhp' => $st['max'],
        'hp' => $st['hp'],
        'lv' => (int) $d['lv'],
    ];
    $job = job_id_of($u);
    $sk = $mode === 'hit' ? null : active_skill($uid, $job);
    $pd = dmg_to_monster($u, $boss);
    $skillName = '普攻';
    $atkEl = '';
    if ($sk !== null && (int) $u['mp'] >= (int) $sk['mp']) {
        $u['mp'] = max(0, (int) $u['mp'] - (int) $sk['mp']);
        $skillName = (string) $sk['name'];
        $atkEl = drg_atk_el($sk);
        $pow = skill_power($sk);
        $kind = (string) ($sk['kind'] ?? 'phys');
        if ($kind === 'magic') {
            $pd = max(1, (int) ((player_matk($u) + random_int(4, 7)) * $pow) - (int) (monster_mdef($boss) / 2));
        } elseif ($kind === 'heal') {
            $pd = max(1, (int) (player_matk($u) * (float) $sk['mult'] * $pow) - (int) (monster_mdef($boss) / 2));
        } else {
            $pd = max(1, (int) ((player_patk($u) + random_int(0, 3)) * (float) $sk['mult'] * $pow) - monster_pdef($boss));
            if ($atkEl !== '') {
                $pd += max(0, (int) (player_matk($u) * 0.5) - (int) (monster_mdef($boss) / 2));
            }
        }
        add_skill_prof($uid, (string) $sk['skill']);
    }
    $pd += soul_truedmg($uid);
    $pd = max(0, (int) $pd);

    // 龙王对自身属性完全免疫：用同属性打它 = 挠痒
    $immune = $atkEl !== '' && $atkEl === (string) $d['el'];
    $real = drg_damage_vs((string) $d['el'], $atkEl, $pd);
    if ($real > 0) {
        drg_hurt($mid, $real);
        drg_add_dmg($uid, (string) $u['username'], $mid, $real);
    }
    $hpLeft = drg_status($mid)['hp'];
    $log = '你放出' . $skillName;
    if ($atkEl !== '') {
        $log .= '（' . element_name($atkEl) . '属性）';
    }
    $log .= '，造成' . $real . '点。';
    if ($immune) {
        $log .= $d['short'] . '对' . element_name((string) $d['el']) . '伤完全免疫——它的力量就是你的力量。';
    }

    if ($hpLeft <= 0 && drg_done($mid) !== 1) {
        drg_kill($uid, $mid);
        drg_rewards($mid);
        user_save($u);
        flash_set($log . $d['name'] . '倒下了！全服已收到公告，它将沉睡一个月。奖励按伤害排行发邮件：' . $d['set_name'] . '、传奇' . $d['short'] . '蛋、' . element_name((string) $d['el']) . '龙魂龙玉。');
        header('Location: wb.php?d=' . urlencode($mid));
        exit;
    }

    // 龙王反击：招式全部为自身属性伤害
    $move = drg_boss_move((string) $d['el']);
    $md = max(1, (int) (dmg_to_player($u, $boss) * $move['mult']));
    $u['hp'] = max(0, (int) $u['hp'] - $md);
    $log .= $d['short'] . '反手【' . $move['name'] . '】（' . element_name((string) $move['el']) . '），你掉' . $md . '点。';
    if (!empty($move['burn'])) {
        $_SESSION['wb_burn'] = min(6, (int) ($_SESSION['wb_burn'] ?? 0) + (int) $move['burn']);
        $log .= '你烧起来了！';
    }
    if (!empty($_SESSION['wb_burn'])) {
        $burn = max(1, (int) ((int) $u['maxhp'] * 0.05));
        $u['hp'] = max(0, (int) $u['hp'] - $burn);
        $_SESSION['wb_burn'] = (int) $_SESSION['wb_burn'] - 1;
        $log .= '灼烧-' . $burn . '。';
    }
    user_save($u);
    if ((int) $u['hp'] <= 0) {
        $pen = death_penalty($u);
        $u['hp'] = 1;
        $u['loc'] = (string) $d['gate'];
        unset($_SESSION['wb_burn']);
        user_save($u);
        flash_set($log . '你倒在了' . $d['lair_name'] . '门口。' . $pen);
        header('Location: home.php');
        exit;
    }
    flash_set($log);
    header('Location: wb.php?d=' . urlencode($mid));
    exit;
}

$flash = flash_get();
$myDmgAll = 0;

wap_start('龙王殿');

// ================= 单龙视图 =================
if ($d !== []) {
    $st = drg_status($mid);
    $myDmg = drg_my_dmg($uid, $mid, $day);
    echo '<div class="t">' . h((string) $d['name']) . '（' . h((string) $d['pillar']) . '·' . (int) $d['lv'] . '级）</div>';
    echo '<div class="muted">' . h((string) $d['epithet']) . '｜' . h(element_name((string) $d['el'])) . '属性</div><br>';
    echo h((string) $d['intro']) . '<br>';
    if ($st['done'] === 1) {
        echo '<div class="warn">已被讨伐，距离重新醒来还有' . h(drg_respawn_text($mid)) . '。奖励已按伤害排行发邮件。</div>';
    } else {
        echo '<div class="hp">全服血量：' . number_format($st['hp']) . '/' . number_format($st['max']) . '（' . $st['pct'] . '%）</div>';
    }
    echo '我本次讨伐的伤害：' . number_format($myDmg) . '<br>';
    echo '<div class="warn">规则：' . h((string) $d['short']) . '对' . element_name((string) $d['el']) . '属性伤害<b>完全免疫</b>，而它的每一次攻击都是' . element_name((string) $d['el']) . '属性。</div>';
    echo '<div class="hr">--------</div>';
    if ($st['done'] !== 1) {
        echo '<a href="wb.php?d=' . h($mid) . '&a=hit">打它一下（默认技能）</a> <a href="wb.php?d=' . h($mid) . '&a=hit&mode=hit">普攻磨血</a><br>';
    }
    echo '<a href="wb.php?d=' . h($mid) . '&a=leave">离开' . h((string) $d['lair_name']) . '（回' . h((string) $d['gate_name']) . '）</a> ';
    echo '<a href="wb.php">六圣柱总览</a><br>';
    echo '<div class="hr">--------</div>本次讨伐排行（前10）<br>';
    $rank = drg_rank($mid, $day);
    if ($rank === []) {
        echo '<span class="muted">还没有人动过它。</span><br>';
    }
    foreach ($rank as $i => $r) {
        echo ($i + 1) . '. ' . h((string) $r['name']) . ' ' . number_format((int) $r['dmg']) . '<br>';
    }
    echo '<div class="hr">--------</div>';
    echo '掉落（按排行邮件发放）：' . h((string) $d['set_name']) . '7件套 / 传奇' . h((string) $d['short']) . '宠物蛋 / ' . element_name((string) $d['el']) . '龙魂·龙玉 / 神魂碎片 / 魔钻·银<br>';
    echo '<span class="muted">门：' . h((string) $d['gate_name']) . '（' . h((string) $d['gate']) . '，' . (int) dragon_min_lv($mid) . '级+，可传送）</span><br>';
    nav_line();
    wap_end(false);
    exit;
}

// ================= 六圣柱总览 =================
echo '<div class="t">六圣柱·六龙王</div>';
echo '南炎（火）西风（风）北寒（冰）东雷（雷）深渊（暗）中央（光）——六柱撑住世界，六条龙各守一柱。<br>';
echo '<span class="muted">每条龙独立血量、独立排行、独立掉落；被讨伐后沉睡一个月才会重新现世（各自独立计时）。龙鳞引每天每条龙各扣一张。</span><br>';
echo '<div class="hr">--------</div>';
if ($flash !== '') {
    echo '<div class="warn">' . h($flash) . '</div>';
}
$order = ['doyle', 'aetherine', 'ysolde', 'rex', 'nidhogg', 'aurelian'];
foreach ($order as $dmId) {
    $dd = dragon($dmId);
    $ds = drg_status($dmId);
    $mine = drg_my_dmg($uid, $dmId, $day);
    $myDmgAll += $mine;
    $el = element_name((string) $dd['el']);
    echo '<div class="hr">--------</div>';
    echo '<div class="t">' . h((string) $dd['name']) . '（' . (int) $dd['lv'] . '）</div>';
    echo $el . '属性 · ' . h((string) $dd['pillar']) . ' · ' . h((string) $dd['epithet']) . '<br>';
    echo '<span class="muted">' . h((string) $dd['intro']) . '</span><br>';
    if ($ds['done'] === 1) {
        echo '<div class="warn">已被讨伐，' . h(drg_respawn_text($dmId)) . '后重新现世。</div>';
    } else {
        echo '<div class="hp">全服血量：' . $ds['pct'] . '%（' . number_format($ds['hp']) . '/' . number_format($ds['max']) . '）</div>';
        echo '<span class="muted">免疫' . $el . '属性伤害｜进场需' . (int) dragon_min_lv($dmId) . '级+龙鳞引×1（今日已进场则免费再进）</span><br>';
        echo '<a href="wb.php?d=' . h($dmId) . '&a=enter">用龙鳞引进' . h((string) $dd['lair_name']) . '</a><br>';
        if (drg_enter_mark($uid, $dmId) === $day) {
            echo '<a href="wb.php?d=' . h($dmId) . '">今日已进场，直接去</a><br>';
        }
    }
    echo '我本次讨伐的伤害：' . number_format($mine) . '（' . $el . '）<br>';
    echo '门：<span class="muted">' . h((string) $dd['gate_name']) . '（' . h((string) $dd['gate']) . '）</span><br>';
    echo '掉落：<b>只有斩龙才有</b> ' . h((string) $dd['set_name']) . ' / 传奇' . h((string) $dd['short']) . '蛋 / ' . $el . '龙魂·龙玉<br>';
}
echo '<div class="hr">--------</div>';
echo '我的六柱总伤害：' . number_format($myDmgAll) . '<br>';
echo '<span class="muted">六圣柱小怪不掉套装，只掉' . element_name('fire') . '—' . element_name('light') . '各色龙魂与龙王精华。套装与宠物蛋只从斩龙排行产出。龙玉是转生必需品（1转2个、2转4个、3转6个）。</span><br>';
nav_line();
wap_end(false);
