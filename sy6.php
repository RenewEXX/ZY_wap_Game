<?php
$p = 'includes/game.php';
$t = file_get_contents($p);

$fns = <<<'EOT'

// ============ 源罪渊·BOSS掉落 ============

// 传说单件（不计入套装，低概率额外出货）
function sy_legendaries(): array
{
    return [
        ['slot' => 'ring', 'name' => '终焉·寂灭之瞳'],
        ['slot' => 'offhand', 'name' => '万渊·噬渊之心'],
        ['slot' => 'necklace', 'name' => '罪源·原初之渊'],
    ];
}

function sy_drop_roll(int $uid): string
{
    $u = user_by_id($uid);
    if (!$u) {
        return '';
    }
    $lv = max(1, (int) ($u['lv'] ?? 1));
    // 70% 出【寂灭】或【噬渊】其中一件
    if (mt_rand(1, 100) <= 70) {
        $set = mt_rand(1, 2) === 1 ? equip_set_pieces('ann') : equip_set_pieces('dev');
        $sn = make_equip($uid, (string) $set[array_rand($set)], 0, 4, $lv);
        return $sn === '' ? '' : '掉落传说【' . $sn . '】（源罪渊·寂灭/噬渊套装）！';
    }
    // 30% 出同主题传说单件
    if (mt_rand(1, 100) <= 30) {
        $lg = sy_legendaries()[array_rand(sy_legendaries())];
        $sn = make_equip($uid, (string) $lg['slot'], (string) $lg['name'], 4, $lv);
        return $sn === '' ? '' : '掉落传说【' . $sn . '】（源罪渊·同主题）！';
    }
    return '';
}
EOT;

// 插在 god_kill_drop 之后（dusk_kill_drop 之前）
$a = "\nfunction dusk_kill_drop(int \$uid, string \$mid): string";
if (strpos($t, $a) === false) { echo "MISS dropfn\n"; exit(1); }
$t = str_replace($a, $fns . $a, $t);

// battle_round：BOSS 掉落
$a = <<<'EOT'
        $duskMsg = dusk_kill_drop((int) ($u['id'] ?? 0), (string) ($b['id'] ?? ''));
        if ($duskMsg !== '') {
            $msg .= $duskMsg;
        }
EOT;
if (strpos($t, $a) === false) { echo "MISS call\n"; exit(1); }
$b = $a . <<<'EOT'
        $syMsg = sy_drop_roll((int) ($u['id'] ?? 0));
        if ($syMsg !== '') {
            $msg .= $syMsg;
        }
EOT;
$t = str_replace($a, $b, $t);

// make_equip 签名确认：传的是完整名还是短名？
file_put_contents($p, $t);
echo "DONE\n";