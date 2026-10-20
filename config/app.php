<?php
/**
 * Konfigurasi Aplikasi BAUK UNIMOF
 */

declare(strict_types=1);

return [
    'name'          => 'BAUK UNIMOF',
    'version'       => '1.0.0',
    'url'           => $_ENV['APP_URL'] ?? 'http://localhost/bauk-unimof',
    'timezone'      => 'Asia/Makassar',
    'locale'        => 'id_ID',
    'encryption_key' => $_ENV['APP_KEY'] ?? 'BAUK-UNIMOF-SECRET-KEY-2026-CHANGE-THIS-IN-PRODUCTION',
    
    'session' => [
        'name'     => 'bauk_session',
        'lifetime' => 120, // menit
        'path'     => '/',
        'domain'   => '',
        'secure'   => false, // true di production
        'httponly'  => true,
        'samesite'  => 'Strict',
    ],

    'upload' => [
        'max_size'    => 10 * 1024 * 1024, // 10MB
        'allowed_ext' => ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx'],
        'path'        => __DIR__ . '/../public/uploads/',
    ],

    'pagination' => [
        'per_page' => 15,
    ],

    'maintenance_mode' => false,
    'debug' => $_ENV['APP_DEBUG'] ?? true,
];