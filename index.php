<?php

session_start();

$error = '';

if (isset($_SESSION['user_data']))
{
    header('location:home.php');
}

if (isset($_POST['login']))
{
    require_once('database/UserModel.php');

    $user_object = new UserModel;

    $user_object->setEmail($_POST['email']);
    $user_data = $user_object->get_user_data_by_email();

    if (is_array($user_data) && count($user_data) > 0)
    {
        if ($user_data['password_hash'] == $_POST['password_hash'])
        {
            $user_object->setUserId($user_data['user_id']);
            $user_object->setIsOnline(True);

            $user_token = md5(uniqid());
            $user_object->setUserToken($user_token);

            if ($user_object->update_user_login_data())
            {
                $_SESSION['user_data'][$user_data['user_id']] = [
                    'id'      =>  $user_data['user_id'],
                    'name'    =>  $user_data['username'],
                    'token'   =>  $user_token
                ];

                header('location:home.php');
            }
        }
        else
        {
            $error = 'Mot de passe incorrect !';
        }
    }
    else
    {
        $error = 'Adresse e-mail incorrecte !';
    }
}

?>

<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <title>Page de Connexion</title>
        <link rel="stylesheet" href="style_register.css">
        <link rel="icon" type="image/x-icon" href="img/bubble-chat.png">
    </head>
    <body>
        <div class="container_login">
            <img class="image-section" src="img/img_login.avif" alt="image" height="500" width="400">

            <div class="form-section">
                <h1>Connexion</h1>
                <p>Accédez à votre espace personnel.</p>

                <?php
                if (isset($_SESSION['success_message'])) {
                    echo '<div id="success" style="color: #00ab0a;">' . $_SESSION['success_message'] . '</div>';
                    unset($_SESSION['success_message']);
                }

                if ($error != '') {
                    echo '<div id="danger" style="color: red;">' . $error . '</div>';
                }
                ?>

                <br/>

                <form id="login-form" method="post" onsubmit="return validateLoginForm()">
                    <div class="input-group">
                        <label for="email">Email</label>
                        <input type="email" name="email" id="email" required>
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
    </body>
</html>