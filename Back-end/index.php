<?php

define('SECURE_API_ACCESS', true);
define('ROOT_DIR', dirname(__DIR__));
define('JWT_EXPIRE', 15 * 60);
date_default_timezone_set('Asia/Ho_Chi_Minh');

session_start();

header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Origin: https:// polygearid.ivi.vn");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

require_once ROOT_DIR . "/Back-end/init.php";
require_once ROOT_DIR . "/Back-end/modules/promotions.php";
define('JWT_SECRET', $_ENV['JWT_SECRET'] ?? 'default_secret_key_change_me');

promotions::autoSync();

$router = new Router();

// đăng ký đường dẫn  'method'('đường dẫn truy cập','tên model', 'tên function')

// products
$router->get('api/products', 'catalog', 'getAll');
$router->post('api/products', 'catalog', 'createProducts');
$router->put('api/products', 'catalog', 'update');
$router->delete('api/products', 'catalog', 'delete');
$router->put('api/products/toggle-status', 'catalog', 'toggleProductStatus');
$router->get('api/products/category', 'catalog', 'findByCategory');
$router->post('api/products/view', 'catalog', 'plusView');
$router->get('api/products/find', 'catalog', 'find');
$router->get('api/products/test', 'catalog', 'test');
$router->get('api/products/origin', 'catalog', 'getAllOrigin');
$router->get('api/products/all-images', 'catalog', 'getAllProductImages');
$router->get('api/products/hot', 'catalog', 'getHot');
$router->get('api/products/new', 'catalog', 'getNew');
$router->get('api/products/sale', 'catalog', 'getSale');
$router->post('api/products/related-cart', 'catalog', 'getRelatedForCart');

// category
$router->get('api/category', 'catalog', 'getAllCategory');
$router->get('api/category/filters', 'catalog', 'getFilter');
$router->post('api/admin/category', 'catalog', 'createCategory');
$router->put('api/admin/category', 'catalog', 'updateCategory');
$router->delete('api/admin/category', 'catalog', 'deleteCategory');

// variant
$router->post('api/products/variant', 'catalog', 'createVariant');
$router->get('api/products/detail', 'catalog', 'getBySku');
$router->get('api/specs', 'catalog', 'getAllSpecs');
$router->get('api/attributes', 'catalog', 'getAllAttribute');

// reviews
$router->get('api/reviews', 'catalog', 'getReviews');
$router->post('api/reviews/add', 'catalog', 'addReview');

// luồng cho admin variant
$router->get('api/admin/variants', 'catalog', 'getAdminVariants');
$router->delete('api/admin/variants', 'catalog', 'deleteVariant');
$router->put('api/admin/variants/toggle-status', 'catalog', 'toggleVariantStatus');
$router->get('api/admin/variant/detail', 'catalog', 'getVariantDetail');
$router->get('api/admin/variant/first-description', 'catalog', 'getVariantFirstDescription');
$router->post('api/admin/variant/update', 'catalog', 'updateVariant');
$router->post('api/admin/variants/update', 'catalog', 'updateVariant');

// quản lý tài khoản admin
$router->get('api/admin/users', 'account', 'getAdminUsers');
$router->put('api/admin/users/toggle-lock', 'account', 'toggleUserLock');
$router->get('api/admin/users/history', 'account', 'getUserPurchaseHistory');
$router->get('api/admin/staff', 'account', 'getAdminStaff');
$router->post('api/admin/staff', 'account', 'createStaff');
$router->put('api/admin/staff', 'account', 'updateStaff');
$router->put('api/admin/staff/reset-password', 'account', 'resetStaffPassword');
$router->delete('api/admin/staff', 'account', 'deleteStaff');
$router->get('api/admin/super', 'account', 'getSuperAdmin');
$router->post('api/admin/variants/update-stock', 'catalog', 'updateVariantStock');

// quản lý thương hiệu
$router->get('api/admin/brands', 'catalog', 'getAllBrands');
$router->post('api/admin/brands', 'catalog', 'createBrand');
$router->post('api/admin/brands/update', 'catalog', 'updateBrand');
$router->delete('api/admin/brands', 'catalog', 'deleteBrand');

// quản lý sản phẩm gốc
$router->get('api/admin/products', 'catalog', 'getAdminProducts');
$router->post('api/admin/products', 'catalog', 'createAdminProduct');
$router->put('api/admin/products', 'catalog', 'updateAdminProduct');

// trang chủ admin
$router->get('api/admin/dashboard/stats', 'dashboard', 'getStats');
$router->get('api/admin/dashboard/chart', 'dashboard', 'getChartData');
$router->get('api/admin/dashboard/inventory-insights', 'dashboard', 'getInventoryInsights');
$router->get('api/admin/dashboard/orders', 'dashboard', 'getOrders');
$router->get('api/admin/dashboard/orders/detail', 'dashboard', 'getOrderDetail');
$router->post('api/admin/dashboard/orders/update-status', 'dashboard', 'updateOrderStatus');
$router->get('api/admin/orders/list', 'dashboard', 'getAllOrders');

