<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

$u = require_login();
sin_sync_hp((int) $u['id']);
// 残鳞兜底：不在战斗中却残留了最大生命削减，恢复它
if (job_id_of($u) === 'summoner' && empty($_SESSION['battle']) && (int) (cflags((int) $u['id'])['rs_base'] ?? 0) > 0) {
    summoner_legend_rescale_clear($u);
    $u = user_by_id((int) $u['id']);
}
$a = (string) ($_GET['a'] ?? '');
$mid = (string) ($_GET['m'] ?? '');
$allm = monsters();

if ((int) $u['hp'] <= 0 && peak_arena_of((string) ($u['loc'] ?? '')) !== '') {
    peak_forfeit((int) $u['id']);
    $u['hp'] = 1;
    $u['loc'] = 'square';
    user_save($u);
    unset($_SESSION['battle']);
    unset($_SESSION['pvp']);
    unset($_SESSION['peak']);
    flash_set('你在巅峰战场倒下，丧失活动资格，被抬回灰雾村广场。');
    header('Location: home.php');
    exit;
}

if ((int) $u['hp'] <= 0) {
    $pen = death_penalty($u);
    if (in_array((string) ($u['loc'] ?? ''), dsw_maps(), true)) {
        $u['loc'] = 'town_sq';
        $pen .= '副本中倒下，直接传回白石镇广场。';
    }
    if (in_array((string) ($u['loc'] ?? ''), abx_maps(), true)) {
        $u['loc'] = 'silver_sq';
        $pen .= '深渊中倒下，直接传回白银广场。';
    }
    if (in_array((string) ($u['loc'] ?? ''), mx_maps(), true)) {
        $u['loc'] = 'avenue';
        $pen .= '母巢中倒下，直接传回中央大道。';
    }
    if (in_array((string) ($u['loc'] ?? ''), gy_maps(), true)) {
        $u['loc'] = 'square';
        $pen .= '在罪渊中倒下，被渡罪拖了回来。';
    }
    if (in_array((string) ($u['loc'] ?? ''), gf_maps(), true)) {
        $u['loc'] = 'god_hall';
        unset($_SESSION['battle']);
        $pen .= '你在堕天阶坠落，被云接住，送回圣都。';
    }
    user_save($u);
    unset($_SESSION['battle']);
    flash_set('你被拖回营地。' . $pen);
    header('Location: home.php');
    exit;
}

if ($a === 'start' && isset($allm[$mid])) {
    $here = loc((string) $u['loc']);
    if (!in_array($mid, $here['monsters'], true)) {
        flash_set('这里没有那种东西。');
        header('Location: home.php');
        exit;
    }
    $n = (int) ($_GET['n'] ?? 1);
    if ($n !== 6) {
        $n = 1;
    }
    $elite = (int) ($_GET['elite'] ?? 0) === 1 ? 1 : 0;
    if ($mid === boss_of_map((string) $u['loc'])) {
        $n = 1;
    }
    $m = $allm[$mid];
    if ($mid === 'echo_rayne' && (int) ($u['quest'] ?? 0) > 14) {
        $m = ['name' => '深渊回响·雷恩', 'hp' => 2000, 'atk' => 85, 'exp' => 2200, 'gold' => 1000];
    }
    $m = scale_monster($m, $mid, $elite);
    if ($elite === 1 && $mid === boss_of_map((string) $u['loc'])) {
        $elite = 0;
    }
    spawn_tick((string) $u['loc'], $mid);
    $got = spawn_take((string) $u['loc'], $mid, $elite, $n);
    if ($got <= 0) {
        $wait = spawn_respawn_in((string) $u['loc'], $mid);
        flash_set($elite === 1 ? '这只精英已经被宰了，蹲刷新吧。' : '这种怪被杀光了，' . ($wait > 0 ? '剩' . gmdate('i:s', $wait) . '刷新。' : '马上刷新。') . '可以打这张图别的怪。');
        header('Location: home.php');
        exit;
    }
    $n = min($n, $got);
    if ($elite === 1) {
        $m['name'] .= '（精英）';
        $m['exp'] = (int) $m['exp'] * 3;
        $m['gold'] = (int) $m['gold'] * 2;
    }
    $old = $_SESSION['battle'] ?? null;
    if (is_array($old) && ($old['id'] ?? '') === $mid && (int) ($old['left'] ?? 0) > 0 && (int) ($old['elite'] ?? 0) === $elite) {
        $old['num'] = (int) ($old['num'] ?? 0) + $n;
        $old['left'] = (int) ($old['left'] ?? 0) + $n;
        $old['log'] = '又有' . $n . '只' . $m['name'] . '加入围殴！(场上剩' . $old['left'] . '只)';
        $_SESSION['battle'] = $old;
        header('Location: fight.php');
        exit;
    }
    $_SESSION['battle'] = [
        'id' => $mid,
        'name' => $m['name'],
        'hp' => $m['hp'],
        'maxhp' => $m['hp'],
        'atk' => $m['atk'],
        'exp' => $m['exp'],
        'gold' => $m['gold'],
        'log' => $n > 1 ? '你被' . $n . '只' . $m['name'] . '围住了！它们会一起打你。' : '你撞上了' . $m['name'] . '。',
        'num' => $n,
        'left' => $n,
        'wexp' => 0,
        'wgold' => 0,
        'drops' => [],
        'elite' => $elite,
        'live' => true,
        'last' => time(),
    ];
    header('Location: fight.php');
    exit;
}

