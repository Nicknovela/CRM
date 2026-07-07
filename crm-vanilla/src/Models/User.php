<?php

namespace Models;

use Core\Database;

class User
{
    // Coste 12 (~250 ms en CPU de hosting compartido): solo se paga al iniciar sesión
    private const BCRYPT_COST = 12;

    public static function all(array $filters = []): array
    {
        $where = [];
        $params = [];
        if (!empty($filters['search'])) {
            $where[] = "(name LIKE ? OR email LIKE ?)";
            $s = '%' . $filters['search'] . '%';
            $params[] = $s;
            $params[] = $s;
        }
        if (isset($filters['is_active'])) {
            $where[] = "is_active = ?";
            $params[] = (int) $filters['is_active'];
        }
        $sql = "SELECT id, name, email, role, is_active, timezone, created_at FROM users";
        if ($where) $sql .= " WHERE " . implode(' AND ', $where);
        $sql .= " ORDER BY name ASC";
        return Database::fetchAll($sql, $params);
    }

    public static function find(int $id): array|false
    {
        return Database::fetchOne("SELECT * FROM users WHERE id = ? LIMIT 1", [$id]);
    }

    public static function findByEmail(string $email): array|false
    {
        return Database::fetchOne("SELECT * FROM users WHERE email = ? LIMIT 1", [strtolower($email)]);
    }

    public static function create(array $data): int
    {
        return Database::insert('users', [
            'name'       => $data['name'],
            'email'      => strtolower($data['email']),
            'password'   => password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => self::BCRYPT_COST]),
            'role'       => $data['role'] ?? 'vendedor',
            'is_active'  => (int) ($data['is_active'] ?? 1),
            'timezone'   => $data['timezone'] ?? 'America/La_Paz',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public static function update(int $id, array $data): void
    {
        $fields = [
            'name'       => $data['name'],
            'email'      => strtolower($data['email']),
            'role'       => $data['role'],
            'is_active'  => (int) ($data['is_active'] ?? 1),
            'timezone'   => $data['timezone'] ?? 'America/La_Paz',
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        if (!empty($data['password'])) {
            $fields['password'] = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => self::BCRYPT_COST]);
        }
        Database::update('users', $fields, 'id = ?', [$id]);
    }

    public static function setActive(int $id, bool $active): void
    {
        Database::update('users', [
            'is_active'  => $active ? 1 : 0,
            'updated_at' => date('Y-m-d H:i:s'),
        ], 'id = ?', [$id]);
    }

    public static function delete(int $id): bool
    {
        $count = Database::fetchOne(
            "SELECT COUNT(*) as c FROM deals WHERE assigned_to = ? AND deleted_at IS NULL",
            [$id]
        );
        if ((int) $count['c'] > 0) return false;
        Database::delete('users', 'id = ?', [$id]);
        return true;
    }

    public static function active(): array
    {
        return Database::fetchAll("SELECT id, name, email, role FROM users WHERE is_active = 1 ORDER BY name");
    }

    public static function vendedores(): array
    {
        return Database::fetchAll(
            "SELECT id, name FROM users WHERE is_active = 1 AND role IN ('vendedor','manager','admin') ORDER BY name"
        );
    }
}
