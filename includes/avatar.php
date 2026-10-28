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
//   - 每个一觉(1转，5 个) 有 1 个专属头像；
//   - 每个转职分支(2觉，10 个) 各有 1 个专属头像；
//   - 五大职业的"二次元头像"在商城付费解锁（跨职业可用）；
//   - 玩家默认头像 = 当前 2觉 > 1觉 > 职业 的头像；
//   - 玩家可手动挑选任何"已解锁"的头像（本职业 + 已转职分支 + 已购二次元），存 users.avatar。
//
// 头像 id 约定：
//   job:<jobId>        五大职业初始头像（warrior/mage/hunter/priest/summoner）
//   rb1:<jobId>        一觉头像（5 个，1 觉后解锁）
//   rb2:<rb2Key>       转职分支头像（berserker/aegis/... 共 10 个）
//   anime:<jobId>      二次元头像（商城付费解锁，5 个，跨职业可用）
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

        // —— 一觉（一转）专属头像：更鲜艳、带辉光 ——
        'rb1:warrior'  => ['name' => '一觉·狂战士', 'glyph' => '⚔', 'bg' => 'radial-gradient(circle at 30% 20%,#ff7a4a,#3a0808)', 'fg' => '#ffe6d0', 'ring' => '#ff4a2a', 'job' => 'warrior'],
        'rb1:mage'     => ['name' => '一觉·元素使', 'glyph' => '✵', 'bg' => 'radial-gradient(circle at 30% 20%,#7ad0ff,#0a1a4a)', 'fg' => '#eaf6ff', 'ring' => '#3fa0ff', 'job' => 'mage'],
        'rb1:hunter'   => ['name' => '一觉·追猎者', 'glyph' => '➹', 'bg' => 'radial-gradient(circle at 30% 20%,#8affa0,#0a3a1a)', 'fg' => '#e8ffe8', 'ring' => '#3aff7a', 'job' => 'hunter'],
        'rb1:priest'   => ['name' => '一觉·圣光牧师', 'glyph' => '✟', 'bg' => 'radial-gradient(circle at 30% 20%,#fff0a0,#4a3a08)', 'fg' => '#fffce0', 'ring' => '#ffdf3a', 'job' => 'priest'],
        'rb1:summoner' => ['name' => '一觉·缚魂使', 'glyph' => '⟁', 'bg' => 'radial-gradient(circle at 30% 20%,#d09aff,#2a0a4a)', 'fg' => '#f4e6ff', 'ring' => '#b04aff', 'job' => 'summoner'],

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

        // —— 二次元头像（商城付费解锁，跨职业也能用）——
        'anime:warrior'  => ['name' => '二次元·战士', 'glyph' => '★', 'bg' => 'linear-gradient(135deg,#ff9ec4,#ff5e7e,#7a2bd0)', 'fg' => '#ffffff', 'ring' => '#ff9ec4', 'job' => 'warrior', 'shop' => true],
        'anime:mage'     => ['name' => '二次元·法师', 'glyph' => '☆', 'bg' => 'linear-gradient(135deg,#a0f6ff,#5fd0ff,#6a6aff)', 'fg' => '#ffffff', 'ring' => '#a0f6ff', 'job' => 'mage', 'shop' => true],
        'anime:hunter'   => ['name' => '二次元·猎手', 'glyph' => '✿', 'bg' => 'linear-gradient(135deg,#d6ffb0,#7ee06a,#22b06a)', 'fg' => '#ffffff', 'ring' => '#b6ff7a', 'job' => 'hunter', 'shop' => true],
        'anime:priest'   => ['name' => '二次元·牧师', 'glyph' => '♥', 'bg' => 'linear-gradient(135deg,#ffe6a0,#ffd05f,#ff7ab0)', 'fg' => '#ffffff', 'ring' => '#ffe0a0', 'job' => 'priest', 'shop' => true],
        'anime:summoner' => ['name' => '二次元·召唤', 'glyph' => '✧', 'bg' => 'linear-gradient(135deg,#e0c0ff,#b07aff,#6a3aff)', 'fg' => '#ffffff', 'ring' => '#d0a0ff', 'job' => 'summoner', 'shop' => true],
    ];
}

