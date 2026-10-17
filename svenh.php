<?php
$files = ['npc.php', 'smith.php', 'includes/game.php'];
foreach ($files as $f) {
    $t = file_get_contents(__DIR__ . '/' . $f);
    $p = 0;
    while (($p = strpos($t, 'enhance', $p)) !== false) {
        $seg = substr($t, max(0, $p - 60), 140);
        if (strpos($seg, 'choice') !== false || strpos($seg, "'a'") !== false || strpos($seg, '$_GET') !== false) {
            echo $f, ': ', trim(preg_replace('/[^\x20-\x7e]/', '?', $seg)), "\n";
        }
        $p += 7;
    }
}
