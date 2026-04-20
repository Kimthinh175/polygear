// admin_dashboard.js - thực thi data real-time với backend api

const API_BASE = 'https:// polygearid.ivi.vn/back-end/api/admin';
let chartInstance = null;

let currentOrderPage = 1;
let isOrderLoading = false;
let hasMoreOrders = true;

// hàm định dạng chung
function formatCurrency(amount) {
    const num = parseFloat(amount);
    if (isNaN(num)) return '0 ₫';
    return num.toLocaleString('vi-VN') + ' ₫';
}

function formatDate(isoString) {
    const d = new Date(isoString);
    return `${d.getDate().toString().padStart(2, '0')}/${(d.getMonth() + 1).toString().padStart(2, '0')}/${d.getFullYear()} ${d.getHours().toString().padStart(2, '0')}:${d.getMinutes().toString().padStart(2, '0')}`;
}

function getStatusBadge(status) {
    const map = {
        'pending': '<span class="status-badge pending">Chờ Duyệt</span>',
        'shipping': '<span class="status-badge shipping">Đang Chuẩn Bị</span>',
        'delivering': '<span class="status-badge delivering">Đang Giao</span>',
        'completed': '<span class="status-badge completed">Thành Công</span>',
        'cancelled': '<span class="status-badge cancelled">Đã Hủy</span>',
        'returned': '<span class="status-badge returned">Đã Hoàn Tiền</span>',
        'failed': '<span class="status-badge failed">Thất Bại</span>',
        'returning': '<span class="status-badge returning">Y/C Trả Hàng</span>'
    };
    return map[status] || `<span class="status-badge">${status}</span>`;
}

function getPaymentBadge(paymentStatus) {
    const map = {
        'paid': '<span class="status-badge completed">Đã Thanh Toán</span>',
        'unpaid': '<span class="status-badge pending">Chưa Thanh Toán</span>',
        'refunded': '<span class="status-badge returning">Đã Hoàn Tiền</span>',
        'failed': '<span class="status-badge failed">Thất Bại</span>'
    };
    return map[paymentStatus] || `<span class="status-badge">${paymentStatus || 'N/A'}</span>`;
}

function getDashboardActionHtml(order) {
    let actionHtml = '';
    const st = order.status;
    const isPayosUnpaid = order.payment_method === 'bank' && order.payment_status === 'unpaid';

    if (st === 'pending') {
        if (isPayosUnpaid) {
            actionHtml = `<button type="button" class="btn btn-secondary btn-sm mt-1 btn-block" disabled title="Chờ khách chuyển khoản"><i class="fa-solid fa-clock"></i> Duyệt đơn</button>`;
        } else {
            actionHtml = `<button type="button" class="btn btn-success btn-sm mt-1 btn-block" onclick="dashboardUpdateStatus('${order.code}', 'shipping')"><i class="fa-solid fa-check"></i> Duyệt đơn</button>`;
        }
    } else if (st === 'shipping') {
        actionHtml = `<button type="button" class="btn btn-info btn-sm mt-1 btn-block" onclick="dashboardUpdateStatus('${order.code}', 'delivering')"><i class="fa-solid fa-truck"></i> Giao Shipper</button>`;
    } else if (st === 'delivering') {
        actionHtml = `<button type="button" class="btn btn-secondary btn-sm mt-1 btn-block" disabled><i class="fa-solid fa-clock"></i> Khách Đang Nhận</button>`;
    } else if (st === 'returning') {
        actionHtml = `<button type="button" class="btn btn-purple btn-sm mt-1 btn-block" onclick="dashboardUpdateStatus('${order.code}', 'returned')"><i class="fa-solid fa-rotate-left"></i> Hoàn Tiền</button>`;
    } else {
        actionHtml = `<div class="mt-1 text-center text-muted" style="font-size: 0.75rem; font-weight: 700;"><i class="fa-solid fa-flag-checkered"></i> Hoàn tất</div>`;
    }
    return actionHtml;
}

async function dashboardUpdateStatus(orderCode, newStatus) {
    if (!confirm('Xác nhận cập nhật trạng thái đơn hàng này?')) return;
    try {
        const res = await fetch(`${API_BASE}/dashboard/orders/update-status`, {
            credentials: 'include',
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ order_code: orderCode, status: newStatus })
        });
        const data = await res.json();
        if (data.status === 'success') {
            initDashboard();
        } else {
            alert('Lỗi: ' + data.message);
        }
    } catch (err) {
        console.error(err);
        alert('Lỗi kết nối máy chủ!');
    }
}

