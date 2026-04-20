// admin_list_product.js

// ────────────────────────────────────────────────────────────
// state
// ────────────────────────────────────────────────────────────
let allProducts  = [];
let allBrands    = [];
let allCategories = [];
let filteredData = [];
let deleteTargetId = null;
let newBrandId   = null;   // step-1 result: brand just created
let currentEditId = null;  // for editing product

const ITEMS_PER_PAGE = 10;
let currentPage = 1;

// ────────────────────────────────────────────────────────────
// init
// ────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    fetchAll();
});

async function fetchAll() {
    await Promise.all([fetchProducts(), fetchBrands(), fetchCategories()]);
}

// ────────────────────────────────────────────────────────────
// fetch data
// ────────────────────────────────────────────────────────────
async function fetchProducts() {
    const body = document.getElementById('productTableBody');
    body.innerHTML = `<div style="padding:3rem;text-align:center;color:var(--text-muted);">
        <i class="fa-solid fa-spinner fa-spin" style="font-size:1.8rem;display:block;margin-bottom:0.75rem;"></i>Đang tải...</div>`;

    try {
        const res  = await fetch('https:// polygearid.ivi.vn/back-end/api/admin/products', { credentials: 'include' });
        const data = await res.json();
        if (data.status === 'success') {
            allProducts = data.data;
            populateCategoryFilter();
            filterAndRender();
        }
    } catch (e) {
        body.innerHTML = `<div style="padding:2rem;text-align:center;color:var(--danger);">Không thể kết nối máy chủ.</div>`;
    }
}

async function fetchBrands() {
    try {
        const res  = await fetch('https:// polygearid.ivi.vn/back-end/api/admin/brands', { credentials: 'include' });
        const data = await res.json();
        if (data.status === 'success') {
            allBrands = data.data;
            populateBrandFilter();
            populateBrandSelect();
        }
    } catch (e) { console.error('fetchBrands', e); }
}

async function fetchCategories() {
    try {
        const res  = await fetch('https:// polygearid.ivi.vn/back-end/api/category', { credentials: 'include' });
        const data = await res.json();
        if (data.status === 'success') {
            allCategories = data.data;
            populateCategorySelect();
        }
    } catch (e) { console.error('fetchCategories', e); }
}

// ────────────────────────────────────────────────────────────
// populate filters
// ────────────────────────────────────────────────────────────
function populateCategoryFilter() {
    const sel = document.getElementById('categoryFilter');
    const cats = new Map();
    allProducts.forEach(p => { if (p.category_id && p.category_name) cats.set(p.category_id, p.category_name); });
    sel.innerHTML = '<option value="">-- Tất cả danh mục --</option>' +
        Array.from(cats.entries()).map(([id, n]) => `<option value="${id}">${n}</option>`).join('');
}

function populateBrandFilter() {
    const sel = document.getElementById('brandFilter');
    sel.innerHTML = '<option value="">-- Tất cả thương hiệu --</option>' +
        allBrands.map(b => `<option value="${b.id}">${b.brand_name}</option>`).join('');
}

function populateBrandSelect() {
    const sel = document.getElementById('cpBrand');
    sel.innerHTML = '<option value="">-- Không có / Chọn sau --</option>' +
        allBrands.map(b => `<option value="${b.id}">${b.brand_name}</option>`).join('') +
        '<option value="new">+ Tạo thương hiệu mới...</option>';
}

function populateCategorySelect() {
    const sel = document.getElementById('cpCategory');
    sel.innerHTML = '<option value="">-- Chọn danh mục --</option>' +
        allCategories.map(c => `<option value="${c.id}">${c.name}</option>`).join('') +
        '<option value="new">+ Tạo danh mục mới...</option>';
}

