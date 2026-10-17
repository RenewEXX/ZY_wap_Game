<?php
$p = 'includes/game.php';
$t = file_get_contents($p);
function rep(&$t, $a, $b, $label) {
    if (strpos($t, $a) === false) { echo "MISS $label\n"; return; }
    $n = 0; $t = str_replace($a, $b, $t, $n);
    echo "$label=$n\n";
}

// 1) owned 白名单补齐 dusk/ann/dev
rep($t, "if (in_array(\$set, ['theater', 'frost', 'ember', 'puppet', 'thorn', 'rockjail', 'sin', 'god'], true)) {", "if (in_array(\$set, ['theater', 'frost', 'ember', 'puppet', 'thorn', 'rockjail', 'sin', 'god', 'dusk', 'ann', 'dev'], true)) {", 'ownedlist');

// 2) 清掉误插入 equip_set_pieces 的 ann/dev 说明块（只保留数组块）
$bad = "    if (\$set === 'ann') {\n        return '【寂灭套装】已穿' . \$owned . '/11——2件：暴击+10%、技伤+15%；3件：全属性+等级×8；5件：暴伤+50%、真伤+等级×30、减伤+15%！渊底不产饶恕，只产终结。';\n    }\n    if (\$set === 'dev') {\n        return '【噬渊套装】已穿' . \$owned . '/11——2件：吸血+12%、生命+等级×60；3件：全属性+等级×8、减伤+12%；5件：真伤+等级×35、攻+20%、吸血再+8%！渊不需要呼吸，它只需要你。';\n    }\n";
if (substr_count($t, $bad) === 2) {
    // 第二处（pieces）删掉，第一处（bonus_text）保留
    $first = strpos($t, $bad);
    $t = substr($t, 0, $first) . substr($t, $first + strlen($bad));
    $t = str_replace($bad, '', $t, $n);
    echo "pieces-clean=$n\n";
} else {
    echo 'bad-count=', substr_count($t, $bad), "\n";
}
file_put_contents($p, $t);
echo "DONE\n";