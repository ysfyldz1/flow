<?php
require_once 'config.php';

// Oturum kontrolü
if (!isset($_SESSION['kullanici_id'])) {
    header('Location: login.php');
    exit();
}

// Tarih aralığı filtresi
$baslangic_tarihi = $_GET['baslangic_tarihi'] ?? date('Y-m-01');
$bitis_tarihi = $_GET['bitis_tarihi'] ?? date('Y-m-t');

// Ödemeleri getir
$stmt = $db->prepare("
    SELECT o.*, org.organizasyon_kodu, org.baslik, m.ad_soyad as musteri_adi 
    FROM odemeler o
    JOIN organizasyonlar org ON o.organizasyon_id = org.id
    JOIN musteriler m ON org.musteri_id = m.id
    WHERE o.odeme_tarihi BETWEEN ? AND ?
    ORDER BY o.odeme_tarihi DESC
");
$stmt->execute([$baslangic_tarihi, $bitis_tarihi]);
$odemeler = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Toplam ödeme tutarı
$stmt = $db->prepare("
    SELECT SUM(tutar) as toplam_tutar 
    FROM odemeler 
    WHERE odeme_tarihi BETWEEN ? AND ?
");
$stmt->execute([$baslangic_tarihi, $bitis_tarihi]);
$toplam_tutar = $stmt->fetch(PDO::FETCH_ASSOC)['toplam_tutar'] ?? 0;
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ödemeler - Flow Organizasyon</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Ödemeler</h2>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#odemeEkleModal">
                <i class="bi bi-plus-lg"></i> Yeni Ödeme
            </button>
        </div>

        <!-- Filtre Formu -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Başlangıç Tarihi</label>
                        <input type="date" class="form-control" name="baslangic_tarihi" value="<?php echo $baslangic_tarihi; ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Bitiş Tarihi</label>
                        <input type="date" class="form-control" name="bitis_tarihi" value="<?php echo $bitis_tarihi; ?>">
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary">Filtrele</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Toplam Ödeme Kartı -->
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Toplam Ödeme</h5>
                <h3 class="text-primary"><?php echo number_format($toplam_tutar, 2, ',', '.'); ?> ₺</h3>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Tarih</th>
                        <th>Organizasyon</th>
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
                        <td>
                            <?php echo htmlspecialchars($odeme['organizasyon_kodu']); ?> - 
                            <?php echo htmlspecialchars($odeme['baslik']); ?>
                        </td>
                        <td><?php echo htmlspecialchars($odeme['musteri_adi']); ?></td>
                        <td><?php echo number_format($odeme['tutar'], 2, ',', '.'); ?> ₺</td>
                        <td>
                            <?php
                            $odeme_turu_class = '';
                            switch($odeme['odeme_turu']) {
                                case 'nakit':
                                    $odeme_turu_class = 'success';
                                    break;
                                case 'kredi_karti':
                                    $odeme_turu_class = 'primary';
                                    break;
                                case 'havale':
                                    $odeme_turu_class = 'info';
                                    break;
                            }
                            ?>
                            <span class="badge bg-<?php echo $odeme_turu_class; ?>">
                                <?php echo ucfirst($odeme['odeme_turu']); ?>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars($odeme['aciklama']); ?></td>
                        <td>
                            <button class="btn btn-sm btn-warning" onclick="odemeDuzenle(<?php echo $odeme['id']; ?>)">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="odemeSil(<?php echo $odeme['id']; ?>)">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Ödeme Ekleme Modal -->
    <div class="modal fade" id="odemeEkleModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Yeni Ödeme</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="odemeEkleForm">
                        <div class="mb-3">
                            <label class="form-label">Organizasyon</label>
                            <select class="form-select" name="organizasyon_id" required>
                                <option value="">Seçiniz</option>
                                <?php
                                $stmt = $db->query("SELECT o.id, o.organizasyon_kodu, o.baslik, m.ad_soyad 
                                                  FROM organizasyonlar o 
                                                  JOIN musteriler m ON o.musteri_id = m.id");
                                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    echo "<option value='{$row['id']}'>{$row['organizasyon_kodu']} - {$row['baslik']} ({$row['ad_soyad']})</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tutar</label>
                            <input type="number" step="0.01" class="form-control" name="tutar" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Ödeme Tarihi</label>
                            <input type="date" class="form-control" name="odeme_tarihi" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Ödeme Türü</label>
                            <select class="form-select" name="odeme_turu" required>
                                <option value="nakit">Nakit</option>
                                <option value="kredi_karti">Kredi Kartı</option>
                                <option value="havale">Havale</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Açıklama</label>
                            <textarea class="form-control" name="aciklama" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="button" class="btn btn-primary" onclick="odemeEkle()">Kaydet</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function odemeEkle() {
            $.ajax({
                url: 'odeme_ekle.php',
                type: 'POST',
                data: $('#odemeEkleForm').serialize(),
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert(response.message);
                    }
                }
            });
        }

        function odemeDuzenle(id) {
            window.location.href = 'odeme_duzenle.php?id=' + id;
        }

        function odemeSil(id) {
            if (confirm('Bu ödemeyi silmek istediğinizden emin misiniz?')) {
                $.ajax({
                    url: 'odeme_sil.php',
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