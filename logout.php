<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';
// 离线即处理队伍：队员退队、队长解散
if (!empty($_SESSION['uid']) && function_exists('party_on_offline')) {
    party_on_offline((int) $_SESSION['uid']);
}

if ((string) ($_GET['a'] ?? '') === 'char' && !empty($_SESSION['account'])) {
    unset($_SESSION['uid']);
    header('Location: account.php');
    exit;
}
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], (bool) $p['secure'], (bool) $p['httponly']);
}
session_destroy();
header('Location: index.php');
exit;
