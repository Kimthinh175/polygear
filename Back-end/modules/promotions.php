<?php
if (!defined('SECURE_API_ACCESS')) {
    http_response_code(403);
    header("Location: /home");
    exit();
}

class promotions
{
    /**
     * Lazy Sync: Tự động dọn dẹp các chiến dịch hết hạn
     */
    public static function autoSync()
    {
        $lockFile = ROOT_DIR . '/Back-end/cache/promo_sync.lock';
        $now = time();

        // chỉ kiểm tra nếu file lock không tồn tại hoặc đã quá 60 giây kể từ lần check cuối
        if (!file_exists($lockFile) || ($now - filemtime($lockFile) > 60)) {
            // cập nhật lại thời gian sửa đổi file lock ngay lập tức để các request khác không nhảy vào nữa
            @touch($lockFile); 

            try {
                // câu lệnh select cực nhẹ để check
                $expired = database::ThucThiTraVe("SELECT id FROM promotions WHERE status = 'active' AND end_time < NOW()");
                if (!empty($expired)) {
                    $instance = new self();
                    foreach ($expired as $p) {
                        $instance->restorePromoPrices($p['id']);
                        database::ThucThi("UPDATE promotions SET status = 'inactive' WHERE id = :id", ['id' => $p['id']]);
                    }
                }
            } catch (Exception $e) {
                // ignore errors to not block request
            }
        }
    }

    public function applyPromoPrices($promoId)
    {
        $promoQuery = database::ThucThiTraVe("SELECT * FROM promotions WHERE id = :id", ['id' => $promoId]);
        if (empty($promoQuery)) return;
        $promo = $promoQuery[0];

        $items = database::ThucThiTraVe("SELECT sku FROM promotion_items WHERE promotion_id = :id", ['id' => $promoId]);

        foreach ($items as $item) {
            $sku = $item['sku'];
            $variantQuery = database::ThucThiTraVe("SELECT price, sale_price FROM product_variants WHERE sku = :sku", ['sku' => $sku]);
            if (empty($variantQuery)) continue;
            $variant = $variantQuery[0];
            
            $basePrice = (!empty($variant['sale_price']) && $variant['sale_price'] > 0) ? $variant['sale_price'] : $variant['price'];
            $newPrice = $basePrice;

            if ($promo['discount_percent']) {
                $newPrice = $basePrice * (1 - $promo['discount_percent'] / 100);
            } else if ($promo['discount_amount']) {
                $newPrice = max(0, $basePrice - $promo['discount_amount']);
            }

            // lưu giá cũ và cập nhật giá mới
            database::ThucThi("UPDATE promotion_items SET original_sale_price = :old WHERE promotion_id = :pid AND sku = :sku", [
                'old' => $variant['sale_price'],
                'pid' => $promoId,
                'sku' => $sku
            ]);
            database::ThucThi("UPDATE product_variants SET sale_price = :new WHERE sku = :sku", [
                'new' => $newPrice,
                'sku' => $sku
            ]);
        }
    }

    public function restorePromoPrices($promoId)
    {
        $items = database::ThucThiTraVe("SELECT sku, original_sale_price FROM promotion_items WHERE promotion_id = :id", ['id' => $promoId]);
        foreach ($items as $item) {
            database::ThucThi("UPDATE product_variants SET sale_price = :old WHERE sku = :sku", [
                'old' => $item['original_sale_price'],
                'sku' => $item['sku']
            ]);
        }
    }

