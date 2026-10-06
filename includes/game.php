<?php
declare(strict_types=1);

function jobs(): array
{
    return [
        'warrior' => ['name' => '战士', 'hp' => 62, 'atk' => 7, 'def' => 5, 'skill' => '重斩', 'weapon' => 'rusty', 'armor' => 'cloth', 'desc' => '血多甲厚，正面硬扛。技能重斩：造成150%物理伤害'],
        'mage' => ['name' => '法师', 'hp' => 42, 'atk' => 11, 'def' => 1, 'skill' => '火球术', 'weapon' => 'staff', 'armor' => 'cloth', 'desc' => '攻击最高，身板最脆。技能火球术：无视敌方防御的高额法术伤害'],
        'hunter' => ['name' => '猎手', 'hp' => 50, 'atk' => 8, 'def' => 3, 'skill' => '双连射', 'weapon' => 'bow', 'armor' => 'cloth', 'desc' => '出手灵活稳定。技能双连射：连续射出两箭，共约160%伤害'],
        'priest' => ['name' => '牧师', 'hp' => 54, 'atk' => 6, 'def' => 3, 'skill' => '治疗术', 'weapon' => 'mace', 'armor' => 'cloth', 'desc' => '能打能奶，容错最高。技能治疗术：恢复25点生命并造成一次小额神圣伤害'],
    ];
}

function job_of(array $u): array
{
    $all = jobs();
    $id = (string) ($u['job'] ?? 'warrior');
    // 兼容老存档
    if ($id === 'scholar') {
        $id = 'mage';
    }
    if ($id === 'doctor') {
        $id = 'priest';
    }
    return $all[$id] ?? $all['warrior'];
}

function job_id_of(array $u): string
{
    $id = (string) ($u['job'] ?? 'warrior');
    if ($id === 'scholar') {
        return 'mage';
    }
    if ($id === 'doctor') {
        return 'priest';
    }
    return isset(jobs()[$id]) ? $id : 'warrior';
}

function locations(): array
{
    return [
        'smithy' => [
            'name' => '灰雾村·铁匠铺',
            'desc' => '打铁声。老铁匠布隆：“醒了？你倒在村口，浑身是灰。雾在往上爬。”桌上有字条【别往下看】。',
            'exits' => ['square' => '村广场'],
            'monsters' => [],
        ],
        'square' => [
            'name' => '灰雾村·广场',
            'desc' => '老井在夜里会唱歌。东是麦田，西是断桥。',
            'exits' => ['smithy' => '铁匠铺', 'field' => '雾谷麦田', 'well' => '老井', 'bridge' => '断桥'],
            'monsters' => [],
        ],
        'field' => [
            'name' => '雾谷麦田',
            'desc' => '麦子倒了一大片。绿皮小鬼在啃麦穗。玛莎求你驱逐它们。',
            'exits' => ['square' => '村广场'],
            'monsters' => ['goblin'],
        ],
        'well' => [
            'name' => '老井',
            'desc' => '井水发甜。采药女莉娜的弟弟喝了后发烧，说地下有姐姐唱歌。夜里雾中女妖会现身。',
            'exits' => ['square' => '村广场'],
            'monsters' => ['banshee'],
        ],
        'bridge' => [
            'name' => '断桥',
            'desc' => '食人魔咕噜堵路，要100个亮闪闪。它只是在等走失的哥布林养子小鼻涕。',
            'exits' => ['square' => '村广场', 'gate' => '白石镇道口'],
            'monsters' => ['ogre'],
        ],
        'gate' => [
            'name' => '白石镇道口',
            'desc' => '黑雾贴地爬行。石碑字迹模糊。往北是白石镇广场，往南是断桥，往下是腐骨甬道。',
            'exits' => ['town_sq' => '白石镇广场', 'bridge' => '断桥', 'tunnel' => '腐骨甬道', 'shop' => '黑市', 'camp' => '残火营地'],
            'monsters' => ['rat'],
        ],
        'tunnel' => [
            'name' => '腐骨甬道',
            'desc' => '墙里嵌着无名骨。每走一步都有细碎的咀嚼声跟着你。',
            'exits' => ['gate' => '罪渊入口', 'shoal' => '黑血浅滩'],
            'monsters' => ['rat', 'wraith'],
        ],
        'shoal' => [
            'name' => '黑血浅滩',
            'desc' => '浅水反光如凝血。有东西在水面下慢慢转圈。',
            'exits' => ['tunnel' => '腐骨甬道', 'altar' => '深渊祭坛'],
            'monsters' => ['wraith', 'leech'],
        ],
        'altar' => [
            'name' => '深渊祭坛',
            'desc' => '九圈锈铁围着空座。座上没有神像，只有一个朝下的掌印。',
            'exits' => ['shoal' => '黑血浅滩', 'prison' => '遗忘监牢'],
            'monsters' => ['leech', 'guard'],
        ],
        'prison' => [
            'name' => '遗忘监牢',
            'desc' => '铁门全开着。真正的锁不在门上，在你还想不想回头。',
            'exits' => ['altar' => '深渊祭坛'],
            'monsters' => ['guard', 'jailer'],
        ],
        'shop' => [
            'name' => '黑市',
            'desc' => '掌柜没有脸。货架上的东西会自己报出价钱。',
            'exits' => ['gate' => '罪渊入口'],
            'monsters' => [],
        ],
        'camp' => [
            'name' => '残火营地',
            'desc' => '一堆快灭的火。睡在这里能把伤养回来，但罪渊不会因此变浅。',
            'exits' => ['gate' => '罪渊入口'],
            'monsters' => [],
        ],
    ] + ch1_locations();
}

function loc(string $id): array
{
    $all = locations();
    return $all[$id] ?? $all['gate'];
}

function monsters(): array
{
    return [
        'goblin' => ['name' => '哥布林哨兵x2', 'hp' => 30, 'atk' => 6, 'exp' => 20, 'gold' => 8],
        'banshee' => ['name' => '雾中女妖', 'hp' => 36, 'atk' => 8, 'exp' => 26, 'gold' => 12],
        'ogre' => ['name' => '食人魔咕噜', 'hp' => 50, 'atk' => 10, 'exp' => 34, 'gold' => 20],
        // ---- 第一章·白石镇 ----
        'slime' => ['name' => '雾史莱姆', 'hp' => 26, 'atk' => 7, 'exp' => 14, 'gold' => 6],
        'wolf' => ['name' => '荒野灰狼', 'hp' => 40, 'atk' => 11, 'exp' => 22, 'gold' => 10],
        'thief' => ['name' => '拦路盗贼', 'hp' => 48, 'atk' => 13, 'exp' => 28, 'gold' => 18],
        'bee' => ['name' => '针刺巨蜂', 'hp' => 44, 'atk' => 14, 'exp' => 30, 'gold' => 14],
        'frog' => ['name' => '黑水毒蛙', 'hp' => 55, 'atk' => 15, 'exp' => 32, 'gold' => 15],
        'skeleton' => ['name' => '锈甲骷髅', 'hp' => 65, 'atk' => 17, 'exp' => 40, 'gold' => 20],
        'ghoul' => ['name' => '食尸鬼', 'hp' => 80, 'atk' => 20, 'exp' => 52, 'gold' => 26],
        'cultist' => ['name' => '低语教徒', 'hp' => 70, 'atk' => 19, 'exp' => 48, 'gold' => 30],
        'bat' => ['name' => '吸血蝠群', 'hp' => 60, 'atk' => 18, 'exp' => 44, 'gold' => 22],
        'knight' => ['name' => '堕落骑士莫尔', 'hp' => 220, 'atk' => 26, 'exp' => 200, 'gold' => 150],
        'rat' => ['name' => '腐骨鼠', 'hp' => 14, 'atk' => 4, 'exp' => 6, 'gold' => 4],
        'wraith' => ['name' => '贴墙怨魂', 'hp' => 22, 'atk' => 7, 'exp' => 12, 'gold' => 8],
        'leech' => ['name' => '黑血蛭', 'hp' => 32, 'atk' => 10, 'exp' => 20, 'gold' => 14],
        'guard' => ['name' => '祭坛守卫', 'hp' => 48, 'atk' => 14, 'exp' => 32, 'gold' => 22],
        'jailer' => ['name' => '罪渊狱卒', 'hp' => 80, 'atk' => 18, 'exp' => 80, 'gold' => 50],
    ];
}

