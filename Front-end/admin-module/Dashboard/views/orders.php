<main class="main-content">
    <div class="main-content-inner">

        <!-- Page Header -->
        <div class="page-header" style="margin-bottom: 1.5rem;">
            <div>
                <h1 class="page-title">Quản Lý Đơn Hàng</h1>
                <p class="text-muted text-sm" style="margin-top: 0.25rem;">Danh sách toàn bộ lịch sử đơn hàng của hệ
                    thống.</p>
            </div>
        </div>

        <!-- Filter & Search Bar -->
        <div class="card" style="margin-bottom: 1.5rem;">
            <div style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
                <!-- Search -->
                <div style="flex: 1; min-width: 220px; position: relative;">
                    <i class="fa-solid fa-magnifying-glass"
                        style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 0.85rem;"></i>
                    <input type="text" id="searchOrderInput" class="form-control"
                        placeholder="Tìm theo SĐT, tên khách hoặc mã đơn..."
                        style="padding-left: 38px; border-radius: 10px;">
                </div>
                <!-- Status Filter -->
                <div style="min-width: 200px;">
                    <select id="ordersStatusFilter" class="form-control form-select"
                        style="border-radius: 10px; cursor: pointer;">
                        <option value="all">📋 Tất cả trạng thái</option>
                        <option value="pending">⏳ Chờ Duyệt</option>
                        <option value="shipping">📦 Đang Chuẩn Bị</option>
                        <option value="delivering">🚚 Đang Giao</option>
                        <option value="returning">↩️ Y/C Trả Hàng</option>
                        <option value="completed">✅ Thành Công</option>
                        <option value="cancelled">❌ Đã Hủy</option>
                        <option value="returned">💸 Đã Hoàn Tiền</option>
                        <option value="failed">⚠️ Thất Bại</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Orders Card List -->
        <div id="ordersTableBody">
            <!-- Order skeleton cards — replaced by JS after data loads -->
            <?php for ($i = 0; $i < 5; $i++): ?>
                <div class="order-card" style="pointer-events:none; user-select:none;">
                    <div class="order-card-header">
                        <div class="order-card-header-left" style="gap:0.75rem; display:flex;">
                            <div class="skeleton" style="width:52px; height:22px; border-radius:20px;"></div>
                            <div class="skeleton" style="width:110px; height:18px; border-radius:4px;"></div>
                        </div>
                        <div class="skeleton" style="width:80px; height:22px; border-radius:20px;"></div>
                    </div>
                    <div class="order-card-body">
                        <div class="order-info-group">
                            <div class="skeleton sk-line sm w-40" style="margin-bottom:0.6rem;"></div>
                            <div class="skeleton sk-line w-80"></div>
                            <div class="skeleton sk-line sm w-60" style="margin:0;"></div>
                        </div>
                        <div class="order-info-group">
                            <div class="skeleton sk-line sm w-40" style="margin-bottom:0.6rem;"></div>
                            <div class="skeleton sk-line w-60"></div>
                        </div>
                        <div class="order-info-group">
                            <div class="skeleton sk-line sm w-40" style="margin-bottom:0.6rem;"></div>
                            <div class="skeleton sk-line w-60"></div>
                            <div class="skeleton" style="width:90px; height:20px; border-radius:20px; margin-top:0.35rem;">
                            </div>
                        </div>
                        <div class="order-info-group">
                            <div class="skeleton sk-line sm w-40" style="margin-bottom:0.6rem;"></div>
                            <div class="skeleton sk-line w-80"></div>
                        </div>
                        <div class="order-actions-group">
                            <div class="skeleton" style="height:34px; border-radius:8px; margin-bottom:0.4rem;"></div>
                            <div class="skeleton" style="height:34px; border-radius:8px;"></div>
                        </div>
                    </div>
                </div>
            <?php endfor; ?>
        </div>

        <!-- Pagination -->
        <div class="pagination-container" id="paginationControls" style="margin-top: 1.5rem;"></div>

    </div>
</main>

<!-- Floating Alert for New Orders - Optional, omitted here to save space or can be added -->

<!-- Order Detail Modal overlay -->
<div class="modal-overlay" id="orderModal">
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-title"><i class="fa-solid fa-receipt text-primary"></i> Chi tiết đơn hàng <span
                    id="modalOrderIdLabel" class="text-primary"></span></h3>
            <button type="button" class="btn-close" onclick="closeModal()">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <div class="modal-body">
            <!-- Shipping Info -->
            <div class="info-block" style="background-color: #f8f9fa; padding: 1rem; border-radius: 8px;">
                <h4 class="info-title">Thông tin Giao hàng & Thanh toán</h4>
                <p class="info-text"><strong>Khách hàng:</strong> <span id="modalCustomerName">...</span> (<span
                        id="modalCustomerPhone">...</span>)</p>
                <p class="info-text"><strong>Địa chỉ:</strong> <span id="modalShippingAddress">...</span></p>
                <p class="info-text"><strong>Ghi chú:</strong> <span id="modalReminder">...</span></p>
                <hr style="margin: 0.5rem 0; border: none; border-top: 1px solid #ddd;">
                <p class="info-text" style="display:flex; justify-content: space-between;">
                    <span><strong>Hình thức TT:</strong> <span id="modalPaymentMethod">...</span></span>
                    <span><strong>Trạng thái TT:</strong> <span id="modalPaymentStatus">...</span></span>
                </p>
            </div>

            <!-- Order Details Table (order_detail) -->
            <h4 class="info-title mt-4" style="margin-top: 1.5rem;">Sản Phẩm Đặt Mua</h4>
            <div class="table-responsive">
                <table class="dashboard-table modal-table">
                    <thead>
                        <tr>
                            <th>Sản phẩm</th>
                            <th>Phiên bản</th>
                            <th>Đơn giá</th>
                            <th>SL</th>
                            <th>Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody id="orderDetailsTableBody">
                        <!-- Detail Rows will be injected by JS -->
                    </tbody>
                </table>
            </div>

            <!-- Update Status Area -->
            <div class="info-block mt-4"
                style="margin-top: 1.5rem; background-color: #fff; border: 1px dashed var(--primary); padding: 1rem; border-radius: 8px; display: none;"
                id="statusUpdateBlock">
                <h4 class="info-title">Tiến trình Giao hàng: <span id="currentStatusLabel"></span></h4>
                <div style="display: flex; gap: 10px; flex-wrap: wrap; margin-top: 10px;" id="statusActionButtons">
                    <!-- Buttons injected by JS -->
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="closeModal()">Đóng Cửa Sổ</button>
        </div>
    </div>
</div> <!-- Close app-container -->

<!-- Scripts -->
<script src="js/admin/admin_order_modal.js"></script>
<script src="js/admin/admin_orders.js"></script>
</body>

</html>