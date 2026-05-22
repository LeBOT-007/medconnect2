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

// Récupérer id_medecin
$stmt = $pdo->prepare("SELECT id_medecin FROM medecin WHERE id_utilisateur = ?");
$stmt->execute([$_SESSION['user_id']]);
$medecin = $stmt->fetch();

if (!$medecin) {
    die("Erreur : Profil médecin introuvable.");
}
$id_medecin = $medecin['id_medecin'];

// Ajouter une disponibilité
if (isset($_POST['action']) && $_POST['action'] === 'ajouter') {
    $jour        = $_POST['jour'];
    $heure_debut = $_POST['heure_debut'];
    $heure_fin   = $_POST['heure_fin'];

    if ($heure_debut >= $heure_fin) {
        $erreur = "L'heure de fin doit être après l'heure de début.";
    } else {
        $check = $pdo->prepare("SELECT id_disponibilite FROM disponibilite WHERE id_medecin = ? AND jour = ? AND heure_debut = ?");
        $check->execute([$id_medecin, $jour, $heure_debut]);
        
        if ($check->fetch()) {
            $erreur = "Ce créneau horaire existe déjà pour ce jour.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO disponibilite (id_medecin, jour, heure_debut, heure_fin) VALUES (?, ?, ?, ?)");
            $stmt->execute([$id_medecin, $jour, $heure_debut, $heure_fin]);
            $message = "Disponibilité ajoutée avec succès.";
        }
    }
}

// Supprimer une disponibilité
if (isset($_GET['action']) && $_GET['action'] === 'supprimer' && isset($_GET['id'])) {
    $id_disp = intval($_GET['id']);
    $stmt = $pdo->prepare("DELETE FROM disponibilite WHERE id_disponibilite = ? AND id_medecin = ?");
    $stmt->execute([$id_disp, $id_medecin]);
    $message = "Créneau de disponibilité supprimé.";
}

// Récupérer la liste des disponibilités du médecin
$stmt = $pdo->prepare("SELECT * FROM disponibilite WHERE id_medecin = ? ORDER BY FIELD(jour, 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'), heure_debut ASC");
$stmt->execute([$id_medecin]);
$dispos = $stmt->fetchAll();

$jours = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MedConnect - Mes Disponibilités</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">

    <?php include_once '../includes/navbar_medecin.php'; ?>

    <div class="container my-4">
        
        <div class="row align-items-center mb-4">
            <div class="col-sm-8">
                <h2 class="fw-bold text-dark mb-1">Mes Horaires de Consultation</h2>
                <p class="text-muted mb-0">Configurez vos créneaux réguliers d'ouverture aux patients.</p>
            </div>
            <div class="col-sm-4 text-sm-end mt-3 mt-sm-0">
                <button type="button" class="btn btn-primary shadow-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#modalAjouter">
                    <i class="bi bi-plus-lg me-1"></i> Ajouter un créneau
                </button>
            </div>
        </div>

        <?php if (!empty($message)): ?>
            <div class="alert alert-success shadow-sm alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i><?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (!empty($erreur)): ?>
            <div class="alert alert-danger shadow-sm alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo $erreur; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <?php if (count($dispos) === 0): ?>
                    <div class="text-center p-5 text-muted">
                        <i class="bi bi-calendar-plus display-4 text-secondary mb-2"></i>
                        <p class="mb-0">Vous n'avez configuré aucune disponibilité pour le moment.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Jour de la semaine</th>
                                    <th>Heure de Début</th>
                                    <th>Heure de Fin</th>
                                    <th class="text-end pe-4">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($dispos as $d): ?>
                                    <tr>
                                        <td class="fw-bold ps-4 text-primary"><i class="bi bi-calendar-check me-2"></i><?php echo htmlspecialchars($d['jour']); ?></td>
                                        <td><?php echo date('H:i', strtotime($d['heure_debut'])); ?></td>
                                        <td><?php echo date('H:i', strtotime($d['heure_fin'])); ?></td>
                                        <td class="text-end pe-4">
                                            <a href="disponibilites.php?action=supprimer&id=<?php echo $d['id_disponibilite']; ?>" 
                                               class="btn btn-sm btn-outline-danger shadow-sm"
                                               onclick="return confirm('Supprimer ce créneau horaire ?');">
                                                <i class="bi bi-trash3-fill"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalAjouter" data-bs-backdrop="static" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold"><i class="bi bi-clock me-2"></i>Nouveau créneau horaire</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="disponibilites.php">
                    <input type="hidden" name="action" value="ajouter">
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Jour de la semaine</label>
                            <select name="jour" class="form-select form-select-lg" required>
                                <option value="" selected disabled>-- Choisir un jour --</option>
                                <?php foreach ($jours as $j): ?>
                                    <option value="<?php echo $j; ?>"><?php echo $j; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="row g-3">
                            <div class="col-6">
                                <label class="form-label fw-semibold">Heure de début</label>
                                <input type="time" name="heure_debut" class="form-control" min="07:00" max="18:00" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold">Heure de fin</label>
                                <input type="time" name="heure_fin" class="form-control" min="07:00" max="19:00" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary shadow-sm" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary shadow-sm fw-bold"><i class="bi bi-check-lg me-1"></i>Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>