function items(): array
{
    return [
        'rusty' => ['name' => '生锈铁剑', 'slot' => 'weapon', 'atk' => 3, 'def' => 0, 'gold' => 300],
        'bow' => ['name' => '猎弓', 'slot' => 'weapon', 'atk' => 4, 'def' => 0, 'gold' => 450],
        'staff' => ['name' => '桦木法杖', 'slot' => 'weapon', 'atk' => 5, 'def' => 0, 'gold' => 500],
        'mace' => ['name' => '白木槌', 'slot' => 'weapon', 'atk' => 3, 'def' => 0, 'gold' => 350],
        'sickle' => ['name' => '草药镰', 'slot' => 'weapon', 'atk' => 2, 'def' => 0, 'gold' => 120],
        'boneclub' => ['name' => '大骨棒', 'slot' => 'weapon', 'atk' => 5, 'def' => 0, 'gold' => 800],
        'spike' => ['name' => '铁刺', 'slot' => 'weapon', 'atk' => 6, 'def' => 0, 'gold' => 1200],
        'brand' => ['name' => '罪纹短剑', 'slot' => 'weapon', 'atk' => 12, 'def' => 0, 'gold' => 5000],
        'cloth' => ['name' => '破布甲', 'slot' => 'armor', 'atk' => 0, 'def' => 2, 'gold' => 200],
        'bone' => ['name' => '骨甲', 'slot' => 'armor', 'atk' => 0, 'def' => 5, 'gold' => 1500],
        'potion' => ['name' => '回血药', 'slot' => 'potion', 'atk' => 0, 'def' => 0, 'gold' => 60],
    ];
}

function item_name(string $id): string
{
    if ($id === '') {
        return '无';
    }
    return items()[$id]['name'] ?? $id;
}

// 货币：库里存铜。1金=100银=10000铜，显示为X金Y银Z铜
function fmt_money(int $copper): string
{
    $copper = max(0, $copper);
    $g = intdiv($copper, 10000);
    $s = intdiv($copper % 10000, 100);
    $c = $copper % 100;
    $out = '';
    if ($g > 0) {
        $out .= $g . '金';
    }
    if ($s > 0) {
        $out .= $s . '银';
    }
    $out .= $c . '铜';
    return $out;
}

// ---------- 装备系统 ----------
// 槽位：武器/上身/头部/下身/手套/鞋子/戒指x2/项链
function equip_slots(): array
{
    return [
        'weapon' => '武器', 'body' => '上身', 'head' => '头部', 'legs' => '下身',
        'gloves' => '手套', 'shoes' => '鞋子', 'ring' => '戒指', 'necklace' => '项链',
    ];
}

function equip_qualities(): array
{
    return ['低劣', '普通', '稀有', '史诗', '传说'];
}

function equip_affix_count(int $q): int
{
    return [1, 2, 3, 4, 5][$q] ?? 1;
}

function equip_quality_config(): array
{
    return [
        0 => ['multiplier' => 0.7, 'tiers' => [80, 20, 0, 0, 0]],
        1 => ['multiplier' => 1.0, 'tiers' => [40, 40, 20, 0, 0]],
        2 => ['multiplier' => 1.3, 'tiers' => [10, 30, 40, 20, 0]],
        3 => ['multiplier' => 1.6, 'tiers' => [0, 10, 30, 40, 20]],
        4 => ['multiplier' => 2.0, 'tiers' => [0, 0, 20, 40, 40]],
    ];
}

