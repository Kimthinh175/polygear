// global state for api data
let appState = {
    products: [],
    specs: [],
    attributes: {}
};
let attributesTemplates = { attrOptions: '<option value="">-- Chọn thuộc tính --</option>' };
let specOptionsTemplate = '<option value="">-- Chọn thông số --</option>';

// mapping specs tự động theo danh mục (category.code)
const categorySpecsMap = {
    'ram': ['LOAI_RAM', 'BUS_RAM', 'DUNG_LUONG', 'DIEN_AP'],
    'cpu': ['DONG_SAN_PHAM', 'SOCKET', 'SO_NHAN', 'SO_LUONG', 'TOC_DO_XU_LY', 'CACHE'],
    'mainboard': ['CHIPSET', 'SOCKET', 'KICH_THUOC', 'CONG_NGHE_HO_TRO'],
    'hard-drive': ['CHUAN_GIAO_TIEP', 'DUNG_LUONG', 'TOC_DO_DOC', 'TOC_DO_GHI'],
    'graphic-card': ['CHIP_DO_HOA', 'DUNG_LUONG_VRAM', 'XUNG_NHIP'],
    'psu': ['CONG_SUAT', 'CHUAN_HIEU_SUAT', 'KICH_THUOC'],
    'cooler': ['LOAI_TAN_NHIET', 'TOC_DO_QUAT', 'TIENG_ON'],
    'case': ['KICH_THUOC', 'CHAT_LIEU']
};

const specLabelsMap = {
    'LOAI_RAM': 'Loại RAM', 'BUS_RAM': 'Bus RAM', 'DUNG_LUONG': 'Dung lượng', 'DIEN_AP': 'Điện áp',
    'DONG_SAN_PHAM': 'Dòng sản phẩm', 'SOCKET': 'Socket', 'SO_NHAN': 'Số nhân', 'SO_LUONG': 'Số luồng',
    'TOC_DO_XU_LY': 'Tốc độ xử lý', 'CACHE': 'Cache', 'CHIPSET': 'Chipset', 'KICH_THUOC': 'Kích thước',
    'CONG_NGHE_HO_TRO': 'Công nghệ hỗ trợ', 'CHUAN_GIAO_TIEP': 'Chuẩn giao tiếp', 'TOC_DO_DOC': 'Tốc độ đọc',
    'TOC_DO_GHI': 'Tốc độ ghi', 'CHIP_DO_HOA': 'Chip đồ họa', 'DUNG_LUONG_VRAM': 'Dung lượng VRAM',
    'XUNG_NHIP': 'Xung nhịp', 'CONG_SUAT': 'Công suất', 'CHUAN_HIEU_SUAT': 'Chuẩn hiệu suất',
    'LOAI_TAN_NHIET': 'Loại tản nhiệt', 'TOC_DO_QUAT': 'Tốc độ quạt', 'TIENG_ON': 'Tiếng ồn', 'CHAT_LIEU': 'Chất liệu'
};

function autoLoadSpecsByCategory(catCode) {
    if (!catCode) return;
    const specsContainer = document.getElementById('specsContainer');
    if (!specsContainer) return;

    // xoá form rỗng cũ
    specsContainer.innerHTML = '';

    const requiredSpecs = categorySpecsMap[catCode.toLowerCase()] || [];

    if (requiredSpecs.length > 0) {
        requiredSpecs.forEach(specCode => {
            addSpecRow();
            const newRow = specsContainer.lastElementChild;
            const selectEl = newRow.querySelector('.spec-key-select');
            const specMeta = appState.specs.find(s => s.spec_code === specCode);

            if (specMeta) {
                selectEl.value = specMeta.id;
                handleSpecChange(selectEl);
            } else {
                selectEl.value = 'new';
                handleSpecChange(selectEl);
                newRow.querySelector('.new-spec-key-system').value = specCode;
                newRow.querySelector('.new-spec-name').value = specLabelsMap[specCode] || specCode;
                const previewEl = newRow.querySelector('.spec-key-display');
                if (previewEl) previewEl.textContent = specCode;
            }
        });
    }

    if (specsContainer.children.length === 0) {
        addSpecRow();
    }
}

// tự động load khi vào trang add_variant standalone (không phải qua openaddform)
document.addEventListener('DOMContentLoaded', () => {
    // nếu .options-list tồn tại ở dom lúc load => đang ở standalone add_variant page
    if (document.querySelector('.options-list')) {
        fetchVariantInitData();
    }
});

// fetch data từ backend
async function fetchVariantInitData() {
    try {
        const [resProducts, resSpecs, resAttrs] = await Promise.all([
            fetch('https:// polygearid.ivi.vn/back-end/api/products/origin', { credentials: 'include' }).then(res => res.json()),
            fetch('https:// polygearid.ivi.vn/back-end/api/specs', { credentials: 'include' }).then(res => res.json()),
            fetch('https:// polygearid.ivi.vn/back-end/api/attributes', { credentials: 'include' }).then(res => res.json())
        ]);

        // 1. process products
        let productsData = resProducts.data || resProducts;
        if (!Array.isArray(productsData)) productsData = [];
        appState.products = productsData;

        // map id categories cho filter (origin api được nâng cấp trả code + name từ câu lệnh join backend)
        let cats = new Map();
        let productListHtml = '';
        productsData.forEach(p => {
            const catCode = p.category_code || p.code || '';
            const catName = p.category_name || (catCode ? (catCode.length > 2 ? catCode.toUpperCase() : catCode) : '');
            if (catCode) cats.set(catCode, catName);

            const pId = p.id || p.product_id;
            productListHtml += `<div class="option" data-value="${pId}" data-category="${catCode}" onclick="selectOption(this)">[${catName || catCode}] ${p.name}</div>`;
        });

        // html dùng 'categoryfilter', list_variant embed có thể dùng 'formcategoryfilter'
        const catSelect = document.getElementById('formCategoryFilter') || document.getElementById('categoryFilter');
        if (catSelect) {
            catSelect.innerHTML = '<option value="">-- Tất cả danh mục --</option>' +
                Array.from(cats.entries()).map(([code, name]) => `<option value="${code}">${name}</option>`).join('');
        }

        const optionsList = document.querySelector('.options-list');
        if (optionsList) optionsList.innerHTML = productListHtml;

        // 2. process specs
        let specsData = resSpecs.data || resSpecs;
        if (!Array.isArray(specsData)) specsData = [];
        appState.specs = specsData;

        specsData.forEach(s => {
            specOptionsTemplate += `<option value="${s.id}" data-key="${s.spec_code}">${s.spec_name}</option>`;
        });
        specOptionsTemplate += '<option value="new">+ Thêm mới...</option>';

        document.querySelectorAll('.spec-key-select').forEach(select => {
            select.innerHTML = specOptionsTemplate;
        });

        // 3. process attributes
        let attrsData = resAttrs.data || resAttrs;
        if (!Array.isArray(attrsData)) attrsData = [];

        attrsData.forEach(item => {
            if (!appState.attributes[item.attribute_id]) {
                appState.attributes[item.attribute_id] = {
                    name: item.attribute_name,
                    values: []
                };
                attributesTemplates.attrOptions += `<option value="${item.attribute_id}">${item.attribute_name}</option>`;
            }
            appState.attributes[item.attribute_id].values.push({
                id: item.value_id,
                value: item.attribute_value
            });
        });
        attributesTemplates.attrOptions += '<option value="new">+ Thêm thuộc tính mới...</option>';

        document.querySelectorAll('.attr-select').forEach(select => {
            select.innerHTML = attributesTemplates.attrOptions;
        });

        // 4. kiểm tra mode edit (đã bị loại bỏ trong cơ chế spa)
        isVariantFormInitialized = true;

    } catch (error) {
        console.error('Lỗi tải dữ liệu khởi tạo API:', error);
    }
}

