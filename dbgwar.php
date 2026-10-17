<?php
require 'includes/bootstrap.php';
// 造两个公会+wars，测war_banner对非成员
$u = user_by_id((int) db()->query("SELECT id FROM users ORDER BY id ASC LIMIT 1")->fetchColumn());
echo 'sess uid exist? ', isset($_SESSION['uid']) ? 'yes' : 'no', "\n";
echo 'war_banner(null)=', war_banner(null), "\n";
echo 'war_banner(该用户uid)=', war_banner((int) $u['id']), "\n";
echo 'my_guild=', json_encode(my_guild((int) $u['id']), JSON_UNESCAPED_UNICODE), "\n";
$w = db()->query('SELECT a_gid,b_gid FROM wars WHERE status="open" ORDER BY id DESC LIMIT 1')->fetch();
echo 'open war: ', json_encode($w), "\n";
