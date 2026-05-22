<?php

session_start();
require_once 'config/database.php';
require_once 'config/mail.php'; // Inclusion de notre nouveau module mail

$erreur = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo    = getConnexion();
    $nom    = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $email  = trim($_POST['email']);
    $mdp    = $_POST['mot_de_passe'];
    
    // Sécurité stricte : rôle verrouillé sur patient
    $role   = 'patient'; 

    // 1. Vérifier si l'adresse email existe déjà
    $stmt = $pdo->prepare("SELECT id_utilisateur FROM utilisateurs WHERE email = ?");
    $stmt->execute([$email]);
    
    if ($stmt->fetch()) {
        $erreur = "Cette adresse email est déjà associée à un compte.";
    } else {
        // 2. Hachage sécurisé du mot de passe
        $hash = password_hash($mdp, PASSWORD_BCRYPT);
        
        // 3. Génération d'une suite de chiffres aléatoires (code à 6 chiffres)
        $code_verification = rand(100000, 999999);

        // 4. Insertion dans la table utilisateurs (statut non validé par défaut)
        $stmt = $pdo->prepare("INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, role, code_verification, est_valide) VALUES (?, ?, ?, ?, 'patient', ?, 0)");
        $stmt->execute([$nom, $prenom, $email, $hash, $code_verification]);
        $id = $pdo->lastInsertId();

        // 5. Insertion des coordonnées obligatoires dans la table patients
        $telephone = trim($_POST['telephone']);
        $date_naissance = $_POST['date_naissance'];
        $adresse = trim($_POST['adresse']);
        
        $stmt2 = $pdo->prepare("INSERT INTO patients (id_utilisateur, telephone, date_naissance, adresse) VALUES (?, ?, ?, ?)");
        $stmt2->execute([$id, $telephone, $date_naissance, $adresse]);

        // 6. Expédition réelle de l'email via PHPMailer
        envoyerEmailVerification($email, $prenom, $code_verification);

        // 7. On mémorise temporairement l'ID de l'utilisateur pour la page de vérification
        $_SESSION['en_cours_validation'] = $id;

        // Redirection instantanée vers l'interface de saisie du code
        header("Location: verifier_code.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MedConnect - Création de compte</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body class="bg-light py-5">

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-7">
                
                <div class="text-center mb-4">
                    <h1 class="text-primary fw-bold mb-1"><i class="bi bi-heart-pulse-fill me-2"></i>MedConnect</h1>
                    <p class="text-muted">Créez votre compte en quelques instants</p>
                </div>

                <div class="card border-0 shadow-sm p-4">
                    <div class="card-body">
                        <h4 class="card-title fw-bold text-dark mb-4 text-center">Formulaire d'inscription</h4>

                        <?php if (!empty($erreur)): ?>
                            <div class="alert alert-danger d-flex align-items-center" role="alert">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                <div><?php echo htmlspecialchars($erreur); ?></div>
                            </div>
                        <?php endif; ?>

                        <form action="register.php" method="POST">
                            
                            <h5 class="text-secondary fw-bold mb-3 border-bottom pb-2"><i class="bi bi-person-fill me-2"></i>Identité</h5>
                            
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label for="nom" class="form-label fw-semibold">Nom</label>
                                    <input type="text" name="nom" id="nom" class="form-control" placeholder="Ex: Ndong" required value="<?php echo isset($_POST['nom']) ? htmlspecialchars($_POST['nom']) : ''; ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="prenom" class="form-label fw-semibold">Prénom</label>
                                    <input type="text" name="prenom" id="prenom" class="form-control" placeholder="Ex: Hestia" required value="<?php echo isset($_POST['prenom']) ? htmlspecialchars($_POST['prenom']) : ''; ?>">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label fw-semibold">Adresse Email</label>
                                <input type="email" name="email" id="email" class="form-control" placeholder="nom@exemple.com" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                            </div>

                            <div class="mb-3">
                                <label for="mot_de_passe" class="form-label fw-semibold">Mot de passe</label>
                                <input type="password" name="mot_de_passe" id="mot_de_passe" class="form-control" placeholder="••••••••" required>
                            </div>

                            <h5 class="text-secondary fw-bold mt-4 mb-3 border-bottom pb-2"><i class="bi bi-file-medical-fill me-2"></i>Dossier et Contacts</h5>
                            
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label for="telephone" class="form-label fw-semibold">Numéro de Téléphone</label>
                                    <input type="tel" name="telephone" id="telephone" class="form-control" placeholder="Ex: 066XXXXXX" required value="<?php echo isset($_POST['telephone']) ? htmlspecialchars($_POST['telephone']) : ''; ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="date_naissance" class="form-label fw-semibold">Date de Naissance</label>
                                    <input type="date" name="date_naissance" id="date_naissance" class="form-control" required value="<?php echo isset($_POST['date_naissance']) ? htmlspecialchars($_POST['date_naissance']) : ''; ?>">
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="adresse" class="form-label fw-semibold">Adresse complète de résidence</label>
                                <textarea name="adresse" id="adresse" class="form-control" rows="2" placeholder="Quartier, Ville..." required><?php echo isset($_POST['adresse']) ? htmlspecialchars($_POST['adresse']) : ''; ?></textarea>
                            </div>

                            <button type="submit" class="btn btn-success w-100 py-2 fw-bold shadow-sm mt-3">
                                <i class="bi bi-envelope-plus-fill me-2"></i>Créer mon compte
                            </button>
                        </form>

                        <div class="text-center mt-4">
                            <p class="text-muted mb-0">Déjà inscrit ?</p>
                            <a href="login.php" class="text-primary fw-semibold text-decoration-none">Se connecter</a>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>