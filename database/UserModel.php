<?php



class UserModel
{
	private $user_id;
	private $username;
	private $email;
	private $password_hash;
	private $created_at;
	private $is_online;
	private $user_token;
	private $user_connection_id;
	public $connect;



	public function __construct()
	{
		require_once('Database_connection.php');
		$database_object = new Database_connection;
		$this->connect = $database_object->connect();
	}


	function setUserId($user_id)
	{
		$this->user_id = $user_id;
	}

	function getUserId()
	{
		return $this->user_id;
	}

	function setUsername($username)
	{
		$this->username = $username;
	}

	function getUsername()
	{
		return $this->username;
	}

	function setEmail($email)
	{
		$this->email = $email;
	}

	function getEmail()
	{
		return $this->email;
	}

	function setPasswordHash($password_hash)
	{
		$this->password_hash = $password_hash;
	}

	function getPasswordHash()
	{
		return $this->password_hash;
	}

	function setCreatedAt($created_at)
	{
		$this->created_at = $created_at;
	}

	function getCreatedAt()
	{
		return $this->created_at;
	}

	function setIsOnline($is_online)
	{
		$this->is_online = $is_online;
	}

	function getIsOnline()
	{
		return $this->is_online;
	}

	function setUserToken($user_token)
	{
		$this->user_token = $user_token;
	}

	function getUserToken()
	{
		return $this->user_token;
	}

	function setUserConnectionId($user_connection_id)
	{
		$this->user_connection_id = $user_connection_id;
	}

	function getUserConnectionId()
	{
		return $this->user_connection_id;
	}

	function get_user_data_by_email()
	{
		$query = 
			"SELECT * FROM User 
			WHERE email = :email
			ORDER BY is_online DESC"
		;

		$stmt = $this->connect->prepare($query);

		$stmt->bindParam(':email', $this->email);

		if ($stmt->execute())
		{
			$user_data = $stmt->fetch(PDO::FETCH_ASSOC);
		}

		return $user_data;
	}

	function save_data()
	{
		$query =
			"INSERT INTO User (user_id, username, email, password_hash, created_at) 
			VALUES (:user_id, :username, :email, :password_hash, :created_at)"
		;
		$stmt = $this->connect->prepare($query);

		$req_user_id_max = 'SELECT MAX(user_id) AS max_user_id FROM User';
		$stmt2 = $this->connect->query($req_user_id_max);
		$ligne = $stmt2->fetch(PDO::FETCH_ASSOC);

		if ($ligne && isset($ligne['max_user_id'])) {
        	$this->user_id = $ligne['max_user_id'] + 1;
    	} else {
        	$this->user_id = 0;
		}
		
		$stmt->bindParam(':user_id'			   , $this->user_id);
		$stmt->bindParam(':username'			   , $this->username);
		$stmt->bindParam(':email'			   , $this->email);
		$stmt->bindParam(':password_hash'		   , $this->password_hash);
		$stmt->bindParam(':created_at'       , $this->created_at);

		if ($stmt->execute())
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	function update_user_login_data()
	{
		$query =
			"UPDATE User 
			SET
				is_online = :is_online,
				user_token 		  = :user_token  
			WHERE
				user_id = :user_id"
		;

		$stmt = $this->connect->prepare($query);

		$stmt->bindParam(':is_online', $this->is_online);
		$stmt->bindParam(':user_token'		  , $this->user_token);
		$stmt->bindParam(':user_id'		  , $this->user_id);

		if ($stmt->execute())
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	function get_user_data_by_id()
	{
		$query = 
			"SELECT * FROM User 
			WHERE user_id = :user_id
			ORDER BY is_online DESC"
		;

		$stmt = $this->connect->prepare($query);
		$stmt->bindParam(':user_id', $this->user_id);

		try
		{
			if ($stmt->execute())
			{
				$user_data = $stmt->fetch(PDO::FETCH_ASSOC);
			}
			else
			{
				$user_data = array();
			}
		}
		catch (Exception $error)
		{
			echo $error->getMessage();
		}


		return $user_data;
	}

	function update_data()
	{
		$query =
			"UPDATE User 
			SET
				username     = :username, 
				email    = :email, 
				password_hash = :password_hash 
			WHERE user_id = :user_id"
		;

		$stmt = $this->connect->prepare($query);

		$stmt->bindParam(':username'	  , $this->username);
		$stmt->bindParam(':email'   , $this->email);
		$stmt->bindParam(':password_hash', $this->password_hash);
		$stmt->bindParam(':user_id'	  , $this->user_id);

		if ($stmt->execute())
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	function get_user_all_data()
	{
		$query =
			"SELECT * FROM User
			 ORDER BY is_online DESC"
		;

		$stmt = $this->connect->prepare($query);
		$stmt->execute();
		$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

		return $data;
	}

	function update_user_connection_id()
	{
		$query =
			"UPDATE User 
			SET user_connection_id = :user_connection_id 
			WHERE user_token = :user_token
			ORDER BY is_online DESC"
		;

		$stmt = $this->connect->prepare($query);

		$stmt->bindParam(':user_connection_id', $this->user_connection_id);
		$stmt->bindParam(':user_token'		   , $this->user_token);

		$stmt->execute();
	}

	function get_user_id_from_token()
	{
		$query =
			"SELECT user_id FROM User 
			WHERE user_token = :user_token"
		;

		$stmt = $this->connect->prepare($query);
		$stmt->bindParam(':user_token', $this->user_token);
		$stmt->execute();
		$user_id = $stmt->fetch(PDO::FETCH_ASSOC);

		return $user_id;
	}

}