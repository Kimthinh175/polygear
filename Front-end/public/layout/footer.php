
<footer>
  <div class="container footer-grid">
    <div class="footer-col">
      <a href="index.html" class="logo" style="margin-bottom: 20px; display: inline-block"><img src="img/layout/polygear-logo.png"
          alt="Logo" class="logo-img" /></a>
      <p>CÔNG TY TNHH THƯƠNG MẠI DỊCH VỤ POLYGEAR</p>
      <p>
        <i class="fa-solid fa-location-dot" style="margin-right: 5px"></i>
        Trụ sở chính: QTSC 9 Building, Đ. Tô Ký, Tân Chánh Hiệp, Quận 12, Thành phố Hồ Chí Minh
      </p>
    </div>
    <div class="footer-col">
      <h4>HỖ TRỢ KHÁCH HÀNG</h4>
      <ul>
        <li><a href="/warranty">Chính sách bảo hành</a></li>
        <li><a href="/payment">Phương thức thanh toán</a></li>
        <li><a href="/returns">Chính sách đổi trả hàng</a></li>
        <li><a href="/shipping">Chính sách vận chuyển</a></li>
      </ul>
    </div>
    <div class="footer-col">
      <h4>THÔNG TIN CÔNG TY</h4>
      <ul>
        <li><a href="/about">Về PolyGear</a></li>
        <li><a href="#">Hệ thống Showroom</a></li>
        <li><a href="#">Tin tức công nghệ</a></li>
        <li><a href="/contact">Liên hệ hợp tác</a></li>
        <li><a href="#">Tuyển dụng</a></li>
      </ul>
    </div>
    <div class="footer-col" style="
        border-radius: 8px;
      ">
      <h4>TỔNG ĐÀI HỖ TRỢ</h4>
      <p>Gọi mua hàng (8:30 - 20:30)</p>
      <span class="hotline-highlight"><i class="fa-solid fa-phone" style="font-size: 18px"></i> 1800
        6820</span>
      <p>Bảo hành cá nhân (8:30 - 18:00)</p>
      <span class="hotline-highlight"><i class="fa-solid fa-phone-volume" style="font-size: 18px"></i>
        0862 159 940</span>
    </div>
  </div>
  <div class="bottom-footer">
    <div class="container">
      <p>
        &copy; 2026 Bản quyền thuộc về Công Ty TNHH Thương Mại Dịch Vụ PolyGear. Chăm sóc bởi đội ngũ KT PolyGear.
      </p>
    </div>
  </div>

  <!-- AI Chat Widget Base -->
  <div id="ai-chat-widget"></div>
</footer>

<script type="module" src="/js/firebase-init.js"></script>
<link rel="stylesheet" href="/css/ai-chat.css">
<script src="/js/ai-chat.js"></script>
<script>
  document.addEventListener("DOMContentLoaded", function() {
    const path = window.location.pathname.toLowerCase();
    const isHome = path === '/' || path.includes('/home') || path.includes('index.php');
    const isCatalog = path.includes('/products') || path.includes('/detail') || path.includes('/category') || path.includes('/search');

    if (isHome || isCatalog) {
      if (typeof window.initAIChat === 'function') {
        window.initAIChat();
      }
    }
  });
</script>
</body>
</html>
