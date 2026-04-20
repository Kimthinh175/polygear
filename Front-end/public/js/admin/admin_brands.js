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

let allBrands = [];
let filteredData = [];
const ITEMS_PER_PAGE = 12;
let currentPage = 1;

document.addEventListener('DOMContentLoaded', () => {
    fetchBrands();
    document.getElementById('brandLogo').addEventListener('change', function (e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function (e) {
                document.getElementById('logoPreview').src = e.target.result;
                document.getElementById('logoPreviewContainer').style.display = 'flex';
            }
            reader.readAsDataURL(file);
        }
    });
});

async function fetchBrands() {
    const grid = document.getElementById('brandGrid');
    grid.innerHTML = `<div style="grid-column:1/-1;text-align:center;padding:3rem;color:var(--text-muted);">
        <i class="fa-solid fa-spinner fa-spin" style="font-size:2rem;"></i>
    </div>`;
    try {
        const res = await fetch('/Back-end/api/admin/brands');
        const data = await res.json();
        if (data.status === 'success') {
            allBrands = data.data;
            filterAndRender();
        } else {
            showToast('Lỗi khi tải thương hiệu: ' + data.message, false);
        }
    } catch (e) {
        showToast('Lỗi kết nối máy chủ', false);
    }
}

function filterAndRender() {
    const search = document.getElementById('searchInput').value.toLowerCase().trim();
    filteredData = allBrands.filter(b => b.brand_name.toLowerCase().includes(search));
    document.getElementById('resultCount').textContent = filteredData.length;
    currentPage = 1;
    renderPage();
    renderPagination();
}

function renderPage() {
    const grid = document.getElementById('brandGrid');
    const noRes = document.getElementById('noResults');

    if (filteredData.length === 0) {
        grid.innerHTML = '';
        noRes.style.display = 'block';
        return;
    }
    noRes.style.display = 'none';

    const start = (currentPage - 1) * ITEMS_PER_PAGE;
    const pageData = filteredData.slice(start, start + ITEMS_PER_PAGE);

    grid.innerHTML = pageData.map(b => {
        const logoHtml = b.logo_url
            ? `<img src="/${b.logo_url}" alt="${b.brand_name}" style="max-width:120px;max-height:60px;object-fit:contain;">`
            : `<div style="width:60px;height:60px;border-radius:50%;background:linear-gradient(135deg,#e0e7ff,#c7d2fe);display:flex;align-items:center;justify-content:center;">
                <span style="font-size:1.5rem;font-weight:800;color:#6366f1;">${b.brand_name.charAt(0).toUpperCase()}</span>
               </div>`;
        return `
        <div class="brand-card" style="background:white;border-radius:16px;border:1px solid var(--border);padding:1.5rem;display:flex;flex-direction:column;align-items:center;gap:1rem;transition:box-shadow 0.2s,transform 0.2s;cursor:default;"
             onmouseenter="this.style.boxShadow='0 8px 30px rgba(0,0,0,0.1)';this.style.transform='translateY(-2px)'"
             onmouseleave="this.style.boxShadow='';this.style.transform=''">
            <!-- Logo -->
            <div style="height:70px;display:flex;align-items:center;justify-content:center;">
                ${logoHtml}
            </div>
            <!-- Name -->
            <div style="font-weight:700;font-size:0.95rem;color:var(--text-primary);text-align:center;line-height:1.3;">${b.brand_name}</div>
            <!-- ID badge -->
            <div style="font-size:0.72rem;color:var(--text-muted);background:var(--bg-secondary);padding:2px 10px;border-radius:20px;">ID #${b.id}</div>
            <!-- Actions -->
            <div style="display:flex;gap:0.5rem;width:100%;margin-top:auto;">
                <button class="btn btn-sm" style="flex:1;background:#e0f2fe;color:#0284c7;border:1px solid #bae6fd;justify-content:center;"
                    onclick='openEditModal(${JSON.stringify(b).replace(/'/g, "&#39;")})'>
                    <i class="fa-solid fa-pen"></i> Sửa
                </button>
                <button class="btn btn-danger-outline btn-sm" style="flex:1;justify-content:center;"
                    onclick="deleteBrand(${b.id}, '${b.brand_name.replace(/'/g, "\\'")}')">
                    <i class="fa-solid fa-trash"></i> Xóa
                </button>
            </div>
        </div>`;
    }).join('');
}

