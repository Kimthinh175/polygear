<?php
if (!defined('SECURE_API_ACCESS')) {
    http_response_code(403);
    header("Location: /home");
    exit();
}
use Firebase\JWT\JWT;
class auth
{
    public function sendOTP()
    {
        require_once ROOT_DIR . "/Back-end/API-app/sms.php";

        $rawdata = file_get_contents("php:// input");
        $data = json_decode($rawdata, true);

        if (!isset($data['phone'])) {
            echo json_encode(["error" => "thieu so dien thoai"]);
            return;
        }

        // kiểm tra tài khoản có bị khóa không
        $userCheck = database::ThucThiTraVe("SELECT is_locked FROM user WHERE phone_number = :phone", ['phone' => $data['phone']]);
        if (!empty($userCheck) && $userCheck[0]['is_locked']) {
            echo json_encode([
                "status" => "error", 
                "is_locked" => true, 
                "message" => "Tài khoản này đã bị khoá do vi phạm chính sách người dùng của chúng tôi, xin vui lòng đăng nhập bằng tài khoản khác."
            ]);
            return;
        }

        $phone = "84" . substr($data['phone'], 1);
        $otp = rand(100000, 999999);
        $_SESSION['phone'] = $data['phone'];
        $_SESSION['otp'] = $otp;
        $res = sms::sendOTP($phone, $otp);
        echo json_encode(['status' => 'success']);

    }

