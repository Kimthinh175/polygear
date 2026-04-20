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

let allCategories = [];
let filteredData = [];
const ITEMS_PER_PAGE = 10;
let currentPage = 1;

document.addEventListener('DOMContentLoaded', () => {
    fetchCategories();
});

async function fetchCategories() {
    try {
        const res = await fetch('/Back-end/api/category');
        const data = await res.json();
        if (data.status === 'success') {
            allCategories = data.data;
            filterAndRender();
        } else {
            showToast('Lỗi khi tải danh mục: ' + data.message, false);
        }
    } catch (e) {
        showToast('Lỗi kết nối máy chủ', false);
    }
}

function filterAndRender() {
    const search = document.getElementById('searchInput').value.toLowerCase().trim();
    
    filteredData = allCategories.filter(c => {
        return c.name.toLowerCase().includes(search) || (c.code && c.code.toLowerCase().includes(search));
    });

    document.getElementById('resultCount').textContent = filteredData.length;
    currentPage = 1;
    renderPage();
    renderPagination();
}

function renderPage() {
    const tbody = document.getElementById('categoryTableBody');
    const noRes = document.getElementById('noResults');

    if (filteredData.length === 0) {
        tbody.innerHTML = '';
        noRes.style.display = 'block';
        return;
    }
    noRes.style.display = 'none';

    const start = (currentPage - 1) * ITEMS_PER_PAGE;
    const end = start + ITEMS_PER_PAGE;
    const pageData = filteredData.slice(start, end);

    tbody.innerHTML = pageData.map(c => `
        <div class="card" style="margin:0;padding:0;border-radius:var(--radius-md);overflow:hidden;">
            <div style="display:flex;align-items:center;padding:0.7rem 1rem;gap:0;">
                <div style="width:50px;flex-shrink:0;">
                    <span style="font-size:0.75rem;font-weight:700;color:var(--text-muted);">#${c.id}</span>
                </div>
                <div style="flex:1;min-width:0;padding:0 0.75rem;">
                    <div style="font-weight:600;font-size:0.88rem;color:var(--text-primary);">${c.name}</div>
                </div>
                <div style="width:200px;flex-shrink:0;">
                    <span style="font-size:0.8rem;color:var(--text-secondary);font-family:monospace;">${c.code}</span>
                </div>
                <div style="width:150px;flex-shrink:0;display:flex;gap:0.4rem;justify-content:flex-end;">
                    <button class="btn btn-sm" style="background:#e0f2fe;color:#0284c7;border:1px solid #bae6fd;" onclick='openEditModal(${JSON.stringify(c).replace(/'/g, "&#39;")})'>
                        <i class="fa-solid fa-pen"></i> Sửa
                    </button>
                    <button class="btn btn-danger-outline btn-sm" onclick="deleteCategory(${c.id})">
                        <i class="fa-solid fa-trash"></i> Xóa
                    </button>
                </div>
            </div>
        </div>
    `).join('');
}

function renderPagination() {
    const totalPages = Math.ceil(filteredData.length / ITEMS_PER_PAGE);
    const container = document.getElementById('paginationContainer');
    const ul = document.getElementById('pagination');

    if (totalPages <= 1) {
        container.style.display = 'none';
        return;
    }
    container.style.display = 'flex';
    let html = '';
    
    html += `<li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="event.preventDefault(); if(currentPage>1) goToPage(currentPage-1)">«</a>
             </li>`;

    for (let i = 1; i <= totalPages; i++) {
        html += `<li class="page-item ${i === currentPage ? 'active' : ''}">
                    <a class="page-link" href="#" onclick="event.preventDefault(); goToPage(${i})">${i}</a>
                 </li>`;
    }

    html += `<li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="event.preventDefault(); if(currentPage<${totalPages}) goToPage(currentPage+1)">»</a>
             </li>`;
             
    ul.innerHTML = html;
}

function goToPage(p) {
    currentPage = p;
    renderPage();
    renderPagination();
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function openCreateModal() {
    document.getElementById('modalTitle').textContent = 'Tạo Danh mục Mới';
    document.getElementById('catId').value = '';
    document.getElementById('catName').value = '';
    document.getElementById('catCode').value = '';
    document.getElementById('categoryModal').style.display = 'flex';
}

function openEditModal(c) {
    document.getElementById('modalTitle').textContent = 'Sửa Danh mục';
    document.getElementById('catId').value = c.id;
    document.getElementById('catName').value = c.name;
    document.getElementById('catCode').value = c.code;
    document.getElementById('categoryModal').style.display = 'flex';
}

function closeModal() {
    document.getElementById('categoryModal').style.display = 'none';
}

async function saveCategory() {
    const id = document.getElementById('catId').value;
    const name = document.getElementById('catName').value.trim();
    const code = document.getElementById('catCode').value.trim();

    if (!name || !code) {
        showToast('Vui lòng điền đầy đủ tên và mã', false);
        return;
    }

    const payload = { name, code };
    let method = 'POST';
    if (id) {
        payload.id = id;
        method = 'PUT';
    }

    const btn = document.getElementById('saveBtn');
    btn.disabled = true;

    try {
        const res = await fetch('/Back-end/api/admin/category', {
            method: method,
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const data = await res.json();
        if (data.status === 'success') {
            showToast(data.message, true);
            closeModal();
            fetchCategories();
        } else {
            showToast(data.message, false);
        }
    } catch (e) {
        showToast('Lỗi kết nối', false);
    } finally {
        btn.disabled = false;
    }
}

async function deleteCategory(id) {
    if (!confirm('Bạn có chắc chắn muốn xóa danh mục này?')) return;
    
    try {
        const res = await fetch('/Back-end/api/admin/category', {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id })
        });
        const data = await res.json();
        if (data.status === 'success') {
            showToast(data.message, true);
            fetchCategories();
        } else {
            showToast(data.message, false);
        }
    } catch (e) {
        showToast('Lỗi kết nối', false);
    }
}
