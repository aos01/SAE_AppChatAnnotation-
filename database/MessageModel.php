<?php

class MessageModel
{
	private $conversation_id;
	private $message_id;
	private $to_user_id;
	private $from_user_id;
	private $content;
	private $timestamp;
	private $emotion;
	protected $connect;



	public function __construct()
	{
		require_once('Database_connection.php');
		$db = new Database_connection();
		$this->connect = $db->connect();
	}


	function setEmotion($emotion)
	{
		$this->emotion = $emotion;
	}

	function getEmotion()
	{
		return $this->emotion;
	}
	function setConversationId($conversation_id)
	{
		$this->conversation_id = $conversation_id;
	}

	function getConversationId()
	{
		return $this->conversation_id;
	}

	function getMessageId()
	{
		return $this->message_id;
	}

	function setMessageId($message_id)
	{
		$this->message_id = $message_id;
	}
	function setToUserId($to_user_id)
	{
		$this->to_user_id = $to_user_id;
	}

	function getToUserId()
	{
		return $this->to_user_id;
	}

	function setFromUserId($from_user_id)
	{
		$this->from_user_id = $from_user_id;
	}

	function getFromUserId()
	{
		return $this->from_user_id;
	}

	function setChatMessage($chat_message)
	{
		$this->content = $chat_message;
	}

	function getChatMessage()
	{
		return $this->content;
	}

	function setTimestamp($timestamp)
	{
		$this->timestamp = $timestamp;
	}

	function getTimestamp()
	{
		return $this->timestamp;
	}

	function get_all_chat_data()
	{
		$query =
			"SELECT
				a.username as from_username, b.username as to_username, message_id, content, timestamp, to_user_id, from_user_id  
			FROM Message
			INNER JOIN User a
				ON Message.from_user_id = a.user_id
			INNER JOIN User b
				ON Message.to_user_id = b.user_id
			WHERE
				(Message.from_user_id = :from_user_id AND Message.to_user_id = :to_user_id)
			OR
				(Message.from_user_id = :to_user_id   AND Message.to_user_id = :from_user_id)"
		;

		$stmt = $this->connect->prepare($query);

		$stmt->bindParam(':from_user_id', $this->from_user_id);
		$stmt->bindParam(':to_user_id'	 , $this->to_user_id);

		$stmt->execute();

		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	function save_chat()
		{
			$check_query = "
				SELECT conversation_id 
				FROM Message 
				WHERE (to_user_id = :to_user_id AND from_user_id = :from_user_id)
				OR (to_user_id = :from_user_id AND from_user_id = :to_user_id)
				LIMIT 1
			";

			$check_stmt = $this->connect->prepare($check_query);

			$check_stmt->bindParam(':to_user_id', $this->to_user_id);
			$check_stmt->bindParam(':from_user_id', $this->from_user_id);
			$check_stmt->execute();

			$result = $check_stmt->fetch(PDO::FETCH_ASSOC);

			if ($result) {
				$this->conversation_id = $result['conversation_id'];
			} else {

				$max_conv_id_query = "SELECT IFNULL(MAX(conversation_id) + 1, 0) AS new_conversation_id FROM Message";
				$max_conv_id_stmt = $this->connect->prepare($max_conv_id_query);
				$max_conv_id_stmt->execute();
				$max_conv_id_result = $max_conv_id_stmt->fetch(PDO::FETCH_ASSOC);

				$this->conversation_id = $max_conv_id_result['new_conversation_id'];
			}

			$max_msg_id_query = "SELECT IFNULL(MAX(message_id) + 1, 0) AS new_message_id FROM Message";
			$max_msg_id_stmt = $this->connect->prepare($max_msg_id_query);
			$max_msg_id_stmt->execute();
			$max_msg_id_result = $max_msg_id_stmt->fetch(PDO::FETCH_ASSOC);

			$this->message_id = $max_msg_id_result['new_message_id'];


			$query = "
				INSERT INTO Message 
					(conversation_id, message_id, to_user_id, from_user_id, content, timestamp) 
				VALUES
					(:conversation_id, :message_id, :to_user_id, :from_user_id, :content, :timestamp)
			";

			$stmt = $this->connect->prepare($query);

			$stmt->bindParam(':conversation_id', $this->conversation_id);
			$stmt->bindParam(':message_id', $this->message_id);
			$stmt->bindParam(':to_user_id', $this->to_user_id);
			$stmt->bindParam(':from_user_id', $this->from_user_id);
			$stmt->bindParam(':content', $this->content);
			$stmt->bindParam(':timestamp', $this->timestamp);

			$stmt->execute();

			return True;
		}

		function save_annotation()
		{
			$max_anno_id_query = "SELECT IFNULL(MAX(annotation_id) + 1, 0) AS new_annotation_id FROM Annotation";
			$max_anno_id_stmt = $this->connect->prepare($max_anno_id_query);
			$max_anno_id_stmt->execute();
			$max_anno_id_result = $max_anno_id_stmt->fetch(PDO::FETCH_ASSOC);

			$query = "INSERT INTO Annotation (annotation_id, message_id, annotator_id, emotion, created_at)
						VALUES (:annotation_id, :message_id, :annotator_id, :emotion, :created_at)";

			$stmt = $this->connect->prepare($query);

			$stmt->bindParam(':annotation_id', $max_anno_id_result['new_annotation_id']);
			$stmt->bindParam(':message_id', $this->message_id);
			$stmt->bindParam(':annotator_id', $this->from_user_id);
			$stmt->bindParam(':emotion', $this->emotion);
			$stmt->bindParam(':created_at', $this->timestamp);
			$stmt->execute();

			return True;
		}

		function get_last_sender() {
			$query = "
				SELECT from_user_id 
				FROM Message 
				WHERE 
					(from_user_id = :from_user_id AND to_user_id = :to_user_id)
				OR
					(from_user_id = :to_user_id   AND to_user_id = :from_user_id)
				ORDER BY message_id DESC 
				LIMIT 1
			";
		
			$stmt = $this->connect->prepare($query);
			$stmt->bindParam(':from_user_id', $this->from_user_id);
			$stmt->bindParam(':to_user_id', $this->to_user_id);
			$stmt->execute();
		
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
		
			return $result ? $result['from_user_id'] : null;
		}
}

?>