// login
$router->post('api/auth/otp/send', 'auth', 'sendOTP');
$router->post('api/auth/otp/check', 'auth', 'checkOTP');
$router->get('api/auth/google', 'auth', 'getUserInfoLoginByGoogle');
$router->get('api/auth/islogin', 'auth', 'islogin');
$router->post('api/admin/auth/login', 'auth', 'adminLogin');

// logout
$router->post('api/auth/logout', 'auth', 'logout');
$router->post('api/admin/auth/logout', 'auth', 'adminLogout');

// ai gợi ý sản phẩm
$router->post("api/ai/sendinfo", "ai", "sendRequest");
$router->post("api/ai/cacheAIdata", "ai", "cacheAIData");
$router->post("api/ai/cacheProducts", "ai", "updateProductCache");
$router->post("api/ai/test", "ai", "getModelsForPostman");

// ai chatbot frontend
$router->post("api/ai/chat/send", "ai", "chatSend");
$router->get("api/ai/chat/history", "ai", "chatHistory");

// cấu hình ai (admin)
$router->get('api/admin/ai/settings', 'ai', 'getSettings');
$router->post('api/admin/ai/settings', 'ai', 'updateSettings');

// upload
$router->post("api/upload/post/img", "upload", "uploadImgs");

// payos
$router->get('api/payos/status', 'checkout', 'checkPayos');

// banners
$router->get('api/banners', 'banners', 'getAll');
$router->get('api/admin/banners', 'banners', 'adminGetAll');
$router->post('api/admin/banners', 'banners', 'create');
$router->post('api/admin/banners/update', 'banners', 'update');
$router->delete('api/admin/banners', 'banners', 'delete');

// checkout
$router->get('api/cart', 'checkout', 'getCart');
$router->post('api/cart/guest-list', 'checkout', 'getGuestCartDetails');
$router->get('api/cart/quantity', 'checkout', 'getCartQuantity');
$router->post('api/cart/sync', 'checkout', 'syncCart');
$router->post('api/cart/addtocart', 'checkout', 'addToCart');
$router->post('api/cart/add', 'checkout', 'addQuantity');
$router->post('api/cart/dec', 'checkout', 'decQuantity');
$router->post('api/cart/remove', 'checkout', 'remove');
$router->post('api/checkout/order', 'checkout', 'createOrder');
$router->post('api/checkout/repay', 'checkout', 'repayOrder');
$router->get('api/checkout/payos_return', 'checkout', 'checkPayos');

// account
$router->get('api/account', 'account', 'getInfo');
$router->post('api/account', 'account', 'updateInfo');
$router->delete('api/address', 'account', 'deleteAddress');
$router->post('api/address', 'account', 'addAddress');
$router->get('api/account/order', 'account', 'getOrderDetail');
$router->get('api/account/orders', 'account', 'getMyOrders');
$router->post('api/account/orders/status', 'account', 'updateMyOrderStatus');
$router->post('api/account/update_fcm', 'account', 'updateFCM');
$router->get('api/account/notifications', 'account', 'getNotifications');

// vouchers
$router->get('api/vouchers', 'voucher', 'getAvailable');
$router->get('api/admin/vouchers', 'voucher', 'getAdminVouchers');
$router->post('api/admin/vouchers', 'voucher', 'createVoucher');
$router->post('api/admin/vouchers/update', 'voucher', 'updateVoucher'); // dùng post thay cho put vì giới hạn form hoặc lười map
$router->delete('api/admin/vouchers', 'voucher', 'deleteVoucher');

// promotions
$router->get('api/promotions', 'promotions', 'getHomePromotions');
$router->get('api/admin/promotions', 'promotions', 'getAdminPromotions');
$router->get('api/admin/promotions/detail', 'promotions', 'getPromotionDetail');
$router->post('api/admin/promotions', 'promotions', 'createPromotion');
$router->post('api/admin/promotions/update', 'promotions', 'updatePromotion');
$router->post('api/admin/promotions/items', 'promotions', 'updatePromotionItems');
$router->post('api/admin/promotions/status', 'promotions', 'updatePromotionStatus');
$router->delete('api/admin/promotions', 'promotions', 'deletePromotion');


// lấy method (get/post) và endpoint từ .htaccess truyền vào
$method = $_SERVER['REQUEST_METHOD'];
$endpoint = isset($_GET['endpoint']) ? $_GET['endpoint'] : '';

$router->dispatch($method, $endpoint);
session_write_close();
?>