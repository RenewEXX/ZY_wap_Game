<?php
declare(strict_types=1);

function h(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function wap_start(string $title): void
{
    echo '<!DOCTYPE html><html lang="zh-CN"><head><meta charset="utf-8">';
    echo '<meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1">';
    echo '<title>' . h($title) . ' - 罪渊</title><style>
body{margin:8px;background:#000;color:#cfc;font:13px/1.5 "SimSun","Songti SC",monospace}
a{color:#8cf;text-decoration:none}
.t{color:#9f9;font-weight:bold}
.gold{color:#fc6}
.hp{color:#f88}
.muted{color:#696}
.hr{color:#363;margin:8px 0}
.warn{color:#fa6}
.nav{display:grid;grid-template-columns:repeat(4,1fr);gap:5px;margin:8px 0}
.nav a{display:block;text-align:center;background:#0d140d;border:1px solid #2a3a2a;color:#8c8;padding:7px 0;border-radius:8px;font-size:13px;white-space:nowrap;overflow:hidden}
.nav a:active{background:#1c2b1c}
.nav a.on{background:#1c2b1c;border-color:#6a6;color:#cfc}
input{background:#111;color:#cfc;border:1px solid #363;padding:4px;font:13px monospace}
</style></head><body>';
    echo '<div class="t">【' . h($title) . '】</div>';
}

function wap_end(bool $home = true): void
{
    echo '<div class="hr">--------</div>';
    if ($home) {
        echo '<a href="home.php">返回营地</a>';
    }
    echo '</body></html>';
}

function require_login(): array
{
    if (empty($_SESSION['uid'])) {
        header('Location: index.php');
        exit;
    }
    $u = user_by_id((int) $_SESSION['uid']);
    if (!$u) {
        $_SESSION = [];
        header('Location: index.php');
        exit;
    }
    if ((int) ($u['maxmp'] ?? 0) <= 0) {
        $jmp = (int) (job_of($u)['mp'] ?? 30);
        $u['maxmp'] = $jmp;
        $u['mp'] = $jmp;
        user_save($u);
    }
    background_battle($u);
    offline_tick($u);
    dummy_tick($u);
    dungeon_tick($u);
    settle_auctions();
    settle_wars();
    settle_war_rewards();
    spawn_tick();
    settle_horse();
    // 一次性迁移：旧武器栏全部转成新装备
    $oldmap = [
        'rusty' => ['weapon', '生锈铁剑', 1], 'bow' => ['weapon', '猎弓', 1],
        'staff' => ['weapon', '桦木法杖', 1], 'mace' => ['weapon', '白木槌', 1],
        'sickle' => ['weapon', '草药镰', 1], 'boneclub' => ['weapon', '大骨棒', 1],
        'spike' => ['weapon', '铁刺', 2], 'brand' => ['weapon', '罪纹短剑', 2],
        'cloth' => ['body', '破布甲', 1], 'bone' => ['body', '骨甲', 2],
    ];
    $migrated = [];
    foreach (['weapon', 'armor'] as $col) {
        $oid = (string) ($u[$col] ?? '');
        if ($oid !== '' && isset($oldmap[$oid])) {
            [$slot, $base, $q] = $oldmap[$oid];
            $migrated[] = make_equip((int) $u['id'], $slot, $base, $q);
            $u[$col] = '';
        }
    }
    // 兼容更早的残留：boneclub 特殊提示过
    if (($u['weapon'] ?? '') === 'boneclub') {
        $migrated[] = make_equip((int) $u['id'], 'weapon', '大骨棒', 1);
        $u['weapon'] = '';
    }
    if ($migrated !== []) {
        user_save($u);
        flash_set('旧武器已换成背包装备：【' . implode('】【', $migrated) . '】，去穿上吧。');
    }
    return $u;
}

function flash_set(string $msg): void
{
    $_SESSION['flash'] = $msg;
}

function flash_get(): string
{
    $m = (string) ($_SESSION['flash'] ?? '');
    unset($_SESSION['flash']);
    return $m;
}

function nav_line(): void
{
    // 快捷栏：以后加按钮只在这里加一行，grid自动排成4列等宽
    $mailN = !empty($_SESSION['uid']) ? mail_unread((int) $_SESSION['uid']) : 0;
    $items = [
        ['home.php', '行动'], ['status.php', '状态'], ['bag.php', '背包'], ['map.php', '地图'],
        ['skills.php', '技能'], ['pet.php', '宠物'], ['quest.php', '任务'], ['mail.php', '邮件' . ($mailN > 0 ? '(' . $mailN . ')' : '')],
        ['teleport.php', '传送'], ['mall.php', '商城'], ['auction.php', '拍卖'], ['logout.php?a=char', '退出'],
        ['guild.php', '公会'], ['chat.php', '聊天'], ['party.php', '组队'], ['look.php', '观察'],
    ];
    $cur = basename((string) ($_SERVER['SCRIPT_NAME'] ?? ''));
    echo '<div class="nav">';
    foreach ($items as [$href, $label]) {
        $on = (explode('?', $href)[0] === $cur) ? ' class="on"' : '';
        echo '<a href="' . $href . '"' . $on . '>' . $label . '</a>';
    }
    echo '</div>';
    if (basename((string) ($_SERVER['SCRIPT_NAME'] ?? '')) !== 'fight.php') {
        echo '<script>setInterval(async()=>{try{const r=await fetch("ping.php",{credentials:"same-origin"});const d=await r.json();if(!d||!d.ok)return;for(const k of["flash","note"]){const t=d[k];if(t&&t!==window["_pz"+k]){window["_pz"+k]=t;const e=document.createElement("div");e.style.cssText="position:fixed;top:0;left:0;right:0;background:#321;color:#fc6;padding:6px;text-align:center;z-index:99";e.textContent=t;e.onclick=()=>e.remove();document.body.appendChild(e);setTimeout(()=>e.remove(),15000);}}}catch(e){}},30000);</script>';
    }
}

function quest_of(int $q): array
{
    $list = [
        0 => ['name' => '麦田驱逐', 'step' => '1/3', 'todo' => '去雾谷麦田，打倒哥布林哨兵x2', 'loc' => 'field', 'locname' => '雾谷麦田', 'hint' => '自动战斗，每1秒放技能。'],
        1 => ['name' => '老井哭声', 'step' => '2/3', 'todo' => '去老井，打倒雾中女妖（普攻减半，靠技能）', 'loc' => 'well', 'locname' => '老井', 'hint' => '记得开自动战斗，牧师可回血。'],
        2 => ['name' => '断桥巨影', 'step' => '3/3', 'todo' => '去断桥，打倒食人魔咕噜，拿黑币毕业', 'loc' => 'bridge', 'locname' => '断桥', 'hint' => '赢了送大骨棒。'],
        3 => ['name' => '雾散之后', 'step' => '完成', 'todo' => '新手毕业，去白石镇广场找冒险者公会', 'loc' => 'town_sq', 'locname' => '白石镇广场', 'hint' => '去公会看看。'],
    ];
    return $list[$q] ?? $list[3];
}

function quest_banner(array $u, string $page = 'home.php'): void
{
    $qs = quest_state($u);
    $prog = (($qs['ch'] ?? 0) === 2) ? quest_progress2_text((int) ($u['id'] ?? 0), $qs) : quest_progress_text((int) ($u['id'] ?? 0), $qs);
    echo '<div class="warn">【任务' . h($qs['step'] ?? '') . '·' . h($qs['name']) . '】' . h($qs['todo']);
    if ($prog !== '') {
        echo '<br>' . h($prog);
    }
    echo '<br>';
    echo '<a href="' . h($page) . '?go=' . h($qs['loc']) . '">【立即前往·' . h($qs['locname']) . '】</a></div>';
    echo '<div class="hr">--------</div>';
}
