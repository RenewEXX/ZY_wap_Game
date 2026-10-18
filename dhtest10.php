<?php
require 'includes/bootstrap.php';
$ok = 0; $bad = 0;
function ck10(string $l, $got, $want) { global $ok,$bad; if ($got === $want) { $ok++; } else { $bad++; echo "FAIL $l: got=",var_export($got,true)," want=",var_export($want,true),"\n"; } }

$cat = skill_catalog();
$ss  = sin_skills();

echo "=== 问题1：rb2_passive_catalog 补齐召唤师2觉 ===\n";
$pc = rb2_passive_catalog();
foreach (rebirth2_names() as $job => $brs) {
    foreach ($brs as $rb => $nm) {
        ck10("catalog 齐全 $rb($nm)", isset($pc[$rb]), true);
        if (isset($pc[$rb])) {
            ck10("$rb 有 name", !empty($pc[$rb]['name']), true);
            ck10("$rb 有 desc", !empty($pc[$rb]['desc']), true);
        }
    }
}
ck10('necro 名称', $pc['necro']['name'] ?? '', '亡者军团');
ck10('plague 名称', $pc['plague']['name'] ?? '', '灵魂腐蚀');
// catalog 与 desc 函数对同一 rb 给出一致的被动名（npc 与 skills 两页不能打架）
foreach (array_keys(rebirth2_names()) as $job) {
    foreach (rebirth2_names()[$job] as $rb => $nm) {
        $d = rebirth2_passive_desc($rb);
        ck10("$rb desc含被动名", str_contains($d, $pc[$rb]['name'] ?? '##'), true);
    }
}

echo "\n=== 问题2：s9 罪技门槛纳入统一白名单 ===\n";
ck10('白名单含 s9', in_array('s9', $ss, true), true);
ck10('白名单5项', count($ss), 5);
foreach (['w9','m9','h9','p9','s9'] as $s) {
    ck10("$s 在白名单", in_array($s, $ss, true), true);
    ck10("$s 确实是罪技", ($cat[$s]['kind'] ?? '') === 'sin', true);
}
ck10('阈值常量', SIN_SKILL_NEED, 100);
// 每个职业确实都有对应罪技
foreach (['warrior','mage','hunter','priest','summoner'] as $job) {
    $set = job_skillset($job);
    $has = false;
    foreach ($set as $sid) { if (in_array($sid, $ss, true)) $has = true; }
    ck10("$job 拥有罪技", $has, true);
}

echo "\n=== 罪值门槛实测：召唤师也受约束 ===\n";
// 造一个没罪的召唤师，学会 s9
$uid = user_create('tsin10b', 'x', 'summoner', 'z1', true, 'tmp');
$_SESSION['uid'] = $uid;
$u = user_by_id($uid);
// 清零罪值
cflag_set($uid, 'sin', 0);
$u = user_by_id($uid);
ck10('罪值为0', sin_of($uid) < 100, true);

// 尝试激活 s9 → 应被拒
db()->prepare('INSERT OR IGNORE INTO skills (uid, skill, level, prof, tier, active) VALUES (?, "s9", 1, 0, 1, 0)')->execute([$uid]);
db()->exec('UPDATE skills SET active=0 WHERE uid=' . (int) $uid);
db()->prepare('UPDATE skills SET active=1 WHERE uid=? AND skill="s9"')->execute([$uid]);
$msg = set_active_skill($uid, 's9');
echo "无罪值激活 s9: $msg\n";
ck10('无罪值不能选 s9', str_contains($msg, '罪值'), true);

// 罪值满 → 允许
cflag_set($uid, 'sin', 500);
$msg2 = set_active_skill($uid, 's9');
echo "罪值500激活 s9: $msg2\n";
ck10('罪值满可选 s9', !str_contains($msg2, '罪值'), true);

// active_skill 不应把 s9 强制切走（罪值满时保持 s9）
$u = user_by_id($uid);
$act = active_skill($uid, 'summoner');
ck10('罪值满时 active=s9', (string)($act['skill'] ?? ''), 's9');

// 罪值掉下 100 → 强制切走 s9
cflag_set($uid, 'sin', 0);
$act2 = active_skill($uid, 'summoner');
echo "罪值归0后 active=" . ($act2['skill'] ?? 'null') . "\n";
ck10('罪值掉下100强制切走罪技', ($act2['skill'] ?? '') !== 's9', true);

db()->exec('DELETE FROM skills WHERE uid=' . (int) $uid);
db()->exec('DELETE FROM users WHERE id=' . (int) $uid);
$_SESSION = [];
echo "cleaned $uid\n";

echo "\nOK=$ok FAIL=$bad\n";

