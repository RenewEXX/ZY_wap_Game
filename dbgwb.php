<?php
require 'includes/bootstrap.php';
// 造两公会+一场war + 一个游离用户
db()->exec('DELETE FROM guild_members WHERE gid IN (9991,9992)');
db()->exec('DELETE FROM guilds WHERE id IN (9991,9992)');
db()->exec('DELETE FROM wars WHERE a_gid IN (9991,9992) OR b_gid IN (9991,9992)');
db()->exec("INSERT INTO guilds (id, name, level, zone, created_at) VALUES (9991,'测试甲',1,'z1',".time().")");
db()->exec("INSERT INTO guilds (id, name, level, zone, created_at) VALUES (9992,'测试乙',1,'z1',".time().")");
db()->exec("INSERT INTO wars (a_gid, b_gid, ends_at, status) VALUES (9991, 9992, ".(time()+50000).", 'open')");
$n = db()->query("SELECT id FROM users WHERE username='__wb_v'")->fetch();
$uid = 0;
if ($n) { $uid = (int) $n['id']; db()->exec('DELETE FROM users WHERE id='.$uid); }
    db()->exec("INSERT INTO users (username, pass, lv, exp, hp, maxhp, mp, maxmp, atk, def, gold, loc, job, quest, zone, created_at) VALUES ('__wb_v','x',10,0,500,500,100,100,20,20,0,'square','warrior',99,'z1',".time().")");
$uid = (int) db()->lastInsertId();
echo 'banner(no guild user)=[', war_banner($uid), "]\n";
echo 'banner(null)=[', war_banner(null), "]\n";
db()->exec("DELETE FROM wars WHERE a_gid IN (9991,9992) OR b_gid IN (9991,9992)");
db()->exec('DELETE FROM guilds WHERE id IN (9991,9992)');
db()->exec('DELETE FROM users WHERE id='.$uid);
echo "OK\n";
