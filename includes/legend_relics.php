<?php
declare(strict_types=1);

// ============================================================
// 遗神铸庭·传说relics（行为型专属传说，第二弹）
// - 与 summoner_legend 同一套思路：靠 lg_* 专属词条判定是否穿戴
// - 每件有：部位 / 需求等级 / 效果词条id / 主参数 / 说明 / 掉落BOSS
// - 战斗钩子集中在 legend_relic_* 系列，battle_round / gear_stats 等处调用
// - 数值已按"平衡"做了上下调和（说明里标注了最终生效值）
// ============================================================

function legend_relics(): array
{
    // name => [slot, lv, key, param, job('' 不限), intro]
    return [
        '不朽' => ['slot' => 'weapon', 'lv' => 440, 'key' => 'lg_undying', 'param' => 10, 'job' => 'warrior',
            'intro' => '技能伤害额外附加自身物防的10%；每次触发反击，该附加提升10%（最多5层，战斗结束清零）。'],
        '叹息之墙' => ['slot' => 'offhand', 'lv' => 725, 'key' => 'lg_sighwall', 'param' => 20, 'job' => '',
            'intro' => '受到伤害降低20%，减免额被储存；你发起反击时把储存的伤害以2倍一次性引爆。'],
        '泰坦之肤' => ['slot' => 'body', 'lv' => 430, 'key' => 'lg_taitan', 'param' => 15, 'job' => '',
            'intro' => '物防、法防各+15%；每次受到伤害，反击伤害+5%（最多10层，战斗结束清零）。'],
        '磐石' => ['slot' => 'head', 'lv' => 335, 'key' => 'lg_rock', 'param' => 20, 'job' => '',
            'intro' => '当自身物防高于敌人物防时，受到的所有伤害-20%。'],
        '荆棘之心' => ['slot' => 'necklace', 'lv' => 250, 'key' => 'lg_bramble', 'param' => 30, 'job' => '',
            'intro' => '反击伤害+30%。'],
        '年轮' => ['slot' => 'ring', 'lv' => 255, 'key' => 'lg_years', 'param' => 20, 'job' => '',
            'intro' => '每次触发反击，恢复该次反击伤害20%的生命。'],
        '血怒' => ['slot' => 'weapon', 'lv' => 605, 'key' => 'lg_bloodrage', 'param' => 8, 'job' => 'warrior',
            'intro' => '生命每损失10%，攻击+8%；生命低于30%时攻击无视目标20%防御。'],
        '狂乱之影' => ['slot' => 'weapon', 'lv' => 610, 'key' => 'lg_frenzy', 'param' => 30, 'job' => 'warrior',
            'intro' => '生命越低攻速越快：每损失10%生命，额外出手概率+30%（据此近似"攻速"）。'],
        '涅槃' => ['slot' => 'back', 'lv' => 970, 'key' => 'lg_nirvana', 'param' => 2, 'job' => '',
            'intro' => '受到致命伤害时保留1点生命并狂暴2回合：物攻翻倍、无法被治疗。每场战斗仅一次。'],
        '死斗者' => ['slot' => 'head', 'lv' => 975, 'key' => 'lg_deathmatch', 'param' => 50, 'job' => '',
            'intro' => '暴击伤害+50%，但自身受到的一切伤害+20%。'],
        '不死鸟之眼' => ['slot' => 'necklace', 'lv' => 980, 'key' => 'lg_phoenix', 'param' => 10, 'job' => '',
            'intro' => '每次暴击，恢复自身最大生命10%。'],
        '绝境' => ['slot' => 'ring', 'lv' => 615, 'key' => 'lg_laststand', 'param' => 20, 'job' => '',
            'intro' => '生命低于20%时，暴击率+20%、闪避率+20%。'],
        '杀戮骨戒' => ['slot' => 'ring', 'lv' => 195, 'key' => 'lg_killbone', 'param' => 6, 'job' => '',
            'intro' => '每击杀一个敌人，物攻+6%（最多50层，战斗结束清零）。'],
        '誓言' => ['slot' => 'belt', 'lv' => 340, 'key' => 'lg_vow', 'param' => 200, 'job' => '',
            'intro' => '生命低于20%时，物防与法防+200%。'],
        '彼岸' => ['slot' => 'belt', 'lv' => 345, 'key' => 'lg_othershore', 'param' => 5, 'job' => '',
            'intro' => '召唤物生命与伤害×5；但任意召唤物阵亡时，玩家当场死亡。'],
        '影牙' => ['slot' => 'weapon', 'lv' => 515, 'key' => 'lg_shadowfang', 'param' => 15, 'job' => 'hunter',
            'intro' => '攻击有额外5%（可叠）概率造成15%物攻的真实伤害；若触发，该次攻击必定暴击。'],
        '夜幕' => ['slot' => 'body', 'lv' => 435, 'key' => 'lg_nightfall', 'param' => 5, 'job' => '',
            'intro' => '闪避率+5%；每次成功闪避后，下一次攻击完全转为真实伤害。'],
        '无面者' => ['slot' => 'head', 'lv' => 520, 'key' => 'lg_faceless', 'param' => 5, 'job' => '',
            'intro' => '每1%闪避率额外增加5%暴击伤害（上限+250%）。'], // 原设10%过强，调为5%并封顶
        '死神之吻' => ['slot' => 'ring', 'lv' => 720, 'key' => 'lg_reaperkiss', 'param' => 2, 'job' => '',
            'intro' => '暴击伤害翻倍。'],
        '破甲之环' => ['slot' => 'ring', 'lv' => 525, 'key' => 'lg_sunder', 'param' => 15, 'job' => '',
            'intro' => '攻击有额外5%（可叠）概率造成15%物攻的真实伤害。'],
        '无影之戒' => ['slot' => 'ring', 'lv' => 715, 'key' => 'lg_noshadow', 'param' => 50, 'job' => '',
            'intro' => '生命低于1%时，闪避率+50%。'],
        '焦炭' => ['slot' => 'weapon', 'lv' => 615, 'key' => 'lg_charcoal', 'param' => 30, 'job' => '',
            'intro' => '攻击有30%概率造成额外灼烧（约+30%伤害/有火属性目标更痛）；重复触发则引爆并刷新。'],

        // ===== 第二接口：40 件转职专属传说（用 eff 描述，通用引擎结算）=====
        // —— 狂战士（低血高伤/狂暴）——
        '怒血' => ['slot' => 'weapon', 'lv' => 600, 'key' => 'lg_rageblood', 'job' => 'warrior', 'intro' => '生命低于50%时，伤害+15%。', 'eff' => ['lowhp_dmg' => [0.5, 15]]],
        '断骨' => ['slot' => 'gloves', 'lv' => 600, 'key' => 'lg_bonebreak', 'job' => 'warrior', 'intro' => '暴击伤害+45%；生命低于40%时暴击伤害再+20%。', 'eff' => ['critdmg' => 45, 'lowhp_crit' => [0.4, 20]]],
        '犄角' => ['slot' => 'head', 'lv' => 500, 'key' => 'lg_horn', 'job' => 'warrior', 'intro' => '生命每损失10%，暴击率+3%（最多+21%）。', 'eff' => ['perlost_crit' => 3]],
        '狂宴' => ['slot' => 'belt', 'lv' => 500, 'key' => 'lg_feast', 'job' => 'warrior', 'intro' => '每击杀一个敌人，回复最大生命8%。', 'eff' => ['kill_heal' => 8]],
        // —— 铁卫（减伤/反击/护盾）——
        '壁垒' => ['slot' => 'body', 'lv' => 520, 'key' => 'lg_bulwark', 'job' => 'warrior', 'intro' => '受到伤害-8%。', 'eff' => ['dr' => 8]],
        '铁棘' => ['slot' => 'back', 'lv' => 500, 'key' => 'lg_ironthorn', 'job' => 'warrior', 'intro' => '反击伤害+25%。', 'eff' => ['counter' => 25]],
        '锚定' => ['slot' => 'legs', 'lv' => 500, 'key' => 'lg_anchor', 'job' => 'warrior', 'intro' => '物防+250，反击伤害+15%。', 'eff' => ['def' => 250, 'counter' => 15]],
        '磐心' => ['slot' => 'necklace', 'lv' => 600, 'key' => 'lg_stoneheart', 'job' => 'warrior', 'intro' => '生命低于50%时，受到伤害-15%。', 'eff' => ['lowhp_reduce' => [0.5, 15]]],
        // —— 凛冬法师（冰雷/麻痹）——
        '极寒' => ['slot' => 'weapon', 'lv' => 600, 'key' => 'lg_extreme', 'job' => 'mage', 'intro' => '冰属性伤害+25%。', 'eff' => ['el' => ['ice' => 25]]],
        '霜噬' => ['slot' => 'head', 'lv' => 500, 'key' => 'lg_frostbite', 'job' => 'mage', 'intro' => '雷属性伤害+25%。', 'eff' => ['el' => ['thunder' => 25]]],
        '静滞' => ['slot' => 'ring', 'lv' => 500, 'key' => 'lg_stasis', 'job' => 'mage', 'intro' => '攻击有12%概率麻痹目标1回合。', 'eff' => ['stun' => 12]],
        '凛魂' => ['slot' => 'body', 'lv' => 700, 'key' => 'lg_frostsoul', 'job' => 'mage', 'intro' => '冰、雷属性伤害各+12%，法攻+300。', 'eff' => ['el' => ['ice' => 12, 'thunder' => 12], 'matk' => 300]],
        // —— 燎原使者（火风/群攻）——
        '燎原' => ['slot' => 'weapon', 'lv' => 600, 'key' => 'lg_blaze', 'job' => 'mage', 'intro' => '火属性伤害+25%。', 'eff' => ['el' => ['fire' => 25]]],
        '焚风' => ['slot' => 'head', 'lv' => 500, 'key' => 'lg_emberwind', 'job' => 'mage', 'intro' => '风属性伤害+25%。', 'eff' => ['el' => ['wind' => 25]]],
        '余烬' => ['slot' => 'ring', 'lv' => 500, 'key' => 'lg_ember', 'job' => 'mage', 'intro' => '火属性伤害+10%，技能伤害+15%。', 'eff' => ['el' => ['fire' => 10], 'skill_damage' => 15]],
        '炽雨' => ['slot' => 'body', 'lv' => 700, 'key' => 'lg_firerain', 'job' => 'mage', 'intro' => '火、风属性伤害各+12%，法攻+300。', 'eff' => ['el' => ['fire' => 12, 'wind' => 12], 'matk' => 300]],
        // —— 游侠（持续伤害/铺层）——
        '毒潮' => ['slot' => 'weapon', 'lv' => 600, 'key' => 'lg_poisontide', 'job' => 'hunter', 'intro' => '所有持续伤害(DOT)+30%。', 'eff' => ['dot' => 30]],
        '腐蚀' => ['slot' => 'ring', 'lv' => 500, 'key' => 'lg_corrode', 'job' => 'hunter', 'intro' => '所有持续伤害(DOT)+15%。', 'eff' => ['dot' => 15]],
        '猎印' => ['slot' => 'necklace', 'lv' => 500, 'key' => 'lg_huntmark', 'job' => 'hunter', 'intro' => '暴击+6%。', 'eff' => ['crit' => 6]],
        '蔓延' => ['slot' => 'head', 'lv' => 600, 'key' => 'lg_spread', 'job' => 'hunter', 'intro' => '所有持续伤害(DOT)+20%。', 'eff' => ['dot' => 20]],
        // —— 影刺客（闪避/单体爆发）——
        '影匿' => ['slot' => 'body', 'lv' => 500, 'key' => 'lg_shadowhide', 'job' => 'hunter', 'intro' => '闪避率+6%。', 'eff' => ['dodge' => 6]],
        '断罪' => ['slot' => 'weapon', 'lv' => 600, 'key' => 'lg_condemn', 'job' => 'hunter', 'intro' => '对单一目标造成的伤害+25%。', 'eff' => ['single' => 25]],
        '无面' => ['slot' => 'head', 'lv' => 500, 'key' => 'lg_maskless', 'job' => 'hunter', 'intro' => '暴击伤害+60%，闪避率+4%。', 'eff' => ['critdmg' => 60, 'dodge' => 4]],
        '死寂' => ['slot' => 'ring', 'lv' => 600, 'key' => 'lg_deadsilence', 'job' => 'hunter', 'intro' => '生命低于30%时，暴击率+15%、闪避率+15%。', 'eff' => ['lowhp_crit' => [0.3, 15], 'lowhp_dodge' => [0.3, 15]]],
        // —— 暗影主教（吸血/暗伤）——
        '血偿' => ['slot' => 'weapon', 'lv' => 500, 'key' => 'lg_bloodpay', 'job' => 'priest', 'intro' => '吸血+5%。', 'eff' => ['lifesteal' => 5]],
        '鞭笞' => ['slot' => 'gloves', 'lv' => 500, 'key' => 'lg_scourge', 'job' => 'priest', 'intro' => '吸血+3%，暴击+4%。', 'eff' => ['lifesteal' => 3, 'crit' => 4]],
        '暗誓' => ['slot' => 'necklace', 'lv' => 600, 'key' => 'lg_darkvow', 'job' => 'priest', 'intro' => '暗属性伤害+20%。', 'eff' => ['el' => ['dark' => 20]]],
        '汲魂' => ['slot' => 'ring', 'lv' => 700, 'key' => 'lg_souldrain', 'job' => 'priest', 'intro' => '每次暴击，回复最大生命4%。', 'eff' => ['oncrit_heal' => 4]],
        // —— 神圣主教（治疗/圣光/减伤）——
        '圣光' => ['slot' => 'weapon', 'lv' => 500, 'key' => 'lg_holy', 'job' => 'priest', 'intro' => '每次行动回复最大生命1%。', 'eff' => ['regen' => 1]],
        '慈航' => ['slot' => 'body', 'lv' => 600, 'key' => 'lg_mercy', 'job' => 'priest', 'intro' => '每次行动回复最大生命2%。', 'eff' => ['regen' => 2]],
        '庇护' => ['slot' => 'necklace', 'lv' => 600, 'key' => 'lg_shelter', 'job' => 'priest', 'intro' => '受到伤害-6%。', 'eff' => ['dr' => 6]],
        '赎罪' => ['slot' => 'ring', 'lv' => 700, 'key' => 'lg_atone', 'job' => 'priest', 'intro' => '每次暴击，回复最大生命6%。', 'eff' => ['oncrit_heal' => 6]],
        // —— 死灵之拥（召唤军团/爆炸）——
        '尸潮' => ['slot' => 'weapon', 'lv' => 500, 'key' => 'lg_corpsetide', 'job' => 'summoner', 'intro' => '召唤数量上限+1。', 'eff' => ['summon_cap' => 1]],
        '亡语' => ['slot' => 'ring', 'lv' => 600, 'key' => 'lg_deathword', 'job' => 'summoner', 'intro' => '召唤物伤害+30%。', 'eff' => ['summon_power' => 30]],
        '骨爆' => ['slot' => 'head', 'lv' => 700, 'key' => 'lg_boneburst', 'job' => 'summoner', 'intro' => '召唤物伤害+20%，召唤物生命+20%。', 'eff' => ['summon_power' => 20, 'summon_hp' => 20]],
        '冥约' => ['slot' => 'necklace', 'lv' => 500, 'key' => 'lg_netherpact', 'job' => 'summoner', 'intro' => '召唤物生命+40%。', 'eff' => ['summon_hp' => 40]],
        // —— 痛苦盛宴（献祭/腐蚀）——
        '噬魂' => ['slot' => 'weapon', 'lv' => 700, 'key' => 'lg_souleat', 'job' => 'summoner', 'intro' => '召唤物伤害+40%。', 'eff' => ['summon_power' => 40]],
        '血宴' => ['slot' => 'ring', 'lv' => 600, 'key' => 'lg_bloodfeast', 'job' => 'summoner', 'intro' => '召唤物伤害+20%，吸血+3%。', 'eff' => ['summon_power' => 20, 'lifesteal' => 3]],
        '瘴疠' => ['slot' => 'body', 'lv' => 600, 'key' => 'lg_miasma', 'job' => 'summoner', 'intro' => '所有持续伤害(DOT)+25%。', 'eff' => ['dot' => 25]],
        '永夜' => ['slot' => 'belt', 'lv' => 700, 'key' => 'lg_endlessnight', 'job' => 'summoner', 'intro' => '召唤数量上限+1，召唤物伤害+15%。', 'eff' => ['summon_cap' => 1, 'summon_power' => 15]],
    ];
}

