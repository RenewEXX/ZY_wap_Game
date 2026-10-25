<?php
declare(strict_types=1);

function jobs(): array
{
    return [
        'warrior' => ['name' => '战士', 'hp' => 62, 'mp' => 20, 'atk' => 7, 'def' => 5, 'skill' => '铁壁格挡', 'weapon' => 'axe', 'armor' => 'cloth', 'desc' => '物理近战，血多甲厚。防战堆双防、挨打按防御反击；狂战本场损血越多攻击越高。只能用斧剑锤，盾牌专精'],
        'mage' => ['name' => '法师', 'hp' => 42, 'mp' => 55, 'atk' => 11, 'def' => 1, 'skill' => '火球术', 'weapon' => 'staff', 'armor' => 'cloth', 'desc' => '纯法术炮台，伤害看智慧+法攻。火球/狂雷/寒霜全是法伤。只能用法杖法器，力量对法师无效'],
        'hunter' => ['name' => '猎手', 'hp' => 50, 'mp' => 30, 'atk' => 8, 'def' => 3, 'skill' => '双连射', 'weapon' => 'bow', 'armor' => 'cloth', 'desc' => '游侠走群攻持续伤害磨死全场；刺客走单体超高爆发+高闪避。只能用弓弩匕首'],
        'priest' => ['name' => '牧师', 'hp' => 54, 'mp' => 45, 'atk' => 6, 'def' => 3, 'skill' => '治疗术', 'weapon' => 'mace', 'armor' => 'cloth', 'desc' => '治疗看法攻，神圣伤害也是法伤。能用锤杖盾'],
        'summoner' => ['name' => '召唤师', 'short' => '召', 'hp' => 40, 'mp' => 50, 'atk' => 5, 'def' => 2, 'skill' => '深渊契约', 'weapon' => 'relic', 'armor' => 'cloth', 'desc' => '法系/召唤/持续伤害。不信神，不靠元素，把自己当容器与深渊游魂签订契约。战斗中可即时召唤深渊小鬼，召唤兽替你承伤也替你输出。本体是五职业里最脆的：血量成长全场最低，强度全在召唤兽身上——你倒了它们就散。主手限魂契器（魂灯/契杖/骨杖），副手限深渊契匣'],
    ];
}

// 职业背景与特色：给创建角色页选职业时看
function job_flavor(string $job): array
{
    $f = [
        'warrior' => [
            'bg' => '灰雾村的最后一批守夜人。曾在矿道塌方那天背着三个人爬出来，从此认定一件事：站着别倒下，比打死谁都重要。',
            'feat' => '两个方向。防战：物防法防都堆，挨打时按防御等比例反击，越硬越疼；狂战：本场掉的血越多攻击越高（按数值算，不是按百分比），越痛越强。',
        ],
        'mage' => [
            'bg' => '白石镇图书馆的抄书员。抄了十年禁书，发现每页边角都写着同一句话，就此学会跟元素说话。',
            'feat' => '纯法术输出，伤害吃法攻与智慧。控制手段最多：麻痹让对手停手，冻结削对手的攻击，元素亲和让所有元素伤再高12%。',
        ],
        'hunter' => [
            'bg' => '走私队出身，箭术是跟猎人学的，毒是跟沼泽里的采药人学的。规矩只有一条：不追不属于自己的猎物。',
            'feat' => '两个方向。游侠：所有技能都往目标身上叠持续伤害，靠磨死全场；刺客：放弃群攻换单体超高爆发，靠高闪避让对手根本打不到你。',
        ],
        'priest' => [
            'bg' => '本该在圣殿安安稳稳念经，却总在不该待的地方。牧师执照是补发的，因为原来的那位已经替他们死在了裂隙里。',
            'feat' => '唯一能把伤害变成治疗的职业：先奶人，打出去的暗伤有四到八成直接转成自身生命，站桩输出也养不活自己。',
        ],
        'summoner' => [
            'bg' => '不信神，不靠元素，把自己当容器，跟深渊游魂签下契约。他很清楚这笔交易的代价——你倒了，它们就散。',
            'feat' => '本体五职业里最脆，血量成长全场最低，强度全在召唤兽身上。召唤兽替你输出也替你承伤，契匣开场自动开匣，魂契5件后每只死时都会爆炸。',
        ],
    ];
    return $f[$job] ?? ['bg' => '', 'feat' => ''];
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
    return world_link_regions([
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
            'monsters' => ['goblin', 'lucky_goblin'],
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
            'exits' => ['town_sq' => '白石镇广场', 'bridge' => '断桥', 'tunnel' => '腐骨甬道', 'shop' => '黑市', 'camp' => '残火营地', 'buf12a' => '银道荒径'],
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
    ] + ch1_locations() + dsw_locations() + ch2_locations() + abx_locations() + ch3_locations() + mx_locations() + farm_locations() + ice_locations() + fr_locations() + fo_locations() + em_locations() + dragon_locations() + pz_locations() + sen_locations() + wild3_locations() + frostburg_locations() + greenheart_locations() + wild45_locations() + as_locations() + vx_locations() + gr_locations() + ry_locations() + god_locations() + gf_locations() + world_expansion_locations() + region_dungeon_locations());
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
        'frog' => ['name' => '黑水毒蛙', 'hp' => 55, 'atk' => 15, 'exp' => 41, 'gold' => 19],
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
        'puppet' => ['name' => '符文傀儡', 'hp' => 1500, 'atk' => 105, 'exp' => 2080, 'gold' => 845],
        'apostle' => ['name' => '深渊使徒', 'hp' => 2500, 'atk' => 110, 'exp' => 3000, 'gold' => 1500],
        'guardian' => ['name' => '巨型傀儡·守护者', 'hp' => 4000, 'atk' => 140, 'exp' => 5000, 'gold' => 2500],
        // ---- 70~100级·影蚀荒原 ----
        'waste_stalker' => ['name' => '荒原影行者', 'hp' => 1600, 'atk' => 100, 'exp' => 2800, 'gold' => 1100],
        'waste_hound' => ['name' => '蚀影荒犬', 'hp' => 1900, 'atk' => 108, 'exp' => 3400, 'gold' => 1300],
        'waste_colossus' => ['name' => '荒原蚀像', 'hp' => 2600, 'atk' => 125, 'exp' => 4600, 'gold' => 1800],
        'valentin' => ['name' => '城主瓦伦丁', 'hp' => 5500, 'atk' => 170, 'exp' => 7000, 'gold' => 4000],
        'dark_lily' => ['name' => '暗影莉莉', 'hp' => 7000, 'atk' => 200, 'exp' => 10000, 'gold' => 6000],
        'abyss_eye' => ['name' => '深渊之眼', 'hp' => 10000, 'atk' => 240, 'exp' => 12000, 'gold' => 10000],
        'shadow_walker' => ['name' => '影蚀行者', 'hp' => 1800, 'atk' => 115, 'exp' => 2000, 'gold' => 800],
        'corrupt_warder' => ['name' => '腐影守卫', 'hp' => 2600, 'atk' => 140, 'exp' => 3000, 'gold' => 1200],
        'whisperer' => ['name' => '深渊低语者', 'hp' => 4200, 'atk' => 180, 'exp' => 5500, 'gold' => 2000],
        'rift_hound' => ['name' => '裂隙猎犬', 'hp' => 5200, 'atk' => 200, 'exp' => 7200, 'gold' => 2800],
        'rift_weaver' => ['name' => '裂隙织影者', 'hp' => 7800, 'atk' => 260, 'exp' => 11000, 'gold' => 4200],
        'colossus' => ['name' => '影蚀巨像', 'hp' => 6500, 'atk' => 230, 'exp' => 9000, 'gold' => 3500],
        'abaddon' => ['name' => '影蚀君王阿巴顿', 'hp' => 20000, 'atk' => 340, 'exp' => 30000, 'gold' => 20000],
        'gy_kindness' => ['name' => '善意', 'hp' => 1, 'atk' => 1, 'exp' => 100, 'gold' => 0],
        'gy_love' => ['name' => '爱意', 'hp' => 1, 'atk' => 1, 'exp' => 100, 'gold' => 0],
        'gy_friendship' => ['name' => '友谊', 'hp' => 1, 'atk' => 1, 'exp' => 100, 'gold' => 0],
        'gy_courage' => ['name' => '勇气', 'hp' => 1, 'atk' => 1, 'exp' => 200, 'gold' => 0],
        'gy_hope' => ['name' => '希望', 'hp' => 1, 'atk' => 1, 'exp' => 200, 'gold' => 0],
        'gy_trust' => ['name' => '信任', 'hp' => 1, 'atk' => 1, 'exp' => 200, 'gold' => 0],
        'gy_mercy' => ['name' => '怜悯', 'hp' => 1, 'atk' => 1, 'exp' => 300, 'gold' => 0],
        'gy_justice' => ['name' => '正义', 'hp' => 1, 'atk' => 1, 'exp' => 500, 'gold' => 0],
        // ---- 神界（700~999级） ----
        'god_seraph' => ['name' => '六翼天使', 'hp' => 2400000, 'atk' => 6500, 'exp' => 4200000, 'gold' => 2000000],
        'god_cloud' => ['name' => '云海执事', 'hp' => 2600000, 'atk' => 6800, 'exp' => 4500000, 'gold' => 2150000],
        'god_watcher' => ['name' => '神庭守望', 'hp' => 2800000, 'atk' => 7000, 'exp' => 4800000, 'gold' => 2300000],
        'god_judge' => ['name' => '裁决天使', 'hp' => 3000000, 'atk' => 7300, 'exp' => 5100000, 'gold' => 2450000],
        'god_warden' => ['name' => '神铸守卫', 'hp' => 3200000, 'atk' => 7500, 'exp' => 5400000, 'gold' => 2600000],
        'god_forger' => ['name' => '神匠', 'hp' => 3400000, 'atk' => 7800, 'exp' => 5800000, 'gold' => 2800000],
        'god_golem' => ['name' => '天柱石像', 'hp' => 3600000, 'atk' => 8000, 'exp' => 6200000, 'gold' => 3000000],
        'god_titan' => ['name' => '天柱泰坦', 'hp' => 4000000, 'atk' => 8500, 'exp' => 7000000, 'gold' => 3400000],
        'god_avatar' => ['name' => '神座化身', 'hp' => 6000000, 'atk' => 9200, 'exp' => 9000000, 'gold' => 4400000],
        'gf_shade' => ['name' => '堕天幽影', 'hp' => 3300000, 'atk' => 7600, 'exp' => 5600000, 'gold' => 2700000],
        'gf_void' => ['name' => '虚空噬者', 'hp' => 3500000, 'atk' => 7900, 'exp' => 6000000, 'gold' => 2900000],
        'gf_reaper' => ['name' => '天罚收割者', 'hp' => 3700000, 'atk' => 8200, 'exp' => 6500000, 'gold' => 3100000],
        'gf_herald' => ['name' => '堕天传令', 'hp' => 4000000, 'atk' => 8600, 'exp' => 7200000, 'gold' => 3500000],
        'god_emperor' => ['name' => '堕天神帝·路西法', 'hp' => 30000000, 'atk' => 10000, 'exp' => 20000000, 'gold' => 10000000],
        'god_cherub' => ['name' => '智天使', 'hp' => 2200000, 'atk' => 6300, 'exp' => 4000000, 'gold' => 1900000],
        'god_heraldg' => ['name' => '神界传令', 'hp' => 2900000, 'atk' => 7100, 'exp' => 5100000, 'gold' => 2450000],
        'god_highpriest' => ['name' => '神界大祭司', 'hp' => 4600000, 'atk' => 8800, 'exp' => 7600000, 'gold' => 3600000],
        'god_archon' => ['name' => '神界执政官', 'hp' => 8000000, 'atk' => 9600, 'exp' => 12000000, 'gold' => 6000000],
        'gf_seraphim' => ['name' => '堕天炽天使', 'hp' => 4200000, 'atk' => 8500, 'exp' => 7000000, 'gold' => 3400000],
        'yz_boss' => ['name' => '源罪·本相', 'hp' => 88000000, 'atk' => 11200, 'exp' => 45000000, 'gold' => 22000000],
        'god_pilgrim' => ['name' => '拾光朝圣者', 'hp' => 2300000, 'atk' => 6400, 'exp' => 4100000, 'gold' => 1900000],
        'god_librarian' => ['name' => '藏星典吏', 'hp' => 3100000, 'atk' => 7200, 'exp' => 5300000, 'gold' => 2500000],
        'god_gardener' => ['name' => '种星园丁', 'hp' => 3500000, 'atk' => 7900, 'exp' => 6000000, 'gold' => 2800000],
        'god_mirror' => ['name' => '镜中判者', 'hp' => 4800000, 'atk' => 9000, 'exp' => 8000000, 'gold' => 3800000],
        'god_sleeper' => ['name' => '失翼沉眠者', 'hp' => 5800000, 'atk' => 9300, 'exp' => 9500000, 'gold' => 4600000],
        'god_serpent' => ['name' => '衔尾天蛇', 'hp' => 7500000, 'atk' => 9500, 'exp' => 11000000, 'gold' => 5500000],
        'gf_fallen' => ['name' => '折翼圣者', 'hp' => 3600000, 'atk' => 8000, 'exp' => 6200000, 'gold' => 3000000],
        'road_wolf' => ['name' => '拦路野狼', 'hp' => 900, 'atk' => 70, 'exp' => 800, 'gold' => 300],
        'road_bandit' => ['name' => '拦路强人', 'hp' => 1400, 'atk' => 95, 'exp' => 1400, 'gold' => 600],
        'dust_raider' => ['name' => '尘埃劫掠者', 'hp' => 9000, 'atk' => 280, 'exp' => 14000, 'gold' => 6000],
        'dust_marauder' => ['name' => '尘埃掠夺者', 'hp' => 14000, 'atk' => 330, 'exp' => 20000, 'gold' => 9000],
        'snow_drifter' => ['name' => '雪原流浪者', 'hp' => 180000, 'atk' => 2100, 'exp' => 400000, 'gold' => 190000],
        'snow_reaver' => ['name' => '雪原掠夺者', 'hp' => 200000, 'atk' => 2250, 'exp' => 450000, 'gold' => 210000],
        'ash_drifter' => ['name' => '灰烬流浪者', 'hp' => 260000, 'atk' => 2900, 'exp' => 600000, 'gold' => 280000],
        'ash_reaver' => ['name' => '灰烬掠夺者', 'hp' => 290000, 'atk' => 3100, 'exp' => 680000, 'gold' => 320000],
        'puppet_guard' => ['name' => '傀儡守卫', 'hp' => 18000, 'atk' => 320, 'exp' => 45500, 'gold' => 23400],
        'masked_noble' => ['name' => '假面贵族', 'hp' => 26000, 'atk' => 380, 'exp' => 52000, 'gold' => 26000],
        'ink_puppet' => ['name' => '墨傀儡', 'hp' => 36000, 'atk' => 460, 'exp' => 80000, 'gold' => 38000],
        'silver_undead' => ['name' => '银丝亡灵', 'hp' => 48000, 'atk' => 560, 'exp' => 115000, 'gold' => 55000],
        'silver_assassin' => ['name' => '银丝刺客', 'hp' => 60000, 'atk' => 660, 'exp' => 160000, 'gold' => 75000],
        'puppet_priest' => ['name' => '牵线牧师', 'hp' => 75000, 'atk' => 760, 'exp' => 279500, 'gold' => 130000],
        'star_puppet' => ['name' => '观星傀儡', 'hp' => 90000, 'atk' => 850, 'exp' => 280000, 'gold' => 130000],
        'silver_puppet' => ['name' => '银丝傀儡', 'hp' => 100000, 'atk' => 900, 'exp' => 310000, 'gold' => 150000],
        'string_puller' => ['name' => '牵线者', 'hp' => 220000, 'atk' => 1100, 'exp' => 900000, 'gold' => 450000],
        'silk_spider' => ['name' => '银丝蛛', 'hp' => 70000, 'atk' => 700, 'exp' => 234000, 'gold' => 110500],
        'cocoon_guard' => ['name' => '茧守', 'hp' => 80000, 'atk' => 780, 'exp' => 220000, 'gold' => 105000],
        'thread_weaver' => ['name' => '织线者', 'hp' => 90000, 'atk' => 840, 'exp' => 260000, 'gold' => 120000],
        'silk_moth' => ['name' => '银丝蛾', 'hp' => 100000, 'atk' => 900, 'exp' => 390000, 'gold' => 182000],
        'nest_watcher' => ['name' => '巢穴守望', 'hp' => 110000, 'atk' => 960, 'exp' => 340000, 'gold' => 160000],
        'brood_maiden' => ['name' => '育巢侍女', 'hp' => 125000, 'atk' => 1050, 'exp' => 400000, 'gold' => 190000],
        'silk_mother' => ['name' => '缠丝之母', 'hp' => 300000, 'atk' => 1300, 'exp' => 1560000, 'gold' => 780000],
        // ---- 第四章·凛冬冰原（350~480级） ----
        'frost_wolf' => ['name' => '冻原霜狼', 'hp' => 196000, 'atk' => 2400, 'exp' => 494000, 'gold' => 234000],
        'ice_specter' => ['name' => '冰隙怨灵', 'hp' => 217000, 'atk' => 2550, 'exp' => 420000, 'gold' => 200000],
        'snow_mammoth' => ['name' => '雪原猛犸', 'hp' => 251999, 'atk' => 2775, 'exp' => 500000, 'gold' => 235000],
        'blizzard_shaman' => ['name' => '暴风雪萨满', 'hp' => 237999, 'atk' => 2925, 'exp' => 520000, 'gold' => 250000],
        'glacier_golem' => ['name' => '冰川魔像', 'hp' => 294000, 'atk' => 3075, 'exp' => 600000, 'gold' => 290000],
        'frost_giant' => ['name' => '霜巨人', 'hp' => 329000, 'atk' => 3270, 'exp' => 884000, 'gold' => 429000],
        'aurora_serpent' => ['name' => '极光冰蛇', 'hp' => 308000, 'atk' => 3375, 'exp' => 700000, 'gold' => 340000],
        'icewyrm' => ['name' => '冰渊蠕虫', 'hp' => 350000, 'atk' => 3480, 'exp' => 760000, 'gold' => 370000],
        'winter_knight' => ['name' => '凛冬骑士', 'hp' => 378000, 'atk' => 3600, 'exp' => 820000, 'gold' => 400000],
        'frost_matriarch' => ['name' => '霜母', 'hp' => 434000, 'atk' => 3825, 'exp' => 1235000, 'gold' => 598000],
        'glacier_colossus' => ['name' => '冰河巨像', 'hp' => 489999, 'atk' => 4020, 'exp' => 1100000, 'gold' => 530000],
        'winter_king' => ['name' => '凛冬之王', 'hp' => 1120000, 'atk' => 5100, 'exp' => 3000000, 'gold' => 1500000],
        // 冰渊副本
        'fr_crawler' => ['name' => '冰渊爬行者', 'hp' => 336000, 'atk' => 3525, 'exp' => 1352000, 'gold' => 642200],
        'fr_stalker' => ['name' => '渊底潜伏者', 'hp' => 378000, 'atk' => 3720, 'exp' => 1521000, 'gold' => 726700],
        'fr_warden' => ['name' => '冰渊守望者', 'hp' => 434000, 'atk' => 3930, 'exp' => 1774500, 'gold' => 845000],
        'fr_horror' => ['name' => '渊底恐惧', 'hp' => 489999, 'atk' => 4125, 'exp' => 2028000, 'gold' => 980200],
        'fr_leviathan' => ['name' => '冰渊利维坦', 'hp' => 1200000, 'atk' => 5400, 'exp' => 5915000, 'gold' => 2873000],
        // ---- 第五章·雾森与火山（500~720级） ----
        'mist_treant' => ['name' => '雾森树人', 'hp' => 403000, 'atk' => 4160, 'exp' => 1050000, 'gold' => 500000],
        'spore_colony' => ['name' => '孢子群落', 'hp' => 442000, 'atk' => 4352, 'exp' => 1150000, 'gold' => 550000],
        'thorn_hunter' => ['name' => '荆棘猎手', 'hp' => 481000, 'atk' => 4560, 'exp' => 1280000, 'gold' => 610000],
        'ancient_boa' => ['name' => '古林巨蚺', 'hp' => 533000, 'atk' => 4768, 'exp' => 1420000, 'gold' => 680000],
        'mist_apeman' => ['name' => '雾猿', 'hp' => 572000, 'atk' => 4960, 'exp' => 1550000, 'gold' => 740000],
        'rot_sage' => ['name' => '腐叶贤者', 'hp' => 611000, 'atk' => 5152, 'exp' => 1680000, 'gold' => 800000],
        'ember_imp' => ['name' => '余烬小鬼', 'hp' => 650000, 'atk' => 5360, 'exp' => 2366000, 'gold' => 1131000],
        'lava_hound' => ['name' => '熔岩猎犬', 'hp' => 702000, 'atk' => 5568, 'exp' => 1960000, 'gold' => 940000],
        'ash_cultist' => ['name' => '灰烬教徒', 'hp' => 747500, 'atk' => 5760, 'exp' => 2730000, 'gold' => 1300000],
        'magma_golem' => ['name' => '熔岩魔像', 'hp' => 812500, 'atk' => 6000, 'exp' => 2280000, 'gold' => 1090000],
        'flame_djinn' => ['name' => '烈焰神灯灵', 'hp' => 858000, 'atk' => 6208, 'exp' => 2440000, 'gold' => 1160000],
        'cinder_dragonspawn' => ['name' => '烬龙裔', 'hp' => 923000, 'atk' => 6400, 'exp' => 2620000, 'gold' => 1250000],
        'volcano_priest' => ['name' => '火山祭司', 'hp' => 975000, 'atk' => 6592, 'exp' => 3640000, 'gold' => 1742000],
        'ash_wyrm' => ['name' => '灰烬飞龙', 'hp' => 1040000, 'atk' => 6800, 'exp' => 3900000, 'gold' => 1872000],
        'south_flame_guard' => ['name' => '南炎守卫', 'hp' => 1137500, 'atk' => 7040, 'exp' => 3300000, 'gold' => 1580000],
        'ancient_ruin_golem' => ['name' => '遗迹守卫', 'hp' => 1760000, 'atk' => 7050, 'exp' => 4200000, 'gold' => 2000000],
        // 熔心副本
        'em_slag' => ['name' => '熔渣怪', 'hp' => 845000, 'atk' => 6240, 'exp' => 4225000, 'gold' => 2028000],
        'em_fiend' => ['name' => '焚心魔', 'hp' => 942500, 'atk' => 6528, 'exp' => 4732000, 'gold' => 2264600],
        'em_magma' => ['name' => '熔核行者', 'hp' => 1040000, 'atk' => 6800, 'exp' => 5239000, 'gold' => 2501200],
        'em_ashlord' => ['name' => '灰烬领主侍从', 'hp' => 1170000, 'atk' => 7120, 'exp' => 5915000, 'gold' => 2839200],
        'em_heart' => ['name' => '熔心化身', 'hp' => 2560000, 'atk' => 8100, 'exp' => 13520000, 'gold' => 6422000],
        // 岩狱深渊副本（700~900级）
        'tt_rock' => ['name' => '岩狱岩奴', 'hp' => 4200000, 'atk' => 12000, 'exp' => 22000000, 'gold' => 10500000],
        'tt_crag' => ['name' => '岩狱峭兽', 'hp' => 4800000, 'atk' => 13200, 'exp' => 25000000, 'gold' => 12000000],
        'tt_magma' => ['name' => '岩狱熔裔', 'hp' => 5500000, 'atk' => 14500, 'exp' => 29000000, 'gold' => 13800000],
        'tt_warden' => ['name' => '岩狱狱守', 'hp' => 6400000, 'atk' => 15800, 'exp' => 34000000, 'gold' => 16200000],
        'tt_titan' => ['name' => '岩狱泰坦·土龙岳柱', 'hp' => 15000000, 'atk' => 20000, 'exp' => 90000000, 'gold' => 42000000],
        // 傀儡回廊副本（380~460级）
        'pz_puller' => ['name' => '回廊牵线者', 'hp' => 800000, 'atk' => 3000, 'exp' => 1820000, 'gold' => 858000],
        'pz_dancer' => ['name' => '回廊舞者', 'hp' => 880000, 'atk' => 3150, 'exp' => 2015000, 'gold' => 949000],
        'pz_mask' => ['name' => '覆面傀儡', 'hp' => 960000, 'atk' => 3280, 'exp' => 2210000, 'gold' => 1040000],
        'pz_golem' => ['name' => '回廊守卫', 'hp' => 1050000, 'atk' => 3400, 'exp' => 2405000, 'gold' => 1131000],
        'pz_master' => ['name' => '傀儡大师', 'hp' => 2000000, 'atk' => 4200, 'exp' => 6500000, 'gold' => 2990000],
        // 雾森祭坛副本（520~600级）
        'sen_priest' => ['name' => '祭坛雾祭', 'hp' => 1200000, 'atk' => 4200, 'exp' => 2860000, 'gold' => 1365000],
        'sen_thorn' => ['name' => '祭坛荆棘', 'hp' => 1300000, 'atk' => 4350, 'exp' => 3120000, 'gold' => 1482000],
        'sen_serpent' => ['name' => '祭坛雾蛇', 'hp' => 1420000, 'atk' => 4500, 'exp' => 3380000, 'gold' => 1612000],
        'sen_guard' => ['name' => '祭坛守卫', 'hp' => 1550000, 'atk' => 4650, 'exp' => 3705000, 'gold' => 1768000],
        'sen_avatar' => ['name' => '雾森化身', 'hp' => 2800000, 'atk' => 5200, 'exp' => 9100000, 'gold' => 4290000],
        // 野外传奇BOSS与趣味小怪
        'shadow_claw' => ['name' => '影爪领主', 'hp' => 900000, 'atk' => 3600, 'exp' => 2000000, 'gold' => 950000],
        'brass_bishop' => ['name' => '黄铜主教', 'hp' => 1800000, 'atk' => 4800, 'exp' => 4200000, 'gold' => 2000000],
        'tundra_wanderer' => ['name' => '雪原漫游者', 'hp' => 1500000, 'atk' => 4400, 'exp' => 3500000, 'gold' => 1650000],
        'lucky_goblin' => ['name' => '幸运哥布林', 'hp' => 5000, 'atk' => 5, 'exp' => 500, 'gold' => 5000],
        'golden_slime' => ['name' => '黄金史莱姆', 'hp' => 80000, 'atk' => 200, 'exp' => 50000, 'gold' => 200000],
        'mimic_chest' => ['name' => '宝箱怪', 'hp' => 300000, 'atk' => 1500, 'exp' => 400000, 'gold' => 500000],        // 新主城野外（t7）
        'frostborn_hunter' => ['slot' => 'weapon', 'base' => '霜裔猎弓'],
        'avalanche_titan' => ['slot' => 'necklace', 'base' => '雪崩之心', 'rate' => 0.2, 'q' => [0, 0, 30, 50, 20], 'legendary' => ['base' => '雪崩之心·永冻', 'extra' => ['k' => 'hp', 'v' => 3000.0]]],
        'sulfur_sprite' => ['slot' => 'ring', 'base' => '硫磺指环'],
        'obsidian_colossus' => ['slot' => 'ring', 'base' => '黑曜石之眼', 'rate' => 0.2, 'q' => [0, 0, 30, 50, 20], 'legendary' => ['base' => '黑曜石之眼·灭世', 'extra' => ['k' => 'critdmg', 'v' => 10.0]]],
        'cinder_crawler' => ['slot' => 'gloves', 'base' => '灰烬爬行手套'],
        'cinder_stalker' => ['slot' => 'shoes', 'base' => '灰烬潜伏靴'],
        'cinder_warden' => ['slot' => 'body', 'base' => '灰烬守望甲'],
        'cinder_horror' => ['slot' => 'head', 'base' => '灰烬恐惧面'],
        'sen_thorn' => ['slot' => 'belt', 'base' => '荆棘·束腰', 'rate' => 0.06, 'q' => [0, 30, 50, 20, 0]],
        'ash_titan' => ['slot' => 'weapon', 'base' => '灰烬泰坦之握', 'rate' => 0.3, 'q' => [0, 0, 20, 50, 30], 'legendary' => ['base' => '泰坦之握·灭世', 'extra' => ['k' => 'atk_pct', 'v' => 5.0]]],
        'frostborn_hunter' => ['name' => '霜裔猎手', 'hp' => 400000, 'atk' => 2900, 'exp' => 806000, 'gold' => 390000],
        'avalanche_titan' => ['name' => '雪崩巨人', 'hp' => 1900000, 'atk' => 5000, 'exp' => 4500000, 'gold' => 2100000],
        'sulfur_sprite' => ['name' => '硫磺精', 'hp' => 1100000, 'atk' => 4900, 'exp' => 2000000, 'gold' => 960000],
        'obsidian_colossus' => ['name' => '黑曜石巨像', 'hp' => 2400000, 'atk' => 5600, 'exp' => 5500000, 'gold' => 2600000],        // 灰烬深渊副本（600~680级，2觉群攻书掉落）
        'cinder_crawler' => ['name' => '灰烬爬行者', 'hp' => 1900000, 'atk' => 6200, 'exp' => 3600000, 'gold' => 1720000],
        'cinder_stalker' => ['name' => '灰烬潜伏者', 'hp' => 2100000, 'atk' => 6450, 'exp' => 4000000, 'gold' => 1900000],
        'cinder_warden' => ['name' => '灰烬守望者', 'hp' => 2300000, 'atk' => 6700, 'exp' => 4400000, 'gold' => 2100000],
        'cinder_horror' => ['name' => '灰烬恐惧', 'hp' => 2500000, 'atk' => 6950, 'exp' => 4800000, 'gold' => 2300000],
        'ash_titan' => ['name' => '深渊灰烬泰坦', 'hp' => 4200000, 'atk' => 8200, 'exp' => 14300000, 'gold' => 6760000],        // 虚空裂隙副本（2觉，600~680级）
        'vx_spawn' => ['name' => '裂隙孳生体', 'hp' => 1600000, 'atk' => 4600, 'exp' => 3000000, 'gold' => 1440000],
        'vx_stalker' => ['name' => '虚空潜伏者', 'hp' => 1750000, 'atk' => 4800, 'exp' => 3300000, 'gold' => 1580000],
        'vx_mage' => ['name' => '裂隙低语者', 'hp' => 1900000, 'atk' => 5000, 'exp' => 3600000, 'gold' => 1720000],
        'vx_knight' => ['name' => '虚空骑士', 'hp' => 2050000, 'atk' => 5200, 'exp' => 3900000, 'gold' => 1860000],
        'vx_herald' => ['name' => '裂隙传令官', 'hp' => 2200000, 'atk' => 5400, 'exp' => 4200000, 'gold' => 2000000],
        'vx_tyrant' => ['name' => '虚空暴君', 'hp' => 3000000, 'atk' => 6000, 'exp' => 9000000, 'gold' => 4300000],        // 世界BOSS（999级，世界BOSS系统专用，野外不可遇敌）
        'doyle' => ['name' => '烬焰领主·火龙多伊尔', 'hp' => 80000000, 'atk' => 9000, 'exp' => 0, 'gold' => 0],
        'gr_hyena' => ['name' => '灰烬鬣狗', 'hp' => 2200000, 'atk' => 5600, 'exp' => 4200000, 'gold' => 2000000],
        'gr_vulture' => ['name' => '食腐秃鹫', 'hp' => 2250000, 'atk' => 5700, 'exp' => 4300000, 'gold' => 2050000],
        'gr_worm' => ['name' => '流沙蠕虫', 'hp' => 2300000, 'atk' => 5800, 'exp' => 4400000, 'gold' => 2100000],
        'gr_raider' => ['name' => '荒原掠夺者', 'hp' => 2350000, 'atk' => 5900, 'exp' => 4500000, 'gold' => 2150000],
        'gr_storm' => ['name' => '沙暴元素', 'hp' => 2400000, 'atk' => 6000, 'exp' => 4600000, 'gold' => 2200000],
        'gr_bat' => ['name' => '洞穴巨蝠', 'hp' => 2500000, 'atk' => 6100, 'exp' => 4800000, 'gold' => 2300000],
        'gr_guard' => ['name' => '巢穴守卫', 'hp' => 2550000, 'atk' => 6200, 'exp' => 4900000, 'gold' => 2350000],
        'gr_whelp' => ['name' => '咕噜崽子', 'hp' => 2600000, 'atk' => 6300, 'exp' => 5000000, 'gold' => 2400000],
        'gr_wraith' => ['name' => '骨堆怨灵', 'hp' => 2650000, 'atk' => 6400, 'exp' => 5100000, 'gold' => 2450000],
        'gr_spider' => ['name' => '毒涎蜘蛛', 'hp' => 2700000, 'atk' => 6500, 'exp' => 5200000, 'gold' => 2500000],
        'gr_retainer' => ['name' => '暴食亲卫', 'hp' => 2800000, 'atk' => 6600, 'exp' => 5400000, 'gold' => 2600000],
        'gr_shaman' => ['name' => '咕噜萨满', 'hp' => 2850000, 'atk' => 6700, 'exp' => 5500000, 'gold' => 2650000],
        'gr_fang' => ['name' => '断牙斗士', 'hp' => 2900000, 'atk' => 6800, 'exp' => 5600000, 'gold' => 2700000],
        'gr_idol' => ['name' => '王座傀儡', 'hp' => 2950000, 'atk' => 6900, 'exp' => 5700000, 'gold' => 2750000],
        'gr_guru' => ['name' => '食人魔·咕噜', 'hp' => 3500000, 'atk' => 7200, 'exp' => 7000000, 'gold' => 3400000],
        'gr_brood' => ['name' => '蠕虫母巢', 'hp' => 3100000, 'atk' => 7000, 'exp' => 6000000, 'gold' => 2900000],
        'gr_shard' => ['name' => '碎片灵体', 'hp' => 3150000, 'atk' => 7100, 'exp' => 6100000, 'gold' => 2950000],
        'gr_shadow' => ['name' => '咕噜之影', 'hp' => 3200000, 'atk' => 7200, 'exp' => 6200000, 'gold' => 3000000],
        'gr_nightmare' => ['name' => '荒原梦魇', 'hp' => 3250000, 'atk' => 7300, 'exp' => 6300000, 'gold' => 3050000],
        'gr_ancient' => ['name' => '远古暴食者', 'hp' => 3400000, 'atk' => 7500, 'exp' => 6600000, 'gold' => 3200000],
        // 岩狱副本（700~900级）
        'ry_crawler' => ['name' => '岩狱爬行者', 'hp' => 3800000, 'atk' => 8000, 'exp' => 7800000, 'gold' => 3700000],
        'ry_guard' => ['name' => '岩狱守卫', 'hp' => 4200000, 'atk' => 8500, 'exp' => 8600000, 'gold' => 4100000],
        'ry_shaper' => ['name' => '岩狱塑岩者', 'hp' => 4600000, 'atk' => 9000, 'exp' => 9500000, 'gold' => 4500000],
        'ry_warder' => ['name' => '岩狱狱卒', 'hp' => 5000000, 'atk' => 9500, 'exp' => 10400000, 'gold' => 4900000],
        'ry_titan' => ['name' => '岩狱泰坦·土龙岳柱', 'hp' => 9000000, 'atk' => 11000, 'exp' => 20000000, 'gold' => 9500000],
    ] + dragon_monsters() + world_expansion_monsters() + region_dungeon_monsters();
}

function items(): array
{
    return [
        'rusty' => ['name' => '生锈铁剑', 'slot' => 'weapon', 'atk' => 3, 'def' => 0, 'gold' => 300],
        'bow' => ['name' => '猎弓', 'slot' => 'weapon', 'atk' => 4, 'def' => 0, 'gold' => 450],
        'shortbow' => ['name' => '硬木短弓', 'slot' => 'weapon', 'atk' => 6, 'def' => 0, 'gold' => 900],
        'quiver' => ['name' => '兽皮箭袋', 'slot' => 'offhand', 'atk' => 2, 'def' => 0, 'gold' => 400],
        'quiver2' => ['name' => '精铁箭袋', 'slot' => 'offhand', 'atk' => 4, 'def' => 1, 'gold' => 1500],
        'quiver3' => ['name' => '龙骨箭袋', 'slot' => 'offhand', 'atk' => 7, 'def' => 2, 'gold' => 6000],
        'staff' => ['name' => '桦木法杖', 'slot' => 'weapon', 'atk' => 5, 'def' => 0, 'gold' => 500],
        'mace' => ['name' => '白木槌', 'slot' => 'weapon', 'atk' => 3, 'def' => 0, 'gold' => 350],
        'sickle' => ['name' => '草药镰', 'slot' => 'weapon', 'atk' => 2, 'def' => 0, 'gold' => 120],
        'boneclub' => ['name' => '大骨棒', 'slot' => 'weapon', 'atk' => 5, 'def' => 0, 'gold' => 800],
        'spike' => ['name' => '铁刺', 'slot' => 'weapon', 'atk' => 6, 'def' => 0, 'gold' => 1200],
        'brand' => ['name' => '罪纹短剑', 'slot' => 'weapon', 'atk' => 12, 'def' => 0, 'gold' => 5000],
        'cloth' => ['name' => '破布甲', 'slot' => 'armor', 'atk' => 0, 'def' => 2, 'gold' => 200],
        'bone' => ['name' => '骨甲', 'slot' => 'armor', 'atk' => 0, 'def' => 5, 'gold' => 1500],
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
    $g['egg_unknown'] = ['name' => '未知宠物蛋', 'price' => 100, 'unit' => 1];
    $g['pet_food'] = ['name' => '宠物粮食', 'price' => 10, 'unit' => 1];
    $g['pet_exp_potion'] = ['name' => '宠物经验药', 'price' => 30, 'unit' => 1];

    // 下架：传奇宠物不直购（魔女之茧）
    $g['dragon_box'] = ['name' => '龙鳞宝箱', 'price' => 200, 'unit' => 1];
    $g['autosell_time'] = ['name' => '自动售卖', 'price' => 20, 'unit' => 3600];
    $g['exp_card10x'] = ['name' => '10倍经验卡·天', 'price' => 10, 'unit' => 1];
    $g['exp_card1000x'] = ['name' => '1000倍经验卡·天', 'price' => 10, 'unit' => 1];
    $g['tent'] = ['name' => '随身帐篷', 'price' => 100, 'unit' => 1];
    $g['exp_card100'] = ['name' => '升级卡100型', 'price' => 100, 'unit' => 1];
    $g['reset_potion'] = ['name' => '属性洗点药', 'price' => 300, 'unit' => 1];
    $g['bag_ext5'] = ['name' => '5格背包扩充', 'price' => 200, 'unit' => 1];
    $g['bag_ext10'] = ['name' => '10格背包扩充', 'price' => 500, 'unit' => 1];
    foreach (['light', 'dark', 'fire', 'wind', 'ice', 'thunder'] as $el) {
        $g['el_' . $el . '_stone'] = ['name' => element_name($el) . '属性石', 'price' => 20, 'unit' => 1];
    }
    $g['dummy_time'] = ['name' => '陪练人偶', 'price' => 50, 'unit' => 3600];
    $g['offline_time'] = ['name' => '人偶离线升级模块', 'price' => 50, 'unit' => 3600];
    $g['offline_mod'] = ['name' => '人偶离线升级模块', 'price' => 50, 'unit' => 3600];
    $g['rename_card'] = ['name' => '改名卡', 'price' => 100, 'unit' => 1];
    // 下架：$g['title_iron'] = ['name' => '称号·黑铁冒险者', 'price' => 50, 'unit' => 1];
    // 下架：$g['title_silver'] = ['name' => '称号·秘银冒险者', 'price' => 150, 'unit' => 1];
    // 下架：$g['title_gold'] = ['name' => '称号·精金传说', 'price' => 300, 'unit' => 1];
    // 下架：$g['tent'] = ['name' => '随身帐篷', 'price' => 100, 'unit' => 1];
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
    // 离线结算只吃独立离线时长（offline_time），不吃在线人偶时间
    $pool = (int) ($mats['offline_time'] ?? 0);
    if (empty($mats['offline_time']) && !empty($mats['dummy_time'])) {
        // 老数据迁移：把剩余人偶时间的一半划给离线时长
        $move = intdiv((int) $mats['dummy_time'], 2);
        add_mat($uid, 'offline_time', $move);
        add_mat($uid, 'dummy_time', -$move);
        $pool = (int) (mats_of($uid)['offline_time'] ?? 0);
    }
    if ($pool <= 0) {
        flash_set('离线' . dummy_fmt($gap) . '，离线模块开着但离线时长没了，去商城续费离线模块（独立计时）。');
        return;
    }
    $here = loc((string) ($u['loc'] ?? ''));
    $mlist = $here['monsters'] ?? [];
    if ($mlist === []) {
        flash_set('离线' . dummy_fmt($gap) . '，你在安全区，人偶没开工。');
        return;
    }
    $allm = monsters();
    $maxFights = min(120, intdiv($gap, 15), intdiv($pool, 15));
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
    $emptyRounds = 0;
    $onlyBoss = count($mlist) === 1 && $mlist[0] === boss_of_map((string) ($u['loc'] ?? ''));
    $maxFights = min($onlyBoss ? 2000 : 500, intdiv($gap, 15), intdiv($pool, 15));
    if ($maxFights <= 0) {
        flash_set('离线' . dummy_fmt($gap) . '，人偶剩' . dummy_fmt($pool) . '不够打一场，去续费。');
        return;
    }
    for ($f = 0; $f < $maxFights; $f++) {
        $mid = $mlist[array_rand($mlist)];
        $m = $allm[$mid];
        if ($mid === 'echo_rayne' && (int) ($u['quest'] ?? 0) > 14) {
            $m = ['name' => '深渊回响·雷恩', 'hp' => 2000, 'atk' => 85, 'exp' => 2200, 'gold' => 1000];
        }
        $m = scale_monster($m, $mid, 0);
        $isBossOff = ($mid === boss_of_map((string) ($u['loc'] ?? '')));
        // 离线人偶同样打幻影：不消耗地图存货（否则一次离线结算能吃光整张图），只扣人偶时间
        $tried++;
        $numOff = $isBossOff ? 1 : (int) (mats_of((int) ($u['id'] ?? 0))['dummy_num'] ?? 6);
        if (!in_array($numOff, [1, 3, 6], true)) { $numOff = 6; }
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
            spawn_tick((string) ($u['loc'] ?? ''));
            $dead = true;
            break;
        }
        $wins++;
    }
    unset($_SESSION['battle']);
    spawn_tick((string) ($u['loc'] ?? ''));
    add_mat($uid, 'offline_time', -$tried * 30);
    mat_set($uid, 'seen_last', $now - max(0, $gap - $tried * 30));
    user_save($u);
    $eq1 = (int) db()->query('SELECT COUNT(*) FROM equips WHERE uid=' . $uid)->fetchColumn();
    $txt = '离线' . dummy_fmt($gap) . '，人偶带打' . $tried . '场胜' . $wins . '场，金+' . fmt_money(max(0, (int) $u['gold'] - $gold0)) . '，装备+' . max(0, $eq1 - $eq0) . '件';
    if ($wins <= 0 && $emptyRounds > 0 && !$onlyBoss) {
        $txt .= '（图里没怪了，人偶在空转——换个怪多的图挂机）';
    }
    if ((int) $u['lv'] > $lv0) {
        $txt .= '，连升' . ((int) $u['lv'] - $lv0) . '级';
    }
    if ($dead) {
        mat_del($uid, 'dummy_on');
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
    // 升级消耗仓库金（铜）：1升2要100金，之后每级+100金
    return $lv >= 10 ? 999999999 : 1000000 * $lv;
}

function guild_role_name(string $role): string
{
    return ['leader' => '会长', 'vice' => '副会长', 'officer' => '管理员', 'member' => '成员'][$role] ?? '成员';
}

function guild_can_invite(array $g): bool
{
    return in_array($g['role'] ?? '', ['leader', 'vice', 'officer'], true);
}

function guild_can_take_warehouse(array $g): bool
{
    return in_array($g['role'] ?? '', ['leader', 'vice'], true);
}

function is_tradable_mat(string $mid): bool
{
    // 公会仓库只收当前版本锻造材料：旧版掉落/杂物一律不可存，杜绝刷贡献和仓库垃圾
    return is_craft_mat($mid);
}

function guild_warehouse_cap(int $level): int
{
    return 20 + (int) $level * 10;
}

function war_banner(?int $uid = null): string
{
    $st = db()->query('SELECT a_gid, b_gid, ends_at FROM wars WHERE status="open" ORDER BY id DESC LIMIT 1');
    $w = $st->fetch();
    if (!$w) {
        return '';
    }
    if ($uid !== null) {
        $g = my_guild((int) $uid);
        $gid = (int) ($g['id'] ?? 0);
        if ($gid !== (int) $w['a_gid'] && $gid !== (int) $w['b_gid']) {
            return '';
        }
    }
    $ga = db()->query('SELECT name FROM guilds WHERE id=' . (int) $w['a_gid'])->fetchColumn();
    $gb = db()->query('SELECT name FROM guilds WHERE id=' . (int) $w['b_gid'])->fetchColumn();
    return '【公会战】' . $ga . ' VS ' . $gb . '，剩' . dummy_fmt(max(0, (int) $w['ends_at'] - time())) . '！去荒芜战场找军需官参战！';
}

function peak_arenas(): array
{
    return [
        'peak1' => ['min' => 100, 'max' => 300, 'pills' => 1],
        'peak2' => ['min' => 301, 'max' => 600, 'pills' => 2],
        'peak3' => ['min' => 601, 'max' => 999, 'pills' => 3],
    ];
}

function peak_arena_of(string $loc): string
{
    return isset(peak_arenas()[$loc]) ? $loc : '';
}

function peak_day(): string
{
    return date('Y-m-d');
}

function peak_open(): bool
{
    $t = time();
    $start = strtotime(date('Y-m-d') . ' 22:30:00');
    $end = strtotime(date('Y-m-d') . ' 23:00:00');
    return $t >= $start && $t < $end;
}

function peak_banner(): string
{
    if (!peak_open()) {
        return '';
    }
    $left = strtotime(date('Y-m-d') . ' 23:00:00') - time();
    return '【巅峰之战】进行中！剩' . dummy_fmt(max(0, $left)) . '，去灰雾村广场找活动专员参加！';
}

function peak_arena_for_level(int $lv): string
{
    foreach (peak_arenas() as $arena => $cfg) {
        if ($lv >= $cfg['min'] && $lv <= $cfg['max']) {
            return $arena;
        }
    }
    return '';
}

function peak_world_announce(string $text): void
{
    db()->prepare('INSERT INTO chat_msgs (uid, username, channel, target, text, created_at) VALUES (0, "战报", "world", 0, ?, ?)')->execute([$text, time()]);
}

function peak_enter(int $uid, string $arena): string
{
    $u = user_by_id((int) $uid);
    if (!$u) {
        return '角色不存在。';
    }
    if (!peak_open()) {
        return '巅峰之战每晚22:30~23:00开启，现在还没开。';
    }
    if (time() >= strtotime(date('Y-m-d') . ' 22:55:00')) {
        return '22:55后封场了，不再放新人进，明天早点来。';
    }
    if (!isset(peak_arenas()[$arena])) {
        return '没有这个战场。';
    }
    if (peak_arena_for_level((int) $u['lv']) !== $arena) {
        $cfg = peak_arenas()[$arena];
        return '这个战场只收' . $cfg['min'] . '~' . $cfg['max'] . '级，你是' . (int) $u['lv'] . '级。';
    }
    if ((int) $u['hp'] <= 0) {
        return '你倒在地上，先起来再说。';
    }
    $st = db()->prepare('SELECT alive FROM peak_join WHERE uid=? AND day=?');
    $st->execute([(int) $uid, peak_day()]);
    $row = $st->fetch();
    if ($row && (int) $row['alive'] === 0) {
        return '你已丧失今日资格（死亡/离开过战场），明天再来。';
    }
    if (!$row) {
        db()->prepare('INSERT INTO peak_join (uid, day, arena, alive) VALUES (?, ?, ?, 1)')->execute([(int) $uid, peak_day(), $arena]);
    }
    $u['loc'] = $arena;
    $u['hp'] = (int) $u['maxhp'];
    $u['mp'] = (int) ($u['maxmp'] ?? 0);
    user_save($u);
    mat_set((int) $uid, 'peak_force', time());
    return '进入【' . loc($arena)['name'] . '】！伤势尽复，活到最后就是巅峰。';
}

function peak_forfeit(int $uid): void
{
    db()->prepare('UPDATE peak_join SET alive=0 WHERE uid=? AND day=?')->execute([(int) $uid, peak_day()]);
}

function peak_alive_in(string $arena, string $zone = 'z1'): array
{
    $day = peak_day();
    $st = db()->prepare("SELECT u.id, u.username, u.lv, u.hp FROM users u JOIN peak_join j ON j.uid=u.id AND j.day=? AND j.arena=? AND j.alive=1 WHERE u.loc=? AND u.zone=? AND u.hp>0 ORDER BY u.lv DESC LIMIT 30");
    $st->execute([$day, $arena, $arena, $zone]);
    return $st->fetchAll();
}

// 巅峰战场·缩圈「圈内/圈外」地图机制
//  - 安全区随时间收窄（safe_ratio 从 1.0 逐步缩到 0）。
//  - 站在「圈内」安全不掉血，但刷不到补给；
//  - 冲进「圈外」才可能抢到补给/增益，但每分钟受重伤，越晚占比越大；
//  - 终局阶段安全区塌缩为 0，圈内也开始掉血——逼所有人出来决一死战。
// 返回：ok/name/pct(圈外每分钟掉血比例)/safe_ratio(安全区占比)/remain(到23:00剩余秒)/final(是否终局)
function peak_storm_phase(?int $now = null): array
{
    $now = $now ?? time();
    $start = strtotime(date('Y-m-d') . ' 22:45:00');
    $end   = strtotime(date('Y-m-d') . ' 23:00:00');
    if ($now < $start) {
        return ['ok' => false, 'name' => '', 'pct' => 0.0, 'safe_ratio' => 1.0, 'remain' => max(0, $end - $now), 'final' => false];
    }
    $frac = min(1.0, max(0.0, ($now - $start) / max(1, ($end - $start)))); // 0~1
    $names = ['警戒', '紧迫', '危殆', '终局'];
    $idx = min(3, (int) floor($frac * 4));
    $final = $idx >= 3;
    // 圈外每分钟掉 4%~16% 最大生命
    $pct = 0.04 + $frac * 0.12;
    // 安全区占比：1.0 → 0（终局塌缩）
    $safeRatio = max(0.0, 1.0 - $frac * 1.15);
    return [
        'ok' => true,
        'name' => '缩圈·' . $names[$idx],
        'pct' => $pct,
        'safe_ratio' => $safeRatio,
        'remain' => max(0, $end - $now),
        'final' => $final,
    ];
}

// 玩家在圈内/圈外。默认圈内(1=安全, 2=圈外)。mats 存 peak_zone（用2表示圈外，因为0会被 mat_set 当删除）。
function peak_zone_state(int $uid): int
{
    $mats = mats_of($uid);
    return array_key_exists('peak_zone', $mats) ? (int) $mats['peak_zone'] : 1;
}

function peak_set_zone(int $uid, int $zone): void
{
    // 1=圈内，2=圈外（避免用 0，否则 mat_set 会删行）
    mat_set($uid, 'peak_zone', $zone === 2 ? 2 : 1);
}

// 每整分钟结算：只有「圈外」掉血、也只有「圈外」可能捡到补给；终局圈内也掉。
function peak_storm_apply(array &$u): string
{
    if (peak_arena_of((string) ($u['loc'] ?? '')) === '' || !peak_open()) {
        return '';
    }
    $ph = peak_storm_phase();
    if (!$ph['ok']) {
        return '';
    }
    $uid = (int) $u['id'];
    $minuteKey = intdiv(time(), 60);
    $mats = mats_of($uid);
    if ((int) ($mats['peak_storm_t'] ?? 0) === $minuteKey) {
        return '';
    }
    mat_set($uid, 'peak_storm_t', $minuteKey);

    $inSafe = peak_zone_state($uid) === 1;
    $exposed = !$inSafe || $ph['final']; // 圈外，或终局圈内也塌缩
    $msg = '【' . $ph['name'] . '】';
    if ($inSafe && !$ph['final']) {
        $msg .= '你缩在安全区内，暂无损伤，但也捡不到场上的补给。';
        return $msg;
    }
    // 掉血（圈外，或终局）：保底 1 血
    $where = $ph['final'] ? '安全区已经塌缩，无处可躲' : '你在安全区外';
    $dmg = max(1, (int) round((int) $u['maxhp'] * $ph['pct']));
    $new = max(1, (int) $u['hp'] - $dmg);
    $real = max(0, (int) $u['hp'] - $new);
    $u['hp'] = $new;
    sin_gain_array($u, 0);
    user_save($u);
    $msg .= $where . '，乱流灼伤 ' . $real . ' 点（剩 ' . $new . '/' . (int) $u['maxhp'] . '）。';
    if ($new <= 1) {
        $msg .= '你已是强弩之末，随时会被收走。';
    }
    // 只有圈外能捡补给（终局圈内也能）。改为在战场上真实掉落共享补给：谁先点到谁拿。
    if ($exposed && mt_rand(1, 100) <= 45) {
        $keys = ['atk', 'def', 'heal'];
        $k = $keys[array_rand($keys)];
        ground_place_peak_supply((string) ($u['loc'] ?? ''), $k);
        $names = ['atk' => '战力增幅', 'def' => '铁壁补给', 'heal' => '急救包'];
        $msg .= ' 战场废墟里刷出了一个【' . $names[$k] . '】，谁先点到归谁——去捡！';
    }
    return $msg;
}

// 顶部红色缩圈倒计时条（HTML）。home / 战场页用。
function peak_storm_bar(int $uid): string
{
    if (peak_arena_of((string) (user_by_id($uid)['loc'] ?? '')) === '' || !peak_open()) {
        return '';
    }
    $ph = peak_storm_phase();
    if (!$ph['ok']) {
        $start = strtotime(date('Y-m-d') . ' 22:45:00');
        $left = max(0, $start - time());
        if ($left <= 0 || $left > 1800) {
            return '';
        }
        return '<div style="background:#221;border:1px solid #553;border-radius:8px;padding:6px 8px;margin:6px 0;color:#fa6">'
            . '缩圈预警：' . gmdate('i:s', $left) . ' 后安全区开始收窄，冲圈外抢补给，别站桩。</div>';
    }
    $remain = (int) $ph['remain'];
    $mmss = sprintf('%02d:%02d', intdiv($remain, 60), $remain % 60);
    $safePct = (int) round($ph['safe_ratio'] * 100);
    $inSafe = peak_zone_state($uid) === 1;
    $whereTag = $inSafe
        ? ($ph['final'] ? '<span style="color:#f55">圈内已塌缩！</span>' : '<span style="color:#6f6">你在圈内·安全</span>')
        : '<span style="color:#f55">你在圈外·每分钟重伤+可抢补给</span>';
    $fill = max(0, min(100, $remain / 900 * 100)); // 15分钟窗口
    return '<div style="background:#2a0f0f;border:1px solid #a33;border-radius:8px;padding:6px 8px;margin:6px 0">'
        . '<div style="color:#ff6b6b;font-weight:bold">' . h($ph['name']) . '　距决赛圈塌缩 ' . $mmss . '</div>'
        . '<div style="background:#1a0808;height:10px;border-radius:5px;overflow:hidden;margin:4px 0">'
        . '<div style="width:' . $fill . '%;height:100%;background:linear-gradient(90deg,#a00,#ff4d4d)"></div></div>'
        . '<div style="color:#f99">安全区剩余 ' . $safePct . '%　' . $whereTag . '</div></div>';
}

// 把玩家在缩圈里捡到的临时增益，转成一次对决的攻击/减伤加成。用完即清。
function peak_buff_take(int $uid): array
{
    $mats = mats_of($uid);
    $out = ['atk_pct' => 0.0, 'dr_pct' => 0.0, 'used' => []];
    if (!empty($mats['peak_buff_atk'])) { $out['atk_pct'] = 15.0; $out['used'][] = 'peak_buff_atk'; }
    if (!empty($mats['peak_buff_def'])) { $out['dr_pct'] = 15.0; $out['used'][] = 'peak_buff_def'; }
    return $out;
}

function peak_buff_consume(int $uid): void
{
    foreach (['peak_buff_atk', 'peak_buff_def', 'peak_buff_heal'] as $k) {
        $mats = mats_of($uid);
        if (!empty($mats[$k])) { mat_del($uid, $k); }
    }
}

// 战场内每分钟强制配对：闲着的人会被拖入决斗
function peak_tick(array &$u): string
{
    $arena = peak_arena_of((string) ($u['loc'] ?? ''));
    if ($arena === '' || !peak_open()) {
        return '';
    }
    $uid = (int) $u['id'];
    $msg = '';
    // 缩圈结算（每整分钟一次，未开缩圈则无）
    $storm = peak_storm_apply($u);
    if ($storm !== '') {
        $msg .= $storm . '<br>';
    }
    // 对方发起的决斗先送达：自动接战
    $foe = (int) (mats_of($uid)['peak_duel_from'] ?? 0);
    if ($foe > 0) {
        mat_del($uid, 'peak_duel_from');
        $t = user_by_id($foe);
        if ($t && peak_arena_of((string) ($t['loc'] ?? '')) === $arena && (int) $t['hp'] > 0) {
            $_SESSION['peak'] = ['tuid' => $foe];
            return '【' . $t['username'] . '】把你拖入了巅峰决斗！<a href="fight.php?a=peakfight">迎战</a>';
        }
    }
    $last = (int) (mats_of($uid)['peak_force'] ?? 0);
    if (time() - $last < 60) {
        return $msg;
    }
    if (!empty($_SESSION['battle']) || !empty($_SESSION['pvp']) || !empty($_SESSION['peak'])) {
        return $msg;
    }
    mat_set($uid, 'peak_force', time());
    $cands = array_values(array_filter(peak_alive_in($arena, (string) ($u['zone'] ?? 'z1')), fn($p) => (int) $p['id'] !== $uid));
    if ($cands === []) {
        return $msg;
    }
    $pick = $cands[array_rand($cands)];
    $_SESSION['peak'] = ['tuid' => (int) $pick['id']];
    mat_set((int) $pick['id'], 'peak_duel_from', $uid);
    return '战鼓响起！你被配对【' . $pick['username'] . '】(' . (int) $pick['lv'] . '级)！<a href="fight.php?a=peakfight">开打</a>';
}

function peak_settle(): void
{
    if (time() < strtotime(date('Y-m-d') . ' 23:00:00')) {
        return;
    }
    $day = peak_day();
    foreach (peak_arenas() as $arena => $cfg) {
        foreach (zones() as $zid => $z) {
            $st = db()->prepare('SELECT winner_uid FROM peak_result WHERE day=? AND arena=? AND zone=?');
            $st->execute([$day, $arena, $zid]);
            if ($st->fetch()) {
                continue;
            }
            $left = peak_alive_in($arena, $zid);
            if (count($left) === 1) {
                $w = $left[0];
                $n = (int) $cfg['pills'];
                add_mat((int) $w['id'], 'dragon_pill', $n);
                send_mail((int) $w['id'], '巅峰裁决', 'peak', '巅峰之战冠军：真龙丹x' . $n . '+清罪丹x1', '你是【' . loc($arena)['name'] . '】最后的站立者！附件是真龙丹（使用+3自由属性点/枚）+清罪丹x1，请查收。', [['t' => 'mat', 'id' => 'qingzui_dan', 'n' => 1]]);
                peak_world_announce('【巅峰之战】' . loc($arena)['name'] . '胜者是【' . $w['username'] . '】！真龙丹x' . $n . '已发放！');
                db()->prepare('INSERT INTO peak_result (day, arena, zone, winner_uid, winner_name, created_at) VALUES (?, ?, ?, ?, ?, ?)')->execute([$day, $arena, $zid, (int) $w['id'], (string) $w['username'], time()]);
                // 散场：胜者传回广场
                $pu = user_by_id((int) $w['id']);
                if ($pu && peak_arena_of((string) ($pu['loc'] ?? '')) === $arena) {
                    $pu['loc'] = 'square';
                    user_save($pu);
                }
            } elseif (count($left) === 0) {
                // 场里没人了才流局
                db()->prepare('INSERT INTO peak_result (day, arena, zone, winner_uid, winner_name, created_at) VALUES (?, ?, ?, 0, "", ?)')->execute([$day, $arena, $zid, time()]);
            }
            // 还剩多人：不等了也不散场，继续等到只剩1人（不记result，下次页面访问再结算）
        }
    }
}

function use_dragon_pill(int $uid): string
{
    $mats = mats_of((int) $uid);
    if (empty($mats['dragon_pill'])) {
        return '你没有真龙丹。';
    }
    $u = user_by_id((int) $uid);
    if (!$u) {
        return '角色不存在。';
    }
    add_mat((int) $uid, 'dragon_pill', -1);
    $u['s_pts'] = (int) ($u['s_pts'] ?? 0) + 3;
    user_save($u);
    return '服下真龙丹，自由属性点+3！';
}

function guild_warehouse_count(int $gid): int
{
    return (int) (db()->query('SELECT COUNT(*) FROM guild_warehouse WHERE gid=' . (int) $gid)->fetchColumn() ?: 0);
}

function guild_exp_bonus(int $level): int
{
    return (int) $level;
}

function guild_level_up(int $gid): string
{
    $st = db()->prepare('SELECT * FROM guilds WHERE id=?');
    $st->execute([(int) $gid]);
    $g = $st->fetch();
    if (!$g) {
        return '没有这个公会。';
    }
    $lv = (int) $g['level'];
    if ($lv >= 10) {
        return '公会已满级。';
    }
    $need = guild_level_need($lv);
    $needGold = intdiv($need, 10000);
    $gold = (int) ($g['gold'] ?? 0);
    if ($gold < $need) {
        return '仓库金不够：有' . fmt_money($gold) . '，升级要' . fmt_money($need) . '。';
    }
    $tot = (int) (db()->query('SELECT COALESCE(SUM(contrib),0) FROM guild_members WHERE gid=' . (int) $gid)->fetchColumn() ?: 0);
    if ($tot < $needGold) {
        return '全员贡献不够：共' . $tot . '金，要' . $needGold . '金贡献。';
    }
    // 只扣仓库金，不扣成员贡献：贡献是付出记录，给会长看的
    db()->prepare('UPDATE guilds SET gold=gold-?, level=level+1 WHERE id=?')->execute([$need, (int) $gid]);
    return '消耗仓库' . fmt_money($need) . '，公会升到' . ($lv + 1) . '级！全员打怪经验+' . ($lv + 1) . '%，仓库上限' . guild_warehouse_cap($lv + 1) . '格。';
}

function is_tradable_equip(array $eq): bool
{
    // 公会仓库只收史诗及以上；穿着的调用方已过滤
    return (int) ($eq['quality'] ?? 0) >= 3;
}

function guild_wh_log(int $gid, int $uid, string $username, string $action, string $detail): void
{
    db()->prepare('INSERT INTO guild_warehouse_log (gid, uid, username, action, detail, created_at) VALUES (?, ?, ?, ?, ?, ?)')->execute([(int) $gid, (int) $uid, (string) $username, (string) $action, mb_substr((string) $detail, 0, 80), time()]);
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
    db()->prepare('INSERT INTO guilds (name, leader_uid, zone, level, exp, created_at) VALUES (?, ?, ?, 1, 0, ?)')->execute([$name, $uid, (string) ($u['zone'] ?? 'z1'), time()]);
    $gid = (int) db()->lastInsertId();
    db()->prepare('INSERT INTO guild_members (uid, gid, role, contrib, joined_at) VALUES (?, ?, "leader", 0, ?)')->execute([$uid, $gid, time()]);
    return '公会【' . $name . '】成立！你是会长。';
}

function guild_rank(string $zone = ''): array
{
    if ($zone !== '') {
        $st = db()->prepare('SELECT g.*, (SELECT COUNT(*) FROM guild_members m WHERE m.gid=g.id) AS num FROM guilds g WHERE g.zone=? ORDER BY g.level DESC, g.exp DESC, num DESC LIMIT 20');
        $st->execute([$zone]);
        return $st->fetchAll();
    }
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
    if (!war_window_open()) {
        return '公会战只在周六、周日20:00~21:00开放宣战。';
    }
    $g = my_guild($uid);
    if (!$g || ($g['role'] ?? '') !== 'leader') {
        return '只有会长能宣战。';
    }
    $myGid = (int) $g['id'];
    if ($targetGid === $myGid) {
        return '不能打自己。';
    }
    $st = db()->prepare('SELECT zone FROM guilds WHERE id=?');
    $st->execute([$targetGid]);
    $tg = $st->fetch();
    if (!$tg) {
        return '没有这个公会。';
    }
    if (open_war($myGid) || open_war($targetGid)) {
        return '其中一方已经在打了，打完再来。';
    }
    $u = user_by_id($uid);
    if (($tg['zone'] ?? 'z1') !== (string) ($u['zone'] ?? 'z1')) {
        return '跨大区不能宣战。';
    }
    if ((int) $u['gold'] < 5000) {
        return '宣战要50银。';
    }
    $u['gold'] = (int) $u['gold'] - 5000;
    user_save($u);
    db()->prepare('INSERT INTO wars (a_gid, b_gid, ends_at, status) VALUES (?, ?, ?, "open")')->execute([$myGid, $targetGid, time() + 86400]);
    $ga = db()->query('SELECT name FROM guilds WHERE id=' . $myGid)->fetchColumn();
    $gb = db()->query('SELECT name FROM guilds WHERE id=' . $targetGid)->fetchColumn();
    foreach ([$myGid, $targetGid] as $gg) {
        foreach (db()->query('SELECT uid FROM guild_members WHERE gid=' . (int) $gg)->fetchAll() as $mm) {
            send_mail((int) $mm['uid'], '战场军需官', 'war', '公会战开启', $ga . ' VS ' . $gb . '：24小时刷怪比战功，去荒芜战场找军需官参战！', []);
        }
    }
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
    db()->prepare('INSERT INTO chat_msgs (uid, username, channel, target, zone, text, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)')->execute([$uid, (string) $u['username'], $channel, $target, (string) ($u['zone'] ?? 'z1'), $text, time()]);
    db()->exec('DELETE FROM chat_msgs WHERE id NOT IN (SELECT id FROM chat_msgs ORDER BY id DESC LIMIT 500)');
    return '';
}

function chat_fetch(string $channel, int $target, int $lastId, string $zone = 'z1'): array
{
    // 世界频道按大区分隔，公会/队伍频道按成员归属（建会建队时已锁区）
    if ($channel === 'world') {
        $st = db()->prepare('SELECT id, username, text, created_at FROM chat_msgs WHERE channel=? AND target=? AND zone=? AND id>? ORDER BY id ASC LIMIT 30');
        $st->execute([$channel, $target, $zone, $lastId]);
        return $st->fetchAll();
    }
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
    $st = db()->prepare("SELECT u.id, u.username, u.lv, u.hp, u.maxhp, u.atk, u.def, g.name AS gname FROM users u JOIN guild_members m ON m.uid=u.id JOIN guilds g ON g.id=m.gid WHERE u.loc='warfield' AND u.zone=? AND u.id!=? AND m.gid!=? ORDER BY u.lv DESC LIMIT 20");
    $st->execute([(string) ($u['zone'] ?? 'z1'), (int) $uid, (int) $g['id']]);
    return $st->fetchAll();
}

function boss_of_map(string $loc): string
{
    static $m = ['boss' => 'knight', 'echo_room' => 'echo_rayne', 'rift' => 'abyss_eye', 'baltar' => 'dark_lily', 'dsw_throne' => 'swamp_king', 'abx_throne' => 'abaddon', 'theater' => 'string_puller', 'mx_heart' => 'silk_mother', 'frost_throne' => 'winter_king', 'fr_core' => 'fr_leviathan', 'volcano_heart' => 'ancient_ruin_golem', 'em_core' => 'em_heart', 'pz_core' => 'pz_master', 'sen_core' => 'sen_avatar', 'shadow_den' => 'shadow_claw', 'brass_chapel' => 'brass_bishop', 'wanderer_camp' => 'tundra_wanderer', 'ice_fall' => 'avalanche_titan', 'obsidian_field' => 'obsidian_colossus', 'as_core' => 'ash_titan', 'vx_core' => 'vx_tyrant', 'ry_core' => 'ry_titan', 'god_throne' => 'god_avatar', 'gf_core' => 'god_emperor', 'yz_core' => 'yz_boss'];
    static $wnMerged = false;
    if (!$wnMerged) {
        foreach (world_expansion_boss_map() as $__wk => $__wv) {
            if (!isset($m[$__wk])) {
                $m[$__wk] = $__wv;
            }
        }
        foreach (region_dungeon_boss_map() as $__rk => $__rv) {
            if (!isset($m[$__rk])) {
                $m[$__rk] = $__rv;
            }
        }
        $wnMerged = true;
    }
    return $m[$loc] ?? '';
}

// 每种怪各20只(BOSS除外仍为1只)，每种怪各自60秒刷新
function spawn_cap(string $mid, string $loc): int
{
    return $mid === boss_of_map($loc) ? 1 : 20;
}

function spawn_period(): int
{
    return 60;
}

function spawn_tick(?string $onlyLoc = null, ?string $onlyMid = null): void
{
    if ($onlyLoc === null && $onlyMid === null && (int) ($_SESSION['spawn_tick'] ?? 0) > time() - 30) {
        return;
    }
    if ($onlyLoc === null && $onlyMid === null) {
        $_SESSION['spawn_tick'] = time();
    }
    $now = time();
    foreach (locations() as $loc => $L) {
        if ($onlyLoc !== null && $loc !== $onlyLoc) {
            continue;
        }
        if (empty($L['monsters'])) {
            continue;
        }
        foreach ($L['monsters'] as $mid) {
            if ($onlyMid !== null && $mid !== $onlyMid) {
                continue;
            }
            $st = db()->prepare('SELECT at FROM map_respawn WHERE loc=? AND mid=?');
            $st->execute([$loc, $mid]);
            $at = (int) ($st->fetchColumn() ?: 0);
            if ($at > 0 && $now - $at < spawn_period()) {
                continue;
            }
            $rs = db()->prepare('UPDATE map_respawn SET at=? WHERE loc=? AND mid=?');
            $rs->execute([$now, $loc, $mid]);
            if ($rs->rowCount() === 0) {
                db()->prepare('INSERT OR IGNORE INTO map_respawn (loc, mid, at) VALUES (?, ?, ?)')->execute([$loc, $mid, $now]);
            }
            spawn_fill_one($loc, $mid);
        }
        // 精英：整图最多2只，随机挑一种小怪补1只
        $boss = boss_of_map($loc);
        $trash = array_values(array_filter($L['monsters'], fn($m) => $m !== $boss));
        if ($trash !== [] && (int) db()->query('SELECT COALESCE(SUM(num),0) FROM map_spawns WHERE loc=' . db()->quote($loc) . ' AND elite=1')->fetchColumn() < 2 && mt_rand(1, 100) <= 5) {
            $em = $trash[array_rand($trash)];
            $es = db()->prepare('UPDATE map_spawns SET num=num+1 WHERE loc=? AND mid=? AND elite=1');
            $es->execute([$loc, $em]);
            if ($es->rowCount() === 0) {
                db()->prepare('INSERT INTO map_spawns (loc, mid, elite, num) VALUES (?, ?, 1, 1)')->execute([$loc, $em]);
            }
        }
    }
}

function spawn_fill_one(string $loc, string $mid): void
{
    $cap = spawn_cap($mid, $loc);
    $st = db()->prepare('SELECT num FROM map_spawns WHERE loc=? AND mid=? AND elite=0');
    $st->execute([$loc, $mid]);
    $have = (int) ($st->fetchColumn() ?: 0);
    if ($have >= $cap) {
        return;
    }
    $add = $cap - $have;
    $up = db()->prepare('UPDATE map_spawns SET num=num+? WHERE loc=? AND mid=? AND elite=0');
    $up->execute([$add, $loc, $mid]);
    if ($up->rowCount() === 0) {
        db()->prepare('INSERT OR IGNORE INTO map_spawns (loc, mid, elite, num) VALUES (?, ?, 0, ?)')->execute([$loc, $mid, $cap]);
    }
}

function spawn_fill(string $loc): void
{
    $all = locations();
    $L = $all[$loc] ?? null;
    if (!$L || empty($L['monsters'])) {
        return;
    }
    foreach ($L['monsters'] as $mid) {
        spawn_fill_one($loc, $mid);
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

function spawn_respawn_in(string $loc, string $mid = ''): int
{
    if ($mid !== '') {
        $st = db()->prepare('SELECT at FROM map_respawn WHERE loc=? AND mid=?');
        $st->execute([$loc, $mid]);
        $at = (int) ($st->fetchColumn() ?: 0);
        return max(0, spawn_period() - (time() - $at));
    }
    $st = db()->prepare('SELECT MAX(at) FROM map_respawn WHERE loc=?');
    $st->execute([$loc]);
    $at = (int) ($st->fetchColumn() ?: 0);
    return max(0, spawn_period() - (time() - $at));
}

function horse_names(): array
{
    return ['疾风', '逐影', '烈焰', '奔雷', '踏雪', '流星', '狂沙', '碧蹄', '夜魈', '金鬃'];
}

function horse_zone_id(string $zone): int
{
    $i = 0;
    foreach (array_keys(zones()) as $zid) {
        if ($zid === $zone) {
            return $i;
        }
        $i++;
    }
    return 0;
}

function horse_race_id(int $pid, string $zone): int
{
    // 各大区独立场次：pid*100+区号
    return $pid * 100 + horse_zone_id($zone);
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
        // id=pid*100+区号，反推原始期号算起止时间
        $base = intdiv($pid, 100);
        $zone = 'z1';
        foreach (zones() as $zid => $z) {
            if (horse_race_id($base, $zid) === $pid) {
                $zone = $zid;
            }
        }
        db()->prepare('INSERT INTO horse_races (id, zone, starts_at, ends_at, status, base_pool) VALUES (?, ?, ?, ?, "open", 500)')->execute([$pid, $zone, $base * 7200, $base * 7200 + 7200]);
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
    $u0 = user_by_id((int) $uid);
    $zone = (string) ($u0['zone'] ?? 'z1');
    $pid = horse_race_id(horse_period(), $zone);
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
    spend_diamonds((int) $uid, $cost);
    horse_race($pid);
    db()->prepare('INSERT INTO horse_bets (race_id, uid, horse, amount) VALUES (?, ?, ?, ?)')->execute([$pid, (int) $uid, $horse, $amount]);
    return '押下' . horse_names()[$horse] . ' ' . $amount . '魔钻！奖池已到' . fmt_diamond(horse_pool($pid)) . '。';
}

function settle_horse(): void
{
    // 分大区结算：截止期号=当前期*100+最大区号
    $cutoff = horse_period() * 100 + count(zones());
    $st = db()->prepare('SELECT * FROM horse_races WHERE id<? AND status="open"');
    $st->execute([$cutoff]);
    while ($r = $st->fetch()) {
        $rid = (int) $r['id'];
        $horses = range(0, 9);
        shuffle($horses);
        $top = array_slice($horses, 0, 3);
        db()->prepare('UPDATE horse_races SET status="done", result=? WHERE id=?')->execute([implode(',', $top), $rid]);
        $pool = (int) $r['base_pool'] + (int) (db()->query('SELECT COALESCE(SUM(amount),0) FROM horse_bets WHERE race_id=' . $rid)->fetchColumn() ?: 0) * 10;
        $shares = [0 => 0.6, 1 => 0.25, 2 => 0.15];
        $names = horse_names();
        $rankTxt = '本场名次：冠【' . $names[(int) $top[0]] . '】亚【' . $names[(int) $top[1]] . '】季【' . $names[(int) $top[2]] . '】，其余：第4【' . $names[(int) $horses[3]] . '】第5【' . $names[(int) $horses[4]] . '】第6【' . $names[(int) $horses[5]] . '】第7【' . $names[(int) $horses[6]] . '】第8【' . $names[(int) $horses[7]] . '】第9【' . $names[(int) $horses[8]] . '】第10【' . $names[(int) $horses[9]] . '】。';
        $winners = [];
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
                $winners[(int) $w['uid']] = true;
                send_mail((int) $w['uid'], '赛马场', 'horse', '赌马中了！' . $names[$h] . '拿了' . ['冠', '亚', '季'][$rank] . '军', '你押的是【' . $names[$h] . '】' . ((int) $w['amount']) . '魔钻，分得' . fmt_diamond($win) . '。' . $rankTxt, [['t' => 'diamond', 'n' => $win]]);
            }
        }
        // 输家也发邮件：告知没中+押的是哪匹+全场名次
        $losers = db()->query('SELECT uid, horse, amount FROM horse_bets WHERE race_id=' . $rid)->fetchAll();
        foreach ($losers as $loser) {
            if (!empty($winners[(int) $loser['uid']])) {
                continue;
            }
            send_mail((int) $loser['uid'], '赛马场', 'horse', '赌马没中：你押的【' . $names[(int) $loser['horse']] . '】落空了', '你押的是【' . $names[(int) $loser['horse']] . '】' . ((int) $loser['amount']) . '魔钻，本场没进前三。' . $rankTxt, []);
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
        'frost_wolf_pup' => ['name' => '霜狼幼崽', 'quality' => 'epic', 'growth' => 1.7, 'atk' => 20, 'def' => 12, 'hp' => 120, 'spd' => 20, 'crit' => 8, 'cd' => 150, 'active' => '寒霜扑咬', 'mult' => 1.5, 'passive' => '霜甲：承伤-12%', 'source' => '冰原章节奖励/商城砸蛋'],
        'puppet_doll' => ['name' => '傀儡人偶', 'quality' => 'epic', 'growth' => 1.6, 'atk' => 12, 'def' => 8, 'hp' => 70, 'spd' => 12, 'crit' => 6, 'cd' => 160, 'active' => '提线绞杀', 'mult' => 1.4, 'passive' => '丝甲：承伤-10%', 'source' => '牵线者掉落'],
        'ember_pup' => ['name' => '火绒犬', 'quality' => 'good', 'growth' => 1.1, 'atk' => 8, 'def' => 6, 'hp' => 70, 'spd' => 12, 'crit' => 4, 'cd' => 155, 'active' => '火绒扑咬', 'mult' => 1.3, 'passive' => '暖绒：主人火抗+5%', 'source' => '商城砸蛋'],
        'mist_fox' => ['name' => '雾狐', 'quality' => 'rare', 'growth' => 1.35, 'atk' => 11, 'def' => 5, 'hp' => 60, 'spd' => 22, 'crit' => 7, 'cd' => 160, 'active' => '雾隐突袭', 'mult' => 1.5, 'passive' => '雾隐：闪避+3%', 'source' => '商城砸蛋'],
        'thunder_bird' => ['name' => '雷鸟', 'quality' => 'rare', 'growth' => 1.35, 'atk' => 12, 'def' => 4, 'hp' => 55, 'spd' => 24, 'crit' => 8, 'cd' => 165, 'active' => '落雷', 'mult' => 1.6, 'passive' => '雷速：出手更快', 'source' => '商城砸蛋'],
        'abyss_witch' => ['name' => '魔女阿比希尔', 'quality' => 'legend', 'growth' => 2.4, 'atk' => 38, 'def' => 12, 'hp' => 120, 'spd' => 20, 'crit' => 12, 'cd' => 175, 'active' => '魔女低语', 'mult' => 1.7, 'passive' => '月之庇佑：低语偷取敌人5%生命', 'source' => '限时活动掉落'],
        'earth_dragon' => ['name' => '土龙岳柱幼体', 'quality' => 'legend', 'growth' => 2.1, 'atk' => 16, 'def' => 24, 'hp' => 380, 'spd' => 10, 'crit' => 5, 'cd' => 190, 'active' => '岳柱镇压', 'mult' => 1.2, 'passive' => '磐岩：承伤-25%', 'source' => '岩狱泰坦掉落'],
    ] + dragon_pets();
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
        'egg_ember' => ['species' => 'ember_pup', 'name' => '火绒犬蛋'],
        'egg_fox' => ['species' => 'mist_fox', 'name' => '雾狐蛋'],
        'egg_bird' => ['species' => 'thunder_bird', 'name' => '雷鸟蛋'],
        'witch_egg' => ['species' => 'abyss_witch', 'name' => '魔女之茧（限时）'],
        'frost_egg' => ['species' => 'frost_wolf_pup', 'name' => '霜狼幼崽蛋'],
        'earth_dragon_egg' => ['species' => 'earth_dragon', 'name' => '土龙岳柱幼体蛋'],
        // 六龙王世界BOSS幼体蛋：名字跟 pets 里的「XX幼体」对齐
        'fire_dragon_egg' => ['species' => 'fire_drake', 'name' => '火龙多伊尔幼体蛋'],
        'wind_dragon_egg' => ['species' => 'sky_serpent', 'name' => '风龙艾瑟琳幼体蛋'],
        'ice_dragon_egg' => ['species' => 'frost_maiden', 'name' => '冰龙伊索尔德幼体蛋'],
        'thunder_dragon_egg' => ['species' => 'storm_tyrant', 'name' => '雷龙雷克斯幼体蛋'],
        'dark_dragon_egg' => ['species' => 'void_dragon', 'name' => '暗龙尼德霍格幼体蛋'],
        'light_dragon_egg' => ['species' => 'seraph_dragon', 'name' => '光龙奥瑞利安幼体蛋'],
        'egg_unknown' => ['species' => '', 'name' => '未知宠物蛋'],
    ];
}


// 限时传奇宠物：魔女阿比希尔（首期限时至2026-11-11，换宠改这里）
function witch_event_active(): bool
{
    return time() >= strtotime('2026-10-11 00:00:00') && time() <= strtotime('2026-11-11 23:59:59');
}

function shop_egg_pool(): array
{
    // 传奇宠物不直购：蛋池只到史诗（幼龙/霜狼幼崽），魔女传奇只能活动掉落
    $pool = ['egg_slime' => 500, 'egg_stone' => 200, 'egg_ember' => 120, 'egg_ghost' => 80, 'egg_fox' => 40, 'egg_bird' => 30, 'egg_dragon' => 20, 'frost_egg' => 8];
    return $pool;
}

function pet_stats(array $p): array
{
    $sp = pet_species()[$p['species']] ?? pet_species()['slime'];
    $lv = max(1, (int) $p['level']);
    $qmult = ['common' => 1.0, 'good' => 1.1, 'rare' => 1.2, 'epic' => 1.35, 'legend' => 1.5][$sp['quality']] ?? 1.0;
    $gf = 0.8 + (float) $sp['growth'] * 0.4;
    $baseA = (float) ($sp['atk'] ?? 10);
    $baseH = (float) ($sp['hp'] ?? 60);
    $baseD = (float) ($sp['def'] ?? 5);
    $s = [
        'atk' => max(1, (int) (($baseA * 0.4 + ($lv - 1) * $baseA * 0.06) * $gf * $qmult)),
        'def' => max(0, (int) (($baseD * 0.4 + ($lv - 1) * $baseD * 0.05) * $gf * $qmult)),
        'maxhp' => max(1, (int) (($baseH + ($lv - 1) * $baseH * 0.12) * (0.8 + (float) $sp['growth'] * 0.3) * $qmult)),
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
    if ($p['species'] === 'shadow_cat' || $p['species'] === 'mist_fox') {
        $s['spd'] = (int) ($s['spd'] * 1.1);
    }
    if ($p['species'] === 'abaddon_spawn') {
        $s['atk'] = (int) ($s['atk'] * 1.1);
    }
    if ($p['species'] === 'abyss_witch') {
        $s['atk'] = (int) ($s['atk'] * 1.3);
        $s['maxhp'] = (int) ($s['maxhp'] * 0.7);
    }
    // 火龙多伊尔幼体：龙威 攻击+20%、承伤-10%（承伤在 pet_hurt 里算）
    if ($p['species'] === 'fire_drake') {
        $s['atk'] = (int) ($s['atk'] * 1.2);
    }
    if ($p['species'] === 'earth_dragon') {
        $s['atk'] = (int) ($s['atk'] * 0.5);
        $s['maxhp'] = (int) ($s['maxhp'] * 1.8);
        $s['def'] = (int) ($s['def'] * 1.3);
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
    if ((int) ($p['fatigue'] ?? 0) >= 70 && !empty($b['live'])) {
        $mats = mats_of((int) ($u['id'] ?? 0));
        if (!empty($mats['pet_food'])) {
            add_mat((int) ($u['id'] ?? 0), 'pet_food', -1);
            db()->prepare('UPDATE pets SET fatigue=0 WHERE id=?')->execute([(int) $p['id']]);
            $p['fatigue'] = 0;
            $autoMsg = $sp['name'] . '饿得叫，自动吃了一份粮食。';
        }
    }
    $sat = (int) (mats_of((int) ($u['id'] ?? 0))['pet_sat_' . (int) $p['id']] ?? 0);
    if ($sat < time()) {
    db()->prepare('UPDATE pets SET fatigue=? WHERE id=?')->execute([min(100, (int) ($p['fatigue'] ?? 0) + 2), (int) $p['id']]);
    }

    $dmg = $stats['atk'] + random_int(0, 3);
    $useSkill = mt_rand(1, 100) <= 30;
    $tag = '';
    if ($useSkill) {
        if ($p['species'] === 'stone') {
            $heal = (int) ((int) $u['maxhp'] * 0.15);
            $u['hp'] = min((int) $u['maxhp'], (int) $u['hp'] + $heal);
            return $sp['name'] . '施展大地之盾，你恢复' . $heal . '点生命。';
        }
        if ($p['species'] === 'abyss_witch' && mt_rand(1, 100) <= 25 && (int) ($b['hp'] ?? 0) > 0) {
            $steal = max(1, (int) ((int) $b['hp'] * 0.05));
            $b['hp'] = (int) $b['hp'] - $steal;
            $u['hp'] = min((int) $u['maxhp'], (int) $u['hp'] + $steal);
            $dmg = (int) ($dmg * (float) $sp['mult']);
            $tag = '（魔女低语，偷取' . $steal . '点生命）';
        } elseif ($p['species'] === 'ghost') {
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
    if ($p['species'] === 'earth_dragon') {
        $take = max(1, (int) ($take * 0.75));
    }
    if ($p['species'] === 'fire_drake') {
        $take = max(1, (int) ($take * 0.9)); // 龙威：承伤-10%
    }
    if ($p['species'] === 'ghost' || $p['species'] === 'puppet_doll') {
        $take = max(1, (int) ($take * 0.9));
    }
    $stats = pet_stats($p);
    $take = max(0, $take - (int) ($stats['def'] / 2));
    $hp = (int) $p['hp'] - $take;
    if ($hp <= 0) {
        $puid = (int) ($p['uid'] ?? 0);
        $pu = $puid > 0 ? user_by_id($puid) : null;
        if ($pu && !empty(mats_of($puid)['pet_autorev']) && (int) ($pu['gold'] ?? 0) >= 100) {
            $pu['gold'] = (int) $pu['gold'] - 100;
            user_save($pu);
            $st = pet_stats($p);
            db()->prepare('UPDATE pets SET hp=?, status="normal" WHERE id=?')->execute([$st['maxhp'], (int) $p['id']]);
            return pet_species()[$p['species']]['name'] . '被击倒了，自动复活（-1银）满血归来！';
        }
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
    if ($mid === 'ry_titan' && mt_rand(1, 100) <= 5) {
        add_mat($uid, 'earth_dragon_egg', 1);
        return '土龙岳柱幼体蛋（5%）';
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
    // 称号/腰带：允许拍卖（称号100级穿，其余规则同下）
    if (in_array(($e['slot'] ?? ''), ['title', 'belt'], true)) {
        return (int) ($e['quality'] ?? 0) >= 3 || equip_is_set(equip_shortname((string) ($e['name'] ?? '')));
    }
    // 史诗/传说/套装可交易
    if ((int) ($e['quality'] ?? 0) >= 3) {
        return true;
    }
    return equip_is_set(equip_shortname((string) ($e['name'] ?? '')));
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
        if ($a['currency'] === 'diamond') {
            spend_diamonds((int) $a['cur_bidder'], $price);
        }
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
            // 回血药已删除：旧邮件显示为雾纹绷带
            $parts[] = '雾纹绷带x' . (int) ($a['n'] ?? 0);
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
            // 回血药已删除：旧邮件按雾纹绷带发放
            add_mat($uid, 'hp_pct10', max(0, (int) ($a['n'] ?? 0)));
        } elseif (($a['t'] ?? '') === 'diamond') {
            $u['diamonds'] = (int) ($u['diamonds'] ?? 0) + max(0, (int) ($a['n'] ?? 0));
        } elseif (($a['t'] ?? '') === 'mat') {
            $mid = (string) ($a['id'] ?? '');
            if ($mid === 'arena_gift') {
                // 低级附魔礼包：开出随机低级属性石
                $els = ['el_light_stone', 'el_dark_stone', 'el_fire_stone', 'el_wind_stone', 'el_ice_stone', 'el_thunder_stone'];
                for ($gi = 0; $gi < max(0, (int) ($a['n'] ?? 0)); $gi++) {
                    add_mat($uid, $els[array_rand($els)], 1);
                }
            } else {
                add_mat($uid, $mid, max(0, (int) ($a['n'] ?? 0)));
            }
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

function is_usable_item(string $mid): bool
{
    if (isset(skill_books()[$mid]) || isset(pet_eggs()[$mid]) || isset(mall_tanks()[$mid]) || isset(pct_potions()[$mid])) {
        return true;
    }
    return in_array($mid, ['exp_card100', 'reset_potion', 'bag_ext5', 'bag_ext10', 'pet_food', 'pet_revive_potion', 'dragon_pill'], true);
}

function mat_hidden(string $mid): bool
{
    if (str_starts_with($mid, 'code_') || str_starts_with($mid, 'dummy_') || str_starts_with($mid, 'hatch_') || str_starts_with($mid, 'soul_lv_') || str_starts_with($mid, 'pet_sat_')) {
        return true;
    }
    // 副本进度标记（计时/阶段）是系统字段，永不进背包显示
    if (str_ends_with($mid, '_enter') || str_ends_with($mid, '_stage')) {
        return true;
    }
    return in_array($mid, ['offline_on', 'offline_time', 'seen_last', 'exp_card_until', 'bag_ext5_used', 'bag_ext10_used', 'dummy_total', 'tank_cd_hp', 'tank_cd_mp', 'peak_force', 'peak_duel_from', 'peak_storm_t', 'peak_buff_atk', 'peak_buff_def', 'peak_buff_heal', 'peak_zone', 'arena_day', 'arena_used', 'arena_bought', 'active_wid', 'active_week', 'priest_wid', 'fr_enter', 'fr_stage', 'em_enter', 'em_stage', 'pz_enter', 'pz_stage', 'sen_enter', 'sen_stage', 'vx_enter', 'wb_claimed', 'legend_fixed', 'rs_summ_hp', 'rs_point', 'exp_card_mult', 'gy_month', 'yz_month'], true);
}

// 当前版本真正有用的锻造材料：强化石 + 属性石/晶石/珠/神石
function is_craft_mat(string $mid): bool
{
    if (in_array($mid, ['enhance_t1', 'enhance_t2', 'enhance_t3'], true)) {
        return true;
    }
    return enchant_mat_el($mid) !== '' && enchant_mat_tier($mid) !== '';
}

function mat_set(int $uid, string $mat, int $n): void
{
    if ($n <= 0) {
        db()->prepare('DELETE FROM mats WHERE uid=? AND mat=?')->execute([(int) $uid, $mat]);
        mats_bust();
        return;
    }
    $st = db()->prepare('UPDATE mats SET num=? WHERE uid=? AND mat=?');
    $st->execute([$n, (int) $uid, $mat]);
    if ($st->rowCount() === 0) {
        db()->prepare('INSERT INTO mats (uid, mat, num) VALUES (?, ?, ?)')->execute([(int) $uid, $mat, $n]);
    }
    mats_bust();
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
        mat_del($uid, 'dummy_on');
        flash_set('陪练人偶没电了，去商城续费。');
        return;
    }
    $elapsed = max(0, min($now - $last, $pool));
    if ($elapsed <= 0) {
        return;
    }
    add_mat($uid, 'dummy_time', -$elapsed);
    $acc = (int) ($mats['dummy_acc'] ?? 0) + $elapsed;
    $fights = min(10, intdiv($acc, 15));
    mat_set($uid, 'dummy_acc', $acc - $fights * 15);
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
        // 人偶打的是幻影：不消耗全服共享的地图存货，只消耗人偶时间。
        // 否则人偶会悄悄吃光你脚下这张图的怪（跨图“被清空”假象的真凶），还会饿死同图的别人。
        $numDummy = $isBossDummy ? 1 : (int) (mats_of((int) ($u['id'] ?? 0))['dummy_num'] ?? 6);
        if (!in_array($numDummy, [1, 3, 6], true)) { $numDummy = 6; }
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
            mat_del($uid, 'dummy_on');
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
    // 血罐：血量<15%自动喝，无冷却；PVP（巅峰/公会战/切磋）不生效；致命伤害不救
    $pct = $kind === 'hp' ? 15 : 30;
    if ($max <= 0 || $cur <= 0 || $cur * 100 >= $max * $pct) {
        return '';
    }
    if ($kind === 'hp' && in_array((string) ($u['loc'] ?? ''), array_merge(array_keys(peak_arenas()), ['warfield']), true)) {
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
function monster_gold_reward(int $base): int
{
    // 怪物金币是铜币；压低挂机产出并封顶，避免高等级怪随等级无限膨胀。
    return max(1, min(10, (int) ceil(max(0, $base) * 0.005)));
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
    if ($c > 0) {
        $out .= $c . '铜';
    }
    if ($out === '') {
        $out = '0铜';
    }
    return $out;
}

// ---------- 装备系统 ----------
// 槽位：主手/副手/上身/头部/下身/手套/鞋子/背部/戒指x2/项链
function equip_slots(): array
{
    return [
        'weapon' => '主手', 'offhand' => '副手', 'body' => '上身', 'head' => '头部', 'legs' => '下身',
        'belt' => '腰部', 'gloves' => '手套', 'shoes' => '鞋子', 'back' => '背部', 'ring' => '戒指', 'necklace' => '项链',
        'soul' => '神魂', 'title' => '称号',
    ];
}

function job_can_twohand(string $job): bool
{
    return $job === 'warrior';
}

// 主手武器细分：按名判定种类，管职业限定+双持判定
function weapon_sub(string $base): string
{
    foreach (['魂灯', '契杖', '骨杖', '魂契', '灵幡', '契匣', '深渊契匣'] as $k) {
        if (str_contains($base, $k)) {
            return 'relic';
        }
    }
    // 召唤师死灵传说主手（灵魂荆棘）：魂契器，只有召唤师能拿
    if (str_contains($base, '灵魂荆棘')) {
        return 'relic';
    }
    $sn = equip_shortname($base);
    $sinMap = ['罪渊·傲慢' => 'staff', '罪渊·嫉妒' => 'axe', '罪渊·暴怒' => 'bow', '罪渊·懒惰' => 'mace', '罪渊·贪婪' => 'shield', '罪渊·暴食' => 'blade', '罪渊·色欲' => 'staff'];
    if (isset($sinMap[$sn])) {
        return $sinMap[$sn];
    }
    $godMap = ['神铸·裁决' => 'staff', '神铸·雷霆' => 'axe', '神铸·苍穹' => 'bow', '神铸·铁律' => 'mace', '神铸·圣盾' => 'shield', '神铸·天罚' => 'blade', '神铸·曙光' => 'staff', '神铸·幽契' => 'relic'];
    if (isset($godMap[$sn])) {
        return $godMap[$sn];
    }

    foreach (['杖', '法器', '魔杖', '圣杖', '提灯', '法典', '法球'] as $k) {
        if (str_contains($base, $k)) {
            return 'staff';
        }
    }
    if (str_contains($base, '斧')) {
        return 'axe';
    }
    foreach (['弓', '弩'] as $k) {
        if (str_contains($base, $k)) {
            return 'bow';
        }
    }
    if (str_contains($base, '锤') || str_contains($base, '杵') || str_contains($base, '槌')) {
        return 'mace';
    }
    if (str_contains($base, '盾')) {
        return 'shield';
    }
    return 'blade';
}

function weapon_jobs(string $sub): array
{
    return [
        'staff' => ['mage', 'priest'],
        'axe' => ['warrior'],
        'bow' => ['hunter'],
        'mace' => ['priest', 'warrior'],
        'shield' => ['warrior', 'priest'],
        'blade' => ['warrior', 'hunter'],
        'relic' => ['summoner'],
    ][$sub] ?? ['warrior', 'hunter', 'mage', 'priest', 'summoner'];
}

function weapon_is_twohand(string $base): bool
{
    // 弓一律视为单手武器：任意弓都能与箭袋(副手)同时装备，不再占用副手位。
    // （弓/弩的 weapon_sub 均为 bow，这里统一放行，含"长弓"等此前判为双手的弓。）
    if (weapon_sub($base) === 'bow') {
        return false;
    }
    if (in_array(equip_shortname($base), twohand_bases(), true)) {
        return true;
    }
    foreach (['权杖', '战斧', '大剑', '长弓', '重锤'] as $k) {
        if (str_contains($base, $k)) {
            return true;
        }
    }
    return false;
}

function job_can_twohand_weapon(string $job, string $base): bool
{
    if ($job === 'warrior') {
        return true;
    }
    $sub = weapon_sub($base);
    if ($job === 'mage' && $sub === 'staff') {
        return true;
    }
    if ($job === 'hunter' && $sub === 'bow') {
        return true;
    }
    return false;
}

// 穿戴展示固定顺序：主手/副手/上身/头部/下身/手套/鞋子/背部/戒指/项链
function equip_sort_by_slot(array $eqs): array
{
    $order = array_flip(array_keys(equip_slots()));
    usort($eqs, function ($a, $b) use ($order) {
        $oa = $order[$a['slot'] ?? ''] ?? 99;
        $ob = $order[$b['slot'] ?? ''] ?? 99;
        if ($oa !== $ob) {
            return $oa <=> $ob;
        }
        return (int) ($a['id'] ?? 0) <=> (int) ($b['id'] ?? 0);
    });
    return $eqs;
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
        'atk' => ['name' => '物攻', 'slots' => ['weapon', 'gloves', 'ring', 'necklace'], 'data' => $r([[2, 5], [5, 10], [10, 18], [18, 30], [30, 50]], 100)],
        'matk' => ['name' => '法攻', 'slots' => ['weapon', 'offhand', 'gloves', 'necklace', 'ring'], 'data' => $r([[2, 5], [5, 10], [10, 18], [18, 30], [30, 50]], 90)],
        'atk_pct' => ['name' => '物攻%', 'slots' => ['weapon'], 'data' => $r([[1, 2], [2, 4], [4, 6], [6, 9], [8, 12]], 80, 'offense_pct', 1)],
        'matk_pct' => ['name' => '法攻%', 'slots' => ['weapon', 'offhand'], 'data' => $r([[1, 2], [2, 4], [4, 6], [6, 9], [8, 12]], 70, 'offense_pct', 1)],
        'bagcap' => ['name' => '背包容量', 'slots' => ['belt'], 'data' => $r([[2, 4], [3, 6], [4, 8], [5, 10], [6, 12]], 100)],
        'crit' => ['name' => '暴击率', 'slots' => ['weapon', 'head', 'gloves', 'ring', 'necklace'], 'data' => $r([[0.1, 0.3], [0.3, 0.6], [0.6, 1.0], [1.0, 1.5], [1.5, 2.0]], 100, 'crit', 1)],
        'critdmg' => ['name' => '暴击伤害', 'slots' => ['weapon', 'head', 'gloves', 'ring', 'necklace'], 'data' => $r([[1, 3], [3, 6], [6, 10], [10, 15], [15, 20]], 100, 'critdmg', 1)],
        'precision' => ['name' => '精准', 'slots' => ['weapon', 'gloves', 'head', 'ring'], 'data' => $r([[1, 2], [2, 4], [4, 7], [7, 10], [10, 15]], 60, '', 1)],
        'speed' => ['name' => '攻速', 'slots' => ['weapon', 'gloves'], 'data' => $r([[1, 2], [2, 4], [4, 6], [6, 8], [7, 10]], 90, 'speed', 1)],
        'element' => ['name' => '元素伤害', 'slots' => ['weapon', 'offhand', 'ring'], 'data' => $r([[2, 4], [4, 8], [8, 13], [13, 19], [15, 25]], 70)],
        'lifesteal' => ['name' => '生命偷取', 'slots' => ['weapon', 'ring'], 'data' => $r([[0.2, 0.5], [0.5, 0.9], [0.9, 1.4], [1.4, 2.0], [2, 3]], 50, 'lifesteal', 1)],
        'summon_power' => ['name' => '召唤兽伤害', 'slots' => ['weapon', 'offhand', 'necklace', 'ring'], 'data' => $r([[2, 5], [5, 9], [9, 15], [15, 22], [22, 32]], 60, 'summon_power', 1)],
        'summon_hp' => ['name' => '召唤兽生命', 'slots' => ['weapon', 'offhand', 'necklace', 'ring'], 'data' => $r([[3, 6], [6, 11], [11, 18], [18, 26], [26, 38]], 55, 'summon_hp', 1)],
        'res_light' => ['name' => '光抗性', 'slots' => ['body', 'necklace'], 'data' => $r([[1, 2], [2, 4], [4, 7], [7, 11], [10, 15]], 40, '', 1)],
        'res_dark' => ['name' => '暗抗性', 'slots' => ['body', 'necklace'], 'data' => $r([[1, 2], [2, 4], [4, 7], [7, 11], [10, 15]], 40, '', 1)],
        'res_fire' => ['name' => '火抗性', 'slots' => ['body', 'necklace'], 'data' => $r([[1, 2], [2, 4], [4, 7], [7, 11], [10, 15]], 40, '', 1)],
        'res_wind' => ['name' => '风抗性', 'slots' => ['body', 'necklace'], 'data' => $r([[1, 2], [2, 4], [4, 7], [7, 11], [10, 15]], 40, '', 1)],
        'res_ice' => ['name' => '冰抗性', 'slots' => ['body', 'necklace'], 'data' => $r([[1, 2], [2, 4], [4, 7], [7, 11], [10, 15]], 40, '', 1)],
        'res_thunder' => ['name' => '雷抗性', 'slots' => ['body', 'necklace'], 'data' => $r([[1, 2], [2, 4], [4, 7], [7, 11], [10, 15]], 40, '', 1)],
        'allres' => ['name' => '全元素抗性', 'slots' => ['body', 'necklace', 'ring'], 'data' => $r([[1, 2], [2, 4], [4, 6], [6, 9], [8, 12]], 25, '', 1)],
        'penetration' => ['name' => '护甲穿透', 'slots' => ['weapon'], 'data' => $r([[1, 2], [2, 4], [4, 7], [7, 10], [8, 12]], 60, 'penetration', 1)],
        'skill_damage' => ['name' => '全伤害加成', 'slots' => ['weapon'], 'data' => $r([[2, 4], [4, 7], [7, 10], [10, 14], [12, 18]], 70, 'skill_damage', 1)],
        'execute' => ['name' => '处决伤害', 'slots' => ['weapon'], 'data' => $r([[3, 6], [6, 10], [10, 16], [16, 23], [20, 30]], 40, 'execute', 1)],
        'hp' => ['name' => '生命', 'slots' => ['body', 'head', 'legs', 'shoes', 'ring', 'necklace', 'offhand', 'back'], 'data' => $r([[20, 30], [30, 48], [42, 65], [58, 100], [90, 150]], 100)],
        'def' => ['name' => '物防', 'slots' => ['body', 'head', 'legs', 'gloves', 'shoes', 'offhand', 'back'], 'data' => $r([[10, 18], [18, 30], [26, 42], [38, 60], [60, 100]], 100)],
        'mdef' => ['name' => '法防', 'slots' => ['body', 'head', 'offhand', 'back', 'necklace'], 'data' => $r([[8, 14], [15, 24], [22, 34], [30, 48], [48, 80]], 90)],
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
        'truedmg' => ['name' => '真伤', 'slots' => ['weapon','necklace','title'], 'data' => ['tiers' => [[1,2],[2,4],[4,7],[7,10],[8,12]], 'precision' => 0]],
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

function equip_affix_html(array $aff, int $enhanceLv = 0): string
{
    $mult = enhance_rate(['enhance_level' => $enhanceLv]);
    $top = [];
    $sub = [];
    foreach ($aff as $x) {
        $id = (string) ($x['id'] ?? $x['k'] ?? '');
        $v = (float) ($x['v'] ?? 0);
        if (!empty($x['main']) || in_array($id, ['atk', 'def', 'hp', 'all_attr'], true)) {
            $v *= $mult;
        }
        if (!empty($x['main'])) {
            $mk = '主·';
        } elseif (!empty($x['legend'])) {
            $mk = '【传奇属性】';
        } elseif (!empty($x['bonus'])) {
            $mk = '强·';
        } else {
            $mk = '';
        }
        $line = '·' . $mk . h(affix_fmt($id, $v, !empty($x['legend']) ? null : ($x['tier'] ?? null)));
        if (!empty($x['legend'])) {
            $line = '<b style="color:#fc3">' . $line . '</b>';
        } elseif (!empty($x['main'])) {
            $line = '<b>' . $line . '</b>';
        }
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

// 高强外观：+7彩虹字，+10彩虹渐变+炫光
function enhance_tag_html(int $lv): string
{
    $lv = max(0, $lv);
    if ($lv >= 10) {
        return '<b style="background:linear-gradient(90deg,#f66,#fc3,#6f6,#6cf,#c6f,#f66);-webkit-background-clip:text;background-clip:text;color:#fc3;-webkit-text-fill-color:transparent;text-shadow:0 0 8px #f6f">✦+' . $lv . '✦</b>';
    }
    if ($lv >= 7) {
        return '<b style="background:linear-gradient(90deg,#cfc,#6f6,#3c6);-webkit-background-clip:text;background-clip:text;color:#6f6;-webkit-text-fill-color:transparent">+' . $lv . '</b>';
    }
    return '+' . $lv;
}

function legend_lore(string $shortName): string
{
    $lore = [
        '食人魔战裙' => '咕噜死前还在笑。它说这条战裙是它从一百个输家身上剥下来的。<br>穿上它的人，听见的笑声比敌人多。',
        '堕落骑士大剑' => '莫尔堕落那天，剑先替他哭了。剑身上的锈，是他没流出来的泪。<br>拿起它，就替他把没打完的那一仗打完。',
        '雷恩回响戒' => '雷恩把名字刻在戒指内侧，字很小，怕深渊听见。<br>戴上它，第三层有人叫你名字时，你会先想起他。',
        '灵魂荆棘' => '荆棘从契约的裂缝里长出来，缠住你召出的每一道影子。<br>影子替你撕开敌人的灵魂时，荆棘也在你手心里扎根。',
        '嘶鸣' => '戴上它，你听不见自己说话，只听见召唤物在尖叫。<br>它们从此不再闪避，每一击都咬在同一个地方。',
        '尼德霍格的残鳞' => '黑龙蜕下的最后一片鳞，还带着它自己的心跳。<br>你把它缝在身上——容器越空，里面装的东西就越凶。',
        '死灵之拥的腐朽头冠' => '生者戴王冠，死者戴这个。<br>你的召唤物倒下时，头冠会替它们把最后一口气炸出去。',
        '玛门的契约之眼' => '玛门不做亏本买卖。它借你一只眼，看得见别人身上的裂缝。<br>代价是：你得替它把契约盯紧。',
        '深渊统御之印' => '深渊认印不认人。越多东西从印里爬出来，它们就越不听你的。',
        '血契骨戒' => '骨戒里锁着一份血契：它替你养兵，你替它喂血。<br>急的时候，它会先咬断最弱的那只，再把血还给你。',
    ];
    if (isset($lore[$shortName])) {
        return $lore[$shortName];
    }
    if (function_exists('hunter_arsenal_legendary_lore')) {
        $hl = hunter_arsenal_legendary_lore();
        if (isset($hl[$shortName])) {
            return $hl[$shortName];
        }
    }
    if (function_exists('dot_legendary_lore')) {
        $dl = dot_legendary_lore();
        if (isset($dl[$shortName])) {
            return $dl[$shortName];
        }
    }
    if (function_exists('legend_story_map')) {
        $extra = legend_story_map();
        if (isset($extra[$shortName])) {
            return $extra[$shortName];
        }
    }
    return '深渊里捞出来的东西，每一件都记得主人的名字。<br>它现在记得你的了。';
}

function affix_fmt(string $k, float $v, ?string $tier = null): string
{
    // 召唤师死灵传说 / 遗神铸庭传说：行为型效果，显示说明文字，不显示数值
    if (str_starts_with($k, 'slegend_')) {
        return summoner_legend_intro_by_key($k);
    }
    if (str_starts_with($k, 'lg_')) {
        return legend_relic_intro_by_key($k);
    }
    $percent = ['atk_pct', 'matk_pct', 'crit', 'critdmg', 'speed', 'lifesteal', 'penetration', 'skill_damage', 'execute', 'damage_reduction', 'resist', 'regen', 'cooldown', 'hit', 'exp_gain', 'dodge', 'move_speed', 'control_resist', 'slow_resist', 'gold_gain', 'magic_find', 'summon_power', 'summon_hp', 'res_light', 'res_dark', 'res_fire', 'res_wind', 'res_ice', 'res_thunder', 'allres'];
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
    $lv = max(1, (int) $itemLevel);
    $qmult = (float) (equip_quality_config()[$q]['multiplier'] ?? 1);
    $flat = ['atk', 'def', 'hp', 'element', 'thorns', 'energy', 'all_attr'];
    if (in_array($k, $flat, true)) {
        // 固定值随等级1.36次方成长（10级≈原来，130级约50倍），否则高级装主属性永远是个位数
        $value *= $qmult * 0.0072 * pow($lv, 1.36);
    } else {
        $value *= $qmult * (1 + $lv * 0.02) * 0.3;
    }
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

// 套装件：沼泽3件/深渊3件/剧场系列，显示醒目套装绿
function equip_is_set(string $shortName): bool
{
    if (function_exists('region_dungeon_prefix_of') && region_dungeon_prefix_of($shortName) !== '') {
        return true;
    }
    if (equip_set_id($shortName) !== '') {
        return true;
    }
    return in_array($shortName, ['沼泽兜帽', '沼泽轻靴', '沼泽之心', '深渊蚀甲', '深渊腿甲', '深渊护手'], true);
}

function equip_color(array $e): string
{
    if (equip_is_set(equip_shortname((string) ($e['name'] ?? '')))) {
        return '#3f6';
    }
    return ['#999', '#fff', '#6cf', '#c6f', '#fc3'][(int) ($e['quality'] ?? 0)] ?? '#fff';
}

function equip_color_by(string $fullName, int $q): string
{
    return equip_color(['name' => $fullName, 'quality' => $q]);
}

// 套装效果说明：有其中一件就展示整套，勾引玩家刷齐
function equip_set_id(string $shortName): string
{
    if (function_exists('region_dungeon_prefix_of')) {
        $rdp = region_dungeon_prefix_of($shortName);
        if ($rdp !== '') {
            return 'rd_set:' . $rdp;
        }
    }
    if (in_array($shortName, ['沼泽兜帽', '沼泽轻靴', '沼泽之心', '沼泽束腰'], true)) {
        return 'swamp';
    }
    if (in_array($shortName, ['深渊蚀甲', '深渊腿甲', '深渊护手', '深渊缚带'], true)) {
        return 'abyss';
    }
    if (str_starts_with($shortName, '剧场·')) {
        return 'theater';
    }
    if (str_starts_with($shortName, '寒霜·')) {
        return 'frost';
    }
    if (str_starts_with($shortName, '烬焰·')) {
        return 'ember';
    }
    if (str_starts_with($shortName, '傀儡·')) {
        return 'puppet';
    }
    if (str_starts_with($shortName, '荆棘·')) {
        return 'thorn';
    }
    if (str_starts_with($shortName, '罪渊·')) {
        return 'sin';
    }
    if (str_starts_with($shortName, '暮影·')) {
        return 'dusk';
    }    if (str_starts_with($shortName, '寂灭·')) {
        return 'jimie';
    }
    if (str_starts_with($shortName, '噬渊·')) {
        return 'shiyuan';
    }    if (str_starts_with($shortName, '魂契·')) {
        return 'soulpact';
    }
    if (str_starts_with($shortName, '神铸·')) {
        return 'god';
    }
    if (str_starts_with($shortName, '岩狱·')) {
        return 'rockjail';
    }
    if (str_starts_with($shortName, '蚀心·')) {
        return 'shixing';
    }
    // —— 六龙王专属套装（按前缀）——
    if (str_starts_with($shortName, '苍岚·')) {
        return 'azure';
    }
    if (str_starts_with($shortName, '永冻·')) {
        return 'glacier';
    }
    if (str_starts_with($shortName, '天谴·')) {
        return 'thunder';
    }
    if (str_starts_with($shortName, '终焉·')) {
        return 'ruin';
    }
    if (str_starts_with($shortName, '圣裁·')) {
        return 'judgment';
    }
    if (str_starts_with($shortName, '烈炎·')) {
        return 'firedragon';
    }
    return '';
}

function equip_set_pieces(string $set): array
{
    if (str_starts_with($set, 'rd_set:')) {
        $info = region_dungeon_set_info(substr($set, 7));
        return $info['pieces'] ?? [];
    }
    if ($set === 'swamp') {
        return ['沼泽兜帽', '沼泽轻靴', '沼泽之心', '沼泽束腰'];
    }
    if ($set === 'abyss') {
        return ['深渊蚀甲', '深渊腿甲', '深渊护手', '深渊缚带'];
    }
    if ($set === 'frost') {
        return ['寒霜·战斧', '寒霜·法杖', '寒霜·战锤', '寒霜·长弓', '寒霜·冰盾', '寒霜·骑士铠', '寒霜·胫甲', '寒霜·束腰'];
    }
    if ($set === 'ember') {
        return ['烬焰·龙裔刃', '烬焰·灰烬法杖', '烬焰·熔核锤', '烬焰·焚心弓', '烬焰·熔盾', '烬焰·余烬囊', '烬焰·守卫铠', '烬焰·祭司冠', '烬焰·熔渣腿', '烬焰·灰烬戒', '烬焰·余火链', '烬焰·束腰'];
    }
    if ($set === 'puppet') {
        return ['傀儡·断线匕', '傀儡·舞步靴', '傀儡·覆面', '傀儡·守卫甲', '傀儡·大师之手', '傀儡·束腰'];
    }
    if ($set === 'thorn') {
        return ['荆棘·雾祭铃', '荆棘·刺甲', '荆棘·蛇牙戒', '荆棘·守卫裙', '荆棘·束腰'];
    }
    if ($set === 'sin') {
        return ['罪渊·傲慢', '罪渊·嫉妒', '罪渊·暴怒', '罪渊·懒惰', '罪渊·贪婪', '罪渊·暴食', '罪渊·色欲', '罪渊·悔恨冠', '罪渊·赎罪甲', '罪渊·缚罪裤', '罪渊·忏悔束腰', '罪渊·堕天靴', '罪渊·噬罪手套', '罪渊·堕羽披风', '罪渊·泣罪戒', '罪渊·殉罪链', '罪渊·赎罪灯'];
    }
    if ($set === 'dusk') {
        return ['暮影·暮刃', '暮影·暮誓手套', '暮影·暮链'];
    }    if ($set === 'jimie') {
        return ['寂灭·终末之刃', '寂灭·永恒寂灭冠', '寂灭·虚无礼赞袍', '寂灭·终焉铭刻链', '寂灭·寂静终戒指'];
    }
    if ($set === 'shiyuan') {
        return ['噬渊·深渊吞噬者', '噬渊·万渊之腹甲', '噬渊·噬魂万手', '噬渊·无尽深渊裹腿', '噬渊·归墟沉没披风'];
    }    if ($set === 'soulpact') {
        return ['魂契·万鬼招魂幡', '魂契·深渊引魂灯', '魂契·亡者契约冠', '魂契·幽魂缚魂链', '魂契·献祭者骨戒'];
    }
    if ($set === 'god') {
        return ['神铸·裁决', '神铸·雷霆', '神铸·苍穹', '神铸·铁律', '神铸·圣盾', '神铸·天罚', '神铸·曙光', '神铸·圣冠', '神铸·羽甲', '神铸·天裤', '神铸·云腰', '神铸·踏云靴', '神铸·执掌手套', '神铸·天翼披风', '神铸·星辰戒', '神铸·神谕链', '神铸·圣灯'];
    }
    if ($set === 'shixing') {
        return ['蚀心·猎弓', '蚀心·箭袋', '蚀心·血衣', '蚀心·覆面', '蚀心·绑腿', '蚀心·裹手', '蚀心·轻履'];
    }
    // 六龙王专属套装
    $dp = function_exists('dragon_set_pieces') ? dragon_set_pieces($set) : [];
    if ($dp !== []) {
        return $dp;
    }
    return [];
}

function equip_set_owned_count(int $uid, string $set): int
{
    if (str_starts_with($set, 'rd_set:')) {
        $pieces = equip_set_pieces($set);
        if ($pieces === []) {
            return 0;
        }
        // BOSS 专属套装箭袋（<前缀>之箭袋）也算作该套装的一件
        $prefix = substr($set, strlen('rd_set:'));
        $quiver = $prefix . '之箭袋';
        $st = db()->prepare('SELECT name FROM equips WHERE uid=? AND pos="wear"');
        $st->execute([(int) $uid]);
        $n = 0;
        while ($r = $st->fetch()) {
            $sn = equip_shortname((string) $r['name']);
            if (in_array($sn, $pieces, true) || $sn === $quiver) { $n++; }
        }
        return min(count($pieces) + 1, $n);
    }
    if (in_array($set, ['theater', 'frost', 'ember', 'puppet', 'thorn', 'rockjail', 'sin', 'god', 'dusk', 'jimie', 'shiyuan', 'soulpact', 'shixing'], true)) {
        $mark = ['theater' => '%剧场·%', 'frost' => '%寒霜·%', 'ember' => '%烬焰·%', 'puppet' => '%傀儡·%', 'thorn' => '%荆棘·%', 'rockjail' => '%岩狱·%', 'sin' => '%罪渊·%', 'god' => '%神铸·%', 'dusk' => '%暮影·%', 'jimie' => '%寂灭·%', 'shiyuan' => '%噬渊·%', 'soulpact' => '%魂契·%', 'shixing' => '%蚀心·%'][$set];
        $cap = ['theater' => 99, 'frost' => 3, 'ember' => 7, 'puppet' => 5, 'thorn' => 4, 'rockjail' => 10, 'sin' => 11, 'god' => 11, 'dusk' => 3, 'jimie' => 5, 'shiyuan' => 5, 'soulpact' => 5, 'shixing' => 7][$set];
        $st = db()->prepare('SELECT COUNT(*) FROM equips WHERE uid=? AND pos="wear" AND name LIKE ?');
        $st->execute([(int) $uid, $mark]);
        return min($cap, (int) $st->fetchColumn());
    }
    // 六龙王专属套装：按前缀计数（15件套，满档11=1职业武器+10防具首饰）
    if (function_exists('dragon_set_pieces') && dragon_set_pieces($set) !== []) {
        $prefix = ['firedragon' => '烈炎·', 'azure' => '苍岚·', 'glacier' => '永冻·', 'thunder' => '天谴·', 'ruin' => '终焉·', 'judgment' => '圣裁·'][$set] ?? '';
        if ($prefix !== '') {
            $st = db()->prepare('SELECT COUNT(*) FROM equips WHERE uid=? AND pos="wear" AND name LIKE ?');
            $st->execute([(int) $uid, '%' . $prefix . '%']);
            return min(11, (int) $st->fetchColumn());
        }
    }
    $n = 0;
    $st = db()->prepare('SELECT name FROM equips WHERE uid=? AND pos="wear"');
    $st->execute([(int) $uid]);
    while ($r = $st->fetch()) {
        if (in_array(equip_shortname((string) $r['name']), equip_set_pieces($set), true)) {
            $n++;
        }
    }
    return $n;
}

function equip_set_bonus_text(string $set, int $owned): string
{
    if (str_starts_with($set, 'rd_set:')) {
        return region_dungeon_set_bonus_text(substr($set, 7));
    }
    if ($set === 'swamp') {
        return '【沼泽套装】已穿' . $owned . '/3（兜帽/轻靴/之心），集齐3件：能量+300，防御+20。';
    }
    if ($set === 'abyss') {
        return '【深渊套装】已穿' . $owned . '/3（蚀甲/腿甲/护手），集齐3件：物攻+25，防御+15。';
    }
    if ($set === 'theater') {
        return '【剧场套装】已穿' . $owned . '件——3件：物攻+15%；5件：元素伤害+80；7件：减伤+12%，技能伤害+20%。';
    }
    if ($set === 'frost') {
        return '【寒霜套装】已穿' . $owned . '/3：3件：物攻+8%，法防+80。';
    }
    if ($set === 'ember') {
        return '【烬焰套装】已穿' . $owned . '/7：2件物攻+10%，4件元素伤害+120，7件真伤+500、减伤+10%、技能伤害+15%。';
    }
    if ($set === 'puppet') {
        return '【傀儡套装】已穿' . $owned . '/5：2件技能伤害+10%，5件元素伤害+100、减伤+8%。';
    }
    if ($set === 'thorn') {
        return '【荆棘套装】已穿' . $owned . '/4：3件物攻+6%，4件吸血+3%。';
    }
    if ($set === 'god') {
        return '【神铸套装】已穿' . $owned . '/11（10防具+1武器，或9防具+主副手）——每件：物攻/法攻+3%、减伤+1%；3件：生命+等级×40；5件：吸血8%；7件：减伤+12%；9件：真伤+等级×20；11件：技能伤+30%、全属性+等级×5！';
    }
    if ($set === 'sin') {
        return '【罪渊套装】已穿' . $owned . '/11（10防具+1武器，或9防具+主副手）——每件：攻击+1%、技能伤+1（×罪加成）；3件：生命+罪×2；5件：吸血5%；7件：减伤8%；9件：真伤+罪×3；11件：减伤+罪/20（封顶20）、技能伤+罪/10（封顶50）！';
    }
    if ($set === 'dusk') {
        return '【暮影套装】已穿' . $owned . '/3（暮刃/暮誓手套/暮链）——2件：物/法攻+15%、技能伤+10%；3件：暴击+8%、真伤+等级×10！流浪者的执念，有罪者佩之更强。';
    }
    if ($set === 'jimie') {
        return '【寂灭套装】已穿' . $owned . '/5（终末之刃/永恒寂灭冠/虚无礼赞袍/终焉铭刻链/寂静终戒指）——每件：全伤+3%；2件：技伤+12%、真伤+等级×15；3件：减伤+15%；5件：真伤+等级×40、技能伤+20%！把一切归零。';
    }
    if ($set === 'shiyuan') {
        return '【噬渊套装】已穿' . $owned . '/5（深渊吞噬者/万渊之腹甲/噬魂万手/无尽深渊裹腿/归墟沉没披风）——每件：吸血+2%；2件：攻+12%、吸血+6%；3件：真伤+等级×20；5件：全属性+等级×5、减伤+20%！深渊回赠你一口。';
    }
    if ($set === 'soulpact') {
        return '【魂契套装】已穿' . $owned . '/5（万鬼招魂幡/深渊引魂灯/亡者契约冠/幽魂缚魂链/献祭者骨戒）——每件：召唤兽伤害+8%；2件：召唤上限+1、真伤+等级×10；3件：召唤兽攻击+等级×4；5件：召唤兽生命+40%、召唤兽死亡必爆炸！容器越空，召唤越强。';
    }
    if ($set === 'rockjail') {
        return '【岩狱套装】已穿' . $owned . '/10：3件血+3000，5件减伤+8%，7件血+8000减伤+12%，10件血+20000减伤+20%、磐岩（承伤再-5%）。';
    }
    if ($set === 'shixing') {
        return '【蚀心DOT套装】已穿' . $owned . '/7（猎弓/箭袋/血衣/覆面/绑腿/裹手/轻履）——每件：物攻+2%、暴击+1；3件：技能伤+8%、暴击+5%、持续伤害+12%；5件：暴伤+20%、吸血+4%、物攻+6%、持续伤害+20%；7件满：物攻+10%、暴伤+25%、真伤+等级×15、减伤+8%、持续伤害+30%！猎人DOT流毕业套（堕天阶掉落）。';
    }
    // 六龙王专属套装
    if (function_exists('dragon_set_bonus') && dragon_set_bonus($set, $owned) !== '') {
        return dragon_set_bonus($set, $owned);
    }
    return '';
}

// 怪物专属掉落：小怪掉率低且多为低劣
function monster_drops(string $mid): array
{
    // 六圣柱小怪：不掉龙王套装（套装只由斩龙排行产出），只走 monster_material 掉龙魂/龙王精华
    $dm = dragon_monster($mid);
    if ($dm !== []) {
        return [];
    }
    return ([
        'rat' => ['slot' => 'gloves', 'base' => '破布手套', 'rate' => 0.01, 'q' => [100, 0, 0, 0, 0]],
        'rat_pack' => ['slot' => 'gloves', 'base' => '腐骨护手'],
        'slime' => ['slot' => 'shoes', 'base' => '黏液布鞋', 'rate' => 0.012, 'q' => [80, 20, 0, 0, 0]],
        'goblin' => ['slot' => 'weapon', 'base' => '断匕', 'rate' => 0.015, 'q' => [70, 30, 0, 0, 0]],
        'wolf' => ['slot' => 'necklace', 'base' => '狼牙项链', 'rate' => 0.02, 'q' => [50, 35, 15, 0, 0]],
        'banshee' => ['slot' => 'ring', 'base' => '哭泣戒指', 'rate' => 0.025, 'q' => [0, 60, 30, 10, 0]],
        'wraith' => ['slot' => 'back', 'base' => '怨魂斗篷', 'rate' => 0.03, 'q' => [30, 50, 20, 0, 0]],
        'rift_hound' => ['slot' => 'weapon', 'base' => '裂隙猎弓', 'rate' => 0.12, 'q' => [0, 30, 50, 20, 0]],
        'rift_weaver' => ['slot' => 'offhand', 'base' => '织影法器', 'rate' => 0.12, 'q' => [0, 30, 50, 20, 0]],
        'swamp_slime' => ['slot' => 'belt', 'base' => '沼泽束腰', 'rate' => 0.04, 'q' => [60, 30, 10, 0, 0]],
        'mist_treant' => ['slot' => 'belt', 'base' => '雾狐软腰', 'rate' => 0.05, 'q' => [30, 45, 25, 0, 0]],
        'mist_apeman' => ['slot' => 'belt', 'base' => '雷鸟羽带', 'rate' => 0.08, 'q' => [0, 10, 40, 40, 10], 'legendary' => ['base' => '雷鸟羽带·九霄', 'extra' => ['k' => 'bagcap', 'v' => 10.0]]],
        'thief' => ['slot' => 'gloves', 'base' => '盗贼手套', 'rate' => 0.025, 'q' => [30, 50, 20, 0, 0]],
        'bee' => ['slot' => 'ring', 'base' => '蜂刺戒指', 'rate' => 0.025, 'q' => [30, 50, 20, 0, 0]],
        'frog' => ['slot' => 'legs', 'base' => '毒蛙腿甲', 'rate' => 0.025, 'q' => [30, 50, 20, 0, 0]],
        'leech' => ['slot' => 'ring', 'base' => '吸血指环', 'rate' => 0.035, 'q' => [0, 55, 35, 10, 0]],
        'ogre' => ['slot' => 'belt', 'base' => '兽皮腰带', 'rate' => 0.06, 'q' => [0, 45, 40, 12, 3], 'legendary' => ['base' => '食人魔战裙', 'extra' => ['k' => 'damage_reduction', 'v' => 2.0]]],
        'skeleton' => ['slot' => 'head', 'base' => '锈盔', 'rate' => 0.035, 'q' => [20, 50, 30, 0, 0]],
        'bat' => ['slot' => 'back', 'base' => '蝠翼披风', 'rate' => 0.035, 'q' => [20, 50, 30, 0, 0]],
        'ghoul' => ['slot' => 'weapon', 'base' => '食尸鬼骨弓', 'rate' => 0.06, 'q' => [0, 30, 50, 20, 0]],
        // 召唤师契匣（过渡副手，掉出来就能戴，各自带开场召唤）
        'cultist' => ['slot' => 'offhand', 'off' => 'magic', 'base' => '引魂旧匣', 'rate' => 0.05, 'q' => [0, 100, 0, 0, 0]],
        'guard' => ['slot' => 'legs', 'base' => '祭坛护腿', 'rate' => 0.06, 'q' => [0, 20, 50, 30, 0]],
        'jailer' => ['slot' => 'body', 'base' => '狱卒重铠', 'rate' => 0.07, 'q' => [0, 0, 65, 35, 0]],
        'knight' => ['slot' => 'weapon', 'base' => '骑士残剑', 'rate' => 0.25, 'q' => [0, 0, 0, 70, 30], 'legendary' => ['base' => '堕落骑士大剑', 'extra' => ['k' => 'execute', 'v' => 5.0]]],
        'doyle' => ['slot' => 'necklace', 'base' => '烬焰龙牙链', 'rate' => 0.5, 'q' => [0, 0, 0, 50, 50], 'legendary' => ['base' => '烬焰龙心·焚天', 'extra' => ['k' => 'truedmg', 'v' => 300.0]]],
        'string_puller' => ['slot' => 'gloves', 'base' => '牵线丝手套', 'rate' => 0.2, 'q' => [0, 0, 30, 50, 20], 'legendary' => ['base' => '提线主宰·傀儡戏', 'extra' => ['k' => 'skill_damage', 'v' => 6.0]]],
        'swamp_king' => ['slot' => 'body', 'base' => '沼泽王袍', 'rate' => 0.25, 'q' => [0, 0, 20, 50, 30], 'legendary' => ['base' => '沼泽王冠·腐殖', 'extra' => ['k' => 'regen', 'v' => 50.0]]],
        'ember_imp' => ['slot' => 'belt', 'base' => '烬焰·束腰', 'rate' => 0.07, 'q' => [0, 20, 50, 30, 0]],
        'ink_puppet' => ['slot' => 'weapon', 'base' => '墨傀之笔', 'rate' => 0.1, 'q' => [0, 20, 50, 30, 0], 'legendary' => ['base' => '墨傀之笔·点睛', 'extra' => ['k' => 'critdmg', 'v' => 8.0]]],
        'string_puller' => ['slot' => 'belt', 'base' => '傀儡·束腰', 'rate' => 0.07, 'q' => [0, 20, 50, 30, 0]],
        'masked_noble' => ['slot' => 'shoes', 'base' => '假面夜行靴', 'rate' => 0.1, 'q' => [0, 20, 50, 30, 0], 'legendary' => ['base' => '假面贵胄·夜宴', 'extra' => ['k' => 'dodge', 'v' => 3.0]]],
        'puppet_guard' => ['slot' => 'body', 'base' => '铁卫傀儡甲', 'rate' => 0.08, 'q' => [0, 30, 50, 20, 0], 'legendary' => ['base' => '铁卫傀儡·忠骸', 'extra' => ['k' => 'damage_reduction', 'v' => 4.0]]],
        'puppet_priest' => ['slot' => 'head', 'base' => '圣骸祭司帽', 'rate' => 0.08, 'q' => [0, 30, 50, 20, 0], 'legendary' => ['base' => '圣骸祭司·赎罪', 'extra' => ['k' => 'mdef', 'v' => 60.0]]],
        'silver_assassin' => ['slot' => 'weapon', 'base' => '银丝刺刃', 'rate' => 0.12, 'q' => [0, 10, 50, 40, 0], 'legendary' => ['base' => '银月影刺·无痕', 'extra' => ['k' => 'crit', 'v' => 4.0]]],
        'silver_puppet' => ['slot' => 'back', 'base' => '银丝之茧', 'rate' => 0.1, 'q' => [0, 20, 50, 30, 0], 'legendary' => ['base' => '银丝之茧·重生', 'extra' => ['k' => 'lifesteal', 'v' => 2.0]]],
        'silver_undead' => ['slot' => 'legs', 'base' => '不死银卫铠', 'rate' => 0.1, 'q' => [0, 20, 50, 30, 0], 'legendary' => ['base' => '不死银卫·永生', 'extra' => ['k' => 'hp', 'v' => 2500.0]]],
        'gr_hyena' => ['slot' => 'weapon', 'base' => '鬣狗之牙', 'rate' => 0.06, 'q' => [0, 0, 60, 40, 0], 'legendary' => ['base' => '鬣狗之牙·噬月', 'extra' => ['k' => 'execute', 'v' => 6.0]]],
        'gr_vulture' => ['slot' => 'head', 'base' => '秃鹫之眼', 'rate' => 0.06, 'q' => [0, 0, 60, 40, 0], 'legendary' => ['base' => '秃鹫之眼·俯瞰', 'extra' => ['k' => 'hit', 'v' => 5.0]]],
        'gr_worm' => ['slot' => 'body', 'base' => '蠕虫之皮', 'rate' => 0.06, 'q' => [0, 0, 60, 40, 0], 'legendary' => ['base' => '蠕虫之皮·流沙', 'extra' => ['k' => 'damage_reduction', 'v' => 5.0]]],
        'gr_raider' => ['slot' => 'weapon', 'base' => '掠夺者弯刀', 'rate' => 0.07, 'q' => [0, 0, 55, 45, 0], 'legendary' => ['base' => '掠夺者弯刀·血债', 'extra' => ['k' => 'lifesteal', 'v' => 3.0]]],
        'gr_storm' => ['slot' => 'necklace', 'base' => '沙暴之心', 'rate' => 0.07, 'q' => [0, 0, 55, 45, 0], 'legendary' => ['base' => '沙暴之心·无垠', 'extra' => ['k' => 'move_speed', 'v' => 8.0]]],
        'gr_bat' => ['slot' => 'back', 'base' => '巨蝠之翼', 'rate' => 0.06, 'q' => [0, 0, 60, 40, 0], 'legendary' => ['base' => '巨蝠之翼·夜幕', 'extra' => ['k' => 'dodge', 'v' => 4.0]]],
        'gr_guard' => ['slot' => 'body', 'base' => '巢穴壁垒', 'rate' => 0.07, 'q' => [0, 0, 55, 45, 0], 'legendary' => ['base' => '巢穴壁垒·磐石', 'extra' => ['k' => 'thorns', 'v' => 200.0]]],
        'gr_whelp' => ['slot' => 'gloves', 'base' => '雏魔之牙', 'rate' => 0.07, 'q' => [0, 0, 55, 45, 0], 'legendary' => ['base' => '雏魔之牙·初啼', 'extra' => ['k' => 'crit', 'v' => 5.0]]],
        'gr_wraith' => ['slot' => 'head', 'base' => '骨堆王冠', 'rate' => 0.07, 'q' => [0, 0, 55, 45, 0], 'legendary' => ['base' => '骨堆王冠·怨语', 'extra' => ['k' => 'skill_damage', 'v' => 7.0]]],
        'gr_spider' => ['slot' => 'ring', 'base' => '毒涎之囊', 'rate' => 0.07, 'q' => [0, 0, 55, 45, 0], 'legendary' => ['base' => '毒涎之囊·蚀心', 'extra' => ['k' => 'penetration', 'v' => 8.0]]],
        'gr_retainer' => ['slot' => 'body', 'base' => '亲卫重铠', 'rate' => 0.08, 'q' => [0, 0, 50, 50, 0], 'legendary' => ['base' => '亲卫重铠·暴食', 'extra' => ['k' => 'hp', 'v' => 4000.0]]],
        'gr_shaman' => ['slot' => 'offhand', 'base' => '萨满图腾', 'rate' => 0.08, 'q' => [0, 0, 50, 50, 0], 'legendary' => ['base' => '萨满图腾·血祭', 'extra' => ['k' => 'magic_find', 'v' => 12.0]]],
        'gr_fang' => ['slot' => 'weapon', 'base' => '断牙之刃', 'rate' => 0.08, 'q' => [0, 0, 50, 50, 0], 'legendary' => ['base' => '断牙之刃·复仇', 'extra' => ['k' => 'critdmg', 'v' => 12.0]]],
        'gr_idol' => ['slot' => 'legs', 'base' => '王座傀儡甲', 'rate' => 0.08, 'q' => [0, 0, 50, 50, 0], 'legendary' => ['base' => '王座傀儡·朝拜', 'extra' => ['k' => 'cooldown', 'v' => 10.0]]],
        'gr_guru' => ['slot' => 'head', 'base' => '咕噜王冠', 'rate' => 0.35, 'q' => [0, 0, 10, 50, 40], 'legendary' => ['base' => '咕噜王冠·暴食君王', 'extra' => ['k' => 'exp_gain', 'v' => 15.0]]],
        'gr_brood' => ['slot' => 'gloves', 'base' => '蠕虫母巢丝', 'rate' => 0.08, 'q' => [0, 0, 50, 50, 0], 'legendary' => ['base' => '蠕虫母巢·繁育', 'extra' => ['k' => 'gold_gain', 'v' => 15.0]]],
        'gr_shard' => ['slot' => 'ring', 'base' => '碎片灵核', 'rate' => 0.08, 'q' => [0, 0, 50, 50, 0], 'legendary' => ['base' => '碎片灵核·虚无', 'extra' => ['k' => 'resist', 'v' => 30.0]]],
        'gr_shadow' => ['slot' => 'shoes', 'base' => '咕噜之影靴', 'rate' => 0.08, 'q' => [0, 0, 50, 50, 0], 'legendary' => ['base' => '咕噜之影·真容', 'extra' => ['k' => 'dodge', 'v' => 5.0]]],
        'gr_nightmare' => ['slot' => 'weapon', 'base' => '梦魇之角', 'rate' => 0.09, 'q' => [0, 0, 45, 55, 0], 'legendary' => ['base' => '梦魇之角·惊醒', 'extra' => ['k' => 'execute', 'v' => 8.0]]],
        'gr_ancient' => ['slot' => 'necklace', 'base' => '远古暴食者之胃', 'rate' => 0.15, 'q' => [0, 0, 30, 50, 20], 'legendary' => ['base' => '远古暴食者·吞天', 'extra' => ['k' => 'truedmg', 'v' => 400.0]]],
        'ry_crawler' => ['slot' => 'gloves', 'base' => '岩狱·爬行护手', 'rate' => 0.12, 'q' => [0, 20, 50, 30, 0]],
        'ry_guard' => ['slot' => 'body', 'base' => '岩狱·守卫岩甲', 'rate' => 0.12, 'q' => [0, 20, 50, 30, 0]],
        'ry_shaper' => ['slot' => 'head', 'base' => '岩狱·塑岩盔', 'rate' => 0.12, 'q' => [0, 20, 50, 30, 0]],
        'ry_warder' => ['slot' => 'legs', 'base' => '岩狱·狱卒腿甲', 'rate' => 0.12, 'q' => [0, 20, 50, 30, 0]],
        'ry_titan' => ['slot' => 'weapon', 'base' => '岩狱·泰坦之握', 'rate' => 0.3, 'q' => [0, 0, 30, 50, 20], 'legendary' => ['base' => '岩狱泰坦·岳柱', 'extra' => ['k' => 'damage_reduction', 'v' => 15.0]]],
        'corrupt_rat' => ['slot' => 'gloves', 'base' => '腐化鼠皮手套', 'rate' => 0.03, 'q' => [60, 35, 5, 0, 0]],
        'deep_bat' => ['slot' => 'shoes', 'base' => '深渊蝠翼靴', 'rate' => 0.04, 'q' => [20, 50, 30, 0, 0]],
        'runaway_miner' => ['slot' => 'weapon', 'base' => '矿工重锤', 'rate' => 0.05, 'q' => [0, 35, 45, 20, 0]],
        'abyss_spore' => ['slot' => 'necklace', 'base' => '孢子呼吸器', 'rate' => 0.06, 'q' => [0, 20, 50, 30, 0], 'legendary' => ['base' => '孢子呼吸器·万菌共鸣', 'extra' => ['k' => 'summon_power', 'v' => 30.0]]],
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
        'valentin' => ['slot' => 'necklace', 'base' => '城主徽记', 'rate' => 0.3, 'q' => [0, 0, 20, 50, 30], 'legendary' => ['base' => '城主徽记·统御', 'extra' => ['k' => 'summon_power', 'v' => 25.0]]],
        'dark_lily' => ['slot' => 'back', 'base' => '暗影披风'],
        'abyss_eye' => ['slot' => 'weapon', 'base' => '深渊之瞳', 'rate' => 0.5, 'q' => [0, 0, 0, 70, 30], 'legendary' => ['base' => '深渊之瞳·万渊', 'extra' => ['k' => 'summon_hp', 'v' => 80.0]]],
        'shadow_walker' => ['slot' => 'gloves', 'base' => '深渊护手'],
        'corrupt_warder' => ['slot' => 'body', 'base' => '蚀影甲'],
        'whisperer' => ['slot' => 'back', 'base' => '低语披风'],
        'colossus' => ['slot' => 'body', 'base' => '巨像重铠'],
        'abaddon' => ['slot' => 'weapon', 'base' => '君王蚀影刃', 'rate' => 0.5, 'q' => [0, 0, 0, 70, 30], 'legendary' => ['base' => '影蚀·万魂蚀影', 'extra' => ['k' => 'summon_power', 'v' => 60.0]]],
        'silk_spider' => ['slot' => 'body', 'base' => '剧场·幕布甲'],
        'leech' => ['slot' => 'belt', 'base' => '幽灵束带', 'rate' => 0.04, 'q' => [50, 35, 15, 0, 0]],
        'cocoon_guard' => ['slot' => 'legs', 'base' => '剧场·悬丝裤'],
        'thread_weaver' => ['slot' => 'gloves', 'base' => '剧场·操线手套'],
        'silk_moth' => ['slot' => 'head', 'base' => '剧场·假面'],
        'nest_watcher' => ['slot' => 'shoes', 'base' => '剧场·无声靴'],
        'brood_maiden' => ['slot' => 'offhand', 'off' => 'magic', 'base' => '剧场·提线灯'],
        'silk_mother' => ['slot' => 'weapon', 'base' => '剧场·断线刃'],
        // 第四章·冰原
        'frost_wolf' => ['slot' => 'weapon', 'base' => '寒霜·战斧'],
        'ice_specter' => ['slot' => 'offhand', 'off' => 'magic', 'base' => '寒霜·法杖'],
        'snow_mammoth' => ['slot' => 'body', 'base' => '雪原皮甲'],
        'blizzard_shaman' => ['slot' => 'weapon', 'base' => '寒霜·战锤'],
        'glacier_golem' => ['slot' => 'offhand', 'off' => 'shield', 'base' => '寒霜·冰盾'],
        'frost_giant' => ['slot' => 'legs', 'base' => '寒霜·胫甲'],
        'aurora_serpent' => ['slot' => 'weapon', 'base' => '寒霜·长弓'],
        'icewyrm' => ['slot' => 'ring', 'base' => '冰渊指环'],
        'winter_knight' => ['slot' => 'body', 'base' => '寒霜·骑士铠'],
        'frost_matriarch' => ['slot' => 'head', 'base' => '霜母之冠'],
        'glacier_colossus' => ['slot' => 'body', 'base' => '冰河重铠'],
        'star_puppet' => ['slot' => 'belt', 'base' => '剧场·束腰', 'rate' => 0.06, 'q' => [0, 30, 50, 20, 0]],
        'winter_king' => ['slot' => 'head', 'base' => '凛冬王冠', 'rate' => 0.3, 'q' => [0, 0, 20, 50, 30], 'legendary' => ['base' => '凛冬王冠·永冻', 'extra' => ['k' => 'damage_reduction', 'v' => 3.0]]],
        'fr_crawler' => ['slot' => 'gloves', 'base' => '冰渊手套'],
        'fr_stalker' => ['slot' => 'shoes', 'base' => '渊底无声靴'],
        'fr_warden' => ['slot' => 'body', 'base' => '守望者冰甲'],
        'fr_horror' => ['slot' => 'back', 'base' => '恐惧冰披'],
        'fr_leviathan' => ['slot' => 'necklace', 'base' => '利维坦之牙', 'rate' => 0.3, 'q' => [0, 0, 20, 50, 30], 'legendary' => ['base' => '利维坦之牙·深渊', 'extra' => ['k' => 'lifesteal', 'v' => 2.0]]],
        // 第五章·雾森与火山
        'mist_treant' => ['slot' => 'body', 'base' => '雾森树皮甲'],
        'spore_colony' => ['slot' => 'head', 'base' => '孢子冠'],
        'thorn_hunter' => ['slot' => 'offhand', 'base' => '烬焰·余烬囊'],
        'ancient_boa' => ['slot' => 'legs', 'base' => '巨蚺皮裙'],
        'mist_apeman' => ['slot' => 'gloves', 'base' => '雾猿拳套'],
        'rot_sage' => ['slot' => 'offhand', 'off' => 'magic', 'base' => '腐叶法器'],
        'ember_imp' => ['slot' => 'ring', 'base' => '余烬指环'],
        'lava_hound' => ['slot' => 'shoes', 'base' => '熔岩蹄靴'],
        'ash_cultist' => ['slot' => 'weapon', 'base' => '烬焰·灰烬法杖'],
        'magma_golem' => ['slot' => 'offhand', 'off' => 'shield', 'base' => '烬焰·熔盾'],
        'flame_djinn' => ['slot' => 'necklace', 'base' => '烬焰·余火链'],
        'cinder_dragonspawn' => ['slot' => 'weapon', 'base' => '烬焰·龙裔刃'],
        'volcano_priest' => ['slot' => 'head', 'base' => '烬焰·祭司冠'],
        'ash_wyrm' => ['slot' => 'back', 'base' => '灰烬龙翼'],
        'south_flame_guard' => ['slot' => 'body', 'base' => '烬焰·守卫铠'],
        'ancient_ruin_golem' => ['slot' => 'offhand', 'off' => 'shield', 'base' => '遗迹壁垒', 'rate' => 0.3, 'q' => [0, 0, 20, 50, 30], 'legendary' => ['base' => '遗迹壁垒·不陨', 'extra' => ['k' => 'damage_reduction', 'v' => 4.0]]],
        'em_slag' => ['slot' => 'legs', 'base' => '烬焰·熔渣腿'],
        'tt_rock' => ['slot' => 'body', 'base' => '岩狱·岩铠', 'rate' => 0.12, 'q' => [0, 20, 50, 30, 0]],
        'tt_crag' => ['slot' => 'weapon', 'base' => '岩狱·峭刃', 'rate' => 0.12, 'q' => [0, 20, 50, 30, 0]],
        'tt_magma' => ['slot' => 'head', 'base' => '岩狱·熔盔', 'rate' => 0.12, 'q' => [0, 20, 50, 30, 0]],
        'tt_warden' => ['slot' => 'offhand', 'base' => '岩狱·狱盾', 'rate' => 0.12, 'q' => [0, 20, 50, 30, 0]],
        'tt_rock2' => ['slot' => 'legs', 'base' => '岩狱·岩裙', 'rate' => 0.1, 'q' => [0, 20, 50, 30, 0]],
        'tt_crag2' => ['slot' => 'gloves', 'base' => '岩狱·峭手', 'rate' => 0.1, 'q' => [0, 20, 50, 30, 0]],
        'tt_magma2' => ['slot' => 'shoes', 'base' => '岩狱·熔靴', 'rate' => 0.1, 'q' => [0, 20, 50, 30, 0]],
        'tt_warden2' => ['slot' => 'belt', 'base' => '岩狱·狱带', 'rate' => 0.1, 'q' => [0, 20, 50, 30, 0]],
        'tt_rock3' => ['slot' => 'ring', 'base' => '岩狱·岩戒', 'rate' => 0.08, 'q' => [0, 20, 50, 30, 0]],
        'tt_crag3' => ['slot' => 'necklace', 'base' => '岩狱·峭链', 'rate' => 0.08, 'q' => [0, 20, 50, 30, 0]],
        'tt_titan' => ['slot' => 'body', 'base' => '岩狱·泰坦岩铠', 'rate' => 0.3, 'q' => [0, 0, 20, 50, 30], 'legendary' => ['base' => '岩狱泰坦·岳柱', 'extra' => ['k' => 'damage_reduction', 'v' => 8.0]]],
        'em_fiend' => ['slot' => 'weapon', 'base' => '烬焰·焚心弓'],
        'em_magma' => ['slot' => 'weapon', 'base' => '烬焰·熔核锤'],
        'em_ashlord' => ['slot' => 'ring', 'base' => '烬焰·灰烬戒'],
        'em_heart' => ['slot' => 'offhand', 'off' => 'magic', 'base' => '熔心', 'rate' => 0.3, 'q' => [0, 0, 20, 50, 30], 'legendary' => ['base' => '熔心·世界之血', 'extra' => ['k' => 'skill_damage', 'v' => 8.0]]],
        'pz_puller' => ['slot' => 'weapon', 'base' => '傀儡·断线匕'],
        'pz_dancer' => ['slot' => 'shoes', 'base' => '傀儡·舞步靴'],
        'pz_mask' => ['slot' => 'head', 'base' => '傀儡·覆面'],
        'pz_golem' => ['slot' => 'body', 'base' => '傀儡·守卫甲'],
        'frost_wolf' => ['slot' => 'belt', 'base' => '寒霜·束腰', 'rate' => 0.06, 'q' => [0, 30, 50, 20, 0]],
        'pz_master' => ['slot' => 'offhand', 'off' => 'magic', 'base' => '傀儡·大师之手', 'rate' => 0.3, 'q' => [0, 0, 20, 50, 30], 'legendary' => ['base' => '傀儡大师·提线', 'extra' => ['k' => 'skill_damage', 'v' => 6.0]]],
        'sen_priest' => ['slot' => 'offhand', 'off' => 'magic', 'base' => '荆棘·雾祭铃'],
        'sen_thorn' => ['slot' => 'body', 'base' => '荆棘·刺甲'],
        'sen_guard' => ['slot' => 'legs', 'base' => '荆棘·守卫裙'],
        'sen_serpent' => ['slot' => 'ring', 'base' => '荆棘·蛇牙戒'],
        'sen_guard' => ['slot' => 'legs', 'base' => '荆棘·守卫裙'],
        'dark_slime' => ['slot' => 'belt', 'base' => '深渊缚带', 'rate' => 0.06, 'q' => [0, 40, 45, 15, 0]],
        'sen_avatar' => ['slot' => 'necklace', 'base' => '雾森之心', 'rate' => 0.3, 'q' => [0, 0, 20, 50, 30], 'legendary' => ['base' => '雾森之心·永绿', 'extra' => ['k' => 'lifesteal', 'v' => 2.0]]],
        'shadow_claw' => ['slot' => 'weapon', 'base' => '影爪', 'rate' => 0.2, 'q' => [0, 0, 30, 50, 20], 'legendary' => ['base' => '影爪领主·撕夜', 'extra' => ['k' => 'crit', 'v' => 3.0]]],
        'brass_bishop' => ['slot' => 'head', 'base' => '黄铜冠', 'rate' => 0.2, 'q' => [0, 0, 30, 50, 20], 'legendary' => ['base' => '黄铜主教·赦令', 'extra' => ['k' => 'damage_reduction', 'v' => 3.0]]],
        'tundra_wanderer' => ['slot' => 'back', 'base' => '漫游者斗篷', 'rate' => 0.2, 'q' => [0, 0, 30, 50, 20], 'legendary' => ['base' => '漫游者·无疆', 'extra' => ['k' => 'move_speed', 'v' => 5.0]]],
        'lucky_goblin' => ['slot' => 'ring', 'base' => '幸运金币', 'rate' => 0.02, 'q' => [0, 0, 0, 60, 40], 'legendary' => ['base' => '幸运哥布林·大金币', 'extra' => ['k' => 'gold_gain', 'v' => 10.0]]],
        'golden_slime' => ['slot' => 'necklace', 'base' => '金色黏液', 'rate' => 0.03, 'q' => [0, 0, 20, 60, 20], 'legendary' => ['base' => '黄金史莱姆·王冠', 'extra' => ['k' => 'exp_gain', 'v' => 8.0]]],
        'mimic_chest' => ['slot' => 'body', 'base' => '宝箱甲', 'rate' => 0.05, 'q' => [0, 0, 30, 50, 20], 'legendary' => ['base' => '宝箱怪·贪婪之胃', 'extra' => ['k' => 'magic_find', 'v' => 10.0]]],
        'vx_spawn' => ['slot' => 'weapon', 'base' => '烬焰·龙裔刃'],
        'vx_stalker' => ['slot' => 'shoes', 'base' => '寒霜·无声靴'],
        'vx_mage' => ['slot' => 'offhand', 'off' => 'magic', 'base' => '烬焰·灰烬法杖'],
        'vx_knight' => ['slot' => 'body', 'base' => '烬焰·守卫铠'],
        'vx_herald' => ['slot' => 'necklace', 'base' => '烬焰·余火链'],
        'vx_tyrant' => ['slot' => 'ring', 'base' => '虚空暴君之戒', 'rate' => 0.3, 'q' => [0, 0, 20, 50, 30], 'legendary' => ['base' => '虚空暴君·终焉', 'extra' => ['k' => 'skill_damage', 'v' => 8.0]]],
    ] + world_expansion_drops() + region_dungeon_drops())[$mid] ?? [];
}

// 箭袋：猎手专属副手（名字含"箭袋"），与法器/盾不同，走猎人的物理词条
function is_quiver(string $base): bool
{
    return str_contains($base, '箭袋');
}

function equip_primary(string $slot, string $offKind = '', string $base = ''): string
{
    if ($slot === 'offhand') {
        // 箭袋：主属性给暴击率（猎手向），而不是法系的元素伤害
        if (is_quiver($base)) {
            return 'crit';
        }
        return $offKind === 'shield' ? 'def' : 'element';
    }
    if ($slot === 'ring') {
        // 戒指主属性：全元素抗性（6系全吃，战斗里已对每系叠加）
        return 'allres';
    }
    return ['weapon' => 'atk', 'body' => 'def', 'head' => 'def', 'legs' => 'def', 'gloves' => 'crit', 'shoes' => 'dodge', 'back' => 'dodge', 'necklace' => 'energy', 'belt' => 'bagcap'][$slot] ?? '';
}

// 武器/法器属于哪一路伤害：法系（杖/魂契器）只出法攻词条，物理系只出物攻词条
function equip_damage_type(string $slot, string $base, string $offKind = ''): string
{
    if ($slot === 'weapon') {
        return in_array(weapon_sub((string) $base), ['staff', 'relic'], true) ? 'magic' : 'phys';
    }
    if ($slot === 'offhand') {
        return ($offKind === 'shield' || is_quiver($base)) ? 'phys' : 'magic';
    }
    // 防具与首饰全职业通用，不做偏向
    return 'any';
}

// 副词条池：按伤害路数剔掉用不上的攻击词条，避免法系刷出物攻、物理刷出法攻
function equip_sub_pool(string $slot, string $base, string $primary, string $legendId = '', string $offKind = ''): array
{
    // 箭袋：猎人物理副词条池（暴击/暴伤/攻速/穿透/元素/物攻/物攻%/生命/物防），不出法攻/召唤词条
    if ($slot === 'offhand' && is_quiver($base)) {
        $pool = ['crit', 'critdmg', 'speed', 'penetration', 'element', 'atk', 'atk_pct', 'hp', 'def'];
        return array_values(array_filter($pool, fn($id) => $id !== $primary && ($legendId === '' || $id !== $legendId)));
    }
    $pool = array_values(array_filter(
        slot_pools()[$slot] ?? ['atk', 'def', 'hp'],
        fn($id) => $id !== $primary && $id !== $legendId
    ));
    $type = equip_damage_type($slot, $base, $offKind);
    if ($type === 'magic') {
        // 法系武器/法器：物攻与物攻%完全无用，且不能和主词条重复算
        $pool = array_values(array_filter($pool, fn($id) => !in_array($id, ['atk', 'atk_pct'], true)));
    } elseif ($type === 'phys') {
        $pool = array_values(array_filter($pool, fn($id) => !in_array($id, ['matk', 'matk_pct'], true)));
    }
    return array_values(array_unique($pool));
}

function equip_reqs(int $itemLevel, int $q): array
{
    $lv = max(1, (int) $itemLevel);
    $req = ['lv' => $lv, 'str' => 0, 'agi' => 0];
    if ($lv < 300) {
        return $req;
    }
    if ($q >= 4) {
        // 传奇：主属性取同级预期的55%，力量装要力量，坚毅装要坚毅
        $main = (int) (($lv - 280) * 1.5 + 120);
        $req['str'] = $main;
    } elseif ($q === 3 || ($lv >= 280 && $q >= 0)) {
        // 套装/史诗：主属性取同级预期的40%
        $main = (int) (($lv - 280) * 1.1 + 80);
        $req['str'] = $main;
    }
    return $req;
}

function equip_req_text(array $e): string
{
    $t = '需' . (int) ($e['req_lv'] ?? 1) . '级';
    if ((int) ($e['req_str'] ?? 0) > 0) {
        $t .= ' 力' . (int) $e['req_str'];
    }
    if ((int) ($e['req_agi'] ?? 0) > 0) {
        $t .= ' 毅' . (int) $e['req_agi'];
    }
    return $t;
}

// 装备详情用：哪些职业能穿（旧函数，统一委托到 equip_jobs_text 保持一致）
function equip_job_text(array $e): string
{
    return equip_jobs_text($e);
}

function equip_jobs_text(array $e): string
{
    $slot = (string) ($e['slot'] ?? '');
    $nm = (string) ($e['name'] ?? '');
    if ($slot === 'title') { return '全职业（100级可戴）'; }
    $jobs = equip_job_list($e);
    $all = ['warrior', 'mage', 'hunter', 'priest', 'summoner'];
    if (count($jobs) >= 5 || !array_diff($all, $jobs)) {
        return '全职业（战法猎牧召）';
    }
    $names = ['warrior' => '战士', 'mage' => '法师', 'hunter' => '猎手', 'priest' => '牧师', 'summoner' => '召唤师'];
    $labels = array_map(fn($j) => $names[$j] ?? $j, $jobs);
    // 单一职业限定 → 统一用「仅X」；多职业限定 → 「X/Y」
    if (count($labels) === 1) {
        return '仅' . $labels[0];
    }
    return implode('/', $labels);
}

// 一件装备能被哪些职业穿戴（job id 数组，按固定顺序）
function equip_job_list(array $e): array
{
    $slot = (string) ($e['slot'] ?? '');
    $nm = (string) ($e['name'] ?? '');
    $order = ['warrior', 'mage', 'hunter', 'priest', 'summoner'];
    if ($slot === 'weapon') {
        $jobs = weapon_jobs(weapon_sub($nm));
        // 双手武器：只有能真正抡动的职业才算（如双手锤/斧/大剑仅战士，双手法杖法师，长弓猎手），
        // 否则「可装备」会错误地把牧师等列到双手重锤上（实际穿戴会被拒）。
        if (weapon_is_twohand($nm)) {
            $jobs = array_values(array_filter($jobs, fn($j) => job_can_twohand_weapon($j, $nm)));
        }
    } elseif ($slot === 'offhand') {
        if (str_contains($nm, '盾')) {
            $jobs = ['warrior', 'priest'];
        } elseif (str_contains($nm, '法器') || str_contains($nm, '法杖') || str_contains($nm, '提灯')) {
            $jobs = ['mage', 'priest'];
        } elseif (str_contains($nm, '契匣') || str_contains($nm, '魂匣') || str_contains($nm, '灵幡') || summoner_is_box($nm)) {
            $jobs = ['summoner'];
        } elseif (str_contains($nm, '箭袋')) {
            $jobs = ['hunter'];
        } else {
            $jobs = $order;
        }
    } elseif ($slot === 'title') {
        $jobs = $order;
    } else {
        $jobs = $order;
    }
    // 去重并按固定职业顺序排列
    $jobs = array_values(array_intersect($order, array_unique($jobs)));
    return $jobs === [] ? $order : $jobs;
}

function equip_can_wear(array $u, array $e): string
{
    if ((int) ($u['lv'] ?? 1) < (int) ($e['req_lv'] ?? 1)) {
        return '等级不够（需' . (int) ($e['req_lv'] ?? 1) . '级）。';
    }
    if ((int) ($u['str'] ?? 0) < (int) ($e['req_str'] ?? 0)) {
        return '力量不够（需' . (int) ($e['req_str'] ?? 0) . '）。';
    }
    if ((int) ($u['agi'] ?? 0) < (int) ($e['req_agi'] ?? 0)) {
        return '坚毅不够（需' . (int) ($e['req_agi'] ?? 0) . '）。';
    }
    $job = job_id_of($u);
    $slot = (string) ($e['slot'] ?? '');
    if ($slot === 'weapon') {
        $sub = weapon_sub((string) ($e['name'] ?? ''));
        if (!in_array($job, weapon_jobs($sub), true)) {
            return '拿不动：' . (rebirth1_names()[$job] ?? job_of($u)['name']) . '用不了' . (['staff' => '法杖', 'axe' => '战斧', 'bow' => '弓弩', 'mace' => '战锤', 'shield' => '盾牌', 'blade' => '刀剑', 'relic' => '魂契器'][$sub] ?? '这种武器') . '。';
        }
    }
    if ($slot === 'offhand') {
        $nm = (string) ($e['name'] ?? '');
        if (str_contains($nm, '盾') && !in_array($job, ['warrior', 'priest'], true)) {
            return '拿不动：只有战士/牧师能举盾。';
        }
        if ((str_contains($nm, '法器') || str_contains($nm, '法杖') || str_contains($nm, '提灯')) && !in_array($job, ['mage', 'priest'], true)) {
            return '拿不动：法器只有法师/牧师能用。';
        }        if (str_contains($nm, '契匣') || str_contains($nm, '魂匣') || str_contains($nm, '灵幡') || summoner_is_box($nm)) {
            if ($job !== 'summoner') {
                return '封不住：契匣只有召唤师能契。';
            }
        }
        if (str_contains($nm, '箭袋') && $job !== 'hunter') {
            return '拿不动：箭袋只有猎手能背。';
        }
    }
    return '';
}

function make_equip(int $uid, string $slot, string $base, int $q, int $itemLevel = 1, ?array $legend = null, string $offKind = ''): string
{
    // 防御：非法部位一律不入库，避免产生"没有部位/名字不对/没有属性"的奇怪装备
    if (!isset(equip_slots()[$slot])) {
        return '';
    }
    $toGround = bag_full((int) $uid);
    $req = equip_reqs(max(1, $itemLevel), $q);
    $isStr = in_array($slot, ['weapon', 'body', 'head', 'legs', 'gloves'], true);
    $reqLv = $req['lv'];
    $reqStr = $isStr ? $req['str'] : 0;
    $reqAgi = !$isStr ? $req['str'] : 0;
    $primary = equip_primary($slot, $offKind, (string) $base);
    if ($slot === 'weapon' && in_array(weapon_sub((string) $base), ['staff', 'relic'], true)) {
        $primary = 'matk';
    }
    // 召唤师专属主副手主词条：魂契器(主手)→召唤兽伤害，深渊契匣(副手)→召唤兽生命
    if ($slot === 'weapon' && weapon_sub((string) $base) === 'relic') {
        $primary = 'summon_power';
    }
    if ($slot === 'offhand' && function_exists('summoner_offhands') && isset(summoner_offhands()[(string) $base])) {
        $primary = 'summon_hp';
    }
    $legendId = (string) ($legend['extra']['k'] ?? '');
    // BOSS 专属传说：按基底名匹配到行为型传说特效后，统一覆盖成对应的 lg_* 词条
    // （掉落表不用逐条改，也能兜住任何漏配的传说）
    if ($q === 4 && function_exists('boss_legendary_key_by_base')) {
        $__blKey = boss_legendary_key_by_base((string) $base);
        if ($__blKey !== '') {
            $legend = ['base' => (string) $base, 'extra' => ['k' => $__blKey, 'v' => 1]];
            $legendId = $__blKey;
        }
    }
    if ($q === 4 && $legendId === '' && $slot !== 'soul' && !str_contains((string) $base, '·')) {
        // 传说装备必须带专属效果：没有专属词条的传说自动补一条
        // （神魂、以及套装/神铸/罪渊/召唤专属等自带效果的不在此列）
        $legendDefaults = [
            'weapon' => ['truedmg', 60.0], 'body' => ['damage_reduction', 5.0], 'head' => ['mdef', 60.0],
            'legs' => ['hp', 1500.0], 'gloves' => ['lifesteal', 2.0], 'shoes' => ['crit', 3.0],
            'back' => ['hp', 1200.0], 'necklace' => ['hp', 1200.0], 'belt' => ['hp', 1200.0],
            'ring' => ['crit', 3.0], 'offhand' => ['def', 40.0],
        ];
        [$legendDefK, $legendDefV] = $legendDefaults[$slot] ?? ['atk', 20.0];
        $legend = ['extra' => ['k' => $legendDefK, 'v' => $legendDefV]];
        $legendId = $legendDefK;
    }
    $legendGroup = $legendId !== '' ? (string) (affix_catalog()[$legendId]['data']['group'] ?? '') : '';
    $pool = equip_sub_pool($slot, (string) $base, $primary, $legendId, $offKind);
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
    $st = db()->prepare('INSERT INTO equips (uid, slot, name, quality, affixes, pos, item_level, req_lv, req_str, req_agi) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $st->execute([$toGround ? 0 : (int) $uid, $slot, $name, $q, json_encode($affixes, JSON_UNESCAPED_UNICODE), $toGround ? 'ground' : '', max(1, $itemLevel), $reqLv, $reqStr, $reqAgi]);
    if ($toGround) {
        $eid = (int) db()->lastInsertId();
        $uo = user_by_id((int) $uid);
        ground_place_equip((string) ($uo['loc'] ?? 'town_sq'), $eid);
        return '';
    }
    $as = auto_sell_check((int) $uid, (int) $q);
    if ($as !== '') { return equip_shortname($name) . $as; }
    return $name;
}

// 一次性迁移：把玩家手里"没有专属效果的传说装备"补上专属词条
function migrate_plain_legendaries(int $uid): void
{
    if ((int) (mats_of($uid)['legend_fixed'] ?? 0) > 0) {
        return;
    }
    $defaults = [
        'weapon' => ['truedmg', 60.0], 'body' => ['damage_reduction', 5.0], 'head' => ['mdef', 60.0],
        'legs' => ['hp', 1500.0], 'gloves' => ['lifesteal', 2.0], 'shoes' => ['crit', 3.0],
        'back' => ['hp', 1200.0], 'necklace' => ['hp', 1200.0], 'belt' => ['hp', 1200.0],
        'ring' => ['crit', 3.0], 'offhand' => ['def', 40.0],
    ];
    $st = db()->prepare('SELECT id, slot, name, affixes FROM equips WHERE uid=? AND quality=4');
    $st->execute([$uid]);
    $rows = $st->fetchAll();
    if ($rows) {
        $upd = db()->prepare('UPDATE equips SET affixes=? WHERE id=?');
        foreach ($rows as $r) {
            $slot = (string) $r['slot'];
            if ($slot === 'soul') {
                continue;
            }
            // 名称形如「传说·基底」，去掉品质前缀后再判断是否为套装/特殊（含·）
            $nm = (string) $r['name'];
            $baseName = str_contains($nm, '·') ? explode('·', $nm, 2)[1] : $nm;
            if (str_contains($baseName, '·')) {
                continue;
            }
            $affixes = json_decode((string) $r['affixes'], true);
            if (!is_array($affixes)) {
                continue;
            }
            $hasLegend = false;
            foreach ($affixes as $a) {
                if (!empty($a['legend'])) {
                    $hasLegend = true;
                    break;
                }
            }
            if ($hasLegend) {
                continue;
            }
            [$dk, $dv] = $defaults[$slot] ?? ['atk', 20.0];
            $affixes[] = ['id' => $dk, 'tier' => '特', 'v' => (float) $dv, 'legend' => true];
            $upd->execute([json_encode($affixes, JSON_UNESCAPED_UNICODE), (int) $r['id']]);
        }
    }
    mat_set($uid, 'legend_fixed', 1);
}

function monster_material(string $mid): array
{
    // 只有副本信物怪和矿工铭牌任务掉材料，其他怪一律不掉（卖钱+装备就够了）
    $fixed = [
        'runaway_miner' => ['mine_badge', '矿工铭牌'],
    ][$mid] ?? [];
    if ($fixed !== []) {
        return $fixed;
    }
    // 六圣柱小怪：区域首领掉龙王精华，其余掉本属性龙魂（套装只由斩龙产出）
    $dm = dragon_monster($mid);
    if ($dm === []) {
        return [];
    }
    $d = dragon_of_el((string) $dm['el']);
    if ($d === []) {
        return [];
    }
    $bosses = ['az_shrine_guard', 'gl_funeral_host', 'th_chain_dragon', 'da_nightmare', 'li_gold_herald'];
    if (in_array($mid, $bosses, true) && mt_rand(1, 100) <= 15) {
        return ['dragon_core', '龙王精华'];
    }
    return [(string) $d['soul'], '龙魂·' . element_name((string) $d['el'])];
}

function quest_mats(): array
{
    return ['blackcoin' => '不断下坠的黑币', 'aiden_badge' => '艾登·灰叶的铭牌', 'investigation_record' => '调查队记录', 'augustus_letter' => '镇长的信', 'dsw_ticket' => '黑暗沼泽副本入场券', 'dsw_item1' => '幽暗黏液', 'dsw_item2' => '沼泽之核', 'dsw_item3' => '腐泥之心', 'dsw_item4' => '王座徽记', 'abx_ticket' => '影蚀深渊入场券', 'abx_item1' => '蚀影尘', 'abx_item2' => '影蚀徽记', 'abx_item3' => '低语残章', 'abx_item4' => '王座蚀印', 'capital_badge' => '王都徽章', 'puppet_eye' => '傀儡之眼', 'ch3_proof' => '第三章通关证明', 'mx_ticket' => '银丝母巢入场券', 'mx_item1' => '黏丝束', 'mx_item2' => '茧壳碎片', 'mx_item3' => '织线梭', 'mx_item4' => '蛾翼磷粉', 'mx_item5' => '守望之瞳', 'mx_item6' => '育巢摇篮曲', 'farm_coin' => '农场币', 'seed_green' => '青菜种子', 'seed_radish' => '萝卜种子', 'seed_melon' => '南瓜种子', 'seed_tomato' => '番茄种子', 'seed_strawberry' => '草莓种子',
 'ice_badge' => '冰原徽章', 'frost_heart' => '霜王之心', 'ch4_proof' => '第四章通关证明', 'fr_ticket' => '冰渊副本入场券', 'fr_item1' => '渊底冰核', 'fr_item2' => '利维坦之牙',
 'forest_badge' => '森语徽章', 'south_flame_token' => '南炎信物', 'ch5_proof' => '第五章通关证明', 'rb2_proof' => '2觉证明', 'em_ticket' => '熔心副本入场券', 'pz_ticket' => '傀儡回廊入场券', 'tt_ticket' => '岩狱副本入场券', 'sen_ticket' => '雾森祭坛入场券', 'em_item1' => '熔核碎片', 'em_item2' => '熔心之血',
 'soul_shard' => '神魂碎片'];
}

function skill_catalog(): array
{
    return [
        // 战士：防战（吃双防、挨打按防御等比反击）/ 狂战（本场损血数值越多家伤越高）两个方向
        'w1' => ['name' => '铁壁格挡', 'job' => 'warrior', 'mp' => 8, 'kind' => 'phys', 'mult' => 1.2, 'el' => '', 'tank' => 1, 't10' => '反击伤害+30%', 'lv20' => '反击伤害再+30%，你减伤+10%'],
        'w2' => ['name' => '怒斩', 'job' => 'warrior', 'mp' => 18, 'kind' => 'phys', 'mult' => 1.9, 'el' => 'fire', 'rage' => 1, 't10' => '狂战加成翻倍', 'lv20' => '伤害+15%，附魔火伤'],
        'm1' => ['name' => '火球术', 'job' => 'mage', 'mp' => 12, 'kind' => 'magic', 'mult' => 0, 'el' => 'fire', 't10' => '伤害+30%', 'lv20' => '伤害+15%'],
        'm2' => ['name' => '狂雷术', 'job' => 'mage', 'mp' => 22, 'kind' => 'magic', 'mult' => 0, 'el' => 'thunder', 't10' => '12%麻痹：敌停一轮', 'lv20' => '伤害+15%'],
        'h1' => ['name' => '双连射', 'job' => 'hunter', 'mp' => 10, 'kind' => 'multi2', 'mult' => 1.4, 'el' => '', 'dot' => 'bleed', 't10' => '出血层数+1，可叠3层', 'lv20' => '每层出血伤害+25%'],
        'h2' => ['name' => '爆裂箭', 'job' => 'hunter', 'mp' => 20, 'kind' => 'multi2', 'mult' => 1.7, 'el' => 'fire', 'dot' => 'burn', 't10' => '焦灼改为溅射（伤害灌进队列）', 'lv20' => '焦灼伤害+40%'],
        'p1' => ['name' => '圣光弹', 'job' => 'priest', 'mp' => 10, 'kind' => 'heal', 'mult' => 0.7, 'heal' => 25, 'el' => 'light', 't10' => '治疗+15', 'lv20' => '治疗+20%，伤害+15%'],
        'p2' => ['name' => '圣光庇佑', 'job' => 'priest', 'mp' => 20, 'kind' => 'heal', 'mult' => 1.0, 'heal' => 45, 'el' => 'light', 't10' => '治疗+25', 'lv20' => '治疗+20%，伤害+15%'],
        'w3' => ['name' => '龟裂践踏', 'job' => 'warrior', 'mp' => 30, 'kind' => 'phys', 'mult' => 2.2, 'el' => 'thunder', 'tank' => 1, 't10' => '敌人攻击-15%持续3轮', 'lv20' => '伤害+15%，反击伤害+25%'],
        'm3' => ['name' => '寒霜新星', 'job' => 'mage', 'mp' => 32, 'kind' => 'magic', 'mult' => 0, 'el' => 'ice', 't10' => '5%冻结：敌攻-15%持续3轮', 'lv20' => '伤害+15%'],
        'h3' => ['name' => '淬毒箭', 'job' => 'hunter', 'mp' => 30, 'kind' => 'multi2', 'mult' => 1.8, 'el' => 'dark', 'dot' => 'poison', 't10' => '剧毒层数+1，可叠3层', 'lv20' => '剧毒每层+1%最大生命'],
        'p3' => ['name' => '神圣审判', 'job' => 'priest', 'mp' => 30, 'kind' => 'heal', 'mult' => 1.2, 'heal' => 60, 'el' => 'light', 't10' => '恢复10点魔力', 'lv20' => '治疗+20%，伤害+15%'],
        'w4' => ['name' => '燃血狂斩', 'job' => 'warrior', 'mp' => 40, 'kind' => 'phys', 'mult' => 2.6, 'el' => 'fire', 'rage' => 1, 't10' => '本次暴击率+25%', 'lv20' => '伤害+15%，附魔火伤'],
        'm4' => ['name' => '冰河葬', 'job' => 'mage', 'mp' => 42, 'kind' => 'magic', 'mult' => 0, 'el' => 'ice', 't10' => '伤害+30%', 'lv20' => '伤害+15%，10%冻结'],
        'h4' => ['name' => '猎龙箭', 'job' => 'hunter', 'mp' => 40, 'kind' => 'multi2', 'mult' => 2.4, 'el' => 'dark', 'dot' => 'bleed', 't10' => '龙裔杀手：对龙类伤害+50%', 'lv20' => '出血全部转化为真实伤害'],
        'p4' => ['name' => '熔心圣歌', 'job' => 'priest', 'mp' => 40, 'kind' => 'heal', 'mult' => 1.5, 'heal' => 90, 'el' => 'light', 't10' => '治疗+30', 'lv20' => '治疗+20%，伤害+15%'],
        'w5' => ['name' => '烈焰旋风', 'job' => 'warrior', 'mp' => 50, 'kind' => 'aoe', 'mult' => 2.0, 'el' => 'fire', 'rage' => 1, 't10' => '溅射+10%', 'lv20' => '伤害+15%'],
        'w6' => ['name' => '撼地重击', 'job' => 'warrior', 'mp' => 50, 'kind' => 'aoe', 'mult' => 1.8, 'el' => 'thunder', 'tank' => 1, 't10' => '10%眩晕', 'lv20' => '伤害+15%，减攻25%'],
        'm5' => ['name' => '暴风雪', 'job' => 'mage', 'mp' => 55, 'kind' => 'aoe', 'mult' => 1.8, 'el' => 'ice', 't10' => '伤害+30%，10%冻结', 'lv20' => '伤害+15%'],
        'm6' => ['name' => '燎原烈焰', 'job' => 'mage', 'mp' => 55, 'kind' => 'aoe', 'mult' => 1.8, 'el' => 'fire', 't10' => '伤害+30%', 'lv20' => '伤害+15%'],
        'h5' => ['name' => '万箭齐发', 'job' => 'hunter', 'mp' => 50, 'kind' => 'aoe', 'mult' => 2.2, 'el' => 'wind', 'dot' => 'bleed', 'ranger' => 1, 't10' => '全场叠2层出血', 'lv20' => '溅射率翻倍（100%）'],
        'h6' => ['name' => '影杀', 'job' => 'hunter', 'mp' => 50, 'kind' => 'step', 'mult' => 4.2, 'el' => 'dark', 'assassin' => 1, 't10' => '单体伤害+60%', 'lv20' => '斩杀线提高到40%'],
        'p5' => ['name' => '暗影新星', 'job' => 'priest', 'mp' => 55, 'kind' => 'aoe', 'mult' => 1.8, 'el' => 'dark', 't10' => '伤害+30%', 'lv20' => '伤害转奶40%'],
        'p6' => ['name' => '圣光普照', 'job' => 'priest', 'mp' => 55, 'kind' => 'aoe', 'mult' => 1.2, 'heal' => 120, 'el' => 'light', 't10' => '治疗+40', 'lv20' => '治疗+20%，队友70%'],
        'w7' => ['name' => '血怒狂斩', 'job' => 'warrior', 'mp' => 55, 'kind' => 'aoe', 'mult' => 2.2, 'el' => 'dark', 'rage' => 1, 't10' => '狂战加成翻倍，吸血+15%', 'lv20' => '伤害+15%，溅射+10%'],
        'w8' => ['name' => '壁垒雷狱', 'job' => 'warrior', 'mp' => 55, 'kind' => 'aoe', 'mult' => 1.6, 'el' => 'thunder', 'tank' => 1, 't10' => '敌攻-20%持续3轮', 'lv20' => '伤害+15%，减攻25%，反击伤害+30%'],
        'm7' => ['name' => '雷暴术', 'job' => 'mage', 'mp' => 60, 'kind' => 'aoe', 'mult' => 2.0, 'el' => 'thunder', 't10' => '25%麻痹', 'lv20' => '伤害+15%，麻痹30%'],
        'm8' => ['name' => '风火燎原', 'job' => 'mage', 'mp' => 60, 'kind' => 'aoe', 'mult' => 2.0, 'el' => 'wind', 't10' => '伤害+30%', 'lv20' => '伤害+15%，溅射+10%'],
        'h7' => ['name' => '穿云箭雨', 'job' => 'hunter', 'mp' => 55, 'kind' => 'aoe', 'mult' => 2.2, 'el' => 'wind', 'dot' => 'bleed', 'ranger' => 1, 't10' => '焦灼全场（无视抗性的持续灼烧）', 'lv20' => '焦灼每层伤害+50%'],
        'h8' => ['name' => '夜幕绞杀', 'job' => 'hunter', 'mp' => 55, 'kind' => 'step', 'mult' => 6.5, 'el' => 'dark', 'assassin' => 1, 't10' => '本次必暴击', 'lv20' => '斩杀线提高到60%'],
        'p7' => ['name' => '噬魂新星', 'job' => 'priest', 'mp' => 60, 'kind' => 'aoe', 'mult' => 2.0, 'el' => 'dark', 't10' => '伤害+30%', 'lv20' => '伤害转奶60%'],
        'p8' => ['name' => '圣光审判', 'job' => 'priest', 'mp' => 60, 'kind' => 'aoe', 'mult' => 1.0, 'heal' => 150, 'el' => 'light', 't10' => '治疗+50', 'lv20' => '治疗+20%，队友80%'],
        'w9' => ['name' => '血狱狂斩', 'job' => 'warrior', 'mp' => 45, 'kind' => 'sin', 'mult' => 3.6, 'el' => 'dark', 'rage' => 1, 't10' => '斩杀线40%', 'lv20' => '伤害+15%，斩杀线50%'],
        'm9' => ['name' => '魂蚀天雷', 'job' => 'mage', 'mp' => 50, 'kind' => 'sin', 'mult' => 4.2, 'el' => 'thunder', 't10' => '35%麻痹', 'lv20' => '伤害+15%，麻痹40%'],
        'h9' => ['name' => '弑神连珠', 'job' => 'hunter', 'mp' => 45, 'kind' => 'sin', 'mult' => 3.0, 'el' => 'dark', 'dot' => 'bleed', 't10' => '引爆目标身上全部DOT层数（每层转成一次额外伤害）', 'lv20' => '引爆伤害+50%'],        // 召唤师：召唤/腐蚀/献祭
        's1' => ['name' => '深渊契约', 'job' => 'summoner', 'mp' => 10, 'kind' => 'summon', 'mult' => 1.2, 'el' => 'dark', 't10' => '召唤1只深渊小鬼', 'lv20' => '小鬼生命+15%'],
        's2' => ['name' => '招魂引', 'job' => 'summoner', 'mp' => 18, 'kind' => 'summon', 'mult' => 1.4, 'el' => 'dark', 't10' => '召唤1只骸骨守卫（需熟练10级），血厚能扛', 'lv20' => '守卫生命+15%，伤害+15%'],
        's3' => ['name' => '血肉筑城', 'job' => 'summoner', 'mp' => 22, 'kind' => 'summon', 'mult' => 1.8, 'el' => 'dark', 't10' => '献祭20%生命召唤憎恶聚合体（需熟练10级），死时爆炸', 'lv20' => '聚合体生命+15%，爆炸+30%'],
        's4' => ['name' => '小鬼潮', 'job' => 'summoner', 'mp' => 30, 'kind' => 'summon', 'mult' => 1.5, 'el' => 'dark', 't10' => '召唤2只深渊小鬼', 'lv20' => '召唤3只，小鬼伤害+15%'],
        's5' => ['name' => '死灵之拥', 'job' => 'summoner', 'mp' => 40, 'kind' => 'summon', 'mult' => 2.2, 'el' => 'dark', 't10' => '召唤2只深渊恶鬼（2觉专属），死时爆炸', 'lv20' => '同时召唤多只，爆炸范围+50%'],
        's6' => ['name' => '噬魂夜宴', 'job' => 'summoner', 'mp' => 48, 'kind' => 'summon', 'mult' => 2.6, 'el' => 'dark', 't10' => '献祭30%生命召唤无面梦魇（2觉专属），最强', 'lv20' => '梦魇生命+15%，伤害+25%'],
        's7' => ['name' => '群魂爆裂', 'job' => 'summoner', 'mp' => 52, 'kind' => 'aoe', 'mult' => 2.6, 'el' => 'dark', 't10' => '引爆全部召唤兽，范围伤害', 'lv20' => '引爆伤害+40%'],
        's8' => ['name' => '永夜契约', 'job' => 'summoner', 'mp' => 52, 'kind' => 'summon', 'mult' => 2.8, 'el' => 'dark', 't10' => '召唤1只憎恶+1只恶鬼（需熟练10级）', 'lv20' => '再多1只恶鬼，伤害+25%'],
        's9' => ['name' => '深渊主宰', 'job' => 'summoner', 'mp' => 45, 'kind' => 'sin', 'mult' => 3.4, 'el' => 'dark', 't10' => '献祭全部召唤兽，全部伤害转化生命', 'lv20' => '伤害+15%，转化率翻倍'],
        'p9' => ['name' => '堕天圣裁', 'job' => 'priest', 'mp' => 50, 'kind' => 'sin', 'mult' => 3.0, 'heal' => 200, 'el' => 'dark', 't10' => '伤害转奶80%', 'lv20' => '治疗+20%，转奶100%'],
    ];
}

function job_skillset(string $job): array
{
    return ['warrior' => ['w1', 'w2', 'w3', 'w4', 'w5', 'w6', 'w7', 'w8', 'w9'], 'mage' => ['m1', 'm2', 'm3', 'm4', 'm5', 'm6', 'm7', 'm8', 'm9'], 'hunter' => ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'h7', 'h8', 'h9'], 'priest' => ['p1', 'p2', 'p3', 'p4', 'p5', 'p6', 'p7', 'p8', 'p9'], 'summoner' => ['s1', 's2', 's3', 's4', 's5', 's6', 's7', 's8', 's9']][$job] ?? ['w1', 'w2', 'w3', 'w4', 'w5', 'w6', 'w7', 'w8', 'w9'];
}

// 技能图鉴用：把技能的基础效果讲清楚（kind/威力/属性/流派/DOT）
function skill_effect_desc(array $c): string
{
    $kind = (string) ($c['kind'] ?? '');
    $mult = (float) ($c['mult'] ?? 0);
    $pw = '';
    if ($mult > 0) {
        $pw = '威力×' . rtrim(rtrim(number_format($mult, 2, '.', ''), '0'), '.');
    }
    switch ($kind) {
        case 'phys':
            $s = '物理攻击' . ($pw !== '' ? '，' . $pw : '');
            break;
        case 'magic':
            $s = '法术攻击（按法攻结算）';
            break;
        case 'multi2':
            $s = '连续多段攻击' . ($pw !== '' ? '，' . $pw : '');
            break;
        case 'heal':
            $s = '造成伤害并治疗自己' . (!empty($c['heal']) ? '（基础治疗+' . (int) $c['heal'] . '）' : '');
            break;
        case 'aoe':
            $s = '群体范围攻击' . ($pw !== '' ? '，' . $pw : '');
            break;
        case 'summon':
            $s = '召唤召唤兽助战（替你承伤与输出）';
            break;
        case 'sin':
            $s = '罪技能（需罪值≥100才能放）' . ($pw !== '' ? '，' . $pw : '');
            break;
        default:
            $s = '特殊效果';
    }
    $el = (string) ($c['el'] ?? '');
    if ($el !== '') {
        $s .= '，' . element_name($el) . '属性';
    }
    if (!empty($c['tank'])) {
        $s .= '，防御向（挨打按防御反击）';
    }
    if (!empty($c['rage'])) {
        $s .= '，狂战向（本场掉血越多伤害越高）';
    }
    if (!empty($c['dot'])) {
        $dotName = ['bleed' => '出血', 'burn' => '焦灼', 'poison' => '剧毒'][$c['dot']] ?? '持续伤害';
        $s .= '，附带' . $dotName;
    }
    return $s;
}

function skill_books(): array
{
    return [
        'book_w1' => ['skill' => 'w1', 'name' => '铁壁秘籍'],
        'book_w2' => ['skill' => 'w2', 'name' => '怒斩秘籍'],
        'book_m1' => ['skill' => 'm1', 'name' => '火球术秘籍'],
        'book_m2' => ['skill' => 'm2', 'name' => '狂雷术秘籍'],
        'book_h1' => ['skill' => 'h1', 'name' => '双连射秘籍'],
        'book_h2' => ['skill' => 'h2', 'name' => '爆裂箭秘籍'],
        'book_p1' => ['skill' => 'p1', 'name' => '治疗术秘籍'],
        'book_p2' => ['skill' => 'p2', 'name' => '圣光秘籍'],
        'book_w3' => ['skill' => 'w3', 'name' => '裂地秘籍'],
        'book_m3' => ['skill' => 'm3', 'name' => '寒霜秘籍'],
        'book_h3' => ['skill' => 'h3', 'name' => '淬毒秘籍'],
        'book_p3' => ['skill' => 'p3', 'name' => '审判秘籍'],
        'book_w4' => ['skill' => 'w4', 'name' => '烬灭秘籍'],
        'book_m4' => ['skill' => 'm4', 'name' => '冰河秘籍'],
        'book_h4' => ['skill' => 'h4', 'name' => '猎龙秘籍'],
        'book_p4' => ['skill' => 'p4', 'name' => '熔心秘籍'],
        'book_w5' => ['skill' => 'w5', 'name' => '旋风秘籍'],
        'book_w6' => ['skill' => 'w6', 'name' => '撼地秘籍'],
        'book_m5' => ['skill' => 'm5', 'name' => '暴风雪秘籍'],
        'book_m6' => ['skill' => 'm6', 'name' => '燎原秘籍'],
        'book_h5' => ['skill' => 'h5', 'name' => '箭雨秘籍'],
        'book_h6' => ['skill' => 'h6', 'name' => '影杀秘籍'],
        'book_p5' => ['skill' => 'p5', 'name' => '暗影秘籍'],
        'book_p6' => ['skill' => 'p6', 'name' => '圣光秘籍'],
        'book_w7' => ['skill' => 'w7', 'name' => '血怒秘籍'],
        'book_w8' => ['skill' => 'w8', 'name' => '壁垒秘籍'],
        'book_m7' => ['skill' => 'm7', 'name' => '雷暴秘籍'],
        'book_m8' => ['skill' => 'm8', 'name' => '风火秘籍'],
        'book_h7' => ['skill' => 'h7', 'name' => '穿云秘籍'],
        'book_h8' => ['skill' => 'h8', 'name' => '夜幕秘籍'],
        'book_p7' => ['skill' => 'p7', 'name' => '噬魂秘籍'],
        'book_p8' => ['skill' => 'p8', 'name' => '审判秘籍'],
        'book_w9' => ['skill' => 'w9', 'name' => '血狱秘籍'],
        'book_m9' => ['skill' => 'm9', 'name' => '魂蚀秘籍'],
        'book_h9' => ['skill' => 'h9', 'name' => '弑神秘籍'],
        'book_p9' => ['skill' => 'p9', 'name' => '堕天秘籍'],
        // 召唤师：s1~s8 的技能书（此前只有 s9，导致召唤师拿到的书学不了、也显示不出名字）
        'book_s1' => ['skill' => 's1', 'name' => '深渊契约秘籍'],
        'book_s2' => ['skill' => 's2', 'name' => '招魂引秘籍'],
        'book_s3' => ['skill' => 's3', 'name' => '血肉筑城秘籍'],
        'book_s4' => ['skill' => 's4', 'name' => '小鬼潮秘籍'],
        'book_s5' => ['skill' => 's5', 'name' => '死灵之拥秘籍'],
        'book_s6' => ['skill' => 's6', 'name' => '噬魂夜宴秘籍'],
        'book_s7' => ['skill' => 's7', 'name' => '群魂爆裂秘籍'],
        'book_s8' => ['skill' => 's8', 'name' => '永夜契约秘籍'],
        'book_s9' => ['skill' => 's9', 'name' => '深渊主宰秘籍'],
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

// 罪技白名单：五个职业各一个（w9/m9/h9/p9/s9）。统一入口，避免各处漏改。
// 罪值 < SIN_SKILL_NEED 时不能选用/激活，active_skill 会强制切走。
function sin_skills(): array
{
    return ['w9', 'm9', 'h9', 'p9', 's9'];
}

const SIN_SKILL_NEED = 100;

function active_skill(int $uid, string $job): ?array
{
    $set = job_skillset($job);
    $mine = [];
    foreach (my_skills($uid) as $s) {
        $mine[$s['skill']] = $s;
    }
    // 罪值掉下门槛：强制切掉罪技能
    $sinSkills = sin_skills();
    if (sin_of((int) $uid) < SIN_SKILL_NEED) {
        $needFix = false;
        foreach ($mine as $sid => $s) {
            if (in_array($sid, $sinSkills, true) && !empty($s['active'])) {
                $needFix = true;
            }
        }
        if ($needFix) {
            db()->exec('UPDATE skills SET active=0 WHERE uid=' . (int) $uid);
            foreach (array_reverse($set) as $sid) {
                if (isset($mine[$sid]) && !in_array($sid, $sinSkills, true)) {
                    db()->prepare('UPDATE skills SET active=1 WHERE uid=? AND skill=?')->execute([(int) $uid, $sid]);
                    break;
                }
            }
            $mine = [];
            foreach (my_skills($uid) as $s) {
                $mine[$s['skill']] = $s;
            }
        }
    }
    foreach ($mine as $sid => $s) {
        if (in_array($sid, $set, true) && !empty($s['active'])) {
            return $s + (skill_catalog()[$sid] ?? []);
        }
    }
    $sinLow = sin_of((int) $uid) < SIN_SKILL_NEED;
    foreach (array_reverse($set) as $sid) {
        if (!isset($mine[$sid])) {
            continue;
        }
        if ($sinLow && in_array($sid, sin_skills(), true)) {
            continue;
        }
        return $mine[$sid] + (skill_catalog()[$sid] ?? []);
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
    if (in_array($sid, sin_skills(), true) && sin_of((int) $uid) < SIN_SKILL_NEED) {
        return '罪技能需要罪值≥' . SIN_SKILL_NEED . '（当前' . sin_of((int) $uid) . '），先去杀怪积攒罪值。';
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
    if (in_array($sid, sin_skills(), true) && sin_of((int) $uid) < SIN_SKILL_NEED) {
        return '罪技能书需要罪值≥' . SIN_SKILL_NEED . '才能领悟（当前' . sin_of((int) $uid) . '）。';
    }
    $u = user_by_id($uid);
    if (!$u || job_id_of($u) !== $cat['job']) {
        return '职业不符，只有' . job_of(['job' => $cat['job']])['name'] . '能用。';
    }
    $needRb2 = ['w5' => 'berserker', 'w6' => 'aegis', 'm5' => 'wintermage', 'm6' => 'wildfire', 'h5' => 'ranger', 'h6' => 'assassin', 'p5' => 'shadowbishop', 'p6' => 'holybishop', 'w7' => 'berserker', 'w8' => 'aegis', 'm7' => 'wintermage', 'm8' => 'wildfire', 'h7' => 'ranger', 'h8' => 'assassin', 'p7' => 'shadowbishop', 'p8' => 'holybishop', 's5' => 'necro', 's6' => 'plague', 's7' => 'necro', 's8' => 'plague'][$sid] ?? '';
    if ($needRb2 !== '' && (cflags($uid)['rb2_job'] ?? '') !== $needRb2) {
        return '这是2觉【' . rebirth2_name($needRb2) . '】的群攻秘籍，先599级2觉再学。';
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

// 三级秘籍（各职业的第3本）有专属来源，不进普通掉落池，统一走深渊 boss 掉落。
// 召唤师的 s3 此前漏在池外，导致【血肉筑城秘籍】哪都不掉，这里集中定义为一处来源。
function tier3_skill_books(): array
{
    return ['book_w3', 'book_m3', 'book_h3', 'book_p3', 'book_s3'];
}

function roll_skillbook(int $uid, string $mid): string
{
    $rate = ['dark_slime' => 0.005, 'swamp_slime' => 0.008, 'swamp_king' => 0.05][$mid] ?? 0;
    if ($rate <= 0 || mt_rand() / mt_getrandmax() > $rate) {
        return '';
    }
    // 三级书有专属来源（深渊 boss，见 roll_abx_skillbook），不进这个池；
    // 其余各职业的书（含召唤师 s1/s2/s4~s9）都在池里。
    $t3 = tier3_skill_books();
    $books = array_values(array_filter(
        array_keys(skill_books()),
        fn($k) => !in_array($k, $t3, true)
    ));
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
    $books = tier3_skill_books();
    $b = $books[array_rand($books)];
    add_mat($uid, $b, 1);
    return skill_books()[$b]['name'];
}

function pct_potions(): array
{
    return [
        'hp_pct10' => ['name' => '雾纹绷带', 'pct' => 10, 'price' => 100, 'unit' => 'silver', 'stat' => 'hp'],
        'hp_pct20' => ['name' => '苔语药膏', 'pct' => 20, 'price' => 300, 'unit' => 'silver', 'stat' => 'hp'],
        'hp_pct35' => ['name' => '龙血愈合剂', 'pct' => 35, 'price' => 1200, 'unit' => 'silver', 'stat' => 'hp'],
        'hp_pct50' => ['name' => '月影圣水', 'pct' => 50, 'price' => 3000, 'unit' => 'silver', 'stat' => 'hp'],
        'mp_pct25' => ['name' => '蓝纹清液', 'pct' => 25, 'price' => 300, 'unit' => 'silver', 'stat' => 'mp'],
        'mp_pct50' => ['name' => '月露凝浆', 'pct' => 50, 'price' => 1500, 'unit' => 'silver', 'stat' => 'mp'],
    ];
}

function buy_pct_potion(int $uid, string $mid): string
{
    $tab = pct_potions()[$mid] ?? null;
    if ($tab === null) {
        return '没这种药。';
    }
    $u = user_by_id((int) $uid);
    if ($tab['unit'] === 'gold') {
        if ((int) ($u['gold'] ?? 0) < $tab['price']) {
            return '金币不够，要' . fmt_money($tab['price']) . '。';
        }
        $u['gold'] = (int) ($u['gold'] ?? 0) - $tab['price'];
    } else {
        if ((int) ($u['gold'] ?? 0) < $tab['price']) {
            return '铜币不够，要' . fmt_money($tab['price']) . '。';
        }
        $u['gold'] = (int) ($u['gold'] ?? 0) - $tab['price'];
    }
    user_save($u);
    add_mat((int) $uid, $mid, 1);
    return '买到【' . $tab['name'] . '】（回' . $tab['pct'] . '%血，战斗中手动喝）。';
}

// 血瓶溢出治疗：自己满血时转治疗召唤物和宠物（召唤物优先，再宠物）。
// 返回 log；$done=false 表示没人需要治疗，调用方不该扣药。
function drink_heal_allies(array &$u, string $mid, array $tab): array
{
    $pct = (int) $tab['pct'];
    $uid = (int) ($u['id'] ?? 0);
    $log = '';
    $done = false;
    // 召唤物（战斗中才会非空）：每只各回 pct% 生命
    $list = summon_list();
    foreach ($list as $i => $s) {
        $heal = max(0, min((int) $s['maxhp'] - (int) $s['hp'], (int) ((int) $s['maxhp'] * $pct / 100)));
        if ($heal > 0) {
            $list[$i]['hp'] = (int) $list[$i]['hp'] + $heal;
            $log .= $s['name'] . '恢复' . $heal . '点生命。';
            $done = true;
        }
    }
    if ($log !== '') {
        summon_save($list);
    }
    // 宠物（出战的那只）
    $p = active_pet($uid);
    if ($p) {
        $st = pet_stats($p);
        $heal = max(0, min((int) $st['maxhp'] - (int) $p['hp'], (int) ((int) $st['maxhp'] * $pct / 100)));
        if ($heal > 0) {
            db()->prepare('UPDATE pets SET hp=? WHERE id=?')->execute([(int) $p['hp'] + $heal, (int) $p['id']]);
            $log .= pet_species()[$p['species']]['name'] . '恢复' . $heal . '点生命。';
            $done = true;
        }
    }
    if (!$done) {
        return ['', false];
    }
    add_mat($uid, $mid, -1);
    return ['喝下【' . $tab['name'] . '】——你血是满的，药喂给了同伴：' . $log, true];
}

function drink_pct_potion(array &$u, string $mid): string
{
    $tab = pct_potions()[$mid] ?? null;
    if ($tab === null) {
        return '没这种药。';
    }
    $mats = mats_of((int) ($u['id'] ?? 0));
    if ((int) ($mats[$mid] ?? 0) <= 0) {
        return '你没有【' . $tab['name'] . '】。';
    }
    if (($tab['stat'] ?? 'hp') === 'mp') {
        if ((int) $u['mp'] >= (int) $u['maxmp']) {
            return '蓝是满的，别浪费药。';
        }
        add_mat((int) ($u['id'] ?? 0), $mid, -1);
        $heal = min((int) $u['maxmp'] - (int) $u['mp'], (int) ((int) $u['maxmp'] * $tab['pct'] / 100));
        $u['mp'] = (int) $u['mp'] + $heal;
        user_save($u);
        return '喝下【' . $tab['name'] . '】，回复魔力' . $heal . '（' . $tab['pct'] . '%）。';
    }
    // 优先自己；血满时转治疗召唤物和宠物
    if ((int) $u['hp'] >= (int) $u['maxhp']) {
        [$msg, $did] = drink_heal_allies($u, $mid, $tab);
        if ($did) {
            return $msg;
        }
        return '血是满的，召唤物和宠物也都健康，别浪费药。';
    }
    add_mat((int) ($u['id'] ?? 0), $mid, -1);
    $heal = min((int) $u['maxhp'] - (int) $u['hp'], (int) ((int) $u['maxhp'] * $tab['pct'] / 100));
    $u['hp'] = (int) $u['hp'] + $heal;
    user_save($u);
    return '喝下【' . $tab['name'] . '】，回复' . $heal . '（' . $tab['pct'] . '%）。';
}

// 任务完成送药：每环都送，低级不断药
function quest_potion_gift(int $uid, int $ch): string
{
    $g = [1 => ['hp_pct10', 3], 2 => ['hp_pct20', 3], 3 => ['hp_pct35', 2], 5 => ['hp_pct35', 3], 6 => ['hp_pct50', 2]][$ch] ?? ['hp_pct10', 2];
    add_mat($uid, $g[0], $g[1]);
    return '。任务奖励【' . mat_name($g[0]) . '×' . $g[1] . '】！';
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
    if ($id === 'rename_card') {
        return '改名卡';
    }
    if ($id === 'title_iron') {
        return '称号·黑铁冒险者';
    }
    if ($id === 'title_silver') {
        return '称号·秘银冒险者';
    }
    if ($id === 'title_gold') {
        return '称号·精金传说';
    }
    if ($id === 'tent') {
        return '随身帐篷';
    }
    if ($id === 'dragon_box') {
        return '龙鳞宝箱';
    }
    $titles = [
        'title_ch1' => '称号·灰雾行者',
        'title_ch2' => '称号·白银守望',
        'title_ch3' => '称号·王都破局者',
        'title_ch4' => '称号·凛冬行者',
        'title_ch5' => '称号·南炎见证',
        'title_rb2' => '称号·虚空征服者',
        'title_dragon1' => '称号·龙鳞初醒',
        'title_dragon2' => '称号·龙血沸腾',
        'title_dragon3' => '称号·灭世龙威',
    ];
    if (isset($titles[$id])) {
        return $titles[$id];
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
    // 六龙王材料：龙魂/龙玉/龙王精华
    $dm = dragon_mats();
    if (isset($dm[$id])) {
        return $dm[$id];
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
    if ($id === 'dragon_pill') {
        return '真龙丹';
    }
    if (isset(pct_potions()[$id])) {
        $t = pct_potions()[$id];
        return $t['name'] . '(' . ((($t['stat'] ?? 'hp') === 'mp') ? '回蓝' : '回血') . $t['pct'] . '%)';
    }
    if ($id === 'offline_mod') {
        return '人偶离线升级模块';
    }
    if ($id === 'offline_time') {
        return '离线时长';
    }
    if ($id === 'arena_coin') {
        return '竞技场币';
    }
    if ($id === 'arena_gift') {
        return '低级附魔礼包';
    }
    if ($id === 'exp_card100') {
        return '升级卡100型';
    }
    if ($id === 'reset_potion') {
        return '属性洗点药';
    }
    if ($id === 'god_badge') { return '神界勋章'; }
    if ($id === 'gf_ticket') { return '堕天阶入场券'; }
    if ($id === 'god_proof') { return '抗神证明'; }
    if ($id === 'qingzui_dan') {
        return '清罪丹';
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
    // 数量归零直接删行，背包不再出现xxx0
    db()->prepare('DELETE FROM mats WHERE uid=? AND num<=0')->execute([(int) $uid]);
    mats_bust();
}

function mats_bust(): void
{
    $GLOBALS['_mats_gen'] = (int) ($GLOBALS['_mats_gen'] ?? 0) + 1;
}

function mats_of(int $uid): array
{
    static $cache = [];
    $k = (int) $uid . ':' . (int) ($GLOBALS['_mats_gen'] ?? 0);
    if (isset($cache[$k])) {
        return $cache[$k];
    }
    $st = db()->prepare('SELECT mat, num FROM mats WHERE uid=? ORDER BY mat');
    $st->execute([$uid]);
    $out = [];
    while ($row = $st->fetch()) {
        $out[$row['mat']] = (int) $row['num'];
    }
    $cache[$k] = $out;
    return $out;
}

function mat_del(int $uid, string $mat): void
{
    db()->prepare('DELETE FROM mats WHERE uid=? AND mat=?')->execute([(int) $uid, $mat]);
    mats_bust();
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
    } elseif (in_array($loc, fr_maps(), true) && $mid === 'fr_leviathan') {
        $id = 'enhance_t2';
    } elseif (in_array($loc, em_maps(), true) && $mid === 'em_heart') {
        $id = 'enhance_t3';
    } elseif (in_array($loc, pz_maps(), true) && $mid === 'pz_master') {
        $id = 'enhance_t2';
    } elseif (in_array($loc, sen_maps(), true) && $mid === 'sen_avatar') {
        $id = 'enhance_t3';
    } elseif ($mid === 'winter_king' || $mid === 'ancient_ruin_golem') {
        $id = 'enhance_t3';
    } elseif (in_array($loc, vx_maps(), true) && $mid === 'vx_tyrant') {
        $id = 'enhance_t3';
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
        'frost_wolf' => 'ice', 'ice_specter' => 'ice', 'snow_mammoth' => 'ice', 'blizzard_shaman' => 'ice',
        'glacier_golem' => 'ice', 'frost_giant' => 'ice', 'aurora_serpent' => 'thunder', 'icewyrm' => 'ice',
        'winter_knight' => 'ice', 'frost_matriarch' => 'ice', 'glacier_colossus' => 'ice', 'winter_king' => 'ice',
        'fr_crawler' => 'ice', 'fr_stalker' => 'dark', 'fr_warden' => 'ice', 'fr_horror' => 'dark', 'fr_leviathan' => 'ice',
        'mist_treant' => 'wind', 'spore_colony' => 'dark', 'thorn_hunter' => 'wind', 'ancient_boa' => 'dark',
        'mist_apeman' => 'wind', 'rot_sage' => 'dark', 'ember_imp' => 'fire', 'lava_hound' => 'fire',
        'ash_cultist' => 'fire', 'magma_golem' => 'fire', 'flame_djinn' => 'fire', 'cinder_dragonspawn' => 'fire',
        'volcano_priest' => 'fire', 'ash_wyrm' => 'fire', 'south_flame_guard' => 'fire', 'ancient_ruin_golem' => 'fire',
        'em_slag' => 'fire', 'em_fiend' => 'fire', 'em_magma' => 'fire', 'em_ashlord' => 'fire', 'em_heart' => 'fire',
        'pz_puller' => 'dark', 'pz_dancer' => 'dark', 'pz_mask' => 'dark', 'pz_golem' => 'thunder', 'pz_master' => 'dark',
        'sen_priest' => 'wind', 'sen_thorn' => 'wind', 'sen_serpent' => 'dark', 'sen_guard' => 'wind', 'sen_avatar' => 'wind',
        'shadow_claw' => 'dark', 'brass_bishop' => 'light', 'tundra_wanderer' => 'ice', 'lucky_goblin' => 'wind', 'golden_slime' => 'light', 'mimic_chest' => 'dark',
        'frostborn_hunter' => 'ice',
        'vx_spawn' => 'dark', 'vx_stalker' => 'dark', 'vx_mage' => 'dark', 'vx_knight' => 'dark', 'vx_herald' => 'dark', 'vx_tyrant' => 'dark', 'avalanche_titan' => 'ice', 'sulfur_sprite' => 'fire', 'obsidian_colossus' => 'fire',
        'cinder_crawler' => 'fire', 'cinder_stalker' => 'dark', 'cinder_warden' => 'fire', 'cinder_horror' => 'dark', 'ash_titan' => 'fire',
        'doyle' => 'fire',
    ];
    static $wnElMerged = false;
    if (!$wnElMerged) {
        foreach (world_expansion_element() as $__wk => $__wv) {
            if (!isset($m[$__wk])) {
                $m[$__wk] = $__wv;
            }
        }
        foreach (region_dungeon_element() as $__rk => $__rv) {
            if (!isset($m[$__rk])) {
                $m[$__rk] = $__rv;
            }
        }
        $wnElMerged = true;
    }
    if (isset($m[$mid])) {
        return $m[$mid];
    }
    // 六圣柱专属小怪：前缀即属性
    return (string) (dragon_monster($mid)['el'] ?? '');
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

function enchant_mat_tier_pct(string $mid): float
{
    $t = enchant_mat_tier($mid);
    $tiers = enchant_tiers();
    $mn = (int) ($tiers[$t]['min'] ?? 1);
    return max(5.0, min(95.0, $mn * 4.0));
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
    // 石头→晶石：按魔晶百分比成功；晶石→珠→神石：90%失败（概率不展示）
    $succ = $t === 'stone' ? (enchant_mat_tier_pct($mid) / 100) : 0.1;
    if (mt_rand() / mt_getrandmax() > $succ && $t !== 'stone') {
        return '合成失败，材料碎了……再攒点石头试试手气。';
    }
    if ($t === 'stone' && mt_rand() / mt_getrandmax() > $succ) {
        return '合成失败，材料碎了……再攒点石头试试手气。';
    }
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
    if (($e['slot'] ?? '') === 'title') {
        return '称号不可强化（荣耀不打折，也不加固）。';
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
    return 30 + 5 * (int) ($mats['bag_ext5_used'] ?? 0) + 10 * (int) ($mats['bag_ext10_used'] ?? 0) + (int) gear_stats((int) $uid)['bagcap'];
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

// 战场补给：做成真实掉落物（谁都能抢）。kind=peak，ref=atk/def/heal。
function ground_place_peak_supply(string $loc, string $buffKey): void
{
    if (!in_array($buffKey, ['atk', 'def', 'heal'], true)) {
        return;
    }
    db()->prepare('INSERT INTO ground_items (loc, kind, ref, num, at) VALUES (?, "peak", ?, 1, ?)')->execute([$loc, $buffKey, time()]);
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
    if (($g['kind'] ?? '') === 'peak') {
        // 战场补给：谁先点到谁拿到，给个人增益
        $k = (string) $g['ref'];
        $names = ['atk' => '战力增幅(攻+15%)', 'def' => '铁壁补给(减伤15%)', 'heal' => '急救包(回血15%)'];
        if (!isset($names[$k])) {
            db()->prepare('DELETE FROM ground_items WHERE id=?')->execute([$gid]);
            return '那件补给已经失效了。';
        }
        $del = db()->prepare('DELETE FROM ground_items WHERE id=?');
        $del->execute([$gid]);
        if ($del->rowCount() === 0) {
            return '慢了一步，补给被别人抢走了！';
        }
        mat_set((int) $uid, 'peak_buff_' . $k, 1);
        $u = user_by_id((int) $uid);
        $tail = '';
        if ($k === 'heal' && $u) {
            $h = max(1, (int) ($u['maxhp'] * 0.15));
            $u['hp'] = min((int) $u['maxhp'], (int) $u['hp'] + $h);
            user_save($u);
            $tail = '，回复 ' . $h . ' 点生命';
        }
        return '你抢到【' . $names[$k] . '】' . $tail . '！';
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
    static $bosses = ['knight', 'echo_rayne', 'swamp_king', 'abaddon', 'abyss_eye', 'dark_lily', 'valentin', 'guardian', 'string_puller', 'silk_mother', 'winter_king', 'fr_leviathan', 'ancient_ruin_golem', 'em_heart', 'doyle', 'pz_master', 'sen_avatar', 'shadow_claw', 'brass_bishop', 'tundra_wanderer', 'avalanche_titan', 'obsidian_colossus', 'ash_titan'];
    return in_array($mid, $bosses, true) || in_array($mid, world_expansion_bosses(), true) || in_array($mid, region_dungeon_bosses(), true);
}

function is_ch3_mid(string $mid): bool
{
    static $mids = ['puppet_guard', 'masked_noble', 'ink_puppet', 'silver_undead', 'silver_assassin', 'puppet_priest', 'star_puppet', 'silver_puppet', 'string_puller', 'silk_spider', 'cocoon_guard', 'thread_weaver', 'silk_moth', 'nest_watcher', 'brood_maiden', 'silk_mother', 'frost_wolf', 'ice_specter', 'snow_mammoth', 'blizzard_shaman', 'glacier_golem', 'frost_giant', 'aurora_serpent', 'icewyrm', 'winter_knight', 'frost_matriarch', 'glacier_colossus', 'winter_king', 'fr_crawler', 'fr_stalker', 'fr_warden', 'fr_horror', 'fr_leviathan', 'mist_treant', 'spore_colony', 'thorn_hunter', 'ancient_boa', 'mist_apeman', 'rot_sage', 'ember_imp', 'lava_hound', 'ash_cultist', 'magma_golem', 'flame_djinn', 'cinder_dragonspawn', 'volcano_priest', 'ash_wyrm', 'south_flame_guard', 'ancient_ruin_golem', 'em_slag', 'em_fiend', 'em_magma', 'em_ashlord', 'em_heart', 'doyle', 'frostborn_hunter', 'avalanche_titan', 'sulfur_sprite', 'obsidian_colossus', 'pz_puller', 'pz_dancer', 'pz_mask', 'pz_golem', 'pz_master', 'sen_priest', 'sen_thorn', 'sen_serpent', 'sen_guard', 'sen_avatar', 'shadow_claw', 'brass_bishop', 'tundra_wanderer', 'lucky_goblin', 'golden_slime', 'mimic_chest', 'vx_spawn', 'vx_stalker', 'vx_mage', 'vx_knight', 'vx_herald', 'vx_tyrant'];
    return in_array($mid, $mids, true);
}

// 该 mid 是否是"某个副本里的小怪"（非 BOSS）。用于统一副本小怪掉率。
// 副本集合 = dungeon_maps() 里所有地图点的小怪（含大区域副本/主线副本/第三阶段副本），
// 不含野外大地图(we2)与主城野外。构建一次后缓存。
function is_dungeon_mob(string $mid): bool
{
    static $set = null;
    if ($set === null) {
        $set = [];
        $locs = locations();
        foreach (dungeon_maps() as $loc) {
            $L = $locs[$loc] ?? null;
            if (!is_array($L)) { continue; }
            foreach (($L['monsters'] ?? []) as $m) {
                $set[(string) $m] = true;
            }
        }
    }
    if (!isset($set[$mid])) {
        return false;
    }
    // BOSS 不算小怪（BOSS 有自己的掉率）
    return !is_boss_mid($mid);
}

function scale_monster(array $m, string $mid, int $elite = 0): array
{
    if (is_ch3_mid($mid) || world_identity_mid($mid)) {
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
    $atk = max(player_patk($u), player_matk($u));
    $def = max(player_pdef($u), player_mdef($u));
    return (int) ($atk * 2 + $def * 1.5 + (int) $u['maxhp'] / 10 + (int) ($u['maxmp'] ?? 0) / 5 + (int) $u['lv'] * 5);
}

function job_name_of(array $u): string
{
    $jid = job_id_of($u);
    return jobs()[$jid]['name'] ?? '战士';
}

// 魔钻输入都是“钻”单位（可小数到0.1），库里是0.1钻整数，统一在这里换算
function diamond_to_internal($input): int
{
    return max(0, (int) round(((float) $input) * 10));
}

function spend_diamonds(int $uid, int $n): void
{
    if ($n <= 0) {
        return;
    }
    db()->prepare('UPDATE users SET diamonds_spent=diamonds_spent+? WHERE id=?')->execute([(int) $n, (int) $uid]);
}

function backfill_rank_stats(): void
{
    // 消费榜：老号按持有与累计充值反推历史消费
    try {
        db()->exec('UPDATE users SET diamonds_spent=CASE WHEN diamonds_bought>diamonds THEN diamonds_bought-diamonds ELSE 0 END WHERE diamonds_spent<=0');
    } catch (Throwable $e) {
    }

    // 充值榜：历史兑换魔钻没有单独计数，老号按持有+累计消费反推
    try {
        $st = db()->query('SELECT id, diamonds, diamonds_bought FROM users WHERE diamonds_bought<=0 AND diamonds>0');
        while ($r = $st->fetch()) {
            $spent = 0;
            try {
                $s2 = db()->prepare('SELECT COALESCE(SUM(amount),0) FROM horse_bets WHERE uid=?');
                $s2->execute([(int) $r['id']]);
                $spent += (int) $s2->fetchColumn();
            } catch (Throwable $e) {
            }
            db()->prepare('UPDATE users SET diamonds_bought=? WHERE id=?')->execute([(int) $r['diamonds'] + $spent, (int) $r['id']]);
        }
    } catch (Throwable $e) {
    }
    // 赛马榜：历史盈利没有单独计数，按邮件里的中奖记录反推
    try {
        $st = db()->query('SELECT uid, body FROM mails WHERE sender="赛马场" AND title LIKE "%中了%"');
        $seen = [];
        while ($r = $st->fetch()) {
            if (preg_match('/分得(\d+)魔钻/u', (string) ($r['body'] ?? ''), $m)) {
                $seen[(int) $r['uid']] = ($seen[(int) $r['uid']] ?? 0) + (int) $m[1];
            }
        }
        foreach ($seen as $uid => $tot) {
            db()->prepare('UPDATE users SET horse_won=? WHERE id=? AND horse_won<=0')->execute([$tot, $uid]);
        }
    } catch (Throwable $e) {
    }
}

function pet_power(array $p): int
{
    $s = pet_stats($p);
    return (int) ((int) ($s['atk'] ?? 0) * 3 + (int) ($s['def'] ?? 0) * 2 + (int) ($s['maxhp'] ?? 0) / 5 + (int) ($s['spd'] ?? 0) * 2 + (int) ($p['level'] ?? 1) * 5);
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
    // 副本小怪：基础掉率固定 1%，只吃寻宝与转生加成，不随怪物等级暴涨
    $m = min(200, gear_stats($uid)['magic_find']) / 100;
    // 转生：掉落率+20%
    if (function_exists('rs_drop_mult')) { $m *= rs_drop_mult($uid); }
    $one = function () use ($uid, $mid, $tab, $m) {
        if (mt_rand() / mt_getrandmax() > 0.01 * (1 + $m)) {
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

// 召唤师契匣独立掉落：按击杀怪等级给对应区间的基底，
// 品质走全游戏同一套规则（普通基底在低劣~史诗随机，传说基底只出传说）
function roll_summoner_box(int $uid, string $mid): string
{
    if (job_id_of(user_by_id((int) $uid) ?? []) !== 'summoner') {
        return '';
    }
    $lv = monster_lv($mid);
    if ($lv < 20) {
        return '';
    }
    $m = 1 + $lv * 0.02 + min(200, gear_stats($uid)['magic_find']) / 100;
    if (mt_rand() / mt_getrandmax() > 0.05 * $m) {
        return '';
    }
    $legend = summoner_offhand_for_lv($lv, true);
    // 传说基底极稀有，且只有高等级区间才有
    $legendRate = $lv >= 700 ? 0.02 : 0.004;
    if ($legend !== null && mt_rand() / mt_getrandmax() < $legendRate) {
        return make_equip($uid, 'offhand', $legend, 4, $lv);
    }
    $base = summoner_offhand_for_lv($lv);
    if ($base === null) {
        return '';
    }
    // 普通基底：低劣~史诗随机，沿用 roll_drop 的档位概率
    $r = mt_rand() / mt_getrandmax();
    $q = $r < 0.10 ? 3 : ($r < 0.35 ? 2 : ($r < 0.70 ? 1 : 0));
    return make_equip($uid, 'offhand', $base, $q, $lv);
}

function player_resist(array $u, string $el): float
{
    $gs = gear_stats((int) ($u['id'] ?? 0));
    $r = (float) ($gs['res_' . $el] ?? 0) + (float) ($gs['allres'] ?? 0) + (float) ($gs['resist'] ?? 0) + enchant_el_res((int) ($u['id'] ?? 0), $el);
    return min(75, $r);
}

// ========== 猎人专属掉落：弓(主手) / 箭袋(副手) 按怪物等级分段掉落 ==========
// 与召唤师契匣同一套思路：不看固定怪表，按击杀怪等级给对应区间的弓或箭袋，
// 猎人打怪就有机会摸到，各等级段都能逐步换新，而不是只能去商店买。
function hunter_arsenal_tiers(): array
{
    return [
        ['min' => 1,   'max' => 99,  'bow' => '缠藤短弓',   'quiver' => '兽皮箭袋'],
        ['min' => 100, 'max' => 199, 'bow' => '逐鹿强弓',   'quiver' => '逐鹿箭袋'],
        ['min' => 200, 'max' => 299, 'bow' => '掠影长弓',   'quiver' => '掠影箭袋'],
        ['min' => 300, 'max' => 419, 'bow' => '霜纹战弓',   'quiver' => '霜纹箭袋'],
        ['min' => 420, 'max' => 499, 'bow' => '穿林强弓',   'quiver' => '穿林箭袋'],
        ['min' => 500, 'max' => 599, 'bow' => '荆棘游侠弓', 'quiver' => '荆棘箭袋'],
        ['min' => 600, 'max' => 699, 'bow' => '烬焰猎日弓', 'quiver' => '烬焰箭囊'],
        ['min' => 700, 'max' => 799, 'bow' => '岩狱裂石弓', 'quiver' => '岩狱箭袋'],
        ['min' => 800, 'max' => 999, 'bow' => '时序追星弓', 'quiver' => '星辉箭袋'],
    ];
}

// 该等级区间的弓/箭袋基底。返回 ['bow'=>名字,'quiver'=>名字] 或 null。
function hunter_arsenal_for_lv(int $lv): ?array
{
    foreach (hunter_arsenal_tiers() as $t) {
        if ($lv >= $t['min'] && $lv <= $t['max']) {
            return ['bow' => $t['bow'], 'quiver' => $t['quiver']];
        }
    }
    return null;
}

// ===== 猎人专属·分等级段的传说弓/箭袋（16 件：8 个等级段 × 弓+箭袋成套）=====
// 每段一套：弓走输出(暴击/暴伤/斩杀/单体)，箭袋走副攻(持续/真伤/吸血/闪避)。
// eff 走 legend_relic 通用引擎结算（已在 legend_relic_eff_index 并入），靠 lg_* 词条判定穿戴。
// 弓名必含“弓”→weapon_sub=bow、职业猎手；箭袋名必含“箭袋”→猎手专属+暴击主属性。
function hunter_arsenal_legendaries(): array
{
    // [slot, base, min, max, key, param, job, intro, eff]
    return [
        // —— 100~199 ——
        ['slot' => 'weapon', 'base' => '白翎短弓',   'min' => 100, 'max' => 199, 'key' => 'lg_hla_bow1', 'param' => 1, 'job' => 'hunter',
            'intro' => '暴击率+4%；对单体目标伤害+8%（白翎惊鸿）。', 'eff' => ['crit' => 4, 'single' => 8]],
        ['slot' => 'offhand', 'base' => '白翎箭袋',  'min' => 100, 'max' => 199, 'key' => 'lg_hla_q1', 'param' => 1, 'job' => 'hunter',
            'intro' => '暴击伤害+15%；持续伤害+10%（翎羽淬毒）。', 'eff' => ['critdmg' => 15, 'dot' => 10]],
        // —— 200~299 ——
        ['slot' => 'weapon', 'base' => '裂石强弓',   'min' => 200, 'max' => 299, 'key' => 'lg_hla_bow2', 'param' => 1, 'job' => 'hunter',
            'intro' => '暴击伤害+20%；对残血(<30%)目标伤害+20%（裂石穿杨）。', 'eff' => ['critdmg' => 20, 'execute' => [0.3, 20]]],
        ['slot' => 'offhand', 'base' => '裂石箭袋',  'min' => 200, 'max' => 299, 'key' => 'lg_hla_q2', 'param' => 1, 'job' => 'hunter',
            'intro' => '持续伤害+18%；吸血+4%（蚀骨箭）。', 'eff' => ['dot' => 18, 'lifesteal' => 4]],
        // —— 300~419 ——
        ['slot' => 'weapon', 'base' => '幽林长弓',   'min' => 300, 'max' => 419, 'key' => 'lg_hla_bow3', 'param' => 1, 'job' => 'hunter',
            'intro' => '暴击率+5%；持续伤害+15%；闪避+5（幽林潜行）。', 'eff' => ['crit' => 5, 'dot' => 15, 'dodge' => 5]],
        ['slot' => 'offhand', 'base' => '幽林箭袋',  'min' => 300, 'max' => 419, 'key' => 'lg_hla_q3', 'param' => 1, 'job' => 'hunter',
            'intro' => '闪避+6；暴击率+4%；暴击时回复4%最大生命（林息）。', 'eff' => ['dodge' => 6, 'crit' => 4, 'oncrit_heal' => 4]],
        // —— 420~499 ——
        ['slot' => 'weapon', 'base' => '霜弦长弓',   'min' => 420, 'max' => 499, 'key' => 'lg_hla_bow4', 'param' => 1, 'job' => 'hunter',
            'intro' => '暴击伤害+26%；对残血(<30%)目标伤害+26%；攻击8%概率麻痹（霜弦封喉）。', 'eff' => ['critdmg' => 26, 'execute' => [0.3, 26], 'stun' => 8]],
        ['slot' => 'offhand', 'base' => '霜弦箭袋',  'min' => 420, 'max' => 499, 'key' => 'lg_hla_q4', 'param' => 1, 'job' => 'hunter',
            'intro' => '暴击伤害+30%；持续伤害+20%（霜蚀箭）。', 'eff' => ['critdmg' => 30, 'dot' => 20]],
        // —— 500~599 ——
        ['slot' => 'weapon', 'base' => '血棘战弓',   'min' => 500, 'max' => 599, 'key' => 'lg_hla_bow5', 'param' => 1, 'job' => 'hunter',
            'intro' => '对单体目标伤害+15%；吸血+5%；暴击时回复5%最大生命（饮血）。', 'eff' => ['single' => 15, 'lifesteal' => 5, 'oncrit_heal' => 5]],
        ['slot' => 'offhand', 'base' => '血棘箭袋',  'min' => 500, 'max' => 599, 'key' => 'lg_hla_q5', 'param' => 1, 'job' => 'hunter',
            'intro' => '真伤+35；吸血+5%；对残血(<30%)目标伤害+25%（血棘蚀）。', 'eff' => ['truedmg' => 35, 'lifesteal' => 5, 'execute' => [0.3, 25]]],
        // —— 600~699 ——
        ['slot' => 'weapon', 'base' => '噬日战弓',   'min' => 600, 'max' => 699, 'key' => 'lg_hla_bow6', 'param' => 1, 'job' => 'hunter',
            'intro' => '暴击率+7%；暴击伤害+34%；真伤+40（噬日）。', 'eff' => ['crit' => 7, 'critdmg' => 34, 'truedmg' => 40]],
        ['slot' => 'offhand', 'base' => '噬日箭袋',  'min' => 600, 'max' => 699, 'key' => 'lg_hla_q6', 'param' => 1, 'job' => 'hunter',
            'intro' => '闪避+8；暴击时回复6%最大生命；暴击率+6%（噬日回环）。', 'eff' => ['dodge' => 8, 'oncrit_heal' => 6, 'crit' => 6]],
        // —— 700~799 ——
        ['slot' => 'weapon', 'base' => '陨星长弓',   'min' => 700, 'max' => 799, 'key' => 'lg_hla_bow7', 'param' => 1, 'job' => 'hunter',
            'intro' => '对单体目标伤害+20%；对残血(<35%)目标伤害+35%；技能伤害+15%（陨落）。', 'eff' => ['single' => 20, 'execute' => [0.35, 35], 'skill_damage' => 15]],
        ['slot' => 'offhand', 'base' => '陨星箭袋',  'min' => 700, 'max' => 799, 'key' => 'lg_hla_q7', 'param' => 1, 'job' => 'hunter',
            'intro' => '持续伤害+30%；真伤+60；吸血+6%（星陨蚀）。', 'eff' => ['dot' => 30, 'truedmg' => 60, 'lifesteal' => 6]],
        // —— 800~999 ——
        ['slot' => 'weapon', 'base' => '猎神之弓',   'min' => 800, 'max' => 999, 'key' => 'lg_hla_bow8', 'param' => 1, 'job' => 'hunter',
            'intro' => '暴击率+8%；暴击伤害+45%；对残血(<40%)目标伤害+45%；真伤+60（猎神裁决）。', 'eff' => ['crit' => 8, 'critdmg' => 45, 'execute' => [0.4, 45], 'truedmg' => 60]],
        ['slot' => 'offhand', 'base' => '猎神箭袋',  'min' => 800, 'max' => 999, 'key' => 'lg_hla_q8', 'param' => 1, 'job' => 'hunter',
            'intro' => '暴击伤害+50%；对残血(<40%)目标伤害+40%；真伤+80；吸血+6%（猎神收割）。', 'eff' => ['critdmg' => 50, 'execute' => [0.4, 40], 'truedmg' => 80, 'lifesteal' => 6]],
    ];
}

// 上述传说弓/箭袋的专属故事（equip.php 讲述用）
function hunter_arsenal_legendary_lore(): array
{
    return [
        '白翎短弓' => '一位白翎部的老猎人临终前把弓掰成两截。<br>他说：短弓射不远，但足够替我看着山下的路。',
        '白翎箭袋' => '箭尾绑的白翎是从他猎到的第一只鸟身上拔的。<br>那之后他再没失手，也再没回过家。',
        '裂石强弓' => '这张弓第一次拉开时，弓背裂了道缝，箭却把石头射穿了。<br>猎人笑着说：弓裂它的，箭走箭的。',
        '裂石箭袋' => '箭袋里永远装着三支没磨完的箭。<br>他说留给最后一头猎物——总要给它一点挣扎的余地。',
        '幽林长弓' => '林子深处没有路，只有被箭钉住的树记着来过的人。<br>你拉满弓时，风会替你指方向。',
        '幽林箭袋' => '箭袋是用一整张雾做的，取出来时还带着林子的潮气。<br>中箭的人先迷路，再倒下。',
        '霜弦长弓' => '弓弦是用冬天第一根结冰的草搓的，一拉就响，像远处有人在哭。<br>听见哭声的猎物，通常已经晚了。',
        '霜弦箭袋' => '箭头凝着一层化不掉的霜，射进肉里才慢慢化。<br>化开的那一下，血是凉的。',
        '血棘战弓' => '弓身缠着吸饱了血的荆棘，越杀长得越密。<br>有一天它会先勒住拿弓的手——猎人知道，但没打算停。',
        '血棘箭袋' => '箭袋上的刺是它替主人挡过一次刀留下的。<br>它不记得救过谁，只记得那口血很烫。',
        '噬日战弓' => '有人试着在正午射太阳，箭在半空烧成了灰。<br>弓没断——它只是从此见不得太亮的东西。',
        '噬日箭袋' => '箭袋是黑的，装进去的箭会慢慢变短，像被谁从里面吃掉一点。<br>没人说得清吃箭的是敌人，还是这张袋。',
        '陨星长弓' => '陨铁打的弓，握把处还留着坠地那晚的温度。<br>它从天上掉下来，是想找个人替它再射一次。',
        '陨星箭袋' => '袋底积着一层星屑，倒出来能照亮一小块地。<br>猎人从不倒，他说那是箭还在烧。',
        '猎神之弓' => '传说第一位猎手射出的箭从没落空，因为猎物从来不躲。<br>它们只是想看看，究竟是谁在拉这张弓。',
        '猎神箭袋' => '箭袋空着也能听见满弓的声音。<br>它记得每一个死在箭下者的名字——按它们倒下的顺序。',
    ];
}

// DOT 传说(10件) + 蚀心DOT套装(7件) 的专属故事（equip.php 讲述用）
function dot_legendary_lore(): array
{
    return [
        // —— 10 件 DOT 传说 ——
        '蚀骨之戒' => '戒指内圈刻着一行小字：毒入骨，方知痛。<br>戴它的人不怕疼，只怕不疼。',
        '淬毒之链' => '链坠是一枚空了的毒囊，凑近还能闻到甜味。<br>甜味散尽的那天，链子会自己收紧。',
        '溃腐箭袋' => '袋里的箭永远在慢慢烂，烂得越久，射出去越不讲道理。<br>它不保养箭，它养的是怨。',
        '腐心裹手' => '手套贴着掌心长出细密的黑脉，像有什么在里面爬。<br>握弓的人说：心先烂，手才稳。',
        '血蚀皮甲' => '甲是用浸透血又晒干的皮缝的，颜色洗不掉。<br>穿上它，敌人的血和你的血分不清谁是谁的。',
        '毒云兜帽' => '帽檐压得很低，垂下的雾终年不散。<br>没人看清过戴它的人的脸——看清的人都已倒下。',
        '流毒护腿' => '护腿的接缝处渗着不凝的黑液，走一步，地上就多一个印。<br>追着脚印找它的人，都倒在了最后一段路。',
        '疾淬之靴' => '靴底淬过毒，落地无声，落地之后的声音也不长久。<br>它跑得越快，身后的路烂得越彻底。',
        '万毒束带' => '带上别着七只小瓶，每只装着一种死法。<br>解毒的人通常先选错一瓶。',
        '蚀天披风' => '披风展开像一片正在扩散的阴影，边缘在慢慢吞掉光。<br>穿它的人背对太阳站着，因为影子才归他管。',
        // —— 蚀心 DOT 套装（堕天阶）——
        '蚀心·猎弓' => '弓臂是用被毒浸透的神木削的，拉满时有细小的黑纹在木头上爬。<br>它猎的从来不是猎物，是猎物剩下的时间。',
        '蚀心·箭袋' => '袋口永远开着一道缝，毒气从缝里一丝丝往外漏。<br>满袋的箭没有一支是干净的。',
        '蚀心·血衣' => '衣服吸进去的血比吐出来的多，越穿越沉，越沉越贴身。<br>它记得每一个被它裹住的人的心跳。',
        '蚀心·覆面' => '覆面只露出一双眼睛，眼睛以下的部分没人想看清。<br>戴上它，你就不再需要表情。',
        '蚀心·绑腿' => '绑腿缠得很紧，紧到能听见血液被逼着改道。<br>改道的血，流得更快，也更毒。',
        '蚀心·裹手' => '裹手的布条浸过三种毒，每一种都为了让下一击更难拔出。<br>被它碰到的地方，第二天还是黑的。',
        '蚀心·轻履' => '轻履落地无声，落地三步之内，草会先黄。<br>它不是让你走得更快，是让追你的人慢下来。',
    ];
}

// 蚀心 DOT 套装：按穿戴件数给持续伤害加成（在 hunter_dot_mult 里乘算）。
// 3件+12%，5件+20%，7件(满)+30%。
function dot_set_dot_pct(int $uid): float
{
    $n = 0;
    $st = db()->prepare('SELECT COUNT(*) FROM equips WHERE uid=? AND pos="wear" AND name LIKE ?');
    $st->execute([(int) $uid, '%蚀心·%']);
    $n = (int) $st->fetchColumn();
    if ($n >= 7) {
        return 30.0;
    }
    if ($n >= 5) {
        return 20.0;
    }
    if ($n >= 3) {
        return 12.0;
    }
    return 0.0;
}

// 某等级段可掉的传说弓/箭袋（弓+箭袋各一件）。返回条目列表。
function hunter_arsenal_legendary_for_lv(int $lv): array
{
    $out = [];
    foreach (hunter_arsenal_legendaries() as $cfg) {
        if ($lv >= (int) $cfg['min'] && $lv <= (int) $cfg['max']) {
            $out[] = $cfg;
        }
    }
    return $out;
}

// 猎人打怪掉本等级段的【传说弓 / 传说箭袋】（走 legend 引擎，穿上有 lg_* 特效）。
// 沿用全游戏规则：传说只由 BOSS 产出（roll_drop 对非 BOSS 封顶史诗），所以这里只在 BOSS 轮判定。
// 掉率 12%，吃寻宝/转生；独立于普通弓/箭袋掉落。
function roll_hunter_legendary_arsenal(int $uid, string $mid): string
{
    if (job_id_of(user_by_id((int) $uid) ?? []) !== 'hunter') {
        return '';
    }
    $lv = monster_lv($mid);
    if ($lv < 1) {
        return '';
    }
    if (!is_boss_mid($mid)) {
        return '';
    }
    $cands = hunter_arsenal_legendary_for_lv($lv);
    if ($cands === []) {
        return '';
    }
    $baseRate = 0.12;
    $m = 1 + min(200, gear_stats($uid)['magic_find']) / 100;
    if (function_exists('rs_drop_mult')) {
        $m *= rs_drop_mult($uid);
    }
    if (mt_rand() / mt_getrandmax() > $baseRate * $m) {
        return '';
    }
    $cfg = $cands[array_rand($cands)];
    return make_equip((int) $uid, (string) $cfg['slot'], (string) $cfg['base'], 4, $lv,
        ['extra' => ['k' => (string) $cfg['key'], 'v' => (float) $cfg['param']]]);
}

// 猎人打怪掉 DOT 增强传说（lg_dot1~10，300~999 级）：只由 BOSS 产出，
// 怪物等级 >= 该件设计等级才可能掉（低级BOSS掉低段，高级BOSS可掉全段）。
// 掉率 9%，吃寻宝/转生。
function roll_dot_legendary_gear(int $uid, string $mid): string
{
    if (job_id_of(user_by_id((int) $uid) ?? []) !== 'hunter') {
        return '';
    }
    if (!is_boss_mid($mid)) {
        return '';
    }
    $lv = monster_lv($mid);
    $cands = [];
    foreach (legend_relics() as $name => $cfg) {
        if (!str_starts_with((string) ($cfg['key'] ?? ''), 'lg_dot')) {
            continue;
        }
        if ($lv >= (int) $cfg['lv']) {
            $cands[$name] = $cfg;
        }
    }
    if ($cands === []) {
        return '';
    }
    $rate = 0.09;
    $m = 1 + min(200, gear_stats($uid)['magic_find']) / 100;
    if (function_exists('rs_drop_mult')) {
        $m *= rs_drop_mult($uid);
    }
    if (mt_rand() / mt_getrandmax() > $rate * $m) {
        return '';
    }
    $name = array_rand($cands);
    $cfg = $cands[$name];
    return make_equip((int) $uid, (string) $cfg['slot'], (string) $name, 4, (int) $cfg['lv'],
        ['extra' => ['k' => (string) $cfg['key'], 'v' => (float) ($cfg['param'] ?? 1)]]);
}

// 猎人打怪独立掉落：每次击杀按怪物等级给对应区间的弓或箭袋（约5%基础，吃寻宝/转生）。
// 只对猎人触发；品质走全游戏同一档位（低劣~史诗）。
function roll_hunter_gear(int $uid, string $mid): string
{
    if (job_id_of(user_by_id((int) $uid) ?? []) !== 'hunter') {
        return '';
    }
    $lv = monster_lv($mid);
    if ($lv < 1) {
        return '';
    }
    $tier = hunter_arsenal_for_lv($lv);
    if ($tier === null) {
        return '';
    }
    $isBoss = is_boss_mid($mid);
    $isDun = function_exists('is_dungeon_mob') ? is_dungeon_mob($mid) : false;
    // 高等级(≥300)弓/箭袋只在副本小怪或 BOSS 掉，野外大地图不再产出（低段仍随处可掉，方便练级）
    $isHigh = $lv >= 300;
    if ($isHigh && !$isBoss && !$isDun) {
        return '';
    }
    // 掉率：野外/低段 4%，副本小怪 7%，BOSS 大幅提高（约30%）
    $baseRate = $isBoss ? 0.30 : ($isDun ? 0.07 : 0.04);
    // 固定基础掉率，不随怪物等级暴涨；只吃寻宝(magic_find)与转生加成
    $m = 1 + min(200, gear_stats($uid)['magic_find']) / 100;
    if (function_exists('rs_drop_mult')) { $m *= rs_drop_mult($uid); }
    if (mt_rand() / mt_getrandmax() > $baseRate * $m) {
        return '';
    }
    // 弓与箭袋各半
    $slot = mt_rand(0, 1) === 0 ? 'weapon' : 'offhand';
    $base = $slot === 'weapon' ? $tier['bow'] : $tier['quiver'];
    $r = mt_rand() / mt_getrandmax();
    $q = $r < 0.10 ? 3 : ($r < 0.35 ? 2 : ($r < 0.70 ? 1 : 0));
    // BOSS 保底稀有(2)，高等级 BOSS 更易出史诗(3)
    if ($isBoss) {
        $q = max($q, $isHigh ? 3 : 2);
    }
    return make_equip($uid, $slot, $base, $q, $lv);
}

// 大区域副本 BOSS 专属：猎人可在此摸到【副本套装弓 / 套装箭袋】与【传说箭袋】。
// 传说与套装级别的弓/箭袋只由副本 BOSS 产出，不走野外/小怪。
function roll_region_boss_hunter_gear(int $uid, string $mid): string
{
    if (job_id_of(user_by_id((int) $uid) ?? []) !== 'hunter') {
        return '';
    }
    if (!is_boss_mid($mid) || !function_exists('region_dungeon_boss_set_piece')) {
        return '';
    }
    $sp = region_dungeon_boss_set_piece($mid);
    if (!$sp) {
        return '';
    }
    $prefix = region_dungeon_prefix_of(equip_shortname((string) ($sp['base'] ?? '')));
    if ($prefix === '' || !region_dungeon_is_set_prefix($prefix)) {
        return '';
    }
    $lv = monster_lv($mid);
    $m = 1 + min(200, gear_stats($uid)['magic_find']) / 100;
    $r = mt_rand() / mt_getrandmax();
    // 传说箭袋（q4，仅 BOSS，约 3%*寻宝）：统一为唯一的传说箭袋，带专属传说特效+故事
    if ($r < 0.03 * $m) {
        return make_equip($uid, 'offhand', '裂空猎魂箭袋', 4, $lv);
    }
    // 副本套装弓 / 套装箭袋（q3，约 22%*寻宝，各半）：弓=主手、箭袋=副手
    if ($r < 0.25 * $m) {
        $wantBow = mt_rand(0, 1) === 0;
        $base = $wantBow ? $prefix . '之弓' : $prefix . '之箭袋';
        return make_equip($uid, $wantBow ? 'weapon' : 'offhand', $base, 3, $lv);
    }
    return '';
}

function monster_dodge(string $mid): float
{
    static $d = ['bat' => 6, 'wraith' => 5, 'thief' => 5, 'cultist' => 3, 'knight' => 10, 'echo_rayne' => 12, 'bee' => 4, 'deep_bat' => 6];
    return (float) ($d[$mid] ?? 0);
}

function roll_drop(int $uid, string $mid, int $elite = 0): string
{
    $base = roll_drop_base($uid, $mid, $elite);
    // 大区域副本 BOSS：额外掉一件副本套装件（磨齐 7 件套的主要来源）
    if (function_exists('region_dungeon_boss_set_piece')) {
        $sp = region_dungeon_boss_set_piece($mid);
        if ($sp !== null) {
            // 55% 概率额外掉一件（封顶史诗）
            if (mt_rand(1, 100) <= 55) {
                $spBase = (string) $sp['base'];
                $spSlot = (string) $sp['slot'];
                // 若是职业武器，按玩家职业换成对应武器
                if (!empty($sp['class_weapons']) && is_array($sp['class_weapons'])) {
                    $job = job_id_of(user_by_id($uid));
                    $spBase = (string) ($sp['class_weapons'][$job] ?? $spBase);
                }
                $piece = make_equip($uid, $spSlot, $spBase, 3, monster_lv($mid));
                if ($piece !== '') {
                    $base = $base !== '' ? $base . ' ' . $piece : $piece;
                }
            }
        }
    }
    // 猎人专属：按怪物等级独立掉弓/箭袋（与主掉落互不影响，各等级段都能陆续换新）
    $hg = roll_hunter_gear($uid, $mid);
    if ($hg !== '') {
        $base = $base !== '' ? $base . ' ' . $hg : $hg;
    }
    // 猎人专属：分等级段的传说弓/传说箭袋（低概率，独立一轮）
    $hl = roll_hunter_legendary_arsenal($uid, $mid);
    if ($hl !== '') {
        $base = $base !== '' ? $base . ' ' . $hl : $hl;
    }
    // 猎人专属：DOT 增强传说（lg_dot1~10，BOSS 掉，独立一轮）
    $hd = roll_dot_legendary_gear($uid, $mid);
    if ($hd !== '') {
        $base = $base !== '' ? $base . ' ' . $hd : $hd;
    }
    // 副本 BOSS：猎人专属套装弓/套装箭袋/传说箭袋（传说与套装级只由 BOSS 产出）
    $hbq = roll_region_boss_hunter_gear($uid, $mid);
    if ($hbq !== '') {
        $base = $base !== '' ? $base . ' ' . $hbq : $hbq;
    }
    return $base;
}

function roll_drop_base(int $uid, string $mid, int $elite = 0): string
{
    $ch3 = roll_ch3_drop($uid, $mid, $elite);
    if ($ch3 !== '' || isset(ch3_drops()[$mid])) {
        // 召唤师：契匣掉率独立一轮（原 offhand 格已被改名，不和专属表冲突）
        $sbox = roll_summoner_box($uid, $mid);
        if ($sbox !== '') {
            return $ch3 !== '' ? $ch3 . ' ' . $sbox : $sbox;
        }
        return $ch3;
    }
    $t = monster_drops($mid);
    if ($t === []) {
        // 六圣柱小怪不掉套装，但召唤师照样能摸出契匣
        return roll_summoner_box($uid, $mid);
    }
    $mlv = monster_lv($mid);
    // 副本小怪：套件/装备基础掉率固定 1%，只吃寻宝与转生加成，不再随怪物等级暴涨
    $isDunMob = is_dungeon_mob($mid);
    $m = $isDunMob ? 1 : (1 + $mlv * 0.03);
    $m += min(200, gear_stats($uid)['magic_find']) / 100;
    // 转生：掉落率+20%
    if (function_exists('rs_drop_mult')) { $m *= rs_drop_mult($uid); }
    if (is_boss_mid($mid)) {
        // BOSS：品质整体上移，传说装备掉率显著提高（传说只挂在BOSS掉落表上）
        $chances = [4 => 0.004 * $m, 3 => 0.012 * $m, 2 => 0.03 * $m, 1 => 0.08 * $m, 0 => 0.16 * $m];
    } elseif ($isDunMob) {
        // 副本小怪：总掉率 1%（q0 阈值即总掉落率），品质分布沿用低阶为主；小怪不可能出传说(4)
        $chances = [4 => 0.000125 * $m, 3 => 0.0005 * $m, 2 => 0.0015 * $m, 1 => 0.0035 * $m, 0 => 0.01 * $m];
    } else {
        $chances = [4 => 0.0002 * $m, 3 => 0.0007 * $m, 2 => 0.0025 * $m, 1 => 0.01 * $m, 0 => 0.04 * $m];
    }
    $roll = mt_rand() / mt_getrandmax();
    $q = -1;
    foreach ($chances as $qq => $ch) {
        if ($roll < $ch) {
            $q = $qq;
            break;
        }
    }
    if ($q < 0) {
        return roll_summoner_box($uid, $mid);
    }
    if ($elite === 1) {
        $q = min(4, $q + 1);
    }
    // 小怪封顶史诗(3)：只有BOSS才可能出传说(4)
    if (!is_boss_mid($mid)) {
        $q = min(3, $q);
    }
    if ($q === 4) {
        if (!isset($t['legendary'])) {
            $q = 3;
        } else {
            return make_equip($uid, $t['slot'], $t['legendary']['base'], 4, monster_lv($mid), $t['legendary']);
        }
    }
    $dropBase = (string) $t['base'];
    $dropSlot = (string) $t['slot'];
    // 副本套装·职业武器：按玩家职业换成对应武器（斧/杖/锤/弓/魂灯）
    if (!empty($t['class_weapons']) && is_array($t['class_weapons'])) {
        $job = job_id_of(user_by_id($uid));
        $dropBase = (string) ($t['class_weapons'][$job] ?? $dropBase);
    }
    $eq = make_equip($uid, $dropSlot, $dropBase, $q, monster_lv($mid), null, (string) ($t['off'] ?? ''));
    if ($eq === '') {
        return roll_summoner_box($uid, $mid);
    }
    // 召唤师额外挂一次契匣判定（不占主掉落）
    $sbox = roll_summoner_box($uid, $mid);
    return $sbox !== '' ? $eq . ' ' . $sbox : $eq;
}

// 已穿装备属性总和（内存缓存一次）
function gear_stats(int $uid): array
{
    static $cache = [];
    if (isset($cache[$uid]) && ($GLOBALS['_gear_bust'] ?? null) !== $uid) {
        return $cache[$uid];
    }
    unset($GLOBALS['_gear_bust']);
    $keys = ['atk', 'matk', 'def', 'mdef', 'hp', 'truedmg', 'crit', 'critdmg', 'dodge', 'lifesteal', 'atk_pct', 'matk_pct', 'precision', 'speed', 'element', 'penetration', 'skill_damage', 'execute', 'damage_reduction', 'resist', 'thorns', 'regen', 'energy', 'cooldown', 'hit', 'exp_gain', 'move_speed', 'control_resist', 'slow_resist', 'all_attr', 'gold_gain', 'magic_find', 'summon_power', 'summon_hp', 'res_light', 'res_dark', 'res_fire', 'res_wind', 'res_ice', 'res_thunder', 'allres', 'bagcap'];
    $s = array_fill_keys($keys, 0.0);
    $st = db()->prepare('SELECT * FROM equips WHERE uid=? AND pos="wear"');
    $st->execute([$uid]);
    while ($row = $st->fetch()) {
        $mult = enhance_rate($row);
        $aff = json_decode((string) $row['affixes'], true);
        if (!is_array($aff)) {
            continue;
        }
        foreach ($aff as $a) {
            $id = (string) ($a['id'] ?? $a['k'] ?? '');
            $value = (float) ($a['v'] ?? 0);
            // 主属性全吃强化倍率（背包容量除外：腰带强化只长数值极小，基本不动）
            if ($id !== 'bagcap' && (!empty($a['main']) || in_array($id, ['atk', 'matk', 'def', 'mdef', 'hp', 'all_attr'], true))) {
                $value *= $mult;
            }
            if (isset($s[$id])) {
                $s[$id] += $value;
            }
        }
    }
        $tw = (string) (mats_of($uid)['title_wear'] ?? '');
    if ($tw !== '') {
        $tb = ['title_ch1' => ['atk' => 30, 'hp' => 500], 'title_ch2' => ['def' => 30, 'hp' => 800], 'title_ch3' => ['crit' => 1.0, 'atk' => 50], 'title_ch4' => ['mdef' => 40, 'hp' => 1200], 'title_ch5' => ['matk' => 50, 'hp' => 1500], 'title_rb2' => ['skill_damage' => 3.0, 'hp' => 2000], 'title_dragon1' => ['critdmg' => 5.0, 'hp' => 1500], 'title_dragon2' => ['atk' => 100, 'matk' => 100], 'title_dragon3' => ['skill_damage' => 8.0, 'truedmg' => 200.0], 'title_god' => ['skill_damage' => 12.0, 'truedmg' => 500.0, 'hp' => 5000.0], 'title_rs_god' => ['skill_damage' => 20.0, 'truedmg' => 1000.0, 'all_attr' => 100.0], 'title_rs_lord' => ['skill_damage' => 16.0, 'truedmg' => 700.0, 'all_attr' => 70.0], 'title_rs_king' => ['skill_damage' => 13.0, 'truedmg' => 500.0, 'all_attr' => 50.0], 'title_rs_gen' => ['skill_damage' => 10.0, 'truedmg' => 300.0, 'all_attr' => 30.0]][$tw] ?? [];
        foreach ($tb as $tk => $tv) { $s[$tk] += $tv; }
    }
// 全属性按攻击、防御、生命各加一次；百分比属性按设计上限截断。
    $s['atk'] += $s['all_attr'];
    $s['def'] += $s['all_attr'];
    $s['hp'] += $s['all_attr'];
    foreach (['crit' => 75, 'dodge' => 60, 'cooldown' => 50, 'hit' => 60] as $key => $cap) {
        $s[$key] = min((float) $cap, $s[$key]);
    }
    $st2 = db()->prepare('SELECT name FROM equips WHERE uid=? AND pos="wear"');
    $st2->execute([$uid]);
    $worn = [];
    while ($r2 = $st2->fetch()) {
        $worn[] = equip_shortname((string) $r2['name']);
    }
    $swampN = 0;
    foreach (['沼泽兜帽', '沼泽轻靴', '沼泽之心', '沼泽束腰'] as $sp) { if (in_array($sp, $worn, true)) { $swampN++; } }
    if ($swampN >= 3) {
        $s['energy'] += 300;
        $s['def'] += 20;
    }
    if ($swampN >= 4) {
        $s['bagcap'] += 4;
    }
    $abyssN = 0;
    foreach (['深渊蚀甲', '深渊腿甲', '深渊护手', '深渊缚带'] as $sp) { if (in_array($sp, $worn, true)) { $abyssN++; } }
    if ($abyssN >= 3) {
        $s['atk'] += 25;
        $s['def'] += 15;
    }
    if ($abyssN >= 4) {
        $s['bagcap'] += 6;
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
    // 寒霜3件 / 烬焰7件 / 傀儡5件 / 荆棘4件
    $frost = 0; $ember = 0; $puppet = 0; $thorn = 0;
    foreach ($worn as $wn) {
        if (str_starts_with($wn, '寒霜·')) { $frost++; }
        if (str_starts_with($wn, '烬焰·')) { $ember++; }
        if (str_starts_with($wn, '傀儡·')) { $puppet++; }
        if (str_starts_with($wn, '荆棘·')) { $thorn++; }
    }
    if ($frost >= 3) { $s['atk_pct'] += 8; $s['mdef'] += 80; }
    if ($ember >= 2) { $s['atk_pct'] += 10; }
    if ($ember >= 4) { $s['element'] += 120; }
    if ($ember >= 7) { $s['truedmg'] += 500; $s['damage_reduction'] += 10; $s['skill_damage'] += 15; }
    if ($puppet >= 2) { $s['skill_damage'] += 10; }
    if ($puppet >= 5) { $s['element'] += 100; $s['damage_reduction'] += 8; }
    if ($thorn >= 3) { $s['atk_pct'] += 6; }
    if ($thorn >= 4) { $s['lifesteal'] += 3; }
    $rock = 0;
    foreach ($worn as $wn) {
        if (str_starts_with($wn, '岩狱·')) { $rock++; }
    }
    if ($rock >= 3) { $s['hp'] += 3000; }
    if ($rock >= 5) { $s['damage_reduction'] += 8; }
    if ($rock >= 7) { $s['hp'] += 8000; $s['damage_reduction'] += 12; }
    if ($rock >= 10) { $s['hp'] += 20000; $s['damage_reduction'] += 20; }
    // —— 六龙王专属套装（西风·北寒·东雷·深渊·中央）——
    $dlv = max(1, (int) (db()->query('SELECT lv FROM users WHERE id=' . (int) $uid)->fetchColumn() ?: 1));
    $drgSets = ['firedragon' => '烈炎·', 'azure' => '苍岚·', 'glacier' => '永冻·', 'thunder' => '天谴·', 'ruin' => '终焉·', 'judgment' => '圣裁·'];
    $dcnt = [];
    foreach ($worn as $wn) {
        foreach ($drgSets as $sid => $pfx) {
            if (str_starts_with($wn, $pfx)) {
                $dcnt[$sid] = ($dcnt[$sid] ?? 0) + 1;
            }
        }
    }
    if (($dcnt['azure'] ?? 0) > 0) {
        $n = $dcnt['azure'];
        $s['dodge'] += $n;
        $s['move_speed'] += $n;
        if ($n >= 3) { $s['element'] += 80; }
        if ($n >= 5) { $s['crit'] += 8; $s['skill_damage'] += 18; $s['move_speed'] += 5; }
        if ($n >= 7) { $s['all_attr'] += $dlv * 6; $s['element'] += 120; }
        if ($n >= 11) { $s['element'] += $dlv * 4; $s['crit'] += 10; $s['move_speed'] += 8; $s['damage_reduction'] += 10; }
    }
    if (($dcnt['glacier'] ?? 0) > 0) {
        $n = $dcnt['glacier'];
        $s['res_ice'] += $n * 8;
        $s['control_resist'] += $n * 3;
        if ($n >= 3) { $s['damage_reduction'] += 10; $s['mdef'] += 150; }
        if ($n >= 5) { $s['hp'] += $dlv * 80; }
        if ($n >= 7) { $s['damage_reduction'] += 12; $s['slow_resist'] += 30; }
        if ($n >= 11) { $s['mdef'] += 300; $s['hp'] += $dlv * 120; $s['damage_reduction'] += 12; $s['all_attr'] += $dlv * 6; }
    }
    if (($dcnt['thunder'] ?? 0) > 0) {
        $n = $dcnt['thunder'];
        $s['element'] += $n * 10;
        if ($n >= 3) { $s['penetration'] += 80; $s['element'] += 100; }
        if ($n >= 5) { $s['skill_damage'] += 22; }
        if ($n >= 7) { $s['damage_reduction'] += 15; $s['element'] += 300; }
        if ($n >= 11) { $s['penetration'] += 150; $s['element'] += $dlv * 5; $s['skill_damage'] += 15; $s['damage_reduction'] += 10; }
    }
    if (($dcnt['ruin'] ?? 0) > 0) {
        $n = $dcnt['ruin'];
        $s['lifesteal'] += $n * 2;
        if ($n >= 3) { $s['truedmg'] += $dlv * 25; }
        if ($n >= 5) { $s['all_attr'] += $dlv * 5; $s['lifesteal'] += 8; }
        if ($n >= 7) { $s['truedmg'] += $dlv * 60; $s['damage_reduction'] += 18; }
        if ($n >= 11) { $s['truedmg'] += $dlv * 40; $s['lifesteal'] += 8; $s['damage_reduction'] += 12; $s['all_attr'] += $dlv * 8; }
    }
    if (($dcnt['judgment'] ?? 0) > 0) {
        $n = $dcnt['judgment'];
        $s['element'] += $n * 10;
        if ($n >= 3) { $s['skill_damage'] += 18; }
        if ($n >= 5) { $s['truedmg'] += $dlv * 40; $s['damage_reduction'] += 12; }
        if ($n >= 7) { $s['truedmg'] += $dlv * 80; $s['skill_damage'] += 30; $s['all_attr'] += $dlv * 8; }
        if ($n >= 11) { $s['truedmg'] += $dlv * 40; $s['skill_damage'] += 20; $s['all_attr'] += $dlv * 10; $s['element'] += $dlv * 5; }
    }
    if (($dcnt['firedragon'] ?? 0) > 0) {
        $n = $dcnt['firedragon'];
        $s['res_fire'] += $n * 6;
        $s['element'] += $n * 8;
        if ($n >= 3) { $s['element'] += 80; $s['truedmg'] += $dlv * 4; }
        if ($n >= 5) { $s['atk_pct'] += 8; $s['skill_damage'] += 15; }
        if ($n >= 7) { $s['damage_reduction'] += 10; $s['truedmg'] += $dlv * 12; $s['all_attr'] += $dlv * 3; }
        if ($n >= 11) { $s['all_attr'] += $dlv * 6; $s['damage_reduction'] += 8; $s['element'] += $dlv * 4; }
    }
    $gy = 0;
    foreach ($worn as $wn) {
        if (str_starts_with($wn, '罪渊·')) { $gy++; }
    }
    if ($gy > 0) {
        $sinLv = sin_of($uid);
        $mult = 1 + $sinLv / 100;
        $s['atk_pct'] += $gy * 1 * $mult;
        $s['skill_damage'] += $gy * 1 * $mult;
        if ($gy >= 3) { $s['hp'] += $sinLv * 2; }
        if ($gy >= 5) { $s['lifesteal'] += 5; }
        if ($gy >= 7) { $s['damage_reduction'] += 8; }
        if ($gy >= 9) { $s['truedmg'] += $sinLv * 3; }
        if ($gy >= 11) { $s['damage_reduction'] += min(20, $sinLv / 20); $s['skill_damage'] += min(50, $sinLv / 10); }
    }
    $god = 0;
    foreach ($worn as $wn) {
        if (str_starts_with($wn, '神铸·')) { $god++; }
    }
    if ($god > 0) {
        $ulv = (int) (db()->query('SELECT lv FROM users WHERE id=' . (int) $uid)->fetchColumn() ?: 1);
        $s['atk_pct'] += $god * 3;
        $s['damage_reduction'] += $god * 1;
        if ($god >= 3) { $s['hp'] += $ulv * 40; }
        if ($god >= 5) { $s['lifesteal'] += 8; }
        if ($god >= 7) { $s['damage_reduction'] += 12; }
        if ($god >= 9) { $s['truedmg'] += $ulv * 20; }
        if ($god >= 11) { $s['skill_damage'] += 30; $s['all_attr'] += $ulv * 5; }
    }
    $dusk = 0;
    foreach ($worn as $wn) {
        if (str_starts_with($wn, '暮影·')) { $dusk++; }
    }    $jm = 0; $sy = 0;    $sp2 = 0;
    foreach ($worn as $wn) {
        if (str_starts_with($wn, '魂契·')) { $sp2++; }
    }
    if ($sp2 > 0) {
        $splv = max(1, (int) (db()->query('SELECT lv FROM users WHERE id=' . (int) $uid)->fetchColumn() ?: 1));
        $s['summon_power'] += $sp2 * 8;
        if ($sp2 >= 2) { $s['truedmg'] += $splv * 10; }
        if ($sp2 >= 3) { $s['summon_power'] += $splv * 4; }
        if ($sp2 >= 5) { $s['summon_power'] += 50; }
    }
    foreach ($worn as $wn) {
        if (str_starts_with($wn, '寂灭·')) { $jm++; }
        if (str_starts_with($wn, '噬渊·')) { $sy++; }
    }
    if ($jm > 0) {
        $uls = max(1, (int) (db()->query('SELECT lv FROM users WHERE id=' . (int) $uid)->fetchColumn() ?: 1));
        $s['skill_damage'] += $jm * 3;
        if ($jm >= 2) { $s['skill_damage'] += 12; $s['truedmg'] += $uls * 15; }
        if ($jm >= 3) { $s['damage_reduction'] += 15; }
        if ($jm >= 5) { $s['truedmg'] += $uls * 40; }
    }
    if ($sy > 0) {
        $uls2 = max(1, (int) (db()->query('SELECT lv FROM users WHERE id=' . (int) $uid)->fetchColumn() ?: 1));
        $s['lifesteal'] += $sy * 2;
        if ($sy >= 2) { $s['atk_pct'] += 12; $s['lifesteal'] += 6; }
        if ($sy >= 3) { $s['truedmg'] += $uls2 * 20; }
        if ($sy >= 5) { $s['all_attr'] += $uls2 * 5; $s['damage_reduction'] += 20; }
    }
    if ($dusk >= 2) { $s['atk_pct'] += 15; $s['skill_damage'] += 10; }
    if ($dusk >= 3) { $s['crit'] += 8; $s['truedmg'] += max(1, (int) (db()->query('SELECT lv FROM users WHERE id=' . (int) $uid)->fetchColumn() ?: 1)) * 10; }
    // —— 蚀心 DOT 套装（堕天阶掉落）：面板通用加成；DOT 加成在 hunter_dot_mult 单独结算 ——
    $shx = 0;
    foreach ($worn as $wn) {
        if (str_starts_with($wn, '蚀心·')) { $shx++; }
    }
    if ($shx > 0) {
        $shxLv = max(1, (int) (db()->query('SELECT lv FROM users WHERE id=' . (int) $uid)->fetchColumn() ?: 1));
        $s['atk_pct'] += $shx * 2;
        $s['crit'] += $shx * 1;
        if ($shx >= 3) { $s['skill_damage'] += 8; $s['crit'] += 5; }
        if ($shx >= 5) { $s['critdmg'] += 20; $s['lifesteal'] += 4; $s['atk_pct'] += 6; }
        if ($shx >= 7) { $s['atk_pct'] += 10; $s['critdmg'] += 25; $s['truedmg'] += $shxLv * 15; $s['damage_reduction'] += 8; }
    }
    $qu = db()->prepare('SELECT quest FROM users WHERE id=?');
    $qu->execute([$uid]);
    if ((int) ($qu->fetchColumn() ?: 0) >= 38) {
        $s['atk_pct'] += 5;
        $s['all_attr'] += 10;
    }
    // 转生加成：基础属性 × 转生次数 × 10%
    $rsN = function_exists('rs_count') ? rs_count((int) $uid) : 0;
    if ($rsN > 0) {
        $m = 1 + $rsN * 0.10;
        foreach (['atk', 'matk', 'def', 'mdef', 'hp', 'truedmg'] as $k) {
            if (!empty($s[$k])) {
                $s[$k] = (int) round($s[$k] * $m);
            }
        }
    }
    // 源罪技能树
    if (function_exists('src_sin_effects')) {
        $se = src_sin_effects((int) $uid);
        if ($se['atk_pct'] > 0) {
            $s['atk_pct'] += $se['atk_pct'];
        }
        // 源罪之盾在学会时直接抬 maxhp 存档列（见 src_sin_learn），这里不再叠 hp_pct 以免双重计算。
        // 源罪之速叠加到 speed，由 speed_combo_strike() 消费（攻速→连击率）。
        if ($se['speed'] > 0) {
            $s['speed'] += $se['speed'];
        }
        // 神之罪主的加伤在战斗出手时按 sin_of 实时结算（见 battle_round），不在此写死。
    }
    // 遗神铸庭传说（面板类无条件加成）
    if (function_exists('legend_relic_gear_apply')) {
        legend_relic_gear_apply((int) $uid, $s);
    }
    // 大区域副本套装集齐加成（随角色等级缩放）
    if (function_exists('region_dungeon_gear_apply')) {
        region_dungeon_gear_apply((int) $uid, $s);
    }
    $cache[$uid] = $s;
    return $s;
}

// ---- 神魂系统：碎片合成天地人三级，装备后加真实伤害，无穿戴等级限制 ----
function soul_tiers(): array
{
    return ['ren' => ['name' => '人魂', 'base' => 50], 'di' => ['name' => '地魂', 'base' => 120], 'tian' => ['name' => '天魂', 'base' => 300]];
}
function soul_craft(int $uid): string
{
    $uid = (int) $uid;
    $mats = mats_of($uid);
    if ((int) ($mats['soul_shard'] ?? 0) < 10) {
        return '神魂碎片不够（10个合一次，当前' . (int) ($mats['soul_shard'] ?? 0) . '个）。';
    }
    $r = mt_rand(1, 100);
    $tier = $r <= 60 ? 'ren' : ($r <= 90 ? 'di' : 'tian');
    add_mat($uid, 'soul_shard', -10);
    $nm = make_equip($uid, 'soul', '神魂·' . soul_tiers()[$tier]['name'], 3, 1);
    if ($nm === '') {
        return '背包满了，神魂掉地上了，去捡！';
    }
    $st = db()->prepare('SELECT id FROM equips WHERE uid=? AND name LIKE ? ORDER BY id DESC LIMIT 1');
    $st->execute([$uid, '%神魂·%']);
    $eid = (int) ($st->fetchColumn() ?: 0);
    if ($eid > 0) { mat_set($uid, 'soul_lv_' . $eid, 1); }
    return '合成出【' . soul_tiers()[$tier]['name'] . '神魂】！已I级，真伤+' . soul_tiers()[$tier]['base'] . '。找神魂师升级。';
}
function soul_lv(int $uid, int $eid): int
{
    return max(0, (int) (mats_of((int) $uid)['soul_lv_' . (int) $eid] ?? 0));
}
// ========== 召唤师·深渊召唤兽 ==========
function summon_units(): array
{
    return [
        'imp' => ['name' => '深渊小鬼', 'atk_mult' => 0.70, 'hp_mult' => 0.35, 'bomb' => false],
        'wraith' => ['name' => '深渊恶鬼', 'atk_mult' => 1.10, 'hp_mult' => 0.60, 'bomb' => true],
        'bone' => ['name' => '骸骨守卫', 'atk_mult' => 0.85, 'hp_mult' => 0.85, 'bomb' => false],
        // 四阶·憎恶聚合体：多只恶鬼缝合的攻城兽，死时爆炸
        'abom' => ['name' => '憎恶聚合体', 'atk_mult' => 1.60, 'hp_mult' => 0.70, 'bomb' => true],
        // 五阶·无面梦魇：深渊第五层的原住民，最强
        'mare' => ['name' => '无面梦魇', 'atk_mult' => 2.10, 'hp_mult' => 0.80, 'bomb' => true],
    ];
}

function summon_cap(array $u): int
{
    $rb2 = cflags((int) ($u['id'] ?? 0))['rb2_job'] ?? '';
    // 死灵之拥：可同时召唤多只
    $cap = $rb2 === 'necro' ? 4 : 2;
    // 魂契2件：召唤上限+1
    if (equip_set_owned_count((int) ($u['id'] ?? 0), 'soulpact') >= 2) {
        $cap++;
    }
    // 传说契匣：召唤上限+1
    $cap += summoner_offhand_eff((int) ($u['id'] ?? 0))['cap'];
    // 死灵传说·深渊统御之印：召唤上限+1
    if (isset(summoner_legend_worn((int) ($u['id'] ?? 0))['slegend_abyssseal'])) {
        $cap++;
    }
    // 遗神铸庭·转职传说：召唤上限
    if (function_exists('legend_relic_summon_cap')) {
        $cap += legend_relic_summon_cap((int) ($u['id'] ?? 0));
    }
    return $cap;
}

function summon_power_bonus(array $u): float
{
    $bonus = (float) gear_stats((int) ($u['id'] ?? 0))['summon_power'];
    // 契匣：召唤兽伤害加成
    $bonus += summoner_offhand_eff((int) ($u['id'] ?? 0))['power'];
    // 死灵传说·尼德霍格的残鳞：每层召唤兽伤害+20%
    $bonus += 20.0 * summoner_legend_rescale_stacks((int) ($u['id'] ?? 0));
    // 遗神铸庭·彼岸：召唤物伤害×5
    if (isset(legend_relic_worn((int) ($u['id'] ?? 0))['lg_othershore'])) {
        $bonus += 400.0;
    }
    // 遗神铸庭·转职传说：召唤物伤害%
    if (function_exists('legend_relic_summon_power')) {
        $bonus += legend_relic_summon_power((int) ($u['id'] ?? 0));
    }
    return $bonus;
}

// 召唤兽生命加成（%）：装备 summon_hp + 契匣 hp 效果
function summon_hp_bonus(array $u): float
{
    $bonus = (float) gear_stats((int) ($u['id'] ?? 0))['summon_hp'];
    $bonus += summoner_offhand_eff((int) ($u['id'] ?? 0))['hp'];
    // 遗神铸庭·彼岸：召唤物生命×5
    if (isset(legend_relic_worn((int) ($u['id'] ?? 0))['lg_othershore'])) {
        $bonus += 400.0;
    }
    // 遗神铸庭·转职传说：召唤物生命%
    if (function_exists('legend_relic_summon_hp')) {
        $bonus += legend_relic_summon_hp((int) ($u['id'] ?? 0));
    }
    return $bonus;
}

function summon_list(): array
{
    $b = $_SESSION['battle'] ?? [];
    return is_array($b['summons'] ?? null) ? $b['summons'] : [];
}

function summon_save(array $list): void
{
    if (!isset($_SESSION['battle'])) {
        return;
    }
    $_SESSION['battle']['summons'] = array_values($list);
}

// 清空全部召唤兽（深渊主宰献祭）
function summon_clear(): void
{
    summon_save([]);
}

// 召唤兽状态（战斗界面显示用）：名字 + 当前血量/最大血量
function summon_status_list(): array
{
    $out = [];
    foreach (summon_list() as $s) {
        $out[] = [
            'name' => (string) ($s['name'] ?? '召唤兽'),
            'hp' => max(0, (int) ($s['hp'] ?? 0)),
            'max' => max(1, (int) ($s['maxhp'] ?? 1)),
        ];
    }
    return $out;
}

// 生成一只召唤兽（无回合数，死亡后消失；原"存活±N轮"加成统一折算成生命%）
function summon_spawn(string $kind, int $patk, int $php, array $u, float $bonusHpPct = 0): array
{
    $cfg = summon_units()[$kind] ?? summon_units()['imp'];
    $sp = 1 + summon_power_bonus($u) / 100;
    $hpPct = 0;
    // 魂契5件：召唤兽生命+40%
    if (equip_set_owned_count((int) ($u['id'] ?? 0), 'soulpact') >= 5) {
        $hpPct += 40;
    }
    // 契匣：原"存活+轮数"折成生命（每轮=+20%）
    $hpPct += summoner_offhand_eff((int) ($u['id'] ?? 0))['turns'] * 20;
    $hpPct += $bonusHpPct;
    // 召唤兽生命：基础生命 × 召唤兽生命%(装备 summon_hp + 契匣 hp + 折算)
    $shp = $php * $cfg['hp_mult'] * (1 + (summon_hp_bonus($u) + $hpPct) / 100);
    return [
        'kind' => $kind,
        'name' => $cfg['name'],
        'hp' => max(1, (int) $shp),
        'maxhp' => max(1, (int) $shp),
        'atk' => max(1, (int) ($patk * $cfg['atk_mult'] * $sp)),
        'bomb' => $cfg['bomb'] || equip_set_owned_count((int) ($u['id'] ?? 0), 'soulpact') >= 5,
    ];
}

// 尝试召唤（受上限约束）。$bonusHpPct：把旧技能里的"存活+1轮"折算成生命（每轮=+15%）
function summon_add(string $kind, array &$u, int $bonusHpPct = 0): string
{
    $list = summon_list();
    $cap = summon_cap($u);
    if (count($list) >= $cap) {
        $list = array_slice($list, 0, $cap - 1);
    }
    // 尼德霍格的残鳞：召唤即叠层（先叠层再算召唤物，吃团队增益）
    $rescaleMsg = '';
    if (isset(summoner_legend_worn((int) ($u['id'] ?? 0))['slegend_rescale'])) {
        $rescaleMsg = summoner_legend_rescale_add((int) ($u['id'] ?? 0));
        summoner_legend_rescale_apply($u);
    }
    $s = summon_spawn($kind, player_matk($u), (int) $u['maxhp'], $u, (float) $bonusHpPct * 15.0);
    $list[] = $s;
    summon_save($list);
    return $s['name'] . '应召而来（生命' . $s['maxhp'] . '）！' . $rescaleMsg;
}

// 1觉被动【魂契】：受击10%概率自动召唤小鬼
function summon_soulpact_trigger(array $u): string
{
    if (job_id_of($u) !== 'summoner') {
        return '';
    }
    if ((cflags((int) ($u['id'] ?? 0))['rb1_job'] ?? '') !== 'summoner') {
        return '';
    }
    if (mt_rand(1, 100) > 10) {
        return '';
    }
    return summon_add('imp', $u);
}

// 召唤兽行动：自动攻击（无回合上限，死亡才消失，死亡由 summon_absorb 结算）
function summon_turn(array &$b): array
{
    $list = summon_list();
    if ($list === []) {
        return ['dmg' => 0, 'log' => '', 'left' => []];
    }
    $uid = (int) ($_SESSION['uid'] ?? 0);
    $u = user_by_id($uid);
    $worn = summoner_legend_worn($uid);
    $hiss = isset($worn['slegend_hiss']);          // 嘶鸣：召唤物必定暴击
    $thorn = $worn['slegend_soulthorn'] ?? null;   // 灵魂荆棘：附带真实伤害
    $frenzy = isset($worn['slegend_abyssseal']) && count($list) >= 4; // 深渊统御之印：≥4只狂乱
    $critMult = $u ? summoner_legend_crit_mult($u) : 1.5;
    $dmg = 0;
    $log = '';
    foreach ($list as $s) {
        $hit = max(1, (int) ($s['atk'] * random_int(85, 115) / 100));
        $isCrit = false;
        if ($hiss) {
            $isCrit = true;
            $hit = (int) round($hit * $critMult);
        }
        if ($frenzy) {
            // 狂乱：伤害+20%，10%概率破防重击（召唤物本就不吃防御，改判额外50%）
            $hit = (int) round($hit * 1.2);
            if (mt_rand(1, 100) <= 10) {
                $hit = (int) round($hit * 1.5);
            }
        }
        $trueTag = '';
        if ($thorn !== null) {
            $pct = $isCrit ? 30 : 15; // 灵魂荆棘：15%，暴击翻倍30%
            $td = max(1, (int) round($hit * $pct / 100));
            $hit += $td;
            $trueTag = '（灵魂撕裂真伤' . $td . '）';
        }
        $dmg += $hit;
        $log .= $s['name'] . '扑上去，造成' . $hit . '点暗影伤害' . ($isCrit ? '（暴击）' : '') . $trueTag . '。';
        if (isset($worn['slegend_mammoneye']) && mt_rand(1, 100) <= 5) {
            $b['stun'] = max((int) ($b['stun'] ?? 0), 2); // 玛门契约之眼：5%麻痹2回合
            $log .= '（玛门之眼·麻痹）';
        }
    }
    // 召唤物不随回合消散，存活列表原样保留，只有被打死才在 summon_absorb 里移除
    return ['dmg' => $dmg, 'log' => $log, 'left' => $list];
}

// 召唤兽承伤：优先由召唤兽吃伤害（战报写清怪物打的是召唤物还是你）
function summon_absorb(int $md, array &$b): array
{
    $list = summon_list();
    if ($list === [] || $md <= 0) {
        return [$md, ''];
    }
    $uid = (int) ($_SESSION['uid'] ?? 0);
    $first = $list[0];
    $abs = min($md, (int) $first['hp']);
    $first['hp'] -= $abs;
    $rest = $md - $abs;
    $hpAfter = max(0, (int) $first['hp']);
    $log = '怪物扑向【' . $first['name'] . '】，它挨了' . $abs . '点（' . $hpAfter . '/' . (int) $first['maxhp'] . '）';
    if ($first['hp'] <= 0) {
        $log .= '，被打散了！';
        // 遗神铸庭·彼岸：任意召唤物阵亡 → 玩家当场死亡
        if (isset(legend_relic_worn((int) $uid)['lg_othershore'])) {
            $GLOBALS['_lg_osh_die'] = 1;
            $log .= '【彼岸】它死了，你也要死！';
        }
        if (!empty($first['bomb'])) {
            $boom = max(1, (int) $first['atk']);
            $b['hp'] = max(0, (int) ($b['hp'] ?? 0) - $boom);
            $log .= '尸骸炸开，溅出' . $boom . '点暗影伤害。';
        }
        $list = array_slice($list, 1);
        $expl = summoner_legend_summon_death($b, $first, $uid);
        if ($expl !== '') {
            $log .= $expl;
        }
    } else {
        $log .= '。';
        $list[0] = $first;
    }
    summon_save($list);
    return [$rest, $log];
}

// 召唤兽死亡爆炸（2觉·死灵之拥）
function summon_bomb(array &$b): string
{
    $list = summon_list();
    $uid = (int) ($_SESSION['uid'] ?? 0);
    $rb2 = cflags($uid)['rb2_job'] ?? '';
    $fullSet = equip_set_owned_count($uid, 'soulpact') >= 5;
    if (($rb2 !== 'necro' && !$fullSet) || $list === []) {
        return '';
    }
    $log = '';
    foreach ($list as $s) {
        if (!empty($s['bomb'])) {
            $d = max(1, (int) ($s['atk'] * 1.5));
            $b['hp'] = max(0, (int) $b['hp'] - $d);
            $log .= $s['name'] . '死亡爆炸，造成' . $d . '点范围伤害！';
        }
    }
    if ($log !== '') {
        summon_save([]);
    }
    return $log;
}
function soul_truedmg(int $uid): int
{
    $uid = (int) $uid;
    $t = (int) gear_stats($uid)['truedmg'];
    $st = db()->prepare('SELECT id, name FROM equips WHERE uid=? AND slot=? AND pos=?');
    $st->execute([$uid, 'soul', 'wear']);
    while ($r = $st->fetch()) {
        $lv = soul_lv($uid, (int) $r['id']);
        if ($lv <= 0) { continue; }
        foreach (soul_tiers() as $tk => $tv) {
            if (str_contains((string) $r['name'], $tv['name'])) { $t += (int) $tv['base'] * $lv; }
        }
    }
    return $t;
}
function soul_upgrade(int $uid, int $eid): string
{
    $uid = (int) $uid; $eid = (int) $eid;
    $st = db()->prepare('SELECT * FROM equips WHERE id=? AND uid=?');
    $st->execute([$eid, $uid]);
    $e = $st->fetch();
    if (!$e || ($e['slot'] ?? '') !== 'soul') { return '不是你的神魂。'; }
    $lv = soul_lv($uid, $eid);
    if ($lv >= 10) { return '神魂已满X级，到顶了。'; }
    $cost = max(10, $lv * 5);
    $mats = mats_of($uid);
    if ((int) ($mats['soul_shard'] ?? 0) < $cost) {
        return '神魂碎片不够（升到' . ($lv + 1) . '级要' . $cost . '个，当前' . (int) ($mats['soul_shard'] ?? 0) . '个）。';
    }
    add_mat($uid, 'soul_shard', -$cost);
    mat_set($uid, 'soul_lv_' . $eid, $lv + 1);
    return '神魂升到' . ($lv + 1) . '级！真伤+' . soul_truedmg($uid) . '（穿戴中）。';
}
// ---- 世界BOSS（六圣柱·六沙王）读写入口，实现见 includes/dragon_wb.php ----
function wb_day(): int
{
    return (int) date('Ymd');
}

// 悬赏板：每日3单，按日期轮换，杀够数领钱领药
function bounty_list(): array
{
    return [
        ['mid' => 'frost_wolf', 'need' => 60, 'gold' => 200000, 'pot' => 'hp_pct20'],
        ['mid' => 'glacier_golem', 'need' => 40, 'gold' => 300000, 'pot' => 'hp_pct20'],
        ['mid' => 'winter_knight', 'need' => 40, 'gold' => 350000, 'pot' => 'hp_pct35'],
        ['mid' => 'mist_treant', 'need' => 60, 'gold' => 400000, 'pot' => 'hp_pct20'],
        ['mid' => 'lava_hound', 'need' => 50, 'gold' => 500000, 'pot' => 'hp_pct35'],
        ['mid' => 'south_flame_guard', 'need' => 30, 'gold' => 800000, 'pot' => 'hp_pct50'],
        ['mid' => 'shadow_claw', 'need' => 5, 'gold' => 600000, 'pot' => 'hp_pct35'],
        ['mid' => 'pz_golem', 'need' => 20, 'gold' => 500000, 'pot' => 'hp_pct35'],
        ['mid' => 'sen_guard', 'need' => 15, 'gold' => 700000, 'pot' => 'hp_pct50'],
    ];
}

function bounty_today(): array
{
    $all = bounty_list();
    $d = (int) date('z');
    $out = [];
    for ($i = 0; $i < 3; $i++) {
        $out[] = $all[($d + $i) % count($all)];
    }
    return $out;
}

function bounty_claim(int $uid, int $i): string
{
    $list = bounty_today();
    if (!isset($list[$i])) { return '没这单。'; }
    $b = $list[$i];
    $day = date('Ymd');
    $key = 'bq' . $day . '_' . $i;
    $f = cflags($uid);
    if (!empty($f[$key . '_done'])) { return '这单今天交过了，明天再来。'; }
    if (empty($f[$key])) {
        $c = kill_counts($uid);
        cflag_set($uid, $key, [$b['mid'] => (int) ($c[$b['mid']] ?? 0)]);
        return '接下悬赏：' . monsters()[$b['mid']]['name'] . '×' . $b['need'] . '，杀完回来交！';
    }
    $base = $f[$key];
    if (!is_array($base)) { $base = []; }
    $c = kill_counts($uid);
    $got = (int) ($c[$b['mid']] ?? 0) - (int) ($base[$b['mid']] ?? 0);
    if ($got < $b['need']) { return '还差' . ($b['need'] - $got) . '只，杀完再来。'; }
    cflag_set($uid, $key . '_done', 1);
    $u = user_by_id($uid);
    $u['gold'] = (int) $u['gold'] + $b['gold'];
    user_save($u);
    add_mat($uid, $b['pot'], 2);
    return '悬赏完成！得' . fmt_money($b['gold']) . '+' . mat_name($b['pot']) . '×2！';
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
        10 => ['rate' => 20, 'fail' => 'down', 'mat' => 'enhance_t3', 'cost' => 10],
        11 => ['rate' => 15, 'fail' => 'down', 'mat' => 'enhance_t3', 'cost' => 12],
        12 => ['rate' => 10, 'fail' => 'down', 'mat' => 'enhance_t3', 'cost' => 12],
        13 => ['rate' => 7, 'fail' => 'down', 'mat' => 'enhance_t3', 'cost' => 15],
        14 => ['rate' => 5, 'fail' => 'down', 'mat' => 'enhance_t3', 'cost' => 15],
        15 => ['rate' => 3, 'fail' => 'down', 'mat' => 'enhance_t3', 'cost' => 20],
    ];
}

function enhance_material_name(string $id): string
{
    return ['enhance_t1' => '下级强化石', 'enhance_t2' => '中级强化石', 'enhance_t3' => '上级强化石'][$id] ?? $id;
}

// 各地区铁匠NPC：强化只能找他们
function blacksmiths(): array
{
    $s = [
        'smithy' => '布隆',
        'smith' => '丹恩·铜须',
    ];
    // 第二弹区域的「根据地」也配一个铁匠（可用 smith.php 强化），名字贴合各区域主题
    if (function_exists('we2_base_ids')) {
        $bn = function_exists('we2_base_npcs') ? we2_base_npcs() : [];
        foreach (we2_base_ids() as $bid) {
            $s[(string) $bid] = (string) ($bn[$bid]['smith'] ?? '营地铁匠');
        }
    }
    return $s;
}

function enhance_per_level(int $lv): int
{
    // 每级强化给主属性加的百分比：+1~2每级5，+3~4每级10，+5~6每级15，+7起每级20
    if ($lv <= 2) {
        return 5;
    }
    if ($lv <= 4) {
        return 10;
    }
    if ($lv <= 6) {
        return 15;
    }
    return 20;
}

function enhance_rate(array $e): float
{
    $lv = max(0, (int) ($e['enhance_level'] ?? 0));
    $sum = 0;
    for ($i = 1; $i <= $lv; $i++) {
        $sum += enhance_per_level($i);
    }
    return 1 + $sum / 100;
}

function enhance_bonus_text(int $next): string
{
    if ($next === 7) {
        return '并觉醒一条额外词缀！';
    }
    if ($next === 12) {
        return '并再觉醒一条额外词缀！';
    }
    return '';
}

// +7/+12成功时从本部位词缀池里随机觉醒一条额外词缀
function enhance_grant_bonus_affix(int $eid): string
{
    $st = db()->prepare('SELECT * FROM equips WHERE id=?');
    $st->execute([$eid]);
    $e = $st->fetch();
    if (!$e) {
        return '';
    }
    $aff = json_decode((string) ($e['affixes'] ?? '[]'), true);
    if (!is_array($aff)) {
        $aff = [];
    }
    $have = [];
    $groups = [];
    $catalog = affix_catalog();
    foreach ($aff as $a) {
        $id = (string) ($a['id'] ?? $a['k'] ?? '');
        $have[$id] = true;
        $gr = (string) ($catalog[$id]['data']['group'] ?? '');
        if ($gr !== '') {
            $groups[$gr] = true;
        }
    }
    $pool = array_values(array_filter(slot_pools()[(string) ($e['slot'] ?? '')] ?? [], function ($id) use ($have, $groups, $catalog) {
        if (isset($have[$id])) {
            return false;
        }
        $gr = (string) ($catalog[$id]['data']['group'] ?? '');
        return $gr === '' || !isset($groups[$gr]);
    }));
    if ($pool === []) {
        return '';
    }
    $id = $pool[array_rand($pool)];
    $rolled = affix_roll($id, (int) ($e['quality'] ?? 0), (int) ($e['item_level'] ?? 1));
    $aff[] = ['id' => $id, 'tier' => $rolled['tier'], 'v' => $rolled['v'], 'bonus' => true];
    db()->prepare('UPDATE equips SET affixes=? WHERE id=?')->execute([json_encode($aff, JSON_UNESCAPED_UNICODE), $eid]);
    return '觉醒【' . affix_fmt($id, (float) $rolled['v'], $rolled['tier']) . '】！';
}

function enhance_equip(int $uid, int $eid): string
{
    $st = db()->prepare('SELECT * FROM equips WHERE id=? AND uid=?');
    $st->execute([$eid, $uid]);
    $e = $st->fetch();
    if (!$e) {
        return '没有这件装备。';
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
        $extra = '';
        if ($next === 7 || $next === 12) {
            $bmsg = enhance_grant_bonus_affix($eid);
            if ($bmsg !== '') {
                $extra = $bmsg;
            }
        }
        $multTxt = rtrim(rtrim(number_format(enhance_rate(['enhance_level' => $next]), 2, '.', ''), '0'), '.');
        return '强化成功！【' . equip_shortname($e['name']) . '】达到+' . $next . '，主属性×' . $multTxt . '。' . $extra;
    }
    if ($rule['fail'] === 'reset') {
        db()->prepare('UPDATE equips SET enhance_level=0, enhance_fail=enhance_fail+1 WHERE id=? AND uid=?')->execute([$eid, $uid]);
        _gear_uncache($uid);
        return '强化失败！装备强化等级归零。';
    }
    if ($rule['fail'] === 'down') {
        $down = max(0, (int) ($e['enhance_level'] ?? 0) - 1);
        db()->prepare('UPDATE equips SET enhance_level=?, enhance_fail=enhance_fail+1 WHERE id=? AND uid=?')->execute([$down, $eid, $uid]);
        _gear_uncache($uid);
        return '强化失败！装备掉到+' . $down . '（觉醒词缀保留）。';
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
    $u = user_by_id($uid);
    if ($u) {
        $reqErr = equip_can_wear($u, $eq);
        if ($reqErr !== '') {
            return $reqErr;
        }
    }
    $isTwo = weapon_is_twohand((string) $eq['name']);
    if ($isTwo) {
        $uo = user_by_id($uid);
        if (!job_can_twohand_weapon((string) ($uo['job'] ?? ''), (string) $eq['name'])) {
            return '双手武器太沉，你这个职业抡不动（战士全能，法师限双手法杖，猎手限长弓）。';
        }
    }
    if (($eq['slot'] ?? '') === 'offhand') {
        $mw = db()->query('SELECT name FROM equips WHERE uid=' . $uid . ' AND slot="weapon" AND pos="wear" LIMIT 1')->fetchColumn();
        if ($mw && weapon_is_twohand((string) $mw)) {
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
            sin_gain_array($u, 0);
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
    // 召唤师死灵传说穿戴缓存同样需要失效
    $GLOBALS['_legend_gen'] = (int) ($GLOBALS['_legend_gen'] ?? 0) + 1;
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
    $mBefore = gear_stats($uid)['energy'];
    db()->exec('UPDATE equips SET pos="" WHERE id=' . $eid);
    _gear_uncache($uid);
    $after = gear_stats($uid)['hp'];
    $mAfter = gear_stats($uid)['energy'];
    $delta = (int) ($after - $before);
    $mDelta = (int) ($mAfter - $mBefore);
    $u = user_by_id($uid);
    if ($u) {
        if ($delta !== 0) {
            $u['maxhp'] = max(1, (int) $u['maxhp'] + $delta);
            $u['hp'] = min((int) $u['maxhp'], (int) $u['hp']);
        }
        if ($mDelta !== 0) {
            $u['maxmp'] = max(0, (int) ($u['maxmp'] ?? 0) + $mDelta);
            $u['mp'] = min((int) $u['maxmp'], (int) ($u['mp'] ?? 0));
        }
        sin_gain_array($u, 0);
        user_save($u);
    }
    return '脱下了【' . $eq['name'] . '】。';
}

function player_atk(array $u): int
{
    return player_patk($u);
}

// 物理攻击：力量+装备。法师牧师恒为0
function player_patk(array $u): int
{
    if (in_array(job_id_of($u), ['mage', 'priest', 'summoner'], true)) {
        return 0;
    }
    $str = (int) ($u['str'] ?? 0);
    $gsP = gear_stats((int) ($u['id'] ?? 0));
    $base = (int) $u['atk'] + $str + (int) $gsP['atk'];
    // 物攻%
    $base += (int) ($base * (float) $gsP['atk_pct'] / 100);
    // 转生：基础属性 × 转生次数 × 10%
    if (function_exists('rs_stat_mult')) {
        $base = (int) round($base * rs_stat_mult((int) ($u['id'] ?? 0)));
    }
    // 1觉·浴血：生命每降低10%，攻击+4%，最多+32%
    if (!empty(cflags((int) ($u['id'] ?? 0))['rb1_job']) && cflags((int) ($u['id'] ?? 0))['rb1_job'] === 'warrior' && (int) ($u['maxhp'] ?? 0) > 0) {
        $lost = 1 - (int) $u['hp'] / max(1, (int) $u['maxhp']);
        $steps = min(8, (int) floor($lost * 10));
        if ($steps > 0) {
            $base = (int) ($base * (1 + $steps * 0.04));
        }
    }
    if (function_exists('legend_relic_atk_mult')) {
        $base = (int) round($base * legend_relic_atk_mult($u));
    }
    return $base;
}

// 魔法攻击（无属性）：智慧+法攻装备
function player_matk(array $u): int
{
    $job = job_id_of($u);
    $base = in_array($job, ['mage', 'priest', 'summoner'], true) ? (int) (jobs()[$job]['atk'] ?? 0) : 0;
    $m = $base + (int) ($u['int'] ?? 0) + (int) ($u['grow_matk'] ?? 0) + (int) gear_stats((int) ($u['id'] ?? 0))['matk'];
    if ($job !== '' && in_array($job, ['mage', 'priest', 'summoner'], true)) {
        $gs = gear_stats((int) ($u['id'] ?? 0));
        // 物攻/物攻% 也计入法攻（法系不点力量，装备上的物攻是法系唯一的固定攻来源）
        $m += (int) $gs['atk'] + (int) ($m * (float) $gs['atk_pct'] / 100);
        // 法攻%：法系专属百分比，只吃 matk_pct
        $m += (int) ($m * (float) $gs['matk_pct'] / 100);
    }
    // 转生：基础属性 × 转生次数 × 10%
    if (function_exists('rs_stat_mult') && $m > 0) {
        $m = (int) round($m * rs_stat_mult((int) ($u['id'] ?? 0)));
    }
    return $m;
}

function player_def(array $u): int
{
    return player_pdef($u);
}

// PVP 出手动词：按职业给出一点打斗味，别千篇一律“你砍”。
function pvp_verb(array $u): string
{
    $job = job_id_of($u);
    return ['warrior' => '你抡起兵器', 'hunter' => '你张弓搭箭', 'mage' => '你掷出法术', 'priest' => '你召来神罚', 'summoner' => '你驱使手下'][$job] ?? '你出手';
}

// ===== PVP 统一攻防换算 =====
// 历史 bug：PVP（巅峰/公会战/罪技PK/竞技场）都用 player_atk()，而法系(法师/牧师/召唤师)
// 的 player_atk 恒为 0，导致法系在 PVP 里每次只能打 1 点，等于白给。
// 这里按“进攻方真实输出类型”取攻、按“防守方对应防御”减伤，并让法系吃 mdef、物理吃 pdef。
function pvp_attack_of(array $u): array
{
    // 返回 [攻击力, 'phys'|'mag']
    $job = job_id_of($u);
    if (in_array($job, ['mage', 'priest', 'summoner'], true)) {
        return [max(1, player_matk($u)), 'mag'];
    }
    return [max(1, player_atk($u)), 'phys'];
}

// 一次 PVP 打击的伤害（未走浮动/暴击管线，调用方再套 player_dmg_pipeline）。
// $ub 为对方。返回 [基础伤害, 类型]。
// 注意：玩家 mdef 天然约为 pdef 的一半（mdef 公式是 def/2+...），
// 若法系直接吃 mdef 会白嫖一半减伤差，导致法系在 PVP 碾压物理。
// 故 PVP 统一用「pdef/mdef 混合防御」，物理法术站位对称，只靠攻/法攻的养成决胜负。
// 攻速连击：PVP 里也让攻速有用——按攻速概率追加半击（与 PVE 同一套 speed 口径）。
function pvp_hit_base(array $ua, array $ub): array
{
    [$atk, $type] = pvp_attack_of($ua);
    $def = (int) round((player_pdef($ub) + player_mdef($ub)) / 2);
    $d = max(1, (int) ($atk * 0.9) + random_int(0, 3) - (int) ($def * 0.5));
    $d = max(1, $d);
    // 攻速连击（PVP 手感）
    $spd = (float) (gear_stats((int) ($ua['id'] ?? 0))['speed'] ?? 0);
    if ($spd > 0 && mt_rand(1, 10000) / 100 <= min(45.0, $spd * 2.5)) {
        $d += max(1, (int) round($d * 0.5));
    }
    return [$d, $type];
}

// 物理防御：护甲+坚毅+装备
function player_pdef(array $u): int
{
    $v = (int) $u['def'] + (int) ($u['agi'] ?? 0) + (int) ($u['grow_def'] ?? 0) + (int) gear_stats((int) ($u['id'] ?? 0))['def'];
    if (function_exists('rs_stat_mult')) {
        $v = (int) round($v * rs_stat_mult((int) ($u['id'] ?? 0)));
    }
    if (function_exists('legend_relic_def_mult')) {
        $v = (int) round($v * legend_relic_def_mult($u));
    }
    return $v;
}

// 魔法防御：护甲一半+坚毅+抗性装备（坚毅既加物防也加法防）
function player_mdef(array $u): int
{
    $v = (int) ((int) $u['def'] / 2) + (int) ($u['agi'] ?? 0) + (int) ($u['grow_def'] ?? 0) + (int) gear_stats((int) ($u['id'] ?? 0))['mdef'];
    if (function_exists('rs_stat_mult')) {
        $v = (int) round($v * rs_stat_mult((int) ($u['id'] ?? 0)));
    }
    if (function_exists('legend_relic_def_mult')) {
        $v = (int) round($v * legend_relic_def_mult($u));
    }
    return $v;
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
        'shadow_walker' => 55, 'corrupt_warder' => 65, 'whisperer' => 80, 'rift_hound' => 88, 'rift_weaver' => 97, 'colossus' => 95, 'abaddon' => 120,
        'gy_kindness' => 1, 'gy_love' => 1, 'gy_friendship' => 1, 'gy_courage' => 1, 'gy_hope' => 1, 'gy_trust' => 1, 'gy_mercy' => 1, 'gy_justice' => 1,
        'god_seraph' => 720, 'god_cloud' => 745, 'god_watcher' => 770, 'god_judge' => 795, 'god_warden' => 815, 'god_forger' => 840, 'god_golem' => 865, 'god_titan' => 900, 'god_avatar' => 935,
        'gf_shade' => 820, 'gf_void' => 850, 'gf_reaper' => 880, 'gf_herald' => 915, 'god_emperor' => 999,
        'god_cherub' => 710, 'god_heraldg' => 785, 'god_highpriest' => 890, 'god_archon' => 960, 'gf_seraphim' => 890,
        'god_pilgrim' => 720, 'god_librarian' => 805, 'god_gardener' => 845, 'god_mirror' => 905, 'god_sleeper' => 930, 'god_serpent' => 945, 'gf_fallen' => 830, 'yz_boss' => 999,
        'road_wolf' => 22, 'road_bandit' => 26, 'dust_raider' => 80, 'dust_marauder' => 95, 'snow_drifter' => 325, 'snow_reaver' => 335, 'ash_drifter' => 470, 'ash_reaver' => 485,
        'dark_slime' => 20, 'swamp_slime' => 21, 'swamp_king' => 25,
        'puppet_guard' => 130, 'masked_noble' => 150, 'ink_puppet' => 180, 'silver_undead' => 210,
        'silver_assassin' => 240, 'puppet_priest' => 270, 'star_puppet' => 300, 'silver_puppet' => 310, 'string_puller' => 320,
        'silk_spider' => 280, 'cocoon_guard' => 295, 'thread_weaver' => 305, 'silk_moth' => 315,
        'nest_watcher' => 325, 'brood_maiden' => 335, 'silk_mother' => 340,
        'frost_wolf' => 350, 'ice_specter' => 360, 'snow_mammoth' => 370, 'blizzard_shaman' => 380,
        'glacier_golem' => 390, 'frost_giant' => 400, 'aurora_serpent' => 410, 'icewyrm' => 420,
        'winter_knight' => 435, 'frost_matriarch' => 450, 'glacier_colossus' => 465, 'winter_king' => 480,
        'fr_crawler' => 430, 'fr_stalker' => 445, 'fr_warden' => 460, 'fr_horror' => 475, 'fr_leviathan' => 495,
        'mist_treant' => 500, 'spore_colony' => 515, 'thorn_hunter' => 530, 'ancient_boa' => 545,
        'mist_apeman' => 560, 'rot_sage' => 575, 'ember_imp' => 590, 'lava_hound' => 605,
        'ash_cultist' => 620, 'magma_golem' => 635, 'flame_djinn' => 650, 'cinder_dragonspawn' => 665,
        'volcano_priest' => 680, 'ash_wyrm' => 695, 'south_flame_guard' => 710, 'ancient_ruin_golem' => 720,
        'em_slag' => 640, 'em_fiend' => 660, 'em_magma' => 680, 'em_ashlord' => 700, 'em_heart' => 720, 'tt_rock' => 720, 'tt_crag' => 760, 'tt_magma' => 800, 'tt_warden' => 850, 'tt_titan' => 900,
        'pz_puller' => 380, 'pz_dancer' => 400, 'pz_mask' => 420, 'pz_golem' => 440, 'pz_master' => 460,
        'sen_priest' => 520, 'sen_thorn' => 540, 'sen_serpent' => 560, 'sen_guard' => 580, 'sen_avatar' => 600,
        'shadow_claw' => 320, 'brass_bishop' => 480, 'tundra_wanderer' => 450, 'lucky_goblin' => 5, 'golden_slime' => 100, 'mimic_chest' => 200,
        'frostborn_hunter' => 430,
        'vx_spawn' => 600, 'vx_stalker' => 620, 'vx_mage' => 640, 'vx_knight' => 660, 'vx_herald' => 670, 'vx_tyrant' => 680, 'avalanche_titan' => 470, 'sulfur_sprite' => 610, 'obsidian_colossus' => 700,
        'cinder_crawler' => 600, 'cinder_stalker' => 620, 'cinder_warden' => 640, 'cinder_horror' => 660, 'ash_titan' => 680,
        'gr_hyena' => 700, 'gr_vulture' => 705, 'gr_worm' => 710, 'gr_raider' => 715, 'gr_storm' => 720, 'gr_bat' => 725, 'gr_guard' => 730, 'gr_whelp' => 735, 'gr_wraith' => 740, 'gr_spider' => 745, 'gr_retainer' => 750, 'gr_shaman' => 755, 'gr_fang' => 760, 'gr_idol' => 765, 'gr_guru' => 780, 'gr_brood' => 785, 'gr_shard' => 790, 'gr_shadow' => 795, 'gr_nightmare' => 800, 'gr_ancient' => 810,
        'ry_crawler' => 720, 'ry_guard' => 760, 'ry_shaper' => 800, 'ry_warder' => 850, 'ry_titan' => 900,
        'doyle' => 999,
    ];
    static $wnMerged = false;
    if (!$wnMerged) {
        foreach (world_expansion_lv() as $__wk => $__wv) {
            if (!isset($lv[$__wk])) {
                $lv[$__wk] = $__wv;
            }
        }
        foreach (region_dungeon_lv() as $__rk => $__rv) {
            if (!isset($lv[$__rk])) {
                $lv[$__rk] = $__rv;
            }
        }
        $wnMerged = true;
    }
    if (isset($lv[$mid])) {
        return $lv[$mid];
    }
    // 六圣柱专属小怪
    $dm = dragon_monster($mid);
    return (int) ($dm['lv'] ?? 1);
}

// 地图上展示的怪物等级（仅用于显示，不影响战斗/经验结算）。
// 回响·雷恩在剧情14后进入二阶段（血量2000/攻击85，明显变强），别再把它显示成一阶段的14级。
function monster_shown_lv(string $mid, ?array $u = null): int
{
    if ($mid === 'echo_rayne' && $u !== null && (int) ($u['quest'] ?? 0) > 14) {
        return 30;
    }
    return monster_lv($mid);
}

// 经验按怪物等级锚定升级需求：同级怪约(10+人物等级)只升一级，越级加成封顶20%
function exp_gain_for(array $u, string $mid, int $elite = 0): int
{
    $mlv = monster_lv($mid);
    $plv = max(1, (int) ($u['lv'] ?? 1));
    $base = exp_need($mlv) / (10 + $plv);
    $diff = $mlv - $plv;
    if ($diff <= -4) {
        $v = $base * 0.2;
    } elseif ($diff < 0) {
        $v = $base * (1 + $diff * 0.1);
    } elseif ($diff === 0) {
        $v = $base;
    } else {
        $v = $base * min(1.2, 1 + $diff * 0.05);
    }
    if ($elite === 1) {
        $v *= 3;
    } elseif ($mid === boss_of_map((string) ($u['loc'] ?? ''))) {
        $v *= 8;
    }
    return max(1, (int) $v);
}

function death_penalty(array &$u, float $mult = 1.0): string
{
    summoner_legend_rescale_clear($u); // 残鳞：倒下时恢复原始最大生命
    $gloss = (int) ((int) $u['gold'] * 0.3 * $mult);
    $u['gold'] = max(0, (int) $u['gold'] - $gloss);
    $eloss = (int) (exp_need((int) $u['lv']) * 0.3 * $mult);
    $u['exp'] = max(0, (int) $u['exp'] - $eloss);
    $u['hp'] = 1;
    $u['loc'] = 'camp';
    return '丢了' . fmt_money($gloss) . '，经验-' . $eloss . '。';
}

function gain_exp(array &$u, int $exp): string
{
    $mm = mats_of((int) ($u['id'] ?? 0));
    $card = $mm['exp_card_until'] ?? 0;
    if ($card > time()) {
        $exp = $exp * max(2, (int) ($mm['exp_card_mult'] ?? 2));
    }
    // 1觉卡级：三章通关（quest>=38）未1觉（rb1_job为空），299级经验冻结
    $rbDone = !empty(cflags((int) ($u['id'] ?? 0))['rb1_job']);
    // 已转生者不受299级冻结约束（转生后可一路修到999）
    $rsN0 = function_exists('rs_count') ? rs_count((int) $u['id']) : 0;
    if ((int) ($u['quest'] ?? 0) >= 38 && !$rbDone && $rsN0 === 0 && (int) ($u['lv'] ?? 1) >= 299) {
        return '经验+' . $exp . '（299级瓶颈：1觉后继续累积，去找瓦尔顿）';
    }
    // 转生：经验获取+50%
    $rsN = function_exists('rs_count') ? rs_count((int) $u['id']) : 0;
    if ($rsN > 0) {
        $exp = (int) ($exp * rs_exp_mult((int) $u['id']));
    }
    $u['exp'] = (int) $u['exp'] + $exp;
    $msg = '经验+' . $exp;
    // 999级封顶：满级后必须转生
    if ((int) $u['lv'] >= lv_cap()) {
        $u['exp'] = 0;
        return $msg . '（' . lv_cap() . '级已封顶：完成转生条件后可转生）';
    }
    while ((int) $u['exp'] >= exp_need((int) $u['lv'])) {
        $u['exp'] -= exp_need((int) $u['lv']);
        $u['lv'] = (int) $u['lv'] + 1;
        // 转生后每级成长翻倍
        $g = $rsN > 0 ? 2 : 1;
        // 升级成长按职业（*$g：转生后翻倍）。核心是每级给 3 点潜力由玩家自由分配，
        // 这里的自动成长只是小幅“职业特长”，大方向仍靠玩家点力量/坚毅/体质/智慧/灵力。
        // 注意：物攻/魔攻/物防法防都走独立的基础成长字段（grow_def/grow_matk/atk），
        // 不碰 5 个可分配属性（力量/坚毅/体质/智慧/灵力），免得面板“莫名其妙”自动加点。
        $jobUp = job_id_of($u);
        $growth = '';
        switch ($jobUp) {
            case 'warrior': // 物防法防+1（基础防御缩放入 pdef/mdef），魔力+2
                $u['grow_def'] = (int) ($u['grow_def'] ?? 0) + 1 * $g;
                $u['maxmp'] = (int) ($u['maxmp'] ?? 0) + 2 * $g;
                $growth = '物防法防+' . (1 * $g) . '，魔力+' . (2 * $g);
                break;
            case 'hunter': // 物攻+2（基础物攻字段），魔力+2
                $u['atk'] = (int) $u['atk'] + 2 * $g;
                $u['maxmp'] = (int) ($u['maxmp'] ?? 0) + 2 * $g;
                $growth = '物攻+' . (2 * $g) . '，魔力+' . (2 * $g);
                break;
            case 'mage': // 魔攻+2（基础法攻字段），魔力+2
                $u['grow_matk'] = (int) ($u['grow_matk'] ?? 0) + 2 * $g;
                $u['maxmp'] = (int) ($u['maxmp'] ?? 0) + 2 * $g;
                $growth = '魔攻+' . (2 * $g) . '，魔力+' . (2 * $g);
                break;
            case 'priest': // 生命+5
                $u['maxhp'] = (int) $u['maxhp'] + 5 * $g;
                $growth = '生命+' . (5 * $g);
                break;
            case 'summoner': // 生命+2，魔力+5
                $u['maxhp'] = (int) $u['maxhp'] + 2 * $g;
                $u['maxmp'] = (int) ($u['maxmp'] ?? 0) + 5 * $g;
                $growth = '生命+' . (2 * $g) . '，魔力+' . (5 * $g);
                break;
        }
        $u['hp'] = (int) $u['maxhp'];
        $u['mp'] = (int) ($u['maxmp'] ?? 0);
        $u['s_pts'] = (int) ($u['s_pts'] ?? 0) + 3;
        // 转生后：每达成一个百级档送1源罪点数
        if ($rsN > 0) {
            $stage = (int) ((int) $u['lv'] / 100);
            $got = (int) (cflags((int) $u['id'])['rs_stage'] ?? 0);
            if ($stage > $got) {
                cflag_set((int) $u['id'], 'rs_stage', $stage);
                mat_set((int) $u['id'], 'rs_point', (int) (mats_of((int) $u['id'])['rs_point'] ?? 0) + ($stage - $got));
                $msg .= '，源罪点数+' . ($stage - $got);
            }
        }
        $msg .= '！升级至' . lv_text((int) $u['id'], (int) $u['lv']) . '，伤势尽复，' . $growth . '，得3属性点';
    }
    sin_gain_array($u, 0);
    return $msg;
}

function alloc_stat(int $uid, string $k): string
{
    $u = user_by_id((int) $uid);
    if (!$u) {
        return '角色不存在。';
    }
    $cost = ['str' => 1, 'agi' => 1, 'vit' => 1, 'int' => 1, 'spi' => 1][$k] ?? 0;
    if ($cost <= 0) {
        return '只能加力量/坚毅/体质/智慧/灵力（全部1点1次）。';
    }
    if ((int) ($u['s_pts'] ?? 0) < $cost) {
        return '属性点不够（剩' . (int) ($u['s_pts'] ?? 0) . '点，要' . $cost . '点），升级获取。';
    }
    if ($k === 'str') {
        if (in_array(job_id_of($u), ['mage', 'priest', 'summoner'], true)) {
            return '法师牧师的身体练不出物理伤害，别浪费点数，去加智慧吧。';
        }
        $u['str'] = (int) ($u['str'] ?? 0) + 1;
    } elseif ($k === 'agi') {
        $u['agi'] = (int) ($u['agi'] ?? 0) + 1;
    } elseif ($k === 'vit') {
        $u['maxhp'] = (int) $u['maxhp'] + 10;
        $u['hp'] = (int) ($u['hp'] ?? 0) + 10;
        $u['vit'] = (int) ($u['vit'] ?? 0) + 1;
    } elseif ($k === 'int') {
        $u['int'] = (int) ($u['int'] ?? 0) + 1;
    } elseif ($k === 'spi') {
        $u['maxmp'] = (int) ($u['maxmp'] ?? 0) + 5;
        $u['mp'] = (int) ($u['mp'] ?? 0) + 5;
        $u['spi'] = (int) ($u['spi'] ?? 0) + 1;
    }
    $u['s_pts'] = (int) ($u['s_pts'] ?? 0) - $cost;
    sin_gain_array($u, 0);
    user_save($u);
    return '加点成功，剩' . (int) $u['s_pts'] . '点。';
}

function alloc_all_stat(int $uid, string $k): string
{
    $cost = ['str' => 1, 'agi' => 1, 'vit' => 1, 'int' => 1, 'spi' => 1][$k] ?? 0;
    if ($cost <= 0) {
        return '只能全加力量/坚毅/体质/智慧/灵力。';
    }
    $u = user_by_id((int) $uid);
    if (!$u) {
        return '角色不存在。';
    }
    if ($k === 'str' && in_array(job_id_of($u), ['mage', 'priest', 'summoner'], true)) {
        return '法师牧师的身体练不出物理伤害，别浪费点数，去加智慧吧。';
    }
    $n = 0;
    while ((int) ($u['s_pts'] ?? 0) >= $cost) {
        $r = alloc_stat((int) $uid, $k);
        if (!str_starts_with($r, '加点成功')) {
            break;
        }
        $u = user_by_id((int) $uid);
        $n++;
        if ($n > 5000) {
            break;
        }
    }
    return $n > 0 ? '全加成功，共加' . $n . '次。' : '属性点不够。';
}

function use_rename_card(int $uid, string $newName): string
{
    $u = user_by_id((int) $uid);
    if (!$u) {
        return '角色不存在。';
    }
    $newName = trim($newName);
    if ($newName === '' || preg_match('/^[\x{4e00}-\x{9fff}A-Za-z0-9_]{2,12}$/u', $newName) !== 1) {
        return '名字2-12字，汉字、字母、数字或下划线。';
    }
    $mats = mats_of($uid);
    if (empty($mats['rename_card'])) {
        return '你没有改名卡，去商城功能道具买。';
    }
    if ($newName === (string) $u['username']) {
        return '和现在的名字一样，不用改。';
    }
    $st = db()->prepare('SELECT id FROM users WHERE username=? AND zone=? AND id!=?');
    $st->execute([$newName, (string) ($u['zone'] ?? 'z1'), (int) $uid]);
    if ($st->fetch()) {
        return '这个名字在本大区已经被拿走了。';
    }
    add_mat($uid, 'rename_card', -1);
    $old = (string) $u['username'];
    db()->prepare('UPDATE users SET username=? WHERE id=?')->execute([$newName, (int) $uid]);
    db()->prepare('UPDATE chat_msgs SET username=? WHERE uid=?')->execute([$newName, (int) $uid]);
    return '改名成功：' . $old . '→' . $newName . '！';
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
    $back = (int) ($u['str'] ?? 0) + (int) ($u['agi'] ?? 0) + (int) ($u['vit'] ?? 0) + (int) ($u['int'] ?? 0) + (int) ($u['spi'] ?? 0);
    if ($back <= 0) {
        return '没加过点，不用洗。';
    }
    add_mat((int) $uid, 'reset_potion', -1);
    $u['maxhp'] = max(1, (int) $u['maxhp'] - (int) ($u['vit'] ?? 0) * 10);
    $u['hp'] = min((int) $u['hp'], (int) $u['maxhp']);
    $u['maxmp'] = max(0, (int) ($u['maxmp'] ?? 0) - (int) ($u['spi'] ?? 0) * 5);
    $u['mp'] = min((int) ($u['mp'] ?? 0), (int) ($u['maxmp'] ?? 0));
    $u['s_pts'] = (int) ($u['s_pts'] ?? 0) + $back;
    $u['str'] = 0;
    $u['agi'] = 0;
    $u['vit'] = 0;
    $u['int'] = 0;
    $u['spi'] = 0;
    sin_gain_array($u, 0);
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

function use_exp_cardx(int $uid, int $mult): string
{
    $u = user_by_id((int) $uid);
    if (!$u) { return '角色不存在。'; }
    $mid = $mult >= 1000 ? 'exp_card1000x' : 'exp_card10x';
    $mats = mats_of((int) $uid);
    if (empty($mats[$mid])) { return '你没有' . mat_name($mid) . '。'; }
    add_mat((int) $uid, $mid, -1);
    $left = max(0, (int) ($mats['exp_card_until'] ?? 0) - time());
    mat_set((int) $uid, 'exp_card_until', time() + 86400 + $left);
    mat_set((int) $uid, 'exp_card_mult', $mult);
    return mat_name($mid) . '生效！1天内' . $mult . '倍经验（可叠加延长）。';
}

// 怪物攻防拆分：物攻占7成，法攻占3成（有元素亲和的怪法攻占5成）；物防/法防按攻击折算
function monster_patk(array $m): int
{
    return (int) ((int) ($m['atk'] ?? 0) * 0.7);
}

function monster_matk(array $m): int
{
    $ratio = monster_element((string) ($m['id'] ?? '')) !== '' ? 0.5 : 0.3;
    return (int) ((int) ($m['atk'] ?? 0) * $ratio);
}

function monster_pdef(array $m): int
{
    return (int) ((int) ($m['atk'] ?? 0) * 0.2);
}

function monster_mdef(array $m): int
{
    return (int) ((int) ($m['atk'] ?? 0) * 0.15);
}

// ===== 伤害浮动管线 =====
// 一次伤害 = 基础 × 均匀浮动 × 档位(重击/完美一击) × 暴击。
// 浮动 / 档位 / 暴击 三段各自独立掷骰，所以「完美一击」与「暴击」可以同时触发，
// 战报会显示「完美一击!+暴击!」。精准(precision)只收窄均匀浮动（±18% 起步，
// 每点精准 -0.5%，最低 ±4%），不影响档位概率与暴击率——精准高＝伤害稳，
// 但该来的尖峰和暴击照样来。
function dmg_variance_half(float $precision): float
{
    return max(0.04, min(0.18, 0.18 - $precision * 0.005));
}

// 返回 [最终伤害, 战报标记]；标记可能为 ''、'重击!'、'完美一击!'、'暴击!'、'完美一击!+暴击!' 等
function dmg_pipeline(int $base, float $critRate = 0.0, float $critDmg = 0.0, float $precision = 0.0): array
{
    if ($base <= 0) {
        return [0, ''];
    }
    $bps = (int) round(dmg_variance_half($precision) * 10000);
    $factor = 1 + mt_rand(-$bps, $bps) / 10000; // 均匀浮动
    $tag = '';
    $grade = mt_rand(1, 10000);
    if ($grade <= 200) {           // 完美一击 2%
        $factor *= mt_rand(180, 220) / 100;
        $tag = '完美一击!';
    } elseif ($grade <= 1000) {    // 重击 8%
        $factor *= mt_rand(130, 150) / 100;
        $tag = '重击!';
    }
    if ($critRate > 0 && mt_rand(1, 10000) / 100 <= $critRate) { // 暴击独立骰，最后乘
        $factor *= 1 + $critDmg / 100;
        $tag = $tag === '' ? '暴击!' : $tag . '+暴击!';
    }
    return [max(1, (int) round($base * $factor)), $tag];
}

// 玩家侧收尾管线便捷封装：自动取该角色的暴击/暴伤/精准（含2觉游侠暴击加成）。
// 主战斗、PK/巅峰/公会战、罪技PK、龙王殿共用同一套判定。
function player_dmg_pipeline(array $u, int $base): array
{
    if ($base <= 0) {
        return [0, ''];
    }
    $gs = gear_stats((int) ($u['id'] ?? 0));
    return dmg_pipeline($base, $gs['crit'], $gs['critdmg'], (float) ($gs['precision'] ?? 0));
}

// 源罪/攻速·连击：速度越高，越可能追加一击。返回追加伤害（0=没触发）。
// 连击打的是同一目标，伤害按主伤害的比例，速度封顶避免无限叠。
function speed_combo_strike(array $u, int $pd, string &$log): int
{
    if ($pd <= 0) {
        return 0;
    }
    $spd = (float) gear_stats((int) ($u['id'] ?? 0))['speed'];
    if ($spd <= 0) {
        return 0;
    }
    $chance = min(45.0, $spd * 2.5);   // 攻速5→12.5%，攻速15→37.5%，上限45%
    if (mt_rand(1, 10000) / 100 > $chance) {
        return 0;
    }
    $dmg = max(1, (int) round($pd * 0.5)); // 追打按主伤害50%
    $log .= '(连击' . $dmg . ')';
    return $dmg;
}

function dmg_to_monster(array $u, array $m = []): int
{
    // 法师牧师普攻转元素（法师火、牧师光），不再造成物理伤害
    $job = job_id_of($u);
    if (in_array($job, ['mage', 'priest', 'summoner'], true)) {
        $el = $job === 'mage' ? 'fire' : ($job === 'summoner' ? 'dark' : 'light');
        $d = max(1, player_matk($u) + random_int(1, 3) - (int) (monster_mdef($m) / 2));
        [$d, $elTag] = apply_el_convert($u, $m, $d, $el);
        $GLOBALS['_melee_el_tag'] = $elTag;
        return $d;
    }
    // 普攻是物理的：物攻-怪物业防。装备暴击统一在战斗收尾管线判定，这里不再算。
    $d = max(1, player_patk($u) + random_int(0, 3) - monster_pdef($m));
    return $d;
}

// 怪物打玩家：同样走浮动管线（档位重击/完美一击）。返回 [伤害, 标记]。
// 怪没有装备暴击（critRate=0），精准用玩家的——玩家堆精准也提高承伤稳定性。
function dmg_to_player(array $u, array $m): array
{
    $gs = gear_stats((int) ($u['id'] ?? 0));
    $dodge = $gs['dodge'] + hunter_assassin_dodge((int) ($u['id'] ?? 0));
    if (function_exists('legend_relic_bonus_dodge')) {
        $dodge += legend_relic_bonus_dodge($u);
    }
    // 源罪技能树·源罪之逃：残血<30% 闪避+30
    if (function_exists('src_sin_effects') && (int) ($u['maxhp'] ?? 0) > 0 && (int) $u['hp'] < (int) $u['maxhp'] * 0.3) {
        $seDodge = src_sin_effects((int) ($u['id'] ?? 0));
        if (!empty($seDodge['flee'])) { $dodge += 30; }
    }
    if ($dodge > 0 && mt_rand(1, 10000) / 100 <= $dodge) {
        $GLOBALS['_lg_dodged'] = 1; // 供夜幕判定"上次闪避"
        return [0, '闪避'];
    }
    // 怪物伤害拆两段：物理段吃物防，法术段吃法防
    $phys = max(0, monster_patk($m) + random_int(0, 2) - player_pdef($u));
    $mag = max(0, monster_matk($m) + random_int(0, 1) - player_mdef($u));
    // 怪物元素攻击全额转对应属性：吃玩家同系抗性（装备+附魔，上限80%）
    $mel = monster_element((string) ($m['id'] ?? ''));
    if ($mel !== '' && $mag > 0) {
        $pres = (float) ($gs['res_' . $mel] ?? 0) + enchant_el_res((int) ($u['id'] ?? 0), $mel);
        $mag = (int) ($mag * (1 - min(80, max(0, $pres)) / 100));
    }
    [$md, $tag] = dmg_pipeline(max(1, $phys + $mag), 0.0, 0.0, (float) ($gs['precision'] ?? 0));
    return [$md, $tag];
}

// 技能全额转属性：伤害整体吃克制与抗性
function apply_el_convert(array $u, array $b, int $pd, string $el): array
{
    $meld = monster_element((string) ($b['id'] ?? ''));
    $chart = element_chart($el, $meld);
    $mres = monster_resist((string) ($b['id'] ?? ''), $el);
    $pd = max(1, (int) ($pd * $chart * (1 - $mres / 100)));
    $tag = '(' . element_name($el) . '属性伤害' . ($chart > 1 ? '·克制' : ($chart < 1 ? '·被抵抗' : '')) . ')';
    $rb2e = cflags((int) ($u['id'] ?? 0))['rb2_job'] ?? '';
    if ($rb2e === 'wintermage' && in_array($el, ['ice', 'thunder'], true)) { $pd = (int) ($pd * 1.3); $tag .= '(凛冬)'; }
    if ($rb2e === 'wildfire' && in_array($el, ['fire', 'wind'], true)) { $pd = (int) ($pd * 1.4); $tag .= '(燎原)'; }
    if (function_exists('legend_relic_el_mult')) {
        $em = legend_relic_el_mult((int) ($u['id'] ?? 0), $el);
        if ($em > 1.0) { $pd = (int) round($pd * $em); }
    }
    return [$pd, $tag];
}

function battle_round(array &$u, array &$b, string $mode): array
{
    // 尼德霍格的残鳞：每轮按层数重算最大生命（战斗中被 user_save 落库）
    if (job_id_of($u) === 'summoner') {
        summoner_legend_rescale_apply($u);
    }
    $pd = dmg_to_monster($u, $b);
    if ($mode !== 'hit') { unset($GLOBALS['_melee_el_tag']); }
    $b['elTagged'] = 0;
    $isSkill = false;
    $skillName = '';
    $logHeal = '';
    $logNoMp = '';
    $profMsg = '';
    $skEl = '';
    $forceCrit = false;   // 夜幕绞杀10级必暴；普攻/无技能回合为 false
    if ($mode !== 'hit') {
        $job = job_id_of($u);
        $sk = active_skill((int) ($u['id'] ?? 0), $job);
        // 召唤型技能但召唤兽已满上限：不再白白耗蓝重复召唤，改为按普通攻击出手
        if ($sk !== null && $job === 'summoner' && ($sk['kind'] ?? '') === 'summon' && count(summon_list()) >= summon_cap($u)) {
            $sk = null;
        }
        if ($sk === null) {
            $isSkill = true;
            if ($job === 'warrior') {
                $pd = (int) ($pd * 1.5);
            } elseif ($job === 'mage') {
                // 法师平A也是搓魔力弹：看法攻
                $pd = max(1, player_matk($u) + random_int(4, 7) - (int) (monster_mdef($b) / 2));
            } elseif ($job === 'hunter') {
                $pd = (int) ($pd * 0.8) + (int) ($pd * 0.8);
            } elseif ($job === 'priest') {
                $u['hp'] = min((int) $u['maxhp'], (int) $u['hp'] + 25);
                $pd = max(1, (int) (player_matk($u) * 0.5) + 2 - (int) (monster_mdef($b) / 2));
            } elseif ($job === 'summoner') {
                // 召唤已满：普通暗影攻击，不再空耗蓝重复召唤，也不献祭生命（否则挂机每回合掉血暴毙）
                if (count(summon_list()) >= summon_cap($u)) {
                    $pd = max(1, (int) (player_matk($u)) + 2 - (int) (monster_mdef($b) / 2));
                    $logHeal .= '(召唤已满·普攻)';
                } elseif ((int) ($u['hp'] ?? 0) > (int) $u['maxhp'] * 0.3) {
                    // 召唤师平A：献祭5%生命换伤害（生命高于30%才献祭，避免挂机自杀）
                    $cost = max(1, (int) ($u['hp'] * 0.05));
                    $u['hp'] = max(1, (int) $u['hp'] - $cost);
                    $pd = max(1, (int) (player_matk($u) * 0.85) + 2 - (int) (monster_mdef($b) / 2));
                    $logHeal .= '(献祭' . $cost . '血)';
                } else {
                    // 残血：改用不献祭的暗影普攻，优先保命
                    $pd = max(1, (int) (player_matk($u)) + 2 - (int) (monster_mdef($b) / 2));
                    $logHeal .= '(残血·免献祭)';
                }
            }
            $skillName = job_of($u)['skill'];
        } elseif ((int) ($u['mp'] ?? 0) >= (int) $sk['mp']) {
            $isSkill = true;
            $u['mp'] = (int) $u['mp'] - (int) $sk['mp'];
            $skillName = (string) $sk['name'];
            $sid = (string) $sk['skill'];
            $profMsg = add_skill_prof((int) ($u['id'] ?? 0), $sid);
            $skEl = (string) ($sk['el'] ?? '');
            $pow = skill_power($sk);
            $sTier = (int) $sk['tier'];
            $sLv = (int) $sk['level'];
            if ($sk['kind'] === 'phys') {
                // 物理技：物攻×倍率后过怪物业防；带元素 rider 的再加一段法伤（过半法防）
                $pd = max(1, (int) ((player_patk($u) + random_int(0, 3)) * (float) $sk['mult'] * $pow) - monster_pdef($b));
                // 战士方向标签：tank=吃双防，rage=吃本场损血数值
                if (!empty($sk['tank'])) {
                    $pd += warr_tank_bonus($u);
                    $logHeal .= '(铁壁+' . warr_tank_bonus($u) . ')';
                }
                if (!empty($sk['rage'])) {
                    $rm = warr_rage_mult($b, (int) ($u['id'] ?? 0));
                    $pd = (int) round($pd * $rm);
                    if ($sTier >= 10) {
                        // 10级：狂战加成翻倍（用平方近似）
                        $pd = (int) round($pd * (2 * $rm - 1));
                    }
                    $rt = warr_rage_text($b, (int) ($u['id'] ?? 0));
                    if ($rt !== '') {
                        $logHeal .= $rt;
                    }
                }
                if ($skEl !== '') {
                    $pd += max(0, (int) (player_matk($u) * 0.4) - (int) (monster_mdef($b) / 2));
                    [$pd, $elTag] = apply_el_convert($u, $b, $pd, $skEl);
                    $logHeal .= $elTag;
                    $b['fullEl'] = 1;
                }
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
                if ($sid === 'w4' && $sTier >= 10 && mt_rand(1, 100) <= 25) {
                    $pd = (int) ($pd * 1.6);
                    $logHeal .= '(烬灭暴击)';
                }
            } elseif ($sk['kind'] === 'magic') {
                // 纯法术：看法攻，过一半法防；带属性的整体转属性伤害（火球术就是全额火伤）
                $pd = max(1, (int) ((player_matk($u) + random_int(4, 7) + ($sid === 'm2' ? 10 : ($sid === 'm3' ? 8 : 5))) * $pow) - (int) (monster_mdef($b) / 2));
                if ($skEl !== '') {
                    [$pd, $elTag] = apply_el_convert($u, $b, $pd, $skEl);
                    $logHeal .= $elTag;
                    $b['fullEl'] = 1;
                }
                if ($sTier >= 10) {
                    $pd = (int) ($pd * 1.3);
                }
                if ($sLv >= 20) {
                    $pd = (int) ($pd * 1.15);
                }
                if ($sid === 'm2' && $sTier >= 10 && mt_rand(1, 100) <= 12) {
                    $b['stun'] = 1;
                    $logHeal .= '(麻痹)';
                }
                if ($sid === 'm3' && $sTier >= 10 && mt_rand(1, 100) <= 5) {
                    $b['frost'] = 3;
                    $logHeal .= '(冻结)';
                }
                if ($sid === 'm4' && $sTier >= 10 && mt_rand(1, 100) <= 10) {
                    $b['frost'] = 3;
                    $logHeal .= '(冰葬冻结)';
                }
            } elseif ($sk['kind'] === 'multi2') {
                // 猎人连射：物理段过物防；带元素的技能再加一段法伤，整体转属性。
                // 猎人职业特色核心：每个技能都往目标身上挂 DOT 层数。
                $pd = max(1, (int) ((player_patk($u) + random_int(0, 3)) * (float) $sk['mult'] * $pow) - monster_pdef($b));
                if ($skEl !== '') {
                    $pd += max(0, (int) (player_matk($u) * 0.5) - (int) (monster_mdef($b) / 2));
                    [$pd, $elTag] = apply_el_convert($u, $b, $pd, $skEl);
                    $logHeal .= $elTag;
                    $b['fullEl'] = 1;
                }
                if ($sTier >= 10) {
                    $pd = (int) ($pd * 1.5);
                }
                if ($sLv >= 20) {
                    $pd = (int) ($pd * 1.15);
                }
                if ($sid === 'h4' && in_array(($b['id'] ?? ''), ['cinder_dragonspawn', 'ash_wyrm', 'south_flame_guard', 'doyle', 'dragon'], true)) {
                    $pd = (int) ($pd * 1.5);
                    $logHeal .= '(龙裔杀手)';
                }
                // 猎人DOT：按技能表 dot 字段挂层
                $dotKey = (string) ($sk['dot'] ?? '');
                if ($dotKey !== '') {
                    $add = $sTier >= 10 ? 2 : 1;
                    if ($sid === 'h3' && $sTier >= 10) { $add = 2; }
                    hunter_dot_bind_lv20($b, $sLv >= 20);
                    $logHeal .= hunter_dot_add($b, $dotKey, $add, $pd, $sLv >= 20, $sTier);
                }
            } elseif ($sk['kind'] === 'step') {
                // 刺客单体爆发：极高倍率打单一目标，不溅射。2觉·影刺客再叠60%。
                $pd = max(1, (int) ((player_patk($u) + random_int(0, 3)) * (float) $sk['mult'] * $pow) - monster_pdef($b));
                $ab = hunter_assassin_bonus($u);
                if ($ab > 1.0) {
                    $pd = (int) round($pd * $ab);
                    $logHeal .= '(影遁+' . (int) (($ab - 1) * 100) . '%)';
                }
                if ($skEl !== '') {
                    $pd += max(0, (int) (player_matk($u) * 0.5) - (int) (monster_mdef($b) / 2));
                    [$pd, $elTag] = apply_el_convert($u, $b, $pd, $skEl);
                    $logHeal .= $elTag;
                    $b['fullEl'] = 1;
                }
                if ($sid === 'h8' && $sTier >= 10) {
                    $pd = (int) ($pd * 1.35);
                    $logHeal .= '(必暴)';
                    $forceCrit = true;
                }
                if ($sLv >= 20) {
                    $pd = (int) ($pd * 1.15);
                }
                // 斩杀：残血目标额外加成
                $cutLine = $sLv >= 20 ? 0.6 : ($sTier >= 10 ? 0.4 : 0.3);
                if ($sTier >= 10 && (int) $b['hp'] <= (int) ((int) $b['maxhp'] * $cutLine)) {
                    $pd = (int) ($pd * 1.5);
                    $logHeal .= '(影杀斩杀)';
                }
            } elseif ($sk['kind'] === 'sin') {
                // 罪技能：罪≥门槛才能放（战斗中罪掉下去则本轮按普攻算）
                if (sin_of((int) ($u['id'] ?? 0)) < SIN_SKILL_NEED) {
                    $pd = max(1, player_patk($u) + random_int(0, 3) - monster_pdef($b));
                    $logHeal .= '(罪值不足，罪技能失效)';
                } else {
                    $sinPow = $pow * 1.3;
                    if ($sid === 'w9') {
                        // 战士·血狱狂斩：物3.6暗，吃狂战损血加成，斩杀（30%血以下+50%，t10线40%，lv20线50%）
                        $pd = max(1, (int) ((player_patk($u) + random_int(0, 3)) * (float) $sk['mult'] * $sinPow) - monster_pdef($b));
                        $pd += max(0, (int) (player_matk($u) * 0.5) - (int) (monster_mdef($b) / 2));
                        $rm9 = warr_rage_mult($b, (int) ($u['id'] ?? 0));
                        $pd = (int) round($pd * ($sTier >= 10 ? (2 * $rm9 - 1) : $rm9));
                        $rt9 = warr_rage_text($b, (int) ($u['id'] ?? 0));
                        if ($rt9 !== '') {
                            $logHeal .= $rt9;
                        }
                        $cutLine = $sLv >= 20 ? 0.5 : ($sTier >= 10 ? 0.4 : 0.3);
                        if ((int) $b['hp'] <= (int) ((int) $b['maxhp'] * $cutLine)) {
                            $pd = (int) ($pd * 1.5);
                            $logHeal .= '(血狱斩杀)';
                        }
                        if ($sTier >= 10) { $pd = (int) ($pd * 1.3); }
                        if ($sLv >= 20) { $pd = (int) ($pd * 1.15); }
                        [$pd, $elTag] = apply_el_convert($u, $b, $pd, $skEl);
                        $logHeal .= $elTag;
                        $b['fullEl'] = 1;
                    } elseif ($sid === 'm9') {
                        // 法师·魂蚀天雷：法4.2雷，35%麻痹（t10）/40%（lv20）
                        $pd = max(1, (int) ((player_matk($u) + random_int(4, 7)) * (float) $sk['mult'] * $sinPow) - (int) (monster_mdef($b) / 2));
                        $stunRate = $sLv >= 20 ? 40 : ($sTier >= 10 ? 35 : 20);
                        if (mt_rand(1, 100) <= $stunRate) {
                            $b['stun'] = 1;
                            $logHeal .= '(魂蚀麻痹)';
                        }
                        if ($sTier >= 10) { $pd = (int) ($pd * 1.3); }
                        if ($sLv >= 20) { $pd = (int) ($pd * 1.15); }
                        [$pd, $elTag] = apply_el_convert($u, $b, $pd, $skEl);
                        $logHeal .= $elTag;
                        $b['fullEl'] = 1;
                    } elseif ($sid === 'h9') {
                        // 猎人·弑神连珠：物3.0双段暗，引燃目标身上全部DOT层数
                        $pd = max(1, (int) ((player_patk($u) + random_int(0, 3)) * (float) $sk['mult'] * $sinPow) - monster_pdef($b));
                        $pd = (int) ($pd * 0.7) + (int) ($pd * 0.7);
                        $pd += max(0, (int) (player_matk($u) * 0.5) - (int) (monster_mdef($b) / 2));
                        if ($sTier >= 10) { $pd = (int) ($pd * 1.3); }
                        if ($sLv >= 20) { $pd = (int) ($pd * 1.15); }
                        [$pd, $elTag] = apply_el_convert($u, $b, $pd, $skEl);
                        $logHeal .= $elTag;
                        $b['fullEl'] = 1;
                        // 引爆：每层DOT转成额外伤害（lv20 再+50%）
                        $detMult = $sTier >= 10 ? 1.0 : 0.5;
                        if ($sLv >= 20) { $detMult *= 1.5; }
                        $det = hunter_dot_detonate($b, (int) $detMult, $sLv >= 20);
                        if ($det['dmg'] > 0) {
                            $pd += $det['dmg'];
                            $logHeal .= '(' . $det['log'] . ')';
                        } else {
                            $logHeal .= '(目标身上没有DOT可引燃)';
                        }
                    } elseif ($sid === 'p9') {
                        // 牧师·堕天圣裁：法3.0暗+奶200（转奶80%，lv20转100%）
                        $healSin = (int) ((int) $sk['heal'] * $pow) + (int) (player_matk($u) * 0.3);
                        if ($sTier >= 10) { $healSin += 30; }
                        if ($sLv >= 20) { $healSin = (int) ($healSin * 1.2); }
                        $u['hp'] = min((int) $u['maxhp'], (int) $u['hp'] + $healSin);
                        $logHeal .= '(堕天回' . $healSin . ')';
                        $pd = max(1, (int) ((player_matk($u) + random_int(4, 7)) * (float) $sk['mult'] * $sinPow) - (int) (monster_mdef($b) / 2));
                        if ($sTier >= 10) { $pd = (int) ($pd * 1.3); }
                        if ($sLv >= 20) { $pd = (int) ($pd * 1.15); }
                        [$pd, $elTag] = apply_el_convert($u, $b, $pd, $skEl);
                        $logHeal .= $elTag;
                        $b['fullEl'] = 1;
                        $leechRate = $sLv >= 20 ? 1.0 : ($sTier >= 10 ? 0.8 : 0.5);
                        $leech = (int) ($pd * $leechRate);
                        $u['hp'] = min((int) $u['maxhp'], (int) $u['hp'] + $leech);
                        $logHeal .= '(噬罪转奶' . $leech . ')';
                    } elseif ($sid === 's9') {
                        // 召唤师·深渊主宰：法3.4暗，献祭全部召唤兽按存活数加伤，全部伤害转化生命
                        $sacN = count(summon_list());
                        $pd = max(1, (int) ((player_matk($u) + random_int(4, 7)) * (float) $sk['mult'] * $sinPow) - (int) (monster_mdef($b) / 2));
                        if ($sacN > 0) { $pd = (int) ($pd * (1 + $sacN * 0.2)); $logHeal .= '(献祭' . $sacN . '只)'; }
                        if ($sTier >= 10) { $pd = (int) ($pd * 1.3); }
                        if ($sLv >= 20) { $pd = (int) ($pd * 1.15); }
                        [$pd, $elTag] = apply_el_convert($u, $b, $pd, $skEl);
                        $logHeal .= $elTag;
                        $b['fullEl'] = 1;
                        $leechS9 = $sLv >= 20 ? 1.0 : 0.5;
                        $leech9 = (int) ($pd * $leechS9);
                        $u['hp'] = min((int) $u['maxhp'], (int) ($u['hp'] ?? 0) + $leech9);
                        $logHeal .= '(主宰转命' . $leech9 . ')';
                        summon_clear();
                    }
                }
            } elseif ($sk['kind'] === 'heal') {
                // 牧师治疗量吃灵力（法攻两成加成）
                $heal = (int) ($sk['heal'] * $pow) + (int) (player_matk($u) * 0.2);
                if ($sTier >= 10) {
                    $heal += in_array($sid, ['p2', 'p3'], true) ? 25 : 15;
                }
                if ($sLv >= 20) {
                    $heal = (int) ($heal * 1.2);
                }
                if ((cflags((int) ($u['id'] ?? 0))['rb2_job'] ?? '') === 'holybishop') { $heal = (int) ($heal * 1.15); $logHeal .= '(圣光灌注)'; }
                $u['hp'] = min((int) $u['maxhp'], (int) $u['hp'] + $heal);
                $logHeal .= '(回' . $heal . ')';
                if ($sid === 'p3' && $sTier >= 10) {
                    $u['mp'] = min((int) ($u['maxmp'] ?? 0), (int) ($u['mp'] ?? 0) + 10);
                    $logHeal .= '(回蓝10)';
                }
                $pd = max(1, (int) (player_matk($u) * (float) $sk['mult'] * $pow) - (int) (monster_mdef($b) / 2));
                if ($skEl !== '') {
                    [$pd, $elTag] = apply_el_convert($u, $b, $pd, $skEl);
                    $logHeal .= $elTag;
                    $b['fullEl'] = 1;
                }
                if ($sLv >= 20) {
                    $pd = (int) ($pd * 1.15);
                }
            } elseif ($sk['kind'] === 'aoe') {
                // 群攻：打当前+溅射队列（余波震死的只给经验金币，不掉落）
                $aoePhys = in_array($sid, ['w5', 'w6', 'h5', 'h7'], true);
                if ($aoePhys) {
                    $pd = max(1, (int) ((player_patk($u) + random_int(0, 3)) * (float) $sk['mult'] * $pow) - monster_pdef($b));
                } else {
                    $pd = max(1, (int) ((player_matk($u) + random_int(4, 7)) * (float) $sk['mult'] * $pow) - (int) (monster_mdef($b) / 2));
                }
                // 战士群攻同样分两方向：w5/w7狂战吃损血，w6/w8防战吃双防
                if (!empty($sk['tank'])) {
                    $pd += warr_tank_bonus($u);
                    $logHeal .= '(铁壁+' . warr_tank_bonus($u) . ')';
                }
                if (!empty($sk['rage'])) {
                    $rm = warr_rage_mult($b, (int) ($u['id'] ?? 0));
                    $pd = (int) round($pd * ($sTier >= 10 ? (2 * $rm - 1) : $rm));
                    $rt = warr_rage_text($b, (int) ($u['id'] ?? 0));
                    if ($rt !== '') {
                        $logHeal .= $rt;
                    }
                }
                if ($skEl !== '') {
                    [$pd, $elTag] = apply_el_convert($u, $b, $pd, $skEl);
                    $logHeal .= $elTag;
                    $b['fullEl'] = 1;
                }
                if ($sTier >= 10) {
                    $pd = (int) ($pd * 1.3);
                }
                if ($sLv >= 20) {
                    $pd = (int) ($pd * 1.15);
                }
                if ($sid === 'w6' && $sTier >= 10 && mt_rand(1, 100) <= 10) {
                    $b['stun'] = 1;
                    $logHeal .= '(眩晕)';
                }
                // 游侠群攻铺 DOT（h5 万箭齐发 / h7 穿云箭雨）。h6/h8 已改为刺客单体，不再铺层。
                if (in_array($sid, ['h5', 'h7'], true)) {
                    $dotKey = (string) ($sk['dot'] ?? '');
                    if ($sid === 'h5' && $sTier >= 10) { $dotKey = 'bleed'; }
                    if ($sid === 'h7' && $sTier >= 10) { $dotKey = 'burn'; }
                    $add = $sTier >= 10 ? 2 : 1;
                    if (!empty($sk['ranger'])) {
                        $add = hunter_ranger_stacks($sid, $sTier, $sLv >= 20);
                    }
                    if ($dotKey !== '') {
                        hunter_dot_bind_lv20($b, $sLv >= 20);
                        $logHeal .= hunter_dot_add($b, $dotKey, $add, $pd, $sLv >= 20);
                    }
                }
                if ($sid === 'h5' && $sTier >= 10 && mt_rand(1, 100) <= 25) {
                    $pd = (int) ($pd * ($sLv >= 20 ? 1.8 : 1.6));
                    $logHeal .= '(箭雨暴击)';
                }
                if ($sid === 'm5' && $sTier >= 10 && mt_rand(1, 100) <= 10) {
                    $b['frost'] = 3;
                    $logHeal .= '(暴雪冻结)';
                }
                if ($sid === 'w7') {
                    $leech7 = $sTier >= 10 ? 0.15 : 0.1;
                    $hl7 = (int) ($pd * $leech7);
                    $u['hp'] = min((int) $u['maxhp'], (int) $u['hp'] + $hl7);
                    $logHeal .= '(血怒汲取+' . $hl7 . ')';
                }
                if ($sid === 'w8' && $sTier >= 10) {
                    $b['breach'] = 3;
                    $b['breachdmg'] = $sLv >= 20 ? 75 : 80;
                    $logHeal .= '(壁垒破甲)';
                }
                if ($sid === 'm7' && $sTier >= 10 && mt_rand(1, 100) <= ($sLv >= 20 ? 30 : 25)) {
                    $b['stun'] = 1;
                    $logHeal .= '(雷暴麻痹)';
                }
                if ($sid === 'h7' && $sTier >= 10 && mt_rand(1, 100) <= 30) {
                    $pd = (int) ($pd * ($sLv >= 20 ? 1.8 : 1.6));
                    $logHeal .= '(穿云暴击)';
                }
                if ($sid === 'h5' && $sLv >= 20) {
                    $b['splash'] = (int) (($b['splash'] ?? 0) + $pd * 0.5); // lv20：万箭齐发溅射翻倍
                    $logHeal .= '(箭雨漫溢)';
                }
                // 召唤师·群魂爆裂：引爆回血（爆炸伤害由 summon_bomb 另算）
                if ($sid === 's7') {
                    $leechS7 = $sLv >= 20 ? 0.4 : 0.25;
                    $hlS7 = (int) ($pd * $leechS7);
                    $u['hp'] = min((int) $u['maxhp'], (int) ($u['hp'] ?? 0) + $hlS7);
                    $logHeal .= '(群魂噬回+' . $hlS7 . ')';
                }
                // 召唤师·永夜契约：敌全体腐蚀2轮
                if ($sid === 'p7') {
                    $leechRate7 = $sLv >= 20 ? 0.6 : 0.5;
                    $leech7b = (int) ($pd * $leechRate7);
                    $u['hp'] = min((int) $u['maxhp'], (int) $u['hp'] + $leech7b);
                    $logHeal .= '(噬魂汲取+' . $leech7b . ')';
                }
                if ($sid === 'p8') {
                    $heal8 = (int) ($sk['heal'] * $pow) + (int) (player_matk($u) * 0.2);
                    if ($sTier >= 10) { $heal8 += 50; }
                    if ($sLv >= 20) { $heal8 = (int) ($heal8 * 1.2); }
                    if ((cflags((int) ($u['id'] ?? 0))['rb2_job'] ?? '') === 'holybishop') { $heal8 = (int) ($heal8 * 1.15); $logHeal .= '(圣光灌注)'; }
                    $u['hp'] = min((int) $u['maxhp'], (int) $u['hp'] + $heal8);
                    $logHeal .= '(审判回血+' . $heal8 . ')';
                    $mateRate8 = $sLv >= 20 ? 0.8 : 0.6;
                    $mparty8 = my_party((int) ($u['id'] ?? 0));
                    if ($mparty8) {
                        foreach (party_mates((int) $mparty8['id'], (int) $u['id']) as $mate) {
                            $mu = user_by_id((int) $mate['id']);
                            if ($mu && (int) $mu['hp'] > 0) {
                                $mh8 = (int) ($heal8 * $mateRate8);
                                $mu['hp'] = min((int) $mu['maxhp'], (int) $mu['hp'] + $mh8);
                                user_save($mu);
                                $logHeal .= '(队友审判回血+' . $mh8 . ')';
                            }
                        }
                    }
                }
                // 暗影主教：伤害转奶
                if ($sid === 'p5') {
                    $leechRate = $sLv >= 20 ? 0.4 : 0.3;
                    $leech = (int) ($pd * $leechRate);
                    $u['hp'] = min((int) $u['maxhp'], (int) $u['hp'] + $leech);
                    $logHeal .= '(暗影汲取+' . $leech . ')';
                }
                // 神圣主教：群奶自己+队友
                if ($sid === 'p6') {
                    $heal6 = (int) ($sk['heal'] * $pow) + (int) (player_matk($u) * 0.2);
                    if ($sTier >= 10) { $heal6 += 40; }
                    if ($sLv >= 20) { $heal6 = (int) ($heal6 * 1.2); }
                    if ((cflags((int) ($u['id'] ?? 0))['rb2_job'] ?? '') === 'holybishop') { $heal6 = (int) ($heal6 * 1.15); $logHeal .= '(圣光灌注)'; }
                    $u['hp'] = min((int) $u['maxhp'], (int) $u['hp'] + $heal6);
                    $logHeal .= '(回' . $heal6 . ')';
                    $mateRate = $sLv >= 20 ? 0.7 : 0.5;
                    $mparty = my_party((int) ($u['id'] ?? 0));
                    if ($mparty) {
                        foreach (party_mates((int) $mparty['id'], (int) $u['id']) as $mate) {
                            $mu = user_by_id((int) $mate['id']);
                            if ($mu && (int) $mu['hp'] > 0) {
                                $mh = (int) ($heal6 * $mateRate);
                                $mu['hp'] = min((int) $mu['maxhp'], (int) $mu['hp'] + $mh);
                                user_save($mu);
                                $logHeal .= '(队友' . $mate['username'] . '+' . $mh . ')';
                            }
                        }
                    }
                }
                // 溅射：50%伤害灌进队列，攒满一条血震死一只（只给经验金币）
                $splashRate = $sTier >= 10 ? 0.6 : 0.5;
                if ($sid === 'm8' && $sLv >= 20) { $b['splash'] = (int) (($b['splash'] ?? 0) + $pd * 0.1); $logHeal .= '(风火蔓延)'; }
                $b['splash'] = (int) (($b['splash'] ?? 0) + $pd * $splashRate);
                while ((int) ($b['left'] ?? 1) > 1 && (int) ($b['splash'] ?? 0) >= (int) ($b['maxhp'] ?? 1)) {
                    $b['splash'] = (int) $b['splash'] - (int) $b['maxhp'];
                    $b['left'] = (int) $b['left'] - 1;
                    $b['wexp'] = (int) ($b['wexp'] ?? 0) + exp_gain_for($u, (string) ($b['id'] ?? ''), (int) ($b['elite'] ?? 0));
                    $b['wgold'] = (int) ($b['wgold'] ?? 0) + monster_gold_reward((int) $b['gold']);
                    $logHeal .= '(余波震死一只！剩' . (int) $b['left'] . ')';
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
    // 1觉被动：法师元素亲和/猎手标记
    $rbJob = cflags((int) ($u['id'] ?? 0))['rb1_job'] ?? '';
    if ($rbJob === 'mage' && $skEl !== '') { $pd = (int)($pd * 1.12); $logHeal .= '(元素亲和)'; }
    if ($rbJob === 'hunter' && $pd > 0 && ($b['hp'] ?? 1) > 0) { $b['mark'] = 2; if (mt_rand(1, 100) <= 15) { $pd = (int)($pd * 1.1); $logHeal .= '(猎物标记)'; } }
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
    $rb2 = cflags((int) ($u['id'] ?? 0))['rb2_job'] ?? '';
    if ($rb2 === 'berserker' && (int) $u['hp'] < (int) $u['maxhp'] / 2 && $pd > 0) { $pd = (int) ($pd * 1.15); $logHeal .= '(狂怒)'; }
    // 源罪技能树·攻击侧：神之罪主(按罪值加全伤) + 之怒(残血<30%物攻翻倍)
    if ($pd > 0 && function_exists('src_sin_effects')) {
        $seAtk = src_sin_effects((int) ($u['id'] ?? 0));
        if (!empty($seAtk['lord'])) {
            $sinPct = sin_of((int) ($u['id'] ?? 0)) * 0.5;
            if ($sinPct > 0) { $pd = (int) round($pd * (1 + $sinPct / 100)); $logHeal .= '(神之罪主+' . round($sinPct, 1) . '%)'; }
        }
        if (!empty($seAtk['rage']) && (int) $u['maxhp'] > 0 && (int) $u['hp'] < (int) $u['maxhp'] * 0.3) {
            $pd = (int) $pd * 2;
            $logHeal .= '(源罪之怒)';
        }
    }
    
        if ($rb2 === 'wintermage' && in_array($skEl, ['ice', 'thunder'], true) && $pd > 0 && mt_rand(1, 100) <= 12) { $b['stun'] = 1; $logHeal .= '(麻痹)'; }
    // 怪架盾期间你的伤害被砍
    if (!empty($b['m_guard']) && $pd > 0) {
        [$pd, $guardTag] = monster_guard_cut($b, (int) $pd);
        $logHeal .= $guardTag;
    }
    // 涅槃狂暴：物攻翻倍（回合数递减），期间无法治疗
    if (!empty($b['lg_berserk']) && (int) $b['lg_berserk'] > 0 && $pd > 0) {
        $pd = (int) round($pd * 2);
        $b['lg_berserk'] = (int) $b['lg_berserk'] - 1;
    }
    // ===== 玩家伤害收尾管线：浮动 → 档位(重击/完美一击) → 暴击（最后乘）=====
    // 放在所有技能/被动倍率算完之后、扣怪血之前；暴击统一在这里判定，
    // 所以物理/法系/召唤/技能全都一致吃暴击（此前物理技能会覆盖掉普攻的暴击）。
    $lgTruePct = 0.0;
    $lgCritAdd = 0.0;
    if ($pd > 0 && function_exists('legend_relic_outgoing')) {
        [$pd, $lgCritAdd, $lgForce, $lgTruePct, $logHeal] = legend_relic_outgoing($u, $b, (int) $pd, $isSkill, $logHeal);
        if (!empty($lgForce)) { $forceCrit = true; }
    }
    if ($pd > 0) {
        $gsV = gear_stats((int) ($u['id'] ?? 0));
        $rb2cV = (string) (cflags((int) ($u['id'] ?? 0))['rb2_job'] ?? '');
        // 游侠不再加暴击（改走DOT加成），刺客靠高闪避而非暴击
        $critRate = $forceCrit ? 10000 : ($gsV['crit'] + $lgCritAdd);
        [$pd, $vTag] = dmg_pipeline($pd, $critRate, $gsV['critdmg'], (float) ($gsV['precision'] ?? 0));
        if ($vTag !== '') {
            $logHeal .= '(' . $vTag . ')';
        }
    }
    $__isCrit = isset($vTag) && strpos((string) $vTag, '暴击') !== false;
    $b['hp'] = (int) $b['hp'] - $pd;
    // 命中后：影牙/破甲真伤附加、不死鸟暴击回血
    if ($pd > 0 && function_exists('legend_relic_on_hit')) {
        legend_relic_on_hit($u, $b, (int) $pd, $__isCrit, $lgTruePct, $logHeal);
    }
    if (!empty($GLOBALS['_melee_el_tag'])) {
        // 技能已自带属性tag时不再重复追加（只保留技能那个）
        if (strpos($logHeal, '属性伤害') === false) {
            $logHeal .= (string) $GLOBALS['_melee_el_tag'];
        }
        unset($GLOBALS['_melee_el_tag']);
    }
    // 攻速·连击：速度够高时追加一击（同一目标，按主伤害50%），击杀/掉落仍由下方统一结算
    if ($pd > 0 && (int) $b['hp'] > 0) {
        $combo = speed_combo_strike($u, (int) $pd, $logHeal);
        if ($combo > 0) {
            $b['hp'] = (int) $b['hp'] - $combo;
        }
    }
    // 召唤师：召唤型技能 → 按技能招不同的兽，越高阶代价越大
    // 注意：必须“真的放出了技能”才算（$sid 只在蓝够、技能成功释放时被设置）。
    // 否则没蓝打“拳击”时也会走这里，$sid 为空 → 落到下面的 else 白嫖一只小鬼。
    if ($mode !== 'hit' && !empty($sid) && isset($sk['kind']) && $sk['kind'] === 'summon' && job_id_of($u) === 'summoner') {
        $sid2 = (string) ($sid ?? '');
        $sLv2 = (int) ($sLv ?? 0);
        $sT2 = (int) ($sTier ?? 0);
        $rb2j = cflags((int) ($u['id'] ?? 0))['rb2_job'] ?? '';
        $blockMsg = '';
        if ($sid2 === 's1') {
            if ($sLv2 >= 20) { $logHeal .= summon_add('imp', $u, 1); } else { $logHeal .= summon_add('imp', $u, 0); }
        } elseif ($sid2 === 's2') {
            if ($sT2 < 10) { $blockMsg = '（招魂引需熟练10级才能引渡守卫）'; }
            elseif ($sLv2 >= 20) { $logHeal .= summon_add('bone', $u, 1); } else { $logHeal .= summon_add('bone', $u, 0); }
        } elseif ($sid2 === 's3') {
            $cost3 = max(1, (int) floor((int) ($u['hp'] ?? 1) * 0.20));
            if ($sT2 < 10) { $blockMsg = '（血肉筑城需熟练10级）'; }
            elseif ((int) ($u['hp'] ?? 1) - $cost3 < 1) { $blockMsg = '（血不够筑城，回满再来）'; }
            else { $u['hp'] = (int) ($u['hp'] ?? 1) - $cost3; $logHeal .= '(血祭' . $cost3 . '血)'; if ($sLv2 >= 20) { $logHeal .= summon_add('abom', $u, 1); } else { $logHeal .= summon_add('abom', $u, 0); } }
        } elseif ($sid2 === 's4') {
            $nImp = $sLv2 >= 20 ? 3 : 2;
            for ($ii = 0; $ii < $nImp; $ii++) { $logHeal .= summon_add('imp', $u, $ii === 0 && $sLv2 >= 20 ? 1 : 0); }
        } elseif ($sid2 === 's5') {
            if ($rb2j !== 'necro') { $blockMsg = '（死灵之拥需2觉·死灵之拥）'; }
            else { $logHeal .= summon_add('wraith', $u, 0); $logHeal .= summon_add('wraith', $u, $sLv2 >= 20 ? 1 : 0); }
        } elseif ($sid2 === 's6') {
            $cost6 = max(1, (int) floor((int) ($u['hp'] ?? 1) * 0.30));
            if ($rb2j !== 'plague') { $blockMsg = '（噬魂夜宴需2觉·痛苦盛宴）'; }
            elseif ($sT2 < 10) { $blockMsg = '（夜宴需熟练10级）'; }
            elseif ((int) ($u['hp'] ?? 1) - $cost6 < 1) { $blockMsg = '（血不够开宴，回满再来）'; }
            else { $u['hp'] = (int) ($u['hp'] ?? 1) - $cost6; $logHeal .= '(血祭' . $cost6 . '血)'; if ($sLv2 >= 20) { $logHeal .= summon_add('mare', $u, 1); } else { $logHeal .= summon_add('mare', $u, 0); } }
        } elseif ($sid2 === 's8') {
            if ($sT2 < 10) { $blockMsg = '（永夜契约需熟练10级）'; }
            else { $logHeal .= summon_add('abom', $u, $sLv2 >= 20 ? 1 : 0); $logHeal .= summon_add('wraith', $u, 0); if ($sLv2 >= 20) { $logHeal .= summon_add('wraith', $u, 0); } }
        } elseif ($sid2 === 's9') {
            $logHeal .= '（主宰降临）';
        } else {
            $logHeal .= summon_add('imp', $u, 0);
        }
                // 噬魂夜宴：梦魇登场附带灵魂腐蚀
                if ($sid2 === 's6' && $blockMsg === '') {
                    $b['poison'] = ($sLv2 >= 20 ? 3 : 2);
                    $b['poison_pct'] = $sLv2 >= 20 ? 2 : 1.5;
                    $logHeal .= '(夜宴腐蚀)';
                }
        if ($blockMsg !== '') { $logHeal .= $blockMsg; }
    }
    // 契匣开场召唤：戴着专属副手的召唤师，每场战斗白嫖一只（不耗蓝不占回合）
    if ($mode !== 'hit' && empty($b['opened']) && job_id_of($u) === 'summoner') {
        $b['opened'] = 1;
        $openMsg = summoner_open_message($u);
        if ($openMsg !== '') {
            $logHeal .= $openMsg;
        }
    }
    // 召唤兽自动攻击
    if ($mode !== 'hit' && summon_list() !== []) {
        $st2 = summon_turn($b);
        if ($st2['dmg'] > 0 && (int) ($b['hp'] ?? 0) > 0) {
            $b['hp'] = max(0, (int) $b['hp'] - $st2['dmg']);
            $logHeal .= $st2['log'];
        }
    }
    // 血契骨戒：你20%的行动触发献祭（吞噬最低血召唤物，回50%最大生命并重新召唤）
    if (job_id_of($u) === 'summoner') {
        $bpMsg = summoner_legend_bloodpact($u, $b);
        if ($bpMsg !== '') {
            $logHeal .= $bpMsg;
        }
    }
    // 真实伤害（神魂/烬焰7件）：无视防御，闪避也躲不掉
    $trueDmg = (int) soul_truedmg((int) ($u['id'] ?? 0));
    if ($trueDmg > 0) { $b['hp'] = (int) $b['hp'] - $trueDmg; $logHeal .= '(真伤+' . $trueDmg . ')'; }
    // 猎人DOT结算：出血/剧毒/焦灼每轮掉一层，层数越多越疼（游侠2觉DOT+35%）
    if ((int) ($b['hp'] ?? 0) > 0 && hunter_dot_stacks($b) !== []) {
        $dt = hunter_dot_tick($b, hunter_dot_mult($u), hunter_dot_hard_cap((int) ($b['maxhp'] ?? 1)));
        if ($dt['dmg'] > 0) {
            $b['hp'] = (int) $b['hp'] - $dt['dmg'];
            $logHeal .= '(' . $dt['log'] . ')';
        }
    }
    // 1觉·猎手标记：目标易伤10%（剩余轮数在受伤时结算）
    if ($pd > 0 && !empty($b['mark']) && ($b['hp'] ?? 1) > 0) { $pd = (int)($pd * 1.1); $b['hp'] = (int)$b['hp'] - (int)($pd * 0.1); $b['mark'] = (int)$b['mark'] - 1; $logHeal .= '(易伤)'; }
    $verb = $skillName === '拳击' ? '你挥出一拳，造成' : ($isSkill ? '你放出' . $skillName . '，造成' : '你劈出');
    $log = $pd === 0 ? '你的攻击被' . $b['name'] . '闪避了。' : $verb . $pd . '点。' . $logHeal . $logNoMp . $profMsg . $tankMp;
    $gs = gear_stats((int) ($u['id'] ?? 0));
    if ($pd > 0 && $gs['lifesteal'] > 0) {
        $hl = max(1, (int) ($pd * $gs['lifesteal'] / 100));
        $u['hp'] = min((int) $u['maxhp'], (int) $u['hp'] + $hl);
    if ($pd > 0 && (cflags((int) ($u['id'] ?? 0))['rb2_job'] ?? '') === 'shadowbishop') {
        $sh = max(1, (int) ($pd * 0.03));
        $u['hp'] = min((int) $u['maxhp'], (int) $u['hp'] + $sh);
        $log .= '(鲜血契约+' . $sh . ')';
    }
        $log .= '(吸血+' . $hl . ')';
    }
    $petMsg = pet_attack($u, $b);
    if ($petMsg !== '') {
        $log .= $petMsg;
    }
    if (!empty($b['poison']) && (int) $b['hp'] > 0) {
        $pct = isset($b['poison_pct']) ? (float) $b['poison_pct'] : 5;
        $ptag = isset($b['poison_pct']) ? '腐蚀发作' : '剧毒发作';
        $pt = max(1, (int) ((int) ($b['maxhp'] ?? 1) * $pct / 100));
        $corCap = hunter_dot_hard_cap((int) ($b['maxhp'] ?? 1));
        if ($corCap > 0) { $pt = min($pt, $corCap); }
        $b['hp'] = (int) $b['hp'] - $pt;
        $b['poison'] = (int) $b['poison'] - 1;
        $log .= $ptag . '，' . $b['name'] . '掉' . $pt . '点生命。';
    }
    if ($skEl !== '' && empty($b['fullEl']) && (int) $b['hp'] > 0) {
        $meld = monster_element((string) ($b['id'] ?? ''));
        $chart = element_chart($skEl, $meld);
        $mres = monster_resist((string) ($b['id'] ?? ''), $skEl);
        $eldmg = max(0, (int) ((player_matk($u) * 0.3 + $gs['element']) * $chart * (1 - $mres / 100)));
        $eldmg += enchant_el_dmg((int) ($u['id'] ?? 0), $skEl);
        if ($eldmg > 0) {
            $b['hp'] = (int) $b['hp'] - $eldmg;
            $log .= element_name($skEl) . '属性伤害' . $eldmg . '点' . ($chart > 1 ? '(克制！)' : ($chart < 1 ? '(被抵抗)' : '')) . '。';
        }
    }
    if ((int) $b['hp'] <= 0) {
        if (function_exists('legend_relic_on_kill')) {
            legend_relic_on_kill($u, $b);
        }
        $killed = (int) ($b['num'] ?? 1) - (int) ($b['left'] ?? 1) + 1;
        if ((int) ($b['left'] ?? 1) > 1) {
            $b['wexp'] = (int) ($b['wexp'] ?? 0) + exp_gain_for($u, (string) ($b['id'] ?? ''), (int) ($b['elite'] ?? 0));
            $b['wgold'] = (int) ($b['wgold'] ?? 0) + monster_gold_reward((int) $b['gold']);
            $b['left'] = (int) $b['left'] - 1;
            $b['hp'] = (int) $b['maxhp'];
            hunter_dot_clear($b); // 换下一只：上一只身上的DOT层清空
            monster_skill_clear($b); // 换下一只：怪DOT与架盾一起清
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
            $ab = roll_aoe_book((int) $u['id'], (string) ($b['id'] ?? ''));
            if ($ab !== '') {
                $b['drops'][] = $ab;
                $log .= '掉落【' . $ab . '】！';
            }
            if (str_starts_with((string) ($b['id'] ?? ''), 'gy_')) {
                sin_gain_typed($u, 'gy');
                $gyMsg = gy_kill_drop((int) ($u['id'] ?? 0), (string) ($b['id'] ?? ''));
                if ($gyMsg !== '') {
                    $log .= $gyMsg;
                }
            } else {
                sin_gain_typed($u, ((int) ($b['elite'] ?? 0) === 1) ? 'elite' : 'normal');
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
        $ab = roll_aoe_book((int) $u['id'], (string) ($b['id'] ?? ''));
        if ($ab !== '') {
            $b['drops'][] = $ab;
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
        $g = (int) ($b['wgold'] ?? 0) + monster_gold_reward((int) $b['gold']);
        // 转生：金币获取+30%
        if (function_exists('rs_gold_mult')) {
            $g = (int) round($g * rs_gold_mult((int) ($u['id'] ?? 0)));
        }
        $u['gold'] = (int) $u['gold'] + $g;
        $rawExp = (int) (((int) ($b['wexp'] ?? 0) + exp_gain_for($u, (string) ($b['id'] ?? ''), (int) ($b['elite'] ?? 0))) * guild_exp_mult((int) $u['id']) * party_bonus_mult((int) $u['id']));
        war_add_score((int) $u['id'], (string) ($b['id'] ?? ''));
        $msg = ($msg ?? '') . gain_exp($u, $rawExp);
        if (str_starts_with((string) ($b['id'] ?? ''), 'gy_')) {
            sin_gain_typed($u, 'gy');
            $gyMsg = gy_kill_drop((int) ($u['id'] ?? 0), (string) ($b['id'] ?? ''));
            if ($gyMsg !== '') {
                $msg .= $gyMsg;
            }
        }
        if (str_starts_with((string) ($b['id'] ?? ''), 'god_') || str_starts_with((string) ($b['id'] ?? ''), 'gf_')) {
            $godMsg = god_kill_drop((int) ($u['id'] ?? 0), (string) ($b['id'] ?? ''));
            if ($godMsg !== '') {
                $msg .= $godMsg;
            }
        }
        $duskMsg = dusk_kill_drop((int) ($u['id'] ?? 0), (string) ($b['id'] ?? ''));
        if ($duskMsg !== '') {
            $msg .= $duskMsg;
        }
        $shixingMsg = gf_dot_set_drop((int) ($u['id'] ?? 0), (string) ($b['id'] ?? ''));
        if ($shixingMsg !== '') {
            $msg .= $shixingMsg;
        }        $yzMsg = yz_kill_drop((int) ($u['id'] ?? 0), (string) ($b['id'] ?? ''));
        if ($yzMsg !== '') {
            $msg .= $yzMsg;
        }        $spMsg = soulpact_kill_drop((int) ($u['id'] ?? 0), (string) ($b['id'] ?? ''));
        if ($spMsg !== '') {
            $msg .= $spMsg;
        } elseif (is_boss_mid((string) ($b['id'] ?? ''))) {
            sin_gain_typed($u, 'boss');
        } else {
            sin_gain_typed($u, ((int) ($b['elite'] ?? 0) === 1) ? 'elite' : 'normal');
        }
        $petLvMsg = pet_gain_exp((int) $u['id'], (int) ($b['wexp'] ?? 0) + exp_gain_for($u, (string) ($b['id'] ?? '')));
        $qmap = ['goblin' => 1, 'banshee' => 2, 'ogre' => 3];
        $bid = (string) ($b['id'] ?? '');
        if (isset($qmap[$bid]) && (int) ($u['quest'] ?? 0) < $qmap[$bid]) {
            $u['quest'] = $qmap[$bid];
            if ($bid === 'goblin') {
                $msg .= '。玛莎给你燕麦肉汤，伤全好了';
                $u['hp'] = (int) $u['maxhp'];
                add_mat((int) $u['id'], 'hp_pct10', 3);
                $msg .= '，得雾纹绷带×1';
            }
            if ($bid === 'banshee') {
                $msg .= '。女妖散去：“别往下听……”莉娜加入';
            }
            if ($bid === 'ogre') {
                $msg .= '。咕噜让路，送你本命武器+黑币！桥通了，去白石镇吧';
                $bb = make_equip((int) $u['id'], 'weapon', (['warrior' => '大骨棒', 'mage' => '雾纹法杖', 'hunter' => '断弦短弓', 'priest' => '燕麦木槌', 'summoner' => '深渊引魂灯'][job_id_of($u)] ?? '大骨棒'), 1);
                $msg .= '。【' . $bb . '】已放进背包，去穿上吧';
                // 召唤师：本命装备是副手契匣（带开场自动召唤），主手那盏引魂灯不带召唤能力，
                // 所以这里额外发一只最破的契匣并直接戴上，保证召唤师从新手期就能用上召唤。
                if (job_id_of($u) === 'summoner') {
                    $gnBox = make_equip((int) $u['id'], 'offhand', '残魂灯匣', 0, max(1, (int) $u['lv']));
                    if ($gnBox !== '') {
                        $boxId = summoner_wear_box((int) $u['id']);
                        if ($boxId > 0) {
                            $msg .= '。【' . $gnBox . '】已戴上，战斗一开始匣门就会自己打开。';
                        }
                    }
                }
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
        // 召唤师死灵传说掉落（现有终局BOSS）
        $lgMsg = summoner_legend_kill_drop((int) $u['id'], (string) ($b['id'] ?? ''));
        if ($lgMsg !== '') {
            $msg .= '。' . $lgMsg;
        }
        // 遗神铸庭·传说遗物掉落
        $lrMsg = legend_relic_kill_drop((int) $u['id'], (string) ($b['id'] ?? ''));
        if ($lrMsg !== '') {
            $msg .= '。' . $lrMsg;
        }
        // 残鳞：战斗结束恢复原始最大生命
        if (job_id_of($u) === 'summoner') {
            summoner_legend_rescale_clear($u);
        }
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
        if ($trashMids !== [] && mt_rand(1, 100) <= 1) {
            $em = $trashMids[array_rand($trashMids)];
            spawn_add_elite((int) $u['id'], (string) ($u['loc'] ?? ''), $em);
            $msg .= '。一只' . monsters()[$em]['name'] . '（精英）出现了！';
        }
        $cur = (int) ($u['quest'] ?? 0);
        if ($cur >= 10 && $cur <= 15) {
            $qs = ch1_quests()[$cur];
            if (check_ch1_done((int) $u['id'], $qs)) {
                $u['quest'] = $cur + 1;
                $txt .= quest_potion_gift((int) $u['id'], 1);
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
                    // 召唤师：咕噜那关已经送过最破的契匣了，这里改成送高一档的（引魂旧匣），
                    // 免得发两只一模一样的。玩家等级不够就跳过。
                    if (job_id_of($u) === 'summoner') {
                        $boxBase = '引魂旧匣';
                        $boxLv = (int) (summoner_offhands()[$boxBase]['lv'] ?? 999);
                        if ((int) $u['lv'] >= $boxLv) {
                            $gnBox = make_equip((int) $u['id'], 'offhand', $boxBase, 1, max(1, (int) $u['lv']));
                            if ($gnBox !== '' && summoner_wear_box((int) $u['id']) > 0) {
                                $txt .= '。你把【' . $gnBox . '】扣在腰上，匣门比上一只更沉了。';
                            }
                        }
                    }
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
                $txt .= quest_potion_gift((int) $u['id'], 2);
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
                $txt .= quest_potion_gift((int) $u['id'], 3);
                $u['gold'] = (int) $u['gold'] + [30 => 2000, 31 => 3000, 32 => 4000, 33 => 5000, 34 => 6000, 35 => 7000, 36 => 8000, 37 => 10000][$cur];
                $gear3 = [31 => ['weapon', '傀儡短剑', 150], 32 => ['body', '傀儡皮甲', 180], 33 => ['weapon', '傀儡长弓', 210], 34 => ['body', '傀儡铠甲', 240], 35 => ['back', '傀儡斗篷', 270]][$cur] ?? null;
                // 召唤师：第三章起按等级发一只契匣（过渡副手，带开场召唤）
                if (job_id_of($u) === 'summoner' && in_array($cur, [31, 35, 37], true)) {
                    $boxName = summoner_offhand_for_lv(max(1, (int) $u['lv']));
                    if ($boxName !== null) {
                        $gear3 = ['offhand', $boxName, 3, max(1, (int) $u['lv'])];
                    }
                }
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
                    add_mat((int) $u['id'], 'qingzui_dan', 1);
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
        if ($cur >= 100 && $cur <= 117) {
            $qsg = god_quests()[$cur] + ['id' => $cur];
            if (check_ch3_done((int) $u['id'], $qsg)) {
                $u['quest'] = $cur + 1;
                $u['gold'] = (int) $u['gold'] + [100 => 200000, 101 => 220000, 102 => 260000, 103 => 300000, 104 => 350000, 105 => 400000, 106 => 450000, 107 => 500000, 108 => 600000, 109 => 700000, 110 => 800000, 111 => 1000000, 112 => 1200000, 113 => 1400000, 114 => 1600000, 115 => 1800000, 116 => 2000000, 117 => 3000000][$cur];
                if ($cur === 100) {
                    add_mat((int) $u['id'], 'god_badge', 1);
                    $txt .= '。【神界勋章】入手，伽楼说：往上去，别回头。';
                } elseif ($cur === 112) {
                    $u2a = user_by_id((int) $u['id']);
                    $uu2 = is_array($u2a) ? $u2a : $u;
                    cflag_set((int) $u['id'], 'gf_free', 1);
                    user_save($uu2);
                    $txt .= '。神座化身消散了。堕天传令·赛丽亚落地行礼：下面请走堕天阶，归路已为你赦免（进堕天阶不再收票）。';
                } elseif ($cur === 117) {
                    add_mat((int) $u['id'], 'god_proof', 1);
                    give_title((int) $u['id'], 'title_god');
                    $gw = ['warrior' => '神铸·雷霆', 'mage' => '神铸·裁决', 'hunter' => '神铸·苍穹', 'priest' => '神铸·铁律', 'summoner' => '神铸·幽契'][job_id_of($u)] ?? '神铸·天罚';
                    $gn = make_equip((int) $u['id'], 'weapon', $gw, 4, max(1, (int) $u['lv']), ['extra' => ['k' => 'skill_damage', 'v' => 10.0]]);
                    $txt .= '。第八章·神界完成！堕天神帝陨落，你夺得【称号·神界主宰】' . ($gn !== '' ? '与【' . $gn . '】' : '') . '！';
                } else {
                    $txt .= '。本轮完成，进下一环！';
                }
                quest_god_baseline((int) $u['id'], $cur + 1);
                user_save($u);
            } else {
                $txt .= '。【' . $qsg['name'] . '】' . quest_progress3_text((int) $u['id'], $qsg);
            }
        }
        if ($cur >= 50 && $cur <= 61) {
            $qs4 = ch4_quests()[$cur] + ['id' => $cur];
            if (check_ch3_done((int) $u['id'], $qs4)) {
                $u['quest'] = $cur + 1;
                $txt .= quest_potion_gift((int) $u['id'], 5);
                $u['gold'] = (int) $u['gold'] + [50 => 50000, 51 => 60000, 52 => 70000, 53 => 80000, 54 => 90000, 55 => 100000, 56 => 120000, 57 => 140000, 58 => 160000, 59 => 180000, 60 => 220000, 61 => 300000][$cur];
                if ($cur === 55) { $gear4 = ['warrior' => ['weapon', '寒霜·战斧', 2, 380], 'mage' => ['offhand', '寒霜·法杖', 2, 380], 'hunter' => ['weapon', '寒霜·长弓', 2, 380], 'priest' => ['weapon', '寒霜·战锤', 2, 380], 'summoner' => ['weapon', '寒霜·魂灯', 2, 380]][job_id_of($u)] ?? null; } elseif ($cur === 59) { $gear4 = ['warrior' => ['offhand', '寒霜·冰盾', 3, 460], 'mage' => ['offhand', '寒霜·法杖', 3, 460], 'hunter' => ['body', '寒霜·骑士铠', 3, 460], 'priest' => ['offhand', '寒霜·冰盾', 3, 460], 'summoner' => ['offhand', '寒霜·契匣', 3, 460]][job_id_of($u)] ?? null; } else { $gear4 = [52 => ['body', '雪原皮甲', 2, 370], 54 => ['legs', '寒霜·胫甲', 2, 410], 57 => ['head', '霜母之冠', 2, 450], 58 => ['body', '冰河重铠', 3, 465]][$cur] ?? null; }
                if ($gear4 !== null) {
                    $gn4 = make_equip((int) $u['id'], $gear4[0], $gear4[1], $gear4[2], $gear4[3]);
                    if ($gn4 !== '') {
                        $txt .= '。获得【' . $gn4 . '】！';
                    }
                }
                if ($cur === 55) {
                    add_mat((int) $u['id'], 'fr_ticket', 1);
                    $txt .= '。老咸鱼塞给你【冰渊副本入场券】！';
                }
                if ($cur === 59) {
                    add_mat((int) $u['id'], 'enhance_t2', 2);
                    $txt .= '。获得中级强化石×2！';
                }
                if ($cur === 61) {
                    add_mat((int) $u['id'], 'ice_badge', 1);
                    add_mat((int) $u['id'], 'frost_heart', 1);
                    add_mat((int) $u['id'], 'ch4_proof', 1);
                    add_mat((int) $u['id'], 'qingzui_dan', 1);
                    add_mat((int) $u['id'], 'frost_egg', 1);
                    if (job_id_of($u) === 'mage') { $gn4b = make_equip((int) $u['id'], 'offhand', '寒霜·法杖', 3, 480); } elseif (job_id_of($u) === 'hunter') { $gn4b = make_equip((int) $u['id'], 'weapon', '寒霜·长弓', 3, 480); } elseif (job_id_of($u) === 'priest') { $gn4b = make_equip((int) $u['id'], 'weapon', '寒霜·战锤', 3, 480); } elseif (job_id_of($u) === 'summoner') { $gn4b = make_equip((int) $u['id'], 'weapon', '寒霜·魂灯', 3, 480); } else { $gn4b = make_equip((int) $u['id'], 'weapon', '寒霜·战斧', 3, 480); }
                    if ($gn4b !== '') {
                        $txt .= '。获得【' . $gn4b . '】！';
                    }
                    $txt .= '。第四章·凛冬冰原完成！获得【冰原徽章】【霜王之心】，霜狼幼崽蛋已发放，去雾森渡口找森语者！';
                } else {
                    $txt .= '。本环完成，进下一环！';
                }
                quest3_baseline((int) $u['id'], $cur + 1);
                user_save($u);
            } else {
                $txt .= '。【' . $qs4['name'] . '】' . quest_progress3_text((int) $u['id'], $qs4);
            }
        }
        if ($cur >= 62 && $cur <= 75) {
            $qs5 = ch5_quests()[$cur] + ['id' => $cur];
            if (check_ch3_done((int) $u['id'], $qs5)) {
                $u['quest'] = $cur + 1;
                $txt .= quest_potion_gift((int) $u['id'], 6);
                $u['gold'] = (int) $u['gold'] + [62 => 300000, 63 => 320000, 64 => 350000, 65 => 380000, 66 => 400000, 67 => 430000, 68 => 460000, 69 => 500000, 70 => 550000, 71 => 600000, 72 => 650000, 73 => 700000, 74 => 800000, 75 => 1000000][$cur];
                if ($cur === 67) { $gear5 = ['warrior' => ['legs', '烬焰·熔渣腿', 2, 575], 'mage' => ['offhand', '腐叶法器', 2, 575], 'hunter' => ['weapon', '烬焰·焚心弓', 2, 575], 'priest' => ['offhand', '腐叶法器', 2, 575], 'summoner' => ['offhand', '裹魂契匣', 2, 575]][job_id_of($u)] ?? null; } elseif ($cur === 71) { $gear5 = ['warrior' => ['offhand', '烬焰·熔盾', 3, 635], 'mage' => ['necklace', '烬焰·余火链', 3, 635], 'hunter' => ['offhand', '烬焰·余烬囊', 3, 635], 'priest' => ['offhand', '烬焰·熔盾', 3, 635], 'summoner' => ['offhand', '烬焰·契匣', 3, 635]][job_id_of($u)] ?? null; } elseif ($cur === 73) { $gear5 = ['warrior' => ['weapon', '烬焰·龙裔刃', 3, 665], 'mage' => ['weapon', '烬焰·灰烬法杖', 3, 665], 'hunter' => ['weapon', '烬焰·焚心弓', 3, 665], 'priest' => ['weapon', '烬焰·熔核锤', 3, 665], 'summoner' => ['weapon', '烬焰·契杖', 3, 665]][job_id_of($u)] ?? null; } else { $gear5 = [64 => ['offhand', '烬焰·余烬囊', 2, 530], 74 => ['legs', '烬焰·熔渣腿', 3, 680]][$cur] ?? null; }
                if ($gear5 !== null) {
                    $gn5 = make_equip((int) $u['id'], $gear5[0], $gear5[1], $gear5[2], $gear5[3]);
                    if ($gn5 !== '') {
                        $txt .= '。获得【' . $gn5 . '】！';
                    }
                }
                if ($cur === 69) {
                    add_mat((int) $u['id'], 'em_ticket', 1);
                    $txt .= '。老灰塞给你【熔心副本入场券】！';
                }
                if ($cur === 74) {
                    add_mat((int) $u['id'], 'enhance_t3', 2);
                    $txt .= '。获得高级强化石×2！';
                }
                if ($cur === 75) {
                    add_mat((int) $u['id'], 'forest_badge', 1);
                    add_mat((int) $u['id'], 'south_flame_token', 1);
                    add_mat((int) $u['id'], 'ch5_proof', 1);
                    add_mat((int) $u['id'], 'qingzui_dan', 1);
                    add_mat((int) $u['id'], 'soul_shard', 10);
                    $sk4 = ['warrior' => 'w4', 'mage' => 'm4', 'hunter' => 'h4', 'priest' => 'p4', 'summoner' => 's4'][job_id_of($u)] ?? '';
                    if ($sk4 !== '') {
                        add_mat((int) $u['id'], 'book_' . $sk4, 1);
                    }
                    $eset = ['warrior' => ['body', '烬焰·守卫铠', 660], 'mage' => ['necklace', '烬焰·余火链', 650], 'hunter' => ['ring', '烬焰·灰烬戒', 700], 'priest' => ['head', '烬焰·祭司冠', 680], 'summoner' => ['head', '烬焰·契者兜帽', 680]][job_id_of($u)] ?? ['body', '烬焰·守卫铠', 660];
                    $gn5b = make_equip((int) $u['id'], $eset[0], $eset[1], 3, $eset[2]);
                    if ($gn5b !== '') {
                        $txt .= '。获得【' . $gn5b . '】！';
                    }
                    $txt .= '。第五章·雾森火山完成！巨龙没有醒，你活下来了。获得【南炎信物】+四阶技能书+烬焰套装1件+神魂碎片×10！六圣柱的龙王不要门票，够等级就能进去车轮战。';
                } else {
                    $txt .= '。本环完成，进下一环！';
                }
                quest3_baseline((int) $u['id'], $cur + 1);
                user_save($u);
            } else {
                $txt .= '。【' . $qs5['name'] . '】' . quest_progress3_text((int) $u['id'], $qs5);
            }
        }
        if ($cur >= 77 && $cur <= 80) {
            $rrf2 = cflags((int) $u['id']);
            if ($cur === 77 && !empty($rrf2['rb2_start'])) { $u['quest'] = 78; user_save($u); quest3_baseline((int) $u['id'], 78); $txt .= '。瓦尔顿为你打开虚空试炼，去火山之心杀南炎守卫×40、遗迹守卫！'; }
            elseif ($cur === 78) {
                $qs78 = rebirth2_quests()[78] + ['id' => 78];
                if (check_ch3_done((int) $u['id'], $qs78)) { $u['quest'] = 79; user_save($u); $txt .= '。试炼完成！回中央大道找瓦尔顿，从两条路里选一条！'; }
                else { $txt .= '。【' . $qs78['name'] . '】' . quest_progress3_text((int) $u['id'], $qs78); }
            }
            elseif ($cur === 79 && !empty($rrf2['rb2_job'])) { $u['quest'] = 80; user_save($u); $txt .= '。路已经选了，去加冕吧！'; }
            elseif ($cur === 80 && !empty($rrf2['rb2_done'])) {
                $u['quest'] = 81; $u['gold'] = (int) $u['gold'] + 1000000; user_save($u);
                add_mat((int) $u['id'], 'reset_potion', 1);
                add_mat((int) $u['id'], 'soul_shard', 20);
                add_mat((int) $u['id'], 'rb2_proof', 1);
                $txt .= '。2觉·群攻之路完成！获得100万+洗点药+神魂碎片×20+2觉证明！去虚空裂隙/灰烬深渊刷群攻秘籍！';
            }
            else { $txt .= '。【' . rebirth2_quests()[$cur]['name'] . '】找瓦尔顿继续。'; }
        }
        if ($cur >= 40 && $cur <= 41) {
            $rrf = cflags((int) $u['id']);
            if ($cur === 40 && !empty($rrf['rb1_start'])) { $u['quest'] = 41; user_save($u); $txt .= '。瓦尔顿为你打开四重试炼，去见四位导师！'; }
            elseif ($cur === 41 && !empty($rrf['rb1_war']) && !empty($rrf['rb1_mag']) && !empty($rrf['rb1_hun']) && !empty($rrf['rb1_pri'])) { $u['quest'] = 45; user_save($u); $txt .= '。四重试炼完成！回中央大道找瓦尔顿抉择！'; }
            else { $txt .= '。【' . rebirth1_quests()[$cur]['name'] . '】试炼进度（战法猎牧）：' . (empty($rrf['rb1_war']) ? 0 : 1) . (empty($rrf['rb1_mag']) ? 0 : 1) . (empty($rrf['rb1_hun']) ? 0 : 1) . (empty($rrf['rb1_pri']) ? 0 : 1); }
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
    $monTags = [];
    for ($i = 0; $i < $alive; $i++) {
        [$oneD, $oneTag] = dmg_to_player($u, $b);
        $md += (int) $oneD;
        if ($oneTag !== '' && $oneTag !== '闪避') {
            $monTags[] = $b['name'] . $oneTag;
        }
    }
    // 夜幕：本回合发生过闪避 → 下次出手转真伤
    if (!empty($GLOBALS['_lg_dodged'])) {
        unset($GLOBALS['_lg_dodged']);
        if (isset(legend_relic_worn((int) ($u['id'] ?? 0))['lg_nightfall'])) {
            $b['lg_nf_ready'] = 1;
        }
    }
    if ($monTags !== []) {
        $log .= implode('', $monTags);
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
    // 怪物出手：先结算你身上的怪DOT，再看放普攻还是专属技能，战报里写清楚
    $skRes = monster_skill_turn($u, $b, (int) $md);
    $md = (int) $skRes['md'];
    $log .= $skRes['log'];
    // 怪放了专属技能后，旧的本系属性冲击不再重复追加（技能里已结算过抗性）
    $meldSkipShock = (string) ($skRes['skill'] ?? '') !== '';
    if (!empty($b['stun'])) {
        $md = 0;
        $b['stun'] = max(0, (int) $b['stun'] - 1);
        $log .= $b['name'] . '被眩晕，动弹不得。';
    }
    if (!empty($b['frost'])) {
        $md = (int) ($md * 0.85);
        $b['frost'] = (int) $b['frost'] - 1;
        $log .= $b['name'] . '被冻结，手脚迟缓。';
    }
    if (!empty($b['breach'])) {
        $md = (int) ($md * (int) ($b['breachdmg'] ?? 80) / 100);
        $b['breach'] = (int) $b['breach'] - 1;
        $log .= $b['name'] . '被破甲，攻击受挫。';
    }
    $meld = monster_element((string) ($b['id'] ?? ''));
    if (!$meldSkipShock && $meld !== '' && $md > 0) {
        $eres = player_resist($u, $meld);
        $emd = max(0, (int) ((int) ($b['atk'] ?? 0) * 0.3 * (1 - $eres / 100)));
        if ($emd > 0) {
            $md += $emd;
            $log .= $b['name'] . '的' . element_name($meld) . '属性冲击' . $emd . '点。';
        }
    }
    if (($b['id'] ?? '') === 'valentin') {
        $cf = cflags((int) ($u['id'] ?? 0));
        if (!empty($cf['lord_alert'])) {
            $md = (int) ($md * 1.1);
        }
    if ((cflags((int) ($u['id'] ?? 0))['rb2_job'] ?? '') === 'aegis') { $md = (int) ($md * 0.92); }
    }
    // 契匣【幽冥·招魂匣】：召唤兽在场时它替你挡刀，你减伤
    if (job_id_of($u) === 'summoner' && summon_list() !== []) {
        $ohGuard = min(60.0, summoner_offhand_eff((int) ($u['id'] ?? 0))['guard']);
        if ($ohGuard > 0) {
            $md = (int) ($md * (1 - $ohGuard / 100));
            $log .= '(匣庇护-' . (int) $ohGuard . '%)';
        }
    }
    // 召唤兽优先承伤（战报直接进 $log：$logHeal 在上半场已并入过，这半场的消息必须另接）
    $hadSummon = summon_list() !== [];
    [$md, $slog] = summon_absorb($md, $b);
    if ($slog !== '') {
        $log .= $slog;
    }
    // 遗神铸庭·彼岸：任意召唤物阵亡 → 玩家当场死亡
    if (!empty($GLOBALS['_lg_osh_die'])) {
        unset($GLOBALS['_lg_osh_die']);
        $u['hp'] = 0;
        $log .= '【彼岸】召唤物阵亡，你随之坠亡！';
    }
    // 遗神铸庭·受击减伤/增伤（叹息之墙/磐石/死斗者）
    if ($md > 0 && function_exists('legend_relic_incoming')) {
        $md = legend_relic_incoming($u, $b, $md);
    }
    // 源罪技能树·源罪之守：残血<30% 受到伤害-30%
    if ($md > 0 && function_exists('src_sin_effects') && (int) ($u['maxhp'] ?? 0) > 0 && (int) $u['hp'] < (int) $u['maxhp'] * 0.3) {
        $seGuard = src_sin_effects((int) ($u['id'] ?? 0));
        if (!empty($seGuard['guard'])) {
            $md = max(1, (int) round($md * 0.7));
            $log .= '(源罪之守-30%)';
        }
    }
    $u['hp'] = (int) $u['hp'] - $md;
    // 泰坦之肤：受击叠层（本次反击即可吃到）
    if ($md > 0) {
        $__w = legend_relic_worn((int) ($u['id'] ?? 0));
        if (isset($__w['lg_taitan'])) {
            $b['lg_taitan'] = min(10, (int) ($b['lg_taitan'] ?? 0) + 1);
        }
    }
    if ($md > 0) {
        // 写清这一下到底打在你身上还是召唤物身上
        if ($hadSummon) {
            $log .= '剩下' . $md . '点打在你身上（你' . max(0, (int) $u['hp']) . '/' . (int) $u['maxhp'] . '）。';
        } else {
            $log .= $b['name'] . '打在你身上' . $md . '点（你' . max(0, (int) $u['hp']) . '/' . (int) $u['maxhp'] . '）。';
        }
    } elseif ($hadSummon) {
        $log .= '召唤物替你挡下了全部伤害。';
    }
    // 战士·狂战/防战：累计本场损血作为狂战燃料（数值，非百分比），并按防御触发荆棘反击
    if ($md > 0) {
        warr_add_hp_lost($b, $md);
        if (job_id_of($u) === 'warrior') {
            // 铁壁反击是防战被动：只要你是战士，挨打就按（物防+法防）等比例反打。
            // 把防战技能（tank）设为当前技能可再放大反击，狂战技能(rage)则不放大。
            $thMult = 1.0;
            $actSk = active_skill((int) ($u['id'] ?? 0), 'warrior');
            if (!empty($actSk['tank'])) {
                $thMult = ((int) ($actSk['tier'] ?? 0) >= 10 ? 1.3 : 1.0) * ((int) ($actSk['level'] ?? 0) >= 20 ? 1.3 : 1.0);
            }
            $thorns = warr_thorns($u, $thMult);
            if (function_exists('legend_relic_counter')) {
                $__cl = '';
                $thorns = legend_relic_counter($u, $b, (int) $thorns, $__cl);
                if ($__cl !== '') {
                    $log .= $__cl;
                }
            }
            if ($thorns > 0) {
                $tDmg = max(1, $thorns - monster_pdef($b));
                $tDmg = max(1, $tDmg - (int) (monster_mdef($b) / 2));
                [$tDmg, $tTag] = player_dmg_pipeline($u, $tDmg);
                // 反击不负责补最后一刀：击杀与掉落统一由本回合玩家出手结算，
                // 所以这里留至少1血，避免出现"0血怪还活着"的时序问题。
                $tDmg = max(0, min($tDmg, (int) $b['hp'] - 1));
                if ($tDmg > 0) {
                    $b['hp'] = (int) $b['hp'] - $tDmg;
                    // 战报主文本 $log 在本阶段已存在，直接追加（$logHeal 已在上半场并入 $log）
                    $log .= '(铁壁反击' . $tDmg . '点' . ($tTag !== '' ? $tTag : '') . ')';
                    warr_add_hp_lost($b, $tDmg);
                    if (function_exists('legend_relic_counter_after')) {
                        $__ca = '';
                        legend_relic_counter_after($u, $b, (int) $tDmg, $__ca);
                        if ($__ca !== '') {
                            $log .= $__ca;
                        }
                    }
                }
            }
            $rageTxt = warr_rage_text($b, (int) ($u['id'] ?? 0));
            if ($rageTxt !== '') {
                $log .= $rageTxt;
            }
        }
    }
    // 1觉被动（召唤师·魂契）：受击10%概率召唤深渊小鬼
    if ($md > 0) {
        $sp = summon_soulpact_trigger($u);
        if ($sp !== '') {
            $log .= $sp;
        }
    }
    // 1觉被动（牧师）：5%减伤40%，每轮回3%
    $rbJob2 = cflags((int) ($u['id'] ?? 0))['rb1_job'] ?? '';
    if ($rbJob2 === 'priest' && $md > 0) {
        if (mt_rand(1, 100) <= 5) { $u['hp'] = (int)$u['hp'] + (int)($md * 0.4); $md = (int)($md * 0.6); $log .= '(圣光庇护)'; }
        $rh = min((int)$u['maxhp'] - (int)$u['hp'], (int)((int)$u['maxhp'] * 0.03));
        if ($rh > 0) { $u['hp'] = (int)$u['hp'] + $rh; $log .= '(圣光+' . $rh . ')'; }
    }
    $tankHp = tank_auto($u, 'hp');
    $log .= $tankHp;
    // 怪物随机打其一：有宠时50%宠全吃（人不掉血），50%人全吃（宠无伤）
    if (active_pet((int) ($u['id'] ?? 0)) && mt_rand(1, 100) <= 50) {
        $petHurt = pet_hurt((int) ($u['id'] ?? 0), $md * 2);
        if ($petHurt !== '') { $log .= $petHurt; }
        $u['hp'] = (int) $u['hp'] + $md;
        $log .= '(怪物转头打了宠物)';
    }
    // 遗神铸庭·涅槃：致命伤害拦截（保命+狂暴）
    if ((int) $u['hp'] <= 0 && function_exists('legend_relic_death_guard')) {
        $__ng = '';
        if (legend_relic_death_guard($u, $b, $__ng)) {
            $log .= $__ng;
        }
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
    // 召唤物存在 $_SESSION['battle']['summons']（summon_save 直接写 session）。
    // $b 是本轮的局部副本，不含本轮新召唤/受击后的召唤物；若直接写回会把它们冲掉，
    // 导致「实时战斗画面」永远看不到召唤物血量。这里先取回本轮最新召唤列表再落 session。
    if (function_exists('summon_list')) {
        $b['summons'] = summon_list();
    }
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
    if ($script === 'fight.php' && in_array($a, ['tick', 'start', 'hit', 'skill', 'drink'], true)) {
        return;
    }
    $b = $_SESSION['battle'];
    $now = time();
    // 前台3秒一轮，后台按同样节奏追（原来按秒追，切出去攒一大坨战报）
    $rounds = min(100, intdiv(max(0, $now - (int) ($b['last'] ?? $now)), 3));
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

// 传送费用：严格按距离（最短步数）收费，且越远单价越高（距离平方级）。
// 近处几步几十铜，横穿整个世界(约30+步)要 1~2 金，让"路远=贵"真正体现出来。
// 公式：20铜 × 步数 × (步数+1)。1步=40铜，5步=6银，10步=22银，20步=84银，34步≈2.38金。
// $playerLv>0 时按玩家等级打折：低等级玩家传送更便宜（≤30级 6折、≤60级 8折、更高原价），
// 缓解"低等级地图传送费用太高"。负方不变（帮助新手跨越早期地图）。
function teleport_cost(string $from, string $to, int $playerLv = 0): ?int
{
    if ($from === $to) {
        return 0;
    }
    $path = find_route($from, $to);
    if ($path === []) {
        return null;
    }
    $steps = count($path) - 1;
    $cost = 20 * $steps * ($steps + 1);
    if ($playerLv > 0) {
        // 低等级折扣：随等级线性从 5 折抬到原价（约 120 级起原价）
        $ratio = min(1.0, max(0.5, $playerLv / 120));
        $cost = (int) ceil($cost * $ratio);
    }
    return $cost;
}

function teleport_region_order(): array
{
    $order = [];
    foreach (map_regions() as $region => $ids) {
        if ($region === '公会战场') {
            continue;
        }
        $min = PHP_INT_MAX;
        foreach ($ids as $id) {
            $min = min($min, teleport_min_lv((string) $id));
        }
        $order[$region] = $min === PHP_INT_MAX ? 1 : $min;
    }
    asort($order);
    return $order;
}

function map_regions(): array
{
    return [
        '灰雾村' => ['smithy', 'square', 'field', 'well', 'bridge'],
        '白石镇' => ['town_sq', 'street_e', 'smith', 'tavern', 'street_w', 'church', 'market', 'supply', 'mansion', 'sewer', 'guild', 'wall', 'gate', 'mine_gate', 'mine1', 'mine2', 'mine3', 'echo_room'],
        '矿坑·灰烬中的低语' => ['mine_gate', 'mine1', 'mine2', 'mine3', 'echo_room'],
        '深渊旧道' => ['tunnel', 'shoal', 'altar', 'prison', 'shop', 'camp'],
        '白银城·影蚀之潮' => ['silver_gate', 'silver_sq', 'sguild', 'dsewer1', 'dsewer2', 'bmine1', 'bmine2', 'sforest', 'manor', 'baltar', 'rift_road', 'rift', 'buf12a', 'buf12b', 'buf23a', 'buf23b'],
        '王都·傀儡之夜' => ['capital_gate', 'avenue', 'noble', 'library', 'ctomb', 'slum', 'cathedral', 'observatory', 'theater'],
        '神界' => ['god_gate', 'god_hall', 'god_pilgrim', 'god_library', 'god_sea', 'god_garden', 'god_orchard', 'god_court', 'god_mirror', 'god_forge', 'god_pillar', 'god_sleeper', 'god_serpent', 'god_throne'],
        '神界·堕天阶' => ['gf_gate', 'gf_stair', 'gf_void', 'gf_wing', 'gf_core'],
        '源罪渊' => ['yz_gate', 'yz_core'],
        '银丝母巢' => ['mx_gate', 'mx_path', 'mx_hall', 'mx_depth', 'mx_nest', 'mx_heart'],
        '公会战场' => ['warfield'],
        '公会战场' => ['warfield'],
        '巅峰战场' => ['peak1', 'peak2', 'peak3'],
        '冰原·凛冬' => ['ice_gate', 'ice_trail', 'ice_village', 'frozen_lake', 'snow_field', 'glacier_pass', 'aurora_vale', 'ice_cave', 'wyrm_tunnel', 'blizzard_peak', 'glacier_heart', 'frost_throne', 'buf34a', 'buf34b'],
        '雾森·火山' => ['fo_gate', 'mist_edge', 'deep_woods', 'thorn_maze', 'boa_swamp', 'ape_hollow', 'sage_grove', 'ash_wastes', 'ember_foothill', 'lava_river', 'cult_altar', 'magma_core', 'flame_temple', 'ruin_gate', 'volcano_heart', 'buf35a', 'buf35b'],
        '六圣柱' => ['sky_ramp', 'sky_isles', 'wind_shrine', 'frost_deep', 'lost_kingdom', 'storm_wall', 'thunder_pool', 'abyss_deep', 'forbidden_origin', 'sky_city_ruin', 'holy_stair'],
    ] + world_expansion_regions();
}

// 副本图：只能找NPC进，不参与传送
function dungeon_maps(): array
{
    return array_merge(dsw_maps(), abx_maps(), mx_maps(), fr_maps(), em_maps(), pz_maps(), sen_maps(), as_maps(), ry_maps(), vx_maps(), gy_maps(), gf_maps(), yz_maps(), region_dungeon_maps());
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
    // 主城/关口（无怪，fallback会漏）：按章节卡等级
    $cityLv = [
        'silver_gate' => 18, 'silver_sq' => 25, 'sguild' => 25,
        'capital_gate' => 100, 'avenue' => 110, 'arena_gate' => 110, 'farm' => 110,
        'buf12a' => 15, 'buf12b' => 20, 'buf23a' => 70, 'buf23b' => 85,
        'buf34a' => 315, 'buf34b' => 325, 'ice_gate' => 330,
        'buf35a' => 460, 'buf35b' => 475, 'fo_gate' => 480,
        'frostburg_gate' => 330, 'frostburg_sq' => 340, 'bounty_hall' => 340, 'fire_inn' => 340, 'furnace_st' => 340,
        'greenheart_gate' => 480, 'greenheart_sq' => 490, 'spore_market' => 490, 'rain_inn' => 490, 'elder_tree' => 490,
        'ice_village' => 335, 'ember_foothill' => 580,
        'god_gate' => 700, 'god_hall' => 700, 'god_pilgrim' => 720, 'god_library' => 800, 'god_sea' => 710, 'god_garden' => 840, 'god_orchard' => 850, 'god_court' => 760, 'god_mirror' => 900, 'god_forge' => 810, 'god_pillar' => 860, 'god_sleeper' => 925, 'god_serpent' => 940, 'god_throne' => 900, 'gf_wing' => 880, 'yz_gate' => 700, 'yz_core' => 700, 'gf_gate' => 750, 'gf_stair' => 800, 'gf_void' => 850, 'gf_core' => 900,
    ];
    if (isset($cityLv[$loc])) {
        return $cityLv[$loc];
    }
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
    // 六圣柱：按每片区域的小怪等级放行
    if (in_array($loc, ['sky_ramp', 'sky_isles', 'wind_shrine'], true)) {
        return 555;
    }
    if (in_array($loc, ['frost_deep', 'lost_kingdom'], true)) {
        return 595;
    }
    if (in_array($loc, ['storm_wall', 'thunder_pool'], true)) {
        return 635;
    }
    if (in_array($loc, ['abyss_deep', 'forbidden_origin'], true)) {
        return 685;
    }
    if (in_array($loc, ['sky_city_ruin', 'holy_stair'], true)) {
        return 755;
    }
    if (in_array($loc, dragon_lair_maps(), true)) {
        return dragon_min_lv(dragon_of_lair($loc));
    }
    // 第二弹「根据地/基地」：无怪，按所属区域第一关（gate）的等级要求放行
    if (function_exists('we2_base_ids') && in_array($loc, we2_base_ids(), true)) {
        foreach (we2_regions() as $ids) {
            if ((string) ($ids[0] ?? '') === $loc) {
                $gate = (string) ($ids[1] ?? '');
                return $gate !== '' ? teleport_min_lv($gate) : 1;
            }
        }
        return 1;
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
    $inFr = in_array($loc, fr_maps(), true);
    $inEm = in_array($loc, em_maps(), true);
    $inPz = in_array($loc, pz_maps(), true);
    $inSen = in_array($loc, sen_maps(), true);
    $inAs = in_array($loc, as_maps(), true); $inVx = in_array($loc, vx_maps(), true); $inGy = in_array($loc, gy_maps(), true); $inGf = in_array($loc, gf_maps(), true); $inYz = in_array($loc, yz_maps(), true);
    $rdGate = function_exists('region_dungeon_of') ? region_dungeon_of($loc) : '';
    $inRd = $rdGate !== '';
    if (!in_array($loc, dsw_maps(), true) && !in_array($loc, abx_maps(), true) && !in_array($loc, mx_maps(), true) && !$inFr && !$inEm && !$inPz && !$inSen && !$inAs && !$inVx && !$inGy && !$inGf && !$inYz && !$inRd) {
        return;
    }
    $inAbx = in_array($loc, abx_maps(), true);
    $inMx = in_array($loc, mx_maps(), true);
    $mats = mats_of((int) ($u['id'] ?? 0));
    $enterKey = 'dsw_enter';
    $rdEnterKey = '';
    if ($inAbx) { $enterKey = 'abx_enter'; } elseif ($inMx) { $enterKey = 'mx_enter'; }
    elseif ($inVx) { $enterKey = 'vx_enter'; } elseif ($inAs) { $enterKey = 'as_enter'; }
    elseif ($inSen) { $enterKey = 'sen_enter'; } elseif ($inPz) { $enterKey = 'pz_enter'; }
    elseif ($inEm) { $enterKey = 'em_enter'; } elseif ($inFr) { $enterKey = 'fr_enter'; }
    elseif (!empty($inGy)) { $enterKey = 'gy_enter'; } elseif (!empty($inGf)) { $enterKey = 'gf_enter'; }
    elseif (!empty($inYz)) { $enterKey = 'yz_enter'; }
    elseif ($inRd) { $rdEnterKey = 'rd_enter_' . $rdGate; $enterKey = $rdEnterKey; }
    // 副本是"走进去"的（无NPC开场），首次进入时自动开始计时
    if ($inRd && $rdEnterKey !== '' && !isset($mats[$enterKey])) {
        mat_set((int) ($u['id'] ?? 0), $enterKey, time());
        $mats = mats_of((int) ($u['id'] ?? 0));
    }
    $enter = (int) ($mats[$enterKey] ?? time());
    $limit = ($inMx || $inFr || $inEm || $inPz || $inSen || $inAs || $inVx || $inGf || $inRd) ? 2700 : 1800;
    if (time() - $enter > $limit) {
        unset($_SESSION['battle']);
        $eject = 'town_sq';
        if ($inFr) { $eject = 'ice_village'; } elseif ($inEm) { $eject = 'ember_foothill'; }
        elseif ($inPz) { $eject = 'theater'; } elseif ($inSen) { $eject = 'fo_gate'; }
        elseif ($inAs) { $eject = 'ember_foothill'; } elseif ($inVx) { $eject = 'volcano_heart'; }
        elseif ($inMx) { $eject = 'avenue'; } elseif ($inAbx) { $eject = 'silver_sq'; }
        elseif (!empty($inGy)) { $eject = 'square'; } elseif (!empty($inGf)) { $eject = 'greenheart_sq'; }
        elseif (!empty($inYz)) { $eject = 'god_hall'; }
        elseif ($inRd) { $eject = region_dungeon_eject($loc); }
        $u['loc'] = $eject;
        user_save($u);
        $backName = ['ice_village' => '冰原村', 'ember_foothill' => '火山麓', 'theater' => '傀儡剧场', 'fo_gate' => '雾森渡口', 'volcano_heart' => '火山之心', 'avenue' => '中央大道', 'silver_sq' => '白银广场', 'square' => '灰雾村广场', 'greenheart_sq' => '青叶广场', 'god_hall' => '神界圣都', 'town_sq' => '白石镇广场'][$eject] ?? '根据地';
        flash_set(($limit >= 2700 ? '45' : '30') . '分钟到了，你被传回' . $backName . '。');
    }
}

// 2觉群攻书掉落：深渊小怪2%随机一本，泰坦40%随机一本
function ch2_locations(): array
{
    $m = [];
    $m['silver_gate'] = ['name' => '白银城门', 'desc' => '灰白城墙高耸。守卫面朝内，影子被夕阳拉得很长，像黑色的手指。', 'exits' => ['buf12b' => '银道荒径', 'silver_sq' => '白银广场'], 'monsters' => []];
    $m['silver_sq'] = ['name' => '白银广场', 'desc' => '天黑后街上没人。每一道影子都可能自己动。', 'exits' => ['silver_gate' => '白银城门', 'sguild' => '公会大厅', 'dsewer1' => '下水道一层', 'warfield' => '荒芜战场', 'buf23a' => '尘埃长路', 'shadow_den' => '影爪巢穴'], 'monsters' => []];
    $m['warfield'] = ['name' => '荒芜战场', 'desc' => '公会战专用。周末晚上，这里只讲拳头。杀不同公会的人+1战功。', 'exits' => ['silver_sq' => '白银广场'], 'monsters' => []];
    $m['peak1'] = ['name' => '巅峰战场·初', 'desc' => '100~300级的角斗场。活到最后的人，只需要一个。', 'exits' => ['square' => '灰雾村·广场'], 'monsters' => []];
    $m['peak2'] = ['name' => '巅峰战场·中', 'desc' => '301~600级的角斗场。影子在这里也会出汗。', 'exits' => ['square' => '灰雾村·广场'], 'monsters' => []];
    $m['peak3'] = ['name' => '巅峰战场·终', 'desc' => '601~999级的角斗场。深渊在上面看着，挑下一个名字。', 'exits' => ['square' => '灰雾村·广场'], 'monsters' => []];
    $m['sguild'] = ['name' => '公会大厅', 'desc' => '油灯照不亮的墙上挂着白银城地图，红圈几十个。格温在桌后等你。', 'exits' => ['silver_sq' => '白银广场'], 'monsters' => []];
    $m['dsewer1'] = ['name' => '下水道一层', 'desc' => '水没脚踝，铁锈混腐肉的气味。白眼睛的影蚀鼠从四面涌来。', 'exits' => ['silver_sq' => '白银广场', 'dsewer2' => '下水道二层'], 'monsters' => ['shadow_rat', 'golden_slime']];
    $m['dsewer2'] = ['name' => '下水道二层', 'desc' => '墙上画满眼睛。角落里三具尸体靠墙坐着，影子不见了。墙上有血字：不要回答。', 'exits' => ['dsewer1' => '下水道一层', 'bmine1' => '白银矿道一层'], 'monsters' => ['shadow_rat', 'shadow_soldier']];
    $m['bmine1'] = ['name' => '白银矿道一层', 'desc' => '比下水道更冷。呼出的气是白的。矿壁后有呼吸声。', 'exits' => ['dsewer2' => '下水道二层', 'bmine2' => '矿道二层'], 'monsters' => ['shadow_soldier', 'corrupt_guard']];
    $m['bmine2'] = ['name' => '矿道二层', 'desc' => '黑袍使徒在这里等你。你的影子比来时长了三寸。', 'exits' => ['bmine1' => '白银矿道一层', 'sforest' => '影蚀森林'], 'monsters' => ['corrupt_guard', 'apostle']];
    $m['sforest'] = ['name' => '影蚀森林', 'desc' => '树是黑的，叶是灰的，落下来像纸片。林心有尊三倍大的傀儡。', 'exits' => ['bmine2' => '矿道二层', 'manor' => '废弃庄园'], 'monsters' => ['shadow_hound', 'corrupt_treant', 'gargoyle', 'guardian']];
    $m['manor'] = ['name' => '废弃庄园', 'desc' => '门开着，安静得不正常。大厅主位上，城主瓦伦丁端着红酒等你。', 'exits' => ['sforest' => '影蚀森林', 'baltar' => '深渊祭坛'], 'monsters' => ['puppet', 'valentin']];
    $m['rift_road'] = ['name' => '裂隙长路', 'desc' => '从深渊祭坛往裂隙口的长路，70~100级练级地（猎犬88/织影者97）。', 'exits' => ['baltar' => '深渊祭坛', 'rift' => '深渊裂隙口'], 'monsters' => ['rift_hound', 'rift_weaver', 'colossus']];
    $m['baltar'] = ['name' => '深渊祭坛', 'desc' => '很大，很黑。核心处站着眼睛发黑、影子如翅的暗影莉莉。', 'exits' => ['manor' => '废弃庄园', 'rift_road' => '裂隙长路'], 'monsters' => ['dark_lily']];
    $m['rift'] = ['name' => '裂隙深处', 'desc' => '没有底。你走在黑水上。深渊之眼睁着，瞳孔里是你的影子。', 'exits' => ['rift_road' => '裂隙长路'], 'monsters' => ['abyss_eye']];
    return $m;
}

// 2觉群攻书掉落：深渊小怪2%随机w5-p6一本，泰坦40%；裂隙小怪2%随机w7-p8一本，暴君40%
// 主线章完成奖称号（幂等，缺勤补发）
// 龙鳞宝箱：单抽（石头45%/蛋30%/陪练20%/称号5%，龙威0.3%大奖）
function open_dragon_box(int $uid): string
{
    $mats = mats_of($uid);
    if (empty($mats['dragon_box'])) { return '你没有龙鳞宝箱。'; }
    add_mat($uid, 'dragon_box', -1);
    $r = mt_rand(1, 1000);
    if ($r <= 450) {
        $tr = mt_rand(1, 100);
        $stone = $tr <= 70 ? 'enhance_t1' : ($tr <= 95 ? 'enhance_t2' : 'enhance_t3');
        $sn = mt_rand(10, 50);
        add_mat($uid, $stone, $sn);
        return '龙鳞宝箱开出：' . enhance_material_name($stone) . '×' . $sn . '！';
    } elseif ($r <= 750) {
        $en = mt_rand(3, 20);
        add_mat($uid, 'egg_unknown', $en);
        return '龙鳞宝箱开出：未知宠物蛋×' . $en . '！';
    } elseif ($r <= 950) {
        $dh = mt_rand(1, 3);
        add_mat($uid, 'dummy_time', 3600 * $dh);
        return '龙鳞宝箱开出：陪练' . $dh . '小时！';
    }
    $lr = mt_rand(1, 1000);
    $tid = $lr <= 660 ? 'title_dragon1' : ($lr <= 900 ? 'title_dragon2' : 'title_dragon3');
    give_title($uid, $tid);
    return '龙鳞宝箱开出大奖：称号【' . title_ids()[$tid] . '】！';
}

// 随身帐篷：野外随时扎营回满（不消耗）
// 称号走装备背包（slot=title）
function title_ids(): array
{
    return ['title_ch1' => '称号·灰雾行者', 'title_ch2' => '称号·白银守望', 'title_ch3' => '称号·王都破局者', 'title_ch4' => '称号·凛冬行者', 'title_ch5' => '称号·南炎见证', 'title_rb2' => '称号·虚空征服者', 'title_dragon1' => '称号·龙鳞初醒', 'title_dragon2' => '称号·龙血沸騰', 'title_dragon3' => '称号·灭世龙威', 'title_god' => '称号·神界主宰', 'title_rs_god' => '称号·源罪之神', 'title_rs_lord' => '称号·源罪之主', 'title_rs_king' => '称号·源罪之王', 'title_rs_gen' => '称号·源罪之将'];
}
function title_bonus(string $tid): array
{
    return ['title_ch1' => ['atk' => 30.0, 'hp' => 500.0], 'title_ch2' => ['def' => 30.0, 'hp' => 800.0], 'title_ch3' => ['crit' => 1.0, 'atk' => 50.0], 'title_ch4' => ['mdef' => 40.0, 'hp' => 1200.0], 'title_ch5' => ['matk' => 50.0, 'hp' => 1500.0], 'title_rb2' => ['skill_damage' => 3.0, 'hp' => 2000.0], 'title_dragon1' => ['critdmg' => 5.0, 'hp' => 1500.0], 'title_dragon2' => ['atk' => 100.0, 'matk' => 100.0], 'title_dragon3' => ['skill_damage' => 8.0, 'truedmg' => 200.0], 'title_god' => ['skill_damage' => 12.0, 'truedmg' => 500.0, 'hp' => 5000.0], 'title_rs_god' => ['skill_damage' => 20.0, 'truedmg' => 1000.0, 'all_attr' => 100.0], 'title_rs_lord' => ['skill_damage' => 16.0, 'truedmg' => 700.0, 'all_attr' => 70.0], 'title_rs_king' => ['skill_damage' => 13.0, 'truedmg' => 500.0, 'all_attr' => 50.0], 'title_rs_gen' => ['skill_damage' => 10.0, 'truedmg' => 300.0, 'all_attr' => 30.0]][$tid] ?? [];
}
function give_title(int $uid, string $tid): bool
{
    $names = title_ids();
    if (!isset($names[$tid])) { return false; }
    // 允许重复获得（不再吞掉第二个）
    $aff = [];
    foreach (title_bonus($tid) as $k => $v) { $aff[] = ['id' => $k, 'v' => $v, 'legend' => true]; }
    db()->prepare('INSERT INTO equips (uid, slot, name, quality, affixes, pos, req_lv) VALUES (?, "title", ?, 4, ?, "", 100)')->execute([$uid, $names[$tid], json_encode($aff, JSON_UNESCAPED_UNICODE)]);
    return true;
}
function title_list(int $uid): array
{
    $st = db()->prepare('SELECT id, name, pos FROM equips WHERE uid=? AND slot=? ORDER BY id');
    $st->execute([$uid, 'title']);
    return $st->fetchAll();
}
function title_worn(int $uid): ?array
{
    $st = db()->prepare('SELECT id, name FROM equips WHERE uid=? AND slot=? AND pos=? LIMIT 1');
    $st->execute([$uid, 'title', 'wear']);
    $r = $st->fetch();
    return $r ?: null;
}
function wear_title(int $uid, $tid): string
{
    if (ctype_digit((string) $tid)) {
        $st = db()->prepare('SELECT id FROM equips WHERE id=? AND uid=? AND slot=?');
        $st->execute([(int) $tid, $uid, 'title']);
        $r = $st->fetch();
        if (!$r) { return '没有这个称号。'; }
        return wear_equip($uid, (int) $r['id']);
    }
    $names = title_ids();
    if (!isset($names[(string) $tid])) { return '没有这个称号。'; }
    $st = db()->prepare('SELECT id FROM equips WHERE uid=? AND slot=? AND name=?');
    $st->execute([$uid, 'title', $names[(string) $tid]]);
    $r = $st->fetch();
    if (!$r) { return '你还没有这个称号。'; }
    return wear_title($uid, (int) $r['id']);
}
function use_tent(int $uid): string
{
    $u = user_by_id($uid);
    if (!$u) { return '角色不存在。'; }
    if (empty(mats_of($uid)['tent'])) { return '你没有随身帐篷。'; }
    if (isset($_SESSION['battle']) && is_array($_SESSION['battle'])) { return '战斗中不能扎营，先打完！'; }
    if (!empty(mats_of($uid)['dummy_on'])) { return '人偶挂机中不能扎营，先停下人偶！'; }
    $u['hp'] = (int) $u['maxhp'];
    $u['mp'] = (int) ($u['maxmp'] ?? 0);
    user_save($u);
    return '支起帐篷睡了一觉，生命魔力全满！';
}
// 自动售卖：开着且有时间，每得装备即判品质卖掉（离线/在线通用）
function auto_sell_check(int $uid, int $q): string
{
    $mats = mats_of($uid);
    if (empty($mats['autosell_on']) || empty($mats['autosell_time'])) { return ''; }
    $lv = (int) ($mats['autosell_q'] ?? 2);
    if ($q > $lv) { return ''; }
    $del = db()->query('SELECT id FROM equips WHERE uid=' . $uid . ' AND quality=' . $q . ' ORDER BY id DESC LIMIT 1')->fetchColumn();
    if ($del) { db()->prepare('DELETE FROM equips WHERE id=?')->execute([(int) $del]); }
    add_mat($uid, 'autosell_time', -60);
    if ((mats_of($uid)['autosell_time'] ?? 0) <= 0) { db()->prepare('DELETE FROM mats WHERE uid=? AND mat=?')->execute([$uid, 'autosell_on']); }
    $price = [0 => 5, 1 => 12, 2 => 30][$q] ?? 5;
    $u = user_by_id($uid);
    if ($u) { $u['gold'] = (int) $u['gold'] + $price; user_save($u); }
    return '（自动售卖+' . $price . '铜）';
}


function grant_chapter_titles(int $uid): void
{
    $u = user_by_id($uid);
    if (!$u) { return; }
    $q = (int) ($u['quest'] ?? 0);
    $map = [16 => 'title_ch1', 28 => 'title_ch2', 38 => 'title_ch3', 50 => 'title_ch4', 62 => 'title_ch5', 81 => 'title_rb2'];
    foreach ($map as $need => $tid) {
        if ($q >= $need) {
            $had = db()->prepare('SELECT id FROM equips WHERE uid=? AND slot=? AND name=?');
            $had->execute([$uid, 'title', title_ids()[$tid]]);
            if (!$had->fetch()) {
                give_title($uid, $tid);
                send_mail($uid, '总会长', 'title', '主线', '主线奖励称号【' . title_ids()[$tid] . '】已发放，去背包·装备穿戴！', []);
            }
        }
    }
}

function roll_aoe_book(int $uid, string $mid): string
{
    $pool = [];
    $rate = 0;
    if (in_array($mid, ['cinder_crawler', 'cinder_stalker', 'cinder_warden', 'cinder_horror'], true)) {
        $pool = ['book_w5', 'book_w6', 'book_m5', 'book_m6', 'book_h5', 'book_h6', 'book_p5', 'book_p6', 'book_s5', 'book_s6'];
        $rate = 0.02;
    } elseif ($mid === 'ash_titan') {
        $pool = ['book_w5', 'book_w6', 'book_m5', 'book_m6', 'book_h5', 'book_h6', 'book_p5', 'book_p6', 'book_s5', 'book_s6'];
        $rate = 0.4;
    } elseif (in_array($mid, ['vx_spawn', 'vx_stalker', 'vx_mage', 'vx_knight', 'vx_herald'], true)) {
        $pool = ['book_w7', 'book_w8', 'book_m7', 'book_m8', 'book_h7', 'book_h8', 'book_p7', 'book_p8', 'book_s7', 'book_s8'];
        $rate = 0.02;
    } elseif ($mid === 'vx_tyrant') {
        $pool = ['book_w7', 'book_w8', 'book_m7', 'book_m8', 'book_h7', 'book_h8', 'book_p7', 'book_p8', 'book_s7', 'book_s8'];
        $rate = 0.4;
    }
    if ($rate <= 0 || mt_rand() / mt_getrandmax() > $rate) {
        return '';
    }
    $b = $pool[array_rand($pool)];
    add_mat($uid, $b, 1);
    return skill_books()[$b]['name'];
}

function ch2_maps(): array
{
    return ['silver_gate', 'silver_sq', 'sguild', 'dsewer1', 'dsewer2', 'bmine1', 'bmine2', 'sforest', 'manor', 'baltar', 'rift_road', 'rift'];
}

function ch3_locations(): array
{
    $m = [];
    $m['capital_gate'] = ['name' => '王都城门', 'desc' => '城墙高耸入云。守卫动作僵硬，像被线提着，关节处有细小的银丝连着天空。', 'exits' => ['buf23b' => '尘埃长路', 'avenue' => '中央大道', 'buf34a' => '风雪关道', 'buf35a' => '灰雾关道'], 'monsters' => []];
    $m['avenue'] = ['name' => '王都中央大道', 'desc' => '市民走路没有声音，关节咔咔作响。瓦尔顿在街角等你：真正的王都二十年前就没了。', 'exits' => ['capital_gate' => '王都城门', 'noble' => '贵族区', 'library' => '皇家图书馆', 'slum' => '贫民窟', 'arena_gate' => '竞技场·试炼门'], 'monsters' => ['puppet_guard']];
    $m['noble'] = ['name' => '贵族区', 'desc' => '舞会彻夜不停。贵族们脚不沾地，被银丝吊在天花板上跳舞。', 'exits' => ['avenue' => '中央大道', 'cathedral' => '光明大教堂'], 'monsters' => ['masked_noble']];
    $m['library'] = ['name' => '皇家图书馆', 'desc' => '满架子全是剧本，记载着王都每一天该发生什么。墨傀儡在吃掉关于真王都的记载。', 'exits' => ['avenue' => '中央大道', 'ctomb' => '皇家陵墓'], 'monsters' => ['ink_puppet', 'mimic_chest']];
    $m['ctomb'] = ['name' => '皇家陵墓', 'desc' => '所有棺材都是空的。国王雷金纳德根本没有葬在这里。', 'exits' => ['library' => '皇家图书馆'], 'monsters' => ['silver_undead']];
    $m['slum'] = ['name' => '贫民窟', 'desc' => '黑市里有人在偷偷剪断银丝。剪线人的刀很快，刺客的刀更快。', 'exits' => ['avenue' => '中央大道', 'observatory' => '皇家天文台'], 'monsters' => ['silver_assassin']];
    $m['cathedral'] = ['name' => '光明大教堂', 'desc' => '大主教塞缪尔被银丝吊了二十年，还在布道。牵线牧师说：莉莉是钥匙，也是锁。', 'exits' => ['noble' => '贵族区', 'theater' => '傀儡剧场', 'brass_chapel' => '废弃礼拜堂'], 'monsters' => ['puppet_priest']];
    $m['observatory'] = ['name' => '皇家天文台', 'desc' => '观星者白天看星星，晚上看深渊。牵线者已经不满足于傀儡剧场，它要整个王国都变成舞台。', 'exits' => ['slum' => '贫民窟', 'theater' => '傀儡剧场'], 'monsters' => ['star_puppet']];
    $m['arena_gate'] = ['name' => '王都竞技场·试炼门', 'desc' => '铁拳的学徒在热身。战士试炼：只能普通攻击，撑十回合。', 'exits' => ['avenue' => '中央大道'], 'monsters' => []];
    $m['theater'] = ['name' => '王宫地下·傀儡剧场', 'desc' => '巨大的舞台。中央吊着真正的国王雷金纳德。牵线者从天花板降下来：我不是深渊，我是国王。', 'exits' => ['cathedral' => '大教堂', 'observatory' => '皇家天文台'], 'monsters' => ['silver_puppet', 'string_puller']];
    return $m;
}

function ch3_maps(): array
{
    return ['capital_gate', 'avenue', 'noble', 'library', 'ctomb', 'slum', 'cathedral', 'observatory', 'theater'];
}

function ice_locations(): array
{
    $m = [];
    $m['buf12a'] = ['name' => '银道荒径·前', 'desc' => '白石镇到白银城的老路，野狼和强人在此拦路。走过去，才是白银城门。', 'exits' => ['gate' => '白石镇道口', 'buf12b' => '银道荒径·后'], 'monsters' => ['road_wolf']];
    $m['buf12b'] = ['name' => '银道荒径·后', 'desc' => '离城门只剩一步，强人比狼更凶。', 'exits' => ['buf12a' => '银道荒径·前', 'silver_gate' => '白银城门'], 'monsters' => ['road_bandit']];
    $m['buf23a'] = ['name' => '尘埃长路·前', 'desc' => '白银城到王都的尘埃路，劫掠者在风里等活物。', 'exits' => ['silver_sq' => '白银广场', 'buf23b' => '尘埃长路·后'], 'monsters' => ['dust_raider']];
    $m['buf23b'] = ['name' => '尘埃长路·后', 'desc' => '王都城墙已经看得见，但掠夺者不会让你白走。', 'exits' => ['buf23a' => '尘埃长路·前', 'capital_gate' => '王都城门'], 'monsters' => ['dust_marauder']];
    $m['buf34a'] = ['name' => '风雪关道·前', 'desc' => '王都北门外的风雪关道，流浪者在雪里刨食，也刨人。', 'exits' => ['capital_gate' => '王都城门', 'buf34b' => '风雪关道·后'], 'monsters' => ['snow_drifter']];
    $m['buf34b'] = ['name' => '风雪关道·后', 'desc' => '再往前就是冰原渡口，雪更大，人更凶。', 'exits' => ['buf34a' => '风雪关道·前', 'ice_gate' => '冰原渡口'], 'monsters' => ['snow_reaver']];
    $m['buf35a'] = ['name' => '灰雾关道·前', 'desc' => '王都南门外的灰雾关道，雾里有人影晃，不是人。', 'exits' => ['capital_gate' => '王都城门', 'buf35b' => '灰雾关道·后'], 'monsters' => ['ash_drifter']];
    $m['buf35b'] = ['name' => '灰雾关道·后', 'desc' => '雾森渡口就在前面，掠夺者在雾里磨刀。', 'exits' => ['buf35a' => '灰雾关道·前', 'fo_gate' => '雾森渡口'], 'monsters' => ['ash_reaver']];
    $m['ice_gate'] =['name' => '冰原渡口', 'desc' => '王都北门外，一步之遥就是风雪。守夜人·北风裹着熊皮：往北走，别回头，冻死比被吃掉体面。', 'exits' => ['buf34b' => '风雪关道', 'ice_trail' => '风雪小径'], 'monsters' => []];
    $m['ice_trail'] = ['name' => '风雪小径', 'desc' => '雪没过膝盖。冻原霜狼在风里若隐若现，跟着落单的人。', 'exits' => ['ice_gate' => '渡口', 'ice_village' => '冰原村', 'frozen_lake' => '冰封湖'], 'monsters' => ['frost_wolf']];
    $m['ice_village'] = ['name' => '冰原村', 'desc' => '地火烧得很旺。猎户·朵拉在分肉干：湖上有怨灵，场上有猛犸，认路再走。冰渊副本从这进。', 'exits' => ['ice_trail' => '小径', 'snow_field' => '雪原', 'frostburg_gate' => '霜炉堡', 'frost_graveyard' => '霜语墓园'], 'monsters' => []];
    $m['frozen_lake'] = ['name' => '冰封湖', 'desc' => '湖面冻得发蓝，底下有东西在游。冰隙怨灵顺着裂缝爬出来，找替身。', 'exits' => ['ice_trail' => '小径', 'snow_field' => '雪原', 'frostburg_gate' => '霜炉堡', 'frost_graveyard' => '霜语墓园'], 'monsters' => ['ice_specter', 'frost_wolf']];
    $m['snow_field'] = ['name' => '雪原牧场', 'desc' => '雪原猛犸群在刨雪找草。别挡路，也别流血，血腥味会引来不止猛犸。', 'exits' => ['ice_village' => '冰原村', 'frozen_lake' => '冰封湖', 'glacier_pass' => '冰川隘口', 'wanderer_camp' => '漫游者营地'], 'monsters' => ['snow_mammoth', 'blizzard_shaman']];
    $m['glacier_pass'] = ['name' => '冰川隘口', 'desc' => '两壁冰墙高不见顶。冰川魔像卡在隘口中间，收买路钱——拿命买。', 'exits' => ['snow_field' => '雪原', 'ice_cave' => '碎冰洞', 'aurora_vale' => '极光谷'], 'monsters' => ['glacier_golem', 'frost_giant']];
    $m['aurora_vale'] = ['name' => '极光谷', 'desc' => '极光垂到山尖。极光冰蛇盘在光柱里，鳞片一张一合，像在呼吸。', 'exits' => ['glacier_pass' => '隘口'], 'monsters' => ['aurora_serpent', 'frost_giant']];
    $m['ice_cave'] = ['name' => '碎冰洞', 'desc' => '洞里全是倒挂的冰锥。冰渊蠕虫在冰层下拱动，头顶会先掉渣。', 'exits' => ['glacier_pass' => '隘口', 'blizzard_peak' => '暴风雪峰', 'wyrm_tunnel' => '蠕虫隧道'], 'monsters' => ['icewyrm', 'aurora_serpent']];
    $m['wyrm_tunnel'] = ['name' => '蠕虫隧道', 'desc' => '蠕虫钻出来的 Eidolon 通道，又黏又滑。凛冬骑士在尽头巡逻，见活物就砍。', 'exits' => ['ice_cave' => '碎冰洞'], 'monsters' => ['winter_knight', 'icewyrm']];
    $m['blizzard_peak'] = ['name' => '暴风雪峰', 'desc' => '风能把人吹上天。霜母带着幼崽在峰顶刨食，护崽的母兽最不要命。', 'exits' => ['ice_cave' => '碎冰洞', 'frost_throne' => '冬王宫'], 'monsters' => ['frost_matriarch', 'winter_knight']];
    $m['glacier_heart'] = ['name' => '冰河之心', 'desc' => '整条冰河的心脏，蓝得发黑。冰河巨像泡在里面，只露出半张脸。', 'exits' => ['blizzard_peak' => '雪峰', 'frost_throne' => '冬王宫'], 'monsters' => ['glacier_colossus', 'frost_matriarch']];
    $m['frost_throne'] = ['name' => '冬王宫', 'desc' => '冰砌的王座上坐着凛冬之王。他说：春天杀了我全家，我便杀了春天。你是春天吗？', 'exits' => ['frost_throne' => '冬王宫', 'blizzard_peak' => '雪峰', 'glacier_heart' => '冰河之心'], 'monsters' => ['winter_king']];
    // 修正：冬王宫出口不该含自己
    $m['frost_throne'] = ['name' => '冬王宫', 'desc' => '冰砌的王座上坐着凛冬之王。他说：春天杀了我全家，我便杀了春天。你是春天吗？', 'exits' => ['blizzard_peak' => '雪峰', 'glacier_heart' => '冰河之心'], 'monsters' => ['winter_king']];
    return $m;
}

function ice_maps(): array
{
    return ['ice_gate', 'ice_trail', 'ice_village', 'frozen_lake', 'snow_field', 'glacier_pass', 'aurora_vale', 'ice_cave', 'wyrm_tunnel', 'blizzard_peak', 'glacier_heart', 'frost_throne'];
}

function fr_locations(): array
{
    $m = [];
    $m['fr_gate'] = ['name' => '冰渊入口', 'desc' => '冰原村后的裂缝，深不见底。潜水人·老咸鱼：45分钟，绳子就这么长，出来或者变成冰雕。', 'exits' => ['fr_descent' => '渊底斜坡'], 'monsters' => ['fr_crawler']];
    $m['fr_descent'] = ['name' => '渊底斜坡', 'desc' => '越往下越暖和，越暖和越不对。潜伏者在岩缝里眨眼，一眨一大片。', 'exits' => ['fr_gate' => '入口', 'fr_abyss' => '渊底'], 'monsters' => ['fr_crawler', 'fr_stalker']];
    $m['fr_abyss'] = ['name' => '渊底', 'desc' => '这里没有天，只有冰。守望者站成一圈，中间什么都没有——可你不敢过去。', 'exits' => ['fr_descent' => '斜坡', 'fr_trench' => '海沟'], 'monsters' => ['fr_stalker', 'fr_warden']];
    $m['fr_trench'] = ['name' => '冰渊海沟', 'desc' => '沟底传来心跳声。恐惧有形状，顺着你的影子往上爬。', 'exits' => ['fr_abyss' => '渊底', 'fr_core' => '渊核'], 'monsters' => ['fr_warden', 'fr_horror']];
    $m['fr_core'] = ['name' => '渊核', 'desc' => '冰渊利维坦盘在这里。它翻个身，整个冰原就要抖三抖。', 'exits' => ['fr_trench' => '海沟'], 'monsters' => ['fr_leviathan']];
    return $m;
}

function fr_maps(): array
{
    return ['fr_gate', 'fr_descent', 'fr_abyss', 'fr_trench', 'fr_core'];
}

function fo_locations(): array
{
    $m = [];
    $m['fo_gate'] = ['name' => '雾森渡口', 'desc' => '王都南门外，雾浓得化不开。森语者·青芽：林子认得路，人不认。跟着萤火虫，别跟着声音。', 'exits' => ['buf35b' => '灰雾关道', 'mist_edge' => '雾森边缘', 'greenheart_gate' => '青叶城'], 'monsters' => []];
    $m['mist_edge'] = ['name' => '雾森边缘', 'desc' => '雾刚没脚面。雾森树人站着不动，动的就是你眼花——希望是眼花。', 'exits' => ['fo_gate' => '渡口', 'deep_woods' => '密林深处'], 'monsters' => ['mist_treant']];
    $m['deep_woods'] = ['name' => '密林深处', 'desc' => '孢子像下雪。吸一口，甜的；吸两口，看见你太奶。群落在雾里开花。', 'exits' => ['mist_edge' => '边缘', 'thorn_maze' => '荆棘迷宫'], 'monsters' => ['spore_colony', 'mist_treant']];
    $m['thorn_maze'] = ['name' => '荆棘迷宫', 'desc' => '荆棘会自己换位置。猎手在刺后面学鸟叫，学得最像的那只不是鸟。', 'exits' => ['deep_woods' => '密林', 'boa_swamp' => '蚺沼'], 'monsters' => ['thorn_hunter', 'spore_colony']];
    $m['boa_swamp'] = ['name' => '蚺沼', 'desc' => '水面漂着落叶，落叶下面是古林巨蚺。它吞过一整支商队，连算盘都没吐出来。', 'exits' => ['thorn_maze' => '迷宫', 'ape_hollow' => '雾猿谷'], 'monsters' => ['ancient_boa', 'thorn_hunter']];
    $m['ape_hollow'] = ['name' => '雾猿谷', 'desc' => '雾猿在谷里打架，输的请赢的吃果子，果子有毒，赢的也跑不了。', 'exits' => ['boa_swamp' => '蚺沼', 'sage_grove' => '贤者林'], 'monsters' => ['mist_apeman', 'ancient_boa']];
    $m['sage_grove'] = ['name' => '贤者林', 'desc' => '腐叶贤者坐在烂叶堆上讲经，听众是蘑菇。它说火山里住着一位老邻居，脾气很差。', 'exits' => ['ape_hollow' => '雾猿谷', 'ash_wastes' => '灰烬荒原', 'whisper_marsh' => '低语沼泽'], 'monsters' => ['rot_sage', 'mist_apeman']];
    $m['ash_wastes'] = ['name' => '灰烬荒原', 'desc' => '林子到这里秃了，灰落到脚面。余烬小鬼在灰里打滚，滚一圈大一圈。', 'exits' => ['sage_grove' => '贤者林', 'ember_foothill' => '火山麓'], 'monsters' => ['ember_imp', 'rot_sage']];
    $m['ember_foothill'] = ['name' => '火山麓', 'desc' => '石头是烫的。熔岩猎犬追着热气跑，你身上有热气，你就是热气。', 'exits' => ['ash_wastes' => '荒原', 'lava_river' => '熔岩河'], 'monsters' => ['lava_hound', 'ember_imp']];
    $m['lava_river'] = ['name' => '熔岩河', 'desc' => '河是亮的，桥是灰烬教徒搭的——用人搭的。他们请你也当一块桥板。', 'exits' => ['ember_foothill' => '火山麓', 'cult_altar' => '祭坛'], 'monsters' => ['ash_cultist', 'lava_hound']];
    $m['cult_altar'] = ['name' => '灰烬祭坛', 'desc' => '祭坛上刻着龙。熔岩魔像跪了一圈，跪的是龙，不是神。', 'exits' => ['lava_river' => '熔岩河', 'magma_core' => '熔核'], 'monsters' => ['magma_golem', 'ash_cultist']];
    $m['magma_core'] = ['name' => '熔核', 'desc' => '地心在发光。烈焰神灯灵从光里出来：许个愿吧，反正我只会实现烧掉的那个。', 'exits' => ['cult_altar' => '祭坛', 'flame_temple' => '焰神殿'], 'monsters' => ['flame_djinn', 'magma_golem']];
    $m['flame_temple'] = ['name' => '焰神殿', 'desc' => '神殿供的不是神，是鳞片。烬龙裔守着鳞片，不许人看，看了就得留下眼睛。', 'exits' => ['magma_core' => '熔核', 'ruin_gate' => '遗迹门'], 'monsters' => ['cinder_dragonspawn', 'flame_djinn']];
    $m['ruin_gate'] = ['name' => '遗迹门', 'desc' => '远古火山遗迹的大门，门缝往外冒热气。火山祭司数着人头放行，数不对就补刀。', 'exits' => ['flame_temple' => '神殿', 'volcano_heart' => '火山之心'], 'monsters' => ['volcano_priest', 'cinder_dragonspawn']];
    $m['volcano_heart'] = ['name' => '火山之心', 'desc' => '遗迹最深处，热得睁不开眼。灰烬飞龙盘在柱子上——南炎柱。柱子后面，有呼吸声。', 'exits' => ['ruin_gate' => '遗迹门', 'guru_out' => '咕噜荒原'], 'monsters' => ['ash_wyrm', 'south_flame_guard', 'ancient_ruin_golem']];
    return $m;
}

function fo_maps(): array
{
    return ['fo_gate', 'mist_edge', 'deep_woods', 'thorn_maze', 'boa_swamp', 'ape_hollow', 'sage_grove', 'ash_wastes', 'ember_foothill', 'lava_river', 'cult_altar', 'magma_core', 'flame_temple', 'ruin_gate', 'volcano_heart'];
}

function em_locations(): array
{
    $m = [];
    $m['em_gate'] = ['name' => '熔心入口', 'desc' => '火山麓裂开的口子。探火人·老灰：45分钟，绳子是石棉的，人不是。', 'exits' => ['em_flow' => '熔流道'], 'monsters' => ['em_slag']];
    $m['em_vein'] = ['name' => '熔脉', 'desc' => '岩浆像血一样在岩管里走。熔核行者顺着脉走，找heart的位置。', 'exits' => ['em_chamber' => '焚心室', 'em_core' => '熔核之心'], 'monsters' => ['em_magma', 'em_ashlord']];
    $m['em_core'] = ['name' => '熔核之心', 'desc' => '熔心化身悬在岩浆海上。它每跳一下，你的心就跟着跳一下。', 'exits' => ['em_vein' => '熔脉'], 'monsters' => ['em_heart']];
    return $m;
}

function em_maps(): array
{
    return ['em_gate', 'em_flow', 'em_chamber', 'em_vein', 'em_core'];
}

function tt_locations(): array
{
    $m = [];
    $m['tt_gate'] = ['name' => '岩狱入口', 'desc' => '黑曜石原裂开的竖井。镇狱人·岩婆：700~900级，45分钟，绳子是精钢的。', 'exits' => ['tt_trail' => '岩狱小径'], 'monsters' => ['tt_rock']];
    $m['tt_trail'] = ['name' => '岩狱小径', 'desc' => '峭兽在岩壁上爬，掉下来的碎石比拳头大。', 'exits' => ['tt_gate' => '岩狱入口', 'tt_vein' => '岩狱岩脉'], 'monsters' => ['tt_rock', 'tt_crag']];
    $m['tt_vein'] = ['name' => '岩狱岩脉', 'desc' => '熔裔顺着岩浆管走，狱守 lans着?守着脉口。', 'exits' => ['tt_trail' => '岩狱小径', 'tt_core' => '泰坦之座'], 'monsters' => ['tt_magma', 'tt_warden']];
    $m['tt_core'] = ['name' => '泰坦之座', 'desc' => '岩狱泰坦·土龙岳柱盘踞于此。它打个盹，整座山都跟着晃。', 'exits' => ['tt_vein' => '岩狱岩脉'], 'monsters' => ['tt_titan']];
    return $m;
}

function tt_maps(): array
{
    return ['tt_gate', 'tt_trail', 'tt_vein', 'tt_core'];
}

function pz_locations(): array
{
    $m = [];
    $m['pz_gate'] = ['name' => '傀儡回廊·入口', 'desc' => '提线从门缝垂下来。学徒说：大师在里面等观众，等了二十年。', 'exits' => ['pz_hall' => '回廊大厅'], 'monsters' => ['pz_puller']];
    $m['pz_hall'] = ['name' => '回廊大厅', 'desc' => '舞者在没音乐的地方跳舞，跳得越好，线勒得越紧。', 'exits' => ['pz_gate' => '入口', 'pz_gallery' => '画廊'], 'monsters' => ['pz_puller', 'pz_dancer']];
    $m['pz_gallery'] = ['name' => '覆面画廊', 'desc' => '墙上全是脸，脸全是你的。覆面傀儡一张张试戴。', 'exits' => ['pz_hall' => '大厅', 'pz_stage' => '舞台'], 'monsters' => ['pz_dancer', 'pz_mask']];
    $m['pz_stage'] = ['name' => '回廊舞台', 'desc' => '守卫站在幕布后面，幕布后面是更多的守卫。', 'exits' => ['pz_gallery' => '画廊', 'pz_core' => '大师之间'], 'monsters' => ['pz_mask', 'pz_golem']];
    $m['pz_core'] = ['name' => '大师之间', 'desc' => '傀儡大师坐在提线中间：二十年没观众了，你来得正好，演完再走——或者别走。', 'exits' => ['pz_stage' => '舞台'], 'monsters' => ['pz_master']];
    return $m;
}

function pz_maps(): array
{
    return ['pz_gate', 'pz_hall', 'pz_gallery', 'pz_stage', 'pz_core'];
}

function sen_locations(): array
{
    $m = [];
    $m['sen_gate'] = ['name' => '雾森祭坛·入口', 'desc' => '雾在这里变绿。祭铃自己响，响一声，雾浓一分。', 'exits' => ['sen_path' => '祭道'], 'monsters' => ['sen_priest']];
    $m['sen_path'] = ['name' => '祭道', 'desc' => '两边全是荆棘，荆棘上挂着雾铃。别碰，碰了会响。', 'exits' => ['sen_gate' => '入口', 'sen_altar' => '祭坛'], 'monsters' => ['sen_priest', 'sen_thorn']];
    $m['sen_altar'] = ['name' => '雾祭坛', 'desc' => '雾蛇盘在坛上，坛下是守卫，守卫脚下是更多的蛇。', 'exits' => ['sen_path' => '祭道', 'sen_heart' => '雾心'], 'monsters' => ['sen_thorn', 'sen_serpent']];
    $m['sen_heart'] = ['name' => '雾心', 'desc' => '雾在这里有心跳。守卫跪了一圈，跪的是雾，不是神。', 'exits' => ['sen_altar' => '祭坛', 'sen_core' => '化身之森'], 'monsters' => ['sen_serpent', 'sen_guard']];
    $m['sen_core'] = ['name' => '化身之森', 'desc' => '雾森化身从雾里长出来：林子记得每一个砍树的人。你砍过吗？', 'exits' => ['sen_heart' => '雾心'], 'monsters' => ['sen_avatar']];
    return $m;
}

function sen_maps(): array
{
    return ['sen_gate', 'sen_path', 'sen_altar', 'sen_heart', 'sen_core'];
}

function wild3_locations(): array
{
    $m = [];
    $m['shadow_den'] = ['name' => '影爪巢穴', 'desc' => '白银城外的裂谷，影子在这里筑巢。影爪领主磨着爪子，等路过的人。', 'exits' => ['silver_sq' => '白银广场'], 'monsters' => ['shadow_claw']];
    $m['brass_chapel'] = ['name' => '废弃礼拜堂', 'desc' => '黄铜主教在这里布道，听众是空椅子。它说空椅子比活人虔诚。', 'exits' => ['cathedral' => '大教堂'], 'monsters' => ['brass_bishop']];
    $m['wanderer_camp'] = ['name' => '漫游者营地', 'desc' => '雪原上的空营地，火还没灭。漫游者从不等人，只等人追上来。', 'exits' => ['snow_field' => '雪原'], 'monsters' => ['tundra_wanderer']];
    return $m;
}

function wild3_maps(): array
{
    return ['shadow_den', 'brass_chapel', 'wanderer_camp'];
}

function frostburg_locations(): array
{
    $m = [];
    $m['frostburg_gate'] = ['name' => '霜炉堡·城门', 'desc' => '地火把城墙烧得发烫。城门上刻着：冷不死人，穷死人。', 'exits' => ['ice_village' => '冰原村', 'frostburg_sq' => '霜炉广场'], 'monsters' => []];
    $m['frostburg_sq'] = ['name' => '霜炉广场', 'desc' => '广场中央是口地火井，烧得全城暖烘烘。悬赏板钉满了单子，冒险者围了一圈。', 'exits' => ['frostburg_gate' => '城门', 'bounty_hall' => '悬赏大厅', 'fire_inn' => '地火旅店', 'furnace_st' => '融炉街'], 'monsters' => []];
    $m['bounty_hall'] = ['name' => '悬赏大厅', 'desc' => '每天三单，杀够数领钱领药。榜爷说：单子天天有，命只有一条。', 'exits' => ['frostburg_sq' => '广场'], 'monsters' => []];
    $m['fire_inn'] = ['name' => '地火旅店', 'desc' => '床是热的，酒是烫的。老板娘：在我这睡一觉，冻伤全好。', 'exits' => ['frostburg_sq' => '广场'], 'monsters' => []];
    $m['furnace_st'] = ['name' => '融炉街', 'desc' => '铁匠铺一家挨一家， 霜钢在这里论斤卖。', 'exits' => ['frostburg_sq' => '广场'], 'monsters' => []];
    return $m;
}

function greenheart_locations(): array
{
    $m = [];
    $m['greenheart_gate'] = ['name' => '青叶城·树门', 'desc' => '整座城长在一棵树上。树门会眨眼，眨一下放一个人。', 'exits' => ['fo_gate' => '雾森渡口', 'greenheart_sq' => '青叶广场'], 'monsters' => []];
    $m['greenheart_sq'] = ['name' => '青叶广场', 'desc' => '广场是片大叶子，踩上去软乎乎。孢子商人摆了一地发光的东西。', 'exits' => ['greenheart_gate' => '树门', 'spore_market' => '孢子市场', 'rain_inn' => '雨枝旅店', 'elder_tree' => '古树议会', 'god_gate' => '神界·天门'], 'monsters' => []];
    $m['spore_market'] = ['name' => '孢子市场', 'desc' => '卖什么的都有，买什么的都得砍价。砍不下来就打一架，打赢了白拿——开玩笑的，别当真。', 'exits' => ['greenheart_sq' => '广场'], 'monsters' => []];
    $m['rain_inn'] = ['name' => '雨枝旅店', 'desc' => '屋顶漏雨，店小二说那是特色。床是干的，这就够了。', 'exits' => ['greenheart_sq' => '广场'], 'monsters' => []];
    $m['elder_tree'] = ['name' => '古树议会', 'desc' => '三棵老树在开会，开了三百年。悬赏单贴在树皮上，风一吹哗哗响。', 'exits' => ['greenheart_sq' => '广场'], 'monsters' => []];
    return $m;
}

function wild45_locations(): array
{
    $m = [];
    $m['frost_graveyard'] = ['name' => '霜语墓园', 'desc' => '墓碑上全是冰。霜裔猎手在碑林里练箭，箭箭不离碑——碑都快被射穿了。', 'exits' => ['ice_village' => '冰原村', 'howl_valley' => '冰嚎谷', 'frostborn_camp' => '霜裔营地'], 'monsters' => ['frostborn_hunter', 'ice_specter']];
    $m['howl_valley'] = ['name' => '冰嚎谷', 'desc' => '风过谷口像狼嚎。冻原霜狼在这里开会，商量下山路线。', 'exits' => ['frost_graveyard' => '墓园', 'frost_waste' => '冻土荒原'], 'monsters' => ['frost_wolf', 'frostborn_hunter']];
    $m['frost_waste'] = ['name' => '冻土荒原', 'desc' => '荒原上只有风和雪盲。雪盲洼地在前面，进去容易，出来看命。', 'exits' => ['howl_valley' => '冰嚎谷', 'snowblind_hollow' => '雪盲洼地'], 'monsters' => ['snow_mammoth', 'winter_knight']];
    $m['snowblind_hollow'] = ['name' => '雪盲洼地', 'desc' => '白茫茫一片，走三步就分不清东南西北。冰瀑在洼地尽头轰隆隆响。', 'exits' => ['frost_waste' => '荒原', 'ice_fall' => '冰瀑'], 'monsters' => ['blizzard_shaman', 'frost_matriarch']];
    $m['ice_fall'] = ['name' => '冰瀑', 'desc' => '瀑布冻在半空。雪崩巨人坐在瀑布顶上，往下扔雪球——雪球有房子大。', 'exits' => ['snowblind_hollow' => '洼地'], 'monsters' => ['avalanche_titan']];
    $m['frostborn_camp'] = ['name' => '霜裔营地', 'desc' => '霜裔猎手的营地，火堆整夜不灭。他们说：火灭了，人就散了。', 'exits' => ['frost_graveyard' => '墓园'], 'monsters' => ['frostborn_hunter', 'aurora_serpent']];
    $m['whisper_marsh'] = ['name' => '低语沼泽', 'desc' => '沼泽里全是硫磺味。硫磺精在气泡里洗澡，洗得越久越黄。', 'exits' => ['sage_grove' => '贤者林', 'mushroom_forest' => '蘑菇林'], 'monsters' => ['sulfur_sprite', 'rot_sage']];
    $m['mushroom_forest'] = ['name' => '蘑菇林', 'desc' => '蘑菇比人高。孢子群落在这里开演唱会，门票是你的鼻子。', 'exits' => ['whisper_marsh' => '沼泽', 'fallen_city' => '落叶城废墟'], 'monsters' => ['spore_colony', 'sulfur_sprite']];
    $m['fallen_city'] = ['name' => '落叶城废墟', 'desc' => '曾经的城，现在只剩城门。荆棘猎手在城墙上巡逻，巡的是寂寞。', 'exits' => ['mushroom_forest' => '蘑菇林', 'sulfur_spring' => '硫磺泉', 'ash_chapel2' => '灰烬小教堂'], 'monsters' => ['thorn_hunter', 'ancient_boa']];
    $m['sulfur_spring'] = ['name' => '硫磺泉', 'desc' => '泉水咕嘟咕嘟冒泡。黑曜石原在前面，黑得发亮。', 'exits' => ['fallen_city' => '废墟', 'obsidian_field' => '黑曜石原'], 'monsters' => ['sulfur_sprite', 'ember_imp']];
    $m['obsidian_field'] = ['name' => '黑曜石原', 'desc' => '满地黑曜石，亮得晃眼。黑曜石巨像站在原中央，一动不动——动起来就是灾难。', 'exits' => ['sulfur_spring' => '硫磺泉'], 'monsters' => ['obsidian_colossus']];
    $m['ash_chapel2'] = ['name' => '灰烬小教堂', 'desc' => '和废弃礼拜堂一个师傅盖的。灰烬教徒在这里排练，排的是葬礼——自己的。', 'exits' => ['fallen_city' => '废墟'], 'monsters' => ['ash_cultist', 'magma_golem']];
    return $m;
}

function as_locations(): array
{
    $m = [];
    $m['as_gate'] = ['name' => '灰烬深渊·入口', 'desc' => '火山底下的裂缝，灰往上冒。引路人说：2觉的群攻书，全在这底下。', 'exits' => ['as_trail' => '灰烬小径'], 'monsters' => ['cinder_crawler']];
    $m['as_trail'] = ['name' => '灰烬小径', 'desc' => '灰没脚面。爬行者在灰里游，游出一条条沟。', 'exits' => ['as_gate' => '入口', 'as_vein' => '灰脉'], 'monsters' => ['cinder_crawler', 'cinder_stalker']];
    $m['as_vein'] = ['name' => '灰脉', 'desc' => '灰烬像血一样流动。潜伏者在流动里眨眼。', 'exits' => ['as_trail' => '小径', 'as_hollow' => '灰烬空洞'], 'monsters' => ['cinder_stalker', 'cinder_warden']];
    $m['as_hollow'] = ['name' => '灰烬空洞', 'desc' => '空洞里全是灰。守望者站成一圈，恐惧在圈里开花。', 'exits' => ['as_vein' => '灰脉', 'as_core' => '泰坦之座'], 'monsters' => ['cinder_warden', 'cinder_horror']];
    $m['as_core'] = ['name' => '泰坦之座', 'desc' => '深渊灰烬泰坦坐在灰里：想学群攻？先从我的灰里活下来。', 'exits' => ['as_hollow' => '空洞'], 'monsters' => ['ash_titan']];
    return $m;
}

function as_maps(): array
{
    return ['as_gate', 'as_trail', 'as_vein', 'as_hollow', 'as_core'];
}

function gr_locations(): array
{
    $m = [];
    $m['guru_out'] = ['name' => '咕噜荒原', 'desc' => '风沙磨骨头，远处土丘上有张大嘴在打哈欠——那是巢穴的门。', 'exits' => ['volcano_heart' => '火山之心', 'guru_den' => '咕噜巢穴'], 'monsters' => ['gr_hyena', 'gr_vulture', 'gr_worm', 'gr_raider', 'gr_storm']];
    $m['guru_den'] = ['name' => '咕噜巢穴', 'desc' => '骨头铺路，口水当灯。越往里越大声的咀嚼。', 'exits' => ['guru_out' => '咕噜荒原', 'guru_throne' => '暴食王座'], 'monsters' => ['gr_bat', 'gr_guard', 'gr_whelp', 'gr_wraith', 'gr_spider']];
    $m['guru_throne'] = ['name' => '暴食王座', 'desc' => '白骨堆的王座上，食人魔咕噜抱着半扇龙排啃。看见你，它笑了。', 'exits' => ['guru_den' => '咕噜巢穴', 'guru_pit' => '巢穴深渊'], 'monsters' => ['gr_retainer', 'gr_shaman', 'gr_fang', 'gr_idol', 'gr_guru']];
    $m['guru_pit'] = ['name' => '巢穴深渊', 'desc' => '王座底下还有底。掉下去的东西，都成了它的夜宵。', 'exits' => ['guru_throne' => '暴食王座'], 'monsters' => ['gr_brood', 'gr_shard', 'gr_shadow', 'gr_nightmare', 'gr_ancient']];
    return $m;
}


function ry_locations(): array
{
    $m = [];
    $m['ry_gate'] = ['name' => '岩狱·入口', 'desc' => '石头在这里会呼吸。岳柱的影子压在门口。', 'exits' => ['ry_path' => '狱道'], 'monsters' => ['ry_crawler']];
    $m['ry_path'] = ['name' => '岩狱道', 'desc' => '两壁全是挤压的岩层，守卫从墙里走出来。', 'exits' => ['ry_gate' => '入口', 'ry_hall' => '塑岩厅'], 'monsters' => ['ry_crawler', 'ry_guard']];
    $m['ry_hall'] = ['name' => '塑岩大厅', 'desc' => '塑岩者把你捏成小泥人，又拆掉，说还不够硬。', 'exits' => ['ry_path' => '狱道', 'ry_prison' => '狱牢'], 'monsters' => ['ry_guard', 'ry_shaper']];
    $m['ry_prison'] = ['name' => '岩狱牢', 'desc' => '狱卒数着你的骨头。牢门后地在震。', 'exits' => ['ry_hall' => '大厅', 'ry_core' => '泰坦之间'], 'monsters' => ['ry_shaper', 'ry_warder']];
    $m['ry_core'] = ['name' => '泰坦之间', 'desc' => '岩狱泰坦·土龙岳柱盘在中央：山就是它，它就是山。', 'exits' => ['ry_prison' => '狱牢'], 'monsters' => ['ry_titan']];
    return $m;
}

function ry_maps(): array
{
    return ['ry_gate', 'ry_path', 'ry_hall', 'ry_prison', 'ry_core'];
}
// 神界：700~999级主线，从青叶广场天门进入
function god_locations(): array
{
    $m = [];
    $m['god_gate'] = ['name' => '神界·天门', 'desc' => '云在脚下断成两半。门楣上没有字，是六只眼睛。神使·伽楼：往上去，七百年才开一次。', 'exits' => ['god_hall' => '圣都', 'greenheart_sq' => '青叶广场'], 'monsters' => ['god_seraph']];
    $m['god_hall'] = ['name' => '神界·圣都', 'desc' => '云砌的城，路上没有影子。神界商人只收金币，不问你从哪来。', 'exits' => ['god_gate' => '天门', 'god_sea' => '云海', 'god_pilgrim' => '拾光阶'], 'monsters' => []];
    $m['god_sea'] = ['name' => '云海', 'desc' => '云海托着碎掉的星。执事在云上走，不留下脚印。', 'exits' => ['god_hall' => '圣都', 'god_court' => '神庭', 'god_library' => '藏星馆', 'god_garden' => '种星园'], 'monsters' => ['god_seraph', 'god_cloud', 'god_cherub']];
    $m['god_court'] = ['name' => '神庭', 'desc' => '白色的大厅没有影子。守望和裁决并排站着，谁也不看谁。', 'exits' => ['god_sea' => '云海', 'god_forge' => '神铸台', 'god_mirror' => '镜厅', 'god_orchard' => '星果林'], 'monsters' => ['god_watcher', 'god_judge']];
    $m['god_forge'] = ['name' => '神铸台', 'desc' => '神匠锤打的是自己的骨头。守卫从炉火里站起来。', 'exits' => ['god_court' => '神庭', 'god_pillar' => '天柱'], 'monsters' => ['god_forger', 'god_warden', 'god_heraldg']];
    $m['god_pillar'] = ['name' => '天柱', 'desc' => '一根柱子撑住整片天。石像爬在柱上，泰坦在柱顶。', 'exits' => ['god_forge' => '神铸台', 'god_throne' => '神座'], 'monsters' => ['god_golem', 'god_titan', 'god_highpriest']];
    $m['god_pilgrim'] = ['name' => '拾光阶', 'desc' => '朝圣者拾起碎掉的星光，一片片拼回去。拼好了，星又碎了。他们不生气，继续拼。', 'exits' => ['god_hall' => '圣都', 'god_library' => '藏星馆'], 'monsters' => ['god_pilgrim']];
    $m['god_library'] = ['name' => '藏星馆', 'desc' => '典吏把星星登记造册，一颗编号一颗命。借走的星，没有一颗按时还过。', 'exits' => ['god_pilgrim' => '拾光阶', 'god_sea' => '云海'], 'monsters' => ['god_librarian']];
    $m['god_garden'] = ['name' => '种星园', 'desc' => '园丁把星种进土里，浇的是晨光。摘星的时候不许哭，哭了星就咸了。', 'exits' => ['god_sea' => '云海', 'god_orchard' => '星果林'], 'monsters' => ['god_gardener']];
    $m['god_orchard'] = ['name' => '星果林', 'desc' => '果子熟了会自己掉下来，砸在地上变成下一颗星的种子。', 'exits' => ['god_garden' => '种星园', 'god_court' => '神庭'], 'monsters' => ['god_gardener']];
    $m['god_mirror'] = ['name' => '镜厅', 'desc' => '满墙的镜子照不出人脸，只照出判词。判者站在镜子里，等你自己走进来认罪。', 'exits' => ['god_court' => '神庭', 'god_throne' => '神座'], 'monsters' => ['god_mirror']];
    $m['god_sleeper'] = ['name' => '沉眠谷', 'desc' => '失翼者在这里睡觉，睡了三百年。他们的梦漏出来，变成谷里的雾。', 'exits' => ['god_pillar' => '天柱', 'god_serpent' => '衔尾渊'], 'monsters' => ['god_sleeper']];
    $m['god_serpent'] = ['name' => '衔尾渊', 'desc' => '天蛇咬着自己的尾巴，围成一个圈。圈里的时间是圆的，走进去的人都会回到入口——除了你。', 'exits' => ['god_sleeper' => '沉眠谷', 'god_throne' => '神座'], 'monsters' => ['god_serpent']];
    $m['god_throne'] = ['name' => '神座', 'desc' => '空的神座，坐着一团没有脸的光。它是这世界的第一个谎言。', 'exits' => ['god_pillar' => '天柱', 'god_mirror' => '镜厅', 'god_serpent' => '衔尾渊', 'gf_gate' => '堕天阶·入口'], 'monsters' => ['god_avatar', 'god_archon']];
    return $m;
}

function god_maps(): array
{
    return ['god_gate', 'god_hall', 'god_pilgrim', 'god_library', 'god_sea', 'god_garden', 'god_orchard', 'god_court', 'god_mirror', 'god_forge', 'god_pillar', 'god_sleeper', 'god_serpent', 'god_throne'];
}

// 神界副本·堕天阶（750~999级，45分钟）
function gf_locations(): array
{
    $m = [];
    $m['gf_gate'] = ['name' => '堕天阶·入口', 'desc' => '台阶是折断的翅膀铺成的。往下走，越走越亮，亮得刺眼。', 'exits' => ['gf_stair' => '坠落之阶'], 'monsters' => ['gf_shade']];
    $m['gf_stair'] = ['name' => '坠落之阶', 'desc' => '每一步都踩在别人的坠路上。幽影贴着阶梯往上爬。', 'exits' => ['gf_gate' => '入口', 'gf_void' => '虚空回廊'], 'monsters' => ['gf_shade', 'gf_void']];
    $m['gf_void'] = ['name' => '虚空回廊', 'desc' => '没有墙，只有坠落的星在缓慢地掉。噬者就在星星之间。', 'exits' => ['gf_stair' => '坠落之阶', 'gf_wing' => '折翼回廊'], 'monsters' => ['gf_void', 'gf_reaper']];
    $m['gf_wing'] = ['name' => '折翼回廊', 'desc' => '折翼圣者排成两列，翅膀铺在地上当红毯。你踩上去，脚底全是旧伤。', 'exits' => ['gf_void' => '虚空回廊', 'gf_core' => '天罚之座'], 'monsters' => ['gf_fallen']];
    $m['gf_core'] = ['name' => '天罚之座', 'desc' => '堕天神帝坐在自己的断翼上。他抬头看你：连神明也会坠落，你凭什么站着。', 'exits' => ['gf_wing' => '折翼回廊'], 'monsters' => ['gf_herald', 'gf_seraphim', 'god_emperor']];
    // 源罪渊·月度副本（700级+，每月一次，单BOSS）
    $m['yz_gate'] = ['name' => '源罪渊·门', 'desc' => '门上没有锁，只有一行字：你所犯的罪，已经够开这扇门了。每月只开一次，门后只有一个东西在等你。', 'exits' => ['yz_core' => '渊底'], 'monsters' => []];
    $m['yz_core'] = ['name' => '渊底·本相之座', 'desc' => '整个世界所有的罪，收缩成一个人形。它没有脸，因为它就是每个人的脸。你杀的不是它，是你自己的罪。', 'exits' => ['yz_gate' => '门'], 'monsters' => ['yz_boss']];
    return $m;
}

function gf_maps(): array
{
    return ['gf_gate', 'gf_stair', 'gf_void', 'gf_wing', 'gf_core'];
}
function vx_locations(): array
{
    $m = [];
    $m['vx_gate'] = ['name' => '虚空裂隙·入口', 'desc' => '天裂开一道口子，往里看是更多的眼睛。虚空行者：进去就别回头，回头的那面你已经死了。', 'exits' => ['vx_path' => '裂隙小径'], 'monsters' => ['vx_spawn']];
    $m['vx_path'] = ['name' => '裂隙小径', 'desc' => '路是碎的，踩上去掉渣。潜伏者在碎片底下游，一游一大片。', 'exits' => ['vx_gate' => '入口', 'vx_hall' => '低语大厅'], 'monsters' => ['vx_spawn', 'vx_stalker']];
    $m['vx_hall'] = ['name' => '低语大厅', 'desc' => '低语者念着你的名字，念对一次，你就矮一寸。', 'exits' => ['vx_path' => '小径', 'vx_throne' => '骑士王座'], 'monsters' => ['vx_stalker', 'vx_mage']];
    $m['vx_throne'] = ['name' => '骑士王座', 'desc' => '虚空骑士坐在空王座上，传令官在旁边报菜名——报的是你的菜。', 'exits' => ['vx_hall' => '大厅', 'vx_core' => '暴君之心'], 'monsters' => ['vx_mage', 'vx_knight', 'vx_herald']];
    $m['vx_core'] = ['name' => '暴君之心', 'desc' => '虚空暴君没有心，它的心就是这条裂隙。你每砍它一刀，裂隙就小一分。', 'exits' => ['vx_throne' => '王座'], 'monsters' => ['vx_tyrant']];
    return $m;
}

function vx_maps(): array
{
    return ['vx_gate', 'vx_path', 'vx_hall', 'vx_throne', 'vx_core'];
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
    $m['gy_gate'] = ['name' => '罪渊·门', 'desc' => '门上刻着：弃尽美德者，方可入此门。善意在门后等你，杀了它们，你就自由了。', 'exits' => ['gy_hall' => '美德回廊'], 'monsters' => ['gy_kindness', 'gy_love', 'gy_friendship']];
    $m['gy_hall'] = ['name' => '罪渊·美德回廊', 'desc' => '勇气、希望、信任挂在回廊两侧，像风干的果子。一碰就碎。', 'exits' => ['gy_gate' => '门', 'gy_abyss' => '渊底'], 'monsters' => ['gy_courage', 'gy_hope', 'gy_trust', 'gy_mercy']];
    $m['gy_abyss'] = ['name' => '罪渊·渊底', 'desc' => '正义坐在渊底，1滴血。它说：动手吧，杀了我，你就是罪本身。', 'exits' => ['gy_hall' => '美德回廊'], 'monsters' => ['gy_mercy', 'gy_justice']];
    return $m;
}

function mx_maps(): array
{
    return ['mx_gate', 'mx_path', 'mx_hall', 'mx_depth', 'mx_nest', 'mx_heart'];
}

function gy_maps(): array
{
    return ['gy_gate', 'gy_hall', 'gy_abyss'];
}

function yz_maps(): array
{
    return ['yz_gate', 'yz_core'];
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
        $fx = ['wild3' => '荒野3', 'swamp1' => '黑水沼1'];
        // 同层串联：低语林1↔2↔3↔4，保证每一座都走得进（原先 3/4 无入口=死图）
        if ($i > 1) { $fx['forest' . ($i - 1)] = '低语林' . ($i - 1); }
        if ($i < 4) { $fx['forest' . ($i + 1)] = '低语林' . ($i + 1); }
        $m['forest' . $i] = ['name' => '低语林' . $i, 'desc' => '树学人说话。巨蜂+盗贼。杀20蜂交差。', 'exits' => $fx, 'monsters' => ['bee', 'thief']];
    }
    for ($i = 1; $i <= 4; $i++) {
        $fx = ['forest2' => '低语林2', 'hill1' => '灰岩丘1'];
        // 同层串联：黑水沼1↔2↔3↔4（原 3/4 无入口）
        if ($i > 1) { $fx['swamp' . ($i - 1)] = '黑水沼' . ($i - 1); }
        if ($i < 4) { $fx['swamp' . ($i + 1)] = '黑水沼' . ($i + 1); }
        $m['swamp' . $i] = ['name' => '黑水沼' . $i, 'desc' => '毒蛙遍地。修女要20份蛙囊。', 'exits' => $fx, 'monsters' => ['frog', 'slime']];
    }
    for ($i = 1; $i <= 4; $i++) {
        $fx = ['swamp2' => '黑水沼2', 'ruin1' => '废弃村1'];
        // 同层串联：灰岩丘1↔2↔3↔4（原 2/3/4 无入口）
        if ($i > 1) { $fx['hill' . ($i - 1)] = '灰岩丘' . ($i - 1); }
        if ($i < 4) { $fx['hill' . ($i + 1)] = '灰岩丘' . ($i + 1); }
        $m['hill' . $i] = ['name' => '灰岩丘' . $i, 'desc' => '骷髅+吸血蝠群。', 'exits' => $fx, 'monsters' => ['skeleton', 'bat']];
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
    $m['mine1'] = ['name' => '矿坑第一层·白纹矿脉', 'desc' => '矿车自己在动。空气里飘着腐化孢子的甜味。', 'exits' => ['mine_gate' => '封锁门', 'mine2' => '矿坑第二层'], 'monsters' => ['runaway_miner', 'corrupt_rat', 'deep_bat']];
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

function god_quests(): array
{
    return [
        // 第一幕·登天（伽楼：天碎了，拾光去）
        100 => ['name' => '天碎之后', 'todo' => '神界·天门：六翼天使×30（伽楼：天碎了，拾光去，真相在云上面）', 'loc' => 'god_gate', 'locname' => '神界·天门', 'need' => ['god_seraph' => 30], 'flag' => null],
        101 => ['name' => '拾光阶', 'todo' => '拾光阶：拾光朝圣者×30（他们说：光不等人，等星）', 'loc' => 'god_pilgrim', 'locname' => '拾光阶', 'need' => ['god_pilgrim' => 30], 'flag' => null],
        // 第二幕·藏星（白羽的人情，情报换门票）
        102 => ['name' => '借星不还', 'todo' => '藏星馆：藏星典吏×30（白羽：典吏欠我三颗星，你去讨，顺便问问"第一个谎言"）', 'loc' => 'god_library', 'locname' => '藏星馆', 'need' => ['god_librarian' => 30], 'flag' => null],
        103 => ['name' => '云海执事', 'todo' => '云海：云海执事×30、智天使×20（执事在云上走，不留下脚印，只留下账）', 'loc' => 'god_sea', 'locname' => '云海', 'need' => ['god_cloud' => 30, 'god_cherub' => 20], 'flag' => null],
        // 第三幕·种星园
        104 => ['name' => '摘星', 'todo' => '种星园：种星园丁×40（园丁：摘星的时候不许哭，哭了星就咸了）', 'loc' => 'god_garden', 'locname' => '种星园', 'need' => ['god_gardener' => 40], 'flag' => null],
        105 => ['name' => '星果林', 'todo' => '星果林：种星园丁×40（熟了的星自己掉下来，砸成下一颗星的种子）', 'loc' => 'god_orchard', 'locname' => '星果林', 'need' => ['god_gardener' => 40], 'flag' => null],
        // 第四幕·神庭之影
        106 => ['name' => '神庭双卫', 'todo' => '神庭：神庭守望×30、裁决天使×30（白色大厅没有影子，影子都在镜厅）', 'loc' => 'god_court', 'locname' => '神庭', 'need' => ['god_watcher' => 30, 'god_judge' => 30], 'flag' => null],
        107 => ['name' => '镜中判词', 'todo' => '镜厅：镜中判者×40（镜胜雪每面镜子都要你认一条罪，去赎罪——用剑）', 'loc' => 'god_mirror', 'locname' => '镜厅', 'need' => ['god_mirror' => 40], 'flag' => null],
        // 第五幕·神铸之骨
        108 => ['name' => '锤骨', 'todo' => '神铸台：神匠×50、神铸守卫×40、神界传令×20（神匠锤打的是自己的骨头）', 'loc' => 'god_forge', 'locname' => '神铸台', 'need' => ['god_forger' => 50, 'god_warden' => 40, 'god_heraldg' => 20], 'flag' => null],
        109 => ['name' => '天柱', 'todo' => '天柱：天柱石像×50、天柱泰坦×30、神界大祭司×15（一根柱子撑住整片天，撑不住了）', 'loc' => 'god_pillar', 'locname' => '天柱', 'need' => ['god_golem' => 50, 'god_titan' => 30, 'god_highpriest' => 15], 'flag' => null],
        // 第六幕·梦与尾
        110 => ['name' => '沉眠谷', 'todo' => '沉眠谷：失翼沉眠者×40（他们的梦漏出来，变成谷里的雾）', 'loc' => 'god_sleeper', 'locname' => '沉眠谷', 'need' => ['god_sleeper' => 40], 'flag' => null],
        111 => ['name' => '衔尾渊', 'todo' => '衔尾渊：衔尾天蛇×30（圈里的时间是圆的——除了你）', 'loc' => 'god_serpent', 'locname' => '衔尾渊', 'need' => ['god_serpent' => 30], 'flag' => null],
        // 第七幕·神座之谎（堕天赛丽亚迎接，免票进堕天阶）
        112 => ['name' => '空的神座', 'todo' => '神座：神座化身×1、神界执政官×10（它是这世界的第一个谎言，它说：杀了我）', 'loc' => 'god_throne', 'locname' => '神座', 'need' => ['god_avatar' => 1, 'god_archon' => 10], 'flag' => null],
        // 第八幕·坠落（堕天阶）
        113 => ['name' => '堕天阶', 'todo' => '堕天阶·入口：堕天幽影×30（台阶是折断的翅膀铺成的）', 'loc' => 'gf_gate', 'locname' => '堕天阶·入口', 'need' => ['gf_shade' => 30], 'flag' => null],
        114 => ['name' => '坠落之阶', 'todo' => '坠落之阶：虚空噬者×40（每一步都踩在别人的坠路上）', 'loc' => 'gf_stair', 'locname' => '坠落之阶', 'need' => ['gf_void' => 40], 'flag' => null],
        115 => ['name' => '折翼回廊', 'todo' => '折翼回廊：折翼圣者×30（翅膀铺在地上当红毯，脚底全是旧伤）', 'loc' => 'gf_wing', 'locname' => '折翼回廊', 'need' => ['gf_fallen' => 30], 'flag' => null],
        116 => ['name' => '虚空回廊', 'todo' => '虚空回廊：天罚收割者×40、堕天炽天使×20（噬者就在星星之间）', 'loc' => 'gf_void', 'locname' => '虚空回廊', 'need' => ['gf_reaper' => 40, 'gf_seraphim' => 20], 'flag' => null],
        // 终幕·天罚之座（三指环，终局抉择在杀神帝时触发）
        117 => ['name' => '天罚之座', 'todo' => '天罚之座：堕天传令×30、堕天神帝·路西法×1（他抬头看你：连神明也会坠落，你凭什么站着）', 'loc' => 'gf_core', 'locname' => '天罚之座', 'need' => ['gf_herald' => 30, 'god_emperor' => 1], 'flag' => null],
    ];
}

function quest_god_baseline(int $uid, int $q): void
{
    $qs = god_quests()[$q] ?? null;
    if (!$qs || empty($qs['need'])) {
        return;
    }
    $ex = cflags((int) $uid)['qb' . $q] ?? null;
    if (is_array($ex) && $ex !== []) {
        return;
    }
    $c = kill_counts((int) $uid);
    $b = [];
    foreach ($qs['need'] as $mid => $need) {
        $b[$mid] = (int) ($c[$mid] ?? 0);
    }
    cflag_set((int) $uid, 'qb' . $q, $b);
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
        // 80级竞技场引导：找雷横对话完成，得20银（一次性，不挡主线）
        if ((int) ($u['lv'] ?? 1) >= 80 && empty(cflags((int) ($u['id'] ?? 0))['arena_guide_done'])) {
            return ['name' => '竞技场征召', 'step' => '引导', 'todo' => '80级了！去灰雾村广场找竞技场大师·雷横，参加竞技场（奖励20银）', 'loc' => 'square', 'locname' => '灰雾村广场', 'id' => 28, 'ch' => 2, 'need' => []];
        }
        return ['name' => '第三章·傀儡之夜', 'step' => '序', 'todo' => '第二章完成！带着格温的推荐信，去王都中央大道找瓦尔顿', 'loc' => 'avenue', 'locname' => '中央大道', 'id' => 28, 'ch' => 2, 'need' => []];
    }
    if ($q >= 30 && $q <= 37) {
        $all = ch3_quests();
        if (!isset($all[$q])) {
            return ['name' => '傀儡之夜·完', 'step' => '完成', 'todo' => '第三章完成，去王都农场种菜吧', 'loc' => 'farm', 'locname' => '王都农场', 'id' => 99, 'ch' => 3, 'need' => []];
        }
        return $all[$q] + ['id' => $q, 'ch' => 3, 'step' => ($q - 29) . '/8'];
    }
    if ($q >= 40 && $q <= 45) {
        $all = rebirth1_quests();
        if (!isset($all[$q])) {
            return ['name' => '影子伙伴', 'step' => '完成', 'todo' => '1觉完成！你的路已经选定（被动生效），继续练级吧', 'loc' => 'avenue', 'locname' => '中央大道', 'id' => 99, 'ch' => 4, 'need' => []];
        }
        // 试炼类任务不需要杀怪，need为空
        return $all[$q] + ['id' => $q, 'ch' => 4, 'step' => ($q - 39) . '/6'];
    }
    if ($q >= 50 && $q <= 61) {
        $all = ch4_quests();
        if (!isset($all[$q])) {
            return ['name' => '凛冬冰原·完', 'step' => '完成', 'todo' => '第四章完成，雾森在南门外等你', 'loc' => 'fo_gate', 'locname' => '雾森渡口', 'id' => 99, 'ch' => 5, 'need' => []];
        }
        return $all[$q] + ['id' => $q, 'ch' => 5, 'step' => ($q - 49) . '/12'];
    }
    if ($q >= 62 && $q <= 75) {
        $all = ch5_quests();
        if (!isset($all[$q])) {
            return ['name' => '雾森火山·完', 'step' => '完成', 'todo' => '第五章完成！世界BOSS、熔心副本、神魂系统等你', 'loc' => 'volcano_heart', 'locname' => '火山之心', 'id' => 99, 'ch' => 6, 'need' => []];
        }
        return $all[$q] + ['id' => $q, 'ch' => 6, 'step' => ($q - 61) . '/14'];
    }
    if ($q === 76) {
        if ((int) ($u['lv'] ?? 1) >= 599) {
            return ['name' => '2觉·群攻之路', 'step' => '序', 'todo' => '599级了！去王都中央大道找总会长瓦尔顿，开启2觉（八条路，群攻）', 'loc' => 'avenue', 'locname' => '中央大道', 'id' => 76, 'ch' => 6, 'need' => []];
        }
        return ['name' => '南炎守望', 'step' => '完成', 'todo' => '五章全通关！世界BOSS每天刷新，神魂还能升级（找神魂师）。599级找瓦尔顿2觉', 'loc' => 'volcano_heart', 'locname' => '火山之心', 'id' => 99, 'ch' => 6, 'need' => []];
    }
    if ($q >= 77 && $q <= 80) {
        $all = rebirth2_quests();
        if (!isset($all[$q])) {
            return ['name' => '群攻之路·完', 'step' => '完成', 'todo' => '2觉完成！虚空裂隙/灰烬深渊刷群攻书', 'loc' => 'vx_gate', 'locname' => '虚空裂隙', 'id' => 99, 'ch' => 7, 'need' => []];
        }
        return $all[$q] + ['id' => $q, 'ch' => 7, 'step' => ($q - 76) . '/4'];
    }
    if ($q >= 100 && $q <= 117) {
        $all = god_quests();
        if (!isset($all[$q])) {
            return ['name' => '神界守望', 'step' => '完成', 'todo' => '神界通关！堕天神帝已伏诛。神铸/堕天套装、圣都商人等你', 'loc' => 'god_hall', 'locname' => '神界·圣都', 'id' => 99, 'ch' => 8, 'need' => []];
        }
        return $all[$q] + ['id' => $q, 'ch' => 8, 'step' => ($q - 99) . '/18'];
    }
    if ($q >= 118) {
        return ['name' => '神界守望', 'step' => '完成', 'todo' => '神界通关！堕天神帝已伏诛。神铸/堕天套装、圣都商人等你', 'loc' => 'god_hall', 'locname' => '神界·圣都', 'id' => 99, 'ch' => 8, 'need' => []];
    }
    if ($q >= 81) {
        if ((int) ($u['lv'] ?? 1) >= 700) {
            return ['name' => '神界·天门', 'step' => '待启', 'todo' => '700级了！去青叶广场往上走到【神界·天门】找神使·伽楼，开启第八章', 'loc' => 'god_gate', 'locname' => '神界·天门', 'id' => 81, 'ch' => 7, 'need' => []];
        }
        return ['name' => '虚空守望', 'step' => '完成', 'todo' => '2觉完成！群攻在手，虚空裂隙/灰烬深渊/世界BOSS等你（700级开神界）', 'loc' => 'vx_gate', 'locname' => '虚空裂隙', 'id' => 99, 'ch' => 7, 'need' => []];
    }
    if ($q >= 38) {
        // 三章通关但没299：自由探索；299了进1觉
        if ((int) ($u['lv'] ?? 1) >= 299) {
            return ['name' => '1觉·觉醒之路', 'step' => '序', 'todo' => '299级了！去王都中央大道找总会长瓦尔顿，开启1觉（不觉醒卡299级）', 'loc' => 'avenue', 'locname' => '中央大道', 'id' => 38, 'ch' => 3, 'need' => []];
        }
        return ['name' => '影子伙伴', 'step' => '完成', 'todo' => '第三章完成！农场、母巢、公会战等你（影子伙伴：全属性+5%）。299级后找瓦尔顿1觉', 'loc' => 'farm', 'locname' => '王都农场', 'id' => 99, 'ch' => 3, 'need' => []];
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

function ch4_quests(): array
{
    return [
        50 => ['name' => '渡口来信', 'todo' => '风雪小径：冻原霜狼×120', 'loc' => 'ice_trail', 'locname' => '风雪小径', 'need' => ['frost_wolf' => 120], 'flag' => null],
        51 => ['name' => '冰封湖的替身', 'todo' => '冰封湖：冰隙怨灵×150', 'loc' => 'frozen_lake', 'locname' => '冰封湖', 'need' => ['ice_specter' => 150], 'flag' => null],
        52 => ['name' => '猛犸之路', 'todo' => '雪原牧场：雪原猛犸×150、暴风雪萨满×60', 'loc' => 'snow_field', 'locname' => '雪原牧场', 'need' => ['snow_mammoth' => 150, 'blizzard_shaman' => 60], 'flag' => null],
        53 => ['name' => '隘口买路钱', 'todo' => '冰川隘口：冰川魔像×150、霜巨人×80', 'loc' => 'glacier_pass', 'locname' => '冰川隘口', 'need' => ['glacier_golem' => 150, 'frost_giant' => 80], 'flag' => null],
        54 => ['name' => '极光下的蛇', 'todo' => '极光谷：极光冰蛇×150', 'loc' => 'aurora_vale', 'locname' => '极光谷', 'need' => ['aurora_serpent' => 150], 'flag' => null],
        55 => ['name' => '洞中回声', 'todo' => '碎冰洞：冰渊蠕虫×150（打完找老咸鱼拿冰渊入场券）', 'loc' => 'ice_cave', 'locname' => '碎冰洞', 'need' => ['icewyrm' => 150], 'flag' => null],
        56 => ['name' => '隧道巡逻兵', 'todo' => '蠕虫隧道：凛冬骑士×150', 'loc' => 'wyrm_tunnel', 'locname' => '蠕虫隧道', 'need' => ['winter_knight' => 150], 'flag' => null],
        57 => ['name' => '护崽的母兽', 'todo' => '暴风雪峰：霜母×120', 'loc' => 'blizzard_peak', 'locname' => '暴风雪峰', 'need' => ['frost_matriarch' => 120], 'flag' => null],
        58 => ['name' => '冰河之心', 'todo' => '冰河之心：冰河巨像×150', 'loc' => 'glacier_heart', 'locname' => '冰河之心', 'need' => ['glacier_colossus' => 150], 'flag' => null],
        59 => ['name' => '冰渊试炼', 'todo' => '冰渊副本：守望者×30、渊底恐惧×20', 'loc' => 'fr_abyss', 'locname' => '冰渊', 'need' => ['fr_warden' => 30, 'fr_horror' => 20], 'flag' => null],
        60 => ['name' => '冬王宫前', 'todo' => '雪峰与冰河：凛冬骑士×100、霜母×60、冰河巨像×60', 'loc' => 'blizzard_peak', 'locname' => '暴风雪峰', 'need' => ['winter_knight' => 100, 'frost_matriarch' => 60, 'glacier_colossus' => 60], 'flag' => null],
        61 => ['name' => '凛冬之王', 'todo' => '冬王宫：击败凛冬之王', 'loc' => 'frost_throne', 'locname' => '冬王宫', 'need' => ['winter_king' => 1], 'flag' => null],
    ];
}

function ch5_quests(): array
{
    return [
        62 => ['name' => '森语者', 'todo' => '雾森边缘：雾森树人×150', 'loc' => 'mist_edge', 'locname' => '雾森边缘', 'need' => ['mist_treant' => 150], 'flag' => null],
        63 => ['name' => '甜蜜的孢子', 'todo' => '密林深处：孢子群落×150', 'loc' => 'deep_woods', 'locname' => '密林深处', 'need' => ['spore_colony' => 150], 'flag' => null],
        64 => ['name' => '荆棘学鸟叫', 'todo' => '荆棘迷宫：荆棘猎手×150', 'loc' => 'thorn_maze', 'locname' => '荆棘迷宫', 'need' => ['thorn_hunter' => 150], 'flag' => null],
        65 => ['name' => '商队的算盘', 'todo' => '蚺沼：古林巨蚺×150', 'loc' => 'boa_swamp', 'locname' => '蚺沼', 'need' => ['ancient_boa' => 150], 'flag' => null],
        66 => ['name' => '果子有毒', 'todo' => '雾猿谷：雾猿×150', 'loc' => 'ape_hollow', 'locname' => '雾猿谷', 'need' => ['mist_apeman' => 150], 'flag' => null],
        67 => ['name' => '蘑菇听众', 'todo' => '贤者林：腐叶贤者×150', 'loc' => 'sage_grove', 'locname' => '贤者林', 'need' => ['rot_sage' => 150], 'flag' => null],
        68 => ['name' => '灰里打滚', 'todo' => '灰烬荒原：余烬小鬼×150', 'loc' => 'ash_wastes', 'locname' => '灰烬荒原', 'need' => ['ember_imp' => 150], 'flag' => null],
        69 => ['name' => '热气', 'todo' => '火山麓：熔岩猎犬×150（打完找老灰拿熔心入场券）', 'loc' => 'ember_foothill', 'locname' => '火山麓', 'need' => ['lava_hound' => 150], 'flag' => null],
        70 => ['name' => '桥板', 'todo' => '熔岩河：灰烬教徒×150', 'loc' => 'lava_river', 'locname' => '熔岩河', 'need' => ['ash_cultist' => 150], 'flag' => null],
        71 => ['name' => '跪的是龙', 'todo' => '灰烬祭坛：熔岩魔像×150', 'loc' => 'cult_altar', 'locname' => '灰烬祭坛', 'need' => ['magma_golem' => 150], 'flag' => null],
        72 => ['name' => '烧掉的愿', 'todo' => '熔核：烈焰神灯灵×150', 'loc' => 'magma_core', 'locname' => '熔核', 'need' => ['flame_djinn' => 150], 'flag' => null],
        73 => ['name' => '鳞片', 'todo' => '焰神殿与遗迹门：烬龙裔×120、火山祭司×60', 'loc' => 'flame_temple', 'locname' => '焰神殿', 'need' => ['cinder_dragonspawn' => 120, 'volcano_priest' => 60], 'flag' => null],
        74 => ['name' => '熔心试炼', 'todo' => '熔心副本：熔核行者×30、灰烬领主侍从×20', 'loc' => 'em_vein', 'locname' => '熔脉', 'need' => ['em_magma' => 30, 'em_ashlord' => 20], 'flag' => null],
        75 => ['name' => '南炎柱', 'todo' => '火山之心：南炎守卫×60、击败遗迹守卫，见熔火守望者（别惊动巨龙）', 'loc' => 'volcano_heart', 'locname' => '火山之心', 'need' => ['south_flame_guard' => 60, 'ancient_ruin_golem' => 1], 'flag' => 'doyle_met'],
    ];
}
// 1觉·觉醒之路（quest 40~45）：四试炼+抉择，不杀怪，纯NPC交互
function rebirth1_quests(): array
{
    return [
        40 => ['name' => '1觉·抉择之始', 'todo' => '去王都中央大道找总会长瓦尔顿，听他讲四条路', 'loc' => 'avenue', 'locname' => '中央大道', 'need' => [], 'flag' => 'rb1_start'],
        41 => ['name' => '1觉·四重试炼', 'todo' => '完成四重试炼：竞技场铁拳（承伤）/图书馆莫里亚蒂（答题）/郊野霍克（追鹿）/大教堂塞缪尔（舍药），顺序不限', 'loc' => 'avenue', 'locname' => '中央大道', 'need' => [], 'flag' => null],
        42 => ['name' => '1觉·战士试炼', 'todo' => '（已并入四重试炼）', 'loc' => 'arena', 'locname' => '王都竞技场', 'need' => [], 'flag' => 'rb1_war'],
        43 => ['name' => '1觉·法师试炼', 'todo' => '（已并入四重试炼）', 'loc' => 'library', 'locname' => '皇家图书馆', 'need' => [], 'flag' => 'rb1_mag'],
        44 => ['name' => '1觉·猎手试炼', 'todo' => '（已并入四重试炼）', 'loc' => 'suburb', 'locname' => '外城郊野', 'need' => [], 'flag' => 'rb1_hun'],
        45 => ['name' => '1觉·抉择', 'todo' => '四试炼完成，回中央大道找瓦尔顿选择你的路（牧师试炼在大教堂找塞缪尔）', 'loc' => 'avenue', 'locname' => '中央大道', 'need' => [], 'flag' => null],
    ];
}

// 2觉·群攻之路（quest 77~80）：599级接取，试炼杀怪，抉择双向
function rebirth2_quests(): array
{
    return [
        77 => ['name' => '2觉·抉择之始', 'todo' => '去王都中央大道找总会长瓦尔顿，听他讲八条路', 'loc' => 'avenue', 'locname' => '中央大道', 'need' => [], 'flag' => 'rb2_start'],
        78 => ['name' => '2觉·虚空试炼', 'todo' => '火山之心：南炎守卫×40、击败遗迹守卫', 'loc' => 'volcano_heart', 'locname' => '火山之心', 'need' => ['south_flame_guard' => 40, 'ancient_ruin_golem' => 1], 'flag' => null],
        79 => ['name' => '2觉·两条路', 'todo' => '回中央大道找瓦尔顿，从本职业两个方向选一个', 'loc' => 'avenue', 'locname' => '中央大道', 'need' => [], 'flag' => 'rb2_job'],
        80 => ['name' => '2觉·加冕', 'todo' => '找瓦尔顿加冕，领取群攻之路奖励', 'loc' => 'avenue', 'locname' => '中央大道', 'need' => [], 'flag' => 'rb2_done'],
    ];
}

function rebirth2_names(): array
{
    return [
        'warrior' => ['berserker' => '狂战士', 'aegis' => '铁卫'],
        'mage' => ['wintermage' => '凛冬法师', 'wildfire' => '燎原使者'],
        'hunter' => ['ranger' => '游侠', 'assassin' => '影刺客'],
        'priest' => ['shadowbishop' => '暗影主教', 'holybishop' => '神圣主教'],
        'summoner' => ['necro' => '死灵之拥', 'plague' => '痛苦盛宴'],
    ];
}

function rebirth2_skill(string $rb2): string
{
    return ['berserker' => 'w5', 'aegis' => 'w6', 'wintermage' => 'm5', 'wildfire' => 'm6', 'ranger' => 'h5', 'assassin' => 'h6', 'shadowbishop' => 'p5', 'holybishop' => 'p6', 'necro' => 's5', 'plague' => 's6'][$rb2] ?? '';
}


function rebirth2_skill2(string $rb2): string

{

    return ['berserker' => 'w7', 'aegis' => 'w8', 'wintermage' => 'm7', 'wildfire' => 'm8', 'ranger' => 'h7', 'assassin' => 'h8', 'shadowbishop' => 'p7', 'holybishop' => 'p8', 'necro' => 's7', 'plague' => 's8'][$rb2] ?? '';

}



// 2觉被动技能目录：抉择即自动领悟，效果常驻


function rb2_passive_catalog(): array

{

    return [

        'berserker' => ['name' => '狂怒', 'desc' => '生命低于50%时伤害+15%；狂战损血加成再+25%'],

        'aegis' => ['name' => '铁壁', 'desc' => '受到伤害-8%；铁壁反击伤害+35%'],

        'wintermage' => ['name' => '凛冬', 'desc' => '冰雷属性伤害+30%，攻击12%麻痹目标'],

        'wildfire' => ['name' => '燎原', 'desc' => '火风属性伤害+40%'],

        'ranger' => ['name' => '毒雨', 'desc' => '所有持续伤害(DOT)+35%；群攻铺层更快'],

        'assassin' => ['name' => '影遁', 'desc' => '闪避+18%；刺客单体爆发伤害+60%'],

        'shadowbishop' => ['name' => '鲜血契约', 'desc' => '吸血+3%'],

        'holybishop' => ['name' => '圣光灌注', 'desc' => '治疗+15%'],

        'necro' => ['name' => '亡者军团', 'desc' => '召唤上限+2（2→4）；召唤兽死亡时爆炸，对周围造成范围暗影伤害'],

        'plague' => ['name' => '灵魂腐蚀', 'desc' => '解锁献祭流召唤兽【噬魂夜宴】(s6)与【永夜契约】(s8)：以自身生命换取强力召唤物'],

    ];

}


function rebirth2_passive_desc(string $rb2): string
{
    return [
        'berserker' => '狂怒：生命低于50%时伤害+15%；狂战损血加成再+25%',
        'aegis' => '铁壁：受到伤害-8%；铁壁反击伤害+35%',
        'wintermage' => '凛冬：冰雷属性伤害+30%，攻击12%麻痹目标',
        'wildfire' => '燎原：火风属性伤害+40%',
        'ranger' => '毒雨：所有持续伤害(DOT)+35%；群攻铺层更快',
        'assassin' => '影遁：闪避+18%；刺客单体爆发伤害+60%',
        'shadowbishop' => '鲜血契约：吸血+3%',
        'holybishop' => '圣光灌注：治疗+15%',
        'necro' => '亡者军团：召唤上限+2（2→4）；召唤兽死亡时爆炸，对周围造成范围暗影伤害',
        'plague' => '灵魂腐蚀：解锁献祭流召唤兽【噬魂夜宴】(s6)与【永夜契约】(s8)，以自身生命换取强力召唤物',
    ][$rb2] ?? '';
}

function rebirth2_name(string $rb2): string
{
    foreach (rebirth2_names() as $jobs) {
        if (isset($jobs[$rb2])) {
            return $jobs[$rb2];
        }
    }
    return '';
}
function rebirth1_names(): array
{
    return ['warrior' => '狂战士', 'mage' => '元素使', 'hunter' => '追猎者', 'priest' => '圣光牧师', 'summoner' => '缚魂使'];
}

function rebirth1_passive_desc(string $job): string
{
    return [
        'warrior' => '浴血：生命每降低10%，攻击+4%（最多+32%，保底不亏）；防战技能额外+10%',
        'mage' => '元素亲和：元素伤害+12%',
        'hunter' => '鹰眼：暴击+8%，命中+8%，15%概率标记使目标易伤10%持续2轮',
        'priest' => '圣光庇护：每回合3%最大生命，受伤5%概率减伤40%',
        'summoner' => '魂契：每当你或你的宠物受到伤害，10%概率召唤一只深渊小鬼助战。小鬼自动攻击敌人造成30%攻击力的暗影伤害，并替你承伤（不占回合，只有被打死才会消失）。',
    ][$job] ?? '';
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

function quest3_baseline(int $uid, int $q, bool $force = false): void
{
    $qs = ch3_quests()[$q] ?? null;
    if (!$qs || empty($qs['need'])) {
        return;
    }
    if (!$force) {
        $ex = cflags((int) $uid)['qb' . $q] ?? null;
        if (is_array($ex) && $ex !== []) {
            return;
        }
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
// ===== 罪值系统 =====
// 罪值：怪物击杀累积；>100可被任何玩家强制PK，<10时生命上限+20%
function sin_name_html(string $name, int $sin): string
{
    $safe = h($name);
    if ($sin >= 999) {
        return '<span style="background:linear-gradient(135deg,#a0f,#000);-webkit-background-clip:text;background-clip:text;color:transparent;font-weight:bold">' . $safe . '</span>';
    }
    if ($sin > 500) {
        return '<span style="color:#c0f;font-weight:bold">' . $safe . '</span>';
    }
    if ($sin > 100) {
        return '<span style="color:#f33;font-weight:bold">' . $safe . '</span>';
    }
    return $safe;
}

function sin_of(int $uid): int
{
    $f = cflags((int) $uid);
    return max(0, min(999, (int) ($f['sin'] ?? 0)));
}

// 在内存用户上结算：加罪值并同步<10的+20%生命（调用方负责 user_save）
function sin_gain_array(array &$u, int $n = 1): void
{
    // 源罪·神之罪主：罪值获取减半
    if ($n > 0 && function_exists('src_sin_effects') && src_sin_effects((int) ($u['id'] ?? 0))['lord']) {
        $n = max(1, (int) floor($n / 2));
    }
    // 罪值曲线（防挂机爆表）：
    // - 普通怪+0.2，精英+1，BOSS+5；挂机一晚(8h,约480只普通)≈96，刚好卡在红线下
    // - 衰减：每次结算按在线时长折，每调用一次扣0.002（约每500只抵1点）
    // - 上限999；<10的+20%生命逻辑不变
    $uid = (int) ($u['id'] ?? 0);
    if ($uid <= 0) {
        return;
    }
    $f = cflags($uid);
    $old = (float) ($f['sin_f'] ?? max(0, (int) ($f['sin'] ?? 0)));
    // 获取速度总系数：当前为原始设计的 1%（打怪攒罪明显变慢）
    $rate = 0.01;
    $gain = ($n === 1 ? 0.2 : $n) * $rate;
    // 衰减也同比缩放，保持"攒多久/掉多快"的手感比例不变
    $new = max(0.0, min(999.0, $old + $gain - 0.002 * $rate));
    $newI = (int) floor($new + 1e-9);
    $have = (int) ($f['sin_hp'] ?? 0);
    $base = max(1, (int) $u['maxhp'] - $have);
    $want = $newI < 10 ? (int) floor($base * 0.2) : 0;
    // 生命上限涨多少，当前血也补多少（上限下降时按新上限截断）
    $oldMax = (int) $u['maxhp'];
    $u['maxhp'] = max(1, $base + $want);
    $u['hp'] = max(1, min((int) $u['hp'] + ((int) $u['maxhp'] - $oldMax), (int) $u['maxhp']));
    $f['sin_f'] = $new;
    $f['sin'] = $newI;
    $f['sin_hp'] = $want;
    db()->prepare('UPDATE users SET chapter_flags=? WHERE id=?')->execute([json_encode($f, JSON_UNESCAPED_UNICODE), $uid]);
    // 罪首次破百：自动领悟本职业罪技能（不抢默认active，玩家去技能页手动切）
    if ($newI >= 100 && $old < 100) {
        $uu = user_by_id($uid);
        if ($uu) {
            $sid = ['warrior' => 'w9', 'mage' => 'm9', 'hunter' => 'h9', 'priest' => 'p9', 'summoner' => 's9'][job_id_of($uu)] ?? 'w9';
            $has = db()->prepare('SELECT skill FROM skills WHERE uid=? AND skill=?');
            $has->execute([$uid, $sid]);
            if (!$has->fetch()) {
                db()->prepare('INSERT OR IGNORE INTO skills (uid, skill, level, prof, tier, active) VALUES (?, ?, 1, 0, 1, 0)')->execute([$uid, $sid]);
                send_mail($uid, '罪字碑', 'sin', '罪技能领悟：' . (skill_catalog()[$sid]['name'] ?? $sid), '你的罪值已满100，罪字碑为你刻下【' . (skill_catalog()[$sid]['name'] ?? $sid) . '】。去技能页设为默认技能（罪<100会被强制切回）。', []);
            }
        }
    }
}

function sin_gain_typed(array &$u, string $kind): void
{
    // kind: normal/elite/boss
    if ($kind === 'boss') {
        sin_gain_array($u, 5);
    } elseif ($kind === 'elite') {
        sin_gain_array($u, 1);
    } elseif ($kind === 'gy') {
        // 罪渊美德怪：1滴血，杀一只+8罪
        sin_gain_array($u, 8);
    } else {
        sin_gain_array($u, 1);
    }
}
// 场景外直接改DB的罪值
function sin_add(int $uid, int $n): int
{
    $uid = (int) $uid;
    $u = user_by_id($uid);
    if (!$u) {
        return 0;
    }
    sin_gain_array($u, $n);
    user_save($u);
    return sin_of($uid);
}

// 载入自愈：把<10的+20%生命补进存量（老号首次登录也生效）
function sin_sync_hp(int $uid): void
{
    $uid = (int) $uid;
    $u = user_by_id($uid);
    if (!$u) {
        return;
    }
    $f = cflags($uid);
    $have = (int) ($f['sin_hp'] ?? 0);
    $base = max(1, (int) $u['maxhp'] - $have);
    $want = sin_of($uid) < 10 ? (int) floor($base * 0.2) : 0;
    if ($want === $have) {
        return;
    }
    // 生命上限涨多少，当前血也补多少：否则新号出生/上限变动后会不满血
    $oldMax = (int) $u['maxhp'];
    $u['maxhp'] = max(1, $base + $want);
    $u['hp'] = max(1, min((int) $u['hp'] + ((int) $u['maxhp'] - $oldMax), (int) $u['maxhp']));
    user_save($u);
    cflag_set($uid, 'sin_hp', $want);
}

// 可被掠夺的赃物池：可交易装备（身上/背包）+ 可交易材料
function sin_loot_pool(int $uid): array
{
    $pool = [];
    $st = db()->prepare('SELECT * FROM equips WHERE uid=?');
    $st->execute([(int) $uid]);
    foreach ($st->fetchAll() as $e) {
        if (($e['slot'] ?? '') === 'soul') {
            continue;
        }
        if ((int) ($e['quality'] ?? 0) >= 3 || equip_is_set(equip_shortname((string) $e['name']))) {
            $pool[] = ['t' => 'equip', 'e' => $e];
        }
    }
    foreach (mats_of((int) $uid) as $mid => $num) {
        if ((int) $num > 0 && is_tradable_mat((string) $mid)) {
            $pool[] = ['t' => 'mat', 'mid' => (string) $mid, 'num' => (int) $num];
        }
    }
    return $pool;
}

// 罪值击杀掉落：随机最多2件，掉在原地（1分钟后消失）
function sin_drop_loot(int $victim, string $loc): array
{
    $pool = sin_loot_pool((int) $victim);
    if ($pool === []) {
        return [];
    }
    shuffle($pool);
    $out = [];
    foreach (array_slice($pool, 0, 2) as $it) {
        if ($it['t'] === 'equip') {
            $e = $it['e'];
            $name = equip_shortname((string) $e['name']);
            if ((string) ($e['pos'] ?? '') === 'wear') {
                take_off_equip((int) $victim, (int) $e['id']);
            }
            db()->prepare('UPDATE equips SET uid=0, pos="ground" WHERE id=?')->execute([(int) $e['id']]);
            ground_place_equip($loc, (int) $e['id']);
            $out[] = $name;
        } else {
            $mid = (string) $it['mid'];
            $drop = max(1, min((int) $it['num'], mt_rand(1, (int) ceil((int) $it['num'] * 0.5))));
            add_mat((int) $victim, $mid, -$drop);
            ground_place_mat($loc, $mid, $drop);
            $out[] = mat_name($mid) . 'x' . $drop;
        }
    }
    _gear_uncache((int) $victim);
    return $out;
}

// 神界击杀掉落：1.2%随机神铸件，BOSS保底
function god_kill_drop(int $uid, string $mid): string
{
    $uid = (int) $uid;
    $u = user_by_id($uid);
    if (!$u) {
        return '';
    }
    $boss = in_array($mid, ['god_avatar', 'god_emperor'], true);
    $chance = $boss ? 100 : 1.2;
    if (mt_rand(1, 1000) / 10 > $chance) {
        return '';
    }
    $pieces = [
        ['weapon', '神铸·雷霆'], ['weapon', '神铸·苍穹'], ['weapon', '神铸·铁律'], ['weapon', '神铸·天罚'], ['weapon', '神铸·圣盾'], ['weapon', '神铸·裁决'], ['weapon', '神铸·曙光'],
        ['head', '神铸·圣冠'], ['body', '神铸·羽甲'], ['legs', '神铸·天裤'], ['belt', '神铸·云腰'], ['shoes', '神铸·踏云靴'], ['gloves', '神铸·执掌手套'], ['back', '神铸·天翼披风'], ['ring', '神铸·星辰戒'], ['necklace', '神铸·神谕链'], ['offhand', '神铸·圣灯'],
    ];
    $pc = $pieces[array_rand($pieces)];
    $sn = make_equip($uid, $pc[0], $pc[1], 4, max(1, (int) ($u['lv'] ?? 1)));
    if ($sn === '') {
        return '';
    }
    return '掉落传说【' . $sn . '】（神铸套装，等级越高越强）！';
}

function dusk_kill_drop(int $uid, string $mid): string
{
    $uid = (int) $uid;
    $rate = ['god_emperor' => 25, 'god_avatar' => 10, 'god_highpriest' => 5][$mid] ?? 0;
    if ($rate <= 0 || mt_rand(1, 100) > $rate) {
        return '';
    }
    $u = user_by_id($uid);
    if (!$u) {
        return '';
    }
    $pieces = [['weapon', '暮影·暮刃'], ['gloves', '暮影·暮誓手套'], ['necklace', '暮影·暮链']];
    $pc = $pieces[array_rand($pieces)];
    $sn = make_equip($uid, $pc[0], $pc[1], 4, max(1, (int) ($u['lv'] ?? 1)));
    if ($sn === '') {
        return '';
    }
    return '掉落传说【' . $sn . '】（暮影套装，拾光者的执念）！';
}

// 蚀心 DOT 套装掉落：神界副本·堕天阶(gf_*)专属，只给猎人。小怪低概率、BOSS 高概率。
function gf_dot_set_drop(int $uid, string $mid): string
{
    // 堕天阶(gf_*)入口~小怪，及其 BOSS 堕天神帝(god_emperor)
    if ($mid !== 'god_emperor' && !str_starts_with($mid, 'gf_')) {
        return '';
    }
    if (job_id_of(user_by_id((int) $uid) ?? []) !== 'hunter') {
        return '';
    }
    $rate = ($mid === 'god_emperor') ? 30 : 6;
    if (mt_rand(1, 100) > $rate) {
        return '';
    }
    $u = user_by_id((int) $uid);
    if (!$u) {
        return '';
    }
    $pieces = [
        ['weapon', '蚀心·猎弓'], ['offhand', '蚀心·箭袋'], ['body', '蚀心·血衣'],
        ['head', '蚀心·覆面'], ['legs', '蚀心·绑腿'], ['gloves', '蚀心·裹手'], ['shoes', '蚀心·轻履'],
    ];
    $p = $pieces[array_rand($pieces)];
    $sn = make_equip((int) $uid, (string) $p[0], (string) $p[1], 4, max(750, (int) ($u['lv'] ?? 750)));
    if ($sn === '') {
        return '';
    }
    return '掉落传说【' . $sn . '】（蚀心DOT套装，凑齐引爆持续伤害）！';
}
// 【源罪渊】BOSS掉落：70%概率给一件【寂灭】/【噬渊】套装或同主题传说
function yz_kill_drop(int $uid, string $mid): string
{
    $uid = (int) $uid;
    if ($mid !== 'yz_boss') {
        return '';
    }
    if (mt_rand(1, 100) > 70) {
        return '本相散了，什么都没留下。（源罪渊掉落 70%）';
    }
    $u = user_by_id($uid);
    if (!$u) {
        return '';
    }
    // 主题池：两套各5件 + 同主题传说单件
    $pool = [
        ['jimie', 'weapon', '寂灭·终末之刃'], ['jimie', 'head', '寂灭·永恒寂灭冠'], ['jimie', 'body', '寂灭·虚无礼赞袍'], ['jimie', 'necklace', '寂灭·终焉铭刻链'], ['jimie', 'ring', '寂灭·寂静终戒指'],
        ['shiyuan', 'weapon', '噬渊·深渊吞噬者'], ['shiyuan', 'body', '噬渊·万渊之腹甲'], ['shiyuan', 'gloves', '噬渊·噬魂万手'], ['shiyuan', 'legs', '噬渊·无尽深渊裹腿'], ['shiyuan', 'back', '噬渊·归墟沉没披风'],
        ['', 'ring', '源罪·本相之瞳'], ['', 'offhand', '渊底·无声之钟'], ['', 'belt', '噬渊·深渊之口'], ['', 'shoes', '源罪·万罪加身'], ['', 'back', '渊心·无底囊'], ['', 'necklace', '寂灭·终末之钥'],
    ];
    $c = $pool[array_rand($pool)];
    $lv = max(1, (int) ($u['lv'] ?? 1));
    $sn = make_equip($uid, $c[1], $c[2], 4, $lv);
    if ($sn === '') {
        return '';
    }
    $tag = $c[0] === 'jimie' ? '寂灭套装' : ($c[0] === 'shiyuan' ? '噬渊套装' : '源罪渊传说');
    return '源罪本相崩解！掉落传说【' . $sn . '】（' . $tag . '）！';
}
// 【召唤师·魂契】BOSS掉落：专用副手深渊契匣 + 魂契套装 + 主题传说
function soulpact_kill_drop(int $uid, string $mid): string
{
    $uid = (int) $uid;
    if ($mid !== 'god_avatar' && $mid !== 'god_archon' && $mid !== 'gy_justice' && $mid !== 'yz_boss') {
        return '';
    }
    $rate = ['yz_boss' => 15, 'god_avatar' => 8, 'god_archon' => 4, 'gy_justice' => 10][$mid];
    if (mt_rand(1, 100) > $rate) {
        return '';
    }
    $u = user_by_id($uid);
    if (!$u) {
        return '';
    }
    $pool = [
        ['offhand', '召唤师·深渊契匣', '专用副手'],
        ['weapon', '魂契·万鬼招魂幡'], ['weapon', '召唤师·深渊引魂灯'],
        ['head', '魂契·亡者契约冠'], ['necklace', '魂契·幽魂缚魂链'], ['ring', '魂契·献祭者骨戒'],
        ['', 'offhand', '幽冥·招魂匣'], ['', 'back', '深渊·万鬼披风'], ['', 'ring', '亡者·空洞之瞳'], ['', 'belt', '献祭·逆生带'], ['', 'shoes', '游魂·踏虚靴'], ['', 'body', '蚀月·裹尸衣'],
    ];
    $c = $pool[array_rand($pool)];
    $sn = make_equip($uid, $c[0], $c[1], 4, max(1, (int) ($u['lv'] ?? 1)));
    if ($sn === '') {
        return '';
    }
    return '深渊低语回应！掉落传说【' . $sn . '】（' . ($c[2] ?? '魂契套装') . '）！';
}

// 罪渊击杀掉落：8%本职业罪技能书，0.3%随机罪渊套装件（传说）
function gy_kill_drop(int $uid, string $mid): string
{
    $uid = (int) $uid;
    $u = user_by_id($uid);
    if (!$u) {
        return '';
    }
    $out = '';
    // 罪技能书（本职业那本）
    if (mt_rand(1, 100) <= 8) {
        $book = ['warrior' => 'book_w9', 'mage' => 'book_m9', 'hunter' => 'book_h9', 'priest' => 'book_p9', 'summoner' => 'book_s9'][job_id_of($u)] ?? 'book_w9';
        $has = db()->prepare('SELECT skill FROM skills WHERE uid=? AND skill=?');
        $sk = ['warrior' => 'w9', 'mage' => 'm9', 'hunter' => 'h9', 'priest' => 'p9', 'summoner' => 's9'][job_id_of($u)] ?? 'w9';
        $has->execute([$uid, $sk]);
        if (!$has->fetch()) {
            add_mat($uid, $book, 1);
            $out .= '掉落【罪技能书·' . mat_name($book) . '】！';
        }
    }
    // 罪渊11件套（0.3%，传说）
    if (mt_rand(1, 1000) <= 3) {
        $pieces = [
            ['weapon', '罪渊·傲慢'], ['weapon', '罪渊·嫉妒'], ['weapon', '罪渊·暴怒'], ['weapon', '罪渊·懒惰'], ['weapon', '罪渊·贪婪'], ['weapon', '罪渊·暴食'], ['offhand', '罪渊·色欲'],
            ['head', '罪渊·悔恨冠'], ['body', '罪渊·赎罪甲'], ['legs', '罪渊·缚罪裤'], ['belt', '罪渊·忏悔束腰'], ['shoes', '罪渊·堕天靴'], ['gloves', '罪渊·噬罪手套'], ['back', '罪渊·堕羽披风'], ['ring', '罪渊·泣罪戒'], ['necklace', '罪渊·殉罪链'], ['offhand', '罪渊·赎罪灯'],
        ];
        $pc = $pieces[array_rand($pieces)];
        $sn = make_equip($uid, $pc[0], $pc[1], 4, max(1, (int) ($u['lv'] ?? 1)));
        if ($sn !== '') {
            $out .= '掉落传说【' . $sn . '】（罪渊套装，罪越高越强）！';
        }
    }
    return $out;
}

// ===== 好感度系统 =====
// 8个主要NPC：爱丽丝/薇拉/阿蛛/青芽/老咸鱼/老灰/虚空行者/磐石；好感>=100每周一发清罪丹邮件
function favor_npcs(): array
{
    return [
        'alice' => '修女·爱丽丝',
        'vera' => '守渊人·薇拉',
        'spider' => '断线人·阿蛛',
        'qingya' => '森语者·青芽',
        'oldfish' => '潜水人·老咸鱼',
        'oldash' => '探火人·老灰',
        'voidwalker' => '虚空行者',
        'rockwarden' => '岩狱守门人·磐石',
    ];
}

function favor_of(int $uid, string $npc): int
{
    $f = cflags((int) $uid);
    return max(0, (int) ($f['favor_' . $npc] ?? 0));
}

// 赠送：任意锻造材料/强化石/附魔石/药水类都可赠，每件+2；每天每NPC限20件
function favor_gift(int $uid, string $npc, string $mid, int $n): string
{
    $uid = (int) $uid;
    if (!isset(favor_npcs()[$npc])) {
        return '这个人不需要你的心意。';
    }
    $n = max(1, min(20, (int) $n));
    $mats = mats_of($uid);
    if ((int) ($mats[$mid] ?? 0) < $n) {
        return '你没有这么多【' . mat_name($mid) . '】。';
    }
    if (!is_tradable_mat($mid) && !isset(pct_potions()[$mid]) && !in_array($mid, ['hp_pct10', 'hp_pct20', 'hp_pct35', 'hp_pct50', 'mp_pct25', 'mp_pct50', 'tent', 'dragon_box'], true)) {
        return '【' . mat_name($mid) . '】人家看不上（只收锻造材料/药水/帐篷/龙箱）。';
    }
    $f = cflags($uid);
    $day = date('Ymd');
    $key = 'favor_day_' . $npc . '_' . $day;
    $used = (int) ($f[$key] ?? 0);
    if ($used + $n > 20) {
        return '今天' . favor_npcs()[$npc] . '已经收了' . $used . '件，再收就撑着了（每天20件）。';
    }
    add_mat($uid, $mid, -$n);
    $f['favor_' . $npc] = favor_of($uid, $npc) + $n * 2;
    $f[$key] = $used + $n;
    db()->prepare('UPDATE users SET chapter_flags=? WHERE id=?')->execute([json_encode($f, JSON_UNESCAPED_UNICODE), $uid]);
    return '赠送【' . mat_name($mid) . '】x' . $n . '，' . favor_npcs()[$npc] . '好感+' . ($n * 2) . '（当前' . $f['favor_' . $npc] . '）。';
}

// 清罪丹：使用一次降随机5~20罪值
function use_qingzui_dan(int $uid): string
{
    $uid = (int) $uid;
    $mats = mats_of($uid);
    if (empty($mats['qingzui_dan'])) {
        return '你没有清罪丹。';
    }
    $u = user_by_id($uid);
    if (!$u) {
        return '角色不存在。';
    }
    $down = mt_rand(5, 20);
    add_mat($uid, 'qingzui_dan', -1);
    $f = cflags($uid);
    $oldF = (float) ($f['sin_f'] ?? max(0, (int) ($f['sin'] ?? 0)));
    $new = max(0.0, $oldF - $down);
    $f['sin_f'] = $new;
    $f['sin'] = (int) floor($new + 1e-9);
    db()->prepare('UPDATE users SET chapter_flags=? WHERE id=?')->execute([json_encode($f, JSON_UNESCAPED_UNICODE), $uid]);
    $u = user_by_id($uid);
    sin_gain_array($u, 0);
    user_save($u);
    return '服下清罪丹，罪值-' . $down . '（当前' . sin_of($uid) . '）。';
}

// 每周一：好感>=100的NPC各发一封清罪丹邮件（每NPC每周一封）
function favor_weekly_tick(int $uid): string
{
    $uid = (int) $uid;
    $week = date('oW');
    $f = cflags($uid);
    $out = [];
    foreach (favor_npcs() as $npc => $nm) {
        if (favor_of($uid, $npc) < 100) {
            continue;
        }
        $key = 'favor_mail_' . $npc . '_' . $week;
        if (!empty($f[$key])) {
            continue;
        }
        if (send_mail($uid, $nm, 'favor', '故人牵挂', $nm . '念你近来杀气渐重，托人捎来一枚清罪丹，望君早日回头。', [['t' => 'mat', 'id' => 'qingzui_dan', 'n' => 1]])) {
            $f[$key] = 1;
            $out[] = $nm;
        }
    }
    if ($out !== []) {
        db()->prepare('UPDATE users SET chapter_flags=? WHERE id=?')->execute([json_encode($f, JSON_UNESCAPED_UNICODE), $uid]);
        return '【好感】' . implode('、', $out) . '给你寄来了清罪丹，去邮件领取。';
    }
    return '';
}

// ===== NPC偷袭 =====
// 8个可偷袭NPC：等级/属性与所在图相符；击败小概率掉专属传说
function ambush_npcs(): array
{
    return [
        'alice' => ['name' => '修女·爱丽丝', 'lv' => 30, 'hp' => 8000, 'atk' => 120, 'exp' => 3000, 'gold' => 1500, 'legend' => ['slot' => 'necklace', 'base' => '圣骸 rosary', 'rate' => 0.05, 'desc' => '传说项链：减伤+5%', 'extra' => ['k' => 'damage_reduction', 'v' => 5.0]]],
        'vera' => ['name' => '守渊人·薇拉', 'lv' => 110, 'hp' => 60000, 'atk' => 420, 'exp' => 12000, 'gold' => 4000, 'legend' => ['slot' => 'weapon', 'base' => '守渊之誓', 'rate' => 0.05, 'desc' => '传说主手：减伤+8%', 'extra' => ['k' => 'damage_reduction', 'v' => 8.0]]],
        'spider' => ['name' => '断线人·阿蛛', 'lv' => 380, 'hp' => 900000, 'atk' => 2800, 'exp' => 60000, 'gold' => 15000, 'legend' => ['slot' => 'weapon', 'base' => '断线剪', 'rate' => 0.05, 'desc' => '传说主手：吸血+5%', 'extra' => ['k' => 'lifesteal', 'v' => 5.0]]],
        'qingya' => ['name' => '森语者·青芽', 'lv' => 550, 'hp' => 1500000, 'atk' => 3800, 'exp' => 90000, 'gold' => 25000, 'legend' => ['slot' => 'body', 'base' => '青芽羽衣', 'rate' => 0.05, 'desc' => '传说上身：闪避+5%', 'extra' => ['k' => 'dodge', 'v' => 5.0]]],
        'oldfish' => ['name' => '潜水人·老咸鱼', 'lv' => 400, 'hp' => 1000000, 'atk' => 3000, 'exp' => 70000, 'gold' => 18000, 'legend' => ['slot' => 'offhand', 'base' => '深渊钓竿', 'rate' => 0.05, 'desc' => '传说副手：寻宝+10%', 'extra' => ['k' => 'magic_find', 'v' => 10.0]]],
        'oldash' => ['name' => '探火人·老灰', 'lv' => 600, 'hp' => 2000000, 'atk' => 4200, 'exp' => 110000, 'gold' => 30000, 'legend' => ['slot' => 'ring', 'base' => '灰烬之戒', 'rate' => 0.05, 'desc' => '传说戒指：技能伤害+5%', 'extra' => ['k' => 'skill_damage', 'v' => 5.0]]],
        'voidwalker' => ['name' => '虚空行者', 'lv' => 680, 'hp' => 2800000, 'atk' => 4800, 'exp' => 150000, 'gold' => 40000, 'legend' => ['slot' => 'weapon', 'base' => '虚空裂刃', 'rate' => 0.08, 'desc' => '传说主手：真实伤害+80', 'extra' => ['k' => 'truedmg', 'v' => 80.0]]],
        'rockwarden' => ['name' => '岩狱守门人·磐石', 'lv' => 800, 'hp' => 4000000, 'atk' => 5600, 'exp' => 200000, 'gold' => 50000, 'legend' => ['slot' => 'body', 'base' => '磐石岳铠', 'rate' => 0.08, 'desc' => '传说上身：减伤+12%', 'extra' => ['k' => 'damage_reduction', 'v' => 12.0]]],
    ];
}

// 偷袭一轮：返回[消息, 结果(npc_dead/player_dead/fight)]
function ambush_round(array &$u, array &$st): array
{
    $npc = (string) $st['npc'];
    $cfg = ambush_npcs()[$npc] ?? null;
    if ($cfg === null) {
        return ['这个人不在。', 'npc_dead'];
    }
    $pd = max(1, player_atk($u) + random_int(0, 5) - (int) ($cfg['lv'] * 0.6));
    $st['hp'] = max(0, (int) $st['hp'] - $pd);
    $log = '你偷袭' . $cfg['name'] . '，造成' . $pd . '点伤害。';
    if ((int) $st['hp'] <= 0) {
        return [$log . ' ' . $cfg['name'] . '倒地不起。', 'npc_dead'];
    }
    $md = max(1, (int) ($cfg['atk'] * 0.5) + random_int(0, 4) - player_def($u));
    $u['hp'] = (int) $u['hp'] - $md;
    $log .= '对方反击' . $md . '点。';
    if ((int) $u['hp'] <= 0) {
        return [$log, 'player_dead'];
    }
    return [$log, 'fight'];
}

// 偷袭奖励：经验金币 + 小概率专属传说
function ambush_reward(int $uid, string $npc): string
{
    $cfg = ambush_npcs()[$npc] ?? null;
    if ($cfg === null) {
        return '';
    }
    $u = user_by_id($uid);
    if (!$u) {
        return '';
    }
    $u['gold'] = (int) $u['gold'] + (int) $cfg['gold'];
    $msg = gain_exp($u, (int) $cfg['exp']);
    $u = user_by_id($uid);
    sin_gain_array($u, 0);
    user_save($u);
    $loot = '';
    if (mt_rand(1, 1000) <= (int) round($cfg['legend']['rate'] * 1000)) {
        $lg = $cfg['legend'];
        $sn = make_equip($uid, (string) $lg['slot'], (string) $lg['base'], 4, (int) $cfg['lv'], $lg);
        $loot = '掉落传说【' . $sn . '】（' . $lg['desc'] . '）！';
    }
    // 罪开战时已+10，这里不再加
    return '击败' . $cfg['name'] . '：' . $msg . '，金币+' . fmt_money((int) $cfg['gold']) . '。' . $loot;
}

// 好感/清罪丹入口（每周一由 home 页面触发）

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

function quest2_baseline(int $uid, int $q, bool $force = false): void
{
    $qs = ch2_quests()[$q] ?? null;
    if (!$qs || empty($qs['need'])) {
        return;
    }
    if (!$force) {
        $ex = cflags((int) $uid)['qb' . $q] ?? null;
        if (is_array($ex) && $ex !== []) {
            return;
        }
    }
    $c = kill_counts((int) $uid);
    $b = [];
    foreach ($qs['need'] as $mid => $need) {
        $b[$mid] = (int) ($c[$mid] ?? 0);
    }
    cflag_set((int) $uid, 'qb' . $q, $b);
}

function quest_stuck_fix(array &$u): void
{
    $cur = (int) ($u['quest'] ?? 0);
    $uid = (int) ($u['id'] ?? 0);
    if ($cur < 20 || ($cur > 37 && $cur < 50) || $cur > 80 || !empty(cflags($uid)['stuck_fixed'])) {
        return;
    }
    $all = $cur >= 77 ? rebirth2_quests() : ($cur >= 62 ? ch5_quests() : ($cur >= 50 ? ch4_quests() : ($cur >= 30 ? ch3_quests() : ch2_quests())));
    $qs = ($all[$cur] ?? null) + ['id' => $cur];
    if (!($all[$cur] ?? null) || empty($qs['need'])) {
        return;
    }
    $c = kill_counts($uid);
    $tot = 0;
    foreach ($qs['need'] as $mid => $need) {
        $tot += (int) ($c[$mid] ?? 0);
    }
    $needTot = array_sum($qs['need']);
    if ($tot >= $needTot * 3) {
        $b = [];
        foreach ($qs['need'] as $mid => $need) {
            $b[$mid] = max(0, (int) ($c[$mid] ?? 0) - $need);
        }
        cflag_set($uid, 'qb' . $cur, $b);
        cflag_set($uid, 'stuck_fixed', 1);
        $u = user_by_id($uid);
    }
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
