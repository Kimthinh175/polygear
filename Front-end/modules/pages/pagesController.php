<?php
if (!defined('SECURE')) {
    http_response_code(403);
    header("Location: /home");
    exit();
}

class pagesController extends controller
{
    public function warranty()
    {
        $this->header([
            'seo_title' => 'Chính sách bảo hành | PolyGear',
            'seo_desc' => 'Thông tin chi tiết về chính sách bảo hành sản phẩm tại PolyGear. Cam kết quyền lợi khách hàng tốt nhất.'
        ]);
        $this->view('warranty');
        $this->footer();
    }

    public function shipping()
    {
        $this->header([
            'seo_title' => 'Chính sách vận chuyển | PolyGear',
            'seo_desc' => 'Tìm hiểu về quy trình giao hàng, phí vận chuyển và thời gian nhận hàng khi mua sắm tại PolyGear.'
        ]);
        $this->view('shipping');
        $this->footer();
    }

    public function payment()
    {
        $this->header([
            'seo_title' => 'Chính sách thanh toán | PolyGear',
            'seo_desc' => 'Các phương thức thanh toán an toàn, linh hoạt (COD, QR Pay, Trả góp) tại PolyGear.'
        ]);
        $this->view('payment');
        $this->footer();
    }

    public function returns()
    {
        $this->header([
            'seo_title' => 'Chính sách đổi trả | PolyGear',
            'seo_desc' => 'Quy định về việc đổi trả sản phẩm, hoàn tiền và các điều kiện áp dụng tại PolyGear.'
        ]);
        $this->view('returns');
        $this->footer();
    }

    public function about()
    {
        $this->header([
            'seo_title' => 'Về chúng tôi - Câu chuyện PolyGear',
            'seo_desc' => 'PolyGear - Hệ thống bán lẻ linh kiện máy tính và thiết bị công nghệ hàng đầu. Tìm hiểu về tầm nhìn, sứ mệnh và đội ngũ của chúng tôi.'
        ]);
        $this->view('about');
        $this->footer();
    }

    public function contact()
    {
        $this->header([
            'seo_title' => 'Liên hệ với PolyGear',
            'seo_desc' => 'Mọi thắc mắc, góp ý hoặc yêu cầu hỗ trợ, vui lòng liên hệ với PolyGear qua hotline, email hoặc trực tiếp tại cửa hàng.'
        ]);
        $this->view('contact');
        $this->footer();
    }
}