function affix_catalog(): array
{
    $r = static function (array $t, int $weight, string $group = '', int $precision = 0): array {
        return ['tiers' => $t, 'weight' => $weight, 'group' => $group, 'precision' => $precision];
    };
    return [
        'atk' => ['name' => '攻击力', 'slots' => ['weapon', 'gloves', 'ring', 'necklace'], 'data' => $r([[2, 5], [5, 10], [10, 18], [18, 30], [30, 50]], 100)],
        'atk_pct' => ['name' => '攻击力%', 'slots' => ['weapon'], 'data' => $r([[1, 2], [2, 4], [4, 6], [6, 9], [8, 12]], 80, 'offense_pct', 1)],
        'crit' => ['name' => '暴击率', 'slots' => ['weapon', 'head', 'gloves', 'ring', 'necklace'], 'data' => $r([[0.1, 0.3], [0.3, 0.6], [0.6, 1.0], [1.0, 1.5], [1.5, 2.0]], 100, 'crit', 1)],
        'critdmg' => ['name' => '暴击伤害', 'slots' => ['weapon', 'head', 'gloves', 'ring', 'necklace'], 'data' => $r([[1, 3], [3, 6], [6, 10], [10, 15], [15, 20]], 100, 'critdmg', 1)],
        'speed' => ['name' => '攻击速度', 'slots' => ['weapon', 'gloves'], 'data' => $r([[1, 2], [2, 4], [4, 6], [6, 8], [7, 10]], 90, 'speed', 1)],
        'element' => ['name' => '元素伤害', 'slots' => ['weapon', 'ring'], 'data' => $r([[2, 4], [4, 8], [8, 13], [13, 19], [15, 25]], 70)],
        'lifesteal' => ['name' => '生命偷取', 'slots' => ['weapon', 'ring'], 'data' => $r([[0.2, 0.5], [0.5, 0.9], [0.9, 1.4], [1.4, 2.0], [2, 3]], 50, 'lifesteal', 1)],
        'penetration' => ['name' => '护甲穿透', 'slots' => ['weapon'], 'data' => $r([[1, 2], [2, 4], [4, 7], [7, 10], [8, 12]], 60, 'penetration', 1)],
        'skill_damage' => ['name' => '技能伤害', 'slots' => ['weapon'], 'data' => $r([[2, 4], [4, 7], [7, 10], [10, 14], [12, 18]], 70, 'skill_damage', 1)],
        'execute' => ['name' => '处决伤害', 'slots' => ['weapon'], 'data' => $r([[3, 6], [6, 10], [10, 16], [16, 23], [20, 30]], 40, 'execute', 1)],
        'hp' => ['name' => '生命值', 'slots' => ['body', 'head', 'legs', 'shoes', 'ring', 'necklace'], 'data' => $r([[8, 15], [15, 30], [30, 55], [55, 100], [90, 150]], 100)],
        'def' => ['name' => '护甲', 'slots' => ['body', 'head', 'legs', 'gloves', 'shoes'], 'data' => $r([[4, 10], [10, 20], [20, 35], [35, 60], [60, 100]], 100)],
        'damage_reduction' => ['name' => '伤害减免', 'slots' => ['body'], 'data' => $r([[0.5, 1], [1, 2], [2, 3.5], [3.5, 5.5], [5, 8]], 70, 'damage_reduction', 1)],
        'resist' => ['name' => '元素抗性', 'slots' => ['body', 'necklace'], 'data' => $r([[1, 2], [2, 4], [4, 7], [7, 11], [10, 15]], 80, 'element_res', 1)],
        'thorns' => ['name' => '荆棘反伤', 'slots' => ['body'], 'data' => $r([[2, 4], [4, 8], [8, 14], [14, 23], [20, 35]], 50)],
        'regen' => ['name' => '生命回复/秒', 'slots' => ['body'], 'data' => $r([[0.5, 1], [1, 2], [2, 3.5], [3.5, 5.5], [5, 8]], 60, 'regen', 1)],
        'energy' => ['name' => '最大能量', 'slots' => ['body', 'necklace'], 'data' => $r([[5, 10], [10, 20], [20, 35], [35, 55], [50, 80]], 60)],
        'cooldown' => ['name' => '技能冷却缩减', 'slots' => ['head'], 'data' => $r([[1, 2], [2, 4], [4, 7], [7, 10], [8, 12]], 70, 'cooldown', 1)],
        'hit' => ['name' => '命中率', 'slots' => ['head', 'gloves'], 'data' => $r([[1, 2], [2, 4], [4, 7], [7, 10], [8, 12]], 60, 'hit', 1)],
        'exp_gain' => ['name' => '经验获取', 'slots' => ['head'], 'data' => $r([[1, 3], [3, 5], [5, 8], [8, 12], [10, 15]], 40, 'exp_gain', 1)],
        'dodge' => ['name' => '闪避率', 'slots' => ['legs', 'shoes'], 'data' => $r([[0.2, 0.5], [0.5, 1.0], [1.0, 1.8], [1.8, 2.8], [2.8, 4.0]], 80, 'dodge', 1)],
        'move_speed' => ['name' => '移动速度', 'slots' => ['legs', 'shoes'], 'data' => $r([[0.5, 1], [1, 2], [2, 3.5], [3.5, 5], [4, 6]], 70, 'move_speed', 1)],
        'control_resist' => ['name' => '控制抗性', 'slots' => ['legs'], 'data' => $r([[1, 2], [2, 4], [4, 7], [7, 10], [8, 12]], 50, 'control_resist', 1)],
        'slow_resist' => ['name' => '减速抗性', 'slots' => ['shoes'], 'data' => $r([[2, 4], [4, 7], [7, 12], [12, 18], [15, 25]], 50, 'slow_resist', 1)],
        'all_attr' => ['name' => '全属性', 'slots' => ['ring', 'necklace'], 'data' => $r([[1, 2], [2, 4], [4, 7], [7, 10], [8, 12]], 70)],
        'gold_gain' => ['name' => '金币获取', 'slots' => ['ring'], 'data' => $r([[2, 5], [5, 8], [8, 13], [13, 19], [15, 25]], 40, 'gold_gain', 1)],
        'magic_find' => ['name' => '魔法物品掉落', 'slots' => ['ring'], 'data' => $r([[1, 2], [2, 4], [4, 7], [7, 10], [8, 12]], 30, 'magic_find', 1)],
    ];
}

function slot_pools(): array
{
    $out = [];
    foreach (affix_catalog() as $id => $a) {
        foreach ($a['slots'] as $slot) {
            $out[$slot][] = $id;
        }
    }
    return $out;
}

function affix_label(string $k): string
{
    return affix_catalog()[$k]['name'] ?? $k;
}

function affix_fmt(string $k, float $v, ?string $tier = null): string
{
    $percent = ['atk_pct', 'crit', 'critdmg', 'speed', 'lifesteal', 'penetration', 'skill_damage', 'execute', 'damage_reduction', 'resist', 'regen', 'cooldown', 'hit', 'exp_gain', 'dodge', 'move_speed', 'control_resist', 'slow_resist', 'gold_gain', 'magic_find'];
    $value = in_array($k, $percent, true) ? rtrim(rtrim(number_format($v, 1, '.', ''), '0'), '.') . '%' : (string) (int) round($v);
    return ($tier ? $tier . ' ' : '') . affix_label($k) . '+' . $value;
}

function affix_roll(string $k, int $q, int $itemLevel = 1): array
{
    $a = affix_catalog()[$k] ?? null;
    if (!$a) {
        return ['tier' => 'T5', 'v' => 1];
    }
    $weights = equip_quality_config()[$q]['tiers'] ?? [100, 0, 0, 0, 0];
    $roll = mt_rand(1, max(1, array_sum($weights)));
    $sum = 0;
    $tierIndex = 0;
    foreach ($weights as $i => $weight) {
        $sum += $weight;
        if ($roll <= $sum) {
            $tierIndex = $i;
            break;
        }
    }
    $range = $a['data']['tiers'][$tierIndex] ?? $a['data']['tiers'][0];
    $value = $range[0] + mt_rand() / mt_getrandmax() * ($range[1] - $range[0]);
    $value *= (float) (equip_quality_config()[$q]['multiplier'] ?? 1) * (1 + max(1, $itemLevel) * 0.02);
    $precision = (int) $a['data']['precision'];
    $value = $precision === 0 ? (float) max(1, round($value)) : round($value, $precision);
    return ['tier' => ['T5', 'T4', 'T3', 'T2', 'T1'][$tierIndex] ?? 'T5', 'v' => $value];
}

function equip_fullname(string $base, int $q): string
{
    return equip_qualities()[$q] . '·' . $base;
}

function equip_shortname(string $name): string
{
    foreach (equip_qualities() as $q) {
        $prefix = $q . '·';
        if (str_starts_with($name, $prefix)) {
            return mb_substr($name, mb_strlen($prefix));
        }
    }
    return $name;
}

