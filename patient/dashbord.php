<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'patient') {
    header("Location: ../login.php");
    exit;
}

$pdo = getConnexion();

// Prochain RDV
$stmt = $pdo->prepare("
    SELECT rv.date_rdv, rv.heure_rdv, rv.statut, rv.motif,
           u.nom AS medecin_nom, u.prenom AS medecin_prenom,
           s.nom_specialite
    FROM rendez_vous rv
    JOIN patients p ON rv.id_patient = p.id_patient
    JOIN medecin m ON rv.id_medecin = m.id_medecin
    JOIN utilisateurs u ON m.id_utilisateur = u.id_utilisateur
    JOIN specialite s ON m.id_specialite = s.id_specialite
    WHERE p.id_utilisateur = ? AND rv.date_rdv >= CURDATE()
    ORDER BY rv.date_rdv ASC, rv.heure_rdv ASC
    LIMIT 1
");
$stmt->execute([$_SESSION['user_id']]);
$prochain_rdv = $stmt->fetch();

// Historique complet
$stmt = $pdo->prepare("
    SELECT rv.id_rdv, rv.date_rdv, rv.heure_rdv, rv.statut, rv.motif,
           u.nom AS medecin_nom, u.prenom AS medecin_prenom,
           s.nom_specialite
    FROM rendez_vous rv
    JOIN patients p ON rv.id_patient = p.id_patient
    JOIN medecin m ON rv.id_medecin = m.id_medecin
    JOIN utilisateurs u ON m.id_utilisateur = u.id_utilisateur
    JOIN specialite s ON m.id_specialite = s.id_specialite
    WHERE p.id_utilisateur = ?
    ORDER BY rv.date_rdv DESC
");
$stmt->execute([$_SESSION['user_id']]);
$historique = $stmt->fetchAll();
?>