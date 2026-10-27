-- Grant the app user read access to the geo database.
-- MariaDB's entrypoint runs /docker-entrypoint-initdb.d files alphabetically,
-- so this file runs after geo.sql.gz and vici.sql.gz have been imported.

GRANT SELECT ON `geo`.* TO 'vici'@'%';
FLUSH PRIVILEGES;
