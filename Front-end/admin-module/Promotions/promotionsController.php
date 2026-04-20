<?php
class promotionsController extends controller
{
    public function promotions()
    {
        $this->adminHeader(['title' => 'Quản Lý Khuyến Mãi - Admin Portal', 'css' => ['admin_dashboard.css']]);
        $this->adminView('promotions', 'Promotions');
    }
}
?>
