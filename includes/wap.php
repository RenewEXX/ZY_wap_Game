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
    echo '<div class="hr">--------</div>';
    echo '<a href="home.php">行动</a> . ';
    echo '<a href="map.php">地图</a> . ';
    echo '<a href="teleport.php">传送</a> . ';
    echo '<a href="status.php">状态</a> . ';
    echo '<a href="bag.php">背包</a> . ';
    echo '<a href="quest.php">任务</a> . ';
    echo '<a href="logout.php">退出</a>';
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
    $prog = quest_progress_text((int) ($u['id'] ?? 0), $qs);
    echo '<div class="warn">【任务' . h($qs['step'] ?? '') . '·' . h($qs['name']) . '】' . h($qs['todo']);
    if ($prog !== '') {
        echo '<br>' . h($prog);
    }
    echo '<br>';
    echo '<a href="' . h($page) . '?go=' . h($qs['loc']) . '">【立即前往·' . h($qs['locname']) . '】</a></div>';
    echo '<div class="hr">--------</div>';
}
