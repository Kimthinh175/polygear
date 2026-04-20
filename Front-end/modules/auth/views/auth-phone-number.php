<?php
    define('BASE_URL', 'https:// polygearid.ivi.vn/');
        if(isset($lmao)){
        $lmao='';
    }
?>

<base href="<?=BASE_URL?>">
<link rel="stylesheet" href="css/auth/auth.css">
<main>
    <div class="auth-box">
        <div id="step1" class="step active">
            <h2>Liên kết số điện thoại</h2>
            <p class="subtitle">Bổ sung số điện thoại để sử dụng dịch vụ</p>    
            <div class="input-group">
                <input type="tel" id="phone" placeholder="Nhập số điện thoại" maxlength="10">
            </div>
            <button class="btn-main" id="btnNext">Tiếp tục</button>
            
        </div>

        <div id="step2" class="step">
            <button class="back-btn" id="btnBack">← Quay lại</button>
            <h2>Nhập mã xác thực</h2>
            <p class="subtitle">Mã 6 số đã được gửi đến <strong id="displayPhone"></strong></p>
            
            <div class="input-group">
                <input type="text" id="otp" placeholder="••••••" maxlength="6" autocomplete="one-time-code">
            </div>
            <button class="btn-main" id="btnVerify">Xác nhận</button>
            
            <p style="text-align: center; font-size: 14px; color: #666; margin-top: 20px;">
                Chưa nhận được mã? <a href="#" style="color: #007bff; text-decoration: none; font-weight: bold;">Gửi lại</a>
            </p>
        </div>
    </div>
</main>
<script src="js\auth\auth.js"></script>
