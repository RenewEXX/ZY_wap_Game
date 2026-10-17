<?php
$p = __DIR__ . '/includes/game.php';
$t = file_get_contents($p);
$edits = 0;
// give_title签名+去重bool
$old = "function give_title(int \$uid, string \$tid): void\n{\n    \$names = title_ids();\n    if (!isset(\$names[\$tid])) { return; }";
if (strpos($t, $old) !== false) {
    $t = str_replace($old, "function give_title(int \$uid, string \$tid): bool\n{\n    \$names = title_ids();\n    if (!isset(\$names[\$tid])) { return false; }", $t);
    $edits++;
}
$old = "    if (\$st->fetch()) { return; }\n    \$aff = [];";
if (strpos($t, $old) !== false) {
    $t = str_replace($old, "    if (\$st->fetch()) { return false; }\n    \$aff = [];", $t);
    $edits++;
}
$old = "VALUES (?, \"title\", ?, 4, ?, \"\")')->execute([\$uid, \$names[\$tid], json_encode(\$aff, JSON_UNESCAPED_UNICODE)]);";
if (strpos($t, $old) !== false) {
    $t = str_replace($old, "VALUES (?, \"title\", ?, 4, ?, \"\", 100)')->execute([\$uid, \$names[\$tid], json_encode(\$aff, JSON_UNESCAPED_UNICODE)]);\n    return true;", $t);
    $edits++;
}
file_put_contents($p, $t);
echo 'edits: ', $edits, "\n";
