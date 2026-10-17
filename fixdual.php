<?php
// 抉择送双书
$p = __DIR__ . '/npc.php';
$t = file_get_contents($p);
$old = "        \$sk = rebirth2_skill(\$choice);\n        if (\$sk !== '') {\n            add_mat((int) \$u['id'], 'book_' . \$sk, 1);\n        }";
if (strpos($t, $old) === false) { echo "MISS\n"; exit(1); }
$new = "        \$sk = rebirth2_skill(\$choice);\n        if (\$sk !== '') {\n            add_mat((int) \$u['id'], 'book_' . \$sk, 1);\n        }\n        \$sk2 = rebirth2_skill2(\$choice);\n        if (\$sk2 !== '') {\n            add_mat((int) \$u['id'], 'book_' . \$sk2, 1);\n        }";
$t = str_replace($old, $new, $t);
file_put_contents($p, $t);
echo "OK npc\n";
