<?php
declare(strict_types=1);

// ============ 召唤师专属副手：契匣 ============
// 契匣只有召唤师能戴（equip_can_wear 已限制）。
// 品质规则跟全游戏一致（见 roll_drop）：
//   普通基底（legend=false）→ 掉落时在 低劣~史诗 之间随机
//   传说基底（legend=true） → 只出传说，且带额外特殊效果
// 另：
//  1) 戴上任意契匣进战斗，开场自动召唤一只对应的召唤兽（不耗蓝、不占回合）。
//  2) 基底按等级区间递进，开场召唤的单位随区间变强。

function summoner_offhands(): array
{
    // name => [lv: 穿戴等级, summon: 开场召唤的单位, eff: 特殊效果, legend: 是否传说基底, intro]
    return [
        // —— 新手区 ——
        '残魂灯匣' => ['lv' => 1, 'summon' => 'imp', 'eff' => [], 'legend' => false, 'intro' => '新手契匣：开场召唤1只深渊小鬼。'],
        '引魂旧匣' => ['lv' => 50, 'summon' => 'imp', 'eff' => [], 'legend' => false, 'intro' => '开场召唤1只深渊小鬼。'],
        '缚魂小匣' => ['lv' => 100, 'summon' => 'imp', 'eff' => ['turns' => 1], 'legend' => false, 'intro' => '开场召唤1只深渊小鬼（多活1轮）。'],
        // —— 中段 ——
        '招魂匣' => ['lv' => 200, 'summon' => 'imp', 'eff' => ['turns' => 1], 'legend' => false, 'intro' => '开场召唤1只深渊小鬼（多活1轮）。'],
        '引魂匣' => ['lv' => 300, 'summon' => 'bone', 'eff' => [], 'legend' => false, 'intro' => '开场召唤1只骸骨守卫，血厚能扛。'],
        '骨契匣' => ['lv' => 360, 'summon' => 'bone', 'eff' => ['turns' => 1], 'legend' => false, 'intro' => '开场召唤1只骸骨守卫（多活1轮）。'],
        '雾森契匣' => ['lv' => 420, 'summon' => 'bone', 'eff' => ['power' => 15], 'legend' => false, 'intro' => '开场召唤1只骸骨守卫（召唤兽伤害+15%）。'],
        // —— 高段 ——
        '寒霜·契匣' => ['lv' => 460, 'summon' => 'bone', 'eff' => ['power' => 20], 'legend' => false, 'intro' => '开场召唤1只骸骨守卫（召唤兽伤害+20%）。'],
        '裹魂契匣' => ['lv' => 575, 'summon' => 'wraith', 'eff' => ['power' => 20], 'legend' => false, 'intro' => '开场召唤1只深渊恶鬼（死时爆炸，召唤兽伤害+20%）。'],
        '剧场·提线匣' => ['lv' => 600, 'summon' => 'abom', 'eff' => ['power' => 25], 'legend' => false, 'intro' => '开场召唤1只憎恶聚合体（死时爆炸，召唤兽伤害+25%）。'],
        '烬焰·契匣' => ['lv' => 635, 'summon' => 'abom', 'eff' => ['power' => 25], 'legend' => false, 'intro' => '开场召唤1只憎恶聚合体（死时爆炸，召唤兽伤害+25%）。'],
        // —— 毕业：传说基底（只出传说，带额外特殊效果）——
        '召唤师·深渊契匣' => ['lv' => 700, 'summon' => 'abom', 'eff' => ['turns' => 2, 'power' => 30, 'hp' => 40, 'cap' => 1], 'legend' => true, 'intro' => '开场召唤1只憎恶聚合体；召唤上限+1；召唤兽存活+2轮；召唤兽伤害+30%；召唤兽生命+40%。'],
        '幽冥·招魂匣' => ['lv' => 800, 'summon' => 'mare', 'eff' => ['turns' => 2, 'power' => 50, 'hp' => 80, 'guard' => 15], 'legend' => true, 'intro' => '开场召唤1只无面梦魇（最强）；召唤兽存活+2轮；召唤兽伤害+50%；召唤兽生命+80%；有召唤兽在场时你减伤+15%。'],
        // —— 魂契套装散件也算契匣（武器位，但同样吃开场召唤）——
        '魂契·深渊引魂灯' => ['lv' => 700, 'summon' => 'abom', 'eff' => ['power' => 30, 'hp' => 40, 'cap' => 1], 'legend' => true, 'intro' => '开场召唤1只憎恶聚合体；召唤上限+1；召唤兽伤害+30%；召唤兽生命+40%。'],
    ];
}