// 怪物专属掉落：小怪掉率低且多为低劣
function monster_drops(string $mid): array
{
    return [
        'rat' => ['slot' => 'gloves', 'base' => '破布手套', 'rate' => 0.03, 'q' => [100, 0, 0, 0, 0]],
        'slime' => ['slot' => 'shoes', 'base' => '黏液布鞋', 'rate' => 0.03, 'q' => [80, 20, 0, 0, 0]],
        'goblin' => ['slot' => 'weapon', 'base' => '断匕', 'rate' => 0.04, 'q' => [70, 30, 0, 0, 0]],
        'wolf' => ['slot' => 'necklace', 'base' => '狼牙项链', 'rate' => 0.05, 'q' => [50, 35, 15, 0, 0]],
        'banshee' => ['slot' => 'ring', 'base' => '哭泣戒指', 'rate' => 0.06, 'q' => [0, 60, 30, 10, 0]],
        'wraith' => ['slot' => 'body', 'base' => '怨魂斗篷', 'rate' => 0.07, 'q' => [30, 50, 20, 0, 0]],
        'thief' => ['slot' => 'gloves', 'base' => '盗贼手套', 'rate' => 0.06, 'q' => [30, 50, 20, 0, 0]],
        'bee' => ['slot' => 'ring', 'base' => '蜂刺戒指', 'rate' => 0.06, 'q' => [30, 50, 20, 0, 0]],
        'frog' => ['slot' => 'legs', 'base' => '毒蛙腿甲', 'rate' => 0.06, 'q' => [30, 50, 20, 0, 0]],
        'leech' => ['slot' => 'ring', 'base' => '吸血指环', 'rate' => 0.08, 'q' => [0, 50, 40, 10, 0]],
        'ogre' => ['slot' => 'body', 'base' => '食人魔腰带', 'rate' => 0.15, 'q' => [0, 50, 40, 10, 0]],
        'skeleton' => ['slot' => 'head', 'base' => '锈盔', 'rate' => 0.08, 'q' => [20, 50, 30, 0, 0]],
        'bat' => ['slot' => 'body', 'base' => '蝠翼披风', 'rate' => 0.08, 'q' => [20, 50, 30, 0, 0]],
        'ghoul' => ['slot' => 'weapon', 'base' => '食尸鬼之爪', 'rate' => 0.10, 'q' => [0, 30, 50, 20, 0]],
        'cultist' => ['slot' => 'necklace', 'base' => '低语项链', 'rate' => 0.10, 'q' => [0, 30, 50, 20, 0]],
        'guard' => ['slot' => 'legs', 'base' => '祭坛护腿', 'rate' => 0.12, 'q' => [0, 20, 50, 30, 0]],
        'jailer' => ['slot' => 'body', 'base' => '狱卒重铠', 'rate' => 0.15, 'q' => [0, 0, 60, 35, 5]],
        'knight' => ['slot' => 'weapon', 'base' => '堕落骑士大剑', 'rate' => 0.60, 'q' => [0, 0, 0, 70, 30]],
    ][$mid] ?? [];
}

function make_equip(int $uid, string $slot, string $base, int $q, int $itemLevel = 1): string
{
    $pool = slot_pools()[$slot] ?? ['atk', 'def', 'hp'];
    $catalog = affix_catalog();
    $selected = [];
    $groups = [];
    $guard = 0;
    while (count($selected) < equip_affix_count($q) && $pool !== [] && $guard++ < 100) {
        $id = $pool[array_rand($pool)];
        $group = (string) ($catalog[$id]['data']['group'] ?? '');
        if (isset($selected[$id]) || ($group !== '' && isset($groups[$group]))) {
            continue;
        }
        $selected[$id] = true;
        if ($group !== '') {
            $groups[$group] = true;
        }
    }
    $affixes = [];
    foreach (array_keys($selected) as $id) {
        $rolled = affix_roll($id, $q, $itemLevel);
        $affixes[] = ['id' => $id, 'tier' => $rolled['tier'], 'v' => $rolled['v']];
    }
    $name = equip_fullname($base, $q);
    $st = db()->prepare('INSERT INTO equips (uid, slot, name, quality, affixes, pos, item_level) VALUES (?, ?, ?, ?, ?, "", ?)');
    $st->execute([$uid, $slot, $name, $q, json_encode($affixes, JSON_UNESCAPED_UNICODE), max(1, $itemLevel)]);
    return $name;
}

function monster_material(string $mid): array
{
    return [
        'rat' => ['rat_tail', '鼠尾'], 'slime' => ['slime_gel', '雾凝黏液'],
        'goblin' => ['goblin_ear', '哥布林耳朵'], 'wolf' => ['wolf_fang', '灰狼牙'],
        'banshee' => ['banshee_hair', '女妖发丝'], 'wraith' => ['soul_dust', '残魂尘'],
        'thief' => ['loot_clasp', '赃物铜扣'], 'bee' => ['bee_sting', '巨蜂刺'],
        'frog' => ['frog_sac', '毒蛙囊'], 'leech' => ['blood_clot', '凝血块'],
        'ogre' => ['ogre_knuckle', '食人魔指骨'], 'skeleton' => ['bone_shard', '碎骨'],
        'bat' => ['bat_membrane', '蝠翼膜'], 'ghoul' => ['ghoul_fang', '尸牙'],
        'cultist' => ['whisper_note', '低语纸条'], 'guard' => ['altar_chip', '祭坛铁片'],
        'jailer' => ['jail_rivet', '牢门铆钉'], 'knight' => ['knight_sigil', '黑骑士徽记'],
    ][$mid] ?? [];
}

function quest_mats(): array
{
    return ['blackcoin' => '不断下坠的黑币'];
}

function mat_name(string $id): string
{
    $all = [];
    foreach (['rat', 'slime', 'goblin', 'wolf', 'banshee', 'wraith', 'thief', 'bee', 'frog', 'leech', 'ogre', 'skeleton', 'bat', 'ghoul', 'cultist', 'guard', 'jailer', 'knight'] as $mid) {
        $m = monster_material($mid);
        if ($m !== []) {
            $all[$m[0]] = $m[1];
        }
    }
    return $all[$id] ?? quest_mats()[$id] ?? enhance_material_name($id);
}

function add_mat(int $uid, string $mat, int $n): void
{
    if ($n === 0) {
        return;
    }
    $st = db()->prepare('INSERT INTO mats (uid, mat, num) VALUES (?, ?, ?) ON CONFLICT(uid, mat) DO UPDATE SET num=MAX(0, num+?)');
    $st->execute([$uid, $mat, max(0, $n), $n]);
}

function mats_of(int $uid): array
{
    $st = db()->prepare('SELECT mat, num FROM mats WHERE uid=? ORDER BY mat');
    $st->execute([$uid]);
    $out = [];
    while ($row = $st->fetch()) {
        $out[$row['mat']] = (int) $row['num'];
    }
    return $out;
}

function roll_material(int $uid, string $mid): string
{
    $m = monster_material($mid);
    if ($m === [] || mt_rand(1, 100) > 45) {
        return '';
    }
    $n = mt_rand(1, 2);
    add_mat($uid, $m[0], $n);
    return $m[1] . 'x' . $n;
}

function roll_enhance_material(int $uid, string $mid): string
{
    $level = monster_lv($mid);
    if ($level <= 5 && mt_rand(1, 100) <= 35) {
        $id = 'enhance_t1';
    } elseif ($level <= 9 && mt_rand(1, 100) <= 25) {
        $id = 'enhance_t2';
    } elseif ($level >= 10 && mt_rand(1, 100) <= 18) {
        $id = 'enhance_t3';
    } else {
        return '';
    }
    $n = mt_rand(1, 3);
    add_mat($uid, $id, $n);
    return enhance_material_name($id) . 'x' . $n;
}

function roll_drop(int $uid, string $mid): string
{
    $t = monster_drops($mid);
    if ($t === [] || mt_rand() / mt_getrandmax() > (float) $t['rate']) {
        return '';
    }
    $r = mt_rand(1, 100);
    $acc = 0;
    $q = 0;
    foreach ($t['q'] as $i => $w) {
        $acc += $w;
        if ($r <= $acc) {
            $q = $i;
            break;
        }
    }
    return make_equip($uid, $t['slot'], $t['base'], $q, monster_lv($mid));
}

