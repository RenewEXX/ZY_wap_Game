<?php
declare(strict_types=1);

// 竞技场：异步数值对撞，按大区分赛区。每周一05:00换赛季，每日05:00结算奖励。

function arena_season(): string
{
    // 以周一为赛季起点
    $t = time();
    $monday = strtotime('monday this week 05:00', $t);
    if ($t < $monday) {
        $monday = strtotime('monday last week 05:00', $t);
    }
    return date('Y-m-d', $monday);
}

function arena_day(): string
{
    return (string) arena_day_idx();
}

function arena_day_idx(): int
{
    // mats.num只能存整数，用天序号记挑战/商店周期
    $t = time();
    $d = strtotime(date('Y-m-d') . ' 05:00');
    if ($t < $d) {
        $t -= 86400;
    }
    return intdiv(strtotime(date('Y-m-d', $t) . ' 05:00'), 86400);
}

// 竞技场假人名：模仿真实游戏排行榜里玩家的昵称习惯。
// 观察到的真实构词模式（来自中文手游/端游常见昵称）：
//   1) 汉语网感短语：「春树与小树」这种"两个字+与+两个字"
//   2) 拼音/英文 handle + 符号：ezerson. / luna_01 / k-47 / 乄
//   3) 词 + 尊称：路亚先生- / 南方姑娘 / 老王
//   4) 纯随机数与字母串：88级的小号、Aa12345
//   5) 自嘲/日常：又菜又爱玩、不想上班、打工人
// 刻意不抄具体真人ID，而是按上述模式组合，避免把真实玩家的身份搬进假人表。
function arena_name_pool(): array
{
    $real = [
        // —— 「A与B」式网名 ——
        '春树与小树', '山川与湖海', '左手与右手', '小满与立秋', '白酒与枸杞',
        '你与风', '我与他', '鸡与鹅', '钱与命', '刀与剑', '酒与烟', '猫与狗',
        '山与海', '光与影', '梦与醒', '生与死',
        // —— 拼音/英文 handle + 符号 ——
        'ezerson.', 'luna_01', 'k47', 'nyx.', 'zzZ_', 'Aether', 'moonlit',
        'sora.', 'kirito_02', 'nekomiya', 'Rem.', 'Aqua..', 'Tsuki-', 'vex',
        'n0va', 'Echo_7', 'ryder.', 'Kaze.', 'bl1tz', 'phoenix_', '404',
        // —— 尊称/身份式 ——
        '路亚先生-', '南方姑娘', '老王', '张三百', '李师傅', '夜行小赵',
        '一只小透明', '半糖去冰', '隔壁老樊', '楼下张大妈', '不锈钢铁王',
        '逃课的花生', '早八人', '摆烂大师', '摸鱼冠军', '带薪拉屎',
        // —— 自嘲/日常 ——
        '又菜又爱玩', '不想上班', '打工人', '今天也没睡', '先吃饭再说',
        '随便玩玩', '可能会有事', '在忙', '困', '饿', '省钱要紧', '薅羊毛',
        '充钱一时爽', '退坑失败中', '再肝一把', '睡了睡了',
        // —— 二次元/游戏梗 ——
        '提瓦特观光团', '米哈游受害者', '原神启动', '咸鱼本鱼', 'Steve.', 'Alex',
        'pickaxe_', '钻石剑', '附魔台常客', '村民甲', '苦力怕', '末影龙退散',
        // —— 现实生活流 ——
        '隔壁老王', '楼下快递', '红烧肉爱好者', '奶茶三分糖', '不吃香菜',
        '断网第六小时', '充电器没电', '空调坏了', '房东涨租', '考试挂科',
    ];
    // 网名常用符号（真假人几乎人手一个）
    $suf = ['', '', '', '', '-', '.', '_', '丨', '、', '☆', '·', 'の'];
    $pre = ['', '', '', '', '【', '丨'];
    // 二次拼接扩充到数千个不重样
    $out = [];
    foreach ($real as $i => $n) {
        $out[] = $n;
        $out[] = $pre[$i % count($pre)] . $n . $suf[$i % count($suf)];
    }
    // 老名字保留一小部分做点缀，但整体以新池为主
    $legacy = ['路人甲', '无名氏', '扫地僧', '看云卷', '摘星辰', '铁蛋', '翠花', '二丫'];
    foreach ($legacy as $i => $n) {
        $out[] = $n . $suf[$i % count($suf)];
    }
    return array_values(array_unique($out));
}

function arena_dummy_power(int $rank): int
{
    // 排名越高战力越高：1名~50万，线性衰到2000名~5000
    if ($rank <= 1) {
        return 3278;
    }
    if ($rank >= 2000) {
        return 79;
    }
    return (int) (3278 - ($rank - 1) * (3199 / 1999));
}

// 本区真玩家的成长基准，用来让机器人“跟随玩家提升”。
// 没有真玩家时退回默认曲线，保证开局就有合理靶子。
function arena_pop_anchor(string $zone): array
{
    $season = arena_season();
    try {
        $st = db()->prepare('SELECT MAX(power) mp, MAX(lv) ml, AVG(power) ap, COUNT(*) c FROM arena_rank WHERE zone=? AND season=? AND is_dummy=0');
        $st->execute([$zone, $season]);
        $r = $st->fetch() ?: [];
    } catch (Throwable $e) {
        $r = [];
    }
    $c = (int) ($r['c'] ?? 0);
    $maxPower = (int) ($r['mp'] ?? 0);
    $maxLv = (int) ($r['ml'] ?? 0);
    $avgPower = (int) ($r['ap'] ?? 0);
    // 目标“榜首机器人”战力：至少 3278；随最强玩家提升，但留 5% 让玩家能实际到顶
    $topPower = $c > 0 ? max(3278, (int) round($maxPower * 0.95)) : 3278;
    // 底部机器人战力：随平均玩家水平提升，但不低于 300，也不高于顶部的 15%
    $bottomPower = $c > 0 ? max(300, min((int) round($maxPower * 0.15), (int) round($avgPower * 0.6))) : 300;
    // 等级：顶部跟随最高玩家（夹在 100~300），底部 50
    $topLv = $c > 0 ? max(100, min(300, $maxLv)) : 100;
    return ['count' => $c, 'maxpower' => $maxPower, 'maxlv' => $maxLv, 'top_power' => $topPower, 'bottom_power' => $bottomPower, 'top_lv' => $topLv];
}

