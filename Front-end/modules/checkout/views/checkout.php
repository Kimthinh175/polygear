<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Thanh toán - PolyGear</title>
  <script src="https:// cdn.tailwindcss.com"></script>
  <link href="https:// fonts.googleapis.com/css2?family=manrope:wght@400;600;700;800&family=inter:wght@400;500;600&display=swap" rel="stylesheet" />
  <link href="https:// fonts.googleapis.com/css2?family=material+symbols+outlined:wght,fill@100..700,0..1&display=swap" rel="stylesheet" />
  
  <link rel="stylesheet" href="https:// unpkg.com/maplibre-gl@3.6.2/dist/maplibre-gl.css" />
  <script src="https:// unpkg.com/maplibre-gl@3.6.2/dist/maplibre-gl.js"></script>

  <style>
    .material-symbols-outlined { font-variation-settings: "FILL" 0, "wght" 400, "GRAD" 0, "opsz" 24; }
    body { font-family: "Inter", sans-serif; background: #f1f5f9; }
    h1, h2, h3, .font-headline { font-family: "Manrope", sans-serif; }
    .modal-backdrop { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.65); backdrop-filter: blur(6px); z-index: 99900; }
    .modal-backdrop.active { display: flex; align-items: center; justify-content: center; }
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #c1c6d5; border-radius: 10px; }
    .payment-option { transition: all 0.2s ease; }
    .payment-option.selected { border-color: #2563eb !important; background: #eff6ff; }
    .address-card { cursor: pointer; transition: all 0.2s; }
    .address-card:hover { border-color: #2563eb; background: #eff6ff; }
    @keyframes shimmer { 0% { background-position: -200% 0; } 100% { background-position: 200% 0; } }
    .shimmer { background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%); background-size: 200% 100%; animation: shimmer 1.5s infinite; border-radius: 6px; display: inline-block; height: 1em; }
  </style>
</head>
<body class="bg-slate-100 min-h-screen">

<main class="max-w-6xl mx-auto w-full px-4 py-6 sm:py-10">
  <input id="user-id" type="hidden" value="<?= $_SESSION['user']['id'] ?? 1 ?>"/>

  <!-- Step Indicator -->
  <div class="mb-8">
    <div class="relative flex justify-center items-center max-w-sm mx-auto">
      <div class="flex flex-col items-center z-10">
        <div class="size-10 rounded-full bg-blue-600 text-white flex items-center justify-center shadow-md shadow-blue-200">
          <span class="material-symbols-outlined text-[18px]">check</span>
        </div>
        <span class="mt-1.5 text-xs font-semibold text-blue-600">Giỏ hàng</span>
      </div>
      <div class="flex-1 h-0.5 bg-blue-500 mb-4 mx-2"></div>
      <div class="flex flex-col items-center z-10">
        <div class="size-10 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold ring-4 ring-blue-100 shadow-md">2</div>
        <span class="mt-1.5 text-xs font-bold text-blue-600">Thanh toán</span>
      </div>
      <div class="flex-1 h-0.5 bg-slate-200 mb-4 mx-2"></div>
      <div class="flex flex-col items-center z-10">
        <div class="size-10 rounded-full bg-white border-2 border-slate-200 text-slate-400 flex items-center justify-center font-bold">3</div>
        <span class="mt-1.5 text-xs font-semibold text-slate-400">Hoàn tất</span>
      </div>
    </div>
  </div>

  <!-- Main Grid -->
  <div class="grid grid-cols-1 lg:grid-cols-[1fr_360px] gap-6 items-start">

    <!-- LEFT: Forms -->
    <div class="space-y-5">

      <!-- Shipping Info Card -->
      <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <!-- Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 bg-slate-50/60">
          <div class="flex items-center gap-2.5">
            <div class="size-8 rounded-lg bg-blue-600 flex items-center justify-center">
              <span class="material-symbols-outlined text-white text-[18px]">local_shipping</span>
            </div>
            <h3 class="text-base font-bold text-slate-800">Thông tin giao hàng</h3>
          </div>
          <button type="button" onclick="openAddressListModal()"
            class="flex items-center gap-1.5 text-sm font-semibold text-blue-600 px-3 py-1.5 rounded-lg hover:bg-blue-50 border border-blue-200 transition-colors">
            <span class="material-symbols-outlined text-[16px]">edit</span>
            Thay đổi
          </button>
        </div>

        <!-- Contact Info -->
        <div class="px-6 pt-5 pb-3 grid grid-cols-2 gap-4">
          <div>
            <p class="text-[11px] font-bold uppercase tracking-widest text-slate-400 mb-1.5">Người nhận</p>
            <span id="cus-name" class="text-sm font-bold text-slate-800 block">
              <span class="shimmer w-28 h-4">&nbsp;</span>
            </span>
          </div>
          <div>
            <p class="text-[11px] font-bold uppercase tracking-widest text-slate-400 mb-1.5">Số điện thoại</p>
            <span id="cus-phone" class="text-sm font-bold text-slate-800 block">
              <span class="shimmer w-24 h-4">&nbsp;</span>
            </span>
          </div>
        </div>

        <!-- Address -->
        <div class="px-6 pb-4 pt-2">
          <p class="text-[11px] font-bold uppercase tracking-widest text-slate-400 mb-2">Địa chỉ giao hàng</p>
          <div class="address-card flex items-start gap-3 p-3.5 rounded-xl border-2 border-slate-200 hover:border-blue-400 hover:bg-blue-50 transition-all" onclick="openAddressListModal()">
            <span class="material-symbols-outlined text-blue-500 text-[20px] mt-0.5 shrink-0">location_on</span>
            <span id="main-ui-address" class="text-sm text-slate-700 leading-relaxed flex-1">
              <span class="shimmer w-56 h-4">&nbsp;</span>
            </span>
            <span class="material-symbols-outlined text-slate-400 text-[18px] shrink-0 mt-0.5">chevron_right</span>
          </div>
          <input type="hidden" id="cus-address" name="cus_address" value="">
          <p id="location-msg" class="text-xs text-red-500 mt-1.5 hidden"></p>
        </div>

        <!-- Note -->
        <div class="px-6 pb-5">
          <label class="block text-[11px] font-bold uppercase tracking-widest text-slate-400 mb-2">Ghi chú (tùy chọn)</label>
          <textarea id="cus-note"
            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all resize-none outline-none placeholder:text-slate-400"
            placeholder="VD: Giao buổi sáng, gọi trước khi giao..." rows="2"></textarea>
        </div>
      </div>

      <!-- Payment Method Card -->
      <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="flex items-center gap-2.5 px-6 py-4 border-b border-slate-100 bg-slate-50/60">
          <div class="size-8 rounded-lg bg-blue-600 flex items-center justify-center">
            <span class="material-symbols-outlined text-white text-[18px]">payments</span>
          </div>
          <h3 class="text-base font-bold text-slate-800">Phương thức thanh toán</h3>
        </div>

        <div class="p-5 grid grid-cols-1 sm:grid-cols-2 gap-3">
          <label class="payment-label payment-option flex items-center gap-3 p-4 border-2 border-slate-200 rounded-xl cursor-pointer">
            <input class="hidden" name="payment" type="radio" value="bank" />
            <div class="size-11 rounded-xl bg-blue-50 flex items-center justify-center shrink-0">
              <span class="material-symbols-outlined text-blue-600 text-[22px]">account_balance</span>
            </div>
            <div class="flex-1">
              <p class="font-bold text-sm text-slate-800">Chuyển khoản QR</p>
              <p class="text-xs text-slate-500 mt-0.5">Quét mã PayOS cực nhanh</p>
            </div>
          </label>

          <label class="payment-label payment-option selected flex items-center gap-3 p-4 border-2 border-slate-200 rounded-xl cursor-pointer">
            <input checked class="hidden" name="payment" type="radio" value="cod" />
            <div class="size-11 rounded-xl bg-green-50 flex items-center justify-center shrink-0">
              <span class="material-symbols-outlined text-green-600 text-[22px]">delivery_dining</span>
            </div>
            <div class="flex-1">
              <p class="font-bold text-sm text-slate-800">Thanh toán COD</p>
              <p class="text-xs text-slate-500 mt-0.5">Kiểm tra hàng rồi mới trả</p>
            </div>
          </label>
        </div>
      </div>

      <!-- Trust Badges -->
      <div class="flex items-center justify-center gap-6 py-1 text-xs text-slate-400">
        <span class="flex items-center gap-1.5"><span class="material-symbols-outlined text-[16px] text-green-500">verified_user</span> Bảo mật SSL</span>
        <span class="flex items-center gap-1.5"><span class="material-symbols-outlined text-[16px] text-blue-500">lock</span> Mã hoá dữ liệu</span>
        <span class="flex items-center gap-1.5"><span class="material-symbols-outlined text-[16px] text-orange-400">replay</span> Đổi trả 7 ngày</span>
      </div>
    </div>

    <!-- RIGHT: Order Summary (Sticky) -->
    <div class="lg:sticky lg:top-6">
      <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">

        <!-- Header -->
        <div class="flex items-center gap-2 px-5 py-4 border-b border-slate-100 bg-slate-50/60">
          <span class="material-symbols-outlined text-blue-600 text-[20px]">receipt_long</span>
          <h3 class="text-base font-bold text-slate-800">Tóm tắt đơn hàng</h3>
        </div>

        <!-- Product List -->
        <div class="px-5 py-4 space-y-3 max-h-52 overflow-y-auto custom-scrollbar product-list"></div>

        <!-- Dashed Divider -->
        <div class="mx-5 border-t border-dashed border-slate-200"></div>

        <!-- Pricing breakdown -->
        <div class="px-5 py-4 space-y-2.5">
          <div class="flex justify-between text-sm">
            <span class="text-slate-500">Tạm tính</span>
            <span class="font-semibold text-slate-700 tam-tinh">--</span>
          </div>
          <div class="flex justify-between text-sm">
            <span class="text-slate-500">Phí vận chuyển</span>
            <span class="font-semibold text-green-600 phi-van-chuyen">--</span>
          </div>
          <div class="flex justify-between text-sm">
            <span class="text-slate-500">Giảm giá</span>
            <span class="font-semibold text-red-500 giam-gia">0₫</span>
          </div>
          <div class="flex justify-between text-sm">
            <span class="text-slate-500">Shop Voucher</span>
            <span class="font-semibold text-red-500 voucher-discount">0₫</span>
          </div>

          <!-- Voucher Section -->
          <div class="pt-2 mt-2 border-t border-dashed border-slate-200">
            <button type="button" class="w-full flex items-center justify-between p-3 bg-gradient-to-r from-blue-50 to-indigo-50/50 hover:from-blue-100/80 hover:to-indigo-100/50 rounded-xl border border-blue-100 shadow-sm transition-all group" onclick="voucherUI.openModal()">
              <div class="flex items-center gap-2.5">
                <span class="material-symbols-outlined text-blue-600 text-[22px] group-hover:scale-110 transition-transform">confirmation_number</span>
                <span class="text-sm font-bold text-slate-800">Shop Voucher</span>
              </div>
              <div class="flex items-center gap-2">
                <div id="checkoutVoucherCodeDisplay" class="hidden">
                  <span class="text-[13px] font-bold text-blue-600 truncate block"></span>
                </div>
                <span id="checkoutVoucherActionText" class="text-[13px] text-blue-600 font-bold group-hover:underline">Chọn mã</span>
                <i class="fa-solid fa-chevron-right text-[12px] text-slate-400 group-hover:translate-x-1 transition-transform"></i>
              </div>
            </button>
          </div>
        </div>

        <!-- Total + CTA -->
        <div class="px-5 pb-5">
          <div class="flex justify-between items-center px-4 py-3 rounded-xl bg-blue-600 mb-3">
            <span class="text-sm font-bold text-blue-100">Tổng thanh toán</span>
            <span class="text-xl font-extrabold text-white tong-tien">--</span>
          </div>
          <button id="btn-submit-order"
            class="w-full flex items-center justify-center gap-2.5 bg-blue-600 hover:bg-blue-700 active:scale-[0.98] text-white font-bold py-4 rounded-xl shadow-lg shadow-blue-600/25 transition-all text-base">
            <span class="material-symbols-outlined text-[20px]">shopping_bag</span>
            ĐẶT HÀNG NGAY
          </button>
          <p class="text-center text-[11px] text-slate-400 mt-2">Đặt hàng là bạn đồng ý với <a href="#" class="underline hover:text-blue-500">Chính sách dịch vụ</a></p>
        </div>
      </div>
    </div>

  </div>
</main>




<div id="addressListModal" class="modal-backdrop">
  <div class="bg-white w-full max-w-2xl max-h-[85vh] rounded-2xl overflow-hidden shadow-2xl flex flex-col relative m-4" onclick="event.stopPropagation()">
    
    <div class="p-5 border-b flex justify-between items-center bg-gray-50">
      <h3 class="font-bold text-lg text-gray-800">Chọn địa chỉ giao hàng</h3>
      <button type="button" class="flex items-center gap-1 px-4 py-2 bg-blue-50 text-blue-600 text-sm font-bold rounded-lg hover:bg-blue-100 transition-colors" onclick="openMapModal()">
        <span class="material-symbols-outlined text-sm">add</span> Thêm địa chỉ mới
      </button>
    </div>

    <div class="p-5 overflow-y-auto custom-scrollbar flex-1 space-y-4 bg-white" id="address-list-container">
        <p class="text-center text-gray-500 text-sm py-4">Đang tải danh sách địa chỉ...</p>
    </div>

    <div class="p-4 border-t flex justify-end gap-3 bg-gray-50">
      <button type="button" class="px-6 py-2.5 border border-gray-300 rounded-xl font-bold text-gray-600 hover:bg-gray-100 transition-colors" onclick="toggleModal('addressListModal', false)">Hủy</button>
      <button type="button" class="px-6 py-2.5 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 transition-colors" onclick="saveSelectedAddress()">Xác nhận</button>
    </div>
  </div>
</div>

<div id="mapModal" class="modal-backdrop">
    <div class="bg-white w-full max-w-2xl rounded-2xl overflow-hidden shadow-2xl flex flex-col relative m-4" onclick="event.stopPropagation()">           
      <div class="p-4 border-b flex justify-between items-center bg-gray-50">
          <div class="flex items-center gap-3">
              <button type="button" class="text-gray-500 hover:text-blue-600 transition-colors w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-200" onclick="backToAddressList()">
                  <span class="material-symbols-outlined">arrow_back</span>
              </button>
              <h3 class="font-bold text-lg text-gray-800">Thêm địa chỉ mới</h3>
          </div>
          <button type="button" class="text-gray-400 hover:text-red-500 transition-colors" onclick="toggleModal('mapModal', false)">
              <span class="material-symbols-outlined text-3xl">close</span>
          </button>
      </div>

      <div class="p-3 bg-white flex gap-2 border-b z-[9000]">
          <div class="relative flex-1">
              <span class="material-symbols-outlined absolute left-3 top-2.5 text-gray-400">search</span>
              <input id="map-search-input" type="text" placeholder="Nhập địa chỉ cần tìm (VD: FPT Polytechnic)..." 
                     class="w-full pl-10 pr-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-600 outline-none text-sm bg-gray-50">
          </div>
          <button type="button" id="search-location-btn" class="px-5 py-2 bg-blue-600 text-white text-sm font-bold rounded-lg hover:bg-blue-700 transition-colors">
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
          <button type="button" id="confirm-address-btn" class="px-8 py-3 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 shadow-lg shadow-blue-600/30 transition-transform active:scale-95 whitespace-nowrap">
              Ghi nhận
          </button>
      </div>
  </div>
</div>

<script>
    // 1. lấy dữ liệu từ api lúc vừa vào trang checkout
    let globalUserName = "Khách";
    let globalUserPhone = "";

    document.addEventListener("DOMContentLoaded", () => {
        const userId = document.getElementById('user-id').value;
        
        fetch(`https:// polygearid.ivi.vn/back-end/api/account?user_id=${userid}`)
            .then(res => res.json())
            .then(data => {
                if(data && data.length > 0) {
                    const user = data[0];
                    globalUserName = user.user_name || "Khách hàng";
                    globalUserPhone = user.phone_number || "";

                    // đổ tên và sđt ra ui thanh toán
                    document.getElementById('cus-name').innerText = globalUserName;
                    document.getElementById('cus-phone').innerText = globalUserPhone;

                    // xử lý địa chỉ (lấy cái mặc định đầu tiên)
                    let defaultAddr = "Bạn chưa có địa chỉ nào. Vui lòng thêm!";
                    if(user.address && user.address.length > 0) {
                        const defObj = user.address.find(a => a.status === "1") || user.address[0];
                        defaultAddr = defObj.address;
                        
                        if (defObj.receiver_name) {
                            document.getElementById('cus-name').innerText = defObj.receiver_name;
                        }
                        if (defObj.receiver_phone) {
                            document.getElementById('cus-phone').innerText = defObj.receiver_phone;
                        }

                        // render toàn bộ vào modal list
                        renderAddressList(user.address, defaultAddr);
                    } else {
                        document.getElementById('address-list-container').innerHTML = '<p class="text-center text-gray-500 text-sm py-4">Bạn chưa có địa chỉ giao hàng nào.</p>';
                    }

                    // đổ địa chỉ chọn sẵn ra màn hình ngoài
                    document.getElementById('main-ui-address').innerText = defaultAddr;
                    document.getElementById('cus-address').value = defaultAddr;
                }
            })
            .catch(err => console.error("Lỗi tải API user:", err));
    });

    // hàm vẽ list địa chỉ
    function renderAddressList(addresses, selectedAddress) {
        const container = document.getElementById('address-list-container');
        let html = '';
        addresses.forEach(addr => {
            const isSelected = (addr.address === selectedAddress);
            const labelClass = isSelected ? "border-blue-500 bg-blue-50/20" : "border-gray-200";
            
            html += `
                <label class="flex items-start gap-3 p-4 border rounded-xl cursor-pointer transition-colors ${labelClass}">
                    <input type="radio" name="selected_address" value="${addr.address}" data-name="${addr.receiver_name || ''}" data-phone="${addr.receiver_phone || ''}" ${isSelected ? 'checked' : ''} class="mt-1 w-4 h-4 text-blue-600">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1.5">
                            <span class="font-bold text-gray-800">${addr.receiver_name}</span>
                            <span class="text-gray-300 text-sm">|</span>
                            <span class="text-gray-600 text-sm font-semibold">${addr.receiver_phone}</span>
                        </div>
                        <p class="text-sm text-gray-600">${addr.address}</p>
                    </div>
                </label>
            `;
        });
        container.innerHTML = html;
    }

    // 2. các hàm xử lý ui modal
    function toggleModal(modalId, show) {
        const modal = document.getElementById(modalId);
        if (!modal) return;
        if (show) {
            modal.classList.add("active");
            document.body.style.overflow = "hidden";
        } else {
            modal.classList.remove("active");
            if (!document.querySelector(".modal-backdrop.active")) {
                document.body.style.overflow = "auto";
            }
        }
    }

    window.onclick = function (event) {
        if (event.target.classList.contains("modal-backdrop")) {
            toggleModal(event.target.id, false);
        }
    };

    function openAddressListModal() { toggleModal('addressListModal', true); }
    
    function backToAddressList() {
        toggleModal('mapModal', false);
        setTimeout(() => toggleModal('addressListModal', true), 200); 
    }

    // khi click chọn 1 cái trong danh sách và bấm xác nhận
    function saveSelectedAddress() {
        const selectedRadio = document.querySelector('input[name="selected_address"]:checked');
        if (selectedRadio) {
            const addressValue = selectedRadio.value;
            const receiverName = selectedRadio.getAttribute('data-name');
            const receiverPhone = selectedRadio.getAttribute('data-phone');
            
            // đưa thẳng ra ngoài giao diện checkout
            document.getElementById('main-ui-address').innerText = addressValue;
            document.getElementById('cus-address').value = addressValue; // cho hàm checkout.js cũ của ông xài
            
            if (receiverName) document.getElementById('cus-name').innerText = receiverName;
            if (receiverPhone) document.getElementById('cus-phone').innerText = receiverPhone;
        }
        toggleModal('addressListModal', false);
    }

    // đổi màu viền khi bấm chọn radio
    document.getElementById('address-list-container').addEventListener('change', function(e) {
        if (e.target.name === 'selected_address') {
            const labels = this.querySelectorAll('label');
            labels.forEach(lbl => {
                lbl.classList.remove('border-blue-500', 'bg-blue-50/20');
                lbl.classList.add('border-gray-200');
            });
            const checkedLabel = e.target.closest('label');
            checkedLabel.classList.remove('border-gray-200');
            checkedLabel.classList.add('border-blue-500', 'bg-blue-50/20');
        }
    });


    // 3. logic bản đồ track asia (thêm địa chỉ mới)
    let map, marker;
    function openMapModal() {
        toggleModal('addressListModal', false);
        setTimeout(() => toggleModal('mapModal', true), 200);
        
        if (!map) {
            const fallbackLngLat = [106.6297, 10.8231]; 
            map = new maplibregl.Map({
                container: 'map-canvas',
                style: 'https:// maps.track-asia.com/styles/v1/streets.json?key=public_key',
                center: fallbackLngLat, zoom: 16, attributionControl: false 
            });
            map.addControl(new maplibregl.NavigationControl(), 'bottom-right');
            const geolocate = new maplibregl.GeolocateControl({
                positionOptions: { enableHighAccuracy: true },
                trackUserLocation: false, showAccuracyCircle: false
            });
            map.addControl(geolocate, 'bottom-right');
            marker = new maplibregl.Marker({ draggable: true, color: "#2563eb" })
                .setLngLat(fallbackLngLat).addTo(map);

            geolocate.on('geolocate', function (e) {
                const lon = e.coords.longitude; const lat = e.coords.latitude;
                document.getElementById("current-selected-address").value = "Đang tìm địa chỉ...";
                marker.setLngLat([lon, lat]);
                getAddressFromCoords(lat, lon);
            });
            getAddressFromCoords(fallbackLngLat[1], fallbackLngLat[0]);
            
            marker.on('dragend', function () {
                document.getElementById("current-selected-address").value = "Đang tìm địa chỉ...";
                const lngLat = marker.getLngLat();
                getAddressFromCoords(lngLat.lat, lngLat.lng);
            });
            
            map.on('click', function (e) {
                document.getElementById("current-selected-address").value = "Đang tìm địa chỉ...";
                marker.setLngLat(e.lngLat);
                getAddressFromCoords(e.lngLat.lat, e.lngLat.lng);
            });
            map.on('load', function() { geolocate.trigger(); });
        }
        setTimeout(() => { map.resize(); }, 300);
    }

    document.getElementById("search-location-btn").onclick = () => {
        const query = document.getElementById("map-search-input").value;
        if (!query) return;
        const btn = document.getElementById("search-location-btn");
        btn.innerText = "...";
        fetch(`https:// nominatim.openstreetmap.org/search?format=jsonv2&q=${encodeuricomponent(query)}&countrycodes=vn`)
            .then(res => res.json())
            .then(data => {
                btn.innerText = "Tìm";
                if (data && data.length > 0) {
                    const lat = parseFloat(data[0].lat); const lon = parseFloat(data[0].lon);
                    map.flyTo({ center: [lon, lat], zoom: 17, essential: true });
                    marker.setLngLat([lon, lat]);
                    getAddressFromCoords(lat, lon);
                } else { alert("Không tìm thấy địa chỉ này!"); }
            })
            .catch(err => { btn.innerText = "Tìm"; });
    };

    document.getElementById("map-search-input").addEventListener("keypress", function(event) {
        if (event.key === "Enter") {
            event.preventDefault(); document.getElementById("search-location-btn").click();
        }
    });

    function getAddressFromCoords(lat, lng) {
        fetch(`https:// nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}`, {
            headers: { 'Accept-Language': 'vi-VN,vi;q=0.9,en;q=0.8' }
        })
        .then(res => res.json())
        .then(data => {
            if (data && data.display_name) {
                document.getElementById("current-selected-address").value = data.display_name;
            } else {
                document.getElementById("current-selected-address").value = "Không tìm thấy địa chỉ cụ thể.";
            }
        });
    }

    // ghi nhận địa chỉ từ map và lưu xuống db
    document.getElementById("confirm-address-btn").onclick = (event) => {
        const newAddress = document.getElementById("current-selected-address").value;
        const newName = document.getElementById("new-receiver-name").value.trim();
        const newPhone = document.getElementById("new-receiver-phone").value.trim();

        if (!newName || !newPhone) {
            alert("Vui lòng nhập tên người nhận và số điện thoại.");
            return;
        }
        
        if (newAddress !== "Đang tìm địa chỉ..." && newAddress !== "Đang tải tọa độ...") {
            const btn = event.currentTarget;
            const originalHTML = btn.innerHTML;
            btn.innerHTML = "Đang xử lý...";
            btn.disabled = true;

            const formData = new FormData();
            formData.append('user_id', document.getElementById('user-id').value);
            formData.append('address', newAddress);
            formData.append('receiver_name', newName);
            formData.append('receiver_phone', newPhone);

            // vẫn gọi api thêm địa chỉ như bình thường (nó sẽ lưu xuống db)
            fetch('https:// polygearid.ivi.vn/back-end/api/address', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(result => {
                btn.innerHTML = originalHTML;
                btn.disabled = false;

                if (result.status === 'success') {
                    // xóa trạng thái checked của mấy cái cũ
                    const labels = document.querySelectorAll('#address-list-container label');
                    labels.forEach(lbl => {
                        lbl.classList.remove('border-blue-500', 'bg-blue-50/20');
                        lbl.classList.add('border-gray-200');
                        const rdo = lbl.querySelector('input');
                        if(rdo) rdo.checked = false;
                    });

                    // tạo ui cho địa chỉ vừa thêm (checked sẵn để order)
                    const newAddressHTML = `
                      <label class="flex items-start gap-3 p-4 border rounded-xl cursor-pointer transition-colors border-blue-500 bg-blue-50/20">
                        <input type="radio" name="selected_address" value="${newAddress}" data-name="${newName}" data-phone="${newPhone}" checked class="mt-1 w-4 h-4 text-blue-600">
                        <div class="flex-1">
                          <div class="flex items-center gap-2 mb-1.5">
                              <span class="font-bold text-gray-800">${newName}</span>
                              <span class="text-gray-300 text-sm">|</span>
                              <span class="text-gray-600 text-sm font-semibold">${newPhone}</span>
                          </div>
                          <p class="text-sm text-gray-600">${newAddress}</p>
                          <div class="mt-2">
                             <span class="inline-block px-2 py-0.5 bg-green-50 text-green-600 text-[10px] uppercase font-bold rounded border border-green-200">Mới thêm</span>
                          </div>
                        </div>
                      </label>
                    `;

                    // push vào container
                    const container = document.getElementById('address-list-container');
                    // xóa dòng "bạn chưa có địa chỉ" nếu có
                    if(container.innerHTML.includes("Bạn chưa có địa chỉ")) container.innerHTML = '';
                    container.insertAdjacentHTML('afterbegin', newAddressHTML);

                    // set luôn ra ui thanh toán
                    document.getElementById('main-ui-address').innerText = newAddress;
                    document.getElementById('cus-address').value = newAddress;
                    document.getElementById('cus-name').innerText = newName;
                    document.getElementById('cus-phone').innerText = newPhone;

                    // reset form thêm địa chỉ
                    document.getElementById("new-receiver-name").value = "";
                    document.getElementById("new-receiver-phone").value = "";

                    backToAddressList();
                } else {
                    alert("Lỗi thêm địa chỉ: " + (result.message || "Không xác định"));
                }
            })
            .catch(error => {
                btn.innerHTML = originalHTML;
                btn.disabled = false;
                console.error('Lỗi API:', error);
                alert("Lỗi mạng khi thêm địa chỉ!");
            });
        }
    };
</script>

<link rel="stylesheet" href="css/tailwind.css?v=1.0.4">
<?php include 'voucher_modal.php'; ?>
<script src="js/checkout/checkout.js"></script>
</body>
