<?php
session_start();
require_once '../config/database.php';

// Sécurité
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'patient') {
    header("Location: ../login.php");
    exit;
}

$pdo = getConnexion();

// Récupérer l'id_patient
$stmt = $pdo->prepare("SELECT id_patient FROM patients WHERE id_utilisateur = ?");
$stmt->execute([$_SESSION['user_id']]);
$patient = $stmt->fetch();
$id_patient = $patient['id_patient'];

// Récupérer la liste des médecins
$stmt = $pdo->prepare("
    SELECT m.id_medecin, u.nom, u.prenom, s.nom_specialite AS specialite
    FROM medecin m
    JOIN utilisateurs u ON m.id_utilisateur = u.id_utilisateur
    JOIN specialite s ON m.id_specialite = s.id_specialite
    ORDER BY u.nom
");
$stmt->execute();
$medecins = $stmt->fetchAll();

$message = '';
$erreur  = '';

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_medecin = $_POST['id_medecin'];
    $date_rdv   = $_POST['date_rdv'];
    $heure_rdv  = $_POST['heure_rdv'];
    $motif      = trim($_POST['motif']);

    // Vérifications simples
    if (empty($id_medecin) || empty($date_rdv) || empty($heure_rdv)) {
        $erreur = "Veuillez remplir tous les champs obligatoires.";

    } elseif ($date_rdv < date('Y-m-d')) {
        $erreur = "La date choisie est déjà passée.";

    } else {
        // Vérifier si ce créneau est déjà pris
        $check = $pdo->prepare("
            SELECT COUNT(*) FROM rendez_vous
            WHERE id_medecin = ? AND date_rdv = ? AND heure_rdv = ?
            AND statut != 'annule'
        ");
        $check->execute([$id_medecin, $date_rdv, $heure_rdv]);

        if ($check->fetchColumn() > 0) {
            $erreur = "Ce créneau est déjà pris. Choisissez un autre horaire.";
        } else {
            // Insérer le rendez-vous
            $insert = $pdo->prepare("
                INSERT INTO rendez_vous (id_patient, id_medecin, date_rdv, heure_rdv, motif, statut)
                VALUES (?, ?, ?, ?, ?, 'en_attente')
            ");
            $insert->execute([$id_patient, $id_medecin, $date_rdv, $heure_rdv, $motif]);
            $message = "Votre rendez-vous a bien été enregistré ! En attente de validation.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prendre un RDV – MedConnect</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Nunito', sans-serif;
            background: #f0f4ff;
            color: #1e293b;
            min-height: 100vh;
        }

        /* ── Navbar ── */
        nav {
            background: #1a6fc4;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(26,111,196,.3);
        }
        nav .logo { color: #fff; font-size: 1.3rem; font-weight: 800; }
        nav .logo span { color: #93c5fd; }
        nav .nav-links { display: flex; gap: .8rem; }
        nav a {
            color: #fff;
            text-decoration: none;
            font-size: .9rem;
            font-weight: 600;
            background: rgba(255,255,255,.15);
            padding: .4rem 1rem;
            border-radius: 20px;
            transition: background .2s;
        }
        nav a:hover { background: rgba(255,255,255,.3); }

        /* ── Formulaire ── */
        .container {
            max-width: 580px;
            margin: 2.5rem auto;
            padding: 0 1rem;
        }

        h1 { font-size: 1.5rem; font-weight: 800; color: #1a6fc4; margin-bottom: 1.5rem; }

        .card {
            background: #fff;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 4px 20px rgba(0,0,0,.08);
            animation: fadeIn .35s ease;
        }

        .group { margin-bottom: 1.2rem; }
        label {
            display: block;
            font-size: .85rem;
            font-weight: 700;
            color: #475569;
            margin-bottom: .4rem;
        }
        input, select, textarea {
            width: 100%;
            padding: .7rem 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-family: 'Nunito', sans-serif;
            font-size: .95rem;
            color: #1e293b;
            background: #f8fafc;
            transition: border-color .2s;
        }
        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: #1a6fc4;
            background: #fff;
        }
        textarea { resize: vertical; min-height: 90px; }

        /* Date min = aujourd'hui via JS */

        .row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }

        /* ── Bouton ── */
        button[type="submit"] {
            width: 100%;
            padding: .9rem;
            background: #1a6fc4;
            color: #fff;
            border: none;
            border-radius: 10px;
            font-family: 'Nunito', sans-serif;
            font-size: 1rem;
            font-weight: 800;
            cursor: pointer;
            margin-top: .5rem;
            transition: background .2s, transform .15s;
        }
        button[type="submit"]:hover { background: #155fa0; transform: translateY(-1px); }

        /* ── Messages ── */
        .alert {
            border-radius: 10px;
            padding: .9rem 1.2rem;
            margin-bottom: 1.2rem;
            font-weight: 700;
            font-size: .92rem;
        }
        .alert-success { background: #dcfce7; color: #15803d; border-left: 4px solid #22c55e; }
        .alert-error   { background: #fee2e2; color: #dc2626; border-left: 4px solid #ef4444; }

        .back-link {
            display: inline-block;
            margin-top: 1rem;
            color: #1a6fc4;
            font-weight: 700;
            text-decoration: none;
            font-size: .9rem;
        }
        .back-link:hover { text-decoration: underline; }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(16px); }
            to   { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav>
    <div class="logo">Med<span>Connect</span></div>
    <div class="nav-links">
        <a href="historique.php">📋 Historique</a>
        <a href="../logout.php">Déconnexion</a>
    </div>
</nav>

<div class="container">
    <h1>🗓️ Prendre un rendez-vous</h1>

    <div class="card">

        <!-- Messages de retour -->
        <?php if ($message): ?>
            <div class="alert alert-success">✅ <?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <?php if ($erreur): ?>
            <div class="alert alert-error">❌ <?= htmlspecialchars($erreur) ?></div>
        <?php endif; ?>

        <form method="POST" action="">

            <!-- Choix du médecin -->
            <div class="group">
                <label for="id_medecin">👨‍⚕️ Médecin *</label>
                <select name="id_medecin" id="id_medecin" required>
                    <option value="">-- Choisir un médecin --</option>
                    <?php foreach ($medecins as $med): ?>
                    <option value="<?= $med['id_medecin'] ?>"
                        <?= (isset($_POST['id_medecin']) && $_POST['id_medecin'] == $med['id_medecin']) ? 'selected' : '' ?>>
                        Dr <?= htmlspecialchars($med['prenom'] . ' ' . $med['nom']) ?>
                        (<?= htmlspecialchars($med['specialite'] ?? 'Généraliste') ?>)
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Date et heure -->
            <div class="row">
                <div class="group">
                    <label for="date_rdv">📅 Date *</label>
                    <input type="date" name="date_rdv" id="date_rdv" required
                           value="<?= htmlspecialchars($_POST['date_rdv'] ?? '') ?>">
                </div>
                <div class="group">
                    <label for="heure_rdv">⏰ Heure *</label>
                    <input type="time" name="heure_rdv" id="heure_rdv" required
                           min="08:00" max="18:00"
                           value="<?= htmlspecialchars($_POST['heure_rdv'] ?? '') ?>">
                </div>
            </div>

            <!-- Motif -->
            <div class="group">
                <label for="motif">📝 Motif de la consultation</label>
                <textarea name="motif" id="motif" placeholder="Ex: Douleur abdominale, contrôle annuel..."><?= htmlspecialchars($_POST['motif'] ?? '') ?></textarea>
            </div>

            <button type="submit">Envoyer la demande →</button>
        </form>

        <a href="historique.php" class="back-link">← Retour à mon historique</a>
    </div>
</div>

<script>
    // Empêcher de choisir une date passée
    document.getElementById('date_rdv').min = new Date().toISOString().split('T')[0];
</script>

</body>
</html>