-- User profile bio/links for the /user/{id} profile pages.
-- Apply to the dev database and to production before deploying:
--   mysql -u <user> <database> < db/usrbio.sql

CREATE TABLE IF NOT EXISTS `usrbio` (
  `ubio_user` int(11) unsigned NOT NULL,
  `ubio_bio` text,
  `ubio_links` text,
  `ubio_public` tinyint(1) NOT NULL DEFAULT 0,
  `ubio_contact` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`ubio_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
