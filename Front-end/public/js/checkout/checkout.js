const cartDataStr = localStorage.getItem("cartData");
const cart = cartDataStr
  ? JSON.parse(cartDataStr)
  : { items: {}, totalDiscount: 0 };

if (Object.keys(cart.items).length == 0) {
  window.location.href = "/home";
}

document.addEventListener("DOMContentLoaded", async () => {
  // 
  // phần 1: render giao diện tóm tắt đơn hàng từ localstorage
  // 
  const product_list = document.querySelector(".product-list");
  const tam_tinh = document.querySelector(".tam-tinh");
  const tong_tien = document.querySelector(".tong-tien");
  const giam_gia = document.querySelector(".giam-gia");
  const voucher_discount_el = document.querySelector(".voucher-discount");
  const phi_van_chuyen = document.querySelector(".phi-van-chuyen");

  let order = [];
  let priceTotal = 0;

  if (cart.items) {
    priceTotal = Object.values(cart.items)
      .filter((item) => item.selected === true)
      .reduce((tong, item) => tong + item.price * item.quantity, 0);

    order = Object.values(cart.items).filter((item) => item.selected === true);
  }

  let html = "";
  order.forEach((val) => {
    let price = val.price.toLocaleString("vi-VN");
    html += `<div class="flex gap-3 ">
            <div class="size-16 rounded bg-slate-100 light:bg-slate-800 overflow-hidden shrink-0">
                <img alt="${val.name}" class="product-img w-full h-full object-contain p-1" src="${val.main_img_url}" />
            </div>
            <div class="flex-grow">
                <p class="product-name text-sm font-medium line-clamp-2">${val.name}</p>
                <p class="text-xs text-slate-500 mt-1">SL: ${val.quantity}</p>
                <p class="product-price text-sm font-bold text-primary mt-1">${price}₫</p>
            </div>
        </div>`;
  });

  let phiVanChuyenValue = priceTotal > 1000000 ? 0 : 50000;
  let phivanchuyen = phiVanChuyenValue === 0 ? "Miễn phí" : "50.000đ";
  if (phi_van_chuyen) phi_van_chuyen.innerText = phivanchuyen;

  if (product_list) product_list.innerHTML = html;
  if (tam_tinh) tam_tinh.innerText = priceTotal.toLocaleString("vi-VN") + "đ";

  // hàm update tổng tiền có voucher
  const updateCheckoutTotal = () => {
    let finalPrice = priceTotal + phiVanChuyenValue;
    let extraDiscount = 0;
    
    if (typeof voucherUI !== 'undefined') {
      voucherUI.setCartTotal(priceTotal);
      const vDiscount = voucherUI.getDiscountAmount();
      
      const vDisplay = document.getElementById('checkoutVoucherCodeDisplay');
      const vAction = document.getElementById('checkoutVoucherActionText');
      
      if (vDiscount > 0) {
        extraDiscount = vDiscount;
        finalPrice -= vDiscount;
        if (vDisplay && vAction) {
          vDisplay.querySelector('span').textContent = 'Đã áp dụng mã: ' + voucherUI.selectedVoucher.code;
          vDisplay.classList.remove('hidden');
          vAction.textContent = 'Đổi mã';
        }
      } else {
        if (vDisplay && vAction) {
          vDisplay.classList.add('hidden');
          vAction.textContent = 'Chọn mã';
        }
      }
    }
    
    const cartDiscount = (cart.totalDiscount || 0);
    if (giam_gia) giam_gia.innerText = cartDiscount > 0 ? "-" + cartDiscount.toLocaleString("vi-VN") + "đ" : "0đ";
    if (voucher_discount_el) voucher_discount_el.innerText = extraDiscount > 0 ? "-" + extraDiscount.toLocaleString("vi-VN") + "đ" : "0đ";
    if (tong_tien) tong_tien.innerText = Math.max(0, finalPrice).toLocaleString("vi-VN") + "đ";
  };

  if (typeof voucherUI !== 'undefined') {
    voucherUI.setCallback(() => updateCheckoutTotal());
  }

  updateCheckoutTotal();

  // 
  // phần 2: xử lý gom data và bắn api (payos nằm ở đây)
  // 
  const btnSubmit = document.getElementById("btn-submit-order");

  if (btnSubmit) {
    btnSubmit.addEventListener("click", async function (e) {
      e.preventDefault();

      // 1. kiểm tra rỗng & gom dữ liệu
      if (order.length === 0) {
        alert("Bạn chưa chọn sản phẩm nào để thanh toán!");
        return;
      }

      const name = document.getElementById("cus-name")?.innerText.trim();
      const phone = document.getElementById("cus-phone")?.innerText.trim();
      const email = document.getElementById("cus-email")?.value.trim();
      const address = document.getElementById("cus-address")?.value.trim();
      const paymentMethodEl = document.querySelector(
        'input[name="payment"]:checked',
      );
      const paymentMethod = paymentMethodEl ? paymentMethodEl.value : "cod";

      if (!name || !phone || !address) {
        alert(
          "Vui lòng điền đầy đủ Họ tên, Số điện thoại và Địa chỉ giao hàng nhé!",
        );
        return;
      }

      const note = document.getElementById("cus-note")?.value.trim() || "";
      if (note && note.length > 100) {
        alert("Vui lòng điền mô tả không quá 100 ký tự !");
        document.getElementById("cus-note").focus();
        return;
      }

      const orderItems = order.map((item) => ({
        sku: item.sku,
        quantity: item.quantity,
      }));

      const orderData = {
        receiver_name: name,
        receiver_phone: phone,
        receiver_email: email,
        shipping_address: address,
        payment_method: paymentMethod,
        reminder: note,
        items: orderItems,
        shipping_fee: phiVanChuyenValue,
        voucher_code: typeof voucherUI !== 'undefined' && voucherUI.selectedVoucher ? voucherUI.selectedVoucher.code : "",
      };

      // 2. hiệu ứng đang xử lý cho nút bấm
      const originalBtnText = btnSubmit.innerHTML;
      btnSubmit.innerHTML =
        '<span class="material-symbols-outlined animate-spin align-middle mr-2">refresh</span> ĐANG XỬ LÝ...';
      btnSubmit.disabled = true;
      btnSubmit.classList.add("opacity-75", "cursor-not-allowed");

      // 3. mở popup & vẽ skeleton (chỉ khi chọn chuyển khoản)
      let payOsWindow = null;
      if (paymentMethod === "bank") {
        const popupWidth = 600;
        const popupHeight = 750;
        const left = (window.innerWidth - popupWidth) / 2;
        const top = (window.innerHeight - popupHeight) / 2;

        payOsWindow = window.open(
          "",
          "ThanhToanPayOS",
          `width=${popupWidth},height=${popupHeight},top=${top},left=${left},toolbar=no,menubar=no,scrollbars=yes,resizable=yes`,
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
      }

      // 4. bắn api
      try {
        const response = await fetch(
          "https:// polygearid.ivi.vn/back-end/api/checkout/order",
          {
            credentials: 'include',
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(orderData),
          },
        );

        const result = await response.json();

        // 5. xử lý phản hồi (success hoặc lỗi)
        if (result.status === "success") {
          localStorage.removeItem("cartData");
          if (result.pay_url) {
            btnSubmit.innerHTML =
              '<span class="material-symbols-outlined align-middle mr-2">qr_code_scanner</span> ĐANG CHỜ QUÉT MÃ...';

            if (payOsWindow) {
              payOsWindow.location.href = result.pay_url;

              // thằng cha nhàn rỗi ngồi uống trà canh tab con đóng
              const checkWindowClosed = setInterval(() => {
                if (payOsWindow.closed) {
                  clearInterval(checkWindowClosed);
                  // tab con đã đóng (có thể do khách bấm x hoặc do payos_return.php tự sát)
                  // giờ chỉ việc bế trang cha qua success
                  window.location.href = `/success?code=${result.order_code}`;
                }
              }, 500); // check lẹ mỗi nửa giây
            } else {
              window.location.href = result.pay_url; // fallback
            }
          } else {
            // thanh toán cod
            if (payOsWindow) payOsWindow.close();
            window.location.href = `/success?code=${result.order_code}`;
          }
        } else {
          // trả về báo lỗi từ db (vd: hết hàng)
          if (payOsWindow) payOsWindow.close();
          
          if (result.message && result.message.includes("không đủ số lượng")) {
             const modalHTML = `
                <div id="outOfStockModal" class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/60 backdrop-blur-sm animate-fade-in">
                   <div class="bg-white rounded-2xl p-6 max-w-md w-full shadow-2xl transform transition-all scale-100">
                      <div class="text-center">
                         <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 mb-4">
                            <span class="material-symbols-outlined text-red-600 text-3xl">inventory_2</span>
                         </div>
                         <h3 class="text-xl font-bold text-slate-800 mb-3">Rất tiếc!</h3>
                         <div class="mt-2 text-sm text-slate-600 text-left bg-red-50/50 border border-red-100 rounded-xl p-4 whitespace-pre-line leading-relaxed max-h-60 overflow-y-auto font-medium">
                            ${result.message}
                         </div>
                      </div>
                      <div class="mt-6 flex gap-3">
                         <button type="button" class="w-full justify-center rounded-xl border border-slate-200 shadow-sm px-4 py-3 bg-white text-sm font-bold text-slate-700 hover:bg-slate-50 transition-colors" onclick="document.getElementById('outOfStockModal').remove();">
                            Đóng
                         </button>
                         <button type="button" class="w-full justify-center rounded-xl border border-transparent shadow-md shadow-blue-500/20 px-4 py-3 bg-blue-600 text-sm font-bold text-white hover:bg-blue-700 transition-colors" onclick="document.getElementById('outOfStockModal').remove(); window.location.href='/cart';">
                            Về Giỏ Hàng
                         </button>
                      </div>
                   </div>
                </div>
             `;
             document.body.insertAdjacentHTML('beforeend', modalHTML);
          } else {
              alert(
                "Có lỗi xảy ra: " +
                (result.message || "Không thể đặt hàng lúc này.")
              );
          }
          resetButton(originalBtnText);
        }
      } catch (error) {
        if (payOsWindow) payOsWindow.close();
        console.error("Lỗi kết nối:", error);
        alert("Hệ thống đang bận hoặc rớt mạng. Vui lòng thử lại!");
        resetButton(originalBtnText);
      }
    });

    function resetButton(originalText) {
      btnSubmit.innerHTML = originalText;
      btnSubmit.disabled = false;
      btnSubmit.classList.remove("opacity-75", "cursor-not-allowed");
    }
  }

  // 
  // phần 3: các tiện ích ui (đổi màu nút radio & lấy vị trí)
  // 

  // đổi màu nút chọn phương thức thanh toán
  const paymentRadios = document.querySelectorAll('input[name="payment"]');

  function updatePaymentStyles() {
    paymentRadios.forEach((radio) => {
      const label = radio.closest(".payment-option");
      if (!label) return;
      if (radio.checked) {
        label.classList.add("selected");
      } else {
        label.classList.remove("selected");
      }
    });
  }

  paymentRadios.forEach((radio) =>
    radio.addEventListener("change", updatePaymentStyles),
  );
  updatePaymentStyles(); // chạy lần đầu

  // api lấy vị trí địa lý
  const btnLocation = document.getElementById("btn-location");
  const addressInput = document.getElementById("cus-address");
  const locationMsg = document.getElementById("location-msg");

  if (btnLocation && addressInput) {
    btnLocation.addEventListener("click", () => {
      const originalText = btnLocation.innerHTML;
      btnLocation.innerHTML = `<span class="animate-pulse">Đang tìm...</span>`;
      locationMsg.classList.add("hidden");

      if (!navigator.geolocation) {
        showError("Trình duyệt của bạn không hỗ trợ tính năng này.");
        btnLocation.innerHTML = originalText;
        return;
      }

      navigator.geolocation.getCurrentPosition(
        async (position) => {
          const lat = position.coords.latitude;
          const lng = position.coords.longitude;
          try {
            const response = await fetch(
              `https:// nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&accept-language=vi`,
              { credentials: 'include' }
            );
            const data = await response.json();

            if (data && data.display_name) {
              addressInput.value = data.display_name;
              addressInput.classList.add("bg-green-50", "border-green-400");
              setTimeout(
                () =>
                  addressInput.classList.remove(
                    "bg-green-50",
                    "border-green-400",
                  ),
                2000,
              );
            } else {
              showError("Không thể xác định được địa chỉ cụ thể.");
            }
          } catch (error) {
            console.error(error);
            showError("Lỗi kết nối khi dịch địa chỉ.");
          } finally {
            btnLocation.innerHTML = originalText;
          }
        },
        (error) => {
          btnLocation.innerHTML = originalText;
          switch (error.code) {
            case error.PERMISSION_DENIED:
              showError("Bạn đã từ chối cấp quyền sử dụng vị trí.");
              break;
            case error.POSITION_UNAVAILABLE:
              showError("Không thể xác định được vị trí của bạn lúc này.");
              break;
            case error.TIMEOUT:
              showError("Yêu cầu định vị quá thời gian, vui lòng thử lại.");
              break;
          }
        },
        { enableHighAccuracy: true, timeout: 10000 },
      );
    });
  }

  function showError(msg) {
    locationMsg.textContent = msg;
    locationMsg.classList.remove("hidden");
  }
});
