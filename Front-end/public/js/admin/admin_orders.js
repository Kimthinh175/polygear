const API_BASE_URL = 'https:// polygearid.ivi.vn/back-end/api/admin';
let currentPage = 1;
let currentSearch = '';
let searchTimeout = null;

// khởi chạy khi dom load xong
document.addEventListener('DOMContentLoaded', () => {
    fetchOrdersList(1);

    // bắt sự kiện gõ tìm kiếm (debounce 500ms)
    document.getElementById('searchOrderInput').addEventListener('input', (e) => {
        clearTimeout(searchTimeout);
        currentSearch = e.target.value.trim();
        searchTimeout = setTimeout(() => {
            fetchOrdersList(1);
        }, 500);
    });

    // bắt sự kiện lọc trạng thái
    const statusFilter = document.getElementById('ordersStatusFilter');
    if (statusFilter) {
        statusFilter.addEventListener('change', () => {
            fetchOrdersList(1);
        });
    }
});

// bắt sự kiện cập nhật từ modal để load lại dữ liệu giữ nguyên trang
window.addEventListener('orderStatusUpdated', () => {
    fetchOrdersList(currentPage);
});

async function fetchOrdersList(page = 1) {
    currentPage = page;
    const tbody = document.getElementById('ordersTableBody');
    tbody.innerHTML = `<tr><td colspan="7" class="text-center text-muted" style="padding: 2rem;"><i class="fa-solid fa-circle-notch fa-spin text-primary"></i> Đang tải dữ liệu...</td></tr>`;
    
    try {
        const statusFilter = document.getElementById('ordersStatusFilter');
        const statusParam = statusFilter ? statusFilter.value : 'all';
        const url = `${API_BASE_URL}/orders/list?page=${page}&limit=12&search=${encodeURIComponent(currentSearch)}&status=${statusParam}`;
        const res = await fetch(url, { credentials: 'include' });
        const data = await res.json();
        
        if (data.status === 'success') {
            renderOrdersTable(data.data);
            renderPagination(data.pagination);
        } else {
            tbody.innerHTML = `<tr><td colspan="7" class="text-center text-danger" style="padding: 2rem;">Có lỗi xảy ra: ${data.message}</td></tr>`;
        }
    } catch (err) {
        console.error('Lỗi lấy danh sách Orders:', err);
        tbody.innerHTML = `<tr><td colspan="7" class="text-center text-danger" style="padding: 2rem;">Lỗi kết nối máy chủ!</td></tr>`;
    }
}

