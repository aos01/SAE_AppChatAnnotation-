SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

CREATE TABLE `Message` (
  `conversation_id` int NOT NULL,
  `message_id` int UNIQUE NOT NULL,
  `to_user_id` int NOT NULL,
  `from_user_id` int NOT NULL,
  `content` text NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE `User` (
  `user_id` int UNIQUE NOT NULL,
  `username` varchar(250) NOT NULL,
  `email` varchar(250) NOT NULL,
  `password_hash` varchar(100) NOT NULL,
  `created_at` datetime NOT NULL,
  `is_online` BOOLEAN NOT NULL,
  `user_token` varchar(32) DEFAULT NULL,
  `user_connection_id` int DEFAULT NULL
);

CREATE TABLE `Annotation` (
    `annotation_id` INT PRIMARY KEY,
    `message_id` INT NOT NULL,
    `annotator_id` INT NOT NULL,
    `emotion` ENUM('joie', 'colère', 'tristesse', 'surprise', 'dégoût', 'peur') NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);