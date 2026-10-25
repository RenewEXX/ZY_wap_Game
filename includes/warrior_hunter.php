<?php
declare(strict_types=1);

// ============ 战士 / 猎手 职业核心机制 ============
// 战士分两个方向，体现在技能与被动：
//   防战：以「物防+法防」为伤害来源，挨打后按防御等比例反击（荆棘）
//   狂战：以「本场已损失的生命数值」为燃料，越痛越强（注意是数值不是百分比）
// 猎手分两个方向：
//   游侠：群体持续伤害（DOT），全场铺层，靠磨
//   刺客：单体高额百分比爆发 + 自身高闪避

// —— 战斗内累计掉血（狂战燃料）——
// 记录本场战斗累计承受的伤害。存 battle 里，战斗结束自然丢弃。
function warr_hp_lost(array &$b): int
{
    return max(0, (int) ($b['warr_hp_lost'] ?? 0));
}

// 累加本场掉血，返回累计值
function warr_add_hp_lost(array &$b, int $dmg): int
{
    if ($dmg > 0) {
        $b['warr_hp_lost'] = warr_hp_lost($b) + $dmg;
    }
    return warr_hp_lost($b);
}

// 狂战系数：每损失 100 点生命，攻击 +1%。封顶 +200%。
// 注意用的是绝对数值——不是"掉了百分之多少生命"。
const WARR_RAGE_PER = 100;   // 每损失 100 点生命算一档
const WARR_RAGE_RATE = 1.0;  // 每档加 1% 攻击（点数，不是小数）
const WARR_RAGE_CAP = 200.0; // 最多 +200 个百分点（= 伤害×3），单位是「百分点」

function warr_rage_mult(array $b, int $uid = 0): float
{
    $lost = warr_hp_lost($b);
    if ($lost <= 0) {
        return 1.0;
    }
    $cap = $uid > 0 ? warr_rage_cap_for($uid) : WARR_RAGE_CAP;
    $stacks = intdiv($lost, WARR_RAGE_PER);          // 掉了几个"百点血"
    $pct = $stacks * WARR_RAGE_RATE;                 // 加多少百分点
    return 1.0 + min($pct, $cap) / 100;
}

// 2觉·狂战士：狂战加成再+25 个百分点（上限 225%）
function warr_rage_cap_for(int $uid): float
{
    $rb2 = (string) (cflags((int) $uid)['rb2_job'] ?? '');
    return $rb2 === 'berserker' ? (WARR_RAGE_CAP + 25.0) : WARR_RAGE_CAP;
}

function warr_rage_text(array $b, int $uid = 0): string
{
    $lost = warr_hp_lost($b);
    if ($lost <= 0) {
        return '';
    }
    $mult = warr_rage_mult($b, $uid);
    $pct = (int) (($mult - 1.0) * 100);
    return '(狂战 损血' . $lost . ' +' . $pct . '%)';
}

// —— 防战：按防御反击 ——
// 荆棘反击系数：每点「物防+法防」折算多少反击伤害
const WARR_THORNS_RATE = 0.85;

// 防战伤害加成：技能直接吃双防（pdef+mdef）
const WARR_TANK_SCALE = 0.6;

// 计算一次荆棘反击伤害。$mult 可被 2觉/装备放大。
function warr_thorns(array $u, float $mult = 1.0): int
{
    $def = player_pdef($u) + player_mdef($u);
    if ($def <= 0) {
        return 0;
    }
    $rb2 = (string) (cflags((int) ($u['id'] ?? 0))['rb2_job'] ?? '');
    if ($rb2 === 'aegis') {
        $mult *= 1.35;
    }
    return max(1, (int) round($def * WARR_THORNS_RATE * $mult));
}

// 防战技能伤害加成段：把双防折进伤害
function warr_tank_bonus(array $u): int
{
    $def = player_pdef($u) + player_mdef($u);
    return max(0, (int) round($def * WARR_TANK_SCALE));
}

// ————————————————————————————————————————
// 猎手
// ————————————————————————————————————————

// 游侠：DOT 伤害加成（2觉被动"百步穿杨"改为群体DOT方向）
const RANGER_DOT_BONUS = 0.35;   // DOT 伤害 +35%

function hunter_dot_mult(array $u): float
{
    $rb2 = (string) (cflags((int) ($u['id'] ?? 0))['rb2_job'] ?? '');
    $m = $rb2 === 'ranger' ? (1 + RANGER_DOT_BONUS) : 1.0;
    // DOT 加成合并成「一个加法基数」：散件传说的 dot 总和 + 蚀心套装加成。
    // 必须相加、不能相乘——否则"穿部分套装 + 剩下槽位塞散件"会因乘法被混搭刷爆，毕业套就不再是终点。
    $pct = 0.0;
    if (function_exists('legend_relic_dot_mult')) {
        $pct += (legend_relic_dot_mult((int) ($u['id'] ?? 0)) - 1) * 100; // 散件传说 dot 总和(%)
    }
    if (function_exists('dot_set_dot_pct')) {
        $pct += dot_set_dot_pct((int) ($u['id'] ?? 0)); // 蚀心套装加成(%)
    }
    $m *= (1 + $pct / 100);
    return $m;
}

// 刺客：单体爆发加成 + 高闪避
const ASSASSIN_STEP_BONUS = 0.6;   // 刺客技能单体伤害 +60%
const ASSASSIN_DODGE = 18;         // 2觉·影刺客 额外闪避 +18%

function hunter_assassin_bonus(array $u): float
{
    $rb2 = (string) (cflags((int) ($u['id'] ?? 0))['rb2_job'] ?? '');
    return $rb2 === 'assassin' ? (1 + ASSASSIN_STEP_BONUS) : 1.0;
}

// 刺客额外闪避点
function hunter_assassin_dodge(int $uid): int
{
    $rb2 = (string) (cflags((int) $uid)['rb2_job'] ?? '');
    return $rb2 === 'assassin' ? ASSASSIN_DODGE : 0;
}

// 游侠铺层：群攻 DOT 每命中一只额外叠层（t10/lv20 各自加一层）
function hunter_ranger_stacks(string $sid, int $tier, bool $lv20): int
{
    $base = 2;
    if ($tier >= 10) {
        $base += 1;
    }
    if ($lv20) {
        $base += 1;
    }
    return $base;
}