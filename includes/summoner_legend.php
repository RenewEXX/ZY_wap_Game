<?php
declare(strict_types=1);

// ============ 召唤师·死灵传说装备（行为型传说） ============
// 跟普通传说不同：这些装备不靠词条数值，而是给召唤体系挂"触发效果"。
// 判定方式：穿戴中带 slegend_* 词条的装备，运行时读出来挂效果。
// 全部为召唤师向，但槽位本身不限职业（其它职业戴上没有召唤物，等于白戴）。

function summoner_legends(): array
{
    // name => [slot: 部位, lv: 掉落等级/穿戴等级, key: 效果词条, param: 主参数, intro: 效果说明]
    return [
        '灵魂荆棘' => [
            'slot' => 'weapon', 'lv' => 760, 'key' => 'slegend_soulthorn', 'param' => 15,
            'intro' => '召唤物造成伤害时，额外附带该次伤害15%的真实伤害；若该次召唤物暴击，则翻倍（30%）。',
        ],
        '嘶鸣' => [
            'slot' => 'gloves', 'lv' => 780, 'key' => 'slegend_hiss', 'param' => 100,
            'intro' => '你的召唤物攻击必定暴击。',
        ],
        '尼德霍格的残鳞' => [
            'slot' => 'body', 'lv' => 800, 'key' => 'slegend_rescale', 'param' => 10,
            'intro' => '每召唤出一只召唤物，你的最大生命-10%、召唤兽伤害+20%，最多叠5层（每场战斗清空）。',
        ],
        '死灵之拥的腐朽头冠' => [
            'slot' => 'head', 'lv' => 820, 'key' => 'slegend_necrohold', 'param' => 10,
            'intro' => '召唤物阵亡时引发灵魂爆炸，对敌人造成其最大生命10%的伤害；若为高级召唤物，额外眩晕1回合。',
        ],
        '玛门的契约之眼' => [
            'slot' => 'necklace', 'lv' => 850, 'key' => 'slegend_mammoneye', 'param' => 5,
            'intro' => '召唤物攻击时有5%概率使目标麻痹2回合。',
        ],
        '深渊统御之印' => [
            'slot' => 'ring', 'lv' => 880, 'key' => 'slegend_abyssseal', 'param' => 4,
            'intro' => '召唤数量上限+1；当召唤物同时存活≥4只时全体狂乱：伤害+20%、10%概率破防重击。',
        ],
        '血契骨戒' => [
            'slot' => 'ring', 'lv' => 920, 'key' => 'slegend_bloodpact', 'param' => 20,
            'intro' => '被动：你20%的行动会吞噬当前生命最低的一只召唤物，回复50%最大生命，并重新召唤它。',
        ],
    ];
}

// 部位对照表（洞悉用）
function summoner_legend_by_name(string $name): ?array
{
    return summoner_legends()[equip_shortname($name)] ?? null;
}

// 按效果词条 id 取说明文字（装备详情展示用）
function summoner_legend_intro_by_key(string $key): string
{
    foreach (summoner_legends() as $cfg) {
        if ((string) $cfg['key'] === $key) {
            return (string) $cfg['intro'];
        }
    }
    return '';
}

// 掉落来源：挂到现有终局BOSS（不新增地图/怪物）
function summoner_legend_drops(): array
{
    return [
        'god_warden' => '灵魂荆棘',
        'god_forger' => '嘶鸣',
        'da_rot_seer' => '尼德霍格的残鳞',
        'gf_shade' => '死灵之拥的腐朽头冠',
        'god_golem' => '玛门的契约之眼',
        'god_avatar' => '深渊统御之印',
        'god_archon' => '血契骨戒',
    ];
}

// 当前穿戴的行为传说：key => param
function summoner_legend_worn(int $uid): array
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
            if (str_starts_with($id, 'slegend_')) {
                $out[$id] = (float) ($a['v'] ?? 0);
            }
        }
    }
    $cache[$ck] = $out;
    return $out;
}

// 召唤物暴击倍率：基础1.5倍，再吃主人的暴击伤害
function summoner_legend_crit_mult(array $u): float
{
    $gs = gear_stats((int) ($u['id'] ?? 0));
    return 1.5 + max(0.0, (float) ($gs['critdmg'] ?? 0)) / 100;
}

// 残鳞：每召唤一只叠一层（只记录层数/基准，最大生命在每轮战斗按层数统一重算）
function summoner_legend_rescale_add(int $uid): string
{
    $f = cflags((int) $uid);
    $stacks = (int) ($f['rs_stacks'] ?? 0);
    if ($stacks >= 5) {
        return '';
    }
    if ((int) ($f['rs_base'] ?? 0) <= 0) {
        $u = user_by_id((int) $uid);
        if (!$u) {
            return '';
        }
        cflag_set((int) $uid, 'rs_base', (int) $u['maxhp']);
    }
    $stacks++;
    cflag_set((int) $uid, 'rs_stacks', $stacks);
    return '（残鳞x' . $stacks . '：最大生命-' . ($stacks * 10) . '%、召唤兽伤害+' . ($stacks * 20) . '%）';
}

