<?php
require_once 'config.php';

// Oturum kontrolü
if (!isset($_SESSION['kullanici_id'])) {
    header('Location: login.php');
    exit();
}

// Organizasyonları getir
$stmt = $db->query("SELECT o.*, m.ad_soyad as musteri_adi 
                    FROM organizasyonlar o 
                    LEFT JOIN musteriler m ON o.musteri_id = m.id 
                    ORDER BY o.tarih DESC");
$organizasyonlar = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Organizasyonlar - Flow Organizasyon</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Organizasyonlar</h1>
            <a href="organizasyon_ekle.php" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Yeni Organizasyon
            </a>
        </div>

        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Kod</th>
                        <th>Müşteri</th>
                        <th>Başlık</th>
                        <th>Tarih</th>
                        <th>Saat</th>
                        <th>Tutar</th>
                        <th>Durum</th>
                        <th>İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($organizasyonlar as $org): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($org['organizasyon_kodu']); ?></td>
                            <td><?php echo htmlspecialchars($org['musteri_adi']); ?></td>
                            <td><?php echo htmlspecialchars($org['baslik']); ?></td>
                            <td><?php echo date('d.m.Y', strtotime($org['tarih'])); ?></td>
                            <td><?php echo date('H:i', strtotime($org['baslangic_saati'])) . ' - ' . date('H:i', strtotime($org['bitis_saati'])); ?></td>
                            <td><?php echo number_format($org['toplam_tutar'], 2, ',', '.'); ?> ₺</td>
                            <td>
                                <span class="badge bg-<?php 
                                    echo $org['durum'] == 'planlandi' ? 'info' : 
                                        ($org['durum'] == 'devam_ediyor' ? 'warning' : 
                                        ($org['durum'] == 'tamamlandi' ? 'success' : 'danger')); 
                                ?>">
                                    <?php 
                                    echo $org['durum'] == 'planlandi' ? 'Planlandı' : 
                                        ($org['durum'] == 'devam_ediyor' ? 'Devam Ediyor' : 
                                        ($org['durum'] == 'tamamlandi' ? 'Tamamlandı' : 'İptal')); 
                                    ?>
                                </span>
                            </td>
                            <td>
                                <a href="organizasyon_detay.php?id=<?php echo $org['id']; ?>" class="btn btn-sm btn-info">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="organizasyon_duzenle.php?id=<?php echo $org['id']; ?>" class="btn btn-sm btn-warning">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="organizasyon_sil.php?id=<?php echo $org['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Bu organizasyonu silmek istediğinizden emin misiniz?')">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 