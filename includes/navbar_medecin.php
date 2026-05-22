<?php
// Détection de la page actuelle pour appliquer la classe 'active'
$current_page = basename($_SERVER['PHP_SELF']);
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm mb-4">
    <div class="container">
        <a class="navbar-brand fw-bold" href="dashboard.php">
            <i class="bi bi-heart-pulse-fill me-2"></i>MedConnect <span class="badge bg-white text-primary ms-1 small" style="font-size: 0.75rem;">Pro</span>
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMedecin" aria-controls="navbarMedecin" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarMedecin">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'dashboard.php') ? 'active fw-bold' : ''; ?>" href="dashboard.php">
                        <i class="bi bi-grid-1x2-fill me-1"></i>Tableau de bord
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'disponibilites.php') ? 'active fw-bold' : ''; ?>" href="disponibilites.php">
                        <i class="bi bi-calendar3 me-1"></i>Mes Disponibilités
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'profils.php') ? 'active fw-bold' : ''; ?>" href="profils.php">
                        <i class="bi bi-person-lines-fill me-1"></i>Mon Profil
                    </a>
                </li>
            </ul>

            <div class="d-flex align-items-center">
                <span class="navbar-text text-white me-3 d-none d-lg-inline">
                    <i class="bi bi-person-md me-1"></i>
                    Dr. <strong><?php echo htmlspecialchars($_SESSION['user_nom'] ?? 'Praticien'); ?></strong>
                </span>
                <a class="btn btn-outline-light btn-sm px-3 shadow-sm" href="../logout.php">
                    <i class="bi bi-box-arrow-right me-1"></i>Déconnexion
                </a>
            </div>
        </div>
    </div>
</nav>