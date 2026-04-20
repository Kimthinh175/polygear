<?php
if (!defined('SECURE_API_ACCESS')) {
    http_response_code(403);
    header("Location: /home");
    exit();
}

class banners
{
    // lấy tất cả banner đang kích hoạt (cho frontend)
    public function getAll()
    {
        try {
            $data = database::ThucThiTraVe("SELECT * FROM banners WHERE status = 1 ORDER BY order_index ASC");
            echo json_encode(['status' => 'success', 'data' => $data]);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    // lấy tất cả banner bao gồm cả ẩn (cho admin)
    public function adminGetAll()
    {
        try {
            $data = database::ThucThiTraVe("SELECT * FROM banners ORDER BY type, order_index ASC");
            echo json_encode(['status' => 'success', 'data' => $data]);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    // thêm banner mới
    public function create()
    {
        if (!isset($_FILES['image'])) {
            echo json_encode(['status' => 'error', 'message' => 'Vui lòng chọn ảnh banner!']);
            return;
        }

        $title = $_POST['title'] ?? 'Untitled';
        $link_url = $_POST['link_url'] ?? '';
        $type = $_POST['type'] ?? 'main_slider';
        $order_index = $_POST['order_index'] ?? 0;
        $status = $_POST['status'] ?? 1;

        // xử lý upload ảnh
        $file = $_FILES['image'];
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $new_filename = 'banner_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
        $relative_path = 'img/banners/' . $new_filename;
        $target_dir = ROOT_DIR . '/Front-end/public/img/banners/';
        
        // đảm bảo thư mục tồn tại (phòng hờ)
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }

        $target_file = $target_dir . $new_filename;

        if (move_uploaded_file($file['tmp_name'], $target_file)) {
            try {
                database::ThucThi("INSERT INTO banners (title, image_url, link_url, type, order_index, status) 
                                    VALUES (:title, :image_url, :link_url, :type, :order_index, :status)", [
                    'title' => $title,
                    'image_url' => $relative_path,
                    'link_url' => $link_url,
                    'type' => $type,
                    'order_index' => $order_index,
                    'status' => $status
                ]);
                echo json_encode(['status' => 'success', 'message' => 'Thêm banner thành công!']);
            } catch (Exception $e) {
                // xoá file nếu db lỗi
                if (file_exists($target_file)) unlink($target_file);
                echo json_encode(['status' => 'error', 'message' => 'Lỗi DB: ' . $e->getMessage()]);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Không thể lưu file ảnh vào thư mục!']);
        }
    }

    // cập nhật banner
    public function update()
    {
        $id = $_POST['id'] ?? null;
        if (!$id) {
            echo json_encode(['status' => 'error', 'message' => 'Thiếu ID banner!']);
            return;
        }

        $title = $_POST['title'] ?? '';
        $link_url = $_POST['link_url'] ?? '';
        $type = $_POST['type'] ?? 'main_slider';
        $order_index = $_POST['order_index'] ?? 0;
        $status = $_POST['status'] ?? 1;

        $fields = [
            'title = :title',
            'link_url = :link_url',
            'type = :type',
            'order_index = :order_index',
            'status = :status'
        ];
        $params = [
            'title' => $title,
            'link_url' => $link_url,
            'type' => $type,
            'order_index' => $order_index,
            'status' => $status,
            'id' => $id
        ];

        // nếu có upload ảnh mới
        if (isset($_FILES['image']) && $_FILES['image']['size'] > 0) {
            // lấy ảnh cũ để xoá
            $old = database::ThucThiTraVe("SELECT image_url FROM banners WHERE id = :id", ['id' => $id]);
            
            $file = $_FILES['image'];
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $new_filename = 'banner_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
            $relative_path = 'img/banners/' . $new_filename;
            $target_file = ROOT_DIR . '/Front-end/public/img/banners/' . $new_filename;

            if (move_uploaded_file($file['tmp_name'], $target_file)) {
                $fields[] = 'image_url = :image_url';
                $params['image_url'] = $relative_path;

                // xoá ảnh cũ
                if (!empty($old)) {
                    $old_file = ROOT_DIR . '/' . $old[0]['image_url'];
                    if (file_exists($old_file)) unlink($old_file);
                }
            }
        }

        try {
            $sql = "UPDATE banners SET " . implode(', ', $fields) . " WHERE id = :id";
            database::ThucThi($sql, $params);
            echo json_encode(['status' => 'success', 'message' => 'Cập nhật banner thành công!']);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    // xoá banner
    public function delete()
    {
        $raw = file_get_contents("php:// input");
        $data = json_decode($raw, true);
        $id = $data['id'] ?? null;

        if (!$id) {
            echo json_encode(['status' => 'error', 'message' => 'Thiếu ID banner!']);
            return;
        }

        try {
            // lấy ảnh để xoá file vật lý
            $old = database::ThucThiTraVe("SELECT image_url FROM banners WHERE id = :id", ['id' => $id]);
            if (!empty($old)) {
                $old_file = ROOT_DIR . '/Front-end/public/' . $old[0]['image_url'];
                if (file_exists($old_file)) unlink($old_file);
            }

            database::ThucThi("DELETE FROM banners WHERE id = :id", ['id' => $id]);
            echo json_encode(['status' => 'success', 'message' => 'Xoá banner thành công!']);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
}