// 残鳞：每轮开打前按层数重算最大生命（配合 battle_round 的 user_save）
function summoner_legend_rescale_apply(array &$u): void
{
    $uid = (int) ($u['id'] ?? 0);
    if ($uid <= 0) {
        return;
    }
    $f = cflags($uid);
    $base = (int) ($f['rs_base'] ?? 0);
    $stacks = (int) ($f['rs_stacks'] ?? 0);
    if ($base <= 0) {
        return;
    }
    if ($stacks <= 0) {
        $u['maxhp'] = $base;
    } else {
        $u['maxhp'] = max(1, (int) round($base * (1 - 0.10 * $stacks)));
    }
    if ((int) $u['hp'] > (int) $u['maxhp']) {
        $u['hp'] = (int) $u['maxhp'];
    }
}

// 残鳞：战斗结束/离场时恢复原始最大生命
function summoner_legend_rescale_clear(array $u): void
{
    $uid = (int) ($u['id'] ?? 0);
    if ($uid <= 0) {
        return;
    }
    $f = cflags($uid);
    if ((int) ($f['rs_base'] ?? 0) <= 0) {
        return;
    }
    $nu = user_by_id($uid);
    if (!$nu) {
        return;
    }
    $nu['maxhp'] = (int) $f['rs_base'];
    if ((int) $nu['hp'] > $nu['maxhp']) {
        $nu['hp'] = $nu['maxhp'];
    }
    cflag_set($uid, 'rs_base', 0);
    cflag_set($uid, 'rs_stacks', 0);
    user_save($nu);
}

// 残鳞当前层数（给伤害结算读）
function summoner_legend_rescale_stacks(int $uid): int
{
    return max(0, (int) (cflags((int) $uid)['rs_stacks'] ?? 0));
}

// 腐朽头冠：召唤物阵亡时灵魂爆炸（对敌造成其最大生命10%伤害；高级召唤物额外眩晕1回合）
function summoner_legend_summon_death(array &$b, array $summon, int $uid): string
{
    if (!isset(summoner_legend_worn($uid)['slegend_necrohold'])) {
        return '';
    }
    $dmg = max(1, (int) round((int) ($summon['maxhp'] ?? 0) * 0.10));
    $b['hp'] = max(0, (int) $b['hp'] - $dmg);
    $log = '【腐朽头冠】' . $summon['name'] . '灵魂炸开，造成' . $dmg . '点范围伤害！';
    if (summoner_legend_advanced_unit((string) ($summon['kind'] ?? ''))) {
        $b['stun'] = max((int) ($b['stun'] ?? 0), 1);
        $log .= '(眩晕)';
    }
    return $log;
}

// 血契骨戒：20%行动触发献祭（吞噬最低血召唤物，回50%最大生命，并重新召唤）
function summoner_legend_bloodpact(array &$u, array &$b): string
{
    $uid = (int) ($u['id'] ?? 0);
    if (!isset(summoner_legend_worn($uid)['slegend_bloodpact'])) {
        return '';
    }
    $list = summon_list();
    if ($list === []) {
        return '';
    }
    if (mt_rand(1, 100) > 20) {
        return '';
    }
    $mi = 0;
    $mh = PHP_INT_MAX;
    foreach ($list as $i => $s) {
        if ((int) $s['hp'] < $mh) {
            $mh = (int) $s['hp'];
            $mi = $i;
        }
    }
    $victim = $list[$mi];
    array_splice($list, $mi, 1);
    summon_save($list);
    $heal = min((int) $u['maxhp'] - (int) $u['hp'], (int) round((int) $u['maxhp'] * 0.5));
    if ($heal > 0) {
        $u['hp'] = (int) $u['hp'] + $heal;
    }
    // 重新召唤被吞噬的那只（不回残鳞层数）
    $ns = summon_spawn((string) ($victim['kind'] ?? 'imp'), player_matk($u), (int) $u['maxhp'], $u);
    $ns['hp'] = $ns['maxhp'];
    $l2 = summon_list();
    $l2[] = $ns;
    summon_save($l2);
    return '【血契骨戒】你吞下' . $victim['name'] . '，回复' . max(0, $heal) . '点生命，它重新站了起来！';
}

// 高级召唤物（吃"腐朽头冠"眩晕的那一类）
function summoner_legend_advanced_unit(string $kind): bool
{
    return in_array($kind, ['bone', 'wraith', 'abom', 'mare'], true);
}

// 击杀掉落：现有终局BOSS，低概率
function summoner_legend_kill_drop(int $uid, string $mid): string
{
    $map = summoner_legend_drops();
    if (!isset($map[$mid])) {
        return '';
    }
    if (mt_rand(1, 100) > 4) {
        return '';
    }
    $name = $map[$mid];
    $cfg = summoner_legends()[$name];
    $u = user_by_id((int) $uid);
    if (!$u) {
        return '';
    }
    $sn = make_equip((int) $uid, (string) $cfg['slot'], (string) $name, 4, (int) $cfg['lv'], ['extra' => ['k' => (string) $cfg['key'], 'v' => (float) $cfg['param']]]);
    if ($sn === '') {
        return '';
    }
    return '深渊赐下召唤师的死灵传说【' . $sn . '】！';
}
