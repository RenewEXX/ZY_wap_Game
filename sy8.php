<?php
$p = 'includes/game.php';
$t = file_get_contents($p);

if (strpos($t, 'function sy_set_pieces') === false) {
    $a = "function sy_legendaries(): array";
    if (strpos($t, $a) === false) { echo "MISS anchor\n"; exit(1); }
    $new = <<<'EOT'
// 套装部件清单：[槽位 => 名称]
function sy_set_pieces(): array
{
    return [
        'ann' => [
            ['weapon', '寂灭·终焉之刃'], ['weapon', '寂灭·终焉之斧'], ['weapon', '寂灭·终焉之弓'],
            ['weapon', '寂灭·终焉之锤'], ['weapon', '寂灭·终焉之杖'], ['weapon', '寂灭·终焉之盾'],
            ['head', '寂灭·无光冠'], ['body', '寂灭·空寂甲'], ['necklace', '寂灭·灭音链'],
            ['gloves', '寂灭·断律手套'], ['ring', '寂灭·静默戒'],
        ],
        'dev' => [
            ['weapon', '噬渊·吞星刃'], ['weapon', '噬渊·裂渊斧'], ['weapon', '噬渊·噬渊弓'],
            ['weapon', '噬渊·万骸锤'], ['weapon', '噬渊·渊喉杖'], ['weapon', '噬渊·无底盾'],
            ['head', '噬渊·无底冠'], ['body', '噬渊·渊壳甲'], ['necklace', '噬渊·沉渊链'],
            ['gloves', '噬渊·啖罪手套'], ['ring', '噬渊·万骸戒'],
        ],
    ];
}

function sy_legendaries(): array
EOT;
    $t = str_replace($a, $new, $t, $c);
    echo "piecefn=$c\n";
}

// 修文案：只roll一次
$a = <<<'EOT'
        $sets = sy_set_pieces();
        $set = mt_rand(1, 2) === 1 ? $sets['ann'] : $sets['dev'];
        $pc = $set[array_rand($set)];
        $sn = make_equip($uid, (string) $pc[0], (string) $pc[1], 4, $lv);
        return $sn === '' ? '' : '掉落传说【' . $sn . '】（源罪渊·' . (mt_rand(1, 2) === 1 ? '寂灭' : '噬渊') . '套装）！';
EOT;
if (strpos($t, $a) !== false) {
    $b = <<<'EOT'
        $sets = sy_set_pieces();
        $isAnn = mt_rand(1, 2) === 1;
        $set = $isAnn ? $sets['ann'] : $sets['dev'];
        $pc = $set[array_rand($set)];
        $sn = make_equip($uid, (string) $pc[0], (string) $pc[1], 4, $lv);
        return $sn === '' ? '' : '掉落传说【' . $sn . '】（源罪渊·' . ($isAnn ? '寂灭' : '噬渊') . '套装）！';
EOT;
    $t = str_replace($a, $b, $t, $c);
    echo "msgfix=$c\n";
} else {
    echo "msgfix=skip\n";
}
file_put_contents($p, $t);
echo "DONE\n";