if ($a === 'ambushfight') {
    $st = $_SESSION['ambush'] ?? null;
    $cfg = is_array($st) ? (ambush_npcs()[(string) ($st['npc'] ?? '')] ?? null) : null;
    if ($cfg === null) {
        unset($_SESSION['ambush']);
        flash_set('偷袭目标不见了。');
        header('Location: home.php');
        exit;
    }
    wap_start('偷袭');
    echo '敌【' . h($cfg['name']) . '】' . (int) $cfg['lv'] . '级<br>';
    echo '<div class="hp">敌生命 ' . (int) $st['hp'] . '/' . (int) $st['maxhp'] . '</div>';
    echo '<div class="hp">你 ' . (int) $u['hp'] . '/' . (int) $u['maxhp'] . '</div>';
    echo '<div class="hr">--------</div>';
    echo '<a href="fight.php?a=ambush_hit">砍他一刀</a> <a href="fight.php?a=run">撤</a>';
    nav_line();
    wap_end(false);
    exit;
}
if ($a === 'ambush_hit') {
    $st = $_SESSION['ambush'] ?? null;
    $npc = is_array($st) ? (string) ($st['npc'] ?? '') : '';
    $cfg = ambush_npcs()[$npc] ?? null;
    if ($cfg === null) {
        unset($_SESSION['ambush']);
        flash_set('偷袭目标不见了。');
        header('Location: home.php');
        exit;
    }
    if ((int) $u['hp'] <= 0) {
        unset($_SESSION['ambush']);
        $pen = death_penalty($u);
        user_save($u);
        flash_set('偷袭失败，被反杀。' . $pen);
        header('Location: home.php');
        exit;
    }
    [$msg, $res] = ambush_round($u, $st);
    $_SESSION['ambush'] = $st;
    if ($res === 'npc_dead') {
        unset($_SESSION['ambush']);
        $rw = ambush_reward((int) $u['id'], $npc);
        $u = user_by_id((int) $u['id']);
        user_save($u);
        flash_set($msg . ' ' . $rw);
        header('Location: home.php');
        exit;
    }
    user_save($u);
    if ($res === 'player_dead') {
        unset($_SESSION['ambush']);
        $pen = death_penalty($u);
        user_save($u);
        flash_set($msg . ' ' . $pen);
        header('Location: home.php');
        exit;
    }
    flash_set($msg);
    header('Location: fight.php?a=ambushfight');
    exit;
}
$b = $_SESSION['battle'] ?? null;
if (!is_array($b)) {
    header('Location: home.php');
    exit;
}

$log = (string) $b['log'];

$isAjax = isset($_GET['ajax']) || (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'fetch');