// BOSS 专属掉落传说·“行为型”传说特效（与遗物同一套引擎：靠 lg_* 词条判定穿戴，
// 战斗钩子已统一处理 eff 字段；少数独特机制在对应钩子里按 key 特判）。
// key => ['intro' => 说明, 'eff' => {与遗物相同的聚合字段}]
function boss_legendary_effects(): array
{
    return [
        'lg_bl_thunder' => [
            'intro' => '雷属性伤害+25%；暴击时汲取风暴之力，回复6%最大生命。',
            'eff' => ['el' => ['thunder' => 25], 'oncrit_heal' => 6],
        ],
        'lg_bl_wind' => [
            'intro' => '闪避+10%；生命低于40%时，再额外闪避+20%。',
            'eff' => ['dodge' => 10, 'lowhp_dodge' => [0.4, 20]],
        ],
        'lg_bl_sky' => [
            'intro' => '对生命低于30%的目标，造成伤害额外+40%（斩尽残穹）。',
            'eff' => [],
        ],
        'lg_bl_siren' => [
            'intro' => '受到伤害时有30%概率以溺歌夺魄，令敌人下一回合无法行动。',
            'eff' => [],
        ],
        'lg_bl_whale' => [
            'intro' => '吸血+4%；每击杀一个敌人，回复10%最大生命（吞海）。',
            'eff' => ['lifesteal' => 4, 'kill_heal' => 10],
        ],
    ];
}

