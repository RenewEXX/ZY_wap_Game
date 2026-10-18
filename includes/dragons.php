<?php
declare(strict_types=1);

// ============ 六圣柱·六龙王 ============
// 世界观：南炎柱(火) 西风柱(风) 北寒柱(冰) 东雷柱(雷) 深渊柱(暗) 中央圣柱(光)
// 天地四方+地下+中央，共同维持世界平衡。
// 每条龙对自身属性伤害完全免疫，自身攻击全部为对应属性伤害。
// 击杀掉落：专属套装 + 专属宠物蛋 + 神魂碎片 + 本属性龙魂/龙玉。

function dragons(): array
{
    return [
        // —— 南·火 ——
        'doyle' => [
            'name' => '烬焰领主·多伊尔',
            'short' => '火龙',
            'el' => 'fire',
            'pillar' => '南炎柱',
            'dir' => '南',
            'lv' => 750,
            'epithet' => '灰烬中的守望者',
            'intro' => '火龙一族的上古遗民，守护世界六圣柱之一“南炎柱”的巨龙。深入远古火山遗迹最深处会惊动这位沉睡的霸主。他自诩“灰烬中的守望者”，对闯入者极度不耐。',
            'hp' => 30000000,
            'atk' => 9000,
            'lair' => 'wb_arena',
            'lair_name' => '龙巢·南炎柱',
            'gate' => 'volcano_heart',
            'gate_name' => '火山之心',
            'set' => 'ember',
            'set_name' => '烬焰套装',
            'egg' => 'fire_dragon_egg',
            'pet' => 'fire_drake',
            'soul' => 'soul_fire',
            'jade' => 'jade_fire',
        ],
        // —— 西·风 ——
        'aetherine' => [
            'name' => '无羁苍岚·艾瑟琳',
            'short' => '风龙',
            'el' => 'wind',
            'pillar' => '西风柱',
            'dir' => '西',
            'lv' => 780,
            'epithet' => '高天的流浪者',
            'intro' => '风龙一族的游离者，亦是六圣柱时代唯一的“旁观者”。曾是镇守“西风柱”的守望者，厌倦了千年的桎梏，自我放逐于苍穹之上。穿越浮空群岛的尽头，或攀上世界最高处的风蚀神坛时，会惊动这位在云端随风游荡的迷惘之龙。她视所有生灵与神明为风中过客，对试图挽留她的人极度冷漠。',
            'hp' => 42000000,
            'atk' => 10000,
            'lair' => 'wb_wind',
            'lair_name' => '龙巢·西风柱',
            'gate' => 'wind_shrine',
            'gate_name' => '风蚀神坛',
            'set' => 'azure',
            'set_name' => '苍岚套装',
            'egg' => 'wind_dragon_egg',
            'pet' => 'sky_serpent',
            'soul' => 'soul_wind',
            'jade' => 'jade_wind',
        ],
        // —— 北·冰 ——
        'ysolde' => [
            'name' => '凛冬灾异·伊索尔德',
            'short' => '冰龙',
            'el' => 'ice',
            'pillar' => '北寒柱',
            'dir' => '北',
            'lv' => 810,
            'epithet' => '永冻之棺的守护者',
            'intro' => '冰龙一脉的最后遗孤，亦是囚禁自身的狱卒。曾是镇守极北“北寒柱”的圣柱之盾，如今却因诅咒成为“永冻之棺”的守护者。探寻万年冰原下的失落神国时，会唤醒这位在绝对零度中沉睡的悲歌女王。',
            'hp' => 56000000,
            'atk' => 11000,
            'lair' => 'wb_ice',
            'lair_name' => '龙巢·北寒柱',
            'gate' => 'lost_kingdom',
            'gate_name' => '失落神国',
            'set' => 'glacier',
            'set_name' => '永冻套装',
            'egg' => 'ice_dragon_egg',
            'pet' => 'frost_maiden',
            'soul' => 'soul_ice',
            'jade' => 'jade_ice',
        ],
        // —— 东·雷 ——
        'rex' => [
            'name' => '碎星狂君·雷克斯',
            'short' => '雷龙',
            'el' => 'thunder',
            'pillar' => '东雷柱',
            'dir' => '东',
            'lv' => 845,
            'epithet' => '天谴之鞭',
            'intro' => '雷龙一脉的异数，亦是六圣柱时代唯一的“破坏者”。他并非“东雷柱”的守护者，而是被封印于圣柱之下的灾厄。闯入雷暴禁区或上古诛魔雷池时，会意外解开这位暴躁龙王的封印。他认为世界腐朽不堪，唯有毁灭才能重生。',
            'hp' => 72000000,
            'atk' => 12200,
            'lair' => 'wb_thunder',
            'lair_name' => '龙巢·东雷柱',
            'gate' => 'thunder_pool',
            'gate_name' => '上古诛魔雷池',
            'set' => 'thunder',
            'set_name' => '天谴套装',
            'egg' => 'thunder_dragon_egg',
            'pet' => 'storm_tyrant',
            'soul' => 'soul_thunder',
            'jade' => 'jade_thunder',
        ],
        // —— 地下·暗 ——
        'nidhogg' => [
            'name' => '终焉黑蚀·尼德霍格',
            'short' => '暗龙',
            'el' => 'dark',
            'pillar' => '深渊柱',
            'dir' => '地下',
            'lv' => 875,
            'epithet' => '末日之影',
            'intro' => '魔龙一脉的末代魔王，亦是六圣柱时代唯一的“否定者”。他并非元素守护者，而是盘踞在世界根系、最终摧毁了“深渊柱”的“噬界者”。深入世界深渊或触碰禁忌之源时，会直面这位被众神封印的绝望具象。他视所有生灵为必将腐烂的残渣。',
            'hp' => 90000000,
            'atk' => 13500,
            'lair' => 'wb_dark',
            'lair_name' => '龙巢·深渊柱',
            'gate' => 'forbidden_origin',
            'gate_name' => '禁忌之源',
            'set' => 'ruin',
            'set_name' => '终焉套装',
            'egg' => 'dark_dragon_egg',
            'pet' => 'void_dragon',
            'soul' => 'soul_dark',
            'jade' => 'jade_dark',
        ],
        // —— 中央·光 ——
        'aurelian' => [
            'name' => '圣辉裁决者·奥瑞利安',
            'short' => '光龙',
            'el' => 'light',
            'pillar' => '中央圣柱',
            'dir' => '中央',
            'lv' => 900,
            'epithet' => '终末审判',
            'intro' => '金龙一脉的末代族长，镇守世界六圣柱之“中央圣柱”的古老存在。攀登浮空群岛或探索天空之城遗迹时，会惊动这位高居云端的审判者。',
            'hp' => 120000000,
            'atk' => 15000,
            'lair' => 'wb_light',
            'lair_name' => '龙巢·中央圣柱',
            'gate' => 'sky_city_ruin',
            'gate_name' => '天空之城遗迹',
            'set' => 'judgment',
            'set_name' => '圣裁套装',
            'egg' => 'light_dragon_egg',
            'pet' => 'seraph_dragon',
            'soul' => 'soul_light',
            'jade' => 'jade_light',
        ],
    ];
}