// 按排名+锚点算目标战力（1..200 线性从 top 到 bottom）。
function arena_dummy_power_for(int $rank, array $anchor): int
{
    $top = (int) $anchor['top_power'];
    $bottom = (int) $anchor['bottom_power'];
    $r = max(1, min(200, $rank));
    $f = (200 - $r) / 199.0; // 1名→1，200名→0
    return (int) round($bottom + ($top - $bottom) * $f);
}

function arena_dummy_lv_for(int $rank, array $anchor): int
{
    $top = (int) $anchor['top_lv'];
    $bottom = 50;
    $r = max(1, min(200, $rank));
    $f = (200 - $r) / 199.0;
    return (int) round($bottom + ($top - $bottom) * $f);
}

function arena_dummy_lv(int $rank): int
{
    if ($rank <= 10) {
        return 100 - intdiv($rank - 1, 4);
    }
    if ($rank <= 50) {
        return 95 + (50 - $rank >= 25 ? 1 : 0);
    }
    if ($rank <= 100) {
        return 90 + intdiv(100 - $rank, 10);
    }
    if ($rank <= 300) {
        return 80 + intdiv(300 - $rank, 20);
    }
    if ($rank <= 600) {
        return 70 + intdiv(600 - $rank, 30);
    }
    if ($rank <= 1000) {
        return 60 + intdiv(1000 - $rank, 40);
    }
    return 50 + intdiv(2000 - $rank, 100);
}

function arena_ensure(string $zone): void
{
    $season = arena_season();
    $n = (int) db()->query('SELECT COUNT(*) FROM arena_rank WHERE zone=' . db()->quote($zone) . ' AND is_dummy=1 AND season=' . db()->quote($season))->fetchColumn();
    if ($n >= 200) {
        return;
    }
    if ($n > 0) { return; }
    $pool = arena_name_pool();
    $jobs = ['战士', '法师', '猎手', '牧师', '召唤师'];
    $pets = ['影猫', '石灵', '幽灵', '幼龙', '深渊魔龙'];
    $used = [];
    $anchor = arena_pop_anchor($zone);
    $st = db()->prepare('INSERT OR IGNORE INTO arena_rank (uid, zone, rank, name, job, lv, power, is_dummy, season) VALUES (?, ?, ?, ?, ?, ?, ?, 1, ?)');
    for ($r = 1; $r <= 200; $r++) {
        // 名字池有限，200个假人必然要重复取样；用有限次重试 + 数字后缀兜底，
        // 避免 do-while 在池子耗尽后死循环。
        $nm = '';
        for ($try = 0; $try < 60; $try++) {
            $cand = $pool[array_rand($pool)];
            if (!isset($used[$cand])) {
                $nm = $cand;
                break;
            }
            // 池子吃紧就带随机数字，几乎不会撞
            $cand .= random_int(2, 9999);
            if (!isset($used[$cand])) {
                $nm = $cand;
                break;
            }
        }
        if ($nm === '') {
            $nm = $pool[array_rand($pool)] . '_' . $r;
        }
        $used[$nm] = true;
        // 假人uid用负数（表主键是uid+zone，全0会撞键）
        try { $st->execute([-$r, $zone, $r, $nm, $jobs[array_rand($jobs)], arena_dummy_lv_for($r, $anchor), arena_dummy_power_for($r, $anchor), $season]); } catch (Throwable $e) { break; }
    }
}

// 机器人动态刷新（每次 arena 相关页面访问时轻量触发，靠 arena_bot_tick 节流到每 3 分钟一次）：
//  1) 战力/等级跟随本区玩家水平重算（缓慢逼近，避免瞬间跳变）；
//  2) 模拟"机器人也会往上爬"：少量机器人小幅上移，制造动态榜单；
//  3) 真玩家之间/机器人之间出现空位时补位。
function arena_bots_tick(string $zone): void
{
    $season = arena_season();
    $now = time();
    $kvKey = 'arena_bot_tick_' . $zone;
    $last = (int) (db()->query('SELECT num FROM sys_kv WHERE k=' . db()->quote($kvKey))->fetchColumn() ?: 0);
    if ($now - $last < 180) {
        return;
    }
    try {
        db()->prepare('INSERT OR REPLACE INTO sys_kv (k, num) VALUES (?, ?)')->execute([$kvKey, $now]);
    } catch (Throwable $e) {
        return;
    }
    $anchor = arena_pop_anchor($zone);
    $rows = db()->query('SELECT rowid, uid, rank, power, lv, name FROM arena_rank WHERE zone=' . db()->quote($zone) . ' AND season=' . db()->quote($season) . ' AND is_dummy=1 ORDER BY rank ASC')->fetchAll();
    if ($rows === []) {
        return;
    }
    $maxRank = 0;
    foreach ($rows as $r) {
        $maxRank = max($maxRank, (int) $r['rank']);
    }
    // 1) 战力/等级缓慢逼近锚点曲线
    foreach ($rows as $r) {
        $targetPw = arena_dummy_power_for((int) $r['rank'], $anchor);
        $targetLv = arena_dummy_lv_for((int) $r['rank'], $anchor);
        $curPw = (int) $r['power'];
        $curLv = (int) $r['lv'];
        // 每次最多变化 20% 差距，最少1点，避免跳变
        $dp = (int) round(($targetPw - $curPw) * 0.2);
        if ($dp === 0 && $targetPw !== $curPw) { $dp = $targetPw > $curPw ? 1 : -1; }
        $dl = (int) round(($targetLv - $curLv) * 0.2);
        if ($dl === 0 && $targetLv !== $curLv) { $dl = $targetLv > $curLv ? 1 : -1; }
        $np = max(1, $curPw + $dp);
        $nl = max(1, min(400, $curLv + $dl));
        if ($np !== $curPw || $nl !== $curLv) {
            db()->prepare('UPDATE arena_rank SET power=?, lv=? WHERE rowid=?')->execute([$np, $nl, (int) $r['rowid']]);
        }
    }
    // 2) 机器人往上爬：随机挑 1~3 个机器人，若目标名次被别的机器人占着，就互换（模拟爬榜）
    $climb = random_int(1, 3);
    for ($i = 0; $i < $climb; $i++) {
        $from = $rows[array_rand($rows)];
        $fromRank = (int) $from['rank'];
        if ($fromRank <= 1) {
            continue;
        }
        // 想爬到前面 1~2 名内（但不能越过真人占据的更高名次边界）
        $toRank = max(1, $fromRank - random_int(1, 3));
        $st = db()->prepare('SELECT rowid, uid, rank, is_dummy FROM arena_rank WHERE zone=? AND season=? AND rank=?');
        $st->execute([$zone, $season, $toRank]);
        $occ = $st->fetch();
        if (!$occ) {
            continue;
        }
        // 只和别的机器人互换；真人名次不主动动（真人是玩家挣的）
        if ((int) ($occ['is_dummy'] ?? 0) !== 1) {
            continue;
        }
        if ((int) $occ['uid'] === (int) $from['uid']) {
            continue;
        }
        db()->prepare('UPDATE arena_rank SET rank=? WHERE rowid=?')->execute([$toRank, (int) $from['rowid']]);
        db()->prepare('UPDATE arena_rank SET rank=? WHERE rowid=?')->execute([$fromRank, (int) $occ['rowid']]);
    }
    // 3) 补位：真玩家离开/被清掉会留下空洞名次。把空洞上方的名次整体上移一格填平，榜单始终连续。
    arena_bots_compact($zone);
}

