<?php
// ============================================================
// 1. LE BACKEND : Logique métier d'ajout et suppression
// ============================================================
session_start();
require_once '../config/database.php';

// Sécurité : Vérification des accès de l'administrateur
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$pdo = getConnexion();
$succes = "";
$erreur = "";

// Traitement des requêtes POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    
    // Action : AJOUTER UN MÉDECIN
    if ($_POST['action'] === 'ajouter') {
        $email = trim($_POST['email']);
        
        // Sécurité : Vérification de l'existence de l'adresse email
        $checkEmail = $pdo->prepare("SELECT id_utilisateur FROM utilisateurs WHERE email = ?");
        $checkEmail->execute([$email]);
        
        if ($checkEmail->fetch()) {
            $erreur = "Cette adresse email est déjà prise.";
        } else {
            $hash = password_hash($_POST['mot_de_passe'], PASSWORD_BCRYPT);

            // Insertion dans la table utilisateurs
            $stmt = $pdo->prepare("INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, role) VALUES (?, ?, ?, ?, 'medecin')");
            $stmt->execute([trim($_POST['nom']), trim($_POST['prenom']), $email, $hash]);
            $id_utilisateur = $pdo->lastInsertId();

            // Insertion dans la table medecin
            $stmt = $pdo->prepare("INSERT INTO medecin (id_utilisateur, id_specialite, numero_ordre, description) VALUES (?, ?, ?, ?)");
            $stmt->execute([$id_utilisateur, (int)$_POST['id_specialite'], trim($_POST['numero_ordre']), trim($_POST['description'])]);

            $succes = "Le médecin a été enregistré et configuré avec succès.";
        }
    }

    // Action : SUPPRIMER UN MÉDECIN
    if ($_POST['action'] === 'supprimer') {
        $id_medecin = (int)$_POST['id_medecin'];
        
        $stmt = $pdo->prepare("
            DELETE u FROM utilisateurs u
            JOIN medecin m ON m.id_utilisateur = u.id_utilisateur
            WHERE m.id_medecin = ?
        ");
        $stmt->execute([$id_medecin]);
        $succes = "Le compte du médecin ainsi que son profil ont été supprimés.";
    }
}

// Récupération de la liste des médecins actifs
$medecins = $pdo->query("
    SELECT m.id_medecin, u.nom, u.prenom, u.email, s.nom_specialite, m.numero_ordre
    FROM medecin m
    JOIN utilisateurs u ON m.id_utilisateur = u.id_utilisateur
    JOIN specialite s ON m.id_specialite = s.id_specialite
    ORDER BY u.nom ASC
")->fetchAll();

// Récupération des spécialités disponibles pour alimenter le menu déroulant
$specialites = $pdo->query("SELECT * FROM specialite ORDER BY nom_specialite ASC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MedConnect - Gestion Médecins</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold text-info" href="dashboard.php"><i class="bi bi-shield-lock-fill me-2"></i>MedConnect Admin</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php"><i class="bi bi-speedometer2 me-1"></i> Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="medecin.php"><i class="bi bi-person-heart me-1"></i> Gestion Médecins</a>
                    </li>
                </ul>
                <ul class="navbar-nav align-items-center">
                    <li class="nav-item">
                        <span class="nav-link active me-3 text-white-50">Admin: <?php echo htmlspecialchars($_SESSION['user_nom']); ?></span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn btn-outline-danger btn-sm text-white px-3" href="../logout.php"><i class="bi bi-box-arrow-right me-1"></i>Déconnexion</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container my-5">
        
        <?php if (!empty($succes)): ?>
            <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i><?php echo $succes; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if (!empty($erreur)): ?>
            <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo $erreur; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm p-3">
                    <div class="card-body">
                        <h5 class="card-title fw-bold text-dark mb-3"><i class="bi bi-person-plus-fill text-info me-2"></i>Ajouter un médecin</h5>
                        
                        <form action="medecin.php" method="POST">
                            <input type="hidden" name="action" value="ajouter">
                            
                            <div class="mb-2">
                                <label class="form-label small fw-semibold">Nom</label>
                                <input type="text" name="nom" class="form-control form-control-sm" required>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small fw-semibold">Prénom</label>
                                <input type="text" name="prenom" class="form-control form-control-sm" required>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small fw-semibold">Adresse Email</label>
                                <input type="email" name="email" class="form-control form-control-sm" required>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small fw-semibold">Mot de passe temporaire</label>
                                <input type="password" name="mot_de_passe" class="form-control form-control-sm" placeholder="••••••••" required>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small fw-semibold">Spécialité médicale</label>
                                <select name="id_specialite" class="form-select form-select-sm" required>
                                    <option value="" hidden>Sélectionner...</option>
                                    <?php foreach ($specialites as $spec): ?>
                                        <option value="<?php echo $spec['id_specialite']; ?>"><?php echo htmlspecialchars($spec['nom_specialite']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small fw-semibold">Numéro d'ordre des médecins</label>
                                <input type="text" name="numero_ordre" class="form-control form-control-sm" placeholder="Ex: CNOM-XXXX" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Biographie / Description</label>
                                <textarea name="description" class="form-control form-control-sm" rows="2" placeholder="Informations complémentaires..."></textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-info text-dark w-100 btn-sm fw-bold shadow-sm">
                                <i class="bi bi-plus-lg me-1"></i>Enregistrer le médecin
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3 border-bottom-0">
                        <h5 class="card-title fw-bold text-dark mb-0"><i class="bi bi-list-stars text-info me-2"></i>Médecins en exercice</h5>
                    </div>
                    <div class="card-body p-0">
                        <?php if (count($medecins) === 0): ?>
                            <div class="text-center p-5 text-muted">
                                <i class="bi bi-heart-pulse display-4"></i>
                                <p class="mt-2 mb-0">Aucun médecin configuré sur la plateforme actuellement.</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="ps-4">Médecin</th>
                                            <th>Email</th>
                                            <th>Spécialité</th>
                                            <th>N° Ordre</th>
                                            <th class="text-center pe-4">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($medecins as $m): ?>
                                            <tr>
                                                <td class="fw-bold ps-4">Dr. <?php echo htmlspecialchars($m['nom'] . ' ' . $m['prenom']); ?></td>
                                                <td class="small"><?php echo htmlspecialchars($m['email']); ?></td>
                                                <td><span class="badge bg-success-subtle text-success px-2 py-1"><?php echo htmlspecialchars($m['nom_specialite']); ?></span></td>
                                                <td class="font-monospace text-secondary small"><?php echo htmlspecialchars($m['numero_ordre']); ?></td>
                                                <td class="text-center pe-4">
                                                    <form action="medecin.php" method="POST" onsubmit="return confirm('Voulez-vous vraiment supprimer le Dr. <?php echo htmlspecialchars($m['nom']); ?> ? Cette action effacera également son compte utilisateur.');">
                                                        <input type="hidden" name="action" value="supprimer">
                                                        <input type="hidden" name="id_medecin" value="<?php echo $m['id_medecin']; ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline-danger py-1 px-2">
                                                            <i class="bi bi-trash3-fill"></i>
                                                        </button>
                                                    </form>
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
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>