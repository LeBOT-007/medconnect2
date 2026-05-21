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
// ✅ APRÈS
$stmt = $pdo->prepare("
    SELECT rv.date_rdv, rv.heure_rdv, rv.motif, rv.statut,
           u.nom, u.prenom, s.nom_specialite
    FROM rendez_vous rv
    JOIN medecin m ON rv.id_medecin = m.id_medecin
    JOIN utilisateurs u ON m.id_utilisateur = u.id_utilisateur
    JOIN specialite s ON m.id_specialite = s.id_specialite
    WHERE rv.id_patient = ?
    ORDER BY rv.date_rdv DESC
");
$stmt->execute([$id_patient]);
$rdvs = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historique – MedConnect</title>
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
        nav .logo { color: #fff; font-size: 1.3rem; font-weight: 800; letter-spacing: -0.5px; }
        nav .logo span { color: #93c5fd; }
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

        /* ── Contenu principal ── */
        .container { max-width: 900px; margin: 2rem auto; padding: 0 1rem; }

        h1 {
            font-size: 1.6rem;
            font-weight: 800;
            margin-bottom: 1.5rem;
            color: #1a6fc4;
        }
        h1 span { color: #64748b; font-weight: 600; font-size: 1rem; margin-left: .5rem; }

        /* ── Cartes RDV ── */
        .rdv-card {
            background: #fff;
            border-radius: 14px;
            padding: 1.2rem 1.5rem;
            margin-bottom: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,.06);
            border-left: 5px solid #e2e8f0;
            transition: transform .15s, box-shadow .15s;
            animation: fadeUp .3s ease both;
        }
        .rdv-card:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,0,0,.1); }

        .rdv-card.valide    { border-left-color: #22c55e; }
        .rdv-card.en_attente{ border-left-color: #f59e0b; }
        .rdv-card.annule    { border-left-color: #ef4444; }
        .rdv-card.refuse    { border-left-color: #8b5cf6; }

        .rdv-info .date  { font-size: .82rem; color: #64748b; margin-bottom: .2rem; }
        .rdv-info .medecin { font-size: 1rem; font-weight: 700; }
        .rdv-info .motif { font-size: .88rem; color: #475569; margin-top: .15rem; }

        /* ── Badge statut ── */
        .badge {
            padding: .35rem .9rem;
            border-radius: 50px;
            font-size: .8rem;
            font-weight: 700;
            white-space: nowrap;
        }
        .badge.valide     { background: #dcfce7; color: #15803d; }
        .badge.en_attente { background: #fef3c7; color: #b45309; }
        .badge.annule     { background: #fee2e2; color: #dc2626; }
        .badge.refuse     { background: #ede9fe; color: #6d28d9; }

        /* ── Message vide ── */
        .vide {
            text-align: center;
            background: #fff;
            border-radius: 14px;
            padding: 3rem;
            color: #94a3b8;
            box-shadow: 0 2px 10px rgba(0,0,0,.05);
        }
        .vide .icon { font-size: 3rem; margin-bottom: .75rem; }

        /* ── Bouton prendre RDV ── */
        .btn-rdv {
            display: inline-block;
            background: #1a6fc4;
            color: #fff;
            padding: .8rem 1.8rem;
            border-radius: 10px;
            font-weight: 700;
            font-size: .95rem;
            text-decoration: none;
            margin-bottom: 1.5rem;
            transition: background .2s, transform .15s;
        }
        .btn-rdv:hover { background: #155fa0; transform: translateY(-1px); }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(12px); }
            to   { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav>
    <div class="logo">Med<span>Connect</span></div>
    <a href="../logout.php">Déconnexion</a>
</nav>

<div class="container">
    <h1> Mes rendez-vous <span><?= count($rdvs) ?> au total</span></h1>

    <a href="prendre_rdv.php" class="btn-rdv">+ Prendre un nouveau rendez-vous</a>

    <?php if (empty($rdvs)): ?>
        <div class="vide">
            <div class="icon"></div>
            <p>Vous n'avez encore aucun rendez-vous.</p>
        </div>
    <?php else: ?>
        <?php foreach ($rdvs as $rdv): ?>
        <div class="rdv-card <?= htmlspecialchars($rdv['statut']) ?>">
            <div class="rdv-info">
                <div class="date">
                     <?= date('d/m/Y', strtotime($rdv['date_rdv'])) ?>
                    &nbsp; <?= substr($rdv['heure_rdv'], 0, 5) ?>
                </div>
                <div class="medecin">
                    Dr <?= htmlspecialchars($rdv['prenom'] . ' ' . $rdv['nom']) ?>
                    <small style="font-weight:500; color:#64748b;">
                        — <?= htmlspecialchars($rdv['specialite'] ?? '') ?>
                    </small>
                </div>
                <div class="motif">Motif : <?= htmlspecialchars($rdv['motif'] ?? 'Non précisé') ?></div>
            </div>
            <?php
                $labels = [
                    'valide'     => ' Validé',
                    'en_attente' => 'En attente',
                    'annule'     => ' Annulé',
                    'refuse'     => 'Refusé',
                ];
                $s = $rdv['statut'];
            ?>
            <span class="badge <?= htmlspecialchars($s) ?>">
                <?= $labels[$s] ?? ucfirst($s) ?>
            </span>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

</body>
</html>