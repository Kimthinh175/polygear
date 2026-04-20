<?php
    if(!defined('SECURE')){http_response_code(403);header("Location: /home");exit();}

    class settingsController extends controller {
        public function ai_settings($param=null) {
            $this->adminHeader(['title' => 'Cài Đặt AI - Admin Portal']);
            $this->adminView('ai_settings', 'Settings');
        }
    }
?>
