<?php

namespace Core;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $instance = null;

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $cfg = require BASE_PATH . '/config/database.php';

            if ($cfg['database'] === '' || $cfg['username'] === '') {
                http_response_code(500);
                exit('Base de datos no configurada. Copia .env.example a .env y completa las credenciales.');
            }

            $dsn = "mysql:host={$cfg['host']};port={$cfg['port']};dbname={$cfg['database']};charset={$cfg['charset']}";
            try {
                self::$instance = new PDO($dsn, $cfg['username'], $cfg['password'], [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
            } catch (PDOException $e) {
                // El detalle va al log; el visitante solo ve un mensaje genérico
                error_log('DB connection failed: ' . $e->getMessage());
                http_response_code(500);
                if (env('APP_DEBUG', 'false') === 'true') {
                    exit('Error de conexión a la base de datos: ' . $e->getMessage());
                }
                exit('Error de conexión a la base de datos. Revisa las credenciales en .env');
            }
        }
        return self::$instance;
    }

    public static function query(string $sql, array $params = []): \PDOStatement
    {
        $stmt = self::getInstance()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public static function fetchAll(string $sql, array $params = []): array
    {
        return self::query($sql, $params)->fetchAll();
    }

    public static function fetchOne(string $sql, array $params = []): array|false
    {
        return self::query($sql, $params)->fetch();
    }

    public static function insert(string $table, array $data): int
    {
        $cols = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        self::query("INSERT INTO {$table} ({$cols}) VALUES ({$placeholders})", array_values($data));
        return (int) self::getInstance()->lastInsertId();
    }

    public static function update(string $table, array $data, string $where, array $whereParams = []): int
    {
        $set = implode(', ', array_map(fn($col) => "{$col} = ?", array_keys($data)));
        $stmt = self::query("UPDATE {$table} SET {$set} WHERE {$where}", [...array_values($data), ...$whereParams]);
        return $stmt->rowCount();
    }

    public static function delete(string $table, string $where, array $params = []): int
    {
        return self::query("DELETE FROM {$table} WHERE {$where}", $params)->rowCount();
    }

    public static function lastId(): int
    {
        return (int) self::getInstance()->lastInsertId();
    }

    public static function transaction(callable $fn): mixed
    {
        $pdo = self::getInstance();
        $pdo->beginTransaction();
        try {
            $result = $fn();
            $pdo->commit();
            return $result;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}
