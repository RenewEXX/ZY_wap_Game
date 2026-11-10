<?php
declare(strict_types=1);

// 竞技场：异步数值对撞，按大区分赛区。每周一05:00换赛季，每日05:00结算奖励。

function arena_season(): string
{
    // 以自然月为赛季：每月 1 号 05:00 切换，和竞技场每日结算时刻一致。
    $now = time();
    $monthStart = strtotime(date('Y-m-01') . ' 05:00');
    if ($now < $monthStart) {
        return date('Y-m', strtotime('first day of last month', $monthStart));
    }
    return date('Y-m');
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

// 竞技场规模：假人总数 + 真人初始名次（需与 arena_dummy_power_for / arena_dummy_lv_for 的曲线上限一致）。
// 竞技场是跨大区的：所有真实玩家（含未来新增大区）共用同一个竞技场池，假人也只维护一份。
function arena_zone(): string
{
    return 'arena';
}

function arena_bot_count(): int
{
    return 1000;
}

function arena_start_rank(): int
{
    // 机器人占据 1..1000，真人入场从 1001 起，避免真人与第1000名机器人重号。
    return arena_bot_count() + 1;
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
    // 目标“榜首机器人”战力：至少 3278；随最强玩家提升
    $topPower = $c > 0 ? max(3278, (int) round($maxPower * 0.95)) : 3278;
    // 底部机器人战力：随平均玩家水平提升，但不低于 300，也不高于顶部的 15%
    $bottomPower = $c > 0 ? max(300, min((int) round($maxPower * 0.15), (int) round($avgPower * 0.6))) : 300;
    // 等级：顶部跟随最高玩家（夹在 100~300），底部 50
    $topLv = $c > 0 ? max(100, min(300, $maxLv)) : 100;
    return ['count' => $c, 'maxpower' => $maxPower, 'maxlv' => $maxLv, 'top_power' => $topPower, 'bottom_power' => $bottomPower, 'top_lv' => $topLv];
}

// 按排名+锚点算目标战力。
// 设计：前 3 名机器人要比最强真人略高一点（1.02/1.01/1.00 倍），让“天花板”有挑战但不至于碾压；
// 第 4 名起回落到最强真人的 0.95 倍，再线性滑到榜尾，形成一道“前几名的墙”。
function arena_dummy_power_for(int $rank, array $anchor): int
{
    $n = arena_bot_count();
    $r = max(1, min($n, $rank));
    $maxPlayer = (int) ($anchor['maxpower'] ?? 0);
    $bottom = (int) $anchor['bottom_power'];
    if ($maxPlayer > 0) {
        $topBots = [1 => 1.02, 2 => 1.01, 3 => 1.00];
        if (isset($topBots[$r])) {
            return (int) round($maxPlayer * $topBots[$r]);
        }
        // 第 4 名起：从 0.95×最强真人 线性滑到榜尾
        $startR = 4;
        $startPower = (int) round($maxPlayer * 0.95);
        $f = ($n - $r) / max(1, ($n - $startR)); // r=4→1, r=$n→0
        $f = max(0.0, min(1.0, $f));
        return (int) round($bottom + ($startPower - $bottom) * $f);
    }
    // 没有真人：默认曲线
    $top = (int) $anchor['top_power'];
    $f = ($n - $r) / max(1, ($n - 1));
    return (int) round($bottom + ($top - $bottom) * $f);
}

function arena_dummy_lv_for(int $rank, array $anchor): int
{
    $n = arena_bot_count();
    $r = max(1, min($n, $rank));
    $maxLv = (int) ($anchor['maxlv'] ?? 0);
    $bottom = 50;
    // 前 3 名机器人等级也压过最强真人一点（封顶 400）
    if ($maxLv > 0 && $r <= 3) {
        return min(400, $maxLv + (4 - $r) * 3);
    }
    $top = $maxLv > 0 ? max(100, min(300, $maxLv)) : 100;
    $f = ($n - $r) / max(1, ($n - 1));
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

// 月末赛季奖励：[金(铜), 竞技场币]，按「上月最终名次」发放，比每日名次奖励更丰厚；王者档最大。
function arena_season_reward(int $rank): array
{
    if ($rank <= 1)   { return [50000, 500]; } // 王者（第1名）：5金 / 500竞技场币
    if ($rank <= 3)   { return [30000, 300]; }
    if ($rank <= 10)  { return [20000, 200]; }
    if ($rank <= 50)  { return [10000, 100]; }
    if ($rank <= 100) { return [6000, 60]; }
    if ($rank <= 300) { return [3000, 40]; }
    if ($rank <= 700) { return [1500, 20]; }
    if ($rank <= 1000){ return [800, 12]; }
    return [300, 5];
}

// 按战力把本区全体（真人 + 机器人）重新排成连续的 1..N 名次：
// 同分时真人(is_dummy=0)排在机器人前面（对玩家友好）。
// 这样新赛季真人不是被一股脑塞到第1000名，而是强者靠前、弱者靠后，名次真实反映实力。
function arena_rerank_by_power(string $zone, string $season): void
{
    $st = db()->prepare('SELECT rowid, is_dummy FROM arena_rank WHERE zone=? AND season=? ORDER BY power DESC, is_dummy ASC, rowid ASC');
    $st->execute([$zone, $season]);
    $rows = $st->fetchAll();
    $upd = db()->prepare('UPDATE arena_rank SET rank=? WHERE rowid=?');
    $rk = 1;
    foreach ($rows as $r) {
        $upd->execute([$rk, (int) $r['rowid']]);
        $rk++;
    }
}

// 月末赛季结算（不依赖每月 1 号当天是否有人访问）：用 sys_kv 记住本区已结算到哪个月，
// 只要 arena_season() 变了，就在「之后第一次访问/结算」时结算一次：
//   ① 给上个月末的真人按最终名次发「月末结算奖励」邮件（第1名=王者档），并告知新赛季已开启；
//   ② 按战力把全体（真人+机器人）重新排序成 1..N 名次（不再「所有人降到第1000名」）。
// 首次运行（kv 为空）或从旧「按周结算/存 Y-m-d」升级时只记基线，绝不群发邮件。
function arena_season_settle(string $zone): void
{
    $cur = arena_season();
    $curKey = (int) str_replace('-', '', $cur); // "Y-m" => YYYYMM（6位）
    $key = 'arena_season_roll_' . $zone;
    try {
        $rec = (int) (db()->query('SELECT num FROM sys_kv WHERE k=' . db()->quote($key))->fetchColumn() ?: 0);
    } catch (Throwable $e) {
        return; // 还没有 sys_kv 表就跳过，等 tick 建好再来
    }
    $saveKey = function () use ($key, $curKey): void {
        try { db()->prepare('INSERT OR REPLACE INTO sys_kv (k, num) VALUES (?, ?)')->execute([$key, $curKey]); } catch (Throwable $e) {}
    };
    if ($rec === 0) { $saveKey(); return; }        // 首次运行：只记基线
    if ($rec === $curKey) { return; }              // 本月已结算
    if ($rec >= 10000000) {
        // 旧「按周」格式(YYYYMMDD≈2千万)升级：旧逻辑已把真人一股脑压到第1000名。
        // 这里做一次性按战力重排修复名次（不群发邮件，避免突然刷屏），然后记新基线。
        arena_rerank_by_power($zone, $cur);
        $saveKey();
        return;
    }

    // —— 月度推进：先给上月末的真人按最终名次发结算奖励 ——
    $rows = db()->query('SELECT * FROM arena_rank WHERE zone=' . db()->quote($zone) . ' AND season=' . db()->quote($cur) . ' AND is_dummy=0 ORDER BY rank ASC')->fetchAll();
    if (function_exists('send_mail')) {
        foreach ($rows as $r) {
            $rk = (int) $r['rank'];
            [$gold, $coin] = arena_season_reward($rk);
            $tier = arena_tier($rk);
            $att = [];
            if ($coin > 0) { $att[] = ['t' => 'mat', 'id' => 'arena_coin', 'n' => $coin]; }
            if ($gold > 0) { $att[] = ['t' => 'gold', 'n' => $gold]; }
            $title = $rk === 1 ? '【王者】上月竞技场第1名·月末结算' : ('上月竞技场结算：第' . $rk . '名');
            $body = '上月竞技场最终名次：第' . $rk . '名（' . $tier[1] . '段位）。月末结算奖励：' . fmt_money($gold) . '、竞技场币x' . $coin . '。请查收附件。';
            try {
                send_mail((int) $r['uid'], '竞技场', 'arena', $title, $body, $att, $rk <= 3 ? 1 : 0);
            } catch (Throwable $e) {}
        }
    }

    // —— 全体（真人+机器人）按战力重排 1..N，强者靠前 ——
    arena_rerank_by_power($zone, $cur);

    // —— 给每个真人发一封「新赛季重置」提示，附上其按战力得到的新名次 ——
    if (function_exists('send_mail')) {
        $mine = db()->query('SELECT * FROM arena_rank WHERE zone=' . db()->quote($zone) . ' AND season=' . db()->quote($cur) . ' AND is_dummy=0 ORDER BY rank ASC')->fetchAll();
        foreach ($mine as $r) {
            $rk = (int) $r['rank'];
            $tier = arena_tier($rk);
            try {
                send_mail((int) $r['uid'], '竞技场', 'arena', '竞技场新赛季已开启', '新的竞技场赛季（' . date('Y年n月') . '）开始了！名次已按当前战力重新排布，你现在的名次是第' . $rk . '名（' . $tier[1] . '段位）。现在就可以继续挑战冲榜。', []);
            } catch (Throwable $e) {}
        }
    }
    $saveKey();
}

function arena_ensure(string $zone): void
{
    // 跨大区竞技场一次性迁移：历史各分大区的真人并入统一竞技场，清理旧分大区假人。
    try {
        $done = (int) (db()->query("SELECT num FROM sys_kv WHERE k='mig_arena_global'")->fetchColumn() ?: 0);
    } catch (Throwable $e) {
        $done = 0;
    }
    if ($done === 0) {
        try {
            db()->exec("UPDATE arena_rank SET zone='arena' WHERE is_dummy=0");
            db()->exec("DELETE FROM arena_rank WHERE is_dummy=1 AND zone<>'arena'");
            db()->exec("INSERT OR REPLACE INTO sys_kv (k, num) VALUES ('mig_arena_global', 1)");
        } catch (Throwable $e) {
        }
    }

    $season = arena_season();

    // 赛季兜底自愈：arena_rank 主键是 (uid, zone)，假人 uid 固定为负数(按名次)。
    // 若某周「周一结算 arena_tick」没跑到（服务器正好没人/错过05:00），旧赛季的假人和真人会卡在上一季；
    // 此时再按同一批 uid 播种会全部撞主键被忽略，导致「竞技场一个人都没有」。这里先把本区其它赛季的行
    // 整体并入当前赛季（保留 uid，改 season 即可，不撞主键），再按需补齐假人。
    $stale = (int) db()->query('SELECT COUNT(*) FROM arena_rank WHERE zone=' . db()->quote($zone) . ' AND season<>' . db()->quote($season))->fetchColumn();
    if ($stale > 0) {
        db()->prepare('UPDATE arena_rank SET season=? WHERE zone=? AND season<>?')->execute([$season, $zone, $season]);
    }

    // 每月赛季滚动兜底：跨月第一次被访问就发上月奖励+新赛季重排，不依赖月初那次 tick。
    arena_season_settle($zone);

    $n = (int) db()->query('SELECT COUNT(*) FROM arena_rank WHERE zone=' . db()->quote($zone) . ' AND is_dummy=1 AND season=' . db()->quote($season))->fetchColumn();
    $botN = arena_bot_count();
    if ($n >= $botN) {
        return;
    }
    $pool = arena_name_pool();
    $jobs = ['战士', '法师', '猎手', '牧师', '召唤师'];
    $used = [];
    $anchor = arena_pop_anchor($zone);
    // 新假人的 uid 从现有最小 uid 再往下分配，绝不与本区已有行撞主键
    $minUid = (int) db()->query('SELECT MIN(uid) FROM arena_rank WHERE zone=' . db()->quote($zone) . ' AND is_dummy=1')->fetchColumn();
    $nextUid = ($minUid < 0 ? $minUid : 0) - 1;
    $st = db()->prepare('INSERT OR IGNORE INTO arena_rank (uid, zone, rank, name, job, lv, power, is_dummy, season) VALUES (?, ?, ?, ?, ?, ?, ?, 1, ?)');
    for ($rank = $n + 1; $rank <= $botN; $rank++) {
        // 名字池有限，重试有限次 + 数字后缀兜底，避免池子耗尽时死循环。
        $nm = '';
        for ($try = 0; $try < 60; $try++) {
            $cand = $pool[array_rand($pool)];
            if (!isset($used[$cand])) {
                $nm = $cand;
                break;
            }
            $cand .= random_int(2, 9999);
            if (!isset($used[$cand])) {
                $nm = $cand;
                break;
            }
        }
        if ($nm === '') {
            $nm = $pool[array_rand($pool)] . '_' . $rank;
        }
        $used[$nm] = true;
        try {
            $st->execute([$nextUid--, $zone, $rank, $nm, $jobs[array_rand($jobs)], arena_dummy_lv_for($rank, $anchor), arena_dummy_power_for($rank, $anchor), $season]);
        } catch (Throwable $e) {
            break;
        }
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
    $climb = random_int(2, 4);
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
    // 2.5) 机器人互殴：随机挑几对机器人战斗，胜者往上爬一格，败者往下滑一格，让榜单更活跃。
    if (count($rows) >= 2) {
        $pairs = random_int(2, 4);
        for ($i = 0; $i < $pairs; $i++) {
            $a = $rows[array_rand($rows)];
            $b = $rows[array_rand($rows)];
            if ((int) $a['rowid'] === (int) $b['rowid']) {
                continue;
            }
            $ra = (int) $a['rank'];
            $rb = (int) $b['rank'];
            if ($ra === $rb) {
                continue;
            }
            // 战力加噪声决定胜负
            $pa = (int) $a['power'] + random_int(-80, 80);
            $pb = (int) $b['power'] + random_int(-80, 80);
            $worse = $ra > $rb ? $a : $b;
            $better = $ra > $rb ? $b : $a;
            $worsePow = $ra > $rb ? $pa : $pb;
            $betterPow = $ra > $rb ? $pb : $pa;
            if ($worsePow > $betterPow) {
                $worseRank = (int) $worse['rank'];
                $betterRank = (int) $better['rank'];
                db()->prepare('UPDATE arena_rank SET rank=? WHERE rowid=?')->execute([$betterRank, (int) $worse['rowid']]);
                db()->prepare('UPDATE arena_rank SET rank=? WHERE rowid=?')->execute([$worseRank, (int) $better['rowid']]);
            }
        }
    }
    // 3) 机器人主动挑战真人：有输有赢，赢了就抢真人的名次。
    arena_bots_attack_players($zone);
    // 4) 补位：真玩家离开/被清掉会留下空洞名次。把空洞上方的名次整体上移一格填平，榜单始终连续。
    arena_bots_compact($zone);
}

// 让排名靠前的机器人主动来打真人。打赢→和真人互换名次（真人掉名次），打输→名次不变。
// 每次刷新最多 1~2 次挑战，只挑“名次在真人前 1~30 名内”的机器人，且真人在 bot 后面（更靠后名次）才打得到。
function arena_bots_attack_players(string $zone): void
{
    $season = arena_season();
    // 本区真人
    $players = db()->query('SELECT rowid, uid, rank, name, lv, power FROM arena_rank WHERE zone=' . db()->quote($zone) . ' AND season=' . db()->quote($season) . ' AND is_dummy=0 ORDER BY rank ASC')->fetchAll();
    if ($players === []) {
        return;
    }
    $bots = db()->query('SELECT rowid, uid, rank, name, job, lv, power FROM arena_rank WHERE zone=' . db()->quote($zone) . ' AND season=' . db()->quote($season) . ' AND is_dummy=1 ORDER BY rank ASC')->fetchAll();
    if ($bots === []) {
        return;
    }
    $attempts = random_int(1, 3);
    for ($i = 0; $i < $attempts; $i++) {
        // 一半概率专门挑"排名靠前的真人"（给强号压力），一半随机
        if (mt_rand(0, 1) === 1) {
            $front = array_slice($players, 0, max(1, (int) ceil(count($players) / 2)));
            $target = $front[array_rand($front)];
        } else {
            $target = $players[array_rand($players)];
        }
        $tRank = (int) $target['rank'];
        // 名次靠前的真人也可以被紧邻其后的机器人挑战（机器人从下方往上爬），
        // 这样榜首/榜二也不会永远没人来打。
        // 找名次刚好在真人后面（更靠后）的机器人，模拟“它来挑战你”
        $cands = [];
        foreach ($bots as $b) {
            $br = (int) $b['rank'];
            if ($br > $tRank && $br <= $tRank + 30) {
                $cands[] = $b;
            }
        }
        if ($cands === []) {
            continue;
        }
        $bot = $cands[array_rand($cands)];
        // 机器人作为进攻方 vs 真人（防御方）
        $tu = user_by_id((int) $target['uid']);
        if (!$tu) {
            continue;
        }
        $defTeam = arena_my_team($tu);  // 真人战队（含 _power）
        $atkTeam = arena_bot_team_against($bot, $defTeam); // 机器人战队：按战力比对真人缩放，顶级机器人确实更强
        $botPow = max(1, (int) ($bot['power'] ?? 1));
        $defPow = max(1, (int) ($defTeam['_power'] ?? 0));
        $ratio = $botPow / $defPow;
        // 战力曲线：战力差距反映胜负概率，避免弱机器人靠随机干败强玩家。
        //   差距 >10%（机器人更弱）：机器人无法取胜（0%）
        //   差距 10% 以内：机器人允许 30% 胜率
        //   机器人更强：按模拟结果（战力占优方胜率更高）
        if ($ratio < 0.9) {
            $botWin = false;
        } elseif ($ratio < 1.0) {
            $botWin = (mt_rand(1, 100) <= 30);
        } else {
            [$botWin, $log] = arena_sim($atkTeam, $defTeam);
        }
        $botRank = (int) $bot['rank'];
        if ($botWin) {
            // 机器人胜：互换名次，真人掉到机器人原位
            db()->prepare('UPDATE arena_rank SET rank=? WHERE rowid=?')->execute([$tRank, (int) $bot['rowid']]);
            db()->prepare('UPDATE arena_rank SET rank=? WHERE rowid=?')->execute([$botRank, (int) $target['rowid']]);
            // 记到真人防守记录里（win=0 表示真人被打败）
            db()->prepare('INSERT INTO arena_log (zone, defender_uid, attacker_name, win, old_rank, new_rank, created_at) VALUES (?, ?, ?, 0, ?, ?, ?)')->execute([$zone, (int) $target['uid'], (string) $bot['name'], $tRank, $botRank, time()]);
            // 给真人发一封邮件提示（不打扰但能看到）
            if (function_exists('send_mail') && mt_rand(1, 100) <= 60) {
                try {
                    send_mail((int) $target['uid'], '竞技场', 'arena', '你被挑战了', '【' . $bot['name'] . '】在竞技场把你打下去了，你的名次从第' . $tRank . '名降到第' . $botRank . '名。打回来！', []);
                } catch (Throwable $e) {}
            }
            arena_announce((string) ($tu['zone'] ?? 'z1'), '【竞技场】机器人' . $bot['name'] . '挑战' . $target['name'] . '成功，排名上升至第' . $tRank . '名。');
            // 更新本地内存里的名次，避免同一轮重复用旧名次
            foreach ($players as &$p) { if ((int) $p['uid'] === (int) $target['uid']) { $p['rank'] = $botRank; } }
            unset($p);
            foreach ($bots as &$b) { if ((int) $b['uid'] === (int) $bot['uid']) { $b['rank'] = $tRank; } }
            unset($b);
        } else {
            // 机器人输：名次不变，也记一条（win=1 真人守住了）
            db()->prepare('INSERT INTO arena_log (zone, defender_uid, attacker_name, win, old_rank, new_rank, created_at) VALUES (?, ?, ?, 1, ?, ?, ?)')->execute([$zone, (int) $target['uid'], (string) $bot['name'], $tRank, $tRank, time()]);
        }
    }
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
    $zone = arena_zone();
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
    $zone = arena_zone();
    arena_ensure($zone);
    $season = arena_season();
    $st = db()->prepare('SELECT * FROM arena_rank WHERE uid=? AND zone=? AND season=?');
    $st->execute([(int) $uid, $zone, $season]);
    if ($st->fetch()) {
        return '你已经在竞技场了。';
    }
    $players = (int) db()->query('SELECT COUNT(*) FROM arena_rank WHERE zone=' . db()->quote($zone) . ' AND season=' . db()->quote($season) . ' AND is_dummy=0')->fetchColumn();
    $rank = arena_start_rank() + $players;
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
    $zone = arena_zone();
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
        '_power' => function_exists('power_score') ? (int) power_score($u) : 0,
    ];
}

function arena_foe_team(array $row): array
{
    if ((int) ($row['is_dummy'] ?? 0) === 1) {
        $pw = max(1, (int) ($row['power'] ?? 100));
        $lv = max(1, (int) ($row['lv'] ?? 1));
        $pets = ['影猫', '石灵', '幽灵', '幼龙', '深渊魔龙'];
        $d = max(0, (int) ($pw * 0.08));        $jobId = arena_job_id_from_name((string) ($row['job'] ?? '战士'));
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

// 机器人来「挑战真人」时用的战队：按“机器人战力 / 真人战力”比例，从真人实际队伍属性缩放而来。
// 这样顶级机器人(战力≈1.15×最强真人)实战确实略强、有赢有输；排名靠后的机器人 power 低则明显打不过。
// 不直接吃 power 的固定倍率，是为了避开“power_score 不含血量、与实战强度非线性”导致的失真。
function arena_bot_team_against(array $botRow, array $defTeam): array
{
    $botPow = max(1, (int) ($botRow['power'] ?? 1));
    $defPow = max(1, (int) ($defTeam['_power'] ?? 0));
    // 比例夹在 0.35~2.6，防止极端情况碾压或送死
    $factor = $defPow > 0 ? $botPow / $defPow : 1.0;
    $factor = max(0.35, min(2.6, $factor));
    $jobId = arena_job_id_from_name((string) ($botRow['job'] ?? '战士'));
    $isMag = in_array($jobId, ['mage', 'priest', 'summoner'], true);
    $lv = max(1, (int) ($botRow['lv'] ?? 1));
    $pets = ['影猫', '石灵', '幽灵', '幼龙', '深渊魔龙'];
    return [
        'name' => (string) ($botRow['name'] ?? '机器人'),
        'lv' => $lv,
        'hp' => max(1, (int) round((int) ($defTeam['hp'] ?? 1000) * $factor)),
        'atk' => max(1, (int) round((int) ($defTeam['atk'] ?? 100) * $factor)),
        'atk_type' => $isMag ? 'mag' : 'phys',
        'job' => $jobId, 'ult' => arena_job_ult($jobId),
        'pdef' => max(0, (int) round((int) ($defTeam['pdef'] ?? 0) * $factor)),
        'mdef' => max(0, (int) round((int) ($defTeam['mdef'] ?? 0) * $factor)),
        'def' => max(0, (int) round((int) ($defTeam['def'] ?? 0) * $factor)),
        'pet' => [ 'name' => $pets[$lv % count($pets)], 'lv' => max(1, $lv - 10), 'hp' => max(1, (int) round((int) ($defTeam['hp'] ?? 1000) * 0.12 * $factor)), 'atk' => max(1, (int) round((int) ($defTeam['atk'] ?? 100) * 0.15 * $factor)) ],
    ];
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
        // 2魔钻买1次（魔钻以 0.1 为单位存储，故扣 20）
        if ((int) ($u['diamonds'] ?? 0) < 20) {
            return '魔钻不够，2魔钻换1次。';
        }
        $u['diamonds'] = (int) $u['diamonds'] - 20;
        user_save($u);
        spend_diamonds((int) $uid, 20);
        add_mat((int) $uid, 'arena_bought', 1);
        return '花2魔钻买下1次挑战。';
    }
    return '没这种买法。';
}

// 竞技场对手立绘：真人对战用其头像，机器人用职业默认头像；没有则返回空串。
function arena_opp_avatar_html(array $t): string
{
    if (!function_exists('avatar_html')) {
        return '';
    }
    if ((int) ($t['is_dummy'] ?? 0) === 0) {
        $tu = user_by_id((int) ($t['uid'] ?? 0));
        if ($tu) {
            return avatar_html($tu, 40);
        }
    }
    $jobCn = (string) ($t['job'] ?? '');
    $jid = 'warrior';
    if (function_exists('jobs')) {
        foreach (jobs() as $id => $j) {
            if ((string) ($j['name'] ?? '') === $jobCn) {
                $jid = (string) $id;
                break;
            }
        }
    }
    return function_exists('avatar_render') ? avatar_render('job:' . $jid, 40) : '';
}

function arena_targets(array $my): array
{
    $zone = (string) ($my['zone'] ?? arena_zone());
    $season = (string) ($my['season'] ?? arena_season());
    $r = (int) $my['rank'];
    $lo = max(1, $r - 34);
    $st = db()->prepare('SELECT * FROM arena_rank WHERE zone=? AND season=? AND rank>=? AND rank<? ORDER BY rank ASC LIMIT 60');
    $st->execute([$zone, $season, $lo, $r]);
    $all = $st->fetchAll();
    // 优先把真人对手挑出来（让真实玩家在列表里可见），其余用 spread 补机器人到 5 个
    $real = [];
    $bots = [];
    foreach ($all as $t) {
        if ((int) ($t['is_dummy'] ?? 0) === 0) {
            $real[] = $t;
        } else {
            $bots[] = $t;
        }
    }
    $out = [];
    foreach ($real as $t) {
        if (count($out) >= 5) {
            break;
        }
        $out[] = $t;
    }
    if (count($out) < 5 && $bots !== []) {
        $need = 5 - count($out);
        if (count($bots) <= $need) {
            foreach ($bots as $b) {
                if (count($out) >= 5) {
                    break;
                }
                $out[] = $b;
            }
        } else {
            $out[] = $bots[0];
            $step = (count($bots) - 1) / max(1, ($need - 1));
            for ($i = 1; $i < $need; $i++) {
                $out[] = $bots[(int) round($i * $step)];
            }
        }
    }
    usort($out, fn($a, $b) => (int) $a['rank'] <=> (int) $b['rank']);
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
    $zone = (string) ($my['zone'] ?? arena_zone());
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
        return [false, ['今日挑战次数用完了（免费5次+购买' . $bought . '次）。花10金买1次，或2魔钻买1次。'], 0, $myRank, $myRank];
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
    // 名次奖励（每日05:00按名次发放）：[铜, 竞技场币]，不含属性石。
    // 最高第1名 1金/200竞技场币，依次下降。
    if ($rank <= 1) {
        return [10000, 200];
    }
    if ($rank <= 2) {
        return [8000, 180];
    }
    if ($rank <= 3) {
        return [6000, 160];
    }
    if ($rank <= 10) {
        return [4000, 120];
    }
    if ($rank <= 20) {
        return [3000, 90];
    }
    if ($rank <= 50) {
        return [2000, 60];
    }
    if ($rank <= 100) {
        return [1000, 40];
    }
    if ($rank <= 300) {
        return [600, 25];
    }
    if ($rank <= 700) {
        return [300, 15];
    }
    if ($rank <= 1000) {
        return [100, 8];
    }
    return [50, 5];
}

// 天梯段位分档：名次上限 => [key, 中文, 代表色]（按名次从小到大）。
// 竞技场已是 1000 名规模（真人从第 1000 名起爬），档位相应拉宽，冲榜更有层次。
function arena_tier_bounds(): array
{
    return [
        1 => ['king', '王者', '#ffcf40'],
        5 => ['master', '大师', '#c56bff'],
        20 => ['diamond', '钻石', '#5ad1ff'],
        60 => ['platinum', '铂金', '#7cf3d0'],
        150 => ['gold', '黄金', '#ffd84d'],
        350 => ['silver', '白银', '#c9d4e0'],
        700 => ['bronze', '青铜', '#c8895a'],
        PHP_INT_MAX => ['iron', '黑铁', '#8a8a8a'],
    ];
}

// 天梯段位：按排名划分。名次越小段位越高。
function arena_tier(int $rank): array
{
    foreach (arena_tier_bounds() as $max => $tier) {
        if ($rank <= $max) {
            return $tier;
        }
    }
    return ['iron', '黑铁', '#8a8a8a'];
}

// 段位奖励（每2周结算一次，邮件发放）：[金币(铜), 竞技场币]
// 王者=40金，依次下降；不奖励属性石。
function arena_tier_bonus(string $tierKey): array
{
    // [金, 竞技场币]
    $map = [
        'iron' => [0, 0], 'bronze' => [1, 3], 'silver' => [2, 5],
        'gold' => [5, 8], 'platinum' => [10, 12], 'diamond' => [15, 20],
        'master' => [25, 30], 'king' => [40, 50],
    ];
    [$g, $c] = $map[$tierKey] ?? [0, 0];
    return [$g * 10000, $c];
}

// 每月1日05:00结算月末赛季；每日05:00结算名次奖励
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
    $zone = arena_zone();
        arena_ensure($zone);
        $season = arena_season();
        $rows = db()->query('SELECT * FROM arena_rank WHERE zone=' . db()->quote($zone) . ' AND season=' . db()->quote($season) . ' AND is_dummy=0 ORDER BY rank ASC')->fetchAll();
        foreach ($rows as $r) {
            [$gold, $coin] = arena_rewards((int) $r['rank']);
            $tier = arena_tier((int) $r['rank']);
            $att = [['t' => 'mat', 'id' => 'arena_coin', 'n' => $coin]];
            if ($gold > 0) {
                $att[] = ['t' => 'gold', 'n' => $gold];
            }
            send_mail((int) $r['uid'], '竞技场', 'arena', '竞技场每日名次奖励：第' . $r['rank'] . '名', '今日排名第' . $r['rank'] . '名（' . $tier[1] . '段位），名次奖励：' . fmt_money($gold) . '、竞技场币x' . $coin . '。请查收附件。', $att);
        }
        // 段位奖励每2周结算一次（邮件）
        arena_tier_settle($zone);
        // 月末赛季结算已移到 arena_season_settle()：由 arena_ensure 在任意访问/结算时按月份变化触发，
        // 不再依赖「每月1号这一天的 tick 恰好跑到」。这里只保留每日名次结算 + 双周段位结算。
}

// 段位奖励结算周期：以 05:00 为界的「天序号」每 14 天一个周期
function arena_tier_period(): int
{
    return intdiv(arena_day_idx(), 14);
}

// 每 2 周给全体真人按当前段位发一次段位奖励（邮件）。首次运行只记基线，不追溯。
function arena_tier_settle(string $zone): void
{
    $period = arena_tier_period();
    $key = 'arena_tier_period_' . $zone;
    try {
        $rec = (int) (db()->query('SELECT num FROM sys_kv WHERE k=' . db()->quote($key))->fetchColumn() ?: 0);
    } catch (Throwable $e) {
        return;
    }
    if ($rec === 0) {
        try { db()->prepare('INSERT OR REPLACE INTO sys_kv (k, num) VALUES (?, ?)')->execute([$key, $period]); } catch (Throwable $e) {}
        return;
    }
    if ($rec >= $period) {
        return;
    }
    $season = arena_season();
    $rows = db()->query('SELECT * FROM arena_rank WHERE zone=' . db()->quote($zone) . ' AND season=' . db()->quote($season) . ' AND is_dummy=0 ORDER BY rank ASC')->fetchAll();
    foreach ($rows as $r) {
        $tier = arena_tier((int) $r['rank']);
        [$bg, $bc] = arena_tier_bonus($tier[0]);
        if ($bg <= 0 && $bc <= 0) {
            continue;
        }
        $att = [];
        if ($bc > 0) {
            $att[] = ['t' => 'mat', 'id' => 'arena_coin', 'n' => $bc];
        }
        if ($bg > 0) {
            $att[] = ['t' => 'gold', 'n' => $bg];
        }
        if ($att === []) {
            continue;
        }
        if (function_exists('send_mail')) {
            try {
                send_mail((int) $r['uid'], '竞技场', 'arena', '竞技场段位奖励：' . $tier[1] . '段位', '你在竞技场的段位为【' . $tier[1] . '】（第' . $r['rank'] . '名），段位奖励：' . fmt_money($bg) . '、竞技场币x' . $bc . '。请查收附件。', $att);
            } catch (Throwable $e) {}
        }
    }
    try { db()->prepare('INSERT OR REPLACE INTO sys_kv (k, num) VALUES (?, ?)')->execute([$key, $period]); } catch (Throwable $e) {}
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
