<?php
$t = file_get_contents('includes/game.php');
// em怪+drops+ticket+enter
foreach (["'em_slag' =>", "'em_heart' =>", 'em_ticket', 'em_stage', 'pz_ticket'] as $k) {
    $p = 0;
    $n = 0;
    while (($p = strpos($t, $k, $p)) !== false && $n < 3) {
        echo $k, ': ', trim(preg_replace('/[^\x20-\x7e]/', '?', substr($t, max(0, $p - 80), 220))), "\n";
        $p += strlen($k);
        $n++;
    }
}
