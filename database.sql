-- Veritabanını oluştur
CREATE DATABASE IF NOT EXISTS flow_organizasyon CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE flow_organizasyon;

-- Kullanıcılar tablosu
CREATE TABLE IF NOT EXISTS kullanicilar (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kullanici_adi VARCHAR(50) NOT NULL UNIQUE,
    sifre VARCHAR(255) NOT NULL,
    ad_soyad VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    rol ENUM('admin', 'kullanici') NOT NULL DEFAULT 'kullanici',
    olusturma_tarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Müşteriler tablosu
CREATE TABLE IF NOT EXISTS musteriler (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ad_soyad VARCHAR(100) NOT NULL,
    telefon VARCHAR(20) NOT NULL,
    email VARCHAR(100),
    adres TEXT,
    notlar TEXT,
    olusturma_tarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Organizasyonlar tablosu
CREATE TABLE IF NOT EXISTS organizasyonlar (
    id INT AUTO_INCREMENT PRIMARY KEY,
    organizasyon_kodu VARCHAR(20) NOT NULL UNIQUE,
    musteri_id INT NOT NULL,
    baslik VARCHAR(255) NOT NULL,
    tarih DATE NOT NULL,
    baslangic_saati TIME NOT NULL,
    bitis_saati TIME NOT NULL,
    toplam_tutar DECIMAL(10,2) NOT NULL,
    kapora DECIMAL(10,2) NOT NULL DEFAULT 0,
    kalan_tutar DECIMAL(10,2) NOT NULL DEFAULT 0,
    ozel_istekler TEXT,
    durum ENUM('planlandi', 'devam_ediyor', 'tamamlandi', 'iptal') NOT NULL DEFAULT 'planlandi',
    olusturma_tarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (musteri_id) REFERENCES musteriler(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Ödemeler tablosu
CREATE TABLE IF NOT EXISTS odemeler (
    id INT AUTO_INCREMENT PRIMARY KEY,
    organizasyon_id INT NOT NULL,
    tutar DECIMAL(10,2) NOT NULL,
    odeme_tarihi DATE NOT NULL,
    odeme_turu ENUM('nakit', 'kredi_karti', 'havale', 'eft') NOT NULL,
    aciklama TEXT,
    olusturma_tarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (organizasyon_id) REFERENCES organizasyonlar(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sözleşmeler tablosu
CREATE TABLE IF NOT EXISTS sozlesmeler (
    id INT AUTO_INCREMENT PRIMARY KEY,
    organizasyon_id INT NOT NULL,
    musteri_id INT NOT NULL,
    sozlesme_no VARCHAR(50) NOT NULL UNIQUE,
    imza_tarihi DATE NOT NULL,
    baslangic_tarihi DATE NOT NULL,
    bitis_tarihi DATE NOT NULL,
    toplam_tutar DECIMAL(10,2) NOT NULL,
    odeme_plani TEXT,
    ozel_kosullar TEXT,
    durum ENUM('aktif', 'pasif', 'iptal') NOT NULL DEFAULT 'aktif',
    olusturma_tarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (organizasyon_id) REFERENCES organizasyonlar(id) ON DELETE CASCADE,
    FOREIGN KEY (musteri_id) REFERENCES musteriler(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Personel tablosu
CREATE TABLE IF NOT EXISTS personel (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ad_soyad VARCHAR(100) NOT NULL,
    telefon VARCHAR(20) NOT NULL,
    email VARCHAR(100),
    adres TEXT,
    pozisyon VARCHAR(100) NOT NULL,
    maas DECIMAL(10,2) NOT NULL,
    baslangic_tarihi DATE NOT NULL,
    durum ENUM('aktif', 'pasif') NOT NULL DEFAULT 'aktif',
    notlar TEXT,
    olusturma_tarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Personel görevleri tablosu
CREATE TABLE IF NOT EXISTS personel_gorevleri (
    id INT AUTO_INCREMENT PRIMARY KEY,
    personel_id INT NOT NULL,
    organizasyon_id INT NOT NULL,
    gorev VARCHAR(255) NOT NULL,
    baslangic_tarihi DATE NOT NULL,
    bitis_tarihi DATE NOT NULL,
    durum ENUM('beklemede', 'devam_ediyor', 'tamamlandi', 'iptal') NOT NULL DEFAULT 'beklemede',
    notlar TEXT,
    olusturma_tarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (personel_id) REFERENCES personel(id) ON DELETE CASCADE,
    FOREIGN KEY (organizasyon_id) REFERENCES organizasyonlar(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Organizasyon personel tablosu
CREATE TABLE IF NOT EXISTS organizasyon_personel (
    id INT AUTO_INCREMENT PRIMARY KEY,
    organizasyon_id INT NOT NULL,
    personel_id INT NOT NULL,
    gorev VARCHAR(255) NOT NULL,
    ucret DECIMAL(10,2) NOT NULL,
    notlar TEXT,
    olusturma_tarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (organizasyon_id) REFERENCES organizasyonlar(id) ON DELETE CASCADE,
    FOREIGN KEY (personel_id) REFERENCES personel(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Hatırlatıcılar tablosu
CREATE TABLE IF NOT EXISTS hatirlaticilar (
    id INT AUTO_INCREMENT PRIMARY KEY,
    baslik VARCHAR(255) NOT NULL,
    aciklama TEXT,
    tarih DATE NOT NULL,
    saat TIME NOT NULL,
    durum ENUM('beklemede', 'tamamlandi') NOT NULL DEFAULT 'beklemede',
    olusturma_tarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Varsayılan admin kullanıcısı oluştur (eğer yoksa)
INSERT IGNORE INTO kullanicilar (kullanici_adi, sifre, ad_soyad, email, rol) 
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'admin@example.com', 'admin'); 