function dragon(string $mid): array
{
    return dragons()[$mid] ?? [];
}

function dragon_elements(): array
{
    return ['fire', 'wind', 'ice', 'thunder', 'dark', 'light'];
}

// 材料名：龙魂（六元素）/ 龙玉（六元素）/ 通用龙鳞引
function dragon_mats(): array
{
    $m = [
        'wb_ticket' => '龙鳞引（龙王入场券）',
        'dragon_core' => '龙王精华',
    ];
    foreach (dragons() as $d) {
        $m[$d['soul']] = '龙魂·' . element_name($d['el']);
        $m[$d['jade']] = '龙玉·' . element_name($d['el']);
        $m[$d['egg']] = '传奇' . $d['short'] . '宠物蛋';
    }
    return $m;
}

// ---------- 召唤/查询辅助 ----------
function dragon_of_el(string $el): array
{
    foreach (dragons() as $d) {
        if ($d['el'] === $el) {
            return $d;
        }
    }
    return [];
}

function dragon_souls_held(int $uid): array
{
    $out = [];
    $mats = mats_of($uid);
    foreach (dragons() as $d) {
        if ((int) ($mats[$d['soul']] ?? 0) > 0) {
            $out[$d['el']] = (int) $mats[$d['soul']];
        }
    }
    return $out;
}