// re-populate dom từ cache — dùng khi spa đã set flag nhưng dom bị reset
function repopulateDropdowns() {
    // sản phẩm gốc
    if (appState.products.length > 0) {
        let cats = new Map();
        let html = '';
        appState.products.forEach(p => {
            const catCode = p.category_code || p.code || '';
            const catName = p.category_name || '';
            if (catCode) cats.set(catCode, catName);
            const pId = p.id || p.product_id;
            html += `<div class="option" data-value="${pId}" data-category="${catCode}" onclick="selectOption(this)">[${catName || catCode}] ${p.name}</div>`;
        });
        const optionsList = document.querySelector('.options-list');
        if (optionsList) optionsList.innerHTML = html;

        const catSelect = document.getElementById('formCategoryFilter') || document.getElementById('categoryFilter');
        if (catSelect) {
            catSelect.innerHTML = '<option value="">-- Tất cả danh mục --</option>' +
                Array.from(cats.entries()).map(([code, name]) => `<option value="${code}">${name}</option>`).join('');
        }
    }
}

async function loadEditVariantData(id) {
    try {
        const res = await fetch(`https:// polygearid.ivi.vn/back-end/api/admin/variant/detail?id=${id}`, { credentials: 'include' });
        const data = await res.json();
        if (data.status === 'success') {
            const v = data.data;

            // basic info
            document.getElementById('variantName').value = v.basic.variant_name;
            document.getElementById('variantSku').value = v.basic.sku;
            document.getElementById('variantPrice').value = v.basic.price;
            if (v.basic.sale_price) document.getElementById('variantSalePrice').value = v.basic.sale_price;
            document.getElementById('variantStock').value = v.basic.stock;
            if (document.getElementById('variantMinStock')) document.getElementById('variantMinStock').value = v.basic.min_stock || 0;
            document.getElementById('variantStatus').value = v.basic.status || 'active';

            // product dropdown
            const pId = v.basic.product_id;
            document.getElementById('productId').value = pId || ''; // luôn gán id để không bị lỗi validate khi lưu

            const option = document.querySelector(`.option[data-value="${pId}"]`);
            let catCode = '';
            if (option) {
                document.getElementById('productId').setAttribute('data-product-name', option.textContent.split('] ')[1] || 'Sản phẩm gốc');
                document.querySelector('.select-header span').textContent = option.textContent;
                catCode = option.getAttribute('data-category');
            } else {
                // nếu option chưa load kịp, tạm thời hiện text mặc định hoặc chờ fetch init data
                document.querySelector('.select-header span').textContent = v.basic.root_product_name || `Sản phẩm #${pId}`;
            }

            // tải lại bộ thông số/thuộc tính chuẩn theo danh mục của sản phẩm gốc này
            if (catCode) {
                await reloadAttributesByProduct(catCode);
            }

            // tinymce (chờ mốc khởi tạo nếu cần)
            setTimeout(() => {
                if (tinymce.get('variantDescription')) {
                    tinymce.get('variantDescription').setContent(v.basic.description || '');
                }
            }, 500);

            // specs
            const builtInSpecs = Array.from(document.querySelectorAll('.spec-row'));
            builtInSpecs.forEach(row => row.remove()); // clear mặc định
            if (v.specs && v.specs.length > 0) {
                v.specs.forEach(spec => {
                    addSpecRow();
                    const row = document.getElementById('specsContainer').lastElementChild;
                    const specSelect = row.querySelector('.spec-key-select');

                    const specMeta = appState.specs.find(s => s.spec_code === spec.spec_code);
                    if (specMeta) {
                        specSelect.value = specMeta.id;
                        handleSpecChange(specSelect);
                    } else {
                        specSelect.value = 'new';
                        handleSpecChange(specSelect);
                        row.querySelector('.new-spec-key-system').value = spec.spec_code;
                        row.querySelector('.new-spec-name').value = spec.spec_name;
                    }
                    row.querySelector('.spec-val').value = spec.spec_value;
                });
            } else {
                addSpecRow();
            }

            // attributes
            const builtInAttrs = Array.from(document.querySelectorAll('.attr-row'));
            builtInAttrs.forEach(row => row.remove());
            if (v.attributes && v.attributes.length > 0) {
                v.attributes.forEach(attrItem => {
                    addAttributeRow();
                    const row = document.getElementById('attributesContainer').lastElementChild;
                    const attrSelect = row.querySelector('.attr-select');

                    if (appState.attributes[attrItem.attribute.id]) {
                        attrSelect.value = attrItem.attribute.id;
                        handleSelectChange(attrSelect, 'attr');

                        const valSelect = row.querySelector('.val-select');
                        const hasVal = appState.attributes[attrItem.attribute.id].values.find(val => val.id == attrItem.value.id);
                        if (hasVal) {
                            valSelect.value = attrItem.value.id;
                        } else {
                            valSelect.value = 'new';
                            handleSelectChange(valSelect, 'val');
                            row.querySelector('.new-val').value = attrItem.value.value;
                        }
                    } else {
                        attrSelect.value = 'new';
                        handleSelectChange(attrSelect, 'attr');
                        row.querySelector('.new-attr').value = attrItem.attribute.name;

                        const valSelect = row.querySelector('.val-select');
                        valSelect.value = 'new';
                        handleSelectChange(valSelect, 'val');
                        row.querySelector('.new-val').value = attrItem.value.value;
                    }
                });
            } else {
                addAttributeRow();
            }

            // khởi tạo trạng thái ảnh
            if (v.basic.main_image_url) {
                mainImageState = { type: 'url', data: v.basic.main_image_url };
            }
            if (v.images && v.images.length > 0) {
                subImagesState = v.images.map(img => ({
                    id: Math.random().toString(36).substr(2, 9),
                    type: 'url',
                    data: img
                }));
            }
            renderMediaSlots();

            const alertMsg = document.createElement('p');

        }
    } catch (e) {
        console.error('Error loadEditVariantData:', e);
    }
}

