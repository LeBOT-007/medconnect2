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

// Supprimer un utilisateur
if (isset($_GET['action']) && $_GET['action'] === 'supprimer' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    if ($id === intval($_SESSION['user_id'])) {
        $erreur = "Vous ne pouvez pas supprimer votre propre compte.";
    } else {
        $pdo->prepare("DELETE FROM utilisateurs WHERE id_utilisateur = ?")->execute([$id]);
        $message = "Utilisateur supprimé avec succès.";
    }
}

$utilisateurs = $pdo->query("
    SELECT u.id_utilisateur, u.nom, u.prenom, u.email, u.role, u.date_creation,
           p.telephone, p.date_naissance
    FROM utilisateurs u
    LEFT JOIN patients p ON u.id_utilisateur = p.id_utilisateur
    ORDER BY u.date_creation DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MedConnect - Gestion des Utilisateurs</title>
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
                <li class="nav-item"><a class="nav-link" href="specialite.php"><i class="bi bi-journal-medical me-1"></i>Spécialités</a></li>
                <li class="nav-item"><a class="nav-link active" href="utilisateurs.php"><i class="bi bi-people me-1"></i>Utilisateurs</a></li>
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
        <h2 class="fw-bold text-dark"><i class="bi bi-people me-2 text-secondary"></i>Gestion des Utilisateurs</h2>
        <span class="badge bg-primary fs-6 px-3 py-2"><?php echo count($utilisateurs); ?> utilisateur(s)</span>
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

    <!-- Filtres -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3">
            <div class="row g-3 align-items-center">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" id="searchInput" class="form-control border-start-0" placeholder="Rechercher par nom, prénom ou email...">
                    </div>
                </div>
                <div class="col-md-3">
                    <select id="filterRole" class="form-select">
                        <option value="">Tous les rôles</option>
                        <option value="admin">Administrateur</option>
                        <option value="medecin">Médecin</option>
                        <option value="patient">Patient</option>
                    </select>
                </div>
                <div class="col-md-3 text-md-end">
                    <span class="text-muted small" id="countResult"><?php echo count($utilisateurs); ?> résultat(s)</span>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <?php if (count($utilisateurs) === 0): ?>
                <div class="text-center p-5 text-muted">
                    <i class="bi bi-people display-4"></i>
                    <p class="mt-2 mb-0">Aucun utilisateur inscrit.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="tableUsers">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">ID</th>
                                <th>Nom & Prénom</th>
                                <th>Email</th>
                                <th>Rôle</th>
                                <th>Téléphone</th>
                                <th>Inscription</th>
                                <th class="pe-4 text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($utilisateurs as $u): ?>
                                <tr data-role="<?php echo $u['role']; ?>">
                                    <td class="text-muted ps-4">#<?php echo $u['id_utilisateur']; ?></td>
                                    <td class="fw-bold"><?php echo htmlspecialchars($u['nom'] . ' ' . $u['prenom']); ?></td>
                                    <td class="text-muted"><?php echo htmlspecialchars($u['email']); ?></td>
                                    <td>
                                        <?php if ($u['role'] === 'admin'): ?>
                                            <span class="badge bg-danger px-2 py-1">Administrateur</span>
                                        <?php elseif ($u['role'] === 'medecin'): ?>
                                            <span class="badge bg-info text-dark px-2 py-1">Médecin</span>
                                        <?php else: ?>
                                            <span class="badge bg-primary px-2 py-1">Patient</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-muted"><?php echo htmlspecialchars($u['telephone'] ?? '—'); ?></td>
                                    <td class="text-muted"><?php echo date('d/m/Y', strtotime($u['date_creation'])); ?></td>
                                    <td class="pe-4 text-end">
                                        <button class="btn btn-sm btn-outline-primary me-1"
                                            data-bs-toggle="modal" data-bs-target="#modalDetail"
                                            data-id="<?php echo $u['id_utilisateur']; ?>"
                                            data-nom="<?php echo htmlspecialchars($u['nom'] . ' ' . $u['prenom']); ?>"
                                            data-email="<?php echo htmlspecialchars($u['email']); ?>"
                                            data-role="<?php echo $u['role']; ?>"
                                            data-tel="<?php echo htmlspecialchars($u['telephone'] ?? '—'); ?>"
                                            data-naissance="<?php echo $u['date_naissance'] ? date('d/m/Y', strtotime($u['date_naissance'])) : '—'; ?>"
                                            data-inscription="<?php echo date('d/m/Y à H:i', strtotime($u['date_creation'])); ?>">
                                            <i class="bi bi-eye-fill"></i>
                                        </button>
                                        <?php if ($u['id_utilisateur'] != $_SESSION['user_id']): ?>
                                            <a href="utilisateurs.php?action=supprimer&id=<?php echo $u['id_utilisateur']; ?>"
                                               class="btn btn-sm btn-outline-danger"
                                               onclick="return confirm('Supprimer cet utilisateur ?');">
                                                <i class="bi bi-trash-fill"></i>
                                            </a>
                                        <?php else: ?>
                                            <button class="btn btn-sm btn-outline-secondary" disabled>
                                                <i class="bi bi-trash-fill"></i>
                                            </button>
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

<!-- Modal Détail -->
<div class="modal fade" id="modalDetail" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-person-fill me-2"></i>Détail utilisateur</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <table class="table table-borderless mb-0">
                    <tr><th class="text-muted" style="width:40%">ID</th><td id="d_id" class="fw-bold"></td></tr>
                    <tr><th class="text-muted">Nom complet</th><td id="d_nom" class="fw-bold"></td></tr>
                    <tr><th class="text-muted">Email</th><td id="d_email"></td></tr>
                    <tr><th class="text-muted">Rôle</th><td id="d_role"></td></tr>
                    <tr><th class="text-muted">Téléphone</th><td id="d_tel"></td></tr>
                    <tr><th class="text-muted">Date de naissance</th><td id="d_naissance"></td></tr>
                    <tr><th class="text-muted">Inscrit le</th><td id="d_inscription"></td></tr>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.getElementById('modalDetail').addEventListener('show.bs.modal', function (e) {
        const btn = e.relatedTarget;
        document.getElementById('d_id').textContent          = '#' + btn.dataset.id;
        document.getElementById('d_nom').textContent         = btn.dataset.nom;
        document.getElementById('d_email').textContent       = btn.dataset.email;
        document.getElementById('d_tel').textContent         = btn.dataset.tel;
        document.getElementById('d_naissance').textContent   = btn.dataset.naissance;
        document.getElementById('d_inscription').textContent = btn.dataset.inscription;
        const role = btn.dataset.role;
        const badges = { admin: 'danger', medecin: 'info', patient: 'primary' };
        const labels = { admin: 'Administrateur', medecin: 'Médecin', patient: 'Patient' };
        document.getElementById('d_role').innerHTML = `<span class="badge bg-${badges[role]}">${labels[role]}</span>`;
    });

    const searchInput = document.getElementById('searchInput');
    const filterRole  = document.getElementById('filterRole');
    const countResult = document.getElementById('countResult');
    const rows        = document.querySelectorAll('#tableUsers tbody tr');

    function filtrer() {
        const q   = searchInput.value.toLowerCase();
        const role = filterRole.value;
        let count  = 0;
        rows.forEach(row => {
            const matchQ = row.textContent.toLowerCase().includes(q);
            const matchR = role === '' || row.dataset.role === role;
            row.style.display = (matchQ && matchR) ? '' : 'none';
            if (matchQ && matchR) count++;
        });
        countResult.textContent = count + ' résultat(s)';
    }

    searchInput.addEventListener('input', filtrer);
    filterRole.addEventListener('change', filtrer);
</script>
</body>
</html>