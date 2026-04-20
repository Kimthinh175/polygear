<?php
if (!defined('SECURE_API_ACCESS')) {
    http_response_code(403);
    header("Location: /home");
    exit();
}
class database
{
    private static $onlyConn;
    private $conn;
    // private $options = [
    // pdo::attr_errmode => pdo::errmode_exception,
    // pdo::attr_default_fetch_mode => pdo::fetch_assoc,
    // pdo::mysql_attr_init_command => "set names utf8mb4",
    // pdo::mysql_attr_ssl_ca => __dir__ . '/cacert.pem',
    // pdo::mysql_attr_ssl_verify_server_cert => false,
    // ];
    private $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
    ];

    private function __construct()
    {
        try {
            $dsn = "mysql:host="
                . $_ENV['DB_HOST']
                . ";port="
                . $_ENV['DB_PORT']
                . ";dbname="
                . $_ENV['DB_NAME']
                . ";charset=utf8mb4";

            $this->conn = new PDO(
                $dsn,
                $_ENV['DB_USER'],
                $_ENV['DB_PASS'],
                $this->options
            );

        } catch (PDOException $e) {
            die(json_encode([
                "status" => "error",
                "message" => "Database connection failed! Chi tiết: " . $e->getMessage()
            ]));
        }
    }
    private static function getInstance()
    {
        if (!self::$onlyConn) {
            self::$onlyConn = new database();
        }
        return self::$onlyConn;
    }

    static function ThucThiTraVe($sql, $params = [])
    {
        $db = self::getInstance();
        $stmt = $db->conn->prepare($sql);
        if (count($params) > 0) {
            foreach ($params as $key => $value) {
                $stmt->bindValue(':' . $key, $value);
            }
        }
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $data;
    }

    public static function ThucThi($sql, $params = [])
    {
        $db = self::getInstance();
        $stmt = $db->conn->prepare($sql);
        if (count($params) > 0) {
            foreach ($params as $key => $value) {
                $stmt->bindValue(':' . $key, $value);
            }
        }
        $stmt->execute();
    }
    public static function beginTransaction()
    {
        self::getInstance()->conn->beginTransaction();
    }

    public static function commit()
    {
        self::getInstance()->conn->commit();
    }

    public static function rollBack()
    {
        self::getInstance()->conn->rollBack();
    }

    public static function inTransaction()
    {
        return self::getInstance()->conn->inTransaction();
    }
}
?>