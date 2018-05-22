DROP TABLE persistent_logins1;
DROP TABLE films;
DROP TABLE user_test1;

CREATE TABLE `user_test1` (
  `id` int(6) unsigned AUTO_INCREMENT NOT NULL,
  `username` varchar(30) NOT NULL,
  `password` varchar(255) NOT NULL,
  `reg_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `api_key` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
);

INSERT INTO user_test1
(username, password, api_key)
VALUES
('user', '$2y$10$lb9VV9kPIYYDyVcKgB.iwe28igPF7Icbj0qavJ5oFaK1CZsrpw.Ku', 'c0BjqevDu9xB1awSo44c');

CREATE TABLE `persistent_logins1` (
  `id` int(6) unsigned AUTO_INCREMENT NOT NULL,
  `hash` varchar(255) NOT NULL,
  `user_id` int(6) unsigned NOT NULL,
  `ip` varchar(100) NOT NULL,
  `expires` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
);

CREATE TABLE `films` (
  `id` int(6) unsigned AUTO_INCREMENT NOT NULL,
  `date_seen` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `title` varchar(255) NOT NULL,
  `user_id` int(6) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_user_id` (`user_id`),
  CONSTRAINT `fk_user_id` FOREIGN KEY (`user_id`) REFERENCES `user_test1` (`id`)
);