    // lấy danh sách khuyến mãi cho trang chủ
    public function getHomePromotions()
    {
        try {
            // lấy tối đa 3 chiến dịch đang active và trong thời gian hiệu lực
            $activePromos = database::ThucThiTraVe("
                SELECT id, name, discount_percent, discount_amount, start_time, end_time
                FROM promotions
                WHERE status = 'active'
                  AND NOW() BETWEEN start_time AND end_time
                ORDER BY id DESC
                LIMIT 3
            ");

            if (empty($activePromos)) {
                echo json_encode(["status" => "success", "data" => []]);
                return;
            }

            foreach ($activePromos as &$promo) {
                $promoId = $promo['id'];
                $products = database::ThucThiTraVe("
                    SELECT cate.code as category_code,
                           cate.name as category_name,
                           concat(pr.name,' - ',pr_vr.variant_name) as name,
                           b.brand_name,
                           pr_vr.price,
                           pr_vr.sale_price,
                           pr_vr.main_image_url,
                           pr_vr.sku,
                           pi.original_sale_price
                    FROM promotion_items pi
                    JOIN product_variants pr_vr ON pi.sku = pr_vr.sku
                    JOIN products pr ON pr.id = pr_vr.product_id
                    JOIN category cate ON cate.id = pr.category_id
                    LEFT JOIN brand b ON pr.brand_id = b.id
                    WHERE pi.promotion_id = :id
                ", ['id' => $promoId]);

                foreach ($products as &$p) {
                    $p['is_promo'] = true;
                    // lấy giá gốc để gạch ngang
                    if (!empty($p['original_sale_price']) && $p['original_sale_price'] > 0) {
                        $p['origin_price_display'] = $p['original_sale_price'];
                    } else {
                        $p['origin_price_display'] = $p['price'];
                    }
                }
                $promo['products'] = $products;
            }

            echo json_encode(["status" => "success", "data" => $activePromos]);
        } catch (\Throwable $e) {
            echo json_encode(["status" => "success", "data" => []]);
        }
    }

    // phần dành cho admin
    
    public function getAdminPromotions()
    {
        try {
            $promos = database::ThucThiTraVe("SELECT * FROM promotions ORDER BY id DESC");
            
            foreach($promos as &$p){
                $now = new DateTime();
                $start = new DateTime($p['start_time']);
                $end = new DateTime($p['end_time']);
                
                if ($p['status'] === 'inactive') {
                    $p['time_status'] = 'Đã tắt';
                } else if ($now < $start) {
                    $p['time_status'] = 'Sắp diễn ra';
                } else if ($now > $end) {
                    $p['time_status'] = 'Đã kết thúc';
                } else {
                    $p['time_status'] = 'Đang diễn ra';
                }

                $count = database::ThucThiTraVe("SELECT COUNT(sku) as c FROM promotion_items WHERE promotion_id = :id", ['id'=>$p['id']]);
                $p['items_count'] = $count[0]['c'];
            }

            echo json_encode(["status" => "success", "data" => $promos]);
        } catch (\Throwable $e) {
            echo json_encode(["status" => "error", "message" => "Lỗi: Chưa có bảng promotions trong DB hoặc lỗi kết nối. Hãy chạy file SQL."]);
        }
    }

    public function getPromotionDetail()
    {
        try {
            $id = $_GET['id'] ?? null;
            if (!$id) die(json_encode(["status" => "error", "message" => "Thiếu ID"]));

            $promo = database::ThucThiTraVe("SELECT * FROM promotions WHERE id = :id", ['id' => $id]);
            if (empty($promo)) {
                die(json_encode(["status" => "error", "message" => "Không tìm thấy chương trình"]));
            }

            $items = database::ThucThiTraVe("
                SELECT pi.sku, concat(pr.name,' - ',pr_vr.variant_name) as name, pr_vr.main_image_url
                FROM promotion_items pi
                JOIN product_variants pr_vr ON pi.sku = pr_vr.sku
                JOIN products pr ON pr.id = pr_vr.product_id
                WHERE pi.promotion_id = :id
            ", ['id' => $id]);

            $promo[0]['items'] = $items;
            echo json_encode(["status" => "success", "data" => $promo[0]]);
        } catch (\Throwable $e) {
            echo json_encode(["status" => "error", "message" => "Lỗi database: " . $e->getMessage()]);
        }
    }

    public function createPromotion()
    {
        try {
            $input = json_decode(file_get_contents('php:// input'), true) ?? $_post;
            
            $name = trim($input['name'] ?? '');
            $discount_percent = isset($input['discount_percent']) && $input['discount_percent'] !== '' ? (int)$input['discount_percent'] : null;
            $discount_amount = isset($input['discount_amount']) && $input['discount_amount'] !== '' ? (int)$input['discount_amount'] : null;
            $start_time = $input['start_time'] ?? null;
            $end_time = $input['end_time'] ?? null;
            $status = $input['status'] ?? 'active';

            if (!$name || !$start_time || !$end_time) {
                die(json_encode(["status" => "error", "message" => "Thiếu thông tin bắt buộc"]));
            }

            if ($discount_percent !== null && $discount_percent > 10) {
                die(json_encode(["status" => "error", "message" => "Khuyến mãi theo phần trăm tối đa là 10%."]));
            }
            if ($discount_amount !== null && $discount_amount > 300000) {
                die(json_encode(["status" => "error", "message" => "Khuyến mãi theo số tiền tối đa là 300.000 VND."]));
            }

            if ($status === 'active') {
                $activeCount = database::ThucThiTraVe("SELECT COUNT(*) as c FROM promotions WHERE status = 'active' AND NOW() < end_time");
                if ($activeCount[0]['c'] >= 3) {
                    die(json_encode(["status" => "error", "message" => "Chỉ được phép tối đa 3 chiến dịch hoạt động!"]));
                }
            }

            database::ThucThi("
                INSERT INTO promotions (name, discount_percent, discount_amount, start_time, end_time, status)
                VALUES (:name, :percent, :amount, :start_time, :end_time, :status)
            ", [
                'name' => $name,
                'percent' => $discount_percent,
                'amount' => $discount_amount,
                'start_time' => $start_time,
                'end_time' => $end_time,
                'status' => $status
            ]);

            echo json_encode(["status" => "success", "message" => "Đã tạo chương trình"]);
        } catch (\Throwable $e) {
            echo json_encode(["status" => "error", "message" => "Lỗi database: " . $e->getMessage()]);
        }
    }

    public function updatePromotion()
    {
        try {
            $input = json_decode(file_get_contents('php:// input'), true) ?? $_post;
            $id = $input['id'] ?? null;

            if (!$id) die(json_encode(["status" => "error", "message" => "Thiếu ID"]));

            $name = trim($input['name'] ?? '');
            $discount_percent = isset($input['discount_percent']) && $input['discount_percent'] !== '' ? (int)$input['discount_percent'] : null;
            $discount_amount = isset($input['discount_amount']) && $input['discount_amount'] !== '' ? (int)$input['discount_amount'] : null;
            $start_time = $input['start_time'] ?? null;
            $end_time = $input['end_time'] ?? null;

            if ($discount_percent !== null && $discount_percent > 10) {
                die(json_encode(["status" => "error", "message" => "Khuyến mãi theo phần trăm tối đa là 10%."]));
            }
            if ($discount_amount !== null && $discount_amount > 300000) {
                die(json_encode(["status" => "error", "message" => "Khuyến mãi theo số tiền tối đa là 300.000 VND."]));
            }

            database::ThucThi("
                UPDATE promotions 
                SET name=:name, discount_percent=:percent, discount_amount=:amount, start_time=:start_time, end_time=:end_time
                WHERE id=:id
            ", [
                'id' => $id,
                'name' => $name,
                'percent' => $discount_percent,
                'amount' => $discount_amount,
                'start_time' => $start_time,
                'end_time' => $end_time
            ]);
            
            $promoQuery = database::ThucThiTraVe("SELECT status FROM promotions WHERE id = :id", ['id' => $id]);
            if (!empty($promoQuery) && $promoQuery[0]['status'] === 'active') {
                $this->applyPromoPrices($id);
            }

            echo json_encode(["status" => "success", "message" => "Đã cập nhật"]);
        } catch (\Throwable $e) {
            echo json_encode(["status" => "error", "message" => "Lỗi database: " . $e->getMessage()]);
        }
    }

    public function updatePromotionStatus()
    {
        try {
            $input = json_decode(file_get_contents('php:// input'), true) ?? $_post;
            $id = $input['id'] ?? null;
            $status = $input['status'] ?? null;

            if (!$id || !$status) die(json_encode(["status" => "error", "message" => "Thiếu dữ liệu"]));

            if ($status === 'active') {
                $activeCount = database::ThucThiTraVe("SELECT COUNT(*) as c FROM promotions WHERE status = 'active' AND NOW() < end_time AND id != :id", ['id'=>$id]);
                if ($activeCount[0]['c'] >= 3) {
                    die(json_encode(["status" => "error", "message" => "Chỉ được phép tối đa 3 chiến dịch hoạt động! Bạn phải tắt bot chiến dịch khác trước."]));
                }
            }

            database::ThucThi("UPDATE promotions SET status = :status WHERE id = :id", [
                'status' => $status,
                'id' => $id
            ]);

            if ($status === 'active') {
                $this->applyPromoPrices($id);
            } else {
                $this->restorePromoPrices($id);
            }

            echo json_encode(["status" => "success", "message" => "Đổi trạng thái thành công"]);
        } catch (\Throwable $e) {
            echo json_encode(["status" => "error", "message" => "Lỗi database: " . $e->getMessage()]);
        }
    }

    public function deletePromotion()
    {
        try {
            $input = json_decode(file_get_contents('php:// input'), true) ?? $_get;
            $id = $input['id'] ?? null;

            if (!$id) die(json_encode(["status" => "error", "message" => "Thiếu ID"]));
            
            $promoQuery = database::ThucThiTraVe("SELECT status FROM promotions WHERE id = :id", ['id' => $id]);
            if (!empty($promoQuery) && $promoQuery[0]['status'] === 'active') {
                $this->restorePromoPrices($id);
            }

            database::ThucThi("DELETE FROM promotions WHERE id = :id", ['id' => $id]);
            echo json_encode(["status" => "success", "message" => "Xóa thành công"]);
        } catch (\Throwable $e) {
            echo json_encode(["status" => "error", "message" => "Lỗi database: " . $e->getMessage()]);
        }
    }

    public function updatePromotionItems()
    {
        $input = json_decode(file_get_contents('php:// input'), true);
        $promoId = $input['promotion_id'] ?? null;
        $skus = $input['skus'] ?? []; // mảng sku thay thế toàn bộ danh sách cũ của mã này

        if (!$promoId) die(json_encode(["status" => "error", "message" => "Thiếu mã KM"]));

        // kiểm tra xem những sku này có nằm trong 1 chiến dịch đang active nào khác không
        if (!empty($skus)) {
            $placeholders = [];
            $params = ['currentPromoId' => $promoId];
            
            foreach ($skus as $index => $sku) {
                $key = "sku" . $index;
                $placeholders[] = ":" . $key;
                $params[$key] = $sku;
            }
            
            $placeholdersStr = implode(',', $placeholders);
            
            $sql = "
                SELECT pi.sku, p.name 
                FROM promotion_items pi
                JOIN promotions p ON pi.promotion_id = p.id
                WHERE p.status = 'active'
                  AND p.id != :currentPromoId
                  AND pi.sku IN ($placeholdersStr)
            ";
            $db = database::ThucThiTraVe($sql, $params);
            
            if (!empty($db)) {
                $dup = array_column($db, 'sku');
                die(json_encode([
                    "status" => "error", 
                    "message" => "Các SKU sau đã thuộc chiến dịch khác đang Active: " . implode(", ", $dup) . ". Hãy bỏ chọn chúng."
                ]));
            }
        }

        try {
            database::beginTransaction();
            
            $promoQuery = database::ThucThiTraVe("SELECT status FROM promotions WHERE id = :id", ['id' => $promoId]);
            $isActive = (!empty($promoQuery) && $promoQuery[0]['status'] === 'active');
            
            if ($isActive) {
                $this->restorePromoPrices($promoId);
            }

            database::ThucThi("DELETE FROM promotion_items WHERE promotion_id = :id", ['id' => $promoId]);

            foreach ($skus as $sku) {
                database::ThucThi("INSERT INTO promotion_items (promotion_id, sku) VALUES (:id, :sku)", [
                    'id' => $promoId,
                    'sku' => $sku
                ]);
            }
            
            if ($isActive) {
                $this->applyPromoPrices($promoId);
            }

            database::commit();
            echo json_encode(["status" => "success", "message" => "Đã lưu danh sách sản phẩm"]);
        } catch (Exception $e) {
            database::rollBack();
            echo json_encode(["status" => "error", "message" => "Lỗi: " . $e->getMessage()]);
        }
    }
}
?>
