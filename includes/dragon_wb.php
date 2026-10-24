<?php
declare(strict_types=1);

// ============ 龙王世界BOSS：每条龙独立血量/独立排行/独立掉落 ============
// 规则：
//  1) 龙王对自身属性伤害完全免疫（用同属性打它 → 伤害归零）
//  2) 龙王攻击全部为对应属性伤害
//  3) 击杀后按伤害排行邮件发奖：专属套装 / 专属宠物蛋 / 神魂碎片 / 本属性龙魂+龙玉

function drg_kv(string $k, int $default = 0): int
{
    $v = db()->query("SELECT num FROM sys_kv WHERE k='" . $k . "'")->fetchColumn();
    return $v === false || $v === null ? $default : (int) $v;
}

function drg_kv_set(string $k, int $v): void
{
    db()->prepare("INSERT OR REPLACE INTO sys_kv (k, num) VALUES (?, ?)")->execute([$k, $v]);
}

function drg_max_hp(string $mid): int
{
    $d = dragon($mid);
    return (int) ($d['hp'] ?? 30000000);
}

// 伤害排行表升级：单例 wb_dmg → 每条龙一份（mid 列）
function drg_migrate_table(): void
{
    $cols = db()->query("PRAGMA table_info(wb_dmg)")->fetchAll();
    if ($cols === []) {
        db()->exec('CREATE TABLE IF NOT EXISTS wb_dmg (id INTEGER PRIMARY KEY AUTOINCREMENT, uid INTEGER, name TEXT, day INTEGER, dmg INTEGER, mid TEXT)');
        db()->exec('CREATE INDEX IF NOT EXISTS idx_wb_dmg ON wb_dmg(day, mid, dmg)');
        return;
    }
    $has = false;
    foreach ($cols as $c) {
        if ((string) $c['name'] === 'mid') {
            $has = true;
        }
    }
    if (!$has) {
        db()->exec('ALTER TABLE wb_dmg ADD COLUMN mid TEXT');
        db()->exec("UPDATE wb_dmg SET mid='doyle' WHERE mid IS NULL OR mid=''");
        db()->exec('CREATE INDEX IF NOT EXISTS idx_wb_dmg ON wb_dmg(day, mid, dmg)');
    }
}

// 龙王复生：每条龙独立计时，击杀后一个月（30天）才重新出现
function drg_respawn_secs(): int
{
    return 30 * 86400;
}

// 距离下次复生还有多少秒（0 = 已可挑战/正在场）
function drg_respawn_left(string $mid): int
{
    if (drg_done($mid) !== 1) {
        return 0;
    }
    return max(0, drg_kv('wb_next_' . $mid) - time());
}

function drg_respawn_text(string $mid): string
{
    $s = drg_respawn_left($mid);
    if ($s <= 0) {
        return '';
    }
    $d = intdiv($s, 86400);
    $h = intdiv($s % 86400, 3600);
    $m = intdiv($s % 3600, 60);
    return $d > 0 ? $d . '天' . $h . '小时' : ($h > 0 ? $h . '小时' . $m . '分' : $m . '分');
}

// 巡检：把已满一个月的龙王重新拉起来（每条龙独立计时，互不等待）
function drg_tick(): void
{
    drg_migrate_table();
    foreach (array_keys(dragons()) as $mid) {
        $has = db()->prepare('SELECT num FROM sys_kv WHERE k=?');
        $has->execute(['wb_hp_' . $mid]);
        if ($has->fetchColumn() === false) {
            drg_kv_set('wb_hp_' . $mid, drg_max_hp($mid));
            drg_kv_set('wb_done_' . $mid, 0);
            drg_kv_set('wb_next_' . $mid, 0);
            continue;
        }
        if (drg_done($mid) === 1 && drg_respawn_left($mid) <= 0) {
            drg_respawn($mid);
        }
    }
}

function drg_respawn(string $mid): void
{
    drg_kv_set('wb_hp_' . $mid, drg_max_hp($mid));
    drg_kv_set('wb_done_' . $mid, 0);
    drg_kv_set('wb_next_' . $mid, 0);
    db()->prepare('DELETE FROM wb_dmg WHERE mid=?')->execute([(string) $mid]);
    $d = dragon($mid);
    if ($d !== []) {
        drg_world_chat('【六圣柱】沉睡了一个月的' . $d['name'] . '在' . $d['lair_name'] . '重新现世了——' . element_name((string) $d['el']) . '属性的龙，再次张开翅膀。');
    }
}

