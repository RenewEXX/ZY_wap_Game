<?php
define('DH_TEST_MODE', true);
require 'includes/bootstrap.php';
// 回归：怪物立绘查找 monster_image_url()——有图返回相对URL，没图返回空串，非法id返回空串。
$ok = 0; $bad = 0;
function ck93($l, $c, $d = '') { global $ok, $bad; if ($c) { $ok++; } else { $bad++; echo "FAIL $l $d\n"; } }

$dir = __DIR__ . '/img/monsters';
if (!is_dir($dir)) { @mkdir($dir, 0777, true); }
$f = $dir . '/__zz_probe.jpg';
@unlink($f);

ck93('无图返回空串', monster_image_url('__zz_probe') === '', monster_image_url('__zz_probe'));

file_put_contents($f, 'x');
ck93('有图返回URL', monster_image_url('__zz_probe') === 'img/monsters/__zz_probe.jpg', monster_image_url('__zz_probe'));

// 其它扩展名
$fpng = $dir . '/__zz_probe2.png';
@unlink($fpng);
file_put_contents($fpng, 'x');
ck93('png也能识别', monster_image_url('__zz_probe2') === 'img/monsters/__zz_probe2.png', monster_image_url('__zz_probe2'));

// 非法id（含路径穿越）会被清洗为空
ck93('非法id返回空串', monster_image_url('../../etc/passwd') === '', monster_image_url('../../etc/passwd'));
ck93('空id返回空串', monster_image_url('') === '', '');

// 真实怪物：无图时返回空（不会破图），不报错
ck93('真实怪物不报错', is_string(monster_image_url('goblin')), monster_image_url('goblin'));

// 中文名兜底：id 找不到时按名字找
$cn = '哥布林哨兵x2';
$fc = $dir . '/' . $cn . '.jpg';
@unlink($fc);
file_put_contents($fc, 'x');
$expectCn = 'img/monsters/' . rawurlencode($cn) . '.jpg';
ck93('中文名兜底命中', monster_image_url('no_such_id_zz', $cn) === $expectCn, monster_image_url('no_such_id_zz', $cn));

// id 优先于名字
$fid = $dir . '/__zz_pri.jpg';
$fnm = $dir . '/__zz_pri_name.jpg';
@unlink($fid); @unlink($fnm);
file_put_contents($fid, 'x');
file_put_contents($fnm, 'x');
ck93('id优先于名字', monster_image_url('__zz_pri', '__zz_pri_name') === 'img/monsters/__zz_pri.jpg', monster_image_url('__zz_pri', '__zz_pri_name'));

// 名字里的文件名非法字符被清洗（a/b\c:d*e -> abcde）
$fclean = $dir . '/abcde.jpg';
@unlink($fclean);
file_put_contents($fclean, 'x');
ck93('名字非法字符清洗', monster_image_url('', 'a/b\\c:d*e') === 'img/monsters/abcde.jpg', monster_image_url('', 'a/b\\c:d*e'));

@unlink($f);
@unlink($fpng);
@unlink($fc);
@unlink($fid);
@unlink($fnm);
@unlink($fclean);

echo "OK=$ok FAIL=$bad\n";
