<?php
define('DH_TEST_MODE', true);
require 'includes/bootstrap.php';
// 回归：传奇故事覆盖层 legend_lore()——可改可还原；未覆盖时透传原版。
$ok = 0; $bad = 0;
function ck96($l, $c, $d = '') { global $ok, $bad; if ($c) { $ok++; } else { $bad++; echo "FAIL $l $d\n"; } }

$name = '堕落骑士大剑';
dev_lore_delete($name);

$base = legend_lore_base($name);
ck96('默认故事非空', $base !== '' && !str_starts_with($base, '深渊里捞出来'), substr($base, 0, 12));
ck96('未覆盖时透传', legend_lore($name) === $base);

dev_lore_set($name, '这是我改过的传奇故事。');
ck96('覆盖故事生效', legend_lore($name) === '这是我改过的传奇故事。', legend_lore($name));

// 其它名字不受影响
ck96('其它名字不受影响', legend_lore('食人魔战裙') === legend_lore_base('食人魔战裙'));

dev_lore_delete($name);
ck96('删除后还原', legend_lore($name) === $base);

dev_lore_delete($name);
echo "OK=$ok FAIL=$bad\n";
