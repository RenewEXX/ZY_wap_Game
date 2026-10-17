<?php
$p = __DIR__ . '/includes/game.php';
$t = file_get_contents($p);
$old = "    // 血罐：血量<10%才自动喝，无冷却；PVP（巅峰/公会战/切磋）不生效；致命伤害不救\n    \$pct = \$kind === 'hp' ? 10 : 30;";
if (strpos($t, $old) === false) { echo "MISS\n"; exit(1); }
$t = str_replace($old, "    // 血罐：血量<15%自动喝，无冷却；PVP（巅峰/公会战/切磋）不生效；致命伤害不救\n    \$pct = \$kind === 'hp' ? 15 : 30;", $t);
// 商城/背包/药罐文案里的15%→同步
$n = 0;
foreach (['血低于15%', '血量低于15%', '<15%', '10%才自动'] as $k) { $n += substr_count($t, $k); }
file_put_contents($p, $t);
echo "OK refs15: $n\n";
