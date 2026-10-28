<?php
define('DH_TEST_MODE', true);
require 'includes/bootstrap.php';
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
// 头像系统审计：一觉(rb1)头像 + 二转头像 + 商城二次元头像(付费解锁/切换) + 行动画面不显示
$ok=0;$bad=0;
function ck56($l,$c,$d=''){ global $ok,$bad; if($c){$ok++;}else{$bad++;echo "FAIL $l $d\n";} }
function del56(int $uid){ db()->exec("DELETE FROM users WHERE id=$uid");db()->exec("DELETE FROM equips WHERE uid=$uid");db()->exec("DELETE FROM mats WHERE uid=$uid"); }

$uid=(int)user_create('a56'.mt_rand(1,999999),'x','warrior','z1',true,'tmp');
$u=user_by_id($uid);

// ---- 1) 头像表完整性 ----
$cat=avatar_catalog();
foreach(['warrior','mage','hunter','priest','summoner'] as $j){
    ck56("rb1:$j 存在", isset($cat['rb1:'.$j]) && ($cat['rb1:'.$j]['job']??'')===$j);
    ck56("anime:$j 存在且shop", isset($cat['anime:'.$j]) && ($cat['anime:'.$j]['job']??'')===$j && !empty($cat['anime:'.$j]['shop']));
}
ck56('rb2 共10个', count(array_filter(array_keys($cat),fn($k)=>str_starts_with($k,'rb2:')))===10);
ck56('job 共5个', count(array_filter(array_keys($cat),fn($k)=>str_starts_with($k,'job:')))===5);

// ---- 2) 默认头像逐级：职业→一觉→二觉 ----
ck56('未觉默认=job', avatar_default_id($u)==='job:warrior', avatar_default_id($u));
ck56('未觉解锁含job', in_array('job:warrior', avatar_unlocked_ids($u)));
ck56('未觉不含rb1', !in_array('rb1:warrior', avatar_unlocked_ids($u)));

cflag_set($uid,'rb1_job','warrior'); $u=user_by_id($uid);
ck56('一觉默认=rb1', avatar_default_id($u)==='rb1:warrior', avatar_default_id($u));
ck56('一觉解锁含rb1', in_array('rb1:warrior', avatar_unlocked_ids($u)));

cflag_set($uid,'rb2_job','berserker'); $u=user_by_id($uid);
ck56('二觉默认=rb2', avatar_default_id($u)==='rb2:berserker', avatar_default_id($u));
ck56('二觉解锁含rb2', in_array('rb2:berserker', avatar_unlocked_ids($u)));

// ---- 3) 二次元头像：未购不可用 → 付费解锁 → 可切换（跨职业）----
ck56('未购不含anime', !in_array('anime:warrior', avatar_unlocked_ids($u)));
avatar_set($uid,'anime:warrior'); $u=user_by_id($uid);
ck56('未购不能设anime', ($u['avatar']??'')!=='anime:warrior', (string)($u['avatar']??''));

avatar_anime_grant($uid,'warrior'); $u=user_by_id($uid);
ck56('购买后owned', avatar_anime_owned($uid,'warrior'));
ck56('购买后解锁含anime', in_array('anime:warrior', avatar_unlocked_ids($u)));
avatar_set($uid,'anime:warrior'); $u=user_by_id($uid);
ck56('可切到二次元头像', avatar_of($u)==='anime:warrior', avatar_of($u));

// 本职业专属：战士即使买了法师的二次元头像也不能用
avatar_anime_grant($uid,'mage'); $u=user_by_id($uid);
ck56('跨职业anime不解锁', !in_array('anime:mage', avatar_unlocked_ids($u)));
avatar_set($uid,'anime:mage'); $u=user_by_id($uid);
ck56('战士不能用法师二次元头像', avatar_of($u)!=='anime:mage', avatar_of($u));

// 二次元头像渲染为 Q版 SVG 少女；职业/转职头像仍是徽章字形
$animeHtml = avatar_render('anime:warrior', 40);
ck56('二次元渲染为SVG', str_contains($animeHtml,'<svg') && str_contains($animeHtml,'linearGradient'));
ck56('SVG含大眼高光', str_contains($animeHtml,'#ffffff'));
$badgeHtml = avatar_render('job:warrior', 40);
ck56('职业头像仍为徽章(非SVG)', !str_contains($badgeHtml,'<svg') && str_contains($badgeHtml,'display:inline-flex'));

// 恢复默认 → 回到当前二觉
avatar_set($uid,''); $u=user_by_id($uid);
ck56('恢复默认=二觉', avatar_of($u)==='rb2:berserker', avatar_of($u));

// ---- 4) 商城上架 5 个二次元头像 ----
$g=mall_goods();
$ag=array_filter(array_keys($g),fn($k)=>str_starts_with($k,'avatar_anime_'));
ck56('商城上架5个二次元头像', count($ag)===5, (string)count($ag));
ck56('商城头像有价格', isset($g['avatar_anime_warrior']['price']) && (int)$g['avatar_anime_warrior']['price']>0);

// ---- 5) 行动画面(home.php)不再显示头像；状态页/看别人仍显示 ----
$home=file_get_contents(__DIR__.'/home.php');
ck56('home.php 行动画面无头像', strpos($home,'avatar_html')===false && strpos($home,'avatar_render')===false);
$status=file_get_contents(__DIR__.'/status.php');
ck56('status.php 状态画面有头像', strpos($status,'avatar_html')!==false);
$look=file_get_contents(__DIR__.'/look.php');
ck56('look.php 看别人有头像', strpos($look,'avatar_html')!==false);

del56($uid);
echo "\nOK=$ok FAIL=$bad\n";
