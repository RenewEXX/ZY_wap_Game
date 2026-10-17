<?php
// 副本怪exp/gold +30%：dsw/abx/theater/mx/frost/fr/volcano/em/ash/fog/puppet/silk前缀
$p = __DIR__ . '/includes/game.php';
$lines = file($p);
$n = 0;
foreach ($lines as $i => $l) {
    if (preg_match("/'(dsw|abx|theater|mx|frost|fr|volcano|em|ash|fog|puppet|silk)[a-z_]*' => \[/", $l)) {
        // 同行exp/gold
        if (preg_match("/'exp' => (\d+), 'gold' => (\d+)/", $l, $m)) {
            $lines[$i] = str_replace("'exp' => " . $m[1] . ", 'gold' => " . $m[2], "'exp' => " . (int) ($m[1] * 1.3) . ", 'gold' => " . (int) ($m[2] * 1.3), $l);
            $n++;
        }
    }
}
file_put_contents($p, implode('', $lines));
echo 'buffed: ', $n, "\n";
