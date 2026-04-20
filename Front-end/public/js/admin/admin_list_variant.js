// admin_list_variant.js


// danh sách variants thật
let realVariants = [];

// phân trang
const itemsPerPage = 5;
let currentPage = 1;
let filteredData = [];

// quản lý bulk action
let selectedIds = new Set();

// format tiền tệ
function formatCurrency(amount) {
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(amount);
}

// hàm khởi tạo
document.addEventListener('DOMContentLoaded', () => {
    // đọc product_id từ url param (từ trang list_product)
    const urlParams = new URLSearchParams(window.location.search);
    const productIdParam = urlParams.get('product_id');
    if (productIdParam) {
        window._filterProductId = parseInt(productIdParam);
        showProductFilterBanner(productIdParam);
    }

    fetchVariants();
});

async function fetchVariants() {
    try {
        const res = await fetch('https:// polygearid.ivi.vn/back-end/api/admin/variants', { credentials: 'include' });
        const data = await res.json();

        if (data.status === 'success') {
            realVariants = data.data;

            // dynamically populate category filter
            const catFilter = document.getElementById('categoryFilter');
            if (catFilter) {
                const cats = new Map();
                realVariants.forEach(v => {
                    if (v.category_id && v.category_name) {
                        cats.set(v.category_id, v.category_name);
                    }
                });
                catFilter.innerHTML = '<option value="">-- Tất cả danh mục --</option>' +
                    Array.from(cats.entries()).map(([id, name]) => `<option value="${id}">${name}</option>`).join('');
            }

            filterAndRender();
        } else {
            console.error('Lỗi khi tải danh sách:', data.message);
            alert('Không thể tải danh sách biến thể!');
        }
    } catch (error) {
        console.error('Lỗi mạng:', error);
    }
}

function filterAndRender() {
    const categoryFilter = document.getElementById('categoryFilter').value;
    const stockFilter = document.getElementById('stockFilter').value;
    const searchInput = document.getElementById('searchInput').value.toLowerCase().trim();
    const productId = window._filterProductId || null;

    // lọc dữ liệu
    filteredData = realVariants.filter(v => {
        const matchCategory = categoryFilter === "" || v.category_id == categoryFilter;
        const matchProduct = !productId || v.product_id == productId;

        // lọc theo tồn kho
        let matchStock = true;
        const stock = parseInt(v.stock) || 0;
        const minStock = parseInt(v.min_stock) || 0;
        if (stockFilter === "out") {
            matchStock = stock <= 0;
        } else if (stockFilter === "low") {
            matchStock = stock > 0 && stock <= minStock;
        } else if (stockFilter === "available") {
            matchStock = stock > minStock;
        }

        // tìm kiếm theo nhiều trường
        const searchableStr = `${v.sku} ${v.name} ${v.root_product}`.toLowerCase();
        const matchSearch = searchInput === "" || searchableStr.includes(searchInput);

        return matchCategory && matchProduct && matchStock && matchSearch;
    });

    // reset về trang 1
    currentPage = 1;

    renderTable();
    renderPagination();
}

