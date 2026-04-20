<?php
    if(isset($lmao)){
        $lmao='';
    }
?>
<?php
define('BASE_URL', 'https:// polygearid.ivi.vn/');
if ($pendingGG) {
    unset($_SESSION['pending_google_login']);
}
?>

<base href="<?= BASE_URL ?>">
<link rel="stylesheet" href="css/auth/auth.css">
<main>
    <div class="auth-box">
        <div id="step1" class="step active">
            <h2>Welcome to PolyGear !</h2>
            <p class="subtitle">Đăng nhập bằng số điện thoại</p>

            <div class="input-group">
                <input type="tel" id="phone" placeholder="Nhập số điện thoại" maxlength="10">
            </div>
            <button class="btn-main" id="btnNext">Tiếp tục</button>

            <div class="divider">Hoặc đăng nhập bằng</div>
            <a href="https:// accounts.google.com/o/oauth2/v2/auth?client_id=245037958001-crunti7j6sca7lorkid81mpkmf5ap86k.apps.googleusercontent.com&redirect_uri=https%3a%2f%2fpolygearid.ivi.vn%2fback-end%2fapi%2fauth%2fgoogle&response_type=code&scope=email%20profile"
                style="text-decoration: none">
                <button class="btn-google">
                    <img src="https:// upload.wikimedia.org/wikipedia/commons/c/c1/google_%22g%22_logo.svg" alt="google">
                    Google
                </button>
            </a>
        </div>

        <div id="step2" class="step">
            <button class="back-btn" id="btnBack">← Quay lại</button>
            <h2>Nhập mã xác thực</h2>
            <p class="subtitle">Mã 6 số đã được gửi đến <strong id="displayPhone"></strong></p>

            <div class="input-group">
                <input type="text" id="otp" placeholder="••••••" maxlength="6" autocomplete="one-time-code">
            </div>
            <center class="verify step"><small>Đang xác thực...</small></center>
            <button class="btn-main" id="btnVerify">Xác nhận</button>

            <p style="text-align: center; font-size: 14px; color: #666; margin-top: 20px;">
                <span id="timer-box">Gửi lại mã sau: <b id="timer">01:00</b></span>
                <a href="javascript:void(0)" id="btnResend"
                    style="color: #007bff; text-decoration: none; font-weight: bold; display: none;">Gửi lại mã</a>
            </p>
        </div>
    </div>
</main>
<script src="js\auth\auth.js"></script>