<?php
$t = file_get_contents('includes/game.php');
// tt全部怪+drops全列
foreach (['tt_rock', 'tt_magma', 'tt_warden', 'tt_titan', 'tt_core', 'tt_trail', 'tt_vein'] as $k) {
    echo $k, ': ', substr_count($t, "'$k'"), "\n";
}
$p = strpos($t, "'tt_rock2'");
echo substr($t, max(0, $p - 200), 400), "\n";