// 已穿装备属性总和（内存缓存一次）
function gear_stats(int $uid): array
{
    static $cache = [];
    if (isset($cache[$uid]) && ($GLOBALS['_gear_bust'] ?? null) !== $uid) {
        return $cache[$uid];
    }
    unset($GLOBALS['_gear_bust']);
    $keys = ['atk', 'def', 'hp', 'crit', 'critdmg', 'dodge', 'lifesteal', 'atk_pct', 'speed', 'element', 'penetration', 'skill_damage', 'execute', 'damage_reduction', 'resist', 'thorns', 'regen', 'energy', 'cooldown', 'hit', 'exp_gain', 'move_speed', 'control_resist', 'slow_resist', 'all_attr', 'gold_gain', 'magic_find'];
    $s = array_fill_keys($keys, 0.0);
    $st = db()->prepare('SELECT * FROM equips WHERE uid=? AND pos="wear"');
    $st->execute([$uid]);
    while ($row = $st->fetch()) {
        if ((int) ($row['broken'] ?? 0) === 1) {
            continue;
        }
        $mult = enhance_rate($row);
        $aff = json_decode((string) $row['affixes'], true);
        if (!is_array($aff)) {
            continue;
        }
        foreach ($aff as $a) {
            $id = (string) ($a['id'] ?? $a['k'] ?? '');
            $value = (float) ($a['v'] ?? 0);
            if (in_array($id, ['atk', 'def', 'hp', 'all_attr'], true)) {
                $value *= $mult;
            }
            if (isset($s[$id])) {
                $s[$id] += $value;
            }
        }
    }
    // 全属性按攻击、防御、生命各加一次；百分比属性按设计上限截断。
    $s['atk'] += $s['all_attr'];
    $s['def'] += $s['all_attr'];
    $s['hp'] += $s['all_attr'];
    foreach (['crit' => 75, 'dodge' => 60, 'cooldown' => 50] as $key => $cap) {
        $s[$key] = min((float) $cap, $s[$key]);
    }
    $cache[$uid] = $s;
    return $s;
}

function my_equips(int $uid): array
{
    $st = db()->prepare('SELECT * FROM equips WHERE uid=? ORDER BY pos DESC, quality DESC, id DESC');
    $st->execute([$uid]);
    return $st->fetchAll();
}

function enhance_table(): array
{
    return [
        1 => ['rate' => 95, 'fail' => 'none', 'mat' => 'enhance_t1', 'cost' => 10],
        2 => ['rate' => 90, 'fail' => 'none', 'mat' => 'enhance_t1', 'cost' => 10],
        3 => ['rate' => 85, 'fail' => 'none', 'mat' => 'enhance_t1', 'cost' => 10],
        4 => ['rate' => 80, 'fail' => 'none', 'mat' => 'enhance_t1', 'cost' => 10],
        5 => ['rate' => 75, 'fail' => 'none', 'mat' => 'enhance_t2', 'cost' => 10],
        6 => ['rate' => 70, 'fail' => 'none', 'mat' => 'enhance_t2', 'cost' => 10],
        7 => ['rate' => 50, 'fail' => 'reset', 'mat' => 'enhance_t2', 'cost' => 10],
        8 => ['rate' => 40, 'fail' => 'reset', 'mat' => 'enhance_t2', 'cost' => 10],
        9 => ['rate' => 30, 'fail' => 'reset', 'mat' => 'enhance_t3', 'cost' => 10],
        10 => ['rate' => 20, 'fail' => 'break', 'mat' => 'enhance_t3', 'cost' => 10],
        11 => ['rate' => 15, 'fail' => 'break', 'mat' => 'enhance_t3', 'cost' => 12],
        12 => ['rate' => 10, 'fail' => 'break', 'mat' => 'enhance_t3', 'cost' => 12],
        13 => ['rate' => 7, 'fail' => 'break', 'mat' => 'enhance_t3', 'cost' => 15],
        14 => ['rate' => 5, 'fail' => 'break', 'mat' => 'enhance_t3', 'cost' => 15],
        15 => ['rate' => 3, 'fail' => 'break', 'mat' => 'enhance_t3', 'cost' => 20],
    ];
}

function enhance_material_name(string $id): string
{
    return ['enhance_t1' => '下级强化石', 'enhance_t2' => '中级强化石', 'enhance_t3' => '上级强化石'][$id] ?? $id;
}

// 各地区铁匠NPC：强化只能找他们
function blacksmiths(): array
{
    return [
        'smithy' => '布隆',
        'smith' => '丹恩·铜须',
    ];
}

function enhance_rate(array $e): float
{
    if ((int) ($e['broken'] ?? 0) === 1) {
        return 0.0;
    }
    return 1 + max(0, (int) ($e['enhance_level'] ?? 0)) * 0.06;
}

function enhance_equip(int $uid, int $eid): string
{
    $st = db()->prepare('SELECT * FROM equips WHERE id=? AND uid=?');
    $st->execute([$eid, $uid]);
    $e = $st->fetch();
    if (!$e) {
        return '没有这件装备。';
    }
    if ((int) ($e['broken'] ?? 0) === 1) {
        return '这件装备已经碎裂，不能强化。';
    }
    $next = (int) ($e['enhance_level'] ?? 0) + 1;
    $table = enhance_table();
    if (!isset($table[$next])) {
        return '这件装备已经达到强化上限+15。';
    }
    $rule = $table[$next];
    $mats = mats_of($uid);
    if (($mats[$rule['mat']] ?? 0) < $rule['cost']) {
        return '强化需要' . enhance_material_name($rule['mat']) . 'x' . $rule['cost'] . '。';
    }
    add_mat($uid, $rule['mat'], -$rule['cost']);
    $ok = random_int(1, 100) <= $rule['rate'];
    if ($ok) {
        db()->prepare('UPDATE equips SET enhance_level=?, enhance_fail=0 WHERE id=? AND uid=?')->execute([$next, $eid, $uid]);
        _gear_uncache($uid);
        return '强化成功！【' . equip_shortname($e['name']) . '】达到+' . $next . '。';
    }
    if ($rule['fail'] === 'reset') {
        db()->prepare('UPDATE equips SET enhance_level=0, enhance_fail=enhance_fail+1 WHERE id=? AND uid=?')->execute([$eid, $uid]);
        _gear_uncache($uid);
        return '强化失败！装备强化等级归零。';
    }
    if ($rule['fail'] === 'break') {
        db()->prepare('UPDATE equips SET broken=1, pos="", enhance_fail=enhance_fail+1 WHERE id=? AND uid=?')->execute([$eid, $uid]);
        _gear_uncache($uid);
        return '强化失败！装备碎裂，已无法穿戴和强化。';
    }
    db()->prepare('UPDATE equips SET enhance_fail=enhance_fail+1 WHERE id=? AND uid=?')->execute([$eid, $uid]);
    return '强化失败，等级不变。';
}

function equip_sell_price(int $q): int
{
    return [50, 120, 300, 800, 2000][$q] ?? 50;
}

