<?php
session_start();
require_once 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo   = getConnexion();
    $email = trim($_POST['email']);
    $mdp   = $_POST['mot_de_passe'];

    $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($mdp, $user['mot_de_passe'])) {
        $_SESSION['user_id']   = $user['id_utilisateur'];
        $_SESSION['user_nom']  = $user['nom'];
        $_SESSION['user_prenom'] = $user['prenom'];
        $_SESSION['user_role'] = $user['role'];

        match($user['role']) {
            'admin'   => header("Location: admin/dashbord.php"),
            'medecin' => header("Location: medecin/dashbord.php"),
            'patient' => header("Location: patient/dashbord.php"),
        };
        exit;
    } else {
        $erreur = "Email ou mot de passe incorrect.";
    }
}
?>