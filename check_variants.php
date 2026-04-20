<?php
try {
    $pdo = new PDO("mysql:host=localhost;port=3306;dbname=polygear;charset=utf8mb4", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt = $pdo->query("DESCRIBE product_variants");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch(PDOException $e) {
    echo "ERROR: " . $e->getMessage();
}