// date range helpers

function formatDateToInput(d) {
    return `${d.getFullYear()}-${(d.getMonth() + 1).toString().padStart(2, '0')}-${d.getDate().toString().padStart(2, '0')}`;
}

function formatDateVN(str) {
    const [y, m, d] = str.split('-');
    return `${d}/${m}/${y}`;
}

function applyPreset(btn) {
    // update active button
    document.querySelectorAll('.date-preset-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');

    const now = new Date();
    let start = new Date();
    const preset = btn.dataset.preset;

    if (preset === 'month') {
        start = new Date(now.getFullYear(), now.getMonth(), 1);
    } else if (preset === 'last_month') {
        start = new Date(now.getFullYear(), now.getMonth() - 1, 1);
        const lastDayOfLastMonth = new Date(now.getFullYear(), now.getMonth(), 0);
        document.getElementById('filterStartDate').value = formatDateToInput(start);
        document.getElementById('filterEndDate').value = formatDateToInput(lastDayOfLastMonth);
        initDashboard();
        return;
    } else if (preset === '7days') {
        start.setDate(now.getDate() - 7);
    } else if (preset === '30days') {
        start.setDate(now.getDate() - 30);
    } else if (preset === 'year') {
        start = new Date(now.getFullYear(), 0, 1);
    } else if (preset === 'all') {
        start = new Date('2020-01-01');
    }

    document.getElementById('filterStartDate').value = formatDateToInput(start);
    document.getElementById('filterEndDate').value = formatDateToInput(now);
    initDashboard();
}

function getDateRange() {
    const startEl = document.getElementById('filterStartDate');
    const endEl = document.getElementById('filterEndDate');
    const now = new Date();

    const startStr = startEl?.value || formatDateToInput(new Date(now.getFullYear(), now.getMonth(), 1));
    const endStr = endEl?.value || formatDateToInput(now);

    // update the subtitle label
    const label = document.getElementById('dateRangeLabel');
    if (label) label.textContent = `Dữ liệu từ ${formatDateVN(startStr)} đến ${formatDateVN(endStr)}`;

    return `?start=${startStr}&end=${endStr}`;
}

// listen for status filter change on dashboard orders table
document.addEventListener('DOMContentLoaded', () => {
    // init default preset to "tháng này"
    const now = new Date();
    const startOfMonth = new Date(now.getFullYear(), now.getMonth(), 1);
    document.getElementById('filterStartDate').value = formatDateToInput(startOfMonth);
    document.getElementById('filterEndDate').value = formatDateToInput(now);

    const dashFilter = document.getElementById('dashboardStatusFilter');
    if (dashFilter) {
        dashFilter.addEventListener('change', () => {
            currentOrderPage = 1;
            hasMoreOrders = true;
            document.getElementById('ordersTableBody').innerHTML = '';
            fetchOrders(1);
        });
    }
});

// khởi chạy khung dữ liệu
async function initDashboard() {
    const dateQuery = getDateRange();

    currentOrderPage = 1;
    hasMoreOrders = true;
    document.getElementById('ordersTableBody').innerHTML = ''; // reset khi filter

    await Promise.all([
        fetchStats(dateQuery),
        fetchChart(dateQuery),
        fetchInventoryInsights(),
        fetchOrders(currentOrderPage)
    ]);
}

// bell notification popup
let bellPopupOpen = false;

function toggleBellPopup() {
    const popup = document.getElementById('bellPopup');
    if (!popup) return;

    bellPopupOpen = !bellPopupOpen;
    if (bellPopupOpen) {
        popup.style.display = 'block';
        // animate in
        requestAnimationFrame(() => {
            popup.style.transform = 'scale(1)';
            popup.style.opacity = '1';
        });
    } else {
        popup.style.transform = 'scale(0.9)';
        popup.style.opacity = '0';
        setTimeout(() => { popup.style.display = 'none'; }, 200);
    }
}

