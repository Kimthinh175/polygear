<title>Thanh toán thành công</title>
<!-- Tailwind CSS Local -->
<link rel="stylesheet" href="/css/tailwind.css">
<link
  href="https:// fonts.googleapis.com/css2?family=manrope:wght@400;500;600;700;800&amp;family=inter:wght@400;500;600&amp;display=swap"
  rel="stylesheet" />
<link href="https:// fonts.googleapis.com/css2?family=material+symbols+outlined:wght,fill@100..700,0..1&amp;display=swap"
  rel="stylesheet" />

<main class="pt-8 pb-20 px-4 md:px-6 max-w-5xl mx-auto">
  <!-- Success Header Section -->
  <div class="bg-white rounded-sm shadow-sm p-8 md:p-12 text-center mb-5 border-t-4 border-primary">
    <div class="inline-flex items-center justify-center w-20 h-20 bg-blue-50 rounded-full mb-6">
      <span class="material-symbols-outlined text-6xl text-primary"
        style="font-variation-settings: 'FILL' 1">check_circle</span>
    </div>
    <h1 class="text-2xl md:text-3xl font-headline font-bold text-primary mb-2">
      Cảm ơn bạn. Đơn hàng của bạn đã được ghi nhận!
    </h1>
    <p class="text-slate-500 mb-6">
      Chúng tôi sẽ gửi email xác nhận cho bạn trong giây lát.
    </p>
    <div class="inline-block bg-slate-50 px-6 py-3 rounded-sm border border-slate-200">
      <span class="text-sm font-medium text-slate-500">MÃ ĐƠN HÀNG:
      </span>
      <span class="text-lg font-bold text-primary"><?= $_GET['code'] ?></span>
    </div>
  </div>
  <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
    <!-- Left Column -->
    <div class="lg:col-span-8 space-y-5">
      <!-- Shipping & Payment Details -->
      <div class="bg-white rounded-sm shadow-sm p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
          <div>
            <h2 class="text-sm font-bold text-primary uppercase tracking-wide mb-4 flex items-center gap-2">
              <span class="material-symbols-outlined text-lg">location_on</span>
              Địa chỉ nhận hàng
            </h2>
            <div class="text-sm space-y-1">
              <p class="font-bold text-slate-800"><?= $data['info']['receiver_name'] ?></p>
              <p class="text-slate-500"><?= $data['info']['receiver_phone'] ?></p>
              <p class="text-slate-500">
                <?= $data['info']['shipping_address'] ?>
              </p>
            </div>
          </div>
          <div class="border-t md:border-t-0 md:border-l border-slate-100 pt-6 md:pt-0 md:pl-8">
            <h2 class="text-sm font-bold text-primary uppercase tracking-wide mb-4 flex items-center gap-2">
              <span class="material-symbols-outlined text-lg">payments</span>
              Phương thức thanh toán
            </h2>
            <div class="space-y-3">
              <p class="text-sm text-slate-800 font-medium">
                <?= $data['info']['payment_method'] == 'bank' ? 'Thanh toán qua mã QR' : 'Thanh toán khi nhận hàng (COD)' ?>
              </p>
              <?php
              $pStatus = $data['info']['payment_status'] ?? 'unpaid';
              $statusLabel = 'Chưa thanh toán';
              $statusClass = 'bg-amber-50 text-amber-600 border-amber-100';

              if ($pStatus === 'paid') {
                $statusLabel = 'Đã thanh toán';
                $statusClass = 'bg-emerald-50 text-emerald-600 border-emerald-100';
              } else if ($pStatus === 'failed') {
                $statusLabel = 'Thanh toán thất bại';
                $statusClass = 'bg-rose-50 text-rose-600 border-rose-100';
              } else if ($pStatus === 'refunded') {
                $statusLabel = 'Đã hoàn tiền';
                $statusClass = 'bg-purple-50 text-purple-600 border-purple-100';
              }
              ?>
              <div class="inline-flex">
                <span class="px-2.5 py-1 text-[10px] font-bold uppercase rounded-sm border <?= $statusClass ?>">
                  <?= $statusLabel ?>
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- Item List -->
      <div class="bg-white rounded-sm shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100">
          <h2 class="text-sm font-bold text-primary uppercase tracking-wide flex items-center gap-2">
            <span class="material-symbols-outlined text-lg">inventory_2</span>
            Chi tiết sản phẩm
          </h2>
        </div>
        <div class="divide-y divide-slate-100">

          <!-- Item 1 -->
          <?php
          foreach ($data['items'] as $val):
            $price = number_format($val['unit_price'] * $val['quantity'], 0, ',', '.');
            ?>
            <div class="p-6 flex items-center gap-4">
              <div class="w-20 h-20 bg-slate-50 border border-slate-200 flex-shrink-0">
                <img alt="<?= $val['product_name'] ?>" class="w-full h-full object-contain"
                  src="<?= $val['main_image_url'] ?>" />
              </div>
              <div class="flex-1 min-w-0">
                <h3 class="text-sm font-medium text-slate-800 truncate">
                  <?= $val['product_name'] ?>
                </h3>
                <p class="text-xs text-slate-500 mt-1">
                  Phiên bản: <?= $val['variant_name'] ?>
                </p>
                <p class="text-sm text-slate-800 mt-1">x<?= $val['quantity'] ?></p>
              </div>
              <div class="text-right">
                <span class="text-sm font-medium text-primary"><?= $price ?>₫</span>
              </div>
            </div>
          <?php endforeach; ?>

        </div>
      </div>
    </div>
    <!-- Right Column -->
    <div class="lg:col-span-4 space-y-5">
      <!-- Order Summary -->
      <div class="bg-white rounded-sm shadow-sm p-6 sticky top-24">
        <h2 class="text-sm font-bold text-slate-800 uppercase mb-6 pb-4 border-b border-slate-100">
          Tổng thanh toán
        </h2>
        <div class="space-y-4 text-sm">
          <div class="flex justify-between text-slate-500">
            <span>Tổng tiền hàng</span>
            <span><?= number_format($data['info']['total_price'], 0, ',', '.'); ?>₫</span>
          </div>
          <div class="flex justify-between text-slate-500">
            <span>Phí vận chuyển</span>
            <span>0₫</span>
          </div>
          <div class="flex justify-between text-slate-500">
            <span>Giảm giá phí vận chuyển</span>
            <span class="text-primary">-0₫</span>
          </div>
          <div class="flex justify-between text-slate-500">
            <span>Voucher từ TechComponent</span>
            <span class="text-primary">-0₫</span>
          </div>
          <div class="pt-4 border-t border-slate-100 flex justify-between items-center">
            <span class="text-base font-medium">Tổng số tiền</span>
            <span
              class="text-2xl font-bold text-primary"><?= number_format($data['info']['total_price'], 0, ',', '.'); ?>₫</span>
          </div>
        </div>
        <div class="mt-8 space-y-3">
          <a href="/products">
            <button
              class="w-full py-3 bg-blue-500 text-white font-bold rounded-sm hover:bg-blue-700 transition-colors uppercase text-sm tracking-wide">
              Tiếp tục mua sắm
            </button>
          </a>
          <a href="/history?code=<?= $_GET['code'] ?>">
            <button
              class="w-full py-3 bg-white border border-slate-200 text-slate-800 font-medium rounded-sm hover:bg-slate-50 transition-colors uppercase text-sm tracking-wide">
              Xem chi tiết đơn hàng
            </button>
          </a>

        </div>
        <div class="mt-6 flex items-start gap-3 p-3 bg-blue-50/50 rounded-sm border border-blue-100">
          <span class="material-symbols-outlined text-primary text-lg">info</span>
          <p class="text-xs text-slate-500 leading-normal">
            Vui lòng kiểm tra email để theo dõi hành trình đơn hàng. Đội ngũ
            hỗ trợ sẽ gọi xác nhận trong vòng 24h.
          </p>
        </div>
      </div>
    </div>
  </div>
</main>