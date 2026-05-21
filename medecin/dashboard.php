<?php
session_start();
require_once '../config/database.php';

// Sécurité : Vérification de la session et du rôle de médecin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'medecin') {
    header("Location: ../login.php");
    exit;
}

$pdo = getConnexion();

// 1. Récupérer l'id_medecin correspondant à l'utilisateur connecté
$stmt = $pdo->prepare("SELECT id_medecin FROM medecin WHERE id_utilisateur = ?");
$stmt->execute([$_SESSION['user_id']]);
$medecin = $stmt->fetch();

if (!$medecin) {
    die("Erreur : Profil médecin introuvable. Veuillez contacter l'administrateur.");
}

$id_medecin = $medecin['id_medecin'];

// 2. Traitement des actions (Validation ou Refus de rendez-vous)
if (isset($_GET['action']) && isset($_GET['id_rdv'])) {
    $id_rdv = intval($_GET['id_rdv']);
    $action = $_GET['action'];
    
    if ($action === 'valider') {
        $stmt = $pdo->prepare("UPDATE rendez_vous SET statut = 'valide' WHERE id_rdv = ? AND id_medecin = ?");
        $stmt->execute([$id_rdv, $id_medecin]);
        $_SESSION['message'] = "Le rendez-vous a été validé avec succès.";
    } elseif ($action === 'refuser') {
        $stmt = $pdo->prepare("UPDATE rendez_vous SET statut = 'annule' WHERE id_rdv = ? AND id_medecin = ?");
        $stmt->execute([$id_rdv, $id_medecin]);
        $_SESSION['message'] = "Le rendez-vous a été refusé.";
    }
    
    // Redirection pour éviter de répéter l'action en rafraîchissant la page
    header("Location: dashboard.php");
    exit;
}

