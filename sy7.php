<?php
$p = 'includes/game.php';
$t = file_get_contents($p);

$a = "// 传说单件（不计入套装，低概率额外出货）";
if (strpos($t, $a) === false) { echo "MISS\n"; exit(1); }

$new = <<<'EOT'
// 套装部件清单：[槽位 => 名称]
function sy_set_pieces(): array
{
    return [
        // 寂灭（爆发/暴击流）
        'ann' => [
            ['weapon', '寂灭·终焉之刃'], ['weapon', '寂灭·终焉之斧'], ['weapon', '寂灭·终焉之弓'],
            ['weapon', '寂灭·终焉之锤'], ['weapon', '寂灭·终焉之杖'], ['weapon', '寂灭·终焉之盾'],
            ['head', '寂灭·无光冠'], ['body', '寂灭·空寂甲'], ['necklace', '寂灭·灭音链'],
            ['gloves', '寂灭·断律手套'], ['ring', '寂灭·静默戒'],
        ],
        // 噬渊（续航/吸血流）
        'dev' => [
            ['weapon', '噬渊·吞星刃'], ['weapon', '噬渊·裂渊斧'], ['weapon', '噬渊·噬渊弓'],
            ['weapon', '噬渊·万骸锤'], ['weapon', '噬渊·渊喉杖'], ['weapon', '噬渊·无底盾'],
            ['head', '噬渊·无底冠'], ['body', '噬渊·渊壳甲'], ['necklace', '噬渊·沉渊链'],
            ['gloves', '噬渊·啖罪手套'], ['ring', '噬渊·万骸戒'],
        ],
    ];
}

// 传说单件（不计入套装，低概率额外出货）
EOT;
$t = str_replace($a, $new, $a === '' ? '' : $a, $c);
$t = str_replace("    // 传说单件（不计入套装，低概率额外出货）", $new, $t, $c);
echo "piecefn=$c\n";

// 修 sy_drop_roll 用槽位
$p0 = strpos($t, 'function sy_drop_roll(int $uid): string');
$p1 = strpos($t, "\n}\n", $p0) + 3;
$body = <<<'EOT'
function sy_drop_roll(int $uid): string
{
    $u = user_by_id($uid);
    if (!$u) {
        return '';
    }
    $lv = max(1, (int) ($u['lv'] ?? 1));
    // 70% 出【寂灭】或【噬渊】其中一件
    if (mt_rand(1, 100) <= 70) {
        $sets = sy_set_pieces();
        $set = mt_rand(1, 2) === 1 ? $sets['ann'] : $sets['dev'];
        $pc = $set[array_rand($set)];
        $sn = make_equip($uid, (string) $pc[0], (string) $pc[1], 4, $lv);
        return $sn === '' ? '' : '掉落传说【' . $sn . '】（源罪渊·' . (mt_rand(1, 2) === 1 ? '寂灭' : '噬渊') . '套装）！';
    }
    // 30% 出同主题传说单件
    $lg = sy_legendaries();
    $l = $lg[array_rand($lg)];
    $sn = make_equip($uid, (string) $l['slot'], (string) $l['name'], 4, $lv);
    return $sn === '' ? '' : '掉落传说【' . $sn . '】（源罪渊·同主题）！';
}

EOT;
$t = substr($t, 0, $p0) . $body . substr($t, $p1);
file_put_contents($p, $t);
echo "DONE\n";