function renderPagination() {
    const totalPages = Math.ceil(filteredData.length / ITEMS_PER_PAGE);
    const container = document.getElementById('paginationContainer');
    const ul = document.getElementById('pagination');
    if (totalPages <= 1) { container.style.display = 'none'; return; }
    container.style.display = 'flex';
    let html = `<li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
        <a class="page-link" href="#" onclick="event.preventDefault();if(currentPage>1)goToPage(currentPage-1)">«</a></li>`;
    for (let i = 1; i <= totalPages; i++) {
        html += `<li class="page-item ${i === currentPage ? 'active' : ''}">
            <a class="page-link" href="#" onclick="event.preventDefault();goToPage(${i})">${i}</a></li>`;
    }
    html += `<li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
        <a class="page-link" href="#" onclick="event.preventDefault();if(currentPage<${totalPages})goToPage(currentPage+1)">»</a></li>`;
    ul.innerHTML = html;
}

function goToPage(p) {
    currentPage = p;
    renderPage();
    renderPagination();
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function openCreateModal() {
    document.getElementById('modalTitle').textContent = 'Tạo Thương hiệu Mới';
    document.getElementById('brandId').value = '';
    document.getElementById('brandName').value = '';
    document.getElementById('brandLogo').value = '';
    document.getElementById('logoPreviewContainer').style.display = 'none';
    document.getElementById('brandModal').style.display = 'flex';
}

function openEditModal(b) {
    document.getElementById('modalTitle').textContent = 'Sửa Thương hiệu';
    document.getElementById('brandId').value = b.id;
    document.getElementById('brandName').value = b.brand_name;
    document.getElementById('brandLogo').value = '';
    if (b.logo_url) {
        document.getElementById('logoPreview').src = '/' + b.logo_url;
        document.getElementById('logoPreviewContainer').style.display = 'flex';
    } else {
        document.getElementById('logoPreviewContainer').style.display = 'none';
    }
    document.getElementById('brandModal').style.display = 'flex';
}

function closeModal() {
    document.getElementById('brandModal').style.display = 'none';
}

async function saveBrand(e) {
    e.preventDefault();
    const id = document.getElementById('brandId').value;
    const name = document.getElementById('brandName').value.trim();
    if (!name) { showToast('Vui lòng điền tên thương hiệu', false); return; }

    const form = document.getElementById('brandForm');
    const formData = new FormData(form);

    const btn = document.getElementById('saveBtn');
    const btnText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Đang lưu...';

    try {
        const url = id ? '/Back-end/api/admin/brands/update' : '/Back-end/api/admin/brands';
        const res = await fetch(url, { method: 'POST', body: formData });
        const data = await res.json();
        if (data.status === 'success') {
            showToast(data.message, true);
            closeModal();
            fetchBrands();
        } else {
            showToast(data.message, false);
        }
    } catch (e) {
        showToast('Lỗi kết nối', false);
    } finally {
        btn.disabled = false;
        btn.innerHTML = btnText;
    }
}

async function deleteBrand(id, name) {
    if (!confirm(`Bạn có chắc muốn xóa thương hiệu "${name}"?`)) return;
    try {
        const res = await fetch('/Back-end/api/admin/brands', {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id })
        });
        const data = await res.json();
        if (data.status === 'success') {
            showToast(data.message, true);
            fetchBrands();
        } else {
            showToast(data.message, false);
        }
    } catch (e) {
        showToast('Lỗi kết nối', false);
    }
}
