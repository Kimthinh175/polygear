<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập Admin Portal</title>
    <base href="<?= BASE_URL ?>">
    <link href="https:// fonts.googleapis.com/css2?family=inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/admin/admin_style.css">
    <style>
        body {
            background-color: var(--background);
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
            font-family: 'Inter', sans-serif;
        }
        .login-card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 2.5rem;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .login-header h1 {
            font-size: 1.5rem;
            color: var(--text);
            margin-bottom: 0.5rem;
        }
        .login-header p {
            color: var(--text-muted);
            font-size: 0.875rem;
        }
        .error-message {
            color: #dc2626;
            background: #fee2e2;
            padding: 0.75rem;
            border-radius: var(--radius-sm);
            font-size: 0.875rem;
            margin-bottom: 1rem;
            display: none;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-header">
            <h1>Admin Portal</h1>
            <p>Đăng nhập để vào bảng điều khiển</p>
        </div>
        
        <div id="errorBox" class="error-message"></div>

        <form id="adminLoginForm">
            <div class="form-group">
                <label class="form-label" for="username">Tên đăng nhập</label>
                <input type="text" id="username" class="form-control" placeholder="Nhập username..." required>
            </div>
            <div class="form-group">
                <label class="form-label" for="password">Mật khẩu</label>
                <input type="password" id="password" class="form-control" placeholder="Nhập mật khẩu..." required>
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem; justify-content: center;">Đăng nhập</button>
        </form>
    </div>

    <script>
        document.getElementById('adminLoginForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;
            const errorBox = document.getElementById('errorBox');

            const submitBtn = e.target.querySelector('button');
            submitBtn.disabled = true;
            submitBtn.textContent = "Đang xử lý...";

            try {
                const response = await fetch('https:// polygearid.ivi.vn/back-end/api/admin/auth/login', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ username, password })
                });

                const data = await response.json();
                
                if (data.status === 'success') {
                    window.location.href = 'https:// polygearid.ivi.vn/admin/dashboard';
                } else {
                    errorBox.style.display = 'block';
                    errorBox.textContent = data.message || 'Đăng nhập thất bại';
                    submitBtn.disabled = false;
                    submitBtn.textContent = "Đăng nhập";
                }
            } catch (err) {
                errorBox.style.display = 'block';
                errorBox.textContent = 'Lỗi kết nối máy chủ';
                submitBtn.disabled = false;
                submitBtn.textContent = "Đăng nhập";
            }
        });
    </script>
</body>
</html>
