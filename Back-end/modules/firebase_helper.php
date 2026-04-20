<?php

class FirebaseHelper {
    private $serviceAccountPath;
    private $authUrl = 'https:// oauth2.googleapis.com/token';

    public function __construct() {
        $this->serviceAccountPath = ROOT_DIR . '/Back-end/API-app/firebase-service-account.json';
    }

    private function getAccessToken() {
        if (!file_exists($this->serviceAccountPath)) {
            error_log("Firebase settings not found at " . $this->serviceAccountPath);
            return false;
        }

        $json = file_get_contents($this->serviceAccountPath);
        $credentials = json_decode($json, true);
        
        $header = json_encode(['alg' => 'RS256', 'typ' => 'JWT']);
        
        $now = time();
        $payload = json_encode([
            'iss' => $credentials['client_email'],
            'scope' => 'https:// www.googleapis.com/auth/firebase.messaging',
            'aud' => $credentials['token_uri'],
            'exp' => $now + 3600,
            'iat' => $now
        ]);

        $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));

        $signatureInput = $base64UrlHeader . '.' . $base64UrlPayload;
        $signature = '';

        $privateKey = openssl_pkey_get_private($credentials['private_key']);
        if (!$privateKey) {
            error_log("Failed to load private key.");
            return false;
        }

        openssl_sign($signatureInput, $signature, $privateKey, 'sha256WithRSAEncryption');
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

        $jwt = $signatureInput . '.' . $base64UrlSignature;

        // exchange jwt for auth token
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->authUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt
        ]));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);
        if (isset($data['access_token'])) {
            return [
                'token' => $data['access_token'],
                'project_id' => $credentials['project_id']
            ];
        }
        
        error_log("Failed to get Firebase access token: " . $response);
        return false;
    }

    public function sendNotification($fcmToken, $title, $body) {
        if (empty($fcmToken)) return false;

        $authData = $this->getAccessToken();
        if (!$authData) return false;

        $url = 'https:// fcm.googleapis.com/v1/projects/' . $authdata['project_id'] . '/messages:send';

        $message = [
            'message' => [
                'token' => $fcmToken,
                'notification' => [
                    'title' => $title,
                    'body' => $body
                ],
                // webpush cho phép hiển thị thông báo với url khi click vào
                'webpush' => [
                    'fcm_options' => [
                        'link' => '/history'
                    ]
                ]
            ]
        ];

        $headers = [
            'Authorization: Bearer ' . $authData['token'],
            'Content-Type: application/json'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($message));
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode != 200) {
            error_log("Failed to send FCM notification. HTTP CODE: $httpCode. Response: " . $response);
            return false;
        }

        return true;
    }
}
?>
