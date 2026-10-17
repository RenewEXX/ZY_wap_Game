<?php
$p = 'mall.php';
$t = file_get_contents($p);
$a = "    if (\$tab === 'func') {\n        echo '功能道具：<br>';";
if (strpos($t, $a) === false) { echo "MISS\n"; exit(1); }
$b = "    if (\$tab === 'func') {\n        echo '功能道具：<br>';\n        echo '·<b><span style=\"color:#6cf\">【自动售卖】</span></b> <b>' . h(fmt_diamond(20)) . '</b>/小时（在线得装即按品质自动卖，去背包功能栏开品质） ';\n        echo '<a href=\"mall.php?a=buy&m=autosell_time&n=1\">买1小时</a> ';\n        echo '<a href=\"mall.php?a=buy&m=autosell_time&n=5\">买5小时</a><br>';";
$t = str_replace($a, $b, $t);
file_put_contents($p, $t);
echo "OK\n";
