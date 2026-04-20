<?php
class dashboard
{

    private function getDateRange()
    {
        $start = $_GET['start'] ?? date('Y-m-d', strtotime('-30 days'));
        $end = $_GET['end'] ?? date('Y-m-d');
        // đảm bảo ngày bao gồm cả khung giờ đầy đủ
        $start .= ' 00:00:00';
        $end .= ' 23:59:59';
        return [$start, $end];
    }

    public function getStats()
    {
        list($start, $end) = $this->getDateRange();

        $stats = database::ThucThiTraVe("
            SELECT 
                COUNT(*) as total_orders,
                SUM(total_price) as total_revenue,
                SUM(CASE WHEN `status` = 'completed' THEN 1 ELSE 0 END) as completed_orders,
                SUM(CASE WHEN `status` = 'pending' THEN 1 ELSE 0 END) as pending_orders
            FROM orders
            WHERE created_at BETWEEN :start AND :end
              AND `status` NOT IN ('cancelled', 'returned', 'failed')
        ", ['start' => $start, 'end' => $end]);

        $data = $stats[0] ?? ['total_orders' => 0, 'total_revenue' => 0, 'pending_orders' => 0, 'completed_orders' => 0];
        $total_orders = $data['total_orders'] ?: 0;
        $total_revenue = $data['total_revenue'] ?: 0;
        $pending_orders = $data['pending_orders'] ?: 0;
        $completed_orders = $data['completed_orders'] ?: 0;
        $aov = $total_orders > 0 ? round((float) $total_revenue / (float) $total_orders) : 0;

        echo json_encode([
            'status' => 'success',
            'data' => [
                'total_revenue' => (float) $total_revenue,
                'total_orders' => (int) $total_orders,
                'pending_orders' => (int) $pending_orders,
                'completed_orders' => (int) $completed_orders,
                'aov' => $aov
            ]
        ]);
    }

    public function getChartData()
    {
        list($start, $end) = $this->getDateRange();

        $data = database::ThucThiTraVe("
            SELECT DATE(created_at) as date, SUM(total_price) as revenue
            FROM orders
            WHERE created_at BETWEEN :start AND :end
              AND `status` NOT IN ('cancelled', 'returned', 'failed')
            GROUP BY DATE(created_at)
            ORDER BY date ASC
        ", ['start' => $start, 'end' => $end]);

        echo json_encode(['status' => 'success', 'data' => $data]);
    }

    public function getInventoryInsights()
    {
        // các mẫu sắp hết hàng
        $low_stock = database::ThucThiTraVe("
            SELECT pv.id, pv.sku, pv.variant_name as name, p.name as product_name, pv.stock, pv.min_stock
            FROM product_variants pv
            JOIN products p ON pv.product_id = p.id
            WHERE pv.stock <= pv.min_stock OR pv.stock = 0
            ORDER BY pv.stock ASC
            LIMIT 20
        ");

        // giả lập dữ liệu ai bằng số thật
        $insights = [];

        // lấy top sản phẩm bán chạy trong 14 ngày qua so với tồn kho
        $fast_selling = database::ThucThiTraVe("
            SELECT pv.id, pv.sku, pv.variant_name as name, pv.stock, SUM(od.quantity) as sold_14d
            FROM order_detail od
            JOIN orders o ON od.order_id = o.id
            JOIN product_variants pv ON od.sku = pv.sku
            WHERE o.created_at >= DATE_SUB(NOW(), INTERVAL 14 DAY)
            GROUP BY pv.id
            HAVING sold_14d > 0
            ORDER BY sold_14d DESC
            LIMIT 5
        ");

        if (!empty($fast_selling)) {
            foreach ($fast_selling as $item) {
                $sold = (int) $item['sold_14d'];
                $stock = (int) $item['stock'];
                if ($sold > 0) {
                    // trung bình mỗi ngày
                    $sold_per_day = $sold / 14;
                    if ($sold_per_day > 0) {
                        $days_left = ceil($stock / $sold_per_day);
                        if ($days_left <= 7 && $days_left > 0) {
                            $insights[] = "Mã SKU <strong>{$item['sku']}</strong> đang bán rất nhanh ({$sold} chiếc/14 ngày). Tồn kho ({$stock}) dự kiến cạn kiệt sau {$days_left} ngày. Cần nhập gấp!";
                        } else if ($days_left == 0) {
                            $insights[] = "Mã SKU <strong>{$item['sku']}</strong> rất hot nhưng đã HẾT HÀNG. Bạn đang bỏ lỡ doanh thu tiềm năng!";
                        }
                    }
                }
            }
        }

        if (empty($insights)) {
            $insights[] = "Tốc độ bán hàng ổn định, chưa phát hiện biến động bất thường dẫn đến đứt hàng ngắn hạn.";
        }

        echo json_encode([
            'status' => 'success',
            'data' => [
                'low_stock' => $low_stock ?? [],
                'ai_insights' => $insights
            ]
        ]);
    }

    public function getOrders()
    {
        $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 10;
        $offset = ($page - 1) * $limit;
        $statusFilter = $_GET['status'] ?? 'pending';

        $validStatuses = ['pending', 'shipping', 'delivering', 'completed', 'cancelled', 'returned', 'failed', 'returning'];
        if (!in_array($statusFilter, $validStatuses)) {
            $statusFilter = 'pending';
        }

        $sql = "
            SELECT id, order_code as code, receiver_name as customer, total_price as total, payment_method, payment_status, `status`, created_at 
            FROM orders
            WHERE `status` = :status
            ORDER BY created_at ASC
            LIMIT $offset, $limit
        ";

        $orders = database::ThucThiTraVe($sql, ['status' => $statusFilter]);

        echo json_encode([
            'status' => 'success',
            'data' => $orders ?? [],
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'has_more' => count($orders ?? []) === $limit
            ]
        ]);
    }

    public function getAllOrders()
    {
        $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 12;
        $offset = ($page - 1) * $limit;
        $search = $_GET['search'] ?? '';

        $sql = "SELECT id, order_code as code, receiver_name as customer, receiver_phone, total_price as total, payment_method, payment_status, `status`, created_at, cancel_reason FROM orders";

        $params = [];
        $conditions = [];
        if (!empty($search)) {
            $conditions[] = "(order_code LIKE :search OR receiver_name LIKE :search OR receiver_phone LIKE :search)";
            $params['search'] = "%$search%";
        }
        $statusFilter = $_GET['status'] ?? '';
        if (!empty($statusFilter) && $statusFilter !== 'all') {
            $conditions[] = "`status` = :status";
            $params['status'] = $statusFilter;
        }
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }

        $sql .= " ORDER BY created_at ASC LIMIT $offset, $limit";

        $orders = database::ThucThiTraVe($sql, $params);

        // đếm tổng số đơn để phân trang chuẩn
        $countSql = "SELECT COUNT(id) as total FROM orders";
        if (!empty($conditions)) {
            $countSql .= " WHERE " . implode(' AND ', $conditions);
        }
        $countResult = database::ThucThiTraVe($countSql, $params);
        $totalItems = $countResult[0]['total'] ?? 0;
        $totalPages = ceil($totalItems / $limit);

        echo json_encode([
            'status' => 'success',
            'data' => $orders ?? [],
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total_items' => $totalItems,
                'total_pages' => $totalPages,
                'has_more' => $page < $totalPages
            ]
        ]);
    }

    public function getOrderDetail()
    {
        $code = $_GET['code'] ?? null;
        if (!$code) {
            echo json_encode(['status' => 'error', 'message' => 'Thiếu mã đơn hàng']);
            return;
        }

        $orderRes = database::ThucThiTraVe("SELECT * FROM orders WHERE order_code = :code", ['code' => $code]);
        if (empty($orderRes)) {
            echo json_encode(['status' => 'error', 'message' => 'Không tìm thấy đơn hàng']);
            return;
        }
        $order = $orderRes[0];

        $items = database::ThucThiTraVe("
            SELECT od.quantity, od.unit_price as price, pv.sku, p.name as product_name, pv.variant_name as variant_name, pv.main_image_url
            FROM order_detail od
            JOIN product_variants pv ON od.sku = pv.sku
            JOIN products p ON pv.product_id = p.id
            WHERE od.order_id = :id
        ", ['id' => $order['id']]);

        $order['items'] = $items ?? [];

        echo json_encode(['status' => 'success', 'data' => $order]);
    }

    public function updateOrderStatus()
    {
        $raw = file_get_contents("php:// input");
        $data = json_decode($raw, true);

        $code = $data['order_code'] ?? null;
        $newStatus = $data['status'] ?? null;
        $cancelReason = $data['cancel_reason'] ?? null;

        if (!$code || !$newStatus) {
            echo json_encode(['status' => 'error', 'message' => 'Dữ liệu không hợp lệ']);
            return;
        }

        $validFlow = [
            'pending' => ['shipping', 'cancelled'],
            'shipping' => ['delivering', 'failed'],
            'delivering' => ['returned', 'failed'], // bỏ 'completed' vì user phải tự bấm hoàn thành
            'returning' => ['returned'],
            'failed' => ['returned'] // cho phép từ giao thất bại -> nhập lại kho (returned)
        ];

        $orderRes = database::ThucThiTraVe("SELECT id, status FROM orders WHERE order_code = :code", ['code' => $code]);
        if (empty($orderRes)) {
            echo json_encode(['status' => 'error', 'message' => 'Không tìm thấy đơn hàng']);
            return;
        }

        $currentStatus = $orderRes[0]['status'];
        $orderId = $orderRes[0]['id'];

        if (!isset($validFlow[$currentStatus]) || !in_array($newStatus, $validFlow[$currentStatus])) {
            echo json_encode(['status' => 'error', 'message' => "Không thể chuyển từ $currentStatus sang $newStatus"]);
            return;
        }

        try {
            database::beginTransaction();

            $updateFields = "status = :status";
            $params = [
                'status' => $newStatus,
                'code' => $code
            ];

            if ($newStatus === 'cancelled' && $cancelReason) {
                $updateFields .= ", cancel_reason = :cancel_reason";
                $params['cancel_reason'] = $cancelReason;
            }

            database::ThucThi("UPDATE orders SET $updateFields WHERE order_code = :code", $params);

            // trường hợp 1: hủy trước khi xuất kho (auto-restock 100%)
            if ($newStatus === 'cancelled') {
                $items = database::ThucThiTraVe("SELECT sku, quantity FROM order_detail WHERE order_id = :order_id", ['order_id' => $orderId]);
                foreach ($items as $item) {
                    database::ThucThi("UPDATE product_variants SET stock = stock + :qty WHERE sku = :sku", [
                        'qty' => $item['quantity'],
                        'sku' => $item['sku']
                    ]);
                }
            }

            database::commit();
        } catch (Exception $e) {
            database::rollBack();
            echo json_encode(['status' => 'error', 'message' => 'Lỗi cập nhật đơn hàng: ' . $e->getMessage()]);
            return;
        }

        // gửi thông báo push
        require_once ROOT_DIR . "/Back-end/modules/firebase_helper.php";
        $userQuery = database::ThucThiTraVe("
            SELECT u.fcm_token, o.user_id 
            FROM orders o 
            JOIN user u ON o.user_id = u.id 
            WHERE o.order_code = :code",
            ['code' => $code]
        );

        if (!empty($userQuery) && !empty($userQuery[0]['fcm_token'])) {
            $fcm = new FirebaseHelper();

            $statusLabels = [
                'shipping' => 'Vận chuyển',
                'delivering' => 'Đang giao hàng',
                'completed' => 'Hoàn thành',
                'cancelled' => 'Đã hủy',
                'returned' => 'Đã hoàn trả',
                'failed' => 'Giao hàng thất bại'
            ];
            $label = $statusLabels[$newStatus] ?? $newStatus;
            $title = "Đơn hàng #$code cập nhật";
            $msg = "Đơn hàng của bạn đã chuyển sang trạng thái: " . $label;

            $fcm->sendNotification(
                $userQuery[0]['fcm_token'],
                $title,
                $msg
            );

            // lưu vào db để hiện chuông thông báo trong app
            try {
                database::ThucThi("CREATE TABLE IF NOT EXISTS notification (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT,
                    title VARCHAR(100),
                    message TEXT,
                    is_read TINYINT DEFAULT 0,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )");

                database::ThucThi("INSERT INTO notification (user_id, title, message) VALUES (:uid, :title, :msg)", [
                    'uid' => $userQuery[0]['user_id'],
                    'title' => $title,
                    'msg' => $msg
                ]);
            } catch (Exception $e) {
            }
        }

        if ($newStatus === 'completed') {
            $orderDetail = database::ThucThiTraVe("SELECT payment_method FROM orders WHERE order_code = :code", ['code' => $code]);
            if (!empty($orderDetail) && $orderDetail[0]['payment_method'] === 'cod') {
                database::ThucThi("UPDATE orders SET payment_status = 'paid' WHERE order_code = :code", ['code' => $code]);
            }
        } else if ($newStatus === 'returned') {
            try {
                database::beginTransaction();
                // cập nhật trạng thái thanh toán thành đã hoàn tiền
                database::ThucThi("UPDATE orders SET payment_status = 'refunded' WHERE order_code = :code", ['code' => $code]);

                // nhập lại kho: lấy các món trong đơn
                $items = database::ThucThiTraVe("SELECT sku, quantity FROM order_detail WHERE order_id = :order_id", ['order_id' => $orderId]);
                foreach ($items as $item) {
                    database::ThucThi("UPDATE product_variants SET stock = stock + :qty WHERE sku = :sku", [
                        'qty' => $item['quantity'],
                        'sku' => $item['sku']
                    ]);
                }
                database::commit();
                echo json_encode(['status' => 'success', 'message' => 'Xác nhận trả hàng và Nhập lại kho +' . count($items) . ' món.']);
            } catch (Exception $e) {
                database::rollBack();
                echo json_encode(['status' => 'error', 'message' => 'Lỗi nhập lại kho: ' . $e->getMessage()]);
            }
            return; // early exit
        }

        echo json_encode(['status' => 'success', 'message' => 'Cập nhật trạng thái thành công']);
    }
}
?>