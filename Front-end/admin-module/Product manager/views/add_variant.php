<main class="main-content">
            <div class="main-content-inner">
                <form id="variantForm">
                    <div class="page-header" style="margin-bottom: 2rem;">
                        <div>
                            <h1 class="page-title">Thêm Biến Thể Sản Phẩm</h1>
                            <p class="text-muted text-sm mt-4">Khai báo cấu hình, giá, kho và chi tiết cho một biến thể
                                (SKU) cụ thể.</p>
                        </div>
                    </div>

                    <div class="two-column-layout">

                        <!-- Cột Trái: Nội dung chính -->
                        <div style="display: flex; flex-direction: column; gap: 1.5rem;">

                            <!-- 1. Tên & SKU -->
                            <div class="card" style="margin-bottom: 0;">
                                <div class="card-header">
                                    <div><i class="fa-solid fa-tag text-primary"></i> Thông tin Biến thể (SKU)</div>
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
                                                <select id="categoryFilter" class="form-select text-sm"
                                                    onchange="filterProductsCombo()">
                                                    <option value="">-- Tất cả danh mục --</option>

                                                </select>
                                                <div class="search-box"
                                                    style="padding: 0; border-bottom: none; display: flex; align-items: center; border: 1px solid var(--border); border-radius: var(--radius-sm); background: var(--surface);">
                                                    <i class="fa-solid fa-magnifying-glass"
                                                        style="margin-left: 0.5rem; color: var(--text-muted);"></i>
                                                    <input type="text" id="productSearchInput"
                                                        placeholder="Tìm kiếm SP..." onkeyup="filterProductsCombo()"
                                                        style="border: none; padding: 0.4rem; outline: none; flex: 1; background: transparent;">
                                                </div>
                                            </div>
                                            <div class="options-list">
                                                <div class="option" style="text-align: center; color: var(--text-muted);">Đang tải dữ liệu...</div>
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
                                        <label class="form-label" for="variantName">Tên biến thể</label>
                                        <input type="text" id="variantName" class="form-control"
                                            placeholder="Ví dụ: Phiên bản đặc biệt (Có thể để trống)" oninput="generateSKU()">
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label" for="variantSku">Mã SKU (Tự động) <span
                                                style="color:red">*</span></label>
                                        <input type="text" id="variantSku" class="form-control"
                                            placeholder="Tự động tạo (Ví dụ: AO-THUN-POLO-DO-XL)" required readonly>
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
                                    LƯU BIẾN THỂ
                                </button>
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
                                <div class="form-group mb-0">
                                    <label class="form-label" for="variantStock">Số lượng Tồn kho</label>
                                    <input type="number" id="variantStock" class="form-control" placeholder="0"
                                        value="0" min="0">
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
        </main>
    </div> <!-- Close app-container -->

    <!-- Media Library Modal -->
    <div class="ml-modal-backdrop" id="mediaLibraryModal">
        <div class="ml-modal-container">
            <div class="ml-modal-header">
                <h3>Thư viện Media</h3>
                <button type="button" class="btn btn-outline btn-sm" onclick="closeMediaLibrary()" style="border:none; border-radius:50%; width:32px; height:32px; display:flex; align-items:center; justify-content:center;"><i class="fa-solid fa-xmark"></i></button>
            </div>
            
            <div class="ml-tabs">
                <button type="button" class="ml-tab-btn active" onclick="switchMediaTab('upload')">Tải lên tệp</button>
                <button type="button" class="ml-tab-btn" onclick="switchMediaTab('library')">Tất cả tập tin</button>
            </div>

            <div class="ml-modal-body">
                <!-- Upload Tab -->
                <div id="mlTabUpload" class="ml-tab-content active">
                    <div class="ml-upload-box" onclick="document.getElementById('mlFileInput').click()">
                        <i class="fa-solid fa-cloud-arrow-up" style="font-size: 3rem; color: var(--primary); margin-bottom: 1rem;"></i>
                        <p style="font-size: 1.1rem; font-weight: 500;">Click hoặc Kéo thả ảnh vào đây</p>
                        <p class="text-sm text-muted mt-2">Dung lượng tối đa 2MB. Hỗ trợ JPG, PNG, WEBP</p>
                        <input type="file" id="mlFileInput" multiple accept="image/*" style="display: none;" onchange="handleModalUpload(this.files)">
                    </div>
                </div>

                <!-- Library Tab -->
                <div id="mlTabLibrary" class="ml-tab-content">
                    <div id="mlGalleryGrid" class="ml-gallery-grid">
                        <!-- Loaded via JS -->
                        <div style="grid-column: 1/-1; text-align: center; padding: 2rem; color: var(--text-muted);">
                            Vui lòng chọn Sản phẩm gốc trước để xem thư viện ảnh.
                        </div>
                    </div>
                </div>
            </div>

            <div class="ml-modal-footer">
                <div class="text-sm text-muted" id="mlStatusText">Chưa có ảnh nào được chọn</div>
                <div>
                    <button type="button" class="btn btn-outline" onclick="closeMediaLibrary()">Hủy</button>
                    <button type="button" class="btn btn-primary ml-2" onclick="confirmMediaSelection()">Chọn ảnh</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts and CSS -->
    <link rel="stylesheet" href="css/admin/media_library.css">
    <script src="https:// cdn.tiny.cloud/1/hum2f8n31ss718sivoueqhuqslo5pm2dfw9rzqx7y1sll5y2/tinymce/8/tinymce.min.js" referrerpolicy="origin" crossorigin="anonymous"></script>
    <script src="js/admin/admin_add_variant.js"></script>
    <script>
        (function() {
            var modal = document.getElementById('mediaLibraryModal');
            if (modal) document.body.appendChild(modal);
        })();
    </script>
</body>
</html>
