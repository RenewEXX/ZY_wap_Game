<?php
require 'includes/bootstrap.php';
// 造号测掉落
db()->exec("INSERT INTO users (username, pass, lv, exp, hp, maxhp, mp, maxmp, atk, def, gold, loc, job, quest, zone, created_at) VALUES ('__syv', 'x', 800, 0, 90000, 90000, 500, 500, 800, 600, 0, 'sy_pit', 'warrior', 118, 'z1', " . time() . ")");
$v = (int) db()->lastInsertId();
echo "make_equip test: ", make_equip($v, 'weapon', '寂灭·终焉之刃', 4, 800), "\n";
echo "make_equip test: ", make_equip($v, 'ring', '终焉·寂灭之瞳', 4, 800), "\n";
$rows = db()->prepare("SELECT slot,name,quality,item_level FROM equips WHERE uid=?");
$rows->execute([$v]);
foreach ($rows->fetchAll() as $r) {
    echo '  ', $r['slot'], ' | ', $r['name'], ' | q=', $r['quality'], ' ilv=', $r['item_level'], "\n";
}
echo "setid(寂灭·终焉之刃)=", equip_set_id('寂灭·终焉之刃'), "\n";
echo "owned ann=", equip_set_owned_count($v, 'ann'), "\n";
$st = db()->prepare("UPDATE equips SET pos='wear' WHERE uid=?");
$st->execute([$v]);
echo "owned ann(after wear)=", equip_set_owned_count($v, 'ann'), "\n";
$g = gear_stats($v);
echo "crit=", $g['crit'], " skill_damage=", $g['skill_damage'], "\n";
echo "drop roll x6:\n";
for ($i = 0; $i < 6; $i++) {
    echo '  ', sy_drop_roll($v), "\n";
}
db()->exec("DELETE FROM equips WHERE uid=$v");
db()->exec("DELETE FROM mats WHERE uid=$v");
db()->exec("DELETE FROM users WHERE id=$v");
echo "OK\n";