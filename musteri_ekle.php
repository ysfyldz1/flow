<?php
require_once 'config.php';

// Oturum kontrolü
if (!isset($_SESSION['kullanici_id'])) {
    header('Location: login.php');
    exit();
}

// Form gönderildi mi kontrol et
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $stmt = $db->prepare("
            INSERT INTO musteriler (
                ad_soyad, telefon, email, tc_no, adres, notlar, olusturma_tarihi, guncelleme_tarihi
            ) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
        ");

        $stmt->execute([
            $_POST['ad_soyad'],
            $_POST['telefon'],
            $_POST['email'],
            $_POST['tc_no'],
            $_POST['adres'],
            $_POST['notlar']
        ]);

        $_SESSION['mesaj'] = "Müşteri başarıyla eklendi.";
        header('Location: musteriler.php');
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
    <title>Yeni Müşteri - Flow Organizasyon</title>
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
                            <i class="bi bi-person-plus me-2"></i>
                            Yeni Müşteri
                        </h5>
                        <a href="musteriler.php" class="btn btn-light btn-sm">
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
                                    <label for="ad_soyad" class="form-label">Ad Soyad</label>
                                    <input type="text" class="form-control" id="ad_soyad" name="ad_soyad" required>
                                    <div class="invalid-feedback">
                                        Lütfen ad soyad giriniz.
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="telefon" class="form-label">Telefon</label>
                                    <input type="tel" class="form-control" id="telefon" name="telefon" required>
                                    <div class="invalid-feedback">
                                        Lütfen telefon numarası giriniz.
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">E-posta</label>
                                    <input type="email" class="form-control" id="email" name="email">
                                    <div class="invalid-feedback">
                                        Lütfen geçerli bir e-posta adresi giriniz.
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="tc_no" class="form-label">T.C. No</label>
                                    <input type="text" class="form-control" id="tc_no" name="tc_no" maxlength="11">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12 mb-3">
                                    <label for="adres" class="form-label">Adres</label>
                                    <textarea class="form-control" id="adres" name="adres" rows="3"></textarea>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12 mb-3">
                                    <label for="notlar" class="form-label">Notlar</label>
                                    <textarea class="form-control" id="notlar" name="notlar" rows="3"></textarea>
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
    </script>
</body>
</html> 