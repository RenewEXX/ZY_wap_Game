<?php
// 称号不显示强化tag：bag/status/look三处加slot判断太散——改enhance_tag_html加$row param? 简单：称号行跳过
$p = __DIR__ . '/bag.php';
$t = file_get_contents($p);
// 216/221/231三处：enhance_tag_html前加($e['slot']==='title'?'':...)
$t = str_replace("echo \$sname . (\$i + 1) . '：' . (\$r ? enhance_tag_html((int) (\$r['enhance_level'] ?? 0))", "echo \$sname . (\$i + 1) . '：' . (\$r ? ((\$r['slot'] ?? '') === 'title' ? '' : enhance_tag_html((int) (\$r['enhance_level'] ?? 0)))", $t);
$t = str_replace("echo \$sname . '：' . (\$e ? enhance_tag_html((int) (\$e['enhance_level'] ?? 0))", "echo \$sname . '：' . (\$e ? ((\$e['slot'] ?? '') === 'title' ? '' : enhance_tag_html((int) (\$e['enhance_level'] ?? 0)))", $t);
$t = str_replace("echo enhance_tag_html((int) (\$e['enhance_level'] ?? 0)) . '<a href=\"equip.php?id='", "echo (((\$e['slot'] ?? '') === 'title') ? '' : enhance_tag_html((int) (\$e['enhance_level'] ?? 0))) . '<a href=\"equip.php?id='", $t);
file_put_contents($p, $t);
$s = file_get_contents(__DIR__ . '/status.php');
$s = str_replace("echo '·[' . h(equip_slots()[\$e['slot']] ?? '') . ']' . enhance_tag_html((int) (\$e['enhance_level'] ?? 0))", "echo '·[' . h(equip_slots()[\$e['slot']] ?? '') . ']' . (((\$e['slot'] ?? '') === 'title') ? '' : enhance_tag_html((int) (\$e['enhance_level'] ?? 0)))", $s);
file_put_contents(__DIR__ . '/status.php', $s);
$l = file_get_contents(__DIR__ . '/look.php');
$l = str_replace("echo enhance_tag_html((int) (\$we['enhance_level'] ?? 0))", "echo (((\$we['slot'] ?? '') === 'title') ? '' : enhance_tag_html((int) (\$we['enhance_level'] ?? 0)))", $l);
file_put_contents(__DIR__ . '/look.php', $l);
echo "OK\n";