function handleBellAction() {
    // close popup
    bellPopupOpen = false;
    const popup = document.getElementById('bellPopup');
    if (popup) {
        popup.style.transform = 'scale(0.9)';
        popup.style.opacity = '0';
        setTimeout(() => { popup.style.display = 'none'; }, 200);
    }
}

// close popup when clicking outside
document.addEventListener('click', (e) => {
    if (bellPopupOpen && !e.target.closest('#newOrdersAlert')) {
        bellPopupOpen = false;
        const popup = document.getElementById('bellPopup');
        if (popup) {
            popup.style.transform = 'scale(0.9)';
            popup.style.opacity = '0';
            setTimeout(() => { popup.style.display = 'none'; }, 200);
        }
    }
});

async function fetchStats(dateQuery) {
    try {
        const res = await fetch(`${API_BASE}/dashboard/stats${dateQuery}`, { credentials: 'include' });
        const data = await res.json();
        if (data.status === 'success') {
            // update stat cards — replace skeleton with real values
            const setVal = (id, html) => {
                const el = document.getElementById(id);
                if (el) { el.innerHTML = html; el.classList.add('page-loaded'); }
            };

            setVal('totalRevenue', formatCurrency(data.data.total_revenue));
            setVal('totalOrdersCount', data.data.total_orders);
            setVal('pendingOrdersCount', data.data.pending_orders);

            // update revenue sub label
            const revSub = document.getElementById('revenueSubLabel');
            if (revSub) revSub.textContent = `${data.data.completed_orders} giao dịch thành công trong kỳ`;

            // restore stat meta text
            const ordersMeta = document.querySelector('#totalOrdersCount + .stat-meta');
            if (ordersMeta) ordersMeta.innerHTML = '<i class="fa-solid fa-receipt" style="color:var(--primary);"></i> trong kỳ đã chọn';
            const pendingMeta = document.querySelector('#pendingOrdersCount + .stat-meta');
            if (pendingMeta) pendingMeta.innerHTML = '<i class="fa-solid fa-arrow-down-to-line"></i> Bấm để xem ngay';

            // bell notification widget
            const pending = data.data.pending_orders;
            const alertBox = document.getElementById('newOrdersAlert');
            if (alertBox) {
                if (pending > 0) {
                    alertBox.style.display = 'block';
                    document.getElementById('newOrdersCount').textContent = pending;
                    const bellPopupCountEl = document.getElementById('bellPopupCount');
                    if (bellPopupCountEl) bellPopupCountEl.textContent = pending;
                } else {
                    alertBox.style.display = 'none';
                }
            }
        }
    } catch (err) { console.error('Lỗi lấy thống kê:', err); }
}

async function fetchChart(dateQuery) {
    try {
        const res = await fetch(`${API_BASE}/dashboard/chart${dateQuery}`, { credentials: 'include' });
        const data = await res.json();
        if (data.status === 'success') {
            renderChart(data.data);
            // remove chart skeleton overlay after chart renders
            const sk = document.getElementById('chartSkeleton');
            if (sk) { sk.style.opacity = '0'; sk.style.transition = 'opacity 0.3s'; setTimeout(() => sk.remove(), 300); }
        }
    } catch (err) { console.error('Lỗi lấy dữ liệu Chart:', err); }
}

