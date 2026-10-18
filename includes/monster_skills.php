<?php
declare(strict_types=1);

// ============ 怪物/首领专属技能 ============
// 战斗报要让玩家知道"我被什么打了"。普通小怪只有招牌动作；首领/BOSS 有 2-4 个
// 独有技能：轮到怪出手时掷骰，命中则放技能（带名字、倍率、附加状态），轮空则普攻。
// 每轮显示怪物的出手动作 + 你受到的伤害，不再只剩一句掉血。
//
// 技能字段：
//   name   在战报里显示的技能名
//   rate   每轮放出的概率（%）
//   mult   伤害倍率（相对怪物本轮普攻）
//   el     可选：转成某系属性伤害，吃玩家同系抗性（不填则沿用怪物的本系）
//   dot    可选：[轮数, 每轮玩家最大生命%]，给你挂持续伤害
//   drain  可选：% 值，按本次伤害回血
//   guard  可选：轮数，怪架盾 N 轮（期间受伤-40%），下一轮你的出手日志里会提示
//   adds   可选：只数，召唤小怪加入围殴（left+1，经验金币正常结算，不掉装备）
//   rush   可选：true，这轮怪打两次（第二下只有一半）——BOSS 压血线专用

function monster_skills(): array
{
    return [
        // —— 第一章 ——
        'rat' => [
            'atk' => '啃咬',
            'skills' => [
                ['name' => '腐牙啃咬', 'rate' => 20, 'mult' => 1.6],
            ],
        ],
        'wolf' => [
            'atk' => '撕咬',
            'skills' => [
                ['name' => '荒原扑杀', 'rate' => 25, 'mult' => 1.6],
            ],
        ],
        'skeleton' => [
            'atk' => '锈剑劈砍',
            'skills' => [
                ['name' => '锈甲冲锋', 'rate' => 20, 'mult' => 1.7],
            ],
        ],
        'cultist' => [
            'atk' => '低语咒火',
            'skills' => [
                ['name' => '深渊低语', 'rate' => 25, 'mult' => 1.5, 'dot' => [2, 2]],
            ],
        ],
        'bat' => [
            'atk' => '群蝠吸血',
            'skills' => [
                ['name' => '血雾围猎', 'rate' => 20, 'mult' => 1.4, 'drain' => 50],
            ],
        ],
        'ghoul' => [
            'atk' => '利爪撕裂',
            'skills' => [
                ['name' => '尸毒撕裂', 'rate' => 20, 'mult' => 1.5, 'dot' => [2, 2]],
            ],
        ],
        // —— 沼泽之王霍克（深水王座）——
        'swamp_king' => [
            'atk' => '王权巨掌',
            'skills' => [
                ['name' => '沼泽王权', 'rate' => 30, 'mult' => 2.0, 'dot' => [3, 3]],
                ['name' => '腐殖泥甲', 'rate' => 15, 'mult' => 0.5, 'guard' => 2],
            ],
        ],
        // —— BOSS·堕落骑士莫尔 ——
        'knight' => [
            'atk' => '锈剑横扫',
            'skills' => [
                ['name' => '堕天十字斩', 'rate' => 30, 'mult' => 2.0],
                ['name' => '锈蚀壁垒', 'rate' => 15, 'mult' => 0.5, 'guard' => 2],
            ],
        ],
        // —— 第二章 ——
        'shadow_hound' => [
            'atk' => '影牙撕咬',
            'skills' => [
                ['name' => '影遁背刺', 'rate' => 25, 'mult' => 1.8],
            ],
        ],
        'gargoyle' => [
            'atk' => '石翼拍击',
            'skills' => [
                ['name' => '雷石坠击', 'rate' => 25, 'mult' => 1.8, 'el' => 'thunder'],
            ],
        ],
        'apostle' => [
            'atk' => '蚀影鞭挞',
            'skills' => [
                ['name' => '使徒低语', 'rate' => 25, 'mult' => 1.6, 'dot' => [3, 2]],
            ],
        ],
        'guardian' => [
            'atk' => '巨拳碾压',
            'skills' => [
                ['name' => '巨像践踏', 'rate' => 25, 'mult' => 2.0],
                ['name' => '符文护壳', 'rate' => 15, 'mult' => 0.5, 'guard' => 2],
            ],
        ],
        // —— BOSS·城主瓦伦丁 ——
        'valentin' => [
            'atk' => '贵族刺剑',
            'skills' => [
                ['name' => '血宴邀约', 'rate' => 30, 'mult' => 2.2, 'drain' => 50],
                ['name' => '城主的晚宴', 'rate' => 15, 'mult' => 1.2, 'dot' => [3, 3]],
            ],
        ],
        // —— BOSS·暗影莉莉 ——
        'dark_lily' => [
            'atk' => '影瓣飞旋',
            'skills' => [
                ['name' => '凋零花葬', 'rate' => 30, 'mult' => 2.2, 'dot' => [3, 3]],
                ['name' => '暗影绽放', 'rate' => 15, 'mult' => 1.5, 'adds' => 1],
            ],
        ],
        // —— BOSS·深渊之眼 ——
        'abyss_eye' => [
            'atk' => '凝视灼烧',
            'skills' => [
                ['name' => '深渊凝视', 'rate' => 30, 'mult' => 2.0, 'dot' => [2, 4]],
                ['name' => '万眼开阖', 'rate' => 15, 'mult' => 1.6, 'rush' => true],
            ],
        ],
        // —— BOSS·影蚀君王阿巴顿 ——
        'abaddon' => [
            'atk' => '君王重劈',
            'skills' => [
                ['name' => '影蚀降临', 'rate' => 30, 'mult' => 2.4],
                ['name' => '君王敕令', 'rate' => 15, 'mult' => 1.2, 'adds' => 1],
                ['name' => '蚀影吞噬', 'rate' => 10, 'mult' => 1.8, 'drain' => 100],
            ],
        ],
        // —— 第三章 ——
        'voidboss' => [
            'atk' => '虚空撕裂',
            'skills' => [],
        ],
        // —— BOSS·牵线者 ——
        'string_puller' => [
            'atk' => '提线切割',
            'skills' => [
                ['name' => '提线木偶戏', 'rate' => 30, 'mult' => 2.0, 'dot' => [3, 2]],
                ['name' => '傀儡入场', 'rate' => 15, 'mult' => 1.0, 'adds' => 1],
            ],
        ],
        // —— BOSS·缠丝之母 ——
        'silk_mother' => [
            'atk' => '蛛丝绞杀',
            'skills' => [
                ['name' => '万丝缠身', 'rate' => 30, 'mult' => 2.0, 'dot' => [3, 3]],
                ['name' => '育巢孵化', 'rate' => 15, 'mult' => 1.0, 'adds' => 2],
            ],
        ],
        // —— 第四章 ——
        'blizzard_shaman' => [
            'atk' => '冰锥连射',
            'skills' => [
                ['name' => '暴风雪幕', 'rate' => 25, 'mult' => 1.7, 'el' => 'ice'],
            ],
        ],
        'frost_giant' => [
            'atk' => '冰霜巨拳',
            'skills' => [
                ['name' => '冰川崩塌', 'rate' => 25, 'mult' => 2.0, 'el' => 'ice'],
            ],
        ],
        // —— BOSS·凛冬之王 ——
        'winter_king' => [
            'atk' => '寒霜王剑',
            'skills' => [
                ['name' => '凛冬审判', 'rate' => 30, 'mult' => 2.4, 'el' => 'ice'],
                ['name' => '永冻王座', 'rate' => 15, 'mult' => 0.5, 'guard' => 2],
                ['name' => '冰河葬礼', 'rate' => 10, 'mult' => 1.5, 'dot' => [3, 4]],
            ],
        ],
        // —— BOSS·冰渊利维坦 ——
        'fr_leviathan' => [
            'atk' => '深渊巨口',
            'skills' => [
                ['name' => '利维坦吞噬', 'rate' => 30, 'mult' => 2.4, 'drain' => 50],
                ['name' => '冰渊漩涡', 'rate' => 15, 'mult' => 1.8, 'rush' => true],
            ],
        ],
        // —— 第五章 ——
        'magma_golem' => [
            'atk' => '熔岩重拳',
            'skills' => [
                ['name' => '熔核爆发', 'rate' => 25, 'mult' => 1.8, 'el' => 'fire'],
            ],
        ],
        'ash_wyrm' => [
            'atk' => '灰烬吐息',
            'skills' => [
                ['name' => '烬风吐息', 'rate' => 25, 'mult' => 1.8, 'dot' => [3, 3]],
            ],
        ],
        'ancient_ruin_golem' => [
            'atk' => '遗迹碾压',
            'skills' => [
                ['name' => '遗迹镇压', 'rate' => 25, 'mult' => 2.0],
                ['name' => '古石护壳', 'rate' => 15, 'mult' => 0.5, 'guard' => 2],
            ],
        ],
        // —— BOSS·烬焰领主多伊尔 ——
        'doyle' => [
            'atk' => '龙爪撕裂',
            'skills' => [
                ['name' => '烬天龙息', 'rate' => 30, 'mult' => 2.6, 'el' => 'fire', 'dot' => [3, 3]],
                ['name' => '龙裔召唤', 'rate' => 15, 'mult' => 1.0, 'adds' => 1],
                ['name' => '熔火复苏', 'rate' => 10, 'mult' => 0.8, 'drain' => 150],
            ],
        ],
        // —— BOSS·熔心化身 ——
        'em_heart' => [
            'atk' => '熔心脉冲',
            'skills' => [
                ['name' => '熔心爆裂', 'rate' => 30, 'mult' => 2.6, 'el' => 'fire'],
                ['name' => '岩浆灌注', 'rate' => 15, 'mult' => 1.2, 'dot' => [4, 3]],
            ],
        ],
        // —— 其他首领 ——
        'echo_rayne' => [
            'atk' => '回响低语',
            'skills' => [
                ['name' => '回响侵蚀', 'rate' => 25, 'mult' => 1.6, 'dot' => [2, 3]],
            ],
        ],
        'pz_master' => [
            'atk' => '傀儡总谱',
            'skills' => [
                ['name' => '千偶夜行', 'rate' => 30, 'mult' => 2.0, 'adds' => 1],
                ['name' => '提线总纲', 'rate' => 15, 'mult' => 1.6, 'dot' => [3, 3]],
            ],
        ],
        'sen_avatar' => [
            'atk' => '荆棘绞刑',
            'skills' => [
                ['name' => '荆棘王庭', 'rate' => 30, 'mult' => 2.2, 'dot' => [3, 3]],
                ['name' => '腐殖再生', 'rate' => 12, 'mult' => 0.8, 'drain' => 120],
            ],
        ],
        'shadow_claw' => [
            'atk' => '影爪连击',
            'skills' => [
                ['name' => '夜幕撕裂', 'rate' => 30, 'mult' => 2.2, 'rush' => true],
            ],
        ],
        'brass_bishop' => [
            'atk' => '黄铜权杖',
            'skills' => [
                ['name' => '赦令神罚', 'rate' => 30, 'mult' => 2.2, 'el' => 'light'],
                ['name' => '圣铜壁垒', 'rate' => 15, 'mult' => 0.5, 'guard' => 2],
            ],
        ],
        'tundra_wanderer' => [
            'atk' => '苔原长矛',
            'skills' => [
                ['name' => '风雪远行', 'rate' => 25, 'mult' => 1.8, 'el' => 'ice'],
            ],
        ],
        'avalanche_titan' => [
            'atk' => '雪崩巨掌',
            'skills' => [
                ['name' => '雪崩倾泻', 'rate' => 30, 'mult' => 2.4, 'el' => 'ice'],
            ],
        ],
        'obsidian_colossus' => [
            'atk' => '黑曜重拳',
            'skills' => [
                ['name' => '灭世碾压', 'rate' => 30, 'mult' => 2.4],
                ['name' => '曜石铠甲', 'rate' => 15, 'mult' => 0.5, 'guard' => 2],
            ],
        ],
        'ash_titan' => [
            'atk' => '灰烬巨拳',
            'skills' => [
                ['name' => '烬灭之握', 'rate' => 30, 'mult' => 2.4, 'el' => 'fire', 'dot' => [3, 3]],
            ],
        ],
        // —— 神界/终局 ——
        'god_avatar' => [
            'atk' => '神座裁决',
            'skills' => [
                ['name' => '神座审判', 'rate' => 30, 'mult' => 2.4, 'el' => 'light'],
                ['name' => '天国军势', 'rate' => 15, 'mult' => 1.2, 'adds' => 2],
                ['name' => '神圣庇佑', 'rate' => 12, 'mult' => 0.5, 'guard' => 2],
            ],
        ],
        'god_emperor' => [
            'atk' => '堕天魔剑',
            'skills' => [
                ['name' => '堕天断罪', 'rate' => 30, 'mult' => 2.6, 'el' => 'dark'],
                ['name' => '路西法之晨星', 'rate' => 15, 'mult' => 2.0, 'rush' => true],
                ['name' => '深渊王座', 'rate' => 12, 'mult' => 0.8, 'drain' => 150],
            ],
        ],
        'yz_boss' => [
            'atk' => '本相碾压',
            'skills' => [
                ['name' => '万罪加身', 'rate' => 30, 'mult' => 2.6, 'dot' => [4, 4]],
                ['name' => '源罪本相', 'rate' => 15, 'mult' => 2.0, 'rush' => true],
                ['name' => '罪渊回响', 'rate' => 12, 'mult' => 1.2, 'adds' => 2],
            ],
        ],
        // —— 虚空暴君（虚空核心，999级世界BOSS）——
        'vx_tyrant' => [
            'atk' => '虚空之握',
            'skills' => [
                ['name' => '虚空吞噬', 'rate' => 28, 'mult' => 2.5, 'el' => 'dark', 'drain' => 100],
                ['name' => '维度裂隙', 'rate' => 18, 'mult' => 1.8, 'rush' => true],
                ['name' => '暴君律令', 'rate' => 12, 'mult' => 1.2, 'adds' => 2],
                ['name' => '虚空壁垒', 'rate' => 10, 'mult' => 0.5, 'guard' => 2],
            ],
        ],
        // —— 岩狱泰坦·土龙岳柱（岩狱核心）——
        'ry_titan' => [
            'atk' => '岩狱崩拳',
            'skills' => [
                ['name' => '山岳倾轧', 'rate' => 30, 'mult' => 2.6],
                ['name' => '崩裂大地', 'rate' => 16, 'mult' => 1.8, 'dot' => [3, 3]],
                ['name' => '岳柱壁垒', 'rate' => 12, 'mult' => 0.5, 'guard' => 2],
            ],
        ],
    ];
}

