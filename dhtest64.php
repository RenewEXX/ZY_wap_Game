<?php
// dhtest64：全游戏货币进制统一（金币 100 进制 / 魔钻 10 进制）+ 关键展示
define('DH_TEST_MODE', true);
require 'includes/bootstrap.php';
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
$ok = 0; $bad = 0;
function ck64(string $l, bool $c, string $d = '') { global $ok, $bad; if ($c) { $ok++; } else { $bad++; echo "FAIL $l $d\n"; } }

// 金币：1金=100银=10000铜
ck64('1铜', fmt_money(1) === '1铜', fmt_money(1));
ck64('99铜', fmt_money(99) === '99铜', fmt_money(99));
ck64('100铜=1银', fmt_money(100) === '1银', fmt_money(100));
ck64('199铜=1银99铜', fmt_money(199) === '1银99铜', fmt_money(199));
ck64('500铜=5银', fmt_money(500) === '5银', fmt_money(500));
ck64('8000铜=80银', fmt_money(8000) === '80银', fmt_money(8000));
ck64('10000铜=1金', fmt_money(10000) === '1金', fmt_money(10000));
ck64('10001铜=1金1铜', fmt_money(10001) === '1金1铜', fmt_money(10001));
ck64('50000铜=5金', fmt_money(50000) === '5金', fmt_money(50000));
ck64('1000000铜=100金', fmt_money(1000000) === '100金', fmt_money(1000000));
ck64('1金=100银', fmt_money(10000) === fmt_money(10000) && fmt_money(100 * 100) === '1金', fmt_money(10000));

// 魔钻：内部存 0.1 钻，10 内部=1魔钻
ck64('1内部=0.1魔钻', fmt_diamond(1) === '0.1魔钻', fmt_diamond(1));
ck64('10内部=1魔钻', fmt_diamond(10) === '1魔钻', fmt_diamond(10));
ck64('100内部=10魔钻', fmt_diamond(100) === '10魔钻', fmt_diamond(100));
ck64('1000内部=100魔钻', fmt_diamond(1000) === '100魔钻', fmt_diamond(1000));
ck64('diamond_to_internal(0.1)=1', diamond_to_internal(0.1) === 1, (string) diamond_to_internal(0.1));
ck64('diamond_to_internal(10)=100', diamond_to_internal(10) === 100, (string) diamond_to_internal(10));

// 公会升级需求：1升2要 100 金，且展示为「100金」
ck64('公会1升2=100金', guild_level_need(1) === 1000000 && fmt_money(guild_level_need(1)) === '100金', fmt_money(guild_level_need(1)));

// 世界BOSS名次奖励与邮件文案一致（1名=500银/100魔钻，2~3=200银/50魔钻，4~10=80银/20魔钻，其余=5魔钻）
if (function_exists('drg_place_reward')) {
    // 值：500银=50000铜，100魔钻=1000内部
    ck64('世界BOSS第1名=500银/100魔钻', drg_place_reward(1) === [50000, 1000], json_encode(drg_place_reward(1)));
    ck64('世界BOSS第3名=200银/50魔钻', drg_place_reward(3) === [20000, 500], json_encode(drg_place_reward(3)));
    ck64('世界BOSS第10名=80银/20魔钻', drg_place_reward(10) === [8000, 200], json_encode(drg_place_reward(10)));
    ck64('世界BOSS参与=5魔钻', drg_place_reward(99) === [0, 50], json_encode(drg_place_reward(99)));
    // 展示走最大单位：500银=5金，200银=2金，80银=80银
    ck64('世界BOSS第1名显示5金', fmt_money(drg_place_reward(1)[0]) === '5金', fmt_money(drg_place_reward(1)[0]));
    ck64('世界BOSS第3名显示2金', fmt_money(drg_place_reward(3)[0]) === '2金', fmt_money(drg_place_reward(3)[0]));
    ck64('世界BOSS第10名显示80银', fmt_money(drg_place_reward(10)[0]) === '80银', fmt_money(drg_place_reward(10)[0]));
}

echo "\nOK=$ok FAIL=$bad\n";
