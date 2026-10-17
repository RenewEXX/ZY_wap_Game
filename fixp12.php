<?php
// equip.php部位行后加职业行
$p = __DIR__ . '/equip.php';
$lines = file($p);
foreach ($lines as $i => $l) {
    if (strpos($l, "部位：") !== false) { echo 'slot @', $i + 1, "\n"; $si = $i; }
}
array_splice($lines, $si + 1, 0, ["echo '可装备：' . h(equip_jobs_text(\$e)) . '<br>';\n"]);
file_put_contents($p, implode('', $lines));
// 2.腰部位排序：equip_slots把belt插到legs后
$g = file_get_contents(__DIR__ . '/includes/game.php');
$old = "        'gloves' => '手套', 'shoes' => '鞋子', 'back' => '背部', 'ring' => '戒指', 'necklace' => '项链',";
if (strpos($g, $old) === false) { echo "MISS slots\n"; exit(1); }
$new = "        'gloves' => '手套', 'belt' => '腰部', 'shoes' => '鞋子', 'back' => '背部', 'ring' => '戒指', 'necklace' => '项链',";
$g = str_replace($old, $new, $g);
$old2 = "        'soul' => '神魂', 'title' => '称号', 'belt' => '腰部',";
if (strpos($g, $old2) === false) { echo "MISS slots2\n"; exit(1); }
$g = str_replace($old2, "        'soul' => '神魂', 'title' => '称号',", $g);
file_put_contents(__DIR__ . '/includes/game.php', $g);
echo "OK\n";