let isVariantFormInitialized = false;
let currentEditId = null;

window.openAddForm = async function () {
    document.getElementById('listViewContainer').style.display = 'none';
    document.getElementById('formViewContainer').style.display = 'block';

    const formTitle = document.querySelector('#formViewContainer .page-title');
    if (formTitle) formTitle.textContent = 'Thêm Biến Thể Sản Phẩm';

    document.getElementById('variantForm').reset();
    document.getElementById('attributesContainer').innerHTML = '';
    addAttributeRow();
    document.getElementById('specsContainer').innerHTML = '';
    addSpecRow();
    mainImageState = null;
    subImagesState = [];
    renderMediaSlots();
    document.getElementById('productId').value = '';
    document.querySelector('.selected-text').textContent = '-- Chọn một sản phẩm gốc --';

    currentEditId = null;

    if (typeof tinymce !== 'undefined' && tinymce.get('variantDescription')) {
        tinymce.get('variantDescription').setContent('');
    }

    if (!isVariantFormInitialized) {
        await fetchVariantInitData();
    } else {
        repopulateDropdowns(); // dom đã reset, cần vẽ lại từ cache
    }
};

window.openEditForm = async function (id) {
    currentEditId = id;
    document.getElementById('listViewContainer').style.display = 'none';
    document.getElementById('formViewContainer').style.display = 'block';

    const formTitle = document.querySelector('#formViewContainer .page-title');
    if (formTitle) formTitle.textContent = 'Chỉnh sửa Biến thể SKU';

    document.getElementById('variantForm').reset();
    document.getElementById('attributesContainer').innerHTML = '';
    document.getElementById('specsContainer').innerHTML = '';
    mainImageState = null;
    subImagesState = [];
    renderMediaSlots();

    if (typeof tinymce !== 'undefined' && tinymce.get('variantDescription')) {
        tinymce.get('variantDescription').setContent('');
    }

    if (!isVariantFormInitialized) {
        await fetchVariantInitData();
    } else {
        repopulateDropdowns(); // dom đã reset, cần vẽ lại từ cache
    }

    await loadEditVariantData(id);
};

window.closeVariantForm = function () {
    const formView = document.getElementById('formViewContainer');
    const listView = document.getElementById('listViewContainer');
    if (formView && listView) {
        formView.style.display = 'none';
        listView.style.display = 'block';
    } else {
        window.location.href = '/admin/list_variant';
    }
};

// searchable select logic
function toggleDropdown(headerEl) {
    const dropdown = headerEl.nextElementSibling;
    const allHeaders = document.querySelectorAll('.select-header');
    const allDropdowns = document.querySelectorAll('.select-dropdown');

    // close others
    allHeaders.forEach(h => { if (h !== headerEl) h.classList.remove('active'); });
    allDropdowns.forEach(d => { if (d !== dropdown) d.classList.remove('show'); });

    // toggle current
    headerEl.classList.toggle('active');
    dropdown.classList.toggle('show');

    // focus search input on open
    if (dropdown.classList.contains('show')) {
        const input = dropdown.querySelector('#productSearchInput');
        const categorySelect = dropdown.querySelector('#formCategoryFilter');
        if (input) input.value = ''; // clear previous search
        if (categorySelect) categorySelect.value = ''; // reset category filter
        filterProductsCombo(); // reset list
        if (input) setTimeout(() => input.focus(), 50);
    }
}

function filterProductsCombo() {
    const inputEl = document.getElementById('productSearchInput');
    const categoryEl = document.getElementById('formCategoryFilter');

    if (!inputEl || !categoryEl) return;

    const filter = inputEl.value.toLowerCase();
    const categoryFilter = categoryEl.value;

    const dropdown = inputEl.closest('.select-dropdown');
    const options = dropdown.querySelectorAll('.option');
    const noResults = dropdown.querySelector('.no-results');
    let hasVisible = false;

    options.forEach(option => {
        const text = option.textContent.toLowerCase();
        const category = option.getAttribute('data-category') || '';

        const matchesText = text.includes(filter);
        const matchesCategory = categoryFilter === '' || category === categoryFilter;

        if (matchesText && matchesCategory) {
            option.style.display = 'block';
            hasVisible = true;
        } else {
            option.style.display = 'none';
        }
    });

    if (noResults) {
        noResults.style.display = hasVisible ? 'none' : 'block';
    }
}

// keep original filterproducts pointing to the combo version if needed elsewhere
function filterProducts(inputEl) {
    filterProductsCombo();
}

function selectOption(optionEl) {
    const selectWidget = optionEl.closest('.searchable-select');
    const header = selectWidget.querySelector('.select-header');
    const selectedText = header.querySelector('.selected-text');
    const dropdown = selectWidget.querySelector('.select-dropdown');

    // store selected value in hidden input related to this select
    // assumes the hidden input is right next to the .searchable-select
    const hiddenInput = selectWidget.nextElementSibling;

    if (hiddenInput && hiddenInput.tagName === 'INPUT') {
        hiddenInput.value = optionEl.getAttribute('data-value');
        const catCode = optionEl.getAttribute('data-category'); // extract category context

        // store product name for sku generation
        const rawText = optionEl.textContent;
        // strip context names like "[ram] " or "[id: x] "
        const productName = rawText.replace(/^\[.*?\]\s*/, '');
        hiddenInput.setAttribute('data-product-name', productName);

        // gọi api lọc attribute & specs
        if (catCode) {
            const tempHTML = selectedText.innerHTML;
            selectedText.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Đang tải...';
            reloadAttributesByProduct(catCode).then(() => {
                selectedText.textContent = optionEl.textContent;
                hiddenInput.dispatchEvent(new Event('change'));

                // cấu hình specs mặc định theo danh mục
                if (typeof autoLoadSpecsByCategory === 'function') {
                    autoLoadSpecsByCategory(catCode);
                }

                generateSKU();

                // lấy nội dung mẫu từ biến thể khác nếu đang thêm mới
                if (!currentEditId) {
                    fetch(`https:// polygearid.ivi.vn/back-end/api/admin/variant/first-description?product_id=${hiddeninput.value}`)
                        .then(r => r.json())
                        .then(res => {
                            if (res.status === 'success' && res.data) {
                                if (typeof tinymce !== 'undefined' && tinymce.get('variantDescription')) {
                                    tinymce.get('variantDescription').setContent(res.data);
                                }
                            }
                        }).catch(e => console.error("Lỗi lấy mô tả mẫu:", e));
                }
            });
        } else {
            selectedText.textContent = optionEl.textContent;
            hiddenInput.dispatchEvent(new Event('change'));
            generateSKU(); // update sku when product changes

            if (!currentEditId) {
                fetch(`https:// polygearid.ivi.vn/back-end/api/admin/variant/first-description?product_id=${hiddeninput.value}`)
                    .then(r => r.json())
                    .then(res => {
                        if (res.status === 'success' && res.data) {
                            if (typeof tinymce !== 'undefined' && tinymce.get('variantDescription')) {
                                tinymce.get('variantDescription').setContent(res.data);
                            }
                        }
                    }).catch(e => console.error("Lỗi lấy mô tả mẫu:", e));
            }
        }
    } else {
        selectedText.textContent = optionEl.textContent;
    }

    header.classList.add('selected'); // can use for styling if needed

    // close dropdown
    header.classList.remove('active');
    dropdown.classList.remove('show');
}