// 掉落表：BOSS mid => [relic名...]
function legend_relic_drops(): array
{
    return [
        'sky_sovereign' => ['杀戮骨戒'],
        'forge_king' => ['荆棘之心'],
        'forge_titan' => ['年轮'],
        'verd_matriarch' => ['磐石'],
        'verd_stag' => ['誓言'],
        'verd_hollow' => ['彼岸'],
        'crypt_lord' => ['泰坦之肤'],
        'crypt_queen' => ['夜幕'],
        'crypt_lichking' => ['不朽'],
        'star_devourer' => ['影牙'],
        'star_oracle' => ['无面者'],
        'star_titan' => ['破甲之环'],
        'magma_behemoth' => ['血怒'],
        'magma_lord' => ['狂乱之影'],
        'magma_leviathan' => ['绝境', '焦炭'],
        'chrono_warden' => ['无影之戒'],
        'chrono_tyrant' => ['死神之吻'],
        'chrono_paradox' => ['叹息之墙'],
        'divine_archon' => ['涅槃'],
        'divine_judge' => ['死斗者'],
        'divine_ruin' => ['不死鸟之眼'],
    ];
}

// 第二接口 40 件（转职传说）的掉落表
function legend_relic_drops2(): array
{
    return [
        'magma_behemoth' => ['怒血', '极寒', '蔓延', '暗誓', '慈航', '亡语', '瘴疠'],
        'magma_lord' => ['断骨', '燎原', '断罪', '庇护', '血宴'],
        'magma_leviathan' => ['磐心', '毒潮', '死寂'],
        'star_devourer' => ['狂宴', '铁棘', '锚定', '焚风', '腐蚀', '血偿', '冥约'],
        'star_oracle' => ['犄角', '霜噬', '余烬', '影匿'],
        'star_titan' => ['壁垒', '静滞', '猎印', '无面', '圣光', '鞭笞', '尸潮'],
        'chrono_warden' => ['凛魂'],
        'chrono_tyrant' => ['炽雨', '汲魂', '赎罪', '骨爆', '永夜'],
        'chrono_paradox' => ['噬魂'],
    ];
}

