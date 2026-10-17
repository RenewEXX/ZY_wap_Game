<?php
require 'includes/bootstrap.php';
foreach (['dusk', 'ann', 'dev', 'god', 'sin'] as $s) {
    $pc = equip_set_pieces($s);
    echo str_pad($s, 6), is_array($pc) ? count($pc) . '件' : 'BAD', "\n";
}
echo "\n", equip_set_bonus_text('ann', 5), "\n\n";
echo equip_set_bonus_text('dev', 5), "\n\n";
echo equip_set_bonus_text('dusk', 3), "\n";