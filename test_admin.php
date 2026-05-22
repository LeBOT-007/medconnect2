<?php
session_start();
require_once 'config/database.php';

$pdo = getConnexion();
$email_test = 'admin@medconnect.com';
$mdp_test = 'admin123';

echo "<h2>--- Diagnostic de Connexion Admin ---</h2>";

// 1. Génération et insertion automatique d'un compte propre
$hash = password_hash($mdp_test, PASSWORD_BCRYPT);

try {
    // On nettoie d'abord l'ancien compte pour éviter les doublons
    $pdo->prepare("DELETE FROM utilisateurs WHERE email = ?")->execute([$email_test]);
    
    // On insère le compte test
    $stmt = $pdo->prepare("INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, role) VALUES ('ADMIN', 'Test', ?, ?, 'admin')");
    $stmt->execute([$email_test, $hash]);
    echo "<p style='color:green;'> Étape 1 : Compte test inséré/réinitialisé avec succès avec l'email <strong>$email_test</strong> et le mot de passe <strong>$mdp_test</strong>.</p>";
} catch (Exception $e) {
    die("<p style='color:red;'> Erreur lors de l'insertion : " . $e->getMessage() . " (Vérifie si les noms de tes colonnes sont bien 'email', 'mot_de_passe', 'role')</p>");
}

// 2. Simulation de la requête de login.php
echo "<h3>Simulation de la recherche en BDD :</h3>";
$stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE email = ?");
$stmt->execute([$email_test]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    echo "<p style='color:green;'> Étape 2 : L'utilisateur a bien été trouvé dans la base de données !</p>";
    echo "<ul>";
    echo "<li>Rôle trouvé en BDD : <strong>'" . $user['role'] . "'</strong></li>";
    echo "<li>Longueur du mot de passe stocké : <strong>" . strlen($user['mot_de_passe']) . "</strong> caractères</li>";
    echo "</ul>";
    
    // 3. Test de la fonction password_verify
    if (password_verify($mdp_test, $user['mot_de_passe'])) {
        echo "<p style='color:green;'> Étape 3 : La fonction password_verify() FONCTIONNE ! Le mot de passe correspond au hash.</p>";
        echo "<p><strong>Résultat :</strong> Si ce script affiche tout en vert, ton problème venait soit de la longueur de ton champ VARCHAR (qui coupait le hash avant), soit d'un espace. Tu peux maintenant essayer de te connecter sur <a href='login.php'>login.php</a> avec les identifiants de test.</p>";
    } else {
        echo "<p style='color:red;'> Étape 3 : password_verify() a ÉCHOUÉ. Le hash récupéré a été altéré ou tronqué par la base de données.</p>";
        echo "<p>Regarde la longueur ci-dessus : si elle fait moins de 60 caractères, augmente la taille de ta colonne 'mot_de_passe' à VARCHAR(255).</p>";
    }
} else {
    echo "<p style='color:red;'> Étape 2 : Impossible de retrouver l'utilisateur juste après l'avoir inséré.</p>";
}
?>