async function fetchInventoryInsights() {
    try {
        const res = await fetch(`${API_BASE}/dashboard/inventory-insights`, { credentials: 'include' });
        const data = await res.json();
        if (data.status === 'success') {

            // ── low stock ──
            const lowStockList = document.getElementById('lowStockList');
            const lowStockCount = document.getElementById('lowStockCount');
            lowStockList.innerHTML = '';

            const items = data.data.low_stock;
            if (items.length === 0) {
                lowStockList.innerHTML = `
                    <div style="text-align:center; padding: 2rem 1rem; color: var(--text-muted);">
                        <i class="fa-solid fa-box-open" style="font-size: 2rem; display:block; margin-bottom:0.75rem; opacity:0.3;"></i>
                        <p style="font-size:0.875rem; font-weight:500;">Tồn kho đang ở mức an toàn 🎉</p>
                    </div>`;
                if (lowStockCount) lowStockCount.style.display = 'none';
            } else {
                if (lowStockCount) {
                    lowStockCount.textContent = items.length;
                    lowStockCount.style.display = 'inline-flex';
                }
                items.forEach(item => {
                    const pct = item.min_stock > 0 ? Math.min(100, Math.round((item.stock / item.min_stock) * 100)) : 0;
                    const isEmpty = item.stock === 0;
                    const barColor = isEmpty ? '#ef4444' : (pct < 50 ? '#f59e0b' : '#ef4444');

                    lowStockList.innerHTML += `
                        <div style="background: ${isEmpty ? '#fff1f2' : '#fffbeb'}; border:1px solid ${isEmpty ? '#fecdd3' : '#fde68a'}; border-radius:10px; padding: 0.85rem 1rem; cursor: pointer;"
                             onclick="openStockModal(${item.id}, ${item.stock}, ${item.min_stock})">
                            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:0.5rem;">
                                <div style="flex:1; min-width:0;">
                                    <div style="font-size:0.8rem; font-weight:700; color:#0f172a; truncate; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                        ${item.product_name || item.name}
                                    </div>
                                    <div style="font-size:0.7rem; color:#64748b; font-family:monospace;">${item.sku}</div>
                                </div>
                                <div style="text-align:right; flex-shrink:0; margin-left:1rem;">
                                    <span style="font-size:1rem; font-weight:800; color:${barColor};">${item.stock}</span>
                                    <span style="font-size:0.72rem; color:#94a3b8;"> / ${item.min_stock}</span>
                                </div>
                            </div>
                            <div style="height:5px; background:#f1f5f9; border-radius:10px; overflow:hidden;">
                                <div style="height:100%; width:${pct}%; background:${barColor}; border-radius:10px; transition:width 0.5s ease;"></div>
                            </div>
                            ${isEmpty ? '<div style="font-size:0.7rem; color:#ef4444; margin-top:0.4rem; font-weight:600;">⚠ Đã hết hàng</div>' : ''}
                        </div>
                    `;
                });
            }

            // ── ai insights ──
            const aiBody = document.getElementById('aiInsightsList');
            aiBody.innerHTML = '';
            const insights = data.data.ai_insights;

            if (!insights || insights.length === 0) {
                aiBody.innerHTML = `
                    <div style="text-align:center; padding: 2rem 1rem; color:var(--text-muted);">
                        <i class="fa-solid fa-check-circle" style="font-size:2rem; display:block; margin-bottom:0.75rem; color:#10b981; opacity:0.7;"></i>
                        <p style="font-size:0.875rem; font-weight:500;">Không phát hiện bất thường nào</p>
                    </div>`;
            } else {
                insights.forEach((ins, idx) => {
                    aiBody.innerHTML += `
                        <div style="display:flex; gap:0.75rem; align-items:flex-start; background:#fafafa; border:1px solid #f1f5f9; border-radius:10px; padding: 0.85rem 1rem;">
                            <div style="width:28px; height:28px; background:#fef3c7; border-radius:8px; display:flex; align-items:center; justify-content:center; flex-shrink:0; margin-top:1px;">
                                <i class="fa-solid fa-lightbulb" style="color:#f59e0b; font-size:0.78rem;"></i>
                            </div>
                            <div style="font-size:0.82rem; color:#374151; line-height:1.55; flex:1;">${ins}</div>
                        </div>
                    `;
                });
            }
        }
    } catch (err) { console.error('Lỗi lấy Dự đoán AI:', err); }
}

