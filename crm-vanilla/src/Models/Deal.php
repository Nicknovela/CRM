<?php

namespace Models;

use Core\Database;
use Core\Auth;

class Deal
{
    public static function query(array $filters = [], ?int $userId = null, string $userRole = 'vendedor', int $limit = 20, int $offset = 0): array
    {
        [$where, $params] = self::buildWhere($filters, $userId, $userRole);
        $sort = self::resolveSort($filters['sort'] ?? 'created_at', $filters['dir'] ?? 'desc');

        $sql = "SELECT d.*, c.name as client_name, c.company_name,
                       s.name as stage_name, s.color as stage_color, s.is_won, s.is_lost,
                       v.name as vertical_name, v.color as vertical_color,
                       u.name as assigned_name
                FROM deals d
                JOIN clients c ON c.id = d.client_id
                JOIN stages s ON s.id = d.stage_id
                JOIN verticals v ON v.id = d.vertical_id
                LEFT JOIN users u ON u.id = d.assigned_to
                WHERE d.deleted_at IS NULL " . ($where ? "AND $where" : "") . "
                ORDER BY $sort
                LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        return Database::fetchAll($sql, $params);
    }

    public static function count(array $filters = [], ?int $userId = null, string $userRole = 'vendedor'): int
    {
        [$where, $params] = self::buildWhere($filters, $userId, $userRole);
        $sql = "SELECT COUNT(*) as c FROM deals d WHERE d.deleted_at IS NULL " . ($where ? "AND $where" : "");
        $row = Database::fetchOne($sql, $params);
        return (int) $row['c'];
    }

    public static function find(int $id): array|false
    {
        return Database::fetchOne(
            "SELECT d.*, c.name as client_name, c.company_name,
                    s.name as stage_name, s.color as stage_color, s.is_won, s.is_lost,
                    v.name as vertical_name, v.color as vertical_color, v.track_commission,
                    u.name as assigned_name
             FROM deals d
             JOIN clients c ON c.id = d.client_id
             JOIN stages s ON s.id = d.stage_id
             JOIN verticals v ON v.id = d.vertical_id
             LEFT JOIN users u ON u.id = d.assigned_to
             WHERE d.id = ? AND d.deleted_at IS NULL LIMIT 1",
            [$id]
        );
    }

    public static function create(array $data): int
    {
        return Database::insert('deals', [
            'title'               => $data['title'],
            'client_id'           => (int) $data['client_id'],
            'vertical_id'         => (int) $data['vertical_id'],
            'stage_id'            => (int) $data['stage_id'],
            'assigned_to'         => !empty($data['assigned_to']) ? (int) $data['assigned_to'] : null,
            'amount'              => (float) ($data['amount'] ?? 0),
            'currency'            => $data['currency'] ?? 'BOB',
            'probability'         => (int) ($data['probability'] ?? 50),
            'commission_rate'     => !empty($data['commission_rate']) ? (float) $data['commission_rate'] : null,
            'expected_close_date' => $data['expected_close_date'] ?: null,
            'notes'               => $data['notes'] ?? '',
            'created_at'          => date('Y-m-d H:i:s'),
            'updated_at'          => date('Y-m-d H:i:s'),
        ]);
    }

    public static function update(int $id, array $data): void
    {
        Database::update('deals', [
            'title'               => $data['title'],
            'client_id'           => (int) $data['client_id'],
            'vertical_id'         => (int) $data['vertical_id'],
            'stage_id'            => (int) $data['stage_id'],
            'assigned_to'         => !empty($data['assigned_to']) ? (int) $data['assigned_to'] : null,
            'amount'              => (float) ($data['amount'] ?? 0),
            'currency'            => $data['currency'] ?? 'BOB',
            'probability'         => (int) ($data['probability'] ?? 50),
            'commission_rate'     => !empty($data['commission_rate']) ? (float) $data['commission_rate'] : null,
            'expected_close_date' => $data['expected_close_date'] ?: null,
            'notes'               => $data['notes'] ?? '',
            'lost_reason'         => $data['lost_reason'] ?? null,
            'updated_at'          => date('Y-m-d H:i:s'),
        ], 'id = ?', [$id]);
    }

