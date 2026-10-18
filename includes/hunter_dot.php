<?php
declare(strict_types=1);

// ============ 猎人职业特色：持续伤害（DOT）+ 群攻 ============
// 猎人的每一个技能都会在目标身上挂 DOT 层数；层数可叠加，打满靠磨。
// 三种 DOT：
//   bleed  出血：按施法瞬间的伤害结算，固定值，无视抗性（物理DOT）
//   poison 剧毒：按目标最大生命的百分比结算，暗属性（法系DOT）
//   burn   焦灼：按施法瞬间伤害的暗影火伤结算，会溅射进队列（范围DOT）

function hunter_dot_names(): array
{
    return ['bleed' => '出血', 'poison' => '剧毒', 'burn' => '焦灼'];
}

// 单层上限：防止无限叠
function hunter_dot_cap(string $key, int $tier): int
{
    $base = ['bleed' => 3, 'poison' => 3, 'burn' => 3][$key] ?? 3;
    return $tier >= 10 ? $base + 2 : $base;
}

// 挂 DOT 层。$srcDmg 是本次技能打出的伤害，作为固定值 DOT 的基准。
function hunter_dot_add(array &$b, string $key, int $add, int $srcDmg, bool $lv20 = false): string
{
    if ($key === '' || $add <= 0) {
        return '';
    }
    $cap = hunter_dot_cap($key, 0);
    $stacks = (int) ($b['dot_' . $key] ?? 0);
    $newStacks = min($cap, $stacks + $add);
    if ($newStacks <= 0) {
        return '';
    }
    $b['dot_' . $key] = $newStacks;
    $b['dot_' . $key . '_turns'] = hunter_dot_turns($key);
    // 固定值 DOT 用最强的那次伤害做基准，层数越高单层越疼
    $per = hunter_dot_per_stack($key, $srcDmg, $lv20);
    $prev = (int) ($b['dot_' . $key . '_per'] ?? 0);
    $b['dot_' . $key . '_per'] = max($prev, $per);
    $tag = hunter_dot_names()[$key] ?? 'DOT';
    $up = $newStacks > $stacks ? '↑' : '';
    return '(' . $tag . $newStacks . '层' . $up . ')';
}

// 每层每轮伤害
function hunter_dot_per_stack(string $key, int $srcDmg, bool $lv20 = false): int
{
    $base = match ($key) {
        'bleed' => max(2, (int) ($srcDmg * 0.18)),
        'poison' => 0, // 按最大生命百分比单独算
        'burn' => max(2, (int) ($srcDmg * 0.12)),
        default => 0,
    };
    if ($lv20) {
        $base = (int) ($base * 1.4);
    }
    return max(0, $base);
}

// 每种 DOT 持续轮数
function hunter_dot_turns(string $key): int
{
    return ['bleed' => 4, 'poison' => 4, 'burn' => 3][$key] ?? 3;
}

// 剧毒按目标最大生命百分比算，lv20 每层多 2%
function hunter_dot_poison_pct(int $bMaxhp, int $stacks, bool $lv20): int
{
    $per = 3 + ($lv20 ? 2 : 0);
    return max(1, (int) ($bMaxhp * $per * $stacks / 100));
}

// 结算一轮所有 DOT，返回掉血和日志
function hunter_dot_tick(array &$b): array
{
    $dmg = 0;
    $log = '';
    if ((int) ($b['hp'] ?? 0) <= 0) {
        return ['dmg' => 0, 'log' => ''];
    }
    $lv20 = !empty($b['dot_lv20']);
    $meld = monster_element((string) ($b['id'] ?? ''));
    foreach (['bleed', 'burn', 'poison'] as $key) {
        $stacks = (int) ($b['dot_' . $key] ?? 0);
        if ($stacks <= 0) {
            continue;
        }
        $name = (string) ($b['name'] ?? '敌人');
        if ($key === 'poison') {
            $hit = hunter_dot_poison_pct((int) ($b['maxhp'] ?? 1), $stacks, $lv20);
            $hit = (int) ($hit * (1 - monster_resist((string) ($b['id'] ?? ''), 'dark') / 100));
            $tag = '剧毒';
        } else {
            $per = (int) ($b['dot_' . $key . '_per'] ?? 0);
            $hit = max(1, $per * $stacks);
            $el = $key === 'burn' ? 'fire' : '';
            if ($el !== '') {
                $hit = (int) ($hit * (1 - monster_resist((string) ($b['id'] ?? ''), $el) / 100));
            }
            $tag = $key === 'bleed' ? '出血' : '焦灼';
        }
        $hit = max(1, $hit);
        $dmg += $hit;
        $log .= $tag . '×' . $stacks . '，' . $name . '掉' . $hit . '点。';
        // 衰减靠「剩余轮数」：层数本身不自己减，轮数耗尽才整层清空
        $turns = (int) ($b['dot_' . $key . '_turns'] ?? 0) - 1;
        $b['dot_' . $key . '_turns'] = max(0, $turns);
        if ($turns <= 0) {
            unset($b['dot_' . $key], $b['dot_' . $key . '_turns'], $b['dot_' . $key . '_per']);
        }
    }
    // 焦灼是范围DOT：伤害一部分灌进队列，攒满震死一只
    if (!empty($b['dot_burn'])) {
        $burnSplash = (int) ($b['dot_burn'] ?? 0) * (int) ($b['dot_burn_per'] ?? 0);
        $b['splash'] = (int) (($b['splash'] ?? 0) + $burnSplash);
    }
    return ['dmg' => $dmg, 'log' => $log];
}

// 弑神连珠：引爆目标身上所有 DOT 层数，每层转成一次额外伤害
function hunter_dot_detonate(array &$b, int $mult, bool $lv20): array
{
    $bonus = 0;
    $log = '';
    foreach (['bleed', 'burn', 'poison'] as $key) {
        $stacks = (int) ($b['dot_' . $key] ?? 0);
        if ($stacks <= 0) {
            continue;
        }
        $per = $key === 'poison'
            ? hunter_dot_poison_pct((int) ($b['maxhp'] ?? 1), 1, $lv20)
            : max(1, (int) ($b['dot_' . $key . '_per'] ?? 0));
        $bonus += $per * $stacks;
        $log .= (hunter_dot_names()[$key] ?? '') . '×' . $stacks . '引爆，';
        unset($b['dot_' . $key], $b['dot_' . $key . '_turns'], $b['dot_' . $key . '_per']);
    }
    if ($bonus <= 0) {
        return ['dmg' => 0, 'log' => ''];
    }
    $bonus = (int) ($bonus * $mult);
    return ['dmg' => $bonus, 'log' => '引燃全部DOT，额外' . $bonus . '点。'];
}

// 战斗对象初始化时带上 lv20 标记（每场战斗从施法者技能等级写入）
function hunter_dot_bind_lv20(array &$b, bool $lv20): void
{
    $b['dot_lv20'] = $lv20;
}

// 清空（战斗结束/换怪时调用）
function hunter_dot_clear(array &$b): void
{
    foreach (['bleed', 'burn', 'poison'] as $key) {
        unset($b['dot_' . $key], $b['dot_' . $key . '_turns'], $b['dot_' . $key . '_per']);
    }
    unset($b['dot_lv20']);
}

// 目标身上当前 DOT 总层数（UI/日志用）
function hunter_dot_stacks(array $b): array
{
    $out = [];
    foreach (['bleed', 'burn', 'poison'] as $key) {
        $n = (int) ($b['dot_' . $key] ?? 0);
        if ($n > 0) {
            $out[$key] = $n;
        }
    }
    return $out;
}