// eff 索引：key => eff 数组（仅第二接口的 40 件带 eff）
function legend_relic_eff_index(): array
{
    static $idx = null;
    if ($idx !== null) {
        return $idx;
    }
    $idx = [];
    foreach (legend_relics() as $cfg) {
        if (isset($cfg['eff']) && is_array($cfg['eff'])) {
            $idx[(string) $cfg['key']] = $cfg['eff'];
        }
    }
    // BOSS 专属传说（行为型）也并入同一套 eff 聚合
    if (function_exists('boss_legendary_effects')) {
        foreach (boss_legendary_effects() as $key => $cfg) {
            if (isset($cfg['eff']) && is_array($cfg['eff']) && $cfg['eff'] !== []) {
                $idx[(string) $key] = $cfg['eff'];
            }
        }
    }
    return $idx;
}

// 当前穿戴的 eff 列表
function legend_relic_effs(int $uid): array
{
    static $cache = [];
    $gen = (int) ($GLOBALS['_legend_gen'] ?? 0);
    $ck = (int) $uid . ':' . $gen;
    if (isset($cache[$ck])) {
        return $cache[$ck];
    }
    $idx = legend_relic_eff_index();
    $out = [];
    foreach (legend_relic_worn($uid) as $k => $v) {
        if (isset($idx[$k])) {
            $out[] = $idx[$k];
        }
    }
    $cache[$ck] = $out;
    return $out;
}

