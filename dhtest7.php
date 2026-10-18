<?php
require 'includes/bootstrap.php';
$ok = 0; $bad = 0;
function chk7(string $l, $got, $want) { global $ok,$bad; if ($got === $want) { $ok++; } else { $bad++; echo "FAIL $l: got=",var_export($got,true)," want=",var_export($want,true),"\n"; } }

// —— 精准收窄区间 ——
chk7('精准0 = ±18%', dmg_variance_half(0.0), 0.18);
chk7('精准10 = ±13%', round(dmg_variance_half(10.0), 4), round(0.13, 4));
chk7('精准28 触底±4%', dmg_variance_half(28.0), 0.04);
chk7('精准100 仍±4%', dmg_variance_half(100.0), 0.04);

// —— 浮动幅度：精准越高越窄 ——
function spread(float $prec, int $n = 4000): array {
    $min = PHP_INT_MAX; $max = 0; $sum = 0;
    for ($i = 0; $i < $n; $i++) {
        [$d] = dmg_pipeline(1000, 0, 0, $prec);
        $min = min($min, $d); $max = max($max, $d); $sum += $d;
    }
    return [$min, $max, $sum / $n];
}
[$lo0, $hi0, $av0] = spread(0.0);
[$loP, $hiP, $avP] = spread(30.0);
$w0 = $hi0 - $lo0; $wP = $hiP - $loP;
echo "精准0  区间[{$lo0},{$hi0}] 宽{$w0}\n";
echo "精准30 区间[{$loP},{$hiP}] 宽{$wP}\n";
chk7('高精准区间更窄', $wP < $w0, true);

// —— 档位概率 ——
$tags = [''=>0,'重击!'=>0,'完美一击!'=>0];
for ($i=0;$i<20000;$i++){[, $t] = dmg_pipeline(1000,0,0,30.0); $tags[$t] = ($tags[$t] ?? 0)+1;}
$pPer = $tags['完美一击!']/20000; $pHea = $tags['重击!']/20000;
echo '完美一击≈'.round($pPer*100,1).'% 重击≈'.round($pHea*100,1)."%\n";
chk7('完美一击~2%', $pPer > 0.010 && $pPer < 0.035, true);
chk7('重击~8%', $pHea > 0.05 && $pHea < 0.12, true);
chk7('完美一击伤害>x1.8', $hiP > 1800, true);

// —— 暴击独立、且与完美一击共存 ——
$both = 0; $critOnly = 0; $noCrit = 0;
for ($i=0;$i<20000;$i++){
    [, $t] = dmg_pipeline(1000, 50, 100, 10.0); // 50%暴击 +100%
    if ($t === '完美一击!+暴击!') $both++;
    elseif ($t === '暴击!') $critOnly++;
    else $noCrit++;
}
echo '暴击率≈'.round(($both+$critOnly)/20000*100,1)."% 其中与完美一击同触发{$both}次\n";
chk7('暴击率≈50%', ($both+$critOnly)/20000 > 0.40 && ($both+$critOnly)/20000 < 0.60, true);
chk7('完美+暴击可共存', $both > 0, true);

// —— 边界 ——
chk7('base=0不伤害', dmg_pipeline(0, 50, 100, 0), [0, '']);
chk7('最小伤害≥1', dmg_pipeline(1, 0, 0, 30)[0] >= 1, true);

// —— 精准词条注册 ——
chk7('精准已注册', isset(affix_catalog()['precision']), true);
chk7('gear_stats认精准', array_key_exists('precision', gear_stats(1)), true);
chk7('精准是整数显示', affix_fmt('precision', 10.0, 'T3'), 'T3 精准+10');

echo "OK=$ok FAIL=$bad\n";