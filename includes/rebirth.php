<?php
declare(strict_types=1);

// ============ 转生系统（999级后开启，最多3转）============

function rs_count(int $uid): int
{
    return max(0, min(3, (int) (cflags((int) $uid)['rs'] ?? 0)));
}

function rs_max(): int
{
    return 3;
}

function lv_cap(): int
{
    return 999;
}

function soul_max_lv(): int
{
    return 10;
}

// 等级前缀：0转=N级，1转=1转 1级
function lv_text(int $uid, ?int $lv = null): string
{
    $n = rs_count($uid);
    $lv = $lv ?? (int) (user_by_id($uid)['lv'] ?? 1);
    return $n > 0 ? $n . '转 ' . $lv . '级' : $lv . '级';
}

// 转生加成倍率：基础属性 × 转生次数 × 10%
function rs_stat_mult(int $uid): float
{
    return 1 + rs_count($uid) * 0.10;
}

// 经验+50% / 掉落+20% / 金币+30%
function rs_exp_mult(int $uid): float
{
    return 1 + rs_count($uid) * 0.5;
}

function rs_drop_mult(int $uid): float
{
    return 1 + rs_count($uid) * 0.2;
}

function rs_gold_mult(int $uid): float
{
    return 1 + rs_count($uid) * 0.3;
}

// 转生资格：999级 + 今日世界BOSS击杀 + 神魂满级
function rs_requirements(int $uid): array
{
    $u = user_by_id($uid);
    $lvOk = $u && (int) $u['lv'] >= lv_cap();
    $rsN = rs_count($uid);
    // 世界BOSS：当日已击杀（wb_done=1）或已刷新过（隔日重置）
    $wbDone = (int) (db()->query("SELECT num FROM sys_kv WHERE k='wb_done'")->fetchColumn() ?: 0);
    $wbDay = (int) (db()->query("SELECT num FROM sys_kv WHERE k='wb_day'")->fetchColumn() ?: 0);
    $wbHp = (int) (db()->query("SELECT num FROM sys_kv WHERE k='wb_hp'")->fetchColumn() ?: 0);
    $wbKill = ($wbDay === wb_day() && ($wbDone === 1 || $wbHp <= 0)) || $wbDay !== wb_day();
    $soulOk = rs_soul_maxed($uid);
    return [
        'lv' => $lvOk,
        'wb' => $wbKill,
        'soul' => $soulOk,
        'max' => $rsN >= rs_max(),
    ];
}

function rs_soul_maxed(int $uid): bool
{
    $rows = db()->prepare("SELECT id FROM equips WHERE uid=? AND slot='soul'");
    $rows->execute([(int) $uid]);
    $ids = $rows->fetchAll();
    if ($ids === []) {
        return false;
    }
    foreach ($ids as $r) {
        if (soul_lv((int) $uid, (int) $r['id']) < soul_max_lv()) {
            return false;
        }
    }
    return true;
}

function rs_require_text(int $uid): string
{
    $r = rs_requirements($uid);
    if ($r['max']) {
        return '你已达成最高转生（' . rs_max() . '转），世间的路到此为止。';
    }
    $t = [];
    if (!$r['lv']) {
        $t[] = '达到999级';
    }
    if (!$r['wb']) {
        $t[] = '击杀当日世界BOSS';
    }
    if (!$r['soul']) {
        $t[] = '任一神魂强化到' . soul_max_lv() . '级';
    }
    return $t === [] ? '条件齐备，随时可以转生。' : '还差：' . implode('、', $t);
}

function rs_can_rebirth(int $uid): bool
{
    $r = rs_requirements($uid);
    return $r['lv'] && $r['wb'] && $r['soul'] && !$r['max'];
}

