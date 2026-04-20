document.addEventListener("DOMContentLoaded", () => {
  const storeElement = document.getElementById("api-data-store");
  const orderContainer = document.getElementById("order-list-container");
  const tabButtons = document.querySelectorAll(".tab-btn");

  // biến toàn cục để lưu lại cục data 8 list từ api (để chuyển tab không phải load lại)
  let orderDataStore = {};

  if (!storeElement) return;

  const userId = storeElement.dataset.id;
  // 1. gọi api lấy data (có gắn cờ gửi kèm session cookie)
  fetch(`https:// polygearid.ivi.vn/back-end/api/account/orders?id=${userid}`, {
    credentials: "include"
  })
    .then((res) => res.json())
    .then((res) => {
      if (res.status === "success") {
        orderDataStore = res.data;
        // mặc định load tab "all" đầu tiên
        updateTabCounts(orderDataStore);
        renderOrders(orderDataStore.all);
      }
    })
    .catch((err) => {
      console.error("Lỗi lấy dữ liệu đơn hàng:", err);
      orderContainer.innerHTML = `<p class="text-center text-red-500 py-8">Lỗi tải dữ liệu. Vui lòng thử lại sau!</p>`;
    });

  // 2. logic click chuyển tab
  tabButtons.forEach((btn) => {
    btn.addEventListener("click", function () {
      // bỏ active hết các nút cũ
      tabButtons.forEach((b) => {
        b.className =
          "tab-btn flex-1 min-w-[120px] py-4 text-center text-sm font-body font-medium text-slate-500 hover:text-primary transition-colors";
        b.style.borderBottom = "none";
        b.style.color = "";
      });

      // gắn class active cho nút vừa click
      this.className =
        "tab-btn active-tab flex-1 min-w-[120px] py-4 text-center text-sm font-body font-bold transition-all";
      this.style.borderBottom = "2px solid #2a83e9";
      this.style.color = "#2a83e9";

      // reset ô tìm kiếm khi chuyển tab
      const searchInput = document.getElementById("search-order");
      if (searchInput) searchInput.value = "";

      // lấy tên tab (all, completed, cancelled...) và render data tương ứng
      const tabName = this.dataset.tab;
      renderOrders(orderDataStore[tabName] || []);
    });
  });

  // 2.1 logic tìm kiếm đơn hàng
  const searchInput = document.getElementById("search-order");
  if (searchInput) {
    searchInput.addEventListener("input", function (e) {
      const keyword = e.target.value.toLowerCase().trim();
      const activeTabBtn = document.querySelector(".tab-btn.active-tab");
      const tabName = activeTabBtn ? activeTabBtn.dataset.tab : "all";
      const currentOrders = orderDataStore[tabName] || [];

      if (keyword === "") {
        renderOrders(currentOrders);
        return;
      }

      const filteredOrders = currentOrders.filter((order) => {
        // tìm theo mã đơn hàng
        const matchCode = order.order_code.toLowerCase().includes(keyword);
        
        // tìm theo tên sản phẩm trong đơn
        const matchProduct = order.items.some((item) => 
          item.name.toLowerCase().includes(keyword)
        );

        return matchCode || matchProduct;
      });

      renderOrders(filteredOrders);
    });
  }

  // 3. hàm đẻ ra html cho từng đơn hàng
  function renderOrders(ordersArray) {
    if (ordersArray.length === 0) {
      orderContainer.innerHTML = `
        <div class="bg-white rounded-xl py-16 text-center shadow-sm">
            <span class="material-symbols-outlined text-6xl text-gray-300 mb-4">receipt_long</span>
            <p class="text-gray-500 font-body">Chưa có đơn hàng nào!</p>
        </div>`;
      return;
    }

    let htmlContent = "";

    ordersArray.forEach((order) => {
      // xử lý ui trạng thái (đổi màu theo status)
      let statusLabel = "";
      let statusColorClass = "";

      let displayStatus = order.internal_status || order.status;

      switch (displayStatus) {
        case "completed":
          statusLabel = "GIAO HÀNG THÀNH CÔNG";
          statusColorClass = "text-primary";
          break;
        case "cancelled":
          statusLabel = "ĐÃ HỦY";
          statusColorClass = "text-red-500";
          break;
        case "pending_payment":
          statusLabel = "CHỜ THANH TOÁN";
          statusColorClass = "text-orange-500";
          break;
        case "pending_confirmation":
          statusLabel = "CHỜ XÁC NHẬN";
          statusColorClass = "text-yellow-500";
          break;
        case "shipping":
          statusLabel = "VẬN CHUYỂN";
          statusColorClass = "text-blue-500";
          break;
        case "delivering":
          statusLabel = "ĐANG GIAO";
          statusColorClass = "text-teal-500";
          break;
        default:
          statusLabel = displayStatus.toUpperCase();
          statusColorClass = "text-gray-500";
      }

      // xử lý render danh sách các sản phẩm (items) trong đơn hàng đó
      let itemsHtml = "";
      order.items.forEach((item) => {

        itemsHtml += `
        <div class="flex gap-4 mb-4 last:mb-0">
          <a href="/detail/${item.sku}" class="w-20 h-20 rounded bg-surface-container overflow-hidden flex-shrink-0 ${displayStatus === "cancelled" ? "grayscale" : ""} hover:opacity-80 transition-opacity">
            <img class="w-full h-full object-cover" src="${item.image}" alt="Hình sản phẩm" onerror="this.src='/img/default.png'" />
          </a>
          <div class="flex-1 min-w-0">
            <h3 class="font-body text-sm font-medium ${displayStatus === "cancelled" ? "text-gray-400" : "text-on-surface"} truncate hover:text-primary transition-colors">
              <a href="/detail/${item.sku}">${item.name}</a>
            </h3>
            <p class="text-xs text-slate-500 mt-1">
              SKU: <span class="font-medium text-slate-700">${item.sku}</span> <span class="mx-1 text-slate-300">|</span> 
              Phiên bản: <span class="font-medium text-slate-700 capitalize">${item.variant_name ? item.variant_name.split(' - ').join(' | ') : 'Mặc định'}</span>
            </p>
            <p class="text-sm font-medium mt-1 font-body">x${item.quantity}</p>
          </div>
          <div class="text-right flex flex-col items-end">
            <span class="text-sm font-body font-semibold ${displayStatus === "cancelled" ? "text-gray-400 line-through opacity-60" : "text-primary"}">
              ${formatMoney(item.price)}
            </span>
            ${displayStatus === "completed" ? (
            !item.has_reviewed ? `
                <button class="mt-2 px-4 py-1 border border-primary text-primary text-xs font-bold rounded-md hover:bg-primary/5 transition-colors open-history-review-btn"
                  data-sku="${item.sku}" 
                  data-detail-id="${item.order_detail_id}" 
                  data-name="${item.name.replace(/"/g, '&quot;')}" 
                  data-attr="${item.variant_name ? item.variant_name.split(' - ').join(' | ').replace(/"/g, '&quot;') : 'Mặc định'}" 
                  data-img="${item.image}">
                  Đánh giá
                </button>
              ` : `
                <button class="mt-2 px-4 py-1 border border-slate-200 text-slate-400 text-xs font-bold rounded-md cursor-not-allowed" disabled>
                  Đã đánh giá
                </button>
              `
          ) : ""}
          </div>
        </div>`;
      });

      // ráp html tổng thể cho 1 card đơn hàng
      htmlContent += `
      <div class="bg-white rounded-xl shadow-md border border-slate-100 overflow-hidden ${displayStatus === "cancelled" ? "opacity-90" : ""}">
        
        <div class="px-6 py-4 flex justify-between items-center bg-white border-b border-gray-50">
          <div class="flex items-center gap-3">
            <span class="font-headline font-bold text-sm">PolyGear Official</span>
            <span class="text-xs text-gray-400">#${order.order_code}</span>
          </div>
          <div class="flex items-center gap-2">
            ${displayStatus === "completed" ? `<span class="material-symbols-outlined text-primary text-lg">local_shipping</span>` : ""}
            <span class="text-xs font-body font-bold uppercase ${statusColorClass} mr-2">${statusLabel}</span>
            <div class="w-[1px] h-4 bg-slate-200"></div>
            <button class="text-primary hover:text-blue-700 font-body text-sm font-semibold flex items-center gap-1 transition-colors open-history-detail-btn ml-1" data-code="${order.order_code}">
              Chi tiết <span class="material-symbols-outlined text-[16px]">chevron_right</span>
            </button>
          </div>
        </div>

        <div class="p-6">
          ${itemsHtml}
        </div>

        <div class="bg-slate-50/50 px-6 py-6 border-t border-dashed border-slate-200">
          <div class="space-y-1 mb-4">
             <div class="flex justify-end gap-10 text-sm text-slate-500">
                <span>Phí vận chuyển:</span>
                <span class="w-24 text-right">${formatMoney(order.shipping_fee || 0)}</span>
             </div>
             ${order.discount > 0 ? `
             <div class="flex justify-end gap-10 text-sm text-red-500">
                <span>Giảm giá voucher:</span>
                <span class="w-24 text-right">-${formatMoney(order.discount)}</span>
             </div>` : ''}
          </div>
          <div class="flex justify-end items-center gap-2 mb-6">
            <span class="text-sm font-body text-on-surface">Thành tiền:</span>
            <span class="text-xl font-headline font-extrabold ${statusColorClass}">${formatMoney(order.total_price)}</span>
          </div>
          <div class="flex flex-wrap justify-end gap-3 flex-col items-end">
            ${order.cancel_reason ? `<div class="text-xs text-red-500 bg-red-50 px-3 py-1 rounded-full w-fit">Lý do hủy: ${order.cancel_reason}</div>` : ''}
            <div class="flex gap-3 mt-1">
                ${renderButtonsByStatus(order, displayStatus)}
            </div>
          </div>
        </div>
      </div>`;
    });

    orderContainer.innerHTML = htmlContent;
  }

  function formatMoney(amount) {
    return new Intl.NumberFormat("vi-VN", {
      style: "currency",
      currency: "VND",
    }).format(amount);
  }

  function renderButtonsByStatus(order, displayStatus) {
    const st = order.status;
    let buttons = '';

    if (st === "pending") {
      buttons = `<button class="px-6 py-2.5 bg-red-50 text-red-600 font-body text-sm font-semibold rounded-md border border-red-200 hover:bg-red-100 transition-colors" onclick="updateMyOrderStatus('${order.order_code}', 'cancelled')">Hủy đơn</button>`;
      if (order.payment_method === 'bank' && order.payment_status === 'unpaid') {
        buttons += `<button class="px-8 py-2.5  text-white font-body text-sm font-bold rounded-md shadow-lg ml-3" style="background-color: var(--blue-primary) !important;" onclick="repayOrder('${order.order_code}', this)">Thanh toán ngay</button>`;
      }
    } else if (st === 'delivering') {
      buttons = `<button class="px-8 py-2.5 bg-green-500 text-white font-body text-sm font-bold rounded-md shadow-lg active:scale-95 transition-transform" onclick="updateMyOrderStatus('${order.order_code}', 'completed')">Đã nhận được hàng</button>`;
    } else if (st === 'completed') {
      buttons = `
            <button class="px-6 py-2.5 font-body text-sm font-semibold rounded-md shadow-sm border hover:bg-surface-container-low transition-colors text-slate-500 border-slate-300" onclick="updateMyOrderStatus('${order.order_code}', 'returning')">Trả hàng / Hoàn tiền</button>
            <button class="px-6 py-2.5 font-body text-sm font-semibold rounded-md shadow-sm border hover:bg-surface-container-low transition-colors text-white border-primary" style="background-color: #2a83e9 !important">Mua lại</button>`;

    } else if (st === 'returning') {
      buttons = `<button class="px-8 py-2.5 bg-purple-50 text-purple-600 font-body text-sm font-bold rounded-md border border-purple-200 cursor-not-allowed" disabled>Đang xử lý trả hàng...</button>`;
    } else if (st === 'returned') {
      buttons = `
            <button class="px-8 py-2.5 bg-slate-100 text-slate-500 font-body text-sm font-bold rounded-md border border-slate-200 cursor-not-allowed" disabled>Đã hoàn trả</button>
            <button class="px-6 py-2.5 font-body text-sm font-semibold rounded-md shadow-sm border hover:bg-surface-container-low transition-colors text-white border-primary" style="background-color: #2a83e9 !important">Mua lại</button>`;
    } else if (st === 'cancelled' || st === 'failed') {
      buttons = `
            <button class="px-6 py-2.5 font-body text-sm font-semibold rounded-md shadow-sm border hover:bg-surface-container-low transition-colors text-white border-primary" style="background-color: #2a83e9 !important">Mua lại</button>`;
    } else {
      // shipping/processing
      buttons = `<button class="px-8 py-2.5 bg-slate-100 text-slate-500 font-body text-sm font-bold rounded-md border border-slate-200 cursor-not-allowed" disabled>Đơn hàng đang giao...</button>`;
    }

    return buttons;
  }
  function updateTabCounts(data) {
    tabButtons.forEach((btn) => {
      const tabName = btn.dataset.tab;
      if (tabName === "all") return;
      const count = data[tabName] ? data[tabName].length : 0;
      let originalText = btn.innerText.split(" (")[0];
      if (count > 0) {
        btn.innerText = `${originalText} (${count})`;
      } else {
        btn.innerText = originalText;
      }
    });
  }

  // 
  // ✍️ đánh giá từ lịch sử đơn hàng (verified purchase)
  // 
  const modalHTML = `
  <div id="history-review-modal" class="fixed inset-0 bg-black/60 z-[100] hidden flex items-center justify-center p-4 opacity-0 transition-opacity duration-300">
    <div class="z-[999999] absolute bg-white rounded-2xl w-full max-w-xl mx-auto shadow-2xl overflow-hidden transform scale-95 transition-transform duration-300">
      <div class="p-6 border-b border-slate-100 flex items-center justify-between">
        <h3 class="font-black text-xl text-slate-900">Đánh giá & nhận xét</h3>
        <button id="close-history-review-modal" class="text-slate-400 hover:text-slate-600 transition-colors">
          <span class="material-symbols-outlined font-bold">close</span>
        </button>
      </div>
      <div class="p-6">
        <div class="flex items-center gap-4 mb-6">
          <img src="" class="w-16 h-16 object-cover rounded-xl border border-slate-200" id="hist-review-modal-img">
          <div>
             <p class="font-bold text-slate-900" id="hist-review-modal-name"></p>
             <p class="text-xs text-slate-500 mt-1 capitalize" id="hist-review-modal-attr"></p>
          </div>
        </div>
        
        <h4 class="font-bold text-slate-800 mb-3 text-sm">Đánh giá chung</h4>
        <div class="flex items-center space-x-4 mb-8 px-8 justify-between" id="hist-star-rating-container">
            <label class="flex flex-col items-center gap-2 cursor-pointer group">
                <span class="material-symbols-outlined text-4xl text-slate-300 transition-colors" data-val="1">star</span>
                <span class="text-xs text-slate-500 font-medium">Rất Tệ</span>
            </label>
            <label class="flex flex-col items-center gap-2 cursor-pointer group">
                <span class="material-symbols-outlined text-4xl text-slate-300 transition-colors" data-val="2">star</span>
                <span class="text-xs text-slate-500 font-medium">Tệ</span>
            </label>
            <label class="flex flex-col items-center gap-2 cursor-pointer group">
                <span class="material-symbols-outlined text-4xl text-slate-300 transition-colors" data-val="3">star</span>
                <span class="text-xs text-slate-500 font-medium">Bình thường</span>
            </label>
            <label class="flex flex-col items-center gap-2 cursor-pointer group">
                <span class="material-symbols-outlined text-4xl text-slate-300 transition-colors" data-val="4">star</span>
                <span class="text-xs text-slate-500 font-medium">Tốt</span>
            </label>
            <label class="flex flex-col items-center gap-2 cursor-pointer group">
                <span class="material-symbols-outlined text-4xl text-amber-400 transition-colors" data-val="5">star</span>
                <span class="text-xs text-slate-500 font-medium">Tuyệt vời</span>
            </label>
        </div>

        <textarea id="hist-review-content" class="w-full rounded-xl border border-slate-200 focus:ring-2 focus:ring-primary/30 focus:border-primary text-sm p-4 h-32 mb-4 transition-all" placeholder="Xin mời chia sẻ một số cảm nhận về sản phẩm (nhập tối thiểu 15 kí tự)"></textarea>
        
        <button id="hist-submit-review-btn" class="w-full py-4 text-center bg-red-600 hover:bg-red-700 text-white font-bold rounded-xl transition-all shadow-md hover:shadow-lg shadow-red-500/20">
          GỬI ĐÁNH GIÁ (Đã mua hàng)
        </button>
      </div>
    </div>
  </div>`;

  if (!document.getElementById("history-review-modal")) {
    document.body.insertAdjacentHTML('beforeend', modalHTML);
  }

  const histReviewModal = document.getElementById("history-review-modal");
  const histCloseBtn = document.getElementById("close-history-review-modal");
  const histStarContainer = document.getElementById("hist-star-rating-container");
  const histSubmitBtn = document.getElementById("hist-submit-review-btn");
  const histReviewContent = document.getElementById("hist-review-content");

  let reviewState = { sku: null, detail_id: null, rating: 5, btnRef: null };

  // ủy quyền sự kiện mở modal cho container
  orderContainer.addEventListener("click", (e) => {
    const btn = e.target.closest('.open-history-review-btn');
    if (btn) {
      reviewState.sku = btn.dataset.sku;
      reviewState.detail_id = btn.dataset.detailId;
      reviewState.rating = 5;
      reviewState.btnRef = btn;

      document.getElementById("hist-review-modal-name").innerText = btn.dataset.name;
      const attrEl = document.getElementById("hist-review-modal-attr");
      if (attrEl) attrEl.innerText = "Phiên bản: " + btn.dataset.attr;

      document.getElementById("hist-review-modal-img").src = btn.dataset.img;
      histReviewContent.value = "";
      updateHistStarUI(5);

      histReviewModal.classList.remove("hidden");
      setTimeout(() => {
        histReviewModal.classList.remove("opacity-0");
        histReviewModal.querySelector('div').classList.remove("scale-95");
        histReviewModal.querySelector('div').classList.add("scale-100");
      }, 10);
    }
  });

  histCloseBtn.addEventListener("click", () => {
    histReviewModal.classList.add("opacity-0");
    histReviewModal.querySelector('div').classList.remove("scale-100");
    histReviewModal.querySelector('div').classList.add("scale-95");
    setTimeout(() => histReviewModal.classList.add("hidden"), 300);
  });

  const histLabels = histStarContainer.querySelectorAll("label");
  histLabels.forEach((label, index) => {
    label.addEventListener("click", () => {
      reviewState.rating = index + 1;
      updateHistStarUI(reviewState.rating);
    });
  });

  function updateHistStarUI(rating) {
    histLabels.forEach((lbl, i) => {
      const starIcon = lbl.querySelector("span.material-symbols-outlined");
      if (i < rating) {
        starIcon.classList.remove("text-slate-300");
        starIcon.classList.add("text-amber-400");
        starIcon.style.fontVariationSettings = "'FILL' 1";
      } else {
        starIcon.classList.remove("text-amber-400");
        starIcon.classList.add("text-slate-300");
        starIcon.style.fontVariationSettings = "'FILL' 0";
      }
    });
  }

  histSubmitBtn.addEventListener("click", async () => {
    const content = histReviewContent.value.trim();
    if (content.length < 15) {
      alert("Vui lòng nhập cảm nhận ít nhất 15 kí tự nhé!");
      return;
    }

    histSubmitBtn.innerHTML = "ĐANG XỬ LÝ...";
    histSubmitBtn.disabled = true;

    try {
      const res = await fetch("https:// polygearid.ivi.vn/back-end/api/reviews/add", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        credentials: "include",
        body: JSON.stringify({
          sku: reviewState.sku,
          order_detail_id: reviewState.detail_id,
          rating: reviewState.rating,
          content: content,
          variant_snapshot: reviewState.btnRef ? reviewState.btnRef.dataset.attr : null
        })
      });

      const data = await res.json();
      if (data.status === 'success') {
        alert("Cảm ơn bạn đã đánh giá!");
        histCloseBtn.click();
        if (reviewState.btnRef) {
          reviewState.btnRef.outerHTML = `
          <button class="px-8 py-2.5 bg-slate-100 text-slate-400 font-body text-sm font-bold rounded-md border border-slate-200 cursor-not-allowed" disabled>
            Đã đánh giá
          </button>`;
        }
      } else {
        alert(data.message || "Lỗi khi gửi đánh giá.");
      }
    } catch (err) {
      alert("Có lỗi xảy ra, vui lòng thử lại.");
    } finally {
      histSubmitBtn.innerHTML = "GỬI ĐÁNH GIÁ";
      histSubmitBtn.disabled = false;
    }
  });

  // 
  // 🔍 xem chi tiết đơn hàng (order lookup flow)
  // 
  const detailModalHTML = `
  <div id="history-detail-modal" class="fixed inset-0 bg-black/60 z-[100] hidden flex items-center justify-center p-4 opacity-0 transition-opacity duration-300">
    <div class="z-[999999] absolute bg-white rounded-2xl w-full max-w-2xl mx-auto shadow-2xl overflow-hidden transform scale-95 transition-transform duration-300 flex flex-col max-h-[90vh]">
      <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50">
        <div>
           <h3 class="font-black text-xl text-slate-900">Chi tiết đơn hàng</h3>
           <p class="text-xs text-slate-500 mt-1" id="hist-detail-code">#---</p>
        </div>
        <button id="close-history-detail-modal" class="p-2 rounded-full hover:bg-slate-200 text-slate-400 hover:text-slate-600 transition-colors">
          <span class="material-symbols-outlined font-bold">close</span>
        </button>
      </div>
      
      <div class="p-6 overflow-y-auto flex-1 custom-scrollbar">
        <!-- Shipper Info -->
        <h4 class="font-bold text-slate-800 mb-3 text-sm flex items-center gap-2"><span class="material-symbols-outlined text-primary text-[18px]">location_on</span> Địa chỉ nhận hàng</h4>
        <div class="bg-slate-50 rounded-xl p-4 mb-6 border border-slate-100 text-sm">
           <p class="font-bold text-slate-800 mb-1" id="hist-detail-name"></p>
           <p class="text-slate-600 mb-1" id="hist-detail-phone"></p>
           <p class="text-slate-500" id="hist-detail-address"></p>
        </div>

        <!-- Order Items -->
        <h4 class="font-bold text-slate-800 mb-3 text-sm flex items-center gap-2"><span class="material-symbols-outlined text-primary text-[18px]">inventory_2</span> Sản phẩm</h4>
        <div id="hist-detail-items-container" class="space-y-4 mb-6">
           <div class="text-center py-4 text-slate-400 text-sm">Đang tải...</div>
        </div>

        <!-- Payment Info -->
        <h4 class="font-bold text-slate-800 mb-3 text-sm flex items-center gap-2"><span class="material-symbols-outlined text-primary text-[18px]">receipt_long</span> Phương thức & Thanh toán</h4>
        <div class="bg-slate-50 rounded-xl p-4 border border-slate-100 text-sm space-y-2">
           <div class="flex justify-between">
              <span class="text-slate-500">Thanh toán:</span>
              <span class="font-bold text-slate-800" id="hist-detail-payment-method"></span>
           </div>
           <div class="flex justify-between">
              <span class="text-slate-500">Tổng tiền hàng:</span>
              <span class="font-medium text-slate-600" id="hist-detail-subtotal"></span>
           </div>
           <div class="flex justify-between">
              <span class="text-slate-500">Phí vận chuyển:</span>
              <span class="font-medium text-slate-600" id="hist-detail-shipping"></span>
           </div>
           <div class="flex justify-between text-red-500">
              <span class="text-slate-500">Voucher giảm giá:</span>
              <span class="font-medium" id="hist-detail-discount"></span>
           </div>
           <div class="pt-2 mt-2 border-t border-slate-200 flex justify-between items-center text-lg">
              <span class="font-bold text-slate-800">Thành tiền:</span>
              <span class="font-black text-primary" id="hist-detail-total"></span>
           </div>
        </div>
      </div>
    </div>
  </div>`;

  if (!document.getElementById("history-detail-modal")) {
    document.body.insertAdjacentHTML('beforeend', detailModalHTML);
  }

  const detailModal = document.getElementById("history-detail-modal");
  const detailCloseBtn = document.getElementById("close-history-detail-modal");

  orderContainer.addEventListener("click", async (e) => {
    const btn = e.target.closest('.open-history-detail-btn');
    if (btn) {
      const orderCode = btn.dataset.code;

      // hiển thị modal loading
      document.getElementById("hist-detail-code").innerText = "#" + orderCode;
      document.getElementById("hist-detail-items-container").innerHTML = '<div class="text-center py-4 text-slate-400 text-sm"><i class="fa-solid fa-spinner fa-spin mr-2"></i> Đang tải thông tin đơn hàng...</div>';

      detailModal.classList.remove("hidden");
      setTimeout(() => {
        detailModal.classList.remove("opacity-0");
        detailModal.querySelector('div').classList.remove("scale-95");
        detailModal.querySelector('div').classList.add("scale-100");
      }, 10);

      try {
        const res = await fetch(`https:// polygearid.ivi.vn/back-end/api/account/order?code=${ordercode}`, {
          credentials: 'include'
        });
        const data = await res.json();

        if (data && data.info) {
          const info = data.info;
          const items = data.items || [];

          document.getElementById("hist-detail-name").innerText = info.receiver_name || "Khách hàng";
          document.getElementById("hist-detail-phone").innerText = info.receiver_phone || "---";
          document.getElementById("hist-detail-address").innerText = info.shipping_address || "---";

          let paymentMethods = {
            'cod': 'Thanh toán khi nhận hàng (COD)',
            'bank': 'Chuyển khoản ngân hàng (QR Pay)'
          };
          document.getElementById("hist-detail-payment-method").innerText = paymentMethods[info.payment_method] || info.payment_method;

          const subtotal = info.total_price - (info.shipping_fee || 0) + (info.discount || 0);
          document.getElementById("hist-detail-subtotal").innerText = formatMoney(subtotal);
          document.getElementById("hist-detail-shipping").innerText = formatMoney(info.shipping_fee || 0);
          document.getElementById("hist-detail-discount").innerText = "-" + formatMoney(info.discount || 0);
          document.getElementById("hist-detail-total").innerText = formatMoney(info.total_price);

          let itemsHTML = "";
          items.forEach(it => {
            itemsHTML += `
              <div class="flex gap-4 p-3 border border-slate-100 rounded-lg">
                 <a href="/detail/${it.sku}" class="w-16 h-16 object-cover rounded-md bg-slate-50 hover:opacity-80 transition-opacity">
                    <img src="${it.main_image_url || 'img/placeholder.png'}" class="w-full h-full object-cover rounded-md">
                 </a>
                 <div class="flex-1">
                    <h4 class="text-sm font-bold text-slate-900 truncate hover:text-primary transition-colors">
                        <a href="/detail/${it.sku}">${it.product_name || 'Sản phẩm'}</a>
                    </h4>
                    <p class="text-xs text-slate-500 mt-0.5">Phiên bản: ${it.variant_name || 'Mặc định'}</p>
                    <div class="flex justify-between items-center mt-2">
                       <span class="text-xs text-slate-600 font-medium">x${it.quantity}</span>
                       <span class="text-sm font-bold text-primary">${formatMoney(it.unit_price)}</span>
                    </div>
                 </div>
              </div>`;
          });

          document.getElementById("hist-detail-items-container").innerHTML = itemsHTML;
        } else {
          document.getElementById("hist-detail-items-container").innerHTML = '<div class="text-center py-4 text-red-500 text-sm">Không thể tải chi tiết đơn hàng hoặc đơn hàng không tồn tại.</div>';
        }
      } catch (e) {
        document.getElementById("hist-detail-items-container").innerHTML = '<div class="text-center py-4 text-red-500 text-sm">Lỗi kết nối khi tải dữ liệu!</div>';
      }
    }
  });

  detailCloseBtn.addEventListener("click", () => {
    detailModal.classList.add("opacity-0");
    detailModal.querySelector('div').classList.remove("scale-100");
    detailModal.querySelector('div').classList.add("scale-95");
    setTimeout(() => detailModal.classList.add("hidden"), 300);
  });

});

