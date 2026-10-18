<?php
require 'includes/bootstrap.php';
$ok = 0; $bad = 0;
function ckS(string $l, $got, $want) { global $ok,$bad; if ($got === $want) { $ok++; } else { $bad++; echo "FAIL $l: got=",var_export($got,true)," want=",var_export($want,true),"\n"; } }

// 造召唤师，只戴主手「魂契·深渊引魂灯」，不戴副手
$id = user_create('tsboxmain', 'x', 'summoner', 'z1', true, 'tmp');
$_SESSION['uid'] = $id;
$u = user_by_id($id);
// 主手武器
db()->prepare('INSERT INTO equips (uid,slot,name,quality,affixes,pos,item_level) VALUES (?,"weapon","魂契·深渊引魂灯",3,"[]","wear",700)')->execute([$id]);
_gear_uncache($id);
$u = user_by_id($id);
$msgMain = summoner_open_message($u);
echo "只戴主手引魂灯，开场召唤: '" . $msgMain . "'\n";
ckS('主手不触发开场召唤', $msgMain, '');
ckS('副手未穿戴', summoner_offhand_worn($id), []);

// 再加副手契匣
db()->prepare('INSERT INTO equips (uid,slot,name,quality,affixes,pos,item_level) VALUES (?,"offhand","烬焰·契匣",3,"[]","wear",635)')->execute([$id]);
_gear_uncache($id);
$u = user_by_id($id);
$worn = summoner_offhand_worn($id);
ckS('副手契匣被识别', $worn['name'] ?? '', '烬焰·契匣');
$msgOff = summoner_open_message($u);
echo "戴副手烬焰·契匣，开场召唤: '" . $msgOff . "'\n";
ckS('副手触发开场召唤', $msgOff !== '', true);

db()->exec('DELETE FROM equips WHERE uid=' . (int) $id);
db()->exec('DELETE FROM users WHERE id=' . (int) $id);
$_SESSION = [];
echo "\nOK=$ok FAIL=$bad\n";