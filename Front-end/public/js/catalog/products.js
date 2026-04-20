document.addEventListener("DOMContentLoaded", async function () {
  // 1. khai báo các biến quan trọng
  const productGrid = document.querySelector(".grid.grid-cols-1"); // khung chứa sản phẩm
  const skeleton = document.getElementById("products-skeleton");
  let allProducts = []; // mảng chứa toàn bộ data gốc kéo từ api về

  // lấy category từ url hiện tại (vd: /category/ram -> lấy chữ 'ram')
  // nếu không lấy được thì mặc định là 'ram' theo ý fen
  const pathArray = window.location.pathname.split("/");
  const categoryCode = pathArray[3] || "";

  // lắng nghe sự kiện "change" trên tất cả các thẻ select
  const allSelects = document.querySelectorAll("select");
  allSelects.forEach((select) => {
    select.addEventListener("change", applyFiltersAndSort);
  });

  // nút xóa lọc
  const btnReset = document.querySelector("button.text-red-500");
  if (btnReset) {
    btnReset.addEventListener("click", () => {
      allSelects.forEach((sel) => (sel.value = "")); // reset tất cả select về mặc định
      document.querySelector('select[name="sort"]').value = "popular"; // trả sắp xếp về bán chạy
      applyFiltersAndSort();
    });
  }

  // 2. gọi api lấy data lần đầu tiên
  await fetchInitialData();

  async function fetchInitialData() {
    try {
      const res = await fetch(
        `https:// polygearid.ivi.vn/back-end/api/products/category?category=${categorycode}`,
        { credentials: "include" }
      );
      const data = await res.json();
      if (data.info && data.info.length > 0) {
        allProducts = data.info; // lưu data gốc lại
        console.log(allProducts[0].specs);
      }
    } catch (error) {
      console.error("Lỗi tải data:", error);
    } finally {
      skeleton.classList.add("hidden"); // tắt skeleton
    }
  }

  // 3. hàm xử lý lọc & sắp xếp (trái tim của tính năng)
  function applyFiltersAndSort() {
    let filteredProducts = [...allProducts]; // tạo bản sao từ data gốc

    // lấy giá trị đang được chọn
    const brandVal = document.querySelector('select[name="brand"]')?.value;
    const priceVal = document.querySelector('select[name="price"]')?.value;
    const sortVal = document.querySelector('select[name="sort"]')?.value;

    // lọc theo hãng (brand)
    if (brandVal) {
      // giả sử api trả về brand_name hoặc brand_code
      filteredProducts = filteredProducts.filter(
        (p) => p.brand_name?.toLowerCase() === brandVal.toLowerCase(),
      );
    }

    // lọc theo giá
    if (priceVal) {
      filteredProducts = filteredProducts.filter((p) => {
        const price = Number(p.price);
        if (priceVal === "duoi-5-trieu") return price < 5000000;
        if (priceVal === "5-10-trieu")
          return price >= 5000000 && price <= 10000000;
        if (priceVal === "tren-10-trieu") return price > 10000000;
        return true;
      });
    }

    // lọc theo specs động (bus ram, dung lượng...)
    const specSelects = document.querySelectorAll('select[name^="filter_"]');
    let activeSpecs = {};

    specSelects.forEach((sel) => {
      if (sel.value) {
        // biến 'filter_dung_luong' thành 'dung_luong' để khớp với data
        const specCode = sel.name.replace("filter_", "").toUpperCase();
        activeSpecs[specCode] = sel.value;
      }
    });

    if (Object.keys(activeSpecs).length > 0) {
      filteredProducts = filteredProducts.filter((p) => {
        // kiểm tra xem sản phẩm này có chứa tất cả các spec đang được chọn không
        return Object.entries(activeSpecs).every(([code, val]) => {
          return p.specs.some(
            (s) => s.spec_code.toUpperCase() === code && s.spec_value === val,
          );
        });
      });
    }

    // 4. xử lý sắp xếp (sort)
    if (sortVal === "price-asc") {
      filteredProducts.sort((a, b) => Number(a.price) - Number(b.price));
    } else if (sortVal === "price-desc") {
      filteredProducts.sort((a, b) => Number(b.price) - Number(a.price));
    }
    // popular và newest cần data db có trường view/created_at, tạm thời bỏ qua hoặc sort theo id

    // 5. render lại html
    renderProducts(filteredProducts);
  }
  // hàm vẽ html (render)
  function renderProducts(products) {
    if (products.length === 0) {
      productGrid.innerHTML =
        '<div class="col-span-full text-center py-10 font-medium text-slate-500">Không tìm thấy sản phẩm phù hợp với bộ lọc!</div>';
      return;
    }

    const html = products
      .map((product) => {
        const shortSpecs = product.specs.map((s) => s.spec_value).join(", ");
        const originalPrice = product.price;
        const currentPrice = (product.sale_price && product.sale_price > 0) ? product.sale_price : originalPrice;
        const priceFormat = new Intl.NumberFormat("vi-VN").format(currentPrice) + "đ";
        const oldPriceFormat = (currentPrice < originalPrice) ? new Intl.NumberFormat("vi-VN").format(originalPrice) + "đ" : null;

        const isOutOfStock = product.stock <= 0;
        const stockStatus = isOutOfStock
          ? '<span class="text-[16px] text-red-600 font-bold">Hết hàng</span>'
          : `<span class="text-[16px] text-green-600 font-bold">Kho: ${product.stock}</span>`;

        return `
            <a href="/detail/${product.sku}" class="group bg-white rounded-xl border border-slate-200 overflow-hidden hover:border-primary/40 hover:shadow-xl hover:shadow-slate-200/50 transition-all flex flex-col">
                <div class="aspect-square relative overflow-hidden bg-slate-50 flex items-center justify-center p-8">
                    <img alt="${product.name}" class="w-full h-full object-contain group-hover:scale-105 transition-transform duration-500" src="${product.main_image_url}" />
                    ${(currentPrice < originalPrice) ? `<div class="absolute top-3 left-3 px-2 py-1 bg-red-500 text-[10px] font-bold text-white rounded uppercase tracking-wide">Giảm giá</div>` : `<div class="absolute top-3 left-3 px-2 py-1 bg-primary text-[10px] font-bold text-white rounded uppercase tracking-wide">Hot Seller</div>`}
                </div>
                <div class="p-5 flex-1 flex flex-col">
                    <div class="flex justify-between items-start mb-2">
                        <span class="text-[10px] font-bold text-primary uppercase tracking-widest">${product.cate_name}</span>
                    </div>
                    <h3 class="font-bold text-lg mb-1 text-slate-900 group-hover:text-primary transition-colors line-clamp-2">${product.name}</h3>
                    <p class="text-xs text-slate-500 mb-4 line-clamp-2 leading-relaxed">${shortSpecs}</p>
                    <div class="mt-auto flex items-center justify-between pt-2">
                        <div class="flex flex-col">
                            <span class="text-xl product-price font-bold text-[#C40016]">${priceFormat}</span>
                            ${oldPriceFormat ? `<span class="text-xs text-slate-400 line-through">${oldPriceFormat}</span>` : ''}
                            ${stockStatus}
                        </div>
                        <button data-sku="${product.sku}" data-name="${product.name}" data-price="${currentPrice}" data-origin="${originalPrice}" data-img="${product.main_image_url}"
                            class="add-to-cart size-10 flex items-center justify-center bg-slate-100 text-primary rounded-lg hover:bg-primary hover:text-white transition-all">
                            <span class="material-symbols-outlined">add_shopping_cart</span>
                        </button>
                    </div>
                </div>
            </a>
            `;
      })
      .join("");

    productGrid.innerHTML = html;

    // gắn sự kiện thêm vào giỏ hàng
    setupAddToCartEvents();
  }

  function setupAddToCartEvents() {
    const cartQuantity = document.getElementById("cart-quantity");
    const buttons = document.querySelectorAll(".add-to-cart");
    buttons.forEach((btn) => {
      btn.addEventListener("click", async (e) => {
        e.preventDefault();
        e.stopPropagation();
        const sku = btn.dataset.sku;
        const name = btn.dataset.name;
        const price = parseInt(btn.dataset.price) || 0;
        const originPrice = parseInt(btn.dataset.origin) || price;
        const img = btn.dataset.img;

        try {
          const authRes = await fetch("https:// polygearid.ivi.vn/back-end/api/auth/islogin", { credentials: 'include' });
          const authData = await authRes.json();

          if (authData.status === "success") {
            const res = await fetch("https:// polygearid.ivi.vn/back-end/api/cart/addtocart", {
              method: "POST",
              headers: { "Content-Type": "application/json" },
              credentials: "include",
              body: JSON.stringify({ sku: sku })
            });
            const addData = await res.json();
            if (addData.status === "success") {
              if (cartQuantity) {
                cartQuantity.innerText = addData.quantity;
                cartQuantity.style.display = "flex";
              }
              localStorage.setItem("cartChangeTime", Date.now());
              alert("Thêm vào giỏ hàng thành công!");
            }
          } else {
            // chế độ khách
            let localCart = JSON.parse(localStorage.getItem("cartData")) || { items: {} };
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
            localStorage.setItem("cartChangeTime", Date.now());

            let totalQty = 0;
            Object.values(localCart.items).forEach(item => totalQty += item.quantity);
            if (cartQuantity) {
              cartQuantity.innerText = totalQty;
              cartQuantity.style.display = "flex";
            }
            alert("Đã thêm vào giỏ hàng tạm thời!");
          }
        } catch (error) {
          console.error(error);
          alert("Lỗi khi thêm vào giỏ hàng!");
        }
      });
    });
  }
});
