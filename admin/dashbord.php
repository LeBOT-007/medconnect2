<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$pdo = getConnexion();

// Statistiques générales
$stats = [
    'patients'  => $pdo->query("SELECT COUNT(*) FROM patients")->fetchColumn(),
    'medecins'  => $pdo->query("SELECT COUNT(*) FROM medecin")->fetchColumn(),
    'rdv_total' => $pdo->query("SELECT COUNT(*) FROM rendez_vous")->fetchColumn(),
    'rdv_attente' => $pdo->query("SELECT COUNT(*) FROM rendez_vous WHERE statut = 'en_attente'")->fetchColumn(),
];

// Liste de tous les utilisateurs
$utilisateurs = $pdo->query("SELECT * FROM utilisateurs ORDER BY date_creation DESC")->fetchAll();
?>