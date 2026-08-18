DROP TABLE IF EXISTS `game1`, `game5`, `game6`, `game7`, `game8`, `game9`, `game10`,
	`game12`, `game13`, `game14`, `game15`, `game16`, `game17`, `game18`, `game19`,
	`game20`, `game21`, `game22`, `game23`, `game24`, `game25`, `game26`, `game27`,
	`game28`, `game29`, `game30`, `game31`, `game32`, `game38`, `game40`, `game41`,
	`game42`, `game43`, `game44`, `game45`, `game46`, `game47`;

DROP TABLE IF EXISTS `games`, `playerstatus`;

DROP TABLE IF EXISTS `word`;
DROP TABLE IF EXISTS `player`;
DROP TABLE IF EXISTS `game`;

CREATE TABLE `game` (
	`id` int(10) NOT NULL AUTO_INCREMENT,
	`source_word` varchar(64) NOT NULL,
	`language` varchar(10) NOT NULL DEFAULT 'de',
	`flexion` tinyint(1) NOT NULL DEFAULT 0,
	`status` tinyint(3) NOT NULL DEFAULT 0,
	`version` int(10) NOT NULL DEFAULT 0,
	`created_by` bigint(20) NOT NULL,
	`created_by_name` varchar(255) NOT NULL,
	`created_at` datetime NOT NULL,
	`last_activity_at` datetime NOT NULL,
	PRIMARY KEY (`id`),
	KEY `status_created` (`status`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `player` (
	`game_id` int(10) NOT NULL,
	`user_id` bigint(20) NOT NULL,
	`display_name` varchar(255) NOT NULL,
	`status` tinyint(3) NOT NULL DEFAULT 0,
	`points` int(10) NOT NULL DEFAULT 0,
	`joined_at` datetime NOT NULL,
	`activity` datetime NOT NULL,
	PRIMARY KEY (`game_id`, `user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `word` (
	`game_id` int(10) NOT NULL,
	`user_id` bigint(20) NOT NULL,
	`word` varchar(64) NOT NULL,
	`created_at` datetime NOT NULL,
	PRIMARY KEY (`game_id`, `user_id`, `word`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
