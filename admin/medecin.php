<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$pdo = getConnexion();

// Ajouter un médecin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'ajouter') {
    $hash = password_hash($_POST['mot_de_passe'], PASSWORD_BCRYPT);

    $stmt = $pdo->prepare("INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, role) VALUES (?, ?, ?, ?, 'medecin')");
    $stmt->execute([trim($_POST['nom']), trim($_POST['prenom']), trim($_POST['email']), $hash]);
    $id_utilisateur = $pdo->lastInsertId();

    $stmt = $pdo->prepare("INSERT INTO medecin (id_utilisateur, id_specialite, numero_ordre, description) VALUES (?, ?, ?, ?)");
    $stmt->execute([$id_utilisateur, (int)$_POST['id_specialite'], trim($_POST['numero_ordre']), trim($_POST['description'])]);

    $succes = "Médecin ajouté avec succès.";
}

// Supprimer un médecin (supprime aussi l'utilisateur via CASCADE)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'supprimer') {
    $stmt = $pdo->prepare("
        DELETE u FROM utilisateurs u
        JOIN medecin m ON m.id_utilisateur = u.id_utilisateur
        WHERE m.id_medecin = ?
    ");
    $stmt->execute([(int)$_POST['id_medecin']]);
    $succes = "Médecin supprimé.";
}

// Lister les médecins
$medecins = $pdo->query("
    SELECT m.id_medecin, u.nom, u.prenom, u.email, s.nom_specialite, m.numero_ordre
    FROM medecin m
    JOIN utilisateurs u ON m.id_utilisateur = u.id_utilisateur
    JOIN specialite s ON m.id_specialite = s.id_specialite
    ORDER BY u.nom
")->fetchAll();

// Spécialités pour le formulaire
$specialites = $pdo->query("SELECT * FROM specialite ORDER BY nom_specialite")->fetchAll();
?>