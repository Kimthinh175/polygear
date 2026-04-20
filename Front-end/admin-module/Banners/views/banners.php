<main class="main-content">
    <div class="main-content-inner">
        <div class="banner-container">
            <div class="banner-header">
                <div>
                    <h1 class="page-title" style="margin: 0;">Quản lý Banner</h1>
                    <p class="text-muted text-sm" style="margin-top: 0.25rem;">Điều chỉnh hình ảnh và quảng cáo trên
                        trang chủ</p>
                </div>
                <button class="btn btn-primary" onclick="openModal()">
                    <i class="fa-solid fa-plus"></i> Thêm Banner Mới
                </button>
            </div>

            <div class="banner-grid" id="bannerGrid">
                <!-- Banners will be loaded here -->
            </div>
        </div>
    </div>
</main>

<!-- Modal Thêm/Sửa -->
<div id="bannerModal" class="modal-overlay">
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-title" id="modalTitle">Thêm Banner</h3>
            <button type="button" class="btn-close" onclick="closeModal()">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <form id="bannerForm" style="display: contents;">
            <div class="modal-body">
                <input type="hidden" name="id" id="bannerId">

                <div class="form-group">
                    <label class="form-label">Hình ảnh banner</label>
                    <div class="image-upload-preview" onclick="document.getElementById('bannerImage').click()">
                        <img id="previewImg" src="" style="display: none;">
                        <div id="uploadPlaceholder" class="flex flex-col items-center text-slate-400">
                            <i class="fa-solid fa-cloud-arrow-up text-3xl mb-2"></i>
                            <span>Click để tải ảnh lên</span>
                        </div>
                    </div>
                    <input type="file" id="bannerImage" name="image" hidden onchange="previewFile()">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="form-group">
                        <label class="form-label">Tiêu đề (Nội bộ)</label>
                        <input type="text" name="title" class="form-control" placeholder="Ví dụ: Summer Sale 2026"
                            required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Vị trí hiển thị</label>
                        <select name="type" class="form-control">
                            <option value="main_slider">Slide chính (Hero)</option>
                            <option value="side_promo_top">Quảng cáo Phải (Trên)</option>
                            <option value="side_promo_bottom">Quảng cáo Phải (Dưới)</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Đường dẫn khi click (Link URL)</label>
                    <input type="text" name="link_url" class="form-control" placeholder="https:// ...">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="form-group">
                        <label class="form-label">Thứ tự hiển thị</label>
                        <input type="number" name="order_index" class="form-control" value="0">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Trạng thái</label>
                        <select name="status" class="form-control">
                            <option value="1">Hiển thị</option>
                            <option value="0">Ẩn</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeModal()" class="btn btn-outline">Huỷ</button>
                <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
            </div>
        </form>
    </div>
</div>