async function reloadAttributesByProduct(catCode) {
    if (!catCode) return;
    try {
        const [resSpecsRaw, resAttrsRaw] = await Promise.all([
            fetch(`https:// polygearid.ivi.vn/back-end/api/specs`),
            fetch(`https:// polygearid.ivi.vn/back-end/api/attributes`)
        ]);
        const resSpecs = await resSpecsRaw.json();
        const resAttrs = await resAttrsRaw.json();

        // 1. process specs
        let specsData = resSpecs.data || resSpecs;
        if (!Array.isArray(specsData)) specsData = [];
        appState.specs = specsData;

        specOptionsTemplate = '<option value="">-- Chọn thông số --</option>';
        specsData.forEach(s => {
            specOptionsTemplate += `<option value="${s.id}" data-key="${s.spec_code}">${s.spec_name}</option>`;
        });
        specOptionsTemplate += '<option value="new">+ Thêm mới...</option>';

        document.querySelectorAll('.spec-key-select').forEach(select => {
            const oldVal = select.value;
            select.innerHTML = specOptionsTemplate;
            if (Array.from(select.options).find(o => o.value == oldVal)) select.value = oldVal;
        });

        // 2. process attributes
        let attrsData = resAttrs.data || resAttrs;
        if (!Array.isArray(attrsData)) attrsData = [];

        appState.attributes = {};
        attributesTemplates.attrOptions = '<option value="">-- Chọn thuộc tính --</option>';

        attrsData.forEach(item => {
            if (!appState.attributes[item.attribute_id]) {
                appState.attributes[item.attribute_id] = {
                    name: item.attribute_name,
                    values: []
                };
                attributesTemplates.attrOptions += `<option value="${item.attribute_id}">${item.attribute_name}</option>`;
            }
            appState.attributes[item.attribute_id].values.push({
                id: item.value_id,
                value: item.attribute_value
            });
        });
        attributesTemplates.attrOptions += '<option value="new">+ Thêm thuộc tính mới...</option>';

        document.querySelectorAll('.attr-select').forEach(select => {
            const oldVal = select.value;
            select.innerHTML = attributesTemplates.attrOptions;
            if (Array.from(select.options).find(o => o.value == oldVal)) select.value = oldVal;
        });
    } catch (e) {
        console.error('Error reloading attributes by category', e);
    }
}

// close dropdown when clicking outside
document.addEventListener('click', function (e) {
    if (!e.target.closest('.searchable-select')) {
        document.querySelectorAll('.select-header').forEach(h => h.classList.remove('active'));
        document.querySelectorAll('.select-dropdown').forEach(d => d.classList.remove('show'));
    }
});

// handle showing/hiding new input fields based on select choice
function handleSelectChange(selectEl, type) {
    const inputField = selectEl.parentElement.querySelector(type === 'attr' ? '.new-attr' : '.new-val');
    if (selectEl.value === 'new') {
        inputField.style.display = 'block';
        inputField.required = true;
    } else {
        inputField.style.display = 'none';
        inputField.required = false;
        inputField.value = '';
    }

    // auto generate sku when attribute values change
    if (type === 'val') {
        generateSKU();
    }

    // update available options when an attribute type is selected
    if (type === 'attr') {
        const valSelect = selectEl.closest('.attr-row').querySelector('.val-select');
        const selectedAttrId = selectEl.value;
        const newValInput = selectEl.closest('.attr-row').querySelector('.new-val');

        if (selectedAttrId === 'new') {
            valSelect.innerHTML = '<option value="">-- Chọn giá trị --</option><option value="new">+ Thêm giá trị mới...</option>';
        } else if (selectedAttrId && appState.attributes[selectedAttrId]) {
            let ops = '<option value="">-- Chọn giá trị --</option>';
            appState.attributes[selectedAttrId].values.forEach(v => {
                ops += `<option value="${v.id}">${v.value}</option>`;
            });
            ops += '<option value="new">+ Thêm giá trị mới...</option>';
            valSelect.innerHTML = ops;
            if (newValInput) newValInput.style.display = 'none';
        } else {
            valSelect.innerHTML = '<option value="">-- Chọn giá trị --</option><option value="new">+ Thêm giá trị mới...</option>';
        }

        updateAvailableAttributes();
    }
}

// function to disable already selected attributes across all rows
function updateAvailableAttributes() {
    // gather all currently selected attribute values (excluding 'new' and empty)
    const selects = document.querySelectorAll('.attr-select');
    const selectedValues = [];

    selects.forEach(select => {
        if (select.value && select.value !== 'new') {
            selectedValues.push(select.value);
        }
    });

    // go through each select and disable the options that are selected elsewhere
    selects.forEach(select => {
        const options = select.querySelectorAll('option');
        options.forEach(option => {
            // reset disabled state first
            option.disabled = false;

            // if option is a valid attribute id (not empty, not 'new')
            // and it's in the selected list but it's not the currently selected value for this element
            if (option.value && option.value !== 'new' &&
                selectedValues.includes(option.value) &&
                select.value !== option.value) {
                option.disabled = true;
            }
        });
    });
}

// helper function to remove accents and filter select options
function removeAccentsAndLower(str) {
    if (!str) return '';
    return str.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
}

