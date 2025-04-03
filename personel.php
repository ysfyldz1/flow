<?php
require_once 'config.php';

// Oturum kontrolü
if (!isset($_SESSION['kullanici_id'])) {
    header('Location: login.php');
    exit();
}

// Personel listesini getir
$stmt = $db->query("SELECT * FROM personel ORDER BY ad_soyad");
$personel = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personel - Flow Organizasyon</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Personel</h2>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#personelEkleModal">
                <i class="bi bi-plus-lg"></i> Yeni Personel
            </button>
        </div>

        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Ad Soyad</th>
                        <th>Pozisyon</th>
                        <th>Telefon</th>
                        <th>E-posta</th>
                        <th>Maaş</th>
                        <th>Durum</th>
                        <th>İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($personel as $p): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($p['ad_soyad']); ?></td>
                        <td><?php echo htmlspecialchars($p['pozisyon']); ?></td>
                        <td><?php echo htmlspecialchars($p['telefon']); ?></td>
                        <td><?php echo htmlspecialchars($p['email']); ?></td>
                        <td><?php echo number_format($p['maas'], 2, ',', '.'); ?> ₺</td>
                        <td>
                            <?php
                            $durum_class = $p['durum'] == 'aktif' ? 'success' : 'danger';
                            ?>
                            <span class="badge bg-<?php echo $durum_class; ?>">
                                <?php echo ucfirst($p['durum']); ?>
                            </span>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-info" onclick="personelGoruntule(<?php echo $p['id']; ?>)">
                                <i class="bi bi-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-warning" onclick="personelDuzenle(<?php echo $p['id']; ?>)">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="personelSil(<?php echo $p['id']; ?>)">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Personel Ekleme Modal -->
    <div class="modal fade" id="personelEkleModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Yeni Personel</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="personelEkleForm">
                        <div class="mb-3">
                            <label class="form-label">Ad Soyad</label>
                            <input type="text" class="form-control" name="ad_soyad" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Pozisyon</label>
                            <input type="text" class="form-control" name="pozisyon" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Telefon</label>
                            <input type="text" class="form-control" name="telefon">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">E-posta</label>
                            <input type="email" class="form-control" name="email">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Adres</label>
                            <textarea class="form-control" name="adres" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Maaş</label>
                            <input type="number" step="0.01" class="form-control" name="maas">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Başlangıç Tarihi</label>
                            <input type="date" class="form-control" name="baslangic_tarihi">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Notlar</label>
                            <textarea class="form-control" name="notlar" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="button" class="btn btn-primary" onclick="personelEkle()">Kaydet</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function personelEkle() {
            $.ajax({
                url: 'personel_ekle.php',
                type: 'POST',
                data: $('#personelEkleForm').serialize(),
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert(response.message);
                    }
                }
            });
        }

        function personelGoruntule(id) {
            window.location.href = 'personel_goruntule.php?id=' + id;
        }

        function personelDuzenle(id) {
            window.location.href = 'personel_duzenle.php?id=' + id;
        }

        function personelSil(id) {
            if (confirm('Bu personeli silmek istediğinizden emin misiniz?')) {
                $.ajax({
                    url: 'personel_sil.php',
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