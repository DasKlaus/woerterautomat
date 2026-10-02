DROP TABLE IF EXISTS `games`, `playerstatus`;

DROP TABLE IF EXISTS `candidate`;
DROP TABLE IF EXISTS `reaction`;
DROP TABLE IF EXISTS `solution`;
DROP TABLE IF EXISTS `word`;
DROP TABLE IF EXISTS `player`;
DROP TABLE IF EXISTS `game`;

CREATE TABLE `game` (
	`id` int(10) NOT NULL AUTO_INCREMENT,
	`source_word` varchar(64) NOT NULL,
	`solutions` int(10) NOT NULL DEFAULT -1, -- frequent and common words; -1 when no dictionary answered
	`language` varchar(10) NOT NULL DEFAULT 'de',
	`umlauts` tinyint(1) NOT NULL DEFAULT 0,
	`flexion` tinyint(1) NOT NULL DEFAULT 0,
	`dictionary` tinyint(1) NOT NULL DEFAULT 0,
	`private` tinyint(1) NOT NULL DEFAULT 0,
	`maxplayers` int(10) NOT NULL DEFAULT 0,
	`timelimit` int(10) NOT NULL DEFAULT 10080,
	`status` tinyint(3) NOT NULL DEFAULT 0,
	`version` int(10) NOT NULL DEFAULT 0,
	`daily` date DEFAULT NULL, -- the day of a daily game, null for any other
	`created_by` bigint(20) NOT NULL,
	`created_by_name` varchar(255) NOT NULL,
	`created_at` datetime NOT NULL,
	`last_activity_at` datetime NOT NULL,
	PRIMARY KEY (`id`),
	UNIQUE KEY `daily_language` (`daily`, `language`),
	KEY `status_created` (`status`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `player` (
	`game_id` int(10) NOT NULL,
	`user_id` bigint(20) NOT NULL,
	`display_name` varchar(255) NOT NULL,
	`status` tinyint(3) NOT NULL DEFAULT 0,
	`points` int(10) NOT NULL DEFAULT 0,
	`streak` int(10) NOT NULL DEFAULT 0, -- daily only: yesterday's same-language row's streak plus one, or one
	`joined_at` datetime NOT NULL,
	`activity` datetime NOT NULL,
	PRIMARY KEY (`game_id`, `user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `word` (
	`game_id` int(10) NOT NULL,
	`user_id` bigint(20) NOT NULL,
	`word` varchar(64) COLLATE utf8mb4_bin NOT NULL,
	`created_at` datetime NOT NULL,
	PRIMARY KEY (`game_id`, `user_id`, `word`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `solution` (
	`game_id` int(10) NOT NULL,
	`word` varchar(64) COLLATE utf8mb4_bin NOT NULL,
	`rarity` tinyint(3) NOT NULL DEFAULT 0, -- the dictionary's 0 (frequent) to 5 (disallowed); the default only serves the migration
	PRIMARY KEY (`game_id`, `word`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `reaction` (
	`game_id` int(10) NOT NULL,
	`word` varchar(64) COLLATE utf8mb4_bin NOT NULL,
	`reactor_id` bigint(20) NOT NULL,
	`emoji` varchar(8) NOT NULL,
	`display_name` varchar(255) NOT NULL,
	`created_at` datetime NOT NULL,
	PRIMARY KEY (`game_id`, `word`, `reactor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- the daily's unused words, filled by candidates.php
CREATE TABLE `candidate` (
	`language` varchar(10) NOT NULL,
	`word` varchar(64) COLLATE utf8mb4_bin NOT NULL,
	`flexion` tinyint(1) NOT NULL DEFAULT 0,
	`umlauts` tinyint(1) NOT NULL DEFAULT 0, -- the word is then spelled substituted too
	`target` varchar(255) COLLATE utf8mb4_bin NOT NULL, -- the ten frequent long words in its solution, space-separated, for review
	`day` int(10) DEFAULT NULL, -- the number of the day it is planned for; a length's lowest number goes first, so a 1 puts a word ahead
	PRIMARY KEY (`language`, `word`, `flexion`, `umlauts`) -- one spelling may qualify under two settings
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