function filterSelectOptions(inputEl, selectClass) {
    const filter = removeAccentsAndLower(inputEl.value);
    const container = inputEl.closest('.col');
    const selectEl = container.querySelector('.' + selectClass);
    if (!selectEl) return;

    Array.from(selectEl.options).forEach(option => {
        if (option.value === "" || option.value === "new") {
            option.style.display = ""; // always show placeholder and new option
            return;
        }
        const text = removeAccentsAndLower(option.text);
        option.style.display = text.includes(filter) ? "" : "none";
    });
}

// add an attribute row
function addAttributeRow() {
    const container = document.getElementById('attributesContainer');
    const row = document.createElement('div');
    row.className = 'dynamic-row attr-row';
    row.innerHTML = `
        <div class="col">
            <label class="form-label text-sm">Thuộc tính (Từ DB)</label>
            <input type="text" class="form-control mb-1 attr-search" placeholder="Tìm thuộc tính..." oninput="filterSelectOptions(this, 'attr-select')" style="font-size: 0.8rem; padding: 0.3rem 0.5rem; max-width: 100%;">
            <select class="form-select attr-select" onchange="handleSelectChange(this, 'attr')">
                ${attributesTemplates.attrOptions}
            </select>
            <input type="text" class="form-control new-input-field new-attr" placeholder="Nhập tên thuộc tính mới">
        </div>
        <div class="col">
            <label class="form-label text-sm">Giá trị tương ứng</label>
            <input type="text" class="form-control mb-1 attr-search" placeholder="Tìm giá trị..." oninput="filterSelectOptions(this, 'val-select')" style="font-size: 0.8rem; padding: 0.3rem 0.5rem; max-width: 100%;">
            <select class="form-select val-select" onchange="handleSelectChange(this, 'val')">
                <option value="">-- Chọn giá trị --</option>
                <option value="new">+ Thêm giá trị mới...</option>
            </select>
            <input type="text" class="form-control new-input-field new-val" placeholder="Nhập giá trị mới" oninput="generateSKU()">
        </div>
        <button type="button" class="btn btn-danger-outline" style="margin-top: 3.5rem;" onclick="removeRowAndUpdate(this)">
            <i class="fa-solid fa-trash"></i>
        </button>
    `;
    container.appendChild(row);
    updateAvailableAttributes(); // update disables on new row
}

// helper to remove row and update logic
function removeRowAndUpdate(btn) {
    btn.parentElement.remove();
    updateAvailableAttributes(); // free up the attribute
    generateSKU(); // update sku
}

// media library & image handling
let mainImageState = null; // { type: 'file' | 'url', data: file | string }
let subImagesState = []; // array of { id, type: 'file'|'url', data: file | string }
let currentMediaTarget = null; // 'main' or 'sub'
let selectedLibraryItems = [];

function renderMediaSlots() {
    // 1. render main image slot
    const mainSlot = document.getElementById('mainImageSlot');
    if (mainImageState) {
        const src = mainImageState.type === 'file' ? URL.createObjectURL(mainImageState.data) : mainImageState.data;
        mainSlot.innerHTML = `
            <img src="${src}">
            <div class="slot-label">ẢNH CHÍNH</div>
            <button type="button" class="btn-remove-slot" onclick="event.stopPropagation(); removeMainImage()">
                <i class="fa-solid fa-xmark"></i>
            </button>
        `;
        mainSlot.style.borderStyle = 'solid';
        mainSlot.style.borderColor = 'var(--primary)';
        mainSlot.style.background = 'white';
    } else {
        mainSlot.innerHTML = `
            <i class="fa-solid fa-cloud-arrow-up text-primary" style="font-size: 1.5rem; margin-bottom: 0.5rem;"></i>
            <span class="slot-action-text">+ Chọn ảnh</span>
            <div class="slot-label">ẢNH CHÍNH</div>
        `;
        mainSlot.style.borderStyle = 'dashed';
        mainSlot.style.borderColor = 'var(--border)';
        mainSlot.style.background = 'var(--bg-color)';
    }

    // 2. render sub image slots
    const subContainer = document.getElementById('subImagesContainer');
    // clear all existing slots except the add button
    const addBtn = subContainer.querySelector('.add-sub-img-btn');
    subContainer.innerHTML = '';

    subImagesState.forEach(img => {
        const div = document.createElement('div');
        div.className = 'variant-img-sub-wrap';
        const src = img.type === 'file' ? URL.createObjectURL(img.data) : img.data;

        div.innerHTML = `
            <div class="variant-img-slot" style="border-style: solid; border-color: var(--border); background: white;">
                <img src="${src}">
            </div>
            <button type="button" class="btn-remove-slot" onclick="removeSubImage('${img.id}')"><i class="fa-solid fa-xmark"></i></button>
        `;
        subContainer.appendChild(div);
    });
    subContainer.appendChild(addBtn);
}

function removeMainImage() {
    mainImageState = null;
    renderMediaSlots();
}

function removeSubImage(id) {
    subImagesState = subImagesState.filter(img => img.id !== id);
    renderMediaSlots();
}

function openMediaLibrary(target) {
    currentMediaTarget = target;
    const modal = document.getElementById('mediaLibraryModal');
    modal.classList.add('active');
    switchMediaTab('upload');
}

function closeMediaLibrary() {
    const modal = document.getElementById('mediaLibraryModal');
    modal.classList.remove('active');
    currentMediaTarget = null;
    selectedLibraryItems = [];
    updateLibrarySelectionUI();
}

function switchMediaTab(tab) {
    document.querySelectorAll('.ml-tab-btn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.ml-tab-content').forEach(c => c.classList.remove('active'));

    if (tab === 'upload') {
        document.querySelector('.ml-tab-btn[onclick*="upload"]').classList.add('active');
        document.getElementById('mlTabUpload').classList.add('active');
    } else {
        document.querySelector('.ml-tab-btn[onclick*="library"]').classList.add('active');
        document.getElementById('mlTabLibrary').classList.add('active');
        loadLibraryImages();
    }
}

