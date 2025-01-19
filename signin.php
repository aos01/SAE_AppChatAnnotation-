<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

$error = '';
$success_message = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    session_start();

    if (isset($_SESSION['user_data'])) {
        header('location:discussion.php');
        exit;
    }

    require_once('database/UserModel.php');

    $user_object = new UserModel;

    $user_object->setUsername($_POST['username']);
    $user_object->setEmail($_POST['email']);
    $user_object->setPasswordHash($_POST['password']);
    $user_object->setCreatedAt(date('Y-m-d H:i:s'));

    $user_data = $user_object->get_user_data_by_email();

    if (is_array($user_data) && count($user_data) > 0) {
        $error = 'Cet email existe déjà !';
    } else {
        if ($user_object->save_data()) {
            $success_message = 'Inscription réussie !';
        } else {
            $error = "Une erreur s'est produite. Veuillez réessayer !";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <title>Page d'Inscription</title>
        <link rel="stylesheet" href="style_register.css">
        <link rel="icon" type="image/x-icon" href="img/bubble-chat.png">
    </head>
    <body>

        <div class="container_signin">
            <img class="image-section" src="img/img_signin.avif" alt="image" height="600" width="500">

            <div class="form-section">

                <h1>Inscription</h1>
                <p>Commencez par créer un compte pour accéder à votre espace personnel.</p>

                <?php if ($error != ''): ?>
                    <div id="danger" style="color: red";> <?= $error ?> </div>
                <?php endif; ?>

                <?php if ($success_message != ''): ?>
                    <div id="succes" style="color: #00ab0a;"> <?= $success_message ?> </div>
                <?php endif; ?>

                <br/>

                <form id="signup-form" method="post" onsubmit="return validateForm()">
                    <div class="form-row">
                        <div class="input-group">
                            <label for="username">Nom d'utilisateur</label>
                            <input type="text" name="username" id="username" placeholder="exemple" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="input-group">
                            <label for="email">Email</label>
                            <input type="email" name="email" id="email" placeholder="mail@exemple.com" required>
                        </div>
                    </div>
                    <div class="input-group">
                        <label for="password">Mot de passe</label>
                        <input type="password" name="password" id="password" required>
                        <i class="fas fa-eye" onclick="togglePasswordVisibility('password')"></i>
                    </div>
                    <button type="submit" class="create-account-btn">Créer un compte</button>
                </form>

                <p class="login-link">Vous avez déjà un compte ? <a href="index.php">Connexion</a></p>
            </div>
        </div>
    <script src="script_register.js"></script>
    </body>
</html>