
document.addEventListener('DOMContentLoaded', () => {
    fetchCategories();
});

async function fetchCategories() {
    try {
        const res  = await fetch('https:// polygearid.ivi.vn/back-end/api/category', { credentials: 'include' });
        const data = await res.json();

        if (data.status === 'success') {
            const select = document.getElementById('categoryId');
            if (!select) return;

            const defaultOpt = select.querySelector('option[value=""]');
            const newOpt     = select.querySelector('option[value="new"]');

            select.innerHTML = '';
            if (defaultOpt) select.appendChild(defaultOpt);

            data.data.forEach(cat => {
                const opt = document.createElement('option');
                opt.value       = cat.id;
                opt.textContent = cat.name;
                select.appendChild(opt);
            });

            if (newOpt) select.appendChild(newOpt);
        }
    } catch (e) {
        console.error('fetchCategories error:', e);
    }
}

function handleCategoryChange(sel) {
    const fields   = document.getElementById('newCategoryFields');
    const nameInp  = document.getElementById('newCategoryName');
    const codeInp  = document.getElementById('newCategoryCode');

    if (sel.value === 'new') {
        fields.style.display = 'flex';
        nameInp.required = true;
        codeInp.required = true;
    } else {
        fields.style.display = 'none';
        nameInp.required = false;
        codeInp.required = false;
        nameInp.value = '';
        codeInp.value = '';
    }

    if (typeof updatePreview === 'function') updatePreview();
}

document.getElementById('productForm').addEventListener('submit', async function (e) {
    e.preventDefault();

    const submitBtns = [document.getElementById('submitBtn'), document.getElementById('submitBtn2')];

    // validate
    const name = document.getElementById('productName').value.trim();
    if (!name) {
        showToast('Vui lòng nhập tên sản phẩm!', false);
        document.getElementById('productName').focus();
        return;
    }

    const categorySelect = document.getElementById('categoryId');
    let categoryData = { id: null, name: null, code: null, is_new: false };

    if (categorySelect.value === 'new') {
        const catName = document.getElementById('newCategoryName').value.trim();
        const catCode = document.getElementById('newCategoryCode').value.trim().toLowerCase().replace(/\s+/g, '_');
        if (!catName || !catCode) {
            showToast('Vui lòng điền đầy đủ thông tin danh mục mới!', false);
            return;
        }
        categoryData = { name: catName, code: catCode, is_new: true };
    } else if (categorySelect.value !== '') {
        categoryData = {
            id: categorySelect.value,
            name: categorySelect.options[categorySelect.selectedIndex].text,
            is_new: false
        };
    } else {
        showToast('Vui lòng chọn danh mục!', false);
        categorySelect.focus();
        return;
    }

    // loading state
    submitBtns.forEach(b => { if (b) { b.disabled = true; b.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Đang lưu...'; }});

    const payload = {
        name,
        brand: document.getElementById('productBrand').value.trim(),
        category: categoryData,
        created_at: new Date().toISOString(),
        deleted_at: ''
    };

    try {
        const res  = await fetch('https:// polygearid.ivi.vn/back-end/api/products', {
            method: 'POST',
            credentials: 'include',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const data = await res.json();

        if (data.status === 'success' || res.ok) {
            showToast('✔ Đã lưu sản phẩm gốc thành công!', true);
            // reset form
            document.getElementById('productForm').reset();
            document.getElementById('newCategoryFields').style.display = 'none';
            if (typeof updatePreview === 'function') updatePreview();
        } else {
            throw new Error(data.message || 'Lỗi từ server');
        }
    } catch (err) {
        console.error('Submit error:', err);
        showToast('Lỗi: ' + err.message, false);
    } finally {
        submitBtns.forEach(b => {
            if (b) {
                b.disabled = false;
                b.innerHTML = b.id === 'submitBtn2'
                    ? '<i class="fa-solid fa-floppy-disk"></i> Lưu Sản Phẩm Gốc'
                    : '<i class="fa-solid fa-floppy-disk"></i> Lưu Sản Phẩm';
            }
        });
    }
});
