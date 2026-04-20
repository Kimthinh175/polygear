<main class="main-content">
    <div id="listProductContainer">

        <!-- Page Header -->
        <div class="page-header" style="margin-bottom: 1.5rem;">
            <div>
                <h1 class="page-title">Quản lý Sản Phẩm Gốc</h1>
                <p class="text-muted text-sm mt-4">Danh sách toàn bộ sản phẩm gốc trong hệ thống.</p>
            </div>
            <div class="flex gap-2">
                <button class="btn btn-primary" onclick="openCreateModal()">
                    <i class="fa-solid fa-plus"></i> Tạo Sản Phẩm Mới
                </button>
            </div>
        </div>

        <!-- Filter & Search Bar -->
        <div class="card" style="padding: 1rem 1.25rem; margin-bottom: 1.25rem;">
            <div style="display:flex; gap: 1rem; flex-wrap: wrap; align-items: center;">
                <div style="flex: 0 0 190px;">
                    <select id="categoryFilter" class="form-select" onchange="filterAndRender()">
                        <option value="">-- Tất cả danh mục --</option>
                    </select>
                </div>
                <div style="flex: 0 0 190px;">
                    <select id="brandFilter" class="form-select" onchange="filterAndRender()">
                        <option value="">-- Tất cả thương hiệu --</option>
                    </select>
                </div>
                <div class="search-box" style="flex:1; min-width:220px;">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" id="searchInput" placeholder="Tìm theo tên sản phẩm..." onkeyup="filterAndRender()">
                </div>
                <div style="font-size:0.8rem; color:var(--text-muted); white-space:nowrap;">
                    Hiển thị <strong id="resultCount">0</strong> sản phẩm
                </div>
            </div>
        </div>

        <!-- Column Header -->
        <div style="display:flex;align-items:center;padding:0 1rem;margin-bottom:0.35rem;">
            <div style="width:50px;flex-shrink:0;font-size:0.7rem;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;color:var(--text-muted);">ID</div>
            <div style="width:180px;flex-shrink:0;font-size:0.7rem;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;color:var(--text-muted);">Thương Hiệu</div>
            <div style="flex:1;font-size:0.7rem;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;color:var(--text-muted);">Tên Sản Phẩm</div>
            <div style="width:150px;flex-shrink:0;font-size:0.7rem;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;color:var(--text-muted);">Danh Mục</div>
            <div style="width:110px;flex-shrink:0;text-align:center;font-size:0.7rem;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;color:var(--text-muted);">Biến Thể</div>
            <div style="width:190px;flex-shrink:0;text-align:right;font-size:0.7rem;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;color:var(--text-muted);">Thao Tác</div>
        </div>

        <!-- Product List -->
        <div id="productTableBody" style="display:flex;flex-direction:column;gap:0.5rem;margin-bottom:1.5rem;"></div>

        <!-- No Results -->
        <div id="noResults" style="display:none;padding:4rem 2rem;text-align:center;color:var(--text-muted);">
            <i class="fa-solid fa-box-open" style="font-size:3rem;display:block;margin-bottom:1rem;opacity:0.3;"></i>
            <p style="font-weight:500;">Không tìm thấy sản phẩm nào phù hợp.</p>
        </div>

        <!-- Pagination -->
        <nav id="paginationContainer" style="display:none;">
            <ul class="pagination" id="pagination"></ul>
        </nav>

    </div>

    <!-- ══════════════════════════════════════════════
         MODAL: Tạo Sản Phẩm Mới
    ══════════════════════════════════════════════ -->
    <div id="createProductModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.55);z-index:9000;align-items:center;justify-content:center;padding:1rem;">
        <div style="background:white;border-radius:14px;width:100%;max-width:560px;box-shadow:0 25px 80px rgba(0,0,0,0.25);overflow:hidden;max-height:92vh;display:flex;flex-direction:column;">
            <!-- Modal Header -->
            <div style="padding:1.25rem 1.5rem;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;flex-shrink:0;">
                <div>
                    <div id="createProductModalTitle" style="font-weight:700;font-size:1.05rem;">Tạo Sản Phẩm Mới</div>
                    <div style="font-size:0.78rem;color:var(--text-muted);margin-top:2px;">Khai báo thông tin cơ bản của sản phẩm gốc</div>
                </div>
                <button onclick="closeCreateModal()" style="border:none;background:none;cursor:pointer;font-size:1.2rem;color:var(--text-muted);padding:4px 8px;border-radius:6px;" title="Đóng">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <div style="padding:1.5rem;overflow-y:auto;flex:1;display:flex;flex-direction:column;gap:1.1rem;">

                <!-- Product Name -->
                <div class="form-group" style="margin:0;">
                    <label class="form-label">Tên sản phẩm <span style="color:red">*</span></label>
                    <input type="text" id="cpName" class="form-control" placeholder="VD: RAM ADATA XPG D50 RGB">
                </div>

                <!-- Category -->
                <div class="form-group" style="margin:0;">
                    <label class="form-label">Danh mục <span style="color:red">*</span></label>
                    <select id="cpCategory" class="form-select" onchange="handleCpCategoryChange()">
                        <option value="">-- Chọn danh mục --</option>
                    </select>
                    <!-- Inline: create new category -->
                    <div id="cpNewCatFields" style="display:none;margin-top:0.75rem;background:var(--bg-secondary);border-radius:8px;padding:0.85rem;border:1px solid var(--border);display:flex;flex-direction:column;gap:0.6rem;">
                        <div style="font-size:0.78rem;font-weight:600;color:var(--text-secondary);display:flex;align-items:center;gap:0.4rem;margin-bottom:0.25rem;">
                            <i class="fa-solid fa-folder-plus" style="color:var(--primary);"></i> Tạo danh mục mới
                        </div>
                        <input type="text" id="cpNewCatName" class="form-control" placeholder="Tên danh mục (VD: RAM PC)">
                        <input type="text" id="cpNewCatCode" class="form-control" placeholder="Code (VD: ram-pc)" style="font-family: monospace; font-size: 0.85rem;">
                    </div>
                </div>

                <!-- Brand -->
                <div class="form-group" style="margin:0;">
                    <label class="form-label">Thương hiệu</label>
                    <select id="cpBrand" class="form-select" onchange="handleCpBrandChange()">
                        <option value="">-- Không có / Chọn sau --</option>
                    </select>
                </div>

                <!-- Inline: create new brand (orange box) -->
                <div id="cpNewBrandFields" style="display:none;background:#fff7ed;border:1.5px solid #f97316;border-radius:10px;padding:1rem;display:flex;flex-direction:column;gap:0.75rem;">
                    <div style="font-size:0.82rem;font-weight:700;color:#ea580c;display:flex;align-items:center;gap:0.5rem;">
                        <i class="fa-solid fa-award"></i> Tạo thương hiệu mới
                    </div>
                    <input type="text" id="cpNewBrandName" class="form-control" placeholder="Tên thương hiệu (VD: ADATA)">
                    
                    <!-- Logo upload with preview -->
                    <div>
                        <label class="form-label" style="font-size:0.8rem;">Logo <span style="color:red">*</span> <span style="color:var(--text-muted);font-weight:400;">(resize tự động về 250px)</span></label>
                        <div id="cpBrandLogoDropzone" onclick="document.getElementById('cpBrandLogoInput').click()"
                             style="border:2px dashed #f97316;border-radius:8px;padding:1rem;text-align:center;cursor:pointer;background:#fffbf7;transition:all 0.2s;">
                            <div id="cpBrandLogoPlaceholder">
                                <i class="fa-solid fa-cloud-arrow-up" style="font-size:1.5rem;color:#f97316;margin-bottom:0.4rem;display:block;"></i>
                                <div style="font-size:0.8rem;color:#f97316;font-weight:600;">Click để chọn ảnh logo</div>
                                <div style="font-size:0.72rem;color:var(--text-muted);">JPG, PNG, WEBP</div>
                            </div>
                            <img id="cpBrandLogoPreview" src="" alt="" style="display:none;max-width:120px;max-height:80px;object-fit:contain;border-radius:6px;margin:0 auto;">
                        </div>
                        <input type="file" id="cpBrandLogoInput" accept="image/*" style="display:none;" onchange="handleBrandLogoPreview(this)">
                    </div>
                    <button type="button" class="btn btn-sm" onclick="submitNewBrand()"
                            style="background:#f97316;color:white;border:none;display:flex;align-items:center;gap:0.5rem;justify-content:center;">
                        <i class="fa-solid fa-check"></i> <span id="cpNewBrandBtnText">Tạo thương hiệu</span>
                    </button>
                    <div id="cpNewBrandMsg" style="display:none;font-size:0.8rem;text-align:center;"></div>
                </div>

            </div>

            <!-- Modal Footer -->
            <div style="padding:1rem 1.5rem;border-top:1px solid var(--border);display:flex;gap:0.75rem;justify-content:flex-end;flex-shrink:0;">
                <button class="btn btn-outline" onclick="closeCreateModal()">Hủy</button>
                <button class="btn btn-primary" id="cpSubmitBtn" onclick="submitNewProduct()">
                    <i class="fa-solid fa-floppy-disk"></i> Lưu Sản Phẩm
                </button>
            </div>
        </div>
    </div>



</main>

<script src="js/admin/admin_list_product.js"></script>