// 执行转生
function rs_perform(int $uid): string
{
    $uid = (int) $uid;
    if (!rs_can_rebirth($uid)) {
        return '源罪未满：' . rs_require_text($uid);
    }
    $u = user_by_id($uid);
    if (!$u) {
        return '找不到你的角色。';
    }
    $n = rs_count($uid) + 1;
    $job = job_id_of($u);
    $base = jobs()[$job] ?? jobs()['warrior'];
    // 等级清零、属性与属性点回初始值
    db()->prepare('UPDATE users SET lv=1, exp=0, atk=?, def=?, hp=?, maxhp=?, mp=?, maxmp=?, str=0, agi=0, vit=0, `int`=0, spi=0, s_pts=0 WHERE id=?')
        ->execute([(int) $base['atk'], (int) $base['def'], (int) $base['hp'], (int) $base['hp'], (int) ($base['mp'] ?? 30), (int) ($base['mp'] ?? 30), $uid]);
    cflag_set($uid, 'rs', $n);
    // 源罪点数：每次转生+1
    mat_set($uid, 'rs_point', (int) (mats_of($uid)['rs_point'] ?? 0) + 1);
    _gear_uncache($uid);
    $u = user_by_id($uid);
    user_save($u);
    db()->prepare('INSERT INTO chat_msgs (uid, username, channel, target, text, created_at) VALUES (0, ?, "world", 0, ?, ?)')
        ->execute(['转生', '【转生】' . $u['username'] . ' 的神魂已觉醒，化为' . $n . '转！', time()]);
    return '【转生】你的神魂已觉醒。' . $n . '转 —— 等级归零，一切基础属性+' . ($n * 10) . '%，每级成长翻倍，经验+50%、掉落+20%、金币+30%，源罪点数+1。';
}

// ============ 源罪技能树（消耗源罪点数）============

function src_sin_skills(): array
{
    return [
        'power' => ['name' => '源罪之力', 'tier' => 1, 'desc' => '攻击力+5%'],
        'shield' => ['name' => '源罪之盾', 'tier' => 1, 'desc' => '生命值+5%'],
        'swift' => ['name' => '源罪之速', 'tier' => 1, 'desc' => '速度+5%'],
        'rage' => ['name' => '源罪之怒', 'tier' => 2, 'desc' => '生命低于30%时攻击力翻倍'],
        'guard' => ['name' => '源罪之守', 'tier' => 2, 'desc' => '生命低于30%时减伤+30%'],
        'flee' => ['name' => '源罪之逃', 'tier' => 2, 'desc' => '生命低于30%时闪避+30%'],
        'lord' => ['name' => '神之罪主', 'tier' => 3, 'desc' => '罪值获取减半，每点罪值+0.5%全伤'],
    ];
}

function src_sin_point(int $uid): int
{
    return max(0, (int) (mats_of((int) $uid)['rs_point'] ?? 0));
}

function src_sin_learned(int $uid): array
{
    $f = cflags((int) $uid);
    $raw = $f['src_sin'] ?? [];
    return is_array($raw) ? $raw : [];
}

// 源罪点数来自转生次数，同时允许技能树消耗
function src_sin_cost(string $sid): int
{
    return ['power' => 1, 'shield' => 1, 'swift' => 1, 'rage' => 2, 'guard' => 2, 'flee' => 2, 'lord' => 3][$sid] ?? 1;
}

// 2觉才解锁源罪树？→ 按需求：转生后解锁，即 rs>=1
function src_sin_unlock(int $uid): bool
{
    return rs_count($uid) >= 1;
}

function src_sin_learn(int $uid, string $sid): string
{
    $uid = (int) $uid;
    if (!src_sin_unlock($uid)) {
        return '源罪技能树需转生后解锁。';
    }
    $all = src_sin_skills();
    if (!isset($all[$sid])) {
        return '没有这个源罪技能。';
    }
    $own = src_sin_learned($uid);
    if (isset($own[$sid])) {
        return '你已学会【' . $all[$sid]['name'] . '】。';
    }
    // 前置：同层需先学一个二层的？→ 无前置，自由学，但点数有限
    $cost = src_sin_cost($sid);
    if (src_sin_point($uid) < $cost) {
        return '源罪点数不足（需' . $cost . '，当前' . src_sin_point($uid) . '）。每次转生+1点。';
    }
    mat_set($uid, 'rs_point', src_sin_point($uid) - $cost);
    $own[$sid] = time();
    cflag_set($uid, 'src_sin', $own);
    return '你学会了【' . $all[$sid]['name'] . '】：' . $all[$sid]['desc'] . '。';
}

