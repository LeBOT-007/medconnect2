<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'patient') {
    header("Location: ../login.php");
    exit;
}

$pdo = getConnexion();

// Action : Annulation d'un rendez-vous
if (isset($_GET['action']) && $_GET['action'] === 'annuler' && isset($_GET['id_rdv'])) {
    $id_rdv = intval($_GET['id_rdv']);
    
    $stmt = $pdo->prepare("
        UPDATE rendez_vous rv
        JOIN patients p ON rv.id_patient = p.id_patient
        SET rv.statut = 'annule'
        WHERE rv.id_rendez_vous = ? AND p.id_utilisateur = ? AND rv.statut = 'en_attente'
    ");
    $stmt->execute([$id_rdv, $_SESSION['user_id']]);
    
    $_SESSION['message'] = "Votre rendez-vous a été annulé avec succès.";
    header("Location: dashboard.php");
    exit;
}

// Prochain rendez-vous
$stmt = $pdo->prepare("
    SELECT rv.id_rendez_vous, rv.date_rdv, rv.heure_rdv, rv.statut, rv.motif,
           u.nom AS medecin_nom, u.prenom AS medecin_prenom,
           s.nom_specialite
    FROM rendez_vous rv
    JOIN patients p ON rv.id_patient = p.id_patient
    JOIN medecin m ON rv.id_medecin = m.id_medecin
    JOIN utilisateurs u ON m.id_utilisateur = u.id_utilisateur
    JOIN specialite s ON m.id_specialite = s.id_specialite
    WHERE p.id_utilisateur = ? AND rv.date_rdv >= CURDATE() AND rv.statut != 'annule'
    ORDER BY rv.date_rdv ASC, rv.heure_rdv ASC
    LIMIT 1
");
$stmt->execute([$_SESSION['user_id']]);
$prochain_rdv = $stmt->fetch();

// Historique complet
$stmt = $pdo->prepare("
    SELECT rv.id_rendez_vous, rv.date_rdv, rv.heure_rdv, rv.statut, rv.motif,
           u.nom AS medecin_nom, u.prenom AS medecin_prenom,
           s.nom_specialite
    FROM rendez_vous rv
    JOIN patients p ON rv.id_patient = p.id_patient
    JOIN medecin m ON rv.id_medecin = m.id_medecin
    JOIN utilisateurs u ON m.id_utilisateur = u.id_utilisateur
    JOIN specialite s ON m.id_specialite = s.id_specialite
    WHERE p.id_utilisateur = ?
    ORDER BY rv.date_rdv DESC, rv.heure_rdv DESC
");
$stmt->execute([$_SESSION['user_id']]);
$historique = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MedConnect - Mon Espace Santé</title>
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
                        <span class="nav-link active me-3">Bonjour, <?php echo htmlspecialchars($_SESSION['user_prenom'] . ' ' . $_SESSION['user_nom']); ?></span>
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
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row align-items-center mb-5 g-4">
            <div class="col-md-8">
                <h2 class="fw-bold text-dark mb-1">Mon Espace Santé</h2>
                <p class="text-muted mb-0">Consultez vos rendez-vous médicaux et planifiez vos prochaines consultations en quelques clics.</p>
            </div>
            <div class="col-md-4 text-md-end">
                <a href="prendre_rdv.php" class="btn btn-primary btn-lg shadow-sm fw-semibold">
                    <i class="bi bi-calendar-plus me-2"></i>Prendre un rendez-vous
                </a>
            </div>
        </div>

        <!-- Prochain RDV -->
        <div class="card border-0 shadow-sm mb-5 overflow-hidden">
            <div class="card-header bg-white py-3 border-bottom-0">
                <h5 class="card-title fw-bold text-dark mb-0"><i class="bi bi-clock-fill me-2 text-primary"></i>Mon prochain rendez-vous</h5>
            </div>
            <div class="card-body p-0">
                <?php if (!$prochain_rdv): ?>
                    <div class="text-center p-5 text-muted bg-white">
                        <i class="bi bi-calendar-x display-4 text-secondary mb-2"></i>
                        <p class="mb-0">Vous n'avez aucun rendez-vous à venir.</p>
                        <small class="d-block mt-1 text-muted">Cliquez sur le bouton ci-dessus pour réserver une consultation.</small>
                    </div>
                <?php else: ?>
                    <div class="p-4 bg-white border-top border-light">
                        <div class="row align-items-center g-3">
                            <div class="col-md-4">
                                <div class="d-flex align-items-center">
                                    <div class="p-3 bg-primary-subtle text-primary rounded-circle me-3">
                                        <i class="bi bi-person-md display-6"></i>
                                    </div>
                                    <div>
                                        <h5 class="mb-1 fw-bold">Dr. <?php echo htmlspecialchars($prochain_rdv['medecin_nom'] . ' ' . $prochain_rdv['medecin_prenom']); ?></h5>
                                        <span class="badge bg-primary-subtle text-primary rounded-pill px-3"><?php echo htmlspecialchars($prochain_rdv['nom_specialite']); ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-muted small"><i class="bi bi-calendar3 me-2"></i>Date & Heure</div>
                                <div class="fw-bold text-dark fs-5 mt-1">
                                    <?php echo date('d/m/Y', strtotime($prochain_rdv['date_rdv'])); ?> à <?php echo date('H:i', strtotime($prochain_rdv['heure_rdv'])); ?>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-muted small"><i class="bi bi-chat-right-text me-2"></i>Motif</div>
                                <div class="text-dark text-truncate mt-1" style="max-width: 200px;">
                                    <?php echo htmlspecialchars($prochain_rdv['motif'] ?: 'Non renseigné'); ?>
                                </div>
                            </div>
                            <div class="col-md-2 text-md-end">
                                <?php if ($prochain_rdv['statut'] === 'en_attente'): ?>
                                    <span class="badge bg-warning text-dark d-block mb-2 py-2">En attente</span>
                                    <a href="dashboard.php?action=annuler&id_rdv=<?php echo $prochain_rdv['id_rendez_vous']; ?>"
                                       class="btn btn-sm btn-outline-danger w-100"
                                       onclick="return confirm('Annuler ce rendez-vous ?');">
                                        <i class="bi bi-x-circle me-1"></i>Annuler
                                    </a>
                                <?php elseif ($prochain_rdv['statut'] === 'confirme'): ?>
                                    <span class="badge bg-success d-block py-2"><i class="bi bi-check-circle-fill me-1"></i>Confirmé</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Historique -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 border-bottom-0">
                <h5 class="card-title fw-bold text-dark mb-0"><i class="bi bi-journal-medical me-2 text-primary"></i>Historique de mes demandes</h5>
            </div>
            <div class="card-body p-0">
                <?php if (count($historique) === 0): ?>
                    <div class="text-center p-5 text-muted">
                        <i class="bi bi-folder2 display-4"></i>
                        <p class="mt-2 mb-0">Aucun historique disponible.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Médecin</th>
                                    <th>Spécialité</th>
                                    <th>Date</th>
                                    <th>Heure</th>
                                    <th>Motif</th>
                                    <th class="pe-4">Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($historique as $rdv): ?>
                                    <tr>
                                        <td class="fw-bold ps-4">Dr. <?php echo htmlspecialchars($rdv['medecin_nom'] . ' ' . $rdv['medecin_prenom']); ?></td>
                                        <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($rdv['nom_specialite']); ?></span></td>
                                        <td><?php echo date('d/m/Y', strtotime($rdv['date_rdv'])); ?></td>
                                        <td><?php echo date('H:i', strtotime($rdv['heure_rdv'])); ?></td>
                                        <td><span class="text-muted"><?php echo htmlspecialchars($rdv['motif'] ?: '—'); ?></span></td>
                                        <td class="pe-4">
                                            <?php if ($rdv['statut'] === 'confirme'): ?>
                                                <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill">Confirmé</span>
                                            <?php elseif ($rdv['statut'] === 'en_attente'): ?>
                                                <span class="badge bg-warning-subtle text-warning-emphasis px-3 py-2 rounded-pill">En attente</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger-subtle text-danger px-3 py-2 rounded-pill">Annulé</span>
                                            <?php endif; ?>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>