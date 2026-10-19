<?php
require 'includes/bootstrap.php';
// 召唤师·死灵传说装备：定义/穿戴识别/触发效果
$ok = 0; $bad = 0;
function ckL(string $l, $got, $want) { global $ok,$bad; if ($got === $want) { $ok++; } else { $bad++; echo "FAIL $l: got=",var_export($got,true)," want=",var_export($want,true),"\n"; } }

// —— 定义齐全 ——
ckL('7件传说', count(summoner_legends()), 7);
foreach (summoner_legends() as $nm => $cfg) {
    ckL("$nm 有部位", in_array($cfg['slot'], array_keys(equip_slots()), true), true);
    ckL("$nm 有等级", (int) $cfg['lv'] > 0, true);
    ckL("$nm 有说明", summoner_legend_intro_by_key((string) $cfg['key']) !== '', true);
}

// —— 装备可创建、可识别 ——
$uid = user_create('tsumleg', 'x', 'summoner', 'z1', true, 'tmp');
$_SESSION['uid'] = $uid;
$u = user_by_id($uid);
$u['lv'] = 999; $u['str'] = 99999; $u['agi'] = 99999; $u['hp'] = 100000; $u['maxhp'] = 100000; $u['mp'] = 9999; $u['maxmp'] = 9999;
user_save($u);

$created = [];
foreach (summoner_legends() as $nm => $cfg) {
    db()->exec('DELETE FROM equips WHERE uid=' . (int) $uid);
    $sn = make_equip((int) $uid, (string) $cfg['slot'], (string) $nm, 4, (int) $cfg['lv'], ['extra' => ['k' => (string) $cfg['key'], 'v' => (float) $cfg['param']]]);
    $created[$nm] = $sn;
    ckL("$nm 造出来了", $sn !== '', true);
    // 穿上
    $eid = (int) db()->query('SELECT id FROM equips WHERE uid=' . (int) $uid . ' ORDER BY id DESC LIMIT 1')->fetchColumn();
    db()->exec('UPDATE equips SET pos="wear" WHERE id=' . $eid);
}
_gear_uncache($uid);

// —— 全部穿上后，识别到全部 7 个效果 ——
db()->exec('DELETE FROM equips WHERE uid=' . (int) $uid);
foreach (summoner_legends() as $nm => $cfg) {
    make_equip((int) $uid, (string) $cfg['slot'], (string) $nm, 4, (int) $cfg['lv'], ['extra' => ['k' => (string) $cfg['key'], 'v' => (float) $cfg['param']]]);
}
db()->exec('UPDATE equips SET pos="wear" WHERE uid=' . (int) $uid);
_gear_uncache($uid);
$worn = summoner_legend_worn($uid);
foreach (summoner_legends() as $nm => $cfg) {
    ckL("识别 $nm", isset($worn[(string) $cfg['key']]), true);
}

// —— 深渊统御之印：召唤上限+1 ——
$u = user_by_id($uid);
ckL('统御之印加cap', summon_cap($u) >= 3, true);

// —— 嘶鸣：召唤物必定暴击（summon_turn 日志含"暴击"） ——
$_SESSION['battle'] = ['id'=>'rat','name'=>'鼠','hp'=>100000000,'maxhp'=>100000000,'atk'=>1,'num'=>1,'left'=>1,'history'=>[],'last'=>time()];
summon_save([['kind'=>'imp','name'=>'深渊小鬼','hp'=>9999,'maxhp'=>9999,'atk'=>1000,'turns'=>5,'bomb'=>false]]);
$st = summon_turn($_SESSION['battle']);
ckL('嘶鸣必暴击', str_contains($st['log'], '暴击'), true);
ckL('灵魂荆棘真伤', str_contains($st['log'], '灵魂撕裂真伤'), true);

// —— 灵魂荆棘单独（无嘶鸣）：非暴击 15% 真伤 ——
db()->exec('DELETE FROM equips WHERE uid=' . (int) $uid);
$cfgThorn = summoner_legends()['灵魂荆棘'];
make_equip((int) $uid, 'weapon', '灵魂荆棘', 4, 760, ['extra' => ['k'=>'slegend_soulthorn','v'=>15]]);
db()->exec('UPDATE equips SET pos="wear" WHERE uid=' . (int) $uid);
_gear_uncache($uid);
summon_save([['kind'=>'imp','name'=>'深渊小鬼','hp'=>9999,'maxhp'=>9999,'atk'=>1000,'turns'=>5,'bomb'=>false]]);
$st2 = summon_turn($_SESSION['battle']);
ckL('无嘶鸣不暴击', str_contains($st2['log'], '（暴击）'), false);
ckL('灵魂荆棘真伤仍在', str_contains($st2['log'], '灵魂撕裂真伤'), true);