// 源罪加成汇总
function src_sin_effects(int $uid): array
{
    $own = src_sin_learned($uid);
    $e = ['atk_pct' => 0.0, 'hp_pct' => 0.0, 'speed' => 0.0, 'rage' => false, 'guard' => false, 'flee' => false, 'lord' => false];
    if (isset($own['power'])) {
        $e['atk_pct'] += 5;
    }
    if (isset($own['shield'])) {
        $e['hp_pct'] += 5;
    }
    if (isset($own['swift'])) {
        $e['speed'] += 5;
    }
    if (isset($own['rage'])) {
        $e['rage'] = true;
    }
    if (isset($own['guard'])) {
        $e['guard'] = true;
    }
    if (isset($own['flee'])) {
        $e['flee'] = true;
    }
    if (isset($own['lord'])) {
        $e['lord'] = true;
    }
    return $e;
}

// ============ 转生排行榜（每周结算）============

function rs_rank(): array
{
    $rows = db()->query('SELECT id, username, chapter_flags, lv FROM users')->fetchAll();
    $out = [];
    foreach ($rows as $r) {
        $f = json_decode((string) ($r['chapter_flags'] ?? '{}'), true);
        $rs = max(0, min(rs_max(), (int) (is_array($f) ? ($f['rs'] ?? 0) : 0)));
        if ($rs <= 0) {
            continue;
        }
        $out[] = ['id' => (int) $r['id'], 'name' => (string) $r['username'], 'rs' => $rs, 'lv' => (int) $r['lv']];
    }
    usort($out, function ($a, $b) {
        return $b['rs'] <=> $a['rs'] ?: $b['lv'] <=> $a['lv'];
    });
    return $out;
}

function rs_week_key(): string
{
    $y = (int) date('oY');
    $w = (int) date('W');
    return $y . '-' . str_pad((string) $w, 2, '0', STR_PAD_LEFT);
}

function rs_rank_title(int $pos): array
{
    if ($pos === 1) {
        return ['name' => '源罪之神', 'diamonds' => 1000];
    }
    if ($pos === 2) {
        return ['name' => '源罪之主', 'diamonds' => 700];
    }
    if ($pos === 3) {
        return ['name' => '源罪之王', 'diamonds' => 500];
    }
    return ['name' => '源罪之将', 'diamonds' => 100];
}

// 每周结算：发奖+公告
function rs_weekly_settle(): string
{
    $key = rs_week_key();
    $done = (int) (db()->query("SELECT num FROM sys_kv WHERE k='rs_week'")->fetchColumn() ?: 0);
    if ((string) $done === $key) {
        return '';
    }
    $rank = rs_rank();
    $msg = [];
    foreach (array_slice($rank, 0, 10) as $i => $r) {
        $pos = $i + 1;
        $t = rs_rank_title($pos);
        rs_add_diamonds((int) $r['id'], $t['diamonds']);
        give_title((int) $r['id'], rs_title_id($pos));
        $msg[] = '第' . $pos . '名 ' . $r['name'] . '（' . $r['rs'] . '转' . $r['lv'] . '级）';
    }
    db()->prepare("INSERT OR REPLACE INTO sys_kv (k, num) VALUES ('rs_week', ?)")->execute([$key]);
    if ($msg !== []) {
        db()->prepare('INSERT INTO chat_msgs (uid, username, channel, target, text, created_at) VALUES (0, ?, "world", 0, ?, ?)')
            ->execute(['转生榜', '【转生榜结算】' . implode('；', $msg) . '。称号与魔钻已发放。', time()]);
    }
    return $msg === [] ? '' : '本周转生榜已结算。';
}

function rs_add_diamonds(int $uid, int $n, string $why = ''): void
{
    db()->prepare('UPDATE users SET diamonds=diamonds+? WHERE id=?')->execute([(int) $n, (int) $uid]);
}

// 称号id映射
function rs_title_id(int $pos): string
{
    $map = [1 => 'title_rs_god', 2 => 'title_rs_lord', 3 => 'title_rs_king', 4 => 'title_rs_gen', 5 => 'title_rs_gen', 6 => 'title_rs_gen', 7 => 'title_rs_gen', 8 => 'title_rs_gen', 9 => 'title_rs_gen', 10 => 'title_rs_gen'];
    return $map[$pos] ?? 'title_rs_gen';
}