DROP TABLE IF EXISTS `game1`, `game5`, `game6`, `game7`, `game8`, `game9`, `game10`,
	`game12`, `game13`, `game14`, `game15`, `game16`, `game17`, `game18`, `game19`,
	`game20`, `game21`, `game22`, `game23`, `game24`, `game25`, `game26`, `game27`,
	`game28`, `game29`, `game30`, `game31`, `game32`, `game38`, `game40`, `game41`,
	`game42`, `game43`, `game44`, `game45`, `game46`, `game47`;

DROP TABLE IF EXISTS `games`;
CREATE TABLE `games` (
	`id` int(10) NOT NULL AUTO_INCREMENT,
	`word` varchar(255) NOT NULL,
	`status` tinyint(3) NOT NULL DEFAULT 0,
	`starter` varchar(255) NOT NULL,
	`date` timestamp NOT NULL DEFAULT current_timestamp(),
	`language` varchar(10) NOT NULL DEFAULT 'de',
	`flexion` int(2) NOT NULL DEFAULT 0,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `playerstatus`;
CREATE TABLE `playerstatus` (
	`game` int(10) NOT NULL,
	`player` varchar(255) NOT NULL,
	`status` int(3) NOT NULL,
	`points` int(10) NOT NULL DEFAULT 0,
	`timeleft` int(10) NOT NULL DEFAULT 600,
	`activity` timestamp NOT NULL DEFAULT current_timestamp(),
	PRIMARY KEY (`game`, `player`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
