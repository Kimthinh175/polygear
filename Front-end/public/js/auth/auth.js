const step1 = document.getElementById("step1");
const step2 = document.getElementById("step2");
const btnNext = document.getElementById("btnNext");
const btnBack = document.getElementById("btnBack");
const btnVerify = document.getElementById("btnVerify");
const btnResend = document.getElementById("btnResend");
const phoneInput = document.getElementById("phone");
const otpInput = document.getElementById("otp");
const displayPhone = document.getElementById("displayPhone");
const verify = document.querySelector(".verify");

let countdown; // biến quản lý bộ đếm ngược

// cảnh báo cho số điện thoại
function showPhoneWarning(message = "Vui lòng nhập số điện thoại") {
  if (!phoneInput) return;
  phoneInput.focus();
  phoneInput.style.borderColor = "#ff4d4f";
  phoneInput.style.boxShadow = "0 0 0 2px rgba(255, 77, 79, 0.2)";

  let errorText = document.getElementById("phone-error-msg");
  if (!errorText) {
    errorText = document.createElement("div");
    errorText.id = "phone-error-msg";
    errorText.className = "error-text";
    errorText.style.color = "#ff4d4f";
    errorText.style.fontSize = "13px";
    errorText.style.marginTop = "8px";
    phoneInput.parentNode.appendChild(errorText);
  }
  errorText.innerText = message;

  phoneInput.addEventListener("input", function clear() {
    phoneInput.style.borderColor = "#ddd";
    phoneInput.style.boxShadow = "none";
    if (errorText) errorText.remove();
    phoneInput.removeEventListener("input", clear);
  });
}

// cảnh báo cho otp
function showOtpError(message) {
  if (!otpInput) return;
  otpInput.focus();
  otpInput.style.borderColor = "#ff4d4f";

  let errorText = document.getElementById("otp-error-msg");
  if (!errorText) {
    errorText = document.createElement("div");
    errorText.id = "otp-error-msg";
    errorText.style.color = "#ff4d4f";
    errorText.style.fontSize = "13px";
    errorText.style.marginTop = "8px";
    otpInput.parentNode.appendChild(errorText);
  }
  errorText.innerText = message;

  otpInput.addEventListener("input", function clear() {
    otpInput.style.borderColor = "#ddd";
    if (errorText) errorText.remove();
    otpInput.removeEventListener("input", clear);
  });
}

// 
// 3. logic bộ đếm ngược (timer)
// 
function startTimer(duration) {
  clearInterval(countdown);
  const timerDisplay = document.getElementById("timer");
  const timerBox = document.getElementById("timer-box");

  if (timerBox) timerBox.style.display = "inline";
  if (btnResend) btnResend.style.display = "none";

  let timer = duration,
    minutes,
    seconds;
  countdown = setInterval(function () {
    minutes = parseInt(timer / 60, 10);
    seconds = parseInt(timer % 60, 10);

    minutes = minutes < 10 ? "0" + minutes : minutes;
    seconds = seconds < 10 ? "0" + seconds : seconds;

    if (timerDisplay) timerDisplay.textContent = minutes + ":" + seconds;

    if (--timer < 0) {
      clearInterval(countdown);
      if (timerBox) timerBox.style.display = "none";
      if (btnResend) btnResend.style.display = "inline";
    }
  }, 1000);
}

// 
// 4. xử lý các sự kiện chính
// 

// bước 1: gửi otp
if (btnNext) {
  btnNext.addEventListener("click", function () {
    if (!phoneInput) return;
    const phoneVal = phoneInput.value.trim();

    // validate nhanh
    if (phoneVal.length == 0) return showPhoneWarning();
    if (phoneVal.length != 10 || isNaN(Number(phoneVal)))
      return showPhoneWarning("Vui lòng nhập đúng số điện thoại 10 số");

    // gọi api gửi otp
    fetch("https:// polygearid.ivi.vn/back-end/api/auth/otp/send", { credentials: 'include',
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ phone: phoneVal }),
    })
      .then((res) => res.json())
      .then((res) => {
        if (res.status == "success") {
          // alert(res.otp); - removed for production sms flow
          if (displayPhone) displayPhone.innerText = phoneVal;
          // chuyển sang bước 2
          if (step1) step1.classList.remove("active");
          if (step2) step2.classList.add("active");

          startTimer(60); // bắt đầu đếm ngược 60s
          if (otpInput) setTimeout(() => otpInput.focus(), 200);
        } else {
          showPhoneWarning(res.message || "Lỗi không thể gửi OTP");
        }
      })
      .catch((err) => console.error("Lỗi Fetch:", err));
  });
}

// gửi lại mã
if (btnResend) {
  btnResend.addEventListener("click", function () {
    if (!phoneInput) return;
    const phoneVal = phoneInput.value.trim();
    fetch("https:// polygearid.ivi.vn/back-end/api/auth/otp/send", { credentials: 'include',
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ phone: phoneVal }),
    })
      .then((res) => res.json())
      .then((res) => {
        if (res.status === "success") {
          alert("Đã gửi lại mã mới!");
          startTimer(60);
        }
      });
  });
}

// bước 2: xác nhận otp & kết thúc
if (btnVerify) {
  btnVerify.addEventListener("click", function () {
    btnVerify.classList.add("active");
    if (verify) verify.classList.add("active");
    if (!otpInput) return;
    const otpVal = otpInput.value.trim();

    if (otpVal.length < 6) {
      btnVerify.classList.remove("active");
      if (verify) verify.classList.remove("active");
      showOtpError("Vui lòng nhập đủ 6 số OTP!");
      return;
    }

    // 2. gom chung otp, phone
    const payload = {
      otp: otpVal,
      phone: phoneInput ? phoneInput.value.trim() : "",
    };

    fetch("https:// polygearid.ivi.vn/back-end/api/auth/otp/check", { credentials: 'include',
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(payload),
    })
      .then((res) => res.json())
      .then((res) => {
        if (res.status === "success") {
          // logic chuyển hướng gốc của ông
          if (window.opener) {
            window.opener.postMessage(res, "https:// polygearid.ivi.vn");
            window.close();
          } else {
            window.location.href = "https:// polygearid.ivi.vn/home";
          }
        } else {
          btnVerify.classList.remove("active");
          if (verify) verify.classList.remove("active");
          showOtpError(res.message || "Mã xác thực không chính xác!");
          otpInput.value = "";
        }
      })
      .catch((err) => {
        // bắt thêm lỗi phòng hờ mạng mẽo chập chờn
        console.error("Lỗi gọi API OTP:", err);
        btnVerify.classList.remove("active");
        if (verify) verify.classList.remove("active");
        showOtpError("Lỗi kết nối máy chủ, vui lòng thử lại!");
      });
  });
}

// quay lại
if (btnBack) {
  btnBack.addEventListener("click", function () {
    clearInterval(countdown); // dừng bộ đếm nếu quay lại
    if (step2) step2.classList.remove("active");
    if (step1) step1.classList.add("active");
    if (phoneInput) phoneInput.focus();
  });
}
