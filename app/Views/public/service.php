<?php
/**
 * BAUK UNIMOF - Halaman Detail Layanan ULTIMATE EDITION
 * Data-driven: setiap bagian memiliki warna, icon, prosedur, SLA, dan FAQ riêng
 */

$services = [
    // =====================================================
    // KEPEGAWAIAN
    // =====================================================
    'kepegawaian' => [
        'color'    => '#1a5276',
        'gradient' => 'linear-gradient(135deg,#0d2f4a 0%,#1a5276 50%,#2980b9 100%)',
        'icon'     => 'bi-person-badge-fill',
        'title'    => 'Bagian Kepegawaian',
        'subtitle' => 'Pengelolaan sumber daya manusia yang profesional, transparan, dan berorientasi pengembangan kompetensi.',
        'description' => 'Bagian Kepegawaian bertanggung jawab dalam pengelolaan sumber daya manusia di lingkungan Universitas Muhammadiyah Maumere. Lingkup kerja meliputi administrasi kepegawaian, pengembangan kompetensi, pengelolaan data pegawai, pengembangan karir, hingga layanan kesejahteraan — seluruhnya terintegrasi dalam sistem e-Kepegawaian.',
        'stats' => [
            ['150+', 'Pegawai Terdata'],
            ['12', 'Jenis Layanan'],
            ['3 Hari', 'Rata-rata SLA'],
            ['98%', 'Kepuasan Pengguna'],
        ],
        'scope' => [
            ['bi-person-vcard-fill', 'Administrasi Kepegawaian', 'Pengelolaan data induk, mutasi, kenaikan pangkat, cuti, dan pemberkasian digital seluruh pegawai.'],
            ['bi-mortarboard-fill', 'Pengembangan Kompetensi', 'Program pelatihan, studi lanjut, workshop, seminar, dan sertifikasi profesi bagi pegawai.'],
            ['bi-graph-up-arrow', 'Pengembangan Karir', 'Pola karir, penilaian kinerja berkala, promosi jabatan, dan succession planning berbasis kompetensi.'],
            ['bi-heart-fill', 'Kesejahteraan Pegawai', 'Pengurusan gaji, tunjangan, asuransi kesehatan, dana pensiun, dan program kesejahteraan lainnya.'],
        ],
        'services' => [
            ['Pengajuan Cuti Pegawai', '3 hari kerja', 'Online'],
            ['Surat Keterangan Kerja / Aktif Bekerja', '1 hari kerja', 'Online'],
            ['Usulan Mutasi & Penugasan', '7 hari kerja', 'Online'],
            ['Kenaikan Pangkat / Golongan', 'Sesuai periode', 'Online'],
            ['Pendaftaran Diklat & Pelatihan', 'Sesuai jadwal', 'Online'],
            ['Klaim Tunjangan & Kesejahteraan', '5 hari kerja', 'Hybrid'],
        ],
        'steps' => [
            ['Ajukan Permohonan', 'Isi formulir digital melalui portal e-Kepegawaian atau datang langsung ke front office BAUK.'],
            ['Verifikasi Berkas', 'Tim kepegawaian memvalidasi kelengkapan dokumen persyaratan dalam 1x24 jam.'],
            ['Proses & Persetujuan', 'Permohonan diproses berjenjang: Kabag → Sekretaris → Kepala BAUK sesuai kewenangan.'],
            ['Penerbitan Dokumen', 'Dokumen/legalitas diterbitkan dengan tanda tangan elektronik dan terarsip digital.'],
        ],
        'requirements' => [
            'Formulir permohonan yang telah diisi lengkap',
            'Fotokopi SK Pengangkatan / SK Terakhir',
            'Kartu Tanda Pegawai (KTPeg) yang masih berlaku',
            'Dokumen pendukung sesuai jenis layanan (mis. surat dokter untuk cuti sakit)',
            'Persetujuan atasan langsung (untuk pengajuan cuti/mutasi)',
        ],
        'faq' => [
            ['Berapa lama proses pengajuan cuti?', 'Pengajuan cuti diproses maksimal 3 hari kerja sejak berkas dinyatakan lengkap. Untuk cuti sakit mendadak, pemberitahuan dapat menyusul maksimal 2 hari setelah masuk kerja dengan melampirkan surat dokter.'],
            ['Bagaimana cara mengajukan surat keterangan kerja?', 'Isi formulir online di portal e-Kepegawaian atau kirim email ke kepegawaian@unimof.ac.id. Surat selesai dalam 1 hari kerja dan dapat diambil di front office atau dikirim versi digital bertanda tangan elektronik.'],
            ['Apakah pegawai kontrak mendapat tunjangan yang sama?', 'Hak tunjangan pegawai kontrak mengikuti ketentuan dalam perjanjian kerja dan peraturan universitas yang berlaku. Detail dapat dikonsultasikan langsung ke Bagian Kepegawaian ext. 101.'],
        ],
        'contact' => ['kepegawaian@unimof.ac.id', '(0382) 21234 ext. 101', 'Kabag Kepegawaian'],
    ],

    // =====================================================
    // KEUANGAN
    // =====================================================
    'keuangan' => [
        'color'    => '#e67e22',
        'gradient' => 'linear-gradient(135deg,#7d3c0e 0%,#e67e22 50%,#f39c12 100%)',
        'icon'     => 'bi-wallet2',
        'title'    => 'Bagian Keuangan',
        'subtitle' => 'Pengelolaan keuangan universitas yang akuntabel, transparan, dan sesuai regulasi berlaku.',
        'description' => 'Bagian Keuangan bertanggung jawab atas pengelolaan keuangan universitas secara menyeluruh — mulai dari penyusunan Rencana Kerja dan Anggaran Tahunan (RKAT), pengelolaan kas, pencatatan transaksi real-time, pelaporan keuangan berkala, hingga pengawasan kepatuhan terhadap regulasi keuangan negara maupun persyarikatan.',
        'stats' => [
            ['850+', 'Transaksi/Bulan'],
            ['100%', 'Digital Recording'],
            ['5 Hari', 'SLA Pembayaran'],
            ['WTP', 'Opini Audit'],
        ],
        'scope' => [
            ['bi-clipboard-data-fill', 'Perencanaan Anggaran', 'Penyusunan RKAT berbasis kinerja bersama seluruh unit kerja setiap akhir tahun anggaran.'],
            ['bi-cash-stack', 'Pengelolaan Kas', 'Penerimaan, penyimpanan, dan pembayaran dana universitas dengan kontrol internal ketat.'],
            ['bi-receipt', 'Pencatatan & Pelaporan', 'Pembukuan real-time dan laporan keuangan bulanan, semesteran, serta tahunan sesuai standar akuntansi.'],
            ['bi-shield-lock-fill', 'Pengawasan & Kepatuhan', 'Verifikasi kepatuhan terhadap regulasi internal, pajak, dan ketentuan persyarikatan Muhammadiyah.'],
        ],
        'services' => [
            ['Pengajuan Pembayaran / Reimbursement', '5 hari kerja', 'Online'],
            ['Penerbitan Invoice & Bukti Bayar', '2 hari kerja', 'Online'],
            ['Verifikasi Dokumen Anggaran', '3 hari kerja', 'Online'],
            ['Usulan Revisi Anggaran (RKAT)', 'Sesuai periode', 'Hybrid'],
            ['Laporan Keuangan Unit Kerja', 'Bulanan', 'Online'],
            ['Konsultasi Perpajakan & Honorarium', 'By appointment', 'Offline'],
        ],
        'steps' => [
            ['Siapkan Dokumen', 'Lengkapi formulir pengajuan, bukti transaksi (kuitansi/invoice), dan persetujuan pimpinan unit.'],
            ['Submit ke Sistem', 'Unggah dokumen melalui portal e-Budgeting atau serahkan ke loket keuangan BAUK.'],
            ['Verifikasi & Validasi', 'Tim keuangan memverifikasi ketersediaan anggaran dan kelengkapan bukti dalam 2 hari kerja.'],
            ['Persetujuan Berjenjang', 'Approve oleh Kabag Keuangan hingga Kepala BAUK sesuai limit nominal transaksi.'],
            ['Pencairan Dana', 'Dana ditransfer ke rekening pemohon dan bukti transfer dikirim via email/sistem.'],
        ],
        'requirements' => [
            'Formulir pengajuan pembayaran/reimbursement tertandatangani',
            'Bukti transaksi asli (kuitansi/invoice bermaterai sesuai nominal)',
            'Rincian penggunaan dana / terms of reference (TOR)',
            'Persetujuan pimpinan unit kerja pemohon',
            'Nomor rekening bank atas nama pemohon (untuk pencairan)',
        ],
        'faq' => [
            ['Kapan jadwal pembayaran gaji dan tunjangan?', 'Gaji pokok dibayarkan setiap tanggal 1 dan tunjangan kinerja setiap tanggal 15. Jika jatuh pada hari libur, pembayaran dimajukan ke hari kerja sebelumnya. Slip gaji dapat diunduh di portal e-Kepegawaian.'],
            ['Berapa limit transaksi yang bisa disetujui langsung?', 'Transaksi hingga Rp 10 juta dapat disetujui Kepala Bagian. Di atas Rp 10 juta hingga Rp 50 juta memerlukan persetujuan Kepala BAUK, dan di atas Rp 50 juta memerlukan persetujuan Rektor.'],
            ['Bagaimana jika bukti transaksi hilang?', 'Pemohon wajib membuat surat pernyataan kehilangan bermaterai disertai laporan kepolisian (untuk nominal di atas Rp 1 juta), kemudian diverifikasi oleh tim keuangan sebelum diproses.'],
        ],
        'contact' => ['keuangan@unimof.ac.id', '(0382) 21234 ext. 102', 'Kabag Keuangan'],
    ],

    // =====================================================
    // ASET & RUMAH TANGGA
    // =====================================================
    'aset' => [
        'color'    => '#27ae60',
        'gradient' => 'linear-gradient(135deg,#0e6636 0%,#27ae60 50%,#2ecc71 100%)',
        'icon'     => 'bi-box-seam-fill',
        'title'    => 'Bagian Aset & Rumah Tangga',
        'subtitle' => 'Pengelolaan aset, sarana, dan prasarana kampus yang tertib, terawat, dan berkelanjutan.',
        'description' => 'Bagian Aset & Rumah Tangga mengelola seluruh fasilitas, sarana, dan prasarana universitas — mencakup inventarisasi dan pelabelan aset berbasis QR code, pemeliharaan terjadwal, layanan kebersihan dan keamanan, hingga pengaturan penggunaan ruang, gedung, dan kendaraan operasional kampus.',
        'stats' => [
            ['2.500+', 'Aset Terlabel QR'],
            ['1x24 Jam', 'Respon Kerusakan'],
            ['40+', 'Ruang & Gedung'],
            ['15', 'Kendaraan Operasional'],
        ],
        'scope' => [
            ['bi-qr-code-scan', 'Inventarisasi Aset', 'Pendataan, pelabelan QR code, dan penilaian kondisi seluruh aset bergerak maupun tidak bergerak.'],
            ['bi-tools', 'Pemeliharaan Terjadwal', 'Program maintenance preventif dan korektif untuk gedung, jaringan, AC, genset, dan peralatan lab.'],
            ['bi-building-fill', 'Pengelolaan Ruang & Gedung', 'Penjadwalan penggunaan ruang kuliah, rapat, aula, dan gedung serba guna secara online.'],
            ['bi-truck-fill', 'Kendaraan Operasional', 'Pengaturan peminjaman kendaraan dinas, perawatan berkala, dan manajemen bahan bakar.'],
        ],
        'services' => [
            ['Laporan Kerusakan Fasilitas', 'Respon 1x24 jam', 'Online'],
            ['Peminjaman Ruang / Aula', '2 hari kerja', 'Online'],
            ['Peminjaman Kendaraan Operasional', '3 hari kerja', 'Hybrid'],
            ['Pengadaan Barang & Jasa', 'Sesuai prosedur', 'Hybrid'],
            ['Penghapusan / Mutasi Aset', 'Sesuai periode', 'Offline'],
            ['Layanan Kebersihan & Keamanan Khusus', '1 hari kerja', 'Online'],
        ],
        'steps' => [
            ['Lapor / Ajukan', 'Sampaikan laporan kerusakan atau permohonan peminjaman melalui portal SI-ASET atau WhatsApp layanan.'],
            ['Verifikasi Lapangan', 'Petugas melakukan pengecekan kondisi aset/ruang dalam maksimal 1x24 jam setelah laporan.'],
            ['Penjadwalan Tindakan', 'Perbaikan atau persetujuan peminjaman dijadwalkan sesuai tingkat urgensi dan ketersediaan.'],
            ['Eksekusi & Serah Terima', 'Perbaikan dilaksanakan / aset diserahterimakan dengan berita acara digital.'],
            ['Evaluasi & Penutupan', 'Pelapor memberikan konfirmasi selesai; tiket ditutup dan tercatat dalam riwayat aset.'],
        ],
        'requirements' => [
            'Formulir laporan/permohonan dengan foto kondisi (untuk kerusakan)',
            'Identitas pelapor/pemohon (KTPeg atau KTM)',
            'Surat persetujuan unit kerja (untuk peminjaman kendaraan/aula)',
            'Jadwal dan keperluan penggunaan yang jelas',
            'Berita acara pengembalian (setelah peminjaman selesai)',
        ],
        'faq' => [
            ['Bagaimana cara melaporkan kerusakan fasilitas?', 'Gunakan portal SI-ASET menu "Laporan Kerusakan" atau kirim foto lokasi kerusakan via WhatsApp layanan. Tim maintenance merespon maksimal 1x24 jam pada hari kerja.'],
            ['Siapa yang boleh meminjam aula kampus?', 'Unit kerja internal, organisasi mahasiswa resmi, dan mitra eksternal dengan surat permohonan. Prioritas diberikan sesuai urutan tanggal pengajuan dan skala kegiatan.'],
            ['Bagaimana prosedur pengadaan barang?', 'Unit mengajukan spesifikasi dan RAB melalui sistem. Pembelian di bawah Rp 10 juta dapat langsung, sedangkan di atasnya mengikuti prosedur penawaran/tender sesuai Peraturan Rektor tentang Pengadaan Barang/Jasa.'],
        ],
        'contact' => ['aset@unimof.ac.id', '(0382) 21234 ext. 103', 'Kabag Aset & Rumah Tangga'],
    ],

    // =====================================================
    // K3L
    // =====================================================
    'k3l' => [
        'color'    => '#8e44ad',
        'gradient' => 'linear-gradient(135deg,#4a235a 0%,#8e44ad 50%,#9b59b6 100%)',
        'icon'     => 'bi-shield-fill-check',
        'title'    => 'Tim K3L',
        'subtitle' => 'Keselamatan, Kesehatan Kerja, dan Lingkungan — wujud kampus yang aman, sehat, dan nyaman.',
        'description' => 'Tim Keselamatan, Kesehatan Kerja, dan Lingkungan (K3L) bertanggung jawab mewujudkan lingkungan kampus yang aman, sehat, dan nyaman bagi seluruh sivitas akademika — melalui perumusan kebijakan K3, pengawasan prosedur keselamatan, manajemen mitigasi bencana, serta pemeliharaan lingkungan kampus yang hijau dan berkelanjutan.',
        'stats' => [
            ['0', 'Kecelakaan Berat'],
            ['4x/Tahun', 'Simulasi Evakuasi'],
            ['120+', 'Titik APAR Aktif'],
            ['Green', 'Kategori Kampus'],
        ],
        'scope' => [
            ['bi-clipboard2-pulse-fill', 'Kebijakan & SOP K3', 'Perumusan standar keselamatan kerja laboratorium, bengkel, kantor, dan area publik kampus.'],
            ['bi-fire', 'Proteksi Kebakaran', 'Penyediaan dan pemeriksaan rutin APAR, hidran, jalur evakuasi, serta titik kumpul aman.'],
            ['bi-exclamation-triangle-fill', 'Mitigasi Bencana', 'Pemetaan risiko, pelatihan tanggap darurat, dan koordinasi dengan BPBD serta pemadam kebakaran.'],
            ['bi-tree-fill', 'Lingkungan Hijau', 'Program pengelolaan sampah terpilah, konservasi air dan energi, serta penghijauan kampus.'],
        ],
        'services' => [
            ['Laporan Kecelakaan / Insiden Kerja', 'Respon segera', 'Online'],
            ['Inspeksi Keselamatan Area Kerja', 'By request', 'Offline'],
            ['Pelatihan & Simulasi Evakuasi', 'Per semester', 'Offline'],
            ['Rekomendasi Izin Kegiatan Berisiko', '3 hari kerja', 'Online'],
            ['Audit Lingkungan & Sampah', 'Per kuartal', 'Offline'],
            ['Konsultasi Kesehatan Kerja', 'By appointment', 'Hybrid'],
        ],
        'steps' => [
            ['Identifikasi / Laporan', 'Sivitas melaporkan kondisi tidak aman, insiden, atau permintaan inspeksi melalui kanal K3L.'],
            ['Asesmen Risiko', 'Tim K3L melakukan penilaian tingkat risiko dan dampak terhadap keselamatan & lingkungan.'],
            ['Rekomendasi & Tindakan', 'Diterbitkan rekomendasi perbaikan, penghentian sementara, atau izin kegiatan berisiko.'],
            ['Monitoring & Evaluasi', 'Tindak lanjut dipantau hingga tuntas dan dievaluasi dalam rapat K3L bulanan.'],
        ],
        'requirements' => [
            'Formulir laporan insiden/permohonan inspeksi',
            'Dokumentasi foto/video kondisi (jika ada)',
            'Identitas pelapor dan lokasi kejadian yang jelas',
            'Proposal kegiatan (untuk permohonan izin kegiatan berisiko)',
        ],
        'faq' => [
            ['Apa yang harus dilakukan saat terjadi keadaan darurat?', 'Segera hubungi nomor darurat internal K3L, ikuti jalur evakuasi menuju titik kumpul terdekat, dan jangan menggunakan lift. Peta evakuasi terpasang di setiap lantai gedung.'],
            ['Kapan simulasi evakuasi dilaksanakan?', 'Minimal 2 kali per semester untuk gedung utama dan laboratorium. Jadwal diumumkan melalui pengumuman resmi dan koordinasi dengan pimpinan unit.'],
            ['Bagaimana prosedur pembuangan limbah laboratorium?', 'Limbah B3 wajib dipilah sesuai kategorinya, disimpan dalam wadah berlabel, dan dilaporkan ke Tim K3L untuk dijemput oleh pihak ketiga berizin secara berkala.'],
        ],
        'contact' => ['k3l@unimof.ac.id', '(0382) 21234 ext. 119', 'Koordinator Tim K3L'],
    ],
];

