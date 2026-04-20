<?php
if (!defined('SECURE')) {
    http_response_code(403);
    header("Location: /home");
    exit();
}

class vouchersController extends controller
{
    public function vouchers()
    {
        $this->adminHeader(['title' => 'Quản lý Voucher']);
        $this->adminView('vouchers', 'Vouchers');
    }
}
?>