async function loadLibraryImages() {
    const pId = document.getElementById('productId').value;
    const grid = document.getElementById('mlGalleryGrid');

    if (!pId) {
        grid.innerHTML = '<div style="grid-column: 1/-1; text-align: center; padding: 2rem; color: var(--text-muted);">Vui lòng chọn Sản phẩm gốc ở thông tin chung trước khi xem thư viện.</div>';
        return;
    }

    grid.innerHTML = '<div style="grid-column: 1/-1; text-align: center; padding: 2rem; color: var(--text-muted);"><i class="fa-solid fa-spinner fa-spin"></i> Đang tải...</div>';

    try {
        const res = await fetch(`https:// polygearid.ivi.vn/back-end/api/products/all-images?product_id=${pid}`);
        const js = await res.json();

        selectedLibraryItems = []; // reset state
        updateLibrarySelectionUI();

        if (js.status === 'success' && js.data && js.data.length > 0) {
            grid.innerHTML = '';
            js.data.forEach(url => {
                const item = document.createElement('div');
                item.className = 'ml-gallery-item';
                item.onclick = () => selectLibraryItem(item, url);
                item.innerHTML = `
                    <div class="ml-checkmark"><i class="fa-solid fa-check"></i></div>
                    <img src="${url}" alt="Library Item">
                `;
                grid.appendChild(item);
            });
        } else {
            grid.innerHTML = '<div style="grid-column: 1/-1; text-align: center; padding: 2rem; color: var(--text-muted);">Chưa có ảnh nào lưu trong máy chủ của sản phẩm này.</div>';
        }
    } catch (e) {
        grid.innerHTML = '<div style="grid-column: 1/-1; text-align: center; padding: 2rem; color: var(--danger);">Lỗi tải ảnh từ máy chủ.</div>';
    }
}

function selectLibraryItem(el, url) {
    if (currentMediaTarget === 'main') {
        document.querySelectorAll('.ml-gallery-item').forEach(i => i.classList.remove('selected'));
        el.classList.add('selected');
        selectedLibraryItems = [url];
    } else {
        if (el.classList.contains('selected')) {
            el.classList.remove('selected');
            selectedLibraryItems = selectedLibraryItems.filter(u => u !== url);
        } else {
            el.classList.add('selected');
            selectedLibraryItems.push(url);
        }
    }
    updateLibrarySelectionUI();
}

function updateLibrarySelectionUI() {
    const txt = document.getElementById('mlStatusText');
    if (selectedLibraryItems && selectedLibraryItems.length > 0) {
        txt.innerHTML = `<span style="color:var(--primary); font-weight: 500;">Đã chọn ${selectedLibraryItems.length} ảnh từ thư viện</span>`;
    } else {
        txt.innerHTML = `Chưa có ảnh nào được chọn`;
    }
}

function handleModalUpload(files) {
    const MAX_SIZE = 2 * 1024 * 1024; // 2mb

    Array.from(files).forEach(file => {
        if (!file.type.match('image.*')) {
            alert(`File "${file.name}" không phải là ảnh!`);
            return;
        }
        if (file.size > MAX_SIZE) {
            alert(`Ảnh "${file.name}" vượt quá dung lượng cho phép (Tối đa 2MB)!`);
            return;
        }

        if (currentMediaTarget === 'main') {
            mainImageState = { type: 'file', data: file };
        } else {
            subImagesState.push({ id: Math.random().toString(36).substr(2, 9), type: 'file', data: file });
        }
    });

    renderMediaSlots();
    closeMediaLibrary();
}

function confirmMediaSelection() {
    if (!selectedLibraryItems || selectedLibraryItems.length === 0) {
        alert("Vui lòng chọn ít nhất 1 ảnh từ thư viện hoặc tải lên ảnh mới.");
        return;
    }

    if (currentMediaTarget === 'main') {
        mainImageState = { type: 'url', data: selectedLibraryItems[0] };
    } else {
        selectedLibraryItems.forEach(url => {
            subImagesState.push({ id: Math.random().toString(36).substr(2, 9), type: 'url', data: url });
        });
    }

    renderMediaSlots();
    closeMediaLibrary();
}

// drag functionality for upload modal box
document.addEventListener('DOMContentLoaded', () => {
    const uploadBox = document.querySelector('.ml-upload-box');
    if (uploadBox) {
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            uploadBox.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) { e.preventDefault(); e.stopPropagation(); }

        ['dragenter', 'dragover'].forEach(eventName => {
            uploadBox.addEventListener(eventName, () => uploadBox.style.borderColor = 'var(--primary)', false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            uploadBox.addEventListener(eventName, () => uploadBox.style.borderColor = 'var(--border)', false);
        });

        uploadBox.addEventListener('drop', (e) => {
            let dt = e.dataTransfer;
            let files = dt.files;
            handleModalUpload(files);
        }, false);
    }
});


// specifications logic
// handle show/hide mapping for specs
function handleSpecChange(selectEl) {
    const row = selectEl.closest('.spec-row');
    const newFieldsWrapper = row.querySelector('.new-spec-fields');
    const nameInput = row.querySelector('.new-spec-name');

    if (selectEl.value === 'new') {
        newFieldsWrapper.style.display = 'flex';
        nameInput.required = true;
    } else {
        newFieldsWrapper.style.display = 'none';
        nameInput.required = false;
        nameInput.value = '';
    }
    updateAvailableSpecs();
}

// disable duplicate specs
function updateAvailableSpecs() {
    const selects = document.querySelectorAll('.spec-key-select');
    const selectedValues = [];

    selects.forEach(select => {
        if (select.value && select.value !== 'new') {
            selectedValues.push(select.value);
        }
    });

    selects.forEach(select => {
        const options = select.querySelectorAll('option');
        options.forEach(option => {
            option.disabled = false;
            if (option.value && option.value !== 'new' &&
                selectedValues.includes(option.value) &&
                select.value !== option.value) {
                option.disabled = true;
            }
        });
    });
}

// add a specification row
function addSpecRow() {
    const container = document.getElementById('specsContainer');
    const row = document.createElement('div');
    row.className = 'dynamic-row spec-row';
    row.innerHTML = `
        <div class="col">
            <label class="form-label text-sm">Tên thông số (Từ DB)</label>
            <input type="text" class="form-control mb-1 attr-search" placeholder="Tìm thông số..." oninput="filterSelectOptions(this, 'spec-key-select')" style="font-size: 0.8rem; padding: 0.3rem 0.5rem; max-width: 100%;">
            <select class="form-select spec-key-select" onchange="handleSpecChange(this)">
                ${specOptionsTemplate}
            </select>
            <div class="new-spec-fields" style="display: none; margin-top: 0.5rem; gap: 0.5rem; flex-direction: column;">
                <input type="hidden" class="new-spec-key-system">
                <input type="text" class="form-control new-spec-name" style="border-color: var(--primary); background: var(--primary-light); color: var(--primary);" placeholder="Tên hiển thị (Tiếng Việt) VD: Trọng lượng" oninput="autoGenSpecKey(this)">
                <div style="font-size:0.72rem; color:var(--text-muted); padding: 0.2rem 0.5rem; background:#f8fafc; border:1px solid var(--border); border-radius:6px;" class="spec-key-preview"><i class="fa-solid fa-key" style="font-size:0.68rem;"></i> Key: <code class="spec-key-display">--</code></div>
            </div>
        </div>
        <div class="col">
            <label class="form-label text-sm">Giá trị thông số</label>
            <input type="text" class="form-control spec-val" placeholder="Ví dụ: 1kg, 120cm, 100% Cotton" style="margin-top: 1.95rem" required>
        </div>
        <button type="button" class="btn btn-danger-outline" style="margin-top: 3.65rem;" onclick="removeSpecRow(this)">
            <i class="fa-solid fa-trash"></i>
        </button>
    `;
    container.appendChild(row);
    updateAvailableSpecs();
}