function dragon_jades_held(int $uid): array
{
    $out = [];
    $mats = mats_of($uid);
    foreach (dragons() as $d) {
        if ((int) ($mats[$d['jade']] ?? 0) > 0) {
            $out[$d['el']] = (int) $mats[$d['jade']];
        }
    }
    return $out;
}

// ---------- 套装（围绕各龙属性）----------
function dragon_set_pieces(string $set): array
{
    return [
        'ember' => ['烬焰·龙裔刃', '烬焰·灰烬法杖', '烬焰·熔核锤', '烬焰·焚心弓', '烬焰·熔盾', '烬焰·余烬囊', '烬焰·守卫铠', '烬焰·祭司冠', '烬焰·熔渣腿', '烬焰·灰烬戒', '烬焰·余火链', '烬焰·束腰'],
        'azure' => ['苍岚·疾风刃', '苍岚·御风铠', '苍岚·飘羽靴', '苍岚·听风链', '苍岚·游云戒', '苍岚·长弓', '苍岚·天穹杖'],
        'glacier' => ['永冻·哀歌冠', '永冻·冰棺胸甲', '永冻·极寒腿', '永冻·霜噬爪', '永冻·寂灭戒', '永冻·悲歌链', '永冻·囚冰杖'],
        'thunder' => ['天谴·碎星锤', '天谴·破晓刃', '天谴·风暴铠', '天谴·雷霆戒', '天谴·雷池链', '天谴·闪羽弓', '天谴·天谴杖'],
        'ruin' => ['终焉·噬界爪', '终焉·黑蚀袍', '终焉·腐朽冠', '终焉·末日戒', '终焉·深渊缚带', '终焉·湮灭刃', '终焉·骸骨杖'],
        'judgment' => ['圣裁·裁决杖', '圣裁·天光铠', '圣裁·辉耀盔', '圣裁·破晓盾', '圣裁·审判链', '圣裁·纯白戒', '圣裁·圣光弓'],
    ][$set] ?? [];
}

function dragon_set_bonus(string $set, int $owned): string
{
    return [
        'ember' => '【烬焰套装】已穿' . $owned . '/7——2件：物攻+10%；4件：元素伤害+120；7件：真伤+500、减伤+10%、技能伤害+15%。',
        'azure' => '【苍岚套装】已穿' . $owned . '/7——每件：闪避+1%、移动速度+1%；3件：风元素伤害+80；5件：暴击+8%、技能伤害+18%、移速+5%；7件：全属性+等级×6、风伤再+120！',
        'glacier' => '【永冻套装】已穿' . $owned . '/7——每件：冰抗+8%、控制抗性+3%；3件：减伤+10%、法防+150；5件：生命+等级×80；7件：寒冰护体——减伤再+12%、免疫冻结！',
        'thunder' => '【天谴套装】已穿' . $owned . '/7——每件：雷元素伤害+10%；3件：穿透+80、元素伤害+100；5件：技能伤害+22%；7件：破除一切——减伤+15%、雷伤+300！',
        'ruin' => '【终焉套装】已穿' . $owned . '/7——每件：吸血+2%；3件：真伤+等级×25；5件：全属性+等级×5、吸血+8%；7件：万物腐朽——真伤+等级×60、减伤+18%！',
        'judgment' => '【圣裁套装】已穿' . $owned . '/7——每件：光元素伤害+10%；3件：技能伤害+18%；5件：真伤+等级×40、减伤+12%；7件：终末审判——真伤+等级×80、技能伤+30%、全属性+等级×8！',
    ][$set] ?? '';
}