function summoner_is_box(string $name): bool
{
    $short = equip_shortname($name);
    foreach (array_keys(summoner_offhands()) as $key) {
        if ($short === $key || str_contains($name, $key) || str_contains($key, $short)) {
            return true;
        }
    }
    return false;
}

// 当前穿戴的契匣（只认 offhand 栏；同一栏位取穿戴等级更高的那个）
function summoner_offhand_worn(int $uid): array
{
    $st = db()->prepare('SELECT name FROM equips WHERE uid=? AND slot="offhand" AND pos="wear" LIMIT 2');
    $st->execute([$uid]);
    $rows = $st->fetchAll();
    $table = summoner_offhands();
    $best = [];
    foreach ($rows as $r) {
        $short = equip_shortname((string) $r['name']);
        foreach ($table as $key => $cfg) {
            if ($short === $key || str_contains((string) $r['name'], $key) || str_contains($key, $short)) {
                if ($best === [] || (int) $cfg['lv'] > (int) ($best['cfg']['lv'] ?? -1)) {
                    $best = ['name' => $key, 'cfg' => $cfg];
                }
            }
        }
    }
    return $best;
}

// 战斗开场：契匣自动召唤（只招一次）
function summoner_open_message(array $u): string
{
    if (job_id_of($u) !== 'summoner') {
        return '';
    }
    $worn = summoner_offhand_worn((int) ($u['id'] ?? 0));
    if ($worn === []) {
        return '';
    }
    $cfg = $worn['cfg'];
    $turns = 0 + (int) ($cfg['eff']['turns'] ?? 0);
    return '【' . $worn['name'] . '】匣门自开——' . summon_add((string) $cfg['summon'], $u, $turns);
}

// 契匣特殊效果汇总（给 summon_* 系列读取）
function summoner_offhand_eff(int $uid): array
{
    $worn = summoner_offhand_worn($uid);
    $e = $worn['cfg']['eff'] ?? [];
    return [
        'cap' => (int) ($e['cap'] ?? 0),
        'power' => (float) ($e['power'] ?? 0),
        'hp' => (float) ($e['hp'] ?? 0),
        'turns' => (int) ($e['turns'] ?? 0),
        'guard' => (float) ($e['guard'] ?? 0),
    ];
}

// 等级区间对照表：基底按等级递进，传说基底只在对应等级以上才出
function summoner_offhand_tiers(): array
{
    return [
        ['min' => 1, 'max' => 99, 'base' => '残魂灯匣', 'legend' => null],
        ['min' => 100, 'max' => 199, 'base' => '引魂旧匣', 'legend' => null],
        ['min' => 200, 'max' => 299, 'base' => '缚魂小匣', 'legend' => null],
        ['min' => 300, 'max' => 419, 'base' => '招魂匣', 'legend' => null],
        ['min' => 420, 'max' => 499, 'base' => '引魂匣', 'legend' => null],
        ['min' => 500, 'max' => 599, 'base' => '骨契匣', 'legend' => null],
        ['min' => 600, 'max' => 699, 'base' => '裹魂契匣', 'legend' => null],
        ['min' => 700, 'max' => 799, 'base' => '烬焰·契匣', 'legend' => '召唤师·深渊契匣'],
        ['min' => 800, 'max' => 999, 'base' => '剧场·提线匣', 'legend' => '幽冥·招魂匣'],
    ];
}

// 该等级区间的基底名。$wantLegend=true 时只在该区间确实有传说基底才返回
function summoner_offhand_for_lv(int $lv, bool $wantLegend = false): ?string
{
    foreach (summoner_offhand_tiers() as $t) {
        if ($lv >= $t['min'] && $lv <= $t['max']) {
            if ($wantLegend) {
                return $t['legend'];
            }
            return $t['base'];
        }
    }
    return null;
}
