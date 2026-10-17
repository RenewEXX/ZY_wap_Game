<?php
require 'includes/bootstrap.php';
$all = locations();
foreach (['sy_gate', 'sy_pit'] as $m) {
    echo $m, isset($all[$m]) ? 'OK' : 'MISS', ' | ', $all[$m]['name'] ?? '', ' | ', implode(',', $all[$m]['monsters'] ?? []), "\n";
}
echo 'dungeon_maps has sy: ', in_array('sy_pit', dungeon_maps(), true) ? 'yes' : 'no', "\n";
echo 'boss sy_pit: ', boss_of_map('sy_pit'), "\n";
echo 'route god_hall->sy_pit: ', implode('>', find_route('god_hall', 'sy_pit')), "\n";