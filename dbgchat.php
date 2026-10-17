<?php
require 'includes/bootstrap.php';
foreach (db()->query("SELECT id, username, channel, target, text, created_at FROM chat_msgs WHERE text LIKE '%军需官%' OR text LIKE '%公会战%' ORDER BY id DESC LIMIT 10")->fetchAll() as $r) {
    echo $r['id'], ' | ', $r['username'], ' | ch=', $r['channel'], ' t=', $r['target'], ' | ', mb_substr((string) $r['text'], 0, 80), " | ", date('m-d H:i', (int) $r['created_at']), "\n";
}
echo "---wars open---\n";
foreach (db()->query("SELECT id, a_gid, b_gid, ends_at, status FROM wars ORDER BY id DESC LIMIT 5")->fetchAll() as $w) {
    $a = db()->query('SELECT name FROM guilds WHERE id='.(int)$w['a_gid'])->fetchColumn();
    $b = db()->query('SELECT name FROM guilds WHERE id='.(int)$w['b_gid'])->fetchColumn();
    echo $w['id'], ' ', $a, ' VS ', $b, ' ends=', date('m-d H:i', (int)$w['ends_at']), ' st=', $w['status'], "\n";
}
