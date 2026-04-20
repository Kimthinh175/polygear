<?php
if (!defined('SECURE_API_ACCESS')) {
    http_response_code(403);
    header("Location: /home");
    exit();
}

class Router
{
    private $routes = [];

    // đăng ký bản đồ: $routes['get']['api/products'] = ...
    public function get($endpoint, $module, $action)
    {
        $this->routes['GET'][$endpoint] = ['module' => $module, 'action' => $action];
    }
    public function post($endpoint, $module, $action)
    {
        $this->routes['POST'][$endpoint] = ['module' => $module, 'action' => $action];
    }
    public function put($endpoint, $module, $action)
    {
        $this->routes['PUT'][$endpoint] = ['module' => $module, 'action' => $action];
    }
    public function delete($endpoint, $module, $action)
    {
        $this->routes['DELETE'][$endpoint] = ['module' => $module, 'action' => $action];
    }

    // phân luồng
    public function dispatch($method, $endpoint)
    {
        $endpoint = trim($endpoint, '/'); // dọn dẹp dấu chéo thừa

        // khớp method và khớp endpoint thì cho vào
        if (isset($this->routes[$method][$endpoint])) {

            require_once ROOT_DIR . "/Back-end/core/middleware.php";

            // chặn api của admin (ngoại trừ login và logout admin)
            if (strpos($endpoint, 'api/admin/') === 0 && $endpoint !== 'api/admin/auth/login' && $endpoint !== 'api/admin/auth/logout') {
                // check token và role (chỉ cho admin)
                AuthMiddleware::check('admin');
            }

            // chặn api thao tác của user
            if (strpos($endpoint, 'api/account') === 0 || strpos($endpoint, 'api/checkout/order') === 0 || strpos($endpoint, 'api/address') === 0) {
                // check token hợp lệ là được
                AuthMiddleware::check();
            }

            $route = $this->routes[$method][$endpoint];
            $moduleName = $route['module'];
            $actionName = $route['action'];

            require_once ROOT_DIR . "/Back-end/modules/$moduleName.php";
            // khởi tạo module và chạy hàm
            $module = new $moduleName();
            $module->$actionName($_GET);
            return;
        }

        // nếu gõ bậy bạ
        http_response_code(404);
        echo json_encode(["status" => "404", "message" => "API NOT FOUND"]);
    }
}
?>