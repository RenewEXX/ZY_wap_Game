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
    echo '<a href="status.php">状态</a> . ';
    echo '<a href="map.php">地图</a> . ';
    echo '<a href="bag.php">行囊</a> . ';
    echo '<a href="logout.php">离开</a>';
}
