<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

$u = require_login();

// 任务传送：留在行动画面
$go = (string) ($_GET['go'] ?? '');
if ($go !== '' && isset(locations()[$go])) {
    $u['loc'] = $go;
    user_save($u);
    header('Location: home.php');
    exit;
}
// 走一步：留在行动画面
$to = (string) ($_GET['to'] ?? '');
if ($to !== '' && isset(locations()[$to])) {
    $here0 = loc((string) $u['loc']);
    if (isset($here0['exits'][$to])) {
        if ($to === 'dsw_throne' && dsw_stage((int) $u['id']) < 5) {
            flash_set('王座大门纹丝不动。集齐四样信物（阶段5/5）才能推开。');
            header('Location: home.php');
            exit;
        }
        if ($to === 'abx_throne' && abx_stage((int) $u['id']) < 5) {
            flash_set('君王大门纹丝不动。集齐四样信物（阶段5/5）才能推开。');
            header('Location: home.php');
            exit;
        }
        if ($to === 'warfield') {
            $werr = warfield_can_enter($u);
            if ($werr !== '') {
                flash_set($werr);
                header('Location: home.php');
                exit;
            }
        }
        if ($to === 'silver_gate') {
            if ((int) $u['lv'] < 30) {
                flash_set('守卫拦住你：白银城只接待30级以上的冒险者，你现在' . (int) $u['lv'] . '级。');
                header('Location: home.php');
                exit;
            }
            if ((int) ($u['quest'] ?? 0) < 16) {
                flash_set('先完成第一章（镇长的信）再去白银城。');
                header('Location: home.php');
                exit;
            }
            if ((int) ($u['quest'] ?? 0) < 20) {
                $u['quest'] = 20;
            }
        }
        $u['loc'] = $to;
        user_save($u);
        if ($to === 'shop') {
            header('Location: shop.php');
            exit;
        }
        if ($to === 'camp') {
            header('Location: rest.php');
            exit;
        }
        header('Location: home.php');
        exit;
    }
}

if ((string) ($_GET['a'] ?? '') === 'pickup') {
    flash_set(ground_pickup((int) $u['id'], (int) ($_GET['id'] ?? 0)));
    header('Location: home.php');
    exit;
}
$flash = flash_get();
$here = loc((string) $u['loc']);
$cur = (string) $u['loc'];
$all = locations();

