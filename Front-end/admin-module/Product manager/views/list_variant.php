<main class="main-content">
    <div id="listViewContainer">

        <!-- Page Header -->
        <div class="page-header" style="margin-bottom: 1.5rem;">
            <div>
                <h1 class="page-title">Quản lý Biến Thể</h1>
                <p class="text-muted text-sm mt-4">Danh sách toàn bộ các biến thể sản phẩm (SKU) có trong hệ thống.</p>
            </div>
            <div class="flex gap-2">
                <button class="btn btn-primary" onclick="goToAddVariant()">
                    <i class="fa-solid fa-plus"></i> Thêm Biến Thể Mới
                </button>
            </div>
        </div>

        <!-- Filter & Search Bar -->
        <div class="card" style="padding: 1rem 1.25rem; margin-bottom: 1.25rem;">
            <div style="display:flex; gap: 1rem; flex-wrap: wrap; align-items: center;">
                <div style="flex: 0 0 200px;">
                    <select id="categoryFilter" class="form-select" onchange="filterAndRender()">
                        <option value="">-- Tất cả danh mục --</option>
                    </select>
                </div>
                <div style="flex: 0 0 160px;">
                    <select id="stockFilter" class="form-select" onchange="filterAndRender()">
                        <option value="">-- Tất cả tồn kho --</option>
                        <option value="out">Hết hàng (0)</option>
                        <option value="low">Sắp hết (Tồn ≤ Min)</option>
                        <option value="available">Còn hàng (Tồn > Min)</option>
                    </select>
                </div>
                <div class="search-box" style="flex:1; min-width:220px;">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" id="searchInput" placeholder="Tìm kiếm theo Tên, SKU, SP gốc..." onkeyup="filterAndRender()">
                </div>
                <div style="font-size:0.8rem; color:var(--text-muted); white-space:nowrap;">
                    Hiển thị <strong id="resultCount">0</strong> biến thể
                </div>
            </div>
        </div>

        <!-- Bulk Actions Bar -->
        <div id="bulkActionsContainer" style="display:none; background:rgba(59,130,246,0.08); border:1px solid var(--primary); padding: 0.85rem 1.25rem; border-radius: var(--radius-md); margin-bottom: 1rem; align-items: center; justify-content: space-between;">
            <div style="font-weight:600; color:var(--primary); font-size:0.9rem;">
                <i class="fa-solid fa-check-square"></i> Đã chọn <span id="selectedCount">0</span> biến thể
            </div>
            <div style="display:flex; gap: 0.5rem;">
                <button class="btn btn-outline btn-sm" onclick="bulkStatusUpdate('stop')">Ngừng bán hàng loạt</button>
            </div>
        </div>

        <!-- Column Header Row -->
        <div class="variant-header-row">
            <div></div>
            <div>Ảnh</div>
            <div>Tên biến thể</div>
            <div>SKU</div>
            <div>Danh mục</div>
            <div class="vhcol-price">Giá bán</div>
            <div class="vhcol-stock">Tồn / Min</div>
            <div class="vhcol-actions">Thao tác</div>
        </div>

        <!-- Variant List — 1 row per item -->
        <div id="variantTableBody" style="display:flex; flex-direction:column; gap:0.6rem; margin-bottom:1.5rem;">
            <!-- Row cards rendered by JS -->
        </div>

        <!-- No Results -->
        <div id="noResults" style="display:none; padding:4rem 2rem; text-align:center; color:var(--text-muted);">
            <i class="fa-solid fa-box-open" style="font-size:3rem; display:block; margin-bottom:1rem; opacity:0.3;"></i>
            <p style="font-weight:500;">Không tìm thấy biến thể nào phù hợp.</p>
        </div>

        <!-- Pagination -->
        <nav aria-label="Page navigation" id="paginationContainer" style="display:none;">
            <ul class="pagination" id="pagination">
                <!-- Rendered by JS -->
            </ul>
        </nav>

    </div><!-- end #listViewContainer -->

