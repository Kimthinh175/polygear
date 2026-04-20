<main class="main-content">
    <form id="productForm" novalidate>
        <div class="main-content-inner">

            <!-- Page Header -->
            <div class="page-header" style="margin-bottom: 2rem;">
                <div>
                    <h1 class="page-title" style="margin:0;">Thêm Sản Phẩm Gốc</h1>
                    <p class="text-muted text-sm" style="margin-top:0.3rem;">Khai báo thông tin cơ bản — sau đó thêm Biến Thể để có giá, tồn kho.</p>
                </div>
                <div style="display:flex;gap:0.75rem;">
                    <a href="/admin/list_variant" class="btn btn-outline">
                        <i class="fa-solid fa-arrow-left"></i> Quay lại
                    </a>
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <i class="fa-solid fa-floppy-disk"></i> Lưu Sản Phẩm
                    </button>
                </div>
            </div>

            <!-- 2-column layout -->
            <div style="display:grid;grid-template-columns:1fr 340px;gap:1.5rem;align-items:start;">

                <!-- LEFT: Form Fields -->
                <div style="display:flex;flex-direction:column;gap:1.25rem;">

                    <!-- Product Name Card -->
                    <div class="card" style="margin:0;">
                        <div class="card-header" style="background:linear-gradient(135deg,#f0f4ff,#e8edff);border-bottom:1px solid #c7d2fe;">
                            <div style="display:flex;align-items:center;gap:0.6rem;">
                                <div style="width:30px;height:30px;background:#4f46e5;border-radius:7px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                    <i class="fa-solid fa-tag" style="color:white;font-size:0.8rem;"></i>
                                </div>
                                <div>
                                    <div style="font-weight:700;font-size:0.88rem;color:#3730a3;">Định danh sản phẩm</div>
                                    <div style="font-size:0.72rem;color:#6366f1;">Tên & thương hiệu</div>
                                </div>
                            </div>
                        </div>
                        <div style="padding:1.5rem;display:flex;flex-direction:column;gap:1rem;">
                            <div class="form-group" style="margin:0;">
                                <label class="form-label" for="productName">
                                    Tên sản phẩm <span style="color:#ef4444;">*</span>
                                </label>
                                <input type="text" id="productName" class="form-control"
                                    placeholder="Ví dụ: RAM PC ADATA XPG D50 RGB"
                                    oninput="updatePreview()"
                                    required
                                    style="font-size:1rem;font-weight:500;">
                                <div class="form-hint" style="font-size:0.75rem;color:var(--text-muted);margin-top:0.35rem;">
                                    <i class="fa-solid fa-circle-info"></i> Đặt tên đầy đủ, rõ ràng để dễ tìm kiếm sau này.
                                </div>
                            </div>

                            <div class="form-group" style="margin:0;">
                                <label class="form-label" for="productBrand">Thương hiệu</label>
                                <div style="position:relative;">
                                    <i class="fa-solid fa-award" style="position:absolute;left:0.85rem;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:0.85rem;pointer-events:none;"></i>
                                    <input type="text" id="productBrand" class="form-control"
                                        placeholder="Ví dụ: ADATA, Samsung, Kingston..."
                                        oninput="updatePreview()"
                                        style="padding-left:2.25rem;">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Category Card -->
                    <div class="card" style="margin:0;">
                        <div class="card-header" style="background:linear-gradient(135deg,#f0fdf4,#dcfce7);border-bottom:1px solid #bbf7d0;">
                            <div style="display:flex;align-items:center;gap:0.6rem;">
                                <div style="width:30px;height:30px;background:#16a34a;border-radius:7px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                    <i class="fa-solid fa-layer-group" style="color:white;font-size:0.8rem;"></i>
                                </div>
                                <div>
                                    <div style="font-weight:700;font-size:0.88rem;color:#166534;">Danh mục</div>
                                    <div style="font-size:0.72rem;color:#16a34a;">Phân loại sản phẩm trong hệ thống</div>
                                </div>
                            </div>
                        </div>
                        <div style="padding:1.5rem;">
                            <div class="form-group" style="margin:0 0 1rem;">
                                <label class="form-label" for="categoryId">
                                    Danh mục <span style="color:#ef4444;">*</span>
                                </label>
                                <select id="categoryId" class="form-select" onchange="handleCategoryChange(this)" required>
                                    <option value="">-- Chọn danh mục --</option>
                                    <!-- Loaded from DB -->
                                    <option value="new">✦ Tạo danh mục mới...</option>
                                </select>
                            </div>

                            <!-- New Category Fields -->
                            <div id="newCategoryFields" style="display:none;flex-direction:column;gap:0.75rem;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:1rem;">
                                <div style="font-size:0.78rem;font-weight:700;color:#15803d;margin-bottom:0.25rem;">
                                    <i class="fa-solid fa-plus-circle"></i> Tạo danh mục mới
                                </div>
                                <div class="form-group" style="margin:0;">
                                    <label class="form-label" style="font-size:0.78rem;">Tên danh mục (Tiếng Việt) <span style="color:#ef4444;">*</span></label>
                                    <input type="text" id="newCategoryName" class="form-control"
                                        placeholder="Ví dụ: RAM Máy Tính"
                                        style="border-color:#86efac;background:#f0fdf4;">
                                </div>
                                <div class="form-group" style="margin:0;">
                                    <label class="form-label" style="font-size:0.78rem;">Mã code (tiếng Anh, không dấu) <span style="color:#ef4444;">*</span></label>
                                    <input type="text" id="newCategoryCode" class="form-control"
                                        placeholder="Ví dụ: ram-may-tinh"
                                        style="border-color:#86efac;background:#f0fdf4;font-family:monospace;">
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- RIGHT: Preview Card -->
                <div style="position:sticky;top:1rem;">
                    <div class="card" style="margin:0;overflow:hidden;">
                        <div style="background:linear-gradient(135deg,#1e1b4b,#3730a3);padding:1.25rem 1.5rem;">
                            <div style="font-size:0.75rem;font-weight:700;color:rgba(199,210,254,0.8);text-transform:uppercase;letter-spacing:0.08em;margin-bottom:0.5rem;">
                                <i class="fa-solid fa-eye"></i> Xem trước
                            </div>
                            <div style="font-weight:800;font-size:1.05rem;color:white;line-height:1.4;" id="previewName">
                                <span style="color:rgba(255,255,255,0.35);font-style:italic;font-weight:400;">Chưa có tên...</span>
                            </div>
                        </div>
                        <div style="padding:1.25rem;display:flex;flex-direction:column;gap:0.75rem;">
                            <div style="display:flex;align-items:center;gap:0.6rem;">
                                <div style="width:32px;height:32px;background:#eef2ff;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                    <i class="fa-solid fa-award" style="color:#4f46e5;font-size:0.8rem;"></i>
                                </div>
                                <div>
                                    <div style="font-size:0.7rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.05em;">Thương hiệu</div>
                                    <div id="previewBrand" style="font-size:0.88rem;font-weight:600;color:#0f172a;">—</div>
                                </div>
                            </div>
                            <div style="display:flex;align-items:center;gap:0.6rem;">
                                <div style="width:32px;height:32px;background:#f0fdf4;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                    <i class="fa-solid fa-layer-group" style="color:#16a34a;font-size:0.8rem;"></i>
                                </div>
                                <div>
                                    <div style="font-size:0.7rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.05em;">Danh mục</div>
                                    <div id="previewCategory" style="font-size:0.88rem;font-weight:600;color:#0f172a;">—</div>
                                </div>
                            </div>
                            <div style="height:1px;background:var(--border);margin:0.25rem 0;"></div>
                            <div style="background:#fafafa;border:1px solid #f1f5f9;border-radius:8px;padding:0.75rem;font-size:0.78rem;color:#64748b;line-height:1.5;">
                                <i class="fa-solid fa-circle-info" style="color:#a5b4fc;"></i>
                                Sau khi lưu sản phẩm gốc, bạn có thể thêm <strong>Biến Thể</strong> (giá, tồn kho, SKU, ảnh) từ trang Quản lý Biến Thể.
                            </div>
                        </div>
                    </div>

                    <!-- Submit button (mobile friendly duplicate) -->
                    <button type="submit" class="btn btn-primary" style="width:100%;margin-top:1rem;justify-content:center;padding:0.8rem;" id="submitBtn2">
                        <i class="fa-solid fa-floppy-disk"></i> Lưu Sản Phẩm Gốc
                    </button>
                </div>

            </div><!-- end grid -->

        </div>
    </form>
