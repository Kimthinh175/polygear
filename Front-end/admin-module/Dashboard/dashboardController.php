<?php
    if(!defined('SECURE')){http_response_code(403);header("Location: /home");exit();}

    class dashboardController extends controller{
        public function login($param=null){
            // trang login không cần header/footer admin layout
            require_once ROOT_DIR . "/Front-end/admin-module/Dashboard/views/login.php";
        }

        public function dashboard($param=null){
            // gọi layout
            $this->adminHeader(['title' => 'Dashboard Đơn Hàng - Admin Portal', 'css' => ['admin_dashboard.css']]);
            $this->adminView('dashboard', 'Dashboard');
        }

        public function orders($param=null){
            $this->adminHeader(['title' => 'Danh Sách Đơn Hàng - Admin Portal', 'css' => ['admin_dashboard.css']]);
            $this->adminView('orders', 'Dashboard');
        }
    }
?>