// Fallback aman jika section tidak valid
if (!isset($services[$section])) {
    $section = 'kepegawaian';
}
$data = $services[$section];

// Layanan lain untuk cross-link
$others = array_filter(array_keys($services), fn($k) => $k !== $section);
?>

<!-- ===== ULTIMATE STYLES ===== -->
<style>
/* ===== SERVICE HERO ===== */
.ult-svc-hero{position:relative;color:#fff;padding:4.5rem 0 6rem;overflow:hidden;background:<?= $data['gradient'] ?>;}
.ult-svc-hero::before{content:'';position:absolute;inset:0;background:url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23ffffff' fill-opacity='0.04'%3E%3Ccircle cx='30' cy='30' r='2'/%3E%3C/g%3E%3C/svg%3E");z-index:1;}
.ult-svc-hero-watermark{position:absolute;right:-60px;bottom:-80px;font-size:22rem;opacity:.07;z-index:1;transform:rotate(-12deg);pointer-events:none;}
.ult-svc-hero-content{position:relative;z-index:2;}
.ult-svc-breadcrumb{display:flex;align-items:center;gap:.5rem;font-size:.85rem;color:rgba(255,255,255,.8);margin-bottom:1.5rem;flex-wrap:wrap;}
.ult-svc-breadcrumb a{color:#fff;transition:color .2s;}
.ult-svc-breadcrumb a:hover{color:#f39c12;}
.ult-svc-breadcrumb i{font-size:.7rem;opacity:.6;}
.ult-svc-hero-grid{display:grid;grid-template-columns:auto 1fr;gap:2rem;align-items:center;}
.ult-svc-hero-icon{width:110px;height:110px;background:rgba(255,255,255,.14);backdrop-filter:blur(10px);border:1px solid rgba(255,255,255,.25);border-radius:28px;display:flex;align-items:center;justify-content:center;font-size:3.25rem;box-shadow:0 15px 40px rgba(0,0,0,.2);}
.ult-svc-hero-badge{display:inline-flex;align-items:center;gap:.5rem;background:rgba(255,255,255,.15);padding:.375rem 1rem;border-radius:50px;font-size:.8rem;font-weight:600;margin-bottom:.875rem;letter-spacing:.5px;text-transform:uppercase;}
.ult-svc-hero h1{font-family:'Playfair Display',serif;font-size:clamp(2rem,4.5vw,3rem);font-weight:700;margin-bottom:.75rem;line-height:1.2;}
.ult-svc-hero p{font-size:1.05rem;max-width:720px;line-height:1.8;color:rgba(255,255,255,.92);}
.ult-svc-hero-wave{position:absolute;bottom:-1px;left:0;right:0;z-index:3;}
.ult-svc-hero-wave svg{display:block;width:100%;}

/* ===== STATS STRIP ===== */
.ult-svc-stats{margin-top:-3.25rem;position:relative;z-index:5;}
.ult-svc-stats-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:1.25rem;}
.ult-svc-stat{background:#fff;border-radius:16px;padding:1.5rem;text-align:center;box-shadow:0 10px 35px rgba(0,0,0,.1);border-bottom:4px solid <?= $data['color'] ?>;transition:transform .3s;}
.ult-svc-stat:hover{transform:translateY(-5px);}
.ult-svc-stat-value{font-family:'Playfair Display',serif;font-size:2rem;font-weight:700;color:<?= $data['color'] ?>;line-height:1;}
.ult-svc-stat-label{font-size:.8rem;color:var(--color-text-secondary);margin-top:.375rem;text-transform:uppercase;letter-spacing:.5px;}

/* ===== LAYOUT DENGAN SIDEBAR ===== */
.ult-svc-layout{display:grid;grid-template-columns:280px 1fr;gap:2.5rem;margin-top:3rem;align-items:start;}
.ult-svc-sidebar{position:sticky;top:100px;display:flex;flex-direction:column;gap:1.25rem;}
.ult-svc-sidenav{background:#fff;border-radius:16px;padding:1.25rem;box-shadow:0 4px 20px rgba(0,0,0,.06);border:1px solid var(--color-border-light);}
.ult-svc-sidenav h4{font-size:.8rem;text-transform:uppercase;letter-spacing:1px;color:var(--color-text-muted);margin-bottom:.875rem;padding-bottom:.625rem;border-bottom:2px solid var(--color-border-light);}
.ult-svc-sidenav a{display:flex;align-items:center;gap:.625rem;padding:.625rem .75rem;border-radius:8px;color:var(--color-text-secondary);font-size:.875rem;font-weight:500;transition:all .2s;border-left:3px solid transparent;}
.ult-svc-sidenav a:hover{background:var(--color-bg);color:<?= $data['color'] ?>;}
.ult-svc-sidenav a.active{background:<?= $data['color'] ?>14;color:<?= $data['color'] ?>;border-left-color:<?= $data['color'] ?>;font-weight:700;}
.ult-svc-sidenav a i{width:16px;text-align:center;}
.ult-svc-contact-card{background:<?= $data['color'] ?>;color:#fff;border-radius:16px;padding:1.5rem;box-shadow:0 10px 30px <?= $data['color'] ?>55;}
.ult-svc-contact-card h4{font-size:1rem;margin-bottom:1rem;display:flex;align-items:center;gap:.5rem;}
.ult-svc-contact-row{display:flex;align-items:center;gap:.625rem;font-size:.85rem;margin-bottom:.625rem;color:rgba(255,255,255,.95);}
.ult-svc-contact-row i{width:16px;}
.ult-svc-contact-row a{color:#fff;text-decoration:underline;text-underline-offset:3px;}
.ult-svc-contact-card .btn{width:100%;justify-content:center;margin-top:.75rem;background:#fff;color:<?= $data['color'] ?>!important;font-weight:700;}
.ult-svc-contact-card .btn:hover{transform:translateY(-2px);box-shadow:0 8px 20px rgba(0,0,0,.2);}

/* ===== SECTION BLOCKS ===== */
.ult-svc-block{background:#fff;border-radius:20px;padding:2.25rem;box-shadow:0 6px 25px rgba(0,0,0,.06);border:1px solid var(--color-border-light);margin-bottom:2rem;scroll-margin-top:110px;}
.ult-svc-block-header{display:flex;align-items:center;gap:.875rem;margin-bottom:1.5rem;padding-bottom:1rem;border-bottom:2px solid var(--color-border-light);}
.ult-svc-block-icon{width:48px;height:48px;border-radius:12px;background:<?= $data['color'] ?>18;color:<?= $data['color'] ?>;display:flex;align-items:center;justify-content:center;font-size:1.35rem;flex-shrink:0;}
.ult-svc-block-header h2{font-family:'Playfair Display',serif;font-size:1.4rem;font-weight:700;color:var(--color-text);}
.ult-svc-block-header small{display:block;font-size:.8rem;color:var(--color-text-muted);font-weight:400;font-family:'Inter',sans-serif;}
.ult-svc-lead{font-size:1.05rem;line-height:1.9;color:var(--color-text-secondary);margin-bottom:1.75rem;}

/* Scope grid */
.ult-svc-scope-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:1.25rem;}
.ult-svc-scope-card{background:var(--color-bg);border-radius:14px;padding:1.5rem;border:1px solid var(--color-border-light);transition:all .3s;position:relative;overflow:hidden;}
.ult-svc-scope-card::before{content:'';position:absolute;top:0;left:0;width:100%;height:3px;background:<?= $data['color'] ?>;transform:scaleX(0);transform-origin:left;transition:transform .3s;}
.ult-svc-scope-card:hover{background:#fff;box-shadow:0 10px 30px rgba(0,0,0,.08);transform:translateY(-4px);}
.ult-svc-scope-card:hover::before{transform:scaleX(1);}
.ult-svc-scope-icon{width:50px;height:50px;border-radius:12px;background:<?= $data['color'] ?>;color:#fff;display:flex;align-items:center;justify-content:center;font-size:1.35rem;margin-bottom:1rem;box-shadow:0 6px 15px <?= $data['color'] ?>44;}
.ult-svc-scope-card h4{font-size:1rem;font-weight:700;margin-bottom:.5rem;color:var(--color-text);}
.ult-svc-scope-card p{font-size:.85rem;color:var(--color-text-secondary);line-height:1.65;margin:0;}

/* Service list table */
.ult-svc-table{width:100%;border-collapse:collapse;}
.ult-svc-table thead th{background:var(--color-bg);padding:.875rem 1rem;text-align:left;font-size:.75rem;text-transform:uppercase;letter-spacing:.75px;color:var(--color-text-secondary);border-bottom:2px solid var(--color-border);}
.ult-svc-table thead th:first-child{border-radius:10px 0 0 10px;}
.ult-svc-table thead th:last-child{border-radius:0 10px 10px 0;}
.ult-svc-table tbody td{padding:1rem;border-bottom:1px solid var(--color-border-light);font-size:.9rem;color:var(--color-text);}
.ult-svc-table tbody tr{transition:background .2s;}
.ult-svc-table tbody tr:hover{background:<?= $data['color'] ?>08;}
.ult-svc-table tbody tr:last-child td{border-bottom:none;}
.ult-svc-svc-name{display:flex;align-items:center;gap:.625rem;font-weight:600;}
.ult-svc-svc-name i{color:<?= $data['color'] ?>;}
.ult-svc-sla{display:inline-flex;align-items:center;gap:.375rem;background:<?= $data['color'] ?>14;color:<?= $data['color'] ?>;padding:.25rem .75rem;border-radius:50px;font-size:.75rem;font-weight:700;white-space:nowrap;}
.ult-svc-channel{display:inline-flex;align-items:center;gap:.375rem;font-size:.75rem;font-weight:600;padding:.25rem .75rem;border-radius:50px;}
.ult-svc-channel.online{background:#d1fae5;color:#065f46;}
.ult-svc-channel.hybrid{background:#fef3c7;color:#92400e;}
.ult-svc-channel.offline{background:#e2e8f0;color:#475569;}

/* Steps */
.ult-svc-steps{position:relative;padding-left:2.5rem;}
.ult-svc-steps::before{content:'';position:absolute;left:19px;top:10px;bottom:10px;width:2px;background:linear-gradient(180deg,<?= $data['color'] ?>,<?= $data['color'] ?>33);}
.ult-svc-step{position:relative;padding-bottom:1.75rem;}
.ult-svc-step:last-child{padding-bottom:0;}
.ult-svc-step-number{position:absolute;left:-2.5rem;top:0;width:40px;height:40px;border-radius:50%;background:<?= $data['color'] ?>;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:.95rem;box-shadow:0 5px 15px <?= $data['color'] ?>55;border:3px solid #fff;}
.ult-svc-step h4{font-size:1.05rem;font-weight:700;margin-bottom:.375rem;color:var(--color-text);padding-top:.5rem;}
.ult-svc-step p{font-size:.9rem;color:var(--color-text-secondary);line-height:1.7;margin:0;}

/* Requirements */
.ult-svc-req-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:.875rem;}
.ult-svc-req-item{display:flex;align-items:flex-start;gap:.75rem;background:var(--color-bg);padding:1rem 1.125rem;border-radius:12px;border:1px solid var(--color-border-light);font-size:.9rem;color:var(--color-text);line-height:1.55;transition:all .2s;}
.ult-svc-req-item:hover{border-color:<?= $data['color'] ?>;background:#fff;}
.ult-svc-req-item i{color:<?= $data['color'] ?>;font-size:1.05rem;flex-shrink:0;margin-top:2px;}

/* FAQ */
.ult-svc-faq-item{border:1px solid var(--color-border-light);border-radius:12px;margin-bottom:.75rem;overflow:hidden;transition:all .3s;background:#fff;}
.ult-svc-faq-item.active{border-color:<?= $data['color'] ?>;box-shadow:0 6px 20px <?= $data['color'] ?>22;}
.ult-svc-faq-q{width:100%;padding:1.125rem 1.25rem;background:none;border:none;cursor:pointer;display:flex;justify-content:space-between;align-items:center;gap:1rem;text-align:left;font-weight:600;font-size:.95rem;color:var(--color-text);}
.ult-svc-faq-q i{color:<?= $data['color'] ?>;transition:transform .3s;flex-shrink:0;}
.ult-svc-faq-item.active .ult-svc-faq-q i{transform:rotate(180deg);}
.ult-svc-faq-a{max-height:0;overflow:hidden;transition:max-height .4s ease;padding:0 1.25rem;font-size:.9rem;color:var(--color-text-secondary);line-height:1.8;}
.ult-svc-faq-item.active .ult-svc-faq-a{max-height:300px;padding-bottom:1.25rem;}

/* CTA dalam block */
.ult-svc-cta-inline{background:<?= $data['gradient'] ?>;border-radius:16px;padding:2rem;color:#fff;display:flex;align-items:center;justify-content:space-between;gap:1.5rem;flex-wrap:wrap;position:relative;overflow:hidden;}
.ult-svc-cta-inline::before{content:'';position:absolute;right:-40px;top:-40px;width:180px;height:180px;background:radial-gradient(circle,rgba(255,255,255,.15),transparent 70%);border-radius:50%;}
.ult-svc-cta-inline h3{font-family:'Playfair Display',serif;font-size:1.35rem;margin-bottom:.375rem;position:relative;}
.ult-svc-cta-inline p{font-size:.9rem;color:rgba(255,255,255,.9);margin:0;position:relative;}
.ult-svc-cta-actions{display:flex;gap:.75rem;flex-wrap:wrap;position:relative;}
.ult-svc-cta-actions .btn-white{background:#fff;color:<?= $data['color'] ?>!important;}
.ult-svc-cta-actions .btn-ghost{background:transparent;border:2px solid rgba(255,255,255,.5);color:#fff!important;}
.ult-svc-cta-actions .btn-ghost:hover{background:rgba(255,255,255,.15);border-color:#fff;}

/* Other services */
.ult-svc-others{background:var(--color-bg);padding:4rem 0;}
.ult-svc-others-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:1.5rem;margin-top:2.5rem;}
.ult-svc-other-card{background:#fff;border-radius:16px;padding:1.75rem;text-align:center;box-shadow:0 4px 20px rgba(0,0,0,.05);border:1px solid var(--color-border-light);transition:all .3s;color:var(--color-text)!important;display:block;}
.ult-svc-other-card:hover{transform:translateY(-6px);box-shadow:0 15px 40px rgba(0,0,0,.1);}
.ult-svc-other-icon{width:64px;height:64px;border-radius:16px;display:flex;align-items:center;justify-content:center;font-size:1.6rem;color:#fff;margin:0 auto 1rem;}
.ult-svc-other-card h4{font-size:1.05rem;font-weight:700;margin-bottom:.375rem;}
.ult-svc-other-card p{font-size:.825rem;color:var(--color-text-secondary);line-height:1.6;margin-bottom:.875rem;}
.ult-svc-other-card span{font-size:.825rem;font-weight:700;color:<?= $data['color'] ?>;display:inline-flex;align-items:center;gap:.25rem;}

/* ===== RESPONSIVE ===== */
@media (max-width:992px){
    .ult-svc-layout{grid-template-columns:1fr;}
    .ult-svc-sidebar{position:static;flex-direction:row;flex-wrap:wrap;}
    .ult-svc-sidebar > *{flex:1;min-width:260px;}
    .ult-svc-hero-grid{grid-template-columns:1fr;text-align:center;}
    .ult-svc-hero-icon{margin:0 auto;}
    .ult-svc-hero-watermark{font-size:12rem;}
}
@media (max-width:768px){
    .ult-svc-block{padding:1.5rem;}
    .ult-svc-table thead{display:none;}
    .ult-svc-table tbody td{display:block;border:none;padding:.375rem 0;}
    .ult-svc-table tbody tr{display:block;border-bottom:1px solid var(--color-border-light);padding:.875rem 0;}
    .ult-svc-cta-inline{flex-direction:column;text-align:center;}
}
</style>

<!-- ===== SERVICE HERO ===== -->
<section class="ult-svc-hero">
    <i class="bi <?= $data['icon'] ?> ult-svc-hero-watermark"></i>
    <div class="container">
        <div class="ult-svc-hero-content">
            <nav class="ult-svc-breadcrumb">
                <a href="<?= url() ?>"><i class="bi bi-house-fill"></i> Beranda</a>
                <i class="bi bi-chevron-right"></i>
                <span>Layanan</span>
                <i class="bi bi-chevron-right"></i>
                <span><?= htmlspecialchars($data['title']) ?></span>
            </nav>
            
            <div class="ult-svc-hero-grid">
                <div class="ult-svc-hero-icon"><i class="bi <?= $data['icon'] ?>"></i></div>
                <div>
                    <span class="ult-svc-hero-badge"><i class="bi bi-building"></i> BAUK UNIMOF</span>
                    <h1><?= htmlspecialchars($data['title']) ?></h1>
                    <p><?= htmlspecialchars($data['subtitle']) ?></p>
                </div>
            </div>
        </div>
    </div>
    <div class="ult-svc-hero-wave">
        <svg viewBox="0 0 1440 80" fill="none" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
            <path d="M0 80L60 70C120 60 240 40 360 30C480 20 600 20 720 25C840 30 960 40 1080 45C1200 50 1320 50 1380 50L1440 50V80H1380C1320 80 1200 80 1080 80C960 80 840 80 720 80C600 80 480 80 360 80C240 80 120 80 60 80H0Z" fill="#f8f9fa"/>
        </svg>
    </div>
</section>

<!-- ===== STATS STRIP ===== -->
<section class="ult-svc-stats">
    <div class="container">
        <div class="ult-svc-stats-grid">
            <?php foreach ($data['stats'] as $stat): ?>
            <div class="ult-svc-stat">
                <div class="ult-svc-stat-value"><?= htmlspecialchars($stat[0]) ?></div>
                <div class="ult-svc-stat-label"><?= htmlspecialchars($stat[1]) ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ===== MAIN LAYOUT ===== -->
<section class="section" style="padding-top:2.5rem;">
    <div class="container">
        <div class="ult-svc-layout">
            
            <!-- ===== SIDEBAR ===== -->
            <aside class="ult-svc-sidebar">
                <nav class="ult-svc-sidenav" id="ultSvcNav">
                    <h4><i class="bi bi-list-ul"></i> Di Halaman Ini</h4>
                    <a href="#ult-overview" class="active"><i class="bi bi-info-circle-fill"></i> Ringkasan</a>
                    <a href="#ult-services"><i class="bi bi-clipboard-check-fill"></i> Daftar Layanan & SLA</a>
                    <a href="#ult-procedure"><i class="bi bi-signpost-split-fill"></i> Prosedur Layanan</a>
                    <a href="#ult-requirements"><i class="bi bi-card-checklist-fill"></i> Persyaratan</a>
                    <a href="#ult-faq"><i class="bi bi-question-circle-fill"></i> FAQ</a>
                </nav>
                
                <div class="ult-svc-contact-card">
                    <h4><i class="bi bi-headset"></i> Butuh Bantuan?</h4>
                    <div class="ult-svc-contact-row"><i class="bi bi-envelope-fill"></i> <a href="mailto:<?= $data['contact'][0] ?>"><?= $data['contact'][0] ?></a></div>
                    <div class="ult-svc-contact-row"><i class="bi bi-telephone-fill"></i> <?= $data['contact'][1] ?></div>
                    <div class="ult-svc-contact-row"><i class="bi bi-person-fill"></i> <?= $data['contact'][2] ?></div>
                    <a href="<?= url('kontak') ?>" class="btn"><i class="bi bi-chat-dots-fill"></i> Ajukan Layanan</a>
                </div>
            </aside>
            
            <!-- ===== CONTENT ===== -->
            <div class="ult-svc-content">
                
                <!-- Overview -->
                <div class="ult-svc-block" id="ult-overview">
                    <div class="ult-svc-block-header">
                        <div class="ult-svc-block-icon"><i class="bi bi-info-circle-fill"></i></div>
                        <div><h2>Ringkasan Layanan</h2><small>Tugas, fungsi, dan ruang lingkup</small></div>
                    </div>
                    <p class="ult-svc-lead"><?= htmlspecialchars($data['description']) ?></p>
                    <div class="ult-svc-scope-grid">
                        <?php foreach ($data['scope'] as $scope): ?>
                        <div class="ult-svc-scope-card">
                            <div class="ult-svc-scope-icon"><i class="bi <?= $scope[0] ?>"></i></div>
                            <h4><?= htmlspecialchars($scope[1]) ?></h4>
                            <p><?= htmlspecialchars($scope[2]) ?></p>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Service List & SLA -->
                <div class="ult-svc-block" id="ult-services">
                    <div class="ult-svc-block-header">
                        <div class="ult-svc-block-icon"><i class="bi bi-clipboard-check-fill"></i></div>
                        <div><h2>Daftar Layanan & SLA</h2><small>Standar waktu penyelesaian setiap layanan</small></div>
                    </div>
                    <div style="overflow-x:auto;">
                        <table class="ult-svc-table">
                            <thead>
                                <tr><th style="width:50%;">Nama Layanan</th><th>Standar Waktu (SLA)</th><th>Kanal</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($data['services'] as $svc): 
                                    $channel = strtolower($svc[2]);
                                ?>
                                <tr>
                                    <td><span class="ult-svc-svc-name"><i class="bi bi-check-circle-fill"></i> <?= htmlspecialchars($svc[0]) ?></span></td>
                                    <td><span class="ult-svc-sla"><i class="bi bi-clock-fill"></i> <?= htmlspecialchars($svc[1]) ?></span></td>
                                    <td><span class="ult-svc-channel <?= $channel ?>"><i class="bi bi-<?= $channel === 'online' ? 'wifi' : ($channel === 'hybrid' ? 'arrow-repeat' : 'building') ?>"></i> <?= htmlspecialchars($svc[2]) ?></span></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Procedure -->
                <div class="ult-svc-block" id="ult-procedure">
                    <div class="ult-svc-block-header">
                        <div class="ult-svc-block-icon"><i class="bi bi-signpost-split-fill"></i></div>
                        <div><h2>Prosedur Layanan</h2><small>Alur pengajuan dari awal hingga selesai</small></div>
                    </div>
                    <div class="ult-svc-steps">
                        <?php foreach ($data['steps'] as $i => $step): ?>
                        <div class="ult-svc-step">
                            <div class="ult-svc-step-number"><?= $i + 1 ?></div>
                            <h4><?= htmlspecialchars($step[0]) ?></h4>
                            <p><?= htmlspecialchars($step[1]) ?></p>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Requirements -->
                <div class="ult-svc-block" id="ult-requirements">
                    <div class="ult-svc-block-header">
                        <div class="ult-svc-block-icon"><i class="bi bi-card-checklist-fill"></i></div>
                        <div><h2>Persyaratan Dokumen</h2><small>Siapkan berkas berikut sebelum mengajukan</small></div>
                    </div>
                    <div class="ult-svc-req-grid">
                        <?php foreach ($data['requirements'] as $req): ?>
                        <div class="ult-svc-req-item">
                            <i class="bi bi-patch-check-fill"></i>
                            <span><?= htmlspecialchars($req) ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- FAQ -->
                <div class="ult-svc-block" id="ult-faq">
                    <div class="ult-svc-block-header">
                        <div class="ult-svc-block-icon"><i class="bi bi-question-circle-fill"></i></div>
                        <div><h2>Pertanyaan Umum</h2><small>Jawaban untuk pertanyaan yang sering diajukan</small></div>
                    </div>
                    <?php foreach ($data['faq'] as $faq): ?>
                    <div class="ult-svc-faq-item">
                        <button class="ult-svc-faq-q">
                            <span><?= htmlspecialchars($faq[0]) ?></span>
                            <i class="bi bi-chevron-down"></i>
                        </button>
                        <div class="ult-svc-faq-a"><?= htmlspecialchars($faq[1]) ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- CTA -->
                <div class="ult-svc-cta-inline">
                    <div>
                        <h3>Siap Mengajukan Layanan?</h3>
                        <p>Tim <?= htmlspecialchars($data['title']) ?> siap membantu proses Anda secara cepat dan transparan.</p>
                    </div>
                    <div class="ult-svc-cta-actions">
                        <a href="<?= url('kontak') ?>" class="btn btn-white"><i class="bi bi-send-fill"></i> Ajukan Sekarang</a>
                        <a href="mailto:<?= $data['contact'][0] ?>" class="btn btn-ghost"><i class="bi bi-envelope-fill"></i> Email</a>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
</section>

<!-- ===== OTHER SERVICES ===== -->
<section class="ult-svc-others">
    <div class="container">
        <div class="section-header text-center">
            <div>
                <span class="section-badge"><i class="bi bi-grid-3x3-gap-fill"></i> Layanan Lainnya</span>
                <h2 class="section-title">Jelajahi Layanan BAUK Lainnya</h2>
            </div>
        </div>
        <div class="ult-svc-others-grid">
            <?php foreach ($others as $key): 
                $o = $services[$key];
            ?>
            <a href="<?= url('layanan/' . $key) ?>" class="ult-svc-other-card">
                <div class="ult-svc-other-icon" style="background:<?= $o['gradient'] ?>;"><i class="bi <?= $o['icon'] ?>"></i></div>
                <h4><?= htmlspecialchars($o['title']) ?></h4>
                <p><?= htmlspecialchars(mb_substr($o['subtitle'], 0, 80)) ?>...</p>
                <span>Lihat Detail <i class="bi bi-arrow-right"></i></span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ===== SCRIPTS ===== -->
<script>
(function() {
    'use strict';
    
    // FAQ Accordion
    document.querySelectorAll('.ult-svc-faq-q').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const item = this.parentElement;
            const wasActive = item.classList.contains('active');
            document.querySelectorAll('.ult-svc-faq-item').forEach(function(el) { el.classList.remove('active'); });
            if (!wasActive) item.classList.add('active');
        });
    });
    
    // Smooth scroll + scrollspy sidebar
    const navLinks = document.querySelectorAll('#ultSvcNav a');
    navLinks.forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                window.scrollTo({ top: target.offsetTop - 100, behavior: 'smooth' });
            }
        });
    });
    
    window.addEventListener('scroll', function() {
        const pos = window.pageYOffset + 150;
        let current = navLinks[0];
        navLinks.forEach(function(link) {
            const section = document.querySelector(link.getAttribute('href'));
            if (section && section.offsetTop <= pos) current = link;
        });
        navLinks.forEach(function(link) { link.classList.remove('active'); });
        if (current) current.classList.add('active');
    });
})();
</script>