// 商城二次元头像是否已购买（存 mats：avatar_anime_<job>）
function avatar_anime_owned(int $uid, string $job): bool
{
    return (int) (mats_of((int) $uid)['avatar_anime_' . $job] ?? 0) > 0;
}

// 五大职业 id 列表（用于二次元头像）
function avatar_classes(): array
{
    return ['warrior', 'mage', 'hunter', 'priest', 'summoner'];
}

// 商城二次元头像商品表：商品 key => [name, job, price(魔钻)]
function avatar_anime_goods(): array
{
    $names = ['warrior' => '战士', 'mage' => '法师', 'hunter' => '猎手', 'priest' => '牧师', 'summoner' => '召唤师'];
    $out = [];
    foreach (avatar_classes() as $j) {
        $out['avatar_anime_' . $j] = [
            'name' => '二次元头像·' . ($names[$j] ?? $j),
            'job' => $j,
            'price' => 200,
        ];
    }
    return $out;
}

// 授予某个二次元头像（购买后调用）
function avatar_anime_grant(int $uid, string $job): void
{
    if (!in_array($job, avatar_classes(), true)) {
        return;
    }
    mat_set((int) $uid, 'avatar_anime_' . $job, 1);
}

// 当前角色对应的"默认头像 id"：2觉 → 1觉 → 五大职业，逐级回退
function avatar_default_id(array $u): string
{
    $flags = cflags((int) ($u['id'] ?? 0));
    $rb2 = (string) ($flags['rb2_job'] ?? '');
    if ($rb2 !== '' && isset(avatar_catalog()['rb2:' . $rb2])) {
        return 'rb2:' . $rb2;
    }
    $rb1 = (string) ($flags['rb1_job'] ?? '');
    if ($rb1 !== '' && isset(avatar_catalog()['rb1:' . $rb1])) {
        return 'rb1:' . $rb1;
    }
    $job = job_id_of($u);
    if (isset(avatar_catalog()['job:' . $job])) {
        return 'job:' . $job;
    }
    return 'job:warrior';
}

