<?php
// 人偶栏补自动售卖 + 背包提示改功能栏
$p = 'mall.php';
$t = file_get_contents($p);
$a = <<<'EOT'
        echo '路<a href="mall.php?m=offline_mod"><b><span style="color:#c6f">銆愪汉鍋剁绾垮崌绾фā鍧椼€?/span></b></a> ';
EOT;
// 用真实文本锚：找dummy tab里offline_mod行
$anchor = "        echo '路<a href=\"mall.php?m=offline_mod\"><b><span style=\"color:#c6f\">【人偶离线升级模块】</span></b></a> ';";
if (strpos($t, $anchor) === false) {
    // 用dummy tab的结束锚：'offline_mod&n=5">买5小时</a><br>';
    $anchor = "'<a href=\"mall.php?a=buy&m=offline_mod&n=5\">买5小时</a><br>';";
    if (strpos($t, $anchor) === false) { echo "MISS dummy\n"; exit(1); }
    $add = $anchor . "\n        echo '·<b><span style=\"color:#6cf\">【自动售卖】</span></b> <b>' . h(fmt_diamond(20)) . '</b>/小时（在线得装备按品质自动卖，去背包功能栏开品质） ';\n        echo '<a href=\"mall.php?a=buy&m=autosell_time&n=1\">买1小时</a> ';\n        echo '<a href=\"mall.php?a=buy&m=autosell_time&n=5\">买5小时</a><br>';";
    $t = str_replace($anchor, $add, $t);
} else {
    echo "already?\n";
}
file_put_contents($p, $t);
echo "ok\n";