// 求和某数值字段
function legend_relic_eff_sum(int $uid, string $field): float
{
    $t = 0.0;
    foreach (legend_relic_effs($uid) as $e) {
        if (isset($e[$field])) {
            $t += (float) $e[$field];
        }
    }
    return $t;
}

// 求和某元素伤害%
function legend_relic_eff_el(int $uid, string $el): float
{
    $t = 0.0;
    foreach (legend_relic_effs($uid) as $e) {
        if (isset($e['el'][$el])) {
            $t += (float) $e['el'][$el];
        }
    }
    return $t;
}

function legend_relic_by_name(string $name): ?array
{
    return legend_relics()[equip_shortname($name)] ?? null;
}

function legend_relic_intro_by_key(string $key): string
{
    foreach (legend_relics() as $cfg) {
        if ((string) $cfg['key'] === $key) {
            return (string) $cfg['intro'];
        }
    }
    if (function_exists('boss_legendary_effects')) {
        $boss = boss_legendary_effects();
        if (isset($boss[$key])) {
            return (string) $boss[$key]['intro'];
        }
    }
    return '';
}

// 读取玩家穿戴中的 lg_* 词条：key => 参数值
function legend_relic_worn(int $uid): array
{
    static $cache = [];
    $gen = (int) ($GLOBALS['_legend_gen'] ?? 0);
    $ck = (int) $uid . ':' . $gen;
    if (isset($cache[$ck])) {
        return $cache[$ck];
    }
    $out = [];
    $st = db()->prepare('SELECT affixes FROM equips WHERE uid=? AND pos="wear"');
    $st->execute([(int) $uid]);
    while ($row = $st->fetch()) {
        $aff = json_decode((string) $row['affixes'], true);
        if (!is_array($aff)) {
            continue;
        }
        foreach ($aff as $a) {
            $id = (string) ($a['id'] ?? $a['k'] ?? '');
            if (str_starts_with($id, 'lg_')) {
                $out[$id] = (float) ($a['v'] ?? 0);
            }
        }
    }
    $cache[$ck] = $out;
    return $out;
}

// 掉落
function legend_relic_kill_drop(int $uid, string $mid): string
{
    $names = array_merge(legend_relic_drops()[$mid] ?? [], legend_relic_drops2()[$mid] ?? []);
    if ($names === []) {
        return '';
    }
    if (mt_rand(1, 100) > 3) { // 3% 概率
        return '';
    }
    $name = $names[array_rand($names)];
    $cfg = legend_relics()[$name] ?? null;
    if ($cfg === null) {
        return '';
    }
    $sn = make_equip((int) $uid, (string) $cfg['slot'], $name, 4, (int) $cfg['lv'], ['extra' => ['k' => (string) $cfg['key'], 'v' => (float) ($cfg['param'] ?? 1)]]);
    if ($sn === '') {
        return '';
    }
    return '遗神铸庭的传说遗物【' . $sn . '】现世！';
}

// ============ 战斗钩子 ============

// 装备面板类（无条件）加成：并入 gear_stats，返回前调用
function legend_relic_gear_apply(int $uid, array &$s): void
{
    $w = legend_relic_worn($uid);
    if ($w === []) {
        return;
    }
    // 夜幕：闪避+5
    if (isset($w['lg_nightfall'])) {
        $s['dodge'] += $w['lg_nightfall'];
    }
    // 无面者：每1%闪避 + critdmg（默认5%，封顶250）
    if (isset($w['lg_faceless'])) {
        $s['critdmg'] += min(250.0, $s['dodge'] * max(1.0, $w['lg_faceless']));
    }
    // 死斗者：暴伤+50
    if (isset($w['lg_deathmatch'])) {
        $s['critdmg'] += $w['lg_deathmatch'];
    }
    // 死神之吻：暴伤翻倍（在其它暴伤之后）
    if (isset($w['lg_reaperkiss']) && $s['critdmg'] > 0) {
        $s['critdmg'] *= max(1.0, $w['lg_reaperkiss']);
    }
    // 第二接口 eff：面板数值（hp/atk/matk/def/mdef/crit/critdmg/dodge/lifesteal/skill_damage/allres/element/truedmg/dr）
    $effMap = ['hp' => 'hp', 'atk' => 'atk', 'matk' => 'matk', 'def' => 'def', 'mdef' => 'mdef', 'crit' => 'crit', 'critdmg' => 'critdmg', 'dodge' => 'dodge', 'lifesteal' => 'lifesteal', 'skill_damage' => 'skill_damage', 'allres' => 'allres', 'element' => 'element', 'truedmg' => 'truedmg'];
    foreach (legend_relic_effs($uid) as $e) {
        foreach ($effMap as $fk => $sk) {
            if (isset($e[$fk])) {
                $s[$sk] += (float) $e[$fk];
            }
        }
        if (isset($e['dr'])) {
            $s['damage_reduction'] += (float) $e['dr'];
        }
    }
}

// 防御倍率（誓言 / 泰坦之肤 / 第二接口 def_pct）——用于 player_pdef / player_mdef
function legend_relic_def_mult(array $u): float
{
    $uid = (int) ($u['id'] ?? 0);
    $w = legend_relic_worn($uid);
    $m = 1.0;
    if (isset($w['lg_taitan'])) {
        $m *= 1 + $w['lg_taitan'] / 100;
    }
    if (isset($w['lg_vow']) && (int) $u['hp'] < (int) $u['maxhp'] * 0.2) {
        $m *= 1 + $w['lg_vow'] / 100;
    }
    $m *= 1 + legend_relic_eff_sum($uid, 'def_pct') / 100;
    return $m;
}

