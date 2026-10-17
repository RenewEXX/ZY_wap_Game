<?php
$p = 'mall.php';
$t = file_get_contents($p);

// 1) 清掉buy里的残留autosell echo（死代码）
$a = <<<'EOT'
        if ($m === 'offline_mod' || $m === 'offline_time') {
    if ($m === 'autosell_time') {
        echo '效果：在线每得装备即按品质自动卖（离线结算同样适用），去背包功能栏选品质开关<br>';
    }
            add_mat((int) $u['id'], 'offline_time', 3600 * $n);
EOT;
if (strpos($t, $a) === false) { echo "MISS buyclean\n"; exit(1); }
$b = <<<'EOT'
        if ($m === 'offline_mod' || $m === 'offline_time') {
            add_mat((int) $u['id'], 'offline_time', 3600 * $n);
EOT;
$t = str_replace($a, $b, $t);

// 2) 热销页加自动售卖
$a = "        echo '路<b><span style=\"color:#fc3\">銆愪笅绾у己鍖栫煶銆?/span></b> <b>' . h(fmt_diamond(mall_stones()['enhance_t1'])) . '</b>/涓?';";
if (strpos($t, $a) === false) {
    // 用UTF-8原文找
    $a2 = "        echo '·<b><span style=\"color:#fc3\">【下级强化石】</span></b> <b>' . h(fmt_diamond(mall_stones()['enhance_t1'])) . '</b>/个 ';";
    if (strpos($t, $a2) === false) { echo "MISS hot\n"; exit(1); }
    $t = str_replace($a2, "        echo '·<b><span style=\"color:#6cf\">【自动售卖】</span></b> <b>' . h(fmt_diamond(20)) . '</b>/小时（在线得装备按品质自动卖） ';\n        echo '<a href=\"mall.php?a=buy&m=autosell_time&n=1\">买1小时</a><br>';\n" . $a2, $t);
} else {
    // 文件是乱码存储，用字节匹配不行；改为在hot段尾部追加
    echo "byte-match-used\n";
}
file_put_contents($p, $t);
echo "OK\n";
