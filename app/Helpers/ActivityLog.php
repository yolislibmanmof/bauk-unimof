<?php
/**
 * Activity Logger - Mencatat semua aktivitas penting
 */

declare(strict_types=1);

namespace App\Helpers;

class ActivityLog
{
    /**
     * Create activity log
     */
    public static function create(
        ?int $userId, 
        string $action, 
        string $description = '', 
        ?string $tableName = null, 
        ?int $recordId = null, 
        ?array $oldValues = null, 
        ?array $newValues = null
    ): void {
        try {
            $db = Database::getInstance();
            $db->insert('activity_logs', [
                'user_id'     => $userId,
                'action'      => $action,
                'table_name'  => $tableName,
                'record_id'   => $recordId,
                'old_values'  => $oldValues ? json_encode($oldValues) : null,
                'new_values'  => $newValues ? json_encode($newValues) : null,
                'ip_address'  => Security::getClientIp(),
                'user_agent'  => $_SERVER['HTTP_USER_AGENT'] ?? '',
                'description' => $description,
            ]);
        } catch (\Throwable $e) {
            error_log('Activity Log Error: ' . $e->getMessage());
        }
    }
}