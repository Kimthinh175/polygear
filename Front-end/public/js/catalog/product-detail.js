document.addEventListener("DOMContentLoaded", () => {
  const btnSpecs = document.getElementById("btn-specs");
  const btnFeatures = document.getElementById("btn-features");
  const tabSpecs = document.getElementById("tab-specs");
  const tabFeatures = document.getElementById("tab-features");
  const btnAddToCart = document.getElementById("add-to-cart");
  const btnBuyNow = document.getElementById("buy-now");
  const cartQuantity = document.getElementById("cart-quantity");

  // 
  // 🕒 hàm mới: check và update thời gian thay đổi giỏ hàng
  // 
  function checkCartChange(isNewItem) {
    if (isNewItem) {
      // chỉ lưu thời gian mới khi đây là sản phẩm lạ chưa từng có trong giỏ
      localStorage.setItem("cartChangeTime", Date.now());
      console.log("⚡ Sản phẩm mới! Đã update cartChangeTime.");
    } else {
      console.log(
        "💤 Sản phẩm đã có sẵn (chỉ tăng số lượng). Không update cartChangeTime.",
      );
    }
  }

  if (btnSpecs && btnFeatures && tabSpecs && tabFeatures) {
    btnSpecs.addEventListener("click", () => {
      btnSpecs.classList.add("active");
      btnFeatures.classList.remove("active");
      tabSpecs.style.display = "block";
      tabFeatures.style.display = "none";
    });

    btnFeatures.addEventListener("click", () => {
      btnFeatures.classList.add("active");
      btnSpecs.classList.remove("active");
      tabFeatures.style.display = "block";
      tabSpecs.style.display = "none";
    });
  }

  if (btnAddToCart) {
    const handleCartAction = async (isBuyNow) => {
      let sku = btnAddToCart.dataset.sku;
      let phone = btnAddToCart.dataset.phone;
      let name = btnAddToCart.dataset.name || "Sản phẩm PolyGear";
      let price = parseInt(btnAddToCart.dataset.price) || 0;
      let originPrice = parseInt(btnAddToCart.dataset.origin) || price;
      let img = btnAddToCart.dataset.img || "";

      // 1. luôn luôn cập nhật localstorage trước (dù đăng nhập hay chưa)
      let localCart = JSON.parse(localStorage.getItem("cartData")) || {
        items: {},
        totalPrice: 0,
        totalSelectedCount: 0,
        totalDiscount: 0,
      };

      let isNewItemForLocal = false;
      if (localCart.items[sku]) {
        localCart.items[sku].quantity += 1;
      } else {
        localCart.items[sku] = {
          sku: sku,
          name: name,
          price: price,
          originalPrice: originPrice,
          main_img_url: img,
          quantity: 1,
          selected: false,
        };
        isNewItemForLocal = true;
      }

      // xử lý mua ngày -> chỉ chọn duy nhất món này
      if (isBuyNow) {
        for (let key in localCart.items) {
          localCart.items[key].selected = false;
        }
        localCart.items[sku].selected = true;
      }

      // tính tổng
      let calcTotalPrice = 0;
      let calcTotalDiscount = 0;
      let calcTotalSelected = 0;
      let totalQty = Object.keys(localCart.items).length;
      for (let key in localCart.items) {
        let item = localCart.items[key];
        calcTotalPrice += item.price * item.quantity;

        if (item.originalPrice > item.price) {
          calcTotalDiscount += (item.originalPrice - item.price) * item.quantity;
        }
        if (item.selected) {
          calcTotalSelected += item.quantity;
        }
      }

      localCart.totalPrice = calcTotalPrice;
      localCart.totalDiscount = calcTotalDiscount;
      localCart.totalSelectedCount = calcTotalSelected;

      localStorage.setItem("cartData", JSON.stringify(localCart));
      checkCartChange(isNewItemForLocal);

      try {
        const checkLogin = await fetch(
          "https:// polygearid.ivi.vn/back-end/api/auth/islogin",
          { credentials: "include" }
        );
        const authRes = await checkLogin.json();

        const isLoggedIn = authRes.status === "success";

        if (isLoggedIn) {
          let userPhone = authRes.info.phone; // lấy trực tiếp từ phiên đăng nhập
          // 
          // 🟢 đã đăng nhập -> gọi thêm api để lưu vào db
          // 
          const api = await fetch(
            "https:// polygearid.ivi.vn/back-end/api/cart/addtocart",
            {
              method: "POST",
              headers: { "Content-Type": "application/json" },
              credentials: "include",
              body: JSON.stringify({ sku: sku }),
            },
          );
          const res = await api.json();

          if (res.status == "success") {
            if (cartQuantity.style.display === "none") {
              cartQuantity.style.display = "flex";
            }
            cartQuantity.innerText = res.quantity;

            if (isBuyNow) {
              console.log(res);
              window.location.href = '/cart';
            } else {
              alert("Thêm vào giỏ hàng thành công!");
            }
          } else {
            alert("Thêm vào giỏ hàng thất bại, vui lòng thử lại sau!");
            window.location.reload();
          }
        } else {
          // 
          // 🟡 chưa đăng nhập -> dùng số lượng local
          // 
          if (cartQuantity.style.display === "none") {
            cartQuantity.style.display = "flex";
          }
          cartQuantity.innerText = totalQty;

          if (isBuyNow) {
            window.location.href = '/cart';
          } else {
            alert("Đã thêm vào giỏ hàng tạm thời (Chưa đăng nhập)!");
          }
        }
      } catch (error) {
        console.error("Lỗi:", error);
        alert("Hệ thống đang bận, vui lòng thử lại sau!");
      }
    };

    btnAddToCart.addEventListener("click", () => handleCartAction(false));
    if (btnBuyNow) {
      btnBuyNow.addEventListener("click", () => handleCartAction(true));
    }
  }

  // 
  // ✍️ hàm mới: xử lý đánh giá & nhận xét
  // 
  const reviewModal = document.getElementById("review-modal");
  const openReviewBtn = document.getElementById("open-review-btn");
  const closeReviewModalBtn = document.getElementById("close-review-modal");
  const starContainer = document.getElementById("star-rating-container");
  const submitReviewBtn = document.getElementById("submit-review-btn");
  const reviewContent = document.getElementById("review-content");
  const reviewsListContainer = document.getElementById("reviews-list-container");

  let currentSku = btnAddToCart ? btnAddToCart.dataset.sku : window.location.pathname.split('/').pop();
  let selectedRating = 5;

  // 1. kéo dữ liệu review ngay khi load trang
  async function fetchReviews() {
    if (!reviewsListContainer) return;
    try {
      const res = await fetch(`https:// polygearid.ivi.vn/back-end/api/reviews?sku=${currentsku}`);
      const data = await res.json();

      if (data.status === 'success' && data.data.length > 0) {
        reviewsListContainer.innerHTML = '';
        data.data.forEach(rv => {
          let starsHtml = '';
          for (let i = 1; i <= 5; i++) {
            const fillVal = i <= rv.rating ? 1 : 0;
            const colorClass = i <= rv.rating ? 'text-amber-400' : 'text-slate-300';
            starsHtml += `<span class="material-symbols-outlined !text-xs ${colorClass}" style="font-variation-settings: 'FILL' ${fillVal}">star</span>`;
          }

          let variantHtml = rv.variant_snapshot
            ? `<div class="text-[11px] text-slate-500 border-l border-slate-300 pl-2 ml-2 self-center">Phiên bản: <span class="font-semibold text-slate-700 capitalize">${rv.variant_snapshot}</span></div>`
            : '';

          let verifiedHtml = `<div class="flex items-center mt-1">${variantHtml}</div>`;

          let dateStr = new Date(rv.created_at).toLocaleDateString('vi-VN', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });

          reviewsListContainer.innerHTML += `
            <div class="flex flex-col gap-4 border-b border-slate-100 pb-6 last:border-0 last:pb-0">
                <div class="flex gap-4">
                <div class="w-12 h-12 rounded-full bg-slate-100 flex-shrink-0 flex items-center justify-center overflow-hidden">
                    ${rv.avatar ? `<img src="${rv.avatar}" class="w-full h-full object-cover">` : `<span class="material-symbols-outlined text-slate-400">person</span>`}
                </div>
                <div class="flex-1">
                    <div class="flex items-center gap-3 mb-1">
                        <span class="font-bold text-slate-900">${rv.fullname || 'Ẩn danh'}</span>
                        <div class="flex">${starsHtml}</div>
                        <span class="text-xs text-slate-400 font-medium">${dateStr}</span>
                    </div>
                    ${verifiedHtml}
                    <p class="text-slate-700 text-sm leading-relaxed mt-2 whitespace-pre-wrap">${rv.content}</p>
                </div>
                </div>
            </div>`;
        });
      }
    } catch (err) {
      console.error("Lỗi lấy danh sách đánh giá:", err);
    }
  }

  fetchReviews();
  // 
  // 🕒 hàm mới: theo dõi sản phẩm đã xem (recently viewed)
  // 
  function trackRecentlyViewed() {
    if (!btnAddToCart) return;

    const sku = btnAddToCart.dataset.sku;
    const name = btnAddToCart.dataset.name;
    const price = btnAddToCart.dataset.price;
    const origin = btnAddToCart.dataset.origin;
    const img = btnAddToCart.dataset.img;

    let recentlyViewed = JSON.parse(localStorage.getItem("recentlyViewed")) || [];

    // xóa nếu đã tồn tại để đưa lên đầu
    recentlyViewed = recentlyViewed.filter((item) => item.sku !== sku);

    // thêm vào đầu mảng
    recentlyViewed.unshift({
      sku,
      name,
      price: parseInt(price),
      origin: parseInt(origin),
      img,
      viewedAt: Date.now(),
    });

    // giới hạn 12 sản phẩm
    if (recentlyViewed.length > 12) {
      recentlyViewed = recentlyViewed.slice(0, 12);
    }

    localStorage.setItem("recentlyViewed", JSON.stringify(recentlyViewed));
  }

  trackRecentlyViewed();
});
