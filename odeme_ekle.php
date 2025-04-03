<?php
require_once 'config.php';

// Oturum kontrolü
if (!isset($_SESSION['kullanici_id'])) {
    header('Location: login.php');
    exit();
}

// Organizasyon ID kontrolü
if (!isset($_GET['organizasyon_id'])) {
    header('Location: index.php');
    exit();
}

$organizasyon_id = $_GET['organizasyon_id'];

try {
    // Organizasyon bilgilerini getir
    $stmt = $db->prepare("
        SELECT o.*, m.ad_soyad as musteri_adi
        FROM organizasyonlar o
        LEFT JOIN musteriler m ON o.musteri_id = m.id
        WHERE o.id = ?
    ");
    $stmt->execute([$organizasyon_id]);
    $organizasyon = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$organizasyon) {
        header('Location: index.php');
        exit();
    }

} catch (PDOException $e) {
    die("Veritabanı hatası: " . $e->getMessage());
}

// Form gönderildi mi kontrol et
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Ödemeyi ekle
        $stmt = $db->prepare("
            INSERT INTO odemeler (
                organizasyon_id, odeme_tarihi, tutar, odeme_turu, aciklama
            ) VALUES (?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $organizasyon_id,
            $_POST['odeme_tarihi'],
            $_POST['tutar'],
            $_POST['odeme_turu'],
            $_POST['aciklama']
        ]);

        // Organizasyonun kalan tutarını güncelle
        $stmt = $db->prepare("
            UPDATE organizasyonlar 
            SET kalan_tutar = kalan_tutar - ? 
            WHERE id = ?
        ");

        $stmt->execute([
            $_POST['tutar'],
            $organizasyon_id
        ]);

        $_SESSION['mesaj'] = "Ödeme başarıyla eklendi.";
        header('Location: organizasyon_detay.php?id=' . $organizasyon_id);
        exit();

    } catch (PDOException $e) {
        $hata = "Veritabanı hatası: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yeni Ödeme - Flow Organizasyon</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-cash me-2"></i>
                            Yeni Ödeme
                        </h5>
                        <a href="organizasyon_detay.php?id=<?php echo $organizasyon_id; ?>" class="btn btn-light btn-sm">
                            <i class="bi bi-arrow-left"></i> Geri Dön
                        </a>
                    </div>
                    <div class="card-body">
                        <?php if (isset($hata)): ?>
                            <div class="alert alert-danger"><?php echo $hata; ?></div>
                        <?php endif; ?>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0">Organizasyon Bilgileri</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label fw-bold">Organizasyon Kodu:</label>
                                                <p><?php echo htmlspecialchars($organizasyon['organizasyon_kodu']); ?></p>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label fw-bold">Müşteri:</label>
                                                <p><?php echo htmlspecialchars($organizasyon['musteri_adi']); ?></p>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label fw-bold">Kalan Tutar:</label>
                                                <p><?php echo number_format($organizasyon['kalan_tutar'], 2, ',', '.'); ?> ₺</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <form method="post" class="needs-validation" novalidate>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="odeme_tarihi" class="form-label">Ödeme Tarihi</label>
                                    <input type="date" class="form-control" id="odeme_tarihi" name="odeme_tarihi" 
                                           value="<?php echo date('Y-m-d'); ?>" required>
                                    <div class="invalid-feedback">
                                        Lütfen ödeme tarihini seçiniz.
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="tutar" class="form-label">Tutar</label>
                                    <div class="input-group">
                                        <input type="number" step="0.01" class="form-control" id="tutar" name="tutar" 
                                               max="<?php echo $organizasyon['kalan_tutar']; ?>" required>
                                        <span class="input-group-text">₺</span>
                                    </div>
                                    <div class="invalid-feedback">
                                        Lütfen tutarı giriniz.
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="odeme_turu" class="form-label">Ödeme Türü</label>
                                    <select class="form-select" id="odeme_turu" name="odeme_turu" required>
                                        <option value="">Ödeme Türü Seçin</option>
                                        <option value="nakit">Nakit</option>
                                        <option value="kredi_karti">Kredi Kartı</option>
                                        <option value="havale">Havale</option>
                                        <option value="eft">EFT</option>
                                    </select>
                                    <div class="invalid-feedback">
                                        Lütfen ödeme türünü seçiniz.
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12 mb-3">
                                    <label for="aciklama" class="form-label">Açıklama</label>
                                    <textarea class="form-control" id="aciklama" name="aciklama" rows="3"></textarea>
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

        // Tutar kontrolü
        document.getElementById('tutar').addEventListener('input', function() {
            var maxTutar = parseFloat(this.getAttribute('max'));
            var girilenTutar = parseFloat(this.value) || 0;
            
            if (girilenTutar > maxTutar) {
                this.setCustomValidity('Girilen tutar kalan tutardan büyük olamaz.');
            } else {
                this.setCustomValidity('');
            }
        });
    </script>
</body>
</html> 