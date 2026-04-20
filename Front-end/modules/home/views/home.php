<h1 class="sr-only">PolyGear - Cửa hàng Linh Kiện Máy Tính, Build PC & Phụ kiện máy tính chính hãng</h1>
<link href="https:// fonts.googleapis.com/css2?family=inter:wght@300;400;500;600;700;800;900&amp;display=swap"
    rel="stylesheet" />
<!-- Material Symbols -->
<link href="https:// fonts.googleapis.com/css2?family=material+symbols+outlined:wght,fill@100..700,0..1&amp;display=swap"
    rel="stylesheet" />
<!-- Tailwind CSS Local -->

<link rel="stylesheet" href="css/layout/swiper.css">
<script src="js/layout/swiper.js"></script>

<style data-purpose="custom-layout">
    body {
        font-family: 'Inter', sans-serif;
        background-color: #F5F5F7;
        color: #1a1a1a;
    }

    .company-logo {
        font-size: 1.5rem;
        font-weight: 800;
        color: #2563eb;
    }

    .hover-scale {
        transition: transform 0.2s ease-in-out;
    }

    .hover-scale:hover {
        transform: scale(1.02);
    }

    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .max-w-7xl-custom {
        max-width: 80rem;
    }

    .material-symbols-outlined {
        font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
    }

    /* product sections */
    .product-section {
        position: relative;
        border-radius: 20px;
        overflow: hidden;
        padding: 32px 36px;
        margin: 8px 0;
    }

    .product-section-bg {
        display: none;
    }

    .product-section::before {
        display: none;
    }

    .product-section::after {
        display: none;
    }

    .product-section-content {
        position: relative;
        z-index: 1;
    }

    /* featured: clean white with blue left accent bar */
    .product-section-featured {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-left: 4px solid #2563eb;
        box-shadow: 0 2px 16px rgba(0, 0, 0, 0.06);
    }

    /* new: clean white with emerald left accent bar */
    .product-section-new {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-left: 4px solid #10b981;
        box-shadow: 0 2px 16px rgba(0, 0, 0, 0.06);
    }

    /* sale: soft warm red bg to keep urgency */
    .product-section-sale {
        background: linear-gradient(135deg, #fff1f1 0%, #ffe4e4 100%);
        border: 1px solid #fecaca;
        border-left: 4px solid #ef4444;
        box-shadow: 0 2px 16px rgba(239, 68, 68, 0.08);
    }

    /* badge */
    .section-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: 10px;
        font-weight: 800;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        padding: 4px 12px;
        border-radius: 999px;
        margin-bottom: 12px;
    }

    .badge-featured {
        background: rgba(37, 99, 235, 0.08);
        color: #2563eb;
        border: 1px solid rgba(37, 99, 235, 0.2);
    }

    .badge-new {
        background: rgba(16, 185, 129, 0.08);
        color: #059669;
        border: 1px solid rgba(16, 185, 129, 0.2);
    }

    .badge-sale {
        background: rgba(239, 68, 68, 0.1);
        color: #dc2626;
        border: 1px solid rgba(239, 68, 68, 0.2);
    }

    /* text */
    .product-section-featured h2,
    .product-section-new h2 {
        color: #0f172a;
    }

    .product-section-sale h2 {
        color: #7f1d1d;
    }

    /* nav buttons */
    .swiper-button-prev-custom,
    .swiper-button-next-custom {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        z-index: 10;
        opacity: 0;
        transition: all 0.3s ease;
    }

    .product-section:hover .swiper-button-prev-custom,
    .product-section:hover .swiper-button-next-custom {
        opacity: 1;
    }

    .product-section .swiper-button-prev-custom,
    .product-section .swiper-button-next-custom {
        background: #fff;
        color: #334155;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .product-section .swiper-button-prev-custom:hover,
    .product-section .swiper-button-next-custom:hover {
        background: #f1f5f9;
        border-color: #cbd5e1;
    }

    .product-section-sale .swiper-button-prev-custom,
    .product-section-sale .swiper-button-next-custom {
        color: #dc2626;
        border-color: #fecaca;
    }

    .swiper-button-prev-custom {
        left: -20px;
    }

    .swiper-button-next-custom {
        right: -20px;
    }

    /* cards inside sections */
    .product-section .swiper-slide a {
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.07);
        border-color: #e2e8f0 !important;
        transition: all 0.25s ease;
    }

    .product-section .swiper-slide a:hover {
        box-shadow: 0 8px 28px rgba(0, 0, 0, 0.12);
        border-color: #bfdbfe !important;
        transform: translateY(-3px);
    }

    .product-section-sale .swiper-slide a:hover {
        border-color: #fca5a5 !important;
    }

    @media (max-width: 1280px) {
        .swiper-button-prev-custom {
            left: 0;
        }

        .swiper-button-next-custom {
            right: 0;
        }

        .product-section {
            padding: 24px 20px;
        }
    }
</style>


