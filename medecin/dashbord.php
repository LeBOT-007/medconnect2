<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'medecin') {
    header("Location: ../login.php");
    exit;
}

$pdo = getConnexion();

// Récupérer l'id_medecin
$stmt = $pdo->prepare("SELECT id_medecin FROM medecin WHERE id_utilisateur = ?");
$stmt->execute([$_SESSION['user_id']]);
$medecin = $stmt->fetch();
$id_medecin = $medecin['id_medecin'];

// RDV en attente
$stmt = $pdo->prepare("
    SELECT rv.*, u.nom, u.prenom, p.telephone
    FROM rendez_vous rv
    JOIN patients p ON rv.id_patient = p.id_patient
    JOIN utilisateurs u ON p.id_utilisateur = u.id_utilisateur
    WHERE rv.id_medecin = ? AND rv.statut = 'en_attente'
    ORDER BY rv.date_rdv ASC, rv.heure_rdv ASC
");
$stmt->execute([$id_medecin]);
$rdv_en_attente = $stmt->fetchAll();

// Tous les RDV validés à venir
$stmt = $pdo->prepare("
    SELECT rv.*, u.nom, u.prenom
    FROM rendez_vous rv
    JOIN patients p ON rv.id_patient = p.id_patient
    JOIN utilisateurs u ON p.id_utilisateur = u.id_utilisateur
    WHERE rv.id_medecin = ? AND rv.statut = 'valide' AND rv.date_rdv >= CURDATE()
    ORDER BY rv.date_rdv ASC, rv.heure_rdv ASC
");
$stmt->execute([$id_medecin]);
$rdv_valides = $stmt->fetchAll();
?>