<?php
require 'includes/bootstrap.php';
$ok = 0; $bad = 0;
function ck9(string $l, $got, $want) { global $ok,$bad; if ($got === $want) { $ok++; } else { $bad++; echo "FAIL $l: got=",var_export($got,true)," want=",var_export($want,true),"\n"; } }

// —— 回归：dmg_to_player 返回数组，wb.php 不再 TypeError ——
ck9('dmg_to_player 返回数组', is_array(dmg_to_player(user_by_id(5), monsters()['knight'])), true);
[$d, $tg] = dmg_to_player(user_by_id(5), monsters()['knight']);
ck9('龙王反击乘法不再报错', is_int(max(1, (int) ($d * 2.3))), true);
ck9('伤害≥1', $d >= 1, true);

// —— player_dmg_pipeline 封装 ——
$u = user_by_id(5);
ck9('封装返回数组', count(player_dmg_pipeline($u, 1000)) === 2, true);
ck9('base<=0 短路', player_dmg_pipeline($u, 0), [0, '']);
// 精准收窄生效
function w9(float $p, int $n = 3000): int {
    $mx = 0; $mn = PHP_INT_MAX;
    for ($i=0;$i<$n;$i++){ [$x] = dmg_pipeline(1000,0,0,$p); $mx=max($mx,$x); $mn=min($mn,$x); }
    return $mx - $mn;
}
ck9('高精准更窄', w9(30.0) < w9(0.0), true);

// —— 档位标记在PK可用 ——
$tags = [];
for ($i=0;$i<5000;$i++){ [, $t] = player_dmg_pipeline($u, 1000); if($t!=='')$tags[$t]=($tags[$t]??0)+1; }
echo "PK档位标记样本: " . json_encode($tags, JSON_UNESCAPED_UNICODE) . "\n";

// —— 三处PVP与wb.php都已接入（静态检查）——
// fight.php 应有6处 player_dmg_pipeline，wb.php 应有 player_dmg_pipeline + 解包dmg_to_player
$fs = file_get_contents(__DIR__ . '/fight.php');
$ws = file_get_contents(__DIR__ . '/wb.php');
ck9('fight.php 6处管线', substr_count($fs, 'player_dmg_pipeline('), 6);
ck9('fight.php 3处flash带标记', substr_count($fs, "(\$pdTag !== ''"), 3);
ck9('fight.php 无残留裸乘法', str_contains($fs, 'dmg_to_player') , false);
ck9('wb.php 玩家侧接入', str_contains($ws, 'player_dmg_pipeline('), true);
ck9('wb.php 解包dmg_to_player', (bool) preg_match('/\[\s*\$\w+Md,\s*\$\w+Tag\s*\]\s*=\s*dmg_to_player\(/', $ws), true);
ck9('wb.php 不再有 数组*float', str_contains($ws, 'dmg_to_player($u, $boss) *'), false);

echo "OK=$ok FAIL=$bad\n";