// —— 尼德霍格的残鳞：召唤叠层、最大生命下降、伤害加成 ——
db()->exec('DELETE FROM equips WHERE uid=' . (int) $uid);
make_equip((int) $uid, 'body', '尼德霍格的残鳞', 4, 800, ['extra' => ['k'=>'slegend_rescale','v'=>10]]);
db()->exec('UPDATE equips SET pos="wear" WHERE uid=' . (int) $uid);
_gear_uncache($uid);
cflag_set($uid, 'rs_base', 0); cflag_set($uid, 'rs_stacks', 0);
$u = user_by_id($uid);
$u['maxhp'] = 100000; $u['hp'] = 100000; user_save($u);
$base0 = (int) user_by_id($uid)['maxhp'];
summon_clear();
$m1 = summon_add('imp', $u, 0);
ckL('残鳞提示', str_contains($m1, '残鳞x1'), true);
ckL('残鳞后maxhp下降', (int) $u['maxhp'] < $base0, true);
user_save($u);
$u2 = user_by_id($uid);
ckL('残鳞落库maxhp下降', (int) $u2['maxhp'] < $base0, true);
ckL('残鳞层数1', summoner_legend_rescale_stacks($uid), 1);
ckL('残鳞伤害加成', summon_power_bonus($u2) >= 20.0, true);
// 再召唤4只，叠满5层
summon_add('imp', $u, 0); summon_add('imp', $u, 0); summon_add('imp', $u, 0); summon_add('imp', $u, 0);
ckL('残鳞封顶5层', summoner_legend_rescale_stacks($uid), 5);
summoner_legend_rescale_clear(user_by_id($uid));
$u3 = user_by_id($uid);
ckL('残鳞清空恢复maxhp', (int) $u3['maxhp'], $base0);
ckL('残鳞清空层数', summoner_legend_rescale_stacks($uid), 0);

// —— 血契骨戒：只判定触发合法性（随机，多试几次应至少触发一次） ——
db()->exec('DELETE FROM equips WHERE uid=' . (int) $uid);
make_equip((int) $uid, 'ring', '血契骨戒', 4, 920, ['extra' => ['k'=>'slegend_bloodpact','v'=>20]]);
db()->exec('UPDATE equips SET pos="wear" WHERE uid=' . (int) $uid);
_gear_uncache($uid);
$u = user_by_id($uid);
$u['maxhp'] = 100000; $u['hp'] = 10000; user_save($u);
$fired = 0;
for ($i = 0; $i < 200; $i++) {
    $u = user_by_id($uid);
    $u['hp'] = max(1, (int) ($u['maxhp'] * 0.1)); user_save($u);
    summon_save([['kind'=>'imp','name'=>'深渊小鬼','hp'=>5,'maxhp'=>100,'atk'=>10,'turns'=>5,'bomb'=>false],
                 ['kind'=>'bone','name'=>'骸骨守卫','hp'=>80,'maxhp'=>200,'atk'=>10,'turns'=>5,'bomb'=>false]]);
    $b = ['id'=>'rat','name'=>'鼠','hp'=>999999,'maxhp'=>999999,'atk'=>1,'num'=>1,'left'=>1];
    $u = user_by_id($uid);
    $msg = summoner_legend_bloodpact($u, $b);
    if ($msg !== '') { $fired++; }
}
ckL('血契骨戒会触发', $fired > 0, true);
ckL('血契骨戒概率~20%', $fired >= 20 && $fired <= 70, true);

// —— 掉落来源：现有终局BOSS映射到7件 ——
$drops = summoner_legend_drops();
ckL('掉落7个来源', count($drops), 7);
foreach ($drops as $mid => $item) {
    ckL('掉落' . $mid . '对应的' . $item . '有定义', isset(summoner_legends()[$item]), true);
    ckL('掉落' . $mid . '在monsters表存在', isset(monsters()[$mid]), true);
}

db()->exec('DELETE FROM equips WHERE uid=' . (int) $uid);
db()->exec('DELETE FROM mats WHERE uid=' . (int) $uid);
db()->exec('DELETE FROM users WHERE id=' . (int) $uid);
$_SESSION = [];
echo "\nOK=$ok FAIL=$bad\n";