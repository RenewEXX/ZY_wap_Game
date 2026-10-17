<?php
$p = __DIR__ . '/includes/game.php';
$t = file_get_contents($p);
// 根因调查：17的maxhp异常大？gear hp vs users maxhp
require __DIR__ . '/includes/bootstrap.php';
$u = user_by_id(17);
echo 'users.maxhp=', $u['maxhp'], ' hp=', $u['hp'], "\n";
echo 'gear hp=', gear_stats(17)['hp'], "\n";
$st = db()->prepare('SELECT slot, name, pos FROM equips WHERE uid=? AND pos="wear"');
$st->execute([17]);
foreach ($st->fetchAll() as $r) { echo $r['slot'], ':', mb_substr($r['name'], 0, 20), "\n"; }
