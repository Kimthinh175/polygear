const voucherUI = {
    vouchers: [],
    selectedVoucher: null,
    cartTotal: 0,
    onApplyCallback: null,
    
    _getMinOrder(cond) {
        if (!cond) return 0;
        try {
            const c = JSON.parse(cond);
            return c.min_order ? parseInt(c.min_order) : 0;
        } catch (e) {
            return parseInt(cond) || 0;
        }
    },

    _getMaxDiscount(cond) {
        if (!cond) return 100000;
        try {
            const c = JSON.parse(cond);
            return c.max_discount ? parseInt(c.max_discount) : 100000;
        } catch (e) {
            return 100000;
        }
    },

    init() {
        // cố gắng lấy voucher đã lưu trước đó nếu có
        const saved = localStorage.getItem('appliedVoucher');
        if (saved) {
            try {
                this.selectedVoucher = JSON.parse(saved);
            } catch (e) {}
        }
    },

    setCartTotal(total) {
        this.cartTotal = total;
    },

    setCallback(cb) {
        this.onApplyCallback = cb;
    },

    async openModal() {
        document.getElementById('voucherSelectModal').classList.remove('hidden');
        // trigger reflow
        void document.getElementById('voucherSelectModal').offsetWidth;
        document.getElementById('voucherSelectModal').classList.remove('opacity-0');
        document.querySelector('#voucherSelectModal > div').classList.remove('translate-y-full', 'sm:scale-95');

        await this.loadVouchers();
    },

    closeModal() {
        document.getElementById('voucherSelectModal').classList.add('opacity-0');
        const modalInner = document.querySelector('#voucherSelectModal > div');
        if (window.innerWidth < 640) {
            modalInner.classList.add('translate-y-full');
        } else {
            modalInner.classList.add('sm:scale-95');
        }

        setTimeout(() => {
            document.getElementById('voucherSelectModal').classList.add('hidden');
        }, 300);
    },

    async loadVouchers() {
        const listEl = document.getElementById('availableVouchersList');
        try {
            const res = await fetch('https:// polygearid.ivi.vn/back-end/api/vouchers', { credentials: 'include' });
            const data = await res.json();
            if (data.status === 'success') {
                this.vouchers = data.data;
                this.renderVouchers();
            } else {
                listEl.innerHTML = `<div class="py-10 text-center text-red-500">${data.message}</div>`;
            }
        } catch (e) {
            listEl.innerHTML = `<div class="py-10 text-center text-red-500">Lỗi kết nối máy chủ.</div>`;
        }
    },

    renderVouchers() {
        const listEl = document.getElementById('availableVouchersList');
        if (!this.vouchers || this.vouchers.length === 0) {
            listEl.innerHTML = `
                <div class="py-16 text-center">
                    <i class="fa-solid fa-ticket fa-3x text-gray-200 mb-4 block"></i>
                    <p class="text-gray-500 font-medium">Hiện không có mã giảm giá nào.</p>
                </div>
            `;
            return;
        }

        let html = '';
        this.vouchers.forEach(v => {
            const minOrder = this._getMinOrder(v.condition);
            const isEligible = !v.condition || this.cartTotal >= minOrder;
            const isSelected = this.selectedVoucher && this.selectedVoucher.code === v.code;
            
            let conditionText = v.condition ? `Đơn tối thiểu ${minOrder.toLocaleString('vi-VN')}đ` : 'Áp dụng cho mọi đơn hàng';
            if (!isEligible) {
                const diff = minOrder - this.cartTotal;
                conditionText = `<span class="text-red-500">Mua thêm ${diff.toLocaleString('vi-VN')}đ để dùng</span> (Tối thiểu ${minOrder.toLocaleString('vi-VN')}đ)`;
            }

            let daysLeftHtml = '';
            if (v.time_end) {
                const end = new Date(v.time_end);
                const now = new Date();
                const diffTime = Math.abs(end - now);
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                if (diffDays <= 3 && end > now) {
                    daysLeftHtml = `<div class="text-[10px] text-red-500 mt-1 flex items-center gap-1"><i class="fa-regular fa-clock"></i> Sắp hết hạn: ${diffDays} ngày nữa</div>`;
                }
            }

            html += `
                <div class="bg-white rounded-xl border ${isSelected ? 'border-blue-500 bg-blue-50/10 shadow-md ring-1 ring-blue-500/50' : 'border-gray-100 shadow-sm'} overflow-hidden flex mb-3 relative ${isEligible ? 'cursor-pointer hover:border-blue-300' : 'opacity-60 cursor-not-allowed'} transition-all" onclick="voucherUI.selectVoucher('${v.code}')">
                    
                    <div class="w-24 ${isEligible ? 'bg-gradient-to-br from-blue-500 to-indigo-600' : 'bg-gray-300'} flex flex-col items-center justify-center text-white p-2 shrink-0">
                        <i class="fa-solid fa-percent text-2xl mb-1"></i>
                    </div>
                    
                    <div class="flex-1 p-3 flex justify-between items-center relative gap-3">
                        <div class="flex-1 min-w-0">
                            <div class="font-bold text-gray-800 break-words line-clamp-2">Giảm ${v.value}% <span class="text-xs font-normal text-gray-500 block uppercase" style="letter-spacing: 1px">${v.code}</span></div>
                            <div class="text-[11px] text-gray-500 mt-1 leading-tight">${conditionText}</div>
                            <div class="text-[10px] text-blue-600 font-medium mt-0.5 italic">Giảm tối đa ${this._getMaxDiscount(v.condition).toLocaleString('vi-VN')}đ</div>
                            ${daysLeftHtml}
                        </div>
                        
                        <div class="w-5 h-5 rounded-full border-2 ${isSelected ? 'border-blue-500' : 'border-gray-300'} flex items-center justify-center shrink-0">
                            <div class="w-2.5 h-2.5 bg-blue-500 rounded-full ${isSelected ? '' : 'hidden'} transition-all"></div>
                        </div>
                    </div>
                    
                    <!-- Decorative dots -->
                    <div class="absolute -left-1.5 top-1/2 -translate-y-1/2 w-3 h-3 bg-gray-50 rounded-full"></div>
                </div>
            `;
        });

        listEl.innerHTML = html;
        
        // show clear button if something is selected
        if (this.selectedVoucher) {
            listEl.innerHTML += `
                <div class="text-center mt-4 mb-2">
                    <button class="text-xs font-medium text-red-500 hover:text-red-700 underline" onclick="voucherUI.clearSelection(event)">Bỏ chọn voucher định dùng</button>
                </div>
            `;
        }
    },

    selectVoucher(code) {
        const v = this.vouchers.find(x => x.code === code);
        if(!v) return;

        // check if eligible
        const minOrder = this._getMinOrder(v.condition);
        if (v.condition && this.cartTotal < minOrder) {
            // can't select
            return;
        }

        // toggle selection
        if (this.selectedVoucher && this.selectedVoucher.code === code) {
            this.selectedVoucher = null;
        } else {
            this.selectedVoucher = v;
        }

        this.renderVouchers();
    },

    clearSelection(e) {
        if(e) e.stopPropagation();
        this.selectedVoucher = null;
        this.renderVouchers();
    },

    applyManualCode() {
        const input = document.getElementById('manualVoucherCode');
        const code = input.value.trim().toUpperCase();
        const err = document.getElementById('manualVoucherError');
        
        if(!code) return;

        const v = this.vouchers.find(x => x.code.toUpperCase() === code);
        if(!v) {
            err.textContent = "Mã voucher không hợp lệ hoặc đã hết hạn!";
            err.classList.remove('hidden');
            return;
        }

        const minOrder = this._getMinOrder(v.condition);
        if (v.condition && this.cartTotal < minOrder) {
            err.textContent = `Chưa đủ điều kiện (Yêu cầu đơn tối thiểu ${minOrder.toLocaleString('vi-VN')}đ)`;
            err.classList.remove('hidden');
            return;
        }

        err.classList.add('hidden');
        input.value = '';
        this.selectedVoucher = v;
        this.renderVouchers();
    },

    confirmSelection() {
        if (this.selectedVoucher) {
            // save to local storage
            localStorage.setItem('appliedVoucher', JSON.stringify(this.selectedVoucher));
        } else {
            localStorage.removeItem('appliedVoucher');
        }

        if(this.onApplyCallback) {
            this.onApplyCallback(this.selectedVoucher);
        }

        this.closeModal();
    },

    getDiscountAmount() {
        if (!this.selectedVoucher) return 0;
        // verify eligibility again just in case total changed
        const minOrder = this._getMinOrder(this.selectedVoucher.condition);
        if (this.selectedVoucher.condition && this.cartTotal < minOrder) {
            this.selectedVoucher = null;
            localStorage.removeItem('appliedVoucher');
            return 0;
        }
        
        // calculate discount %
        const percent = parseFloat(this.selectedVoucher.value);
        if (isNaN(percent)) return 0;
        
        let discount = Math.floor(this.cartTotal * (percent / 100));
        
        // cap by max discount
        const maxDisc = this._getMaxDiscount(this.selectedVoucher.condition);
        if (discount > maxDisc) discount = maxDisc;

        return discount;
    }
};

document.addEventListener('DOMContentLoaded', () => {
    voucherUI.init();
});
