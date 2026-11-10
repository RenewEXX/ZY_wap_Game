<?php
define('DH_TEST_MODE', true);
require 'includes/bootstrap.php';
// 安全回归：avatar_image_url / avatar_render。只用临时专用 key，绝不读取或删除真实头像文件。
$ok = 0; $bad = 0;
function ck94($l, $c, $d = '') { global $ok, $bad; if ($c) { $ok++; } else { $bad++; echo "FAIL $l $d\n"; } }

$dir = __DIR__ . '/img/avatars';
if (!is_dir($dir)) { @mkdir($dir, 0777, true); }
$exts = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
$keys = ['zz_none__94x', 'zz_map__94x', 'zz_prio__94x'];
foreach ($keys as $k) { foreach ($exts as $e) { @unlink($dir . '/' . $k . '.' . $e); } }

// 1) 未知 id（确保无任何扩展文件）→ 空串
ck94('无图空串', avatar_image_url('zz:none__94x') === '', avatar_image_url('zz:none__94x'));

// 2) ':' → '_' 映射 + 渲染用图片
$id = 'zz:map__94x'; $key = 'zz_map__94x';
file_put_contents($dir . '/' . $key . '.jpg', 'x');
ck94('id冒号换下划线', avatar_image_url($id) === 'img/avatars/' . $key . '.jpg', avatar_image_url($id));
$html = avatar_render($id, 40);
ck94('渲染含img', str_contains($html, '<img') && str_contains($html, 'img/avatars/' . $key . '.jpg'), $html);

// 3) 支持 png 扩展（只建 .png，应返回 .png）
$id2 = 'zz:prio__94x'; $key2 = 'zz_prio__94x';
file_put_contents($dir . '/' . $key2 . '.png', 'x');
ck94('支持png扩展', avatar_image_url($id2) === 'img/avatars/' . $key2 . '.png', avatar_image_url($id2));

// 清理仅自建临时文件
foreach ($keys as $k) { foreach ($exts as $e) { @unlink($dir . '/' . $k . '.' . $e); } }
echo "OK=$ok FAIL=$bad\n";