<div id="formViewContainer" style="display: none;">
            <div class="main-content-inner">
                <form id="variantForm">
                    <div class="page-header" style="margin-bottom: 2rem;">
                        <div>
                            <h1 class="page-title">Thêm Biến Thể Sản Phẩm</h1>
                            <p class="text-muted text-sm mt-4">Khai báo cấu hình, giá, kho và chi tiết cho một biến thể
                                cụ thể.</p>
                        </div>
                    </div>

                    <div class="two-column-layout">

                        <!-- Cột Trái: Nội dung chính -->
                        <div style="display: flex; flex-direction: column; gap: 1.5rem;">

                            <!-- 1. Tên & SKU -->
                            <div class="card" style="margin-bottom: 0;">
                                <div class="card-header">
                                    <div><i class="fa-solid fa-tag text-primary"></i> Thông tin Biến thể</div>
                                </div>

                                <!-- Chọn SP Gốc (Từ cột phải chuyển sang) -->
                                <div class="form-group" id="productFormGroup" style="position: relative;">
                                    <label class="form-label" style="display:block;">Sản Phẩm Gốc <span
                                            style="color:red">*</span></label>
                                    <div class="searchable-select" id="productSelect">
                                        <div class="select-header" onclick="toggleDropdown(this)">
                                            <span class="selected-text">-- Chọn một sản phẩm gốc --</span>
                                            <i class="fa-solid fa-chevron-down"></i>
                                        </div>
                                        <div class="select-dropdown">
                                            <div class="filter-box"
                                                style="padding: 0.5rem 1rem; border-bottom: 1px solid var(--border); display: flex; gap: 0.5rem; flex-direction: column; background: var(--bg-color);">
                                            </div>
                                            <div class="no-results"
                                                style="display: none; padding: 0.75rem 1rem; color: var(--text-muted); text-align: center; font-size: 0.875rem;">
                                                Không tìm thấy sản phẩm</div>
                                        </div>
                                    </div>
                                    <input type="hidden" id="productId" required>
                                </div>

                                <div class="form-grid">
                                    <div class="form-group">
                                        <label class="form-label" for="variantName">Tên biến thể <span
                                                style="color:red">*</span></label>
                                        <input type="text" id="variantName" class="form-control"
                                            placeholder="Ví dụ: Phiên bản đặc biệt" required oninput="generateSKU()">
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label" for="variantSku">Mã SKU (Tự động) <span
                                                style="color:red">*</span></label>
                                        <input type="text" id="variantSku" class="form-control"
                                            placeholder="Tự động tạo (Ví dụ: AO-THUN-POLO-DO-XL)" required>
                                    </div>
                                </div>
                            </div>

                            <!-- 2. TinyMCE Bài viết -->
                            <div class="card" style="margin-bottom: 0;">
                                <div class="card-header">
                                    <div><i class="fa-solid fa-file-lines text-primary"></i> Bài viết chi tiết (Mô tả)
                                    </div>
                                </div>
                                <textarea id="variantDescription"></textarea>
                            </div>

                            <!-- 3. Thuộc tính -->
                            <div class="card" style="margin-bottom: 0;">
                                <div class="card-header">
                                    <div><i class="fa-solid fa-layer-group text-primary"></i> Thuộc tính của Biến thể
                                    </div>
                                    <button type="button" class="btn btn-outline btn-sm" onclick="addAttributeRow()">
                                        <i class="fa-solid fa-plus"></i> Thêm thuộc tính
                                    </button>
                                </div>
                                <p class="text-sm text-muted" style="margin-bottom: 1rem;">Theo màu sắc, kích thước...
                                    áp
                                    dụng cho SKU này.</p>

                                <div id="attributesContainer">
                                    <div class="dynamic-row attr-row">
                                        <div class="col">
                                            <label class="form-label text-sm">Thuộc tính (Từ DB)</label>
                                            <select class="form-select attr-select"
                                                onchange="handleSelectChange(this, 'attr')">
                                                <option value="">-- Chọn thuộc tính --</option>
                                            </select>
                                            <input type="text" class="form-control new-input-field new-attr"
                                                placeholder="Nhập tên thuộc tính">
                                        </div>
                                        <div class="col">
                                            <label class="form-label text-sm">Giá trị tương ứng</label>
                                            <select class="form-select val-select"
                                                onchange="handleSelectChange(this, 'val')">
                                                <option value="">-- Chọn giá trị --</option>
                                                <option value="new">+ Thêm giá trị mới...</option>
                                            </select>
                                            <input type="text" class="form-control new-input-field new-val"
                                                placeholder="Nhập giá trị">
                                        </div>
                                        <button type="button" class="btn btn-danger-outline" style="margin-top: 1.7rem;"
                                            onclick="removeRowAndUpdate(this)">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- 4. Thông số -->
                            <div class="card" style="margin-bottom: 0;">
                                <div class="card-header">
                                    <div><i class="fa-solid fa-sliders text-primary"></i> Thông số riêng biệt</div>
                                    <button type="button" class="btn btn-outline btn-sm" onclick="addSpecRow()">
                                        <i class="fa-solid fa-plus"></i> Thêm thông số
                                    </button>
                                </div>

                                <div id="specsContainer">
                                    <div class="dynamic-row spec-row">
                                        <div class="col">
                                            <label class="form-label text-sm">Tên thông số (Từ DB)</label>
                                            <select class="form-select spec-key-select"
                                                onchange="handleSpecChange(this)">
                                                <option value="">-- Chọn thông số --</option>
                                            </select>
                                            <div class="new-spec-fields"
                                                style="display: none; margin-top: 0.5rem; gap: 0.5rem; flex-direction: column;">
                                                <input type="text" class="form-control new-spec-name"
                                                    placeholder="Tiếng Việt (Trọng lượng)">
                                                <input type="text" class="form-control new-spec-key-system"
                                                    placeholder="Tiếng Anh (weight)">
                                            </div>
                                        </div>
                                        <div class="col">
                                            <label class="form-label text-sm">Giá trị thông số</label>
                                            <input type="text" class="form-control spec-val"
                                                placeholder="Ví dụ: 1kg, 120cm..." required>
                                        </div>
                    <button type="button" class="btn btn-danger-outline" style="margin-top: 1.7rem;"
                                            onclick="removeSpecRow(this)">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Cột Phải: Sidebar -->
                        <div style="display: flex; flex-direction: column; gap: 1.5rem;">

                            <!-- Xuất Bản -->
                            <div class="card"
                                style="margin-bottom: 0; padding: 1rem; background-color: var(--primary-light); border-color: var(--primary); box-shadow: none;">
                                <div class="card-header"
                                    style="border-bottom-color: rgba(79, 70, 229, 0.2); font-size: 0.875rem; padding-bottom: 0.5rem; margin-bottom: 0.75rem;">
                                    <div style="font-weight: 700;"><i class="fa-solid fa-paper-plane text-primary"></i>
                                        Xuất Bản</div>
                                    <span class="text-sm" style="color: var(--primary); font-weight: normal;">Mới</span>
                                </div>
                                <button type="submit" class="btn btn-primary"
                                    style="width: 100%; justify-content: center; padding: 0.5rem; font-size: 0.875rem;">
                                    LƯU BIẾN THỂ</button>
                                <button type="button" class="btn btn-outline" onclick="closeVariantForm()" style="width: 100%; justify-content: center; padding: 0.5rem; font-size: 0.875rem; margin-top: 0.5rem;">HỦY / QUAY LẠI</button>
                            </div>

                            <!-- Giá & Kho -->
                            <div class="card" style="margin-bottom: 0;">
                                <div class="card-header">
                                    <div><i class="fa-solid fa-money-bill-wave text-primary"></i> Giá bán & Kho</div>
                                </div>
                                <div class="form-group" style="margin-bottom: 1rem;">
                                    <label class="form-label" for="variantPrice">Giá gốc <span style="color:red">*</span></label>
                                    <input type="number" id="variantPrice" class="form-control" placeholder="000000" min="0" step="10000" required>
                                </div>
                                <div class="form-group" style="margin-bottom: 1.5rem;">
                                    <label class="form-label" for="variantSalePrice" style="color: var(--danger);">Giá KM</label>
                                    <input type="number" id="variantSalePrice" class="form-control" placeholder="0" min="0" step="10000">
                                </div>
                                <div class="form-group mb-0" style="margin-bottom: 1rem !important;">
                                    <label class="form-label" for="variantStock">Số lượng Tồn kho</label>
                                    <input type="number" id="variantStock" class="form-control" placeholder="0"
                                        value="0">
                                </div>
                                <div class="form-group mb-0">
                                    <label class="form-label" for="variantMinStock" style="color: var(--danger);">Cảnh báo tồn kho (Min)</label>
                                    <input type="number" id="variantMinStock" class="form-control" placeholder="0" value="0" min="0">
                                </div>
                                <input type="hidden" id="variantStatus" value="active">
                            </div>

                            <!-- Bilder -->
                            <div class="card" style="margin-bottom: 0;">
                                <div class="card-header">
                                    <div><i class="fa-solid fa-image text-primary"></i> Hình ảnh Biến thể</div>
                                </div>
                                
                                <div style="display: flex; gap: 1.5rem; flex-wrap: wrap;">
                                    <!-- Ảnh chính -->
                                    <div style="flex: 0 0 150px;">
                                        <label class="form-label mb-2">Ảnh đại diện (1)</label>
                                        <div class="variant-img-slot" id="mainImageSlot" onclick="openMediaLibrary('main')">
                                            <i class="fa-solid fa-cloud-arrow-up text-primary" style="font-size: 1.5rem; margin-bottom: 0.5rem;"></i>
                                            <span class="slot-action-text">+ Chọn ảnh</span>
                                            <div class="slot-label">ẢNH CHÍNH</div>
                                        </div>
                                    </div>

                                    <!-- Ảnh phụ -->
                                    <div style="flex: 1; min-width: 200px;">
                                        <label class="form-label mb-2">Ảnh phụ (Chi tiết)</label>
                                        <div class="sub-images-grid" id="subImagesContainer">
                                            <!-- Sub image slots will be appended here -->
                                            <div class="add-sub-img-btn" onclick="openMediaLibrary('sub')" title="Thêm ảnh phụ">
                                                <i class="fa-solid fa-plus text-primary" style="font-size: 1.5rem;"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </form>
            </div>
        </div><!-- end #formViewContainer -->
    </main>

    <!-- Scripts -->
    <link rel="stylesheet" href="css/admin/admin_list_variant.css">
    <link rel="stylesheet" href="css/admin/media_library.css">
    <script src="js/admin/admin_list_variant.js"></script>
    <script src="https:// cdn.tiny.cloud/1/hum2f8n31ss718sivoueqhuqslo5pm2dfw9rzqx7y1sll5y2/tinymce/8/tinymce.min.js" referrerpolicy="origin" crossorigin="anonymous"></script>
    <script src="js/admin/admin_add_variant.js"></script>

    <!-- ============================
         Modal: Thư Viện Media
         (Dùng chung cho cả ảnh chính & ảnh phụ biến thể)
         ============================ -->
    <div class="ml-modal-backdrop" id="mediaLibraryModal">
        <div class="ml-modal-container">

            <!-- Header -->
            <div class="ml-modal-header">
                <h3><i class="fa-regular fa-images" style="color: var(--primary); margin-right: 0.5rem;"></i>Thư viện hình ảnh</h3>
                <button type="button" onclick="closeMediaLibrary()"
                    style="background:none; border:none; cursor:pointer; font-size:1.25rem; color:var(--text-muted); display:flex; align-items:center; justify-content:center; padding:0.25rem;">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <!-- Tabs -->
            <div class="ml-tabs">
                <button type="button" class="ml-tab-btn active" onclick="switchMediaTab('upload')">
                    <i class="fa-solid fa-cloud-arrow-up" style="margin-right: 0.4rem;"></i>Tải ảnh lên
                </button>
                <button type="button" class="ml-tab-btn" onclick="switchMediaTab('library')">
                    <i class="fa-solid fa-layer-group" style="margin-right: 0.4rem;"></i>Ảnh sản phẩm gốc
                </button>
            </div>

            <!-- Body -->
            <div class="ml-modal-body">

                <!-- Tab 1: Upload -->
                <div id="mlTabUpload" class="ml-tab-content active">
                    <div class="ml-upload-box" onclick="document.getElementById('mlFileInput').click()">
                        <i class="fa-solid fa-cloud-arrow-up" style="font-size: 3rem; color: var(--primary); margin-bottom: 1rem;"></i>
                        <p style="font-size: 1.1rem; font-weight: 600; color: var(--text-main);">
                            Click hoặc kéo & thả ảnh vào đây
                        </p>
                        <p class="text-sm text-muted" style="margin-top: 0.5rem;">
                            Hỗ trợ JPG, PNG, WEBP &mdash; Tối đa 2MB mỗi ảnh
                        </p>
                        <input type="file" id="mlFileInput" multiple accept="image/*"
                            style="display: none;" onchange="handleModalUpload(this.files)">
                    </div>
                </div>

                <!-- Tab 2: Library (ảnh từ sản phẩm gốc via JOIN backend) -->
                <div id="mlTabLibrary" class="ml-tab-content">
                    <div id="mlGalleryGrid" class="ml-gallery-grid">
                        <div style="grid-column: 1/-1; text-align: center; padding: 3rem 2rem; color: var(--text-muted);">
                            <i class="fa-regular fa-image" style="font-size: 2rem; display: block; margin-bottom: 0.75rem; opacity: 0.4;"></i>
                            Vui lòng chọn <strong>Sản phẩm gốc</strong> trước để xem ảnh liên quan.
                        </div>
                    </div>
                </div>

            </div>

            <!-- Footer -->
            <div class="ml-modal-footer">
                <div class="text-sm text-muted" id="mlStatusText">Chưa có ảnh nào được chọn</div>
                <div style="display: flex; gap: 0.75rem;">
                    <button type="button" class="btn btn-outline" onclick="closeMediaLibrary()">Hủy</button>
                    <button type="button" class="btn btn-primary" onclick="confirmMediaSelection()">
                        <i class="fa-solid fa-check" style="margin-right: 0.3rem;"></i>Chọn ảnh này
                    </button>
                </div>
            </div>

        </div>
    </div>
    <script>
        // teleport modal ra document.body để thoát khỏi .app-container (flex)
        // nếu không làm vậy, position:fixed sẽ bị giới hạn trong flex context
        (function() {
            var modal = document.getElementById('mediaLibraryModal');
            if (modal) document.body.appendChild(modal);
        })();
        function goToAddVariant() {
            const urlParams = new URLSearchParams(window.location.search);
            const pid = urlParams.get('product_id');
            if (pid) {
                window.location.href = `/admin/add_variant?product_id=${pid}`;
            } else {
                window.location.href = `/admin/add_variant`;
            }
        }
    </script>
