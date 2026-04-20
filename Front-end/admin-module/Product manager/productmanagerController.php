<?php
    if(!defined('SECURE')){http_response_code(403);header("Location: /home");exit();}

    class productmanagerController extends controller{
        // route cho danh sách biến thể
        public function list_variant($param=null){
            $this->adminHeader(['title' => 'Danh sách Biến thể Sản phẩm', 'css' => ['admin_list_variant.css', 'admin_dashboard.css']]);
            $this->adminView('list_variant', 'Product manager');
        }

        // route cho danh sách sản phẩm gốc
        public function list_product($param=null){
            $this->adminHeader(['title' => 'Danh sách Sản Phẩm Gốc', 'css' => ['admin_list_variant.css', 'admin_dashboard.css']]);
            $this->adminView('list_product', 'Product manager');
        }

        // route cho thêm biến thể
        public function add_variant($param=null){
            $this->adminHeader(['title' => 'Thêm Biến thể Sản phẩm']);
            $this->adminView('add_variant', 'Product manager');
        }
        
        // route cho thêm sản phẩm gốc
        public function add_product($param=null){
            $this->adminHeader(['title' => 'Thêm Sản phẩm Gốc']);
            $this->adminView('add_product', 'Product manager');
        }

        public function categories($param=null){
            $this->adminHeader(['title' => 'Quản lý Danh mục', 'css' => ['admin_list_variant.css', 'admin_dashboard.css']]);
            $this->adminView('categories', 'Product manager');
        }

        public function brands($param=null){
            $this->adminHeader(['title' => 'Quản lý Thương hiệu', 'css' => ['admin_list_variant.css', 'admin_dashboard.css']]);
            $this->adminView('brands', 'Product manager');
        }
    }
?>
