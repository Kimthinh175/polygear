<main class="main-content">
    <div class="main-content-inner">

        <!-- ===== PAGE HEADER ===== -->
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
            <div>
                <h1 class="page-title" style="margin: 0;">Dashboard</h1>
                <p class="text-muted text-sm" style="margin-top: 0.25rem;" id="dateRangeLabel">Đang tải dữ liệu...</p>
            </div>
            <!-- Date Range Picker -->
            <div class="card" style="margin: 0; padding: 0.75rem 1.25rem; display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <label class="text-muted" style="font-size: 0.8rem; font-weight: 600; white-space: nowrap;">Từ ngày</label>
                    <input type="date" id="filterStartDate" class="form-control" style="width: 150px; font-size: 0.85rem; padding: 0.4rem 0.7rem;">
                </div>
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <label class="text-muted" style="font-size: 0.8rem; font-weight: 600; white-space: nowrap;">Đến ngày</label>
                    <input type="date" id="filterEndDate" class="form-control" style="width: 150px; font-size: 0.85rem; padding: 0.4rem 0.7rem;">
                </div>
                <!-- Quick Presets -->
                <div style="display: flex; gap: 0.4rem; flex-wrap: wrap;">
                    <button class="date-preset-btn active" data-preset="month" onclick="applyPreset(this)">Tháng này</button>
                    <button class="date-preset-btn" data-preset="last_month" onclick="applyPreset(this)">Tháng trước</button>
                    <button class="date-preset-btn" data-preset="7days" onclick="applyPreset(this)">7 ngày</button>
                    <button class="date-preset-btn" data-preset="30days" onclick="applyPreset(this)">30 ngày</button>
                    <button class="date-preset-btn" data-preset="year" onclick="applyPreset(this)">Năm nay</button>
                    <button class="date-preset-btn" data-preset="all" onclick="applyPreset(this)">Tất cả</button>
                </div>
                <button type="button" class="btn btn-primary btn-sm" onclick="initDashboard()" style="white-space: nowrap;">
                    <i class="fa-solid fa-rotate-right"></i> Áp dụng
                </button>
            </div>
        </div>

        <!-- ===== HERO REVENUE BANNER ===== -->
        <div class="revenue-hero-banner">
            <div class="revenue-hero-left" style="flex: 1;">
                <div class="revenue-hero-label"><i class="fa-solid fa-chart-line"></i> Tổng Doanh Thu</div>
                <div class="revenue-hero-value" id="totalRevenue">
                    <div class="skeleton sk-line lg w-60" style="background:rgba(255,255,255,0.25); height:44px; margin:0;"></div>
                </div>
                <div class="revenue-hero-sub text-muted" id="revenueSubLabel" style="margin-top:0.5rem;">Giao dịch thành công trong kỳ</div>
            </div>
        </div>

        <!-- ===== KPI STAT CARDS ===== -->
        <div class="stats-grid" style="margin-bottom: 2rem;">
            <div class="stat-card">
                <div class="stat-title">Tổng Đơn Hàng</div>
                <div class="stat-value" id="totalOrdersCount">
                    <div class="skeleton sk-line lg w-25" style="height:32px; margin:4px 0;"></div>
                </div>
                <div class="stat-meta text-muted"><div class="skeleton sk-line sm w-60" style="margin:0;"></div></div>
            </div>
            <div class="stat-card" style="cursor: pointer;" onclick="document.getElementById('ordersTableAnchor').scrollIntoView({behavior:'smooth', block:'start'})" title="Bấm để xem đơn cần xử lý">
                <div class="stat-title">Đơn Chờ Xử Lý</div>
                <div class="stat-value" style="color: var(--warning);" id="pendingOrdersCount">
                    <div class="skeleton sk-line lg w-25" style="height:32px; margin:4px 0;"></div>
                </div>
                <div class="stat-meta"><div class="skeleton sk-line sm w-60" style="margin:0;"></div></div>
            </div>
        </div>


            <!-- Chart Area -->
            <div class="card mt-4 mb-4">
                <div class="card-header">
                    <div><i class="fa-solid fa-chart-area text-primary"></i> Biểu đồ Doanh thu (Area Chart)</div>
                </div>
                <div class="card-body" style="padding: 1.5rem; height: 350px; position:relative;">
                    <div id="chartSkeleton" class="skeleton" style="position:absolute; inset:1.5rem; border-radius:8px;"></div>
                    <canvas id="revenueChart" style="position:relative; z-index:1;"></canvas>
                </div>
            </div>

            <!-- Insights & Inventory Split Layout -->
            <div class="grid-2 mt-4 mb-4">

                <!-- AI Insights -->
                <div class="card" style="overflow: hidden;">
                    <div class="card-header" style="background: linear-gradient(135deg, #fffbeb, #fef3c7); border-bottom: 1px solid #fde68a;">
                        <div style="display:flex; align-items:center; gap: 0.6rem;">
                            <div style="width:32px; height:32px; background: #f59e0b; border-radius: 8px; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                                <i class="fa-solid fa-bolt" style="color:white; font-size:0.85rem;"></i>
                            </div>
                            <div>
                                <div style="font-weight:700; font-size:0.9rem; color:#92400e;">AI Insight</div>
                                <div style="font-size:0.72rem; color:#b45309;">Phân tích tự động từ dữ liệu bán hàng</div>
                            </div>
                        </div>
                    </div>
                    <div style="padding: 1.25rem;">
                        <div id="aiInsightsList" style="display: flex; flex-direction: column; gap: 0.75rem; max-height: 320px; overflow-y: auto;">
                            <!-- Injected by JS -->
                            <div class="insight-skeleton"></div>
                            <div class="insight-skeleton" style="width:80%"></div>
                        </div>
                    </div>
                </div>

                <!-- Low Stock Inventory -->
                <div class="card" style="overflow: hidden;">
                    <div class="card-header" style="background: linear-gradient(135deg, #fff1f2, #ffe4e6); border-bottom: 1px solid #fecdd3;">
                        <div style="display:flex; align-items:center; gap:0.6rem; justify-content:space-between;">
                            <div style="display:flex; align-items:center; gap:0.6rem">
                                <div style="width:32px; height:32px; background:#ef4444; border-radius:8px; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                                    <i class="fa-solid fa-triangle-exclamation" style="color:white; font-size:0.85rem;"></i>
                                </div>
                                <div>
                                    <div style="font-weight:700; font-size:0.9rem; color:#991b1b;">Cảnh Báo Tồn Kho</div>
                                    <div style="font-size:0.72rem; color:#b91c1c;">Sản phẩm dưới mức tối thiểu</div>
                                </div>
                            </div>
                            <span id="lowStockCount" style="background:#ef4444; color:white; font-size:0.7rem; font-weight:800; padding:0.2rem 0.55rem; border-radius:20px; display:none;">0</span>
                        </div>
                    </div>
                    <div id="lowStockList" style="padding: 1rem; display:flex; flex-direction:column; gap:0.6rem; max-height: 320px; overflow-y: auto;">
                        <!-- Injected by JS -->
                    </div>
                </div>

            </div>

            <!-- Orders Section -->
            <div id="ordersTableAnchor">
                <div class="card" style="margin-bottom: 1rem;">
                    <div style="display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
                        <div style="flex: 1;">
                            <h3 style="font-size: 1rem; font-weight: 700; margin: 0; display: flex; align-items: center; gap: 0.5rem;">
                                <i class="fa-solid fa-cart-shopping text-primary"></i> Đơn Hàng Cần Xử Lý
                            </h3>
                        </div>
                        <div style="min-width: 200px;">
                            <select id="dashboardStatusFilter" class="form-control form-select" style="border-radius: 10px; cursor: pointer; font-size: 0.875rem;">
                                <option value="pending" selected>⏳ Chờ Duyệt</option>
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

                <!-- Cards injected by JS -->
                <div id="ordersTableBody"></div>
            </div>

        </main>

    <!-- Floating Bell Notification Button -->
    <div id="newOrdersAlert" style="display: none; position: fixed; bottom: 2rem; right: 2rem; z-index: 9999;">

        <!-- The Bell Button -->
        <button id="bellBtn" onclick="toggleBellPopup()" aria-label="Đơn hàng chờ duyệt"
            style="position: relative; width: 54px; height: 54px; border-radius: 50%; background: #4f46e5; border: none; color: white; font-size: 1.25rem; box-shadow: 0 8px 24px rgba(79,70,229,0.4); cursor: pointer; display: flex; align-items: center; justify-content: center; transition: transform 0.2s ease, box-shadow 0.2s ease;">
            <i class="fa-solid fa-bell" style="animation: bellShake 3s ease infinite;"></i>
            <!-- Badge -->
            <span id="newOrdersCount"
                style="position: absolute; top: -4px; right: -4px; min-width: 20px; height: 20px; background: #ef4444; color: white; border-radius: 10px; font-size: 0.7rem; font-weight: 800; display: flex; align-items: center; justify-content: center; padding: 0 4px; border: 2px solid white; line-height: 1;">
                0
            </span>
        </button>

        <!-- Popup Panel -->
        <div id="bellPopup"
            style="display: none; position: absolute; bottom: 66px; right: 0; width: 280px; background: white; border-radius: 14px; box-shadow: 0 20px 50px rgba(0,0,0,0.15); border: 1px solid #e2e8f0; overflow: hidden; transform-origin: bottom right; transform: scale(0.9); opacity: 0; transition: transform 0.2s ease, opacity 0.2s ease;">
            <!-- Popup Header -->
            <div style="background: linear-gradient(135deg, #4f46e5, #3730a3); padding: 1rem 1.25rem; display: flex; align-items: center; gap: 0.75rem;">
                <div style="width: 36px; height: 36px; background: rgba(255,255,255,0.15); border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <i class="fa-solid fa-bell" style="color: white; font-size: 1rem;"></i>
                </div>
                <div>
                    <div style="font-weight: 700; color: white; font-size: 0.9rem;">Đơn hàng chờ duyệt</div>
                    <div style="font-size: 0.75rem; color: rgba(255,255,255,0.7);">Cần xử lý ngay</div>
                </div>
            </div>
            <!-- Popup Body -->
            <div style="padding: 1.25rem;">
                <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                    <div style="font-size: 2.5rem; font-weight: 800; color: #f59e0b; line-height: 1;" id="bellPopupCount">0</div>
                    <div style="font-size: 0.85rem; color: #64748b; line-height: 1.4;">đơn hàng đang<br><strong style="color: #0f172a;">chờ xác nhận</strong></div>
                </div>
                <p style="font-size: 0.8rem; color: #94a3b8; margin-bottom: 1rem; line-height: 1.5;">Khách hàng đang chờ bạn xác nhận đơn. Hãy xử lý sớm để đảm bảo trải nghiệm tốt nhất!</p>
                <button onclick="handleBellAction()" class="btn btn-success" style="width: 100%; justify-content: center; font-size: 0.85rem;">
                    <i class="fa-solid fa-arrow-down"></i> Xem & Xử lý ngay
                </button>
            </div>
        </div>
    </div>

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
                    <p class="info-text"><strong>Khách hàng:</strong> <span id="modalCustomerName">...</span> (<span id="modalCustomerPhone">...</span>)</p>
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
                                <th>Phân loại</th>
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
                <div class="info-block mt-4" style="margin-top: 1.5rem; background-color: #fff; border: 1px dashed var(--primary); padding: 1rem; border-radius: 8px; display: none;" id="statusUpdateBlock">
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
    <script src="https:// cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="js/admin/admin_order_modal.js"></script>
    <script src="js/admin/admin_dashboard.js"></script>
</body>
</html>
