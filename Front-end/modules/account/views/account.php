

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Thông tin cá nhân - PolyGear</title>
  <script src="https:// cdn.tailwindcss.com"></script>
  <link
    href="https:// fonts.googleapis.com/css2?family=manrope:wght@400;600;700;800&family=inter:wght@400;500;600&display=swap"
    rel="stylesheet" />
  <link href="https:// fonts.googleapis.com/css2?family=material+symbols+outlined:wght,fill@100..700,0..1&display=swap"
    rel="stylesheet" />
  <link rel="stylesheet" href="https:// unpkg.com/maplibre-gl@3.6.2/dist/maplibre-gl.css" />
  <script src="https:// unpkg.com/maplibre-gl@3.6.2/dist/maplibre-gl.js"></script>
  <style>
    .material-symbols-outlined {
      font-variation-settings: "FILL" 0, "wght" 400, "GRAD" 0, "opsz" 24;
    }

    body {
      font-family: "Inter", sans-serif;
    }

    .modal-backdrop {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(25, 28, 34, 0.6);
      backdrop-filter: blur(4px);
      z-index: 99900;
    }

    .modal-backdrop.active {
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .custom-scrollbar::-webkit-scrollbar {
      width: 4px;
    }

    .custom-scrollbar::-webkit-scrollbar-track {
      background: transparent;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb {
      background: #c1c6d5;
      border-radius: 10px;
    }

    .input-locked {
      background-color: transparent;
      border: none;
      padding-left: 0;
    }

    .input-unlocked {
      background-color: white;
      border: 1px solid #e5e7eb;
      padding-left: 1rem;
    }
  </style>
</head>

<body class="bg-gray-50 text-gray-900 min-h-screen">

  <?php
  $user = $data[0] ?? [];
  $userId = $user['id'] ?? '';

  $defaultAddressText = "Chưa cập nhật địa chỉ...";
  if (!empty($user['address'])) {
    foreach ($user['address'] as $addr) {
      if ($addr['status'] == '1') {
        $defaultAddressText = htmlspecialchars($addr['address']);
        break;
      }
    }
    if ($defaultAddressText == "Chưa cập nhật địa chỉ...") {
      $defaultAddressText = htmlspecialchars($user['address'][0]['address']);
    }
  }
  ?>

  <main class="pt-6 pb-20 max-w-7xl mx-auto px-8">

    <div class="grid grid-cols-12 gap-8">
      <aside class="col-span-12 md:col-span-3 space-y-6">
        <div class="sticky top-28 space-y-6">
          <div class="flex items-center gap-4 px-2">
            <div class="w-12 h-12 rounded-full bg-primary-fixed flex items-center justify-center overflow-hidden">
              <img class="w-full h-full object-cover" data-alt="User profile avatar close up"
                src="<?= $_SESSION['user']['avatar'] ?? 'img/user/default_user.jpg' ?>" />
            </div>
            <div>
              <p class="font-headline font-bold text-on-surface">
                <?= $_SESSION['user']['name'] ?? $_SESSION['user']['phone'] ?>
              </p>
              <a href="/account">
                <p
                  class="text-xs text-on-surface-variant flex items-center gap-1 cursor-pointer hover:text-primary transition-colors">
                  <span class="material-symbols-outlined" style="font-size:14px !important;">edit</span>
                  Sửa hồ sơ
                </p>
              </a>
            </div>
          </div>

          <nav class="space-y-1">
            <?php
            $current_url = $_GET['url'] ?? 'account';
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
      </aside>

      <section class="col-span-12 md:col-span-9">
        <div class="bg-white rounded-xl p-8 md:p-10 shadow-sm border border-gray-100">
          <div class="mb-2 pb-2 border-b border-gray-100">
            <h1 class="text-2xl font-extrabold tracking-tight text-gray-900 mb-1">Hồ sơ của tôi</h1>
            <p class="text-gray-500 text-sm">Quản lý thông tin hồ sơ để bảo mật tài khoản</p>
          </div>

          <div class="flex flex-col lg:flex-row gap-16">
            <div class="flex-grow space-y-8 max-w-2xl">

              <input type="hidden" id="hidden-user-id" value="<?= htmlspecialchars($userId) ?>">

              <div class="grid grid-cols-4 items-center">
                <label class="col-span-1 text-sm font-semibold text-gray-600 text-right pr-6">Họ tên</label>
                <div class="col-span-3">
                  <input id="input-user-name"
                    class="w-full bg-gray-50 rounded-lg px-4 py-3 text-sm font-medium focus:bg-white focus:ring-2 focus:ring-blue-500/20 transition-all outline-none border border-transparent focus:border-blue-300"
                    type="text" value="<?= htmlspecialchars($user['user_name'] ?? '') ?>" />
                </div>
              </div>

              <div class="grid grid-cols-4 items-center">
                <label class="col-span-1 text-sm font-semibold text-gray-600 text-right pr-6">Email</label>
                <div class="col-span-3 flex items-center justify-between">
                  <input id="input-user-email"
                    class="w-full text-sm font-medium text-gray-900 outline-none transition-all py-3 rounded-lg input-locked"
                    type="email" value="<?= htmlspecialchars($user['gmail'] ?? '') ?>" readonly
                    placeholder="Chưa cập nhật" />
                  <button type="button"
                    class="text-blue-600 text-xs font-bold underline underline-offset-4 hover:text-blue-800 ml-4 whitespace-nowrap"
                    onclick="unlockInput('input-user-email')">Thay đổi</button>
                </div>
              </div>

              <div class="grid grid-cols-4 items-center">
                <label class="col-span-1 text-sm font-semibold text-gray-600 text-right pr-6">Số điện thoại</label>
                <div class="col-span-3 flex items-center justify-between">
                  <input id="input-user-phone"
                    class="w-full text-sm font-medium text-gray-900 outline-none transition-all py-3 rounded-lg input-locked"
                    type="text" value="<?= htmlspecialchars($user['phone_number'] ?? '') ?>" readonly
                    placeholder="Chưa cập nhật" />
                  <button type="button"
                    class="text-blue-600 text-xs font-bold underline underline-offset-4 hover:text-blue-800 ml-4 whitespace-nowrap"
                    onclick="unlockInput('input-user-phone')">Thay đổi</button>
                </div>
              </div>

              <div class="grid grid-cols-4 items-center">
                <label class="col-span-1 text-sm font-semibold text-gray-600 text-right pr-6">Địa chỉ</label>
                <div class="col-span-3 flex items-center justify-between">
                  <span id="main-ui-address"
                    class="text-sm font-medium text-gray-900 truncate pr-4"><?= $defaultAddressText ?></span>
                  <input type="hidden" id="final-address-input" value="<?= $defaultAddressText ?>">
                  <button type="button"
                    class="text-blue-600 text-xs font-bold underline underline-offset-4 hover:text-blue-800 transition-colors whitespace-nowrap"
                    onclick="openAddressListModal()">
                    Thay đổi
                  </button>
                </div>
              </div>

              <div class="grid grid-cols-4 items-center pt-4">
                <div class="col-start-2 col-span-3">
                  <button onclick="submitAllData()"
                    class="bg-blue-600 text-white font-bold py-3 px-10 rounded-lg hover:bg-blue-700 hover:shadow-lg hover:shadow-blue-600/30 active:scale-95 transition-all duration-200">
                    Lưu thay đổi
                  </button>
                </div>
              </div>
            </div>

            <div class="flex-shrink-0 lg:w-1/3 flex flex-col items-center lg:border-l border-gray-100 lg:pl-12 pt-4">
              <div class="w-32 h-32 rounded-full overflow-hidden mb-6 ring-4 ring-gray-50 ring-offset-2">
                <?php 
                  $display_avatar = $user['avatar_url'] ?? 'img/user/default-user.jpg';
                  if (!filter_var($display_avatar, FILTER_VALIDATE_URL)) {
                      $display_avatar = '/' . ltrim($display_avatar, '/');
                  }
                ?>
                <img id="main-avatar-preview" alt="Large User Avatar" class="w-full h-full object-cover"
                  src="<?= htmlspecialchars($display_avatar) ?>" />
              </div>
              <input type="hidden" id="hidden-avatar-base64" value="">

              <label for="avatarInput"
                class="px-6 py-2 border-gray-200 border rounded-lg text-sm font-bold text-gray-600 hover:bg-gray-50 transition-all mb-4 cursor-pointer">
                Chọn ảnh
              </label>
              <input type="file" id="avatarInput" accept="image/jpeg, image/png, image/gif" class="hidden"
                onchange="previewAndEncodeImage(this)">

              <div class="text-xs text-gray-400 space-y-1 text-center">
                <p>Dung lượng file tối đa 1 MB</p>
                <p>Định dạng: JPEG, PNG, GIF</p>
              </div>
            </div>

          </div>
        </div>
      </section>
    </div>
  </main>

  <div id="addressListModal" class="modal-backdrop">
    <div
      class="bg-white w-full max-w-2xl max-h-[85vh] rounded-2xl overflow-hidden shadow-2xl flex flex-col relative m-4"
      onclick="event.stopPropagation()">

      <div class="p-5 border-b flex justify-between items-center bg-gray-50">
        <h3 class="font-bold text-lg text-gray-800">Địa chỉ của tôi</h3>
        <button type="button"
          class="flex items-center gap-1 px-4 py-2 bg-blue-50 text-blue-600 text-sm font-bold rounded-lg hover:bg-blue-100 transition-colors"
          onclick="openMapModal()">
          <span class="material-symbols-outlined text-sm">add</span> Thêm địa chỉ mới
        </button>
      </div>

      <div class="p-5 overflow-y-auto custom-scrollbar flex-1 space-y-4 bg-white" id="address-list-container">

        <?php if (!empty($user['address'])): ?>
          <?php foreach ($user['address'] as $addr): ?>
            <?php
            $isDefault = ($addr['status'] == '1');
            $labelClass = $isDefault ? "border-blue-500 bg-blue-50/20" : "border-gray-200";
            ?>
            <label class="flex items-start gap-3 p-4 border rounded-xl cursor-pointer transition-colors <?= $labelClass ?>">
              <input type="radio" name="selected_address" value="<?= htmlspecialchars($addr['address']) ?>" <?= $isDefault ? 'checked' : '' ?> class="mt-1 w-4 h-4 text-blue-600">
              <div class="flex-1">

                <div class="flex items-center gap-2 mb-1.5">
                  <span class="font-bold text-gray-800"><?= htmlspecialchars($addr['receiver_name']) ?></span>
                  <span class="text-gray-300 text-sm">|</span>
                  <span class="text-gray-600 text-sm font-semibold"><?= htmlspecialchars($addr['receiver_phone']) ?></span>
                </div>
                <p class="text-sm text-gray-600"><?= htmlspecialchars($addr['address']) ?></p>

                <div class="flex items-center gap-2 mt-2">
                  <?php if ($isDefault): ?>
                    <span
                      class="inline-block px-2 py-0.5 bg-red-50 text-red-600 text-[10px] uppercase font-bold rounded border border-red-200">Mặc
                      định</span>
                  <?php else: ?>
                    <button type="button"
                      class="text-gray-400 hover:text-red-500 transition-colors text-xs flex items-center gap-1"
                      onclick="deleteAddress(event, <?= $addr['id'] ?>, this)">
                      <span class="material-symbols-outlined text-[14px]">delete</span> Xóa
                    </button>
                  <?php endif; ?>
                </div>

              </div>
            </label>
          <?php endforeach; ?>
        <?php else: ?>
          <p class="text-center text-gray-500 text-sm py-4">Bạn chưa có địa chỉ giao hàng nào.</p>
        <?php endif; ?>

      </div>

      <div class="p-4 border-t flex justify-end gap-3 bg-gray-50">
        <button type="button"
          class="px-6 py-2.5 border border-gray-300 rounded-xl font-bold text-gray-600 hover:bg-gray-100 transition-colors"
          onclick="toggleModal('addressListModal', false)">Hủy</button>
        <button type="button"
          class="px-6 py-2.5 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 transition-colors"
          onclick="saveSelectedAddress()">Xác nhận</button>
      </div>
    </div>
  </div>

  <div id="mapModal" class="modal-backdrop">
    <div class="bg-white w-full max-w-2xl rounded-2xl overflow-hidden shadow-2xl flex flex-col relative m-4"
      onclick="event.stopPropagation()">

      <div class="p-4 border-b flex justify-between items-center bg-gray-50">
        <div class="flex items-center gap-3">
          <button type="button"
            class="text-gray-500 hover:text-blue-600 transition-colors w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-200"
            onclick="backToAddressList()">
            <span class="material-symbols-outlined">arrow_back</span>
          </button>
          <h3 class="font-bold text-lg text-gray-800">Thêm địa chỉ mới</h3>
        </div>
        <button type="button" class="text-gray-400 hover:text-red-500 transition-colors"
          onclick="toggleModal('mapModal', false)">
          <span class="material-symbols-outlined text-3xl">close</span>
        </button>
      </div>

      <div class="p-3 bg-white flex gap-2 border-b z-[9000]">
        <div class="relative flex-1">
          <span class="material-symbols-outlined absolute left-3 top-2.5 text-gray-400">search</span>
          <input id="map-search-input" type="text" placeholder="Nhập địa chỉ cần tìm (VD: FPT Polytechnic)..."
            class="w-full pl-10 pr-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none text-sm bg-gray-50">
        </div>
        <button type="button" id="search-location-btn"
          class="px-5 py-2 bg-blue-600 text-white text-sm font-bold rounded-lg hover:bg-blue-700 transition-colors">
          Tìm
        </button>
      </div>

      <div class="p-4 grid grid-cols-2 gap-4 bg-white border-b border-gray-100 z-[9000] relative shadow-sm">
        <div>
          <label class="block text-xs font-bold text-gray-600 mb-1">Tên người nhận (*)</label>
          <input type="text" id="new-receiver-name" placeholder="Họ và tên"
            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none text-sm bg-gray-50">
        </div>
        <div>
          <label class="block text-xs font-bold text-gray-600 mb-1">Số điện thoại (*)</label>
          <input type="text" id="new-receiver-phone" placeholder="Số điện thoại"
            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none text-sm bg-gray-50">
        </div>
      </div>

      <div id="map-canvas" class="w-full relative shrink-0" style="height: 300px;"></div>

      <div class="p-4 bg-white flex justify-between items-center border-t border-gray-100">
        <div class="flex-1 mr-4">
          <label class="block text-xs font-bold text-gray-600 mb-1">Địa chỉ cụ thể (*)</label>
          <input type="text" id="current-selected-address" placeholder="Đang tải tọa độ..."
            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none text-sm text-gray-800 font-medium bg-gray-50"
            style="border: 0.5px solid black;">
        </div>
        <button type="button" id="confirm-address-btn"
          class="px-8 py-3 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 shadow-lg shadow-blue-500/30 transition-transform active:scale-95 whitespace-nowrap">
          Ghi nhận
        </button>
      </div>
    </div>
  </div>

  <script src="js/account/account.js"></script>
</body>