// 攻击倍率（血怒 low-hp atk% + 第二接口 atk_pct）——用于 player_patk
function legend_relic_atk_mult(array $u): float
{
    $uid = (int) ($u['id'] ?? 0);
    $m = 1.0;
    $w = legend_relic_worn($uid);
    if (isset($w['lg_bloodrage']) && (int) $u['maxhp'] > 0) {
        $lost = 1 - (int) $u['hp'] / max(1, (int) $u['maxhp']);
        $steps = min(10, (int) floor(max(0.0, $lost) * 10));
        $m *= 1 + $steps * $w['lg_bloodrage'] / 100;
    }
    $m *= 1 + legend_relic_eff_sum($uid, 'atk_pct') / 100;
    return $m;
}

// 额外闪避（绝境 / 无影 / 第二接口 lowhp_dodge）——dmg_to_player 里叠加
function legend_relic_bonus_dodge(array $u): float
{
    $uid = (int) ($u['id'] ?? 0);
    $w = legend_relic_worn($uid);
    $add = 0.0;
    if (isset($w['lg_laststand']) && (int) $u['hp'] < (int) $u['maxhp'] * 0.2) {
        $add += $w['lg_laststand'];
    }
    if (isset($w['lg_noshadow']) && (int) $u['hp'] < (int) $u['maxhp'] * 0.01) {
        $add += $w['lg_noshadow'];
    }
    if ((int) $u['maxhp'] > 0) {
        foreach (legend_relic_effs($uid) as $e) {
            if (isset($e['lowhp_dodge']) && (int) $u['hp'] < (int) $u['maxhp'] * (float) $e['lowhp_dodge'][0]) {
                $add += (float) $e['lowhp_dodge'][1];
            }
        }
    }
    return $add;
}

// 出手前调整：返回 [pd, critAdd(暴击率%), forceCrit, trueProcPct, log]
function legend_relic_outgoing(array &$u, array &$b, int $pd, bool $isSkill, string &$log): array
{
    $uid = (int) ($u['id'] ?? 0);
    $w = legend_relic_worn($uid);
    if ($w === []) {
        return [$pd, 0.0, false, 0.0, ''];
    }
    $critAdd = 0.0;
    $force = false;
    $truePct = 0.0;
    $mPdef = monster_pdef($b);

    // 血怒：残血无视20%防御（近似：把被扣掉的20%物防补回来）
    if (isset($w['lg_bloodrage']) && (int) $u['hp'] < (int) $u['maxhp'] * 0.3) {
        $pd += (int) ($mPdef * 0.2);
    }
    // 狂乱之影：残血额外出手（近似成命中回数）——由调用处处理双打，这里只加暴击/伤害不动
    // 绝境：残血暴击+20
    if (isset($w['lg_laststand']) && (int) $u['hp'] < (int) $u['maxhp'] * 0.2) {
        $critAdd += $w['lg_laststand'];
    }
    // 杀戮骨戒：叠层加伤
    if (isset($w['lg_killbone'])) {
        $kb = (int) ($b['lg_kbone'] ?? 0);
        if ($kb > 0) {
            $pd = (int) round($pd * (1 + $kb * $w['lg_killbone'] / 100));
        }
    }
    // 不朽：技能额外附体物防%的真实伤害（叠层在反击里加）
    if ($isSkill && isset($w['lg_undying'])) {
        $und = (int) ($b['lg_undying'] ?? 0);
        $extra = (int) (player_pdef($u) * $w['lg_undying'] / 100 * (1 + $und * 0.1));
        if ($extra > 0) {
            $pd += $extra;
            $log .= '(不朽+' . $extra . ')';
        }
    }
    // 影牙 / 破甲之环：proc 真实伤害（概率可叠），触发即必定暴击
    $procChance = 0;
    if (isset($w['lg_shadowfang'])) { $procChance += 5; }
    if (isset($w['lg_sunder'])) { $procChance += 5; }
    if ($procChance > 0 && mt_rand(1, 100) <= $procChance) {
        $truePct = isset($w['lg_shadowfang']) ? $w['lg_shadowfang'] : $w['lg_sunder'];
        $force = true;
        $log .= '(破甲·真伤)';
    }
    // 夜幕：上次闪避后，这一击全力真伤（近似无视防御）
    if (!empty($b['lg_nf_ready'])) {
        $pd += $mPdef;
        $b['lg_nf_ready'] = 0;
        $log .= '(夜幕·真伤)';
    }
    // 焦炭：30% 灼烧/引爆（近似为一次性火伤爆发）
    if (isset($w['lg_charcoal']) && mt_rand(1, 100) <= (int) $w['lg_charcoal']) {
        $burn = (int) ($b['lg_burn'] ?? 0);
        $bonus = $burn > 0 ? (int) ($pd * 0.8) : (int) ($pd * 0.3);
        $pd += max(1, $bonus);
        $b['lg_burn'] = $burn > 0 ? 0 : $burn + 1;
        $log .= $burn > 0 ? '(焦炭·引爆)' : '(焦炭·灼烧)';
    }
    // ===== 第二接口 eff =====
    $effs = legend_relic_effs($uid);
    if ($effs !== []) {
        $maxhp = max(1, (int) $u['maxhp']);
        $hp = (int) $u['hp'];
        $lostFrac = max(0.0, 1 - $hp / $maxhp);
        $isSingle = (int) ($b['left'] ?? 1) <= 1;
        foreach ($effs as $e) {
            // 每次行动回血（神圣主教）
            if (isset($e['regen'])) {
                $heal = (int) ($maxhp * (float) $e['regen'] / 100);
                if ($heal > 0 && $hp < $maxhp) {
                    $u['hp'] = min($maxhp, (int) $u['hp'] + $heal);
                    $log .= '(圣光+' . $heal . ')';
                }
            }
            // 残血增伤（狂战士）
            if (isset($e['lowhp_dmg']) && $hp < $maxhp * (float) $e['lowhp_dmg'][0]) {
                $pd = (int) round($pd * (1 + (float) $e['lowhp_dmg'][1] / 100));
            }
            // 单体增伤（影刺客）
            if (isset($e['single']) && $isSingle) {
                $pd = (int) round($pd * (1 + (float) $e['single'] / 100));
            }
            // 残血暴击（狂战士/影刺客）
            if (isset($e['lowhp_crit']) && $hp < $maxhp * (float) $e['lowhp_crit'][0]) {
                $critAdd += (float) $e['lowhp_crit'][1];
            }
            // 每损失10%生命暴击（狂战士）
            if (isset($e['perlost_crit'])) {
                $steps = (int) floor($lostFrac * 10);
                $critAdd += min(7, $steps) * (float) $e['perlost_crit'];
            }
            // 麻痹触发（凛冬）
            if (isset($e['stun']) && mt_rand(1, 100) <= (int) $e['stun']) {
                $b['stun'] = max((int) ($b['stun'] ?? 0), 1);
                $log .= '(麻痹)';
            }
        }
    }
    // BOSS传说·苍穹之主【斩尽残穹】：对残血目标增伤
    if (isset($w['lg_bl_sky']) && (int) ($b['maxhp'] ?? 0) > 0 && (int) ($b['hp'] ?? 0) < (int) $b['maxhp'] * 0.3) {
        $pd = (int) round($pd * 1.4);
        $log .= '(斩尽残穹)';
    }
    return [$pd, $critAdd, $force, $truePct, $log];
}

