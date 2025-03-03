<?php
namespace Summit\Core;

class Database {
    private static $instance = null;
    private $pdo;
    private $config;

    private function __construct() {
        $this->config = Config::get('database');
        $this->connect();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function connect() {
        $connection = $this->config['connections'][$this->config['default']];

        try {
            $dsn = sprintf(
                "%s:host=%s;dbname=%s;charset=%s",
                $connection['driver'],
                $connection['host'],
                $connection['database'],
                $connection['charset']
            );

            $this->pdo = new \PDO($dsn, $connection['username'], $connection['password'], [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                \PDO::ATTR_EMULATE_PREPARES => false,
            ]);

            // Set timezone
            $this->pdo->exec("SET time_zone = '" . $connection['timezone'] . "'");

        } catch (\PDOException $e) {
            ErrorHandler::logError("Database connection failed: " . $e->getMessage());
            throw new \Exception("Database connection failed");
        }
    }

    public function initializeDatabase() {
        try {
            foreach ($this->config['schema']['tables'] as $table => $sql) {
                $this->pdo->exec($sql);
            }
        } catch (\PDOException $e) {
            ErrorHandler::logError("Database initialization failed: " . $e->getMessage());
            throw new \Exception("Database initialization failed");
        }
    }

    public function prepare($sql) {
        return $this->pdo->prepare($sql);
    }

    public function lastInsertId() {
        return $this->pdo->lastInsertId();
    }

    public function beginTransaction() {
        return $this->pdo->beginTransaction();
    }

    public function commit() {
        return $this->pdo->commit();
    }

    public function rollBack() {
        return $this->pdo->rollBack();
    }

    public function query($sql) {
        return $this->pdo->query($sql);
    }

    public function quote($value) {
        return $this->pdo->quote($value);
    }
}
