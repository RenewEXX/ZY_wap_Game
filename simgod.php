<?php
require 'includes/bootstrap.php';
function mk($name, $lv) {
    foreach (db()->query("SELECT id FROM users WHERE username=" . db()->quote($name))->fetchAll() as $r) {
        db()->exec('DELETE FROM equips WHERE uid=' . (int) $r['id']);
        db()->exec('DELETE FROM mats WHERE uid=' . (int) $r['id']);
        db()->exec('DELETE FROM skills WHERE uid=' . (int) $r['id']);
        db()->exec('DELETE FROM users WHERE id=' . (int) $r['id']);
    }
    db()->exec("INSERT INTO users (username, pass, lv, exp, hp, maxhp, mp, maxmp, atk, def, gold, loc, job, quest, zone, created_at) VALUES (" . db()->quote($name) . ", 'x', $lv, 0, 100000, 100000, 500, 500, 5000, 5000, 99999999, 'god_gate', 'warrior', 99, 'z1', " . time() . ")");
    return (int) db()->lastInsertId();
}
$v = mk('__god_v', 700);
$u = user_by_id($v);
// T1 maps
echo 'T1 god_maps has hall: ', in_array('god_hall', god_maps()) ? 'yes' : 'NO', "\n";
// T2 new monsters
foreach (['god_cherub','god_heraldg','god_highpriest','god_archon','gf_seraphim'] as $mid) {
    $m = monsters()[$mid];
    echo 'T2 ', $mid, ' lv=', monster_lv($mid), ' hp=', $m['hp'], ' atk=', $m['atk'], ' in map?';
    $found = false;
    foreach (locations() as $lid => $lm) { if (in_array($mid, $lm['monsters'] ?? [], true)) { $found = $lid; break; } }
    echo $found ?: 'NOWHERE', "\n";
}
// T3 god quests
$gq = god_quests();
echo 'T3 god quests: ', count($gq), ' first=', array_key_first($gq), "\n";
// T4 quest_state at lv700 q81
$u['quest'] = 81;
$qs = quest_state($u);
echo 'T4 q81 state: ', $qs['name'], ' / loc=', $qs['loc'], ' ch=', $qs['ch'], "\n";
$u['quest'] = 100;
$qs = quest_state($u);
echo 'T4 q100 state: ', $qs['name'], ' step=', $qs['step'], ' ch=', $qs['ch'], "\n";
// T5 god set bonus
db()->exec("INSERT INTO equips (uid, slot, name, quality, affixes, pos, item_level) VALUES ($v, 'head', '神铸·圣冠', 4, '[]', 'wear', 400)");
db()->exec("INSERT INTO equips (uid, slot, name, quality, affixes, pos, item_level) VALUES ($v, 'body', '神铸·羽甲', 4, '[]', 'wear', 400)");
db()->exec("INSERT INTO equips (uid, slot, name, quality, affixes, pos, item_level) VALUES ($v, 'legs', '神铸·天裤', 4, '[]', 'wear', 400)");
_gear_uncache($v);
$gs = gear_stats($v);
echo 'T5 god3 atk_pct=', $gs['atk_pct'], ' dr=', $gs['damage_reduction'], ' hp=', $gs['hp'], ' (want 9/3/28000)', "\n";
echo 'T5 owned=', equip_set_owned_count($v, 'god'), "\n";
// T6 gf dungeon tick
mat_set($v, 'gf_enter', time() - 2800);
$u = user_by_id($v);
$u['loc'] = 'gf_gate';
user_save($u);
$u = user_by_id($v);
dungeon_tick($u);
echo 'T6 gf timeout loc=', $u['loc'], ' (want greenheart_sq)', "\n";
// T7 god drop
$n = 0;
for ($i = 0; $i < 5; $i++) { $m = god_kill_drop($v, 'god_emperor'); if (strpos($m, '神铸') !== false) $n++; }
echo 'T7 god_emperor drop 5/5 burnout: ', $n, "\n";
db()->exec("DELETE FROM equips WHERE uid=$v");
db()->exec("DELETE FROM mats WHERE uid=$v");
db()->exec("DELETE FROM skills WHERE uid=$v");
db()->exec("DELETE FROM users WHERE id=$v");
echo "SMOKE OK\n";