function wear_equip(int $uid, int $eid): string
{
    $uid = (int) $uid;
    $eid = (int) $eid;
    $st = db()->prepare('SELECT * FROM equips WHERE id=? AND uid=?');
    $st->execute([$eid, $uid]);
    $eq = $st->fetch();
    if (!$eq) {
        return '不是你的装备。';
    }
    if ((int) ($eq['broken'] ?? 0) === 1) {
        return '碎裂装备不能穿戴。';
    }
    $before = gear_stats($uid)['hp'];
    if ($eq['slot'] === 'ring') {
        $cnt = (int) db()->query('SELECT COUNT(*) FROM equips WHERE uid=' . $uid . ' AND slot="ring" AND pos="wear"')->fetchColumn();
        if ($cnt >= 2) {
            db()->exec('UPDATE equips SET pos="" WHERE id=(SELECT id FROM equips WHERE uid=' . $uid . ' AND slot="ring" AND pos="wear" ORDER BY id LIMIT 1)');
        }
        db()->exec('UPDATE equips SET pos="wear" WHERE id=' . $eid);
    } else {
        db()->exec('UPDATE equips SET pos="" WHERE uid=' . $uid . ' AND slot="' . $eq['slot'] . '" AND pos="wear"');
        db()->exec('UPDATE equips SET pos="wear" WHERE id=' . $eid);
    }
    // 清缓存并同步生命上限
    _gear_uncache($uid);
    $after = gear_stats($uid)['hp'];
    $delta = (int) ($after - $before);
    if ($delta !== 0) {
        $u = user_by_id($uid);
        if ($u) {
            $u['maxhp'] = max(1, (int) $u['maxhp'] + $delta);
            $u['hp'] = min((int) $u['maxhp'], max(1, (int) $u['hp'] + max(0, $delta)));
            user_save($u);
        }
    }
    return '穿上了【' . $eq['name'] . '】。';
}

function _gear_uncache(int $uid): void
{
    // gear_stats 用 static 缓存，同请求内穿脱后需重算：用反射外的办法——直接清表级标记
    // 简单实现：再次查询覆盖（见 gear_stats 的引用 Trick 不可行，故此处用全局变量中转）
    $GLOBALS['_gear_bust'] = $uid;
}

function take_off_equip(int $uid, int $eid): string
{
    $uid = (int) $uid;
    $eid = (int) $eid;
    $st = db()->prepare('SELECT * FROM equips WHERE id=? AND uid=? AND pos="wear"');
    $st->execute([$eid, $uid]);
    $eq = $st->fetch();
    if (!$eq) {
        return '没穿着这件。';
    }
    $before = gear_stats($uid)['hp'];
    db()->exec('UPDATE equips SET pos="" WHERE id=' . $eid);
    _gear_uncache($uid);
    $after = gear_stats($uid)['hp'];
    $delta = (int) ($after - $before);
    $u = user_by_id($uid);
    if ($u) {
        $u['maxhp'] = max(1, (int) $u['maxhp'] + $delta);
        $u['hp'] = min((int) $u['maxhp'], (int) $u['hp']);
        user_save($u);
    }
    return '脱下了【' . $eq['name'] . '】。';
}

function player_atk(array $u): int
{
    return (int) $u['atk'] + (int) gear_stats((int) ($u['id'] ?? 0))['atk'];
}

function player_def(array $u): int
{
    return (int) $u['def'] + (int) gear_stats((int) ($u['id'] ?? 0))['def'];
}

function exp_need(int $lv): int
{
    return max(20, (int) (20 * pow(max(1, $lv), 1.6)));
}

function monster_lv(string $mid): int
{
    static $lv = [
        'rat' => 1, 'goblin' => 2, 'banshee' => 3, 'ogre' => 4,
        'slime' => 4, 'wolf' => 5, 'wraith' => 5, 'thief' => 6, 'bee' => 6,
        'frog' => 7, 'leech' => 7, 'skeleton' => 8, 'bat' => 8,
        'ghoul' => 9, 'cultist' => 9, 'guard' => 10, 'jailer' => 13, 'knight' => 14,
    ];
    return $lv[$mid] ?? 1;
}

// 等级差决定实际经验：低4级以上刷怪只给2成（防跨级碾压），越级打给加成
function exp_gain_for(array $u, string $mid): int
{
    $base = (int) (monsters()[$mid]['exp'] ?? 5);
    $diff = monster_lv($mid) - (int) ($u['lv'] ?? 1);
    if ($diff <= -4) {
        return max(1, (int) ($base * 0.2));
    }
    if ($diff < 0) {
        return max(1, (int) ($base * (1 + $diff * 0.1)));
    }
    if ($diff === 0) {
        return $base;
    }
    return (int) ($base * min(2.0, 1 + $diff * 0.15));
}

function gain_exp(array &$u, int $exp): string
{
    $u['exp'] = (int) $u['exp'] + $exp;
    $msg = '经验+' . $exp;
    while ((int) $u['exp'] >= exp_need((int) $u['lv'])) {
        $u['exp'] -= exp_need((int) $u['lv']);
        $u['lv'] = (int) $u['lv'] + 1;
        $u['maxhp'] = (int) $u['maxhp'] + 6;
        $u['atk'] = (int) $u['atk'] + 1;
        $u['def'] = (int) $u['def'] + 1;
        $u['hp'] = (int) $u['maxhp'];
        $msg .= '！升级至' . $u['lv'] . '级，伤势尽复';
    }
    return $msg;
}

function dmg_to_monster(array $u): int
{
    $d = max(1, player_atk($u) + random_int(0, 3));
    $gs = gear_stats((int) ($u['id'] ?? 0));
    if ($gs['crit'] > 0 && mt_rand(1, 10000) / 100 <= $gs['crit']) {
        $d = (int) ($d * (1 + $gs['critdmg'] / 100));
    }
    return $d;
}

function dmg_to_player(array $u, array $m): int
{
    $gs = gear_stats((int) ($u['id'] ?? 0));
    if ($gs['dodge'] > 0 && mt_rand(1, 10000) / 100 <= $gs['dodge']) {
        return 0;
    }
    return max(1, (int) $m['atk'] + random_int(0, 2) - player_def($u));
}

function find_route(string $from, string $to): array
{
    if ($from === $to) {
        return [$from];
    }
    $all = locations();
    if (!isset($all[$from]) || !isset($all[$to])) {
        return [];
    }
    $prev = [$from => ''];
    $queue = [$from];
    while ($queue !== []) {
        $cur = array_shift($queue);
        foreach (array_keys($all[$cur]['exits'] ?? []) as $nx) {
            if (!isset($all[$nx]) || isset($prev[$nx])) {
                continue;
            }
            $prev[$nx] = $cur;
            if ($nx === $to) {
                $path = [$to];
                while ($prev[end($path)] !== '') {
                    $path[] = $prev[end($path)];
                }
                return array_reverse($path);
            }
            $queue[] = $nx;
        }
    }
    return [];
}

