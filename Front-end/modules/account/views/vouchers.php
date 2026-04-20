<?php
if (!isset($_SESSION['user'])) {
    header('Location: /home');
    exit();
}
?>
<title>Kho Voucher | PolyGear</title>
<main class="pt-6 pb-16 min-h-screen">

  <div class="max-w-7xl mx-auto px-4 md:px-8 grid grid-cols-12 gap-8">
    <aside class="hidden lg:block col-span-3">
      <div class="sticky top-28 space-y-6">
        <div class="flex items-center gap-4 px-2">
          <div class="w-12 h-12 rounded-full bg-primary-fixed flex items-center justify-center overflow-hidden">
            <img class="w-full h-full object-cover" src="<?= $_SESSION['user']['avatar'] ?? 'img/user/default_user.jpg' ?>" />
          </div>
          <div>
            <p class="font-headline font-bold text-on-surface">
              <?= $_SESSION['user']['name'] ?? $_SESSION['user']['phone'] ?>
            </p>
            <a href="/account">
              <p class="text-xs text-on-surface-variant flex items-center gap-1 cursor-pointer hover:text-primary transition-colors">
                <span class="material-symbols-outlined text-[14px]">edit</span>
                Sửa hồ sơ
              </p>
            </a>
          </div>
        </div>

        <nav class="space-y-1">
          <?php
          $current_url = $_GET['url'] ?? 'vouchers';
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

    <section class="col-span-12 lg:col-span-9 space-y-4">
      <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-100">
          <h1 class="text-xl font-extrabold text-slate-900">Kho Voucher</h1>
          <p class="text-sm text-slate-400 mt-0.5">Các voucher bạn có thể sử dụng khi đặt hàng</p>
        </div>

        <div id="vouchers-list" class="grid grid-cols-1 md:grid-cols-2 gap-4 p-6">
          <!-- Skeleton loading -->
          <?php for ($i = 0; $i < 4; $i++): ?>
          <div class="border border-dashed border-slate-200 rounded-2xl p-5 animate-pulse flex gap-4">
            <div class="w-12 h-12 rounded-xl bg-slate-100 flex-shrink-0"></div>
            <div class="flex-1 space-y-2.5 pt-1">
              <div class="h-4 bg-slate-100 rounded-full w-1/2"></div>
              <div class="h-3 bg-slate-100 rounded-full w-3/4"></div>
              <div class="h-3 bg-slate-100 rounded-full w-1/3 mt-3"></div>
            </div>
          </div>
          <?php endfor; ?>
        </div>

        <!-- Empty state (hidden by default) -->
        <div id="vouchers-empty" class="hidden text-center py-20 px-6">
          <div class="w-20 h-20 bg-amber-50 rounded-full flex items-center justify-center mx-auto mb-5">
            <span class="material-symbols-outlined text-4xl text-amber-300">confirmation_number</span>
          </div>
          <h3 class="font-bold text-slate-700 text-lg mb-1">Chưa có voucher nào khả dụng</h3>
          <p class="text-slate-400 text-sm">Hãy theo dõi các chương trình ưu đãi từ PolyGear để không bỏ lỡ!</p>
          <a href="/products" class="mt-6 inline-block px-6 py-3 bg-primary text-white rounded-xl font-bold text-sm hover:opacity-90 transition-opacity">
            Mua sắm ngay
          </a>
        </div>
      </div>

      <!-- Copy toast -->
      <div id="copy-toast" class="fixed bottom-6 left-1/2 -translate-x-1/2 bg-slate-800 text-white text-sm font-bold px-5 py-3 rounded-xl shadow-xl opacity-0 pointer-events-none transition-all duration-300 z-50 flex items-center gap-2">
        <span class="material-symbols-outlined text-green-400 text-[18px]">check_circle</span>
        Đã sao chép mã voucher!
      </div>
    </section>
  </div>
</main>
<script src="js/account/vouchers.js"></script>