    public static function moveToStage(int $id, int $stageId, int $userId): void
    {
        $deal  = self::find($id);
        $stage = Stage::find($stageId);
        if (!$deal || !$stage) return;

        $update = ['stage_id' => $stageId, 'updated_at' => date('Y-m-d H:i:s')];
        if ($stage['is_won']) {
            $update['actual_close_date'] = date('Y-m-d');
            $update['probability']       = 100;
        } elseif ($stage['is_lost']) {
            $update['probability'] = 0;
        }
        Database::update('deals', $update, 'id = ?', [$id]);

        if ($deal['stage_id'] !== $stageId) {
            Activity::log($id, $userId, 'stage_change',
                'Movido de etapa "' . ($deal['stage_name'] ?? '?') . '" a "' . $stage['name'] . '"',
                $deal['stage_id'], $stageId
            );
        }
    }

    public static function softDelete(int $id): void
    {
        Database::update('deals', ['deleted_at' => date('Y-m-d H:i:s')], 'id = ?', [$id]);
    }

    public static function restore(int $id): void
    {
        Database::update('deals', ['deleted_at' => null], 'id = ?', [$id]);
    }

    public static function forceDelete(int $id): void
    {
        Database::delete('deals', 'id = ?', [$id]);
    }

    public static function byStage(int $stageId, ?int $userId = null, string $role = 'admin'): array
    {
        $sql = "SELECT d.*, c.name as client_name, c.company_name, u.name as assigned_name
                FROM deals d
                JOIN clients c ON c.id = d.client_id
                LEFT JOIN users u ON u.id = d.assigned_to
                WHERE d.stage_id = ? AND d.deleted_at IS NULL";
        $params = [$stageId];
        if ($role === 'vendedor' && $userId) {
            $sql .= " AND d.assigned_to = ?";
            $params[] = $userId;
        }
        $sql .= " ORDER BY d.updated_at DESC";
        return Database::fetchAll($sql, $params);
    }

    // Metrics
    public static function metrics(int $verticalId = 0, string $period = '30'): array
    {
        $since = date('Y-m-d', strtotime("-{$period} days"));
        $params = [];
        $verticalFilter = '';
        if ($verticalId) {
            $verticalFilter = "AND d.vertical_id = ?";
            $params[] = $verticalId;
        }

        $pipeline = Database::fetchOne(
            "SELECT COALESCE(SUM(d.amount),0) as total FROM deals d
             JOIN stages s ON s.id = d.stage_id
             WHERE d.deleted_at IS NULL AND s.is_won = 0 AND s.is_lost = 0 $verticalFilter",
            $params
        );

        $conversionParams = $params;
        $total = Database::fetchOne(
            "SELECT COUNT(*) as c FROM deals d JOIN stages s ON s.id=d.stage_id
             WHERE d.deleted_at IS NULL AND d.created_at >= ? $verticalFilter",
            [$since, ...$params]
        );
        $won = Database::fetchOne(
            "SELECT COUNT(*) as c FROM deals d JOIN stages s ON s.id=d.stage_id
             WHERE d.deleted_at IS NULL AND s.is_won = 1 AND d.created_at >= ? $verticalFilter",
            [$since, ...$params]
        );

        $avgTicket = Database::fetchOne(
            "SELECT AVG(d.amount) as avg FROM deals d JOIN stages s ON s.id=d.stage_id
             WHERE d.deleted_at IS NULL AND s.is_won = 1 $verticalFilter",
            $params
        );

        $avgClose = Database::fetchOne(
            "SELECT AVG(DATEDIFF(actual_close_date, created_at)) as avg FROM deals d
             JOIN stages s ON s.id=d.stage_id
             WHERE d.deleted_at IS NULL AND s.is_won = 1 AND actual_close_date IS NOT NULL $verticalFilter",
            $params
        );

        return [
            'pipeline_value'   => (float) $pipeline['total'],
            'conversion_rate'  => $total['c'] > 0 ? round($won['c'] / $total['c'] * 100, 1) : 0,
            'average_ticket'   => round((float) $avgTicket['avg'], 2),
            'avg_close_days'   => round((float) $avgClose['avg'], 1),
        ];
    }

