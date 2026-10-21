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
    $map = legend_relic_drops();
    if (!isset($map[$mid])) {
        return '';
    }
    if (mt_rand(1, 100) > 3) { // 3% 概率
        return '';
    }
    $names = $map[$mid];
    $name = $names[array_rand($names)];
    $cfg = legend_relics()[$name] ?? null;
    if ($cfg === null) {
        return '';
    }
    $sn = make_equip((int) $uid, (string) $cfg['slot'], $name, 4, (int) $cfg['lv'], ['extra' => ['k' => (string) $cfg['key'], 'v' => (float) $cfg['param']]]);
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
}

// 防御倍率（誓言 / 泰坦之肤）——用于 player_pdef / player_mdef
function legend_relic_def_mult(array $u): float
{
    $w = legend_relic_worn((int) ($u['id'] ?? 0));
    $m = 1.0;
    if (isset($w['lg_taitan'])) {
        $m *= 1 + $w['lg_taitan'] / 100;
    }
    if (isset($w['lg_vow']) && (int) $u['hp'] < (int) $u['maxhp'] * 0.2) {
        $m *= 1 + $w['lg_vow'] / 100;
    }
    return $m;
}

// 攻击倍率（血怒 low-hp atk%）——用于 player_patk
function legend_relic_atk_mult(array $u): float
{
    $w = legend_relic_worn((int) ($u['id'] ?? 0));
    if (!isset($w['lg_bloodrage']) || (int) $u['maxhp'] <= 0) {
        return 1.0;
    }
    $lost = 1 - (int) $u['hp'] / max(1, (int) $u['maxhp']);
    $steps = min(10, (int) floor(max(0.0, $lost) * 10));
    return 1 + $steps * $w['lg_bloodrage'] / 100;
}

// 额外闪避（绝境 / 无影）——dmg_to_player 里叠加
function legend_relic_bonus_dodge(array $u): float
{
    $w = legend_relic_worn((int) ($u['id'] ?? 0));
    $add = 0.0;
    if (isset($w['lg_laststand']) && (int) $u['hp'] < (int) $u['maxhp'] * 0.2) {
        $add += $w['lg_laststand'];
    }
    if (isset($w['lg_noshadow']) && (int) $u['hp'] < (int) $u['maxhp'] * 0.01) {
        $add += $w['lg_noshadow'];
    }
    return $add;
}

// 出手前调整：返回 [pd, critAdd(暴击率%), forceCrit, trueProcPct, log]
function legend_relic_outgoing(array &$u, array &$b, int $pd, bool $isSkill, string &$log): array
{
    $w = legend_relic_worn((int) ($u['id'] ?? 0));
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
}

// 涅槃狂暴翻倍：出手前物攻翻倍，回合数递减
function legend_relic_berserk_mult(array &$b): float
{
    if (!empty($b['lg_berserk']) && (int) $b['lg_berserk'] > 0) {
        return 2.0;
    }
    return 1.0;
}
