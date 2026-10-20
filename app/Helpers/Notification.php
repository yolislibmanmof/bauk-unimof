<?php
/**
 * =====================================================
 * BAUK UNIMOF - NOTIFICATION HELPER
 * Sistem notifikasi internal realtime-ready
 * =====================================================
 */

namespace App\Helpers;

class Notification
{
    public const TYPE_INFO     = 'info';
    public const TYPE_SUCCESS  = 'success';
    public const TYPE_WARNING  = 'warning';
    public const TYPE_ERROR    = 'error';
    public const TYPE_APPROVAL = 'approval';
    public const TYPE_SYSTEM   = 'system';

    public static function create(int $userId, string $type, string $message, array $data = [], ?string $title = null): int
    {
        return Database::getInstance()->insert('notifications', [
            'uuid'       => Security::generateUuid(),
            'user_id'    => $userId,
            'type'       => $type,
            'title'      => $title,
            'message'    => $message,
            'data'       => $data ? json_encode($data, JSON_UNESCAPED_UNICODE) : null,
            'is_read'    => 0,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public static function send(int $userId, string $type, string $message, array $data = [], ?string $title = null): void
    {
        self::create($userId, $type, $message, $data, $title);
    }

    public static function sendToMultiple(array $userIds, string $type, string $message, array $data = [], ?string $title = null): int
    {
        $n = 0;
        foreach (array_unique(array_map('intval', $userIds)) as $uid) {
            if ($uid > 0) { self::send($uid, $type, $message, $data, $title); $n++; }
        }
        return $n;
    }

    public static function sendToRole(string $role, string $type, string $message, array $data = [], ?string $title = null): int
    {
        $users = Database::getInstance()->fetchAll(
            "SELECT id FROM users WHERE role = ? AND deleted_at IS NULL", [$role]
        );
        return self::sendToMultiple(array_column($users, 'id'), $type, $message, $data, $title);
    }

    /**
     * Kirim ke semua admin (super_admin + admin_bauk)
     */
    public static function sendToAdmins(string $type, string $message, array $data = [], ?string $title = null): int
    {
        $users = Database::getInstance()->fetchAll(
            "SELECT id FROM users WHERE role IN ('super_admin','admin_bauk') AND deleted_at IS NULL"
        );
        return self::sendToMultiple(array_column($users, 'id'), $type, $message, $data, $title);
    }

    public static function getForUser(int $userId, int $limit = 10, bool $unreadOnly = false): array
    {
        $limit = max(1, min(100, $limit));
        $where = 'user_id = ' . $userId;
        if ($unreadOnly) $where .= ' AND is_read = 0';
        return Database::getInstance()->fetchAll(
            "SELECT * FROM notifications WHERE {$where} ORDER BY created_at DESC LIMIT {$limit}"
        );
    }

    public static function getUnreadCount(int $userId): int
    {
        return Database::getInstance()->count('notifications', 'user_id = ? AND is_read = 0', [$userId]);
    }

    public static function markAsRead(int $notificationId, int $userId): bool
    {
        return Database::getInstance()->update(
            'notifications',
            ['is_read' => 1, 'read_at' => date('Y-m-d H:i:s')],
            'id = ? AND user_id = ?',
            [$notificationId, $userId]
        ) > 0;
    }

    public static function markAllAsRead(int $userId): bool
    {
        Database::getInstance()->update(
            'notifications',
            ['is_read' => 1, 'read_at' => date('Y-m-d H:i:s')],
            'user_id = ? AND is_read = 0',
            [$userId]
        );
        return true;
    }

    public static function delete(int $notificationId, int $userId): bool
    {
        return Database::getInstance()->delete('notifications', 'id = ? AND user_id = ?', [$notificationId, $userId]) > 0;
    }

    /**
     * Notifikasi otomatis: transaksi baru menunggu approval
     */
    public static function notifyTransactionPending(string $trxNumber, float $amount): void
    {
        self::sendToAdmins(
            self::TYPE_APPROVAL,
            'Transaksi ' . $trxNumber . ' sebesar Rp ' . number_format($amount, 0, ',', '.') . ' menunggu persetujuan.',
            ['transaction_number' => $trxNumber],
            'Approval Dibutuhkan'
        );
    }
}