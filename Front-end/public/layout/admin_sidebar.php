<?php
// lấy route hiện tại để active menu
$current_route = isset($_GET['url']) ? rtrim($_GET['url'], '/') : '';
$is_active = function ($path) use ($current_route) {
    if ($current_route === $path)
        return 'active';
    if ($current_route === 'admin' && $path === 'admin/dashboard')
        return 'active';
    return '';
};
// group active: trả về 'open' nếu bất kỳ child nào đang active
$group_active = function (array $paths) use ($current_route) {
    foreach ($paths as $p) {
        if ($current_route === $p)
            return true;
    }
    return false;
};
$productGroup = $group_active(['admin/list_product', 'admin/list_variant', 'admin/add_variant', 'admin/categories', 'admin/brands']);
?>
<!-- Sidebar -->
<aside class="sidebar">
    <div class="sidebar-logo">
        <i class="fa-solid fa-cube"></i> AdminPanel
    </div>
    <nav>

        <!-- Dashboard -->
        <a href="/admin/dashboard" class="nav-item <?= $is_active('admin/dashboard') ?>">
            <i class="fa-solid fa-house"></i> Dashboard
        </a>

        <!-- Đơn hàng -->
        <a href="/admin/orders" class="nav-item <?= $is_active('admin/orders') ?>">
            <i class="fa-solid fa-cart-shopping"></i> Đơn hàng
        </a>

        <!-- Quản lý sản phẩm (collapsible group) -->
        <div class="nav-group <?= $productGroup ? 'open' : '' ?>">
            <div class="nav-group-header" onclick="toggleNavGroup(this)">
                <span><i class="fa-solid fa-box-open"></i> Sản phẩm</span>
                <i class="fa-solid fa-chevron-right nav-group-arrow"></i>
            </div>
            <div class="nav-group-children">
                <a href="/admin/list_product" class="nav-item nav-child <?= $is_active('admin/list_product') ?>">
                    <i class="fa-solid fa-list"></i> Sản phẩm gốc
                </a>
                <a href="/admin/list_variant" class="nav-item nav-child <?= $is_active('admin/list_variant') ?>">
                    <i class="fa-solid fa-table-list"></i> Biến thể
                </a>
                <a href="/admin/add_variant" class="nav-item nav-child <?= $is_active('admin/add_variant') ?>">
                    <i class="fa-solid fa-plus-circle"></i> Thêm biến thể
                </a>
                <a href="/admin/categories" class="nav-item nav-child <?= $is_active('admin/categories') ?>">
                    <i class="fa-solid fa-folder-tree"></i> Danh mục
                </a>
                <a href="/admin/brands" class="nav-item nav-child <?= $is_active('admin/brands') ?>">
                    <i class="fa-solid fa-copyright"></i> Thương hiệu
                </a>
            </div>
        </div>

        <!-- Quản lý Banner -->
        <a href="/admin/banners" class="nav-item <?= $is_active('admin/banners') ?>">
            <i class="fa-solid fa-images"></i> Quản lý Banner
        </a>

        <!-- Quản lý Voucher -->
        <a href="/admin/vouchers" class="nav-item <?= $is_active('admin/vouchers') ?>">
            <i class="fa-solid fa-ticket"></i> Quản lý Voucher
        </a>

        <!-- Quản lý Khuyến mãi -->
        <a href="/admin/promotions" class="nav-item <?= $is_active('admin/promotions') ?>">
            <i class="fa-solid fa-bullhorn"></i> Quản lý Khuyến mãi
        </a>

        <!-- Cài đặt AI -->
        <a href="/admin/ai_settings" class="nav-item <?= $is_active('admin/ai_settings') ?>">
            <i class="fa-solid fa-robot"></i> Cài đặt AI
        </a>

        <!-- Quản lý Tài khoản -->
        <div class="nav-group <?= $group_active(['admin/users', 'admin/staff']) ? 'open' : '' ?>">
            <div class="nav-group-header" onclick="toggleNavGroup(this)">
                <span><i class="fa-solid fa-users-gear"></i> Tài khoản</span>
                <i class="fa-solid fa-chevron-right nav-group-arrow"></i>
            </div>
            <div class="nav-group-children">
                <a href="/admin/users" class="nav-item nav-child <?= $is_active('admin/users') ?>">
                    <i class="fa-solid fa-user"></i> Khách hàng
                </a>
                <?php if (isset($_SESSION['admin']) && $_SESSION['admin']['role'] === 'admin'): ?>
                <a href="/admin/admins" class="nav-item nav-child <?= $is_active('admin/admins') ?>">
                    <i class="fa-solid fa-user-gear"></i> Quản trị viên
                </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Divider -->
        <div class="nav-divider"></div>

        <!-- Xem Website -->
        <a href="/home" class="nav-item" target="_blank">
            <i class="fa-solid fa-store"></i> Xem Website
        </a>

        <!-- Đăng xuất -->
        <a href="javascript:void(0)" id="admin-logout-btn" class="nav-item nav-danger">
            <i class="fa-solid fa-right-from-bracket"></i> Đăng xuất
        </a>

    </nav>
</aside>

<script>
    function toggleNavGroup(header) {
        const group = header.closest('.nav-group');
        group.classList.toggle('open');
    }

    document.getElementById('admin-logout-btn').addEventListener('click', async () => {
        try {
            const resp = await fetch('https:// polygearid.ivi.vn/back-end/api/admin/auth/logout', {
                method: 'POST',
                credentials: 'include'
            });
            if (resp.ok) window.location.href = '/admin/login';
        } catch (e) {
            console.error('Logout error', e);
        }
    });
</script>