<?php

namespace Models;

use Core\Database;

class Vertical
{
    public static function all(): array
    {
        return Database::fetchAll("SELECT * FROM verticals ORDER BY name");
    }

    public static function active(): array
    {
        return Database::fetchAll("SELECT * FROM verticals WHERE is_active = 1 ORDER BY name");
    }

    public static function find(int $id): array|false
    {
        return Database::fetchOne("SELECT * FROM verticals WHERE id = ? LIMIT 1", [$id]);
    }

    public static function create(array $data): int
    {
        $slug = self::generateSlug($data['name']);
        return Database::insert('verticals', [
            'name'             => $data['name'],
            'slug'             => $slug,
            'description'      => $data['description'] ?? '',
            'color'            => $data['color'] ?? '#3B82F6',
            'is_active'        => (int) ($data['is_active'] ?? 1),
            'track_commission' => (int) ($data['track_commission'] ?? 0),
            'created_at'       => date('Y-m-d H:i:s'),
            'updated_at'       => date('Y-m-d H:i:s'),
        ]);
    }

    public static function update(int $id, array $data): void
    {
        Database::update('verticals', [
            'name'             => $data['name'],
            'slug'             => self::generateSlug($data['name'], $id),
            'description'      => $data['description'] ?? '',
            'color'            => $data['color'] ?? '#3B82F6',
            'is_active'        => (int) ($data['is_active'] ?? 1),
            'track_commission' => (int) ($data['track_commission'] ?? 0),
            'updated_at'       => date('Y-m-d H:i:s'),
        ], 'id = ?', [$id]);
    }

    public static function delete(int $id): bool
    {
        $count = Database::fetchOne("SELECT COUNT(*) as c FROM deals WHERE vertical_id = ?", [$id]);
        if ($count['c'] > 0) return false;
        Database::delete('verticals', 'id = ?', [$id]);
        return true;
    }

    private static function generateSlug(string $name, ?int $excludeId = null): string
    {
        $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $name));
        $slug = trim($slug, '-') ?: 'vertical';
        $base = $slug;
        // Bucle acotado: evita un while(true) con una consulta por iteración
        for ($i = 1; $i <= 100; $i++) {
            $sql    = "SELECT id FROM verticals WHERE slug = ?";
            $params = [$slug];
            if ($excludeId) {
                $sql    .= " AND id != ?";
                $params[] = $excludeId;
            }
            if (!Database::fetchOne($sql, $params)) break;
            $slug = $base . '-' . $i;
        }
        return $slug;
    }
}
