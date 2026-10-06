<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

$u = require_login();
$to = (string) ($_GET['to'] ?? '');
$all = locations();

if ($to !== '' && isset($all[$to])) {
    $here = loc((string) $u['loc']);
    if (isset($here['exits'][$to]) || $to === $u['loc']) {
        $u['loc'] = $to;
        user_save($u);
        if ($to === 'shop') {
            header('Location: shop.php');
            exit;
        }
        if ($to === 'camp') {
            header('Location: rest.php');
            exit;
        }
        header('Location: map.php');
        exit;
    }
}

$here = loc((string) $u['loc']);
wap_start($here['name']);
echo '<div class="muted">' . h($here['desc']) . '</div>';
echo '<div class="hr">--------</div>';

if ($here['monsters'] !== []) {
    echo '此处可遇：<br>';
    foreach ($here['monsters'] as $mid) {
        $m = monsters()[$mid];
        echo '· <a href="fight.php?a=start&m=' . h($mid) . '">' . h($m['name']) . '</a><br>';
    }
    echo '<div class="hr">--------</div>';
}

echo '去往：<br>';
foreach ($here['exits'] as $id => $name) {
    echo '· <a href="map.php?to=' . h($id) . '">' . h($name) . '</a><br>';
}
nav_line();
wap_end(false);
