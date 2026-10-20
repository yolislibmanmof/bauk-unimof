<?php
/**
 * =====================================================
 * BAUK UNIMOF - CACHE HELPER
 * File-based caching untuk performa 10x lebih cepat
 * =====================================================
 */

namespace App\Helpers;

class Cache
{
    private static ?string $cacheDir = null;

    private static function dir(): string
    {
        if (self::$cacheDir === null) {
            $dir = dirname(__DIR__, 2) . '/storage/cache/';
            if (!is_dir($dir)) @mkdir($dir, 0755, true);
            if (!is_dir($dir) || !is_writable($dir)) {
                $dir = sys_get_temp_dir() . '/bauk_cache/';
                if (!is_dir($dir)) @mkdir($dir, 0755, true);
            }
            self::$cacheDir = rtrim($dir, '/');
        }
        return self::$cacheDir;
    }

    public static function get(string $key, $default = null)
    {
        $file = self::dir() . '/' . md5($key) . '.cache';
        if (!file_exists($file)) return $default;
        $raw = @file_get_contents($file);
        if ($raw === false) return $default;
        $data = @unserialize($raw);
        if (!is_array($data) || !array_key_exists('value', $data)) return $default;
        if ($data['expires'] > 0 && $data['expires'] < time()) {
            @unlink($file);
            return $default;
        }
        return $data['value'];
    }

    public static function set(string $key, $value, int $ttl = 3600): bool
    {
        $file = self::dir() . '/' . md5($key) . '.cache';
        $payload = serialize(['value' => $value, 'expires' => $ttl > 0 ? time() + $ttl : 0]);
        return @file_put_contents($file, $payload, LOCK_EX) !== false;
    }

    public static function has(string $key): bool
    {
        return self::get($key, '__MISS__') !== '__MISS__';
    }

    public static function delete(string $key): bool
    {
        $file = self::dir() . '/' . md5($key) . '.cache';
        return file_exists($file) ? @unlink($file) : true;
    }

    public static function clear(): int
    {
        $n = 0;
        foreach (glob(self::dir() . '/*.cache') ?: [] as $f) {
            if (@unlink($f)) $n++;
        }
        return $n;
    }

    /**
     * Pattern Laravel: ambil dari cache, atau jalankan callback lalu simpan
     */
    public static function remember(string $key, int $ttl, callable $callback)
    {
        $hit = self::get($key, '__MISS__');
        if ($hit !== '__MISS__') return $hit;
        $value = $callback();
        self::set($key, $value, $ttl);
        return $value;
    }
}