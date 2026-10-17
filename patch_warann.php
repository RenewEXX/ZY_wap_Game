<?php
// declare_war：世界频道广播→只给两公会成员发信
$p = 'includes/game.php';
$t = file_get_contents($p);
$a = <<<'EOT'
    db()->prepare('INSERT INTO chat_msgs (uid, username, channel, target, text, created_at) VALUES (0, "战报", "world", 0, ?, ?)')->execute(['【公会战】' . $ga . '向' . $gb . '宣战！24小时刷怪比战功，去荒芜战场找军需官参战！', time()]);
EOT;
if (strpos($t, $a) === false) { echo "MISS\n"; exit(1); }
$b = <<<'EOT'
    foreach ([$myGid, $targetGid] as $gg) {
        foreach (db()->query('SELECT uid FROM guild_members WHERE gid=' . (int) $gg)->fetchAll() as $mm) {
            send_mail((int) $mm['uid'], '战场军需官', 'war', '公会战开启', $ga . ' VS ' . $gb . '：24小时刷怪比战功，去荒芜战场找军需官参战！', []);
        }
    }
EOT;
$t = str_replace($a, $b, $t);
file_put_contents($p, $t);
echo "OK\n";
