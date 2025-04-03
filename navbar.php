<?php
// Oturum kontrolü
if (!isset($_SESSION['kullanici_id'])) {
    header('Location: login.php');
    exit();
}

$current_page = basename($_SERVER['PHP_SELF']);
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="index.php">Flow Organizasyon</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page == 'index.php' ? 'active' : ''; ?>" href="index.php">
                        <i class="bi bi-house-door"></i> Ana Sayfa
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page == 'organizasyonlar.php' ? 'active' : ''; ?>" href="organizasyonlar.php">
                        <i class="bi bi-calendar-event"></i> Organizasyonlar
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page == 'musteriler.php' ? 'active' : ''; ?>" href="musteriler.php">
                        <i class="bi bi-people"></i> Müşteriler
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page == 'sozlesmeler.php' ? 'active' : ''; ?>" href="sozlesmeler.php">
                        <i class="bi bi-file-text"></i> Sözleşmeler
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page == 'odemeler.php' ? 'active' : ''; ?>" href="odemeler.php">
                        <i class="bi bi-cash"></i> Ödemeler
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page == 'personel.php' ? 'active' : ''; ?>" href="personel.php">
                        <i class="bi bi-person-badge"></i> Personel
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page == 'takvim.php' ? 'active' : ''; ?>" href="takvim.php">
                        <i class="bi bi-calendar"></i> Takvim
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page == 'raporlar.php' ? 'active' : ''; ?>" href="raporlar.php">
                        <i class="bi bi-graph-up"></i> Raporlar
                    </a>
                </li>
            </ul>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">
                        <i class="bi bi-box-arrow-right"></i> Çıkış
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav> 