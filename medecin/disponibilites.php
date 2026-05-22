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
        // Vérifier si ce créneau existe déjà
        $check = $pdo->prepare("SELECT id_disponibilite FROM disponibilite WHERE id_medecin = ? AND jour = ? AND heure_debut = ?");
        $check->execute([$id_medecin, $jour, $heure_debut]);
        if ($check->fetch()) {
            $erreur = "Ce créneau existe déjà.";
        } else {
            $pdo->prepare("INSERT INTO disponibilite (id_medecin, jour, heure_debut, heure_fin) VALUES (?, ?, ?, ?)")
                ->execute([$id_medecin, $jour, $heure_debut, $heure_fin]);
            $message = "Disponibilité ajoutée avec succès.";
        }
    }
}

// Supprimer une disponibilité
if (isset($_GET['action']) && $_GET['action'] === 'supprimer' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $pdo->prepare("DELETE FROM disponibilite WHERE id_disponibilite = ? AND id_medecin = ?")
        ->execute([$id, $id_medecin]);
    $message = "Disponibilité supprimée.";
}

// Récupérer les disponibilités
$disponibilites = $pdo->prepare("SELECT * FROM disponibilite WHERE id_medecin = ? ORDER BY FIELD(jour, 'Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi','Dimanche'), heure_debut ASC");
$disponibilites->execute([$id_medecin]);
$disponibilites = $disponibilites->fetchAll();

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

<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="dashboard.php"><i class="bi bi-heart-pulse-fill me-2"></i>MedConnect</a>
        <div class="collapse navbar-collapse justify-content-end">
            <ul class="navbar-nav align-items-center">
                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php"><i class="bi bi-speedometer2 me-1"></i>Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="profil.php"><i class="bi bi-person-fill me-1"></i>Mon Profil</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="disponibilite.php"><i class="bi bi-calendar-week me-1"></i>Disponibilités</a>
                </li>
                <li class="nav-item ms-2">
                    <a class="nav-link btn btn-outline-light btn-sm text-white px-3" href="../logout.php"><i class="bi bi-box-arrow-right me-1"></i>Déconnexion</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container my-5">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-dark"><i class="bi bi-calendar-week me-2 text-secondary"></i>Mes Disponibilités</h2>
        <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAjouter">
            <i class="bi bi-plus-circle me-2"></i>Ajouter un créneau
        </button>
    </div>

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

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <?php if (count($disponibilites) === 0): ?>
                <div class="text-center p-5 text-muted">
                    <i class="bi bi-calendar-x display-4"></i>
                    <p class="mt-2 mb-0">Aucune disponibilité enregistrée.</p>
                    <small class="d-block mt-1">Cliquez sur "Ajouter un créneau" pour commencer.</small>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Jour</th>
                                <th>Heure de début</th>
                                <th>Heure de fin</th>
                                <th class="pe-4 text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($disponibilites as $d): ?>
                                <tr>
                                    <td class="fw-bold ps-4">
                                        <span class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill">
                                            <?php echo htmlspecialchars($d['jour']); ?>
                                        </span>
                                    </td>
                                    <td><i class="bi bi-clock me-2 text-success"></i><?php echo date('H:i', strtotime($d['heure_debut'])); ?></td>
                                    <td><i class="bi bi-clock me-2 text-danger"></i><?php echo date('H:i', strtotime($d['heure_fin'])); ?></td>
                                    <td class="pe-4 text-end">
                                        <a href="disponibilite.php?action=supprimer&id=<?php echo $d['id_disponibilite']; ?>"
                                           class="btn btn-sm btn-outline-danger"
                                           onclick="return confirm('Supprimer ce créneau ?');">
                                            <i class="bi bi-trash-fill"></i>
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

<!-- Modal Ajouter -->
<div class="modal fade" id="modalAjouter" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-plus-circle me-2"></i>Ajouter un créneau</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="disponibilite.php">
                <input type="hidden" name="action" value="ajouter">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Jour</label>
                        <select name="jour" class="form-select" required>
                            <option value="">-- Choisir un jour --</option>
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
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary fw-bold"><i class="bi bi-check-lg me-1"></i>Ajouter</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>