// 把 1..maxRank 里的空名次填平：每次找最小空洞，把其上方最近一个机器人拉下来补位（真人优先保持不动，
// 但为保持连续，整体做一次紧致重排）。
function arena_bots_compact(string $zone): void
{
    $season = arena_season();
    $rows = db()->query('SELECT rowid, rank FROM arena_rank WHERE zone=' . db()->quote($zone) . ' AND season=' . db()->quote($season) . ' ORDER BY rank ASC')->fetchAll();
    if ($rows === []) {
        return;
    }
    $expect = 1;
    $need = false;
    foreach ($rows as $r) {
        if ((int) $r['rank'] !== $expect) { $need = true; break; }
        $expect++;
    }
    if (!$need) {
        return;
    }
    // 按现有 rank 顺序依次压成 1..N（O(n)，极端情况才触发，3分钟一次）
    $rk = 1;
    foreach ($rows as $r) {
        if ((int) $r['rank'] !== $rk) {
            db()->prepare('UPDATE arena_rank SET rank=? WHERE rowid=?')->execute([$rk, (int) $r['rowid']]);
        }
        $rk++;
    }
}

function arena_my(int $uid): ?array
{
    $u = user_by_id((int) $uid);
    if (!$u) {
        return null;
    }
    $zone = (string) ($u['zone'] ?? 'z1');
    arena_ensure($zone);
    // 机器人动态刷新（节流到每3分钟一次）：战力跟随玩家 + 爬榜 + 补位
    if (function_exists('arena_bots_tick')) {
        try { arena_bots_tick($zone); } catch (Throwable $e) {}
    }
    $season = arena_season();
    $st = db()->prepare('SELECT * FROM arena_rank WHERE uid=? AND zone=? AND season=?');
    $st->execute([(int) $uid, $zone, $season]);
    $row = $st->fetch();
    return $row ?: null;
}

function arena_join(int $uid): string
{
    $u = user_by_id((int) $uid);
    if (!$u) {
        return '角色不存在。';
    }
    if ((int) ($u['lv'] ?? 1) < 80) {
        return '80级才能进竞技场，你现在' . (int) $u['lv'] . '级。';
    }
    $zone = (string) ($u['zone'] ?? 'z1');
    arena_ensure($zone);
    $season = arena_season();
    $st = db()->prepare('SELECT * FROM arena_rank WHERE uid=? AND zone=? AND season=?');
    $st->execute([(int) $uid, $zone, $season]);
    if ($st->fetch()) {
        return '你已经在竞技场了。';
    }
    $players = (int) db()->query('SELECT COUNT(*) FROM arena_rank WHERE zone=' . db()->quote($zone) . ' AND season=' . db()->quote($season) . ' AND is_dummy=0')->fetchColumn();
    $rank = 100 + $players;
    // 把该名次及以下的人整体往下挤一位
    db()->prepare('UPDATE arena_rank SET rank=rank+1 WHERE zone=? AND season=? AND rank>=?')->execute([$zone, $season, $rank]);
    db()->prepare('INSERT INTO arena_rank (uid, zone, rank, name, job, lv, power, is_dummy, season) VALUES (?, ?, ?, ?, ?, ?, ?, 0, ?)')->execute([(int) $uid, $zone, $rank, (string) $u['username'], job_name_of($u), (int) $u['lv'], power_score($u), $season]);
    return '欢迎来到竞技场！你的初始排名：第' . $rank . '名。';
}

function arena_refresh_power(int $uid): void
{
    $u = user_by_id((int) $uid);
    if (!$u) {
        return;
    }
    $zone = (string) ($u['zone'] ?? 'z1');
    db()->prepare('UPDATE arena_rank SET name=?, job=?, lv=?, power=? WHERE uid=? AND zone=? AND season=?')->execute([(string) $u['username'], job_name_of($u), (int) $u['lv'], power_score($u), (int) $uid, $zone, arena_season()]);
}

