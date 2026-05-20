<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'medecin') {
    header("Location: ../login.php");
    exit;
}

$pdo = getConnexion();

$stmt = $pdo->prepare("SELECT id_medecin FROM medecin WHERE id_utilisateur = ?");
$stmt->execute([$_SESSION['user_id']]);
$medecin = $stmt->fetch();
$id_medecin = $medecin['id_medecin'];

// Ajouter une disponibilité
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    if ($_POST['action'] === 'ajouter') {
        $stmt = $pdo->prepare("
            INSERT INTO disponibilite (id_medecin, jour_semaine, heure_debut, heure_fin)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$id_medecin, $_POST['jour_semaine'], $_POST['heure_debut'], $_POST['heure_fin']]);
        $succes = "Disponibilité ajoutée.";
    }

    if ($_POST['action'] === 'supprimer') {
        $stmt = $pdo->prepare("
            DELETE FROM disponibilite WHERE id_disponibilite = ? AND id_medecin = ?
        ");
        $stmt->execute([(int)$_POST['id_disponibilite'], $id_medecin]);
        $succes = "Disponibilité supprimée.";
    }
}

// Lister les disponibilités
$stmt = $pdo->prepare("SELECT * FROM disponibilite WHERE id_medecin = ? ORDER BY FIELD(jour_semaine,'Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi','Dimanche'), heure_debut");
$stmt->execute([$id_medecin]);
$disponibilites = $stmt->fetchAll();
?>