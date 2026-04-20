<?php
if (!defined('SECURE')) {
    http_response_code(403);
    header("Location: /home");
    exit();
}

class bannersController extends controller
{
    public function banners($param = null)
    {
        $this->adminHeader([
            'title' => 'Quản lý Banner',
            'css' => ['admin_dashboard.css', 'admin_banners.css']
        ]);
        $this->adminView('banners', 'Banners');
    }
}