function drg_hp_now(string $mid): int
{
    return drg_kv('wb_hp_' . $mid);
}

function drg_done(string $mid): int
{
    return drg_kv('wb_done_' . $mid);
}

function drg_hurt(string $mid, int $dmg): int
{
    $max = drg_max_hp($mid);
    $left = max(0, drg_hp_now($mid) - max(0, $dmg));
    db()->prepare('UPDATE sys_kv SET num=? WHERE k=?')->execute([$left, 'wb_hp_' . $mid]);
    return $left;
}

// 全服公屏播报
function drg_world_chat(string $text, string $who = '六圣柱'): void
{
    db()->prepare('INSERT INTO chat_msgs (uid, username, channel, target, text, created_at) VALUES (0, ?, "world", 0, ?, ?)')
        ->execute([$who, $text, time()]);
}

// 斩龙：血量归零、标记已讨伐、锁定一个月复生、全服播报
function drg_kill(int $uid, string $mid): void
{
    drg_kv_set('wb_hp_' . $mid, 0);
    drg_kv_set('wb_done_' . $mid, 1);
    drg_kv_set('wb_next_' . $mid, time() + drg_respawn_secs());
    $d = dragon($mid);
    if ($d !== []) {
        $hero = (string) (user_by_id($uid)['username'] ?? '');
        drg_world_chat(
            '【六圣柱惊变】' . $d['name'] . '倒下了！最后一击来自 ' . ($hero !== '' ? $hero : '无名者') . '。'
            . $d['lair_name'] . '陷入死寂，它将在一个月后重新醒来——在那之前，' . element_name((string) $d['el']) . '属性的力量已归于沉寂。',
            '六圣柱'
        );
    }
}

// ---------- 伤害排行（每条龙各一份）----------
function drg_my_dmg(int $uid, string $mid, int $day = 0): int
{
    if ($day === 0) {
        $day = wb_day();
    }
    $st = db()->prepare('SELECT dmg FROM wb_dmg WHERE uid=? AND day=? AND mid=?');
    $st->execute([(int) $uid, $day, (string) $mid]);
    return (int) ($st->fetchColumn() ?: 0);
}

function drg_rank(string $mid, int $day = 0): array
{
    if ($day === 0) {
        $day = wb_day();
    }
    $st = db()->prepare('SELECT uid, name, dmg FROM wb_dmg WHERE day=? AND mid=? ORDER BY dmg DESC LIMIT 20');
    $st->execute([$day, (string) $mid]);
    return $st->fetchAll();
}

function drg_add_dmg(int $uid, string $name, string $mid, int $dmg): void
{
    $day = wb_day();
    $st = db()->prepare('SELECT dmg FROM wb_dmg WHERE uid=? AND day=? AND mid=?');
    $st->execute([(int) $uid, $day, (string) $mid]);
    if ($st->fetchColumn() === false) {
        db()->prepare('INSERT INTO wb_dmg (uid, name, day, dmg, mid) VALUES (?, ?, ?, ?, ?)')->execute([(int) $uid, (string) $name, $day, max(0, $dmg), (string) $mid]);
    } else {
        db()->prepare('UPDATE wb_dmg SET dmg=dmg+?, name=? WHERE uid=? AND day=? AND mid=?')->execute([max(0, $dmg), (string) $name, (int) $uid, $day, (string) $mid]);
    }
}

// ---------- 属性克制：龙王对自身属性完全免疫 ----------
// 传入本次攻击的元素，返回实际伤害（同属性 → 伤害归零）
function drg_damage_vs(string $dragonEl, string $atkEl, int $dmg): int
{
    if ($atkEl === '' || $atkEl === 'phys' || $atkEl !== $dragonEl) {
        return $dmg;
    }
    return 0;
}

// 玩家本次出手的元素：无元素技能的物攻打空，只有附魔技能才吃对应克制
function drg_atk_el(?array $sk): string
{
    $el = (string) ($sk['el'] ?? '');
    return in_array($el, dragon_elements(), true) ? $el : '';
}

// 龙王当前状态：血量/是否今日已讨伐/百分比
function drg_status(string $mid): array
{
    $max = drg_max_hp($mid);
    $hp = drg_hp_now($mid);
    if ($hp <= 0 && drg_done($mid) !== 1) {
        $hp = $max;
    }
    return [
        'hp' => $hp,
        'max' => $max,
        'done' => $hp <= 0 || drg_done($mid) === 1 ? 1 : 0,
        'pct' => $max > 0 ? max(0, min(100, (int) round($hp / $max * 100))) : 0,
    ];
}

