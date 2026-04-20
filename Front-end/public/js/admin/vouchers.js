const vouchersManager = {
    vouchers: [],
    currentFilter: 'all',
    
    init() {
        this.loadVouchers();
        document.getElementById('voucherSearch').addEventListener('input', (e) => this.filterVouchers(e.target.value));
    },

    async loadVouchers() {
        const grid = document.getElementById('vouchersList');
        try {
            const res  = await fetch('https:// polygearid.ivi.vn/back-end/api/admin/vouchers', { credentials: 'include' });
            const data = await res.json();
            if (data.status === 'success') {
                this.vouchers = data.data;
                this.updateStats();
                this.applyFilter();
            } else {
                grid.innerHTML = `<div class="vouchers-empty"><i class="fa-solid fa-circle-exclamation" style="color:#ef4444;opacity:1;"></i><p style="color:#ef4444;">Lỗi: ${data.message}</p></div>`;
            }
        } catch (e) {
            grid.innerHTML = `<div class="vouchers-empty"><i class="fa-solid fa-wifi-slash"></i><p>Không thể kết nối máy chủ</p></div>`;
            console.error(e);
        }
    },

    updateStats() {
        const total    = this.vouchers.length;
        const active   = this.vouchers.filter(v => v.status == 1).length;
        const inactive = total - active;
        const avg      = total > 0
            ? Math.round(this.vouchers.reduce((s, v) => s + +v.value, 0) / total)
            : 0;
        document.getElementById('stat-total').textContent    = total;
        document.getElementById('stat-active').textContent   = active;
        document.getElementById('stat-inactive').textContent = inactive;
        document.getElementById('stat-avg').textContent      = avg + '%';
    },

    setFilter(filter, el) {
        this.currentFilter = filter;
        document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
        el.classList.add('active');
        this.applyFilter();
    },

    applyFilter() {
        const query = (document.getElementById('voucherSearch').value || '').toLowerCase();
        let list = this.vouchers;
        if (this.currentFilter === 'active')   list = list.filter(v => v.status == 1);
        if (this.currentFilter === 'inactive') list = list.filter(v => v.status != 1);
        if (query) list = list.filter(v => v.code.toLowerCase().includes(query));
        this.renderVouchers(list);
    },

    filterVouchers(query) {
        this.applyFilter();
    },

    fmt(n) {
        if (!n) return 'Không có';
        return parseInt(n).toLocaleString('vi-VN') + 'đ';
    },

    renderVouchers(list) {
        const grid = document.getElementById('vouchersList');

        if (list.length === 0) {
            grid.innerHTML = `
                <div class="vouchers-empty">
                    <i class="fa-solid fa-ticket"></i>
                    <p style="font-weight:600; color:#64748b;">Không tìm thấy voucher nào</p>
                    <p style="font-size:0.8rem; margin-top:0.25rem;">Thử thay đổi bộ lọc hoặc từ khóa tìm kiếm</p>
                </div>`;
            return;
        }

        grid.innerHTML = list.map(v => {
            const isActive = v.status == 1;
            const stripeClass = isActive ? '' : 'inactive';
            const pillClass   = isActive ? 'active' : 'inactive';
            const pillLabel   = isActive ? '● Hoạt động' : '◌ Tạm dừng';
            const codeClass   = isActive ? '' : 'inactive';

            // parse condition
            let minOrder = null, maxAge = null, globalLimit = null, userLimit = null, maxDisc = null;
            if (v.condition) {
                try {
                    const c = JSON.parse(v.condition);
                    minOrder    = c.min_order    ? this.fmt(c.min_order)   : null;
                    maxAge      = c.max_account_age ? c.max_account_age + ' ngày' : null;
                    globalLimit = c.global_limit ? c.global_limit + ' lần' : null;
                    userLimit   = c.user_limit   ? c.user_limit   + ' lần/người' : null;
                    maxDisc     = c.max_discount ? this.fmt(c.max_discount) : null;
                } catch(e) {
                    minOrder = this.fmt(v.condition);
                }
            }

            // dates
            let timeStr = '—';
            if (v.time_start || v.time_end) {
                const s = v.time_start ? new Date(v.time_start).toLocaleDateString('vi-VN') : '∞';
                const e = v.time_end   ? new Date(v.time_end).toLocaleDateString('vi-VN')   : '∞';
                timeStr = s + ' → ' + e;
            }

            return `
            <div class="voucher-card" id="vc-${v.id}">
                <div class="voucher-card-stripe ${stripeClass}"></div>
                <div class="voucher-card-body">
                    <div class="voucher-code-display">
                        <div class="voucher-code-badge ${codeClass}">${v.code}</div>
                        <span class="voucher-status-pill ${pillClass}">${pillLabel}</span>
                    </div>

                    <div class="voucher-info-grid">
                        <div class="voucher-info-item">
                            <label><i class="fa-solid fa-percent" style="font-size:0.6rem;"></i> Giảm giá</label>
                            <span style="color:#ef4444; font-size:1.05rem;">-${v.value}%</span>
                        </div>
                        <div class="voucher-info-item">
                            <label><i class="fa-solid fa-cart-shopping" style="font-size:0.6rem;"></i> Đơn tối thiểu</label>
                            <span>${minOrder || 'Không có'}</span>
                        </div>
                        <div class="voucher-info-item">
                            <label><i class="fa-solid fa-calendar" style="font-size:0.6rem;"></i> Thời gian</label>
                            <span style="font-size:0.76rem;">${timeStr}</span>
                        </div>
                        <div class="voucher-info-item">
                            <label><i class="fa-solid fa-layer-group" style="font-size:0.6rem;"></i> Lượt dùng</label>
                            <span>${globalLimit || 'Không giới hạn'}</span>
                        </div>
                        <div class="voucher-info-item">
                            <label><i class="fa-solid fa-hand-holding-dollar" style="font-size:0.6rem;"></i> Giảm tối đa</label>
                            <span>${maxDisc || '100.000đ'}</span>
                        </div>
                        ${maxAge ? `<div class="voucher-info-item"><label><i class="fa-solid fa-user-clock" style="font-size:0.6rem;"></i> Tài khoản mới</label><span>${maxAge}</span></div>` : ''}
                        ${userLimit ? `<div class="voucher-info-item"><label><i class="fa-solid fa-user" style="font-size:0.6rem;"></i> / 1 User</label><span>${userLimit}</span></div>` : ''}
                    </div>

                    <div class="voucher-divider"><i class="fa-solid fa-scissors" style="font-size:0.75rem;"></i></div>
                </div>

                <div class="voucher-card-footer">
                    <button class="vbtn vbtn-toggle ${isActive ? 'deactivate' : ''}"
                        onclick="vouchersManager.toggleStatus(${v.id}, ${isActive ? 0 : 1})">
                        <i class="fa-solid ${isActive ? 'fa-pause' : 'fa-play'}"></i>
                        ${isActive ? 'Tạm dừng' : 'Kích hoạt'}
                    </button>
                    <button class="vbtn vbtn-edit" onclick='vouchersManager.editVoucher(${JSON.stringify(v)})'>
                        <i class="fa-solid fa-pen-to-square"></i> Sửa
                    </button>
                    <button class="vbtn vbtn-del" onclick="vouchersManager.deleteVoucher(${v.id})">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </div>
            </div>`;
        }).join('');
    },

    openModal(isEdit = false) {
        document.getElementById('modalTitle').innerHTML = isEdit
            ? '<i class="fa-solid fa-pen text-primary"></i> Cập nhật Voucher'
            : '<i class="fa-solid fa-plus text-primary"></i> Thêm Voucher Mới';
        document.getElementById('modalError').style.display = 'none';
        document.getElementById('voucherModal').classList.add('show');
        if (!isEdit) {
            ['voucherId','voucherCode','voucherValue','voucherCondition','voucherMaxAge',
             'voucherGlobalLimit','voucherUserLimit','voucherStart','voucherEnd']
                .forEach(id => document.getElementById(id).value = '');
            const cb = document.getElementById('voucherStatus');
            cb.checked = true;
            cb.dispatchEvent(new Event('change'));
        }
    },

    closeModal() {
        document.getElementById('voucherModal').classList.remove('show');
    },

    editVoucher(v) {
        document.getElementById('voucherId').value   = v.id;
        document.getElementById('voucherCode').value = v.code;
        document.getElementById('voucherValue').value = v.value;
        let minOrder = '', maxAge = '', globalLimit = '', userLimit = '', maxDiscount = '';
        if (v.condition) {
            try {
                const c = JSON.parse(v.condition);
                minOrder    = c.min_order ?? '';
                maxAge      = c.max_account_age ?? '';
                globalLimit = c.global_limit ?? '';
                userLimit   = c.user_limit ?? '';
                maxDiscount = c.max_discount ?? '';
            } catch (e) { minOrder = v.condition; }
        }
        document.getElementById('voucherCondition').value   = minOrder;
        document.getElementById('voucherMaxDiscount').value = maxDiscount;
        document.getElementById('voucherMaxAge').value      = maxAge;
        document.getElementById('voucherGlobalLimit').value = globalLimit;
        document.getElementById('voucherUserLimit').value   = userLimit;
        document.getElementById('voucherStart').value       = v.time_start ? v.time_start.slice(0,16) : '';
        document.getElementById('voucherEnd').value         = v.time_end   ? v.time_end.slice(0,16)   : '';
        const cb = document.getElementById('voucherStatus');
        cb.checked = v.status == 1;
        cb.dispatchEvent(new Event('change'));
        this.openModal(true);
    },

    async toggleStatus(id, newStatus) {
        const v = this.vouchers.find(x => x.id == id);
        if (!v) return;
        try {
            const res = await fetch('https:// polygearid.ivi.vn/back-end/api/admin/vouchers/update', {
                method: 'POST', credentials: 'include',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id, status: newStatus })
            });
            const data = await res.json();
            if (data.status === 'success') this.loadVouchers();
            else alert('Lỗi: ' + data.message);
        } catch (e) { alert('Lỗi kết nối!'); }
    },

    async saveVoucher() {
        // reset validation styles
        document.querySelectorAll('.form-control').forEach(el => el.style.borderColor = '');
        
        const id = document.getElementById('voucherId').value;
        const codeInput = document.getElementById('voucherCode');
        const valueInput = document.getElementById('voucherValue');
        const maxDiscountInput = document.getElementById('voucherMaxDiscount');
        const conditionInput = document.getElementById('voucherCondition');
        const globalLimitInput = document.getElementById('voucherGlobalLimit');
        const userLimitInput = document.getElementById('voucherUserLimit');
        const startInput = document.getElementById('voucherStart');
        const endInput = document.getElementById('voucherEnd');

        const code = codeInput.value.trim();
        const value = parseFloat(valueInput.value);
        const maxDiscount = maxDiscountInput.value === '' ? 100000 : parseFloat(maxDiscountInput.value);
        const minOrder = conditionInput.value === '' ? 0 : parseFloat(conditionInput.value);
        const globalLimit = globalLimitInput.value === '' ? 100 : parseInt(globalLimitInput.value);
        const userLimit = userLimitInput.value === '' ? 1 : parseInt(userLimitInput.value);
        const timeStart = startInput.value;
        const timeEnd = endInput.value;

        // validation
        const validate = (el, condition, msg) => {
            if (condition) {
                el.style.borderColor = '#ef4444';
                el.focus();
                this.showError(msg);
                return false;
            }
            return true;
        };

        if (!validate(codeInput, !code, 'Vui lòng nhập mã voucher!')) return;
        if (!validate(valueInput, isNaN(value) || value < 0 || value > 20, 'Mức giảm giá phải từ 0 đến 20%!')) return;
        if (!validate(maxDiscountInput, maxDiscount < 0, 'Giảm tối đa không được âm!')) return;
        if (!validate(conditionInput, minOrder < 0, 'Đơn tối thiểu không được âm!')) return;
        if (!validate(globalLimitInput, globalLimit < 0 || globalLimit > 300, 'Số lần dùng hệ thống phải từ 0 đến 300!')) return;
        if (!validate(userLimitInput, userLimit < 0, 'Số lần dùng user không được âm!')) return;
        if (!validate(startInput, !timeStart, 'Vui lòng chọn ngày bắt đầu!')) return;
        if (!validate(endInput, !timeEnd, 'Vui lòng chọn ngày kết thúc!')) return;

        const conditionObj = {
            min_order: minOrder,
            max_discount: maxDiscount,
            max_account_age: document.getElementById('voucherMaxAge').value || null,
            global_limit: globalLimit,
            user_limit: userLimit
        };

        const payload = {
            code: code,
            value: value,
            condition: JSON.stringify(conditionObj),
            time_start: timeStart,
            time_end: timeEnd,
            status: document.getElementById('voucherStatus').checked ? 1 : 0
        };

        if (id) payload.id = id;
        const endpoint = id
            ? 'https:// polygearid.ivi.vn/back-end/api/admin/vouchers/update'
            : 'https:// polygearid.ivi.vn/back-end/api/admin/vouchers';
            
        try {
            const res = await fetch(endpoint, {
                method: 'POST', credentials: 'include',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const data = await res.json();
            if (data.status === 'success') { this.closeModal(); this.loadVouchers(); }
            else this.showError(data.message);
        } catch (e) { this.showError('Lỗi kết nối tới máy chủ!'); }
    },

    async deleteVoucher(id) {
        if (!confirm('Bạn có chắc chắn muốn xóa voucher này?')) return;
        try {
            const res  = await fetch('https:// polygearid.ivi.vn/back-end/api/admin/vouchers', {
                method: 'DELETE', credentials: 'include',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id })
            });
            const data = await res.json();
            if (data.status === 'success') this.loadVouchers();
            else alert('Lỗi: ' + data.message);
        } catch (e) { alert('Lỗi kết nối tới máy chủ!'); }
    },

    showError(msg) {
        const el = document.getElementById('modalError');
        el.textContent = msg;
        el.style.display = 'block';
    }
};

document.addEventListener('DOMContentLoaded', () => vouchersManager.init());