// 命中后：真实附加伤害、暴击回血(不死鸟)
function legend_relic_on_hit(array &$u, array &$b, int $pd, bool $isCrit, float $truePct, string &$log): void
{
    $w = legend_relic_worn((int) ($u['id'] ?? 0));
    if ($w === []) {
        return;
    }
    // 影牙/破甲：真伤 = 物攻%
    if ($truePct > 0 && $pd > 0) {
        $td = max(1, (int) (player_patk($u) * $truePct / 100));
        $b['hp'] = (int) $b['hp'] - $td;
        $log .= '(真伤' . $td . ')';
    }
    // 不死鸟之眼：暴击回血
    if ($isCrit && isset($w['lg_phoenix'])) {
        $heal = max(1, (int) ((int) $u['maxhp'] * $w['lg_phoenix'] / 100));
        $u['hp'] = min((int) $u['maxhp'], (int) $u['hp'] + $heal);
        $log .= '(不死鸟+' . $heal . ')';
    }
    // 狂乱之影：残血"攻速"→ 额外出手（近似为追加一次60%伤害的攻击）
    if (isset($w['lg_frenzy']) && (int) $u['maxhp'] > 0) {
        $lost = 1 - (int) $u['hp'] / max(1, (int) $u['maxhp']);
        $chance = min(60.0, max(0.0, $lost) * $w['lg_frenzy']);
        if ($chance > 0 && mt_rand(1, 100) <= (int) $chance) {
            $extra = max(1, (int) ($pd * 0.6));
            $b['hp'] = (int) $b['hp'] - $extra;
            $log .= '(狂乱·追加' . $extra . ')';
        }
    }
    // 第二接口：暴击回血（暗影主教汲魂 / 神圣主教赎罪）
    if ($isCrit) {
        foreach (legend_relic_effs((int) ($u['id'] ?? 0)) as $e) {
            if (isset($e['oncrit_heal'])) {
                $heal = max(1, (int) ((int) $u['maxhp'] * (float) $e['oncrit_heal'] / 100));
                $u['hp'] = min((int) $u['maxhp'], (int) $u['hp'] + $heal);
                $log .= '(暴击回血+' . $heal . ')';
            }
        }
    }
}

// 受到伤害后（扣血前）：叹息之墙 / 磐石 / 死斗者
function legend_relic_incoming(array &$u, array &$b, int $md): int
{
    $w = legend_relic_worn((int) ($u['id'] ?? 0));
    if ($w === [] || $md <= 0) {
        return $md;
    }
    // 叹息之墙：-20%，储存减免（写回 $b 以便反击引爆）
    if (isset($w['lg_sighwall'])) {
        $cut = (int) ($md * $w['lg_sighwall'] / 100);
        $md -= $cut;
        $b['lg_sigh'] = (int) ($b['lg_sigh'] ?? 0) + $cut;
    }
    // 磐石：己方物防更高时 -20%
    if (isset($w['lg_rock']) && player_pdef($u) > monster_pdef($b)) {
        $md = (int) ($md * (1 - $w['lg_rock'] / 100));
    }
    // 死斗者：受到伤害 +20%
    if (isset($w['lg_deathmatch'])) {
        $md = (int) ($md * 1.2);
    }
    // 第二接口：残血减伤（铁卫磐心）
    if ((int) $u['maxhp'] > 0) {
        foreach (legend_relic_effs((int) ($u['id'] ?? 0)) as $e) {
            if (isset($e['lowhp_reduce']) && (int) $u['hp'] < (int) $u['maxhp'] * (float) $e['lowhp_reduce'][0]) {
                $md = (int) ($md * (1 - (float) $e['lowhp_reduce'][1] / 100));
            }
        }
    }
    // BOSS传说·海妖女王【溺歌夺魄】：挨打时反制，概率令敌人下一回合无法行动
    if (isset($w['lg_bl_siren']) && mt_rand(1, 100) <= 30) {
        $b['stun'] = max((int) ($b['stun'] ?? 0), 1);
    }
    return max(0, $md);
}