// 各职业竞技场大招（取该职业罪技大招做“绝招”，数值略调以免秒杀）
function arena_job_ult(string $jobId): array
{
    $map = [
        'warrior'  => ['name' => '血狱狂斩', 'mult' => 2.4, 'heal' => false],
        'mage'     => ['name' => '魂蚀天雷', 'mult' => 2.8, 'heal' => false],
        'hunter'   => ['name' => '弑神连珠', 'mult' => 2.4, 'heal' => false],
        'priest'   => ['name' => '堕天圣裁', 'mult' => 1.8, 'heal' => true],
        'summoner' => ['name' => '深渊主宰', 'mult' => 2.5, 'heal' => false],
    ];
    return $map[$jobId] ?? ['name' => '全力一击', 'mult' => 2.0, 'heal' => false];
}

function arena_job_id_from_name(string $name): string
{
    return ['战士' => 'warrior', '法师' => 'mage', '猎手' => 'hunter', '猎人' => 'hunter', '牧师' => 'priest', '召唤师' => 'summoner'][$name] ?? 'warrior';
}

// 我方战阵：攻/防/血 + 出战宠物 + 职业大招
function arena_my_team(array $u): array
{
    $pet = active_pet((int) ($u['id'] ?? 0));
    $p = null;
    if ($pet) {
        $ps = pet_stats($pet);
        $pn = pet_species()[$pet['species']]['name'] ?? $pet['species'];
        $p = ['name' => $pn, 'lv' => (int) $pet['level'], 'hp' => (int) $ps['maxhp'], 'atk' => (int) $ps['atk']];
    }
    // 法系取 matk，物理取 atk；防御取对方法系/物理两侧的通用 pvp 防御
    [$atk, $type] = function_exists('pvp_attack_of') ? pvp_attack_of($u) : [(int) player_atk($u), 'phys'];
    $jobId = job_id_of($u);
    return [
        'name' => (string) ($u['username'] ?? ''), 'lv' => (int) ($u['lv'] ?? 1),
        'hp' => (int) ($u['maxhp'] ?? 1),
        'atk' => $atk, 'atk_type' => $type,
        'job' => $jobId, 'ult' => arena_job_ult($jobId),
        'pdef' => (int) player_pdef($u), 'mdef' => (int) player_mdef($u),
        'def' => (int) player_def($u), 'pet' => $p,
    ];
}

function arena_foe_team(array $row): array
{
    if ((int) ($row['is_dummy'] ?? 0) === 1) {
        $pw = max(1, (int) ($row['power'] ?? 100));
        $lv = max(1, (int) ($row['lv'] ?? 1));
        $pets = ['影猫', '石灵', '幽灵', '幼龙', '深渊魔龙'];
        $d = max(0, (int) ($pw * 0.08));
        $jobId = arena_job_id_from_name((string) ($row['job'] ?? '战士'));
        $isMag = in_array($jobId, ['mage', 'priest', 'summoner'], true);
        return [
            'name' => (string) $row['name'], 'lv' => $lv,
            'hp' => (int) ($pw * 0.5), 'atk' => max(1, (int) ($pw * 0.12)), 'atk_type' => $isMag ? 'mag' : 'phys',
            'job' => $jobId, 'ult' => arena_job_ult($jobId),
            'pdef' => $d, 'mdef' => $d, 'def' => $d,
            'pet' => ['name' => $pets[$lv % count($pets)], 'lv' => max(1, $lv - 10), 'hp' => (int) ($pw * 0.1), 'atk' => max(1, (int) ($pw * 0.03))],
        ];
    }
    $tu = user_by_id((int) $row['uid']);
    if (!$tu) {
        $pw = max(1, (int) ($row['power'] ?? 100));
        $d = max(0, (int) ($pw * 0.08));
        $jobId = arena_job_id_from_name((string) ($row['job'] ?? '战士'));
        return ['name' => (string) $row['name'], 'lv' => (int) $row['lv'], 'hp' => (int) ($pw * 0.5), 'atk' => max(1, (int) ($pw * 0.12)), 'atk_type' => 'phys', 'job' => $jobId, 'ult' => arena_job_ult($jobId), 'pdef' => $d, 'mdef' => $d, 'def' => $d, 'pet' => null];
    }
    return arena_my_team($tu);
}

// 竞技场单次打击：PVP 统一用 pdef/mdef 混合防御，物理法术对称，带浮动与暴击风味。
function arena_strike(array $atkTeam, array $defTeam): array
{
    $def = ((float) ($defTeam['pdef'] ?? $defTeam['def'] ?? 0) + (float) ($defTeam['mdef'] ?? $defTeam['def'] ?? 0)) / 2;
    $d = max(1, (int) ($atkTeam['atk'] * (0.9 + mt_rand(0, 20) / 100) - $def * 0.5));
    // 暴击风味：8% 概率 ×1.6，让战斗不呆板
    $critTag = '';
    if (mt_rand(1, 100) <= 8) {
        $d = (int) ($d * 1.6);
        $critTag = '(暴击)';
    }
    return [$d, $critTag];
}

// 竞技场大招一击：倍率更高、可暴击、牧师系带自愈。返回 [伤害, 标签]。
function arena_ult_strike(array $atkTeam, array $defTeam): array
{
    $ult = $atkTeam['ult'] ?? ['name' => '全力一击', 'mult' => 2.0, 'heal' => false];
    $def = ((float) ($defTeam['pdef'] ?? $defTeam['def'] ?? 0) + (float) ($defTeam['mdef'] ?? $defTeam['def'] ?? 0)) / 2;
    $d = max(1, (int) ($atkTeam['atk'] * (float) $ult['mult'] - $def * 0.5));
    $critTag = '';
    if (mt_rand(1, 100) <= 12) {
        $d = (int) ($d * 1.6);
        $critTag = '(暴击)';
    }
    return [$d, $critTag];
}

