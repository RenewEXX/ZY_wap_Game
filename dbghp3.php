<?php
require __DIR__ . '/includes/bootstrap.php';
echo take_off_equip(17, 507), "\n";
$u = user_by_id(17);
echo 'after off: hp=', $u['hp'], ' maxhp=', $u['maxhp'], "\n";
echo wear_title(17, 507), "\n";
$u = user_by_id(17);
echo 'after wear: hp=', $u['hp'], ' maxhp=', $u['maxhp'], "\n";
echo take_off_equip(17, 507), "\n";
$u = user_by_id(17);
echo 'after off2: hp=', $u['hp'], ' maxhp=', $u['maxhp'], "\n";
