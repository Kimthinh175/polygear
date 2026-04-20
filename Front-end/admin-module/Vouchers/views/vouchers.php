<?php
if (!defined('SECURE')) {
    http_response_code(403);
    header("Location: /home");
    exit();
}
?>

<style>
/* ── voucher page styles ── */
.voucher-stats-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
    gap: 1rem;
    margin-bottom: 1.75rem;
}
.voucher-stat-card {
    background: #fff;
    border-radius: 14px;
    padding: 1.1rem 1.25rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    border: 1px solid #f1f5f9;
    box-shadow: 0 2px 8px rgba(59,130,246,0.06);
    transition: transform 0.2s, box-shadow 0.2s;
}
.voucher-stat-card:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(59,130,246,0.11); }
.voucher-stat-icon {
    width: 42px; height: 42px;
    border-radius: 11px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.1rem; flex-shrink: 0;
}
.voucher-stat-label { font-size: 0.72rem; color: #94a3b8; font-weight: 600; text-transform: uppercase; letter-spacing: 0.04em; }
.voucher-stat-value { font-size: 1.45rem; font-weight: 800; color: #0f172a; line-height: 1.2; }

/* card grid */
.vouchers-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(295px, 1fr));
    gap: 1rem;
    padding: 1.25rem;
}

/* single voucher card */
.voucher-card {
    background: #fff;
    border-radius: 16px;
    border: 1.5px solid #e8edf5;
    overflow: hidden;
    transition: transform 0.2s, box-shadow 0.2s;
    position: relative;
    display: flex; flex-direction: column;
}
.voucher-card:hover { transform: translateY(-3px); box-shadow: 0 10px 28px rgba(21,68,183,0.1); }

