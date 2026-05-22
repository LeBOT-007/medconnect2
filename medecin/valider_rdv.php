<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'medecin') {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo        = getConnexion();
    $id_rdv     = (int) $_POST['id_rdv'];
    $action     = $_POST['action']; // 'valide' ou 'annule'

    // Vérifier que le RDV appartient bien à ce médecin
    $stmt = $pdo->prepare("
        SELECT rv.id_rdv FROM rendez_vous rv
        JOIN medecin m ON rv.id_medecin = m.id_medecin
        WHERE rv.id_rdv = ? AND m.id_utilisateur = ?
    ");
    $stmt->execute([$id_rdv, $_SESSION['user_id']]);

    if ($stmt->fetch()) {
        $stmt = $pdo->prepare("UPDATE rendez_vous SET statut = ? WHERE id_rdv = ?");
        $stmt->execute([$action, $id_rdv]);
    }

    header("Location: dashboard.php");
    exit;
}
?>