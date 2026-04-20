<?php

define('SECURE_API_ACCESS', true);
define('ROOT_DIR', dirname(dirname(__DIR__)));


require_once ROOT_DIR . "/Back-end/init.php";

try {
    echo "[" . date('Y-m-d H:i:s') . "] BẮT ĐẦU QUY TRÌNH QUÉT ĐƠN HÀNG QUÁ HẠN...\n";

    $sql = "UPDATE orders 
            SET status = 'completed', 
                payment_status = 'paid' 
            WHERE status = 'delivering' 
            AND updated_at < (NOW() - INTERVAL 14 DAY)";

    database::ThucThi($sql);
    
    echo "[" . date('Y-m-d H:i:s') . "] HOÀN TẤT: Đã cập nhật xong các đơn hàng quá hạn.\n";

} catch (Exception $e) {
    echo "[" . date('Y-m-d H:i:s') . "] LỖI: " . $e->getMessage() . "\n";
}