// 异步数值对撞：战力+宠物+骰子，每 3 回合双方各放一次职业大招。返回[win, log[]]
function arena_sim(array $a, array $b): array
{
    $log = [];
    $ahp = $a['hp'];
    $bhp = $b['hp'];
    $amax = max(1, $a['hp']);
    $bmax = max(1, $b['hp']);
    $atypeTag = ($a['atk_type'] ?? 'phys') === 'mag' ? '法术' : '物理';
    $btypeTag = ($b['atk_type'] ?? 'phys') === 'mag' ? '法术' : '物理';
    $alog = '你的队伍：' . $a['name'] . '，等级' . $a['lv'] . '，生命' . $a['hp'] . '，' . $atypeTag . '攻击' . $a['atk'] . '。';
    if ($a['pet']) {
        $alog .= '宠物：' . $a['pet']['name'] . '，等级' . $a['pet']['lv'] . '，生命' . $a['pet']['hp'] . '，攻击' . $a['pet']['atk'] . '。';
    }
    $log[] = $alog;
    $blog = '对手队伍：' . $b['name'] . '，等级' . $b['lv'] . '，生命' . $b['hp'] . '，' . $btypeTag . '攻击' . $b['atk'] . '。';
    if ($b['pet']) {
        $blog .= '宠物：' . $b['pet']['name'] . '，等级' . $b['pet']['lv'] . '，生命' . $b['pet']['hp'] . '，攻击' . $b['pet']['atk'] . '。';
    }
    $log[] = $blog;
    $log[] = '战斗开始。';
    $aphp = $a['pet']['hp'] ?? 0;
    $bphp = $b['pet']['hp'] ?? 0;
    $aSkills = ($a['atk_type'] ?? 'phys') === 'mag' ? ['火球术', '冰锥术', '雷光术'] : ['怒斩', '旋风斩', '重击'];
    $bSkills = ($b['atk_type'] ?? 'phys') === 'mag' ? ['火球术', '冰锥术', '雷光术'] : ['怒斩', '旋风斩', '重击'];
    $aUlt = $a['ult']['name'] ?? '全力一击';
    $bUlt = $b['ult']['name'] ?? '全力一击';
    $aUltHeal = !empty($a['ult']['heal']);
    $bUltHeal = !empty($b['ult']['heal']);
    // 我方一个回合的进攻（含宠物）；返回 true 表示把对方主将打死
    $actA = function (bool $ultRound) use (&$ahp, &$bhp, &$aphp, &$bphp, &$log, $a, $b, $aSkills, $aUlt, $aUltHeal, $amax): bool {
        if ($ultRound) {
            [$d, $t] = arena_ult_strike($a, $b);
            $bhp -= $d;
            $log[] = '【大招】你释放' . $aUlt . $t . '，对' . $b['name'] . '造成' . $d . '点重创！';
            if ($aUltHeal && $ahp < $amax) {
                $h = max(1, (int) ($amax * 0.08));
                $ahp = min($amax, $ahp + $h);
                $log[] = '圣光回涌，你恢复' . $h . '点生命。';
            }
        } else {
            [$d, $t] = arena_strike($a, $b);
            if ($bphp > 0 && mt_rand(1, 100) <= 30) {
                $bphp -= $d;
                $log[] = '你使用' . $aSkills[array_rand($aSkills)] . $t . '，对' . $b['pet']['name'] . '造成' . $d . '点伤害。';
                if ($bphp <= 0) { $log[] = $b['pet']['name'] . '倒下了！'; }
            } else {
                $bhp -= $d;
                $log[] = '你使用' . $aSkills[array_rand($aSkills)] . $t . '，对' . $b['name'] . '造成' . $d . '点伤害。';
            }
        }
        if ($bhp <= 0) { return true; }
        if ($a['pet'] && $aphp > 0) {
            $pd = max(1, (int) ($a['pet']['atk'] * (0.9 + mt_rand(0, 20) / 100)));
            $bhp -= $pd;
            $log[] = $a['pet']['name'] . '扑向' . $b['name'] . '，造成' . $pd . '点伤害。';
            if ($bhp <= 0) { return true; }
        }
        return false;
    };
    // 对方一个回合的进攻；返回 true 表示把你打死
    $actB = function (bool $ultRound) use (&$ahp, &$bhp, &$aphp, &$bphp, &$log, $a, $b, $bSkills, $bUlt, $bUltHeal, $bmax): bool {
        if ($ultRound) {
            [$d, $t] = arena_ult_strike($b, $a);
            $ahp -= $d;
            $log[] = '【大招】' . $b['name'] . '释放' . $bUlt . $t . '，对你造成' . $d . '点重创！';
            if ($bUltHeal && $bhp < $bmax) {
                $h = max(1, (int) ($bmax * 0.08));
                $bhp = min($bmax, $bhp + $h);
                $log[] = $b['name'] . '圣光回涌，恢复' . $h . '点生命。';
            }
        } else {
            [$d, $t] = arena_strike($b, $a);
            if ($aphp > 0 && $a['pet'] && mt_rand(1, 100) <= 30) {
                $aphp -= $d;
                $log[] = $b['name'] . '使用' . $bSkills[array_rand($bSkills)] . $t . '，对' . $a['pet']['name'] . '造成' . $d . '点伤害。';
                if ($aphp <= 0) { $log[] = $a['pet']['name'] . '倒下了！'; }
            } else {
                $ahp -= $d;
                $log[] = $b['name'] . '使用' . $bSkills[array_rand($bSkills)] . $t . '，对你造成' . $d . '点伤害。';
            }
        }
        if ($ahp <= 0) { return true; }
        if ($b['pet'] && $bphp > 0) {
            $pd2 = max(1, (int) ($b['pet']['atk'] * (0.9 + mt_rand(0, 20) / 100)));
            $ahp -= $pd2;
            $log[] = $b['pet']['name'] . '使用普通攻击，对你造成' . $pd2 . '点伤害。';
            if ($ahp <= 0) { return true; }
        }
        return false;
    };
    for ($r = 1; $r <= 30; $r++) {
        $ultRound = ($r % 3 === 0); // 每 3 回合放一次大招
        // 每回合随机先手，避免“进攻方永远先放大招”的结构性优势
        if (mt_rand(0, 1) === 1) {
            if ($actA($ultRound)) { $log[] = '战斗结束。你获胜。'; return [true, $log]; }
            if ($actB($ultRound)) { $log[] = '战斗结束。你落败。'; return [false, $log]; }
        } else {
            if ($actB($ultRound)) { $log[] = '战斗结束。你落败。'; return [false, $log]; }
            if ($actA($ultRound)) { $log[] = '战斗结束。你获胜。'; return [true, $log]; }
        }
    }
    // 30轮未分胜负：剩余血量多者胜，平局算守方胜
    $win = ($ahp / max(1, $a['hp'])) > ($bhp / max(1, $b['hp']));
    $log[] = '战斗结束。' . ($win ? '你获胜。' : '你落败。');
    return [$win, $log];
}

