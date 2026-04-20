<?php
if (!defined('SECURE_API_ACCESS')) {
    http_response_code(403);
    header("Location: /home");
    exit();
}
// bật cái này lúc đang code
ini_set('display_errors', 1);

// bật cái này lúc nộp bài / đưa lên host thật
// ini_set('display_errors', 0);
// error_reporting(0);

require_once ROOT_DIR . "/Back-end/core/database.php";
require_once ROOT_DIR . "/Back-end/core/router.php";
require_once ROOT_DIR . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(ROOT_DIR);
$dotenv->load();
