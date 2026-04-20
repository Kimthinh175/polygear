<?php
if (!defined('SECURE')) {
    http_response_code(403);
    header("Location: /home");
    exit();
}
class checkoutController extends controller
{
    public function cart()
    {
        if (isset($_SESSION['user'])) {
            $cart = ApiCaller::get("api/cart?id={$_SESSION['user']['id']}", true)['cart'];
        }
        $this->header([
            'seo_title' => 'Giỏ hàng của bạn | PolyGear',
            'seo_desc' => 'Quản lý giỏ hàng và chuẩn bị thanh toán các sản phẩm công nghệ tuyệt vời tại PolyGear.'
        ]);
        $this->view('cart', $cart ?? []);
        $this->footer();
    }
    public function checkout()
    {
        if (!isset($_SESSION['user'])) {
            header("location: /home");
        }
        $this->header([
            'seo_title' => 'Thanh toán đơn hàng | PolyGear',
            'seo_desc' => 'Hoàn tất thông tin thanh toán để sở hữu những sản phẩm công nghệ từ PolyGear.'
        ]);
        $this->view("checkout", $data = []);
        $this->footer();
    }

    public function success()
    {
        if (!isset($_SESSION['user'])) {
            header("location: /home");
        }

        $info = ApiCaller::get("api/account/order?code={$_GET['code']}", true);
        $this->header([
            'seo_title' => 'Đặt hàng thành công | PolyGear',
            'seo_desc' => 'Cảm ơn bạn đã tin tưởng mua sắm tại PolyGear. Đơn hàng của bạn đã được tiếp nhận.'
        ]);
        $this->view("success", $info ?? []);
        $this->footer();
    }
}
?>