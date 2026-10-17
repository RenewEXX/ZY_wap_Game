<?php
$p = __DIR__ . '/status.php';
$lines = file($p);
foreach ($lines as $i => $l) {
    if (strpos($l, "=== 'titleoff'") !== false) {
        // 找到块内UPDATE行替换
        for ($k = $i; $k < $i + 8; $k++) {
            if (strpos($lines[$k], 'UPDATE equips SET pos=') !== false) {
                $lines[$k] = "    \$w = title_worn((int) \$u['id']);\n    flash_set(\$w ? take_off_equip((int) \$u['id'], (int) \$w['id']) : '没戴着称号。');\n";
                // 删掉旧的_gear_uncache+flash两行
                array_splice($lines, $k + 1, 2);
                break;
            }
        }
        break;
    }
}
file_put_contents($p, implode('', $lines));
echo "OK\n";
