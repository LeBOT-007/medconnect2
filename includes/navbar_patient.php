<?php
// Détection de la page actuelle pour la classe active
$current_page = basename($_SERVER['PHP_SELF']);
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm mb-4">
    <div class="container">
        <a class="navbar-brand fw-bold" href="dashboard.php">
            <i class="bi bi-heart-pulse-fill me-2"></i>MedConnect
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarPatient" aria-controls="navbarPatient" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarPatient">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'dashboard.php') ? 'active fw-bold' : ''; ?>" href="dashboard.php">
                        <i class="bi bi-house-door-fill me-1"></i>Mon Espace
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'prendre_rdv.php') ? 'active fw-bold' : ''; ?>" href="prendre_rdv.php">
                        <i class="bi bi-calendar-plus-fill me-1"></i>Prendre RDV
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'historique.php') ? 'active fw-bold' : ''; ?>" href="historique.php">
                        <i class="bi bi-journal-medical me-1"></i>Historique complet
                    </a>
                </li>
            </ul>

            <div class="d-flex align-items-center">
                <span class="navbar-text text-white me-3 d-none d-lg-inline">
                    <i class="bi bi-person-circle me-1"></i>
                    Bonjour, <strong><?php echo htmlspecialchars($_SESSION['user_prenom'] ?? 'Patient'); ?></strong>
                </span>
                <a class="btn btn-outline-light btn-sm px-3 shadow-sm" href="../logout.php">
                    <i class="bi bi-box-arrow-right me-1"></i>Déconnexion
                </a>
            </div>
        </div>
    </div>
</nav>