if ($a === 'hit' || $a === 'skill' || $a === 'tick') {
    $b['last'] = time();
    $mode = $a === 'hit' ? 'hit' : 'skill';
    $r = battle_round($u, $b, $mode);
    if ($r['status'] === 'fight') {
        if ($isAjax) {
            header('Content-Type: application/json; charset=utf-8');
            $bb = $_SESSION['battle'];
            echo json_encode([
                'done' => false,
                'log' => $r['log'],
                'uhp' => (int) $u['hp'], 'umax' => (int) $u['maxhp'],
                'ump' => (int) ($u['mp'] ?? 0), 'umaxmp' => (int) ($u['maxmp'] ?? 0),
                'bhp' => (int) $bb['hp'], 'bmax' => (int) $bb['maxhp'],
                'bname' => (string) $bb['name'],
                'left' => (int) ($bb['left'] ?? 1), 'num' => (int) ($bb['num'] ?? 1),
                'histhtml' => array_map('battle_log_html', array_slice((array) ($bb['history'] ?? []), -3)),
                'pet' => (function () use ($u) { $ap = active_pet((int) ($u['id'] ?? 0)); if (!$ap) { return ''; } $st = pet_stats($ap); return pet_species()[$ap['species']]['name'] . ' ' . min((int) $ap['hp'], $st['maxhp']) . '/' . $st['maxhp']; })(),
                'summons' => summon_status_list(),
            ]);
            exit;
        }
        header('Location: fight.php');
        exit;
    }
    flash_set($r['flash']);
    if ($isAjax) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['done' => true, 'log' => $r['log'], 'redirect' => 'home.php', 'flash' => $r['flash']]);
        exit;
    }
    header('Location: home.php');
    exit;
}
// 单轮战斗逻辑见 includes/game.php 的 battle_round()，切页后台推进见 background_battle()

if ($a === 'run') {
    unset($_SESSION['battle']);
    unset($_SESSION['pvp']);
    unset($_SESSION['ambush']);
    summoner_legend_rescale_clear($u);
    flash_set('你撤了。没人笑话你，因为笑话你的多半已经死了。');
    header('Location: home.php');
    exit;
}

function sin_check(array $u, int $tuid): array
{
    $t = user_by_id($tuid);
    if (!$t) {
        return [null, '那个人不见了。'];
    }
    if ((string) ($t['loc'] ?? '') !== (string) ($u['loc'] ?? '')) {
        return [null, '对方不在这个区域。'];
    }
    if (($t['zone'] ?? 'z1') !== (string) ($u['zone'] ?? 'z1')) {
        return [null, '跨大区不能打。'];
    }
    if ((int) $t['id'] === (int) $u['id']) {
        return [null, '你不能打自己。'];
    }
    if ((int) ($t['hp'] ?? 0) <= 0) {
        return [null, '对方已倒下。'];
    }
    if ((int) ($u['hp'] ?? 0) <= 0) {
        return [null, '你先回满血再来。'];
    }
    if (sin_of((int) $t['id']) < 100) {
        return [null, '对方罪值未满100，不能强制PK。'];
    }
    return [$t, ''];
}

if ($a === 'sin') {
    [$t, $err] = sin_check($u, (int) ($_GET['uid'] ?? 0));
    if ($err !== '') {
        flash_set($err);
        header('Location: home.php');
        exit;
    }
    $_SESSION['sin'] = ['tuid' => (int) $t['id']];
    header('Location: fight.php?a=sinfight');
    exit;
}
if ($a === 'sin_hit') {
    $pk = $_SESSION['sin'] ?? null;
    [$t, $err] = sin_check($u, (int) ($pk['tuid'] ?? 0));
    if ($err !== '') {
        unset($_SESSION['sin']);
        flash_set($err);
        header('Location: home.php');
        exit;
    }
    [$pd] = pvp_hit_base($u, $t);
    // 浮动管线（罪技PK）：浮动 → 档位 → 暴击
    [$pd, $pdTag] = player_dmg_pipeline($u, $pd);
    $t['hp'] = (int) $t['hp'] - $pd;
    if ((int) $t['hp'] <= 0) {
        // 罪值击杀：对方随机最多2件可交易装备/道具掉在原地（1分钟后消失）
        $loc = (string) ($t['loc'] ?? (string) $u['loc']);
        $loot = sin_drop_loot((int) $t['id'], $loc);
        $t = user_by_id((int) $t['id']);
        $t['hp'] = 1;
        $t['loc'] = 'camp';
        user_save($t);
        unset($_SESSION['sin']);
        user_save($u);
        flash_set('你结果了恶贯满盈的【' . $t['username'] . '】！' . ($loot !== [] ? '掉落：' . implode('、', $loot) . '（掉在' . loc($loc)['name'] . '，1分钟后消失）。' : '他身上没有可掉的东西。') . '他被抬回营地。');
        header('Location: home.php');
        exit;
    }
    user_save($t);
    [$md] = pvp_hit_base($t, $u);
    // 对方回敬也走浮动管线（用对方自己的暴击/精准）
    [$md, $mdTag] = player_dmg_pipeline($t, $md);
    $u['hp'] = (int) $u['hp'] - $md;
    user_save($u);
    if ((int) $u['hp'] <= 0) {
        unset($_SESSION['sin']);
        $pen = death_penalty($u);
        user_save($u);
        flash_set('你被【' . $t['username'] . '】反杀了。' . $pen);
        header('Location: home.php');
        exit;
    }
    flash_set(pvp_verb($u) . '，造成' . $pd . '点' . ($pdTag !== '' ? '(' . $pdTag . ')' : '') . '，对方回敬' . $md . '点' . ($mdTag !== '' ? '(' . $mdTag . ')' : '') . '。');
    header('Location: fight.php?a=sinfight');
    exit;
}
if ($a === 'sinfight') {
    $pk = $_SESSION['sin'] ?? null;
    [$t, $err] = sin_check($u, (int) ($pk['tuid'] ?? 0));
    if ($err !== '') {
        unset($_SESSION['sin']);
        flash_set($err);
        header('Location: home.php');
        exit;
    }
    wap_start('强制PK');
    echo '敌【' . h($t['username']) . '】' . (int) $t['lv'] . '级 罪值' . sin_of((int) $t['id']) . '<br>';
    echo '<div class="hp">敌生命 ' . (int) $t['hp'] . '/' . (int) $t['maxhp'] . '</div>';
    echo '<div class="hp">你 ' . (int) $u['hp'] . '/' . (int) $u['maxhp'] . '</div>';
    echo '<div class="hr">--------</div>';
    echo '<a href="fight.php?a=sin_hit">砍他一刀</a> <a href="fight.php?a=run">撤</a>';
    nav_line();
    wap_end(false);
    exit;
}

