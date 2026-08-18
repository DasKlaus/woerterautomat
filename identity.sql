DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
	`id` bigint(20) NOT NULL AUTO_INCREMENT,
	`display_name` varchar(255) NOT NULL,
	`email` varchar(190) DEFAULT NULL,
	`password_hash` varchar(255) DEFAULT NULL,
	`created_at` datetime NOT NULL,
	`claimed_at` datetime DEFAULT NULL,
	`last_seen_at` datetime NOT NULL,
	PRIMARY KEY (`id`),
	UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
