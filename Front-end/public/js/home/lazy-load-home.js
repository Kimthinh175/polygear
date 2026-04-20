/**
 * Lazy loading for homepage category sections
 */
(function() {
    const API_BASE = "https:// polygearid.ivi.vn/back-end/api";
    const loadedTabs = new Set();

    async function fetchCategoryProducts(catCode, containerId) {
        const container = document.getElementById(containerId);
        if (!container) return;

        const productListTarget = container.querySelector('.product-list-target');
        const skeleton = container.querySelector('.skeleton-container');
        const swiperContainer = container.querySelector('.swiper');

        try {
            const response = await fetch(`${API_BASE}/products/category?category=${catCode}`);
            const data = await response.json();
            const products = data.info || [];

            if (products.length === 0) {
                productListTarget.innerHTML = '<div class="w-full text-center py-10 text-slate-400">Không có sản phẩm nào trong danh mục này</div>';
            } else {
                const html = products.slice(0, 8).map(p => {
                    const price = p.price.toLocaleString('vi-VN');
                    const salePrice = (p.sale_price && p.sale_price > 0) ? p.sale_price.toLocaleString('vi-VN') : null;
                    const discount = (p.sale_price && p.sale_price > 0 && p.price > 0) ? Math.round((1 - p.sale_price / p.price) * 100) : 0;
                    const shortSpecs = p.specs ? p.specs.slice(0, 2).map(s => s.spec_value).join(', ') : '';

                    return `
                        <div class="swiper-slide h-full">
                            <a href="/detail/${p.sku}" class="group h-[408px] bg-white rounded-xl border border-slate-200 overflow-hidden hover:border-blue-500/40 hover:shadow-xl transition-all flex flex-col">
                                <div class="aspect-[4/3] relative overflow-hidden bg-slate-50 flex items-center justify-center p-4">
                                    <img alt="${p.name}" class="w-full h-full object-contain group-hover:scale-105 transition-transform duration-500" src="${p.main_image_url}" />
                                    ${discount > 0 ? `
                                        <div class="absolute top-0 right-0 z-10">
                                            <div class="bg-red-500 text-white px-3 py-1.5 rounded-bl-xl shadow-sm flex flex-col items-center justify-center">
                                                <span class="text-[8px] font-bold uppercase tracking-widest opacity-90 leading-none mb-0.5">Giảm</span>
                                                <span class="text-sm font-black leading-none">${discount}%</span>
                                            </div>
                                        </div>` : ''}
                                </div>
                                <div class="p-4 flex-1 flex flex-col">
                                    <h3 class="font-bold text-sm mb-1 text-slate-900 group-hover:text-blue-600 transition-colors line-clamp-2 min-h-[40px]">${p.name}</h3>
                                    <p class="min-h-[32px] text-xs text-slate-500 mb-4 line-clamp-2 leading-relaxed">${shortSpecs}</p>
                                    <div class="mt-auto flex items-center justify-between pt-3 border-t border-slate-100">
                                        <div class="flex flex-col">
                                            ${salePrice ? `
                                                <span class="text-[10px] text-gray-400 line-through">${price}₫</span>
                                                <span class="text-base font-extrabold text-slate-900 leading-tight">${salePrice}₫</span>
                                            ` : `<span class="text-base font-extrabold text-slate-900 leading-tight">${price}₫</span>`}
                                        </div>
                                        <button data-sku="${p.sku}" data-name="${p.name}"
                                            data-price="${(p.sale_price && p.sale_price > 0) ? p.sale_price : p.price}"
                                            data-origin="${p.price}" data-img="${p.main_image_url}"
                                            class="add-to-cart size-9 flex items-center justify-center bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-600 hover:text-white transition-all">
                                            <span class="material-symbols-outlined text-xl">shopping_cart</span>
                                        </button>
                                    </div>
                                </div>
                            </a>
                        </div>`;
                }).join('');

                productListTarget.innerHTML = html;
                
                // initialize swiper for this tab
                new Swiper(swiperContainer, {
                    slidesPerView: 2,
                    spaceBetween: 16,
                    pagination: { el: swiperContainer.querySelector('.swiper-pagination'), clickable: true },
                    breakpoints: {
                        1024: { slidesPerView: 4, spaceBetween: 24 }
                    },
                    observer: true,
                    observeParents: true
                });
            }

            skeleton.classList.add('hidden');
            swiperContainer.classList.remove('hidden');
            loadedTabs.add(containerId);

        } catch (error) {
            console.error("Error loading products:", error);
            productListTarget.innerHTML = '<div class="w-full text-center py-10 text-red-400">Không thể tải sản phẩm. Vui lòng thử lại sau.</div>';
            skeleton.classList.add('hidden');
        }
    }

    window.switchCategoryTab = function(sectionId, index) {
        const buttons = document.querySelectorAll(`.cat-tab-btn-${sectionId}`);
        const contents = document.querySelectorAll(`.cat-tab-content-${sectionId}`);

        buttons.forEach((btn, i) => {
            if (i === index) {
                btn.classList.add('bg-white', 'text-blue-600', 'shadow-sm');
                btn.classList.remove('text-slate-500', 'hover:text-slate-800');
            } else {
                btn.classList.remove('bg-white', 'text-blue-600', 'shadow-sm');
                btn.classList.add('text-slate-500', 'hover:text-slate-800');
            }
        });

        contents.forEach((content, i) => {
            if (i === index) {
                content.classList.remove('hidden');
                const catCode = content.getAttribute('data-cat-code');
                const containerId = content.id;

                if (!loadedTabs.has(containerId)) {
                    fetchCategoryProducts(catCode, containerId);
                }

                // update "view all" button
                const viewAllBtn = document.getElementById(`view-all-${sectionId}`);
                if (viewAllBtn && buttons[index]) {
                    const catCode = buttons[index].getAttribute('data-code');
                    const catName = buttons[index].getAttribute('data-name');
                    viewAllBtn.href = `/category/${catCode}`;
                    const nameSpan = viewAllBtn.querySelector(`.cat-name-display-${sectionId}`);
                    if (nameSpan) nameSpan.innerText = catName;
                }
            } else {
                content.classList.add('hidden');
            }
        });
    };

    // intersection observer to start loading when visible
    const observerOptions = {
        root: null,
        rootMargin: '100px',
        threshold: 0.1
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const sectionId = entry.target.getAttribute('data-section-id');
                // auto-load the first tab of this section
                window.switchCategoryTab(sectionId, 0);
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);

    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.lazy-cat-section').forEach(section => {
            observer.observe(section);
        });
    });

})();