</main>

<!-- Toast -->
<div id="productToast" style="display:none;position:fixed;bottom:2rem;left:50%;transform:translateX(-50%);background:#0f172a;color:white;padding:0.85rem 1.5rem;border-radius:12px;font-size:0.875rem;font-weight:600;box-shadow:0 8px 24px rgba(0,0,0,0.25);z-index:99999;gap:0.6rem;align-items:center;">
    <i id="toastIcon" class="fa-solid fa-check-circle" style="color:#34d399;"></i>
    <span id="toastMsg"></span>
</div>

<script>
function updatePreview() {
    const name  = document.getElementById('productName')?.value.trim();
    const brand = document.getElementById('productBrand')?.value.trim();
    const catEl = document.getElementById('categoryId');
    const catTxt = catEl?.value && catEl.value !== 'new' && catEl.value !== ''
        ? catEl.options[catEl.selectedIndex]?.text
        : document.getElementById('newCategoryName')?.value.trim() || null;

    const pName = document.getElementById('previewName');
    if (pName) pName.innerHTML = name
        ? `<span>${name}</span>`
        : `<span style="color:rgba(255,255,255,0.35);font-style:italic;font-weight:400;">Chưa có tên...</span>`;

    const pBrand = document.getElementById('previewBrand');
    if (pBrand) pBrand.textContent = brand || '—';

    const pCat = document.getElementById('previewCategory');
    if (pCat) pCat.textContent = catTxt || '—';
}

function showToast(msg, success = true) {
    const t = document.getElementById('productToast');
    document.getElementById('toastMsg').textContent = msg;
    document.getElementById('toastIcon').className = success
        ? 'fa-solid fa-check-circle' : 'fa-solid fa-triangle-exclamation';
    document.getElementById('toastIcon').style.color = success ? '#34d399' : '#fb923c';
    t.style.display = 'flex';
    setTimeout(() => { t.style.display = 'none'; }, 3500);
}

// update preview when category changes
document.getElementById('categoryId').addEventListener('change', updatePreview);
document.getElementById('newCategoryName')?.addEventListener('input', updatePreview);
</script>

<script src="js/admin/admin_add_product.js"></script>
</html>
