<?php
    if(!defined('SECURE')){http_response_code(403);header("Location: /home");exit();}
    // gọi api bằng hàm apicaller::get();
    // vd: apicaller::get('products/' . $id);
    // chưa thiết kế xong back-end, có viết giao diện thì tạo mảng giả lập data.
    class catalogController extends controller{
        public function products($keyword=null){
            if($keyword){
                $products = ApiCaller::get('api/products/find?keyword=' . urlencode($keyword));
            }else{
                $products=ApiCaller::get('api/products/category?category=');
            }
            
            $categoryList = ApiCaller::get('api/category')['data'];
            
            $title = $keyword ? "Kết quả tìm kiếm cho \"$keyword\" | PolyGear" : "Tất cả sản phẩm | PolyGear";
            $desc = $keyword ? "Tìm thấy các sản phẩm phù hợp với từ khóa \"$keyword\" tại PolyGear. Cam kết chính hãng, giá tốt." : "Khám phá danh sách sản phẩm công nghệ, linh kiện máy tính, laptop gaming chính hãng tại PolyGear.";

            $this->header([
                'seo_title' => $title,
                'seo_desc' => $desc
            ]);
            $brandsResponse = ApiCaller::get('api/admin/brands');
            $brands = $brandsResponse['data'] ?? [];

            $this->view('products', [
                'info' => $products['info'] ?? [],
                'category' => $categoryList ?? [],
                'keyword' => $keyword,
                'brands' => $brands
            ]);
            $this->footer();
        }
        public function detail($sku){
            // gọi api lấy data
            $res = ApiCaller::get('api/products/detail?sku=' . $sku);
            $product = $res['data'] ?? null;
            
            $title = $product ? $product['name'] . " - " . number_format($product['price'], 0, ',', '.') . "đ | PolyGear" : "Chi tiết sản phẩm | PolyGear";
            $desc = $product ? "Mua ngay " . $product['name'] . " chính hãng tại PolyGear. " . mb_substr(strip_tags($product['description'] ?? ''), 0, 150) . "..." : "Thông tin chi tiết sản phẩm công nghệ chính hãng tại PolyGear.";

            // view
            $this->header([
                'seo_title' => $title,
                'seo_desc' => $desc,
                'seo_img' => $product['main_image_url'] ?? null
            ]);
            $this->view('product-detail', $res ?? []);
            $this->footer();
        }

        public function category($category=''){
            $dynamicFilters = []; 
            $products = ApiCaller::get('api/products/category?category=' . $category);
            
            // logic xử lý bộ lọc động
            // bản đồ spec: danh mục nào thì load spec nấy
            $filter_map = [
                'ram' => ['DUNG_LUONG', 'LOAI_RAM', 'BUS_RAM'],
                'cpu' => ['DONG_SAN_PHAM', 'SOCKET', 'THE_HE_CPU']
            ];

            // lấy danh sách mã spec cần thiết cho danh mục hiện tại
            $active_specs = $filter_map[$category] ?? [];

            if (!empty($active_specs)) {
                $specParams = implode(',', $active_specs);
                
                $filterResponse = ApiCaller::get("api/category/filters?category=$category&specs=$specParams");
                $dynamicFilters = $filterResponse['data'] ?? [];
            }
            
            // lấy danh sách category cho sidebar/header
            $categories = ApiCaller::get('api/category')['data'] ?? [];
            
            // tìm tên danh mục hiện tại để làm seo
            $currentCateName = "Danh mục";
            foreach($categories as $c) {
                if($c['code'] === $category) {
                    $currentCateName = $c['name'];
                    break;
                }
            }

            $this->header([
                'seo_title' => "$currentCateName chính hãng, giá tốt nhất | PolyGear",
                'seo_desc' => "Mua sắm $currentCateName chính hãng tại PolyGear. Đa dạng mẫu mã, thương hiệu uy tín, bảo hành dài hạn và nhiều ưu đãi hấp dẫn."
            ]);
            
            // lấy danh sách hãng
            $brandsResponse = ApiCaller::get('api/admin/brands');
            $brands = $brandsResponse['data'] ?? [];

            // truyền bộ lọc ra view: thêm biến 'dynamicfilters' và 'brands' vào mảng data
            $this->view('products', [
                'info'           => $products['info'] ?? [],
                'category'       => $categories,
                'dynamicFilters' => $dynamicFilters,
                'brands'         => $brands
            ]);
            
            $this->footer();
        }

        
    }
?>
