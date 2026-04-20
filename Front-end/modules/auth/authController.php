<?php
    if(!defined('SECURE')){http_response_code(403);header("Location: /home");exit();}
    // gọi api bằng hàm apicaller::get();
    // vd: apicaller::get('api/products/detail?param=' . $sku);
    // thằng này k đi qua index
    
    require_once ROOT_DIR."/Front-end/core/controller.php";
    require_once ROOT_DIR."/Front-end/core/ApiCaller.php";
    class authController extends controller{
        public function index(){
            if(isset($_SESSION['user'])){
                header("Location: /home");
                exit;
            }
            $this->header();
            $this->view('login',$data ?? []);
            $this->footer();
        }

    }
?>
