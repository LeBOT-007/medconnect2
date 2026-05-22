<?php
session_start();
require_once '../config/database.php';

// Sécurité : Vérification de l'accès du patient
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'patient' || !isset($_SESSION['patient_id'])) {
    header("Location: ../login.php");
    exit;
}

$pdo = getConnexion();
$id_patient_connecte = $_SESSION['patient_id'];

// Action : Annulation d'un rendez-vous
if (isset($_GET['action']) && $_GET['action'] === 'annuler' && isset($_GET['id_rdv'])) {
    $id_rdv = intval($_GET['id_rdv']);
    
    // Filtrage direct via id_patient sécurisé
    $stmt = $pdo->prepare("
        UPDATE rendez_vous 
        SET statut = 'annule'
        WHERE id_rdv = ? AND id_patient = ? AND statut = 'en_attente'
    ");
    $stmt->execute([$id_rdv, $id_patient_connecte]);
    
    $_SESSION['message'] = "Votre rendez-vous a été annulé avec succès.";
    header("Location: dashboard.php");
    exit;
}

// Récupération du prochain rendez-vous actif
$stmt = $pdo->prepare("
    SELECT rv.id_rdv, rv.date_rdv, rv.heure_rdv, rv.statut, rv.motif,
           u.nom AS medecin_nom, u.prenom AS medecin_prenom,
           s.nom_specialite
    FROM rendez_vous rv
    JOIN medecin m ON rv.id_medecin = m.id_medecin
    JOIN utilisateurs u ON m.id_utilisateur = u.id_utilisateur
    JOIN specialite s ON m.id_specialite = s.id_specialite
    WHERE rv.id_patient = ? AND rv.date_rdv >= CURDATE() AND rv.statut != 'annule'
    ORDER BY rv.date_rdv ASC, rv.heure_rdv ASC
    LIMIT 1
");
$stmt->execute([$id_patient_connecte]);
$prochain_rdv = $stmt->fetch(PDO::FETCH_ASSOC);

// Historique complet résumé (3 derniers RDV)
$stmt = $pdo->prepare("
    SELECT rv.id_rdv, rv.date_rdv, rv.heure_rdv, rv.statut, rv.motif,
           u.nom AS medecin_nom, u.prenom AS medecin_prenom,
           s.nom_specialite
    FROM rendez_vous rv
    JOIN medecin m ON rv.id_medecin = m.id_medecin
    JOIN utilisateurs u ON m.id_utilisateur = u.id_utilisateur
    JOIN specialite s ON m.id_specialite = s.id_specialite
    WHERE rv.id_patient = ?
    ORDER BY rv.date_rdv DESC, rv.heure_rdv DESC
    LIMIT 3
");
$stmt->execute([$id_patient_connecte]);
$historique = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MedConnect - Tableau de bord Patient</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">

    <?php if(file_exists('../includes/navbar_patient.php')) { include_once '../includes/navbar_patient.php'; } ?>

    <div class="container my-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="fw-bold text-dark h3">Bonjour, <?= htmlspecialchars($_SESSION['user_prenom'] . ' ' . $_SESSION['user_nom']) ?></h1>
                <p class="text-muted mb-0">Bienvenue sur votre espace santé</p>
            </div>
            <a href="prendre_rdv.php" class="btn btn-primary fw-semibold py-2 shadow-sm">
                <i class="bi bi-calendar-plus me-2"></i>Prendre un rendez-vous
            </a>
        </div>

        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i><?= $_SESSION['message']; unset($_SESSION['message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card border-0 shadow-sm mb-4 rounded-3">
            <div class="card-body p-4">
                <h5 class="fw-bold text-secondary text-uppercase small tracking-wider mb-3">Votre prochain rendez-vous</h5>
                <?php if ($prochain_rdv): ?>
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h4 class="fw-bold text-primary mb-1">Dr. <?= htmlspecialchars($prochain_rdv['medecin_nom'] . ' ' . $prochain_rdv['medecin_prenom']) ?></h4>
                            <p class="text-muted mb-2"><i class="bi bi-tags me-1"></i><?= htmlspecialchars($prochain_rdv['nom_specialite']) ?></p>
                            <div class="d-flex gap-3 text-dark fw-medium small">
                                <span><i class="bi bi-calendar3 me-1 text-primary"></i> Le <?= date('d/m/Y', strtotime($prochain_rdv['date_rdv'])) ?></span>
                                <span><i class="bi bi-clock me-1 text-primary"></i> à <?= date('H\hi', strtotime($prochain_rdv['heure_rdv'])) ?></span>
                            </div>
                        </div>
                        <div class="col-md-4 text-md-end mt-3 mt-md-0">
                            <?php if ($prochain_rdv['statut'] === 'en_attente'): ?>
                                <a href="dashboard.php?action=annuler&id_rdv=<?= $prochain_rdv['id_rdv'] ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('Annuler ce rendez-vous ?');">
                                    <i class="bi bi-x-circle me-1"></i> Annuler la demande
                                </a>
                            <?php else: ?>
                                <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill">Confirmé</span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <p class="text-muted mb-0 py-2">Aucun rendez-vous planifié pour le moment.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-4">
                <h5 class="fw-bold text-secondary text-uppercase small tracking-wider mb-3">Dernières consultations</h5>
                <?php if (!empty($historique)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr class="text-muted small">
                                    <th>Praticien</th>
                                    <th>Date & Heure</th>
                                    <th>Motif</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($historique as $h): ?>
                                    <tr>
                                        <td class="fw-bold text-dark">Dr. <?= htmlspecialchars($h['medecin_nom'] . ' ' . $h['medecin_prenom']) ?></td>
                                        <td><?= date('d/m/Y', strtotime($h['date_rdv'])) ?> à <?= date('H\hi', strtotime($h['heure_rdv'])) ?></td>
                                        <td class="text-muted text-truncate" style="max-width: 200px;"><?= htmlspecialchars($h['motif'] ?: 'Aucun') ?></td>
                                        <td>
                                            <?php if($h['statut'] === 'en_attente'): ?>
                                                <span class="badge bg-warning-subtle text-warning">En attente</span>
                                            <?php elseif($h['statut'] === 'confirme'): ?>
                                                <span class="badge bg-success-subtle text-success">Confirmé</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger-subtle text-danger">Annulé</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted mb-0 py-2">Aucun historique de rendez-vous disponible.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>