// 怪物的招牌普攻动作（没抽中技能时显示）
function monster_atk_name(string $mid): string
{
    $cfg = monster_skills()[$mid] ?? null;
    return $cfg['atk'] ?? '攻击';
}

// 每轮怪物出手：先结算你身上的怪DOT，再掷骰决定普攻还是技能。
// 返回 [伤害增量, 战报片段, 是否连打, 本轮技能名]
function monster_skill_turn(array &$u, array &$b, int $base): array
{
    $mid = (string) ($b['id'] ?? '');
    $mname = (string) ($b['name'] ?? '怪物');
    $log = '';
    // DOT 单独算，不能被技能倍率放大
    $dotDmg = 0;

    // 你身上的怪DOT（上轮技能挂的）
    if (!empty($b['mdot']) && (int) $u['hp'] > 0) {
        $dturns = (int) ($b['mdot']['turns'] ?? 0);
        if ($dturns > 0) {
            $dotDmg = max(1, (int) ((int) $u['maxhp'] * (float) ($b['mdot']['pct'] ?? 2) / 100));
            $b['mdot']['turns'] = $dturns - 1;
            $log .= (string) ($b['mdot']['name'] ?? '余毒') . '发作，你掉' . $dotDmg . '点生命。';
            if ((int) $b['mdot']['turns'] <= 0) {
                unset($b['mdot']);
            }
        } else {
            unset($b['mdot']);
        }
    }
    $md = $base;

    $cfg = monster_skills()[$mid] ?? null;
    if ($cfg === null || ($cfg['skills'] ?? []) === []) {
        // 没有专属技能的怪：照样报动作+伤害，不再沉默
        if ($md <= 0) {
            $log .= $mname . '向你' . monster_generic_move($mid) . '，你闪开了。';
        } else {
            $log .= $mname . '向你' . monster_generic_move($mid) . '，你掉' . $md . '点生命。';
        }
        return ['md' => $md + $dotDmg, 'log' => $log, 'rushed' => false, 'skill' => ''];
    }

    // 掷骰选技能（同一轮最多放一个）
    $picked = null;
    foreach ($cfg['skills'] as $sk) {
        if (mt_rand(1, 100) <= (int) ($sk['rate'] ?? 0)) {
            $picked = $sk;
            break;
        }
    }
    if ($picked === null) {
        if ($md <= 0) {
            $log .= $mname . '使出【' . (string) ($cfg['atk'] ?? '攻击') . '】，你闪开了。';
        } else {
            $log .= $mname . '使出【' . (string) ($cfg['atk'] ?? '攻击') . '】，你掉' . $md . '点生命。';
        }
        return ['md' => $md + $dotDmg, 'log' => $log, 'rushed' => false, 'skill' => ''];
    }

    $sname = (string) ($picked['name'] ?? '攻击');
    $mult = (float) ($picked['mult'] ?? 1.0);
    $md = max(0, (int) ($md * $mult));

    // 属性转换
    $el = (string) ($picked['el'] ?? '');
    if ($el === '') {
        $el = monster_element($mid);
    }
    if ($el !== '') {
        $pres = (float) ((float) gear_stats((int) ($u['id'] ?? 0))['res_' . $el] ?? 0) + (float) enchant_el_res((int) ($u['id'] ?? 0), $el);
        $md = (int) ($md * (1 - min(80, max(0, $pres)) / 100));
        $log .= $mname . '使出【' . $sname . '】（' . element_name($el) . '）！';
    } else {
        $log .= $mname . '使出【' . $sname . '】！';
    }
    $log .= '你掉' . $md . '点生命。';

    // 吸血
    if (!empty($picked['drain'])) {
        $heal = max(1, (int) ($md * (float) $picked['drain'] / 100));
        $b['hp'] = min((int) ($b['maxhp'] ?? $b['hp']), (int) ($b['hp'] ?? 0) + $heal);
        $log .= $mname . '吞噬了你的生命，恢复' . $heal . '点。';
    }
    // 挂DOT
    if (!empty($picked['dot']) && is_array($picked['dot'])) {
        $b['mdot'] = ['name' => $sname, 'turns' => (int) $picked['dot'][0], 'pct' => (float) $picked['dot'][1]];
        $log .= '你中了【' . $sname . '】，持续掉血！';
    }
    // 架盾
    if (!empty($picked['guard'])) {
        $b['m_guard'] = (int) $picked['guard'];
        $log .= $mname . '架起防御，接下来几轮你的伤害会被削弱！';
    }
    // 召唤援军
    if (!empty($picked['adds'])) {
        $n = max(1, (int) $picked['adds']);
        $b['left'] = (int) ($b['left'] ?? 1) + $n;
        $b['num'] = (int) ($b['num'] ?? 1) + $n;
        $log .= $mname . '召唤了' . $n . '只帮手加入围殴（场上剩' . (int) $b['left'] . '只）！';
    }
    // 连打第二下
    $rushed = false;
    if (!empty($picked['rush'])) {
        $second = max(0, (int) ($base * 0.5));
        $md += $second;
        $log .= '还没完——' . $mname . '紧接着又补了一下，你再掉' . $second . '点生命。';
        $rushed = true;
    }
    // DOT 在技能倍率之外单独叠加
    $md += $dotDmg;
    return ['md' => $md, 'log' => $log, 'rushed' => $rushed, 'skill' => $sname];
}

// 没有专属表的怪，按本系给一句通用动作
function monster_generic_move(string $mid): string
{
    $el = monster_element($mid);
    return [
        'fire' => '喷出一口烈焰',
        'wind' => '卷起一阵狂风',
        'ice' => '甩出一片冰棱',
        'thunder' => '劈下一道雷光',
        'dark' => '扑上来撕咬',
        'light' => '洒下一片圣光',
    ][$el] ?? '扑上来';
}

// 怪架盾期间，你的出手被削弱（在你的伤害落定、闪避结算前调用）
function monster_guard_cut(array &$b, int $pd): array
{
    if (empty($b['m_guard']) || $pd <= 0) {
        return [$pd, ''];
    }
    $pd = (int) ($pd * 0.6);
    $b['m_guard'] = (int) $b['m_guard'] - 1;
    $tag = '（怪架盾-40%）';
    if ((int) $b['m_guard'] <= 0) {
        unset($b['m_guard']);
        $tag .= '（盾碎了）';
    }
    return [$pd, $tag];
}

// 换下一只时清掉怪DOT与架盾
function monster_skill_clear(array &$b): void
{
    unset($b['mdot'], $b['m_guard']);
}