async function fetchOrders(page = 1) {
    if (isOrderLoading || !hasMoreOrders) return;
    isOrderLoading = true;

    const container = document.getElementById('ordersTableBody');

    // show loading indicator
    if (page === 1) {
        container.innerHTML = `
            <div class="card" style="text-align: center; padding: 2rem; color: var(--text-muted);">
                <i class="fa-solid fa-circle-notch fa-spin text-primary" style="font-size: 1.5rem; margin-bottom: 0.75rem; display: block;"></i>
                Đang tải đơn hàng...
            </div>`;
    } else {
        const loadingCard = document.createElement('div');
        loadingCard.id = 'loadingOrdersRow';
        loadingCard.className = 'card';
        loadingCard.style.cssText = 'text-align:center; padding: 1.25rem; color: var(--text-muted); margin-bottom: 0.75rem;';
        loadingCard.innerHTML = `<i class="fa-solid fa-circle-notch fa-spin text-primary"></i> Đang tải thêm...`;
        container.appendChild(loadingCard);
    }

    try {
        const statusFilter = document.getElementById('dashboardStatusFilter');
        const statusParam = statusFilter ? statusFilter.value : 'pending';
        const res = await fetch(`${API_BASE}/dashboard/orders?page=${page}&limit=10&status=${statusParam}`, { credentials: 'include' });
        const data = await res.json();

        const loader = document.getElementById('loadingOrdersRow');
        if (loader) loader.remove();
        if (page === 1) container.innerHTML = '';

        if (data.status === 'success') {
            if (data.data.length === 0 && page === 1) {
                container.innerHTML = `
                    <div class="card" style="text-align: center; padding: 3rem; color: var(--text-muted);">
                        <i class="fa-solid fa-check-circle" style="font-size: 2.5rem; margin-bottom: 1rem; display: block; color: var(--success); opacity: 0.7;"></i>
                        <p style="font-size: 1rem; font-weight: 500;">Tuyệt vời! Không còn đơn hàng nào tồn đọng.</p>
                    </div>`;
                hasMoreOrders = false;
                return;
            }

            data.data.forEach(order => {
                const paymentMethodLabel = order.payment_method === 'bank' ? '💳 Chuyển khoản' : '💵 COD';
                const card = document.createElement('div');
                card.className = 'order-card';
                card.innerHTML = `
                    <div class="order-card-header">
                        <div class="order-card-header-left">
                            <span class="order-id-badge">#${order.id}</span>
                            <span class="order-code">${order.code || ''}</span>
                        </div>
                        <div class="order-card-header-right">
                            ${getStatusBadge(order.status)}
                        </div>
                    </div>
                    <div class="order-card-body">
                        <div class="order-info-group">
                            <div class="order-info-label"><i class="fa-solid fa-user"></i> Khách hàng</div>
                            <div class="order-info-value"><strong>${order.customer || 'Khách Vãng Lai'}</strong></div>
                        </div>
                        <div class="order-info-group">
                            <div class="order-info-label"><i class="fa-solid fa-receipt"></i> Tổng tiền</div>
                            <div class="order-info-value order-amount">${formatCurrency(order.total)}</div>
                        </div>
                        <div class="order-info-group">
                            <div class="order-info-label"><i class="fa-solid fa-credit-card"></i> Thanh toán</div>
                            <div class="order-info-value" style="font-size: 0.85rem;">${paymentMethodLabel}</div>
                            <div style="margin-top: 0.35rem;">${getPaymentBadge(order.payment_status)}</div>
                        </div>
                        <div class="order-info-group">
                            <div class="order-info-label"><i class="fa-solid fa-calendar"></i> Ngày đặt</div>
                            <div class="order-info-value" style="font-size: 0.85rem; color: var(--text-muted);">${formatDate(order.created_at)}</div>
                        </div>
                        <div class="order-info-group order-actions-group">
                            <button type="button" class="btn btn-primary btn-sm" style="width: 100%; margin-bottom: 0.4rem;" onclick="viewOrderDetail('${order.code}')">
                                <i class="fa-solid fa-eye"></i> Chi Tiết
                            </button>
                            ${getDashboardActionHtml(order).replace('btn-block mt-1', '').replace('mt-1', '')}
                        </div>
                    </div>
                `;
                container.appendChild(card);
            });

            if (data.pagination) {
                hasMoreOrders = data.pagination.has_more;
                if (hasMoreOrders && page === currentOrderPage) {
                    currentOrderPage++;
                }
            }
        }
    } catch (err) {
        console.error('Lỗi lấy Đơn Hàng:', err);
        const loader = document.getElementById('loadingOrdersRow');
        if (loader) {
            loader.innerHTML = `<i class="fa-solid fa-triangle-exclamation text-danger"></i> Tải dữ liệu thất bại. Hãy thử lại.`;
        }
    } finally {
        isOrderLoading = false;
    }
}

// bắt sự kiện scroll window để chạy infinite scroll bảng đơn hàng
window.addEventListener('scroll', () => {
    const { scrollTop, scrollHeight, clientHeight } = document.documentElement;
    // kiểm tra xem đã cuộn tới gần đáy chưa (cách đáy 300px)
    if (scrollTop + clientHeight >= scrollHeight - 300) {
        if (!isOrderLoading && hasMoreOrders) {
            fetchOrders(currentOrderPage);
        }
    }
});

