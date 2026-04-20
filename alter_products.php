<?php
try {
    $pdo = new PDO("mysql:host=localhost;port=3306;dbname=polygear;charset=utf8mb4", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("ALTER TABLE products ADD COLUMN status ENUM('active', 'inactive') DEFAULT 'active' AFTER brand_id");
    echo "SUCCESS: Added status column";
} catch(PDOException $e) {
    echo "ERROR: " . $e->getMessage();
}
