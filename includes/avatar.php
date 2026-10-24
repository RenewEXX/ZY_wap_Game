<?php
declare(strict_types=1);

// ============================================================
// 玩家头像系统
// ------------------------------------------------------------
// 本游戏是纯文本 WAP（等宽字体 + 颜色），头像用"可渲染的徽章"实现：
// 一段 HTML（CSS 渐变底 + 一个字形/符号），无需图片文件，随端自适应。
//
// 头像来源：
//   - 每个职业(5) 有 1 个初始头像；
//   - 每个转职分支(2觉，10 个) 各有 1 个专属头像；
//   - 玩家默认头像 = 当前职业（未 2 觉）或当前转职（已 2 觉）的头像；
//   - 玩家可手动挑选任何"已解锁"的头像（本职业 + 已转职分支），存 users.avatar。
//
// 头像 id 约定：
//   job:<jobId>        五大职业初始头像（warrior/mage/hunter/priest/summoner）
//   rb2:<rb2Key>       转职分支头像（berserker/aegis/... 共 10 个）
// ============================================================

// 头像全表：id => [name, glyph, bg(渐变), fg(前景色), ring(描边色), job(归属职业，用于解锁/筛选)]
function avatar_catalog(): array
{
    return [
        // —— 五大职业初始头像 ——
        'job:warrior'  => ['name' => '战士·守夜', 'glyph' => '⚔', 'bg' => 'linear-gradient(135deg,#5a2b1e,#a8442a)', 'fg' => '#ffd9c0', 'ring' => '#e0743c', 'job' => 'warrior'],
        'job:mage'     => ['name' => '法师·抄书', 'glyph' => '✦', 'bg' => 'linear-gradient(135deg,#1b2a5a,#3f6fcf)', 'fg' => '#d8e6ff', 'ring' => '#5f9bff', 'job' => 'mage'],
        'job:hunter'   => ['name' => '猎手·追风', 'glyph' => '➹', 'bg' => 'linear-gradient(135deg,#1f3a24,#3f8a4a)', 'fg' => '#d6ffd9', 'ring' => '#54cf6a', 'job' => 'hunter'],
        'job:priest'   => ['name' => '牧师·圣恩', 'glyph' => '✚', 'bg' => 'linear-gradient(135deg,#4a4a1e,#c9b23a)', 'fg' => '#fff7d0', 'ring' => '#e6cf4a', 'job' => 'priest'],
        'job:summoner' => ['name' => '召唤·深渊', 'glyph' => '⟁', 'bg' => 'linear-gradient(135deg,#2a1b3a,#7a3fae)', 'fg' => '#ecd8ff', 'ring' => '#a45fdf', 'job' => 'summoner'],

        // —— 战士转职 ——
        'rb2:berserker' => ['name' => '狂战士·血怒', 'glyph' => '⚒', 'bg' => 'linear-gradient(135deg,#4a0f0f,#c62d2d)', 'fg' => '#ffd0d0', 'ring' => '#ff5a5a', 'job' => 'warrior'],
        'rb2:aegis'     => ['name' => '铁卫·磐壁', 'glyph' => '⛨', 'bg' => 'linear-gradient(135deg,#2f3540,#7f8da3)', 'fg' => '#e7edf7', 'ring' => '#9fb0c8', 'job' => 'warrior'],

        // —— 法师转职 ——
        'rb2:wintermage' => ['name' => '凛冬法师·冰雷', 'glyph' => '❄', 'bg' => 'linear-gradient(135deg,#123a4a,#2f8fc4)', 'fg' => '#d8f4ff', 'ring' => '#5fd0ff', 'job' => 'mage'],
        'rb2:wildfire'   => ['name' => '燎原使者·烈焰', 'glyph' => '✹', 'bg' => 'linear-gradient(135deg,#5a1f0a,#e6681a)', 'fg' => '#ffe2c0', 'ring' => '#ff9440', 'job' => 'mage'],

        // —— 猎手转职 ——
        'rb2:ranger'   => ['name' => '游侠·毒雨', 'glyph' => '❦', 'bg' => 'linear-gradient(135deg,#1f3a1c,#5aa83a)', 'fg' => '#e0ffcf', 'ring' => '#7ed24a', 'job' => 'hunter'],
        'rb2:assassin' => ['name' => '影刺客·潜行', 'glyph' => '☠', 'bg' => 'linear-gradient(135deg,#101018,#3a3a52)', 'fg' => '#cfd0ff', 'ring' => '#6a6adf', 'job' => 'hunter'],

        // —— 牧师转职 ——
        'rb2:shadowbishop' => ['name' => '暗影主教·血契', 'glyph' => '✝', 'bg' => 'linear-gradient(135deg,#2a0f2a,#8a2d6a)', 'fg' => '#f5d0ec', 'ring' => '#d24aa6', 'job' => 'priest'],
        'rb2:holybishop'   => ['name' => '神圣主教·圣光', 'glyph' => '✟', 'bg' => 'linear-gradient(135deg,#3a3410,#e8d24a)', 'fg' => '#fffbe0', 'ring' => '#ffe14d', 'job' => 'priest'],

        // —— 召唤师转职 ——
        'rb2:necro'  => ['name' => '死灵之拥·亡军', 'glyph' => '⚰', 'bg' => 'linear-gradient(135deg,#1a2a14,#4a7a2a)', 'fg' => '#d6f0c0', 'ring' => '#7ec24a', 'job' => 'summoner'],
        'rb2:plague' => ['name' => '痛苦盛宴·腐宴', 'glyph' => '❖', 'bg' => 'linear-gradient(135deg,#2a0f1a,#7a2a4a)', 'fg' => '#f0c0d8', 'ring' => '#d24a7a', 'job' => 'summoner'],
    ];
}