function renderTable() {
    const container = document.getElementById('variantTableBody');
    const noResults = document.getElementById('noResults');
    container.innerHTML = '';

    selectedIds.clear();
    updateBulkActionBar();

    const countEl = document.getElementById('resultCount');
    if (countEl) countEl.textContent = filteredData.length;

    if (filteredData.length === 0) {
        noResults.style.display = 'block';
        document.getElementById('paginationContainer').style.display = 'none';
        return;
    }

    noResults.style.display = 'none';
    document.getElementById('paginationContainer').style.display = 'flex';

    const startIndex = (currentPage - 1) * itemsPerPage;
    const endIndex = startIndex + itemsPerPage;
    const currentItems = filteredData.slice(startIndex, endIndex);

    currentItems.forEach(v => {
        const imgUrl = v.main_image_url || v.main_image || v.image_url || v.thumbnail || 'https:// placehold.co/56x56?text=?';
        const isEmpty = v.stock <= 0;
        const isLow = !isEmpty && v.min_stock > 0 && v.stock <= v.min_stock;
        const stockColor = isEmpty ? '#ef4444' : isLow ? '#f59e0b' : '#10b981';
        const isActive = v.delete_at == null;

        const card = document.createElement('div');
        card.className = 'variant-row-card';
        card.innerHTML = `
            <!-- Checkbox -->
            <div class="vrc-col vrc-check">
                <input type="checkbox" value="${v.id}" onchange="toggleRowSelection(this)"
                    style="width:16px;height:16px;cursor:pointer;accent-color:var(--primary);">
            </div>

            <!-- Image -->
            <div class="vrc-col vrc-img">
                <div style="position:relative;display:inline-block;">
                    <img src="${imgUrl}" alt=""
                        style="width:52px;height:52px;object-fit:cover;border-radius:8px;border:1px solid var(--border);"
                        onerror="this.src='https:// placehold.co/52x52?text=?'">
                    <span style="position:absolute;bottom:-4px;right:-4px;width:12px;height:12px;border-radius:50%;
                        background:${isActive ? '#10b981' : '#ef4444'};border:2px solid white;"
                        title="${isActive ? 'Hoạt động' : 'Ngừng bán'}"></span>
                </div>
            </div>

            <!-- Name + Root Product -->
            <div class="vrc-col vrc-name">
                <div class="vrc-name-title" title="${v.name}">${v.name}</div>
                <div class="vrc-name-sub" title="${v.root_product || ''}">
                    <i class="fa-solid fa-box" style="font-size:0.65rem;"></i> ${v.root_product || 'N/A'}
                </div>
            </div>

            <!-- SKU -->
            <div class="vrc-col vrc-sku">
                <code>
                    <span title="${v.sku}">${v.sku}</span>
                    <button type="button" class="copy-btn" onclick="copyToClipboard('${v.sku}')" title="Copy SKU">
                        <i class="fa-regular fa-copy"></i>
                    </button>
                </code>
            </div>

            <!-- Category -->
            <div class="vrc-col vrc-cat">
                <span class="vrc-cat-badge" title="${v.category_name || ''}">${v.category_name || 'N/A'}</span>
            </div>

            <!-- Price -->
            <div class="vrc-col vrc-price">
                <div class="vrc-price-val">${formatCurrency(v.price)}</div>
            </div>

            <!-- Stock (click to edit) -->
            <div class="vrc-col vrc-stock">
                <div class="vrc-stock-inner"
                    onclick="openStockModal(${v.id}, ${v.stock}, ${v.min_stock || 0})"
                    title="Bấm để chỉnh tồn kho">
                    <span class="vrc-stock-qty" style="color:${stockColor};">${isEmpty ? 'Hết hàng' : v.stock}</span>
                    <span class="vrc-stock-min">min: ${v.min_stock || 0}</span>
                </div>
            </div>

            <!-- Actions -->
            <div class="vrc-col vrc-actions">
                <button type="button" onclick="openEditForm(${v.id})" class="vrc-action-btn vrc-action-edit" title="Sửa biến thể">
                    <i class="fa-solid fa-pen-to-square"></i> Sửa
                </button>
                ${isActive
                ? `<button type="button" onclick="toggleVariantStatus(${v.id}, 'stop')" class="vrc-action-btn vrc-action-stop" title="Ngừng bán">
                           <i class="fa-solid fa-pause"></i> Ngừng bán
                       </button>`
                : `<button type="button" onclick="toggleVariantStatus(${v.id}, 'start')" class="vrc-action-btn vrc-action-start" title="Bán lại">
                           <i class="fa-solid fa-play"></i> Bán lại
                       </button>`
            }
            </div>
        `;
        container.appendChild(card);
    });
}


