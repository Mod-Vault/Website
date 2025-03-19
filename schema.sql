SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

CREATE TABLE `attached_file_status` (
  `uid` int(11) NOT NULL,
  `description` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `attached_file_status` (`uid`, `description`) VALUES
(1, 'Pending'),
(2, 'Processing'),
(3, 'Rejected'),
(4, 'Approved');

CREATE TABLE `games` (
  `uid` int(11) NOT NULL,
  `thumbnail` text NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `mod_attached_files` (
	`uid` INT(11) NOT NULL,
	`mod_catalog_id` INT(11) NOT NULL,
	`status` INT(11) NOT NULL DEFAULT '1',
	`version` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_general_ci',
	`path` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_general_ci',
	`filename` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_general_ci',
	`set_new_version_on_approval` TINYINT(1) NOT NULL DEFAULT '0',
	`rejection_reason` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	`judged_timestamp` DATETIME NULL DEFAULT NULL,
	`virustotal_analyses_id` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	`submitted_timestamp` DATETIME NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `mod_attached_links` (
  `uid` int(11) NOT NULL,
  `mod_catalog_id` int(11) NOT NULL,
  `href` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `mod_catalog` (
  `uid` int(11) NOT NULL,
  `game_id` int(11) NOT NULL,
  `disabled` int(11) NOT NULL DEFAULT 0,
  `owner` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `current_version` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `mod_catalog_changelogs` (
  `uid` int(11) NOT NULL,
  `mod_catalog_id` int(11) NOT NULL,
  `version` varchar(255) NOT NULL,
  `log` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `users` (
  `uid` int(11) NOT NULL,
  `display_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `is_admin` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `users_login_tokens` (
  `user_id` int(11) NOT NULL,
  `hash` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


ALTER TABLE `attached_file_status`
  ADD PRIMARY KEY (`uid`);

ALTER TABLE `games`
  ADD PRIMARY KEY (`uid`);

ALTER TABLE `mod_attached_files`
  ADD PRIMARY KEY (`uid`),
  ADD KEY `mod_catalog_id` (`mod_catalog_id`),
  ADD KEY `status` (`status`);

ALTER TABLE `mod_attached_links`
  ADD PRIMARY KEY (`uid`),
  ADD KEY `mod_catalog_id` (`mod_catalog_id`);

ALTER TABLE `mod_catalog`
  ADD PRIMARY KEY (`uid`),
  ADD KEY `game_id` (`game_id`);

ALTER TABLE `mod_catalog_changelogs`
  ADD PRIMARY KEY (`uid`),
  ADD KEY `mod_catalog_id` (`mod_catalog_id`);

ALTER TABLE `users`
  ADD PRIMARY KEY (`uid`);

ALTER TABLE `users_login_tokens`
  ADD KEY `users_login_tokens_ibfk_1` (`user_id`);


ALTER TABLE `attached_file_status`
  MODIFY `uid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

ALTER TABLE `games`
  MODIFY `uid` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `mod_attached_files`
  MODIFY `uid` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `mod_attached_links`
  MODIFY `uid` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `mod_catalog`
  MODIFY `uid` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `mod_catalog_changelogs`
  MODIFY `uid` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `users`
  MODIFY `uid` int(11) NOT NULL AUTO_INCREMENT;


ALTER TABLE `mod_attached_files`
  ADD CONSTRAINT `mod_attached_files_ibfk_1` FOREIGN KEY (`mod_catalog_id`) REFERENCES `mod_catalog` (`uid`),
  ADD CONSTRAINT `mod_attached_files_ibfk_2` FOREIGN KEY (`status`) REFERENCES `attached_file_status` (`uid`);

ALTER TABLE `mod_attached_links`
  ADD CONSTRAINT `mod_attached_links_ibfk_1` FOREIGN KEY (`mod_catalog_id`) REFERENCES `mod_catalog` (`uid`);

ALTER TABLE `mod_catalog`
  ADD CONSTRAINT `mod_catalog_ibfk_1` FOREIGN KEY (`game_id`) REFERENCES `games` (`uid`);

ALTER TABLE `mod_catalog_changelogs`
  ADD CONSTRAINT `mod_catalog_changelogs_ibfk_1` FOREIGN KEY (`mod_catalog_id`) REFERENCES `mod_catalog` (`uid`);

ALTER TABLE `users_login_tokens`
  ADD CONSTRAINT `users_login_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`uid`) ON DELETE CASCADE ON UPDATE CASCADE;


CREATE TABLE `mod_file_downloads` (
	`game_catalog_id` INT(11) NOT NULL,
	`mod_attached_file_id` INT(11) NOT NULL,
	`timestamp` DATETIME NOT NULL DEFAULT current_timestamp(),
	INDEX `game_catalog_id` (`game_catalog_id`) USING BTREE,
	INDEX `mod_attached_file_id` (`mod_attached_file_id`) USING BTREE,
	CONSTRAINT `FK__mod_attached_files` FOREIGN KEY (`mod_attached_file_id`) REFERENCES `mod_attached_files` (`uid`) ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT `FK__mod_catalog` FOREIGN KEY (`game_catalog_id`) REFERENCES `mod_catalog` (`uid`) ON UPDATE CASCADE ON DELETE CASCADE
)
COLLATE='utf8mb4_general_ci'
ENGINE=InnoDB;

-- 03-16-2025
ALTER TABLE `mod_attached_links`
	ADD COLUMN `required` TINYINT NOT NULL DEFAULT 0 AFTER `description`;

ALTER TABLE `mod_catalog_changelogs`
	DROP FOREIGN KEY `mod_catalog_changelogs_ibfk_1`;
ALTER TABLE `mod_catalog_changelogs`
	ADD CONSTRAINT `mod_catalog_changelogs_ibfk_1` FOREIGN KEY (`mod_catalog_id`) REFERENCES `mod_catalog` (`uid`) ON UPDATE RESTRICT ON DELETE RESTRICT;

COMMIT;
