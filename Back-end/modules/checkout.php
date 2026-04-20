<?php
if (!defined('SECURE_API_ACCESS')) {
    http_response_code(403);
    header("Location: /home");
    exit();
}
use Firebase\JWT\JWT;
class checkout
{

    public function getCart($param)
    {
        if (!isset($_SESSION['user'])) {
            echo json_encode(['status' => 'error', 'message' => 'Vui lòng đăng nhập']);
            return;
        }
        $userId = $_SESSION['user']['id'];
        $cart = database::ThucThiTraVe("SELECT 
                pr_vr.sku,
                pr_vr.variant_name,
                pr_vr.main_image_url,
                pr_vr.price,
                pr_vr.sale_price,
                cart.quantity,
                pr_vr.status
                FROM cart
                JOIN product_variants pr_vr on pr_vr.sku = cart.sku 
                WHERE user_id = :userId
                ORDER BY cart.id DESC
            ", ['userId' => $userId]);
        echo json_encode(['status' => 'success', 'cart' => $cart]);
    }

    public function addToCart()
    {
        if (!isset($_SESSION['user'])) {
            echo json_encode(['status' => false]);
            return;
        }

        $rawdata = file_get_contents("php:// input");
        $data = json_decode($rawdata, true);

        $userId = $_SESSION['user']['id'];
        $sku = $data['sku'];

        try {
            database::beginTransaction();
            $checkStock = database::ThucThiTraVe("SELECT stock, delete_at FROM product_variants 
                                                    WHERE sku = :sku FOR UPDATE",
                ['sku' => $sku]
            );
            
            // nếu không tìm thấy, hoặc đã bị đánh dấu xóa (ngừng bán), hoặc hết hàng
            if (empty($checkStock) || $checkStock[0]['delete_at'] !== null) {
                database::rollBack();
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Sản phẩm này đã ngừng kinh doanh!'
                ]);
                return;
            }

            if ($checkStock[0]['stock'] <= 0) {
                database::rollBack();
                echo json_encode([
                    'status' => 'out-of-stock',
                    'message' => 'Sản phẩm đã hết hàng!'
                ]);
                return;
            }

            $cart = database::ThucThiTraVe("SELECT quantity FROM cart
                                                WHERE user_id = :userId AND sku = :sku FOR UPDATE
                                                ", ['userId' => $userId, 'sku' => $sku]);

            $is_new = false; // cờ báo cho frontend cập nhật thời gian đổi giỏ hàng

            if (isset($cart) && $cart) {
                database::ThucThi("UPDATE cart 
                                    SET quantity = quantity + 1
                                    WHERE user_id = :userId AND sku = :sku
                                    ", ['userId' => $userId, 'sku' => $sku]);
            } else {
                database::ThucThi("INSERT INTO cart(user_id, sku, quantity) 
                                    VALUES (:userId, :sku, 1)
                                    ", ['userId' => $userId, 'sku' => $sku]);
                $is_new = true;
            }

            $quantity_data = database::ThucThiTraVe("SELECT COUNT(id) as total_quantity
                                                    FROM cart
                                                    WHERE user_id = :userId
                                                    ", ['userId' => $userId]);

            $total_quantity = isset($quantity_data[0]['total_quantity']) ? (int)$quantity_data[0]['total_quantity'] : 0;

            database::commit();

            echo json_encode([
                'status' => 'success',
                'quantity' => $total_quantity,
                'is_new' => $is_new
            ]);

        } catch (Exception $th) {
            database::rollBack();
            echo json_encode(['status' => 'error', 'message' => 'Lỗi hệ thống: ' . $th->getMessage()]);
        }
    }

    public function getCartQuantity($param)
    {
        if (!isset($_SESSION['user'])) {
            echo json_encode(['status' => 'error', 'message' => 'Vui lòng đăng nhập']);
            return;
        }
        $userId = $_SESSION['user']['id'];
        $quantity = database::ThucThiTraVe("SELECT 
                COUNT(id) as quantity
                FROM cart
                WHERE user_id = :userId
            ", ['userId' => $userId])[0]['quantity'] ?? 0;
        echo json_encode(['status' => 'success', 'quantity' => (int)$quantity]);
    }

    public function addQuantity()
    {
        if (!isset($_SESSION['user'])) {
            echo json_encode(['status' => 'error', 'message' => 'Vui lòng đăng nhập']);
            return;
        }

        $raw = file_get_contents("php:// input");
        $data = json_decode($raw, true);

        if (!isset($data['sku']))
            die(json_encode(['status' => "error, thiếu sku"]));

        $userId = $_SESSION['user']['id'];
        $sku = $data['sku'];
        try {
            database::beginTransaction();

            $checkStock = database::ThucThiTraVe("SELECT stock FROM product_variants 
                                                    WHERE sku = :sku FOR UPDATE",
                ['sku' => $sku]
            );
            if (empty($checkStock) || $checkStock[0]['stock'] <= 0) {
                database::rollBack();
                echo json_encode([
                    'status' => 'out-of-stock',
                    'message' => 'Rất tiếc, sản phẩm này đã hết hàng !'
                ]);
                return;
            }

            database::ThucThi("UPDATE cart 
                                SET quantity = quantity + 1
                                WHERE user_id = :userId
                                AND sku = :sku",
                ['userId' => $userId, 'sku' => $sku]
            );
            database::commit();

            echo json_encode(['status' => 'success']);

        } catch (Exception $th) {
            if (database::inTransaction()) {
                database::rollBack();
            }
            echo json_encode(['status' => 'error', 'message' => $th->getMessage()]);
        }

    }

    public function decQuantity()
    {
        if (!isset($_SESSION['user'])) {
            echo json_encode(['status' => 'error', 'message' => 'Vui lòng đăng nhập']);
            return;
        }

        $raw = file_get_contents("php:// input");
        $data = json_decode($raw, true);

        if (!isset($data['sku']))
            die(json_encode(['status' => "error, thiếu sku"]));

        $userId = $_SESSION['user']['id'];
        $sku = $data['sku'];
        $check = database::ThucThiTraVe("SELECT quantity FROM cart 
                    WHERE user_id = :userId
                    AND sku = :sku
                ", ['userId' => $userId, 'sku' => $sku]);

        if (empty($check) || $check[0]['quantity'] == 1)
            die(json_encode(['status' => false]));

        try {
            database::ThucThi("UPDATE cart 
                    set quantity = quantity - 1
                    WHERE user_id = :userId
                    AND sku = :sku AND quantity > 1
                ", ['userId' => $userId, 'sku' => $sku]);
        } catch (Exception $th) {
            die(json_encode(['error' => $th->getMessage()]));
        }

        echo json_encode(['status' => 'success']);
    }

    public function remove()
    {
        if (!isset($_SESSION['user'])) {
            echo json_encode(['status' => 'error', 'message' => 'Vui lòng đăng nhập']);
            return;
        }

        $raw = file_get_contents("php:// input");
        $data = json_decode($raw, true);

        if (!isset($data['sku']))
            die(json_encode(['status' => "error, thiếu sku"]));

        $userId = $_SESSION['user']['id'];
        $sku = $data['sku'];
        try {
            database::ThucThi("DELETE FROM cart 
                    WHERE user_id = :userId
                    AND sku = :sku
                ", ['userId' => $userId, 'sku' => $sku]);
        } catch (Exception $th) {
            die(json_encode(['error' => $th->getMessage()]));
        }
        echo json_encode(['status' => 'success']);
    }

    public function syncCart()
    {
        if (!isset($_SESSION['user'])) {
            echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
            return;
        }

        $raw = file_get_contents("php:// input");
        $data = json_decode($raw, true);

        if (!isset($data['cartData']) || !is_array($data['cartData'])) {
            echo json_encode(['status' => 'success', 'message' => 'No data to sync']);
            return;
        }

        $userId = $_SESSION['user']['id'];
        $items = $data['cartData'];

        try {
            database::beginTransaction();

            foreach ($items as $item) {
                if (!isset($item['sku']) || !isset($item['quantity']))
                    continue;

                $sku = $item['sku'];
                $qty = (int) $item['quantity'];

                // kiểm tra tồn kho
                $product = database::ThucThiTraVe("SELECT stock FROM product_variants WHERE sku = :sku FOR UPDATE", ['sku' => $sku]);
                if (empty($product) || $product[0]['stock'] <= 0)
                    continue;

                // giới hạn số lượng nhâp từ khách (không vượt quá tồn kho thực tế)
                $qty = min($qty, $product[0]['stock']);

                // kiểm tra xem đã có trong giỏ chưa
                $existing = database::ThucThiTraVe("SELECT id, quantity FROM cart WHERE user_id = :userId AND sku = :sku", [
                    'userId' => $userId,
                    'sku' => $sku
                ]);

                if ($existing) {
                    database::ThucThi("UPDATE cart SET quantity = quantity + :qty WHERE id = :id", [
                        'qty' => $qty,
                        'id' => $existing[0]['id']
                    ]);
                } else {
                    database::ThucThi("INSERT INTO cart (user_id, sku, quantity) VALUES (:userId, :sku, :qty)", [
                        'userId' => $userId,
                        'sku' => $sku,
                        'qty' => $qty
                    ]);
                }
            }

            database::commit();
            echo json_encode(['status' => 'success']);
        } catch (Exception $e) {
            database::rollBack();
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function createOrder()
    {
        if (!isset($_SESSION['user'])) {
            echo json_encode(['status' => 'error', 'message' => 'Vui lòng đăng nhập để thanh toán!']);
            return;
        }

        $raw = file_get_contents("php:// input");
        $data = json_decode($raw, true);

        if (empty($data['items'])) {
            echo json_encode(['status' => 'error', 'message' => 'Giỏ hàng của bạn đang trống!']);
            return;
        }

        $userId = $_SESSION['user']['id'];
        $userPhone = $_SESSION['user']['phone'];
        $name = $data['receiver_name'];
        $phone = $data['receiver_phone'];
        $address = $data['shipping_address'];
        $paymentMethod = $data['payment_method'];
        $reminder = isset($data['reminder']) ? $data['reminder'] : '';
        $items = $data['items'];
        $voucherCode = isset($data['voucher_code']) ? trim($data['voucher_code']) : '';

        $orderCode = date('ymdHis') . str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
        $totalItemsPrice = 0;

        try {
            database::beginTransaction();

            $orderDetailsToInsert = [];
            $stockErrors = [];

            // sắp xếp sku để tránh bị deadlock
            usort($items, function ($a, $b) {
                return strcmp($a['sku'], $b['sku']);
            });

            foreach ($items as $item) {
                $sku = $item['sku'];
                $qty = (int) $item['quantity'];

                // lấy cả price và sale_price từ db
                $product = database::ThucThiTraVe("SELECT variant_name, price, sale_price, stock FROM product_variants WHERE sku = :sku FOR UPDATE", ['sku' => $sku]);

                if (empty($product)) {
                    $stockErrors[] = "- Sản phẩm mã $sku không tồn tại.";
                    continue;
                }

                $currentStock = $product[0]['stock'];
                $variantName = $product[0]['variant_name'];

                if ($currentStock < $qty) {
                    if ($currentStock <= 0) {
                        $stockErrors[] = "- Sản phẩm '$variantName' đã hết hàng.";
                    } else {
                        $stockErrors[] = "- Sản phẩm '$variantName' chỉ còn $currentStock sản phẩm.";
                    }
                    continue;
                }

                // nếu sale_price > 0 thì dùng nó, ngược lại dùng price gốc
                $dbPrice = $product[0]['price'];
                $dbSalePrice = $product[0]['sale_price'];
                $actualUnitPrice = (!empty($dbSalePrice) && $dbSalePrice > 0) ? $dbSalePrice : $dbPrice;

                // tính tổng tiền (giá real * số lượng)
                $totalItemsPrice += ($actualUnitPrice * $qty);

                // gom data chuẩn bị chèn vào order_detail với giá thực tế
                $orderDetailsToInsert[] = [
                    'sku' => $sku,
                    'quantity' => $qty,
                    'unit_price' => $actualUnitPrice
                ];

                // trừ kho ngay lập tức
                database::ThucThi("UPDATE product_variants SET stock = stock - :qty WHERE sku = :sku", [
                    'qty' => $qty,
                    'sku' => $sku
                ]);
            }

            if (!empty($stockErrors)) {
                $errorMessage = "Xin lỗi, đơn hàng của bạn có một số sản phẩm không đủ số lượng:\n" . implode("\n", $stockErrors);
                throw new Exception($errorMessage);
            }

            // tính phí vận chuyển
            $shippingFee = ($totalItemsPrice > 1000000) ? 0 : 50000;

            // kiểm tra voucher
            $discountAmount = 0;
            if (!empty($voucherCode)) {
                $voucherData = database::ThucThiTraVe("SELECT * FROM voucher WHERE code = :code AND status = 1", ['code' => $voucherCode]);
                if (!empty($voucherData)) {
                    $v = $voucherData[0];
                    $isValid = true;
                    $now = date('Y-m-d H:i:s');
                    
                    if (!empty($v['time_start']) && $now < $v['time_start']) $isValid = false;
                    if (!empty($v['time_end']) && $now > $v['time_end']) $isValid = false;

                    if ($isValid && !empty($v['condition'])) {
                        $condObj = json_decode($v['condition'], true);
                        if (is_array($condObj)) {
                            // 1. check đơn tối thiểu
                            if (!empty($condObj['min_order']) && $totalItemsPrice < (int)$condObj['min_order']) $isValid = false;
                            
                            // 2. check tuổi tài khoản tối đa
                            if ($isValid && !empty($condObj['max_account_age'])) {
                                $userQuery = database::ThucThiTraVe("SELECT create_at FROM user WHERE id = :id", ['id' => $userId]);
                                if (!empty($userQuery)) {
                                    $accountAgeDays = (strtotime($now) - strtotime($userQuery[0]['create_at'])) / (60 * 60 * 24);
                                    if ($accountAgeDays > (int)$condObj['max_account_age']) $isValid = false;
                                }
                            }

                            // 3. check giới hạn toàn hệ thống
                            if ($isValid && !empty($condObj['global_limit'])) {
                                $globalUses = database::ThucThiTraVe("SELECT COUNT(id) as c FROM orders WHERE voucher_code = :vc AND status NOT IN ('cancelled', 'failed')", ['vc' => $voucherCode]);
                                if ($globalUses[0]['c'] >= (int)$condObj['global_limit']) $isValid = false;
                            }

                            // 4. check giới hạn mỗi người dùng
                            if ($isValid && !empty($condObj['user_limit'])) {
                                $userUses = database::ThucThiTraVe("SELECT COUNT(id) as c FROM orders WHERE voucher_code = :vc AND user_id = :uid AND status NOT IN ('cancelled', 'failed')", ['vc' => $voucherCode, 'uid' => $userId]);
                                if ($userUses[0]['c'] >= (int)$condObj['user_limit']) $isValid = false;
                            }
                        } else {
                            // legacy format: plain string min order
                            if ($totalItemsPrice < (int)$v['condition']) $isValid = false;
                        }
                    }

                    if ($isValid) {
                        $discountAmount = floor($totalItemsPrice * ((int)$v['value'] / 100));
                        
                        // áp dụng giảm giá tối đa
                        $maxDisc = 100000; // default
                        if (!empty($v['condition'])) {
                            $condObj = json_decode($v['condition'], true);
                            if (isset($condObj['max_discount'])) {
                                $maxDisc = (int)$condObj['max_discount'];
                            }
                        }
                        if ($discountAmount > $maxDisc) $discountAmount = $maxDisc;
                    }
                }
            }

            $finalCheckoutPrice = $totalItemsPrice + $shippingFee - $discountAmount;
            if ($finalCheckoutPrice < 0) $finalCheckoutPrice = 0;

            database::ThucThi("INSERT INTO orders (user_id, order_code, voucher_code, receiver_name, receiver_phone, shipping_address, total_price, payment_method,payment_status, reminder, discount, shipping_fee) 
                                VALUES (:user_id, :order_code, :voucher_code, :name, :phone, :address, :total_price, :method, :status, :reminder, :discount, :shipping_fee)", [
                'user_id' => $userId,
                'order_code' => $orderCode,
                'voucher_code' => empty($voucherCode) ? null : $voucherCode,
                'name' => $name,
                'phone' => $phone,
                'address' => $address,
                'total_price' => $finalCheckoutPrice,
                'method' => $paymentMethod,
                'status' => 'unpaid',
                'reminder' => $reminder,
                'discount' => $discountAmount,
                'shipping_fee' => $shippingFee
            ]);

            $newOrder = database::ThucThiTraVe("SELECT id FROM orders WHERE order_code = :order_code", ['order_code' => $orderCode]);
            $orderId = $newOrder[0]['id'];

            foreach ($orderDetailsToInsert as $detail) {
                database::ThucThi("INSERT INTO order_detail (order_id, sku, quantity, unit_price) 
                                    VALUES (:order_id, :sku, :quantity, :unit_price)", [
                    'order_id' => $orderId,
                    'sku' => $detail['sku'],
                    'quantity' => $detail['quantity'],
                    'unit_price' => $detail['unit_price']
                ]);
            }


            database::commit();

            // nếu là thanh toán chuyển khoản -> gọi payos
            if ($paymentMethod === 'bank') {
                require_once ROOT_DIR . "/Back-end/API-app/payos.php";
                // lấy thông tin tạo link (ordercode nãy phải sinh dạng số nhé)
                $payOsResponse = PayOS::createPaymentLink($orderCode, $finalCheckoutPrice, "Thanh toan don $orderCode");

                if ($payOsResponse['code'] == '00') {
                    // lấy đường link trang quét qr của payos
                    $checkoutUrl = $payOsResponse['data']['checkoutUrl'];

                    echo json_encode([
                        'status' => 'success',
                        'message' => 'Đang chuyển hướng đến cổng thanh toán...',
                        'pay_url' => $checkoutUrl,
                        'order_code' => $orderCode
                    ]);
                    return;
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Lỗi tạo mã QR từ PayOS!']);
                    return;
                }
            }

            // nếu là cod -> trả về success bình thường
            echo json_encode([
                'status' => 'success',
                'message' => 'Đặt hàng thành công!',
                'order_code' => $orderCode
            ]);

        } catch (Exception $e) {
            database::rollBack();
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function repayOrder()
    {
        if (!isset($_SESSION['user'])) {
            echo json_encode(['status' => 'error', 'message' => 'Vui lòng đăng nhập để thanh toán!']);
            return;
        }

        $raw = file_get_contents("php:// input");
        $data = json_decode($raw, true);

        if (empty($data['order_code'])) {
            echo json_encode(['status' => 'error', 'message' => 'Thiếu thông tin mã đơn hàng!']);
            return;
        }

        $orderCode = $data['order_code'];
        $userId = $_SESSION['user']['id'];

        $order = database::ThucThiTraVe("SELECT * FROM orders WHERE order_code = :order_code AND user_id = :user_id AND status = 'pending' AND payment_method = 'bank' AND payment_status = 'unpaid'", [
            'order_code' => $orderCode,
            'user_id' => $userId
        ]);

        if (empty($order)) {
            echo json_encode(['status' => 'error', 'message' => 'Đơn hàng không hợp lệ, hoặc đã được thanh toán!']);
            return;
        }

        $totalPrice = $order[0]['total_price'];
        $newOrderCode = date('ymdHis') . str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);

        try {
            // cập nhật lại mã đơn hàng mới vào cơ sở dữ liệu để tạo được link thanh toán payos
            database::ThucThi("UPDATE orders 
            SET order_code = :new_code 
            WHERE order_code = :old_code 
            AND user_id = :user_id", [
                'new_code' => $newOrderCode,
                'old_code' => $orderCode,
                'user_id' => $userId
            ]);

            require_once ROOT_DIR . "/Back-end/API-app/payos.php";
            $payOsResponse = PayOS::createPaymentLink($newOrderCode, $totalPrice, "Thanh toan don $newOrderCode");

            if ($payOsResponse['code'] == '00') {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Đang chuyển hướng đến cổng thanh toán...',
                    'pay_url' => $payOsResponse['data']['checkoutUrl'],
                    'order_code' => $newOrderCode
                ]);
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Lỗi tạo link PayOS. Vui lòng thử lại sau.',
                    "e" => $payOsResponse
                ]);
            }
        } catch (Exception $e) {
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function getGuestCartDetails()
    {
        $raw = file_get_contents("php:// input");
        $data = json_decode($raw, true);

        if (!isset($data['skus']) || !is_array($data['skus'])) {
            echo json_encode(['status' => 'error', 'message' => 'Dữ liệu không hợp lệ']);
            return;
        }

        $skus = $data['skus'];
        if (empty($skus)) {
            echo json_encode(['status' => 'success', 'cart' => []]);
            return;
        }

        // tạo danh sách placeholder cho query in
        $placeholders = [];
        $params = [];
        foreach ($skus as $index => $sku) {
            $key = 'sku' . $index;
            $placeholders[] = ':' . $key;
            $params[$key] = $sku;
        }

        $placeholderStr = implode(',', $placeholders);
        $sql = "SELECT 
                pr_vr.sku,
                pr_vr.variant_name,
                pr_vr.main_image_url,
                pr_vr.price,
                pr_vr.sale_price,
                pr_vr.status,
                pr_vr.stock
                FROM product_variants pr_vr
                WHERE pr_vr.sku IN ($placeholderStr)";

        try {
            $items = database::ThucThiTraVe($sql, $params);
            echo json_encode(['status' => 'success', 'cart' => $items]);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function checkPayos()
    {
        $orderCode = $_GET['orderCode'] ?? '';
        $status = $_GET['status'] ?? '';

        if (!empty($orderCode)) {
            if ($status === 'PAID') {
                database::ThucThi("UPDATE orders SET payment_status = 'paid' WHERE order_code = :code", [
                    'code' => $orderCode
                ]);
            } else if ($status === 'CANCELLED') {
                // thanh toán thất bại hoặc user bấm hủy thanh toán qr
                database::ThucThi("UPDATE orders SET status = 'cancelled', payment_status = 'failed' WHERE order_code = :code", [
                    'code' => $orderCode
                ]);
            }
        }
        header('Content-Type: text/html; charset=utf-8');
        echo "<!DOCTYPE html>
            <html lang='vi'>
            <head>
                <meta charset='UTF-8'>
                <title>Đang xử lý...</title>
            </head>
            <body>
                <h3 style='text-align:center; margin-top:50px; font-family:sans-serif;'>Thanh toán hoàn tất! Đang đóng cửa sổ...</h3>
                <script>
                    // tự động đóng tab con ngay lập tức
                    window.close();
                </script>
            </body>
            </html>";
    }
}
?>