document.addEventListener("DOMContentLoaded", async () => {
  // sync cart badge on every load
  await syncHeaderCartBadge();

  // add interactivity to search button
  const searchInput = document.querySelector(".cps-search input");
  const searchBtn = document.querySelector(".cps-search button");

  if (searchBtn && searchInput) {
    searchBtn.addEventListener("click", () => {
      if (searchInput.value.trim() !== "") {
        window.location.href = "/products/" + searchInput.value.trim();
      }
    });
    searchInput.addEventListener("keydown", (e) => {
      if (e.key == "Enter") {
        window.location.href = "/products/" + searchInput.value.trim();
      }
    });
  }

  // header scroll effect
  const header = document.querySelector(".cps-header");
  if (header) {
    window.addEventListener("scroll", () => {
      if (window.scrollY > 50) {
        header.style.boxShadow = "0 10px 30px rgba(255,255,255,0.5)";
      } else {
        header.style.boxShadow = "0 2px 4px rgba(255, 255, 255, 0.1)";
      }
    });
  }

  const userIcon = document.querySelector(".user-dropdown");
  if (userIcon) {
    userIcon.addEventListener("click", () => {
      window.location.href = "/account";
    });
  }

  const login = document.getElementById("btnOpenLogin");
  if (login) {
    login.addEventListener("click", function (e) {
      e.preventDefault();

      // 1. kích thước cái tab thu nhỏ mà bạn muốn
      const popupWidth = 450;
      const popupHeight = 550;

      // 2. thuật toán canh giữa màn hình
      const left = window.screen.width / 2 - popupWidth / 2;
      const top = window.screen.height / 2 - popupHeight / 3;

      // 3. thông số của cửa sổ (tắt thanh địa chỉ, tắt cuộn...)
      const windowFeatures = `width=${popupWidth},height=${popupHeight},top=${top},left=${left},resizable=no,scrollbars=no,status=no,menubar=no,toolbar=no`;

      // 4. mở cửa sổ! (trỏ thẳng vào trang form đăng nhập bằng php của bạn)
      // giả sử link form đăng nhập của bạn là /auth/login-form
      const loginWindow = window.open(
        "https:// polygearid.ivi.vn/front-end/modules/auth/views/login.php",
        "FPT_Login_Window",
        windowFeatures,
      );

      // 5. đưa cửa sổ đó nổi lên trên cùng
      if (loginWindow) {
        loginWindow.focus();
      }
    });
  }

  // logout
  const logout = document.getElementById("logout");
  if (logout) {
    logout.addEventListener("click", async () => {
      const con = await fetch(
        "https:// polygearid.ivi.vn/back-end/api/auth/logout",
        {
          credentials: 'include',
          method: "POST",
        },
      );
      let res = await con.json();
      if (res.status == "success") {
        // xóa giỏ hàng local và các cache liên quan để tránh nhân đôi khi đăng nhập lại
        localStorage.removeItem("cartData");
        localStorage.removeItem("cartChangeTime");
        localStorage.removeItem("ResAi");

        window.location.reload();
      } else {
        alert("Không thể đang xuất lúc này, vui lòng thử lại.");
      }
    });
  }

  // mobile menu logic
  const mobileMenuToggle = document.getElementById("mobile-menu-toggle");
  const closeMobileMenu = document.getElementById("close-mobile-menu");
  const mobileSidebar = document.getElementById("mobile-sidebar");
  const mobileOverlay = document.getElementById("mobile-sidebar-overlay");
  const mobileSearchInput = document.getElementById("mobile-search-input");
  const mobileSearchBtn = document.getElementById("mobile-search-btn");
  const mobileLoginBtn = document.getElementById("mobile-login-btn");
  const mobileLogoutBtn = document.getElementById("mobile-logout");

  const toggleMobileMenu = (show) => {
    if (show) {
      mobileSidebar.classList.add("active");
      document.body.style.overflow = "hidden";
    } else {
      mobileSidebar.classList.remove("active");
      document.body.style.overflow = "";
    }
  };

  if (mobileMenuToggle) mobileMenuToggle.addEventListener("click", () => toggleMobileMenu(true));
  if (closeMobileMenu) closeMobileMenu.addEventListener("click", () => toggleMobileMenu(false));
  if (mobileOverlay) mobileOverlay.addEventListener("click", () => toggleMobileMenu(false));

  if (mobileSearchBtn && mobileSearchInput) {
    const handleMobileSearch = () => {
      if (mobileSearchInput.value.trim() !== "") {
        window.location.href = "/products/" + mobileSearchInput.value.trim();
      }
    };
    mobileSearchBtn.addEventListener("click", handleMobileSearch);
    mobileSearchInput.addEventListener("keydown", (e) => {
      if (e.key == "Enter") handleMobileSearch();
    });
  }

  if (mobileLoginBtn) {
    mobileLoginBtn.addEventListener("click", () => {
      toggleMobileMenu(false);
      document.getElementById("btnOpenLogin").click();
    });
  }

  if (mobileLogoutBtn) {
    mobileLogoutBtn.addEventListener("click", () => {
      document.getElementById("logout").click();
    });
  }

});
window.addEventListener(
  "message",
  async (event) => {
    // bảo mật: chỉ nhận tin nhắn từ đúng domain của mình
    if (event.origin !== "https:// polygearid.ivi.vn") return;

    const data = event.data;

    if (data.status === "success") {
      console.log("Đã nhận Token từ Tab Con:", data.token);

      // lưu vào localstorage
      localStorage.setItem("token", data.token);

      // đồng bộ giỏ hàng guest -> db
      const cartDataRaw = localStorage.getItem("cartData");
      if (cartDataRaw) {
        try {
          const cartData = JSON.parse(cartDataRaw);
          const items = Object.values(cartData.items || {});
          if (items.length > 0) {
            console.log("🔄 Đang đồng bộ giỏ hàng khách lên server...");
            await fetch("https:// polygearid.ivi.vn/back-end/api/cart/sync", {
              method: "POST",
              headers: { "Content-Type": "application/json" },
              body: JSON.stringify({ cartData: items }),
              credentials: "include",
            });
            // đồng bộ xong thì xoá cart local để tránh xung đột
            localStorage.removeItem("cartData");
          }
        } catch (e) {
          console.error("Lỗi đồng bộ giỏ hàng:", e);
        }
      }

      // thông báo hoặc chuyển hướng trang chính

      window.location.reload(); // hoặc redirect đi đâu tùy bồ
    }
  },
  false,
);

