<?php
require_once 'config.php';

// Oturum kontrolü
if (!isset($_SESSION['kullanici_id'])) {
    header('Location: login.php');
    exit();
}

// Sözleşmeleri getir
$stmt = $db->prepare("
    SELECT s.*, o.organizasyon_kodu, o.baslik, m.ad_soyad as musteri_adi 
    FROM sozlesmeler s 
    JOIN organizasyonlar o ON s.organizasyon_id = o.id 
    JOIN musteriler m ON o.musteri_id = m.id 
    ORDER BY s.olusturma_tarihi DESC
");
$stmt->execute();
$sozlesmeler = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sözleşmeler - Flow Organizasyon</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Sözleşmeler</h2>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#sozlesmeEkleModal">
                <i class="bi bi-plus-lg"></i> Yeni Sözleşme
            </button>
        </div>

        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Sözleşme No</th>
                        <th>Organizasyon</th>
                        <th>Müşteri</th>
                        <th>İmza Tarihi</th>
                        <th>Toplam Tutar</th>
                        <th>Durum</th>
                        <th>İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sozlesmeler as $sozlesme): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($sozlesme['sozlesme_no']); ?></td>
                        <td>
                            <?php echo htmlspecialchars($sozlesme['organizasyon_kodu']); ?> - 
                            <?php echo htmlspecialchars($sozlesme['baslik']); ?>
                        </td>
                        <td><?php echo htmlspecialchars($sozlesme['musteri_adi']); ?></td>
                        <td><?php echo date('d.m.Y', strtotime($sozlesme['imza_tarihi'])); ?></td>
                        <td><?php echo number_format($sozlesme['toplam_tutar'], 2, ',', '.'); ?> ₺</td>
                        <td>
                            <?php
                            $durum_class = '';
                            switch($sozlesme['durum']) {
                                case 'aktif':
                                    $durum_class = 'success';
                                    break;
                                case 'pasif':
                                    $durum_class = 'warning';
                                    break;
                                case 'iptal':
                                    $durum_class = 'danger';
                                    break;
                            }
                            ?>
                            <span class="badge bg-<?php echo $durum_class; ?>">
                                <?php echo ucfirst($sozlesme['durum']); ?>
                            </span>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-info" onclick="sozlesmeGoruntule(<?php echo $sozlesme['id']; ?>)">
                                <i class="bi bi-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-warning" onclick="sozlesmeDuzenle(<?php echo $sozlesme['id']; ?>)">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="sozlesmeSil(<?php echo $sozlesme['id']; ?>)">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Sözleşme Ekleme Modal -->
    <div class="modal fade" id="sozlesmeEkleModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Yeni Sözleşme</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="sozlesmeEkleForm">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Organizasyon</label>
                                <select class="form-select" name="organizasyon_id" required>
                                    <option value="">Seçiniz</option>
                                    <?php
                                    $stmt = $db->query("SELECT o.id, o.organizasyon_kodu, o.baslik, m.ad_soyad 
                                                      FROM organizasyonlar o 
                                                      JOIN musteriler m ON o.musteri_id = m.id 
                                                      WHERE NOT EXISTS (SELECT 1 FROM sozlesmeler s WHERE s.organizasyon_id = o.id)");
                                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                        echo "<option value='{$row['id']}'>{$row['organizasyon_kodu']} - {$row['baslik']} ({$row['ad_soyad']})</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Sözleşme No</label>
                                <input type="text" class="form-control" name="sozlesme_no" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">İmza Tarihi</label>
                                <input type="date" class="form-control" name="imza_tarihi" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Başlangıç Tarihi</label>
                                <input type="date" class="form-control" name="baslangic_tarihi" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Bitiş Tarihi</label>
                                <input type="date" class="form-control" name="bitis_tarihi" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Toplam Tutar</label>
                            <input type="number" step="0.01" class="form-control" name="toplam_tutar" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Ödeme Planı</label>
                            <textarea class="form-control" name="odeme_plani" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Özel Koşullar</label>
                            <textarea class="form-control" name="ozel_kosullar" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="button" class="btn btn-primary" onclick="sozlesmeEkle()">Kaydet</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function sozlesmeEkle() {
            $.ajax({
                url: 'sozlesme_ekle.php',
                type: 'POST',
                data: $('#sozlesmeEkleForm').serialize(),
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert(response.message);
                    }
                }
            });
        }

        function sozlesmeGoruntule(id) {
            window.location.href = 'sozlesme_goruntule.php?id=' + id;
        }

        function sozlesmeDuzenle(id) {
            window.location.href = 'sozlesme_duzenle.php?id=' + id;
        }

        function sozlesmeSil(id) {
            if (confirm('Bu sözleşmeyi silmek istediğinizden emin misiniz?')) {
                $.ajax({
                    url: 'sozlesme_sil.php',
                    type: 'POST',
                    data: { id: id },
                    success: function(response) {
                        if (response.success) {
                            location.reload();
                        } else {
                            alert(response.message);
                        }
                    }
                });
            }
        }
    </script>
</body>
</html> 