<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Chargement sécurisé des fichiers extraits dans ton dossier libs
require_once __DIR__ . '/../libs/PHPMailer/Exception.php';
require_once __DIR__ . '/../libs/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../libs/PHPMailer/SMTP.php';

/**
 * Fonction globale pour envoyer le code de validation par email
 */
function envoyerEmailVerification($email_destinataire, $prenom_destinataire, $code_code) {
    $mail = new PHPMailer(true);

    try {
        // Configuration du serveur de messagerie (SMTP de Gmail)
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        
        // ⚠️ À CONFIGURER AVEC TES PARAMÈTRES GOOGLE :
        $mail->Username   = 'melvynnkeze@gmail.com'; 
        $mail->Password   = 'njqw nrkw vyeb vfup'; // Le code de 16 caractères de Google
        
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';

        // Expéditeur et Destinataire
        $mail->setFrom('no-reply@medconnect.ga', 'MedConnect');
        $mail->addAddress($email_destinataire, $prenom_destinataire);

        // Contenu de l'email au format HTML
        $mail->isHTML(true);
        $mail->Subject = 'MedConnect - Code de validation';
        $mail->Body    = "
            <div style='font-family: Arial, sans-serif; padding: 30px; border: 1px solid #e0e0e0; border-radius: 8px; max-width: 600px; margin: 0 auto;'>
                <div style='text-align: center; margin-bottom: 20px;'>
                    <h2 style='color: #0d6efd; margin: 0;'>Bienvenue sur MedConnect</h2>
                    <p style='color: #6c757d; margin-top: 5px;'>Votre plateforme de santé en ligne</p>
                </div>
                <hr style='border: 0; border-top: 1px solid #eee;'>
                <p>Bonjour <strong>" . htmlspecialchars($prenom_destinataire) . "</strong>,</p>
                <p>Merci pour votre inscription. Pour finaliser la création de votre espace patient sécurisé, veuillez saisir le code de validation d'activation ci-dessous :</p>
                
                <div style='background: #f8f9fa; padding: 20px; text-align: center; font-size: 32px; font-weight: bold; letter-spacing: 6px; color: #0d6efd; border: 1px dashed #0d6efd; border-radius: 6px; margin: 25px 0;'>
                    " . $code_code . "
                </div>
                
                <p style='font-size: 13px; color: #6c757d;'>Ce code est confidentiel et unique. Si vous n'êtes pas à l'origine de cette inscription, veuillez ignorer cet email.</p>
                <hr style='border: 0; border-top: 1px solid #eee; margin-top: 30px;'>
                <p style='text-align: center; font-size: 11px; color: #adb5bd; margin: 0;'>&copy; 2026 MedConnect. Tous droits réservés.</p>
            </div>
        ";

        $mail->send();
        return true;
    } catch (Exception $e) {
        // En cas de bug, décommente la ligne suivante pour voir l'erreur :
        // echo "Erreur d'envoi : " . $mail->ErrorInfo;
        return false;
    }
}