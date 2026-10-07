<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

$u = require_login();
$here = loc((string) $u['loc']);

wap_start('状态');
echo h($u['username']) . '　' . (int) $u['lv'] . '级';
echo '　' . h(job_of($u)['name']) . '(' . h(job_of($u)['skill']) . ')';
echo '<br>';
echo '<span class="hp">生命 ' . (int) $u['hp'] . '/' . (int) $u['maxhp'] . '</span><br>';
echo '<span style="color:#6cf">魔力 ' . (int) ($u['mp'] ?? 0) . '/' . (int) ($u['maxmp'] ?? 0) . '</span><br>';
echo '攻击 ' . player_atk($u) . '　防御 ' . player_def($u) . '<br>';
echo '<span class="gold">' . h(fmt_money((int) $u['gold'])) . '</span><br>';
echo '经验 ' . (int) $u['exp'] . '/' . exp_need((int) $u['lv']) . '<br>';
$st = db()->prepare('SELECT * FROM equips WHERE uid=? AND pos="wear"');
$st->execute([(int) $u['id']]);
$worn = $st->fetchAll();
if ($worn !== []) {
    echo '穿戴：<br>';
    $qcolor = ['#999', '#fff', '#6cf', '#c6f', '#fc3'];
    foreach ($worn as $e) {
        echo '·[' . h(equip_slots()[$e['slot']] ?? '') . ']<a href="equip.php?id=' . $e['id'] . '"><span style="color:' . $qcolor[(int) $e['quality']] . '">' . h(equip_shortname($e['name'])) . '</span></a><br>';
    }
    $gs = gear_stats((int) $u['id']);
    echo '<span class="muted">共加成：攻+' . (int) $gs['atk'] . ' 防+' . (int) $gs['def'] . ' 命+' . (int) $gs['hp'];
    foreach (['crit' => '暴击', 'critdmg' => '爆伤', 'dodge' => '闪避', 'lifesteal' => '吸血'] as $k => $n) {
        if ($gs[$k] > 0) {
            echo ' ' . $n . $gs[$k] . '%';
        }
    }
    echo '</span><br>';
}
echo '回血药：' . (int) $u['potion'] . '<br>';
echo '所在：' . h($here['name']);
nav_line();
wap_end(false);
