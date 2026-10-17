<?php
// 1.职业说明函数
$p = __DIR__ . '/includes/game.php';
$t = file_get_contents($p);
$anchor = 'function equip_can_wear(';
$fn = "function equip_jobs_text(array \$e): string\n{\n    \$slot = (string) (\$e['slot'] ?? '');\n    \$nm = (string) (\$e['name'] ?? '');\n    if (\$slot === 'title') { return '全职业（100级可戴）'; }\n    if (\$slot === 'weapon') {\n        \$m = ['staff' => '法师/牧师', 'axe' => '战士', 'bow' => '猎手', 'mace' => '战士/牧师', 'shield' => '战士/牧师', 'blade' => '战士/猎手'];\n        return \$m[weapon_sub(\$nm)] ?? '战士';\n    }\n    if (\$slot === 'offhand') {\n        if (str_contains(\$nm, '盾')) { return '战士/牧师'; }\n        if (str_contains(\$nm, '法器') || str_contains(\$nm, '法杖') || str_contains(\$nm, '提灯')) { return '法师/牧师'; }\n        return '全职业';\n    }\n    return '全职业（战法猎牧）';\n}\n\n";
$pos = strpos($t, $anchor);
$t = substr($t, 0, $pos) . $fn . substr($t, $pos);
file_put_contents($p, $t);
echo "OK func\n";
