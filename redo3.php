<?php
// 4) T1/T2下限
$p = __DIR__ . '/includes/game.php';
$t = file_get_contents($p);
$rep = [
    "[[8, 15], [15, 30], [30, 55], [55, 100], [90, 150]], 100)]" => "[[20, 30], [35, 55], [30, 55], [55, 100], [90, 150]], 100)]",
    "[[4, 10], [10, 20], [20, 35], [35, 60], [6" => "[[10, 18], [22, 35], [20, 35], [35, 60], [6",
    "[[3, 8], [8, 16], [16, 28], [28, 48], [48, 80]], 90)]" => "[[8, 14], [18, 28], [16, 28], [28, 48], [48, 80]], 90)]",
];
foreach ($rep as $o => $n) { $t = str_replace($o, $n, $t); }
file_put_contents($p, $t);
// 5) 副本+30%
$lines = file($p);
$n = 0;
foreach ($lines as $i => $l) {
    if (preg_match("/'(dsw|abx|theater|mx|frost|fr|volcano|em|ash|fog|puppet|silk)[a-z_]*' => \[/", $l)) {
        if (preg_match("/'exp' => (\d+), 'gold' => (\d+)/", $l, $m)) {
            $lines[$i] = str_replace("'exp' => " . $m[1] . ", 'gold' => " . $m[2], "'exp' => " . (int) ($m[1] * 1.3) . ", 'gold' => " . (int) ($m[2] * 1.3), $l);
            $n++;
        }
    }
}
file_put_contents($p, implode('', $lines));
echo "step3 buffed=$n\n";
