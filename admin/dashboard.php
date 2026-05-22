<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$pdo = getConnexion();

$stats = [
    'patients'    => $pdo->query("SELECT COUNT(*) FROM patients")->fetchColumn(),
    'medecins'    => $pdo->query("SELECT COUNT(*) FROM medecin")->fetchColumn(),
    'rdv_total'   => $pdo->query("SELECT COUNT(*) FROM rendez_vous")->fetchColumn(),
    'rdv_attente' => $pdo->query("SELECT COUNT(*) FROM rendez_vous WHERE statut = 'en_attente'")->fetchColumn(),
];

$utilisateurs = $pdo->query("SELECT * FROM utilisateurs ORDER BY date_creation DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MedConnect - Administration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">

    <?php include_once '../includes/navbar.php'; ?>

    <div class="container my-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold text-dark"><i class="bi bi-speedometer2 me-2 text-secondary"></i>Vue d'ensemble du système</h2>
        </div>
        
        <div class="row g-4 mb-5">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm bg-primary text-white h-100">
                    <div class="card-body d-flex align-items-center justify-content-between p-4">
                        <div>
                            <h6 class="text-uppercase fw-semibold mb-1 opacity-75">Patients</h6>
                            <h2 class="display-6 fw-bold mb-0"><?php echo $stats['patients']; ?></h2>
                        </div>
                        <i class="bi bi-people display-4 opacity-50"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm bg-info text-white h-100">
                    <div class="card-body d-flex align-items-center justify-content-between p-4">
                        <div>
                            <h6 class="text-uppercase fw-semibold mb-1 opacity-75">Médecins</h6>
                            <h2 class="display-6 fw-bold mb-0"><?php echo $stats['medecins']; ?></h2>
                        </div>
                        <i class="bi bi-heart-pulse display-4 opacity-50"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm bg-success text-white h-100">
                    <div class="card-body d-flex align-items-center justify-content-between p-4">
                        <div>
                            <h6 class="text-uppercase fw-semibold mb-1 opacity-75">Rendez-vous</h6>
                            <h2 class="display-6 fw-bold mb-0"><?php echo $stats['rdv_total']; ?></h2>
                        </div>
                        <i class="bi bi-calendar2-check display-4 opacity-50"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm bg-warning text-dark h-100">
                    <div class="card-body d-flex align-items-center justify-content-between p-4">
                        <div>
                            <h6 class="text-uppercase fw-semibold mb-1 opacity-75">En Attente</h6>
                            <h2 class="display-6 fw-bold mb-0"><?php echo $stats['rdv_attente']; ?></h2>
                        </div>
                        <i class="bi bi-clock-history display-4 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 border-bottom-0">
                <h5 class="card-title fw-bold text-dark mb-0"><i class="bi bi-person-lines-fill me-2 text-primary"></i>Tous les utilisateurs inscrits</h5>
            </div>
            <div class="card-body p-0">
                <?php if (count($utilisateurs) === 0): ?>
                    <div class="text-center p-5 text-muted">
                        <i class="bi bi-people display-4"></i>
                        <p class="mt-2 mb-0">Aucun utilisateur inscrit.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">ID</th>
                                    <th>Nom & Prénom</th>
                                    <th>Email</th>
                                    <th>Rôle</th>
                                    <th class="pe-4">Date de création</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($utilisateurs as $user): ?>
                                    <tr>
                                        <td class="text-muted ps-4">#<?php echo $user['id_utilisateur']; ?></td>
                                        <td class="fw-bold"><?php echo htmlspecialchars($user['nom'] . ' ' . $user['prenom']); ?></td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td>
                                            <?php if ($user['role'] === 'admin'): ?>
                                                <span class="badge bg-danger px-2 py-1">Administrateur</span>
                                            <?php elseif ($user['role'] === 'medecin'): ?>
                                                <span class="badge bg-info text-dark px-2 py-1">Médecin</span>
                                            <?php else: ?>
                                                <span class="badge bg-primary px-2 py-1">Patient</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-muted pe-4"><?php echo date('d/m/Y à H:i', strtotime($user['date_creation'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include_once '../includes/footer.php'; ?>
</body>
</html>