// ────────────────────────────────────────────────────────────
// filter & render list
// ────────────────────────────────────────────────────────────
function filterAndRender() {
    const cat    = document.getElementById('categoryFilter').value;
    const brand  = document.getElementById('brandFilter').value;
    const search = document.getElementById('searchInput').value.toLowerCase().trim();

    filteredData = allProducts.filter(p => {
        const matchCat   = !cat   || p.category_id == cat;
        const matchBrand = !brand || p.brand_id == brand;
        const matchSearch = !search || `${p.name} ${p.brand_name || ''}`.toLowerCase().includes(search);
        return matchCat && matchBrand && matchSearch;
    });

    currentPage = 1;
    document.getElementById('resultCount').textContent = filteredData.length;
    renderPage();
    renderPagination();
}

function renderPage() {
    const body   = document.getElementById('productTableBody');
    const noRes  = document.getElementById('noResults');
    const start  = (currentPage - 1) * ITEMS_PER_PAGE;
    const page   = filteredData.slice(start, start + ITEMS_PER_PAGE);

    if (filteredData.length === 0) {
        body.innerHTML = '';
        noRes.style.display = 'block';
        return;
    }
    noRes.style.display = 'none';

    body.innerHTML = page.map(p => {
        // brand logo + name
        const logoUrl = p.brand_logo
            ? `https:// polygearid.ivi.vn/${p.brand_logo}`
            : null;
        const brandCell = logoUrl
            ? `<div style="display:flex;align-items:center;gap:0.6rem;">
                 <img src="${logoUrl}" alt="" style="width:32px;height:32px;object-fit:contain;border-radius:5px;background:#f8f9fa;border:1px solid var(--border);padding:2px;">
                 <span style="font-size:0.82rem;font-weight:600;color:var(--text-primary);">${p.brand_name}</span>
               </div>`
            : `<span style="font-size:0.8rem;color:var(--text-muted);">—</span>`;

        // category chip
        const catChip = p.category_name
            ? `<span style="font-size:0.75rem;font-weight:500;padding:3px 10px;background:var(--bg-secondary);border-radius:20px;color:var(--text-secondary);border:1px solid var(--border);">${p.category_name}</span>`
            : `<span style="color:var(--text-muted);font-size:0.8rem;">—</span>`;

        // variant count badge
        const varCount = parseInt(p.variant_count) || 0;
        const varBadge = `<span style="display:inline-block;font-size:0.75rem;font-weight:700;padding:3px 12px;border-radius:20px;background:${varCount > 0 ? '#d1fae5' : '#f1f5f9'};color:${varCount > 0 ? '#065f46' : '#64748b'};">${varCount}</span>`;

        return `
        <div class="card" style="margin:0;padding:0;border-radius:var(--radius-md);overflow:hidden;">
            <div style="display:flex;align-items:center;padding:0.7rem 1rem;gap:0;">
                <!-- ID -->
                <div style="width:50px;flex-shrink:0;">
                    <span style="font-size:0.75rem;font-weight:700;color:var(--text-muted);">#${p.id}</span>
                </div>
                <!-- Brand -->
                <div style="width:180px;flex-shrink:0;">${brandCell}</div>
                <!-- Name -->
                <div style="flex:1;min-width:0;padding:0 0.75rem;">
                    <div style="font-weight:600;font-size:0.88rem;color:var(--text-primary);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" title="${p.name}">${p.name}</div>
                </div>
                <!-- Category -->
                <div style="width:150px;flex-shrink:0;">${catChip}</div>
                <!-- Variants -->
                <div style="width:110px;flex-shrink:0;text-align:center;">${varBadge}</div>
                <!-- Actions -->
                <div style="width:190px;flex-shrink:0;display:flex;gap:0.4rem;justify-content:flex-end;">
                    <a href="/admin/list_variant?product_id=${p.id}" class="btn btn-outline btn-sm" title="Xem biến thể">
                        <i class="fa-solid fa-layer-group"></i>
                    </a>
                    <a href="/admin/add_variant?product_id=${p.id}" class="btn btn-sm" style="background:#d1fae5;color:#065f46;border:1px solid #6ee7b7;" title="Thêm biến thể mới">
                        <i class="fa-solid fa-plus"></i>
                    </a>
                    <button class="btn btn-sm" style="background:#e0f2fe;color:#0284c7;border:1px solid #bae6fd;" title="Sửa sản phẩm" onclick='openEditModal(${JSON.stringify(p).replace(/'/g, "&#39;")})'>
                        <i class="fa-solid fa-pen"></i> Sửa
                    </button>
                    ${p.variant_count > 0 && p.active_variant_count == 0 
                        ? `<button class="btn btn-sm" style="background:#fef3c7;color:#92400e;border:1px solid #fde68a;" title="Bán lại" onclick="toggleProductStatus(${p.id}, 'start')">
                             <i class="fa-solid fa-play"></i> Bán lại
                           </button>`
                        : `<button class="btn btn-danger-outline btn-sm" title="Ngừng bán" onclick="toggleProductStatus(${p.id}, 'stop')" ${p.variant_count == 0 ? 'disabled style="opacity: 0.5;"' : ''}>
                             <i class="fa-solid fa-pause"></i> Ngừng bán
                           </button>`
                    }
                </div>
            </div>
        </div>`;
    }).join('');
}

