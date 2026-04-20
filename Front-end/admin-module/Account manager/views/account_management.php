<?php
if (!defined('SECURE')) {
    exit('No direct script access allowed');
}
$activeTab = $data['activeTab'] ?? 'user';
?>

<main class="main-content">
<div class="acc-page page-loaded">

    <!-- ===== PAGE HEADER ===== -->
    <div class="acc-header">
        <div class="acc-header-left">
            <div class="acc-header-icon <?= ($activeTab === 'admin' || $activeTab === 'super') ? 'purple' : 'blue' ?>">
                <i class="fa-solid <?= ($activeTab === 'admin' || $activeTab === 'super') ? 'fa-shield-halved' : 'fa-users' ?>"></i>
            </div>
            <div>
                <h1 class="acc-title">
                    <?= $activeTab === 'super' ? 'Quản lý Quản trị viên' : ($activeTab === 'admin' ? 'Quản lý Nhân viên' : 'Quản lý Khách hàng') ?>
                </h1>
                <p class="acc-subtitle">
                    <?= $activeTab === 'super' ? 'Quản lý các tài khoản có quyền cao nhất' : ($activeTab === 'admin' ? 'Tạo tài khoản, phân quyền và quản lý đội ngũ nội bộ' : 'Danh sách khách hàng, lịch sử mua hàng và trạng thái tài khoản') ?>
                </p>
            </div>
        </div>
        <div class="acc-header-right">
            <?php if ($activeTab === 'admin' || $activeTab === 'super'): ?>
            <button class="btn btn-primary" onclick="openCreateStaffModal()" id="add-staff-btn">
                <i class="fa-solid fa-user-plus"></i> Thêm nhân viên
            </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- ===== STATS STRIP ===== -->
    <div class="acc-stats" id="acc-stats">
        <?php if ($activeTab === 'user'): ?>
        <div class="acc-stat-card">
            <div class="acc-stat-icon blue"><i class="fa-solid fa-users"></i></div>
            <div><div class="acc-stat-value" id="stat-total">—</div><div class="acc-stat-label">Tổng khách hàng</div></div>
        </div>
        <div class="acc-stat-card">
            <div class="acc-stat-icon green"><i class="fa-solid fa-circle-check"></i></div>
            <div><div class="acc-stat-value" id="stat-active">—</div><div class="acc-stat-label">Đang hoạt động</div></div>
        </div>
        <div class="acc-stat-card">
            <div class="acc-stat-icon red"><i class="fa-solid fa-lock"></i></div>
            <div><div class="acc-stat-value" id="stat-locked">—</div><div class="acc-stat-label">Đã bị khóa</div></div>
        </div>
        <?php elseif ($activeTab === 'admin'): ?>
        <div class="acc-stat-card">
            <div class="acc-stat-icon purple"><i class="fa-solid fa-user-shield"></i></div>
            <div><div class="acc-stat-value" id="stat-total">—</div><div class="acc-stat-label">Tổng nhân viên</div></div>
        </div>
        <div class="acc-stat-card">
            <div class="acc-stat-icon orange"><i class="fa-solid fa-briefcase"></i></div>
            <div><div class="acc-stat-value" id="stat-staff-count">—</div><div class="acc-stat-label">Nhân viên</div></div>
        </div>
        <div class="acc-stat-card">
            <div class="acc-stat-icon blue"><i class="fa-solid fa-user-gear"></i></div>
            <div><div class="acc-stat-value" id="stat-other-count">—</div><div class="acc-stat-label">Vai trò khác</div></div>
        </div>
        <?php else: // super tab ?>
        <div class="acc-stat-card">
            <div class="acc-stat-icon purple"><i class="fa-solid fa-crown"></i></div>
            <div><div class="acc-stat-value" id="stat-total">—</div><div class="acc-stat-label">Tổng quản trị viên</div></div>
        </div>
        <div class="acc-stat-card">
            <div class="acc-stat-icon green"><i class="fa-solid fa-circle-check"></i></div>
            <div><div class="acc-stat-value" id="stat-active-admin">—</div><div class="acc-stat-label">Đang hoạt động</div></div>
        </div>
        <div class="acc-stat-card">
            <div class="acc-stat-icon red"><i class="fa-solid fa-shield-halved"></i></div>
            <div><div class="acc-stat-value" id="stat-total-admins">—</div><div class="acc-stat-label">Hệ thống</div></div>
        </div>
        <?php endif; ?>
    </div>

    <!-- ===== SEARCH + FILTER BAR ===== -->
    <div class="acc-toolbar">
        <div class="acc-search-wrap">
            <i class="fa-solid fa-search acc-search-icon"></i>
            <input type="text" id="acc-search" placeholder="<?= $activeTab === 'admin' ? 'Tìm username...' : 'Tìm tên, số điện thoại, email...' ?>" class="acc-search-input" oninput="filterTable()">
        </div>
        <?php if ($activeTab === 'user'): ?>
        <select id="status-filter" class="acc-filter-select" onchange="filterTable()">
            <option value="">Tất cả trạng thái</option>
            <option value="active">Hoạt động</option>
            <option value="locked">Đã khóa</option>
        </select>
        <?php else: ?>
        <select id="role-filter" class="acc-filter-select" onchange="filterTable()">
            <option value="">Tất cả vai trò</option>
            <option value="admin">Quản trị viên</option>
            <option value="manager">Quản lý</option>
            <option value="sales">Sales</option>
            <option value="warehouse">Kho</option>
        </select>
        <?php endif; ?>
    </div>

    <!-- ===== TABLE CARD ===== -->
    <div class="acc-card">
        <div class="acc-table-wrap">

        <?php if ($activeTab === 'user'): ?>
        <!-- USER TABLE -->
        <table class="acc-table" id="user-table">
            <thead>
                <tr>
                    <th>Khách hàng</th>
                    <th>Liên hệ</th>
                    <th>Ngày tham gia</th>
                    <th>Trạng thái</th>
                    <th style="text-align:right">Thao tác</th>
                </tr>
            </thead>
            <tbody id="user-list">
                <tr><td colspan="5" class="acc-loading-row"><i class="fa-solid fa-circle-notch fa-spin"></i> Đang tải dữ liệu...</td></tr>
            </tbody>
        </table>

        <?php else: ?>
        <!-- STAFF/ADMIN TABLE -->
        <table class="acc-table" id="staff-table">
            <thead>
                <tr>
                    <th>Người dùng</th>
                    <th>Vai trò</th>
                    <th>Quyền hạn</th>
                    <th style="text-align:right">Thao tác</th>
                </tr>
            </thead>
            <tbody id="admin-list">
                <tr><td colspan="4" class="acc-loading-row"><i class="fa-solid fa-circle-notch fa-spin"></i> Đang tải dữ liệu...</td></tr>
            </tbody>
        </table>
        <?php endif; ?>

        </div><!-- end table-wrap -->
    </div><!-- end card -->

