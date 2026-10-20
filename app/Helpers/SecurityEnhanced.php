<?php
/**
 * =====================================================
 * BAUK UNIMOF - ENHANCED SECURITY HELPER
 * 2FA (TOTP), Rate Limit, Enkripsi, Password Strength
 * =====================================================
 */

namespace App\Helpers;

class SecurityEnhanced
{
    // ===== TWO-FACTOR AUTHENTICATION (TOTP / Google Authenticator) =====

    public static function generate2FASecret(): string
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $secret = '';
        for ($i = 0; $i < 16; $i++) $secret .= $chars[random_int(0, 31)];
        return $secret;
    }

    public static function generate2FAQRUrl(string $secret, string $email, string $issuer = 'BAUK UNIMOF'): string
    {
        $otpauth = sprintf(
            'otpauth://totp/%s:%s?secret=%s&issuer=%s&algorithm=SHA1&digits=6&period=30',
            rawurlencode($issuer), rawurlencode($email), $secret, rawurlencode($issuer)
        );
        return 'https://api.qrserver.com/v1/create-qr-code/?data=' . urlencode($otpauth) . '&size=220x220';
    }

    public static function verify2FA(string $secret, string $code, int $window = 1): bool
    {
        $code = preg_replace('/\s/', '', $code);
        if (!preg_match('/^\d{6}$/', $code)) return false;
        $timeSlice = (int)floor(time() / 30);
        for ($i = -$window; $i <= $window; $i++) {
            if (hash_equals(self::calculateTOTP($secret, $timeSlice + $i), $code)) return true;
        }
        return false;
    }

    private static function calculateTOTP(string $secret, int $timeSlice): string
    {
        $key = self::base32Decode($secret);
        $time = pack('N*', 0) . pack('N*', $timeSlice);
        $hash = hash_hmac('sha1', $time, $key, true);
        $offset = ord(substr($hash, -1)) & 0x0F;
        $code = (
            ((ord($hash[$offset]) & 0x7F) << 24) |
            ((ord($hash[$offset + 1]) & 0xFF) << 16) |
            ((ord($hash[$offset + 2]) & 0xFF) << 8) |
            (ord($hash[$offset + 3]) & 0xFF)
        ) % 1000000;
        return str_pad((string)$code, 6, '0', STR_PAD_LEFT);
    }

    private static function base32Decode(string $input): string
    {
        $map = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $input = strtoupper(preg_replace('/[^A-Za-z2-7]/', '', $input));
        $buffer = 0; $bitsLeft = 0; $output = '';
        for ($i = 0, $len = strlen($input); $i < $len; $i++) {
            $val = strpos($map, $input[$i]);
            if ($val === false) continue;
            $buffer = ($buffer << 5) | $val;
            $bitsLeft += 5;
            if ($bitsLeft >= 8) {
                $output .= chr(($buffer >> ($bitsLeft - 8)) & 0xFF);
                $bitsLeft -= 8;
            }
        }
        return $output;
    }

    public static function generateBackupCodes(int $count = 10): array
    {
        $codes = [];
        for ($i = 0; $i < $count; $i++) $codes[] = strtoupper(bin2hex(random_bytes(4)));
        return $codes;
    }

    // ===== RATE LIMITING (anti brute-force) =====

    public static function rateLimit(string $key, int $maxAttempts = 5, int $windowSeconds = 300): bool
    {
        $cacheKey = 'ratelimit_' . $key;
        $data = Cache::get($cacheKey, ['count' => 0, 'reset_at' => 0]);
        if (!is_array($data) || ($data['reset_at'] ?? 0) < time()) {
            $data = ['count' => 0, 'reset_at' => time() + $windowSeconds];
        }
        if ($data['count'] >= $maxAttempts) return false;
        $data['count']++;
        Cache::set($cacheKey, $data, max(1, $data['reset_at'] - time()));
        return true;
    }

    public static function rateLimitRemaining(string $key, int $maxAttempts = 5): int
    {
        $data = Cache::get('ratelimit_' . $key, ['count' => 0]);
        return max(0, $maxAttempts - (int)($data['count'] ?? 0));
    }

    // ===== ENKRIPSI DATA =====

    public static function encrypt(string $data, string $key): string
    {
        $iv = random_bytes(16);
        $enc = openssl_encrypt($data, 'AES-256-CBC', hash('sha256', $key, true), OPENSSL_RAW_DATA, $iv);
        return base64_encode($iv . $enc);
    }

    public static function decrypt(string $payload, string $key): string
    {
        $raw = base64_decode($payload, true);
        if ($raw === false || strlen($raw) < 17) return '';
        $iv  = substr($raw, 0, 16);
        $enc = substr($raw, 16);
        $dec = openssl_decrypt($enc, 'AES-256-CBC', hash('sha256', $key, true), OPENSSL_RAW_DATA, $iv);
        return $dec === false ? '' : $dec;
    }

    // ===== PASSWORD STRENGTH =====

    public static function checkPasswordStrength(string $password): array
    {
        $score = 0; $feedback = [];
        if (strlen($password) >= 8) $score++; else $feedback[] = 'Minimal 8 karakter';
        if (preg_match('/[A-Z]/', $password)) $score++; else $feedback[] = 'Huruf kapital';
        if (preg_match('/[a-z]/', $password)) $score++; else $feedback[] = 'Huruf kecil';
        if (preg_match('/[0-9]/', $password)) $score++; else $feedback[] = 'Angka';
        if (preg_match('/[^A-Za-z0-9]/', $password)) $score++; else $feedback[] = 'Simbol';
        return [
            'score'    => $score,
            'strength' => $score >= 4 ? 'strong' : ($score >= 3 ? 'medium' : 'weak'),
            'feedback' => $feedback,
        ];
    }

    // ===== TOKEN AMAN =====

    public static function generateToken(int $bytes = 32): string
    {
        return bin2hex(random_bytes($bytes));
    }
}