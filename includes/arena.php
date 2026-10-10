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

function arena_name_pool(): array
{
    $a = ['剑影', '风语', '夜行', '孤狼', '残阳', '墨白', '青衫', '断魂', '无痕', '铁血', '狂刀', '影舞', '龙吟', '虎啸', '鹤鸣', '狼牙', '鹰眼', '豹影', '熊罴', '蛇信', '霜月', '雪落', '雷震', '电掣', '云游', '雾隐', '星尘', '月影', '日曜', '辰砂', '苏木', '沉香', '檀香', '青瓷', '白釉', '玄铁', '赤铜', '流银', '鎏金', '寒玉', '温酒', '煮茶', '听雨', '观雪', '踏歌', '长歌', '短笛', '长箫', '古筝', '琵琶', '老酒鬼', '卖炭翁', '打铁匠', '放牛娃', '采药人', '守夜人', '摆渡人', '说书人', '跑堂的', '掌灯使', '执剑人', '仗剑走', '一蓑衣', '独钓翁', '醉卧沙', '笑红尘', '看云卷', '数星星', '追风者', '逐日者', '踏浪客', '翻云手', '覆雨剑', '摘星辰', '揽明月', '破军', '贪狼', '七杀', '天机', '紫微', '武曲', '廉贞', '文曲', '巨门', '天同', '铁牛', '二狗', '三炮', '大锤', '小石头', '狗剩', '栓柱', '铁蛋', '翠花', '二丫', '小莲', '阿福', '来福', '旺财', '大黄', '黑子', '白面书生', '青衣剑客', '红衣女侠', '布衣神相', '蓑衣客', '斗笠人', '蒙面客', '无名氏', '路人甲', '扫地僧', '砍柴人', '钓鱼翁'];
    $b = ['的剑', '的刀', '的影', '之怒', '之心', '传说', '不败', '无双', '逍遥', '自在'];
    // 组合出数千个不重样的真人风格名字
    $out = $a;
    foreach ($a as $i => $x) {
        $out[] = $x . $b[$i % count($b)];
    }
    return $out;
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
    if ($n >= 2000) {
        return;
    }
    db()->prepare('DELETE FROM arena_rank WHERE zone=? AND is_dummy=1 AND season=?')->execute([$zone, $season]);
    $pool = arena_name_pool();
    $jobs = ['战士', '法师', '猎手', '牧师'];
    $pets = ['影猫', '石灵', '幽灵', '幼龙', '深渊魔龙'];
    $used = [];
    $st = db()->prepare('INSERT INTO arena_rank (uid, zone, rank, name, job, lv, power, is_dummy, season) VALUES (0, ?, ?, ?, ?, ?, ?, 1, ?)');
    for ($r = 1; $r <= 2000; $r++) {
        do {
            $nm = $pool[array_rand($pool)];
            if (count($used) > 3000) {
                $nm .= random_int(2, 99);
            }
        } while (isset($used[$nm]));
        $used[$nm] = true;
        $st->execute([$zone, $r, $nm, $jobs[array_rand($jobs)], arena_dummy_lv($r), arena_dummy_power($r), $season]);
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

// 我方战阵：攻/防/血 + 出战宠物
function arena_my_team(array $u): array
{
    $pet = active_pet((int) ($u['id'] ?? 0));
    $p = null;
    if ($pet) {
        $ps = pet_stats($pet);
        $pn = pet_species()[$pet['species']]['name'] ?? $pet['species'];
        $p = ['name' => $pn, 'lv' => (int) $pet['level'], 'hp' => (int) $ps['maxhp'], 'atk' => (int) $ps['atk']];
    }
    return ['name' => (string) ($u['username'] ?? ''), 'lv' => (int) ($u['lv'] ?? 1), 'hp' => (int) ($u['maxhp'] ?? 1), 'atk' => player_atk($u), 'def' => player_def($u), 'pet' => $p];
}

function arena_foe_team(array $row): array
{
    if ((int) ($row['is_dummy'] ?? 0) === 1) {
        $pw = max(1, (int) ($row['power'] ?? 100));
        $lv = max(1, (int) ($row['lv'] ?? 1));
        $pets = ['影猫', '石灵', '幽灵', '幼龙', '深渊魔龙'];
        return [
            'name' => (string) $row['name'], 'lv' => $lv,
            'hp' => (int) ($pw * 0.5), 'atk' => max(1, (int) ($pw * 0.12)), 'def' => max(0, (int) ($pw * 0.08)),
            'pet' => ['name' => $pets[$lv % count($pets)], 'lv' => max(1, $lv - 10), 'hp' => (int) ($pw * 0.1), 'atk' => max(1, (int) ($pw * 0.03))],
        ];
    }
    $tu = user_by_id((int) $row['uid']);
    if (!$tu) {
        $pw = max(1, (int) ($row['power'] ?? 100));
        return ['name' => (string) $row['name'], 'lv' => (int) $row['lv'], 'hp' => (int) ($pw * 0.5), 'atk' => max(1, (int) ($pw * 0.12)), 'def' => max(0, (int) ($pw * 0.08)), 'pet' => null];
    }
    return arena_my_team($tu);
}

// 异步数值对撞：战力+宠物+骰子，返回[win, log[]]
function arena_sim(array $a, array $b): array
{
    $log = [];
    $ahp = $a['hp'];
    $bhp = $b['hp'];
    $alog = '你的队伍：' . $a['name'] . '，等级' . $a['lv'] . '，生命' . $a['hp'] . '，攻击' . $a['atk'] . '，防御' . $a['def'] . '。';
    if ($a['pet']) {
        $alog .= '宠物：' . $a['pet']['name'] . '，等级' . $a['pet']['lv'] . '，生命' . $a['pet']['hp'] . '，攻击' . $a['pet']['atk'] . '。';
    }
    $log[] = $alog;
    $blog = '对手队伍：' . $b['name'] . '，等级' . $b['lv'] . '，生命' . $b['hp'] . '，攻击' . $b['atk'] . '，防御' . $b['def'] . '。';
    if ($b['pet']) {
        $blog .= '宠物：' . $b['pet']['name'] . '，等级' . $b['pet']['lv'] . '，生命' . $b['pet']['hp'] . '，攻击' . $b['pet']['atk'] . '。';
    }
    $log[] = $blog;
    $log[] = '战斗开始。';
    $aphp = $a['pet']['hp'] ?? 0;
    $bphp = $b['pet']['hp'] ?? 0;
    $skills = ['重斩', '断岳斩', '火球术', '双连射', '普通攻击'];
    for ($r = 1; $r <= 30; $r++) {
        // 我方出手
        $d = max(1, (int) ($a['atk'] * (0.9 + mt_rand(0, 20) / 100) - $b['def'] * 0.5));
        if ($bphp > 0 && mt_rand(1, 100) <= 30) {
            $bphp -= $d;
            $log[] = '你使用' . $skills[array_rand($skills)] . '，对' . $b['pet']['name'] . '造成' . $d . '点伤害。';
            if ($bphp <= 0) {
                $log[] = $b['pet']['name'] . '倒下了！';
            }
        } else {
            $bhp -= $d;
            $log[] = '你使用' . $skills[array_rand($skills)] . '，对' . $b['name'] . '造成' . $d . '点伤害。';
        }
        if ($bhp <= 0) {
            $log[] = '战斗结束。你获胜。';
            return [true, $log];
        }
        if ($a['pet'] && $aphp > 0) {
            $pd = max(1, (int) ($a['pet']['atk'] * (0.9 + mt_rand(0, 20) / 100)));
            $bhp -= $pd;
            $log[] = $a['pet']['name'] . '扑向' . $b['name'] . '，造成' . $pd . '点伤害。';
            if ($bhp <= 0) {
                $log[] = '战斗结束。你获胜。';
                return [true, $log];
            }
        }
        // 对方出手
        $d2 = max(1, (int) ($b['atk'] * (0.9 + mt_rand(0, 20) / 100) - $a['def'] * 0.5));
        if ($aphp > 0 && $a['pet'] && mt_rand(1, 100) <= 30) {
            $aphp -= $d2;
            $log[] = $b['name'] . '使用盾击，对' . $a['pet']['name'] . '造成' . $d2 . '点伤害。';
            if ($aphp <= 0) {
                $log[] = $a['pet']['name'] . '倒下了！';
            }
        } else {
            $ahp -= $d2;
            $log[] = $b['name'] . '使用盾击，对你造成' . $d2 . '点伤害。';
        }
        if ($ahp <= 0) {
            $log[] = '战斗结束。你落败。';
            return [false, $log];
        }
        if ($b['pet'] && $bphp > 0) {
            $pd2 = max(1, (int) ($b['pet']['atk'] * (0.9 + mt_rand(0, 20) / 100)));
            $ahp -= $pd2;
            $log[] = $b['pet']['name'] . '使用普通攻击，对你造成' . $pd2 . '点伤害。';
            if ($ahp <= 0) {
                $log[] = '战斗结束。你落败。';
                return [false, $log];
            }
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
            $att = [['t' => 'mat', 'id' => 'arena_coin', 'n' => $coin]];
            foreach ($stones as $sid => $n) {
                $att[] = ['t' => 'mat', 'id' => $sid, 'n' => $n];
            }
            if ($gold > 0) {
                $att[] = ['t' => 'gold', 'n' => $gold];
            }
            send_mail((int) $r['uid'], '竞技场', 'arena', '竞技场每日奖励：第' . $r['rank'] . '名', '今日排名第' . $r['rank'] . '名，奖励：' . fmt_money($gold) . '、竞技场币x' . $coin . '。请查收附件。', $att);
        }
        if ($isMonday) {
            // 上周额外奖励：冠军翻倍概念——按名次再发一份
            foreach ($rows as $r) {
                [$gold, $coin, $stones] = arena_rewards((int) $r['rank']);
                $att = [['t' => 'mat', 'id' => 'arena_coin', 'n' => $coin]];
                if ($gold > 0) {
                    $att[] = ['t' => 'gold', 'n' => $gold];
                }
                send_mail((int) $r['uid'], '竞技场', 'arena', '竞技场赛季重置：上周第' . $r['rank'] . '名', '新赛季开始，你的排名回到初始状态。上周第' . $r['rank'] . '名的额外奖励请查收。', $att);
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
    if ($item === 'pet_food' || $item === 'egg_unknown' || str_starts_with($item, 'enhance_')) {
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
