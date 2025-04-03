<?php
require_once 'config.php';

try {
    // Admin kullanıcısını oluştur
    $sifre = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $db->prepare("INSERT INTO kullanicilar (kullanici_adi, sifre, ad_soyad, email, rol) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute(['admin', $sifre, 'Sistem Yöneticisi', 'admin@flow.com', 'admin']);
    
    echo "Admin kullanıcısı başarıyla oluşturuldu!<br>";
    echo "Kullanıcı adı: admin<br>";
    echo "Şifre: admin123";
} catch(PDOException $e) {
    echo "Hata: " . $e->getMessage();
}
?> 