<?php
class GeminiAI {
    private static function getConfig() {
        $config_path = ROOT_DIR . '/Back-end/cache/ai_config.json';
        if (file_exists($config_path)) {
            $config = json_decode(file_get_contents($config_path), true);
            if ($config) return $config;
        }
        return [
            'provider' => 'google',
            'model' => 'gemini-3-flash-preview',
            'api_key' => ''
        ];
    }

    public static function askQuestion($prompt) {
        $config = self::getConfig();
        $provider = $config['provider'];
        $model = $config['model'];

        if ($provider === 'groq') {
            $apiKey = !empty($config['api_key']) ? $config['api_key'] : $_ENV['GROQ_API_KEY'];
            $url = "https:// api.groq.com/openai/v1/chat/completions";
            
            // lấy products_cache thay vì cache riêng (groq không hỗ trợ cachedcontents như gemini)
            $AllProductInfo = file_get_contents(ROOT_DIR."/Back-end/cache/products_cache.json");
            $system_prompt = "Bạn là chuyên gia build PC siêu đẳng của cửa hàng PolyGear. Dưới đây là danh sách sản phẩm cửa hàng đang bán: $AllProductInfo. 
            Nhiệm vụ: 
            1. Đánh giá độ tương thích các linh kiện trong giỏ hàng. 
            2. Gợi ý sản phẩm còn thiếu từ danh sách trên cho đủ bộ PC. 
            
            YÊU CẦU: Trả về chuỗi JSON ở dạng Minified, nằm trên 1 dòng duy nhất, không có text dư thừa, theo cấu trúc:
            {
              \"message\": \"Lời nhận xét ngắn gọn (tối đa 30 chữ)\",
              \"warning\": \"Cảnh báo tương thích nếu có\",
              \"suggest\": [
                {\"sku\": \"mã_sku\", \"name\": \"tên_sản_phẩm\", \"main_image_url\": \"đường_dẫn_ảnh\"}
              ]
            }";

            $data = [
                "model" => $model,
                "messages" => [
                    [
                        "role" => "system",
                        "content" => $system_prompt
                    ],
                    [
                        "role" => "user",
                        "content" => $prompt
                    ]
                ],
                "response_format" => ["type" => "json_object"]
            ];

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey
            ]);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            $response = curl_exec($ch);
            curl_close($ch);

            $result = json_decode($response, true);
            
            if (isset($result['error'])) {
                 echo json_encode([
                     'status' => 'error',
                     'message' => 'Lỗi từ Groq API: ' . ($result['error']['message'] ?? 'Không xác định'),
                     'debug' => $result['error']
                 ], JSON_UNESCAPED_UNICODE);
                 return;
            }

            $ai_text_string = $result['choices'][0]['message']['content'] ?? '{}';
            
            // dọn dẹp khối mã markdown nếu ai không tuân thủ định dạng
            $ai_text_string = preg_replace('/```json\s*|\s*```/', '', $ai_text_string);

