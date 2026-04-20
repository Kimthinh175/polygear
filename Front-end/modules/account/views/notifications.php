<?php
if (!isset($_SESSION['user'])) {
    header('Location: /home');
    exit();
}
?>
<title>Thông báo | PolyGear</title>
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
          $current_url = $_GET['url'] ?? 'notifications';
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
        <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
          <div>
            <h1 class="text-xl font-extrabold text-slate-900">Thông báo của tôi</h1>
            <p class="text-sm text-slate-400 mt-0.5">Tất cả cập nhật về đơn hàng và ưu đãi từ PolyGear</p>
          </div>
          <button id="mark-all-read-btn"
            class="text-xs font-bold text-blue-600 hover:text-blue-700 hover:underline transition-colors px-3 py-1.5 rounded-lg hover:bg-blue-50">
            Đánh dấu tất cả đã đọc
          </button>
        </div>

        <div id="notifications-list" class="divide-y divide-slate-50">
          <!-- Skeleton loading -->
          <?php for ($i = 0; $i < 4; $i++): ?>
          <div class="flex gap-4 px-6 py-5 animate-pulse">
            <div class="w-10 h-10 rounded-full bg-slate-100 flex-shrink-0"></div>
            <div class="flex-1 space-y-2.5 pt-1">
              <div class="h-3.5 bg-slate-100 rounded-full w-2/3"></div>
              <div class="h-3 bg-slate-100 rounded-full w-full"></div>
              <div class="h-2.5 bg-slate-100 rounded-full w-1/4 mt-2"></div>
            </div>
          </div>
          <?php endfor; ?>
        </div>

        <!-- Empty state (hidden by default) -->
        <div id="notifications-empty" class="hidden text-center py-20 px-6">
          <div class="w-20 h-20 bg-blue-50 rounded-full flex items-center justify-center mx-auto mb-5">
            <span class="material-symbols-outlined text-4xl text-blue-300">notifications_off</span>
          </div>
          <h3 class="font-bold text-slate-700 text-lg mb-1">Chưa có thông báo nào</h3>
          <p class="text-slate-400 text-sm">Các cập nhật về đơn hàng và ưu đãi sẽ xuất hiện ở đây.</p>
        </div>
      </div>
    </section>
  </div>
</main>
<script src="js/account/notifications.js"></script>