// ---------- 专属宠物 ----------
function dragon_pets(): array
{
    return [
        'fire_drake' => ['name' => '炎龙雏', 'quality' => 'legend', 'growth' => 1.9, 'atk' => 190, 'def' => 120, 'hp' => 3200, 'spd' => 14, 'crit' => 12, 'cd' => 140, 'active' => '烬焰吐息', 'mult' => 3.2, 'passive' => '烬焰：火元素伤害+30%', 'source' => '火龙多伊尔'],
        'sky_serpent' => ['name' => '苍岚灵蛇', 'quality' => 'legend', 'growth' => 1.95, 'atk' => 205, 'def' => 108, 'hp' => 3000, 'spd' => 24, 'crit' => 15, 'cd' => 130, 'active' => '疾风穿击', 'mult' => 3.4, 'passive' => '凌风：闪避+12%、移动速度+15%', 'source' => '风龙艾瑟琳'],
        'frost_maiden' => ['name' => '霜歌女妖', 'quality' => 'legend', 'growth' => 2.0, 'atk' => 218, 'def' => 145, 'hp' => 3600, 'spd' => 13, 'crit' => 11, 'cd' => 135, 'active' => '永冻哀歌', 'mult' => 3.5, 'passive' => '哀歌：冰抗+40%、受致命伤时冻结敌人', 'source' => '冰龙伊索尔德'],
        'storm_tyrant' => ['name' => '风暴暴君', 'quality' => 'legend', 'growth' => 2.1, 'atk' => 240, 'def' => 118, 'hp' => 3300, 'spd' => 18, 'crit' => 18, 'cd' => 128, 'active' => '碎星连雷', 'mult' => 3.8, 'passive' => '天谴：雷元素伤害+40%、麻痹抗性+50%', 'source' => '雷龙雷克斯'],
        'void_dragon' => ['name' => '虚空噬龙', 'quality' => 'legend', 'growth' => 2.15, 'atk' => 268, 'def' => 132, 'hp' => 3800, 'spd' => 15, 'crit' => 14, 'cd' => 132, 'active' => '噬界黑洞', 'mult' => 3.9, 'passive' => '噬界：吸血+15%、暗伤+35%', 'source' => '暗龙尼德霍格'],
        'seraph_dragon' => ['name' => '炽天使龙', 'quality' => 'legend', 'growth' => 2.3, 'atk' => 295, 'def' => 175, 'hp' => 4200, 'spd' => 16, 'crit' => 16, 'cd' => 120, 'active' => '圣辉裁决', 'mult' => 4.2, 'passive' => '圣裁：全元素伤害+25%、减伤+10%', 'source' => '光龙奥瑞利安'],
    ];
}