    public function checkOTP()
    {
        $rawdata = file_get_contents("php:// input");
        $data = json_decode($rawdata, true);

        if (!isset($data['otp'])) {
            echo json_encode(["error" => "thieu otp", 'status' => false]);
        }
        if (isset($_SESSION['otp']) && $_SESSION['otp'] == $data['otp']) {
            $user = database::ThucThiTraVe("SELECT id,google_id,user_name,gmail,phone_number,avatar_url,is_locked FROM user 
                WHERE phone_number=:phone ", ['phone' => $_SESSION['phone']]);

            $pendingGG = $_SESSION['pending_google_login'] ?? null;
            $userId = null;

            if ($user && count($user) > 0) {
                $existingUser = $user[0];

                if (isset($existingUser['is_locked']) && $existingUser['is_locked']) {
                    echo json_encode(['status' => 'error', 'message' => 'Tài khoản này đã bị khoá do vi phạm chính sách người dùng của chúng tôi, xin vui lòng đăng nhập bằng tài khoản khác.']);
                    return;
                }

                $userId = $existingUser['id'];

                // nếu có luồng gg đang đợi
                if ($pendingGG) {
                    if (empty($existingUser['google_id'])) {
                        // gộp tài khoản
                        database::ThucThi("UPDATE user SET google_id=:gid, gmail=:gmail, avatar_url=:avatar WHERE id=:id", [
                            'gid' => $pendingGG['id'],
                            'gmail' => $pendingGG['gmail'],
                            'avatar' => $pendingGG['avatar_url'],
                            'id' => $userId
                        ]);
                        $existingUser['google_id'] = $pendingGG['id'];
                        $existingUser['gmail'] = $pendingGG['gmail'];
                        $existingUser['avatar_url'] = $pendingGG['avatar_url'];
                    } else if ($existingUser['google_id'] !== $pendingGG['id']) {
                        // tài khoản đã liên kết vs gg khác
                        echo json_encode(['status' => 'error', 'message' => 'Số điện thoại này đã được liên kết với một tài khoản Google khác. Vui lòng đăng nhập bằng tài khoản Google đó hoặc dùng số điện thoại khác!']);
                        // giữ nguyên otp để họ nhập sđt khác
                        return;
                    }
                }

                $_SESSION['user']['id'] = $existingUser['id'];
                $_SESSION['user']['name'] = $existingUser['user_name'];
                $_SESSION['user']['phone'] = $existingUser['phone_number'];
                $_SESSION['user']['google_id'] = $existingUser['google_id'] ?? null;
                $_SESSION['user']['gmail'] = $existingUser['gmail'] ?? null;
                $_SESSION['user']['avatar'] = $existingUser['avatar_url'] ?? 'img/user/default_user.avif';

            } else {
                // chưa có user
                $time = date('Y-m-d H:i:s', time());
                if ($pendingGG) {
                    database::ThucThi("INSERT INTO user(user_name,phone_number,avatar_url,google_id,gmail,create_at)
                    values(:name,:phone,:avatar,:gid,:gmail,:time)",
                        [
                            'name' => $pendingGG['name'],
                            'phone' => $_SESSION['phone'],
                            'avatar' => $pendingGG['avatar_url'],
                            'gid' => $pendingGG['id'],
                            'gmail' => $pendingGG['gmail'],
                            'time' => $time
                        ]
                    );
                } else {
                    database::ThucThi("INSERT INTO user(user_name,phone_number,avatar_url,create_at)
                        values(:name,:phone,:avatar,:time)",
                        [
                            'name' => $_SESSION['phone'],
                            'phone' => $_SESSION['phone'],
                            'avatar' => 'img/user/default_user.avif',
                            'time' => $time
                        ]
                    );
                }

                $userdata = database::ThucThiTraVe("SELECT id,user_name, phone_number, google_id,gmail, avatar_url
                        FROM user WHERE phone_number=:phone", ['phone' => $_SESSION['phone']]);

                $_SESSION['user']['id'] = $userdata[0]['id'];
                $_SESSION['user']['name'] = $userdata[0]['user_name'];
                $_SESSION['user']['phone'] = $userdata[0]['phone_number'];
                $_SESSION['user']['google_id'] = $userdata[0]['google_id'] ?? null;
                $_SESSION['user']['gmail'] = $userdata[0]['gmail'] ?? null;
                $_SESSION['user']['avatar'] = $userdata[0]['avatar_url'] ?? 'img/user/default_user.avif';
            }

            // xóa session pending gg
            if ($pendingGG) {
                unset($_SESSION['pending_google_login']);
            }

            $payload = [
                'iss' => 'du-an-1',
                'iat' => time(),
                'data' => [
                    'id' => $_SESSION['user']['id'],
                    'role' => 'user'
                ]
            ];
            $jwt = JWT::encode($payload, JWT_SECRET, 'HS256');
            setcookie('user_token', $jwt, [
                'expires' => 0,         // thời gian sống (0 = khi tắt trình duyệt)
                'path' => '/',          // hoạt động trên toàn bộ website
                'domain' => '',         // tên miền (để rỗng là lấy domain hiện tại)
                'secure' => true,       // chỉ gửi qua https (chống nghe lén)
                'httponly' => true,     // js không đọc được (chống xss)
                'samesite' => 'Strict'  // chỉ gửi cookie nếu request xuất phát từ chính web polygear (chống csrf)
            ]);
            $_SESSION['user']['token'] = $jwt;

            echo json_encode(['status' => 'success']);
            unset($_SESSION['otp']);
            unset($_SESSION['phone']);
            return;
        } else {
            echo json_encode(['status' => 'error: k có otp hoặc sai otp ']);
            return;
        }
    }

    public function getUserInfoLoginByGoogle()
    {
        header('Content-Type: text/html; charset=utf-8'); // để tí trả về js cho sub tab tự sát
        // kiểm tra xem google có trả code về không
        if (isset($_GET['code'])) {
            $post_data = [
                'client_id' => $_ENV['GG_CLIENT_ID'],
                'client_secret' => $_ENV['GG_CLIENT_SECRET'],
                'redirect_uri' => $_ENV['GG_REDIRECT_URL'],
                'code' => $_GET['code'],
                'grant_type' => 'authorization_code'
            ];

            $ch = curl_init($_ENV['GG_TOKEN_URL']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // tắt ssl để chạy xampp
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
            $token_response = curl_exec($ch);
            curl_close($ch);

            $token_data = json_decode($token_response, true);

            // có access token rồi, đi lấy thông tin khách hàng thôi!
            if (isset($token_data['access_token'])) {
                $access_token = $token_data['access_token'];

                // gọi api lấy user info
                $user_info_url = "https:// www.googleapis.com/oauth2/v2/userinfo";
                $ch2 = curl_init($user_info_url);
                curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch2, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch2, CURLOPT_HTTPHEADER, ["Authorization: Bearer " . $access_token]);
                $user_response = curl_exec($ch2);
                curl_close($ch2);

                // data google trả về
                $google_user = json_decode($user_response, true);

                $email = $google_user['email'] ?? '';
                $name = $google_user['name'] ?? '';
                $avatar = $google_user['picture'] ?? '';
                $google_id = $google_user['id'] ?? '';

                if (!$google_id) {
                    die("Không lấy được Google ID. Phản hồi: " . $user_response);
                }

                $user = database::ThucThiTraVe("SELECT * FROM user 
                    WHERE google_id=:id ", ['id' => $google_id]);

                $isfirstlogin = empty($user);

                if ($isfirstlogin) {
                    // lưu tạm thông tin gg vào session để dùng trong checkotp
                    $_SESSION['pending_google_login'] = [
                        'id' => $google_id,
                        'name' => $name,
                        'gmail' => $email,
                        'avatar_url' => $avatar
                    ];
                    header('location: https:// polygearid.ivi.vn/front-end/modules/auth/views/auth-phone-number.php');
                    exit;
                } else {
                    $existingUser = $user[0];
                    if (isset($existingUser['is_locked']) && $existingUser['is_locked']) {
                        echo "<script>
                            alert('Tài khoản này đã bị khoá do vi phạm chính sách người dùng của chúng tôi, xin vui lòng đăng nhập bằng tài khoản khác.');
                            window.close();
                        </script>";
                        exit;
                    }
                    // đã có tài khoản gg trong db, đăng nhập trực tiếp luôn
                    $_SESSION['user'] = [
                        'id' => $user[0]['id'],
                        'name' => $user[0]['user_name'],
                        'phone' => $user[0]['phone_number'],
                        'google_id' => $user[0]['google_id'],
                        'gmail' => $user[0]['gmail'],
                        'avatar' => $user[0]['avatar_url']
                    ];

                    $payload = [
                        'iss' => 'du-an-1',
                        'iat' => time(),
                        'data' => [
                            'id' => $_SESSION['user']['id'],
                            'role' => 'user'
                        ]
                    ];
                    $jwt = JWT::encode($payload, JWT_SECRET, 'HS256');
                    setcookie('user_token', $jwt, 0, '/', '', false, true);
                    $_SESSION['user']['token'] = $jwt;

                    echo "
                    <script>
                        if (window.opener) {
                            window.opener.postMessage({
                                status: 'success',
                                token: '$jwt'
                            }, 'https:// polygearid.ivi.vn');
                            window.close();
                        } else {
                            window.location.href = 'https:// polygearid.ivi.vn/home';
                        }
                    </script>";
                    exit;
                }
            } else {
                die("Lỗi trao đổi token với Google. Phản hồi: " . $token_response);
            }
        }
    }

    public function logout()
    {
        unset($_SESSION['user']);
        setcookie('user_token', '', time() - 3600, '/', '', false, true);
        echo json_encode(['status' => 'success']);
    }

    public function adminLogout()
    {
        if (isset($_SESSION['admin'])) {
            unset($_SESSION['admin']);
        }
        setcookie('admin_token', '', time() - 3600, '/', '', false, true);
        echo json_encode(['status' => 'success']);
    }

    public function adminLogin()
    {
        $rawdata = file_get_contents("php:// input");
        $data = json_decode($rawdata, true);
        if (empty($data['username']) || empty($data['password'])) {
            echo json_encode(["status" => "error", "message" => "Thiếu username hoặc password"]);
            return;
        }

        $admin = database::ThucThiTraVe("SELECT * FROM admin WHERE username=:u", ['u' => $data['username']]);
        if (!$admin) {
            echo json_encode(["status" => "error", "message" => "Sai tài khoản"]);
            return;
        }

        $admin = $admin[0];

        // so khớp mật khẩu: dùng password_verify, nếu thất bại thử so sánh chuỗi trơn (cho quá trình chuyển đổi / test)
        if (!password_verify($data['password'], $admin['password']) && $data['password'] !== $admin['password']) {
            echo json_encode(["status" => "error", "message" => "Sai mật khẩu"]);
            return;
        }

        $payload = [
            'iss' => 'du-an-1',
            'iat' => time(),
            'data' => [
                'id' => $admin['id'],
                'role' => $admin['role'],
                'type' => 'admin'
            ]
        ];

        $jwt = JWT::encode($payload, JWT_SECRET, 'HS256');

        $_SESSION['admin'] = [
            'id' => $admin['id'],
            'username' => $admin['username'],
            'role' => $admin['role'],
            'token' => $jwt
        ];
        // 0 makes it match the php session lifecycle (cleared when browser closes)
        setcookie('admin_token', $jwt, 0, '/', '', false, true);

        echo json_encode(["status" => "success", "message" => "Đăng nhập admin thành công", "role" => $admin['role']]);
    }

    public function islogin()
    {
        if (!isset($_SESSION['user'])) {
            echo json_encode(['status' => false]);
            return;
        } else {
            echo json_encode(['status' => 'success', 'info' => $_SESSION['user']]);
        }

    }
}
?>