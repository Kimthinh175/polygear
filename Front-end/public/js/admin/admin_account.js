const API_BASE = 'https:// polygearid.ivi.vn/back-end/api/admin';

// page-level init
document.addEventListener('DOMContentLoaded', () => {
    if (window.ACTIVE_TAB === 'user') {
        loadUsers();
    } else if (window.ACTIVE_TAB === 'admin') {
        loadStaff();
    } else if (window.ACTIVE_TAB === 'super') {
        loadSuperAdmin();
    }

    const staffForm = document.getElementById('staffForm');
    if (staffForm) staffForm.addEventListener('submit', handleStaffSubmit);
});

// 
// utilities
// 
function closeAccModal(id, event) {
    if (event && event.target !== document.getElementById(id)) return;
    document.getElementById(id).classList.remove('open');
}
function openAccModal(id) {
    document.getElementById(id).classList.add('open');
}

function togglePwd() {
    const inp = document.getElementById('staff-password');
    const icon = document.getElementById('eye-icon');
    if (inp.type === 'password') { inp.type = 'text'; icon.className = 'fa-solid fa-eye-slash'; }
    else { inp.type = 'password'; icon.className = 'fa-solid fa-eye'; }
}
function toggleResetPwd() {
    const inp = document.getElementById('reset-new-password');
    const icon = document.getElementById('reset-eye-icon');
    if (inp.type === 'password') { inp.type = 'text'; icon.className = 'fa-solid fa-eye-slash'; }
    else { inp.type = 'password'; icon.className = 'fa-solid fa-eye'; }
}

function showToast(msg, type = 'success') {
    const existing = document.getElementById('acc-toast');
    if (existing) existing.remove();

    const toast = document.createElement('div');
    toast.id = 'acc-toast';
    toast.style.cssText = `
        position: fixed; bottom: 2rem; right: 2rem; z-index: 9999;
        background: ${type === 'success' ? '#10b981' : '#ef4444'};
        color: white; padding: 0.85rem 1.5rem; border-radius: 10px;
        font-size: 0.875rem; font-weight: 600; box-shadow: 0 8px 24px rgba(0,0,0,0.15);
        display: flex; align-items: center; gap: 0.6rem;
        animation: slideInToast 0.3s ease forwards;
    `;
    toast.innerHTML = `<i class="fa-solid ${type === 'success' ? 'fa-circle-check' : 'fa-circle-xmark'}"></i> ${msg}`;
    document.body.appendChild(toast);

    const style = document.createElement('style');
    style.textContent = `@keyframes slideInToast { from { opacity:0; transform: translateY(10px); } to { opacity:1; transform: translateY(0); } }`;
    document.head.appendChild(style);

    setTimeout(() => toast.remove(), 3500);
}

// 
// filter (client-side)
// 
function filterTable() {
    const searchVal = (document.getElementById('acc-search')?.value || '').toLowerCase().trim();
    const statusFilter = document.getElementById('status-filter')?.value || '';
    const roleFilter = document.getElementById('role-filter')?.value || '';

    if (window.ACTIVE_TAB === 'user') {
        filterUsers(searchVal, statusFilter);
    } else {
        filterStaff(searchVal, roleFilter);
    }
}

// 
// users
// 
let _allUsers = [];

async function loadUsers() {
    try {
        const resp = await fetch(`${API_BASE}/users`, { credentials: 'include' });
        const text = await resp.text();
        try {
            const res = JSON.parse(text);
            if (res.status === 'success') {
                _allUsers = res.data;
                renderUsers(_allUsers);
                updateUserStats(_allUsers);
            } else {
                console.error('API Error:', res.message);
            }
        } catch (e) {
            console.error('JSON Parse Error. Raw response:', text);
        }
    } catch (e) {
        console.error('Error loading users', e);
    }
}

