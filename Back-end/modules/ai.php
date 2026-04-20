<?php
    class ai {

        private function getAllAndDetail() {
            $info = database::ThucThiTraVe("SELECT cate.code,
                cate.name as cate_name,
                concat(pr.name,' ',pr_vr.variant_name) as name,
                pr.brand_id,
                pr_vr.price,
                pr_vr.stock,
                pr_vr.main_image_url,
                pr_vr.sku,
                pr_vr.status
                FROM products pr
                JOIN product_variants pr_vr on pr.id=pr_vr.product_id
                JOIN category cate on cate.id=pr.category_id
            ") ?? [];

            for ($i = 0; $i < count($info); $i++) {
                $info[$i]['specs'] = database::ThucThiTraVe("SELECT 
                    vr_s.spec_value,
                    s.spec_code
                    FROM variant_specs vr_s
                    JOIN specs s on s.spec_code=vr_s.spec_code
                    JOIN product_variants pr_vr on vr_s.variant_id= pr_vr.id
                    WHERE sku = :sku
                    ", ['sku' => $info[$i]['sku']]);
            }


            return $info ?? null;
        }

        public function sendRequest(){
           session_write_close();
            require_once ROOT_DIR."/Back-end/API-app/AI.php";

            $rawdata= file_get_contents("php:// input");
            $data= json_decode($rawdata,true);

            $cart = json_encode($data['cart'] ?? []);
            $budget = $data['budget'] ?? 'Không giới hạn';
            $level = $data['level'] ?? 'Trung bình';
            $task = $data['task'] ?? 'Đa dụng';

            $result = GeminiAI::buildPcFromCart($budget, $level, $task, $cart);
            
            echo $result;
        }

        public function cacheAIData(){
            require_once ROOT_DIR."/Back-end/API-app/AI.php";
            $rawdata = file_get_contents(ROOT_DIR.'/Back-end/cache/products_cache.json');
            $products= json_encode($rawdata);

            $prompt = "Bạn là chuyên gia build PC siêu đẳng của cửa hàng PolyGear.
                    Dưới đây là các sản phẩm cửa hàng đang bán: $products.

                    Nhiệm vụ của bạn: 
                    2. Đánh giá độ tương thích của các linh kiện trong giỏ (có nghẽn cổ chai không, cắm vừa nhau không).
                    3. Gợi ý 1 sản phẩm mỗi danh mục TỪ DANH SÁCH CỬA HÀNG ĐANG BÁN để khách mua thêm cho đủ bộ (Ví dụ thấy thiếu Nguồn thì gợi ý Nguồn phù hợp công suất, thiếu RAM thì gợi ý RAM).

                    BẮT BUỘC trả về đúng định dạng JSON này, không in thêm bất kỳ chữ nào khác:
                    {
                        'message': 'Lời nhận xét của bạn về giỏ hàng (ngắn gọn).',
                        'warning': 'Nếu có linh kiện không tương thích thì ghi vào đây, nếu không thì để trống.',
                        'suggest': '[Điền sku, name, main img của mỗi sản phẩm gợi ý vào đây]'
                    }
                    BẮT BUỘC trả về đúng định dạng JSON.
                    YÊU CẦU QUAN TRỌNG: Trả về chuỗi JSON ở dạng Minified (nằm trên đúng 1 dòng duy nhất, TUYỆT ĐỐI KHÔNG dùng ký tự xuống dòng \n, KHÔNG thụt lề, KHÔNG có khoảng trắng thừa).
                    YÊU CẦU ÉP BUỘC: Phần 'message' viết CỰC KỲ NGẮN GỌN, tối đa 20 chữ, 'suggest' chỉ gợi ý những linh kiện có độ phù hợp cao nhất.
                    Lưu ý: data của giỏ hàng sẽ được gửi vào những lần sau.
                    Lưu ý: trả về kết quả nhanh nhất có thể, dưới 2s.";
                    
            GeminiAI::cache($prompt);
        }


        public function updateProductCache() {

            $AllProductInfo = $this->getAllAndDetail();
            
            $json_string = json_encode($AllProductInfo, JSON_UNESCAPED_UNICODE);
            
            $file_path = ROOT_DIR . '/Back-end/cache/products_cache.json';
            file_put_contents($file_path, $json_string);
            
            echo json_encode(['status' => 'success', 'message' => 'Cập nhật bộ nhớ đệm sản phẩm thành công!'], JSON_UNESCAPED_UNICODE);
        }

        public function getSettings() {
             $config_path = ROOT_DIR . '/Back-end/cache/ai_config.json';
             if (file_exists($config_path)) {
                 $config = json_decode(file_get_contents($config_path), true);
                 if ($config) {
                     echo json_encode(['status' => 'success', 'data' => $config]);
                     return;
                 }
             }
             // cài đặt mặc định
             echo json_encode(['status' => 'success', 'data' => [
                 'provider' => 'google',
                 'model' => 'gemini-3-flash-preview',
                 'api_key' => ''
             ]]);
        }
        
        public function updateSettings() {
             $raw = file_get_contents('php:// input');
             $body = json_decode($raw, true);

             // hỗ trợ cả json thô và post bình thường
             $provider = $body['provider'] ?? $_POST['provider'] ?? 'google';
             $model = $body['model'] ?? $_POST['model'] ?? 'gemini-3-flash-preview';
             $apiKey = $body['api_key'] ?? $_POST['api_key'] ?? '';

             $config = [
                 'provider' => $provider,
                 'model' => $model,
                 'api_key' => $apiKey
             ];

             $config_path = ROOT_DIR . '/Back-end/cache/ai_config.json';
             file_put_contents($config_path, json_encode($config, JSON_PRETTY_PRINT));
             
             // nếu chọn google thì có thể cần kích hoạt cache
             if ($provider === 'google') {
             // kích hoạt cache bất đồng bộ nếu cần hoặc để frontend tự gọi
                 // frontend có thể gọi lại api cacheaidata là xong
             }

             echo json_encode(['status' => 'success', 'message' => 'Lưu cài đặt AI thành công']);
        }
        
        public function chatHistory() {
             if (session_status() === PHP_SESSION_NONE) session_start();
             $history = $_SESSION['ai_chat'] ?? [];
             echo json_encode(['status' => 'success', 'data' => $history]);
        }

        public function chatSend() {
             if (session_status() === PHP_SESSION_NONE) session_start();
             require_once ROOT_DIR."/Back-end/API-app/AI.php";
             
             $raw = file_get_contents('php:// input');
             $body = json_decode($raw, true);
             $message = trim($body['message'] ?? '');

             if (!$message) {
                 echo json_encode(['status' => 'error', 'message' => 'Tin nhắn trống']);
                 return;
             }

             if (!isset($_SESSION['ai_chat'])) {
                 $_SESSION['ai_chat'] = [];
             }
             
             // giữ 10 tin nhắn cuối để làm ngữ cảnh
             $history = array_slice($_SESSION['ai_chat'], -10);

             // lấy danh sách danh mục để ai map chính xác (ví dụ "ổ cứng" -> "hard-drive")
             $cats = database::ThucThiTraVe("SELECT name, code FROM category") ?? [];
             $catListStr = implode(", ", array_column($cats, 'code')); // truyền mã category hoặc tên tùy ý, ở đây mình truyền cả 2
             if (empty($catListStr)) {
                 $catNames = [];
                 foreach ($cats as $c) $catNames[] = $c['name'] . " (code: " . $c['code'] . ")";
                 $catListStr = implode(", ", $catNames);
             } else {
                 $catNames = [];
                 foreach ($cats as $c) $catNames[] = $c['name'] . " (hoặc: " . $c['code'] . ")";
                 $catListStr = implode(", ", $catNames);
             }

             // nhận diện ý định và trích xuất từ khoá
             $p1_result_json = GeminiAI::extractChatIntent($message, $history, $catListStr);
             $p1_result = json_decode($p1_result_json, true) ?: [];
             
             // ghi log để debug
             file_put_contents(ROOT_DIR . '/Back-end/cache/ai_debug.log', "Msg: $message\nResult: $p1_result_json\n\n", FILE_APPEND);
             
             $intent = $p1_result['intent'] ?? 'chat';
             $filters = $p1_result['filters'] ?? [];
             
             // trả về nhanh cho chat thường (khỏi cần truy vấn db phức tạp)
             if ($intent === 'chat') {
                 $answer = $p1_result['answer'] ?? 'Xin lỗi, tôi không hiểu ý bạn.';
                 $_SESSION['ai_chat'][] = ['role' => 'user', 'content' => $message];
                 $_SESSION['ai_chat'][] = ['role' => 'system', 'content' => $answer, 'products' => []];
                 echo json_encode(['status' => 'success', 'answer' => $answer, 'suggested_products' => []], JSON_UNESCAPED_UNICODE);
                 return;
             }
             
             $foundProducts = [];
             if ($intent === 'buy_hardware') {
                 $foundProducts = $this->advancedSearch($filters, 10);
             }

             // tạo câu trả lời cuối cùng với dữ liệu db hoặc build pc
             $p2_result_json = GeminiAI::generateChatAnswer($message, $foundProducts, $history, $intent, $filters);
             $p2_result = json_decode($p2_result_json, true);

             if (isset($p2_result['error'])) {
                 echo $p2_result_json;
                 return;
             }
             
             // nếu build pc thì hiện mô tả dạng chat + các thẻ sản phẩm riêng
             if ($intent === 'build_pc' && !empty($p2_result['configs'])) {
                 $allSkus = [];
                 foreach ($p2_result['configs'] as $config) {
                     if (!empty($config['skus'])) $allSkus = array_merge($allSkus, $config['skus']);
                 }
                 $allSkus = array_unique($allSkus);
                 $components = $this->getProductsBySkus($allSkus);
                 
                 $productMap = [];
                 foreach ($components as $p) $productMap[$p['sku']] = $p;

             // phần 1: trả lời bằng văn bản (chỉ giới thiệu)
                 $answerHtml = '<div style="font-size:14px; line-height:1.6;">' . ($p2_result['answer'] ?? 'Dạ em gửi bạn các cấu hình phù hợp nhé!') . '</div>';
                 $p2_result['answer'] = $answerHtml;

             // phần 2: từng cấu hình với tiêu đề, mô tả và sản phẩm
                 $configsWithProducts = [];
                 foreach ($p2_result['configs'] as $config) {
                     $configSkusData = [];
                     $actualTotal = 0;
                     foreach (($config['skus'] ?? []) as $sku) {
                         if (isset($productMap[$sku])) {
                             $p = $productMap[$sku];
                             $price = ($p['sale_price'] > 0) ? $p['sale_price'] : $p['price'];
                             $actualTotal += $price;
                             $configSkusData[] = $p;
                         }
                     }
                     $configsWithProducts[] = [
                         'title'       => $config['title'] ?? '',
                         'description' => $config['description'] ?? '',
                         'total'       => $actualTotal,
                         'products'    => $configSkusData,
                     ];
                 }
                 $foundProducts = []; // không dùng slider mặc định
                 $p2_result['configs'] = $configsWithProducts;
             } elseif ($intent === 'build_pc' && !empty($p2_result['skus'])) {
                 $foundProducts = $this->getProductsBySkus($p2_result['skus']);
             }
             
             // lưu vào session
             $_SESSION['ai_chat'][] = ['role' => 'user', 'content' => $message];
             $_SESSION['ai_chat'][] = [
                 'role' => 'system', 
                 'content' => $p2_result['answer'] ?? 'Xin lỗi, tôi không thể xử lý.',
                 'products' => $foundProducts,
                 'pc_configs' => $p2_result['configs'] ?? []
             ];

             echo json_encode([
                 'status' => 'success',
                 'answer' => $p2_result['answer'] ?? '',
                 'suggested_products' => $foundProducts,
                 'pc_configs' => $p2_result['configs'] ?? []
             ], JSON_UNESCAPED_UNICODE);
        }

        private function getProductsBySkus($skus) {
            if (empty($skus)) return [];
            $inQuery = [];
            $params = [];
            foreach ($skus as $i => $sku) {
                $inQuery[] = ":sku$i";
                $params["sku$i"] = $sku;
            }
            $inSql = implode(',', $inQuery);
            $sql = "SELECT cate.code as category_code,
                    cate.name as category_name,
                    concat(pr.name,' - ',pr_vr.variant_name) as name,
                    b.brand_name,
                    pr_vr.price,
                    pr_vr.sale_price,
                    pr_vr.main_image_url,
                    pr_vr.sku
                    FROM products pr
                    JOIN product_variants pr_vr on pr.id=pr_vr.product_id
                    JOIN category cate on cate.id=pr.category_id
                    LEFT JOIN brand b on pr.brand_id=b.id
                    WHERE pr_vr.sku IN ($inSql)";
            return database::ThucThiTraVe($sql, $params) ?? [];
        }

        private function advancedSearch($filters, $limit = 8) {
            $conditions = [];
            $params = [];
            
            // gộp từ khoá và danh mục vào một nhóm để tìm kiếm
            $searchTerms = [];
            if (!empty($filters['keywords'])) {
                $searchTerms = array_merge($searchTerms, $filters['keywords']);
            }
            if (!empty($filters['category'])) {
                $searchTerms[] = $filters['category'];
            }
            
            if (!empty($searchTerms)) {
                $kwConditions = [];
                foreach ($searchTerms as $idx => $kw) {
                    // tìm từ khoá này trong tên, tên bản và tên danh mục
                    $kwConditions[] = "(pr.name LIKE :kw$idx OR pr_vr.variant_name LIKE :kw$idx OR cate.name LIKE :kw$idx OR cate.code LIKE :kw$idx)";
                    $params["kw$idx"] = "%$kw%";
                }
                // dùng and để cả "ram" và "16gb" đều phải khớp mới hiện
                $conditions[] = "(" . implode(' AND ', $kwConditions) . ")";
            }

            // giá
            if (!empty($filters['min_price'])) {
                $conditions[] = "IF(pr_vr.sale_price > 0, pr_vr.sale_price, pr_vr.price) >= :min_price";
                $params['min_price'] = $filters['min_price'];
            }
            if (!empty($filters['max_price'])) {
                $conditions[] = "IF(pr_vr.sale_price > 0, pr_vr.sale_price, pr_vr.price) <= :max_price";
                $params['max_price'] = $filters['max_price'];
            }

            $whereSql = empty($conditions) ? "1=1" : implode(' AND ', $conditions);
            
            $sql = "SELECT cate.code as category_code,
                    cate.name as category_name,
                    concat(pr.name,' - ',pr_vr.variant_name) as name,
                    b.brand_name,
                    pr_vr.price,
                    pr_vr.sale_price,
                    pr_vr.main_image_url,
                    pr_vr.sku
                    FROM products pr
                    JOIN product_variants pr_vr on pr.id=pr_vr.product_id
                    JOIN category cate on cate.id=pr.category_id
                    LEFT JOIN brand b on pr.brand_id=b.id
                    WHERE $whereSql
                    ORDER BY pr.id DESC
                    LIMIT " . (int)$limit;
            
            $results = database::ThucThiTraVe($sql, $params) ?? [];
            file_put_contents(ROOT_DIR . '/Back-end/cache/ai_debug.log', "SQL: $sql\nParams: " . json_encode($params) . "\nFound: " . count($results) . "\n\n", FILE_APPEND);
            return $results;
        }

    }
?>