<?php
// dhtest83：王都中央大道不再同时出现两个瓦尔顿（同一人按剧情进度只显示一个）
//  - 瓦尔顿早期是「线人」（第三章），开启1觉线（quest>=38）后改称「总会长」。
//  - 旧实现把两个都写死在 avenue 的在场列表里，导致同一人同时出现两次。
define('DH_TEST_MODE', true);
require 'includes/bootstrap.php';
$ok = 0; $bad = 0;
function ck83(string $l, bool $c, string $d = '') { global $ok, $bad; if ($c) { $ok++; } else { $bad++; echo "FAIL $l $d\n"; } }

$src = file_get_contents(__DIR__ . '/home.php');

// 1) avenue 的静态在场列表里不能再同时写死 线人·瓦尔顿 + 总会长·瓦尔顿
ck83('静态 avenue 不再写死「线人·瓦尔顿」', !str_contains($src, "'avenue' => [['waldon'"));
ck83('静态 avenue 不再写死「总会长·瓦尔顿」', !str_contains($src, "'avenue' => [['rb_waldon'"));

// 2) 必须存在按进度二选一的逻辑，且两个身份仍可达（分别对应不同阶段）
ck83('存在按进度二选一的逻辑', str_contains($src, '$waldonAwake'));
ck83('仍保留「线人·瓦尔顿」分支', str_contains($src, "['waldon', '线人·瓦尔顿']"));
ck83('仍保留「总会长·瓦尔顿」分支', str_contains($src, "['rb_waldon', '总会长·瓦尔顿']"));

// 3) 门槛是 1觉线：quest >= 38
ck83('门槛为 quest >= 38（1觉）', (bool) preg_match("/\\\$waldonAwake\s*=\s*\(int\)\s*\(\\\$u\['quest'\]\s*\?\?\s*0\)\s*>=\s*38/", $src));

echo "\nOK=$ok FAIL=$bad\n";
