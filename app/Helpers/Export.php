<?php
/**
 * =====================================================
 * BAUK UNIMOF - EXPORT HELPER
 * CSV / Excel / PDF-Print TANPA Composer (murni PHP)
 * =====================================================
 */

namespace App\Helpers;

class Export
{
    /**
     * Download CSV (buka di Excel aman, ada BOM UTF-8)
     */
    public static function csv(array $rows, array $headers, string $filename): void
    {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . self::safeName($filename) . '.csv"');
        $out = fopen('php://output', 'w');
        fwrite($out, "\xEF\xBB\xBF");
        fputcsv($out, $headers);
        foreach ($rows as $r) {
            $line = [];
            foreach ($headers as $h) $line[] = $r[$h] ?? '';
            fputcsv($out, $line);
        }
        fclose($out);
        exit;
    }

    /**
     * Download Excel (.xls via HTML table - dibuka sempurna di MS Excel)
     */
    public static function excel(array $rows, array $headers, string $filename, string $title = ''): void
    {
        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . self::safeName($filename) . '.xls"');
        echo "\xEF\xBB\xBF";
        echo '<html><head><meta charset="UTF-8"></head><body>';
        if ($title !== '') echo '<h2>' . htmlspecialchars($title) . '</h2>';
        echo '<table border="1"><thead><tr>';
        foreach ($headers as $h) echo '<th style="background:#4e73df;color:#fff;">' . htmlspecialchars($h) . '</th>';
        echo '</tr></thead><tbody>';
        foreach ($rows as $r) {
            echo '<tr>';
            foreach ($headers as $h) echo '<td>' . htmlspecialchars((string)($r[$h] ?? '')) . '</td>';
            echo '</tr>';
        }
        echo '</tbody></table></body></html>';
        exit;
    }

    /**
     * Halaman cetak rapi -> user tinggal Ctrl+P / tombol cetak -> Save as PDF
     */
    public static function printable(array $rows, array $headers, string $title): void
    {
        $siteName = 'BAUK UNIMOF';
        echo '<!DOCTYPE html><html><head><meta charset="UTF-8">';
        echo '<title>' . htmlspecialchars($title) . '</title>';
        echo '<style>
            body{font-family:Arial,sans-serif;font-size:11pt;margin:24px;color:#222}
            .head{text-align:center;border-bottom:3px double #333;padding-bottom:10px;margin-bottom:18px}
            .head h1{margin:0;font-size:16pt} .head h2{margin:4px 0;font-size:13pt;color:#555}
            .head p{margin:4px 0;font-size:9pt;color:#777}
            table{width:100%;border-collapse:collapse;margin-top:10px}
            th{background:#4e73df;color:#fff;padding:7px;border:1px solid #333;text-align:left;font-size:9pt}
            td{padding:6px;border:1px solid #999;font-size:9pt;vertical-align:top}
            tr:nth-child(even) td{background:#f4f6fb}
            .foot{margin-top:24px;font-size:9pt;color:#666;display:flex;justify-content:space-between}
            .noprint{position:fixed;top:10px;right:10px}
            .noprint button{background:#4e73df;color:#fff;border:0;padding:10px 18px;border-radius:6px;cursor:pointer;font-size:12pt}
            @media print{.noprint{display:none}}
        </style></head><body>';
        echo '<div class="noprint"><button onclick="window.print()">🖨️ Cetak / Simpan PDF</button></div>';
        echo '<div class="head"><h1>' . htmlspecialchars($siteName) . '</h1>';
        echo '<h2>' . htmlspecialchars($title) . '</h2>';
        echo '<p>Dicetak: ' . date('d F Y H:i:s') . ' | Oleh: ' . htmlspecialchars(self::currentUserName()) . '</p></div>';
        echo '<table><thead><tr>';
        foreach ($headers as $h) echo '<th>' . htmlspecialchars($h) . '</th>';
        echo '</tr></thead><tbody>';
        foreach ($rows as $r) {
            echo '<tr>';
            foreach ($headers as $h) echo '<td>' . htmlspecialchars((string)($r[$h] ?? '')) . '</td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
        echo '<div class="foot"><span>Total baris: ' . count($rows) . '</span><span>Dokumen resmi sistem BAUK UNIMOF</span></div>';
        echo '</body></html>';
        exit;
    }

    /**
     * Download JSON
     */
    public static function json(array $data, string $filename): void
    {
        header('Content-Type: application/json; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . self::safeName($filename) . '.json"');
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }

    public static function formatCurrency($amount): string
    {
        return 'Rp ' . number_format((float)$amount, 0, ',', '.');
    }

    public static function formatDate($date): string
    {
        return $date ? date('d/m/Y', strtotime($date)) : '-';
    }

    private static function safeName(string $name): string
    {
        return preg_replace('/[^a-zA-Z0-9_\-]/', '-', $name);
    }

    private static function currentUserName(): string
    {
        try {
            if (Auth::check()) {
                $u = Database::getInstance()->fetch("SELECT full_name FROM users WHERE id = ?", [Auth::id()]);
                return $u['full_name'] ?? 'System';
            }
        } catch (\Throwable $e) { /* abaikan */ }
        return 'System';
    }
}