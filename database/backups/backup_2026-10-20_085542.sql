-- BAUK UNIMOF Database Backup
-- Generated: 2026-10-20 08:55:42


-- Table: activity_logs
INSERT INTO `activity_logs` (`id`,`user_id`,`action`,`table_name`,`record_id`,`old_values`,`new_values`,`ip_address`,`user_agent`,`description`,`created_at`) VALUES ('1','1','login',NULL,NULL,NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36','Berhasil masuk ke sistem','2026-10-19 21:57:19');
INSERT INTO `activity_logs` (`id`,`user_id`,`action`,`table_name`,`record_id`,`old_values`,`new_values`,`ip_address`,`user_agent`,`description`,`created_at`) VALUES ('2','1','logout',NULL,NULL,NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36','Keluar dari sistem','2026-10-19 22:19:41');
INSERT INTO `activity_logs` (`id`,`user_id`,`action`,`table_name`,`record_id`,`old_values`,`new_values`,`ip_address`,`user_agent`,`description`,`created_at`) VALUES ('3','1','login',NULL,NULL,NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36','Berhasil masuk ke sistem','2026-10-20 04:06:19');

-- Table: anggaran

-- Table: aset

-- Table: berita

-- Table: categories_aset

-- Table: categories_keuangan

-- Table: contact_messages

-- Table: departments
INSERT INTO `departments` (`id`,`uuid`,`code`,`name`,`description`,`head_name`,`parent_id`,`status`,`sort_order`,`created_at`,`updated_at`,`deleted_at`) VALUES ('1','3a041d6b-cbc6-11f1-96fd-9829a6478450','KEP','Bagian Kepegawaian','Pengelolaan sumber daya manusia dan administrasi kepegawaian',NULL,NULL,'active','1','2026-10-19 21:06:06','2026-10-19 21:06:06',NULL);
INSERT INTO `departments` (`id`,`uuid`,`code`,`name`,`description`,`head_name`,`parent_id`,`status`,`sort_order`,`created_at`,`updated_at`,`deleted_at`) VALUES ('2','3a0426de-cbc6-11f1-96fd-9829a6478450','KEU','Bagian Keuangan','Pengelolaan anggaran, kas, dan pelaporan keuangan',NULL,NULL,'active','2','2026-10-19 21:06:06','2026-10-19 21:06:06',NULL);
INSERT INTO `departments` (`id`,`uuid`,`code`,`name`,`description`,`head_name`,`parent_id`,`status`,`sort_order`,`created_at`,`updated_at`,`deleted_at`) VALUES ('3','3a042871-cbc6-11f1-96fd-9829a6478450','ASET','Bagian Aset & Rumah Tangga','Pengelolaan aset, sarana, dan prasarana kampus',NULL,NULL,'active','3','2026-10-19 21:06:06','2026-10-19 21:06:06',NULL);
INSERT INTO `departments` (`id`,`uuid`,`code`,`name`,`description`,`head_name`,`parent_id`,`status`,`sort_order`,`created_at`,`updated_at`,`deleted_at`) VALUES ('4','3a0429f2-cbc6-11f1-96fd-9829a6478450','K3L','Tim K3L','Keselamatan, Kesehatan Kerja, dan Lingkungan',NULL,NULL,'active','4','2026-10-19 21:06:06','2026-10-19 21:06:06',NULL);

-- Table: dokumen_regulasi

-- Table: kegiatan

-- Table: maintenance_aset

-- Table: pegawai

-- Table: pengumuman

-- Table: settings
INSERT INTO `settings` (`id`,`key`,`value`,`type`,`group`,`description`,`updated_at`) VALUES ('1','site_name','BAUK UNIMOF','text','general','Nama Website','2026-10-19 21:06:06');
INSERT INTO `settings` (`id`,`key`,`value`,`type`,`group`,`description`,`updated_at`) VALUES ('2','site_description','Biro Administrasi Umum dan Keuangan - Universitas Muhammadiyah Maumere','text','general','Deskripsi Website','2026-10-19 21:06:06');
INSERT INTO `settings` (`id`,`key`,`value`,`type`,`group`,`description`,`updated_at`) VALUES ('3','site_logo','/assets/images/logo-unimof.png','file','general','Logo Website','2026-10-19 21:06:06');
INSERT INTO `settings` (`id`,`key`,`value`,`type`,`group`,`description`,`updated_at`) VALUES ('4','address','Jl. Ahmad Yani No. 17, Maumere, Sikka, NTT','text','contact','Alamat Kantor','2026-10-19 21:06:06');
INSERT INTO `settings` (`id`,`key`,`value`,`type`,`group`,`description`,`updated_at`) VALUES ('5','phone','(0382) 21234','text','contact','Nomor Telepon','2026-10-19 21:06:06');
INSERT INTO `settings` (`id`,`key`,`value`,`type`,`group`,`description`,`updated_at`) VALUES ('6','email','bauk@unimof.ac.id','text','contact','Email Resmi','2026-10-19 21:06:06');
INSERT INTO `settings` (`id`,`key`,`value`,`type`,`group`,`description`,`updated_at`) VALUES ('7','max_login_attempts','5','number','security','Maksimum percobaan login','2026-10-19 21:06:06');
INSERT INTO `settings` (`id`,`key`,`value`,`type`,`group`,`description`,`updated_at`) VALUES ('8','lockout_duration','15','number','security','Durasi lockout (menit)','2026-10-19 21:06:06');
INSERT INTO `settings` (`id`,`key`,`value`,`type`,`group`,`description`,`updated_at`) VALUES ('9','session_lifetime','120','number','security','Lifetime session (menit)','2026-10-19 21:06:06');
INSERT INTO `settings` (`id`,`key`,`value`,`type`,`group`,`description`,`updated_at`) VALUES ('10','csrf_protection','1','boolean','security','Aktifkan CSRF Protection','2026-10-19 21:06:06');
INSERT INTO `settings` (`id`,`key`,`value`,`type`,`group`,`description`,`updated_at`) VALUES ('11','password_min_length','8','number','security','Minimum panjang password','2026-10-19 21:06:06');
INSERT INTO `settings` (`id`,`key`,`value`,`type`,`group`,`description`,`updated_at`) VALUES ('12','require_special_char','1','boolean','security','Wajib karakter spesial','2026-10-19 21:06:06');
INSERT INTO `settings` (`id`,`key`,`value`,`type`,`group`,`description`,`updated_at`) VALUES ('13','require_uppercase','1','boolean','security','Wajib huruf kapital','2026-10-19 21:06:06');
INSERT INTO `settings` (`id`,`key`,`value`,`type`,`group`,`description`,`updated_at`) VALUES ('14','require_number','1','boolean','security','Wajib angka','2026-10-19 21:06:06');

-- Table: transaksi_keuangan

-- Table: users
INSERT INTO `users` (`id`,`uuid`,`username`,`email`,`password`,`full_name`,`phone`,`avatar`,`role`,`status`,`email_verified_at`,`last_login_at`,`last_login_ip`,`two_factor_secret`,`two_factor_enabled`,`password_changed_at`,`login_attempts`,`locked_until`,`remember_token`,`created_by`,`updated_by`,`created_at`,`updated_at`,`deleted_at`) VALUES ('1','3a01da7d-cbc6-11f1-96fd-9829a6478450','superadmin','admin@unimof.ac.id','$argon2id$v=19$m=65536,t=4,p=3$enBGei9JcDRTMnMxdmIyMQ$VKMCdhNxY6NVQ+pz7OcKwCGg8cpd0sQDNHiS+rnOddI','Super Administrator BAUK',NULL,NULL,'super_admin','active','2026-10-19 21:06:06','2026-10-20 05:06:19','::1',NULL,'0','2026-10-19 14:54:20','0',NULL,NULL,NULL,NULL,'2026-10-19 21:06:06','2026-10-20 04:06:19',NULL);
