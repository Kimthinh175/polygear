<?php
    if(!defined('SECURE')){http_response_code(403);header("Location: /home");exit();}
    if(!isset($_SESSION['user'])){
        header('Location: /home');
        exit();
    }
    class AccountController extends controller{
        function account(){
            if(!isset($_SESSION['user'])){
                header('Location: /homes');
                exit();
            }
            
            $data = ApiCaller::get("api/account?user_id={$_SESSION['user']['id']}", true);
            $this->header([
                'seo_title' => 'Tài khoản của tôi | PolyGear',
                'seo_desc' => 'Quản lý thông tin cá nhân và địa chỉ nhận hàng tại PolyGear.'
            ]);
            $this->view("account",$data);
            $this->footer();
        }
        function history(){
            $data = [];
            $this->header([
                'seo_title' => 'Lịch sử mua hàng | PolyGear',
                'seo_desc' => 'Xem lại các đơn hàng đã đặt và theo dõi trạng thái vận chuyển tại PolyGear.'
            ]);
            $this->view("history",$data ?? []);
            $this->footer();
        }
        function notifications(){
            $this->header([
                'seo_title' => 'Thông báo | PolyGear',
                'seo_desc' => 'Cập nhật những thông tin mới nhất về đơn hàng và ưu đãi dành riêng cho bạn.'
            ]);
            $this->view("notifications", []);
            $this->footer();
        }
        function vouchers(){
            $this->header([
                'seo_title' => 'Kho voucher | PolyGear',
                'seo_desc' => 'Tổng hợp các mã giảm giá và chương trình khuyến mãi đang diễn ra tại PolyGear.'
            ]);
            $this->view("vouchers", []);
            $this->footer();
        }
        
    }

?>
