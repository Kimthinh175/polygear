<?php
    if (!defined('SECURE_API_ACCESS')) {
        http_response_code(403);
        header("Location: /home");
        exit();
    }

    class upload {
        public function uploadImgs(){
                if (empty($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
                http_response_code(400); 
                echo json_encode(['error' => 'Lỗi: Không tìm thấy file hoặc quá trình upload thất bại.']);
                return;
            }

            $file = $_FILES['file'];
            $tmp_name = $file['tmp_name'];
            $original_name = basename($file['name']);
            
            $allowed_exts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
            
            if (!in_array($ext, $allowed_exts)) {
                http_response_code(400);
                echo json_encode(['error' => 'Lỗi: Chỉ chấp nhận định dạng ảnh (JPG, PNG, GIF, WEBP).']);
                return;
            }

            $upload_dir = ROOT_DIR . '/Front-end/public/img/post/';
            
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $new_filename = 'polygear_post_' . time() . '_' . rand(1000, 9999) . '.webp';
            $target_file = $upload_dir . $new_filename;

            $is_saved = $this->convertToWebp($tmp_name, $target_file, $ext);

            if ($is_saved) {

                $image_url = '/Front-end/public/img/post/' . $new_filename;
                
                echo json_encode(['location' => $image_url]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Lỗi hệ thống: Không thể xử lý và lưu ảnh.']);
            }
        }

        private function convertToWebp($source, $destination, $ext, $quality = 80) {
            if ($ext == 'jpeg' || $ext == 'jpg') {
                $image = imagecreatefromjpeg($source);
            } elseif ($ext == 'png') {
                $image = imagecreatefrompng($source);
                imagepalettetotruecolor($image);
                imagealphablending($image, true);
                imagesavealpha($image, true);
            } elseif ($ext == 'gif') {
                $image = imagecreatefromgif($source);
            } elseif ($ext == 'webp') {
                return move_uploaded_file($source, $destination);
            } else {
                return false;
            }

            if ($image) {
                $result = imagewebp($image, $destination, $quality);
                imagedestroy($image); 
                return $result;
            }
            return false;
        }
        
        
    }
?>