function renderPagination() {

    const totalPages = Math.ceil(filteredData.length / itemsPerPage);
    const paginationEl = document.getElementById('pagination');
    paginationEl.innerHTML = '';

    if (totalPages <= 1) {
        document.getElementById('paginationContainer').style.display = 'none';
        return;
    }

    // nút prev
    const prevLi = document.createElement('li');
    prevLi.className = `page-item ${currentPage === 1 ? 'disabled' : ''}`;
    prevLi.innerHTML = `<a class="page-link" onclick="changePage(${currentPage - 1})"><i class="fa-solid fa-chevron-left"></i></a>`;
    paginationEl.appendChild(prevLi);

    // nút số
    for (let i = 1; i <= totalPages; i++) {
        const li = document.createElement('li');
        li.className = `page-item ${currentPage === i ? 'active' : ''}`;
        li.innerHTML = `<a class="page-link" onclick="changePage(${i})">${i}</a>`;
        paginationEl.appendChild(li);
    }

    // nút next
    const nextLi = document.createElement('li');
    nextLi.className = `page-item ${currentPage === totalPages ? 'disabled' : ''}`;
    nextLi.innerHTML = `<a class="page-link" onclick="changePage(${currentPage + 1})"><i class="fa-solid fa-chevron-right"></i></a>`;
    paginationEl.appendChild(nextLi);
}

function changePage(page) {
    const totalPages = Math.ceil(filteredData.length / itemsPerPage);
    if (page < 1 || page > totalPages) return;
    currentPage = page;
    renderTable();
    renderPagination();
}

// stock modal (chỉnh stock + min_stock)
function openStockModal(id, stock, minStock) {
    // xóa modal cũ nếu có
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

            <!-- Stock -->
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

            <!-- Min Stock -->
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
                <div style="font-size:0.7rem;color:#94a3b8;margin-top:0.35rem;">Khi tồn ≤ ngưỡng này sẽ hiển thị cảnh báo</div>
            </div>

            <!-- Buttons -->
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
            // update cache
            const v = realVariants.find(x => x.id == id);
            if (v) { v.stock = stockVal; v.min_stock = minVal; }
            closeStockModal();
            // re-render để cập nhật tồn kho + thanh bar
            renderTable();
            renderPagination();
        } else {
            throw new Error(data.message || 'Lỗi không xác định');
        }
    } catch (e) {
        btn.disabled = false;
        btn.innerHTML = `<i class="fa-solid fa-triangle-exclamation"></i> Lỗi — Thử lại`;
        btn.style.background = '#ef4444';
        console.error('saveStockModal:', e);
    }
}

async function toggleVariantStatus(id, action) {
    const confirmMsg = action === 'stop'
        ? 'Bạn có chắc chắn muốn NGỪNG BÁN biến thể này?'
        : 'Bạn có chắc chắn muốn BÁN LẠI biến thể này?';

    if (!confirm(confirmMsg)) return;

    try {
        const res = await fetch('https:// polygearid.ivi.vn/back-end/api/admin/variants/toggle-status', {
            method: 'PUT',
            credentials: 'include',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id, action })
        });
        const data = await res.json();
        if (data.status === 'success') {
            alert(data.message);
            fetchVariants();
        } else {
            alert('Lỗi: ' + (data.message || 'Không thể cập nhật trạng thái'));
        }
    } catch (e) {
        alert('Lỗi kết nối!');
    }
}

function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        // có thể thay bằng toast ui, tạm thời bỏ alert để tránh phiền
        console.log('Đã sao chép: ' + text);
    }).catch(err => {
        console.error('Lỗi khi sao chép:', err);
    });
}

function toggleSelectAll(checkbox) {
    const rowCheckboxes = document.querySelectorAll('#variantTableBody input[type="checkbox"]');
    rowCheckboxes.forEach(cb => {
        cb.checked = checkbox.checked;
        if (checkbox.checked) {
            selectedIds.add(cb.value);
        } else {
            selectedIds.delete(cb.value);
        }
    });
    updateBulkActionBar();
}

function toggleRowSelection(checkbox) {
    if (checkbox.checked) {
        selectedIds.add(checkbox.value);
    } else {
        selectedIds.delete(checkbox.value);
    }

    const rowCheckboxes = document.querySelectorAll('#variantTableBody input[type="checkbox"]');
    const selectAllCb = document.getElementById('selectAllCheckbox');
    if (selectAllCb) {
        selectAllCb.checked = rowCheckboxes.length > 0 && selectedIds.size === rowCheckboxes.length;
    }

    updateBulkActionBar();
}