// 进场校验：等级门槛即可。龙王属性已经够变态，不再收门票，鼓励全服车轮战。
function drg_enter(array &$u, string $mid): string
{
    $d = dragon($mid);
    if ($d === []) {
        return '没有这条龙。';
    }
    $uid = (int) $u['id'];
    if ((int) $u['lv'] < dragon_min_lv($mid)) {
        return $d['name'] . '懒得理' . (int) $u['lv'] . '级的你——' . dragon_min_lv($mid) . '级再来。';
    }
    return '';
}

// ---------- 龙王出手（全部为对应属性伤害）----------
// 龙王招式：每条龙都有自己的一套招牌技能，不再是按元素共用的模板。
// 每条龙 3-4 招，含专属名号与特殊效果；多伊尔用老的多用途结构（含灼烧）。
function drg_boss_move(string $mid_or_el): array
{
    // 用 mid 精确匹配；兼容旧调用（直接传元素）时回落到元素表
    $byMid = [
        'doyle' => [
            ['name' => '龙爪撕裂', 'mult' => 1.0],
            ['name' => '烬焰吐息', 'mult' => 1.5, 'burn' => 3],
            ['name' => '灰烬风暴', 'mult' => 0.8],
            ['name' => '南炎柱·焚世', 'mult' => 2.2, 'burn' => 2],
        ],
        'aetherine' => [
            ['name' => '罡风撕裂', 'mult' => 1.0],
            ['name' => '苍岚斩', 'mult' => 1.5],
            ['name' => '天穹风刃', 'mult' => 0.9],
            ['name' => '西风柱·万刃归寂', 'mult' => 2.2],
        ],
        'ysolde' => [
            ['name' => '冰棱贯穿', 'mult' => 1.0],
            ['name' => '永冻吐息', 'mult' => 1.5],
            ['name' => '哀歌回响', 'mult' => 0.9],
            ['name' => '北寒柱·千古冰封', 'mult' => 2.2],
        ],
        'rex' => [
            ['name' => '雷霆轰击', 'mult' => 1.0],
            ['name' => '天谴之鞭', 'mult' => 1.6],
            ['name' => '碎星爆闪', 'mult' => 0.9],
            ['name' => '东雷柱·九霄落雷', 'mult' => 2.3],
        ],
        'nidhogg' => [
            ['name' => '暗影腐蚀', 'mult' => 1.0],
            ['name' => '噬界之爪', 'mult' => 1.5],
            ['name' => '腐朽蔓延', 'mult' => 0.9],
            ['name' => '深渊柱·万鬼归渊', 'mult' => 2.2],
        ],
        'aurelian' => [
            ['name' => '圣光贯穿', 'mult' => 1.0],
            ['name' => '裁决之光', 'mult' => 1.5],
            ['name' => '天罚降世', 'mult' => 0.9],
            ['name' => '中央柱·净世圣裁', 'mult' => 2.2],
        ],
    ];
    $elTable = [
        'fire' => [['name' => '龙爪撕裂', 'mult' => 1.0, 'burn' => 0], ['name' => '烬焰吐息', 'mult' => 1.5, 'burn' => 3], ['name' => '灰烬风暴', 'mult' => 0.8, 'burn' => 0]],
        'wind' => [['name' => '罡风撕裂', 'mult' => 1.0], ['name' => '苍岚斩', 'mult' => 1.4], ['name' => '天穹风刃', 'mult' => 0.8]],
        'ice' => [['name' => '冰棱贯穿', 'mult' => 1.0], ['name' => '永冻吐息', 'mult' => 1.5], ['name' => '哀歌回响', 'mult' => 0.8]],
        'thunder' => [['name' => '雷霆轰击', 'mult' => 1.0], ['name' => '天谴之鞭', 'mult' => 1.6], ['name' => '碎星爆闪', 'mult' => 0.8]],
        'dark' => [['name' => '暗影腐蚀', 'mult' => 1.0], ['name' => '噬界之爪', 'mult' => 1.5], ['name' => '腐朽蔓延', 'mult' => 0.8]],
        'light' => [['name' => '圣光贯穿', 'mult' => 1.0], ['name' => '裁决之光', 'mult' => 1.5], ['name' => '天罚降世', 'mult' => 0.8]],
    ];
    $dd = dragon($mid_or_el);
    if ($dd !== []) {
        $table = $byMid[$mid_or_el] ?? ($elTable[(string) $dd['el']] ?? [['name' => '龙爪', 'mult' => 1.0]]);
        $mv = $table[array_rand($table)];
        $mv['el'] = (string) $dd['el'];
        return $mv;
    }
    $table = $elTable[$mid_or_el] ?? [['name' => '龙爪', 'mult' => 1.0]];
    $mv = $table[array_rand($table)];
    $mv['el'] = $mid_or_el;
    return $mv;
}

