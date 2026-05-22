<?php
session_start();
require_once '../config/database.php';

// Sécurité : Vérification de l'accès du patient
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'patient' || !isset($_SESSION['patient_id'])) {
    header("Location: ../login.php");
    exit;
}

$pdo = getConnexion();
$erreur  = '';

// On utilise le VRAI id_patient stocké en session lors du login
$id_patient_connecte = $_SESSION['patient_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_medecin = $_POST['id_medecin'] ?? null;
    $date_rdv   = $_POST['date_rdv'] ?? null;
    $heure_rdv  = $_POST['heure_rdv'] ?? null;
    $motif      = trim($_POST['motif'] ?? '');

    if (empty($id_medecin) || empty($date_rdv) || empty($heure_rdv)) {
        $erreur = "Tous les champs obligatoires (*) doivent être remplis.";
    } else {
        try {
            // Requête SQL alignée sur la structure réelle (id_patient)
            $sql = "INSERT INTO rendez_vous (id_patient, id_medecin, date_rdv, heure_rdv, motif, statut) 
                    VALUES (?, ?, ?, ?, ?, 'en_attente')";
            $insert = $pdo->prepare($sql);
            $insert->execute([$id_patient_connecte, $id_medecin, $date_rdv, $heure_rdv, $motif]);

            $_SESSION['message'] = "Votre demande de rendez-vous a bien été envoyée !";
            header("Location: dashboard.php");
            exit;
            
        } catch (PDOException $e) {
            $erreur = "Erreur lors de l'enregistrement : " . $e->getMessage();
        }
    }
}

// Récupérer la liste des médecins pour le formulaire
$stmt = $pdo->prepare("
    SELECT m.id_medecin, u.nom, u.prenom, s.nom_specialite AS specialite
    FROM medecin m
    JOIN utilisateurs u ON m.id_utilisateur = u.id_utilisateur
    JOIN specialite s ON m.id_specialite = s.id_specialite
    ORDER BY u.nom ASC
");
$stmt->execute();
$medecins = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MedConnect - Prendre un Rendez-vous</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">

    <?php if(file_exists('../includes/navbar_patient.php')) { include_once '../includes/navbar_patient.php'; } ?>

    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-body p-4 p-md-5">
                        <div class="text-center mb-4">
                            <div class="p-3 bg-primary-subtle text-primary rounded-circle d-inline-block mb-2">
                                <i class="bi bi-calendar-plus display-6"></i>
                            </div>
                            <h2 class="fw-bold text-dark">Prendre un rendez-vous</h2>
                        </div>

                        <?php if (!empty($erreur)): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($erreur) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="prendre_rdv.php">
                            <div class="mb-3">
                                <label for="id_medecin" class="form-label fw-semibold">Sélectionner un praticien *</label>
                                <select name="id_medecin" id="id_medecin" class="form-select form-select-lg" required>
                                    <option value="" selected disabled>-- Choisissez un médecin --</option>
                                    <?php foreach ($medecins as $m): ?>
                                        <option value="<?= $m['id_medecin'] ?>">
                                            Dr. <?= htmlspecialchars($m['nom'] . ' ' . $m['prenom']) ?> (<?= htmlspecialchars($m['specialite']) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-6">
                                    <label for="date_rdv" class="form-label fw-semibold">Date *</label>
                                    <input type="date" name="date_rdv" id="date_rdv" class="form-control form-control-lg" required>
                                </div>
                                <div class="col-6">
                                    <label for="heure_rdv" class="form-label fw-semibold">Heure *</label>
                                    <input type="time" name="heure_rdv" id="heure_rdv" class="form-control form-control-lg" min="08:00" max="18:00" required>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="motif" class="form-label fw-semibold">Motif de consultation</label>
                                <textarea name="motif" id="motif" class="form-control" rows="3" placeholder="Ex: Consultation..."></textarea>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 py-3 fw-bold shadow-sm rounded-3 mb-3">
                                Confirmer la demande
                            </button>
                            <a href="dashboard.php" class="btn btn-outline-secondary w-100 py-2 rounded-3 text-decoration-none text-center">Retour à mon espace</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>document.getElementById('date_rdv').min = new Date().toISOString().split('T')[0];</script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>