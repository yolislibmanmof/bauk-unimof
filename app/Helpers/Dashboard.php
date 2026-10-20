<?php
/**
 * =====================================================
 * BAUK UNIMOF - DASHBOARD ANALYTICS HELPER
 * Advanced Statistics & Chart Data Generator
 * =====================================================
 */

namespace App\Helpers;

class Dashboard
{
    /**
     * Statistik komprehensif (backward-compatible dengan view lama)
     */
    public static function getStats(): array
    {
        $db = Database::getInstance();

        // === KEY LAMA (agar view dashboard lama tetap jalan) ===
        $stats = [
            'total_pegawai'       => $db->count('pegawai', "`status` = 'active' AND deleted_at IS NULL"),
            'total_aset'          => $db->count('aset', "`status` = 'active' AND deleted_at IS NULL"),
            'transaksi_bulan_ini' => $db->count('transaksi_keuangan', "MONTH(transaction_date) = MONTH(CURRENT_DATE()) AND YEAR(transaction_date) = YEAR(CURRENT_DATE()) AND deleted_at IS NULL"),
            'pengumuman_aktif'    => $db->count('pengumuman', "is_active = 1"),
        ];

        // === KEY BARU (ULTIMATE) ===
        $stats['pegawai_baru_bulan_ini'] = $db->count('pegawai', "MONTH(join_date) = MONTH(CURRENT_DATE()) AND YEAR(join_date) = YEAR(CURRENT_DATE()) AND deleted_at IS NULL");
        $stats['pegawai_per_departemen'] = $db->fetchAll("
            SELECT d.name, COUNT(p.id) AS total
            FROM departments d
            LEFT JOIN pegawai p ON d.id = p.department_id AND p.deleted_at IS NULL
            WHERE d.deleted_at IS NULL
            GROUP BY d.id, d.name
            ORDER BY total DESC
        ");
        $stats['aset_baik']          = $db->count('aset', "`condition` = 'baik' AND deleted_at IS NULL");
        $stats['aset_rusak_ringan']  = $db->count('aset', "`condition` = 'rusak_ringan' AND deleted_at IS NULL");
        $stats['aset_rusak_berat']   = $db->count('aset', "`condition` = 'rusak_berat' AND deleted_at IS NULL");
        $stats['nilai_total_aset']   = (float)($db->fetch("SELECT COALESCE(SUM(purchase_price),0) AS total FROM aset WHERE deleted_at IS NULL")['total'] ?? 0);
        $stats['pemasukan_bulan_ini']   = (float)($db->fetch("SELECT COALESCE(SUM(amount),0) AS total FROM transaksi_keuangan WHERE `type`='pemasukan' AND `status`='approved' AND MONTH(transaction_date)=MONTH(CURRENT_DATE()) AND YEAR(transaction_date)=YEAR(CURRENT_DATE()) AND deleted_at IS NULL")['total'] ?? 0);
        $stats['pengeluaran_bulan_ini'] = (float)($db->fetch("SELECT COALESCE(SUM(amount),0) AS total FROM transaksi_keuangan WHERE `type`='pengeluaran' AND `status`='approved' AND MONTH(transaction_date)=MONTH(CURRENT_DATE()) AND YEAR(transaction_date)=YEAR(CURRENT_DATE()) AND deleted_at IS NULL")['total'] ?? 0);
        $stats['transaksi_pending']  = $db->count('transaksi_keuangan', "`status`='pending' AND deleted_at IS NULL");
        $stats['kegiatan_minggu_ini'] = $db->count('kegiatan', "start_datetime >= DATE_SUB(CURRENT_DATE, INTERVAL 7 DAY)");
        $stats['login_hari_ini']     = $db->count('activity_logs', "action = 'login' AND DATE(created_at) = CURRENT_DATE");
        $stats['unread_notifications'] = Auth::check() ? Notification::getUnreadCount(Auth::id()) : 0;

        return $stats;
    }

    /**
     * Tren keuangan 12 bulan terakhir (1 query, optimal)
     */
    public static function getFinancialTrend(int $months = 12): array
    {
        $db = Database::getInstance();
        $start = date('Y-m-01', strtotime('-' . ($months - 1) . ' months'));

        $rows = $db->fetchAll(
            "SELECT YEAR(transaction_date) AS y, MONTH(transaction_date) AS m, `type`, COALESCE(SUM(amount),0) AS total
             FROM transaksi_keuangan
             WHERE `status` = 'approved' AND deleted_at IS NULL AND transaction_date >= ?
             GROUP BY YEAR(transaction_date), MONTH(transaction_date), `type`",
            [$start]
        );

        $map = [];
        foreach ($rows as $r) {
            $map[(int)$r['y'] . '-' . (int)$r['m']][$r['type']] = (float)$r['total'];
        }

        $data = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $ts  = strtotime("-{$i} months");
            $key = (int)date('Y', $ts) . '-' . (int)date('m', $ts);
            $p   = $map[$key]['pemasukan'] ?? 0;
            $g   = $map[$key]['pengeluaran'] ?? 0;
            $data[] = [
                'month'       => date('M Y', $ts),
                'pemasukan'   => $p,
                'pengeluaran' => $g,
                'saldo'       => $p - $g,
            ];
        }
        return $data;
    }

