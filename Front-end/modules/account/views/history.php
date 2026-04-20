<title>Lịch sử đơn hàng | PolyGear<?= $_SESSION['user']['name'] ? " | " . $_SESSION['user']['name'] : "" ?></title>
<main class="pt-6 pb-16 min-h-screen">

  <div class="max-w-7xl mx-auto px-4 md:px-8 grid grid-cols-12 gap-8">
    <aside class="hidden lg:block col-span-3">
      <div class="sticky top-28 space-y-6">
        <div class="flex items-center gap-4 px-2">
          <div class="w-12 h-12 rounded-full bg-primary-fixed flex items-center justify-center overflow-hidden">
            <img class="w-full h-full object-cover" data-alt="User profile avatar close up"
              src="<?= $_SESSION['user']['avatar'] ?? 'img/user/default_user.jpg' ?>" />
          </div>
          <div>
            <p class="font-headline font-bold text-on-surface">
              <?= $_SESSION['user']['name'] ?? $_SESSION['user']['phone'] ?></p>
            <a href="/account">
              <p
                class="text-xs text-on-surface-variant flex items-center gap-1 cursor-pointer hover:text-primary transition-colors">
                <span class="material-symbols-outlined text-[14px]">edit</span>
                Sửa hồ sơ
              </p>
            </a>
          </div>
        </div>

        <nav class="space-y-1">
          <?php
          $current_url = $_GET['url'] ?? 'history';
          $is_account = ($current_url === 'account');
          $is_history = ($current_url === 'history');
          $is_notifications = ($current_url === 'notifications');
          $is_vouchers = ($current_url === 'vouchers');
          ?>
          <a class="flex items-center gap-3 px-4 py-3 rounded-xl transition-colors <?= $is_account ? 'bg-[#e0efff] text-primary' : 'text-on-surface-variant hover:bg-surface-container-low' ?>"
            href="/account">
            <span class="material-symbols-outlined" style="<?= $is_account ? "font-variation-settings: 'FILL' 1" : "" ?>">person</span>
            <span class="font-body text-sm <?= $is_account ? 'font-bold' : 'font-medium' ?>">Tài Khoản Của Tôi</span>
          </a>

          <a class="flex items-center gap-3 px-4 py-3 rounded-xl transition-colors <?= $is_history ? 'bg-[#e0efff] text-primary' : 'text-on-surface-variant hover:bg-surface-container-low' ?>"
            href="/history">
            <span class="material-symbols-outlined" style="<?= $is_history ? "font-variation-settings: 'FILL' 1" : "" ?>">list_alt</span>
            <span class="font-body text-sm <?= $is_history ? 'font-bold' : 'font-medium' ?>">Đơn Mua</span>
          </a>

          <a class="flex items-center gap-3 px-4 py-3 rounded-xl transition-colors <?= $is_notifications ? 'bg-[#e0efff] text-primary' : 'text-on-surface-variant hover:bg-surface-container-low' ?>"
            href="/notifications">
            <span class="material-symbols-outlined" style="<?= $is_notifications ? "font-variation-settings: 'FILL' 1" : "" ?>">notifications</span>
            <span class="font-body text-sm <?= $is_notifications ? 'font-bold' : 'font-medium' ?>">Thông Báo</span>
          </a>

          <a class="flex items-center gap-3 px-4 py-3 rounded-xl transition-colors <?= $is_vouchers ? 'bg-[#e0efff] text-primary' : 'text-on-surface-variant hover:bg-surface-container-low' ?>"
            href="/vouchers">
            <span class="material-symbols-outlined" style="<?= $is_vouchers ? "font-variation-settings: 'FILL' 1" : "" ?>">confirmation_number</span>
            <span class="font-body text-sm <?= $is_vouchers ? 'font-bold' : 'font-medium' ?>">Kho Voucher</span>
          </a>
        </nav>
      </div>
    </aside>

    <div id="api-data-store" data-id="<?= $_SESSION['user']['id'] ?>" style="display: none;"></div>

    <section class="col-span-12 lg:col-span-9 space-y-4">
      <!-- Tabs Navigation -->
      <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div id="order-tabs" class="flex overflow-x-auto scrollbar-hide border-b border-slate-100">
          <button data-tab="all"
            class="tab-btn active-tab flex-1 min-w-[120px] py-4 text-center text-sm font-body font-bold transition-all text-[#2a83e9]"
            style="border-bottom: 2px solid #2a83e9 !important;">Tất cả</button>
          <button data-tab="pending_payment"
            class="tab-btn flex-1 min-w-[120px] py-4 text-center text-sm font-body font-medium text-slate-500 hover:text-primary transition-colors">Chờ
            thanh toán</button>
          <button data-tab="pending_confirmation"
            class="tab-btn flex-1 min-w-[120px] py-4 text-center text-sm font-body font-medium text-slate-500 hover:text-primary transition-colors">Chờ
            xác nhận</button>
          <button data-tab="shipping"
            class="tab-btn flex-1 min-w-[120px] py-4 text-center text-sm font-body font-medium text-slate-500 hover:text-primary transition-colors">Vận
            chuyển</button>
          <button data-tab="delivering"
            class="tab-btn flex-1 min-w-[120px] py-4 text-center text-sm font-body font-medium text-slate-500 hover:text-primary transition-colors">Đang
            giao</button>
          <button data-tab="completed"
            class="tab-btn flex-1 min-w-[120px] py-4 text-center text-sm font-body font-medium text-slate-500 hover:text-primary transition-colors">Hoàn
            thành</button>
          <button data-tab="cancelled"
            class="tab-btn flex-1 min-w-[120px] py-4 text-center text-sm font-body font-medium text-slate-500 hover:text-primary transition-colors">Đã
            hủy</button>
        </div>
      </div>

      <!-- Search Bar Container -->
      <div class="bg-white rounded-xl shadow-sm p-4">
        <div
          class="bg-slate-100/50 rounded-lg p-3 flex items-center gap-3 border border-slate-100 focus-within:border-primary/30 focus-within:bg-white transition-all">
          <span class="material-symbols-outlined text-slate-400 ml-1">search</span>
          <input id="search-order"
            class="w-full bg-transparent border-none focus:ring-0 text-sm font-body text-slate-700 py-1 outline-none"
            placeholder="Tìm kiếm theo ID đơn hàng hoặc Tên Sản phẩm" type="text" />
        </div>
      </div>

      <!-- Orders List -->
      <div id="order-list-container" class="space-y-6">
      </div>
    </section>
  </div>
</main>
<script src="js/account/history.js"></script>