wap_start('行动');
echo '你是 <span class="gold">' . h($u['username']) . '</span>';
$jobs = jobs();
$jid = job_id_of($u);
if (isset($jobs[$jid])) {
    echo '·' . h($jobs[$jid]['name']);
}
echo '<br>';
echo '等级 ' . (int) $u['lv'] . '　';
echo '<span class="hp">生命 ' . (int) $u['hp'] . '/' . (int) $u['maxhp'] . '</span> <span style="color:#6cf">魔力 ' . (int) ($u['mp'] ?? 0) . '/' . (int) ($u['maxmp'] ?? 0) . '</span>　';
echo '<span class="gold">' . h(fmt_money((int) $u['gold'])) . '</span><br>';
echo '所在：' . h($here['name']);
if ($cur === 'camp') {
    echo '<br><a href="rest.php?a=sleep">靠着火堆睡觉（回满血蓝）</a>';
}
$grounds = ground_list($cur);
if ($grounds !== []) {
    echo '<div class="hr">--------</div>地上有东西（1分钟后消失）：<br>';
    foreach ($grounds as $g) {
        if (($g['kind'] ?? '') === 'equip') {
            $st = db()->prepare('SELECT name, quality FROM equips WHERE id=? AND uid=0 AND pos="ground"');
            $st->execute([(int) $g['ref']]);
            $e = $st->fetch();
            $gn = $e ? '【' . equip_shortname((string) $e['name']) . '】' : '【烂掉的装备】';
        } else {
            $gn = '【' . mat_name((string) $g['ref']) . '】x' . (int) $g['num'];
        }
        echo '·' . h($gn) . ' <a href="home.php?a=pickup&id=' . $g['id'] . '">拾取</a><br>';
    }
}
if ($flash !== '') {
    echo '<div class="warn">' . h($flash) . '</div>';
}
echo '<div class="hr">--------</div>';
echo '<div class="muted">' . h($here['desc']) . '</div>';
$npcLinks = [
    'smith' => [['barton', '老铁匠·巴顿']], 'supply' => [['martha', '杂货商·玛莎']],
    'market' => [['aileen', '药剂师·艾琳'], ['med_t', '卖药郎中']], 'wall' => [['carl', '守卫队长·卡尔']], 'tavern' => [['jack', '酒馆老板·老杰克']],
    'gate' => [['lily', '神秘少女·莉莉']], 'mansion' => [['augustus', '镇长·奥古斯都']], 'church' => [['thomas', '牧师·托马斯']],
    'town_sq' => [['alice', '修女·爱丽丝'], ['horse_t', '赛马人·老霍'], ['rank_t', '榜单老人']],
    'silver_gate' => [['sentry', '守卫·布雷']], 'sguild' => [['gwen', '会长·格温'], ['gmaster', '公会管理员·霍尔']],
    'silver_sq' => [['vera', '守渊人·薇拉'], ['horse_s', '赛马人·阿金'], ['rank_s', '榜单老人'], ['med_s', '卖药郎中']],
    'square' => [['horse_g', '赛马人·豆芽'], ['rank_g', '榜单老人'], ['med_g', '卖药郎中']],
    'guild' => [['greg', '公会接待·格雷']],
    'dsewer1' => [['reed', '守卫·雷德']], 'dsewer2' => [['lily2', '少女·莉莉']],
    'manor' => [['lord', '城主·瓦伦丁']],
    'noble' => [['guard_captain', '守卫队长']],
    'theater' => [['stringer', '牵线者'], ['spider', '断线人·阿蛛']],
    'farm' => [['grocer', '菜商·豆豆']],
    'avenue' => [['waldon', '线人·瓦尔顿'], ['rank_c', '榜单老人'], ['med_c', '卖药郎中']],
];
foreach ($npcLinks[$cur] ?? [] as [$npcId, $npcName]) {
    echo 'NPC【' . h($npcName) . '】：<a href="npc.php?who=' . h($npcId) . '">对话</a><br>';
}
if ($cur === 'bmine2' && (int) $u['quest'] >= 23) {
    echo 'NPC【使徒残响】：<a href="npc.php?who=apostle_echo">对话</a><br>';
}
if ($cur === 'sforest' && (int) $u['quest'] >= 24) {
    echo 'NPC【守护者残响】：<a href="npc.php?who=golem_echo">对话</a><br>';
}
if ($cur === 'baltar' && (int) $u['quest'] >= 26) {
    echo 'NPC【暗影莉莉】：<a href="npc.php?who=darklily">对话</a><br>';
}
$smiths = blacksmiths();
if (isset($smiths[$cur])) {
    echo '铁匠【' . h($smiths[$cur]) . '】在炉子边。<a href="smith.php">找他强化装备</a><br>';
}
echo '<div class="hr">--------</div>';
if (in_array($cur, dsw_maps(), true)) {
    $left = 1800 - (time() - (int) (mats_of((int) $u['id'])['dsw_enter'] ?? time()));
    $stg = dsw_stage((int) $u['id']);
    $need = [1 => '幽暗黏液×10（黑暗史莱姆）', 2 => '沼泽之核×10（沼泽史莱姆）', 3 => '腐泥之心×8（黑暗史莱姆）', 4 => '王座徽记×5（沼泽史莱姆）', 5 => '去王座大门，推开王座之间，杀霍克'][min(5, $stg)];
    echo '<div class="warn">黑暗沼泽：剩' . gmdate('i:s', max(0, $left)) . '　阶段' . $stg . '/5：' . h($need) . '</div>';
}
if (in_array($cur, abx_maps(), true)) {
    $left = 1800 - (time() - (int) (mats_of((int) $u['id'])['abx_enter'] ?? time()));
    $stg = abx_stage((int) $u['id']);
    $need = [1 => '蚀影尘×10（影蚀行者）', 2 => '影蚀徽记×10（腐影守卫）', 3 => '低语残章×8（深渊低语者）', 4 => '王座蚀印×5（影蚀巨像）', 5 => '去君王大门，推开君王王座，杀阿巴顿'][min(5, $stg)];
    echo '<div class="warn">影蚀深渊：剩' . gmdate('i:s', max(0, $left)) . '　阶段' . $stg . '/5：' . h($need) . '</div>';
}
if (in_array($cur, mx_maps(), true)) {
    $left = 2700 - (time() - (int) (mats_of((int) $u['id'])['mx_enter'] ?? time()));
    $stg = mx_stage((int) $u['id']);
    $need = [1 => '黏丝束×15（银丝蛛）', 2 => '茧壳碎片×15（茧守）', 3 => '织线梭×12（织线者）', 4 => '蛾翼磷粉×12（银丝蛾）', 5 => '守望之瞳×10（巢穴守望）', 6 => '育巢摇篮曲×8（育巢侍女）', 7 => '去母巢之心，杀缠丝之母'][min(7, $stg)];
    echo '<div class="warn">银丝母巢：剩' . gmdate('i:s', max(0, $left)) . '　阶段' . $stg . '/7：' . h($need) . '</div>';
}
if ($cur === 'warfield') {
    echo '<div class="warn">公会战进行中！你本周战功' . war_my_points((int) $u['id']) . '分。杀不同公会的人+1。</div>';
    $foes = war_enemies((int) $u['id']);
    if ($foes === []) {
        echo '<span class="muted">场上没有敌对公会的人，先埋伏着。</span><br>';
    }
    foreach ($foes as $f) {
        echo '·敌【' . h($f['username']) . '】' . (int) $f['lv'] . '级(' . h($f['gname'] ?? '') . ')血' . (int) $f['hp'] . ' <a href="fight.php?a=pvp&uid=' . $f['id'] . '">砍他</a><br>';
    }
    echo '<div class="hr">--------</div>【本周战功榜】<br>';
    foreach (war_rank(war_week()) as $i => $r) {
        echo ($i + 1) . '.' . h($r['username']) . '(' . h($r['gname'] ?? '') . ')' . (int) $r['points'] . '分<br>';
    }
}
$mailN = mail_unread((int) $u['id']);
if ($mailN > 0) {
    echo '<div class="warn">【邮件】你有' . $mailN . '封未读邮件！<a href="mail.php">查看邮件</a></div>';
}
quest_banner($u, 'home.php');
echo '<div class="hr">--------</div>';
echo '附近的人：<br>';
$nears = pres_close((int) $u['id']);
if ($nears === []) {
    echo '<span class="muted">只有风声。</span><br>';
}
foreach ($nears as $nu) {
    echo '·<a href="look.php?id=' . (int) $nu['id'] . '">' . h($nu['username']) . '</a>(' . (int) $nu['lv'] . '级' . h(job_of(['job' => (string) $nu['job']])['name']) . ')<br>';
}

