<?php
try {
    $pdo = new PDO("mysql:host=localhost;port=3306;dbname=polygear;charset=utf8mb4", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("ALTER TABLE orders ADD COLUMN cancel_reason TEXT NULL DEFAULT NULL AFTER reminder;");
    echo "SUCCESS";
} catch(PDOException $e) {
    if ($e->getCode() == '42S21') { // Column already exists
        echo "SUCCESS - Column already exists";
    } else {
        echo "ERROR: " . $e->getMessage();
    }
}
