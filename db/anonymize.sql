-- Anonymize a vici database for development or sharing.
-- Removes all personal data and non-content cache tables.
-- Usage: mysql -u <user> <database> < db/anonymize.sql

UPDATE accounts SET
  acc_name     = CONCAT('user', acc_id),
  acc_pw       = '',
  acc_realname = CONCAT('user', acc_id),
  acc_email    = CONCAT('user', acc_id, '@example.local'),
  acc_passwd   = '';

TRUNCATE TABLE unconfirmedaccs;
TRUNCATE TABLE resetpw;

DROP TABLE batch;
DROP TABLE q_dare;
DROP TABLE q_pleiades;
DROP TABLE q_wikipedia;

-- Remove hidden (superseded/deleted) lines and their zoom simplifications
DELETE d FROM pline_data d JOIN plines l ON d.pldata_pline_id = l.line_id WHERE l.line_hide=1;
DELETE FROM plines WHERE line_hide=1;
