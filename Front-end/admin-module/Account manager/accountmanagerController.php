<?php
class accountmanagerController extends controller
{
    public function users()
    {
        $this->adminHeader(['title' => 'Quản lý Khách hàng - Admin Portal', 'css' => ['admin_account.css']]);
        $this->adminView('account_management', 'Account manager', ['activeTab' => 'user']);
    }

    public function staff()
    {
        $this->adminHeader(['title' => 'Quản lý Nhân viên - Admin Portal', 'css' => ['admin_account.css']]);
        $this->adminView('account_management', 'Account manager', ['activeTab' => 'admin']);
    }

    public function admins()
    {
        // kiểm tra quyền: chỉ admin thực thụ mới được vào trang quản lý tài khoản admin khác
        if (!isset($_SESSION['admin']) || $_SESSION['admin']['role'] !== 'admin') {
            header('location: /admin/dashboard');
            exit;
        }

        $this->adminHeader(['title' => 'Quản trị viên - Admin Portal', 'css' => ['admin_account.css']]);
        $this->adminView('account_management', 'Account manager', ['activeTab' => 'super']);
    }
}
