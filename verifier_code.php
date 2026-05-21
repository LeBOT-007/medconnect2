<?php
// ============================================================
// 1. LE BACKEND : Traitement et validation du code
// ============================================================
session_start();
require_once 'config/database.php';

// Sécurité : Si aucun utilisateur n'est en cours de validation, on redirige vers l'inscription
if (!isset($_SESSION['en_cours_validation'])) {
    header("Location: register.php");
    exit;
}

$erreur = "";
$succes = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo = getConnexion();
    
    // Récupération et sécurisation du code tapé par l'utilisateur
    $code_saisi = intval($_POST['code_verification']);
    $id_user    = $_SESSION['en_cours_validation'];

    // Récupération des informations de l'utilisateur en base de données
    $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE id_utilisateur = ?");
    $stmt->execute([$id_user]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Vérification de la correspondance du code
    if ($user && intval($user['code_verification']) === $code_saisi) {
        
        // Le code est bon : On valide le compte et on nettoie le code de vérification
        $update = $pdo->prepare("UPDATE utilisateurs SET est_valide = 1, code_verification = NULL WHERE id_utilisateur = ?");
        $update->execute([$id_user]);

        // Connexion automatique immédiate de l'utilisateur
        $_SESSION['user_id']     = $user['id_utilisateur'];
        $_SESSION['user_nom']    = $user['nom'];
        $_SESSION['user_prenom'] = $user['prenom'];
        $_SESSION['user_role']   = trim($user['role']);

        // Suppression de la variable temporaire de validation
        unset($_SESSION['en_cours_validation']);

        // Redirection vers le tableau de bord du patient
        header("Location: patient/dashboard.php");
        exit;
    } else {
        $erreur = "Le code d'activation saisi est incorrect. Veuillez réessayer.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MedConnect - Validation de compte</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        /* Style pour espacer visuellement les chiffres dans l'input */
        .code-input {
            letter-spacing: 8px;
            font-size: 2rem;
            font-weight: bold;
            text-align: center;
        }
    </style>
</head>
<body class="bg-light d-flex align-items-center" style="min-height: 100vh;">

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                
                <div class="text-center mb-4">
                    <h1 class="text-primary fw-bold mb-1"><i class="bi bi-heart-pulse-fill me-2"></i>MedConnect</h1>
                    <p class="text-muted">Sécurisation de votre accès santé</p>
                </div>

                <div class="card border-0 shadow-sm p-4">
                    <div class="card-body text-center">
                        
                        <div class="p-3 bg-primary-subtle text-primary rounded-circle d-inline-block mb-3">
                            <i class="bi bi-shield-lock-fill h1 mb-0 d-block" style="line-height: 1;"></i>
                        </div>
                        
                        <h4 class="card-title fw-bold text-dark mb-2">Vérification de l'adresse email</h4>
                        <p class="text-muted small mb-4">Un code d'activation unique à 6 chiffres a été envoyé dans votre boîte de réception. Veuillez le renseigner pour activer votre compte.</p>

                        <?php if (!empty($erreur)): ?>
                            <div class="alert alert-danger d-flex align-items-center justify-content-center small py-2" role="alert">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                <div><?php echo htmlspecialchars($erreur); ?></div>
                            </div>
                        <?php endif; ?>

                        <form action="verifier_code.php" method="POST">
                            
                            <div class="mb-4">
                                <label for="code_verification" class="form-label fw-semibold text-secondary small">Code de validation</label>
                                <input type="number" 
                                       name="code_verification" 
                                       id="code_verification" 
                                       class="form-control form-control-lg code-input" 
                                       placeholder="000000" 
                                       min="100000" 
                                       max="999999" 
                                       required 
                                       autocomplete="off" 
                                       autofocus>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 py-2 fw-bold shadow-sm mb-3">
                                <i class="bi bi-check-circle-fill me-2"></i>Confirmer et Se connecter
                            </button>
                        </form>

                        <hr class="text-muted">

                        <div class="text-center mt-3">
                            <p class="text-muted small mb-1">Vous n'avez pas reçu l'email ?</p>
                            <span class="text-secondary small">Vérifiez vos courriers indésirables (Spams) ou réessayez l'inscription.</span>
                        </div>
                        
                    </div>
                </div>
                
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>