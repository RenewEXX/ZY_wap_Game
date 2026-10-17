<?php
require __DIR__ . '/includes/bootstrap.php';
$u = user_by_id(17);
echo 'st: hp=', $u['hp'], ' maxhp=', $u['maxhp'], ' lv=', $u['lv'], "\n";
$w = title_worn(17);
echo 'worn: ', $w ? $w['name'] . '#' . $w['id'] : 'NONE', "\n";
