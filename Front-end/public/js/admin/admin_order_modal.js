// lấy api_base từ script chính (admin_dashboard.js hoặc admin_orders.js)
const MODAL_API_BASE = 'https:// polygearid.ivi.vn/back-end/api/admin';

// modal xem chi tiết
async function viewOrderDetail(orderCode) {
    if (!orderCode) return;
    try {
        const modal = document.getElementById('orderModal');
        modal.classList.add('show');
        document.getElementById('modalOrderIdLabel').textContent = '#' + orderCode;
        
        // đặt trạng thái tải
        document.getElementById('orderDetailsTableBody').innerHTML = '<tr><td colspan="5" class="text-center">Đang tải chi tiết...</td></tr>';
        document.getElementById('statusUpdateBlock').style.display = 'none';

        const res = await fetch(`${MODAL_API_BASE}/dashboard/orders/detail?code=${orderCode}`, { credentials: 'include' });
        const data = await res.json();
        
        if(data.status === 'success') {
            const order = data.data;
            document.getElementById('modalCustomerName').textContent = order.receiver_name;
            document.getElementById('modalCustomerPhone').textContent = order.receiver_phone;
            document.getElementById('modalShippingAddress').textContent = order.shipping_address;
            document.getElementById('modalReminder').textContent = order.reminder || 'Không có ghi chú';
            document.getElementById('modalPaymentMethod').textContent = order.payment_method;
            document.getElementById('modalPaymentStatus').textContent = order.payment_status;

            const tbody = document.getElementById('orderDetailsTableBody');
            tbody.innerHTML = '';
            
            let html = '';
            let subtotal = 0;
            order.items.forEach(item => {
                const itemTotal = item.price * item.quantity;
                subtotal += itemTotal;
                html += `
                    <tr>
                        <td style="display: flex; align-items: center; gap: 10px;">
                            <img src="${item.main_image_url || 'https:// via.placeholder.com/40'}" style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px;">
                            <span style="white-space: normal; min-width: 150px;">${item.product_name || 'Đang tải...'}</span>
                        </td>
                        <td>${item.variant_name || ''} <br><small class="text-muted">SKU: ${item.sku}</small></td>
                        <td>${formatCurrency(item.price)}</td>
                        <td>${item.quantity}</td>
                        <td style="font-weight: 600; color: var(--primary);">${formatCurrency(itemTotal)}</td>
                    </tr>
                `;
            });

            // thêm summary breakdown
            const discount = parseFloat(order.discount || 0);
            const shipping = parseFloat(order.shipping_fee || 0);
            const total = parseFloat(order.total_price);

            html += `
                <tr style="background-color: #fcfcfc;">
                    <td colspan="4" style="text-align: right; border-top: 2px solid #eee; padding-top: 1.5rem;"><strong>Tạm tính:</strong></td>
                    <td style="border-top: 2px solid #eee; padding-top: 1.5rem;">${formatCurrency(subtotal)}</td>
                </tr>
                ${discount > 0 ? `
                <tr style="background-color: #fcfcfc;">
                    <td colspan="4" style="text-align: right; color: #ef4444;"><strong>Giảm giá voucher:</strong></td>
                    <td style="color: #ef4444;">-${formatCurrency(discount)}</td>
                </tr>` : ''}
                <tr style="background-color: #fcfcfc;">
                    <td colspan="4" style="text-align: right;"><strong>Phí vận chuyển:</strong></td>
                    <td>${formatCurrency(shipping)}</td>
                </tr>
                <tr style="background-color: #f8fafc;">
                    <td colspan="4" style="text-align: right; font-size: 1.1rem; padding-bottom: 1.5rem;"><strong>Tổng thanh toán:</strong></td>
                    <td style="font-size: 1.1rem; font-weight: 800; color: var(--primary); padding-bottom: 1.5rem;">${formatCurrency(total)}</td>
                </tr>
            `;

            tbody.innerHTML = html;

            renderStatusUpdateFlow(order);
        } else {
            document.getElementById('orderDetailsTableBody').innerHTML = `<tr><td colspan="5" class="text-center text-danger">${data.message || 'Lỗi lấy dữ liệu'}</td></tr>`;
        }
    } catch(err) { 
        console.error('Lỗi lấy chi tiết đơn hàng:', err);
        document.getElementById('orderDetailsTableBody').innerHTML = `<tr><td colspan="5" class="text-center text-danger">Lỗi kết nối máy chủ</td></tr>`;
    }
}