            echo trim($ai_text_string);
            return;
        }

        // logic của google gemini
        // ưu tiên key từ file cấu hình, nếu không có lấy từ env
        $apiKey = (!empty($config['api_key']) && $provider === 'google') ? $config['api_key'] : $_ENV['GEMINI_API_KEY'];
        
        $cache_link = ROOT_DIR . "/Back-end/cache/gemini_cache.json";
        $cacheData = file_exists($cache_link) ? json_decode(file_get_contents($cache_link), true) : null;
        $cache_name = $cacheData['name'] ?? null;
        
        $url = "https:// generativelanguage.googleapis.com/v1beta/models/" . $model . ":generatecontent?key=" . $apikey;

        if ($cache_name && strpos($model, 'gemini-1.5') !== false || strpos($model, 'gemini-3') !== false) {
             $data = [
                 "cachedContent" => $cache_name, 
                 "contents" => [
                     [
                         "role" => "user",
                         "parts" => [
                             ["text" => $prompt]
                         ]
                     ]
                 ],
                 "generationConfig" => [
                     "responseMimeType" => "application/json"
                 ]
             ];
        } else {
             // dự phòng nếu cache lỗi hoặc định dạng không hỗ trợ
             $system_prompt = "Bạn là chuyên gia build PC siêu đẳng của cửa hàng PolyGear..."; // simplified fallback
             $data = [
                 "contents" => [
                     [
                         "role" => "user",
                         "parts" => [
                             ["text" => $prompt]
                         ]
                     ]
                 ],
                 "generationConfig" => [
                     "responseMimeType" => "application/json"
                 ]
             ];
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        $response = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($response, true);

        if (isset($result['error'])) {
             echo json_encode([
                 'status' => 'error',
                 'message' => 'Lỗi từ Gemini API: ' . ($result['error']['message'] ?? 'Không xác định'),
                 'debug' => $result['error']
             ], JSON_UNESCAPED_UNICODE);
             return;
        }

        $ai_text_string = $result['candidates'][0]['content']['parts'][0]['text'] ?? '{}';
        
        // dọn dẹp các khối mã markdown
        $ai_text_string = preg_replace('/```json\s*|\s*```/', '', $ai_text_string);

        $ai_string = preg_replace('/```json\s*|\s*```/', '', trim($ai_text_string));
        $ai_array = json_decode($ai_string, true);
        echo json_encode($ai_array ? $ai_array : $ai_string, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    public static function buildPcFromCart($budget, $level, $task, $cart) {
        $config = self::getConfig();
        $provider = $config['provider'];
        $model = $config['model'];
        $AllProductInfo = file_get_contents(ROOT_DIR."/Back-end/cache/products_cache.json");

        $prompt = "Bạn là chuyên gia build PC. Bạn cần tư vấn MỘT cấu hình PC duy nhất dựa trên:
        - Ngân sách: $budget
        - Phân khúc: $level
        - Mục đích sử dụng: $task
        - Giỏ hàng hiện tại: $cart
        - Danh sách toàn bộ sản phẩm cửa hàng: $AllProductInfo

        Quy trình (Chain of thought):
        Phase 1: Phân tích giỏ hàng để xem khách đã có gì và CÒN THIẾU những danh mục linh kiện cơ bản nào (Mainboard, CPU, RAM, Ổ cứng, Nguồn, Case, VGA nếu cần) để thành một bộ PC hoàn chỉnh và hoạt động được.
        Phase 2: Dựa vào Danh sách sản phẩm cửa hàng, chọn thêm các sản phẩm còn thiếu đó sao cho tương thích với giỏ hàng, phù hợp ngân sách $budget và phân khúc $level, mục đích $task.

        YÊU CẦU: Trả về chuỗi JSON ở dạng Minified, nằm trên 1 dòng duy nhất, không có text dư thừa, theo cấu trúc BẮT BUỘC sau:
        {
          \"description\": \"Mô tả chi tiết cấu hình (Tại sao chọn, tương thích, hiệu năng)\",
          \"products\": [
            {\"sku\": \"mã\", \"name\": \"tên\", \"main_image_url\": \"đường_dẫn_ảnh\", \"price\": 1000000}
          ],
          \"total\": tổng_giá_toàn_bộ_bằng_số
        }
        Lưu ý: mảng 'products' BẮT BUỘC phải gộp cả linh kiện KHÁCH ĐÃ CHỌN TRONG GIỎ HÀNG và linh kiện AI GỢI Ý THÊM.";

        return self::executeAI($provider, $model, $prompt, $config, true);
    }

    public static function extractChatIntent($message, $history, $categories = '') {
        $config = self::getConfig();
        $provider = $config['provider'];
        $model = $config['model'];

        $histStr = "";
        foreach($history as $h) {
            $r = $h['role'] === 'user' ? 'Khách' : 'Bot';
            $histStr .= "$r: {$h['content']}\n";
        }

        $prompt = "Lịch sử chat:\n$histStr\n\nTin nhắn mới: '$message'.\nPhân tích ý định của khách hàng và CHỈ trả về JSON theo cấu trúc:\n{\n  \"intent\": \"chat\" (nếu chỉ hỏi đáp/chào hỏi thông thường) HOẶC \"buy_hardware\" (nếu tìm mua linh kiện đơn lẻ) HOẶC \"build_pc\" (nếu muốn tư vấn cấu hình, build máy),\n  \"answer\": \"Nếu intent='chat', hãy tư vấn/trả lời người dùng bằng ngôn ngữ tự nhiên, ngắn gọn (dưới 50 từ). Nếu intent khác, để chuỗi rỗng\",\n  \"filters\": {\n     \"keywords\": [\"các từ khóa cụ thể về sản phẩm (vd: 16gb, 500gb, core i5). KHÔNG bao gồm tên danh mục chung chung ở đây\"],\n     \"category\": \"Tên danh mục chính xác (chọn 1 từ danh sách sau nếu phù hợp: $categories). Nếu không khớp danh mục nào, để chuỗi rỗng.\",\n     \"min_price\": số_tiền_thấp_nhất (0 nếu không có),\n     \"max_price\": số_tiền_cao_nhất (nếu có, ví dụ 5000000, 0 nếu không có)\n  }\n}";
        
        return self::executeAI($provider, $model, $prompt, $config, true);
    }

    public static function generateChatAnswer($message, $foundProducts, $history, $intent, $filters) {
        $config = self::getConfig();
        $provider = $config['provider'];
        $model = $config['model'];

        $histStr = "";
        foreach($history as $h) {
            $r = $h['role'] === 'user' ? 'Khách' : 'Bot';
            $histStr .= "$r: {$h['content']}\n";
        }

        if ($intent === 'build_pc') {
            $AllProductInfo = file_get_contents(ROOT_DIR."/Back-end/cache/products_cache.json");
            $max_price = $filters['max_price'] ?? 0;
            
            $prompt = "Lịch sử chat:\n$histStr\n\nYêu cầu build PC mới: '$message'.\nNgân sách tối đa (VND): $max_price.\nDanh sách toàn bộ sản phẩm cửa hàng (JSON): $AllProductInfo.\n\nNhiệm vụ: Chọn lọc các linh kiện từ danh sách sản phẩm để tạo ra tối đa 3 bộ cấu hình PC phù hợp nhất với ngân sách. Mỗi bộ phải CỐ GẮNG đầy đủ linh kiện cơ bản (CPU, Mainboard, RAM, SSD/HDD, Nguồn, Vỏ Case, VGA nếu có). Tổng giá linh kiện mỗi bộ không được vượt quá ngân sách quá xa.\n\nCHỈ trả về chuỗi JSON định dạng:\n{\n  \"answer\": \"Đoạn chào hỏi ngắn gọn (VD: Dạ em gửi anh 3 cấu hình...)\",\n  \"configs\": [\n    {\n      \"title\": \"Tên cấu hình (VD: Cấu hình Sinh Viên)\",\n      \"description\": \"Mô tả ưu nhược điểm...\",\n      \"skus\": [\"mã sku 1\", \"mã sku 2\"]\n    }\n  ]\n}";
        } else {
            $productStr = empty($foundProducts) ? "Không tìm thấy" : json_encode($foundProducts, JSON_UNESCAPED_UNICODE);
            $prompt = "Lịch sử chat:\n$histStr\n\nTin nhắn mới: '$message'.\nSản phẩm tìm thấy trong DB: $productStr.\n\nTư vấn ngắn gọn, thân thiện (dưới 50 từ). Nếu có sản phẩm tìm thấy phù hợp thì tư vấn và nhắc đến chúng, nếu không thì báo hết hàng. CHỈ trả về JSON định dạng: {\"answer\": \"lời tư vấn\"}";
        }

        return self::executeAI($provider, $model, $prompt, $config, true);
    }

    private static function executeAI($provider, $model, $prompt, $config, $jsonMode = false) {
        if ($provider === 'groq') {
            $apiKey = !empty($config['api_key']) ? $config['api_key'] : $_ENV['GROQ_API_KEY'];
            $url = "https:// api.groq.com/openai/v1/chat/completions";
            $data = [
                "model" => $model,
                "messages" => [["role" => "system", "content" => "Bạn là nhân viên tư vấn PolyGear."], ["role" => "user", "content" => $prompt]]
            ];
            if ($jsonMode) $data["response_format"] = ["type" => "json_object"];

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [ 'Content-Type: application/json', 'Authorization: Bearer ' . $apiKey ]);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            $response = curl_exec($ch);
            curl_close($ch);

            $result = json_decode($response, true);
            if (isset($result['error'])) return json_encode(['error' => true, 'message' => $result['error']['message']]);
            $str = $result['choices'][0]['message']['content'] ?? '{}';
            return preg_replace('/```json\s*|\s*```/', '', $str);
        }

        // gemini
        $apiKey = (!empty($config['api_key']) && $provider === 'google') ? $config['api_key'] : $_ENV['GEMINI_API_KEY'];
        $url = "https:// generativelanguage.googleapis.com/v1beta/models/" . $model . ":generatecontent?key=" . $apikey;
        $data = [
            "contents" => [["role" => "user", "parts" => [["text" => $prompt]]]]
        ];
        if ($jsonMode) {
             $data["generationConfig"] = ["responseMimeType" => "application/json"];
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        $response = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($response, true);
        if (isset($result['error'])) return json_encode(['error' => true, 'message' => $result['error']['message']]);
        $str = $result['candidates'][0]['content']['parts'][0]['text'] ?? '{}';
        return preg_replace('/```json\s*|\s*```/', '', trim($str));
    }
    public static function cache($prompt) {
        $config = self::getConfig();
        $provider = $config['provider'];
        $model = $config['model'];

        // api groq không hỗ trợ hệ thống cachedcontents của google
        if ($provider === 'groq') {
            echo json_encode(['status' => 'skipped', 'message' => 'Groq không cần cache']);
            return;
        }

        $apiKey = (!empty($config['api_key']) && $provider === 'google') ? $config['api_key'] : $_ENV['GEMINI_API_KEY'];
        
        $url = "https:// generativelanguage.googleapis.com/v1beta/cachedcontents?key=" . $apikey;

        $data = [
            "model" => "models/" . $model,
            "contents" => [
                [
                    "role" => "user",
                    "parts" => [
                        ["text" => $prompt]
                    ]
                ]
            ],
            "ttl" => "3592000s"
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        $response = curl_exec($ch);
        curl_close($ch);

        $file_path = ROOT_DIR . '/Back-end/cache/gemini_cache.json';
        file_put_contents($file_path,json_encode($response,JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

        $result = json_decode($response, true);
        echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
    
}
?>