<body class="antialiased font-display">
    <main>
        <section class="container py-4">
            <div class="grid grid-cols-10 gap-4 h-[500px]">
                <!-- Large Primary Banner (7/10) -->
                <div class="col-span-10 lg:col-span-7 relative overflow-hidden rounded-3xl h-full">
                    <!-- Background Slider -->
                    <div id="heroSlider" class="absolute inset-0 flex transition-transform duration-700 ease-in-out">
                        <?php
                        $mainBanners = array_filter($data['banners'], fn($b) => $b['type'] === 'main_slider');
                        if (empty($mainBanners)): ?>
                            <div class="min-w-full h-full relative">
                                <img alt="Default Banner" class="w-full h-full object-cover"
                                    src="https:// images.unsplash.com/photo-1593642532400-2682810df593?auto=format&fit=crop&w=1600&q=80" />
                                <div class="absolute inset-0 bg-black/30"></div>
                            </div>
                        <?php else: ?>
                            <?php foreach ($mainBanners as $b): ?>
                                <div class="min-w-full h-full relative cursor-pointer overflow-hidden"
                                    onclick="<?= !empty($b['link_url']) ? "window.location.href='{$b['link_url']}'" : "" ?>">
                                    <!-- Blurred Background -->
                                    <img src="<?= $b['image_url'] ?>"
                                        class="absolute inset-0 w-full h-full object-cover blur-2xl opacity-40 scale-125 pointer-events-none">
                                    <!-- Main Image -->
                                    <img alt="<?= htmlspecialchars($b['title']) ?>"
                                        class="relative w-full h-full object-contain z-10" src="<?= $b['image_url'] ?>" />
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <!-- Overlay for dark gradient -->
                    <div
                        class="absolute inset-0 bg-gradient-to-r from-black/20 via-transparent to-transparent pointer-events-none">
                    </div>

                    <!-- Navigation Controls -->
                    <button onclick="prevSlide()"
                        class="absolute left-4 top-1/2 -translate-y-1/2 z-30 w-12 h-12 flex items-center justify-center rounded-full bg-white/80 text-gray-700 shadow-lg hover:bg-white transition-all focus:outline-none">
                        <span class="material-symbols-outlined">chevron_left</span>
                    </button>
                    <button onclick="nextSlide()"
                        class="absolute right-4 top-1/2 -translate-y-1/2 z-30 w-12 h-12 flex items-center justify-center rounded-full bg-white/80 text-gray-700 shadow-lg hover:bg-white transition-all focus:outline-none">
                        <span class="material-symbols-outlined">chevron_right</span>
                    </button>
                </div>

                <!-- Side Promotional Ads (3/10) -->
                <div class="hidden lg:flex lg:col-span-3 flex-col gap-4 h-full">
                    <?php
                    $sideTop = array_filter($data['banners'], fn($b) => $b['type'] === 'side_promo_top');
                    $sideTop = !empty($sideTop) ? array_values($sideTop)[0] : null;

                    $sideBottom = array_filter($data['banners'], fn($b) => $b['type'] === 'side_promo_bottom');
                    $sideBottom = !empty($sideBottom) ? array_values($sideBottom)[0] : null;
                    ?>

                    <!-- Top Ad Block -->
                    <div class="flex-1 bg-slate-900 rounded-3xl overflow-hidden relative group cursor-pointer"
                        onclick="<?= $sideTop && !empty($sideTop['link_url']) ? "window.location.href='{$sideTop['link_url']}'" : "" ?>">
                        <?php if ($sideTop): ?>
                            <!-- Blurred Background -->
                            <img src="<?= $sideTop['image_url'] ?>"
                                class="absolute inset-0 w-full h-full object-cover blur-xl opacity-40 scale-110 pointer-events-none">
                            <!-- Main Image -->
                            <img alt="Promo Top"
                                class="relative w-full h-full object-contain z-10 group-hover:scale-105 transition-transform duration-500"
                                src="<?= $sideTop['image_url'] ?>" />
                        <?php else: ?>
                            <img alt="Promo Top" class="absolute inset-0 w-full h-full object-cover"
                                src="https:// images.unsplash.com/photo-1541807084-5c52b6b3adef?auto=format&fit=crop&w=800&q=80" />
                        <?php endif; ?>
                    </div>

                    <!-- Bottom Ad Block -->
                    <div class="flex-1 rounded-3xl overflow-hidden relative group cursor-pointer"
                        onclick="<?= $sideBottom && !empty($sideBottom['link_url']) ? "window.location.href='{$sideBottom['link_url']}'" : "" ?>">
                        <?php if ($sideBottom): ?>
                            <!-- Blurred Background -->
                            <img src="<?= $sideBottom['image_url'] ?>"
                                class="absolute inset-0 w-full h-full object-cover blur-xl opacity-40 scale-110 pointer-events-none">
                            <!-- Main Image -->
                            <img alt="Promo Bottom"
                                class="relative w-full h-full object-contain z-10 group-hover:scale-105 transition-transform duration-500"
                                src="<?= $sideBottom['image_url'] ?>" />
                        <?php else: ?>
                            <img alt="Promo Bottom" class="absolute inset-0 w-full h-full object-cover"
                                src="https:// images.unsplash.com/photo-1518770660439-4636190af475?auto=format&fit=crop&w=800&q=80" />
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>


        <!-- END: HeroSection -->


        <section class="container py-4">
            <div class="flex items-center justify-between mb-8">
                <h2 class="text-2xl font-bold text-slate-900">Danh mục sản phẩm</h2>
                <a class="text-primary text-sm font-medium hover:underline" href="/category">Xem tất cả</a>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                <!-- Category Item -->
                <?php $i = 0;
                foreach ($data['category'] as $cate): ?>
                    <?php $i++; ?>
                    <a href="/category/<?= $cate['code'] ?>"
                        class="bg-white border border-gray-100 rounded-2xl p-6 flex flex-col items-center text-center hover-scale shadow-sm cursor-pointer hover:border-primary/20 transition-all">
                        <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center text-primary mb-4">
                            <img style="max-width:70px;" src="<?= $cate['img'] ?>" alt="">
                        </div>
                        <span class="font-bold text-gray-900"><?= $cate['name'] ?></span>
                    </a>
                    <?php if ($i == 6)
                        break; ?>
                <?php endforeach; ?>
            </div>
        </section>
        <!-- END: ProductCategories -->

        <?php if (!empty($data['promotions'])): ?>
            <!-- BEGIN: Khuyến Mãi Đặc Biệt -->
            <section class="container py-2 my-2">
                <div
                    style="border-radius: 20px; overflow: hidden; border: 1px solid #fca5a5; box-shadow: 0 4px 20px rgba(239,68,68,0.12);">

                    <!-- Dark Red Header Bar -->
                    <div
                        style="background: linear-gradient(135deg, #991b1b 0%, #b91c1c 50%, #dc2626 100%); padding: 20px 28px;">
                        <div class="flex flex-wrap items-center justify-between gap-4">
                            <div class="flex items-center gap-4 flex-wrap">
                                <!-- Flash Sale Badge -->
                                <div
                                    style="display:inline-flex; align-items:center; gap:5px; font-size:10px; font-weight:800; letter-spacing:0.12em; text-transform:uppercase; padding:4px 12px; border-radius:999px; background:rgba(255,255,255,0.15); color:#fff; border:1px solid rgba(255,255,255,0.3);">
                                    <svg width="11" height="11" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M13 2l-9 11h7l-1 9 9-11h-7z" />
                                    </svg>
                                    FLASH SALE
                                </div>

                                <!-- Campaign Title -->
                                <h2 id="promoCampaignTitle"
                                    style="color:#fff; font-size:1.4rem; font-weight:800; margin:0;">
                                    <?= htmlspecialchars($data['promotions'][0]['name']) ?>
                                </h2>

                                <!-- Countdown Timer -->
                                <div class="flex items-center gap-2" id="promoCountdownWrapper" style="display:none;">
                                    <span style="color:rgba(255,255,255,0.75); font-size:12px; font-weight:600;">Kết thúc
                                        sau:</span>
                                    <div class="flex gap-1.5">
                                        <div id="cd-days-wrapper"
                                            style="display:none; flex-direction:column; align-items:center; background:rgba(0,0,0,0.25); border-radius:8px; padding:6px 10px; min-width:44px;">
                                            <span id="cd-days"
                                                style="font-size:1.25rem; font-weight:900; color:#fff; line-height:1;">00</span>
                                            <span
                                                style="font-size:9px; font-weight:700; color:#fca5a5; text-transform:uppercase; margin-top:2px;">Ngày</span>
                                        </div>
                                        <span id="cd-days-sep"
                                            style="display:none; color:#fff; font-weight:900; font-size:1.25rem; align-self:flex-start; margin-top:4px;">:</span>

                                        <div
                                            style="display:flex; flex-direction:column; align-items:center; background:rgba(0,0,0,0.25); border-radius:8px; padding:6px 10px; min-width:44px;">
                                            <span id="cd-hours"
                                                style="font-size:1.25rem; font-weight:900; color:#fff; line-height:1;">00</span>
                                            <span
                                                style="font-size:9px; font-weight:700; color:#fca5a5; text-transform:uppercase; margin-top:2px;">Giờ</span>
                                        </div>
                                        <span
                                            style="color:#fff; font-weight:900; font-size:1.25rem; align-self:flex-start; margin-top:4px;">:</span>
                                        <div
                                            style="display:flex; flex-direction:column; align-items:center; background:rgba(0,0,0,0.25); border-radius:8px; padding:6px 10px; min-width:44px;">
                                            <span id="cd-minutes"
                                                style="font-size:1.25rem; font-weight:900; color:#fff; line-height:1;">00</span>
                                            <span
                                                style="font-size:9px; font-weight:700; color:#fca5a5; text-transform:uppercase; margin-top:2px;">Phút</span>
                                        </div>
                                        <span
                                            style="color:#fff; font-weight:900; font-size:1.25rem; align-self:flex-start; margin-top:4px;">:</span>
                                        <div
                                            style="display:flex; flex-direction:column; align-items:center; background:rgba(0,0,0,0.25); border-radius:8px; padding:6px 10px; min-width:44px;">
                                            <span id="cd-seconds"
                                                style="font-size:1.25rem; font-weight:900; color:#fff; line-height:1;">00</span>
                                            <span
                                                style="font-size:9px; font-weight:700; color:#fca5a5; text-transform:uppercase; margin-top:2px;">Giây</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Tabs (only if multiple promos) -->
                            <?php if (count($data['promotions']) > 1): ?>
                                <div class="flex gap-2" id="promoTabsContainer">
                                    <?php foreach ($data['promotions'] as $index => $promo): ?>
                                        <button onclick="switchPromoTab(<?= $index ?>)"
                                            class="promo-tab-btn px-4 py-1.5 rounded-lg text-sm font-bold transition-all duration-300 <?= $index === 0 ? 'bg-white text-red-600 shadow' : 'text-white/70 hover:text-white hover:bg-white/15' ?>"
                                            data-index="<?= $index ?>">
                                            <?= htmlspecialchars($promo['name']) ?>
                                        </button>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- White Products Area -->
                    <div style="background:#fff; padding: 24px 20px 10px;">
                        <script>
                            var promoData = <?= json_encode(array_map(fn($p) => [
                                'name' => $p['name'],
                                'end_time' => $p['end_time'] ?? null,
                            ], $data['promotions']), JSON_UNESCAPED_UNICODE) ?>;
                        </script>

                        <!-- Tab Contents -->
                        <div class="relative min-h-[380px]">
                            <?php foreach ($data['promotions'] as $index => $promo): ?>
                                <div id="promo-tab-<?= $index ?>"
                                    class="promo-tab-content transition-all duration-500 <?= $index === 0 ? 'opacity-100 translate-y-0 relative z-10 visible' : 'opacity-0 translate-y-4 absolute top-0 left-0 w-full z-0 invisible' ?>">
                                    <div class="relative">
                                        <div class="swiper promoSwiper-<?= $index ?>">
                                            <div class="swiper-wrapper pb-8">
                                                <?php foreach ($promo['products'] as $val):
                                                    $promoSalePrice = number_format($val['sale_price'], 0, ',', '.');
                                                    $promoOriginPrice = number_format($val['price'], 0, ',', '.');
                                                    $promoDiscount = null;
                                                    if ($val['price'] > 0 && $val['sale_price'] < $val['price']) {
                                                        $promoDiscount = round((($val['price'] - $val['sale_price']) / $val['price']) * 100);
                                                    }
                                                    $short_specs = (isset($val['specs']) && is_array($val['specs'])) ? implode(', ', array_column($val['specs'], 'spec_value')) : '';
                                                    ?>
                                                    <div class="swiper-slide h-auto">
                                                        <a href="/detail/<?= $val['sku'] ?>"
                                                            class="group h-[408px] bg-white rounded-xl border border-slate-200 overflow-hidden hover:border-red-500/40 hover:shadow-xl transition-all flex flex-col"
                                                            data-purpose="product-card">
                                                            <div
                                                                class="aspect-[4/3] relative overflow-hidden bg-slate-50 flex items-center justify-center p-4">
                                                                <img alt="<?= htmlspecialchars($val['name']) ?>"
                                                                    class="w-full h-full object-contain group-hover:scale-105 transition-transform duration-500"
                                                                    src="<?= $val['main_image_url'] ?>" />
                                                                <?php if ($promoDiscount !== null && $promoDiscount > 0): ?>
                                                                    <div class="absolute top-0 right-0 z-10">
                                                                        <div
                                                                            class="bg-red-500 text-white px-3 py-1.5 rounded-bl-xl shadow-sm flex flex-col items-center justify-center origin-top-right">
                                                                            <span
                                                                                class="text-[8px] font-bold uppercase tracking-widest opacity-90 leading-none mb-0.5">Giảm</span>
                                                                            <span
                                                                                class="text-sm font-black leading-none"><?= $promoDiscount ?>%</span>
                                                                        </div>
                                                                    </div>
                                                                <?php endif; ?>
                                                            </div>
                                                            <div class="p-4 flex-1 flex flex-col">
                                                                <span
                                                                    class="text-[10px] font-bold text-red-500 uppercase tracking-widest mb-1"><?= $val['brand_name'] ?? 'CHÍNH HÃNG' ?></span>
                                                                <h3
                                                                    class="font-bold text-sm mb-1 text-slate-900 group-hover:text-red-600 transition-colors line-clamp-2 min-h-[40px]">
                                                                    <?= $val['name'] ?>
                                                                </h3>
                                                                <p
                                                                    class="min-h-[32px] text-xs text-slate-500 mb-4 line-clamp-2 leading-relaxed">
                                                                    <?= $short_specs ?>
                                                                </p>
                                                                <div
                                                                    class="mt-auto flex items-center justify-between pt-3 border-t border-slate-100">
                                                                    <div class="flex flex-col">
                                                                        <?php if ($promoOriginPrice !== $promoSalePrice): ?>
                                                                            <span
                                                                                class="text-[10px] text-gray-400 line-through"><?= $promoOriginPrice ?>₫</span>
                                                                        <?php endif; ?>
                                                                        <span
                                                                            class="text-base font-extrabold text-red-600 leading-tight"><?= $promoSalePrice ?>₫</span>
                                                                        <span
                                                                            class="text-[10px] text-green-600 font-bold mt-0.5">Còn
                                                                            hàng</span>
                                                                    </div>
                                                                    <button data-sku="<?= $val['sku'] ?>"
                                                                        data-name="<?= $val['name'] ?>"
                                                                        data-price="<?= $val['sale_price'] ?>"
                                                                        data-origin="<?= $val['origin_price_display'] ?>"
                                                                        data-img="<?= $val['main_image_url'] ?>"
                                                                        data-phone="<?= $_SESSION['user']['phone'] ?? null ?>"
                                                                        class="add-to-cart size-9 flex items-center justify-center bg-red-50 text-red-500 rounded-lg hover:bg-red-500 hover:text-white transition-all">
                                                                        <span
                                                                            class="material-symbols-outlined z-[9]">shopping_cart</span>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </a>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                            <div class="swiper-pagination"></div>
                                        </div>
                                        <button
                                            class="swiper-button-prev-custom size-10 flex items-center justify-center rounded-full transition-all shadow-lg"><span
                                                class="material-symbols-outlined">chevron_left</span></button>
                                        <button
                                            class="swiper-button-next-custom size-10 flex items-center justify-center rounded-full transition-all shadow-lg"><span
                                                class="material-symbols-outlined">chevron_right</span></button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div><!-- end white products area -->
                </div><!-- end outer wrapper -->

                <script>
                    var promoCountdownInterval = null;

                    function startPromoCountdown(endTimeStr) {
                        if (promoCountdownInterval) clearInterval(promoCountdownInterval);
                        const wrapper = document.getElementById('promoCountdownWrapper');
                        if (!endTimeStr) { wrapper.style.display = 'none'; return; }
                        const endTime = new Date(endTimeStr.replace(' ', 'T')).getTime();
                        function tick() {
                            const diff = endTime - Date.now();
                            if (diff <= 0) {
                                wrapper.style.display = 'none';
                                clearInterval(promoCountdownInterval);
                                return;
                            }
                            wrapper.style.display = '';

                            const days = Math.floor(diff / (1000 * 60 * 60 * 24));
                            const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                            const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                            const seconds = Math.floor((diff % (1000 * 60)) / 1000);

                            const dayEl = document.getElementById('cd-days-wrapper');
                            const daySep = document.getElementById('cd-days-sep');
                            if (days > 0) {
                                dayEl.style.display = 'flex';
                                daySep.style.display = 'block';
                                document.getElementById('cd-days').textContent = String(days).padStart(2, '0');
                            } else {
                                dayEl.style.display = 'none';
                                daySep.style.display = 'none';
                            }

                            document.getElementById('cd-hours').textContent = String(hours).padStart(2, '0');
                            document.getElementById('cd-minutes').textContent = String(minutes).padStart(2, '0');
                            document.getElementById('cd-seconds').textContent = String(seconds).padStart(2, '0');
                        }
                        tick();
                        promoCountdownInterval = setInterval(tick, 1000);
                    }

                    function switchPromoTab(idx) {
                        // update tab button styles
                        document.querySelectorAll('.promo-tab-btn').forEach(btn => {
                            if (parseInt(btn.dataset.index) === idx) {
                                btn.className = "promo-tab-btn px-4 py-1.5 rounded-lg text-sm font-bold transition-all duration-300 bg-white text-red-600 shadow";
                            } else {
                                btn.className = "promo-tab-btn px-4 py-1.5 rounded-lg text-sm font-bold transition-all duration-300 text-white/60 hover:text-white hover:bg-white/10";
                            }
                        });

                        // show/hide tab content
                        document.querySelectorAll('.promo-tab-content').forEach(content => {
                            const contentIdx = parseInt(content.id.split('-')[2]);
                            if (contentIdx === idx) {
                                content.className = "promo-tab-content transition-all duration-500 opacity-100 translate-y-0 relative z-10 visible";
                            } else {
                                content.className = "promo-tab-content transition-all duration-500 opacity-0 translate-y-4 absolute top-0 left-0 w-full z-0 invisible";
                            }
                        });

                        // update campaign title & countdown dynamically
                        if (typeof promoData !== 'undefined' && promoData[idx]) {
                            document.getElementById('promoCampaignTitle').textContent = promoData[idx].name;
                            startPromoCountdown(promoData[idx].end_time);
                        }
                    }

                    // start countdown for the first campaign on page load
                    if (typeof promoData !== 'undefined' && promoData.length > 0) {
                        startPromoCountdown(promoData[0].end_time);
                    }
                </script>
            </section>
            <!-- END: Khuyến Mãi Đặc Biệt -->
        <?php endif; ?>




        <!-- sản phẩm nổi bật -->
        <section class="container py-2">
            <div class="product-section product-section-featured">

                <!-- No SVG bg -->

                <div class="product-section-content">
                    <div class="section-badge badge-featured">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 2C6.4 8 4 12 4 15a8 8 0 0016 0c0-3-2.4-7-8-13z" />
                        </svg>
                        NỔI BẬT
                    </div>

                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-2xl font-bold">Sản phẩm nổi bật</h2>
                    </div>

                    <div class="relative">
                        <div class="swiper featuredProductsSwiper">
                            <div class="swiper-wrapper pb-2">
                                <?php foreach ($data['hot'] as $val):
                                    $price = (!empty($val['sale_price']) && $val['sale_price'] > 0)
                                        ? number_format($val['sale_price'], 0, ',', '.')
                                        : number_format($val['price'], 0, ',', '.');
                                    $originPrice = (!empty($val['sale_price']) && $val['sale_price'] > 0) ? number_format($val['price'], 0, ',', '.') : null;
                                    $short_specs = implode(', ', array_column($val['specs'], 'spec_value'));
                                    $phantramgiam = (!empty($val['sale_price']) && $val['sale_price'] > 0) ? round((($val['price'] - $val['sale_price']) / $val['price']) * 100) : null;
                                    ?>
                                    <div class="swiper-slide h-full">
                                        <a href="/detail/<?= $val['sku'] ?>"
                                            class="group h-[408px] bg-white rounded-xl border border-slate-200 overflow-hidden hover:border-blue-500/40 hover:shadow-xl transition-all flex flex-col"
                                            data-purpose="product-card">
                                            <div
                                                class="aspect-[4/3] relative overflow-hidden bg-slate-50 flex items-center justify-center p-4">
                                                <img alt="<?= htmlspecialchars($val['name']) ?>"
                                                    class="w-full h-full object-contain group-hover:scale-105 transition-transform duration-500"
                                                    src="<?= $val['main_image_url'] ?>" />
                                                <?php if ($phantramgiam != null): ?>
                                                    <div class="absolute top-0 right-0 z-10">
                                                        <div
                                                            class="bg-red-500 text-white px-3 py-1.5 rounded-bl-xl shadow-sm flex flex-col items-center justify-center origin-top-right">
                                                            <span
                                                                class="text-[8px] font-bold uppercase tracking-widest opacity-90 leading-none mb-0.5">Giảm</span>
                                                            <span
                                                                class="text-sm font-black leading-none"><?= $phantramgiam ?>%</span>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="p-4 flex-1 flex flex-col">
                                                <span
                                                    class="text-[10px] font-bold text-blue-600 uppercase tracking-widest mb-1"><?= $val['brand_name'] ?></span>
                                                <h3
                                                    class="font-bold text-sm mb-1 text-slate-900 group-hover:text-blue-600 transition-colors line-clamp-2 min-h-[40px]">
                                                    <?= $val['name'] ?>
                                                </h3>
                                                <p
                                                    class="min-h-[32px] text-xs text-slate-500 mb-4 line-clamp-2 leading-relaxed">
                                                    <?= $short_specs ?>
                                                </p>
                                                <div
                                                    class="mt-auto flex items-center justify-between pt-3 border-t border-slate-100">
                                                    <div class="flex flex-col">
                                                        <?php if ($originPrice != null): ?><span
                                                                class="text-[10px] text-gray-400 line-through"><?= $originPrice ?>₫</span><?php endif; ?>
                                                        <span
                                                            class="text-base font-extrabold text-slate-900 leading-tight"><?= $price ?>₫</span>
                                                        <span class="text-[10px] text-green-600 font-bold mt-0.5">Còn
                                                            hàng</span>
                                                    </div>
                                                    <button data-sku="<?= $val['sku'] ?>" data-name="<?= $val['name'] ?>"
                                                        data-price="<?= (!empty($val['sale_price']) && $val['sale_price'] > 0) ? $val['sale_price'] : $val['price'] ?>"
                                                        data-origin="<?= $val['price'] ?>"
                                                        data-img="<?= $val['main_image_url'] ?>"
                                                        data-phone="<?= $_SESSION['user']['phone'] ?? null ?>"
                                                        class="add-to-cart size-9 flex items-center justify-center bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-600 hover:text-white transition-all">
                                                        <span class="material-symbols-outlined z-[9]">shopping_cart</span>
                                                    </button>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <button
                            class="swiper-button-prev-custom size-10 flex items-center justify-center rounded-full transition-all shadow-lg">
                            <span class="material-symbols-outlined">chevron_left</span>
                        </button>
                        <button
                            class="swiper-button-next-custom size-10 flex items-center justify-center rounded-full transition-all shadow-lg">
                            <span class="material-symbols-outlined">chevron_right</span>
                        </button>
                    </div>
                </div>
            </div>
        </section>


        <!-- sản phẩm mới -->
        <section class="container py-2">
            <div class="product-section product-section-new">

                <!-- No SVG bg -->

                <div class="product-section-content">
                    <div class="section-badge badge-new">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 2l2.4 7.4H22l-6.2 4.5 2.4 7.4L12 17l-6.2 4.3 2.4-7.4L2 9.4h7.6z" />
                        </svg>
                        MỚI
                    </div>
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-2xl font-bold">Sản phẩm mới</h2>
                    </div>

                    <div class="relative">
                        <div class="swiper featuredProductsSwiper">
                            <div class="swiper-wrapper pb-2">
                                <?php foreach ($data['new'] as $val):
                                    $price = (!empty($val['sale_price']) && $val['sale_price'] > 0) ? number_format($val['sale_price'], 0, ',', '.') : number_format($val['price'], 0, ',', '.');
                                    $originPrice = (!empty($val['sale_price']) && $val['sale_price'] > 0) ? number_format($val['price'], 0, ',', '.') : null;
                                    $short_specs = implode(', ', array_column($val['specs'], 'spec_value'));
                                    $phantramgiam = (!empty($val['sale_price']) && $val['sale_price'] > 0) ? round((($val['price'] - $val['sale_price']) / $val['price']) * 100) : null;
                                    ?>
                                    <div class="swiper-slide h-full">
                                        <a href="/detail/<?= $val['sku'] ?>"
                                            class="group h-[408px] bg-white rounded-xl border border-slate-200 overflow-hidden hover:border-blue-500/40 hover:shadow-xl transition-all flex flex-col"
                                            data-purpose="product-card">
                                            <div
                                                class="aspect-[4/3] relative overflow-hidden bg-slate-50 flex items-center justify-center p-4">
                                                <img alt="<?= htmlspecialchars($val['name']) ?>"
                                                    class="w-full h-full object-contain group-hover:scale-105 transition-transform duration-500"
                                                    src="<?= $val['main_image_url'] ?>" />
                                                <?php if ($phantramgiam != null): ?>
                                                    <div class="absolute top-0 right-0 z-10">
                                                        <div
                                                            class="bg-red-500 text-white px-3 py-1.5 rounded-bl-xl shadow-sm flex flex-col items-center justify-center origin-top-right">
                                                            <span
                                                                class="text-[8px] font-bold uppercase tracking-widest opacity-90 leading-none mb-0.5">Giảm</span><span
                                                                class="text-sm font-black leading-none"><?= $phantramgiam ?>%</span>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="p-4 flex-1 flex flex-col">
                                                <span
                                                    class="text-[10px] font-bold text-blue-600 uppercase tracking-widest mb-1"><?= $val['brand_name'] ?></span>
                                                <h3
                                                    class="font-bold text-sm mb-1 text-slate-900 group-hover:text-blue-600 transition-colors line-clamp-2 min-h-[40px]">
                                                    <?= $val['name'] ?>
                                                </h3>
                                                <p
                                                    class="min-h-[32px] text-xs text-slate-500 mb-4 line-clamp-2 leading-relaxed">
                                                    <?= $short_specs ?>
                                                </p>
                                                <div
                                                    class="mt-auto flex items-center justify-between pt-3 border-t border-slate-100">
                                                    <div class="flex flex-col">
                                                        <?php if ($originPrice != null): ?><span
                                                                class="text-[10px] text-gray-400 line-through"><?= $originPrice ?>₫</span><?php endif; ?>
                                                        <span
                                                            class="text-base font-extrabold text-slate-900 leading-tight"><?= $price ?>₫</span>
                                                        <span class="text-[10px] text-green-600 font-bold mt-0.5">Còn
                                                            hàng</span>
                                                    </div>
                                                    <button data-sku="<?= $val['sku'] ?>" data-name="<?= $val['name'] ?>"
                                                        data-price="<?= (!empty($val['sale_price']) && $val['sale_price'] > 0) ? $val['sale_price'] : $val['price'] ?>"
                                                        data-origin="<?= $val['price'] ?>"
                                                        data-img="<?= $val['main_image_url'] ?>"
                                                        data-phone="<?= $_SESSION['user']['phone'] ?? null ?>"
                                                        class="add-to-cart size-9 flex items-center justify-center bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-600 hover:text-white transition-all">
                                                        <span class="material-symbols-outlined z-[9]">shopping_cart</span>
                                                    </button>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <button
                            class="swiper-button-prev-custom size-10 flex items-center justify-center rounded-full transition-all shadow-lg"><span
                                class="material-symbols-outlined">chevron_left</span></button>
                        <button
                            class="swiper-button-next-custom size-10 flex items-center justify-center rounded-full transition-all shadow-lg"><span
                                class="material-symbols-outlined">chevron_right</span></button>
                    </div>
                </div>
            </div>
        </section>

        <!-- sản phẩm sale -->
        <section class="container py-2">
            <div class="product-section product-section-sale">

                <!-- No SVG bg -->

                <div class="product-section-content">
                    <div class="section-badge badge-sale">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M13 2l-9 11h7l-1 9 9-11h-7z" />
                        </svg>
                        SALE
                    </div>
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-2xl font-bold">Sản phẩm Sale</h2>
                    </div>

                    <div class="relative">
                        <div class="swiper featuredProductsSwiper">
                            <div class="swiper-wrapper pb-2">
                                <?php foreach ($data['sale'] as $val):
                                    $price = (!empty($val['sale_price']) && $val['sale_price'] > 0) ? number_format($val['sale_price'], 0, ',', '.') : number_format($val['price'], 0, ',', '.');
                                    $originPrice = (!empty($val['sale_price']) && $val['sale_price'] > 0) ? number_format($val['price'], 0, ',', '.') : null;
                                    $short_specs = implode(', ', array_column($val['specs'], 'spec_value'));
                                    $phantramgiam = (!empty($val['sale_price']) && $val['sale_price'] > 0) ? round((($val['price'] - $val['sale_price']) / $val['price']) * 100) : null;
                                    ?>
                                    <div class="swiper-slide h-full">
                                        <a href="/detail/<?= $val['sku'] ?>"
                                            class="group h-[408px] bg-white rounded-xl border border-slate-200 overflow-hidden hover:border-blue-500/40 hover:shadow-xl transition-all flex flex-col"
                                            data-purpose="product-card">
                                            <div
                                                class="aspect-[4/3] relative overflow-hidden bg-slate-50 flex items-center justify-center p-4">
                                                <img alt="<?= htmlspecialchars($val['name']) ?>"
                                                    class="w-full h-full object-contain group-hover:scale-105 transition-transform duration-500"
                                                    src="<?= $val['main_image_url'] ?>" />
                                                <?php if ($phantramgiam != null && $phantramgiam > 0): ?>
                                                    <div class="absolute top-0 right-0 z-10">
                                                        <div
                                                            class="bg-red-500 text-white px-3 py-1.5 rounded-bl-xl shadow-sm flex flex-col items-center justify-center origin-top-right">
                                                            <span
                                                                class="text-[8px] font-bold uppercase tracking-widest opacity-90 leading-none mb-0.5">Giảm</span><span
                                                                class="text-sm font-black leading-none"><?= $phantramgiam ?>%</span>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="p-4 flex-1 flex flex-col">
                                                <span
                                                    class="text-[10px] font-bold text-blue-600 uppercase tracking-widest mb-1"><?= $val['brand_name'] ?></span>
                                                <h3
                                                    class="font-bold text-sm mb-1 text-slate-900 group-hover:text-blue-600 transition-colors line-clamp-2 min-h-[40px]">
                                                    <?= $val['name'] ?>
                                                </h3>
                                                <p
                                                    class="min-h-[32px] text-xs text-slate-500 mb-4 line-clamp-2 leading-relaxed">
                                                    <?= $short_specs ?>
                                                </p>
                                                <div
                                                    class="mt-auto flex items-center justify-between pt-3 border-t border-slate-100">
                                                    <div class="flex flex-col">
                                                        <?php if ($originPrice != null): ?><span
                                                                class="text-[10px] text-gray-400 line-through"><?= $originPrice ?>₫</span><?php endif; ?>
                                                        <span
                                                            class="text-base font-extrabold text-slate-900 leading-tight"><?= $price ?>₫</span>
                                                        <span class="text-[10px] text-green-600 font-bold mt-0.5">Còn
                                                            hàng</span>
                                                    </div>
                                                    <button data-sku="<?= $val['sku'] ?>" data-name="<?= $val['name'] ?>"
                                                        data-price="<?= (!empty($val['sale_price']) && $val['sale_price'] > 0) ? $val['sale_price'] : $val['price'] ?>"
                                                        data-origin="<?= $val['price'] ?>"
                                                        data-img="<?= $val['main_image_url'] ?>"
                                                        data-phone="<?= $_SESSION['user']['phone'] ?? null ?>"
                                                        class="add-to-cart size-9 flex items-center justify-center bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-600 hover:text-white transition-all">
                                                        <span class="material-symbols-outlined z-[9]">shopping_cart</span>
                                                    </button>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <button
                            class="swiper-button-prev-custom size-10 flex items-center justify-center rounded-full transition-all shadow-lg"><span
                                class="material-symbols-outlined">chevron_left</span></button>
                        <button
                            class="swiper-button-next-custom size-10 flex items-center justify-center rounded-full transition-all shadow-lg"><span
                                class="material-symbols-outlined">chevron_right</span></button>
                    </div>
                </div>
            </div>
        </section>

        <!-- BEGIN: Danh Mục Sản Phẩm (Lazy Loaded) -->
        <?php
        // chunk categories to render sections
        $categoryChunks = array_chunk($data['category'], 4);
        foreach ($categoryChunks as $sectionIndex => $chunk):
            $sectionId = "cat-section-" . $sectionIndex;
            ?>
            <section class="container py-10 lazy-cat-section" data-section-id="<?= $sectionId ?>">
                <div class="grid grid-cols-1 lg:grid-cols-3 items-center mb-8 gap-6">
                    <!-- Left: Title -->
                    <div class="flex items-center gap-4">
                        <div class="w-1.5 h-8 bg-blue-600 rounded-full"></div>
                        <h2 class="text-2xl font-bold text-slate-900 whitespace-nowrap">Khám phá theo danh mục</h2>
                    </div>

                    <!-- Center: Tabs Navigation -->
                    <div class="flex justify-center">
                        <div class="flex bg-slate-100 p-1 rounded-xl overflow-x-auto no-scrollbar whitespace-nowrap max-w-full">
                            <?php foreach ($chunk as $idx => $cat): ?>
                                <button onclick="switchCategoryTab('<?= $sectionId ?>', <?= $idx ?>)"
                                    data-code="<?= $cat['code'] ?>" 
                                    data-name="<?= htmlspecialchars($cat['name']) ?>"
                                    class="cat-tab-btn-<?= $sectionId ?> px-5 py-2.5 rounded-lg text-sm font-bold transition-all duration-300 <?= $idx === 0 ? 'bg-white text-blue-600 shadow-sm' : 'text-slate-500 hover:text-slate-800' ?>">
                                    <?= $cat['name'] ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Right: View All -->
                    <div class="flex justify-start lg:justify-end">
                        <a id="view-all-<?= $sectionId ?>" href="/category/<?= $chunk[0]['code'] ?>"
                            class="flex items-center gap-1 group whitespace-nowrap text-blue-600 font-bold text-sm hover:underline"
                            style="border: 1px solid rgba(37, 99, 235, 0.2); padding: 8px 16px; border-radius: 12px; background: rgba(37, 99, 235, 0.05);">
                            <span>Xem tất cả <span class="cat-name-display-<?= $sectionId ?>"><?= $chunk[0]['name'] ?></span></span>
                            <span class="material-symbols-outlined text-lg group-hover:translate-x-1 transition-transform">chevron_right</span>
                        </a>
                    </div>
                </div>

                <div class="relative min-h-[420px]" id="container-<?= $sectionId ?>">
                    <?php foreach ($chunk as $idx => $cat): ?>
                        <div id="<?= $sectionId ?>-tab-<?= $idx ?>"
                            data-cat-code="<?= $cat['code'] ?>"
                            class="cat-tab-content-<?= $sectionId ?> transition-all duration-500 <?= $idx === 0 ? '' : 'hidden' ?>">
                            
                            <!-- Skeleton Loader -->
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 skeleton-container">
                                <?php for($i=0; $i<4; $i++): ?>
                                <div class="bg-white rounded-xl border border-slate-100 p-4 animate-pulse">
                                    <div class="aspect-square bg-slate-100 rounded-lg mb-4"></div>
                                    <div class="h-4 bg-slate-100 rounded w-2/3 mb-2"></div>
                                    <div class="h-3 bg-slate-100 rounded w-full mb-4"></div>
                                    <div class="h-8 bg-slate-100 rounded w-full"></div>
                                </div>
                                <?php endfor; ?>
                            </div>

                            <div class="swiper catSwiper-<?= $sectionId ?>-<?= $idx ?> hidden">
                                <div class="swiper-wrapper pb-10 product-list-target">
                                    <!-- Products will be injected here via JS -->
                                </div>
                                <div class="swiper-pagination"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endforeach; ?>

        <!-- BEGIN: Sản phẩm đã xem -->
        <section id="recently-viewed-section" class="container py-10 hidden">
            <div class="flex items-center justify-between mb-8">
                <div class="flex items-center gap-4">
                    <div class="w-1.5 h-8 bg-orange-500 rounded-full"></div>
                    <h2 class="text-2xl font-bold text-slate-900">Sản phẩm bạn đã xem</h2>
                </div>
                <button onclick="clearHistory()"
                    class="text-sm text-slate-400 hover:text-red-500 transition-colors flex items-center gap-1">
                    <span class="material-symbols-outlined text-lg">delete</span> Xóa lịch sử
                </button>
            </div>
            <div class="relative">
                <div class="swiper recentlyViewedSwiper">
                    <div id="recently-viewed-container" class="swiper-wrapper pb-10">
                        <!-- Dynamic content via JS -->
                    </div>
                    <div class="swiper-pagination"></div>
                </div>
            </div>
        </section>

        <script>

            function renderRecentlyViewed() {
                const section = document.getElementById('recently-viewed-section');
                const container = document.getElementById('recently-viewed-container');
                const history = JSON.parse(localStorage.getItem('recentlyViewed')) || [];

                if (history.length === 0) {
                    section.classList.add('hidden');
                    return;
                }

                section.classList.remove('hidden');
                container.innerHTML = history.map(p => {
                    const price = p.price.toLocaleString('vi-VN');
                    const origin = p.origin ? p.origin.toLocaleString('vi-VN') : null;
                    const discount = p.origin > p.price ? Math.round((1 - p.price / p.origin) * 100) : 0;

                    return `
                        <div class="swiper-slide h-full">
                            <a href="/detail/${p.sku}" class="group h-[408px] bg-white rounded-xl border border-slate-200 overflow-hidden hover:border-orange-400 hover:shadow-xl transition-all flex flex-col">
                                <div class="aspect-[4/3] relative overflow-hidden bg-slate-50 flex items-center justify-center p-4">
                                    <img src="${p.img}" alt="${p.name}" class="w-full h-full object-contain group-hover:scale-105 transition-transform duration-500">
                                    ${discount > 0 ? `
                                        <div class="absolute top-0 right-0 z-10">
                                            <div class="bg-red-500 text-white px-3 py-1.5 rounded-bl-xl shadow-sm flex flex-col items-center justify-center">
                                                <span class="text-[8px] font-bold uppercase tracking-widest opacity-90 leading-none mb-0.5">Giảm</span>
                                                <span class="text-sm font-black leading-none">${discount}%</span>
                                            </div>
                                        </div>` : ''}
                                </div>
                                <div class="p-4 flex-1 flex flex-col">
                                    <h3 class="font-bold text-sm mb-1 text-slate-900 group-hover:text-orange-600 transition-colors line-clamp-2 min-h-[40px]">${p.name}</h3>
                                    <div class="mt-auto flex items-center justify-between pt-3 border-t border-slate-100">
                                        <div class="flex flex-col">
                                            ${origin ? `<span class="text-[10px] text-gray-400 line-through">${origin}₫</span>` : ''}
                                            <span class="text-base font-extrabold text-slate-900 leading-tight">${price}₫</span>
                                        </div>
                                        <div class="size-9 flex items-center justify-center bg-orange-50 text-orange-600 rounded-lg">
                                            <span class="material-symbols-outlined text-xl">history</span>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>`;
                }).join('');

                new Swiper('.recentlyViewedSwiper', {
                    slidesPerView: 2,
                    spaceBetween: 16,
                    pagination: { el: '.swiper-pagination', clickable: true },
                    breakpoints: {
                        1024: { slidesPerView: 4, spaceBetween: 24 }
                    }
                });
            }

            function clearHistory() {
                if (confirm('Bạn có muốn xóa lịch sử xem sản phẩm?')) {
                    localStorage.removeItem('recentlyViewed');
                    renderRecentlyViewed();
                }
            }

            document.addEventListener('DOMContentLoaded', () => {
                renderRecentlyViewed();

                // init all category swipers
                document.querySelectorAll('.swiper[class*="catSwiper-"]').forEach(el => {
                    new Swiper(el, {
                        slidesPerView: 2,
                        spaceBetween: 16,
                        pagination: { el: el.querySelector('.swiper-pagination'), clickable: true },
                        breakpoints: {
                            1024: { slidesPerView: 4, spaceBetween: 24 }
                        },
                        observer: true,
                        observeParents: true
                    });
                });
            });
        </script>

        <section class="container py-4">
            <div class="flex items-center justify-between mb-8">
                <h2 class="text-2xl font-bold text-slate-900">Tin tức công nghệ</h2>
                <a class="text-primary text-sm font-medium hover:underline" href="#">Xem thêm tin tức</a>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- News Card -->
                <article class="group cursor-pointer">
                    <div class="aspect-video rounded-2xl overflow-hidden mb-4 bg-gray-100">
                        <img alt="Tech News"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                            src="https:// lh3.googleusercontent.com/aida-public/ab6axuacjtp5fqbdqhatfgyzgija_a8bu0vgytrbmud4v_kogfjrbjvbccfelld6kx95jd7vzey5e4sor9meoehm7xdxyu1q3iuiqfrssxv4-5wdukbfjbdhfz-1t8jlmxqyctdx-oxzbhwgidpp_3yjmyoeliiasgd470lcgtronbl6sosigeedcmnnh_vtcep3_fkuxwv0baefvd5tzwmqs7o9bszlpxutv3kynsq4bvqkiluhcxvk1u5-u3ynx3atjcnzmf1vh8oyybg" />
                    </div>
                    <div class="space-y-2">
                        <span class="text-xs font-bold text-primary uppercase">TIN TỨC</span>
                        <h3 class="text-lg font-bold group-hover:text-primary transition-colors leading-snug">Đánh giá
                            hiệu năng RTX 50-series: Bước nhảy vọt về AI và Ray Tracing</h3>
                        <p class="text-gray-500 text-sm line-clamp-2">Tìm hiểu chi tiết về kiến trúc mới nhất của NVIDIA
                            và những cải tiến tính toán kinh ngạc trong năm nay...</p>
                        <div class="flex items-center text-xs text-gray-400 gap-2 mt-4">
                            <span class="material-symbols-outlined text-sm">schedule</span>
                            <span>2 giờ trước</span>
                        </div>
                    </div>
                </article>
                <!-- News Card -->
                <article class="group cursor-pointer">
                    <div class="aspect-video rounded-2xl overflow-hidden mb-4 bg-gray-100">
                        <img alt="Tech News"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                            src="https:// lh3.googleusercontent.com/aida-public/ab6axubrgtbnqaq1t0wap0vgddcbzzls9tc8kyfiv9lw0v0eh6wefpqqtu0gr6rxsxhpjqrslqkupyuzbxfw-xeytepjmrro71zr9tg-7sjakslzkhwkij0parsylur2xhxqggwaedlt9gae7mlq2axzhgl0rkbys2moob5s59fio8k4uzuxnsrphkmpoh8svmfl4nzib1zylev3wvvivqq1jgfcx9h0ngfdjtvk1wde9zicfjpfvhyqqyl7zpah_h52elpun1wfx4d5jtc" />
                    </div>
                    <div class="space-y-2">
                        <span class="text-xs font-bold text-primary uppercase">ESPORTS</span>
                        <h3 class="text-lg font-bold group-hover:text-primary transition-colors leading-snug">Hướng dẫn
                            tối ưu PC để chiến các tựa game AAA mượt mà nhất</h3>
                        <p class="text-gray-500 text-sm line-clamp-2">Những mẹo nhỏ giúp bạn tận dụng tối đa phần cứng
                            hiện có để có trải nghiệm gaming tuyệt vời...</p>
                        <div class="flex items-center text-xs text-gray-400 gap-2 mt-4">
                            <span class="material-symbols-outlined text-sm">schedule</span>
                            <span>5 giờ trước</span>
                        </div>
                    </div>
                </article>
                <!-- News Card -->
                <article class="group cursor-pointer">
                    <div class="aspect-video rounded-2xl overflow-hidden mb-4 bg-gray-100">
                        <img alt="Tech News"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                            src="https:// lh3.googleusercontent.com/aida-public/ab6axuaxtbiqjjs1gek4r_bqx0gsxkfkfcetbndk-u55og5ozuu8lon_jaqcnvip9jiwocayp3x1hjkovvd7rk__cc6sbzqewsyh5ogbyhmbxkbb4syktldrwq5e4l62lqhptqd9mwb7ckwkd0dsl5uwcszfwg6ky3_0ijepq3egllm_0ma4ypubyolggygnh8_gvuphzixlxhqte_kaqqeobo6woaxc96cpgipspwwrrhm1ayuvw7g5oo9udphxqbk6oe_ux-acdenkwua" />
                    </div>
                    <div class="space-y-2">
                        <span class="text-xs font-bold text-primary uppercase">SẢN PHẨM MỚI</span>
                        <h3 class="text-lg font-bold group-hover:text-primary transition-colors leading-snug">Intel công
                            bố thế hệ CPU mới tiết kiệm điện năng hơn 40%</h3>
                        <p class="text-gray-500 text-sm line-clamp-2">Các dòng chip mới hứa hẹn sẽ thay đổi bộ mặt của
                            các dòng Laptop Ultrabook mỏng nhẹ...</p>
                        <div class="flex items-center text-xs text-gray-400 gap-2 mt-4">
                            <span class="material-symbols-outlined text-sm">schedule</span>
                            <span>Hôm qua</span>
                        </div>
                    </div>
                </article>
            </div>
        </section>
        <!-- END: TechNews -->
        <button id="backToTopBtn" class="back-to-top" aria-label="Lên đầu trang">
            <i class="fas fa-chevron-up"></i>

    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // khởi tạo swiper cho promo
            <?php if (!empty($data['promotions'])): ?>
                <?php foreach ($data['promotions'] as $index => $promo): ?>
                        (function () {
                            const selector = '.promoSwiper-<?= $index ?>';
                            const el = document.querySelector(selector);
                            if (!el) return;
                            const parent = el.closest('.relative');
                            new Swiper(selector, {
                                slidesPerView: 2,
                                spaceBetween: 16,
                                pagination: {
                                    el: selector + " .swiper-pagination",
                                    clickable: true,
                                },
                                navigation: {
                                    nextEl: parent.querySelector('.swiper-button-next-custom'),
                                    prevEl: parent.querySelector('.swiper-button-prev-custom'),
                                },
                                breakpoints: {
                                    640: { slidesPerView: 2, spaceBetween: 16 },
                                    1024: { slidesPerView: 4, spaceBetween: 24 },
                                }
                            });
                        })();
                <?php endforeach; ?>
            <?php endif; ?>

            document.querySelectorAll('.featuredProductsSwiper').forEach(el => {
                const parent = el.parentElement;
                const nextBtn = parent.querySelector('.swiper-button-next-custom');
                const prevBtn = parent.querySelector('.swiper-button-prev-custom');

                new Swiper(el, {
                    slidesPerView: 1,
                    spaceBetween: 24,
                    loop: true,
                    autoplay: {
                        delay: 4000 + Math.random() * 2000, // randomize delay a bit to look more natural
                        disableOnInteraction: false,
                    },
                    navigation: {
                        nextEl: nextBtn,
                        prevEl: prevBtn,
                    },
                    breakpoints: {
                        640: { slidesPerView: 2 },
                        1024: { slidesPerView: 4 },
                    }
                });
            });
        });
    </script>
    <script>
        let index = 0;
        const slider = document.getElementById("heroSlider");
        const total = slider.children.length;

        function nextSlide() {
            index = (index + 1) % total;
            slider.style.transform = `translateX(-${index * 100}%)`;
        }

        function prevSlide() {
            index = (index - 1 + total) % total;
            slider.style.transform = `translateX(-${index * 100}%)`;
        }

        // auto chạy
        setInterval(nextSlide, 3000);
    </script>
    <script src="js/home/lazy-load-home.js"></script>
    <script src="js/home/trangchu.js"></script>
</body>
