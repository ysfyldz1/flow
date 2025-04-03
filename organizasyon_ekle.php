<?php
require_once 'config.php';

// Oturum kontrolü
if (!isset($_SESSION['kullanici_id'])) {
    header('Location: login.php');
    exit();
}

// Müşterileri getir
$stmt = $db->query("SELECT id, ad_soyad FROM musteriler ORDER BY ad_soyad");
$musteriler = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tarih = $_POST['tarih'];
    $baslangic_saati = $_POST['baslangic_saati'];
    $bitis_saati = $_POST['bitis_saati'];
    
    // Tarih ve saat çakışması kontrolü
    $stmt = $db->prepare("
        SELECT COUNT(*) as sayi 
        FROM organizasyonlar 
        WHERE tarih = :tarih 
        AND (
            (baslangic_saati <= :bitis_saati AND bitis_saati >= :baslangic_saati)
            OR
            (baslangic_saati >= :baslangic_saati AND baslangic_saati <= :bitis_saati)
        )
    ");
    
    $stmt->execute([
        ':tarih' => $tarih,
        ':baslangic_saati' => $baslangic_saati,
        ':bitis_saati' => $bitis_saati
    ]);
    
    $sonuc = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($sonuc['sayi'] > 0) {
        $hata = "Bu tarih ve saat aralığında başka bir organizasyon bulunmaktadır!";
    } else {
        try {
            // Son organizasyon kodunu al
            $stmt = $db->query("SELECT organizasyon_kodu FROM organizasyonlar ORDER BY id DESC LIMIT 1");
            $son_kod = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Yeni organizasyon kodu oluştur
            $yeni_kod = 'FLOW-0001';
            if ($son_kod) {
                $numara = intval(substr($son_kod['organizasyon_kodu'], 5)) + 1;
                $yeni_kod = 'FLOW-' . str_pad($numara, 4, '0', STR_PAD_LEFT);
            }

            // Organizasyonu ekle
            $stmt = $db->prepare("INSERT INTO organizasyonlar (
                organizasyon_kodu, musteri_id, baslik, tarih, baslangic_saati, 
                bitis_saati, toplam_tutar, kapora, kalan_tutar, ozel_istekler
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

            $stmt->execute([
                $yeni_kod,
                $_POST['musteri_id'],
                $_POST['baslik'],
                $_POST['tarih'],
                $_POST['baslangic_saati'],
                $_POST['bitis_saati'],
                $_POST['toplam_tutar'],
                $_POST['kapora'],
                $_POST['toplam_tutar'] - $_POST['kapora'],
                $_POST['ozel_istekler']
            ]);

            header('Location: organizasyonlar.php');
            exit();
        } catch (PDOException $e) {
            $hata = "Organizasyon eklenirken bir hata oluştu: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yeni Organizasyon - Flow Organizasyon</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <div class="card">
                    <div class="card-header">
                        <h3>Yeni Organizasyon Ekle</h3>
                    </div>
                    <div class="card-body">
                        <?php if (isset($hata)): ?>
                            <div class="alert alert-danger"><?php echo $hata; ?></div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="musteri_id" class="form-label">Müşteri</label>
                                <select class="form-select" id="musteri_id" name="musteri_id" required>
                                    <option value="">Müşteri Seçin</option>
                                    <?php foreach ($musteriler as $musteri): ?>
                                        <option value="<?php echo $musteri['id']; ?>">
                                            <?php echo htmlspecialchars($musteri['ad_soyad']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="baslik" class="form-label">Organizasyon Başlığı</label>
                                <input type="text" class="form-control" id="baslik" name="baslik" required>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="tarih" class="form-label">Tarih</label>
                                        <input type="date" class="form-control" id="tarih" name="tarih" required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="baslangic_saati" class="form-label">Başlangıç Saati</label>
                                        <input type="time" class="form-control" id="baslangic_saati" name="baslangic_saati" required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="bitis_saati" class="form-label">Bitiş Saati</label>
                                        <input type="time" class="form-control" id="bitis_saati" name="bitis_saati" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="toplam_tutar" class="form-label">Toplam Tutar</label>
                                        <input type="number" step="0.01" class="form-control" id="toplam_tutar" name="toplam_tutar" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="kapora" class="form-label">Kapora</label>
                                        <input type="number" step="0.01" class="form-control" id="kapora" name="kapora" value="0">
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="ozel_istekler" class="form-label">Özel İstekler</label>
                                <textarea class="form-control" id="ozel_istekler" name="ozel_istekler" rows="3"></textarea>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">Organizasyonu Kaydet</button>
                                <a href="organizasyonlar.php" class="btn btn-secondary">İptal</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 