// ---------- 专属小怪 ----------
// 前缀即属性：az=风 gl=冰 th=雷 da=暗 li=光
function dragon_monsters(): array
{
    return [
        // 西·风（浮空群岛~风蚀神坛）
        'az_sky_thief' => ['name' => '窃风者', 'hp' => 1800000, 'atk' => 6200, 'exp' => 2600000, 'gold' => 900000, 'el' => 'wind', 'lv' => 560],
        'az_gale_hound' => ['name' => '疾风猎犬', 'hp' => 2100000, 'atk' => 6600, 'exp' => 2800000, 'gold' => 950000, 'el' => 'wind', 'lv' => 580],
        'az_cloud_titan' => ['name' => '积云巨人', 'hp' => 2600000, 'atk' => 7000, 'exp' => 3000000, 'gold' => 1000000, 'el' => 'wind', 'lv' => 610],
        'az_storm_serpent' => ['name' => '风暴巨蛇', 'hp' => 3200000, 'atk' => 7400, 'exp' => 3200000, 'gold' => 1100000, 'el' => 'wind', 'lv' => 640],
        'az_shrine_guard' => ['name' => '神坛守卫', 'hp' => 4200000, 'atk' => 8200, 'exp' => 3600000, 'gold' => 1200000, 'el' => 'wind', 'lv' => 670],
        // 北·冰（冰原深处~失落神国）
        'gl_deep_wraith' => ['name' => '深渊寒魂', 'hp' => 2400000, 'atk' => 7200, 'exp' => 2900000, 'gold' => 1000000, 'el' => 'ice', 'lv' => 600],
        'gl_ice_choir' => ['name' => '冰霜唱诗班', 'hp' => 2800000, 'atk' => 7600, 'exp' => 3100000, 'gold' => 1050000, 'el' => 'ice', 'lv' => 630],
        'gl_coffin_warden' => ['name' => '冰棺守望者', 'hp' => 3600000, 'atk' => 8200, 'exp' => 3500000, 'gold' => 1150000, 'el' => 'ice', 'lv' => 670],
        'gl_eternal_bear' => ['name' => '永冻巨熊', 'hp' => 4200000, 'atk' => 8800, 'exp' => 3800000, 'gold' => 1250000, 'el' => 'ice', 'lv' => 710],
        'gl_funeral_host' => ['name' => '冰原丧主', 'hp' => 5200000, 'atk' => 9600, 'exp' => 4200000, 'gold' => 1350000, 'el' => 'ice', 'lv' => 750],
        // 东·雷（雷暴禁区~诛魔雷池）
        'th_storm_shade' => ['name' => '雷暴残影', 'hp' => 3000000, 'atk' => 8600, 'exp' => 3400000, 'gold' => 1150000, 'el' => 'thunder', 'lv' => 640],
        'th_mad_capacitor' => ['name' => '狂暴电容器', 'hp' => 3400000, 'atk' => 9000, 'exp' => 3600000, 'gold' => 1200000, 'el' => 'thunder', 'lv' => 670],
        'th_pool_wraith' => ['name' => '雷池游魂', 'hp' => 4000000, 'atk' => 9600, 'exp' => 3900000, 'gold' => 1280000, 'el' => 'thunder', 'lv' => 710],
        'th_thunder_priest' => ['name' => '雷祭司', 'hp' => 4600000, 'atk' => 10200, 'exp' => 4200000, 'gold' => 1350000, 'el' => 'thunder', 'lv' => 750],
        'th_chain_dragon' => ['name' => '缚雷龙裔', 'hp' => 6000000, 'atk' => 11500, 'exp' => 4800000, 'gold' => 1500000, 'el' => 'thunder', 'lv' => 800],
        // 地下·暗（世界深渊~禁忌之源）
        'da_rot_priest' => ['name' => '腐化祭司', 'hp' => 4200000, 'atk' => 9800, 'exp' => 4000000, 'gold' => 1300000, 'el' => 'dark', 'lv' => 690],
        'da_void_eater' => ['name' => '虚空吞噬者', 'hp' => 4800000, 'atk' => 10400, 'exp' => 4300000, 'gold' => 1380000, 'el' => 'dark', 'lv' => 730],
        'da_root_horror' => ['name' => '世界根系之恐', 'hp' => 5400000, 'atk' => 11000, 'exp' => 4600000, 'gold' => 1450000, 'el' => 'dark', 'lv' => 770],
        'da_rot_seer' => ['name' => '腐朽先知', 'hp' => 6200000, 'atk' => 11800, 'exp' => 5000000, 'gold' => 1550000, 'el' => 'dark', 'lv' => 820],
        'da_nightmare' => ['name' => '群魔噩梦', 'hp' => 7800000, 'atk' => 13000, 'exp' => 5600000, 'gold' => 1700000, 'el' => 'dark', 'lv' => 870],
        // 中央·光（天空之城遗迹~中央圣阶）
        'li_cloud_sentinel' => ['name' => '云端哨兵', 'hp' => 5200000, 'atk' => 11200, 'exp' => 4500000, 'gold' => 1450000, 'el' => 'light', 'lv' => 760],
        'li_holy_knight' => ['name' => '圣裁骑士', 'hp' => 6000000, 'atk' => 12000, 'exp' => 4900000, 'gold' => 1550000, 'el' => 'light', 'lv' => 800],
        'li_prayer_machine' => ['name' => '祷告机兵', 'hp' => 6800000, 'atk' => 12800, 'exp' => 5300000, 'gold' => 1650000, 'el' => 'light', 'lv' => 840],
        'li_judgment_eye' => ['name' => '裁决之眼', 'hp' => 7800000, 'atk' => 13600, 'exp' => 5700000, 'gold' => 1750000, 'el' => 'light', 'lv' => 880],
        'li_gold_herald' => ['name' => '金龙传令', 'hp' => 9500000, 'atk' => 15000, 'exp' => 6400000, 'gold' => 1900000, 'el' => 'light', 'lv' => 920],
    ];
}

