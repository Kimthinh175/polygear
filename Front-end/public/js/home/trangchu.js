document.addEventListener("DOMContentLoaded", () => {
  const cartQuantity = document.getElementById("cart-quantity");
  // sử dụng event delegation để hỗ trợ các sản phẩm được lazy load
  document.addEventListener("click", async (e) => {
    const btn = e.target.closest(".add-to-cart");
    if (!btn) return;

    e.preventDefault();
    e.stopPropagation();
    
    let sku = btn.dataset.sku;
    let phone = btn.dataset.phone;
    let name = btn.dataset.name;
    let price = parseInt(btn.dataset.price) || 0;
    let originPrice = parseInt(btn.dataset.origin) || price;
    let img = btn.dataset.img;

    try {
      // kiểm tra login trước
      const authRes = await fetch("https:// polygearid.ivi.vn/back-end/api/auth/islogin", { credentials: 'include' });
      const authData = await authRes.json();

      if (authData.status === "success") {
        // đã đăng nhập
        const api = await fetch(
          "https:// polygearid.ivi.vn/back-end/api/cart/addtocart",
          {
            credentials: 'include',
            method: "POST",
            body: JSON.stringify({ sku: sku }),
          },
        );
        const res = await api.json();
        if (res.status == "success") {
          let isNewItem = res.is_new === true;
          checkCartChange(isNewItem);

          if (cartQuantity && cartQuantity.style.display === "none") {
            cartQuantity.style.display = "flex";
          }
          if (cartQuantity) cartQuantity.innerText = res.quantity;
          alert("Thêm vào giỏ hàng thành công");
        } else {
          alert("Thêm vào giỏ hàng thất bại: " + res.message);
        }
      } else {
        // chưa đăng nhập (guest)
        let localCart = JSON.parse(localStorage.getItem("cartData")) || { items: {} };
        let isNewItem = !localCart.items[sku];

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
            selected: true,
          };
        }
        localStorage.setItem("cartData", JSON.stringify(localCart));
        checkCartChange(isNewItem);

        let totalQty = 0;
        Object.values(localCart.items).forEach(item => totalQty += item.quantity);

        if (cartQuantity) {
          cartQuantity.innerText = totalQty;
          cartQuantity.style.display = "flex";
        }
        alert("Đã thêm vào giỏ hàng tạm thời!");
      }
    } catch (error) {
      console.error("Lỗi thêm vào giỏ:", error);
      alert("Có lỗi xảy ra, vui lòng thử lại sau!");
    }
  });

  const backToTopBtn = document.getElementById("backToTopBtn");
  if (!backToTopBtn) alert();
  window.addEventListener("scroll", () => {
    if (window.scrollY > 300) {
      backToTopBtn.classList.add("show");
    } else {
      backToTopBtn.classList.remove("show");
    }
  });

  backToTopBtn.addEventListener("click", () => {
    window.scrollTo({
      top: 0,
      behavior: "smooth",
    });
  });
});
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
