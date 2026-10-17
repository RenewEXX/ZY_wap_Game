<?php
require 'includes/bootstrap.php';
echo "guilds:\n";
foreach (db()->query('SELECT id,name FROM guilds')->fetchAll() as $g) {
    echo ' ', $g['id'], ' ', $g['name'], "\n";
}
echo "wars:\n";
foreach (db()->query('SELECT * FROM wars')->fetchAll() as $w) {
    echo ' id=', $w['id'], ' a=', $w['a_gid'], ' b=', $w['b_gid'], ' status=', $w['status'], ' ends=', $w['ends_at'], "\n";
}
echo "guild_members table cols: ", implode(',', array_keys(db()->query('SELECT * FROM guild_members LIMIT 1')->fetch() ?: [])), "\n";
