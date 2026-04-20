<main class="main-content">
    <div id="listCategoryContainer">
        <!-- Page Header -->
        <div class="page-header" style="margin-bottom: 1.5rem;">
            <div>
                <h1 class="page-title">Quản lý Danh mục</h1>
                <p class="text-muted text-sm mt-4">Quản lý các danh mục sản phẩm trong hệ thống.</p>
            </div>
            <div class="flex gap-2">
                <button class="btn btn-primary" onclick="openCreateModal()">
                    <i class="fa-solid fa-plus"></i> Tạo Danh mục Mới
                </button>
            </div>
        </div>

        <!-- Filter & Search Bar -->
        <div class="card" style="padding: 1rem 1.25rem; margin-bottom: 1.25rem;">
            <div style="display:flex; gap: 1rem; flex-wrap: wrap; align-items: center;">
                <div class="search-box" style="flex:1; min-width:220px;">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" id="searchInput" placeholder="Tìm theo tên danh mục..." onkeyup="filterAndRender()">
                </div>
                <div style="font-size:0.8rem; color:var(--text-muted); white-space:nowrap;">
                    Hiển thị <strong id="resultCount">0</strong> danh mục
                </div>
            </div>
        </div>

        <!-- Column Header -->
        <div style="display:flex;align-items:center;padding:0 1rem;margin-bottom:0.35rem;">
            <div style="width:50px;flex-shrink:0;font-size:0.7rem;font-weight:700;text-transform:uppercase;color:var(--text-muted);">ID</div>
            <div style="flex:1;font-size:0.7rem;font-weight:700;text-transform:uppercase;color:var(--text-muted);">Tên Danh Mục</div>
            <div style="width:200px;flex-shrink:0;font-size:0.7rem;font-weight:700;text-transform:uppercase;color:var(--text-muted);">Mã Danh Mục (Code)</div>
            <div style="width:150px;flex-shrink:0;text-align:right;font-size:0.7rem;font-weight:700;text-transform:uppercase;color:var(--text-muted);">Thao Tác</div>
        </div>

        <!-- Category List -->
        <div id="categoryTableBody" style="display:flex;flex-direction:column;gap:0.5rem;margin-bottom:1.5rem;"></div>

        <!-- No Results -->
        <div id="noResults" style="display:none;padding:4rem 2rem;text-align:center;color:var(--text-muted);">
            <i class="fa-solid fa-folder-open" style="font-size:3rem;display:block;margin-bottom:1rem;opacity:0.3;"></i>
            <p style="font-weight:500;">Không tìm thấy danh mục nào phù hợp.</p>
        </div>

        <!-- Pagination -->
        <nav id="paginationContainer" style="display:none;">
            <ul class="pagination" id="pagination"></ul>
        </nav>
    </div>

    <!-- MODAL -->
    <div id="categoryModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.55);z-index:9000;align-items:center;justify-content:center;padding:1rem;">
        <div style="background:white;border-radius:14px;width:100%;max-width:500px;box-shadow:0 25px 80px rgba(0,0,0,0.25);overflow:hidden;max-height:92vh;display:flex;flex-direction:column;">
            <div style="padding:1.25rem 1.5rem;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;flex-shrink:0;">
                <div>
                    <div style="font-weight:700;font-size:1.05rem;" id="modalTitle">Tạo Danh mục Mới</div>
                </div>
                <button onclick="closeModal()" style="border:none;background:none;cursor:pointer;font-size:1.2rem;color:var(--text-muted);padding:4px 8px;border-radius:6px;">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div style="padding:1.5rem;overflow-y:auto;flex:1;display:flex;flex-direction:column;gap:1.1rem;">
                <input type="hidden" id="catId">
                <div class="form-group" style="margin:0;">
                    <label class="form-label">Tên danh mục <span style="color:red">*</span></label>
                    <input type="text" id="catName" class="form-control" placeholder="VD: Bàn phím cơ">
                </div>
                <div class="form-group" style="margin:0;">
                    <label class="form-label">Mã danh mục (Code) <span style="color:red">*</span></label>
                    <input type="text" id="catCode" class="form-control" placeholder="VD: ban-phim-co" style="font-family: monospace;">
                </div>
            </div>
            <div style="padding:1rem 1.5rem;border-top:1px solid var(--border);display:flex;gap:0.75rem;justify-content:flex-end;flex-shrink:0;">
                <button class="btn btn-outline" onclick="closeModal()">Hủy</button>
                <button class="btn btn-primary" id="saveBtn" onclick="saveCategory()">
                    <i class="fa-solid fa-floppy-disk"></i> Lưu
                </button>
            </div>
        </div>
    </div>
</main>
<script src="js/admin/admin_categories.js"></script>
