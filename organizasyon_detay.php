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

    // Ödemeleri getir
    $stmt = $db->prepare("
        SELECT o.*, m.ad_soyad as musteri_adi
        FROM odemeler o
        LEFT JOIN organizasyonlar org ON o.organizasyon_id = org.id
        LEFT JOIN musteriler m ON org.musteri_id = m.id
        WHERE o.organizasyon_id = ?
        ORDER BY o.odeme_tarihi DESC
    ");
    $stmt->execute([$id]);
    $odemeler = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Sözleşmeleri getir
    $stmt = $db->prepare("
        SELECT s.*, m.ad_soyad as musteri_adi
        FROM sozlesmeler s
        LEFT JOIN organizasyonlar org ON s.organizasyon_id = org.id
        LEFT JOIN musteriler m ON org.musteri_id = m.id
        WHERE s.organizasyon_id = ?
        ORDER BY s.imza_tarihi DESC
    ");
    $stmt->execute([$id]);
    $sozlesmeler = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Personel görevlerini getir
    $stmt = $db->prepare("
        SELECT pg.*, p.ad_soyad as personel_adi
        FROM personel_gorevleri pg
        JOIN personel p ON pg.personel_id = p.id
        WHERE pg.organizasyon_id = ?
        ORDER BY pg.baslangic_tarihi DESC
    ");
    $stmt->execute([$id]);
    $gorevler = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Veritabanı hatası: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Organizasyon Detayı - Flow Organizasyon</title>
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
                            <i class="bi bi-calendar-event me-2"></i>
                            Organizasyon Detayı
                        </h5>
                        <div>
                            <a href="sozlesme.php?id=<?php echo $id; ?>" class="btn btn-light btn-sm me-2" target="_blank">
                                <i class="bi bi-file-text"></i> Sözleşme
                            </a>
                            <a href="organizasyon_duzenle.php?id=<?php echo $id; ?>" class="btn btn-light btn-sm me-2">
                                <i class="bi bi-pencil"></i> Düzenle
                            </a>
                            <a href="index.php" class="btn btn-light btn-sm">
                                <i class="bi bi-arrow-left"></i> Geri Dön
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Organizasyon Bilgileri -->
                            <div class="col-md-6">
                                <div class="card mb-4">
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
                                                <label class="form-label fw-bold">Durum:</label>
                                                <p>
                                                    <?php
                                                    $durum_renk = [
                                                        'planlandi' => 'warning',
                                                        'devam_ediyor' => 'info',
                                                        'tamamlandi' => 'success',
                                                        'iptal' => 'danger'
                                                    ];
                                                    $durum_metin = [
                                                        'planlandi' => 'Planlandı',
                                                        'devam_ediyor' => 'Devam Ediyor',
                                                        'tamamlandi' => 'Tamamlandı',
                                                        'iptal' => 'İptal'
                                                    ];
                                                    ?>
                                                    <span class="badge bg-<?php echo $durum_renk[$organizasyon['durum']]; ?>">
                                                        <?php echo $durum_metin[$organizasyon['durum']]; ?>
                                                    </span>
                                                </p>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label fw-bold">Tarih:</label>
                                                <p><?php echo date('d.m.Y', strtotime($organizasyon['tarih'])); ?></p>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label fw-bold">Saat:</label>
                                                <p><?php echo date('H:i', strtotime($organizasyon['baslangic_saati'])); ?> - <?php echo date('H:i', strtotime($organizasyon['bitis_saati'])); ?></p>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label fw-bold">Toplam Tutar:</label>
                                                <p><?php echo number_format($organizasyon['toplam_tutar'], 2, ',', '.'); ?> ₺</p>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label fw-bold">Kapora:</label>
                                                <p><?php echo number_format($organizasyon['kapora'], 2, ',', '.'); ?> ₺</p>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-12 mb-3">
                                                <label class="form-label fw-bold">Kalan Tutar:</label>
                                                <p><?php echo number_format($organizasyon['kalan_tutar'], 2, ',', '.'); ?> ₺</p>
                                            </div>
                                        </div>
                                        <?php if ($organizasyon['ozel_istekler']): ?>
                                        <div class="row">
                                            <div class="col-12">
                                                <label class="form-label fw-bold">Özel İstekler:</label>
                                                <p><?php echo nl2br(htmlspecialchars($organizasyon['ozel_istekler'])); ?></p>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Müşteri Bilgileri -->
                            <div class="col-md-6">
                                <div class="card mb-4">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0">Müşteri Bilgileri</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-12 mb-3">
                                                <label class="form-label fw-bold">Ad Soyad:</label>
                                                <p><?php echo htmlspecialchars($organizasyon['musteri_adi']); ?></p>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label fw-bold">Telefon:</label>
                                                <p><?php echo htmlspecialchars($organizasyon['musteri_telefon']); ?></p>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label fw-bold">E-posta:</label>
                                                <p><?php echo htmlspecialchars($organizasyon['musteri_email']); ?></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Ödemeler -->
                        <div class="card mb-4">
                            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">Ödemeler</h6>
                                <a href="odeme_ekle.php?organizasyon_id=<?php echo $id; ?>" class="btn btn-primary btn-sm">
                                    <i class="bi bi-plus"></i> Yeni Ödeme
                                </a>
                            </div>
                            <div class="card-body">
                                <?php if (count($odemeler) > 0): ?>
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Tarih</th>
                                                    <th>Müşteri</th>
                                                    <th>Tutar</th>
                                                    <th>Ödeme Türü</th>
                                                    <th>Açıklama</th>
                                                    <th>İşlemler</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($odemeler as $odeme): ?>
                                                    <tr>
                                                        <td><?php echo date('d.m.Y', strtotime($odeme['odeme_tarihi'])); ?></td>
                                                        <td><?php echo htmlspecialchars($odeme['musteri_adi']); ?></td>
                                                        <td><?php echo number_format($odeme['tutar'], 2, ',', '.'); ?> ₺</td>
                                                        <td><?php echo ucfirst($odeme['odeme_turu']); ?></td>
                                                        <td><?php echo htmlspecialchars($odeme['aciklama']); ?></td>
                                                        <td>
                                                            <a href="odeme_duzenle.php?id=<?php echo $odeme['id']; ?>" class="btn btn-sm btn-primary">
                                                                <i class="bi bi-pencil"></i>
                                                            </a>
                                                            <a href="odeme_sil.php?id=<?php echo $odeme['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Bu ödemeyi silmek istediğinizden emin misiniz?')">
                                                                <i class="bi bi-trash"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <p class="text-center text-muted">Henüz ödeme kaydı bulunmamaktadır.</p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Sözleşmeler -->
                        <div class="card mb-4">
                            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">Sözleşmeler</h6>
                                <a href="sozlesme_ekle.php?organizasyon_id=<?php echo $id; ?>" class="btn btn-primary btn-sm">
                                    <i class="bi bi-plus"></i> Yeni Sözleşme
                                </a>
                            </div>
                            <div class="card-body">
                                <?php if (count($sozlesmeler) > 0): ?>
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Sözleşme No</th>
                                                    <th>Müşteri</th>
                                                    <th>İmza Tarihi</th>
                                                    <th>Başlangıç</th>
                                                    <th>Bitiş</th>
                                                    <th>Tutar</th>
                                                    <th>Durum</th>
                                                    <th>İşlemler</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($sozlesmeler as $sozlesme): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($sozlesme['sozlesme_no']); ?></td>
                                                        <td><?php echo htmlspecialchars($sozlesme['musteri_adi']); ?></td>
                                                        <td><?php echo date('d.m.Y', strtotime($sozlesme['imza_tarihi'])); ?></td>
                                                        <td><?php echo date('d.m.Y', strtotime($sozlesme['baslangic_tarihi'])); ?></td>
                                                        <td><?php echo date('d.m.Y', strtotime($sozlesme['bitis_tarihi'])); ?></td>
                                                        <td><?php echo number_format($sozlesme['toplam_tutar'], 2, ',', '.'); ?> ₺</td>
                                                        <td>
                                                            <?php
                                                            $durum_renk = [
                                                                'aktif' => 'success',
                                                                'pasif' => 'secondary',
                                                                'iptal' => 'danger'
                                                            ];
                                                            $durum_metin = [
                                                                'aktif' => 'Aktif',
                                                                'pasif' => 'Pasif',
                                                                'iptal' => 'İptal'
                                                            ];
                                                            ?>
                                                            <span class="badge bg-<?php echo $durum_renk[$sozlesme['durum']]; ?>">
                                                                <?php echo $durum_metin[$sozlesme['durum']]; ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <a href="sozlesme_duzenle.php?id=<?php echo $sozlesme['id']; ?>" class="btn btn-sm btn-primary">
                                                                <i class="bi bi-pencil"></i>
                                                            </a>
                                                            <a href="sozlesme_sil.php?id=<?php echo $sozlesme['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Bu sözleşmeyi silmek istediğinizden emin misiniz?')">
                                                                <i class="bi bi-trash"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <p class="text-center text-muted">Henüz sözleşme kaydı bulunmamaktadır.</p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Personel Görevleri -->
                        <div class="card">
                            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">Personel Görevleri</h6>
                                <a href="gorev_ekle.php?organizasyon_id=<?php echo $id; ?>" class="btn btn-primary btn-sm">
                                    <i class="bi bi-plus"></i> Yeni Görev
                                </a>
                            </div>
                            <div class="card-body">
                                <?php if (count($gorevler) > 0): ?>
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Personel</th>
                                                    <th>Görev</th>
                                                    <th>Başlangıç</th>
                                                    <th>Bitiş</th>
                                                    <th>Durum</th>
                                                    <th>Notlar</th>
                                                    <th>İşlemler</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($gorevler as $gorev): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($gorev['personel_adi']); ?></td>
                                                        <td><?php echo htmlspecialchars($gorev['gorev']); ?></td>
                                                        <td><?php echo date('d.m.Y', strtotime($gorev['baslangic_tarihi'])); ?></td>
                                                        <td><?php echo date('d.m.Y', strtotime($gorev['bitis_tarihi'])); ?></td>
                                                        <td>
                                                            <?php
                                                            $durum_renk = [
                                                                'beklemede' => 'warning',
                                                                'devam_ediyor' => 'info',
                                                                'tamamlandi' => 'success',
                                                                'iptal' => 'danger'
                                                            ];
                                                            $durum_metin = [
                                                                'beklemede' => 'Beklemede',
                                                                'devam_ediyor' => 'Devam Ediyor',
                                                                'tamamlandi' => 'Tamamlandı',
                                                                'iptal' => 'İptal'
                                                            ];
                                                            ?>
                                                            <span class="badge bg-<?php echo $durum_renk[$gorev['durum']]; ?>">
                                                                <?php echo $durum_metin[$gorev['durum']]; ?>
                                                            </span>
                                                        </td>
                                                        <td><?php echo htmlspecialchars($gorev['notlar']); ?></td>
                                                        <td>
                                                            <a href="gorev_duzenle.php?id=<?php echo $gorev['id']; ?>" class="btn btn-sm btn-primary">
                                                                <i class="bi bi-pencil"></i>
                                                            </a>
                                                            <a href="gorev_sil.php?id=<?php echo $gorev['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Bu görevi silmek istediğinizden emin misiniz?')">
                                                                <i class="bi bi-trash"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <p class="text-center text-muted">Henüz personel görevi bulunmamaktadır.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 