// 当前角色对应的"默认头像 id"：2觉后跟随转职，否则跟随五大职业
function avatar_default_id(array $u): string
{
    $rb2 = (string) (cflags((int) ($u['id'] ?? 0))['rb2_job'] ?? '');
    if ($rb2 !== '' && isset(avatar_catalog()['rb2:' . $rb2])) {
        return 'rb2:' . $rb2;
    }
    $job = job_id_of($u);
    if (isset(avatar_catalog()['job:' . $job])) {
        return 'job:' . $job;
    }
    return 'job:warrior';
}

// 该角色已解锁的头像 id 列表：
//   - 本职业初始头像 job:<job> 永远解锁；
//   - 转职分支头像 rb2:<key> 只有在玩家真正选了该 2 觉方向后才解锁。
function avatar_unlocked_ids(array $u): array
{
    $ids = [];
    $job = job_id_of($u);
    if (isset(avatar_catalog()['job:' . $job])) {
        $ids[] = 'job:' . $job;
    }
    $rb2 = (string) (cflags((int) ($u['id'] ?? 0))['rb2_job'] ?? '');
    if ($rb2 !== '' && isset(avatar_catalog()['rb2:' . $rb2]) && (avatar_catalog()['rb2:' . $rb2]['job'] ?? '') === $job) {
        $ids[] = 'rb2:' . $rb2;
    }
    return $ids;
}

// 角色当前实际生效的头像 id：优先用户自选（需已解锁），否则用默认
function avatar_of(array $u): string
{
    $chosen = (string) ($u['avatar'] ?? '');
    if ($chosen !== '' && isset(avatar_catalog()[$chosen])) {
        // 只允许用本职业/已转职分支解锁的头像
        if (in_array($chosen, avatar_unlocked_ids($u), true)) {
            return $chosen;
        }
    }
    return avatar_default_id($u);
}

// 直接按头像 id 渲染徽章（不校验解锁，供选择页展示用）
function avatar_render(string $id, int $size = 40): string
{
    $cat = avatar_catalog();
    $a = $cat[$id] ?? $cat['job:warrior'];
    $fs = (int) round($size * 0.5);
    $glyph = h((string) $a['glyph']);
    return '<span title="' . h((string) $a['name']) . '" style="display:inline-flex;align-items:center;justify-content:center;'
        . 'width:' . $size . 'px;height:' . $size . 'px;border-radius:' . ($size >= 32 ? '10px' : '8px') . ';'
        . 'background:' . $a['bg'] . ';color:' . $a['fg'] . ';border:1px solid ' . $a['ring'] . ';'
        . 'box-shadow:0 0 6px ' . $a['ring'] . '66;font-size:' . $fs . 'px;line-height:1;'
        . 'vertical-align:middle;font-weight:bold">' . $glyph . '</span>';
}

// 渲染头像徽章（HTML）。$size 为像素边长。
function avatar_html(array $u, int $size = 40): string
{
    return avatar_render(avatar_of($u), $size);
}

// 头像 + 名字（聊天/榜单/查看他人等紧凑场景用）
function avatar_name_html(array $u, int $size = 22): string
{
    return avatar_html($u, $size) . ' ' . sin_name_html((string) ($u['username'] ?? ''), sin_of((int) ($u['id'] ?? 0)));
}

// 按 uid 取头像徽章（带内存缓存，聊天/榜单批量渲染用，避免逐条查库）
function avatar_by_uid_html(int $uid, int $size = 22): string
{
    static $cache = [];
    if (!array_key_exists($uid, $cache)) {
        $row = user_by_id($uid);
        $cache[$uid] = $row ?: null;
    }
    $row = $cache[$uid];
    if (!$row) {
        return '';
    }
    return avatar_html($row, $size);
}

// 按 uid 渲染"头像+名字"（带缓存）
function avatar_name_by_uid_html(int $uid, string $name, int $size = 22): string
{
    return avatar_by_uid_html($uid, $size) . ' ' . sin_name_html($name, sin_of($uid));
}

// 切换头像（校验解锁）。返回提示文案。
function avatar_set(int $uid, string $id): string
{
    $u = user_by_id((int) $uid);
    if (!$u) {
        return '角色不存在。';
    }
    if ($id === '') {
        // 清空 = 恢复默认（跟随职业/转职）
        $u['avatar'] = '';
        user_save($u);
        return '已恢复默认头像（跟随当前职业/转职）。';
    }
    $cat = avatar_catalog();
    if (!isset($cat[$id])) {
        return '没有这个头像。';
    }
    if (!in_array($id, avatar_unlocked_ids($u), true)) {
        return '这个头像还没解锁（需要选择对应的转职方向）。';
    }
    $u['avatar'] = $id;
    user_save($u);
    return '头像已更换为【' . $cat[$id]['name'] . '】。';
}