// hàm vẽ chart area dùng thư viện chart.js
function renderChart(timelineData) {
    const ctx = document.getElementById('revenueChart').getContext('2d');

    // nếu có chart cũ đang vẽ thì destroy đi để vẽ mới
    if (chartInstance) {
        chartInstance.destroy();
    }

    const labels = timelineData.map(d => d.date);
    const revenues = timelineData.map(d => parseFloat(d.revenue));

    // gradient tĩnh cho line area
    let gradient = ctx.createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, 'rgba(79, 70, 229, 0.4)'); // primary color with opacity
    gradient.addColorStop(1, 'rgba(79, 70, 229, 0.05)');

    chartInstance = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Doanh thu (VNĐ)',
                data: revenues,
                borderColor: '#4f46e5',
                backgroundColor: gradient,
                borderWidth: 2,
                pointBackgroundColor: '#ffffff',
                pointBorderColor: '#4f46e5',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6,
                fill: true,
                tension: 0.4 // làm cong đường line (area)
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(0,0,0,0.8)',
                    padding: 12,
                    callbacks: {
                        label: function (context) {
                            let label = context.dataset.label || '';
                            if (label) { label += ': '; }
                            if (context.parsed.y !== null) { label += formatCurrency(context.parsed.y); }
                            return label;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: '#e5e7eb', drawBorder: false },
                    ticks: {
                        callback: function (value) { return formatCurrency(value); }
                    }
                },
                x: {
                    grid: { display: false, drawBorder: false }
                }
            }
        }
    });
}

// bắt sự kiện reload từ modal
window.addEventListener('orderStatusUpdated', () => {
    initDashboard();
});

document.addEventListener('DOMContentLoaded', () => {
    initDashboard();

    // toggle and scroll logic for floating alert
    const alertBox = document.getElementById('newOrdersAlert');
    const closeBtn = document.getElementById('closeAlertBtn');

    if (alertBox) {
        alertBox.addEventListener('click', function (e) {
            if (!this.classList.contains('expanded')) {
                this.classList.add('expanded');
            } else {
                const target = document.getElementById('ordersTableAnchor');
                if (target) target.scrollIntoView({ behavior: 'smooth' });
            }
        });
    }

    if (closeBtn) {
        closeBtn.addEventListener('click', function (e) {
            e.stopPropagation(); // tránh kích hoạt scroll của công cha
            alertBox.classList.remove('expanded');
        });
    }
});

