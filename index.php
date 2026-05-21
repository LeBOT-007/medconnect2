<?php
session_start();

// Si déjà connecté, rediriger vers le bon dashboard
if (isset($_SESSION['user_role'])) {
    switch (trim($_SESSION['user_role'])) {
        case 'admin':
            header("Location: admin/dashboard.php");
            break;
        case 'medecin':
            header("Location: medecin/dashboard.php");
            break;
        case 'patient':
            header("Location: patient/dashboard.php");
            break;
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MedConnect - Votre santé, notre priorité</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { font-family: 'Segoe UI', sans-serif; }

        .hero {
            background: linear-gradient(135deg, #1a6fc4 0%, #0d47a1 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }

        .hero h1 { font-size: 3rem; font-weight: 800; }
        .hero p  { font-size: 1.2rem; opacity: .9; }

        .feature-icon {
            width: 64px;
            height: 64px;
            background: #e8f0fe;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            color: #1a6fc4;
            margin-bottom: 1rem;
        }

        .card-feature {
            border: none;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,.07);
            transition: transform .2s;
        }
        .card-feature:hover { transform: translateY(-5px); }

        footer {
            background: #0d1b2a;
            color: #94a3b8;
        }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold fs-4" href="index.php">
                <i class="bi bi-heart-pulse-fill me-2"></i>MedConnect
            </a>
            <div class="ms-auto d-flex gap-2">
                <a href="login.php" class="btn btn-outline-light btn-sm px-3">
                    <i class="bi bi-box-arrow-in-right me-1"></i>Connexion
                </a>
                <a href="register.php" class="btn btn-light btn-sm px-3 text-primary fw-bold">
                    <i class="bi bi-person-plus me-1"></i>Inscription
                </a>
            </div>
        </div>
    </nav>

    <!-- Hero -->
    <section class="hero text-white">
        <div class="container text-center py-5">
            <i class="bi bi-heart-pulse-fill display-1 mb-4 d-block"></i>
            <h1 class="mb-3">Votre santé, notre priorité</h1>
            <p class="mb-5 mx-auto" style="max-width: 600px;">
                MedConnect vous permet de prendre rendez-vous avec un médecin en quelques clics, 
                suivre vos consultations et gérer votre dossier médical en toute simplicité.
            </p>
            <div class="d-flex justify-content-center gap-3 flex-wrap">
                <a href="register.php" class="btn btn-light btn-lg fw-bold px-5 text-primary shadow">
                    <i class="bi bi-person-plus-fill me-2"></i>Créer un compte
                </a>
                <a href="login.php" class="btn btn-outline-light btn-lg px-5">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Se connecter
                </a>
            </div>
        </div>
    </section>

    <!-- Features -->
    <section class="py-5 bg-white">
        <div class="container">
            <h2 class="text-center fw-bold mb-2">Pourquoi choisir MedConnect ?</h2>
            <p class="text-center text-muted mb-5">Une plateforme complète pour patients et médecins</p>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card card-feature p-4 h-100">
                        <div class="feature-icon"><i class="bi bi-calendar2-check"></i></div>
                        <h5 class="fw-bold">Prise de rendez-vous facile</h5>
                        <p class="text-muted mb-0">Réservez une consultation en ligne 24h/24, choisissez votre médecin et votre créneau en quelques secondes.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card card-feature p-4 h-100">
                        <div class="feature-icon"><i class="bi bi-person-heart"></i></div>
                        <h5 class="fw-bold">Médecins qualifiés</h5>
                        <p class="text-muted mb-0">Accédez à un réseau de médecins spécialistes vérifiés et disponibles pour vous accompagner.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card card-feature p-4 h-100">
                        <div class="feature-icon"><i class="bi bi-shield-check"></i></div>
                        <h5 class="fw-bold">Données sécurisées</h5>
                        <p class="text-muted mb-0">Vos informations médicales sont protégées et accessibles uniquement par vous et votre médecin.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Comment ça marche -->
    <section class="py-5 bg-light">
        <div class="container">
            <h2 class="text-center fw-bold mb-2">Comment ça marche ?</h2>
            <p class="text-center text-muted mb-5">3 étapes simples pour votre consultation</p>
            <div class="row g-4 text-center">
                <div class="col-md-4">
                    <div class="display-4 fw-bold text-primary mb-2">1</div>
                    <h5 class="fw-bold">Créez votre compte</h5>
                    <p class="text-muted">Inscrivez-vous gratuitement en tant que patient en quelques minutes.</p>
                </div>
                <div class="col-md-4">
                    <div class="display-4 fw-bold text-primary mb-2">2</div>
                    <h5 class="fw-bold">Choisissez un médecin</h5>
                    <p class="text-muted">Parcourez notre liste de médecins et sélectionnez celui qui correspond à vos besoins.</p>
                </div>
                <div class="col-md-4">
                    <div class="display-4 fw-bold text-primary mb-2">3</div>
                    <h5 class="fw-bold">Confirmez votre RDV</h5>
                    <p class="text-muted">Choisissez un créneau disponible et recevez la confirmation de votre rendez-vous.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Final -->
    <section class="py-5 bg-primary text-white text-center">
        <div class="container">
            <h2 class="fw-bold mb-3">Prêt à prendre soin de votre santé ?</h2>
            <p class="mb-4 opacity-75">Rejoignez MedConnect et simplifiez vos démarches médicales dès aujourd'hui.</p>
            <a href="register.php" class="btn btn-light btn-lg fw-bold px-5 text-primary shadow">
                <i class="bi bi-person-plus-fill me-2"></i>S'inscrire gratuitement
            </a>
        </div>
    </section>

    <!-- Footer -->
    <footer class="py-4 text-center">
        <div class="container">
            <p class="mb-0">
                <i class="bi bi-heart-pulse-fill text-danger me-2"></i>
                <strong class="text-white">MedConnect</strong> &copy; <?php echo date('Y'); ?> — Tous droits réservés
            </p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>