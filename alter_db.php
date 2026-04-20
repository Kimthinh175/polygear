<?php
require_once "Back-end/init.php";
try {
    database::ThucThi("ALTER TABLE orders ADD COLUMN cancel_reason TEXT NULL DEFAULT NULL AFTER reminder;");
    file_put_contents("alter_db_result.txt", "SUCCESS");
} catch (Exception $e) {
    file_put_contents("alter_db_result.txt", "ERROR: " . $e->getMessage());
}
