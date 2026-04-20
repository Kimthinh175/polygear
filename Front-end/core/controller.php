<?php
    class controller{
        public function view($name, $data = [])
        {
            $controllerName = get_class($this);
            $moduleName = strtolower(str_replace('Controller', '', $controllerName));
            $viewFile = ROOT_DIR . "/Front-end/modules/" . $moduleName . "/views/" . $name . ".php";
            if (file_exists($viewFile)) {
                require_once $viewFile;
            } else {
                die("Error: View '{$name}' not found for module '{$moduleName}'.<br>Attempted to load: {$viewFile}");
            }
        }
        public function header($data = [])
        {
            require_once ROOT_DIR . "/Front-end/public/layout/header.php";
        }
        public function footer($data = [])
        {
            require_once ROOT_DIR . "/Front-end/public/layout/footer.php";
        }
        public static function pageNotFound($data = []){
            require_once ROOT_DIR . "/Front-end/public/layout/header.php";
            require_once ROOT_DIR . "/Front-end/public/layout/404.php";
            require_once ROOT_DIR . "/Front-end/public/layout/footer.php";
            exit();
        }

        public function adminView($name, $folderName, $data = [])
        {
            $viewFile = ROOT_DIR . "/Front-end/admin-module/" . $folderName . "/views/" . $name . ".php";
            if (file_exists($viewFile)) {
                require_once $viewFile;
            } else {
                die("Error: Admin View '{$name}' not found in folder '{$folderName}'.<br>Attempted to load: {$viewFile}");
            }
        }

        public function adminHeader($data = [])
        {
            require_once ROOT_DIR . "/Front-end/public/layout/admin_header.php";
            require_once ROOT_DIR . "/Front-end/public/layout/admin_sidebar.php";
        }
    }
?>
