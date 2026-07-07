<?php

namespace Models;

use Core\Database;

class Client
{
    public static function all(array $filters = [], int $limit = 20, int $offset = 0): array
    {
        $where  = [];
        $params = [];
        if (!empty($filters['search'])) {
            $s = '%' . $filters['search'] . '%';
            $where[]  = "(c.name LIKE ? OR c.company_name LIKE ? OR c.email LIKE ?)";
            $params[] = $s; $params[] = $s; $params[] = $s;
        }
        $sql = "SELECT c.*, COUNT(d.id) as deal_count, COALESCE(SUM(d.amount),0) as pipeline_value
                FROM clients c
                LEFT JOIN deals d ON d.client_id = c.id AND d.deleted_at IS NULL
                " . ($where ? "WHERE " . implode(' AND ', $where) : "") . "
                GROUP BY c.id
                ORDER BY c.name ASC
                LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        return Database::fetchAll($sql, $params);
    }

    public static function count(array $filters = []): int
    {
        $where  = [];
        $params = [];
        if (!empty($filters['search'])) {
            $s = '%' . $filters['search'] . '%';
            $where[]  = "(name LIKE ? OR company_name LIKE ? OR email LIKE ?)";
            $params[] = $s; $params[] = $s; $params[] = $s;
        }
        $sql = "SELECT COUNT(*) as c FROM clients" . ($where ? " WHERE " . implode(' AND ', $where) : "");
        $row = Database::fetchOne($sql, $params);
        return (int) $row['c'];
    }

    public static function find(int $id): array|false
    {
        return Database::fetchOne("SELECT * FROM clients WHERE id = ? LIMIT 1", [$id]);
    }

    public static function create(array $data): int
    {
        return Database::insert('clients', [
            'name'         => $data['name'],
            'company_name' => $data['company_name'] ?? '',
            'email'        => $data['email'] ?? null,
            'phone'        => $data['phone'] ?? '',
            'industry'     => $data['industry'] ?? '',
            'website'      => $data['website'] ?? '',
            'address'      => $data['address'] ?? '',
            'notes'        => $data['notes'] ?? '',
            'created_at'   => date('Y-m-d H:i:s'),
            'updated_at'   => date('Y-m-d H:i:s'),
        ]);
    }

    public static function update(int $id, array $data): void
    {
        Database::update('clients', [
            'name'         => $data['name'],
            'company_name' => $data['company_name'] ?? '',
            'email'        => $data['email'] ?: null,
            'phone'        => $data['phone'] ?? '',
            'industry'     => $data['industry'] ?? '',
            'website'      => $data['website'] ?? '',
            'address'      => $data['address'] ?? '',
            'notes'        => $data['notes'] ?? '',
            'updated_at'   => date('Y-m-d H:i:s'),
        ], 'id = ?', [$id]);
    }

    public static function delete(int $id): bool
    {
        // No se borra un cliente con negocios: dejaría deals huérfanos
        $count = Database::fetchOne(
            "SELECT COUNT(*) as c FROM deals WHERE client_id = ?",
            [$id]
        );
        if ((int) $count['c'] > 0) return false;
        Database::delete('clients', 'id = ?', [$id]);
        return true;
    }

    public static function deals(int $clientId): array
    {
        return Database::fetchAll(
            "SELECT d.*, s.name as stage_name, s.color as stage_color,
                    v.name as vertical_name, u.name as assigned_name
             FROM deals d
             JOIN stages s ON s.id = d.stage_id
             JOIN verticals v ON v.id = d.vertical_id
             LEFT JOIN users u ON u.id = d.assigned_to
             WHERE d.client_id = ? AND d.deleted_at IS NULL
             ORDER BY d.created_at DESC",
            [$clientId]
        );
    }

    public static function forSelect(): array
    {
        return Database::fetchAll("SELECT id, name, company_name FROM clients ORDER BY name LIMIT 500");
    }
}