function map_regions(): array
{
    return [
        '灰雾村' => ['smithy', 'square', 'field', 'well', 'bridge'],
        '白石镇' => ['town_sq', 'street_e', 'smith', 'tavern', 'street_w', 'church', 'market', 'sewer', 'guild', 'wall', 'gate'],
        '野外' => ['wild1', 'wild2', 'wild3', 'wild4', 'wild5', 'wild6', 'forest1', 'forest2', 'forest3', 'forest4', 'swamp1', 'swamp2', 'swamp3', 'swamp4', 'hill1', 'hill2', 'hill3', 'hill4'],
        '废弃村·墓穴' => ['ruin1', 'ruin2', 'ruin3', 'tomb1', 'tomb2', 'tomb3', 'tomb4', 'boss'],
        '深渊旧道' => ['tunnel', 'shoal', 'altar', 'prison', 'shop', 'camp'],
    ];
}

function exit_dirs(string $from): array
{
    static $table = [
        'town_sq' => ['北' => 'guild', '东' => 'street_e', '西' => 'street_w', '南' => 'wall'],
        'street_e' => ['西' => 'town_sq', '东' => 'smith'],
        'smith' => ['西' => 'street_e', '东' => 'tavern'],
        'tavern' => ['西' => 'smith'],
        'street_w' => ['东' => 'town_sq', '西' => 'church'],
        'church' => ['东' => 'street_w', '西' => 'market'],
        'market' => ['东' => 'church', '西' => 'sewer'],
        'sewer' => ['东' => 'market'],
        'guild' => ['南' => 'town_sq'],
        'wall' => ['北' => 'town_sq', '南' => 'gate', '东' => 'wild1', '西' => 'wild2'],
        'gate' => ['北' => 'wall', '南' => 'bridge', '东' => 'shop', '西' => 'camp', '下' => 'tunnel'],
        'bridge' => ['北' => 'gate', '南' => 'square'],
        'square' => ['北' => 'bridge', '东' => 'field', '南' => 'smithy', '西' => 'well'],
        'smithy' => ['北' => 'square'],
        'field' => ['西' => 'square'],
        'well' => ['东' => 'square'],
        'tunnel' => ['上' => 'gate', '下' => 'shoal'],
        'shoal' => ['上' => 'tunnel', '下' => 'altar'],
        'altar' => ['上' => 'shoal', '下' => 'prison'],
        'prison' => ['上' => 'altar'],
        'shop' => ['西' => 'gate'],
        'camp' => ['东' => 'gate'],
        'wild1' => ['西' => 'wall', '东' => 'wild2'],
        'wild2' => ['西' => 'wild1', '东' => 'wild3'],
        'wild3' => ['西' => 'wild2', '东' => 'wild4', '南' => 'forest1'],
        'wild4' => ['西' => 'wild3', '东' => 'wild5'],
        'wild5' => ['西' => 'wild4', '东' => 'wild6'],
        'wild6' => ['西' => 'wild5'],
        'forest1' => ['北' => 'wild3', '南' => 'swamp1'],
        'forest2' => ['北' => 'wild3', '南' => 'swamp1'],
        'forest3' => ['北' => 'wild3', '南' => 'swamp1'],
        'forest4' => ['北' => 'wild3', '南' => 'swamp1'],
        'swamp1' => ['北' => 'forest2', '南' => 'hill1'],
        'swamp2' => ['北' => 'forest2', '南' => 'hill1'],
        'swamp3' => ['北' => 'forest2', '南' => 'hill1'],
        'swamp4' => ['北' => 'forest2', '南' => 'hill1'],
        'hill1' => ['北' => 'swamp2', '南' => 'ruin1'],
        'hill2' => ['北' => 'swamp2', '南' => 'ruin1'],
        'hill3' => ['北' => 'swamp2', '南' => 'ruin1'],
        'hill4' => ['北' => 'swamp2', '南' => 'ruin1'],
        'ruin1' => ['北' => 'hill1', '南' => 'ruin2'],
        'ruin2' => ['北' => 'ruin1', '南' => 'ruin3'],
        'ruin3' => ['北' => 'ruin2', '下' => 'tomb1'],
        'tomb1' => ['上' => 'ruin3', '下' => 'tomb2'],
        'tomb2' => ['上' => 'tomb1', '下' => 'tomb3'],
        'tomb3' => ['上' => 'tomb2', '下' => 'tomb4'],
        'tomb4' => ['上' => 'tomb3', '南' => 'boss'],
        'boss' => ['北' => 'tomb4'],
    ];
    $dirs = $table[$from] ?? [];
    // 兜底：表里没写的出口按东南西北上下顺位补上
    $all = locations();
    $used = array_values($dirs);
    foreach (array_keys($all[$from]['exits'] ?? []) as $eid) {
        if (in_array($eid, $used, true)) {
            continue;
        }
        foreach (['东', '南', '西', '北', '上', '下'] as $d) {
            if (!isset($dirs[$d])) {
                $dirs[$d] = $eid;
                $used[] = $eid;
                break;
            }
        }
    }
    // 反向兜底：表里有但实际没这个出口的去掉
    foreach ($dirs as $d => $eid) {
        if (!isset($all[$from]['exits'][$eid])) {
            unset($dirs[$d]);
        }
    }
    return $dirs;
}

