-- Grant viciuser access to the geo database
-- MariaDB executes files in /docker-entrypoint-initdb.d alphabetically,
-- so this file runs after geo.sql.gz and vici.sql.gz

GRANT SELECT ON geo.* TO 'viciuser'@'%';
FLUSH PRIVILEGES;
