<?php
if (!defined('SECURE_API_ACCESS')) {
    http_response_code(403);
    header("Location: /home");
    exit();
}

class catalog
{
    // lấy tất cả sản phẩm
    public function getAll()
    {
        $data = database::ThucThiTraVe("
                SELECT cate.code as category_code,
                cate.name as category_name,
                concat(pr.name,'-',pr_vr.variant_name) as name,
                b.brand_name,
                pr_vr.price,
                pr_vr.sale_price,
                pr_vr.main_image_url,
                pr_vr.sku
                FROM products pr
                JOIN product_variants pr_vr on pr.id=pr_vr.product_id
                JOIN category cate on cate.id=pr.category_id
                LEFT JOIN brand b on pr.brand_id=b.id;
            ");
        self::applyPromotions($data);
        echo json_encode(["status" => "success", "data" => $data]);
    }

    public function getAllOrigin()
    {
        $data = database::ThucThiTraVe("
            SELECT pr.*, cate.code as category_code, cate.name as category_name 
            FROM products pr
            LEFT JOIN category cate ON pr.category_id = cate.id
        ");
        echo json_encode(["status" => "success", "data" => $data]);
    }

    // lấy sản phẩm bằng sku
    public function getBySku($param)
    {
        if (!$param['sku'])
            die(json_encode(["status" => "error", "message" => "Không tìm thấy!"]));
        $sku = $param['sku'] ?? '';
        $info = database::ThucThiTraVe("SELECT cate.code,
                cate.name as cate_name,
                concat(pr.name,'-',pr_vr.variant_name) as name,
                pr.brand_id,
                pr_vr.price,
                pr_vr.sale_price,
                pr_vr.main_image_url,
                pr_vr.sku,
                pr_vr.stock,
                pr_vr.delete_at,
                pr_vr.description
                FROM products pr
                JOIN product_variants pr_vr on pr.id=pr_vr.product_id
                JOIN category cate on cate.id=pr.category_id
                WHERE sku= :sku
            ", ['sku' => $sku]) ?? [];

        $info = count($info) > 0 ? $info[0] : null;

        if (!$info) {
            http_response_code(404);
            die(json_encode(["status" => "error", "message" => "Không tìm thấy!"]));
        }

        $imgs = database::ThucThiTraVe("SELECT 
                vr_img.detail_image_url
                FROM variant_images vr_img
                JOIN product_variants pr_vr on vr_img.variant_id= pr_vr.id
                WHERE sku = :sku
            ", ['sku' => $sku]) ?? [];

        $spec = database::ThucThiTraVe("SELECT s.spec_code, vr_s.spec_value, s.spec_name 
                FROM variant_specs vr_s
                JOIN specs s on s.spec_code=vr_s.spec_code
                JOIN product_variants pr_vr on vr_s.variant_id= pr_vr.id
                WHERE sku = :sku
            ", ['sku' => $sku]) ?? [];

        $variant_attr = database::ThucThiTraVe("SELECT 
                attr_val.id
                FROM attribute_value attr_val
                JOIN attributes attr on attr.code=attr_val.attribute_code
                JOIN variant_attribute_values vr_attr_val on vr_attr_val.attribute_value_id=attr_val.id
                JOIN product_variants pr_vr on pr_vr.id=vr_attr_val.variant_id
                WHERE sku= :sku
            ", ['sku' => $sku]) ?? [];
        $info['attribute'] = $variant_attr;

        $id_row = database::ThucThiTraVe("SELECT pr.id FROM products pr
                JOIN product_variants pr_vr ON pr.id=pr_vr.product_id
                WHERE pr_vr.sku=:sku
            ", ['sku' => $sku]);
        $id = !empty($id_row) ? $id_row[0]['id'] : null;

        $review_stats = database::ThucThiTraVe("
            SELECT COUNT(*) as total_reviews, COALESCE(AVG(rating), 0) as avg_rating 
            FROM product_reviews 
            WHERE product_id = :id
        ", ['id' => $id]);
        $info['total_reviews'] = $review_stats[0]['total_reviews'] ?? 0;
        $info['avg_rating'] = round($review_stats[0]['avg_rating'], 1);

        $attr = database::ThucThiTraVe("SELECT DISTINCT 
                attr.code,
                attr.name,
                attr_val.id AS value_id, 
                attr_val.value
                FROM products pr 
                JOIN product_variants pr_vr ON pr_vr.product_id = pr.id
                JOIN variant_attribute_values vr_attr_val ON vr_attr_val.variant_id = pr_vr.id
                JOIN attribute_value attr_val ON attr_val.id = vr_attr_val.attribute_value_id
                JOIN attributes attr ON attr.code = attr_val.attribute_code
                WHERE pr.id = :id
            ", ['id' => $id]);

        $attributes = [];

        foreach ($attr as $row) {
            $code = $row['code'];

            if (!isset($attributes[$code])) {
                $attributes[$code] = [
                    'name' => $row['name'],
                    'values' => []
                ];
            }

            $attributes[$code]['values'][] = [
                'id' => $row['value_id'],
                'value' => $row['value']
            ];
        }

        $raw_map_data = database::ThucThiTraVe("
                SELECT 
                    pr_vr.sku,
                    vr_attr_val.attribute_value_id
                FROM product_variants pr_vr
                JOIN variant_attribute_values vr_attr_val ON vr_attr_val.variant_id = pr_vr.id
                WHERE pr_vr.product_id = :id
            ", ['id' => $id]);

        $variant_map_temp = [];

        foreach ($raw_map_data as $row) {
            $sku = $row['sku'];
            $val_id = (int) $row['attribute_value_id'];

            if (!isset($variant_map_temp[$sku])) {
                $variant_map_temp[$sku] = [
                    'sku' => $sku,
                    'attr_ids' => []
                ];
            }

            $variant_map_temp[$sku]['attr_ids'][] = $val_id;
        }

        $final_variant_map = array_values($variant_map_temp);

        // sản phẩm liên quan: cùng danh mục, khác sản phẩm hiện tại, lấy tối đa 8
        $related = database::ThucThiTraVe("
            SELECT DISTINCT 
                pv.sku,
                CONCAT(p.name, ' - ', pv.variant_name) as name,
                pv.price,
                pv.sale_price,
                pv.main_image_url,
                pv.sold,
                p.brand_id
            FROM products p
            JOIN product_variants pv ON pv.product_id = p.id
            JOIN category c ON c.id = p.category_id
            WHERE c.code = :cate_code
              AND pv.sku != :sku
              AND pv.status = 'active'
            ORDER BY pv.sold DESC
            LIMIT 8
        ", ['cate_code' => $info['code'], 'sku' => $sku]) ?? [];

        self::applyPromotionToSingle($info);
        self::applyPromotions($related);
        echo json_encode([
            "status" => "success",
            "info" => $info,
            'imgs' => $imgs,
            'specs' => $spec,
            'attributes' => $attributes ?? [],
            'variant_map' => $final_variant_map ?? [],
            'related' => $related
        ]);

    }

    public function getAllCategory()
    {
        $data = database::ThucThiTraVe("SELECT id, code,
                name,
                img
                FROM category
            ");
        echo json_encode(["status" => "success", "data" => $data]);
    }

    public function findByCategory($param)
    {
        $cate = $param['category'] ?? null;
        if ($cate != null) {
            $sql = 'WHERE cate.code= :cate';
            $param = ['cate' => $cate];
        } else {
            $sql = '';
            $param = [];
        }
        $info = database::ThucThiTraVe("SELECT cate.code,
                cate.name as cate_name,
                concat(pr.name,' ',pr_vr.variant_name) as name,
                pr.brand_id,
                pr_vr.price,
                pr_vr.sale_price,
                pr_vr.stock,
                pr_vr.main_image_url,
                pr_vr.sku,
                pr_vr.status
                FROM products pr
                JOIN product_variants pr_vr on pr.id=pr_vr.product_id
                JOIN category cate on cate.id=pr.category_id
                $sql
            ", $param) ?? [];

        for ($i = 0; $i < count($info); $i++) {
            $info[$i]['specs'] = database::ThucThiTraVe("SELECT 
                vr_s.spec_value,
                s.spec_code
                FROM variant_specs vr_s
                JOIN specs s on s.spec_code=vr_s.spec_code
                JOIN product_variants pr_vr on vr_s.variant_id= pr_vr.id
                WHERE sku = :sku
                LIMIT 4
                ", ['sku' => $info[$i]['sku']]);
        }

        echo json_encode([
            "status" => "success",
            "info" => $info
        ]);

    }

    public function find($param)
    {
        $keyword = $param['keyword'] ?? '';
        $dbParam = [];
        $sql = '';

        if (trim($keyword) !== '') {
            // tách từ khóa thành các mảng từ con (vd: "ram 16 gb" -> ['ram','16','gb'])
            $words = explode(' ', preg_replace('/\s+/', ' ', trim($keyword)));
            $conditions = [];

            foreach ($words as $index => $word) {
                $conditions[] = "(CONCAT(pr.name, ' ', pr_vr.variant_name) LIKE :kw_$index OR pr_vr.sku LIKE :kw_$index OR cate.name LIKE :kw_$index)";
                $dbParam["kw_$index"] = '%' . $word . '%';
            }

            if (!empty($conditions)) {
                $sql = 'WHERE ' . implode(' AND ', $conditions);
            }
        }

        $info = database::ThucThiTraVe("SELECT cate.code,
                cate.name as cate_name,
                concat(pr.name,' ',pr_vr.variant_name) as name,
                pr.brand_id,
                pr_vr.price,
                pr_vr.sale_price,
                pr_vr.stock,
                pr_vr.main_image_url,
                pr_vr.sku,
                pr_vr.status
                FROM products pr
                JOIN product_variants pr_vr on pr.id=pr_vr.product_id
                JOIN category cate on cate.id=pr.category_id
                $sql
            ", $dbParam) ?? [];

        for ($i = 0; $i < count($info); $i++) {
            $info[$i]['specs'] = database::ThucThiTraVe("SELECT 
                vr_s.spec_value,
                s.spec_code
                FROM variant_specs vr_s
                JOIN specs s on s.spec_code=vr_s.spec_code
                JOIN product_variants pr_vr on vr_s.variant_id= pr_vr.id
                WHERE sku = :sku
                LIMIT 4
                ", ['sku' => $info[$i]['sku']]);
        }

        echo json_encode([
            "status" => "success",
            "info" => $info
        ]);
    }

    public function getProducts()
    {
        $data = database::ThucThiTraVe("SELECT * FROM products");
        echo json_encode($data);
    }

    public function getAllProductImages()
    {
        $product_id = $_GET['product_id'] ?? null;
        if (!$product_id) {
            echo json_encode(['status' => 'error', 'message' => 'Thiếu ID sản phẩm', 'data' => []]);
            return;
        }

        try {
            // lấy ảnh chính từ các biến thể
            $mainImgs = database::ThucThiTraVe("SELECT DISTINCT main_image_url as url FROM product_variants WHERE product_id = :pid AND delete_at IS NULL AND main_image_url IS NOT NULL AND main_image_url != ''", ['pid' => $product_id]);

            // lấy ảnh chi tiết từ variant_images
            $detailImgs = database::ThucThiTraVe("
                SELECT DISTINCT vi.detail_image_url as url 
                FROM variant_images vi
                JOIN product_variants pv ON vi.variant_id = pv.id
                WHERE pv.product_id = :pid AND pv.delete_at IS NULL AND vi.detail_image_url IS NOT NULL AND vi.detail_image_url != ''
            ", ['pid' => $product_id]);

            $allUrls = [];
            if (!empty($mainImgs)) {
                foreach ($mainImgs as $row) {
                    if (!in_array($row['url'], $allUrls))
                        $allUrls[] = $row['url'];
                }
            }
            if (!empty($detailImgs)) {
                foreach ($detailImgs as $row) {
                    if (!in_array($row['url'], $allUrls))
                        $allUrls[] = $row['url'];
                }
            }

            echo json_encode(['status' => 'success', 'data' => $allUrls]);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage(), 'data' => []]);
        }
    }

    public function getAllSpecs()
    {
        $cate_code = $_GET['cate_code'] ?? null;
        if ($cate_code) {
            $data = database::ThucThiTraVe("
                SELECT DISTINCT s.* 
                FROM specs s
                JOIN variant_specs vs ON s.spec_code = vs.spec_code
                JOIN product_variants pv ON vs.variant_id = pv.id
                JOIN products p ON pv.product_id = p.id
                JOIN category c ON p.category_id = c.id
                WHERE c.code = :cate_code
            ", ['cate_code' => $cate_code]);
        } else {
            $data = database::ThucThiTraVe("SELECT * FROM specs");
        }
        echo json_encode($data);
    }

    public function getAllAttribute()
    {
        $cate_code = $_GET['cate_code'] ?? null;
        if ($cate_code) {
            $data = database::ThucThiTraVe("
                SELECT DISTINCT
                    a.id AS attribute_id, 
                    a.name AS attribute_name, 
                    av.id AS value_id, 
                    av.value AS attribute_value
                FROM attribute_value av
                JOIN attributes a ON av.attribute_code = a.code
                JOIN variant_attribute_values vav ON av.id = vav.attribute_value_id
                JOIN product_variants pv ON vav.variant_id = pv.id
                JOIN products p ON pv.product_id = p.id
                JOIN category c ON p.category_id = c.id
                WHERE c.code = :cate_code
                ORDER BY a.name ASC, av.value ASC
            ", ['cate_code' => $cate_code]);
        } else {
            $data = database::ThucThiTraVe("
                SELECT DISTINCT
                    a.id AS attribute_id, 
                    a.name AS attribute_name, 
                    av.id AS value_id, 
                    av.value AS attribute_value
                FROM attribute_value av
                JOIN attributes a ON av.attribute_code = a.code
                ORDER BY a.name ASC, av.value ASC
            ");
        }
        echo json_encode(['status' => 'success', 'data' => $data]);
    }


    private function toSlug($str)
    {
        $str = trim(mb_strtolower($str));
        $str = preg_replace('/(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)/', 'a', $str);
        $str = preg_replace('/(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)/', 'e', $str);
        $str = preg_replace('/(ì|í|ị|ỉ|ĩ)/', 'i', $str);
        $str = preg_replace('/(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)/', 'o', $str);
        $str = preg_replace('/(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)/', 'u', $str);
        $str = preg_replace('/(ỳ|ý|ỵ|ỷ|ỹ)/', 'y', $str);
        $str = preg_replace('/(đ)/', 'd', $str);
        $str = preg_replace('/[^a-z0-9-\s]/', '', $str);
        $str = preg_replace('/([\s]+)/', '-', $str);
        return $str;
    }

    public function createVariant()
    {
        // 1. kiểm tra validate
        $validation = $this->validateVariantInput($_POST, $_FILES);
        if ($validation['status'] === 'error') {
            echo json_encode($validation);
            return;
        }

        // nhận dữ liệu cơ bản
        $product_id = $_POST['product_id'];
        $variant_name = $_POST['variant_name'];
        $sku = $_POST['sku'];
        $price = $_POST['price'];
        $sale_price = $_POST['sale_price'] ?? 0;
        $stock = $_POST['stock'];
        $min_stock = $_POST['min_stock'] ?? 0;
        $view = $_POST['view'] ?? 0;
        $sold = $_POST['sold'] ?? 0;
        $status = $_POST['status'] ?? 'active';
        $description = $_POST['description'] ?? '';
        $create_at = date('Y-m-d H:i:s');
        if (!empty($_POST['create_at'])) {
            $parsed = strtotime($_POST['create_at']);
            if ($parsed) {
                $create_at = date('Y-m-d H:i:s', $parsed);
            }
        }
        $delete_at = null;

        // decode json mảng
        $attributes = isset($_POST['attributes']) ? json_decode($_POST['attributes'], true) : [];
        $specs = isset($_POST['variant_specs']) ? json_decode($_POST['variant_specs'], true) : [];

        $upload_dir = __DIR__ . '/../../Front-end/public/img/products/';
        if (!is_dir($upload_dir))
            mkdir($upload_dir, 0777, true);

        // mảng chứa thông tin [tmp_name => thư_mục_lưu_thật]. gán sau khi commit
        $filesToMove = [];
        $main_image_url_db = '';

        // xử lý nạp trước file chính (main_image_url)
        if (!empty($_FILES['main_image_url']['name'])) {
            $ext = strtolower(pathinfo($_FILES['main_image_url']['name'], PATHINFO_EXTENSION));
            $new_filename = $sku . '_main_' . time() . '.' . $ext;

            $target_file = $upload_dir . $new_filename;
            $filesToMove[$_FILES['main_image_url']['tmp_name']] = $target_file;

            // link nhét vào db
            $main_image_url_db = 'img/products/' . $new_filename;
        } else if (!empty($_POST['main_image_url_existing'])) {
            $main_image_url_db = $_POST['main_image_url_existing'];
        }

        try {
            // kiểm tra trùng sku
            $skuCheck = database::ThucThiTraVe("SELECT id FROM product_variants WHERE sku = :sku", ['sku' => $sku]);
            if (!empty($skuCheck)) {
                echo json_encode([
                    'status' => 'error',
                    'messages' => ['Mã SKU này đã tồn tại trong hệ thống. Vui lòng sử dụng mã khác.']
                ]);
                return;
            }

            // bắt đầu transaction
            database::beginTransaction();

            // bước 1: insert vào bảng product_variants
            $sql_variant = "INSERT INTO product_variants (product_id, variant_name, sku, price, sale_price, stock, min_stock, view, sold, status, description, main_image_url, create_at, delete_at) 
                                VALUES (:product_id, :variant_name, :sku, :price, :sale_price, :stock, :min_stock, :view, :sold, :status, :description, :main_image_url, :create_at, :delete_at)";
            database::ThucThi($sql_variant, [
                'product_id' => $product_id,
                'variant_name' => $variant_name,
                'sku' => $sku,
                'price' => $price,
                'sale_price' => $sale_price,
                'stock' => $stock,
                'min_stock' => $min_stock,
                'view' => $view,
                'sold' => $sold,
                'status' => $status,
                'description' => $description,
                'main_image_url' => $main_image_url_db,
                'create_at' => $create_at,
                'delete_at' => $delete_at
            ]);

            // lấy id của biến thể vừa thêm
            $variantData = database::ThucThiTraVe("SELECT id FROM product_variants WHERE sku = :sku", ['sku' => $sku]);
            $variant_id = $variantData[0]['id'];

            // bước 2: xử lý attributes
            if (!empty($attributes)) {
                foreach ($attributes as $item) {
                    $attr_id = $item['attribute']['id'] ?? null;
                    $val_id = $item['value']['id'] ?? null;
                    $attr_code = '';

                    // nếu thuộc tính là thêm mới
                    if (!empty($item['attribute']['is_new'])) {
                        $name = $item['attribute']['name'];
                        $attr_code = $this->toSlug($name);

                        // đảm bảo không bị trùng code
                        $checkCode = database::ThucThiTraVe("SELECT id FROM attributes WHERE code = :code", ['code' => $attr_code]);
                        if (empty($checkCode)) {
                            database::ThucThi("INSERT INTO attributes (code, name) VALUES (:code, :name)", ['code' => $attr_code, 'name' => $name]);
                        }
                    } else {
                        // nếu là thuộc tính cũ, lấy code ra dựa vào id
                        if ($attr_id) {
                            $row = database::ThucThiTraVe("SELECT code FROM attributes WHERE id = :id", ['id' => $attr_id]);
                            if (!empty($row))
                                $attr_code = $row[0]['code'];
                        }
                    }

                    // nếu giá trị là thêm mới
                    if (!empty($item['value']['is_new']) && $attr_code !== '') {
                        $value_name = $item['value']['value'];

                        // thếm mới vào db
                        database::ThucThi("INSERT INTO attribute_value (attribute_code, value) VALUES (:attr_code, :val_name)", [
                            'attr_code' => $attr_code,
                            'val_name' => $value_name
                        ]);

                        // bốc lấy id vừa sinh dùng cho bảng trung gian
                        $valData = database::ThucThiTraVe("SELECT id FROM attribute_value WHERE attribute_code = :attr_code AND value = :val_name ORDER BY id DESC LIMIT 1", [
                            'attr_code' => $attr_code,
                            'val_name' => $value_name
                        ]);
                        $val_id = $valData[0]['id'];
                    }

                    // gắn vào bảng trung gian
                    if ($val_id && $variant_id) {
                        database::ThucThi("INSERT INTO variant_attribute_values (variant_id, attribute_value_id) VALUES (:var_id, :val_id)", ['var_id' => $variant_id, 'val_id' => $val_id]);
                    }
                }
            }

            // bước 3: xử lý thông số kỹ thuật (specs)
            if (!empty($specs)) {
                foreach ($specs as $spec) {
                    $spec_code = $spec['spec_code'] ?? null;

                    // nếu thông số là thêm mới
                    if (!empty($spec['_is_new'])) {
                        $spec_name = $spec['spec_name'];
                        if (!$spec_code)
                            $spec_code = str_replace('-', '_', $this->toSlug($spec_name));

                        $checkSpec = database::ThucThiTraVe("SELECT id FROM specs WHERE spec_code = :spec_code", ['spec_code' => $spec_code]);
                        if (empty($checkSpec)) {
                            database::ThucThi("INSERT INTO specs (spec_code, spec_name) VALUES (:spec_code, :spec_name)", ['spec_code' => $spec_code, 'spec_name' => $spec_name]);
                        }
                    }

                    // gắn vào bảng variant_specs, nếu mảng js gửi rỗng thì bỏ qua
                    if ($spec_code && $variant_id && trim((string) $spec['spec_value']) !== '') {
                        database::ThucThi("INSERT INTO variant_specs (variant_id, spec_code, spec_value) VALUES (:var_id, :spec_code, :spec_val)", [
                            'var_id' => $variant_id,
                            'spec_code' => $spec_code,
                            'spec_val' => $spec['spec_value']
                        ]);
                    }
                }
            }

            // bước 4: xử lý dàn ảnh phụ (images)
            if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
                $total_files = count($_FILES['images']['name']);

                for ($i = 0; $i < $total_files; $i++) {
                    $tmp_name = $_FILES['images']['tmp_name'][$i];
                    if (!$tmp_name)
                        continue;

                    $ext = strtolower(pathinfo($_FILES['images']['name'][$i], PATHINFO_EXTENSION));
                    $new_filename = $sku . '_sub_' . time() . '_' . $i . '.' . $ext;
                    $target_file = $upload_dir . $new_filename;

                    $filesToMove[$tmp_name] = $target_file;

                    // nhét link vào db bảng variant_images
                    $detail_url = 'img/products/' . $new_filename;
                    database::ThucThi("INSERT INTO variant_images (variant_id, detail_image_url) VALUES (:var_id, :url)", ['var_id' => $variant_id, 'url' => $detail_url]);
                }
            }

            if (!empty($_POST['images_existing'])) {
                $existing_imgs = json_decode($_POST['images_existing'], true);
                if (is_array($existing_imgs)) {
                    foreach ($existing_imgs as $img_url) {
                        database::ThucThi("INSERT INTO variant_images (variant_id, detail_image_url) VALUES (:var_id, :url)", ['var_id' => $variant_id, 'url' => $img_url]);
                    }
                }
            }

            // chốt giao dịch database
            database::commit();

            // sau khi commmit an toàn -> move file vào thư mục thật (theo luật yêu cầu của admin)
            $moveErrorCount = 0;
            foreach ($filesToMove as $tmp => $dest) {
                if (!move_uploaded_file($tmp, $dest)) {
                    $moveErrorCount++;
                }
            }

            if ($moveErrorCount > 0) {
                echo json_encode([
                    'status' => 'success',
                    'message' => "Tạo biến thể Database thành công nhưng có $moveErrorCount file ảnh không thể lưu vào thư mục hệ thống do phân quyền!"
                ]);
            } else {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Tạo biến thể và lưu trữ mảng ảnh thành công hoàn toàn!'
                ]);
            }

        } catch (Exception $e) {
            // nếu có lỗi bất kỳ đâu trong quá trình db -> hủy thao tác rác
            database::rollBack();
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'Lỗi hệ thống khi thiết lập DB. Đã thu hồi lệnh.',
                'detail' => $e->getMessage()
            ]);
        }
    }

    function validateVariantInput($postData, $fileData = [])
    {
        $errors = [];

        // 1. danh sách các trường bắt buộc phải có trong bảng product_variants
        $requiredFields = [
            'product_id' => 'ID Sản phẩm gốc',
            'sku' => 'Mã SKU',
            'price' => 'Giá bán',
            'stock' => 'Số lượng tồn kho',
            'status' => 'Trạng thái hiển thị'
        ];

        // 2. kiểm tra bắt buộc
        foreach ($requiredFields as $field => $label) {
            if (!isset($postData[$field]) || (trim((string) $postData[$field]) === '' && $postData[$field] !== '0' && $postData[$field] !== 0)) {
                $errors[] = "Thiếu hoặc để trống thông tin: $label";
            }
        }

        // 3. kiểm tra kiểu dữ liệu (giá, số lượng phải là số)
        if (isset($postData['price'])) {
            if (!is_numeric($postData['price'])) {
                $errors[] = "Giá bán phải là một con số.";
            } else if ($postData['price'] < 0) {
                $errors[] = "Giá bán không được là số âm.";
            }
        }

        if (isset($postData['sale_price']) && trim((string) $postData['sale_price']) !== '') {
            if (!is_numeric($postData['sale_price'])) {
                $errors[] = "Giá khuyến mãi phải là một con số.";
            } else if ($postData['sale_price'] < 0) {
                $errors[] = "Giá khuyến mãi không được là số âm.";
            }
        }

        if (isset($postData['sale_price']) && is_numeric($postData['sale_price']) && isset($postData['price']) && is_numeric($postData['price'])) {
            if ($postData['sale_price'] > 0 && $postData['sale_price'] >= $postData['price']) {
                $errors[] = "Giá khuyến mãi không được lớn hơn hoặc bằng Giá gốc.";
            }
        }

        if (isset($postData['stock'])) {
            if (!is_numeric($postData['stock'])) {
                $errors[] = "Tồn kho phải là một con số.";
            } else if ($postData['stock'] < 0) {
                $errors[] = "Tồn kho không được là số âm.";
            }
        }

        // 4. kiểm tra ảnh đại diện [sửa 'main_image' thành 'main_image_url']
        // frontend javascript đã dùng key: formdata.append('main_image_url', uploadedfiles[0]);
        if (empty($postData['variant_id']) && empty($postData['main_image_url']) && empty($postData['main_image_url_existing']) && empty($fileData['main_image_url']['name'])) {
            $errors[] = "Thiếu ảnh đại diện cho phiên bản này (Vui lòng upload ít nhất 1 ảnh).";
        }

        // 5. kiểm tra mảng attributes
        // (đã loại bỏ yêu cầu bắt buộc phải có thuộc tính để hỗ trợ sp không có biến thể)
        if (!empty($postData['attributes'])) {
            $parsedAttributes = json_decode($postData['attributes'], true);
        }

        // (tùy chọn) 6. kiểm tra specs
        if (!empty($postData['variant_specs'])) {
            $parsedSpecs = json_decode($postData['variant_specs'], true);
            // có thể lặp qua $parsedspecs ở đây để check xem họ nhập value bị bỏ trống không
        }

        // 7. trả kết quả
        if (count($errors) > 0) {
            return [
                'status' => 'error',
                'messages' => $errors
            ];
        }

        return [
            'status' => 'success'
        ];
    }

    public function plusView()
    {
        $rawdata = file_get_contents("php:// input");
        $data = json_decode($rawdata, true);
        if (isset($data['sku']) && $data['sku']) {
            try {
                database::ThucThi(
                    "UPDATE product_variants SET view = view+1 WHERE sku=:sku",
                    ['sku' => $data['sku']]
                );
                echo json_encode(['status' => 'success']);
            } catch (Exception $e) {
                echo json_encode(['error' => $e->getMessage()]);
            }

        } else {
            echo json_encode(['status' => false]);
        }
    }

    public function getHot()
    {
        $data = database::ThucThiTraVe("
                SELECT cate.code,
                cate.name,
                br.brand_name,
                concat(pr.name,'-',pr_vr.variant_name) as name,
                pr_vr.price,
                pr_vr.sale_price,
                pr_vr.main_image_url,
                pr_vr.sku,
                pr_vr.view
                FROM products pr
                JOIN product_variants pr_vr on pr.id=pr_vr.product_id
                JOIN category cate on cate.id=pr.category_id
                JOIN brand br on br.id=pr.brand_id
                WHERE pr_vr.stock > 0
                ORDER BY (pr_vr.view + pr_vr.sold * 10) DESC
                LIMIT 16
            ") ?? [];

        for ($i = 0; $i < count($data); $i++) {
            $data[$i]['specs'] = database::ThucThiTraVe("
                    SELECT vr_s.spec_value
                    FROM variant_specs vr_s
                    JOIN specs s on s.spec_code = vr_s.spec_code
                    JOIN product_variants pr_vr on vr_s.variant_id = pr_vr.id
                    WHERE pr_vr.sku = :sku
                    LIMIT 4
                ", ['sku' => $data[$i]['sku']]);
        }

        self::applyPromotions($data);
        echo json_encode([
            "status" => "success",
            "data" => $data
        ]);
    }

    public function getNew()
    {
        $data = database::ThucThiTraVe("
                SELECT cate.code,
                cate.name,
                br.brand_name,
                concat(pr.name,'-',pr_vr.variant_name) as name,
                pr_vr.price,
                pr_vr.sale_price,
                pr_vr.main_image_url,
                pr_vr.sku,
                pr_vr.view
                FROM products pr
                JOIN product_variants pr_vr on pr.id=pr_vr.product_id
                JOIN category cate on cate.id=pr.category_id
                JOIN brand br on br.id=pr.brand_id
                WHERE pr_vr.stock > 0
                ORDER BY pr_vr.create_at DESC
                LIMIT 16
            ");
        for ($i = 0; $i < count($data); $i++) {
            $data[$i]['specs'] = database::ThucThiTraVe("
                    SELECT vr_s.spec_value
                    FROM variant_specs vr_s
                    JOIN specs s on s.spec_code = vr_s.spec_code
                    JOIN product_variants pr_vr on vr_s.variant_id = pr_vr.id
                    WHERE pr_vr.sku = :sku
                    LIMIT 4
                ", ['sku' => $data[$i]['sku']]);
        }

        self::applyPromotions($data);
        echo json_encode([
            "status" => "success",
            "data" => $data
        ]);
    }

    public function getSale()
    {
        $data = database::ThucThiTraVe("
                SELECT cate.code,
                cate.name,
                br.brand_name,
                concat(pr.name,'-',pr_vr.variant_name) as name,
                pr_vr.price,
                pr_vr.sale_price,
                pr_vr.main_image_url,
                pr_vr.sku,
                pr_vr.view
                FROM products pr
                JOIN product_variants pr_vr on pr.id=pr_vr.product_id
                JOIN category cate on cate.id=pr.category_id
                JOIN brand br on br.id=pr.brand_id
                WHERE pr_vr.sale_price IS NOT NULL
                AND pr_vr.stock > 0
                ORDER BY pr_vr.sale_price DESC, pr_vr.view DESC
                LIMIT 16
            ");
        for ($i = 0; $i < count($data); $i++) {
            $data[$i]['specs'] = database::ThucThiTraVe("
                    SELECT vr_s.spec_value
                    FROM variant_specs vr_s
                    JOIN specs s on s.spec_code = vr_s.spec_code
                    JOIN product_variants pr_vr on vr_s.variant_id = pr_vr.id
                    WHERE pr_vr.sku = :sku
                    LIMIT 4
                ", ['sku' => $data[$i]['sku']]);
        }

        self::applyPromotions($data);
        echo json_encode([
            "status" => "success",
            "data" => $data
        ]);
    }

    public function getFilter()
    {
        // 1. lấy dữ liệu đầu vào
        $category_code = isset($_GET['category']) ? trim($_GET['category']) : '';
        $specs_string = isset($_GET['specs']) ? trim($_GET['specs']) : '';

        if (empty($category_code) || empty($specs_string)) {
            echo json_encode(['status' => 'error', 'message' => 'Thiếu tham số', 'data' => []]);
            return;
        }

        // 2. xử lý mảng params để bind
        $spec_array = array_filter(array_map('trim', explode(',', $specs_string)));

        // khởi tạo mảng biến để truyền vào hàm thucthitrave
        $params = ['category_code' => $category_code];
        $placeholders = [];

        // tự động sinh ra các biến bind cho mệnh đề in. vd: :spec_0, :spec_1
        foreach ($spec_array as $index => $spec) {
            $param_key = 'spec_' . $index;
            $placeholders[] = ':' . $param_key;
            $params[$param_key] = $spec;
        }

        $in_clause = implode(',', $placeholders); // ra được chuỗi ":spec_0, :spec_1"

        // 3. xây dựng câu sql (code siêu sạch, không nối chuỗi dữ liệu)
        $sql = "
            SELECT s.spec_code, s.spec_name, vs.spec_value
            FROM specs s
            JOIN variant_specs vs ON s.spec_code = vs.spec_code
            JOIN product_variants pv ON vs.variant_id = pv.id
            JOIN products p ON pv.product_id = p.id
            JOIN category c ON p.category_id = c.id 
            WHERE c.code = :category_code 
            AND s.spec_code IN ($in_clause)
            GROUP BY s.spec_code, s.spec_name, vs.spec_value
            ORDER BY s.spec_code, vs.spec_value ASC
        ";

        try {
            // 4. thực thi bằng hàm custom có bind params của fen
            $raw_data = database::ThucThiTraVe($sql, $params);

            // 5. format lại data
            $formatted_data = [];
            if ($raw_data) {
                foreach ($raw_data as $row) {
                    $code = $row['spec_code'];
                    if (!isset($formatted_data[$code])) {
                        $formatted_data[$code] = [
                            'name' => $row['spec_name'],
                            'values' => []
                        ];
                    }
                    $formatted_data[$code]['values'][] = $row['spec_value'];
                }
            }

            self::applyPromotions($formatted_data);
            echo json_encode(['status' => 'success', 'data' => $formatted_data]);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'Lỗi DB: ' . $e->getMessage()]);
        }
    }

    // các api quản lý thương hiệu

    public function getAllBrands()
    {
        $data = database::ThucThiTraVe("SELECT id, brand_name, logo_url FROM brand ORDER BY brand_name ASC");
        echo json_encode(['status' => 'success', 'data' => $data ?? []]);
    }

    public function createBrand()
    {
        $brand_name = trim($_POST['brand_name'] ?? '');
        if (!$brand_name) {
            echo json_encode(['status' => 'error', 'message' => 'Tên thương hiệu không được để trống']);
            return;
        }

        // kiểm tra trùng lặp
        $exists = database::ThucThiTraVe("SELECT id FROM brand WHERE brand_name = :n", ['n' => $brand_name]);
        if (!empty($exists)) {
            echo json_encode(['status' => 'error', 'message' => 'Thương hiệu đã tồn tại']);
            return;
        }

        $logoPath = null;

        // tải logo lên (bắt buộc)
        if (empty($_FILES['logo']['tmp_name'])) {
            echo json_encode(['status' => 'error', 'message' => 'Vui lòng upload ảnh đại diện thương hiệu']);
            return;
        }

        $ext = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
            echo json_encode(['status' => 'error', 'message' => 'Chỉ hỗ trợ JPG, PNG, WEBP']);
            return;
        }

        $uploadDir = ROOT_DIR . '/Front-end/public/img/brands/';
        if (!is_dir($uploadDir))
            mkdir($uploadDir, 0777, true);

        $filename = 'brand_' . time() . '_' . preg_replace('/[^a-z0-9]/', '_', strtolower($brand_name)) . '.' . $ext;
        $dest = $uploadDir . $filename;

        // chỉnh lại cỡ ảnh về 250px dùng thư viện gd
        $src = null;
        if ($ext === 'jpg' || $ext === 'jpeg')
            $src = imagecreatefromjpeg($_FILES['logo']['tmp_name']);
        elseif ($ext === 'png')
            $src = imagecreatefrompng($_FILES['logo']['tmp_name']);
        elseif ($ext === 'webp')
            $src = imagecreatefromwebp($_FILES['logo']['tmp_name']);

        if ($src) {
            $origW = imagesx($src);
            $origH = imagesy($src);
            $newW = 250;
            $newH = (int) round($origH * $newW / $origW);
            $dst = imagecreatetruecolor($newW, $newH);
            // giữ độ trong suốt cho file png/webp
            imagealphablending($dst, false);
            imagesavealpha($dst, true);
            imagecopyresampled($dst, $src, 0, 0, 0, 0, $newW, $newH, $origW, $origH);
            if ($ext === 'png')
                imagepng($dst, $dest);
            elseif ($ext === 'webp')
                imagewebp($dst, $dest);
            else
                imagejpeg($dst, $dest, 90);
            imagedestroy($src);
            imagedestroy($dst);
            $logoPath = 'img/brands/' . $filename;
        } else {
            // phương án dự phòng: chỉ di chuyển file
            move_uploaded_file($_FILES['logo']['tmp_name'], $dest);
            $logoPath = 'img/brands/' . $filename;
        }

        database::ThucThi(
            "INSERT INTO brand (brand_name, logo_url) VALUES (:n, :l)",
            ['n' => $brand_name, 'l' => $logoPath]
        );

        $newId = database::ThucThiTraVe("SELECT id FROM brand WHERE brand_name = :n ORDER BY id DESC LIMIT 1", ['n' => $brand_name]);
        echo json_encode(['status' => 'success', 'message' => 'Đã tạo thương hiệu', 'id' => $newId[0]['id'] ?? null, 'logo_url' => $logoPath]);
    }
    public function updateBrand()
    {
        $id = $_POST['id'] ?? null;
        $brand_name = trim($_POST['brand_name'] ?? '');
        if (!$id || !$brand_name) {
            echo json_encode(['status' => 'error', 'message' => 'Tên thương hiệu và ID không được để trống']);
            return;
        }

        $logoPath = null;
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] == UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
            $filename = uniqid('brand_') . '.' . $ext;
            
            $uploadDir = ROOT_DIR . '/Front-end/public/img/brands/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            
            $dest = $uploadDir . $filename;
            if (move_uploaded_file($_FILES['logo']['tmp_name'], $dest)) {
                $logoPath = 'img/brands/' . $filename;
            }
        }

        if ($logoPath) {
            database::ThucThi("UPDATE brand SET brand_name = :n, logo_url = :l WHERE id = :id", ['n' => $brand_name, 'l' => $logoPath, 'id' => $id]);
        } else {
            database::ThucThi("UPDATE brand SET brand_name = :n WHERE id = :id", ['n' => $brand_name, 'id' => $id]);
        }
        echo json_encode(['status' => 'success', 'message' => 'Đã cập nhật thương hiệu']);
    }

    public function deleteBrand()
    {
        $raw = file_get_contents('php:// input');
        $body = json_decode($raw, true);
        $id = $body['id'] ?? null;
        if (!$id) {
            echo json_encode(['status' => 'error', 'message' => 'Thiếu ID thương hiệu']);
            return;
        }

        $check = database::ThucThiTraVe("SELECT id FROM products WHERE brand_id = :id LIMIT 1", ['id' => $id]);
        if (!empty($check)) {
            echo json_encode(['status' => 'error', 'message' => 'Không thể xóa hãng đang có sản phẩm']);
            return;
        }

        database::ThucThi("DELETE FROM brand WHERE id = :id", ['id' => $id]);
        echo json_encode(['status' => 'success', 'message' => 'Đã xóa thương hiệu']);
    }

    public function createCategory()
    {
        $raw = file_get_contents('php:// input');
        $body = json_decode($raw, true);
        $name = trim($body['name'] ?? '');
        $code = trim($body['code'] ?? '');
        
        if (!$name || !$code) {
            echo json_encode(['status' => 'error', 'message' => 'Điền đầy đủ tên và mã danh mục']);
            return;
        }

        $check = database::ThucThiTraVe("SELECT id FROM category WHERE code = :c", ['c' => $code]);
        if (!empty($check)) {
            echo json_encode(['status' => 'error', 'message' => 'Mã danh mục đã tồn tại']);
            return;
        }

        database::ThucThi("INSERT INTO category (name, code) VALUES (:n, :c)", ['n' => $name, 'c' => $code]);
        echo json_encode(['status' => 'success', 'message' => 'Đã thêm danh mục']);
    }

    public function updateCategory()
    {
        $raw = file_get_contents('php:// input');
        $body = json_decode($raw, true);
        $id = $body['id'] ?? null;
        $name = trim($body['name'] ?? '');
        $code = trim($body['code'] ?? '');
        
        if (!$id || !$name || !$code) {
            echo json_encode(['status' => 'error', 'message' => 'Thiếu thông tin cập nhật']);
            return;
        }

        $check = database::ThucThiTraVe("SELECT id FROM category WHERE code = :c AND id != :id", ['c' => $code, 'id' => $id]);
        if (!empty($check)) {
            echo json_encode(['status' => 'error', 'message' => 'Mã danh mục đã tồn tại ở mục khác']);
            return;
        }

        database::ThucThi("UPDATE category SET name = :n, code = :c WHERE id = :id", ['n' => $name, 'c' => $code, 'id' => $id]);
        echo json_encode(['status' => 'success', 'message' => 'Đã cập nhật danh mục']);
    }

    public function deleteCategory()
    {
        $raw = file_get_contents('php:// input');
        $body = json_decode($raw, true);
        $id = $body['id'] ?? null;
        
        if (!$id) {
            echo json_encode(['status' => 'error', 'message' => 'Thiếu ID danh mục']);
            return;
        }

        $check = database::ThucThiTraVe("SELECT id FROM products WHERE category_id = :id LIMIT 1", ['id' => $id]);
        if (!empty($check)) {
            echo json_encode(['status' => 'error', 'message' => 'Không thể xóa danh mục đang có sản phẩm']);
            return;
        }

        database::ThucThi("DELETE FROM category WHERE id = :id", ['id' => $id]);
        echo json_encode(['status' => 'success', 'message' => 'Đã xóa danh mục']);
    }

    // các api quản lý sản phẩm gốc

    public function getAdminProducts()
    {
        $data = database::ThucThiTraVe("
            SELECT p.id, p.name, p.brand_id, p.category_id,
                   b.brand_name, b.logo_url as brand_logo,
                   c.name as category_name,
                   COUNT(pv.id) as variant_count,
                   COUNT(CASE WHEN pv.id IS NOT NULL AND pv.delete_at IS NULL THEN 1 END) as active_variant_count,
                   p.created_at
            FROM products p
            LEFT JOIN brand b ON b.id = p.brand_id
            LEFT JOIN category c ON c.id = p.category_id
            LEFT JOIN product_variants pv ON pv.product_id = p.id
            WHERE p.deleted_at IS NULL
            GROUP BY p.id
            ORDER BY p.id DESC
        ");
        echo json_encode(['status' => 'success', 'data' => $data ?? []]);
    }

    public function createAdminProduct()
    {
        $raw = file_get_contents('php:// input');
        $body = json_decode($raw, true);

        // dùng $_post nếu là request kiểu form-encoded
        $name = trim($body['name'] ?? $_POST['name'] ?? '');
        $brand_id = $body['brand_id'] ?? $_POST['brand_id'] ?? null;
        $category = $body['category'] ?? null;

        if (!$name) {
            echo json_encode(['status' => 'error', 'message' => 'Tên sản phẩm không được để trống']);
            return;
        }
        if (!$category) {
            echo json_encode(['status' => 'error', 'message' => 'Vui lòng chọn danh mục']);
            return;
        }

        // xử lý lấy category_id
        $category_id = null;
        if (!empty($category['is_new'])) {
            $catName = trim($category['name'] ?? '');
            $catCode = trim($category['code'] ?? '');
            if (!$catName || !$catCode) {
                echo json_encode(['status' => 'error', 'message' => 'Điền đủ thông tin danh mục mới']);
                return;
            }
            $exists = database::ThucThiTraVe("SELECT id FROM category WHERE code = :c", ['c' => $catCode]);
            if (!empty($exists)) {
                $category_id = $exists[0]['id'];
            } else {
                database::ThucThi("INSERT INTO category (name, code) VALUES (:n, :c)", ['n' => $catName, 'c' => $catCode]);
                $catRow = database::ThucThiTraVe("SELECT id FROM category WHERE code = :c ORDER BY id DESC LIMIT 1", ['c' => $catCode]);
                $category_id = $catRow[0]['id'] ?? null;
            }
        } else {
            $category_id = $category['id'] ?? null;
        }

        if (!$category_id) {
            echo json_encode(['status' => 'error', 'message' => 'Danh mục không hợp lệ']);
            return;
        }

        $now = date('Y-m-d H:i:s');
        database::ThucThi(
            "INSERT INTO products (name, brand_id, category_id, created_at) VALUES (:n, :b, :c, :t)",
            ['n' => $name, 'b' => $brand_id ?: null, 'c' => $category_id, 't' => $now]
        );
        $newRow = database::ThucThiTraVe("SELECT id FROM products WHERE name=:n ORDER BY id DESC LIMIT 1", ['n' => $name]);
        echo json_encode(['status' => 'success', 'message' => 'Đã tạo sản phẩm gốc', 'id' => $newRow[0]['id'] ?? null]);
    }

    // các api quản lý biến thể
    
    public function updateAdminProduct()
    {
        $raw = file_get_contents('php:// input');
        $body = json_decode($raw, true);

        $id = $body['id'] ?? null;
        $name = trim($body['name'] ?? '');
        $brand_id = $body['brand_id'] ?? null;
        $category_id = $body['category_id'] ?? null;

        if (!$id || !$name || !$category_id) {
            echo json_encode(['status' => 'error', 'message' => 'Thiếu thông tin bắt buộc']);
            return;
        }

        try {
            database::ThucThi("UPDATE products SET name = :n, brand_id = :b, category_id = :c WHERE id = :id", [
                'n' => $name,
                'b' => $brand_id ?: null,
                'c' => $category_id,
                'id' => $id
            ]);
            echo json_encode(['status' => 'success', 'message' => 'Đã cập nhật sản phẩm']);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    // các api quản lý biến thể

    public function getAdminVariants()
    {
        $forPromo = isset($_GET['for_promo']) ? $_GET['for_promo'] : false;
        $excludePromoId = isset($_GET['exclude_promo_id']) ? $_GET['exclude_promo_id'] : null;

        $showDeleted = isset($_GET['show_deleted']) ? $_GET['show_deleted'] : true;
        $whereClause = $showDeleted ? "WHERE 1=1" : "WHERE pv.delete_at IS NULL";
        $params = [];

        if ($forPromo) {
            $whereClause .= " AND pv.stock > 0 AND pv.sku NOT IN (
                SELECT pi.sku FROM promotion_items pi
                JOIN promotions p ON p.id = pi.promotion_id
                WHERE p.status = 'active'
                " . ($excludePromoId ? "AND p.id != :exclude_promo_id" : "") . "
            )";
            if ($excludePromoId) {
                $params['exclude_promo_id'] = $excludePromoId;
            }
        }

        $sql = "
            SELECT pv.id, pv.sku, pv.variant_name as name,
                   p.id as product_id, p.name as root_product,
                   c.name as category_name, c.id as category_id,
                   pv.price, pv.sale_price, pv.stock, pv.min_stock, pv.status, pv.delete_at,
                   pv.main_image_url, pv.sold
            FROM product_variants pv
            JOIN products p ON pv.product_id = p.id
            JOIN category c ON p.category_id = c.id
            $whereClause
            ORDER BY pv.id DESC
        ";
        try {
            $data = database::ThucThiTraVe($sql, $params) ?? [];
            echo json_encode(['status' => 'success', 'data' => $data]);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function deleteVariant()
    {
        // hỗ trợ cả file_get_contents cho json payload và $_get
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $raw = file_get_contents("php:// input");
            $data = json_decode($raw, true);
            $id = $data['id'] ?? null;
        }

        if (!$id) {
            echo json_encode(['status' => 'error', 'message' => 'Thiếu ID biến thể']);
            return;
        }

        // ưu tiên role admin gửi lên để test, nếu ko có thì là nhân viên (soft delete)
        $role = $_GET['role'] ?? 2;

        try {
            if ($role == 1) { // admin -> xóa cứng
                database::beginTransaction();
                database::ThucThi("DELETE FROM variant_images WHERE variant_id = :id", ['id' => $id]);
                database::ThucThi("DELETE FROM variant_specs WHERE variant_id = :id", ['id' => $id]);
                database::ThucThi("DELETE FROM variant_attribute_values WHERE variant_id = :id", ['id' => $id]);
                database::ThucThi("DELETE FROM product_variants WHERE id = :id", ['id' => $id]);
                database::commit();
                echo json_encode(['status' => 'success', 'message' => 'Đã xóa cứng vĩnh viễn']);
            } else { // nhân viên -> xóa mềm
                database::ThucThi("UPDATE product_variants SET delete_at = :time WHERE id = :id", [
                    'time' => date('Y-m-d H:i:s'),
                    'id' => $id
                ]);
                echo json_encode(['status' => 'success', 'message' => 'Đã chuyển vào thùng rác (Xóa mềm)']);
            }
        } catch (Exception $e) {
            if ($role == 1)
                database::rollBack();
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function toggleProductStatus()
    {
        $raw = file_get_contents("php:// input");
        $data = json_decode($raw, true);
        $id = $data['id'] ?? null;
        $action = $data['action'] ?? 'stop'; // 'stop' or 'start'

        if (!$id) {
            echo json_encode(['status' => 'error', 'message' => 'Thiếu ID sản phẩm']);
            return;
        }

        try {
            $time = ($action === 'stop') ? date('Y-m-d H:i:s') : null;
            
            database::ThucThi("UPDATE product_variants SET delete_at = :time WHERE product_id = :id", [
                'time' => $time,
                'id' => $id
            ]);
            $msg = ($action === 'stop') ? 'Đã ngừng bán tất cả biến thể' : 'Đã mở bán lại tất cả biến thể';
            echo json_encode(['status' => 'success', 'message' => $msg]);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function toggleVariantStatus()
    {
        $raw = file_get_contents("php:// input");
        $data = json_decode($raw, true);
        $id = $data['id'] ?? null;
        $action = $data['action'] ?? 'stop'; // 'stop' or 'start'

        if (!$id) {
            echo json_encode(['status' => 'error', 'message' => 'Thiếu ID biến thể']);
            return;
        }

        try {
            $time = ($action === 'stop') ? date('Y-m-d H:i:s') : null;
            database::ThucThi("UPDATE product_variants SET delete_at = :time WHERE id = :id", [
                'time' => $time,
                'id' => $id
            ]);
            $msg = ($action === 'stop') ? 'Đã ngừng bán biến thể này' : 'Đã mở bán lại biến thể này';
            echo json_encode(['status' => 'success', 'message' => $msg]);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function delete()
    {
        echo json_encode(['status' => 'error', 'message' => 'Thao tác xóa bị từ chối. Vui lòng sử dụng chức năng Ngừng bán / Bán lại.']);
    }

    public function getVariantFirstDescription()
    {
        $product_id = $_GET['product_id'] ?? null;
        if (!$product_id) {
            echo json_encode(['status' => 'error', 'message' => 'Thiếu ID sản phẩm']);
            return;
        }

        try {
            $data = database::ThucThiTraVe("SELECT description FROM product_variants WHERE product_id = :pid AND delete_at IS NULL AND description IS NOT NULL AND description != '' ORDER BY id ASC LIMIT 1", ['pid' => $product_id]);
            if (!empty($data) && isset($data[0]['description'])) {
                echo json_encode(['status' => 'success', 'data' => $data[0]['description']]);
            } else {
                echo json_encode(['status' => 'success', 'data' => '']);
            }
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function getVariantDetail()
    {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            echo json_encode(['status' => 'error', 'message' => 'Thiếu ID']);
            return;
        }

        try {
            $v = database::ThucThiTraVe("SELECT * FROM product_variants WHERE id = :id", ['id' => $id]);
            if (!$v || empty($v)) {
                echo json_encode(['status' => 'error', 'message' => 'Không tìm thấy variant']);
                return;
            }
            $variant = $v[0];

            $attrs = database::ThucThiTraVe("
                SELECT a.name as attr_name, a.id as attr_id, a.code as attr_code, 
                       av.value as val_name, av.id as val_id
                FROM variant_attribute_values vav
                JOIN attribute_value av ON vav.attribute_value_id = av.id
                JOIN attributes a ON av.attribute_code = a.code
                WHERE vav.variant_id = :id
            ", ['id' => $id]);

            $attributesList = [];
            if (!empty($attrs)) {
                foreach ($attrs as $a) {
                    $attributesList[] = [
                        'attribute' => ['id' => $a['attr_id'], 'name' => $a['attr_name'], 'code' => $a['attr_code']],
                        'value' => ['id' => $a['val_id'], 'value' => $a['val_name']]
                    ];
                }
            }

            $specsArr = database::ThucThiTraVe("
                SELECT s.spec_code, s.spec_name, vs.spec_value 
                FROM variant_specs vs
                JOIN specs s ON vs.spec_code = s.spec_code
                WHERE vs.variant_id = :id
            ", ['id' => $id]);

            $imgs = database::ThucThiTraVe("SELECT detail_image_url FROM variant_images WHERE variant_id = :id", ['id' => $id]);
            $subImages = [];
            if (!empty($imgs)) {
                foreach ($imgs as $img) {
                    $subImages[] = $img['detail_image_url'];
                }
            }

            $result = [
                'basic' => $variant,
                'attributes' => $attributesList,
                'specs' => $specsArr ?? [],
                'images' => $subImages
            ];

            echo json_encode(['status' => 'success', 'data' => $result]);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function updateVariantStock()
    {
        $variant_id = $_POST['variant_id'] ?? null;
        $stock = $_POST['stock'] ?? 0;
        $min_stock = $_POST['min_stock'] ?? 0;

        if (!$variant_id) {
            echo json_encode(['status' => 'error', 'message' => 'Thiếu ID biến thể']);
            return;
        }

        try {
            database::ThucThi(
                "UPDATE product_variants SET stock = :s, min_stock = :ms WHERE id = :id",
                ['s' => $stock, 'ms' => $min_stock, 'id' => $variant_id]
            );
            echo json_encode(['status' => 'success', 'message' => 'Cập nhật tồn kho thành công']);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function updateVariant()
    {
        $validation = $this->validateVariantInput($_POST, $_FILES);
        if ($validation['status'] === 'error') {
            echo json_encode($validation);
            return;
        }

        $variant_id = $_POST['variant_id'] ?? null;
        if (!$variant_id) {
            echo json_encode(['status' => 'error', 'message' => 'Thiếu ID biến thể cần sửa']);
            return;
        }

        $allowedFields = ['product_id', 'variant_name', 'sku', 'price', 'sale_price', 'stock', 'min_stock', 'status', 'description'];
        $updates = [];
        $params = ['id' => $variant_id];
        $sku = $_POST['sku'] ?? 'unknown_sku';

        foreach ($allowedFields as $field) {
            if (isset($_POST[$field])) {
                $val = $_POST[$field];
                if ($val === '') {
                    $updates[] = "$field = NULL";
                } else {
                    $updates[] = "$field = :$field";
                    $params[$field] = $val;
                }
            } else {
                $updates[] = "$field = NULL";
            }
        }

        $attributes = isset($_POST['attributes']) ? json_decode($_POST['attributes'], true) : [];
        $specs = isset($_POST['variant_specs']) ? json_decode($_POST['variant_specs'], true) : [];

        $upload_dir = __DIR__ . '/../../Front-end/public/img/products/';
        $filesToMove = [];
        $main_image_url_db = null;

        if (!empty($_FILES['main_image_url']['name'])) {
            $ext = strtolower(pathinfo($_FILES['main_image_url']['name'], PATHINFO_EXTENSION));
            $new_filename = $_POST['sku'] . '_main_' . time() . '.' . $ext;
            $target_file = $upload_dir . $new_filename;
            $filesToMove[$_FILES['main_image_url']['tmp_name']] = $target_file;
            $main_image_url_db = 'img/products/' . $new_filename;
        } else if (!empty($_POST['main_image_url_existing'])) {
            $main_image_url_db = $_POST['main_image_url_existing'];
        }

        try {
            // kiểm tra trùng sku
            if (isset($_POST['sku'])) {
                $skuCheck = database::ThucThiTraVe("SELECT id FROM product_variants WHERE sku = :sku AND id != :id", ['sku' => $_POST['sku'], 'id' => $variant_id]);
                if (!empty($skuCheck)) {
                    echo json_encode([
                        'status' => 'error',
                        'messages' => ['Mã SKU này đã tồn tại trong hệ thống. Vui lòng sử dụng mã khác.']
                    ]);
                    return;
                }
            }

            database::beginTransaction();

            if ($main_image_url_db) {
                $updates[] = "main_image_url = :imgUrl";
                $params['imgUrl'] = $main_image_url_db;
            }

            $setQuery = implode(', ', $updates);
            database::ThucThi("UPDATE product_variants SET $setQuery WHERE id=:id", $params);

            // xóa rễ specs & attributes cũ rồi trồng lại cây mới cho nhàn!
            database::ThucThi("DELETE FROM variant_attribute_values WHERE variant_id = :id", ['id' => $variant_id]);
            if (!empty($attributes)) {
                foreach ($attributes as $item) {
                    $attr_id = $item['attribute']['id'] ?? null;
                    $val_id = $item['value']['id'] ?? null;
                    $attr_code = '';

                    if (!empty($item['attribute']['is_new'])) {
                        $name = $item['attribute']['name'];
                        $attr_code = $this->toSlug($name);
                        $checkCode = database::ThucThiTraVe("SELECT id FROM attributes WHERE code = :code", ['code' => $attr_code]);
                        if (empty($checkCode)) {
                            database::ThucThi("INSERT INTO attributes (code, name) VALUES (:code, :name)", ['code' => $attr_code, 'name' => $name]);
                        }
                    } else if ($attr_id) {
                        $row = database::ThucThiTraVe("SELECT code FROM attributes WHERE id = :id", ['id' => $attr_id]);
                        if (!empty($row))
                            $attr_code = $row[0]['code'];
                    }

                    if (!empty($item['value']['is_new']) && $attr_code !== '') {
                        $value_name = $item['value']['value'];
                        database::ThucThi("INSERT INTO attribute_value (attribute_code, value) VALUES (:attr_code, :val_name)", ['attr_code' => $attr_code, 'val_name' => $value_name]);
                        $valData = database::ThucThiTraVe("SELECT id FROM attribute_value WHERE attribute_code = :attr_code AND value = :val_name ORDER BY id DESC LIMIT 1", ['attr_code' => $attr_code, 'val_name' => $value_name]);
                        $val_id = $valData[0]['id'];
                    }

                    if ($val_id && $variant_id) {
                        database::ThucThi("INSERT INTO variant_attribute_values (variant_id, attribute_value_id) VALUES (:var_id, :val_id)", ['var_id' => $variant_id, 'val_id' => $val_id]);
                    }
                }
            }

            database::ThucThi("DELETE FROM variant_specs WHERE variant_id = :id", ['id' => $variant_id]);
            if (!empty($specs)) {
                foreach ($specs as $spec) {
                    $spec_code = $spec['spec_code'] ?? null;
                    if (!empty($spec['_is_new'])) {
                        $spec_name = $spec['spec_name'];
                        if (!$spec_code)
                            $spec_code = str_replace('-', '_', $this->toSlug($spec_name));
                        $checkSpec = database::ThucThiTraVe("SELECT id FROM specs WHERE spec_code = :spec_code", ['spec_code' => $spec_code]);
                        if (empty($checkSpec)) {
                            database::ThucThi("INSERT INTO specs (spec_code, spec_name) VALUES (:s_code, :s_name)", ['s_code' => $spec_code, 's_name' => $spec_name]);
                        }
                    }
                    if ($spec_code && trim((string) $spec['spec_value']) !== '') {
                        database::ThucThi("INSERT INTO variant_specs (variant_id, spec_code, spec_value) VALUES (:var_id, :s_code, :s_val)", [
                            'var_id' => $variant_id,
                            's_code' => $spec_code,
                            's_val' => $spec['spec_value']
                        ]);
                    }
                }
            }

            // hình ảnh phụ
            // chỉ xóa ảnh cũ nếu có cung cấp ảnh mới hoặc ảnh hiện tại
            if ((isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) || !empty($_POST['images_existing'])) {
                database::ThucThi("DELETE FROM variant_images WHERE variant_id = :id", ['id' => $variant_id]);

                if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
                    $total_files = count($_FILES['images']['name']);
                    for ($i = 0; $i < $total_files; $i++) {
                        $tmp_name = $_FILES['images']['tmp_name'][$i];
                        if (!$tmp_name)
                            continue;
                        $ext = strtolower(pathinfo($_FILES['images']['name'][$i], PATHINFO_EXTENSION));
                        $new_filename = $sku . '_sub_' . time() . '_' . $i . '.' . $ext;
                        $filesToMove[$tmp_name] = $upload_dir . $new_filename;
                        database::ThucThi("INSERT INTO variant_images (variant_id, detail_image_url) VALUES (:var_id, :url)", ['var_id' => $variant_id, 'url' => 'img/products/' . $new_filename]);
                    }
                }

                if (!empty($_POST['images_existing'])) {
                    $existing_imgs = json_decode($_POST['images_existing'], true);
                    if (is_array($existing_imgs)) {
                        foreach ($existing_imgs as $img_url) {
                            database::ThucThi("INSERT INTO variant_images (variant_id, detail_image_url) VALUES (:var_id, :url)", ['var_id' => $variant_id, 'url' => $img_url]);
                        }
                    }
                }
            }

            database::commit();

            foreach ($filesToMove as $tmp => $dest) {
                move_uploaded_file($tmp, $dest);
            }
            echo json_encode(['status' => 'success', 'message' => 'Cập nhật biến thể thành công!']);

        } catch (Exception $e) {
            database::rollBack();
            echo json_encode(['status' => 'error', 'message' => 'Lỗi cập nhật: ' . $e->getMessage()]);
        }
    }
    public function getReviews()
    {
        $sku = $_GET['sku'] ?? null;
        if (!$sku) {
            echo json_encode(['status' => 'error', 'message' => 'Thiếu SKU sản phẩm']);
            return;
        }
        $pv = database::ThucThiTraVe("SELECT product_id FROM product_variants WHERE sku = :sku", ['sku' => $sku]);
        if (empty($pv)) {
            echo json_encode(['status' => 'error', 'message' => 'Sản phẩm không tồn tại']);
            return;
        }
        $product_id = $pv[0]['product_id'];

        $reviews = database::ThucThiTraVe("SELECT r.*, u.user_name as fullname, u.avatar_url as avatar FROM product_reviews r JOIN user u ON r.user_id = u.id WHERE r.product_id = :id ORDER BY r.created_at DESC", ['id' => $product_id]);

        echo json_encode(['status' => 'success', 'data' => $reviews ?? []]);
    }

    public function addReview()
    {
        if (!isset($_SESSION['user'])) {
            echo json_encode(['status' => 'unauthenticated', 'message' => 'Vui lòng đăng nhập']);
            return;
        }

        $raw = file_get_contents("php:// input");
        $data = json_decode($raw, true);

        $sku = $data['sku'] ?? null;
        $rating = $data['rating'] ?? 5;
        $content = $data['content'] ?? '';
        $order_detail_id = $data['order_detail_id'] ?? null;
        $variant_snapshot = $data['variant_snapshot'] ?? null;

        if (!$sku || !$rating) {
            echo json_encode(['status' => 'error', 'message' => 'Dữ liệu không hợp lệ']);
            return;
        }

        $pv = database::ThucThiTraVe("SELECT product_id FROM product_variants WHERE sku = :sku", ['sku' => $sku]);
        if (empty($pv)) {
            echo json_encode(['status' => 'error', 'message' => 'Sản phẩm không tồn tại']);
            return;
        }
        $product_id = $pv[0]['product_id'];
        $user_id = $_SESSION['user']['id'];

        // nếu review trực tiếp từ history (có gửi kèm order_detail_id)
        if ($order_detail_id) {
            $checkOrder = database::ThucThiTraVe("
                SELECT 1 FROM orders o
                JOIN order_detail od ON o.id = od.order_id
                WHERE od.id = :od_id AND o.user_id = :uid AND o.status = 'completed' AND od.sku = :sku LIMIT 1
            ", ['od_id' => $order_detail_id, 'uid' => $user_id, 'sku' => $sku]);

            if (empty($checkOrder)) {
                echo json_encode(['status' => 'error', 'message' => 'Đơn hàng không hợp lệ, sản phẩm không thuộc đơn này, hoặc đơn chưa hoàn thành.']);
                return;
            }
        }

        try {
            database::ThucThi("INSERT INTO product_reviews (product_id, user_id, order_detail_id, rating, content, variant_snapshot) VALUES (:pid, :uid, :odid, :rating, :content, :snapshot)", [
                'pid' => $product_id,
                'uid' => $user_id,
                'odid' => $order_detail_id ?: null,
                'rating' => $rating,
                'content' => $content,
                'snapshot' => $variant_snapshot
            ]);

            echo json_encode(['status' => 'success', 'message' => 'Cảm ơn bạn đã đánh giá!']);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Lỗi hệ thống khi lưu đánh giá.', "e" => $e]);
        }
    }
    public function getRelatedForCart()
    {
        $raw = file_get_contents("php:// input");
        $data = json_decode($raw, true);
        $skus = $data['skus'] ?? [];

        $whereClause = "WHERE pr_vr.status = 'active'";
        $finalParams = [];

        if (!empty($skus)) {
            // lấy danh sách category_id của các sản phẩm trong giỏ
            $placeholders = [];
            $tempParams = [];
            foreach ($skus as $index => $sku) {
                $key = "sku_$index";
                $placeholders[] = ":$key";
                $tempParams[$key] = $sku;
            }
            $placeholderStr = implode(',', $placeholders);

            $cateIds = database::ThucThiTraVe("
                SELECT DISTINCT category_id 
                FROM products p 
                JOIN product_variants pv ON p.id = pv.product_id 
                WHERE pv.sku IN ($placeholderStr)
            ", $tempParams);

            if (!empty($cateIds)) {
                $ids = array_column($cateIds, 'category_id');
                $idList = implode(',', $ids);
                $whereClause .= " AND pr.category_id IN ($idList)";

                // loại trừ các sản phẩm đã có trong giỏ
                foreach ($skus as $index => $sku) {
                    $whereClause .= " AND pr_vr.sku != :exclude_$index";
                    $finalParams["exclude_$index"] = $sku;
                }
            }
        }

        $info = database::ThucThiTraVe("
            SELECT DISTINCT
                cate.code as category_code,
                cate.name as category_name,
                concat(pr.name,' ',pr_vr.variant_name) as name,
                pr.brand_id,
                pr_vr.price,
                pr_vr.sale_price,
                pr_vr.stock,
                pr_vr.main_image_url,
                pr_vr.sku,
                pr_vr.status,
                pr_vr.sold,
                b.brand_name
            FROM products pr
            JOIN product_variants pr_vr ON pr.id = pr_vr.product_id
            JOIN category cate ON cate.id = pr.category_id
            LEFT JOIN brand b ON pr.brand_id = b.id
            $whereClause
            ORDER BY pr_vr.sold DESC
            LIMIT 8
        ", $finalParams) ?? [];

        // lấy thêm specs cho mỗi sản phẩm để render ở home card
        for ($i = 0; $i < count($info); $i++) {
            $info[$i]['specs'] = database::ThucThiTraVe("
                SELECT 
                    vr_s.spec_value,
                    s.spec_code
                FROM variant_specs vr_s
                JOIN specs s ON s.spec_code = vr_s.spec_code
                JOIN product_variants pr_vr ON vr_s.variant_id = pr_vr.id
                WHERE sku = :sku
                LIMIT 4
            ", ['sku' => $info[$i]['sku']]);
        }

        self::applyPromotions($info);
        echo json_encode([
            "status" => "success",
            "data" => $info
        ]);
    }

    /**
     * Apply active promotions to modify sale_price dynamically for an array of products
     */
    public static function applyPromotions(&$products)
    {
        if (empty($products)) return;

        try {
            // lấy tất cả chiến dịch đang active
            $activePromos = database::ThucThiTraVe("
                SELECT id, discount_percent, discount_amount
                FROM promotions
                WHERE status = 'active'
                  AND NOW() BETWEEN start_time AND end_time
                LIMIT 3
            ");

            if (empty($activePromos)) return;

            foreach ($activePromos as $promo) {
                $promoId = $promo['id'];
                $items = database::ThucThiTraVe("SELECT sku, original_sale_price FROM promotion_items WHERE promotion_id = :id", ['id' => $promoId]);
                
                $promoData = [];
                foreach ($items as $item) {
                    $promoData[$item['sku']] = $item['original_sale_price'];
                }

                foreach ($products as &$p) {
                    if (isset($p['sku']) && isset($promoData[$p['sku']])) {
                        $p['is_promo'] = true;
                        $origSale = $promoData[$p['sku']];
                        if (!empty($origSale) && $origSale > 0) {
                            $p['origin_price_display'] = $origSale;
                        } else {
                            $p['origin_price_display'] = $p['price'];
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            // bỏ qua lỗi thiếu bảng khi chưa migration
        }
    }

    /**
     * Apply active promotions to a single product object
     */
    public static function applyPromotionToSingle(&$product)
    {
        if (empty($product) || !isset($product['sku'])) return;

        try {
            $activePromos = database::ThucThiTraVe("
                SELECT pm.discount_percent, pm.discount_amount, pi.original_sale_price
                FROM promotions pm
                JOIN promotion_items pi ON pm.id = pi.promotion_id
                WHERE pm.status = 'active'
                  AND NOW() BETWEEN pm.start_time AND pm.end_time
                  AND pi.sku = :sku
                LIMIT 1
            ", ['sku' => $product['sku']]);

            if (!empty($activePromos)) {
                $promo = $activePromos[0];
                $product['is_promo'] = true;
                
                $origSale = $promo['original_sale_price'];
                if (!empty($origSale) && $origSale > 0) {
                    $product['origin_price_display'] = $origSale;
                } else {
                    $product['origin_price_display'] = $product['price'];
                }
            }
        } catch (\Throwable $e) {
            // bỏ qua lỗi thiếu bảng
        }
    }
}
?>