function updateUserStats(users) {
    const total = users.length;
    const locked = users.filter(u => u.is_locked == 1).length;
    const active = total - locked;
    const statTotal = document.getElementById('stat-total');
    const statActive = document.getElementById('stat-active');
    const statLocked = document.getElementById('stat-locked');
    if (statTotal) statTotal.textContent = total;
    if (statActive) statActive.textContent = active;
    if (statLocked) statLocked.textContent = locked;
}

function filterUsers(search, status) {
    let filtered = _allUsers;
    if (search) {
        filtered = filtered.filter(u =>
            (u.user_name || '').toLowerCase().includes(search) ||
            (u.phone_number || '').toLowerCase().includes(search) ||
            (u.gmail || '').toLowerCase().includes(search)
        );
    }
    if (status === 'active') filtered = filtered.filter(u => !u.is_locked);
    if (status === 'locked') filtered = filtered.filter(u => u.is_locked);
    renderUsers(filtered);
}

function renderUsers(users) {
    const tbody = document.getElementById('user-list');
    if (!tbody) return;

    if (!users.length) {
        tbody.innerHTML = '<tr><td colspan="5" class="acc-loading-row">Không tìm thấy khách hàng nào.</td></tr>';
        return;
    }

    tbody.innerHTML = users.map(user => {
        const isLocked = user.is_locked == 1;
        const initial = (user.user_name || '?')[0].toUpperCase();
        const dateStr = user.create_at
            ? new Date(user.create_at).toLocaleDateString('vi-VN', { day: '2-digit', month: '2-digit', year: 'numeric' })
            : '—';

        return `
        <tr data-id="${user.id}">
            <td>
                <div class="acc-user-cell">
                    <img src="${user.avatar_url
                ? (user.avatar_url.startsWith('http') ? user.avatar_url : `https:// polygearid.ivi.vn/${user.avatar_url}`)
                : 'https:// polygearid.ivi.vn/img/user/default_user.avif'}"
                        class="acc-avatar" 
                        onerror="this.onerror=null;this.src='https:// polygearid.ivi.vn/img/user/default_user.avif'">
                    <div>
                        <div class="acc-user-name">${user.user_name || '—'}</div>
                        <div class="acc-user-id">#${user.id}</div>
                    </div>
                </div>
            </td>
            <td>
                <div class="acc-contact-phone">${user.phone_number || '—'}</div>
                <div class="acc-contact-email">${user.gmail || ''}</div>
            </td>
            <td><div class="acc-date">${dateStr}</div></td>
            <td>
                <span class="acc-badge ${isLocked ? 'locked' : 'active'}">
                    <i class="fa-solid ${isLocked ? 'fa-lock' : 'fa-circle-check'}"></i>
                    ${isLocked ? 'Đã khóa' : 'Hoạt động'}
                </span>
            </td>
            <td>
                <div class="acc-actions">
                    <button class="acc-action-btn" onclick="viewHistory(${user.id}, '${(user.user_name || '').replace(/'/g, "\\'")}')" title="Lịch sử mua hàng">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                    </button>
                    <button class="acc-action-btn ${isLocked ? 'unlock-btn' : 'lock-btn'}"
                        onclick="toggleLock(${user.id}, ${isLocked ? 0 : 1})"
                        title="${isLocked ? 'Mở khóa tài khoản' : 'Khóa tài khoản'}">
                        <i class="fa-solid ${isLocked ? 'fa-unlock' : 'fa-lock'}"></i>
                    </button>
                </div>
            </td>
        </tr>`;
    }).join('');
}

async function toggleLock(userId, lockStatus) {
    const action = lockStatus ? 'khóa' : 'mở khóa';
    if (!confirm(`Bạn có chắc muốn ${action} tài khoản này?`)) return;
    try {
        const resp = await fetch(`${API_BASE}/users/toggle-lock`, {
            method: 'PUT',
            credentials: 'include',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: userId, is_locked: lockStatus })
        });
        const res = await resp.json();
        if (res.status === 'success') {
            showToast(res.message);
            await loadUsers();
        } else {
            showToast(res.message, 'error');
        }
    } catch (e) {
        showToast('Lỗi khi thực hiện thao tác', 'error');
    }
}

async function viewHistory(userId, userName) {
    openAccModal('historyModal');
    const nameEl = document.getElementById('history-user-name');
    if (nameEl) nameEl.textContent = userName;

    const list = document.getElementById('history-list');
    const loading = document.getElementById('history-loading');
    const empty = document.getElementById('history-empty');
    const table = document.getElementById('history-table');

    list.innerHTML = '';
    loading.style.display = 'flex';
    empty.style.display = 'none';
    table.style.display = 'none';

    try {
        const resp = await fetch(`${API_BASE}/users/history?id=${userId}`, { credentials: 'include' });
        const res = await resp.json();
        loading.style.display = 'none';

        if (res.status === 'success' && res.data.length > 0) {
            table.style.display = 'table';
            list.innerHTML = res.data.map(order => {
                const orderDate = order.created_at
                    ? new Date(order.created_at).toLocaleString('vi-VN', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' })
                    : '—';
                const total = new Intl.NumberFormat('vi-VN').format(order.total_price || 0);
                
                // determine order status badge
                let statusClass = 'pending';
                let statusIcon = 'fa-clock';
                const s = (order.status || '').toLowerCase();
                if (s.includes('giao') || s.includes('delivering') || s.includes('shipping')) {
                    statusClass = 'delivering'; statusIcon = 'fa-truck-fast';
                } else if (s.includes('hoàn thành') || s.includes('completed') || s.includes('xong')) {
                    statusClass = 'completed'; statusIcon = 'fa-circle-check';
                } else if (s.includes('hủy') || s.includes('cancelled')) {
                    statusClass = 'cancelled'; statusIcon = 'fa-circle-xmark';
                }

                // determine payment status badge
                const isPaid = (order.payment_status === 'paid' || order.payment_status === 'đã thanh toán');
                const payClass = isPaid ? 'paid' : 'unpaid';
                const payIcon = isPaid ? 'fa-credit-card' : 'fa-hourglass-half';
                const payText = isPaid ? 'Đã thanh toán' : 'Chờ thanh toán';

                return `
                <tr>
                    <td><b style="color:var(--primary)">${order.order_code || '#' + order.id}</b></td>
                    <td>${orderDate}</td>
                    <td><b style="color:var(--text-main)">${total}đ</b></td>
                    <td>
                        <span class="acc-badge ${statusClass}">
                            <i class="fa-solid ${statusIcon}"></i>
                            ${order.status || 'Chờ xử lý'}
                        </span>
                    </td>
                    <td>
                        <span class="acc-badge ${payClass}">
                            <i class="fa-solid ${payIcon}"></i>
                            ${payText}
                        </span>
                    </td>
                </tr>`;
            }).join('');
        } else {
            empty.style.display = 'flex';
        }
    } catch (e) {
        loading.style.display = 'none';
        empty.style.display = 'flex';
    }
}

// 
// staff (admin)
// 
let _allStaff = [];

async function loadStaff() {
    try {
        const resp = await fetch(`${API_BASE}/staff`, { credentials: 'include' });
        const res = await resp.json();
        if (res.status === 'success') {
            _allStaff = res.data;
            renderStaff(_allStaff);
            updateStaffStats(_allStaff);
        }
    } catch (e) {
        console.error('Error loading staff', e);
    }
}

function updateStaffStats(staff) {
    const total = staff.length;
    const managers = staff.filter(s => s.role === 'manager').length;
    const others = total - managers;
    const statTotal = document.getElementById('stat-total');
    const statStaff = document.getElementById('stat-staff-count');
    const statOther = document.getElementById('stat-other-count');
    if (statTotal) statTotal.textContent = total;
    if (statStaff) statStaff.textContent = others;
    if (statOther) statOther.textContent = managers;
}

async function loadSuperAdmin() {
    try {
        const resp = await fetch(`${API_BASE}/super`, { credentials: 'include' });
        const res = await resp.json();
        if (res.status === 'success') {
            _allStaff = res.data; // reuse _allstaff array
            renderStaff(_allStaff);
            updateSuperStats(_allStaff);
        }
    } catch (e) {
        console.error('Error loading super admin', e);
    }
}

function updateSuperStats(admins) {
    const total = admins.length;
    const statTotal = document.getElementById('stat-total');
    const statActive = document.getElementById('stat-active-admin');
    const statAdmins = document.getElementById('stat-total-admins');
    if (statTotal) statTotal.textContent = total;
    if (statActive) statActive.textContent = total; // mocking active for now
    if (statAdmins) statAdmins.textContent = total;
}

function filterStaff(search, role) {
    let filtered = _allStaff;
    if (search) {
        filtered = filtered.filter(s => (s.username || '').toLowerCase().includes(search));
    }
    if (role) filtered = filtered.filter(s => s.role === role);
    renderStaff(filtered);
}

const ROLE_LABELS = {
    admin: { label: 'Quản trị viên', cls: 'admin' },
    manager: { label: 'Quản lý', cls: 'manager' },
    sales: { label: 'Sales', cls: 'sales' },
    warehouse: { label: 'Kho', cls: 'warehouse' },
};

const PERM_LABELS = {
    view_orders: 'Xem ĐH',
    manage_orders: 'Xử lý ĐH',
    view_stock: 'Xem kho',
    manage_stock: 'Q.lý kho',
    manage_products: 'Q.lý SP',
    manage_accounts: 'Q.lý TK',
};

function renderStaff(staff) {
    const tbody = document.getElementById('admin-list');
    if (!tbody) return;

    if (!staff.length) {
        tbody.innerHTML = '<tr><td colspan="4" class="acc-loading-row">Không tìm thấy nhân viên nào.</td></tr>';
        return;
    }

    tbody.innerHTML = staff.map(s => {
        let perms = [];
        try { perms = JSON.parse(s.permissions) || []; } catch (e) { }
        const roleInfo = ROLE_LABELS[s.role] || { label: s.role, cls: 'staff' };
        const initial = (s.username || '?')[0].toUpperCase();

        const deleteBtnHtml = (s.id == 1) 
            ? '' 
            : `<button class="acc-action-btn delete-btn" onclick="deleteStaff(${s.id})" title="Xóa nhân viên">
                   <i class="fa-solid fa-trash"></i>
               </button>`;

        return `
        <tr data-id="${s.id}">
            <td>
                <div class="acc-user-cell">
                    <div class="acc-avatar-placeholder">${initial}</div>
                    <div>
                        <div class="acc-user-name">${s.username}</div>
                        <div class="acc-user-id">#${s.id}</div>
                    </div>
                </div>
            </td>
            <td>
                <span class="acc-badge ${roleInfo.cls}">
                    ${roleInfo.label}
                </span>
            </td>
            <td>
                <div class="acc-perm-tags">
                    <span style="color:var(--text-muted);font-size:0.8rem">Toàn quyền</span>
                </div>
            </td>
            <td>
                <div class="acc-actions">
                    <button class="acc-action-btn" onclick="openResetModal(${s.id})" title="Đổi mật khẩu">
                        <i class="fa-solid fa-key"></i>
                    </button>
                    ${deleteBtnHtml}
                </div>
            </td>
        </tr>`;
    }).join('');
}

function openCreateStaffModal() {
    document.getElementById('staffModalTitle').innerText = 'Thêm quản trị viên mới';
    document.getElementById('staffForm').reset();
    document.getElementById('staff-id').value = '';
    document.getElementById('staff-username').disabled = false;
    document.getElementById('password-group').style.display = 'block';
    document.getElementById('staff-password').required = true;
    document.getElementById('password-help').style.display = 'none';

    openAccModal('staffModal');
}

function openEditStaffModal(staff) {
    // we only allow reset password now, so edit modal might not be used, but keeping it simple
    document.getElementById('staffModalTitle').innerText = `Sửa: ${staff.username}`;
    document.getElementById('staff-id').value = staff.id;
    document.getElementById('staff-username').value = staff.username;
    document.getElementById('staff-username').disabled = true;

    document.getElementById('password-group').style.display = 'block';
    document.getElementById('staff-password').required = false;
    document.getElementById('password-help').style.display = 'block';
    openAccModal('staffModal');
}

async function handleStaffSubmit(e) {
    e.preventDefault();
    const id = document.getElementById('staff-id').value;
    const username = document.getElementById('staff-username').value;
    const password = document.getElementById('staff-password').value;
    const role = document.getElementById('staff-role').value || 'admin';
    const permissions = []; // removed permissions

    if (!id && !password) { showToast('Vui lòng nhập mật khẩu!', 'error'); return; }

    const method = id ? 'PUT' : 'POST';
    const payload = { id, username, role, permissions };
    if (!id) payload.password = password;
    else if (password) payload.password = password; // allow password change on edit too

    try {
        const resp = await fetch(`${API_BASE}/staff`, {
            method,
            credentials: 'include',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const res = await resp.json();
        if (res.status === 'success') {
            showToast(res.message);
            closeAccModal('staffModal');
            await loadStaff();
        } else {
            showToast(res.message, 'error');
        }
    } catch (e) {
        showToast('Lỗi khi lưu thông tin', 'error');
    }
}

function openResetModal(id) {
    document.getElementById('reset-staff-id').value = id;
    document.getElementById('reset-new-password').value = '';
    openAccModal('resetModal');
}

async function confirmResetPassword() {
    const id = document.getElementById('reset-staff-id').value;
    const newPass = document.getElementById('reset-new-password').value.trim();
    if (!newPass) { showToast('Vui lòng nhập mật khẩu mới!', 'error'); return; }

    try {
        const resp = await fetch(`${API_BASE}/staff/reset-password`, {
            method: 'PUT',
            credentials: 'include',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id, password: newPass })
        });
        const res = await resp.json();
        if (res.status === 'success') {
            showToast(res.message);
            closeAccModal('resetModal');
        } else {
            showToast(res.message, 'error');
        }
    } catch (e) {
        showToast('Lỗi khi reset mật khẩu', 'error');
    }
}

async function deleteStaff(id) {
    if (!confirm('Bạn có chắc muốn xóa nhân viên này? Thao tác không thể hoàn tác!')) return;
    try {
        const resp = await fetch(`${API_BASE}/staff`, {
            method: 'DELETE',
            credentials: 'include',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id })
        });
        const res = await resp.json();
        if (res.status === 'success') {
            showToast(res.message);
            await loadStaff();
        } else {
            showToast(res.message, 'error');
        }
    } catch (e) {
        showToast('Lỗi khi xóa nhân viên', 'error');
    }
}

function handleRoleChange() {
    const role = document.querySelector('input[name="staff-role-radio"]:checked')?.value;
    if (!role) return;
    const checkboxes = document.querySelectorAll('input[name="permissions"]');
    if (role === 'admin') {
        checkboxes.forEach(cb => cb.checked = true);
    } else if (role === 'sales') {
        checkboxes.forEach(cb => { cb.checked = ['view_orders', 'manage_orders'].includes(cb.value); });
    } else if (role === 'warehouse') {
        checkboxes.forEach(cb => { cb.checked = ['view_stock', 'manage_stock', 'manage_products'].includes(cb.value); });
    } else if (role === 'manager') {
        checkboxes.forEach(cb => { cb.checked = ['view_orders', 'manage_orders', 'view_stock', 'manage_stock'].includes(cb.value); });
    }
}

// esc key closes any open modal
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        document.querySelectorAll('.acc-modal-overlay.open').forEach(m => m.classList.remove('open'));
    }
});
