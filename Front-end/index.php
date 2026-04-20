<?php

define('SECURE', true);
define('ROOT_DIR', dirname(__DIR__));
define('BASE_URL', 'https:// polygearid.ivi.vn/');

session_start([
    'read_and_close' => true
]);

header( // chặn xss toàn diện
    "Content-Security-Policy: " .
    "default-src 'self'; " .

    // chỉ cho phép js nội bộ, tinymce, firebase, tailwind cdn và jsdelivr (swal)
    "script-src 'self' 'unsafe-inline' https:// cdn.tiny.cloud https://*.tinymce.com https://*.gstatic.com https://*.googleapis.com https://cdn.tailwindcss.com https://cdn.jsdelivr.net https://unpkg.com; " .

    // chỉ cho phép css nội bộ, tinymce, google fonts, fontawesome và jsdelivr
    "style-src 'self' 'unsafe-inline' https:// cdn.tiny.cloud https://*.tinymce.com https://fonts.googleapis.com https://cdnjs.cloudflare.com https://cdn.jsdelivr.net https://unpkg.com; " .

    // chặn mọi luồng gửi data ra ngoài, chỉ cho phép localhost, api, firebase và jsdelivr
    "connect-src 'self' https:// polygearid.ivi.vn https://api.polygear.com https://*.firebaseio.com https://*.googleapis.com https://*.gstatic.com https://cdn.jsdelivr.net https://*.tiny.cloud https://unpkg.com https://*.basemaps.cartocdn.com https://*.track-asia.com https://*.openstreetmap.org; " .

    // xử lý ảnh: nội bộ, base64 (data:), blob, tinymce, google và unsplash
    "img-src 'self' data: blob: https:// *.tiny.cloud https://*.tinymce.com https://*.googleusercontent.com https://images.unsplash.com https://*.google.com https://*.gstatic.com https://unpkg.com https://*.basemaps.cartocdn.com https://*.track-asia.com https://*.openstreetmap.org; " .

    // cho phép load font của tinymce, google fonts và fontawesome
    "font-src 'self' data: https:// *.tiny.cloud https://*.tinymce.com https://fonts.gstatic.com https://cdnjs.cloudflare.com; " .
    "worker-src 'self' blob:; " .
    "child-src 'self' blob:; " .

    // không cho nhúng iframe bậy bạ
    "frame-src 'self';"
);
require_once ROOT_DIR . "/Front-end/core/controller.php";
require_once ROOT_DIR . "/Front-end/core/ApiCaller.php";

$url_str = isset($_GET['url']) ? rtrim($_GET['url'], '/') : '';
if ($url_str === '') {
    $url_str = 'home';
}
$url = explode('/', $url_str);
$page = $url[0];
$param = $url[1] ?? null;

function loadController($file, $class, $method, $param)
{
    if (file_exists($file)) {
        require_once $file;
        $controller = new $class();
        if (method_exists($controller, $method)) {
            $controller->$method($param);
            return;
        }
    }
    controller::pageNotFound();
}

// admin thì xử lý riêng, luc này $url[1] = page, $url[2] = param

if ($page === 'admin') {
    $adminPage = $url[1] ?? 'dashboard';
    $adminParam = $url[2] ?? null;

    if (!isset($_SESSION['admin']) && $adminPage !== 'login') {
        header("Location: ../../admin/login");
        exit();
    }

    $adminRoutes = [ // khai báo các trang cho admin
        'login' => ['Dashboard', 'dashboardController'],
        'dashboard' => ['Dashboard', 'dashboardController'],
        'orders' => ['Dashboard', 'dashboardController'],
        'products' => ['Product manager', 'productmanagerController'],
        'list_variant' => ['Product manager', 'productmanagerController'],
        'add_variant' => ['Product manager', 'productmanagerController'],
        'add_product' => ['Product manager', 'productmanagerController'],
        'users' => ['Account manager', 'accountmanagerController', 'users'],
        'staff' => ['Account manager', 'accountmanagerController', 'staff'],
        'admins' => ['Account manager', 'accountmanagerController', 'admins'],
        'banners' => ['Banners', 'bannersController'],
        'vouchers' => ['Vouchers', 'vouchersController'],
        'ai_settings' => ['Settings', 'settingsController'],
        '' => ['Dashboard', 'dashboardController'],
        "list_product" => ["Product manager", "productmanagerController"],
        'promotions' => ['Promotions', 'promotionsController'],
        'categories' => ['Product manager', 'productmanagerController'],
        'brands' => ['Product manager', 'productmanagerController'],
    ];

    if (isset($adminRoutes[$adminPage])) {
        [$folder, $class] = $adminRoutes[$adminPage];
        loadController(ROOT_DIR . "/Front-end/admin-module/$folder/$class.php", $class, $adminPage, $adminParam);
    } else {
        controller::pageNotFound();
    }
    exit();
}

$userRoutes = [ // khai báo các trang cho client
    'home' => 'home',
    'products' => 'catalog',
    'cart' => 'checkout',
    'account' => 'account',
    'category' => 'catalog',
    'checkout' => 'checkout',
    'success' => 'checkout',
    'history' => 'account',
    'detail' => 'catalog',
    'notifications' => 'account',
    'vouchers' => 'account',
    // các trang tĩnh
    'warranty' => 'pages',
    'shipping' => 'pages',
    'payment' => 'pages',
    'returns' => 'pages',
    'about' => 'pages',
    'contact' => 'pages'
];

// chỗ điều hướng cho client

if (isset($userRoutes[$page])) {
    $module = $userRoutes[$page];
    $class = $module . "Controller";
    $file = ROOT_DIR . "/Front-end/modules/$module/$class.php";
    loadController($file, $class, $page, $param);
} else {
    controller::pageNotFound();
}

?>