function arena_chances(int $uid): array
{
    $day = arena_day_idx();
    $mats = mats_of((int) $uid);
    $used = (int) ((int) ($mats['arena_day'] ?? 0) === $day ? ($mats['arena_used'] ?? 0) : 0);
    $bought = (int) ((int) ($mats['arena_day'] ?? 0) === $day ? ($mats['arena_bought'] ?? 0) : 0);
    return ['free' => max(0, 5 - $used), 'used' => $used, 'bought' => $bought, 'left' => max(0, 5 + $bought - $used)];
}

function arena_buy_chances(int $uid, string $kind): string
{
    $u = user_by_id((int) $uid);
    if (!$u) {
        return '角色不存在。';
    }
    $day = arena_day_idx();
    $mats = mats_of((int) $uid);
    if ((int) ($mats['arena_day'] ?? 0) !== $day) {
        mat_set((int) $uid, 'arena_day', $day);
        mat_set((int) $uid, 'arena_used', 0);
        mat_set((int) $uid, 'arena_bought', 0);
        $mats = mats_of((int) $uid);
    }
    if ($kind === 'gold') {
        if ((int) ($u['gold'] ?? 0) < 100000) {
            return '金币不够，一次10金。';
        }
        $u['gold'] = (int) $u['gold'] - 100000;
        user_save($u);
        add_mat((int) $uid, 'arena_bought', 1);
        return '花10金买下1次挑战。';
    }
    if ($kind === 'diamond') {
        if ((int) ($u['diamonds'] ?? 0) < 10) {
            return '魔钻不够，一魔钻换5次。';
        }
        $u['diamonds'] = (int) $u['diamonds'] - 10;
        user_save($u);
        spend_diamonds((int) $uid, 10);
        add_mat((int) $uid, 'arena_bought', 5);
        return '花1魔钻买下5次挑战。';
    }
    return '没这种买法。';
}

function arena_targets(array $my): array
{
    $zone = (string) ($my['zone'] ?? 'z1');
    $season = (string) ($my['season'] ?? arena_season());
    $r = (int) $my['rank'];
    $lo = max(1, $r - 34);
    $st = db()->prepare('SELECT * FROM arena_rank WHERE zone=? AND season=? AND rank>=? AND rank<? ORDER BY rank ASC LIMIT 60');
    $st->execute([$zone, $season, $lo, $r]);
    $all = $st->fetchAll();
    // 挑5个 spread 开的对手
    if (count($all) <= 5) {
        return $all;
    }
    $out = [$all[0]];
    $step = (count($all) - 1) / 4;
    for ($i = 1; $i <= 4; $i++) {
        $out[] = $all[(int) round($i * $step)];
    }
    return $out;
}

// 挑战：成功互换排名，失败不变。返回[win, log[], foeRank, myOld, myNew]
function arena_fight(int $uid, int $foeRank): array
{
    $my = arena_my((int) $uid);
    if (!$my) {
        return [false, ['你还没进竞技场。'], 0, 0, 0];
    }
    $u = user_by_id((int) $uid);
    if (!$u) {
        return [false, ['角色不存在。'], 0, 0, 0];
    }
    $zone = (string) ($my['zone'] ?? 'z1');
    $season = (string) ($my['season'] ?? arena_season());
    $myRank = (int) $my['rank'];
    if ($foeRank >= $myRank || $foeRank < max(1, $myRank - 34)) {
        return [false, ['只能挑战排名比你高的对手（第' . max(1, $myRank - 34) . '名到第' . ($myRank - 1) . '名）。'], 0, $myRank, $myRank];
    }
    $day = arena_day_idx();
    $mats = mats_of((int) $uid);
    if ((int) ($mats['arena_day'] ?? 0) !== $day) {
        mat_set((int) $uid, 'arena_day', $day);
        mat_set((int) $uid, 'arena_used', 0);
        mat_set((int) $uid, 'arena_bought', 0);
        $mats = mats_of((int) $uid);
    }
    $used = (int) ($mats['arena_used'] ?? 0);
    $bought = (int) ($mats['arena_bought'] ?? 0);
    if ($used >= 5 + $bought) {
        return [false, ['今日挑战次数用完了（免费5次+购买' . $bought . '次）。花10金买1次，或1魔钻买5次。'], 0, $myRank, $myRank];
    }
    $st = db()->prepare('SELECT * FROM arena_rank WHERE zone=? AND season=? AND rank=?');
    $st->execute([$zone, $season, $foeRank]);
    $foe = $st->fetch();
    if (!$foe) {
        return [false, ['对手不在这个名次了，刷新列表重试。'], 0, $myRank, $myRank];
    }
    if ((int) $foe['uid'] === (int) $uid) {
        return [false, ['不能挑战自己。'], 0, $myRank, $myRank];
    }
    arena_refresh_power((int) $uid);
    if ((int) ($foe['is_dummy'] ?? 0) === 0) {
        arena_refresh_power((int) $foe['uid']);
        $st->execute([$zone, $season, $foeRank]);
        $foe = $st->fetch();
    }
    [$win, $log] = arena_sim(arena_my_team($u), arena_foe_team($foe));
    add_mat((int) $uid, 'arena_used', 1);
    if ($win) {
        // 互换排名
        db()->prepare('UPDATE arena_rank SET rank=? WHERE uid=? AND zone=? AND season=?')->execute([$myRank, (int) ($foe['uid'] ?? 0), $zone, $season]);
        db()->prepare('UPDATE arena_rank SET rank=? WHERE uid=? AND zone=? AND season=?')->execute([$foeRank, (int) $uid, $zone, $season]);
        $log[] = '你的排名从第' . $myRank . '名上升至第' . $foeRank . '名。' . $foe['name'] . '排名下降至第' . $myRank . '名。';
        // 防守记录（只记真人被打）
        if ((int) ($foe['is_dummy'] ?? 0) === 0) {
            db()->prepare('INSERT INTO arena_log (zone, defender_uid, attacker_name, win, old_rank, new_rank, created_at) VALUES (?, ?, ?, 0, ?, ?, ?)')->execute([$zone, (int) $foe['uid'], (string) $u['username'], $foeRank, $myRank, time()]);
        }
        arena_announce($zone, '【竞技场】' . $u['username'] . '挑战' . $foe['name'] . '成功，排名上升至第' . $foeRank . '名。');
        return [true, $log, $foeRank, $myRank, $foeRank];
    }
    if ((int) ($foe['is_dummy'] ?? 0) === 0) {
        db()->prepare('INSERT INTO arena_log (zone, defender_uid, attacker_name, win, old_rank, new_rank, created_at) VALUES (?, ?, ?, 1, ?, ?, ?)')->execute([$zone, (int) $foe['uid'], (string) $u['username'], $foeRank, $foeRank, time()]);
    }
    $log[] = '挑战失败，排名不变（第' . $myRank . '名）。';
    return [false, $log, $foeRank, $myRank, $myRank];
}

