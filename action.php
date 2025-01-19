<?php

session_start();

// Script de déconnexion : (requête AJAX depuis group_chat.php et discussion.php) : Si le bouton Logout a été cliqué dans group_chat.php et discussion.php, et que la requête HTTP a été envoyée/effectuée via AJAX
if (isset($_POST['action']) && $_POST['action'] == 'leave')
{
	require('database/UserModel.php');

	$user_object = new UserModel;

	$user_object->setUserId($_POST['user_id']);
	$user_object->setIsOnline(False);
	$user_object->setUserToken($_SESSION['user_data'][$_POST['user_id']]['token']);

	if ($user_object->update_user_login_data())
	{
		unset($_SESSION['user_data']);
		session_destroy();
		echo json_encode([
			'status' => 1
		]);
	}
}



// Requête AJAX depuis discussion.php : Récupérer l'historique des discussions privées dans la table `Message` entre l'utilisateur authentifié/connecté et l'utilisateur qu'il (l'utilisateur authentifié/connecté) a sélectionné/cliqué pour discuter avec lui (dans discussion.php).
if (isset($_POST["action"]) && $_POST["action"] == 'fetch_chat')
{
	require 'database/MessageModel.php';

	$private_chat_object = new MessageModel;

	$private_chat_object->setFromUserId($_POST["to_user_id"]);
	$private_chat_object->setToUserId($_POST["from_user_id"]);

	echo json_encode($private_chat_object->get_all_chat_data());
}


?>