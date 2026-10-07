<?php
declare(strict_types=1);

function jobs(): array
{
    return [
        'warrior' => ['name' => '战士', 'hp' => 62, 'mp' => 20, 'atk' => 7, 'def' => 5, 'skill' => '重斩', 'weapon' => 'rusty', 'armor' => 'cloth', 'desc' => '血多甲厚，正面硬扛。技能重斩：造成150%物理伤害'],
        'mage' => ['name' => '法师', 'hp' => 42, 'mp' => 55, 'atk' => 11, 'def' => 1, 'skill' => '火球术', 'weapon' => 'staff', 'armor' => 'cloth', 'desc' => '攻击最高，身板最脆。技能火球术：无视敌方防御的高额法术伤害'],
        'hunter' => ['name' => '猎手', 'hp' => 50, 'mp' => 30, 'atk' => 8, 'def' => 3, 'skill' => '双连射', 'weapon' => 'bow', 'armor' => 'cloth', 'desc' => '出手灵活稳定。技能双连射：连续射出两箭，共约160%伤害'],
        'priest' => ['name' => '牧师', 'hp' => 54, 'mp' => 45, 'atk' => 6, 'def' => 3, 'skill' => '治疗术', 'weapon' => 'mace', 'armor' => 'cloth', 'desc' => '能打能奶，容错最高。技能治疗术：恢复25点生命并造成一次小额神圣伤害'],
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
            'exits' => ['town_sq' => '白石镇广场', 'bridge' => '断桥', 'tunnel' => '腐骨甬道', 'shop' => '黑市', 'camp' => '残火营地', 'silver_gate' => '白银城门'],
            'monsters' => ['rat'],
        ],
        'tunnel' => [
            'name' => '腐骨甬道',
            'desc' => '墙里嵌着无名骨。成群的腐骨鼠在黑暗里集体转头看你。',
            'exits' => ['gate' => '罪渊入口', 'shoal' => '黑血浅滩'],
            'monsters' => ['rat_pack', 'wraith'],
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
    ] + ch1_locations() + dsw_locations() + ch2_locations() + abx_locations() + ch3_locations() + mx_locations() + farm_locations();
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
        'rat_pack' => ['name' => '腐骨鼠群', 'hp' => 150, 'atk' => 24, 'exp' => 100, 'gold' => 45],
        'wraith' => ['name' => '贴墙怨魂', 'hp' => 170, 'atk' => 26, 'exp' => 120, 'gold' => 55],
        'leech' => ['name' => '黑血蛭', 'hp' => 210, 'atk' => 30, 'exp' => 150, 'gold' => 70],
        'guard' => ['name' => '祭坛守卫', 'hp' => 260, 'atk' => 34, 'exp' => 190, 'gold' => 90],
        'jailer' => ['name' => '罪渊狱卒', 'hp' => 340, 'atk' => 40, 'exp' => 260, 'gold' => 130],
        'corrupt_rat' => ['name' => '腐化老鼠', 'hp' => 42, 'atk' => 13, 'exp' => 30, 'gold' => 12],
        'deep_bat' => ['name' => '深渊蝙蝠', 'hp' => 58, 'atk' => 17, 'exp' => 44, 'gold' => 18],
        'runaway_miner' => ['name' => '失控矿工', 'hp' => 76, 'atk' => 21, 'exp' => 62, 'gold' => 28],
        'abyss_spore' => ['name' => '腐化孢子体', 'hp' => 88, 'atk' => 24, 'exp' => 78, 'gold' => 35],
        'echo_rayne' => ['name' => '深渊回响·雷恩', 'hp' => 280, 'atk' => 30, 'exp' => 320, 'gold' => 220],
        'dark_slime' => ['name' => '黑暗史莱姆', 'hp' => 180, 'atk' => 28, 'exp' => 120, 'gold' => 60],
        'swamp_slime' => ['name' => '沼泽史莱姆', 'hp' => 220, 'atk' => 32, 'exp' => 150, 'gold' => 80],
        'swamp_king' => ['name' => '沼泽之王霍克', 'hp' => 800, 'atk' => 45, 'exp' => 800, 'gold' => 500],
        'shadow_rat' => ['name' => '影蚀鼠', 'hp' => 450, 'atk' => 48, 'exp' => 300, 'gold' => 160],
        'shadow_soldier' => ['name' => '影子士兵', 'hp' => 600, 'atk' => 58, 'exp' => 450, 'gold' => 220],
        'corrupt_guard' => ['name' => '腐化守卫', 'hp' => 700, 'atk' => 64, 'exp' => 600, 'gold' => 260],
        'shadow_hound' => ['name' => '影蚀猎犬', 'hp' => 850, 'atk' => 72, 'exp' => 750, 'gold' => 320],
        'corrupt_treant' => ['name' => '腐化树精', 'hp' => 1000, 'atk' => 80, 'exp' => 950, 'gold' => 400],
        'gargoyle' => ['name' => '石像鬼', 'hp' => 1200, 'atk' => 90, 'exp' => 1200, 'gold' => 500],
        'puppet' => ['name' => '符文傀儡', 'hp' => 1500, 'atk' => 105, 'exp' => 1600, 'gold' => 650],
        'apostle' => ['name' => '深渊使徒', 'hp' => 2500, 'atk' => 110, 'exp' => 3000, 'gold' => 1500],
        'guardian' => ['name' => '巨型傀儡·守护者', 'hp' => 4000, 'atk' => 140, 'exp' => 5000, 'gold' => 2500],
        'valentin' => ['name' => '城主瓦伦丁', 'hp' => 5500, 'atk' => 170, 'exp' => 7000, 'gold' => 4000],
        'dark_lily' => ['name' => '暗影莉莉', 'hp' => 7000, 'atk' => 200, 'exp' => 10000, 'gold' => 6000],
        'abyss_eye' => ['name' => '深渊之眼', 'hp' => 10000, 'atk' => 240, 'exp' => 12000, 'gold' => 10000],
        'shadow_walker' => ['name' => '影蚀行者', 'hp' => 1800, 'atk' => 115, 'exp' => 2000, 'gold' => 800],
        'corrupt_warder' => ['name' => '腐影守卫', 'hp' => 2600, 'atk' => 140, 'exp' => 3000, 'gold' => 1200],
        'whisperer' => ['name' => '深渊低语者', 'hp' => 4200, 'atk' => 180, 'exp' => 5500, 'gold' => 2000],
        'colossus' => ['name' => '影蚀巨像', 'hp' => 6500, 'atk' => 230, 'exp' => 9000, 'gold' => 3500],
        'abaddon' => ['name' => '影蚀君王阿巴顿', 'hp' => 20000, 'atk' => 340, 'exp' => 30000, 'gold' => 20000],
        'puppet_guard' => ['name' => '傀儡守卫', 'hp' => 30000, 'atk' => 420, 'exp' => 35000, 'gold' => 18000],
        'masked_noble' => ['name' => '假面贵族', 'hp' => 44000, 'atk' => 520, 'exp' => 52000, 'gold' => 26000],
        'ink_puppet' => ['name' => '墨傀儡', 'hp' => 65000, 'atk' => 680, 'exp' => 80000, 'gold' => 38000],
        'silver_undead' => ['name' => '银丝亡灵', 'hp' => 92000, 'atk' => 840, 'exp' => 115000, 'gold' => 55000],
        'silver_assassin' => ['name' => '银丝刺客', 'hp' => 125000, 'atk' => 1000, 'exp' => 160000, 'gold' => 75000],
        'puppet_priest' => ['name' => '牵线牧师', 'hp' => 165000, 'atk' => 1180, 'exp' => 215000, 'gold' => 100000],
        'star_puppet' => ['name' => '观星傀儡', 'hp' => 210000, 'atk' => 1350, 'exp' => 280000, 'gold' => 130000],
        'silver_puppet' => ['name' => '银丝傀儡', 'hp' => 235000, 'atk' => 1420, 'exp' => 310000, 'gold' => 150000],
        'string_puller' => ['name' => '牵线者', 'hp' => 450000, 'atk' => 1900, 'exp' => 900000, 'gold' => 450000],
        'silk_spider' => ['name' => '银丝蛛', 'hp' => 150000, 'atk' => 1100, 'exp' => 180000, 'gold' => 85000],
        'cocoon_guard' => ['name' => '茧守', 'hp' => 180000, 'atk' => 1220, 'exp' => 220000, 'gold' => 105000],
        'thread_weaver' => ['name' => '织线者', 'hp' => 205000, 'atk' => 1320, 'exp' => 260000, 'gold' => 120000],
        'silk_moth' => ['name' => '银丝蛾', 'hp' => 230000, 'atk' => 1420, 'exp' => 300000, 'gold' => 140000],
        'nest_watcher' => ['name' => '巢穴守望', 'hp' => 260000, 'atk' => 1520, 'exp' => 340000, 'gold' => 160000],
        'brood_maiden' => ['name' => '育巢侍女', 'hp' => 300000, 'atk' => 1650, 'exp' => 400000, 'gold' => 190000],
        'silk_mother' => ['name' => '缠丝之母', 'hp' => 600000, 'atk' => 2200, 'exp' => 1200000, 'gold' => 600000],
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

// 魔钻：库里存0.1钻为单位的整数，只能充值/活动获取，不掉落
function fmt_diamond(int $tenth): string
{
    $tenth = max(0, $tenth);
    $v = number_format($tenth / 10, 1, '.', '');
    $v = rtrim(rtrim($v, '0'), '.');
    return $v . '魔钻';
}

function mall_stones(): array
{
    return [
        'enhance_t1' => 5,
        'enhance_t2' => 20,
        'enhance_t3' => 50,
    ];
}

function mall_tanks(): array
{
    return [
        'hp_tank_s' => ['name' => '小型生命罐', 'kind' => 'hp', 'cap' => 10000, 'price' => 10],
        'hp_tank_m' => ['name' => '中型生命罐', 'kind' => 'hp', 'cap' => 100000, 'price' => 100],
        'hp_tank_l' => ['name' => '大型生命罐', 'kind' => 'hp', 'cap' => 500000, 'price' => 400],
        'mp_tank_s' => ['name' => '小型魔法罐', 'kind' => 'mp', 'cap' => 1000, 'price' => 10],
        'mp_tank_m' => ['name' => '中型魔法罐', 'kind' => 'mp', 'cap' => 10000, 'price' => 100],
        'mp_tank_l' => ['name' => '大型魔法罐', 'kind' => 'mp', 'cap' => 50000, 'price' => 400],
    ];
}

function mall_goods(): array
{
    $g = [];
    foreach (mall_stones() as $mid => $price) {
        $g[$mid] = ['name' => enhance_material_name($mid), 'price' => $price, 'unit' => 1];
    }
    foreach (mall_tanks() as $tid => $t) {
        $g[$tid] = ['name' => $t['name'], 'price' => $t['price'], 'unit' => $t['cap']];
    }
    $g['egg_unknown'] = ['name' => '未知宠物蛋', 'price' => 10, 'unit' => 1];
    $g['exp_card100'] = ['name' => '升级卡100型', 'price' => 100, 'unit' => 1];
    $g['reset_potion'] = ['name' => '属性洗点药', 'price' => 300, 'unit' => 1];
    $g['bag_ext5'] = ['name' => '5格背包扩充', 'price' => 200, 'unit' => 1];
    $g['bag_ext10'] = ['name' => '10格背包扩充', 'price' => 500, 'unit' => 1];
    foreach (['light', 'dark', 'fire', 'wind', 'ice', 'thunder'] as $el) {
        $g['el_' . $el . '_stone'] = ['name' => element_name($el) . '属性石', 'price' => 20, 'unit' => 1];
    }
    $g['dummy_time'] = ['name' => '陪练人偶', 'price' => 50, 'unit' => 3600];
    $g['offline_mod'] = ['name' => '人偶离线升级模块', 'price' => 50, 'unit' => 3600];
    return $g;
}

function offline_tick(array &$u): void
{
    $uid = (int) ($u['id'] ?? 0);
    $mats = mats_of($uid);
    $now = time();
    $seen = (int) ($mats['seen_last'] ?? $now);
    mat_set($uid, 'seen_last', $now);
    if (empty($mats['offline_on']) || empty($mats['dummy_on'])) {
        return;
    }
    if (isset($_SESSION['battle']) && is_array($_SESSION['battle'])) {
        return;
    }
    $gap = $now - $seen;
    if ($gap < 60) {
        return;
    }
    $pool = (int) ($mats['dummy_time'] ?? 0);
    if ($pool <= 0) {
        flash_set('离线' . dummy_fmt($gap) . '，离线模块开着但人偶没时间了，去商城续费。');
        return;
    }
    $here = loc((string) ($u['loc'] ?? ''));
    $mlist = $here['monsters'] ?? [];
    if ($mlist === []) {
        flash_set('离线' . dummy_fmt($gap) . '，你在安全区，人偶没开工。');
        return;
    }
    $allm = monsters();
    $maxFights = min(120, intdiv($gap, 30), intdiv($pool, 30));
    if ($maxFights <= 0) {
        flash_set('离线' . dummy_fmt($gap) . '，人偶剩' . dummy_fmt($pool) . '不够打一场，去续费。');
        return;
    }
    $eq0 = (int) db()->query('SELECT COUNT(*) FROM equips WHERE uid=' . $uid)->fetchColumn();
    $gold0 = (int) $u['gold'];
    $lv0 = (int) $u['lv'];
    $wins = 0;
    $tried = 0;
    $dead = false;
    for ($f = 0; $f < $maxFights; $f++) {
        $tried++;
        $mid = $mlist[array_rand($mlist)];
        $m = $allm[$mid];
        if ($mid === 'echo_rayne' && (int) ($u['quest'] ?? 0) > 14) {
            $m = ['name' => '深渊回响·雷恩', 'hp' => 2000, 'atk' => 85, 'exp' => 2200, 'gold' => 1000];
        }
        $m = scale_monster($m, $mid, 0);
        $isBossOff = ($mid === boss_of_map((string) ($u['loc'] ?? '')));
        $takeOff = spawn_take((string) ($u['loc'] ?? ''), $mid, 0, $isBossOff ? 1 : 6);
        if ($takeOff <= 0) {
            continue;
        }
        $numOff = $isBossOff ? 1 : min(6, $takeOff);
        $b = [
            'id' => $mid, 'name' => $m['name'], 'hp' => $m['hp'], 'maxhp' => $m['hp'],
            'atk' => $m['atk'], 'exp' => $m['exp'], 'gold' => $m['gold'],
            'log' => '', 'num' => $numOff, 'left' => $numOff, 'wexp' => 0, 'wgold' => 0, 'drops' => [], 'last' => $now,
        ];
        $r = ['status' => 'fight', 'log' => '', 'flash' => ''];
        for ($i = 0; $i < 300; $i++) {
            $r = battle_round($u, $b, 'tick');
            if ($r['status'] !== 'fight') {
                break;
            }
        }
        if ($r['status'] === 'fight') {
            unset($_SESSION['battle']);
            break;
        }
        if ($r['status'] === 'dead') {
            $dead = true;
            break;
        }
        $wins++;
    }
    unset($_SESSION['battle']);
    add_mat($uid, 'dummy_time', -$tried * 30);
    mat_set($uid, 'seen_last', $now - max(0, $gap - $tried * 30));
    user_save($u);
    $eq1 = (int) db()->query('SELECT COUNT(*) FROM equips WHERE uid=' . $uid)->fetchColumn();
    $txt = '离线' . dummy_fmt($gap) . '，人偶带打' . $tried . '场胜' . $wins . '场，金+' . fmt_money(max(0, (int) $u['gold'] - $gold0)) . '，装备+' . max(0, $eq1 - $eq0) . '件';
    if ((int) $u['lv'] > $lv0) {
        $txt .= '，连升' . ((int) $u['lv'] - $lv0) . '级';
    }
    if ($dead) {
        db()->prepare('DELETE FROM mats WHERE uid=? AND mat=?')->execute([$uid, 'dummy_on']);
        $txt .= '（陪练中倒下，人偶已停）';
    }
    flash_set($txt);
}

function my_guild(int $uid): ?array
{
    $st = db()->prepare('SELECT g.*, m.role, m.contrib FROM guild_members m JOIN guilds g ON g.id=m.gid WHERE m.uid=?');
    $st->execute([(int) $uid]);
    $row = $st->fetch();
    return $row ?: null;
}

function guild_level_need(int $lv): int
{
    return $lv >= 10 ? 999999999 : $lv * 5000;
}

function guild_add_exp(int $gid, int $exp): string
{
    $st = db()->prepare('SELECT * FROM guilds WHERE id=?');
    $st->execute([(int) $gid]);
    $g = $st->fetch();
    if (!$g) {
        return '';
    }
    $lv = (int) $g['level'];
    $ex = (int) $g['exp'] + $exp;
    $msg = '';
    while ($lv < 10 && $ex >= guild_level_need($lv)) {
        $ex -= guild_level_need($lv);
        $lv++;
        $msg .= '公会升到' . $lv . '级！全员打怪经验+' . $lv . '%。';
    }
    db()->prepare('UPDATE guilds SET level=?, exp=? WHERE id=?')->execute([$lv, $ex, (int) $gid]);
    return $msg;
}

function guild_exp_mult(int $uid): float
{
    $g = my_guild((int) $uid);
    if (!$g) {
        return 1.0;
    }
    return 1 + (int) $g['level'] * 0.01;
}

function guild_create(int $uid, string $name): string
{
    $name = trim(mb_substr($name, 0, 8));
    if ($name === '') {
        return '起个名字。';
    }
    if (my_guild($uid)) {
        return '你已经有公会了。';
    }
    $u = user_by_id($uid);
    if (!$u) {
        return '角色不存在。';
    }
    if ((int) $u['lv'] < 10) {
        return '10级才能创建公会。';
    }
    if ((int) $u['gold'] < 10000) {
        return '创建公会要1金。';
    }
    $st = db()->prepare('SELECT id FROM guilds WHERE name=?');
    $st->execute([$name]);
    if ($st->fetch()) {
        return '这个名字被占了。';
    }
    $u['gold'] = (int) $u['gold'] - 10000;
    user_save($u);
    db()->prepare('INSERT INTO guilds (name, leader_uid, level, exp, created_at) VALUES (?, ?, 1, 0, ?)')->execute([$name, $uid, time()]);
    $gid = (int) db()->lastInsertId();
    db()->prepare('INSERT INTO guild_members (uid, gid, role, contrib, joined_at) VALUES (?, ?, "leader", 0, ?)')->execute([$uid, $gid, time()]);
    return '公会【' . $name . '】成立！你是会长。';
}

function guild_rank(): array
{
    return db()->query('SELECT g.*, (SELECT COUNT(*) FROM guild_members m WHERE m.gid=g.id) AS num FROM guilds g ORDER BY g.level DESC, g.exp DESC, num DESC LIMIT 20')->fetchAll();
}

function open_war(int $gid): ?array
{
    $st = db()->prepare('SELECT * FROM wars WHERE status="open" AND (a_gid=? OR b_gid=?) ORDER BY id DESC LIMIT 1');
    $st->execute([(int) $gid, (int) $gid]);
    $row = $st->fetch();
    return $row ?: null;
}

function declare_war(int $uid, int $targetGid): string
{
    $g = my_guild($uid);
    if (!$g || ($g['role'] ?? '') !== 'leader') {
        return '只有会长能宣战。';
    }
    $myGid = (int) $g['id'];
    if ($targetGid === $myGid) {
        return '不能打自己。';
    }
    $st = db()->prepare('SELECT id FROM guilds WHERE id=?');
    $st->execute([$targetGid]);
    if (!$st->fetch()) {
        return '没有这个公会。';
    }
    if (open_war($myGid) || open_war($targetGid)) {
        return '其中一方已经在打了，打完再来。';
    }
    $u = user_by_id($uid);
    if ((int) $u['gold'] < 5000) {
        return '宣战要50银。';
    }
    $u['gold'] = (int) $u['gold'] - 5000;
    user_save($u);
    db()->prepare('INSERT INTO wars (a_gid, b_gid, ends_at, status) VALUES (?, ?, ?, "open")')->execute([$myGid, $targetGid, time() + 86400]);
    return '宣战成功！24小时内双方成员刷怪涨战功。';
}

function war_add_score(int $uid, string $mid): void
{
    $g = my_guild($uid);
    if (!$g) {
        return;
    }
    $w = open_war((int) $g['id']);
    if (!$w) {
        return;
    }
    $col = ((int) $w['a_gid'] === (int) $g['id']) ? 'a_score' : 'b_score';
    db()->prepare('UPDATE wars SET ' . $col . '=' . $col . '+? WHERE id=?')->execute([monster_lv($mid), (int) $w['id']]);
}

function settle_wars(): void
{
    $st = db()->prepare('SELECT * FROM wars WHERE status="open" AND ends_at<=?');
    $st->execute([time()]);
    while ($w = $st->fetch()) {
        $a = (int) $w['a_score'];
        $b = (int) $w['b_score'];
        $winner = $a === $b ? 0 : ($a > $b ? (int) $w['a_gid'] : (int) $w['b_gid']);
        db()->prepare('UPDATE wars SET status="done", winner=? WHERE id=?')->execute([$winner, (int) $w['id']]);
        if ($winner > 0) {
            guild_add_exp($winner, 5000);
        }
    }
}

function my_party(int $uid): ?array
{
    $st = db()->prepare('SELECT p.* FROM party_members m JOIN parties p ON p.id=m.pid WHERE m.uid=?');
    $st->execute([(int) $uid]);
    $row = $st->fetch();
    return $row ?: null;
}

function party_mates(int $pid, int $uid): array
{
    $st = db()->prepare('SELECT u.id, u.username, u.lv, u.loc FROM party_members m JOIN users u ON u.id=m.uid WHERE m.pid=? AND m.uid!=? ORDER BY m.joined_at');
    $st->execute([(int) $pid, (int) $uid]);
    return $st->fetchAll();
}

function party_bonus_mult(int $uid): float
{
    $p = my_party($uid);
    if (!$p) {
        return 1.0;
    }
    $u = user_by_id($uid);
    if (!$u) {
        return 1.0;
    }
    foreach (party_mates((int) $p['id'], $uid) as $m) {
        if (($m['loc'] ?? '') === ($u['loc'] ?? '')) {
            return 1.1;
        }
    }
    return 1.0;
}

function chat_post(int $uid, string $channel, int $target, string $text): string
{
    $text = trim(mb_substr($text, 0, 60));
    if ($text === '') {
        return '说点什么。';
    }
    $u = user_by_id($uid);
    if (!$u) {
        return '角色不存在。';
    }
    if ($channel === 'guild') {
        $g = my_guild($uid);
        if (!$g) {
            return '你没有公会。';
        }
        $target = (int) $g['id'];
    } elseif ($channel === 'party') {
        $p = my_party($uid);
        if (!$p) {
            return '你没有队伍。';
        }
        $target = (int) $p['id'];
    } else {
        $channel = 'world';
        $target = 0;
    }
    $last = (int) db()->query('SELECT MAX(created_at) FROM chat_msgs WHERE uid=' . (int) $uid)->fetchColumn();
    if (time() - $last < 5) {
        return '说太快了，歇5秒。';
    }
    db()->prepare('INSERT INTO chat_msgs (uid, username, channel, target, text, created_at) VALUES (?, ?, ?, ?, ?, ?)')->execute([$uid, (string) $u['username'], $channel, $target, $text, time()]);
    db()->exec('DELETE FROM chat_msgs WHERE id NOT IN (SELECT id FROM chat_msgs ORDER BY id DESC LIMIT 500)');
    return '';
}

function chat_fetch(string $channel, int $target, int $lastId): array
{
    $st = db()->prepare('SELECT id, username, text, created_at FROM chat_msgs WHERE channel=? AND target=? AND id>? ORDER BY id ASC LIMIT 30');
    $st->execute([$channel, $target, $lastId]);
    return $st->fetchAll();
}

function war_window_open(): bool
{
    $w = (string) date('w');
    $h = (int) date('H');
    return ($w === '0' || $w === '6') && $h === 20;
}

function war_week(): string
{
    return date('Y-W');
}

function warfield_can_enter(array $u): string
{
    if (!war_window_open()) {
        return '荒芜战场只在周六、周日20:00~21:00开放。';
    }
    if (!my_guild((int) ($u['id'] ?? 0))) {
        return '只有公会成员能进战场。';
    }
    return '';
}

function war_my_points(int $uid): int
{
    $st = db()->prepare('SELECT points FROM war_points WHERE uid=? AND week=?');
    $st->execute([(int) $uid, war_week()]);
    return (int) ($st->fetchColumn() ?: 0);
}

function war_rank(string $week): array
{
    $st = db()->prepare('SELECT w.uid, w.points, u.username, g.name AS gname FROM war_points w JOIN users u ON u.id=w.uid LEFT JOIN guilds g ON g.id=w.gid WHERE w.week=? ORDER BY w.points DESC, w.uid LIMIT 10');
    $st->execute([$week]);
    return $st->fetchAll();
}

function war_add_point(int $uid): void
{
    $g = my_guild((int) $uid);
    $st = db()->prepare('UPDATE war_points SET points=points+1, gid=? WHERE uid=? AND week=?');
    $st->execute([(int) ($g['id'] ?? 0), (int) $uid, war_week()]);
    if ($st->rowCount() === 0) {
        db()->prepare('INSERT INTO war_points (uid, week, gid, points, settled) VALUES (?, ?, ?, 1, 0)')->execute([(int) $uid, war_week(), (int) ($g['id'] ?? 0)]);
    }
}

function settle_war_rewards(): void
{
    $cur = war_week();
    $doneWeeks = [];
    $st = db()->prepare('SELECT * FROM war_points WHERE week!=? AND settled=0');
    $st->execute([$cur]);
    while ($row = $st->fetch()) {
        $p = (int) $row['points'];
        if ($p >= 10) {
            $att = [['t' => 'mat', 'id' => 'dummy_time', 'n' => 43200]];
            $title = '公会战奖励（10杀以上）：挂机12小时';
        } elseif ($p >= 5) {
            $att = [['t' => 'mat', 'id' => 'dummy_time', 'n' => 21600]];
            $title = '公会战奖励（5杀以上）：挂机6小时';
        } elseif ($p >= 1) {
            $att = [['t' => 'mat', 'id' => 'dummy_time', 'n' => 7200]];
            $title = '公会战奖励（参与奖）：挂机2小时';
        } else {
            $att = [];
            $title = '';
        }
        if ($att !== []) {
            send_mail((int) $row['uid'], '战场军需官', 'war', $title . '：本周战功' . $p . '分', '荒芜战场结算，附件是你的奖励，请查收。', $att);
        }
        db()->prepare('UPDATE war_points SET settled=1 WHERE uid=? AND week=?')->execute([(int) $row['uid'], (string) $row['week']]);
        $doneWeeks[(string) $row['week']] = true;
    }
    foreach (array_keys($doneWeeks) as $wk) {
        $gr = db()->prepare('SELECT gid, SUM(points) AS tot FROM war_points WHERE week=? AND gid>0 GROUP BY gid ORDER BY tot DESC LIMIT 3');
        $gr->execute([$wk]);
        $rank = 0;
        while ($grow = $gr->fetch()) {
            $rank++;
            if ($rank === 1) {
                $gatt = [['t' => 'mat', 'id' => 'abaddon_egg', 'n' => 1], ['t' => 'mat', 'id' => 'dummy_time', 'n' => 28800]];
                $gt = '冠军公会';
            } elseif ($rank === 2) {
                $gatt = [['t' => 'mat', 'id' => 'egg_unknown', 'n' => 2], ['t' => 'mat', 'id' => 'dummy_time', 'n' => 14400]];
                $gt = '亚军公会';
            } else {
                $gatt = [['t' => 'mat', 'id' => 'egg_unknown', 'n' => 1], ['t' => 'mat', 'id' => 'dummy_time', 'n' => 7200]];
                $gt = '季军公会';
            }
            $gst = db()->prepare('SELECT name FROM guilds WHERE id=?');
            $gst->execute([(int) $grow['gid']]);
            $gname = (string) ($gst->fetchColumn() ?: '');
            $ms = db()->prepare('SELECT uid FROM guild_members WHERE gid=?');
            $ms->execute([(int) $grow['gid']]);
            while ($m = $ms->fetch()) {
                send_mail((int) $m['uid'], '战场军需官', 'war', '公会战' . $gt . '：【' . $gname . '】总战功' . (int) $grow['tot'] . '分', '全员额外奖励，请查收。', $gatt);
            }
        }
    }
}

function war_enemies(int $uid): array
{
    $u = user_by_id((int) $uid);
    $g = my_guild((int) $uid);
    if (!$u || !$g) {
        return [];
    }
    $st = db()->prepare("SELECT u.id, u.username, u.lv, u.hp, u.maxhp, u.atk, u.def, g.name AS gname FROM users u JOIN guild_members m ON m.uid=u.id JOIN guilds g ON g.id=m.gid WHERE u.loc='warfield' AND u.id!=? AND m.gid!=? ORDER BY u.lv DESC LIMIT 20");
    $st->execute([(int) $uid, (int) $g['id']]);
    return $st->fetchAll();
}

function boss_of_map(string $loc): string
{
    static $m = ['boss' => 'knight', 'echo_room' => 'echo_rayne', 'rift' => 'abyss_eye', 'baltar' => 'dark_lily', 'dsw_throne' => 'swamp_king', 'abx_throne' => 'abaddon', 'theater' => 'string_puller', 'mx_heart' => 'silk_mother'];
    return $m[$loc] ?? '';
}

function spawn_tick(): void
{
    if ((int) ($_SESSION['spawn_tick'] ?? 0) > time() - 30) {
        return;
    }
    $_SESSION['spawn_tick'] = time();
    $now = time();
    foreach (locations() as $loc => $L) {
        if (empty($L['monsters'])) {
            continue;
        }
        $st = db()->prepare('SELECT at FROM map_respawn WHERE loc=?');
        $st->execute([$loc]);
        $at = (int) ($st->fetchColumn() ?: 0);
        if ($at > 0 && $now - $at < 180) {
            continue;
        }
        $rs = db()->prepare('UPDATE map_respawn SET at=? WHERE loc=?');
        $rs->execute([$now, $loc]);
        if ($rs->rowCount() === 0) {
            db()->prepare('INSERT INTO map_respawn (loc, at) VALUES (?, ?)')->execute([$loc, $now]);
        }
        $boss = boss_of_map($loc);
        $trash = array_values(array_filter($L['monsters'], fn($mid) => $mid !== $boss));
        db()->prepare('DELETE FROM map_spawns WHERE loc=?')->execute([$loc]);
        $ins = db()->prepare('INSERT INTO map_spawns (loc, mid, elite, num) VALUES (?, ?, ?, ?)');
        if ($boss !== '') {
            $ins->execute([$loc, $boss, 0, 1]);
        }
        $cnt = [];
        for ($i = 0; $i < 20 && $trash !== []; $i++) {
            $mid = $trash[array_rand($trash)];
            $cnt[$mid] = ($cnt[$mid] ?? 0) + 1;
        }
        foreach ($cnt as $mid => $num) {
            $ins->execute([$loc, $mid, 0, $num]);
        }
        if ($trash !== [] && mt_rand(1, 100) <= 40) {
            $ins->execute([$loc, $trash[array_rand($trash)], 1, 1]);
        }
    }
}

function map_spawns(string $loc): array
{
    $st = db()->prepare('SELECT mid, elite, num FROM map_spawns WHERE loc=? AND num>0 ORDER BY elite DESC, mid');
    $st->execute([$loc]);
    return $st->fetchAll();
}

function spawn_take(string $loc, string $mid, int $elite, int $n): int
{
    $st = db()->prepare('SELECT num FROM map_spawns WHERE loc=? AND mid=? AND elite=?');
    $st->execute([$loc, $mid, $elite]);
    $have = (int) ($st->fetchColumn() ?: 0);
    $take = min($n, $have);
    if ($take > 0) {
        db()->prepare('UPDATE map_spawns SET num=num-? WHERE loc=? AND mid=? AND elite=?')->execute([$take, $loc, $mid, $elite]);
    }
    return $take;
}

function spawn_add_elite(int $uid, string $loc, string $mid): void
{
    $st = db()->prepare('SELECT COALESCE(SUM(num),0) FROM map_spawns WHERE loc=? AND elite=1');
    $st->execute([$loc]);
    if ((int) $st->fetchColumn() >= 2) {
        return;
    }
    $es = db()->prepare('UPDATE map_spawns SET num=num+1 WHERE loc=? AND mid=? AND elite=1');
    $es->execute([$loc, $mid]);
    if ($es->rowCount() === 0) {
        db()->prepare('INSERT INTO map_spawns (loc, mid, elite, num) VALUES (?, ?, 1, 1)')->execute([$loc, $mid]);
    }
}

function spawn_respawn_in(string $loc): int
{
    $st = db()->prepare('SELECT at FROM map_respawn WHERE loc=?');
    $st->execute([$loc]);
    $at = (int) ($st->fetchColumn() ?: 0);
    return max(0, 180 - (time() - $at));
}

function horse_names(): array
{
    return ['疾风', '逐影', '烈焰', '奔雷', '踏雪', '流星', '狂沙', '碧蹄', '夜魈', '金鬃'];
}

function horse_period(): int
{
    return (int) floor(time() / 7200);
}

function horse_race(int $pid): array
{
    $st = db()->prepare('SELECT * FROM horse_races WHERE id=?');
    $st->execute([$pid]);
    $row = $st->fetch();
    if (!$row) {
        db()->prepare('INSERT INTO horse_races (id, starts_at, ends_at, status, base_pool) VALUES (?, ?, ?, "open", 500)')->execute([$pid, $pid * 7200, $pid * 7200 + 7200]);
        $st->execute([$pid]);
        $row = $st->fetch();
    }
    return $row;
}

function horse_pool(int $pid): int
{
    $r = horse_race($pid);
    $sum = (int) (db()->query('SELECT COALESCE(SUM(amount),0) FROM horse_bets WHERE race_id=' . $pid)->fetchColumn() ?: 0);
    return (int) $r['base_pool'] + $sum * 10;
}

function horse_bet(int $uid, int $horse, int $amount): string
{
    $pid = horse_period();
    if ($horse < 0 || $horse > 9) {
        return '没这匹马。';
    }
    if ($amount < 1 || $amount > 10) {
        return '每次1~10魔钻。';
    }
    $u = user_by_id((int) $uid);
    if (!$u) {
        return '角色不存在。';
    }
    $st = db()->prepare('SELECT id FROM horse_bets WHERE race_id=? AND uid=?');
    $st->execute([$pid, (int) $uid]);
    if ($st->fetch()) {
        return '这场已经押过了，开赛等结果吧。';
    }
    $cost = $amount * 10;
    if ((int) ($u['diamonds'] ?? 0) < $cost) {
        return '魔钻不够。';
    }
    $u['diamonds'] = (int) ($u['diamonds'] ?? 0) - $cost;
    user_save($u);
    horse_race($pid);
    db()->prepare('INSERT INTO horse_bets (race_id, uid, horse, amount) VALUES (?, ?, ?, ?)')->execute([$pid, (int) $uid, $horse, $amount]);
    return '押下' . horse_names()[$horse] . ' ' . $amount . '魔钻！奖池已到' . fmt_diamond(horse_pool($pid)) . '。';
}

function settle_horse(): void
{
    $pid = horse_period();
    $st = db()->prepare('SELECT * FROM horse_races WHERE id<? AND status="open"');
    $st->execute([$pid]);
    while ($r = $st->fetch()) {
        $rid = (int) $r['id'];
        $horses = range(0, 9);
        shuffle($horses);
        $top = array_slice($horses, 0, 3);
        db()->prepare('UPDATE horse_races SET status="done", result=? WHERE id=?')->execute([implode(',', $top), $rid]);
        $pool = (int) $r['base_pool'] + (int) (db()->query('SELECT COALESCE(SUM(amount),0) FROM horse_bets WHERE race_id=' . $rid)->fetchColumn() ?: 0) * 10;
        $shares = [0 => 0.6, 1 => 0.25, 2 => 0.15];
        $names = horse_names();
        foreach ($top as $rank => $h) {
            $wb = db()->query('SELECT uid, amount FROM horse_bets WHERE race_id=' . $rid . ' AND horse=' . $h)->fetchAll();
            $tot = 0;
            foreach ($wb as $w) {
                $tot += (int) $w['amount'];
            }
            if ($tot <= 0) {
                continue;
            }
            $tier = (int) ($pool * $shares[$rank]);
            foreach ($wb as $w) {
                $win = (int) ($tier * (int) $w['amount'] / $tot);
                if ($win <= 0) {
                    continue;
                }
                $tu = user_by_id((int) $w['uid']);
                if ($tu) {
                    $tu['diamonds'] = (int) ($tu['diamonds'] ?? 0) + $win;
                    $tu['horse_won'] = (int) ($tu['horse_won'] ?? 0) + $win;
                    user_save($tu);
                }
                send_mail((int) $w['uid'], '赛马场', 'horse', '赌马中了！' . $names[$h] . '拿了' . ['冠', '亚', '季'][$rank] . '军', '你押' . ((int) $w['amount']) . '魔钻，分得' . fmt_diamond($win) . '。', [['t' => 'diamond', 'n' => $win]]);
            }
        }
    }
}

function zones(): array
{
    return [
        'z1' => ['name' => '灰烬新区', 'tag' => '火爆新区'],
        'z2' => ['name' => '白石旧忆', 'tag' => '经典怀旧'],
    ];
}

function pres_close(int $uid): array
{
    $u = user_by_id((int) $uid);
    if (!$u) {
        return [];
    }
    $st = db()->prepare("SELECT u.id, u.username, u.lv, u.job FROM users u JOIN mats m ON m.uid=u.id AND m.mat='seen_last' WHERE u.loc=? AND u.zone=? AND u.id!=? AND m.num>=? ORDER BY u.lv DESC LIMIT 10");
    $st->execute([(string) ($u['loc'] ?? ''), (string) ($u['zone'] ?? 'z1'), (int) $uid, time() - 300]);
    return $st->fetchAll();
}

function pet_species(): array
{
    return [
        'slime' => ['name' => '史莱姆', 'quality' => 'common', 'growth' => 0.8, 'atk' => 4, 'def' => 2, 'hp' => 30, 'spd' => 8, 'crit' => 2, 'cd' => 150, 'active' => '猛撞', 'mult' => 1.2, 'passive' => '体魄：生命+10%', 'source' => '商城砸蛋'],
        'stone' => ['name' => '石灵', 'quality' => 'good', 'growth' => 1.0, 'atk' => 7, 'def' => 6, 'hp' => 60, 'spd' => 6, 'crit' => 2, 'cd' => 150, 'active' => '大地之盾', 'mult' => 0, 'passive' => '坚硬：生命+20%', 'source' => '商城砸蛋'],
        'ghost' => ['name' => '幽灵', 'quality' => 'rare', 'growth' => 1.3, 'atk' => 9, 'def' => 4, 'hp' => 45, 'spd' => 15, 'crit' => 5, 'cd' => 160, 'active' => '恐惧尖啸', 'mult' => 1.0, 'passive' => '虚无：承伤-10%', 'source' => '商城砸蛋'],
        'dragon' => ['name' => '幼龙', 'quality' => 'epic', 'growth' => 1.6, 'atk' => 16, 'def' => 8, 'hp' => 90, 'spd' => 14, 'crit' => 8, 'cd' => 180, 'active' => '火焰吐息', 'mult' => 1.3, 'passive' => '龙鳞：防御+15%', 'source' => '商城砸蛋'],
        'shadow_cat' => ['name' => '影猫', 'quality' => 'rare', 'growth' => 1.3, 'atk' => 10, 'def' => 5, 'hp' => 50, 'spd' => 18, 'crit' => 5, 'cd' => 150, 'active' => '暗影突袭', 'mult' => 1.5, 'passive' => '敏捷：速度+10%', 'source' => '雷恩掉落'],
        'abaddon_spawn' => ['name' => '蚀影幼体', 'quality' => 'epic', 'growth' => 1.6, 'atk' => 14, 'def' => 7, 'hp' => 80, 'spd' => 16, 'crit' => 6, 'cd' => 170, 'active' => '蚀影冲击', 'mult' => 1.4, 'passive' => '君王血脉：攻击+10%', 'source' => '阿巴顿掉落'],
        'puppet_doll' => ['name' => '傀儡人偶', 'quality' => 'epic', 'growth' => 1.6, 'atk' => 12, 'def' => 8, 'hp' => 70, 'spd' => 12, 'crit' => 6, 'cd' => 160, 'active' => '提线绞杀', 'mult' => 1.4, 'passive' => '丝甲：承伤-10%', 'source' => '牵线者掉落'],
    ];
}

function pet_quality_name(string $q): string
{
    return ['common' => '普通', 'good' => '优秀', 'rare' => '稀有', 'epic' => '史诗', 'legend' => '传说'][$q] ?? $q;
}

function pet_eggs(): array
{
    return [
        'shadow_cat_egg' => ['species' => 'shadow_cat', 'name' => '雷恩蛋'],
        'egg_slime' => ['species' => 'slime', 'name' => '史莱姆蛋'],
        'egg_stone' => ['species' => 'stone', 'name' => '石灵蛋'],
        'egg_ghost' => ['species' => 'ghost', 'name' => '幽灵蛋'],
        'egg_dragon' => ['species' => 'dragon', 'name' => '幼龙蛋'],
        'abaddon_egg' => ['species' => 'abaddon_spawn', 'name' => '阿巴顿之蛋'],
        'puppet_egg' => ['species' => 'puppet_doll', 'name' => '傀儡人偶蛋'],
        'egg_unknown' => ['species' => '', 'name' => '未知宠物蛋'],
    ];
}

function shop_egg_pool(): array
{
    return ['egg_slime' => 60, 'egg_stone' => 25, 'egg_ghost' => 10, 'egg_dragon' => 5];
}

function pet_stats(array $p): array
{
    $sp = pet_species()[$p['species']] ?? pet_species()['slime'];
    $lv = max(1, (int) $p['level']);
    $qmult = ['common' => 1.0, 'good' => 1.1, 'rare' => 1.2, 'epic' => 1.35, 'legend' => 1.5][$sp['quality']] ?? 1.0;
    $gf = 0.8 + (float) $sp['growth'] * 0.4;
    $s = [
        'atk' => max(1, (int) ((1 + ($lv - 1) * 0.5) * $gf * $qmult)),
        'def' => max(0, (int) ((0.5 + ($lv - 1) * 0.25) * $gf * $qmult)),
        'maxhp' => max(1, (int) ((20 + ($lv - 1) * 8) * (0.8 + (float) $sp['growth'] * 0.3) * $qmult)),
        'spd' => (int) $sp['spd'],
        'crit' => (float) $sp['crit'],
        'cd' => (float) $sp['cd'],
    ];
    if ($p['species'] === 'slime' || $p['species'] === 'stone') {
        $s['maxhp'] = (int) ($s['maxhp'] * ($p['species'] === 'stone' ? 1.2 : 1.1));
    }
    if ($p['species'] === 'dragon') {
        $s['def'] = (int) ($s['def'] * 1.15);
    }
    if ($p['species'] === 'shadow_cat') {
        $s['spd'] = (int) ($s['spd'] * 1.1);
    }
    if ($p['species'] === 'abaddon_spawn') {
        $s['atk'] = (int) ($s['atk'] * 1.1);
    }
    return $s;
}

function my_pets(int $uid): array
{
    $st = db()->prepare('SELECT * FROM pets WHERE uid=? ORDER BY active DESC, level DESC, id');
    $st->execute([(int) $uid]);
    return $st->fetchAll();
}

function active_pet(int $uid): ?array
{
    $st = db()->prepare('SELECT * FROM pets WHERE uid=? AND active=1 AND status="normal" LIMIT 1');
    $st->execute([(int) $uid]);
    $row = $st->fetch();
    return $row ?: null;
}

function pet_set_active(int $uid, int $pid): string
{
    $st = db()->prepare('SELECT * FROM pets WHERE id=? AND uid=?');
    $st->execute([$pid, $uid]);
    $p = $st->fetch();
    if (!$p) {
        return '没有这只宠物。';
    }
    if ($p['status'] !== 'normal') {
        return '虚弱中，先治疗。';
    }
    db()->exec('UPDATE pets SET active=0 WHERE uid=' . (int) $uid);
    db()->prepare('UPDATE pets SET active=1 WHERE id=?')->execute([$pid]);
    $stats = pet_stats($p);
    db()->prepare('UPDATE pets SET hp=? WHERE id=?')->execute([min((int) $p['hp'], $stats['maxhp']), $pid]);
    return '【' . pet_species()[$p['species']]['name'] . '】出战！';
}

function pet_gain_exp(int $uid, int $exp): string
{
    $p = active_pet($uid);
    if (!$p) {
        $all = my_pets($uid);
        $p = $all[0] ?? null;
        if (!$p || $p['status'] !== 'normal') {
            return '';
        }
    }
    $exp = (int) ($exp / 2);
    if ($exp <= 0) {
        return '';
    }
    $lv = (int) $p['level'];
    $pe = (int) $p['exp'] + $exp;
    $msg = '';
    while ($pe >= $lv * 100) {
        $pe -= $lv * 100;
        $lv++;
        $msg .= '【宠物】你的' . pet_species()[$p['species']]['name'] . '升到了' . $lv . '级！';
    }
    $stats = pet_stats(['species' => $p['species'], 'level' => $lv]);
    db()->prepare('UPDATE pets SET level=?, exp=?, hp=? WHERE id=?')->execute([$lv, $pe, $stats['maxhp'], (int) $p['id']]);
    return $msg;
}

function pet_heal_cost(array $p): int
{
    return max(1, (int) $p['level']) * 100;
}

function pet_attack(array &$u, array &$b): string
{
    $p = active_pet((int) ($u['id'] ?? 0));
    if (!$p) {
        return '';
    }
    $stats = pet_stats($p);
    $sp = pet_species()[$p['species']];
    $autoMsg = '';
    if ((int) $p['hp'] <= 0) {
        return '';
    }
    if ((int) ($p['fatigue'] ?? 0) >= 100) {
        return '';
    }
    if ((int) ($p['fatigue'] ?? 0) > 90 && !empty($b['live'])) {
        $mats = mats_of((int) ($u['id'] ?? 0));
        if (!empty($mats['pet_food'])) {
            add_mat((int) ($u['id'] ?? 0), 'pet_food', -1);
            db()->prepare('UPDATE pets SET fatigue=0 WHERE id=?')->execute([(int) $p['id']]);
            $p['fatigue'] = 0;
            $autoMsg = $sp['name'] . '饿得叫，自动吃了一份粮食。';
        }
    }
    db()->prepare('UPDATE pets SET fatigue=? WHERE id=?')->execute([min(100, (int) ($p['fatigue'] ?? 0) + 2), (int) $p['id']]);
    $dmg = $stats['atk'] + random_int(0, 3);
    $useSkill = mt_rand(1, 100) <= 30;
    $tag = '';
    if ($useSkill) {
        if ($p['species'] === 'stone') {
            $heal = (int) ((int) $u['maxhp'] * 0.15);
            $u['hp'] = min((int) $u['maxhp'], (int) $u['hp'] + $heal);
            return $sp['name'] . '施展大地之盾，你恢复' . $heal . '点生命。';
        }
        if ($p['species'] === 'ghost') {
            $b['fear'] = 3;
            $dmg = (int) ($dmg * (float) $sp['mult']);
            $tag = '（恐惧尖啸，敌人变弱）';
        } else {
            $dmg = (int) ($dmg * (float) $sp['mult']);
            $tag = '（' . $sp['active'] . '）';
        }
    }
    if (mt_rand(1, 100) <= $stats['crit']) {
        $dmg = (int) ($dmg * $stats['cd'] / 100);
        $tag .= '（暴击）';
    }
    $dmg = max(1, $dmg);
    $b['hp'] = (int) $b['hp'] - $dmg;
    return $autoMsg . $sp['name'] . '扑向' . $b['name'] . '，造成' . $dmg . '点伤害。' . $tag;
}

function pet_hurt(int $uid, int $md): string
{
    $p = active_pet($uid);
    if (!$p) {
        return '';
    }
    $take = max(1, (int) ($md / 2));
    if ($p['species'] === 'ghost' || $p['species'] === 'puppet_doll') {
        $take = max(1, (int) ($take * 0.9));
    }
    $stats = pet_stats($p);
    $take = max(0, $take - (int) ($stats['def'] / 2));
    $hp = (int) $p['hp'] - $take;
    if ($hp <= 0) {
        db()->prepare('UPDATE pets SET hp=0, status="weak", active=0 WHERE id=?')->execute([(int) $p['id']]);
        return pet_species()[$p['species']]['name'] . '被击倒了，进入虚弱状态！[治疗宠物]';
    }
    db()->prepare('UPDATE pets SET hp=? WHERE id=?')->execute([$hp, (int) $p['id']]);
    return '';
}

function roll_pet_egg(int $uid, string $mid): string
{
    if (mt_rand(1, 100) > 1) {
        return '';
    }
    if ($mid === 'echo_rayne') {
        add_mat($uid, 'shadow_cat_egg', 1);
        return '雷恩蛋';
    }
    if ($mid === 'abaddon') {
        add_mat($uid, 'abaddon_egg', 1);
        return '阿巴顿之蛋';
    }
    if ($mid === 'string_puller') {
        add_mat($uid, 'puppet_egg', 1);
        return '傀儡人偶蛋';
    }
    return '';
}

function mail_unread(int $uid): int
{
    return (int) db()->query('SELECT COUNT(*) FROM mails WHERE uid=' . (int) $uid . ' AND is_read=0')->fetchColumn();
}

function send_mail(int $uid, string $sender, string $type, string $title, string $body, array $att = [], int $pinned = 0): bool
{
    $uid = (int) $uid;
    $cnt = (int) db()->query('SELECT COUNT(*) FROM mails WHERE uid=' . $uid)->fetchColumn();
    if ($cnt >= 50) {
        return false;
    }
    $now = time();
    $st = db()->prepare('INSERT INTO mails (uid, sender, type, title, body, attachments, is_read, claimed, pinned, created_at, expires_at) VALUES (?, ?, ?, ?, ?, ?, 0, 0, ?, ?, ?)');
    $st->execute([$uid, $sender, $type, $title, $body, json_encode($att, JSON_UNESCAPED_UNICODE), $pinned, $now, $now + 30 * 86400]);
    return true;
}

function auction_listable_equip(array $e): bool
{
    if (($e['pos'] ?? '') !== '') {
        return false;
    }
    return in_array(equip_shortname((string) $e['name']), ['沼泽兜帽', '沼泽轻靴', '沼泽之心', '深渊蚀甲', '深渊腿甲', '深渊护手'], true);
}

function auction_min_inc(int $cur): int
{
    return max(1, (int) ceil($cur * 0.05));
}

function auction_fee(int $price, int $dur = 3600): int
{
    return (int) round($price * ($dur >= 86400 ? 0.07 : 0.05));
}

function auction_money_text(int $n, string $currency): string
{
    return $currency === 'diamond' ? fmt_diamond($n) : fmt_money($n);
}

function auction_log(int $uid, string $text): void
{
    $st = db()->prepare('INSERT INTO auction_logs (uid, text, created_at) VALUES (?, ?, ?)');
    $st->execute([(int) $uid, $text, time()]);
}

function auction_give_item(int $uid, array $a): void
{
    if ($a['kind'] === 'equip') {
        $st = db()->prepare('INSERT INTO equips (uid, slot, name, quality, affixes, pos, item_level, enhance_level) VALUES (?, ?, ?, ?, ?, "", ?, ?)');
        $st->execute([$uid, $a['item_slot'], $a['item_name'], (int) $a['item_quality'], (string) $a['item_affixes'], max(1, (int) $a['item_level']), (int) $a['enhance_level']]);
    } else {
        add_mat($uid, (string) $a['mat_id'], max(1, (int) $a['qty']));
    }
}

function auction_item_att(array $a): array
{
    if ($a['kind'] === 'equip') {
        return ['t' => 'equip', 'slot' => $a['item_slot'], 'name' => $a['item_name'], 'q' => (int) $a['item_quality'], 'aff' => (string) $a['item_affixes'], 'il' => (int) $a['item_level'], 'el' => (int) $a['enhance_level']];
    }
    return ['t' => 'mat', 'id' => (string) $a['mat_id'], 'n' => max(1, (int) $a['qty'])];
}

function auction_item_name(array $a): string
{
    if ($a['kind'] === 'equip') {
        return equip_shortname((string) $a['item_name']) . '+' . (int) $a['enhance_level'];
    }
    return mat_name((string) $a['mat_id']) . 'x' . (int) $a['qty'];
}

function settle_auction(int $aid): void
{
    $st = db()->prepare('SELECT * FROM auctions WHERE id=?');
    $st->execute([(int) $aid]);
    $a = $st->fetch();
    if (!$a || $a['status'] !== 'open') {
        return;
    }
    $name = auction_item_name($a);
    if ((int) $a['cur_bidder'] > 0) {
        $price = (int) $a['cur_price'];
        $dur = max(0, (int) $a['ends_at'] - (int) $a['created_at']);
        $rate = $dur >= 86400 ? '7%' : '5%';
        $fee = auction_fee($price, $dur);
        $net = $price - $fee;
        $mt = $a['currency'] === 'diamond' ? 'diamond' : 'gold';
        auction_give_item((int) $a['cur_bidder'], $a);
        send_mail((int) $a['cur_bidder'], '拍卖行', 'auction', '你竞拍的【' . $name . '】已成交', '恭喜，拍卖品已成交，附件即是你的拍品，请查收。', [auction_item_att($a)]);
        send_mail((int) $a['seller_uid'], '拍卖行', 'auction', '你出售的【' . $name . '】已成交', '成交价' . auction_money_text($price, $a['currency']) . '，手续费' . auction_money_text($fee, $a['currency']) . '（' . $rate . '），实收' . auction_money_text($net, $a['currency']) . '。', [['t' => $mt, 'n' => $net]]);
        auction_log((int) $a['seller_uid'], '卖出：' . $name . ' +' . auction_money_text($net, $a['currency']) . '（已扣' . $rate . '手续费）');
        auction_log((int) $a['cur_bidder'], '买入：' . $name . ' -' . auction_money_text($price, $a['currency']));
        db()->prepare("UPDATE auctions SET status='sold' WHERE id=?")->execute([(int) $a['id']]);
    } else {
        send_mail((int) $a['seller_uid'], '拍卖行', 'auction', '你上架的【' . $name . '】已流拍', '无人出价，物品已退回，请查收附件。', [auction_item_att($a)]);
        db()->prepare("UPDATE auctions SET status='expired' WHERE id=?")->execute([(int) $a['id']]);
    }
}

function settle_auctions(): void
{
    $st = db()->prepare("SELECT id FROM auctions WHERE status='open' AND ends_at<=?");
    $st->execute([time()]);
    while ($row = $st->fetch()) {
        settle_auction((int) $row['id']);
    }
}

function mail_att_text(array $att): string
{
    $parts = [];
    foreach ($att as $a) {
        if (($a['t'] ?? '') === 'gold') {
            $parts[] = fmt_money((int) ($a['n'] ?? 0));
        } elseif (($a['t'] ?? '') === 'potion') {
            $parts[] = '回血药x' . (int) ($a['n'] ?? 0);
        } elseif (($a['t'] ?? '') === 'diamond') {
            $parts[] = fmt_diamond((int) ($a['n'] ?? 0));
        } elseif (($a['t'] ?? '') === 'mat') {
            $parts[] = mat_name((string) ($a['id'] ?? '')) . 'x' . (int) ($a['n'] ?? 0);
        } elseif (($a['t'] ?? '') === 'equip') {
            $parts[] = equip_shortname((string) ($a['name'] ?? '')) . '+' . (int) ($a['el'] ?? 0);
        }
    }
    return implode('、', $parts);
}

function claim_mail(int $uid, int $mid): string
{
    $uid = (int) $uid;
    $st = db()->prepare('SELECT * FROM mails WHERE id=? AND uid=?');
    $st->execute([$mid, $uid]);
    $m = $st->fetch();
    if (!$m) {
        return '没有这封邮件。';
    }
    $att = json_decode((string) $m['attachments'], true);
    if (!is_array($att) || $att === []) {
        return '这封邮件没有附件。';
    }
    if ((int) $m['claimed'] === 1) {
        return '附件已经领过了。';
    }
    $u = user_by_id($uid);
    if (!$u) {
        return '角色不存在。';
    }
    foreach ($att as $a) {
        if (($a['t'] ?? '') === 'gold') {
            $u['gold'] = (int) $u['gold'] + max(0, (int) ($a['n'] ?? 0));
        } elseif (($a['t'] ?? '') === 'potion') {
            $u['potion'] = (int) $u['potion'] + max(0, (int) ($a['n'] ?? 0));
        } elseif (($a['t'] ?? '') === 'diamond') {
            $u['diamonds'] = (int) ($u['diamonds'] ?? 0) + max(0, (int) ($a['n'] ?? 0));
        } elseif (($a['t'] ?? '') === 'mat') {
            add_mat($uid, (string) ($a['id'] ?? ''), max(0, (int) ($a['n'] ?? 0)));
        } elseif (($a['t'] ?? '') === 'equip') {
            $st2 = db()->prepare('INSERT INTO equips (uid, slot, name, quality, affixes, pos, item_level, enhance_level) VALUES (?, ?, ?, ?, ?, "", ?, ?)');
            $st2->execute([$uid, (string) ($a['slot'] ?? 'weapon'), (string) ($a['name'] ?? ''), (int) ($a['q'] ?? 0), (string) ($a['aff'] ?? '[]'), max(1, (int) ($a['il'] ?? 1)), (int) ($a['el'] ?? 0)]);
        }
    }
    user_save($u);
    db()->prepare('UPDATE mails SET claimed=1, is_read=1 WHERE id=? AND uid=?')->execute([$mid, $uid]);
    return '你领取了' . mail_att_text($att) . '。(背包查看)';
}

function claim_all_mail(int $uid): string
{
    $uid = (int) $uid;
    $st = db()->prepare('SELECT id FROM mails WHERE uid=? AND claimed=0');
    $st->execute([$uid]);
    $n = 0;
    while ($row = $st->fetch()) {
        $att = json_decode((string) (db()->query('SELECT attachments FROM mails WHERE id=' . (int) $row['id'])->fetchColumn()), true);
        if (!is_array($att) || $att === []) {
            db()->prepare('UPDATE mails SET claimed=1, is_read=1 WHERE id=?')->execute([(int) $row['id']]);
            continue;
        }
        claim_mail($uid, (int) $row['id']);
        $n++;
    }
    return $n > 0 ? '一键领取了' . $n . '封邮件的附件。' : '没有可领取的附件。';
}

function del_read_mail(int $uid): int
{
    $uid = (int) $uid;
    $st = db()->prepare("SELECT id, attachments, claimed FROM mails WHERE uid=? AND is_read=1 AND pinned=0");
    $st->execute([$uid]);
    $n = 0;
    while ($row = $st->fetch()) {
        $att = json_decode((string) $row['attachments'], true);
        if (is_array($att) && $att !== [] && (int) $row['claimed'] === 0) {
            continue;
        }
        db()->prepare('DELETE FROM mails WHERE id=?')->execute([(int) $row['id']]);
        $n++;
    }
    return $n;
}

function mat_hidden(string $mid): bool
{
    return str_starts_with($mid, 'code_') || str_starts_with($mid, 'dummy_') || str_starts_with($mid, 'hatch_') || in_array($mid, ['offline_on', 'seen_last', 'exp_card_until', 'bag_ext5_used', 'bag_ext10_used', 'dummy_total'], true);
}

function mat_set(int $uid, string $mat, int $n): void
{
    $st = db()->prepare('UPDATE mats SET num=? WHERE uid=? AND mat=?');
    $st->execute([$n, (int) $uid, $mat]);
    if ($st->rowCount() === 0) {
        db()->prepare('INSERT INTO mats (uid, mat, num) VALUES (?, ?, ?)')->execute([(int) $uid, $mat, $n]);
    }
}

function dummy_fmt(int $sec): string
{
    $sec = max(0, $sec);
    $h = intdiv($sec, 3600);
    $m = intdiv($sec % 3600, 60);
    if ($h > 0) {
        return $h . '小时' . $m . '分';
    }
    if ($m > 0) {
        return $m . '分';
    }
    return $sec . '秒';
}

function dummy_tick(array &$u): void
{
    $uid = (int) ($u['id'] ?? 0);
    $mats = mats_of($uid);
    if (empty($mats['dummy_on'])) {
        return;
    }
    if (isset($_SESSION['battle']) && is_array($_SESSION['battle'])) {
        return;
    }
    $now = time();
    $last = (int) ($mats['dummy_last'] ?? $now);
    $pool = (int) ($mats['dummy_time'] ?? 0);
    mat_set($uid, 'dummy_last', $now);
    if ($pool <= 0) {
        db()->prepare('DELETE FROM mats WHERE uid=? AND mat=?')->execute([$uid, 'dummy_on']);
        flash_set('陪练人偶没电了，去商城续费。');
        return;
    }
    $elapsed = max(0, min($now - $last, $pool));
    if ($elapsed <= 0) {
        return;
    }
    add_mat($uid, 'dummy_time', -$elapsed);
    $acc = (int) ($mats['dummy_acc'] ?? 0) + $elapsed;
    $fights = min(10, intdiv($acc, 30));
    mat_set($uid, 'dummy_acc', $acc - $fights * 30);
    if ($fights <= 0) {
        user_save($u);
        return;
    }
    $here = loc((string) ($u['loc'] ?? ''));
    $mlist = $here['monsters'] ?? [];
    if ($mlist === []) {
        return;
    }
    $allm = monsters();
    $done = 0;
    $lastFlash = '';
    for ($f = 0; $f < $fights; $f++) {
        $mid = $mlist[array_rand($mlist)];
        $m = $allm[$mid];
        if ($mid === 'echo_rayne' && (int) ($u['quest'] ?? 0) > 14) {
            $m = ['name' => '深渊回响·雷恩', 'hp' => 2000, 'atk' => 85, 'exp' => 2200, 'gold' => 1000];
        }
        $m = scale_monster($m, $mid, 0);
        $isBossDummy = ($mid === boss_of_map((string) ($u['loc'] ?? '')));
        $takeDummy = spawn_take((string) ($u['loc'] ?? ''), $mid, 0, $isBossDummy ? 1 : 6);
        if ($takeDummy <= 0) {
            continue;
        }
        $numDummy = $isBossDummy ? 1 : min(6, $takeDummy);
        $b = [
            'id' => $mid, 'name' => $m['name'], 'hp' => $m['hp'], 'maxhp' => $m['hp'],
            'atk' => $m['atk'], 'exp' => $m['exp'], 'gold' => $m['gold'],
            'log' => '陪练人偶带着你撞上了' . $numDummy . '只' . $m['name'] . '。',
            'num' => $numDummy, 'left' => $numDummy, 'wexp' => 0, 'wgold' => 0, 'drops' => [], 'last' => $now,
        ];
        $r = ['status' => 'fight', 'log' => '', 'flash' => ''];
        for ($i = 0; $i < 500; $i++) {
            $r = battle_round($u, $b, 'tick');
            if ($r['status'] !== 'fight') {
                break;
            }
        }
        if ($r['status'] === 'fight') {
            unset($_SESSION['battle']);
            break;
        }
        $done++;
        if ($r['status'] === 'dead') {
            db()->prepare('DELETE FROM mats WHERE uid=? AND mat=?')->execute([$uid, 'dummy_on']);
            user_save($u);
            flash_set('陪练中' . $r['flash']);
            return;
        }
        $lastFlash = $r['flash'];
    }
    unset($_SESSION['battle']);
    user_save($u);
    if ($done > 0) {
        $tot = (int) (mats_of($uid)['dummy_total'] ?? 0) + $done;
        mat_set($uid, 'dummy_total', $tot);
        flash_set('人偶本次带打' . $done . '场（累计' . $tot . '场）：' . $lastFlash);
    }
}

function tank_auto(array &$u, string $kind): string
{
    $maxk = $kind === 'hp' ? 'maxhp' : 'maxmp';
    $max = (int) ($u[$maxk] ?? 0);
    $cur = (int) ($u[$kind] ?? 0);
    $pct = $kind === 'hp' ? 15 : 30;
    if ($max <= 0 || $cur * 100 >= $max * $pct) {
        return '';
    }
    $order = $kind === 'hp' ? ['hp_tank_s', 'hp_tank_m', 'hp_tank_l'] : ['mp_tank_s', 'mp_tank_m', 'mp_tank_l'];
    $tanks = mall_tanks();
    $mats = mats_of((int) ($u['id'] ?? 0));
    foreach ($order as $tid) {
        $have = (int) ($mats[$tid] ?? 0);
        if ($have <= 0) {
            continue;
        }
        $take = min($max - $cur, $have);
        add_mat((int) $u['id'], $tid, -$take);
        $u[$kind] = $cur + $take;
        return '自动喝下' . $tanks[$tid]['name'] . '，恢复' . $take . '点' . ($kind === 'hp' ? '生命' : '魔力') . '。';
    }
    return '';
}

function redeem_codes(): array
{
    return [
        'WELCOME7' => 50,
        'ASHES1' => 100,
        'TEST1000' => 10000,
    ];
}

function redeem_repeatable(): array
{
    return ['TEST1000'];
}
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
// 槽位：主手/副手/上身/头部/下身/手套/鞋子/背部/戒指x2/项链
function equip_slots(): array
{
    return [
        'weapon' => '主手', 'offhand' => '副手', 'body' => '上身', 'head' => '头部', 'legs' => '下身',
        'gloves' => '手套', 'shoes' => '鞋子', 'back' => '背部', 'ring' => '戒指', 'necklace' => '项链',
    ];
}

function job_can_twohand(string $job): bool
{
    return $job === 'warrior';
}

function twohand_bases(): array
{
    return ['堕落骑士大剑', '君王蚀影刃'];
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
        'element' => ['name' => '元素伤害', 'slots' => ['weapon', 'offhand', 'ring'], 'data' => $r([[2, 4], [4, 8], [8, 13], [13, 19], [15, 25]], 70)],
        'lifesteal' => ['name' => '生命偷取', 'slots' => ['weapon', 'ring'], 'data' => $r([[0.2, 0.5], [0.5, 0.9], [0.9, 1.4], [1.4, 2.0], [2, 3]], 50, 'lifesteal', 1)],
        'res_light' => ['name' => '光抗性', 'slots' => ['body', 'necklace'], 'data' => $r([[1, 2], [2, 4], [4, 7], [7, 11], [10, 15]], 40, '', 1)],
        'res_dark' => ['name' => '暗抗性', 'slots' => ['body', 'necklace'], 'data' => $r([[1, 2], [2, 4], [4, 7], [7, 11], [10, 15]], 40, '', 1)],
        'res_fire' => ['name' => '火抗性', 'slots' => ['body', 'necklace'], 'data' => $r([[1, 2], [2, 4], [4, 7], [7, 11], [10, 15]], 40, '', 1)],
        'res_wind' => ['name' => '风抗性', 'slots' => ['body', 'necklace'], 'data' => $r([[1, 2], [2, 4], [4, 7], [7, 11], [10, 15]], 40, '', 1)],
        'res_ice' => ['name' => '冰抗性', 'slots' => ['body', 'necklace'], 'data' => $r([[1, 2], [2, 4], [4, 7], [7, 11], [10, 15]], 40, '', 1)],
        'res_thunder' => ['name' => '雷抗性', 'slots' => ['body', 'necklace'], 'data' => $r([[1, 2], [2, 4], [4, 7], [7, 11], [10, 15]], 40, '', 1)],
        'allres' => ['name' => '全属性抗性', 'slots' => ['body', 'necklace'], 'data' => $r([[1, 2], [2, 4], [4, 6], [6, 9], [8, 12]], 25, '', 1)],
        'penetration' => ['name' => '护甲穿透', 'slots' => ['weapon'], 'data' => $r([[1, 2], [2, 4], [4, 7], [7, 10], [8, 12]], 60, 'penetration', 1)],
        'skill_damage' => ['name' => '技能伤害', 'slots' => ['weapon'], 'data' => $r([[2, 4], [4, 7], [7, 10], [10, 14], [12, 18]], 70, 'skill_damage', 1)],
        'execute' => ['name' => '处决伤害', 'slots' => ['weapon'], 'data' => $r([[3, 6], [6, 10], [10, 16], [16, 23], [20, 30]], 40, 'execute', 1)],
        'hp' => ['name' => '生命值', 'slots' => ['body', 'head', 'legs', 'shoes', 'ring', 'necklace', 'offhand', 'back'], 'data' => $r([[8, 15], [15, 30], [30, 55], [55, 100], [90, 150]], 100)],
        'def' => ['name' => '护甲', 'slots' => ['body', 'head', 'legs', 'gloves', 'shoes', 'offhand', 'back'], 'data' => $r([[4, 10], [10, 20], [20, 35], [35, 60], [60, 100]], 100)],
        'damage_reduction' => ['name' => '伤害减免', 'slots' => ['body'], 'data' => $r([[0.5, 1], [1, 2], [2, 3.5], [3.5, 5.5], [5, 8]], 70, 'damage_reduction', 1)],
        'resist' => ['name' => '元素抗性', 'slots' => ['body', 'necklace'], 'data' => $r([[1, 2], [2, 4], [4, 7], [7, 11], [10, 15]], 80, 'element_res', 1)],
        'thorns' => ['name' => '荆棘反伤', 'slots' => ['body'], 'data' => $r([[2, 4], [4, 8], [8, 14], [14, 23], [20, 35]], 50)],
        'regen' => ['name' => '生命回复/秒', 'slots' => ['body'], 'data' => $r([[0.5, 1], [1, 2], [2, 3.5], [3.5, 5.5], [5, 8]], 60, 'regen', 1)],
        'energy' => ['name' => '最大能量', 'slots' => ['body', 'necklace'], 'data' => $r([[5, 10], [10, 20], [20, 35], [35, 55], [50, 80]], 60)],
        'cooldown' => ['name' => '技能冷却缩减', 'slots' => ['head'], 'data' => $r([[1, 2], [2, 4], [4, 7], [7, 10], [8, 12]], 70, 'cooldown', 1)],
        'hit' => ['name' => '命中率', 'slots' => ['head', 'gloves'], 'data' => $r([[1, 2], [2, 4], [4, 7], [7, 10], [8, 12]], 60, 'hit', 1)],
        'exp_gain' => ['name' => '经验获取', 'slots' => ['head'], 'data' => $r([[1, 3], [3, 5], [5, 8], [8, 12], [10, 15]], 40, 'exp_gain', 1)],
        'dodge' => ['name' => '闪避率', 'slots' => ['legs', 'shoes', 'back'], 'data' => $r([[0.2, 0.5], [0.5, 1.0], [1.0, 1.8], [1.8, 2.8], [2.8, 4.0]], 80, 'dodge', 1)],
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

function equip_affix_html(array $aff): string
{
    $top = [];
    $sub = [];
    foreach ($aff as $x) {
        $mk = !empty($x['main']) ? '主·' : (!empty($x['legend']) ? '传·' : '');
        $line = '·' . $mk . h(affix_fmt((string) ($x['id'] ?? $x['k'] ?? ''), (float) $x['v'], $x['tier'] ?? null));
        if (!empty($x['main']) || !empty($x['legend'])) {
            $top[] = $line;
        } else {
            $sub[] = $line;
        }
    }
    $out = implode('<br>', $top);
    if ($top !== [] && $sub !== []) {
        $out .= '<br>----<br>';
    }
    $out .= implode('<br>', $sub);
    if ($out !== '') {
        $out .= '<br>';
    }
    return $out;
}

function affix_fmt(string $k, float $v, ?string $tier = null): string
{
    $percent = ['atk_pct', 'crit', 'critdmg', 'speed', 'lifesteal', 'penetration', 'skill_damage', 'execute', 'damage_reduction', 'resist', 'regen', 'cooldown', 'hit', 'exp_gain', 'dodge', 'move_speed', 'control_resist', 'slow_resist', 'gold_gain', 'magic_find', 'res_light', 'res_dark', 'res_fire', 'res_wind', 'res_ice', 'res_thunder', 'allres'];
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
    $flat = ['atk', 'def', 'hp', 'element', 'thorns', 'energy', 'all_attr'];
    $value *= in_array($k, $flat, true) ? 0.1 : 0.3;
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
        'rat' => ['slot' => 'gloves', 'base' => '破布手套', 'rate' => 0.01, 'q' => [100, 0, 0, 0, 0]],
        'rat_pack' => ['slot' => 'gloves', 'base' => '腐骨护手'],
        'slime' => ['slot' => 'shoes', 'base' => '黏液布鞋', 'rate' => 0.012, 'q' => [80, 20, 0, 0, 0]],
        'goblin' => ['slot' => 'weapon', 'base' => '断匕', 'rate' => 0.015, 'q' => [70, 30, 0, 0, 0]],
        'wolf' => ['slot' => 'necklace', 'base' => '狼牙项链', 'rate' => 0.02, 'q' => [50, 35, 15, 0, 0]],
        'banshee' => ['slot' => 'ring', 'base' => '哭泣戒指', 'rate' => 0.025, 'q' => [0, 60, 30, 10, 0]],
        'wraith' => ['slot' => 'back', 'base' => '怨魂斗篷', 'rate' => 0.03, 'q' => [30, 50, 20, 0, 0]],
        'thief' => ['slot' => 'gloves', 'base' => '盗贼手套', 'rate' => 0.025, 'q' => [30, 50, 20, 0, 0]],
        'bee' => ['slot' => 'ring', 'base' => '蜂刺戒指', 'rate' => 0.025, 'q' => [30, 50, 20, 0, 0]],
        'frog' => ['slot' => 'legs', 'base' => '毒蛙腿甲', 'rate' => 0.025, 'q' => [30, 50, 20, 0, 0]],
        'leech' => ['slot' => 'ring', 'base' => '吸血指环', 'rate' => 0.035, 'q' => [0, 55, 35, 10, 0]],
        'ogre' => ['slot' => 'body', 'base' => '兽皮腰带', 'rate' => 0.06, 'q' => [0, 45, 40, 12, 3], 'legendary' => ['base' => '食人魔战裙', 'extra' => ['k' => 'damage_reduction', 'v' => 2.0]]],
        'skeleton' => ['slot' => 'head', 'base' => '锈盔', 'rate' => 0.035, 'q' => [20, 50, 30, 0, 0]],
        'bat' => ['slot' => 'back', 'base' => '蝠翼披风', 'rate' => 0.035, 'q' => [20, 50, 30, 0, 0]],
        'ghoul' => ['slot' => 'weapon', 'base' => '食尸鬼之爪', 'rate' => 0.05, 'q' => [0, 30, 50, 20, 0]],
        'cultist' => ['slot' => 'offhand', 'off' => 'magic', 'base' => '低语法器', 'rate' => 0.05, 'q' => [0, 30, 50, 20, 0]],
        'guard' => ['slot' => 'legs', 'base' => '祭坛护腿', 'rate' => 0.06, 'q' => [0, 20, 50, 30, 0]],
        'jailer' => ['slot' => 'body', 'base' => '狱卒重铠', 'rate' => 0.07, 'q' => [0, 0, 65, 35, 0]],
        'knight' => ['slot' => 'weapon', 'base' => '骑士残剑', 'rate' => 0.25, 'q' => [0, 0, 0, 70, 30], 'legendary' => ['base' => '堕落骑士大剑', 'extra' => ['k' => 'execute', 'v' => 5.0]]],
        'corrupt_rat' => ['slot' => 'gloves', 'base' => '腐化鼠皮手套', 'rate' => 0.03, 'q' => [60, 35, 5, 0, 0]],
        'deep_bat' => ['slot' => 'shoes', 'base' => '深渊蝠翼靴', 'rate' => 0.04, 'q' => [20, 50, 30, 0, 0]],
        'runaway_miner' => ['slot' => 'weapon', 'base' => '矿工重锤', 'rate' => 0.05, 'q' => [0, 35, 45, 20, 0]],
        'abyss_spore' => ['slot' => 'necklace', 'base' => '孢子呼吸器', 'rate' => 0.06, 'q' => [0, 20, 50, 30, 0]],
        'echo_rayne' => ['slot' => 'ring', 'base' => '残响指环', 'rate' => 0.30, 'q' => [0, 0, 20, 50, 30], 'legendary' => ['base' => '雷恩回响戒', 'extra' => ['k' => 'lifesteal', 'v' => 1.0]]],
        'shadow_rat' => ['slot' => 'gloves', 'base' => '影蚀手套'],
        'shadow_soldier' => ['slot' => 'body', 'base' => '影蚀甲'],
        'corrupt_guard' => ['slot' => 'legs', 'base' => '腐化腿甲'],
        'shadow_hound' => ['slot' => 'shoes', 'base' => '影蚀之靴'],
        'corrupt_treant' => ['slot' => 'ring', 'base' => '腐木戒'],
        'gargoyle' => ['slot' => 'head', 'base' => '石像面甲'],
        'puppet' => ['slot' => 'body', 'base' => '符文外壳'],
        'apostle' => ['slot' => 'offhand', 'off' => 'magic', 'base' => '使徒法器'],
        'guardian' => ['slot' => 'offhand', 'off' => 'shield', 'base' => '守护者之盾'],
        'valentin' => ['slot' => 'necklace', 'base' => '城主徽记'],
        'dark_lily' => ['slot' => 'back', 'base' => '暗影披风'],
        'abyss_eye' => ['slot' => 'weapon', 'base' => '深渊之瞳'],
        'shadow_walker' => ['slot' => 'gloves', 'base' => '深渊护手'],
        'corrupt_warder' => ['slot' => 'body', 'base' => '蚀影甲'],
        'whisperer' => ['slot' => 'back', 'base' => '低语披风'],
        'colossus' => ['slot' => 'body', 'base' => '巨像重铠'],
        'abaddon' => ['slot' => 'weapon', 'base' => '君王蚀影刃'],
        'silk_spider' => ['slot' => 'body', 'base' => '剧场·幕布甲'],
        'cocoon_guard' => ['slot' => 'legs', 'base' => '剧场·悬丝裤'],
        'thread_weaver' => ['slot' => 'gloves', 'base' => '剧场·操线手套'],
        'silk_moth' => ['slot' => 'head', 'base' => '剧场·假面'],
        'nest_watcher' => ['slot' => 'shoes', 'base' => '剧场·无声靴'],
        'brood_maiden' => ['slot' => 'offhand', 'off' => 'magic', 'base' => '剧场·提线灯'],
        'silk_mother' => ['slot' => 'weapon', 'base' => '剧场·断线刃'],
    ][$mid] ?? [];
}

function equip_primary(string $slot, string $offKind = ''): string
{
    if ($slot === 'offhand') {
        return $offKind === 'shield' ? 'def' : 'element';
    }
    return ['weapon' => 'atk', 'body' => 'def', 'head' => 'def', 'legs' => 'def', 'gloves' => 'crit', 'shoes' => 'dodge', 'back' => 'dodge', 'ring' => 'hit', 'necklace' => 'energy'][$slot] ?? '';
}

function make_equip(int $uid, string $slot, string $base, int $q, int $itemLevel = 1, ?array $legend = null, string $offKind = ''): string
{
    $toGround = bag_full((int) $uid);
    $primary = equip_primary($slot, $offKind);
    $legendId = (string) ($legend['extra']['k'] ?? '');
    $legendGroup = $legendId !== '' ? (string) (affix_catalog()[$legendId]['data']['group'] ?? '') : '';
    $pool = array_values(array_filter(slot_pools()[$slot] ?? ['atk', 'def', 'hp'], fn($id) => $id !== $primary && $id !== $legendId));
    if ($pool === []) {
        $pool = [$primary !== '' ? $primary : 'atk'];
    }
    $catalog = affix_catalog();
    $selected = [];
    $groups = [];
    if ($legendGroup !== '') {
        $groups[$legendGroup] = true;
    }
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
    if ($primary !== '') {
        $rolled = affix_roll($primary, $q, $itemLevel);
        $affixes[] = ['id' => $primary, 'tier' => $rolled['tier'], 'v' => $rolled['v'], 'main' => true];
    }
    if ($legendId !== '' && isset($legend['extra']['v'])) {
        $affixes[] = ['id' => $legendId, 'tier' => '特', 'v' => (float) $legend['extra']['v'], 'legend' => true];
    }
    $name = equip_fullname($base, $q);
    $st = db()->prepare('INSERT INTO equips (uid, slot, name, quality, affixes, pos, item_level) VALUES (?, ?, ?, ?, ?, ?, ?)');
    $st->execute([$toGround ? 0 : (int) $uid, $slot, $name, $q, json_encode($affixes, JSON_UNESCAPED_UNICODE), $toGround ? 'ground' : '', max(1, $itemLevel)]);
    if ($toGround) {
        $eid = (int) db()->lastInsertId();
        $uo = user_by_id((int) $uid);
        ground_place_equip((string) ($uo['loc'] ?? 'town_sq'), $eid);
        return '';
    }
    return $name;
}

function monster_material(string $mid): array
{
    return [
        'rat' => ['rat_tail', '鼠尾'], 'rat_pack' => ['pack_fang', '鼠群獠牙'], 'slime' => ['slime_gel', '雾凝黏液'],
        'goblin' => ['goblin_ear', '哥布林耳朵'], 'wolf' => ['wolf_fang', '灰狼牙'],
        'banshee' => ['banshee_hair', '女妖发丝'], 'wraith' => ['soul_dust', '残魂尘'],
        'thief' => ['loot_clasp', '赃物铜扣'], 'bee' => ['bee_sting', '巨蜂刺'],
        'frog' => ['frog_sac', '毒蛙囊'], 'leech' => ['blood_clot', '凝血块'],
        'ogre' => ['ogre_knuckle', '食人魔指骨'], 'skeleton' => ['bone_shard', '碎骨'],
        'bat' => ['bat_membrane', '蝠翼膜'], 'ghoul' => ['ghoul_fang', '尸牙'],
        'cultist' => ['whisper_note', '低语纸条'], 'guard' => ['altar_chip', '祭坛铁片'],
        'jailer' => ['jail_rivet', '牢门铆钉'], 'knight' => ['knight_sigil', '黑骑士徽记'],
        'corrupt_rat' => ['corrupt_tail', '腐化鼠尾'], 'deep_bat' => ['deep_membrane', '深渊蝙蝠膜'],
        'abyss_spore' => ['abyss_spore', '腐化孢子'],
        'echo_rayne' => ['echo_shard', '雷恩回响碎片'],
        'shadow_rat' => ['shadow_tail', '影蚀鼠尾'], 'shadow_soldier' => ['shadow_shard', '影子碎片'],
        'corrupt_guard' => ['corrupt_badge', '腐化徽章'], 'shadow_hound' => ['hound_fang', '影蚀犬牙'],
        'corrupt_treant' => ['treant_heart', '腐木心'], 'gargoyle' => ['gargoyle_chip', '石像碎片'],
        'puppet' => ['rune_chip', '符文碎片'], 'apostle' => ['apostle_cloth', '使徒法袍片'],
        'guardian' => ['guardian_core', '守护者核心'], 'valentin' => ['valentin_seal', '城主印戒'],
        'dark_lily' => ['dark_hair', '暗影发丝'], 'abyss_eye' => ['eye_pupil', '深渊眼瞳'],
        'shadow_walker' => ['walker_ash', '行者余烬'], 'corrupt_warder' => ['warder_sigil', '守卫蚀印'],
        'whisperer' => ['whisper_tongue', '低语之舌'], 'colossus' => ['colossus_eye', '巨像之眼'],
        'abaddon' => ['abaddon_horn', '君王断角'],
        'corrupt_rat' => ['corrupt_tail', '腐化鼠尾'], 'deep_bat' => ['deep_membrane', '深渊蝠膜'],
        'runaway_miner' => ['mine_badge', '矿工铭牌'], 'abyss_spore' => ['abyss_spore', '腐化孢子'],
        'echo_rayne' => ['echo_shard', '回响碎片'],
    ][$mid] ?? [];
}

function quest_mats(): array
{
    return ['blackcoin' => '不断下坠的黑币', 'aiden_badge' => '艾登·灰叶的铭牌', 'investigation_record' => '调查队记录', 'augustus_letter' => '镇长的信', 'dsw_ticket' => '黑暗沼泽副本入场券', 'dsw_item1' => '幽暗黏液', 'dsw_item2' => '沼泽之核', 'dsw_item3' => '腐泥之心', 'dsw_item4' => '王座徽记', 'abx_ticket' => '影蚀深渊入场券', 'abx_item1' => '蚀影尘', 'abx_item2' => '影蚀徽记', 'abx_item3' => '低语残章', 'abx_item4' => '王座蚀印', 'capital_badge' => '王都徽章', 'puppet_eye' => '傀儡之眼', 'ch3_proof' => '第三章通关证明', 'mx_ticket' => '银丝母巢入场券', 'mx_item1' => '黏丝束', 'mx_item2' => '茧壳碎片', 'mx_item3' => '织线梭', 'mx_item4' => '蛾翼磷粉', 'mx_item5' => '守望之瞳', 'mx_item6' => '育巢摇篮曲', 'farm_coin' => '农场币', 'seed_green' => '青菜种子', 'seed_radish' => '萝卜种子', 'seed_melon' => '南瓜种子', 'seed_tomato' => '番茄种子', 'seed_strawberry' => '草莓种子'];
}

function skill_catalog(): array
{
    return [
        'w1' => ['name' => '重斩', 'job' => 'warrior', 'mp' => 8, 'kind' => 'phys', 'mult' => 1.5, 'el' => '', 't10' => '伤害+25%', 'lv20' => '狂怒：伤害+15%'],
        'w2' => ['name' => '断岳斩', 'job' => 'warrior', 'mp' => 18, 'kind' => 'phys', 'mult' => 2.2, 'el' => 'fire', 't10' => '本次暴击率+20%', 'lv20' => '伤害+15%'],
        'm1' => ['name' => '火球术', 'job' => 'mage', 'mp' => 12, 'kind' => 'magic', 'mult' => 0, 'el' => 'fire', 't10' => '伤害+30%', 'lv20' => '伤害+15%'],
        'm2' => ['name' => '爆裂火球', 'job' => 'mage', 'mp' => 22, 'kind' => 'magic', 'mult' => 0, 'el' => 'thunder', 't10' => '伤害+30%', 'lv20' => '伤害+15%'],
        'h1' => ['name' => '双连射', 'job' => 'hunter', 'mp' => 10, 'kind' => 'multi2', 'mult' => 1.6, 'el' => 'wind', 't10' => '追加第三箭', 'lv20' => '伤害+15%'],
        'h2' => ['name' => '暴雨连射', 'job' => 'hunter', 'mp' => 20, 'kind' => 'multi2', 'mult' => 2.4, 'el' => 'wind', 't10' => '追加第三箭', 'lv20' => '伤害+15%'],
        'p1' => ['name' => '治疗术', 'job' => 'priest', 'mp' => 10, 'kind' => 'heal', 'mult' => 0.7, 'heal' => 25, 'el' => 'light', 't10' => '治疗+15', 'lv20' => '治疗+20%，伤害+15%'],
        'p2' => ['name' => '圣光庇佑', 'job' => 'priest', 'mp' => 20, 'kind' => 'heal', 'mult' => 1.0, 'heal' => 45, 'el' => 'light', 't10' => '治疗+25', 'lv20' => '治疗+20%，伤害+15%'],
        'w3' => ['name' => '裂地猛击', 'job' => 'warrior', 'mp' => 30, 'kind' => 'phys', 'mult' => 3.0, 'el' => 'thunder', 't10' => '5%眩晕敌人1轮', 'lv20' => '伤害+15%'],
        'm3' => ['name' => '寒霜新星', 'job' => 'mage', 'mp' => 32, 'kind' => 'magic', 'mult' => 0, 'el' => 'ice', 't10' => '5%冻结：敌攻-15%持续3轮', 'lv20' => '伤害+15%'],
        'h3' => ['name' => '淬毒箭', 'job' => 'hunter', 'mp' => 30, 'kind' => 'multi2', 'mult' => 2.0, 'el' => 'dark', 't10' => '剧毒：5%最大生命/轮持续3轮', 'lv20' => '伤害+15%'],
        'p3' => ['name' => '神圣审判', 'job' => 'priest', 'mp' => 30, 'kind' => 'heal', 'mult' => 1.2, 'heal' => 60, 'el' => 'light', 't10' => '恢复10点魔力', 'lv20' => '治疗+20%，伤害+15%'],
    ];
}

function job_skillset(string $job): array
{
    return ['warrior' => ['w1', 'w2', 'w3'], 'mage' => ['m1', 'm2', 'm3'], 'hunter' => ['h1', 'h2', 'h3'], 'priest' => ['p1', 'p2', 'p3']][$job] ?? ['w1', 'w2', 'w3'];
}

function skill_books(): array
{
    return [
        'book_w1' => ['skill' => 'w1', 'name' => '重斩秘籍'],
        'book_w2' => ['skill' => 'w2', 'name' => '断岳斩秘籍'],
        'book_m1' => ['skill' => 'm1', 'name' => '火球术秘籍'],
        'book_m2' => ['skill' => 'm2', 'name' => '爆裂火球秘籍'],
        'book_h1' => ['skill' => 'h1', 'name' => '双连射秘籍'],
        'book_h2' => ['skill' => 'h2', 'name' => '暴雨连射秘籍'],
        'book_p1' => ['skill' => 'p1', 'name' => '治疗术秘籍'],
        'book_p2' => ['skill' => 'p2', 'name' => '圣光秘籍'],
        'book_w3' => ['skill' => 'w3', 'name' => '裂地秘籍'],
        'book_m3' => ['skill' => 'm3', 'name' => '寒霜秘籍'],
        'book_h3' => ['skill' => 'h3', 'name' => '淬毒秘籍'],
        'book_p3' => ['skill' => 'p3', 'name' => '审判秘籍'],
    ];
}

function tier_prof_need(int $tier): int
{
    return [1 => 0, 2 => 100, 3 => 250, 4 => 500, 5 => 900, 6 => 1400, 7 => 2000, 8 => 2700, 9 => 3500, 10 => 4500][$tier] ?? 999999;
}

function my_skills(int $uid): array
{
    $st = db()->prepare('SELECT * FROM skills WHERE uid=? ORDER BY skill');
    $st->execute([$uid]);
    return $st->fetchAll();
}

function learn_skill(int $uid, string $sid): void
{
    $st = db()->prepare('INSERT OR IGNORE INTO skills (uid, skill, level, prof, tier, active) VALUES (?, ?, 1, 0, 1, 0)');
    $st->execute([$uid, $sid]);
    db()->exec('UPDATE skills SET active=0 WHERE uid=' . (int) $uid);
    db()->exec('UPDATE skills SET active=1 WHERE uid=' . (int) $uid . ' AND skill="' . $sid . '"');
}

function active_skill(int $uid, string $job): ?array
{
    $set = job_skillset($job);
    $mine = [];
    foreach (my_skills($uid) as $s) {
        $mine[$s['skill']] = $s;
    }
    foreach ($mine as $sid => $s) {
        if (in_array($sid, $set, true) && !empty($s['active'])) {
            return $s + (skill_catalog()[$sid] ?? []);
        }
    }
    foreach (array_reverse($set) as $sid) {
        if (isset($mine[$sid])) {
            return $mine[$sid] + (skill_catalog()[$sid] ?? []);
        }
    }
    return null;
}

function set_active_skill(int $uid, string $sid): string
{
    $cat = skill_catalog()[$sid] ?? null;
    if (!$cat) {
        return '没有这个技能。';
    }
    $st = db()->prepare('SELECT * FROM skills WHERE uid=? AND skill=?');
    $st->execute([$uid, $sid]);
    if (!$st->fetch()) {
        return '你还没学会这个技能。';
    }
    db()->exec('UPDATE skills SET active=0 WHERE uid=' . (int) $uid);
    db()->prepare('UPDATE skills SET active=1 WHERE uid=? AND skill=?')->execute([$uid, $sid]);
    return '默认技能设为【' . $cat['name'] . '】。';
}

function skill_power(array $s): float
{
    return 1 + max(0, (int) ($s['tier'] ?? 1) - 1) * 0.05 + max(0, (int) ($s['level'] ?? 1) - 1) * 0.04;
}

function add_skill_prof(int $uid, string $sid): string
{
    $st = db()->prepare('SELECT * FROM skills WHERE uid=? AND skill=?');
    $st->execute([$uid, $sid]);
    $s = $st->fetch();
    if (!$s) {
        return '';
    }
    $prof = (int) $s['prof'] + 1;
    $tier = (int) $s['tier'];
    while ($tier < 10 && $prof >= tier_prof_need($tier + 1)) {
        $tier++;
    }
    db()->prepare('UPDATE skills SET prof=?, tier=? WHERE uid=? AND skill=?')->execute([$prof, $tier, $uid, $sid]);
    if ($tier > (int) $s['tier']) {
        return '【' . (skill_catalog()[$sid]['name'] ?? $sid) . '】熟练度升至' . $tier . '阶！';
    }
    return '';
}

function use_skill_book(int $uid, string $book): string
{
    $books = skill_books();
    if (!isset($books[$book])) {
        return '这不是技能书。';
    }
    $sid = $books[$book]['skill'];
    $cat = skill_catalog()[$sid];
    $u = user_by_id($uid);
    if (!$u || job_id_of($u) !== $cat['job']) {
        return '职业不符，只有' . job_of(['job' => $cat['job']])['name'] . '能用。';
    }
    $mats = mats_of($uid);
    if (empty($mats[$book])) {
        return '你没有这本技能书。';
    }
    $st = db()->prepare('SELECT * FROM skills WHERE uid=? AND skill=?');
    $st->execute([$uid, $sid]);
    $s = $st->fetch();
    if (!$s) {
        $set = job_skillset($cat['job']);
        if ($sid === $set[1]) {
            $st2 = db()->prepare('SELECT * FROM skills WHERE uid=? AND skill=?');
            $st2->execute([$uid, $set[0]]);
            if (!$st2->fetch()) {
                return '先学会基础技能，再学进阶。';
            }
        }
        if (($set[2] ?? '') === $sid) {
            $st2 = db()->prepare('SELECT * FROM skills WHERE uid=? AND skill=?');
            $st2->execute([$uid, $set[1]]);
            if (!$st2->fetch()) {
                return '先学会进阶技能，再学终极。';
            }
        }
        add_mat($uid, $book, -1);
        learn_skill($uid, $sid);
        return '学会【' . $cat['name'] . '】！已设为默认技能。';
    }
    if ((int) $s['level'] >= 20) {
        return '技能已满20级。';
    }
    add_mat($uid, $book, -1);
    db()->prepare('UPDATE skills SET level=level+1 WHERE uid=? AND skill=?')->execute([$uid, $sid]);
    return '【' . $cat['name'] . '】升至' . ((int) $s['level'] + 1) . '级！';
}

function roll_skillbook(int $uid, string $mid): string
{
    $rate = ['dark_slime' => 0.005, 'swamp_slime' => 0.008, 'swamp_king' => 0.05][$mid] ?? 0;
    if ($rate <= 0 || mt_rand() / mt_getrandmax() > $rate) {
        return '';
    }
    $books = array_values(array_filter(array_keys(skill_books()), fn($k) => !str_ends_with($k, '_w3') && !str_ends_with($k, '_m3') && !str_ends_with($k, '_h3') && !str_ends_with($k, '_p3')));
    $b = $books[array_rand($books)];
    add_mat($uid, $b, 1);
    return skill_books()[$b]['name'];
}

function roll_abx_skillbook(int $uid, string $mid): string
{
    $rate = ['shadow_walker' => 0.005, 'corrupt_warder' => 0.008, 'whisperer' => 0.01, 'colossus' => 0.015, 'abaddon' => 0.05][$mid] ?? 0;
    if ($rate <= 0 || mt_rand() / mt_getrandmax() > $rate) {
        return '';
    }
    $books = ['book_w3', 'book_m3', 'book_h3', 'book_p3'];
    $b = $books[array_rand($books)];
    add_mat($uid, $b, 1);
    return skill_books()[$b]['name'];
}

function mat_name(string $id): string
{
    $all = [];
    foreach (['rat', 'slime', 'goblin', 'wolf', 'banshee', 'wraith', 'thief', 'bee', 'frog', 'leech', 'ogre', 'skeleton', 'bat', 'ghoul', 'cultist', 'guard', 'jailer', 'knight', 'corrupt_rat', 'deep_bat', 'runaway_miner', 'abyss_spore', 'echo_rayne', 'shadow_rat', 'shadow_soldier', 'corrupt_guard', 'shadow_hound', 'corrupt_treant', 'gargoyle', 'puppet', 'apostle', 'guardian', 'valentin', 'dark_lily', 'abyss_eye', 'shadow_walker', 'corrupt_warder', 'whisperer', 'colossus', 'abaddon'] as $mid) {
        $m = monster_material($mid);
        if ($m !== []) {
            $all[$m[0]] = $m[1];
        }
    }
    if (isset(skill_books()[$id])) {
        return skill_books()[$id]['name'];
    }
    if (isset(mall_tanks()[$id])) {
        return mall_tanks()[$id]['name'];
    }
    if (isset(pet_eggs()[$id])) {
        return pet_eggs()[$id]['name'];
    }
    if ($id === 'pet_revive_potion') {
        return '宠物复活药';
    }
    if ($id === 'pet_food') {
        return '宠物粮食';
    }
    if ($id === 'bag_ext5') {
        return '5格背包扩充';
    }
    if ($id === 'bag_ext10') {
        return '10格背包扩充';
    }
    if ($id === 'dummy_time') {
        return '挂机时间(秒)';
    }
    if ($id === 'offline_mod') {
        return '人偶离线升级模块';
    }
    if ($id === 'exp_card100') {
        return '升级卡100型';
    }
    if ($id === 'reset_potion') {
        return '属性洗点药';
    }
    if ($id === 'exp_card_until') {
        return '双倍经验(剩余)';
    }
    $enm = enchant_mat_name($id);
    if ($enm !== '') {
        return $enm;
    }
    return $all[$id] ?? quest_mats()[$id] ?? enhance_material_name($id);
}

function add_mat(int $uid, string $mat, int $n): void
{
    if ($n === 0) {
        return;
    }
    $st = db()->prepare('UPDATE mats SET num=MAX(0, num+?) WHERE uid=? AND mat=?');
    $st->execute([$n, (int) $uid, $mat]);
    if ($st->rowCount() === 0) {
        db()->prepare('INSERT INTO mats (uid, mat, num) VALUES (?, ?, ?)')->execute([(int) $uid, $mat, max(0, $n)]);
    }
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
    if ($m === [] || mt_rand(1, 100) > 25) {
        return '';
    }
    $n = mt_rand(1, 2);
    add_mat($uid, $m[0], $n);
    return $m[1] . 'x' . $n;
}

function roll_enhance_material(int $uid, string $mid, string $loc = ''): string
{
    $id = '';
    if (in_array($loc, dsw_maps(), true) && $mid === 'swamp_king') {
        $id = 'enhance_t1';
    } elseif (in_array($loc, abx_maps(), true) && $mid === 'abaddon') {
        $id = 'enhance_t1';
    } elseif (in_array($loc, mx_maps(), true) && $mid === 'silk_mother') {
        $id = 'enhance_t2';
    }
    if ($id === '') {
        return '';
    }
    if (mt_rand(1, 100) > 30) {
        return '';
    }
    $n = mt_rand(1, 3);
    add_mat($uid, $id, $n);
    return enhance_material_name($id) . 'x' . $n;
}

function element_name(string $el): string
{
    return ['light' => '光', 'dark' => '暗', 'fire' => '火', 'wind' => '风', 'ice' => '冰', 'thunder' => '雷'][$el] ?? '无';
}

function element_chart(string $atk, string $def): float
{
    if ($atk === '' || $def === '' || $atk === $def) {
        return 1.0;
    }
    static $strong = ['light' => ['dark'], 'dark' => ['light'], 'fire' => ['ice', 'wind'], 'ice' => ['wind'], 'wind' => ['thunder'], 'thunder' => ['fire']];
    if (in_array($def, $strong[$atk] ?? [], true)) {
        return 1.3;
    }
    if (in_array($atk, $strong[$def] ?? [], true)) {
        return 0.7;
    }
    return 1.0;
}

function monster_element(string $mid): string
{
    static $m = [
        'slime' => 'ice', 'goblin' => 'fire', 'banshee' => 'dark', 'wraith' => 'dark', 'bee' => 'wind',
        'leech' => 'dark', 'ogre' => 'fire', 'bat' => 'wind', 'ghoul' => 'dark', 'cultist' => 'fire',
        'knight' => 'dark', 'deep_bat' => 'wind', 'abyss_spore' => 'dark', 'echo_rayne' => 'dark',
        'dark_slime' => 'dark', 'swamp_slime' => 'dark', 'swamp_king' => 'dark',
        'shadow_rat' => 'dark', 'shadow_soldier' => 'dark', 'shadow_hound' => 'dark',
        'gargoyle' => 'thunder', 'puppet' => 'thunder', 'apostle' => 'dark', 'guardian' => 'thunder',
        'valentin' => 'dark', 'dark_lily' => 'dark', 'abyss_eye' => 'dark',
        'shadow_walker' => 'dark', 'corrupt_warder' => 'fire', 'whisperer' => 'dark', 'colossus' => 'thunder', 'abaddon' => 'dark',
        'puppet_guard' => 'thunder', 'masked_noble' => 'dark', 'ink_puppet' => 'dark', 'silver_undead' => 'ice',
        'silver_assassin' => 'wind', 'puppet_priest' => 'light', 'star_puppet' => 'fire', 'silver_puppet' => 'thunder', 'string_puller' => 'dark',
        'silk_spider' => 'wind', 'cocoon_guard' => 'thunder', 'thread_weaver' => 'dark', 'silk_moth' => 'fire',
        'nest_watcher' => 'ice', 'brood_maiden' => 'dark', 'silk_mother' => 'dark',
    ];
    return $m[$mid] ?? '';
}

function monster_resist(string $mid, string $el): float
{
    if ($el === '') {
        return 0;
    }
    static $boss = [
        'knight' => ['dark' => 30], 'echo_rayne' => ['dark' => 30], 'swamp_king' => ['dark' => 20],
        'valentin' => ['dark' => 30], 'dark_lily' => ['dark' => 50, 'light' => 20], 'abyss_eye' => ['dark' => 50],
        'guardian' => ['thunder' => 30], 'abaddon' => ['dark' => 40, 'fire' => 20], 'colossus' => ['thunder' => 30],
    ];
    $r = (float) ($boss[$mid][$el] ?? 0);
    if (monster_element($mid) === $el) {
        $r += 20;
    }
    return min(75, $r);
}

function enchant_tiers(): array
{
    return [
        'stone' => ['name' => '属性石', 'min' => 1, 'max' => 5, 'need' => 10, 'next' => 'crystal'],
        'crystal' => ['name' => '属性晶石', 'min' => 6, 'max' => 15, 'need' => 5, 'next' => 'orb'],
        'orb' => ['name' => '属性珠', 'min' => 16, 'max' => 30, 'need' => 5, 'next' => 'gem'],
        'gem' => ['name' => '属性神石', 'min' => 31, 'max' => 50, 'need' => 0, 'next' => ''],
    ];
}

function enchant_mat_el(string $mid): string
{
    if (!str_starts_with($mid, 'el_')) {
        return '';
    }
    $parts = explode('_', $mid);
    $el = $parts[1] ?? '';
    return in_array($el, ['light', 'dark', 'fire', 'wind', 'ice', 'thunder'], true) ? $el : '';
}

function enchant_mat_tier(string $mid): string
{
    $parts = explode('_', $mid);
    $t = $parts[2] ?? '';
    return isset(enchant_tiers()[$t]) ? $t : '';
}

function enchant_mat_name(string $mid): string
{
    $el = enchant_mat_el($mid);
    $t = enchant_mat_tier($mid);
    if ($el === '' || $t === '') {
        return '';
    }
    return element_name($el) . enchant_tiers()[$t]['name'];
}

function synth_enchant(int $uid, string $mid): string
{
    $el = enchant_mat_el($mid);
    $t = enchant_mat_tier($mid);
    $tiers = enchant_tiers();
    if ($el === '' || $t === '' || ($tiers[$t]['next'] ?? '') === '') {
        return '这个合不了。';
    }
    $need = (int) $tiers[$t]['need'];
    $mats = mats_of((int) $uid);
    if (($mats[$mid] ?? 0) < $need) {
        return '要' . $need . '颗' . enchant_mat_name($mid) . '才合得成。';
    }
    add_mat((int) $uid, $mid, -$need);
    $nm = 'el_' . $el . '_' . $tiers[$t]['next'];
    add_mat((int) $uid, $nm, 1);
    return '合成成功：' . enchant_mat_name($nm) . '×1！';
}

function enchant_equip(int $uid, int $eid, string $mid): string
{
    $el = enchant_mat_el($mid);
    $t = enchant_mat_tier($mid);
    if ($el === '' || $t === '') {
        return '这不是属性石。';
    }
    $mats = mats_of((int) $uid);
    if (empty($mats[$mid])) {
        return '你没有这颗石头。';
    }
    $st = db()->prepare('SELECT * FROM equips WHERE id=? AND uid=?');
    $st->execute([$eid, (int) $uid]);
    $e = $st->fetch();
    if (!$e) {
        return '没有这件装备。';
    }
    $cur = (string) ($e['enchant_el'] ?? '');
    if ($cur !== '' && $cur !== $el) {
        return '这件装备已经是' . element_name($cur) . '附魔，只能继续附同属性。';
    }
    $slot = (string) ($e['slot'] ?? '');
    if (in_array($slot, ['ring', 'necklace'], true)) {
        return '戒指和项链不能附魔。';
    }
    $isWeapon = ($slot === 'weapon');
    $tiers = enchant_tiers();
    $nv = random_int($tiers[$t]['min'], $tiers[$t]['max']);
    add_mat((int) $uid, $mid, -1);
    db()->prepare('UPDATE equips SET enchant_el=?, enchant_val=? WHERE id=?')->execute([$el, $nv, $eid]);
    return '附魔成功！' . element_name($el) . ($isWeapon ? '属性伤害+' . $nv : '属性抗性+' . $nv . '%') . '（同石重附只在' . $tiers[$t]['min'] . '~' . $tiers[$t]['max'] . '波动，不叠加）。';
}

function enchant_el_dmg(int $uid, string $el): int
{
    if ($el === '') {
        return 0;
    }
    $st = db()->prepare('SELECT enchant_val FROM equips WHERE uid=? AND pos="wear" AND enchant_el=? AND slot="weapon"');
    $st->execute([(int) $uid, $el]);
    $tot = 0;
    while ($r = $st->fetch()) {
        $tot += (int) $r['enchant_val'];
    }
    return $tot;
}

function enchant_el_res(int $uid, string $el): float
{
    if ($el === '') {
        return 0;
    }
    $st = db()->prepare('SELECT enchant_val FROM equips WHERE uid=? AND pos="wear" AND enchant_el=? AND slot!="weapon"');
    $st->execute([(int) $uid, $el]);
    $tot = 0;
    while ($r = $st->fetch()) {
        $tot += (int) $r['enchant_val'];
    }
    return (float) $tot;
}

function bag_size(int $uid): int
{
    $mats = mats_of((int) $uid);
    return 30 + 5 * (int) ($mats['bag_ext5_used'] ?? 0) + 10 * (int) ($mats['bag_ext10_used'] ?? 0);
}

function bag_count(int $uid): int
{
    return (int) (db()->query('SELECT COUNT(*) FROM equips WHERE uid=' . (int) $uid)->fetchColumn() ?: 0);
}

function bag_full(int $uid): bool
{
    return bag_count((int) $uid) >= bag_size((int) $uid);
}

function use_bag_ext(int $uid, string $mid): string
{
    $caps = ['bag_ext5' => [5, 5, 'bag_ext5_used'], 'bag_ext10' => [10, 2, 'bag_ext10_used']];
    if (!isset($caps[$mid])) {
        return '这不是背包扩充。';
    }
    [$slots, $maxUse, $usedKey] = $caps[$mid];
    $mats = mats_of((int) $uid);
    if (empty($mats[$mid])) {
        return '你没有这个扩充。';
    }
    if ((int) ($mats[$usedKey] ?? 0) >= $maxUse) {
        return '这个扩充最多用' . $maxUse . '次，到顶了。';
    }
    add_mat((int) $uid, $mid, -1);
    add_mat((int) $uid, $usedKey, 1);
    return '背包扩充+' . $slots . '格！现在' . bag_size((int) $uid) . '格。';
}

function ground_tick(): void
{
    $cut = time() - 60;
    $st = db()->prepare('SELECT id, ref FROM ground_items WHERE kind="equip" AND at<?');
    $st->execute([$cut]);
    while ($r = $st->fetch()) {
        db()->exec('DELETE FROM equips WHERE id=' . (int) $r['ref'] . ' AND uid=0 AND pos="ground"');
    }
    db()->prepare('DELETE FROM ground_items WHERE at<?')->execute([$cut]);
}

function ground_trim(string $loc): void
{
    $n = (int) (db()->query('SELECT COUNT(*) FROM ground_items WHERE loc=' . db()->quote($loc))->fetchColumn() ?: 0);
    if ($n > 50) {
        db()->exec('DELETE FROM ground_items WHERE id IN (SELECT id FROM ground_items WHERE loc=' . db()->quote($loc) . ' ORDER BY at LIMIT ' . ($n - 50) . ')');
    }
}

function ground_place_mat(string $loc, string $mid, int $num): void
{
    db()->prepare('INSERT INTO ground_items (loc, kind, ref, num, at) VALUES (?, "mat", ?, ?, ?)')->execute([$loc, $mid, max(1, $num), time()]);
    ground_trim($loc);
}

function ground_place_equip(string $loc, int $eid): void
{
    db()->prepare('INSERT INTO ground_items (loc, kind, ref, num, at) VALUES (?, "equip", ?, 1, ?)')->execute([$loc, (string) $eid, time()]);
    ground_trim($loc);
}

function ground_list(string $loc): array
{
    $st = db()->prepare('SELECT * FROM ground_items WHERE loc=? ORDER BY at DESC LIMIT 50');
    $st->execute([$loc]);
    return $st->fetchAll();
}

function ground_pickup(int $uid, int $gid): string
{
    $st = db()->prepare('SELECT * FROM ground_items WHERE id=?');
    $st->execute([$gid]);
    $g = $st->fetch();
    if (!$g) {
        return '地上啥也没有了。';
    }
    if (($g['kind'] ?? '') === 'equip') {
        if (bag_full((int) $uid)) {
            return '背包满了，捡不起来。';
        }
        $st2 = db()->prepare('SELECT * FROM equips WHERE id=? AND uid=0 AND pos="ground"');
        $st2->execute([(int) $g['ref']]);
        $e = $st2->fetch();
        if (!$e) {
            db()->prepare('DELETE FROM ground_items WHERE id=?')->execute([$gid]);
            return '那件装备已经烂没了。';
        }
        db()->prepare('UPDATE equips SET uid=?, pos="" WHERE id=?')->execute([(int) $uid, (int) $e['id']]);
        db()->prepare('DELETE FROM ground_items WHERE id=?')->execute([$gid]);
        _gear_uncache((int) $uid);
        return '捡起【' . equip_shortname((string) $e['name']) . '】。';
    }
    add_mat((int) $uid, (string) $g['ref'], (int) $g['num']);
    db()->prepare('DELETE FROM ground_items WHERE id=?')->execute([$gid]);
    return '捡起【' . mat_name((string) $g['ref']) . '】x' . (int) $g['num'] . '。';
}

function is_boss_mid(string $mid): bool
{
    static $bosses = ['knight', 'echo_rayne', 'swamp_king', 'abaddon', 'abyss_eye', 'dark_lily', 'valentin', 'guardian', 'string_puller', 'silk_mother'];
    return in_array($mid, $bosses, true);
}

function is_ch3_mid(string $mid): bool
{
    static $mids = ['puppet_guard', 'masked_noble', 'ink_puppet', 'silver_undead', 'silver_assassin', 'puppet_priest', 'star_puppet', 'silver_puppet', 'string_puller', 'silk_spider', 'cocoon_guard', 'thread_weaver', 'silk_moth', 'nest_watcher', 'brood_maiden', 'silk_mother'];
    return in_array($mid, $mids, true);
}

function scale_monster(array $m, string $mid, int $elite = 0): array
{
    if (is_ch3_mid($mid)) {
        if ($elite === 1) {
            $m['hp'] = (int) ($m['hp'] * 1.6);
            $m['maxhp'] = (int) ($m['hp']);
            $m['atk'] = (int) ($m['atk'] * 1.4);
        }
        return $m;
    }
    $lv = min(120, monster_lv($mid));
    $hm = 1 + $lv * 0.008;
    $am = 1 + $lv * 0.005;
    if (is_boss_mid($mid)) {
        $hm *= 1.5;
        $am *= 1.3;
    }
    if ($elite === 1) {
        $hm *= 1.6;
        $am *= 1.4;
    }
    $m['hp'] = (int) ($m['hp'] * $hm);
    $m['maxhp'] = (int) ($m['hp']);
    $m['atk'] = (int) ($m['atk'] * $am);
    return $m;
}

function power_score(array $u): int
{
    $gs = gear_stats((int) ($u['id'] ?? 0));
    $atk = (int) $u['atk'] + (int) ($u['str'] ?? 0) + (int) $gs['atk'];
    $def = (int) $u['def'] + (int) ($u['agi'] ?? 0) + (int) $gs['def'];
    return (int) ($atk * 2 + $def * 1.5 + (int) $u['maxhp'] / 10 + (int) ($u['maxmp'] ?? 0) / 5 + (int) $u['lv'] * 5);
}

function fmt_playtime(int $secs): string
{
    if ($secs < 3600) {
        return max(1, intdiv($secs, 60)) . '分钟';
    }
    return round($secs / 3600, 1) . '小时';
}

function ch3_drops(): array
{
    return [
        'puppet_guard' => [['weapon', '傀儡短剑', ''], ['body', '傀儡皮甲', '']],
        'masked_noble' => [['weapon', '傀儡短剑', ''], ['body', '傀儡皮甲', '']],
        'ink_puppet' => [['offhand', '傀儡法杖', 'magic'], ['necklace', '傀儡项链', '']],
        'silver_undead' => [['weapon', '傀儡大剑', ''], ['body', '傀儡铠甲', '']],
        'silver_assassin' => [['weapon', '银丝短剑', ''], ['body', '银丝皮甲', '']],
        'puppet_priest' => [['offhand', '牵线法杖', 'magic'], ['necklace', '牵线项链', '']],
        'star_puppet' => [['offhand', '星眼法杖', 'magic'], ['necklace', '星眼项链', '']],
        'silver_puppet' => [['offhand', '银丝法杖', 'magic'], ['necklace', '银丝项链', '']],
    ];
}

function roll_ch3_drop(int $uid, string $mid, int $elite = 0): string
{
    $tab = ch3_drops()[$mid] ?? null;
    if ($tab === null) {
        return '';
    }
    $m = 1 + monster_lv($mid) * 0.03 + min(200, gear_stats($uid)['magic_find']) / 100;
    $one = function () use ($uid, $mid, $tab, $m) {
        if (mt_rand() / mt_getrandmax() > 0.05 * $m) {
            return '';
        }
        $r = mt_rand(1, 100);
        $q = $r <= 55 ? 0 : ($r <= 87 ? 1 : 2);
        $e = $tab[array_rand($tab)];
        return make_equip($uid, $e[0], $e[1], $q, monster_lv($mid), null, $e[2]);
    };
    $a = $one();
    if ($elite === 1) {
        $b = $one();
        if ($a !== '' && $b !== '') {
            return $a . '】【' . $b;
        }
        return $a . $b;
    }
    return (string) $a;
}

function player_resist(array $u, string $el): float
{
    $gs = gear_stats((int) ($u['id'] ?? 0));
    $r = (float) ($gs['res_' . $el] ?? 0) + (float) ($gs['allres'] ?? 0) + (float) ($gs['resist'] ?? 0) + enchant_el_res((int) ($u['id'] ?? 0), $el);
    return min(75, $r);
}

function monster_dodge(string $mid): float
{
    static $d = ['bat' => 6, 'wraith' => 5, 'thief' => 5, 'cultist' => 3, 'knight' => 10, 'echo_rayne' => 12, 'bee' => 4, 'deep_bat' => 6];
    return (float) ($d[$mid] ?? 0);
}

function roll_drop(int $uid, string $mid, int $elite = 0): string
{
    $ch3 = roll_ch3_drop($uid, $mid, $elite);
    if ($ch3 !== '' || isset(ch3_drops()[$mid])) {
        return $ch3;
    }
    $t = monster_drops($mid);
    if ($t === []) {
        return '';
    }
    $mlv = monster_lv($mid);
    $m = 1 + $mlv * 0.03 + min(200, gear_stats($uid)['magic_find']) / 100;
    $chances = [4 => 0.0002 * $m, 3 => 0.0007 * $m, 2 => 0.0025 * $m, 1 => 0.01 * $m, 0 => 0.04 * $m];
    $roll = mt_rand() / mt_getrandmax();
    $q = -1;
    foreach ($chances as $qq => $ch) {
        if ($roll < $ch) {
            $q = $qq;
            break;
        }
    }
    if ($q < 0) {
        return '';
    }
    if ($elite === 1) {
        $q = min(4, $q + 1);
    }
    if ($q === 4) {
        if (!isset($t['legendary'])) {
            $q = 3;
        } else {
            return make_equip($uid, $t['slot'], $t['legendary']['base'], 4, monster_lv($mid), $t['legendary']);
        }
    }
    return make_equip($uid, $t['slot'], $t['base'], $q, monster_lv($mid), null, (string) ($t['off'] ?? ''));
}

// 已穿装备属性总和（内存缓存一次）
function gear_stats(int $uid): array
{
    static $cache = [];
    if (isset($cache[$uid]) && ($GLOBALS['_gear_bust'] ?? null) !== $uid) {
        return $cache[$uid];
    }
    unset($GLOBALS['_gear_bust']);
    $keys = ['atk', 'def', 'hp', 'crit', 'critdmg', 'dodge', 'lifesteal', 'atk_pct', 'speed', 'element', 'penetration', 'skill_damage', 'execute', 'damage_reduction', 'resist', 'thorns', 'regen', 'energy', 'cooldown', 'hit', 'exp_gain', 'move_speed', 'control_resist', 'slow_resist', 'all_attr', 'gold_gain', 'magic_find', 'res_light', 'res_dark', 'res_fire', 'res_wind', 'res_ice', 'res_thunder', 'allres'];
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
    $st2 = db()->prepare('SELECT name FROM equips WHERE uid=? AND pos="wear"');
    $st2->execute([$uid]);
    $worn = [];
    while ($r2 = $st2->fetch()) {
        $worn[] = equip_shortname((string) $r2['name']);
    }
    if (in_array('沼泽兜帽', $worn, true) && in_array('沼泽轻靴', $worn, true) && in_array('沼泽之心', $worn, true)) {
        $s['energy'] += 300;
        $s['def'] += 20;
    }
    if (in_array('深渊蚀甲', $worn, true) && in_array('深渊腿甲', $worn, true) && in_array('深渊护手', $worn, true)) {
        $s['atk'] += 25;
        $s['def'] += 15;
    }
    $theater = 0;
    foreach ($worn as $wn) {
        if (str_starts_with($wn, '剧场·')) {
            $theater++;
        }
    }
    if ($theater >= 3) {
        $s['atk_pct'] += 15;
    }
    if ($theater >= 5) {
        $s['element'] += 80;
    }
    if ($theater >= 7) {
        $s['damage_reduction'] += 12;
        $s['skill_damage'] += 20;
    }
    $qu = db()->prepare('SELECT quest FROM users WHERE id=?');
    $qu->execute([$uid]);
    if ((int) ($qu->fetchColumn() ?: 0) >= 38) {
        $s['atk_pct'] += 5;
        $s['all_attr'] += 10;
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
    return [10, 25, 60, 150, 400][$q] ?? 10;
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
    $isTwo = in_array(equip_shortname((string) $eq['name']), twohand_bases(), true);
    if ($isTwo) {
        $uo = user_by_id($uid);
        if (!job_can_twohand((string) ($uo['job'] ?? ''))) {
            return '双手武器太沉，需要战士（或相关转职）才能挥动。';
        }
    }
    if (($eq['slot'] ?? '') === 'offhand') {
        $mw = db()->query('SELECT name FROM equips WHERE uid=' . $uid . ' AND slot="weapon" AND pos="wear" LIMIT 1')->fetchColumn();
        if ($mw && in_array(equip_shortname((string) $mw), twohand_bases(), true)) {
            return '主手是双手武器，副手没空位。';
        }
    }
    $before = gear_stats($uid)['hp'];
    $mBefore = gear_stats($uid)['energy'];
    if ($eq['slot'] === 'ring') {
        $cnt = (int) db()->query('SELECT COUNT(*) FROM equips WHERE uid=' . $uid . ' AND slot="ring" AND pos="wear"')->fetchColumn();
        if ($cnt >= 2) {
            db()->exec('UPDATE equips SET pos="" WHERE id=(SELECT id FROM equips WHERE uid=' . $uid . ' AND slot="ring" AND pos="wear" ORDER BY id LIMIT 1)');
        }
        db()->exec('UPDATE equips SET pos="wear" WHERE id=' . $eid);
    } else {
        db()->exec('UPDATE equips SET pos="" WHERE uid=' . $uid . ' AND slot="' . $eq['slot'] . '" AND pos="wear"');
        db()->exec('UPDATE equips SET pos="wear" WHERE id=' . $eid);
        if ($isTwo && ($eq['slot'] ?? '') === 'weapon') {
            db()->exec('UPDATE equips SET pos="" WHERE uid=' . $uid . ' AND slot="offhand" AND pos="wear"');
        }
    }
    // 清缓存并同步生命上限
    _gear_uncache($uid);
    $after = gear_stats($uid)['hp'];
    $mAfter = gear_stats($uid)['energy'];
    $delta = (int) ($after - $before);
    $mDelta = (int) ($mAfter - $mBefore);
    if ($delta !== 0) {
        $u = user_by_id($uid);
        if ($u) {
            $u['maxhp'] = max(1, (int) $u['maxhp'] + $delta);
            $u['hp'] = min((int) $u['maxhp'], max(1, (int) $u['hp'] + max(0, $delta)));
            $u['maxmp'] = max(0, (int) ($u['maxmp'] ?? 0) + $mDelta);
            $u['mp'] = min((int) $u['maxmp'], max(0, (int) ($u['mp'] ?? 0) + max(0, $mDelta)));
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
        $u['maxmp'] = max(0, (int) ($u['maxmp'] ?? 0) + $mDelta);
        $u['mp'] = min((int) $u['maxmp'], (int) ($u['mp'] ?? 0));
        user_save($u);
    }
    return '脱下了【' . $eq['name'] . '】。';
}

function player_atk(array $u): int
{
    return (int) $u['atk'] + (int) ($u['str'] ?? 0) + (int) gear_stats((int) ($u['id'] ?? 0))['atk'];
}

function player_def(array $u): int
{
    return (int) $u['def'] + (int) ($u['agi'] ?? 0) + (int) gear_stats((int) ($u['id'] ?? 0))['def'];
}

function exp_need(int $lv): int
{
    return max(20, (int) (20 * pow(max(1, $lv), 1.65)));
}

function monster_lv(string $mid): int
{
    static $lv = [
        'rat' => 1, 'goblin' => 2, 'banshee' => 3, 'ogre' => 4,
        'slime' => 4, 'wolf' => 5, 'rat_pack' => 18, 'wraith' => 19, 'thief' => 6, 'bee' => 6,
        'frog' => 7, 'leech' => 21, 'skeleton' => 8, 'bat' => 8,
        'ghoul' => 9, 'cultist' => 9, 'guard' => 23, 'jailer' => 25, 'knight' => 14,
        'corrupt_rat' => 6, 'deep_bat' => 8, 'runaway_miner' => 9, 'abyss_spore' => 10, 'echo_rayne' => 14,
        'shadow_rat' => 30, 'shadow_soldier' => 35, 'corrupt_guard' => 38, 'shadow_hound' => 42, 'corrupt_treant' => 46,
        'gargoyle' => 50, 'puppet' => 55, 'apostle' => 45, 'guardian' => 55, 'valentin' => 62, 'dark_lily' => 66, 'abyss_eye' => 70,
        'shadow_walker' => 55, 'corrupt_warder' => 65, 'whisperer' => 80, 'colossus' => 95, 'abaddon' => 120,
        'dark_slime' => 20, 'swamp_slime' => 21, 'swamp_king' => 25,
        'puppet_guard' => 130, 'masked_noble' => 150, 'ink_puppet' => 180, 'silver_undead' => 210,
        'silver_assassin' => 240, 'puppet_priest' => 270, 'star_puppet' => 300, 'silver_puppet' => 310, 'string_puller' => 320,
        'silk_spider' => 280, 'cocoon_guard' => 295, 'thread_weaver' => 305, 'silk_moth' => 315,
        'nest_watcher' => 325, 'brood_maiden' => 335, 'silk_mother' => 340,
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
    return (int) ($base * min(1.5, 1 + $diff * 0.05));
}

function death_penalty(array &$u): string
{
    $gloss = (int) ((int) $u['gold'] * 0.3);
    $u['gold'] = max(0, (int) $u['gold'] - $gloss);
    $eloss = (int) (exp_need((int) $u['lv']) * 0.3);
    $u['exp'] = max(0, (int) $u['exp'] - $eloss);
    $u['hp'] = 1;
    $u['loc'] = 'camp';
    return '丢了' . fmt_money($gloss) . '，经验-' . $eloss . '。';
}

function gain_exp(array &$u, int $exp): string
{
    $card = mats_of((int) ($u['id'] ?? 0))['exp_card_until'] ?? 0;
    if ($card > time()) {
        $exp = $exp * 2;
    }
    $u['exp'] = (int) $u['exp'] + $exp;
    $msg = '经验+' . $exp;
    while ((int) $u['exp'] >= exp_need((int) $u['lv'])) {
        $u['exp'] -= exp_need((int) $u['lv']);
        $u['lv'] = (int) $u['lv'] + 1;
        $u['maxhp'] = (int) $u['maxhp'] + 6;
        $u['atk'] = (int) $u['atk'] + 1;
        $u['def'] = (int) $u['def'] + 1;
        $u['hp'] = (int) $u['maxhp'];
        $u['mp'] = (int) ($u['maxmp'] ?? 0);
        $u['s_pts'] = (int) ($u['s_pts'] ?? 0) + 3;
        $msg .= '！升级至' . $u['lv'] . '级，伤势尽复，得3属性点';
    }
    return $msg;
}

function alloc_stat(int $uid, string $k): string
{
    $u = user_by_id((int) $uid);
    if (!$u) {
        return '角色不存在。';
    }
    $cost = ['str' => 3, 'agi' => 3, 'vit' => 1, 'int' => 1][$k] ?? 0;
    if ($cost <= 0) {
        return '只能加力量/坚毅/体质/智慧（力量坚毅3点1次，体质智慧1点1次）。';
    }
    if ((int) ($u['s_pts'] ?? 0) < $cost) {
        return '属性点不够（剩' . (int) ($u['s_pts'] ?? 0) . '点，要' . $cost . '点），升级获取。';
    }
    if ($k === 'str') {
        $u['str'] = (int) ($u['str'] ?? 0) + 1;
    } elseif ($k === 'agi') {
        $u['agi'] = (int) ($u['agi'] ?? 0) + 1;
    } elseif ($k === 'vit') {
        $u['maxhp'] = (int) $u['maxhp'] + 5;
        $u['hp'] = (int) $u['hp'] + 5;
        $u['vit'] = (int) ($u['vit'] ?? 0) + 1;
    } elseif ($k === 'int') {
        $u['maxmp'] = (int) ($u['maxmp'] ?? 0) + 3;
        $u['mp'] = (int) ($u['mp'] ?? 0) + 3;
        $u['int'] = (int) ($u['int'] ?? 0) + 1;
    }
    $u['s_pts'] = (int) ($u['s_pts'] ?? 0) - $cost;
    user_save($u);
    return '分配成功（-' . $cost . '点）。';
}

function alloc_all_stat(int $uid, string $k): string
{
    $n = 0;
    $cost = ['str' => 3, 'agi' => 3, 'vit' => 1, 'int' => 1][$k] ?? 0;
    if ($cost <= 0) {
        return '只能加力量/坚毅/体质/智慧。';
    }
    while (true) {
        $u = user_by_id((int) $uid);
        if (!$u || (int) ($u['s_pts'] ?? 0) < $cost) {
            break;
        }
        alloc_stat((int) $uid, $k);
        $n++;
        if ($n > 1000) {
            break;
        }
    }
    $nm = ['str' => '力量', 'agi' => '坚毅', 'vit' => '体质', 'int' => '智慧'][$k];
    return $n > 0 ? '全部投入' . $nm . $n . '次（-' . ($n * $cost) . '点）。' : '属性点不够一次（要' . $cost . '点）。';
}

function use_reset_potion(int $uid): string
{
    $mats = mats_of((int) $uid);
    if (empty($mats['reset_potion'])) {
        return '你没有洗点药。';
    }
    $u = user_by_id((int) $uid);
    if (!$u) {
        return '角色不存在。';
    }
    $back = (int) ($u['str'] ?? 0) + (int) ($u['agi'] ?? 0) + (int) ($u['vit'] ?? 0) + (int) ($u['int'] ?? 0);
    if ($back <= 0) {
        return '没加过点，不用洗。';
    }
    add_mat((int) $uid, 'reset_potion', -1);
    $u['maxhp'] = max(1, (int) $u['maxhp'] - (int) ($u['vit'] ?? 0) * 5);
    $u['hp'] = min((int) $u['hp'], (int) $u['maxhp']);
    $u['maxmp'] = max(0, (int) ($u['maxmp'] ?? 0) - (int) ($u['int'] ?? 0) * 3);
    $u['mp'] = min((int) ($u['mp'] ?? 0), (int) ($u['maxmp'] ?? 0));
    $u['s_pts'] = (int) ($u['s_pts'] ?? 0) + $back;
    $u['str'] = 0;
    $u['agi'] = 0;
    $u['vit'] = 0;
    $u['int'] = 0;
    user_save($u);
    return '重修成功，返还' . $back . '潜力点。';
}

function grant_ch3_reset_potion(int $uid): void
{
    add_mat((int) $uid, 'reset_potion', 1);
}

function use_exp_card(int $uid): string
{
    $u = user_by_id((int) $uid);
    if (!$u) {
        return '角色不存在。';
    }
    if ((int) $u['lv'] > 100) {
        return '升级卡100型只给1~100级用，你超了。';
    }
    $mats = mats_of((int) $uid);
    if (empty($mats['exp_card100'])) {
        return '你没有升级卡100型。';
    }
    add_mat((int) $uid, 'exp_card100', -1);
    $left = max(0, (int) ($mats['exp_card_until'] ?? 0) - time());
    mat_set((int) $uid, 'exp_card_until', time() + 3600 + $left);
    return '升级卡生效！1小时内双倍经验（可叠加延长）。';
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

function battle_round(array &$u, array &$b, string $mode): array
{
    $pd = dmg_to_monster($u);
    $isSkill = false;
    $skillName = '';
    $logHeal = '';
    $logNoMp = '';
    $profMsg = '';
    $skEl = '';
    if ($mode !== 'hit') {
        $job = job_id_of($u);
        $sk = active_skill((int) ($u['id'] ?? 0), $job);
        if ($sk === null) {
            $isSkill = true;
            if ($job === 'warrior') {
                $pd = (int) ($pd * 1.5);
            } elseif ($job === 'mage') {
                $pd = player_atk($u) + random_int(4, 7);
            } elseif ($job === 'hunter') {
                $pd = (int) ($pd * 0.8) + (int) ($pd * 0.8);
            } elseif ($job === 'priest') {
                $u['hp'] = min((int) $u['maxhp'], (int) $u['hp'] + 25);
                $pd = (int) ($pd * 0.7) + 2;
            }
            $skillName = job_of($u)['skill'];
        } elseif ((int) ($u['mp'] ?? 0) >= (int) $sk['mp']) {
            $isSkill = true;
            $u['mp'] = (int) $u['mp'] - (int) $sk['mp'];
            $skillName = (string) $sk['name'];
            $sid = (string) $sk['skill'];
            $skEl = (string) ($sk['el'] ?? '');
            $pow = skill_power($sk);
            $sTier = (int) $sk['tier'];
            $sLv = (int) $sk['level'];
            if ($sk['kind'] === 'phys') {
                $pd = (int) ($pd * (float) $sk['mult'] * $pow);
                if ($sid === 'w3' && $sTier >= 10 && mt_rand(1, 100) <= 5) {
                    $b['stun'] = 1;
                    $logHeal .= '(眩晕)';
                }
                if ($sid === 'w1' && $sTier >= 10) {
                    $pd = (int) ($pd * 1.25);
                }
                if ($sid === 'w1' && $sLv >= 20) {
                    $pd = (int) ($pd * 1.15);
                }
                if ($sid === 'w2' && $sLv >= 20) {
                    $pd = (int) ($pd * 1.15);
                }
                if ($sid === 'w2' && $sTier >= 10 && mt_rand(1, 100) <= 20) {
                    $pd = (int) ($pd * 1.5);
                    $logHeal .= '(暴击)';
                }
            } elseif ($sk['kind'] === 'magic') {
                $pd = (int) ((player_atk($u) + random_int(4, 7) + ($sid === 'm2' ? 10 : ($sid === 'm3' ? 8 : 5))) * $pow);
                if ($sTier >= 10) {
                    $pd = (int) ($pd * 1.3);
                }
                if ($sLv >= 20) {
                    $pd = (int) ($pd * 1.15);
                }
                if ($sid === 'm3' && $sTier >= 10 && mt_rand(1, 100) <= 5) {
                    $b['frost'] = 3;
                    $logHeal .= '(冻结)';
                }
            } elseif ($sk['kind'] === 'multi2') {
                $pd = (int) ($pd * (float) $sk['mult'] * $pow);
                if ($sTier >= 10) {
                    $pd = (int) ($pd * 1.5);
                }
                if ($sLv >= 20) {
                    $pd = (int) ($pd * 1.15);
                }
                if ($sid === 'h3' && $sTier >= 10) {
                    $b['poison'] = 3;
                    $logHeal .= '(剧毒)';
                }
            } elseif ($sk['kind'] === 'heal') {
                $heal = (int) ($sk['heal'] * $pow);
                if ($sTier >= 10) {
                    $heal += in_array($sid, ['p2', 'p3'], true) ? 25 : 15;
                }
                if ($sLv >= 20) {
                    $heal = (int) ($heal * 1.2);
                }
                $u['hp'] = min((int) $u['maxhp'], (int) $u['hp'] + $heal);
                $logHeal .= '(回' . $heal . ')';
                if ($sid === 'p3' && $sTier >= 10) {
                    $u['mp'] = min((int) ($u['maxmp'] ?? 0), (int) ($u['mp'] ?? 0) + 10);
                    $logHeal .= '(回蓝10)';
                }
                $pd = (int) ($pd * (float) $sk['mult'] * $pow);
                if ($sLv >= 20) {
                    $pd = (int) ($pd * 1.15);
                }
            }
            $profMsg = add_skill_prof((int) ($u['id'] ?? 0), $sid);
        } else {
            $isSkill = true;
            $skillName = '拳击';
            $pd = max(1, (int) ($pd * 0.3));
        }
        if ($isSkill && $skillName !== '拳击' && ($b['id'] ?? '') === 'banshee') {
            $pd += 4;
        }
    }
    $u['mp'] = min((int) ($u['maxmp'] ?? 0), (int) ($u['mp'] ?? 0) + 2);
    $tankMp = tank_auto($u, 'mp');
    if (($b['id'] ?? '') === 'banshee' && !$isSkill) {
        $pd = max(1, (int) ($pd / 2));
    }
    $eDodge = max(0, monster_dodge((string) ($b['id'] ?? '')) - gear_stats((int) ($u['id'] ?? 0))['hit']);
    if ($pd > 0 && $eDodge > 0 && mt_rand(1, 10000) / 100 <= $eDodge) {
        $pd = 0;
    }
    $b['hp'] = (int) $b['hp'] - $pd;
    $verb = $skillName === '拳击' ? '你挥出一拳，造成' : ($isSkill ? '你放出' . $skillName . '，造成' : '你劈出');
    $log = $pd === 0 ? '你的攻击被' . $b['name'] . '闪避了。' : $verb . $pd . '点。' . $logHeal . $logNoMp . $profMsg . $tankMp;
    $gs = gear_stats((int) ($u['id'] ?? 0));
    if ($pd > 0 && $gs['lifesteal'] > 0) {
        $hl = max(1, (int) ($pd * $gs['lifesteal'] / 100));
        $u['hp'] = min((int) $u['maxhp'], (int) $u['hp'] + $hl);
        $log .= '(吸血+' . $hl . ')';
    }
    $petMsg = pet_attack($u, $b);
    if ($petMsg !== '') {
        $log .= $petMsg;
    }
    if (!empty($b['poison']) && (int) $b['hp'] > 0) {
        $pt = max(1, (int) ((int) ($b['maxhp'] ?? 1) * 0.05));
        $b['hp'] = (int) $b['hp'] - $pt;
        $b['poison'] = (int) $b['poison'] - 1;
        $log .= '剧毒发作，' . $b['name'] . '掉' . $pt . '点生命。';
    }
    if ($skEl !== '' && (int) $b['hp'] > 0) {
        $meld = monster_element((string) ($b['id'] ?? ''));
        $chart = element_chart($skEl, $meld);
        $mres = monster_resist((string) ($b['id'] ?? ''), $skEl);
        $eldmg = max(0, (int) ((player_atk($u) * 0.3 + $gs['element']) * $chart * (1 - $mres / 100)));
        $eldmg += enchant_el_dmg((int) ($u['id'] ?? 0), $skEl);
        if ($eldmg > 0) {
            $b['hp'] = (int) $b['hp'] - $eldmg;
            $log .= element_name($skEl) . '属性伤害' . $eldmg . '点' . ($chart > 1 ? '(克制！)' : ($chart < 1 ? '(被抵抗)' : '')) . '。';
        }
    }
    if ((int) $b['hp'] <= 0) {
        $killed = (int) ($b['num'] ?? 1) - (int) ($b['left'] ?? 1) + 1;
        if ((int) ($b['left'] ?? 1) > 1) {
            $b['wexp'] = (int) ($b['wexp'] ?? 0) + exp_gain_for($u, (string) ($b['id'] ?? ''));
            $b['wgold'] = (int) ($b['wgold'] ?? 0) + (int) $b['gold'] + random_int(0, 2);
            $b['left'] = (int) $b['left'] - 1;
            $b['hp'] = (int) $b['maxhp'];
            $dp = roll_drop((int) $u['id'], (string) ($b['id'] ?? ''), (int) ($b['elite'] ?? 0));
            if ($dp !== '') {
                $b['drops'][] = $dp;
                $log .= '掉落【' . $dp . '】！';
            }
            $mi = roll_material((int) $u['id'], (string) ($b['id'] ?? ''));
            if ($mi === '' && (int) ($b['elite'] ?? 0) === 1) {
                $mm = monster_material((string) ($b['id'] ?? ''));
                if ($mm !== []) {
                    add_mat((int) $u['id'], (string) $mm[0], 1);
                    $mi = (string) $mm[1];
                    $log .= '精英必掉【' . $mi . '】！';
                }
            }
            $ei = roll_enhance_material((int) $u['id'], (string) ($b['id'] ?? ''), (string) ($u['loc'] ?? ''));
            $bi = roll_skillbook((int) $u['id'], (string) ($b['id'] ?? ''));
            if ($mi !== '') {
                $b['drops'][] = $mi;
            }
            if ($ei !== '') {
                $b['drops'][] = $ei;
            }
            if ($bi !== '') {
                $b['drops'][] = $bi;
                $log .= '掉落【' . $bi . '】！';
            }
            $tk = roll_dsw_ticket((int) $u['id'], (string) ($b['id'] ?? ''));
            if ($tk !== '') {
                $b['drops'][] = $tk;
                $log .= '掉落【' . $tk . '】！';
            }
            $peg = roll_pet_egg((int) $u['id'], (string) ($b['id'] ?? ''));
            if ($peg !== '') {
                $b['drops'][] = $peg;
                $log .= '掉落【' . $peg . '】！';
            }
            $dq = roll_dungeon_quest((int) $u['id'], (string) ($b['id'] ?? ''));
            if ($dq !== '') {
                $b['drops'][] = $dq;
                $log .= '掉落【' . $dq . '】！';
            }
            $ax = roll_abx_ticket((int) $u['id'], (string) ($b['id'] ?? ''));
            if ($ax !== '') {
                $b['drops'][] = $ax;
                $log .= '掉落【' . $ax . '】！';
            }
            $aq = roll_abx_quest((int) $u['id'], (string) ($b['id'] ?? ''));
            if ($aq !== '') {
                $b['drops'][] = $aq;
                $log .= '掉落【' . $aq . '】！';
            }
            $mx = roll_mx_ticket((int) $u['id'], (string) ($b['id'] ?? ''));
            if ($mx !== '') {
                $b['drops'][] = $mx;
                $log .= '掉落【' . $mx . '】！';
            }
            $mq = roll_mx_quest((int) $u['id'], (string) ($b['id'] ?? ''));
            if ($mq !== '') {
                $b['drops'][] = $mq;
                $log .= '掉落【' . $mq . '】！';
            }
            $sb = roll_abx_skillbook((int) $u['id'], (string) ($b['id'] ?? ''));
            if ($sb !== '') {
                $b['drops'][] = $sb;
                $log .= '掉落【' . $sb . '】！';
            }
            $log .= '第' . $killed . '只倒了！下一只扑上来(剩' . (int) $b['left'] . ')。';
        } else {
        $num = (int) ($b['num'] ?? 1);
        $dp = roll_drop((int) $u['id'], (string) ($b['id'] ?? ''), (int) ($b['elite'] ?? 0));
        $mi = roll_material((int) $u['id'], (string) ($b['id'] ?? ''));
        if ($mi === '' && (int) ($b['elite'] ?? 0) === 1) {
            $mm = monster_material((string) ($b['id'] ?? ''));
            if ($mm !== []) {
                add_mat((int) $u['id'], (string) $mm[0], 1);
                $mi = (string) $mm[1];
            }
        }
        if ($mi !== '') {
            $b['drops'][] = $mi;
        }
        $ei = roll_enhance_material((int) $u['id'], (string) ($b['id'] ?? ''), (string) ($u['loc'] ?? ''));
        $bi = roll_skillbook((int) $u['id'], (string) ($b['id'] ?? ''));
        if ($bi !== '') {
            $b['drops'][] = $bi;
        }
        $tk = roll_dsw_ticket((int) $u['id'], (string) ($b['id'] ?? ''));
        if ($tk !== '') {
            $b['drops'][] = $tk;
        }
        $peg = roll_pet_egg((int) $u['id'], (string) ($b['id'] ?? ''));
        if ($peg !== '') {
            $b['drops'][] = $peg;
        }
        $dq = roll_dungeon_quest((int) $u['id'], (string) ($b['id'] ?? ''));
        if ($dq !== '') {
            $b['drops'][] = $dq;
        }
        $ax = roll_abx_ticket((int) $u['id'], (string) ($b['id'] ?? ''));
        if ($ax !== '') {
            $b['drops'][] = $ax;
        }
        $aq = roll_abx_quest((int) $u['id'], (string) ($b['id'] ?? ''));
        if ($aq !== '') {
            $b['drops'][] = $aq;
        }
        $mx = roll_mx_ticket((int) $u['id'], (string) ($b['id'] ?? ''));
        if ($mx !== '') {
            $b['drops'][] = $mx;
        }
        $mq = roll_mx_quest((int) $u['id'], (string) ($b['id'] ?? ''));
        if ($mq !== '') {
            $b['drops'][] = $mq;
        }
        if ((string) ($b['id'] ?? '') === 'string_puller') {
            add_mat((int) $u['id'], 'capital_badge', 1);
            add_mat((int) $u['id'], 'puppet_eye', 1);
            $b['drops'][] = '王都徽章';
            $b['drops'][] = '傀儡之眼';
            $peg2 = roll_pet_egg((int) $u['id'], 'string_puller');
            if ($peg2 !== '') {
                $b['drops'][] = $peg2;
            }
        }
        $sb = roll_abx_skillbook((int) $u['id'], (string) ($b['id'] ?? ''));
        if ($sb !== '') {
            $b['drops'][] = $sb;
        }
        if ($mi !== '') {
            $b['drops'][] = $mi;
        }
        if ($ei !== '') {
            $b['drops'][] = $ei;
        }
        if ($dp !== '') {
            $b['drops'][] = $dp;
        }
        $bid = (string) ($b['id'] ?? '');
        if ($bid === 'abaddon') {
            if (mt_rand(1, 100) <= 50) {
                $pieces = [['body', '深渊蚀甲'], ['legs', '深渊腿甲'], ['gloves', '深渊护手']];
                $pc = $pieces[array_rand($pieces)];
                $sn = make_equip((int) $u['id'], $pc[0], $pc[1], 3, 120);
                $b['drops'][] = $sn;
            }
            $u['loc'] = 'silver_sq';
            user_save($u);
            $msg = ($msg ?? '') . '。影蚀君王倒下，你被传回白银广场';
        }
        if ($bid === 'swamp_king') {
            if (mt_rand(1, 100) <= 50) {
                $pieces = [['head', '沼泽兜帽'], ['shoes', '沼泽轻靴'], ['necklace', '沼泽之心']];
                $pc = $pieces[array_rand($pieces)];
                $sn = make_equip((int) $u['id'], $pc[0], $pc[1], 3, 25);
                $b['drops'][] = $sn;
            }
            $u['loc'] = 'town_sq';
            user_save($u);
            $msg = ($msg ?? '') . '。沼泽之王倒下，你被传回白石镇广场';
        }
        $g = (int) ($b['wgold'] ?? 0) + (int) $b['gold'] + random_int(0, 2);
        $u['gold'] = (int) $u['gold'] + $g;
        $rawExp = (int) (((int) ($b['wexp'] ?? 0) + exp_gain_for($u, (string) ($b['id'] ?? ''))) * guild_exp_mult((int) $u['id']) * party_bonus_mult((int) $u['id']));
        war_add_score((int) $u['id'], (string) ($b['id'] ?? ''));
        $msg = ($msg ?? '') . gain_exp($u, $rawExp);
        $petLvMsg = pet_gain_exp((int) $u['id'], (int) ($b['wexp'] ?? 0) + exp_gain_for($u, (string) ($b['id'] ?? '')));
        $qmap = ['goblin' => 1, 'banshee' => 2, 'ogre' => 3];
        $bid = (string) ($b['id'] ?? '');
        if (isset($qmap[$bid]) && (int) ($u['quest'] ?? 0) < $qmap[$bid]) {
            $u['quest'] = $qmap[$bid];
            if ($bid === 'goblin') {
                $msg .= '。玛莎给你燕麦肉汤，伤全好了';
                $u['hp'] = (int) $u['maxhp'];
                $u['potion'] = (int) $u['potion'] + 1;
            }
            if ($bid === 'banshee') {
                $msg .= '。女妖散去：“别往下听……”莉娜加入';
            }
            if ($bid === 'ogre') {
                $msg .= '。咕噜让路，送你大骨棒+黑币！桥通了，去白石镇吧';
                $bb = make_equip((int) $u['id'], 'weapon', '大骨棒', 1);
                $msg .= '。【' . $bb . '】已放进背包，去穿上吧';
                add_mat((int) $u['id'], 'blackcoin', 1);
                $msg .= '。获得任务物品【不断下坠的黑币】';
            }
        }
        if ($bid === 'echo_rayne' && (int) ($u['quest'] ?? 0) === 14) {
            add_mat((int) $u['id'], 'aiden_badge', 1);
            add_mat((int) $u['id'], 'investigation_record', 1);
            add_mat((int) $u['id'], 'augustus_letter', 1);
            $msg .= '。你找到艾登·灰叶的铭牌、调查队记录，以及镇长写给深处的信';
        }
        user_save($u);
        unset($_SESSION['battle']);
        $txt = ($num > 1 ? '共杀' . $num . '只' : '') . $b['name'] . '散了。' . $msg . '，钱+' . fmt_money($g) . $petLvMsg;
        if (!empty($b['drops'])) {
            $txt .= '。掉落' . h('【' . implode('】【', $b['drops']) . '】') . '(背包查看)';
        }
        if (bag_full((int) $u['id'])) {
            $txt .= '。（背包满了' . bag_count((int) $u['id']) . '/' . bag_size((int) $u['id']) . '，多余战利品烂地上了）';
        }
        $bid = (string) ($b['id'] ?? '');
        for ($ki = 0; $ki < $num; $ki++) {
            $kc = add_kill((int) $u['id'], $bid);
        }
        $trashMids = array_values(array_filter(loc((string) ($u['loc'] ?? ''))['monsters'] ?? [], fn($mid) => $mid !== boss_of_map((string) ($u['loc'] ?? ''))));
        if ($trashMids !== [] && mt_rand(1, 100) <= 5) {
            $em = $trashMids[array_rand($trashMids)];
            spawn_add_elite((int) $u['id'], (string) ($u['loc'] ?? ''), $em);
            $msg .= '。一只' . monsters()[$em]['name'] . '（精英）出现了！';
        }
        $cur = (int) ($u['quest'] ?? 0);
        if ($cur >= 10 && $cur <= 15) {
            $qs = ch1_quests()[$cur];
            if (check_ch1_done((int) $u['id'], $qs)) {
                $u['quest'] = $cur + 1;
                if ($cur === 10) {
                    $u['quest'] = 11;
                    $txt .= '。艾琳给你铜牌！';
                } elseif ($cur === 12) {
                    add_mat((int) $u['id'], 'investigation_record', 1);
                    $txt .= '。你在营火灰烬里找到调查队记录。';
                } elseif ($cur === 14) {
                    $txt .= '。雷恩的声音停止了，但第三层的黑色液体仍在呼吸。';
                } elseif ($cur === 15) {
                    $up = job_skillset(job_id_of($u))[1];
                    add_mat((int) $u['id'], 'book_' . $up, 1);
                    $txt .= '。第一章完成！获得进阶技能书【' . (skill_books()['book_' . $up]['name'] ?? '') . '】，去背包学习！';
                } else {
                    $txt .= '。本环完成，进下一环！';
                }
                user_save($u);
            } else {
                $txt .= '。【' . $qs['name'] . '】' . quest_progress_text((int) $u['id'], $qs);
            }
        }
        if ($cur >= 20 && $cur <= 27) {
            $qs2 = ch2_quests()[$cur] + ['id' => $cur];
            if (check_ch2_done((int) $u['id'], $qs2)) {
                $u['quest'] = $cur + 1;
                if ($cur === 27) {
                    $up3 = job_skillset(job_id_of($u))[2] ?? '';
                    if ($up3 !== '') {
                        add_mat((int) $u['id'], 'book_' . $up3, 1);
                    }
                    $txt .= '。第二章·影蚀之潮完成！莉莉握住了你的手。王都，万眼之夜。获得终极技能书【' . (skill_books()['book_' . $up3]['name'] ?? '') . '】，去背包学习！';
                } elseif ($cur === 20) {
                    add_mat((int) $u['id'], 'bag_ext5', 1);
                    $txt .= '。本环完成，进下一环！格温塞给你【5格背包扩充】，去背包使用！';
                } else {
                    $txt .= '。本环完成，进下一环！';
                }
                quest2_baseline((int) $u['id'], $cur + 1);
                user_save($u);
            } else {
                $txt .= '。【' . $qs2['name'] . '】' . quest_progress2_text((int) $u['id'], $qs2);
            }
        }
        if ($cur >= 30 && $cur <= 37) {
            $qs3 = ch3_quests()[$cur] + ['id' => $cur];
            if (check_ch3_done((int) $u['id'], $qs3)) {
                $u['quest'] = $cur + 1;
                $u['gold'] = (int) $u['gold'] + [30 => 2000, 31 => 3000, 32 => 4000, 33 => 5000, 34 => 6000, 35 => 7000, 36 => 8000, 37 => 10000][$cur];
                $gear3 = [31 => ['weapon', '傀儡短剑', 150], 32 => ['body', '傀儡皮甲', 180], 33 => ['weapon', '傀儡长弓', 210], 34 => ['body', '傀儡铠甲', 240], 35 => ['back', '傀儡斗篷', 270]][$cur] ?? null;
                if ($gear3 !== null) {
                    $gn3 = make_equip((int) $u['id'], $gear3[0], $gear3[1], 2, $gear3[2]);
                    if ($gn3 !== '') {
                        $txt .= '。获得【' . $gn3 . '】！';
                    }
                }
                if ($cur === 36) {
                    add_mat((int) $u['id'], 'puppet_eye', 1);
                    $txt .= '。获得【傀儡之眼】！';
                }
                if ($cur === 37) {
                    add_mat((int) $u['id'], 'capital_badge', 1);
                    add_mat((int) $u['id'], 'ch3_proof', 1);
                    $txt .= '。第三章·傀儡之夜完成！影子融入体内（影子伙伴：全属性+5%）。获得【王都徽章】，农场开启，去王都农场种菜！';
                } else {
                    $txt .= '。本环完成，进下一环！';
                }
                quest3_baseline((int) $u['id'], $cur + 1);
                user_save($u);
            } else {
                $txt .= '。【' . $qs3['name'] . '】' . quest_progress3_text((int) $u['id'], $qs3);
            }
        }
        if ($bid === 'ogre' && (int) ($u['quest'] ?? 0) === 3) {
            $u['quest'] = 10;
            $base = job_skillset(job_id_of($u))[0];
            add_mat((int) $u['id'], 'book_' . $base, 1);
            user_save($u);
            $txt .= '。去白石镇广场找公会吧！获得技能书【' . (skill_books()['book_' . $base]['name'] ?? '') . '】，去背包学习！';
        }
        return ['status' => 'victory', 'log' => $log . $txt, 'flash' => $txt];
        }
    }
    $alive = max(1, (int) ($b['left'] ?? 1));
    $echoWeak = false;
    if (($b['id'] ?? '') === 'echo_rayne') {
        $b['echo_calls'] = (int) ($b['echo_calls'] ?? 0) + 1;
        $echoWeak = $b['echo_calls'] % 3 === 0;
        if ($echoWeak) {
            $log .= '雷恩呼唤你的名字：“回答我……”你没有回应，回响短暂虚弱。';
        }
    }
    $md = 0;
    for ($i = 0; $i < $alive; $i++) {
        $md += dmg_to_player($u, $b);
    }
    if ($echoWeak) {
        $md = (int) ($md * 0.6);
    }
    if (!empty($b['fear'])) {
        $md = (int) ($md * 0.8);
        $b['fear'] = (int) $b['fear'] - 1;
    }
    if (!empty($b['eye_weak'])) {
        $md = (int) ($md * 0.9);
    }
    if (!empty($b['stun'])) {
        $md = 0;
        $b['stun'] = 0;
        $log .= $b['name'] . '被眩晕，动弹不得。';
    }
    if (!empty($b['frost'])) {
        $md = (int) ($md * 0.85);
        $b['frost'] = (int) $b['frost'] - 1;
        $log .= $b['name'] . '被冻结，手脚迟缓。';
    }
    $meld = monster_element((string) ($b['id'] ?? ''));
    if ($meld !== '' && $md > 0) {
        $eres = player_resist($u, $meld);
        $emd = max(0, (int) ((int) ($b['atk'] ?? 0) * 0.3 * (1 - $eres / 100)));
        if ($emd > 0) {
            $md += $emd;
            $log .= element_name($meld) . '属性冲击' . $emd . '点。';
        }
    }
    if (($b['id'] ?? '') === 'valentin') {
        $cf = cflags((int) ($u['id'] ?? 0));
        if (!empty($cf['lord_alert'])) {
            $md = (int) ($md * 1.1);
        }
    }
    $u['hp'] = (int) $u['hp'] - $md;
    $tankHp = tank_auto($u, 'hp');
    $log .= $alive > 1 ? $b['name'] . '合击x' . $alive . '共' . $md . '点。' : $b['name'] . '回击' . $md . '点。';
    $log .= $tankHp;
    $petHurt = pet_hurt((int) ($u['id'] ?? 0), $md);
    if ($petHurt !== '') {
        $log .= $petHurt;
    }
    user_save($u);
    if ((int) $u['hp'] <= 0) {
        $pen = death_penalty($u);
        if (in_array((string) ($u['loc'] ?? ''), dsw_maps(), true)) {
            $u['loc'] = 'town_sq';
            $pen .= '副本中倒下，直接传回白石镇广场。';
        } elseif (in_array((string) ($u['loc'] ?? ''), abx_maps(), true)) {
            $u['loc'] = 'silver_sq';
            $pen .= '深渊中倒下，直接传回白银广场。';
        } elseif (in_array((string) ($u['loc'] ?? ''), mx_maps(), true)) {
            $u['loc'] = 'avenue';
            $pen .= '母巢中倒下，直接传回中央大道。';
        }
        user_save($u);
        unset($_SESSION['battle']);
        return ['status' => 'dead', 'log' => $log, 'flash' => '你倒了，被拖回营地。' . $pen];
    }
    $b['log'] = $log;
    if (!isset($b['history']) || !is_array($b['history'])) {
        $b['history'] = [];
    }
    $b['history'][] = $log;
    $b['history'] = array_slice($b['history'], -20);
    $_SESSION['battle'] = $b;
    return ['status' => 'fight', 'log' => $log];
}

function background_battle(array &$u): void
{
    if (!isset($_SESSION['battle']) || !is_array($_SESSION['battle'])) {
        return;
    }
    $script = basename((string) ($_SERVER['SCRIPT_NAME'] ?? ''));
    $a = (string) ($_GET['a'] ?? '');
    if ($script === 'fight.php' && ($a === 'tick' || $a === 'start' || $a === 'hit' || $a === 'skill')) {
        return;
    }
    $b = $_SESSION['battle'];
    $now = time();
    $rounds = min(300, $now - (int) ($b['last'] ?? $now));
    if ($rounds <= 0) {
        return;
    }
    for ($i = 0; $i < $rounds; $i++) {
        $b['last'] = $now;
        $r = battle_round($u, $b, 'tick');
        if ($r['status'] !== 'fight') {
            user_save($u);
            flash_set(($r['status'] === 'victory' ? '切出去这会儿战斗打完了：' : '') . $r['flash']);
            return;
        }
    }
    $b['log'] = $r['log'] ?? '';
    $_SESSION['battle'] = $b;
    user_save($u);
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
        '白石镇' => ['town_sq', 'street_e', 'smith', 'tavern', 'street_w', 'church', 'market', 'supply', 'mansion', 'sewer', 'guild', 'wall', 'gate', 'mine_gate', 'mine1', 'mine2', 'mine3', 'echo_room'],
        '野外' => ['wild1', 'wild2', 'wild3', 'wild4', 'wild5', 'wild6', 'forest1', 'forest2', 'forest3', 'forest4', 'swamp1', 'swamp2', 'swamp3', 'swamp4', 'hill1', 'hill2', 'hill3', 'hill4'],
        '废弃村·墓穴' => ['ruin1', 'ruin2', 'ruin3', 'tomb1', 'tomb2', 'tomb3', 'tomb4', 'boss'],
        '矿坑·灰烬中的低语' => ['mine_gate', 'mine1', 'mine2', 'mine3', 'echo_room'],
        '深渊旧道' => ['tunnel', 'shoal', 'altar', 'prison', 'shop', 'camp'],
        '白银城·影蚀之潮' => ['silver_gate', 'silver_sq', 'sguild', 'dsewer1', 'dsewer2', 'bmine1', 'bmine2', 'sforest', 'manor', 'baltar', 'rift'],
        '王都·傀儡之夜' => ['capital_gate', 'avenue', 'noble', 'library', 'ctomb', 'slum', 'cathedral', 'observatory', 'theater'],
        '银丝母巢' => ['mx_gate', 'mx_path', 'mx_hall', 'mx_depth', 'mx_nest', 'mx_heart'],
        '公会战场' => ['warfield'],
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
        'mine_gate' => ['北' => 'town_sq', '下' => 'mine1'],
        'mine1' => ['上' => 'mine_gate', '下' => 'mine2'],
        'mine2' => ['上' => 'mine1', '下' => 'mine3'],
        'mine3' => ['上' => 'mine2', '下' => 'echo_room'],
        'echo_room' => ['上' => 'mine3'],
        'dsw_gate' => ['东' => 'dsw_path'],
        'dsw_path' => ['西' => 'dsw_gate', '东' => 'dsw_mire'],
        'dsw_mire' => ['西' => 'dsw_path', '东' => 'dsw_moss'],
        'dsw_moss' => ['西' => 'dsw_mire', '东' => 'dsw_altar'],
        'dsw_altar' => ['西' => 'dsw_moss', '东' => 'dsw_hall'],
        'dsw_hall' => ['西' => 'dsw_altar', '东' => 'dsw_door'],
        'dsw_door' => ['西' => 'dsw_hall', '东' => 'dsw_throne'],
        'dsw_throne' => ['西' => 'dsw_door'],
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

function teleport_min_lv(string $loc): int
{
    if (in_array($loc, abx_maps(), true)) {
        return 50;
    }
    if (in_array($loc, dsw_maps(), true)) {
        return 10;
    }
    if (in_array($loc, ch2_maps(), true)) {
        return 30;
    }
    if (in_array($loc, ch3_maps(), true)) {
        return 110;
    }
    if (in_array($loc, mx_maps(), true)) {
        return 150;
    }
    if ($loc === 'warfield') {
        return 30;
    }
    $mx = 0;
    foreach (loc($loc)['monsters'] ?? [] as $mid) {
        $mx = max($mx, monster_lv($mid));
    }
    return max(1, $mx - 5);
}

function teleport_max_lv(string $loc): int
{
    if (in_array($loc, dsw_maps(), true)) {
        return 35;
    }
    return 999;
}

function dsw_locations(): array
{
    $m = [];
    $m['dsw_gate'] = ['name' => '黑暗沼泽·入口', 'desc' => '雾气发黑。修女的祝福还落在你肩上，身后已无退路，只能往前。', 'exits' => ['dsw_path' => '黑水小径'], 'monsters' => ['dark_slime']];
    $m['dsw_path'] = ['name' => '黑水小径', 'desc' => '水面浮着油彩。史莱姆从水底鼓起来。', 'exits' => ['dsw_gate' => '入口', 'dsw_mire' => '腐泥洼地'], 'monsters' => ['dark_slime', 'swamp_slime']];
    $m['dsw_mire'] = ['name' => '腐泥洼地', 'desc' => '一脚下去，泥里有东西在咬靴子。', 'exits' => ['dsw_path' => '黑水小径', 'dsw_moss' => '荧光苔原'], 'monsters' => ['swamp_slime', 'dark_slime']];
    $m['dsw_moss'] = ['name' => '荧光苔原', 'desc' => '苔藓发着绿光，照出水底无数眼睛。', 'exits' => ['dsw_mire' => '腐泥洼地', 'dsw_altar' => '沉没祭坛'], 'monsters' => ['dark_slime']];
    $m['dsw_altar'] = ['name' => '沉没祭坛', 'desc' => '半截祭坛露出水面，供品全是黏液。', 'exits' => ['dsw_moss' => '荧光苔原', 'dsw_hall' => '王座前厅'], 'monsters' => ['swamp_slime']];
    $m['dsw_hall'] = ['name' => '王座前厅', 'desc' => '石柱上刻满朝拜的史莱姆。再往前就是王座大门。', 'exits' => ['dsw_altar' => '沉没祭坛', 'dsw_door' => '王座大门'], 'monsters' => ['dark_slime', 'swamp_slime']];
    $m['dsw_door'] = ['name' => '王座大门', 'desc' => '青铜巨门。门缝后传来霍克的呼吸。集齐四样信物才能推开。', 'exits' => ['dsw_hall' => '王座前厅', 'dsw_throne' => '王座之间'], 'monsters' => []];
    $m['dsw_throne'] = ['name' => '王座之间', 'desc' => '沼泽之王霍克坐在黏液王座上。杀了它，或者死在这里。', 'exits' => ['dsw_door' => '王座大门'], 'monsters' => ['swamp_king']];
    return $m;
}

function dsw_maps(): array
{
    return ['dsw_gate', 'dsw_path', 'dsw_mire', 'dsw_moss', 'dsw_altar', 'dsw_hall', 'dsw_door', 'dsw_throne'];
}

function dsw_stage(int $uid): int
{
    return max(1, min(5, (int) (mats_of($uid)['dsw_stage'] ?? 1)));
}

function roll_dsw_ticket(int $uid, string $mid): string
{
    if ($mid !== 'echo_rayne' || mt_rand(1, 100) > 1) {
        return '';
    }
    add_mat($uid, 'dsw_ticket', 1);
    return '黑暗沼泽副本入场券';
}

function roll_dungeon_quest(int $uid, string $mid): string
{
    $stage = dsw_stage($uid);
    $give = '';
    if ($mid === 'dark_slime' && $stage === 1) {
        $give = 'dsw_item1';
    } elseif ($mid === 'swamp_slime' && $stage === 2) {
        $give = 'dsw_item2';
    } elseif ($mid === 'dark_slime' && $stage === 3) {
        $give = 'dsw_item3';
    } elseif ($mid === 'swamp_slime' && $stage === 4) {
        $give = 'dsw_item4';
    }
    if ($give === '') {
        return '';
    }
    add_mat($uid, $give, 1);
    $need = ['dsw_item1' => 10, 'dsw_item2' => 10, 'dsw_item3' => 8, 'dsw_item4' => 5][$give];
    $out = quest_mats()[$give] . '+1';
    if ((mats_of($uid)[$give] ?? 0) >= $need) {
        mat_set($uid, 'dsw_stage', $stage + 1);
        $out .= '（本轮完成，下一阶段开启！）';
    }
    return $out;
}

function abx_locations(): array
{
    $m = [];
    $m['abx_gate'] = ['name' => '影蚀深渊·入口', 'desc' => '黑里透紫的雾。薇拉的祝福还落在肩上，身后已无退路。', 'exits' => ['abx_path' => '低语长廊'], 'monsters' => ['shadow_walker']];
    $m['abx_path'] = ['name' => '低语长廊', 'desc' => '墙缝里全是声音，仔细听又什么都没有。行者与守卫在雾里巡逻。', 'exits' => ['abx_gate' => '入口', 'abx_hall' => '蚀影大厅'], 'monsters' => ['shadow_walker', 'corrupt_warder']];
    $m['abx_hall'] = ['name' => '蚀影大厅', 'desc' => '穹顶倒吊着无数影子。低语者在影子中间念你的名字。', 'exits' => ['abx_path' => '低语长廊', 'abx_depth' => '深渊之底'], 'monsters' => ['corrupt_warder', 'whisperer']];
    $m['abx_depth'] = ['name' => '深渊之底', 'desc' => '巨像在黑暗里呼吸。每一次吐息，雾就浓一分。', 'exits' => ['abx_hall' => '蚀影大厅', 'abx_door' => '君王大门'], 'monsters' => ['whisperer', 'colossus']];
    $m['abx_door'] = ['name' => '君王大门', 'desc' => '黑铁巨门。门缝后是阿巴顿的心跳。集齐四样信物才能推开。', 'exits' => ['abx_depth' => '深渊之底', 'abx_throne' => '君王王座'], 'monsters' => []];
    $m['abx_throne'] = ['name' => '君王王座', 'desc' => '影蚀君王阿巴顿端坐蚀影王座。杀了它，或者死在这里。', 'exits' => ['abx_door' => '君王大门'], 'monsters' => ['abaddon']];
    return $m;
}

function abx_maps(): array
{
    return ['abx_gate', 'abx_path', 'abx_hall', 'abx_depth', 'abx_door', 'abx_throne'];
}

function abx_stage(int $uid): int
{
    return max(1, min(5, (int) (mats_of($uid)['abx_stage'] ?? 1)));
}

function roll_abx_ticket(int $uid, string $mid): string
{
    if ($mid !== 'abyss_eye' || mt_rand(1, 100) > 1) {
        return '';
    }
    add_mat($uid, 'abx_ticket', 1);
    return '影蚀深渊入场券';
}

function roll_mx_ticket(int $uid, string $mid): string
{
    if ($mid === 'string_puller' && mt_rand(1, 100) <= 1) {
        add_mat($uid, 'mx_ticket', 1);
        return '银丝母巢入场券';
    }
    if ($mid === 'silk_mother' && mt_rand(1, 100) <= 5) {
        add_mat($uid, 'mx_ticket', 1);
        return '银丝母巢入场券';
    }
    return '';
}

function roll_mx_quest(int $uid, string $mid): string
{
    $stage = mx_stage($uid);
    $give = '';
    if ($mid === 'silk_spider' && $stage === 1) {
        $give = 'mx_item1';
    } elseif ($mid === 'cocoon_guard' && $stage === 2) {
        $give = 'mx_item2';
    } elseif ($mid === 'thread_weaver' && $stage === 3) {
        $give = 'mx_item3';
    } elseif ($mid === 'silk_moth' && $stage === 4) {
        $give = 'mx_item4';
    } elseif ($mid === 'nest_watcher' && $stage === 5) {
        $give = 'mx_item5';
    } elseif ($mid === 'brood_maiden' && $stage === 6) {
        $give = 'mx_item6';
    }
    if ($give === '') {
        return '';
    }
    add_mat($uid, $give, 1);
    $need = ['mx_item1' => 15, 'mx_item2' => 15, 'mx_item3' => 12, 'mx_item4' => 12, 'mx_item5' => 10, 'mx_item6' => 8][$give];
    $out = quest_mats()[$give] . '+1';
    if ((mats_of($uid)[$give] ?? 0) >= $need) {
        mat_set($uid, 'mx_stage', $stage + 1);
        $out .= '（本轮完成，下一阶段开启！）';
    }
    return $out;
}

function roll_abx_quest(int $uid, string $mid): string
{
    $stage = abx_stage($uid);
    $give = '';
    if ($mid === 'shadow_walker' && $stage === 1) {
        $give = 'abx_item1';
    } elseif ($mid === 'corrupt_warder' && $stage === 2) {
        $give = 'abx_item2';
    } elseif ($mid === 'whisperer' && $stage === 3) {
        $give = 'abx_item3';
    } elseif ($mid === 'colossus' && $stage === 4) {
        $give = 'abx_item4';
    }
    if ($give === '') {
        return '';
    }
    add_mat($uid, $give, 1);
    $need = ['abx_item1' => 10, 'abx_item2' => 10, 'abx_item3' => 8, 'abx_item4' => 5][$give];
    $out = quest_mats()[$give] . '+1';
    if ((mats_of($uid)[$give] ?? 0) >= $need) {
        mat_set($uid, 'abx_stage', $stage + 1);
        $out .= '（本轮完成，下一阶段开启！）';
    }
    return $out;
}

function dungeon_tick(array &$u): void
{
    $loc = (string) ($u['loc'] ?? '');
    if (!in_array($loc, dsw_maps(), true) && !in_array($loc, abx_maps(), true) && !in_array($loc, mx_maps(), true)) {
        return;
    }
    $inAbx = in_array($loc, abx_maps(), true);
    $inMx = in_array($loc, mx_maps(), true);
    $mats = mats_of((int) ($u['id'] ?? 0));
    $enter = (int) ($mats[$inMx ? 'mx_enter' : ($inAbx ? 'abx_enter' : 'dsw_enter')] ?? time());
    $limit = $inMx ? 2700 : 1800;
    if (time() - $enter > $limit) {
        unset($_SESSION['battle']);
        $u['loc'] = $inMx ? 'avenue' : ($inAbx ? 'silver_sq' : 'town_sq');
        user_save($u);
        flash_set(($inMx ? '45' : '30') . '分钟到了，你被传回' . ($inMx ? '中央大道' : ($inAbx ? '白银广场' : '白石镇广场')) . '。');
    }
}

function ch2_locations(): array
{
    $m = [];
    $m['silver_gate'] = ['name' => '白银城门', 'desc' => '灰白城墙高耸。守卫面朝内，影子被夕阳拉得很长，像黑色的手指。', 'exits' => ['gate' => '罪渊入口', 'silver_sq' => '白银广场'], 'monsters' => []];
    $m['silver_sq'] = ['name' => '白银广场', 'desc' => '天黑后街上没人。每一道影子都可能自己动。', 'exits' => ['silver_gate' => '白银城门', 'sguild' => '公会大厅', 'dsewer1' => '下水道一层', 'warfield' => '荒芜战场', 'capital_gate' => '王都城门'], 'monsters' => []];
    $m['warfield'] = ['name' => '荒芜战场', 'desc' => '公会战专用。周末晚上，这里只讲拳头。杀不同公会的人+1战功。', 'exits' => ['silver_sq' => '白银广场'], 'monsters' => []];
    $m['sguild'] = ['name' => '公会大厅', 'desc' => '油灯照不亮的墙上挂着白银城地图，红圈几十个。格温在桌后等你。', 'exits' => ['silver_sq' => '白银广场'], 'monsters' => []];
    $m['dsewer1'] = ['name' => '下水道一层', 'desc' => '水没脚踝，铁锈混腐肉的气味。白眼睛的影蚀鼠从四面涌来。', 'exits' => ['silver_sq' => '白银广场', 'dsewer2' => '下水道二层'], 'monsters' => ['shadow_rat']];
    $m['dsewer2'] = ['name' => '下水道二层', 'desc' => '墙上画满眼睛。角落里三具尸体靠墙坐着，影子不见了。墙上有血字：不要回答。', 'exits' => ['dsewer1' => '下水道一层', 'bmine1' => '白银矿道一层'], 'monsters' => ['shadow_rat', 'shadow_soldier']];
    $m['bmine1'] = ['name' => '白银矿道一层', 'desc' => '比下水道更冷。呼出的气是白的。矿壁后有呼吸声。', 'exits' => ['dsewer2' => '下水道二层', 'bmine2' => '矿道二层'], 'monsters' => ['shadow_soldier', 'corrupt_guard']];
    $m['bmine2'] = ['name' => '矿道二层', 'desc' => '黑袍使徒在这里等你。你的影子比来时长了三寸。', 'exits' => ['bmine1' => '白银矿道一层', 'sforest' => '影蚀森林'], 'monsters' => ['corrupt_guard', 'apostle']];
    $m['sforest'] = ['name' => '影蚀森林', 'desc' => '树是黑的，叶是灰的，落下来像纸片。林心有尊三倍大的傀儡。', 'exits' => ['bmine2' => '矿道二层', 'manor' => '废弃庄园'], 'monsters' => ['shadow_hound', 'corrupt_treant', 'gargoyle', 'guardian']];
    $m['manor'] = ['name' => '废弃庄园', 'desc' => '门开着，安静得不正常。大厅主位上，城主瓦伦丁端着红酒等你。', 'exits' => ['sforest' => '影蚀森林', 'baltar' => '深渊祭坛'], 'monsters' => ['puppet', 'valentin']];
    $m['baltar'] = ['name' => '深渊祭坛', 'desc' => '很大，很黑。核心处站着眼睛发黑、影子如翅的暗影莉莉。', 'exits' => ['manor' => '废弃庄园', 'rift' => '裂隙深处'], 'monsters' => ['dark_lily']];
    $m['rift'] = ['name' => '裂隙深处', 'desc' => '没有底。你走在黑水上。深渊之眼睁着，瞳孔里是你的影子。', 'exits' => ['baltar' => '深渊祭坛'], 'monsters' => ['abyss_eye']];
    return $m;
}

function ch2_maps(): array
{
    return ['silver_gate', 'silver_sq', 'sguild', 'dsewer1', 'dsewer2', 'bmine1', 'bmine2', 'sforest', 'manor', 'baltar', 'rift'];
}

function ch3_locations(): array
{
    $m = [];
    $m['capital_gate'] = ['name' => '王都城门', 'desc' => '城墙高耸入云。守卫动作僵硬，像被线提着，关节处有细小的银丝连着天空。', 'exits' => ['silver_sq' => '白银广场', 'avenue' => '中央大道'], 'monsters' => []];
    $m['avenue'] = ['name' => '王都中央大道', 'desc' => '市民走路没有声音，关节咔咔作响。瓦尔顿在街角等你：真正的王都二十年前就没了。', 'exits' => ['capital_gate' => '王都城门', 'noble' => '贵族区', 'library' => '皇家图书馆', 'slum' => '贫民窟'], 'monsters' => ['puppet_guard']];
    $m['noble'] = ['name' => '贵族区', 'desc' => '舞会彻夜不停。贵族们脚不沾地，被银丝吊在天花板上跳舞。', 'exits' => ['avenue' => '中央大道', 'cathedral' => '光明大教堂'], 'monsters' => ['masked_noble']];
    $m['library'] = ['name' => '皇家图书馆', 'desc' => '满架子全是剧本，记载着王都每一天该发生什么。墨傀儡在吃掉关于真王都的记载。', 'exits' => ['avenue' => '中央大道', 'ctomb' => '皇家陵墓'], 'monsters' => ['ink_puppet']];
    $m['ctomb'] = ['name' => '皇家陵墓', 'desc' => '所有棺材都是空的。国王雷金纳德根本没有葬在这里。', 'exits' => ['library' => '皇家图书馆'], 'monsters' => ['silver_undead']];
    $m['slum'] = ['name' => '贫民窟', 'desc' => '黑市里有人在偷偷剪断银丝。剪线人的刀很快，刺客的刀更快。', 'exits' => ['avenue' => '中央大道', 'observatory' => '皇家天文台'], 'monsters' => ['silver_assassin']];
    $m['cathedral'] = ['name' => '光明大教堂', 'desc' => '大主教塞缪尔被银丝吊了二十年，还在布道。牵线牧师说：莉莉是钥匙，也是锁。', 'exits' => ['noble' => '贵族区', 'theater' => '傀儡剧场'], 'monsters' => ['puppet_priest']];
    $m['observatory'] = ['name' => '皇家天文台', 'desc' => '观星者白天看星星，晚上看深渊。牵线者已经不满足于傀儡剧场，它要整个王国都变成舞台。', 'exits' => ['slum' => '贫民窟', 'theater' => '傀儡剧场'], 'monsters' => ['star_puppet']];
    $m['theater'] = ['name' => '王宫地下·傀儡剧场', 'desc' => '巨大的舞台。中央吊着真正的国王雷金纳德。牵线者从天花板降下来：我不是深渊，我是国王。', 'exits' => ['cathedral' => '大教堂', 'observatory' => '皇家天文台'], 'monsters' => ['silver_puppet', 'string_puller']];
    return $m;
}

function ch3_maps(): array
{
    return ['capital_gate', 'avenue', 'noble', 'library', 'ctomb', 'slum', 'cathedral', 'observatory', 'theater'];
}

function mx_locations(): array
{
    $m = [];
    $m['mx_gate'] = ['name' => '银丝母巢·入口', 'desc' => '银丝织成的茧门，一收一缩像在呼吸。断线人说：里面是牵线者死后留下的卵，45分钟，出来或者变成茧。', 'exits' => ['mx_path' => '黏丝小径'], 'monsters' => ['silk_spider']];
    $m['mx_path'] = ['name' => '黏丝小径', 'desc' => '脚下全是黏丝。茧守从茧里睁开眼睛。', 'exits' => ['mx_gate' => '入口', 'mx_hall' => '织丝大厅'], 'monsters' => ['silk_spider', 'cocoon_guard']];
    $m['mx_hall'] = ['name' => '织丝大厅', 'desc' => '穹顶垂下上万根丝。织线者在丝上爬，修补着破掉的茧。', 'exits' => ['mx_path' => '黏丝小径', 'mx_depth' => '巢穴深处'], 'monsters' => ['cocoon_guard', 'thread_weaver']];
    $m['mx_depth'] = ['name' => '巢穴深处', 'desc' => '银丝蛾的磷粉像雪。守望者站在茧堆上，一动不动。', 'exits' => ['mx_hall' => '织丝大厅', 'mx_nest' => '育巢'], 'monsters' => ['thread_weaver', 'silk_moth', 'nest_watcher']];
    $m['mx_nest'] = ['name' => '育巢', 'desc' => '育巢侍女抱着卵唱歌。歌声越好听，丝勒得越紧。', 'exits' => ['mx_depth' => '巢穴深处', 'mx_heart' => '母巢之心'], 'monsters' => ['nest_watcher', 'brood_maiden']];
    $m['mx_heart'] = ['name' => '母巢之心', 'desc' => '缠丝之母盘踞在巨茧上。它是牵线者死后留下的最后的卵，孵出来就是下一个牵线者。', 'exits' => ['mx_nest' => '育巢'], 'monsters' => ['silk_mother']];
    return $m;
}

function mx_maps(): array
{
    return ['mx_gate', 'mx_path', 'mx_hall', 'mx_depth', 'mx_nest', 'mx_heart'];
}

function mx_stage(int $uid): int
{
    return max(1, min(7, (int) (mats_of($uid)['mx_stage'] ?? 1)));
}

function farm_locations(): array
{
    $m = [];
    $m['farm'] = ['name' => '王都农场', 'desc' => '牵线者死后，银丝化成了肥料。守卫队长（如果你救了他）在这里帮你看菜。种菜收菜赚农场币。', 'exits' => ['avenue' => '中央大道'], 'monsters' => []];
    return $m;
}

function farm_crops(): array
{
    return [
        'green' => ['name' => '青菜', 'seed' => 'seed_green', 'cost' => 500, 'grow' => 600, 'coin' => 5],
        'tomato' => ['name' => '番茄', 'seed' => 'seed_tomato', 'cost' => 3000, 'grow' => 1200, 'coin' => 8],
        'radish' => ['name' => '萝卜', 'seed' => 'seed_radish', 'cost' => 2000, 'grow' => 1800, 'coin' => 12],
        'melon' => ['name' => '南瓜', 'seed' => 'seed_melon', 'cost' => 5000, 'grow' => 3600, 'coin' => 25],
        'strawberry' => ['name' => '草莓', 'seed' => 'seed_strawberry', 'cost' => 15000, 'grow' => 7200, 'coin' => 40],
    ];
}

function farm_slots(int $uid): int
{
    return min(6, max(4, 4 + (int) (cflags((int) $uid)['farm_slots'] ?? 0)));
}

function farm_buy_slot(int $uid): string
{
    $d = cflags((int) $uid);
    $bought = (int) ($d['farm_slots'] ?? 0);
    if ($bought >= 2) {
        return '地已经扩到6块，到顶了。';
    }
    $cost = [100, 300][$bought];
    $mats = mats_of((int) $uid);
    if ((int) ($mats['farm_coin'] ?? 0) < $cost) {
        return '农场币不够，下一块地要' . $cost . '币。';
    }
    add_mat((int) $uid, 'farm_coin', -$cost);
    cflag_set((int) $uid, 'farm_slots', $bought + 1);
    return '花' . $cost . '农场币开垦出第' . (5 + $bought) . '块地！';
}

function farm_fertilize(int $uid, int $plot): string
{
    $d = farm_plots((int) $uid);
    if (empty($d[$plot]) || !empty($d[$plot]['fert'])) {
        return '这块地不用施肥。';
    }
    $c = farm_crops()[$d[$plot]['crop']] ?? null;
    if ($c === null) {
        return '这茬坏了。';
    }
    $u = user_by_id((int) $uid);
    if ((int) ($u['gold'] ?? 0) < 1000) {
        return '施肥要10银，钱不够。';
    }
    $u['gold'] = (int) ($u['gold'] ?? 0) - 1000;
    user_save($u);
    $d[$plot]['fert'] = 1;
    $d[$plot]['at'] = (int) $d[$plot]['at'] - (int) ($c['grow'] * 0.3);
    cflag_set((int) $uid, 'farm', $d);
    return '施了肥，' . $c['name'] . '长得更快了（-30%时间）！';
}

function farm_steal(int $uid, int $victim, int $plot): string
{
    $uid = (int) $uid;
    $victim = (int) $victim;
    if ($victim === $uid) {
        return '偷自己？你没事吧。';
    }
    $vd = farm_plots($victim);
    if (empty($vd[$plot]) || !empty($vd[$plot]['stolen'])) {
        return '下手晚了，这块地没得偷。';
    }
    $c = farm_crops()[$vd[$plot]['crop']] ?? null;
    if ($c === null) {
        return '这茬坏了。';
    }
    if (time() - (int) $vd[$plot]['at'] < $c['grow']) {
        return '还没熟呢，贼也讲规矩。';
    }
    $take = max(1, (int) ($c['coin'] * 0.4));
    $vd[$plot]['stolen'] = $take;
    cflag_set($victim, 'farm', $vd);
    add_mat($uid, 'farm_coin', $take);
    $vu = user_by_id($victim);
    $tu = user_by_id($uid);
    if ($vu) {
        send_mail($victim, '农场', 'farm', '菜被偷了！', ($tu['username'] ?? '有人') . '偷了你' . $plot . '号地的' . $c['name'] . '（-' . $take . '币），收菜时只剩' . ($c['coin'] - $take) . '币了。', []);
    }
    return '得手！偷到' . $take . '农场币，主人收菜只剩' . ($c['coin'] - $take) . '币了。';
}

function farm_plots(int $uid): array
{
    $d = cflags((int) $uid)['farm'] ?? null;
    if (!is_array($d)) {
        $d = [];
    }
    return $d;
}

function farm_plant(int $uid, int $plot, string $crop): string
{
    $crops = farm_crops();
    if (!isset($crops[$crop]) || $plot < 1 || $plot > farm_slots((int) $uid)) {
        return '没这种种法。';
    }
    $d = farm_plots((int) $uid);
    if (!empty($d[$plot])) {
        return '这块地已经种了。';
    }
    $c = $crops[$crop];
    $u = user_by_id((int) $uid);
    if ((int) ($u['gold'] ?? 0) < $c['cost']) {
        return '钱不够，' . $c['name'] . '种子要' . fmt_money($c['cost']) . '。';
    }
    $u['gold'] = (int) ($u['gold'] ?? 0) - $c['cost'];
    user_save($u);
    $d[$plot] = ['crop' => $crop, 'at' => time()];
    cflag_set((int) $uid, 'farm', $d);
    return '种下' . $c['name'] . '，' . dummy_fmt($c['grow']) . '后收菜。';
}

function farm_harvest(int $uid, int $plot): string
{
    $d = farm_plots((int) $uid);
    if (empty($d[$plot])) {
        return '这块地是空的。';
    }
    $c = farm_crops()[$d[$plot]['crop']] ?? null;
    if ($c === null) {
        unset($d[$plot]);
        cflag_set((int) $uid, 'farm', $d);
        return '这茬坏了，清掉了。';
    }
    if (time() - (int) $d[$plot]['at'] < $c['grow']) {
        return $c['name'] . '还没熟，剩' . dummy_fmt($c['grow'] - (time() - (int) $d[$plot]['at'])) . '。';
    }
    $lost = (int) ($d[$plot]['stolen'] ?? 0);
    unset($d[$plot]);
    cflag_set((int) $uid, 'farm', $d);
    $gain = max(1, $c['coin'] - $lost);
    add_mat((int) $uid, 'farm_coin', $gain);
    return '收获' . $c['name'] . '！+' . $gain . '农场币' . ($lost > 0 ? '（被偷了' . $lost . '币）' : '') . '。';
}

function farm_exchange(int $uid, string $m): string
{
    $tab = ['petfood' => ['pet_food', 10, 20, '宠物粮食'], 'expcard' => ['exp_card100', 100, 2, '升级卡100型'], 'reset' => ['reset_potion', 200, 1, '属性洗点药']];
    if (!isset($tab[$m])) {
        return '不换这个。';
    }
    [$give, $cost, $limit, $nm] = $tab[$m];
    $wk = 'fx_' . $m . '_' . war_week();
    $used = (int) (cflags((int) $uid)[$wk] ?? 0);
    if ($used >= $limit) {
        return $nm . '本周换满了（限' . $limit . '）。';
    }
    $mats = mats_of((int) $uid);
    if ((int) ($mats['farm_coin'] ?? 0) < $cost) {
        return '农场币不够，要' . $cost . '。';
    }
    add_mat((int) $uid, 'farm_coin', -$cost);
    add_mat((int) $uid, $give, 1);
    cflag_set((int) $uid, $wk, $used + 1);
    return '换到【' . $nm . '】！本周已换' . ($used + 1) . '/' . $limit . '。';
}

function ch1_locations(): array
{
    $m = [];
    $m['town_sq'] = ['name' => '白石镇·广场', 'desc' => '第一章·灰烬中的低语。镇口木牌写着“人口：47”。风里有硫磺和腐锈味，镇长宅邸在北面。', 'exits' => ['guild' => '冒险者公会', 'wall' => '城墙', 'street_e' => '石板街·东', 'street_w' => '雾巷·西', 'gate' => '白石镇道口'], 'monsters' => []];
    $m['street_e'] = ['name' => '石板街·东', 'desc' => '老铁匠·巴顿的铁钩敲着白纹矿石。杂货铺的灯在街尽头。', 'exits' => ['town_sq' => '广场', 'smith' => '镇铁匠', 'supply' => '杂货铺'], 'monsters' => []];
    $m['street_w'] = ['name' => '雾巷·西', 'desc' => '雾最浓的巷子。教堂钟声从里面传来，镇长宅邸的窗帘紧闭。', 'exits' => ['town_sq' => '广场', 'church' => '教堂', 'mansion' => '镇长宅邸'], 'monsters' => []];
    $m['guild'] = ['name' => '冒险者公会', 'desc' => '王国公会派你来调查白石镇异变。镇长在等你，公会前台暂由空缺的调查队记录代替。', 'exits' => ['town_sq' => '广场', 'mansion' => '镇长宅邸'], 'monsters' => []];
    $m['church'] = ['name' => '小教堂', 'desc' => '牧师·托马斯蜷在角落，反复念叨：“眼睛……到处都是眼睛……”', 'exits' => ['street_w' => '雾巷', 'market' => '市场'], 'monsters' => []];
    $m['smith'] = ['name' => '镇铁匠', 'desc' => '老铁匠·巴顿只有一只手，另一边是铁钩。他说：空手下矿就是送死。', 'exits' => ['street_e' => '石板街', 'tavern' => '醉狼酒馆'], 'monsters' => []];
    $m['tavern'] = ['name' => '醉狼酒馆', 'desc' => '老杰克擦着永远擦不干净的杯子：“别信镇长。”', 'exits' => ['smith' => '镇铁匠'], 'monsters' => []];
    $m['market'] = ['name' => '杂货铺', 'desc' => '玛莎的嗓门比街角的钟还响：火把、干粮、回城卷轴，什么都有。', 'exits' => ['church' => '小教堂', 'sewer' => '旧水道'], 'monsters' => []];
    $m['supply'] = ['name' => '杂货铺后铺', 'desc' => '玛莎：“石头比金子值钱，别把矿里捡的破烂扔了。”', 'exits' => ['street_e' => '石板街'], 'monsters' => []];
    $m['mansion'] = ['name' => '镇长宅邸', 'desc' => '奥古斯都的窗帘紧闭。门缝里飘出白纹矿石的粉尘。', 'exits' => ['street_w' => '雾巷', 'guild' => '冒险者公会'], 'monsters' => []];
    $m['wall'] = ['name' => '城墙', 'desc' => '队长：“荒野狼20只，杀完领赏。东边两条荒野，南边是道口。”', 'exits' => ['town_sq' => '广场', 'gate' => '白石镇道口', 'mine_gate' => '矿坑封锁门', 'wild1' => '荒野1', 'wild2' => '荒野2'], 'monsters' => []];
    $m['sewer'] = ['name' => '旧水道', 'desc' => '盗贼眼线。腐化老鼠从排水沟钻出，卡尔要你证明自己能活着回来。', 'exits' => ['market' => '市场'], 'monsters' => ['corrupt_rat', 'thief', 'rat']];
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
    $m['mine_gate'] = ['name' => '白石矿坑·封锁门', 'desc' => '三年前被封死的矿坑。门上钉着王国调查队的旧封条。', 'exits' => ['town_sq' => '白石镇广场', 'mine1' => '矿坑第一层'], 'monsters' => []];
    $m['mine1'] = ['name' => '矿坑第一层·白纹矿脉', 'desc' => '矿车自己在动。空气里飘着腐化孢子的甜味。', 'exits' => ['mine_gate' => '封锁门', 'mine2' => '矿坑第二层'], 'monsters' => ['corrupt_rat', 'deep_bat']];
    $m['mine2'] = ['name' => '矿坑第二层·失落营地', 'desc' => '调查队的营火还在燃。墙上写着：不要回答。', 'exits' => ['mine1' => '第一层', 'mine3' => '矿坑第三层'], 'monsters' => ['runaway_miner', 'deep_bat']];
    $m['mine3'] = ['name' => '矿坑第三层·裂隙前', 'desc' => '凿穿的墙后没有岩石。十二具人形轮廓嵌在黑色流动空间里。', 'exits' => ['mine2' => '第二层', 'echo_room' => '回响厅'], 'monsters' => ['abyss_spore']];
    $m['echo_room'] = ['name' => '回响厅', 'desc' => '雷恩的声音从四面传来：“你也要进来吗？里面很温暖。”不要回答。', 'exits' => ['mine3' => '第三层'], 'monsters' => ['echo_rayne']];
    return $m;
}

function ch1_quests(): array
{
    return [
        10 => ['name' => '灰烬中的低语', 'todo' => '去冒险者公会，再到镇长宅邸接受白石矿坑调查委托', 'loc' => 'guild', 'locname' => '冒险者公会', 'need' => []],
        11 => ['name' => '腐化的前兆', 'todo' => '听卡尔队长的考验：击杀10只腐化老鼠', 'loc' => 'sewer', 'locname' => '旧水道', 'need' => ['corrupt_rat' => 10]],
        12 => ['name' => '调查队的营火', 'todo' => '进入矿坑第一层，击败10名失控矿工并找回调查队记录', 'loc' => 'mine1', 'locname' => '矿坑第一层', 'need' => ['runaway_miner' => 10]],
        13 => ['name' => '不要回答', 'todo' => '穿过矿坑第二层，在第三层击杀10只腐化孢子体，找到裂隙入口', 'loc' => 'mine3', 'locname' => '矿坑第三层', 'need' => ['abyss_spore' => 10]],
        14 => ['name' => '深渊回响·雷恩', 'todo' => '在回响厅击败调查队长雷恩，绝不要回应他的呼唤', 'loc' => 'echo_room', 'locname' => '回响厅', 'need' => ['echo_rayne' => 1]],
        15 => ['name' => '镇长的信', 'todo' => '回到镇长宅邸，决定揭发、质问或隐瞒奥古斯都的交易', 'loc' => 'mansion', 'locname' => '镇长宅邸', 'need' => []],
    ];
}

function quest_state(array $u): array
{
    $q = (int) ($u['quest'] ?? 0);
    if ($q < 10) {
        return quest_of($q) + ['id' => $q, 'ch' => 0];
    }
    if ($q === 16) {
        if ((int) ($u['lv'] ?? 1) >= 30) {
            return ['name' => '第二章·影蚀之潮', 'step' => '序', 'todo' => '30级了！去白银城公会大厅找会长格温，开启第二章', 'loc' => 'sguild', 'locname' => '公会大厅', 'id' => 16, 'ch' => 1, 'need' => []];
        }
        return ['name' => '雾散之后', 'step' => '完成', 'todo' => '第一章完成，自由探索。30级后去白银城公会大厅找格温', 'loc' => 'town_sq', 'locname' => '白石镇广场', 'id' => 16, 'ch' => 1, 'need' => []];
    }
    if ($q === 28) {
        return ['name' => '第三章·傀儡之夜', 'step' => '序', 'todo' => '第二章完成！带着格温的推荐信，去王都中央大道找瓦尔顿', 'loc' => 'avenue', 'locname' => '中央大道', 'id' => 28, 'ch' => 2, 'need' => []];
    }
    if ($q >= 30 && $q <= 37) {
        $all = ch3_quests();
        if (!isset($all[$q])) {
            return ['name' => '傀儡之夜·完', 'step' => '完成', 'todo' => '第三章完成，去王都农场种菜吧', 'loc' => 'farm', 'locname' => '王都农场', 'id' => 99, 'ch' => 3, 'need' => []];
        }
        return $all[$q] + ['id' => $q, 'ch' => 3, 'step' => ($q - 29) . '/8'];
    }
    if ($q >= 38) {
        return ['name' => '影子伙伴', 'step' => '完成', 'todo' => '第三章完成！农场、母巢、公会战等你（影子伙伴：全属性+5%）', 'loc' => 'farm', 'locname' => '王都农场', 'id' => 99, 'ch' => 3, 'need' => []];
    }
    if ($q >= 20 && $q <= 27) {
        $all = ch2_quests();
        if (!isset($all[$q])) {
            return ['name' => '影蚀之潮·完', 'step' => '完成', 'todo' => '第二章完成，自由探索', 'loc' => 'silver_sq', 'locname' => '白银广场', 'id' => 99, 'ch' => 2, 'need' => []];
        }
        return $all[$q] + ['id' => $q, 'ch' => 2, 'step' => ($q - 19) . '/8'];
    }
    $all = ch1_quests();
    if (!isset($all[$q])) {
        return ['name' => '雾散之后', 'step' => '完成', 'todo' => '第一章完成，自由探索', 'loc' => 'town_sq', 'locname' => '白石镇广场', 'id' => 99, 'ch' => 1, 'need' => []];
    }
    return $all[$q] + ['id' => $q, 'ch' => 1, 'step' => ($q - 9) . '/6'];
}

function ch2_quests(): array
{
    return [
        20 => ['name' => '影蚀之潮·抵达', 'todo' => '去公会大厅见格温，清理下水道一层影蚀鼠×10', 'loc' => 'sguild', 'locname' => '公会大厅', 'need' => ['shadow_rat' => 10], 'flag' => null],
        21 => ['name' => '下水道·雷德', 'todo' => '继续清理影蚀鼠×15，角落里的守卫雷德在求救', 'loc' => 'dsewer1', 'locname' => '下水道一层', 'need' => ['shadow_rat' => 15], 'flag' => 'reed_done'],
        22 => ['name' => '深处笔记·莉莉', 'todo' => '下水道二层：影蚀鼠×10、影子士兵×10，找到调查队笔记', 'loc' => 'dsewer2', 'locname' => '下水道二层', 'need' => ['shadow_rat' => 10, 'shadow_soldier' => 10], 'flag' => 'note_done'],
        23 => ['name' => '矿道·使徒', 'todo' => '影子士兵×15、腐化守卫×10，矿道二层斩深渊使徒', 'loc' => 'bmine2', 'locname' => '矿道二层', 'need' => ['shadow_soldier' => 15, 'corrupt_guard' => 10, 'apostle' => 1], 'flag' => 'apostle_done'],
        24 => ['name' => '森林·守护者', 'todo' => '猎犬×10、树精×8、石像鬼×7，直面巨型傀儡', 'loc' => 'sforest', 'locname' => '影蚀森林', 'need' => ['shadow_hound' => 10, 'corrupt_treant' => 8, 'gargoyle' => 7], 'flag' => 'golem_done'],
        25 => ['name' => '庄园·城主', 'todo' => '符文傀儡×15，废弃庄园对峙城主瓦伦丁', 'loc' => 'manor', 'locname' => '废弃庄园', 'need' => ['puppet' => 15, 'valentin' => 1], 'flag' => 'lord_done'],
        26 => ['name' => '祭坛·暗影莉莉', 'todo' => '深渊祭坛直面暗影莉莉，问出真正的莉莉在哪', 'loc' => 'baltar', 'locname' => '暗影祭坛', 'need' => ['dark_lily' => 1], 'flag' => 'lily_done'],
        27 => ['name' => '裂隙·深渊之眼', 'todo' => '裂隙深处：沉默面对深渊之眼，绝不回答', 'loc' => 'rift', 'locname' => '裂隙深处', 'need' => ['abyss_eye' => 1], 'flag' => null],
    ];
}

function ch3_quests(): array
{
    return [
        30 => ['name' => '入城清剿', 'todo' => '王都中央大道：傀儡守卫×80', 'loc' => 'avenue', 'locname' => '中央大道', 'need' => ['puppet_guard' => 80], 'flag' => null],
        31 => ['name' => '假面舞会', 'todo' => '贵族区：假面贵族×100', 'loc' => 'noble', 'locname' => '贵族区', 'need' => ['masked_noble' => 100], 'flag' => null],
        32 => ['name' => '图书馆的剧本', 'todo' => '皇家图书馆：墨傀儡×100', 'loc' => 'library', 'locname' => '皇家图书馆', 'need' => ['ink_puppet' => 100], 'flag' => null],
        33 => ['name' => '陵墓的空棺', 'todo' => '皇家陵墓：银丝亡灵×120', 'loc' => 'ctomb', 'locname' => '皇家陵墓', 'need' => ['silver_undead' => 120], 'flag' => null],
        34 => ['name' => '黑市的剪线人', 'todo' => '贫民窟：银丝刺客×150', 'loc' => 'slum', 'locname' => '贫民窟', 'need' => ['silver_assassin' => 150], 'flag' => null],
        35 => ['name' => '大教堂的木偶戏', 'todo' => '光明大教堂：牵线牧师×180', 'loc' => 'cathedral', 'locname' => '光明大教堂', 'need' => ['puppet_priest' => 180], 'flag' => null],
        36 => ['name' => '天文台的观众', 'todo' => '皇家天文台：观星傀儡×200', 'loc' => 'observatory', 'locname' => '皇家天文台', 'need' => ['star_puppet' => 200], 'flag' => null],
        37 => ['name' => '傀儡剧场', 'todo' => '王宫地下：银丝傀儡×250、击败牵线者', 'loc' => 'theater', 'locname' => '傀儡剧场', 'need' => ['silver_puppet' => 250, 'string_puller' => 1], 'flag' => null],
    ];
}

function check_ch3_done(int $uid, array $qs): bool
{
    $uid = (int) $uid;
    if (!empty($qs['flag'])) {
        $f = cflags($uid);
        if (empty($f[$qs['flag']])) {
            return false;
        }
    }
    if (!empty($qs['need'])) {
        $c = kill_counts($uid);
        $base = cflags($uid)['qb' . ($qs['id'] ?? 0)] ?? [];
        if (!is_array($base)) {
            $base = [];
        }
        foreach ($qs['need'] as $mid => $need) {
            if ((int) ($c[$mid] ?? 0) - (int) ($base[$mid] ?? 0) < $need) {
                return false;
            }
        }
    }
    return true;
}

function quest3_baseline(int $uid, int $q): void
{
    $qs = ch3_quests()[$q] ?? null;
    if (!$qs || empty($qs['need'])) {
        return;
    }
    $c = kill_counts((int) $uid);
    $b = [];
    foreach ($qs['need'] as $mid => $need) {
        $b[$mid] = (int) ($c[$mid] ?? 0);
    }
    cflag_set((int) $uid, 'qb' . $q, $b);
}

function quest_progress3_text(int $uid, array $qs): string
{
    if (empty($qs['need'])) {
        return '';
    }
    $c = kill_counts((int) $uid);
    $base = cflags((int) $uid)['qb' . ($qs['id'] ?? 0)] ?? [];
    if (!is_array($base)) {
        $base = [];
    }
    $parts = [];
    foreach ($qs['need'] as $mid => $need) {
        $mn = monsters()[$mid]['name'] ?? $mid;
        $parts[] = $mn . max(0, (int) ($c[$mid] ?? 0) - (int) ($base[$mid] ?? 0)) . '/' . $need;
    }
    return implode('，', $parts);
}

function cflags(int $uid): array
{
    $u = user_by_id((int) $uid);
    $d = json_decode((string) ($u['chapter_flags'] ?? ''), true);
    return is_array($d) ? $d : [];
}

function cflag_set(int $uid, string $k, $v): void
{
    $d = cflags((int) $uid);
    $d[$k] = $v;
    db()->prepare('UPDATE users SET chapter_flags=? WHERE id=?')->execute([json_encode($d, JSON_UNESCAPED_UNICODE), (int) $uid]);
}

function check_ch2_done(int $uid, array $qs): bool
{
    $uid = (int) $uid;
    if (!empty($qs['flag'])) {
        $f = cflags($uid);
        if (empty($f[$qs['flag']])) {
            return false;
        }
    }
    if (!empty($qs['need'])) {
        $c = kill_counts($uid);
        $base = cflags($uid)['qb' . ($qs['id'] ?? 0)] ?? [];
        if (!is_array($base)) {
            $base = [];
        }
        foreach ($qs['need'] as $mid => $need) {
            if ((int) ($c[$mid] ?? 0) - (int) ($base[$mid] ?? 0) < $need) {
                return false;
            }
        }
    }
    return true;
}

function quest2_baseline(int $uid, int $q): void
{
    $qs = ch2_quests()[$q] ?? null;
    if (!$qs || empty($qs['need'])) {
        return;
    }
    $c = kill_counts((int) $uid);
    $b = [];
    foreach ($qs['need'] as $mid => $need) {
        $b[$mid] = (int) ($c[$mid] ?? 0);
    }
    cflag_set((int) $uid, 'qb' . $q, $b);
}

function quest_progress2_text(int $uid, array $qs): string
{
    if (empty($qs['need'])) {
        return '';
    }
    $c = kill_counts((int) $uid);
    $base = cflags((int) $uid)['qb' . ($qs['id'] ?? 0)] ?? [];
    if (!is_array($base)) {
        $base = [];
    }
    $parts = [];
    $killsDone = true;
    foreach ($qs['need'] as $mid => $need) {
        $mn = monsters()[$mid]['name'] ?? $mid;
        $have = max(0, (int) ($c[$mid] ?? 0) - (int) ($base[$mid] ?? 0));
        if ($have < $need) {
            $killsDone = false;
        }
        $parts[] = $mn . $have . '/' . $need;
    }
    $txt = implode('，', $parts);
    if ($killsDone && !empty($qs['flag'])) {
        $f = cflags((int) $uid);
        if (empty($f[$qs['flag']])) {
            $who = ['reed_done' => '下水道一层的守卫雷德', 'note_done' => '下水道二层的莉莉', 'apostle_done' => '矿道二层的使徒残响', 'golem_done' => '影蚀森林的守护者残响', 'lord_done' => '废弃庄园的城主瓦伦丁', 'lily_done' => '暗影祭坛的暗影莉莉'][$qs['flag']] ?? '关键NPC';
            $txt .= '（怪杀够了，去找' . $who . '做抉择）';
        }
    }
    return $txt;
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