async function updateMyOrderStatus(orderCode, newStatus) {
  let defaultMsg = "Xác nhận thay đổi trạng thái đơn hàng?";
  let cancelReason = null;

  if (newStatus === 'cancelled') {
      defaultMsg = "Bạn chắc chắn muốn hủy đơn hàng này?";
      cancelReason = prompt("Vui lòng nhập lý do hủy đơn (Bắt buộc):");
      if (cancelReason === null || cancelReason.trim() === "") {
          alert("Bạn phải nhập lý do để hủy đơn hàng.");
          return;
      }
  }
  if (newStatus === 'completed') defaultMsg = "Xác nhận bạn đã nhận được hàng và hàng hóa đúng như mô tả?";
  if (newStatus === 'returning') defaultMsg = "Xác nhận tạo yêu cầu Trả hàng / Hoàn tiền? Bạn sẽ cần trả lại hàng qua đường bưu điện.";

  if (newStatus !== 'cancelled' && !confirm(defaultMsg)) return;

  try {
    const payload = { order_code: orderCode, status: newStatus };
    if (cancelReason) payload.cancel_reason = cancelReason;

    const res = await fetch(`https:// polygearid.ivi.vn/back-end/api/account/orders/status`, {
      credentials: 'include',
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    const data = await res.json();
    if (data.status === 'success') {
      alert('Cập nhật trạng thái thành công!');
      window.location.reload(); // hoặc gọi lại hàm gọi api renderorders tùy ý, để nhanh nên gọi reload
    } else {
      alert('Lỗi: ' + data.message);
    }
  } catch (err) {
    console.error(err);
    alert('Lỗi kết nối máy chủ!');
  }
}

async function repayOrder(orderCode, btn) {
  const originalBtnText = btn.innerHTML;
  btn.innerHTML = '<span class="material-symbols-outlined align-middle mr-2">qr_code_scanner</span> ĐANG CHỜ QUÉT MÃ...';
  btn.disabled = true;
  btn.classList.add("opacity-75", "cursor-not-allowed");

  const popupWidth = 600;
  const popupHeight = 750;
  const left = (window.innerWidth - popupWidth) / 2;
  const top = (window.innerHeight - popupHeight) / 2;

  let payOsWindow = window.open(
    "",
    "ThanhToanPayOS",
    `width=${popupWidth},height=${popupHeight},top=${top},left=${left},toolbar=no,menubar=no,scrollbars=yes,resizable=yes`
  );

  const skeletonHTML = `
        <!DOCTYPE html>
        <html lang="vi">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Đang kết nối PayOS...</title>
            <script src="https:// cdn.tailwindcss.com"></script>
        </head>
        <body class="bg-slate-50 flex items-center justify-center min-h-screen m-0">
            <div class="bg-white p-8 rounded-2xl shadow-sm border border-slate-100 w-full max-w-sm flex flex-col items-center space-y-6 animate-pulse">
                <div class="h-8 bg-slate-200 rounded-md w-32 mb-2"></div>
                <div class="w-full flex flex-col items-center space-y-3">
                    <div class="h-4 bg-slate-200 rounded w-3/4"></div>
                    <div class="h-6 bg-slate-200 rounded w-1/2"></div>
                </div>
                <div class="w-64 h-64 bg-slate-200 rounded-2xl"></div>
                <div class="w-full space-y-2 mt-4 flex flex-col items-center">
                    <div class="h-3 bg-slate-200 rounded w-full"></div>
                    <div class="h-3 bg-slate-200 rounded w-5/6"></div>
                </div>
                <div class="h-12 bg-slate-200 rounded-xl w-full mt-4"></div>
            </div>
        </body>
        </html>`;
  if (payOsWindow) payOsWindow.document.write(skeletonHTML);

  try {
    const response = await fetch("https:// polygearid.ivi.vn/back-end/api/checkout/repay", {
      credentials: 'include',
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ order_code: orderCode })
    });
    const result = await response.json();

    if (result.status === "success" && result.pay_url) {
      if (payOsWindow) {
        payOsWindow.location.href = result.pay_url;
        const checkWindowClosed = setInterval(() => {
          if (payOsWindow.closed) {
            clearInterval(checkWindowClosed);
            window.location.reload();
          }
        }, 500);
      } else {
        window.location.href = result.pay_url;
      }
    } else {
      if (payOsWindow) payOsWindow.close();
      alert("Có lỗi xảy ra: " + (result.message || "Không thể thanh toán lúc này."));
      btn.innerHTML = originalBtnText;
      btn.disabled = false;
      btn.classList.remove("opacity-75", "cursor-not-allowed");
    }
  } catch (error) {
    if (payOsWindow) payOsWindow.close();
    console.error("Lỗi:", error);
    alert("Hệ thống bận, vui lòng thử lại sau.");
    btn.innerHTML = originalBtnText;
    btn.disabled = false;
    btn.classList.remove("opacity-75", "cursor-not-allowed");
  }
}

