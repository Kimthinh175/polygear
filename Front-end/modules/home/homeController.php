<?php
if (!defined('SECURE')) {
    http_response_code(403);
    header("Location: /home");
    exit();
}

class homeController extends controller
{
    public function home()
    {
        $hot = ApiCaller::get('api/products/hot');
        $new = ApiCaller::get('api/products/new');
        $sale = ApiCaller::get('api/products/sale');
        $categoryRes = ApiCaller::get('api/category')['data'] ?? [];
        // removed sequential category product fetching to improve ttfb
        // these will now be lazy-loaded on the client side
        $categoryProducts = []; 

        $banners = ApiCaller::get('api/banners')['data'] ?? [];
        $promotions = ApiCaller::get('api/promotions')['data'] ?? [];
        $this->header([
            'seo_title' => 'PolyGear - Hệ thống bán lẻ Linh Kiện Máy Tính, PC & Laptop Gaming chính hãng',
            'seo_desc' => 'PolyGear chuyên cung cấp linh kiện máy tính, PC Build, Laptop Gaming và phụ kiện cao cấp. Bảo hành chính hãng, trả góp 0%, giao hàng siêu tốc.'
        ]);
        $this->view(
            'home',
            [
                'hot' => $hot['data'] ?? [],
                'new' => $new['data'] ?? [],
                'sale' => $sale['data'] ?? [],
                'category' => $categoryRes,
                'categoryProducts' => $categoryProducts,
                'banners' => $banners,
                'promotions' => $promotions
            ]
        );
        $this->footer();
    }

}
?>
