<?php
// seo defaults
$seo_title = $data['seo_title'] ?? 'PolyGear - Hệ thống bán lẻ Laptop, PC & Phụ kiện Gaming chính hãng';
$seo_desc = $data['seo_desc'] ?? 'PolyGear chuyên cung cấp Laptop Gaming, PC Build, linh kiện máy tính và phụ kiện chính hãng giá tốt nhất. Bảo hành uy tín, giao hàng toàn quốc.';
$seo_key = $data['seo_key'] ?? 'laptop gaming, pc build, linh kien may tinh, ban phim co, chuot gaming, polygear';
$seo_img = BASE_URL . ($data['seo_img'] ?? 'img/layout/logo-mark.avif');

if (isset($_SESSION['user'])) {
  $cart_data = ApiCaller::get("api/cart/quantity?phone={$_SESSION['user']['phone']}", true);
}
$quantity = $cart_data['quantity'] ?? 0;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8" />
    <title><?= $seo_title ?></title>
    <meta name="description" content="<?= $seo_desc ?>" />
    <meta name="keywords" content="<?= $seo_key ?>" />
    <meta name="robots" content="index, follow" />
    <link rel="canonical" href="<?= BASE_URL . (isset($_GET['url']) ? $_GET['url'] : '') ?>" />

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website" />
    <meta property="og:title" content="<?= $seo_title ?>" />
    <meta property="og:description" content="<?= $seo_desc ?>" />
    <meta property="og:image" content="<?= $seo_img ?>" />
    <meta property="og:url" content="<?= BASE_URL ?>" />

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image" />
    <meta property="twitter:title" content="<?= $seo_title ?>" />
    <meta property="twitter:description" content="<?= $seo_desc ?>" />
    <meta property="twitter:image" content="<?= $seo_img ?>" />

    <!-- Schema.org (JSON-LD) for Google -->
    <script type="application/ld+json">
    {
      "@context": "https:// schema.org",
      "@type": "Store",
      "name": "PolyGear",
      "image": "<?= $seo_img ?>",
      "@id": "<?= BASE_URL ?>",
      "url": "<?= BASE_URL ?>",
      "telephone": "0862159940",
      "address": {
        "@type": "PostalAddress",
        "streetAddress": "Trịnh Văn Bô",
        "addressLocality": "Nam Từ Liêm",
        "addressRegion": "Hà Nội",
        "postalCode": "100000",
        "addressCountry": "VN"
      },
      "openingHoursSpecification": {
        "@type": "OpeningHoursSpecification",
        "dayOfWeek": [
          "Monday",
          "Tuesday",
          "Wednesday",
          "Thursday",
          "Friday",
          "Saturday",
          "Sunday"
        ],
        "opens": "08:00",
        "closes": "22:00"
      }
    }
    </script>

    <base href="<?= BASE_URL ?>">
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
<script src="https:// cdn.tailwindcss.com?plugins=forms,container-queries"></script>

<link
  href="https:// fonts.googleapis.com/css2?family=manrope:wght@400;700;800&amp;family=inter:wght@400;500;600&amp;display=swap"
  rel="stylesheet" />
<link href="https:// fonts.googleapis.com/css2?family=material+symbols+outlined:wght,fill@100..700,0..1&amp;display=swap"
  rel="stylesheet" />

<link rel="stylesheet" href="css/tailwind.css?v=1.0.1">

<link href="https:// fonts.googleapis.com/css2?family=inter:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
<!-- FontAwesome -->
<link rel="shortcut icon" href="img/layout/logo-mark.avif" type="image/x-icon">
<link rel="stylesheet" href="https:// cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
<link rel="stylesheet" href="css/layout/header.css">
<link rel="stylesheet" href="css/layout/footer.css">
<link rel="stylesheet" href="css/layout/style.css">