function pvp_check(array $u, int $tuid): array
{    if (!war_window_open()) {
        return [null, '战场没开（周六日20点）。'];
    }
    if ((string) ($u['loc'] ?? '') !== 'warfield') {
        return [null, '你不在战场。'];
    }
    $t = user_by_id($tuid);
    if (!$t) {
        return [null, '那个人不见了。'];
    }
    if ((string) ($t['loc'] ?? '') !== 'warfield' || (int) $t['hp'] <= 0) {
        return [null, '对方不在战场或已倒下。'];
    }
    if (($t['zone'] ?? 'z1') !== (string) ($u['zone'] ?? 'z1')) {
        return [null, '跨大区不能打。'];
    }
    $g1 = my_guild((int) ($u['id'] ?? 0));
    $g2 = my_guild($tuid);
    if (!$g1 || !$g2 || (int) $g1['id'] === (int) $g2['id']) {
        return [null, '只能打不同公会的人。'];
    }
    return [$t, ''];
}

if ($a === 'pvp') {
    [$t, $err] = pvp_check($u, (int) ($_GET['uid'] ?? 0));
    if ($err !== '') {
        flash_set($err);
        header('Location: home.php');
        exit;
    }
    $_SESSION['pvp'] = ['tuid' => (int) $t['id']];
    header('Location: fight.php?a=pvpfight');
    exit;
}
if ($a === 'pvp_hit') {
    $pvp = $_SESSION['pvp'] ?? null;
    [$t, $err] = pvp_check($u, (int) ($pvp['tuid'] ?? 0));
    if ($err !== '') {
        unset($_SESSION['pvp']);
        flash_set($err);
        header('Location: home.php');
        exit;
    }
    [$pd] = pvp_hit_base($u, $t);
    // 浮动管线：浮动 → 档位 → 暴击
    [$pd, $pdTag] = player_dmg_pipeline($u, $pd);
    $t['hp'] = (int) $t['hp'] - $pd;
    if ((int) $t['hp'] <= 0) {
        $t['hp'] = 1;
        $t['loc'] = 'silver_sq';
        user_save($t);
        unset($_SESSION['pvp']);
        war_add_point((int) $u['id']);
        user_save($u);
        flash_set('你一刀结果了【' . $t['username'] . '】！战功+1（本周' . war_my_points((int) $u['id']) . '分）。他被抬回白银广场。');
        header('Location: home.php');
        exit;
    }
    user_save($t);
    [$md] = pvp_hit_base($t, $u);
    // 对方回敬也走浮动管线（用对方自己的暴击/精准）
    [$md, $mdTag] = player_dmg_pipeline($t, $md);
    $u['hp'] = (int) $u['hp'] - $md;
    user_save($u);
    if ((int) $u['hp'] <= 0) {
        unset($_SESSION['pvp']);
        $pen = death_penalty($u);
        user_save($u);
        flash_set('你被【' . $t['username'] . '】反杀了。' . $pen);
        header('Location: home.php');
        exit;
    }
    flash_set('你砍' . $pd . '点' . ($pdTag !== '' ? '(' . $pdTag . ')' : '') . '，对方回敬' . $md . '点' . ($mdTag !== '' ? '(' . $mdTag . ')' : '') . '。');
    header('Location: fight.php?a=pvpfight');
    exit;
}
if ($a === 'pvpfight') {
    $pvp = $_SESSION['pvp'] ?? null;
    [$t, $err] = pvp_check($u, (int) ($pvp['tuid'] ?? 0));
    if ($err !== '') {
        unset($_SESSION['pvp']);
        flash_set($err);
        header('Location: home.php');
        exit;
    }
    wap_start('公会战');
    echo '敌【' . h($t['username']) . '】' . (int) $t['lv'] . '级<br>';
    echo '<div class="hp">敌生命 ' . (int) $t['hp'] . '/' . (int) $t['maxhp'] . '</div>';
    echo '<div class="hp">你 ' . (int) $u['hp'] . '/' . (int) $u['maxhp'] . '</div>';
    echo '<div class="hr">--------</div>';
    echo '<a href="fight.php?a=pvp_hit">砍他一刀</a> <a href="fight.php?a=run">撤</a>';
    nav_line();
    wap_end(false);
    exit;
}
function peak_check(array $u, int $tuid): array
{
    $arena = peak_arena_of((string) ($u['loc'] ?? ''));
    if ($arena === '') {
        return [null, '你不在巅峰战场。'];
    }
    $t = user_by_id($tuid);
    if (!$t) {
        return [null, '那个人不见了。'];
    }
    if (peak_arena_of((string) ($t['loc'] ?? '')) !== $arena || (int) $t['hp'] <= 0) {
        return [null, '对方不在战场或已倒下。'];
    }
    if (($t['zone'] ?? 'z1') !== (string) ($u['zone'] ?? 'z1')) {
        return [null, '跨大区不能打。'];
    }
    $st = db()->prepare('SELECT alive FROM peak_join WHERE uid=? AND day=?');
    $st->execute([(int) $tuid, peak_day()]);
    $row = $st->fetch();
    if (!$row || (int) $row['alive'] !== 1) {
        return [null, '对方已丧失资格。'];
    }
    return [$t, ''];
}

