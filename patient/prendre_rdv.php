<?php
session_start();
require_once '../config/database.php';

// Sécurité
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'patient') {
    header("Location: ../login.php");
    exit;
}

$pdo = getConnexion();

// Récupérer l'id_patient
$stmt = $pdo->prepare("SELECT id_patient FROM patients WHERE id_utilisateur = ?");
$stmt->execute([$_SESSION['user_id']]);
$patient = $stmt->fetch();
$id_patient = $patient['id_patient'];

// Récupérer la liste des médecins
$stmt = $pdo->prepare("
    SELECT m.id_medecin, u.nom, u.prenom, s.nom_specialite AS specialite
    FROM medecin m
    JOIN utilisateurs u ON m.id_utilisateur = u.id_utilisateur
    JOIN specialite s ON m.id_specialite = s.id_specialite
    ORDER BY u.nom
");
$stmt->execute();
$medecins = $stmt->fetchAll();

$message = '';
$erreur  = '';

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_medecin = $_POST['id_medecin'];
    $date_rdv   = $_POST['date_rdv'];
    $heure_rdv  = $_POST['heure_rdv'];
    $motif      = trim($_POST['motif']);

    if (empty($id_medecin) || empty($date_rdv) || empty($heure_rdv)) {
        $erreur = "Veuillez remplir tous les champs obligatoires.";
    } else {
        // Insertion de la demande de rendez-vous
        $stmt = $pdo->prepare("
            INSERT INTO rendez_vous (id_patient, id_medecin, date_rdv, heure_rdv, motif, statut)
            VALUES (?, ?, ?, ?, ?, 'en_attente')
        ");
        if ($stmt->execute([$id_patient, $id_medecin, $date_rdv, $heure_rdv, $motif])) {
            $_SESSION['message'] = "Votre demande de rendez-vous a bien été enregistrée.";
            header("Location: dashboard.php");
            exit;
        } else {
            $erreur = "Une erreur est survenue lors de l'enregistrement de votre rendez-vous.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MedConnect - Prendre un rendez-vous</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">

    <?php include_once '../includes/navbar_patient.php'; ?>

    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                
                <div class="mb-4">
                    <a href="dashboard.php" class="text-decoration-none small fw-semibold"><i class="bi bi-arrow-left me-1"></i> Retour à mon espace</a>
                    <h2 class="fw-bold text-dark mt-2">Prendre un rendez-vous</h2>
                    <p class="text-muted">Remplissez le formulaire ci-dessous pour planifier votre consultation avec un praticien.</p>
                </div>

                <?php if (!empty($erreur)): ?>
                    <div class="alert alert-danger shadow-sm" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo $erreur; ?>
                    </div>
                <?php endif; ?>

                <div class="card border-0 shadow-sm p-4">
                    <form method=\"POST\" action=\"prendre_rdv.php\">
                        
                        <div class="mb-4">
                            <label for="id_medecin" class="form-label fw-semibold">Choisir un médecin *</label>
                            <select name="id_medecin" id="id_medecin" class="form-select form-select-lg" required>
                                <option value="" selected disabled>-- Sélectionnez un praticien --</option>
                                <?php foreach ($medecins as $medecin): ?>
                                    <option value="<?php echo $medecin['id_medecin']; ?>" <?php echo (isset($_POST['id_medecin']) && $_POST['id_medecin'] == $medecin['id_medecin']) ? 'selected' : ''; ?>>
                                        Dr. <?php echo htmlspecialchars($medecin['nom'] . ' ' . $medecin['prenom'] . ' (' . $medecin['specialite'] . ')'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="date_rdv" class="form-label fw-semibold">Date souhaitée *</label>
                                <input type="date" name="date_rdv" id="date_rdv" class="form-control form-control-lg" required
                                       value="<?php echo htmlspecialchars($_POST['date_rdv'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="heure_rdv" class="form-label fw-semibold">Heure souhaitée *</label>
                                <input type="time" name="heure_rdv" id="heure_rdv" class="form-control form-control-lg" required
                                       min="08:00" max="18:00"
                                       value="<?php echo htmlspecialchars($_POST['heure_rdv'] ?? ''); ?>">
                                <div class="form-text">Heures d'ouverture : 08:00 à 18:00.</div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="motif" class="form-label fw-semibold">Motif de la consultation</label>
                            <textarea name="motif" id="motif" rows="4" class="form-control" placeholder="Ex: Consultation de contrôle, symptômes grippaux, renouvellement d'ordonnance..."><?php echo htmlspecialchars($_POST['motif'] ?? ''); ?></textarea>
                        </div>

                        <div class="d-grid mt-2">
                            <button type="submit" class="btn btn-primary btn-lg fw-semibold shadow-sm">
                                <i class="bi bi-calendar-check me-2"></i>Envoyer la demande de rendez-vous
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>

    <script>
        // Sécurité front-end basique : interdire le choix de dates passées
        document.getElementById('date_rdv').min = new Date().toISOString().split("T")[0];
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>