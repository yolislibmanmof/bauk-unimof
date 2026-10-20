<?php
/**
 * =====================================================
 * BAUK UNIMOF - CHART HELPER
 * Chart Data Generator untuk Chart.js (CDN)
 * =====================================================
 */

namespace App\Helpers;

class Chart
{
    public static function getColorPalette(): array
    {
        return ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b',
                '#858796', '#6f42c1', '#dd6099', '#fd7e14', '#20c997'];
    }

    public static function barChart(array $labels, array $datasets, string $title = ''): array
    {
        return [
            'type' => 'bar',
            'title' => $title,
            'data' => ['labels' => $labels, 'datasets' => $datasets],
            'options' => [
                'responsive' => true,
                'maintainAspectRatio' => false,
                'plugins' => ['legend' => ['position' => 'top']],
                'scales' => ['y' => ['beginAtZero' => true]],
            ],
        ];
    }

    public static function lineChart(array $labels, array $datasets, string $title = ''): array
    {
        return [
            'type' => 'line',
            'title' => $title,
            'data' => ['labels' => $labels, 'datasets' => $datasets],
            'options' => [
                'responsive' => true,
                'maintainAspectRatio' => false,
                'plugins' => ['legend' => ['position' => 'top']],
                'scales' => ['y' => ['beginAtZero' => true]],
            ],
        ];
    }

    public static function doughnutChart(array $labels, array $data, string $title = ''): array
    {
        $colors = array_slice(self::getColorPalette(), 0, max(1, count($labels)));
        return [
            'type' => 'doughnut',
            'title' => $title,
            'data' => ['labels' => $labels, 'datasets' => [[
                'data' => $data,
                'backgroundColor' => $colors,
                'borderWidth' => 2,
                'borderColor' => '#ffffff',
            ]]],
            'options' => [
                'responsive' => true,
                'maintainAspectRatio' => false,
                'plugins' => ['legend' => ['position' => 'bottom']],
            ],
        ];
    }

    /**
     * Chart tren keuangan dari Dashboard::getFinancialTrend()
     */
    public static function financialTrendChart(array $trendData): array
    {
        return self::lineChart(
            array_column($trendData, 'month'),
            [
                [
                    'label' => 'Pemasukan',
                    'data' => array_column($trendData, 'pemasukan'),
                    'borderColor' => '#1cc88a',
                    'backgroundColor' => 'rgba(28,200,138,0.15)',
                    'tension' => 0.4,
                    'fill' => true,
                ],
                [
                    'label' => 'Pengeluaran',
                    'data' => array_column($trendData, 'pengeluaran'),
                    'borderColor' => '#e74a3b',
                    'backgroundColor' => 'rgba(231,74,59,0.15)',
                    'tension' => 0.4,
                    'fill' => true,
                ],
            ],
            'Tren Keuangan 12 Bulan'
        );
    }

    /**
     * Chart distribusi pegawai dari stats['pegawai_per_departemen']
     */
    public static function departmentDistributionChart(array $deptData): array
    {
        return self::doughnutChart(
            array_column($deptData, 'name'),
            array_map('intval', array_column($deptData, 'total')),
            'Distribusi Pegawai per Departemen'
        );
    }

    /**
     * Chart kondisi aset
     */
    public static function asetConditionChart(array $stats): array
    {
        return self::doughnutChart(
            ['Baik', 'Rusak Ringan', 'Rusak Berat'],
            [
                (int)($stats['aset_baik'] ?? 0),
                (int)($stats['aset_rusak_ringan'] ?? 0),
                (int)($stats['aset_rusak_berat'] ?? 0),
            ],
            'Kondisi Aset'
        );
    }

    /**
     * Render canvas + script Chart.js (butuh CDN Chart.js di layout)
     * CDN: https://cdn.jsdelivr.net/npm/chart.js
     */
    public static function canvasHtml(string $id, array $chart, string $height = '300px'): string
    {
        $json = json_encode($chart, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
        $sid  = htmlspecialchars($id, ENT_QUOTES);
        return '<div style="position:relative;height:' . htmlspecialchars($height, ENT_QUOTES) . ';">'
             . '<canvas id="' . $sid . '"></canvas></div>'
             . '<script>document.addEventListener("DOMContentLoaded",function(){'
             . 'new Chart(document.getElementById(' . json_encode($id) . '),' . $json . ');});</script>';
    }

    public static function toJson(array $chartData): string
    {
        return json_encode($chartData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}