</div><!-- end acc-page -->
</main>

<!-- ===================================================
     MODAL: Lịch sử mua hàng
===================================================== -->
<div id="historyModal" class="acc-modal-overlay" onclick="closeAccModal('historyModal', event)">
    <div class="acc-modal modal-xl">
        <div class="acc-modal-header">
            <div class="acc-modal-title-wrap">
                <div class="acc-modal-icon blue"><i class="fa-solid fa-clock-rotate-left"></i></div>
                <div>
                    <h3 class="acc-modal-title">Lịch sử mua hàng</h3>
                    <p id="history-user-name" class="acc-modal-sub"></p>
                </div>
            </div>
            <button class="acc-modal-close" onclick="closeAccModal('historyModal')">&times;</button>
        </div>
        <div class="acc-modal-body">
            <div id="history-loading" class="acc-empty-state" style="display:none;">
                <i class="fa-solid fa-circle-notch fa-spin fa-2x" style="color:var(--primary)"></i>
                <p>Đang tải lịch sử...</p>
            </div>
            <div id="history-empty" class="acc-empty-state" style="display:none;">
                <i class="fa-solid fa-bag-shopping fa-2x" style="color:#cbd5e1"></i>
                <p>Khách hàng này chưa có đơn hàng nào.</p>
            </div>
            <div class="acc-table-wrap">
                <table class="acc-table" id="history-table" style="display:none;">
                    <thead>
                        <tr>
                            <th>Mã đơn hàng</th>
                            <th>Thời gian</th>
                            <th>Tổng tiền</th>
                            <th>Trạng thái đơn</th>
                            <th>Thanh toán</th>
                        </tr>
                    </thead>
                    <tbody id="history-list"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ===================================================
     MODAL: Tạo / Chỉnh sửa nhân viên
