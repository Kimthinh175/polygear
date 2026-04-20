<?php
if (isset($data['info'])) {
  $price = $data['info']['price'];
  $sale_price = $data['info']['sale_price'] ?? 0;
  $is_promo = isset($data['info']['is_promo']) && $data['info']['is_promo'] ? true : false;

  if ($sale_price > 0 && $sale_price < $price) {
      $display_price = $sale_price;
      $display_origin_price = $price;
  } else {
      $display_price = $price;
      $display_origin_price = null;
  }

  $display_price_formatted = number_format($display_price, 0, ',', '.');
  $display_origin_price_formatted = $display_origin_price ? number_format($display_origin_price, 0, ',', '.') : null;

  $discount_percent = 0;
  if ($display_origin_price > 0 && $display_origin_price > $display_price) {
      $discount_percent = round((($display_origin_price - $display_price) / $display_origin_price) * 100);
  }

  $active_ids = array_column($data['info']['attribute'], 'id');
  $isOutOfStock = $data['info']['stock'] > 0 ? false : true;
  $isStopped = !empty($data['info']['delete_at']);
  
  $total_reviews = $data['info']['total_reviews'] ?? 0;
  $avg_rating = $data['info']['avg_rating'] ?? 0;
  if(isset($lmao)){
    $lmao='';
  }
}
?>
<link href="https:// fonts.googleapis.com/css2?family=inter:wght@300;400;500;600;700;800;900&amp;display=swap"
  rel="stylesheet" />
<link href="https:// fonts.googleapis.com/css2?family=material+symbols+outlined:wght,fill@100..700,0..1&amp;display=swap"
  rel="stylesheet" />
<!-- Tailwind CSS Local -->
<title><?= $data['info']['name'] ?></title>
</head>

