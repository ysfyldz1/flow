<?php
// Hata raporlamayı aç
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config.php';

// Oturum kontrolü
if (!isset($_SESSION['kullanici_id'])) {
    header('Location: login.php');
    exit();
}

// ID kontrolü
if (!isset($_GET['id'])) {
    $_SESSION['hata'] = "Müşteri ID'si belirtilmedi.";
    header('Location: musteriler.php');
    exit();
}

$id = $_GET['id'];

try {
    // Müşteri bilgilerini getir
    $stmt = $db->prepare("SELECT * FROM musteriler WHERE id = ?");
    $stmt->execute([$id]);
    $musteri = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$musteri) {
        $_SESSION['hata'] = "Müşteri bulunamadı.";
        header('Location: musteriler.php');
        exit();
    }

} catch (PDOException $e) {
    $_SESSION['hata'] = "Veritabanı hatası: " . $e->getMessage();
    header('Location: musteriler.php');
    exit();
}

// Form gönderildi mi kontrol et
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Boş alan kontrolü
        if (empty($_POST['ad_soyad']) || empty($_POST['telefon'])) {
            throw new Exception("Ad Soyad ve Telefon alanları zorunludur.");
        }

        $stmt = $db->prepare("
            UPDATE musteriler SET
                ad_soyad = ?,
                telefon = ?,
                email = ?,
                tc_no = ?,
                adres = ?,
                notlar = ?,
                guncelleme_tarihi = NOW()
            WHERE id = ?
        ");

        $stmt->execute([
            trim($_POST['ad_soyad']),
            trim($_POST['telefon']),
            trim($_POST['email']),
            trim($_POST['tc_no']),
            trim($_POST['adres']),
            trim($_POST['notlar']),
            $id
        ]);

        if ($stmt->rowCount() > 0) {
            $_SESSION['mesaj'] = "Müşteri başarıyla güncellendi.";
        } else {
            $_SESSION['hata'] = "Müşteri güncellenirken bir hata oluştu.";
        }

        header('Location: musteriler.php');
        exit();

    } catch (Exception $e) {
        $hata = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Müşteri Düzenle - Flow Organizasyon</title>
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
                            <i class="bi bi-person-gear me-2"></i>
                            Müşteri Düzenle
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
                                    <input type="text" class="form-control" id="ad_soyad" name="ad_soyad" 
                                           value="<?php echo htmlspecialchars($musteri['ad_soyad']); ?>" required>
                                    <div class="invalid-feedback">
                                        Lütfen ad soyad giriniz.
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="telefon" class="form-label">Telefon</label>
                                    <input type="tel" class="form-control" id="telefon" name="telefon" 
                                           value="<?php echo htmlspecialchars($musteri['telefon']); ?>" required>
                                    <div class="invalid-feedback">
                                        Lütfen telefon numarası giriniz.
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">E-posta</label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?php echo htmlspecialchars($musteri['email']); ?>">
                                    <div class="invalid-feedback">
                                        Lütfen geçerli bir e-posta adresi giriniz.
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="tc_no" class="form-label">T.C. No</label>
                                    <input type="text" class="form-control" id="tc_no" name="tc_no" maxlength="11" value="<?php echo htmlspecialchars($musteri['tc_no']); ?>">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12 mb-3">
                                    <label for="adres" class="form-label">Adres</label>
                                    <textarea class="form-control" id="adres" name="adres" rows="3"><?php echo htmlspecialchars($musteri['adres']); ?></textarea>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12 mb-3">
                                    <label for="notlar" class="form-label">Notlar</label>
                                    <textarea class="form-control" id="notlar" name="notlar" rows="3"><?php echo htmlspecialchars($musteri['notlar']); ?></textarea>
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