/**
 * Hàm đồng bộ Badge số lượng giỏ hàng trên Header (FRESH)
 */
async function syncHeaderCartBadge() {
  const badge = document.getElementById("cart-quantity");
  if (!badge) return;

  try {
    // 1. kiểm tra trạng thái đăng nhập
    const authRes = await fetch("https:// polygearid.ivi.vn/back-end/api/auth/islogin", {
      credentials: "include",
    });
    const authData = await authRes.json();

    let totalQty = 0;

    if (authData.status === "success") {
      // 2. nếu đã đăng nhập -> gọi api lấy số lượng từ db
      const cartRes = await fetch("https:// polygearid.ivi.vn/back-end/api/cart/quantity", {
        credentials: "include",
      });
      const cartData = await cartRes.json();
      if (cartData.status === "success") {
        totalQty = parseInt(cartData.quantity) || 0;
      }
    } else {
      // 3. nếu là guest -> tính từ localstorage
      const localCartRaw = localStorage.getItem("cartData");
      if (localCartRaw) {
        try {
          const localCart = JSON.parse(localCartRaw);
          const items = Object.values(localCart.items || {});
          totalQty = items.length;
        } catch (e) {
          console.error("Lỗi parse giỏ hàng local:", e);
        }
      }
    }

    // 4. update ui
    if (totalQty > 0) {
      badge.innerText = totalQty;
      badge.style.display = "flex";
    } else {
      badge.innerText = "";
      badge.style.display = "none";
    }
  } catch (err) {
    console.error("Lỗi đồng bộ badge giỏ hàng:", err);
  }
}