// 打怪：在行动画面直接开打（3分钟一刷新，每种怪各20只，精英2只，BOSS独苗）
if ($here['monsters'] !== []) {
    echo '动手：<br>';
    spawn_tick($cur);
    $spawns = [];
    foreach (map_spawns($cur) as $s) {
        $spawns[$s['mid'] . '|' . $s['elite']] = (int) $s['num'];
    }
    foreach ($here['monsters'] as $mid) {
        $m = monsters()[$mid];
        $diff = monster_lv($mid) - (int) ($u['lv'] ?? 1);
        $tag = $diff <= -4 ? '经验衰减' : ($diff > 0 ? '越级+' . (int) min(50, $diff * 5) . '%' : '');
        $isBoss = ($mid === boss_of_map($cur));
        $left = $spawns[$mid . '|0'] ?? 0;
        if ($left <= 0) {
            echo '· ' . h($m['name']) . '[Lv' . monster_lv($mid) . ']' . ($isBoss ? '（BOSS剩' . gmdate('i:s', spawn_respawn_in($cur, $mid)) . '刷新）' : '（这种杀光了，剩' . gmdate('i:s', spawn_respawn_in($cur, $mid)) . '刷新，可打别的怪）') . '<br>';
            continue;
        }
        $gang = (!$isBoss && $left >= 6) ? ' / <a href="fight.php?a=start&m=' . h($mid) . '&n=6">群殴6只</a>' : '';
        echo '· ' . h($m['name']) . '[Lv' . monster_lv($mid) . ($tag !== '' ? '·' . $tag : '') . ']剩' . $left . '只 <a href="fight.php?a=start&m=' . h($mid) . '">打1只</a>' . $gang . '<br>';
    }
    foreach ($spawns as $key => $num) {
        [$emid, $eel] = explode('|', $key);
        if ((int) $eel !== 1 || !isset(monsters()[$emid])) {
            continue;
        }
        echo '·<b>' . h(monsters()[$emid]['name']) . '（精英）</b>[Lv' . monster_lv($emid) . ']剩' . $num . '只（经验×3，掉率更高） <a href="fight.php?a=start&m=' . h($emid) . '&elite=1">挑战</a><br>';
    }
    echo '<div class="hr">--------</div>';
}

// 走动：上下左右下一步
$dirs = exit_dirs($cur);
$step = function ($d) use ($dirs, $all) {
    if (!isset($dirs[$d])) {
        return '·';
    }
    $id = $dirs[$d];
    return '<a href="home.php?to=' . h($id) . '">' . h($d . $all[$id]['name']) . '</a>';
};
echo '走动：北' . $step('北') . ' 南' . $step('南') . ' 东' . $step('东') . ' 西' . $step('西');
if (isset($dirs['上']) || isset($dirs['下'])) {
    echo ' 上' . $step('上') . ' 下' . $step('下');
}
echo '<br><a href="map.php">看大地图</a>';
echo '<div class="hr">--------</div>';
if ((int) $u['potion'] > 0) {
    echo '<a href="bag.php?a=drink">喝药 (' . (int) $u['potion'] . ')</a><br>';
}
echo '<a href="status.php">查看自身</a><br>';
echo '<a href="bag.php">打开背包</a>';
nav_line();
wap_end(false);
