<?php
$t = file_get_contents(__DIR__ . '/npc.php');
$p = strpos($t, 'rebirth2_skill($choice)');
echo $p === false ? "NONE\n" : substr($t, $p - 200, 500) . "\n";