function escapeName(str) {
    return (str || '').replace(/\\/g, '\\\\').replace(/'/g, "\\'");
}

// ────────────────────────────────────────────────────────────
// pagination
// ────────────────────────────────────────────────────────────
function renderPagination() {
    const container  = document.getElementById('paginationContainer');
    const ul         = document.getElementById('pagination');
    const totalPages = Math.ceil(filteredData.length / ITEMS_PER_PAGE);
    if (totalPages <= 1) { container.style.display = 'none'; return; }
    container.style.display = 'block';

    let html = '';
    if (currentPage > 1) html += `<li class="page-item"><a class="page-link" onclick="goToPage(${currentPage - 1})">‹</a></li>`;
    for (let i = 1; i <= totalPages; i++) {
        html += `<li class="page-item ${i === currentPage ? 'active' : ''}"><a class="page-link" onclick="goToPage(${i})">${i}</a></li>`;
    }
    if (currentPage < totalPages) html += `<li class="page-item"><a class="page-link" onclick="goToPage(${currentPage + 1})">›</a></li>`;
    ul.innerHTML = html;
}

function goToPage(page) {
    currentPage = page;
    renderPage();
    renderPagination();
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// ────────────────────────────────────────────────────────────
// create product modal
// ────────────────────────────────────────────────────────────
function openCreateModal() {
    currentEditId = null;
    newBrandId = null;
    document.getElementById('cpName').value = '';
    document.getElementById('cpCategory').value = '';
    document.getElementById('cpBrand').value = '';
    document.getElementById('cpNewCatFields').style.display = 'none';
    document.getElementById('cpNewBrandFields').style.display = 'none';
    document.getElementById('cpBrandLogoPreview').style.display = 'none';
    document.getElementById('cpBrandLogoPlaceholder').style.display = 'block';
    document.getElementById('cpBrandLogoInput').value = '';
    document.getElementById('cpNewBrandName').value = '';
    document.getElementById('cpNewCatName').value = '';
    document.getElementById('cpNewCatCode').value = '';
    document.getElementById('cpNewBrandMsg').style.display = 'none';
    document.getElementById('cpNewBrandBtnText').textContent = 'Tạo thương hiệu';
    document.getElementById('createProductModalTitle').textContent = 'Thêm Sản Phẩm Gốc Mới';
    document.getElementById('createProductModal').style.display = 'flex';
}

function openEditModal(p) {
    currentEditId = p.id;
    newBrandId = null;
    document.getElementById('cpName').value = p.name;
    document.getElementById('cpCategory').value = p.category_id || '';
    document.getElementById('cpBrand').value = p.brand_id || '';
    document.getElementById('cpNewCatFields').style.display = 'none';
    document.getElementById('cpNewBrandFields').style.display = 'none';
    document.getElementById('cpBrandLogoPreview').style.display = 'none';
    document.getElementById('cpBrandLogoPlaceholder').style.display = 'block';
    document.getElementById('createProductModalTitle').textContent = 'Sửa Thông Tin Sản Phẩm';
    document.getElementById('createProductModal').style.display = 'flex';
}

function closeCreateModal() {
    document.getElementById('createProductModal').style.display = 'none';
}

function handleCpCategoryChange() {
    const sel = document.getElementById('cpCategory').value;
    const fields = document.getElementById('cpNewCatFields');
    fields.style.display = sel === 'new' ? 'flex' : 'none';
}

function handleCpBrandChange() {
    const sel = document.getElementById('cpBrand').value;
    const box = document.getElementById('cpNewBrandFields');
    box.style.display = sel === 'new' ? 'flex' : 'none';
    newBrandId = null;
    if (sel !== 'new') {
        document.getElementById('cpNewBrandMsg').style.display = 'none';
    }
}

function handleBrandLogoPreview(input) {
    const file = input.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = e => {
        const prev = document.getElementById('cpBrandLogoPreview');
        const ph   = document.getElementById('cpBrandLogoPlaceholder');
        prev.src = e.target.result;
        prev.style.display = 'block';
        ph.style.display   = 'none';
    };
    reader.readAsDataURL(file);
}

// create brand
async function submitNewBrand() {
    const name  = document.getElementById('cpNewBrandName').value.trim();
    const fileInput = document.getElementById('cpBrandLogoInput');
    const msgEl = document.getElementById('cpNewBrandMsg');
    const btnText = document.getElementById('cpNewBrandBtnText');

    if (!name) { showModalMsg(msgEl, 'Vui lòng nhập tên thương hiệu!', false); return; }
    if (!fileInput.files.length) { showModalMsg(msgEl, 'Vui lòng chọn ảnh logo!', false); return; }

    btnText.textContent = 'Đang tạo...';

    const fd = new FormData();
    fd.append('brand_name', name);
    fd.append('logo', fileInput.files[0]);

    try {
        const res  = await fetch('https:// polygearid.ivi.vn/back-end/api/admin/brands', {
            method: 'POST', credentials: 'include', body: fd
        });
        const data = await res.json();
        if (data.status === 'success') {
            newBrandId = data.id;
            showModalMsg(msgEl, `✓ Đã tạo thương hiệu #${newBrandId}!`, true);
            btnText.textContent = '✓ Đã tạo';
            // refresh brands list & add to select
            await fetchBrands();
            const sel = document.getElementById('cpBrand');
            sel.value = newBrandId;
            handleCpBrandChange();
        } else {
            showModalMsg(msgEl, data.message || 'Lỗi!', false);
            btnText.textContent = 'Tạo thương hiệu';
        }
    } catch (e) {
        showModalMsg(msgEl, 'Lỗi kết nối!', false);
        btnText.textContent = 'Tạo thương hiệu';
    }
}

// create product
async function submitNewProduct() {
    const name     = document.getElementById('cpName').value.trim();
    const catSel   = document.getElementById('cpCategory');
    const catVal   = catSel.value;
    const brandSel = document.getElementById('cpBrand');
    const brandVal = brandSel.value;
    const btn      = document.getElementById('cpSubmitBtn');

    if (!name) { showToast('Vui lòng nhập tên sản phẩm!', false); document.getElementById('cpName').focus(); return; }
    if (!catVal || catVal === 'new') {
        if (catVal === 'new') {
            const catName = document.getElementById('cpNewCatName').value.trim();
            const catCode = document.getElementById('cpNewCatCode').value.trim();
            if (!catName || !catCode) { showToast('Vui lòng điền đầy đủ thông tin danh mục!', false); return; }
        } else {
            showToast('Vui lòng chọn danh mục!', false); return;
        }
    }
    if (brandVal === 'new' && !newBrandId) {
        showToast('Vui lòng tạo thương hiệu trước (bấm "Tạo thương hiệu")!', false); return;
    }

    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Đang lưu...';

    // build payload
    let categoryData = {};
    if (catVal === 'new') {
        categoryData = {
            name: document.getElementById('cpNewCatName').value.trim(),
            code: document.getElementById('cpNewCatCode').value.trim().toLowerCase().replace(/\s+/g, '-'),
            is_new: true
        };
    } else {
        categoryData = { id: catVal, name: catSel.options[catSel.selectedIndex].text, is_new: false };
    }

    const brand_id = brandVal === 'new' ? newBrandId : (brandVal || null);

    const payload = {
        id: currentEditId,
        name,
        brand_id,
        brand: brandVal === '' ? '' : (brandSel.options[brandSel.selectedIndex]?.text || ''),
        category_id: categoryData.id,
        category: categoryData,
        created_at: new Date().toISOString()
    };

    const method = currentEditId ? 'PUT' : 'POST';

    try {
        const res  = await fetch('https:// polygearid.ivi.vn/back-end/api/admin/products', {
            method: method, credentials: 'include',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const data = await res.json();
        if (data.status === 'success' || res.ok) {
            showToast(currentEditId ? '✔ Đã cập nhật sản phẩm!' : '✔ Đã tạo sản phẩm thành công!', true);
            closeCreateModal();
            await fetchProducts();
            await fetchCategories();
            populateCategorySelect();
        } else {
            showToast('Lỗi: ' + (data.message || 'Không thể tạo sản phẩm'), false);
        }
    } catch (e) {
        showToast('Lỗi kết nối máy chủ!', false);
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-floppy-disk"></i> Lưu Sản Phẩm';
    }
}

function showModalMsg(el, msg, ok) {
    el.style.display = 'block';
    el.style.color   = ok ? '#16a34a' : '#dc2626';
    el.textContent   = msg;
}

async function toggleProductStatus(id, action) {
    const confirmMsg = action === 'stop' 
        ? 'Bạn có chắc chắn muốn NGỪNG BÁN sản phẩm này (bao gồm tất cả biến thể)?' 
        : 'Bạn có chắc chắn muốn BÁN LẠI sản phẩm này?';
    
    if (!confirm(confirmMsg)) return;

    try {
        const res = await fetch('https:// polygearid.ivi.vn/back-end/api/products/toggle-status', {
            method: 'PUT',
            credentials: 'include',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id, action })
        });
        const data = await res.json();
        if (data.status === 'success') {
            showToast(data.message, true);
            await fetchProducts();
        } else {
            showToast('Lỗi: ' + (data.message || 'Không thể cập nhật trạng thái'), false);
        }
    } catch (e) {
        showToast('Lỗi kết nối!', false);
    }
}

// ────────────────────────────────────────────────────────────
// toast
// ────────────────────────────────────────────────────────────
function showToast(msg, ok = true) {
    const existing = document.querySelector('.lp-toast');
    if (existing) existing.remove();
    const el = document.createElement('div');
    el.className = 'lp-toast';
    el.style.cssText = `
        position:fixed;bottom:1.5rem;right:1.5rem;z-index:99999;
        background:${ok ? '#10b981' : '#ef4444'};color:white;
        padding:0.85rem 1.5rem;border-radius:10px;font-weight:600;font-size:0.88rem;
        box-shadow:0 8px 24px rgba(0,0,0,0.2);display:flex;align-items:center;gap:0.6rem;`;
    el.innerHTML = `<i class="fa-solid fa-${ok ? 'check-circle' : 'circle-exclamation'}"></i> ${msg}`;
    document.body.appendChild(el);
    setTimeout(() => el.remove(), 3500);
}
