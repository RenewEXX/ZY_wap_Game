<?php
$p = __DIR__ . '/includes/game.php';
$t = file_get_contents($p);
// wear_title走wear_equip（统一maxhp调整+100级门槛）
$old = "function wear_title(int \$uid, \$tid): string\n{\n    if (ctype_digit((string) \$tid)) {\n        \$st = db()->prepare('SELECT id, name FROM equips WHERE id=? AND uid=? AND slot=?');\n        \$st->execute([(int) \$tid, \$uid, 'title']);\n        \$r = \$st->fetch();\n        if (!\$r) { return '没有这个称号。'; }\n        db()->prepare('UPDATE equips SET pos=\"\" WHERE uid=? AND slot=?')->execute([\$uid, 'title']);\n        db()->prepare('UPDATE equips SET pos=\"wear\" WHERE id=?')->execute([(int) \$r['id']]);\n        _gear_uncache(\$uid);\n        return '佩戴【' . \$r['name'] . '】！';\n    }";
if (strpos($t, $old) === false) { echo "MISS wear\n"; exit(1); }
$new = "function wear_title(int \$uid, \$tid): string\n{\n    if (ctype_digit((string) \$tid)) {\n        \$st = db()->prepare('SELECT id FROM equips WHERE id=? AND uid=? AND slot=?');\n        \$st->execute([(int) \$tid, \$uid, 'title']);\n        \$r = \$st->fetch();\n        if (!\$r) { return '没有这个称号。'; }\n        return wear_equip(\$uid, (int) \$r['id']);\n    }";
$t = str_replace($old, $new, $t);
// give_title：允许重复获得+100级门槛
$old2 = "    \$st = db()->prepare('SELECT id FROM equips WHERE uid=? AND slot=? AND name=?');\n    \$st->execute([\$uid, 'title', \$names[\$tid]]);\n    if (\$st->fetch()) { return; }";
if (strpos($t, $old2) === false) { echo "MISS dup\n"; exit(1); }
$t = str_replace($old2, "    // 允许重复获得（不再吞掉第二个）", $t);
$old3 = "    db()->prepare('INSERT INTO equips (uid, slot, name, quality, affixes, pos) VALUES (?, \"title\", ?, 4, ?, \"\")')->execute([\$uid, \$names[\$tid], json_encode(\$aff, JSON_UNESCAPED_UNICODE)]);";
if (strpos($t, $old3) === false) { echo "MISS ins\n"; exit(1); }
$t = str_replace($old3, "    db()->prepare('INSERT INTO equips (uid, slot, name, quality, affixes, pos, req_lv) VALUES (?, \"title\", ?, 4, ?, \"\", 100)')->execute([\$uid, \$names[\$tid], json_encode(\$aff, JSON_UNESCAPED_UNICODE)]);", $t);
file_put_contents($p, $t);
echo "OK\n";
