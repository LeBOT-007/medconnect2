<?php
session_start();
require_once '../config/database.php';

// Sécurité : seul un patient connecté peut accéder
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'patient') {
    header("Location: ../login.php");
    exit;
}

$pdo = getConnexion();

// Récupérer l'id_patient de la session
$stmt = $pdo->prepare("SELECT id_patient FROM patients WHERE id_utilisateur = ?");
$stmt->execute([$_SESSION['user_id']]);
$patient = $stmt->fetch();
$id_patient = $patient['id_patient'];

// Récupérer tous les rendez-vous du patient
$stmt = $pdo->prepare("
    SELECT rv.date_rdv, rv.heure_rdv, rv.motif, rv.statut,
           u.nom, u.prenom, s.nom_specialite
    FROM rendez_vous rv
    JOIN medecin m ON rv.id_medecin = m.id_medecin
    JOIN utilisateurs u ON m.id_utilisateur = u.id_utilisateur
    JOIN specialite s ON m.id_specialite = s.id_specialite
    WHERE rv.id_patient = ?
    ORDER BY rv.date_rdv DESC, rv.heure_rdv DESC
");
$stmt->execute([$id_patient]);
$rdvs = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MedConnect - Mon Historique de Consultations</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">

    <?php include_once '../includes/navbar_patient.php'; ?>

    <div class="container my-5">
        
        <div class="mb-4">
            <h2 class="fw-bold text-dark mb-1">Historique de mes demandes</h2>
            <p class="text-muted">Retrouvez l'ensemble de vos rendez-vous passés, à venir, ainsi que leurs statuts de validation.</p>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <?php if (count($rdvs) === 0): ?>
                    <div class="text-center p-5 text-muted">
                        <i class="bi bi-folder2-open display-3 text-secondary mb-3"></i>
                        <p class="mb-0 fw-medium">Vous n'avez encore enregistré aucun rendez-vous sur la plateforme.</p>
                        <a href="prendre_rdv.php" class="btn btn-primary btn-sm mt-3 fw-semibold">Prendre mon premier RDV</a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Médecin</th>
                                    <th>Spécialité</th>
                                    <th>Date de consultation</th>
                                    <th>Heure</th>
                                    <th>Motif de la demande</th>
                                    <th class="pe-4">Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($rdvs as $rdv): ?>
                                    <tr>
                                        <td class="fw-bold ps-4">Dr. <?php echo htmlspecialchars($rdv['nom'] . ' ' . $rdv['prenom']); ?></td>
                                        <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($rdv['nom_specialite']); ?></span></td>
                                        <td><i class="bi bi-calendar3 me-2 text-muted"></i><?php echo date('d/m/Y', strtotime($rdv['date_rdv'])); ?></td>
                                        <td><i class="bi bi-clock me-2 text-muted"></i><?php echo date('H:i', strtotime($rdv['heure_rdv'])); ?></td>
                                        <td><span class="text-muted"><?php echo htmlspecialchars($rdv['motif'] ?: '—'); ?></span></td>
                                        <td class="pe-4">
                                            <?php 
                                            $s = $rdv['statut'];
                                            if ($s === 'confirme' || $s === 'valide'): ?>
                                                <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill"><i class="bi bi-check-circle-fill me-1"></i> Validé</span>
                                            <?php elseif ($s === 'en_attente'): ?>
                                                <span class="badge bg-warning-subtle text-warning-emphasis px-3 py-2 rounded-pill"><i class="bi bi-hourglass-split me-1"></i> En attente</span>
                                            <?php elseif ($s === 'refuse'): ?>
                                                <span class="badge bg-danger-subtle text-danger px-3 py-2 rounded-pill"><i class="bi bi-x-circle-fill me-1"></i> Refusé</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary-subtle text-secondary px-3 py-2 rounded-pill"><i class="bi bi-dash-circle-fill me-1"></i> Annulé</span>
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