if ($a === 'peak_zone') {
    // 圈内/圈外切换：冲进圈外可抢补给但每分钟重伤；回圈内安全但没补给。
    if (peak_arena_of((string) ($u['loc'] ?? '')) === '' || !peak_open()) {
        flash_set('你不在巅峰战场，或战场没开。');
        header('Location: home.php');
        exit;
    }
    $z = (int) ($_GET['z'] ?? 1) === 0 ? 2 : 1; // 0=冲出安全区→圈外(2)；1=退回圈内
    peak_set_zone((int) $u['id'], $z);
    flash_set($z === 1 ? '你撤回安全区，喘口气——但补给也跟你无缘。' : '你冲进安全区外的废墟，乱流开始灼烧，但废墟里有补给。');
    header('Location: home.php');
    exit;
}
if ($a === 'peak') {
    [$t, $err] = peak_check($u, (int) ($_GET['uid'] ?? 0));
    if ($err !== '') {
        flash_set($err);
        header('Location: home.php');
        exit;
    }
    $_SESSION['peak'] = ['tuid' => (int) $t['id']];
    header('Location: fight.php?a=peakfight');
    exit;
}
if ($a === 'peak_hit') {
    $pk = $_SESSION['peak'] ?? null;
    [$t, $err] = peak_check($u, (int) ($pk['tuid'] ?? 0));
    if ($err !== '') {
        unset($_SESSION['peak']);
        flash_set($err);
        header('Location: home.php');
        exit;
    }
    [$pd] = pvp_hit_base($u, $t);
    // 缩圈增益：把捡到的临时增益转成这次对决的伤害/减伤
    $buf = function_exists('peak_buff_take') ? peak_buff_take((int) $u['id']) : ['atk_pct' => 0.0, 'dr_pct' => 0.0, 'used' => []];
    if ($buf['atk_pct'] > 0) { $pd = (int) round($pd * (1 + $buf['atk_pct'] / 100)); }
    // 浮动管线：浮动 → 档位 → 暴击
    [$pd, $pdTag] = player_dmg_pipeline($u, $pd);
    $t['hp'] = (int) $t['hp'] - $pd;
    if ((int) $t['hp'] <= 0) {
        $t['hp'] = 1;
        $t['loc'] = 'square';
        user_save($t);
        peak_forfeit((int) $t['id']);
        unset($_SESSION['peak']);
        if ($buf['used'] !== [] && function_exists('peak_buff_consume')) { peak_buff_consume((int) $u['id']); }
        user_save($u);
        flash_set('你一刀结果了【' . $t['username'] . '】！他被抬出战场，丧失资格。');
        header('Location: home.php');
        exit;
    }
    user_save($t);
    [$md] = pvp_hit_base($t, $u);
    // 对方回敬也走浮动管线（用对方自己的暴击/精准）
    [$md, $mdTag] = player_dmg_pipeline($t, $md);
    if ($buf['dr_pct'] > 0) { $md = max(1, (int) round($md * (1 - $buf['dr_pct'] / 100))); }
    if ($buf['used'] !== [] && function_exists('peak_buff_consume')) { peak_buff_consume((int) $u['id']); }
    $u['hp'] = (int) $u['hp'] - $md;
    user_save($u);
    if ((int) $u['hp'] <= 0) {
        unset($_SESSION['peak']);
        peak_forfeit((int) $u['id']);
        $u['hp'] = 1;
        $u['loc'] = 'square';
        user_save($u);
        flash_set('你被【' . $t['username'] . '】反杀了，丧失资格，被抬回灰雾村广场。');
        header('Location: home.php');
        exit;
    }
    flash_set('你砍' . $pd . '点' . ($pdTag !== '' ? '(' . $pdTag . ')' : '') . '，对方回敬' . $md . '点' . ($mdTag !== '' ? '(' . $mdTag . ')' : '') . '。');
    header('Location: fight.php?a=peakfight');
    exit;
}
if ($a === 'peakfight') {
    $pk = $_SESSION['peak'] ?? null;
    [$t, $err] = peak_check($u, (int) ($pk['tuid'] ?? 0));
    if ($err !== '') {
        unset($_SESSION['peak']);
        flash_set($err);
        header('Location: home.php');
        exit;
    }
    wap_start('巅峰决斗');
    if (function_exists('peak_storm_bar')) { echo peak_storm_bar((int) $u['id']); }
    echo '敌【' . h($t['username']) . '】' . (int) $t['lv'] . '级<br>';
    echo '<div class="hp">敌生命 ' . (int) $t['hp'] . '/' . (int) $t['maxhp'] . '</div>';
    echo '<div class="hp">你 ' . (int) $u['hp'] . '/' . (int) $u['maxhp'] . '</div>';
    echo '<div class="hr">--------</div>';
    echo '<a href="fight.php?a=peak_hit">砍他一刀</a> <a href="fight.php?a=run">撤(仍在战场)</a>';
    nav_line();
    wap_end(false);
    exit;
}
if ($a === 'answer') {
    $bb = $_SESSION['battle'] ?? null;
    if (is_array($bb) && ($bb['id'] ?? '') === 'abyss_eye') {
        unset($_SESSION['battle']);
        $pen = death_penalty($u);
        user_save($u);
        flash_set('你回答了。你的影子站起来，穿上了你。' . $pen);
    }
    header('Location: home.php');
    exit;
}
if ($a === 'silence') {
    $bb = $_SESSION['battle'] ?? null;
    if (is_array($bb) && ($bb['id'] ?? '') === 'abyss_eye') {
        $bb['eye_weak'] = true;
        $bb['log'] = '你保持沉默。深渊之眼动摇了，它打你-10%。';
        $bb['last'] = time();
        $_SESSION['battle'] = $bb;
    }
    header('Location: fight.php');
    exit;
}

