<?php
if (!defined('SECURE_API_ACCESS')) {
    http_response_code(403);
    header("Location: /home");
    exit();
}
use Firebase\JWT\JWT;
class account
{

    function getInfo($param)
    {
        if (!isset($_SESSION['user']['id'])) {
            echo json_encode([]);
            return;
        }

        $userId = $_SESSION['user']['id'];

        $data = database::ThucThiTraVe(
            "SELECT * FROM user 
                WHERE id = :id AND delete_at IS NULL",
            ['id' => $userId]
        )[0];

        $address = database::ThucThiTraVe(
            "SELECT * FROM shipping_address 
                WHERE user_id = :id",
            ['id' => $userId]
        );
        $data['address'] = $address;

        echo json_encode([$data]);
    }

    function updateInfo()
    {
        if (!isset($_SESSION['user']['id'])) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Bạn chưa đăng nhập'
            ]);
            return;
        }

        $userId = $_SESSION['user']['id'];

        $userName = $_POST['user_name'] ?? '';
        $userEmail = $_POST['gmail'] ?? '';
        $userPhone = $_POST['phone_number'] ?? '';
        $userAddress = $_POST['user_address'] ?? '';

        try {
            // xử lý ảnh $_files
            $avatarUpdateSql = "";
            $avatarParam = [];
            $dbAvatarPath = "";

            if (isset($_FILES['avatar_file']) && $_FILES['avatar_file']['error'] === UPLOAD_ERR_OK) {
                $fileTmpPath = $_FILES['avatar_file']['tmp_name'];
                $fileNameOriginal = $_FILES['avatar_file']['name'];

                $fileExtension = strtolower(pathinfo($fileNameOriginal, PATHINFO_EXTENSION));
                $newFileName = 'user_' . $userId . '_' . time() . '.' . $fileExtension;
                $uploadPath = ROOT_DIR . '/Front-end/public/img/user/' . $newFileName;

                if (move_uploaded_file($fileTmpPath, $uploadPath)) {
                    $dbAvatarPath = 'img/user/' . $newFileName;
                    $avatarUpdateSql = ", avatar_url = :avatar";
                    $avatarParam = ['avatar' => $dbAvatarPath];
                }
            }

            // fix 1: cập nhật bảng user (sửa lại cột gmail và key :gmail cho khớp)
            $updateSql = "UPDATE user SET user_name = :name, gmail = :gmail, phone_number = :phone" . $avatarUpdateSql . " WHERE id = :id AND delete_at IS NULL";

            $params = [
                'name' => $userName,
                'gmail' => $userEmail, // đã đổi cho trùng với :gmail
                'phone' => $userPhone,
                'id' => $userId
            ];

            $finalParams = array_merge($params, $avatarParam);
            database::ThucThi($updateSql, $finalParams);

            // xử lý logic bảng shipping address
            if (!empty($userAddress)) {
                // phế truất địa chỉ mặc định cũ
                database::ThucThi("UPDATE shipping_address SET status = 0 WHERE user_id = :id", ['id' => $userId]);

                $checkExist = database::ThucThiTraVe(
                    "SELECT id FROM shipping_address WHERE user_id = :id AND address = :address",
                    ['id' => $userId, 'address' => $userAddress]
                );

                if (!empty($checkExist)) {
                    $addrId = $checkExist[0]['id'];
                    database::ThucThi("UPDATE shipping_address SET status = 1 WHERE id = :addr_id", ['addr_id' => $addrId]);
                } else {
                    // fix 2: bơm thêm tên và sđt vào nếu phải insert thêm địa chỉ mới
                    database::ThucThi(
                        "INSERT INTO shipping_address (user_id, receiver_name, receiver_phone, address, status) VALUES (:id, :name, :phone, :address, 1)",
                        [
                            'id' => $userId,
                            'name' => $userName,
                            'phone' => $userPhone,
                            'address' => $userAddress
                        ]
                    );
                }
            }

            // 
            // cập nhật lại session cho frontend
            // 
            $_SESSION['user']['name'] = $userName;
            $_SESSION['user']['phone'] = $userPhone;
            $_SESSION['user']['gmail'] = $userEmail;

            if (!empty($dbAvatarPath)) {
                $_SESSION['user']['avatar'] = $dbAvatarPath;
            }

            // trả về json thành công
            echo json_encode([
                'status' => 'success',
                'message' => 'Cập nhật thông tin thành công!'
            ]);

        } catch (Exception $e) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Lỗi hệ thống: ' . $e->getMessage()
            ]);
        }
    }

    function deleteAddress()
    {
        if (!isset($_SESSION['user']['id'])) {
            echo json_encode(['status' => 'error', 'message' => 'Bạn chưa đăng nhập']);
            return;
        }

        $userId = $_SESSION['user']['id'];
        $raw = file_get_contents("php:// input");
        $id = json_decode($raw, true)['id'];

        if ($id) {
            // kiểm tra xem địa chỉ có thuộc về user này không trước khi xóa
            database::ThucThi("DELETE FROM shipping_address WHERE id = :id AND user_id = :user_id", [
                'id' => $id,
                'user_id' => $userId
            ]);
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Lỗi ID']);
        }

    }

    function addAddress()
    {
        if (!isset($_SESSION['user']['id'])) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Bạn chưa đăng nhập'
            ]);
            return;
        }

        $userId = $_SESSION['user']['id'];
        $receiverName = trim($_POST['receiver_name'] ?? '');
        $receiverPhone = trim($_POST['receiver_phone'] ?? '');
        $address = trim($_POST['address'] ?? '');

        // 2. check validate cơ bản (kiểm tra luôn cả tên và sđt)
        if (empty($address) || empty($receiverName) || empty($receiverPhone)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Thiếu thông tin người nhận hoặc địa chỉ rỗng!'
            ]);
            return;
        }

        try {
            // phế truất địa chỉ mặc định cũ của user này
            database::ThucThi(
                "UPDATE shipping_address SET status=0 WHERE user_id=:id",
                ['id' => $userId]
            );

            // 3. insert địa chỉ mới vào db (bơm đầy đủ tên, sđt, địa chỉ và set làm mặc định = 1)
            database::ThucThi(
                "INSERT INTO shipping_address (user_id, receiver_name, receiver_phone, address, status) 
                    VALUES (:id, :name, :phone, :address, 1)",
                [
                    'id' => $userId,
                    'name' => $receiverName,
                    'phone' => $receiverPhone,
                    'address' => $address
                ]
            );

            // 4. lấy cái id của dòng vừa insert xong để trả về cho frontend làm nút "xóa"
            // (sắp xếp giảm dần lấy 1 dòng đầu tiên của user này = id mới nhất)
            $latestAddress = database::ThucThiTraVe(
                "SELECT id FROM shipping_address WHERE user_id = :id ORDER BY id DESC LIMIT 1",
                ['id' => $userId]
            );

            $newId = $latestAddress[0]['id'] ?? 0;

            // 5. báo tin vui về cho javascript
            echo json_encode([
                'status' => 'success',
                'new_id' => $newId
            ]);

        } catch (Exception $e) {
            // bắt lỗi sập db
            echo json_encode([
                'status' => 'error',
                'message' => 'Lỗi DB: ' . $e->getMessage()
            ]);
        }
    }

    function getOrderDetail($param)
    {
        if (!isset($_SESSION['user']['id'])) {
            echo json_encode(['status' => 'error', 'message' => 'Bạn chưa đăng nhập']);
            return;
        }

        $userId = $_SESSION['user']['id'];

        $data = database::ThucThiTraVe("SELECT * FROM orders 
            WHERE order_code = :code AND user_id = :user_id LIMIT 1",
            [
                'code' => $param['code'],
                'user_id' => $userId
            ]
        );

        if (empty($data)) {
            echo json_encode(['status' => 'error', 'message' => 'Không tìm thấy đơn hàng hoặc bạn không có quyền xem']);
            return;
        }

        $orderInfo = $data[0];

        $orderItems = database::ThucThiTraVe("SELECT od.*, 
                pr_vr.variant_name, 
                pr_vr.main_image_url,
                pr.name AS product_name,
                cate.name AS cate_name
                FROM order_detail od
                JOIN product_variants pr_vr ON od.sku = pr_vr.sku
                JOIN products pr ON pr.id = pr_vr.product_id
                JOIN category cate ON cate.id = pr.category_id
                WHERE od.order_id = :order_id",
            ['order_id' => $orderInfo['id']]
        );

        foreach ($orderItems as &$item) {
            $attr = database::ThucThiTraVe("SELECT DISTINCT 
                attr.code,
                attr.name,
                attr_val.id AS value_id, 
                attr_val.value
                FROM product_variants pr_vr 
                JOIN variant_attribute_values vr_attr_val ON vr_attr_val.variant_id = pr_vr.id
                JOIN attribute_value attr_val ON attr_val.id = vr_attr_val.attribute_value_id
                JOIN attributes attr ON attr.code = attr_val.attribute_code
                WHERE pr_vr.sku = :sku
            ", ['sku' => $item['sku']]);
            $item['attributes'] = $attr;
            
            $attrValues = array_column($attr, 'value');
            if(!empty($attrValues)) {
                $item['variant_name'] = implode(' | ', $attrValues);
            }
        }

        echo json_encode([
            'info' => $orderInfo,
            'items' => $orderItems
        ]);
    }

    function getMyOrders($param)
    {
        if (!isset($_SESSION['user']['id'])) {
            echo json_encode(['status' => 'error', 'message' => 'Bạn chưa đăng nhập']);
            return;
        }

        $userId = $_SESSION['user']['id'];
        $sql = "SELECT 
                        o.id AS order_id, o.order_code, o.status, o.payment_status, o.payment_method, o.total_price, o.created_at, o.cancel_reason,
                        od.id AS order_detail_id, od.sku, od.quantity, od.unit_price,
                        pv.variant_name, pv.main_image_url,
                        p.name AS product_name,
                        (SELECT GROUP_CONCAT(attr_val.value SEPARATOR ' | ') 
                         FROM variant_attribute_values vr_attr_val 
                         JOIN attribute_value attr_val ON attr_val.id = vr_attr_val.attribute_value_id 
                         WHERE vr_attr_val.variant_id = pv.id) as dynamic_attr,
                        (SELECT COUNT(*) FROM product_reviews pr WHERE pr.order_detail_id = od.id LIMIT 1) as item_has_reviewed
                    FROM orders o
                    LEFT JOIN order_detail od ON o.id = od.order_id
                    LEFT JOIN product_variants pv ON od.sku = pv.sku
                    LEFT JOIN products p ON pv.product_id = p.id
                    WHERE o.user_id = :user_id
                    ORDER BY o.created_at DESC";

        $rawOrders = database::ThucThiTraVe($sql, ['user_id' => $userId]);

        if (empty($rawOrders)) {
            echo json_encode(['status' => "success", "data" => $this->getEmptyOrderStructure()]);
            return;
        }

        $groupedOrders = [];
        foreach ($rawOrders as $row) {
            $oid = $row['order_id'];

            if (!isset($groupedOrders[$oid])) {
                $groupedOrders[$oid] = [
                    'order_id' => $oid,
                    'order_code' => $row['order_code'],
                    'status' => $row['status'],
                    'payment_status' => $row['payment_status'],
                    'payment_method' => $row['payment_method'],
                    'total_price' => $row['total_price'],
                    'created_at' => $row['created_at'],
                    'cancel_reason' => $row['cancel_reason'],
                    'items' => []
                ];
            }

            if (!empty($row['sku'])) {
                $groupedOrders[$oid]['items'][] = [
                    'order_detail_id' => $row['order_detail_id'],
                    'sku' => $row['sku'],
                    'name' => $row['product_name'] ?? 'Sản phẩm PolyGear',
                    'variant_name' => $row['dynamic_attr'] ?: ($row['variant_name'] ?? ''),
                    'image' => $row['main_image_url'] ?? '',
                    'price' => $row['unit_price'],
                    'quantity' => $row['quantity'],
                    'has_reviewed' => $row['item_has_reviewed'] > 0
                ];
            }
        }

        $result = $this->getEmptyOrderStructure();

        foreach ($groupedOrders as $order) {
            $status = $order['status'];

            $paymentMethod = $order['payment_method'] ?? 'cod';
            $paymentStatus = $order['payment_status'] ?? 'unpaid';

            switch ($status) {
                case 'pending':
                    if (in_array($paymentStatus, ['unpaid']) && $paymentMethod === 'bank') {
                        // chờ thanh toán: pending + unpaid + bank
                        $order['internal_status'] = 'pending_payment';
                        $result['pending_payment'][] = $order;
                    } else if ($paymentMethod === 'cod' || $paymentStatus === 'paid') {
                        // chờ xác nhận: pending + cod hoặc paid
                        $order['internal_status'] = 'pending_confirmation';
                        $result['pending_confirmation'][] = $order;
                    } else {
                        $order['internal_status'] = 'other';
                        $result['other'][] = $order;
                    }
                    break;
                case 'processing':
                case 'shipping':
                    $order['internal_status'] = 'shipping';
                    $result['shipping'][] = $order;
                    break;
                case 'delivering':
                    $order['internal_status'] = 'delivering';
                    $result['delivering'][] = $order;
                    break;
                case 'completed':
                    $order['internal_status'] = 'completed';
                    $result['completed'][] = $order;
                    break;
                case 'cancelled':
                case 'returning':
                case 'returned':
                case 'failed':
                    $order['internal_status'] = 'cancelled';
                    $result['cancelled'][] = $order;
                    break;
                default:
                    $order['internal_status'] = 'other';
                    $result['other'][] = $order;
                    break;
            }

            // đẩy thông báo sau khi set trạng thái xong
            $result['all'][] = $order;
        }

        echo json_encode(['status' => "success", "data" => $result]);
    }

    public function updateMyOrderStatus() {
        if (!isset($_SESSION['user']['id'])) {
            echo json_encode(['status' => 'error', 'message' => 'Bạn chưa đăng nhập']);
            return;
        }

        $raw = file_get_contents("php:// input");
        $data = json_decode($raw, true);

        $code = $data['order_code'] ?? null;
        $newStatus = $data['status'] ?? null;
        $cancelReason = $data['cancel_reason'] ?? null;

        if (!$code || !$newStatus) {
            echo json_encode(['status' => 'error', 'message' => 'Dữ liệu không hợp lệ']);
            return;
        }

        $userId = $_SESSION['user']['id'];
        $orderRes = database::ThucThiTraVe("SELECT id, status, payment_method FROM orders WHERE order_code = :code AND user_id = :user_id", [
            'code' => $code,
            'user_id' => $userId
        ]);

        if (empty($orderRes)) {
            echo json_encode(['status' => 'error', 'message' => 'Không tìm thấy đơn hàng']);
            return;
        }

        $currentStatus = $orderRes[0]['status'];
        $paymentMethod = $orderRes[0]['payment_method'];

        $validUserFlow = [
            'pending' => ['cancelled'],
            'delivering' => ['completed'],
            'completed' => ['returning']
        ];

        if (!isset($validUserFlow[$currentStatus]) || !in_array($newStatus, $validUserFlow[$currentStatus])) {
            echo json_encode(['status' => 'error', 'message' => "Không thể chuyển từ $currentStatus sang $newStatus"]);
            return;
        }

        // nếu xác nhận đã nhận hàng (completed) và là cod thì set thanh toán luôn
        $paymentUpdateSql = "";
        if ($newStatus === 'completed' && $paymentMethod === 'cod') {
            $paymentUpdateSql = ", payment_status = 'paid'";
        }

        try {
            database::beginTransaction();

            $updateFields = "status = :status $paymentUpdateSql";
            $params = [
                'status' => $newStatus,
                'code' => $code,
                'user_id' => $userId
            ];

            if ($newStatus === 'cancelled' && $cancelReason) {
                $updateFields .= ", cancel_reason = :cancel_reason";
                $params['cancel_reason'] = $cancelReason;
            }

            database::ThucThi("UPDATE orders SET $updateFields WHERE order_code = :code AND user_id = :user_id", $params);

            // trường hợp 1: hủy trước khi xuất kho (auto-restock 100%)
            if ($newStatus === 'cancelled') {
                $orderId = $orderRes[0]['id'];
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

        echo json_encode(['status' => 'success', 'message' => 'Cập nhật trạng thái thành công']);
    }

    private function getEmptyOrderStructure()
    {
        return [
            'all' => [], // 1. tất cả
            'pending_payment' => [], // 2. chờ thanh toán
            'pending_confirmation' => [], // 3. chờ xác nhận
            'shipping' => [], // 4. vận chuyển
            'delivering' => [], // 5. đang giao
            'completed' => [], // 6. hoàn thành
            'cancelled' => [], // 7. đã hủy, trả hàng, giao thất bại
        ];
    }

    public function updateFCM() {
        if (!isset($_SESSION['user'])) {
            echo json_encode(['status' => 'error', 'message' => 'Vui lòng đăng nhập']);
            return;
        }

        $raw = file_get_contents("php:// input");
        $data = json_decode($raw, true);

        if (empty($data['fcm_token'])) {
            echo json_encode(['status' => 'error', 'message' => 'Missing FCM token']);
            return;
        }

        $userId = $_SESSION['user']['id'];
        $fcmToken = trim($data['fcm_token']);

        try {
            database::ThucThi("UPDATE user SET fcm_token = :fcm_token WHERE id = :id", [
                'fcm_token' => $fcmToken,
                'id' => $userId
            ]);
            echo json_encode(['status' => 'success']);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function getNotifications() {
        if (!isset($_SESSION['user'])) {
            echo json_encode(['status' => 'error']);
            return;
        }
        
        try {
            database::ThucThi("CREATE TABLE IF NOT EXISTS notification (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT,
                title VARCHAR(100),
                message TEXT,
                is_read TINYINT DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )");

            $userId = $_SESSION['user']['id'];

            // nếu người dùng yêu cầu đánh dấu đã đọc
            if (isset($_GET['mark_read'])) {
                database::ThucThi("UPDATE notification SET is_read = 1 WHERE user_id = :id", ['id' => $userId]);
                echo json_encode(['status' => 'success']);
                return;
            }

            $notifs = database::ThucThiTraVe("SELECT * FROM notification WHERE user_id = :id ORDER BY created_at DESC", ['id' => $userId]);
            echo json_encode(['status' => 'success', 'data' => $notifs ?? []]);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error']);
        }
    }

    // 
    // quản lý tài khoản admin
    // 

    public function getAdminUsers()
    {
        try {
            $data = database::ThucThiTraVe("SELECT id, user_name, phone_number, gmail, avatar_url, create_at, is_locked FROM user ORDER BY create_at DESC");
            echo json_encode(['status' => 'success', 'data' => $data ?? []]);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function toggleUserLock()
    {
        $raw = file_get_contents("php:// input");
        $data = json_decode($raw, true);
        $id = $data['id'] ?? null;
        $isLocked = $data['is_locked'] ?? 0;

        if (!$id) {
            echo json_encode(['status' => 'error', 'message' => 'Thiếu ID người dùng']);
            return;
        }

        database::ThucThi("UPDATE user SET is_locked = :locked WHERE id = :id", [
            'locked' => $isLocked,
            'id' => $id
        ]);

        $msg = $isLocked ? 'Đã khóa tài khoản thành công' : 'Đã mở khóa tài khoản thành công';
        echo json_encode(['status' => 'success', 'message' => $msg]);
    }

    public function getUserPurchaseHistory($param)
    {
        $userId = $_GET['id'] ?? null;
        if (!$userId) {
            echo json_encode(['status' => 'error', 'message' => 'Thiếu ID người dùng']);
            return;
        }

        $orders = database::ThucThiTraVe("SELECT * FROM orders WHERE user_id = :id ORDER BY created_at DESC", ['id' => $userId]);
        echo json_encode(['status' => 'success', 'data' => $orders ?? []]);
    }

    public function getAdminStaff()
    {
        try {
            $data = database::ThucThiTraVe("SELECT id, username, role FROM admin WHERE role != 'admin' ORDER BY id DESC");
            echo json_encode(['status' => 'success', 'data' => $data ?? []]);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function getSuperAdmin()
    {
        try {
            $data = database::ThucThiTraVe("SELECT id, username, role FROM admin WHERE role = 'admin' ORDER BY id DESC");
            echo json_encode(['status' => 'success', 'data' => $data ?? []]);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function createStaff()
    {
        $raw = file_get_contents("php:// input");
        $data = json_decode($raw, true);
        $username = $data['username'] ?? '';
        $password = $data['password'] ?? '';
        $role = $data['role'] ?? 'staff';

        if (empty($username) || empty($password)) {
            echo json_encode(['status' => 'error', 'message' => 'Thiếu username hoặc password']);
            return;
        }

        // kiểm tra tồn tại
        $exists = database::ThucThiTraVe("SELECT id FROM admin WHERE username = :u", ['u' => $username]);
        if (!empty($exists)) {
            echo json_encode(['status' => 'error', 'message' => 'Username đã tồn tại']);
            return;
        }

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        database::ThucThi("INSERT INTO admin (username, password, role) VALUES (:u, :p, :r)", [
            'u' => $username,
            'p' => $hashedPassword,
            'r' => $role
        ]);

        echo json_encode(['status' => 'success', 'message' => 'Tạo tài khoản nhân viên thành công']);
    }

    public function updateStaff()
    {
        $raw = file_get_contents("php:// input");
        $data = json_decode($raw, true);
        $id = $data['id'] ?? null;
        $role = $data['role'] ?? null;

        if (!$id) {
            echo json_encode(['status' => 'error', 'message' => 'Thiếu ID']);
            return;
        }

        database::ThucThi("UPDATE admin SET role = :r WHERE id = :id", [
            'r' => $role,
            'id' => $id
        ]);

        echo json_encode(['status' => 'success', 'message' => 'Cập nhật nhân viên thành công']);
    }

    public function resetStaffPassword()
    {
        $raw = file_get_contents("php:// input");
        $data = json_decode($raw, true);
        $id = $data['id'] ?? null;
        $newPassword = $data['password'] ?? '';

        if (!$id || empty($newPassword)) {
            echo json_encode(['status' => 'error', 'message' => 'Dữ liệu không hợp lệ']);
            return;
        }

        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        database::ThucThi("UPDATE admin SET password = :p WHERE id = :id", [
            'p' => $hashedPassword,
            'id' => $id
        ]);

        echo json_encode(['status' => 'success', 'message' => 'Reset mật khẩu thành công']);
    }

    public function deleteStaff()
    {
        $raw = file_get_contents("php:// input");
        $data = json_decode($raw, true);
        $id = $data['id'] ?? null;

        if (!$id) {
            echo json_encode(['status' => 'error', 'message' => 'Thiếu ID']);
            return;
        }

        if ($id == 1) {
            echo json_encode(['status' => 'error', 'message' => 'Không thể xóa tài khoản Quản trị viên gốc (ID 1)']);
            return;
        }

        // đừng tự xóa mình
        if (isset($_SESSION['admin']['id']) && $_SESSION['admin']['id'] == $id) {
            echo json_encode(['status' => 'error', 'message' => 'Không thể tự xóa chính mình']);
            return;
        }

        database::ThucThi("DELETE FROM admin WHERE id = :id", ['id' => $id]);
        echo json_encode(['status' => 'success', 'message' => 'Đã xóa nhân viên thành công']);
    }
}


?>