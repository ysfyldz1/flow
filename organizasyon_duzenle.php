<?php
require_once 'config.php';

// Oturum kontrolü
if (!isset($_SESSION['kullanici_id'])) {
    header('Location: login.php');
    exit();
}

// ID kontrolü
if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$id = $_GET['id'];

try {
    // Organizasyon bilgilerini getir
    $stmt = $db->prepare("
        SELECT o.*, m.ad_soyad as musteri_adi, m.telefon as musteri_telefon, m.email as musteri_email
        FROM organizasyonlar o
        LEFT JOIN musteriler m ON o.musteri_id = m.id
        WHERE o.id = ?
    ");
    $stmt->execute([$id]);
    $organizasyon = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$organizasyon) {
        header('Location: index.php');
        exit();
    }

    // Müşterileri getir
    $stmt = $db->query("SELECT id, ad_soyad FROM musteriler ORDER BY ad_soyad");
    $musteriler = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Veritabanı hatası: " . $e->getMessage());
}

// Form gönderildi mi kontrol et
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tarih = $_POST['tarih'];
    $baslangic_saati = $_POST['baslangic_saati'];
    $bitis_saati = $_POST['bitis_saati'];
    
    // Tarih ve saat çakışması kontrolü (kendi ID'si hariç)
    $stmt = $db->prepare("
        SELECT COUNT(*) as sayi 
        FROM organizasyonlar 
        WHERE tarih = :tarih 
        AND id != :id
        AND (
            (baslangic_saati <= :bitis_saati AND bitis_saati >= :baslangic_saati)
            OR
            (baslangic_saati >= :baslangic_saati AND baslangic_saati <= :bitis_saati)
        )
    ");
    
    $stmt->execute([
        ':tarih' => $tarih,
        ':baslangic_saati' => $baslangic_saati,
        ':bitis_saati' => $bitis_saati,
        ':id' => $id
    ]);
    
    $sonuc = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($sonuc['sayi'] > 0) {
        $hata = "Bu tarih ve saat aralığında başka bir organizasyon bulunmaktadır!";
    } else {
        try {
            $stmt = $db->prepare("
                UPDATE organizasyonlar SET
                    organizasyon_kodu = ?,
                    musteri_id = ?,
                    tarih = ?,
                    baslangic_saati = ?,
                    bitis_saati = ?,
                    toplam_tutar = ?,
                    kapora = ?,
                    kalan_tutar = ?,
                    durum = ?,
                    ozel_istekler = ?
                WHERE id = ?
            ");

            $stmt->execute([
                $_POST['organizasyon_kodu'],
                $_POST['musteri_id'],
                $_POST['tarih'],
                $_POST['baslangic_saati'],
                $_POST['bitis_saati'],
                $_POST['toplam_tutar'],
                $_POST['kapora'],
                $_POST['toplam_tutar'] - $_POST['kapora'],
                $_POST['durum'],
                $_POST['ozel_istekler'],
                $id
            ]);

            $_SESSION['mesaj'] = "Organizasyon başarıyla güncellendi.";
            header('Location: organizasyon_detay.php?id=' . $id);
            exit();

        } catch (PDOException $e) {
            $hata = "Veritabanı hatası: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Organizasyon Düzenle - Flow Organizasyon</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container py-4">
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-pencil-square me-2"></i>
                            Organizasyon Düzenle
                        </h5>
                        <a href="organizasyon_detay.php?id=<?php echo $id; ?>" class="btn btn-light btn-sm">
                            <i class="bi bi-arrow-left"></i> Geri Dön
                        </a>
                    </div>
                    <div class="card-body">
                        <?php if (isset($hata)): ?>
                            <div class="alert alert-danger"><?php echo $hata; ?></div>
                        <?php endif; ?>

                        <form method="post" class="needs-validation" novalidate>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="organizasyon_kodu" class="form-label">Organizasyon Kodu</label>
                                    <input type="text" class="form-control" id="organizasyon_kodu" name="organizasyon_kodu" 
                                           value="<?php echo htmlspecialchars($organizasyon['organizasyon_kodu']); ?>" required>
                                    <div class="invalid-feedback">
                                        Lütfen organizasyon kodunu giriniz.
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="musteri_id" class="form-label">Müşteri</label>
                                    <select class="form-select" id="musteri_id" name="musteri_id" required>
                                        <option value="">Müşteri Seçin</option>
                                        <?php foreach ($musteriler as $musteri): ?>
                                            <option value="<?php echo $musteri['id']; ?>" 
                                                    <?php echo $musteri['id'] == $organizasyon['musteri_id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($musteri['ad_soyad']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="invalid-feedback">
                                        Lütfen müşteri seçiniz.
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="tarih" class="form-label">Tarih</label>
                                    <input type="date" class="form-control" id="tarih" name="tarih" 
                                           value="<?php echo date('Y-m-d', strtotime($organizasyon['tarih'])); ?>" required>
                                    <div class="invalid-feedback">
                                        Lütfen tarih seçiniz.
                                    </div>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="baslangic_saati" class="form-label">Başlangıç Saati</label>
                                    <input type="time" class="form-control" id="baslangic_saati" name="baslangic_saati" 
                                           value="<?php echo date('H:i', strtotime($organizasyon['baslangic_saati'])); ?>" required>
                                    <div class="invalid-feedback">
                                        Lütfen başlangıç saatini giriniz.
                                    </div>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="bitis_saati" class="form-label">Bitiş Saati</label>
                                    <input type="time" class="form-control" id="bitis_saati" name="bitis_saati" 
                                           value="<?php echo date('H:i', strtotime($organizasyon['bitis_saati'])); ?>" required>
                                    <div class="invalid-feedback">
                                        Lütfen bitiş saatini giriniz.
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="toplam_tutar" class="form-label">Toplam Tutar</label>
                                    <div class="input-group">
                                        <input type="number" step="0.01" class="form-control" id="toplam_tutar" name="toplam_tutar" 
                                               value="<?php echo $organizasyon['toplam_tutar']; ?>" required>
                                        <span class="input-group-text">₺</span>
                                    </div>
                                    <div class="invalid-feedback">
                                        Lütfen toplam tutarı giriniz.
                                    </div>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="kapora" class="form-label">Kapora</label>
                                    <div class="input-group">
                                        <input type="number" step="0.01" class="form-control" id="kapora" name="kapora" 
                                               value="<?php echo $organizasyon['kapora']; ?>" required>
                                        <span class="input-group-text">₺</span>
                                    </div>
                                    <div class="invalid-feedback">
                                        Lütfen kapora tutarını giriniz.
                                    </div>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="kalan_tutar" class="form-label">Kalan Tutar</label>
                                    <div class="input-group">
                                        <input type="number" step="0.01" class="form-control" id="kalan_tutar" name="kalan_tutar" 
                                               value="<?php echo $organizasyon['kalan_tutar']; ?>" readonly>
                                        <span class="input-group-text">₺</span>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="durum" class="form-label">Durum</label>
                                    <select class="form-select" id="durum" name="durum" required>
                                        <option value="planlandi" <?php echo $organizasyon['durum'] == 'planlandi' ? 'selected' : ''; ?>>Planlandı</option>
                                        <option value="devam_ediyor" <?php echo $organizasyon['durum'] == 'devam_ediyor' ? 'selected' : ''; ?>>Devam Ediyor</option>
                                        <option value="tamamlandi" <?php echo $organizasyon['durum'] == 'tamamlandi' ? 'selected' : ''; ?>>Tamamlandı</option>
                                        <option value="iptal" <?php echo $organizasyon['durum'] == 'iptal' ? 'selected' : ''; ?>>İptal</option>
                                    </select>
                                    <div class="invalid-feedback">
                                        Lütfen durum seçiniz.
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12 mb-3">
                                    <label for="ozel_istekler" class="form-label">Özel İstekler</label>
                                    <textarea class="form-control" id="ozel_istekler" name="ozel_istekler" rows="3"><?php echo htmlspecialchars($organizasyon['ozel_istekler']); ?></textarea>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-save me-2"></i>Kaydet
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form doğrulama
        (function () {
            'use strict'
            var forms = document.querySelectorAll('.needs-validation')
            Array.prototype.slice.call(forms)
                .forEach(function (form) {
                    form.addEventListener('submit', function (event) {
                        if (!form.checkValidity()) {
                            event.preventDefault()
                            event.stopPropagation()
                        }
                        form.classList.add('was-validated')
                    }, false)
                })
        })()

        // Kalan tutarı otomatik hesapla
        document.getElementById('toplam_tutar').addEventListener('input', function() {
            var toplam = parseFloat(this.value) || 0;
            var kapora = parseFloat(document.getElementById('kapora').value) || 0;
            document.getElementById('kalan_tutar').value = (toplam - kapora).toFixed(2);
        });

        document.getElementById('kapora').addEventListener('input', function() {
            var toplam = parseFloat(document.getElementById('toplam_tutar').value) || 0;
            var kapora = parseFloat(this.value) || 0;
            document.getElementById('kalan_tutar').value = (toplam - kapora).toFixed(2);
        });
    </script>
</body>
</html> 