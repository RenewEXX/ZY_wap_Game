<?php
$p = __DIR__ . '/includes/game.php';
$lines = file($p);
// splash后插入m8加成（index 5028是5029行）
array_splice($lines, 5028, 0, ["                if (\$sid === 'm8' && \$sLv >= 20) { \$b['splash'] = (int) (\$b['splash'] + \$pd * 0.1); \$logHeal .= '(WindSpread)'; }\n"]);
// frost块结束：找5559后首个trim=='}'
$fi = null;
foreach ($lines as $i => $l) {
    if (strpos($l, "if (!empty(\$b['frost'])) {") !== false) { $fi = $i; break; }
}
$end = null;
for ($k = $fi + 1; $k < $fi + 8; $k++) {
    if (trim($lines[$k]) === '}') { $end = $k; break; }
}
array_splice($lines, $end + 1, 0, [
    "    if (!empty(\$b['breach'])) {\n",
    "        \$md = (int) (\$md * (\$breachLv20 ? 0.75 : 0.8));\n",
    "        \$b['breach'] = (int) \$b['breach'] - 1;\n",
    "        \$log .= \$b['name'] . 'BeiPoJia，GongJiShouC挫。';\n",
    "    }\n",
]);
file_put_contents($p, implode('', $lines));
echo "OK\n";
