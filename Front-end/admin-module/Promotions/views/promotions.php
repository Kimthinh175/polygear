<main class="main-content">
    <div class="main-content-inner">

        <!-- Page Header -->
        <div class="promo-header">
            <div class="promo-header-left">
                <div class="promo-header-icon">
                    <i class="fa-solid fa-fire-flame-curved"></i>
                </div>
                <div>
                    <h1 class="promo-title">Quản Lý Khuyến Mãi</h1>
                    <p class="promo-subtitle">Quản lý các chiến dịch giảm giá, tối đa 3 chiến dịch hoạt động cùng lúc.</p>
                </div>
            </div>
            <button class="promo-add-btn" onclick="openPromoModal()">
                <i class="fa-solid fa-plus"></i>
                <span>Tạo Chiến Dịch</span>
            </button>
        </div>

        <!-- Stats Strip -->
        <div class="promo-stats" id="promo-stats-strip">
            <div class="promo-stat-card">
                <div class="promo-stat-icon orange"><i class="fa-solid fa-fire"></i></div>
                <div><div class="promo-stat-val" id="stat-active-count">—</div><div class="promo-stat-label">Đang hoạt động</div></div>
            </div>
            <div class="promo-stat-card">
                <div class="promo-stat-icon blue"><i class="fa-solid fa-clock"></i></div>
                <div><div class="promo-stat-val" id="stat-upcoming-count">—</div><div class="promo-stat-label">Sắp diễn ra</div></div>
            </div>
            <div class="promo-stat-card">
                <div class="promo-stat-icon green"><i class="fa-solid fa-list-check"></i></div>
                <div><div class="promo-stat-val" id="stat-total-count">—</div><div class="promo-stat-label">Tổng chiến dịch</div></div>
            </div>
        </div>

        <!-- Table Card -->
        <div class="promo-card">
            <div class="promo-card-header">
                <div class="promo-card-title"><i class="fa-solid fa-rectangle-list"></i> Danh sách chiến dịch</div>
            </div>
            <div class="table-responsive">
                <table class="promo-table">
                    <thead>
                        <tr>
                            <th>Chiến Dịch</th>
                            <th>Mức Giảm</th>
                            <th>Thời Gian</th>
                            <th>SP Áp Dụng</th>
                            <th>Trạng Thái</th>
                            <th class="text-right">Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody id="promoTableBody">
                        <tr><td colspan="6" class="promo-loading-row"><i class="fa-solid fa-circle-notch fa-spin"></i> Đang tải dữ liệu...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</main>

<!-- Modal Setup Promotion (Create/Edit) -->
<div class="modal-overlay" id="promoModal">
    <div class="modal" style="max-width: 600px;">
        <div class="modal-header">
            <h3 class="modal-title" id="promoModalTitle">Thêm Chiến Dịch</h3>
            <button type="button" class="btn-close" onclick="closePromoModal()">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <div class="modal-body">
            <form id="promoForm">
                <input type="hidden" id="promoId">
                
                <div class="form-group mb-3">
                    <label class="form-label font-medium mb-1 block">Tên chiến dịch*</label>
                    <input type="text" id="promoName" class="form-control" required placeholder="Ví dụ: Siêu Sale Black Friday">
                </div>
                
                <div class="grid grid-cols-2 gap-4 mb-3" style="display:grid; grid-template-columns: 1fr 1fr; gap:1rem;">
                    <div class="form-group">
                        <label class="form-label font-medium mb-1 block">Bắt đầu*</label>
                        <input type="datetime-local" id="promoStart" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label font-medium mb-1 block">Kết thúc*</label>
                        <input type="datetime-local" id="promoEnd" class="form-control" required>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-3" style="display:grid; grid-template-columns: 1fr 1fr; gap:1rem;">
                    <div class="form-group">
                        <label class="form-label font-medium mb-1 block">Giảm phần trăm (%)</label>
                        <input type="number" id="promoPercent" class="form-control" min="1" max="100" placeholder="VD: 15">
                    </div>
                    <div class="form-group">
                        <label class="form-label font-medium mb-1 block">Hoặc Giảm tiền mặt (đ)</label>
                        <input type="number" id="promoAmount" class="form-control" min="1000" step="1000" placeholder="VD: 500000">
                    </div>
                </div>

            </form>
        </div>
        <div class="modal-footer" style="display: flex; justify-content: flex-end; gap: 0.5rem;">
            <button type="button" class="btn btn-outline" onclick="closePromoModal()">Hủy</button>
            <button type="button" class="btn btn-primary" onclick="savePromo()">Lưu Thông Tin</button>
        </div>
    </div>
