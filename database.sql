-- =====================================================
-- Jadwal UMK — Database Schema
-- Jalankan sekali untuk setup database
-- =====================================================

-- Tabel settings (menyimpan API key Gemini, dll)
CREATE TABLE IF NOT EXISTS settings (
    `key`   VARCHAR(100) PRIMARY KEY,
    `value` TEXT NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel info mahasiswa
CREATE TABLE IF NOT EXISTS mahasiswa (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    nama       VARCHAR(255) NOT NULL,
    nim        VARCHAR(20)  NOT NULL,
    prodi      VARCHAR(255) NOT NULL,
    dosen_pa   VARCHAR(255) NOT NULL,
    sks        INT NOT NULL DEFAULT 0,
    semester   VARCHAR(100) NOT NULL,
    dicetak    VARCHAR(100),
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel matakuliah
CREATE TABLE IF NOT EXISTS matakuliah (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    mahasiswa_id INT NOT NULL,
    no_urut      INT NOT NULL,
    kelas        VARCHAR(5)   NOT NULL,
    kode         VARCHAR(20)  NOT NULL,
    nama         VARCHAR(255) NOT NULL,
    is_praktikum TINYINT(1)   NOT NULL DEFAULT 0,
    dosen        VARCHAR(255) NOT NULL,
    sks          INT          NOT NULL DEFAULT 2,
    -- Jadwal per hari: null = tidak ada, JSON object = ada jadwal
    jadwal_sn    JSON,
    jadwal_sl    JSON,
    jadwal_rb    JSON,
    jadwal_km    JSON,
    jadwal_jm    JSON,
    jadwal_sb    JSON,
    jadwal_mg    JSON,
    FOREIGN KEY (mahasiswa_id) REFERENCES mahasiswa(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