    public static function funnelData(int $verticalId = 0): array
    {
        $params = [];
        $filter = '';
        if ($verticalId) {
            $filter = "AND d.vertical_id = ?";
            $params[] = $verticalId;
        }
        return Database::fetchAll(
            "SELECT s.name, s.color, COUNT(d.id) as count, COALESCE(SUM(d.amount),0) as total
             FROM stages s
             LEFT JOIN deals d ON d.stage_id = s.id AND d.deleted_at IS NULL $filter
             ORDER BY s.position",
            $params
        );
    }

    public static function upcoming(int $days = 7, ?int $userId = null, string $role = 'vendedor'): array
    {
        $params = [date('Y-m-d'), date('Y-m-d', strtotime("+{$days} days"))];
        $filter = '';
        if ($role === 'vendedor' && $userId) {
            $filter = "AND d.assigned_to = ?";
            $params[] = $userId;
        }
        return Database::fetchAll(
            "SELECT d.*, c.name as client_name, s.name as stage_name, s.color as stage_color
             FROM deals d
             JOIN clients c ON c.id = d.client_id
             JOIN stages s ON s.id = d.stage_id
             WHERE d.deleted_at IS NULL AND s.is_won = 0 AND s.is_lost = 0
               AND d.expected_close_date BETWEEN ? AND ? $filter
             ORDER BY d.expected_close_date ASC",
            $params
        );
    }

    public static function forReport(array $filters = [], ?int $userId = null, string $role = 'admin'): array
    {
        [$where, $params] = self::buildWhere($filters, $userId, $role);
        $sql = "SELECT d.*, c.name as client_name, c.company_name,
                       s.name as stage_name, s.is_won, s.is_lost,
                       v.name as vertical_name,
                       u.name as assigned_name
                FROM deals d
                JOIN clients c ON c.id = d.client_id
                JOIN stages s ON s.id = d.stage_id
                JOIN verticals v ON v.id = d.vertical_id
                LEFT JOIN users u ON u.id = d.assigned_to
                WHERE d.deleted_at IS NULL " . ($where ? "AND $where" : "") . "
                ORDER BY d.created_at DESC";
        return Database::fetchAll($sql, $params);
    }

    private static function buildWhere(array $filters, ?int $userId, string $role): array
    {
        $where  = [];
        $params = [];

        if ($role === 'vendedor' && $userId) {
            $where[]  = "d.assigned_to = ?";
            $params[] = $userId;
        }
        if (!empty($filters['search'])) {
            $s = '%' . $filters['search'] . '%';
            $where[]  = "(d.title LIKE ? OR c.name LIKE ? OR c.company_name LIKE ?)";
            $params[] = $s; $params[] = $s; $params[] = $s;
        }
        if (!empty($filters['vertical_id'])) {
            $where[]  = "d.vertical_id = ?";
            $params[] = (int) $filters['vertical_id'];
        }
        if (!empty($filters['stage_id'])) {
            $where[]  = "d.stage_id = ?";
            $params[] = (int) $filters['stage_id'];
        }
        if (!empty($filters['assigned_to'])) {
            $where[]  = "d.assigned_to = ?";
            $params[] = (int) $filters['assigned_to'];
        }
        if (!empty($filters['date_from'])) {
            $where[]  = "d.created_at >= ?";
            $params[] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $where[]  = "d.created_at <= ?";
            $params[] = $filters['date_to'] . ' 23:59:59';
        }
        return [implode(' AND ', $where), $params];
    }

    private static function resolveSort(string $field, string $dir): string
    {
        $allowed = ['title', 'amount', 'created_at', 'expected_close_date', 'probability'];
        $field   = in_array($field, $allowed) ? $field : 'created_at';
        $dir     = strtolower($dir) === 'asc' ? 'ASC' : 'DESC';
        return "d.{$field} {$dir}";
    }
}
