<?php
// 公会战公告只发给参战双方成员
$p = 'includes/game.php';
$t = file_get_contents($p);
$a = <<<'EOT'
function war_banner(): string
{
    $st = db()->query('SELECT a_gid, b_gid, ends_at FROM wars WHERE status="open" ORDER BY id DESC LIMIT 1');
    $w = $st->fetch();
    if (!$w) {
        return '';
    }
EOT;
if (strpos($t, $a) === false) { echo "MISS\n"; exit(1); }
$b = <<<'EOT'
function war_banner(?int $uid = null): string
{
    $st = db()->query('SELECT a_gid, b_gid, ends_at FROM wars WHERE status="open" ORDER BY id DESC LIMIT 1');
    $w = $st->fetch();
    if (!$w) {
        return '';
    }
    if ($uid !== null) {
        $g = my_guild((int) $uid);
        $gid = (int) ($g['id'] ?? 0);
        if ($gid !== (int) $w['a_gid'] && $gid !== (int) $w['b_gid']) {
            return '';
        }
    }
EOT;
$t = str_replace($a, $b, $t);
file_put_contents($p, $t);
echo "OK\n";
