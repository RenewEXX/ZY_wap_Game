<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

$u = require_login();
$a = (string) ($_GET['a'] ?? '');
$mid = (string) ($_GET['m'] ?? '');
$allm = monsters();

if ((int) $u['hp'] <= 0) {
    $u['hp'] = 1;
    $u['loc'] = 'camp';
    $u['gold'] = max(0, (int) $u['gold'] - 5);
    user_save($u);
    unset($_SESSION['battle']);
    flash_set('你被拖回营地。丢了5金。');
    header('Location: home.php');
    exit;
}

if ($a === 'start' && isset($allm[$mid])) {
    $here = loc((string) $u['loc']);
    if (!in_array($mid, $here['monsters'], true)) {
        flash_set('这里没有那种东西。');
        header('Location: map.php');
        exit;
    }
    $m = $allm[$mid];
    $_SESSION['battle'] = [
        'id' => $mid,
        'name' => $m['name'],
        'hp' => $m['hp'],
        'maxhp' => $m['hp'],
        'atk' => $m['atk'],
        'exp' => $m['exp'],
        'gold' => $m['gold'],
        'log' => '你撞上了' . $m['name'] . '。',
    ];
    header('Location: fight.php');
    exit;
}

$b = $_SESSION['battle'] ?? null;
if (!is_array($b)) {
    header('Location: map.php');
    exit;
}

$log = (string) $b['log'];

if ($a === 'hit') {
    $pd = dmg_to_monster($u);
    $b['hp'] = (int) $b['hp'] - $pd;
    $log = '你劈出' . $pd . '点。';
    if ((int) $b['hp'] <= 0) {
        $g = (int) $b['gold'] + random_int(0, 2);
        $u['gold'] = (int) $u['gold'] + $g;
        $msg = gain_exp($u, (int) $b['exp']);
        user_save($u);
        unset($_SESSION['battle']);
        flash_set($b['name'] . '散了。' . $msg . '，金+' . $g);
        header('Location: map.php');
        exit;
    }
    $md = dmg_to_player($u, $b);
    $u['hp'] = (int) $u['hp'] - $md;
    $log .= $b['name'] . '回击' . $md . '点。';
    user_save($u);
    if ((int) $u['hp'] <= 0) {
        $u['hp'] = 1;
        $u['loc'] = 'camp';
        $u['gold'] = max(0, (int) $u['gold'] - 5);
        user_save($u);
        unset($_SESSION['battle']);
        flash_set('你倒了。被拖回营地，金-5。');
        header('Location: home.php');
        exit;
    }
    $b['log'] = $log;
    $_SESSION['battle'] = $b;
    header('Location: fight.php');
    exit;
}

if ($a === 'run') {
    unset($_SESSION['battle']);
    flash_set('你撤了。没人笑话你，因为笑话你的多半已经死了。');
    header('Location: map.php');
    exit;
}

if ($a === 'drink' && (int) $u['potion'] > 0) {
    $u['potion'] = (int) $u['potion'] - 1;
    $heal = min(20, (int) $u['maxhp'] - (int) $u['hp']);
    $u['hp'] = (int) $u['hp'] + $heal;
    user_save($u);
    $b['log'] = '你喝下一瓶药，回复' . $heal . '。';
    $_SESSION['battle'] = $b;
    header('Location: fight.php');
    exit;
}

wap_start('交手');
echo '敌人：' . h((string) $b['name']) . '<br>';
echo '<span class="hp">敌生命 ' . (int) $b['hp'] . '/' . (int) $b['maxhp'] . '</span><br>';
echo '<span class="hp">你 ' . (int) $u['hp'] . '/' . (int) $u['maxhp'] . '</span>';
echo '<div class="hr">--------</div>';
echo h($log);
echo '<div class="hr">--------</div>';
echo '<a href="fight.php?a=hit">下手</a><br>';
if ((int) $u['potion'] > 0) {
    echo '<a href="fight.php?a=drink">喝药 (' . (int) $u['potion'] . ')</a><br>';
}
echo '<a href="fight.php?a=run">撤</a>';
nav_line();
wap_end(false);