</div>

<!-- Modal Quản Lý SP Dành Riêng Cho Promotion -->
<div class="modal-overlay" id="itemsModal">
    <div class="modal" style="max-width: 900px; width: 90vw; max-height: 90vh; display: flex; flex-direction: column;">
        <div class="modal-header shrink-0">
            <h3 class="modal-title">SP Áp Dụng: <span id="itemsPromoName" class="text-primary"></span></h3>
            <button type="button" class="btn-close" onclick="closeItemsModal()">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        
        <div class="modal-body overflow-y-auto p-4 flex-1" style="background:#f8f9fa;">
            <div style="display: flex; justify-content:space-between; margin-bottom: 1rem;">
                <input type="text" id="searchVariantInput" class="form-control" placeholder="Tìm tên SP, danh mục, SKU..." style="max-width:300px;" oninput="renderVariantList()">
                <button id="selectAllBtn" class="btn btn-outline" onclick="toggleSelectAll()">Chọn tất cả kết quả</button>
            </div>

            <div class="table-responsive" style="background:#fff; border-radius: 8px; border: 1px solid #ebebfc;">
                <table class="dashboard-table" style="margin-bottom:0;">
                    <thead style="position: sticky; top: 0; z-index: 10;">
                        <tr>
                             <th style="width: 40px; text-align: center;"> Chọn </th>
                             <th style="width: 60px; text-align: center;"> Ảnh </th>
                             <th>Mã SKU</th>
                             <th>Tên Phiên Bản</th>
                             <th>Giá Gốc</th>
                        </tr>
                    </thead>
                    <tbody id="variantsTableBody" style="max-height: 400px;">
                        <tr>
                             <td colspan="5" class="text-center py-4">Đang tải toàn bộ sản phẩm...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="modal-footer shrink-0 flex justify-between items-center" style="display: flex; justify-content: space-between;">
            <div class="text-muted"><span id="selectedCount" class="font-bold text-primary">0</span> SP được chọn</div>
            <div style="display: flex; gap: 0.5rem;">
                <button type="button" class="btn btn-outline" onclick="closeItemsModal()">Hủy</button>
                <button type="button" class="btn btn-primary" onclick="saveItems()">Lưu Danh Sách</button>
            </div>
        </div>
    </div>
</div>

