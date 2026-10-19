<?php
define('DH_TEST_MODE', true);
require 'includes/bootstrap.php';
// 真实跑几场战斗，确认防战反击/狂战掉血/DOT/刺客单体都在战报里出现
$uid = user_create('tbattle12d', 'x', 'warrior', 'z1', true, 'tmp');
$_SESSION['uid'] = $uid;
$u = user_by_id($uid);
$u['lv'] = 300; $u['def'] = 3000; $u['str'] = 300; $u['maxhp'] = 200000; $u['hp'] = 200000; $u['maxmp'] = 500; $u['mp'] = 500;
user_save($u);

// 给全套战士技能
foreach (['w1','w2','w3','w4','w5','w6','w7','w8'] as $s) {
    db()->prepare('INSERT OR IGNORE INTO skills (uid, skill, level, prof, tier, active) VALUES (?,?,20,10,10,?)')
        ->execute([$uid, $s, $s === 'w2' ? 1 : 0]);
}
$u = user_by_id($uid);
$cflags = cflags($uid);
$logs = [];
$thornHits = 0; $rageSeen = false; $tankSeen = false;
for ($i = 0; $i < 6; $i++) {
    $mid = ['knight','ogre','jailer'][$i % 3];
    $m = monsters()[$mid];
    // 用极高血量的怪，保证玩家秒不掉，怪能还手触发反击/狂战
    $b = ['id'=>$mid,'name'=>$m['name'],'hp'=>50000000,'maxhp'=>50000000,'atk'=>$m['atk'],
          'exp'=>$m['exp'],'gold'=>$m['gold'],'log'=>'',
          'num'=>1,'left'=>1,'wexp'=>0,'wgold'=>0,'drops'=>[],'last'=>time()];
    $_SESSION['battle'] = $b;
    for ($r = 0; $r < 40; $r++) {
        $u = user_by_id($uid);
        if ((int) $u['hp'] <= 0) { $u['hp'] = (int) $u['maxhp']; user_save($u); break; }
        $res = battle_round($u, $_SESSION['battle'], $i === 0 ? 'skill' : 'hit');
        $all = ($res['log'] ?? '') . ($res['flash'] ?? '');
        if (str_contains($all, '铁壁反击')) { $thornHits++; $tankSeen = true; }
        if (str_contains($all, '狂战 损血')) { $rageSeen = true; }
        if ($res['status'] !== 'fight') break;
    }
    $bnow = $_SESSION['battle'] ?? [];
    $logs[] = '第' . ($i+1) . '场 怪剩余' . ($bnow['hp'] ?? 0) . '/' . ($bnow['maxhp'] ?? 0) . ' 本场损血累计=' . warr_hp_lost($bnow);
}
echo "\n铁壁反击触发次数: $thornHits " . ($thornHits > 0 ? 'OK' : '★未触发★') . "\n";
echo "狂战加成出现: " . ($rageSeen ? 'OK' : '★未出现★') . "\n";
echo "防御加成出现: " . ($tankSeen ? 'OK' : '★未出现★') . "\n";
// 打印一次实际战报看看
$_SESSION['battle'] = $b;
$u = user_by_id($uid);
$u['hp'] = (int) $u['maxhp'];
$res = battle_round($u, $b, 'skill');
echo "\n--- 实际战报 ---\n" . $res['log'] . "\n";

unset($_SESSION['battle']);
db()->exec('DELETE FROM skills WHERE uid=' . (int) $uid);
db()->exec('DELETE FROM users WHERE id=' . (int) $uid);

// === 猎手：游侠群攻DOT / 刺客单体 ===
function runHunter(string $cls, array $skills): array {
    $uid = user_create('th'.$cls, 'x', 'hunter', 'z1', true, 'tmp');
    $_SESSION['uid'] = $uid;
    $u = user_by_id($uid);
    $u['lv']=300; $u['agi']=200; $u['maxhp']=200000; $u['hp']=200000; $u['mp']=9999;
    user_save($u);
    cflag_set($uid, 'rb2_job', $cls);
    foreach ($skills as $s) {
        db()->prepare('INSERT OR IGNORE INTO skills (uid, skill, level, prof, tier, active) VALUES (?,?,20,10,10,?)')->execute([$uid,$s,0]);
    }
    // 只激活第一个
    db()->exec('UPDATE skills SET active=0 WHERE uid='.(int)$uid);
    db()->prepare('UPDATE skills SET active=1 WHERE uid=? AND skill=?')->execute([$uid, $skills[0]]);
    $m = monsters()['knight'];
    $b = ['id'=>'knight','name'=>$m['name'],'hp'=>50000000,'maxhp'=>50000000,'atk'=>1,'exp'=>$m['exp'],'gold'=>$m['gold'],'log'=>'','num'=>1,'left'=>1,'wexp'=>0,'wgold'=>0,'drops'=>[],'last'=>time()];
    $_SESSION['battle'] = $b;
    $u = user_by_id($uid);
    $res = battle_round($u, $_SESSION['battle'], 'skill');
    unset($_SESSION['battle']);
    db()->exec('DELETE FROM skills WHERE uid='.(int)$uid);
    db()->exec('DELETE FROM users WHERE id='.(int)$uid);
    $_SESSION = [];
    return $res;
}
$rRanger = runHunter('ranger', ['h5']);
echo "\n--- 游侠·万箭齐发(群攻DOT) ---\n" . $rRanger['log'] . "\n";
$rAssn = runHunter('assassin', ['h8']);
echo "\n--- 刺客·夜幕绞杀(单体爆发) ---\n" . $rAssn['log'] . "\n";
echo "\n游侠挂了DOT: " . (str_contains($rRanger['log'], '出血') ? 'OK' : '★无★') . "\n";
echo "刺客标记影遁/必暴: " . (str_contains($rAssn['log'], '影遁') || str_contains($rAssn['log'], '必暴') ? 'OK' : '★无★') . "\n";

