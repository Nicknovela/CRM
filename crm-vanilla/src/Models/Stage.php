<?php

namespace Models;

use Core\Database;

class Stage
{
    public static function byVertical(int $verticalId): array
    {
        return Database::fetchAll(
            "SELECT * FROM stages WHERE vertical_id = ? ORDER BY position ASC",
            [$verticalId]
        );
    }

    public static function find(int $id): array|false
    {
        return Database::fetchOne("SELECT * FROM stages WHERE id = ? LIMIT 1", [$id]);
    }

    public static function create(array $data): int
    {
        $maxPos = Database::fetchOne(
            "SELECT MAX(position) as mp FROM stages WHERE vertical_id = ?",
            [$data['vertical_id']]
        );
        $position = ($maxPos['mp'] ?? 0) + 1;

        return Database::insert('stages', [
            'vertical_id' => $data['vertical_id'],
            'name'        => $data['name'],
            'color'       => $data['color'] ?? '#6B7280',
            'position'    => $data['position'] ?? $position,
            'is_won'      => (int) ($data['is_won'] ?? 0),
            'is_lost'     => (int) ($data['is_lost'] ?? 0),
            'created_at'  => date('Y-m-d H:i:s'),
            'updated_at'  => date('Y-m-d H:i:s'),
        ]);
    }

    public static function update(int $id, array $data): void
    {
        Database::update('stages', [
            'name'       => $data['name'],
            'color'      => $data['color'] ?? '#6B7280',
            'is_won'     => (int) ($data['is_won'] ?? 0),
            'is_lost'    => (int) ($data['is_lost'] ?? 0),
            'updated_at' => date('Y-m-d H:i:s'),
        ], 'id = ?', [$id]);
    }

    public static function reorder(array $ids): void
    {
        foreach ($ids as $pos => $id) {
            Database::update('stages', ['position' => $pos + 1], 'id = ?', [(int)$id]);
        }
    }

    public static function delete(int $id): bool
    {
        $count = Database::fetchOne("SELECT COUNT(*) as c FROM deals WHERE stage_id = ?", [$id]);
        if ($count['c'] > 0) return false;
        Database::delete('stages', 'id = ?', [$id]);
        return true;
    }
}
