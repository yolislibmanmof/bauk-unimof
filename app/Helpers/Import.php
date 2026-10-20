<?php
/**
 * =====================================================
 * BAUK UNIMOF - IMPORT HELPER
 * Import CSV massal (tanpa Composer) + template
 * =====================================================
 */

namespace App\Helpers;

class Import
{
    /**
     * Proses file upload CSV -> import pegawai
     * Return: ['success'=>n, 'failed'=>n, 'errors'=>[], 'preview'=>[]]
     */
    public static function importPegawaiFromUpload(array $file): array
    {
        $result = ['success' => 0, 'failed' => 0, 'errors' => [], 'preview' => []];

        $ext = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
        if (!in_array($ext, ['csv', 'txt'], true)) {
            $result['errors'][] = 'Format file harus .csv (simpan Excel sebagai CSV UTF-8)';
            return $result;
        }
        if (($file['size'] ?? 0) > 5 * 1024 * 1024) {
            $result['errors'][] = 'Ukuran file maksimal 5MB';
            return $result;
        }

        $handle = @fopen($file['tmp_name'], 'r');
        if (!$handle) { $result['errors'][] = 'Gagal membaca file'; return $result; }

        $db = Database::getInstance();
        $headers = null;
        $rowNum = 0;

        while (($row = fgetcsv($handle)) !== false) {
            $rowNum++;
            // Skip BOM di baris pertama
            if ($rowNum === 1 && isset($row[0])) $row[0] = preg_replace('/^\xEF\xBB\xBF/', '', $row[0]);
            if ($headers === null) {
                $headers = array_map(fn($h) => strtolower(trim((string)$h)), $row);
                continue;
            }
            if (count(array_filter($row, fn($c) => trim((string)$c) !== '')) === 0) continue;

            $data = array_combine(
                array_slice($headers, 0, count($row)),
                array_slice($row, 0, count($headers))
            ) ?: [];
            $get = fn(...$keys) => self::pick($data, $keys);

            $nip  = trim((string)$get('nip'));
            $name = trim((string)$get('nama lengkap', 'full_name', 'nama'));
            $errors = [];
            if ($nip === '')  $errors[] = 'NIP kosong';
            if ($name === '') $errors[] = 'Nama kosong';
            if ($nip !== '' && $db->fetch("SELECT id FROM pegawai WHERE nip = ? AND deleted_at IS NULL", [$nip])) {
                $errors[] = 'NIP duplikat';
            }
            $email = trim((string)$get('email'));
            if ($email !== '' && !Security::isValidEmail($email)) $errors[] = 'Email tidak valid';

            if ($errors) {
                $result['failed']++;
                $result['errors'][] = "Baris {$rowNum}: " . implode(', ', $errors);
                continue;
            }

            $deptId = null;
            $deptName = trim((string)$get('departemen', 'department'));
            if ($deptName !== '') {
                $d = $db->fetch("SELECT id FROM departments WHERE name = ? AND deleted_at IS NULL", [$deptName]);
                $deptId = $d['id'] ?? null;
            }

            $id = $db->insert('pegawai', [
                'uuid'          => Security::generateUuid(),
                'nip'           => $nip,
                'full_name'     => $name,
                'nik'           => trim((string)$get('nik')) ?: null,
                'gender'        => strtoupper(substr(trim((string)$get('jenis kelamin', 'gender', 'jk')) ?: 'L', 0, 1)) === 'P' ? 'P' : 'L',
                'birth_place'   => trim((string)$get('tempat lahir', 'birth_place')) ?: null,
                'birth_date'    => self::normDate($get('tanggal lahir', 'birth_date')),
                'phone'         => trim((string)$get('telepon', 'phone', 'hp')) ?: null,
                'email'         => $email ?: null,
                'address'       => trim((string)$get('alamat', 'address')) ?: null,
                'position'      => trim((string)$get('jabatan', 'position')) ?: null,
                'department_id' => $deptId,
                'employment_status' => trim((string)$get('status kepegawaian', 'employment_status')) ?: 'tetap',
                'status'        => in_array(strtolower((string)$get('status')), ['active','inactive','retired','resigned'], true) ? strtolower((string)$get('status')) : 'active',
                'join_date'     => self::normDate($get('tanggal masuk', 'join_date')),
                'created_by'    => Auth::id(),
            ]);

            if ($id) {
                $result['success']++;
                $result['preview'][] = ['nip' => $nip, 'nama' => $name];
            } else {
                $result['failed']++;
                $result['errors'][] = "Baris {$rowNum}: gagal insert ke database";
            }
        }
        fclose($handle);

        if ($result['success'] > 0) {
            ActivityLog::create(Auth::id(), 'import', "Import CSV pegawai: {$result['success']} berhasil, {$result['failed']} gagal", 'pegawai');
        }
        return $result;
    }

    /**
     * Download template CSV import pegawai
     */
    public static function downloadPegawaiTemplate(): void
    {
        Export::csv(
            [[
                'nip' => '199001012020011001', 'nama lengkap' => 'Contoh Nama', 'nik' => '1234567890123456',
                'jenis kelamin' => 'L', 'tempat lahir' => 'Maumere', 'tanggal lahir' => '1990-01-01',
                'alamat' => 'Jl. Contoh No. 1', 'telepon' => '08123456789', 'email' => 'contoh@unimof.ac.id',
                'jabatan' => 'Staff Admin', 'departemen' => 'BAUK', 'status' => 'active',
            ]],
            ['nip','nama lengkap','nik','jenis kelamin','tempat lahir','tanggal lahir','alamat','telepon','email','jabatan','departemen','status'],
            'template-import-pegawai'
        );
    }

    private static function pick(array $data, array $keys)
    {
        foreach ($keys as $k) {
            if (isset($data[$k]) && trim((string)$data[$k]) !== '') return $data[$k];
        }
        return '';
    }

    private static function normDate($value): ?string
    {
        $v = trim((string)$value);
        if ($v === '') return null;
        // dukung d/m/Y dan Y-m-d
        $ts = strtotime(str_replace('/', '-', $v));
        return $ts ? date('Y-m-d', $ts) : null;
    }
}