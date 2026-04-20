<?php
    if(isset($lmao)){
        $lmao='';
    }
?>
<title>Sản phẩm</title>
<link
  href="https:// fonts.googleapis.com/css2?family=inter:wght@300;400;500;600;700&amp;display=swap"
  rel="stylesheet"
/>
<link
  href="https:// fonts.googleapis.com/css2?family=material+symbols+outlined:wght,fill@100..700,0..1&amp;display=swap"
  rel="stylesheet"
/>
<style>
  body { font-family: "Inter", sans-serif; }
  .material-symbols-outlined {
    font-variation-settings: "FILL" 0, "wght" 400, "GRAD" 0, "opsz" 24;
  }
  .no-scrollbar::-webkit-scrollbar { display: none; }
  .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
  select { -webkit-appearance: none !important; -moz-appearance: none !important; appearance: none !important; }
  select::-ms-expand { display: none !important; }

  /* category chips */
  .cat-strip { display: flex; align-items: center; gap: 0.5rem; min-width: max-content; padding: 0.25rem 0; }
  .cat-chip {
    display: inline-flex; align-items: center; gap: 0.55rem;
    padding: 0.5rem 1.1rem; border-radius: 9999px;
    border: 1.5px solid #e2e8f0; background: #ffffff; color: #475569;
    font-size: 0.8125rem; font-weight: 600; text-decoration: none;
    white-space: nowrap; cursor: pointer;
    transition: border-color 0.18s, color 0.18s, box-shadow 0.18s, transform 0.18s, background 0.18s;
    position: relative; overflow: hidden;
  }
  .cat-chip .chip-bg {
    position: absolute; inset: 0;
    background: linear-gradient(135deg, #4F46E5 0%, #7C3AED 100%);
    opacity: 0; transition: opacity 0.18s; border-radius: inherit; z-index: 0;
  }
  .cat-chip > * { position: relative; z-index: 1; }
  .cat-chip:hover { border-color: #4F46E5; color: #4F46E5; box-shadow: 0 4px 14px rgba(79,70,229,0.18); transform: translateY(-1.5px); }
  .cat-chip.active { border-color: #4F46E5; color: #fff; box-shadow: 0 4px 18px rgba(79,70,229,0.28); }
  .cat-chip.active .chip-bg { opacity: 1; }
  .cat-chip .chip-img { width: 22px; height: 22px; object-fit: contain; filter: drop-shadow(0 1px 2px rgba(0,0,0,.1)); }
  .cat-chip.active .chip-img { filter: brightness(0) invert(1); }
  .cat-divider { width: 1px; height: 28px; background: #e2e8f0; flex-shrink: 0; }
  .cat-section-header { display: flex; align-items: center; gap: 0.65rem; margin-bottom: 0.875rem; }
  .cat-section-header h2 { font-size: 1.2rem; font-weight: 800; color: #0f172a; letter-spacing: -0.02em; margin: 0; }
  .cat-section-header .badge {
    font-size: 0.68rem; font-weight: 700; letter-spacing: 0.05em; text-transform: uppercase;
    background: linear-gradient(135deg, #4F46E5, #7C3AED); color: #fff;
    padding: 0.15rem 0.55rem; border-radius: 9999px;
  }
</style>
<body class="bg-background-light text-slate-800 min-h-screen">
  <main class="max-w-7xl mx-auto p-12 lg:p-12">

    <!-- ===== Category Navigation ===== -->
    <?php
      $pathParts = explode('/', trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/'));
      $currentCatCode = end($pathParts) ?: '';
    ?>
    <div class="mb-8">
      <div class="cat-section-header">
        <h2>Danh mục</h2>
        <span class="badge"><?= count($data['category']) ?> danh mục</span>
      </div>
      <div class="overflow-x-auto no-scrollbar pb-1">
        <div class="cat-strip">
          <!-- Tất cả -->
          <a class="cat-chip <?= (in_array($currentCatCode, ['category', '']) || !in_array($currentCatCode, array_column($data['category'], 'code'))) ? 'active' : '' ?>"
             href="/category">
            <span class="chip-bg"></span>
            <span class="material-symbols-outlined" style="font-size:1.05rem; line-height:1;">apps</span>
            <span>Tất cả</span>
          </a>
          <div class="cat-divider"></div>
          <?php foreach ($data['category'] as $val): ?>
            <a class="cat-chip <?= ($currentCatCode === $val['code']) ? 'active' : '' ?>"
               href="/category/<?= htmlspecialchars($val['code']) ?>">
              <span class="chip-bg"></span>
              <?php if (!empty($val['img'])): ?>
                <img class="chip-img" src="<?= htmlspecialchars($val['img']) ?>" alt="<?= htmlspecialchars($val['name']) ?>">
              <?php endif; ?>
              <span><?= htmlspecialchars($val['name']) ?></span>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
    <!-- Quick Filters & Sorting -->
   <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4 transition-all">
    
      <div class="flex flex-wrap items-center gap-3">
          <div class="flex items-center gap-2 mr-2">
              <span class="material-symbols-outlined text-primary">filter_list</span>
              <span class="font-bold text-slate-800">Bộ lọc:</span>
          </div>

          <div>
              <select name="brand" class="w-full bg-slate-50 border border-slate-200 text-slate-700 py-2 pl-4 pr-8 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all cursor-pointer text-sm font-medium hover:bg-slate-100">
                  <option value="">Hãng: Tất cả</option>
                  <?php if (!empty($data['brands'])): ?>
                      <?php foreach ($data['brands'] as $brand): ?>
                          <option value="<?= htmlspecialchars(strtolower($brand['brand_name'])) ?>"><?= htmlspecialchars($brand['brand_name']) ?></option>
                      <?php endforeach; ?>
                  <?php endif; ?>
              </select>
          </div>

          <div>
              <select name="price" class="w-full bg-slate-50 border border-slate-200 text-slate-700 py-2 pl-4 pr-8 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all cursor-pointer text-sm font-medium hover:bg-slate-100">
                  <option value="">Giá: Tất cả</option>
                  <option value="duoi-5-trieu">Dưới 5 triệu</option>
                  <option value="5-10-trieu">Từ 5 - 10 triệu</option>
                  <option value="tren-10-trieu">Trên 10 triệu</option>
              </select>
          </div>

          <?php if (!empty($data['dynamicFilters'])): ?>
              <?php foreach ($data['dynamicFilters'] as $spec_code => $spec_data): ?>
                  <div>
                      <select name="filter_<?= htmlspecialchars(strtolower($spec_code)) ?>" class="w-full bg-slate-50 border border-slate-200 text-slate-700 py-2 pl-4 pr-8 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all cursor-pointer text-sm font-medium hover:bg-slate-100">
                          <option value=""><?= htmlspecialchars($spec_data['name']) ?>: Tất cả</option>
                          
                          <?php foreach ($spec_data['values'] as $val): ?>
                              <option value="<?= htmlspecialchars($val) ?>"><?= htmlspecialchars($val) ?></option>
                          <?php endforeach; ?>
                          
                      </select>
                  </div>
              <?php endforeach; ?>
          <?php endif; ?>

          <button class="flex items-center gap-1 text-xs font-semibold text-red-500 hover:text-red-700 hover:bg-red-50 px-3 py-2 rounded-lg transition-colors">
              <span class="material-symbols-outlined text-sm">restart_alt</span>
              Xóa lọc
          </button>
      </div>

      <div class="flex items-center gap-3 pt-4 border-t border-slate-100 md:pt-0 md:border-t-0 md:border-l md:pl-6 shrink-0">
          <span class="text-sm font-medium text-slate-500 whitespace-nowrap">Sắp xếp:</span>
          
          <div class="shrink-0 min-w-[170px]">
              <select name="sort" class="w-full bg-white border border-slate-300 text-slate-800 py-2 pl-4 pr-8 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all cursor-pointer text-sm font-bold hover:shadow-sm">
                  <option value="popular">Bán chạy nhất</option>
                  <option value="newest">Mới cập nhật</option>
                  <option value="price-asc">Giá: Thấp đến Cao</option>
                  <option value="price-desc">Giá: Cao đến Thấp</option>
              </select>
          </div>
      </div>

  </div>
    <?php if(!empty($data['info'])):?>
    <section>
      <div class="mb-6">
        <h2 class="text-2xl font-bold text-slate-900">
          <?= !empty($data['keyword']) ? 'Kết quả tìm kiếm cho: "' . htmlspecialchars($data['keyword']) . '"' : ($data['info'][0]['cate_name'] ?? 'Sản phẩm') ?>
        </h2>
      </div>
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        <!-- Product 1 -->
        <?php foreach ($data['info'] as $val):
                        $price = (!empty($val['sale_price']) && $val['sale_price'] > 0)
                            ? number_format($val['sale_price'], 0, ',', '.')
                            : number_format($val['price'], 0, ',', '.');

                        $originPrice = (!empty($val['sale_price']) && $val['sale_price'] > 0)
                            ? number_format($val['price'], 0, ',', '.')
                            : null;
                        $short_specs = implode(', ', array_column($val['specs'], 'spec_value'));
                        $phantramgiam = (!empty($val['sale_price']) && $val['sale_price'] > 0) ? round((($val['price'] - $val['sale_price']) / $val['price']) * 100) : null;
                        ?>
                        <div class="swiper-slide h-full">
                            <a href="/detail/<?= $val['sku'] ?>"
                                class="group h-auto bg-white rounded-xl border border-slate-200 overflow-hidden hover:border-blue-500/40 hover:shadow-xl hover:shadow-slate-200/50 transition-all flex flex-col"
                                data-purpose="product-card">

                                <div
                                    class="aspect-[4/3] relative overflow-hidden bg-slate-50 flex items-center justify-center p-4">
                                    <img alt="Card màn hình"
                                        class="w-full h-full object-contain group-hover:scale-105 transition-transform duration-500"
                                        src="<?= $val['main_image_url'] ?>" />
                                    <?php if ($phantramgiam != null): ?>
                                         <div class="absolute top-0 right-0 z-10">
                                            <div class="bg-red-500 text-white px-3 py-1.5 rounded-bl-xl shadow-sm flex flex-col items-center justify-center  origin-top-right">
                                                <span class="text-[8px] font-bold uppercase tracking-widest opacity-90 leading-none mb-0.5">Giảm</span>
                                                <span class="text-sm font-black leading-none"><?= $phantramgiam ?>%</span>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="p-4 flex-1 flex flex-col">
                                    <div class="flex justify-between items-start mb-1.5">
                                        <span
                                            class="text-[10px] font-bold text-blue-600 uppercase tracking-widest"><? $val['brand_name'] ?></span>

                                    </div>

                                    <h3
                                        class="font-bold text-sm mb-1 text-slate-900 group-hover:text-blue-600 transition-colors line-clamp-2 min-h-[40px]">
                                        <?= $val['name'] ?>
                                    </h3>
                                    <p class="min-h-[32px] text-xs text-slate-500 mb-4 line-clamp-2 leading-relaxed">
                                        <?= $short_specs ?>
                                    </p>


                                    <div class="mt-4 flex items-center justify-between pt-3 border-t border-slate-100">
                                        <div class="flex flex-col">
                                            <?php if ($originPrice != null): ?>
                                                <span class="text-[10px] text-gray-400 line-through"><?= $originPrice ?>₫</span>
                                            <?php endif; ?>
                                            <span
                                                class="text-base font-extrabold text-slate-900 leading-tight"><?= $price ?>₫</span>
                                            <span class="text-[10px] text-green-600 font-bold mt-0.5">Còn hàng</span>
                                        </div>
                                        <button data-sku="<?= $val['sku'] ?>"
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
    </section>
    <?php else:?>
      <div class="bg-white rounded-xl py-16 text-center shadow-sm border border-slate-200 mt-6">
          <span class="material-symbols-outlined text-6xl text-slate-300 mb-4">search_off</span>
          <p class="text-slate-500 font-body text-lg">Không tìm thấy sản phẩm nào <?= !empty($data['keyword']) ? 'cho: "' . htmlspecialchars($data['keyword']) . '"' : 'trong danh mục này' ?>!</p>
          <a href="/category" class="mt-4 inline-block px-6 py-2.5 bg-blue-50 text-blue-600 font-semibold rounded-lg hover:bg-blue-600 hover:text-white transition-colors">Xem tất cả sản phẩm</a>
      </div>
    <?php endif;?>
    <section id="products-skeleton" class="max-w-7xl mx-auto px-4 py-12 hidden">
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 ">
          <!-- Skeleton Card 1 -->
          <div class="bg-white rounded-xl border border-slate-200 overflow-hidden flex flex-col animate-pulse h-full">
              <div class="aspect-square bg-slate-200 w-full relative overflow-hidden flex items-center justify-center p-8"></div>
              <div class="p-5 flex-1 flex flex-col">
                  <div class="flex justify-between items-start mb-2">
                      <div class="h-3 w-1/3 bg-slate-200 rounded"></div>
                  </div>
                  <div class="h-5 w-3/4 bg-slate-200 rounded mb-3 mt-1"></div>
                  <div class="space-y-2 mb-4">
                      <div class="h-3 w-full bg-slate-200 rounded"></div>
                      <div class="h-3 w-5/6 bg-slate-200 rounded"></div>
                  </div>
                  <div class="mt-auto flex items-center justify-between pt-2">
                      <div class="flex flex-col space-y-2">
                          <div class="h-6 w-24 bg-slate-200 rounded"></div>
                          <div class="h-3 w-16 bg-slate-200 rounded"></div>
                      </div>
                      <div class="size-10 bg-slate-200 rounded-lg"></div>
                  </div>
              </div>
          </div>
          <!-- Skeleton Card 2 -->
          <div class="bg-white rounded-xl border border-slate-200 overflow-hidden flex flex-col animate-pulse h-full">
              <div class="aspect-square bg-slate-200 w-full relative overflow-hidden flex items-center justify-center p-8"></div>
              <div class="p-5 flex-1 flex flex-col">
                  <div class="flex justify-between items-start mb-2">
                      <div class="h-3 w-1/3 bg-slate-200 rounded"></div>
                  </div>
                  <div class="h-5 w-3/4 bg-slate-200 rounded mb-3 mt-1"></div>
                  <div class="space-y-2 mb-4">
                      <div class="h-3 w-full bg-slate-200 rounded"></div>
                      <div class="h-3 w-5/6 bg-slate-200 rounded"></div>
                  </div>
                  <div class="mt-auto flex items-center justify-between pt-2">
                      <div class="flex flex-col space-y-2">
                          <div class="h-6 w-24 bg-slate-200 rounded"></div>
                          <div class="h-3 w-16 bg-slate-200 rounded"></div>
                      </div>
                      <div class="size-10 bg-slate-200 rounded-lg"></div>
                  </div>
              </div>
          </div>
          <!-- Skeleton Card 3 -->
          <div class="bg-white rounded-xl border border-slate-200 overflow-hidden flex flex-col animate-pulse h-full">
              <div class="aspect-square bg-slate-200 w-full relative overflow-hidden flex items-center justify-center p-8"></div>
              <div class="p-5 flex-1 flex flex-col">
                  <div class="flex justify-between items-start mb-2">
                      <div class="h-3 w-1/3 bg-slate-200 rounded"></div>
                  </div>
                  <div class="h-5 w-3/4 bg-slate-200 rounded mb-3 mt-1"></div>
                  <div class="space-y-2 mb-4">
                      <div class="h-3 w-full bg-slate-200 rounded"></div>
                      <div class="h-3 w-5/6 bg-slate-200 rounded"></div>
                  </div>
                  <div class="mt-auto flex items-center justify-between pt-2">
                      <div class="flex flex-col space-y-2">
                          <div class="h-6 w-24 bg-slate-200 rounded"></div>
                          <div class="h-3 w-16 bg-slate-200 rounded"></div>
                      </div>
                      <div class="size-10 bg-slate-200 rounded-lg"></div>
                  </div>
              </div>
          </div>
          <!-- Skeleton Card 4 -->
          <div class="bg-white rounded-xl border border-slate-200 overflow-hidden flex flex-col animate-pulse h-full">
              <div class="aspect-square bg-slate-200 w-full relative overflow-hidden flex items-center justify-center p-8"></div>
              <div class="p-5 flex-1 flex flex-col">
                  <div class="flex justify-between items-start mb-2">
                      <div class="h-3 w-1/3 bg-slate-200 rounded"></div>
                  </div>
                  <div class="h-5 w-3/4 bg-slate-200 rounded mb-3 mt-1"></div>
                  <div class="space-y-2 mb-4">
                      <div class="h-3 w-full bg-slate-200 rounded"></div>
                      <div class="h-3 w-5/6 bg-slate-200 rounded"></div>
                  </div>
                  <div class="mt-auto flex items-center justify-between pt-2">
                      <div class="flex flex-col space-y-2">
                          <div class="h-6 w-24 bg-slate-200 rounded"></div>
                          <div class="h-3 w-16 bg-slate-200 rounded"></div>
                      </div>
                      <div class="size-10 bg-slate-200 rounded-lg"></div>
                  </div>
              </div>
          </div>
      </div>
  </section>
  <button id="backToTopBtn" class="back-to-top" aria-label="Lên đầu trang">
    <i class="fas fa-chevron-up"></i>
  </main>
</body>
<script src="js/catalog/products.js"></script>
<script>
  const backToTopBtn = document.getElementById("backToTopBtn");
  if (!backToTopBtn) alert();
  window.addEventListener("scroll", () => {
    if (window.scrollY > 300) {
      backToTopBtn.classList.add("show");
    } else {
      backToTopBtn.classList.remove("show");
    }
  });

  backToTopBtn.addEventListener("click", () => {
    window.scrollTo({
      top: 0,
      behavior: "smooth",
    });
  });
</script>
