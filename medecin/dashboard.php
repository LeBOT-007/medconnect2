<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'medecin') {
    header("Location: ../login.php");
    exit;
}

$pdo = getConnexion();

$stmt = $pdo->prepare("SELECT id_medecin FROM medecin WHERE id_utilisateur = ?");
$stmt->execute([$_SESSION['user_id']]);
$medecin = $stmt->fetch();

if (!$medecin) {
    die("Erreur : Profil médecin introuvable. Veuillez contacter l'administrateur.");
}

$id_medecin = $medecin['id_medecin'];

// Actions valider / refuser
if (isset($_GET['action']) && isset($_GET['id_rdv'])) {
    $id_rdv = intval($_GET['id_rdv']);
    $action = $_GET['action'];
    
    if ($action === 'valider') {
        $stmt = $pdo->prepare("UPDATE rendez_vous SET statut = 'confirme' WHERE id_rdv = ? AND id_medecin = ?");
        $stmt->execute([$id_rdv, $id_medecin]);
        $_SESSION['message'] = "Le rendez-vous a été validé avec succès.";
    } elseif ($action === 'refuser') {
        $stmt = $pdo->prepare("UPDATE rendez_vous SET statut = 'refuse' WHERE id_rdv = ? AND id_medecin = ?");
        $stmt->execute([$id_rdv, $id_medecin]);
        $_SESSION['message'] = "Le rendez-vous a été refusé.";
    }
    header("Location: dashboard.php");
    exit;
}

// Récupérer les demandes en attente
$stmt_attente = $pdo->prepare("
    SELECT rv.id_rdv, rv.date_rdv, rv.heure_rdv, rv.motif, u.nom, u.prenom 
    FROM rendez_vous rv
    JOIN patients p ON rv.id_patient = p.id_patient
    JOIN utilisateurs u ON p.id_utilisateur = u.id_utilisateur
    WHERE rv.id_medecin = ? AND rv.statut = 'en_attente'
    ORDER BY rv.date_rdv ASC, rv.heure_rdv ASC
");
$stmt_attente->execute([$id_medecin]);
$rdv_attente = $stmt_attente->fetchAll();

// Récupérer les rendez-vous confirmés
$stmt_valides = $pdo->prepare("
    SELECT rv.date_rdv, rv.heure_rdv, rv.motif, u.nom, u.prenom, p.telephone
    FROM rendez_vous rv
    JOIN patients p ON rv.id_patient = p.id_patient
    JOIN utilisateurs u ON p.id_utilisateur = u.id_utilisateur
    WHERE rv.id_medecin = ? AND rv.statut = 'confirme'
    ORDER BY rv.date_rdv ASC, rv.heure_rdv ASC
");
$stmt_valides->execute([$id_medecin]);
$rdv_valides = $stmt_valides->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MedConnect - Espace Praticien</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">

    <?php include_once '../includes/navbar_medecin.php'; ?>

    <div class="container my-4">
        
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i><?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="mb-4">
            <h2 class="fw-bold text-dark mb-1">Tableau de bord Médical</h2>
            <p class="text-muted mb-0">Gérez vos consultations en attente et visualisez votre planning.</p>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 border-bottom-0">
                <h5 class="card-title fw-bold text-warning mb-0"><i class="bi bi-hourglass-split me-2"></i>Demandes de rendez-vous en attente</h5>
            </div>
            <div class="card-body p-0">
                <?php if (count($rdv_attente) === 0): ?>
                    <div class="text-center p-5 text-muted bg-white">
                        <i class="bi bi-calendar-check display-4 text-success mb-2"></i>
                        <p class="mb-0">Aucune demande en attente. Vous êtes à jour !</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Patient</th>
                                    <th>Date</th>
                                    <th>Heure</th>
                                    <th>Motif</th>
                                    <th class="text-end pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($rdv_attente as $rdv): ?>
                                    <tr>
                                        <td class="fw-bold ps-4"><?php echo htmlspecialchars($rdv['nom'] . ' ' . $rdv['prenom']); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($rdv['date_rdv'])); ?></td>
                                        <td><?php echo date('H:i', strtotime($rdv['heure_rdv'])); ?></td>
                                        <td><span class="text-muted"><?php echo htmlspecialchars($rdv['motif'] ?: 'Non renseigné'); ?></span></td>
                                        <td class="text-end pe-4">
                                            <a href="dashboard.php?action=valider&id_rdv=<?php echo $rdv['id_rdv']; ?>" class="btn btn-sm btn-success me-1 shadow-sm">
                                                <i class="bi bi-check-lg me-1"></i>Accepter
                                            </a>
                                            <a href="dashboard.php?action=refuser&id_rdv=<?php echo $rdv['id_rdv']; ?>" class="btn btn-sm btn-outline-danger shadow-sm" onclick="return confirm('Refuser ce rendez-vous ?');">
                                                <i class="bi bi-xl-lg me-1"></i>Refuser
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
                <h5 class="card-title fw-bold text-success mb-0"><i class="bi bi-calendar3-event me-2"></i>Mes Consultations Confirmées</h5>
            </div>
            <div class="card-body p-0">
                <?php if (count($rdv_valides) === 0): ?>
                    <div class="text-center p-5 text-muted">
                        <i class="bi bi-calendar-x display-4 text-secondary mb-2"></i>
                        <p class="mb-0">Aucun rendez-vous confirmé programmé.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Patient</th>
                                    <th>Téléphone</th>
                                    <th>Date</th>
                                    <th>Heure</th>
                                    <th>Motif</th>
                                    <th class="pe-4">Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($rdv_valides as $rdv): ?>
                                    <tr>
                                        <td class="fw-bold ps-4"><?php echo htmlspecialchars($rdv['nom'] . ' ' . $rdv['prenom']); ?></td>
                                        <td><?php echo htmlspecialchars($rdv['telephone'] ?: '—'); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($rdv['date_rdv'])); ?></td>
                                        <td><?php echo date('H:i', strtotime($rdv['heure_rdv'])); ?></td>
                                        <td><?php echo htmlspecialchars($rdv['motif'] ?: 'Non renseigné'); ?></td>
                                        <td class="pe-4"><span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill"><i class="bi bi-check-circle-fill me-1"></i> Confirmé</span></td>
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