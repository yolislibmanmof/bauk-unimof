<?php
/**
 * Database Connection Class
 * Menggunakan PDO untuk keamanan maksimal
 */

declare(strict_types=1);

namespace App\Helpers;

use PDO;
use PDOException;

class Database
{
    private static ?Database $instance = null;
    private PDO $connection;
    private array $config;

    private function __construct()
    {
        $this->config = require __DIR__ . '/../../config/database.php';
        $this->connect();
    }

    private function connect(): void
    {
        try {
            $dsn = sprintf(
                '%s:host=%s;port=%s;dbname=%s;charset=%s',
                $this->config['driver'],
                $this->config['host'],
                $this->config['port'],
                $this->config['database'],
                $this->config['charset']
            );

            $this->connection = new PDO($dsn, $this->config['username'], $this->config['password'], $this->config['options']);
        } catch (PDOException $e) {
            if ($this->config['debug'] ?? false) {
                throw new PDOException('Database connection failed: ' . $e->getMessage());
            }
            throw new PDOException('Database connection failed. Please contact administrator.');
        }
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection(): PDO
    {
        return $this->connection;
    }

    public function query(string $sql, array $params = []): \PDOStatement
    {
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public function fetchAll(string $sql, array $params = []): array
    {
        return $this->query($sql, $params)->fetchAll();
    }

    public function fetch(string $sql, array $params = []): array|false
    {
        return $this->query($sql, $params)->fetch();
    }

    /**
     * Insert data into table (dengan backtick untuk mencegah reserved keyword error)
     */
    public function insert(string $table, array $data): int
    {
        // Wrap column names and table name with backticks
        $columns = implode(', ', array_map(fn($k) => '`' . trim($k, '`') . '`', array_keys($data)));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        
        $sql = "INSERT INTO `" . trim($table, '`') . "` ({$columns}) VALUES ({$placeholders})";
        $this->query($sql, array_values($data));
        
        return (int) $this->connection->lastInsertId();
    }

    /**
     * Update data in table (dengan backtick untuk mencegah reserved keyword error)
     */
    public function update(string $table, array $data, string $where, array $whereParams = []): int
    {
        // Wrap column names and table name with backticks
        $set = implode(', ', array_map(fn($k) => '`' . trim($k, '`') . '` = ?', array_keys($data)));
        $sql = "UPDATE `" . trim($table, '`') . "` SET {$set} WHERE {$where}";
        
        $stmt = $this->query($sql, array_merge(array_values($data), $whereParams));
        return $stmt->rowCount();
    }

    public function delete(string $table, string $where, array $params = []): int
    {
        $sql = "DELETE FROM `" . trim($table, '`') . "` WHERE {$where}";
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }

    public function count(string $table, string $where = '1=1', array $params = []): int
    {
        $sql = "SELECT COUNT(*) as total FROM `" . trim($table, '`') . "` WHERE {$where}";
        $result = $this->fetch($sql, $params);
        return (int) ($result['total'] ?? 0);
    }

    public function beginTransaction(): bool
    {
        return $this->connection->beginTransaction();
    }

    public function commit(): bool
    {
        return $this->connection->commit();
    }

    public function rollback(): bool
    {
        return $this->connection->rollBack();
    }

    // Mencegah cloning dan unserialization
    private function __clone() {}
    public function __wakeup()
    {
        throw new \Exception("Cannot unserialize singleton");
    }
}