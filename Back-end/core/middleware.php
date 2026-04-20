<?php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthMiddleware
{
    public static function check($requiredType = null)
    {
        // lấy token tùy theo luồng yêu cầu
        $token = null;
        if ($requiredType === 'admin') {
            $token = $_COOKIE['admin_token'] ?? null;
            if (!isset($_SESSION['admin'])) {
                self::response(401, "chưa login");
            }
        } else {
            // mặc định hoặc luồng user
            $token = $_COOKIE['user_token'] ?? null;
            if (!isset($_SESSION['user'])) {
                self::response(401, "chưa login");
            }
        }

        if (!$token) {
            self::response(401, "chưa login");
        }

        try {
            $decoded = JWT::decode($token, new Key(JWT_SECRET, 'HS256'));
            $userData = $decoded->data;

            if ($requiredType === 'admin') {

                if (!isset($userData->type) || $userData->type !== 'admin') {
                    self::response(403, "từ chối truy cập: không có quyền admin");
                }

                if (!isset($_SESSION['admin']['id']) || $_SESSION['admin']['id'] !== $userData->id) {
                    self::response(401, "từ chối truy cập: phát hiện giả mạo token admin");
                }

            } else if ($requiredType === 'user') {

                if (!isset($_SESSION['user']['id']) || $_SESSION['user']['id'] !== $userData->id) {
                    self::response(401, "từ chối truy cập: phát hiện giả mạo token user");
                }

            }

            return $userData;

        } catch (Exception $e) {
            self::response(401, "hết hạn hoặc token không hợp lệ !");
        }
    }

    private static function response($code, $msg)
    {
        http_response_code($code);
        echo json_encode(["status" => "error", "message" => $msg]);
        exit;
    }
}
?>