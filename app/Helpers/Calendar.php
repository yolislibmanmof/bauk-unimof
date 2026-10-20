<?php
/**
 * =====================================================
 * BAUK UNIMOF - CALENDAR HELPER
 * Agenda kegiatan + export iCal (Google/Outlook Calendar)
 * =====================================================
 */

namespace App\Helpers;

class Calendar
{
    public static function getUpcomingEvents(int $days = 7, int $limit = 10): array
    {
        $days  = max(1, min(365, $days));
        $limit = max(1, min(100, $limit));
        return Database::getInstance()->fetchAll(
            "SELECT k.*, d.name AS dept_name
             FROM kegiatan k
             LEFT JOIN departments d ON k.department_id = d.id
             WHERE k.start_datetime >= CURRENT_DATE
               AND k.start_datetime <= DATE_ADD(CURRENT_DATE, INTERVAL {$days} DAY)
             ORDER BY k.start_datetime ASC
             LIMIT {$limit}"
        );
    }

    public static function getEventsByRange(string $start, string $end): array
    {
        return Database::getInstance()->fetchAll(
            "SELECT k.*, d.name AS dept_name, d.code AS dept_code
             FROM kegiatan k
             LEFT JOIN departments d ON k.department_id = d.id
             WHERE k.start_datetime >= ? AND k.start_datetime <= ?
             ORDER BY k.start_datetime ASC",
            [$start, $end]
        );
    }

    public static function getEventsByDate(string $date): array
    {
        return Database::getInstance()->fetchAll(
            "SELECT k.*, d.name AS dept_name
             FROM kegiatan k
             LEFT JOIN departments d ON k.department_id = d.id
             WHERE DATE(k.start_datetime) = ?
             ORDER BY k.start_datetime ASC",
            [$date]
        );
    }

    /**
     * Format untuk FullCalendar.js (opsional)
     */
    public static function toFullCalendar(array $events): array
    {
        return array_map(fn($e) => [
            'id'      => (int)$e['id'],
            'title'   => $e['title'],
            'start'   => $e['start_datetime'],
            'end'     => $e['end_datetime'] ?: $e['start_datetime'],
            'allDay'  => (bool)$e['is_all_day'],
            'color'   => self::getEventColor($e),
            'location'=> $e['location'],
            'department' => $e['dept_name'] ?? null,
            'status'  => $e['status'],
        ], $events);
    }

    public static function getEventColor(array $event): string
    {
        $colors = [
            'BAUK' => '#4e73df', 'KEPEG' => '#1cc88a', 'KEU' => '#f6c23e',
            'ASET' => '#36b9cc', 'K3L' => '#e74a3b',
        ];
        $code = strtoupper((string)($event['dept_code'] ?? ''));
        foreach ($colors as $prefix => $color) {
            if (str_starts_with($code, $prefix)) return $color;
        }
        return '#858796';
    }

    public static function createEvent(array $data): int
    {
        $id = Database::getInstance()->insert('kegiatan', [
            'uuid'            => Security::generateUuid(),
            'title'           => $data['title'],
            'description'     => $data['description'] ?? null,
            'location'        => $data['location'] ?? null,
            'start_datetime'  => $data['start_datetime'],
            'end_datetime'    => $data['end_datetime'] ?? null,
            'is_all_day'      => !empty($data['is_all_day']) ? 1 : 0,
            'department_id'   => $data['department_id'] ?? null,
            'organizer'       => $data['organizer'] ?? null,
            'status'          => $data['status'] ?? 'planned',
            'created_by'      => Auth::id(),
        ]);
        ActivityLog::create(Auth::id(), 'create', 'Agenda baru: ' . $data['title'], 'kegiatan', $id);
        return $id;
    }

    public static function updateEvent(int $id, array $data): bool
    {
        $ok = Database::getInstance()->update('kegiatan', [
            'title'          => $data['title'],
            'description'    => $data['description'] ?? null,
            'location'       => $data['location'] ?? null,
            'start_datetime' => $data['start_datetime'],
            'end_datetime'   => $data['end_datetime'] ?? null,
            'is_all_day'     => !empty($data['is_all_day']) ? 1 : 0,
            'department_id'  => $data['department_id'] ?? null,
            'organizer'      => $data['organizer'] ?? null,
            'status'         => $data['status'] ?? 'planned',
        ], 'id = ?', [$id]) > 0;
        if ($ok) ActivityLog::create(Auth::id(), 'update', 'Update agenda ID ' . $id, 'kegiatan', $id);
        return $ok;
    }

    public static function deleteEvent(int $id): bool
    {
        $ok = Database::getInstance()->delete('kegiatan', 'id = ?', [$id]) > 0;
        if ($ok) ActivityLog::create(Auth::id(), 'delete', 'Hapus agenda ID ' . $id, 'kegiatan', $id);
        return $ok;
    }

    /**
     * Export iCal (.ics) - bisa di-import ke Google/Outlook/Apple Calendar
     */
    public static function exportToICal(array $events): string
    {
        $esc = fn($s) => str_replace(["\\", ";", ",", "\n"], ["\\\\", "\\;", "\\,", "\\n"], (string)$s);
        $ical  = "BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-//BAUK UNIMOF//Agenda//ID\r\nCALSCALE:GREGORIAN\r\nMETHOD:PUBLISH\r\n";
        foreach ($events as $e) {
            $ical .= "BEGIN:VEVENT\r\n";
            $ical .= 'UID:bauk-kegiatan-' . $e['id'] . '@unimof.ac.id' . "\r\n";
            $ical .= 'DTSTAMP:' . gmdate('Ymd\THis\Z') . "\r\n";
            $ical .= 'DTSTART:' . date('Ymd\THis', strtotime($e['start_datetime'])) . "\r\n";
            $ical .= 'DTEND:' . date('Ymd\THis', strtotime($e['end_datetime'] ?: $e['start_datetime'])) . "\r\n";
            $ical .= 'SUMMARY:' . $esc($e['title']) . "\r\n";
            if (!empty($e['location']))    $ical .= 'LOCATION:' . $esc($e['location']) . "\r\n";
            if (!empty($e['description'])) $ical .= 'DESCRIPTION:' . $esc($e['description']) . "\r\n";
            $ical .= "END:VEVENT\r\n";
        }
        $ical .= "END:VCALENDAR\r\n";
        return $ical;
    }

    public static function downloadICal(array $events, string $filename = 'agenda-bauk-unimof'): void
    {
        $ical = self::exportToICal($events);
        header('Content-Type: text/calendar; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . preg_replace('/[^a-zA-Z0-9_\-]/', '-', $filename) . '.ics"');
        echo $ical;
        exit;
    }
}