    /**
     * Perbandingan bulan ini vs bulan lalu
     */
    public static function getPeriodComparison(): array
    {
        $db = Database::getInstance();

        $cur = $db->fetchAll("SELECT `type`, COALESCE(SUM(amount),0) AS total FROM transaksi_keuangan
            WHERE `status`='approved' AND deleted_at IS NULL
              AND YEAR(transaction_date)=YEAR(CURRENT_DATE) AND MONTH(transaction_date)=MONTH(CURRENT_DATE)
            GROUP BY `type`");
        $prev = $db->fetchAll("SELECT `type`, COALESCE(SUM(amount),0) AS total FROM transaksi_keuangan
            WHERE `status`='approved' AND deleted_at IS NULL
              AND YEAR(transaction_date)=YEAR(DATE_SUB(CURRENT_DATE, INTERVAL 1 MONTH))
              AND MONTH(transaction_date)=MONTH(DATE_SUB(CURRENT_DATE, INTERVAL 1 MONTH))
            GROUP BY `type`");

        $c = ['pemasukan' => 0, 'pengeluaran' => 0];
        $p = ['pemasukan' => 0, 'pengeluaran' => 0];
        foreach ($cur as $r)  $c[$r['type']]  = (float)$r['total'];
        foreach ($prev as $r) $p[$r['type']]  = (float)$r['total'];

        return [
            'pemasukan_current'     => $c['pemasukan'],
            'pemasukan_previous'    => $p['pemasukan'],
            'pemasukan_growth'      => self::calculateGrowth($c['pemasukan'], $p['pemasukan']),
            'pengeluaran_current'   => $c['pengeluaran'],
            'pengeluaran_previous'  => $p['pengeluaran'],
            'pengeluaran_growth'    => self::calculateGrowth($c['pengeluaran'], $p['pengeluaran']),
        ];
    }

    /**
     * Timeline aktivitas
     */
    public static function getActivityTimeline(int $limit = 20): array
    {
        return Database::getInstance()->fetchAll(
            "SELECT al.*, u.full_name, u.username
             FROM activity_logs al
             LEFT JOIN users u ON al.user_id = u.id
             ORDER BY al.created_at DESC
             LIMIT " . max(1, min(100, $limit))
        );
    }

    /**
     * Transaksi terbaru
     */
    public static function getRecentTransactions(int $limit = 10): array
    {
        return Database::getInstance()->fetchAll(
            "SELECT t.*, ck.name AS category_name, u.full_name AS created_by_name
             FROM transaksi_keuangan t
             LEFT JOIN categories_keuangan ck ON t.category_id = ck.id
             LEFT JOIN users u ON t.created_by = u.id
             WHERE t.deleted_at IS NULL
             ORDER BY t.transaction_date DESC, t.id DESC
             LIMIT " . max(1, min(100, $limit))
        );
    }

    /**
     * Format rupiah
     */
    public static function formatCurrency($amount): string
    {
        return 'Rp ' . number_format((float)$amount, 0, ',', '.');
    }

    /**
     * Hitung persentase pertumbuhan
     */
    public static function calculateGrowth($current, $previous): float
    {
        if ((float)$previous == 0.0) return $current > 0 ? 100.0 : 0.0;
        return round(((($current - $previous) / $previous) * 100), 2);
    }
}