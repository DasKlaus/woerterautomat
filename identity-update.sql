ALTER TABLE `user`
	DROP COLUMN `email`,
	DROP COLUMN `password_hash`,
	DROP COLUMN `claimed_at`,
	ADD COLUMN `code` char(32) DEFAULT NULL AFTER `display_name`,
	ADD COLUMN `restriction` tinyint(3) unsigned DEFAULT NULL AFTER `code`,
	ADD COLUMN `restriction_reason` varchar(255) DEFAULT NULL AFTER `restriction`;

UPDATE `user` SET `code` = md5(rand());

ALTER TABLE `user`
	MODIFY COLUMN `code` char(32) NOT NULL,
	ADD UNIQUE KEY `code` (`code`);
