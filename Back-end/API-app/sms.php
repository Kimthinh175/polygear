<?php
class sms {
    public static function sendOTP($phone, $otpCode) {
        $url = $_ENV['OTP_BASE_URL'] . "/sms/3/messages";
        
        // chuẩn bị cục dữ liệu json theo đúng chuẩn infobip
        $payload = [
            "messages" => [
                [
                    "sender" => "PolyGear", 
                    "destinations" => [
                        ["to" => $phone] 
                    ],
                    "content" => [
                        "text" => "[PolyGear] Ma xac thuc dang nhap cua ban la: " . $otpCode
                    ]
                ]
            ]
        ];

        // mở luồng curl
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true); // tắt ssl check cho xampp localhost
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        
        // 3. khai báo header
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: App " . $_ENV['OTP_API_KEY'],
            "Content-Type: application/json",
            "Accept: application/json"
        ]);

        $response = curl_exec($ch);
        curl_close($ch);
        
        // trả về kết quả
        return json_decode($response, true);

        // "bulkid": "177348234782907950956015",
        // "messages": [
        // {
        // "messageid": "177348234782907950956016",
        // "status": {
        // "groupid": 1,
        // "groupname": "pending",
        // "id": 26,
        // "name": "pending_accepted",
        // "description": "message sent to next instance"
        // },
        // "destination": "8486219940"
        // }
        // ]
    }
}
?>