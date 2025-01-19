<?php
// Voici notre classe de gestionnaire WebSocket personnalisée (qui met en œuvre l'interface « MessageComponentInterface » de la bibliothèque Ratchet)
// Remarque : si vous modifiez un code lié au serveur WebSocket (c'est-à-dire la bibliothèque Ratchet) (exemple : si vous modifiez un code de la classe Chat.php), vous devez redémarrer le serveur WebSocket pour que les modifications soient prises en compte (en ouvrant le terminal et en arrêtant le serveur WebSocket en cours d'exécution par CTRL + C, puis en le redémarrant à l'aide de la commande « php bin/server.php »)

namespace MyApp;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
require dirname(__DIR__) . "/database/UserModel.php";
require dirname(__DIR__) . "/database/MessageModel.php";


class Chat implements MessageComponentInterface { // La classe « Chat » est notre classe de traitement WebSocket personnalisée qui met en œuvre l'interface « MessageComponentInterface » de la bibliothèque Ratchet.
    protected $clients;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
        echo 'Server Started';
    }

    public function onOpen(ConnectionInterface $conn) {
        // Stocker la nouvelle connexion pour envoyer des messages ultérieurement
        echo 'Server Started';

        $this->clients->attach($conn);


        // Indiquer le statut en ligne/hors ligne
        $querystring = $conn->httpRequest->getUri()->getQuery(); // Récupérer le «token» que nous avons généré nous-mêmes lors de la connexion de l'utilisateur dans index.php, puis JavaScript (côté client) le transmet dans l'URL en tant que paramètre de la chaîne de requête dans discussion.php

        parse_str($querystring, $queryarray);

        if (isset($queryarray['token'])) // Afficher le statut de l'utilisateur en ligne/hors ligne
        {
            // Obtenir l'identifiant de l'utilisateur `user_id` qui vient d'ouvrir la connexion WebSocket à partir de la table `User` en se basant sur le 'token'
            $user_object = new \UserModel;

            $user_object->setUserToken($queryarray['token']);
            $user_object->setUserConnectionId($conn->resourceId);
            $user_object->update_user_connection_id();
            $user_data = $user_object->get_user_id_from_token();
            $user_id = $user_data['user_id'];

            $data['user_id']     = $user_id;
            $data['status_type'] = 'Online';

            foreach ($this->clients as $client)
            {
                $client->send(json_encode($data));
            }
        }
        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $numRecv = count($this->clients) - 1;
        echo sprintf('Connection %d sending message "%s" to %d other connection%s' . "\n"
            , $from->resourceId, $msg, $numRecv, $numRecv == 1 ? '' : 's');


        $data = json_decode($msg, true);

        // Pour stocker les messages de Chat dans la table `Message` (pour sauvegarder et afficher l'historique du Chat)
        $private_chat_object = new \MessageModel;

        $private_chat_object->setToUserId($data['receiver_userid']);
        $private_chat_object->setFromUserId($data['userId']);
        $private_chat_object->setChatMessage($data['msg']);
        $timestamp = date('Y-m-d h:i:s');
        $private_chat_object->setTimestamp($timestamp);

        if ($private_chat_object->getFromUserId() === $private_chat_object->get_last_sender()) {
            foreach ($this->clients as $client)
            {
                $client->send(json_encode(['error' => 'ping-pong'])); // permet d'envoyer "l'erreur" au client
            }
        } 
        else {
            $private_chat_object->save_chat(); // permet de sauvegarder le message

            $private_chat_object->setEmotion($data['emotion']);
            $private_chat_object->save_annotation(); // permet de sauvegarder l'annotation

            $user_object = new \UserModel;

            // Obtenir les données de l'expéditeur d'un message à partir de la table `User`.
            $user_object->setUserId($data['userId']);
            $sender_user_data = $user_object->get_user_data_by_id();
            $sender_username = $sender_user_data['username'];

            // Récupérer les données du destinataire d'un message dans la table `User`.
            $user_object->setUserId($data['receiver_userid']);
            $receiver_user_data = $user_object->get_user_data_by_id();
            $receiver_user_connection_id = $receiver_user_data['user_connection_id'];

            $data['datetime'] = $timestamp;

            foreach ($this->clients as $client)
            {
                if ($from == $client)
                {
                    $data['from'] = 'Me';
                }
                else
                {
                    $data['from'] = $sender_username; 
                }


                if ($client->resourceId == $receiver_user_connection_id || $from == $client) 
                {   
                    $client->send(json_encode($data)); 
                }
            }
        }
    }

    public function onClose(ConnectionInterface $conn) {

        $querystring = $conn->httpRequest->getUri()->getQuery();

        parse_str($querystring, $queryarray);

        if (isset($queryarray['token']))
        {
            $user_object = new \UserModel;

            $user_object->setUserToken($queryarray['token']);
            $user_data = $user_object->get_user_id_from_token();
            $user_id = $user_data['user_id'];

            $data['user_id']     = $user_id;  
            $data['status_type'] = 'Offline'; 

            foreach ($this->clients as $client)
            {
                $client->send(json_encode($data)); 
            }
        }

        $this->clients->detach($conn);

        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }
}

?>