if ($a === 'drink') {
    $pm = (string) ($_GET['m'] ?? 'hp_pct10');
    $msg = drink_pct_potion($u, $pm);
    $u = user_by_id((int) ($u['id'] ?? 0)) ?: $u;
    $b['log'] = $msg;
    // 战斗页渲染的是 history 数组（不是 log），喝药消息必须进 history，否则看着像没喝
    if (!isset($b['history']) || !is_array($b['history'])) {
        $b['history'] = [];
    }
    $b['history'][] = $msg;
    $b['history'] = array_slice($b['history'], -20);
    $b['last'] = time();
    $_SESSION['battle'] = $b;
    $isAjaxDrink = isset($_GET['ajax']) || (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'fetch');
    if ($isAjaxDrink) {
        header('Content-Type: application/json; charset=utf-8');
        $bb = $_SESSION['battle'];
        $ap = active_pet((int) ($u['id'] ?? 0));
        echo json_encode([
            'done' => false,
            'log' => $msg,
            'uhp' => (int) $u['hp'], 'umax' => (int) $u['maxhp'],
            'ump' => (int) ($u['mp'] ?? 0), 'umaxmp' => (int) ($u['maxmp'] ?? 0),
            'bhp' => (int) $bb['hp'], 'bmax' => (int) $bb['maxhp'],
            'bname' => (string) $bb['name'],
            'left' => (int) ($bb['left'] ?? 1), 'num' => (int) ($bb['num'] ?? 1),
            'histhtml' => array_map('battle_log_html', array_slice((array) ($bb['history'] ?? []), -3)),
            'mats' => array_intersect_key(mats_of((int) ($u['id'] ?? 0)), array_flip(array_keys(pct_potions()))),
            'pet' => $ap ? (pet_species()[$ap['species']]['name'] . ' ' . min((int) $ap['hp'], pet_stats($ap)['maxhp']) . '/' . pet_stats($ap)['maxhp']) : '',
            'summons' => summon_status_list(),
        ]);
        exit;
    }
    header('Location: fight.php');
    exit;
}

