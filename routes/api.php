// API Routes
Router::get('/api/dashboard/stats', function () {
    Api::authenticate();
    Api::success(Dashboard::getStats());
});

Router::get('/api/pegawai', function () {
    Api::authenticate();
    $db = Database::getInstance();
    $pegawai = $db->fetchAll("SELECT * FROM pegawai WHERE deleted_at IS NULL");
    Api::success($pegawai);
});