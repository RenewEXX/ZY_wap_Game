<?php
$files = ['status.php', 'equip.php', 'bag.php'];
foreach ($files as $f) {
    $t = file_get_contents(__DIR__ . '/' . $f);
    $p = 0;
    echo '== ', $f, "\n";
    while (($p = strpos($t, 'maxhp', $p)) !== false) {
        echo substr($t, max(0, $p - 140), 300), "\n---\n";
        $p += 5;
    }
}
