<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$pdo = getConnexion();
$message = '';
$erreur  = '';

// Ajouter une spécialité
if (isset($_POST['action']) && $_POST['action'] === 'ajouter') {
    $nom = trim($_POST['nom_specialite']);
    if (empty($nom)) {
        $erreur = "Le nom de la spécialité est obligatoire.";
    } else {
        $check = $pdo->prepare("SELECT id_specialite FROM specialite WHERE nom_specialite = ?");
        $check->execute([$nom]);
        if ($check->fetch()) {
            $erreur = "Cette spécialité existe déjà.";
        } else {
            $pdo->prepare("INSERT INTO specialite (nom_specialite) VALUES (?)")->execute([$nom]);
            $message = "Spécialité ajoutée avec succès.";
        }
    }
}

// Supprimer une spécialité
if (isset($_GET['action']) && $_GET['action'] === 'supprimer' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $check = $pdo->prepare("SELECT COUNT(*) FROM medecin WHERE id_specialite = ?");
    $check->execute([$id]);
    if ($check->fetchColumn() > 0) {
        $erreur = "Impossible de supprimer : des médecins utilisent cette spécialité.";
    } else {
        $pdo->prepare("DELETE FROM specialite WHERE id_specialite = ?")->execute([$id]);
        $message = "Spécialité supprimée avec succès.";
    }
}

// Modifier une spécialité
if (isset($_POST['action']) && $_POST['action'] === 'modifier') {
    $id  = intval($_POST['id_specialite']);
    $nom = trim($_POST['nom_specialite']);
    if (empty($nom)) {
        $erreur = "Le nom est obligatoire.";
    } else {
        $pdo->prepare("UPDATE specialite SET nom_specialite = ? WHERE id_specialite = ?")->execute([$nom, $id]);
        $message = "Spécialité modifiée avec succès.";
    }
}

$specialites = $pdo->query("
    SELECT s.id_specialite, s.nom_specialite, COUNT(m.id_medecin) AS nb_medecins
    FROM specialite s
    LEFT JOIN medecin m ON s.id_specialite = m.id_specialite
    GROUP BY s.id_specialite, s.nom_specialite
    ORDER BY s.nom_specialite ASC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MedConnect - Gestion des Spécialités</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold text-info" href="dashboard.php"><i class="bi bi-shield-lock-fill me-2"></i>MedConnect Admin</a>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="dashboard.php"><i class="bi bi-speedometer2 me-1"></i>Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="medecin.php"><i class="bi bi-person-heart me-1"></i>Médecins</a></li>
                <li class="nav-item"><a class="nav-link active" href="specialite.php"><i class="bi bi-journal-medical me-1"></i>Spécialités</a></li>
                <li class="nav-item"><a class="nav-link" href="utilisateurs.php"><i class="bi bi-people me-1"></i>Utilisateurs</a></li>
            </ul>
            <ul class="navbar-nav align-items-center">
                <li class="nav-item"><span class="nav-link text-white-50 me-3">Admin: <?php echo htmlspecialchars($_SESSION['user_nom']); ?></span></li>
                <li class="nav-item"><a class="nav-link btn btn-outline-danger btn-sm text-white px-3" href="../logout.php"><i class="bi bi-box-arrow-right me-1"></i>Déconnexion</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-dark"><i class="bi bi-journal-medical me-2 text-secondary"></i>Gestion des Spécialités</h2>
        <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAjouter">
            <i class="bi bi-plus-circle me-2"></i>Nouvelle spécialité
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
            <?php if (count($specialites) === 0): ?>
                <div class="text-center p-5 text-muted">
                    <i class="bi bi-journal-x display-4"></i>
                    <p class="mt-2 mb-0">Aucune spécialité enregistrée.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">ID</th>
                                <th>Nom de la spécialité</th>
                                <th class="text-center">Nombre de médecins</th>
                                <th class="pe-4 text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($specialites as $s): ?>
                                <tr>
                                    <td class="text-muted ps-4">#<?php echo $s['id_specialite']; ?></td>
                                    <td class="fw-bold"><?php echo htmlspecialchars($s['nom_specialite']); ?></td>
                                    <td class="text-center">
                                        <span class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill"><?php echo $s['nb_medecins']; ?> médecin(s)</span>
                                    </td>
                                    <td class="pe-4 text-end">
                                        <button class="btn btn-sm btn-outline-warning me-1"
                                            data-bs-toggle="modal" data-bs-target="#modalModifier"
                                            data-id="<?php echo $s['id_specialite']; ?>"
                                            data-nom="<?php echo htmlspecialchars($s['nom_specialite']); ?>">
                                            <i class="bi bi-pencil-fill"></i>
                                        </button>
                                        <a href="specialite.php?action=supprimer&id=<?php echo $s['id_specialite']; ?>"
                                           class="btn btn-sm btn-outline-danger"
                                           onclick="return confirm('Supprimer cette spécialité ?');">
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
                <h5 class="modal-title fw-bold"><i class="bi bi-plus-circle me-2"></i>Nouvelle Spécialité</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="specialite.php">
                <input type="hidden" name="action" value="ajouter">
                <div class="modal-body">
                    <label class="form-label fw-semibold">Nom de la spécialité</label>
                    <input type="text" name="nom_specialite" class="form-control" placeholder="Ex: Cardiologie" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary fw-bold"><i class="bi bi-check-lg me-1"></i>Ajouter</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Modifier -->
<div class="modal fade" id="modalModifier" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title fw-bold"><i class="bi bi-pencil-fill me-2"></i>Modifier la Spécialité</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="specialite.php">
                <input type="hidden" name="action" value="modifier">
                <input type="hidden" name="id_specialite" id="modifier_id">
                <div class="modal-body">
                    <label class="form-label fw-semibold">Nom de la spécialité</label>
                    <input type="text" name="nom_specialite" id="modifier_nom" class="form-control" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-warning fw-bold text-dark"><i class="bi bi-check-lg me-1"></i>Modifier</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.getElementById('modalModifier').addEventListener('show.bs.modal', function (e) {
        const btn = e.relatedTarget;
        document.getElementById('modifier_id').value  = btn.getAttribute('data-id');
        document.getElementById('modifier_nom').value = btn.getAttribute('data-nom');
    });
</script>
</body>
</html>