function updateBulkActionBar() {
    const container = document.getElementById('bulkActionsContainer');
    const countSpan = document.getElementById('selectedCount');
    if (!container || !countSpan) return;

    countSpan.textContent = selectedIds.size;

    if (selectedIds.size > 0) {
        container.style.display = 'flex';
    } else {
        container.style.display = 'none';
    }
}



async function bulkStatusUpdate(newStatus) {
    if (selectedIds.size === 0) return;
    if (!confirm('Bạn có chắc chắn muốn gỡ/ẩn hàng loạt ' + selectedIds.size + ' biến thể đã chọn?')) {
        return;
    }

    let idsArray = Array.from(selectedIds);
    // in a real app, send one bulk api request.
    for (let id of idsArray) {
        try {
            await fetch('https:// polygearid.ivi.vn/back-end/api/admin/variants/toggle-status', {
                method: 'PUT',
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id, action: 'stop' })
            });
        } catch (e) { console.error('Lỗi cập nhật ID ' + id, e); }
    }

    alert('Đã cập nhật trạng thái các biến thể được chọn.');
    fetchVariants();
}

// ── banner lọc theo sản phẩm gốc ─────────────────────────────────
function showProductFilterBanner(productId) {
    // xóa banner cũ nếu có
    document.getElementById('productFilterBanner')?.remove();

    // tìm tên sản phẩm từ data (sau khi fetch xong) — hoặc chỉ hiện id
    const getProductName = () => {
        const v = realVariants.find(x => x.product_id == productId);
        return v ? v.root_product : `#${productId}`;
    };

    const banner = document.createElement('div');
    banner.id = 'productFilterBanner';
    banner.style.cssText = `
        display:flex; align-items:center; justify-content:space-between;
        background:#fef3c7; border:1px solid #fde68a; border-radius:10px;
        padding:0.65rem 1.1rem; margin-bottom:1rem; gap:0.75rem;
        font-size:0.85rem; color:#92400e;
    `;
    banner.innerHTML = `
        <span><i class="fa-solid fa-filter" style="margin-right:0.4rem;"></i>
        Đang lọc biến thể của: <strong id="bannerProductName">Sản phẩm #${productId}</strong></span>
        <div style="display:flex;gap:0.5rem;">
            <a href="/admin/list_product" style="font-size:0.78rem;font-weight:600;color:#92400e;text-decoration:none;border:1px solid #f59e0b;border-radius:6px;padding:0.2rem 0.65rem;">
                <i class="fa-solid fa-arrow-left"></i> Sản phẩm gốc
            </a>
            <button onclick="clearProductFilter()" style="font-size:0.78rem;font-weight:600;color:#6b7280;background:white;border:1px solid #e2e8f0;border-radius:6px;padding:0.2rem 0.65rem;cursor:pointer;">
                Xem tất cả
            </button>
        </div>
    `;

    // chèn trước varianttablebody header
    const refEl = document.querySelector('.variant-row-card')?.parentElement
        || document.getElementById('variantTableBody');
    if (refEl?.parentElement) {
        refEl.parentElement.insertBefore(banner, refEl);
    } else {
        document.querySelector('.main-content')?.prepend(banner);
    }

    // cập nhật tên sau khi fetch xong
    const updateName = setInterval(() => {
        const name = getProductName();
        const el = document.getElementById('bannerProductName');
        if (el && name !== `#${productId}`) {
            el.textContent = name;
            clearInterval(updateName);
        }
    }, 300);
}

function clearProductFilter() {
    window._filterProductId = null;
    document.getElementById('productFilterBanner')?.remove();
    // xóa param khỏi url
    const url = new URL(window.location);
    url.searchParams.delete('product_id');
    window.history.replaceState({}, '', url);
    filterAndRender();
}