function arena_rewards(int $rank): array
{
    // 返回[gold铜, arena_coin, 附魔石id=>n]
    $pool = ['light', 'dark', 'fire', 'wind', 'ice', 'thunder'];
    $stone = 'el_' . $pool[array_rand($pool)] . '_stone';
    if ($rank === 1) {
        return [100000, 200, [$stone => 10]];
    }
    if ($rank === 2) {
        return [50000, 150, [$stone => 5]];
    }
    if ($rank === 3) {
        return [30000, 120, [$stone => 4]];
    }
    if ($rank <= 10) {
        return [20000, 100, [$stone => 3]];
    }
    if ($rank <= 50) {
        return [10000, 60, [$stone => 2]];
    }
    if ($rank <= 100) {
        return [5000, 40, [$stone => 1]];
    }
    if ($rank <= 300) {
        return [3000, 25, []];
    }
    if ($rank <= 1000) {
        return [1000, 10, []];
    }
    return [500, 5, []];
}

// 天梯段位：按排名划分。名次越小段位越高。
function arena_tier(int $rank): array
{
    // [key, 中文, 代表色]
    if ($rank <= 1)   { return ['king', '王者', '#ffcf40']; }
    if ($rank <= 3)   { return ['master', '大师', '#c56bff']; }
    if ($rank <= 10)  { return ['diamond', '钻石', '#5ad1ff']; }
    if ($rank <= 30)  { return ['platinum', '铂金', '#7cf3d0']; }
    if ($rank <= 70)  { return ['gold', '黄金', '#ffd84d']; }
    if ($rank <= 150) { return ['silver', '白银', '#c9d4e0']; }
    if ($rank <= 400) { return ['bronze', '青铜', '#c8895a']; }
    return ['iron', '黑铁', '#8a8a8a'];
}

// 段位保底奖励系数：越往上越丰厚（用于每日+赛季结算兜底，不让人越打越亏）
function arena_tier_bonus(string $tierKey): array
{
    // [金币额外, 竞技场币额外]
    $map = [
        'iron' => [0, 0], 'bronze' => [500, 3], 'silver' => [1500, 5],
        'gold' => [3000, 8], 'platinum' => [6000, 12], 'diamond' => [12000, 20],
        'master' => [20000, 30], 'king' => [40000, 50],
    ];
    return $map[$tierKey] ?? [0, 0];
}

