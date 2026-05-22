<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'medecin') {
    header("Location: ../login.php");
    exit;
}

$pdo = getConnexion();
$message = '';
$erreur  = '';

// Récupérer les infos du médecin
$stmt = $pdo->prepare("
    SELECT u.nom, u.prenom, u.email, m.id_medecin, m.telephone, m.adresse, m.id_specialite
    FROM utilisateurs u
    JOIN medecin m ON u.id_utilisateur = m.id_utilisateur
    WHERE u.id_utilisateur = ?
");
$stmt->execute([$_SESSION['user_id']]);
$medecin = $stmt->fetch();

if (!$medecin) {
    die("Erreur : Profil médecin introuvable.");
}

// Récupérer les spécialités
$specialites = $pdo->query("SELECT * FROM specialite ORDER BY nom_specialite ASC")->fetchAll();

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom        = trim($_POST['nom']);
    $prenom     = trim($_POST['prenom']);
    $email      = trim($_POST['email']);
    $telephone  = trim($_POST['telephone']);
    $adresse    = trim($_POST['adresse']);
    $id_specialite = intval($_POST['id_specialite']);

    // Vérifier si email déjà utilisé par quelqu'un d'autre
    $check = $pdo->prepare("SELECT id_utilisateur FROM utilisateurs WHERE email = ? AND id_utilisateur != ?");
    $check->execute([$email, $_SESSION['user_id']]);

    if ($check->fetch()) {
        $erreur = "Cet email est déjà utilisé par un autre compte.";
    } else {
        // Mettre à jour la table utilisateurs
        $pdo->prepare("UPDATE utilisateurs SET nom = ?, prenom = ?, email = ? WHERE id_utilisateur = ?")
            ->execute([$nom, $prenom, $email, $_SESSION['user_id']]);

        // Mettre à jour la table medecin
        $pdo->prepare("UPDATE medecin SET telephone = ?, adresse = ?, id_specialite = ? WHERE id_utilisateur = ?")
            ->execute([$telephone, $adresse, $id_specialite, $_SESSION['user_id']]);

        // Mettre à jour la session
        $_SESSION['user_nom']    = $nom;
        $_SESSION['user_prenom'] = $prenom;

        $message = "Votre profil a été mis à jour avec succès.";

        // Recharger les données
        $stmt->execute([$_SESSION['user_id']]);
        $medecin = $stmt->fetch();
    }

    // Changer le mot de passe
    if (!empty($_POST['nouveau_mdp'])) {
        $ancien_mdp   = $_POST['ancien_mdp'];
        $nouveau_mdp  = $_POST['nouveau_mdp'];
        $confirm_mdp  = $_POST['confirm_mdp'];

        // Vérifier l'ancien mot de passe
        $stmt2 = $pdo->prepare("SELECT mot_de_passe FROM utilisateurs WHERE id_utilisateur = ?");
        $stmt2->execute([$_SESSION['user_id']]);
        $user = $stmt2->fetch();

        if (!password_verify($ancien_mdp, $user['mot_de_passe'])) {
            $erreur = "L'ancien mot de passe est incorrect.";
        } elseif ($nouveau_mdp !== $confirm_mdp) {
            $erreur = "Les nouveaux mots de passe ne correspondent pas.";
        } else {
            $hash = password_hash($nouveau_mdp, PASSWORD_BCRYPT);
            $pdo->prepare("UPDATE utilisateurs SET mot_de_passe = ? WHERE id_utilisateur = ?")
                ->execute([$hash, $_SESSION['user_id']]);
            $message = "Mot de passe modifié avec succès.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MedConnect - Mon Profil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="dashboard.php"><i class="bi bi-heart-pulse-fill me-2"></i>MedConnect</a>
        <div class="collapse navbar-collapse justify-content-end">
            <ul class="navbar-nav align-items-center">
                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php"><i class="bi bi-speedometer2 me-1"></i>Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="profil.php"><i class="bi bi-person-fill me-1"></i>Mon Profil</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="disponibilite.php"><i class="bi bi-calendar-week me-1"></i>Disponibilités</a>
                </li>
                <li class="nav-item ms-2">
                    <a class="nav-link btn btn-outline-light btn-sm text-white px-3" href="../logout.php"><i class="bi bi-box-arrow-right me-1"></i>Déconnexion</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container my-5">

    <?php if ($message): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm">
            <i class="bi bi-check-circle-fill me-2"></i><?php echo htmlspecialchars($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if ($erreur): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm">
            <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo htmlspecialchars($erreur); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row g-4">

        <!-- Infos du profil -->
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="fw-bold mb-0"><i class="bi bi-person-fill me-2 text-primary"></i>Informations du profil</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="profil.php">
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nom</label>
                                <input type="text" name="nom" class="form-control" required
                                    value="<?php echo htmlspecialchars($medecin['nom']); ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Prénom</label>
                                <input type="text" name="prenom" class="form-control" required
                                    value="<?php echo htmlspecialchars($medecin['prenom']); ?>">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Adresse Email</label>
                            <input type="email" name="email" class="form-control" required
                                value="<?php echo htmlspecialchars($medecin['email']); ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Spécialité</label>
                            <select name="id_specialite" class="form-select" required>
                                <?php foreach ($specialites as $s): ?>
                                    <option value="<?php echo $s['id_specialite']; ?>"
                                        <?php echo $s['id_specialite'] == $medecin['id_specialite'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($s['nom_specialite']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Téléphone</label>
                            <input type="tel" name="telephone" class="form-control"
                                value="<?php echo htmlspecialchars($medecin['telephone'] ?? ''); ?>">
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Adresse du cabinet</label>
                            <textarea name="adresse" class="form-control" rows="2"><?php echo htmlspecialchars($medecin['adresse'] ?? ''); ?></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary fw-bold px-4">
                            <i class="bi bi-check-lg me-2"></i>Enregistrer les modifications
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Changer mot de passe -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="fw-bold mb-0"><i class="bi bi-lock-fill me-2 text-warning"></i>Changer le mot de passe</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="profil.php">
                        <input type="hidden" name="nom" value="<?php echo htmlspecialchars($medecin['nom']); ?>">
                        <input type="hidden" name="prenom" value="<?php echo htmlspecialchars($medecin['prenom']); ?>">
                        <input type="hidden" name="email" value="<?php echo htmlspecialchars($medecin['email']); ?>">
                        <input type="hidden" name="telephone" value="<?php echo htmlspecialchars($medecin['telephone'] ?? ''); ?>">
                        <input type="hidden" name="adresse" value="<?php echo htmlspecialchars($medecin['adresse'] ?? ''); ?>">
                        <input type="hidden" name="id_specialite" value="<?php echo $medecin['id_specialite']; ?>">

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Ancien mot de passe</label>
                            <input type="password" name="ancien_mdp" class="form-control" placeholder="••••••••">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nouveau mot de passe</label>
                            <input type="password" name="nouveau_mdp" class="form-control" placeholder="••••••••">
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Confirmer le mot de passe</label>
                            <input type="password" name="confirm_mdp" class="form-control" placeholder="••••••••">
                        </div>
                        <button type="submit" class="btn btn-warning fw-bold w-100 text-dark">
                            <i class="bi bi-shield-lock me-2"></i>Modifier le mot de passe
                        </button>
                    </form>
                </div>
            </div>

            <!-- Carte résumé -->
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-body text-center p-4">
                    <div class="p-3 bg-primary-subtle text-primary rounded-circle d-inline-block mb-3">
                        <i class="bi bi-person-md display-5"></i>
                    </div>
                    <h5 class="fw-bold">Dr. <?php echo htmlspecialchars($medecin['prenom'] . ' ' . $medecin['nom']); ?></h5>
                    <span class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill">
                        <?php
                        foreach ($specialites as $s) {
                            if ($s['id_specialite'] == $medecin['id_specialite']) {
                                echo htmlspecialchars($s['nom_specialite']);
                            }
                        }
                        ?>
                    </span>
                    <p class="text-muted small mt-3 mb-0"><i class="bi bi-envelope me-1"></i><?php echo htmlspecialchars($medecin['email']); ?></p>
                    <?php if (!empty($medecin['telephone'])): ?>
                        <p class="text-muted small mb-0"><i class="bi bi-telephone me-1"></i><?php echo htmlspecialchars($medecin['telephone']); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>