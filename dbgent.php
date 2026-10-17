<?php
$t = file_get_contents('includes/game.php');
foreach (['dsw_enter', 'abx_enter', 'mx_enter', 'vx_enter'] as $k) {
    $p = 0;
    while (($p = strpos($t, "'$k'", $p + 1)) !== false) {
        $line = substr($t, strrpos(substr($t, 0, $p), "\n") + 1);
        $fnPos = strrpos(substr($t, 0, $p), 'function ');
        $fnName = substr($t, $fnPos + 9, 30);
        $fnName = substr($fnName, 0, strpos($fnName, '('));
        echo "$k in fn=$fnName\n";
    }
}