// 该角色已解锁的头像 id 列表：
//   - 本职业初始头像 job:<job> 永远解锁；
//   - 一觉头像 rb1:<job>：完成 1 觉后解锁；
//   - 转职分支头像 rb2:<key>：只有选了该 2 觉方向才解锁；
//   - 二次元头像 anime:<job>：在商城付费购买后解锁（**本职业专属**，不能跨职业使用）。
function avatar_unlocked_ids(array $u): array
{
    $ids = [];
    $uid = (int) ($u['id'] ?? 0);
    $flags = cflags($uid);
    $job = job_id_of($u);
    if (isset(avatar_catalog()['job:' . $job])) {
        $ids[] = 'job:' . $job;
    }
    $rb1 = (string) ($flags['rb1_job'] ?? '');
    if ($rb1 !== '' && isset(avatar_catalog()['rb1:' . $rb1])) {
        $ids[] = 'rb1:' . $rb1;
    }
    $rb2 = (string) ($flags['rb2_job'] ?? '');
    if ($rb2 !== '' && isset(avatar_catalog()['rb2:' . $rb2]) && (avatar_catalog()['rb2:' . $rb2]['job'] ?? '') === $job) {
        $ids[] = 'rb2:' . $rb2;
    }
    // 二次元头像：本职业专属（只解锁自己职业那一个）
    if (isset(avatar_catalog()['anime:' . $job]) && avatar_anime_owned($uid, $job)) {
        $ids[] = 'anime:' . $job;
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

// 二次元头像：内联 Q 版动漫少女（纯 SVG，无需图片文件），按职业区分发色/瞳孔/服饰/配饰
function avatar_chibi_svg(string $job, int $size = 40): string
{
    static $seq = 0;
    $seq++;
    $gid = 'av' . $seq;
    $p = [
        'warrior'  => ['bg1' => '#ffd6e0', 'bg2' => '#ff8fb0', 'hair' => '#ff6b4a', 'hair2' => '#c63a1f', 'eye' => '#e8462a', 'skin' => '#ffe3cc', 'outfit' => '#7a1f2a', 'ribbon' => '#ffe14d', 'acc' => 'sword'],
        'mage'     => ['bg1' => '#d9efff', 'bg2' => '#93c6ff', 'hair' => '#8fb8ff', 'hair2' => '#5f7fe0', 'eye' => '#3f6fd0', 'skin' => '#ffe8da', 'outfit' => '#2a3f8a', 'ribbon' => '#ffe14d', 'acc' => 'star'],
        'hunter'   => ['bg1' => '#dcffd8', 'bg2' => '#93e88c', 'hair' => '#7be08a', 'hair2' => '#3aa04a', 'eye' => '#2a8a3a', 'skin' => '#ffe3cc', 'outfit' => '#1f5a2a', 'ribbon' => '#ffe14d', 'acc' => 'leaf'],
        'priest'   => ['bg1' => '#fff2c6', 'bg2' => '#ffd76a', 'hair' => '#ffe08a', 'hair2' => '#e0b03a', 'eye' => '#c99a2a', 'skin' => '#ffe8d0', 'outfit' => '#8a7a20', 'ribbon' => '#ff9ec4', 'acc' => 'halo'],
        'summoner' => ['bg1' => '#eed9ff', 'bg2' => '#b98aff', 'hair' => '#c58aff', 'hair2' => '#8a3adf', 'eye' => '#8a3adf', 'skin' => '#f1ddff', 'outfit' => '#3a1a5a', 'ribbon' => '#ffe14d', 'acc' => 'horns'],
    ][$job] ?? null;
    if ($p === null) {
        $p = ['bg1' => '#eee', 'bg2' => '#bbb', 'hair' => '#666', 'hair2' => '#444', 'eye' => '#333', 'skin' => '#ffe0c8', 'outfit' => '#555', 'ribbon' => '#f5c', 'acc' => 'star'];
    }
    $r = $size >= 32 ? 10 : 8;
    $s = '';
    $s .= '<svg width="' . $size . '" height="' . $size . '" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg" style="display:block;border-radius:' . $r . 'px">';
    $s .= '<defs><linearGradient id="' . $gid . '" x1="0" y1="0" x2="1" y2="1">'
        . '<stop offset="0" stop-color="' . $p['bg1'] . '"/><stop offset="1" stop-color="' . $p['bg2'] . '"/></linearGradient></defs>';
    $s .= '<rect width="100" height="100" rx="' . ($r + 4) . '" fill="url(#' . $gid . ')"/>';
    // 身体/衣领
    $s .= '<path d="M16 100 Q26 72 50 72 Q74 72 84 100 Z" fill="' . $p['outfit'] . '"/>';
    $s .= '<path d="M40 74 L50 86 L60 74 Z" fill="' . $p['ribbon'] . '" opacity="0.85"/>';
    // 脖子
    $s .= '<rect x="44" y="60" width="12" height="16" rx="5" fill="' . $p['skin'] . '"/>';
    // 后发
    $s .= '<ellipse cx="50" cy="46" rx="34" ry="35" fill="' . $p['hair2'] . '"/>';
    // 脸
    $s .= '<ellipse cx="50" cy="48" rx="25" ry="26" fill="' . $p['skin'] . '"/>';
    // 耳朵
    $s .= '<circle cx="26" cy="50" r="5" fill="' . $p['skin'] . '"/><circle cx="74" cy="50" r="5" fill="' . $p['skin'] . '"/>';
    // 刘海
    $s .= '<path d="M24 46 Q26 16 50 16 Q74 16 76 46 Q71 33 62 30 Q54 28 50 33 Q46 28 38 30 Q29 33 24 46 Z" fill="' . $p['hair'] . '"/>';
    // 两侧发丝
    $s .= '<path d="M24 46 Q20 62 26 76 Q30 62 30 50 Z" fill="' . $p['hair'] . '"/>';
    $s .= '<path d="M76 46 Q80 62 74 76 Q70 62 70 50 Z" fill="' . $p['hair'] . '"/>';
    // 眼睛（大眼 + 白眼球 + 彩色虹膜 + 高光）
    foreach ([38, 62] as $ex) {
        $s .= '<ellipse cx="' . $ex . '" cy="52" rx="6.2" ry="7.6" fill="#ffffff"/>';
        $s .= '<ellipse cx="' . $ex . '" cy="53" rx="4.2" ry="5.6" fill="' . $p['eye'] . '"/>';
        $s .= '<ellipse cx="' . $ex . '" cy="54.4" rx="1.9" ry="2.4" fill="#1a1020"/>';
        $s .= '<circle cx="' . ($ex - 2.0) . '" cy="49.6" r="1.7" fill="#ffffff"/>';
    }
    // 刘海高光
    $s .= '<path d="M33 27 Q41 23 52 25" stroke="#ffffff" stroke-width="2" opacity="0.45" fill="none" stroke-linecap="round"/>';
    // 腮红
    $s .= '<ellipse cx="32" cy="62" rx="5.2" ry="3.1" fill="#ff8aa0" opacity="0.55"/>';
    $s .= '<ellipse cx="68" cy="62" rx="5.2" ry="3.1" fill="#ff8aa0" opacity="0.55"/>';
    // 嘴（微笑）
    $s .= '<path d="M46 66 Q50 70.5 54 66" stroke="#c8577a" stroke-width="2" fill="none" stroke-linecap="round"/>';
    // 配饰（职业区分）
    switch ($p['acc']) {
        case 'sword':
            $s .= '<g transform="translate(80,12) rotate(38)"><rect x="-2.2" y="0" width="4.4" height="24" rx="2" fill="#e6eefc"/><rect x="-7" y="21" width="14" height="4" rx="2" fill="' . $p['ribbon'] . '"/><rect x="-2.2" y="25" width="4.4" height="8" rx="2" fill="#8a5a2a"/></g>';
            break;
        case 'star':
            $s .= '<g transform="translate(75,22) scale(0.95)"><path d="M0,-8 L2.35,-2.47 L8,-2.47 L3.53,1 L5.35,6.47 L0,3 L-5.35,6.47 L-3.53,1 L-8,-2.47 L-2.35,-2.47 Z" fill="' . $p['ribbon'] . '" stroke="#fff8d0" stroke-width="0.8"/></g>';
            break;
        case 'leaf':
            $s .= '<path d="M70 20 Q82 10 92 18 Q82 30 70 20 Z" fill="#4ac06a"/><path d="M71 20 Q82 19 90 18" stroke="#2a8a3a" stroke-width="1.4" fill="none"/>';
            break;
        case 'halo':
            $s .= '<ellipse cx="50" cy="13" rx="16" ry="4.2" fill="none" stroke="' . $p['ribbon'] . '" stroke-width="3" opacity="0.95"/>';
            break;
        case 'horns':
            $s .= '<path d="M30 26 Q24 12 33 9 Q34 20 39 24 Z" fill="' . $p['ribbon'] . '"/>';
            $s .= '<path d="M70 26 Q76 12 67 9 Q66 20 61 24 Z" fill="' . $p['ribbon'] . '"/>';
            break;
    }
    $s .= '</svg>';
    return $s;
}

// 直接按头像 id 渲染徽章（不校验解锁，供选择页展示用）
function avatar_render(string $id, int $size = 40): string
{
    $cat = avatar_catalog();
    $a = $cat[$id] ?? $cat['job:warrior'];
    // 二次元头像：渲染 Q 版动漫少女
    if (!empty($a['shop'])) {
        return '<span title="' . h((string) $a['name']) . '" style="display:inline-block;vertical-align:middle;line-height:0">'
            . avatar_chibi_svg((string) ($a['job'] ?? 'warrior'), $size) . '</span>';
    }
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