// 每日05:00结算：按排名发邮件；每周一05:00重置赛季+发上周奖励
function arena_tick(): void
{
    $now = time();
    $last = (int) (db()->query("SELECT num FROM sys_kv WHERE k='arena_settle'")->fetchColumn() ?: 0);
    $today5 = strtotime(date('Y-m-d') . ' 05:00');
    if ($now < $today5) {
        $today5 -= 86400;
    }
    if ($last >= $today5) {
        return;
    }
    try {
        db()->exec("INSERT OR REPLACE INTO sys_kv (k, num) VALUES ('arena_settle', {$today5})");
    } catch (Throwable $e) {
        try {
            db()->exec('CREATE TABLE IF NOT EXISTS sys_kv (k TEXT PRIMARY KEY, num INTEGER NOT NULL DEFAULT 0)');
            db()->exec("INSERT OR REPLACE INTO sys_kv (k, num) VALUES ('arena_settle', {$today5})");
        } catch (Throwable $e2) {
            return;
        }
    }
    $isMonday = date('N', $today5) === '1';
    foreach (array_keys(zones()) as $zone) {
        arena_ensure($zone);
        $season = arena_season();
        $rows = db()->query('SELECT * FROM arena_rank WHERE zone=' . db()->quote($zone) . ' AND season=' . db()->quote($season) . ' AND is_dummy=0 ORDER BY rank ASC')->fetchAll();
        foreach ($rows as $r) {
            [$gold, $coin, $stones] = arena_rewards((int) $r['rank']);
            $tier = arena_tier((int) $r['rank']);
            [$bg, $bc] = arena_tier_bonus($tier[0]);
            $gold += $bg; $coin += $bc;
            $att = [['t' => 'mat', 'id' => 'arena_coin', 'n' => $coin]];
            foreach ($stones as $sid => $n) {
                $att[] = ['t' => 'mat', 'id' => $sid, 'n' => $n];
            }
            if ($gold > 0) {
                $att[] = ['t' => 'gold', 'n' => $gold];
            }
            send_mail((int) $r['uid'], '竞技场', 'arena', '竞技场每日奖励：' . $tier[1] . '段位·第' . $r['rank'] . '名', '今日排名第' . $r['rank'] . '名（' . $tier[1] . '段位），奖励：' . fmt_money($gold) . '、竞技场币x' . $coin . '。请查收附件。', $att);
        }
        if ($isMonday) {
            // 上周额外奖励：按段位加成后的名次奖励再发一份
            foreach ($rows as $r) {
                [$gold, $coin, $stones] = arena_rewards((int) $r['rank']);
                $tier = arena_tier((int) $r['rank']);
                [$bg, $bc] = arena_tier_bonus($tier[0]);
                $gold += $bg; $coin += $bc;
                $att = [['t' => 'mat', 'id' => 'arena_coin', 'n' => $coin]];
                if ($gold > 0) {
                    $att[] = ['t' => 'gold', 'n' => $gold];
                }
                send_mail((int) $r['uid'], '竞技场', 'arena', '竞技场赛季重置：上周' . $tier[1] . '段位·第' . $r['rank'] . '名', '新赛季开始，你的排名回到初始状态。上周第' . $r['rank'] . '名（' . $tier[1] . '段位）的额外奖励请查收。', $att);
            }
            // 真人回初始位，假人回初始位置
            $players = db()->query('SELECT uid FROM arena_rank WHERE zone=' . db()->quote($zone) . ' AND season=' . db()->quote($season) . ' AND is_dummy=0 ORDER BY rank ASC')->fetchAll();
            $newSeason = date('Y-m-d', $today5);
            db()->prepare('UPDATE arena_rank SET season=? WHERE zone=? AND season=?')->execute([$newSeason, $zone, $season]);
            $rk = 100;
            foreach ($players as $p) {
                db()->prepare('UPDATE arena_rank SET rank=? WHERE uid=? AND zone=? AND season=?')->execute([$rk, (int) $p['uid'], $zone, $newSeason]);
                $rk++;
            }
            // 假人重排1..2000
            $dummies = db()->query('SELECT rowid FROM arena_rank WHERE zone=' . db()->quote($zone) . ' AND season=' . db()->quote($newSeason) . ' AND is_dummy=1 ORDER BY power DESC')->fetchAll();
            $dr = 1;
            foreach ($dummies as $d) {
                db()->prepare('UPDATE arena_rank SET rank=? WHERE rowid=?')->execute([$dr, (int) $d['rowid']]);
                $dr++;
            }
        }
    }
}

function arena_shop(): array
{
    return [
        'enhance_t1' => ['name' => '下级强化石', 'price' => 10, 'limit' => 10, 'period' => 'day'],
        'enhance_t2' => ['name' => '中级强化石', 'price' => 30, 'limit' => 5, 'period' => 'day'],
        'enhance_t3' => ['name' => '上级强化石', 'price' => 80, 'limit' => 5, 'period' => 'day'],
        'pet_food' => ['name' => '宠物粮', 'price' => 50, 'limit' => 5, 'period' => 'month'],
        'egg_unknown' => ['name' => '随机宠物蛋', 'price' => 100, 'limit' => 1, 'period' => 'month'],
        'qingzui_dan' => ['name' => '清罪丹', 'price' => 60, 'limit' => 1, 'period' => 'month'],
    ];
}

function arena_shop_buy(int $uid, string $item): string
{
    $shop = arena_shop();
    if (!isset($shop[$item])) {
        return '没这件商品。';
    }
    $g = $shop[$item];
    $period = $g['period'] === 'day' ? 'd' . arena_day_idx() : date('Y-m');
    $st = db()->prepare('SELECT num FROM arena_shop_log WHERE uid=? AND item=? AND period=?');
    $st->execute([(int) $uid, $item, $period]);
    $had = (int) ($st->fetchColumn() ?: 0);
    if ($had >= $g['limit']) {
        return '限购到了（' . ($g['period'] === 'day' ? '每日' : '每月') . '限购' . $g['limit'] . '次）。';
    }
    $mats = mats_of((int) $uid);
    if ((int) ($mats['arena_coin'] ?? 0) < $g['price']) {
        return '竞技场币不够，要' . $g['price'] . '币。';
    }
    add_mat((int) $uid, 'arena_coin', -$g['price']);
    if ($item === 'pet_food' || $item === 'egg_unknown' || $item === 'qingzui_dan' || str_starts_with($item, 'enhance_')) {
        add_mat((int) $uid, $item, 1);
    }
    if ($had > 0) {
        db()->prepare('UPDATE arena_shop_log SET num=num+1 WHERE uid=? AND item=? AND period=?')->execute([(int) $uid, $item, $period]);
    } else {
        db()->prepare('INSERT INTO arena_shop_log (uid, item, period, num) VALUES (?, ?, ?, 1)')->execute([(int) $uid, $item, $period]);
    }
    $u = user_by_id((int) $uid);
    if ($u) {
        arena_announce((string) ($u['zone'] ?? 'z1'), '【竞技场】' . $u['username'] . '在竞技场商店购买了' . $g['name'] . '。');
    }
    return '买下【' . $g['name'] . '】，已进背包。';
}

function arena_announce(string $zone, string $text): void
{
    db()->prepare('INSERT INTO chat_msgs (uid, username, channel, target, zone, text, created_at) VALUES (0, "战报", "world", 0, ?, ?, ?)')->execute([$zone, $text, time()]);
}

function arena_defense_log(int $uid): array
{
    $st = db()->prepare('SELECT * FROM arena_log WHERE defender_uid=? ORDER BY id DESC LIMIT 10');
    $st->execute([(int) $uid]);
    return $st->fetchAll();
}