<body class="bg-white font-display text-slate-800 antialiased">
  <div class="relative flex h-auto min-h-screen w-full flex-col overflow-x-hidden">

    <main class="max-w-7xl mx-auto w-full px-6 md:px-10 py-8 lg:py-12">
      <!-- Breadcrumbs -->
      <nav class="flex text-slate-400 text-xs font-medium gap-2 mb-8">
        <a class="hover:text-primary" href="/home">Trang chủ</a>
        <span>/</span>
        <a class="hover:text-primary"
          href="/category/<?= $data['info']['code'] ?>"><?= $data['info']['cate_name'] ?></a>
        <span>/</span>
        <span class="text-slate-900"><?= $data['info']['name'] ?></span>
      </nav>
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-12">
        <!-- Product Gallery Section -->
        <div class="lg:col-span-7 flex flex-col gap-3">
          <!-- Main image with arrows -->
          <div
            class="relative aspect-video w-full overflow-hidden rounded-xl bg-slate-50 border border-slate-200 group">
            <div
              class="absolute inset-0 bg-contain bg-no-repeat bg-center transition-transform duration-500 scale-90 group-hover:scale-[0.95]"
              id="main-product-image" data-alt="Product image"
              style='background-image: url("<?= $data['imgs'][0]['detail_image_url'] ?>");'>
            </div>

            <!-- Prev arrow -->
            <button onclick="prevProductImg()" class="absolute left-2 top-1/2 -translate-y-1/2 z-10
              w-9 h-9 flex items-center justify-center rounded-full
              bg-white/80 backdrop-blur-sm border border-slate-200 shadow
              text-slate-600 hover:bg-white hover:text-blue-600 transition
              opacity-0 group-hover:opacity-100">
              <svg xmlns="http:// www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewbox="0 0 24 24"
                stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
              </svg>
            </button>

            <!-- Next arrow -->
            <button onclick="nextProductImg()" class="absolute right-2 top-1/2 -translate-y-1/2 z-10
              w-9 h-9 flex items-center justify-center rounded-full
              bg-white/80 backdrop-blur-sm border border-slate-200 shadow
              text-slate-600 hover:bg-white hover:text-blue-600 transition
              opacity-0 group-hover:opacity-100">
              <svg xmlns="http:// www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewbox="0 0 24 24"
                stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
              </svg>
            </button>
          </div>

          <!-- Thumbnail strip: horizontal scroll, 100x100 -->
          <div class="flex gap-2 overflow-x-auto pb-1" id="thumb-strip"
            style="scrollbar-width:thin; scrollbar-color:#cbd5e1 transparent;">
            <?php foreach ($data['imgs'] as $i => $img): ?>
              <div
                class="flex-none w-[100px] h-[100px] rounded-lg bg-cover bg-center bg-no-repeat bg-slate-50 border-2 cursor-pointer transition-all thumb-item <?= $i === 0 ? 'border-blue-500' : 'border-slate-200 hover:border-blue-400' ?>"
                data-img-url="<?= $img['detail_image_url'] ?>" data-index="<?= $i ?>"
                style='background-image: url("<?= $img['detail_image_url'] ?>");' onclick="selectProductImg(this)">
              </div>
            <?php endforeach; ?>
          </div>
        </div>
        <!-- Product Purchase Details -->
        <div class="lg:col-span-5 flex flex-col gap-6">

          <div class="p-6 rounded-xl bg-white border border-slate-200 shadow-sm relative">
            <?php if ($is_promo): ?>
              <div class="absolute -top-3 -right-3 bg-red-500 text-white text-xs font-bold px-3 py-1.5 rounded-xl shadow-lg border border-white flex items-center gap-1 z-10 animate-bounce">
                <span class="material-symbols-outlined text-[14px]">local_fire_department</span>
                ĐANG TRONG CHIẾN DỊCH KHUYẾN MÃI
              </div>
            <?php endif; ?>

            <span class="text-[18px] font-black text-slate-600"><?= $data['info']['name'] ?></span>
            
            <!-- Review Stars -->
            <div class="flex items-center gap-2 mt-2 mb-4">
              <div class="flex items-center">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <span class="material-symbols-outlined text-[16px] <?= $i <= round($avg_rating) ? 'text-amber-400' : 'text-slate-200' ?>" style="font-variation-settings: 'FILL' <?= $i <= round($avg_rating) ? '1' : '0' ?>">star</span>
                <?php endfor; ?>
              </div>
              <span class="text-sm font-semibold <?= $total_reviews > 0 ? 'text-amber-500' : 'text-slate-400' ?>">
                  <?= $total_reviews > 0 ? $avg_rating . '/5' : '0/5' ?>
              </span>
              <span class="text-sm text-slate-500">
                  (<?= $total_reviews > 0 ? $total_reviews . ' đánh giá' : 'Chưa có đánh giá nào' ?>)
              </span>
            </div>

            <!-- Price -->
            <div class="flex items-baseline gap-3 mb-4">
              <span class="text-4xl font-black text-red-600"><?= $display_price_formatted ?>đ</span>
              <?php if ($display_origin_price_formatted != null): ?>
                <span class="text-slate-400 line-through text-lg"><?= $display_origin_price_formatted ?>đ</span>
                <?php if ($discount_percent > 0): ?>
                  <span class="bg-red-100 text-red-600 text-xs font-bold px-2 py-0.5 rounded ml-2">-<?= $discount_percent ?>%</span>
                <?php endif; ?>
              <?php endif; ?>
            </div>

            <!-- Phiên bản (Capacity) Selection -->
            <?php foreach ($data['attributes'] as $attr): ?>
              <div class="mb-6">
                <h3 class="text-sm font-bold text-slate-900 mb-3"><?= $attr['name'] ?></h3>

                <div class="flex flex-wrap gap-2 attr-group">
                  <?php foreach ($attr['values'] as $val):
                    $isActive = in_array($val['id'], $active_ids);

                    ?>

                    <div data-attr_id="<?= $val['id'] ?>" class="attr-option relative min-w-0 cursor-pointer rounded-lg border-2 transition-all p-3 
                                            <?= $isActive
                                              ? 'border-red-500 bg-red-50 ring-1 ring-red-500 is-active'
                                              : 'border-slate-200 hover:border-red-300'
                                              ?> 
                                            ">

                      <div class="flex flex-col items-center justify-center">
                        <span class="text-xs whitespace-nowrap font-bold 
                                                    <?= $isActive ? 'text-red-700' : 'text-slate-900' ?>">
                          <?= $val['value'] ?>
                        </span>
                      </div>



                      <?php if ($isActive): ?>
                        <div class="absolute -top-1.5 -right-1.5 bg-red-500 text-white rounded-full p-0.5 shadow-sm">
                          <svg xmlns="http:// www.w3.org/2000/svg" class="h-2 w-2" viewbox="0 0 20 20" fill="currentcolor">
                            <path fill-rule="evenodd"
                              d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                              clip-rule="evenodd" />
                          </svg>
                        </div>
                      <?php endif; ?>

                    </div>
                  <?php endforeach; ?>
                </div>
              </div>
            <?php endforeach; ?>
            <div class="flex gap-3 flex-row">
              <?php if ($isStopped): ?>
                <div class="w-full flex flex-col gap-4 mt-4">
                  <div class="bg-slate-100 rounded-lg py-4 px-4 flex flex-col items-center justify-center text-center border-2 border-dashed border-slate-300">
                    <span class="material-symbols-outlined text-slate-400 text-3xl mb-1">block</span>
                    <span class="font-black text-slate-500 text-xl uppercase tracking-wider">Ngừng kinh doanh</span>
                    <span class="text-slate-400 text-sm mt-1">Sản phẩm này hiện không còn được bán tại hệ thống</span>
                  </div>
                  <a href="/category/<?= $data['info']['code'] ?>"
                    class="w-full bg-primary text-white font-bold py-3 px-2 rounded-lg hover:bg-primary/90 transition-all text-center shadow-lg shadow-primary/20">
                    Xem sản phẩm tương tự
                  </a>
                </div>
              <?php elseif (!$isOutOfStock): ?>
                <button id="buy-now"
                  class="w-4/5 py-4 bg-[#2a83e9] hover:bg-[#2a83e9]/90 text-white font-bold rounded-lg transition-all transform active:scale-[0.98] flex items-center justify-center gap-2 shadow-lg shadow-primary/20">
                  <span class="material-symbols-outlined">shopping_bag</span>
                  Mua ngay
                </button>
                <button id="add-to-cart" data-sku="<?= $data['info']['sku'] ?>"
                  data-phone="<?= $_SESSION['user']['phone'] ?? null ?>" data-name="<?= $data['info']['name'] ?>"
                  data-price="<?= $display_price ?>"
                  data-origin="<?= $display_origin_price ?? $display_price ?>" data-img="<?= $data['info']['main_image_url'] ?>"
                  class="w-1/5 py-4 bg-slate-100 hover:bg-slate-200 text-slate-900 font-bold rounded-lg transition-all flex items-center justify-center border border-slate-200">
                  <span class="material-symbols-outlined z-[9]">shopping_cart</span>
                </button>
              <?php else: ?>
                <div class="w-full flex flex-col gap-4 mt-4">

                  <div class="bg-[#f3f4f6] rounded-lg py-3 px-4 flex flex-col items-center justify-center text-center">
                    <span class="font-bold text-slate-800 text-lg uppercase">Tạm hết hàng</span>
                    <span class="text-slate-700 text-sm mt-0.5">(Vui lòng liên hệ 0862159940)</span>
                  </div>

                  <div class="text-center mt-2">
                    <p class="font-bold text-slate-800 text-[16px]">Đăng ký nhận thông tin khi có hàng</p>
                  </div>

                  <div class="flex items-center gap-2">
                    <input type="checkbox" id="promo-email"
                      class="w-4 h-4 rounded border-slate-300 text-[#d70018] focus:ring-[#d70018] cursor-pointer">
                    <label for="promo-email" class="text-sm text-slate-500 cursor-pointer select-none">
                      Đăng ký nhận tin khuyến mãi qua email
                    </label>
                  </div>

                  <div class="flex flex-row gap-3 w-full mt-1">
                    <button
                      class="flex-1 bg-[#d70018] text-white font-semibold py-3 px-2 rounded-lg hover:bg-[#b00012] transition-colors text-sm text-center shadow-sm">
                      Đăng ký nhận thông tin
                    </button>
                    <a href="/category/<?= $data['info']['code'] ?>"
                      class="flex-1 bg-white text-[#d70018] border border-[#d70018] font-semibold py-3 px-2 rounded-lg hover:bg-red-50 transition-colors text-sm text-center shadow-sm">
                      Xem thêm sản phẩm
                    </a>
                  </div>

                </div>
              <?php endif; ?>
            </div>
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div class="flex items-center gap-3 p-3 rounded-lg border border-slate-200 bg-white">
              <span class="material-symbols-outlined text-primary">local_shipping</span>
              <div>
                <p class="text-[10px] uppercase font-bold text-slate-400">Shipping</p>
                <p class="text-sm font-semibold text-slate-700">Giao hàng nhanh miễn phí</p>
              </div>
            </div>
            <div class="flex items-center gap-3 p-3 rounded-lg border border-slate-200 bg-white">
              <span class="material-symbols-outlined text-primary">assignment_return</span>
              <div>
                <p class="text-[10px] uppercase font-bold text-slate-400">Hoàn trả</p>
                <p class="text-sm font-semibold text-slate-700">Miễn phí trong 30 ngày</p>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- Details & Specifications Section -->
      <div class="mt-20">
        <div class="flex border-b border-slate-100 mb-10 overflow-x-auto no-scrollbar">
          <button class="px-8 py-4 border-b-2 border-primary text-primary font-bold whitespace-nowrap">
            Thông số kỹ thuật</button>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
          <div class="flex flex-col gap-6">
            <div class="overflow-hidden rounded-xl border border-slate-200 shadow-sm bg-white">
              <table class="w-full text-left text-sm">
                <tbody class="divide-y divide-slate-100">
                  <?php foreach ($data['specs'] as $spec): ?>
                    <tr class="bg-slate-50/50">
                      <td class="px-6 py-4 font-bold text-slate-500 w-1/3"><?= $spec['spec_name'] ?></td>
                      <td class="px-6 py-4 text-slate-700"><?= nl2br($spec['spec_value']) ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
          <div class="flex flex-col">
            <div class="p-6 rounded-2xl bg-blue-50/50 border border-blue-100 flex flex-col gap-6">
              <div class="flex items-center gap-3">
                <span class="material-symbols-outlined text-red-500 text-3xl font-bold">card_giftcard</span>
                <h3 class="text-xl font-bold text-slate-900">Ưu đãi thanh toán</h3>
              </div>
              <ul class="flex flex-col gap-4">
                <li class="flex items-start gap-3">
                  <span class="material-symbols-outlined text-green-500 text-xl flex-shrink-0">check_circle</span>
                  <span class="text-sm text-slate-700 font-medium">Xem chính sách ưu đãi dành cho
                    thành viên Smember</span>
                </li>
                <li class="flex items-start gap-3">
                  <span class="material-symbols-outlined text-green-500 text-xl flex-shrink-0">check_circle</span>
                  <div class="flex flex-col gap-1">
                    <span class="text-sm text-slate-700 font-medium">Giảm đến 5.000.000đ khi thanh
                      toán qua Kredivo</span>
                  </div>
                </li>
                <li class="flex items-start gap-3">
                  <span class="material-symbols-outlined text-green-500 text-xl flex-shrink-0">check_circle</span>
                  <span class="text-sm text-slate-700 font-medium">Hoàn tiền đến 2 triệu khi mở thẻ
                    tín dụng HSBC</span>
                </li>
                <li class="flex items-start gap-3">
                  <span class="material-symbols-outlined text-green-500 text-xl flex-shrink-0">check_circle</span>
                  <span class="text-sm text-slate-700 font-medium">Mở thẻ VIB nhận E-Voucher đến
                    600K</span>
                </li>
                <li class="flex items-start gap-3">
                  <span class="material-symbols-outlined text-green-500 text-xl flex-shrink-0">check_circle</span>
                  <span class="text-sm text-slate-700 font-medium">Giảm 2% tối đa 200K khi thanh toán
                    qua MOMO</span>
                </li>
                <li class="flex items-start gap-3">
                  <span class="material-symbols-outlined text-green-500 text-xl flex-shrink-0">check_circle</span>
                  <span class="text-sm text-slate-700 font-medium">Giảm đến 500K khi mở thẻ TPBANK
                    EVO</span>
                </li>
                <li class="flex items-start gap-3">
                  <span class="material-symbols-outlined text-green-500 text-xl flex-shrink-0">check_circle</span>
                  <span class="text-sm text-slate-700 font-medium leading-relaxed">Liên hệ B2B để được
                    tư vấn giá tốt nhất cho khách hàng doanh nghiệp khi mua số lượng nhiều</span>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>
      <!-- Mô tả sản phẩm -->
      <div class="mt-20">
        <div class="p-8 rounded-2xl bg-slate-50/50 border border-slate-200 shadow-sm relative">
          <h2 class="text-3xl font-black mb-8 text-slate-900">Mô tả sản phẩm</h2>

          <!-- Collapsible wrapper -->
          <div id="desc-wrapper" class="relative overflow-hidden transition-all duration-700 ease-in-out"
            style="max-height: 400px;">
            <div class="text-slate-600 leading-relaxed prose max-w-none">
              <?= $data['info']['description'] ?>
            </div>

            <!-- Fade gradient khi thu gọn - Làm cao hơn và mờ dần đẹp hơn -->
            <div id="desc-fade"
              class="absolute bottom-0 left-0 right-0 h-40 bg-gradient-to-t from-slate-50 via-slate-50/80 to-transparent pointer-events-none transition-opacity duration-500 z-10">
            </div>
          </div>

          <!-- Toggle button - Đặt ngay trên vùng mờ -->
          <div class="absolute bottom-6 left-0 right-0 flex justify-center z-20">
            <button id="desc-toggle-btn" onclick="toggleDescription()"
              class="flex items-center gap-2 px-8 py-2.5 rounded-full bg-white border border-slate-200 text-sm font-bold text-slate-800 hover:text-blue-600 hover:border-blue-500 hover:shadow-lg transition-all transform active:scale-95 group shadow-md">
              <span id="desc-toggle-label">Xem thêm mô tả</span>
              <svg id="desc-toggle-icon" xmlns="http:// www.w3.org/2000/svg"
                class="w-5 h-5 transition-transform duration-500 text-slate-400 group-hover:text-blue-500" fill="none"
                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
              </svg>
            </button>
          </div>
        </div>
      </div>

      <!-- Thông số kỹ thuật -->
      <?php if (!empty($data['specs'])): ?>
        <div class="mt-16 pt-12 border-t border-slate-100">
          <h2 class="text-3xl font-black mb-8 text-slate-900">Thông số kỹ thuật</h2>
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <?php foreach ($data['specs'] as $spec): ?>
              <div
                class="flex items-start gap-4 p-4 rounded-xl bg-slate-50 border border-slate-200 hover:border-blue-300 hover:bg-blue-50/40 transition-all">
                <div class="flex-1 min-w-0">
                  <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">
                    <?= htmlspecialchars($spec['spec_name']) ?></p>
                  <p class="text-base font-bold text-slate-900 truncate"><?= htmlspecialchars($spec['spec_value']) ?></p>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>

      <script>
        let _descExpanded = false;
        function toggleDescription() {
          const wrapper = document.getElementById('desc-wrapper');
          const fade = document.getElementById('desc-fade');
          const label = document.getElementById('desc-toggle-label');
          const icon = document.getElementById('desc-toggle-icon');
          _descExpanded = !_descExpanded;
          if (_descExpanded) {
            wrapper.style.maxHeight = (wrapper.scrollHeight + 100) + 'px'; // thêm chút padding trừ hao
            fade.style.opacity = '0';
            label.textContent = 'Thu gọn mô tả';
            icon.style.transform = 'rotate(180deg)';
          } else {
            wrapper.style.maxHeight = '400px';
            fade.style.opacity = '1';
            label.textContent = 'Xem thêm mô tả';
            icon.style.transform = 'rotate(0deg)';
          }
        }
      </script>

      <!-- Customer Reviews Section -->
      <div class="mt-20 border-t border-slate-100 pt-16">
        <div class="flex items-center justify-between mb-10">
          <h2 class="text-3xl font-black text-slate-900">Đánh giá & nhận xét</h2>
        </div>
        <div class="max-w-4xl space-y-8" id="reviews-list-container">
          <div class="text-center py-12 text-slate-500">
            <span class="material-symbols-outlined text-4xl mb-3 block text-slate-300">chat_bubble</span>
            Chưa có đánh giá nào cho sản phẩm này.
          </div>
        </div>
      </div>
      <!-- Sản phẩm liên quan (real data) -->
      <?php if (!empty($data['related'])): ?>
        <section class="mt-16 pt-12 border-t border-slate-100">
          <div class="flex items-center justify-between mb-8">
            <h2 class="text-3xl font-black text-slate-900">Sản phẩm liên quan</h2>
            <a href="/category/<?= $data['info']['code'] ?>"
              class="text-sm font-semibold text-blue-600 hover:underline">Xem tất cả &rarr;</a>
          </div>
          <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-5">
            <?php foreach ($data['related'] as $p):
              $pPrice = number_format($p['price'], 0, ',', '.');
              $pSalePrice = (!empty($p['sale_price']) && $p['sale_price'] > 0) ? number_format($p['sale_price'], 0, ',', '.') : null;
              $discount = (!empty($p['sale_price']) && $p['sale_price'] > 0 && $p['price'] > 0)
                ? round((1 - $p['sale_price'] / $p['price']) * 100) : 0;
              ?>
              <a href="/detail/<?= htmlspecialchars($p['sku']) ?>"
                class="group bg-white rounded-xl border border-slate-200 overflow-hidden hover:border-blue-400 hover:shadow-xl hover:shadow-slate-200/50 transition-all flex flex-col no-underline">

                <!-- Ảnh -->
                <div class="relative aspect-square overflow-hidden bg-slate-50 flex items-center justify-center p-6">
                  <img src="/<?= htmlspecialchars($p['main_image_url']) ?>" alt="<?= htmlspecialchars($p['name']) ?>"
                    class="w-full h-full object-contain group-hover:scale-105 transition-transform duration-500"
                    loading="lazy"
                    onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\'http:// www.w3.org/2000/svg\' width=\'100\' height=\'100\'%3e%3crect fill=\'%23f1f5f9\' width=\'100\' height=\'100\'/%3e%3c/svg%3e'" />
                  <?php if ($discount > 0): ?>
                    <span
                      class="absolute top-2 left-2 bg-red-500 text-white text-[10px] font-bold px-2 py-0.5 rounded uppercase">
                      -<?= $discount ?>%
                    </span>
                  <?php endif; ?>
                </div>

                <!-- Info -->
                <div class="p-4 flex-1 flex flex-col">
                  <h3
                    class="font-semibold text-sm text-slate-800 group-hover:text-blue-600 transition-colors line-clamp-2 mb-2 leading-snug flex-1">
                    <?= htmlspecialchars($p['name']) ?>
                  </h3>
                  <div class="mt-auto">
                    <?php if ($pSalePrice): ?>
                      <span class="text-[11px] text-slate-400 line-through block"><?= $pPrice ?>đ</span>
                      <span class="text-base font-black text-slate-900"><?= $pSalePrice ?>đ</span>
                    <?php else: ?>
                      <span class="text-base font-black text-slate-900"><?= $pPrice ?>đ</span>
                    <?php endif; ?>
                  </div>
                </div>
              </a>
            <?php endforeach; ?>
          </div>
        </section>
      <?php endif; ?>
