DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
	`id` bigint(20) NOT NULL AUTO_INCREMENT,
	`display_name` varchar(255) NOT NULL,
	`language` char(2) NOT NULL DEFAULT '',
	`code` char(32) NOT NULL,
	`restriction` tinyint(3) unsigned DEFAULT NULL,
	`restriction_reason` varchar(255) DEFAULT NULL,
	`created_at` datetime NOT NULL,
	`last_seen_at` datetime NOT NULL,
	PRIMARY KEY (`id`),
	UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `achievement`;
CREATE TABLE `achievement` (
	`user_id` bigint(20) NOT NULL,
	`badge` varchar(32) NOT NULL,
	`level` tinyint(3) unsigned NOT NULL,
	`granted_at` datetime NOT NULL,
	`seen` tinyint(1) NOT NULL DEFAULT 0,
	PRIMARY KEY (`user_id`, `badge`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
