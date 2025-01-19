<?php



session_start();

if (!isset($_SESSION['user_data'])) // Si l'utilisateur n'est pas authentifié/déconnecté, redirigez-le vers la page de connexion
{
	header('location:index.php');
}


require('database/UserModel.php');

foreach ($_SESSION['user_data'] as $key => $value)
{
	$token = $value['token'];
}

$user_object = new UserModel;
$user_data = $user_object->get_user_all_data();

?>

<!DOCTYPE html>
<html lang="fr">
	<head>
		<meta charset="UTF-8">
		<title>Discussions</title>

		<link rel="icon" type="image/x-icon" href="img/bubble-chat.png">
		<link rel="stylesheet" href="style_discussion.css">
		<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

		<!-- JavaScript de base d'amorçage -->
		<script src="vendor-front/jquery/jquery.min.js"></script>
		<script type="text/javascript" src="vendor-front/parsley/dist/parsley.min.js"></script>
		
	</head>
	<body>

		<div class="container"> <!--container-fluid-->
			<div class="sidebar" id="sidebar">
				<div>
					<img src="img/profile.png" alt="profil" class="user_icon">
					<?php foreach ($user_data as $user) {if ($user['user_token'] === $token) {echo "<p style='margin-top: 0px; color: white; font-size: 16px; font-weight: 500; text-align: center; font-family: 'Poppins', sans-serif;'>" . $user['username'] . '</p>';}}?>
				</div>
				<ul class="menu">
					<li class="menu-item">
						<a href="home.php"><img src="img/home.png" alt="Home Icon"></a>
					</li>
					<li class="menu-item active">
						<a href="discussion.php"><img src="img/chat.png" alt="Messages Icon"></a>
					</li>
					<li class="menu-item">
						<a href="profile.php"><img src="img/settings.png" alt="Settings Icon"></a>
					</li>
				</ul>
				<div class="logout">
					<a id="logout"><img src="img/logout.png"/></a>
				</div>
			</div>

			<div class="main-container" style="font-family: 'Poppins';">
				<div class="main-content" style="background: linear-gradient(to bottom, #f306d3, #6a11cb, #21fcff);">
					<div class="box" style="height: 100vh; flex: 0.35">
						<?php
							$login_user_id = '';
							$token = '';

							foreach ($_SESSION['user_data'] as $key => $value)
							{
								$login_user_id = $value['id'];
								$token = $value['token'];
						?>
								<!-- Afficher les données du profil utilisateur authentifié/connecté (sur le côté gauche de la page) -->
								<input type="hidden" name="login_user_id" id="login_user_id" value="<?php echo $login_user_id; ?>" />
								<input type="hidden" name="is_active_chat" id="is_active_chat" value="No" /> <!-- Ceci est utilisé pour contrôler l'ouverture/fermeture de la zone de discussion privée (à l'aide de JavaScript) lorsqu'on clique sur un utilisateur de la liste des utilisateurs (sur le côté gauche de la page) pour discuter avec lui. -->

								<div>
									<h2>Utilisateurs</h2>
								</div>
						<?php
							}
						?>

						<?php
							$user_object->setUserId($login_user_id);
						?>

						<!-- Afficher tous les utilisateurs/membres du chat (sur le côté gauche de la page) et leur statut en ligne/hors ligne (basé sur la colonne « is_online » de la table de base de données « Utilisateur »)-->
						<div class="list-group" style="max-height: 80vh; overflow-y: auto; -webkit-overflow-scrolling: touch;">
						<?php
								foreach ($user_data as $key => $user)
								{
									// Afficher le statut de l'utilisateur en ligne/hors ligne (ici avec le cas « Chat en tête-à-tête/privé) (basé sur onOpen() et onClose du gestionnaire WebSocket personnalisé Chat.php Class)
									$icon = '<i class="offline"></i>'; 

								// Si l'utilisateur est authentifié/connecté (en fonction de la colonne `is_online` de la table de base de données `User`, et non de la session du navigateur), affichez le cercle 'vert' pour indiquer le statut de l'utilisateur 'En ligne'
									if ($user['is_online'])
									{
										$icon = '<i class="online"></i>'; // Afficher un cercle « vert » pour indiquer le statut « En ligne » de l'utilisateurs
									}


									// Pour afficher tous les utilisateurs/membres du chat SAUF l'utilisateur authentifié/connecté (nous ne voulons pas afficher l'utilisateur actuellement authentifié. Nous voulons les exclure.)
									if ($user['user_id'] != $login_user_id) 
									{
										echo "
											<a class='list-group-item list-group-item-action select_user' style='cursor:pointer' data-userid = '" . $user['user_id'] . "'>
												<img src='img/profile.png' width='50' />
												<span>
													<strong>
														<span id='list_username_" . $user["user_id"] . "'>" . $user['username'] . "</span>
													</strong>
												</span>
												<span id='userstatus_" . $user['user_id'] . "'>" . $icon . "</span>
											</a>
										";
									}
								}
							?>
						</div>
					</div>
					
					<div class="box" style="backgroud-color: white;">
						<h2>Discussions</h2>
						<hr />
						<br />
						<div id="chat_area"></div> 
					
				</div>
			</div>
		</div>
	</body>

	<!-- JavaScript --------------------------------------------------------------->

	<script type="text/javascript">
		$(document).ready(function(){
			

			var receiver_userid = ''; // L'utilisateur sur lequel l'utilisateur authentifié/connecté a cliqué (dans la liste des utilisateurs) pour lui envoyer (il/elle) un message de discussion « privé »


			// Gestion de la partie côté client (navigateur) de la connexion WebSocket (en utilisant JavaScript)
			var conn = new WebSocket('ws://localhost:8080?token=<?php echo $token; ?>'); 



			conn.onopen = function(event)
			{
			
				console.log('Connection Established!');
			};

			



			
			conn.onmessage = function(event)
			{
				var data = JSON.parse(event.data); 
				console.log(data);

				if (!data.error) {

					// Note: Pour afficher le statut de l'utilisateur en ligne/hors ligne, avec le chat « One-to-One/Privé », nous nous sommes appuyés sur les méthodes onOpen() et onClose() de la classe Chat.php du gestionnaire WebSocket personnalisé (qui est le meilleur moyen car c'est réel). -time et Instantané), mais avec le Chat 'Groupe', nous dépendions de la colonne `is_online` de la table de la base de données `User` (ce qui est une mauvaise idée, car un utilisateur peut simplement fermer le navigateur et ne cliquez pas sur Déconnexion, et s'ils ne cliquent pas sur Déconnexion, la valeur de la colonne « is_online » ne sera pas modifiée, alors leur statut en ligne/hors ligne sera toujours « En ligne »).
					if (data.status_type == 'Online') 
					{
						$('#userstatus_' + data.user_id).html('<i class="online"></i>'); 
					}
					else if (data.status_type == 'Offline') 
					{
						$('#userstatus_' + data.user_id).html('<i class="offline"></i>'); 
					}
					else 
					{
						
						if (data.from == 'Me')
						{
							
							var myNotificationAudioPath = 'vendor-front/sounds/mixkit-clear-announce-tones-2861.wav';
						}
						else 
						{
							
							var myNotificationAudioPath = 'vendor-front/sounds/mixkit-arabian-mystery-harp-notification-2489.wav';
						}

						// Audio quand y'a un message
						let myAudio = new Audio(myNotificationAudioPath);
						myAudio.play(); 

										
						
						if (receiver_userid == data.userId || data.from == 'Me') {
							if ($('#is_active_chat').val() == 'Yes') {
								// Déterminez le style pour les messages envoyés ou reçus
								// Déterminez le style pour les messages envoyés ou reçus


								/// premier solution de message long
								const isSender = data.from == 'Me';
								const alignmentStyle = isSender 
									? 'display: flex; justify-content: flex-end; margin-bottom: 10px;' 
									: 'display: flex; justify-content: flex-start; margin-bottom: 10px;';
								const bubbleStyle = isSender
									? 'max-width: 70%; padding: 10px; border-radius: 15px; background-color: #d1e7dd; color: #0f5132; text-align: right; word-wrap: break-word; word-break: break-word; box-shadow: 0px 2px 4px rgba(0, 0, 0, 0.1);'
									: 'max-width: 70%; padding: 10px; border-radius: 15px; background-color: #f8d7da; color: #842029; text-align: left; word-wrap: break-word; word-break: break-word; box-shadow: 0px 2px 4px rgba(0, 0, 0, 0.1);';

								// Construisez le HTML pour le message avec styles en ligne
								const html_data = `
								<div style="${alignmentStyle}">
									<div style="${bubbleStyle}">
									<b>${isSender ? 'Vous' : data.from} :</b> ${data.msg}<br />
									<div style="text-align: right; font-size: 12px; color: #555;">
										<i>${data.datetime}</i>
									</div>
									</div>
								</div>
								`;

								// Ajoutez le message dans la zone de discussion
								$('#messages_area').append(html_data);
								$('#messages_area').scrollTop($('#messages_area')[0].scrollHeight); // Faites défiler vers le bas
								$('#chat_message').val(''); // Effacez le champ de saisie après envoi ou réception

							}
						}
					}
				}
			};



			conn.onclose = function(event) 
			{
				console.log('Connection Closed!');
			};


			function make_chat_area(username)// Lorsqu'on clique sur un utilisateur pour discuter (sur le côté gauche dans le chat privé) // Le paramètre de la fonction username est le nom d'utilisateur de l'utilisateur cliqué (destinataire)
			{
				var html = `
					<div style="border: 1px solid #e0e0e0; border-radius: 10px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); margin-bottom: 20px;">
						<div style="background-color: #f7f7f7; border-bottom: 1px solid #e0e0e0; padding: 10px 15px;">
							<div class="row" style="display: flex; justify-content: space-between; align-items: center;">
								<div style="display: flex; align-items: center;">
									<b style="font-size: 16px;">Discussion avec : <span class="text-danger" id="chat_username">${username}</span></b>
								</div>
								<div>
									<button type="button" class="close" id="close_chat_area" data-dismiss="alert" aria-label="Close" style="font-size: 1.5rem; color: #999; cursor: pointer;">
										<span aria-hidden="true">&times;</span>
									</button>
								</div>
							</div>
						</div>
						<div id="messages_area" style="height: 400px; overflow-y: auto; padding: 10px; background-color: #fdfdfd; font-family: Arial, sans-serif;">
							<!-- Les messages seront affichés ici -->
						</div>
					</div>

					<form id="chat_form" method="POST" style="display: flex; align-items: center; margin-top: 10px;">

						<select id="emotion" name="annotation" style="border-radius: 20px; padding: 10px; font-size: 14px; margin-right: 10px; height: 50px;">
							<option value="" disabled selected>Annotation</option>
							<option value="joie">😄</option>
							<option value="colère">😡</option>
							<option value="dégoût">🤮</option>
							<option value="tristesse">😢</option>
							<option value="surprise">😲</option>
							<option value="peur">😱</option>
						</select>
						<textarea id="chat_message" name="chat_message" placeholder="Écrivez votre message ici" style="border-radius: 20px; resize: none; height: 50px; padding: 10px; font-size: 14px; flex-grow: 1; margin-right: 10px;"></textarea>
						<button type="submit" name="send" id="send" class="btn btn-primary" style="background-color: #007bff; border: none; border-radius: 50%; color: white; padding: 15px; cursor: pointer;">
							<i style="font-size: 18px;"></i>
						</button>
					</form>
				`;


				$('#chat_area').html(html); 
			}

			// Lorsque l'utilisateur authentifié/connecté clique sur un utilisateur dans la liste des utilisateurs de gauche pour lui envoyer un message de discussion, afficher/afficher la zone de discussion privée, récupérer l'historique des discussions avec cet utilisateur via un AJAX. demande et supprimez le nombre/numéro de messages « non lus » de notification push de couleur rouge
			$(document).on('click', '.select_user', function(){
				receiver_userid  = $(this).data('userid');   
				var from_user_id = $('#login_user_id').val(); 
				var receiver_username = $('#list_username_' + receiver_userid).text(); 

				$('.select_user.active').removeClass('active'); 
				$(this).addClass('active'); 

				make_chat_area(receiver_username); 
				$('#is_active_chat').val('Yes'); 


				//Récupère l'historique de discussion privé de l'utilisateur authentifié/connecté avec l'utilisateur sélectionné dans la table de base de données `Message` en utilisant AJAX
				$.ajax({
					url     :"action.php",
					method  :"POST",
					data    :{
						action      :'fetch_chat',
						to_user_id  : receiver_userid, 
						from_user_id: from_user_id     
					},
					dataType:"JSON",
					success:function(data) 
					{
						if (data.length > 0) { 
    
							let html_data = '';

							for (let count = 0; count < data.length; count++) {
								// Déterminez le style pour les messages envoyés ou reçus
								const isSender = data[count].from_user_id == from_user_id; // Vérifie si l'utilisateur authentifié a envoyé le message
								const alignmentStyle = isSender 
								? 'display: flex; justify-content: flex-end; margin-bottom: 10px;' 
								: 'display: flex; justify-content: flex-start; margin-bottom: 10px;';
								const bubbleStyle = isSender
								? 'max-width: 70%; padding: 10px; border-radius: 15px; background-color: #d1e7dd; color: #0f5132; text-align: right; word-wrap: break-word; word-break: break-word; box-shadow: 0px 2px 4px rgba(0, 0, 0, 0.1);'
								: 'max-width: 70%; padding: 10px; border-radius: 15px; background-color: #f8d7da; color: #842029; text-align: left; word-wrap: break-word; word-break: break-word; box-shadow: 0px 2px 4px rgba(0, 0, 0, 0.1);';

								const username = isSender ? 'Vous' : data[count].from_username;

								// Construisez le HTML pour chaque message
								html_data += `
									<div style="${alignmentStyle}">
										<div style="${bubbleStyle}">
											<b>${username} :</b> ${data[count].content}<br />
											<div style="text-align: right; font-size: 12px; color: #555;">
												<i>${data[count].timestamp}</i>
											</div>
										</div>
									</div>
								`;
							}


							$('#userid_' + receiver_userid).html(''); 
							$('#messages_area').html(html_data); 
							$('#messages_area').scrollTop($('#messages_area')[0].scrollHeight); 
						}
					}
				})

			});









		
			$(document).on('click', '#close_chat_area', function(){
				$('#chat_area').html(''); 
				$('.select_user.active').removeClass('active'); 
				$('#is_active_chat').val('No'); 

				receiver_userid = ''; 
			});

			// Gestion de la soumission de formulaire HTML de chat 'en tête-à-tête/privé' (envoi de messages à un utilisateur particulier/spécifique, PAS à tous les utilisateurs comme avec le chat 'Groupe') (Gestion de l'envoi de messages de chat à la méthode onMessage() du gestionnaire WebSocket personnalisé (classe Chat.php)
			$(document).on('submit', '#chat_form', function(event){
				event.preventDefault(); 

				if ($('#chat_form').parsley().isValid()) 
				{
					var user_id = parseInt($('#login_user_id').val()); 
					var message = $('#chat_message').val(); 
					var emotion = $('#emotion').val();

					if (message && emotion) {

						var data = { 
							userId         : user_id,
							msg            : message,
							receiver_userid:receiver_userid,
							emotion		   : emotion,
							command        :'private' 
						};
					
					conn.send(JSON.stringify(data)); 
					$('#emotion').prop('selectedIndex', 0);
					}
					else {
						alert('Veuillez choisir une annotation et entrer un message avant d\'envoyer.');
					}
				
				}

			});

			// Déconnexion (Lorsque vous cliquez sur le bouton Déconnexion (le bouton est dans ce fichier)) (N.B. Cela met à jour la colonne `is_online` de la table de base de données `Utilisateur` de 'Connexion' à 'Déconnexion')
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