function removeSpecRow(btn) {
    btn.parentElement.remove();
    updateAvailableSpecs();
}

// auto-generate spec key from vietnamese display name
function autoGenSpecKey(inputEl) {
    const row = inputEl.closest('.spec-row');
    const preview = row.querySelector('.spec-key-display');
    const systemKeyInput = row.querySelector('.new-spec-key-system');
    const key = inputEl.value
        .normalize('NFD').replace(/[\u0300-\u036f]/g, '') // remove diacritics
        .replace(/đ/g, 'd').replace(/Đ/g, 'd')          // handle đ/đ
        .toLowerCase()
        .replace(/[^a-z0-9\s]/g, '')                     // remove special chars
        .trim()
        .replace(/\s+/g, '_');                           // spaces -> underscores

    if (preview) preview.textContent = key || '--';
    if (systemKeyInput) systemKeyInput.value = key;
}

// helper to slugify text to sku format (remove diacritics, uppercase, replace spaces with hyphens)
function createSlug(str) {
    if (!str) return '';
    return str.normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '') // remove vietnamese diacritics
        .replace(/đ/g, 'd').replace(/Đ/g, 'D')
        .toUpperCase()
        .replace(/[^A-Z0-9\s-]/g, '') // remove special chars
        .trim()
        .replace(/\s+/g, '-'); // replace spaces with hyphens
}

// generate sku automatically
function generateSKU() {
    const productIdInput = document.getElementById('productId');
    const productName = productIdInput ? productIdInput.getAttribute('data-product-name') || '' : '';
    const variantName = document.getElementById('variantName') ? document.getElementById('variantName').value : '';

    // gather all attribute values
    const attrValues = [];
    document.querySelectorAll('.attr-row').forEach(row => {
        const valSelect = row.querySelector('.val-select');
        let val = '';
        if (valSelect && valSelect.value === 'new') {
            val = row.querySelector('.new-val').value;
        } else if (valSelect && valSelect.value !== '') {
            val = valSelect.options[valSelect.selectedIndex].text;
        }
        if (val.trim()) {
            attrValues.push(val.trim());
        }
    });

    // build parts list
    const parts = [
        productName,
        variantName,
        ...attrValues
    ].filter(p => p !== ''); // exclude empty parts

    // combine and process
    const skuString = parts.join(' '); // join with space first
    const generatedSku = createSlug(skuString);

    const skuInput = document.getElementById('variantSku');
    if (skuInput) {
        skuInput.value = generatedSku;
    }
}