wap_start('交手');
$history = $b['history'] ?? [$log];
$num = (int) ($b['num'] ?? 1);
$left = (int) ($b['left'] ?? 1);
echo '<div>敌人：' . h((string) $b['name']) . ($num > 1 ? '（共' . $num . '只，剩' . $left . '）' : '') . '</div>';
echo '<div class="hp" id="bhp">敌生命 ' . (int) $b['hp'] . '/' . (int) $b['maxhp'] . '</div>';
if ($num > 1) {
    echo '<div class="warn" id="bleft">剩余 ' . $left . '/' . $num . '只，存活怪每回合一起打你！</div>';
}
$fsk = active_skill((int) ($u['id'] ?? 0), job_id_of($u));
echo '<div class="hp" id="uhp">你 ' . (int) $u['hp'] . '/' . (int) $u['maxhp'] . '</div>';
echo '<div style="color:#6cf" id="ump">魔力 ' . (int) ($u['mp'] ?? 0) . '/' . (int) ($u['maxmp'] ?? 0) . ($fsk ? '·' . h($fsk['name']) . $fsk['mp'] . '蓝' : '·未学技能') . ' <a href="skills.php">换技能</a></div>';
$fpet = active_pet((int) ($u['id'] ?? 0));
if ($fpet) {
    $fpst = pet_stats($fpet);
    echo '<div style="color:#9c9" id="ppet">' . h(pet_species()[$fpet['species']]['name']) . ' ' . min((int) $fpet['hp'], $fpst['maxhp']) . '/' . $fpst['maxhp'] . '</div>';
} else {
    echo '<div style="color:#9c9" id="ppet"></div>';
}
// 召唤物：像宠物一样显示血量状态
$fsum = summon_status_list();
echo '<div style="color:#c9f" id="psum">';
foreach ($fsum as $si => $ss) {
    if ($si > 0) {
        echo '<br>';
    }
    echo h($ss['name']) . ' ' . $ss['hp'] . '/' . $ss['max'];
}
echo '</div>';
if (($b['id'] ?? '') === 'abyss_eye') {
    echo '<div class="warn">深渊之眼呼唤你的名字：“回答我……” <a href="fight.php?a=answer">回答</a> <a href="fight.php?a=silence">沉默</a></div>';
}
echo '<div class="muted">自动战斗中：每1秒默认放[' . h(job_of($u)['skill']) . ']，战报每2秒刷新。切到别的画面战斗也会在后台继续。</div>';
echo '<div class="hr">--------</div>';
echo '<div id="blog">';
foreach (array_slice($history, -3) as $h) {
    echo battle_log_html((string) $h) . '<div class="hr" style="margin:4px 0;opacity:.6">--------</div>';
}
echo '</div>';
echo '<div class="hr">--------</div>';
// 战斗中手动喝药
$pmatsDrink = mats_of((int) $u['id']);
$drinkBtns = [];
foreach (pct_potions() as $pmid => $pt) {
    $have = (int) ($pmatsDrink[$pmid] ?? 0);
    if ($have <= 0) {
        continue;
    }
    $drinkBtns[] = '<button type="button" class="drinkBtn" data-mid="' . h($pmid) . '">喝' . h($pt['name']) . '(+' . $pt['pct'] . '%' . ($pt['stat'] === 'mp' ? '蓝' : '血') . '×<span id="pm_' . h($pmid) . '">' . $have . '</span>)</button>';
}
if ($drinkBtns !== []) {
    echo '<div class="muted" id="drinkBar">' . implode(' ', $drinkBtns) . '</div>';
} else {
    echo '<div class="muted">没带药。<a href="shop.php">黑市</a>或背包里买。</div>';
}
echo '<button id="pauseBtn" type="button">暂停</button> ';
echo '<a href="fight.php?a=run">撤</a>';
nav_line();
echo '<script>
let paused=false;
document.getElementById("pauseBtn").onclick=()=>{paused=!paused;document.getElementById("pauseBtn").innerText=paused?"继续":"暂停";};
// 轮与轮之间用细分隔线隔开，内容已经是服务端排版好的 HTML，直接 innerHTML
function renderHist(arr){return arr.join("<div class=\"hr\" style=\"margin:4px 0;opacity:.6\">--------</div>");}
document.querySelectorAll(".drinkBtn").forEach(function(btn){
  btn.onclick=async()=>{
    const mid=btn.getAttribute("data-mid");
    btn.disabled=true;
    try{
      const r=await fetch("fight.php?a=drink&m="+encodeURIComponent(mid)+"&ajax=1",{headers:{"X-Requested-With":"fetch"},credentials:"same-origin"});
      const d=await r.json();
      if(!d.done){
        document.getElementById("uhp").innerText="我"+d.uhp+"/"+d.umax;
        if(d.ump!==undefined){ const me=document.getElementById("ump"); if(me) me.innerText="魔力 "+d.ump+"/"+d.umaxmp; }
        if(d.histhtml&&d.histhtml.length){
          document.getElementById("blog").innerHTML=renderHist(d.histhtml);
        }
        if(d.mats){
          for(const k in d.mats){
            const el=document.getElementById("pm_"+k);
            if(el) el.innerText=d.mats[k];
            if((+d.mats[k])<=0){
              document.querySelectorAll(".drinkBtn[data-mid=\""+k+"\"]").forEach(function(x){x.disabled=true;});
            }
          }
        }
      }
    }catch(e){}
    btn.disabled=false;
  };
});
// 每2秒打一轮并同步刷新血量+战报（同一tick，无延迟）
setInterval(async ()=>{
  if(paused||document.hidden) return;
  try{
    const r=await fetch("fight.php?a=tick&ajax=1",{headers:{"X-Requested-With":"fetch"},credentials:"same-origin"});
    const d=await r.json();
    if(d.done){ location.href=d.redirect||"home.php"; return; }
    document.getElementById("uhp").innerText="我"+d.uhp+"/"+d.umax;
    if(d.ump!==undefined){ const me=document.getElementById("ump"); if(me) me.innerText="魔力 "+d.ump+"/"+d.umaxmp; }
    if(d.pet!==undefined){ const pe=document.getElementById("ppet"); if(pe) pe.innerText=d.pet; }
    if(d.summons!==undefined){ const se=document.getElementById("psum"); if(se) se.innerHTML=d.summons.map(function(s){return s.name+" "+s.hp+"/"+s.max;}).join("<br>"); }
    document.getElementById("bhp").innerText="敌生命"+d.bhp+"/"+d.bmax;
    if(d.num>1){ const bl=document.getElementById("bleft"); if(bl) bl.innerText="剩余 "+d.left+"/"+d.num+"只，存活怪每回合一起打你！"; }
    const box=document.getElementById("blog");
    if(d.histhtml&&d.histhtml.length){box.innerHTML=renderHist(d.histhtml);}else{box.innerHTML=(box.innerHTML+"<br>"+String(d.log).replace(/</g,"&lt;")).split("<br>").slice(-3).join("<br>");}
    window.scrollTo(0,document.body.scrollHeight);
  }catch(e){}
},2000);
</script>';
echo '<noscript><meta http-equiv="refresh" content="6;url=fight.php?a=skill"></noscript>';
wap_end(false);
