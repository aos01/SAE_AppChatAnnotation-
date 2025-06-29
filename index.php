<?php

session_start();

$error = '';

// MODIFICATION 1 : Redirection directe vers discussion au lieu de home.php
if (isset($_SESSION['user_data'])) {
    header('location:discussion.php');
    exit();
}

if (isset($_POST['login'])) {
    require_once('database/UserModel.php');

    $user_object = new UserModel;

    // Validation côté serveur
    if (empty($_POST['email']) || empty($_POST['password_hash'])) {
        $error = 'Veuillez remplir tous les champs.';
    } else {
        $user_object->setEmail($_POST['email']);
        $user_data = $user_object->get_user_data_by_email();

        if (is_array($user_data) && count($user_data) > 0) {
            // VÉRIFICATION SÉCURISÉE DU MOT DE PASSE
            if (method_exists($user_object, 'verifyPassword')) {
                $password_valid = $user_object->verifyPassword($_POST['password_hash'], $user_data['password_hash']);
            } else {
                $password_valid = password_verify($_POST['password_hash'], $user_data['password_hash']);
            }

            if ($password_valid) {
                $user_object->setUserId($user_data['user_id']);
                $user_object->setIsOnline(True);

                // Génération d'un token sécurisé
                $user_token = bin2hex(random_bytes(16));
                $user_object->setUserToken($user_token);

                if ($user_object->update_user_login_data()) {
                    $_SESSION['user_data'][$user_data['user_id']] = [
                        'id'      =>  $user_data['user_id'],
                        'name'    =>  $user_data['username'],
                        'token'   =>  $user_token
                    ];

                    // MODIFICATION : Redirection directe vers discussion
                    header('location:discussion.php');
                    exit();
                }
            } else {
                $error = 'Mot de passe incorrect !';
            }
        } else {
            $error = 'Adresse e-mail incorrecte !';
        }
    }
}

?>

<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <title>Connexion - Poppins</title>
        <link rel="stylesheet" href="style_register.css">
        <link rel="icon" type="image/x-icon" href="img/bubble-chat.png">
    </head>
    <body>
        <div class="container_login">
            <img class="image-section" src="img/img_login.avif" alt="image" height="500" width="400">

            <div class="form-section">
                <h1>Connexion à Poppins</h1>
                <p>Chat avec annotation émotionnelle obligatoire</p>

                <?php
                if (isset($_SESSION['success_message'])) {
                    echo '<div id="success" style="color: #00ab0a; padding: 10px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 5px; margin: 10px 0;">' . htmlspecialchars($_SESSION['success_message']) . '</div>';
                    unset($_SESSION['success_message']);
                }

                if ($error != '') {
                    echo '<div id="danger" style="color: #721c24; padding: 10px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 5px; margin: 10px 0;">' . htmlspecialchars($error) . '</div>';
                }
                ?>

                <br/>

                <form id="login-form" method="post" onsubmit="return validateLoginForm()">
                    <div class="input-group">
                        <label for="email">Email</label>
                        <input type="email" name="email" id="email" required 
                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    </div>
                    <div class="input-group">
                        <label for="password_hash">Mot de passe</label>
                        <input type="password" name="password_hash" id="password" required>
                        <i class="fas fa-eye" onclick="togglePasswordVisibility('password')"></i>
                    </div>
                    <button type="submit" name="login" class="create-account-btn">Se connecter</button>
                </form>
                <p class="login-link">Vous n'avez pas de compte ? <a href="signin.php">Inscrivez-vous</a></p>
            </div>
        </div>

        <script src="script_register.js"></script>
        
        <script>
        // Validation côté client améliorée
        function validateLoginForm() {
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;
            
            if (email === '' || password === '') {
                alert('Veuillez remplir tous les champs.');
                return false;
            }
            
            if (password.length < 6) {
                alert('Le mot de passe doit contenir au moins 6 caractères.');
                return false;
            }
            
            return true;
        }
        </script>
    </body>
</html>