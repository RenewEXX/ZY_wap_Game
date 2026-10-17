<?php
$file = $argv[1];
$name = $argv[2];
$t = file_get_contents($file);
$needle = 'function ' . $name . '(';
$p = strpos($t, $needle);
if ($p === false) { echo "MISS $name\n"; exit(1); }
$end = strpos($t, "\nfunction ", $p + 10);
if ($end === false) { $end = strlen($t); }
echo "===== $name =====\n";
echo substr($t, $p, $end - $p), "\n";