// handle json building on submit
document.getElementById('variantForm').addEventListener('submit', function (e) {
    e.preventDefault();

    const priceEl = document.getElementById('variantPrice');
    const salePriceEl = document.getElementById('variantSalePrice');
    const price = parseFloat(priceEl.value) || 0;
    const salePrice = parseFloat(salePriceEl.value) || 0;
    const stock = parseInt(document.getElementById('variantStock').value) || 0;

    if (price < 0) {
        alert('Giá gốc không được là số âm!');
        priceEl.focus();
        return;
    }

    if (salePrice < 0) {
        alert('Giá khuyến mãi không được là số âm!');
        salePriceEl.focus();
        return;
    }

    if (salePrice > 0 && salePrice >= price) {
        alert('Giá khuyến mãi không được lớn hơn hoặc bằng Giá gốc!');
        salePriceEl.focus();
        return;
    }

    if (stock < 0) {
        alert('Số lượng tồn kho không được là số âm!');
        document.getElementById('variantStock').focus();
        return;
    }

    // basic variant fields
    const payload = {
        product_id: document.getElementById('productId').value,
        variant_name: document.getElementById('variantName').value,
        sku: document.getElementById('variantSku').value,
        price: parseFloat(document.getElementById('variantPrice').value) || 0,
        sale_price: parseFloat(document.getElementById('variantSalePrice').value) || 0,
        stock: parseInt(document.getElementById('variantStock').value) || 0,
        view: 0,
        sold: 0,
        status: document.getElementById('variantStatus').value,
        description: tinymce.get('variantDescription') ? tinymce.get('variantDescription').getContent() : '',
        main_image_url: mainImageState ? (mainImageState.type === 'file' ? mainImageState.data.name : mainImageState.data) : '',
        images: subImagesState.map(i => i.type === 'file' ? i.data.name : i.data),
        attributes: [],
        specs: []
    };

    // tạo timestamp hiện tại cho bảng product_variants
    const nowTimestamp = new Date().toISOString(); // hoặc format yyyy-mm-dd hh:mm:ss tuỳ backend yêu cầu
    payload.create_at = nowTimestamp;
    payload.delete_at = null; // rỗng hoặc null

    // process attributes form
    document.querySelectorAll('.attr-row').forEach(row => {
        const attrSelect = row.querySelector('.attr-select');
        const valSelect = row.querySelector('.val-select');

        let attrData = { id: null, name: null, is_new: false };
        let valData = { id: null, value: null, is_new: false };

        // get attribute info
        if (attrSelect.value === 'new') {
            attrData.name = row.querySelector('.new-attr').value.trim();
            attrData.is_new = true;
        } else if (attrSelect.value !== '') {
            attrData.id = attrSelect.value;
            attrData.name = attrSelect.options[attrSelect.selectedIndex].text;
        }

        // get value info
        if (valSelect.value === 'new') {
            valData.value = row.querySelector('.new-val').value.trim();
            valData.is_new = true;
        } else if (valSelect.value !== '') {
            valData.id = valSelect.value;
            valData.value = valSelect.options[valSelect.selectedIndex].text;
        }

        // only add if at least an attribute and value are present logically
        if ((attrData.id || attrData.name) && (valData.id || valData.value)) {
            payload.attributes.push({ attribute: attrData, value: valData });
        }
    });

    // process specs form
    document.querySelectorAll('.spec-row').forEach(row => {
        const specSelect = row.querySelector('.spec-key-select');
        const val = row.querySelector('.spec-val').value.trim();

        let specData = { id: null, key: null, name: null, is_new: false };

        if (specSelect.value === 'new') {
            specData.name = row.querySelector('.new-spec-name').value.trim();
            const systemKeyInput = row.querySelector('.new-spec-key-system');

            if (systemKeyInput && systemKeyInput.value.trim()) {
                specData.key = systemKeyInput.value.trim();
            } else {
                // fallback auto-generate key from display name
                specData.key = specData.name
                    .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
                    .replace(/đ/g, 'd').replace(/Đ/g, 'd')
                    .toLowerCase()
                    .replace(/[^a-z0-9\s]/g, '')
                    .trim()
                    .replace(/\s+/g, '_');
            }
            specData.is_new = true;
        } else if (specSelect.value !== '') {
            specData.id = specSelect.value;
            specData.name = specSelect.options[specSelect.selectedIndex].text;
            specData.key = specSelect.options[specSelect.selectedIndex].getAttribute('data-key');
        }

        if ((specData.id || specData.key) && val) {
            payload.specs.push({
                // map to db: spec_code (english), spec_name (vietnamese) and spec_value based on erd
                spec_code: specData.key,
                spec_name: specData.name,
                spec_value: val,
                // passing extra meta if backend needs it to create new specs
                _is_new: specData.is_new,
                _spec_id: specData.id
            });
        }
    });

    console.log('--- DỮ LIỆU BIẾN THỂ (Variant JSON Payload) ---');
    console.log(JSON.stringify(payload, null, 2));

    // gửi api fetch bằng formdata (có chứa file)
    // map to db: các key (product_id, sku, price, stock, status) tương ứng với bảng product_variants

    let formData = new FormData();
    const editId = currentEditId;
    if (editId) {
        formData.append('variant_id', editId);
    }

    formData.append('product_id', payload.product_id);
    formData.append('variant_name', payload.variant_name);
    formData.append('sku', payload.sku);
    formData.append('price', payload.price);
    formData.append('sale_price', payload.sale_price);
    formData.append('stock', payload.stock);
    formData.append('view', payload.view);
    formData.append('sold', payload.sold);
    formData.append('status', payload.status);
    formData.append('description', payload.description);
    // bổ sung các mốc thời gian (map to db: create_at, delete_at)
    formData.append('create_at', payload.create_at);
    formData.append('delete_at', payload.delete_at); // rỗng hoặc có thể đẩy string "null" tùy logic api backend

    // appending arrays of objects as stringified json
    // backend needs to json.decode() these fields
    formData.append('attributes', JSON.stringify(payload.attributes)); // bảng variant_attribute_values
    formData.append('variant_specs', JSON.stringify(payload.specs));   // bảng variant_specs

    // đính kèm danh sách file / link lên form_data
    if (mainImageState) {
        if (mainImageState.type === 'file') {
            formData.append('main_image_url', mainImageState.data);
        } else {
            formData.append('main_image_url_existing', mainImageState.data); // link ảnh cũ
        }
    }

    let subImageUrls = [];
    subImagesState.forEach(img => {
        if (img.type === 'file') {
            formData.append('images[]', img.data);
        } else {
            subImageUrls.push(img.data);
        }
    });

    if (subImageUrls.length > 0) {
        formData.append('images_existing', JSON.stringify(subImageUrls));
    }

    /* gửi fetch thực tế đến api */
    const apiEndpoint = editId ? 'https:// polygearid.ivi.vn/back-end/api/admin/variant/update' : 'https://polygearid.ivi.vn/back-end/api/products/variant';

    fetch(apiEndpoint, {
        credentials: 'include',
        method: 'POST',
        // không truyền content-type header khi dùng formdata, fetch sẽ tự động
        // set content-type: multipart/form-data kèm theo boundary string.
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            console.log('Server response:', data);
            if (data.status === 'success') {
                alert('Đã lưu dữ liệu biến thể thành công!');
                if (typeof fetchVariants === 'function') fetchVariants();
                closeVariantForm();
            } else {
                alert('Lỗi từ hệ thống: ' + (data.message || 'Không thể lưu biến thể'));
            }
        })
        .catch(error => {
            console.error('Error submitting variant:', error);
            alert('Lỗi kết nối tới Server API!');
        });
});

// khởi tạo tinymce editor
document.addEventListener('DOMContentLoaded', () => {
    if (typeof tinymce !== 'undefined') {
        tinymce.init({
            selector: '#variantDescription',
            plugins: [
                'image', 'anchor', 'autolink', 'charmap', 'codesample', 'emoticons', 'link', 'lists', 'media', 'searchreplace', 'table', 'visualblocks', 'wordcount'
            ],
            toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | image link media table | align lineheight | numlist bullist indent outdent | emoticons charmap | removeformat',
            // cấu hình để dễ dàng tùy chỉnh ảnh
            image_dimensions: true,
            image_advtab: true,
            image_class_list: [
                { title: 'Mặc định', value: '' },
                { title: 'Ảnh tròn', value: 'rounded-full' },
                { title: 'Bo góc nhẹ', value: 'rounded-lg' },
                { title: 'Full Width', value: 'w-full' }
            ],
            // style trong editor: cho phép kéo giãn nhưng không tràn khung, thêm viền khi chọn để dễ thấy handle
            content_style: `
                body { font-family:Inter,sans-serif; font-size:14px } 
                img { max-width: 100%; height: auto; cursor: pointer; border: 1px solid transparent; }
                img:hover { border: 1px dashed #2563eb; }
                img.mce-item-selected { border: 2px solid #2563eb !important; }
            `,

            setup: function (editor) {
                // tự động khống chế ảnh "quá khổ" khi mới thêm vào, nhưng vẫn cho phép user chỉnh lại
                editor.on('NodeChange', function (e) {
                    if (e.element.nodeName === 'IMG') {
                        // nếu ảnh chưa có width (vừa drop vào) thì cho một kích thước khởi tạo vừa phải (ví dụ 500px)
                        // để không bị "to chà bá" gây khó chịu, nhưng không khóa cứng 300x300 như trước.
                        if (!e.element.getAttribute('width') && !e.element.style.width) {
                            editor.dom.setAttrib(e.element, 'width', '500');
                            editor.dom.setStyle(e.element, 'height', 'auto');
                        }
                    }
                });
            },

            // thêm đoạn này vào để tắt chế độ base64 và bật upload server
            images_upload_url: 'https:// polygearid.ivi.vn/back-end/api/upload/post/img',
            automatic_uploads: true,
            file_picker_types: 'image'
        });
    }
});
