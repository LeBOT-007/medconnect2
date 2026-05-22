<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold text-info" href="dashboard.php"><i class="bi bi-shield-lock-fill me-2"></i>MedConnect Admin</a>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'active' : ''; ?>" href="dashboard.php">
                        <i class="bi bi-speedometer2 me-1"></i>Tableau de bord
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'medecin.php') ? 'active' : ''; ?>" href="medecin.php">
                        <i class="bi bi-person-heart me-1"></i>Gestion Médecins
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'specialite.php') ? 'active' : ''; ?>" href="specialite.php">
                        <i class="bi bi-journal-medical me-1"></i>Spécialités
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'utilisateurs.php') ? 'active' : ''; ?>" href="utilisateurs.php">
                        <i class="bi bi-people me-1"></i>Gestion des Utilisateurs
                    </a>
                </li>
            </ul>
            <ul class="navbar-nav align-items-center">
                <li class="nav-item">
                    <span class="nav-link text-white-50 me-3">Admin: <?php echo htmlspecialchars($_SESSION['user_nom'] ?? 'Administrateur'); ?></span>
                </li>
                <li class="nav-item">
                    <a class="nav-link btn btn-outline-danger btn-sm text-white px-3" href="../logout.php"><i class="bi bi-box-arrow-right me-1"></i>Déconnexion</a>
                </li>
            </ul>
        </div>
    </div>
</nav>