</main>
</div>
</body>
<script src="js/catalog/product-detail.js"></script>
<script>
  const VARIANT_MAP = <?= json_encode($data['variant_map']) ?>;
  console.log("Danh sách biến thể:", VARIANT_MAP);

  // gallery navigation
  let _galleryIdx = 0;
  const _thumbs = () => document.querySelectorAll('.thumb-item');

  function selectProductImg(el) {
    const url = el.getAttribute('data-img-url');
    document.getElementById('main-product-image').style.backgroundImage = `url("${url}")`;
    _galleryIdx = parseInt(el.getAttribute('data-index')) || 0;
    _thumbs().forEach(t => {
      t.classList.remove('border-blue-500');
      t.classList.add('border-slate-200');
    });
    el.classList.add('border-blue-500');
    el.classList.remove('border-slate-200');
    // scroll thumbnail into view
    el.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
  }

  function prevProductImg() {
    const thumbs = _thumbs();
    if (!thumbs.length) return;
    _galleryIdx = (_galleryIdx - 1 + thumbs.length) % thumbs.length;
    selectProductImg(thumbs[_galleryIdx]);
  }

  function nextProductImg() {
    const thumbs = _thumbs();
    if (!thumbs.length) return;
    _galleryIdx = (_galleryIdx + 1) % thumbs.length;
    selectProductImg(thumbs[_galleryIdx]);
  }

  document.querySelectorAll('.attr-option').forEach(option => {
    option.addEventListener('click', function () {
      // bỏ qua nếu đang click lại chính nó
      if (this.classList.contains('is-active')) return;

      const clickedId = parseInt(this.getAttribute('data-attr_id'));
      const currentGroup = this.closest('.attr-group');

      let selectedIds = [];

      // gom tất cả id đang được chọn (bao gồm cả cái vừa click)
      document.querySelectorAll('.attr-group').forEach(group => {
        if (group === currentGroup) {
          selectedIds.push(clickedId);
        } else {
          const activeOption = group.querySelector('.attr-option.is-active');
          if (activeOption) {
            selectedIds.push(parseInt(activeOption.getAttribute('data-attr_id')));
          }
        }
      });

      selectedIds.sort((a, b) => a - b);

      // bước 1: tìm khớp tuyệt đối (ví dụ: 8gb + xanh)
      const matchVariant = VARIANT_MAP.find(variant => {
        let mapIds = [...variant.attr_ids].sort((a, b) => a - b);
        return JSON.stringify(mapIds) === JSON.stringify(selectedIds);
      });

      if (matchVariant) {
        // có tồn tại tổ hợp này -> chuyển trang bình thường
        window.location.href = `/detail/${matchVariant.sku}`;
      } else {
        // bước 2: nếu tổ hợp tuyệt đối không tồn tại -> tìm "biến thể thay thế"
        // ưu tiên đi tìm bất kỳ biến thể nào có chứa cái thuộc tính vừa bấm (clickedid)
        const fallbackVariant = VARIANT_MAP.find(variant => variant.attr_ids.includes(clickedId));

        if (fallbackVariant) {
          // tự động chuyển hướng sang phiên bản khả dụng (ví dụ: nhảy qua 8gb + đỏ)
          // bản có thể bật console.log này lên để test
          // console.log("tự động chuyển sang phiên bản khả dụng gần nhất:", fallbackvariant.sku);
          window.location.href = `/detail/${fallbackVariant.sku}`;
        } else {
          // trường hợp hiếm: bản thân cái nút vừa bấm đã hết hàng/bị xóa hoàn toàn
          alert("Rất tiếc, thuộc tính này hiện không có sẵn sản phẩm!");
        }
      }
    });
  });
</script>