// 3. Récupération des rendez-vous en attente
$stmt = $pdo->prepare("
    SELECT rv.*, u.nom, u.prenom, p.telephone
    FROM rendez_vous rv
    JOIN patients p ON rv.id_patient = p.id_patient
    JOIN utilisateurs u ON p.id_utilisateur = u.id_utilisateur
    WHERE rv.id_medecin = ? AND rv.statut = 'en_attente'
    ORDER BY rv.date_rdv ASC, rv.heure_rdv ASC
");
$stmt->execute([$id_medecin]);
$rdv_en_attente = $stmt->fetchAll();

// 4. Récupération de tous les rendez-vous validés à venir
$stmt = $pdo->prepare("
    SELECT rv.*, u.nom, u.prenom, p.telephone
    FROM rendez_vous rv
    JOIN patients p ON rv.id_patient = p.id_patient
    JOIN utilisateurs u ON p.id_utilisateur = u.id_utilisateur
    WHERE rv.id_medecin = ? AND rv.statut = 'valide' AND rv.date_rdv >= CURDATE()
    ORDER BY rv.date_rdv ASC, rv.heure_rdv ASC
");
$stmt->execute([$id_medecin]);
$rdv_valides = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MedConnect - Espace Médecin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#"><i class="bi bi-heart-pulse-fill me-2"></i>MedConnect</a>
            <button class="navbar-expand" type="button">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end">
                <ul class="navbar-nav align-items-center">
                    <li class="nav-item">
                        <span class="nav-link active me-3">Bienvenue, Dr. <?php echo htmlspecialchars($_SESSION['user_nom']); ?></span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn btn-outline-light btn-sm text-white px-3" href="../logout.php"><i class="bi bi-box-arrow-right me-1"></i>Déconnexion</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container my-5">
        
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i><?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="row g-4 mb-5">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm bg-warning text-dark h-100">
                    <div class="card-body d-flex align-items-center justify-content-between p-4">
                        <div>
                            <h6 class="text-uppercase fw-semibold mb-1 opacity-75">Demandes en attente</h6>
                            <h2 class="display-5 fw-bold mb-0"><?php echo count($rdv_en_attente); ?></h2>
                        </div>
                        <i class="bi bi-clock-history display-4 opacity-50"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card border-0 shadow-sm bg-success text-white h-100">
                    <div class="card-body d-flex align-items-center justify-content-between p-4">
                        <div>
                            <h6 class="text-uppercase fw-semibold mb-1 opacity-75">Consultations validées à venir</h6>
                            <h2 class="display-5 fw-bold mb-0"><?php echo count($rdv_valides); ?></h2>
                        </div>
                        <i class="bi bi-calendar-check display-4 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-5">
            <div class="card-header bg-white py-3 border-bottom-0">
                <h5 class="card-title text-warning fw-bold mb-0"><i class="bi bi-exclamation-circle-fill me-2"></i>Nouvelles demandes de rendez-vous</h5>
            </div>
            <div class="card-body p-0">
                <?php if (count($rdv_en_attente) === 0): ?>
                    <div class="text-center p-5 text-muted">
                        <i class="bi bi-folder-symlink display-4"></i>
                        <p class="mt-2 mb-0">Aucune demande de rendez-vous en attente pour le moment.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Patient</th>
                                    <th>Téléphone</th>
                                    <th>Date demandée</th>
                                    <th>Heure</th>
                                    <th>Motif</th>
                                    <th class="text-center pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($rdv_en_attente as $rdv): ?>
                                    <tr>
                                        <td class="fw-bold ps-4"><?php echo htmlspecialchars($rdv['nom'] . ' ' . $rdv['prenom']); ?></td>
                                        <td><?php echo htmlspecialchars($rdv['telephone']); ?></td>
                                        <td><i class="bi bi-calendar3 me-2 text-secondary"></i><?php echo date('d/m/Y', strtotime($rdv['date_rdv'])); ?></td>
                                        <td><i class="bi bi-clock me-2 text-secondary"></i><?php echo date('H:i', strtotime($rdv['heure_rdv'])); ?></td>
                                        <td><span class="text-truncate d-inline-block" style="max-width: 200px;"><?php echo htmlspecialchars($rdv['motif'] ?: 'Non renseigné'); ?></span></td>
                                        <td class="text-center pe-4">
                                            <a href="dashboard.php?action=valider&id_rdv=<?php echo $rdv['id_rdv']; ?>" class="btn btn-sm btn-success me-2" onclick="return confirm('Confirmer la validation de ce rendez-vous ?');">
                                                <i class="bi bi-check-lg"></i> Accepter
                                            </a>
                                            <a href="dashboard.php?action=refuser&id_rdv=<?php echo $rdv['id_rdv']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Êtes-vous sûr de vouloir refuser ce rendez-vous ?');">
                                                <i class="bi bi-x-lg"></i> Refuser
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

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 border-bottom-0">
                <h5 class="card-title text-success fw-bold mb-0"><i class="bi bi-calendar-event-fill me-2"></i>Mon planning des consultations validées</h5>
            </div>
            <div class="card-body p-0">
                <?php if (count($rdv_valides) === 0): ?>
                    <div class="text-center p-5 text-muted">
                        <i class="bi bi-calendar-x display-4"></i>
                        <p class="mt-2 mb-0">Aucun rendez-vous validé à venir dans votre agenda.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Patient</th>
                                    <th>Téléphone</th>
                                    <th>Date consultation</th>
                                    <th>Heure</th>
                                    <th>Motif</th>
                                    <th class="pe-4">Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($rdv_valides as $rdv): ?>
                                    <tr>
                                        <td class="fw-bold ps-4"><?php echo htmlspecialchars($rdv['nom'] . ' ' . $rdv['prenom']); ?></td>
                                        <td><?php echo htmlspecialchars($rdv['telephone']); ?></td>
                                        <td><i class="bi bi-calendar-check me-2 text-success"></i><?php echo date('d/m/Y', strtotime($rdv['date_rdv'])); ?></td>
                                        <td><i class="bi bi-clock me-2 text-success"></i><?php echo date('H:i', strtotime($rdv['heure_rdv'])); ?></td>
                                        <td><span class="text-truncate d-inline-block" style="max-width: 250px;"><?php echo htmlspecialchars($rdv['motif'] ?: 'Non renseigné'); ?></span></td>
                                        <td class="pe-4"><span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill">Confirmé</span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>