===================================================== -->
<div id="staffModal" class="acc-modal-overlay" onclick="closeAccModal('staffModal', event)">
    <div class="acc-modal modal-md">
        <div class="acc-modal-header">
            <div class="acc-modal-title-wrap">
                <div class="acc-modal-icon purple"><i class="fa-solid fa-user-gear"></i></div>
                <h3 class="acc-modal-title" id="staffModalTitle">Thêm nhân viên mới</h3>
            </div>
            <button class="acc-modal-close" onclick="closeAccModal('staffModal')">&times;</button>
        </div>
        <div class="acc-modal-body">
            <form id="staffForm">
                <input type="hidden" id="staff-id">

                <div class="acc-form-row">
                    <div class="acc-form-group">
                        <label class="acc-label">Username <span class="required">*</span></label>
                        <input type="text" id="staff-username" class="acc-input" placeholder="vd: nguyenvana" required>
                    </div>
                    <div class="acc-form-group" id="password-group">
                        <label class="acc-label">Mật khẩu <span class="required">*</span></label>
                        <div class="acc-input-wrap">
                            <input type="password" id="staff-password" class="acc-input" placeholder="Tối thiểu 6 ký tự">
                            <button type="button" class="acc-input-eye" onclick="togglePwd()"><i class="fa-solid fa-eye" id="eye-icon"></i></button>
                        </div>
                        <small class="acc-hint" id="password-help">Để trống nếu không thay đổi mật khẩu</small>
                    </div>
                </div>

                <input type="hidden" id="staff-role" value="admin">
                <!-- Permissions have been removed -->
            </form>
        </div>
        <div class="acc-modal-footer">
            <button type="button" class="btn btn-outline" onclick="closeAccModal('staffModal')">Hủy</button>
            <button type="submit" class="btn btn-primary" onclick="document.getElementById('staffForm').requestSubmit()">
                <i class="fa-solid fa-floppy-disk"></i> Lưu thông tin
            </button>
        </div>
    </div>
</div>

<!-- Reset Password Toast-style confirm -->
<div id="resetModal" class="acc-modal-overlay" onclick="closeAccModal('resetModal', event)">
    <div class="acc-modal modal-sm">
        <div class="acc-modal-header">
            <div class="acc-modal-title-wrap">
                <div class="acc-modal-icon orange"><i class="fa-solid fa-key"></i></div>
                <h3 class="acc-modal-title">Reset mật khẩu</h3>
            </div>
            <button class="acc-modal-close" onclick="closeAccModal('resetModal')">&times;</button>
        </div>
        <div class="acc-modal-body">
            <input type="hidden" id="reset-staff-id">
            <div class="acc-form-group">
                <label class="acc-label">Mật khẩu mới <span class="required">*</span></label>
                <div class="acc-input-wrap">
                    <input type="password" id="reset-new-password" class="acc-input" placeholder="Nhập mật khẩu mới...">
                    <button type="button" class="acc-input-eye" onclick="toggleResetPwd()"><i class="fa-solid fa-eye" id="reset-eye-icon"></i></button>
                </div>
            </div>
        </div>
        <div class="acc-modal-footer">
            <button class="btn btn-outline" onclick="closeAccModal('resetModal')">Hủy</button>
            <button class="btn btn-warning" onclick="confirmResetPassword()">
                <i class="fa-solid fa-key"></i> Xác nhận reset
            </button>
        </div>
    </div>
</div>

<link rel="stylesheet" href="css/admin/admin_account.css">
<script src="js/admin/admin_account.js"></script>
<script>
    // pass php activetab to js
    window.ACTIVE_TAB = '<?= $activeTab ?>';
</script>
