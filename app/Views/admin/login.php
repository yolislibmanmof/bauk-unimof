<?php
/**
 * =====================================================
 * BAUK UNIMOF - ADMIN LOGIN (ULTIMATE EDITION)
 * Secure • Elegant • Professional
 * =====================================================
 */
$siteName = 'BAUK UNIMOF';
$siteDesc = 'Biro Administrasi Umum & Keuangan<br>Universitas Muhammadiyah Maumere';

try {
    $db = \App\Helpers\Database::getInstance();
    $name = $db->fetch("SELECT `value` FROM settings WHERE `key` = 'site_name'");
    $desc = $db->fetch("SELECT `value` FROM settings WHERE `key` = 'site_description'");
    if ($name && !empty($name['value'])) $siteName = $name['value'];
    if ($desc && !empty($desc['value'])) $siteDesc = nl2br(htmlspecialchars($desc['value']));
} catch (\Throwable $e) {
    // Fallback aman jika DB belum siap
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#0d2f4a">
    <title><?= htmlspecialchars($title ?? 'Login Admin - ' . $siteName) ?></title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= asset('css/admin-style.css') ?>">

    <style>
        /* ===== LOGIN ULTIMATE STYLES ===== */
        :root {
            --login-primary: #1a5276;
            --login-primary-dark: #0d2f4a;
            --login-accent: #e67e22;
            --login-bg: #f8fafc;
            --login-text: #33475b;
            --login-text-muted: #64748b;
            --login-border: #e2e8f0;
        }

        .login-body {
            margin: 0;
            padding: 0;
            font-family: 'Inter', sans-serif;
            background: var(--login-bg);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow-x: hidden;
        }

        .login-wrapper {
            display: grid;
            grid-template-columns: 1fr 1.2fr;
            width: 100%;
            max-width: 1200px;
            min-height: 600px;
            background: #fff;
            border-radius: 24px;
            box-shadow: 0 25px 50px -12px rgba(13, 47, 74, 0.15);
            overflow: hidden;
            margin: 1rem;
            animation: loginFadeIn 0.6s ease-out;
        }

        @keyframes loginFadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* ===== LEFT SIDE: FORM ===== */
        .login-form-side {
            padding: 3rem 3.5rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
        }

        .login-brand {
            margin-bottom: 2.5rem;
        }

        .login-brand-icon {
            width: 56px;
            height: 56px;
            background: linear-gradient(135deg, var(--login-primary), var(--login-primary-dark));
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 1.25rem;
            box-shadow: 0 10px 25px rgba(26, 82, 118, 0.3);
        }

        .login-brand h1 {
            font-family: 'Playfair Display', serif;
            font-size: 1.75rem;
            color: var(--login-primary-dark);
            margin: 0 0 0.5rem 0;
        }

        .login-brand p {
            color: var(--login-text-muted);
            font-size: 0.9rem;
            margin: 0;
        }

        .login-alert {
            padding: 1rem 1.25rem;
            border-radius: 12px;
            font-size: 0.88rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
            animation: shake 0.4s ease-in-out;
        }

        .login-alert-error {
            background: rgba(239, 68, 68, 0.08);
            color: #dc2626;
            border: 1px solid rgba(239, 68, 68, 0.2);
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        .login-form .form-group {
            margin-bottom: 1.5rem;
        }

        .login-form label {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--login-text);
            margin-bottom: 0.5rem;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper i.input-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--login-text-muted);
            font-size: 1.1rem;
            transition: color 0.2s;
        }

        .login-form input[type="text"],
        .login-form input[type="password"] {
            width: 100%;
            padding: 0.85rem 1rem 0.85rem 2.75rem;
            border: 1.5px solid var(--login-border);
            border-radius: 12px;
            font-size: 0.95rem;
            font-family: 'Inter', sans-serif;
            color: var(--login-text);
            background: #fff;
            transition: all 0.2s;
            box-sizing: border-box;
        }

        .login-form input:focus {
            outline: none;
            border-color: var(--login-primary);
            box-shadow: 0 0 0 4px rgba(26, 82, 118, 0.1);
        }

        .login-form input:focus + i.input-icon,
        .login-form input:focus ~ i.input-icon {
            color: var(--login-primary);
        }

        .password-toggle {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--login-text-muted);
            cursor: pointer;
            font-size: 1.1rem;
            padding: 0.25rem;
            transition: color 0.2s;
        }

        .password-toggle:hover {
            color: var(--login-primary);
        }

        .btn-login {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, var(--login-primary), var(--login-primary-dark));
            color: #fff;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 700;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 0.5rem;
            box-shadow: 0 10px 25px rgba(26, 82, 118, 0.3);
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 35px rgba(26, 82, 118, 0.4);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .btn-login:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        .login-spinner {
            display: none;
            width: 20px;
            height: 20px;
            border: 2.5px solid rgba(255, 255, 255, 0.3);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .login-footer {
            margin-top: 2.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--login-border);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.75rem;
        }

        .login-footer a {
            color: var(--login-text-muted);
            text-decoration: none;
            font-size: 0.88rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            transition: color 0.2s;
        }

        .login-footer a:hover {
            color: var(--login-primary);
        }

        .login-version {
            font-size: 0.75rem;
            color: var(--login-text-muted);
            margin: 0;
        }

        /* ===== RIGHT SIDE: VISUAL ===== */
        .login-visual-side {
            background: linear-gradient(135deg, var(--login-primary-dark) 0%, var(--login-primary) 50%, #2980b9 100%);
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 3rem;
            overflow: hidden;
        }

        .login-visual-side::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Ccircle cx='30' cy='30' r='1.5'/%3E%3C/g%3E%3C/svg%3E");
        }

        .visual-content {
            position: relative;
            z-index: 2;
            text-align: center;
            color: #fff;
            max-width: 400px;
        }

        .visual-content h2 {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            margin: 0 0 1rem 0;
            line-height: 1.2;
        }

        .visual-content p {
            font-size: 1.05rem;
            line-height: 1.7;
            color: rgba(255, 255, 255, 0.85);
            margin: 0;
        }

        .visual-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            padding: 0.5rem 1.25rem;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        /* Floating shapes */
        .floating-shape {
            position: absolute;
            border-radius: 50%;
            filter: blur(60px);
            opacity: 0.4;
            z-index: 1;
        }

        .shape-1 {
            width: 300px;
            height: 300px;
            background: var(--login-accent);
            top: -50px;
            right: -50px;
            animation: float 15s ease-in-out infinite;
        }

        .shape-2 {
            width: 250px;
            height: 250px;
            background: #2ecc71;
            bottom: -50px;
            left: -50px;
            animation: float 20s ease-in-out infinite reverse;
        }

        @keyframes float {
            0%, 100% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(30px, -30px) scale(1.1); }
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 992px) {
            .login-wrapper {
                grid-template-columns: 1fr;
                max-width: 500px;
            }
            .login-visual-side {
                display: none;
            }
            .login-form-side {
                padding: 2.5rem 2rem;
            }
        }

        @media (max-width: 480px) {
            .login-wrapper {
                margin: 0;
                border-radius: 0;
                min-height: 100vh;
            }
            .login-form-side {
                padding: 2rem 1.5rem;
            }
        }
    </style>
