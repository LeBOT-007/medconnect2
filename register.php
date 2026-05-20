<?php
session_start();
require_once 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo    = getConnexion();
    $nom    = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $email  = trim($_POST['email']);
    $mdp    = $_POST['mot_de_passe'];
    $role   = $_POST['role'];

    // Vérifier email existant
    $stmt = $pdo->prepare("SELECT id_utilisateur FROM utilisateurs WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $erreur = "Cet email est déjà utilisé.";
    } else {
        $hash = password_hash($mdp, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, role) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$nom, $prenom, $email, $hash, $role]);
        $id = $pdo->lastInsertId();

        if ($role === 'patient') {
            $stmt2 = $pdo->prepare("INSERT INTO patients (id_utilisateur, telephone, date_naissance, adresse) VALUES (?, ?, ?, ?)");
            $stmt2->execute([$id, trim($_POST['telephone']), $_POST['date_naissance'], trim($_POST['adresse'])]);
        }

        $_SESSION['user_id']     = $id;
        $_SESSION['user_nom']    = $nom;
        $_SESSION['user_prenom'] = $prenom;
        $_SESSION['user_role']   = $role;

        match($role) {
            'patient' => header("Location: patient/dashbord.php"),
            'medecin' => header("Location: medecin/dashbord.php"),
            'admin'   => header("Location: admin/dashbord.php"),
        };
        exit;
    }
}
?>