// stock modal (chỉnh stock + min_stock) - reused from variant list
function openStockModal(id, stock, minStock) {
    document.getElementById('stockEditModal')?.remove();

    const overlay = document.createElement('div');
    overlay.id = 'stockEditModal';
    overlay.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,0.45);z-index:10000;display:flex;align-items:center;justify-content:center;';
    overlay.onclick = (e) => { if (e.target === overlay) closeStockModal(); };

    overlay.innerHTML = `
        <div style="background:white;border-radius:16px;padding:1.75rem 2rem;width:360px;max-width:92vw;box-shadow:0 24px 60px rgba(0,0,0,0.2);animation:fadeInPage 0.2s ease;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;">
                <div>
                    <div style="font-weight:800;font-size:1rem;color:#0f172a;">Chỉnh tồn kho</div>
                    <div style="font-size:0.75rem;color:#94a3b8;margin-top:0.1rem;">Biến thể #${id}</div>
                </div>
                <button onclick="closeStockModal()" style="background:none;border:none;cursor:pointer;color:#94a3b8;font-size:1.25rem;line-height:1;padding:0.25rem;">×</button>
            </div>
            <div style="margin-bottom:1rem;">
                <label style="font-size:0.78rem;font-weight:700;color:#374151;display:block;margin-bottom:0.4rem;"><i class="fa-solid fa-boxes-stacked" style="color:#4f46e5;"></i> Tồn kho hiện tại</label>
                <div style="display:flex;align-items:center;gap:0.4rem;">
                    <button type="button" onclick="adjustStockField('stockFieldInput', -1)"
                        style="width:36px;height:36px;border:1px solid #e2e8f0;border-radius:8px;background:#f8fafc;cursor:pointer;font-weight:700;font-size:1.1rem;">−</button>
                    <input id="stockFieldInput" type="number" min="0" value="${stock}"
                        style="flex:1;text-align:center;border:1px solid #e2e8f0;border-radius:8px;padding:0.45rem;font-size:1rem;font-weight:700;color:#0f172a;outline:none;">
                    <button type="button" onclick="adjustStockField('stockFieldInput', 1)"
                        style="width:36px;height:36px;border:1px solid #e2e8f0;border-radius:8px;background:#f8fafc;cursor:pointer;font-weight:700;font-size:1.1rem;">+</button>
                </div>
            </div>
            <div style="margin-bottom:1.5rem;">
                <label style="font-size:0.78rem;font-weight:700;color:#374151;display:block;margin-bottom:0.4rem;"><i class="fa-solid fa-triangle-exclamation" style="color:#f59e0b;"></i> Ngưỡng cảnh báo (min)</label>
                <div style="display:flex;align-items:center;gap:0.4rem;">
                    <button type="button" onclick="adjustStockField('minFieldInput', -1)"
                        style="width:36px;height:36px;border:1px solid #fde68a;border-radius:8px;background:#fef9c3;cursor:pointer;font-weight:700;font-size:1.1rem;">−</button>
                    <input id="minFieldInput" type="number" min="0" value="${minStock}"
                        style="flex:1;text-align:center;border:1px solid #fde68a;border-radius:8px;padding:0.45rem;font-size:1rem;font-weight:700;color:#92400e;outline:none;background:#fffbeb;">
                    <button type="button" onclick="adjustStockField('minFieldInput', 1)"
                        style="width:36px;height:36px;border:1px solid #fde68a;border-radius:8px;background:#fef9c3;cursor:pointer;font-weight:700;font-size:1.1rem;">+</button>
                </div>
            </div>
            <div style="display:flex;gap:0.6rem;">
                <button onclick="closeStockModal()" style="flex:1;padding:0.55rem;border:1px solid #e2e8f0;border-radius:10px;background:white;cursor:pointer;font-size:0.85rem;font-weight:600;color:#64748b;">Hủy</button>
                <button id="saveStockBtn" onclick="saveStockModal(${id})" style="flex:2;padding:0.55rem;background:#4f46e5;color:white;border:none;border-radius:10px;cursor:pointer;font-size:0.85rem;font-weight:700;">
                    <i class="fa-solid fa-floppy-disk"></i> Lưu thay đổi
                </button>
            </div>
        </div>
    `;
    document.body.appendChild(overlay);
    setTimeout(() => document.getElementById('stockFieldInput')?.select(), 60);
}

function closeStockModal() {
    document.getElementById('stockEditModal')?.remove();
}

function adjustStockField(inputId, delta) {
    const inp = document.getElementById(inputId);
    if (!inp) return;
    inp.value = Math.max(0, (parseInt(inp.value) || 0) + delta);
}

async function saveStockModal(id) {
    const stockVal = parseInt(document.getElementById('stockFieldInput')?.value);
    const minVal = parseInt(document.getElementById('minFieldInput')?.value);
    if (isNaN(stockVal) || stockVal < 0 || isNaN(minVal) || minVal < 0) return;

    const btn = document.getElementById('saveStockBtn');
    btn.disabled = true;
    btn.innerHTML = `<i class="fa-solid fa-spinner fa-spin"></i> Đang lưu...`;

    try {
        const formData = new FormData();
        formData.append('variant_id', id);
        formData.append('stock', stockVal);
        formData.append('min_stock', minVal);

        const res = await fetch('https:// polygearid.ivi.vn/back-end/api/admin/variants/update-stock', {
            method: 'POST',
            credentials: 'include',
            body: formData
        });
        const data = await res.json();
        if (data.status === 'success') {
            closeStockModal();
            initDashboard(); // reload dashboard stats
        } else {
            alert('Lỗi: ' + data.message);
            btn.disabled = false;
            btn.innerHTML = `<i class="fa-solid fa-floppy-disk"></i> Lưu thay đổi`;
        }
    } catch (e) {
        alert('Lỗi kết nối!');
        btn.disabled = false;
        btn.innerHTML = `<i class="fa-solid fa-floppy-disk"></i> Lưu thay đổi`;
    }
}
