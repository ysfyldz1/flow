-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Anamakine: 127.0.0.1
-- Üretim Zamanı: 03 Nis 2025, 13:51:18
-- Sunucu sürümü: 10.4.32-MariaDB
-- PHP Sürümü: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Veritabanı: `flow_organizasyon`
--

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `hatirlaticilar`
--

CREATE TABLE `hatirlaticilar` (
  `id` int(11) NOT NULL,
  `baslik` varchar(255) NOT NULL,
  `aciklama` text DEFAULT NULL,
  `tarih` date NOT NULL,
  `saat` time NOT NULL,
  `durum` enum('beklemede','tamamlandi') NOT NULL DEFAULT 'beklemede',
  `olusturma_tarihi` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `kullanicilar`
--

CREATE TABLE `kullanicilar` (
  `id` int(11) NOT NULL,
  `kullanici_adi` varchar(50) NOT NULL,
  `sifre` varchar(255) NOT NULL,
  `ad_soyad` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `rol` enum('admin','kullanici') DEFAULT 'kullanici',
  `olusturma_tarihi` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Tablo döküm verisi `kullanicilar`
--

INSERT INTO `kullanicilar` (`id`, `kullanici_adi`, `sifre`, `ad_soyad`, `email`, `rol`, `olusturma_tarihi`) VALUES
(3, 'admin', '$2y$10$OjQN1q51DZEH.6zDX/ZwxOkmwSR53wkheihamkDanqol.dux1dPea', 'Sistem Yöneticisi', 'admin@flow.com', 'admin', '2025-04-03 08:12:19');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `musteriler`
--

CREATE TABLE `musteriler` (
  `id` int(11) NOT NULL,
  `ad_soyad` varchar(100) NOT NULL,
  `telefon` varchar(20) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `tc_no` varchar(11) DEFAULT NULL,
  `adres` text DEFAULT NULL,
  `notlar` text DEFAULT NULL,
  `olusturma_tarihi` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tablo döküm verisi `musteriler`
--

INSERT INTO `musteriler` (`id`, `ad_soyad`, `telefon`, `email`, `tc_no`, `adres`, `notlar`, `olusturma_tarihi`) VALUES
(1, 'Yusuf Yıldız', '05338161314', 'admin@flow.com', NULL, 'Fatih\r\nFatih', '', '2025-04-03 08:35:55');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `odemeler`
--

CREATE TABLE `odemeler` (
  `id` int(11) NOT NULL,
  `organizasyon_id` int(11) NOT NULL,
  `tutar` decimal(10,2) NOT NULL,
  `odeme_tarihi` date NOT NULL,
  `odeme_turu` enum('nakit','kredi_karti','havale','eft') NOT NULL,
  `aciklama` text DEFAULT NULL,
  `olusturma_tarihi` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tablo döküm verisi `odemeler`
--

INSERT INTO `odemeler` (`id`, `organizasyon_id`, `tutar`, `odeme_tarihi`, `odeme_turu`, `aciklama`, `olusturma_tarihi`) VALUES
(2, 1, 200.00, '2025-04-03', 'nakit', '', '2025-04-03 08:43:12'),
(3, 3, 500.00, '2025-04-03', 'nakit', '', '2025-04-03 09:48:38');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `organizasyonlar`
--

CREATE TABLE `organizasyonlar` (
  `id` int(11) NOT NULL,
  `organizasyon_kodu` varchar(20) NOT NULL,
  `musteri_id` int(11) NOT NULL,
  `baslik` varchar(255) NOT NULL,
  `tarih` date NOT NULL,
  `baslangic_saati` time NOT NULL,
  `bitis_saati` time NOT NULL,
  `toplam_tutar` decimal(10,2) NOT NULL,
  `kapora` decimal(10,2) NOT NULL DEFAULT 0.00,
  `kalan_tutar` decimal(10,2) NOT NULL DEFAULT 0.00,
  `ozel_istekler` text DEFAULT NULL,
  `durum` enum('planlandi','devam_ediyor','tamamlandi','iptal') NOT NULL DEFAULT 'planlandi',
  `olusturma_tarihi` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tablo döküm verisi `organizasyonlar`
--

INSERT INTO `organizasyonlar` (`id`, `organizasyon_kodu`, `musteri_id`, `baslik`, `tarih`, `baslangic_saati`, `bitis_saati`, `toplam_tutar`, `kapora`, `kalan_tutar`, `ozel_istekler`, `durum`, `olusturma_tarihi`) VALUES
(1, 'FLOW-0001', 1, 'dogum gunu', '2025-04-04', '15:00:00', '18:00:00', 2000.00, 1000.00, 1000.00, 'demir adam\r\ntatlı olacak\r\nicecek olacak', 'tamamlandi', '2025-04-03 08:36:16'),
(3, 'FLOW-0002', 1, 'dogum gunu', '2025-04-04', '19:00:00', '20:00:00', 2000.00, 1000.00, 500.00, '', 'planlandi', '2025-04-03 09:44:35');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `organizasyon_personel`
--

CREATE TABLE `organizasyon_personel` (
  `id` int(11) NOT NULL,
  `organizasyon_id` int(11) NOT NULL,
  `personel_id` int(11) NOT NULL,
  `gorev` varchar(255) NOT NULL,
  `ucret` decimal(10,2) NOT NULL,
  `notlar` text DEFAULT NULL,
  `olusturma_tarihi` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `personel`
--

CREATE TABLE `personel` (
  `id` int(11) NOT NULL,
  `ad_soyad` varchar(100) NOT NULL,
  `telefon` varchar(20) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `adres` text DEFAULT NULL,
  `pozisyon` varchar(100) NOT NULL,
  `maas` decimal(10,2) NOT NULL,
  `baslangic_tarihi` date NOT NULL,
  `durum` enum('aktif','pasif') NOT NULL DEFAULT 'aktif',
  `notlar` text DEFAULT NULL,
  `olusturma_tarihi` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `personel_gorevleri`
--

CREATE TABLE `personel_gorevleri` (
  `id` int(11) NOT NULL,
  `personel_id` int(11) NOT NULL,
  `organizasyon_id` int(11) NOT NULL,
  `gorev` varchar(255) NOT NULL,
  `baslangic_tarihi` date NOT NULL,
  `bitis_tarihi` date NOT NULL,
  `durum` enum('beklemede','devam_ediyor','tamamlandi','iptal') NOT NULL DEFAULT 'beklemede',
  `notlar` text DEFAULT NULL,
  `olusturma_tarihi` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `sozlesmeler`
--

CREATE TABLE `sozlesmeler` (
  `id` int(11) NOT NULL,
  `organizasyon_id` int(11) NOT NULL,
  `musteri_id` int(11) NOT NULL,
  `sozlesme_no` varchar(50) NOT NULL,
  `imza_tarihi` date NOT NULL,
  `baslangic_tarihi` date NOT NULL,
  `bitis_tarihi` date NOT NULL,
  `toplam_tutar` decimal(10,2) NOT NULL,
  `odeme_plani` text DEFAULT NULL,
  `ozel_kosullar` text DEFAULT NULL,
  `durum` enum('aktif','pasif','iptal') NOT NULL DEFAULT 'aktif',
  `olusturma_tarihi` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dökümü yapılmış tablolar için indeksler
--

--
-- Tablo için indeksler `hatirlaticilar`
--
ALTER TABLE `hatirlaticilar`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `kullanicilar`
--
ALTER TABLE `kullanicilar`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kullanici_adi` (`kullanici_adi`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Tablo için indeksler `musteriler`
--
ALTER TABLE `musteriler`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `odemeler`
--
ALTER TABLE `odemeler`
  ADD PRIMARY KEY (`id`),
  ADD KEY `organizasyon_id` (`organizasyon_id`);

--
-- Tablo için indeksler `organizasyonlar`
--
ALTER TABLE `organizasyonlar`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `organizasyon_kodu` (`organizasyon_kodu`),
  ADD KEY `musteri_id` (`musteri_id`);

--
-- Tablo için indeksler `organizasyon_personel`
--
ALTER TABLE `organizasyon_personel`
  ADD PRIMARY KEY (`id`),
  ADD KEY `organizasyon_id` (`organizasyon_id`),
  ADD KEY `personel_id` (`personel_id`);

--
-- Tablo için indeksler `personel`
--
ALTER TABLE `personel`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `personel_gorevleri`
--
ALTER TABLE `personel_gorevleri`
  ADD PRIMARY KEY (`id`),
  ADD KEY `personel_id` (`personel_id`),
  ADD KEY `organizasyon_id` (`organizasyon_id`);

--
-- Tablo için indeksler `sozlesmeler`
--
ALTER TABLE `sozlesmeler`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sozlesme_no` (`sozlesme_no`),
  ADD KEY `organizasyon_id` (`organizasyon_id`),
  ADD KEY `musteri_id` (`musteri_id`);

--
-- Dökümü yapılmış tablolar için AUTO_INCREMENT değeri
--

--
-- Tablo için AUTO_INCREMENT değeri `hatirlaticilar`
--
ALTER TABLE `hatirlaticilar`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `kullanicilar`
--
ALTER TABLE `kullanicilar`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Tablo için AUTO_INCREMENT değeri `musteriler`
--
ALTER TABLE `musteriler`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Tablo için AUTO_INCREMENT değeri `odemeler`
--
ALTER TABLE `odemeler`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Tablo için AUTO_INCREMENT değeri `organizasyonlar`
--
ALTER TABLE `organizasyonlar`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Tablo için AUTO_INCREMENT değeri `organizasyon_personel`
--
ALTER TABLE `organizasyon_personel`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `personel`
--
ALTER TABLE `personel`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `personel_gorevleri`
--
ALTER TABLE `personel_gorevleri`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `sozlesmeler`
--
ALTER TABLE `sozlesmeler`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Dökümü yapılmış tablolar için kısıtlamalar
--

--
-- Tablo kısıtlamaları `odemeler`
--
ALTER TABLE `odemeler`
  ADD CONSTRAINT `odemeler_ibfk_1` FOREIGN KEY (`organizasyon_id`) REFERENCES `organizasyonlar` (`id`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `organizasyonlar`
--
ALTER TABLE `organizasyonlar`
  ADD CONSTRAINT `organizasyonlar_ibfk_1` FOREIGN KEY (`musteri_id`) REFERENCES `musteriler` (`id`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `organizasyon_personel`
--
ALTER TABLE `organizasyon_personel`
  ADD CONSTRAINT `organizasyon_personel_ibfk_1` FOREIGN KEY (`organizasyon_id`) REFERENCES `organizasyonlar` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `organizasyon_personel_ibfk_2` FOREIGN KEY (`personel_id`) REFERENCES `personel` (`id`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `personel_gorevleri`
--
ALTER TABLE `personel_gorevleri`
  ADD CONSTRAINT `personel_gorevleri_ibfk_1` FOREIGN KEY (`personel_id`) REFERENCES `personel` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `personel_gorevleri_ibfk_2` FOREIGN KEY (`organizasyon_id`) REFERENCES `organizasyonlar` (`id`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `sozlesmeler`
--
ALTER TABLE `sozlesmeler`
  ADD CONSTRAINT `sozlesmeler_ibfk_1` FOREIGN KEY (`organizasyon_id`) REFERENCES `organizasyonlar` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sozlesmeler_ibfk_2` FOREIGN KEY (`musteri_id`) REFERENCES `musteriler` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
