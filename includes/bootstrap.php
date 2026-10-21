<?php
declare(strict_types=1);

date_default_timezone_set('Asia/Shanghai');
session_start();
header('Content-Type: text/html; charset=utf-8');

define('ROOT', dirname(__DIR__));
// 测试脚本（dhtest*.php）走独立库，绝不碰玩家的真实存档
if (defined('DH_TEST_MODE') && DH_TEST_MODE) {
    define('DATA_DIR', ROOT . DIRECTORY_SEPARATOR . 'data_test');
} else {
    define('DATA_DIR', ROOT . DIRECTORY_SEPARATOR . 'data');
}

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/wap.php';
require_once __DIR__ . '/game.php';
require_once __DIR__ . '/world_expansion.php';
require_once __DIR__ . '/world_expansion2.php';
require_once __DIR__ . '/legend_relics.php';
require_once __DIR__ . '/dragons.php';
require_once __DIR__ . '/dragon_wb.php';
require_once __DIR__ . '/summoner_gear.php';
require_once __DIR__ . '/summoner_legend.php';
require_once __DIR__ . '/hunter_dot.php';
require_once __DIR__ . '/warrior_hunter.php';
require_once __DIR__ . '/monster_skills.php';
require_once __DIR__ . '/arena.php';
require_once __DIR__ . '/rebirth.php';

db_init();
