<?php
$p = __DIR__ . '/includes/game.php';
$t = file_get_contents($p);
// wear_title走wear_equip（同步生命上限，根治HP=1）
$old = "    if (ctype_digit((string) \$tid)) {\n        \$st = db()->prepare('SELECT id, name FROM equips WHERE id=? AND uid=? AND slot=?');\n        \$st->execute([(int) \$tid, \$uid, 'title']);\n        \$r = \$st->fetch();\n        if (!\$r) { return '没有这个称号。'; }\n        db()->prepare('UPDATE equips SET pos=\"\" WHERE uid=? AND slot=?')->execute([\$uid, 'title']);\n        db()->prepare('UPDATE equips SET pos=\"wear\" WHERE id=?')->execute([(int) \$r['id']]);\n        _gear_uncache(\$uid);\n        return '佩戴【' . \$r['name'] . '】！';\n    }";
if (strpos($t, $old) === false) { echo "MISS wear\n"; exit(1); }
$new = "    if (ctype_digit((string) \$tid)) {\n        \$st = db()->prepare('SELECT id FROM equips WHERE id=? AND uid=? AND slot=?');\n        \$st->execute([(int) \$tid, \$uid, 'title']);\n        if (!\$st->fetch()) { return '没有这个称号。'; }\n        return wear_equip(\$uid, (int) \$tid);\n    }";
$t = str_replace($old, $new, $t);
// give_title返回bool+req_lv100
$old2 = "function give_title(int \$uid, string \$tid): void\n{\n    \$names = title_ids();\n    if (!isset(\$names[\$tid])) { return; }\n    \$st = db()->prepare('SELECT id FROM equips WHERE uid=? AND slot=? AND name=?');\n    \$st->execute([\$uid, 'title', \$names[\$tid]]);\n    if (\$st->fetch()) { return; }";
if (strpos($t, $old2) === false) { echo "MISS give\n"; exit(1); }
$new2 = "function give_title(int \$uid, string \$tid): bool\n{\n    \$names = title_ids();\n    if (!isset(\$names[\$tid])) { return false; }\n    \$st = db()->prepare('SELECT id FROM equips WHERE uid=? AND slot=? AND name=?');\n    \$st->execute([\$uid, 'title', \$names[\$tid]]);\n    if (\$st->fetch()) { return false; }";
$t = str_replace($old2, $new2, $t);
$old3 = "    db()->prepare('INSERT INTO equips (uid, slot, name, quality, affixes, pos) VALUES (?, \"title\", ?, 4, ?, \"\")')->execute([\$uid, \$names[\$tid], json_encode(\$aff, JSON_UNESCAPED_UNICODE)]);\n}";
if (strpos($t, $old3) === false) { echo "MISS ins\n"; exit(1); }
$new3 = "    db()->prepare('INSERT INTO equips (uid, slot, name, quality, affixes, pos, req_lv) VALUES (?, \"title\", ?, 4, ?, \"\", 100)')->execute([\$uid, \$names[\$tid], json_encode(\$aff, JSON_UNESCAPED_UNICODE)]);\n    return true;\n}";
$t = str_replace($old3, $new3, $t);
// 龙箱重复称号→安慰奖
$old4 = "    give_title(\$uid, \$tid);\n    return '龙鳞宝箱开出大奖：称号【' . title_ids()[\$tid] . '】！';";
if (strpos($t, $old4) === false) { echo "MISS boxdupe\n"; exit(1); }
$new4 = "    if (give_title(\$uid, \$tid)) {\n        return '龙鳞宝箱开出大奖：称号【' . title_ids()[\$tid] . '】！';\n    }\n    add_mat(\$uid, 'enhance_t2', 20);\n    add_mat(\$uid, 'egg_unknown', 5);\n    return '称号【' . title_ids()[\$tid] . '】重复了，折成中级强化石×20+未知蛋×5！';";
$t = str_replace($old4, $new4, $t);
// 章 grants重复不发邮件
$old5 = "            if (!\$had->fetch()) {\n                give_title(\$uid, \$tid);\n                send_mail(";
if (strpos($t, $old5) === false) { echo "MISS grantmail\n"; exit(1); }
$new5 = "            if (!\$had->fetch()) {\n                if (give_title(\$uid, \$tid)) {\n                    send_mail(";
$t = str_replace($old5, $new5, $t);
$old6 = "'主线奖励称号【' . title_ids()[\$tid] . '】已发放，去背包·装备穿戴！', []);\n            }";
if (strpos($t, $old6) === false) { echo "MISS grantmail2\n"; exit(1); }
$new6 = "'主线奖励称号【' . title_ids()[\$tid] . '】已发放，去背包·装备穿戴！', []);\n                }\n            }";
$t = str_replace($old6, $new6, $t);
file_put_contents($p, $t);
echo "OK\n";