// 供 monster_element() / monster_lv() 回退查询
function dragon_monster(string $mid): array
{
    return dragon_monsters()[$mid] ?? [];
}

// 龙王讨伐进场等级（按圣柱方位逐级收紧）
function dragon_min_lv(string $mid): int
{
    return [
        'doyle' => 500, 'aetherine' => 600, 'ysolde' => 680, 'rex' => 720, 'nidhogg' => 760, 'aurelian' => 800,
    ][$mid] ?? 500;
}

// 龙巢（战斗地点）→ 门（安全区）双向对应
function dragon_of_lair(string $lair): string
{
    foreach (dragons() as $mid => $d) {
        if ((string) $d['lair'] === $lair) {
            return $mid;
        }
    }
    return '';
}

// ---------- 地图（六圣柱方位）----------
function dragon_locations(): array
{
    $m = [];
    // —— 南·火：火山之心 → 龙巢·南炎柱 ——
    $m['wb_arena'] = ['name' => '龙巢·南炎柱', 'desc' => '热浪扑面。多伊尔盘在柱子上，睁一只眼闭一只眼。用火打它等于给它添柴。', 'exits' => ['volcano_heart' => '火山之心'], 'monsters' => []];
    // —— 西·风：浮空群岛 → 风蚀神坛 → 西风柱 ——
    $m['sky_ramp'] = ['name' => '浮空岛·起风处', 'desc' => '绳子系在崖边，往西走出陆地的最后一步。艾瑟琳说过：挽留她的人，都是风中过客。', 'exits' => ['avenue' => '中央大道'], 'monsters' => ['az_sky_thief']];
    $m['sky_isles'] = ['name' => '浮空群岛', 'desc' => '石岛悬在云上，用风而不是桥相连。走错一块，就是万丈高。', 'exits' => ['sky_ramp' => '起风处', 'wind_shrine' => '风蚀神坛'], 'monsters' => ['az_sky_thief', 'az_gale_hound', 'az_cloud_titan']];
    $m['wind_shrine'] = ['name' => '风蚀神坛', 'desc' => '世界最高处。西风柱立在云里，被风啃了千年。有龙在云端游荡，不肯落地。', 'exits' => ['sky_isles' => '浮空群岛'], 'monsters' => ['az_cloud_titan', 'az_storm_serpent', 'az_shrine_guard']];
    $m['wb_wind'] = ['name' => '龙巢·西风柱', 'desc' => '风在这里有了形状。艾瑟琳盘在柱顶，看起来随时会走——但她已经走了千年。', 'exits' => ['wind_shrine' => '风蚀神坛'], 'monsters' => []];
    // —— 北·冰：冰原深处 → 失落神国 → 北寒柱 ——
    $m['frost_deep'] = ['name' => '冰原深处·裂隙', 'desc' => '冰原走到尽头，地面裂开。下面有一座没人记得名字的神国。', 'exits' => ['ice_village' => '冰原村', 'lost_kingdom' => '失落神国'], 'monsters' => ['gl_deep_wraith', 'gl_ice_choir']];
    $m['lost_kingdom'] = ['name' => '失落神国', 'desc' => '万年前的教堂冻在冰里，穹顶还完整。有个声音在唱歌，是女人的声音，很悲伤。', 'exits' => ['frost_deep' => '冰原深处'], 'monsters' => ['gl_ice_choir', 'gl_coffin_warden', 'gl_eternal_bear']];
    $m['wb_ice'] = ['name' => '龙巢·北寒柱', 'desc' => '绝对零度。连火焰落下来都会停在半空。伊索尔德在冰棺里，睁着眼。', 'exits' => ['lost_kingdom' => '失落神国'], 'monsters' => []];
    // —— 东·雷：雷暴禁区 → 诛魔雷池 → 东雷柱 ——
    $m['storm_wall'] = ['name' => '雷暴禁区·外围', 'desc' => '往东走，天色发紫。空气里有臭氧味，汗毛会立起来。', 'exits' => ['avenue' => '中央大道', 'thunder_pool' => '上古诛魔雷池'], 'monsters' => ['th_storm_shade', 'th_mad_capacitor']];
    $m['thunder_pool'] = ['name' => '上古诛魔雷池', 'desc' => '当年用来劈魔头的池子。池底压着东雷柱，柱下压着一条龙。', 'exits' => ['storm_wall' => '雷暴禁区'], 'monsters' => ['th_mad_capacitor', 'th_pool_wraith', 'th_thunder_priest']];
    $m['wb_thunder'] = ['name' => '龙巢·东雷柱', 'desc' => '封印裂开了。雷克斯抖掉身上的锁链，第一句话是“世界该重生了”。', 'exits' => ['thunder_pool' => '上古诛魔雷池'], 'monsters' => []];
    // —— 地下·暗：世界深渊 → 禁忌之源 → 深渊柱 ——
    $m['abyss_deep'] = ['name' => '世界深渊·底层', 'desc' => '往下没有底了。根系从四面八方穿过来，都在往同一个地方输送东西。', 'exits' => ['altar' => '深渊祭坛', 'forbidden_origin' => '禁忌之源'], 'monsters' => ['da_rot_priest', 'da_void_eater', 'da_root_horror']];
    $m['forbidden_origin'] = ['name' => '禁忌之源', 'desc' => '深渊柱已经不在了——它被某样东西吃掉了。尼德霍格就坐在残骸上。', 'exits' => ['abyss_deep' => '世界深渊'], 'monsters' => ['da_rot_seer', 'da_nightmare', 'da_void_eater']];
    $m['wb_dark'] = ['name' => '龙巢·深渊柱', 'desc' => '没有光，也不完全是黑。尼德霍格说他等了很久，久到忘了在等什么。', 'exits' => ['forbidden_origin' => '禁忌之源'], 'monsters' => []];
    // —— 中央·光：天空之城遗迹 → 中央圣阶 → 中央圣柱 ——
    $m['sky_city_ruin'] = ['name' => '天空之城遗迹', 'desc' => '中央圣柱在天空之城正中央。废墟上空有金色的东西在盘旋，不是鸟。', 'exits' => ['sky_isles' => '浮空群岛', 'holy_stair' => '中央圣阶'], 'monsters' => ['li_cloud_sentinel', 'li_holy_knight']];
    $m['holy_stair'] = ['name' => '中央圣阶', 'desc' => '一千级白石阶，一级比一级亮。奥瑞利安站在最高处，俯视一切。', 'exits' => ['sky_city_ruin' => '天空之城遗迹'], 'monsters' => ['li_prayer_machine', 'li_judgment_eye', 'li_holy_knight']];
    $m['wb_light'] = ['name' => '龙巢·中央圣柱', 'desc' => '光从柱子里流出来，像水一样淌。奥瑞利安说：审判结束了，你们只是还不知道。', 'exits' => ['holy_stair' => '中央圣阶'], 'monsters' => []];
    return $m;
}

function dragon_maps(): array
{
    return [
        'sky_ramp', 'sky_isles', 'wind_shrine',
        'frost_deep', 'lost_kingdom',
        'storm_wall', 'thunder_pool',
        'abyss_deep', 'forbidden_origin',
        'sky_city_ruin', 'holy_stair',
    ];
}

// 通往龙巢的门：可传送、可安全休整
function dragon_gate_maps(): array
{
    return ['volcano_heart', 'sky_ramp', 'frost_deep', 'storm_wall', 'abyss_deep', 'sky_city_ruin'];
}

// 龙巢本身不出现在传送列表（要走龙鳞引进场）
function dragon_lair_maps(): array
{
    return ['wb_arena', 'wb_wind', 'wb_ice', 'wb_thunder', 'wb_dark', 'wb_light'];
}