function renderOrdersTable(orders) {
    const container = document.getElementById('ordersTableBody');
    container.innerHTML = '';
    
    if (orders.length === 0) {
        container.innerHTML = `
            <div class="card" style="text-align: center; padding: 3rem; color: var(--text-muted);">
                <i class="fa-solid fa-box-open" style="font-size: 2.5rem; margin-bottom: 1rem; display: block; opacity: 0.3;"></i>
                <p style="font-size: 1rem; font-weight: 500;">Không tìm thấy đơn hàng nào phù hợp.</p>
            </div>`;
        return;
    }

    orders.forEach(order => {
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
                    ${getStatusBadgeDetail(order.status)}
                </div>
            </div>

            <div class="order-card-body">
                <!-- Customer Info -->
                <div class="order-info-group">
                    <div class="order-info-label"><i class="fa-solid fa-user"></i> Khách hàng</div>
                    <div class="order-info-value"><strong>${order.customer || 'Khách Vãng Lai'}</strong></div>
                    <div class="order-info-sub">${order.receiver_phone || ''}</div>
                </div>

                <!-- Amount -->
                <div class="order-info-group">
                    <div class="order-info-label"><i class="fa-solid fa-receipt"></i> Tổng tiền</div>
                    <div class="order-info-value order-amount">${formatCurrencyStyle(order.total)}</div>
                </div>

                <!-- Payment -->
                <div class="order-info-group">
                    <div class="order-info-label"><i class="fa-solid fa-credit-card"></i> Thanh toán</div>
                    <div class="order-info-value" style="font-size: 0.85rem;">${paymentMethodLabel}</div>
                    <div style="margin-top: 0.35rem;">${getPaymentBadge(order.payment_status)}</div>
                </div>

                <!-- Date -->
                <div class="order-info-group">
                    <div class="order-info-label"><i class="fa-solid fa-calendar"></i> Ngày đặt</div>
                    <div class="order-info-value" style="font-size: 0.85rem; color: var(--text-muted);">${formatDateStyle(order.created_at)}</div>
                    ${order.cancel_reason ? `<div style="margin-top: 0.5rem; font-size: 0.8rem; color: #dc3545;">Lý do hủy: ${order.cancel_reason}</div>` : ''}
                </div>

                <!-- Actions -->
                <div class="order-info-group order-actions-group">
                    <button type="button" class="btn btn-primary btn-sm" style="width: 100%; margin-bottom: 0.4rem;" onclick="viewOrderDetail('${order.code}')">
                        <i class="fa-solid fa-eye"></i> Xem Chi Tiết
                    </button>
                    ${getActionButtonsHtml(order).replace('btn-block mt-1', 'mt-0').replace('mt-1', '')}
                </div>
            </div>
        `;
        container.appendChild(card);
    });
}

function renderPagination(pagination) {
    const container = document.getElementById('paginationControls');
    container.innerHTML = '';
    
    const { page, total_pages } = pagination;
    if (total_pages <= 1) return; // không cần phân trang nếu chỉ có 1 trang

    // nút previous
    const prevBtn = document.createElement('button');
    prevBtn.className = 'page-btn';
    prevBtn.disabled = page === 1;
    prevBtn.innerHTML = '<i class="fa-solid fa-chevron-left"></i>';
    prevBtn.onclick = () => fetchOrdersList(page - 1);
    container.appendChild(prevBtn);

    // tính toán số trang hiển thị xung quanh trang hiện tại
    let startPage = Math.max(1, page - 2);
    let endPage = Math.min(total_pages, page + 2);

    // luôn hiển thị trang 1
    if (startPage > 1) {
        container.appendChild(createPageLabelBtn(1));
        if (startPage > 2) {
            const dots = document.createElement('span');
            dots.textContent = '...';
            dots.style.padding = '0.5rem';
            container.appendChild(dots);
        }
    }

    // các trang ở giữa
    for (let i = startPage; i <= endPage; i++) {
        container.appendChild(createPageLabelBtn(i, i === page));
    }

    // luôn hiển thị trang cuối cùng
    if (endPage < total_pages) {
        if (endPage < total_pages - 1) {
            const dots = document.createElement('span');
            dots.textContent = '...';
            dots.style.padding = '0.5rem';
            container.appendChild(dots);
        }
        container.appendChild(createPageLabelBtn(total_pages));
    }

    // nút next
    const nextBtn = document.createElement('button');
    nextBtn.className = 'page-btn';
    nextBtn.disabled = page === total_pages;
    nextBtn.innerHTML = '<i class="fa-solid fa-chevron-right"></i>';
    nextBtn.onclick = () => fetchOrdersList(page + 1);
    container.appendChild(nextBtn);
}

function createPageLabelBtn(pageNum, isActive = false) {
    const btn = document.createElement('button');
    btn.className = `page-btn ${isActive ? 'active' : ''}`;
    btn.textContent = pageNum;
    if (!isActive) {
        btn.onclick = () => fetchOrdersList(pageNum);
    }
    return btn;
}

// helpers
function formatCurrencyStyle(amount) {
    if (!amount) return '0 ₫';
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(amount);
}

function formatDateStyle(dateString) {
    if (!dateString) return '';
    try {
        const date = new Date(dateString);
        if(isNaN(date.getTime())) return dateString;
        return date.toLocaleDateString('vi-VN') + ' ' + date.toLocaleTimeString('vi-VN', {hour: '2-digit', minute:'2-digit'});
    } catch {
        return dateString;
    }
}

function getStatusBadgeDetail(status) {
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

function getActionButtonsHtml(order) {
    let actionHtml = '';
    const st = order.status;
    const isPayosUnpaid = order.payment_method === 'bank' && order.payment_status === 'unpaid';

    if (st === 'pending') {
        if (isPayosUnpaid) {
            actionHtml = `<button type="button" class="btn btn-secondary btn-sm mt-1 btn-block" disabled title="Chờ khách chuyển khoản"><i class="fa-solid fa-clock"></i> Duyệt đơn</button>`;
        } else {
            actionHtml = `<button type="button" class="btn btn-success btn-sm mt-1 btn-block" onclick="quickUpdateOrderStatus('${order.code}', 'shipping')"><i class="fa-solid fa-check"></i> Duyệt đơn</button>`;
        }
    } else if (st === 'shipping') {
        actionHtml = `<button type="button" class="btn btn-info btn-sm mt-1 btn-block" onclick="quickUpdateOrderStatus('${order.code}', 'delivering')"><i class="fa-solid fa-truck"></i> Giao Shipper</button>`;
    } else if (st === 'delivering') {
        actionHtml = `<button type="button" class="btn btn-secondary btn-sm mt-1 btn-block" disabled><i class="fa-solid fa-clock"></i> Khách Đang Nhận</button>`;
    } else if (st === 'returning') {
        actionHtml = `<button type="button" class="btn btn-purple btn-sm mt-1 btn-block" onclick="quickUpdateOrderStatus('${order.code}', 'returned')"><i class="fa-solid fa-rotate-left"></i> Hoàn Tiền</button>`;
    } else if (st === 'completed' || st === 'cancelled' || st === 'failed' || st === 'returned') {
        actionHtml = `<div class="mt-1 text-center text-muted" style="font-size: 0.75rem; font-weight: 700;"><i class="fa-solid fa-flag-checkered"></i> Hoàn tất</div>`;
    }

    return actionHtml;
}

async function quickUpdateOrderStatus(orderCode, newStatus) {
    if(!confirm('Xác nhận cập nhật trạng thái đơn hàng này?')) return;
    try {
        const res = await fetch(`${API_BASE_URL}/dashboard/orders/update-status`, {
            credentials: 'include',
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ order_code: orderCode, status: newStatus })
        });
        const data = await res.json();
        if(data.status === 'success') {
            fetchOrdersList(currentPage);
        } else {
            alert('Lỗi: ' + data.message);
        }
    } catch(err) {
        console.error(err);
        alert('Lỗi kết nối máy chủ!');
    }
}