// 反击调整：返回新的反击基础伤害；年轮回血/叹息引爆在里面处理
function legend_relic_counter(array &$u, array &$b, int $thorns, string &$log): int
{
    $w = legend_relic_worn((int) ($u['id'] ?? 0));
    if ($w === []) {
        return $thorns;
    }
    // 泰坦之肤：每挨打叠层，反击+
    if (isset($w['lg_taitan'])) {
        $st = min(10, (int) ($b['lg_taitan'] ?? 0));
        $thorns = (int) round($thorns * (1 + $st * 0.05));
    }
    // 荆棘之心：反击+30%
    if (isset($w['lg_bramble'])) {
        $thorns = (int) round($thorns * (1 + $w['lg_bramble'] / 100));
    }
    // 叹息之墙：把储存伤害以2倍引爆
    if (isset($w['lg_sighwall'])) {
        $store = (int) ($b['lg_sigh'] ?? 0);
        if ($store > 0) {
            $thorns += $store * 2;
            $b['lg_sigh'] = 0;
            $log .= '(叹息引爆+' . ($store * 2) . ')';
        }
    }
    // 不朽：每次反击，技能附加+10%叠层（最多5）
    if (isset($w['lg_undying'])) {
        $b['lg_undying'] = min(5, (int) ($b['lg_undying'] ?? 0) + 1);
    }
    // 第二接口：反击伤害%（铁卫）
    foreach (legend_relic_effs((int) ($u['id'] ?? 0)) as $e) {
        if (isset($e['counter'])) {
            $thorns = (int) round($thorns * (1 + (float) $e['counter'] / 100));
        }
    }
    return $thorns;
}

// 反击命中后：年轮回血（按反击伤害%）
function legend_relic_counter_after(array &$u, array &$b, int $counterDmg, string &$log): void
{
    $w = legend_relic_worn((int) ($u['id'] ?? 0));
    if (isset($w['lg_years']) && $counterDmg > 0) {
        $heal = max(1, (int) ($counterDmg * $w['lg_years'] / 100));
        $u['hp'] = min((int) $u['maxhp'], (int) $u['hp'] + $heal);
        $log .= '(年轮+' . $heal . ')';
    }
}

// 致命伤害拦截：涅槃。返回 true 表示已拦截（保命）
function legend_relic_death_guard(array &$u, array &$b, string &$log): bool
{
    $w = legend_relic_worn((int) ($u['id'] ?? 0));
    if (!isset($w['lg_nirvana']) || !empty($b['lg_nirvana_used'])) {
        return false;
    }
    if ((int) $u['hp'] > 0) {
        return false;
    }
    $u['hp'] = 1;
    $b['lg_nirvana_used'] = 1;
    $b['lg_berserk'] = max(1, (int) $w['lg_nirvana']);
    $log .= '【涅槃】你从死亡边缘被拉回，物攻翻倍' . $b['lg_berserk'] . '回合（期间无法治疗）！';
    return true;
}

// 击杀叠加：杀戮骨戒
function legend_relic_on_kill(array &$u, array &$b): void
{
    $w = legend_relic_worn((int) ($u['id'] ?? 0));
    if (isset($w['lg_killbone'])) {
        $b['lg_kbone'] = min(50, (int) ($b['lg_kbone'] ?? 0) + 1);
    }
    // 第二接口：击杀回血（狂战士狂宴）
    foreach (legend_relic_effs((int) ($u['id'] ?? 0)) as $e) {
        if (isset($e['kill_heal'])) {
            $heal = max(1, (int) ((int) $u['maxhp'] * (float) $e['kill_heal'] / 100));
            $u['hp'] = min((int) $u['maxhp'], (int) $u['hp'] + $heal);
        }
    }
}

// 供外部（element / dot / summon）调用的通用聚合
function legend_relic_el_mult(int $uid, string $el): float
{
    return 1 + legend_relic_eff_el($uid, $el) / 100;
}
function legend_relic_dot_mult(int $uid): float
{
    return 1 + legend_relic_eff_sum($uid, 'dot') / 100;
}
function legend_relic_summon_cap(int $uid): int
{
    return (int) legend_relic_eff_sum($uid, 'summon_cap');
}
function legend_relic_summon_power(int $uid): float
{
    return legend_relic_eff_sum($uid, 'summon_power');
}
function legend_relic_summon_hp(int $uid): float
{
    return legend_relic_eff_sum($uid, 'summon_hp');
}

// 涅槃狂暴翻倍：出手前物攻翻倍，回合数递减
function legend_relic_berserk_mult(array &$b): float
{
    if (!empty($b['lg_berserk']) && (int) $b['lg_berserk'] > 0) {
        return 2.0;
    }
    return 1.0;
}
