<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title><?= htmlspecialchars($title ?? 'Login - BAUK UNIMOF') ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= asset('css/admin-style.css') ?>">
</head>
<body class="login-body">
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="login-logo"><div class="logo-icon">B</div></div>
                <h1>BAUK UNIMOF</h1>
                <p>Admin Panel Login</p>
            </div>

            <?php if (isset($_SESSION['flash_error'])): ?>
                <div class="login-alert login-alert-error"><i class="bi bi-exclamation-circle"></i> <?= htmlspecialchars($_SESSION['flash_error']) ?></div>
                <?php unset($_SESSION['flash_error']); endif; ?>

            <form method="POST" action="<?= url('admin/login') ?>" class="login-form" id="loginForm">
                <input type="hidden" name="csrf_token" value="<?= \App\Helpers\Security::generateCsrfToken() ?>">
                <div class="form-group">
                    <label for="username"><i class="bi bi-person"></i> Username / Email</label>
                    <input type="text" id="username" name="username" placeholder="Masukkan username" required autocomplete="username" autofocus>
                </div>
                <div class="form-group">
                    <label for="password"><i class="bi bi-lock"></i> Password</label>
                    <div class="password-wrapper">
                        <input type="password" id="password" name="password" placeholder="Masukkan password" required autocomplete="current-password">
                        <button type="button" class="password-toggle" id="togglePassword"><i class="bi bi-eye"></i></button>
                    </div>
                </div>
                <button type="submit" class="btn-login"><i class="bi bi-box-arrow-in-right"></i> Masuk</button>
            </form>

            <div class="login-footer">
                <a href="<?= url() ?>"><i class="bi bi-arrow-left"></i> Kembali ke Website</a>
                <p class="login-version">v1.0.0 &bull; PHP <?= PHP_VERSION ?></p>
            </div>
        </div>
        <div class="login-decoration">
            <div class="decoration-circle circle-1"></div>
            <div class="decoration-circle circle-2"></div>
            <div class="decoration-circle circle-3"></div>
            <div class="decoration-text">
                <h2>Selamat Datang</h2>
                <p>Biro Administrasi Umum & Keuangan<br>Universitas Muhammadiyah Maumere</p>
            </div>
        </div>
    </div>
    <script>
        document.getElementById('togglePassword').addEventListener('click', function () {
            const p = document.getElementById('password');
            const i = this.querySelector('i');
            p.type = p.type === 'password' ? 'text' : 'password';
            i.classList.toggle('bi-eye'); i.classList.toggle('bi-eye-slash');
        });
    </script>
</body>
</html>