function renderStatusUpdateFlow(order) {
    const orderCode = order.order_code;
    const currentStatus = order.status;
    const cancelReason = order.cancel_reason;

    const block = document.getElementById('statusUpdateBlock');
    const label = document.getElementById('currentStatusLabel');
    const buttonsContainer = document.getElementById('statusActionButtons');
    
    // dùng getstatusbadge nếu có, hoặc custom map
    const badgeMap = {
        'pending': '<span class="status-badge pending">Đang chờ duyệt</span>',
        'shipping': '<span class="status-badge shipping">Đang chuẩn bị hàng</span>',
        'delivering': '<span class="status-badge shipping">Đang giao</span>',
        'completed': '<span class="status-badge completed">Đã hoàn thành</span>',
        'cancelled': '<span class="status-badge cancelled">Đã hủy</span>',
        'returning': '<span class="status-badge returning">Yêu cầu trả hàng</span>',
        'returned': '<span class="status-badge returned">Đã hoàn tiền</span>',
        'failed': '<span class="status-badge failed">Thất bại</span>'
    };
    
    let labelHtml = badgeMap[currentStatus] || currentStatus;
    if (currentStatus === 'cancelled' && cancelReason) {
        labelHtml += `<br><span style="color: #dc3545; font-size: 0.85rem; font-weight: normal; margin-top: 5px; display: inline-block;">Lý do hủy: ${cancelReason}</span>`;
    }
    label.innerHTML = labelHtml;

    let html = '';
    
    switch(currentStatus) {
        case 'pending': 
            html += `<button class="btn" style="background-color: #004085; color: white; border: none; padding: 0.5rem 1rem; border-radius: 4px; cursor: pointer;" onclick="updateOrderStatus('${orderCode}', 'shipping')">Chuyển sang: Đang Vận Chuyển</button>`;
            html += `<button class="btn" style="background-color: #dc3545; color: white; border: none; padding: 0.5rem 1rem; border-radius: 4px; cursor: pointer;" onclick="updateOrderStatus('${orderCode}', 'cancelled')">Hủy đơn hàng</button>`;
            break;
        case 'shipping':
            html += `<button class="btn" style="background-color: #17a2b8; color: white; border: none; padding: 0.5rem 1rem; border-radius: 4px; cursor: pointer;" onclick="updateOrderStatus('${orderCode}', 'delivering')">Xác nhận: Đang Giao Đến Khách</button>`;
            html += `<button class="btn" style="background-color: #dc3545; color: white; border: none; padding: 0.5rem 1rem; border-radius: 4px; cursor: pointer;" onclick="updateOrderStatus('${orderCode}', 'failed')">Vận chuyển thất bại</button>`;
            break;
        case 'delivering':
            html += `<span class="text-muted mb-2 block" style="font-size: 0.9rem;">Chờ khách C/N đã nhận (Admin không ghi đè Thành Công)</span><br>`;
            html += `<button class="btn" style="background-color: #dc3545; color: white; border: none; padding: 0.5rem 1rem; border-radius: 4px; cursor: pointer;" onclick="updateOrderStatus('${orderCode}', 'failed')">Khách không nhận hàng (Boom)</button>`;
            html += `<button class="btn" style="background-color: #6c757d; color: white; border: none; padding: 0.5rem 1rem; border-radius: 4px; cursor: pointer; ml-2;" onclick="updateOrderStatus('${orderCode}', 'returned')">Nhập lại kho (Trả về)</button>`;
            break;
        case 'returning':
            html += `<button class="btn" style="background-color: #a855f7; color: white; border: none; padding: 0.5rem 1rem; border-radius: 4px; cursor: pointer;" onclick="updateOrderStatus('${orderCode}', 'returned')">Nhận Hàng & Hoàn Tiền</button>`;
            break;
        default:
            html = `<span class="text-muted" style="font-size: 0.9rem;">Đơn hàng đã đóng tiến trình ở trạng thái cuối, không thể thay đổi thêm.</span>`;
            break;
    }

    buttonsContainer.innerHTML = html;
    block.style.display = 'block';
}

async function updateOrderStatus(orderCode, newStatus) {
    if(!confirm('Xác nhận cập nhật trạng thái đơn hàng này?')) return;
    
    let cancelReason = null;
    if (newStatus === 'cancelled') {
        cancelReason = prompt('Vui lòng nhập lý do hủy đơn:');
        if (cancelReason === null || cancelReason.trim() === '') {
            alert('Bạn phải nhập lý do để hủy đơn hàng.');
            return;
        }
    }

    try {
        const res = await fetch(`${MODAL_API_BASE}/dashboard/orders/update-status`, {
          credentials: 'include',
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ order_code: orderCode, status: newStatus, cancel_reason: cancelReason })
        });
        const data = await res.json();
        if(data.status === 'success') {
            viewOrderDetail(orderCode);
            // kích hoạt event custom để các trang có thể tự reload table
            window.dispatchEvent(new Event('orderStatusUpdated'));
        } else {
            alert('Lỗi: ' + data.message);
        }
    } catch(err) {
        console.error(err);
        alert('Lỗi kết nối máy chủ!');
    }
}

function closeModal() {
    document.getElementById('orderModal').classList.remove('show');
}

// format currency
function formatCurrency(amount) {
    if (!amount) return '0 ₫';
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(amount);
}

// bắt sự kiện click ra ngoài vùng mờ để đóng modal
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('orderModal');
    if (modal) {
        modal.addEventListener('click', function (e) {
            if (e.target === this) {
                closeModal();
            }
        });
    }
});