</head>
<body class="login-body">

    <div class="login-wrapper">
        <!-- LEFT: FORM -->
        <div class="login-form-side">
            <div class="login-brand">
                <div class="login-brand-icon">B</div>
                <h1><?= htmlspecialchars($siteName) ?></h1>
                <p>Silakan masuk ke panel administrasi</p>
            </div>

            <?php if (isset($_SESSION['flash_error'])): ?>
                <div class="login-alert login-alert-error">
                    <i class="bi bi-exclamation-circle-fill"></i> 
                    <span><?= htmlspecialchars($_SESSION['flash_error']) ?></span>
                </div>
                <?php unset($_SESSION['flash_error']); endif; ?>

            <form method="POST" action="<?= url('admin/login') ?>" class="login-form" id="loginForm">
                <input type="hidden" name="csrf_token" value="<?= \App\Helpers\Security::generateCsrfToken() ?>">
                
                <div class="form-group">
                    <label for="username">Username atau Email</label>
                    <div class="input-wrapper">
                        <input type="text" id="username" name="username" placeholder="Masukkan username" required autocomplete="username" autofocus>
                        <i class="bi bi-person input-icon"></i>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-wrapper">
                        <input type="password" id="password" name="password" placeholder="Masukkan password" required autocomplete="current-password">
                        <i class="bi bi-lock input-icon"></i>
                        <button type="button" class="password-toggle" id="togglePassword" aria-label="Tampilkan password">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-login" id="submitBtn">
                    <span class="btn-text"><i class="bi bi-box-arrow-in-right"></i> Masuk ke Sistem</span>
                    <span class="login-spinner" id="loginSpinner"></span>
                </button>
            </form>

            <div class="login-footer">
                <a href="<?= url() ?>"><i class="bi bi-arrow-left"></i> Kembali ke Website Publik</a>
                <p class="login-version">Secure Login &bull; v1.0.0 &bull; PHP <?= PHP_VERSION ?></p>
            </div>
        </div>

        <!-- RIGHT: VISUAL -->
        <div class="login-visual-side">
            <div class="floating-shape shape-1"></div>
            <div class="floating-shape shape-2"></div>
            
            <div class="visual-content">
                <div class="visual-badge">
                    <i class="bi bi-shield-lock-fill"></i> Secure Admin Panel
                </div>
                <h2>Selamat Datang</h2>
                <p><?= $siteDesc ?></p>
            </div>
        </div>
    </div>

    <script>
        (function() {
            'use strict';
            
            // ===== PASSWORD TOGGLE =====
            const toggleBtn = document.getElementById('togglePassword');
            const passInput = document.getElementById('password');
            
            if (toggleBtn && passInput) {
                toggleBtn.addEventListener('click', function() {
                    const isPassword = passInput.type === 'password';
                    passInput.type = isPassword ? 'text' : 'password';
                    
                    const icon = this.querySelector('i');
                    icon.classList.toggle('bi-eye');
                    icon.classList.toggle('bi-eye-slash');
                    
                    // Keep focus on input
                    passInput.focus();
                });
            }

            // ===== FORM SUBMIT LOADING STATE =====
            const form = document.getElementById('loginForm');
            const submitBtn = document.getElementById('submitBtn');
            const btnText = submitBtn.querySelector('.btn-text');
            const spinner = document.getElementById('loginSpinner');

            if (form && submitBtn) {
                form.addEventListener('submit', function() {
                    submitBtn.disabled = true;
                    btnText.style.display = 'none';
                    spinner.style.display = 'block';
                });
            }

            // ===== AUTO-FOCUS ERROR FIELD (if any) =====
            // (Optional enhancement for future validation)
        })();
    </script>
</body>
</html>