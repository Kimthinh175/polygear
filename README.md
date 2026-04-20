# PolyGear - E-commerce Build PC & Linh Kiện Máy Tính

PolyGear là một nền tảng thương mại điện tử chuyên về linh kiện máy tính, hỗ trợ người dùng build PC thông minh với sự trợ giúp của AI (Gemini). Dự án được xây dựng theo kiến trúc SPA (Single Page Application) cho Frontend và RESTful API cho Backend.

## 🚀 Tính năng nổi bật
- **Build PC với AI:** Tự động gợi ý cấu hình và kiểm tra độ tương thích.
- **Thanh toán trực tuyến:** Tích hợp cổng thanh toán PayOS.
- **Xác thực OTP:** Gửi mã xác thực qua SMS (Infobip).
- **Đăng nhập Google:** Tiện lợi và nhanh chóng.
- **Quản trị toàn diện:** Dashboard thống kê, quản lý đơn hàng, kho vận và khuyến mãi.

---

## 🛠️ Hướng dẫn cài đặt

### 1. Yêu cầu môi trường
- PHP >= 8.0
- MySQL >= 5.7
- Composer
- Apache/Nginx (Hỗ trợ `.htaccess`)

### 2. Cài đặt các thư viện
Mở terminal tại thư mục gốc và chạy:
```bash
composer install
```

### 3. Cấu hình môi trường (QUAN TRỌNG)
Tạo file `.env` từ file mẫu:
```bash
cp .env.example .env
```
Mở file `.env` và thay thế các thông tin sau để dự án hoạt động:

#### A. Database
- `DB_HOST`, `DB_PORT`, `DB_USER`, `DB_PASS`, `DB_NAME`: Thông tin kết nối MySQL của bạn.

#### B. Google Login (OAuth 2.0)
- Lấy tại: [Google Cloud Console](https://console.cloud.google.com/)
- Cần tạo Credentials -> OAuth 2.0 Client ID.

#### C. Thanh toán PayOS
- Lấy tại: [PayOS Dashboard](https://dashboard.payos.vn/)
- Copy `Client ID`, `API Key`, và `Checksum Key`.

#### D. SMS OTP (Infobip)
- Lấy tại: [Infobip](https://www.infobip.com/)
- Copy `Base URL` và `API Key`.

#### E. AI (Gemini)
- Lấy tại: [Google AI Studio](https://aistudio.google.com/)
- Tạo `API Key` để sử dụng tính năng tư vấn build PC.

#### F. Firebase (Thông báo Push)
- Bạn cần tải file `firebase-service-account.json` từ Project Settings -> Service Accounts của Firebase Console.
- Đặt file vào đường dẫn: `Back-end/API-app/firebase-service-account.json`.

---

## 📁 Cấu trúc thư mục chính
- `Back-end/`: Chứa mã nguồn API, Core xử lý và các Module chức năng.
- `Front-end/`: Chứa giao diện người dùng, các module SPA và tài nguyên (CSS/JS).
- `.htaccess`: Cấu hình điều hướng cho SPA.

## 🔒 Bảo mật
- Không bao giờ commit file `.env` và `firebase-service-account.json` lên repository công khai.
- Đổi `JWT_SECRET` trong `.env` thành một chuỗi ngẫu nhiên dài để bảo mật token.

---
*Chúc bạn có trải nghiệm tốt với PolyGear!*