<script>
    const apiBase = '/Back-end/api/admin/banners';

    async function loadBanners() {
        const grid = document.getElementById('bannerGrid');
        if (!grid) return;

        grid.innerHTML = '<div class="col-span-full py-12 text-center text-slate-400"><i class="fa-solid fa-spinner fa-spin text-3xl mb-4"></i><p>Đang tải dữ liệu...</p></div>';

        try {
            const res = await fetch(apiBase, {  });
            if (!res.ok) throw new Error('API request failed');
            const json = await res.json();

            if (json.status === 'success') {
                if (!json.data || json.data.length === 0) {
                    grid.innerHTML = `
                    <div class="col-span-full py-20 flex flex-col items-center justify-center bg-white rounded-3xl border-2 border-dashed border-slate-200">
                        <i class="fa-solid fa-images text-5xl text-slate-100 mb-4"></i>
                        <p class="text-slate-400 font-medium">Chưa có banner nào. Nhấn <strong>"Thêm Banner Mới"</strong> ở góc trên phải để bắt đầu!</p>
                    </div>
                `;
                    return;
                }
                grid.innerHTML = json.data.map(b => `
            <div class="banner-card">
                <img src="${b.image_url}" class="banner-preview" alt="${b.title}">
                <div class="banner-info">
                    <h3 class="banner-title text-slate-900">${b.title}</h3>
                    <div class="banner-meta">
                        <span class="badge ${b.type === 'main_slider' ? 'badge-main' : 'badge-side'}">${b.type}</span>
                        <span class="badge ${b.status == 1 ? 'badge-active' : 'badge-hidden'}">${b.status == 1 ? 'Hiển thị' : 'Đang ẩn'}</span>
                    </div>
                    <div class="banner-actions">
                        <button class="btn-icon" onclick="editBanner(${JSON.stringify(b).replace(/"/g, '&quot;')})" title="Chỉnh sửa">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </button>
                        <button class="btn-icon btn-delete" onclick="deleteBanner(${b.id})" title="Xoá">
                            <i class="fa-solid fa-trash-can"></i>
                        </button>
                    </div>
                </div>
            </div>
        `).join('');
            }
        } catch (err) {
            console.error('Error loading banners:', err);
        }
    }

    function openModal(data = null) {
        const modal = document.getElementById('bannerModal');
        const form = document.getElementById('bannerForm');
        const title = document.getElementById('modalTitle');

        form.reset();
        document.getElementById('previewImg').style.display = 'none';
        document.getElementById('uploadPlaceholder').style.display = 'flex';
        document.getElementById('bannerId').value = '';

        if (data) {
            title.innerText = 'Chỉnh sửa Banner';
            document.getElementById('bannerId').value = data.id;
            form.title.value = data.title;
            form.type.value = data.type;
            form.link_url.value = data.link_url;
            form.order_index.value = data.order_index;
            form.status.value = data.status;

            if (data.image_url) {
                const preview = document.getElementById('previewImg');
                preview.src = data.image_url;
                preview.style.display = 'block';
                document.getElementById('uploadPlaceholder').style.display = 'none';
            }
        } else {
            title.innerText = 'Thêm Banner Mới';
        }

        modal.classList.add('show');
    }

    function closeModal() {
        document.getElementById('bannerModal').classList.remove('show');
    }

    function previewFile() {
        const preview = document.getElementById('previewImg');
        const file = document.getElementById('bannerImage').files[0];
        const reader = new FileReader();

        reader.onloadend = function () {
            preview.src = reader.result;
            preview.style.display = 'block';
            document.getElementById('uploadPlaceholder').style.display = 'none';
        }

        if (file) {
            reader.readAsDataURL(file);
        }
    }

    document.getElementById('bannerForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        const id = formData.get('id');
        const url = id ? apiBase + '/update' : apiBase;

        try {
            const res = await fetch(url, {
                method: 'POST',
                body: formData,
                
            });
            const json = await res.json();
            if (json.status === 'success') {
                alert(json.message);
                closeModal();
                loadBanners();
            } else {
                alert(json.message);
            }
        } catch (err) {
            console.error(err);
            alert('Có lỗi xảy ra, vui lòng thử lại!');
        }
    });

    async function deleteBanner(id) {
        if (!confirm('Bạn có chắc chắn muốn xoá banner này?')) return;

        try {
            const res = await fetch(apiBase, {
                method: 'DELETE',
                body: JSON.stringify({ id }),

                headers: { 'Content-Type': 'application/json' }
            });
            const json = await res.json();
            if (json.status === 'success') {
                loadBanners();
            } else {
                alert(json.message);
            }
        } catch (err) {
            console.error(err);
        }
    }

    function editBanner(data) {
        openModal(data);
    }

    // khởi tạo
    loadBanners();

    // đóng modal khi click ra ngoài
    window.onclick = function (event) {
        const modal = document.getElementById('bannerModal');
        if (event.target == modal) {
            closeModal();
        }
    }
</script>