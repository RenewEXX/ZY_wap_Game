<?php
$p = __DIR__ . '/npc.php';
$lines = file($p);
$idx = null;
foreach ($lines as $i => $l) {
    if (strpos($l, '$sk = rebirth2_skill($choice);') !== false) { $idx = $i; break; }
}
if ($idx === null) { echo "MISS\n"; exit(1); }
// idx+1是if，idx+2是add_mat，idx+3是}——在idx+3后插入
$add = [
    "        \$sk2 = rebirth2_skill2(\$choice);\n",
    "        if (\$sk2 !== '') {\n",
    "            add_mat((int) \$u['id'], 'book_' . \$sk2, 1);\n",
    "        }\n",
];
array_splice($lines, $idx + 4, 0, $add);
file_put_contents($p, implode('', $lines));
echo "OK\n";
