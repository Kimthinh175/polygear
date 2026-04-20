<?php
define('SECURE_API_ACCESS', true);
define('ROOT_DIR', dirname(__DIR__));
require_once ROOT_DIR . "/vendor/autoload.php";
$dotenv = Dotenv\Dotenv::createImmutable(ROOT_DIR);
$dotenv->load();
require_once ROOT_DIR . "/Back-end/core/database.php";

$banners = database::ThucThiTraVe("SELECT * FROM banners LIMIT 5");
echo json_encode($banners, JSON_PRETTY_PRINT);
