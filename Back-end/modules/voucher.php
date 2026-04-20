<?php
if (!defined('SECURE_API_ACCESS')) {
    http_response_code(403);
    header("Location: /home");
    exit();
}

class voucher
{
    // api lấy voucher
    // lấy các voucher có status = 1 (hoạt động) và còn hiệu lực
    public function getAvailable()
    {
        try {
            $now = date('Y-m-d H:i:s');
            // '`condition`' needs backticks to avoid sql error
            $sql = "SELECT id, code, value, `condition`, time_start, time_end 
                    FROM voucher 
                    WHERE status = 1 
                      AND (time_start IS NULL OR time_start <= :now)
                      AND (time_end IS NULL OR time_end >= :now)";
            
            $vouchers = database::ThucThiTraVe($sql, ['now' => $now]);

            $validVouchers = [];
            $userId = $_SESSION['user']['id'] ?? null;
            $userCreatedAt = null;
            
            if ($userId) {
                $userQuery = database::ThucThiTraVe("SELECT create_at FROM user WHERE id = :id", ['id' => $userId]);
                if (!empty($userQuery)) {
                    $userCreatedAt = $userQuery[0]['create_at'];
                }
            }

            foreach ($vouchers as $v) {
                $isValid = true;
                if (!empty($v['condition'])) {
                    $condObj = json_decode($v['condition'], true);
                    if (is_array($condObj)) {
                        // lọc theo tuổi tài khoản
                        if (!empty($condObj['max_account_age'])) {
                            if (!$userId || !$userCreatedAt) {
                                $isValid = false;
                            } else {
                                $accountAgeDays = (strtotime($now) - strtotime($userCreatedAt)) / (60 * 60 * 24);
                                if ($accountAgeDays > (int)$condObj['max_account_age']) $isValid = false;
                            }
                        }

                        // lọc theo giới hạn hệ thống
                        if ($isValid && !empty($condObj['global_limit'])) {
                            $globalUses = database::ThucThiTraVe("SELECT COUNT(id) as c FROM orders WHERE voucher_code = :vc AND status NOT IN ('cancelled', 'failed')", ['vc' => $v['code']]);
                            if ($globalUses[0]['c'] >= (int)$condObj['global_limit']) $isValid = false;
                        }

                        // lọc theo giới hạn người dùng
                        if ($isValid && !empty($condObj['user_limit'])) {
                            if (!$userId) {
                                $isValid = false; 
                            } else {
                                $userUses = database::ThucThiTraVe("SELECT COUNT(id) as c FROM orders WHERE voucher_code = :vc AND user_id = :uid AND status NOT IN ('cancelled', 'failed')", ['vc' => $v['code'], 'uid' => $userId]);
                                if ($userUses[0]['c'] >= (int)$condObj['user_limit']) $isValid = false;
                            }
                        }
                    }
                }

                if ($isValid) {
                    $validVouchers[] = $v;
                }
            }

            echo json_encode(["status" => "success", "data" => $validVouchers]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => $e->getMessage()]);
        }
    }

    // api admin lấy voucher
    public function getAdminVouchers()
    {
        try {
            $sql = "SELECT * FROM voucher ORDER BY id DESC";
            $vouchers = database::ThucThiTraVe($sql);
            echo json_encode(["status" => "success", "data" => $vouchers]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => $e->getMessage()]);
        }
    }

    // api admin tạo voucher
    public function createVoucher()
    {
        try {
            $data = json_decode(file_get_contents("php:// input"), true);
            $code = $data['code'] ?? '';
            $value = $data['value'] ?? 0;
            $condition = $data['condition'] ?? NULL; // varchar
            if($condition === '') $condition = NULL;
            $time_start = $data['time_start'] ?? NULL;
            if($time_start === '') $time_start = NULL;
            $time_end = $data['time_end'] ?? NULL;
            if($time_end === '') $time_end = NULL;
            $status = isset($data['status']) ? (int)$data['status'] : 1;

            if (empty($code)) {
                http_response_code(400);
                echo json_encode(["status" => "error", "message" => "Vui lòng nhập mã voucher."]);
                return;
            }

            // kiểm tra trùng mã
            $check = database::ThucThiTraVe("SELECT id FROM voucher WHERE code = :code", ['code' => $code]);
            if (count($check) > 0) {
                http_response_code(400);
                echo json_encode(["status" => "error", "message" => "Mã voucher đã tồn tại."]);
                return;
            }

            $sql = "INSERT INTO voucher (code, status, value, `condition`, time_start, time_end) 
                    VALUES (:code, :status, :value, :condition, :time_start, :time_end)";
            
            database::ThucThi($sql, [
                'code' => $code,
                'status' => $status,
                'value' => $value,
                'condition' => $condition,
                'time_start' => $time_start,
                'time_end' => $time_end
            ]);

            echo json_encode(["status" => "success", "message" => "Thêm voucher thành công."]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => $e->getMessage()]);
        }
    }

    // api admin sửa voucher
    public function updateVoucher()
    {
        try {
            $data = json_decode(file_get_contents("php:// input"), true);
            $id = $data['id'] ?? null;
            $code = $data['code'] ?? '';
            $value = $data['value'] ?? 0;
            $condition = $data['condition'] ?? NULL;
            if($condition === '') $condition = NULL;
            $time_start = $data['time_start'] ?? NULL;
            if($time_start === '') $time_start = NULL;
            $time_end = $data['time_end'] ?? NULL;
            if($time_end === '') $time_end = NULL;
            $status = isset($data['status']) ? (int)$data['status'] : 1;

            if (!$id || empty($code)) {
                http_response_code(400);
                echo json_encode(["status" => "error", "message" => "Thiếu thông tin bắt buộc."]);
                return;
            }

            // kiểm tra mã voucher có trùng với id khác không
            $check = database::ThucThiTraVe("SELECT id FROM voucher WHERE code = :code AND id != :id", [
                'code' => $code,
                'id' => $id
            ]);
            if (count($check) > 0) {
                http_response_code(400);
                echo json_encode(["status" => "error", "message" => "Mã voucher đã tồn tại."]);
                return;
            }

            $sql = "UPDATE voucher 
                    SET code = :code, status = :status, value = :value, `condition` = :condition, time_start = :time_start, time_end = :time_end 
                    WHERE id = :id";
            
            database::ThucThi($sql, [
                'code' => $code,
                'status' => $status,
                'value' => $value,
                'condition' => $condition,
                'time_start' => $time_start,
                'time_end' => $time_end,
                'id' => $id
            ]);

            echo json_encode(["status" => "success", "message" => "Cập nhật voucher thành công."]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => $e->getMessage()]);
        }
    }

    // api admin xóa voucher
    public function deleteVoucher()
    {
        try {
            $data = json_decode(file_get_contents("php:// input"), true);
            $id = $data['id'] ?? null;

            if (!$id) {
                http_response_code(400);
                echo json_encode(["status" => "error", "message" => "Lỗi: Cần truyền ID."]);
                return;
            }

            database::ThucThi("DELETE FROM voucher WHERE id = :id", ['id' => $id]);
            echo json_encode(["status" => "success", "message" => "Đã xóa voucher."]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => $e->getMessage()]);
        }
    }
}
?>
