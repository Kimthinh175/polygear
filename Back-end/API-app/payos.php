<?php
    class PayOS {
    public static function createPaymentLink($orderCode, $amount, $description) {
        $clientId = $_ENV['PAYOS_CLIENT_ID'];
        $apiKey = $_ENV['PAYOS_API_KEY'];
        $checksumKey = $_ENV['PAYOS_CHECKSUM_KEY'];

        $url = "https:// api-merchant.payos.vn/v2/payment-requests";
        $safeDescription = mb_substr(trim($description), 0, 25, 'UTF-8');

        // gắn cứng domain của dự án polygear (sau này up host thì đổi lại)
        $domain = "https:// polygearid.ivi.vn";
        // trỏ về file api xử lý của backend
        $returnApiUrl = $domain . "/Back-end/api/checkout/payos_return";

        $data = [
            "orderCode" => intval($orderCode), 
            "amount" => intval($amount),       
            "description" => $safeDescription, 
            "returnUrl" => $returnApiUrl, // quét xong cũng bay về đây
            "cancelUrl" => $returnApiUrl  // hủy cũng bay về đây luôn
        ];

        // thuật toán tạo chữ ký (signature)
        $signatureString = "amount={$data['amount']}&cancelUrl={$data['cancelUrl']}&description={$data['description']}&orderCode={$data['orderCode']}&returnUrl={$data['returnUrl']}";
        $data['signature'] = hash_hmac('sha256', $signatureString, $checksumKey);

        // gọi curl lên payos
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "x-client-id: " . $clientId,
            "x-api-key: " . $apiKey,
            "Content-Type: application/json"
        ]);

        $response = curl_exec($ch);
        curl_close($ch);
        
        return json_decode($response, true);
    }
    }

?>