/* ticket tear stripe at top */
.voucher-card-stripe {
    height: 6px;
    background: linear-gradient(90deg, #1544b7, #2a83e9, #60a5fa);
}
.voucher-card-stripe.inactive { background: linear-gradient(90deg, #94a3b8, #cbd5e1); }

.voucher-card-body { padding: 1.1rem 1.2rem; flex: 1; }

/* big code display */
.voucher-code-display {
    display: flex; align-items: center; gap: 0.6rem;
    margin-bottom: 0.85rem;
}
.voucher-code-badge {
    font-size: 1.05rem; font-weight: 800;
    font-family: 'Courier New', monospace;
    color: #1544b7;
    background: #eef2ff;
    border: 1.5px dashed #93c5fd;
    border-radius: 8px;
    padding: 0.3rem 0.7rem;
    letter-spacing: 0.08em;
    flex: 1;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.voucher-code-badge.inactive { color: #64748b; background: #f8fafc; border-color: #e2e8f0; }

.voucher-status-pill {
    font-size: 0.68rem; font-weight: 700;
    padding: 0.25rem 0.65rem; border-radius: 999px;
    white-space: nowrap; flex-shrink: 0;
}
.voucher-status-pill.active { background: #dcfce7; color: #16a34a; }
.voucher-status-pill.inactive { background: #f1f5f9; color: #64748b; }

/* info rows */
.voucher-info-grid {
    display: grid; grid-template-columns: 1fr 1fr; gap: 0.55rem 1rem;
    font-size: 0.8rem;
}
.voucher-info-item label {
    display: block; font-size: 0.68rem; color: #94a3b8; font-weight: 600;
    text-transform: uppercase; letter-spacing: 0.04em; margin-bottom: 1px;
}
.voucher-info-item span { color: #1e293b; font-weight: 600; }

/* divider with scissors */
.voucher-divider {
    margin: 0.9rem 0 0;
    display: flex; align-items: center; gap: 0.5rem;
    color: #cbd5e1; font-size: 0.7rem;
}
.voucher-divider::before, .voucher-divider::after {
    content: ''; flex: 1; height: 1px;
    border-top: 1.5px dashed #e2e8f0;
}

/* actions footer */
.voucher-card-footer {
    padding: 0.75rem 1.2rem;
    display: flex; justify-content: flex-end; gap: 0.5rem;
    background: #f8fafc;
    border-top: 1px solid #f1f5f9;
}
.vbtn {
    height: 32px; padding: 0 0.85rem; border-radius: 8px;
    font-size: 0.78rem; font-weight: 700; cursor: pointer;
    border: none; display: inline-flex; align-items: center; gap: 0.4rem;
    transition: opacity 0.18s, transform 0.15s;
}
.vbtn:hover { opacity: 0.85; transform: scale(0.97); }
.vbtn-edit  { background: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe; }
.vbtn-del   { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
.vbtn-toggle { background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; }
.vbtn-toggle.deactivate { background: #fff7ed; color: #ea580c; border-color: #fed7aa; }

/* empty state */
.vouchers-empty {
    text-align: center; padding: 3.5rem 2rem; color: #94a3b8;
    grid-column: 1 / -1;
}
.vouchers-empty i { font-size: 3rem; margin-bottom: 1rem; display: block; opacity: 0.35; }

/* toolbar */
.voucher-toolbar {
    padding: 1rem 1.25rem;
    border-bottom: 1px solid #f1f5f9;
    background: #f8fafc;
    display: flex; flex-wrap: wrap;
    justify-content: space-between; align-items: center; gap: 0.75rem;
}
.search-wrap {
    position: relative; flex: 1; max-width: 300px;
}
.search-wrap i {
    position: absolute; left: 0.9rem; top: 50%; transform: translateY(-50%); color: #94a3b8;
}
.search-wrap input {
    width: 100%; padding: 0.55rem 1rem 0.55rem 2.4rem;
    border-radius: 10px; border: 1px solid #e2e8f0;
    font-size: 0.85rem; outline: none; background: #fff;
    transition: border-color 0.2s, box-shadow 0.2s;
}
.search-wrap input:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,0.1); }

.filter-tabs { display: flex; gap: 0.4rem; }
.filter-tab {
    padding: 0.4rem 0.9rem; border-radius: 8px; font-size: 0.78rem; font-weight: 600;
    cursor: pointer; border: 1px solid #e2e8f0; background: #fff; color: #64748b;
    transition: all 0.2s;
}
.filter-tab:hover { border-color: #3b82f6; color: #3b82f6; }
.filter-tab.active { background: #3b82f6; color: #fff; border-color: #3b82f6; }
</style>

<main class="main-content">
    <div class="main-content-inner">

        <!-- Page Header -->
        <div style="display:flex; align-items:flex-start; justify-content:space-between; margin-bottom:1.5rem; gap:1rem; flex-wrap:wrap;">
            <div>
                <h1 class="page-title" style="margin:0; display:flex; align-items:center; gap:0.6rem;">
                    <span style="width:38px;height:38px;border-radius:10px;background:linear-gradient(135deg,#1544b7,#2a83e9);display:inline-flex;align-items:center;justify-content:center;color:#fff;font-size:1rem;">
                        <i class="fa-solid fa-ticket"></i>
                    </span>
                    Quản lý Voucher
                </h1>
                <p class="text-muted text-sm" style="margin-top:0.35rem; padding-left:46px;">Tạo và quản lý các mã giảm giá cho hệ thống</p>
            </div>
            <button type="button" class="btn btn-primary" onclick="vouchersManager.openModal()" style="gap:0.5rem; align-self:center;">
                <i class="fa-solid fa-plus"></i> Thêm Voucher Mới
            </button>
        </div>

        <!-- Stats Row -->
        <div class="voucher-stats-row">
            <div class="voucher-stat-card">
                <div class="voucher-stat-icon" style="background:#eff6ff; color:#3b82f6;">
                    <i class="fa-solid fa-ticket"></i>
                </div>
                <div>
                    <div class="voucher-stat-label">Tổng voucher</div>
                    <div class="voucher-stat-value" id="stat-total">—</div>
                </div>
            </div>
            <div class="voucher-stat-card">
                <div class="voucher-stat-icon" style="background:#f0fdf4; color:#16a34a;">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
                <div>
                    <div class="voucher-stat-label">Đang hoạt động</div>
                    <div class="voucher-stat-value" id="stat-active">—</div>
                </div>
            </div>
            <div class="voucher-stat-card">
                <div class="voucher-stat-icon" style="background:#fff7ed; color:#ea580c;">
                    <i class="fa-solid fa-clock"></i>
                </div>
                <div>
                    <div class="voucher-stat-label">Tạm dừng</div>
                    <div class="voucher-stat-value" id="stat-inactive">—</div>
                </div>
            </div>
            <div class="voucher-stat-card">
                <div class="voucher-stat-icon" style="background:#fdf4ff; color:#a855f7;">
                    <i class="fa-solid fa-percent"></i>
                </div>
                <div>
                    <div class="voucher-stat-label">Giảm TB</div>
                    <div class="voucher-stat-value" id="stat-avg">—</div>
                </div>
            </div>
        </div>

        <!-- Main Card -->
        <div class="card p-0" style="overflow:hidden;">

            <!-- Toolbar -->
            <div class="voucher-toolbar">
                <div class="search-wrap">
                    <i class="fa-solid fa-search"></i>
                    <input type="text" id="voucherSearch" placeholder="Tìm kiếm mã voucher...">
                </div>
                <div class="filter-tabs">
                    <button class="filter-tab active" data-filter="all" onclick="vouchersManager.setFilter('all', this)">Tất cả</button>
                    <button class="filter-tab" data-filter="active" onclick="vouchersManager.setFilter('active', this)">
                        <i class="fa-solid fa-circle" style="font-size:0.5rem; color:#16a34a;"></i> Hoạt động
                    </button>
                    <button class="filter-tab" data-filter="inactive" onclick="vouchersManager.setFilter('inactive', this)">Tạm dừng</button>
                </div>
            </div>

            <!-- Cards Grid -->
            <div class="vouchers-grid" id="vouchersList">
                <div class="vouchers-empty">
                    <i class="fa-solid fa-circle-notch fa-spin" style="font-size:2rem; opacity:1; color:#3b82f6;"></i>
                    <p style="margin-top:0.75rem;">Đang tải dữ liệu...</p>
                </div>
            </div>
        </div>

    </div>
</main>

<!-- Modal Form -->
<div id="voucherModal" class="modal-overlay">
    <div class="modal" style="max-width:540px;" onclick="event.stopPropagation()">
        <!-- Modal Header -->
        <div class="modal-header">
            <h3 class="modal-title" id="modalTitle">
                <i class="fa-solid fa-ticket text-primary"></i> Quản lý Voucher
            </h3>
            <button type="button" class="btn-close" onclick="vouchersManager.closeModal()">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <!-- Modal Body -->
        <div class="modal-body">
            <input type="hidden" id="voucherId">

            <div class="form-group">
                <label class="form-label">Mã Voucher <span style="color:red">*</span></label>
                <input type="text" id="voucherCode" class="form-control"
                    style="text-transform:uppercase; font-family:monospace; letter-spacing:0.1em; font-size:1.05rem; font-weight:700;"
                    placeholder="VD: TECH50, GIAM20">
                <p style="font-size:0.72rem; color:#94a3b8; margin-top:0.3rem;">Nên sử dụng chữ in hoa và không dấu.</p>
            </div>

            <div class="grid-2" style="gap:1rem;">
                <div class="form-group">
                    <label class="form-label">Mức Giảm Giá (%) <span style="color:red">*</span></label>
                    <div style="position:relative;">
                        <input type="number" id="voucherValue" class="form-control" min="1" max="20" placeholder="Max 20%"
                            style="padding-right:2.5rem;">
                        <span style="position:absolute; right:0.9rem; top:50%; transform:translateY(-50%); color:#94a3b8; font-weight:700;">%</span>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Giảm Tối Đa (đ)</label>
                    <input type="number" id="voucherMaxDiscount" class="form-control" placeholder="Mặc định: 100.000đ">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Đơn Tối Thiểu (đ)</label>
                <input type="number" id="voucherCondition" class="form-control" placeholder="Mặc định: 0đ">
            </div>

            <div class="grid-2" style="gap:1rem;">
                <div class="form-group">
                    <label class="form-label">Phạm vi tài khoản mới (ngày)</label>
                    <input type="number" id="voucherMaxAge" class="form-control" placeholder="Bỏ trống = tất cả">
                </div>
                <div class="form-group">
                    <label class="form-label">Số lần dùng (Hệ thống)</label>
                    <input type="number" id="voucherGlobalLimit" class="form-control" placeholder="Bỏ trống = ∞">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Số lần dùng tối đa / 1 User</label>
                <input type="number" id="voucherUserLimit" class="form-control" placeholder="Bỏ trống = ∞">
            </div>

            <div class="grid-2" style="gap:1rem;">
                <div class="form-group">
                    <label class="form-label">Ngày Bắt Đầu</label>
                    <input type="datetime-local" id="voucherStart" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">Ngày Kết Thúc</label>
                    <input type="datetime-local" id="voucherEnd" class="form-control">
                </div>
            </div>

            <!-- Status toggle -->
            <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:0.9rem 1rem; display:flex; align-items:center; justify-content:space-between;">
                <div>
                    <div style="font-weight:700; color:#1e293b; font-size:0.875rem;">Trạng Thái Kích Hoạt</div>
                    <div style="font-size:0.75rem; color:#94a3b8; margin-top:2px;">Voucher sẽ hiển thị và có thể sử dụng</div>
                </div>
                <label style="position:relative; display:inline-flex; align-items:center; cursor:pointer;">
                    <input type="checkbox" id="voucherStatus" style="display:none;" checked>
                    <div id="toggleBg" style="width:48px; height:26px; background:#3b82f6; border-radius:13px; position:relative; transition:background 0.2s;">
                        <div id="toggleDot" style="position:absolute; width:20px; height:20px; background:white; border-radius:50%; top:3px; left:3px; transition:left 0.2s; box-shadow:0 1px 4px rgba(0,0,0,0.2);"></div>
                    </div>
                </label>
            </div>

            <p class="text-sm" id="modalError" style="display:none; color:#ef4444; background:#fef2f2; padding:0.6rem 0.8rem; border-radius:8px; font-weight:600; margin-top:0.75rem;"></p>
        </div>

        <!-- Modal Footer -->
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="vouchersManager.closeModal()">Hủy Bỏ</button>
            <button type="button" class="btn btn-primary" onclick="vouchersManager.saveVoucher()">
                <i class="fa-solid fa-save"></i> Lưu Thông Tin
            </button>
        </div>
    </div>
</div>

<script>
// toggle status ui
const toggleCheckbox = document.getElementById('voucherStatus');
const toggleBg       = document.getElementById('toggleBg');
const toggleDot      = document.getElementById('toggleDot');
function updateToggle() {
    if (toggleCheckbox.checked) {
        toggleBg.style.background  = '#3b82f6';
        toggleDot.style.left       = '25px';
    } else {
        toggleBg.style.background  = '#cbd5e1';
        toggleDot.style.left       = '3px';
    }
}
toggleCheckbox.addEventListener('change', updateToggle);
toggleBg.addEventListener('click', () => {
    toggleCheckbox.checked = !toggleCheckbox.checked;
    toggleCheckbox.dispatchEvent(new Event('change'));
});
updateToggle();
</script>

<script src="js/admin/vouchers.js"></script>
