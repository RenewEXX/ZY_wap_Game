<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

$u = require_login();
wb_tick();
$a = (string) ($_GET['a'] ?? '');
$mode = (string) ($_GET['mode'] ?? 'skill');

$hp = wb_hp_now();
$done = (int) (db()->query("SELECT num FROM sys_kv WHERE k='wb_done'")->fetchColumn() ?: 0);
$day = wb_day();
$myDmg = wb_my_dmg((int) $u['id'], $day);

if ($a === 'enter') {
    $mats = mats_of((int) $u['id']);
    if ((int) ($u['lv'] ?? 1) < 500) {
        flash_set('多伊尔连看都懒得看你——500级再来。');
        header('Location: home.php');
        exit;
    }
    if ((string) ($mats['wb_in_day'] ?? '') !== (string) $day) {
        if (empty($mats['wb_ticket'])) {
            flash_set('没有【龙鳞引】，进不去龙巢。75环奖励1张，打完记得留着。');
            header('Location: home.php');
            exit;
        }
        add_mat((int) $u['id'], 'wb_ticket', -1);
        mat_set((int) $u['id'], 'wb_in_day', $day);
    }
    $u['loc'] = 'wb_arena';
    user_save($u);
    header('Location: wb.php');
    exit;
}

if ($a === 'leave') {
    $u['loc'] = 'volcano_heart';
    user_save($u);
    header('Location: home.php');
    exit;
}

if ($a === 'hit') {
    if ($done === 1 || $hp <= 0) {
        flash_set('多伊尔今天已经倒下了，明天凌晨它会醒来。奖励按伤害排行发邮件。');
        header('Location: wb.php');
        exit;
    }
    if ((int) $u['hp'] <= 0) {
        $pen = death_penalty($u, 0.3);
        $u['hp'] = 1;
        $u['loc'] = 'volcano_heart';
        user_save($u);
        flash_set('你倒在了龙巢里，被抬了出来。' . $pen);
        header('Location: home.php');
        exit;
    }
    $boss = ['id' => 'doyle', 'name' => '烬焰领主·火龙多伊尔', 'atk' => 9000, 'maxhp' => wb_max_hp(), 'hp' => $hp];
    // 玩家出手（与野外同公式：技能倍率+物魔双防+真伤）
    $job = job_id_of($u);
    $sk = active_skill((int) ($u['id'] ?? 0), $job);
    $pd = dmg_to_monster($u, $boss);
    $skillName = '普攻';
    if ($mode !== 'hit' && $sk !== null && (int) ($u['mp'] ?? 0) >= (int) $sk['mp']) {
        $u['mp'] = (int) $u['mp'] - (int) $sk['mp'];
        $skillName = (string) $sk['name'];
        $pow = skill_power($sk);
        $kind = (string) ($sk['kind'] ?? 'phys');
        if ($kind === 'magic') {
            $pd = max(1, (int) ((player_matk($u) + random_int(4, 7)) * $pow) - (int) (monster_mdef($boss) / 2));
        } elseif ($kind === 'heal') {
            $pd = max(1, (int) (player_matk($u) * (float) $sk['mult'] * $pow) - (int) (monster_mdef($boss) / 2));
        } else {
            $pd = max(1, (int) ((player_patk($u) + random_int(0, 3)) * (float) $sk['mult'] * $pow) - monster_pdef($boss));
            if ((string) ($sk['el'] ?? '') !== '') {
                $pd += max(0, (int) (player_matk($u) * 0.5) - (int) (monster_mdef($boss) / 2));
            }
        }
        add_skill_prof((int) ($u['id'] ?? 0), (string) $sk['skill']);
    }
    $pd += soul_truedmg((int) ($u['id'] ?? 0));
    $pd = max(1, (int) $pd);
    db()->prepare("UPDATE sys_kv SET num=CASE WHEN num<? THEN 0 ELSE num-? END WHERE k='wb_hp'")->execute([$pd, $pd]);
    wb_add_dmg((int) $u['id'], (string) $u['username'], $pd);
    $hpLeft = wb_hp_now();
    $log = '你放出' . $skillName . '，造成' . $pd . '点。';
    if ($hpLeft <= 0 && (int) (db()->query("SELECT num FROM sys_kv WHERE k='wb_done'")->fetchColumn() ?: 0) === 0) {
        wb_rewards();
        user_save($u);
        flash_set($log . '多伊尔倒下了！全服按伤害排行发邮件，查收！');
        header('Location: wb.php');
        exit;
    }
    // BOSS反击（三技能轮转，数值见README模拟）
    $move = wb_boss_move($pd);
    $md = max(1, (int) (dmg_to_player($u, $boss) * $move['mult']));
    $u['hp'] = (int) $u['hp'] - $md;
    $log .= $boss['name'] . '反手【' . $move['name'] . '】，你掉' . $md . '点。';
    if (!empty($move['burn'])) {
        $b = (int) ($_SESSION['wb_burn'] ?? 0) + $move['burn'];
        $_SESSION['wb_burn'] = min(6, $b);
        $log .= '你烧起来了！';
    }
    if (!empty($_SESSION['wb_burn'])) {
        $burn = max(1, (int) ((int) $u['maxhp'] * 0.05));
        $u['hp'] = (int) $u['hp'] - $burn;
        $_SESSION['wb_burn'] = (int) $_SESSION['wb_burn'] - 1;
        $log .= '灼烧-' . $burn . '。';
    }
    user_save($u);
    if ((int) $u['hp'] <= 0) {
        $pen = death_penalty($u, 0.3);
        $u['hp'] = 1;
        $u['loc'] = 'volcano_heart';
        unset($_SESSION['wb_burn']);
        user_save($u);
        flash_set($log . '你倒在了龙巢门口。' . $pen);
        header('Location: home.php');
        exit;
    }
    flash_set($log);
    header('Location: wb.php');
    exit;
}

wap_start('世界BOSS');
echo '<div class="t">烬焰领主·火龙多伊尔（999级）</div>';
echo '自诩“灰烬中的守望者”，守着南炎柱，睡觉。对闯入者极度不耐。<br>';
if ($done === 1 || $hp <= 0) {
    echo '<div class="warn">今日已讨伐（明天凌晨刷新）。奖励已按伤害排行发邮件。</div>';
} else {
    echo '<div class="hp">全服血量：' . number_format($hp) . '/' . number_format(wb_max_hp()) . '</div>';
}
echo '我的今日伤害：' . number_format($myDmg) . '<br>';
echo '<div class="hr">--------</div>';
if ($done !== 1 && $hp > 0) {
    echo '<a href="wb.php?a=hit">打它一下（默认技能）</a> <a href="wb.php?a=hit&mode=hit">普攻磨血</a> <a href="wb.php?a=leave">离开</a><br>';
} else {
    echo '<a href="wb.php?a=leave">离开</a><br>';
}
echo '<div class="hr">--------</div>今日排行（前10）<br>';
foreach (wb_rank($day) as $i => $r) {
    echo ($i + 1) . '. ' . h($r['name']) . ' ' . number_format((int) $r['dmg']) . '<br>';
}
echo '<div class="hr">--------</div>掉落：金币/魔钻/传奇火龙蛋/烬焰7件套/神魂碎片（按排行邮件发放）<br>';
nav_line();
wap_end(false);
