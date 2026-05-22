<?php
// ============================================================
// 1. LE BACKEND : Traitement de l'authentification (Optimisé)
// ============================================================
session_start();
require_once 'config/database.php';

// Si l'utilisateur est déjà connecté, on le redirige directement vers son espace
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

$erreur = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo   = getConnexion();
    $email = trim($_POST['email']);
    $mdp   = $_POST['mot_de_passe'];

    // Recherche de l'utilisateur par son email
    $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Vérification de l'existence de l'utilisateur et du mot de passe haché
    if ($user && password_verify($mdp, $user['mot_de_passe'])) {
        
        // Nettoyage des chaînes pour éviter les espaces liés au type ENUM de MySQL
        $role_nettoye = trim($user['role']);

        $_SESSION['user_id']     = $user['id_utilisateur'];
        $_SESSION['user_nom']    = $user['nom'];
        $_SESSION['user_prenom'] = $user['prenom'];
        $_SESSION['user_role']   = $role_nettoye;

        // --- RÉCUPÉRATION DE L'ID PATIENT SI RÔLE PATIENT ---
        if ($role_nettoye === 'patient') {
            $stmtPatient = $pdo->prepare("SELECT id_patient FROM patients WHERE id_utilisateur = ?");
            $stmtPatient->execute([$user['id_utilisateur']]);
            $patientData = $stmtPatient->fetch(PDO::FETCH_ASSOC);
            
            if ($patientData) {
                // On stocke le VRAI id_patient lié à la clé étrangère
                $_SESSION['patient_id'] = $patientData['id_patient'];
            } else {
                $erreur = "Profil patient introuvable. Veuillez contacter l'administrateur.";
                session_destroy();
                unset($_SESSION);
            }
        }

        if (empty($erreur)) {
            // Redirection basée sur le rôle nettoyé
            switch ($role_nettoye) {
                case 'admin':
                    header("Location: admin/dashboard.php");
                    break;
                case 'medecin':
                    header("Location: medecin/dashboard.php");
                    break;
                case 'patient':
                    header("Location: patient/dashboard.php");
                    break;
                default:
                    header("Location: login.php");
                    break;
            }
            exit;
        }
    } else {
        $erreur = "Email ou mot de passe incorrect.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MedConnect - Connexion</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-light d-flex align-items-center" style="min-height: 100vh;">

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                
                <div class="text-center mb-4">
                    <h1 class="text-primary fw-bold mb-1"><i class="bi bi-heart-pulse-fill me-2"></i>MedConnect</h1>
                    <p class="text-muted">Votre plateforme de santé en ligne</p>
                </div>

                <div class="card border-0 shadow-sm p-4">
                    <div class="card-body">
                        <h4 class="card-title fw-bold text-dark mb-4 text-center">Connexion</h4>

                        <?php if (!empty($erreur)): ?>
                            <div class="alert alert-danger d-flex align-items-center" role="alert">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                <div><?php echo htmlspecialchars($erreur); ?></div>
                            </div>
                        <?php endif; ?>

                        <form action="login.php" method="POST">
                            <div class="mb-3">
                                <label for="email" class="form-label fw-semibold">Adresse Email</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-envelope"></i></span>
                                    <input type="email" name="email" id="email" class="form-control border-start-0 ps-0" placeholder="exemple@domaine.com" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="mot_de_passe" class="form-label fw-semibold">Mot de passe</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-lock"></i></span>
                                    <input type="password" name="mot_de_passe" id="mot_de_passe" class="form-control border-start-0 ps-0" placeholder="••••••••" required>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 py-2 fw-bold shadow-sm mb-3">
                                <i class="bi bi-box-arrow-in-right me-2"></i>Se connecter
                            </button>
                        </form>

                        <hr class="text-muted">

                        <div class="text-center mt-3">
                            <p class="text-muted mb-0">Nouveau sur MedConnect ?</p>
                            <a href="register.php" class="text-primary fw-semibold text-decoration-none">Créer un compte patient</a>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>