// ---------- 掉落：按排行发奖 ----------
function drg_rewards(string $mid): void
{
    $d = dragon($mid);
    if ($d === []) {
        return;
    }
    $rank = drg_rank($mid);
    if ($rank === []) {
        return;
    }
    $pieces = dragon_set_pieces((string) $d['set']);
    $slots = function_exists('dragon_piece_slot_map') ? dragon_piece_slot_map() : dragon_piece_slots();
    $elName = element_name((string) $d['el']);

    foreach ($rank as $i => $row) {
        $uid = (int) $row['uid'];
        $place = $i + 1;
        // 每位参与者都拿龙魂+龙玉（保证转生材料可累积）
        add_mat($uid, (string) $d['soul'], 1);
        add_mat($uid, (string) $d['jade'], 1);

        if ($place === 1) {
            add_mat($uid, (string) $d['egg'], 1);
            $pc1 = $pieces[array_rand($pieces)];
            make_equip($uid, $slots[$pc1] ?? 'weapon', $pc1, 4, 700);
            $u = user_by_id($uid);
            if ($u) {
                $u['diamonds'] = (int) ($u['diamonds'] ?? 0) + 100;
                $u['gold'] = (int) ($u['gold'] ?? 0) + 5000000;
                user_save($u);
            }
            add_mat($uid, 'soul_shard', 30);
            send_mail($uid, '世界BOSS·' . $d['name'], 'wb', '斩龙首功', '你对' . $d['name'] . '造成全服第一的伤害！' . (string) (pet_eggs()[$d['egg']]['name'] ?? ('传奇' . $d['short'] . '宠物蛋')) . '×1、' . $d['set_name'] . '随机1件、' . $elName . '龙魂×1、龙玉×1、100魔钻、500银、神魂碎片×30！', []);
        } elseif ($place <= 3) {
            $pc = $pieces[array_rand($pieces)];
            make_equip($uid, $slots[$pc] ?? 'weapon', $pc, 3, 680);
            $u = user_by_id($uid);
            if ($u) {
                $u['diamonds'] = (int) ($u['diamonds'] ?? 0) + 50;
                $u['gold'] = (int) ($u['gold'] ?? 0) + 2000000;
                user_save($u);
            }
            add_mat($uid, 'soul_shard', 20);
            send_mail($uid, '世界BOSS·' . $d['name'], 'wb', '斩龙前三', '第' . $place . '名！' . $d['set_name'] . '随机1件、' . $elName . '龙魂×1、龙玉×1、50魔钻、200银、神魂碎片×20！', []);
        } elseif ($place <= 10) {
            $u = user_by_id($uid);
            if ($u) {
                $u['diamonds'] = (int) ($u['diamonds'] ?? 0) + 20;
                $u['gold'] = (int) ($u['gold'] ?? 0) + 800000;
                user_save($u);
            }
            add_mat($uid, 'soul_shard', 10);
            if (mt_rand(1, 100) <= 30) {
                $pc = $pieces[array_rand($pieces)];
                make_equip($uid, $slots[$pc] ?? 'weapon', $pc, 2, 650);
            }
            send_mail($uid, '世界BOSS·' . $d['name'], 'wb', '斩龙有功', '第' . $place . '名！' . $elName . '龙魂×1、龙玉×1、20魔钻、80银、神魂碎片×10（30%概率再掉' . $d['set_name'] . '件）！', []);
        } else {
            $u = user_by_id($uid);
            if ($u) {
                $u['diamonds'] = (int) ($u['diamonds'] ?? 0) + 5;
                user_save($u);
            }
            add_mat($uid, 'soul_shard', 3);
            send_mail($uid, '世界BOSS·' . $d['name'], 'wb', '见龙有份', '参与奖：' . $elName . '龙魂×1、龙玉×1、5魔钻、神魂碎片×3！', []);
        }
    }
}

// 套装件默认部位（兼容旧调用；以 dragon_set_layout 为准）
function dragon_piece_slots(): array
{
    return function_exists('dragon_piece_slot_map') ? dragon_piece_slot_map() : [];
}