<?php
declare(strict_types=1);

date_default_timezone_set('Asia/Shanghai');
session_start();
header('Content-Type: text/html; charset=utf-8');

define('ROOT', dirname(__DIR__));
define('DATA_DIR', ROOT . DIRECTORY_SEPARATOR . 'data');

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/wap.php';
require_once __DIR__ . '/game.php';

db_init();
