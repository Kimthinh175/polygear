<main class="main-content">
    <div id="listBrandContainer">

        <!-- Page Header -->
        <div class="page-header" style="margin-bottom: 1.5rem;">
            <div>
                <h1 class="page-title">Quản lý Thương hiệu</h1>
                <p class="text-muted text-sm mt-4">Quản lý các thương hiệu sản phẩm trong hệ thống.</p>
            </div>
            <div class="flex gap-2">
                <button class="btn btn-primary" onclick="openCreateModal()">
                    <i class="fa-solid fa-plus"></i> Tạo Thương hiệu Mới
                </button>
            </div>
        </div>

        <!-- Search Bar -->
        <div class="card" style="padding: 1rem 1.25rem; margin-bottom: 1.5rem;">
            <div style="display:flex; gap: 1rem; flex-wrap: wrap; align-items: center;">
                <div class="search-box" style="flex:1; min-width:220px;">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" id="searchInput" placeholder="Tìm theo tên thương hiệu..." onkeyup="filterAndRender()">
                </div>
                <div style="font-size:0.8rem; color:var(--text-muted); white-space:nowrap;">
                    Hiển thị <strong id="resultCount">0</strong> thương hiệu
                </div>
            </div>
        </div>

        <!-- Brand Grid -->
        <div id="brandGrid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:1rem;margin-bottom:1.5rem;"></div>

        <!-- No Results -->
        <div id="noResults" style="display:none;padding:4rem 2rem;text-align:center;color:var(--text-muted);">
            <i class="fa-solid fa-award" style="font-size:3rem;display:block;margin-bottom:1rem;opacity:0.3;"></i>
            <p style="font-weight:500;">Không tìm thấy thương hiệu nào phù hợp.</p>
        </div>

        <!-- Pagination -->
        <nav id="paginationContainer" style="display:none;">
            <ul class="pagination" id="pagination"></ul>
        </nav>
    </div>

    <!-- ══════════════════════════════════════════════
         MODAL: Tạo / Sửa Thương hiệu
    ══════════════════════════════════════════════ -->
    <div id="brandModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.55);z-index:9000;align-items:center;justify-content:center;padding:1rem;">
        <div style="background:white;border-radius:16px;width:100%;max-width:480px;box-shadow:0 25px 80px rgba(0,0,0,0.25);overflow:hidden;max-height:92vh;display:flex;flex-direction:column;">

            <!-- Modal Header -->
            <div style="padding:1.25rem 1.5rem;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;flex-shrink:0;">
                <div style="font-weight:700;font-size:1.05rem;" id="modalTitle">Tạo Thương hiệu Mới</div>
                <button onclick="closeModal()" style="border:none;background:none;cursor:pointer;font-size:1.2rem;color:var(--text-muted);padding:4px 8px;border-radius:6px;" title="Đóng">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <!-- Modal Form -->
            <form id="brandForm" onsubmit="saveBrand(event)" style="display:flex;flex-direction:column;flex:1;overflow:hidden;">
                <div style="padding:1.5rem;overflow-y:auto;flex:1;display:flex;flex-direction:column;gap:1.25rem;">
                    <input type="hidden" id="brandId" name="id">

                    <!-- Brand Name -->
                    <div class="form-group" style="margin:0;">
                        <label class="form-label">Tên thương hiệu <span style="color:red">*</span></label>
                        <input type="text" id="brandName" name="brand_name" class="form-control" placeholder="VD: Logitech" required>
                    </div>

                    <!-- Logo Upload -->
                    <div class="form-group" style="margin:0;">
                        <label class="form-label">Logo thương hiệu</label>
                        <div id="logoDropzone"
                             onclick="document.getElementById('brandLogo').click()"
                             style="border:2px dashed var(--border);border-radius:10px;padding:1.25rem;text-align:center;cursor:pointer;background:var(--bg-secondary);transition:border-color 0.2s;"
                             onmouseenter="this.style.borderColor='var(--primary)'"
                             onmouseleave="this.style.borderColor='var(--border)'">
                            <i class="fa-solid fa-cloud-arrow-up" style="font-size:1.6rem;color:var(--primary);margin-bottom:0.4rem;display:block;"></i>
                            <div style="font-size:0.82rem;color:var(--text-secondary);font-weight:600;">Click để chọn ảnh</div>
                            <div style="font-size:0.72rem;color:var(--text-muted);margin-top:2px;">JPG, PNG, WEBP – tự động resize về 250px</div>
                        </div>
                        <input type="file" id="brandLogo" name="logo" accept="image/*" style="display:none;">

                        <!-- Logo Preview -->
                        <div id="logoPreviewContainer" style="display:none;margin-top:0.75rem;align-items:center;gap:0.75rem;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:0.75rem;">
                            <img id="logoPreview" src="" alt="Logo preview" style="height:50px;max-width:120px;object-fit:contain;border-radius:4px;">
                            <div style="flex:1;min-width:0;">
                                <div style="font-size:0.78rem;font-weight:600;color:#065f46;">Ảnh đã chọn</div>
                                <div style="font-size:0.72rem;color:var(--text-muted);">Để trống nếu không muốn thay đổi</div>
                            </div>
                            <button type="button" onclick="clearLogo()" style="border:none;background:none;cursor:pointer;color:var(--text-muted);font-size:1rem;padding:4px;" title="Xóa ảnh đã chọn">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div style="padding:1rem 1.5rem;border-top:1px solid var(--border);display:flex;gap:0.75rem;justify-content:flex-end;flex-shrink:0;">
                    <button type="button" class="btn btn-outline" onclick="closeModal()">Hủy</button>
                    <button type="submit" class="btn btn-primary" id="saveBtn">
                        <i class="fa-solid fa-floppy-disk"></i> Lưu
                    </button>
                </div>
            </form>
        </div>
    </div>
</main>

<script>
// clearlogo helper
function clearLogo() {
    document.getElementById('brandLogo').value = '';
    document.getElementById('logoPreviewContainer').style.display = 'none';
}
</script>
<script src="js/admin/admin_brands.js"></script>
