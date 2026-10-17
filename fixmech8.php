<?php
$p = __DIR__ . '/includes/game.php';
$lines = file($p);
// 定位m5冻结块结束
$idx = null;
foreach ($lines as $i => $l) {
    if (strpos($l, "if (\$sid === 'm5' && \$sTier >= 10") !== false) { $idx = $i; break; }
}
if ($idx === null) { echo "MISS m5\n"; exit(1); }
$end = null;
for ($k = $idx + 1; $k < $idx + 6; $k++) {
    if (trim($lines[$k]) === '}') { $end = $k; break; }
}
$add = [
    "                if (\$sid === 'w7') {\n",
    "                    \$leech7 = \$sTier >= 10 ? 0.15 : 0.1;\n",
    "                    \$hl7 = (int) (\$pd * \$leech7);\n",
    "                    \$u['hp'] = min((int) \$u['maxhp'], (int) \$u['hp'] + \$hl7);\n",
    "                    \$logHeal .= '(BloodRage+' . \$hl7 . ')';\n",
    "                }\n",
    "                if (\$sid === 'w8' && \$sTier >= 10) {\n",
    "                    \$b['breach'] = 3;\n",
    "                    \$logHeal .= '(WallBreak)';\n",
    "                }\n",
    "                if (\$sid === 'm7' && \$sTier >= 10 && mt_rand(1, 100) <= (\$sLv >= 20 ? 30 : 25)) {\n",
    "                    \$b['stun'] = 1;\n",
    "                    \$logHeal .= '(ThunderPara)';\n",
    "                }\n",
    "                if (\$sid === 'h7' && \$sTier >= 10 && mt_rand(1, 100) <= 30) {\n",
    "                    \$pd = (int) (\$pd * (\$sLv >= 20 ? 1.8 : 1.6));\n",
    "                    \$logHeal .= '(CloudCrit)';\n",
    "                }\n",
    "                \$exeLine8 = \$sLv >= 20 ? 0.6 : 0.5;\n",
    "                if (\$sid === 'h8' && (int) (\$b['maxhp'] ?? 1) > 0 && (int) (\$b['hp'] ?? 1) <= (int) (\$b['maxhp'] ?? 1) * \$exeLine8) {\n",
    "                    \$pd = (int) (\$pd * 1.5);\n",
    "                    \$logHeal .= '(NightExec)';\n",
    "                }\n",
    "                if (\$sid === 'p7') {\n",
    "                    \$leechRate7 = \$sLv >= 20 ? 0.6 : 0.5;\n",
    "                    \$leech7b = (int) (\$pd * \$leechRate7);\n",
    "                    \$u['hp'] = min((int) \$u['maxhp'], (int) \$u['hp'] + \$leech7b);\n",
    "                    \$logHeal .= '(SoulLeech+' . \$leech7b . ')';\n",
    "                }\n",
    "                if (\$sid === 'p8') {\n",
    "                    \$heal8 = (int) (\$sk['heal'] * \$pow) + (int) (player_matk(\$u) * 0.2);\n",
    "                    if (\$sTier >= 10) { \$heal8 += 50; }\n",
    "                    if (\$sLv >= 20) { \$heal8 = (int) (\$heal8 * 1.2); }\n",
    "                    if ((cflags((int) (\$u['id'] ?? 0))['rb2_job'] ?? '') === 'holybishop') { \$heal8 = (int) (\$heal8 * 1.15); \$logHeal .= '(HolyBless)'; }\n",
    "                    \$u['hp'] = min((int) \$u['maxhp'], (int) \$u['hp'] + \$heal8);\n",
    "                    \$logHeal .= '(Heal8+' . \$heal8 . ')';\n",
    "                    \$mateRate8 = \$sLv >= 20 ? 0.8 : 0.6;\n",
    "                    \$mparty8 = my_party((int) (\$u['id'] ?? 0));\n",
    "                    if (\$mparty8) {\n",
    "                        foreach (party_mates((int) \$mparty8['id'], (int) \$u['id']) as \$mate) {\n",
    "                            \$mu = user_by_id((int) \$mate['id']);\n",
    "                            if (\$mu && (int) \$mu['hp'] > 0) {\n",
    "                                \$mh8 = (int) (\$heal8 * \$mateRate8);\n",
    "                                \$mu['hp'] = min((int) \$mu['maxhp'], (int) \$mu['hp'] + \$mh8);\n",
    "                                user_save(\$mu);\n",
    "                                \$logHeal .= '(MateHeal8+' . \$mh8 . ')';\n",
    "                            }\n",
    "                        }\n",
    "                    }\n",
    "                }\n",
];
array_splice($lines, $end + 1, 0, $add);
file_put_contents($p, implode('', $lines));
echo "OK mech\n";