function ch1_locations(): array
{
    $m = [];
    $m['town_sq'] = ['name' => '白石镇·广场', 'desc' => '第一章·坠星者。喷泉干了，告示牌：公会招F级新人。东去石板街，西去雾巷。', 'exits' => ['guild' => '冒险者公会', 'wall' => '城墙', 'street_e' => '石板街·东', 'street_w' => '雾巷·西'], 'monsters' => []];
    $m['street_e'] = ['name' => '石板街·东', 'desc' => '打铁声叮当。往里是镇铁匠，再往里是酒馆。', 'exits' => ['town_sq' => '广场', 'smith' => '镇铁匠'], 'monsters' => []];
    $m['street_w'] = ['name' => '雾巷·西', 'desc' => '雾最浓的巷子。教堂钟声从里面传来。', 'exits' => ['town_sq' => '广场', 'church' => '教堂'], 'monsters' => []];
    $m['guild'] = ['name' => '冒险者公会', 'desc' => '接待员艾琳：“坠星者？先去荒野杀20只雾史莱姆拿铜牌。”', 'exits' => ['town_sq' => '广场'], 'monsters' => []];
    $m['church'] = ['name' => '教堂', 'desc' => '修女索菲亚：复活术总缺一块，像被下面抽走。往里是市场。', 'exits' => ['street_w' => '雾巷', 'market' => '市场'], 'monsters' => []];
    $m['smith'] = ['name' => '镇铁匠', 'desc' => '矮人：“给我5雾核开锋。巨蜂20只也顺手清了吧。穿过去是酒馆。”', 'exits' => ['street_e' => '石板街', 'tavern' => '酒馆'], 'monsters' => []];
    $m['tavern'] = ['name' => '酒馆', 'desc' => '诗人唱：转生勇者死了三批，你是第四批。（从铁匠铺穿过来）', 'exits' => ['smith' => '镇铁匠'], 'monsters' => []];
    $m['market'] = ['name' => '市场', 'desc' => '卖解毒草。沼泽蛙毒只有这里能解。后面有旧水道口。', 'exits' => ['church' => '教堂', 'sewer' => '旧水道'], 'monsters' => []];
    $m['wall'] = ['name' => '城墙', 'desc' => '队长：“荒野狼20只，杀完领赏。东边两条荒野，南边是道口。”', 'exits' => ['town_sq' => '广场', 'gate' => '白石镇道口', 'wild1' => '荒野1', 'wild2' => '荒野2'], 'monsters' => []];
    $m['sewer'] = ['name' => '旧水道', 'desc' => '盗贼眼线。教徒用黑币收买流浪汉。（从市场进来）', 'exits' => ['market' => '市场'], 'monsters' => ['thief', 'rat']];
    for ($i = 1; $i <= 6; $i++) {
        $ex = ['wall' => '城墙', 'forest1' => '低语林1'];
        if ($i > 1) {
            $ex['wild' . ($i - 1)] = '荒野' . ($i - 1);
        }
        if ($i < 6) {
            $ex['wild' . ($i + 1)] = '荒野' . ($i + 1);
        }
        $m['wild' . $i] = ['name' => '荒野' . $i, 'desc' => '灰黄草原，史莱姆与灰狼游荡。任务：各杀20只。', 'exits' => $ex, 'monsters' => ['slime', 'wolf']];
    }
    for ($i = 1; $i <= 4; $i++) {
        $m['forest' . $i] = ['name' => '低语林' . $i, 'desc' => '树学人说话。巨蜂+盗贼。杀20蜂交差。', 'exits' => ['wild3' => '荒野3', 'swamp1' => '黑水沼1'], 'monsters' => ['bee', 'thief']];
    }
    for ($i = 1; $i <= 4; $i++) {
        $m['swamp' . $i] = ['name' => '黑水沼' . $i, 'desc' => '毒蛙遍地。修女要20份蛙囊。', 'exits' => ['forest2' => '低语林2', 'hill1' => '灰岩丘1'], 'monsters' => ['frog', 'slime']];
    }
    for ($i = 1; $i <= 4; $i++) {
        $m['hill' . $i] = ['name' => '灰岩丘' . $i, 'desc' => '骷髅+吸血蝠群。', 'exits' => ['swamp2' => '黑水沼2', 'ruin1' => '废弃村1'], 'monsters' => ['skeleton', 'bat']];
    }
    $m['ruin1'] = ['name' => '废弃村1·外围', 'desc' => '和灰雾村一样的空村。食尸鬼啃着燕麦。', 'exits' => ['hill1' => '灰岩丘1', 'ruin2' => '废弃村2'], 'monsters' => ['ghoul', 'skeleton']];
    $m['ruin2'] = ['name' => '废弃村2·教堂', 'desc' => '教徒布道：下面很暖和。', 'exits' => ['ruin1' => '外围', 'ruin3' => '废弃村3'], 'monsters' => ['cultist', 'ghoul']];
    $m['ruin3'] = ['name' => '废弃村3·墓园', 'desc' => '墓穴入口。莫尔的气息渗出来。', 'exits' => ['ruin2' => '教堂', 'tomb1' => '墓穴B1'], 'monsters' => ['cultist', 'bat']];
    for ($i = 1; $i <= 4; $i++) {
        $ex = [];
        $ex[$i > 1 ? 'tomb' . ($i - 1) : 'ruin3'] = $i > 1 ? '墓穴B' . ($i - 1) : '墓园';
        $ex[$i < 4 ? 'tomb' . ($i + 1) : 'boss'] = $i < 4 ? '墓穴B' . ($i + 1) : '骑士之间';
        $m['tomb' . $i] = ['name' => '地下墓穴B' . $i, 'desc' => '越往下越暖。墙上全是朝下掌印。', 'exits' => $ex, 'monsters' => ['skeleton', 'ghoul', 'cultist']];
    }
    $m['boss'] = ['name' => '骑士之间', 'desc' => '终幕·堕落骑士莫尔。前三批勇者死于此。他胸口别着黑币。', 'exits' => ['tomb4' => '墓穴B4'], 'monsters' => ['knight']];
    return $m;
}

function ch1_quests(): array
{
    return [
        10 => ['name' => '公会铜牌', 'todo' => '杀20只雾史莱姆(荒野1-6)', 'loc' => 'wild1', 'locname' => '荒野1', 'need' => ['slime' => 20]],
        11 => ['name' => '狼患', 'todo' => '杀20只荒野灰狼(荒野1-6)', 'loc' => 'wild2', 'locname' => '荒野2', 'need' => ['wolf' => 20]],
        12 => ['name' => '林间蜂巢', 'todo' => '杀20只针刺巨蜂(低语林1-4)', 'loc' => 'forest1', 'locname' => '低语林1', 'need' => ['bee' => 20]],
        13 => ['name' => '解毒剂', 'todo' => '杀20只黑水毒蛙(黑水沼1-4)', 'loc' => 'swamp1', 'locname' => '黑水沼1', 'need' => ['frog' => 20]],
        14 => ['name' => '墓穴低语', 'todo' => '杀10食尸鬼+10教徒(废弃村/墓穴)', 'loc' => 'ruin1', 'locname' => '废弃村1', 'need' => ['ghoul' => 10, 'cultist' => 10]],
        15 => ['name' => '堕落骑士', 'todo' => '讨伐莫尔(骑士之间)', 'loc' => 'boss', 'locname' => '骑士之间', 'need' => ['knight' => 1]],
    ];
}

function quest_state(array $u): array
{
    $q = (int) ($u['quest'] ?? 0);
    if ($q < 10) {
        return quest_of($q) + ['id' => $q, 'ch' => 0];
    }
    $all = ch1_quests();
    if (!isset($all[$q])) {
        return ['name' => '雾散之后', 'step' => '完成', 'todo' => '第一章完成，自由探索', 'loc' => 'town_sq', 'locname' => '白石镇广场', 'id' => 99, 'ch' => 1, 'need' => []];
    }
    return $all[$q] + ['id' => $q, 'ch' => 1, 'step' => ($q - 9) . '/6'];
}

function kill_file(int $uid): string
{
    return DATA_DIR . '/kill_' . $uid . '.json';
}

function kill_counts(int $uid): array
{
    $f = kill_file($uid);
    if (!is_file($f)) {
        return [];
    }
    $d = json_decode((string) file_get_contents($f), true);
    return is_array($d) ? $d : [];
}

function add_kill(int $uid, string $mid): array
{
    $c = kill_counts($uid);
    $c[$mid] = (int) ($c[$mid] ?? 0) + 1;
    @file_put_contents(kill_file($uid), json_encode($c));
    return $c;
}

function quest_progress_text(int $uid, array $qs): string
{
    if (empty($qs['need'])) {
        return '';
    }
    $c = kill_counts($uid);
    $parts = [];
    foreach ($qs['need'] as $mid => $need) {
        $mn = monsters()[$mid]['name'] ?? $mid;
        $parts[] = $mn . (int) ($c[$mid] ?? 0) . '/' . $need;
    }
    return implode('，', $parts);
}

function check_ch1_done(int $uid, array $qs): bool
{
    if (empty($qs['need'])) {
        return true;
    }
    $c = kill_counts($uid);
    foreach ($qs['need'] as $mid => $need) {
        if ((int) ($c[$mid] ?? 0) < $need) {
            return false;
        }
    }
    return true;
}
