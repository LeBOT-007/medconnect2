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
    $id_spec    = intval($_POST['id_specialite']);

    if (empty($nom) || empty($prenom) || empty($email) || empty($id_spec)) {
        $erreur = "Veuillez remplir tous les champs obligatoires.";
    } else {
        // Début transaction pour assurer l'intégrité
        $pdo->beginTransaction();
        try {
            // Mettre à jour les utilisateurs
            $stmt1 = $pdo->prepare("UPDATE utilisateurs SET nom = ?, prenom = ?, email = ? WHERE id_utilisateur = ?");
            $stmt1->execute([$nom, $prenom, $email, $_SESSION['user_id']]);

            // Mettre à jour les détails du médecin
            $stmt2 = $pdo->prepare("UPDATE medecin SET telephone = ?, adresse = ?, id_specialite = ? WHERE id_utilisateur = ?");
            $stmt2->execute([$telephone, $adresse, $id_spec, $_SESSION['user_id']]);

            $pdo->commit();
            $message = "Votre profil a été mis à jour avec succès.";
            
            // Re-fetch des informations mises à jour
            $stmt = $pdo->prepare("
                SELECT u.nom, u.prenom, u.email, m.id_medecin, m.telephone, m.adresse, m.id_specialite
                FROM utilisateurs u
                JOIN medecin m ON u.id_utilisateur = m.id_utilisateur
                WHERE u.id_utilisateur = ?
            ");
            $stmt->execute([$_SESSION['user_id']]);
            $medecin = $stmt->fetch();
            
        } catch (Exception $e) {
            $pdo->rollBack();
            $erreur = "Une erreur est survenue lors de la mise à jour.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MedConnect - Mon Profil Professionnel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">

    <?php include_once '../includes/navbar_medecin.php'; ?>

    <div class="container my-5">
        <div class="row g-4">
            
            <div class="col-lg-8 order-2 order-lg-1">
                <div class="mb-4">
                    <h2 class="fw-bold text-dark mb-1">Mes Informations Personnelles</h2>
                    <p class="text-muted">Gérez vos données de contact professionnelles visibles par vos patients.</p>
                </div>

                <?php if (!empty($message)): ?>
                    <div class="alert alert-success shadow-sm" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i><?php echo $message; ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($erreur)): ?>
                    <div class="alert alert-danger shadow-sm" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo $erreur; ?>
                    </div>
                <?php endif; ?>

                <div class="card border-0 shadow-sm p-4">
                    <form method="POST" action="profils.php">
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nom *</label>
                                <input type="text" name="nom" class="form-control" required value="<?php echo htmlspecialchars($medecin['nom']); ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Prénom *</label>
                                <input type="text" name="prenom" class="form-control" required value="<?php echo htmlspecialchars($medecin['prenom']); ?>">
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Adresse Email *</label>
                                <input type="email" name="email" class="form-control" required value="<?php echo htmlspecialchars($medecin['email']); ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Spécialité Médicale *</label>
                                <select name="id_specialite" class="form-select" required>
                                    <?php foreach ($specialites as $s): ?>
                                        <option value="<?php echo $s['id_specialite']; ?>" <?php echo ($s['id_specialite'] == $medecin['id_specialite']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($s['nom_specialite']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Téléphone Professionnel</label>
                            <input type="text" name="telephone" class="form-control" value="<?php echo htmlspecialchars($medecin['telephone'] ?: ''); ?>">
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Adresse du Cabinet</label>
                            <textarea name="adresse" class="form-control" rows="2"><?php echo htmlspecialchars($medecin['adresse'] ?: ''); ?></textarea>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="submit" class="btn btn-primary px-4 fw-semibold shadow-sm">
                                <i class="bi bi-save me-2"></i>Enregistrer les modifications
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="col-lg-4 order-1 order-lg-2">
                <div class="card border-0 shadow-sm position-sticky" style="top: 24px;">
                    <div class="card-body text-center p-4">
                        <div class="p-3 bg-primary-subtle text-primary rounded-circle d-inline-block mb-3">
                            <i class="bi bi-person-fill display-5"></i>
                        </div>
                        <h5 class="fw-bold mb-1">Dr. <?php echo htmlspecialchars($medecin['prenom'] . ' ' . $medecin['nom']); ?></h5>
                        <span class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill mb-3">
                            <?php
                            foreach ($specialites as $s) {
                                if ($s['id_specialite'] == $medecin['id_specialite']) {
                                    echo htmlspecialchars($s['nom_specialite']);
                                }
                            }
                            ?>
                        </span>
                        
                        <hr class="text-muted opacity-25">

                        <div class="text-start mt-3">
                            <p class="text-muted small mb-2"><i class="bi bi-envelope me-2 text-primary"></i><?php echo htmlspecialchars($medecin['email']); ?></p>
                            <?php if (!empty($medecin['telephone'])): ?>
                                <p class="text-muted small mb-2"><i class="bi bi-telephone me-2 text-primary"></i><?php echo htmlspecialchars($medecin['telephone']); ?></p>
                            <?php endif; ?>
                            <?php if (!empty($medecin['adresse'])): ?>
                                <p class="text-muted small mb-0"><i class="bi bi-geo-alt me-2 text-primary"></i><?php echo htmlspecialchars($medecin['adresse']); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>