<!-- Top Info Bar -->
<div class="cps-top-info">
  <div class="container flex-between" style="display: flex; justify-content: space-between; align-items: center;">
    <div>
      Chào mừng bạn đến với PolyGear!
      <?php if (isset($_SESSION['admin'])): ?>
        <span style="margin: 0 10px;">|</span>
        <a href="/admin/dashboard" style="color: #ffd700; font-weight: 600; text-decoration: none;">
          <i class="fa-solid fa-user-shield"></i> Trang Quản Trị
        </a>
      <?php endif; ?>
    </div>
    <div>
      <a href="mailto:polygear@gmail.com"><i class="fa-regular fa-envelope"></i> polygear@gmail.com</a>
      <a href="tel:0862159940"><i class="fa-solid fa-phone"></i> 0862159940</a>
    </div>
  </div>
</div>

<!-- Cellphones Header -->
<header class="cps-header">
  <div class="cps-header-container px-4 md:px-12">
    <!-- Hamburger Menu (Mobile Only) -->
    <button id="mobile-menu-toggle" class="mobile-menu-btn lg:hidden">
      <span class="material-symbols-outlined">menu</span>
    </button>

    <a href="/" class="cps-logo"><img src="img/layout/am-ban.png" alt="Logo" class="logo-img" /></a>

    <div class="cps-header-btn catalog-btn header-items hidden lg:flex">
      <i class="fa-solid fa-list"></i>
      <span>Danh mục</span>

      <!-- Dropdown Menu -->
      <div class="catalog-dropdown">
        <ul>

          <!-- Simple Links for the rest -->

          <li>
            <a href="/category">
              <div class="cat-left">
                <i class="fa-solid fa-microchip"></i> Linh Kiện Máy Tính
              </div>
            </a>
          </li>
          <li>
            <a href="/news">
              <div class="cat-left">
                <i class="fa-solid fa-newspaper"></i> Tin tức
              </div>
            </a>
          </li>
        </ul>
      </div>
    </div>

    <div class="cps-search header-items">
      <input type="text" placeholder="Bạn cần tìm gì?" />
      <button><i class="fa-solid fa-magnifying-glass"></i></button>
    </div>
    <?php
    if (isset($_SESSION['user']) && $_SESSION['user']) {
      $base_url = 'C:/xampp/htdocs/';
      $avatar = $_SESSION['user']['avatar'] ? $_SESSION['user']['avatar'] : 'default-user.jpg';
      echo <<<HTML
                <div class="notification-dropdown header-items" style="position: relative; margin-right: 15px; cursor: pointer;">
                    <i class="fa-regular fa-bell" style="font-size: 22px;"></i>
                    <span id="notif-badge" class="cps-cart-badge" style="display:none; background: #ed212d; top: -5px; left: 10px !important; font-size:10px;">0</span>
                    <div class="notif-dropdown-menu" style="display:none; position: absolute; right: -50px; top: 120%; width: 320px; background: #fff; box-shadow: 0 4px 15px rgba(0,0,0,0.15); border-radius: 8px; z-index: 1000; padding: 15px; text-align: left;">
                        <h4 style="margin:0 0 10px 0; font-size: 15px; color: #333; font-weight:600;">Thông báo</h4>
                        <ul id="notif-list" style="list-style: none; padding: 0; margin: 0; max-height: 300px; overflow-y: auto;">
                            <li style="font-size: 13px; color: #666; text-align: center; padding: 10px 0;">Đang tải...</li>
                        </ul>
                    </div>
                </div>

                <div class="user-dropdown">
                  <img src="{$avatar}" alt="User Avatar" class="user-avatar" />             
                  <div class="user-dropdown-menu">
                    
                    <a href="/account" class="user-dropdown-item">
                      <span class="material-symbols-outlined user-dropdown-icon">person</span>
                      Tài Khoản Của Tôi
                    </a>
                    <a href="/history" class="user-dropdown-item">
                      <span class="material-symbols-outlined user-dropdown-icon">local_shipping</span>
                      Đơn Mua
                    </a>
                    <a href="/notifications" class="user-dropdown-item">
                      <span class="material-symbols-outlined user-dropdown-icon">notifications</span>
                      Thông báo
                    </a>
                    <a href="/vouchers" class="user-dropdown-item">
                      <span class="material-symbols-outlined user-dropdown-icon">confirmation_number</span>
                      Kho voucher
                    </a>
                    <div class="user-dropdown-divider"></div>
                    <div id="logout" class="user-dropdown-item logout-item">
                      <span class="material-symbols-outlined user-dropdown-icon">logout</span>
                      Đăng xuất
                    </div>
                  </div>
                </div>
            HTML;
    } else {
      echo <<<HTML
                <div id="btnOpenLogin" class="cps-header-login">
                    <div class="login-icon"><i class="fa-solid fa-user"></i></div>
                    <span>Đăng nhập</span>
                </div>
            HTML;
    }
    ?>
    <a href="/cart" class="cps-header-btn cart-btn">
      <span class="cps-cart-badge header-items" id="cart-quantity" style="<?= $quantity > 0 ? '' : 'display:none;' ?>">
        <?= $quantity > 0 ? $quantity : '' ?>
      </span>
      <i class="fa-solid fa-cart-shopping"></i>
      <span class="hidden md:block">Giỏ hàng</span>
    </a>

  </div>

  <!-- Mobile Menu Sidebar -->
  <div id="mobile-sidebar" class="fixed inset-0 z-[200] lg:hidden transition-all duration-300">

    <div id="mobile-sidebar-overlay" class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>
    <div id="mobile-sidebar-content" class="absolute inset-y-0 left-0 w-72 bg-white shadow-2xl transform -translate-x-full transition-transform duration-300 flex flex-col">
      <div class="p-6 border-b flex items-center justify-between">
        <img src="img/layout/am-ban.png" alt="Logo" class="h-8 object-contain" />
        <button id="close-mobile-menu" class="text-slate-500 hover:text-slate-800">
          <span class="material-symbols-outlined">close</span>
        </button>
      </div>
      <div class="flex-1 overflow-y-auto py-4">
        <div class="px-6 mb-6">
          <div class="relative">
            <input type="text" id="mobile-search-input" placeholder="Bạn cần tìm gì?" class="w-full bg-slate-100 border-none rounded-lg py-3 px-4 text-sm focus:ring-2 focus:ring-blue-500" />
            <button id="mobile-search-btn" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400">
              <span class="material-symbols-outlined">search</span>
            </button>
          </div>
        </div>
        <nav class="space-y-1 px-4">
          <a href="/home" class="flex items-center gap-4 px-4 py-3 rounded-lg text-slate-700 hover:bg-slate-50 hover:text-blue-600 transition-all font-bold">
            <span class="material-symbols-outlined">home</span> Trang chủ
          </a>
          <a href="/category" class="flex items-center gap-4 px-4 py-3 rounded-lg text-slate-700 hover:bg-slate-50 hover:text-blue-600 transition-all font-bold">
            <span class="material-symbols-outlined">category</span> Danh mục sản phẩm
          </a>
          <a href="/news" class="flex items-center gap-4 px-4 py-3 rounded-lg text-slate-700 hover:bg-slate-50 hover:text-blue-600 transition-all font-bold">
            <span class="material-symbols-outlined">newspaper</span> Tin tức công nghệ
          </a>
        </nav>
      </div>
      <div class="p-6 border-t bg-slate-50">
        <?php if (isset($_SESSION['user'])): ?>
          <a href="/account" class="flex items-center gap-4 px-4 py-3 bg-white rounded-xl border border-slate-200 shadow-sm mb-3">
            <img src="<?= $avatar ?>" class="w-10 h-10 rounded-full border-2 border-white shadow-sm" />
            <div class="flex-1">
              <p class="text-sm font-bold text-slate-900 line-clamp-1"><?= $_SESSION['user']['fullname'] ?></p>
              <p class="text-[10px] text-slate-500">Xem hồ sơ của tôi</p>
            </div>
          </a>
          <button id="mobile-logout" class="w-full py-3 text-red-600 font-bold bg-red-50 rounded-xl hover:bg-red-100 transition-all flex items-center justify-center gap-2">
            <span class="material-symbols-outlined text-lg">logout</span> Đăng xuất
          </button>
        <?php else: ?>
          <button id="mobile-login-btn" class="w-full py-4 bg-blue-600 text-white font-bold rounded-xl shadow-lg shadow-blue-500/30 hover:bg-blue-700 transition-all">
            Đăng nhập ngay
          </button>
        <?php endif; ?>
      </div>
    </div>
  </div>
</header>

<script src="js/layout/header.js"></script>
