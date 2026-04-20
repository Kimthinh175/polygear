<?php
class ApiCaller {
    
    private static $baseUrl = "https:// polygearid.ivi.vn/back-end/";

    // hàm lõi: chịu trách nhiệm cấu hình và gửi mọi loại request
    private static function sendRequest($method, $endpoint, $data = null, $withAuth = false) {
        $url = self::$baseUrl . $endpoint;

        // cấu hình cơ bản (tắt cảnh báo lỗi, set method)
        $options = [
            'http' => [
                'ignore_errors' => true,
                'method'  => $method 
            ]
        ];

        $headers = "";
        
        // 1. xử lý cookies (chỉ khi $withauth = true) (bảo mật)
        if ($withAuth && !empty($_COOKIE)) {
            $cookies = [];
            foreach ($_COOKIE as $key => $value) {
                $cookies[] = $key . '=' . urlencode($value);
            }
            $headers .= "Cookie: " . implode('; ', $cookies) . "\r\n";
        }

        // 2. xử lý data (json body)
        if ($data !== null) {
            $jsonData = json_encode($data);
            $headers .= "Content-Type: application/json\r\n";
            $options['http']['content'] = $jsonData;
        }

        // 3. gắn headers vào options nếu có
        if ($headers !== "") {
            $options['http']['header'] = $headers;
        }

        // đóng gói cấu hình và gửi đi
        $context  = stream_context_create($options);
        $response = @file_get_contents($url, false, $context);
        
        if ($response === false) {
            return null; // gọi thất bại (sập server, mất mạng...)
        }

        // dịch cục json của back-end thành mảng php
        return json_decode($response, true);
    }

    // 
    // các hàm mặt tiền (gọi cho gọn)
    // 
    
    // get: không có body
    public static function get($endpoint, $withAuth = false) {
        return self::sendRequest('GET', $endpoint, null, $withAuth);
    }

    // post: có gửi data tạo mới
    public static function post($endpoint, $data, $withAuth = false) {
        return self::sendRequest('POST', $endpoint, $data, $withAuth);
    }

    // put: có gửi data cập nhật (ghi đè toàn bộ)
    public static function put($endpoint, $data, $withAuth = false) {
        return self::sendRequest('PUT', $endpoint, $data, $withAuth);
    }

    // patch: có gửi data cập nhật (sửa 1 phần nhỏ)
    public static function patch($endpoint, $data, $withAuth = false) {
        return self::sendRequest('PATCH', $endpoint, $data, $withAuth);
    }

    // delete
    public static function delete($endpoint, $data, $withAuth = false) {
        return self::sendRequest('DELETE', $endpoint, $data, $withAuth);
    }
}
?>
