<?php

namespace Models;

use Core\Database;

class Activity
{
    public static function log(int $dealId, int $userId, string $type, string $description, ?int $oldStageId = null, ?int $newStageId = null): void
    {
        Database::insert('activities', [
            'deal_id'      => $dealId,
            'user_id'      => $userId,
            'type'         => $type,
            'description'  => $description,
            'old_stage_id' => $oldStageId,
            'new_stage_id' => $newStageId,
            'created_at'   => date('Y-m-d H:i:s'),
        ]);
    }

    public static function forDeal(int $dealId): array
    {
        return Database::fetchAll(
            "SELECT a.*, u.name as user_name,
                    s1.name as old_stage_name, s1.color as old_stage_color,
                    s2.name as new_stage_name, s2.color as new_stage_color
             FROM activities a
             JOIN users u ON u.id = a.user_id
             LEFT JOIN stages s1 ON s1.id = a.old_stage_id
             LEFT JOIN stages s2 ON s2.id = a.new_stage_id
             WHERE a.deal_id = ?
             ORDER BY a.created_at DESC",
            [$dealId]
        );
    }

    public static function recent(?int $userId = null, string $role = 'admin', int $limit = 15): array
    {
        $filter = '';
        $params = [];
        if ($role === 'vendedor' && $userId) {
            $filter   = "AND d.assigned_to = ?";
            $params[] = $userId;
        }
        $params[] = $limit;
        return Database::fetchAll(
            "SELECT a.*, u.name as user_name, d.title as deal_title
             FROM activities a
             JOIN users u ON u.id = a.user_id
             JOIN deals d ON d.id = a.deal_id
             WHERE d.deleted_at IS NULL $filter
             ORDER BY a.created_at DESC
             LIMIT ?",
            $params
        );
    }
}