<script src="https:// cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    let allPromotions = [];
    let allVariants = [];
    let currentPromoId = null;
    let selectedSkus = new Set();
    const apiUrl = 'https:// polygearid.ivi.vn/back-end/api/admin';

    document.addEventListener('DOMContentLoaded', () => {
        loadPromotions();
        // removed static variant pre-fetch to fetch dynamically instead

        // mutually exclusive inputs for discount
        const pPercent = document.getElementById('promoPercent');
        const pAmount = document.getElementById('promoAmount');

        pPercent.addEventListener('input', () => {
            pAmount.disabled = pPercent.value.length > 0;
        });

        pAmount.addEventListener('input', () => {
            pPercent.disabled = pAmount.value.length > 0;
        });
    });

    async function loadPromotions() {
        try {
            const res = await fetch(apiUrl + '/promotions');
            const result = await res.json();
            if (result.status === 'success') {
                allPromotions = result.data;
                renderPromotions();
            }
        } catch (e) { console.error('Error fetching promos:', e); }
    }

    function updateStats(promos) {
        const active = promos.filter(p => p.status === 'active' && p.time_status === 'Đang diễn ra').length;
        const upcoming = promos.filter(p => p.status === 'active' && p.time_status === 'Sắp diễn ra').length;
        const el1 = document.getElementById('stat-active-count');
        const el2 = document.getElementById('stat-upcoming-count');
        const el3 = document.getElementById('stat-total-count');
        if(el1) el1.textContent = active;
        if(el2) el2.textContent = upcoming;
        if(el3) el3.textContent = promos.length;
    }

    function renderPromotions() {
        const tbody = document.getElementById('promoTableBody');
        tbody.innerHTML = '';
        updateStats(allPromotions);
        if (allPromotions.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" class="promo-empty-row"><i class="fa-solid fa-tags" style="font-size:2rem;color:#cbd5e1;"></i><br>Chưa có chiến dịch nào</td></tr>';
            return;
        }

        allPromotions.forEach(p => {
            const tr = document.createElement('tr');

            let discountHtml = '<span class="promo-no-discount">—</span>';
            if (p.discount_percent) discountHtml = `<span class="promo-discount-badge pct">🔥 -${p.discount_percent}%</span>`;
            else if (p.discount_amount) discountHtml = `<span class="promo-discount-badge amt">💰 -${parseInt(p.discount_amount).toLocaleString()}đ</span>`;

            let timeCls = 'promo-badge-inactive';
            let timeIcon = 'fa-ban';
            if (p.status === 'active') {
                if (p.time_status === 'Đang diễn ra') { timeCls = 'promo-badge-active'; timeIcon = 'fa-circle-play'; }
                else if (p.time_status === 'Sắp diễn ra') { timeCls = 'promo-badge-upcoming'; timeIcon = 'fa-clock'; }
                else if (p.time_status === 'Đã kết thúc') { timeCls = 'promo-badge-ended'; timeIcon = 'fa-flag-checkered'; }
            }

            const startFmt = p.start_time ? p.start_time.replace('T', ' ').substring(0,16) : '—';
            const endFmt = p.end_time ? p.end_time.replace('T', ' ').substring(0,16) : '—';

            tr.innerHTML = `
                <td>
                    <div class="promo-name">${p.name}</div>
                    <div class="promo-id-tag">#${p.id}</div>
                </td>
                <td>${discountHtml}</td>
                <td>
                    <div class="promo-time-row"><i class="fa-regular fa-calendar-plus" style="color:#94a3b8"></i> ${startFmt}</div>
                    <div class="promo-time-row"><i class="fa-regular fa-calendar-xmark" style="color:#f87171"></i> ${endFmt}</div>
                </td>
                <td>
                    <button class="promo-items-btn" onclick="openItemsModal(${p.id}, '${p.name.replace(/'/g,"\\'")}')">
                        <i class="fa-solid fa-boxes-stacked"></i> ${p.items_count || 0} SP
                    </button>
                </td>
                <td>
                    <div class="promo-status-cell">
                        <span class="promo-time-badge ${timeCls}"><i class="fa-solid ${timeIcon}"></i> ${p.time_status || 'Tắt'}</span>
                        <label class="promo-toggle">
                            <input type="checkbox" ${p.status === 'active' ? 'checked' : ''} onchange="togglePromoStatus(${p.id}, this.checked)">
                            <span class="promo-toggle-slider"></span>
                        </label>
                    </div>
                </td>
                <td>
                    <div class="promo-actions">
                        <button class="promo-act-btn edit" onclick='editPromo(${JSON.stringify(p).replace(/'/g,"\\'")})'  title="Sửa"><i class="fa-solid fa-pen"></i></button>
                        <button class="promo-act-btn del" onclick="deletePromo(${p.id})" title="Xóa"><i class="fa-solid fa-trash"></i></button>
                    </div>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    // modal form info
    function openPromoModal() {
        console.log('Opening Promo Modal...');
        const modal = document.getElementById('promoModal');
        if (!modal) {
            console.error('Modal element #promoModal not found!');
            return;
        }
        document.getElementById('promoForm').reset();
        document.getElementById('promoId').value = '';
        document.getElementById('promoModalTitle').innerText = 'Thêm Chiến Dịch Mới';
        
        // reset disabled states
        document.getElementById('promoPercent').disabled = false;
        document.getElementById('promoAmount').disabled = false;
        
        modal.classList.add('show');
    }
    
    function closePromoModal() {
        document.getElementById('promoModal').classList.remove('show');
    }

    function editPromo(p) {
        document.getElementById('promoId').value = p.id;
        document.getElementById('promoName').value = p.name;
        document.getElementById('promoStart').value = p.start_time.replace(' ', 'T');
        document.getElementById('promoEnd').value = p.end_time.replace(' ', 'T');
        
        const pPercent = document.getElementById('promoPercent');
        const pAmount = document.getElementById('promoAmount');
        
        pPercent.value = p.discount_percent || '';
        pAmount.value = p.discount_amount || '';
        
        // set disabled states based on current values
        pAmount.disabled = pPercent.value.length > 0;
        pPercent.disabled = pAmount.value.length > 0;
        
        document.getElementById('promoModalTitle').innerText = 'Cập Nhật Chiến Dịch';
        document.getElementById('promoModal').classList.add('show');
    }

    async function savePromo() {
        const id = document.getElementById('promoId').value;
        const data = {
            id: id,
            name: document.getElementById('promoName').value,
            start_time: document.getElementById('promoStart').value.replace('T', ' ') + ':00',
            end_time: document.getElementById('promoEnd').value.replace('T', ' ') + ':00',
            discount_percent: document.getElementById('promoPercent').value,
            discount_amount: document.getElementById('promoAmount').value
        };

        if(!data.name || !data.start_time || !data.end_time) {
            Swal.fire('Thiếu thông tin', 'Vui lòng nhập Tên, Thời gian Bắt đầu và Kết thúc.', 'warning');
            return;
        }

        if(data.discount_percent && data.discount_amount) {
            Swal.fire('Lỗi Logic', 'Chỉ được chọn 1 phương thức giảm giá (Hoặc % Đặc Biệt, Hoặc Tiền Chẵn).', 'warning');
            return;
        }

        const endpoint = id ? '/promotions/update' : '/promotions';
        
        try {
            const res = await fetch(apiUrl + endpoint, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            const result = await res.json();
            if (result.status === 'success') {
                Swal.fire('Thành công', result.message, 'success');
                closePromoModal();
                loadPromotions();
            } else {
                Swal.fire('Lỗi', result.message, 'error');
            }
        } catch (e) {
            Swal.fire('Lỗi hệ thống', 'Có lỗi xảy ra', 'error');
        }
    }

    async function togglePromoStatus(id, isActive) {
        try {
            const res = await fetch(apiUrl + '/promotions/status', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: id, status: isActive ? 'active' : 'inactive' })
            });
            const result = await res.json();
            if (result.status === 'success') {
                loadPromotions();
            } else {
                Swal.fire('Lỗi', result.message, 'error');
                loadPromotions(); // reload to reset toggle
            }
        } catch (e) {
            Swal.fire('Lỗi', 'Lỗi hệ thống', 'error');
            loadPromotions();
        }
    }

    async function deletePromo(id) {
        if(!confirm('Bạn có chắc muốn xóa chiến dịch này? Các cài đặt SP cũng sẽ bị xóa khỏi chiến dịch theo.')) return;
        
        try {
            const res = await fetch(apiUrl + `/promotions?id=${id}`, {
                method: 'DELETE'
            });
            const result = await res.json();
            if (result.status === 'success') {
                loadPromotions();
            } else {
                Swal.fire('Lỗi', result.message, 'error');
            }
        } catch (e) {
            Swal.fire('Lỗi', 'Lỗi hệ thống', 'error');
        }
    }


    /* items modal logic */
    async function openItemsModal(id, name) {
        currentPromoId = id;
        document.getElementById('itemsPromoName').innerText = name;
        document.getElementById('itemsModal').classList.add('show');
        document.getElementById('variantsTableBody').innerHTML = '<tr><td colspan="5" class="text-center py-4">Đang tải...</td></tr>';
        selectedSkus.clear();
        updateCount();

        try {
            const vRes = await fetch(apiUrl + `/variants?for_promo=true&exclude_promo_id=${id}`);
            const vData = await vRes.json();
            if (vData.status === 'success') {
                allVariants = vData.data;
            }

            const r = await fetch(apiUrl + `/promotions/detail?id=${id}`);
            const data = await r.json();
            if(data.status === 'success' && data.data.items) {
                data.data.items.forEach(item => selectedSkus.add(item.sku));
            }
            renderVariantList();
        } catch (e) { console.error('fetch detl err', e); }
    }

    function closeItemsModal() {
        document.getElementById('itemsModal').classList.remove('show');
    }

    function toggleSkuSelection(sku, isChecked) {
        if (isChecked) selectedSkus.add(sku);
        else selectedSkus.delete(sku);
        updateCount();
    }

    function updateCount() {
        document.getElementById('selectedCount').innerText = selectedSkus.size;
    }

    let curFilteredSkus = [];
    function renderVariantList() {
        const rawInput = document.getElementById('searchVariantInput').value.toLowerCase().trim();
        const searchWords = rawInput.split(/\s+/).filter(w => w.length > 0);
        
        const tbody = document.getElementById('variantsTableBody');
        tbody.innerHTML = '';

        if(allVariants.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4">Không có SP nào trong hệ thống.</td></tr>';
            return;
        }

        const filtered = allVariants.filter(v => {
            if (searchWords.length === 0) return true;
            const targetText = `${v.sku} ${v.root_product} ${v.name || ''} ${v.category_name || ''}`.toLowerCase();
            return searchWords.every(word => targetText.includes(word));
        });

        curFilteredSkus = filtered.map(v => v.sku);
        updateSelectAllBtnText();

        if(filtered.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4">Không có kết quả tìm kiếm phù hợp.</td></tr>';
            return;
        }

        const MAX = 200; 
        const toRender = filtered.slice(0, MAX);

        toRender.forEach(v => {
            const tr = document.createElement('tr');
            const isChecked = selectedSkus.has(v.sku);
            
            tr.innerHTML = `
                <td style="text-align:center;">
                    <input type="checkbox" class="form-check-input item-check-box cursor-pointer" data-sku="${v.sku}" ${isChecked ? 'checked' : ''} onchange="toggleSkuSelection('${v.sku}', this.checked)" style="width:20px;height:20px;">
                </td>
                <td style="text-align:center;">
                    <img src="${v.main_image_url || 'https:// via.placeholder.com/40'}" style="width:40px;height:40px;object-fit:cover;border-radius:4px;border:1px solid #eee;">
                </td>
                <td class="font-bold text-dark text-sm" title="${v.sku}">
                    <div style="max-width:120px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">${v.sku}</div>
                </td>
                <td>
                    <div class="text-sm font-medium" style="max-width:320px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" title="${v.root_product} - ${v.name || ''}">
                        ${v.root_product} - ${v.name || ''}
                    </div>
                    <div class="text-xs text-muted">Kho: ${v.stock} | Đã Bán: ${v.sold || 0}</div>
                </td>
                <td class="text-primary font-bold">${parseInt(v.price).toLocaleString()}đ</td>
            `;
            tbody.appendChild(tr);
        });

        if (filtered.length > MAX) {
            tbody.insertAdjacentHTML('beforeend', `<tr><td colspan="5" class="text-center text-muted py-2 bg-slate-50 italic">Có quá nhiều kết quả (${filtered.length}), chỉ hiển thị top ${MAX}. Sử dụng ô tìm kiếm để lọc thêm.</td></tr>`);
        }
        updateCount();
    }

    function updateSelectAllBtnText() {
        const btn = document.getElementById('selectAllBtn');
        if (!btn || curFilteredSkus.length === 0) return;
        
        const allSelected = curFilteredSkus.every(sku => selectedSkus.has(sku));
        btn.innerText = allSelected ? 'Huỷ chọn tất cả' : 'Chọn tất cả kết quả';
        btn.classList.toggle('btn-primary', allSelected);
        btn.classList.toggle('btn-outline', !allSelected);
    }

    function toggleSelectAll() {
        if (curFilteredSkus.length === 0) return;
        
        const allSelected = curFilteredSkus.every(sku => selectedSkus.has(sku));
        
        if (allSelected) {
            // deselect all filtered
            curFilteredSkus.forEach(sku => selectedSkus.delete(sku));
        } else {
            // select all filtered
            curFilteredSkus.forEach(sku => selectedSkus.add(sku));
        }
        
        renderVariantList(); // re-render to update checkboxes and button text
        updateCount();
    }

    async function saveItems() {
        if(!currentPromoId) return;

        const skusArray = Array.from(selectedSkus);
        
        try {
            const res = await fetch(apiUrl + '/promotions/items', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ promotion_id: currentPromoId, skus: skusArray })
            });
            const result = await res.json();

            if (result.status === 'success') {
                Swal.fire('Thành công', result.message, 'success');
                closeItemsModal();
                loadPromotions();
            } else {
                Swal.fire('Cảnh Báo', result.message, 'error');
            }
        } catch (e) {
            console.error(e);
            Swal.fire('Lỗi', 'Có lỗi xử lý máy chủ', 'error');
        }
    }
</script>
<style>
/* promo page layout */
.promo-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:1.5rem; }
.promo-header-left { display:flex; align-items:center; gap:1rem; }
.promo-header-icon { width:48px; height:48px; border-radius:14px; background:linear-gradient(135deg,#f97316,#ef4444); display:flex; align-items:center; justify-content:center; color:#fff; font-size:1.3rem; box-shadow:0 4px 14px rgba(249,115,22,.35); flex-shrink:0; }
.promo-title { font-size:1.35rem; font-weight:800; color:#0f172a; letter-spacing:-.02em; margin:0; }
.promo-subtitle { font-size:0.82rem; color:#64748b; margin:.2rem 0 0; }
.promo-add-btn { display:inline-flex; align-items:center; gap:.5rem; background:linear-gradient(135deg,#4f46e5,#7c3aed); color:#fff; border:none; border-radius:10px; padding:.6rem 1.2rem; font-size:.875rem; font-weight:700; cursor:pointer; box-shadow:0 4px 14px rgba(79,70,229,.35); transition:transform .15s,box-shadow .15s; }
.promo-add-btn:hover { transform:translateY(-1px); box-shadow:0 6px 20px rgba(79,70,229,.45); }

/* stats */
.promo-stats { display:grid; grid-template-columns:repeat(3,1fr); gap:1rem; margin-bottom:1.5rem; }
.promo-stat-card { background:#fff; border:1.5px solid #f1f5f9; border-radius:14px; padding:1rem 1.25rem; display:flex; align-items:center; gap:.9rem; box-shadow:0 2px 8px rgba(0,0,0,.04); }
.promo-stat-icon { width:40px; height:40px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:1rem; flex-shrink:0; }
.promo-stat-icon.orange { background:#fff7ed; color:#f97316; }
.promo-stat-icon.blue   { background:#eff6ff; color:#3b82f6; }
.promo-stat-icon.green  { background:#f0fdf4; color:#22c55e; }
.promo-stat-val { font-size:1.5rem; font-weight:800; color:#0f172a; line-height:1; }
.promo-stat-label { font-size:.75rem; color:#94a3b8; margin-top:.15rem; font-weight:600; }

/* card */
.promo-card { background:#fff; border:1.5px solid #f1f5f9; border-radius:16px; overflow:hidden; box-shadow:0 2px 12px rgba(0,0,0,.05); margin-bottom:2rem; }
.promo-card-header { padding:1rem 1.5rem; border-bottom:1px solid #f1f5f9; display:flex; align-items:center; justify-content:space-between; }
.promo-card-title { font-size:.9rem; font-weight:700; color:#0f172a; display:flex; align-items:center; gap:.5rem; }
.promo-card-title i { color:#4f46e5; }

/* table */
.promo-table { width:100%; border-collapse:collapse; }
.promo-table thead th { background:#f8fafc; color:#64748b; font-size:.78rem; font-weight:700; text-transform:uppercase; letter-spacing:.05em; padding:.9rem 1.25rem; border-bottom:1px solid #f1f5f9; white-space:nowrap; }
.promo-table tbody tr { border-bottom:1px solid #f8fafc; transition:background .15s; }
.promo-table tbody tr:hover { background:#fafbff; }
.promo-table tbody td { padding:.9rem 1.25rem; vertical-align:middle; }
.promo-loading-row, .promo-empty-row { text-align:center; color:#94a3b8; padding:3rem 1rem !important; font-size:.95rem; }
.promo-empty-row { line-height:2.5; }

/* cell content */
.promo-name { font-weight:700; color:#0f172a; font-size:.9rem; }
.promo-id-tag { font-size:.72rem; color:#94a3b8; margin-top:.15rem; }
.promo-discount-badge { display:inline-flex; align-items:center; gap:.25rem; font-weight:700; font-size:.85rem; padding:.3rem .75rem; border-radius:9999px; }
.promo-discount-badge.pct { background:#fff7ed; color:#ea580c; }
.promo-discount-badge.amt { background:#fdf2f8; color:#9333ea; }
.promo-no-discount { color:#cbd5e1; }
.promo-time-row { font-size:.8rem; color:#475569; display:flex; align-items:center; gap:.35rem; margin:.15rem 0; }
.promo-items-btn { display:inline-flex; align-items:center; gap:.35rem; background:#f1f5f9; border:1px solid #e2e8f0; color:#475569; border-radius:8px; padding:.35rem .75rem; font-size:.8rem; font-weight:600; cursor:pointer; transition:all .15s; }
.promo-items-btn:hover { background:#4f46e5; border-color:#4f46e5; color:#fff; }
.promo-status-cell { display:flex; align-items:center; gap:.6rem; flex-wrap:wrap; }
.promo-time-badge { font-size:.72rem; font-weight:700; padding:.25rem .65rem; border-radius:9999px; display:inline-flex; align-items:center; gap:.3rem; white-space:nowrap; }
.promo-badge-active   { background:#dcfce7; color:#16a34a; }
.promo-badge-upcoming { background:#fef9c3; color:#b45309; }
.promo-badge-ended    { background:#fee2e2; color:#dc2626; }
.promo-badge-inactive { background:#f1f5f9; color:#94a3b8; }
.promo-actions { display:flex; gap:.35rem; justify-content:flex-end; }
.promo-act-btn { width:32px; height:32px; border-radius:8px; border:1.5px solid #e2e8f0; background:#fff; display:flex; align-items:center; justify-content:center; cursor:pointer; font-size:.8rem; transition:all .15s; }
.promo-act-btn.edit:hover { border-color:#4f46e5; color:#4f46e5; background:#eef2ff; }
.promo-act-btn.del:hover  { border-color:#ef4444; color:#ef4444; background:#fef2f2; }

/* custom toggle */
.promo-toggle { position:relative; display:inline-flex; align-items:center; cursor:pointer; }
.promo-toggle input { opacity:0; width:0; height:0; position:absolute; }
.promo-toggle-slider { width:38px; height:20px; background:#e2e8f0; border-radius:9999px; transition:background .2s; position:relative; flex-shrink:0; }
.promo-toggle-slider::after { content:''; position:absolute; top:2px; left:2px; width:16px; height:16px; background:#fff; border-radius:50%; transition:transform .2s; box-shadow:0 1px 3px rgba(0,0,0,.2); }
.promo-toggle input:checked + .promo-toggle-slider { background:#22c55e; }
.promo-toggle input:checked + .promo-toggle-slider::after { transform:translateX(18px); }

/* modals */
.modal-overlay#promoModal, .modal-overlay#itemsModal { position:fixed !important; inset:0; background:rgba(15,23,42,.5); display:none; align-items:center; justify-content:center; z-index:99999; backdrop-filter:blur(6px); padding:20px; }
.modal-overlay#promoModal.show, .modal-overlay#itemsModal.show { display:flex !important; }
.modal { background:#fff; border-radius:16px; box-shadow:0 20px 50px rgba(0,0,0,.2); width:100%; max-width:600px; overflow:hidden; animation:scaleUp .2s ease-out; }
@keyframes scaleUp { from { transform:scale(.95); opacity:0; } to { transform:scale(1); opacity:1; } }
.modal-header { padding:1.25rem 1.5rem; border-bottom:1px solid #f1f5f9; display:flex; justify-content:space-between; align-items:center; background:linear-gradient(135deg,#f8fafc,#fff); }
.modal-body { padding:1.5rem; }
.modal-footer { padding:1rem 1.5rem; border-top:1px solid #f1f5f9; background:#f8fafc; }
.modal-title { font-size:1.1rem; font-weight:800; color:#0f172a; }
.btn-close { background:none; border:none; width:32px; height:32px; border-radius:8px; cursor:pointer; color:#64748b; font-size:1.1rem; display:flex; align-items:center; justify-content:center; transition:background .15s; }
.btn-close:hover { background:#f1f5f9; }
</style>

    </div><!-- Close app-container -->
</body>
</html>
