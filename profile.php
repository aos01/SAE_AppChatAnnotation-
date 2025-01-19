<?php 


session_start();

if (!isset($_SESSION['user_data']))
{
    header('location:index.php');
}


require('database/UserModel.php');


$user_object = new UserModel;
$login_user_id = '';

foreach ($_SESSION['user_data'] as $key => $value)
{
    $login_user_id = $value['id'];
    $token = $value['token'];
}

$user_object->setUserId($login_user_id);
$user_data = $user_object->get_user_data_by_id();


$message = '';

if (isset($_POST['edit']))
{
    $user_object->setUsername($_POST['username']);
    $user_object->setEmail($_POST['email']);
    $user_object->setPasswordHash($_POST['password_hash']);
    $user_object->setUserId($login_user_id);

    if ($user_object->update_data())
    {
        $message = '<div class="alert alert-success" style="color: #00ab0a;">Les détails du profil ont été modifiés avec succès.</div>';
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <title>Détails du profil</title>

        <link rel="stylesheet" href="style_profile.css">
        <link rel="icon" type="image/x-icon" href="img/bubble-chat.png">
		<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

        <!-- Bootstrap core JavaScript -->
		<script src="vendor-front/jquery/jquery.min.js"></script>
		<script type="text/javascript" src="vendor-front/parsley/dist/parsley.min.js"></script>
		
    </head>
    <body>
        <div class="container">

            <div class="sidebar" id="sidebar">
                <div>
                    <img src="img/profile.png" alt="profil" class="user_icon">
                    <?php echo "<p style='margin-top: 0px; color: white; font-size: 16px; font-weight: 500; text-align: center;'>" . $user_data['username'] . '</p>';?>
                </div>
                <ul class="menu">
                    <li class="menu-item">
                        <a href="home.php"><img src="img/home.png" alt="Home Icon"></a>
                    </li>
                    <li class="menu-item">
                        <a href="discussion.php"><img src="img/chat.png" alt="Messages Icon"></a>
                    </li>
                    <li class="menu-item active">
                        <a href="profile.php"><img src="img/settings.png" alt="Settings Icon"></a>
                    </li>
                </ul>
                <div class="logout">
                    <a id="logout"><img src="img/logout.png"/></a>
                </div>
            </div>
                    <input type="hidden" name="login_user_id" id="login_user_id" value="<?php echo $login_user_id; ?>" />
            <div class="main-content" style="font-family:'Poppins';">
                <div class="form-container">
                    <h1>Détails du profil</h1>
                    <?php echo $message; ?>
                    <br/>
                    <form method="POST" id="profile_form" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="name">Nom</label>
                            <input type="text" name="username" id="username" class="form-control" data-parsley-pattern="/^[a-zA-Z\s]+$/" required value="<?php echo $user_data['username']; ?>" />
                        </div>
                        <div class="form-group">
                            <label for="mail">Adresse Mail</label>
                            <input type="email" name="email" id="email" class="form-control" data-parsley-type="email" required readonly value="<?php echo $user_data['email']; ?>" />
                        </div>
                        <div class="form-group">
                            <label>Mot de passe</label>
                            <input type="password" name="password_hash" id="password_hash" class="form-control" data-parsley-minlength="6" data-parsley-maxlength="12" data-parsley-pattern="^[a-zA-Z0-9]+$" required value="<?php echo $user_data['password_hash']; ?>" />
                        </div>
                        <div class="form-actions">
                            <button style="background-color: #007bff; color: white" type="submit" name="edit" id="save" class="btn btn-succes">Sauvegarder</button>
                            <a href="discussion.php"><button type="button" id="Return">Retour</button></a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </body>
    <script type="text/javascript">
		$(document).ready(function(){

            var conn = new WebSocket('ws://localhost:8080?token=<?php echo $token; ?>');

            $('#logout').click(function(){
				user_id = $('#login_user_id').val();

				$.ajax({
					url   :"action.php",
					method:"POST",
					data  : {
						user_id: user_id,
						action : 'leave'
					},
					success:function(data) 
					{
						var response = JSON.parse(data);

						if (response.status == 1) 
						{
							conn.close();
							location = 'index.php';
						}
					}
				})
			});
		})
	</script>
</html>