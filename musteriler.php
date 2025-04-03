<?php
require_once 'config.php';

// Oturum kontrolü
if (!isset($_SESSION['kullanici_id'])) {
    header('Location: login.php');
    exit();
}

// Müşterileri getir
$stmt = $db->query("SELECT * FROM musteriler ORDER BY ad_soyad");
$musteriler = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Müşteriler - Flow Organizasyon</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Müşteriler</h1>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#musteriEkleModal">
                <i class="bi bi-plus-circle"></i> Yeni Müşteri
            </button>
        </div>

        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Ad Soyad</th>
                        <th>Telefon</th>
                        <th>E-posta</th>
                        <th>Adres</th>
                        <th>İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($musteriler as $musteri): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($musteri['ad_soyad']); ?></td>
                            <td><?php echo htmlspecialchars($musteri['telefon']); ?></td>
                            <td><?php echo htmlspecialchars($musteri['email']); ?></td>
                            <td><?php echo htmlspecialchars($musteri['adres']); ?></td>
                            <td>
                                <button type="button" class="btn btn-sm btn-info" onclick="musteriDetay(<?php echo $musteri['id']; ?>)">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-warning" onclick="musteriDuzenle(<?php echo $musteri['id']; ?>)">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-danger" onclick="musteriSil(<?php echo $musteri['id']; ?>)">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Müşteri Ekleme Modal -->
    <div class="modal fade" id="musteriEkleModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Yeni Müşteri Ekle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="musteriEkleForm">
                        <div class="mb-3">
                            <label for="ad_soyad" class="form-label">Ad Soyad</label>
                            <input type="text" class="form-control" id="ad_soyad" name="ad_soyad" required>
                        </div>
                        <div class="mb-3">
                            <label for="telefon" class="form-label">Telefon</label>
                            <input type="tel" class="form-control" id="telefon" name="telefon">
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">E-posta</label>
                            <input type="email" class="form-control" id="email" name="email">
                        </div>
                        <div class="mb-3">
                            <label for="adres" class="form-label">Adres</label>
                            <textarea class="form-control" id="adres" name="adres" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="notlar" class="form-label">Notlar</label>
                            <textarea class="form-control" id="notlar" name="notlar" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="button" class="btn btn-primary" onclick="musteriKaydet()">Kaydet</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function musteriKaydet() {
            const form = document.getElementById('musteriEkleForm');
            const formData = new FormData(form);
            
            fetch('musteri_kaydet.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Hata: ' + data.message);
                }
            });
        }

        function musteriDetay(id) {
            window.location.href = `musteri_detay.php?id=${id}`;
        }

        function musteriDuzenle(id) {
            window.location.href = `musteri_duzenle.php?id=${id}`;
        }

        function musteriSil(id) {
            if (confirm('Bu müşteriyi silmek istediğinizden emin misiniz?')) {
                fetch('musteri_sil.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ id: id })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Hata: ' + data.message);
                    }
                });
            }
        }
    </script>
</body>
</html> 