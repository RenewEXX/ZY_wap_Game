<?php
declare(strict_types=1);

// ============ 召唤师专属副手：契匣 ============
// 契匣只有召唤师能戴（equip_can_wear 已限制）。
// 规则：
//  1) 戴上任意契匣进战斗，开场自动召唤一只对应的召唤兽（不耗蓝、不占回合）。
//  2) 过渡副手（普通~史诗）按等级区间给：开场召唤的强度随等级爬升。
//  3) 传说副手带额外特殊效果（召唤上限/存活/增伤/减伤）。

function summoner_offhands(): array
{
    // name => [lv: 穿戴等级, q: 品质(0低劣~4传说), summon: 开场召唤的单位, eff: 特殊效果, intro]
    return [
        // —— 新手区：普通过渡 ——
        '残魂灯匣' => ['lv' => 1, 'q' => 1, 'summon' => 'imp', 'eff' => [], 'intro' => '新手契匣：开场召唤1只深渊小鬼。'],
        '引魂旧匣' => ['lv' => 50, 'q' => 1, 'summon' => 'imp', 'eff' => [], 'intro' => '开场召唤1只深渊小鬼。'],
        '缚魂小匣' => ['lv' => 100, 'q' => 2, 'summon' => 'imp', 'eff' => ['turns' => 1], 'intro' => '开场召唤1只深渊小鬼（多活1轮）。'],
        // —— 中段：稀有过渡 ——
        '招魂匣' => ['lv' => 200, 'q' => 2, 'summon' => 'imp', 'eff' => ['turns' => 1], 'intro' => '开场召唤1只深渊小鬼（多活1轮）。'],
        '引魂匣' => ['lv' => 300, 'q' => 2, 'summon' => 'bone', 'eff' => [], 'intro' => '开场召唤1只骸骨守卫，血厚能扛。'],
        '骨契匣' => ['lv' => 360, 'q' => 2, 'summon' => 'bone', 'eff' => ['turns' => 1], 'intro' => '开场召唤1只骸骨守卫（多活1轮）。'],
        // —— 高段：史诗过渡 ——
        '雾森契匣' => ['lv' => 420, 'q' => 3, 'summon' => 'bone', 'eff' => ['turns' => 1, 'power' => 15], 'intro' => '开场召唤1只骸骨守卫（+1轮，召唤兽伤害+15%）。'],
        '寒霜·契匣' => ['lv' => 460, 'q' => 3, 'summon' => 'bone', 'eff' => ['turns' => 1, 'power' => 20], 'intro' => '开场召唤1只骸骨守卫（+1轮，召唤兽伤害+20%）。'],
        '裹魂契匣' => ['lv' => 575, 'q' => 2, 'summon' => 'wraith', 'eff' => ['power' => 20], 'intro' => '开场召唤1只深渊恶鬼（死时爆炸，召唤兽伤害+20%）。'],
        '烬焰·契匣' => ['lv' => 635, 'q' => 3, 'summon' => 'abom', 'eff' => ['power' => 25], 'intro' => '开场召唤1只憎恶聚合体（死时爆炸，召唤兽伤害+25%）。'],
        '剧场·提线匣' => ['lv' => 600, 'q' => 3, 'summon' => 'abom', 'eff' => ['power' => 25], 'intro' => '开场召唤1只憎恶聚合体（死时爆炸，召唤兽伤害+25%）。'],
        // —— 毕业：传说（额外特殊效果）——
        '召唤师·深渊契匣' => ['lv' => 700, 'q' => 4, 'summon' => 'abom', 'eff' => ['turns' => 2, 'power' => 30, 'cap' => 1], 'intro' => '开场召唤1只憎恶聚合体；召唤上限+1；召唤兽存活+2轮；召唤兽伤害+30%。'],
        '幽冥·招魂匣' => ['lv' => 800, 'q' => 4, 'summon' => 'mare', 'eff' => ['turns' => 2, 'power' => 50, 'guard' => 15], 'intro' => '开场召唤1只无面梦魇（最强）；召唤兽存活+2轮；召唤兽伤害+50%；有召唤兽在场时你减伤+15%。'],
        // —— 魂契套装散件也算契匣 ——
        '魂契·深渊引魂灯' => ['lv' => 700, 'q' => 4, 'summon' => 'abom', 'eff' => ['power' => 30, 'cap' => 1], 'intro' => '开场召唤1只憎恶聚合体；召唤上限+1；召唤兽伤害+30%。'],
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

// 当前穿戴的契匣（只认 offhand 栏）
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
                if ($best === [] || (int) $cfg['q'] > (int) ($best['cfg']['q'] ?? -1)) {
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
        'turns' => (int) ($e['turns'] ?? 0),
        'guard' => (float) ($e['guard'] ?? 0),
    ];
}

// 等级区间推荐：给任务/掉落/UI 用的对照表
function summoner_offhand_tiers(): array
{
    return [
        ['min' => 1, 'max' => 99, 'common' => '残魂灯匣', 'epic' => null, 'legend' => null],
        ['min' => 100, 'max' => 199, 'common' => '引魂旧匣', 'epic' => null, 'legend' => null],
        ['min' => 200, 'max' => 299, 'common' => '缚魂小匣', 'epic' => null, 'legend' => null],
        ['min' => 300, 'max' => 399, 'common' => '招魂匣', 'epic' => null, 'legend' => null],
        ['min' => 360, 'max' => 419, 'common' => '引魂匣', 'epic' => '骨契匣', 'legend' => null],
        ['min' => 420, 'max' => 499, 'common' => '雾森契匣', 'epic' => '雾森契匣', 'legend' => null],
        ['min' => 500, 'max' => 599, 'common' => '骨契匣', 'epic' => '寒霜·契匣', 'legend' => null],
        ['min' => 600, 'max' => 699, 'common' => '裹魂契匣', 'epic' => '裹魂契匣', 'legend' => null],
        ['min' => 700, 'max' => 799, 'common' => '烬焰·契匣', 'epic' => '烬焰·契匣', 'legend' => '召唤师·深渊契匣'],
        ['min' => 800, 'max' => 999, 'common' => '烬焰·契匣', 'epic' => '剧场·提线匣', 'legend' => '幽冥·招魂匣'],
    ];
}

function summoner_offhand_for_lv(int $lv, string $grade = 'common'): ?string
{
    foreach (summoner_offhand_tiers() as $t) {
        if ($lv >= $t['min'] && $lv <= $t['max']) {
            return $t[$grade] ?? $t['common'] ?? null;
        }
    }
    return null;
}
