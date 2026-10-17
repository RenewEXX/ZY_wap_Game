<?php
$t = file_get_contents('includes/game.php');
// pz进入/计时：pz_enter/pz_stage/enter函数
foreach (['pz_enter', 'mat_set((int) $u', "'pz_stage'"] as $k) {
    // skip
}
$p = strpos($t, "choice === 'open2'");
echo substr($t, max(0, $p - 100), 900), "\n";
