// 
// hàm global: kiểm tra sự thay đổi của giỏ hàng
// 
function isCartChange() {
  const cartChangeTime = localStorage.getItem("cartChangeTime");
  const resAiRaw = localStorage.getItem("ResAi"); // đã đồng bộ tên key

  if (!resAiRaw) {
    console.log("🤖 Chưa có Cache AI. Tiến hành gọi AI...");
    return true;
  }
  if (!cartChangeTime) return false;

  try {
    const resAi = JSON.parse(resAiRaw);
    if (resAi.time && resAi.time.toString() === cartChangeTime.toString()) {
      console.log("⚡ Giỏ hàng không đổi, sử dụng lại dữ liệu AI cũ.");
      return false;
    } else {
      console.log("🔄 Giỏ hàng đã thay đổi, cần gọi lại AI.");
      return true;
    }
  } catch (error) {
    console.warn("⚠️ Data ResAi bị lỗi, dọn dẹp và gọi lại AI.");
    localStorage.removeItem("ResAi");
    return true;
  }
}

// 
// chương trình chính
// 
document.addEventListener("DOMContentLoaded", () => {
  if (typeof voucherUI !== 'undefined') {
    voucherUI.setCallback(() => updateCartStateAndStorage());
  }
  setupScrollAndCheckout();

  // kiểm tra đăng nhập và khởi tạo giỏ hàng
  checkLoginAndInit();

  // gọi ai tư vấn (đã có bọc cache)
  handleAIConsultation();

  // load sản phẩm liên quan (home design)
  setupRelatedProducts();

  async function checkLoginAndInit() {
    try {
      const response = await fetch("https:// polygearid.ivi.vn/back-end/api/auth/islogin", {
        credentials: 'include'
      });
      const res = await response.json();

      if (res.status === "success") {
        console.log("✅ Đã đăng nhập. Sử dụng giỏ hàng từ server.");
        setupCartItemEvents();
        setupSelectAllEvent();
        // tính toán tiền lần đầu tiên khi load trang (đối với user đã login)
        updateCartStateAndStorage();
      } else {
        console.log("👤 Chế độ khách. Hiển thị giỏ hàng từ localStorage.");
        await renderLocalCart();
        // setupcartitemevents và setupselectallevent đã được gọi bên trong/sau renderlocalcart nếu cần,
        // nhưng gọi ở đây là chắc chắn nhất sau khi dom đã có data.
        setupCartItemEvents();
        setupSelectAllEvent();
        window.dispatchEvent(new Event("scroll"));
      }
    } catch (error) {
      console.error("Lỗi kiểm tra đăng nhập:", error);
      // fallback về render local nếu lỗi api
      await renderLocalCart();
      setupCartItemEvents();
      setupSelectAllEvent();
    }
  }

  async function renderLocalCart() {
    const listContainer = document.getElementById("list-product-cart");
    if (!listContainer) return;

    const cartDataRaw = localStorage.getItem("cartData");
    let cartData = { items: {} };

    try {
      if (cartDataRaw) {
        cartData = JSON.parse(cartDataRaw);
      }
    } catch (e) {
      console.error("Lỗi parse cartData:", e);
    }

    const localItems = Object.values(cartData.items || {});

    if (localItems.length === 0) {
      listContainer.innerHTML = `
        <div class="bg-white p-12 rounded-xl shadow-sm border border-slate-200 text-center">
          <span class="material-symbols-outlined text-6xl text-slate-200 mb-4">shopping_cart</span>
          <p class="text-slate-500 font-medium text-lg">Giỏ hàng của bạn đang trống</p>
          <a href="/home" class="inline-block mt-4 px-6 py-2 bg-primary text-black font-bold rounded-lg hover:bg-primary-dark transition-all">
            Tiếp tục mua sắm
          </a>
        </div>
      `;
      const cartQuantityBadge = document.getElementById("cart-quantity");
      if (cartQuantityBadge) cartQuantityBadge.style.display = "none";
      return;
    }

    // fetch fresh data from server
    try {
      const skuList = localItems.map(item => item.sku);
      const res = await fetch("https:// polygearid.ivi.vn/back-end/api/cart/guest-list", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ skus: skuList })
      });
      const data = await res.json();

      if (data.status === "success") {
        const freshItemsMap = {};
        data.cart.forEach(item => {
          freshItemsMap[item.sku] = item;
        });

        // merge dữ liệu mới vào cartdata.items
        const newItems = {};
        let dataChanged = false;

        localItems.forEach(localItem => {
          const freshItem = freshItemsMap[localItem.sku];
          if (freshItem) {
            // cập nhật thông tin từ server, giữ lại quantity và selected từ local
            newItems[localItem.sku] = {
              ...localItem,
              name: freshItem.variant_name,
              price: freshItem.sale_price || freshItem.price,
              originalPrice: freshItem.price,
              main_img_url: freshItem.main_image_url,
              status: freshItem.status,
              stock: freshItem.stock
            };
          } else {
            // sản phẩm không còn tồn tại trong db -> đánh dấu đã thay đổi để xóa khỏi localcart
            dataChanged = true;
          }
        });

        cartData.items = newItems;
        if (dataChanged) {
          localStorage.setItem("cartData", JSON.stringify(cartData));
        }
      }
    } catch (err) {
      console.error("Lỗi khi đồng bộ giỏ hàng khách:", err);
      // nếu lỗi fetch, vẫn tiếp tục render với dữ liệu local cũ
    }

    const items = Object.values(cartData.items);
    let html = '';
    let totalQty = items.length;
    items.forEach(product => {
      const priceFormatted = new Intl.NumberFormat("vi-VN").format(product.originalPrice || product.price) + "đ";
      const salePriceFormatted = new Intl.NumberFormat("vi-VN").format(product.price) + "đ";
      const isSale = (product.originalPrice && product.originalPrice > product.price);

      // đảm bảo ảnh luôn có dấu / ở đầu nếu là đường dẫn tương đối từ gốc
      let imgPath = product.main_img_url || '';
      if (imgPath && !imgPath.startsWith('http') && !imgPath.startsWith('/')) {
        imgPath = '/' + imgPath;
      }

      html += `
        <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-200 flex flex-col sm:flex-row gap-4 items-center">
          <div class="flex items-center pr-2">
            <input
              class="item-checkbox rounded text-primary focus:ring-primary border-2 border-primary bg-gray-300 size-5 cursor-pointer transition-all"
              type="checkbox" data-id="${product.sku}" data-price="${product.originalPrice || product.price}"
              data-sale-price="${product.price}" ${product.selected ? 'checked' : ''} />
          </div>
          <div class="size-24 bg-slate-100 rounded-lg flex-shrink-0">
            <img alt="${product.name}" class="w-full h-full object-contain p-2" src="${imgPath}" />
          </div>
          <div class="flex-1 min-w-0">
            <a href="/detail/${product.sku}">
              <h3 class="font-bold text-lg text-slate-900 truncate">${product.name}</h3>
            </a>
            <div class="flex items-center gap-4 mt-2">
              <span class="font-bold text-primary">${salePriceFormatted}</span>
              ${isSale ? `<span class="text-slate-400 line-through text-xs">${priceFormatted}</span>` : ''}
            </div>
            ${product.stock <= 0 ? '<p class="text-red-500 text-xs font-bold mt-1">Hết hàng</p>' : ''}
          </div>
          <div class="flex items-center gap-6 w-full sm:w-auto justify-between">
            <div class="flex items-center bg-slate-100 rounded-lg p-1">
              <button data-sku="${product.sku}" class="btn-minus size-8 flex items-center justify-center hover:bg-slate-200 rounded-md transition-all">
                <span class="material-symbols-outlined text-sm">remove</span>
              </button>
              <span class="quantity-val w-10 text-center font-bold">${product.quantity}</span>
              <button data-sku="${product.sku}" class="btn-plus size-8 flex items-center justify-center hover:bg-slate-200 rounded-md transition-all text-primary">
                <span class="material-symbols-outlined text-sm font-bold">add</span>
              </button>
            </div>
            <button class="remove" data-sku="${product.sku}">
              <span class="material-symbols-outlined text-slate-400 hover:text-red-500 transition-colors">delete</span>
            </button>
          </div>
        </div>
      `;
    });
    listContainer.innerHTML = html;

    // cập nhật số lượng giỏ hàng trên header
    const cartQuantity = document.getElementById("cart-quantity");
    if (cartQuantity) {
      cartQuantity.innerText = totalQty;
      cartQuantity.style.display = totalQty > 0 ? "flex" : "none";
    }

    // cập nhật ui tổng quát (tổng tiền, v.v.)
    updateCartStateAndStorage();
  }

  // 
  // module 1: ui toggles & utils
  // 
  function formatCurrency(amount) {
    return new Intl.NumberFormat("vi-VN").format(amount) + "đ";
  }



  // 
  // module 2: xử lý state và tính tiền giỏ hàng
  // 
  function updateCartStateAndStorage() {
    const selectedCountText = document.getElementById("selectedCountText");
    const totalCountText = document.getElementById("totalCountText");
    const totalPriceText = document.getElementById("totalPriceText");
    const selectAllCheckbox = document.getElementById("selectAllCheckbox");

    let checkedCount = 0;
    let totalOriginalPrice = 0;
    let totalDiscount = 0;
    let finalPrice = 0;

    const cartState = {
      items: {},
      totalPrice: 0,
      totalSelectedCount: 0,
      totalDiscount: 0,
    };

    const currentCheckboxes = document.querySelectorAll(".item-checkbox");

    currentCheckboxes.forEach((cb) => {
      const container = cb.closest(".bg-white");
      const id = cb.getAttribute("data-id");
      const name = container.querySelector("h3").textContent.trim();
      const sku = cb.getAttribute("data-id");
      const imgUrl = container.querySelector("img").src;
      const quantitySpan = container.querySelector(".quantity-val");
      const quantity = parseInt(quantitySpan.textContent) || 1;
      const isSelected = cb.checked;

      const originalPrice = parseInt(cb.getAttribute("data-price")) || 0;
      const salePriceAttr = cb.getAttribute("data-sale-price");
      const salePrice =
        salePriceAttr && salePriceAttr !== "null" && salePriceAttr !== ""
          ? parseInt(salePriceAttr)
          : originalPrice;

      if (isSelected) {
        checkedCount++;
        totalOriginalPrice += originalPrice * Math.max(1, quantity);
        finalPrice += salePrice * Math.max(1, quantity);
      }

      cartState.items[id] = {
        sku: sku,
        name: name,
        price: salePrice,
        originalPrice: originalPrice,
        quantity: quantity,
        main_img_url: imgUrl,
        selected: isSelected,
      };
    });

    totalDiscount = totalOriginalPrice - finalPrice;

    // add voucher logic
    if (typeof voucherUI !== 'undefined') {
      voucherUI.setCartTotal(finalPrice);
      const vDiscount = voucherUI.getDiscountAmount();

      const vDisplayCode = document.getElementById('cartVoucherCodeDisplay');
      const vActionText = document.getElementById('cartVoucherActionText');
      if (vDiscount > 0) {
        finalPrice -= vDiscount;

        if (vDisplayCode && vActionText) {
          vDisplayCode.querySelector('span').textContent = 'Đã áp dụng mã: ' + voucherUI.selectedVoucher.code;
          vDisplayCode.classList.remove('hidden');
          vActionText.classList.add('hidden');
        }
      } else {
        if (vDisplayCode && vActionText) {
          vDisplayCode.classList.add('hidden');
          vActionText.classList.remove('hidden');
        }
      }
    }
    // 

    cartState.totalPrice = finalPrice;
    cartState.totalDiscount = totalDiscount;
    cartState.totalSelectedCount = checkedCount;

    // cập nhật ui
    if (selectedCountText) selectedCountText.textContent = checkedCount;
    if (totalCountText)
      totalCountText.textContent = `Tổng cộng (${checkedCount} sản phẩm):`;
    if (totalPriceText) totalPriceText.textContent = formatCurrency(finalPrice);

    // cập nhật text giảm giá (id & class)
    const totalDiscountText = document.getElementById("totalDiscountText");
    if (totalDiscountText) {
      if (totalDiscount > 0) {
        totalDiscountText.textContent = `-${formatCurrency(totalDiscount)}`;
        totalDiscountText.closest("div").classList.remove("hidden");
      } else {
        totalDiscountText.textContent = "0đ";
        totalDiscountText.closest("div").classList.add("hidden");
      }
    }

    const el = document.querySelector(".totalDiscount");
    if (el) {
      if (totalDiscount > 0) {
        el.textContent = `-${formatCurrency(totalDiscount)}`;
        if (el.parentElement) {
          el.parentElement.classList.remove("hidden");
          el.parentElement.classList.add("flex");
        }
      } else {
        el.textContent = "0đ";
        if (el.parentElement) {
          el.parentElement.classList.add("hidden");
          el.parentElement.classList.remove("flex");
        }
      }
    }

    if (selectAllCheckbox) {
      selectAllCheckbox.checked =
        checkedCount === currentCheckboxes.length &&
        currentCheckboxes.length > 0;
    }

    const cartSummary = document.getElementById("cart-summary");
    if (currentCheckboxes.length === 0) {
      if (cartSummary) cartSummary.style.display = "none";
    } else {
      if (cartSummary) cartSummary.style.display = "block";
    }

    localStorage.setItem("cartData", JSON.stringify(cartState));
  }

  // 
  // module 3: sự kiện của từng item trong giỏ
  // 
  function setupCartItemEvents() {
    const initialCheckboxes = document.querySelectorAll(".item-checkbox");
    const savedCart = JSON.parse(localStorage.getItem("cartData")) || {
      items: {},
    };

    initialCheckboxes.forEach((cb) => {
      const id = cb.getAttribute("data-id");
      const container = cb.closest(".bg-white");
      const quantitySpan = container.querySelector(".quantity-val");

      const buyNowTarget = sessionStorage.getItem("buyNowTarget");
      if (buyNowTarget) {
        cb.checked = (id === buyNowTarget);
      } else {
        // phục hồi tick
        if (savedCart && savedCart.items && savedCart.items[id]) {
          cb.checked = savedCart.items[id].selected;
        } else {
          const oldSelection =
            JSON.parse(localStorage.getItem("cartSelection")) || {};
          if (oldSelection[id] !== undefined) cb.checked = oldSelection[id];
        }
      }


      const btnRemove = container.querySelector(".remove");
      const cartQuantity = document.getElementById("cart-quantity");
      if (btnRemove) {
        btnRemove.addEventListener("click", async () => {
          if (confirm("Bạn có chắc chắn muốn bỏ sản phẩm này khỏi giỏ hàng?")) {
            let phone = btnRemove.dataset.phone;
            let sku = btnRemove.dataset.sku;

            // xử lý khách (guest)
            if (!phone || phone === "" || phone === "undefined") {
              let cartData = JSON.parse(localStorage.getItem("cartData")) || { items: {} };
              if (cartData.items && cartData.items[sku]) {
                delete cartData.items[sku];
                localStorage.setItem("cartData", JSON.stringify(cartData));
                localStorage.setItem("cartChangeTime", Date.now());
                localStorage.removeItem("ResAi");
              }
              container.remove();
              updateCartStateAndStorage();
              if (cartQuantity) {
                cartQuantity.innerText = Object.keys(cartData.items || {}).length;
              }
              handleAIConsultation();
              if (document.querySelectorAll(".item-checkbox").length === 0) window.location.reload();
              return;
            }

            // xử lý đã đăng nhập
            try {
              const db = await fetch(
                "https:// polygearid.ivi.vn/back-end/api/cart/remove",
                {
                  credentials: 'include',
                  method: "POST",
                  body: JSON.stringify({ sku: sku }),
                },
              );
              let res = await db.json();
              if (res.status == "success") {
                localStorage.setItem("cartChangeTime", Date.now());
                // xoá cache ai cũ để buộc gọi lại
                localStorage.removeItem("ResAi");

                container.remove();
                updateCartStateAndStorage();
                let quantity = Number(cartQuantity.innerText) - 1;
                cartQuantity.innerText = quantity;

                // hiện skeleton loading trước khi gọi ai
                const skeletonEl = document.getElementById("ai-consultation-skeleton");
                const contentEl = document.getElementById("ai-consultation-content");
                if (skeletonEl) skeletonEl.classList.remove("hidden");
                if (contentEl) contentEl.classList.add("hidden");

                handleAIConsultation();

                if (document.querySelectorAll(".item-checkbox").length === 0)
                  window.location.reload();
              } else {
                alert("Lỗi xóa sản phẩm: " + res.message);
              }
            } catch (error) {
              console.error("Lỗi API xóa:", error);
            }
          }
        });
      }

      // tăng giảm số lượng
      const btnMinus = container.querySelector(".btn-minus");
      const btnPlus = container.querySelector(".btn-plus");
      if (btnMinus && btnPlus && quantitySpan) {
        let isProcessing = false;

        btnMinus.addEventListener("click", async () => {
          if (isProcessing) return;
          let currentQ = parseInt(quantitySpan.textContent) || 1;
          if (currentQ > 1) {
            let phone = btnMinus.dataset.phone;
            let sku = btnMinus.dataset.sku;

            // xử lý khách (guest)
            if (!phone || phone === "" || phone === "undefined") {
              quantitySpan.textContent = currentQ - 1;
              updateCartStateAndStorage();
              return;
            }

            // xử lý đã đăng nhập
            isProcessing = true;
            btnMinus.classList.add("opacity-50", "cursor-wait");
            btnPlus.classList.add("opacity-50", "cursor-wait");
            try {
              const db = await fetch(
                "https:// polygearid.ivi.vn/back-end/api/cart/dec",
                {
                  credentials: 'include',
                  method: "POST",
                  body: JSON.stringify({
                    sku: sku,
                  }),
                },
              );
              let res = await db.json();
              if (res.status == "success") {
                quantitySpan.textContent = currentQ - 1;
                updateCartStateAndStorage();
              }
            } catch (error) {
              console.error("Lỗi giảm số lượng:", error);
            } finally {
              isProcessing = false;
              btnMinus.classList.remove("opacity-50", "cursor-wait");
              btnPlus.classList.remove("opacity-50", "cursor-wait");
            }
          }
        });

        btnPlus.addEventListener("click", async () => {
          if (isProcessing) return;
          let currentQ = parseInt(quantitySpan.textContent) || 1;
          if (currentQ < 99) {
            let phone = btnPlus.dataset.phone;
            let sku = btnPlus.dataset.sku;

            // xử lý khách (guest)
            if (!phone || phone === "" || phone === "undefined") {
              quantitySpan.textContent = currentQ + 1;
              updateCartStateAndStorage();
              return;
            }

            // xử lý đã đăng nhập
            isProcessing = true;
            btnMinus.classList.add("opacity-50", "cursor-wait");
            btnPlus.classList.add("opacity-50", "cursor-wait");
            try {
              const db = await fetch(
                "https:// polygearid.ivi.vn/back-end/api/cart/add",
                {
                  credentials: 'include',
                  method: "POST",
                  body: JSON.stringify({
                    sku: sku,
                  }),
                },
              );
              let res = await db.json();
              if (res.status == "success") {
                quantitySpan.textContent = currentQ + 1;
                updateCartStateAndStorage();
              }
            } catch (error) {
              console.error("Lỗi tăng số lượng:", error);
            } finally {
              isProcessing = false;
              btnMinus.classList.remove("opacity-50", "cursor-wait");
              btnPlus.classList.remove("opacity-50", "cursor-wait");
            }
          }
        });
      }

      // checkbox change
      cb.addEventListener("change", () => updateCartStateAndStorage());
    });

    const buyNowTarget = sessionStorage.getItem("buyNowTarget");
    if (buyNowTarget) {
      sessionStorage.removeItem("buyNowTarget");
      updateCartStateAndStorage();
    }
  }

  function setupSelectAllEvent() {
    const selectAllCheckbox = document.getElementById("selectAllCheckbox");
    if (selectAllCheckbox) {
      selectAllCheckbox.addEventListener("change", (e) => {
        const isChecked = e.target.checked;
        document.querySelectorAll(".item-checkbox").forEach((cb) => {
          cb.checked = isChecked;
        });
        updateCartStateAndStorage();
      });
    }
  }

  // 
  // module 4: ai consultation & suggestion
  // 
  let aiLoadingInterval = null;

  function startAILoadingAnimation() {
    const steps = [0, 1, 2, 3];
    let currentStep = 0;

    // reset all steps
    steps.forEach(i => {
      const stepEl = document.getElementById(`ai-step-${i}`);
      const iconEl = document.getElementById(`ai-step-icon-${i}`);
      const labelEl = document.getElementById(`ai-step-label-${i}`);
      const checkEl = document.getElementById(`ai-step-check-${i}`);
      const dotEl = document.getElementById(`ai-step-dot-${i}`);

      if (stepEl) {
        stepEl.style.opacity = '0.35';
        stepEl.style.background = '#f8fafc';
        stepEl.style.borderColor = '#e2e8f0';
      }
      if (iconEl) {
        iconEl.style.background = '#e2e8f0';
        iconEl.querySelector('.material-symbols-outlined').style.color = '#94a3b8';
      }
      if (labelEl) labelEl.style.color = '#94a3b8';
      if (checkEl) checkEl.style.display = 'none';
      if (dotEl) {
        dotEl.style.display = 'block';
        dotEl.style.background = '#94a3b8';
        dotEl.style.animation = 'none';
      }
    });

    const updateStep = () => {
      if (currentStep > 0) {
        // mark previous step as completed
        const prev = currentStep - 1;
        const prevStep = document.getElementById(`ai-step-${prev}`);
        const prevCheck = document.getElementById(`ai-step-check-${prev}`);
        const prevDot = document.getElementById(`ai-step-dot-${prev}`);

        if (prevStep) {
          prevStep.style.opacity = '1';
          prevStep.style.background = '#f0fdf4';
          prevStep.style.borderColor = '#bbf7d0';
        }
        if (prevCheck) prevCheck.style.display = 'block';
        if (prevDot) prevDot.style.display = 'none';
      }

      if (currentStep < steps.length) {
        // mark current step as active
        const curr = currentStep;
        const currStep = document.getElementById(`ai-step-${curr}`);
        const currIcon = document.getElementById(`ai-step-icon-${curr}`);
        const currLabel = document.getElementById(`ai-step-label-${curr}`);
        const currDot = document.getElementById(`ai-step-dot-${curr}`);

        if (currStep) {
          currStep.style.opacity = '1';
          currStep.style.background = '#eff6ff';
          currStep.style.borderColor = '#bfdbfe';
        }
        if (currIcon) {
          currIcon.style.background = '#3b82f6';
          currIcon.querySelector('.material-symbols-outlined').style.color = '#fff';
        }
        if (currLabel) currLabel.style.color = '#1e40af';
        if (currDot) {
          currDot.style.background = '#3b82f6';
          currDot.style.animation = 'iconPulse 1s ease-in-out infinite';
        }

        currentStep++;
      } else {
        // optional: reset or stay at last step
        // currentstep = 0; // loop
      }
    };

    updateStep(); // start immediately
    aiLoadingInterval = setInterval(updateStep, 3500); // change step every 1.5s
  }

  function stopAILoadingAnimation() {
    if (aiLoadingInterval) {
      clearInterval(aiLoadingInterval);
      aiLoadingInterval = null;
    }
  }
  function handleAIConsultation() {
    const btnExpand = document.getElementById("btn-expand-ai-build");
    const form = document.getElementById("ai-build-form");
    const radios = document.querySelectorAll('input[name="ai-task"]');
    const customInput = document.getElementById("ai-task-custom");
    const btnSubmit = document.getElementById("btn-submit-ai-build");
    const loading = document.getElementById("ai-build-loading");
    const resultBox = document.getElementById("ai-build-result");
    const btnBuy = document.getElementById("btn-buy-ai-build");

    // toggle other input
    if (radios) {
      radios.forEach(radio => {
        radio.addEventListener('change', (e) => {
          if (customInput) {
            customInput.style.display = (e.target.id === 'ai-task-other-radio') ? 'block' : 'none';
          }
        });
      });
    }

    // toggle form
    if (btnExpand && form) {
      btnExpand.addEventListener("click", () => {
        const isHidden = form.style.display === 'none' || form.style.display === '';
        if (isHidden) {
          form.style.display = 'flex';
          document.getElementById('ai-build-cta').style.display = 'none';
        } else {
          form.style.display = 'none';
          document.getElementById('ai-build-cta').style.display = 'block';
        }
      });
    }

    if (btnSubmit) {
      btnSubmit.addEventListener("click", async () => {
        const budgetEl = document.getElementById("ai-budget");
        const levelEl = document.getElementById("ai-level");

        let budget = budgetEl ? budgetEl.value.trim() : "";
        if (!budget) budget = "Không giới hạn";
        else budget = budget + " VNĐ";

        let level = levelEl ? levelEl.value : "Trung bình";

        let task = "Chơi game";
        const selectedRadio = document.querySelector('input[name="ai-task"]:checked');
        if (selectedRadio) {
          if (selectedRadio.id === 'ai-task-other-radio' && customInput) {
            task = customInput.value.trim() || "Khác";
          } else {
            task = selectedRadio.value;
          }
        }

        const currentCart = JSON.parse(localStorage.getItem("cartData")) || { items: {} };
        const cartdata = Object.values(currentCart.items).map((item) => {
          return {
            name: item.name, price: item.price, sku: item.sku, quantity: item.quantity
          };
        });

        const reqData = {
          budget: budget,
          level: level,
          task: task,
          cart: cartdata
        };

        // ui state
        form.style.display = 'none';
        document.getElementById('ai-build-cta').style.display = 'none';
        resultBox.style.display = 'none';
        loading.style.display = 'flex';
        startAILoadingAnimation();

        try {
          const res = await fetch("https:// polygearid.ivi.vn/back-end/api/ai/sendinfo", {
            credentials: 'include',
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(reqData),
          });

          if (res.ok) {
            const data = await res.json();
            renderAIBuildResult(data);
          } else {
            alert("Lỗi khi gọi AI. Vui lòng thử lại sau.");
          }
        } catch (error) {
          console.error(error);
          alert("Mất kết nối. Vui lòng thử lại.");
        } finally {
          loading.style.display = 'none';
          stopAILoadingAnimation();
        }
      });
    }

    // nút mua ngay cả cấu hình
    if (btnBuy) {
      btnBuy.addEventListener("click", async () => {
        btnBuy.innerHTML = `<span class="material-symbols-outlined animate-spin text-base">refresh</span> Đang xử lý...`;
        btnBuy.disabled = true;

        const configSkus = btnBuy.dataset.skus ? JSON.parse(btnBuy.dataset.skus) : [];
        if (configSkus.length === 0) {
          alert("Cấu hình trống!");
          return;
        }

        try {
          const authRes = await fetch("https:// polygearid.ivi.vn/back-end/api/auth/islogin", { credentials: "include" });
          const authData = await authRes.json();

          if (authData.status === "success") {
            // thêm vào giỏ hàng server nhưng không cần đợi hết mới chuyển trang để tránh lag
            // chạy song song các request add to cart
            const addPromises = configSkus.map(sku =>
              fetch("https:// polygearid.ivi.vn/back-end/api/cart/addtocart", {
                credentials: 'include', method: "POST", headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ sku: sku })
              })
            );
            await Promise.all(addPromises);
          } else {
            // nếu chưa login thì kích hoạt login modal
            const loginBtn = document.getElementById("btnOpenLogin");
            if (loginBtn) {
              loginBtn.click();
            } else {
              alert("Vui lòng đăng nhập để thanh toán!");
            }
            // reset button state
            btnBuy.innerHTML = `<span class="material-symbols-outlined" style="font-size:16px;font-variation-settings:'FILL' 1;">bolt</span> Mua ngay cấu hình này`;
            btnBuy.disabled = false;
            return; // dừng lại không checkout
          }
        } catch (e) { console.error(e); }

        let localCart = JSON.parse(localStorage.getItem("cartData")) || { items: {} };

        // 1. bỏ chọn tất cả các sản phẩm hiện có
        for (let key in localCart.items) {
          localCart.items[key].selected = false;
        }

        // 2. thêm và chọn các sản phẩm từ ai build
        for (let prod of window.currentAIProducts || []) {
          if (localCart.items[prod.sku]) {
            localCart.items[prod.sku].selected = true;
            // đảm bảo số lượng ít nhất là 1
            if (localCart.items[prod.sku].quantity < 1) localCart.items[prod.sku].quantity = 1;
          } else {
            localCart.items[prod.sku] = {
              sku: prod.sku,
              name: prod.name,
              price: prod.price,
              quantity: 1,
              main_img_url: prod.main_image_url,
              selected: true
            };
          }
        }

        // 3. cập nhật lại tổng số lượng đã chọn
        let selectedCount = 0;
        for (let key in localCart.items) {
          if (localCart.items[key].selected) selectedCount++;
        }
        localCart.totalSelectedCount = selectedCount;

        // 4. lưu và chuyển hướng
        localStorage.setItem("cartData", JSON.stringify(localCart));
        localStorage.setItem("cartChangeTime", Date.now());

        window.location.href = "/checkout";
      });
    }
  }

  function renderAIBuildResult(data) {
    if (!data) {
      alert("Dữ liệu trả về bị lỗi.");
      return;
    }

    const resultBox = document.getElementById("ai-build-result");
    const descEl = document.getElementById("ai-build-desc");
    const listEl = document.getElementById("ai-build-products");
    const totalEl = document.getElementById("ai-build-total");
    const btnBuy = document.getElementById("btn-buy-ai-build");

    descEl.innerHTML = data.description || "Đây là cấu hình tối ưu dành cho bạn.";

    let html = "";
    let skus = [];
    window.currentAIProducts = [];

    if (data.products && Array.isArray(data.products)) {
      window.currentAIProducts = data.products;
      data.products.forEach(p => {
        skus.push(p.sku);
        const priceFmt = p.price > 0 ? new Intl.NumberFormat("vi-VN").format(p.price) + "đ" : "Hết hàng";
        const imgHtml = p.main_image_url ? `<img src="${p.main_image_url}" class="w-full h-full object-contain p-2"/>` : `<span class="material-symbols-outlined text-slate-300 text-3xl">inventory_2</span>`;

        html += `
                <div class="flex-none w-32 bg-white rounded-xl border border-slate-200 overflow-hidden hover:border-primary hover:shadow-lg transition-all flex flex-col group relative">
                    <a href="/detail/${p.sku}" class="block aspect-square bg-slate-50 relative flex items-center justify-center p-2">
                        ${imgHtml}
                    </a>
                    <div class="p-2.5 flex flex-col flex-1 gap-1">
                        <a href="/detail/${p.sku}" class="block">
                            <p class="text-[10px] font-semibold text-slate-800 line-clamp-2 leading-tight group-hover:text-primary transition-colors" title="${p.name}">${p.name}</p>
                        </a>
                        <p class="text-[11px] font-black text-slate-900 mt-auto">${priceFmt}</p>
                    </div>
                </div>
              `;
      });
    }

    listEl.innerHTML = html;
    const totalFmt = data.total ? new Intl.NumberFormat("vi-VN").format(data.total) + "đ" : "0đ";
    totalEl.textContent = totalFmt;

    btnBuy.dataset.skus = JSON.stringify(skus);

    resultBox.style.display = 'flex';

    const btnPrev = document.getElementById("ai-build-prev");
    const btnNext = document.getElementById("ai-build-next");

    if (btnPrev && btnNext && listEl) {
      const updateArrows = () => {
        const { scrollLeft, scrollWidth, clientWidth } = listEl;
        btnPrev.style.display = scrollLeft <= 5 ? 'none' : 'flex';
        btnNext.style.display = scrollLeft + clientWidth >= scrollWidth - 5 ? 'none' : 'flex';
      };
      setTimeout(updateArrows, 200);
      btnPrev.onclick = () => listEl.scrollBy({ left: -150, behavior: "smooth" });
      btnNext.onclick = () => listEl.scrollBy({ left: 150, behavior: "smooth" });
      listEl.onscroll = updateArrows;
    }
  }

  // 
  // module 5: scroll animation & checkout
  // 
  function setupScrollAndCheckout() {
    const cartSummary = document.getElementById("cart-summary");
    const relatedProducts = document.getElementById("related-products");
    const btnBuy = document.getElementById("buy");

    if (cartSummary && relatedProducts) {
      window.addEventListener("scroll", () => {
        const relatedRect = relatedProducts.getBoundingClientRect();
        const viewportHeight = window.innerHeight;

        if (relatedRect.top < viewportHeight) {
          const scrollY =
            window.pageYOffset || document.documentElement.scrollTop;
          const totalDocumentTopToRelated = relatedRect.top + scrollY;
          const summaryHeight = cartSummary.offsetHeight;

          cartSummary.classList.remove("fixed", "bottom-0");
          cartSummary.style.position = "absolute";
          cartSummary.style.top = `${totalDocumentTopToRelated - summaryHeight}px`;
          cartSummary.style.bottom = "auto";
          cartSummary.style.transform = "translateY(0)";
        } else {
          cartSummary.style.position = "";
          cartSummary.style.top = "";
          cartSummary.style.bottom = "";
          cartSummary.classList.add("fixed", "bottom-0");
          cartSummary.style.transform = "translateY(0)";
        }
      });

      setTimeout(() => {
        window.dispatchEvent(new Event("scroll"));
      }, 100);
    }

    if (btnBuy) {
      btnBuy.addEventListener("click", async () => {
        const cartState = JSON.parse(localStorage.getItem("cartData")) || { totalSelectedCount: 0 };
        if (cartState.totalSelectedCount <= 0) {
          alert("Bạn chưa chọn sản phẩm nào để thanh toán. Vui lòng chọn ít nhất một sản phẩm.");
          return;
        }

        try {
          const authRes = await fetch("https:// polygearid.ivi.vn/back-end/api/auth/islogin", { credentials: "include" });
          const authData = await authRes.json();

          if (authData.status === "success") {
            window.location.href = "/checkout";
          } else {
            const loginBtn = document.getElementById("btnOpenLogin");
            if (loginBtn) {
              loginBtn.click();
            } else {
              alert("Vui lòng đăng nhập để thanh toán!");
            }
          }
        } catch (e) {
          console.error("Lỗi xác thực:", e);
        }
      });
    }
  }

  // 
  // module: sản phẩm liên quan (home design)
  // 
  async function setupRelatedProducts() {
    const listContainer = document.getElementById("related-products-list");
    if (!listContainer) return;

    // lấy danh sách sku từ cartdata trong localstorage
    const cartData = JSON.parse(localStorage.getItem("cartData")) || { items: {} };
    const skus = Object.keys(cartData.items);

    try {
      const products = await fetchRelatedProducts(skus);
      renderRelatedProducts(products, listContainer);
    } catch (error) {
      console.error("Lỗi khi tải sản phẩm liên quan:", error);
      listContainer.innerHTML = '<p class="col-span-full text-center text-slate-500 py-10">Không thể tải sản phẩm liên quan lúc này.</p>';
    }
  }

  async function fetchRelatedProducts(skus) {
    const res = await fetch("https:// polygearid.ivi.vn/back-end/api/products/related-cart", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ skus: skus })
    });
    const result = await res.json();
    return result.status === "success" ? result.data : [];
  }

  function renderRelatedProducts(products, container) {
    container.innerHTML = "";

    if (products.length === 0) {
      container.innerHTML = '<p class="col-span-full text-center text-slate-500 py-10">Không có sản phẩm liên quan phù hợp.</p>';
      return;
    }

    products.forEach(product => {
      const price = product.sale_price
        ? new Intl.NumberFormat("vi-VN").format(product.sale_price) + "₫"
        : new Intl.NumberFormat("vi-VN").format(product.price) + "₫";

      const originPrice = product.sale_price
        ? new Intl.NumberFormat("vi-VN").format(product.price) + "₫"
        : null;

      const discount = product.sale_price
        ? Math.round(((product.price - product.sale_price) / product.price) * 100)
        : null;

      const specs = product.specs ? product.specs.map(s => s.spec_value).join(", ") : "";

      const card = document.createElement("div");
      card.className = "h-full";
      card.innerHTML = `
        <a href="/detail/${product.sku}"
            class="group h-[408px] bg-white rounded-xl border border-slate-200 overflow-hidden hover:border-blue-500/40 hover:shadow-xl hover:shadow-slate-200/50 transition-all flex flex-col"
            data-purpose="product-card">

            <div class="aspect-[4/3] relative overflow-hidden bg-slate-50 flex items-center justify-center p-4">
                <img alt="${product.name}"
                    class="w-full h-full object-contain group-hover:scale-105 transition-transform duration-500"
                    src="${product.main_image_url}" />
                ${discount ? `
                  <div class="absolute top-0 right-0 z-10">
                      <div class="bg-red-500 text-white px-3 py-1.5 rounded-bl-xl shadow-sm flex flex-col items-center justify-center origin-top-right">
                          <span class="text-[8px] font-bold uppercase tracking-widest opacity-90 leading-none mb-0.5">Giảm</span>
                          <span class="text-sm font-black leading-none">${discount}%</span>
                      </div>
                  </div>
                ` : ""}
            </div>

            <div class="p-4 flex-1 flex flex-col">
                <div class="flex justify-between items-start mb-1.5">
                    <span class="text-[10px] font-bold text-blue-600 uppercase tracking-widest">${product.brand_name || ""}</span>
                </div>

                <h3 class="font-bold text-sm mb-1 text-slate-900 group-hover:text-blue-600 transition-colors line-clamp-2 min-h-[40px]">
                    ${product.name}
                </h3>
                <p class="min-h-[32px] text-xs text-slate-500 mb-4 line-clamp-2 leading-relaxed">
                    ${specs}
                </p>

                <div class="mt-4 flex items-center justify-between pt-3 border-t border-slate-100">
                    <div class="flex flex-col">
                        ${originPrice ? `<span class="text-[10px] text-gray-400 line-through">${originPrice}</span>` : ""}
                        <span class="text-base font-extrabold text-slate-900 leading-tight">${price}</span>
                        <span class="text-[10px] text-green-600 font-bold mt-0.5">Còn hàng</span>
                    </div>
                    <button data-sku="${product.sku}"
                        class="add-to-cart size-9 flex items-center justify-center bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-600 hover:text-white transition-all">
                        <span class="material-symbols-outlined z-[9]">shopping_cart</span>
                    </button>
                </div>
            </div>
        </a>
      `;
      container.appendChild(card);
    });

    // re-setup add-to-cart events for the new cards
    setupAddToCartEventsOnContainer(container);
  }

  function setupAddToCartEventsOnContainer(container) {
    const buttons = container.querySelectorAll(".add-to-cart");
    buttons.forEach(btn => {
      btn.addEventListener("click", async (e) => {
        e.preventDefault();
        e.stopPropagation();
        const sku = btn.dataset.sku;
        // gọi hàm thêm vào giỏ hàng có sẵn trong cart.js hoặc handle logic tại đây
        // vì trong cart.js thường có sự kiện delegate hoặc init, tôi sẽ giả định có hàm add
        addToCartManual(sku, btn);
      });
    });
  }

  async function addToCartManual(sku, btn) {
    btn.disabled = true;
    const originalContent = btn.innerHTML;
    btn.innerHTML = '<span class="material-symbols-outlined animate-spin">sync</span>';

    try {
      const authRes = await fetch("https:// polygearid.ivi.vn/back-end/api/auth/islogin", { credentials: 'include' });
      const authData = await authRes.json();

      if (authData.status === "success") {
        const phone = authData.info.phone;
        const addRes = await fetch("https:// polygearid.ivi.vn/back-end/api/cart/addtocart", {
          credentials: 'include',
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ sku: sku, phone: phone })
        });
        const addData = await addRes.json();
        if (addData.status === "success") {
          localStorage.setItem("cartChangeTime", Date.now());
          window.location.reload();
        } else {
          alert("Lỗi: " + addData.message);
        }
      } else {
        // logic cho khách vãng lai (guest)
        let localCart = JSON.parse(localStorage.getItem("cartData")) || { items: {} };
        const name = btn.dataset.name || "Sản phẩm PolyGear";
        const price = parseInt(btn.dataset.price) || 0;
        const img = btn.dataset.img || "";

        if (localCart.items[sku]) {
          localCart.items[sku].quantity += 1;
        } else {
          localCart.items[sku] = {
            sku: sku,
            name: name,
            price: price,
            originalPrice: price,
            main_img_url: img,
            quantity: 1,
            selected: true,
          };
        }
        localStorage.setItem("cartData", JSON.stringify(localCart));
        localStorage.setItem("cartChangeTime", Date.now());
        window.location.reload();
      }
    } catch (error) {
      console.error(error);
    } finally {
      btn.disabled = false;
      btn.innerHTML = originalContent;
    }
  }
});
